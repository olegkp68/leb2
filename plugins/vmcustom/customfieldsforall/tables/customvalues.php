<?php
/**
 * @version		$Id: customvalues.php 2013-07-2 20:32 sakis Terz $
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class TableCustomvalues extends Table   
{
    /**
     * The column names based on which a row is considered unique
     *
     * @var array
     */
    protected $uniqueColumnNames = ['customsforall_value_name'];
    
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct(&$_db)
	{
		parent::__construct('#__virtuemart_custom_plg_customsforall_values', 'customsforall_value_id', $_db);

	}

    public function check()
    {
        $k = $this->_tbl_key; // the primary key
        $this->virtuemart_custom_id = (int) $this->virtuemart_custom_id;
        $this->ordering = (int) trim($this->ordering);
        
        if (empty($this->ordering)) {
            // get the max ordering and use it for that record if its new
            if (! $this->$k) {
                $db = $this->getDbo();
                $table_name = $db->quoteName($this->getTableName());
                $q = "SELECT MAX(ordering) FROM $table_name WHERE virtuemart_custom_id=$this->virtuemart_custom_id";
                $db->setQuery($q);
                $ordering = (int) $db->loadResult();
                
                if ($ordering) {
                    $this->ordering = $ordering += 1;
                }
            }
        }
        return true;
    }
    
    /**
     * The column names based on which a row is considered unique
     *
     * @return array
     */
    public function getUniqueColumnNames()
    {
        return $this->uniqueColumnNames;
    }
}