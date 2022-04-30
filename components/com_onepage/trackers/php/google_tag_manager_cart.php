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
<script>
/* <![CDATA[ */
//cart visit event
if (typeof dataLayer == 'undefined')
	dataLayer = []; 
<?php

$product = $this->product; 
$this->params->adwords_remarketing = true; 

include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'datalayer_cart.php'); 
 
$this->isPureJavascript = true; 

?>
/* ]]> */
</script>

<script>

<?php
$step = JRequest::getVar('step', -1); 
if (!empty($step)) { $step++; }

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
$is_multi_step = OPCconfig::get('is_multi_step', false); 

if ($is_multi_step) {
	if ($step <= 0) $step = 1; 
	if ($step === 1) {
		//cart
	?>	onCheckoutOptionGTM(<?php echo $step; ?>, ''); 
	<?php
	}
	
	if ($step === 2) {
		//shipment
	?>	onCheckoutOptionGTM(<?php echo $step; ?>, ''); 
	<?php
	}
	
	if ($step === 3) {
		//payemnt
	?>	onCheckoutOptionGTM(<?php echo $step; ?>, ''); 
	<?php
	}
	
	if ($step > 3) {
		//confirm
	?>	onCheckoutOptionGTM(<?php echo $step; ?>, ''); 
	<?php
	}
	
	
} 
else {
?>

if (typeof addOpcTriggerer != 'undefined')
{
  addOpcTriggerer('callAfterPaymentSelect', callAfterPaymentSelectGTM); 
  addOpcTriggerer('callAfterShippingSelect', callAfterShippingSelectGTM); 
   addOpcTriggerer('callSubmitFunct', callSubmitFunctGTM); 
   addOpcTriggerer('onOpcErrorMessage', onOpcErrorMessageGTM); 
  
}


<?php 
}

?>


if (typeof addOpcTriggerer != 'undefined') {
//onPQUpdate
addOpcTriggerer('onPQUpdate', GTMcallBeforeProductQuantityUpdate); 
}

</script>