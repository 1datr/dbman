<?php 
// Р Р°СЃС€РёСЂРµРЅРёРµ dbman
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
								Array('short'=>'ru','full'=>'Р СѓСЃСЃРєРёР№'),
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
	// make language cache
	VAR $_LANG=Array();
	function make_lang_table($args)
	{
		//$this->_LANG = Array();
		if(count($this->_LANG)) return ;
		$_args = $args['scheme']->get_current_args(); // get the current args
		$_res = $args['scheme']->select('language',Array('id','short'))->exe();
		while($row = $args['scheme']->res_row($_res))
		{
			//var_dump($row);
			$this->_LANG[$row['short']]=$row['id'];
		}
		$args['scheme']->set_args($_args); // set the saved args
	}
	
	// after update 
	function aq_on_update($args)
	{
		$this->make_lang_table($args);
		 //var_dump($row);
		$id=$row['id'];
		foreach ($this->_UPDATED_ROWS as $_updrow)
		{		
			foreach($args['scheme']->_UPDATE_ARGS['data'] as $key => $val)
			{
				$matches = Array();
				// \ml:field
				if(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$key,$matches))
				{					
					$fldname = $matches[1][0];
					$lang_descriptor = $matches[2][0];
					$tblname = $args['scheme']->_UPDATE_ARGS['table']."_$fldname";
						
					$_args = $args['scheme']->get_current_args(); // get the current args
					
					$lang_id = $this->_LANG[$lang_descriptor];
					
					$_res = $args['scheme']->select($tblname,Array('*'))
->where("@@$tblname.recid={$_updrow['id']} AND @@$tblname.lang=$lang_id")
/*
->op(Array('table'=>$tblname,'field'=>'recid'),$_updrow['id'],'=')				
->op(Array('table'=>$tblname,'field'=>'lang'),$lang_id,'=')	*/				
->exe();
					if($args['scheme']->result_count($_res))
					{
						$args['scheme']->update($tblname,Array('text'=>$val))
->where("@@$tblname.recid={$_updrow['id']} AND @@$tblname.lang=$lang_id")
/*->op(Array('table'=>$tblname,'field'=>'recid'),$_updrow['id'],'=')				
->op(Array('table'=>$tblname,'field'=>'lang'),$lang_id,'=')*/
->exe();
					}
					else 
					{
						$args['scheme']->insert($tblname,Array(
								'recid'=>$args['qresult'][$idx],
								'lang'=>$lang_id,
								'text'=>$val,
						)
						)->exe();
					}
					
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
	}
	// after add query
	function aq_on_add($args)
	{
		global $_CURR_LANGUAGE;
		$this->make_lang_table($args);
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
								'lang'=>$this->_LANG[$lang_descriptor],
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
							'lang'=>$this->_LANG[$_CURR_LANGUAGE],
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
	
	VAR $_UPDATED_ROWS=Array();
	
	function on_update(&$args)
	{
		// Запоминаем данные которые будут изменены
		global $_CURR_LANGUAGE;
		$_args = $args['scheme']->get_current_args(); // get the current args
		$_res = $args['scheme']->select($_args['_UPDATE_ARGS']['table'],'*'
				//'user|id<groupmember:group',
				)->where($_args['_UPDATE_ARGS']['where'])->exe(
						//'q1'
				);
		$args['scheme']->set_args($_args); // set the saved args
		$this->_UPDATED_ROWS=Array();
		while($row=$args['scheme']->res_row($_res))
			{
				$this->_UPDATED_ROWS[]=$row;
			}
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