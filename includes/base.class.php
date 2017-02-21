<?php

class BaseClass
{
    protected $dbh;

    public function __construct()
    {
        $this->dbh = new Mysql();
    }

    protected function getFTPSite($ftpID)
    {
        $sql = "select * from ftp_site where ftp_site_id = ?";
        $params = array($ftpID);
        $obj = $this->dbh->query($sql, $params);
        if (!isset($obj[0])) {
            $this->errOut("\n\nFTP LookUp Failure... \n");
        }
        return $obj[0];
    }


    protected static function chop_slash($path)
    {
        if (substr($path, -1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (substr($path, -1) == "\\") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        return $path;

    }

}