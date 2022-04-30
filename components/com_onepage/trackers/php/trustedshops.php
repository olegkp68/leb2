<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
$order_total = $this->order['details']['BT']->order_total;



if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 

// generic fix: 
if (empty($this->order['details']['BT']->currency_code_3))
$this->order['details']['BT']->currency_code_3 = 'EUR'; 


  $idformat = $this->idformat; 
 

$tag = JFactory::getLanguage()->getTag(); 
$tag = str_replace('-', '_', strtolower($tag)); 

?>

<div id="trustedShopsCheckout" style="display: none;">
<span id="tsCheckoutOrderNr"><?php echo $this->idformat; ?></span>
<span id="tsCheckoutBuyerEmail"><?php echo $this->order['details']['BT']->email; ?></span>
<span id="tsCheckoutOrderAmount"><?php echo number_format($order_total, 2, '.', ''); ?></span>
<span id="tsCheckoutOrderCurrency"><?php echo $this->order['details']['BT']->currency_code_3; ?></span>
<span id="tsCheckoutOrderPaymentType"><?php 
$db = JFactory::getDBO(); 
$pid = (int)$this->order['details']['BT']->virtuemart_paymentmethod_id; 
$q = 'select `payment_name` from `#__virtuemart_paymentmethods_'.$tag.'` where virtuemart_paymentmethod_id = '.(int)$pid; 
try { 
$db->setQuery($q); 
$pname = $db->loadResult(); 
echo $pname; 
}
catch (Exception $e) {
	echo '-'; 
}

?></span>
<?php 
/*
<span id="tsCheckoutOrderEstDeliveryDate">2016-05-24</span>
*/
?></div> 
<?php

  $this->isPureJavascript = false; 
 
