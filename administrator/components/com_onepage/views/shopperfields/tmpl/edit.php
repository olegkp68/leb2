<?php 
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include(__DIR__.DIRECTORY_SEPARATOR.'js.php'); 


	  
require(__DIR__.DIRECTORY_SEPARATOR.'checkboxes.php'); 



$document = JFactory::getDocument(); 
$opc_cr_type = OPCconfig::get('opc_cr_type', ''); 
$business_selector = OPCconfig::get('business_selector', ''); 
$password_clear_text = OPCconfig::get('password_clear_text', ''); 
$business_fields = OPCconfig::get('business_fields', array()); 
$custom_rendering_fields = OPCconfig::get('custom_rendering_fields', array()); 

$per_order_rendering = OPCconfig::get('per_order_rendering', array()); 
$opc_ajax_fields = OPCconfig::get('opc_ajax_fields', null); 
$admin_shopper_fields = OPCconfig::get('admin_shopper_fields', array()); 
$render_as_hidden = OPCconfig::get('render_as_hidden', array()); 
$render_in_third_address = OPCconfig::get('render_in_third_address', array()); 
$html5_fields = OPCconfig::get('html5_fields', array()); 
$html5_autocomplete = OPCconfig::get('html5_autocomplete', array()); 
$html5_fields_extra = OPCconfig::get('html5_fields_extra', array()); 
$html5_placeholder = OPCconfig::get('html5_placeholder', array()); 
$html5_validation_error = OPCconfig::get('html5_validation_error', array()); 
$business_obligatory_fields = OPCconfig::get('business_obligatory_fields', array()); 
$shipping_obligatory_fields = OPCconfig::get('shipping_obligatory_fields', array()); 

$do_not_display_business = OPCconfig::get('do_not_display_business', null); 
$opc_switch_rd = OPCconfig::get('opc_switch_rd', false); 
$opc_btrd_def = OPCconfig::get('opc_btrd_def', false); 
$opc_copy_bt_st = OPCconfig::get('opc_copy_bt_st', false); 
$business_fields2 = OPCconfig::get('business_fields2', array()); 
$is_business2 = OPCconfig::get('is_business2', false); 
$business2_value = OPCconfig::get('business2_value', ''); 

$one_or_the_other = OPCconfig::get('one_or_the_other', array()); 
$one_or_the_other2 = OPCconfig::get('one_or_the_other2', array()); 

$checkbox_products = OPCconfig::get('checkbox_products', array()); 
$opc_address_history = OPCconfig::get('opc_address_history', false); 
$ignore_address_history = OPCconfig::get('ignore_address_history', array()); 

$estimator_fields = OPCconfig::get('estimator_fields', array()); 

$row = reset($this->ulist); 
$key = 0;

$sysdisabled = array('password', 'password2', 'username'); 

 $arr = array(); 
 
 $root = Juri::base(); 
		if (substr($root, -1) !== '/') {
		 $root .= '/'; 
		}
 $arr['url'] = $root.'index.php?option=com_onepage&view=shopperfields&task=alterfield'; 


$default = ''; 


 
?><config data-config="<?php echo htmlentities(json_encode($arr)); ?>"></config> 

