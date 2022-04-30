<?php
/* ======================================================
# Login as User for Joomla! - v3.3.2
# -------------------------------------------------------
# For Joomla! CMS
# Author: Web357 (Yiannis Christodoulou)
# Copyright (©) 2009-2019 Web357. All rights reserved.
# License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
# Website: https:/www.web357.com/
# Demo: https://demo.web357.com/?item=loginasuser
# Support: support@web357.com
# Last modified: 21 Mar 2019, 01:46:36
========================================================= */

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users master display controller.
 *
 * @since  1.6
 */
class LoginasuserController extends JControllerLegacy
{
	/**
	 * Checks whether a user can see this view.
	 *
	 * @param   string  $view  The view name.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function canView($view)
	{

		// Get Joomla! version
		$jversion = new JVersion;
		$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
		$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

		if (!version_compare( $mini_version, "2.5", "<=")) :
		// joomla 3.x
			$canDo = JHelperContent::getActions('com_users');
		else:
		// joomla 2.5.x
			$canDo	= LoginasuserHelper::getActions();
		endif;

		switch ($view)
		{
			// Special permissions.
			case 'groups':
			case 'group':
			case 'levels':
			case 'level':
				return $canDo->get('core.admin');
				break;

			// Default permissions.
			default:
				return true;
		}
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController	 This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get Joomla! version
		$jversion = new JVersion;
		$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
		$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

		if (!version_compare( $mini_version, "2.5", "<=")) :
		// joomla 3.x
			$view   = $this->input->get('view', 'loginasuser');
			$layout = $this->input->get('layout', 'default');
			$id     = $this->input->getInt('id');
		else:
		// joomla 2.5.x
			// Load the submenu.
			LoginasuserHelper::addSubmenu(JFactory::getApplication()->input->get('view', 'loginasuser', 'CMD'));
		
			$view		= JFactory::getApplication()->input->get('view', 'loginasuser', 'CMD');
			$layout 	= JFactory::getApplication()->input->get('layout', '', 'CMD');
			$id			= JFactory::getApplication()->input->get('id', '', 'INT');
		endif;

		if (!$this->canView($view))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		// Check for edit form.
		if ($view == 'user' && $layout == 'edit' && !$this->checkEditId('com_users.edit.user', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=loginasuser', false));

			return false;
		}
		elseif ($view == 'group' && $layout == 'edit' && !$this->checkEditId('com_users.edit.group', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=groups', false));

			return false;
		}
		elseif ($view == 'level' && $layout == 'edit' && !$this->checkEditId('com_users.edit.level', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=levels', false));

			return false;
		}
		elseif ($view == 'note' && $layout == 'edit' && !$this->checkEditId('com_users.edit.note', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=notes', false));

			return false;
		}

		return parent::display();
	}
}
