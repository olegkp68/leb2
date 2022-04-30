<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldWarehouse extends JFormField
{
  protected $type = 'warehouse';
  function getInput()
	{
		
		
		
		
		if (!empty($this->element['soapmethod'])) {
			
			$soapmethod = (string)$this->element['soapmethod'];
			
		 

		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
		try {
			$xml = sherpaHelper::sendRequest($soapmethod); 
		}
		catch (Exception $e) {
			return 'Set your security code first and click save'; 
		}
		$datas = array(); 
		
		if ($xml === false) return 'Set your security code first and click save'; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return 'Set your security code first and click save'; 
		
		
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		
		foreach ($xmlres->children() as $i) {

			$paymentdata = $i->ResponseValue->children(); 
			foreach ($paymentdata as $si) {
				$code = (string)$si->WarehouseCode; 
				$desc = (string)$si->Name; 
				$datas[$code] = $desc; 
			}
		}
		
		
				
			
		}
		
		
	    $nm = (string)$this->element['id'];
		$html .= '<select name="'.$this->name.'" id="'.$nm.'_select">'; 
		if (empty($datas)) {
			$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		}
		else
		{
			//var_dump($this->value); die(); 
			
			
		
		foreach ($datas as $key=>$n) {
			$html .= '<option value="'.htmlentities($key).'" ';; 
			if (is_array($this->value) && (!empty($this->value[$current_id])) && ($this->value[$current_id] === $key)) {
				$html .= ' selected="selected" '; 
			}
			else 
			if (is_object($this->value) && (!empty($this->value->$current_id)) && ($this->value->$current_id === $key)) {
				$html .= ' selected="selected" '; 
			}
			$html .= ' >'.htmlentities($key.' '.$n).'</option>'; 
		}
		}
		$html .= '</select>'; 
		
		
		
		
		
		$html .= '<br />'; 
		
		
		
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

