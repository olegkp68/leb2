<?php
/*
*
* @copyright Copyright (C) 2007 - 2015 RuposTel - All rights reserved.
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

 
 
$this->params->universalga = (int)$this->params->universalga; 
 
if ($this->params->universalga === 2) {
	 $this->isPureJavascript = true; 
	 $max = count($this->order['items']); 
	  $i = 0; 
	  $items = array(); 
	  foreach ($this->products as $key=>$order_item) 
	  { 
	  
	  $product_id = $order_item->pid; 
	    $i++; 
	  if (empty($order_item->category_name)) $order_item->category_name = ''; 
	  if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name; 
	  
	  $json = array(); 
	    $json['id'] = (string)$product_id; 
	    $json['name'] = $order_item->order_item_name;
        $json['list_name'] = 'Product Impression'; 
		$json['brand'] = ''; 
		$json['category'] = $order_item->category_name;
        $json['variant'] = $order_item->order_item_sku;
		$json['list_position'] = $i; 
		$json['price'] = number_format($order_item->product_final_price, 2, '.', '');
        $json['quantity'] = (int)number_format($order_item->product_quantity , 0, '.', ''); 
		if (!empty($this->order['details']['BT']->coupon_code)) 
        $json['coupon'] = $this->order['details']['BT']->coupon_code;
	    $items[] = $json; 
	  
	  }
	if (!empty($this->params->google_adwords_id)) { 
	?>
	<script>
  gtag('event', 'page_view', {
	 'send_to': '<?php echo $this->params->google_adwords_id; ?>',
    'value': '<?php echo $json['price']; ?>',
    'items': [{
      'id': '<?php echo $json['id']; ?>',
      'google_business_vertical': 'retail'
    }]
  });
</script>
<?php 
	} ?>	
	<script>
	
	
	<?php if (count($items)>1) { ?>
	gtag('event', 'view_item_list', {
	  'currency': '<?php echo $this->escapeSingle($this->currency['currency_code_3']); ?>',	 
	  'items': <?php echo json_encode($items, JSON_PRETTY_PRINT); ?> });
	<?php }
	else {
		?>
	gtag('event', 'view_item', {
	  'currency': '<?php echo $this->escapeSingle($this->currency['currency_code_3']); ?>',	 
	  'items': <?php echo json_encode($items, JSON_PRETTY_PRINT); ?> });	
	  <?php
	}
	?>
	  
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Product Impression.'); 
	  }
	  
	</script>
	<?php
	
	
}
else {

if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 
$product = $this->product; 
// generic fix: 

$idformat = $this->idformat; 

 


 $tracker_name = 'OPCTracker'; 
 
?>

<script type="text/javascript">
//<![CDATA[
  
  
  // if universtal analytics is initialized
  if (typeof ga != 'undefined')
   {
      
	
      <?php 
	  // if normal ecommerce tracking enabled: 
	  if (!empty($this->params->ec_type)) 
	  { 
	  // if normal ec
	   //added to _first: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_init.php'); 	
 	  // include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_addtransaction.php'); 	
		
	foreach ($this->products as $key=>$product) 
	{ 
	

	$pid = $product->pid; 
	
   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each 
   $order_item = $product; 
    include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_addimpression.php'); 	
    ?>
	if (typeof ga != 'undefined')
	{
	ga('<?php echo $tracker_name.'.'; ?>ecommerce:setAction', 'detail');
	
	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: ecommerce ecommerce_addimpression'); 
	  }
	}
	<?php
   
	} 
		
		
	    include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_send.php'); 
		////added later: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php'); 
	?>

	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: ecommerce send'); 
	  }
	
<?php
	  }  //if normal ec
	  else 
	  {   //if enhanced
	
		// enhanced ecommerce GA
		//added to _first: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_init.php'); 
		
			?>

	
<?php

		
				
			foreach ($this->products as $key=>$product) 
			{ 
			// add item might be called for every item in the shopping cart
			// where your ecommerce engine loops through each item in the cart and
			// prints out _addItem for each 
			
			$pid = $product->pid; 
			$order_item = $product; 
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_addimpression.php'); 
	
	?>
	
	
	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: enhanced EC ec_addimpression'); 
	  }
	
	<?php
	
	
	
include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_addproduct.php'); 	
			
								?>
	
	
	
	
	
	
<?php

			
			} 
			//end of foreach
			$action = 'detail'; 
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_action.php'); 
	    
		
										?>
	
	
<?php

		
		
	  //added later: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php'); 

										?>

	
<?php

	  
	}  // end if enhanced ec
	
	?>
	
	   
   if (typeof ga != 'undefined')
    {
	
	   <?php 
	   // do not send Thank you page: 
	   
	   $this->params->page_url = ''; 
	   
	   //moved to _last include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php');  
	   ?>
	   
	}

	
   
   }
   else
   {
  
  if (typeof _gat != 'undefined')
  {
  var pageTracker = _gat._getTracker("<?php echo $this->params->google_analytics_id; ?>");
  pageTracker._trackPageview();
  
  										
	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: _gat pageTracker _trackPageview'); 
	  }
  }


  
  <?php 
  
  
  
  
  foreach ($this->products as $key=>$product) { ?>
   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each 
   <?php 
   $pid = $product->pid; 

   $order_item = $product; 
   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'_gat_addimpression.php');  
   
   
   ?>
  
<?php

   
   
   } 
   ?>
  


   
   
   }

  
//]]>
</script>

<?php
}