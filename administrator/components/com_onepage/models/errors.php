<?php
/**
 * 
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 */


// no direct access
defined('_JEXEC') or die;
jimport( 'joomla.application.component.modellist' );

class JModelErrors extends JModelList {
	    var $_pagination = null;
	    function __construct()
		{
			parent::__construct();
		
		}
		
		function getPagination()
  {
	    JHtml::_('behavior.framework'); 
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart', JRequest::getVar('limitstart', 0)), $this->getState('limit', JRequest::getVar('limit', 0)) );
        }
       
        return $this->_pagination;
  }
		
		
		
		
	protected function populateState($ordering = 'a.created_on', $direction = 'desc')
	{
		$app = JFactory::getApplication();
		$view = JRequest::getCmd('view');
		$limit = (int)$app->getUserStateFromRequest('com_onepage.'.$view.'.limit', 'limit');
		if (empty($limit)) $limit = JRequest::getInt('limit', 25); 
		$this->setState('com_onepage.'.$view.'.limit',$limit);
		$this->setState('limit', $limit); 
		
		$limitstart = (int)$app->getUserStateFromRequest('com_onepage.'.$view.'.limitstart', 'limitstart', JRequest::getInt('limitstart',0,'GET'), 'int');
		
		$this->setState('limitstart', $limitstart); 
		$this->setState('com_onepage.'.$view.'.limitstart',$limitstart);
		
		$this->_limit = $limit; 
		$this->_limitStart = $limitStart;
		
		// List state information.
		parent::populateState($ordering, $direction);

		
	}
	
	function getTotal()
  {
	  
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
			//testing: 
			$db= JFactory::getDBO(); 
			$db->setQuery($query); 
			try {
			
			$db->loadAssoc(); 
			
			
			
			}
			catch (Exception $e) { 
			  $msg = (string)$e; 
			  JFactory::getApplication()->enqueueMessage($msg); 
			}
			
            $this->_total = $this->_getListCount($query);    
			
			
            
        }
		
        
        return $this->_total;
  }
	public function _buildQuery() {
		return $this->getListQuery(); 
	}
	
		
		
	public function getItems()
	{
		$items = parent::getItems();

		

		return $items;
	}
	
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'*'
			)
		);
		$query->from('#__onepage_errorlog AS a');

		
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.created_on');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		
		//echo $query; die(); 
		return $query;
	}

		
}