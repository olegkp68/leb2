<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
 
class JViewThemeconfig extends OPCView
{
	
	 
	public function display($tpl = null)
	{
		
	    $model = $this->getModel();
		$this->form = $model->getForm(); 
		parent::display($tpl);
		
	}

}
