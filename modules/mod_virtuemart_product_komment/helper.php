<?php
    defined ('_JEXEC') or  die('Direct Access to ' . basename (__FILE__) . ' is not allowed.');
/*
 * Module Helper
 * just for legacy, will be removed
 * @package VirtueMart
 * @copyright (C) 2011 - 2014 The VirtueMart Team
 * @Email: max@virtuemart.net
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * www.virtuemart.net
 */
    
class mod_virtuemart_product_komment {
    
    static $globalIndex = 0;

    static function addText($text) {
        echo $text;
    }

    static function getProductsComments($cart) {
        $text = '';
        if (is_object($cart) && isset($cart->products) && is_array($cart->products) && count($cart->products)) {
            $productIds = array();
            foreach($cart->products as $product) {
                $productIds[] = $product->virtuemart_product_id;
            }
            $user =& JFactory::getUser();
            $text = mod_virtuemart_product_komment::getUserCommentsTable($user->id, $productIds);
        }
        return $text;
    }

    static function updateOrderProductsComments($order) {
        $text = '';
        //var_dump(__LINE__.':'.self::$globalIndex++);
        if (is_object($order) && isset($order->orderDetails) && is_array($order->orderDetails)) {
            $productIds = array();
            foreach($order->orderDetails['items'] as $product) {
                $productIds[] = $product->virtuemart_product_id;
            }
            $userId = $order->orderDetails['details']['BT']->virtuemart_user_id;
            $orderId = $order->orderDetails['details']['BT']->virtuemart_order_id;
            $customerNote = $order->orderDetails['details']['BT']->customer_note;

            $text = mod_virtuemart_product_komment::getUserCommentsTable($userId, $productIds);

            if ($text) {
                //var_dump(__LINE__);
                $db =& JFactory::getDBO();
                
                $customerNote = empty($customerNote) ? $text : $customerNote . '<br/>' . $text;
                // update customer note & clean komments
                $query = 'DELETE FROM `leb_vm_komment` WHERE user_id = '.$userId;
                $db->setQuery($query);
                $db->execute();
                $query = 'UPDATE `j3_virtuemart_order_userinfos` SET `customer_note` = '.$db->quote($customerNote).' WHERE virtuemart_order_id = '.$orderId;
                $db->setQuery($query);
                $db->execute();
            }
            return $customerNote;
        }
    }

    protected static function getUserCommentsTable($userId, array $productIds) {
        $commentText = '';
        if ($userId && !empty($productIds)) {
            $db = & JFactory::getDBO();
            $query = 'SELECT * FROM `leb_vm_komment` WHERE user_id = '.$userId.' and prod_id in ('. implode(',', $productIds).') ';
            $db->setQuery($query);
            if ($data_rows_assoc_list = $db->loadAssocList()) {
                $komments = array();
                foreach($data_rows_assoc_list as $row) {
                    $komment = trim($row['komment']);
                    if (!empty($komment)) {
                        $komments[] = '<tr><td>'.$row['prod_name'].'</td><td style="padding-left: 10px;">'.$row['komment'].'</td></tr>';
                    }
                }
                if (!empty($komments)) {
                    $commentText = '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr style="border:1px solid #CCCCCC"><td style="font-weight:bold" width="50%">Наименование товара</td><td style="font-weight:bold;padding-left: 10px;" width="50%">Пожелания</td></tr>';
                    $commentText .= implode('', $komments);
                    $commentText .= '</table>';
                }
            }
        }
        return $commentText;  
    }
}

class ModVirtuemartProductKommentHelper {
        
