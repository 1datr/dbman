<?php 
/*
 * 			MySQL database driver
 * 
 * */
class DBD_Mysql extends DBDriver
{
	var $_LINK;
	var $_PREFIX;
	// Connect to server
	function Connect($connData)
	{
		if(!is_array($connData)) $connData=Array();
		if(empty($connData['server'])) $connData['server']='localhost';
		if(empty($connData['user'])) $connData['user']='root';
		if(empty($connData['password'])) $connData['password']='';
		$this->_LINK= mysql_pconnect($connData['server'], $connData['user'], $connData['password'])
		or die("Could not connect: " . mysql_error());
		
		$this->_PREFIX=$connData['prefix'];
		mysql_select_db($connData['dbname'],$this->_LINK);
	}
	// Disconnect from db
	function Disonnect($disconnectvar=NULL)
	{
		
		
	}
	
	function CheckField($fld)
	{
		return  $fld;
	}
	// Create table
	function CreateTable($TableData)
	{ 
	
	}
	// Change table
	function ChangeTable($tblname,$TableData)
	{ 
		// show all columns of table
		$res = $this->exe_query("SHOW COLUMNS FROM ".$this->_PREFIX.$tblname);
		$fields = Array();
		$fildinfo = Array();
		// walk all columns
		while($row = mysql_fetch_array($res))
		{
			
			if(empty($TableData->_FIELDS[$row[0]]))	// no this columns in scheme
			{
				if($row[0]!='id')
					$this->exe_query("ALTER TABLE `".$this->_PREFIX.$tblname."` DROP ".$row[0]);
			}
			else 
			{
				// Watch the difference between field image and the real field 
				$fldinfo = $TableData->_FIELDS[$row[0]];
				//var_dump($TableData->_FIELDS[$row[0]]);
				if(!empty($TableData->_FIELDS[$row[0]]['bind']))
				{
					//	echo ">>";
					//var_dump($TableData->_FIELDS);
					
					$this->_BINDINGS[]=Array(
							'tblname'=>$tblname,
							'field'=>$row[0],
							'bind_data'=>&$TableData->_FIELDS[$row[0]]['bind'],
					);
					
					
				}
			/*	
			 * ALTER TABLE  `tdb_user` CHANGE  `name`  `name` TEXT CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL ;
			 * 
			 * */
				$str_collate = "";
				if($fldinfo['charset']!='')
				{
					$str_collate = " CHARACTER SET ".$fldinfo['charset']." COLLATE ".$fldinfo['sub_charset']." ";
				
				}
				
		/*		if(($row['Type']!=$fldinfo['Type'])||($row['Null']!=$fldinfo['Null']))
				{*/
				//	$fldinfo = $this->normalized_field($fld);
					$nullstr = ($fldinfo['Null']=="NO") ? "NOT NULL" : "NULL";
					if($fldinfo['Default']==NULL)
						$fldstr = "`".$row[0]."` ".$fldinfo['Type']." $str_collate  ".$nullstr;
					else
						$fldstr = "`".$row[0]."` ".$fldinfo['Type']." $str_collate  Default ".$fldinfo['Default']." ".$nullstr;
					$_sql = "ALTER TABLE  `".$this->_PREFIX.$tblname."` CHANGE  `".$row[0]."`  $fldstr ";
					global $_DEBUG;
					if($_DEBUG)
						echo $_sql."\n";
					$this->exe_query($_sql);
			//	}
				
				
			}
			$fields[]=$row[0];
			
		}
		
		foreach($TableData->_FIELDS as $fldname => $fldimage)
		{
			
			
			if(!in_array($fldname, $fields))
			{
				$fldinfo = $this->CheckField($fldimage);
				$nullstr = ($fldinfo['Null']=="NO") ? "NOT NULL" : "NULL";
				if($fldinfo['Default']==NULL)
					$sql = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD `$fldname` ".$fldinfo['Type']." $nullstr";
				else
					$sql = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD `$fldname` ".$fldinfo['Type']." Default ".$fldinfo['Default']." $nullstr";
				global $_DEBUG;
				if($_DEBUG)
						echo $sql."\n";				//var_dump($sql);
				$this->exe_query($sql);
			}
		}
		//var_dump($fields);
	}
	// Delete table
	function DeleteTable($table)
	{ 
	
		$this->exe_query("DROP TABLE `".$this->_PREFIX.$table."` CASCADE");
	}
	
