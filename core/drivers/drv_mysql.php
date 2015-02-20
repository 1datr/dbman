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
	// Create table
	function CreateTable($TableData)
	{ 
	
	}
	// Change table
	function ChangeTable($tblname,$TableData)
	{ 
		$res = mysql_query("SHOW COLUMNS FROM ".$this->_PREFIX.$tblname,$this->_LINK);
		$fields = Array();
		$fildinfo = Array();
		while($row = mysql_fetch_array($res))
		{
			var_dump($row);
			if(empty($TableData->_FIELDS[$row[0]]))
			{
				if($row[0]!='id')
					mysql_query("ALTER TABLE ".$this->_PREFIX.$tblname." DROP ".$row[0],$this->_LINK);
			}
			else 
			{
				// Watch the difference between field image and the real field 
				if($row['Type']==$TableData->_FIELDS[$row[0]])
				{
					
				}
			}
			$fields[]=$row[0];
			
		}
		
		foreach($TableData->_FIELDS as $fldname => $fldimage)
		{
			
			
			if(!in_array($fldname, $fields))
			{
				$fldinfo = $this->normalized_field($fldimage);
				if($fldinfo['Default']==NULL)
					$sql = "ALTER TABLE ".$this->_PREFIX.$tblname." ADD `$fldname` ".$fldinfo['Type']." ".$fldinfo['Null'];
				else
					$sql = "ALTER TABLE ".$this->_PREFIX.$tblname." ADD `$fldname` ".$fldinfo['Type']." Default ".$fldinfo['Default']." ".$fldinfo['Null'];
											
								//var_dump($sql);
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
	
	function TableList()
	{
		$res = mysql_query("SHOW TABLES",$this->_LINK);
		$arr = Array();
		while($row = mysql_fetch_array($res))
			$arr[]= substr($row[0],strlen($this->_PREFIX));
		return $arr;
	}
	// get associative array of normalized fields
	function normalized_field($info)
	{
		$typeinfo = Array();
		$typeinfo['Type']='INT';	
		$typeinfo["Default"]=NULL;
		$typeinfo["Null"]="NOT NULL";
		if(is_string($info))
		{
			$typeinfo['Type']=$info;
			
		}
		else 
		{
			$typeinfo['Type']=$info['Type'];
			$typeinfo["Default"]=$info["Default"];
		}
		// control
		if($typeinfo['Type']=='varchar')
			$typeinfo['Type']='varchar(20)';
		$notdefault = Array('varchar','text');
		if(in_array($typeinfo['Type'], $notdefault))
			$typeinfo["Default"]=NULL;
		return $typeinfo;
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
				$fldinfo = $this->normalized_field($fld);
				if($fldinfo['Default']==NULL)
					$fldstr = "`$name` ".$fldinfo['Type']." ".$fldinfo['Null'];
				else
					$fldstr = "`$name` ".$fldinfo['Type']." Default ".$fldinfo['Default']." ".$fldinfo['Null'];
				
				$sql = $sql."$fldstr,\n";
			
			}
			$sql = $sql."
	PRIMARY KEY(`id`)
	)";
			echo  $sql;
			
			mysql_query($sql,$this->_LINK);
		}
	}
	
	
}
?>