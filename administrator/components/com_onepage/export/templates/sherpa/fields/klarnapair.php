<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldKlarnapair extends JFormField
{
  protected $type = 'klarnapair';
  function getInput()
	{
		
		
		
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 $config = new JModelConfig(); 
		 $config->loadVmConfig(); 
		 
		 $mytype = $this->element['mytype'];
		 $mytype = (string)$mytype; 
		 
		 
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
		$db = JFactory::getDBO(); 
		$q = 'select lx.`'.$mytype.'_name`, cx.`virtuemart_'.$mytype.'method_id` from `#__virtuemart_'.$mytype.'methods_'.$lang.'` as lx, #__virtuemart_'.$mytype.'methods as cx where cx.virtuemart_'.$mytype.'method_id = lx.virtuemart_'.$mytype.'method_id and cx.`published` = 1 order by lx.`'.$mytype.'_name`'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (empty($res)) return ''; 
		
		if (!empty($this->element['soapmethod'])) {
			
			$soapmethod = (string)$this->element['soapmethod'];
			
		 

		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
		try {
			$xml = sherpaHelper::sendRequest($soapmethod); 
			
		}
		catch (Exception $e) {
			$msg = (string)$e; 
			JFactory::getApplication()->enqueueMessage($msg); 
			return 'Set your security code first and click save'; 
		}
		
		
		$datas = array(); 
		
		
		if ($xml === false) return 'Set your security code first and click save'; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return 'Set your security code first and click save'; 
		
		
		
		if ($soapmethod === 'PaymentMethodList') {
		
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		
		foreach ($xmlres->children() as $i) {

			$paymentdata = $i->ResponseValue->children(); 
			foreach ($paymentdata as $si) {
				$code = (string)$si->PaymentMethodCode; 
				$desc = (string)$si->PaymentMethodDescription; 
				$datas[$code] = $desc; 
			}
		}
		
		}
		elseif ($soapmethod === 'ParcelServiceList') {
			
			$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		
		
		
		foreach ($xmlres->children() as $i) {

			$paymentdata = $i->ResponseValue->children(); 
			foreach ($paymentdata as $si) {
				$code = (string)$si->ParcelServiceCode; 
				$code2 = (string)$si->ParcelTypeCode; 
				$desc = (string)$si->ParcelServiceDescription; 
				$datas[$code.'____'.$code2] = $desc; 
			}
		}
			
		}			
			
		}
		
		
		
		$list = (string)$this->element['list']; 
		
		$this->name = str_replace('[]', '', $this->name); 
		$selected_virtuemart_custom_id = 0; 
		$html = ''; 
		
		
		
		
		$html .= '<select name="'.$this->name.'['.$mytype.']" id="'.$nm.'_select">'; 
		$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		
		$current_id = $mytype; 
		
		foreach ($res as $key=>$row) {
		$html .= '<option '; 
		
			
		if (isset($this->value->{$current_id})) {
					//$this->value->$current_id = (int)$this->value->select; 
				if ($this->value->{$current_id} == $row['virtuemart_'.$mytype.'method_id']) {
				$html .= ' selected="selected" '; 
				$selected_virtuemart_custom_id = (int)$row['virtuemart_'.$mytype.'method_id'];
			}
				}
			
			$html .= ' value="'.$row['virtuemart_'.$mytype.'method_id'].'">'.htmlspecialchars(JText::_($row[$mytype.'_name'])).'</option>'; 
		}
		$html .= '</select>'; 
		
		$current_id = 'order_total';
		$html .= '<input type="text" placeholder="Min. order total" name="'.$this->name.'[order_total]" value="'; 
		
				if (isset($this->value->{$current_id})) {
					$html .= (int)$this->value->{$current_id}; 
					
			
				}
			
		$html .= '" />'; 
		
		$html .= '<select name="'.$this->name.'[sherpa_shipping]">'; 
		$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		$current_id = 'sherpa_shipping'; 
		foreach ($datas as $ind => $val) {
			$html .= '<option value="'.htmlentities($ind).'" '; 
			
		
				if (isset($this->value->$current_id)) {
					
				if ($this->value->{$current_id} == $ind) {
				$html .= ' selected="selected" '; 
				
			}
							}
			
			$html .= ' >'.$ind.'</option>'; 
		}
		$html .= '</select>'; 
		
		
		$db = JFactory::getDBO(); 
		$q = 'select virtuemart_country_id as `id`, country_name from #__virtuemart_countries where 1=1'; 
		$db->setQuery($q); 
		$cc = $db->loadAssocList(); 
		
		$html .= '<select name="'.$this->name.'[country]">'; 
		$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		$current_id = 'country'; 
		foreach ($cc as $ind => $row) {
			$html .= '<option value="'.htmlentities($row['id']).'" '; 
			
		
				if (isset($this->value->{$current_id})) {
					
				if ($this->value->{$current_id} == $row['id']) {
				$html .= ' selected="selected" '; 
				
			}
							}
			
			$html .= ' >'.$row['country_name'].'</option>'; 
		}
		$html .= '</select>'; 
		
		
		
		$html .= '<br /><div style="clear: both; float: left;">&nbsp;</div>'; 
		
		
		
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

