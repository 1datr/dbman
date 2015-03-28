<?php 
// Расширение dbman
class DBMExtMultilang extends DBMExtention{
	function on_before_add_table($args)
	{
		echo ":MULTILANG:";
		var_dump($args);
	}
}
?>