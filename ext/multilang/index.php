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
			if(preg_match_all("|[\\/]{0,1}ml\:(.+)|",$val,$matches))
			{
				$fldname = $matches[1][0];
				$tblname = $args['args']['table']."_$fldname";
				// delete the field
				$args['scheme']->join(Array(
						'from'=>Array('table'=>$args['args']['table'], 'field'=>'id'),
						'to'=>Array('table'=>$tblname, 'field'=>'recid')
						)
				);
				unset($args['args']['select'][$idx]);
			}
			// \ml:field[ru]
			elseif(preg_match_all("|[\\/]{0,1}ml\:(.+)\[(.+)\]|",$val,$matches))
			{
				
				// delete the field
				unset($args['args']['select'][$idx]);
			}
			
		}
	}
	
	function on_update(&$args)
	{
		global $_CURR_LANGUAGE;
		foreach($args['args'] as $idx => $val)
		{
				
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