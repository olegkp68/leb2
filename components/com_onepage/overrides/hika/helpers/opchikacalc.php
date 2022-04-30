<?php
defined('_JEXEC') or die('Restricted access');
class OPCHikaCalc {
	public static function getPreCalcTotals() {
		$ref = OPChikaref::getInstance(); 
		$cart = OPCHikaCart::getCart(); 
		
		$ref->shippingClass->get('reset_cache');
		$options = OPCHikaParams::get('cart'); 
		
		$totals = array(); 
		
		$shippings = $ref->shippingClass->getShippings($cart, true);
		$payments = $ref->paymentClass->getPayments($cart, true);
		
		foreach ($shippings as $k=>$m) {
		
		foreach ($payments as $k2=>$pm) {
		
		//$shipping_id = $m->shipping_id; 
		if (!isset($m->shipping_warehouse_id)) $m->shipping_warehouse_id = 0; 
		$shipping_id = 'shipping_radio_0_0__'.$m->shipping_warehouse_id.'__'.$m->shipping_type.'_'.$m->shipping_id;
		$payment_id = $pm->payment_id; 
		//$payment_id = 'payment_radio_0_0__'.$pm->payment_type.'_'.$pm->payment_id;
		
		if (empty($totals[$shipping_id])) $totals[$shipping_id] = array(); 
		if (empty($totals[$shipping_id][$payment_id])) $totals[$shipping_id][$payment_id] = array(); 
		
		
		if (isset($m->shipping_warehouse_id)) {
		  $ware_house_id = (int)$m->shipping_warehouse_id; 
		}
		else {
			$ware_house_id = 0; 
		}
		
		    $with_tax = true; 
			$taxes = round($cart->full_total->prices[0]->price_value_with_tax - $cart->full_total->prices[0]->price_value, $ref->currencyClass->getRounding($cart->full_total->prices[0]->price_currency_id));
					
					if(empty($taxes) || (empty($options['price_with_tax']))) {
					   $with_tax = false; 
					}
		
		
		OPCHikaCart::set('cart_shipping_ids', array(0 => $m->shipping_id.'@'.$ware_house_id)); 
		OPCHikaCart::set('cart_payment_id', $pm->payment_id); 
		
		$cart = OPCHikaCart::getCart(); 
		
		$total = $cart->full_total->prices[0]->price_value_with_tax; 
		$totals[$shipping_id][$payment_id] = (array)$cart->full_total->prices[0]; 
		
		$totals[$shipping_id][$payment_id]['debug'] = (array)$cart->full_total; 
		$totals[$shipping_id][$payment_id]['debug_ship'] = (array)$m; 
		$totals[$shipping_id][$payment_id]['debug_pay'] = (array)$pm; 
		
		if ($with_tax) {
			$totals[$shipping_id][$payment_id]['order_shipping'] = floatval($m->shipping_price_with_tax);
			$totals[$shipping_id][$payment_id]['payment_discount'] = floatval($pm->payment_price_with_tax);
			$totals[$shipping_id][$payment_id]['shipping_price'] = floatval($m->shipping_price_with_tax);
			$totals[$shipping_id][$payment_id]['payment_price'] = floatval($pm->payment_price_with_tax);
		}
		else {
		 $totals[$shipping_id][$payment_id]['order_shipping'] = floatval($m->shipping_price);
		 $totals[$shipping_id][$payment_id]['payment_discount'] = floatval($pm->payment_price);
		 $totals[$shipping_id][$payment_id]['shipping_price'] = floatval($m->shipping_price);
		 $totals[$shipping_id][$payment_id]['payment_price'] = floatval($pm->payment_price);
		}
		$totals[$shipping_id][$payment_id]['order_total'] = $total;
		$totals[$shipping_id][$payment_id]['coupon_discount'] = 0;
		$totals[$shipping_id][$payment_id]['coupon_discount2'] = 0;
		$totals[$shipping_id][$payment_id]['tax'] = 0;
		if ($with_tax) {
			if (isset($cart->full_total->prices[0]->price_value_without_discount_with_tax)) {
		  $totals[$shipping_id][$payment_id]['order_subtotal'] = $cart->full_total->prices[0]->price_value_without_discount_with_tax;
			}
			else {
		$totals[$shipping_id][$payment_id]['order_subtotal'] = $cart->full_total->prices[0]->price_value_with_tax;	
			}

		  
		  

		}
		else {
			if (isset($cart->full_total->prices[0]->price_value_without_discount)) {
				$totals[$shipping_id][$payment_id]['order_subtotal'] = $cart->full_total->prices[0]->price_value_without_discount;
			}
			else {
				$totals[$shipping_id][$payment_id]['order_subtotal'] = $cart->full_total->prices[0]->price_value;
			}
		}
		
		if( empty($with_tax)) {
					$coupon_display =  $ref->currencyClass->format($cart->coupon->discount_value_without_tax * -1, $cart->coupon->discount_currency_id);
					
					$totals[$shipping_id][$payment_id]['coupon_discount'] = $cart->coupon->discount_value_without_tax;
					
				}
				else {
					$coupon_display = $ref->currencyClass->format($cart->coupon->discount_value * -1, $cart->coupon->discount_currency_id);
					
					
					$totals[$shipping_id][$payment_id]['coupon_discount'] = $cart->coupon->discount_value;
					
					}
		
		
		
		$totals[$shipping_id][$payment_id]['dynamic'] = array(); 
		$ind = 0; 
		if (!empty($cart->full_total->prices[0]->taxes)) {
			foreach ($cart->full_total->prices[0]->taxes as $kn=>$dt) {
				
				$totals[$shipping_id][$payment_id]['dynamic'][$ind]['stringname'] = $dt->tax_namekey; 
				$totals[$shipping_id][$payment_id]['dynamic'][$ind]['value'] = $dt->tax_amount; 
				$totals[$shipping_id][$payment_id]['dynamic'][$ind]['rate'] = $dt->tax_rate; 
				$totals[$shipping_id][$payment_id]['dynamic'][$ind]['id'] = $kn.'_'.$ind; 
				$ind++;
			}
		}
		
		
		
		 }
		}
		return $totals; 
		
		
	}
}