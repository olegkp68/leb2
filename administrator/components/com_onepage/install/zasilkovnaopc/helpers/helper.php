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

class ZasilkovnaopcHelper
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
    
	public static function getInstance(&$ref=null)
	{
		static $me;
		if (!empty($me)) return $me; 
		
		$me = new ZasilkovnaopcHelper($ref); 
	}

	public function __construct(&$ref=null)
	{
		if (!empty($ref)) {
			$this->plugin = $ref; 
		}
		else {
			if (class_exists('plgVmShipmentZasilkovnaopc')) {
				$this->plugin = new plgVmShipmentZasilkovnaopc(null, null); 
			}
		}
		
		$language = JFactory::getLanguage();
		$language->load('plg_vmshipment_zasilkovna', JPATH_ADMINISTRATOR, null, true);
		$language->load('plg_vmshipment_zasilkovna', JPATH_SITE, null, true);

		$config        = VmConfig::loadConfig();
		/*
		$this->_media_url=JURI::root( true )."/media/com_zasilkovna/media/";
		$this->_media_path=JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna".DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR;	
		
		
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna")) {
		    jimport( 'joomla.filesystem.folder' );
		   JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna"); 
		   JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_zasilkovna".DIRECTORY_SEPARATOR.'media'); 
		}
		*/
		
		
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
			$q = 'SELECT   `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`=' . $currency_id;
			$db->setQuery ($q);
			return $db->loadResult ();
	}

//TODO: write check for restriction code in file /components/com_virtuemart/views/cart/tmpl/select_payment.php
	public function isShipmentPaymentRestrictionInstalled(){
		return false; 
	}

  	public function checkModuleVersion(){ 
	    $checkUrl=$this->_zas_url."api/v4/".$this->api_key."/branch.json";
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

	}

	public function updateBranchesInfo(){
		//stAn, not needed: 
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

    public function getBranchesJson($method, $virtuemart_country_id=0, $force=false)
	{
		$md5 = md5($method->zasilkovna_api_pass); 
		jimport( 'joomla.filesystem.folder' );
		$savedir = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovnaopc'; 
		if (!file_exists($savedir)) {
			JFolder::create($savedir); 
		}
		$savedirhtml = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovnaopc'.DIRECTORY_SEPARATOR.'html'; 
		if (!file_exists($savedirhtml)) {
			JFolder::create($savedirhtml); 
		}
		$savedir = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovnaopc'.DIRECTORY_SEPARATOR.'js'; 
		if (!file_exists($savedir)) {
			JFolder::create($savedir); 
		}
		$file = $savedir.DIRECTORY_SEPARATOR.'zasilkovna_'.$md5.'.json'; //stan moved to media instead cache
		if (file_exists($file)) {
			if (empty($force)) {
				return true; 
			}
		}
		
		
		
		if (!class_exists('OPCloader')) {
			JFactory::getApplication()->enqueueMessage('Chyba: RuposTel OPC musi byt nainstalovane !'); 
			return ''; 
		}
		
		jimport( 'joomla.filesystem.file' );
		if (isset($method->zasilkovna_api_pass))
		{
	
			
			
				$url = $this->_zas_url.'api/v3/'.$method->zasilkovna_api_pass.'/branch.json'; 
				
				$data = OPCloader::fetchUrl($url); 
				/*
				$zjson = $savedir.DIRECTORY_SEPARATOR.'zasilkovna_data.json';
				$jsontest = @json_decode($data); 
				$jsontest2 = json_encode($jsontest,JSON_PRETTY_PRINT);  
				JFile::write($zjson, $jsontest2); 
				*/
	 
			
		}
		else
		{
		  JFactory::getApplication()->enqueueMessage('Chyba stahovania pobociek zasilkovny - vyplnte API Heslo'); 
		}

		if (empty($data)) return false; 
		
		$json = @json_decode($data); 




if (!empty($json))
{
  $json->OPCtime = time(); 
  
  $by_country = array(); 
  
  foreach ($json->data as $k=>$branch)
				{
					
					if (!isset($branch->id)) continue; 
					$id = (int)$branch->id; 
					$branch_id = (int)$branch_id; 
					
					$country = $branch->country; 
					$name = $branch->name; 
				
					$obj = $this->plugin->getOptionObj($branch); 
				
					$virtuemart_country_id = $this->plugin->countryCode2IntoVirtuemartId($country); 
					if (empty($by_country[$virtuemart_country_id])) {
						$by_country[$virtuemart_country_id] = array(); 
					}
					$by_country[$virtuemart_country_id][] = $obj; 
					
					$branchHtml = $this->plugin->getBranchHtml($branch); 
					$fn = $savedirhtml.DIRECTORY_SEPARATOR.'branch_'.$branch->id.'.html'; 
					JFile::write($fn, $branchHtml); 
					
					//pre ziskanie dat pobocky podla ID:
					$js = json_encode($branch);
					
					$fn = $savedir.DIRECTORY_SEPARATOR.'branch_'.$branch->id.'.json'; 
					JFile::write($fn, $js); 
					
					
				}
				
				
				
	//pre renderovanie options:
	foreach ($by_country as $virtuemart_country_id=>$data) {
		$js = ' if (typeof zasilkovnaCountryBranches === \'undefined\') var zasilkovnaCountryBranches = []; '."\n"; 
		$js .= ' zasilkovnaCountryBranches['.(int)$virtuemart_country_id.'] = '.json_encode($by_country[$virtuemart_country_id]).'; '."\n";
		$fn = $savedir.DIRECTORY_SEPARATOR.'zasilkovnaCountryBranches_'.(int)$virtuemart_country_id.'.js'; 
		JFile::write($fn, $js); 
	}
  
  $data = @json_encode($json, JSON_PRETTY_PRINT); 
  JFile::write($file, $data); 
  $zjson = $savedir.DIRECTORY_SEPARATOR.'zasilkovna.json';
  
  JFile::write($zjson, $data); 
  
}

 

	return true; 
	}
	
	
	function getSingleBranch($branch_id) {
		$savedir = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovnaopc'.DIRECTORY_SEPARATOR.'js'; 
		$fn = $savedir.DIRECTORY_SEPARATOR.'branch_'.(int)$branch_id.'.json'; 
		
		if (!file_exists($fn)) {
			return new stdClass(); 
		}
		$data = file_get_contents($fn); 
		$json = json_decode($data, false); 
		if (empty($json)) {
			return new stdClass(); 
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