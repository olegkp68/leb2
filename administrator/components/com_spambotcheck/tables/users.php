<?php
/**
 * USERS table class for Spambotcheck
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;


/**
 * Users Table class
 *
 * @package    com_Spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @subpackage Components
 */
class TableUsers extends JTable
{
	public function __construct(&$db) {
		parent::__construct('#__user_spambotcheck', 'user_id', $db);
	}
	
	public function trust($pks = null, $state = 0) {
        // Sanitize input.
        ArrayHelper::toInteger($pks);
        $state = (int) $state;
		
		// If there are no primary keys nothing to do.
		if (empty($pks)) {
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			return false;

		}

		if (!is_array($pks)) {
			$pks = array($pks);
		}

		foreach ($pks as $key => $pk) {
			if (!is_array($pk)) {
				$pks[$key] = array($this->_tbl_key => $pk);
			}
		}

		foreach ($pks as $pk) {
			// Get the JDatabaseQuery object
			$query = $this->_db->getQuery(true);
			// Update the publishing state for rows with the given primary keys.
			$query->update($this->_db->quoteName($this->_tbl));
			$query->set($this->_db->quoteName('trust') . ' = ' . (int) $state);
			if ((int) $state == 1) {
				$query->set($this->_db->quoteName('suspicious') . ' = ' . (int) 1);
			}
			$this->appendPrimaryKeys($query, $pk);
			$this->_db->setQuery($query);
			$this->_db->execute();
			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		$this->setError('');
 
        return true;
	
	}

}
?>
