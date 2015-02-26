<?php 

require_once dirName(__FILE__).'/core/index.php';
$_DEBUG=FALSE;
require_once dirName(__FILE__).'/config.php';
$mydb = new db($connection);
if(file_exists('./db.ser'))
{
	$mydb->scheme->import('./db.ser');
	$mydb->commit();
}
else
{
	$mydb->scheme->add('user',Array(
			'login'=>'text',
			'password'=>'text',
			'name'=>Array("Type"=>'text','charset'=>'utf8')
	));
	$mydb->scheme->add('group',Array(
			'name'=>'text',
			//'fld1'=>'varchar',
			'parent'=>'bigint'
	));
	$mydb->scheme->add('groupmember',Array(
			'user'=>'#user.id',
			//'fld1'=>'varchar',
			'group'=>'bigint',
			'owner'=>'logic'
	));
	
	$mydb->commit();
	$mydb->scheme->gettable('user')->addfield('avatar','text');
	
	//$_DEBUG=TRUE;
	$mydb->commit();
	$mydb->scheme->export('./db.ser');
}



//var_dump($mydb->scheme->gettable('user'));



//var_dump($mydb->scheme->gettable('user'));

$mydb->scheme->export('./db.jsd',DSIE_JSON);
//$mydb->scheme->export('./db.xml',DSIE_XML);

//var_dump($mydb);
?>