<?php 
// Расширение dbman
class DBMExtMultilang extends DBMExtention{
	// event before adding new table
	function on_before_add_table($args)
	{
		
		foreach ($args['fields'] as $fld => $fldinfo)
		{
			$matches = Array();
			if(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$fld,$matches))
			{				
				$own_fld_name=$matches[1][0];
				// add languages table if not exists
				if(!$args['scheme']->obj_exist('language'))
				{
					$args['scheme']->add('language',Array(
							'short'=>'text',
							'full'=>'text',
							'#defdata'=>Array(
								Array('short'=>'ru','full'=>'Русский'),
								Array('short'=>'en','full'=>'English'),
									)
					)
					);
				}
				// add translation table if not exists
				$mltable_name = $args['table']."_".$own_fld_name."";
				if(!$args['scheme']->obj_exist($mltable_name))
				{
					$args['scheme']->add($mltable_name,Array(
							'recid'=>"#".$args['table'].".id",
							'lang'=>'#language.id',
							'text'=>$fldinfo,
						)
					);
				}
				
				if(is_string($args['fields'][$fld])) 
					$args['fields'][$fld]="/".$args['fields'][$fld];
				elseif(is_array($args['fields'][$fld]))
					$args['fields'][$fld]['virtual']=true;
			}
		}
	}
	
	// event on query
	function on_before_query($args)
	{
		switch ($args['qmode'])
		{
			case 'select':
					$this->on_select($args);
				break;
			case 'update': 
					$this->on_update($args);
				break;
			case 'add': 
					$this->on_add($args);
				break;
			case 'delete': 
					$this->on_delete($args);
				break;
			case 'delitem': 
					$this->on_delitem($args);
				break;
		}
	}
	
	function on_select(&$args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['args']['select'] as $idx => $val)
		{
			$matches = Array();
			// \ml:field
			if(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$val,$matches))
			{
				//	var_dump($matches);
				
					$fldname = $matches[1][0];
					$tblname = $args['args']['table']."_$fldname";
					// delete the field
					$args['scheme']->join(Array(
							'from'=>Array('table'=>$args['args']['table'], 'field'=>'id'),
							'to'=>Array('table'=>$tblname, 'field'=>'recid')
					)
					);
					$args['scheme']->join(Array(
							'from'=>Array('table'=>$tblname, 'field'=>'lang'),
							'to'=>Array('table'=>'language', 'field'=>'id')
					)
					);
					$args['scheme']->op(Array('table'=>'language','field'=>'short'),$matches[2][0],'=');
					unset($args['scheme']->_SELECT_ARGS['select'][$idx]);
					$args['scheme']->_SELECT_ARGS['select'][]='$'.$args['scheme']->_DRV->_PREFIX."$tblname.text";
					// delete the field
					unset($args['args']['select'][$idx]);
			}				
			// \ml:field[ru]
			elseif(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$val,$matches))
			{
				$fldname = $matches[1][0];
				$tblname = $args['args']['table']."_$fldname";
				// delete the field
				$args['scheme']->join(Array(
						'from'=>Array('table'=>$args['args']['table'], 'field'=>'id'),
						'to'=>Array('table'=>$tblname, 'field'=>'recid')
				)
				);
				$args['scheme']->join(Array(
						'from'=>Array('table'=>$tblname, 'field'=>'lang'),
						'to'=>Array('table'=>'language', 'field'=>'id')
				)
				);
				$args['scheme']->op(Array('table'=>'language','field'=>'short'),$_CURR_LANGUAGE,'=');
				unset($args['scheme']->_SELECT_ARGS['select'][$idx]);
				$args['scheme']->_SELECT_ARGS['select'][]='$'.$args['scheme']->_DRV->_PREFIX."$tblname.text";
			}
		}
	}
	// event after query
	function on_after_query(&$args)
	{
		switch ($args['qmode'])
		{
			case 'select':
				$this->aq_on_select($args);
				break;
			case 'update':
				$this->aq_on_update($args);
				break;
			case 'add':
				$this->aq_on_add($args);
				break;
			case 'delete':
				$this->aq_on_delete($args);
				break;
			case 'delitem':
				$this->aq_on_delitem($args);
				break;
		}
	}
	// after select query
	function aq_on_select($args)
	{
	
	}
	// after update 
	function aq_on_update($args)
	{
		
	}
	// after add query
	function aq_on_add($args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['scheme']->_ADD_ARGS['data'] as $idx => $arr)
		{
			foreach ($arr as $key => $val)
			{
				$matches = Array();
				// \ml:field
				if(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$key,$matches))
				{
					//	var_dump($matches);
			
					$fldname = $matches[1][0];
					$lang_descriptor = $matches[2][0];
					$tblname = $args['scheme']->_ADD_ARGS['table']."_$fldname";
					
					$_args = $args['scheme']->get_current_args(); // get the current args
					$args['scheme']->insert($tblname,Array(
								'recid'=>$args['qresult'][$idx],
								'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$lang_descriptor'")->exeq()->getfield(0,'id'),
								'text'=>$val,
								)
							)->exe();
					//
					$args['scheme']->set_args($_args); // set the saved args
					
					//unset($args['args']['select'][$idx]);
				}
				// \ml:field[ru]
				elseif(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$key,$matches))
				{
					$fldname = $matches[1][0];
					$lang_descriptor = $matches[2][0];
					$tblname = $args['scheme']->_ADD_ARGS['table']."_$fldname";
						
					$args['scheme']->insert($tblname,Array(
							'recid'=>$args['qresult'][$idx],
							'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$_CURR_LANGUAGE'")->exeq()->getfield(0,'id'),
							'text'=>$val,
					)
					)->exe();
				}
			}
		}
	}
	// on delete item
	function aq_on_delitem($args)
	{
		
	}
	
	function on_update(&$args)
	{
	global $_CURR_LANGUAGE;
	$_args = $args['scheme']->get_current_args(); // get the current args
	$_res = $args['scheme']->select($_args['_UPDATE_ARGS']['table'],'*'
			//'user|id<groupmember:group',
			)->where($_args['_UPDATE_ARGS']['where'])->exe(
					//'q1'
			);
	$args['scheme']->set_args($_args); // set the saved args
	while($row=$args['scheme']->res_row($_res))
	{
		//var_dump($row);
		$id=$row['id'];
		foreach($args['scheme']->_UPDATE_ARGS['data'] as $key => $val)
		{
			$matches = Array();
			// \ml:field
			if(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$key,$matches))
			{
				//	var_dump($matches);
			
				$fldname = $matches[1][0];
				$lang_descriptor = $matches[2][0];
				$tblname = $args['scheme']->_UPDATE_ARGS['table']."_$fldname";
			
				$_args = $args['scheme']->get_current_args(); // get the current args
				$args['scheme']->insert($tblname,Array(
						'recid'=>$args['qresult'][$idx],
						'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$lang_descriptor'")->exeq()->getfield(0,'id'),
						'text'=>$val,
				)
				)->exe();
				//
				$args['scheme']->set_args($_args); // set the saved args
			
				//unset($args['args']['select'][$idx]);
			}
			// \ml:field[ru]
			elseif(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$key,$matches))
			{
				$fldname = $matches[1][0];
				$lang_descriptor = $matches[2][0];
				$tblname = $args['scheme']->_UPDATE_ARGS['table']."_$fldname";
			
				$args['scheme']->insert($tblname,Array(
						'recid'=>$args['qresult'][$idx],
						'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$_CURR_LANGUAGE'")->exeq()->getfield(0,'id'),
						'text'=>$val,
				)
				)->exe();
			}
		}
	}
	//

	/*
		foreach($args['scheme']->_UPDATE_ARGS['data'] as $key => $val)
		{
				$matches = Array();
				// \ml:field
				if(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$key,$matches))
				{
					//	var_dump($matches);
						
					$fldname = $matches[1][0];
					$lang_descriptor = $matches[2][0];
					$tblname = $args['scheme']->_UPDATE_ARGS['table']."_$fldname";
						
					$_args = $args['scheme']->get_current_args(); // get the current args
					$args['scheme']->insert($tblname,Array(
							'recid'=>$args['qresult'][$idx],
							'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$lang_descriptor'")->exeq()->getfield(0,'id'),
							'text'=>$val,
					)
					)->exe();
					//
					$args['scheme']->set_args($_args); // set the saved args
						
					//unset($args['args']['select'][$idx]);
				}
				// \ml:field[ru]
				elseif(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$key,$matches))
				{
					$fldname = $matches[1][0];
					$lang_descriptor = $matches[2][0];
					$tblname = $args['scheme']->_UPDATE_ARGS['table']."_$fldname";
		
					$args['scheme']->insert($tblname,Array(
							'recid'=>$args['qresult'][$idx],
							'lang'=>$args['scheme']->select('language',Array('id'))->where("short='$_CURR_LANGUAGE'")->exeq()->getfield(0,'id'),
							'text'=>$val,
					)
					)->exe();
				}
		}*/
	}
	
	function on_add(&$args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['args'] as $idx => $val)
		{
				
		}
	}
	
	function on_delete(&$args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['args'] as $idx => $val)
		{
				
		}
	}
	
	function on_delitem(&$args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['args'] as $idx => $val)
		{
				
		}
	}	
}
?>