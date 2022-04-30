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
# Last modified: 21 Mar 2019, 01:46:37
========================================================= */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

abstract class LoginasuserHelper
{
	/**
	 * @var    JObject  A cache for the available actions.
	 * @since  1.6
	 */
	protected static $actions;

	public static function addSubmenu($submenu) 
	{
		//  get the ID of plugin
		$db = JFactory::getDBO();
		$query = "SELECT extension_id "
		."FROM #__extensions "
		."WHERE type='plugin' AND element='loginasuser' AND folder='system' "
		;
		$db->setQuery($query);
		$db->execute();
		$extension_id = (int)$db->loadResult();

		// Get Joomla! version
		$jversion = new JVersion;
		$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
		$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

		if (version_compare( $mini_version, "2.5", "<=")) :

			// J25X
			$vName = JFactory::getApplication()->input->get('view', 'loginasuser', 'CMD');
			echo JSubMenuHelper::addEntry( JText::_('Users List (Login as any User)'), 'index.php?option=com_loginasuser&view=loginasuser', $vName == 'loginasuser' );
	
			$vName = JFactory::getApplication()->input->get('view', 'plugin_settings', 'CMD');
			echo JSubMenuHelper::addEntry( JText::_('Settings'), 'index.php?option=com_plugins&task=plugin.edit&extension_id='.$extension_id, $vName == 'plugin_settings' );

			$vName = JFactory::getApplication()->input->get('view', 'overv', 'CMD');
			echo JSubMenuHelper::addEntry( JText::_('About Login as User'), 'index.php?option=com_loginasuser&view=overv', $vName == 'overv' );
	
			$vName = JFactory::getApplication()->input->get('view', 'about', 'CMD');
			echo JSubMenuHelper::addEntry( JText::_('About Web357'), 'index.php?option=com_loginasuser&view=about', $vName == 'about' );
			
		else:

			// J3X
			JHtmlSidebar::addEntry(JText::_('Users List (Login as any User)'), 'index.php?option=com_loginasuser&view=loginasuser', $submenu == 'loginasuser');
			JHtmlSidebar::addEntry(JText::_('Settings'), 'index.php?option=com_plugins&task=plugin.edit&extension_id='.$extension_id, $submenu == 'plugin_settings');
			JHtmlSidebar::addEntry(JText::_('About Login as User'), 'index.php?option=com_loginasuser&view=overv', $submenu == 'overv');
			JHtmlSidebar::addEntry(JText::_('About Web357'), 'index.php?option=com_loginasuser&view=about', $submenu == 'about');
			
		endif; 
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Get Joomla! version
		$jversion = new JVersion;
		$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
		$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

		if (!version_compare( $mini_version, "2.5", "<=")) :
		// joomla 3.x
			// Log usage of deprecated function
			JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');
	
			// Get list of actions
			$result = JHelperContent::getActions('com_users');
	
			return $result;
		else:
		// joomla 2.5.x
			if (empty(self::$actions))
			{
				$user = JFactory::getUser();
				self::$actions = new JObject;
	
				$actions = JAccess::getActions('com_users');
	
				foreach ($actions as $action)
				{
					self::$actions->set($action->name, $user->authorise($action->name, 'com_users'));
				}
			}
	
			return self::$actions;
		endif;
	}

	/**
	 * Get a list of filter options for the blocked state of a user.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JENABLED'));
		$options[] = JHtml::_('select.option', '1', JText::_('JDISABLED'));

		return $options;
	}

	/**
	 * Get a list of filter options for the activated state of a user.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getActiveOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('COM_USERS_ACTIVATED'));
		$options[] = JHtml::_('select.option', '1', JText::_('COM_USERS_UNACTIVATED'));

		return $options;
	}

	/**
	 * Get a list of the user groups for filtering.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getGroups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value')
			->select('a.title AS text')
			->select('COUNT(DISTINCT b.id) AS level')
			->from('#__usergroups as a')
			->join('LEFT', '#__usergroups  AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id, a.title, a.lft, a.rgt')
			->order('a.lft ASC');
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseNotice(500, $e->getMessage());

			return null;
		}

		foreach ($options as &$option)
		{
			$option->text = str_repeat('- ', $option->level) . $option->text;
		}

		return $options;
	}

	/**
	 * Creates a list of range options used in filter select list
	 * used in com_users on users view
	 *
	 * @return  array
	 *
	 * @since   2.5
	 */
	public static function getRangeOptions()
	{
		$options = array(
			JHtml::_('select.option', 'today', JText::_('COM_USERS_OPTION_RANGE_TODAY')),
			JHtml::_('select.option', 'past_week', JText::_('COM_USERS_OPTION_RANGE_PAST_WEEK')),
			JHtml::_('select.option', 'past_1month', JText::_('COM_USERS_OPTION_RANGE_PAST_1MONTH')),
			JHtml::_('select.option', 'past_3month', JText::_('COM_USERS_OPTION_RANGE_PAST_3MONTH')),
			JHtml::_('select.option', 'past_6month', JText::_('COM_USERS_OPTION_RANGE_PAST_6MONTH')),
			JHtml::_('select.option', 'past_year', JText::_('COM_USERS_OPTION_RANGE_PAST_YEAR')),
			JHtml::_('select.option', 'post_year', JText::_('COM_USERS_OPTION_RANGE_POST_YEAR')),
		);

		return $options;
	}

	/**
	 * Creates a list of two factor authentication methods used in com_users
	 * on user view
	 *
	 * @return  array
	 *
	 * @since   3.2.0
	 */
	public static function getTwoFactorMethods()
	{
		// Load the Joomla! RAD layer
		if (!defined('FOF_INCLUDED'))
		{
			include_once JPATH_LIBRARIES . '/fof/include.php';
		}

		FOFPlatform::getInstance()->importPlugin('twofactorauth');
		$identities = FOFPlatform::getInstance()->runPlugins('onUserTwofactorIdentify', array());

		$options = array(
			JHtml::_('select.option', 'none', JText::_('JGLOBAL_OTPMETHOD_NONE'), 'value', 'text'),
		);

		if (!empty($identities))
		{
			foreach ($identities as $identity)
			{
				if (!is_object($identity))
				{
					continue;
				}

				$options[] = JHtml::_('select.option', $identity->method, $identity->title, 'value', 'text');
			}
		}

		return $options;
	}

}