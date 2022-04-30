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
# Last modified: 21 Mar 2019, 01:46:39
========================================================= */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemLoginasuserInstallerScript extends PlgSystemLoginasuserInstallerScriptHelper
{
	public $name           	= 'Login as User';
	public $alias          	= 'loginasuser';
	public $extension_type 	= 'plugin';
	public $plugin_folder   = 'system';

	// Find the default template name in joomla! backend
	function getDefaultAdminTemplate()
	{
		// connect to db
		$db = JFactory::getDBO();
		
		// Find the default template name in joomla! backend
		$query = 'SELECT template FROM #__template_styles WHERE client_id=1 AND home=1';
		$db->setQuery($query);
		$default_admin_template = $db->loadResult();
		
		return $default_admin_template;
	}
	
	function postflight($type, $parent) 
	{
		// connect to db
		$db = JFactory::getDBO();
			
		// Enable Plugin and Set Ordering #1
		$query = "UPDATE #__extensions SET enabled=1, ordering=1 WHERE element='loginasuser' AND type='plugin' AND folder='system'";
		$db->setQuery($query);
		$db->execute();
		
		// importjoomla file system
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );
		
		// create "html" folder
		$html_folder = JPATH_ADMINISTRATOR."/templates/".$this->getDefaultAdminTemplate()."/html/";
		if (!JFolder::create($html_folder))
		{
			throw new Exception(JText::_('Error creating the folder: ' . $html_folder), 500);
		}

		// create "com_users" folder
		$com_users_folder = JPATH_ADMINISTRATOR."/templates/".$this->getDefaultAdminTemplate()."/html/com_users/";
		if (!JFolder::create($com_users_folder))
		{
			throw new Exception(JText::_('Error creating the folder: ' . $com_users_folder), 500);
		}

		// create "users" folder
		$users_folder = JPATH_ADMINISTRATOR."/templates/".$this->getDefaultAdminTemplate()."/html/com_users/users/";
		if (!JFolder::create($users_folder))
		{
			throw new Exception(JText::_('Error creating the folder: ' . $users_folder), 500);
		}
		
		// BEGIN: copy files to the current admin template html folder (default is isis)
		$src = JPATH_PLUGINS."/system/loginasuser/com_users_helper_files/joomla_com_users/default.php";
		$dest = JPATH_ADMINISTRATOR."/templates/".$this->getDefaultAdminTemplate()."/html/com_users/users/default.php";
		if (JFile::exists($src))
		{
			JFile::copy($src, $dest);
		}
		// END: copy files to the current admin template html folder (default is isis)
	}
	
	function uninstall($parent) 
	{
		jimport( 'joomla.filesystem.file' );
		$dest = JPATH_ADMINISTRATOR."/templates/".$this->getDefaultAdminTemplate()."/html/com_users/users/default.php";
		JFile::delete($dest);
	}
}