<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
*      One Page Checkout configuration file
*      Copyright RuposTel s.r.o. under GPL license
*      Version 1 of date 17.August 2010
*      Feel free to modify this file according to your needs
*
*
*     @copyright Copyright (C) 2007 - 2010 RuposTel - All rights reserved.
*     @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*     One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
*     VirtueMart is free software. This version may have been modified pursuant
*     to the GNU General Public License, and as distributed it includes or
*     is derivative of works licensed under the GNU General Public License or
*     other free or open source software licenses.
* 
*/

$cut_login = true;
//$ajaxify_cart = false; 

$component = JRequest::getVar('option'); 

$ajaxify_cart = true; 

  $has_own_cart_postion_cart_fields = true; 
 
if (!empty($ismulti) || ($component == 'com_multiorders'))
   {
	   // adjust opc config for multi basket: 
	   $shipping_inside_basket = true; 
	   //$payment_inside_basket = false; 
	   $shipping_inside = true; 
	   $op_delay_ship = false; 
   
   }
   
   