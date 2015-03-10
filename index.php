<?php 

require_once dirName(__FILE__).'/core/index.php';
$_DEBUG=FALSE;
require_once dirName(__FILE__).'/config.php';
$mydb = new db($connection);
/*
if(file_exists('./db.ser'))
{
	$mydb->scheme->import('./db.ser');
	$mydb->commit();
}
else
{*/
	$mydb->scheme->add('user',Array(
			'login'=>'text',
			'password'=>'text',
			'name'=>Array("Type"=>'text','charset'=>'utf8')
	));
	$mydb->scheme->add('group',Array(
			'name'=>'text',
			//'fld1'=>'varchar',
			'parent'=>Array('Type'=>'bigint',
					"Default"=>0,
					'bind'=>Array(
						'table_to'=>'group',
						'field_to'=>'id',
					),
				)
			));
	$mydb->scheme->add('groupmember',Array(
			'user'=>'#user.id',
			//'fld1'=>'varchar',
			'group'=>'#group.id',
			'owner'=>'logic'
	));
	$mydb->scheme->add('category',Array(
			'name'=>'text',
			'user'=>'#user.id',
			//'fld1'=>'varchar',
			'parent'=>Array('Type'=>'bigint',
					"Default"=>0,
					'bind'=>Array(
						'table_to'=>'category',
						'field_to'=>'id',
					),
				)
	));
	$mydb->scheme->add('project',Array(
			'name'=>'text',
			'user'=>'#user.id',
			//'fld1'=>'varchar',
			'date'=>'datetime',			
	));
	
	$mydb->commit();
	$mydb->scheme->gettable('user')->addfield('avatar','text');
	
	//$_DEBUG=TRUE;
	$mydb->commit();
	$mydb->scheme->export('./db.ser');
//}
/*
$res = $mydb->scheme->select(Array(
	'table'=>'user'
));
*/
	$_DEBUG=TRUE;
//  $mydb->scheme->export('./db.jsd',DSIE_JSON);
//$mydb->scheme->export('./db.xml',DSIE_XML);
$res = $mydb->scheme->select('groupmember',Array(
		'user|name',
		'user|login',
		'user',
		'group|name',
		'owner'))->exe('q1');
while($row=$mydb->scheme->res_row($res))
{
	var_dump($row);
}
//var_dump($mydb);
?>