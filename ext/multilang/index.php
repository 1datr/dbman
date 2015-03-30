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
				echo "ML:";
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
							'lang'=>'#language.id',
							'text'=>$fldinfo,
						)
					);
				}
			}
		}
	}
	
	// event on query
	function on_before_query($args)
	{
		
	}
}
?>