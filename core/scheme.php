<?php 
// Datascheme object type
define("DSOT_DB",1);
define("DSOT_VIEW",2);

// Datascheme export/import mode
define("DSIE_PHPSERIALIZE",1);
define("DSIE_XML",2);
define("DSIE_YML",3);


class DBScheme
{
	
	VAR $_SCHEME = Array();
	VAR $_DRV = null;
	
	function setdriver(&$drv)
	{
		$this->_DRV = $drv;
		
	}
	
	function  __construct($dbscheme=NULL)
	{
		
		if($dbscheme==NULL)
			$this->_SCHEME = Array();
		elseif(is_string($dbscheme))
			$this->import($dbscheme);
		elseif(is_array($dbscheme))
			$this->_SCHEME = $dbscheme;
		else
			throw new Exception("Wrong datascheme");
	}
	
	function add($objname,$obj_params=NULL,$objtype=DSOT_DB)
	{
		switch ($objtype)
		{
			case DSOT_DB: 
					$this->_SCHEME[$objname] = new DBSTable($objname,$obj_params);
				break;
			case DSOT_VIEW:
				
				break;
		}
		
	}
	// Export datascheme to file
	function export($fname, $mode=DSIE_PHPSERIALIZE)
	{
		switch ($mode)
		{
			case DSIE_PHPSERIALIZE: 
					$context = serialize($this->_SCHEME);
					file_put_contents($fname, $context);
				break;
			case DSIE_XML:
					
				break;
			case DSIE_YML:
					
				break;
			
		}		
	}
	// Import datascheme from file
	function import($fname)
	{
		
	}
	// commit all changes in scheme
	function dbcommit()
	{
		// список таблиц удаляем те, которых нет в схеме
		$tables = $this->_DRV->TableList();
		
		foreach($tables as $tbl)
		{
			if(empty($this->_SCHEME[$tbl]))
			{
				//var_dump($tbl);
				$this->_DRV->DeleteTable($tbl);
			}
		}
		// добавляем/изменяем
		foreach($this->_SCHEME as $key => $obj)
		{
							
			$this->_DRV->CommitObject($key,$obj);
		}
		
	}
	// get the table
	function gettable($tbl)
	{ 
		global $res;
		$res = &$this->_SCHEME[$tbl];
		return $res;
	}
}
?>