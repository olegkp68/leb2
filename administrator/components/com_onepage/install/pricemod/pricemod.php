<?php
/**
 * @version		pricemod.php 
 * @copyright	Copyright (C) 2005 - 2019 RuposTel.com
 * @license		COMMERCIAL !
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemPricemod extends JPlugin
{
	public static $calculator; 
	public static $lastcart; 
	public static $isFakeCartProces; 
	function plgCheckCartQuantities(&$cart) {
		
		self::$lastcart = $cart; 
		$this->plgAdjustCartPrices($cart); 
		
		
	}
	
    function __construct(& $subject, $config)
	{
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php')) return; 
		
		parent::__construct($subject, $config);
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	}
	
	function plgVmOnUpdateCart(&$cart, &$force, &$html) {
		
		if (!empty($cart->cartProductsData)) {
		 $this->plgAdjustCartPrices($cart, true); 
		 /*
		 foreach ($cart->products as $in=>$p) {
			 var_dump($p->prices['basePrice']); 
		 }
		 */
		 
		}
	}
	
	public function onAfterRoute() {
		
		//index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json&nosef=1&lang=sk&field%5B7468%5D%5B78858%5D%5Bcustomfield_value%5D%5B0%5D=37&quantity%5B%5D=122&virtuemart_product_id%5B%5D=7468&option=com_virtuemart&virtuemart_product_id%5B%5D=7468&pname=Obuv+ARDON+O1+%C4%8D.37&pid=7468&Itemid=1064&_=1555578026496
		$view = JRequest::getVar('view', ''); 
		$option = JRequest::getVar('option', ''); 
		$task = JRequest::getVar('task', ''); 
		$virtuemart_product_id = JRequest::getVar('virtuemart_product_id', ''); 
		$pid = JRequest::getVar('pid', ''); 
		if ($option === 'com_virtuemart') {
			if ($view === 'productdetails') {
				if ($task === 'recalculate') {
					
					if (!class_exists('VmConfig'))
					require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
					VmConfig::loadConfig(); 
					
					$virtuemart_product_idArray = JRequest::getVar('virtuemart_product_id', array()); 
					if (is_array($virtuemart_product_idArray)) {
						$virtuemart_product_id = (int)reset($virtuemart_product_idArray); 
					}
					else 
					{
						$virtuemart_product_id = (int)$virtuemart_product_idArray;
					}
	

		$quantity = 0;
		$quantityArray = JRequest::getVar('quantity', array()); 
		
		if(is_array($quantityArray)){
			$quantity = (int)reset($quantityArray); 
			
		} else {
			$quantity = (int)$quantityArray;
		}

		if (empty($quantity)) {
			$quantity = 1;
		}

		$product_model = VmModel::getModel ('product');
		$product = $product_model->getProduct($virtuemart_product_id, true, true, true, 1); //, $quantity); 
		if (empty($product->plgUpdateProductObject)) {
			
			if (!empty(self::$lastcart)) {
				 $cart = self::$lastcart; 
			 }
			 else {
			  if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
			  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
			  $cart = VirtuemartCart::getCart(); 
			  self::$lastcart = $cart; 
			 }
			}
			$group_quantity = $this->getGroupQuantity($cart, null, $virtuemart_product_id); 
			
			$group_quantity += $quantity; 
			
			
		    $this->plgUpdateProductObject($product, $group_quantity); 
			$prices = $product->allPrices[$product->selectedPrice];
			
			$priceFormated = array();

		$currency = CurrencyDisplay::getInstance ();

		foreach (CurrencyDisplay::$priceNames as $name) {
			if(isset($prices[$name])){
				
				$t1 = round($prices[$name], 4); 
			 $t2 = round($prices[$name], 2); 
			 
			 
			 /*
			 if ($t1 !== $t2) {
			  $priceFormated[$name] = $currency->priceDisplay($product->prices['priceWithoutTax'], 0, 1, false, 4); 
			 }
			 else {
				 $priceFormated[$name] = $currency->priceDisplay($product->prices['priceWithoutTax'], 0, 1, false, 2); 
			 }
			 */
				
				$priceFormated[$name] = $currency->createPriceDiv ($name, '', $prices, TRUE);
			}
		}

		// Also return all messages (in HTML format!):
		// Since we are in a JSON document, we have to temporarily switch the type to HTML
		// to make sure the html renderer is actually used
		@header('Content-Type: application/json');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		
		
		echo json_encode ($priceFormated);
		$app = JFactory::getApplication(); 
		$app->close(); 
			
		  
		}
		
				}
			}
		}
	}
	
	private function updateStepQuantity($p) {
		$p->step_order_level = (int)$p->step_order_level; 
		if (empty($p->step_order_level)) $p->step_order_level = 1; 
		$p->min_order_level = (int)$p->min_order_level; 
		if (empty($p->min_order_level)) $p->min_order_level = 1; 
		
		$quantity_exception = $this->params->get('quantity_exception', ''); 
		if (!empty($quantity_exception)) {
		$quantity_exception = str_replace(array("\r\r\n", "\r\n"), array("\n", "\n"), $quantity_exception); 
		$xa = explode("\n", $quantity_exception); 
		foreach ($xa as $test_sku) {
			$test_sku = trim($test_sku); 
			if ($p->product_sku === $test_sku) {
				return; 
			}
		}
		}
		 
		if ($p->step_order_level > 1)
		if ($this->params->get('ignq', false)) {
			
			
			if (($p->product_in_stock > $p->step_order_level) && ($p->product_in_stock > $p->min_order_level)) {
				//override step quantity + min quantity:
				$p->min_order_level = 1; 
				$p->step_order_level = 1; 
				
			

				
			}
		}
	}
	
	function plgVmOnCheckoutCheckStock( &$cart, &$p, &$quantity, &$errorMsg, &$adjustQ) {
		
		$this->updateStepQuantity($p); 
		return; 
		
		/*CAUSES RECURSION, DO NOT USE !*/
		if (empty($quantity)) return; 
		self::$lastcart = $cart; 
		
		static $inrecursion;
		if ($inrecursion) return;
		$inrecursion = true; 	

		
		if (!empty($product->plgUpdateProductObject)) return; 
		
		$group_quantity = $this->getGroupQuantity($cart, $product, 0, $quantity ); 
		
		if (empty($group_quantity)) $group_quantity = $quantity; 
		
		$this->plgUpdateProductObject($product, $group_quantity, $cart); 
		
		
		//$errorMsg .= 'step: '.var_export($product->step_order_level, true); 
		$inrecursion = false; 
		
		return true; 
	}
	
	private function canRun()
	{
		
		if (JFactory::getApplication()->isAdmin()) return false; 
		
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'))
			{
				
				return false;
			}
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		
	
	
	   //OPCNumbering::$debug = false; 
	   return true; 
	}
	
	public function onVmSiteController($controller) {
		if (empty(self::$lastcart)) {
			$cart = VirtuemartCart::getCart(); 
			self::$lastcart = $cart; 
		}
		else {
			$cart = self::$lastcart; 
		}
		if (!empty($cart->cartProductsData)) {
		 $this->plgAdjustCartPrices($cart); 
		}
	}
	public function plgVmgetPaymentCurrency( $virtuemart_paymentmethod_id, &$paymentCurrency) {
		
		static $inrecursion; 
		if (!empty($inrecursion)) return; 
		
		$inrecursion = true; 
		
		if (empty(self::$lastcart)) {
			$cart = VirtuemartCart::getCart(); 
			self::$lastcart = $cart; 
		}
		else {
			$cart = self::$lastcart; 
		}
		
		$this->plgVmUpdateAllProductsInCache(); 
		$this->plgAdjustCartPrices($cart); 
		
		$inrecursion = false; 
	}
	
	public function plgVmUpdateAllProductsInCache() {
		
		if (class_exists('VirtueMartModelProduct')) {
			
			if (!empty(VirtueMartModelProduct::$_products)) {
				foreach (VirtueMartModelProduct::$_products as $key => $p) {
					if (!empty(VirtueMartModelProduct::$_products[$key])) 
					{
					$this->saveMemoryOnProduct(VirtueMartModelProduct::$_products[$key]); 
					
					$this->plgUpdateProductObject(VirtueMartModelProduct::$_products[$key]); 
					}
				}
			}
		}
	}
	
	
	private static function getParentIds($productsIDs) {
		static $cache; 
		$cO = count($productsIDs); 
		$toRet = array(); 
	if (!empty($cache)) {
		
			foreach ($productsIDs as $ind => $id) {
			   if (isset($cache[$id])) {
				   $toRet[$id] = $cache[$id]; 
				   unset($productsIDs[$ind]); 
			   }				   
			}
		
	}
	else {
		$cache = array(); 
	}
	
	
	
	if (empty($productsIDs)) {
		
		return $toRet; 
	}
	
	
	
	
		$toSearch = array(); 
		foreach ($productsIDs as $id) {
			$id = (int)$id; 
			$toSearch[$id] = $id; 
		}
		if (!empty($toSearch)) {
		$db = JFactory::getDBO(); 
		$q = 'select `product_parent_id`, `virtuemart_product_id` from `#__virtuemart_products` where `virtuemart_product_id` IN ('.implode(',', $toSearch).')'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (!empty($res)) 
		foreach ($res as $row) {
			$virtuemart_parent_id = (int)$row['product_parent_id']; 
			$virtuemart_product_id = (int)$row['virtuemart_product_id']; 
			$toRet[$virtuemart_product_id] = $virtuemart_parent_id; 
			$cache[$virtuemart_product_id] = $virtuemart_parent_id; 
			
		}
		}
		
		return $toRet; 
	}
	
	private function saveMemoryOnProduct(&$product) {
		if (empty($product)) return; 
		if (!empty($product->savedMemoryOnProduct)) return; 
		
		$class = get_class($product); 
		if ($class === 'TableProducts') {
			$p = new stdClass(); 
			foreach ($product as $k=>$x) {
				
					if (strpos($k, '_') !== 0) 
					if (property_exists($product, $k)) 
					{
						$p->$k = $x;
					}
				
			}
		}
		else {
			$p = $product; 
		}
		
		
		$p->savedMemoryOnProduct = true; 
		$p->virtuemart_product_id  = (int)$p->virtuemart_product_id ; 
		$p->virtuemart_vendor_id   = (int)$p->virtuemart_vendor_id  ; 
		$p->product_parent_id   = (int)$p->product_parent_id; 
		$p->product_weight   = (float)$p->product_weight; 
		if (is_nan($p->product_weight)) $p->product_weight = 0.0; 
		
		$p->product_length    = (float)$p->product_length; 
		if (is_nan($p->product_length )) $p->product_length  = 0.0; 
		
		$p->product_length    = (float)$p->product_length; 
		if (is_nan($p->product_length )) $p->product_length  = 0.0; 
		
		$p->product_width    = (float)$p->product_width; 
		if (is_nan($p->product_width )) $p->product_width  = 0.0; 
		
		$p->product_height     = (float)$p->product_height ; 
		if (is_nan($p->product_height  )) $p->product_height   = 0.0; 
		
		$p->product_in_stock = (int)$p->product_in_stock; 
		if ($p->product_in_stock < 0) $p->product_in_stock = 0; 
		
		$p->product_ordered  = (int)$p->product_ordered ; 
		if ($p->product_ordered < 0) $p->product_ordered = 0; 
		
		$p->product_stockhandle  = (int)$p->product_stockhandle ; 
		$p->low_stock_notification  = (int)$p->low_stock_notification ; 
		$p->product_special  = (int)$p->product_special ; 
		$p->product_discontinued  = (int)$p->product_discontinued ; 
		$p->pordering  = (int)$p->pordering ; 
		
		
		$p->pordering  = (int)$p->pordering; 
		$p->product_sales   = (int)$p->product_sales; 
		$p->product_packaging   = (float)$p->product_packaging ; 
		if (is_nan($p->product_packaging )) $p->product_packaging  = 0.0; 
		
		$p->published   = (int)$p->published; 
		$p->product_canon_category_id  = (int)$p->product_canon_category_id;
		
		$product = $p; 
	}
	
	private function getGroupQuantity(&$cart, $product=null, $virtuemart_product_id=0, $new_quantity=0) {
		
		static $recursion; 
		
		if ($recursion) {
			
			//$x = debug_backtrace(); 
			//foreach($x as $l) 
			{
				
			}
		}
		
		
		$recursion = true; 
		
		if (!empty($product)) {
		 $current_product_id = (int)$product->virtuemart_product_id; 
		 $current_parent_id = (int)$product->product_parent_id; 
		}
		else {
			if (!empty($virtuemart_product_id)) {
				$current_product_id = $virtuemart_product_id; 
			}
		}
		
		if (empty($current_product_id)) {
			
			return 0; 
		}
		
		if (empty(self::$isFakeCartProces)) 
		if (empty(self::$lastcart)) {
			self::$lastcart = $cart; 
		}
		
		foreach ($cart->cartProductsData as $ind => $p) {
			$ids[(int)$p['virtuemart_product_id']] = (int)$p['virtuemart_product_id']; 
		}
	
		$ids[$current_product_id] = $current_product_id; 
		
		$parents = self::getParentIds($ids); 
	
		$current_parent_id = $parents[$current_product_id]; 
		
		
		
		$qa = array(); 
		$parent_id = (int)$current_product_id; 
		if (!empty($cart->cartProductsData)) {
		foreach ($cart->cartProductsData as $ind => $p) {
			$p['virtuemart_product_id'] = (int)$p['virtuemart_product_id']; 
			
			$parent_id = $parents[$p['virtuemart_product_id']]; 
			
			
			
			if ((int)$parent_id === (int)$current_parent_id) 
			{
			if (!empty($parent_id)) {
				
				if (empty($qa[$parent_id])) $qa[$parent_id] = 0; 
				$cart->cartProductsData[$ind]['quantity'] = (int)$cart->cartProductsData[$ind]['quantity']; 
				
				if ((!empty($new_quantity)) && ($current_product_id === $p['virtuemart_product_id'])) {
					$qa[$parent_id] += $new_quantity;
				}
				else {
				 $qa[$parent_id] += $cart->cartProductsData[$ind]['quantity']; 
				}
				
				
			}
			else {
				if ((int)$current_product_id === (int)$p['virtuemart_product_id']) {
				 if (empty($qa[0])) $qa[0] = 0; 
				 if ((!empty($new_quantity)) && ($current_product_id === $p['virtuemart_product_id'])) {
					$qa[0] += $new_quantity;
				}
				else {
				 $qa[0] += (int)$cart->cartProductsData[$ind]['quantity'];
				}
				}
			}
			}
			
		}
		}
		
		
		
		
		$recursion = false; 
		
		if (empty($qa)) return 0; 
		if (!isset($qa[$current_parent_id])) return 0; 
		
		
		return $qa[$current_parent_id]; 
		
	}
	
	public function plgAdjustCartPrices(&$cart, $force=false, $isFakeCart=false) {
		
		if (empty($isFakeCart)) {
			self::$lastcart = $cart; 
			self::$isFakeCartProces = false; 
		}
		else {
			self::$isFakeCartProces = true; 
		}
		
		//static $done; 
		//if (!empty($done)) return; 
		
		if (empty($cart->cartProductsData)) {
			self::$isFakeCartProces = false; 
			return; 
		}
		
		static $inrecursion2; 
		if (!empty($inrecursion2)) {
			self::$isFakeCartProces = false; 
			return; 
		}
		$inrecursion2 = true; 
		
		$qa = array(); 
		$debugmsg = array(); 
		$code = ''; 
		$debug = $this->params->get('debug', false); 
		$runCheckoutPrices = false; 
		
		/*
		foreach ($cart->products as $ind => $p) {
			if (!empty($p->product_parent_id)) {
				$parent_id = (int)$p->product_parent_id; 
				if (empty($qa[$parent_id])) $qa[$parent_id] = 0; 
				$cart->cartProductsData[$ind]['quantity'] = (int)$cart->cartProductsData[$ind]['quantity']; 
				$qa[$parent_id] += $cart->cartProductsData[$ind]['quantity']; 
			}
			else {
				
			}
		}
		*/
		
		
		
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		
		$highsg = $this->params->get('highsg', false); 
		foreach ($cart->products as $ind => $p) {
				
				
				
				
				$group_quantity = $this->getGroupQuantity($cart, $p); 
				/*
				var_dump($p->product_name); 
				var_dump($group_quantity); 
				*/
				
				
				
				if (empty($group_quantity)) continue; 
				
				$this->plgUpdateProductObject($cart->products[$ind], $group_quantity, $cart, $force, $force);
				
				 //var_dump($group_quantity);
				 //var_dump($cart->products[$ind]->prices['basePrice']); 
				
			
			if (!isset($cart->products[$ind]->prices['subTotal'])) {
				$runCheckoutPrices = false; 
			}
			
			
		}
		
		
		
		
		if ($runCheckoutPrices) {
			
			static $inrecursion; 
			
			
			
			if (empty($inrecursion) || ($inrecursion !== 1)) {
				
				if (empty(self::$calculator)) {
					self::$calculator = calculationHelper::getInstance ();
				}
				
				$inrecursion = 1; 
			    self::$calculator->getCheckoutPrices($cart); 
			}
			$inrecursion = 2;
		}
		
		$inrecursion2 = false; 
		$done = true; 
		
		self::$isFakeCartProces = false; 
	}
	
	public static $debugmsg; 
	public static $code; 
	
	
	private static function getAllPrices($p, $group_quantity) {
		static $cache; 
		if (isset($cache[$p->virtuemart_product_id])) return $cache[$p->virtuemart_product_id]; 
		$prices = self::$calculator->getProductPrices ($p, TRUE, $group_quantity);
		$cache[$p->virtuemart_product_id] = $prices; 
		return $prices; 
	}
	
	private static function calculateSalesPrice(&$p, $group_quantity) {
		$priceIndex = $p->selectedPrice;
		if (empty($p->allPrices[$priceIndex]['salesPrice'])) {
		$qprices = self::$calculator->getProductPrices ($p, TRUE, $group_quantity);
		$p->allPrices[$priceIndex] = $qprices;
		} 
		return $p->allPrices[$priceIndex]; 
	}
	
	public function plgUpdateProductObject(&$p, $group_quantity=0, &$cart=null, $skipCalc=false, $force=false) {
		
		
		
		
		static $inrecursion; 
		//if (!empty($inrecursion)) return; 
		$inrecursion = true; 
		
		static $done; 
		if (empty($force)) {
		if ((!empty($done[$p->virtuemart_product_id])) && (!empty($p->plgUpdateProductObject))) {
			return; 
		}
		}
		
		$current_sgs = array(); 
		if (!$skipCalc)
		if (empty(self::$calculator)) {
			self::$calculator = calculationHelper::getInstance ();
			$current_sgs = self::$calculator->getShopperGroupId(); 
		}
		
		
		$done[$p->virtuemart_product_id] = true; 
		
		
		
		$p->product_name = str_replace("\0", "", $p->product_name); 
		$p->product_s_desc = str_replace("\0", "", $p->product_s_desc); 
		$p->product_desc = str_replace("\0", "", $p->product_desc); 
		
		
		$p->plgUpdateProductObject = true; 
		$p->virtuemart_product_id = (int)$p->virtuemart_product_id; 
		
		$gp = false; 
		//if group_quantity is 0 we'll check the cart to calculate +1 current price
		if (empty($group_quantity)) {
			$group_quantity_was_zero = true; 
			
			$qp = true; 
			if (empty($cart)) {
			 if (!empty(self::$lastcart)) {
				 $cart = self::$lastcart; 
			 }
			 else {
			  if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
			  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
			  $cart = VirtuemartCart::getCart(); 
			  self::$lastcart = $cart; 
			 }
			}
			}
			else {
				
			}
			$group_quantity = $this->getGroupQuantity($cart, $p); 
			

			
			
			
		}
		else {
			$group_quantity_was_zero = false; 
		}
		
		$this->updateStepQuantity($p); 
		
		
		
			if ($force) {
				//var_dump($group_quantity); 
			}

		
		
		//index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json&nosef=1&lang=sk&field%5B56466%5D%5B248697%5D%5Bcustomfield_value%5D%5B0%5D=39&quantity%5B%5D=5&virtuemart_product_id%5B%5D=56466&option=com_virtuemart&virtuemart_product_id%5B%5D=56466&pname=Obuv+LEWER+DP2N+S3+%C4%8D.39&pid=56466&Itemid=1064&_=1556533005732
		if ($group_quantity_was_zero) {
			/*
		$task = JRequest::getVar('task', ''); 
		if ($task === 'recalculate') {
			$pid = JRequest::getVar('virtuemart_product_id', 0); 
			if (!empty($pid)) {
				if (is_array($pid)) $pid = (int)reset($pid); 
				else $pid = (int)$pid; 
			}
			$quantity = JRequest::getVar('quantity', 0); 
			if (!empty($quantity)) {
				if (is_array($quantity)) $quantity = (int)reset($quantity); 
				else $quantity = (int)$quantity; 
				
				
				
			}
			
			if ($p->virtuemart_product_id === $pid) {
				if (!empty($quantity)) {
					$group_quantity += $quantity; 
				}
			}
			
		}
		*/
		}
		if (empty($group_quantity)) {
			$group_quantity = 1;
		}
		
		
		
		if (empty(self::$debugmsg)) self::$debugmsg = array(); 
		if (empty(self::$code)) self::$code = ''; 
		$debug = $this->params->get('debug', false); 
		$highsg = $this->params->get('highsg', false); 
		//$calculator = calculationHelper::getInstance();
		
		
				
				
				
				$candidate = array(); 
				$candidateIndex = null;
				$candidatePrice = null; 
				if (!empty($highsg)) {
				
				//get largest SG of the product:
				$has_sg = 0; 
				foreach ($p->allPrices as $priceIndex => $price) {
					$sg = (int)$price['virtuemart_shoppergroup_id']; 
					$testP1 = (float)$price['product_price'];
					$testQ1 = (int)$price['price_quantity_start'];
					//we'll set current highest SG price only if it has a starting quantity
					if (!empty($sg)) {
						if (!empty($testP1) && ($testQ1 <= $group_quantity))
						if ($sg > $has_sg) {
						  $has_sg = $sg; 
						}
					}
				}
				
				//remove smaller SG IDs if we use large SG IDs 
				if ($has_sg) {
				 foreach ($p->allPrices as $priceIndex => $price) {
				   	
					if (empty($price['virtuemart_product_price_id'])) {
						unset($p->allPrices[$priceIndex]); 
						continue; 
					}
					$sg = (int)$price['virtuemart_shoppergroup_id']; 
					if ($sg !== $has_sg) {
						unset($p->allPrices[$priceIndex]); 
						if ($debug) {
						self::$debugmsg[] = 'Unsetting price '.$priceIndex.' price SG '.(int)$sg.' largest sg '.(int)$has_sg; 
						self::$debugmsg[] = $price; 
						
						}
						continue; 
					}
				 }
				}
				}
				
					
				
				if (count($p->allPrices) <= 1) {
					$xtest = reset($p->allPrices); 
					$xtest['price_quantity_start']  = (int)$xtest['price_quantity_start']; 
					$xtest['price_quantity_end']  = (int)$xtest['price_quantity_end']; 
					
					if (empty($xtest['virtuemart_product_price_id'])) {
						$p->allPrices = array(); 
					}
					else {
					if (($xtest['price_quantity_start'] <= 1) && ($xtest['price_quantity_end'] <= 1)) {
						//price is OK
						$prices = $p->allPrices; 
					}
					else {
						//reload price just in case:
						if (!$skipCalc) {
						$prices = self::getAllPrices($p, $group_quantity); 
						}
						
					}
					}
				 
				}
				
				
				
				foreach ($p->allPrices as $priceIndex => $price) {
					
					//if it was calculated for checkout: if (empty($price['subTotal'])) {
					//if it was calculated against taxes:
					
					
				
					if (false)
					if (empty($price['salesPrice'])) {
						
						
						
						//calculate taxes:
						

						$saved_index = $p->selectedPrice; 
						$p->selectedPrice = $priceIndex; 
						$qprices = self::$calculator->getProductPrices ($p, TRUE, $group_quantity);
						
						if (!empty($qprices)) {
						  $p->allPrices[$priceIndex] = $qprices; 
						  $price = $qprices; //includes tax
						}
						$p->selectedPrice = $saved_index; 
						
						
						
						if ($debug) {
						self::$debugmsg[] = 'Updating price '.$priceIndex.' as it is missing salesPrice '; 
						self::$debugmsg[] = $price; 
						
						}
						
						
					}
					
					
					
					
					
					$price_test = (float)$price['product_price']; 
					$override = (float)$price['override']; 
					if (!empty($override)) {
						$price_test = $override; 
					}
					
					
						
					
					if (empty($price_test)) continue; 
					
					$price_quantity_start = (int)$price['price_quantity_start']; 
					if (empty($price_quantity_start)) 
					{
						$price_quantity_start = 1; 
					}
					if (empty($group_quantity)) {
						$group_quantity = 1; 
					}
					$price_quantity_end = (int)$price['price_quantity_end']; 
					if (empty($price_quantity_end)) 
					{
						$price_quantity_end = PHP_INT_MAX; 
					}
					
					 
					
				    if (($group_quantity >= $price_quantity_start) && ($group_quantity <= $price_quantity_end)) {
					
						if ((is_null($candidatePrice)) || ($candidatePrice > $price_test)) {
							$candidatePrice = $price_test; 
							$candidate = $price; 
							$candidateIndex = $priceIndex; 
						}
					}
					if ($debug) 
					if (is_null($candidateIndex)) {
					
					self::$code = ' console.log('.json_encode('not found').','.str_replace(array("\r\r\n", "\r\n", "\n"), array('', '', ''),json_encode(array('price'=>$price,'group_quantity'=>$group_quantity,'price_quantity_start'=>$price_quantity_start, 'price_quantity_end'=>$price_quantity_end, 'candidatePrice'=>$candidatePrice, 'price_test'=>$price_test))).'); '."\n"; 
					}
				
				
					
					
					
				}
				
				
				
				if (!is_null($candidateIndex)) {
					$p->selectedPrice = $candidateIndex; 
					if (empty($skipCalc)) {
					 $retPrice = self::calculateSalesPrice($p, $group_quantity); 
					 $p->prices = $retPrice;
					}
					else {
					 $p->prices = $candidate; 
					}
					if ($debug)  {
					self::$code = ' console.log('.json_encode($p->selectedPrice).', '.str_replace(array("\r\r\n", "\r\n", "\n"), array('', '', ''),json_encode($p->prices)).'); '; 
					}
				}
				
				
				
				if (!empty($p->allPrices))
				if (!isset($p->allPrices[$p->selectedPrice])) {
					reset($p->allPrices);
					$first_key = key($p->allPrices);
					$p->selectedPrice = $first_key; 
					$p->prices = $p->allPrices[$p->selectedPrice];
				}
				
				
				
				if ($debug) {
				 $document = JFactory::getDocument();
				

				
				 if (!empty(self::$debugmsg)) {
					$json = str_replace(array("\r\r\n", "\r\n", "\n"), array('', '', ''),json_encode(self::$debugmsg));  
					self::$code .= ' var debugmsgs = '.$json.'; console.log(debugmsgs); '."\n";
				 }
				 
				 if (!empty(self::$code)) {
				  $document->addScriptDeclaration(self::$code); 
				 }
				 
				}
				
			
				
		
		/*
		if ($gp) {
			//we run this from product template, recalculate prices:
			if (!isset($p->prices['subTotal'])) {
				$runCheckoutPrices = true; 
			}
			if ($runCheckoutPrices) {
			static $inrecursion; 
			if (empty($inrecursion) || ($inrecursion !== 1)) {
				$inrecursion = 1; 
			    $calculator->getCheckoutPrices($cart); 
			}
			$inrecursion = 2;
			}
			
		}
		*/
		
		 
		
				
				$inrecursion = false; 
			
	}
	
	/*
	public function onAfterDispatch() {
		$option = JRequest::getVar('option', ''); 
		$view = JRequest::getVar('view', ''); 
		$task = JRequest::getVar('task', ''); 
		if (($option === 'com_virtuemart') && ($view === 'shoppergroup') && ($task === 'edit')) {
		$document = JFactory::getDocument(); 
		$type = $document->getType(); 
		if ($type === 'html') {
			$x = $document->getBuffer('component'); 
			$x .= '<test></test>'; 
			$document->setBuffer($x, 'component'); 
		}
		}
		
	}
	*/
	
	
}