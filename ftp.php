<?php
require_once "config.php";
require_once "includes/base.class.php";
require_once "includes/ftpoperation.class.php";
require_once "includes/mysql.class.php";
require_once "includes/readftp.class.php";
require_once "includes/writeftp.class.php";
require_once "includes/gitprocess.class.php";


$dbh = new Mysql();
$sql = "select ftp_site_id from ftp_site  ";
$obj = $dbh->query($sql);
foreach ($obj as $o) {
    new ReadFTP($o['ftp_site_id']);
    new WriteFTP($o['ftp_site_id']);
    new GitProcess($o['ftp_site_id']);
}
/*
$array = array(
    '/AdminLogs',
    '/csv',
    '/_vti_cnf'
);

print json_encode($array);

*/