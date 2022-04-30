<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
		include(__DIR__.DIRECTORY_SEPARATOR.'js.php'); 
		
		


$opc_ajax_fields = OPCconfig::get('opc_ajax_fields', null); 

if (is_null($opc_ajax_fields)) {
			$opc_ajax_fields = array(); 
			$opc_ajax_fields[] = 'zip'; 
			$opc_ajax_fields[] = 'address_1'; 
			$opc_ajax_fields[] = 'address_2'; 
			$opc_ajax_fields[] = 'virtuemart_state_id'; 
			$opc_ajax_fields[] = 'virtuemart_country_id'; 
			OPCconfig::save('opc_ajax_fields', $opc_ajax_fields); 
}

$document = JFactory::getDocument(); 
$opc_cr_type = OPCconfig::get('opc_cr_type', ''); 
$business_selector = OPCconfig::get('business_selector', ''); 
$password_clear_text = OPCconfig::get('password_clear_text', ''); 
$business_fields = OPCconfig::get('business_fields', array()); 
$custom_rendering_fields = OPCconfig::get('custom_rendering_fields', array()); 

$per_order_rendering = OPCconfig::get('per_order_rendering', array()); 
$opc_ajax_fields = OPCconfig::get('opc_ajax_fields', array()); 
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
$render_registration_only = OPCconfig::get('render_registration_only', array()); 
$registration_obligatory_fields = OPCconfig::get('registration_obligatory_fields', array()); 
require(__DIR__.DIRECTORY_SEPARATOR.'checkboxes.php'); 


