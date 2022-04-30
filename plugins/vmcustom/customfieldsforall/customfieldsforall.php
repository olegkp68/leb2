<?php
/**
 * @package customfieldsforall
 * @copyright Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomfieldsForAllFilter;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

require_once __DIR__.DIRECTORY_SEPARATOR.'bootstrap.php';

/**
 * Plugin's main class
 * @author sakis
 * @since 1.0
 *
 */
class plgVmCustomCustomfieldsforall extends plgVmCustomCustomfieldsforallbase
{
    /**
     * The path to the admin layouts
     *
     * @var string
     * @since 4.0.0
     */
    const ADMIN_LAYOUT_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'tmpl';

    /**
     * Constructor class of the custom field
     *
     * @param string $subject
     * @param array $config
     * @since 1.0
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);

        $this->_tablepkey = 'id';
        $this->tableFields = array();
        $this->_tablename = '#__virtuemart_product_custom_plg_customsforall';
        $this->updaterHelper = new CustomFieldsForAllUpdate();
    }

    /**
     * Exec when a cf is created/updated (stored) - Customfield view
     *
     * @param $psType
     * @param $data
     * @return bool|void
     * @throws Exception
     * @since 1.0
     */
    public function plgVmOnStoreInstallPluginTable($psType, $data)
    {
        if ($data['custom_element'] !== $this->_name) {
            return false;
        }
        parent::plgVmOnStoreInstallPluginTable($psType, $data);
        $virtuemart_custom_id = $data['virtuemart_custom_id'];
        $customfield = Customfield::getInstance($virtuemart_custom_id, $this->_name);
        $db_value_ids = $customfield->getCustomValues($field = 'customsforall_value_id');
        $used_ids = Customfield::getField($data['cf_val'], $field = 'customsforall_value_id');

        // update the values
        $toBeDeleted = array_diff($db_value_ids, $used_ids);

        // check for duplicates
        $customfield->store($data['cf_val']);
        $customfield->delete($toBeDeleted);
    }

    /**
     * Displays the custom field in the product view of the backend
     *
     * @param \TableProducts $field - The custom field
     * @param int $product_id
     * @param int $row - The a/a of that field within the product
     * @param string $retValue - The html that regards the custom fields of that product
     * @param string $field_prefix The prefix that will be used in the input fields. All the variables with that prefix will be then returned to the save function
     * @return bool
     * @since 1.0
     */
    public function plgVmOnStockableDisplayBE($field, $product_id, &$row, &$retValue, $field_prefix)
    {
        if (empty($field) || $field->custom_element != $this->_name)
            return '';
        $this->setDisplayOnProductForm($field, $product_id, $row, $retValue, $field_prefix, $force_multiple = false);
    }

    /**
     * Creates the backend output of the plugin inside the product
     *
     * @param object $field - The custom field
     * @param int $product_id
     * @param int $row - The a/a of that field within the product
     * @param string $retValue - The html that regards the custom fields of that product
     * @param string $field_prefix -The prefix that will be used in the input fields
     * @return  bool
     * @since    3.0
     */
    protected function setDisplayOnProductForm($field, $product_id, &$row, &$retValue, $field_prefix, $force_multiple = null)
    {
        $block = new ProductRow($this->_name, $product_id, $field->virtuemart_custom_id);
        $layout = self::ADMIN_LAYOUT_DIR . DIRECTORY_SEPARATOR . 'productRow.php';
        $filterInput = CustomfieldsForAllFilter::getInstance(); //filter
        ob_start();
        include $layout;
        $html = ob_get_clean();
        $retValue .= $html;
        return true;
    }

    /**
     * Store the custom fields for a specific product
     *
     * @param array $data
     * @param array $plugin_param
     * @since 1.0
     */
    public function plgVmOnStoreProduct($data, $plugin_param)
    {
        parent::plgVmOnStoreProduct($data, $plugin_param);
    }

