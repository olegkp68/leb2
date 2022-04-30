<?php
/**
 * @package      CustomfieldsforallBasebase
 * @copyright    Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$custom_params = $viewData->custom_params;
$required = !empty($custom_params['is_required']) ? true : false;
$selects = array();
$options = $viewData->values;

$virtuemart_customfield_id = $viewData->virtuemart_customfield_id;
$field_name = 'customProductData[' . $viewData->virtuemart_product_id . '][' . $viewData->virtuemart_custom_id . '][' . $virtuemart_customfield_id . ']';
$compatibilityClass = "";
if(!empty($custom_params['parent_id'])) {
    $compatibilityClass = "cf4all_incompatible";
}

if(empty($options)) {
    return false;
}

if ($viewData->calculate_price) {
    if (!class_exists('\CurrencyDisplay')) {
        require(JPATH_VM_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'currencydisplay.php');
    }
    $currency = \CurrencyDisplay::getInstance();
    if (!class_exists('\calculationHelper')) {
        require(JPATH_VM_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'calculationh.php');
    }
    $calculator = \calculationHelper::getInstance();
}

$wrapper_class = $this->_name == 'dependentcustomfieldsforall' ? 'dep_cf4all_wrapper' : 'cf4all_wrapper';
$wrapper_id = 'cf4all_wrapper_' . $viewData->virtuemart_customfield_id . '_' . $viewData->pb_group_id;
if ($required) {
    $wrapper_class .= ' cf4all_required';
} ?>

<div class="<?php echo $wrapper_class ?>" id="<?php echo $wrapper_id ?>">
    <?php
    if ($required):?>
        <span class="cf4all_error_msg" style="display:none"><?php echo JText::_('PLG_CUSTOMSFORALL_REQUIRED_FIELD') ?></span>
        <?php
        $fist_option = array('value' => 0, 'text' => JText::_('PLG_CUSTOMSFORALL_SELECT_AN_OPTION_FE'));
    endif;
    foreach ($options as $v) {
        $label = $this->languageHandler->__($v);
        $price = '';
        $custom_price = (float)$v->custom_price;
        if (!empty($viewData->calculate_price) && !empty($custom_price)) {
            $op = '';
            if ($custom_price >= 0) {
                $op = '+';
            }
            $price = $op . $currency->priceDisplay($calculator->calculateCustomPriceWithTax($custom_price));
            if ($custom_params['display_price'] == 'label') {
                $label .= '&nbsp;(' . $price . ')';
            }
        }
        $selects[] = array('value' => $v->id, 'text' => $label);
    }

    // Add the 1st option to the list
    if (!empty($fist_option)) {
        array_unshift($selects, $fist_option);
    }
    if (!empty($selects)) {
        $html = HTMLHelper::_('select.genericlist', $selects, $field_name . '[customsforall_option]', "data-custom-id=\"$viewData->virtuemart_custom_id\" data-level=\"$viewData->level\" class=\"$compatibilityClass\"", 'value', 'text', $selects[0], false, false);
        echo $html;
    } ?>
</div>
