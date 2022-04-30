<?php 
/**
 * @version		cartstock.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		COMMERCIAL 
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemCartstock extends JPlugin
{
	
	public static $calculator; 
	public static $lastcart; 
	public static $isFakeCartProces;
	
	private static $myParams; 
	private static $errors = array(); 
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		
		if ($this->canRun()) {
			require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
			
		}
		self::$myParams = $this->params; 
		
		
	}
	
	
	function plgCheckCartQuantities(&$cart) {
		
		self::$lastcart = $cart; 
		
		
		
	}
	
	function plgVmOnDisplayMiniCart(&$html='', &$errors=array()) {
		$timestamp = self::getTimestamp(); 
		
		if (!empty($timestamp)) {
			$date = new DateTime($timestamp);
			$timestamp = $date->format(DateTime::ATOM);
		}
		else {
			$timestamp = ''; 
		}
		
		$countdown_html = $this->renderByLayout('countdown', array('timestamp'=>$timestamp, 'minutes'=>(int)self::$myParams->get('cart_clear_time', 15))); 
		$html .= $countdown_html; 
		
		
		if (!empty(self::$errors)) {
			$errors = self::$errors; 
		}
	}
	
	
	function renderByLayout($layout, $viewData=array()) {
		$defaultLayout = __DIR__.DIRECTORY_SEPARATOR.'cartstock'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$layout.'.php'; 
		$viewData['debug'] = $this->params->get('debug', false); 
		if (file_exists($defaultLayout)) {
			ob_start(); 
			require($defaultLayout); 
			$html = ob_get_clean(); 
			
			return $html; 
		}
		return ''; 
	}
	
	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {
		$html = ''; 
		$this->plgVmOnDisplayMiniCart($html); 
		if (!empty($html)) {
			if (empty($payment_advertise)) $payment_advertise = array(); 
			$payment_advertise[] = $html; 
		}
	}
	public static function getTimestamp() {
		$session_id = self::getSessionId(); 
		$db = JFactory::getDBO(); 
		$q = 'select max(added_on) from #__onepage_cartstock where `session_id` = \''.$db->escape($session_id).'\' limit 1'; 
		$db->setQuery($q); 
		$time = $db->loadResult(); 
		return $time; 
	}
	public static function getProductRow($cart_key, $row) {
			$session_id = self::getSessionId(); 
			$arr = array(); 
			$arr['id'] = 'NULL'; 
			$arr['session_id'] = $session_id; 
			$arr['cart_key'] = $cart_key; 
			$arr['product_id'] = (int)$row['virtuemart_product_id']; 
			$arr['quantity'] = (int)$row['quantity']; 
			if (empty($row['customProductData'])) {
				$arr['customProductData'] = json_encode(array()); 
			}
			elseif (is_string( $row['customProductData'])) {
				$arr['customProductData'] = $row['customProductData']; 
			}
			else {
				$arr['customProductData'] = json_encode($row['customProductData']); 
			}
			$arr['added_on'] = NULL; 
			$arr['seen_on'] = 'NOW()'; 
			return $arr; 
	}
	
	public function plgVmOnAddToCart(&$cart) {
		
		foreach ($cart->cartProductsData as $cart_key=>$row) {
			$p = new stdClass(); 
			$avai_except_me = self::getCurrentAvailableStock($row, $p, true); 
			if (((int)$row['quantity'] > $avai_except_me) && ($avai_except_me > 0)) {
				//$msg = 'Došlo k zníženiu požadovaného množstva vo Vašom košíku'; 
				//$msg = 'Produkt už nieje dostupný v požadovanom množstve, množstvo bolo znížené na '.$avai_except_me.' ks'; 
				$msg = JText::_('PLG_SYSTEM_CARTSTOCK_LOWERED'); 
				$msg = str_replace('{quantity}', $avai_except_me, $msg); 
				self::$errors[$msg] = $msg; 
				if ($row['quantity'] != $avai_except_me) {
					//self::resetCounter(true, $cart_key);
					
				}
				$cart->cartProductsData[$cart_key]['quantity'] = (int)$avai_except_me;  
				if (isset($cart->products[$cart_key])) {
					$cart->products[$cart_key]->quantity = (int)$avai_except_me;
					$cart->products[$cart_key]->errorMsg = $msg; 
				}
				 
			}
			else
			if ($avai_except_me <= 0) {
				 
				if (isset($cart->products[$cart_key])) {
					$cart->products[$cart_key]->quantity = 0; 
					//$msg = 'Produkt už nieje dostupný'; 
					
					//self::$errors[$msg] = $msg; 
					$cart->products[$cart_key]->errorMsg = $msg; 
				}
				if (isset($cart->cartProductsData[$cart_key])) {
					$msg = JText::_('PLG_SYSTEM_CARTSTOCK_NOTADDED'); 
					self::$errors[$msg] = $msg; 
				}
				unset($cart->cartProductsData[$cart_key]);
				
			} elseif ((int)$row['quantity'] <= $avai_except_me) {
				//self::resetCounter(true, $cart_key); 
				
				if ($row['quantity'] != $avai_except_me) {
					self::resetCounter(true, $cart_key); 
				
				}
				
			}
			else {
				//self::$errors[__LINE__] = __LINE__; 
				self::resetCounter(); 
			}
		}
		
		self::syncCart($cart); 
	}
	public static function resetCounter($updateState=false, $cart_key=null) {
		$session_id = self::getSessionId(); 
		$db = JFactory::getDBO(); 
		$q = 'update `#__onepage_cartstock` set `added_on` = CURRENT_TIMESTAMP '; 
		
		if (($updateState) && (is_null($cart_key))) {
			//only if the product is still available we can set it to new counter
			
			$q .= ', `state` = 1 '; 
			
		
			$db->setQuery($q); 
			$db->execute(); 
			
		}
		
		$q .= ' where `session_id` = \''.$db->escape($session_id).'\''; 
		
		$db->setQuery($q); 
		$db->execute(); 
		
		if (($updateState) && (!is_null($cart_key))) {
			//only if the product is still available we can set it to new counter
			
			$q = 'update `#__onepage_cartstock` set `state` = 0 '; 
			$q .= ' where `session_id` = \''.$db->escape($session_id).'\' and `cart_key` = '.(int)$cart_key; 
		
			$db->setQuery($q); 
			$db->execute(); 
			
		}
		
		
		
		
	}
	public static function clearCurrent() {
		$db = JFactory::getDBO(); 
		$session_id = self::getSessionId(); 
		$q = 'delete from #__onepage_cartstock where `session_id` = \''.$db->escape($session_id).'\''; 
		$db->setQuery($q); 
		$db->execute(); 
		return; 
	}
	public static function getSessionId() {
			static $session_id; 
			if (empty($session_id)) {
				$session_id = JFactory::getSession()->getId(); 
			}
			return $session_id; 
	}
	public static function syncCart($cart=null) {
		if (empty($cart)) {
			if (!class_exists('VmConfig'))
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
			VmConfig::loadConfig(); 
		
			$cart = VirtuemartCart::getCart(); 
		}
		self::createTable(); 
	
		$session_id = self::getSessionId(); 
		
		if (empty($cart->cartProductsData)) {
			self::clearCurrent(); 
			return; 
		}
		
		$db = JFactory::getDBO(); 
		$q = 'select * from #__onepage_cartstock where `session_id` = \''.$db->escape($session_id).'\''; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		$q = 'start transaction'; $db->setQuery($q); $db->execute(); 
		
		
		
		
		if (empty($res)) {
			foreach ($cart->cartProductsData as $cart_key => $row) {
				$arr = self::getProductRow($cart_key, $row); 
				OPCMini::insertArray('#__onepage_cartstock', $arr); 
				
				
				
			}
		}
		else {
			
			
			
			
			
			$done = array(); 
			foreach ($res as $row) {
				foreach ($cart->cartProductsData as $cart_key => $pd) {
					//to update or skip:
					if ($row['cart_key'] == $cart_key) {
						$arr = self::getProductRow($cart_key, $pd); 
						$arr['id'] = (int)$row['id']; 
						$arr['seen_on'] = 'NOW()'; 
						OPCMini::insertArray('#__onepage_cartstock', $arr); 
						$done[$cart_key] = $cart_key; 
						
					}
				}
			}
			foreach ($cart->cartProductsData as $cart_key => $pd) {
				if (!isset($done[$cart_key])) {
					$arr = self::getProductRow($cart_key, $pd); 
					OPCMini::insertArray('#__onepage_cartstock', $arr); 
					$done[$cart_key] = $cart_key; 
				}
			}
			
			$toRemove = array(); 
			foreach ($res as $row) {
				if (!isset($done[$row['cart_key']])) {
					$toRemove[(int)$row['id']] = (int)$row['id']; 
				}
			}
			if (!empty($toRemove)) {
				
				$q = 'delete from #__onepage_cartstock where `id` IN ('.implode(',', $toRemove).')'; 
				$db->setQuery($q); $db->execute(); 
			}
			
		}
		
		
		$q = 'commit'; $db->setQuery($q); $db->execute(); 
		
		
		
	}
	
	public static function createTable() {
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		
		$db = JFactory::getDBO(); 
		if (!OPCmini::tableExists('onepage_cartstock')) {
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_cartstock` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `cart_key` int(1) NOT NULL,
  `product_id` int(1) NOT NULL,
  `customProductData` varchar(1024) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `seen_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` SMALLINT NOT NULL DEFAULT \'0\' COMMENT \'state=0 - rezervovane, state=1 - v procese objednavky, state = 2 expirovane\', 
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_key` (`cart_key`,`session_id`),
  KEY `session_id` (`session_id`),
  KEY `product_id` (`product_id`),
  KEY `quantity` (`quantity`),
  KEY `added_on` (`added_on`),
  KEY `see_on` (`see_on`),
  KEY (`state`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;'; 
	$db->setQuery($q); 
	$db->execute(); 
	OPCmini::clearTableExistsCache(); 
		}
	}
	
	function plgVmOnUpdateCart(&$cart, &$force, &$html) {
		
		foreach ($cart->cartProductsData as $cart_key=>$row) {
			
			
			$p = new stdClass(); 
			$avai_except_me = self::getCurrentAvailableStock($row, $p, true); 
			if ((int)$row['quantity'] > $avai_except_me) {
				//$msg = 'Došlo k zníženiu požadovaného množstva vo Vašom košíku'; 
				
				$msg = JText::_('PLG_SYSTEM_CARTSTOCK_LOWERED'); 
				$msg = str_replace('{quantity}', $avai_except_me, $msg); 
				
				$html .= $msg;
				$cart->cartProductsData[$cart_key]['quantity'] = (int)$avai_except_me; 
				if (!empty($cart->products) && (!empty($cart->products[$cart_key]))) {
					$cart->products[$cart_key]->quantity = $avai_except_me;
				}
				
				if (!empty(self::$errors[$msg])) {
						$errorMsg = ''; 
					
					}
					else {
						JFactory::getApplication()->enqueueMessage($msg); 
						self::$errors[$msg] = $msg; 
					}
				
				
			}
			if ($avai_except_me <= 0) {
				unset($cart->cartProductsData[$cart_key]); 
				unset($cart->products[$cart_key]); 
				
				//$msg = 'Produkt už nieje dostupný'; 
				$msg = JText::_('PLG_SYSTEM_CARTSTOCK_NOTADDED'); 
				if (!empty(self::$errors[$msg])) {
						$errorMsg = ''; 
					
					}
					else {
						JFactory::getApplication()->enqueueMessage($msg, 'error'); 
						self::$errors[$msg] = $msg; 
						
					}
				
				
			}
			
		}
		
		self::syncCart($cart); 
	}
	
	public function onAfterRoute() {
		JFactory::getLanguage()->load('plg_system_cartstock', __DIR__); 
	
		self::syncCart(); 
		
		
		//index.php?option=com_virtuemart&view=productdetails&task=recalculate&format=json&nosef=1&lang=sk&field%5B7468%5D%5B78858%5D%5Bcustomfield_value%5D%5B0%5D=37&quantity%5B%5D=122&virtuemart_product_id%5B%5D=7468&option=com_virtuemart&virtuemart_product_id%5B%5D=7468&pname=Obuv+ARDON+O1+%C4%8D.37&pid=7468&Itemid=1064&_=1555578026496
		$view = JRequest::getVar('view', ''); 
		$option = JRequest::getVar('option', ''); 
		$task = JRequest::getVar('task', ''); 
		$virtuemart_product_id = JRequest::getVar('virtuemart_product_id', ''); 
		$pid = JRequest::getVar('pid', ''); 
		if ($option === 'com_virtuemart') {
			if ($view === 'productdetails') {
				if ($task === 'recalculate') {
					
					
					
					$virtuemart_product_idArray = JRequest::getVar('virtuemart_product_id', array()); 
					if (is_array($virtuemart_product_idArray)) {
						$virtuemart_product_id = (int)reset($virtuemart_product_idArray); 
					}
					else 
					{
						$virtuemart_product_id = (int)$virtuemart_product_idArray;
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
			
		    $this->plgUpdateProductObject($product); 
			

		// Also return all messages (in HTML format!):
		// Since we are in a JSON document, we have to temporarily switch the type to HTML
		// to make sure the html renderer is actually used
		/*
		@header('Content-Type: application/json');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		*/
		
		
		
			
		  
		}
		
				}
			}
		}
	}
	
	function plgVmOnCheckoutCheckStock( &$cart, &$p, &$quantity, &$errorMsg, &$adjustQ, $inCheckoutValidation=false) {
	
		foreach ($cart->cartProductsData as $cart_key=>$row) {
			if ((int)$row['virtuemart_product_id'] !== (int)$p->virtuemart_product_id) continue; 
			
			$px = new stdClass(); 
			//updates product_in_stock:
			$avai_all = self::getCurrentAvailableStock($row, $p, false); 
			
			$avai_except_me = self::getCurrentAvailableStock($row, $px, true); 
			
			if (false)			
			if (($row['virtuemart_product_id'] == 1606)) {
				var_dump($avai_all); 
				var_dump($avai_except_me); 
				//die('x'.__LINE__); 
			}
			//$p->product_name .= var_export($avai_except_me); 
			$p->max_order_level = $avai_except_me; 
			
			if ($avai_except_me <= 0) {
				
				if ($adjustQ) {
				//give error: 
					$quantity = 0;
					$p->quantity = 0; 
				
				}
				
				if (isset($p->product_name)) {
					//$errorMsg = 'Produkt '.$p->product_name.' už nieje dostupný a bol odstránený z košíku'; 
					$errorMsg = JText::_('PLG_SYSTEM_CARTSTOCK_REMOVED_NAME'); 
					$errorMsg = str_replace('{product_name}', $p->product_name, $errorMsg); 
					
				}
				else {
					//$errorMsg = 'Produkt už nieje dostupný a bol odstránený z košíku'; 
					$errorMsg = JText::_('PLG_SYSTEM_CARTSTOCK_REMOVED'); 
				}
				if (!empty(self::$errors[$errorMsg])) {
					$errorMsg = ''; 
				}
				else {
					self::$errors[$errorMsg] = $errorMsg; 
				}
				return false; 
			}
			/*
			echo __LINE__.':';var_dump($avai_except_me); 
			echo __LINE__.':';var_dump($quantity); 
			die('ok'); 
			*/
			
			if ($quantity > $avai_except_me) {
				
				
				if ($adjustQ) {
					$quantity = $avai_except_me; 
					$p->quantity = $avai_except_me;
					//$errorMsg = 'Produkt už nieje dostupný v požadovanom množstve, množstvo bolo znížené na '.$avai_except_me.' ks'; 
					//$msg = 'Produkt už nieje dostupný v požadovanom množstve, množstvo bolo znížené na '.$avai_except_me.' ks'; 
					$msg = JText::_('PLG_SYSTEM_CARTSTOCK_LOWERED'); 
					$errorMsg = str_replace('{quantity}', $avai_except_me, $msg); 
					
					
					if (!empty(self::$errors[$errorMsg])) {
						$errorMsg = ''; 
					
					}
					else {
						JFactory::getApplication()->enqueueMessage($errorMsg); 
						self::$errors[$errorMsg] = $errorMsg; 
					}
					
					
					//JFactory::getApplication()->enqueueMessage($errorMsg, 'warning'); 
					$adjustQ = true; 
				}
				else {
					//$errorMsg = 'Produkt už nieje dostupný v požadovanom množstve, množstvo bolo znížené na '.$avai_except_me.' ks'; 
					
					$msg = JText::_('PLG_SYSTEM_CARTSTOCK_LOWERED'); 
					$errorMsg = str_replace('{quantity}', $avai_except_me, $msg); 
					
					if (!empty(self::$errors[$errorMsg])) {
						$errorMsg = ''; 
					
					}
					else {
						JFactory::getApplication()->enqueueMessage($errorMsg, 'warning'); 
						self::$errors[$errorMsg] = $errorMsg; 
					}
					
					return false; 
				}
				//$cart->cartProductsData[$cart_key]['quantity'] = $quantity; 
				
				
				return true; 
			}
			if ($quantity < $avai_except_me) {
				return true; 
			}
			
			
		}
		
		return; 
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
		 
		}
	}
	
	public function plgUpdateProductObject(&$p, $group_quantity=0, &$cart=null, $skipCalc=false, $force=false) {
		
		self::createTable(); 
		self::syncCart(); 
		
		
		
				$view = JRequest::getVar('view', ''); 
		$option = JRequest::getVar('option', ''); 
		$task = JRequest::getVar('task', ''); 
		$virtuemart_product_id = JRequest::getVar('virtuemart_product_id', ''); 
		$pid = JRequest::getVar('pid', ''); 
		
		
		
		
		
		self::getCurrentAvailableStock(array(), $p); 
		
		
		$arr = array('productdetails', 'notify', 'category', 'virtuemart'); 
		
		if ($option === 'com_virtuemart') {
			if (in_array($view, $arr)) {
				$p->product_in_stock = $p->product_in_stock_original - $p->product_cart_reserved_all; 
				
			}
		}
		
		$cart = VirtuemartCart::getCart(); 
		self::syncCart($cart); 
		
		$p->plgUpdateProductObject = true; 
	}
	
	//stan remove item from counter:
	public static function plgVmOnRemoveFromCart($cart, $cart_key) {
		$db = JFactory::getDBO(); 
		$session_id = JFactory::getSession()->getId(); 
		$q = 'delete from #__onepage_cartstock where `session_id` = \''.$db->escape($session_id).'\' and `cart_key` = \''.$db->escape($cart_key).'\''; 
		$db->setQuery($q); 
		$db->execute(); 
	}
	
	//stan - clear temp data since they are accounted in product_ordered
	public static function plgVmConfirmedOrder($cart, $order) {
		$db = JFactory::getDBO(); 
		$session_id = JFactory::getSession()->getId(); 
		$q = 'delete from #__onepage_cartstock where `session_id` = \''.$db->escape($session_id).'\''; 
		$db->setQuery($q); 
		$db->execute(); 
	}
	
	public static function getCurrentAvailableStock($cartRow=array(), &$p=null, $exceptMine=false) {
		if (!empty($cartRow)) {
			$product_id = (int)$cartRow['virtuemart_product_id']; 
		}
		elseif (!empty($p)) {
			$product_id = (int)$p->virtuemart_product_id; 
		}
		$db = JFactory::getDBO(); 
		$q = 'select `product_in_stock`, `product_ordered` from #__virtuemart_products where `virtuemart_product_id` = '.(int)$product_id; 
		$db->setQuery($q); 
		$stock = $db->loadAssoc(); 
		if ((empty($p)) || (empty($p->virtuemart_product_id))) {
			$p = new stdClass(); 
			$p->virtuemart_product_id = $product_id; 
		}
		
		$reserved = self::getReservedStock($p, true); 
		$reserved_all = self::getReservedStock($p, false); 
		
		if (false)
		if (($cartRow['virtuemart_product_id'] == 1606)) {
				echo __LINE__; var_dump($reserved); 
				echo __LINE__; var_dump($reserved_all); 
				
			}
		
		
		$mine_reserved = $reserved_all - $reserved; 
		/*
		echo __LINE__.': reserved '.$product_id.' '; 
		var_dump($reserved);
		echo __LINE__.': reserved all '.$product_id.' ';
		var_dump($reserved_all); 
		echo __LINE__.': mine reserved '.$product_id.' ';
		var_dump($mine_reserved); 
		*/
		
		if ($reserved < 0) $reserved = 0; 
		$t = (int)$stock['product_in_stock'] - (int)$reserved; 
		if ($t < 0) $t = 0; 
		
		{
		$p->product_in_stock = $t; 
		
		$childRes = self::getChilds($p->virtuemart_product_id); 
		
		//is not a parent poduct:
		if ($childRes === false) {
			$p->orderable = true; 
			
		}
		
		if ($childRes === false) {
			//$p->orderable = false; 
		}
		$p->is_parent_product = false; 
		//parent product handling:
		if ($childRes === true) {
				//parent is not available
				$p->product_in_stock = 0; 
				$p->product_ordered = 0; 
				$p->is_parent_product = true; 
		}
		elseif (($chidRes !== false) && ($childRes >= 1)) {
			$p->product_in_stock = (int)$childRes; 
			
			$stock = array();
			
			$stock['product_in_stock'] = (int)$childRes; 
			$stock['product_ordered'] = 0; 
			
			$reserved = 0; 
			$reserved_all = 0; 
			$p->product_ordered = 0; 
			$p->is_parent_product = true; 
			
			
			
		}
		//end parent handling
		
		$p->product_in_stock_original = (int)$stock['product_in_stock']; 
		$p->product_cart_reserved = (int)$reserved; 
		$p->product_cart_reserved_all = (int)$reserved_all; 
		
		if (self::$myParams->get('ignore_ordered', false)) {
			$p->product_ordered = 0; 
		}
		
		if ((empty($p->max_order_level)) || ((int)$p->max_order_level < ((int)$p->product_in_stock_original - $reserved_all - abs((int)$p->product_ordered)))) {
			$p->max_order_level = (int)$p->product_in_stock_original - $reserved_all - abs((int)$p->product_ordered); 
		}
		if (empty($p->step_order_level)) $p->step_order_level = 1; 
		if (empty($p->min_order_level)) $p->min_order_level = 1; 
		
		
		}
		if (self::$myParams->get('ignore_ordered', false)) {
			$stock['product_ordered'] = 0; 
		}
		return (int)$stock['product_in_stock'] - (int)$reserved - abs((int)$stock['product_ordered']); 
	}
	
	//return true -> is parent but has no stock on child
	//return false -> not a parent product
	//return (int)X -> stock
	public static function getChilds($product_id) {
		$db = JFactory::getDBO(); 
		$q = 'select virtuemart_product_id, `product_in_stock`, `published` as publish, `product_discontinued` from #__virtuemart_products where product_parent_id = '.(int)$product_id; 
		//.' and publish = 1 and product_discontinued != 1'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (empty($res)) return false; 
		 
		$p = array(); 
		$stock = 0; 
		foreach ($res as $row) {
			$pid = (int)$row['virtuemart_product_id']; 
			$p[$pid] = $pid; 
			if (!empty($row['publish']) && (empty($row['product_discontinued']))) {
				$stock += (int)$row['product_in_stock']; 
			}
		}
		
		if (empty($stock)) return true; 
		
		$session_id = self::getSessionId(); 
		
		$q = 'select sum(quantity) from #__onepage_cartstock where product_id IN ('.implode(',', $p).') and `session_id` != \''.$db->escape($session_id).'\' and `state` < 2'; 
		$db->setQuery($q); 
		$child_sum = (int)$db->loadResult(); 
		
		
		
		
		$avai_stock = $stock - $child_sum; 
		if ($avai_stock > 0) {
			return (int)$avai_stock; 
		}
		
		//not available:
		return true; 
		
		
	}
	
	public static function updateLocks($order_status) {
		$db = JFactory::getDBO(); 
		$q = 'select `order_stock_handle` from #__virtuemart_orderstates where `order_status_code` = \''.$db->escape($order_status).'\''; 
		$db->setQuery($q); 
		$stock_handle = $db->loadResult(); 
		switch($stock_handle) {
			//O removed
			//R reserved
			//A available
			case 'O': 
			//is removed from stock
				self::clearCurrent(); 
				break; 
			default: 
				//pocas platby mu zastavime pocitadlo:
				self::resetCounter(true); 
				break; 
				
		}
		
	}
	
	public function plgVmOnUpdateOrderShipment(&$data,$old_order_status)
	{
		if (is_object($data)) {
			self::updateLocks($data->order_status); 
		}
		elseif (is_array($data)) 
		{
		if (isset($data['details']['BT'])) {
			self::updateLocks($data['details']['BT']->order_status); 
		}
		else
		if (isset($data['order_status'])) {
			self::updateLocks($data['order_status']); 
		}
		}
	}
	public function plgVmOnUpdateOrderPayment(&$data,$old_order_status)
	{
		if (is_object($data)) {
			self::updateLocks($data->order_status); 
		}
		elseif (is_array($data)) 
		{
		if (isset($data['details']['BT'])) {
			self::updateLocks($data['details']['BT']->order_status); 
		}
		else
		if (isset($data['order_status'])) {
			self::updateLocks($data['order_status']); 
		}
		}
	}
	public function plgVmCouponUpdateOrderStatus($data,$old_order_status)
	{
		if (is_object($data)) {
			self::updateLocks($data->order_status); 
		}
		elseif (is_array($data)) 
		{
		if (isset($data['details']['BT'])) {
			self::updateLocks($data['details']['BT']->order_status); 
		}
		else
		if (isset($data['order_status'])) {
			self::updateLocks($data['order_status']); 
		}
		}
		
	}
	
	public static function getReservedStock($p, $exceptMine=false) {
		
		static $session_id; 
			if (empty($session_id)) {
				$session_id = JFactory::getSession()->getId(); 
			}
		
		$db = JFactory::getDBO(); $q = 'start transaction'; $db->setQuery($q); $db->execute(); 
		
		$q = 'update `#__onepage_cartstock` set `state` = 2 where (NOW() - INTERVAL '.(int)self::$myParams->get('cart_clear_time', 15).' MINUTE) >  `added_on` and `state` != 2'; 
		$db->setQuery($q); $db->execute(); 
		
		$q = 'delete from `#__onepage_cartstock` where `state` = 2 and (NOW() - INTERVAL 360 MINUTE) >  `added_on` '; 
		$db->setQuery($q); $db->execute(); 
		
		$q = 'select sum(quantity) from #__onepage_cartstock where `product_id` = '.(int)$p->virtuemart_product_id.' '; ; 
		if (!empty($exceptMine)) {
			$q .= ' and `session_id` != \''.$db->escape($session_id).'\''; 
		}
		$q .= ' and `state` < 2 '; 
		
		$db->setQuery($q); 
		$reserved = (int)$db->loadResult(); 
		
		$q = 'commit'; $db->setQuery($q); $db->execute(); 
		
		return (int)$reserved; 
	}
	
	
	
}
