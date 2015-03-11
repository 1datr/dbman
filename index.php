<?php 

require_once dirName(__FILE__).'/core/index.php';
$_DEBUG=FALSE;
require_once dirName(__FILE__).'/config.php';
$mydb = new db($connection);

if(file_exists('./db.ser'))
{
	$mydb->scheme->import('./db.ser');
	//$mydb->commit();
}
else
{
	require dirName(__FILE__).'/dbinit.php';
	
	$mydb->commit();
	$mydb->scheme->gettable('user')->addfield('avatar','text');
	
	//$_DEBUG=TRUE;
	$mydb->commit();
	$mydb->scheme->export('./db.ser');
}

	$_DEBUG=TRUE;
//  $mydb->scheme->export('./db.jsd',DSIE_JSON);
//$mydb->scheme->export('./db.xml',DSIE_XML);

	/*
$res = $mydb->scheme->select('groupmember',Array(
		'user|name',
		'user|login',
		'user',
		'group|name',		
		'owner'))->exe(
		//'q1'
		);
while($row=$mydb->scheme->res_row($res))
{
	var_dump($row);
}
*/
	/*
	$res = $mydb->scheme->select('project',Array(
			'name',
			'user|login as userlogin',			
			'user|name as username',
			//'user|id<groupmember:group',
			))->exe(
					//'q1'
			);
	while($row=$mydb->scheme->res_row($res))
	{
		var_dump($row);
	}
	*/
	/*
	$mydb->scheme->insert('user',Array(
			Array('login'=>'user1','name'=>'user1'),
			Array('login'=>'user2','name'=>'user2')
			)
	)->exe();
	
	$mydb->scheme->update('user',Array('password'=>'123456'))->where("password=''")->exe();
	
	$mydb->scheme->insert('user',Array(
			Array('login'=>'user3','name'=>'user3'),
	)
	)->exe();
	*/
	// $mydb->scheme->delete('user')->where("login='user1'")->exe();
	$mydb->scheme->delete_item('user',5)->exe();
//var_dump($mydb);
?>