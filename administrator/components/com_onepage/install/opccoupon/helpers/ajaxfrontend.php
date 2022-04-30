<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/

if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

class OpccouponAjax extends JPlugin {
	public function __construct(& $subject, $config){
		parent::__construct($subject, $config);
	}
}