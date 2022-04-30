<?php
defined('_JEXEC') or die('Restricted access');

class OPChikaconfig {
	public static $defaultConfig; 
	public static $fullConfig;
	public static function get($var, $default) {
		
		//if ($var === 'selected_theme') return 'clean_simple2'; 
		
		$ref = OPChikaRef::getInstance(); 
		switch ($var) {
			case 'useSSL': 
			  return $ref->config->get('force_ssl', 0);
			  break; 
			 case 'opc_ajax_fields': 
			  return array('address_country', 'address_state', 'address_zip', 'address_post_code'); 
			  break; 
		}
		return OPCconfig::get($var, $default); 
	}
	
	public static function set($var, $value) {
		$key = 'hika_config'; 
		OPCconfig::store($key, $var, 0, $value); 
		
	}
	
	
	public static function loadFullConfig() {
	if (!empty(self::$fullConfig)) return self::$fullConfig; 
	
	$defaultConfig = OPCHikaConfig::loadDefaultConfig(); 
	$hika_config = OPCConfig::getArray('hika_config'); 
	
	foreach ($hika_config as $id=>$row) {
		$val = $row['value']; 
		$key = $row['config_subname']; 
		
		if (!is_numeric($key)) {
			$defaultConfig[$key] = $val; 
	}
	}
	self::$fullConfig = $defaultConfig;
	return self::$fullConfig; 
	
	}
	
	
	public static function loadDefaultConfig() {
			self::$defaultConfig = array(); 
			$config_template = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'hikaconfig.xml';
			$fields = simplexml_load_file($config_template); 
			
			
			foreach ($fields as $field) {
				
				$attributes = $field->attributes(); 
				$name = (string)$attributes->name; 
				$default = (string)$attributes->default; 
				if (isset($attributes->transform)) {
				 $transform = (string)$attributes->transform;
				}
				else {
					$transform = 'user'; 
				}
				
			    
					$value = $default; 
					//checkbox handling: 
					if ($transform === 'boolval') {
						$value = false; 
					}
					$value = OPCHikaConfig::transform($name, $value, $transform);
				
				
				self::$defaultConfig[$name] = $value; 
			
			}
			
			return self::$defaultConfig;
	}
	
	
	public static function transform($key, $value, $type) {
		
		$safeHtmlFilter = JFilterInput::getInstance(); 
		$type = strtolower($type); 
		switch ($type) {
						case 'boolval': 
						  if (!empty($value)) $value = true; 
						  else $value = false; 
						  break; 
						case 'intval':
						   $value = (int)$value; 
						   break; 
						case 'cmd': 
						  $value = $safeHtmlFilter->clean($value, 'CMD'); 
						  break; 
						case 'user':
						 if (is_array($value)) $value = reset($value); 
						 break; 
						case 'string':
						  $value = $safeHtmlFilter->clean($value, 'STRING'); 
						case 'word':
						  $value = $safeHtmlFilter->clean($value, 'WORD'); 
						 case 'file':
						  jimport( 'joomla.filesystem.file' );
						  $value = JFile::makeSafe($value); 
						  break; 
						 default: 
						   $type = strtoupper($type); 
						   $value = $safeHtmlFilter->clean($value, $type); ; 
						   break; 
					}
				return $value; 
	}
	
	
}
