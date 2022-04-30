<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class JViewShopperfields extends OPCView
{
	
	public function display($tpl = null)
	{
		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 
		 
		 
		 
		 $config = new JModelConfig(); 
		 $config->loadVmConfig(); 
	     $this->ftypes = $ftypes = $config->getFTypes(); 
		 
		 $coref = array(); 
		 $ulist = $config->getUserFieldsLists($coref); 
		 $this->clist = $coref; 
		 $this->ulist = $ulist; 
		 $this->alllist = $this->ulist; 
		 $this->countries = $config->getShippingCountries();
 		 $this->acyfields = $config->getAcyFields(); 
		 //$model = $this->getModel();
		 //$this->form = $model->getForm(); 
		
		$layout = JRequest::getVar('layout', 'default'); 
		$fn = JRequest::getVar('fn', ''); 
		
		if ($layout === 'edit') {
			foreach ($this->ulist as $k=>$v) {
				if ($v->name !== $fn) {
					unset($this->ulist[$k]); 
					$this->setLayout('edit'); 
				}
			}
			
			
		}
		
		parent::display($tpl);
		
	}

}