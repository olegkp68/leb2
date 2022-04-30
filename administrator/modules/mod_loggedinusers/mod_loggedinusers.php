<?php
/**
* @package Logged-in-Users (mod_loggedinusers)
* @version 2.0.1
* @copyright Copyright (C) 2011-2014 Carsten Engel. All rights reserved.
* @license GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html 
* @author http://www.pages-and-items.com
*/// No direct access.
defined('_JEXEC') or die;

$database = JFactory::getDBO();

$lang = JFactory::getLanguage();
$lang->load('mod_status', JPATH_ADMINISTRATOR, null, false);

$activity_time = $params->get('activity_time', '30');
$activity_time = time()-$activity_time*60;

if($params->get('show_loggedin_frontend', 1)){
	$database->setQuery("SELECT session_id FROM #__session WHERE guest='0' AND client_id='0' AND time>'$activity_time' ");
	$frontend = count($database->loadResult());
}

if($params->get('show_loggedin_backend', 1)){
	$database->setQuery("SELECT session_id FROM #__session WHERE guest='0' AND client_id='1' AND time>'$activity_time' ");
	$backend = count($database->loadResult());
}

require JModuleHelper::getLayoutPath('mod_loggedinusers', 'default');

?>