<?php
/**
 * @package         Add to Menu
 * @version         6.1.6PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2018 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

/**
 * Module that adds menu items
 */

$user = JFactory::getUser();
if ( ! $user->authorise('core.create', 'com_menus'))
{
	return;
}

// return if Regular Labs Library plugin is not installed
jimport('joomla.filesystem.file');
if (
	! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
	return;
}

jimport('joomla.filesystem.folder');
$option = JFactory::getApplication()->input->get('option');
$folder = JPATH_ADMINISTRATOR . '/components/' . $option . '/addtomenu';
if ( ! JFolder::exists($folder))
{
	$folder = JPATH_ADMINISTRATOR . '/modules/mod_addtomenu/components/' . $option;
}

if ( ! JFolder::exists($folder))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$helper = new ModAddToMenu($params);
$helper->render();
