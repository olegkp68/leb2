<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/

if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;


class plgSystemOpcbonus extends JPlugin {
	
	static $calc; 
	
	public function __construct(& $subject, $config){
	    if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
			  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		}
		
		
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_vmcoupon_opcbonus', __DIR__); 
	}
	
	
	
	function onAfterRoute() {
		
		if (!class_exists('VirtuemartCart')) return;
		$cart = VirtuemartCart::getCart(); 
		if (!empty($cart->cartProductsData)) {
			
			
		  $this->checkRestrictions($cart); 
		}
	}
	
	function plgVmOnUpdateCart(&$cart, &$force, &$html) {
		static $inRecursion; 
		if (!empty($inRecursion)) return null; 
		$inRecursion = true; 
		
		if (!empty($cart->cartProductsData)) {
		 $this->checkRestrictions($cart); 
		}
		$inRecursion= false; 
	}
	
	function plgUpdateProductObject(&$product) {
		if (!is_object($product)) return; 
		$product->virtuemart_product_id = (int)$product->virtuemart_product_id; 
		$pid = (int)$this->params->get('productID', 0); 
		if ($product->virtuemart_product_id === $pid) {
			$product->opc_hide_quantity_controls = true; 
			
			
		}
		
	}
	
	function plgVmOnRemoveFromCart($cart, $prod_index) {
		$pid = (int)$this->params->get('productID', 0); 
		
		$free_ind = -1; 
		$found = 0;
		
		if (count($cart->cartProductsData) === 2) {
			foreach ($cart->cartProductsData as $ind=>$r) {
				$r['virtuemart_product_id'] = (int)$r['virtuemart_product_id']; 
				if ($pid === $r['virtuemart_product_id']) {
					
					$found++; 
					$free_ind = $ind; 
				}
				elseif ((int)$prod_index === (int)$ind) {
					
					$found++; 
				}
			}
			if ($found === 2) {
				$this->removeFreeProduct($cart); 
			}
		}
		
		
	}
	
	public function onVmSiteController($controller) {
		
		$cart = VirtuemartCart::getCart(); 
		
		if (!empty($cart->cartProductsData)) {
		  $this->checkRestrictions($cart); 
		}
	}
	
	static $code; 
	static $msg; 
	static $silent; 
	function plgVmValidateCouponCode($_code,$_billTotal) {
		
		
		
		static $inRecursion; 
		
		if (!empty($inRecursion)) return null; 
		$inRecursion = true; 
	
			
			$cart = VirtuemartCart::getCart(); 
			$ret = $this->checkRestrictions($cart); 
			
			
			
			
		
		$inRecursion= false; 
		return null; 
	}
	
	function plgVmRemoveCoupon($_code,$_force) {
		
		return null; 
	}
	
	function plgVmCouponInUse($_code) {
		return null; 
		
		 
	}
	
	function plgVmOnAddToCart(&$cart) {
		static $inRecursion; 
		if (!empty($inRecursion)) return null; 
		$inRecursion = true; 
		
		if (!empty($cart->cartProductsData)) {
		 $this->checkRestrictions($cart); 
		}
		$inRecursion= false; 
	}
	
	
	
	private function calculateSubtotal(&$cart) {
		$subtotal = 0; 
		foreach ($cart->cartProductsData as $kx => $d) {
			if (!isset($cart->products[$kx])) return 0; 
			
			$p = $cart->products[$kx]; 
			$slesPrice = (float)$p->allPrices[$p->selectedPrice]['salesPrice']; 
			$q = (float)$d['quantity']; 
			$subtotal += $q * $slesPrice;
			
		}
		
		return $subtotal; 
		
		
		
	}
	
	
	private function checkRestrictions(&$cart) {
		if (!empty($cart->products))
		if (count($cart->products) === 1) {
				$first = reset($cart->products); 
				$pid = (int)$this->params->get('productID', 0); 
				if ($pid === (int)$first->virtuemart_product_id) {
					$this->removeFreeProduct($cart); 
					return; 
				}
			}
		
		static $inRecursion; 
		if (!empty($inRecursion)) return null; 
		$inRecursion = true; 
			
			
			
			if (!empty($cart->products)) {
				  $_billTotal = $this->calculateSubtotal($cart); 
				}
				else {
					$cart = OPCmini::getCart(); 
					$_billTotal = $this->calculateSubtotal($cart); 
					
				}
				
			
			
			$ref = new stdClass; 
			$ref->cart =& $cart;

		$threashorld = (float)$this->params->get('order_amount', 0); 
		$pid = (int)$this->params->get('productID', 0); 
		if (($_billTotal >= $threashorld) && (!empty($_billTotal))) {
			
			
			
			require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'addtocartaslink.php');
			
			OPCAddToCartAsLink::addtocartaslink($ref, array($pid=>$pid), array($pid=>1), array(), false, 2); 
			$this->checkMultiple($cart); 
			
			$inRecursion= false; 
			return true; 
		}
		elseif (!empty($_billTotal)) {
			$this->checkMultiple($cart); 
			$this->removeFreeProduct($cart); 
			
			
			
			 
			
			 
			$inRecursion= false; 
			//let VM process it
			return null; 
		}
		elseif (empty($_billTotal)) {
			
		}
		
		//if just one:
		if (count($cart->cartProductsData) === 1) {
			
			$first = reset($cart->cartProductsData); 
			$pid = (int)$this->params->get('productID', 0); 
			
			
			
			if (!empty($pid))
			if ((int)$first['virtuemart_product_id'] === $pid) {
				
				$this->removeFreeProduct($cart); 
				$inRecursion= false; 
				return null; 
			}
		}
		
		$this->checkMultiple($cart); 
		$inRecursion= false; 
		return null; 
	}
	
	private function checkMultiple(&$cart) {
		
		$found = false; 
		$pid = (int)$this->params->get('productID', 0); 
		foreach ($cart->cartProductsData as $kx=>$d) {
				$d['virtuemart_product_id'] = (int)$d['virtuemart_product_id']; 
				$pid = (int)$this->params->get('productID', 0); 
				if (!empty($pid))
				if ($d['virtuemart_product_id'] === $pid) {
					if (empty($found)) {
					if ($d['quantity'] >= 1) {
						$cart->cartProductsData[$kx]['quantity'] = 1;
						$found = true; 
					}
					}
					else {
						unset($cart->cartProductsData[$kx]); 
						unset($cart->products[$kx]); 
					}
				}
		}
	}
	
	
	private function removeFreeProduct($cart) {
		foreach ($cart->cartProductsData as $kx=>$d) {
			$d['virtuemart_product_id'] = (int)$d['virtuemart_product_id']; 
				$pid = (int)$this->params->get('productID', 0); 
				if (!empty($pid))
				if ($d['virtuemart_product_id'] === $pid) {
					//$cart->removeProductCart($kx); 
					unset($cart->cartProductsData[$kx]); 
					unset($cart->products[$kx]); 
					
				}
			}
	}
	
	
}