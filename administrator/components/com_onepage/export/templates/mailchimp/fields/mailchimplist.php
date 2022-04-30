<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldMailchimplist extends JFormField
{
  protected $type = 'mailchimplist';
  function getInput()
	{ 
		$option = JRequest::getVar('option'); 
		
		if (empty($this->value)) $this->value = array(); 
		$this->value = (array)$this->value; 
		
		$this->name = str_replace('[]', '', $this->name); 
		
		$html = ''; 
		
		$nm = $this->element['id'];
		$nm = (string)$nm; 
		
		$mytype = (string)$this->element['filter-tab-type'];
		$myname = (string)$this->element['name'];
		
		 if (!empty($this->filter_type)) {
			 $this->value[$mytype] = (array)$this->value[$mytype]; 
			 if (empty($this->value[$mytype]['generic'])) return ''; 
			 switch($this->filter_type) {
			  case 'manufacturer':
			  
				$where_id = JRequest::getVar('virtuemart_manufacturer_id', JRequest::getVar('cid', 0)); 
				break; 
			  case 'category':
			    $where_id = JRequest::getVar('virtuemart_category_id', JRequest::getVar('cid', 0)); 
				break; 
			  case 'product':
			    $where_id = JRequest::getVar('virtuemart_product_id', JRequest::getVar('cid', 0)); 
				break; 
				
			  
			  
			}
			if (is_array($where_id)) $where_id = reset($where_id); 
			  $where_id = (int)$where_id; 
			
			if (empty($where_id)) return ''; 
			
			$dispatcher = JDispatcher::getInstance();
		$lists = array(); 
		$dispatcher->trigger('plgMailChimpGetLists', array(&$lists));
		
		$default = ''; 
		$retval = OPCconfig::getValue($myname, $mytype, (int)$where_id, $default); 
		
		
		$html .= '<select name="'.$this->name.'['.$mytype.']['.(int)$where_id.']" id="'.$nm.'_select">'; 
		$html .= '<option value="0">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		foreach ($lists as $id => $name) {
			$html .= '<option '; 
			if ($retval === $id) $html .= ' selected="selected" '; 
			$html .= ' value="'.htmlentities($id).'">'.htmlentities($id.' - '.$name).'</option>'; 
		}
		$html .= '</select>'; 
		return $html; 
			
			 
		 }
		 else {
			 
			 //$dispatcher = JDispatcher::getInstance();
			$lists = array(); 
			//$dispatcher->trigger('plgMailChimpGetLists', array(&$lists));
			$where_id = 'generic'; 
			$html .= '<select name="'.$this->name.'['.$mytype.']['.$where_id.']" id="'.$nm.'_select">'; 
			$html .= '<option value="0">'.JText::_('COM_ONEPAGE_NOT_CONFIGURED').'</option>'; 
		
			$html .= '<option '; 
			if (!empty($this->value[$mytype])) {
				$this->value[$mytype] = (array)$this->value[$mytype]; 
			}
			if (!empty($this->value[$mytype]['generic'])) $html .= ' selected="selected" '; 
			$html .= ' value="1">'.htmlentities(JText::_('COM_ONEPAGE_DISPLAY_TAB')).'</option>'; 
		
		$html .= '</select>'; 
		return $html; 
		 }
		
		
		
		
		
		
		
	
	}
	
	public function onStoreGeneric($field, $data=array(), $xmlf='', $tid=0) {
		static $done; 
		if (!empty($done[$xmlf])) return; 
		$done[$xmlf] = true; 
		foreach ($data[$xmlf] as $key => $val) {
			
			foreach ($val as $generic => $value) {

				if (!empty($value['generic'])) {
					$Vx = 1; 
					//listcategory, category, 0 = 1
					
					OPCconfig::store($key, $generic, 0, $Vx); 
				}
				else {
					OPCconfig::clear($key, $generic, 0); 
				}
			}
			
		}
	}
	
	public function onStoreField($field, $data=array(), $filter_type='', $xmlf='', $tid=0) {
	   
	   foreach ($data[$xmlf] as $typename => $row) {
		   foreach ($row as $type => $vm_method) {
			   foreach ($vm_method as $vm_id => $mailchimp_id) {
				   /*
				   var_dump($typename); //listmanufacturer
				   var_dump($type);  //manufacturer
				   var_dump($vm_id);  //3
				   var_dump($mailchimp_id);  //xxss
				   */
				   
				   if (empty($mailchimp_id)) {
					   OPCconfig::clear($typename, $type, (int)$vm_id); 
				   }
				   else {
					OPCconfig::store($typename, $type, (int)$vm_id, $mailchimp_id); 
					
				   }
				   
			   }
		   }
	   }
	  
	}
}