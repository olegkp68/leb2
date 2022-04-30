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

$this->params->universalga = (int)$this->params->universalga; 

if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 

// generic fix: 
if (empty($this->order['details']['BT']->currency_code_3))
$this->order['details']['BT']->currency_code_3 = 'USD'; 


 $idformat = $this->idformat; 
 $tracker_name = 'OPCTracker'; //.$idformat; 

 
 $this->params->server2server = (int)$this->params->server2server; 
 
 if (!empty($this->params->server2server))
 {
	 //php_ga_addtransaction.php
	 try 
	 {
	 include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'php-ga'.DIRECTORY_SEPARATOR.'autoload.php'); 
	 include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'php_ga_addtransaction.php'); 
	 
	 ?><script>
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: php ga server2server order tracking sent'); 
	  }
	  </script><?php
	 
	 }
	 catch (Exception $e)
	 {
		 ?><script>
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: php ga server 2 server order tracking ERROR'); 
	  }
	  </script><?php
	 }
	 
	 
 }
 if ((empty($this->params->server2server)) || ($this->params->server2server === 2))
 if ($this->params->universalga === 2) {
	 $this->isPureJavascript = true; 
	 ob_start(); 
	 //we will indeed print this data at the very end from within analytics_last.php so we get the data per thank-you page URL
	
	 //gtag.js: 
	 	if ($order_total < 0) {
			?><script>
			gtag('event', 'refund', { 'transaction_id': '<?php echo $this->escapeSingle($idformat); ?>'<?php 
			$app = JFactory::getApplication();
			if ($app->getName() === 'administrator') {
				?> ,'non_interaction': true <?php
			}
			?> });
			</script>
			<?php
			
		}
		else {
	 ?><script>
	 gtag('event', 'purchase', { <?php
	 if (!empty($this->params->google_adwords_id)) { ?>
	 'send_to': ['<?php echo $this->params->google_analytics_id; ?>', '<?php echo $this->params->google_adwords_id; ?>/<?php echo $this->params->adwrods_conversion_id; ?>'],
	 <?php } else { ?>
	 'send_to': '<?php echo $this->params->google_analytics_id; ?>',
	 <?php } ?>
     'transaction_id': '<?php echo $this->escapeSingle($idformat); ?>',
	 'affiliation':  '<?php echo $this->escapeSingle($this->vendor['company']); ?>',<?php 
		    $app = JFactory::getApplication();
			if ($app->getName() === 'administrator') {
				?> 
	'non_interaction': true, <?php
			} ?>
	 'value': <?php echo number_format($order_total, 2, '.', ''); ?>,             
	 'currency': '<?php echo $this->escapeSingle($this->order['details']['BT']->currency_code_3); ?>',	 
	 'tax': '<?php echo number_format($this->order['details']['BT']->order_tax, 2, '.', ''); ?>',
     'shipping': '<?php echo number_format($this->order['details']['BT']->order_shipment, 2, '.', ''); ?>',
	 'coupon': '<?php if (!empty($this->order['details']['BT']->coupon_code)) echo $this->escapeSingle($this->order['details']['BT']->coupon_code); ?>',
     'items': <?php  
	  $max = count($this->order['items']); 
	  $i = 0; 
	  $items = array(); 
	  foreach ($this->order['items'] as $key=>$order_item) 
	  { 
	  
	  $product_id = $order_item->pid; 
	    $i++; 
	  if (empty($order_item->category_name)) $order_item->category_name = ''; 
	  if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name; 
	  
	  $json = array(); 
	    $json['id'] = (string)$product_id; 
	    $json['name'] = $order_item->order_item_name;
        $json['list_name'] = 'Purchase'; 
		$json['brand'] = ''; 
		$json['category'] = $order_item->category_name;
        $json['variant'] = $order_item->order_item_sku;
		$json['list_position'] = $i; 
		$json['price'] = (string)number_format($order_item->product_final_price, 2, '.', '');
        $json['quantity'] = (string)number_format($order_item->product_quantity , 0, '.', ''); 
		if (!empty($this->order['details']['BT']->coupon_code)) 
        $json['coupon'] = $this->order['details']['BT']->coupon_code;
	    $items[] = $json; 
	  
	  }
	  echo json_encode($items, JSON_PRETTY_PRINT); 
      ?> });
	 
	 
<?php 

	 $root = Juri::root(); 
	 if (substr($root, -1) !== '/') $root .= '/'; 
	 if (empty($this->params->page_url)) $this->params->page_url = '/thank-you'; 
	 if (substr($this->params->page_url, 0, 1) === '/') $cart_path = substr($this->params->page_url, 1); 
	 else {
		 $cart_path = $this->params->page_url; 
		 $this->params->page_url = '/'.$this->params->page_url;
	 }
	 if (empty($this->params->page_title)) $this->params->page_title = 'OPC Checkout'; 
	 if (empty($GLOBALS['_gtag'])) {
	 $GLOBALS['_gtag'] = new stdClass(); 
	 $GLOBALS['_gtag']->page_title = $this->params->page_title; 
	 $GLOBALS['_gtag']->page_location = $root.$cart_path; 
	 $GLOBALS['_gtag']->page_path = $this->params->page_url; 
	 
	 }


if (!empty($this->params->google_adwords_id)) { ?>

  gtag('event', 'conversion', { <?php
	  	 if (!empty($this->params->google_adwords_id)) { ?>
	 'send_to': ['<?php echo $this->params->google_analytics_id; ?>', '<?php echo $this->params->google_adwords_id; ?>/<?php echo $this->params->adwrods_conversion_id; ?>'],
	 <?php } else { ?>
	 'send_to': '<?php echo $this->params->google_analytics_id; ?>',
	 <?php } ?>
      'value': <?php echo number_format($order_total, 2, '.', ''); ?>,
      'currency': '<?php echo $this->escapeSingle($this->order['details']['BT']->currency_code_3); ?>',	 
      'transaction_id': '<?php echo $this->escapeSingle($idformat); ?>',
	  <?php 
			$app = JFactory::getApplication();
			if ($app->getName() === 'administrator') {
				?> 'non_interaction': true <?php
			}
			?>
  });

<?php } ?>
	 
	 if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Purchase.'); 
	  }
	 
	 </script>
	 <?php
		}
	 
	 
	
	   $order_js = ob_get_clean(); 
	   $GLOBALS['GTAG_ORDER_JS'] = $order_js; 
	
	 
 }
 else
 {
 
 
?>
<script type="text/javascript">
//<![CDATA[
  
  
  // if universtal analytics is initialized
  if (typeof ga != 'undefined')
   {
	   
	  
	   
      <?php 
	  $app = JFactory::getApplication();
	  
			if ($app->getName() === 'administrator') {
				
				
	  ?>
		ga('OPCTracker.set', 'nonInteraction', true);
	  <?php
		}
			//include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ga_create.php'); 
	  ?>
	
      <?php 
	  // if normal ecommerce tracking enabled: 
	  if (!empty($this->params->ec_type)) 
	  { 
	  // if normal ec
	   //added to _first: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_init.php'); 	
 	   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_addtransaction.php'); 	
		
	foreach ($this->order['items'] as $key=>$order_item) 
	{ 
   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each 
   if (empty($order_item->category_name)) $order_item->category_name = ''; 
   if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name;  
   
    include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_additem.php'); 	
    
	} 
		
		// maybe we are missing action here ??
	   //added to _last:  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_send.php'); 
		////added later: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php'); 
	
	  }  //if normal ec
	  else 
	  {   //if enhanced
	
		// enhanced ecommerce GA
		//added to _first: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_init.php'); 
		
		
		if ($order_total < 0)
		{
		include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_refund.php'); 
		
		
		
		}
		else
		{		
			foreach ($this->order['items'] as $key=>$order_item) 
			{ 
			// add item might be called for every item in the shopping cart
			// where your ecommerce engine loops through each item in the cart and
			// prints out _addItem for each 
			if (empty($order_item->category_name)) $order_item->category_name = ''; 
			if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name;  
   
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_addproduct.php'); 
			
			
			
			} 
			//end of foreach
			$action = 'purchase'; 
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_action.php'); 
	    

		
		}
	  //added later: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php'); 

	  
	}  // end if enhanced ec
	
	?>
	
	  

	
   
   }
   else
   {

	


  
  <?php 
  
  
  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'_gat_addtrans.php');  

  
  foreach ($this->order['items'] as $key=>$order_item) { 
   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each 
    if (empty($order_item->category_name)) $order_item->category_name = ''; 
   if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name;  
   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'_gat_additem.php');  
   
   
   
   
   } 
   ?>
   
   
   if (typeof pageTracker != 'undefined')
   {
   pageTracker._trackTrans(); //submits transaction to the Analytics servers
   

  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: _gat pageTracker _trackTrans'); 
	  }
   }

   
   
   }

  
//]]>
</script>

<?php 
 }
 // server2server or javascript tracking...