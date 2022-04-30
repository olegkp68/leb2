<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldVmmultilang extends JFormField
{
  protected $type = 'vmmultilang';
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
		
		
		 $active_langs = VmConfig::get('active_languages', array('en-GB')); 
		 $this->name = str_replace('[]', '', $this->name); 
		 ob_start(); 
		 foreach ($active_langs as $lang) {
		  ?><input type="text" value="<?php 
		  if (!empty($this->value) && (is_object($this->value))) {
		   echo $this->value->$lang; 
		  }
		  ?>" placeholder="<?php echo htmlentities($lang).': '.htmlentities($this->element['label']); ?>" name="<?php echo $this->name; ?>[<?php echo $lang; ?>]" /><br /><?php
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

