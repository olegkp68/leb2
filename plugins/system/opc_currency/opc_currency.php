<?php
/**
 * @version		opc_currency.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;


	  
jimport('joomla.plugin.plugin');

class plgSystemOpc_currency extends JPlugin
{
	
	static $post_virtuemart_currency_id; 
	
    function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAfterInitialise() {
	   if ((!empty($_GET)) && (!empty($_GET['carttmpl'])) && (!empty($_GET['virtuemart_currency_id']))) {
	      
	   }	
	  if ((!empty($_POST)) && (count($_POST) === 1) && (!empty($_POST['virtuemart_currency_id']))) {
		 
		self::$post_virtuemart_currency_id = (int)$_POST['virtuemart_currency_id']; 
	  }
	  
	   
	}
	
	
	function plgSetCurrency(&$cart=null) {
		if (!$this->_check()) return; 
		$currency_switch = OPCconfig::get('currency_switch', 1); 
		$this->checkSetCurrency($cart); 
	}
	
	function parseTestIPs() {
		 if (!empty($this->params) && (method_exists($this->params, 'get'))) {
			$testIPs = $this->params->get('testIPs', ''); 
			
			
			if (stripos($testIPs, ',') === false) {
				if (strpos($testIPs, ':') === false) return array(); 
				$ea = explode(':', $testIPs); 
				if (count($ea) !== 2) return array(); 
			
			
				$ip = trim($ea[0]); 
				$country = trim($ea[1]); 
				return array ($ip => $country); 
				
			}
			else {
				$ret = array(); 
				$ex = explode(',', $testIPs); 
				foreach ($ex as $item) {
					if (strpos($testIPs, ':') === false) continue; 
					$ea = explode(':', $item); 
					if (count($ea) !== 2) continue;
					
					$ip = trim($ea[0]); 
					if (empty($ip)) continue; 
					$country = trim($ea[1]); 
					if (empty($country)) continue; 
					$ret[$ip] = $country; 
				}
				return $ret; 
			}
		 }
		 return array(); 
	}
	
	
	private function _check() {
		 $app = JFactory::getApplication(); 
		 if ($app->isAdmin()) return false; 
		 if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR); 
		 
		 if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'))
		 return false; 
	 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		
		return true; 
	}
	
	function onAfterRoute()
	{
		if (!$this->_check()) return; 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		//this loads checkSetCurrency:
		//OPCmini::getCart(); 
		$this->checkSetCurrency(); 
	}
	
    function checkSetCurrency(&$cart=null)
	{
		if (!$this->_check()) return; 
		
	 
	 
	 
	 
	 // opc is requried: 
	
 	 
	 
	 
	 $currency_per_lang = OPCconfig::get('currency_per_lang', array()); 
	 $currency_switch = OPCconfig::get('currency_switch', -1); 
	 $currency_switch = (int)$currency_switch; 
	 
	 
	 if ($currency_switch === 1)
	 {
	   if (!file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_geolocator'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'helper.php')) 
	   {
		   return;
	   }		   
	   
	   include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_geolocator'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'helper.php');
	   
	 }
	 

	 
	 
	 
	 $can_change = OPCconfig::getValueNoCache('currency_config', 'can_change', 0, true, false); 
	 
	 
	 
	  $session = JFactory::getSession(); 
	 if (empty($can_change))
	 {
	
	 if (!empty(self::$post_virtuemart_currency_id)) {
		 $ci = (int)self::$post_virtuemart_currency_id;
	 }
	 else {
	   $ci = JRequest::getInt('virtuemart_currency_id', 0); 
	 }
	
	 
	 $app = JFactory::getApplication(); 
	
	 if (!empty($ci))
	  {
		  
		  
	    // user can change currency, so let's change it: 
	  
	    //currency was set elsewhere
		
		$session->set('opc_currency', $ci); 
		// set global request variable
		$c_int = (int)$ci; 
		JRequest::setVar('virtuemart_currency_id', $c_int); 
	 
		$app->setUserState('virtuemart_currency_id', $c_int); 
		$app->setUserState('com_virtuemart.virtuemart_currency_id', $c_int); 
		$this->setCurrency($c_int); 
		
		$debug = $this->params->get('debug'); 
		if (!empty($debug)) {
		$app->enqueueMessage('OPC currency debug: currency was set from GET/POST, currency ID: '.$c_int); 
		}
		$this->setCurrency($c_int); 
		return; 
	  }
	  
	  // user is not currently changing the currency
	  
	  //$ci2 = $session->get('opc_currency');
      //if (!empty($ci2)) return; 	  
	 }
	 
	 
	 //debug: 
	 $c_int = $session->get('opc_currency', null);
	 
	 $tag = JFactory::getLanguage()->getTag(); 
	 $tag_old = $session->get('opc_last_language', $tag); 
	 $redo = false; 
	 if (($tag_old != $tag) && ($currency_switch === 2)) {
	    $redo = true; 
		
	 }
	 if ($currency_switch === 3) {
		 $redo = true; 
	 }
	 
	 if ((empty($c_int)) || ($redo))
	 {
	 if ($currency_switch === 1)
	 {
	 
	 $c2c_override = ''; 
	 $devIPs = $this->parseTestIPs(); 
	 
	 
	 if (isset($devIPs[$_SERVER['REMOTE_ADDR']])) {
		 
		 $c2c_override = $devIPs[$_SERVER['REMOTE_ADDR']]; 
		 $debug = $this->params->get('debug'); 
		 if (!empty($debug)) {
		 JFactory::getApplication()->enqueueMessage('OPC currency debug: Developer IP detected, country set to '.$c2c_override); 
		 }
	 }
	 
	 
	 
	 if (empty($c2c_override)) {
	 if (class_exists('geoHelper'))  {
			$c2c = geoHelper::getCountry2Code(); 
		}
	 }
	 else {
		 $c2c = $c2c_override; 
	 }
	 
	 
	 
		 
	 
	 
	 
	 if (empty($c2c)) {
		 
		 $debug = $this->params->get('debug'); 
		if (!empty($debug)) {
				$app->enqueueMessage('OPC currency debug: geolocator is not installed  ! '); 
		}
		 
		 return; 
	 }
	 
	 
	 
	 
	 
	 $default = 0; 
	 $c_int = OPCconfig::getValueNoCache('currency_config', $c2c, 0, $default); 
	 
	 $debug = $this->params->get('debug'); 
		if (!empty($debug)) {
				$app->enqueueMessage('OPC currency debug: geolocator country is '.$c2c.' for IP '.$_SERVER['REMOTE_ADDR'].' and currency returned: '.$c_int.' '); 
		}
	 
	 
	 }
	 else
	 if ($currency_switch === 2)
		 {
			 
			 
			 
			 if (isset($currency_per_lang[$tag]))
			 {
				  $c_int = (int)$currency_per_lang[$tag]; 
				  
				  $debug = $this->params->get('debug'); 
				  if (!empty($debug)) {
				    $app->enqueueMessage('OPC currency debug: currency per language: '.$tag.': '.$c_int); 
				  }
				  if ($redo) {
				    $session->set('opc_currency', $c_int); 
				  }
				  
				  $session->set('opc_last_language', $tag); 
				  
			 }
			 else
			 {
				  $debug = $this->params->get('debug'); 
				  if (!empty($debug)) {
				  $app->enqueueMessage('OPC currency debug: currency per language, language tag not found: '.$tag.': '.$c_int); 
				  }
			 }
			 
			 
			
		 }
	 }
	 else {
		 $debug = $this->params->get('debug'); 
		if (!empty($debug)) {
		  $app->enqueueMessage('OPC currency debug: currency was already decided, clear your cookies to see the page as the first time visitor'); 
		}
	 }
	//$arr = array ('lang'=>JRequest::getVar('lang'), 'language'=>JRequest::getVar('language'), 'switchlang'=>JRequest::getVar('switchlang'), 'post'=>$_POST); 
	//echo json_encode($arr); 
	
	 if ($currency_switch === 3) {
		 $cid = 0; 
		 if (!empty($cart)) {
			 
			 $cid = JRequest::getInt('shipto_virtuemart_country_id', JRequest::getInt('virtuemart_country_id', 0)); 
			 if (empty($cid)) {
			 if (method_exists($cart, 'getST')) {
			   $st = $cart->getST(); 
			   if (isset($st['virtuemart_country_id'])) {
			    $cid = $st['virtuemart_country_id']; 
			   }
			 }
			 if (empty($cid))
			 if (!empty($cart->ST)) {
				 if (!empty($cart->ST['virtuemart_country_id'])) {
					 $cid = $cart->ST['virtuemart_country_id']; 
				 }
			 }
			 if ((empty($cid)) && (!empty($cart->BT['virtuemart_country_id']))) {
				 $cid = $cart->BT['virtuemart_country_id']; 
				 
			 }
			 }
			 
		 }
		 if (!empty($cid)) {
			 static $cache; 
			 if (empty($cache[$cid])) {
				$db = JFactory::getDBO(); 
				$q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$cid; 
				$db->setQuery($q); 
				$c2c = $db->loadResult(); 
			 }
			 else {
				 $c2c = $cache[$cid]; 
			 }
			  $default = 0; 
			 $c_int = (int)OPCconfig::getValueNoCache('currency_config', $c2c, 0, $default); 
			 $debug = $this->params->get('debug'); 
			 if (!empty($debug)) {
					$app->enqueueMessage('OPC currency debug: cart country is '.$c2c.'  and currency returned: '.$c_int.' '); 
			 }
			 
			 if (!empty($c_int)) {
				 $session->set('opc_currency', $c_int);
				 self::$post_virtuemart_currency_id = $c_int; 
			 }
			
			 
		 }
		 
		 
	 }
	 
	 
	 $c_int = (int)$c_int; 
	 if (empty($c_int)) return; 
	 
	 $debug = $this->params->get('debug'); 
		if (!empty($debug)) {
	 $app->enqueueMessage('OPC currency debug: current currency is '.$c_int); 
		}
	 $this->setCurrency($c_int); 
	 
	 
	// $virtuemart_currency_id = $app->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getVar('virtuemart_currency_id',$currencyDisplay->_vendorCurrency) );
	 
	}
	
	public function setCurrency($c_int, &$cart=null)
	{
		
		if (!class_exists('VmConfig')) 
		 require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	 
		
		 
		  

		
		 // set global request variable
	 JRequest::setVar('virtuemart_currency_id', $c_int); 
	 JRequest::setVar('virtuemart_currency_id', $c_int, 'get'); 
	 JRequest::setVar('virtuemart_currency_id', $c_int, 'post'); 
	 JRequest::setVar('virtuemart_currency_id', $c_int, 'request'); 
	 
	 $app = JFactory::getApplication(); 
	 $app->setUserState('virtuemart_currency_id', $c_int); 
	 $app->setUserState('com_virtuemart.virtuemart_currency_id', $c_int); 
	 $app->input->set('virtuemart_currency_id', $c_int, 0); 
	 $app->input->set('virtuemart_currency_id', $c_int, 'none'); 
	 
	 
	 
	 
	 
	 VmConfig::loadConfig(); 
	 
	 if (!class_exists('VirtuemartCart'))
	 require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cart.php'); 
	 
	 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	 /*$cart = VirtuemartCart::getCart(); 
	 $cart = OPCmini::getCart(); 
	 */
	 if (empty($cart)) {
	   $cart = VirtuemartCart::getCart();
	 }
	 $cart->pricesCurrency = $c_int; 
	 
	
	 
	 
	 
	 if (!class_exists('CurrencyDisplay')) 
	 require(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .  DIRECTORY_SEPARATOR. 'helpers' . DIRECTORY_SEPARATOR . 'currencydisplay.php');
	 $cd = CurrencyDisplay::getInstance($c_int); 
	 
	 
	 
	  
	 $cd = CurrencyDisplay::getInstance(0, 1); 
	 $cd = CurrencyDisplay::getInstance(); 
	 if ($cd->getCurrencyForDisplay() !== $c_int) {
		 CurrencyDisplay::$_instance = array(); 
		 $cd = CurrencyDisplay::getInstance(); 
		 
	 }
	 if ($c_int !== 26) {
		
	 }
	 //
	 
	 
	}
	
	
	public function onAfterRender()
	{
		 $buffer = JResponse::getBody();
		 $x = stripos($buffer, '</body'); 
		 
		 if ($x !== false)
		 {
			 $code = $this->_getLangCode(); 
			 if (empty($code)) return; 
			 $ins = '<script>
//<![CDATA[  
var vmLang = \'&lang='.$code.'\'; 
//]]>
</script>'; 

			 $buffer = substr($buffer, 0, $x).$ins.substr($buffer, $x); 
			 JResponse::setBody($buffer);
		 }
		
	}
	
	private function _getLangCode()
 {
	 $langO = JFactory::getLanguage();
			$lang = JRequest::getVar('lang', ''); 
			$locales = $langO->getLocale();
		$tag = $langO->getTag(); 
		$app = JFactory::getApplication(); 		
		
		
		if (class_exists('JLanguageHelper') && (method_exists('JLanguageHelper', 'getLanguages')))
		{
		$sefs 		= JLanguageHelper::getLanguages('sef');
		foreach ($sefs as $k=>$v)
		{
			if ($v->lang_code == $tag)
			if (isset($v->sef)) 
			{
				$ret = $v->sef; 

				return $ret; 
			}
		}
		}
		
		
		
			 if ( version_compare( JVERSION, '3.0', '<' ) == 1) {       
			if (isset($locales[6]) && (strlen($locales[6])==2))
			{
				$action_url .= '&amp;lang='.$locales[6]; 
				$lang = $locales[6]; 
				return $lang; 
			}
			else
			if (!empty($locales[4]))
			{
				$lang = $locales[4]; 
				
				if (stripos($lang, '_')!==false)
				{
					$la = explode('_', $lang); 
					$lang = $la[1]; 
					if (stripos($lang, '.')!==false)
					{
						$la2 = explode('.', $lang); 
						$lang = strtolower($la2[0]); 
					}
				
					
				}
		     	return $lang; 
			}
			else
			{
				return $lang; 
			
			}
			 }
			return $lang; 
 }
 
	
		
}
