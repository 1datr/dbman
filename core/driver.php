<?php 
// driver of database
abstract class DBDriver {
	// Connect to server
	abstract function Connect($connData);
	// Disconnect from db
	abstract function Disonnect($disconnectvar=NULL);
	// Create table
	abstract function CreateTable($TableData);
	// Change table
	abstract function ChangeTable($tblname,$TableData);
	// Delete table
	abstract function DeleteTable($tblname);
	// Select queries
	abstract function Select($selectdata);
	// Commit data table
	abstract function CommitTable($tblname,$TableData);
	
	// Commit data table
	function CommitObject($oname,$object)
	{
		//var_dump($object);
	//	echo get_class($object);
		switch(get_class($object))
		{
			case 'DBSTable':
				$this->CommitTable($oname,$object);
				break;
			case 'DBSView':
				
				break;
		}
	}
}
?>