<?php 

class sqlpreprocessor {
	
		function setscheme(&$sch)
		{
			$this->_scheme = $sch;
		}
		
		function chain_merge($chnew,&$chbuf)
		{
			if(count($chbuf)==0)
			{
				$chbuf[]=$chnew;
				return;
			}
			
			$found = true;
			while (list($key, $val) = each($chbuf)) {
				
				$itemequal = true;
				foreach($val as $k => $v)
				{
					$itemequal = $itemequal && ($chnew[$k]==$val[$k]);
				}
				$found = $found && $itemequal;
			}
			if(!$found)
				$chbuf[]=$chnew;
		}
	
		function preprocess_select($args)
		{
			$newargs = $args;
			
			if(empty($newargs['where']))
				$newargs['where'] = '1';
			//var_dump($newargs);
			
			$newargs['select'] = Array();
			$chains = Array();
			foreach($args['select'] as $selitem)
			{
				
				$chain = $this->chain_field($selitem,$args['table'],$newargs);

				
			/*	while (list($key, $val) = each($chain)) 
				{
					$this->chain_merge($val,$chains);
				}
				*/
			
			/*	
				if(is_array($selitem)) // field defined as array
				{
					$this->process_autojoin($selitem,$newargs,true);
				}
				else
				{
					$arr = explode('|', $selitem);
						
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
				}*/
			}
			
			return $newargs;
		}
		// add field
		function addfield($fld,$table,&$ref_selects,$fldname=NULL)
		{
			if($fldname!=NULL)
			{
				$fld_key = $fldname;
			}
			else 
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
			}
			$ref_selects[$fld_key]=Array(
					'table'=>$table,
					'fld'=>$fld,
			);
		}
		
		VAR $_scheme;
		// add new join
		function add_join($join,&$jkey,&$select_params)
		{
			if(empty($select_params['joins']))
				$select_params['joins'] = Array();
			$found = false;
			foreach($select_params['joins'] as $jk => $j)
			{
				if( ($j['jtype']==$join['jtype'])&&
					($j['from']['table']==$join['from']['table'])&&
					($j['from']['field']==$join['from']['field'])&&
					($j['to']['table']==$join['to']['table'])&&
					($j['to']['field']==$join['to']['field']))
				{
					$found = true;
					$jkey = $jk;
					return;
				}
			}
			
			$select_params['joins'][$jkey]=$join;
		}
		
		/*	
					$newj = Array(
							'jtype'=>$jtype,
							'jtable_to'=>$thetable,
							'jto'=>$_table->_FIELDS[$fld]['bind']['field_to'],
							'jfrom'=>$fld,
							'jtable_from'=>$table_last,
					);
		*/
		//	echo "\n>>\n$_thetable";
			
		// 
		function chain_field($str,$table,&$selects)
		{
			
			$_AS = NULL;	// field AS ...
			$nullable = false;
			$jtype = 'left';
			if(is_array($str))	// field as array
			{
				$arr = $str;
				if($arr[0])
				{
					$nullable = true;
					$jtype = 'inner';
					unset($arr[0]);
				}
			}
			else
			{
				if($str[0]=='!')	// Жесткое соответствие
				{
					$nullable = true;
					$jtype = 'inner';
					$str = substr($str,1);
				}
				// detect as option
				$expl = explode('as',$str);
				if(count($expl)>1)
				{
					$_AS = ltrim(rtrim($expl[1]));
					$str = ltrim(rtrim($expl[0]));
				}
				$arr = explode('|',$str);
			}
		//	$chain = Array();
			$_table = $table;
			$i=0;
			$_table_last = $_table;
			foreach($arr as $element)
			{
				$pieces = Array();
				$res = preg_match_all('/(.+)\<(.+)\:(.+)/',$element,$pieces);
			//	var_dump($pieces);
				if($res==0)
				{
					$z = Array(
							'field'=>$element,
							'table'=>$_table,
							'nullable'=>$nullable,
							);
					$chain[] = $z;
				/*	echo ">>";
					var_dump($this->_scheme[$_table]->_FIELDS[$element]);*/
					$_thetable = $_table;
					if(!empty($this->_scheme[$_table]->_FIELDS[$element]['bind']))
					{
						$newj = Array(
								'jtype'=>$jtype,
								'from'=>Array(
									'table'=>$_table,
									'field'=>$element,
								),
								'to'=>Array(
									'table'=>$this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'],
									'field'=>$this->_scheme[$_table]->_FIELDS[$element]['bind']['field_to'],
								),			
						);
						$this->add_join($newj,$this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'],$selects);
						//var_dump($newj);
					}
					if($i==count($arr)-1) // ending element
					{
							
						$this->addfield($element,$_thetable,$selects['select'],$_AS);
						return $chain;
					}
					if(empty($this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to']))
					{
						return null;
					}
					$_table = $this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'];
				}
				else 
				{
					
					$z1 = Array(
							'field'=>$pieces[1][0],	
							'table'=>$_table,
							'nullable'=>$nullable,
							);
					$chain[] = $z1;
					$_table = $pieces[2][0];
					$z2 = Array(
							'field'=>$pieces[3][0],
							'table'=>$_table,
							'nullable'=>$nullable,
					);
					$chain[] = $z2;
					
					/*
					$newj = Array(
							'jtype'=>$jtype,
							'from'=>Array(
									'table'=>$_table,
									'field'=>$element,
							),
							'to'=>Array(
									'table'=>$this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'],
									'field'=>$this->_scheme[$_table]->_FIELDS[$element]['bind']['field_to'],
							),
					);
					$this->add_join($newj,$this->_scheme[$_table]->_FIELDS[$element]['bind']['table_to'],$selects);
					
				*/
					if($i==count($arr)-1) // ending element
					{
							
						$this->addfield($pieces[3][0],$_table,$selects['select'],$_AS);
						return $chain;
					}
					else
						return null;
				}
				$i++;
				$_table_last = $_table;
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