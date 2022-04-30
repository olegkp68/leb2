<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldStatuspair extends JFormField
{
  protected $type = 'statuspair';
  function getInput()
	{
		
		
		//debug_zval_dump($this->form->data->get('module')); die(); 
		//var_dump($this->value); var_dump($this->value['custom_field']); die(); 
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 $config = new JModelConfig(); 
		 $config->loadVmConfig(); 
		 
		 $mytype = $this->element['mytype'];
		 $mytype = (string)$mytype; 
		 
		 
		 VmConfig::loadJLang('com_virtuemart');
		 VmConfig::loadJLang('plg_vmpsplugin', false);
		
		if (!class_exists('CurrencyDisplay'))
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');

		if (!class_exists('VmHTML'))
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'html.php');

		if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DIRECTORY_SEPARATOR.'vmpsplugin.php');
		$orderStatusModel=VmModel::getModel('orderstatus');
		$orderStates = $orderStatusModel->getOrderStatusList();
		
		
		
		 $this->name = str_replace('[]', '', $this->name); 
		 ob_start(); 
		 foreach ($orderStates as $status) {
			 

			 $code = $status->order_status_code;

			 
		  ?><div >
		  <?php echo htmlentities('('.$status->order_status_code.') '.JText::_($status->order_status_name)).': '.htmlentities($this->element['label']).':';
		  ?>
		  </div><br /><input type="text" value="<?php 
		  if (!empty($this->value) && (is_object($this->value))) {
		   echo $this->value->$code; 
		  }
		  ?>" placeholder="<?php echo htmlentities('('.$status->order_status_code.') '.JText::_($status->order_status_name)).': '.htmlentities($this->element['label']); ?>" name="<?php echo $this->name; ?>[<?php echo $code; ?>]" /><br /><br /><?php
		 }
		 
		 $html = ob_get_clean(); 
		
		
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

