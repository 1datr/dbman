<?php
/* multi-language scheme */
class MLScheme extends DBScheme
{
	function  __construct($dbscheme=NULL)
	{
	
		if($dbscheme==NULL)
		{
			$this->_SCHEME = Array();
			
			$this->add('language',Array(
					'short'=>'text',
					'full'=>Array("Type"=>'text','charset'=>'utf8')
			));
		}
		else
			parent::__construct($dbscheme);
			
	}
}
?>