<?php 
$res =null;
$_DEBUG=TRUE;
$_QSKIP=FALSE;
require_once dirName(__FILE__).'/config.php';

$d = dir(dirName(__FILE__).'/dbso/');

while (false !== ($entry = $d->read())) {
	$ext = pathinfo(dirName(__FILE__).'/dbso/'.$entry, PATHINFO_EXTENSION);	
	if($ext=="php")
		require_once dirName(__FILE__).'/dbso/'.$entry;
}
$d->close();

require_once dirName(__FILE__).'/scheme.php';
require_once dirName(__FILE__).'/driver.php';
require_once $DIR_INC.'/utils.php';
require_once $DIR_INC.'/extention.php';


// require database drivers
foreach ($DBMAN_DRVLIST as $drv)
{
	require_once $DBMAN_DRIVERPATH.'/drv_'.$drv.'.php';
	
}

require_once dirName(__FILE__).'/db.php';

?>