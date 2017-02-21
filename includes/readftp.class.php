<?php

class ReadFTP extends FtpOperation
{


    private $ftpID;

    public function __construct($ftpID)
    {
        parent::__construct();
        $ftpInfo = $this->getFTPSite($ftpID);
        $this->connectFTP($ftpInfo);
        $this->ftpID = $ftpID;
        $this->flushFTPQueue();
        $ignores = self::prependSource(
            self::chop_slash($ftpInfo['ftp_path']),
            (array)json_decode($ftpInfo['paths_to_ignore'])
        );
        $this->readAndSaveFTPPaths($ftpInfo['ftp_path'], $ignores, true);
        $this->disconnectFTP();
    }

    private static function prependSource($sourcePath, $ignores)
    {
        foreach ($ignores as &$ignore) {
            $ignore = self::chop_slash($sourcePath . $ignore);
        }
        return $ignores;
    }


    private function readAndSaveFTPPaths($directory, $ignores, $recursive = false)
    {

        $files = array();

        $list = $this->ftpNList( $directory);
        if (is_array($list)) {

            // Strip away dot directories.
            $list = array_slice($list, 2);

            foreach ($list as $filename) {

                $filename = str_replace("\\", "/", $filename);

                if (strpos($filename, $directory) === false) {
                    $path = self::chop_slash($directory) . '/' . $filename;
                } else {
                    $path = $filename;
                }

                if (in_array($path, $ignores)) {
                    print "skipped: " . $path . "\n";
                    continue;
                }

                $filesize = $this->ftpSize($path);

                // If size equals -1 it's a directory.
                if ($filesize === -1) {
                    if ($recursive) {
                        $this->readAndSaveFTPPaths($path, $ignores, $recursive);
                    }
                } else {
                    // Strip away root directory path to ensure all file paths are relative.
                    $file = array(
                        'filename' => $path,
                        'filesize' => $filesize,
                        'lastModified' => $this->ftpMdtm($path),
                    );
                    print "queuing: " . $path . "\n";
                    $this->saveFTPPath($file);
                }
            }
        }
    }


    private function flushFTPQueue()
    {
        $sql = "delete from ftp_queue where ftp_site_id = ?";
        $params = array($this->ftpID);
        $this->dbh->exec($sql, $params);
    }

    private function saveFTPPath($file)
    {
        $fileInfo = json_encode($file);
        $sql = "insert into ftp_queue (ftp_site_id, ftp_info) values (?,?)";
        $params = array($this->ftpID, $fileInfo);
        $this->dbh->exec($sql, $params);
    }
}