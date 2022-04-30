<?php
/**
* @package Logged-in-Users (mod_loggedinusers)
* @version 2.0.1
* @copyright Copyright (C) 2011-2014 Carsten Engel. All rights reserved.
* @license GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html 
* @author http://www.pages-and-items.com
*/// No direct access.
defined('_JEXEC') or die;

$version = new JVersion;

if($params->get('show_loggedin_frontend', 1)){	
	echo '<span class="loggedin-users"><span class="badge">'.$frontend.'</span> ';	
	if($version->RELEASE >= '3.0'){
		echo JText::plural('MOD_STATUS_USERS', $frontend);
	}else{
		echo str_replace('%s ', '', JText::_('MOD_STATUS_USERS'));
	}
	echo '</span>&nbsp;';
}

if($params->get('show_loggedin_backend', 1)){
	echo '<span class="backloggedin-users"><span class="badge">'.$backend.'</span> ';
	if($version->RELEASE >= '3.0'){
		echo JText::plural('MOD_STATUS_BACKEND_USERS', $backend);
	}else{
		echo str_replace('%s ', '', JText::_('MOD_STATUS_BACKEND_USERS'));
	}
	echo '</span>&nbsp;';
}

?>