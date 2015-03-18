<?php 
function xarray_key_exists($key,$srch)
{
	if(is_array($srch))
		return array_key_exists($key,$srch);
	else 
		return false;
	
}

?>