<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_opcard
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 
$extJs = ' var op_age_alert = \''.JText::_('MOD_OPCARD_ALERT_AGE', true).'\'; ';
$document = JFactory::getDocument();
$document->addScriptDeclaration('//<![CDATA[ '."\n".$extJs."\n".'//]]>');
?>
<div class="moduleOPcard">
<div class="notification">
	<span><?php echo $notification; ?></span>
	<span><?php echo $control; ?></span>
</div>

<div>
	<div class="birth_date_div">
		<div class="cardLabel"><span><?php echo JText::_('MOD_OPCARD_FRONTEND_DATE_BIRTH'); ?></span></div>
		<div class="cardField"><?php
		if (!class_exists('vmJsApi'))
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php'); 
		$cal = vmJsApi::jDate('', 'birth_date', 'birth_date_field', true, '1910'); 
		$cal = str_replace(JText::_('COM_VIRTUEMART_NEVER'), '', $cal); 
					  $cal = str_replace('vmicon vmicon-16-logout icon-nofloat js-date-reset', 'jdateimg', $cal); 
					  $dn = date('Y-m-d', time()); 
					  $cal = str_replace('value=""', 'value="'.$dn.'"', $cal); 
					//$cal = str_replace('name=""', 'name="'.JText::_('MOD_OPCARD_FRONTEND_DATE_BIRTH').'"', $cal); 
					//$cal = str_replace('onblur=""', 'onblur="javascript:return op_check1()"', $cal); 
					//$cal = JHTML::calendar('', 'my_calendar', 'pickup_date_input', '%d-%m-%Y', ''); 
					//echo JHTML::calendar('','cal_field_name','cal_field_id','%Y-%m-%d');
		echo $cal;
		?></div>
	</div>
	<div class="card_num" id="national_id_div">
		<div class="cardLabel"><span><?php echo JText::_('MOD_OPCARD_FRONTEND_CARD_NUM'); ?></span></div>
		<div class="cardField"><input type="text" name="national_id" id="national_id_field" onclick="javascript:return true"/></div>
	</div>
	<div class="card_holder" id="national_id_name_div">
		<div class="cardLabel"><span class="name_cardholder"><?php echo JText::_('MOD_OPCARD_FRONTEND_CARDHOLDER'); ?></span></div>
		<div class="cardField"><input type="text" name="national_id_name" id="national_id_name_field" /></div>
	</div>
</div>
</div>
<script type="text/javascript">
addOpcTriggerer('callSubmitFunct', 'op_check1'); 
</script>
<?php 
