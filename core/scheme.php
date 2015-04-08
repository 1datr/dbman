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
	VAR $_EXTBUF = Array();
	
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
		
		$this->load_extentions();
		
	}
	// object exists
	function obj_exist($objname)
	{
		return !empty($this->_SCHEME[$objname]);
	}
	// add data query
	function add($objname,$obj_params=NULL,$objtype=DSOT_DB)
	{
		switch ($objtype)
		{
			case DSOT_DB:
					// event before add table 
					$this->exe_event('before_add_table',
							Array(
								'table'=>$objname,
								'fields'=>&$obj_params,
								));
					if(xarray_key_exists('#defdata', $obj_params))
					{
						$_keys = array_keys($obj_params['#defdata']);
					//	var_dump($_keys);
						if(!is_array($obj_params['#defdata'][ $_keys[0] ]))
						{
							$defdata = Array($obj_params['#defdata']);
							//var_dump($defdata);
						}
						else
						{
							$defdata = $obj_params['#defdata'];
						}
						unset($obj_params['#defdata']);
						$this->_SCHEME[$objname] = new DBSTable($objname,$obj_params,$defdata);
					}
					else 
						$this->_SCHEME[$objname] = new DBSTable($objname,$obj_params);
					// event after add table
					$this->exe_event('after_add_table',
							Array(
									'table'=>$objname,
									'fields'=>&$obj_params,
							));
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
	// scan database structure and make datascheme
	function scandb($scan_data=false)
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
				
				//var_dump($fld);
				
				$fldlist[$fld['Field']]=Array(
						'Type'=>$fld['Type'],
						'Default'=>$fld['Default'],
						"Null"=>$fld["Null"],
						
				);

			}
			if($scan_data)
			{
				$fldlist['#defdata']=$this->_DRV->GetTableRows($tbl);
			}
			else 
			{
				if(xarray_key_exists($tbl, $scan_data))
				{
					$fldlist['#defdata']=$this->_DRV->GetTableRows($tbl);
				}
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
	
	// РџСЂРёРІРµСЃС‚Рё РёРЅС„РѕСЂРјР°С†РёСЋ Рѕ СЃС‚РѕР»Р±С†Р°С… С‚Р°Р±Р»РёС†С‹ РІ РЅРѕСЂРјР°Р»СЊРЅСѓСЋ С„РѕСЂРјСѓ
	function normalize()
	{
		foreach ($this->_SCHEME as $tbl => $t)
		{
			if(method_exists($this->_SCHEME[$tbl], 'normalize'))
				$this->_SCHEME[$tbl]->normalize();
		}
	}
	// проверка биндингов и их типов
	function normalize_bindings()
	{
		$_BINDS=Array();
		foreach($this->_SCHEME as $key => &$obj)
		{
			foreach ($obj->_FIELDS as $fldkey => &$fld )
			{
			//	echo "::>>";
			//	var_dump($fld['bind']);
				if( xarray_key_exists($fld, 'bind')) // Есть связка
				{
					/*echo ">>>";
					var_dump($fld);*/
					if($fld['bind']['field_to']=='id') // id
					{
						$fld['Type']='bigint';
					}
					else 
					{
						$fld['Type']=$this->_SCHEME[$fld['bind']['table_to']]->_FIELDS[$fld['bind']['field_to']]['Type'];
					}
				/*	echo "000>>>";
					var_dump($fld);*/
				}
			}
		}
	}
	// commit all changes in scheme
	function dbcommit()
	{
	//	var_dump($this->_SCHEME);
		$this->normalize();
	var_dump($this->_SCHEME);
		$this->normalize_bindings();
		echo "AFTER::";
	var_dump($this->_SCHEME);
		// пїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅ, пїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅпїЅ
		$tables = $this->_DRV->TableList();
		
		foreach($tables as $tbl)
		{
		//	echo "<br />>>>$tbl";
			if(empty($this->_SCHEME[$tbl]))
			{
				//var_dump($tbl);
				$this->_DRV->DeleteTable($tbl);
			}
		}
		// пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ/пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ
		$_DEFDATA=Array();
		foreach($this->_SCHEME as $key => $obj)
		{
			//	echo "\n<br />".count($this->_SCHEME);		
			if(property_exists($obj,"_DEFDATA"))
			{
				if($obj->_DEFDATA!=NULL)
					$_DEFDATA[]=Array('key'=>$key,'defdata'=>$obj->_DEFDATA);
			}
			$this->_DRV->CommitObject($key,$obj);
			
		}
		
		$this->_DRV->CommitBindings();
		// write default data
		$this->_DRV->WriteDefData($_DEFDATA);
		
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