<?php
/**
 * GDPR privacy checkbox for 3rd party providers 
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 */
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
?><!-- italian privacy checkbox -->
<div style="width: 100%;" class="opc_bottom_checkboxwrap">
<?php
if (empty($gdpr_checkbox_type)) { ?>
<div  class="checkbox_5">


<input value="1" type="checkbox" id="<?php echo $element_name; ?>_field" name="<?php echo $element_name; ?>" <?php if (!empty($agree_checked)) echo ' checked="checked" '; ?> class="terms-of-service "  autocomplete="off" />


</div>
<div class="checkbox_95" >
<?php 
}
else {
	?>
	
<script>
op_userfields.push('<?php echo $element_name; ?>'); 
custom_rendering_fields.push('<?php echo $element_name; ?>'); 
var <?php echo $element_name; ?>_agreeerr = "<?php echo OPCloader::slash(OPCLang::_($COM_ONEPAGE_GDPR_DROPDOWN_ERROR)); ?>";
			  function validate_<?php echo $element_name; ?>() {
				 d = document.getElementById('<?php echo $element_name; ?>_field'); 
				 if (d != null) {
					 var val = d.options[d.selectedIndex].value; 
					 if (val === "") {
						 d.className += ' invalid '; 
						 alert(<?php echo $element_name; ?>_agreeerr); 
						 return false; 
					 }
				 }
				 d.className = d.className.split('invalid').join(''); 
				 return true; 
			   }
addOpcTriggerer('callSubmitFunct', 'validate_<?php echo $element_name; ?>'); 			   
</script>
	
	 <select name="<?php echo $element_name; ?>" class="gdpr_dropdown" id="<?php echo $element_name; ?>_field" required="required" validate="validate" onerrormsg="<?php echo htmlentities($COM_ONEPAGE_GDPR_DROPDOWN_ERROR); ?>">
	<option value=""><?php echo htmlentities(JText::_($COM_ONEPAGE_GDPR_DROPDOWN_CHOOSE)); ?></option>
			 <option value="1" <?php if (!empty($agree_checked)) echo ' selected="selected" '; ?> ><?php echo htmlentities(JText::_($COM_ONEPAGE_GDPR_DROPDOWN_IAGREE)); ?></option>
			 <option value="2"><?php echo htmlentities(JText::_($COM_ONEPAGE_GDPR_DROPDOWN_IDONOTAGREE)); ?></option>
			 </select>
			 <?php
}
?>	
					
					<label class="opc_bottom_labels" for="<?php echo $element_name; ?>_field"  ><span class="checkbox_label_opc"><?php 
					
					if (empty($gdpr_checkbox_type)) {
					 echo OPCLang::_($COM_ONEPAGE_GDPR_DROPDOWN_IAGREE).' '.OPCLang::_($COM_ONEPAGE_GDPR_DROPDOWN_LABEL); 
					}
					else {
					 echo OPCLang::_($COM_ONEPAGE_GDPR_DROPDOWN_LABEL); 
					}
					
					?>
					
					<?php 
					if (!empty($privacy_link)) { 
					JHTMLOPC::_('behavior.modal', 'a.opcmodalprivacy'); 
					?>
					
					<a target="_blank" rel="{handler: 'iframe', size: {x: 500, y: 400}}" class="opcmodalprivacy"  href="<?php echo $privacy_link; ?>" onclick="javascript: return Onepage.op_openlink(this); "><?php echo JText::_($link_title); ?></a>
					<?php
					}
					?>
					
					</span><br />
					<?php
					echo OPCLang::_($COM_ONEPAGE_GDPR_DROPDOWN_DESC); 
					?>
					</label>
					
					<?php if (empty($gdpr_checkbox_type)) { ?>
					</div>
					<?php } ?>
</div>	
<!-- END italian privacy checkbox -->