$document = JFactory::getDocument(); 
$document->addStyleDeclaration('
 .subhead-collapse { display: none; }
 header.header { display: none; }
 
'); 

?>

<?php 
 $arr = array(); 
 
 $root = Juri::base(); 
		if (substr($root, -1) !== '/') {
		 $root .= '/'; 
		}
 $arr['url'] = $root.'index.php?option=com_onepage&view=shopperfields&task=alterfield'; 
 
?><config data-config="<?php echo htmlentities(json_encode($arr)); ?>"></config>
<h1><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PANEL'); ?></h1>
<fieldset class="adminformF" style="max-width: 100%; float: left; clear: both;">
		 <?php
		   if (empty($this->ulist)) echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_INFO'); 
		   else
		   {
		   ?>
		 
		  <div style="overflow-x: scroll; max-width: 100%;">
		  <table class="admintable table table-striped" style="width: 100%; ">
		  <tr>
		   <th><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_FIELDNAME'); ?>
		   </th>
		   <th class="hasTip" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC')); ?>" ><?php $col1_label = htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS')); echo $col1_label; ?></th>
		   <th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC')); ?>"><?php $col2_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_CUSTOM'); echo $col2_label; ?></th>
		   <th class="hasTip" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ORDER_ALT')); 
		    
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
		   
		   ?></th>
		   <th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING_ALT')); ?>" ><?php $col4_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_SHIPPING'); echo $col4_label; ?></th>
		   
		    <th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD_ALT')); ?>" ><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD'); ?></th>
			
			
			<?php 
			
			$col_al = JText::_('COM_ONEPAGE_SHOPPERFIELDS_AJAXFIELD');
			?>
			
			
			<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER_ALT')); ?>" ><?php $col5_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADMIN_ADDSHOPPER'); echo $col5_label; ?></th>
			
			
			<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN')); ?>" ><?php $col6_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_HIDDEN'); echo $col6_label; ?></th>
			
			
				<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS')); ?>" ><?php $col7_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_THIRDADDRESS'); echo $col7_label; ?></th>
				
				<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONLYREGISTRATION_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONLYREGISTRATION_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONLYREGISTRATION_DESC')); ?>" ><?php $col_html6_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ONLYREGISTRATION'); echo $col_html6_label; ?></th>
				
				<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELD_DESC')); ?>" ><?php $col_html5_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_HTML5_FIELDS'); echo $col_html5_label; ?></th>
				
				
				
				
				
				<?php 
				
				if (!empty($this->acyfields)) {
					?><th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS_DESC')); ?>" ><?php $col9_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ACYFIELDS'); echo $col9_label; ?></th><?php
				}
				
				
				
				/*
				$cols = OPCconfig::getValues('vm_userfields'); 
				
				foreach ($cols as $row) {
				$data = @json_decode($row['value'], false); 
				if (!empty($data))
				if (!empty($data->path)) {
			    $title = 'COM_ONEPAGE_USERFIELDS_'.strtoupper($data->path); 
				$title = JText::_($title); 
				$title2 = str_replace('_', ' ', $title); 
				*/
				
				/*
				$ftypes = $this->ftypes; 
				if (!empty($ftypes)) {
					foreach ($ftypes as $t) {
				
				$title = $t->header; 
				$title2 = $t->header2; 
				?>
				<th class="hasTip" tooltip="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title2); ?>" title="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title); ?>" alt="<?php echo htmlentities(JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title); ?>" ><?php $col8_label = JText::_('COM_ONEPAGE_SHOPPERFIELDS_ADVANCED_IN').' '.$title2; echo $col8_label; ?></th>
				
				<?php } } 
				
				*/
				?>
			
		  </tr>
		  

		   <?php
		   
		   foreach ($this->ulist as $key=>$row)
		    {
				
			$skip = array('tos', 'agreed', 'customer_note'); 
			if (in_array($row->name, $skip)) continue; 
				
			 if (!$row->published) continue; 
			 
			 // the next line will filter core fields
			 //if (in_array($row->name, $this->clist)) continue;
			  //if ($row->published)

			   ?>
			   		<tr  data-name="<?php echo htmlentities($row->name); ?>" >
	    <td class="key">
	     <label><a href="#" onclick="return fieldedit(this);" data-name="<?php echo htmlentities($row->name); ?>"><?php 
		 $title = $row->title; 
		 $title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) echo htmlentities(strip_tags($title2.' ('.JText::_($row->name).')')); 
		 else echo $row->name; 
		 
		 ?></a><?php
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

		 ?></label>
	    </td>
	    <td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="business_fields" data-value="<?php echo $row->name; ?>" <?php 
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col1_label.': '.$title; 
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
			 if (!empty($business_selector) && ($row->name === $business_selector)) echo $disabledfield; 
			 else
		     if ((!empty($business_fields)) && (in_array($row->name, $business_fields))) { echo $checkedfield; 	}
			else 
				echo $uncheckedfield; 
			 
			 break; 
			
		}
		?>
		</a>
		
		</td>

	    <td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="custom_rendering_fields" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col2_label.': '.$title; 
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
		
		
		
		</td>
		
		
		  <td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="per_order_rendering" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col3_label.': '.$title; 
		 
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
		
		</td>
		
		
		
		
		
		 <td>&nbsp;
		</td>

		
		 <td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="opc_ajax_fields" data-value="<?php echo $row->name; ?>" <?php
		// case 'password2':
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col_al.': '.$title; 
		 
		  $title= htmlentities(strip_tags($title)); 
		
		
		?> class="hasTip" tooltip="<?php echo $title; ?>"  alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >  
		
		<?php
		$isr = false; 
		if (!empty($opc_ajax_fields))
		if (in_array($row->name, $opc_ajax_fields)) {
			$isr = true; 
			echo $checkedfield; 
		}
		
		if (empty($opc_ajax_fields))
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
		
		</td>
		
		
		 <td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="admin_shopper_fields" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col5_label.': '.$title; 
		 $title= htmlentities(strip_tags($title)); 
		 
		
	?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
	
	<?php
	if ((!empty($admin_shopper_fields)) && (in_array($row->name, $admin_shopper_fields))) echo $checkedfield; 
		else echo $uncheckedfield;
		
		?>
		
		</a>
		</td>
		
		
		<td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="render_as_hidden" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col6_label.': '.$title; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  alt="<?php echo $title; ?>" title="<?php echo $title; ?>" class="hasTip" >
		<?php
		if ((!empty($render_as_hidden)) && (in_array($row->name, $render_as_hidden))) echo $checkedfield; 
		else echo $uncheckedfield; 
		?>
		</a>
		
		
		
		</td>
		
		
		<td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="render_in_third_address" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col7_label.': '.$title; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		if ((!empty($render_in_third_address)) && (in_array($row->name, $render_in_third_address))) echo $checkedfield; 
		else echo $uncheckedfield; 
		
		?>
		</a>
		</td>
		
		
		<td><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="render_registration_only" data-value="<?php echo $row->name; ?>" <?php
		
			
		
		
		$title = $row->title; 
		$title2 = JText::_($row->title); 
		 
		 if ($title2 != $title) $title = $title2.' ('.JText::_($row->name).')'; 
		 else $title = $row->name; 
		 
		 
		 $title = $col_html6_label.': '.$title; 
		 $title= htmlentities(strip_tags($title)); 
		
		?>  class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" >
		
		<?php
		if ((!empty($render_registration_only)) && (in_array($row->name, $render_registration_only))) echo $checkedfield; 
		else echo $uncheckedfield; 
		
		?>
		</a>
		</td>
		
		<td>
		
		<a href="#" onclick="return fieldedit(this);" data-name="<?php echo htmlentities($row->name); ?>"><i class="far fa-edit"></i></a>
		
		</td>
		
		
			<?php
		
		if (!empty($this->acyfields)) {
			?><td>
				<a href="#" onclick="return fieldedit(this);" data-name="<?php echo htmlentities($row->name); ?>"><i class="far fa-edit"></i></a>
			
			
		
		</td>
			
			<?php
		}
		?>
		
		
		
		<?php 
		/*
		if (!empty($ftypes)) {
					foreach ($ftypes as $t) {
							?>
							<td>
							
							<input type="checkbox" name="field_<?php echo $t->config_subname; ?>[]" value="<?php echo $row->name; ?>" <?php
		$ro = false; 
		
		//if (!empty($shipping_obligatory_fields))
		//if (in_array($row->name, $shipping_obligatory_fields)) echo '" checked="checked'; 
		
		$title = htmlentities(strip_tags($t->header2.' ('.$row->name.')')); 		
		
		
		?> class="hasTip" tooltip="<?php echo $title; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"  <?php 
		if (isset($t->fields)) {
			if (isset($t->fields->{$row->name})) echo ' checked="checked" '; 
		}
		
		?> />
							
		<script>
		 console.log('<?php echo $t->header; ?>'); 
		 
		 
		</script>		
							</td>
							<?php 
					}
		}
		
		*/
		?>
		
		</tr>
		<tr><td><div style="clear: both; text-indent: 20px;"><?php echo JText::_('JOPTION_REQUIRED'); ?></div>
		<td>
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
		
		
		
		
		</td>
		<td class="req_custom">&nbsp;</td>
		<td class="req_order">&nbsp;</td>
		<td class="req_shipping"><a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="shipping_obligatory_fields" data-value="<?php echo $row->name; ?>" <?php
		
		
		$title = htmlentities(strip_tags($col4_label.' ('.$row->name.')')); 
		
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
		
		</td>
		<td class="req_ajax">&nbsp;</td>
		<td class="req_admin">&nbsp;</td>
		<td class="req_hidden">&nbsp;</td>
		<td class="req_third">&nbsp;</td>
		<td class="req_reg">
		
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-name="registration_obligatory_fields" data-value="<?php echo $row->name; ?>" <?php
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
		
		
			
		
		
		if ((!empty($registration_obligatory_fields)) && (in_array($row->name, $registration_obligatory_fields))) {
			echo $checkedfield; 
			$isr = true; 
		}; 
		if (!$isr) echo $uncheckedfield; 
		
		?></a>
		
		
		</td>
		<td class="req_html5">&nbsp;</td>	
		
		<?php if (!empty($this->acyfields)) { 
		?>
		<td class="req_acy" colspan="1">&nbsp;</td>
		<?php
		} ?>
		
		<?php
		/*
		if (!empty($ftypes)) {
			    ?>
							<td colspan="<?php echo count($ftypes); ?>">&nbsp;</td>
					<?php } 
					
					*/
					?>
			
		
		</tr>
		
		
		
		<?php
			}
			?></table></div>
			
			
			
			
			
			
			 <h3><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_HEAD'); ?></h3><p><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_VIEW_DESC'); ?></p>
		  <h3><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER'); ?></h3><p><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_RENDER_DESC'); ?></p>
		  <div>
		  <h3><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PERSONAL'); ?></h3>
		  <p><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PERSONAL_DESC'); ?></p>
		  <select onchange="return alterfield(this)" data-fn="select" data-type="select" name="opc_cr_type" data-name="opc_cr_type" data-value="select">
		  <option value="save_all" <?php if (!empty($opc_cr_type) && ($opc_cr_type=="save_all")) echo ' selected="selected" '; ?>><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PERSONAL_SAVE_ALL'); ?></option>
		  <option value="save_order" <?php if (!empty($opc_cr_type) && ($opc_cr_type=="save_order")) echo ' selected="selected" '; ?>><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PERSONAL_SAVE_ORDER'); ?></option>
		  <option value="save_none" <?php if (!empty($opc_cr_type) && ($opc_cr_type=="save_none")) echo ' selected="selected" '; ?>><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_PERSONAL_SAVE_NONE'); ?></option>
		  </select>
		  </div>
		  <br style="clear: both;"/>
			
			
			<?php		
		   }
		 ?>
		</fieldset>
		
		<?php 
		jimport( 'joomla.plugin.helper' );
		$tg = JPluginHelper::getPlugin('system', 'vmfield_toggler'); 
		if (!empty($tg)) {
		
		?>
			<fieldset class="adminform">
		 <legend><?php echo JText::_('PLG_SYSTEM_VMFIELD_TOGGLER'); ?></legend>
		  <table class="table table-striped admintable table table-striped">
		    
		<tr>
	    <td><label for="clear_data"><?php echo JText::_('PLG_SYSTEM_VMFIELD_TOGGLER_CLEAR_DATA'); ?></label></td>
		
		
		<td>
		 <input type="button" value="<?php echo JText::_('PLG_SYSTEM_VMFIELD_TOGGLER_CLEAR_DATA'); ?>" id="clear_data" name="clear_data" class="btn btn-small btn-success"  onclick="submitbutton('clearfieldpaths');" />
		</td>
		<td><label for="clear_data">
		<?php echo JText::_('PLG_SYSTEM_VMFIELD_TOGGLER_CLEAR_DATA_DESC'); ?>
		</label>
		</td>
		
		</tr>
		</table>
		</fieldset>
		<?php 
		}
		?>
		
		
		<fieldset class="adminform">
		 <legend><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_FIELDS'); ?></legend>
		  <table class="table table-striped admintable table table-striped">
		    
		<tr>
	    <td><label for="do_not_display_business"><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_LOGGED_IN'); ?></label></td>
		
		
		<td>
		<a href="#" onclick="return alterfield(this);"  data-type="checkbox" data-value="1" id="do_not_display_business" data-name="do_not_display_business" <?php if (!empty($do_not_display_business)) echo ' checked="checked" '; ?> >
		<?php if (!empty($do_not_display_business)) echo $checkedfield;
		else echo $uncheckedfield;		
		?>

		</td>
		<td><label for="do_not_display_business">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_BUSINESS_LOGGED_IN_DESC'); ?>
		</label>
		</td>
		
		</tr>
		</table>
		</fieldset>
		<fieldset class="adminform">
		 <legend><?php echo JText::_('COM_ONEPAGE_THIRD_ADDRESS'); ?></legend>
		  <table class="table table-striped admintable table table-striped">
		    
		<tr>
	    <td><label for="opc_switch_rd">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_THIRDADDRESS_SWITCH'); ?>
		</label>
		</td>
		<td>
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-value="1" id="opc_switch_rd" data-name="opc_switch_rd" <?php if (!empty($opc_switch_rd)) echo ' checked="checked" '; ?> >
		<?php if (!empty($opc_switch_rd)) echo $checkedfield;
		else echo $uncheckedfield;		
		?>
		</a>
	    </td>
		<td><label for="opc_switch_rd">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_THIRDADDRESS_SWITCH_DESC'); ?></label>
		</td>
		</tr>
		
		<tr>
	    <td><label for="opc_btrd_def">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_THIRDADDRESS_DEFAULT'); ?>
		</label>
		</td>
		<td>
		<a href="#" onclick="return alterfield(this);" data-type="checkbox" data-value="1" id="opc_btrd_def" data-name="opc_btrd_def" <?php if (!empty($opc_btrd_def)) echo ' checked="checked" '; ?> >
			<?php if (!empty($opc_btrd_def)) echo $checkedfield;
		else echo $uncheckedfield;		
		?>
		</a>
	    </td>
		<td><label for="opc_btrd_def">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_THIRDADDRESS_DEFAULT_BT'); ?></label>
		</td>
		</tr>
		
		
		</table>
		</fieldset>
		
		
			<fieldset class="adminform">
		 <legend><?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ST'); ?></legend>
		  <table class="table table-striped admintable table table-striped">
		    
		<tr>
	    <td><label for="opc_copy_bt_st">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ST'); ?>
		</label>
		</td>
		<td>
		<a href="#" onclick="return alterfield(this);" data-name="opc_copy_bt_st" data-type="checkbox" data-value="1" id="opc_copy_bt_st" name="opc_copy_bt_st"  >
		<?php if (!empty($opc_copy_bt_st)) echo $checkedfield;
		else echo $uncheckedfield;		
		?>
		</a>
		
	    </td>
		<td><label for="opc_copy_bt_st">
		<?php echo JText::_('COM_ONEPAGE_SHOPPERFIELDS_ST_DESC'); ?></label>
		</td>
		</tr>
		</table>
		</fieldset>

		