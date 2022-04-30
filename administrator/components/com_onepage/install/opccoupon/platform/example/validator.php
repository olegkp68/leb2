<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/
 
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

class anwbValidator {
	public function __construct(&$ref) {
		$this->ref = $ref; 
	}
	
	
	public function validateCouponSyntax($coupon_code) {
		if ($coupon_code === 'example') {
		$Outputs->msg = "Valid";
		$Outputs->ret = true; 
		$Outputs->coupon_code = $coupon_code; 
		return $Outputs; 
		}
		
		$Outputs->msg = "Invalid: Input Value does not comply with the 11 proof.";
		$Outputs->ret = false;
		return $Outputs;
		
	}
}	