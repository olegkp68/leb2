<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
OPC for Virtuemart by RuposTel.com
*/



?>

<div id="couponcode_field" style="float: left; width: 100%; ">
<?php
// If you have a coupon code, please enter it here:
?>  
	
	<div class="coupon_wrapper">
	<div class="coupon_in">
    <input type="text" name="coupon_code" autocomplete="off" id="coupon_code" size="20"  class="coupon_input" placeholder="<?php echo htmlentities($this->coupon_text); ?>" value="" />
	</div>
    <span class="detailsbutton_opc">
	<input class="buttonopc" id="submit_coupon_button" type="submit" value="<?php echo OPCLang::_('COM_VIRTUEMART_SAVE'); ?>" onclick="return Onepage.setCouponAjax(this);"  />
    
    </span>

	</div>
	
		
</div>

	

