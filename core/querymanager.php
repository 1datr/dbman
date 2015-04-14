<?php 

class QMan
{
	
	VAR $_SELECT_ARGS=Array();
	VAR $_ADD_ARGS=Array();
	VAR $_UPDATE_ARGS=Array();
	VAR $_DELETE_ARGS=Array();
	VAR $_DELITEM_ARGS=Array();
	VAR $_WHEREBUF=Array();
	VAR $_RES=Null;
	VAR $_ROW=Null;
	// get current arguments
	function get_current_args()
	{
		$_ARGS = Array();
		$_ARGS['mode']=$this->mode;
		$points_to_copy = Array('_SELECT_ARGS','_ADD_ARGS','_UPDATE_ARGS','_DELETE_ARGS','_DELITEM_ARGS');
		foreach ($points_to_copy as $p)
		{
			$_ARGS[$p]=$this->$p;
		}
		return $_ARGS;
	}
	function result_count($res)
	{ 
		return $this->_DRV->res_count($res);
	}
	// set arguments
	function set_args($_args)
	{
		$this->mode = $_args['mode'];
		$points_to_copy = Array('_SELECT_ARGS','_ADD_ARGS','_UPDATE_ARGS','_DELETE_ARGS','_DELITEM_ARGS');
		foreach ($points_to_copy as $p)
		{
			$this->$p = $_args[$p];
		}
	}
	// =
	function op($param,$val,$op)
	{
		global $resx;
	
		$this->where_buf_ptr[] = Array('op'=>$op,'op1'=>$param,'op2'=>$val);
		$resx=&$this;
		return $resx;
	}
	// &&
	function _and($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->where($param,'AND');
		return $resx;
	}
	// ||
	function _or($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->where($param,'OR');
		return $resx;
	}
	// !
	function _not($param,$val)
	{
		global $resx;
		$resx=&$this;
		$this->_SELECT_ARGS['WHEREBUF'][] = Array('op'=>'NOT');
		return $resx;
	}
	
	VAR $prepr=null;
	// preprocess select query
	function preprocess_select($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		//	echo "$DIR_INC/$preprc.php";
		require_once "$DIR_INC/$preprc.php";
	
		$this->prepr = new $preprc();
	
		$this->prepr->setscheme($this->_SCHEME);
		$newargs = $this->prepr->preprocess_select($args);
		//	var_dump($newargs);
		return $newargs;
	}
	
	function preprocess_update($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
			require_once "$DIR_INC/$preprc.php";
	
		$this->prepr = new $preprc();
			
		$prepr->scheme = $this->_SCHEME;
		return  $this->prepr->preprocess_update($args);
	
	}
	
	function preprocess_add($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		require_once "$DIR_INC/$preprc.php";
		
		$this->prepr = new $preprc();
		$this->prepr->scheme = $this->_SCHEME;
		return  $this->prepr->preprocess_add($args);
	}
	
	VAR $mode;
	
	function preprocess_delete($args)
	{
		global $DIR_INC;
		$preprc = "sqlpreprocessor";
		if(!empty($args['prepr'])) $preprc = $args['prepr'];
		require_once "$DIR_INC/$preprc.php";
		
		$this->prepr = new $preprc();
		$this->prepr->scheme = $this->_SCHEME;
		return  $this->prepr->preprocess_delete($args);
	}
	
	// saved query exists
	function qexists($qid)
	{
		if($qid==NULL) return false;
		global $QCACHE_DIR;
		return file_exists($QCACHE_DIR.'/'.$qid);
	}
	
	function exe_event($event,$args=NULL)
	{
		$args['scheme']=&$this;
		foreach ($this->_EXTBUF as $idx => $ext)
		{
			$evname = "on_$event";
			if(method_exists($ext,$evname))
				$ext->$evname($args);
		}
	}
	// load all extentions
	function load_extentions()
	{
		$this->_EXTBUF=Array();
		GLOBAL $DIR_EXT;
		GLOBAL $EXT_ENABLE;
		foreach ($EXT_ENABLE as $idx => $ext)
		{
			if(is_string($idx))
			{
				require_once "$DIR_EXT/$idx/index.php";
				$extclassname="DBMExt".strtolower($idx);
				$this->_EXTBUF[]=new $extclassname($ext);
			}
			else // load without params
			{
			require_once "$DIR_EXT/$ext/index.php";
			$extclassname="DBMExt".strtolower($ext);
			$this->_EXTBUF[]=new $extclassname();
			}
		}
	}
	
