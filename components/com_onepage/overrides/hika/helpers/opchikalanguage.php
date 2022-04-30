<?php

class OPCHikaLanguage {
	public static $toStore; 
	public static function storeGetMissing($str) {
		
		if (empty(self::$toStore)) self::$toStore = array(); 
		
		if (strpos($str, ' ') !== false) return $str; 
		
		static $arr; 
		if ((!empty($arr)) && (isset($arr[$str]))) return $arr[$str]; 
		
		$ini = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'hikapairing.ini';
		if (file_exists($ini)) {
			$arr = parse_ini_file($ini); 
			if (isset($arr[$str])) return $arr[$str]; 
			
			$data = $str.'="'.htmlentities($str).'"';
			self::$toStore[$str] = $data; 
			$arr[$str] = $str; 
		}
		else {
			$data = $str.'="'.htmlentities($str).'"';
			$arr[$str] = $str; 
			self::$toStore[$str] = $data; 
		}
		
		
		
		return $str; 
	}
	public static function storeINI() {
			$ini = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'hikapairing.ini';
			
			if (!empty(self::$toStore)) {
				$data = implode("\n", self::$toStore)."\n"; 
				if (file_exists($ini)) { 
				  file_put_contents($ini, $data, FILE_APPEND);
				}
				else {
					file_put_contents($ini, $data);
				}
			}
			
	}
	
	
	public static function loadVMLangFiles() {
		static $loaded; 
		if (!empty($loaded)) return; 
		
		$lang = JFactory::getLanguage(); 
		$tag = $lang->getTag(); 
		
		$lang_override = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$tag;
		
		$lang_override_dir = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart';
		$lang_override_dir2 = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart';
		
		if (file_exists($lang_override)) {
		
			$lang->load('com_virtuemart', $lang_override_dir); 
			$lang->load('com_virtuemart', $lang_override_dir2); 
			$lang->load('com_virtuemart_orders', $lang_override_dir); 
			$lang->load('com_virtuemart_shoppers', $lang_override_dir); 
			$lang->load('com_onepage'); 
			
		}
		else {
		
		 $lang->load('com_virtuemart', JPATH_SITE, $tag, true); 
		 $lang->load('com_virtuemart', JPATH_ADMINISTRATOR, $tag, true); 
		 $lang->load('com_virtuemart_orders'); 
		 $lang->load('com_virtuemart_shoppers'); 
		 $lang->load('com_onepage'); 
		}
		$loaded = true; 
		
		
	}
	
}