	// Select queries
	function Select($selectdata)
	{
		
	}
	
	function existsTable($table) {
		$res = $this->exe_query("SELECT * FROM `".$this->_PREFIX.$table."` LIMIT 1",FALSE);
		$err_no = mysql_errno();
		return ($err_no != '1146' && $res = true);
	}
	
	function MakeFieldStr($fld_array)
	{
		//return $fld_array['type']
	}
	
	function create_binding($tblname,$field,$bind_data)
	{
		if(empty($bind_data['on_delete']))
			$bind_data['on_delete']='CASCADE';
		if(empty($bind_data['on_update']))
			$bind_data['on_update']='RESTRICT';
		$query = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD FOREIGN KEY ( `$field` ) REFERENCES `".$this->_PREFIX.$tblname."` (`".$bind_data['field_to']."`) ON DELETE ".$bind_data['on_delete']." ON UPDATE ".$bind_data['on_update']."";
		global $_DEBUG;
		if($_DEBUG)
			echo $query;
		$this->exe_query($query);
	}
	// List of tavble in database now
	function TableList()
	{
		$res = $this->exe_query("SHOW TABLES");
		$arr = Array();
		while($row = mysql_fetch_array($res))
			$arr[]= substr($row[0],strlen($this->_PREFIX));
		return $arr;
	}
	
	function exe_query($q,$exept=true)
	{
		$res = mysql_query($q,$this->_LINK);
		if($exept)
		{
			if($res==FALSE)
				throw new Exception("Bad query result [$q] ");
		}
		return $res;
	}
	// get row of result
	function res_row($res)
	{
		return mysql_fetch_array($res,MYSQL_ASSOC);
	}
	// get associative array of normalized fields
	
	// query select
	function q_select($select_params)
	{
		// select [t1.f1, t2,f2 ...]
		function make_select_str($selects,$_PREFIX='')
		{
			$str_select="";
			$i=0;
			foreach ($selects as $selkey => $selitem)
			{
				/*echo ">>\n";
				var_dump($selitem);*/
				if($i)
					$str_select = $str_select.", $_PREFIX{$selitem['table']}.{$selitem['fld']} as $selkey";
				else
					$str_select = $str_select."$_PREFIX{$selitem['table']}.{$selitem['fld']} as $selkey";
				$i++;
			}
			return $str_select;
		}
		// join  t2 on t1.f1=t2.f2
		function make_join_str($joins,&$select_params,$_PREFIX='')
		{
			$str_join = "";
			$tbl_last = $select_params['table'];
			foreach ($joins as $jkey => $jval )
			{
			
				if($jval['to']['table']==$jkey)
					$str_join = $str_join." {$jval['jtype']} join 
	$_PREFIX$jkey on 
	$_PREFIX{$jval['from']['table']}.{$jval['from']['field']}=".$_PREFIX.$jkey.'.'.$jval['to']['field']." ";
				else 
					$str_join = $str_join." {$jval['jtype']} join 
	$_PREFIX{$jval['to']['table']} as $_PREFIX$jkey on 
	$_PREFIX{$jval['from']['table']}.{$jval['from']['field']}=$_PREFIX.$jkey.{$jval['to']['field']} ";
				//$tbl_last = $jval['jtable'];
			}
				
			//$str_from = $_PREFIX.$select_params['table'].$str_join;
			return $str_join; 
		}
		// add field to selects list
		
		
		$str_select = "*";
		$str_where=1;
		$limit="";
		
		$select_items = Array();
		$joins = Array();
		
		if(!empty($select_params['join']))
		{
			foreach ($select_params['join'] as $jkey => $j)
			{
				$joins[$jkey]=$j;				
			}
			
		}
		
		$_limit = "";
		if(!empty($select_params['limit']))
		{
			$l1 = ($select_params['limit']['page']-1) * $select_params['limit']['size'];
			$_limit = "$l1,".$select_params['limit']['size'];
		}
		
		
		$sql = "SELECT ".make_select_str($select_params['select'],$this->_PREFIX)." FROM ".$this->_PREFIX.$select_params['table']." ".
				make_join_str($select_params['joins'],$select_params,$this->_PREFIX)." WHERE ".$select_params['where']." ".$_limit ;
		//echo $sql;
		return $sql;

	}
	

