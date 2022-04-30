<?php
/**
 * @package		CustomfieldsforallBasebase
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;
$custom_params = $viewData->custom_params;
$required = !empty($custom_params['is_required']) ? true : false;
$options = $viewData->values;
$virtuemart_customfield_id = $viewData->virtuemart_customfield_id;
$field_name = 'customProductData[' . $viewData->virtuemart_product_id . '][' . $viewData->virtuemart_custom_id . '][' . $virtuemart_customfield_id . ']';
$compatibilityClass = "";
if (!empty($custom_params['parent_id'])) {
    $compatibilityClass = "cf4all_incompatible";
}

if(empty($options)) {
    return false;
}

if($viewData->calculate_price){
    if(!class_exists('\CurrencyDisplay')) {
        require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'currencydisplay.php');
    }
    $currency = \CurrencyDisplay::getInstance();
    if(!class_exists('\calculationHelper')) {
        require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'calculationh.php');
    }
    $calculator = \calculationHelper::getInstance();
}

$wrapper_class = $this->_name == 'dependentcustomfieldsforall' ? 'dep_cf4all_wrapper' : 'cf4all_wrapper';

$wrapper_id='cf4all_wrapper_'.$viewData->virtuemart_customfield_id.'_'.$viewData->pb_group_id;
if($required) {
    $wrapper_class .=' cf4all_required';
}?>

<div id="<?php echo $wrapper_id?>" class="<?php echo $wrapper_class?> cf4all_buttons">
	<?php
	if($required):?>
	    <span class="cf4all_error_msg" style="display: none"><?php echo JText::_('PLG_CUSTOMSFORALL_REQUIRED_FIELD')?></span>
	<?php endif;?>

	<?php
	$checked='';
	if(!$required) {
	    $checked='checked';
    }
	foreach ($options as $v) {
		$label= $this->languageHandler->__($v);

		//generate the price
		$price='';
        $custom_price = (float)$v->custom_price;
        $display_key_element = $virtuemart_customfield_id.'_'.$v->customsforall_value_id.'_'.$viewData->virtuemart_product_id.'_'.$viewData->pb_group_id.$input->get('bundled_products','');
        $showTooltip = false;
        if (!empty($viewData->calculate_price) && !empty($custom_price)) {
            if ($custom_price >= 0) {
                $op = '+';
            }
            else {
                $op = '';
            }
            $price = $op . $currency->priceDisplay($calculator->calculateCustomPriceWithTax($custom_price));

            if ($custom_params['display_price'] == 'tooltip' && !empty($price)) {
                $showTooltip = true;
            }
            elseif ($custom_params['display_price'] == 'label' && !empty($price)) {
                $label .= '&nbsp;' . $price;
            }
        }
	$input_id='cf4all_input_'.$display_key_element;
	?>

	<div class="inline-control-group cf4all-relative <?php echo $compatibilityClass?>">
		<input type="radio" value="<?php echo $v->id ?>" id="<?php echo $input_id?>" class="cf4all_radio" name="<?php echo $field_name?>[customsforall_option]" data-custom-id="<?php echo $viewData->virtuemart_custom_id?>" data-level="<?php echo $viewData->level?>" <?php echo $checked?>/>
		<label class="cf4all_button cf4all_no_color_btn" for="<?php echo $input_id?>">
            <?php echo $label?>
	    </label>

        <?php
        // load the tooltip
        if ($showTooltip) {
            $tooltipContent = $price;
            $layoutPath = $this->getPluginLayout('_tooltip');
            if (file_exists($layoutPath)) {
                require $layoutPath;
            }
        }?>
	</div>
	<?php
	$checked='';
}?>
</div>
