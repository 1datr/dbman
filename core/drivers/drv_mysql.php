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
				$fldinfo = $this->normalized_field($TableData->_FIELDS[$row[0]]);
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
				$fldinfo = $this->normalized_field($fldimage);
				if($fldinfo['Default']==NULL)
					$sql = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD `$fldname` ".$fldinfo['Type']." ".$fldinfo['Null'];
				else
					$sql = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD `$fldname` ".$fldinfo['Type']." Default ".$fldinfo['Default']." ".$fldinfo['Null'];
											
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
	
	function create_binding($tblname,$field,$bind_data)
	{
		$query = "ALTER TABLE `".$this->_PREFIX.$tblname."` ADD FOREIGN KEY ( `$field` ) REFERENCES `".$bind_data['table_to']."` (`".$bind_data['field_to']."`) ON DELETE ".$bind_data['on_delete']." ON UPDATE ".$bind_data['on_update'].";";
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
	function normalized_field($info)
	{
		$typeinfo = Array();
		$typeinfo['Type']='INT';	
		$typeinfo["Default"]=NULL;
		$typeinfo["Null"]="NO";
		$typeinfo["charset"]='';
		$typeinfo["sub_charset"]='';
		
		if(is_string($info))
		{
			if($info[0]=='#')
			{
				$typeinfo['Type']='bigint';
				$info = substr($info,1);
				$arr = explode('.', $info);
			//	var_dump($arr);
				$typeinfo['bind']=Array('table_to'=>$arr[0],'field_to'=>$arr[1],'on_delete'=>'RESTRICT','on_update'=>'RESTRICT');			
			}
			else
				$typeinfo['Type']=$info;
			
		}
		else 
		{
			$typeinfo['Type']=$info['Type'];
			$typeinfo["Default"]=$info["Default"];
			$typeinfo["charset"]=$info["charset"];
			$typeinfo["sub_charset"]=$info["sub_charset"];
		}
		
		$sinonims = Array("string"=>"text","memo"=>"longtext","logic"=>"BOOLEAN","logical"=>"BOOLEAN");// datatype synonims
		if(!empty($sinonims[$typeinfo['Type']]))
			$typeinfo['Type'] = $sinonims[$typeinfo['Type']];
		// control
		if($typeinfo['Type']=='varchar')
			$typeinfo['Type']='varchar(20)';
		$notdefault = Array('varchar','text');
		if(in_array($typeinfo['Type'], $notdefault))
			$typeinfo["Default"]=NULL;
		// collation
		if(($typeinfo["charset"]=="utf8") && ($typeinfo["sub_charset"]==""))
			$typeinfo["sub_charset"]="utf8_general_ci";
		return $typeinfo;
	}
	// query select
	function q_select($select_params)
	{
		
		
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
				$fldinfo = $this->normalized_field($fld);
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
			echo  $sql;
			foreach ($TableData->_FIELDS as $name => $fld)
			{
				if(!empty($fldinfo['bind']))
					$this->create_binding($name, $fldinfo['bind']);
			}
			
			mysql_query($sql,$this->_LINK);
		}
	}
	
	
}
?>