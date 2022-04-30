<?php
/**
 * @package		CustomfieldsforallBasebase
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomfieldsForAllFilter;

$input = Factory::getApplication()->input;
$custom_params = $viewData->custom_params;
$required = !empty($custom_params['is_required']) ? true : false;

//filter
$filterInput = CustomfieldsForAllFilter::getInstance();
$wrapper_class = '';
$class = '';
$selects = array();
$options = $viewData->values;

$virtuemart_customfield_id=$viewData->virtuemart_customfield_id;
$field_name='customProductData['.$viewData->virtuemart_product_id.']['.$viewData->virtuemart_custom_id.']['.$virtuemart_customfield_id.']';
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
$wrapper_id='cf4all_wrapper_'.$virtuemart_customfield_id.'_'.$viewData->pb_group_id;
if($required) {
    $wrapper_class .= ' cf4all_required';
}?>

<div class="<?php echo $wrapper_class?> cf4all_color_buttons" id="<?php echo $wrapper_id?>">
	<?php
	if($required):?>
	    <span class="cf4all_error_msg" style="display: none"><?php echo Text::_('PLG_CUSTOMSFORALL_REQUIRED_FIELD')?></span>
	<?php
	endif;?>

	<?php
    $checked = '';
    if (!$required) {
        $checked = 'checked';
    }
    foreach ($options as $v) {
        $display_key_element = $virtuemart_customfield_id.'_'.$v->customsforall_value_id.'_'.$viewData->virtuemart_product_id.'_'.$viewData->pb_group_id.$input->get('bundled_products','');
        $custom_value_name_multi = explode('|', $v->customsforall_value_name);
        $label_html = '';
        $count_multi_values = count($custom_value_name_multi);
        $width = 100 / $count_multi_values;
        if ($count_multi_values == 1) {
            $customsforall_value_label = $custom_value_name_multi[0];
        }

        //multi-colors
        foreach ($custom_value_name_multi as $custom_value_name) {
            //validate that is a color either hex or a standard color name
            $color = $filterInput->checkNFormatColor($custom_value_name);
            if ($color === false) {
                continue;
            }
            $label_style = 'background-color:' . $color . '; width:' . $width . '%;';
            $label_html .= '<div class="cf4all_inner_value" style="' . $label_style . '" aria-hidden="true"></div>';
        }

        $ishex = false;
        if (strpos($color, '#') !== false) {
            $ishex = true;
        }
        $class = 'cf4all_color_btn_medium';
        $input_id = 'cf4all_input_' . $virtuemart_customfield_id . '_' . $v->customsforall_value_id . '_' . $viewData->virtuemart_product_id . '_' . $viewData->pb_group_id . $input->get('bundled_products', '');
        $price = '';
        $tooltip = '';

        //use the color name/label as tooltip
        if (!empty($v->customsforall_value_label)) {
            $tooltip .= $this->languageHandler->__($v, $lang = '', $group = 'label') . ' ';
        } else if ($ishex === false) {
            $tooltip .= Text::_($color) . ' ';
        }

        $custom_price = (float)$v->custom_price;
        if (!empty($viewData->calculate_price) && !empty($custom_price)) {
            $op = '';
            if ($custom_price >= 0) {
                $op = '+';
            }
            $price = $op . $currency->priceDisplay($calculator->calculateCustomPriceWithTax($custom_price));

            if ($custom_params['display_price'] == 'tooltip') {
                $tooltip .= '&nbsp;' . $price;
            }
        }?>

	<div class="inline-control-group cf4all-relative <?php echo $compatibilityClass?>">
	    <input type="radio" value="<?php echo $v->id ?>" id="<?php echo $input_id?>" class="cf4all_radio" name="<?php echo $field_name?>[customsforall_option]" data-custom-id="<?php echo $viewData->virtuemart_custom_id?>" data-level="<?php echo $viewData->level?>" <?php echo $checked?> />
	    <label class="cf4all_button cf4all_color_btn <?php echo $class?>" for="<?php echo $input_id?>">
            <span class="cf4all_hidden_text"><?php echo $tooltip?></span>
            <?php echo $label_html?>
        </label>

        <?php
        // load the tooltip
        if ($tooltip) {
            $tooltipContent = $tooltip;
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

