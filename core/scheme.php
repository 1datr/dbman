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
	
	VAR $_SELECT_ARGS=Array();
	VAR $_ADD_ARGS=Array();
	VAR $_UPD_ARGS=Array();
	
	
	// =
	function op($param,$val,$op)
	{
		global $resx;
	
		$this->_BUF[] = Array('op'=>$op,'op1'=>$param,'op2'=>$val);
		$resx=&$this;
		return $resx;
	}
	// where 
	function where($where=NULL)
	{
		if($where!=NULL)
			$this->_SELECT_ARGS['where']=$where;
		return $this;
	}
	
	// &&
	function _and($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->_BUF[] = Array('op'=>'AND');
		return $resx;
	}
	// ||
	function _or($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->_BUF[] = Array('op'=>'OR');
		return $resx;
	}
	// !
	function _not($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->_BUF[] = Array('op'=>'NOT');
		return $resx;
	}
	
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
	// saved query exists
	function qexists($qid)
	{
		global $QCACHE_DIR;
		return file_exists($QCACHE_DIR.'/'.$qid);			
	}
	
	function preprocess_select()
	{
		
	}
	
	function preprocess_update()
	{
	
	}
	
	function preprocess_add()
	{
	
	}
	
	function preprocess_delete()
	{
	
	}
	
	function exe($qid,$params=NULL)
	{
		global $QCACHE_DIR;
		$filename = "$QCACHE_DIR/$qid";
		if($this->qexists($qid))	// load saved query if exists
		{
			
			$q = file_get_contents($filename);
						
		}
		else 
		{
			switch($this->mode)
			{
				case "select" : 
						$this->_DRV->preprocess_select();
						$q = $this->_DRV->q_select($this->_SELECT_ARGS);
					break;
				case "update" : 
						$this->_DRV->preprocess_update();
						$q = $this->_DRV->q_update($this->_SELECT_ARGS);
					break;
				case "add" : 
						$this->_DRV->preprocess_add();
						$q = $this->_DRV->q_add($this->_SELECT_ARGS);
					break;
				case "delete" : 
						$this->_DRV->preprocess_delete();
						$q = $this->_DRV->q_delete($this->_SELECT_ARGS);
					break;
			}
			@chmod($QCACHE_DIR, 775);
			file_put_contents($filename, $q);
		}
		return $this->_DRV->exe_query($q);
	}
	
	VAR $mode ="select";
	// select from table
	function select($table,$selparams="*")
	{
		$this->mode = 'select';
		$this->_SELECT_ARGS = Array();
		$this->_SELECT_ARGS['table']=$table;	
		$this->_SELECT_ARGS['select']=$selparams;
		$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
	}
	// joins
	function join($joinarg)
	{
		if(empty($this->_SELECT_ARGS['join']))
			$this->_SELECT_ARGS['join']=Array();
		$this->_SELECT_ARGS['join'][]=$joinarg;
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
		
		foreach($tables as $tbl)
		{
			if(empty($this->_SCHEME[$tbl]))
			{
				//var_dump($tbl);
				$this->_DRV->DeleteTable($tbl);
			}
		}
		// ���������/��������
		foreach($this->_SCHEME as $key => $obj)
		{
							
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