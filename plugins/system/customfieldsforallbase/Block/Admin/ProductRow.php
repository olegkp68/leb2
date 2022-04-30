<?php

/**
 * @package		CustomfieldsforallBase
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;

/**
 * Block class that gives values to the layouts
 * @author sakis
 *
 */
class ProductRow
{

    /**
     *
     * @var Customfield
     */
    protected $custom;

    /**
     *
     * @var string
     */
    protected $pluginName;

    /**
     *
     * @var int
     */
    protected $product_id;

    /**
     *
     * @var int
     */
    protected $virtuemart_custom_id = 0;

    /**
     * 
     * @param string $pluginName
     * @param int $product_id
     * @param int $virtuemart_custom_id
     */
    public function __construct($pluginName, $product_id, $virtuemart_custom_id)
    {
        $this->pluginName = $pluginName;
        $this->product_id = $product_id;
        $this->virtuemart_custom_id = $virtuemart_custom_id;
    }

    /**
     * 
     * @return Customfield
     */
    public function getCustom()
    {
        if ($this->custom === null) {
            $this->custom = Customfield::getInstance($this->virtuemart_custom_id, $this->pluginName);
        }
        return $this->custom;
    }
    
    /**
     * 
     * @return number
     */
    public function getVirtuemartCustom_id()
    {
        return $this->virtuemart_custom_id;
    }
    
    /**
     * 
     * @return array
     */
    public function getCustomParams()
    {
        return $this->getCustom()->getCustomfieldParams($this->virtuemart_custom_id);
    }
    
    /**
     * 
     * @return array
     */
    public function getCustomValues($with_parent = false)
    {
        $valueObjectList = $this->getCustom()->getCustomValues();
        $customParam = $this->getCustom()->getCustomfieldParams();
        
        if($with_parent && !empty($customParam['parent_id'])) {
            //get the parent custom field values
            $customField = Customfield::getInstance($customParam['parent_id'], $this->pluginName);
            $parentValueObjectList = $customField->getCustomValues();
             
            foreach ($valueObjectList as $obj) {
                if ($obj->parent_id) {
                    //Avoid recursion. Can happen with price variants which assgned multiple times
                    if(strpos($obj->customsforall_value_name, $parentValueObjectList[$obj->parent_id]->customsforall_value_name) === false) {
                        $obj->customsforall_value_name = $parentValueObjectList[$obj->parent_id]->customsforall_value_name . ' > ' . $obj->customsforall_value_name;
                    }
                }
            }
        }        
        return $valueObjectList;
    }
    
    /**
     *
     * @param number $customfield_id            
     * @return array
     */
    public function getProductValueIds($customfield_id = 0)
    {
        if (empty($customfield_id)) {
            return [];
        }        
        $product_value_ids = $this->getCustom()->getProductCustomValues($this->product_id, 'p_cf.customsforall_value_id AS customsforall_value_id', $customfield_id);
        return $product_value_ids;
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function isHexColor($value)
    {
        $ishex = false;
        if (strpos($value, '#') !== false) {
            $ishex = true;
        }
        return $ishex;
    }
}