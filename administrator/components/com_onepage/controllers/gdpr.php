<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class JControllerGdpr extends JControllerBase
{
     function getViewName() 
	{ 
		return 'gdpr';		
	} 

   function getModelName() 
	{		
		return 'gdpr';
	}

    public function save()
	{
	  return $this->apply(); 
	}
	public function apply()
	{

		$msg = ''; 
		$this->setRedirect(JRoute::_('index.php?option=com_onepage&view=gdpr', false), $msg);
		return false;
	}

	
}
