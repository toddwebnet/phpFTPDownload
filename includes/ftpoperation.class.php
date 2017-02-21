<?php

class FtpOperation extends BaseClass
{

    protected $ftpConnection;
    protected $ftpInfo;

    public function __construct()
    {
        parent::__construct();
    }

    protected function connectFTP($ftpInfo)
    {
        $this->ftpInfo = $ftpInfo;
        extract($ftpInfo);

        $this->ftpConnection = ftp_connect($server);
        $login_result = ftp_login($this->ftpConnection, $username, $password);
        ftp_pasv($this->ftpConnection, true);


    }

    protected function disconnectFTP()
    {
        ftp_close($this->ftpConnection);
    }


    protected function errOut($msg)
    {
        print $msg;
        $this->disconnectFTP();
        die();
    }

    protected function ftpGet($newTargetPath, $filePath)
    {
        $c = 0;
        while (true) {
            if (ftp_get($this->ftpConnection, $newTargetPath, $filePath, FTP_BINARY)) {
                if ($c > 0) {
                    print "Looks like it worked\n\n";
                }
                break;
            }
            if ($c == 10) {
                $this->errOut("\n\nI give up - FTP looks hosed\n");
            }
            print "::ftp_get('{$filePath}')\n";
            print "\nAttempting to disconnect and reconnect\n";
            $this->disconnectFTP();
            $this->connectFTP($this->ftpInfo);
            $c++;
        }
    }

    protected function ftpSize($path)
    {
        return ftp_size($this->ftpConnection, $path);
        /*
        $c = 0;
        $size = 0;
        while (true) {
            $size = ftp_size($this->ftpConnection, $path);
            if ($size >= 0) {
                if ($c > 0) {
                    print "Looks like it worked\n\n";
                }
                break;
            }

            if ($c == 10) {
                print("\n\nI give up - FTP looks hosed\n");
                $size = 0;
            }
            print "::ftp_size('{$path}')\n";
            print "\nAttempting to disconnect and reconnect\n";
            $this->disconnectFTP();
            $this->connectFTP($this->ftpInfo);
            $c++;
        }
        return $size;**/
    }

    protected function ftpMdtm($path)
    {
        $c = 0;
        $mdtm = 0;
        while (true) {
            if ($mdtm = ftp_mdtm($this->ftpConnection, $path)) {
                if ($c > 0) {
                    print "Looks like it worked\n\n";
                }
                break;
            }
            if ($c == 10) {
                $size = time();
                print ("\n\nI give up - FTP looks hosed\n");
            }
            print "::ftp_mdtm('{$path}')\n";
            print "\nAttempting to disconnect and reconnect\n";
            $this->disconnectFTP();
            $this->connectFTP($this->ftpInfo);
            $c++;
        }
        return $mdtm;
    }

    protected function ftpNList($path)
    {
        $c = 0;
        while (true) {
            if ($list = ftp_nlist($this->ftpConnection, $path)) {
                if ($c > 0) {
                    print "Looks like it worked\n\n";
                }
                break;
            }
            if (is_array($list)) {
                return $list;
            }
            if ($c == 10) {
                $list = null;
                print("\n\nI give up - FTP looks hosed\n");
            }
            print "::ftp_nlist('{$path}')\n";
            print "\nAttempting to disconnect and reconnect\n";
            $this->disconnectFTP();
            $this->connectFTP($this->ftpInfo);
            $c++;
        }
        return $list;
    }

}