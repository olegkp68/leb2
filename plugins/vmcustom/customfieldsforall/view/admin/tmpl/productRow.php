<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

$values_obj_list = $block->getCustomValues();
$virtuemart_customfield_id=isset($field->virtuemart_customfield_id)?$field->virtuemart_customfield_id:0;
$product_value_ids = $block->getProductValueIds($virtuemart_customfield_id);
$custom_params = $block->getCustomParams();
$datatype=$custom_params['data_type'];
//load scripts and styles
$block->getCustom()->loadStylesScripts();

$option_style='';
$selected='';
$multiple='';
if(!$custom_params['is_price_variant'] && (!isset($force_multiple) || $force_multiple==true))$multiple='multiple';

//Non color types    
if($datatype!='color_hex'){
    include __DIR__.DIRECTORY_SEPARATOR.'productRow_select.php';
}

//color buttons
else{
    include __DIR__.DIRECTORY_SEPARATOR.'productRow_colors.php';
}

//create also new values using the existing JElement
if(!class_exists('RenderFields')) {
    require(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'vmcustom'.DIRECTORY_SEPARATOR.'customfieldsforall'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'renderFields.php');
}
$renderFields=new RenderFields;
$custom_params['single_entry']=empty($multiple)?true:false;
$addValues_el=$renderFields->fetchCustomvalues($name=$this->_product_paramName.'['.$row.']'.$field_prefix.'[customfieldsforall][newvalues]',$block->getVirtuemartCustom_id(),$value='',$row,$custom_params);
echo $addValues_el;
?>
<input type="hidden" name="<?php echo $this->_product_paramName?>[<?php echo $row?>]<?php echo $field_prefix?>[customfieldsforall][virtuemart_custom_id]" value="<?php echo $block->getVirtuemartCustom_id()?>"/>
<input type="hidden" name="<?php echo $this->_product_paramName?>[<?php echo $row?>]<?php echo $field_prefix?>[customfieldsforall][row]" value="<?php echo $row?>"/>