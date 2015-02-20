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
	abstract function ChangeTable($TableData);
	// Delete table
	abstract function DeleteTable($TableData);
	// Select queries
	abstract function Select($selectdata);
}
?>