    public static function submitKommentAjax() {

        /* Neue Satz anlegen bzw. aendern */
        $user_id = intval($_POST['user_id']);
        $prod_id = intval($_POST['prod_id']);
        $product_name = $_POST['product_name'];
        $komment = trim($_POST['komment']);

        /* Neue Satz anlegen bzw. aendern */
        $suche = 0;

        if ($user_id != 0) {

            $db = & JFactory::getDBO();
            $select_satz = "select * from leb_vm_komment where user_id = '$user_id' and prod_id=$prod_id";
            $db->setQuery($select_satz);
            if ($row = $db->loadAssoc()) {
                if (empty($komment)) {
                    // remove
                    $andern = "DELETE FROM leb_vm_komment WHERE prod_id=$prod_id AND user_id=$user_id";
                } else {
                    $andern = "UPDATE leb_vm_komment SET komment=".$db->quote($komment)." WHERE prod_id=$prod_id AND user_id=$user_id";
                }
                $db->setQuery($andern);
                $db->execute();
                echo '<b style="line-height:34px">Пожелание изменено! ' . date('H:i:s') . '</b>';
            } else {
                if (empty($komment)) {
                    // nothing to do
                } else {
                    $satz_neu = "insert into leb_vm_komment (user_id, prod_id, prod_name, komment) values ('$user_id', '$prod_id', ".$db->quote($product_name).", ".$db->quote($komment).")";
                    $db->setQuery($satz_neu);
                    $db->execute();
                    echo '<b style="line-height:34px">Пожелание добавлено! ' . date('H:i:s') . '</b>';
                }   
            }
        } else {
            echo "<b style='line-height:34px; color:#FF0000'>Ошибка: Вы не авторизированы!</b>";
        }
        die();
    }

