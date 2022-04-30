<?php 
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* 
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 



class OPCrenderer extends OPCview {
  public static $extrahtml;
  public function __construct() {
	  
	  if (defined('OPC_FOR_HIKA_LOADED')) {
		  		 $view = JRequest::getVar('view', ''); 
				 if ($view !== 'opc') {
					return; 
				 }
	   }
	   
	   
		if (!class_exists('VirtueMartViewCart'))
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'virtuemart.cart.view.html.php'); 
	  
			if (class_exists('op_languageHelper'))
			{
		$layoutName = $this->getLayout();
		if (!$layoutName) $layoutName = JRequest::getWord('layout', 'default');
		$this->assignRef('layoutName', $layoutName);
		$format = JRequest::getWord('format');
		if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
		$cart = VirtueMartCart::getCart();
		$this->assignRef('cart', $cart);
		$this->cart =& $cart; 
		$checkout_task = 'confirm';
		$this->assignRef('checkout_task', $checkout_task);
		if (method_exists($this, 'getCheckoutAdvertise'))
		$checkoutAdvertise = array(); //$this->getCheckoutAdvertise();
		$totalInPaymentCurrency = 0; //$this->getTotalInPaymentCurrency();
		$shippingText = ''; 
		$this->assignRef('select_shipment_text', $shippingText);
		$paymentText = ''; 
		$this->assignRef('select_payment_text', $paymentText);
		$this->assignRef('checkout_link_html', $paymentText);
	    //set order language
	    $lang = JFactory::getLanguage();
	    $order_language = $lang->getTag();
		$this->assignRef('order_language',$order_language);
		$useSSL = (int)VmConfig::get('useSSL', 0);
		$useXHTML = true;
		$this->assignRef('useSSL', $useSSL);
		$this->assignRef('useXHTML', $useXHTML);
		$this->assignRef('totalInPaymentCurrency', $totalInPaymentCurrency);
		$this->assignRef('checkoutAdvertise', $checkoutAdvertise);
		$tmp = 0;
		
	    $this->couponCode = (isset($this->cart->couponCode) ? $this->cart->couponCode : '');
		$coupon_text = $cart->couponCode ? OPCLang::_('COM_VIRTUEMART_COUPON_CODE_CHANGE') : OPCLang::_('COM_VIRTUEMART_COUPON_CODE_ENTER');
		$customfieldsModel = VmModel::getModel ('Customfields');
		$this->assignRef('customfieldsModel', $customfieldsModel); 
		$this->assignRef('coupon_text', $coupon_text);
		
		
		$userFieldsModel = VmModel::getModel ('userfields');
		if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
			$userFieldsCart = $userFieldsModel->getUserFields(
				'cart'
				, array('captcha' => true, 'delimiters' => true) // Ignore these types
				, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
			);
		
		

			$userFieldsCartFields = $userFieldsModel->getUserFieldsFilled(
				$userFieldsCart
				,$this->cart->cartfields
			);
			
			$this->assignRef('userFieldsCart', $userFieldsCartFields); 
		}
		else {
			$userFieldsCartFields = array(); 
			$userFieldsCartFields['fields'] = array(); 
			$this->assignRef('userFieldsCart', $userFieldsCartFields); 
		}	
		
		$this->assignRef('found_shipment_method', $tmp);

		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');  
		$OPCloader = new OPCloader; 
		$this->user = $OPCloader->getUser($cart); 
		$this->cart =& $cart; 
		
		if (!class_exists ('CurrencyDisplay'))
				require(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'currencydisplay.php');

