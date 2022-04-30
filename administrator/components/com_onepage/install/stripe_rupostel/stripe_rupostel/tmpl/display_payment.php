<?php  

defined ('_JEXEC') or die();

$method = $viewData['plugin']; 
$m = $method; 
$me = $viewData['stripe_rupostel'];
$config = array(
	'kount'=>(int)$method->kount, 
	'mode' => $mode,
	'threed' => (int)$method->threed,
	'prefillzip' => (int)$method->prefillzip,
	'log_response' => (int)$method->log_response,
	'always_open' => (int)$method->always_open,
	'methodtype' => $method->methodtype,
	'publicKey' => $me->getPublicKey($method),
	'country' => $method->stripe_country
	);
$pid = (int)$viewData['plugin']->virtuemart_paymentmethod_id; 	
	/*
 
$intent = $me->getPaymentIntent($method, $cart->prices['billTotal'], 'eur'); 
var_dump($intent); die(); 
*/

JFactory::getLanguage()->load('plg_vmpayment_authorizenet', JPATH_SITE); 


?>
<div id="stripe_wrap_<?php echo (int)$pid; ?>" <?php  
if ($m->methodtype === 'googlePay') {
//stAn - we'll check for compatiblity before showing this	
echo ' style="display: none;" '; }
?>>
<input type="radio" name="virtuemart_paymentmethod_id" data-stripe="1" data-stripeconfig="<?php echo htmlentities(json_encode($config)); ?>"
       id="payment_id_<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>"
       value="<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>" <?php echo $viewData ['checked']; ?>>
<label for="payment_id_<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>">

    <span class="vmpayment">
        <?php if (!empty($viewData['payment_logo'] )) { ?>
	        <span class="vmpayment_logo"><?php echo $viewData ['payment_logo']; ?> </span>
        <?php } ?>
	    <span class="vmpayment_name"><?php echo $viewData['plugin']->payment_name; ?></span>
	    <?php if (!empty($viewData['plugin']->payment_desc )) { ?>
		    <span class="vmpayment_description"><?php echo $viewData['plugin']->payment_desc; ?></span>
	    <?php } ?>
	    <?php if (!empty($viewData['payment_cost']  )) { ?>
		    <span class="vmpayment_cost"><?php echo vmText::_ ('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') .  $viewData['payment_cost']  ?></span>
	    <?php } ?>
    </span>
	<?php echo $viewData['relatedBanks']; ?>
	
	
</label>
<?php  if ($m->methodtype === 'googlePay') { ?>
	<div class="car_form" style="width:100%; float: left; clear: both;">
	<div id="payment-request-button_<?php echo (int)$pid; ?>">
		
	</div>
	</div>
<?php } ?>
<?php  if ($m->methodtype === 'card') { ?>	
	<div class="car_form" style="width:100%; float: left; clear: both;">
	
<style>
 .cardInput { background-color: white; float: left; clear: both; min-width: 200px; min-height: 30px; border: 1px solid #dd; text-align: middle; }
 .cardLabel, .card-errors, .cardAll { float: left; clear: both; width: 100%; }
 
</style>
<?php if (!empty($m->cardformsingle)) { ?>
<label for="cardNumber_<?php echo (int)$pid; ?>" class="cardLabel"><?php echo JText::_('VMPAYMENT_PAYPAL_CC_CCNUM'); ?></label>
<div id="cardNumber_<?php echo (int)$pid; ?>" class="cardNumber cardInput" ></div>
<label for="cardCvc_<?php echo (int)$pid; ?>" class="cardLabel"><?php echo JText::_('VMPAYMENT_PAYPAL_CC_CVV2'); ?></label>
<div id="cardCvc_<?php echo (int)$pid; ?>" class="cardCvc cardInput"></div>
<label for="cardExpiry_<?php echo (int)$pid; ?>" class="cardLabel"><?php echo JText::_('VMPAYMENT_PAYPAL_CC_EXDATE'); ?></label>
<div id="cardExpiry_<?php echo (int)$pid; ?>" class="cardExpiry cardInput"></div>
<?php 
}
else {
	?><div id="card-element_<?php echo (int)$pid; ?>" class="cardAll cardInput"></div><?php
}
/*
	<div id="card-element2" style="background-color: white; line-height:20px;border: 1px solid #ddd; margin-left:10px; padding: 5px; width:100%;">
    <!-- Elements will create input elements here -->
  </div>
  */
?>

  <!-- We'll put the error messages in this element -->
  <div id="card-errors_<?php echo (int)$pid; ?>" role="alert" class="card-errors"></div>

  </div>
<?php 
 }
 
 if ($m->sandbox) {
					?><br /><div style="width: 100%; clear: both;float:left;" class="stripe_sandbox_msg">Stripe is in sandbox mode.</div><?php
				}
				
?>
</div>