    /**
     * For logged user
     * leb-verlag.itera-research.com/?option=com_ajax&module=virtuemart_product_komment&format=debug&method=prepareExportData
     */
    public static function prepareExportDataAjax() {
        echo date('d-m-Y').'<br/>';
        
        $db = & JFactory::getDBO();
        $query = 'SELECT product_id, `attribute` FROM `leb2_vm_product` WHERE `attribute` > "" limit 100';
        $db->setQuery($query);
        if ($data_rows_assoc_list = $db->loadAssocList()) {
            $attributes = array();
            foreach($data_rows_assoc_list as $row) {
                $attributeData = $row['attribute'];
                $values = explode(',', $attributeData);
                if (count($values)>1) {
                    $attributeName = array_shift($values);
                    if (!array_key_exists($attributeName, $attributes)) {
                        $attributes[$attributeName] = array();
                    }
                    foreach($values as $value) {
                        if (!array_key_exists($value, $attributes[$attributeName])) {
                            $attributes[$attributeName][$value] = array();
                        }
                        $attributes[$attributeName][$value][] = $row['product_id']+1000;
                    }
                }
            }
//             Names:   SELECT * FROM `j3_virtuemart_customs` 
//                virtuemart_custom_id
//                custom_parent_id
//                virtuemart_vendor_id
//                custom_jplugin_id
//                custom_element
//                admin_only
//                custom_title
//                show_title
//                custom_tip
//                custom_value
//                custom_desc
//                field_type
//                is_list
//                is_hidden
//                is_cart_attribute
//                is_input
//                searchable
//                layout_pos
//                custom_params
//                shared
//                published
//             Values:  SELECT * FROM `j3_virtuemart_custom_plg_customsforall_values` (
//                customsforall_value_id
//                customsforall_value_name
//                customsforall_value_label
//                virtuemart_custom_id
//                ordering
//                
//             SELECT * FROM `j3_virtuemart_product_customfields`
//                virtuemart_customfield_id
//                virtuemart_product_id
//                virtuemart_custom_id
//                customfield_value
//                customfield_price
//                disabler
//                override
//                customfield_params
//                
//             Links:   SELECT * FROM `j3_virtuemart_product_custom_plg_customsforall` (
//                id, 
//                customsforall_value_id, 
//                virtuemart_product_id, 
//                customfield_id);
            
            // clean previous data
            //  $query = 'select * FROM `j3_virtuemart_customs` WHERE `created_by` < 0';
            $query = 'DELETE FROM `j3_virtuemart_customs` WHERE `created_by` < 0';
            $db->setQuery($query);
            $db->execute();
            //  $query = 'select * FROM `j3_virtuemart_custom_plg_customsforall_values` WHERE `virtuemart_custom_id` not in (SELECT virtuemart_custom_id FROM `j3_virtuemart_customs`)';
            $query = 'DELETE FROM `j3_virtuemart_custom_plg_customsforall_values` WHERE `virtuemart_custom_id` not in (SELECT virtuemart_custom_id FROM `j3_virtuemart_customs`)';
            $db->setQuery($query);
            $db->execute();
            //  $query = 'select * FROM `j3_virtuemart_product_customfields` WHERE `virtuemart_custom_id` not in (SELECT virtuemart_custom_id FROM `j3_virtuemart_customs`)';
            $query = 'DELETE FROM `j3_virtuemart_product_customfields` WHERE `virtuemart_custom_id` not in (SELECT virtuemart_custom_id FROM `j3_virtuemart_customs`)';
            $db->setQuery($query);
            $db->execute();
            //  $query = 'select * FROM `j3_virtuemart_product_custom_plg_customsforall` WHERE `customfield_id` not in (SELECT virtuemart_customfield_id FROM `j3_virtuemart_product_customfields`)';
            $query = 'DELETE FROM `j3_virtuemart_product_custom_plg_customsforall` WHERE `customfield_id` not in (SELECT virtuemart_customfield_id FROM `j3_virtuemart_product_customfields`)';
            $db->setQuery($query);
            $db->execute();
            
            foreach($attributes as $attributeName=>$values) {
                // get attribute id
                $index = 1;
                $attributeId = null;
                $query = 'SELECT virtuemart_custom_id FROM `j3_virtuemart_customs` WHERE `custom_title` = '.$db->quote($attributeName).' AND `created_by` = -1 limit 1';
                $db->setQuery($query);
                if ($row = $db->loadAssoc()) {
                    $attributeId = $row['virtuemart_custom_id'];
                } else {
                    $object = new stdClass();
                    $object->custom_title = $attributeName;
                    $object->custom_jplugin_id = 10077;
                    $object->custom_element = 'customfieldsforall';
                    $object->custom_value = 'customfieldsforall';
                    $object->field_type = 'E';
                    $object->is_cart_attribute = 1;
                    $object->layout_pos = 'addtocart';
                    $object->custom_params = 'display_type="select"|data_type="string"|is_required="0"|is_price_variant="1"|display_price="0"|';
                    $object->is_input = 1;
                    $object->created_by = -1;
                    //var_dump($object);
                    if ($db->insertObject('j3_virtuemart_customs', $object)) {
                        $attributeId = $db->insertid();
                    }
                }
                if ($attributeId) {
                    foreach($values as $key=>$productIds) {
                        $valueId = null;
                        $query = 'SELECT customsforall_value_id FROM `j3_virtuemart_custom_plg_customsforall_values` WHERE virtuemart_custom_id = '.$attributeId.' AND `customsforall_value_name` = '.$db->quote($key).' limit 1';
                        $db->setQuery($query);
                        if ($row = $db->loadAssoc()) {
                            $valueId = $row['customsforall_value_id'];
                        } else {
                            $object = new stdClass();
                            $object->customsforall_value_name = $key;
                            $object->customsforall_value_label = '';
                            $object->virtuemart_custom_id = $attributeId;
                            //var_dump($object);
                            if ($db->insertObject('j3_virtuemart_custom_plg_customsforall_values', $object)) {
                                $valueId = $db->insertid();
                            }
                        }
                        //
                        if ($valueId) {
                            foreach($productIds as $productId) {
                                $object = new stdClass();
                                $object->virtuemart_product_id = $productId;
                                $object->virtuemart_custom_id = $attributeId;
                                $object->customfield_value = 'customfieldsforall';
                                $object->customfield_params = 'display_type="button"|data_type=""|is_required="0"|is_price_variant="0"|display_price="0"|';
                                //var_dump($object);
                                if ($db->insertObject('j3_virtuemart_product_customfields', $object)) {
                                    $fieldId = $db->insertid();
                                    //
                                    $object = new stdClass();
                                    $object->customsforall_value_id = $valueId;
                                    $object->virtuemart_product_id = $productId;
                                    $object->customfield_id = $fieldId;
                                    //var_dump($object);
                                    if ($db->insertObject('j3_virtuemart_product_custom_plg_customsforall', $object)) {
                                        $rowId = $db->insertid();
                                    }
                                }
                            }   
                        }
                    }
                }
                //var_dump($attributeName, $values);
                var_dump($attributeName);
                //break;
            }
            //var_dump($attributes);
        }
        die();
    }
}