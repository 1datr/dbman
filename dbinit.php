<?php 
$mydb->scheme->add('user',Array(
		'login'=>'text',
		'password'=>'text',
		'name'=>Array("Type"=>'text','charset'=>'utf8'),
		'avatar'=>'/avatar',
		'#defdata'=>Array(
				Array('login'=>'root','name'=>'root','password'=>'123456'),
				Array('login'=>'vasya','name'=>'Vasya','password'=>'vasya'),
				Array('login'=>'masha','name'=>'Masha','password'=>'masha'),
				Array('login'=>'grisha','name'=>'grisha','password'=>'grisha'),
				Array('login'=>'pasha','name'=>'pasha','password'=>'pasha'),
				Array('login'=>'sasha','name'=>'sasha','password'=>'sasha'),
				Array('login'=>'dasha','name'=>'dasha','password'=>'dasha'),
		)
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
//  
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

$mydb->scheme->add('mail',Array(
		'topic'=>'text',
		'userfrom'=>'#user.id',
		'userto'=>'#user.id',
		'message'=>'memo',
		'date'=>'datetime',
));

$mydb->scheme->add('article',Array(
		'name'=>'text',
		'autor'=>'#user.id',
		'/ml:atext'=>'memo',
		'date'=>'datetime',				
	)
		
);

//var_dump($mydb->scheme);  //
//$_QDEBUG =TRUE;

/*
$mydb->scheme->insert('user',Array(
		Array('login'=>'root','name'=>'root','password'=>'123456'),
		Array('login'=>'vasya','name'=>'Vasya','password'=>'vasya'),
		Array('login'=>'masha','name'=>'Masha','password'=>'masha'),
)
)->exe();*/
?>