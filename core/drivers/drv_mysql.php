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
		$res = mysql_query("SHOW COLUMNS FROM ".$this->_PREFIX.$tblname,$this->_LINK);
		$fields = Array();
		$fildinfo = Array();
		// walk all columns
		while($row = mysql_fetch_array($res))
		{
			
			if(empty($TableData->_FIELDS[$row[0]]))	// no this columns in scheme
			{
				if($row[0]!='id')
					mysql_query("ALTER TABLE `".$this->_PREFIX.$tblname."` DROP ".$row[0],$this->_LINK);
			}
			else 
			{
				// Watch the difference between field image and the real field 
				$fldinfo = $this->CheckField($TableData->_FIELDS[$row[0]]);
				if(!empty($fldinfo['bind']))
					$this->create_binding($tblname,$row[0], $fldinfo['bind']);
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
					mysql_query($_sql);
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
				mysql_query($sql,$this->_LINK);
			}
		}
		//var_dump($fields);
	}
	// Delete table
	function DeleteTable($table)
	{ 
	
		mysql_query("DROP TABLE `".$this->_PREFIX.$table."` CASCADE",$this->_LINK);
	}
	
	// Select queries
	function Select($selectdata)
	{
		
	}
	
	function existsTable($table) {
		$res = mysql_query("SELECT * FROM `".$this->_PREFIX.$table."` LIMIT 1",$this->_LINK);
		$err_no = mysql_errno();
		return ($err_no != '1146' && $res = true);
	}
	
	function MakeFieldStr($fld_array)
	{
		//return $fld_array['type']
	}
	
	function create_binding($tblname,$field,$bind_data)
	{
		$query = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD FOREIGN KEY ( `$field` ) REFERENCES `".$bind_data['table_to']."` (`".$bind_data['field_to']."`) ON DELETE ".$bind_data['on_delete']." ON UPDATE ".$bind_data['on_update'].";";
		global $_DEBUG;
		if($_DEBUG)
			echo $query;
		mysql_query($query,$this->_LINK);
	}
	// List of tavble in database now
	function TableList()
	{
		$res = mysql_query("SHOW TABLES",$this->_LINK);
		$arr = Array();
		while($row = mysql_fetch_array($res))
			$arr[]= substr($row[0],strlen($this->_PREFIX));
		return $arr;
	}
	// get associative array of normalized fields
	
	// query select
	function q_select($select_params)
	{
		$str_select = "*";
		$str_where=1;
		$limit="";
		
		if(empty($select_params['join']))
			$str_from = $this->_PREFIX.$select_params['table']; 
		if(!empty($select_params['page']))
		{
			$limit="LIMIT $l1,$page";
		}
		
		if(!empty($select_params['where']))
		{
			
		}
		
		$sql = "SELECT $str_select FROM $str_from WHERE $str_where $limit";
		global $_DEBUG;
		if($_DEBUG)
			echo $sql;
		$res = mysql_query($sql,$this->_LINK);
		if($res==FALSE)
			throw new Exception("Bad query result");
		return $res;
	}
	// get row of result
	function res_row($res)
	{
		return mysql_fetch_array($res,MYSQL_ASSOC);
	}
	// query delete
	function q_delete($del_params)
	{
	
	
	}
	// query select
	function q_delete_item($id)
	{
	
	
	}
	// query add
	function q_add($add_data)
	{
		
	}
	// query update
	function q_update($upd_data)
	{
		
	}
	
	// Commit data table
	function CommitTable($tblname,$TableData)
	{
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
			foreach ($TableData->_FIELDS as $name => $fld)
			{
				if(!empty($fldinfo['bind']))
					$this->create_binding($name, $fldinfo['bind']);
			}
			
			mysql_query($sql,$this->_LINK);
			
			//Set the default values
			foreach ($fldinfo['defdata'] as $d)
			{
				
			}
		}
	}
	
	
}
?>