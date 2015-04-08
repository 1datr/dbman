<?php 
$resx = NULL;

class DBSTable {
	// Operator for select
	VAR $_FIELDS;
	VAR $_DEFDATA;
			
	function  __construct($name,$data = NULL,$defdata=NULL)
	{
		$this->_FIELDS = $data;
		$this->_DEFDATA = $defdata;
	}
	
	function addfield($fldname,$fldinfo)
	{
		if(empty($this->_FIELDS[$fldname]))
			$this->_FIELDS[$fldname] = $this->normalized_field($fldinfo);
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
		$this->_FIELDS[$fldname] = $this->normalized_field($fldinfo);		
	}
	// dlete the field
	function deletefield($fldname)
	{
		unset($this->_FIELDS[$fldname]);
	}
	
	function normalize()
	{
		foreach($this->_FIELDS as $fld => &$finfo)
		{
			
			if($fld=='#defdata')
			{
				$this->_DEFDATA = $this->_FIELDS[$fld];
				unset($this->_FIELDS[$fld]);
			}
			else
				$this->normalize_field($finfo);
			
		}
		
		
	}
	
	
	function normalize_field(&$info)
	{
		// NEW CODE
		GLOBAL $_DEF_CHARSET;
		GLOBAL $_DEF_SUBCHARSET;
		
		$deffields = Array('Type'=>'INT',"Default"=>NULL,"Null"=>"NO",
				"charset"=>"","sub_charset"=>"","virtual"=>FALSE);
		$infostr ="";
		if(is_string($info))
			$infostr = "$info";
		// make array if is not array
		if(!is_array($info))
			$info=Array();
		
		foreach($deffields as $fld => $val)
		{
			if(!xarray_key_exists($fld, $info))
				$info[$fld]=$val;
		}
		
		if($infostr!="")
		{
			if($infostr[0]=='#')
			{
				$info['Type']='bigint';
				$_info = substr($infostr,1);
				$arr = explode('.', $_info);
				//	var_dump($arr);
				$info['bind']=Array('table_to'=>$arr[0],
						'field_to'=>$arr[1],
						'on_delete'=>'RESTRICT',
						'on_update'=>'RESTRICT');
			}
			elseif($infostr[0]=='/' || $infostr[0]=='\\') // &fld - virtual field
			{
				$info["virtual"]=TRUE;
				//	echo ">>".$info;
			}
			else
				$info['Type']=$infostr;
		}
		
		$sinonims = Array("string"=>"text","memo"=>"longtext","logic"=>"BOOLEAN","logical"=>"BOOLEAN");// datatype synonims
		if(!empty($sinonims[$info['Type']]))
			$info['Type'] = $sinonims[$info['Type']];
			
		$texttypes=Array('text','tinytext','longtext','mediumtext');
		if(in_array($info['Type'],$texttypes) && ($info['charset']=='') )
		{
			$info['charset']=$_DEF_CHARSET;
			$info["sub_charset"]=$_DEF_SUBCHARSET;
		}
			
			// control
		if($info['Type']=='varchar')
			$info['Type']='varchar(20)';
		$notdefault = Array('varchar','text');
		if(in_array($info['Type'], $notdefault))
			$info["Default"]=NULL;
		// collation
		if(($info["charset"]=="utf8") && ($info["sub_charset"]==""))
			$info["sub_charset"]="utf8_general_ci";
		
		
		//var_dump($info);	
	}
	
	
	
}
?>
