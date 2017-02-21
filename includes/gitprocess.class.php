<?php

class GitProcess extends BaseClass
{

    private $gitPath;

    public function __construct($ftpID)
    {
        parent::__construct();
        $ftpInfo = $this->getFTPSite($ftpID);
        $targetPath = self::chop_slash(BACKUP_PATH) . self::chop_slash($ftpInfo['ftp_path']);
        $this->gitPath = $targetPath;
        if ($this->testGitPath()) {

            if (!$this->isGitStatusEmpty()) {

                $this->perfromGitUpdate();
            }
        }
    }

    private function testGitPath()
    {
        $path = $this->gitPath;
        if (!file_exists($path)) {
            print "\n\nPath does not exist: \n{$path}\n";
            return false;
        }

        if (!file_exists($path . DIRECTORY_SEPARATOR . ".git")) {
            {
                print "\n\nPath is not a git repository: \n{$path}\n";
                return false;
            }
        }
        return true;
    }

    public function isGitStatusEmpty()
    {
        $path = $this->gitPath;
        $response = $this->getGitStatus($path);
        if (in_array("nothing to commit, working tree clean", $response)) {

            return true;
        } else {
            return false;
        }
    }

    private function perfromGitUpdate()
    {

        $comment = "updates as of " . date("Y-m-d H:i");

        $this->doGitCommand("git checkout master");
        $this->doGitCommand("git pull origin master");
        $this->doGitCommand("git add .");
        $this->doGitCommand("git commit -am \"{$comment}\"");
        $this->doGitCommand("git pull origin master");
        $this->doGitCommand("git push origin master");


    }

    private function getGitStatus($gitDir)
    {
        return $this->doGitCommand("git status");
    }

    private function doGitCommand($cmd)
    {
        print "\nexecuting git command: {$cmd}\n";
        $output = [];
        $res = 0;
        $gitDir = $path = $this->gitPath;
        extract($this->getGitStartAndAppend($gitDir));
        $cmds = "{$start} {$append} {$cmd}";
        exec($cmds, $output, $res);
        print_r($output);
        return ($output);
    }

    private function getGitStartAndAppend()
    {
        $path = $this->gitPath;
        if (DIRECTORY_SEPARATOR == "\\") {
            $ar = explode(":", $path);
            $drive = $ar[0] . ":";
            $path = $ar[1];

            return array(
                'start' => "{$drive}: & cd {$path} ",
                'append' => "&"
            );
        } else {
            return array(
                'start' => "cd {$path} ",
                'append' => "&&"
            );
        }

    }
}