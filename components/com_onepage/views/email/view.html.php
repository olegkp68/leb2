<?php
/**
 * @version		$Id: view.html.php 21705 2011-06-28 21:19:50Z RuposTel.com $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

 
class virtuemartViewEmail extends OPCView
{ 
	
    function display($tpl = null) {
	
	   $app = JFactory::getApplication('site'); 
	   require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
	   $JModelConfig = new JModelConfig(); 
	   $JModelConfig->loadVmConfig(); 
	   
	   $params = self::getParams(); 
		if (empty($params)) return; 
		if (empty($params['tid_enabled'])) return; 
		
		$days = (int)$params['config']->days; 
		if (empty($days)) return; 
		
		$debug = (int)$params['config']->debug; 
	   
		$admin = false; 
		
		if ((OPCmini::isSuperVendor()) || ((JFactory::getUser()->authorise('core.admin', 'com_virtuemart') || JFactory::getUser()->authorise('core.admin', 'com_virtuemart')))) { 
			  $admin = true; 
			  $this->getRandomOrder(); 
			  
			  $this->myParams = aftersale::getParams(); 
			  $this->myItemid = (int)$params['config']->myItemid; 
			 
		}
		else {
			if (php_sapi_name() !== 'cli') {
				$app->enqueueMessage('Please log in to frontend as administrator to see email template'); 
				return false; 
			}
		}
		
		if (empty($this->order)) return; 
		$this->user_currency_id = $this->order['details']['BT']->user_currency_id;
		
		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
		$this->currency = CurrencyDisplay::getInstance($this->user_currency_id);
		
		
		$customfields = VmModel::getModel('CustomFields'); 
		$language = $this->order['details']['BT']->order_language; 
		$db = JFactory::getDBO(); 
		
		$root_full = Juri::root(); 
		$root = Juri::root(true); 
				
		$dif  = strlen($root_full) - strlen($root) - 1; 
		$root_full = substr($root_full, 0, $dif); 
		
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('vmcustom');
		    $dispatcher->trigger('setCurrency', $this->user_currency_id); 
		
		$cart = VirtuemartCart::getCart(); 
		$cart->pricesCurrency = $this->user_currency_id; 
		
		foreach ($this->order['items'] as $ind=>$item) {
			
			$productModel = VmModel::getModel('product');
			$p = $productModel->getProduct($item->virtuemart_product_id, true, true); 
			$dispatcher->trigger('plgUpdateProductObject', array(&$p)); 
			
			
			//display current sales price:
			if (!empty($p->prices['salesPrice'])) {
				$this->order['items'][$ind]->price = $this->currency->priceDisplay($p->prices['salesPrice'], $this->user_currency_id);
				
				$this->order['items'][$ind]->product_subtotal_with_tax = (float)$this->order['items'][$ind]->product_quantity * $p->prices['salesPrice'];
			}
			else {
			
			
			$item->product_discountedPriceWithoutTax = (float) $item->product_discountedPriceWithoutTax;
			if (!empty($item->product_priceWithoutTax) && $item->product_discountedPriceWithoutTax != $item->product_priceWithoutTax) {
				
				
			
				
				$this->order['items'][$ind]->price = $this->currency->priceDisplay($item->product_discountedPriceWithoutTax, $this->user_currency_id);
			} else {
				$this->order['items'][$ind]->price = $this->currency->priceDisplay($item->product_item_price, $this->user_currency_id);
			}
			}
			$q = 'select `virtuemart_product_id`, `product_canon_category_id` from #__virtuemart_products where virtuemart_product_id = '.(int)$item->virtuemart_product_id.' and published = 1 and product_discontinued < 1'; 
			
			$q = 'select * from #__virtuemart_products where virtuemart_product_id = '.(int)$item->virtuemart_product_id.' and published = 1 and product_discontinued < 1'; 
			
			$db->setQuery($q); 
			$product_info = $db->loadAssoc(); 
			
			$q = "select `name` from #__extensions where name like 'plg_content_loadproduct' or element like 'loadproduct' and folder = 'content' and state = 1"; 
			$db->setQuery($q); 
			$load_product_enabled = $db->loadResult(); 
			
			
				
				
				
				
				if (!empty($product_info)) {
					
					
				if (!empty($load_product_enabled)) { 		
					
				$product_id = (int)$item->virtuemart_product_id; 
				
	   
				$q = 'select c.id from #__content as c where c.`attribs` LIKE \'%"virtuemart_product_id":"'.(int)$product_id.'"%\' and c.`state` = 1 order by modified desc limit 0,1'; 
				
				$db->setQuery($q); 
				$article_id = $db->loadResult();
		
		
				$aid = OPCmini::getArticleInLang($article_id, $language, array(), true); 
				if (!empty($aid)) {
					$article_id = (int)$aid; 
				}
	   
				if (!empty($article_id)) {
				require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_content'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'route.php'); 
				$t = ContentHelperRoute::getArticleRoute($article_id); 
				
				
				$defaultLnag = JFactory::getLanguage()->getDefault(); 
				
				if ($defaultLnag !== $language) {
				$seflang = OPCmini::getSefLangCode($language); 
				if (!empty($seflang)) {
				  $t .= '&lang='.$seflang; 
				}
				}
				if (!empty($this->myItemid)) {
					$t .= '&Itemid='.(int)$this->myItemid;
				}
				
				$url = JRoute::_($t); 
				
				
				
	   
				$this->order['items'][$ind]->mylink = $root_full.$url; 
			if (class_exists('cliHelper')) {
					//cliHelper::debug('Item '.$this->order['items'][$ind]->order_item_sku.'link in CLI is: '.$this->order['items'][$ind]->mylink); 
				}
				
			}
			}
			else {
				$url = 'index.php?option=com_virtuemart&virtuemart_product_id='.(int)$item->virtuemart_product_id; 
				if (!empty($product_info['product_canon_category_id'])) {
					$url .= '&virtuemart_category_id='.(int)$product_info['product_canon_category_id']; 
				}
				$url = $root_full.JRroute::_($url); 
				$this->order['items'][$ind]->mylink = $url; 
				
				if (class_exists('cliHelper')) {
					//cliHelper::debug('Item '.$this->order['items'][$ind]->order_item_sku.'link in CLI is: '.$this->order['items'][$ind]->mylink); 
				}
				
			}
			}
			else {
				//product is no longer published:
			    $this->order['items'][$ind]->mylink = ''; 
			}
			
		}
		
		
		$this->bottom_article = ''; 
		$this->top_article = ''; 
		
		if (!empty($params['config']->article_top)) {
			$this->top_article = OPCmini::getArticleInLang($params['config']->article_top, $language); 
		}
		
	   if (!empty($params['config']->article_bottom)) {
			$this->bottom_article = OPCmini::getArticleInLang($params['config']->article_bottom, $language); 
		}
	   if (php_sapi_name() !== 'cli') {
	    $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); 
		$x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); $x = @ob_get_clean(); 
	   }
	   
		parent::display($tpl); 
		
		if (php_sapi_name() !== 'cli') {
		 JFactory::getApplication()->close(); 
		 die(); 
		}
	
	 
	}
	
	
	private function getRandomOrder() {
		 $params = self::getParams(); 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php');
		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'opc.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'numbering.php');
		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR.'aftersale.php');
	     if ((OPCmini::isSuperVendor()) || ((JFactory::getUser()->authorise('core.admin', 'com_virtuemart') || JFactory::getUser()->authorise('core.admin', 'com_virtuemart')))) { 
			  $admin = true; 
				
				if (empty($params)) return; 
		if (empty($params['tid_enabled'])) return; 
		
		$days = (int)$params['config']->days; 
		if (empty($days)) return; 
		
		$debug = (int)$params['config']->debug; 
		
			  if ($debug) {
				  $statuses = $params['tid_autocreatestatus']; 
		
		$db = JFactory::getDBO(); 
		$im = array(); 
		foreach ($statuses as $s) {
		  $im[] = "'".$db->escape($s)."'"; 	
		}
		
		
		
		if ($debug) {
		for ($i=$days; $i>=0; $i--) {
			
		$q = 'select virtuemart_order_id from #__virtuemart_orders where '; 
		$w = array(); 
		if (!empty($im)) {
		 $w[] = ' order_status IN ('.implode(',', $im).') '; 
		}
		
		$w[] = ' DATE(DATE_ADD(created_on, INTERVAL +'.(int)$i.' DAY)) = CURRENT_DATE '; 
		$q = $q.implode(' and ', $w); 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (!empty($res)) {
				break;
		}
		}
		
		if (!empty($res)) {
			foreach ($res as $row) {
				$virtuemart_order_id = (int)$row['virtuemart_order_id']; 
				$orderModel = OPCmini::getModel('Orders'); 
				VirtueMartControllerOpc::emptyCache(); 			
				$order = $orderModel->getOrder($virtuemart_order_id);
				
				$this->order = $order; 
				return; 
			}
		}
		
		}
			  }
		 }			  
	}
	
	 private static function getParams() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
		$OnepageTemplateHelper = new OnepageTemplateHelper(); 
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDER_DATA_TXT', true); 
		
		foreach ($tids as $t) {
			if ($t['file'] === 'aftersale.php') return $t; 
		}
		
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDERS_TXT', true); 
		
		foreach ($tids as $t) {
			if ($t['file'] === 'aftersale.php') return $t; 
		}
		
		return array(); 
	}
	
    public function renderMailLayout($doVendor, $recipient) {

	$useSSL = (int)VmConfig::get('useSSL', 0);
	$useXHTML = true;
	$this->assignRef('useSSL', $useSSL);
	$this->assignRef('useXHTML', $useXHTML);
	
	
	
	
	
	$usermodel = VmModel::getModel('user');
	$usermodel->_data = null; 
	if (empty($usermodel->_id))
	{
	 if (!empty($GLOBALS['opc_new_user']))
	 $usermodel->_id = $GLOBALS['opc_new_user']; 
	}
	if (isset($this->user))
	if (isset($this->user->userInfo))
	{
	 $vmuser = $this->user->userInfo; 
	}
	else
	{
	$vmuser = $usermodel->getUser();
	$vmuser = reset($vmuser->userInfo);
	}
	$this->userFields = $userFieldsModel->getUserFieldsFilled($userFields, $vmuser);
	
	

    if (VmConfig::get('order_mail_html')) {
	    $mailFormat = 'html';
	    $lineSeparator="<br />";
    } else {
	    $mailFormat = 'raw';
	    $lineSeparator="\n";
    }

    $virtuemart_vendor_id=1;
    $vendorModel = VmModel::getModel('vendor');
    $vendor = $vendorModel->getVendor($virtuemart_vendor_id);
    $vendorModel->addImages($vendor);
	
	if (method_exists($vendorModel, 'getVendorAddressFields'))
	$vendor->vendorFields = $vendorModel->getVendorAddressFields();
    $this->assignRef('vendor', $vendor);

	if (!$doVendor) {
	    $this->subject = JText::sprintf('COM_VIRTUEMART_NEW_SHOPPER_SUBJECT', $this->user->username, $this->vendor->vendor_store_name);
	    $tpl = 'mail_' . $mailFormat . '_reguser';
	} else {
	    $this->subject = JText::sprintf('COM_VIRTUEMART_VENDOR_NEW_SHOPPER_SUBJECT', $this->user->username, $this->vendor->vendor_store_name);
	    $tpl = 'mail_' . $mailFormat . '_regvendor';
	}

	$this->assignRef('recipient', $recipient);
	$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);
	$this->layoutName = $tpl;
	$this->setLayout($tpl);
	parent::display();
    }

	 
	 
}