    /**
     * Triggered by the stockableCustomfields plugin on saving a product
     *
     * @param object $data
     * @param object $custom_plugin
     * @return bool
     * @since    3.0
     */
    public function plgVmOnStockableSave($data, $custom_plugin)
    {
        vmdebug('DATA', $custom_plugin);
        $custom_plugins = array($custom_plugin);
        $new_data = array('field' => array($data), 'virtuemart_product_id' => $data['virtuemart_product_id']);

        $result = $this->storeAllCustomsforall($new_data, $custom_plugins, $single_assignment = true, $delete_previous = false);
        return $result;
    }

    /**
     * Sets the output/html
     * Also sets an array of objects containing the customfields objects
     * Each object should have these fields: id, value, virtuemart_custom_id,virtuemart_product_id,
     * id: The unique id of the value (virtuemart_customfield_id if no 3rd party tables used)
     * value: The value (e.g. Red)
     * virtuemart_custom_id: The virtuemart_custom_id of the custom
     * virtuemart_product_id: The virtuemart_product_id of the product where the custom field is assigned
     *
     * @param VirtueMartModelProduct $current_product The current product
     * @param \TableProducts $custom_obj The custom field. An object containing the data of the custom
     * @param array $product_ids All the product ids which are derived by this custom for that parent product
     * @param string $output The html output returned from the plugin
     *
     * @return    boolean
     * @since    3.0
     */
    public function plgVmOnStockableDisplayFE($current_product, $custom_obj, $product_ids, &$customfields, &$output)
    {
        if ($custom_obj->custom_element != $this->_name) return '';
        $customfields = array();
        $custom_id = $custom_obj->virtuemart_custom_id;
        $customfield = Customfield::getInstance($custom_id, $this->_name);
        $product_ids = ArrayHelper::toInteger($product_ids);

        //get the custom values related with a set of products (derived products)
        $customfields = $customfield->getProductCustomValues(
            $product_ids, '
			cf.customsforall_value_id,
			cf.customsforall_value_id AS id,
			cf.customsforall_value_name,
			cf.customsforall_value_name AS value,
			p_cf.virtuemart_product_id AS virtuemart_product_id,
			"' . $custom_id . '" AS virtuemart_custom_id,
			cf.customsforall_value_label,
			cf_p.virtuemart_customfield_id,
			cf_p.customfield_price AS custom_price',
            $customfield_id = 0,
            $order = 'FIELD(cf_p.virtuemart_product_id,' . implode(',', $product_ids) . ') ASC'
        );

        if (empty($customfields)) return false;
        $customfields_for_display = $customfield->array_unique($customfields, 'id');
        //display generation
        $custom_params = $customfield->getCustomfieldParams($custom_id);
        $custom_obj->virtuemart_customfield_id = end($customfields)->virtuemart_customfield_id;
        $custom_obj->calculate_price = true;
        $custom_obj->custom_params = $custom_params;
        $custom_obj->values = $customfields_for_display;
        if (empty($custom_obj->pb_group_id)) $custom_obj->pb_group_id = '';
        $layout = $custom_params['display_type'];
        if (empty($layout)) $layout = 'select';
        //cart input
        $viewdata = $custom_obj;
        $viewdata->virtuemart_product_id = $current_product->virtuemart_product_id;
        $viewdata->level = $customfield->getLevel();

        //cannot use multi-select layout
        switch ($layout) {
            case 'checkbox':
                $layout = 'radio';
                break;
            case 'button_multi':
                $layout = 'button';
                break;
            case 'color_multi':
                $layout = 'color';
                break;
        }

        Factory::getDocument()->addStyleSheet(Uri::root(true) . '/plugins/system/customfieldsforallbase/view/frontend/css/style.css');
        Factory::getDocument()->addScript(Uri::root(true) . '/plugins/vmcustom/customfieldsforall/assets/js/customfields_fe.js');

        //Some vm 3 -templates alter the default order of the scripts loading, hence we have to reload the scripts in the correct order, to prevent js errors
        \vmJsApi::jPrice();
        echo \vmJsApi::writeJS();
        $output = $this->renderByLayout($layout, $viewdata);
        return true;
    }

