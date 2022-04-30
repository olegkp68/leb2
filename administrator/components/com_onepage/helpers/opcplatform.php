<?php
// No direct access to this file
defined('_JEXEC') or die;

class OPCplatform {
	public static function isVM() {
		return (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'));
	}
	public static function isHika() {
		static $e; 
		if (isset($e)) return $e; 
		
		$option = JRequest::getVar('option', ''); 
		$view = JRequest::getVar('view', ''); 
		if ($option === 'com_virtuemart') {
			$e = false; 
			return false; 
		}
		if ($view === 'opc') {
			$e = false; 
			return false; 
		}
		
		$e = (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'views'));
		return $e; 
	}
	
	public static function hikaAutoload() {
		

		if (!self::isHika()) return; 
		
		if (defined('AUTOLOADREGISTERED')) return; 
		
		
		if (!defined('OPC_FOR_HIKA_LOADED')) {
				  define('OPC_FOR_HIKA_LOADED', 1); 
				}
		
		define('AUTOLOADREGISTERED', true); 
			spl_autoload_register(function ($class_name) {
				$cn = strtolower($class_name); 
				$cn = JFile::makeSafe($cn); 
				if (substr($cn, 0, 7)==='opchika') {
					$fn = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'hika'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$cn.'.php'; 
					if (file_exists($fn)) {
						require_once($fn);
						return;
					}
				}
				else {
					if (substr($cn, 0, 3)==='opc') {
						
						$class_map = array('opclang'=>'language'); 
						if (isset($class_map[$cn])) {
							$cnX = $class_map[$cn];
							$fn = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$cnX.'.php'; 
							if (file_exists($fn)) {
								require_once($fn);
								return;
							}
						}
						
						
						$cnX = substr($cn, 3); 
						$fn = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$cnX.'.php'; 
						if (file_exists($fn)) {
							require_once($fn);
							return;
						}
						else {
						   $fn = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$cn.'.php'; 	
						   if (file_exists($fn)) {
							require_once($fn);
							return;
						   }
						}
					}
				}
			});
			
	}
	
}