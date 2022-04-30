<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id:couponField.tpl.php 431 2006-10-17 21:55:46 +0200 (Di, 17 Okt 2006) soeren_nb $
* @package VirtueMart
* @subpackage themes
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
* @author Erich Vinson
* http://virtuemart.net
*/



?>



<div style="width: 100%; clear: both;"></div>
<div id="couponcode_field" class="cart-view"> 
<?php
// If you have a coupon code, please enter it here:
?>  
	<form method="post" id="userForm" name="enterCouponCode" action="<?php echo JRoute::_('index.php'); ?>">
	<div class="wrapper_coupon"> 
	<div class="input_coupon_div">
   <input type="text" name="coupon_code" autocomplete="off" id="coupon_code" size="20"  class="coupon_input" alt="<?php echo $this->coupon_text ?>" value="" placeholder="<?php echo $this->coupon_text; ?>" />
	</div>
    <div class="details-button">
	<button id="submit_coupon_button" class="cuponb button " type="submit"><?php echo OPCLang::_('COM_VIRTUEMART_SAVE'); ?></button>
    
    </div>
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="cart" />
    <input type="hidden" name="task" value="setcoupon" />
    <input type="hidden" name="controller" value="cart" />
	</div>
	</form>
		
</div>
