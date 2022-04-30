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
?>


<?php 
if (!empty($this->params->adwords_remarketing))
{
$products_tags = array(); 
foreach ($this->order['items'] as $key=>$order_item) { 
$pid = $order_item->pid; 

$products_tags[] = "'".$this->escapeSingle($pid)."'"; 
?>


		

<?php } ?>

var google_tag_params = {
      ecomm_prodid: <?php 
	  
	  if (count($products_tags)==1) {
		  $pids =  reset($products_tags); 
		  echo $pids; 
	  }
	  else
	  {
	   $pr = implode(',', $products_tags); 
	   $pids = '['.$pr.']'; 
	   echo $pids; 
	  }
	  
	  // will show products like ['1', '2'] .... 
	  ?>,
      'ecomm_pagetype': 'purchase',
      'ecomm_totalvalue': <?php echo number_format($order_total, 2, '.', ''); ?>,
	  'dynx_itemid': <?php echo $pids; ?>, 
	  'dynx_itemid2': <?php echo $pids; ?>, 
	  'dynx_pagetype': 'conversion',
	  'dynx_totalvalue': <?php echo number_format($order_total, 2, '.', ''); ?>,
    };


if (typeof dataLayer != 'undefined')
dataLayer.push({
    'event': '<?php echo $this->escapeSingle($this->params->tag_event); ?>',
    'google_tag_params': window.google_tag_params
   });

    if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking GTM: Adwords ecommerce datalayer added and triggered for <?php echo $this->escapeSingle($this->params->tag_event); ?>'); 
	  }
   
<?php
// GA remarketing tag end
}



$idformat = $this->idformat; 
 
$dispatcher = JDispatcher::getInstance(); 


if (!isset($this->vendor['company'])) $this->vendor['company'] = 'Shop'; 
?>
// Send transaction data with a pageview if available
// when the page loads. Otherwise, use an event when the transaction
// data becomes available.
if (typeof dataLayer != 'undefined')
dataLayer.push({
  'event':'Purchase',
  'ecommerce': {
    'purchase': {
      'actionField': {
        'id': '<?php echo $this->escapeSingle($idformat); ?>',
        'affiliation':  '<?php echo $this->escapeSingle($this->vendor['company']); ?>',
        'revenue': '<?php echo number_format($order_total, 2, '.', ''); ?>',
        'tax': '<?php echo number_format($this->order['details']['BT']->order_tax, 2, '.', ''); ?>',
        'shipping': '<?php echo number_format($this->order['details']['BT']->order_shipment, 2, '.', ''); ?>',
        'coupon': '<?php if (!empty($this->order['details']['BT']->coupon_code)) echo $this->escapeSingle($this->order['details']['BT']->coupon_code); ?>'
      },
      'products': [<?php 
	  
	  $max = count($this->order['items']); 
	  $i = 0; 
	  foreach ($this->order['items'] as $key=>$order_item) 
	  { 
	  $list = 'Category: '.$order_item->category_name; 
	  $dispatcher->trigger('plgGetProductMetadata', array(&$order_item, $this->params->pidformat, 'Purchase', 'purchase', $list )); 
	  $product_id = $order_item->pid; 
	  
	  
	  $i++; 
	  
	  if (empty($order_item->category_name)) $order_item->category_name = ''; 
	  if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name; 
	  if (empty($order_item->mf_name)) $order_item->mf_name = ''; 
	  ?>{                            
        'name': <?php echo json_encode($order_item->order_item_name); ?>,     
        'id': '<?php echo $this->escapeSingle($product_id); ?>',
        'price': '<?php echo number_format($order_item->product_final_price, 2, '.', ''); ?>',
        'brand': <?php echo json_encode($order_item->mf_name) ?>,
        'category': <?php echo json_encode($order_item->category_name ); ?>,
        'variant': <?php echo json_encode($order_item->order_item_sku); ?>,
        'quantity': <?php echo number_format($order_item->product_quantity , 0, '.', ''); ?>,
        'coupon':  <?php 
		if (!empty($this->order['details']['BT']->coupon_code)) 
		{
			echo json_encode($this->order['details']['BT']->coupon_code); 
		}
		else {
			echo '\'\''; 
		}
		
			
       ?>}<?php if ($i !== $max) echo ','; ?>
   <?php } ?>]
    }
  }
});

    if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking GTM: Purchase datalayer event added.'); 
	  }
	  if (typeof gtmProductEvent !== 'undefined') {
	    gtmProductEvent.ecommercePushed = true; 
	  }

<?php


