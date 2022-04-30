<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
class JFormFieldDirlist extends JFormField
{
  protected $type = 'dirlist';
  function getInput()
	{
		$element_definition = (array)$this->element;
		$element_definition = $element_definition['@attributes'];
		
		$name = (string)$this->element['name']; 
		$element_id = 'id_'.JFile::makeSafe($name); 
		if (isset($element_definition['multiple'])) {
			$multiple = true; 
			$name = str_replace('[]', '', $name); 
		}
		$getfiles = false; 
		if (!empty($element_definition['getfiles'])) {
			$getfiles = (string)$this->element['getfiles']; 
			
		}
		
		if (empty($this->value)) $this->value = 'example'; 
		
		if (!$this->checkPerm()) {
			return 'This Feature is available only to Super Administrators'; 
		}
		$pluginname = $this->form->getData()->get('element', 'plugin');
		$path = (string)$this->element['path']; 
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path); 
		$path = JPATH_SITE.$path; 
		$fns = array(); 
		if ((file_exists($path)) && (is_dir($path))) {
			$di = new DirectoryIterator($path);
			foreach ($di as $fileInfo) {
				if($fileInfo->isDot()) continue;
				if (empty($getfiles)) {
				if($fileInfo->isDir()) {
					
					$fns[] = $fileInfo->getFileName(); 
				}
				}
				else {
					if($fileInfo->isFile()) {
					$fnm = $fileInfo->getFileName(); 
					if (strpos($getfiles, '*.') === 0) {
						$ext = str_replace('*.', '', $getfiles); 
						$extNow = $fileInfo->getExtension(); 
						if ($extNow === $ext) { 
							$fns[] = $fnm; 
						}
					}
					else {
						$fns[] = $fnm; 
					}
				}
				}
			}
		}
		
		$html = '<select name="jform[params]['.$name.']'; 
		if ($multiple) $html .= '[]'; 
		$html .= '" '; 
		if ($multiple) $html .= ' multiple="multiple" '; 
		
		$html .= ' id="'.$element_id.'" '; 
		$html .= ' >'; 
		foreach ($fns as $o) {
			$html .= '<option value="'.htmlentities($o).'" '; 
			if ((!is_array($this->value)) && ($this->value === $o)) $html .= ' selected="selected" '; 
			if ((is_array($this->value)) && (in_array($o, $this->value)))  $html .= ' selected="selected" '; 
			$html .= ' >'.$o.'</option>'; 
		}
		$html .= '</select>'; 
		
		
	   return $html; 
	
	}
	
	
	function checkPerm() {
	   $user = JFactory::getUser(); 
	   
      $isroot = $user->authorise('core.admin');	
	  
	  if (!$isroot) 
	  {
		
		return false; 
	  }
	  
	  $iss = JFactory::getApplication()->isSite(); 
	  if (!empty($iss)) return false; 
	  
	  return true; 
   }
	
	
	
}

