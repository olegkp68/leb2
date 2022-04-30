<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* 
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JControllerHikaconfig extends JControllerBase
{	

   function __construct() {
	    jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );
		
		if (!defined('AUTOLOADREGISTERED')) {
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
			OPCPlatform::hikaAutoload(); 
		}
		parent::__construct(); 
   }
   function getViewName() 
	{ 
		return 'hikaconfig';		
	} 

   function getModelName() 
	{		
		return 'hikaconfig';
	}
	
	function installopcext()
	{
	  $link = 'index.php?option=com_onepage&view=hikaconfig'; 
	  $msg = 'Not implemented'; 
	  $this->setRedirect($link, $msg);
	
	 
	}
	function ignoreMsg() {
		$hash = JRequest::getVar('ignhash', ''); 
		if (!empty($hash)) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
			$val = true; 
			OPCconfig::store('opc_ignored_messages', $hash, 0, $val); 
			
			
			
		}
		$link = 'index.php?option=com_onepage&view=hikaconfig'; 
		$msg = JText::_('COM_ONEPAGE_OK'); 
	    $this->setRedirect($link, $msg);
		
	}
	
	function installlangfiles() {
		   $model = $this->getModel('hikaconfig');
		   $msg = ''; 
	       $ret = $model->installVmLanguageFiles();  
		   $msg .= $ret; 
		   $link = 'index.php?option=com_onepage&view=hikaconfig'; 
		   if (empty($msg)) { $msg = JText::_('COM_ONEPAGE_OK'); }
	       $this->setRedirect($link, $msg);
	  }
	
	function fix_db_charset() {
		$db = JFactory::getDBO(); 
		$config = JFactory::getConfig();
		$dbname = $config->get('db');
		$q = 'ALTER DATABASE `'.$db->escape($dbname).'` CHARACTER SET utf8 COLLATE utf8_unicode_ci'; 
		$db->setQuery($q); 
		$db->execute(); 
		
	   $link = 'index.php?option=com_onepage&view=hikaconfig'; 
	   
	   
	   
		 $msg = JText::_('COM_ONEPAGE_OK'); 
	   

	   $this->setRedirect($link, $msg);
		
	}
	
   
	function installext()
	{
	  $id = JRequest::getVar('ext_id', '-1');
	  $id = (int)$id; 
      if ($id >= 0)
       {
	       $model = $this->getModel('config');
		   $msg = ''; 
	       $ret = $model->installext($id, '', $msg);  
		   $msg .= $ret; 
	   }	   
	   
	  $link = 'index.php?option=com_onepage&view=hikaconfig'; 
	 
	  $this->setRedirect($link, $msg);
	}
	
    function changelang()
	{
  
    JRequest::setVar( 'view', '[ hikaconfig ]' );
    JRequest::setVar( 'layout', 'default'  );  
    
    $model = $this->getModel('hikaconfig');
    $reply = $model->store();
    if ($reply===true) {
    $msg = JText::_('COM_ONEPAGE_CONFIGURATION_SAVED');
    } else { $msg = JText::_('COM_ONEPAGE_ERROR_SAVING_CONFIGURATION'); 
    }
    $link = 'index.php?option=com_onepage&view=hikaconfig';
	
	$opc_lang = JRequest::getVar('opclang', ''); 
	$link .= '&opclang='.$opc_lang; 
    $this->setRedirect($link, $msg); 	      
	
	}
	
	
	function langcopy()
	{
	   
	   $link = 'index.php?option=com_onepage&view=hikaconfig';
	   $msg = ''; 
       $this->setRedirect($link, $msg);
	  
	}
		
	function langedit()
	{
	  $from = JRequest::getVar('tr_fromlang', 'en-GB'); 
	  $to = JRequest::getVar('tr_tolang', 'en-GB'); 
	   $tr_type = JRequest::getVar('tr_type', 'site'); 
	  $xt = JRequest::getVar('tr_ext_'.$tr_type, 'com_onepage.ini'); 
	  $tr_type = JRequest::getVar('tr_type', 'site'); 
	  
	  $url = 'index.php?option=com_onepage&view=edit&tr_fromlang='.$from.'&tr_tolang='.$to.'&tr_ext='.$xt.'&tr_type='.$tr_type; 
	  
	  $this->setRedirect($url);
	  
	}
	
    
    
   

   function checkPerm() {
	   
      $isroot = $user->authorise('core.admin');	
	  if (!$isroot) {
	    $msg = JText::_('COM_ONEPAGE_PERMISSION_DENIED'); 
		JFactory::getApplication()->enqueueMessage($msg); 
		return false; 
	  }
	  return true; 
   }


   
    
   

   
	function apply()
	{
		return $this->save(); 
	}
	
	function rename_theme()
	{
	  JRequest::setVar('orig_selected_template', JRequest::getVar('selected_template')); 
	  JRequest::setVar('selected_template', JRequest::getVar('selected_template').'_custom'); 
	  JRequest::setVar('rename_to_custom', true); 
	  return $this->save(); 
	}
   function save()  // <-- edit, add, delete 
  {
    
    JRequest::setVar( 'view', '[ config ]' );
    JRequest::setVar( 'layout', 'default'  );  
    
    $model = $this->getModel('hikaconfig');
    $reply = $model->store();
    if ($reply===true) {
    $msg = JText::_('COM_ONEPAGE_CONFIGURATION_SAVED');
    } else { $msg = JText::_('COM_ONEPAGE_ERROR_SAVING_CONFIGURATION'); 
    }
    $link = 'index.php?option=com_onepage&view=hikaconfig';
	
	$opc_lang = JRequest::getVar('opclang', ''); 
	$link .= '&opclang='.$opc_lang; 
	
	/*
	$y = JFactory::getApplication()->get('_messageQueue', array());
	
	

	
	  $x = JFactory::getApplication()->set('messageQueue', array()); 
			$x = JFactory::getApplication()->set('_messageQueue', array()); 
			$session = JFactory::getSession();
            $sessionQueue = $session->set('application.queue', array());
	*/
	
    $this->setRedirect($link, $msg); 
  }

}
