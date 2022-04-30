<?php
/**
* 
*
* @package One Page Checkout for VirtueMart 2
* @subpackage opc
* @author stAn
* @author RuposTel s.r.o.
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 


if (class_exists('plgSystemOrder_discount_rules'))
{
$dispatcher = JDispatcher::getInstance();
jimport( 'joomla.plugin.helper' );
$plugin = JPluginHelper::getPlugin('system', 'order_discount_rules'); 

$arr = array(); 
foreach ($plugin as $key=>$val)
$arr[$key] = $val; 

//$dt->loadView(); 
class dtorder_discount_rules extends plgSystemOrder_discount_rules
{
  public function plgVmOnCheckoutAdvertise(&$cart)
   {
     return $this->plgVmOnCheckoutAdvertise($cart, $checkoutAdvertise); 
   }
}
$dt = new dtorder_discount_rules($dispatcher, $arr); 




}
