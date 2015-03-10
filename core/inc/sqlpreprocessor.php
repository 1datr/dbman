<?php 

class sqlpreprocessor {
	
		function setscheme(&$sch)
		{
			$this->_scheme = $sch;
		}
	
		function preprocess_select($args)
		{
			$newargs = $args;
			
			if(empty($newargs['where']))
				$newargs['where'] = '1';
			//var_dump($newargs);
			
			$newargs['select'] = Array();
			foreach($args['select'] as $selitem)
			{
			/*	echo ">>\n";
				var_dump($newargs['select']);*/
				if(is_array($selitem)) // field defined as array
				{
					$this->process_autojoin($selitem,$newargs,true);
				}
				else
				{
					$arr = explode('|', $selitem);
					/*echo "::>";*/
						$this->chain_array($selitem,$args['table']);
					if(count($arr)>1)
					{
						$null = true;
						if($arr[0][0]=='!')
						{
							$arr[0]=substr($arr[0],1);
							$null=false;
						}
							
						$this->process_autojoin($arr,$newargs,$null);
					}
					else
						$this->addfield($selitem,$args['table'],$newargs['select']);
				}
			}
			
			return $newargs;
		}
		// add field
		function addfield($fld,$table,&$ref_selects)
		{
			$fld_key = $fld;
			if(!empty($ref_selects[$fld_key]))
			{
				$fld_key = "{$table}_$fld";
				$j=1;
				while(!empty($ref_selects[$fld_key]))
				{
					$fld_key=$fld_key.$j;
					$j++;
				}
			}
			$ref_selects[$fld_key]=Array(
					'table'=>$table,
					'fld'=>$fld,
			);
		}
		
		VAR $_scheme;
		// process autojoin items
		function process_autojoin($arr,&$select_params,$null)
		{
			$thetable = $select_params['table'];
			$table_last = $select_params['table'];
			$_thetable = "";
			foreach($arr as $fld)
			{
				
				$_table = $this->_scheme[$thetable];
				//var_dump($_table->_FIELDS[$fld]);
				if(!empty($_table->_FIELDS[$fld]['bind']))
				{
					
					$thetable = $_table->_FIELDS[$fld]['bind']['table_to'];
					if($null) $jtype="left"; else $jtype="inner";
						
					$newj = Array(
							'jtype'=>$jtype,
							'jtable_to'=>$thetable,
							'jto'=>$_table->_FIELDS[$fld]['bind']['field_to'],
							'jfrom'=>$fld,
							'jtable_from'=>$table_last,
					);
					if(empty($select_params['joins'][$thetable]))
						$select_params['joins'][$thetable]=$newj;
					else
					{
						if(($ref_joins['jtable_to']!=$newj['jtable_to'])||($ref_joins['jtype']!=$newj['jtype']))
						{
								
						}
					}
						
					if(empty($_table->_FIELDS[$fld]['bind']))
						continue;
					$thetable = $_table->_FIELDS[$fld]['bind'];
					$table_last = $thetable;
					
					$_thetable = $_table->_FIELDS[$fld]['bind']['table_to'];
				}
				
					
			}
		//	echo "\n>>\n$_thetable";
			// add selection
			$this->addfield($fld,$thetable,$select_params['select']);
				
		}
		// 
		function chain_array($str,$table)
		{
			$arr = explode('|',$str);
		//	$chain = Array();
			$_table = $table;
			foreach($arr as $element)
			{
				$pieces = sscanf($element, "%s<%s:%s");
				if(($pieces[1]=="") || ($pieces[2]==""))
				{
					$z = Array(
							'field'=>$element,
							'table'=>$_table,
							);
					$chain[] = $z;
					echo ">>";
					var_dump($this->_scheme[$_table]->_FIELDS[$element]);
					if(empty($this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to']))
						return $chain;
					$_table = $this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'];
				}
				else 
				{
					$z1 = Array(
							'field'=>$pieces[0],	
							'table'=>$_table,
							);
					$chain[] = $z1;
					$_table = $pieces[1];
					$z2 = Array(
							'field'=>$pieces[2],
							'table'=>$pieces[1],
					);
					$chain[] = $z2;
				}
				//echo "::>";
				//var_dump($pieces);
			}	
			return  $chain;		
		}
		
		function getchain($arr,$idx)
		{
			$chain = Array();
			
			$i=0;
			$tablename = $table;
			
			
			
			foreach($arr as $fld)
			{
				$newz = Array(
					'table'=>$tablename,
						''=>''
				);
				$chain[]=$newz;
				
				$i++;
				$tablename = $this->_scheme[$thetable]->_FIELDS[$fld]['bind']['table_to'];
			}
			
		/*	$chain = Array();
			foreach ($arr as $fld)
			{
				if(!empty($this->_scheme[$thetable]->_FIELDS[$fld]['bind']['field_to']))
				{
					
				}
			}	*/		
		}
}
?>		