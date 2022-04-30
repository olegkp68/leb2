<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2018 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class CustomFieldsForAllLanguageInstaller
{
    /**
     *
     * @var Language
     */
    protected $language;

    /**
     *
     * @param string $lang
     */
    public function __construct($lang)
    {
        $this->language = $lang;
    }

    /**
     * Creates the language database table
     *
     * @return boolean
     */
    public function install()
    {
        $db = JFactory::getDbo();
        $query = '
            CREATE TABLE IF NOT EXISTS `#__virtuemart_custom_plg_customsforall_values_'.$this->language->db_code.'`
                (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `customsforall_value_id` int(11) NOT NULL,
                `customsforall_value_name` varchar(255) NOT NULL COMMENT \'is the value of a custom field\',
                `customsforall_value_label` varchar(255) COMMENT \'Used to add also labels (Primarily to colors)\',
                PRIMARY KEY (`id`),
                 CONSTRAINT customsforall_value_id_'.$this->language->db_code.' FOREIGN KEY (`customsforall_value_id`)
                 REFERENCES #__virtuemart_custom_plg_customsforall_values(`customsforall_value_id`)
                ON DELETE CASCADE
                ) ENGINE=INNODB DEFAULT CHARSET=utf8;';
        try{
            $db->setQuery($query);
            $db->query();
        }
        catch (Exception $e) {
            throw $e;
        }
        return true;
    }
}
