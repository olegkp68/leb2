<?php
/**
 * @package	Zasilkovna 
 * @author Zasilkovna
 * @link http://www.zasilkovna.cz
 */
defined('_JEXEC') or die('Restricted access');

/**
 * @author Zasilkovna
 */

class VirtueMartModelZasilkovnaopc extends VmModel
{
	const VERSION = '1.0';
    const PLG_NAME = 'zasilkovna';
	public $warnings = array();
	public $api_key;
	
	static $_couriers_to_address = array(13 => 'Česká pošta',106 => 'Doručení na adresu ČR',16 => 'Slovenská pošta');
		
	var $_zas_url = "https://www.zasilkovna.cz/";
	
	var $_media_url = "";
	var $_media_path = "";
	
	var $_db_table_name="#__virtuemart_shipment_plg_zasilkovna";	
	var $checked_configuration = false;
	var $config_ok = false;
    
	

	public function __construct()
	{
		$language = JFactory::getLanguage();
		$language->load('plg_vmshipment_zasilkovna', JPATH_ADMINISTRATOR, null, true);
		$language->load('plg_vmshipment_zasilkovna', JPATH_SITE, null, true);

		$config        = VmConfig::loadConfig();
		
		$this->_media_url=JURI::root( true )."/media/com_zasilkovna/media/";
		$this->_media_path=JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna".DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR;	
		
		
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna")) {
		    jimport( 'joomla.filesystem.folder' );
		   JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna"); 
		   JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna".DIRECTORY_SEPARATOR.'media'); 
		}
		
