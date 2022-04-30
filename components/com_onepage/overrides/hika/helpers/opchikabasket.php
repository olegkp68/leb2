<?php
defined('_JEXEC') or die('Restricted access');

class OPCHikaBasket {
	public static function getBasket($withwrapper=true, &$op_coupon='', $shipping='', $payment='', $isexpress=false, $ismulti=0) {
		$ref = OPChikaRef::getInstance(); 
		if (empty($ref->cart)) {
		//	$ref->cart = OPChikaRef::getCart(); 
		}
		$options = OPCHikaParams::get('cart'); 
		
		$url_itemid = ''; 
		
		$product_rows = array(); 
		$extra_html = ''; 
		
		$no_shipping = OPChikashipping::getShippingEnabled(); 
		
		/*unused variables*/
		$coupon_display_before = ''; 
			  $shipping_select = ''; 
			  $payment_select = ''; 
			  $discount_after = ''; 
			  $tax_display = ''; 
			  $discount_before = ''; 
			  $coupon_display_before = ''; 
			  $opc_show_weight_display = ''; 
			  $ismulti = 0; 
			  $coupon_display = ''; 
			  $group = $ref->config->get('group_options',0);
		/*unused variables END*/
		
			$with_tax = true; 
			$taxes = round($ref->cart->full_total->prices[0]->price_value_with_tax - $ref->cart->full_total->prices[0]->price_value, $ref->currencyClass->getRounding($ref->cart->full_total->prices[0]->price_currency_id));
					
					if(empty($taxes) || (empty($options['price_with_tax']))) {
					   $with_tax = false; 
					}
			
			
			if (empty($ref->cart->products)) {
				$ref->cart = OPChikaRef::getCart(); 
				if (empty($ref->cart->products)) {
					return ''; 
				}
				
			}
					
					
					
		
		foreach ($ref->cart->products as $k=>$product) {
			
			$hikaproduct = array(); 
			$product->cart_product_quantity = floatval($product->cart_product_quantity); 
			
			if(empty($product->cart_product_quantity)) {
				
				continue;
			}
			if($group && !empty($product->cart_product_option_parent_id)) {
				
			continue;
			}
			$ref->productClass->addAlias($product);
			$hikaproduct['product_full_image'] = ''; 
			if (!empty($product->images)) {
			  $image = reset($product->images);
			  if (isset($image->file_path)) {
			  $hikaproduct['product_full_image'] = $ref->imageHelper->uploadFolder . $image->file_path;
			 }
			}
			
			//$hikaproduct['product_price'] = strip_tags($ref->cartView->getDisplayProductPrice($product, true));
			
			
			
			$unit_price = $product->prices[0]->unit_price;
			
			if (($with_tax) && (isset($unit_price->price_value_with_tax))) { 
			  $hikaproduct['product_price'] = $ref->currencyHelper->format($unit_price->price_value_with_tax, $unit_price->price_currency_id);
			}
			else {
				$hikaproduct['product_price'] = $ref->currencyHelper->format($unit_price->price_value, $unit_price->price_currency_id);
			}
			
		    $hikaproduct['product_quantity'] = $product->cart_product_quantity; 
			$hikaproduct['product_link'] = hikashop_contentLink('product&task=show&cid=' . $product->product_id . '&name=' . $product->alias . $url_itemid, $product); 
			$hikaproduct['product_name'] = $product->product_name;
			$hikaproduct['product_sku'] = $product->product_code; 
			$hikaproduct['product_stock_label'] = ''; 
			$hikaproduct['product_attributes'] = ''; 
			$hikaproduct['info'] = $product; 
			$hikaproduct['product'] = $product; 
			$hikaproduct['product_id'] = $hikaproduct['virtuemart_product_id'] = $product->product_id; 
			
			//$hikaproduct['subtotal'] = $ref->cartView->getDisplayProductPrice($product, false);
			
			if (($with_tax) && (isset($product->prices[0]->price_value_with_tax))) { 
			  $product_subtotal = $product->prices[0]->price_value_with_tax;
			  
			}
			else {
				$product_subtotal = $product->prices[0]->price_value;
				
			}
			
			
			
			
			
			
			$hikaproduct['product_subtotal'] = $ref->currencyHelper->format($product_subtotal, $product->prices[0]->price_currency_id);
			
			$hikaproduct['subtotal'] = $hikaproduct['product_subtotal'];
		
			
			
			$useSSL = OPChikaconfig::get('useSSL', 0); 
			$action_url = '#'; 
			
			$productCopy = $product; 
			$productCopy->cart_item_id = $product->cart_product_id; 
			$productCopy->quantity = $product->cart_product_quantity;
			
			
			 $v = array('product'=>$product, 
			   'action_url'=>$action_url, 
			   'use_ssl'=>$useSSL, 
			   'useSSL'=>$useSSL,
			   'product' => $productCopy,
			   );
			
			$hasSteps = self::checkSteps($product, $v); 
			  $suffix = ''; 
			  if ($hasSteps) {
			    $suffix = '_steps'; 
			  }
			
			/*legacy update form*/
			$update_form_html = OPChikarenderer::fetch('update_form_ajax'.$suffix.'.tpl', $v); 
			$delete_form_html = OPChikarenderer::fetch('delete_form_ajax.tpl', $v); 
				   $xz1 = stripos($update_form_html, 'onclick='); 
				   $xz2 = stripos($delete_form_html, 'onclick='); 
				   if ($xz1 === false)
				   {
				    $update_form_html = str_replace('href=', 'onclick="return Hikaonepage.updateProduct(this);" href=', $update_form_html);
				   }
				   if ($xz2 === false)
				   {
				    $delete_form_html = str_replace('href=', 'onclick="return Hikaonepage.deleteProduct(this);" href=', $delete_form_html); 
				   }
			  
			   $update_form_html = str_replace('name="quantity"', 'name="quantity['.$product->cart_product_id.']"', $update_form_html); 			   
			   $update_form_html = str_replace('value="update"', 'value="updatecart"', $update_form_html); 
			   
			  
			
			/*end legacy update form*/
			
			
			
			$hikaproduct['update_form'] = $update_form_html; 
			$hikaproduct['delete_form'] = $delete_form_html; 
			
			$product_rows[] = $hikaproduct; 
			
			 $span_subtotal = $hikaproduct['subtotal']; 
			  
			  //break; 
			  $extra_html .= '<div class="opccf_confirm_row">
			  <span class="opccf_quantity">'.$product->cart_product_quantity.'</span>
			  <span class="opccf_times">&nbsp;x&nbsp;</span>
			  <span class="opccf_productname">'.$product->product_name.'</span>
			  <span class="opccf_productsubtotal">'.$span_subtotal.'</span>
			  </div>'; 
			  
			 
		}
				
			
				
				if(!empty($ref->cart->coupon)) {
					if (empty($with_tax)) {
					  $coupon_display =  $ref->currencyClass->format($ref->cart->coupon->discount_value_without_tax * -1, $ref->cart->coupon->discount_currency_id);
					}
					else {
					$coupon_display = $ref->currencyClass->format($ref->cart->coupon->discount_value * -1, $ref->cart->coupon->discount_currency_id);
					}
				}
				else {
				 $coupon_display = ''; 	
				}
				
				
				if (!empty($with_tax)) {
				$subtotal_display = $ref->currencyClass->format($ref->cart->full_total->prices[0]->price_value_with_tax,$ref->cart->full_total->prices[0]->price_currency_id);
				}
				else {
				$subtotal_display =  $ref->currencyClass->format($ref->cart->full_total->prices[0]->price_value,$ref->cart->full_total->prices[0]->price_currency_id);
				}
				
				
				
			  $order_shipping = OPChikashipping::getShippingPrice($with_tax); 
			  
			  $op_coupon_ajax = self::getCouponHtml($coupon_display); 
			  
			  $continue_link = OPChikacontinuelink::get(); 
			  
			  
			  
			  $order_total_display = $ref->currencyClass->format($ref->cart->full_total->prices[0]->price_value_with_tax, $ref->cart->full_total->prices[0]->price_currency_id);			  
			  $op_coupon = $op_coupon_ajax; 
			  
			  $disable_couponns = OPChikaconfig::get('coupons_enable', true); 
			  if (empty($disable_couponns))
			  $op_coupon = $op_coupon_ajax = ''; 
			  
			 
			
			  
			  
			  $vars = array ('product_rows' => $product_rows, 
						   'payment_inside_basket' => '',
						   'shipping_select' => '', 
						   'payment_select' => '', 
						   'shipping_inside_basket' => '', 
						   'coupon_display' => $coupon_display, 
						   'subtotal_display' => $subtotal_display, 
						   'no_shipping' => $no_shipping,
						   'order_total_display' => $order_total_display, 
						   'tax_display' => $tax_display, 
						   'op_coupon_ajax' => $op_coupon_ajax,
						   'continue_link' => $continue_link, 
						   'coupon_display_before' => $coupon_display_before,
						   'discount_before' => $discount_before,
						   'discount_after'=>$discount_after,
						   'order_shipping'=>$order_shipping,
						   'cart' => $ref->cart, 
						   'op_coupon'=>$op_coupon,
						   'opc_show_weight_display'=>$opc_show_weight_display,
						   'cart_id' => $ismulti,
						   
						   );
						   
		$html = OPChikarenderer::fetch('basket.html', $vars); 
		
			if ($withwrapper)
			$html = '<div id="opc_basket">'.$html.'</div><!-- end id opc_basket -->'; 
			
			$op_no_basket = OPCHikaConfig::get('op_no_basket', false); 
			if (!empty($op_no_basket))
			{
			$html = '<div class="nobasket" style="display: none;">'.$html.'</div><!-- end class nobasket -->'; 
			
			}
		
		
		return $html; 
		
	}
	
	
	
