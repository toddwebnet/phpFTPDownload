<?php

class WriteFtp extends FtpOperation
{
    private $ftpID;

    public function __construct($ftpID)
    {
        parent::__construct();
        $ftpInfo = $this->getFTPSite($ftpID);
        $this->connectFTP($ftpInfo);
        $this->ftpID = $ftpID;
        $this->processFtpQueue($ftpInfo);
        $this->disconnectFTP();
    }

    private function processFtpQueue($ftpInfo)
    {

        //$targetPath = self::chop_slash($ftpInfo['target_path']);
        $targetPath = self::chop_slash(BACKUP_PATH);// . self::chop_slash($ftpInfo['ftp_path']);

        $sourcePath = self::chop_slash($ftpInfo['ftp_path']);
        $this->testTargetPath($targetPath);

        while (true) {
            $fileInfo = $this->getNextFtpInfoFromQueue();
            if ($fileInfo == null) {
                break;
            }
            $queueId = $fileInfo['queue_id'];
            $file = (array)json_decode($fileInfo['ftp_info']);

            $this->saveNewPath($sourcePath, $targetPath, $file);
            $this->removeFromQueue($queueId);
        }

    }

    private function saveNewPath($sourcePath, $targetPath, $file)
    {
        $filePath = $file['filename'];
        $sourcePath .= self::chop_slash($sourcePath) . "/";
        $newTargetPath = self::chop_slash($targetPath);
        $aTree = explode("/", str_replace($sourcePath, "", $filePath));
        $saveFile = false;
        foreach ($aTree as $index => $branch) {
            $newTargetPath .= $branch;
            if ($index == count($aTree) - 1) {
                if (file_exists($newTargetPath)) {

                    $fileSize = filesize($newTargetPath);
                    $mdtm = filemtime($newTargetPath);
                    if ($file['filesize'] != $fileSize || $file['lastModified'] > $mdtm) {
                        $saveFile = true;
                    }
                } else {
                    $saveFile = true;
                }
            } else {

                if (!file_exists($newTargetPath)) {
                    mkdir($newTargetPath);
                }
                $newTargetPath .= DIRECTORY_SEPARATOR;
            }
        }
        if ($saveFile) {
            print "downloading: {$filePath}\n";
            $this->ftpGet($newTargetPath, $filePath);

        } else {
            print "skipping   : {$filePath}\n";
        }

    }

    private function removeFromQueue($queueId)
    {
        $sql = "delete from ftp_queue where queue_id = ?";
        $params = array($queueId);
        $this->dbh->exec($sql, $params);
    }

    private function getNextFtpInfoFromQueue()
    {
        $sql = "select * from ftp_queue where ftp_site_id = ? order by queue_id limit 1";
        $params = array($this->ftpID);
        $obj = $this->dbh->query($sql, $params);
        if (count($obj) > 0) {
            return $obj[0];
        } else {
            return null;
        }
    }


    private function testTargetPath($targetPath)
    {
        if (!file_exists($targetPath)) {
            $this->errOut("\n\nTarget Path Does Not Exist:\n{$targetPath}\n");
        }
        $touchFile = $targetPath . DIRECTORY_SEPARATOR . "dontTouchMe_" . time() . ".txt";
        touch($touchFile);
        if (!file_exists($targetPath)) {
            $this->errOut("\n\nTarget Path Is Not Writeable:\n{$targetPath}\n");
        }
        unlink($touchFile);
    }

}