<?php 
// driver of database
abstract class DBDriver {
	
	VAR $currentdb;
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
	// List of table
	abstract function TableList();
	// make table binding
	abstract function create_binding($tblname,$field,$bind_data);	
	// query select
	abstract function q_select($select_params);
	// query delete
	abstract function q_delete($del_params);
	// query select
	abstract function q_delete_item($id);
	// query add
	abstract function q_add($add_data);
	// query update
	abstract function q_update($upd_data);
	// get the table structure
	abstract function getTableStruct($tblname);
	
	abstract function res_row($res);
	
	abstract function CommitBindings();
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