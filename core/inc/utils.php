<?php 
function xarray_key_exists($key,$srch)
{
	if(is_array($srch))
		return array_key_exists($key,$srch);
	else 
		return false;
	
}

// mark mutex occupied
function mutex_mark($mtxname)
{
	$_mtx_file =".mtx_$mtxname";
	file_put_contents($_mtx_file,time());
}

// wait when the will be free
function mutex_wait($mtxname)
{
	$_mtx_file =".mtx_$mtxname";
	while(file_exists($_mtx_file ))
	{
	
	}
	mutex_mark($mtxname);
}


// free the mutex
function mutex_free($mtxname)
{
	$_mtx_file =".mtx_$mtxname";
	@unlink($_mtx_file);
}

?>