<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
class OPCmini
{
	
 private static $req_state; 
 
 public static function setMsg($msg, $type='info') {
	 require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers' .DIRECTORY_SEPARATOR. 'opc.php');
	 if (empty(VirtueMartControllerOpc::$opc_msgs)) VirtueMartControllerOpc::$opc_msgs = array(); 
	 $mdx = md5($msg); 
	 VirtueMartControllerOpc::$opc_msgs[$type.'_'.$mdx] = $msg; 
	 
	
 }
 
 public static function setVendorId(&$cart) {
	 //will set vendorId to the first product of the cart
	 if (!empty($cart->products)) {
		 foreach ($cart->products as $p) {
			 if (!empty($p->virtuemart_vendor_id)) {
				 $vendorId = (int)$p->virtuemart_vendor_id; 
				 $cart->vendorId = $vendorId; 
				 break;
			 }
		 }
	 }
 }
 
 
 
 
 public static function getMemLimit() {
		 $memory_limit = ini_get('memory_limit');
		 
		 if ($memory_limit <= 0) {
			 return PHP_INT_MAX;
		 }
		 if (empty($memory_limit)) return PHP_INT_MAX; 
			$val = trim($memory_limit);
			$mem = (int)substr($val, 0, -1); 
    $last = strtolower($val[strlen($val)-1]);
	
	 
	
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val = $mem * 1024 * 1024 * 1024;
			break;
        case 'm':
            $val = $mem * 1024 * 1024;
			break;
        case 'k':
            $val = $mem * 1024;
			break;
    }
	
	
	
	return $val; 
	}
 
 
 public static function isInteger($val)
{
    if (!is_scalar($val) || is_bool($val)) {
        return false;
    }
	if (!is_numeric($val)) return false; 
    if (is_float($val + 0) && ($val + 0) > PHP_INT_MAX) {
        return false;
    }
    return is_float($val) ? false : preg_match('~^((?:\+|-)?[0-9]+)$~', $val);
}

