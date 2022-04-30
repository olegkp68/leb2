<?php
/**
 * Form component for Joomla
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

// Access check: is this user allowed to access the backend of this component?
if (!JFactory::getUser()->authorise('core.manage', 'com_spambotcheck')) {
	throw new JAccessExceptionNotallowed(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

try {
	JHtml::_('behavior.tabstate');
}
catch (Exception $e) {
	// J4 error handling and message to the user: need to update spambotcheck version
	$text = $e->getMessage();
	$code = $e->getCode();
	$app = \JFactory::getApplication();
	// enqueue the redirect message
	$app->enqueueMessage(\JText::_('COM_SPAMBOTCHECK_J4_AFTER_UPDATE_MESSAGE'), 'error');
	// execute the redirect to extensions update installer
	$app->redirect("index.php?option=com_installer&view=update");
}

// Register helper class
JLoader::register('SpambotcheckHelper', dirname(__FILE__) . '/helpers/spambotcheck.php');
JLoader::register('JHTMLSpambotcheck', dirname(__FILE__) . '/helpers/html/spambotcheck.php');
JLoader::register('plgSpambotCheckHelpers', JPATH_SITE.'/plugins/user/spambotcheck/SpambotCheck/SpambotCheckHelpers.php');
JLoader::register('UsersHelper', JPATH_ADMINISTRATOR.'/components/com_users/helpers/users.php');
JLoader::register('UsersModelUser', JPATH_ADMINISTRATOR.'/components/com_users/models/user.php');
JLoader::register('JHtmlUsers', JPATH_ADMINISTRATOR.'/components/com_users/helpers/html/users.php');

$lang = \JFactory::getLanguage();
$extension = 'com_users';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $lang->getTag();
$reload = true;
$lang->load($extension, $base_dir, null, $language_tag, $reload);


$controller = JControllerLegacy::getInstance('Spambotcheck',  array('default_view' =>'Users'));
$controller->execute(\JFactory::getApplication()->input->get('task'));
$controller->redirect();