<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

 jimport('joomla.application.component.view');
class JViewExt extends OPCView
{
	
	public function display($tpl = null)
	{
	   		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
		 
			$model = new JModelConfig(); 
			$this->opcexts = $model->getOPCExtensions(); 
			
			parent::display($tpl);
		
	}

}