public static function isFloat($val)
{
    if (!is_scalar($val)) {
        return false;
    }
    return is_float($val + 0);
}
 
 public static function updateProductCart($quantities=array()) {
		
		$cart = self::getCart(); 
		if (empty($quantities)) {
			$quantities = vRequest::getInt('quantity');
		}
		if(empty($quantities)) return false;
		$updated = false;

		foreach($quantities as $key=>$quantity){
			if (isset($cart->cartProductsData[$key]) and !empty($quantity) and !isset($_POST['delete_'.$key])) {
				if($quantity!=$cart->cartProductsData[$key]['quantity']){
					$cart->cartProductsData[$key]['quantity'] = $quantity;
					$updated = true;
					
				}

			} else {
				unset($cart->cartProductsData[$key]);
				
				$updated = true;
			}
		}

		$cart->setCartIntoSession(true);
		
		self::getCart(); 
		
		return $updated;
	}

 
 public static function printDebugMsgs() {
	  if (!class_exists('OPCconfig')) return; 
	  $doc = JFactory::getDocument(); 
	  $c = strtolower(get_class($doc)); 
	  
	  if (method_exists($doc, 'getType')) {
			$type = $doc->getType(); 
			if ($type !== 'html') {
			   return;
			}
			else {
				$arr = array('joomla\cms\document\htmldocument', 'jdocumenthtml'); 
				if (!in_array($c, $arr)) return; 
			}
	  }
	  
	  $d = OPCconfig::get('opc_debug', false); 
	  if (empty($d)) return; 
	  
	  if (class_exists('OPCloader')) {
	   if (!empty(OPCloader::$debugMsg)) {
		   //php5.3 as far as it's using latest json: 
		   if (!defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 128); 
		   
		     if (!empty(OPCloader::$debug_disabled)) return; 
			 $json = json_encode(array('debug' => OPCloader::$debugMsg), JSON_PRETTY_PRINT); 
			 $jsc = '  var opc_debug_msgs = '.$json.'; '."\n \n ".' if ((typeof console !== \'undefined\') && (console.log !== null)) {
				 console.log(\'OPC DEBUG\', opc_debug_msgs); 
			 }
			 '; 
			 //$js = "<script type=\"text/javascript\">\n"; 
			 //$js .= "//<![CDATA[\n";
			 $js = "\n".$jsc."\n";
			 //$js .= "//]]>\n";
			 //$js .= "</script>\n"; 

			$document = JFactory::getDocument();
			if (method_exists($document, 'addScriptDeclaration')) {
			 $document->addScriptDeclaration($js); 
			}
			 
	   
	   }
	  }
	}
 public static function clearCartCache() {
	 OPCmini::$cartCache = array();

		$session = JFactory::getSession(); 
		$key = '_';
		$session->clear('opc_before_payment'.$key); 	 
 }
 public static $cartCache; 
 public static function storeCartState($cart, $key='_', $toSession=true) {
	    if (empty($cart)) return; 
		$toStore = new stdClass(); 
		$toStore->virtuemart_shipmentmethod_id = (int)$cart->virtuemart_shipmentmethod_id; 
		$toStore->virtuemart_paymentmethod_id = (int)$cart->virtuemart_paymentmethod_id; 
		if (!empty($cart->BT)) {
		if (isset($cart->selected_shipto)) {
		  $toStore->selected_shipto = (int)$cart->selected_shipto; 
		}
		$toStore->STsameAsBT = (int)$cart->STsameAsBT; 
		$toStore->BT = array(); 
		foreach ($cart->BT as $k=>$v) {
			if (is_object($v)) $v = (array)$v; 
			if (empty($v) && (is_array($v))) {
				$v = ''; 
			}
			
			$toStore->BT[$k] = $v; 
		}
		
		
		
		if (!empty($cart->ST)) {
		$toStore->ST = array(); 
		foreach ($cart->ST as $k=>$v) {
			if (is_object($v)) $v = (array)$v; 
			if (empty($v) && (is_array($v))) {
				$v = ''; 
			}
			$toStore->ST[$k] = $v; 
		}
		}
		else {
			$toStore->ST = $cart->ST; 
		}
		}
		if (!empty($toSession)) {
		$session = JFactory::getSession(); 
		$data = json_encode($toStore); 
		$session->set('opc_before_payment'.$key, $data); 
		}
		else {
			OPCmini::$cartCache[$key] = $toStore; 
		}
		
	}
	
	public static function loadCartState(&$cart,$key='_', $fromSession=true, $clean=false) {
		if (empty($cart)) return; 
		
		if (empty(OPCmini::$cartCache[$key])) {
		if (empty($fromSession)) {
			return;
		}
		$session = JFactory::getSession(); 
		$data = $session->get('opc_before_payment'.$key, ''); 
		
		if (empty($data)) return; 
		if (!empty($data)) {
			$storedData = json_decode($data); 
			OPCmini::$cartCache[$key] = $storedData; 
			if ($key !== '_') {
			  $session->clear('opc_before_payment'.$key, ''); 
			}
		}
		if (empty($storedData)) return; 
		}
		else {
			$storedData = OPCmini::$cartCache[$key];
		}
		$cart->virtuemart_shipmentmethod_id = (int)$storedData->virtuemart_shipmentmethod_id; 
		$cart->virtuemart_paymentmethod_id = (int)$storedData->virtuemart_paymentmethod_id; 
		if (!empty($storedData->BT)) {
			if (isset($storedData->selected_shipto)) {
				$cart->selected_shipto = (int)$storedData->selected_shipto; 
			}
		$cart->STsameAsBT = (int)$storedData->STsameAsBT; 
		$cart->BT = array(); 
		foreach ($storedData->BT as $k=>$v) {
			$cart->BT[$k] = $v; 
		}
		if (!empty($storedData->ST)) {
		$cart->ST = array(); 
		foreach ($storedData->ST as $k=>$v) {
			$cart->ST[$k] = $v; 
		}
		}
		else {
			$cart->ST = $storedData->ST; 
		}
		}
		
		if (!empty($clean)) {
			unset(OPCmini::$cartCache[$key]); 
		}
		
		
		
	}
 
 public static function &getCart($debug=false, &$cart=null) {
		
		
		
		if (!class_exists('VmConfig'))
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		VmConfig::loadConfig(); 
		
		$storedPOST = $_POST; 
		$storedGET = $_GET; 
		$storedREQUEST = $_REQUEST; 
		
		
		$currency_id = JRequest::getInt('virtuemart_currency_id', 0); 
		
	 		if (!class_exists('VirtueMartCart'))
			require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
		  	if (!class_exists('calculationHelper'))
			require(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'calculationh.php');
		
		
		
		
		if (is_null($cart)) {
		  $cart = VirtuemartCart::getCart(); 
		}
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		$opc_debug = OPCconfig::get('opc_debug', false); 
		if ($opc_debug) 
		{
		if (class_exists('OPCloader')) {
			
			$x = debug_backtrace(); 
			$b = array(); 
			foreach ($x as $l) {
				if ((isset($l['file'])) && (isset($l['line']))) {
				 $b[] = $l['file'].' '.$l['line']; 
				}
			}
			  OPCloader::opcDebug(array('STsameAsBT' => $cart->STsameAsBT, 'ST' => $cart->ST, 'BT'=>$cart->BT, 'b'=>$b), 'mini '.__LINE__); 
			}
		}
		
		if ((!empty($cart->STsameAsBT)) && ($cart->STsameAsBT === 1)) {
			$cart->ST = 0; 
		}
		
		require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'loader.php');
		if ((empty($cart->BT)) || (empty($cart->BT['virtuemart_country_id']))) {
		   if (empty($cart->BT)) $cart->BT = array(); 
		   $cart->BT['virtuemart_country_id'] = OPCloader::getDefaultCountry($cart); 
		}
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('plgSetCurrency', array(&$cart));
		
		
		$new_currency_id = JRequest::getInt('virtuemart_currency_id', 0); 
		if (!empty($new_currency_id) && ($new_currency_id !== $currency_id)) {
			if (isset($storedPOST['virtuemart_currency_id'])) $storedPOST['virtuemart_currency_id'] = $new_currency_id; 
			if (isset($storedGET['virtuemart_currency_id'])) $storedGET['virtuemart_currency_id'] = $new_currency_id; 
			if (isset($storedREQUEST['virtuemart_currency_id'])) $storedREQUEST['virtuemart_currency_id'] = $new_currency_id; 
			
		}
		
		
		//adjust prices for already initialized cart:
		JPluginHelper::importPlugin('vmcoupon');
		JPluginHelper::importPlugin('vmcustom');								  
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('plgAdjustCartPrices', array(&$cart));
		if (isset($cart->pricesUnformatted)) {
		 if (empty($cart->cartPrices)) $cart->cartPrices = $cart->pricesUnformatted; 
		}
		if (!empty($cart->cartPrices)) {
		 $dispatcher->trigger('plgVmUpdateTotals', array(&$cart->cartData, &$cart->cartPrices));		
		}
		  
		  $signature = microtime(true); 
		  self::storeCartState($cart, 'getCart_'.$signature, false); 
			
	 $ptest = JRequest::getVar('virtuemart_product_id', null); 
	 if (defined('VM_VERSION') && (VM_VERSION >= 3))
		{
			unset($cart->products); 
			$cart->products = array(); 
			
			if (isset($cart->_productAdded))
			{
				$cart->_productAdded = true; 
				
			}
			if (isset($cart->_calculated)) {
				$cart->_calculated = false;
			}
			
			
			
			
			
			
			if (!is_null($ptest)) {
				unset($_GET['virtuemart_product_id']); 
				unset($_POST['virtuemart_product_id']); 
				unset($_REQUEST['virtuemart_product_id']); 
				JRequest::setVar('virtuemart_product_id', null); 
			}
			$ptest2 = JRequest::getVar('field', null); 
			if (!is_null($ptest)) {
				unset($_GET['field']); 
				unset($_POST['field']); 
				unset($_REQUEST['field']); 
				JRequest::setVar('field', null); 
			}
			$ptestCustomProductData = JRequest::getVar('customProductData', null); 
			if (!is_null($ptest)) {
				unset($_GET['customProductData']); 
				unset($_POST['customProductData']); 
				unset($_REQUEST['customProductData']); 
				JRequest::setVar('customProductData', null); 
			}
			$ptestCartProductId = JRequest::getVar('cart_virtuemart_product_id', null); 
			if (!is_null($ptest)) {
				unset($_GET['cart_virtuemart_product_id']); 
				unset($_POST['cart_virtuemart_product_id']); 
				unset($_REQUEST['cart_virtuemart_product_id']); 
				JRequest::setVar('cart_virtuemart_product_id', null); 
			}
			$ptestCartProductId = JRequest::getVar('cart_virtuemart_product_id', null); 
			if (!is_null($ptest)) {
				unset($_GET['quantity']); 
				unset($_POST['quantity']); 
				unset($_REQUEST['quantity']); 
				JRequest::setVar('quantity', null); 
			}
			
			if (class_exists('OPCloader')) {
			 //OPCloader::opcDebug($cart->cartProductsData, 'quantity_updateCart'); 
			}
			
			ob_start(); 
			$stored_coupon = $cart->couponCode; 
			$cart->prepareCartData(); 
			$cart->couponCode = $stored_coupon; 
			$zz = ob_get_clean(); 
			
			
			
			
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmgetPaymentCurrency', array( $cart->virtuemart_paymentmethod_id, &$cart->paymentCurrency));
			
			//if (empty($cart->couponCode)) 
			
			$view = JRequest::getVar('view', ''); 
			if ((defined('OPC_IN_CHECKOUT')) || ($view === 'cart'))
			{
			
					require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'awohelper.php'); 
					if (OPCAwoHelper::isAwoEnabled()) {
						
						OPCAwoHelper::defineItems($cart); 
						OPCAwoHelper::processAutoCoupon($debug); 
						
						
					}
				
				
				
			
			}
			
			
			
			
			$cart->order_language = JRequest::getVar('order_language', $cart->order_language);
		
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('plgVmUpdateTotals', array(&$cart->cartData, &$cart->cartPrices));		
			
		}
		
			self::setVendorId($cart); 				
		
		
			if (!is_null($ptest)) {
				JRequest::setVar('virtuemart_product_id', $ptest); 
			}
		
		$_POST = $storedPOST; 
		$_GET = $storedGET; 
		$_REQUEST = $storedREQUEST;
		
		self::loadCartState($cart, 'getCart_'.$signature, false); 
		return $cart; 
 }
 
 public static function storeReqState($arr) {
	 
	 OPCmini::$req_state = array(); 
	 
	 foreach($arr as $k=>$v) {
		 $obj = array(); 
		 if (isset($_GET[$k])) $obj['_GET'] = $v; 
		 else $obj['_GET'] = null; 
		 
		 if (isset($_POST[$k])) $obj['_POST'] = $v; 
		 else $obj['_POST'] = null; 
		 
		 if (isset($_REQUEST[$k])) $obj['_REQUEST'] = $v; 
		 else $obj['_REQUEST'] = null; 
		 
		 OPCmini::$req_state[$k] = $obj; 
		 
	 }
	 
	 
 }
 
 public static function loadReqState($arr) {
	 foreach ($arr as $k => $v) {
		 
		 if (isset(OPCmini::$req_state[$k])) {
			 
			 if (!is_null(OPCmini::$req_state[$k]['_GET'])) $_GET[$k] = OPCmini::$req_state[$k]['_GET']; 
			else unset($_GET[$k]);
		 
			if (!is_null(OPCmini::$req_state[$k]['_POST'])) $_POST[$k] = OPCmini::$req_state[$k]['_POST']; 
			else unset($_POST[$k]);
			
			 if (!is_null(OPCmini::$req_state[$k]['_REQUEST'])) $_REQUEST[$k] = OPCmini::$req_state[$k]['_REQUEST']; 
			else unset($_REQUEST[$k]);
		 
			 
		 }
		 
	 }
 }
 
	
 function loadJSfile($file)
 {
   jimport('joomla.filesystem.file');
   $file = JFile::makeSafe($file); 
   $pa = pathinfo($file); 
   $fullpath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$file; 
   if (!empty($pa['extension']))
   if ($pa['extension']=='js')
    {
	 //http://php.net/manual/en/function.header.php 
	if(strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")==false) {
		@header("Content-type: text/javascript");
		@header("Content-Disposition: inline; filename=\"".$file."\"");
		//@header("Content-Length: ".filesize($fullpath));
	} else {
		@header("Content-type: application/force-download");
		@header("Content-Disposition: attachment; filename=\"".$file."\"");
		//@header("Content-Length: ".filesize($fullpath));
	}
	@header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
	if(strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")==false) {
	@header("Cache-Control: no-cache");
	@header("Pragma: no-cache");
    }
	//include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$file);
	echo file_get_contents($fullpath); 
	$doc = JFactory::getApplication(); 
	$doc->close(); 
   

	}
	
	
 }
 public static function extExists($ext, $type='', $folder='', $enabled='') {
	 static $c; 
	 $cache_string = $ext.'_'.$type.'_'.$folder.'_'.$enabled; 
	 if (isset($c[$cache_string])) return $c[$cache_string]; 
   $db = JFactory::getDBO(); 
   $q = "select * from `#__extensions` where `element` = '".$db->escape($ext)."' "; 
   if (!empty($type)) $q .= " and `type` = '".$db->escape($type)."' ";
   if (!empty($folder)) $q .= " and `folder` = '".$db->escape($folder)."' ";
   if (!empty($enabled)) $q .= " and `enabled` = ".(int)$enabled." ";
   $q .= ' and `state` = 0 '; 
   $q .= " limit 0,1"; 
   
   
   $db->setQuery($q); 
   $r = $db->loadAssoc(); 
   if (empty($r)) return false; 
   $c[$cache_string] = (array)$r; 
   return $c[$cache_string]; 
 }
 
  public static function getCurrentCurrency() {
	 $mainframe = JFactory::getApplication();
	 $virtuemart_currency_id = (int)$mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id') );	 
	 
	 if (!class_exists('VirtuemartCart'))
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cart.php'); 
		$cart = VirtuemartCart::getCart(); 
	 
	 if (empty($virtuemart_currency_id)) {
		

		if (!empty($cart->pricesCurrency)) {
			$virtuemart_currency_id = (int)$cart->pricesCurrency; 
		}
		else {
			if (!empty($cart->paymentCurrency))
	 	    {
				$virtuemart_currency_id = (int)$cart->paymentCurrency;
			}
		}
	 }
	 $db = JFactory::getDBO(); 
	 if (empty($virtuemart_currency_id)) {
		 // secnd take the vendors currency: 
		if (!empty($cart->vendorId))
		$vendorId = $cart->vendorId; 
		else $vendorId = 1; 	
		
		if (empty($vendorId)) $vendorId = 1; 
			
		$q  = 'SELECT  `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = '.(int)$vendorId.' limit 0,1';
		$db->setQuery($q);
		$virtuemart_currency_id = $db->loadResult();
		$virtuemart_currency_id = (int)$virtuemart_currency_id; 
		
		
		  
	 }
	 
	 return (int)$virtuemart_currency_id; 
 }
 
 public static function displayPrice($price) {
	 if ($cur === 0) $cur = self::getCurrentCurrency(); 
	 return self::displayCustomCurrency($price, $cur, '', false); 
 }
 public static function displayCustomCurrency($price, $cur=0, $convertFrom='', $hideIfCurrent=true) {
    if ($cur === 0) $cur = self::getCurrentCurrency(); 
	if (!empty($convertFrom)) {
		$price = OPCmini::convertPrice($price, $convertFrom, $cur); 
	}
	
	
	
	if (!class_exists('CurrencyDisplay'))
	require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
	
	$price = (float)$price; 
	
	static $curinfo; 
	
	
	
	if (!isset($curinfo[$cur])) {
   if ((!is_numeric($cur)) && (strlen($cur)==3)) {
	   $db = JFactory::getDBO(); 
	   $q = 'select `virtuemart_currency_id` from `#__virtuemart_currencies` where `currency_code_3` = '."'".$db->escape($cur)."'".' limit 0,1'; 
	   $db->setQuery($q); 
	   $cidI = $db->loadResult(); 
	   $cidI = (int)$cidI; 
	   if (empty($cidI)) return ''; 
   }
   else {
	   $cidI = (int)$cur; 
   }
      
      $curinfo[$cur] = self::getCurInfo($cidI); 
	}
   
   
   
   
   if ($hideIfCurrent) {
    $current = self::getCurrentCurrency(); 
	$cid = (int)$curinfo[$cur]['virtuemart_currency_id']; 
	
    if ($current === $cid) return ''; 
   }
   
   //format per curency config: 
   $nbDecimal = (int)$curinfo[$cur]['currency_decimal_place']; 
   $decimalP = $curinfo[$cur]['currency_decimal_symbol']; 
   $positivePos =  $curinfo[$cur]['currency_positive_style']; 
   $negativePos =  $curinfo[$cur]['currency_negative_style']; 
   $thousands = $curinfo[$cur]['currency_thousands']; 
   $symbol = $curinfo[$cur]['currency_symbol']; 
   
   if($price>=0){
			$format = $positivePos;
			$sign = '+';
		} else {
			$format = $negativePos;
			$sign = '-';
			$price = abs($price);
		}

		
		$res = number_format($price, $nbDecimal, $decimalP, $thousands);
		$search = array('{sign}', '{number}', '{symbol}');
		$replace = array($sign, $res, $symbol);
		$resultPrice = str_replace ($search,$replace,$format);
   
   
   
   return  $resultPrice; 
   
   
 }
 
 public static function getVendorCurrency() {
	 static $virtuemart_currency_id; 
	 if (!empty($virtuemart_currency_id)) return $virtuemart_currency_id; 
	 
	  if (!class_exists('VirtuemartCart'))
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cart.php'); 
		$cart = VirtuemartCart::getCart(); 
	 
	 if (!empty($cart->vendorId))
		$vendorId = $cart->vendorId; 
		else $vendorId = 1; 	
	 
	  $db = JFactory::getDBO(); 
	 $q  = 'SELECT  `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = '.(int)$vendorId.' limit 0,1';
		$db->setQuery($q);
		$virtuemart_currency_id = $db->loadResult();
		$virtuemart_currency_id = (int)$virtuemart_currency_id; 
		
		return $virtuemart_currency_id; 
 }
 
 // from cur1 to cur2 
 public static function convertPrice($price, $cur1, $cur2) {
		
		if (($cur1 === $cur2) || (empty($cur1)) || (empty($cur2))) return $price; 
	$db = JFactory::getDBO(); 
	if ((!is_numeric($cur1)) && (strlen($cur1)==3)) {
	   
	   $q = 'select `virtuemart_currency_id` from `#__virtuemart_currencies` where `currency_code_3` = '."'".$db->escape($cur1)."'".' limit 0,1'; 
	   $db->setQuery($q); 
	   $cidI = $db->loadResult(); 
	   $cur1 = (int)$cidI; 
	   if (empty($cidI)) return $price; 
   }
   if ((!is_numeric($cur2)) && (strlen($cur2)==3)) {
	  
	   $q = 'select `virtuemart_currency_id` from `#__virtuemart_currencies` where `currency_code_3` = '."'".$db->escape($cur2)."'".' limit 0,1'; 
	   $db->setQuery($q); 
	   $cidI = $db->loadResult(); 
	   $cur2 = (int)$cidI; 
	   if (empty($cidI)) return $price; 
   }
		
		
	 if (!class_exists('VmConfig'))	  
	 {
	  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	  VmConfig::loadConfig(); 
	 }
		
			
		$c1o = self::getCurInfo($cur1); 
		$c2o = self::getCurInfo($cur2); 
		
		$v = self::getVendorCurrency(); 
		
		
		
		if (!empty($c2o['currency_exchange_rate']))
		if ($cur1 === $v) {
			
			$price = (float)$price; 
			$c2o['currency_exchange_rate'] = (float)$c2o['currency_exchange_rate'];
			$price = $c2o['currency_exchange_rate'] * $price; 
			return $price; 
		}
		
		
		if (!empty($c1o['currency_exchange_rate']))
		if ($cur2 === $v) {
			$price = (float)$price; 
			$c1o['currency_exchange_rate'] = (float)$c1o['currency_exchange_rate'];
			if (empty($c1o['currency_exchange_rate'])) $c1o['currency_exchange_rate'] = 1; 
			$price = $price / $c1o['currency_exchange_rate']; 
			return $price; 
		}
		
		
		static $cC; 
		static $rate; 
		
		
		if (empty($cC)) {
		$converterFile  = VmConfig::get('currency_converter_module','convertECB.php');

		if (file_exists( JPATH_ADMINISTRATOR.DS.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'plugins'.DS.'currency_converter'.DIRECTORY_SEPARATOR.$converterFile ) and !is_dir(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'currency_converter'.DIRECTORY_SEPARATOR.$converterFile)) {
			$module_filename=substr($converterFile, 0, -4);
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DS.'plugins'.DS.'currency_converter'.DS.$converterFile);
			if( class_exists( $module_filename )) {
				$cC = new $module_filename();
			}
		} else {

			if(!class_exists('convertECB')) require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'currency_converter'.DIRECTORY_SEPARATOR.'convertECB.php');
			$cC = new convertECB();

		}
		}
		
		
		
		$priceC = (float)$price; 
		if (empty($priceC)) return $price; 
		
		if (!isset($rate[$cur1.'_'.$cur2]))
		if ((method_exists($cC, 'convert'))) {
		  
		  $multi = PHP_INT_MAX;
		  try {
		   $rateZ = $cC->convert( PHP_INT_MAX, $c1o['currency_code_3'], $c2o['currency_code_3']);
		  }
		  catch (Exception $e) {
		    $rateZ = 1; 
		  }
		  $rate[$cur1.'_'.$cur2] = $rateZ / PHP_INT_MAX; 
		}
		else
		{
			$rate[$cur1.'_'.$cur2] = 1; 
		}
		
		$priceC = $price * $rate[$cur1.'_'.$cur2]; 
		return $priceC; 
		
		
 }
 
 
 public static function getCurInfo($currency)
   {
	   static $c; 
	   static $c2; // always vendor currency
	   $currency = (int)$currency; 
	   $db = JFactory::getDBO();
	   if (empty($currency)) {
		if (empty($c2)) {
	    
		if (!class_exists('VmConfig'))	  
		{
		 require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 VmConfig::loadConfig(); 
		}
		
		
			if (!class_exists('VirtueMartCart'))
			require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
			$cart = VirtuemartCart::getCart(); 
		
			   if (defined('VM_VERSION') && (VM_VERSION >= 3))
			   {
				    if (method_exists($cart, 'prepareCartData')) {
						ob_start(); 
				     $cart->prepareCartData(); 
					 $zz = ob_get_clean(); 
					}
			   }
		
		// first take the cart currency: 
		if (!empty($cart->pricesCurrency)) {
			$currency  = $c2 = $cart->pricesCurrency; 
		}
		else {
			
		// secnd take the vendors currency: 
		if (!empty($cart->vendorId))
		$vendorId = $cart->vendorId; 
		else $vendorId = 1; 	
		
		if (empty($vendorId)) $vendorId = 1; 
			
		$q  = 'SELECT  `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`='.$vendorId;
		$db->setQuery($q);
		$vendor_currency = $db->loadResult();
		$c2 = $vendor_currency; 
		
		
		  
		}
		}
		else
		{
			$currency = $c2; 
		}
	   }
	   
	   
	   if (isset($c[$currency])) return $c[$currency]; 
	    
	   $q = 'select * from #__virtuemart_currencies where virtuemart_currency_id = '.(int)$currency.' limit 0,1'; 
	   $db->setQuery($q); 
	   $res = $db->loadAssoc(); 
	   if (empty($res)) {
	 
	
	   $res = array(); 
	   $res['currency_symbol'] = '$'; 
	   $res['currency_decimal_place'] = 2; 
	   $res['currency_decimal_symbol'] = '.'; 
	   $res['currency_thousands'] = ' '; 
	   $res['currency_positive_style'] = '{number} {symbol}';
	   $res['currency_negative_style'] = '{sign}{number} {symbol}'; 
	   
	   
	   

	   
	   
	   }
	   $res = (array)$res; 
	   
	   $c[$currency] = $res; 
	   return $res; 
   }
 
 
 public static function setVMLANG() {
	 
	  if (!class_exists('VmConfig'))
		    {
			     if (!class_exists('VmConfig'))
				require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
				VmConfig::loadConfig ();

			}
			if(!defined('VMLANG'))		
			if (method_exists('VmConfig', 'setdbLanguageTag')) {
			   VmConfig::setdbLanguageTag();
			}
	 
    
	if ((!defined('VMLANG')) && (!empty(VmConfig::$vmlang))) {
	  define('VMLANG', VmConfig::$vmlang); 
	}
		
 }
 // gets rid of any DB references or DB objects, all objects are converted to stdClass
 public static function toObject(&$product, $recursion=0) {
    
	
	
	if (is_object($product)) {
	 $copy = new stdClass(); 
	 $attribs = get_object_vars($product); 
	 $isO = true; 
	}
	elseif (is_array($product)) {
		  $copy = array(); 
		  $isO = false; 
		  $attribs = array_keys($product); 
		  $copy2 = array(); 
		  foreach ($attribs as $zza=>$kka) {
		       if (strpos($kka, "\0")===0) continue;
			   $copy2[$kka] = $product[$kka]; 
		  }
		  $attribs = $copy2; 
		}
		
	if (!empty($attribs))
    foreach ($attribs as $k=> $v) {
		
		if (strpos($k, "\0")===0) continue;
		if ($isO) {
	      $copy->{$k} = $v; 	
		}
		else
		{
			$copy[$k] = $v; 
		}
		if (empty($v)) continue; 
		//if ($recursion < 5)
		if ((is_object($v)) && (!($v instanceof stdClass))) {
		   $recursion++; 
		   if ($isO) {
		     OPCmini::toObject($copy->{$k}, $recursion); 
		   }
		   else
		   {
			   OPCmini::toObject($copy[$k], $recursion); 
		   }
		}
		else
		{
			
			if (is_array($v)) {
			   $recursion++; 
			   if ($isO) {
		        OPCmini::toObject($copy->{$k}, $recursion); 
			   }
			   else
			   {
				   OPCmini::toObject($copy[$k], $recursion); 
			   }
			}
		}
		/*
		if (is_array($v)) {
		
		  $keys = array_keys($v); 
	  
		  foreach ($keys as $kk2=>$z2) {
		     if (strpos($z2, "\0")===0) continue;
			 $copy->{$k}[$z2] = $v[$z2]; 
			 if ((is_object($v[$z2])) && (!($v[$z2] instanceof stdClass))) {
				$recursion++; 
			    OPCmini::toObject($copy->{$k}[$z2]); 
			 }
			 else
			 if (is_array($v[$z2])) {
			    $recursion++; 
			    OPCmini::toObject($copy->{$k}[$z2]); 
			 }
			 
		  }
		}
		*/
		
		
	}
	$recursion--;
	if (empty($copy)) return; 
	$product = $copy; 
 }
 public static function isMysql($ver, $operator='>=') {
   $db = JFactory::getDBO(); 
   $q = 'SELECT @@version as version'; 
   $db->setQuery($q); 
   $version = $db->loadResult(); 
   
   if (stripos($version, '-')) {
     $versionA = explode('-', $version); 
	 if (count($versionA)>1) $version = $versionA[0]; 
   }
   return version_compare($version, $ver, $operator); 
   
 }
 
 
 public static function compareEqual($fields, $table, $primary_col, $primary_id, &$alreadyloaded=array()) {
	  if ((empty($primary_col)) || (empty($primary_id))) return false; 
	  
	  $keys = array_keys($fields); 
	  $db = JFactory::getDBO(); 
	  foreach ($keys as $kk=>$vv) {
		 $keys[$kk] = '`'.$db->escape($vv).'`'; 
	  }
	  $q = 'select '.implode(',', $keys).' from '.$table.' where `'.$primary_col.'` = '.(int)$primary_id;
	  if (!empty($alreadyloaded)) {
		  $res = $alreadyloaded; 
	  }
	  else {
		$db->setQuery($q); 
		$res = $db->loadAssoc(); 
	  }
	  if (empty($res)) return false; 
	  foreach ($res as $ind=>$k) {
		 if ($res[$ind] == $fields[$ind]) {
			 unset($fields[$ind]); 
			 continue; 
		 }
		 if (trim($res[$ind]) == trim($fields[$ind])) unset($fields[$ind]); 
	  }
	  
	  if (empty($fields)) return true; 
	  foreach ($fields as $xk=>$vk) {
		  if (is_null($vk) && (empty($res[$xk]))) unset($fields[$xk]); 
		  if (($vk === 'NULL') && (empty($res[$xk]))) unset($fields[$xk]); 
		  if ($xk === 'created_on') unset($fields[$xk]); 
		  if ($xk === 'modified_on') unset($fields[$xk]); 
		  if ($xk === 'modified_by') unset($fields[$xk]); 
		  
	  }
	  
	  if (empty($fields)) return true; 
	  
	  $orig = array(); 
	  foreach ($fields as $k=>$z) {
		  
		  $orig[$k] = $res[$k]; 
	  }
	  //n_log::notice('compareEqual found a difference: '.var_export($fields, true).' vs '.var_export($orig, true)); 
	  return false; 
	  
	   
  }
 
 public static function parseCommas($str, $toint=true, $SEP=',')
 {
	  if (empty($str)) return array(); 
	  $e = explode($SEP, $str); 
	  
	  $ea = array(); 
	  if (count($e)>0) {
	    foreach ($e as $c) {
		  $c = trim($c); 
		  if ($c === '0') {
		   $ea[0] = 0; 
		   continue; 
		  }
		  if ($toint) {
		    $c = (int)$c; 
		  }
		  if (empty($c)) continue; 
		  $ea[$c] = $c; 
		}
	  }
	  else
	  {
		  $c = trim( $str ); 
		  if ($c === '0') {
		   $ea[0] = 0; 
		  }
		  if ($toint) {
			$c = (int)$c;
		  }		  
		  if (!empty($c)) $ea[$c] = $c; 
	  }
	  return $ea; 
 }
 
 public static function getPrimary($table) {
	  if (!OPCmini::tableExists($table)) return array(); 
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	 
   if (isset(OPCmini::$cache['primary_'.$table])) return OPCmini::$cache['primary_'.$table]; 
   // here we load a first row of a table to get columns
   
   $q = 'SHOW COLUMNS FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
   $new = '';
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		$field = (string)$v['Field']; 
		$auto = (string)$v['Extra']; 
		$key = (string)$v['Key']; 
		if (($key === 'PRI') || (stripos($auto, 'auto_increment')!==false)) {
			$new = $field; 
			break; 
		}
		
	}
	OPCmini::$cache['primary_'.$table] = $new; 
	return $new; 
   }
   OPCmini::$cache['primary_'.$table] = '';
   return array(); 
 }
 
 public static function getUnique($table) {
	  if (!OPCmini::tableExists($table)) return array(); 
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	 
   if (isset(OPCmini::$cache['unique_'.$table])) return OPCmini::$cache['unique_'.$table]; 
   // here we load a first row of a table to get columns
   
   $q = 'SHOW COLUMNS FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
   $new = '';
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		$field = (string)$v['Field']; 
		$auto = (string)$v['Extra']; 
		$key = (string)$v['Key']; 
		if (($key === 'PRI') && (stripos($auto, 'auto_increment')!==false)) {
			if (empty($new))
			$new = $field; 
		
		}
		if ($key === 'UNI') {
			
			$new = $field; 
		}
		
	}
	OPCmini::$cache['unique_'.$table] = $new; 
	return $new; 
   }
   OPCmini::$cache['unique_'.$table] = '';
   return array(); 
 }
 
 public static function getUniques($table) {
	  if (!self::tableExists($table)) return array(); 
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $tableOrig = $table; 
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	 
   if (isset(OPCmini::$cache['uniques'.$table])) return OPCmini::$cache['uniques'.$table]; 
   // here we load a first row of a table to get columns
   
   $q = 'SHOW COLUMNS FROM '.$table; 
   $q = 'SHOW INDEX FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
  
  
   $allkeys = array(); 
   
   $new = '';
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		
		
		
		$colname = $v['Column_name']; 
		
		
		if (!empty($v['Non_unique'])) continue; 
		
		
		
		$keyname = (string)$v['Key_name']; 
		if (empty($allkeys[$keyname])) $allkeys[$keyname] = array(); 
		$colname = $v['Column_name']; 
		$allkeys[$keyname][$colname] = $colname; 
		
		
		
	}
	OPCmini::$cache['uniques_'.$table] = $allkeys; 
	return $allkeys; 
   }
   
   return array(); 
 }
 public static function getUniquesRemoved($table) {
	  if (!OPCmini::tableExists($table)) return array(); 
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	 
   if (isset(OPCmini::$cache['uniques'.$table])) return OPCmini::$cache['uniques'.$table]; 
   // here we load a first row of a table to get columns
   
   $q = 'SHOW COLUMNS FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
  
  
   $allkeys = array(); 
   
   $new = '';
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		$field = (string)$v['Field']; 
		$auto = (string)$v['Extra']; 
		$key = (string)$v['Key']; 
		
		
		if ($key === 'UNI') {
			
			$new = $field; 
			$allkeys[] = $new; 
		}
		
	}
	OPCmini::$cache['uniques_'.$table] = $allkeys; 
	return $allkeys; 
   }
   
   return array(); 
 }
 
   public static function insertArray($table, &$fields, $def=array())
 {
	 $db = JFactory::getDBO(); 
	 $primary = $primary_col = OPCmini::getPrimary($table); 
	 $primary_id = 'NULL'; 
	 if ((!empty($primary)) && (isset($fields[$primary]) && ($fields[$primary] !== 'NULL'))) {
		 
		 $primary_id = (int)$fields[$primary]; 
     }
	 
	 
	 $uniques = OPCmini::getUniques($table); 
	 $whereAll = array(); 
	 
	 
	 
	 foreach ($uniques as $keys) {
			if (count($keys)>1) {
				$test = array(); 
				$cols = array(); 
				foreach ($keys as $ucol) {
					if (isset($fields[$ucol])) {
					  $test[] = "'".$db->escape($fields[$ucol])."'"; 
					  $cols[] = '`'.$db->escape($ucol).'`'; 
					}
					else {
						
						
						
						$test[] = ' DEFAULT('.$db->escape($ucol).')'; 
						$cols[] = '`'.$db->escape($ucol).'`'; 
					}
				}
				
				$w = array(); 
				foreach ($cols as $i => $k) {
					$w[] = $k.'='.$test[$i]; 
				}
				
				
			    $whereAll[] = '('.implode(' and ', $w).')'; 
			}
			else {
				
				$test_key = reset($keys); 
				if ((isset($fields[$test_key]) && ($fields[$test_key] !== 'NULL'))) 
				{
				
				
				$whereAll[] = '(`'.$test_key.'` = \''.$db->escape($fields[$test_key]).'\')';
				
				
				
				
				
				
				}
				
				
			}
		}
		$alreadyLoaded = array(); 
		if (!empty($whereAll)) {
			$q = 'select * from `'.$table.'` where '.implode(' OR ', $whereAll).' limit 1';
			
			
			
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			
			 foreach ($uniques as $keys) {
				 foreach ($keys as $kw) {
					 if ((!isset($fields[$kw])) || ($fields[$kw] === 'NULL')) {
						 $fields[$kw] = $res[$kw]; 
						 if ($kw === $primary_col) {
							 $primary_id = $res[$kw]; 
							 
							 $alreadyLoaded = $res; 
						 }
					 }
				 }
			 }
			 
			
		}
		
		
		
    if ((!empty($primary_col)) && (!empty($primary_id)))
	 if (OPCmini::compareEqual($fields, $table, $primary_col, $primary_id, $alreadyLoaded)) {
		 //if we are updating/inserting exactly same data... 
		 // n_log::notice('skipping insert update...'); 
		 return false; 
	 }
		
	 /*
	 $unique_val = ''; 
	 $unique_col = OPCmini::getUnique($table); 
	 if ((!empty($unique_col)) && (isset($fields[$unique_col]) && ($fields[$unique_col] !== 'NULL'))) {
	  $unique_val = $fields[$unique_col]; 
	 }
	 $db = JFactory::getDBO(); 
	 // check for other unique keys before insert
	 
	 if ((!empty($primary_col)) && (!empty($unique_col)))
	 if ($unique_col !== $primary_col) {
		 
		 
		 $q = 'select * from '.$table.' where '.$unique_col." = '".$db->escape($unique_val)."'"; 
		 $db->setQuery($q); 
		 $res = $db->loadAssoc(); 
		 
		 
		 
	 }
	 */
	 
	 
	 
	 if (empty($def)) {
		 $def = OPCmini::getColumns($table); 
	 }
	 foreach ($fields as $k=>$v)
	 {
		 if (!isset($def[$k])) {
			 //n_log::notice('Found extra column for '.$table.'.'.$k); 
			 unset($fields[$k]); 
		 }
		 else
		 if (is_null($fields[$k])) {
			 //if we are just updating, don't overwrite with null values !
			 //n_log::notice('DB unsetting null values '.$table.'.'.$k); 
			 unset($fields[$k]); 
		 }
		 
	 }
	 
	 if (empty($fields)) {
		 //n_log::warning('attempt to insert empty values to '.$table); 
		 return; 
	 }
	 
	
	 $q = 'insert into `'.$table.'` (';
	 $qu = 'update `'.$table.'` set '; 
	 $keys = array(); 
	 $vals = array(); 
	 $i = 0; 
	 $c = count($fields); 
	 $quq = array(); 
	 foreach ($fields as $key=>$val)
	 {
		 //do not update or insert generated columns:
		 if (self::isGenerated($table, $key)) continue; 
		 
	  $keys[$key] = '`'.$db->escape($key).'`'; 
	  $i++;
	  
	  if ($val === 'NULL')
	   $val = 'NULL'; 
      elseif ($val === 'NOW()') {
		  $val = 'NOW()'; 
	  }
	  elseif ($val === 'CURRENT_TIMESTAMP') {
		  $val = 'CURRENT_TIMESTAMP'; 
	  }
	  else 
	   $val = "'".$db->escape($val)."'"; 
	  
	  $vals[$key] = $val; 
	  
	  if ($i < $c) { 
	   //$keys .= ', ';
	   //$vals .= ', ';
	   }
	   
	   if ($key !== $primary)
	   $quq[] = ' `'.$db->escape($key)."`= ".$val; 
	  
	 }
	 $q .= implode(',', $keys).') values ('.implode(',', $vals).') ';
	 
	 $q .= ' ON DUPLICATE KEY UPDATE '; 
	 $u = false; 
	 foreach ($fields as $key=>$val)
	 {
		 if (self::isGenerated($table, $key)) continue; 
		 
		 if ($key === $primary_col) {
			  continue; 
			  /*
			 if ($u) $q .= ','; 
			 $q .= ' @LASTID:=`'.$primary_col.'` '; 
			 $u = true; 
			 continue; 
			 */
			 
		 }
		 /*
	   if ($key === $primary_col) {
			 if ($u) $q .= ','; 
			 
			 
			 
			$q .= ' `'.$primary_col.'` = LAST_INSERT_ID(`'.$primary_col.'`)'; 
			 $u = true; 
			 continue; 
		 }
		 */
	  //if ($key === $primary_col) continue; 
		 
	  if ($u) $q .= ','; 
	  $q .= '`'.$db->escape($key).'` = '; 
	  
	   if ($val === 'NULL')
	   $val = 'NULL'; 
      elseif ($val === 'NOW()') {
		  $val = 'NOW()'; 
	  }
	  elseif ($val === 'CURRENT_TIMESTAMP') {
		  $val = 'CURRENT_TIMESTAMP'; 
	  }
	  else 
	   $val = "'".$db->escape($val)."'"; 
	  
	  $q .= $val; 
	  $u = true; 
	 }
	 
	 
	 
	 //n_log::_($q); 
	 
	 if ((!empty($primary)) && (!empty($primary_id))) {
	   $qu .= '  '.implode(', ', $quq). ' where `'.$primary.'` = '.(int)$primary_id; 
	   //n_log::_('UPDATETEST: '.$qu); 
	 }
	 
	  
	  
	 $db->setQuery($q); 
	 $db->execute();
	 //echo $q; die(); 
	
	 
	 if ((!empty($primary)) && ((empty($primary_id)) || ($primary_id === 'NULL'))) {
	   $primary_id_now = $db->insertid(); 
	   if (empty($primary_id_now)) {
		   //$uniques = self::getUniques($table); 
		   
	   }
	   $primary_id = (int)$primary_id_now; 
	   $fields[$primary] = $primary_id; 
	 }
	 
	  
 }
 
 public static function insertArrayRemoved($table, $fields, $def=array())
 {
	 if (empty($def)) {
		 $def = self::getColumns($table); 
	 }
	 foreach ($fields as $k=>$v)
	 {
		 if (!isset($def[$k])) unset($fields[$k]); 
	 }
	 
	 if (empty($fields)) return; 
	 
	 $dbvv = JFactory::getDBO(); 
	 $q = 'insert into `'.$table.'` (';
	 $keys = ''; 
	 $vals = ''; 
	 $i = 0; 
	 $c = count($fields); 
	 foreach ($fields as $key=>$val)
	 {
	  $keys .= '`'.$key.'`'; 
	  $i++;
	  
	  if ($val === 'NULL')
	   $vals .= 'NULL'; 
	  else 
	   $vals .= "'".$dbvv->escape($val)."'"; 

	  if ($i < $c) { 
	   $keys .= ', ';
	   $vals .= ', ';
	   }
	  
	 }
	 $q .= $keys.') values ('.$vals.') ';
	 $q .= ' ON DUPLICATE KEY UPDATE '; 
	 $u = false; 
	 foreach ($fields as $key=>$val)
	 {
	  if ($u) $q .= ','; 
	  $q .= '`'.$key.'` = '; 
	  $q .= "'".$dbvv->escape($val)."'"; 
	  $u = true; 
	 }
	 
	 $dbvv->setQuery($q); 
	 $dbvv->execute();
 }
 
 public static function isGenerated($table, $col) {
	 $db = JFactory::getDBO(); 
	 $def = self::getColumns($table); 
	 
 
	 
	 $table = str_replace('#__', '', $table); 
     $table = str_replace($prefix, '', $table); 
     $table = $db->getPrefix().$table; 
	 
if ($table === 'g52p3_onepage_address_history') {
	 //var_dump(OPCmini::$cache['coldef_'.$table]); 
	 //die('ok '.__LINE__); 
	 if ($col === 'address_md5_st') {
		// var_dump(OPCmini::$cache['coldef_'.$table][$col]); 
	 }
 }	 
	 
	 if (isset(OPCmini::$cache['coldef_'.$table][$col])) {
		 $v = OPCmini::$cache['coldef_'.$table][$col]; 
		 if (!empty($v['Extra'])) {
			 if (stripos($v['Extra'], 'GENERATED') !== false) {
				 return true; 
			 }
			 if (stripos($v['Extra'], 'auto_increment') !== false) {
				 //return true; 
			 }
		 }
	 }
	 return false; 
 }
 
 public static function getColumns($table) {
   if (!self::tableExists($table)) return array(); 
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $tableOrig = $table; 
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	
   if (isset(OPCmini::$cache['columns_'.$table])) return OPCmini::$cache['columns_'.$table]; 
   // here we load a first row of a table to get columns
   $db = JFactory::getDBO(); 
   $q = 'SHOW COLUMNS FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
  
  
  
   $new = array(); 
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		//tableOrig
		$fieldName = $v['Field']; 
		if (empty(OPCmini::$cache['coldef_'.$table])) 
		{
			OPCmini::$cache['coldef_'.$table] = array(); 
		}
		OPCmini::$cache['coldef_'.$table][$fieldName] = $v; 
		
		$new[$v['Field']] = $v['Field']; 
	}
	
	OPCmini::$cache['columns_'.$table] = $new; 
	return $new; 
   }
   OPCmini::$cache['columns_'.$table] = array(); 
   return array(); 
   
   
 }
 
   public static function getVMTemplate($view='', $layout='') {
	   jimport('joomla.filesystem.file');
	   $view = JFile::makeSafe($view); 
	   $view = strtolower($view); 
	   $layout = strtolower($layout); 
	   $layout = JFile::makeSafe($layout); 
	   
		$vmtemplate = VmConfig::get( 'vmtemplate', 'default' );
		
		
		
		
			$template = JFactory::getApplication()->getTemplate(); 
			
			  
			  $pj = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart';
			  
			  if (!empty($view)) {
			  $pj .= DIRECTORY_SEPARATOR.$view; 
			  }
			  
			  if (!empty($layout)) { 
			   $pj .= DIRECTORY_SEPARATOR.$layout.'.php'; 
			    
			  }
			  
			  
			  
			  if (file_exists($pj)) {
				  
				  if (empty($view) && (empty($layout))) return $template; 
				  
				  return $pj; 
			  }
			  
			  if (($vmtemplate !== 'default') && (!empty($vmtemplate))) {
			   $template = $vmtemplate; 
			   $pj = JPATH_SITE.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart';
			  
			  if (!empty($view)) {
			  $pj .= DIRECTORY_SEPARATOR.$view; 
			  }
			  
			  if (!empty($layout)) { 
			   $pj .= DIRECTORY_SEPARATOR.$layout.'.php'; 
			    
			  }
			  
			  if (file_exists($pj)) {
				  
				  if (empty($view) && (empty($layout))) return $template; 
				  
				  
				  return $pj; 
			  }
			  }
			  
			  
			
		  $pv = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'; 
		  
		    if (!empty($view)) {
		    $pv .= DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR;
		    }
			
			if (!empty($layout)) { 
			$pv .= $layout.'.php'; 
			
			}
			if (file_exists($pv)) { 
			
			if (empty($view) && (empty($layout))) return 'default'; 
			
			return $pv; 
			}
			
		 
		
		
		
		
		
   
   }
 
    public static $selected_template; 
	
	
	public static function getSelectedTemplate($selected_template_config=null, $mobile_template_config=null)
	{

	if (!empty(OPCmini::$selected_template)) 
	 {
		 return OPCmini::$selected_template; 
	 }
		
	  if (is_null($selected_template_config)) {
	   $selected_template_config='clean_simple2'; 
	  }
	 
	 try {
	 $db = JFactory::getDBO(); 
	 $q = "select `config_subname`, `value` from #__onepage_config where `config_name` = 'opc_vm_config' and (`config_subname` = 'selected_template' or `config_subname` = 'mobile_template') limit 2"; 
	 $db->setQuery($q); 
	 $tx = $db->loadAssocList(); 
	 jimport('joomla.filesystem.file');
   
	 if (!empty($tx)) {
	 foreach ($tx as $row) {
		 
		 if ($row['config_subname'] === 'selected_template') $selected_template_config = JFile::makeSafe(json_decode($row['value'])); 
		 else
		 if ($row['config_subname'] === 'mobile_template') $mobile_template_config = JFile::makeSafe(json_decode($row['value'])); 
	 }
	 }
	 }
	 catch (Exception $e) {
		 
	 }
	 
	 
	 $app = JFactory::getApplication(); 
	 if (($app->isAdmin()) && (!is_null($selected_template_config)))
	 {
		 OPCmini::$selected_template = $selected_template_config; 
		 return OPCmini::$selected_template; 
	 }
	 
	 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');  
	 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');  
	 OPCplugin::detectMobile(); 
	 
	 $selected = JRequest::getVar('opc_theme', null); 
	  {
	    if (!empty($selected))
		 {
			 
		    jimport( 'joomla.filesystem.file' );
		    $selected = JFile::makeSafe($selected); 
			$dir = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected;
			
			if (file_exists($dir) && ($selected != 'extra'))
			{
			
		     OPCmini::$selected_template = $selected; 
			 if (!defined('OPC_DETECTED_DEVICE'))
			 define('OPC_DETECTED_DEVICE', 'DESKTOP'); 
			 return OPCmini::$selected_template; 
			}
		 }
		 else
		 {
			
		 }
	  }
	 
	 if (!defined('OPC_DETECTED_DEVICE'))
	 {
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php'); 
	  OPCplugin::detectMobile(); 
	 }
	 
	 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
	 
	
		$selected_template = $selected_template_config; 
		$mobile_template = $mobile_template_config; 
	 
	 
	 
	 if (!empty($selected_template) && ($selected_template != 'extra'))
     OPCmini::$selected_template = $selected_template; 
	 
	 if (defined('OPC_DETECTED_DEVICE') && (OPC_DETECTED_DEVICE != 'DESKTOP'))
     if (!empty($mobile_template)) {
		 OPCmini::$selected_template = $selected_template = $mobile_template; 	 
	 
	 
	 }
 
	 if (class_exists('OPCloader'))
	 if (OPCloader::checkOPCSecret())
     {
	  if (!empty($selected_template) && ($selected_template != 'extra'))
	  OPCmini::$selected_template .= '_preview'; 
     }
	 
	 
	 

	 
	 return OPCmini::$selected_template; 
	}
 public static function isSuperVendor(){

 if (!class_exists('VmConfig'))	  
	 {
	  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	  VmConfig::loadConfig(); 
	 }
 
	if ((!defined('VM_VERSION')) || (VM_VERSION < 3))
			{
			if (!class_exists('Permissions'))
			require(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'permissions.php');
			if (Permissions::getInstance()->check("admin,storeadmin")) {
				return true; 
				
			}
			}
			else
			{
			 $text = '';
			$user = JFactory::getUser();
			if($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart') or VmConfig::isSuperVendor()) {
			  return true; 
			}
			}
			
			return false; 
	}
 
   public static $cache; 
   static function clearTableExistsCache()
   {
    OPCmini::$cache = array(); 
   }
   
   // -1 for a DB error, true for has index and false for does not have index
   public static function hasIndex($table, $column, $isunique=false)
   {
	   
	   
	    $db = JFactory::getDBO(); 
		$prefix = $db->getPrefix();
	    $table = str_replace('#__', '', $table); 
		$table = str_replace($prefix, '', $table); 
		$table = $db->getPrefix().$table; 
	    if (!OPCmini::tableExists($table)) return -1; 
	    $q = "SHOW INDEX FROM `".$table."`"; 
		try
		{
		 $db->setQuery($q); 
		 $r = $db->loadAssocList(); 
		}
		catch (Exception $e)
		{
			//JFactory::getApplication()->enqueueMessage($e); 
			
			return -1; 
		}
		
		if (empty($r)) return false; 
		
		$composite = array(); 

		$toreturn = -1; 
		
		
		foreach ($r as $k=>$row)
		{
		OPCmini::toUpperKeys($row); 
		
		if ((!empty($row['NON_UNIQUE'])) && (!empty($isunique))) {
			
			
			continue; 
		}
		
		if (isset($row['KEY_NAME'])) {
		  if (empty($composite[$row['KEY_NAME']])) $composite[$row['KEY_NAME']] = array(); 
		  $composite[$row['KEY_NAME']][] = $row['COLUMN_NAME']; 
		}
		/*
		foreach ($row as $kn=>$data)
		{
			$kk = strtolower($kn); 
			
			if (($kk === 'key_name') || ($kk === 'column_name'))
			{
				
				
				$dt = strtolower($data); 
				$c = strtolower($column); 
				if ($dt === $c) $toreturn = true; 
				if ($dt === $c.'_index') $toreturn = true; 
			}
		}
		*/
		}
		if (!is_array($column)) {
			
		  $c = strtolower($column);
		foreach ($composite as $z=>$r2) {
		  $first = $r2[0]; 
		  $first = strtolower($first); 

		  if ($first === $c) {
			 
			  return true; 
		  }
		  if ($first === $c.'_index') return true; 
		  
		 //echo 'first: '.$first."<br />\n";
		 //echo 'c: '.$c."<br />\n";
		  
		}
		
		 
		
		}
		else
		{
			foreach ($composite as $z=>$r2) {
				$ok = false; 
				foreach ($column as $c) {
				   $rx = $r2; 
				   if (!in_array($c, $r2)) {
					   $ok = false; 
					   continue; 
				   }
				   $ok = true; 
				}
				
				if ($ok) return true; 
				
			}
		}
		
		
		
		return false; 
	   
   }
   public static function toUpperKeys(&$arr) {
	   $arr2 = array(); 
	   foreach ($arr as $k=>$v)
	   {
		   if (is_string($k)) {
		     $arr2[strtoupper($k)] = $v; 
		   }
		   else
		   {
			   $arr2[$k] = $v; 
		   }
	   }
	   $arr = $arr2; 
   
   }
   static function addIndex($table, $cols=array(), $isUnique=false)
   {
	   if (empty($cols)) return ''; 
	   $db = JFactory::getDBO(); 
		$prefix = $db->getPrefix();
	    $table = str_replace('#__', '', $table); 
		$table = str_replace($prefix, '', $table); 
		$table_no_prefix = $table; 
		$table = $db->getPrefix().$table; 
		
		
		
		if (!OPCmini::tableExists($table)) return 'Table does not exist !'; 
		
		$name = reset($cols); 
		if ($isUnique) {
			$name .= '_uindex'; 
		}
		else {
		 $name .= '_index'; 
		}
		foreach ($cols as $k=>$v)
		{
			if (!is_numeric($k)) { $name = $k; }
			$cols[$k] = '`'.$db->escape($v).'`'; 
		}
		$cols = implode(', ', $cols); 
		
		if ($isUnique) {
		 //ALTER TABLE `vepao_virtuemart_products` ADD UNIQUE `product_sku` (`product_sku`);
		 $q = "ALTER TABLE  `".$table."` ADD UNIQUE  `".$db->escape($name)."` (  ".$cols." ) "; 
		}
		else {
		 $q = "ALTER TABLE  `".$table."` ADD INDEX  `".$db->escape($name)."` (  ".$cols." ) "; 
		}
		try {
		 $db->setQuery($q); 
		 $db->execute(); 
		}
		catch (Exception $e)
		{
		   //JFactory::getApplication()->enqueueMessage($e); 
		   return (string)$e; 
		}
		
		$ign_key = 'ignore.index.'.$table_no_prefix.'.'.$name;
		self::writeVMConfig($ign_key, 1); 
		
		
		return ''; 
   }
   
   public static function writeVMConfig($key, $val) {
	    $val = (string)$val; 
	    $ign_line = $key.'='.$val; 
		$cfg = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'virtuemart.cfg';
		$written = false; 
		
		if (file_exists($cfg)) {
			try {
				$datas = @parse_ini_file($cfg, false); 
				if (!isset($datas[$key])) {
					$new_data = "\r\n".'# '.date("Y-m-d H:i:s").' - OPC Optimalizations: '."\r\n"; 
					$new_data .= $ign_line."\r\n"; 
					file_put_contents($cfg, $new_data, FILE_APPEND); 
					$written = true; 
				}
			}
			catch (Exception $e) {
			}
			if (empty($written)) {
				$x = file_get_contents($cfg); 
				if (strpos($x, $ign_line) === false) {
					if (strpos($x, "\r\r\n") !== false) {
						$el = "\r\r\n"; 
					}
					elseif (strpos($x, "\r\n") !== false) {
						$el = "\r\n"; 
					}
					else {
						$el = "\n"; 
					}
					$new_data = $el.'# '.date("Y-m-d H:i:s").' - OPC Optimalizations: '.$el; 
					$new_data .= $ign_line.$el; 
					file_put_contents($cfg, $new_data, FILE_APPEND); 
				}
			}
			
		}
		if (OPCmini::tableExists('virtuemart_configs')) {
		if (! class_exists('VmConfig')) {
            require (JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
        }
		
		$config = VmConfig::loadConfig(); 
		$test = VmConfig::get($key, ''); 
		if ($test != $val) 	{
			
			
			$db = JFactory::getDBO(); 
			$q = 'select `config` from #__virtuemart_configs where `virtuemart_config_id` = 1'; 
			$db->setQuery($q); 
			$config = $db->loadResult(); 
			$test = explode('|', $config); 
			if (count($test) > 10 ) {
			
			if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
				$config .= '|'.$key.'='.json_encode((string)$val);
			}
			else {
				return; 
			}
			
			
			
			$q = 'update `#__virtuemart_configs` set `config` = \''.$db->escape($config).'\' where `virtuemart_config_id` = 1';
			$db->setQuery($q); 
			$db->execute(); 
			
			$config = VmConfig::loadConfig(true); 
			
			}
			}
			
		}
		
		
   }
   
   public static function getCountryByID($id, $what = 'country_name' ) {
		static $c; 
		if (isset($c[$id.'_'.$what])) return $c[$id.'_'.$what]; 
		
		if (!class_exists('ShopFunctions'))
		   require(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'shopfunctions.php');
	   
		$ret = (string)shopFunctions::getCountryByID($id, $what); 
	    $c[$id.'_'.$what] = $ret; 
		return $ret; 
	}
	
   static function tableExists($table)
  {
   
   
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
   
   
   
   if (isset(OPCmini::$cache[$table])) return OPCmini::$cache[$table]; 
   
   $q = 'select * from '.$table.' where 1 limit 0,1';
   // stAn, it's much faster to do a positive select then to do a show tables like...
    /*
	if(version_compare(JVERSION,'3.0.0','ge')) 
	{
	try
    {
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		
		if (!empty($res))
		{
			OPCmini::$cache[$table] = true; 
			return true;
		}
		
		
		
    } catch (Exception $e)
	{
		  $e = (string)$e; 
	}
	}
    */	
   
   $q = "SHOW TABLES LIKE '".$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   
	   if (empty(OPCmini::$cache)) OPCmini::$cache = array(); 
	   
	   if (!empty($r)) 
	    {
		OPCmini::$cache[$table] = true; 
		return true;
		}
		OPCmini::$cache[$table] = false; 
   return false;
  }

     // moved from opc loaders so we do not load loader when not needed
	static $modelCache; 
   	public static function getModel($model)
	 {
	 
	 // make sure VM is loaded:
	 if (!class_exists('VmConfig'))	  
	 {
	  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	  VmConfig::loadConfig(); 
	 }
		if (empty(OPCmini::$modelCache)) OPCmini::$modelCache = array(); 
	    if (!empty(OPCmini::$modelCache[$model])) return OPCmini::$modelCache[$model]; 
		
		
	    if (!class_exists('VirtueMartModel'.ucfirst($model)))
		require(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. strtolower($model).'.php');
		if ((method_exists('VmModel', 'getModel')))
		{
		
		$view = JRequest::getWord('view','virtuemart');
		
		$resetview = false; 
		if (empty($view))
		{
			$view = JRequest::setVar('view','virtuemart');
			$resetview = true; 
		}
		
		$Omodel = VmModel::getModel($model); 
		
		if ($resetview)
		{
			$view = JRequest::setVar('view','');
		}
		
		OPCmini::$modelCache[$model] = $Omodel; 
		return $Omodel; 
		}
		else
		{
			// this section loads models for VM2.0.0 to VM2.0.4
		   $class = 'VirtueMartModel'.ucfirst($model); 
		   if (class_exists($class))
		    {
				
				if ($class == 'VirtueMartModelUser')
				{
				
				//require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'user.php'); 
				//$class .= 'Override'; 
				
				 $Omodel = new VirtueMartModelUser; 
				 
				 return $Omodel; 
				 $Omodel->setMainTable('virtuemart_vmusers');
				 
				}
				
				
			    $Omodel = new $class(); 
				
			  OPCmini::$modelCache[$model] = $Omodel; 
			  return $Omodel; 
			}
			else
			{  
			  echo 'Class not found: '.$class; 
			  $app = JFactory::getApplication()->close(); 
			}
			
		}
		echo 'Model not found: '.$model; 
		$app = JFactory::getApplication()->close(); 
		
		//return new ${'VirtueMartModel'.ucfirst($model)}(); 
	 
	 }	
	 
	 public static function slash($string, $insingle = true)
	 {
	    $string = str_replace("\r\r\n", " ", $string); 
   $string = str_replace("\r\n", " ", $string); 
   $string = str_replace("\n", " ", $string); 
   $string = (string)$string; 
   if ($insingle)
    {
	 $string = addslashes($string); 
     $string = str_replace('/"', '"', $string); 
	 return $string; 
	}
	else
	{
	  $string = addslashes($string); 
	  $string = str_replace("/'", "'", $string); 
	  return $string; 
	}
	 
	 }
	 
	 public static function getArticleInLang($article_id, $langTag=null, $repVals=array(), $returnId=false) {
		if (empty($article_id)) return ''; 
		$article_id = (int)$article_id; 
		
 $db = JFactory::getDBO();
 if (class_exists('JLanguageAssociations')) { 
 $advClause = array(); 
 
 if (empty($langTag)) {
	$currentLang = JFactory::getLanguage()->getTag();
 }
 else {
	 $currentLang = $langTag; 
 }
 $advClause[] = 'c2.language = ' . $db->quote($currentLang); 
 
 $associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $article_id, 'id', 'alias', 'catid', $advClause);
 $return = array(); 
 foreach ($associations as $tag => $item) {
	 
	 if ($item->language === $currentLang) {
		 
		 $article_idT = $item->id; 
		 
		 if (strpos($article_idT, ':')!==false) {
			 $e = explode(':', $article_idT); 
			 $e[0] = (int)$e[0]; 
			 if (!empty($e[0])) {
			  $article_id = $e[0]; 
			 }
		 }
		 else {
			 $article_id = $article_idT;
		 }
		 break; 
	 }
 
 }
 }
 
	$article_id = (int)$article_id; 
	
	if (!empty($returnId)) {
		return $article_id; 
	}
	
	if (empty($article_id)) return ''; 
	return self::getArticle($article_id, $repVals); 
 
	}

	 public static function getArticle($id, $repvals=array())
	{
		$id = (int)$id; 
		if (empty($id)) return ''; 
		$article = JTable::getInstance("content");
		
		$article->load($id);
		
		
		
		if (!class_exists('CurrencyDisplay'))
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
		$currencyDisplay = CurrencyDisplay::getInstance();

		
		
		$parametar = new OPCParameter($article->attribs);
		$x = $parametar->get('show_title', false); 
		$x2 = $parametar->get('title_show', false); 
		
		$intro = $article->get('introtext'); 
		$full = $article->get("fulltext"); 
		JPluginHelper::importPlugin('content'); 
		$dispatcher = JDispatcher::getInstance(); 
		$mainframe = JFactory::getApplication(); 
		$params = $mainframe->getParams('com_content'); 
		
		if ($x || $x2)
		{
			
			

			$title = '<div class="componentheading'.$params->get('pageclass_sfx').'">'.$article->get('title').'</div>';
			
		}
		else $title = ''; 
		if (empty($article->text))
		$article->text = $title.$intro.$full; 
		
		if (!empty($repvals))
		foreach ($repvals as $key=>$val)
		{
			if ((is_array($val)) || (is_object($val)))
			{
				foreach ($val as $k2=>$nval)
				{
					if (!is_string($nval)) continue; 
					
					if ((stripos($k2, 'price')!==false) && (is_numeric($nval)))
					{
						$nval = (float)$nval; 
						$nval2 = $currencyDisplay->priceDisplay ($nval);
						
						$article->text = str_replace('{'.$k2.'_text}', $nval2, $article->text); 
					}
					
					if (is_string($nval))
					$article->text = str_replace('{'.$k2.'}', $nval, $article->text); 
				}
			}
			else
			{
				if (!is_string($val)) continue; 
				$article->text = str_replace('{'.$key.'}', $val, $article->text); 
			}
		}
		
		$results = $dispatcher->trigger('onPrepareContent', array( &$article, &$params, 0)); 
		$results = $dispatcher->trigger('onContentPrepare', array( 'text', &$article, &$params, 0)); 
		
		return $article->get('text');
		
		
	}

	public static function getSefLangCode($tag='')
	{
		$langO = JFactory::getLanguage();
		$lang = JRequest::getVar('lang', ''); 
		$lang = preg_replace('/[^a-zA-Z0-9\-]/', '', $lang);
		$locales = $langO->getLocale();
		if (empty($tag)) {
		  $tag = $langO->getTag(); 
		}
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
				//$action_url .= '&amp;lang='.$locales[6]; 
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
	
	
	
	/* IMPORTANT - YOU MUST MODIFY YOUR TEMPLATES index.php FOR THIS TO WORK PROPERLY UNLESS FIXED IN LATER JOOMLA:
	
	//ADD THIS TO TOP OF YOUR index.php after php tag start:
	
	if (JFactory::getApplication()->get('canon_alt_set')) {
	if (!empty($doc->_links))
	foreach ($doc->_links as $url => $l) {
		if ($l['relation'] === 'canonical') { unset($doc->_links[$url]); }
		if ($l['relation'] === 'alternate') { unset($doc->_links[$url]); }
		}
	}
	
	*/


	
	
	public static function addProductAlternateUrls($product, &$canonical='') {
		
		$doc = JFactory::getDocument(); 
		if (!method_exists($doc, 'addHeadLink')) return; 
		
		if (empty($product->virtuemart_product_id)) return; 
		$currentlanguageTag = JFactory::getLanguage()->getTag(); 
		$vars = JFactory::getApplication()->getRouter()->getVars(); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'google_sitemap.php'); 
		$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$Google_sitemapXml = new Google_sitemapXml(null, null); 
		
		$url = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id;
		if (!empty($product->product_canon_category_id)) {
			$url .= '&virtuemart_category_id='.$product->product_canon_category_id;
		}
		
		 $multi_lang_links = $Google_sitemapXml->getList($url); 
		 
		
		$langs = JLanguageHelper::getLanguages();
		
	
	$canonUrl = ''; 
	foreach ($langs as $sef => $l) {
		
		if (!isset($multi_lang_links[$l->lang_code])) continue; 
		$sefl = $l->sef; 
		$url .= '&lang='.$sefl; 
		
		$sefurl = $multi_lang_links[$l->lang_code]; 
		
		
		$root = JUri::root(); 
		if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
		//$sefurl = $root.$sefurl; 
		if ($l->lang_code === $default_lang) {
			//$sefurl = str_replace($root.'/'.$l->sef.'/', $root.'/', $sefurl); //remove /en/ from URL for default lang
			
			$doc->addCustomTag('<link href="' . htmlspecialchars($sefurl, ENT_QUOTES, 'UTF-8') . '" rel="alternate" hreflang="x-default" />');
		}
		if ($l->lang_code === $currentlanguageTag) {
		  $canonUrl = $sefurl; 
		  $canonical = $canonUrl; 
		   $doc->addCustomTag('<link href="' . htmlspecialchars($canonUrl, ENT_QUOTES, 'UTF-8') . '" rel="canonical" />');
		}
		
		$doc->addCustomTag('<link href="'.htmlspecialchars($sefurl, ENT_QUOTES, 'UTF-8').'" rel="alternate" hreflang="'.htmlentities($l->lang_code).'" />');
		
		
		
	}
	
	


	
	JFactory::getApplication()->getRouter()->setVars($vars, false); 
	JFactory::getApplication()->set('canon_alt_set', true); 
	return $multi_lang_links; 
	}
	
	public static function addCategoryAlternateUrls($category, &$canonical='') {
		
		
		
		
		
		$doc = JFactory::getDocument(); 
		if (!method_exists($doc, 'addHeadLink')) return; 
		
		if (empty($category->virtuemart_category_id)) return; 
		
		$vars = JFactory::getApplication()->getRouter()->getVars(); 
		$currentlanguageTag = JFactory::getLanguage()->getTag(); 
		$langOrig		= JFactory::getLanguage()->getTag();
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'google_sitemap.php'); 
		$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$Google_sitemapXml = new Google_sitemapXml(null, null); 
		
		$url = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id;
		
		
		 $multi_lang_links = $Google_sitemapXml->getList($url); 
		 
		
		 $langs = JLanguageHelper::getLanguages();
		
		
		
		
	
	$canonUrl = ''; 
	foreach ($langs as $sef => $l) {
		
		if (!isset($multi_lang_links[$l->lang_code])) continue; 
		$sefurl = $multi_lang_links[$l->lang_code]; 
		$root = JUri::root(); 
		if ($l->lang_code === $default_lang) {
			$doc->addCustomTag('<link href="' . htmlspecialchars($sefurl, ENT_QUOTES, 'UTF-8') . '" rel="alternate" hreflang="x-default" />');
		}
		if ($l->lang_code === $currentlanguageTag) {
		  $canonUrl = $sefurl; 
		  $canonical = $canonUrl; 
		  $doc->addCustomTag('<link href="' . htmlspecialchars($canonUrl, ENT_QUOTES, 'UTF-8')  . '" rel="canonical" />');
		}
		$doc->addCustomTag('<link href="'.htmlspecialchars($sefurl, ENT_QUOTES, 'UTF-8').'" rel="alternate" hreflang="'.htmlentities($l->lang_code).'" />');
		
		
		
		
		
		
		
	}
	
	JFactory::getApplication()->getRouter()->setVars($vars, false); 
	
	JFactory::getApplication()->set('canon_alt_set', true); 
	return $multi_lang_links; 
	}
 
	
 
}