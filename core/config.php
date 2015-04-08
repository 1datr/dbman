<?php 
// database driver path
$DBMAN_DRIVERPATH = dirName(__FILE__).'/drivers';
$DBMAN_DRVLIST = Array('mysql');
$DIR_INC = dirName(__FILE__).'/inc';
$QCACHE_DIR = dirName(__FILE__).'/sqlcache';
// dbman extentions directory
$DIR_EXT = dirName(__FILE__).'/../ext';
// dbman extentions enabled
$EXT_ENABLE = Array('multilang');
$_DEF_CHARSET = "utf8"; 
$_DEF_SUBCHARSET = "utf8_general_ci";
$_MAX_COUNT_IN_ADDBLOCK = 3;
$_QDEBUG = false;
$_AUTO_CREATE_DB = true;
$_QFILEDUMP = false;
?>