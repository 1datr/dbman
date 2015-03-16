<?php 
// Datascheme object type
define("DSOT_DB",1);
define("DSOT_VIEW",2);

// Datascheme export/import mode
define("DSIE_PHPSERIALIZE",1);
define("DSIE_XML",2);
define("DSIE_JSON",3);

require_once dirName(__FILE__).'/querymanager.php';

class DBScheme extends QMan
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
	// scan database and make datascheme
	function scandb()
	{
		$res = $this->_DRV->TableList();
	//	var_dump($res);
		foreach($res as $tbl)
		{
			$tableinfo = $this->_DRV->getTableStruct($tbl);
			$fldlist = Array();
			foreach ($tableinfo as $fld)
			{
				if($fld[0]=='Id')
					continue;
				
				var_dump($fld);
				
				$fldlist[$fld['Field']]=Array(
						'Type'=>$fld['Type'],
						'Default'=>$fld['Default'],
						"Null"=>$fld["Null"],
						
				);

			}
			$this->add($tbl,$fldlist);
				
			
		}
		//var_dump($this);
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
	
	// Привести информацию о столбцах таблицы в нормальную форму
	function normalize()
	{
		foreach ($this->_SCHEME as $tbl => $t)
		{
			if(method_exists($this->_SCHEME[$tbl], 'normalize'))
				$this->_SCHEME[$tbl]->normalize();
		}
	}
	// commit all changes in scheme
	function dbcommit()
	{
	//	var_dump($this->_SCHEME);
		$this->normalize();
	//	var_dump($this->_SCHEME);
		// ������ ������ ������� ��, ������� ��� � �����
		$tables = $this->_DRV->TableList();
		//echo "TABLES:";
		//var_dump($tables);
		foreach($tables as $tbl)
		{
		//	echo "<br />>>>$tbl";
			if(empty($this->_SCHEME[$tbl]))
			{
				//var_dump($tbl);
				$this->_DRV->DeleteTable($tbl);
			}
		}
		// ���������/��������
		
		foreach($this->_SCHEME as $key => $obj)
		{
				echo "\n<br />".count($this->_SCHEME);		
			$this->_DRV->CommitObject($key,$obj);
		}
		
		$this->_DRV->CommitBindings();
		
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