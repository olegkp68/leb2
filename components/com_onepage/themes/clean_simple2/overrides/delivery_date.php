<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
if (!empty($calendard_gif)) {
$css = '
 input#opc_date_picker {
   background: white url('.$calendard_gif.') no-repeat 90% 5px !important;
   padding-right: 3px; 
 
}
';
JFactory::getDocument()->addStyleDeclaration($css); 
}
?>


<div class="op_inside">

<h3 class="payment_h3"><?php echo OPCLang::_('COM_ONEPAGE_CHOOSE_DESIRED_DELIVERY_DATE'); ?></h3>

<fieldset>
  <div class="pay_box">
    <div><p><?php echo OPCLang::_('COM_ONEPAGE_CHOOSE_DESIRED_DELIVERY_DATE_DESC'); ?></p></div>
    <div><input <?php echo $required; ?> type="text" value="<?php echo $now; ?>" id="opc_date_picker" class="opc_date_picker" name="opc_date_picker" />
	</div>
  </div>
</fieldset>
</div>