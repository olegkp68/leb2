<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* OPC ADS plugin is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 


// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemStockshare extends JPlugin
{
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function plgUpdateProductObject(&$product) {
		if (!JFactory::getApplication()->isSite()) return; 
		
		$this->updateProduct($product); 
		$names = $this->plgCheckStockInCart(null,$product);
		
	}
	
	public function plgUpdateCategoryProducts(&$products) {
		if (!JFactory::getApplication()->isSite()) return; 
		
		foreach ($products as $ind=>$producX) {
		  
		  if ((!empty($producX)) && (is_array($producY))) {
			  foreach ($producX as $indP => $product) {
				    
					$this->updateProduct($products[$ind][$indP]); 
					$names = $this->plgCheckStockInCart(null,$products[$ind][$indP]);
			  }
		  }
		  elseif ((!empty($producX)) && (is_object($producX)))  {
				    $product = $producX; 
					$this->updateProduct($products[$ind]); 
					$names = $this->plgCheckStockInCart(null,$products[$ind]);
			  
		  }
		}
		
	}
	
	
	public function plgUpdateCustomPrice(&$product) {
		if (!JFactory::getApplication()->isSite()) return; 
		
		$this->updateProduct($product); 
		$names = $this->plgCheckStockInCart(null,$product);
		
	}
	
	private static function _debug($msg, $data) {
		 if (class_exists('OPCloader')) {
			OPCloader::opcDebug($data, $msg); 
		   }
	}
	
	
	function isCartUpdate($cart, $product, $quantity=0) {
		$p->virtuemart_product_id  = (int)$p->virtuemart_product_id; 
		$product->virtuemart_product_id  = (int)$product->virtuemart_product_id; 
		foreach ($cart->products as $p) {
			
			$p->quantity = (float)$p->quantity; 
			if (isset($product->quantity)) {
			 $quantityP = (float)$product->quantity; 
			}
			else {
				$quantityP = 0; 
			}
			
			if ($p->virtuemart_product_id === $product->virtuemart_product_id) {
				if ($p->quantity === $quantityP) {
					return false; 
				}
				else {
					return true; 
				}
			}
		}
		return false; 
	}
	
	
	public function plgCheckCartQuantities(&$cart) {
		
		$null = 0; $errorMsg = ''; $error_info = array();
		$state = $this->_getCartState($cart); 
		$names = $this->plgCheckStockInCart($cart, $null,  $error_info);
		$done = array(); 
		$msg = ''; 
		self::loadLang(); 
		if (!empty($names)) {
			
			foreach ($error_info['errors_avai'] as $mpn => $num) {
				if ($num <= 0) {
					

					//$state['names'][$mpn]
					
					if (is_array($state['names'][$mpn])) {
					 $msg = JText::_('PLG_SYSTEM_STOCKSHARE_OUT_OF_STOCK_ALL').'<br />'.implode('<br />', $state['names'][$mpn]); 
					}
					else {
						if (is_string($state['names'][$mpn])) {
						 $msg = JText::_('PLG_SYSTEM_STOCKSHARE_OUT_OF_STOCK_ALL').'<br />'.$state['names'][$mpn]; 
						}
					}
					$done[$mpn] = 1; 
				}
				
				
			 //PLG_SYSTEM_STOCKSHARE_OUT_OF_STOCK
			 
			 //PLG_SYSTEM_STOCKSHARE_SOLDOUT_ONE
			 //PLG_SYSTEM_STOCKSHARE_SOLDOUT_MORE
			}
		  
			
		}
		foreach ($error_info['errors'] as $mpn=>$names) {
			if (!empty($done[$mpn])) continue; 
			
			
			$msg = JText::_('PLG_SYSTEM_STOCKSHARE_NOT_ENOUGH_QUANTITY').$error_info['errors'][$mpn]; 
			
			
		}
		if (!empty($msg)) 
		JFactory::getApplication()->enqueueMessage($msg); 
		
	}
	
	//return false to block checkout
	function plgVmOnCheckoutCheckStock( &$cart, &$product, &$quantity, &$errorMsg, &$adjustQ, $inCheckout = false )
    {
	
		//trigger only on cart change and checkout check:
		if (empty($inCheckout)) {
			return; 
		}

		if (!JFactory::getApplication()->isSite()) return; 
		
		$this->updateProduct($product); 
		
		
		if (empty($cart->cartProductsData)) return; 
		if (empty($product->product_mpn)) return; 
		
		
		
		$state = $this->_getCartState($cart, $product);
		
		
		
		$mpn = $product->product_mpn; 
		if (isset($state['toTest'][$mpn])) {
			$in_cart_now_except = (float)$state['toTest'][$mpn]; 
		}
		else  {
			$in_cart_now_except = 0; 
		}
		
		
		
		$stock = $this->getStock($mpn, $product->product_sku); 
		$avai = $stock['product_in_stock'] - $stock['product_ordered']; 
		
		$requested_quantity = $quantity; 
		//requested quantity minus available quantity minus sum of mpns in the cart is smaller zero return false and adjust quantity
		
		if (($avai - $requested_quantity - $in_cart_now_except) < 0) {
			$error = array($product->product_name); 
			$this->showCartError($error, $errorMsg, false); 
			/*
			var_dump($stock); 
			echo ' avai:'; var_dump($avai); 
			echo 'requested:'; var_dump($requested_quantity); 
			echo 'in cart now except: :'; var_dump($in_cart_now_except); 
		var_dump($state); 
		die(); 
			*/
			
			return false; 
		}
		
		return;
		$retStock = $this->plgCheckStockInCart($cart); 
		
		if (!empty($inCheckout)) {
			die('incheckout'); 
		  return $this->plgCheckStockInCart($cart, 0, $errorMsg); 
		}
		
		
		
		$avai = (int)$product->product_in_stock - (int)$product->product_ordered; 
		
		self::_debug($avai, 'avai'); 
		self::_debug($quantity, 'quantity'); 
		
		
		if (($quantity > $avai) || ($avai === 0)) {
			
			$quantity = $avai; 
			if (empty($avai)) {
			  return false; 
			}
			else {
				return true; 
			}
		}
		
		
		//if ($product->product_in_stock <= 0) return false; 
		
		
	}
	
	
	private function _getCartState(&$cart, $exceptProduct=null) {
		$toTest = array(); 
		$skus = array(); 
		$names = array(); 
		
		$mpn = ''; 
		
		if (!empty($exceptProduct))
		$exceptProduct->virtuemart_product_id = (int)$exceptProduct->virtuemart_product_id; 
		$productModel = VmModel::getModel('product');
		foreach ($cart->cartProductsData as $k=>$p) {
			 
			 
			 
			 
			 $virtuemart_product_id = (int)$p['virtuemart_product_id']; 
			 
			 
			 if (!empty($exceptProduct))
			 if ((!empty($exceptProduct)) && ($exceptProduct->virtuemart_product_id === $virtuemart_product_id)) {
				 continue; 
			 }
			 
			 $quantity = $p['quantity']; 
			 
			 if ((isset($cart->products[$k])) && (isset($cart->products[$k]->product_mpn))) {
				 $mpn = $cart->products[$k]->product_mpn;
				 $sku = $cart->products[$k]->product_sku; 
				 $names[$sku] = $cart->products[$k]->product_name; 
				 $names[$mpn] = $cart->products[$k]->product_name; 
			 }
			 else {
				
				
				 $productTemp = $productModel->getProduct($virtuemart_product_id, true, false,true,$quantity);
				 if (!empty($productTemp)) {
				   $mpn = $productTemp->product_mpn; 
				   $sku = $productTemp->product_sku; 
				   $names[$sku] = $productTemp->product_name; 
				   $names[$mpn] = $productTemp->product_name; 
				 }
				 else {
					 
				 }
			 }
			 if (!empty($mpn)) {
				 if (!isset($toTest[$mpn])) $toTest[$mpn] = 0; 
				 $toTest[$mpn] += $quantity;
			 }
			 else {
				 if (!isset($skus[$sku])) $skus[$sku] = 0; 
				 $skus[$sku] += $quantity; 
			 }
			 
		}
		
		return array('skus'=>$skus, 'names'=>$names, 'toTest'=>$toTest, 'mpn'=>$mpn); 
	}
	//if not empty product_id, it will check if we can buy it with the current content of the cart
	//if empty product_id, it will check if we can buy products in the cart itself
	//product_id CAN BE A CURRENT PRODUCT OBJECT AS WELL
	function plgCheckStockInCart($cart=null, &$product_id=0, &$error_info=array()) {
		
		if (empty($cart)) $cart = VirtuemartCart::getCart(); 
		$data = $this->_getCartState($cart); 
		
		$skus = $data['skus']; 
		$names = $data['names']; 
		$toTest = $data['toTest']; 
		$mpn = $data['mpn']; 
		
		$errors = array(); 
		$errors_avai = array(); 
		$errors_remaining = array(); 
		
		$productModel = VmModel::getModel('product');
		
		
		
		
		//tests selected product agains the cart, if we can display add-to-cart
		//section to test new product if it can be added to the cart: 
		if (!empty($product_id)) {
			
			
			
		$test_mpn = ''; 
		if ((!empty($product_id)) && (is_numeric($product_id))) {
		 $testProduct = $productModel->getProduct($product_id, true, false,true,1);
		 $test_mpn = $testProduct->product_mpn; 
		 $sku = $testProduct->product_sku; 
		}
		else 
		if ((!empty($product_id)) && (is_object($product_id))) {
		   $test_mpn = $product_id->product_mpn; 	
		   $sku = $product_id->product_sku; 
		}
		if (!empty($test_mpn)) {
			$stock = $this->getStock($test_mpn, $sku); 
		
			if (isset($toTest[$test_mpn])) {
				$in_cart = (int)$toTest[$test_mpn]; 
				$avai = $stock['product_in_stock'] - $stock['product_ordered']; 
				if (($avai - $in_cart) <= 0) {
						if (is_object($product_id)) {
							
							//changes value of current stock level on the product within the view:
							
							$product_id->product_in_stock = 0; 
							if ($product_id->product_ordered < 0) {
							   $product_id->product_ordered = 0; 
							}
						}
				}
				else {
					$remaining = $avai - $in_cart;
					if (is_object($product_id)) {
							
							//changes value of current stock level on the product within the view:
							
							$product_id->product_in_stock = $remaining; 
							
						}
				}
			}
		}
		}
		
		
		//section to test whole cart if it can be processed via checkout: 
		foreach ($toTest as $mpn => $q) {
			$in_cart = (int)$toTest[$mpn]; 
			$stock = $this->getStock($mpn, ''); 
			$avai = $stock['product_in_stock'] - $stock['product_ordered']; 
			$errors_remaining[$mpn] = $avai - $in_cart; 
			if (($avai - $in_cart) < 0) {
				//current content of the cart is invalid
				$errors[$mpn] = $names[$mpn]; 
				
			}
			$errors_avai[$mpn] = $avai; 
		}
		
		foreach ($skus as $sku => $q) {
			$in_cart = (int)$skus[$sku]; 
			$stock = $this->getStock('', $sku); 
			$avai = $stock['product_in_stock'] - $stock['product_ordered']; 
			$errors_remaining[$mpn] = $avai - $in_cart; 
			if (($avai - $in_cart) < 0) {
				//current content of the cart is invalid
				$errors[$sku] = $names[$sku]; 
				
			}
			$errors_avai[$sku] = $avai; 
		}
		
		$error_info = array(); 
		$error_info['errors'] = $errors; 
		$error_info['errors_avai'] = $errors_avai; 
		$error_info['errors_remaining'] = $errors_remaining;
		return $errors; 
		/*
		if (empty($product_id)) {
		if (!empty($errors)) {
			$this->showCartError($errors, $errorMsg); 
			return false; 
		}
		}
		*/
	}
	
	
	public static function loadLang() {
         JFactory::getLanguage()->load('plg_system_stockshare', dirname(__FILE__).DIRECTORY_SEPARATOR); 
		JFactory::getLanguage()->load('plg_system_stockshare', JPATH_ADMINISTRATOR); 	
	
	if (!class_exists('VmConfig'))
	{
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		VmConfig::loadConfig(); 
	}

	   
	   if (!class_exists('VirtueMartCart')) require(JPATH_SITE. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php');
	   
	   if (class_exists('vmLanguage')) {
		   if (method_exists('vmLanguage', 'loadJLang')) {
				vmLanguage::loadJLang('com_virtuemart', true);
		   }
	   }
	   
	}
	
	static $_msgSent; 
	
	private function showCartError($errors, &$errorMsg, $enqueue=true) {
		self::loadLang(); 
		if (count($errors) === 1) {
		  
			$msg = JText::_('PLG_SYSTEM_STOCKSHARE_SOLDOUT_ONE').'<br />'.implode('<br />', $errors);
		}
		elseif (count($errors)>1) {
			$msg = JText::_('PLG_SYSTEM_STOCKSHARE_SOLDOUT_MORE').'<br />'.implode('<br />', $errors); 
			
		}
		
		$errorMsg = $msg; 
		
		if (empty(self::$_msgSent)) self::$_msgSent = array(); 
		
		if ($enqueue) {
		if (empty(self::$_msgSent[$msg])) {
		 JFactory::getApplication()->enqueueMessage($msg); 
		 
		 }
		 self::$_msgSent[$msg] = true; 
		}
	}
	
	private static function _getOPCMini() {
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
			echo 'OPC not found, install RuposTel One Page Checkout'; 
			JFactory::getApplication()->close(); 
		}
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		
	}
	private function cmdinstallmpnindex() {
		  self::_getOPCMini(); 
		  $z = OPCmini::hasIndex('virtuemart_products', array('product_mpn')); 
		  if (empty($z)) {
		     OPCmini::addIndex('virtuemart_products', array('product_mpn')); 
			 echo 'Index added to #__virtuemart_products'; 
		  }
		  
		JFactory::getApplication()->close(); 
	}
	private function cmdinstallmpnindexstatus() {
		self::_getOPCMini(); 
		
		$z = OPCmini::hasIndex('virtuemart_products', array('product_mpn')); 
		 if (empty($z)) {
			 echo 'Index product_mpn does not exists in #__virtuemart_products'; 
		  }
		  else {
			  echo 'Index product_mpn exists in #__virtuemart_products'; 
		  }
		JFactory::getApplication()->close(); 
	}
	private function cmdinstallskuindex() {
		self::_getOPCMini(); 
		 $z = OPCmini::hasIndex('virtuemart_products', array('product_sku')); 
		  if (empty($z)) {
		     OPCmini::addIndex('virtuemart_products', array('product_sku')); 
			 echo 'Index added to #__virtuemart_products'; 
		  }
		JFactory::getApplication()->close(); 
	}
	function cmdinstallskuindexstatus() {
		self::_getOPCMini(); 
		$z = OPCmini::hasIndex('virtuemart_products', array('product_sku')); 
		 if (empty($z)) {
			 echo 'Index product_sku does not exists in #__virtuemart_products'; 
		  }
		  else {
			  echo 'Index product_sku exists in #__virtuemart_products'; 
		  }
		JFactory::getApplication()->close(); 
	}
	
	
	function cmdcreatetrigstatus() {
		$db = JFactory::getDBO(); 
		$q = "SHOW TRIGGERS "; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$found = false; 
		if (!empty($res)) {
			foreach ($res as $k=>$v) {
				if ($v['Trigger'] === 'SHAREDSTOCK') {
					$found = true; 
					/*
					$check = $this->getTSQL(); 
					$current = $v['Statement']; 
					
					if ($check === $current) {
						
					}
					*/
					
				}
			}
		}
		
		if ($found) { echo '<div style="color:green;">Triggerer SHAREDSTOCK Installed</div>'; }
		else {
			echo '<div style="color:red;">Triggerer SHAREDSTOCK NOT Installed</div>'; 
		}
		$prefix = $db->getPrefix();
	   $q = "SHOW TABLES LIKE '".$prefix."virtuemart_sharedstock'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	if (!empty($r)) {
		echo '<div style="color:green;">Table #__virtuemart_sharedstock OK</div>'; 
	}
	else {
		echo '<div style="color:red;">Table #__virtuemart_sharedstock does not exist</div>'; 
	}
		
		
	}
	
	function cmddroptrigstatus() {
		$db = JFactory::getDBO(); 
		$q = "SHOW TRIGGERS "; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$found = false; 
		if (!empty($res)) {
			foreach ($res as $k=>$v) {
				if ($v['Trigger'] === 'SHAREDSTOCK') {
					$found = true; 
					/*
					$check = $this->getTSQL(); 
					$current = $v['Statement']; 
					
					if ($check === $current) {
						
					}
					*/
					
				}
			}
		}
		
		if ($found) { echo '<div style="color:green;">Triggerer SHAREDSTOCK Installed</div>'; }
		else {
			echo '<div style="color:red;">Triggerer SHAREDSTOCK NOT Installed</div>'; 
		}
		$prefix = $db->getPrefix();
	   $q = "SHOW TABLES LIKE '".$prefix."virtuemart_sharedstock'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	if (!empty($r)) {
		echo '<div style="color:green;">Table #__virtuemart_sharedstock OK</div>'; 
	}
	else {
		echo '<div style="color:red;">Table #__virtuemart_sharedstock does not exist</div>'; 
	}
		
		
	}
	
	function cmdcreatetrig() {
		$this->installSQL(); 
	}
	
	function cmddroptrig() {
		$db = JFactory::getDBO(); 
		$q = 'DROP TRIGGER IF EXISTS `SHAREDSTOCK`'; 
	$db->setQuery($q); 
	$db->execute(); 
	echo 'OK, Triggerer is now uninstalled'; 
	}
	
	private function getTSQL() {
		
		$config = JFactory::getConfig(); 
	$username = $config->get('user'); 
	$host = $config->get('host'); 
	$dbprefix = $config->get('dbprefix'); 
	$q = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'trig.sql'); 
	$q = str_replace('{{username}}', $username, $q); 
	$q = str_replace('{{host}}', $host, $q); 
	$q = str_replace('{{prefix}}', $dbprefix, $q); 
	
	return $q; 
	}
	
	private function installSQL() {
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_sharedstock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_id` int(11) NOT NULL,
  `virtuemart_product_id` int(1) NOT NULL,
  `mpn` varchar(160) NOT NULL,
  `product_in_stock` int(11) DEFAULT NULL,
  `product_ordered` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_product_id_2` (`virtuemart_product_id`,`mpn`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`),
  KEY `ref_id` (`ref_id`),
  KEY `mpm` (`mpn`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;'; 
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$db->execute(); 
	
	
	
	$q = 'DROP TRIGGER IF EXISTS `SHAREDSTOCK`'; 
	$db->setQuery($q); 
	$db->execute(); 
	
	$q = $this->getTSQL(); 
	
	
	
	
	try { 
	 $db->setQuery($q); 
	 $db->execute(); 
	}
	catch(Exception $e) {
		echo 'Error creating triggerer '.(string)$e.':<br />'; 
		echo '<textarea style="width:100%;" rows="10">'.$q.'</textarea>'; 
		
	}
	
	echo 'OK, Triggerer is now installed'; 
	
	
	
	

		
	}
	
	function onAjaxStockshare() {
		
		if (!$this->_checkPerm()) {
			echo 'This feature is only available to Super Administrators'; 
			JFactory::getApplication()->close(); 
		}
		
		
		
		ob_start(); 
		$post = JRequest::get('post'); 
		$cmd = JRequest::getWord('cmd'); 
		
		$checkstatus = JRequest::getVar('checkstatus', null); 
		if (!empty($checkstatus)) $cmd .= 'status'; 
		
		if (method_exists($this, 'cmd'.$cmd)) {
			$funct = 'cmd'.$cmd; 
			//$this->$cmd($post); 
		    call_user_func(array($this, $funct), $post); 
		}
		else {
			$this->_die('Command not found'); 
		}
		$html = ob_get_clean(); 
		
		@header('Content-Type: text/html; charset=utf-8');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		echo $html; 
		JFactory::getApplication()->close(); 
		
		
		
	}
	
	private function cmduploadxls() {
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$fileName = $_FILES['file']['name'];
		$fileTemp = $_FILES['file']['tmp_name'];
		$this->_getPHPExcel(); 
		
				
		// Create new PHPExcel object
		
		$reader = PHPExcel_IOFactory::createReaderForFile($fileTemp); 
		$reader->setReadDataOnly(true);
		$objXLS = $reader->load($fileTemp);
		$value = $objXLS->getSheet(0)->getCell('A1')->getValue();
		$sheetData = $objXLS->getSheet(0)->toArray(null, true, true, true);
		$lines = ''; 
		foreach ($sheetData as $ind=>$row) {
			//echo var_export($row, true).'<br />'; 
			if ((is_null($row['A'])) && (is_null($row['B']))) break; 
			
			if (is_null($row['A'])) $row['A'] = ''; 
			if (is_null($row['B'])) $row['B'] = ''; 
			if (is_null($row['C'])) $row['C'] = ''; 
			$lines .= $row['A'].','.$row['B'].','.$row['C']."\n"; 
			//echo $lines; 
			
		}
		$objXLS->disconnectWorksheets();
		JFile::delete($fileTemp); 
		unset($reader);
		unset($objXLS);
		
		$this->processData($lines, true); 
		echo 'finished...'; 
		JFactory::getApplication()->close(); 
	}
	
	private function getStock($mpn, $sku='', $usecache=true) {
		
		$db = JFactory::getDBO(); 
		static $cache; 
		
		
		if (!empty($cache[$mpn.'~_~'.$sku])) return $cache[$mpn.'~_~'.$sku];
		
		
		if (!empty($mpn)) {
		
		$q = "select * from #__virtuemart_sharedstock where mpn = '".$db->escape($mpn)."' and ref_id = 0 and virtuemart_product_id = 0 limit 1"; 
	    $db->setQuery($q); 
	    $mainmpn = $db->loadAssoc(); 
		if (!empty($mainmpn)) {
			$ret = array('product_in_stock'=>(int)$mainmpn['product_in_stock'], 'product_ordered'=>(int)$mainmpn['product_ordered'], 'found'=>true); 
			$cache[$mpn.'~_~'.$sku] = (array)$ret;
			return (array)$ret;
		}
		}
		if (!empty($sku)) {
			$q = "select product_in_stock, product_ordered from #__virtuemart_products where product_sku = '".$db->escape($sku)."' limit 1"; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			if (!empty($res)) {
				$ret = array('product_in_stock'=>(int)$res['product_in_stock'], 'product_ordered'=>(int)$res['product_ordered'], 'found'=>true); 
				$cache[$mpn.'~_~'.$sku] = (array)$ret;
				return (array)$ret; 
			}
		}
		//product not found !
		$e = array('product_in_stock'=>0, 'product_ordered'=>0, 'found'=>false); 
		$cache[$mpn.'~_~'.$sku] = $e;
		return $e;
	}
	
	private function cmdloadstock($post) {
		return $this->cmdloadmpns($post, true);
	}
	
	private function processData($data, $incstock=false) {
		if (strpos($data, "\r\n")!== false) {
			$del = "\r\n";
		}
		else {
			$del = "\n"; 
		}
		$DataCsv = str_getcsv($data, $del); //parse the rows 
	    foreach($DataCsv as $k=>$Row) $DataCsv[$k] = str_getcsv($Row, ","); //parse the items in rows 
		$last_mpn = ''; 
		$in_stock = 0; 
		
		$db = JFactory::getDBO(); 
		
		$q = 'START TRANSACTION'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		 $q = 'SET @STOCK_TRIG_DISABLED = true'; 
		 $db->setQuery($q); 
	     $db->execute(); 
		
		for ($i=1; $i<count($DataCsv); $i++) {
			$row = $DataCsv[$i]; 
			
			if (!empty($row[0])) {
				$last_mpn = trim($row[0]); 
				
				
				
				$in_stock = 0; 
			}
			else {
				$row[0] = $last_mpn; 
				
			}
			
			if (!empty($row[2])) {
				$in_stock = $row[2]; 
			}
			else {
				$row[2] = $in_stock;  
			}
			
			$mpn = trim($row[0]); 
			$sku = trim($row[1]); 
			
			$remove = array("\r", "\n"); 
			$removeto = array("", ""); 
			$mpn = str_replace($remove, $removeto, $mpn);
			$sku = str_replace($remove, $removeto, $sku);
			
			if (substr($sku, 0, 1) === "'") $sku = substr($sku, 1); 
			if (substr($mpn, 0, 1) === "'") $mpn = substr($mpn, 1); 
			
			echo 'MPN: '.$mpn.' SKU:'.$sku; 
			if ($incstock) {
			echo ' In Stock:'.$in_stock; 
			}
			echo "<br />\n"; 
				
			if (empty($sku) || (empty($mpn))) continue; 
			
			$mpn = trim($mpn); 
			
			$q = "update #__virtuemart_products set product_mpn = '".$db->escape($mpn)."'"; 
			
			if ($incstock) {
				$q .= ', product_in_stock = '.(int)$in_stock; 
			}
			$q .= " where product_sku = '".$db->escape($sku)."'"; 
			$db->setQuery($q); 
			$db->execute(); 
			
			
			
			
			if ($incstock) {
				$q = "update #__virtuemart_sharedstock set ";
				$q .= ' product_in_stock = '.(int)$in_stock; 
				$q .= " where `mpn` = '".$db->escape($mpn)."'"; 
				$db->setQuery($q); 
				$db->execute(); 	
			}
			
			
		}
		
		 $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
		$q = 'START TRANSACTION'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		for ($i=1; $i<count($DataCsv); $i++) 
		{
		
		   $row = $DataCsv[$i]; 
			
			if (!empty($row[0])) {
				$last_mpn = $row[0]; 
				$in_stock = 0; 
			}
			else {
				$row[0] = trim($last_mpn); 
				
			}
			
			if (!empty($row[2])) {
				$in_stock = $row[2]; 
			}
			else {
				$row[2] = $in_stock;  
			}
			
			$mpn = trim($row[0]); 
			$sku = trim($row[1]); 
			
			$remove = array("\r", "\n"); 
			$removeto = array("", ""); 
			$mpn = str_replace($remove, $removeto, $mpn);
			$sku = str_replace($remove, $removeto, $sku);
			
			if (empty($sku) || (empty($mpn))) continue; 
			   
			   $q = "select * from #__virtuemart_sharedstock where mpn = '".$db->escape($mpn)."' and ref_id = 0 and virtuemart_product_id = 0 limit 1"; 
			   $db->setQuery($q); 
			   $mainmpn = $db->loadAssoc(); 
			   
			   if ($incstock) {
			   $store_stock = $in_stock; 
			   $store_ordered = 0; 
			   }
			   else {
				   $q = "select product_in_stock from #__virtuemart_products where product_mpn = '".$db->escape($mpn)."' order by product_in_stock asc limit 1"; 
				   $db->setQuery($q); 
				   $min = $db->loadResult(); 
				   $store_stock = (int)$min; 
				   
				   $q = "select product_ordered from #__virtuemart_products where product_mpn = '".$db->escape($mpn)."' order by product_ordered desc limit 1"; 
				   $db->setQuery($q); 
				   $max = $db->loadResult(); 
				   $store_ordered = (int)$max; 
			   }
			   
			
			   
			   if (empty($mainmpn)) {
				   $q = "insert into #__virtuemart_sharedstock (id, ref_id, virtuemart_product_id, mpn, product_in_stock, product_ordered) values ('NULL', 0, 0, '".$db->escape($mpn)."', ".(int)$store_stock.", 0)"; 
				   $db->setQuery($q); 
				   $db->execute(); 
				   $ref_id = (int)$db->insertid();
				   
				   echo 'Inserting new main MPN stock info<br />'; 
			   }
			   else {
				   $ref_id = (int)$mainmpn['id']; 
				   echo 'Found master MPN: '.$mpn.'<br />'; 
			   }
			   
			   
			   if (!empty($sku)) {
			   $q = "select virtuemart_product_id, product_ordered from #__virtuemart_products where product_sku = '".$db->escape($sku)."'"; 
			   $db->setQuery($q); 
			   $ressku = $db->loadAssocList(); 
			   
			   if (!empty($ressku)) {
				   echo 'Updating stock for '.$sku.'<br />'; 
				   foreach ($ressku as $rrow) {
					   $virtuemart_product_id = (int)$rrow['virtuemart_product_id']; 
					   $store_ordered = $rrow['product_ordered']; 
					   $q = "select * from #__virtuemart_sharedstock where virtuemart_product_id = ".(int)$virtuemart_product_id." limit 1"; 
					   $db->setQuery($q); 
					   $resline = $db->loadAssoc(); 
					   if (empty($resline)) {
						   $q = "insert into #__virtuemart_sharedstock  (id, ref_id, virtuemart_product_id, mpn, product_in_stock, product_ordered) values ('NULL', ".(int)$ref_id.", ".(int)$virtuemart_product_id.", '".$db->escape($mpn)."', ".(int)$store_stock.", ".(int)$store_ordered.")"; 
						   $db->setQuery($q); 
						   $db->execute(); 
					   }
					   else {
						   $id = (int)$resline['id']; 
						   $resline['ref_id'] = (int)$resline['ref_id']; 
						   if (($resline['mpn'] !== $mpn) || ($ref_id !== $resline['ref_id'])) {
							   $q = "update #__virtuemart_sharedstock set mpn = '".$db->escape($mpn)."', ref_id = ".(int)$ref_id.' where `id` = '.(int)$id; 
							   $db->setQuery($q); 
							   $db->execute(); 
						   }
					   }
					   
				   }
			   }
			   }
			   
			   
			   }
			   
			    $q = 'SET @STOCK_TRIG_DISABLED = NULL'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
		
		
	}
	
	private function cmdloadmpns($post, $incstock=false) {
		
		$url = $post['jform']['params']['google_csv_url']; 
		
		$data = $this->_curlget($url);
		$this->processData($data); 
		
		
		echo ' Finished '."<br />\n"; 
	}
	
	private function _getPHPExcel() {
		@ini_set("memory_limit","512M");
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) $this->_die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel<br />Install via RuposTel One Page Checkout -> OPC Order Manager -> Excell Export -> Download and Install');
		
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');
	}
	
	private function cmddownloadstock() {
		
		$this->_getPHPExcel(); 
		
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("RuposTel Systems")
							 ->setLastModifiedBy("RuposTel Systems")
							 ->setTitle("OPC Stock Management")
							 ->setSubject("OPC Stock Management")
							 ->setDescription("Stock Management for VirtueMart")
							 ->setKeywords("orders, virtuemart, eshop")
							 ->setCategory("Stock");
		
		
		$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(0, 1, 'Code');
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(1, 1, 'Art.nr.');
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(2, 1, 'InStock');
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(3, 1, 'Reserved (NOT IMPORTED)');
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(4, 1, 'Available (NOT IMPORTED)');
			 
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
		
		$db = JFactory::getDBO(); 
		$q = "select product_sku, product_mpn, product_in_stock from #__virtuemart_products where product_mpn <> '' and product_sku <> '' order by product_mpn"; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		$last_mpn = ''; 
		if (!empty($res))
		foreach ($res as $ind=>$row) {
			
			
			
			$product_mpn = trim($row['product_mpn']); 
			$product_sku = $row['product_sku']; 
			$product_sku_print = $product_sku; 
			//$product_sku_print = "'".$product_sku; 
			if ($last_mpn === $product_mpn) {
				$product_mpn_print = ''; 
				$product_in_stock = ''; 
			}
			else {
				//$product_mpn_print = "'".$product_mpn; 
				$product_mpn_print = $product_mpn; 
				$product_in_stock = $this->_getLowestStock($product_mpn, $product_sku); 
				$product_in_stock_last = $product_in_stock;
			}
			
			$stock = $this->getStock($product_mpn, ''); 
			if (!empty($stock['found'])) {
			  $product_ordered = (int)$stock['product_ordered']; 
			}
			else {
				$product_in_stock = $this->_getLowestStock('', $product_sku); 
				$product_ordered = (int)$stock['product_ordered']; 
				$product_in_stock_last = $product_in_stock;
			}
			
			
			
			
			
			//$product_in_stock = $row['product_in_stock']; 
			$rown_n = $ind+2; 
			$rown_stored = $rown_n; 
			
			$type = PHPExcel_Cell_DataType::TYPE_STRING;
			
			$objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(0, $rown_n)->setValueExplicit($product_mpn_print, $type);
             //->setCellValueByColumnAndRow(0, $rown_n, $product_mpn_print);
			
			
			
			
			$rown_n = $rown_stored; 
			
			//$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $rown_n, $product_sku_print);
			$objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(1, $rown_n)->setValueExplicit($product_sku_print, $type);
			 
			 
			 $rown_n = $rown_stored; 
			 
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(2, $rown_n, $product_in_stock);
			 
			 $rown_n = $rown_stored; 
			 
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(3, $rown_n, $product_ordered);
			 
			 $rown_n = $rown_stored; 
			 
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(4, $rown_n, '='.$product_in_stock_last.'-D'.$rown_n);
			 
			 $rown_n = $rown_stored; 
			
			
			$last_mpn = $product_mpn; 
			
		}
		
		unset($row); 
		$last_ind = $ind+1; 
		
		$db = JFactory::getDBO(); 
		$q = "select product_sku, product_mpn, product_in_stock from #__virtuemart_products where ((product_mpn = '') or (product_mpn IS NULL)) and product_sku <> '' order by product_mpn"; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		$last_mpn = ''; 
		if (!empty($res))
		foreach ($res as $ind=>$row) {
			
			
			
			$product_mpn = trim($row['product_sku']); 
			$product_sku = $row['product_sku']; 
			
			if ($last_mpn === $product_mpn) {
				$product_mpn_print = ''; 
			}
			else {
				$product_mpn_print = $product_mpn; 
			}
			
			
			
			$product_in_stock = $this->_getLowestStock($product_mpn, $product_sku); 
			
			
			$stock = $this->getStock($product_mpn, ''); 
			if (!empty($stock['found'])) {
			  $product_ordered = (int)$stock['product_ordered']; 
			}
			else {
				$product_in_stock = $this->_getLowestStock('', $product_sku); 
				$product_ordered = (int)$stock['product_ordered']; 
			}
			
			//$product_in_stock = $row['product_in_stock']; 
			$rown_n = $last_ind+$ind+2; 
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(0, $rown_n, $product_mpn_print);
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(1, $rown_n, $product_sku);
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(2, $rown_n, $product_in_stock);
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(3, $rown_n, $product_ordered);
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(4, $rown_n, '=C'.$rown_n.'-D'.$rown_n);
			$last_mpn = $product_mpn; 
			
		}
		
		
		

	$objPHPExcel->getActiveSheet()->setTitle('Products');
	$objPHPExcel->setActiveSheetIndex(0);
	

//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//$tmp = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'temp'.uniqid().'.tmp'; 
$objWriter->save('php://output'); 
//$objWriter->save($tmp); 

unset($objWriter); 
$objWriter = null; 

@header('Content-Type: application/vnd.ms-excel');
@header('Content-Disposition: attachment;filename="products.xlsx"');
@header('Cache-Control: max-age=0');
	flush(); 
	JFactory::getApplication()->close(); 
	}
	
	
	private function _getLowestStock($mpn, $sku) {
		$stock = $this->getStock($mpn, ''); 
		$db = JFactory::getDBO(); 
		if (!empty($mpn)) {
		if (empty($stock['found'])) {
			$q = 'select MIN(product_in_stock) as min_stock from #__virtuemart_products where product_mpn = \''.$db->escape($mpn).'\' limit 1';
			$db->setQuery($q); 
			$min = $db->loadResult(); 
			if (!is_null($min)) {
			$min = (int)$min; 
			if (!empty($min)) {
			 return $min; 
			}
			}
		}
		else {
			return (int)$stock['product_in_stock']; 
		}
		}
		if (!empty($sku)) {
		$stock = $this->getStock('', $sku); 
		return (int)$stock['product_in_stock']; 
		
		}
		
		
	}
	
	private function _curlget($url) {
	
	if (!function_exists('curl_init')) return file_get_contents($url); 
	
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	$err = curl_error($ch); 
	if (!empty($err)) $this->_die($url.'=>'.$err); 
	curl_close($ch);
	return $data;
	}
	
	private function _die($msg) {
		echo $msg; 
		$html = ob_get_clean(); 
		echo $html;
		//@header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Error', true, 500);
		JFactory::getApplication()->close(); 
	}
	
	private function _checkPerm() {
	   $user = JFactory::getUser(); 
	   
      $isroot = $user->authorise('core.admin');	
	  
	  if (!$isroot) 
	  {
		
		return false; 
	  }
	  
	  $iss = JFactory::getApplication()->isSite(); 
	  if (!empty($iss)) return false; 
	  
	  return true; 
   }
	
	function onAfterInitialise() {
		
		if (JFactory::getApplication()->isAdmin()) {
			$db = JFactory::getDBO(); 
			$q = 'SET @JOOMLA_IS_ADMIN = true;'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		else {
			$db = JFactory::getDBO(); 
			$q = 'SET @JOOMLA_IS_ADMIN = false;'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
	}
	function plgVmBeforeProductSearch(&$select, &$join, &$where, &$group, &$order, &$joinLang) {
		
	}
	
	
	public function updateProduct(&$article) {
		 $virtuemart_product_id = (int)$article->virtuemart_product_id; 
			
			if (empty($article->product_mpn)) return; 
			
			$article->product_mpn = trim($article->product_mpn); 
			
			
			$db = JFactory::getDBO(); 
			$q = 'select ref.product_in_stock, ref.product_ordered from #__virtuemart_sharedstock as ref,#__virtuemart_sharedstock as p where (p.virtuemart_product_id = '.(int)$virtuemart_product_id.') and ((p.ref_id = ref.id) and (ref.ref_id = 0)) limit 1'; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			
			
			
			if (!empty($res)) {
			 $article->product_in_stock = (int)$article->product_in_stock; 
			 $article->product_ordered = (int)$article->product_ordered; 
			 
			 
			 
			 $product_in_stock = (int)$res['product_in_stock'];
			 $product_ordered = (int)$res['product_ordered']; 
			 if (($product_in_stock !== $article->product_in_stock) || ($product_ordered !== $article->product_ordered)) {
			   
			   
			  
			   
			   $q = 'START TRANSACTION'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   $q = 'SET @STOCK_TRIG_DISABLED = true'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   $q = 'update #__virtuemart_products set product_in_stock = '.(int)$product_in_stock.', product_ordered = '.(int)$product_ordered.' where virtuemart_product_id = '.(int)$virtuemart_product_id.' '; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   
			   
			   $q = 'SET @STOCK_TRIG_DISABLED = NULL'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			   
			   $article->product_in_stock = $product_in_stock; 
			   $article->product_ordered = $product_ordered;
			   
			   
			 }
			 
			 
			}
	}
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		
		if (!JFactory::getApplication()->isSite()) return; 
		//recursive protection: 
		if (!empty(self::$inloop)) return; 
		
		$class = get_class($article); 
		
		
		if (($class == 'TableProducts') || (($class == 'stdClass') && (isset($article->virtuemart_product_id)))) {
		   //self::toObject($article); 
		   if (isset($article->virtuemart_product_id)) {
			 $this->updateProduct($article); 
			   
		   
		   }
		}
	}
	
	public function plgVmOnUpdateCart(&$cart, &$force, &$html) {
		$this->plgCheckStockInCart($cart); 
	}
	
	public function plgVmOnAddToCart(&$cart) {
		$this->plgCheckStockInCart($cart); 
	}
	
	private function enableTriggerer() {
			$db = JFactory::getDBO(); 
			$q = 'SET @JOOMLA_IS_ADMIN = false;'; 
			$db->setQuery($q); 
			$db->execute(); 
			
			
	}
	
	private function disableTriggerer() {
			$db = JFactory::getDBO(); 
			$q = 'SET @JOOMLA_IS_ADMIN = true;'; 
			$db->setQuery($q); 
			$db->execute(); 
			
			
	}
	
	function plgVmOnUpdateOrderShipment(&$data, $old_status) {
		
		$this->enableTriggerer(); 
	}
	function plgVmOnUpdateOrderPayment(&$data, $old_status) {
		$this->enableTriggerer(); 
	}
	function plgVmOnCancelPayment(&$data, $old_status) {
		$this->enableTriggerer(); 
	}
	function plgVmOnUserOrder(&$data) {
		$this->enableTriggerer(); 
	}
	function plgVmGetProductStockToUpdateByCustom(&$data, $param, $custom) {
		$this->enableTriggerer(); 
	}
	
	function plgVmOnUpdateOrderLineShipment($data) {
		$this->enableTriggerer(); 
	}
	function plgVmOnUpdateOrderLinePayment($data) {
		$this->enableTriggerer(); 
	}
	function plgVmConfirmedOrder($cart, $order) {
		$this->enableTriggerer(); 
	}
	
	function plgVmBeforeStoreProduct(&$data, &$product_data) {
		$this->disableTriggerer(); 
	}
	function plgVmCloneProduct(&$product) {
		$this->disableTriggerer(); 
	}
	
	
	
}