    /**
     * Display of the Cart Variant/Non cart variants Custom fields - VM3
     *
     * @param \stdClass $product
     * @param \stdClass $group
     * @return bool|string
     * @since 1.0
     */
    public function plgVmOnDisplayProductFEVM3(&$product, &$group)
    {
        if ($group->custom_element != $this->_name) return '';
        $html = '';
        $custom_id = $group->virtuemart_custom_id;
        $this->displayed_customfields[] = $custom_id;
        $calculate_price = false;
        $customfield = Customfield::getInstance($custom_id, $this->_name);
        $custom_params = $customfield->getCustomfieldParams($custom_id);
        if ($custom_params['is_price_variant'] && !empty($custom_params['display_price'])) {
            $calculate_price = true;
        }

        $group->calculate_price = $calculate_price;
        $group->custom_params = $custom_params;
        $group->values = $customfield->getProductCustomValues($group->virtuemart_product_id);

        /*
         * when the same custom exists multiple times in a product, then it is probably a price variant and should be loaded only once.
         * Exception are the price variants coming from PB. Those custom fields are unique records.
         */
        if (!empty($group->values) && end($group->values)->customfield_id != $group->virtuemart_customfield_id && !isset($group->pb_group_id)) {
            return false;
        }
        if (!isset($group->pb_group_id)) $group->pb_group_id = '';
        $layout = $custom_params['display_type'];
        if (empty($layout)) $layout = 'select';

        //cart input
        $viewdata = $group;
        $viewdata->virtuemart_product_id = $product->virtuemart_product_id;

        if ($group->is_input) {
            $viewdata->level = $customfield->getLevel();
            /*
             * load the styles and scripts here for all the layouts
             * This lets us do massive updates for all the layouts.
             * Also these files should not be removed in case of layout overrides
             */
            Factory::getDocument()->addStyleSheet(Uri::root(true) . '/plugins/system/customfieldsforallbase/view/frontend/css/style.css');
            Factory::getDocument()->addScript(Uri::root(true) . '/plugins/vmcustom/customfieldsforall/assets/js/customfields_fe.js');

            $html = $this->renderByLayout($layout, $viewdata);
        } //non cart input
        else {
            if (!empty($group->values)) {
                Factory::getDocument()->addStyleSheet(Uri::root(true) . '/plugins/system/customfieldsforallbase/view/frontend/css/style.css');
                $html = '<div class="cf4all_customvalues_wrapper">';
                $counter = count($group->values);
                $i = 0;

                foreach ($group->values as $v) {
                    $html .= Customfield::displayCustomValue($v, $layout); //generate the html of that custom field
                    if ($i < $counter - 1 && $layout != 'color' && $layout != 'color_multi') $html .= '<span class="cf4all_comma">, </span>'; //add a comma
                    $i++;
                }
                $html .= '</div>';
            }
        }
        $group->display = $html;
        return true;
    }

    /**
     * function triggered by the stockable custom fields plug-in on display cart - VM3
     *
     * @param  \stdClass $product
     * @param  \stdClass $productCustom
     * @param  string $html
     * @return  bool|string
     * @author  Sakis Terz
     * @since   3.0
     */
    public function plgVmOnStockableDisplayCart(&$product, &$productCustom, &$html)
    {
        if (empty($productCustom->custom_element) or $productCustom->custom_element != $this->_name) return false;
        $customfield = Customfield::getInstance($productCustom->virtuemart_custom_id, $this->_name);
        $values = $customfield->getProductCustomValues($product->virtuemart_product_id, 'p_cf.id');
        //the printStaticFields requires that the values use that key (customsforall_option)
        $new_values = array('customsforall_option' => reset($values));
        $return = $this->printStaticFields($productCustom, $new_values, $html);
        return $return;
    }

    /**
     * Hook for generating custom filters from the plugin
     *
     * @param $name
     * @param $virtuemart_custom_id
     * @param $data_type
     * @return bool
     * @since 1.0
     */
    public function onGenerateCustomfilters($name, $virtuemart_custom_id, &$data_type)
    {
        if (empty($name) || empty($virtuemart_custom_id) || $name != $this->_name) return false; //exec only for this plugin
        $customfield = Customfield::getInstance($virtuemart_custom_id, $this->_name);
        $custom_params = $customfield->getCustomfieldParams($virtuemart_custom_id);

        if ($custom_params['display_type'] == 'color' && $custom_params['data_type'] != 'color_hex') {
            $data_type = 'color_name'; //use of color names
        } else $data_type = $custom_params['data_type'];
        if (empty($data_type)) $data_type = 'string';
        return true;
    }