	function datarow($rnumber)
	{
		
		$this->_ROW = $this->_DRV->result_row($this->_RES,$rnumber);
		return $this;
	}
	
	function rowfield($fld)
	{
		if(!empty($this->_ROW))
			return null;
		return $this->_ROW[$fld];
	}
	
	function exeq($qid=NULL,$params=NULL)
	{
		$this->_RES = $this->exe($qid,$params);
		return $this;
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
			$this->exe_event('before_saved_query',Array('params'=>$params,'sql'=>$q));
		}
		else
		{
			switch($this->mode)
			{
				case "select" :
					
					$this->exe_event('before_query',Array('qmode'=>'select','params'=>$params,'args'=>&$this->_SELECT_ARGS));
					
					$this->_SELECT_ARGS = $this->preprocess_select($this->_SELECT_ARGS);
				//	var_dump($this->_SELECT_ARGS);
					$q = $this->_DRV->q_select($this->_SELECT_ARGS);
					break;
				case "update" :
					
					$this->exe_event('before_query',Array('qmode'=>'update','params'=>$params,'args'=>&$this->_UPDATE_ARGS));
					
					$this->_UPDATE_ARGS = $this->preprocess_update($this->_UPDATE_ARGS);
					//   var_dump($this->_UPDATE_ARGS);
					$q = $this->_DRV->q_update($this->_UPDATE_ARGS);
					//echo $q;
					break;
				case "add" :
					
					$this->exe_event('before_query',Array('qmode'=>'add','params'=>$params,'args'=>&$this->_ADD_ARGS));
					
					$this->_ADD_ARGS = $this->preprocess_add($this->_ADD_ARGS);
					mutex_wait("add_".$this->_ADD_ARGS['table']);
					GLOBAL $_MAX_COUNT_IN_ADDBLOCK;
					if(count($this->_ADD_ARGS['data'])>$_MAX_COUNT_IN_ADDBLOCK )
					{
						$_BUF = xsplit_array($this->_ADD_ARGS['data'], $_MAX_COUNT_IN_ADDBLOCK);
						$q = Array();
						foreach($_BUF as $_ITEM)
						{
							$this->_ADD_ARGS['data']=$_ITEM;
							$q[] = $this->_DRV->q_add($this->_ADD_ARGS);
						}
					}
					else 
					{
						$q = $this->_DRV->q_add($this->_ADD_ARGS);
					}
					//var_dump($q);
					break;
				case "delete" :
					
					$this->exe_event('before_query',Array('qmode'=>'delete','params'=>$params,'args'=>&$this->_DELETE_ARGS));
					
					//var_dump($this->_DELETE_ARGS);
					$this->_DELETE_ARGS = $this->preprocess_delete($this->_DELETE_ARGS);
					
					$q = $this->_DRV->q_delete($this->_DELETE_ARGS);
					break;
				case "deleteitem" :
					
					$this->exe_event('before_query',Array('qmode'=>'deleteitem','params'=>$params,'args'=>&$this->_DELITEM_ARGS));
					
					//$this->_DELITEM_ARGS = $this->preprocess_delete($this->_DELITEM_ARGS);
					$q = $this->_DRV->q_delete_item($this->_DELITEM_ARGS);
					break;
			}
				
			@chmod($QCACHE_DIR, 775);
			if($qid!=NULL)
				file_put_contents($filename, $q);
			}
			
