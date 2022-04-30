<?php
defined('_JEXEC') or die('Restricted access');

class OPChikapayment {
	public static function getPaymentHTML($withWrap=false, &$checkoutView=null) {
		if (empty($checkoutview)) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'hika'.DIRECTORY_SEPARATOR.'checkout.controller.php');
			$checkoutControllerOpc = new checkoutControllerOpc; 
			$checkoutView = $checkoutControllerOpc->getViewOPC(); 
		}
		$payment_html = $checkoutView->getPaymentHtml(); 
		if (!empty($withWrap)) {
			$payment_html = '<div id="payment_html">'.$payment_html.'</div>'; 
		}
		
		return $payment_html; 
	}
	
	public static function setPayment($payment_value) {
		$cart_id = OPChikaCart::getCartId(); 
		if (!empty($cart_id)) {
			$db = JFactory::getDBO(); 
			$q = 'update #__hikashop_cart set `cart_payment_id` = \''.(int)$payment_value.'\' where cart_id = '.(int)$cart_id; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
	}
}