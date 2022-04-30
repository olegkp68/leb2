<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* 
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

$checked = '';

if (!empty($method->sandbox)) {
	$mode = 'sandbox'; 
}
else {
	$mode = 'production'; 
}

$config = array(
	'kount'=>(int)$method->kount, 
	'mode' => $mode,
	'threed' => (int)$method->threed,
	'prefillzip' => (int)$method->prefillzip,
	'log_response' => (int)$method->log_response,
	'always_open' => (int)$method->always_open,
	'methodType' => $method->methodtype
	
	);

/*
$x = debug_backtrace(); 
foreach ($x as $l) echo $l['file'].' '.$l['line']."<br />\n"; 
die(); 
 */
$virtuemart_paymentmethod_id = $method->virtuemart_paymentmethod_id; 
$costDisplay = false;
if ($pluginSalesPrice) {
	$pluginPriceWithTaxDisplay = $currency->priceDisplay( $pluginPriceWithTax );
	$pluginPriceWithoutTaxDisplay = $currency->priceDisplay( $pluginPriceWithoutTax );
	$costDisplay = true;	
}
$tokenHtml = '';
				/*
				static $clientToken; 
				if (empty($clientToken)) {
					
					try {
						$clientToken = ''; 
					}
					catch(Exception $e) {
						 
					}
				ob_start(); 
				 ?><br class="br_br"/>
				 <input type="hidden" name="braintree_currency_iso" id="braintree_currency_iso" value="<?php echo htmlentities($this->getCurrencyISO($cart)); ?>" />
				 <input <?php 
				 
				 $merchantAccountId = $this->getMerchantIdPercurrency($m, $cart); 
				 echo ' data-currentid="'.htmlentities($merchantAccountId).'" '; 
				 ?> data-tokenurl="<?php 
				 echo htmlentities(JRoute::_('index.php?option=com_virtuemart&view=vmplg&task=pluginNotification&virtuemart_paymentmethod_id='.(int)$virtuemart_paymentmethod_id.'&cmd=gettoken&nosef=1&time='.time(), false));
				 
				 
				 ?>" type="hidden" name="clienttoken" id="braintree_clienttoken" value="<?php echo htmlentities($clientToken); ?>" /><?php
				$tokenHtml = ob_get_clean(); 
				}
				*/
if (empty($method->always_open)) {
?>
<label for="payment_id_<?php echo $virtuemart_paymentmethod_id; ?>" id="label_payment_<?php echo $virtuemart_paymentmethod_id; ?>" onclick="return braintreeHelper.clicked(this);" class="label_payment <?php 
	
	if (!empty($method->always_open)) {
		echo 'was_clicked '; 
	}
	else {
		echo 'wasnot_clicked '; 
	}
	
	
	?>"><span class="vmpayment">
<?php

?>

<?php

if (!empty($method->payment_logos)) { ?><img src="<?php 
$root = JUri::root(); 
if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
echo $root.'/images/virtuemart/payment/'.$method->payment_logos; ?>" >
<?php 
}
?>
<?php
if (!empty($costDisplay)) {
	?><span class="vmpayment_cost_fee cena_s_dph hide_on_desktop"> (<?php echo $pluginPriceWithTaxDisplay; ?>)</span>
	<span class="vmpayment_cost_fee cena_bez_dph hide_on_mobile"> (<?php echo $pluginPriceWithoutTaxDisplay; ?>)</span>
	<?php
}	
?></span></label>
<?php 
}
?>

<span style="display:none;height:1px;width:1px;">
<input data-braintree="<?php echo htmlentities(json_encode($config)); ?>" type="radio" name="virtuemart_paymentmethod_id" id="payment_id_<?php echo $virtuemart_paymentmethod_id; ?>"   value="<?php echo $virtuemart_paymentmethod_id; ?>" <?php echo $checked; ?> data-braintreemethod="<?php echo $method->methodtype; ?>" />
</span>

<div class="braintree_container_wrap" id="braintree_container_wrap_<?php echo $virtuemart_paymentmethod_id; ?>">
<?php 
if ($method->methodtype === 'card') {
	?><h3 class="securecard">Secure Card Payment</h3><?php
}
?>
<div style="box-sizing:border-box;" id="braintree-dropin-container-<?php echo $virtuemart_paymentmethod_id; ?>" class="braintree-dropin-container"></div>

<div class="confirm_btn_wrap confirm_btn_wrap_card full_wrap" id="pay_by_card_<?php echo $virtuemart_paymentmethod_id; ?>">
<button type="submit" class="confirm_button_green buttonopc myGreenBackground opcbutton" id="braintree_button_<?php echo $virtuemart_paymentmethod_id; ?>" value="Complete Order" onclick="return braintreeHelper.onBraintreeSubmitClick(event, this, <?php echo (int)$virtuemart_paymentmethod_id; ?>);" autocomplete="off" data-id="confirmbtn_button"><span><?php echo JText::_($method->pay_btn); ?></span>
</button>
</div>
</div>
<?php 

 
				
echo $tokenHtml; 



