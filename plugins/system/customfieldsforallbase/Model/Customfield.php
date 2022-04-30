<?php
/**
 * @package CustomfieldsforallBase
 * @copyright   Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Breakdesigns\Plugin\System\Customfieldsforallbase\Model;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Language\CustomFieldsForAllLanguageHandlerFactory;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Language\Table as TableCustomvalueLanguage;

/**
 *
 * Class that contains the necessary functions used by the customfield
 *
 * @package        CustomfieldsforallBase
 * @author        Sakis Terz
 * @since 1.0
 */
Class Customfield
{
    /**
     *
     * @var string
     */
    protected $pluginName;

    /**
     * the name of the fields in the product, different name in VM2 and VM3
     *
     * @var string
     */
    protected $_paramName = 'customfield_params';

    /**
     *
     * @var array
     */
    protected static $assigned_customvalue_ids = [];

    /**
     *
     * @var array
     * @since 1.1.0
     */
    protected $_customparams;

    /**
     *
     * @var array
     * @since 1.1.0
     */
    protected $_customvalues;

    /**
     *
     * @var array
     */
    protected static $_product_customvalue_ids = [];

    /**
     *
     * @var array
     */
    protected static $_customStored = [];

    /**
     * The order of the current custom
     *
     * @var int
     */
    protected static $custom_order = 0;

    /**
     *
     * @var boolean
     */
    protected static $_isCustomSetInProduct;

    /**
     *
     * @var int
     */
    protected $_custom_id;

    /**
     *
     * @var array
     */
    protected static $instances = [];

    /**
     *
     * @var CustomFieldsForAllLanguageHandler
     */
    protected $languageHandler;

    /**
     * @var int
     * @since 1.1.0
     */
    protected $parent_id;

    /**
     * The level of the custom field in the dependency stack
     *
     * @var int
     * @since 1.1.0
     */
    protected $level;

    /**
     * Constructor
     *
     * @param int $_custom_id
     * @param string $pluginName
     * @since 1.0
     */
    public function __construct($_custom_id, $pluginName)
    {
        $this->pluginName = $pluginName;
        $this->_custom_id = (int)$_custom_id;
        if (!defined('VM_VERSION')) define('VM_VERSION', '3.0');
        $languageHandlerFactory = new CustomFieldsForAllLanguageHandlerFactory();
        $this->languageHandler = $languageHandlerFactory->get();
    }

    /**
     * Get the singleton customfield instance
     *
     * @param $custom_id
     * @param string $pluginName
     * @return Customfield
     * @throws \RuntimeException
     * @since 1.0
     */
    public static function getInstance($custom_id, $pluginName = 'CustomfieldsforallBase')
    {
        if (empty(self::$instances[$custom_id])) {
            self::$instances[$custom_id] = new Customfield($custom_id, $pluginName);
        }
        return self::$instances[$custom_id];
    }

    /**
     * Get the custom's parent id
     *
     * @return int
     * @since 1.1.0
     */
    public function getParentId()
    {
        if ($this->parent_id === null) {
            $params = $this->getCustomfieldParams();
            $this->parent_id = isset($params['parent_id']) ? (int)$params['parent_id'] : 0;
        }
        return $this->parent_id;
    }


    public function getLevel($level = 0)
    {
        if ($this->getParentId() > 0) {
            $level++;
            $level = self::getInstance($this->getParentId())->getLevel($level);
        }
        return $level;
    }

    /**
     * Returns all the values (labels,id,etc..) of a custom field
     *
     * @param string $field
     * @return array
     * @since 1.0
     */
    public function getCustomValues($field = '')
    {
        if ($this->_customvalues === null && $this->_custom_id!==null) {
            try {
                $db = Factory::getDbo();
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from($db->quoteName('#__virtuemart_custom_plg_customsforall_values'));
                $query->where('virtuemart_custom_id=' . (int)$this->_custom_id);
                $query->order('ordering ASC');
                $db->setQuery($query);
                $results = $db->loadObjectList('customsforall_value_id');
            } catch (\RuntimeException $e) {
                Log::add(sprintf('Cannot get custom values:: error:%s', $e->getMessage()));
            }

            $this->_customvalues = $results;
        }
        $field = (string)$field;
        // return an array only with that field
        if(!empty($field)) {
            foreach ($results as $res) {
                $new_results[$res->customsforall_value_id] = $res->$field;
            }
            return $new_results;
        }
        return $this->_customvalues;
    }

    /**
     * Get and returns data about a custom value also about the price variant for a specific product when product_id is used
     *
     * @param int $custom_value_id
     * @param int $product_id
     * @return \stdClass
     * @since 1.0
     *
     */
    public static function getCustomValue($custom_value_id = 0, $product_id = 0, $value_product_id = 0)
    {
        $vmCompatibility = VmCompatibilityCF::getInstance();
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->select('*');
        $q->from('#__virtuemart_custom_plg_customsforall_values AS cf');
        // from 1.7 and then get the price from the virtuemart_product_customfields table
        if ($product_id) {
            $q->select('cf_p.' . $vmCompatibility->getColumnName('custom_price') . ' AS custom_price');
            $q->leftJoin('#__virtuemart_product_custom_plg_customsforall AS cf_pr ON cf.customsforall_value_id=cf_pr.customsforall_value_id');
            $q->innerJoin('#__virtuemart_product_customfields  AS cf_p ON cf_pr.customfield_id=cf_p.virtuemart_customfield_id');
            $q->where('cf_pr.virtuemart_product_id=' . (int)$product_id);
        }
        if ($value_product_id) {
            $q->select('cf_p.' . $vmCompatibility->getColumnName('custom_price') . ' AS custom_price');
            $q->leftJoin('#__virtuemart_product_custom_plg_customsforall AS cf_pr ON cf.customsforall_value_id=cf_pr.customsforall_value_id');
            $q->innerJoin('#__virtuemart_product_customfields  AS cf_p ON cf_pr.customfield_id=cf_p.virtuemart_customfield_id');
            $q->where('cf_pr.id=' . (int)$value_product_id);
        }
        if ($custom_value_id) {
            $q->where('cf.customsforall_value_id=' . (int)$custom_value_id);
        }
        $db->setQuery($q);
        $result = $db->loadObject();
        return $result;
    }

    /**
     * Returns all the value ids(or othe fields) of a product for a specific custom field
     *
     * @param mixed $product_id array or int
     * @param string $fields the fields to return (based on the database fields of the queried tables)
     * @param int $customfield_id
     * @param string $order the query's ordering
     * @return  array
     * @since    1.0
     */
    public function getProductCustomValues($product_id, $fields = '*', $customfield_id = 0, $order = null, $parent_id = 0)
    {
        $customfield_id = (int)$customfield_id;
        $key = md5(json_encode($product_id) . '_' . $this->_custom_id . '_' . $customfield_id . '_' . $fields . '_' . $parent_id);
        if (empty(self::$_product_customvalue_ids[$key])) {

            // if we have to get all the fields then we need the compatibility for the price
            if ($fields == '*') {
                $vmCompatibility = VmCompatibilityCF::getInstance();
                $fields .= ' ,' . $vmCompatibility->getColumnName('custom_price') . ' AS custom_price';
            }
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($fields);
            $query->from('`#__virtuemart_product_custom_plg_customsforall` AS p_cf');
            $query->innerJoin('`#__virtuemart_custom_plg_customsforall_values` AS cf ON p_cf.customsforall_value_id=cf.customsforall_value_id');
            $query->leftJoin('#__virtuemart_product_customfields  AS cf_p ON p_cf.customfield_id=cf_p.virtuemart_customfield_id');
            if (!is_array($product_id)) {
                $query->where('p_cf.virtuemart_product_id=' . (int)$product_id);
            } else {
                $query->where('p_cf.virtuemart_product_id IN(' . implode(',', $product_id) . ')');
            }
            if ($customfield_id) {
                $query->where('p_cf.customfield_id=' . $customfield_id);
            }

            if ($parent_id) {
                $query->where('cf.parent_id=' . (int)$parent_id);
            }

            if (empty($order)) {
                if (!empty($customfield_id)) {
                    $query->order('p_cf.customfield_id, cf.ordering ASC');
                } else {
                    $query->order('cf.ordering ASC');
                }
            } else {
                $query->order($order);
            }

            //if there is $parent_id, it could point to another custom
            if (empty($parent_id)) {
                $query->where('cf.virtuemart_custom_id=' . (int)$this->_custom_id);
            }
            $db->setQuery($query);

            if (strpos($fields, '*') !== false || strpos($fields, ',') !== false) {
                $results = $db->loadObjectList();
            }             // single field
            else {
                $results = $db->loadColumn();
            }

            self::$_product_customvalue_ids[$key] = $results;
        }
        return self::$_product_customvalue_ids[$key];
    }

    /**
     * Stores/updates the custom values table
     *
     * @param $data_array
     * @param bool $set_ordering
     * @return array
     * @throws \Exception
     * @since 1.0
     */
    public function store($data_array, $set_ordering = true)
    {
        Table::addIncludePath(JPATH_PLUGINS . '/vmcustom/' . $this->pluginName . '/tables');
        $stored_ids = [];
        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            return $stored_ids;
        }

        $filterInput = CustomfieldsForAllFilter::getInstance(); //filter
        $ordering = 1;

        $custom_params = $this->getCustomfieldParams($this->_custom_id);
        $data_type = $custom_params['data_type'];
        $table = Table::getInstance('Customvalues', 'Table');
        $unique_column_names = $table->getUniqueColumnNames();

        $this->findExistingUnsetDuplicates($data_array, $with_empty_pk = true);
        $newly_inserted = []; //stores all the new inserted values then check for duplicates between the new values

        foreach ($data_array as $data_row) {
            //Null values are not allowed
            $value_name = $data_row['customsforall_value_name'];
            if (!is_array($value_name)) {
                $value_name = [$value_name];
            }
            $value_name = array_map('trim', $value_name);
            //filter the values
            $data_row['customsforall_value_name'] = array_map(function ($value) use ($data_type) {
                $filterInput = CustomfieldsForAllFilter::getInstance();
                $result = $filterInput->clean($value, $data_type);
                return $result;
            }, $value_name);
            $customsforall_value_label = isset($data_row['customsforall_value_label']) ? trim($data_row['customsforall_value_label']) : '';
            $data_row['customsforall_value_label'] = $filterInput->clean($customsforall_value_label, 'string');
            $row_key = '';
            if (is_array($data_row['customsforall_value_name'])) {
                $data_row['customsforall_value_name'] = implode('|', $data_row['customsforall_value_name']);
            }
            foreach ($unique_column_names as $column_name) {
                $row_key .= md5($data_row[$column_name]);
            }
            $is_new_and_exist = false;

            if (in_array($row_key, $newly_inserted)) {
                $is_new_and_exist = true;
            }

            if ((!empty($data_row['customsforall_value_name']) || $data_row['customsforall_value_name'] == 0) && $is_new_and_exist === false) {

                $row = Table::getInstance('Customvalues', 'Table');
                if ($set_ordering && isset($ordering)) {
                    $data_row['ordering'] = $ordering;
                }
                $data_row['virtuemart_custom_id'] = $this->_custom_id;

                if (!$row->bind($data_row)) {
                    \vmdebug('CF4All Error binding: ', $row->getError());
                    continue;
                }

                if (!$row->check()) {
                    \vmdebug('CF4All Error Checking: ', $row->getError());
                    continue;
                }
                if ($row->store()) {
                    $newly_inserted [] = $row_key;
                    $stored_ids[] = $row->customsforall_value_id;
                    $data_row['customsforall_value_id'] = $row->customsforall_value_id;
                    $this->storeLanguageData($data_row);
                    $ordering = (int)$row->ordering + 1;
                } else {
                    \vmdebug('CF4All Error Inserting Values: ', $row->getError());
                }
            } else {
                //is invalid
                if ($is_new_and_exist) {
                    $msg = Text::sprintf('PLG_CUSTOMSFORALL_NOTICE_VALUE_EXIST_CANNOT_SAVED', $value_name);
                    $app->enqueueMessage($msg, 'notice');
                }
                if (!empty($data_row['customsforall_value_name'])) {
                    $msg = Text::sprintf('PLG_CUSTOMSFORALL_NOTICE_VALUE_INVALID_CANNOT_SAVED', $value_name);
                    $app->enqueueMessage($msg, 'notice');
                }
            }
        }
        return $stored_ids;
    }

    /**
     * Save data to the language tables
     *
     * @param array $data_row
     * @return boolean
     * @throws \Exception
     * @since 1.0
     */
    protected function storeLanguageData($data_row)
    {
        $filterInput = CustomfieldsForAllFilter::getInstance(); // filter
        $languages = $this->languageHandler->getLanguages($withDefault = false);
        $languageTables = $this->languageHandler->getTranslator()->getLanguageTables();
        foreach ($languages as $language) {

            // no language data or no table created
            if (empty($data_row[$language->lang_code])) {
                continue;
            }

            if (!isset($languageTables[$language->db_code])) {
                \vmError('The language tables for the "Custom Fields For All" plugin are missing. Please go the custom field and save it, to create the tables.');
                continue;
            }

            $langFields = $data_row[$language->lang_code];

            $new_data_row = [
                'id' => !empty($langFields['id']) ? $langFields['id'] : (!empty($data_row['id']) ? $data_row['id'] : ''),
                'customsforall_value_id' => $data_row['customsforall_value_id'],
                'customsforall_value_name' => !empty($langFields['customsforall_value_name']) ? $langFields['customsforall_value_name'] : $data_row['customsforall_value_name'],
                'customsforall_value_label' => isset($langFields['customsforall_value_label']) ? $filterInput->clean($langFields['customsforall_value_label'], 'string') : ''
            ];
            $db = Factory::getDbo();
            $row = new TableCustomvalueLanguage($db, $language->db_code);

            try {
                if (!$row->bind($new_data_row)) {
                    \vmdebug('CF4All Error binding: ', $row->getError());
                }
            } catch (\Exception $e) {
                Log::add('Error binding Custom Fields For All table for the language:' . $language->lang_code);
                throw $e;
            }

            if (!$row->check()) {
                \vmdebug('CF4All Error Checking Language Table: ', $row->getError());
                continue;
            }

            if (!$row->store()) {
                \vmError('CF4All Error Inserting Values to the Language Table: ', $row->getError() . ' CF4All Error Inserting Values to the Language Table:' . $language->lang_code);
                continue;
            }
        }
        return true;
    }

    /**
     * Deletes the custom values and their connections
     *
     * @param array $ids
     * @since    1.0
     */
    public function delete($ids)
    {
        Table::addIncludePath(JPATH_PLUGINS . '/vmcustom/' . $this->pluginName . '/tables');
        $db = Factory::getDbo();
        $q = $db->getQuery(true);

        foreach ($ids as $id) {
            $row = Table::getInstance('Customvalues', 'Table');
            if (!$row->delete($id)) {
                \vmdebug('Error deleting Custom Value: ', $id . ' ' . $row->getError());
            } else {
                //if deleted successfully clean also the connections with other tables
                $q->delete('#__virtuemart_product_custom_plg_customsforall');
                $q->where('customsforall_value_id=' . (int)$id);
                $db->setQuery($q);
                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    Log::add(sprintf('CF4All Error deleting Value\'s connections for id: %s. ERROR: %s', $id, $e->getMessage()));
                    \vmdebug('CF4All Error deleting Value\'s connections: ', $id . ' ' . $e->getMessage());
                }
                $q->clear();
            }
        }
    }

    /**
     * Store the product-custom value id associations
     *
     * @param $value_ids
     * @param $product_id
     * @param int $customfield_id
     * @return array|bool
     * @since 1.0
     */
    public function storeProductValues($value_ids, $product_id, $customfield_id = 0)
    {
        $params = $this->getCustomfieldParams();
        $value_ids = array_filter($value_ids);
        $parent_value_ids = [];
        $db = Factory::getDbo();

        foreach ($value_ids as $id) {
            self::$assigned_customvalue_ids[] = $id;
            //check if the combination product_id - custom_value_id exist in the db table
            $q = $db->getQuery(true);
            $q->select('customsforall_value_id,customfield_id');
            $q->from('#__virtuemart_product_custom_plg_customsforall');
            $q->where('customsforall_value_id=' . (int)$id);
            $q->where('virtuemart_product_id=' . (int)$product_id . ' LIMIT 1');
            $db->setQuery($q);
            $result = $db->loadObject();

            if (!empty($result) && empty($customfield_id) && !$params['is_price_variant']) {
                $customfield_id = $result->customfield_id;
            }

            if (empty($customfield_id)) {
                return false;
            }

            if (empty($result)) {//does not exist, so insert it
                $this->storeProductValue($id, $product_id, $customfield_id);
            } else {
                //exists for that product but with another customfield record. Updated it and put it here
                if ($result->customfield_id != $customfield_id) $this->storeProductValue($id, $product_id, $customfield_id, 'update');
            }

            //check for parents and save them
            if (!empty($this->getParentId())) {
                $custom_value = $this->getCustomValue($id);
                $parent_value_ids [] = $custom_value->parent_id;
            }
            unset($id);
        }

        // check if it has parents (dependent values)
        if (!empty($parent_value_ids)) {
            $parentCustomfield = self::getInstance($this->getParentId(), $this->pluginName);
            $customfield_id = $parentCustomfield->getVmProductCustomfieldId($product_id);
            if ($customfield_id === false) {
                self::$custom_order--;
                $customfield_id = $parentCustomfield->storeCustomfield($product_id, self::$custom_order);
            }
            $parentCustomfield->storeProductValues($parent_value_ids, $product_id, $customfield_id);
        }

        return self::$assigned_customvalue_ids;
    }

    /**
     * Store the product-custom value id assoc
     * Single
     *
     * @param int $value_id
     * @param int $product_id
     * @return bool
     * @since  1.0
     */
    protected function storeProductValue($value_id, $product_id, $customfield_id, $type = 'insert')
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);

        if ($type == 'insert') {
            $recordObject = new \stdClass();
            $recordObject->customsforall_value_id = (int)$value_id;
            $recordObject->virtuemart_product_id = (int)$product_id;
            $recordObject->customfield_id = (int)$customfield_id;
            $db->insertObject('#__virtuemart_product_custom_plg_customsforall', $recordObject);

        } else {
            $q->update('#__virtuemart_product_custom_plg_customsforall');
            $q->set('customfield_id=' . (int)$customfield_id);
            $q->where('customsforall_value_id=' . (int)$value_id);
            $q->where('virtuemart_product_id=' . (int)$product_id);
            $db->setQuery($q);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                Log::add(sprintf('CF4All Error updating product connection for value id: %s. ERROR: %s', $value_id, $e->getMessage()));
                \vmdebug('CF4All Error updating product connection: ', $value_id . ' ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param int $custom_id
     * @param int $product_id
     * @param bool $is_price_variant
     * @return boolean|int
     * @since 1.0
     */
    protected function storeCustomfield($product_id, $ordering = 0)
    {
        $db = Factory::getDbo();
        $recordObject = new \stdClass();
        $recordObject->virtuemart_product_id = (int)$product_id;
        $recordObject->virtuemart_custom_id = (int)$this->_custom_id;
        $recordObject->customfield_value = $this->pluginName;
        $recordObject->ordering = $ordering;
        if (!$db->insertObject('#__virtuemart_product_customfields', $recordObject)) {
            return false;
        }
        return $db->insertid();
    }

    /**
     * Delete the product's custom values which are other than the supplied $value_ids
     *
     * @param int $product_id
     * @param array $value_ids
     * @return  bool
     * @since   1.0
     */
    public static function deleteProductValues($product_id, $value_ids = [], $custom_ids_lookup = [])
    {
        $where = [];
        if (count($custom_ids_lookup) > 0) {
            $custom_ids_lookup = array_filter($custom_ids_lookup);
            $custom_ids_lookup = ArrayHelper::toInteger($custom_ids_lookup);
            $where[] = "val.virtuemart_custom_id IN(" . implode(',', $custom_ids_lookup) . ")";
        }

        if (count($value_ids) > 0) {
            $value_ids = array_filter($value_ids);
            $value_ids = ArrayHelper::toInteger($value_ids); // sanitize them
            $where[] = "prd_v.customsforall_value_id NOT IN(" . implode(',', $value_ids) . ")";
        }

        $db = Factory::getDbo();
        $where[] = "prd_v.virtuemart_product_id=" . (int)$product_id;

        $q = "
			DELETE prd_v FROM #__virtuemart_product_custom_plg_customsforall AS prd_v
			INNER JOIN #__virtuemart_custom_plg_customsforall_values AS val ON val.customsforall_value_id=prd_v.customsforall_value_id";

        if (count($where) > 0) {
            $q .= " WHERE " . implode(" AND ", $where);
        }

        try {
            $db->setQuery($q);
            $db->execute();
        } catch (\RuntimeException $e) {
            Log::add(sprintf('CF4All Error deleting all product connections : %s. ERROR: %s', $e->getMessage()));
            \vmdebug('CF4All Error deleting product customforall connections: ', $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Delete all the connections of a product for using custom ids.
     * Delete all the values which do not belong to the supplied fields (custom ids)
     *
     * @param $product_id
     * @param bool $custom_ids_to_product
     * @return bool
     * @since 1.0
     */
    public static function deleteAllProductAssociations($product_id, $custom_ids_to_product = false)
    {
        $db = Factory::getDbo();
        $where = [];

        if (!empty($custom_ids_to_product) && is_array($custom_ids_to_product)) {
            $custom_ids_to_product = array_filter($custom_ids_to_product);
            $custom_ids_to_product = ArrayHelper::toInteger($custom_ids_to_product); // sanitize them
            $where[] = "val.virtuemart_custom_id NOT IN(" . implode(',', $custom_ids_to_product) . ")";
        }
        $where[] = "prd_v.virtuemart_product_id=" . (int)$product_id;

        try {
            $q = "
    			DELETE prd_v FROM #__virtuemart_product_custom_plg_customsforall AS prd_v
    			INNER JOIN #__virtuemart_custom_plg_customsforall_values AS val ON val.customsforall_value_id=prd_v.customsforall_value_id
    			WHERE " . implode(" AND ", $where);

            $db->setQuery($q);
            $db->execute();
        } catch (\RuntimeException $e) {
            Log::add(sprintf('CF4All Error deleting all product connections for value id: ERROR: %s', $e->getMessage()));
            \vmdebug('CF4All Error deleting all product connections: ', $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Get and return an array which contains only a specified field from an associative array
     *
     * @param array $array
     * @param string $field
     * @return  array
     * @since    1.0
     */
    public static function getField($array, $field = 'customsforall_value_id')
    {
        $new_array = [];
        foreach ($array as $el) {
            $new_array[] = $el[$field];
        }
        return $new_array;
    }

    /**
     * Gets an assoc array and checks if the value of each array element exists in a db table
     * If exists unset it.
     * The returned array contains the ids of all the deleted elements
     *
     * @param array $array
     * @param bool $with_empty_pk
     * @return array
     * @since 1.0
     */
    public function findExistingUnsetDuplicates(&$array, $with_empty_pk = false)
    {
        Table::addIncludePath(JPATH_PLUGINS . '/vmcustom/' . $this->pluginName . '/tables');
        $table = Table::getInstance('Customvalues', 'Table');
        $comparison_field = $table->getUniqueColumnNames();

        /*
         * Atm it works only for the 'virtuemart_custom_plg_customsforall_values' tabele as it is using its getCustomValues function.
         * To be used for other tables in the future select query should be called here
         */
        $db_rows = $this->getCustomValues('*');
        $existing_ids = [];
        $pk = 'customsforall_value_id';

        if (count($db_rows) == 0) {
            return $existing_ids; // nothing in the db so all are valid
        }

        // it's not the best way but it's fast
        $comparison_field0 = $comparison_field[0];
        if (isset($comparison_field[1])) {
            $comparison_field1 = $comparison_field[1];
        }

        foreach ($array as $key => $el) {
            // that's for multi-colors
            if (is_array($el[$comparison_field0])) {
                $el2 = implode('|', $el[$comparison_field0]);
            } else {
                $el2 = trim($el[$comparison_field0]);
            }

            $found_key = false;
            foreach ($db_rows as $id => $db_row) {
                if ($db_row->$comparison_field0 == $el2) {
                    if (isset($comparison_field1)) {
                        if ($db_row->$comparison_field1 == $el[$comparison_field1]) {
                            if ($with_empty_pk && empty($el[$pk])) {
                                $found_key = $key;
                                $found_id = $id;
                                break;
                            } elseif ($with_empty_pk === false) {
                                $found_key = $key;
                                $found_id = $id;
                                break;
                            }
                        }
                    } else {
                        if ($with_empty_pk && empty($el[$pk])) {
                            $found_key = $key;
                            $found_id = $id;
                            break;
                        } elseif ($with_empty_pk === false) {
                            $found_key = $key;
                            $found_id = $id;
                            break;
                        }
                    }
                }
            }
            if ($found_key !== false) {
                $existing_ids[] = $found_id;
                unset($array[$found_key]);
            }
        }
        return $existing_ids;
    }

    /**
     * Gets an array of objects and checks for duplicates
     * Returns only uniques records based on the $comparison_field
     *
     * @param array $array
     * @param string $comparison_field
     * @return  array
     * @since    3.0
     */
    public function array_unique($array, $comparison_field = 'id')
    {
        $new_array = array();
        if (!empty($array)) {
            foreach ($array as $el) {
                if (!isset($new_array[$el->$comparison_field])) {
                    $new_array[$el->$comparison_field] = $el;
                }
            }
        }
        return $new_array;
    }

    /**
     * Function that checks if this is the 1st customforall in this product form
     *
     * @param int $row
     * @param array $data
     * @return bool
     * @throws \Exception
     * @since 1.0
     */
    public function isFirstCustomOfType($row, $data)
    {
        $jinput = Factory::getApplication()->input;
        $custom_plugins = $jinput->get($this->_paramName, array(), 'array');
        $first = '';

        foreach ($custom_plugins as $key => $plg) {
            if (isset($plg[$this->pluginName])) {
                $first = $plg[$this->pluginName]['row'];
                break;
            }
        }
        if ($first == $row) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the params of a plugin with a given id
     *
     * @param int $custom_id
     * @return array|bool|mixed
     * @since 1.0
     */
    public function getCustomfieldParams($custom_id = 0)
    {
        if (empty($custom_id) && empty($this->_custom_id)) {
            return [];
        }
        //fallback. Remove the $custom_id in the future
        if (empty($custom_id)) {
            $custom_id = $this->_custom_id;
        }
        if ($this->_customparams === null) {
            $db = Factory::getDbo();
            $q = $db->getQuery(true);
            $q->select('custom_params');
            $q->from('#__virtuemart_customs');
            $q->where('virtuemart_custom_id=' . (int)$custom_id);
            $db->setQuery($q);
            $custom_params = $db->loadResult();

            if (empty($custom_params)) return false;
            $custom_param_array = explode('|', $custom_params);
            $params_array = array();
            foreach ($custom_param_array as $var) {
                $values = explode('=', $var);

                if (isset($values[0]) && isset($values[1])) {
                    $params_array[$values[0]] = json_decode($values[1]);//removes the double quotes
                }
                unset($values);
            }
            $this->_customparams = $params_array;
        }
        //if(isset($this->_customparams->parent_custom))
        return $this->_customparams;
    }

    /**
     * Check if any customforall is set for this product
     *
     * @param $product_id
     * @return bool
     * @throws \Exception
     * @since 1.0
     */
    public function isCustomSetInProduct($product_id)
    {
        if (!isset(self::$_isCustomSetInProduct[$product_id])) {
            $jinput = Factory::getApplication()->input;
            $custom_plugins = $jinput->get($this->_paramName, array(), 'array');
            $found = false;

            foreach ($custom_plugins as $key => $plg) {
                //check if a customforall plugin exists in the product
                if (isset($plg[$this->pluginName])) {
                    $found = true;
                }
            }
            self::$_isCustomSetInProduct[$product_id] = $found;
        }
        return self::$_isCustomSetInProduct[$product_id];
    }

    /**
     * Check if there is any customforall already stored for this product
     * The function is usefull for scripts loading where we should use different approach when there is a storage
     * And different when the custom field is created on the fly with ajax
     *
     * @param int $product_id
     * @return bool
     * @since  1.0
     */
    public function isCustomStoredInProduct($product_id)
    {
        if (empty(self::$_customStored[$product_id])) {
            $db = Factory::getDbo();
            $q = $db->getQuery(true);
            $q->select('1');
            $q->from('#__virtuemart_product_custom_plg_customsforall AS prd_forall');
            $q->innerJoin('#__virtuemart_product_customfields AS prd_customs ON prd_customs.virtuemart_customfield_id=prd_forall.customfield_id');
            $q->where('prd_forall.virtuemart_product_id=' . (int)$product_id . ' LIMIT 1');
            $db->setQuery($q);
            self::$_customStored[$product_id] = $db->loadResult();
        }
        return self::$_customStored[$product_id];
    }

    /**
     * If the current record has no customfield we should get that from the db (virtuemart_product_customfields)
     * New records has no customfield id as this is not included in the POST variabe (from the product form)
     * Although since vm stores first the customfield to the product_customfield table and then calls the plugins
     * we can get it using its position
     *
     * @param $product_id
     * @param int $position
     * @return bool
     * @since 1.0
     */
    function getVmProductCustomfieldId($product_id, $position = 0)
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->select('virtuemart_customfield_id');
        $q->from('#__virtuemart_product_customfields');
        $q->where('virtuemart_product_id=' . (int)$product_id);
        $q->where('virtuemart_custom_id=' . (int)$this->_custom_id);
        $q->order('ordering ASC');
        $db->setQuery($q);
        $custom_field_ids = $db->loadObjectList();

        foreach ($custom_field_ids as $counter => $cf) {
            \vmdebug('Cf4all virtuemart_customfield_id for position:' . $position . ' is: ', $cf->virtuemart_customfield_id);
            if ($counter == $position) {
                return $cf->virtuemart_customfield_id;
            }
        }
        return false;
    }

    /**
     * Loads the necessary scripts and filesfor the customfields to the page
     *
     * @since    1.0
     */
    public function loadStylesScripts()
    {
        $jinput = Factory::getApplication()->input;
        $view = $jinput->get('view', '', 'STRING');
        $scripts_loaded = $jinput->get('scripts_loaded', false, 'BOOLEAN');

        //if the scripts are not already loaded
        if (!$scripts_loaded) {
            $is_custom_view = false;
            $is_stored = false;

            if (!empty($virtuemart_custom_id) && $view == 'custom') $is_custom_view = true;
            else {
                $virtuemart_product_id = $jinput->get('virtuemart_product_id', array(), 'ARRAY');
                $virtuemart_product_id = end($virtuemart_product_id);
                $is_stored = $this->isCustomStoredInProduct($virtuemart_product_id);
            }

            /*
             * If there is a record in the custom view we can load it to the document
             * if there is already a record to the product we can load it to the document
             */
            if ((!empty($this->_custom_id) && $is_custom_view) || (!$is_custom_view && $is_stored)) {
                $color_script_uri = Uri::root(true) . '/plugins/system/customfieldsforallbase/view/admin/js/jscolor/jscolor.js';
                $css_be_uri = Uri::root(true) . '/plugins/system/customfieldsforallbase/view/admin/css/backend.css';
                $document = Factory::getDocument();
                $document->addScript($color_script_uri);
                $document->addStyleSheet($css_be_uri);
                $jinput->set('scripts_loaded', true);
            }
        }
    }

    /**
     * @param $customfield_id
     * @return mixed
     * @todo To be removed. Use getVmProductCustomfieldId which should return the entire object
     * @since 1.0
     */
    public function getProductFromCustomfield_id($customfield_id)
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->select('virtuemart_product_id');
        $q->from('#__virtuemart_product_customfields');
        $q->where('virtuemart_customfield_id=' . (int)$customfield_id);
        $db->setQuery($q);
        $product_id = $db->loadResult();
        return $product_id;
    }

    /**
     * Generates the html code for the custom values
     *
     * This function can be used by any page that displays static/non selectable custom fields
     * e.g. Product Details page, Cart, Orders, Invoices
     *
     * @param $option
     * @param string $display_type
     * @param string $class
     * @param bool $inline_css
     * @param bool $display_color_label
     * @param bool $isPDF
     * @return bool|string
     * @since 1.0
     */
    public static function displayCustomValue(
        $option,
        $display_type = 'button',
        $class = 'cf4all_color_btn_medium',
        $inline_css = false,
        $display_color_label = false,
        $isPDF = false)
    {
        if (empty($option->customsforall_value_name)) {
            return false;
        }
        $languageHandlerFactory = new CustomFieldsForAllLanguageHandlerFactory();
        $languageHandler = $languageHandlerFactory->get();
        $filterInput = CustomfieldsForAllFilter::getInstance();
        $outside_label = '';
        $style = '';
        if ($display_type == 'color' || $display_type == 'color_multi') {
            $custom_value_name_multi = explode('|', $option->customsforall_value_name);
            $label_html = '';
            $count_multi_values = count($custom_value_name_multi);
            $width = 100 / $count_multi_values;
            $min_width = '0.8em';

            foreach ($custom_value_name_multi as $custom_value_name) {
                // validate hex color values
                $color = $filterInput->checkNFormatColor($custom_value_name);
                if ($color === false) {
                    return false;
                }

                /**
                 * This is a stupid hack we have to use for setting width to the spans in TCPDF
                 * It ignores any width style of the elements.
                 * Also by using divs, it shows them full width.
                 * Seems like the only workaround is to use spans and define their width by using content inside them
                 */
                $inlineContent = '';
                if ($isPDF) {
                    // str_pad can take a single character. hence we pass ~ and we replace it with '&nbsp;'
                    $inlineContent = str_replace('~', '&nbsp;', str_pad('', (int)6 / $count_multi_values, '~'));
                }
                $label_style = 'background-color:' . $color . '; height:1em; min-width:' . $min_width . '; width:' . $width . '%; display:inline-block';
                $label_html .= '<span class="cf4all_inner_value" style="' . $label_style . '"> ' . $inlineContent . '</span>';
            }

            if ($inline_css) {
                $style = 'style="height: 1em; font-weight: 500; border: 1px solid #474949; color:#ffffff; text-shadow:-1px 1px #444444; display:block; width:40px"';
            }
            $outside_label = '';

            if ($display_color_label) {
                if (!empty($option->customsforall_value_label)) {
                    $outside_label .= $languageHandler->__($option, $lang = '', $group = 'label');
                    if ($count_multi_values == 1) {
                        $outside_label .= '<span class="cf4all_actual_value"> (' . $option->customsforall_value_name . ') </span>';
                    }
                } else {
                    $outside_label .= '(' . $languageHandler->__($option) . ')';
                }
            }
            $class .= ' cf4all_color_btn';
            $label = $label_html;
        } else {
            $class = ''; // class used only for colors. Otherwise it will be text following the styling of every text
            $label = $languageHandler->__($option);
            while (!empty($option->parent_id)) {
                $option_parent = self::getCustomValue($option->parent_id);
                $label = $languageHandler->__($option_parent) . ' - ' . $label;
                $option = $option_parent;
            }
        }
        if (!empty($outside_label)) {
            $outside_label = '<span class="cf4all_outside_label">' . $outside_label . '</span>';
        }

        $html = '
        <span class="cf4all_option ' . $class . '" id="cf4all_option_' . $option->customsforall_value_id . '" ' . $style . '>'
            . $label .
            '</span> '
            . $outside_label;

        return $html;
    }
}
