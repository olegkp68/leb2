<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
*/

// No direct access
defined('_JEXEC') or die;

/**
 * Spambotcheck component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_spambotcheck
 * @since		1.6
 */
class SpambotcheckHelper
{
	public static $extension = 'com_spambotcheck';
	
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			\JText::_('COM_SPAMBOTCHECK_SUBMENU_USERS'),
			'index.php?option=com_spambotcheck&view=users',
			$vName == 'users'
		);
		JHtmlSidebar::addEntry(
			\JText::_('COM_SPAMBOTCHECK_SUBMENU_LOGS'),
			'index.php?option=com_spambotcheck&view=logs',
			$vName == 'logs'
		);
		JHtmlSidebar::addEntry(
			\JText::_('COM_SPAMBOTCHECK_SUBMENU_HELP'),
			'index.php?option=com_spambotcheck&view=help',
			$vName == 'help');
	}
	
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @since   1.6
	 */
	public static function getActions()
	{
		$user = \JFactory::getUser();
		$result	= new JObject;
		$actions = array('core.admin', 'core.manage');

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, 'com_spambotcheck'));
		}


		return $result;
	}
}
