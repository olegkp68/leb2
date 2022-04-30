<?php
defined('_JEXEC') or die('Restricted access');
class OPChikaRef {
	public static $ref; 
	function __construct() {
		self::$ref = new stdClass(); 
		
		$cartHelper = hikashop_get('helper.cart');
		
		hikashop_get('helper.checkout');
		$cartClass = hikashop_get('class.cart');
		self::$ref->cartClass =& $cartClass; 
		
		self::$ref->cart_id = self::$ref->cartClass->getCurrentCartId();
		self::$ref->checkoutHelper = hikashopCheckoutHelper::get(self::$ref->cart_id);
		//$checkoutHelper = hikashopCheckoutHelper::get();
		
		
		
		self::$ref->addressClass = hikashop_get('class.address');
		self::$ref->cart_addresses = self::$ref->checkoutHelper->getAddresses();
		self::$ref->fieldClass = hikashop_get('class.field');
		self::$ref->edit_address = new stdClass();
		self::$ref->productClass = hikashop_get('class.product');
		self::$ref->imageHelper = hikashop_get('helper.image');
		$currencyClass = hikashop_get('class.currency');
		self::$ref->currencyClass =& $currencyClass;
		self::$ref->currencyHelper =& $currencyClass;
		$config =  hikashop_config();
		
		self::$ref->config = $config; 
		self::$ref->params =& self::$ref->config;
		
		
		$imageHelper = hikashop_get('helper.image');
		self::$ref->imageHelper = $imageHelper; 
		
		self::$ref->cartHelper =& $cartHelper; 
		self::$ref->shippingClass = hikashop_get('class.shipping');
		self::$ref->paymentClass = hikashop_get('class.payment');
		
		
		require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'checkout.controller.php'); 
		$emptya = array(); 
		$checkoutController = new checkoutControllerOpc($emptya, true); 
		self::$ref->checkoutController = $checkoutController; 
		
		
	}
	
	public static function &getInstance() {
		if (!empty(self::$ref)) return self::$ref;
		$hikaRef = new OPChikaRef();
		$ref =& self::$ref; 
		return $ref; 
	}
	
	public static function getCart() {
		$ref =& self::getInstance(); 
		$ref->cart = self::$ref->checkoutHelper->getCart(true);
		return $ref->cart; 
	}
	
	
	
}

class refObject {
	var $cartClass; 
	var $cart_id; 
	var $checkoutHelper; 
	var $addressClass; 
	var $cart_addresses; 
	var $fieldClass; 
	var $edit_address; 
	var $productClass; 
	var $imageHelper; 
	var $currencyClass; 
	var $currencyHelper; 
	var $config; 
	var $cartHelper;
	
	public function __construct() {
		
	}
	
	
	
}