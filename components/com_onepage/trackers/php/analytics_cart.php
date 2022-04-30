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
	 $root = Juri::root(); 
	 if (substr($root, -1) !== '/') $root .= '/'; 
	 if (empty($this->params->page_url_cart)) $this->params->page_url_cart = '/cart'; 
	 if (substr($this->params->page_url_cart, 0, 1) === '/') $cart_path = substr($this->params->page_url_cart, 1); 
	 if (empty($this->params->page_title_cart)) $this->params->page_title_cart = 'OPC Cart'; 
	 if (empty($GLOBALS['_gtag'])) {
	 $GLOBALS['_gtag'] = new stdClass(); 
	 $GLOBALS['_gtag']->page_title = $this->params->page_title_cart; 
	 $GLOBALS['_gtag']->page_location = $root.$cart_path; 
	 $GLOBALS['_gtag']->page_path = $this->params->page_url_cart; 
	 
	 }
	 
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
        $json['list_name'] = 'Cart Products'; 
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
	
	?>
	<script>
	
	var gtagCartItems = <?php echo json_encode($items, JSON_PRETTY_PRINT); ?>;
	
	gtag('event', 'begin_checkout', {
	  'currency': '<?php echo $this->escapeSingle($this->currency['currency_code_3']); ?>',	 
	  'items':  gtagCartItems });
	  
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Cart View.'); 
	  }
	  
	  
	  

function callAfterPaymentSelectGTAG()
{
	if (typeof Onepage == 'undefined') return; 
	if (typeof jQuery == 'undefined') return; 
	if (typeof ga == 'undefined') return; 
	
	var payment_id = Onepage.getPaymentId();
	
	if (payment_id == 'payment_id_0') return; 
	if (payment_id == 0) return; 
	
	var my_label = payment_id; 
	
	
	var label = jQuery("label[for='payment_id_"+payment_id+"']"); 
	if (label != null)
	if (typeof label.text != 'undefined')
	{
	  	var my_label2 = label.text(); 
		if (my_label2.length > 0) my_label = my_label2; 
		
		my_label = my_label.split("\r\r\n").join(' ').split("\r\n").join(" ").split("\n").join(" ").trim(); 
	}
	
	onCheckoutOptionGTAG(3, my_label, 'payment method'); 
	Onepage.op_log('OPC GTAG Tracking: Payment selected '+my_label+', step 3'); 
}

function onCheckoutOptionGTAG(step, checkoutOption, optionName) {

  
  
  gtag('event', 'set_checkout_option', {
  "checkout_step": step,
  "checkout_option": optionName,
  "value": checkoutOption
  });
  
}

function callAfterShippingSelectGTAG()
{
	
	 
	 
	if (typeof Onepage == 'undefined') return; 
	if (typeof jQuery == 'undefined') return; 
	if (typeof ga == 'undefined') return; 
	
	
	 var ship_id = Onepage.getInputIDShippingRate();
	 
	if (ship_id == 'choose_shipping') return; 
	if (ship_id == 'shipment_id_0') return; 
	
	var my_label = ship_id; 
	
	
	var label = jQuery("label[for='"+ship_id+"']"); 
	if (label != null)
	if (typeof label.text != 'undefined')
	{
	  	var my_label2 = label.text(); 
		if (my_label2.length > 0) my_label = my_label2; 
		
		my_label = my_label.split("\r\r\n").join(' ').split("\r\n").join(" ").split("\n").join(" ").trim(); 
	}
	
	
	onCheckoutOptionGTAG(2, my_label, 'shipping method'); 
	Onepage.op_log('OPC GTAG Tracking: Shipping selected '+my_label+', step 2'); 
	 
}

function onOpcErrorMessageGTAG(msg, cat) {
  gtagLog('OPC GTAG Tracking: Error detected '+msg+' category '+cat); 
  onCheckoutOptionGTAG(5, cat+': '+msg, 'error'); 
  
  gtag('event', 'exception', {
  'description': cat+': '+msg,
  'fatal': false   // set to true if the error is fatal
	});
}
function gtagLog(msg) {

if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log(msg); 
	  }	
}
function callSubmitFunctGTAG()
{
	onCheckoutOptionGTAG(4, 'OPC Confirm Button Clicked', 'confirm button'); 
	Onepage.op_log('OPC GTAG Tracking: Confirm order button clicked, step 4'); 
}

if (typeof addOpcTriggerer != 'undefined')
{
  addOpcTriggerer('callAfterPaymentSelect', callAfterPaymentSelectGTAG); 
  addOpcTriggerer('callAfterShippingSelect', callAfterShippingSelectGTAG); 
   addOpcTriggerer('callSubmitFunct', callSubmitFunctGTAG); 
   addOpcTriggerer('onOpcErrorMessage', onOpcErrorMessageGTAG); 
  
}
</script>
	  
	  
	  
	
	<?php
}
else {

if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 


$idformat = $this->idformat; 
$tracker_name = 'OPCTracker'; //.$idformat; 
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
//    include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_addimpression.php'); 	
    ?>
	
	
	
	
	<?php
   
	} 
	?>
	if (typeof ga != 'undefined') {
	ga('<?php echo $tracker_name.'.'; ?>ecommerce:setAction', 'checkout');	
		<?php
	    include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_send.php'); 
		////added later: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php'); 
	?>

	if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: ecommerce send'); 
	  }
	}
	
<?php
	  }  //if normal ec
	  else 
	  {   //if enhanced
	
		// enhanced ecommerce GA
		//added to _first: include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_init.php'); 
		
			?>

	
<?php

		
		
		{		
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
			$action = 'checkout'; 
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_action.php'); 
	    
		include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_opc_actions.php'); 
									?>
	
	
<?php

		
		}
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