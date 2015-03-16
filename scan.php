<?php 

require_once dirName(__FILE__).'/core/index.php';
$_QDEBUG=FALSE;
$connection=Array(
		'host'=>'localhost',
		'user'=>'root',
		//'password'=>'123456',
		'dbname'=>'svancrm2015',
		'prefix'=>'tbl_'
);
$mydb = new db($connection);

$ser_file = './svan21.ser';
$_marker_file = "./.lift";

$mydb->scheme->scandb();
$mydb->scheme->export('./svancrm.ser');
?>