	public static function getCouponHtml($coupon_display='') {
		$options = OPCHikaParams::get('cart'); 
		
		if (empty($coupon_display)) {
			$ref = OPChikaRef::getInstance(); 
			$taxes = round($ref->cart->full_total->prices[0]->price_value_with_tax - $ref->cart->full_total->prices[0]->price_value, $ref->currencyClass->getRounding($ref->cart->full_total->prices[0]->price_currency_id));
				if(!empty($ref->cart->coupon)) {
					if($taxes == 0 || empty($options['price_with_tax']))
					$coupon_display =  $ref->currencyClass->format($ref->cart->coupon->discount_value_without_tax * -1, $ref->cart->coupon->discount_currency_id);
					else
					$coupon_display = $ref->currencyClass->format($ref->cart->coupon->discount_value * -1, $ref->cart->coupon->discount_currency_id);
				}
				else {
				 $coupon_display = ''; 	
				}
		}
		
		$ref = OPChikaRef::getInstance(); 
			  if (empty($ref->cart->coupon)) {
			    $coupon_text = JText::_('ADD');
			  }
			  else {
				  $coupon_text = JText::sprintf('HIKASHOP_COUPON_LABEL', $ref->cart->coupon->discount_code);
			  }
			  
			  $vars = array('coupon_text'=> $coupon_text, 
			  'coupon_display'=>$coupon_display); 
			  $op_coupon_ajax = OPChikarenderer::fetch('couponField_ajax', $vars); 
			  
			  return $op_coupon_ajax; 
	}
	
	public static function checkSteps($product, $vars) {
       return false; 		
	}
	
	
	
}