    /**
     * Hook for filtering from plugins
     *
     * The filtering can work either if the custom_values and the product_ids are in the same table
     * Or if the custom_values use their own table and the custom_values->product_ids connection is happening in another table using the custom_value_ids
     * In both cases there should be a field named virtuemart_custom_id in the custom_values table, that indicates (is key) the VM custom_id for these records
     *
     * @param string $name the plugin name as stored in the custom_element field of the virtuemart_customs table
     * @param int	 $virtuemart_custom_id as stored in the virtuemart_customs table
     * @param string $product_customvalues_table the name of the table where the custom_value->product relationship is saved-- Table alias: cfp
     * @param string $customvalues_table the name of the table where the custom_values are saved 							-- Table alias: cf
     * @param string $filter_by_field the column by which the filtering will be done. If 2 tables indicates the custom_value id in both of the above tables
     * @param string $customvalue_value_field the field name where the custom value is stored in the table $customvalues_table
     * @param string $filter_data_type the datatype of the field which will be used for filtering (string|int|float|boolean)
     * @param string $sort_by	The field by which the values/options will be sorted. name and id cane be applied to all
     * @param int    $custom_parent_id	The field inidicating the parent id field of the current custom field
     * @param string $value_parent_id_field	The field inidicating the parent id field of a value
     * @param string $customvalue_value_description_field	The field that adds description to the value (e.g. color name to a color code)
     *
     * @return boolean
     * @since 1.0
     */
    public function onFilteringCustomfilters(
        $name,
        $virtuemart_custom_id,
        &$product_customvalues_table,
        &$customvalues_table,
        &$filter_by_field,
        &$customvalue_value_field,
        &$filter_data_type,
        &$sort_by = 'name',
        &$custom_parent_id = 0,
        &$value_parent_id_field = '',
        &$customvalue_value_description_field = '')
    {
        //exec only for this plugin
        if (empty($name) || empty($virtuemart_custom_id) || $name != $this->_name) {
            return false;
        }

        $product_customvalues_table = '#__virtuemart_product_custom_plg_customsforall';
        $filter_by_field = 'customsforall_value_id';
        $customvalue_value_field = 'customsforall_value_name';
        $customvalue_value_description_field = 'customsforall_value_label';
        $filter_data_type = 'int';
        $sort_by = 'cf.ordering';

        //can be the same as above if the custom_values and the product ids are in the same table (as happens in built in custom fields)
        $customvalues_table = '#__virtuemart_custom_plg_customsforall_values';
        $languages = $this->languageHandler->getLanguages();
        $customfield = Customfield::getInstance($virtuemart_custom_id, $this->_name);
        $custom_params = $customfield->getCustomfieldParams($virtuemart_custom_id);

        //enable multi-lingual
        if (
            $this->languageHandler->getDefaultLangTag() != $this->languageHandler->getAppLanguageCode() &&
            count($languages) > 1
            && in_array($custom_params['data_type'], ['string', 'color_hex'])) {
            $langTable = $customvalues_table . '_' . $languages[$this->languageHandler->getAppLanguageCode()]->db_code;
            $customvalues_table = '
		        (SELECT tbl1.customsforall_value_name, tbl1.customsforall_value_label, tbl1.customsforall_value_id, tbl2.virtuemart_custom_id, tbl2.ordering
		        FROM `' . $langTable . '` AS tbl1
		        LEFT JOIN ' . $customvalues_table . ' AS tbl2 ON tbl1.' . $filter_by_field . '= tbl2.' . $filter_by_field . ')';
        }

        return true;
    }

    /**
     * Hook for the stockableCustomfields plugin
     * Returns the name of that plugin, so that custom fields from that can be used as stockables
     *
     * @return	string
     * @since	3.0
     */
    public function onDetectStockables()
    {
        return $this->_name;
    }
}
