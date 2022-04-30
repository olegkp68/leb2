<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JControllerExt extends JControllerBase
{	
   function getViewName() 
	{ 
		return 'ext';		
	} 

   function getModelName() 
	{		
		return 'ext';
	}
	
	function installext()
	{
	  $msg = ''; 
	  if ($this->checkPerm()) {
	  $id = JRequest::getVar('ext_id', '-1');
	  $id = (int)$id; 
      if ($id >= 0)
       {
	       $model = $this->getModel('config');
		   
	       $ret = $model->installext($id, '', $msg);  
		   $msg = $ret; 
	   }	   
	  }
	  $link = 'index.php?option=com_onepage'; 
	 
	  $this->setRedirect($link, $msg);
	}
	
	 function checkPerm() {
	   $user = JFactory::getUser(); 
	   
      $isroot = $user->authorise('core.admin');	
	  
	  if (!$isroot) 
	  {
		if (!empty($_FILES))
		foreach ($_FILES as $f) {
		  if (!empty($f['tmp_name'])) {
		    if (file_exists($f['tmp_name'])) {
			  unlink($f['tmp_name']); 
			}
		  }
		}
	    $msg = JText::_('COM_ONEPAGE_PERMISSION_DENIED'); 
		JFactory::getApplication()->enqueueMessage($msg); 
		return false; 
	  }
	  
	  $iss = JFactory::getApplication()->isSite(); 
	  if (!empty($iss)) return false; 
	  
	  return true; 
   }
}