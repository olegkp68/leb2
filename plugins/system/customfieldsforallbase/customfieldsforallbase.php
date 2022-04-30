<?php
/**
 * @package CustomfieldsforallBasebase
 * @copyright   Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once __DIR__.DIRECTORY_SEPARATOR.'bootstrap.php';
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Language\CustomFieldsForAllLanguageHandlerFactory;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomFieldsForAllLanguageHandler;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * Plugin's main class
 * @since 1.0
 *
 */
class plgVmCustomCustomfieldsforallbase extends vmCustomPlugin
{

    /**
     *
     * @var boolean;
    */
    protected $product_associations_deleted = false;

    /**
     *
     * @var int
     */
    protected $tmp_custom_id = 0;

    /**
     *
     * @var array
     */
    protected $displayed_customfields = [];

    /**
     *
     * @var string
    */
    protected $_product_paramName = '';

    /**
     *
     * @var CustomFieldsForAllLanguageHandler
     */
    protected $languageHandler;

    /**
     *
     * @var UpdaterBase
     */
    protected $updaterHelper;

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

        $languageFactory = new CustomFieldsForAllLanguageHandlerFactory();
        $this->languageHandler = $languageFactory->get();

        $varsToPush = array(
            'display_type' => array('button', 'string'),
            'data_type' => array('', 'string'),
            'is_required' => array('0', 'int'),
            'is_price_variant' => array('0', 'int'),
            'display_price' => array('0', 'string')
        );

