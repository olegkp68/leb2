<?php
/**
 * 
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * 
 */
 
 /*
 usage: 
 OPCconfig::set sets temporary override
 OPCconfig::store stores indexed value by 3 columns and possibly language
 OPCconfig::get gets main config from onepage.cfg.php OR opc_vm_config/key/0 
 OPCconfig::save: sets main config (in future will replace onepage.cfg.php
 
 */
 
 
defined('_JEXEC') or die;
class OPCconfig {
 static $config; 
 static $override; 
 public static function set($var, $val) {
	 if (empty(self::$override)) self::$override = array(); 
	 self::$override[$var] = $val; 
 }
 
 //to replace onepage.cfg.php
 public static function save($key, $var, $allow_empty=false) {
	 if (!empty(self::$config['opc_vm_config'])) {
	  self::$config['opc_vm_config'][$key][0] = $var; 
	 }
	 if ($allow_empty) {
		 	 return self::store('opc_vm_config', $key, 0, $var); 
	 }
	 
	 if (empty($var)) {
		 return self::clearConfig('opc_vm_config', $key, 0); 
	 }
	 
	
	 return self::store('opc_vm_config', $key, 0, $var); 
 }
 
 public static function migrateConfig() {
	 if (defined('OPCCONFIGLOADED')) {
		 
		
		 if (!empty(OPCconfig::$config))
		 if (empty(OPCconfig::$config['is_migrated'])) {
			 
			 
			 include(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'); 
			  
			  if (empty(OPCconfig::$config['is_migrated'])) {
			 $arr = get_defined_vars(); 
			
			 unset($arr['opc_vm_config']); 
			 unset($arr['v']); 
			 unset($arr['k']); 
			 unset($arr['val']); 
			 
			 foreach ($arr as $key => $val) {
				 //don't store empty values:
				 if (!empty($val)) {
				   OPCconfig::save($key, $val); 
				 }
				 
				 
			 }
			 
			 self::setMigratedConfig(); 
			  }
		 }
	 }
 }
 
 public static function setMigratedConfig($dobackup = false) {
	 $cf = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'; 
	 $cf_migrated = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.migrated.cfg.php';
	 if ($dobackup) {
		 $cfbackup = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.backup.cfg.php'; 
		 JFile::copy($cf, $cfbackup); 
	 }
	 return JFile::copy($cf_migrated, $cf); 
 }
 
 public static function get($var, $default=false)
  {
	  
	  
	 //self::migrateConfig(); 
	 
	 if ((!empty(self::$override)) && (isset(self::$override[$var]))) return self::$override[$var]; 
	 
	 if (empty(OPCconfig::$config)) OPCconfig::$config = array(); 
    //if (isset(OPCconfig::$config[$var])) return OPCconfig::$config[$var]; 
	
	if (isset(OPCconfig::$config['opc_vm_config'][$var][0])) {
			return OPCconfig::$config['opc_vm_config'][$var][0];
		}
		
		
		
	 
	
	if (defined('OPCCONFIGLOADED')) {
		
		/*
		if (isset(OPCconfig::$config[$var]))
		{
			
			$ret = OPCconfig::$config[$var]; 
			
			return $ret; 
		}
		*/
		return $default; 
	}
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 

	
    if (OPCPlatform::isVM()) {
	  include(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'); 
	  
	  
	  if (empty(OPCconfig::$config['is_migrated'])) {
	  require_once (JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	  
	  /*
	  $selected_template = OPCmini::getSelectedTemplate();  
	  
	  $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'onepage.cfg.php'; 
	   
	   if (file_exists($f))
	   {
		   include($f); 
	   }
	   */
	   if (empty(OPCconfig::$config['opc_vm_config'])) OPCconfig::$config['opc_vm_config'] = array();
	   
	   $arr = get_defined_vars(); 
	   $ign = array('f', 'this', 'default', 'var'); 
	   foreach ($arr as $key=>$val)
	   {
		 if (in_array($key, $ign)) continue; 
		 if (!isset(OPCconfig::$config['opc_vm_config'][$key])) {
			 OPCconfig::$config['opc_vm_config'][$key] = array();
		 }
		 
		 
		 
	     OPCconfig::$config['opc_vm_config'][$key][0] = $val;   
	   }
	   }
	  
		   self::getOverrides(); 
	   
	   
	  	  
	  
	}
	elseif (OPCPlatform::isHika()) 
	{
		
			$hika_config = self::getArray('hika_config'); 
			foreach ($hika_config as $keyH=>$row) {
					$val = $row['value']; 
					$key = $row['config_subname']; 
					OPCconfig::$config[$key] = $val; 
			}
			
			if (empty(OPCconfig::$config['selected_template'])) OPCconfig::$config['selected_template'] = 'clean_simple2'; 
				
			if (!empty(OPCconfig::$config['selected_template'])) {
				 jimport( 'joomla.filesystem.file' );
				$selected_template = JFile::makeSafe(OPCconfig::$config['selected_template']);
				 $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'onepage.cfg.php'; 
	   
			if (file_exists($f))
			{
				include($f); 
			}
			}
			
		
	}
	define('OPCCONFIGLOADED', 1); 
	if (isset(OPCconfig::$config['opc_vm_config'][$var][0])) 
	  {
		  $default = OPCconfig::$config['opc_vm_config'][$var][0]; 
	      return OPCconfig::$config['opc_vm_config'][$var][0]; 
	  }
	  else {
		    static $opc_vm_config; 
			if (empty($opc_vm_config)) {
		     $opc_vm_config = self::getArray('opc_vm_config'); 
			}
			
			
			if (!isset(OPCconfig::$config['opc_vm_config'])) OPCconfig::$config['opc_vm_config'] = array(); 
			
			foreach ($opc_vm_config as $keyH=>$row) {
					$val = $row['value']; 
					$key = $row['config_subname']; 
					OPCconfig::$config['opc_vm_config'][$key] = array(); 
					OPCconfig::$config['opc_vm_config'][$key][0] = $val; 
			}
			
			if (isset(OPCconfig::$config[$var])) 
			{
				$default = OPCconfig::$config['opc_vm_config'][$var][0]; 
				return OPCconfig::$config['opc_vm_config'][$var][0]; 
			}
			
			
	  }
	  
	  
	  
	OPCconfig::$config['opc_vm_config'][$var][0] = $default; 
	
	return $default; 
	// OPCconfig::get is only used for non-database settings
	// for database related stuff use OPCconfig::getValue
	// return self::getValue($var, '', 0, $default); 
	

  }
  
  public static function getOverrides() {
	  
	  
	  
	  $selected_template = OPCmini::getSelectedTemplate();  
	  
	  
	  
	  if (empty(OPCconfig::$override))  OPCconfig::$override = array(); 
	  if (!empty(OPCconfig::$override['loaded_'.$selected_template]))  return; 
	  
	  $f = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'onepage.cfg.php'; 
	   
	   if (file_exists($f))
	   {
		   include($f); 
	   }
	   
	    $arr = get_defined_vars(); 
		$ign = array('f', 'this'); 
	   
	   foreach ($arr as $key=>$val)
	   {
		   
		   if (!in_array($key, $ign)) {
		     OPCconfig::$override[$key] = $val; 
		   }
		 
	   }
	   
	   OPCconfig::$override['loaded_'.$selected_template] = true; 
	  
  }
  
  public static function copy($config_name, $config_sub_from, $config_sub_to)
  {
	  $db = JFactory::getDBO(); 
	  $q = "select `config_ref`, `value` from `#__onepage_config` where `config_name` = '".$db->escape($config_name)."' "; 
      $db->setQuery($q); 
	  $res = $db->loadAssocList(); 	  
	  if (!empty($res))
	  foreach ($res as $row)
	  {
		  $config_name = $config_name; 
		  $config_sub = $config_sub_to; 
		  $config_ref = $row['config_ref']; 
		  $value = $row['value']; 
		  
		     $datains = $db->escape($value); 
	  	 $q = "insert into `#__onepage_config` (`id`, `config_name`, `config_subname`, `config_ref`, `value`) values (NULL, '".$db->escape($config_name)."', '".$db->escape($config_sub)."', '".(int)$config_ref."', '".$datains."')  ON DUPLICATE KEY UPDATE value = '".$datains."' ";  

	 
	
	 
     $db->setQuery($q); 
	 $db->execute(); 

	  }
	  
  }
  
  public static function tableExists()
  {
    static $ret; if (isset($ret)) return $ret; 
	
	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	$ret = OPCmini::tableExists('onepage_config'); 
	return $ret; 
  }
  public static function clear($config_name, $config_sub=0, $config_ref=0) {
	  return self::clearConfig($config_name, $config_sub, $config_ref);
  }
  public static function clearConfig($config_name, $config_sub=0, $config_ref=0)
  {
     if (!self::tableExists()) return; 
	 
     $db = JFactory::getDBO(); 
	 $q = "delete from #__onepage_config where `config_name` LIKE '".$db->escape($config_name)."' "; 
	 if (!empty($config_sub))
	 $q .= " and `config_subname` LIKE '".$db->escape($config_sub)."' "; 
	 if (!empty($config_ref))
	 $q .= " and `config_ref` = ".(int)$config_ref; 
	 $db->setQuery($q); 
	 $db->execute(); 
	 
	 
	 
	 unset(self::$config[$config_name][$config_sub][$config_ref]); 
	 
	 if (empty($config_sub)) unset(self::$config[$config_name]); 
	 else {
	   unset(self::$config[$config_name][$config_sub]); 
	   if ($config_name === 'opc_vm_config') {
	     unset(self::$config[$config_sub]); 
	   }
	 }
 
	 
	 
  }
  
  //original: 
  public static function getValueNoCache($config_name, $config_sub, $config_ref, $default, $checklang=false)
  { 
  if (!self::tableExists()) return $default; 


	if ($checklang)
	 {
	   $config_sub_orig = $config_sub; 
	   if (is_bool($checklang))
	   $config_sub .= JFactory::getLanguage()->getTag(); 
	   else 
	   $config_sub .= $checklang; 
	 }
	
    $db = JFactory::getDBO(); 
	if (!defined('OPCJ3') || (OPCJ3))
	{
	      $q = "select `value` from #__onepage_config where config_name = '".$db->escape($config_name)."' and config_subname = '".$db->escape($config_sub)."' and config_ref = ".(int)$config_ref." limit 0,1"; 

	}
	else
	{
    $q = "select `value` from #__onepage_config where config_name = '".$db->escape($config_name)."' and config_subname = '".$db->escape($config_sub)."' and config_ref = ".(int)$config_ref." limit 0,1"; 
	}
	
	$db->setQuery($q); 
	
	$res = $db->loadResult(); 
	
	
	
	if (is_null($res)) 
	{
	  // default language query: 
	  if ($checklang)
	  {
	  $q = "select value from #__onepage_config where config_name = '".$db->escape($config_name)."' and config_subname = '".$db->escape($config_sub_orig)."' and config_ref = ".(int)$config_ref." limit 0,1"; 
	  $db->setQuery($q); 
	  $res = $db->loadResult(); 
	  }
	  
	  if (!isset($default)) $default = new stdClass(); 
	  
	  if (is_null($res))
	  return $default; 
	}
	
	return self::translateValue($res, $default); 
	
	
	
	
  }
  
  // returns only objects, associative arrays are not supported here
  public static function translateValue($res, $default, $test=array())
  {
	  $res_input = $res; 
	  require_once (JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	  
	  
	  if (($res === 0) && (is_array($default))) return $default; 
	  if (is_null($res)) return $default; 
	  if ($res === 'true') return true; 
	  if ($res === 'false') return false; 
	  
	  if ($res === '') {
		  if (is_string($default)) return ''; 
		  return $default; 
	  }
	  
	  if ($res === '""') {
		  if (is_string($default)) return ''; 
		  return $default; 
	  }
	  if (is_object($res)) {
		  if (is_object($default)) {
		    return $res; 
		  }
	  }
	  if (is_string($res)) {
	  $fl = substr($res, 0, 1); 
	  if (($fl === '{') || ($fl === '[') || ($fl === '"')) {
	  if (is_array($default)) {  
	    $r = @json_decode($res, true); 
	  }
	  else {
		  $r = @json_decode($res); 
	  }
	  
	  
	  if (json_last_error() !== JSON_ERROR_NONE) {
		 
	  }
	  
	  }
	  else {
		  $r = $res;
	  }
	 
	  }
	 
	 if (is_string($r)) {
		$extest = explode('%', $r); 
		$extest2 = explode('+', $r); 
		 if ((count($extest) > 3) || (count($extest2) > 4)) {
			 $r = urldecode($r); 
		 }
		 
		 
	 }
	 elseif (is_array($r)) {
		 foreach ($r as $kx => $line) {
			 if (is_string($line)) {
			 $extest = explode('%', $line); 
			 $extest2 = explode('+', $line); 
			 if ((count($extest) > 3) || (count($extest2) > 4)) {
				$r[$kx] = urldecode($line); 
			 }
			 }
		 }
	 }
	 

	if (is_int($default)) {
		
		return (int)$r; 
	}	 
	if (OPCmini::isInteger($r)) {
		
		
		return (int)$r; 
	}
	
	
	
	  
	if ((empty($r) || ($res=='[]')) && (!empty($default))) 
	{
		
		return $default; 
	}
	else
	if ((empty($r) || ($res=='[]'))) 
	{
		
			  
		
	 if (is_bool($default)) return false; 
	 if (is_array($default)) return $default; 
	 if (empty($default)) return $r; 
	 //$r = new stdClass(); 
	}
	
	
	
	if (empty($r)) {
		return $default; 
	}
	else 
	{
		return $r; 
	}
	
	
	
	return $r; 
	  
  } 
  public static function getArray($config_name, $config_sub=0, $config_ref='', $default=array()) 
  {
	  $db = JFactory::getDBO(); 
	  $q = "select * from `#__onepage_config` where `config_name` = '".$db->escape($config_name)."' "; 
	  if (!empty($config_sub))
		  $q .= " and `config_subname` =  '".$db->escape($config_sub)."' "; 
	  if (!empty($config_ref))
		  $q .= " and `config_ref` =  '".$db->escape($config_ref)."' "; 
	  
	  $db->setQuery($q); 
	  $res = $db->loadAssocList(); 
	  
	 
	 
	  if (empty($res)) return $default; 
	  if (!is_array($res)) return $default; 
	  $ret = array();  
	  foreach ($res as $k => $row)
	  {
		  $row = (array)$row; // get rid of any mysql row reference if possible
		  $row['value'] = self::translateValue($row['value'], $default, $row); 
		  $ret[$k] = $row; 
	  }
	 
	  return $ret; 
  }
  public static function getValues($config_name, $config_sub=0, $config_ref=0) {
	  
	  if (!empty(self::$config[$config_name])) {
	    {
		  return self::$config[$config_name]; 
		}
		/*
		elseif ((!empty($config_sub)) && (is_null($config_ref)))  {
			if (!empty(self::$config[$config_name][$config_sub]))
			{
			  return self::$config[$config_name][$config_sub]; 
			}
			
		}
		elseif ((!empty($config_sub)) && (!is_null($config_ref))) {
			if (!empty(self::$config[$config_name][$config_sub][$config_ref])) {
			  return self::$config[$config_name][$config_sub][$config_ref]; 
			}				
		}
		*/
	  }
         $db = JFactory::getDBO(); 
		$q = "select * from `#__onepage_config` where `config_name` = '".$db->escape($config_name)."'"; 
		$db->setQuery($q); 
		$results = $db->loadAssocList(); 
		if (empty($results)) $results = array(); 
		$results = (array)$results; 
		/*
		if ($config_name === 'opc_config') {
			var_dump($results); 
			die(); 
		}
		if ($config_sub === 'email_fix3') {
			var_dump(self::$config[$config_name]); die(); 
		}
		*/
		
		return $results; 
  }
  public static function getValue($config_name, $config_sub=0, $config_ref=0, $default='', $checklang=false, $notused=false)
  { 
  
  
  
  

   if (empty($config_name)) return $default; 
   if (!is_string($config_sub))
    {
	  $config_sub = ''; 
	}
   if (!self::tableExists()) return $default; 
   
   $config_sub_orig = 0; 
   
    if ($checklang)
	 {
	   $config_sub_orig = $config_sub; 
	   if (empty($config_sub_orig)) $config_sub_orig = 0; 
	   if (is_bool($checklang))
	   $config_sub .= JFactory::getLanguage()->getTag(); 
	   else 
	   $config_sub .= $checklang; 
	 }
	 
	 $res = null; 
	 
	 
	if (empty($config_sub)) $config_sub = 0; 
	 
	if (!isset(self::$config[$config_name]))
	 {
		 
		$results = self::getValues($config_name, $config_sub, $config_ref); 
		
		
		
		
	    // fill the cache: 
		
		
		
		

		if (!empty($results))
		 {
		 
		    foreach ($results as $k=>$row)
			 {
				if (empty($row['config_subname'])) $row['config_subname'] = 0; 
			    //init array: 
			    if (!isset(self::$config[$config_name][$row['config_subname']])) self::$config[$config_name][$row['config_subname']] = array(); 
				
				$row['config_ref'] = (int)$row['config_ref']; 
				
				
				/*
				if ($config_sub === $row['config_subname']) {
			     self::$config[$config_name][$row['config_subname']][$row['config_ref']] = self::translateValue($row['value'], $default); 
				}
				else {
				*/
				 self::$config[$config_name][$row['config_subname']][(int)$row['config_ref']] = $row['value']; 	
				
				
				
				
			 }
		 }
		 
		 
	 }
	 
	 if ($config_name === 'acymailing_fields') {
		// var_dump(self::$config[$config_name]); 
	 }
	 
	 /*
	 if ($config_name === 'acymailing_fields') {
		 var_dump(self::$config[$config_name]); 
		 var_dump($config_sub); 
		 var_dump($default); 
		 var_dump($results); 
		 //var_dump($res); 
		 die(); 
	 }
	 */
	 
	 
	   if (isset(self::$config[$config_name]))
	   if (isset(self::$config[$config_name][$config_sub]))
	   if (isset(self::$config[$config_name][$config_sub][$config_ref])) {
	     $res = self::$config[$config_name][$config_sub][$config_ref]; 
		 
		 $res = self::translateValue($res, $default); 
	   }
	  
	 
	 
  
	if (is_null($res)) 
	{
	  // default language query: 
	  if ($checklang)
	  {
	 
	

	 
	  
	   if (isset(self::$config[$config_name]))
	   if (isset(self::$config[$config_name][$config_sub_orig]))
	   if (isset(self::$config[$config_name][$config_sub_orig][$config_ref])) {
	     
		 $res = self::$config[$config_name][$config_sub_orig][$config_ref]; 
		 $res = self::translateValue($res, $default); 
	   }
	   
	 
	  
	  
	  }
	  
	   
	  
	  if (!isset($default)) $default = new stdClass(); 
	  
	  
	  
	  if (is_null($res))
	  return $default; 
	}
	
	
	
	
	return $res; 
	/*
	if ($res === 'true') return true; 
	if ($res === 'false') return false; 
	
	$r = @json_decode($res); 
	
	
		

	
	if ((empty($r) || ($res=='[]')) && (!empty($default))) return $default; 
	else
	if ((empty($r) || ($res=='[]'))) 
	{
	 if (is_bool($default)) return false; 
	 if (is_array($default)) return array(); 
	 //$r = new stdClass(); 
	}
	
	if (empty($r)) return $default; 
	else return $r; 
	
	return $r; 
	*/
  }


  
  public static function store($config_name, $config_sub, $config_ref=0, $data)
  {
    if (!self::tableExists()) return false; 
     $db = JFactory::getDBO(); 
	
	if (empty($config_sub)) $config_sub = 0; 
	
	if (isset(self::$config[$config_name])) {
		if (!isset(self::$config[$config_name][$config_sub])) self::$config[$config_name][$config_sub] = array();
		self::$config[$config_name][$config_sub][$config_ref] = $data; 
	}
	
	 
	
	 
	   $datains = $db->escape(json_encode($data)); 
	   $q = "insert into `#__onepage_config` (`id`, `config_name`, `config_subname`, `config_ref`, `value`) values (NULL, '".$db->escape($config_name)."', '".$db->escape($config_sub)."', '".(int)$config_ref."', '".$datains."')  ON DUPLICATE KEY UPDATE value = '".$datains."', `config_subname` = '".$db->escape($config_sub)."'";  

	  
	
	 
     $db->setQuery($q); 
	 $db->execute(); 

	 
	
  }
  
  public static function buildObject($post, $key='')
  {
    $ret = new stdClass(); 
	if (!empty($key))
	{
     foreach ($post[$key] as $key2->$val)
	 {
	   $ret->$key2 = $val; 
	 }
	  
	}
	else
    foreach ($post as $key=>$val)
	{
	  $ret->$key = $val; 
	}
	
	
	
	return $ret; 
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