<?php
defined('_JEXEC') or die('Restricted access');

class OPCHikaCart {
	public static function set($key, $val) {
		$ref = OPChikaref::getInstance(); 
		$cart_id = (int)$ref->cart_id;
		
		if (!empty($cart_id)) {
		 $db = JFactory::getDBO(); 
		 $cart_table = hikashop_table('cart');
		 $ins_val = $val; 
		 if (($key === 'cart_shipping_ids') && (is_array($val))) {
			 $ins_val = implode(',', $val);
		 }
		 elseif (($key === 'cart_coupon') && (is_array($val))) {
			 $ins_val = implode("\r\n", $val);
		 }
		 
		 $q = 'update `'.$db->escape($cart_table).'` set `'.$db->escape($key)."` = '".$db->escape($ins_val)."', `cart_modified` = ".(int)time()." where cart_id = ".(int)$cart_id;
		 //echo $q."<br />\n"; 
		 $db->setQuery($q); 
		 $db->execute(); 
		}
		$cart = self::getCart(); 
		$cart->{$key} = $val; 
		
	}
	
	public static function &getCart() {
		OPChikacache::clear(); 
		$ref = OPCHikaRef::getInstance(); 
		$cart = $ref->checkoutHelper->getCart(true);
		return $cart;
	}
	
	public static function getCartId() {
		
		$ref = OPChikaref::getInstance(); 
		$cart_id = $ref->cartClass->getCurrentCartId();
		return (int)$cart_id; 
			
		
		
		
	}
	
	public static function updateCartQuantities() {
		$quantity = JRequest::getVar('quantity', array()); 
		$cart_hikashop_product_id = JRequest::getInt('cart_hikashop_product_id', 0); 
		if (!is_array($quantity)) {
			$product_id = (int)$cart_hikashop_product_id;
			$p_quantity = (int)$quantity; 
			$nq = array(); 
			$nq[$product_id] = array(); 
			$nq[$product_id]['qty'] = (int)$p_quantity; 
			$nq[$product_id]['id'] = (int)$product_id; 
		}
		else {
		if (empty($quantity)) return; 
		$nq = array(); 
		foreach ($quantity as $product_id => $p_quantity) {
			$product_id = (int)$product_id; 
			if (empty($product_id)) continue; 
			$nq[$product_id] = array(); 
			$nq[$product_id]['qty'] = (int)$p_quantity; 
			$nq[$product_id]['id'] = (int)$product_id; 
		}
		}
		if (!empty($nq)) {
		 $cart_id = OPCHikaCart::getCartId(); 
		 $ref = OPCHikaRef::getInstance(); 
		 $ref->cartClass->updateProduct($cart_id, $nq); 
		}
		
		OPChikacache::clear(); 
		return; 
		
		
	}
	
	public static function addCoupon($new_coupon) {
		
		$checkout = JRequest::getVar('checkout', array(), '', 'array');
		
		$ref = OPCHikaRef::getInstance(); 
		$block_task = 'coupon'; 
		$ctrl = hikashop_get('helper.checkout-' . $block_task);
		$workflow = $checkoutHelper->checkout_workflow;
		$content = array(); 
		$content['task'] = 'coupon'; 
		$content['params']['src'] = array(
			'step' => 0,
			'workflow_step' => 0,
			'pos' => 0
		);
		
		
		$checkout['coupon'] = $new_coupon; 
		JRequest::setVar('checkout', $checkout); 
		
		if(!empty($ctrl)) {
			$ret = $ctrl->validate($ref->checkoutController, $content['params']);
			
		} else {
			$ref->checkoutController->initDispatcherOPC();
			$go_back = false;
			$original_go_back = false;
			$ret = $this->dispatcher->trigger('onAfterCheckoutStep', array($block_task, &$go_back, $original_go_back, &$ref->checkoutController));
		}
		
		
		
		
	}
	
		
}