<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
*      One Page Checkout configuration file
*      Copyright RuposTel s.r.o. under GPL license
*      Version 2 of date 31.March 2012
*      Feel free to modify this file according to your needs
*
*
*     @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
*     @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*     One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
*     VirtueMart is free software. This version may have been modified pursuant
*     to the GNU General Public License, and as distributed it includes or
*     is derivative of works licensed under the GNU General Public License or
*     other free or open source software licenses.
* 
*/





		  if (!class_exists('VmConfig'))
		  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		  VmConfig::loadConfig(); 
		  
		  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		  
		  if (!isset(OPCconfig::$config['opc_vm_config']))  {
			$opc_vm_config = OPCconfig::getArray('opc_vm_config'); 
			OPCconfig::$config['opc_vm_config'] = array(); 
		  

		  
		  if (empty(OPCconfig::$config)) OPCconfig::$config = array(); 
		  if (!empty($opc_vm_config)) {
		  foreach ($opc_vm_config as $k=>$v) {
			  $n = $v['config_subname']; 
			  $val = $v['value']; 
			  OPCconfig::$config['opc_vm_config'][$n][0] = $val; 
			  $toExtract[$n] = $val; 
		  }
		  }
		  }
		  else {
			  foreach (OPCconfig::$config['opc_vm_config'] as $k=>$v) {
				  $toExtract[$k] = $v[0]; 
			  }
		  }
		  
		  OPCconfig::$config['is_migrated'] = true;
	      extract($toExtract, EXTR_SKIP);
			 
		  
		  
  
		  require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	 
		  $selected_template = OPCmini::getSelectedTemplate('clean_simple2', ''); 
	  
	if (empty($disable_theme_overrides))
	if (class_exists('OPCmini'))
	{
		jimport('joomla.filesystem.file');
		$selected_template = JFile::makeSafe($selected_template); 
		if (!empty($selected_template) && (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_onepage".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR."overrides".DIRECTORY_SEPARATOR."onepage.cfg.php")))
		{
  
			include(JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_onepage".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR."overrides".DIRECTORY_SEPARATOR."onepage.cfg.php");
 
		}
	}
	



