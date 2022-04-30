<?php
/**
 * Logs model for Spambotcheck
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Logs Model
 *
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since        Joomla 1.6 
 */
class SpambotcheckModelLogs extends JModelList
{
	function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
			'id', 'a.id',
			'action', 'a.action',
			'email', 'a.email',
			'ip', 'a.ip',
			'username', 'a.username',
			'engine', 'a.engine',
			'attempt_date', 'a.attempt_date',
			);
		}
		
		parent::__construct($config);
	}
	 
	protected function populateState($ordering = null, $direction = null) {
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		parent::populateState('a.id', 'asc');
	}

	protected function getStoreId($id = '') {
		$id	.= ':'.$this->getState('filter.search');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
				
			)
		);
		$tn = "#__spambot_attempts";
		$query->from($db->quoteName($tn) . ' AS a');

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
	
	public function delete(&$pks) {
		// Initialise variables.
		$pks = (array) $pks;
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if (!$table->delete($pk)) {
					$this->setError($table->getError());
					return false;
				}
			}
			else {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
	
