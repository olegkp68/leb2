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

// import Joomla view library
jimport('joomla.application.component.view');

class LoginasuserViewAbout extends JViewLegacy
{
	function display($tpl = null) 
	{
		$form	= $this->get('Form');
		
		// Check for model errors.
		if ($errors = $this->get('Errors')) {
			throw new Exception(implode('<br />', $errors), 500);
		}

		// Get Joomla! version
		$jversion = new JVersion;
		$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
		$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

		if (version_compare($mini_version, "2.5", "<=")):
			// j25x
			// Show sidebar
			$this->sidebar = "";
		else:
			// j3X
			// Include helper submenu
			LoginasuserHelper::addSubmenu('about');
	
			// Show sidebar
			$this->sidebar = JHtmlSidebar::render();
		endif;

		// mapping variables
		$this->form = $form;
		
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	protected function addToolBar()
	{
		// Build title
		$title = JText::_('About Web357');
		
		// Set document title
		JFactory::getDocument()->setTitle($title);
		
		// Set ToolBar title
		JToolbarHelper::title($title, 'loginasuser icon-user');
			
	}
}