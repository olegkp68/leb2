<?php
/**
 * Users model for Spambotcheck
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

/**
 * Users Model
 *
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since        Joomla 1.6 
 */
class SpambotcheckModelUsers extends JModelList
{
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'ip', 'a.ip',
				'hits', 'a.hits',
				'suspicious', 'a.suspicious',
				'trust', 'a.trust',
				'note', 'a.note',
				'name', 'b.name',
				'username', 'b.username',
				'email', 'b.email',
				'registerdate', 'b.registerDate',
				'block', 'b.block',
				'activation', 'b.activation',
				'groupname', 'ug.title',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$trust = $this->getUserStateFromRequest($this->context.'.filter.trust', 'filter_trust', '');
		$this->setState('filter.trust', $trust);

		$suspicious = $this->getUserStateFromRequest($this->context.'.filter.suspicious', 'filter_suspicious', '');
		$this->setState('filter.suspicious', $suspicious);
		
		$block = $this->getUserStateFromRequest($this->context.'.filter.block', 'filter_block', '');
		$this->setState('filter.block', $block);
		
		$activation = $this->getUserStateFromRequest($this->context.'.filter.activation', 'filter_activation', '');
		$this->setState('filter.activation', $activation);
		
		$range = $this->getUserStateFromRequest($this->context.'.filter.range', 'filter_range');
		$this->setState('filter.range', $range);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.trust');
		$id	.= ':'.$this->getState('filter.suspicious');
		$id	.= ':'.$this->getState('filter.block');
		$id	.= ':'.$this->getState('filter.activation');
		$id .= ':'.$this->getState('filter.range');

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
				'a.*, b.id as bid, b.name as name, b.email as email, b.username as username, b.registerDate as registerdate, b.block as block, b.activation as activation, ' .
				'm.user_id as muser_id, m.group_id as mgroup_id, ug.id as ugid, ug.title as groupname'
			)
		);
		$query->from('#__user_spambotcheck AS a');
		$query->join('LEFT', '#__users AS b ON b.id=a.user_id');
		$query->join('LEFT', '#__user_usergroup_map AS m ON m.user_id=b.id');
		$query->join('LEFT', '#__usergroups AS ug ON ug.id=m.group_id');
		$query->group('a.id,a.user_id,a.ip,a.hits,a.suspicious,a.trust,a.note,b.name,b.username,b.email,b.block,b.registerDate,b.activation');

		// Filter by trust state
		$trust = $this->getState('filter.trust');
		if (is_numeric($trust)) {
			$query->where('a.trust = ' . (int) $trust);
		}
		
		// Filter by suspicious state
		$suspicious = $this->getState('filter.suspicious');
		if (is_numeric($suspicious)) {
			$query->where('a.suspicious = ' . (int) $suspicious);
		}
		
		// Filter by block state
		$block = $this->getState('filter.block');
		if (is_numeric($block)) {
			$query->where('b.block = ' . (int) $block);
		}

		// If the model is set to check the activated state, add to the query.
		$active = $this->getState('filter.activation');
		if (is_numeric($active)) {
			if ($active == '0') {
				// might be '' or '0'
				$query->where($query->length('b.activation').' != 32');
			}
			elseif ($active == '1') {
				$query->where($query->length('b.activation').' = 32');
			}
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%'.$db->escape($search, true).'%');
			$query->where('('. $db->qn('a.ip') . ' LIKE '.$search.' OR ' . $db->qn('b.username') .' LIKE '.$search.' OR ' . $db->qn('b.email') . ' LIKE '.$search.' OR ' . $db->qn('b.name') .' LIKE '.$search.')');
		}

		// Apply the range filter.
		$range = $this->getState('filter.range');
		if ($range != '' && $range != '*') {
			jimport('joomla.utilities.date');

			// Get UTC for now.
			$dNow = new JDate;
			$dStart = clone $dNow;

			switch ($range) {
				case 'past_week':
					$dStart->modify('-7 day');
					break;

				case 'past_1month':
					$dStart->modify('-1 month');
					break;

				case 'past_3month':
					$dStart->modify('-3 month');
					break;

				case 'past_6month':
					$dStart->modify('-6 month');
					break;

				case 'post_year':
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Ranges that need to align with local 'days' need special treatment.
					$offset	= \JFactory::getApplication()->get('offset');

					// Reset the start time to be the beginning of today, local time.
					$dStart	= new JDate('now', $offset);
					$dStart->setTime(0, 0, 0);

					// Now change the timezone back to UTC.
					$tz = new DateTimeZone('GMT');
					$dStart->setTimezone($tz);
					break;
			}

			if ($range == 'post_year') {
				$query->where(
					$db->qn('b.registerDate') .' < '.$db->quote($dStart->format('Y-m-d H:i:s'))
				);
			}
			else {
				$query->where(
					$db->qn('b.registerDate') .'  >= '.$db->quote($dStart->format('Y-m-d H:i:s')).
					' AND ' .$db->qn('b.registerDate') . ' <='.$db->quote($dNow->format('Y-m-d H:i:s'))
				);
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
	
	public function trust(&$pks, $value = 0) {
        $table = $this->getTable();
        $pks = (array) $pks;

		 // Attempt to change the state of the records.
        if (!$table->trust($pks, $value)) {
            $this->setError($table->getError());
            return false;
        }

        // Clear the component's cache
        $this->cleanCache();
 
        return true;
	}
	
	public function delete(&$pks) {
		$pks = (array) $pks;
		$table = $this->getTable();
		$usertable = $this->getTable('User', 'JTable');
		$user	= \JFactory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin	= $user->authorise('core.admin');


		if (in_array($user->id, $pks)) {
			$this->setError(\JText::_('COM_USERS_USERS_ERROR_CANNOT_DELETE_SELF'));
			return false;
		}
		

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Access checks.
				$allow = $user->authorise('core.delete', 'com_users');
				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin', 'com_users')) ? false : $allow;
			
				$userIp = plgSpambotCheckHelpers::getTableFieldValue('#__user_spambotcheck', 'ip', 'user_id', $pk);
				if ($allow) {
					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}
				}
				else {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, \JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
					break;
				}
			}
			else {
				$this->setError($table->getError());
				return false;
			}
			
			//clean up user_spambotcheck fields
			plgSpambotCheckHelpers::cleanUserSpambotTable($userIp, $pk);
			
			if ($usertable->load($pk)) {
				if (!$usertable->delete($pk)) {
					$this->setError($usertable->getError());
					return false;
				}
			}
			else {
				$this->setError($usertable->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	public function getItems() {
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (empty($this->cache[$store])) {
			$items = parent::getItems();

			// Bail out on an error or empty list.
			if (empty($items)) {
				$this->cache[$store] = $items;

				return $items;
			}

			// Joining the groups with the main query is a performance hog.
			// Find the information only on the result set.

			// First pass: get list of the user id's and reset the counts.
			$userIds = array();
			foreach ($items as $item) {
				$userIds[] = (int) $item->user_id;
				$item->group_count = 0;
				$item->group_names = '';
			}

			// Get the counts from the database only for the users in the list.
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			// Join over the group mapping table.
			$query->select(array($db->qn('map.user_id'), 'COUNT('. $db->qn('map.group_id'). ') AS '. $db->qn('group_count')))
				->from($db->qn('#__user_usergroup_map') . ' AS '. $db->qn('map'))
				->where($db->qn('map.user_id') .' IN ('.implode(',', $userIds).')')
				->group($db->qn('map.user_id'))
				// Join over the user groups table.
				->join('LEFT', $db->qn('#__usergroups') .' AS ' . $db->qn('g2') .  'ON '. $db->qn('g2.id') . ' = '. $db->qn('map.group_id'));
			$db->setQuery($query);

			// Load the counts into an array indexed on the user id field.
			try {
				$userGroups = $db->loadObjectList('user_id');
			}
			catch (RuntimeException $e) {
				return false;
			}

			// Second pass: collect the group counts into the master items array.
			foreach ($items as &$item) {
				if (isset($userGroups[$item->user_id])) {
					$item->group_count = $userGroups[$item->user_id]->group_count;
					//Group_concat in other databases is not supported
					$item->group_names = $this->getUserDisplayedGroups($item->user_id);
				}
			}

			// Add the items to the internal cache.
			$this->cache[$store] = $items;
		}

		return $this->cache[$store];
	}
	
	private function getUserDisplayedGroups($user_id) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('title'))
			->from($db->qn('#__usergroups') . ' AS ' . $db->qn('ug'))
			->join('LEFT', $db->qn('#__user_usergroup_map') . ' AS ' . $db->qn('map') . ' ON' . $db->qn('ug.id') . ' = ' . $db->qn('map.group_id'))
			->where($db->qn('map.user_id') . ' = ' .$user_id);

		$db->setQuery($query);
		try {
			$result = $db->loadColumn();
			return implode("\n", $result);
		}
		catch (RuntimeException $e) {
			return '';
		}
	}
}