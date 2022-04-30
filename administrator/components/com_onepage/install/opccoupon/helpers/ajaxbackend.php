<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/

if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

class OpccouponAjax extends JPlugin {
	public function __construct(& $subject, $config){
		self::loadVM(); 
		parent::__construct($subject, $config); 
	}
	
	function onAjaxOpccoupon() {
		
		if (!$this->_checkPerm()) {
			echo 'This feature is only available to Super Administrators'; 
			JFactory::getApplication()->close(); 
		}
		
		
		
		ob_start(); 
		$post = JRequest::get('post'); 
		$cmd = JRequest::getWord('cmd'); 
		
		$checkstatus = JRequest::getVar('checkstatus', null); 
		if (!empty($checkstatus)) $cmd .= 'status'; 
		
		if (method_exists($this, 'cmd'.$cmd)) {
			$funct = 'cmd'.$cmd; 
			//$this->$cmd($post); 
		    call_user_func(array($this, $funct), $post); 
		}
		else {
			$this->_die('Command not found'); 
		}
		$html = ob_get_clean(); 
		
		@header('Content-Type: text/html; charset=utf-8');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		echo $html; 
		JFactory::getApplication()->close(); 
		
		
		
	}
	
	private function cmdajaxfieldselect() {
		
		$plugin = JRequest::getWord('plugin'); 
		$manifest = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$plugin.'.xml';
		
		if (file_exists($manifest)) {
			
			$dom = new DomDocument();
			$dom->load($manifest); 
			$xpath = new DOMXPath($dom);
			$elementname = htmlentities(JRequest::getVar('elementname', 'none'));
			
			$nodeList = $xpath->query("//field[contains(@name,'".$elementname."')]"); 
			
			foreach ($nodeList as $node) {
				
				$atr = $node->getAttribute('data-sql'); 
				$query = (string)$atr;
				$defaultlabel = $node->getAttribute('data-defaultlabel'); 
				$defaultvalue = $node->getAttribute('data-defaultvalue'); 
				break;
			}
			
			if (!empty($query)) {
				if (stripos($query, '{VMLANG}')!==false) {
					
					$lang = $this->getCurrentLang(); 
					$lang = strtolower(str_replace('-', '_', $lang)); 
					$query = str_replace('{VMLANG}', $lang, $query); 
					
				}
					$db = JFactory::getDBO(); 
					try { 
					$db->setQuery($query); 
					$res = $db->loadAssocList(); 
					
					
					}
					catch (Exception $e) {
						echo (string)$e; 
						JFactory::getApplication()->close(); 
					}
					$ret = array(); 
					
					if (!empty($defaultlabel)) {
						$ret[$defaultvalue] = $defaultlabel; 
					}
					
					foreach ($res as $row) {
						$id = $row['keyvalue']; 
						$name = $row['keyname']; 
						$ret[$id] = JText::_($name); 
							
							@header('Content-Type: text/html; charset=utf-8');
							@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
							@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

						
					}
					echo json_encode($ret); 
				
			}
		}
		
	}
	
	public function getCurrentLang() {
		if (!self::loadVM()) return; 
		$lang = JFactory::getLanguage()->getTag(); 
		$default_lang = $this->getDefaultLang(); 
		
		
		
		
		//backend override:
		 $lg = JRequest::getVar('vmlang', $lang); 
		 if (empty($lg)) $lg = $lang; 
		 
		 $langs = VmConfig::get('active_languages',array());
         
		 if (!in_array($lg, $langs) ) {
				$lg = $default_lang; 
				return $default_lang; 
		 }
		
		$lg2 = $lg;
		
		
		if ($lg2 === $default_lang) $lg = $default_lang; 
		//if ($lang === $default_lang) return $default_lang; 
		return $lg; 
	}
	
	public function getDefaultLang() {
		$cache; 
		if (!empty($cache)) return $cache; 
		
		$q = 'select `params` from #__extensions where `type` = "component" and `element` = "com_languages"'; 
		$db = JFactory::getDBO(); 
		$db->setQuery($q); 
		$json = $db->loadResult(); 
		$data = json_decode($json, true); 
		if (isset($data['site'])) {
			$retlang = $data['site']; 
			$cache = $retlang; 
			if (!empty($retlang)) return $retlang; 
		}
		
		if (!self::loadVM()) return; 

		if (!isset(VmConfig::$jDefLang)) return JFactory::getLanguage()->getTag();
		
		$cache = VmConfig::$jDefLang;
		return VmConfig::$jDefLang;
	}
	
	private static function loadVM() {
		
		static $run; 
		if (!empty($run)) return true; 
		$run = true; 
		
		/* Require the config */
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php')) return false; 
	
		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		VmConfig::loadConfig();
		if(!class_exists('VmImage')) require(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
		if(!class_exists('shopFunctionsF'))require(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php'); 
		
		if (class_exists('vmLanguage')) {
			if (method_exists('vmLanguage', 'loadJLang')) {
				vmLanguage::loadJLang('com_virtuemart', true);
				vmLanguage::loadJLang('com_virtuemart_orders', true);
				vmLanguage::loadJLang('com_virtuemart_shoppers', true);
			}
		}
		return true; 
		
	}
	
	protected function _checkPerm() {
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