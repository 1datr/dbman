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
		// preprocess where parameters 
		function preprocess_where(&$scheme)
		{
			if(count($this->_WHEREBUF)>0)
			{
				
			}
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
				
			}
						
		//	var_dump($newargs);
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
		
		function preprocess_addjoin($join,&$args)
		{
			
			if(empty($args['joins']))
				$args['joins'] = Array();
			$found = false;
			foreach($args['joins'] as $jk => $j)
			{
				if( ($j['jtype']==$join['jtype'])&&
				($j['from']['table']==$join['from']['table'])&&
				($j['from']['field']==$join['from']['field'])&&
				($j['to']['table']==$join['to']['table'])&&
				($j['to']['field']==$join['to']['field']))
				{
					$found = true;
				
					return;
				}
			}
				
			$args['joins'][$join['to']['table']]=$join;
		}
		
		function preprocess_add(&$_ARGS)
		{
			return $_ARGS;
		}
		
		function preprocess_update(&$_ARGS)
		{
			return $_ARGS;
		}
		
		function preprocess_delete(&$_ARGS)
		{
			return $_ARGS;
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
		
		// 
		function chain_field($str,$table,&$selects)
		{
			if($str[0]=='$') // �������� ��������
			{
				$str = substr($str,1);
				$selects["select"][]=$str;	
				echo "<<}}{{>>";
				var_dump($selects);	
				return ;
			}
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
					
					$z2 = Array(
							'field'=>$pieces[3][0],
							'table'=>$pieces[2][0],
							'nullable'=>$nullable,
					);
					$chain[] = $z2;
					
					// Insert new join
					$table_to = $pieces[2][0];
					$newj = Array(
							'jtype'=>$jtype,
							'from'=>Array(
									'table'=>$_table,
									'field'=>$pieces[1][0],
							),
							'to'=>Array(
									'table'=>$table_to,
								//	'field'=>$this->_scheme[$table_to]->_FIELDS[],
							),
					);
					$_table = $pieces[2][0];
					$this->add_join($newj,$_table,$selects);
					
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