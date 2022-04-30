<?php
class OPCHikaParams {
	public static function get($type) {
		$ctrl = hikashop_get('helper.checkout-cart');
		if (empty($ctrl)) return array(); 
		$emptyView = new OPCHikaEmptyView(); 
		$params = array(); 
		$ctrl->display($emptyView, $params); 
		return $params; 
					
	}
	
	
	public static function getWorkflowSteps() {
	    $cart_id = OPChikaCart::getCartId(); 
		$cart_id = (int)$cart_id; 
		JRequest::setVar('cart_id', $cart_id); 
		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get($cart_id);
		$workflow = $checkoutHelper->checkout_workflow;
		$steps = array(); 
		foreach ($workflow['steps'] as $step_id => $step) {
			foreach ($step['content'] as $content_id => $content) 
			{
				
					$steps[$step_id.'_'.$content_id] = $content['task'];
				
			}
		}
		
		return $steps; 
	}
}