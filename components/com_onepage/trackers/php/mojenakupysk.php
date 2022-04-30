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


$total = 0; 
foreach ($this->order['items'] as $key=>$order_item) {
  $total += ((float)$order_item->product_discountedPriceWithoutTax * (float)$order_item->product_quantity); 
  
}
?>
	
<script type="text/javascript" src="https://www.mojenakupy.sk/ra.js"></script>
<script type="text/javascript">
	if (typeof diltracking !== 'undefined') {
    diltracking.shop    = '<?php echo $this->params->shop_id; ?>';
    diltracking.orderId = '<?php echo (int)$this->order['details']['BT']->virtuemart_order_id; ?>';
    <?php
	  $order_status = $this->order['details']['BT']->order_status; 
	  if (empty($this->params->paid_statuses)) $this->params->paid_statuses = array(); 
	  if (in_array($order_status, $this->params->paid_statuses)) {
		  $paid = '1'; 
	  }
	  else $paid = '0'; 
	?>
	diltracking.paid = '<?php echo $paid; ?>';
    diltracking.currency = '<?php echo $this->order['details']['BT']->currency_code_3; ?>';
	<?php 
	foreach ($this->order['items'] as $key=>$order_item) {
		$product_id = (int)$order_item->virtuemart_product_id; 
	?>
		diltracking.addItem(<?php echo $product_id; ?>, '<?php echo number_format($order_item->product_final_price, 2, '.', ''); ?>', <?php echo (int)number_format($order_item->product_quantity , 0, '.', ''); ?>);
	<?php } ?>
    diltracking.send();
	
	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: mojenakupy.sk order event', diltracking); 
	  }
	
	}
	
	
</script>
