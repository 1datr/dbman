<?php 
// Datascheme object type
define("DSOT_DB",1);
define("DSOT_VIEW",2);

// Datascheme export/import mode
define("DSIE_PHPSERIALIZE",1);
define("DSIE_XML",2);
define("DSIE_JSON",3);


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
		$this->normalize();
		switch ($mode)
		{
			case DSIE_PHPSERIALIZE: 
					$context = serialize($this->_SCHEME);
					file_put_contents($fname, $context);
				break;
			case DSIE_XML:
					global $DIR_INC;
					require_once "$DIR_INC/Serializer.php";
					$serman = new XMLSerializer();
					file_put_contents($fname, $serman->SerializeClass($this->_SCHEME));
				break;
			case DSIE_JSON:
					file_put_contents($fname, json_encode($this->_SCHEME));
				break;
			
		}		
	}
	// Import datascheme from file
	function import($fname)
	{
		$content = file_get_contents($fname);
		$extension = end(explode('.', $fname));
		switch($extension)
		{
			case 'jsd':
			case 'js':
			case 'jso':
					$this->_SCHEME = json_decode($content);
				break;
			case 'xml':
					global $DIR_INC;
					require_once "$DIR_INC/Serializer.php";
					$serman = new XMLSerializer();
					$this->_SCHEME = xmlrpc_decode($content);
				break;
			case 'ser':
					$this->_SCHEME = unserialize($content);
				break;
		}
	}
	
	// select from table
	function select($selparams)
	{
		return $this->_DRV->q_select($selparams);
	}
	// get row from result
	function res_row($res)
	{
		return $this->_DRV->res_row($res);
	}
	
	function normalize()
	{
		foreach ($this->_SCHEME as $tbl => $t)
		{
			if(method_exists($t, 'normalize'))
				$this->_SCHEME[$tbl]->normalize();
		}
	}
	// commit all changes in scheme
	function dbcommit()
	{
		$this->normalize();
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