<div id="vmMainPageOPC">
<form class="uk-form">
<fieldset class="adminformF" style="max-width: 100%; float: left; clear: both; width:100%;" data-uk-margin>
		 <?php
		   if (empty($this->ulist)) echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_INFO'); 
		   else
		   {
		   ?>
		
		  
		  <div class="admintable table table-striped" style="width: 100%; ">
		  <div class="row">
		   <legend><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_FIELDNAME').': '; 
		   
		    $title = $row->title; 
		 $title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $fieldTitle = htmlentities(strip_tags($title2.' ('.JText::_($row->name).')')); 
		 else $fieldTitle = $row->name; 
		 
		 echo $fieldTitle; 
		 
		   ?>
		   </legend>

		  

		   <?php
		   
		
		    {
				
		
			
	$res = array(); 
	$db = JFactory::getDBO(); 
	//$q = 'select `v`.`fieldtitle` as field_title, `v`.`fieldvalue` as field_value from `#__virtuemart_userfield_values` as v, `#__virtuemart_userfields` as f where `f`.`virtuemart_userfield_id` = `v`.`virtuemart_userfield_id` and `f`.`name` = \''.$db->escape($business_selector).'\''; 
	
	$q = 'select  `v`.`fieldtitle` as fieldtitle, `v`.`fieldvalue`, f.name as name, f.type, f.value from `#__virtuemart_userfield_values` as v, `#__virtuemart_userfields` as f where `f`.`virtuemart_userfield_id` = `v`.`virtuemart_userfield_id` and `f`.`name` = \''.$db->escape($row->name).'\''; 
	try
	{
	$db->setQuery($q); 
	$res = $db->loadAssocList(); 
	
	
	
	}
	catch(Exception $e)
	{
	
		$res = array(); 
		
	}
	
	//if (!empty($res)) 
	{
		
		
			   ?>
			   
			  
			   
	<div class="row "  data-name="<?php echo htmlentities($row->name); ?>" >
	  
		
	   
		<div class="controlwrap span1" style="font-size:1.5em;" >
		 <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="business_fields" data-value="<?php echo $row->name; ?>" <?php 
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $title; 
		 $title = htmlentities(strip_tags($title)); 
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" > 
		<?php
		switch ($row->name)
		{
		 
		  case 'password':
		  case 'password2':
		    echo $disabledfield; 
			
			break; 
		  default: 
			 if (!empty($business_selector) && ($row->name === $business_selector)) {
				 
				 
				 
				 echo $disabledfield; 
			 }
			 else
		     if ((!empty($business_fields)) && (in_array($row->name, $business_fields))) { echo $checkedfield; 	}
			else 
				echo $uncheckedfield; 
			 
			 break; 
			
		}
		?>
		
		</a>
		
		
		
		</div>
		
		 <div class="span11 labelwrap" >
		 <label class="hasTip " title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" ><?php $col1_label = htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS')); echo $col1_label; ?></label>
		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC'); ?></p>
		  <div class="key">
	     <p><?php 
		
		 if ($row->name == 'register_account')
		  {
		    echo '<br /><small>'.JText::_('COM_ONEPAGE_SHOPPERFIELDS_REGISTER_NOTE').'</small>';
		  }
		  if ($row->name == 'password2')
		  {
		    echo '<br /><small>'.JText::_('COM_ONEPAGE_SHOPPERFIELDS_PASSW2_NOTE').'</small><br /><input type="checkbox" name="password_clear_text" value="1" ';
			if (!empty($password_clear_text)) echo ' checked="checked" ';
			echo ' /> '.JText::_('COM_ONEPAGE_SHOPPERFIELDS_PASS_CLEAR'); 
		  }

		 ?></p>
	    </div>
		 
		 </div>
		
		
		 
		
</div>
	<?php } ?>
	    <div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="custom_rendering_fields" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $title; 
		 $title = htmlentities(strip_tags($title)); 
		 
		
	?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
	
	
	<?php
	$isr = false; 
	switch ($row->name)
		{
		  case 'email':
		  case 'email2':
		  case 'username':
		  case 'agreed':
		  case 'name': 
		  case 'password':
		  case 'register_account':
		  case 'customer_note':
		  case 'privacy':
		  case 'tos':
			$isr = true;
		    echo $disabledfield;
			break; 
		
		
		}
		
		if ((!empty($custom_rendering_fields)) && (in_array($row->name, $custom_rendering_fields))) { 
		$isr = true;
		echo $checkedfield; 
		}
		if (!$isr) {
			echo $uncheckedfield; 
		}
		
		?>
		</a>
		
		</div>
		
		 <div class="span11 labelwrap" >
		
		<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>"><?php $col2_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_CUSTOM'); echo $col2_label; ?></label>
		<p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC'); ?></p>
		</div>
		
		
		
		</div>
		
		
		  <div class="row">
		  <div class="controlwrap span1" style="font-size:1.5em;">
		  <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="per_order_rendering" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $title; 
		 
		$title= htmlentities(strip_tags($title)); 
		
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >  
		
		
		<?php
		$isr = false; 
		switch ($row->name)
		{
		  case 'email':
		  case 'email2':
		  case 'username':
		  case 'agreed':
		  case 'name': 
		  case 'password':
		  case 'register_account':
		  case 'customer_note':
		  case 'privacy':
		    $isr = true; 
		    echo $disabledfield; 
			break;
		
		}
		
		$cart_fields = array(); 
		if (defined('VM_VERSION') && (VM_VERSION >= 3))
		{
		
	
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php'); 
	    require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php');   
		
		$userFieldsModel = OPCmini::getModel ('userfields');
	    $userFields = $userFieldsModel->getUserFields(
				'cart',
				array(),
				array()
			);
		
		
		if (!empty($userFields))
		foreach ($userFields as $k=>$v)
		 {
		    $cart_fields[] = $v->name; 
		 }
		
		
		}
		
		
		
		if (in_array($row->name, $cart_fields))
		{
		 $isr = true; 
		 echo $checkedfield; 
		
		
		}
		else
		if (!empty($per_order_rendering))
		if (in_array($row->name, $per_order_rendering)) {
			$isr = true; 
			echo $checkedfield; 
		}
		
		if (!$isr) echo $uncheckedfield; 
	
		?>
		</a>
		  </div>
		  <div class="span11 labelwrap" >
		   <label class="hasTip" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER_ALT')); 
		    
			if (defined('VM_VERSION') && (VM_VERSION >= 3))
		   {
		     echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER_NOTE')); 
		   }
		   
		   ?>" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER')); 
		   
		     if (defined('VM_VERSION') && (VM_VERSION >= 3))
		   {
		     echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER_NOTE')); 
		   }
		   
		   ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER')); ?>" ><?php  $col3_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER'); 
		   echo $col3_label; 
		   
		   ?></label>
		  <p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER_NOTE'); ?></p>
		  </div>
		  
		</div>
		
		
		
		
	

		
		 <div class="row">
		  
		  <div class="controlwrap span1" style="font-size:1.5em;" >
		  <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="opc_ajax_fields" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 
		 
		  $title= htmlentities(strip_tags($title)); 
		
		
		?> class="hasTip" tooltip="<?php echo $title; ?>"  alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >  
		
		<?php
		$isr = false; 
		if (!empty($opc_ajax_fields))
		if (in_array($row->name, $opc_ajax_fields)) {
			$isr = true; 
			echo $checkedfield; 
		}
		
		if (!isset($opc_ajax_fields))
		 {
		    switch ($row->name)
		{
		  case 'address_1':
		  case 'address_2':
		  case 'zip':
		  case 'virtuemart_country_id':
		  case 'virtuemart_state_id': 
		    $isr = true; 
		    echo $checkedfield; 
			break; 
		
		}
		 }
		 
		 if (!$isr) echo $uncheckedfield; 
		 
		 ?>
		 </a>
		 </div>
		  <div class="span11 labelwrap" >
		  <label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" ><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD'); ?></label>
			
			<p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT'); ?></p>
			<?php 
			
			$col_al = JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD');
			?>
		 
		 </div>
		</div>
		
		
		 <div class="row">
		 <div class="controlwrap span1" style="font-size:1.5em;" >
		 <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="admin_shopper_fields" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $title; 
		 $title= htmlentities(strip_tags($title)); 
		 
		
	?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
	
	<?php
	if ((!empty($admin_shopper_fields)) && (in_array($row->name, $admin_shopper_fields))) echo $checkedfield; 
		else echo $uncheckedfield;
		
		?>
		
		</a>
		 </div>
		 <div class="span11 labelwrap" >
		  <label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" ><?php $col5_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER'); echo $col5_label; ?></label>
		  		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT'); ?></p>
		 </div>
		 
		</div>
		
		
		<div class="row">
		
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="render_as_hidden" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  alt="<?php echo $title; ?>" title="<?php echo $title; ?>" class="hasTip" >
		<?php
		if ((!empty($render_as_hidden)) && (in_array($row->name, $render_as_hidden))) echo $checkedfield; 
		else echo $uncheckedfield; 
		?>
		</a>
		</div>
		<div class="span11 labelwrap" >
			<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" ><?php $col6_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN'); echo $col6_label; ?></label>
			<p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN'); ?></p>
		</div>
		
		
		</div>
		
		
		<div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="render_in_third_address" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		if ((!empty($render_in_third_address)) && (in_array($row->name, $render_in_third_address))) echo $checkedfield; 
		else echo $uncheckedfield; 
		
		?>
		</a>
		</div>
		<div class="span11 labelwrap" >
		<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" ><?php $col7_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS'); echo $col7_label; ?></label>
		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS_DESC'); ?></p>
		</div>
		
		</div>
		
		
		
		
		<?php 
		
		
		if (!empty($opc_address_history))    { 
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php');  
		$BTrelevant = OPCUserFields::getEditableFieldsNames('BT'); 
		$STrelevant = OPCUserFields::getEditableFieldsNames('ST'); 
		
		?>
		<div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="ignore_address_history" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		
		if (!(in_array($row->name, $BTrelevant) || (in_array($row->name, $STrelevant)))) {
			
			 if (in_array($row->name, $ignore_address_history)) {
				 foreach ($ignore_address_history as $ke=>$te) {
					 if ($te === $row->name) {
						 unset($ignore_address_history[$ke]); 
					 }
				 }
				 
				 
				 OPCconfig::store('opc_vm_config', 'ignore_address_history', 0, $ignore_address_history); 
			 }
			 echo $disabledfield; 
			 
		}
		else {
		if ((!empty($ignore_address_history)) && (in_array($row->name, $ignore_address_history))) echo $checkedfield; 
		else echo $uncheckedfield; 
		}
		
		?>
		</a>
		</div>
		<div class="span11 labelwrap" >
		<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_IGNORE_HISTORY_FIELD')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_IGNORE_HISTORY_FIELD')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_IGNORE_HISTORY_FIELD')); ?>" ><?php $col7_label = JText::_('COM_ONEPAGE_IGNORE_HISTORY_FIELD'); echo $col7_label; ?></label>
		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_IGNORE_HISTORY_FIELD_DESC'); ?></p>
		</div>
		
		</div>
		
		
		<div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="private_address_fields" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		
		if (!(in_array($row->name, $BTrelevant) || (in_array($row->name, $STrelevant)))) {
			
			 if (in_array($row->name, $private_address_fields)) {
				 foreach ($private_address_fields as $ke=>$te) {
					 if ($te === $row->name) {
						 unset($private_address_fields[$ke]); 
					 }
				 }
				 
				 
				 OPCconfig::store('opc_vm_config', 'private_address_fields', 0, $private_address_fields); 
			 }
			 echo $disabledfield; 
			 
		}
		else {
		if ((!empty($private_address_fields)) && (in_array($row->name, $private_address_fields))) echo $checkedfield; 
		else echo $uncheckedfield; 
		}
		
		?>
		</a>
		</div>
		<div class="span11 labelwrap" >
		<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_PRIVATE_ADDRESS_FIELD')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_PRIVATE_ADDRESS_FIELD')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_PRIVATE_ADDRESS_FIELD')); ?>" ><?php $col7_label = JText::_('COM_ONEPAGE_PRIVATE_ADDRESS_FIELD'); echo $col7_label; ?></label>
		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_PRIVATE_ADDRESS_FIELD_DESC'); ?></p>
		</div>
		
		</div>
		
		<?php } ?>
		
		
		
		<div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="shipping_obligatory_fields" data-value="<?php echo $row->name; ?>" <?php
		
		
		$title = htmlentities(strip_tags($row->name)); 
		
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"  >
		<?php
		
		$ro = false; 
		$isr = false; 
		switch ($row->name)
		{
		  case 'email':
		  case 'email2':
		  case 'username':
		  case 'agreed':
		  case 'name': 
		  case 'password':
		  case 'password2':
		  case 'register_account':
		  case 'customer_note':
		  case 'privacy':
		  case 'tos':
		  
			$isr = true; 
		    echo $disabledfield; 
			$ro = true; 
			break; 
		
		}
		
		if (!$ro)
		if ($row->shipment != '1') {
			$isr = true; 
		    echo $disabledfield; 
		}
			
		if (!empty($shipping_obligatory_fields))
		if (in_array($row->name, $shipping_obligatory_fields)) {
			$isr = true; 
			echo $checkedfield; 
		}
		if (!$isr) echo $uncheckedfield; 
		
		?>
		</a>
		</div>
		
		 <div class="span11 labelwrap" >
		<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" ><?php $col4_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING'); echo $col4_label; ?></label>
		 <p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT'); ?></p>
		</div>
		
		
		
		</div>
		
		
		<div class="row">
			<div class="controlwrap span1" style="font-size:1.5em;" >
			   <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="business_obligatory_fields" data-value="<?php echo $row->name; ?>" <?php
		$ro = false;
		
		
		
		
		 $title = $row->title; 
		 $title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		  $title= htmlentities(strip_tags($title)); 
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		$ro = false; 
		$isr = false; 
		switch ($row->name)
		{
		  case 'email':
		  case 'email2':
		  case 'username':
		  case 'agreed':
		  case 'name': 
		  case 'password':
		  case 'password2':
		  case 'register_account':
		  case 'customer_note':
		  case 'privacy':
		  case 'tos':
		    $isr = true; 
		    echo $disabledfield; 
			$ro = true; 
			break; 
		
		}
		if ($ro) {
			//$isr = true; 
		    //echo $disabledfield; 
		}
			
		if (!$isr)
		if ((!empty($business_fields)) && (!in_array($row->name, $business_fields))) {
			$isr = true; 
			echo $disabledfield;
		}
		if (!$isr)
		if ((!empty($business_obligatory_fields)) && (in_array($row->name, $business_obligatory_fields))) {
			echo $checkedfield; 
			$isr = true; 
		}; 
		if (!$isr) echo $uncheckedfield; 
		
		?></a>
			</div>
			<div class="span11 labelwrap" >
			 <label><?php echo JText::_('COM_ONEPAGE_REQUIRED_FOR_BUSINESS'); ?></label>
			</div>
		
		
		</div>
		
		
		<div class="row">
			<div class="controlwrap span1" style="font-size:1.5em;" >
			   <a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="estimator_fields" data-value="<?php echo $row->name; ?>" <?php
		$ro = false;
		
		
		
		
		 $title = $row->title; 
		 $title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		  $title= htmlentities(strip_tags($title)); 
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		$ro = false; 
		$isr = false; 
		switch ($row->name)
		{
		  case 'email':
		  case 'email2':
		  case 'username':
		  case 'agreed':
		  case 'name': 
		  case 'password':
		  case 'password2':
		  case 'register_account':
		  case 'customer_note':
		  case 'privacy':
		  case 'tos':
		    $isr = true; 
		    echo $disabledfield; 
			$ro = true; 
			break; 
		
		}
		if ($ro) {
			//$isr = true; 
		    //echo $disabledfield; 
		}
			
		if (!$isr)
		if ((!empty($business_fields)) && (!in_array($row->name, $business_fields))) {
			$isr = true; 
			echo $disabledfield;
		}
		if (!$isr)
		if ((!empty($estimator_fields)) && (in_array($row->name, $estimator_fields))) {
			echo $checkedfield; 
			$isr = true; 
		}; 
		if (!$isr) echo $uncheckedfield; 
		
		?></a>
			</div>
			<div class="span11 labelwrap" >
			 <label><?php echo JText::_('COM_ONEPAGE_SHIPPING_ESTIMATOR_FIELDS'); ?></label>
			</div>
		
		
		</div>
		
		
			<?php
		
		if (!empty($this->acyfields)) {
			?><div class="row">
			<div class="controlwrap span1" style="font-size:1.5em;" >
			  <select data-value="select" data-type="select" onchange="return alterfield(this);" style="max-width:100px;" class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" data-name="acymailing_fields" data-fn="<?php echo htmlentities($row->name); ?>" name="acymailing_fields[<?php echo $row->name; ?>]">
			  <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
			  <?php foreach ($this->acyfields as $k=>$f) {
					?><option <?php 
					$default = ''; 
					$con = OPCconfig::getValue('acymailing_fields', $row->name, 0, $default); 
					
					
					if ($con === $k) {
						echo ' selected="selected" '; 
					}
					
					?> value="<?php echo $k; ?>"><?php echo $k; ?></option>
					<?php
			  }
			  ?>
			</select>
			</div>
			
			<div class="span11 labelwrap" >
			<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" ><?php $col9_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS'); echo $col9_label; ?></label>
			<p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC'); ?></p>
			<?php
			$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col9_label.': '.$title; 
		 $title= htmlentities(strip_tags($title)); 
			
			?>
			</div>
			
			
		
			
		</div>
		
		
		
		

			
			<?php
		}
		
		
		
		
		
		{
			$title = htmlentities(str_replace('{field}', $row->name, JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONE_OR_THE_OTHER_TITLE'))); 

	$con = OPCconfig::get('one_or_the_other', array()); 

			?><div class="row">
			<div class="controlwrap span1" style="font-size:1.5em;" >
			  <select data-value="select" data-type="select" onchange="return alterfield(this);" style="max-width:100px;" class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" data-name="one_or_the_other" data-fn="<?php echo htmlentities($row->name); ?>" name="one_or_the_other[<?php echo $row->name; ?>]">
			  <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
			  <?php foreach ($this->alllist as $k=>$f) {
				 
					?><option <?php 
					$default = ''; 
				
					
					
					if (((!empty($con)) && (((isset($con[$f->name])) && ($con[$f->name] === $row->name)) || (isset($con[$row->name]) && ($con[$row->name] === $f->name))) ))  {
						
						echo ' selected="selected" '; 
					}
					
					?> value="<?php echo htmlentities($f->name); ?>"><?php echo JText::_($f->name); ?></option>
					<?php
			  }
			  ?>
			</select>
			</div>
			
			<div class="span11 labelwrap" >
			<?php
			$label = htmlentities(str_replace('{field}', $row->name, JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONE_OR_THE_OTHER_TITLE'))); 
			?>
			<label class="hasTip"><?php echo $label; ?></label>
			<p class="desc"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONE_OR_THE_OTHER_PAIR'); ?></p>
			<?php
			$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $title; 
		 $title= htmlentities(strip_tags($title)); 
			
			?>
			</div>
			
			
		
			
		</div>
		
		
		
		

			
			<?php
		}
		
		
		$ftypes = $this->ftypes; 
				
				
				
				
		
		if (!empty($ftypes)) {
					foreach ($ftypes as $t) {
							?>
							<div class="row">
							<?php
							$title = $t->header; 
				$title2 = $t->header2; 
				?>
				
				<div class="controlwrap span1" style="font-size:1.5em;" >
				<a href="#" onclick="return alterfield(this);" data-type="checkbox" type="checkbox" name="field_<?php echo $t->config_subname; ?>[]" value="<?php echo $row->name; ?>" <?php
		$ro = false; 
		
		//if (!empty($shipping_obligatory_fields))
		//if (in_array($row->name, $shipping_obligatory_fields)) echo '" checked="checked'; 
		
		$title = htmlentities(strip_tags($t->header2.' ('.$row->name.')')); 		
		
		
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"  <?php 
		if (isset($t->fields)) {
			if (isset($t->fields->{$row->name})) echo ' checked="checked" '; 
		}
		
		?> />
							
			
				</div>
		
			<div class="span11 labelwrap" >
				
				<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title2); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title); ?>" ><?php $col8_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title2; echo $col8_label; ?></label>
			</div>		
							
							
							</div>
							<?php 
					}
		}
		?>
		
		
		
	
		
		
		
		
	
		
		
					
		</div>
		
		
		
		
			<div class="row">
		<div class="controlwrap span1" style="font-size:1.5em;" >
				
				<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" ><?php $col_html5_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS'); echo $col_html5_label; ?></label>				
				
				</div>
		
		 <div class="span11 labelwrap" >
			<?php if (!in_array($row->name, $sysdisabled)) { 
		
		 
		?>
			<input  onchange="return alterfield(this);" data-name="html5_fields" data-fn="<?php echo htmlentities($row->name); ?>" data-value="select" type="text" name="html5_fields[<?php echo $row->name; ?>]" value="<?php 
		
			
		if (!empty($html5_fields))
		if (!empty($html5_fields[$row->name])) echo htmlentities($html5_fields[$row->name]); 
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS_TYPE'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>" class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		<br />
			<?php } ?>
		<input <?php if (in_array($row->name, $sysdisabled)) { echo ' readyonly="readonly" '; } ?> onchange="return alterfield(this);" data-name="html5_autocomplete" data-fn="<?php echo htmlentities($row->name); ?>" data-value="select"  type="text" name="html5_autocomplete[<?php echo $row->name; ?>]" value="<?php 
		
		
			
		if ((!empty($html5_autocomplete)) && (!empty($html5_autocomplete[$row->name]))) {
			$autocomplete = $html5_autocomplete[$row->name];
			echo htmlentities($autocomplete); 
		}
		else {
			$autocomplete = ''; 
			$key = $row->name; 
			switch ($row->name) {
			   case 'username':
				$autocomplete = 'username email'; 
				break; 
			   case 'password':
			   case 'password2':
			   case 'opc_password':
			   case 'opc_password2': 
			    $autocomplete = 'new-password'; 
			    break; 
			  case 'email':
			  case 'email2':
			    $autocomplete = 'email'; 
			    break; 
			  case 'first_name':
			  case 'shipto_first_name':
			    $autocomplete = 'given-name'; 
			    break; 
			  case 'last_name':
			  case 'shipto_last_name':
			    $autocomplete = 'family-name'; 
			    break; 
			  case 'middle_name':
			  case 'shipto_middle_name':
			    $autocomplete = 'additional-name';
			    break; 
			case 'address_1':
			case 'shipto_address_1':
			    $autocomplete = 'street-address';
			    break; 
			case 'address_2':
			case 'shipto_address_2':
			    $autocomplete = 'address-line-2'; 
			    break; 				
			case 'virtuemart_country_id':
			case 'shipto_virtuemart_country_id':
			    $autocomplete = 'country-name'; 
			    break; 	
			case 'zip':
			case 'shipto_zip':
			    $autocomplete = 'postal-code';
			    break; 
			case 'company':
			case 'shipto_company':
			    $autocomplete='organization';
			    break; 		
			case 'phone':
			case 'phone_1':
			case 'phone_2':
			case 'phone1':
			case 'phone2':
			case 'shipto_phone':
			case 'shipto_phone1':
			case 'shipto_phone2':
			    $autocomplete = 'tel';
			    break; 	
			case 'fax':
			case 'shipto_fax':
				$autocomplete = 'fax tel';
			    break;
			case 'virtuemart_state_id':
			case 'shipto_virtuemart_state_id':
			    $autocomplete = 'address-level1';
			    break; 
			case 'city':
			case 'shipto_city':
			case 'town':
			case 'shipto_town':
				$autocomplete= 'address-level2';
			    break; 			
			case 'title':
				$autocomplete = ' honorific-prefix'; 
			case 'birthday': 
			case 'birth_day': 
				$autocomplete = 'bday'; 
			default:
			    $autocomplete = $row->name; 
			    break; 
			  
			  
		   }
		   
		   echo htmlentities($autocomplete); 
		}
	
	
		
		
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS_AUTOCOMPLETE'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>" class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		
		<br />
		<input onchange="return alterfield(this);" data-name="html5_fields_extra" data-fn="<?php echo htmlentities($row->name); ?>" data-value="select" type="text" name="html5_fields_extra[<?php echo $row->name; ?>]" value="<?php 
		

			
		if (!empty($html5_fields_extra))
		if (!empty($html5_fields_extra[$row->name])) echo htmlentities($html5_fields_extra[$row->name]); 
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS_EXTRA'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>"  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		<br />
		<input onchange="return alterfield(this);" data-name="html5_placeholder" data-fn="<?php echo htmlentities($row->name); ?>" data-value="select" type="text" name="html5_placeholder[<?php echo $row->name; ?>]" value="<?php 
		
			$placeholder = ''; 
		if (!empty($html5_placeholder))
		if (!empty($html5_placeholder[$row->name])) {
			$placeholder = $html5_placeholder[$row->name]; 
			echo htmlentities($html5_placeholder[$row->name]); 
		}
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_PLACEHOLDER'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>"  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		
		<br />
		<input onchange="return alterfield(this);" data-name="html5_validation_error" data-fn="<?php echo htmlentities($row->name); ?>" data-value="select" type="text" name="html5_validation_error[<?php echo $row->name; ?>]" value="<?php 
		
		$validation_error = ''; 
		if (!empty($html5_validation_error))
		if (!empty($html5_validation_error[$row->name])) {
			$validation_error = $html5_validation_error[$row->name];
			echo htmlentities($html5_validation_error[$row->name]); 
		}
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_VALIDATION_ERROR_MSG'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>"  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		
		 </div>
		</div>
		
		<?php
			}
			?></div></div><?php		
		   }
		 ?>
		</fieldset>
		
		
		
		
		<?php
		
		if (!in_array($row->name, $sysdisabled)) 
		if (!empty($res)) {
			?>
		
		<fieldset class="adminform" style="width:100%;">
		 <legend><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE'); ?></legend>
		<?php
		
		$business_selector =  OPCconfig::get('business_selector', ''); 
		if (!empty($business_selector) && ($business_selector !== $row->name)) {


			$t = JText::_('COM_ONEPAGE_ALREADY_COFIGURED_FIELD'); 
			$t = str_replace('{fieldname}', $business_selector, $t); 
			echo $t; 
			
			?>
			<a href="#" onclick="return alterfield(this);" data-newstate="0" data-type="checkbox" data-name="clear_business" data-value="clear_business" data-name="clear_business" alt="<?php echo htmlentities(JText::_("JCLEAR")); ?>" style="color:red;"><?php echo JText::_("JCLEAR"); ?> <i class="fa fas fa-times"></i></a>
			<br /><?php
		}
		else {
		
		if (!empty($res)) { ?>
		
		  <table class="table table-striped admintable table table-striped">
		    
		<tr>
	    <td>
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE_WHEN'); ?>
		</td>
		<td>
		<?php echo $fieldTitle; 
		
		/*
		?>
		
		<select name="business_selector" id="bussiness_selector">
		<option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php 
		  
		  unset($row); reset($this->ulist); 
		  foreach ($this->ulist as $key=>$row)
		  {
			   $arr = array('select', 'checkbox', 'radio', 'select-one'); 
			  if (!in_array($row->type, $arr)) continue; 
			  $later = array('virtuemart_country_id', 'virtuemart_state_id','opc_vat_field', 'pluginistraxx_euvatchecker' ); 
			  if (in_array($row->name, $later)) continue; 
			  
			   $title = JText::_($row->title).' ('.$row->name.')'; 
			   $title= htmlentities(strip_tags($title)); 
			  ?><option <?php 
			  if (!empty($business_selector)) if ($business_selector === $row->name) echo ' selected="selected" '; 
			  ?>value="<?php echo $row->name; ?>"><?php echo $title; ?></option>
			  
			  <?php
		  }
		  
		?>
		
		</select>
		
		*/
		?>
		</td>
		</tr>
		<tr>
		<td>
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE_HIDE'); ?>
		</td>
		<td>
		<select data-value="select" data-type="multiple" onchange="return alterfield(this);" data-fn="<?php echo htmlentities($row->name); ?>" data-name="business_fields2" name="business_fields2[]" id="business_fields2" multiple="multiple" class="vm-chzn-select" >
		<option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php 
		
		
		  foreach ($this->alllist as $key2=>$row2)
		  {
			 
			  
			  $title = JText::_($row2->title).' ('.$row2->name.')'; 
			  ?><option <?php 
			  
			  
			  if (((!empty($business_fields2[$row->name])) && (in_array($row2->name, $business_fields2[$row->name]))) || (in_array($row2->name, $business_fields2))) echo ' selected="selected" ';
			  else			  
				  if (!empty($is_business2))
				  {
					 // if (!empty($business_fields)) if (in_array($row->name, $business_fields)) echo ' selected="selected" ';
				  }
			  ?>value="<?php echo $row2->name; ?>"><?php echo htmlentities(strip_tags($title)); ?></option>
			  
			  <?php
		  }
		?>
		
		</select>
		</td>
		
		
		
		
		</tr>
		<tr>
		<td >
		<a data-data-type="bool" href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="is_business2" data-value="<?php echo $row->name; ?>"  type="checkbox" value="1" name="is_business2"  >
		<?php if (!empty($is_business2)) echo $checkedfield; 
			else echo $uncheckedfield;		?>
		 
		</a>
		</td>
		<td><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE_SG'); ?></td>
		
		
		</tr>
		<tr>
		<td>
		
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE_VALUE'); ?>
		</td>
		<td>
		 <select data-name="business2_value" name="business2_value" data-value="select" data-type="checkbox" onchange="return alterfield(this);"  data-fn="singleselect" >
		 <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		 <?php 
		 /*
		 if (empty($business_selector)) { ?>
		 
		  <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED_CLICK_SAVE'); ?></option>
		  
		 <?php }
else 
	*/
{

	
	
	foreach ($res as $k=>$row2)
	{
		
		if ($row2['type'] == 'checkbox') {
	  ?><option value="1"><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_CHECKED'); ?></option>
	  <option value=""><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_NOTCHECKED'); ?></option>
	  <?php
	  break; 
	  }
	
		
		if (empty($row2['fieldvalue']) && (empty($row2['fieldtitle']))) continue; 
		?><option <?php 
		if (!empty($business2_value))
		{
			$test = (int)$row2['fieldvalue']; 
			if (($business2_value === $row2['fieldvalue']) || ($test === $business2_value))
			{
				echo ' selected="selected" '; 
			}
		}
		$name = $row2['name']; 
		
		?> value="<?php echo htmlentities($row2['fieldvalue']); ?>"><?php echo htmlentities(strip_tags(JText::_($row2['fieldtitle'])).' - '.$name); ?> (<?php echo $row2['fieldvalue']?>)</option>
		<?php
	}
	?>
	
<?php } ?>
		 
		 </select>
		</td>
		
		
		</tr>
		<tr>
		<td colspan="2">
		
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_DEPENDENCE_DESC'); ?>
		</td>
		</tr>
		
	   
	   
		 </table>
		
		<?php 
		} 
		
		}
		?>
		</fieldset>
		
		<?php } 
		
		if (!in_array($row->name, $sysdisabled)) { 
		?>
		<fieldset>
		  
		  <legend><?php echo JText::_('COM_ONEPAGE_COUNTRY_FIELD_CONFIG'); ?></legend>
		  
		  <?php 
		  $n = 0; 
		  ?>
		  
		  <div class="admintable table table-striped" style="width: 100%; ">
		  <div class="row">
		    <div class="section_wrap row "  data-name="<?php echo htmlentities($row->name); ?>" >
			   <div class="controlwrap span2" style="font-size:1.5em;" >
			       <select onchange="return alterfield(this);" class="vm-chzn-select" name="country_field_required" multiple="multiple" data-config_name="country_field_required" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="0" data-type="select" data-value="select" data-fn="<?php echo htmlentities($row->name); ?>" >
				     <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php

		$n = 0; 
		$sel = OPCconfig::getValue('country_field_required', $row->name, $n, array()); 
		
		foreach($this->countries as $p)
		{
		 $selected = false;  
		 $id = (int)$p['virtuemart_country_id']; 
		 if (in_array($id, $sel)) $selected = true; 

		 ?> <option value="<?php echo (int)$p['virtuemart_country_id']; ?>" <?php if ($selected) { echo ' selected="selected" '; } 
		 ?>><?php echo JText::_($p['country_name']); ?></option>
		 <?php
		}
		
		?>
				   </select>
			   </div>
			   <div class="span10 labelwrap" >
			     <?php echo JText::_('COM_ONEPAGE_COUNTRY_FIELD_CONFIG_REQUIRED'); ?>
			   </div> 
			</div>
		  </div>
		  
		  <div class="row">
		    <div class="section_wrap row "  data-name="<?php echo htmlentities($row->name); ?>" >
			   <div class="controlwrap span2" style="font-size:1.5em;" >
			       <select onchange="return alterfield(this);" class="vm-chzn-select" name="country_field_shown" multiple="multiple" data-config_name="country_field_shown" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="<?php echo (int)$n; ?>" data-type="select" data-value="select" data-fn="<?php echo htmlentities($row->name); ?>" >
				     <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php

		$n = 0; 
		$sel = OPCconfig::getValue('country_field_shown', $row->name, $n, array()); 
		
		foreach($this->countries as $p)
		{
		 $selected = false;  
		 $id = (int)$p['virtuemart_country_id']; 
		 if (in_array($id, $sel)) $selected = true; 

		 ?> <option value="<?php echo (int)$p['virtuemart_country_id']; ?>" <?php if ($selected) { echo ' selected="selected" '; } 
		 ?>><?php echo JText::_($p['country_name']); ?></option>
		 <?php
		}
		
		?>
				   </select>
			   </div>
			   <div class="span10 labelwrap" >
			     <?php echo JText::_('COM_ONEPAGE_COUNTRY_FIELD_CONFIG_SHOWN'); ?>
			   </div>
			</div>
		  </div>
		  
		  
		  <div class="row">
		    <div class="section_wrap row "  data-name="<?php echo htmlentities($row->name); ?>" >
			   <div class="controlwrap span2" style="font-size:1.5em;" >
			       <select onchange="return alterfield(this);" class="vm-chzn-select" name="country_field_hidden" multiple="multiple" data-config_name="country_field_hidden" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="<?php echo (int)$n; ?>" data-type="select" data-value="select" data-fn="<?php echo htmlentities($row->name); ?>" >
				     <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php

		$n = 0; 
		$sel = OPCconfig::getValue('country_field_hidden', $row->name, $n, array()); 
		
		foreach($this->countries as $p)
		{
		 $selected = false;  
		 $id = (int)$p['virtuemart_country_id']; 
		 if (in_array($id, $sel)) $selected = true; 

		 ?> <option value="<?php echo (int)$p['virtuemart_country_id']; ?>" <?php if ($selected) { echo ' selected="selected" '; } 
		 ?>><?php echo JText::_($p['country_name']); ?></option>
		 <?php
		}
		
		?>
				   </select>
			   </div>
			   <div class="span10 labelwrap" >
			     <?php echo JText::_('COM_ONEPAGE_COUNTRY_FIELD_CONFIG_HIDDEN'); ?>
			   </div>
			</div>
		  </div>
		  
		  </div>
		</fieldset>
		
		<?php
		
		/*PER COUNTRY HTML5 CONFIG */
		?>
		<fieldset>
		 <legend><?php echo JText::_('COM_ONEPAGE_CONFIG_PER_COUNTRIES'); ?></legend>
		<?php
		
		$config_id = 0; 
		
		$country_config = OPCconfig::getValues('country_config', $row->name); 

		$max = count($country_config); 
		if (empty($country_config)) $country_config = array(); 
		
		foreach ($country_config as $k=>$v) {
			if ($v['config_subname'] !== $row->name) {
				unset($country_config[$k]); 
				continue; 
			}
		}
		
		//if (empty($country_config)) 
		{
			$tostore = new stdClass(); 
				$tostore->countries = array(); 
				$tostore->html5_autocomplete = $autocomplete; 
				$tostore->html5_fields_validation = ''; 
				$tostore->html5_placeholder = $placeholder; 
				$tostore->html5_validation_error = $validation_error; 
				$tostore->custom_css = ''; 
				$ax = array(); 
				$ax['config_ref'] = $max;
				$ax['value'] = json_encode($tostore); 
				$ax['last'] = true; 
				$country_config[] = $ax;
		}
		
		
		$last_config_id = 0; 
		foreach ($country_config as $r) {
			
			$config = json_decode($r['value'], false); 
			
		if (!empty($r['last'])) {
			$last_config_id++; 
			$r['config_ref'] = $last_config_id; 
		}
		
		$config_id = (int)$r['config_ref'];
		if ($config_id >= $last_config_id) {
		  $last_config_id = $config_id; 
		}
		
				ob_start(); 
		?><div class="row" data-config_id="[n]">
		<div class="controlwrap span1" style="font-size:1.5em;" >
				
				<label class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" ><?php $col_html5_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS'); echo $col_html5_label; ?></label>				
				
				</div>
		
		 <div class="span11 labelwrap" >
			<select data-value="collect" data-config_id="[n]"  class="vm-chzn-select" name="country_field_selected" data-name="country_field_selected" multiple="multiple" data-config_name="country_config" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="[n]" data-type="select" data-fn="<?php echo htmlentities($row->name); ?>">
				     <option value=""><?php echo JText::_('COM_ONEPAGE_NOT_CONFIGURED'); ?></option>
		<?php

		$n = 0; 
		$sel = (array)$config->countries;
		
		foreach($this->countries as $p)
		{
		 $selected = false;  
		 $id = (int)$p['virtuemart_country_id']; 
		 if (in_array($id, $sel)) $selected = true; 

		 ?> <option value="<?php echo (int)$p['virtuemart_country_id']; ?>" <?php if ($selected) { echo ' selected="selected" '; } 
		 ?>><?php echo JText::_($p['country_name']); ?></option>
		 <?php
		}
		
		?>
				   </select><br />
		
		
		<input data-value="collect" data-config_id="[n]" type="text" data-config_id="[n]" data-name="html5_autocomplete" name="html5_autocomplete[<?php echo $row->name; ?>]" value="<?php 
		
		
		$html5_autocomplete = $config->html5_autocomplete; 
			
		if ((!empty($html5_autocomplete)) && (!empty($html5_autocomplete))) {
			echo htmlentities($html5_autocomplete); 
		}
		else {
			$autocomplete = ''; 
			$key = $row->name; 
			switch ($row->name) {
			   case 'username':
				$autocomplete = 'username email'; 
				break; 
			   case 'password':
			   case 'password2':
			   case 'opc_password':
			   case 'opc_password2': 
			    $autocomplete = 'new-password'; 
			    break; 
			  case 'email':
			  case 'email2':
			    $autocomplete = 'email'; 
			    break; 
			  case 'first_name':
			  case 'shipto_first_name':
			    $autocomplete = 'given-name'; 
			    break; 
			  case 'last_name':
			  case 'shipto_last_name':
			    $autocomplete = 'family-name'; 
			    break; 
			  case 'middle_name':
			  case 'shipto_middle_name':
			    $autocomplete = 'additional-name';
			    break; 
			case 'address_1':
			case 'shipto_address_1':
			    $autocomplete = 'street-address';
			    break; 
			case 'address_2':
			case 'shipto_address_2':
			    $autocomplete = 'address-line-2'; 
			    break; 				
			case 'virtuemart_country_id':
			case 'shipto_virtuemart_country_id':
			    $autocomplete = 'country-name'; 
			    break; 	
			case 'zip':
			case 'shipto_zip':
			    $autocomplete = 'postal-code';
			    break; 
			case 'company':
			case 'shipto_company':
			    $autocomplete='organization';
			    break; 		
			case 'phone':
			case 'phone_1':
			case 'phone_2':
			case 'phone1':
			case 'phone2':
			case 'shipto_phone':
			case 'shipto_phone1':
			case 'shipto_phone2':
			    $autocomplete = 'tel';
			    break; 	
			case 'fax':
			case 'shipto_fax':
				$autocomplete = 'fax tel';
			    break;
			case 'virtuemart_state_id':
			case 'shipto_virtuemart_state_id':
			    $autocomplete = 'address-level1';
			    break; 
			case 'city':
			case 'shipto_city':
			case 'town':
			case 'shipto_town':
				$autocomplete= 'address-level2';
			    break; 			
			case 'title':
				$autocomplete = ' honorific-prefix'; 
			case 'birthday': 
			case 'birth_day': 
				$autocomplete = 'bday'; 
			default:
			    $autocomplete = $row->name; 
			    break; 
			  
			  
		   }
		   
		   echo htmlentities($autocomplete); 
		}
	
	
		?>" <?php
		
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS_AUTOCOMPLETE'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?> class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		
		<br />
		<input data-config_name="country_config" data-config_id="[n]"  data-name="html5_fields_validation" data-fn="<?php echo htmlentities($row->name); ?>" data-value="collect" type="text" name="html5_fields_validation[<?php echo $row->name; ?>]" value="<?php 
		
		$html5_fields_validation = $config->html5_fields_validation; 
			
		if (!empty($html5_fields_validation))
		if (!empty($html5_fields_validation)) echo htmlentities($html5_fields_validation); ?>" <?php
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS_VALIDATIONPATTER'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		<br />
		<input data-value="collect"  data-config_name="country_config" data-config_id="[n]"  data-name="html5_placeholder" data-fn="<?php echo htmlentities($row->name); ?>" type="text" name="html5_placeholder[<?php echo $row->name; ?>]" value="<?php 
		
		$html5_placeholder = $config->html5_placeholder; 
		if (!empty($html5_placeholder))
		if (!empty($html5_placeholder)) echo htmlentities($html5_placeholder); ?>" <?php
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_PLACEHOLDER'); 
		 $title = $col_html5_label.': '.$title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		
		<br />
		<input data-config_name="country_config" data-config_id="[n]"  data-name="html5_validation_error" data-fn="<?php echo htmlentities($row->name); ?>" data-value="collect" type="text" name="html5_validation_error[<?php echo $row->name; ?>]" value="<?php 
		
		$html5_validation_error = $config->html5_validation_error;
		if (!empty($html5_validation_error))
		if (!empty($html5_validation_error)) echo htmlentities($html5_validation_error); ?>" <?php
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_VALIDATION_ERROR_MSG'); 
		 $title = $title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
		
		<br />
		<label for="custom_css"><?php echo JText::_('COM_ONEPAGE_CSS_PER_COUNTRY_LABEL'); ?></label><br />
		<textarea cols="100" style="width:100%; min-height:100px;" data-config_name="country_config" data-config_id="[n]"  data-name="custom_css" data-fn="<?php echo htmlentities($row->name); ?>" data-value="collect" type="text" name="custom_css[<?php echo $row->name; ?>]" <?php 
		
		
		
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 $extr = JText::_('COM_ONEPAGE_CSS_PER_COUNTRY'); 
		 $title = $title.' '.$extr; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" placeholder="<?php echo htmlentities($extr); ?>" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" ><?php if (!empty($config->custom_css)) echo htmlentities($config->custom_css); ?></textarea>
		
		<br />
		
		
		<a class="btn btn-primary" data-name="country_config" data-value="collect" data-config_id="[n]" data-config_name="country_config" data-fn="<?php echo htmlentities($row->name); ?>" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="[n]" href="#" onclick="javascript: return alterfield(this)" ><?php echo JText::_('JAPPLY'); ?></a><br /> <br />
		<a class="btn btn-primary"  data-name="country_config" data-value="collect" data-config_id="[n]" data-config_name="country_config" data-fn="<?php echo htmlentities($row->name); ?>" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="[n]" href="#" onclick="javascript: return add_more_section(this)" ><?php echo JText::_('COM_ONEPAGE_ADD_MORE'); ?></a>
		<a class="btn btn-danger"  data-name="country_config" data-value="collect" data-config_id="[n]" data-config_name="country_config" data-fn="<?php echo htmlentities($row->name); ?>" data-config_sub="<?php echo htmlentities($row->name); ?>"  data-config_ref="<?php echo (int)$n; ?>" href="#" onclick="javascript: return remove_section(this)" ><?php echo JText::_('COM_ONEPAGE_REMOVE'); ?></a>
		<br /> <hr />
		 </div>
		</div><?php 
		  $html = ob_get_clean(); 
		  
		 if (!empty($r['last'])) {
		    $last_html = $html; 
		 }
		 $html = str_replace('"[n]"', '"'.$config_id.'"', $html); 
		 echo $html; 
		 //$config_id++; 
		}
		 
		 //$config_id++; 
		 
		 
		 ?>
		<repeathtml data-largest_config_id="<?php echo $config_id; ?>" data-html="<?php echo htmlentities(json_encode($last_html)); ?>"></repeathtml>
		</fieldset>
		<?php } ?>
		

</form>		
		
		
</div>		