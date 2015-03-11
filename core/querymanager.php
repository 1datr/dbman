<?php 
class QMan
{
	
	VAR $_SELECT_ARGS=Array();
	VAR $_ADD_ARGS=Array();
	VAR $_UPD_ARGS=Array();
	VAR $_DELETE_ARGS=Array();
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
	
	// preprocess select query
	function preprocess_select($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		//	echo "$DIR_INC/$preprc.php";
		require_once "$DIR_INC/$preprc.php";
	
		$prepr = new $preprc();
	
		$prepr->setscheme($this->_SCHEME);
		$newargs = $prepr->preprocess_select($args);
		//	var_dump($newargs);
		return $newargs;
	}
	
	function preprocess_update($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
			require_once "$DIR_INC/$preprc.php";
	
			$prepr = new $preprc();;
			$prepr->scheme = $this->_SCHEME;
			return  $prepr->preprocess_update($args);
	
	}
	
	function preprocess_add($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		require_once "$DIR_INC/$preprc.php";
		
		$prepr = new $preprc();
		$prepr->scheme = $this->_SCHEME;
		return  $prepr->preprocess_add($args);
	}
	
	VAR $mode;
	
	function preprocess_delete()
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		require_once "$DIR_INC/$preprc.php";
		
		$prepr = new $preprc();
				$prepr->scheme = $this->_SCHEME;
				return  $prepr->preprocess_delete($args);
	}
	
	// saved query exists
	function qexists($qid)
	{
		if($qid==NULL) return false;
		global $QCACHE_DIR;
		return file_exists($QCACHE_DIR.'/'.$qid);
	}
	
	function exe($qid=NULL,$params=NULL)
	{
		global $QCACHE_DIR;
		$q="";
		if($qid!=NULL)
			$filename = "$QCACHE_DIR/$qid";
		if($this->qexists($qid))	// load saved query if exists
		{
		if($qid!=NULL)
			$q = file_get_contents($filename);
		
		}
		else
		{
		switch($this->mode)
		{
			case "select" :
				$this->_SELECT_ARGS = $this->preprocess_select($this->_SELECT_ARGS);
			//	var_dump($this->_SELECT_ARGS);
				$q = $this->_DRV->q_select($this->_SELECT_ARGS);
				break;
			case "update" :
				$this->_UPDATE_ARGS = $this->preprocess_update($this->_UPDATE_ARGS);
				$q = $this->_DRV->q_update($this->_UPDATE_ARGS);
				break;
			case "add" :
				$this->_ADD_ARGS= $this->preprocess_add($this->_ADD_ARGS);
				$q = $this->_DRV->q_add($this->_ADD_ARGS);
				var_dump($q);
				break;
			case "delete" :
				$this->_DELETE_ARGS = $this->preprocess_delete($this->_DELETE_ARGS);
				$q = $this->_DRV->q_delete($this->_DELETE_ARGS);
				break;
		}
			
		@chmod($QCACHE_DIR, 775);
		if($qid!=NULL)
			file_put_contents($filename, $q);
		}
		
		if($params!=NULL)
			$q = $this->make_params($q, $params);
		return $this->_DRV->exe_query($q);
	}
	
	function make_params($sql,$params)
	{
		$params2 = Array();
		foreach($params as $k => $v)
		{
			$params2['{'.$k.'}']=$v;
		}
		return strtr($sql,$params2);
	}
	

	// select from table
	function select($table,$selparams="*")
	{
		$this->mode = 'select';
		$this->_SELECT_ARGS = Array();
		$this->_SELECT_ARGS['table']=$table;
		$this->_SELECT_ARGS['select']=$selparams;
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
	}
	
	function insert($table,$data)
	{
		$this->mode = 'add';
		$this->_ADD_ARGS = Array();
		$this->_ADD_ARGS['table']=$table;
		if(empty($this->_ADD_ARGS['data']))
			$this->_ADD_ARGS['data']=Array();
		
		if(is_array($data[0]))
		{
			foreach ($data as $d)
				$this->_ADD_ARGS['data'][]=$d;
		}
		else 
		{
			$this->_ADD_ARGS['data'][]=$data;
		}
		
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
		
	}
	
	// joins
	function join($joinarg)
	{
		if(empty($this->_SELECT_ARGS['joins']))
			$this->_SELECT_ARGS['joins']=Array();
	
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		//	echo "$DIR_INC/$preprc.php";
		require_once "$DIR_INC/$preprc.php";
	
		$prepr = new $preprc();
	
		$prepr->setscheme($this->_SCHEME);
		$prepr->preprocess_addjoin($joinarg,$this->_SELECT_ARGS);
	}
	// add group argument
		function group($group_arg)
		{
		if(empty($this->_SELECT_ARGS['group']))
			$this->_SELECT_ARGS['group']=Array();
				$this->_SELECT_ARGS['group'][]=$group_arg;
		}
	
		// get row from result
		function res_row($res)
		{
		return $this->_DRV->res_row($res);
		}
}
?>