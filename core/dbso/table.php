<?php 
class DBSTable {
	
	VAR $_FIELDS;
	
	function  __construct($name,$data = NULL)
	{
		$this->_FIELDS = $data;
	}
	
	function addfield($fldname,$fldinfo)
	{
		if(empty($this->_FIELDS[$fldname]))
			$this->_FIELDS[$fldname] = $fldinfo;
	}
	
	function getfield($fldname)
	{
		global $res;
		$res = &$this->_FIELDS[$fldname];
		return $res;
	}
	// set the field info
	function setfield($fldname,$fldinfo)
	{
		$this->_FIELDS[$fldname] = $fldinfo;		
	}
	// dlete the field
	function deletefield($fldname)
	{
		unset($this->_FIELDS[$fldname]);
	}
}
?>