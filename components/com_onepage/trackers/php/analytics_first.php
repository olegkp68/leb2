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
$this->params->universalga = (int)$this->params->universalga; 

if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 

$root = JUri::root(false); 

$tracker_name = 'OPCTracker'; //.$idformat; 
if ($this->params->universalga === 2) {
	$this->isPureJavascript = true; 
	$first_analytics_id = $this->params->google_analytics_id;
	if (strpos($this->params->google_analytics_id, ',') !== false) {
	  $xa = explode(',', $this->params->google_analytics_id); 
	  foreach ($xa as $google_analytics_id) {
		  $google_analytics_id = trim($google_analytics_id); 
		  if (empty($google_analytics_id)) continue; 
		  $first_analytics_id = $google_analytics_id; 
		  break; 
	  }
	}
	
	?><!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo urlencode($first_analytics_id); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments)};
  gtag('js', new Date());
  <?php
  if (strpos($this->params->google_analytics_id, ',') !== false) {
	  $xa = explode(',', $this->params->google_analytics_id); 
	  foreach ($xa as $google_analytics_id) {
		  $google_analytics_id = trim($google_analytics_id); 
		  if (empty($google_analytics_id)) continue; 
		  
		 ?> if (typeof navigator.sendBeacon !== 'undefined') {
   gtag('config', <?php echo json_encode($google_analytics_id); ?>, {"transport_type": "beacon"});
  }
  else {
	  gtag('config', <?php echo json_encode($google_analytics_id); ?>);
  } <?php
		  
	  }
  } else { ?>
  if (typeof navigator.sendBeacon !== 'undefined') {
   gtag('config', '<?php echo $this->params->google_analytics_id; ?>', {"transport_type": "beacon"});
  }
  else {
	  gtag('config', '<?php echo $this->params->google_analytics_id; ?>');
  }
  <?php
  }
  ?>
  
  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Analytics loaded with page view.'); 
	  }
	  
  <?php if (!empty($this->params->google_adwords_id)) { 
  
		   $obj = new stdClass(); 
		   $obj_set = false; 
		   $obj->transport_type = 'beacon';
		   //$obj->send_page_view = true; 
		   if (!empty($this->params->track_user_id)) {
		    $obj->user_id = (int)JFactory::getUser()->get('id'); 
			$obj_set = true; 
		   } 
		  if (!empty($this->params->anon_ip)) {
		   $obj->anonymize_ip = true; 
		   $obj_set = true; 
		  }
  ?>
  <?php if ($obj_set) { ?>
		if (typeof navigator.sendBeacon !== 'undefined') {
		gtag('config', '<?php echo $this->params->google_adwords_id; ?>', <?php echo json_encode($obj, JSON_PRETTY_PRINT); ?>); 
		}
		else {
		<?php unset($obj->transport_type); ?>
		gtag('config', '<?php echo $this->params->google_adwords_id; ?>', <?php echo json_encode($obj, JSON_PRETTY_PRINT); ?>); 
		}
  <?php } else { ?>
		if (typeof navigator.sendBeacon !== 'undefined') {
	     gtag('config', '<?php echo $this->params->google_adwords_id; ?>', { "transport_type": "beacon" }); 
		}
		else {
			gtag('config', '<?php echo $this->params->google_adwords_id; ?>'); 
		}
  <?php } ?>
		
		 if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	     {
	       console.log('OPC Tracking gtag: Adwords page view sent.'); 
	     }
	 <?php } 
	 
	 
	 if (!empty($this->params->foreignlinkstracking)) { 
	 ?>
	 
	 
	 /* <![CDATA[ */
	   function trackOutboundLink (url) {
		   
		   if (typeof navigator.sendBeacon !== 'undefined') {
		   var data = {
    'event_category': 'outbound',
    'event_label': url,
    'transport_type': 'beacon',
    'event_callback': function() {document.location = url;}
     };
		   }
		   else {
		   var data = {
    'event_category': 'outbound',
    'event_label': url,
	'transport_type': 'xhr',
    'event_callback': function() {document.location = url;}
     };		   
		   }
	 
	gtag('event', 'click', data );
  
  return false; 
	  }
	  

	  	  <?php 
	  /*
	  
	   if (typeof jQuery !== 'undefined') {
	   
		jQuery(document).ready( function() {
	jQuery('a').click( function(event) { 
	   var el = this; 
	   if (typeof this.href !== 'undefined') {
		   <?php $root = JUri::root(false); 
		         ?> var detectedRoot = <?php echo json_encode($root); ?>; 
			if (this.href.indexOf(detectedRoot) < 0) {
				event.preventDefault();
				if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
				{
					console.log('OPC Tracking gtag: Outbond URL tracking.'); 
				}
				
				return trackOutboundLink(this.href); 
			}
		   
	   }
	   return true; 
	   }
	   );
	   }
	   ); 
	} 
	*/
	?>
	
	/* ]]> */
	 <?php } ?>
</script>