		parent::__construct();
	}
	
	
	public function setConfig($zasilkovna_api_pass)
	{
		$this->api_pass =$zasilkovna_api_pass;
		$this->api_key = substr($zasilkovna_api_pass,0,16);	
	}

	public function getDbTableName(){			
		return $this->_db_table_name;
	}
    
    public function getShipmentMethodIds(){
        $q = "SELECT virtuemart_shipmentmethod_id FROM #__virtuemart_shipmentmethods WHERE shipment_element = 'zasilkovna' or shipment_element = 'zasilkovnaopc'";
        $db = JFactory::getDBO ();
        $db->setQuery($q);
        $objList = $db->loadObjectList();
        $list = array();
        foreach($objList as $obj){
            $list[] = $obj->virtuemart_shipmentmethod_id;
        }
        return $list;
    }



	public function getBranches(){
		return array(); 
		$db = JFactory::getDBO ();
		$q = "SELECT * from #__virtuemart_zasilkovna_branches";
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	public function getCurrencyCode($currency_id){			
			$vendorId = VirtueMartModelVendor::getLoggedVendor();
			$db = JFactory::getDBO ();
			$q = 'SELECT   `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`=' . (int)$currency_id;
			$db->setQuery ($q);
			return $db->loadResult ();
	}

//TODO: write check for restriction code in file /components/com_virtuemart/views/cart/tmpl/select_payment.php
	public function isShipmentPaymentRestrictionInstalled(){
		return false; 
	}

  	public function checkModuleVersion(){ 
	    $checkUrl=$this->_zas_url."api/".$this->api_key."/v2/branch.json";
	    $data = json_decode($this->fetch($checkUrl));        
		 
	    if($data->version > self::VERSION) {	            
	        $lg = JFactory::getLanguage();
	        $lang=substr($lg->getTag(), 0, 2);
	        return JText::_('PLG_VMSHIPMENT_ZASILKOVNA_NEW_VERSION').": ".$data->message->$lang;	        
	    }else{        
	        return JText::_('PLG_VMSHIPMENT_ZASILKOVNA_VERSION_IS_NEWEST')." - ".self::VERSION;
	    }   	    
  	}

	public function checkConfiguration()
	{
		if ($this->checked_configuration) return $this->config_ok;
		$this->checked_configuration = true;
		$key                         = $this->api_key;
		$testUrl                     = $this->_zas_url . "api/$key/test";

		if (!$key) {
			$this->errors[] = JText::_('PLG_VMSHIPMENT_ZASILKOVNA_API_KEY_NOT_SET');
			$this->config_ok  = false;
			return false;
		}
		if (!$this->httpAccessMethod()) {
			$this->errors[] = 'cannot load curl or url_fopen';
			$this->config_ok  = false;
			return false;
		}
		if ($this->fetch($testUrl) != 1) {
			$this->errors[] = JText::_('PLG_VMSHIPMENT_ZASILKOVNA_API_KEY_NOT_VERIFIED');
		}
		$this->config_ok = true;
		return true;
	}
	private function httpAccessMethod(){
	   if(extension_loaded('curl')) return true;
       if(ini_get('allow_url_fopen')) return true;
       return false;
       
	}
	private function fetch($url)
	{
		if (extension_loaded('curl')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$body = curl_exec($ch);
			if (curl_errno($ch) > 0) {
				return false;
			}
			return $body;
		} elseif (ini_get('allow_url_fopen')) {
			if (function_exists('stream_context_create')) {
				$ctx = stream_context_create(array(
					'http' => array(
						'timeout' => 3
					)
				));
				return file_get_contents($url, 0, $ctx);
			} else {
				return file_get_contents($url);
			}
		} else
			return false;
	}

	/*
	* Return js api url and if it is needed, updates it
	*/
	public function updateJSApi(){
		
		 //not used:
		 return true; 

		
		$js_path = $this->_media_path . 'branch.js';
		if (!$this->is_writable($js_path)) {
		    JFactory::getApplication()->enqueueMessage('Cannot write to '.$js_path); 
			return false;		
		}
		
		if (!$this->isFileUpToDate($js_path)) {
			if (!$this->updateFile($js_path, 'js')) {
				//updating file failed                	
				if (!$this->isFileUsable($js_path)) {
					// if file is older than 5 days
					$this->errors[] = "Cannot update javascript file and it is older than 5 days.";
					
					
					return false;
				}
			}
		}
		
		
		if(!$this->updateBranchesInfo()){
			
			return false;
		}
		
		return $this->_media_url . "branch.js";
	}

	public function updateBranchesInfo(){
		//stAn, not needed: 
	   return true; 
		
	  $localFilePath = $this->_media_path.'branch.xml';        
	  
	  if(!$this->is_writable($localFilePath))return false;
	  if(!$this->isFileUpToDate($localFilePath)){         	  
	    // file is older than one days	   	
	    if(!$this->updateFile($localFilePath,"xml")){
	      //failed updating
	      if(!$this->isFileUsable($localFilePath)){
	        //file is older than 5 days and thus not usable
	        $this->errors[]='Cannot update branches xml file and it is older than 5 days.';
	        return false;
	      }      
	    }else{      
	      //updating succeeded, update mysql db
	      if(!$this->saveBranchesXmlToDb($localFilePath)){        
	        $this->errors[]='cannot update branches database records from xml file';
	        return false;
	      }
	    }
	  }
	  return true;
	}	 

	/**
	*	shows errors in module administration
	*/
	public function raiseErrors(){
		if(is_array($this->errors)){
			foreach ($this->errors as $error) {
				JError::raiseWarning(600, $error);
			}
		}
	}

    public function getBranchesJson($method)
	{
		static $json; 
		if (!empty($json)) return $json; 
		
		$file = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovna.json'; //stan moved to media instead cache
jimport( 'joomla.filesystem.file' );
if (file_exists($file))
{
  $data = file_get_contents($file); 
  
  $json = json_decode($data); 
  
  
  if (!empty($json))
 {
   $time = $json->OPCtime; 
   $now = time(); 
   if (($now - $time) > (24 * 60 * 60 * 30)) $refresh = true; //stAn added 30 days for refresh
   
  
 }
 else $refresh = true; 
}
else $refresh = true; 



if ((!empty($refresh)))
{
	
if (isset($method->zasilkovna_api_pass))
{
	
	$zas_model = VmModel::getModel('zasilkovnaopc');
	if (method_exists($zas_model, 'setConfig'))
	{
	 $zas_model->setConfig($method->zasilkovna_api_pass); 
     $url = $zas_model->_zas_url.'api/v2/'.$zas_model->api_key.'/branch.json'; 
     $data = OPCloader::fetchUrl($url); 
	 
	 
	}
}
else
{
$zas_model = VmModel::getModel('zasilkovna');
$url = $zas_model->_zas_url.'api/v2/'.$zas_model->api_key.'/branch.json'; 
$data = OPCloader::fetchUrl($url); 
}


$json = @json_decode($data); 
if (!empty($json))
{
  $json->OPCtime = time(); 
  $data = @json_encode($json); 
}

 JFile::write($file, $data); 
}
return $json; 
	}
	
	
	private function saveBranchesXmlToDb($path){    
	    return true; 
	    $xml = simplexml_load_file($path);
	    if($xml){      
	    	$db = JFactory::getDBO();
	    	$query = 'TRUNCATE TABLE #__virtuemart_zasilkovna_branches';
			$db->setQuery($query);
			$db->execute();
	      	$q = "INSERT INTO #__virtuemart_zasilkovna_branches (
	              `id` ,
	              `name_street` ,
	              `currency` ,
	              `country`
	              ) VALUES ";
	      	$first=true;              
	      	foreach($xml->branches->branch as $key => $branch){          
	      	  if($first){
	      	    $q.=" (";
	      	    $first=false;
	      	  }else{
	      	    $q.=", (";          
	      	  }  
	      	  $q .= "'$branch->id', '$branch->name_street','$branch->currency','$branch->country')";                    
	      	  
	      	}
	      	$db->setQuery($q);
	      	$db->execute();
	    }else{      
	      return false;
	    }
	    return true;
	}



	private function isFileUpToDate($path){
		if (!file_exists($path))return false;
		if (filemtime($path) < time() - (60 * 60 * 24))return false;
		if (filesize($path) <= 1024)return false;
		return true;
	}
	
	private function isFileUsable($path)//true if not older than 5 days
	{
		if (!file_exists($path))return false;
		if (filemtime($path) < time() - (60 * 60 * 24 * 5))return false;
		if (filesize($path) <= 1024)return false;
		return true;
	}
	
	
	private function updateFile($path, $type)
	{
		$remote = $this->_zas_url . "api/" . $this->api_key . "/branch." . $type;
		if ($type == 'js') {
			$lib_path = substr($this->_media_url, 0, -1);			
			$remote .= "?callback=addHooks";
			$remote .= "&lib_path=".urlencode($lib_path)."&sync_load=1";
		}
		
		$data = $this->fetch($remote);
		if (empty($data) || (strlen($data)<1024)) return false; 
		
		jimport( 'joomla.filesystem.file' );
		JFile::write($path, $data); 
		//file_put_contents($path, $data);
		clearstatcache();
		if (filesize($path) < 1024) {
			
			return false;
		}
		return true;
	}

	private function is_writable($filepath){
	  if(!file_exists($filepath)){      
	    @touch($filepath);
	  }
	  if(is_writable($filepath)){
	    return true;
	  }
	  $this->errors[]=$filepath." must be writable.";
	return false;
	}
	

	public function loadLanguage(){
		$language = JFactory::getLanguage();
		$language->load('plg_vmshipment_zasilkovna', JPATH_ADMINISTRATOR, 'en-GB', true);
		$language->load('plg_vmshipment_zasilkovna', JPATH_SITE, 'en-GB', true);
	}
    

}

//pure php no closing tag