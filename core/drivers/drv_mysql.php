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
	
	}
	// Delete table
	function DeleteTable($tblname)
	{ 
	
	}
	
	// Select queries
	function Select($selectdata)
	{
		
	}
	
	function existsTable($table) {
		$res = mysql_query("SELECT * FROM `".$this->_PREFIX.$table."` LIMIT 1");
		$err_no = mysql_errno();
		return ($err_no != '1146' && $res = true);
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
		`id` BIGINT(11) NOT NULL AUTO_INCREMENT,\n";
			foreach ($TableData->_FIELDS as $name => $fld)
			{
				if(is_string($fld))
				{
					$sql = $sql."`$name` ".$fld." NOT NULL,\n";
				}
			}
			$sql = $sql."
	PRIMARY KEY(`id`)
	)";
			mysql_query($sql,$this->_LINK);
		}
	}
	
	
}
?>