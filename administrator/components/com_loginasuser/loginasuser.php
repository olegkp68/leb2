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

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// BEGIN: Check if Web357 Framework plugin exists and is enabled
jimport('joomla.plugin.helper');
if(!JPluginHelper::isEnabled('system', 'web357framework')):
	$web357framework_required_msg = JText::_('<p>The <strong>"Web357 Framework"</strong> is required for this extension and must be active. Please, download and install it from <a href="http://downloads.web357.com/?item=web357framework&type=free">here</a>. It\'s FREE!</p>');
	JFactory::getApplication()->enqueueMessage($web357framework_required_msg, 'error');
	return false;
endif;
// END: Check if Web357 Framework plugin exists and is enabled

// BEGIN: Check if the plugin exists
if(!JPluginHelper::isEnabled('system', 'loginasuser')):
	$plugin_required_msg = JText::_('<p>The <strong>"Login as User"</strong> plugin is required for this extension and must be active. Check if is unpublished. If does not exists in the Plugins list, download and install it from <a href="https://www.web357.com/downloads/" target="_blank">web357.com/downloads</a>.</p>');
	JFactory::getApplication()->enqueueMessage($plugin_required_msg, 'error');
	return false;
endif;
// END: Check if the plugin exists

// Call the Web357 Framework Helper Class
require_once(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'web357framework'.DIRECTORY_SEPARATOR.'web357framework.class.php');
$w357frmwrk = new Web357FrameworkHelperClass;

// API Key Checker
$w357frmwrk->apikeyChecker();

// ACL
if (!JFactory::getUser()->authorise('core.manage', 'com_loginasuser')):
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
endif;

// BEGIN: Loading com_users language
$lang = JFactory::getLanguage();
$current_lang_tag = $lang->getTag();
$lang = JFactory::getLanguage();
$extension = 'com_users';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = (!empty($current_lang_tag)) ? $current_lang_tag : 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
// END: Loading com_users language

// Load cms libraries
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');

// Load joomla libraries without overwrite
JLoader::registerPrefix('J', JPATH_PLATFORM . '/joomla',false);

// Register helper class
JLoader::register('LoginasuserHelper', dirname(__FILE__) . '/helpers/helper.php');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Estore
$controller = JControllerLegacy::getInstance('Loginasuser');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();