			if(is_array($q)) // if $q is array
			{
				$qres = Array();
				foreach ($q as $qitem)
				{
					$qres[] = $this->_DRV->exe_query($qitem);
				}
		}
		else 
		{
			if($q!=FALSE) // если запрос адекватен
			{
				if($params!=NULL)
				{
					$q = $this->make_params($q, $params);
					global $_QDEBUG;
					if($_QDEBUG)
						echo ">>$q>>";
				}
				$qres = $this->_DRV->exe_query($q);
			}
		}
		switch($this->mode)
		{
			case "select" :
				
				$this->exe_event('after_query',Array('qmode'=>'select','qresult'=>$qres));
				break;
			case "update" :
				
				$this->exe_event('after_query',Array('qmode'=>'update','qresult'=>$qres));
				break;
			case "add" :
				
				$qres = $this->_DRV->last_added_ids($this->_ADD_ARGS['table']);
				mutex_free("add_".$this->_ADD_ARGS['table']);
				
				$this->exe_event('after_query',Array('qmode'=>'add','qresult'=>$qres));
					
				//var_dump($q);
				break;
			case "delete" :
			
				$this->exe_event('after_query',Array('qmode'=>'delete','qresult'=>$qres));
				break;
			case "deleteitem" :
				
				$this->exe_event('after_query',Array('qmode'=>'deleteitem','qresult'=>$qres));
				break;
		}
		return $qres;
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
		$this->_SELECT_ARGS['joins']=Array();
		$this->_SELECT_ARGS['group']=Array();
		$this->_SELECT_ARGS['having']='';
		$this->_SELECT_ARGS['order']=Array();
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
	}
	// group
	function group($fld)
	{
		$this->_SELECT_ARGS['group'][]=$fld;
		return $this;
	}
	function having($_having)
	{
		$this->_SELECT_ARGS['having']=$_having;
	}
	// order asc
	function order($fld)
	{
		$this->_SELECT_ARGS['order'][]=Array('fld'=>$fld,'dir'=>'ASC');
		return $this;
	}
	// order desc
	function order_d($fld)
	{
		$this->_SELECT_ARGS['order'][]=Array('fld'=>$fld,'dir'=>'DESC');
		return $this;
	}
	// insert some data
	function insert($table,$data)
	{
		$this->mode = 'add';
		$this->_ADD_ARGS = Array();
		$this->_ADD_ARGS['table']=$table;
		if(empty($this->_ADD_ARGS['data']))
			$this->_ADD_ARGS['data']=Array();
		// normalize data
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
	
	// insert some data
	function update($table,$data=NULL)
	{
		$this->mode = 'update';
		$this->_UPDATE_ARGS = Array();
		$this->_UPDATE_ARGS['table']=$table;
		if(empty($this->_UPDATE_ARGS['data']))
			$this->_UPDATE_ARGS['data']=Array();
		if($data!=NULL)
			foreach ($data as $k => $v)
				$this->_UPDATE_ARGS['data'][$k]=$v;
		
	
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;	
	}
	
	// delete some data
	function delete($table)
	{
		$this->mode = 'delete';
		$this->_DELETE_ARGS = Array();
		$this->_DELETE_ARGS['table']=$table;
		
	
	
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
	}
	
	// delete some data
	function delete_item($table,$id)
	{
		$this->mode = 'deleteitem';
		$this->_DELITEM_ARGS = Array();
		$this->_DELITEM_ARGS['table']=$table;
		$this->_DELITEM_ARGS['id']=$id;
	
	
		//$this->_SELECT_ARGS['scheme']=&$this->_SCHEME;
		return $this;
	}
	
	
	function set($fld,$val)
	{
		switch($this->mode)
		{
			
			case 'update':
				$this->_UPDATE_ARGS['data'][$fld]=$val;
		}
		return $this;
	}
		
	function where($_WHERE=Null,$binding='AND')
	{
		if($_WHERE ==Null)
			$_WHERE="1";
		switch($this->mode)
		{
			case 'select':
				if(empty($this->_SELECT_ARGS['where']))
					$this->_SELECT_ARGS['where']=$_WHERE;
				else 
					$this->_SELECT_ARGS['where']=$this->_SELECT_ARGS['where']." $binding $_WHERE";
				break;
			case 'update':
				if(empty($this->_UPDATE_ARGS['where']))
					$this->_UPDATE_ARGS['where']=$_WHERE;
				else
					$this->_UPDATE_ARGS['where']=$this->_UPDATE_ARGS['where']." $binding $_WHERE";
				break;
			case 'delete':
				if(empty($this->_DELETE_ARGS['where']))
					$this->_DELETE_ARGS['where']=$_WHERE;
				else
					$this->_DELETE_ARGS['where']=$this->_DELETE_ARGS['where']." $binding $_WHERE";
				break;
		}
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
	
	
	// exe sql query
	function exe_sql($query,$exept=true)
	{
		$q = $this->_DRV->exe_query($query,$exept);
	}
		// get row from result
	function getfield($rid,$fld)
	{
		return $this->_DRV->result_row_by_number($this->_RES,$rid,$fld);
	}	
	// result row
	function res_row($rid)
	{
		return $this->_DRV->res_row($rid);
	}
}
?>