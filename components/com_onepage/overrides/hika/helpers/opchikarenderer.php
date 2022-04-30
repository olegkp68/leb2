<?php
defined('_JEXEC') or die('Restricted access');
class OPCHikaRenderer {
	public static $globalVars; 
	
	public static $selected_template; 
	public static function getSelectedTemplate()
	{
		return OPCHikaConfig::get('selected_theme', 'clean_simple2'); 
	}
    public function __construct() {
		
	}
	public static function getInstance() {
		static $renderer; 
		if (isset($renderer)) return $renderer; 
		$renderer = new OPCHikaRenderer(); 
		return $renderer; 
	}
	
	public static function adjustRendered(&$html) {
		
		
		
		$rep = array('Onepage.', 'name="adminForm"', '"showSA', 'javascript: showSA', 'javascript: return op_login', 'return submitenter', 'return op_openlink', 'return changeST', 'return op_unhide(', 'onchange="showFields('); 
		
		$repwith = array('Hikaonepage.', ' id="adminForm" name="adminForm" ', '"Hikaonepage.showSA', 'javascript: Hikaonepage.showSA', 'javascript: return Hikaonepage.op_login', 'return Hikaonepage.submitenter', 'return Hikaonepage.op_openlink', 'return Hikaonepage.changeST', 'return Hikaonepage.op_unhide(', 'onclick="return Hikaonepage.showFields('); 
		if (OPChikaconfig::get('useSSL', false))
			 {
			    $rep[] = 'src="http:'; 
				$repwith[] = 'src="https:';
			 }
		
		$html = str_replace($rep, $repwith, $html); 
		
		OPCHikaUrl::appendFormVars($html); 
		
	}
	
	public function fetchTemplate($template, $vars, $new='') {
		
		
	
		
	$ref = OPChikaRef::getInstance(); 
	$opc_debug_theme = OPChikaconfig::get('opc_debug_theme', false); 
	
 
  
   if (empty(self::$globalVars)) self::$globalVars = array(); 
	
   foreach ($vars as $k=>$v)
   {
	   self::$globalVars[$k] = $v; 
	   if (!empty($k)) {
		   if ($k !== 'selected_template') {
			$this->{$k} = $v; 
		   }
	   }
   }
   if (!empty(self::$globalVars))
   {
	   foreach (self::$globalVars as $k=>$v)
	   {
		   if (!isset($vars[$k])) {
		     $vars[$k] = $v; 
		   }
	   }
	   
   }
   
  
   
   
   
   $selected_template = self::getSelectedTemplate(); 
  
  
   
    $op_shipto_opened = OPChikashipping::getShipToOpened(); 
   
   
   
   OPChikaregistration::setRegType(); 
   
   
    
   
	
   
   
   
   
   if (VM_REGISTRATION_TYPE != 'OPTIONAL_REGISTRATION')
   $op_create_account_unchecked = false; 
   
   if (!empty($ref->cart))
   $cart = $ref->cart; 
   
   
   $op_disable_shipping = OPChikashipping::getShippingEnabled();
   $no_shipping = $op_disable_shipping;
   
   
   $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.$template.'.php'; 
   if (file_exists($f))
    {



	  ob_start(); 
	  extract($vars); 
	  

	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  include($f); 
	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  $ret = ob_get_clean(); 
	  
	  
	  
	  $useSSL = (int)OPChikaconfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
	  
			 
	  return $ret; 
	}
	
	if ($template === 'index') return ''; 
	
   
   $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.$template.'.php'; 
   
	if (($template === 'list_user_fields_registration.tpl') || (($template === 'list_user_fields_shipping.tpl')))
	{
		if (!file_exists($f)) {
		  $template = 'list_user_fields.tpl'; 
		  $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.$template.'.php'; 
		}
	}

   

   if (file_exists($f))
    {
	
	  ob_start(); 
	  extract($vars); 
	  
	  
	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  include($f); 
	  if (!empty($opc_debug_theme)) self::debugTheme($f); 
	  $ret = ob_get_clean(); 
	  
	  
	  
	  $useSSL = OPChikaconfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
			 
			 
	  return $ret; 
	}
   else
    {
	  if (!empty($new))
	   {
	     $ly = $ref->layoutName; 
		 if (empty($ly)) $ly = 'default'; 
		 if (empty($new)) $new = 'prices'; 
	     if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$ly.'_'.$new.'.php'))
		  {
		    ob_start(); 
			$z = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'cart'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$ly.'_'.$new.'.php';
			if (!empty($opc_debug_theme)) self::debugTheme($z); 
			include($z); 
			if (!empty($opc_debug_theme)) self::debugTheme($z); 
			$ret = ob_get_clean(); 
			$useSSL = OPChikaconfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
			return $ret; 
		  }
	     
	   }
	}
	
	
	$f2 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'extra'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'.php'; 
	if (file_exists($f2))
	 {
	 
	     ob_start(); 
	     extract($vars); 
		if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	     include($f2); 
		 if (!empty($opc_debug_theme)) self::debugTheme($f2); 
	     $ret = ob_get_clean(); 
		 $useSSL = OPChikaconfig::get('useSSL', 0);
			if ($useSSL)
			 {
			    $ret = str_replace('src="http:', 'src="https:', $ret); 
			 }
	     return $ret; 
	 }
	}
	
	public static function fetch($template, $data, $new='') {
	
	
	$renderer = self::getInstance(); 
	$html = $renderer->fetchTemplate($template, $data, $new); 
	$html = str_replace('virtuemart', 'hikashop', $html);
	//self::processCustom($template, $html, $data, $new); 
	
	return $html; 
	

	
	}
	
	
 
 
 public static function debugTheme($f) {
	 return; 
 }
 public function op_show_image(&$image, $extra, $width, $height, $type)
	{
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
	  return OPCimage::op_show_image($image, $extra, $width, $height, $type);

	  
	}
 
	
}