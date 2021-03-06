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

	defined( '_JEXEC' ) or die( 'Restricted access' );
	jimport('joomla.application.component.view');
	class JViewHikaconfig extends OPCView
	{
		function display($tpl = null)
		{	
			$model = $this->getModel('hikaconfig');
		    OPCplatform::hikaAutoload(); 
			$this->errors = $model->installCheck(); 
			$configModel = $model->getConfigModel(); 
			
			$this->registration = $configModel->getOPCRegistration(); 
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'videohelp.php'); 
			
			$this->model = $model; 
			
			$this->templates = $model->getTemplates();
			
			
			$this->opcexts = $model->getOPCExtensions(); 
			
			$dis = (bool)$model->getDisabledOPC(); 
			
			
			// plugin enabled, check config:
			if (empty($dis)) {
			  $dis = OPCHikaConfig::get('disable_op', true); 
			  
			  
			}
			$this->disable_onepage = $dis; 
			
			
		
			
			parent::display($tpl); 
		}
		
	}