<?php
if (!empty($this->params->addtocarttracking)) { ?>

<script>
/* <![CDATA[ */
	var productQueryUrl = '<?php echo $root; ?>index.php?option=com_onepage&view=xmlexport&task=getproduct&format=opchtml&pidformat=<?php echo $this->params->pidformat; ?>&pid_prefix=<?php echo urlencode($this->params->pid_prefix); ?>&pid_suffix=<?php echo urlencode($this->params->pid_suffix); ?><?php 
	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');
	$lang = OPCloader::getLangCode(); 
	if (!empty($lang)) echo '&lang='.$lang; 
	?>'; 

	
	if (typeof jQuery !== 'undefined') {
		jQuery(document).ready( function() {
			
			
			var cartForms = jQuery("form.product"); 
			if (cartForms.length == 0) return; 
			cartForms.each( function() {
				var productform = jQuery(this); 
				
				
				
				var jelcart = productform.find('button[name="addtocart"], input[name="addtocart"], a[name="addtocart"]');
				if (jelcart.length > 0) {
					
		
		if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null)) 
							{
								console.log('Add to cart GA Tracking Attached'); 
							}
		
		jelcart.click(function() {
			try {
			if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null)) 
							{
								console.log('Add to cart GA Tracking Click Detected'); 
							}
			
			
			if (gtag === 'undefined') return; 
			
			
			
					
					var virtuemart_product_id = productform.find('input[name="virtuemart_product_id[]"]').val();
					if ((typeof virtuemart_product_id !== 'undefined') && (virtuemart_product_id !== 'undefined'))
					{
						if ((typeof _productTrackingData !== 'undefined') && (typeof _productTrackingData[virtuemart_product_id] !== 'undefined')) 
						{
							var productData = _productTrackingData[virtuemart_product_id]; 
							var gaData = {
							'ecomm_prodid': [productData.productPID],
							'ecomm_pagetype': 'cart',
							'ecomm_totalvalue': productData.productPrice,
							'ecomm_currency': productData.productCurrency_currency_code_3,
							'items': [
							{
								"id": productData.productPID,
								"name": productData.name,
								"list_name": "Add To Cart Tracking",
								"brand": "",
								"category": productData.productCategory,
								"list_position": 1,
								"quantity": 1,
								"price": productData.productPrice
							}
							]
							};
					
							gtag('event', 'add_to_cart', gaData); 
							if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null)) 
							{
								console.log('Add to cart GA Tracking', gaData); 
							}
						}
						else {
							
							
							
						var myUrl = productQueryUrl+'&virtuemart_product_id='+virtuemart_product_id; 
						
						
						
						
						jQuery.ajax({ 
						type: 'GET',
						cache: false, 
						dataType: 'json',
						timeout: '10000', 
						url: myUrl, 
						data: []
						}).done( function (data, testStatus ) {
							
							
							
						if ((typeof data !== 'undefined') && (data !== null)) {
							if (typeof data.productCurrency_currency_code_3 !== 'undefined') {
								
								
								var gaData = {
							'ecomm_prodid': [data.productPID],
							'ecomm_pagetype': 'cart',
							'ecomm_totalvalue': data.productPrice,
							'ecomm_currency': data.productCurrency_currency_code_3,
							'items': [
							{
								"id": data.productPID,
								"name": data.name,
								"list_name": "Add To Cart Tracking",
								"brand": "",
								"category": data.productCategory,
								"list_position": 1,
								"quantity": 1,
								"price": data.offers.price
							}
							]
							}
							
					
							gtag('event', 'add_to_cart', gaData); 
							if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null)) {
								console.log('Add to cart GA Tracking', gaData); 
							}
								
								
								
							}
						}
						}).fail( function(err) {
							console.log(err); 
						}); 

						
					}
					}
			
			} catch(e) {
			return true; 
		}
		});
		
		}
	});
})
}

/* ]]> */
</script>




<?php
}
	
}
else {
?>
<script type="text/javascript">
//<![CDATA[
   
   // if google universal analytics enabled + it is not initialized + old GA is not created
   if ((<?php echo $uga; ?>) && ((typeof ga == 'undefined') && ((typeof _gat == 'undefined'))))
   {
    <?php 
	include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ga_init.php'); 
    
	?>
	
	
   }
   
   
   // if universal analytics is not initialized, check if OLD GA is initialized
  if (((typeof gaJsHost == 'undefined') || (typeof _gat == 'undefined')) && (typeof ga == 'undefined'))
   {
   
      <?php include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'_gat_init.php'); ?>
	  
	  
	  
   }
   


  if (typeof ga != 'undefined')
   {
      <?php 
	  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ga_create.php'); 
	  ?>
	 
	
      <?php 
	  // if normal ecommerce tracking enabled: 
	  if (!empty($this->params->ec_type)) 
	  { 
	  // if normal ec
	   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_init.php'); 	
	  //if normal ec
	  } 
	  else 
	  {   //if enhanced
		include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ec_init.php'); 
	  }
	 ?>
  
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

   }
  
//]]>
</script>

<?php 
}
