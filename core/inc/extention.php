<?php 
// Расширение dbman
class DBMExtention{
	
	VAR $_PARMS = Array();
	
	function __construct($params=NULL)
	{
		if($params)
			$this->_PARMS = $params;
	}
}
?>