		if (!empty($cart) && (!empty($cart->pricesCurrency)))
			{
			$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);
			$this->currencyDisplay = $currencyDisplay; 
			$this->assignRef('currencyDisplay', $currencyDisplay); 
			}
			else
			{
			$currencyDisplay = CurrencyDisplay::getInstance();
			$this->currencyDisplay = $currencyDisplay; 
			$this->assignRef('currencyDisplay', $currencyDisplay); 
			}
		
		}
		
	
	}
	static $_instance;
	static public function getInstance() {
		if (!is_object(self::$_instance)) {
			self::$_instance = new OPCrenderer();
		} else {
			//We store in UTC and use here of course also UTC
			
		}
		return self::$_instance;
	}
	
	//only FE+published+and public modules can be shown:
	public static function renderModuleById($id) 
		{
		
		$db = JFactory::getDBO(); 
		$q = 'select * from `#__modules` where `id` = '.(int)$id; 
		$db->setQuery($q); 
		$mrow = $db->loadAssoc(); 
		
		if (($mrow['published'] === "1") && ($mrow['access'] === "1") && ($mrow['client_id'] === "0")) {
		
		if (!empty($mrow)) {
			$params = $mrow['params']; 
			if (!empty($params)) $params = json_decode($params, true); 
			
			else $params = null; 
			$name = $mrow['module']; 
			
			 jimport( 'joomla.application.module.helper' );

	    $document   = JFactory::getDocument();
		$renderer   = $document->loadRenderer('module');
		if (empty($params))
		$params   = array();
		$module = JModuleHelper::getModule($name); 
		foreach ($mrow as $key=>$val) {
			$module->{$key} = $val; 
		}
		
		$html = $renderer->render($module, $params);
			
			
			return $html; 
		}
		}
		
		 
		
		
		return '<notfound data-msg="requires joomla 3.9"></notfound>'; 
	}
	
	
	public static $delStarted;
	 
	//based on \libraries\joomla\document\html\html.php to support the same type of arguments
	public static function parseTemplate(&$html)
	{
		$matches = array();

		if (preg_match_all('#<jdoc:include\ type="([^"]+)"(.*)\/>#iU', $html, $matches))
		{
			$template_tags_first = array();
			$template_tags_last = array();

			// Step through the jdocs in reverse order.
			for ($i = count($matches[0]) - 1; $i >= 0; $i--)
			{
				$type = $matches[1][$i];
				$attribs = empty($matches[2][$i]) ? array() : JUtility::parseAttributes($matches[2][$i]);
				$name = isset($attribs['name']) ? $attribs['name'] : null;

				// Separate buffers to be executed first and last
				if ($type == 'module' || $type == 'modules')
				{
					$template_tags_first[$matches[0][$i]] = array('type' => $type, 'name' => $name, 'attribs' => $attribs);
				}
				else
				{
					$template_tags_last[$matches[0][$i]] = array('type' => $type, 'name' => $name, 'attribs' => $attribs);
				}
			}
			// Reverse the last array so the jdocs are in forward order.
			$template_tags_last = array_reverse($template_tags_last);
			
			$opc_debug_theme = OPCconfig::get('opc_debug_theme', false); 
			
			
			foreach ($template_tags_first as $khtml=>$mp)
			{
				$position= $mp['name']; 
				$htmlO = self::renderModuleByPosition($position); 
				$html = str_replace($khtml, $htmlO, $html); 
				
				if (!empty($opc_debug_theme)) {
				  $html = '<fieldset class="opc_debug"><legend class="opc_debug">Module Position:'.$position.'</legend>'.$html.'<fieldset>'; 
				}
				
			}
		}
		
		if (empty(self::$extrahtml)) self::$extrahtml = ''; 	

		//return $html;
	}
	 
	public static function hasDel()
	{
	   static $hasDel; 
	   if (isset($hasDel)) return $hasDel; 
	   
	   
	   
	   $selected_template = self::getSelectedTemplate();  
	   
	   
	   
	   $hasDel = file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'delimiter_start.php'); 
	   
	   if (!$hasDel) return false; 
	   
	   $config = OPCconfig::getValue('theme_config', $selected_template, 0, false, false); 
	   if (!empty($config) && (empty($config->use_delimiters))) $hasDel = false; 
	   else
	   if (!empty($config) && (!empty($config->use_delimiters))) $hasDel = true; 
	   else
	   if (empty($config)) $hasDel = false; 
	   
	   
	   return $hasDel; 
	}
	public static $num_delimiter; 
	public function delStart($title)
	{
	   
	   $ret = ''; 
	   if (!empty(OPCrenderer::$delStarted)) 
	   {
	     $ret .= $this->delEnd(); 
	   }
	   if (!isset(OPCrenderer::$num_delimiter)) OPCrenderer::$num_delimiter = 0; 
	   OPCrenderer::$num_delimiter++; 
	   OPCrenderer::$delStarted = true; 
	   $data = array('title' => $title, 'num'=>OPCrenderer::$num_delimiter); 
	   return $ret.$this->fetch($this, 'delimiter_start', $data, false); 
	}
	
	public function delEnd()
	{
	  if (empty(OPCrenderer::$delStarted)) return ''; 
	  OPCrenderer::$delStarted = false;  
	  $data = array(); 
	  return $this->fetch($this, 'delimiter_end', $data, false); 
	}
	
	private static function getSelected($cart_key, $custom_id)
	{
	   $a1 = explode('::', $cart_key); 
	   
	   if (count($a1) <= 1) return ''; 
	   $a2 = explode(';', $a1[1]); 
	   
	   if (count($a2) <= 1) return ''; 
	   foreach ($a2 as $val)
	    {
		  $a3 = explode(':', $val); 
		  if (count($a3) <= 1) return ''; 
		  if ($a3[1] == $custom_id) return $a3[0]; 
		}
	  return ''; 
	}
	
	public static function getCustomFields($virtuemart_product_id, $cart_key='', $quantity=1, $cart=null)
	{
	
	  $html = ''; 
	  $cart_key_hash = md5($cart_key); 
	  require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 	
	  require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 	
  	  $ajaxifyCart = OPCconfig::get('ajaxify_cart', false); 
	   $product_model = OPCmini::getModel('product');
	   
	   
	   
	   if (defined('VM_VERSION') && (VM_VERSION >= 3))
	   {
		   /*
		   if (isset($cart->products[$cart_key]))
		   {
			   $product =& $cart->products[$cart_key]; 
		   }
		   */
		   {
		     $product = $product_model->getProduct($virtuemart_product_id,TRUE,TRUE,TRUE,$quantity, 0);
		   }
		   
		   
	   }
	   else
	   {
	    $product = $product_model->getProduct($virtuemart_product_id,TRUE,TRUE,TRUE,$quantity, true);
	   }
	   
	   
	    //clear any cached data: 
	   foreach ($product->customfields as $kk=>$cf) {
		   
		   if (!empty($cf->display)) {
			   $product->customfields[$kk]->display = null; 
		   }
	   }
	   
	   $customfieldModel = OPCmini::getModel ('Customfields');
	   if (defined('VM_VERSION') && (VM_VERSION >= 3))
	   {
		    if (!method_exists($customfieldModel, 'getCustomEmbeddedProductCustomFields')) return $html; 
			
			
			if (empty($product->customfields))
			{
ob_start(); 
		     $product->customfields = $customfieldModel->getCustomEmbeddedProductCustomFields ($product->allIds);
$zzz = ob_get_clean(); 
		    }
			
		 
		   if (empty($product->customfields))
		   {
			   return ''; 
		   }
		   		   

		   ob_start(); 
		   $customfieldModel->displayProductCustomfieldFE($product, $product->customfields); 
			$zzz = ob_get_clean(); 
		   
		
		   
		   
	   }
	   else
	   {
		   
		   
	     if (!method_exists($customfieldModel, 'getproductCustomslist')) return $html; 
	     $product->customfields = $customfieldModel->getproductCustomslist ($virtuemart_product_id);
		 
		 if (empty($product->customfields) and !empty($product->product_parent_id)) {
						//$product->customfields = $this->productCustomsfieldsClone($product->product_parent_id,true) ;
				$product->customfields = $customfieldModel->getproductCustomslist ($product->product_parent_id, $virtuemart_product_id);
				$product->customfields_fromParent = TRUE;
		}
	   
	    $customfieldModel->getProductCustomsField($product); 
		//
		$product->customfields = $customfieldModel->getProductCustomsFieldCart ($product);
		 
		 
	   }

		 OPCrenderer::filterCustomFields($product);    
			
			
		
		
		
		
		$html .= '<div class="opc_editable_attributes">'; 
		
		
		JHTMLOPC::script('opcattributes.js', 'components/com_onepage/assets/js/'); 
		if (empty($ajaxifyCart))
{
		$html .= '<form method="post" id="atr_'.$cart_key_hash.'" class="opccartproduct opc-recalculate" action="'.JRoute::_('index.php').'"> '; 
}
else
{
	$html .= '<div class="opccartproduct opc-recalculate" id="atr_'.$cart_key_hash.'"> '; 
}

$html .= '
		<input name="quantity[0]" class=".quantity-input opc_atr_'.$cart_key_hash.'" type="hidden" value="'.$quantity.'">
		
		<input class="opc_atr_'.$cart_key_hash.'" name="virtuemart_product_id[0]" class="opc_product" type="hidden" value="'.$virtuemart_product_id.'">
		
		<input class="opc_atr_'.$cart_key_hash.'" name="cart_key" value="'.$cart_key.'" type="hidden" />
		
		<input class="opc_atr_'.$cart_key_hash.'" name="update_attribute_s" value="0" type="hidden" id="atr_switch_'.$cart_key_hash.'" />
		
		<input class="opc_atr_'.$cart_key_hash.'" name="cart_key_hash" value="'.$cart_key_hash.'" type="hidden" />
		<input class="opc_atr_'.$cart_key_hash.'" name="cart_virtuemart_product_id" value="'.$cart_key.'" type="hidden" />
		<div class="product-fields">'; 
	    
		/*
		
		*/
		 
	    $custom_title = null;
		//foreach ($product->customfieldsSorted as $positions=>$val)
	    foreach ($product->customfields as $field) 
		{
			$rep = array(); 
			$with = array(); 
			if (defined('VM_VERSION') && (VM_VERSION >= 3))
			{
				if ((empty($field->is_input)) && ($field->field_type != 'A')) continue; 
				
				//if ($field->field_type === 'A') continue; 
				
				if ((($field->is_input)) && ($field->field_type === 'A') ) {
					$parent_id = $product->product_parent_id; 
					if (!empty($parent_id)) {
					$db = JFactory::getDBO(); 
					$q = 'select `virtuemart_product_id` from #__virtuemart_products where `product_parent_id` = '.(int)$parent_id.' and `published` = 1'; 
					$db->setQuery($q); 
					$res = $db->loadAssocList(); 
					foreach ($res as $r) {
						
						$pid = $r['virtuemart_product_id']; 
						
							$p = $product_model->getProduct($pid,TRUE,TRUE,TRUE,$quantity, 0);
							if (!empty($p->product_s_desc)) {
							 $multi_variant_name = $p->product_s_desc;
							}
							else {
							 $multi_variant_name = $p->product_name; 
							}
						
						$multi_variant_data = array('product_id'=>$pid, 'quantity'=>$quantity, 'cart_key'=>$cart_key, 'cart_key_hash'=>$cart_key_hash); 
						$rep[] = '>'.htmlentities($multi_variant_name).'<'; 
						$with[] = ' data-multivariant="'.htmlentities(json_encode($multi_variant_data)).'"'.'>'.htmlentities($multi_variant_name).'<';; 
					}
					}
					
				}
				/*
				if ((($field->is_input)) && ($field->field_type === 'S') ) {
					
					
					$parent_id = $product->product_parent_id; 
					if (!empty($parent_id)) {
					$with = array(); 
					foreach ($field->options as $f_id => $r) {
						
						$pid = $product->virtuemart_product_id; 
						
							
						if (!empty($field->customfield_value)) {
						  $multi_variant_name = JText::_($field->customfield_value); 
						}
						else {
							$multi_variant_name = $product->product_name; 
						}
						$multi_variant_data = array('product_id'=>$pid, 'quantity'=>$quantity, 'cart_key'=>$cart_key, 'cart_key_hash'=>$cart_key_hash); 
						$rep[] = ' value="'.htmlentities($f_id).'"'; 
						$with[] = ' data-multivariant="'.htmlentities(json_encode($multi_variant_data)).'" value="'.htmlentities($f_id).'"'; 
						
						
					}
					}
					
				}
				*/
				
			}
	    	if ( $field->is_hidden ) {
	    		continue;
			}
			if (!empty($field->display))
			{
			
			
			if ($field->field_type === 'C') {
				
				
				$with = array(); 
				foreach ($field->options as $pid => $na) {
					$multi_variant_name = reset($na); 
					$multi_variant_data = array('product_id'=>$pid, 'quantity'=>$quantity, 'cart_key'=>$cart_key, 'cart_key_hash'=>$cart_key_hash); 
					$rep[] = '"'.htmlentities($multi_variant_name).'"'; 
					$with[] = '"'.htmlentities($multi_variant_name).'"'.' data-multivariant="'.htmlentities(json_encode($multi_variant_data)).'"'; 
				}
				
			}
			
			$rep[] = 'vm-chzn-select'; 
			$rep[] = 'cvselection'; 
			$rep[] = 'no-vm-bind'; 
			
			$with[] = ' opc_atr '; 
			$with[] = ' opc_atr '; 
			$with[] = ' opc_atr '; 
			/*
			static $x; 
			$x++; 
			if ($x > 1) {
		var_dump($rep); 
		var_dump($with); 
		die(); 
			}
			*/
			$field->display = str_replace($rep, $with, $field->display); 
			
			$html .= '<div class="product-field product-field-type-'.$field->field_type.'">'; 
		    if ($field->custom_title != $custom_title && $field->show_title) { 
			    $html .= '<div class="product-fields-title" >'.JText::_($field->custom_title).'</div>'; 
			    
			    if ($field->custom_tip)
				$html .= JHTML::tooltip($field->custom_tip, JText::_($field->custom_title), 'tooltip.png');
			}
			$display = $field->display;
			if (stripos($display, 'class="')===false)
			{
			 $display = str_replace('<select ', '<select class="opc_atr_'.$cart_key_hash.'" onchange="OPCCart.setproducttype2(\'atr_'.$cart_key_hash.'\')" ', $display); 
			 
			 
			  $display = str_replace('<input ', '<input class="opc_atr_'.$cart_key_hash.'" onchange="OPCCart.setproducttype2(\'atr_'.$cart_key_hash.'\')" ', $display);
			}
			else
			{
			 $display = str_replace('<select ', '<select  onchange="OPCCart.setproducttype2(\'atr_'.$cart_key_hash.'\')" ', $display); 	
			 
			  $display = str_replace('<input ', '<input  onchange="OPCCart.setproducttype2(\'atr_'.$cart_key_hash.'\')" ', $display); 	
			 
			$display = str_replace('class="', 'class="opc_atr_'.$cart_key_hash.' ', $display); 
			}
			
			$display = str_replace('name="', 'name="opc_atr_'.$cart_key_hash.'_', $display); 
			
			
			
			
			
			$display = str_replace(JText::_('COM_VIRTUEMART_CART_PRICE_FREE'), '', $display); 
			if ((!defined('VM_VERSION') || (VM_VERSION < 3)))
			{
			$selected = self::getSelected($cart_key, $field->virtuemart_custom_id); 

			$display = str_replace('value="'.$selected.'"', ' checked="checked" selected="selected" value="'.$selected.'" ', $display); 
			}
			else
			{
				$selected = $field->virtuemart_customfield_id;
				
				
				if (isset($cart->cartProductsData[$cart_key]['customProductData'][$field->virtuemart_custom_id]))
				{
					$selected = $cart->cartProductsData[$cart_key]['customProductData'][$field->virtuemart_custom_id]; 
					
					if (is_array($selected))
					{
						$z = reset($selected); 
						if (is_array($z))
						{
							$z = reset($z); 
						}
						if (!is_array($z))
						{
							$display = str_replace('value=""', 'value="'.$z.'"', $display); 
							$display = str_replace('value="0"', 'value="'.$z.'"', $display); 
							$display = str_replace('value="'.$z.'"', ' checked="checked" selected="selected" value="'.$z.'" ', $display); 
						}
						$display = str_replace('<script', '<removed', $display); 
						$display = str_replace('</script', '</removed', $display); 
						
						
						
					}
					else
					{
					$display = str_replace('value="'.$selected.'"', ' checked="checked" selected="selected" value="'.$selected.'" ', $display); 
					}
				} 
				
			}
			
			
			$html .= '<div class="product-field-display" style="clear:both;">'.$display.'</div>'; 
			
			if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
			 if (!empty($field->custom_desc))
			 $html .= '<span class="product-field-desc">'.JText::_($field->custom_desc).'</span>'; 
			}
			else
			{
			 if (!empty($field->custom_field_desc))
			 $html .= '<span class="product-field-desc">'.JText::_($field->custom_field_desc).'</span>'; 	
			}
			
			
			
			$html .= '</div>'; 
		    
		    $custom_title = $field->custom_title;
			}
	    }
	   $html .= '</div>'; 

	  
	   
	    if (empty($ajaxifyCart)) 
	    $html .= '</form>'; 
		else
		$html .= '</div>'; 
		
		$html .= '</div>';
		
		
	return $html; 	
    }
	public static function filterCustomFields(&$prow) {
		if (empty($prow->customfields)) return; 
		if (!is_array($prow->customfields)) return; 
		
			$all_ids = array(); 
				$disabled = array(); 
				$overrided = array(); 
				
				 foreach ($prow->customfields as $k=>$pr) {
					  
					 if (!empty($pr->disabler)) {
						 $disabled[] = (int)$pr->disabler; 
						 unset($prow->customfields[$k]); 
						 continue; 
					 }
					 if (!empty($pr->override)) {
						 $override_id = (int)$pr->override;
						 $overrided[$override_id] = $override_id;
					 }
					 
					  $id = (int)$pr->virtuemart_customfield_id; 
					 if (empty($all_ids[$id])) $all_ids[$id] = 0; 
					 $all_ids[$id]++;
					
					
					 
				 }
				 if (!empty($disabled))
				 foreach ($prow->customfields as $k=>$prZ) {
						foreach ($disabled as $did) {
							if ($did == $prZ->virtuemart_customfield_id) {
								unset($prow->customfields[$k]); 
							}
						}
				 }
		
			//fix override bug in VM3.2.X:
			//reorder:
			if (!empty($prow->customfields)) {
			foreach ($prow->customfields as $key=>$field) {
				    $id = (int)$field->virtuemart_customfield_id; 
					if (isset($overrided[$id])) {
						unset($prow->customfields[$key]); 
						continue; 
					}
					if ($all_ids[$id] > 1) {
						unset($prow->customfields[$key]); 
						$all_ids[$id]--; 
						continue; 
					}
					
					
			}
			}
			
		
	}
	
	
	public static function renderModuleByPosition($position, $params=null)
	{
	    jimport( 'joomla.application.module.helper' );
		$searchmodules = JModuleHelper::getModules($position);
		$output = ''; 
               foreach ($searchmodules as $searchmodule)
                {
				    $params = new JRegistry;
                    $params->loadString($searchmodule->params);
					$attribs = array(); 
					$attribs['style'] = 'xhtml';
                    $html = JModuleHelper::renderModule($searchmodule, $attribs);
					
					$hash = md5($html); 
					
					if (!empty($html)) {
					$form = 'form_'.$hash; 
					$search  = array('<form ', '</form', '<FORM ', '</FORM', ' form=', '<input', '<select', '<INPUT', '<SELECT', '<textarea', '<TEXTAREA', '<button', '<BUTTON', '<datalist', '<keygen', '<output', '<fieldset', '<label', '<legend', 															  '</input', '</select', '</INPUT', '</SELECT', '</textarea', '</TEXTAREA', '</button', '</BUTTON', '</datalist', '</keygen', '</output', '</fieldset', '</label', '</legend', 'id="adminForm"'); 
					//$replace = array('<disabledform', '</disabledform', '<DISABLEDFORM', '</DISABLEDFORM', ' disabledform=', '<input form="'.$form.'" ', '<select form="'.$form.'" ', '<INPUT form="'.$form.'" ', '<SELECT form="'.$form.'" ', '<textarea form="'.$form.'" ', '<TEXTAREA form="'.$form.'" ', '<button form="'.$form.'" ', '<BUTTON form="'.$form.'" ', '<datalist form="'.$form.'" ', '<keygen form="'.$form.'" ', '<output form="'.$form.'" ', '<fieldset form="'.$form.'" ', '<label form="'.$form.'" ', '<legend form="'.$form.'" ', 
					$replace = array('<disabledform data-ref="'.$form.'" ', '</disabledform', '<DISABLEDFORM data-ref="'.$form.'" ', '</DISABLEDFORM', ' disabledform=', '<input form="'.$form.'" ', '<select form="'.$form.'" ', '<INPUT form="'.$form.'" ', '<SELECT form="'.$form.'" ', '<textarea form="'.$form.'" ', '<TEXTAREA form="'.$form.'" ', '<button form="'.$form.'" ', '<BUTTON form="'.$form.'" ', '<datalist form="'.$form.'" ', '<keygen form="'.$form.'" ', '<output form="'.$form.'" ', '<fieldset form="'.$form.'" ', '<label form="'.$form.'" ', '<legend form="'.$form.'" ', '</input', '</select', '</INPUT', '</SELECT', '</textarea', '</TEXTAREA', '</button', '</BUTTON', '</datalist', '</keygen', '</output', '</fieldset', '</label', '</legend', 'was_id="adminForm"'); 
					$html = str_replace($search, $replace, $html); 
					
					if (empty(self::$extrahtml)) self::$extrahtml = ''; 	
					self::$extrahtml .= '<form id="'.$form.'" name="'.$form.'" action="#" method="post"><input type="hidden" name="opc_moved_form" value="1" /></form>'; 
					}
                    $output = $html; 
					
                }
		

				
				
	   return $output; 
	}
	
	public static function renderModuleByName($name, $params=null)
	{
	    jimport( 'joomla.application.module.helper' );

	    $document   = JFactory::getDocument();
		$renderer   = $document->loadRenderer('module');
		if (empty($params))
		$params   = array();
		$module = JModuleHelper::getModule($name); 
		return $renderer->render($module, $params);

	}
	
	public function op_show_image(&$image, $extra, $width, $height, $type)
	{
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
	  return OPCimage::op_show_image($image, $extra, $width, $height, $type);

	  
	}
	
	public static $selected_template; 
	public static function getSelectedTemplate()
	{
	 if (defined('OPC_FOR_HIKA_LOADED')) {
		 $view = JRequest::getVar('view', ''); 
		 if ($view !== 'opc') {
		   return OPCHikaRenderer::getSelectedTemplate(); 
		 }
	 }
		
	 require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 	
	 return OPCmini::getSelectedTemplate(); 
	  
	}
	
	function debugTheme($file)
	{
		static $d; 
		if (isset($d[$file])) 
		{
			echo '</fieldset>'; 
			unset($d[$file]); 
		}
		else {
			$d[$file] = 1; 
			$file = str_replace(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes', '', $file); 
			$file = str_replace(DIRECTORY_SEPARATOR, ' '.DIRECTORY_SEPARATOR.' ', $file); 
			
			echo '<fieldset class="opc_debug"><legend class="opc_debug">'.$file.'</legend>'; 
		}
		
		if (!defined('OPCDEBUGCSS'))
		{
			$css = '
			 #vmMainPageOPC fieldset.opc_debug {
			  border: 3px solid black !important; 
			 }
			 #vmMainPageOPC legend.opc_debug {
			  font-size: 10px !important; 
			 }
			 
			';
				JFactory::getDocument()->addStyleDeclaration($css); 
				define('OPCDEBUGCSS', 1); 
		}
		
	}
	
	public static function registerVar($name, $value)
	{
		if (empty(self::$globalVars)) self::$globalVars = array(); 
		self::$globalVars[$name] = $value; 
	}
	
	public static $globalVars; 
	public static function addModules(&$ref, &$positions) { 
	  
	  $opc_debug_theme = OPCconfig::get('opc_debug_theme', false); 
	  foreach ($positions as $position_name => $html) { 
	      
		  $ignore = array('op_onclick'); 
		  if (in_array($position_name, $ignore)) continue; 
		  
		  $above = self::renderModuleByPosition('opc_'.$position_name.'_above'); 

		  if (!empty($opc_debug_theme)) {
		   $above = '<fieldset class="opc_debug"><legend class="opc_debug">Module Position:'.'opc_'.$position_name.'_above'.'</legend>'.$above.'<fieldset>'; 
		  }
		  
		  
		  $under = self::renderModuleByPosition('opc_'.$position_name.'_under'); 
		  
		  if (!empty($opc_debug_theme)) {
		   $under = '<fieldset class="opc_debug"><legend class="opc_debug">Module Position:'.'opc_'.$position_name.'_under'.'</legend>'.$under.'<fieldset>'; 
		  
		  
		  
		  }
		  
	      if (is_string($html)) {
		     
			  if (!empty($opc_debug_theme)) {
			     $html = '<fieldset class="opc_debug"><legend class="opc_debug">Module Position:'.'opc_'.$position_name.'</legend>'.$html.'<fieldset>'; 
			  }
			 
			 $positions[$position_name] = $above.$html.$under; 
			 
		  }
		  else
		  if (is_array($html)) {
			  $new = array(); 
			  if (!empty($above)) 
			  $new['-0'] = $above; 
			  foreach ($html as $k=>$v) {
			    $new[$k] = $v; 
			  }
			  if (!empty($under)) 
			  $new[] = $under; 
			  
			  $positions[$position_name] = $new; 
			  
			  }
	  
	  }
	  
	  
	  $checkbox_products_position = OPCconfig::get('checkbox_products_position', 'checkbox_products'); 
	  $checkbox_products = OPCconfig::get('checkbox_products', array()); 
	  
	  if (!empty($checkbox_products_position) && (isset($positions[$checkbox_products_position])))
				{
					
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');  
	  $OPCloader = new OPCloader; 
		
		
	  $checkbox_products_html = $OPCloader->getCheckBoxProducts($ref); 
					
	  if ((!empty($checkbox_products)) && (!empty($checkbox_products_html)))
			{
				
					if (is_string($positions[$checkbox_products_position])) {
						if ($checkbox_products_position === 'checkbox_products') {
							$positions[$checkbox_products_position] = $checkbox_products_html; 
						}
						else {
						 $positions[$checkbox_products_position] .= $checkbox_products_html; 
						}
						
					}
					else
						if (is_array($positions[$checkbox_products_position])) $positions[$checkbox_products_position][] = $checkbox_products_html; 
					
					
					
			}
				}
				
				
			
			$opc_estimator_position = OPCconfig::get('opc_estimator_position', 'shipping_estimator');
			
			
			
			
				if (isset($positions[$opc_estimator_position]))
				{
					if (!empty($shipping_estimator))
					{
						
						 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');  
						$OPCloader = new OPCloader; 
						
					$shipping_estimator = $OPCloader->getShippingEstimator($ref); 
					
					
					
					if (is_string($positions[$opc_estimator_position])) $positions[$opc_estimator_position] .= $shipping_estimator; 
					else
						if (is_array($positions[$opc_estimator_position])) $positions[$opc_estimator_position][] = $shipping_estimator; 
					
					}
					
				}
			
	  
	  
	  
	} 
	
	public static function setOPCConfigDefaults() {
		static $wasRun; 
		if (!empty($wasRun)) return; 
		
		if (empty(self::$globalVars)) self::$globalVars = array(); 
		
		self::$globalVars['no_jscheck'] = true; 
		self::$globalVars['no_continue_link'] = OPCconfig::get('no_continue_link', false); 
		self::$globalVars['no_login_in_template'] = OPCconfig::get('no_login_in_template', false); 
		self::$globalVars['force_hide_payment'] = OPCconfig::get('force_hide_payment', false); 
		self::$globalVars['show_full_tos'] = OPCconfig::get('show_full_tos', false); 
		self::$globalVars['tos_required'] = OPCconfig::get('tos_required', false); 
		self::$globalVars['agree_checked'] = OPCconfig::get('agree_checked', false); 
		
		
		self::$globalVars['custom_rendering_fields'] = OPCconfig::get('custom_rendering_fields', array());
		if (class_exists('OPCloader')) {
			self::$globalVars['custom_rendering_fields'] = OPCloader::getCustomRenderedFields();  
		}
		self::$globalVars['opc_ajax_fields'] =  OPCconfig::get('opc_ajax_fields', array());
		self::$globalVars['render_as_hidden'] = OPCconfig::get('render_as_hidden', array());
		self::$globalVars['render_in_third_address'] = OPCconfig::get('render_in_third_address', array());
		self::$globalVars['business_obligatory_fields'] = OPCconfig::get('business_obligatory_fields', array());
		self::$globalVars['shipping_obligatory_fields'] = OPCconfig::get('shipping_obligatory_fields', array());
		self::$globalVars['business_fields2'] = OPCconfig::get('business_fields2', array());
		self::$globalVars['business_fields'] = OPCconfig::get('business_fields', array());
		//tos_required
		
		
		
	}
	
	
	function fetch(&$ref, $template, $vars, $new='')
 {
	 
	 self::setOPCConfigDefaults(); 
	 
	 $payment_inside_basket = OPCconfig::get('payment_inside_basket', false); 
	 $shipping_inside_basket = OPCconfig::get('shipping_inside_basket', false); 
	 
  
   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php');
  
   if (empty(self::$globalVars)) self::$globalVars = array(); 
	
   foreach ($vars as $k=>$v)
   {
	   self::$globalVars[$k] = $v; 
   }
   if (!empty(self::$globalVars))
   {
	   foreach (self::$globalVars as $k=>$v)
	   {
		   $vars[$k] = $v; 
	   }
	   
   }
	
	if (!isset($step)) { 
	  $step = 0; 
	}
    if (empty($vars['step'])) {
	  $step = JRequest::getInt('step', 0); 
	  $vars['step'] = $step; 
	}
	if (!isset($vars['step'])) $vars['step'] = 0; 
	
	
  
      
   
   
   $selected_template = self::getSelectedTemplate(); 
  
  if ($template === 'basket.html') {
	  
	  OPCloader::opcDebug($selected_template, 'selected_template'); 
  }
  
   
    $op_shipto_opened = OPCloader::getShipToOpened(); 
	if (isset($vars['cart'])) $cart = $vars['cart']; 
	else
	if (isset($this->cart)) $cart = $this->cart; 
	else
	$cart = VirtuemartCart::getCart(); 
	if (isset($vars['default_shipping_country'])) {
		$default_shipping_country  = $vars['default_shipping_country']; 
	}
	else {
		$default_shipping_country = OPCloader::getDefaultCountry($cart, false); 
	}
   
   
   OPCloader::setRegType(); 
   
   if ($template === 'index') {
	  $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.$template.'.php'; 
	  if (file_exists($f)) {
		    ob_start(); 
			extract($vars); 
			include($f); 
			return ob_get_clean(); 
	  }
	  else {
		  return ''; 
	  }
   }
   
   
   if (VM_REGISTRATION_TYPE != 'OPTIONAL_REGISTRATION')
   $op_create_account_unchecked = false; 
   
   if (!empty($ref->cart))
   $cart = $ref->cart; 
   else
   $cart = VirtueMartCart::getCart(false); 
   
   $op_disable_shipping = OPCloader::getShippingEnabled();
   $no_shipping = $op_disable_shipping;
   
   
   
   $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.$template.'.php'; 
   
	if (($template === 'list_user_fields_registration.tpl') || (($template === 'list_user_fields_shipping.tpl')))
	{
		if (!file_exists($f)) {
		  $template = 'list_user_fields.tpl'; 
		  $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.$template.'.php'; 
		}
	}

   

   if (file_exists($f))
    {
	
	  ob_start(); 
	  extract($vars); 
	  
	  
	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  include($f); 
	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  $ret = ob_get_clean(); 
	  $useSSL = (int)VmConfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
	  return $ret; 
	}
   else
    {
	  if (!empty($new))
	   {
	     $ly = $ref->layoutName; 
		 if (empty($ly)) $ly = 'default'; 
		 if (empty($new)) $new = 'prices'; 
	     if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$ly.'_'.$new.'.php'))
		  {
		    ob_start(); 
			$z = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$ly.'_'.$new.'.php';
			if (!empty($opc_debug_theme)) self::debugTheme($z); 
			include($z); 
			if (!empty($opc_debug_theme)) self::debugTheme($z); 
			$ret = ob_get_clean(); 
			$useSSL = (int)VmConfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
			return $ret; 
		  }
	     
	   }
	}
	
	
	$f2 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'extra'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'.php'; 
	if (file_exists($f2))
	 {
	 
	     ob_start(); 
	     extract($vars); 
		if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	     include($f2); 
		 if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	     $ret = ob_get_clean(); 
		 $useSSL = (int)VmConfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
	     return $ret; 
	 }
	
 }
 
 public function css($css_file)
 {
	  $selected_template = self::getSelectedTemplate(); 
	  
	 $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.$css_file.'.css'; 
	 if (file_exists($f))
	 {
		 JHTMLOPC::stylesheet($css_file.'.css', 'components/com_onepage/themes/'.$selected_template.'/'); 
	 }
	 JHTMLOPC::stylesheet($css_file.'.css', 'components/com_onepage/themes/extra/default/'); 
	 
	 
	 
	 
 }
 
 public function fetchVirtuemart($name, $view='cart', $layout='default')
 {
	 
	    if (empty(self::$globalVars)) self::$globalVars = array(); 
	
   $vars = array(); 
   if (!empty(self::$globalVars))
   {
	   foreach (self::$globalVars as $k=>$v)
	   {
		   $vars[$k] = $v; 
	   }
	   
   }

	 
     $template = VmConfig::get( 'vmtemplate', 'default' );
	 if (empty($template) || (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template))) {
			$app = JFactory::getApplication(); 
			$template = $app->getTemplate(); 
		}
	 
	 include(JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_onepage".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR."overrides".DIRECTORY_SEPARATOR."onepage.cfg.php");
	 
   if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.$layout.'_'.$name.'.php'))
    {
	 
	  ob_start(); 
	  extract($vars); 
	  $x = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.$layout.'_'.$name.'.php';
	  if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	  include($x);
	  if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	  $ret = ob_get_clean(); 
	  
	  return $ret; 
	}
   else
    {
	  
	   
	    
	     if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$layout.'_'.$name.'.php'))
		  {
		    ob_start(); 
			$x = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$layout.'_'.$name.'.php';
			 if (!empty($opc_debug_theme)) self::debugTheme($f2); 
			include($x); 
			 if (!empty($opc_debug_theme)) self::debugTheme($f2); 
			$ret = ob_get_clean(); 
			return $ret; 
		  }
	     
	   
	}
 }
 
 public function fetchBasket(&$ref, $template, $vars, $new='')
 {
	
  if (empty(self::$globalVars)) self::$globalVars = array(); 
	
   foreach ($vars as $k=>$v)
   {
	   self::$globalVars[$k] = $v; 
   }
   if (!empty(self::$globalVars))
   {
	   foreach (self::$globalVars as $k=>$v)
	   {
		   $vars[$k] = $v; 
	   }
	   
   }
   
   $payment_inside_basket = OPCconfig::get('payment_inside_basket', false); 
   $shipping_inside_basket = OPCconfig::get('shipping_inside_basket', false); 
   
   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php');   

   $op_disable_shipping = OPCloader::getShippingEnabled();
   $no_shipping = $op_disable_shipping;
   $instance = OPCrenderer::getInstance(); 
   return $instance->fetchVirtuemart('pricelist', 'cart', 'default'); 
 
 
 }
 public function loadTemplate($theme=NULL, $z=true)
 {
  return ""; 
  $instance = OPCrenderer::getInstance(); 
  return $instance->fetchVirtuemart($theme, 'cart', 'default'); 
 }
 
  public function currencyDisplay($floatn, $virtuemart_currency_id=0, $convert=true) 
  {
	  if (!empty($this->cart)) 
	  {
		  $cart = $this->cart; 
	  }
	  
	  
	  if (!class_exists('CurrencyDisplay'))
	require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
   $currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);
	  
	  if ($convert) {
	  if (empty($virtuemart_currency_id)) {
	    $virtuemart_currency_id = OPCloader::getCurrency($cart); 
	  }
		$floatn = $currencyDisplay->convertCurrencyTo( $virtuemart_currency_id, $floatn,false);
		$floatn = (float)$floatn; 
		if (is_nan($floatn)) $floatn = 0; 
	  }
	  $arr = array('ocp_salesPrice'=>$floatn); 
	  $display = $currencyDisplay->createPriceDiv('ocp_salesPrice','', $arr,false,false, 1);
	  $display = str_replace('class="', 'class="opc_', $display); 
	  return $display; 
	  
  }
	
}