<?php 

require_once dirName(__FILE__).'/core/index.php';
$_QDEBUG=FALSE;
require_once dirName(__FILE__).'/config.php';
$mydb = new db($connection);

$ser_file = './db.ser';
$_marker_file = "./.lift";
$_init_file = dirName(__FILE__).'/dbinit.php';


function initialize()
{
	GLOBAL $mydb;
	GLOBAL $_QDEBUG;
	GLOBAL $_init_file;
	require $_init_file;
	
	$mydb->commit();
	$mydb->scheme->gettable('user')->addfield('avatar','text');
	
//	$_QDEBUG=TRUE;
	$mydb->commit();
	$mydb->scheme->export('./db.ser');
}

if(!file_exists($ser_file))
{
	file_put_contents($_marker_file, time());
	initialize();
}
else
{
	
	$_t_change = filemtime($_init_file); // time of last init file change
	$_t_lif = $_t_change-6;	// last time when the init script was run

	if(file_exists($_marker_file))	// if time last
	{
		$_Marker=file_get_contents($_marker_file);		
		$_t_lif = (int)	$_Marker;
	}
//	echo $_t_change.">>".$_t_lif."<br/>";
	if($_t_change>$_t_lif)
	{
		file_put_contents($_marker_file, time());
		initialize();
	}
	else
		$mydb->scheme->import($ser_file);
	//$mydb->commit();
}

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

	$ids = $mydb->scheme->insert('user',Array(
			Array('login'=>'petya','name'=>'petya','password'=>'123456'),
			Array('login'=>'valya','name'=>'Valya','password'=>'valya'),
			
	)
	)->exe();
	var_dump($ids);
	
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
	//$mydb->scheme->delete_item('user',5)->exe();
//var_dump($mydb);
?>