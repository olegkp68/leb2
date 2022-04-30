<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldPaymentpair extends JFormField
{
  protected $type = 'paymentpair';
  function getInput()
	{
		 $is_single = false;
		 if (!empty($this->filter_type)) {
			 $is_single = true; 
		 }
		//debug_zval_dump($this->form->data->get('module')); die(); 
		//var_dump($this->value); var_dump($this->value['custom_field']); die(); 
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
		$q = 'select lx.`'.$mytype.'_name`, cx.`virtuemart_'.$mytype.'method_id` from `#__virtuemart_'.$mytype.'methods_'.$lang.'` as lx, #__virtuemart_'.$mytype.'methods as cx where cx.virtuemart_'.$mytype.'method_id = lx.virtuemart_'.$mytype.'method_id ';
		
		if ($is_single) {
			$method_id = JRequest::getVar('cid', 0); 
			if (is_array($method_id)) $method_id = reset($method_id); 
			$method_id = (int)$method_id; 
			if (!empty($method_id)) {
				$q .= ' and cx.`virtuemart_'.$mytype.'method_id` = '.(int)$method_id; 
			}
		}
		else {
			$q .= ' and cx.`published` = 1 '; 
		}
		$q .= ' order by lx.`'.$mytype.'_name`'; 
		
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
			return '<b style="color:red;">Set your security code first and click save</b>'; 
		}
		
		
		
		$datas = array(); 
		
		
		if ($xml === false) return '<b style="color:red;">Set your security code first and click save</b>'; 
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
		if ($is_single) $list = 'me'; 
		
		$this->name = str_replace('[]', '', $this->name); 
		$selected_virtuemart_custom_id = 0; 
		$html = ''; 
		
		if ($list === 'reversed') {
			$shipments = $res; 
			$res = $datas; 
			
			$newdatas = array(); 
			foreach ($shipments as $k=>$v) {
				$id = (int)$v['virtuemart_'.$mytype.'method_id'];
				$newdatas[$id] = $v[$mytype.'_name']; 
			}
			$datas = $newdatas; 
		}
		if ($mytype === 'shipment') {
			//var_dump($this->value); die(); 
		}
		foreach ($res as $key=>$shipment) {
			
			
		if ($list === 'me') {
			$nm = 'id_'.JFile::makeSafe($this->name).'_'.$mytype.'_'.$shipment['virtuemart_'.$mytype.'method_id']; 
			$html .= '<div class="pair_with"><b>'.htmlspecialchars(JText::_($shipment[$mytype.'_name'])).'</b></div>';
			
			$current_id = $shipment['virtuemart_'.$mytype.'method_id'] = (int)$shipment['virtuemart_'.$mytype.'method_id']; 
			$current_id = $mytype.'_'.$current_id; 
			
		}
		elseif ($list === 'reversed') {
			$nm = 'id_'.JFile::makeSafe($this->name).'_'.$mytype.'_'.$key;
		    $html .= '<div class="pair_with"><b>'.htmlspecialchars($key.' '.$shipment).'</b></div>';
			
			$current_id = $mytype.'_'.$key; 
		}
		else {
		$html .= '<select name="'.$this->name.'['.$mytype.'_'.$shipment['virtuemart_'.$mytype.'method_id'].']" id="'.$nm.'_select">'; 
		$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		foreach ($res as $row) {
			
			$current_id = $row['virtuemart_'.$mytype.'method_id'] = (int)$row['virtuemart_'.$mytype.'method_id']; 
			
			$current_id = $mytype.'_'.$current_id; 
			
			
			
			
			$html .= '<option '; 
			if (is_array($this->value)) {
			if (isset($this->value[$current_id])) {
				$this->value[$current_id] = (int)$this->value['select']; 
			if ($this->value[$current_id] === $row['virtuemart_'.$mytype.'method_id']) {
				$html .= ' selected="selected" '; 
				$selected_virtuemart_custom_id = (int)$row['virtuemart_'.$mytype.'method_id'];
			}
			}
			}
			else {
				if (isset($this->value->$current_id)) {
					$this->value->$current_id = (int)$this->value->select; 
				if ($this->value->$current_id === $row['virtuemart_'.$mytype.'method_id']) {
				$html .= ' selected="selected" '; 
				$selected_virtuemart_custom_id = (int)$row['virtuemart_'.$mytype.'method_id'];
			}
				}
			}
			$html .= ' value="'.$row['virtuemart_'.$mytype.'method_id'].'">'.htmlspecialchars(JText::_($row[$mytype.'_name'])).'</option>'; 
		}
		$html .= '</select>'; 
		}
		$html .= '<label for="'.$nm.'_select">Pair with sherpa '.$mytype.' method: </label>'; 
		
		
		
			
		$html .= '<select name="'.$this->name.'['.$current_id.']'; 
		if ($list === 'reversed') {
			$html .= '[]'; 
		}
		$html .= '" id="'.$nm.'_select" '; 
		if ($list === 'reversed') {
			$html .= ' multiple="multiple" '; 
			//$html .= ' class="vm-chzn-select" '; 
		}
		//$html .= ' class="vm-chzn-select" '; 
		$html .= '>'; 
		if (empty($datas)) {
			$html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		}
		else
		{
			//var_dump($this->value); die(); 
			
		if ($list !== 'reversed') {
		 $html .= '<option value="">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		}
		foreach ($datas as $key=>$n) {
			$html .= '<option value="'.htmlentities($key).'" ';; 
			/*
			if (is_array($this->value) && (!empty($this->value[$current_id])) && ($this->value[$current_id] === $key)) {
				$html .= ' selected="selected" '; 
			}
			else 
			*/
		//if (false) 
		
		
		if (is_object($this->value)) {
			$this->value->$current_id = (array)$this->value->$current_id; 
		}
		
			if (is_object($this->value) && (!empty($this->value->$current_id)) && ((is_string($this->value->$current_id) && ($this->value->$current_id === $key)) || (is_array($this->value->$current_id) && (in_array($key, $this->value->$current_id))))) {
				$html .= ' selected="selected" '; 
			}
		//check reversed as well:
		$current_id_test = $mytype.'_'.$key; 
		$testData = (array)$this->value->$current_id_test; 
		$myIdTest = str_replace($mytype.'_', '', $current_id); 
		
		if (in_array($myIdTest, $testData)) {
			$html .= ' selected="selected" '; 
		}
			
			$html .= ' >'.htmlentities($key.' '.$n).'</option>'; 
		}
		}
		$html .= '</select>'; 
		
		}
		
		
		
		$html .= '<br />'; 
		
		
		
		
		  return $html; 
		  
		  
		  
		
	
	}
	
	public function onStoreField($field, $data=array(), $filter_type='', $xmlf='', $tid=0) {
		if (!empty($xmlf) && (!empty($tid))) {
				$default = new stdClass();  
				$allData = OPCconfig::getValue('order_export_config', $xmlf, $tid, $default);
				
				
				$was_test = 'was_'.$xmlf; 
				
				$search_key = $filter_type.'_'; 
				
				
				
				
				if ($filter_type === 'shipment') {
				if (isset($data[$was_test])) {
					foreach ($data[$was_test] as $kn => $val) {
						
						foreach ($val as $vm_shipment_id => $remote_val ) {
							$key = $search_key.$remote_val;
							if (!isset($allData->$kn)) $allData->$kn = new stdClass(); 
							if (!isset($allData->$kn->$key)) $allData->$kn->$key = array();
							
							
							
							$ship_id = (int)str_replace($search_key, '', $vm_shipment_id); 
							$allData->$kn->{$key} = (array)$allData->$kn->{$key}; 
							$allData->$kn->{$key}[$ship_id] = $ship_id; 
							if (in_array($ship_id, $allData->$kn->{$key})) {
								$index = array_search($ship_id, $allData->$kn->{$key}); 
								if ($index !== false) { 
									unset($allData->$kn->{$key}[$index]); 
								}
							}
							
							
							
						}
						
					}
				}
				$working_remote_ids = array(); 
				if (isset($data[$xmlf])) {
					foreach ($data[$xmlf] as $kn => $val) {
						
						foreach ($val as $vm_shipment_id => $remote_val ) {
							$key = $search_key.$remote_val;
							if (!isset($allData->$kn)) $allData->$kn = new stdClass(); 
							if (!isset($allData->$kn->$key)) $allData->$kn->$key = array();
							
							
							
							$ship_id = (int)str_replace($search_key, '', $vm_shipment_id); 
							$allData->$kn->{$key} = (array)$allData->$kn->{$key}; 
							$allData->$kn->{$key}[$ship_id] = $ship_id; 
							
							$working_remote_ids[$ship_id] = $key; 
							
							
						}
						
						//make sure we got only 1 VM method agains 1 remote method:
						foreach ($allData->{$kn} as $testKey => $testVals) {
							foreach ($testVals as $ind=> $ship_id) {
								if (isset($working_remote_ids[$ship_id])) {
									$remote_id = $working_remote_ids[$ship_id];
									if ($remote_id !== $testKey) {
									$allData->{$kn}->{$testKey} = (array)$allData->{$kn}->{$testKey}; 
									//echo "unsetting ship_id: $ship_id kn: $kn $testKey $ind with value ".var_export($allData->{$kn}->{$testKey}[$ind], true)."\n<br />"; 
									JFactory::getApplication()->enqueueMessage('Unsetting current shipping method from remote method '.$testKey); 
									unset($allData->{$kn}->{$testKey}[$ind]); 
									}
									
								}
							}
							
						}
						
					}
				
					
					OPCconfig::store('order_export_config', $xmlf, $tid, $allData);
				}
				}
				if ($filter_type === 'payment') {
					
				
				foreach($data[$was_test] as $k=>$v) {
					foreach ($v as $vv => $vvv) {
						if (isset($allData->{$k})) {
						$allData->{$k} = (array)$allData->{$k}; 
						unset($allData->{$k}[$vv]); 
						}
					}
				}
				foreach ($data[$xmlf] as $k=>$v) {
					foreach ($v as $vv => $vvv) {
						$allData->{$k} = (array)$allData->{$k}; 
						$allData->{$k}[$vv] = $vvv; 
					}
				}
				
				OPCconfig::store('order_export_config', $xmlf, $tid, $allData);
				
				}
		}
		
	}
	
	
	private function getArray($value) {
	   $a = explode(',', $value); 
	   foreach ($a as $k=>$v) {
	     $a[$k] = trim($v); 
	   }
	   return $a; 
	}
	
	
}

