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
		foreach($this->_FIELDS as $fld => $finfo)
			$this->_FIELDS[$fld] = $this->normalized_field($finfo);
	}
	
	
	function normalized_field($info)
	{
		$typeinfo = Array();
		$typeinfo['Type']='INT';
		$typeinfo["Default"]=NULL;
		$typeinfo["Null"]="NO";
		$typeinfo["charset"]='';
		$typeinfo["sub_charset"]='';
		$typeinfo["defdata"]=Array();
		$typeinfo["virtual"]=FALSE;	// the field is virtual
		//$typeinfo['bind']=NULL;
	
		if(is_string($info))
		{
			if($info[0]=='#')
			{
				$typeinfo['Type']='bigint';
				$info = substr($info,1);
				$arr = explode('.', $info);
				//	var_dump($arr);
				$typeinfo['bind']=Array('table_to'=>$arr[0],
						'field_to'=>$arr[1],
						'on_delete'=>'RESTRICT',
						'on_update'=>'RESTRICT');
			}
			elseif($info[0]=='/') // &fld - virtual field
			{
				$typeinfo["virtual"]=TRUE;
			}
			else
				$typeinfo['Type']=$info;
				
		}
		else
		{
			$typeinfo['Type']=$info['Type'];
			$typeinfo["Default"]=$info["Default"];
			$typeinfo["charset"]=$info["charset"];
			$typeinfo["sub_charset"]=$info["sub_charset"];
			if(!empty($info['defdata']))
				$typeinfo["defdata"]=$info['defdata'];
		}
		//echo "BIND > ";// var_dump($info['bind']);
		if(xarray_key_exists("virtual",$info))
		{
			$typeinfo["virtual"] = $info["virtual"];
		}
		if(xarray_key_exists('bind',$info))
		{
			$typeinfo['bind']=$info['bind'];
		}
		$sinonims = Array("string"=>"text","memo"=>"longtext","logic"=>"BOOLEAN","logical"=>"BOOLEAN");// datatype synonims
		if(!empty($sinonims[$typeinfo['Type']]))
			$typeinfo['Type'] = $sinonims[$typeinfo['Type']];
		// control
		if($typeinfo['Type']=='varchar')
			$typeinfo['Type']='varchar(20)';
		$notdefault = Array('varchar','text');
		if(in_array($typeinfo['Type'], $notdefault))
			$typeinfo["Default"]=NULL;
		// collation
		if(($typeinfo["charset"]=="utf8") && ($typeinfo["sub_charset"]==""))
			$typeinfo["sub_charset"]="utf8_general_ci";
		
		//
		return $typeinfo;
	}
	
	
	
}
?>