	// query delete
	function q_delete($del_params)
	{
		if(empty($del_params['where']))
			$del_params['where']=1;
		$sql = "DELETE FROM `{$this->_PREFIX}{$del_params['table']}` WHERE ".$del_params['where'];
		
		return $sql;
	
	}
	// query select
	function q_delete_item($params)
	{			
		$sql = "DELETE FROM `{$this->_PREFIX}{$params['table']}` WHERE id=".$params['id'];
		
		return $sql;
	}
	// query add
	function q_add($add_data)
	{		
	//	var_dump($add_data);
		$sql1 = "INSERT INTO `{$this->_PREFIX}{$add_data['table']}` (";
		$sql2 = ") VALUES ";
		$j = 0;
		foreach($add_data['data'] as $k => $v)
		{
			if($j==0)
				$ins = " (NULL";
			else 
				$ins = ", (NULL";
			$i=0;
			if($j==0)
				$fldstr = "`id`";
			foreach($v as $fld => $val)
			{
				if($j==0)
				{
					$fldstr = "$fldstr, `$fld`";
				}
					
				if($val[0]=='@')
					{
					$val= substr($val,1);
					$ins .= ", $val";
					}
				else
					$ins .= ", '$val'";
				$i++;
			}
			$ins = "$ins)";
			$sql2 = "$sql2 $ins";
			$j++;
		}
		
		return $sql1.$fldstr.$sql2;
	}
	// query update
	function q_update($upd_data)
	{
		$sql = "UPDATE {$this->_PREFIX}{$upd_data['table']} SET ";
		$i = 0;
		foreach($upd_data as $col => $val)
		{
			if($val[0]=='@')
			{
				$val = sustr($val,1);
				$newelement = "`$col`=$val";
			}
			else 
				$newelement = "`$col`='$val'";
			
			if($i)
				$sql .= ", $newelement";
			else 
				$sql .= $newelement;
			$i++;
		}
		
		return  $sql;
	}
	// Commit all bindings
	VAR $_BINDINGS = Array();
	function CommitBindings()
	{
		//var_dump($this->_BINDINGS);
		foreach($this->_BINDINGS as $b)
		{
		/*	echo ">>";
			var_dump($b);*/
			$this->create_binding($b['tblname'], $b['field'], $b['bind_data']);
		}
	}
	
	// Commit data table
	function CommitTable($tblname,$TableData)
	{
		//var_dump($TableData);
		if($this->existsTable($tblname))
		{
			// Change table structure
			
			$this->ChangeTable($tblname,$TableData);
		}
		else 
		{
		//	var_dump($TableData->_FIELDS);
			//CREATE TABLE
			$sql = "CREATE TABLE `".$this->_PREFIX.$tblname."` (
		`id` BIGINT(20) NOT NULL AUTO_INCREMENT,\n";
			foreach ($TableData->_FIELDS as $name => $fld)
			{
				$fldinfo = $this->CheckField($fld);
				$nullstr = ($fldinfo['Null']=="NO") ? "Not null" : "Null";
				if($fldinfo['Default']==NULL)
					$fldstr = "`$name` ".$fldinfo['Type']." ".$nullstr;
				else
					$fldstr = "`$name` ".$fldinfo['Type']." Default ".$fldinfo['Default']." ".$nullstr;
				
				$sql = $sql."$fldstr,\n";
			
			}
			$sql = $sql."
	PRIMARY KEY(`id`)
	)";
			global $_DEBUG;
			if($_DEBUG)
				echo  $sql;
			
			$this->exe_query($sql);
			
			foreach ($TableData->_FIELDS as $name => $fld)
			{
				if(!empty($fld['bind']))
					$this->_BINDINGS[]=Array(
							'tblname'=>$tblname, 
							'field'=>$name, 
							'bind_data'=>$fld['bind'],
							);
					//$this->create_binding($tblname, $name, $fldinfo['bind']);
			}
			
			
			
			//Set the default values
			foreach ($fldinfo['defdata'] as $d)
			{
				
			}
		}
	}
	
	
}
?>