<?php
class sherpastocksync {
  function __construct() {
    
  }
  
  
  
  function onCli() {
	  @ini_set('memory_limit','1024M');
	  
	require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
	sherpaHelper::$disable_cache = true; 
	sherpaHelper::onCliSherpaStockSync($this); 
	
  }
}