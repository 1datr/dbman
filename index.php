<?php 

require_once dirName(__FILE__).'/core/index.php';
$connection=Array(
	'host'=>'localhost','user'=>'root','dbname'=>'dbmantest','prefix'=>'tdb_'
);
$mydb = new db($connection);
$mydb->scheme->add('user',Array(
		'login'=>'text',
		'password'=>'text',
		'name'=>'text',
));
$mydb->scheme->add('group',Array(
		'name'=>'text',
		//'fld1'=>'varchar',
		'parent'=>'bigint'
));

$mydb->scheme->export('./db.ser');
$mydb->commit();
//var_dump($mydb);
?>