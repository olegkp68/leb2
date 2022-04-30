<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/
 
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;


//stAn - this is not a security measure, this is only to save memory:
if (strpos($_SERVER['SCRIPT_FILENAME'], '/administrator/index.php') !== false) {
	require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'ajaxbackend.php'); 
}
else {
	require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'ajaxfrontend.php'); 
}

class plgVmCouponOpccoupon extends OpccouponAjax {
	
	public function __construct(& $subject, $config){
		
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_vmcoupon_opccoupon', __DIR__); 
		
		$platform = $this->params->get('platform', 'example'); 
		$this->validator = false; 
		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.$platform.DIRECTORY_SEPARATOR.'validator.php')) {
			require_once(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.$platform.DIRECTORY_SEPARATOR.'validator.php'); 
			$className = strtolower($platform.'Validator'); 
			if (class_exists($className)) {
				$this->validator = new $className($this); 
			}
		}
		if (empty($this->validator))
		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.'example'.DIRECTORY_SEPARATOR.'validator.php')) {
			require_once(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.'example'.DIRECTORY_SEPARATOR.'validator.php'); 
			$className = strtolower('exampleValidator'); 
			if (class_exists($className)) {
				$this->validator = new $className($this); 
			}
		}
		
		  
		
		
	}
	
	function plgOpcEmptyCoupon(&$cart, &$msg='') {
		$this->removeFreeProduct($cart); 
		//if (empty($msg)) $msg = ''; 
		//$msg .= JText::_('PLG_VMCOUPON_OPCCOUPON_CANNOTBE_APPLIED'); 
	}
	
	static $code; 
	static $msg; 
	static $silent; 
	function plgVmValidateCouponCode($_code,$_billTotal) {
		
		
		
		static $inRecursion; 
		self::$code = $_code; 
		if (!empty($inRecursion)) {
			self::$silent = false; 
			return ''; 
		}
		
		if (empty(self::$silent)) {
			self::$silent = false; 
			if (class_exists('VirtueMartControllerOpc')) {
			  VirtueMartControllerOpc::$clear_previous_msgs = true; 
			}
		}
		
		
		if (class_exists('OPCloader')) {
			
			$x = debug_backtrace(); 
			$b = array(); 
			foreach ($x as $l) {
				$b[] = $l['file'].' '.$l['line']; 
			}
			  OPCloader::opcDebug(array('silent' => self::$silent, 'code' => $_code, 'b' => $b), 'opc coupon '.__LINE__); 
			}
		
		
		//$x = debug_backtrace(); foreach ($x as $l) echo $l['file'].' '.$l['line']."<br />\n"; 
		$ret = $this->validateCouponSyntax($_code); 
		require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php');
		if ($ret->ret === true) {
			
			
			
			$db = JFactory::getDBO(); 
			$q = 'select * from `#__virtuemart_coupons` where `coupon_code` LIKE \''.$db->escape($ret->coupon_code).'\''; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			//if (empty($res)) 
			{
				
				//var_dump($ret->coupon_code); die(); 
				
				$arr = array(); 
				if (!empty($res)) {
					$arr['virtuemart_coupon_id'] = (int)$res['virtuemart_coupon_id']; 
				}
				else {
				  $arr['virtuemart_coupon_id'] = 'NULL'; 
				}
				
				$arr['virtuemart_vendor_id'] = 1; 
				$arr['coupon_code'] = $ret->coupon_code; 
				$arr['percent_or_total'] = 'percent'; 
				$arr['coupon_type'] = 'permanent'; 
				$arr['coupon_value'] = $this->params->get('discount_percent', 5); 
				//$arr['coupon_start_date'] = 'NOW()'; 
				//$arr['coupon_expiry_date'] = 'NOW()'; 
				$arr['coupon_value_valid'] = 0; 
				if (empty($res)) {
					$arr['created_on'] = 'NOW()'; 
				}
				$arr['modified_on'] = 'NOW()'; 
				
				if ((!empty($res)) && ((float)$arr['coupon_value'] != (float)$res['coupon_value'])) {
					OPCMini::insertArray('#__virtuemart_coupons', $arr); 
				}
				else
				if (empty($res)) {
					OPCMini::insertArray('#__virtuemart_coupons', $arr); 
				}
				
				//var_dump($arr); die(); 
				
			}
				$cart = VirtuemartCart::getCart(); 
				$cart->couponCode = $ret->coupon_code; 
				//$cart = OPCmini::getCart(); 
				$retR = $this->checkRestrictions($cart, $_code); 
				
				
				
				if (!$this->checkTimeValidity($ret->coupon_code)) {
					
					$this->removeFreeProduct($cart); 
					$cart->couponCode = ''; 
					if (empty(self::$silent))
					if (empty(self::$msg)) 
					{
					
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
					$is_multi_step = OPCconfig::get('is_multi_step', false); 
					$step = JRequest::getInt('step', 0); 
					$stepX = $step; 
					$checkout_steps = OPCconfig::get('checkout_steps', array()); 
					if (!empty($is_multi_step))
					{
					if (((isset($checkout_steps[$stepX])) && (in_array('op_coupon', $checkout_steps[$stepX])))) 
					{
						$cd = JRequest::getVar('coupon_code', JRequest::getVar('new_coupon', '')); 
					if (!empty($cd)) {
					self::$msg = JText::_('PLG_VMCOUPON_OPCCOUPON_CANNOTBE_APPLIED');  
					JFactory::getApplication()->enqueueMessage(self::$msg, 'info'); 
					OPCmini::setMsg(self::$msg); 
					
					 //unset coupon code which cannot be used second time:
					
					 return JText::_('PLG_VMCOUPON_OPCCOUPON_CANNOTBE_APPLIED'); //this clears the coupon
					}
					}
					else {
						
						
						
						return ''; 
					}
					}
					
					
					return JText::_('PLG_VMCOUPON_OPCCOUPON_CANNOTBE_APPLIED'); 
					
					$cd = JRequest::getVar('coupon_code', JRequest::getVar('new_coupon', '')); 
					if (!empty($cd)) {
					self::$msg = JText::_('PLG_VMCOUPON_OPCCOUPON_CANNOTBE_APPLIED');  
					JFactory::getApplication()->enqueueMessage(self::$msg, 'info'); 
					OPCmini::setMsg(self::$msg); 
					}
					return ''; 
					}
					
					
				}
				
				$inRecursion= false; 
				//if ($ret === true) 
				{
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
					$is_multi_step = OPCconfig::get('is_multi_step', false); 
					$step = JRequest::getInt('step', 0); 
					$stepX = $step; 
					$checkout_steps = OPCconfig::get('checkout_steps', array()); 
					if (!empty($is_multi_step))
					{
					if (((isset($checkout_steps[$stepX])) && (in_array('op_coupon', $checkout_steps[$stepX])))) 
					{
						//we are OK
					}
					else {
						
						
						self::$silent = true; 
						return ''; 
					}
					}
					
					if (empty(self::$silent))
					if (empty(self::$msg)) {
					
					$cd = JRequest::getVar('coupon_code', JRequest::getVar('new_coupon', '')); 
					
					
					if (!empty($cd)) {
						if (!empty($cart->products)) {
						$_billTotal = $this->calculateSubtotal($cart); 
						
						$threashorld = (float)$this->params->get('order_amount', 0); 
						if (($_billTotal < $threashorld) && (!empty($_billTotal))) {
							self::$msg = JText::_('PLG_VMCOUPON_OPCCOUPON_APPLIED2');  
							
							
							$remainingF = (float)($threashorld - $_billTotal);
							$remaining = (int)ceil($remainingF);
							self::$msg = str_replace('{remaining}', $remaining, self::$msg); 
							self::$msg = str_replace('{order_amount}', (int)$threashorld, self::$msg); 
							JFactory::getApplication()->enqueueMessage(self::$msg, 'info'); 
							OPCmini::setMsg(self::$msg); 
							return ''; 
						}
						}
						
						
					self::$msg = JText::_('PLG_VMCOUPON_OPCCOUPON_APPLIED');  
					JFactory::getApplication()->enqueueMessage(self::$msg, 'info'); 
					OPCmini::setMsg(self::$msg); 
					}
					return ''; 
					
					
					
					}
					//true will show "1" as a message
					//null will proceed with awo and VM and will give an error
					//false will just follow up and show 2 msgs
					return false; 
				}
				return $retR; 
		
		}
		else {
			
			$cart = VirtuemartCart::getCart(); 
			$this->removeFreeProduct($cart); 
			/*
			//$cart = OPCmini::getCart(); 
			foreach ($cart->cartProductsData as $kx=>$d) {
				$pid = $this->params->get('productID', 0); 
				if (!empty($pid))
				if ($d['virtuemart_product_id'] == $pid) {
					//$cart->removeProductCart($kx); 
					unset($cart->cartProductsData[$kx]); 
					unset($cart->products[$kx]); 
					
				}
			}
			*/
			
			
			
		}
		$inRecursion= false; 
		return null; 
	}
	
	function awoDefineCartItemsAfter(&$itemsdef, &$items, $prodcts, $ref) {
		
	}
	
	function awoIsHandledElsewhere($coupon_code) {
		$isValid = $this->validateCouponSyntax($coupon_code); 
		if ($isValid->ret === true) {
					return true; 
				}
			return false;
	}
	
	function checkCreateTable() {
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_opccoupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `virtuemart_order_id` int(11) NOT NULL,
  `coupon_code` varchar(160) NOT NULL DEFAULT \'\',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_order_id` (`virtuemart_order_id`),
  KEY `coupon_code` (`coupon_code`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$db->execute(); 
	
	
	
	}
	
	
	//returns true if valid in time, returns false if not valid it time
	function checkTimeValidity($coupon_code) {
		
		if (empty($this->validator)) return true; 
		
		return $this->validator->checkTimeValidity($coupon_code); 
	}
	function plgOpcOrderCreated($cart, $order) {
		$this->plgVmConfirmedOrder($cart, $order); 
	}
	
	function plgVmConfirmedOrder($cart, $order) {
		static $done; 
		if (!empty($done))
		{
			return; 
		}
		$done = true; 
		
		if (!empty($cart->couponCode)) {
			$coupon_code = $cart->couponCode;
		}
		else {
	      $coupon_code = $order['details']['BT']->coupon_code; 
		}
		
		if (empty($coupon_code)) {
			$coupon_code = JRequest::getVar('opc_coupon_code_returned', JRequest::getVar('coupon_code', '')); 
		}
		
		if (!empty($coupon_code)) {
			$ret = $this->validateCouponSyntax($coupon_code); 
			
			if ($ret->ret === true) {
				require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php');
				$db = JFactory::getDBO();
				$this->checkCreateTable(); 
				$arr = array(); 
				$arr['id'] = 'NULL'; 
				$arr['virtuemart_order_id'] = (int)$order['details']['BT']->virtuemart_order_id; 
				$arr['coupon_code'] = $ret->coupon_code; 
				$arr['created_on'] = 'NOW()'; 
				OPCMini::insertArray('#__onepage_opccoupon', $arr); 
				
				
				
			}
		}
	}
	
	function plgOnOrderFilter(&$orders=array()) {
		
		if (!empty($orders)) {
			//check if me: 
			$order_filter = JRequest::getInt('order_filter', 0); 
			if ($order_filter === 1000) {
				foreach ($orders as $xx => $order) {
					$coupon_code = $order['coupon_code']; 
					if (empty($coupon_code)) {
						unset($orders[$xx]); 
						continue; 
					}
					$isValid = $this->validateCouponSyntax($coupon_code); 
					if ($isValid->ret !== true) {
						unset($orders[$xx]); 
						continue; 
					}
				}
			}
		}
		
		
		//return filter definition: 
		$ret = new stdClass(); 
		$ret->value = 1000;  //random non-conflicting ID
		$ret->text = 'PLG_VMCOUPON_OPCCOUPON_FILTER';
		return $ret; 
		
	}
	//defined in: \administrator\components\com_onepage\views\order_export\view.html.php
	function plgOnOrderExport($what, $startdate='', $enddate='', $start_order_id=0, $end_order_id=0) {
		if ($what !== 1000) return null; 
		
		$platform = $this->params->get('platform', 'example'); 
		$this->exporter = false; 
		
		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.$platform.DIRECTORY_SEPARATOR.'export.php')) {
		
			require_once(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.$platform.DIRECTORY_SEPARATOR.'export.php'); 
			$className = strtolower($platform.'Export'); 
			if (class_exists($className)) {
				$this->exporter = new $className($this, $this->validator); 
			}
		}
		
		if (empty($this->exporter))
		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.'example'.DIRECTORY_SEPARATOR.'export.php')) {
			require_once(__DIR__.DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.'example'.DIRECTORY_SEPARATOR.'export.php'); 
			$className = strtolower('exampleExport'); 
			if (class_exists($className)) {
				$this->exporter = new $className($this, $this->validator); 
			}
		}
		
		$this->exporter->runExport($startdate, $enddate, $start_order_id, $end_order_id); 
		
		JFactory::getApplication()->close(); 
		return true; 
	}
	
	function plgVmRemoveCoupon($_code,$_force) {
		
		return null; 
	}
	
	function plgVmCouponInUse($_code) {
		return null; 
		
		 
	}
	
	function plgVmOnAddToCart(&$cart) {
		
	}
	
	function plgVmCouponHandler($_code, &$_cartData, &$_cartPrices) {
		static $inRecursion; 
		if (!empty($inRecursion)) return null; 
		
		
		
		$inRecursion= true; 
		require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php');
		$calc = calculationHelper::getInstance(); 
		
		if (empty($_code)) {
			$this->removeFreeProduct($calc->_cart); 
			return null; 
		}
		
		$salesPrice = $calc->_cart->cartPrices['salesPrice']; 
		if (empty($calc->_cart)) {
			$calc->_cart = OPCmini::getCart(); 
		}
		
		if (count($calc->_cart->cartProductsData) === 1) {
			$first = reset($calc->_cart->cartProductsData); 
			$pid = $this->params->get('productID', 0); 
			if (!empty($pid))
			if ($first['virtuemart_product_id'] == $pid) {
				$this->removeFreeProduct($calc->_cart); 
				return null; 
			}
		}
		self::$silent = true; 
		$msg = $this->plgVmValidateCouponCode($_code, $calc->_cart->cartData, $calc->_cart->cartPrices); 
		if (!empty($msg)) {
			$calc->_cart->couponCode = ''; 
		}
		self::$silent = false;
		
		$subtotal_with_tax = $this->calculateSubtotal($calc->_cart); 
		$ret = $this->checkRestrictions($calc->_cart, $_code); 
		
		if ($ret === true) {
		 $_cartPrices['couponValue'] = $calc->_cart->cartPrices['couponValue']; 
		 $_cartPrices['salesPriceCoupon'] = $calc->_cart->cartPrices['salesPriceCoupon']; 
		 $_cartPrices['couponTax'] = $calc->_cart->cartPrices['couponTax']; 
		}
		$inRecursion= false; 
		return $ret; 
	} 
	private function calculateSubtotal(&$cart) {
		$subtotal = 0; 
		foreach ($cart->cartProductsData as $kx => $d) {
			if (!isset($cart->products[$kx])) return 0; 
			
			$p = $cart->products[$kx]; 
			$slesPrice = (float)$p->allPrices[$p->selectedPrice]['salesPrice']; 
			$q = (float)$d['quantity']; 
			$subtotal += $q * $slesPrice;
			
		}
		
		return $subtotal; 
		
		
		
	}
	private function calculateSubtotalREMOVEDUERECURSION($cart) {
		require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'basket.php');
		if (empty($cart)) {
			$cart = OPCMini::getcart(); 
		}
		$subtotal = OPCBasket::calculateSubtotal($cart); 
		return $subtotal; 
		
	}
	
	
	private function checkRestrictions(&$cart, $_code='') {
			
			
			if (empty($_code)) {
			   if (empty($cart->couponCode)) return null;
				$_code = $cart->couponCode;
			}
			
			$ret = $this->validateCouponSyntax($_code); 
			if ($ret->ret !== true) {
				return null; 
			}
			if (!empty($cart->products)) {
				  $_billTotal = $this->calculateSubtotal($cart); 
				}
				
			
			$ref = new stdClass; 
			$ref->cart = $cart;

		$threashorld = (float)$this->params->get('order_amount', 0); 
		$pid = (int)$this->params->get('productID', 0); 
		if (($_billTotal >= $threashorld) && (!empty($_billTotal))) {
			if (!$this->checkTimeValidity($ret->coupon_code)) {
				$this->removeFreeProduct($cart); 
			    return null; 
			}
			
			
			require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'addtocartaslink.php');
			
			OPCAddToCartAsLink::addtocartaslink($ref, array($pid=>$pid), array($pid=>1), array(), false, 2); 
			$cart->couponValue = 0; 
			$cart->cartPrices['couponValue'] = 0.00001; 
			$cart->cartPrices['salesPriceCoupon'] = 0.00001;
			$cart->cartPrices['couponTax'] = 0; 			
			
			if (!empty(self::$code)) {
				$cart->couponCode = self::$code; 
			}
			
			if (!empty($cart->products))
			foreach ($cart->products as $k=>$p) {
					$p->virtuemart_product_id = (int)$p->virtuemart_product_id; 
					$pid = (int)$this->params->get('productID', 0); 
					if ($p->virtuemart_product_id === $pid) {
					$cart->products[$k]->opc_hide_quantity_controls = true; 
				}
			}				   
			
			return true; 
		}
		elseif (!empty($_billTotal)) {
			$this->removeFreeProduct($cart); 
			
			
			 
			
			//this one is cleared from spaces:
			$cart->couponCode = $ret->coupon_code;
			$cart->setCartIntoSession(); 
			JRequest::setVar('coupon_code', $ret->coupon_code); 
			
			//let VM process it
			return null; 
		}
		elseif (empty($_billTotal)) {
			
		}
		
		if (!$this->checkTimeValidity($ret->coupon_code)) {
			$this->removeFreeProduct($cart); 
		}
		
		return null; 
	}
	
	private function removeFreeProduct($cart) {
		foreach ($cart->cartProductsData as $kx=>$d) {
				$pid = $this->params->get('productID', 0); 
				if (!empty($pid))
				if ($d['virtuemart_product_id'] == $pid) {
					//$cart->removeProductCart($kx); 
					unset($cart->cartProductsData[$kx]); 
					unset($cart->products[$kx]); 
					
				}
			}
	}
	
	public function validateCouponSyntax($Relatienummer) {
		if (empty($this->validator)) {
			$Outputs = new stdClass; 
			$Outputs->ret = false; 
			$Outputs->msg = ''; 
			$Outputs->code = ''; 
			return $Outputs; 
		}
		return $this->validator->validateCouponSyntax($Relatienummer); 
		
	}
	
}