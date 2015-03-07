<?php 
class XMLSerializer 
{ 
    private static $Data; 

   
    public  function SerializeClass($ObjectInstance) 
    { 
       return xmlrpc_encode($ObjectInstance);
    	
    } 

    public function UnSerialize($xml)
    {
    	//xmlrpc_decode ($xml)
    }
} 
?>