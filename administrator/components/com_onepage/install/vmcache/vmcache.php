<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Virtuemart Page Cache Plugin 
 *
 * @package		vmcache
 * @subpackage	System.vmcache
 */
$GLOBALS['vmcachestart'] = microtime(true); 
//JLoader::register('JRoute', JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'vmcache'.DIRECTORY_SEPARATOR.'methods.php');

class plgSystemVmcache extends JPlugin
{

	static $_cache;
	static $_instance; 
	static $_urls; 
	var $https_override = false; 
	static $ttl; 
	private $token = ''; 
	static $setCaching;
	public static function &getInstance()
	{
	  return self::$_instance; 
	}
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param	array	$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		//Set the language in the class
		$config = JFactory::getConfig();
		$options = array(
			'defaultgroup'	=> 'page',
			'browsercache'	=> false,
			'caching'		=> false,
			'storage'		=> 'file',
			'checkTime'		=> false,
			'locking'		=> true, 
			
			
		);
		
		
		self::$setCaching = true; 
		///self::$_cache = \JCache::getInstance('page', $options);
		self::$_cache = JFactory::getCache();
		
		self::$_instance =& $this; 
		$token  = \JSession::getFormToken();
		$this->token = $token; 
		
	}
	
	function onExtensionAfterSave($tes2, $test)
	{
	
	  if (empty($test)) return; 
	  if (!is_object($test)) return; 
	 
	  if (!(($test->element === 'vmcache_last') || ($test->element === 'vmcache')))
	  return; 
	
	  if ($test->folder !== 'system') return; 
	  $test->ordering = -999; 
	  if (method_exists($test, 'store'))
	  $test->store(); 
	  
	  $isEnabled = $test->enabled; 
	  // update other plugin
	  $db = JFactory::getDBO(); 
	  
	  $q = 'select (min(ordering)-100) as min from #__extensions where 1'; 
	  
	  $db->setQuery($q); 
	  $min = (int)$db->loadResult(); 
	  
	  $q = 'select (max(ordering)+100) as min from #__extensions where 1'; 
	  $db->setQuery($q); 
	  $max = (int)$db->loadResult(); 
	  
	  // update other plugin
	 
	  $q = 'update #__extensions set `enabled` = '.(int)$test->enabled.', `ordering` = '.$min.' where `element` = "vmcache" and `folder` = "system" limit 1'; 
	  $db->setQuery($q); 
	  $db->execute($q); 
	  

	  
	  $db = JFactory::getDBO(); 
	  $q = 'update #__extensions set `enabled` = '.(int)$test->enabled.', `ordering` = '.$max.' where `element` = "vmcache_last" and `folder` = "system" limit 1'; 
	  $db->setQuery($q); 
	  $db->execute($q); 
	  
	 
	 
	 $x = JRequest::getVar('routercache', false); 
	  if (!empty($x))
	   {
	      if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'defines.php'))
		   {
		      jimport( 'joomla.filesystem.file' );
			  $x = @JFile::copy(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'vmcache'.DIRECTORY_SEPARATOR.'defines.php', JPATH_SITE.DIRECTORY_SEPARATOR.'defines.php'); 
			  if ($x === false)
			   {
			      JFactory::getApplication()->enqueueMessage(JText::_('PLG_VMCACHE_COPY_ERROR'), 'error');
			   }
		      
		   }
	   }
	   else
	   {
	      if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'defines.php'))
		   {
		     //check if it's us: 
			 $data = file_get_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'defines.php'); 
			 if (stripos($data, 'vmcache')!==false)
		    {
		      JFile::delete(JPATH_SITE.DIRECTORY_SEPARATOR.'defines.php'); 
			 
		    }
	   
		   }
	   }
	 
	}
	function setDimensions(&$arr)
	{
	  $app = JFactory::getApplication();
	  $template = $app->getTemplate(); 
	  
		
		// per URL address:
		//$app->registeredurlparams->mycacheurl = 'ALNUM'; //md5(serialize(JRequest::getURI()));
			   jimport('joomla.filesystem.file');
			   $template = JFile::makeSafe($template); 
		if (file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'productquery.php'))
		{
		$where = array(); 
		
		// to add further dimensions create this file: 
		include(JPATH_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'productquery.php'); 
		// and return $where as an array
		// 
		$hash = md5(implode('and', $where)); 
		$w = substr($hash, 0, 7);
		$arr['stocksystem'] =  $w; 
		}
	    
	}
	public function plgDisablePageCache($reason='') {
	   
	   self::$_cache->setCaching(false);
	   self::$setCaching = false; 
	}
	//rs forms used: 
	public function rsfp_f_onAfterShowThankyouMessage($formId) {
		self::$_cache->setCaching(false);
		
	}
	public function rsfp_f_onSwitchTasks() {
		self::$_cache->setCaching(false);
		
	}
	public function rsfp_f_onBeforeFormValidation($arr) {
		self::$_cache->setCaching(false);
		
	}
	public function rsfp_onAfterManageDirectoriesQuery($formId) {
		self::$_cache->setCaching(false);
		
	}
	//end rs forms used: 
	function getDimensions()
	{
	  $detectMobiles = $this->params->get('detectmobiles', false); 
	  $ret = array(); 
	  if (!empty($detectMobiles))
	  {
	    $this->loadMobileDetect(); 
	  
	   $type = 'desktop'; 
	   $app = JFactory::getApplication(); 
	   $changeTplRequest = $app->getUserStateFromRequest('jtpl', 'jtpl', -1, 'int');
	   
	   if ($changeTplRequest >= 0) 
	   {
	    $ismobile = true; 
	    $type = 'mobile'; 
		define('VMCACHE_DETECTED_DEVICE', $type); 
	   }
	   else
	   if (!defined('VMCACHE_DETECTED_DEVICE'))
	   {
		   
		   
		{
		if ($type == 'desktop')
		{
			
			
			
		    if(!class_exists('uagent_info')){
		    if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR.'mdetect.php'))
			{
    		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR.'mdetect.php');
			}
			else 
			{
				require_once(__DIR__.DIRECTORY_SEPARATOR.'mdetect.php');
			}
    	}
		
		if (class_exists('uagent_info'))
		{
    	$ua = new uagent_info();
    	$isMobile = false; 
    	//if($ua->DetectMobileQuick()){
		if ($ua->DetectMobileLong()) {
		    
    		define('VMCACHE_DETECTED_DEVICE', 'mobile');
    		$isMobile = true;
			
			
    	}
		else
    	if ($ua->DetectTierTablet() ){
    		define('VMCACHE_DETECTED_DEVICE', 'tablet');
    		$isMobile = true;
    	}
    	else
    	if($isMobile == false){
    		define('VMCACHE_DETECTED_DEVICE', 'desktop');
    	}
		$type = VMCACHE_DETECTED_DEVICE; 
		
		}
		}
		}
		   
		   
		   if (!defined('VMCACHE_DETECTED_DEVICE')) {
		   
	   if (!class_exists('MobileDetector'))
	   {
	     if (file_exists(JPATH_ADMINISTRATOR . '/components/com_yagendooproductmanager/lib/helper.mobile_detector.php'))
		 require_once(JPATH_ADMINISTRATOR . '/components/com_yagendooproductmanager/lib/helper.mobile_detector.php'); 
	   }
	   
	   
	   if (class_exists('MobileDetector'))
	    {
		 //JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'yagendooismobile'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'Mobile_Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php'
		  if (!class_exists('Mobile_Detect'))
		  {
		    if (file_exists(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'yagendooismobile'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'Mobile_Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php'))
		    require_once(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'yagendooismobile'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'Mobile_Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php');
		    else
		    if (file_exists(JPATH_ADMINISTRATOR . '/components/com_yagendooproductmanager/lib'.DIRECTORY_SEPARATOR.'Mobile_Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php'))
		    require_once(JPATH_ADMINISTRATOR . '/components/com_yagendooproductmanager/lib'.DIRECTORY_SEPARATOR.'Mobile_Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php'); 
		  }
		  
		  if (method_exists('MobileDetector', 'isTablet'))
		   if (MobileDetector::isTablet()) 
		    {
			$type = 'tablet'; 
			define('VMCACHE_DETECTED_DEVICE', $type); 
			}
			
			if (!defined('VMCACHE_DETECTED_DEVICE'))
			if (method_exists('MobileDetector', 'isMobile'))
		   {
		      if (MobileDetector::isMobile()) 
			  {
			  
			  $type = 'mobile'; 
			  define('VMCACHE_DETECTED_DEVICE', $type); 
			  }
			  
		   }
		   
		 
		
		}
		
		   }
		
		 
		}
		
		if (defined('VMCACHE_DETECTED_DEVICE'))
		$type = VMCACHE_DETECTED_DEVICE;
		
		$ret['device_type'] = $type; 
		}
		
		$this->setDimensions($ret); 
		
		return $ret; 
	}
	
	function getIdAndGroup(&$id=0, &$group='', &$filepath='')
	{
	   $uri = JRequest::getURI();
	   $rep = array('http://', 'https://'); 
	   $repWith = array('//', '//'); 
	   $uri = str_replace($rep, $repWith, $uri); 
	   
	   //error_log(__LINE__.'vmcache get: '.$uri); 
	   
	   if (is_string($uri)) {
		   $id = md5($uri); 
	   }
	   else {
	     $id = md5(serialize(JRequest::getURI())); 
	   }
	   
	   $lang = JFactory::getLanguage()->getTag(); 
	   
	   //$session = JFactory::getSession(); 
	   
	   $arr = $this->getDimensions(); 
	   //$this->setDimensions($arr); 
	   
	   $dir = ''; 
	   jimport('joomla.filesystem.file');

	   $lang = JFile::makeSafe($lang); 
	   foreach ($arr as $key=>$val)
	   {
	     $dir .= JFile::makeSafe('_'.$key.'_'.$val); 
	   }
	   $group = 'vmcache_'.$lang.$dir; 
	   
	   
	   $filepath = JPATH_CACHE.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR.$id.'.html'; 
	   
	   //error_log(__LINE__.'filepath: '.$filepath); 
	}
	
	function loadMobileDetect() {
	
	}
	function setCaching($checkCaching=false)
	{
		
		
		
	    if (php_sapi_name() === 'cli') {
			self::$_cache->setCaching(false);
			self::$setCaching = false; 
			return false; 
		}
		//by default this must be initialized in the constructor, once set to false, it's false always
		if (empty(self::$setCaching)) {
			self::$_cache->setCaching(false);
			self::$setCaching = false; 
			return false; 
		}
		
	    $this->https_override = $this->params->get('https_override', false); 
		self::$ttl = $this->params->get('xcache', 120); 
		
	    $app  = JFactory::getApplication(); 
		
		$z = $app->isAdmin(); 
		
		$debug = $this->params->get('debug', false); 
		if ($app->isAdmin() || JDEBUG) {
		     self::$_cache->setCaching(false);
			 self::$setCaching = false;  
			 if ($debug) {
			   JFactory::getApplication()->enqueueMessage('vmcache disabled: is admin or debug'); 
			 }
			 
			return false;
		}
		
		
		
		
		if ($checkCaching)
		{
		 $enabled = self::$_cache->options['caching']; 
		 if (empty($enabled)) {
			 
			  if ($debug) {
			   JFactory::getApplication()->enqueueMessage('vmcache disabled: caching disabled'); 
			 }
			 
			 return false; 
		 }
		}
		
		$session = JFactory::getSession();
		$data = $session->get('com_rsform'); 
		if ((is_object($data)) && (!empty($data->formparams))) {
			return false; 
		}
		
		
		if (count($app->getMessageQueue())) {
		     self::$_cache->setCaching(false);
			 self::$setCaching = false; 
			 if ($debug) {
			   JFactory::getApplication()->enqueueMessage('vmcache disabled: messages in the queue'); 
			 }
			 
			return false;
		}
		
		
		if ($_SERVER['REQUEST_METHOD'] != 'GET')
		 {
			 if ($debug) {
			   JFactory::getApplication()->enqueueMessage('vmcache disabled: method not GET'); 
			 }
			 
		   self::$_cache->setCaching(false);
		   self::$setCaching = false; 
			return false;
		 }
		 
		$user = JFactory::getUser(); 
		$user_id = $user->get('id'); 
		
		
		
		$session = JFactory::getSession(); 
		
		
		
		//loads virtuemart: 
		if (!defined('VIRTUEMART_INSTALLED'))
		if (!file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'))
		{
		  define('VIRTUEMART_INSTALLED', false); 
		}
		else
		define('VIRTUEMART_INSTALLED', true); 
		
		if (VIRTUEMART_INSTALLED)
		{
			/*
		if (!class_exists('VmConfig'))	  
		{
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		}
		VmConfig::loadConfig(); 
		
		
		if(!class_exists('shopFunctionsF'))
		require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php');
		
		if (!class_exists('VmImage'))
		{
			if (!defined('VM_VERSION'))
			require(JPATH_VM_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'image.php');
		}
		*/
		$sgcheck = $this->params->get('checkshopergroups', false); 
		
		if (!empty($sgcheck))
		{
		$session = JFactory::getSession(); 
		$a1 = $session->get('vm_shoppergroups_add',array(),'vm'); 
		$a1m = array(); 
		foreach ($a1 as $g)
		 {
		   $a1m[$g] = $g; 
		 }
		$a2 = $session->get('vm_shoppergroups_remove',array(),'vm'); 
		$a2m = array(); 
		foreach ($a2 as $g2)
		 {
		   $a2m[$g2] = $g2; 
		 }
		foreach ($a2m as $sr)
		{
		  if (in_array($sr, $a1m)) unset($a1m[$sr]); 
		}
		
		// if we have more then one current shopper group, do not cache: 
		if (!empty($a1m))
		if (count($a1m)>1)
		 {
		   self::$_cache->setCaching(false);
		   self::$setCaching = false; 
		   
		   if ($debug) {
			   JFactory::getApplication()->enqueueMessage('vmcache disabled: more than one shopper group found'); 
			 }
		   
		   return false; 
		 }
		 else
		 {
		  //default shopper group for anonymous must be 1, if not, caching will not work !!!
		   if (isset($a1m[2]))
		   {
		    // do nothing for default 
		   }
		   else
		   if (!isset($a1m[1]))
		    {
			
				self::$_cache->setCaching(false);
				self::$setCaching = false; 
				
				if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: non default shopper group'); 
				}
			 
				return false; 
			
			}
		 }
		}
		
		
		
		
		
		/*
		 if (!class_exists('VirtueMartCart'))
	     require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php');
	   
		$cart = VirtueMartCart::getCart(false); 
		*/
		$session = JFactory::getSession();
				$cartSession = $session->get('vmcart', 0, 'vm');
				if (!empty($cartSession)) {
					$sessionCart = (object)json_decode( $cartSession ,true);
		
	    //cartData
	    if (!empty($sessionCart->cartProductsData))
		{
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: cart not empty'); 
				}
		
		return false; 
		}
				}
		$tmpl = JRequest::getVar('virtuemart_userinfo_id', ''); 
		if (!empty($tmpl)) {
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: user_id in URL'); 
				}
		
		return false;
		} 
		// end of virtuemart section
		}
		
		if ($app->getName() != 'site') {
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
			if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: app is not site'); 
				}
			
			return false;
		}
		
		$option = JRequest::getVar('option', ''); 
		$disabled = array('com_users', 'com_onepage'); 
		if (in_array($option, $disabled))
		{
		  self::$_cache->setCaching(false);
		  self::$setCaching = false; 
		  
		  if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this component'); 
				}
		  
		  return false; 
		}
		
		
		
		
		$format = JRequest::getVar('format', 'html'); 
		if ($format != 'html') 
		{
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
				  if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for non html format'); 
				}
		
		return false;
		}

		$doc = JFactory::getDocument(); 
		$class = strtoupper(get_class($doc)); 
		
		$arr = array('JDOCUMENTHTML', 'JOOMLA\CMS\DOCUMENT\HTMLDOCUMENT'); 
		
		if (!in_array($class, $arr)) {
		{
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for non html format page'); 
				}
		
		return false; 
		}
		}
		$doc = JFactory::getDocument(); 
		if (method_exists($doc, 'getType')) {
			$type = $doc->getType(); 
			if (($type !== 'html')) {
				self::$_cache->setCaching(false);
				self::$setCaching = false; 
				return false; 
			   
			}
		}
		
		
		$format = JRequest::getVar('format', 'html'); 
		if ($format != 'html') {
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for non html format URL'); 
				}
		
		return false;
		}
		
	
		
		$tmpl = JRequest::getVar('tmpl', ''); 
		if (!empty($tmpl)) {
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for non standard template'); 
				}
		
		return false;
		} 
		
		$tmpl = JRequest::getVar('nosef', ''); 
		if (!empty($tmpl)) {
		self::$_cache->setCaching(false);
		self::$setCaching = false; 
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for nosef urls'); 
				}
		
		return false;
		} 
		
		
		
		// check disabled components, options
		$options = $this->params->get('disabledoptions', ''); 
		if (!empty($options))
		{
		
		$newopt = $this->parseCommas($options); 
			
		
		
		$co = JRequest::getVar('option', null); 
		if (in_array($co, $newopt))
		 {
			 
			 
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
			if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this componet per vmcache options'); 
				}
			
			return false; 
		 }
		}
		
		// disabled views by default: 
		$view = JRequest::getVar('view', ''); 
		$views = array('cart', 'invoice', 'opc', 'orders', 'pdf', 'pluginresponse', 'user', 'login', 'logout'); 
		if (in_array($view, $views))
		 {
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
			if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this view per vmcache options'); 
				}
			
			return false; 
		 }
		 
		 
		 $ctrl = JRequest::getVar('ctrl', ''); 
		$views = array('cart', 'invoice', 'opc', 'orders', 'pdf', 'pluginresponse', 'user', 'login', 'logout', 'checkout', 'address'); 
		if (in_array($ctrl, $views))
		 {
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
			if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this view per vmcache options'); 
				}
			
			return false; 
		 }
		
		
		// check disabled views
		$options = $this->params->get('disabledviews', ''); 
		if (!empty($options))
		{
			$newopt = $this->parseCommas($options); 
		
		
		$co = JRequest::getVar('view', null); 
		if (in_array($co, $newopt))
		 {
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
			if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this view and component'); 
				}
			
			return false; 
		 }
		 
		 $layout = JRequest::getVar('layout', ''); 
		 if (in_array($layout, $newopt))
		 {
		    self::$_cache->setCaching(false);
			self::$setCaching = false; 
			
				if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: disabled for this layout'); 
				}

			
			return false; 
		 } 
		 
		}
		
		
		if (empty($user_id))
		if ($user->get('guest')) {
		    // this plugin works ONLY when the cart is empty and the user is not logged in
			
			
			self::$_cache->setCaching(true);
			self::$setCaching = true; 
			return true; 
		}
		
		if ($debug) {
				  JFactory::getApplication()->enqueueMessage('vmcache disabled: logged in user'); 
				}
		
		return false; 
		
	}
	function checkGetData() {
		$app	= JFactory::getApplication();
		
		if (!$this->setCaching()) return; 
		
		
		
		$id = 0; $group = ''; $fn = ''; $this->getIdAndGroup($id, $group, $fn); 
		
		//error_log("\n\nVmCache");
		//error_log(__LINE__.'vmcache before get: '.$id.' '.$group.' '.$fn); 
		self::$_cache->setCaching(true);
		
		
			   if (strpos(JVERSION, '-')!==false) {
					$ja = explode('-', JVERSION); 
					$jversion = $ja[0]; 
				}
				else {
					$jversion = JVERSION;
				}
		
		$data = false; 	
		if(version_compare($jversion,'3.0.0','ge'))  {
		if (file_exists($fn)) {
			$data = file_get_contents($fn); 
			if (empty($data)) return; 
		    $token = $this->token; 
		    $data = str_replace('{token}', $token, $data); 
			
			$ct = microtime(true) - $GLOBALS['vmcachestart']; 
			$ct = $ct * 100; 
			$ct = number_format($ct, 2, '.', ''); 
		    $data .= '<!-- Vm Cache Load: '.$ct.'ms -->'; 
		    //error_log('RET DATA LOADED '.var_export($data, true)); 
			echo $data; 
			$app->close(); 
		
		}
		}
		else {
			$data  = self::$_cache->get($id, $group, false);
		}
	
		
		
		
		if (($data !== false) && (!is_null($data)))
		{
			
			
			JResponse::setBody($data);
			
			echo JResponse::toString(false);
			$app->close();
		}
	}

	function onAfterInitialise()
	{
		$this->checkGetData(); 
	}
	
	public function onAfterRoute()
	{
		
	    $this->checkGetData(); 
	    if (!$this->setCaching()) return; 
	}
	
	function callAtShutDown()
	{
	 
		
	   $a3 = $this->params->get('useasync', false); 
	   $a1 = function_exists('fastcgi_finish_request'); 
	   
	   if ((!$a3) || (!$a1))
	    {
		  $this->_storeUrlCache(); 
		  $this->_storeCache(); 
		  
		  return; 
		}
		else
		if (!empty($a3) && (!empty($a1)))
		{
		
		 fastcgi_finish_request(); 
		 $this->_storeUrlCache(); 
		 $this->_storeCache(); 
		

		}
		else
		{
		$this->_storeUrlCache(); 
		$this->_storeCache(); 
	    }
	   
	}
	public function _storeUrlCache()
	{
	

	   if (!empty(plgSystemVmcache::$_urls))
		 {
	  if (!file_exists(JPATH_CACHE.DIRECTORY_SEPARATOR.'vmcache_urls'))
		 {
		    jimport( 'joomla.filesystem.folder' );
			JFolder::create(JPATH_CACHE.DIRECTORY_SEPARATOR.'vmcache_urls'); 
		 }
		 
		
		    foreach (plgSystemVmcache::$_urls as $k=>$v)
			 {
			   jimport( 'joomla.filesystem.file' );    
			   if (JFile::write(JPATH_CACHE.DIRECTORY_SEPARATOR.'vmcache_urls'.DIRECTORY_SEPARATOR.$k.'.lock', $v)!==false)
			   {
			    if (!file_exists($k))
			    JFile::move(JPATH_CACHE.DIRECTORY_SEPARATOR.'vmcache_urls'.DIRECTORY_SEPARATOR.$k.'.lock', JPATH_CACHE.DIRECTORY_SEPARATOR.'vmcache_urls'.DIRECTORY_SEPARATOR.$k.'.html'); 
			   }
			 }
		 }
	}
	
	public function modifyDataBeforeStore(&$data) {
		    $https_override = $this->params->get('https_override', false); 
			
			$rep = array(); 
			$repWith = array(); 
			
			if ($https_override) {
				$rep[] = 'src="http://';
				$repWith[] = 'src="//';
				
				$rep[] = 'href="http://';
				$repWith[] = 'href="https://';
			   
			}
			
			if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
				$rep[] = 'base href="http://'; 
				$repWith[] = 'base href="https://';
			}
			
		    $ct = microtime(true) - $GLOBALS['vmcachestart']; 
			$ct = $ct * 100; 
			$ct = number_format($ct, 2, '.', ''); 
		    $data .= '<!-- Vm Cache Store: '.$ct.'ms -->'; 
			
			
			$token = $this->token; 
			
			$rep[] = $token; 
			$repWith[] = '{token}'; 
			
		    $data = str_replace($rep,$repWith, $data); 
	}
	
	
	public function _storeCache()
	{
		
	   
	   if (!isset(plgSystemVmcache::$setCaching)) return; 
	   if (empty(plgSystemVmcache::$setCaching)) return; 	
	
	   $cache = self::$_cache->getCaching();
	   $id = 0; $group = ''; $fn = ''; $this->getIdAndGroup($id, $group, $fn); 
	   
	   if (strpos(JVERSION, '-')!==false) {
			$ja = explode('-', JVERSION); 
			$jversion = $ja[0]; 
		}
		else {
			$jversion = JVERSION;
		}

		if(version_compare($jversion,'3.0.0','ge'))  {
			$data = \JFactory::getApplication()->getBody();
			$search_buffer = $this->params->get('disabledbuffersearch', ''); 
			
			if (!empty($search_buffer)) {
				$search = $this->parseCommas($search_buffer); 
				
				foreach ($search as $kw) {
					if (strpos($data, $kw) !== false) return; 
				}
			}
			//error_log('before store: '.$id.' '.$group); 
		    //self::$_cache->store($data, $id, $group, false);
			if (!file_exists(JPATH_CACHE.DIRECTORY_SEPARATOR.$group)) {
				jimport( 'joomla.filesystem.folder' );
				if (\JFolder::create(JPATH_CACHE.DIRECTORY_SEPARATOR.$group)===false) return;
			}
			
			$this->modifyDataBeforeStore($data); 
			
			
			
			$fn_temp = $fn.'.'.rand().'.tmp';
			jimport( 'joomla.filesystem.file' );    
			if (JFile::write($fn_temp, $data) !== false) {
			  JFile::move($fn_temp, $fn); 
			}
			
		}
		else {
			self::$_cache->store();
		}
	   
	   
	   
	}
	
    function parseCommas($options) {
		$newopt = array(); 
		$a = explode(',', $options);
		{
		  if (!empty($a))
		   {
		     foreach ($a as $opt)
			  {
			     $newopt[] = trim($opt); 
			  }
		   }
		   else
		   {
		     if (!empty($options))
			  $newopt[] = trim($options); 
		   }
		}
		return $newopt;
	}
	
	function onAfterRenderRemoved()
	{
		if (!$this->setCaching(true)) return; 
		
		
		$enabled = self::$_cache->options['caching']; 
		if (empty($enabled)) return false; 
		
		
		
		$user = JFactory::getUser();
		$user_id = $user->get('id'); 
		
		$id = 0; $group = ''; $this->getIdAndGroup($id, $group); 
		
		$a1 = function_exists('fastcgi_finish_request'); 
		$a2 = function_exists('register_shutdown_function'); 
		
		if (((!$this->params->get('useasync', false)) || (!$a1)) && (!$a2))
		{
		
			$this->_storeCache(); 
			return; 
			
		
		}
		else
		{
		   register_shutdown_function(array($this, 'callAtShutDown')); 
		}
	}
}
