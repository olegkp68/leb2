<?php
defined('_JEXEC') or die('Restricted access');

class OPChikashipping {
	public static function getShipToOpened() {
		return OPCHikaShipTo::isOpen(); 
	}
	
	public static function getShippingEnabled() {
		//if shiping enabled, return false
		//if shipping disabled, return true
		$cart = OPCHikaCart::getCart(); 
		$no_shipping = false; 
		if (self::isZeroWeight($cart)) {
			$no_shipping = true; 
		}
		return $no_shipping; 
	}
	
	public static function getShipToEnabled() {
		$no_shipto = false; 
		$cart = OPCHikaCart::getCart(); 
		if (self::isZeroWeight($cart)) {
			$no_shipto = true; 
		}
		
		if (!defined('NO_SHIPTO')) define('NO_SHIPTO', $no_shipto); 
		return NO_SHIPTO; 
	}
	
	
	
	public static function isZeroWeight($cart) {
		$ref = OPCHikaRef::getInstance(); 
		
		if(!$ref->config->get('force_shipping') && ((isset($cart->package['weight']) && $cart->package['weight']['value'] <= 0.0) || (isset($cart->weight) && bccomp($cart->weight, 0, 5) <= 0)))
			return true; 
		
		return false; 
	}
	
	public static function getShippingPriceRAW($with_tax = true) {
		$shipping_price = null; 
		$ref = OPChikaRef::getInstance(); 
		foreach($ref->cart->shipping as $shipping) {
					if(!isset($shipping->shipping_price) && isset($shipping->shipping_price_with_tax) ) {
						$shipping->shipping_price = floatval($shipping->shipping_price_with_tax);
					}
					if(isset($shipping->shipping_price)) {
						$shipping->shipping_price = floatval($shipping->shipping_price); 
						$taxes = $shipping->shipping_price_with_tax - $shipping->shipping_price;
						
						if($shipping_price === null)
							$shipping_price = 0.0;
						
						if (($with_tax) || (!isset($shipping->shipping_price_with_tax))) {
							$shipping_price += $shipping->shipping_price;
						}
						else {
							$shipping_price += $shipping->shipping_price_with_tax;
						}
					}
				}
				return $shipping_price; 
	}
	public static function getShippingPrice($with_tax = true) {
		$shipping_price = self::getShippingPriceRAW($with_tax); 
		if (!is_null($shipping_price)) {
			$ref = OPChikaRef::getInstance(); 
			return $ref->currencyClass->format($shipping_price, $ref->cart->full_total->prices[0]->price_currency_id);
		}
		return ''; 
	}
	
	public static function setShipping($shipping_value) {
		$cart_id = OPChikaCart::getCartId(); 
		if (!empty($cart_id)) {
			$db = JFactory::getDBO(); 
			$q = 'update #__hikashop_cart set `cart_shipping_ids` = \''.$db->escape($shipping_value).'\' where cart_id = '.(int)$cart_id; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
	}
	
	
	public static function getInvalidShipping() {
		$html = '<input type="hidden" name="invalid_country" id="invalid_country" value="invalid_country" /><input type="hidden" name="checkout[shipping][0][id]" checked="checked" id="shipment_id_0" value="choose_shipping" />'; 
		return $html; 
	}
	
	
}