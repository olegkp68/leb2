<?php

require_once(__DIR__.DIRECTORY_SEPARATOR.'vmpluginbase.php'); 
class vmPlugin extends vmPluginOverride {
	
	
	
	protected function declarePluginParams ($psType, &$data) {

	
		if (!is_object($data)) return null; 
		
		//vmdebug('declarePluginParams ',$this->_psType,$data);
		if(!empty($this->_psType)){
			$element = $this->_psType.'_element';
			$jplugin_id = $this->_psType.'_jplugin_id';
			if(!isset($data->$element) or !isset($data->$jplugin_id)) {
				
				return FALSE;
			}
			if(!$this->selectedThis($psType,$data->$element,$data->$jplugin_id)){
				
				
				return FALSE;
			}
		}
		if (!class_exists ('VmTable')) {
			require(JPATH_VM_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vmtable.php');
		}
		
		if (defined('VM_VERSION') && (VM_VERSION >= 3))
		 {
		     //Is only used for the config tables!
		//VmTable::bindParameterable ($data, $data->_xParams, $this->_varsToPushParam);
		if(isset($this->_varsToPushParam)){
			if(isset($data->_varsToPushParam)){
				$data->_varsToPushParam = array_merge((array)$data->_varsToPushParam, (array)$this->_varsToPushParam);
			} else {
				$data->_varsToPushParam = (array)$this->_varsToPushParam;
			}
			//vmdebug(' vars to push',$data->_varsToPushParam);
			//$data->_varsToPushParam = $this->_varsToPushParam;
		} else{
			vmdebug('no vars to push?',$this);
		}
		 }
		 else
		 {
			 
		if (!empty($this->_xParams)) {
			if (empty($this->_varsToPushParam)) $this->_varsToPushParam = array(); 
		    VmTable::bindParameterable ($data, $this->_xParams, $this->_varsToPushParam);
		}
		 }
		 
		 if($this->_cryptedFields){
			$data->setCryptedFields($this->_cryptedFields);
		}
		return TRUE;

	}

}