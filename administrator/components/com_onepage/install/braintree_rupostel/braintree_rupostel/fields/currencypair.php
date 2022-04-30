<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldCurrencypair extends JFormField
{
  protected $type = 'currencypair';
  function getInput()
	{
		
		
		//debug_zval_dump($this->form->data->get('module')); die(); 
		//var_dump($this->value); var_dump($this->value['custom_field']); die(); 
		 if (!class_exists('VmConfig')) {
		  
		  require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 }
		 VmConfig::loadConfig(); 
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		 
		 $mytype = $this->element['mytype'];
		 $mytype = (string)$mytype; 
		 
		 $db = JFactory::getDBO(); 
		 $q = 'select `vendor_accepted_currencies` from #__virtuemart_vendors where 1=1'; 
		 $db->setQuery($q); 
		 $res = $db->loadAssocList(); 
		 $currencies = array(); 
		 foreach ($res as $row) {
			 $cS = $row['vendor_accepted_currencies']; 
			 $cA = OPCmini::parseCommas($cS); 
			 foreach ($cA as $c) {
				 $currencies[$c] = $c; 
			 }
			 
		 }
		 $q = 'select virtuemart_currency_id, `currency_code_3` from #__virtuemart_currencies where virtuemart_currency_id IN ('.implode(',', $currencies).')'; 
		 $db->setQuery($q); 
		 $currencies = $db->loadAssocList(); 
		 
		 VmConfig::loadJLang('com_virtuemart');
		 VmConfig::loadJLang('plg_vmpsplugin', false);
		
		if (defined('VMLANG')) {
			$lang = VMLANG; 
		}
		else {
		$lang = VmConfig::$vmlang; 
		if (empty($lang)) {
			$lang = 'en_gb'; 
		}
		}

		
	
		$list = (string)$this->element['list']; 
		
		$this->name = str_replace('[]', '', $this->name); 
		
		$html = ''; 
		
		
		foreach ($currencies as $key=>$currency) {
			$currency_id = $currency['virtuemart_currency_id']; 
			$currency2 = $currency['currency_code_3']; 
			$html .= '<label>'.$currency2.':</label><input placeholder="'.htmlentities($currency2.' - merchantId').'" type="text" name="'.$this->name.'['.$currency_id.']" value="';
			if (!empty($this->value[$currency_id])) {
				$html .= htmlentities($this->value[$currency_id]); 
			}
			$html .= '" /><br />'; 
		}
		
		  return $html; 
		  
		  
		  
		
	
	}
	private function getArray($value) {
	   $a = explode(',', $value); 
	   foreach ($a as $k=>$v) {
	     $a[$k] = trim($v); 
	   }
	   return $a; 
	}
	
	
}

