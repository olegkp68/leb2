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
// to be used as described at https://support.google.com/tagmanager/answer/3002580?hl=en
//if (!empty($this->params->adwords_remarketing))
{
	
$products_tags = array(); 

$total = 0; 
foreach ($this->products as $key=>$product) { 

$pid = $product->pid;  
$products_tags[] = "'".$this->escapeSingle($pid)."'"; 
$total += ($product->product_final_price * $product->product_quantity); 


}
$order_total = $total; 

?>



var google_tag_params = {
      'ecomm_prodid': <?php 
	  
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
      'ecomm_pagetype': 'cart',
      'ecomm_totalvalue': <?php echo number_format($order_total, 2, '.', ''); ?>,
	  'dynx_itemid': <?php echo $pids; ?>, 
	  'dynx_itemid2': <?php echo $pids; ?>, 
	  'dynx_pagetype': 'conversionintent',
	  'dynx_totalvalue': <?php echo number_format($order_total, 2, '.', ''); ?>,
    };



if (typeof dataLayer != 'undefined')
dataLayer.push({
'event': '<?php echo $this->escapeSingle($this->params->tag_event); ?>',
    'google_tag_params': window.google_tag_params
}); 



    if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking GTM: google_tag_manager remarketing cart event, triggered for <?php echo $this->escapeSingle($this->params->tag_event); ?>'); 
	  }
	



<?php 
}



?>
	
if (typeof dataLayerImprCart == 'undefined') {
	 dataLayerImprCart = {}; 
	 
	 dataLayerImprCart.event = 'cartViewEvent'; 
	 <?php if (false) { ?>
     dataLayerImprCart.ecommerce = {};         
	 dataLayerImprCart.ecommerce.currencyCode =  '<?php echo $this->escapeSingle($this->currency['currency_code_3']); ?>'
      
	
	dataLayerImprCart.ecommerce.impressions = new Array(); 
	 <?php } ?>
}

var gtmCartProducts = new Array(); 
var gtmCartProductsIndexed = new Array(); 
var currentPI = null; 

<?php 

if (empty($this->listName)) $this->listName = 'Impressions'; 

 

$pos = 1; 
if (!empty($this->products)) {
foreach ($this->products as $key => $product) {
	
	$pid = $this->getPID($product->virtuemart_product_id, $product->product_sku); 

//dataLayerImprCart.ecommerce.impressions
//'list': 'CartImpressions',

$list = 'Category: '.$product->category_name;
if (empty($product->quantity)) $product->quantity = 1; 
if (empty($product->mf_name)) $product->mf_name = ''; 
?> 




currentPI = {
       'name': '<?php echo trim($this->escapeSingle($product->product_name)); ?>',       
       'id': '<?php echo $this->escapeSingle($pid); ?>',
       'price': '<?php echo number_format($product->product_final_price, 2, '.', ''); ?>',
       'brand': '<?php echo $this->escapeSingle($product->mf_name); ?>',
       'category': '<?php echo $this->escapeSingle($product->category_name ); ?>',
       'variant': '<?php echo $this->escapeSingle($product->product_sku); ?>',
       'position': <?php echo $pos; ?>,
	   'quantity': <?php echo (int)$product->quantity; ?>,
	   
     }; 
	 
gtmCartProducts.push(currentPI); 
gtmCartProductsIndexed[<?php echo json_encode($key); ?>] = currentPI; 	 
	 <?php
	$pos++; 
}
$pos--; 
}
$this->isPureJavascript = true; 


?>
 <?php if (false) { ?>
dataLayerImprCart.ecommerce.impressions = gtmCartProducts; 
 <?php } ?>
 
if ((typeof console != 'undefined') && (typeof console.log == 'function')) {
		console.log('OPC Tracking GTM: Adding cart product impressions into CartImpressions list and cartViewEvent', dataLayerImprCart); 
	}


<?php

$step = JRequest::getVar('step', -1); 
if (!empty($step)) { $step++; }

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
$is_multi_step = OPCconfig::get('is_multi_step', false); 

if ($is_multi_step) {
	if ($step <= 0) $step = 1; 
	if ($step === 1) {
		
		?>

//this is a custom event for cart view: 
if (typeof dataLayer !== 'undefined') 
{	
	dataLayer.push(dataLayerImprCart); 
}

<?php

	}
}
else {
	
	?>
	if (typeof dataLayer !== 'undefined') 
	{
	dataLayer.push(dataLayerImprCart);
	}
	
	<?php
}
