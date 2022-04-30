<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class JControllerShopperfields extends JControllerBase
{
    function getViewName() 
	{ 
		return 'shopperfields';		
	} 

   function getModelName() 
	{		
		return 'shopperfields';
	}

    public function save()
	{
	  return $this->apply(); 
	}
	public function apply()
	{

		$msg = ''; 
		$this->setRedirect(JRoute::_('index.php?option=com_onepage&view=shopperfields', false), $msg);
		return false;
	}
	function showerror($msg='') {
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'shopperfields'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'checkboxes.php'); 
		echo $accessdenied.$msg; 
		JFactory::getApplication()->close(); 
	}
	
		function showok($msg='') {
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'shopperfields'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'checkboxes.php'); 
		echo $checkedfield.$msg; 
		JFactory::getApplication()->close(); 
	}
	
	function processCustom($atr) {
		
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'shopperfields'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'checkboxes.php'); 
		
		if (!$this->checkPerm()) {
			return $this->showerror('Error '.__LINE__); 
			JFactory::getApplication()->close(); 
		}
		
		$fn = JRequest::getVar('fn', ''); 
		$selectedvalue = JRequest::getVar('selectedvalue', ''); 
		$newstate = JRequest::getVar('newstate', 0); 
		
		
		
		switch($atr) {
			case 'clear_business':
				OPCconfig::save('business_selector', ''); 
				OPCconfig::save('business_fields2', array()); 
				OPCconfig::save('business2_value', ''); 
				return $this->showok(); 
			case 'is_business2':
				  $datas = OPCconfig::get('business_fields2', array()); 
				  if ((empty($newstate)) && (empty($datas))) {
					  OPCconfig::save('business_selector', ''); 
				  }
				  elseif ((!empty($newstate)) && (!empty($datas))) { 
					  OPCconfig::save('business_selector', $fn); 
				  }
				  break; 
			case 'business_fields2': 
				if (!empty($selectedvalue)) {
				 if (!is_array($selectedvalue)) $selectedvalue = array($selectedvalue); 
				}
				
				
				
				if (empty($selectedvalue)) {
					OPCconfig::save('business_selector', ''); 
				}
				else {
			     OPCconfig::save('business_selector', $fn); 
				}
				OPCconfig::save('business_fields2', $selectedvalue); 
				if (!empty($selectedvalue)) {
				$is_business2 = OPCconfig::get('is_business2', false); 
				if ($is_business2) {
					$business_fields = OPCconfig::get('business_fields', array()); 
					foreach ($selectedvalue as $fnb) {
					if (!in_array($fnb, $business_fields)) {
						$business_fields[] = $fnb;
					}
					}
					OPCconfig::save('business_fields', $business_fields); 
					
				}
				}
				JFactory::getApplication()->close(); 
				break; 
			case 'country_config':
				$countries = JRequest::getVar('country_field_selected', array()); //[5],[4]
				$first = reset($countries); if (empty($first)) $countries = array(); 
				
				$fn = JRequest::getVar('config_sub', ''); //address_2
				
				if (empty($fn)) return $this->showerror(); 
				
				$config_id = JRequest::getInt('config_id', -1); 
				if ($config_id < 0) return $this->showerror(); 
				
				if (empty($countries)) {
					$prev = OPCconfig::getValue('country_config', $fn, $config_id); 
				 	
					if (empty($prev)) {
						return $this->showerror(JText::_('COM_ONEPAGE_NO_COUNTRY_SELELECTED')); 
					}
				   OPCconfig::clearConfig('country_config', $fn, $config_id); 
			
				   return $this->showok(JText::_('COM_ONEPAGE_CONFIGURATION_CLEARED')); 
				}
				
			
				$html5_autocomplete = JRequest::getVar('html5_autocomplete', ''); 
				$html5_fields_validation = JRequest::getVar('html5_fields_validation', ''); 
				$html5_placeholder = JRequest::getVar('html5_placeholder', ''); 
				$html5_validation_error = JRequest::getVar('html5_validation_error', ''); 
				
				$custom_css = JRequest::getVar('custom_css', ''); 
				
				$custom_css = str_replace("\r\r\n", "\n", $custom_css); 
				$custom_css = str_replace("\r\n", "\n", $custom_css); 
				
				$nc = array(); 
				foreach ($countries as $c) {
					$nc[(int)$c] = (int)$c; 
				}
				
				$tostore = new stdClass(); 
				$tostore->countries = $nc; 
				$tostore->html5_autocomplete = $html5_autocomplete; 
				$tostore->html5_fields_validation = $html5_fields_validation; 
				$tostore->html5_placeholder = $html5_placeholder; 
				$tostore->html5_validation_error = $html5_validation_error; 
				$tostore->custom_css = $custom_css;
				OPCconfig::store('country_config', $fn, $config_id, $tostore); 
				return $this->showok(); 
			break; 
		}
	}
	
	function alterField() {
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'shopperfields'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'checkboxes.php'); 
		
		if (!$this->checkPerm()) {
			return $this->showerror('Error '.__LINE__); 
			JFactory::getApplication()->close(); 
		}
		
		$fn = JRequest::getVar('fn', ''); 
		$atr = JRequest::getVar('atr', ''); 
		$newstate = JRequest::getVar('newstate', 0); 
		
		$this->processCustom($atr);
		
		
		
		$config_name = JRequest::getVar('config_name', ''); 
		$config_sub = JRequest::getVar('config_sub', ''); 
		if ((!empty($config_name)) && (!empty($config_sub))) {
			if ($config_name === $atr) {
				$config_ref = JRequest::getInt('config_ref', 0); 
				$selectedvalue = JRequest::getVar('selectedvalue', ''); 
				OPCconfig::store($config_name, $config_sub, $config_ref, $selectedvalue); 
				echo $checkedfield; 
				JFactory::getApplication()->close(); 
			}
		}
		if ($fn === 'singleselect') {
			$selectedvalue = JRequest::getVar('selectedvalue', ''); 
			
			OPCconfig::save($atr, $selectedvalue); 
			echo $checkedfield; 
			JFactory::getApplication()->close(); 
		}
		
		
		
		
		if ((!empty($fn)) && (!empty($atr))) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
			
			
			$current_data = OPCconfig::get($atr, array()); 
			
			
			$data_type = JRequest::getVar('data_type', null); 
				if (!is_null($data_type)) {
				if ($data_type === 'bool') {
					
					
					if (empty($newstate)) {
						OPCconfig::save($atr, false); 
						echo $uncheckedfield; 
						JFactory::getApplication()->close(); 
					}
					else {
						OPCconfig::save($atr, true); 
						echo $checkedfield; 
						JFactory::getApplication()->close(); 
					}
					
					
				}
			}
			
			
			if ($newstate === 'select') {
				//current config name:
				if ($atr === 'acymailing_fields') {
					
					$default = ''; 
					$con = OPCconfig::getValue('acymailing_fields', $fn, 0, $default); 
					$selectedvalue = JRequest::getVar('selectedvalue', ''); 
					
				
					if ($con !== $selectedvalue) {
						
						if (empty($selectedvalue)) {
							OPCconfig::clearConfig('acymailing_fields', $fn, 0); 
							
						}
						else {
						  OPCconfig::store('acymailing_fields', $fn, 0, $selectedvalue); 
						 
						}
						
					}
					else {
					
					}
					
					$test = OPCconfig::getValue('acymailing_fields', $fn, 0, $default); 
					
					
					
					echo $checkedfield; 
					JFactory::getApplication()->close(); 
				}
				else {
				$config = OPCconfig::get($atr, array()); 
				
				if ($fn === 'select') {
					$selectedvalue = JRequest::getVar('selectedvalue', ''); 
					$config = $selectedvalue; 
				}
				else {
				//current field name:
				
				$selectedvalue = JRequest::getVar('selectedvalue', ''); 
				
				if (empty($selectedvalue)) {
					unset($config[$fn]); 
				}
				else {
					$config[$fn] = $selectedvalue; 
				}
				}
				
				 
				
				OPCconfig::save($atr, $config); 
				}
				
				
				echo $checkedfield; 
				JFactory::getApplication()->close(); 
				
			}
			else {
				$newstate = (int)$newstate; 
			
			if (is_array($current_data)) {
			if (empty($newstate) && (in_array($fn, $current_data))) {
				foreach ($current_data as $k => $v) {
					if ($v === $fn) {
						unset($current_data[$k]); 
						OPCconfig::save($atr, $current_data); 
						echo $uncheckedfield; 
						JFactory::getApplication()->close(); 
						break; 
					}
				}
			}
			else {
				if (!empty($newstate) && (!in_array($fn, $current_data))) {
					$current_data[] = $fn;
					OPCconfig::save($atr, $current_data); 
					echo $checkedfield; 
					JFactory::getApplication()->close(); 
					
				}
				else {
					echo $uncheckedfield; 
					JFactory::getApplication()->close(); 
				}
			}
			}
			else {
				
			}
			}
			
		}
		
			return $this->showerror('Error '.__LINE__); 
			JFactory::getApplication()->close(); 
		
		
		
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
