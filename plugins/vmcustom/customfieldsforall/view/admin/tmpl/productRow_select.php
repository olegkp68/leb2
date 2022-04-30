<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

$wrapper_id='customsforall_'.$row.'_'.$block->getVirtuemartCustom_id();

//add buttons toolbar
if(count($values_obj_list)>1 && !empty($multiple)) {?>
	<div class="cf4all_values_toolbar" style="min-height:3em;">
		<button class="btn" type="button" 
		onclick="jQuery('#<?php echo $wrapper_id?> option').attr('selected','selected'); jQuery('#<?php echo $wrapper_id?>').trigger('liszt:updated');"><?php echo JText::_('JGLOBAL_SELECTION_ALL')?>
		</button>
		<button class="btn" type="button" 
		onclick="jQuery('#<?php echo $wrapper_id?> option').removeAttr('selected'); jQuery('#<?php echo $wrapper_id?>').trigger('liszt:updated');"><?php echo JText::_('JGLOBAL_SELECTION_NONE')?>
		</button>
	<div class="clr"></div>
	</div>
	<div class="clr"></div>
<?php 
    }?>

<select class="cfield-chosen-select" id="<?php echo $wrapper_id?>" name="<?php echo $this->_product_paramName?>[<?php echo $row?>]<?php echo $field_prefix?>[customfieldsforall][value][]" <?php echo $multiple ?>>
<?php 
    if(empty($multiple)){?>
        <option value=""><?php echo JText::_('PLG_CUSTOMSFORALL_SELECT_AN_OPTION')?></option>
<?php 
    }        
foreach($values_obj_list as $v){
    if(in_array($v->customsforall_value_id, $product_value_ids))$selected='selected="selected"'; ?>

    <option value="<?php echo $v->customsforall_value_id?>" <?php echo $selected?> style="<?php echo $option_style?>">
        <?php echo $this->languageHandler->__($v);?>
    </option>
    <?php 
    $selected='';
}?>        
</select>