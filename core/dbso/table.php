<?php 
class DBSTable {
	
	VAR $_FIELDS;
	
	function  __construct($name,$data = NULL)
	{
		$this->_FIELDS = $data;
	}
}
?>