<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class JControllerCron extends JControllerBase
{
     function getViewName() 
	{ 
		return 'cron';		
	} 

   function getModelName() 
	{		
		return 'cron';
	}

    public function save()
	{
	  return $this->apply(); 
	}
	public function apply()
	{

		$msg = ''; 
		$this->setRedirect(JRoute::_('index.php?option=com_onepage&view=cron', false), $msg);
		return false;
	}

	
}