        if (!defined('VM_VERSION')) {
            define('VM_VERSION', '3.0');
        }
        $this->setConfigParameterable('customfield_params', $varsToPush);
        $this->_product_paramName = 'customfield_params';
    }

    /**
     * Trigered when a product is cloned
     * This function is inconsistent as it tries to guess the next product_id and virtuemart_customfield_id
     * In case of multi-user environment will may fail due to concurrent insertions of products and custom fields
     *
     * @param $product
     * @return bool
     * @since 1.4.0
     */
    public function plgVmCloneProduct($product)
    {
        $product = (object)$product;
        $product_id = $product->originId;

        if (empty($product->customfields) && ! empty($product_id)) {
            $customfields = $this->getProductCustomFields($product_id);
        } else {
            $customfields = $product->customfields;
        }

        // get the next customfield_id - autoincrement
        $db = Factory::getDbo();
        $query = "SHOW TABLE STATUS LIKE '" . $db->getPrefix() . "virtuemart_product_customfields'";
        $db->setQuery($query);
        try {
            $db->execute();
            $tableStatus = $db->loadObject();
            $last_virtuemart_customfield_id = $tableStatus->Auto_increment;
            if (empty($last_virtuemart_customfield_id)) {
                return false;
            }
        } catch (\RuntimeException $e) {
            \JLog::add(sprintf('CF4All Error cloning Product : ERROR: %s', $e->getMessage()));
            \vmdebug('CF4All Error cloning Product : ERROR: %s', $e->getMessage());
        }

        $new_entry = (int) $last_virtuemart_customfield_id - count($customfields);
        $new_product_id_entry = $product->virtuemart_product_id;

        for ($i = 0; $i < count($customfields); $i ++) {
            $cf = $customfields[$i];
            if ($cf->custom_element == $this->_name) {
                // insert it to the our tables
                $custom_id = $cf->virtuemart_custom_id;
                $customfield = Customfield::getInstance($custom_id, $this->_name);
                // if we could not get the original product id
                if (empty($product_id)) {
                    $product_id = $customfield->getProductFromCustomfield_id($cf->virtuemart_customfield_id);
                }
                $product_assigned_id = $customfield->getProductCustomValues($product_id, 'p_cf.customsforall_value_id', $customfield_id = $cf->virtuemart_customfield_id);
                $customfield->storeProductValues($product_assigned_id, $new_product_id_entry, $new_entry);
            }
            $new_entry ++;
        }
        return true;
    }

    /**
     * Declares the Parameters of a plugin
     *
     * @param $data
     * @return bool
     * @since 1.0
     */
    public function plgVmDeclarePluginParamsCustomVM3(&$data)
    {
        return $this->declarePluginParams('custom', $data);
    }

    /**
     *
     * @param string $psType
     * @param string $name
     * @param int $id
     * @param array $xParams
     * @param array $varsToPush
     * @return boolean
     * @since 1.0
     */
    public function plgVmGetTablePluginParams($psType, $name, $id, &$xParams, &$varsToPush)
    {
        return $this->getTablePluginParams($psType, $name, $id, $xParams, $varsToPush);
    }

    /**
     *
     * @return array:
     */
    public function getVmPluginCreateTableSQL()
    {
        return [];
    }

    /**
     * (non-PHPdoc)
     * @see vmPlugin::getTableSQLFields()
     */
    function getTableSQLFields()
    {
        return [];
    }

    /**
     * Exec when a cf is created/updated (stored) - Customfield view
     *
     * @param $psType
     * @param $data
     * @throws Exception
     * @since 1.0
     */
    public function plgVmOnStoreInstallPluginTable($psType, $data)
    {
        //install additional language tables
        try {
            $this->languageHandler->createLanguageTables();
        }
        catch(Exception $e) {
            throw $e;
        }
        //update extension site with the download id
        $this->updaterHelper->refreshUpdateSite();
    }

    /**
     *
     * @param array $selectList
     * @param unknown $searchCustomValues
     * @param int $virtuemart_custom_id
     * @return boolean
     * @since 1.0
     */
    public function plgVmSelectSearchableCustom(&$selectList, &$searchCustomValues, $virtuemart_custom_id)
    {
        return true;
    }

    /**
     * Displays the custom field in the product view of the backend
     *
     * @param object $field
     *            - The custom field
     * @param int $product_id
     * @param int $row
     *            - The a/a of that field within the product
     * @param string $retValue
     *            - The html that regards the custom fields of that product
     * @return string
     * @since 1.0
     */
    public function plgVmOnProductEdit($field, $product_id, &$row, &$retValue)
    {
        if ($field->custom_element != $this->_name) {
            return '';
        }
        $this->setDisplayOnProductForm($field, $product_id, $row, $retValue, $field_name = '');
    }

    /**
     *
     * Get the custom fields of a product
     *
     * @param 	int $product_id
     * @return	array   the custom fields
     * @since   2.1.2
     */
    public function getProductCustomFields($product_id)
    {
        $db=Factory::getDbo();
        $q=$db->getQuery(true);
        $q->select('*')->from('#__virtuemart_product_customfields')->where('virtuemart_product_id='.(int)$product_id);
        $q->leftJoin('#__virtuemart_customs AS customs ON #__virtuemart_product_customfields.virtuemart_custom_id=customs.virtuemart_custom_id');
        $db->setQuery($q);
        $results=$db->loadObjectList();
        return $results;
    }

    /**
     * Store the custom fields for a specific product
     *
     * @param array $data
     * @param array $plugin_param
     * @return boolean
     */
    public function plgVmOnStoreProduct($data, $plugin_param)
    {
        $plugin_name = key($plugin_param);
        if ($plugin_name != $this->_name) {
            return false;
        }
        if (isset($plugin_param[$plugin_name]['virtuemart_custom_id'])) {
            $custom_id = $plugin_param[$plugin_name]['virtuemart_custom_id'];
        }
        else {
            $custom_id = 0;
        }

        $product_id = (int) $data['virtuemart_product_id'];
        $customfield = Customfield::getInstance($custom_id, $this->_name);
        $isCustomSetInProduct = $customfield->isCustomSetInProduct($product_id);

        // if there are records to be stored
        if ($isCustomSetInProduct) {
            $row = $plugin_param[$this->_name]['row'];

            // check if its the 1st with that id. We run this only to the 1st of its type
            if ($customfield->isFirstCustomOfType($row, $data)) {
                $jinput = Factory::getApplication()->input;
                $custom_plugins = $jinput->get($this->_product_paramName, array(), 'array');
                $this->storeAllCustomsforall($data, $custom_plugins);
            }
        } else
            if ($this->product_associations_deleted == false) {
                if (customfield::deleteAllProductAssociations($product_id)) {
                    vmdebug('Cf4All All product associations deleted', $product_id);
                    $this->product_associations_deleted = true;
                }
            }
        return $this->OnStoreProduct($data, $plugin_param);
    }

    /**
     * Store all the values for all the custom fields
     * This function should run only once for each product
     *
     * @param array $data
     * @param array $custom_plugins
     * @param null $single_assignment
     * @param bool $update_all_of_kind
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    protected function storeAllCustomsforall(
        $data,
        $custom_plugins,
        $single_assignment = null,
        $update_all_of_kind = true
    ) {
        $product_id = $data['virtuemart_product_id'];
        $custom_ids_to_product = [];
        $custom_ids_to_product_tmp = [];
        static $all_values = [];
        $position = [];//indicates the position of that record in the products custom fields

        foreach ($custom_plugins as $key => $plg) {

            //check if its the correct plugin type and the correct custom id
            //maybe the user is using more than 1 customforall plugins for that product
            if (isset($plg[$this->_name])) {
                $custom_id = $data['field'][$key]['virtuemart_custom_id'];
                if (!isset($position[$custom_id])) {
                    $position[$custom_id] = 0;
                }
                $customfield = Customfield::getInstance($custom_id, $this->_name);
                $custom_params = $customfield->getCustomfieldParams($custom_id);
                $custom_ids_to_product[] = $custom_id;
                $customfield_id_parent = $data['field'][$key]['override'];
                $customfield_id = $data['field'][$key]['virtuemart_customfield_id'];

                //new record without customfield id or a child having the record of the parent
                if (empty($customfield_id) || $customfield_id_parent == $customfield_id) {
                    //get it
                    $customfield_id = $customfield->getVmProductCustomfieldId($product_id, $position[$custom_id]);
                }
                $position[$custom_id]++;

                $myplugin = $plg[$this->_name];
                $selected_ids = $myplugin['value'];
                if (empty($selected_ids)) {
                    $selected_ids = array();
                }
                $new_values = $myplugin['newvalues'];
                if (empty($new_values)) {
                    $new_values = array();
                }

                $existing_ids = array();
                $new_stored_ids = array();
                $product_assigned_ids = array();

                $stored_ids = array();
                if (!empty($new_values)) {

                    //the ids of the values which already exist in the db although the user has tried to re-insert them
                    $existing_ids = $customfield->findExistingUnsetDuplicates($new_values);

                    //the ids of the new inserted values
                    if (!empty($new_values)) {
                        $new_stored_ids = $customfield->store($new_values, $set_ordering = false);
                    }
                }

                /*
                 * in case of price variant only 1 assignment should be done.
                 * This will be always the last one. So the last new value if exist
                 */
                if ($custom_params['is_price_variant'] || (isset($single_assignment) && $single_assignment == true)) {
                    if ($existing_ids) {
                        $product_assigned_ids = $existing_ids;
                    } else {
                        if ($new_stored_ids) {
                            $product_assigned_ids = $new_stored_ids;
                        } else {
                            $product_assigned_ids = $selected_ids;
                        }
                    }
                } else {

                    /*
                     * Non price variants
                     * the assignment should contain the existing selected, the values added (accidentaly) which already exist and the new inserted values
                     */
                    $product_assigned_ids = array_merge($selected_ids, $existing_ids, $new_stored_ids);
                    $product_assigned_ids = array_unique($product_assigned_ids);
                }

                vmdebug('CF4All product association ids for product id:' . $product_id . ' and custom_id ' . $custom_id . ': ',
                    implode(',', $product_assigned_ids));
                //vmdebug('CF4All customfield_id for custom_id '.$custom_id.' and position '.$position[$custom_id].': ',$customfield_id);
                $product_assigned_ids = $customfield->storeProductValues($product_assigned_ids, $product_id,
                    $customfield_id);
                $all_values = array_merge($all_values, $product_assigned_ids);
            }
        }

        /**
         * delete/update all or only of this type/custom_id
         * Do note that when the function is called by other plugins (e.g. stockable),
         * we want only the customs of the stockable to be updated and the rest stay as they were
         */
        if ($update_all_of_kind === false) {
            $custom_ids_to_product_tmp = $custom_ids_to_product;
        }
        $result = Customfield::deleteProductValues($product_id, $all_values, $custom_ids_to_product_tmp);
        return $result;
    }

    /**
     * Override this function as we do not want VM to store the plug-in data
     * @see vmCustomPlugin::storePluginInternalDataProduct()
     *
     * @return bool
     * @since 1.0
     */
    protected function storePluginInternalDataProduct (&$values, $primaryKey = 0, $product_id = 0)
    {
        return true;
    }

    /**
     * Display of the Cart Variant Custom fields - VM3
     *
     * @param object $field
     * @param int $idx
     * @param object $group
     * @since 1.0
     */
    public function plgVmOnDisplayProductVariantFEVM3($field,&$idx,&$group)
    {
        vmdebug('Cf4All All display variants',$group);
    }

    /**
     * Display of the Cart Variant/Non cart variants Custom fields - VM3
     *
     * @param object $field
     * @param int $idx
     * @param object $group
     * @return bool
     * @since 1.0
     */
    public function plgVmOnDisplayProductFEVM3(&$product,&$group)
    {
        return true;
    }

    /**
     * Calculates the price of a product applying specific/selected custom field values - VM3
     * Same as plgVmCalculateCustomVariant (VM2)
     *
     * @param object $product The product object
     * @param object $productCustomsPrice the customfield object
     * @param array $selected
     * @param float $modificatorSum The modificator that affects the price
     * @return bool
     * @since 1.0
     */
    public function plgVmPrepareCartProduct(&$product, &$customfield, $selected, &$modificatorSum)
    {
        if ($customfield->custom_element !== $this->_name) {
            return false;
        }

        $total_custom_price = 0;
        foreach ($selected as $key => $value) {
            if (strpos($key, 'customsforall_option') !== false) {
                $selected_option = $value;
                $custom_value = Customfield::getCustomValue(0, 0, $selected_option);
                if (!empty($custom_value->custom_price)) {
                    $total_custom_price += (float)$custom_value->custom_price;
                }
            }
        }
        $modificatorSum += $total_custom_price;

        return true;
    }

    /**
     *
     * Prints the fields in a static way - non selectable
     *
     * @param object $productCustom
     * @param array $values
     * @param string $html
     * @param bool $inline_css
     * @param bool $display_color_label
     * @param bool $pdf
     * @return  bool
     *
     * @since    2.0
     * @author    Sakis Terz
     */
    protected function printStaticFields(
        $productCustom,
        $values,
        &$html,
        $inline_css = false,
        $display_color_label = true,
        $pdf = false
    ) {
        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root(true).'/plugins/system/customfieldsforallbase/view/frontend/css/style.css');
        $separator = '';
        $innerHtml = '';

        $custom_id = $productCustom->virtuemart_custom_id;
        $customfield = Customfield::getInstance($custom_id, $this->_name);
        $custom_params = $customfield->getCustomfieldParams($custom_id);
        $display_type = $custom_params['display_type'];

        if (!empty($values)) {
            foreach ($values as $key => $selval) {
                if (strpos($key, 'customsforall_option') !== false) {
                    $customOption = Customfield::getCustomValue($custom_value_id = 0, $product_id = 0,
                        $value_product_id = $selval);
                    \vmdebug('Cf4All $values', $customOption);
                    $innerHtml .=
                        $separator .
                        Customfield::displayCustomValue(
                            $customOption,
                            $display_type,
                            $class = 'cf4all_color_btn_small',
                            $inline_css,
                            $display_color_label,
                            $pdf);
                    $separator = ',';
                }
            }
        }

        if (!empty($innerHtml)) {
            $html .= '<span class="product-field-wrapper">';
            $html .= '<span class="product-field-label">' . JText::_($productCustom->custom_title) . ': </span>';
            $html .= $innerHtml;
            $html .= '</span>';
        }
        return true;
    }

    /**
     * Triggered on cart display - VM3
     *
     * @param $product
     * @param $productCustom
     * @param $html
     * @param bool $inline_css
     * @param bool $display_color_label
     * @return bool
     * @throws Exception
     * @since 1.0
     */
    public function plgVmOnViewCartVM3(&$product, &$productCustom, &$html, $inline_css = false, $display_color_label = true)
    {
        if (empty($productCustom->custom_element) or $productCustom->custom_element != $this->_name) {
            return false;
        }
        if (!empty($product->customProductData[$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])) {
            $values = $product->customProductData[$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];
        }
        else {
            return false;
        }
        $format = Factory::getApplication()->input->get('format', 'html', 'cmd');
        $isPdf = $format == 'pdf' ? true : false;
        $this->printStaticFields($productCustom, $values, $html, $inline_css, $display_color_label, $isPdf);

        return true;
    }

    /**
     * function triggered by the stockable custom fields plug-in on display cart - VM3
     *
     * @param 	$product
     * @param 	$productCustom
     * @param 	$html
     *
     * @author	Sakis Terz
     * @return 	bool|string
     */
    public function plgVmOnStockableDisplayCart(&$product, &$productCustom, &$html)
    {
        if (empty($productCustom->custom_element) or $productCustom->custom_element != $this->_name) {
            return false;
        }
        $customfield=Customfield::getInstance($productCustom->virtuemart_custom_id, $this->_name);
        $values=$customfield->getProductCustomValues($product->virtuemart_product_id,'p_cf.id');

        //the printStaticFields requires that the values use that key (customsforall_option)
        $new_values=array('customsforall_option'=>reset($values));
        $return=$this->printStaticFields($productCustom, $new_values, $html);
        return $return;
    }

    /**
     * Returns the proper layout path
     *
     * @param $layout
     * @param bool $is_site
     * @return string
     * @since 4.1.0
     */
    public function getPluginLayout($layout, $is_site = true)
    {
        // the default layouts folder
        $folder = $is_site ? 'frontend' : 'admin';
        $layoutFolder = realpath(CF4ALLBASE_PLUGIN_PATH) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'layout';

        // first check for template overrides
        if($is_site) {
            $pluginCurrentTmplLayout = JPluginHelper::getLayoutPath($this->_type, $this->_name, $layout);
            $pluginBaseTmplLayout = JPluginHelper::getLayoutPath('system', 'customfieldsforallbase', $layout);

            if (file_exists($pluginCurrentTmplLayout)) {
                return $pluginCurrentTmplLayout;
            }
            elseif (file_exists($pluginBaseTmplLayout)) {
                return $pluginBaseTmplLayout;
            }
        }
        return $layoutFolder . DIRECTORY_SEPARATOR . $layout. '.php';
    }


    public function plgVmSetOnTablePluginParamsCustom($name, $id, &$table)
    {
        return $this->setOnTablePluginParams($name, $id, $table);
    }

    public function plgVmOnDeleteProduct($virtuemart_product_id, $ok)
    {
        $return=Customfield::deleteProductValues($virtuemart_product_id);
        return $return;
    }

    public function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data)
    {
        return $this->declarePluginParams($psType, $name, $id, $data);
    }

    public function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin)
    {
        return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
    }

    public function plgVmOnCloneProduct($data,$plugin_param)
    { // not work! need to edit VM2 core
        return $this->OnStoreProduct($data,$plugin_param);
    }

    public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE')
    {
        $this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
    }

    /**
     *
     * Order display BE- VM3
     */
    public function plgVmDisplayInOrderBEVM3( &$product, &$productCustom, &$html)
    {
        $this->plgVmOnViewCartVM3($product,$productCustom,$html, $inline_css=true);
    }

    /**
     * Order display FE - VM3
     * Also used for the invoice creation
     *
     * @since 1.0
     */
    public function plgVmDisplayInOrderFEVM3( &$product, &$productCustom, &$html)
    {
        $this->plgVmOnViewCartVM3($product,$productCustom,$html, $inline_css=true, $display_color_label=true);
    }

}
