<?php
/**
 * 
 *
 * @package One Page Checkout for VirtueMart
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * 
 */
defined('_JEXEC') or die;
class OPCDbcache {
 static $cache; 
 static $override; 
 public static function set($var, $val) {
	 if (empty(self::$override)) self::$override = array(); 
	 self::$override[$var] = $val; 
 }
 public static function get($var, $default=false)
  {
	 
	  
	 if ((!empty(self::$override)) && (isset(self::$override[$var]))) return self::$override[$var]; 
	  
    if (isset(OPCDbcache::$cache[$var])) return OPCDbcache::$cache[$var]; 
	if (defined('OPCDbcacheLOADED')) {
		if (isset(OPCDbcache::$cache[$var]))
		{
			$default = OPCDbcache::$cache[$var]; 
			return OPCDbcache::$cache[$var]; 
		}
		
		return $default; 
	}
	
	
	define('OPCDbcacheLOADED', 1); 
	
	OPCDbcache::$cache[$var] = $default; 
	
	return $default; 
	
	

  }
  
  
  public static function copy($cache_name, $cache_sub_from, $cache_sub_to)
  {
	  $db = JFactory::getDBO(); 
	  $q = "select `cache_ref`, `value` from `#__onepage_cache` where `cache_name` = '".$db->escape($cache_name)."' "; 
      $db->setQuery($q); 
	  $res = $db->loadAssocList(); 	  
	  if (!empty($res))
	  foreach ($res as $row)
	  {
		  $cache_name = $cache_name; 
		  $cache_sub = $cache_sub_to; 
		  $cache_ref = $row['cache_ref']; 
		  $value = $row['value']; 
		  
		     $datains = $db->escape($value); 
	  	 $q = "insert into `#__onepage_cache` (`id`, `cache_name`, `cache_subname`, `cache_ref`, `value`) values (NULL, '".$db->escape($cache_name)."', '".$db->escape($cache_sub)."', '".(int)$cache_ref."', '".$datains."')  ON DUPLICATE KEY UPDATE value = '".$datains."' ";  

	 
	
	 
     $db->setQuery($q); 
	 $db->execute(); 

	  }
	  
  }
  
  public static function tableExists()
  {
    
	
	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	$ret = OPCmini::tableExists('onepage_cache'); 
	return $ret; 
  }
  public static function clearConfig($cache_name, $cache_sub='', $cache_ref=0)
  {
     if (!self::tableExists()) {
		 self::createTable(); 
	 }
	 
     $db = JFactory::getDBO(); 
	 $q = "delete from #__onepage_cache where cache_name LIKE '".$db->escape($cache_name)."' "; 
	 if (!empty($cache_sub))
	 $q .= " and cache_subname LIKE '".$db->escape($cache_sub)."' "; 
	 
	 
	 //here we got the difference:
	 if (!empty($cache_ref))
	 $q .= " and cache_ref <= ".(int)$cache_ref;
	 

 
	 $db->setQuery($q); 
	 $db->execute(); 
	 
	 
  }
  
  //original: 
  public static function getValueNoCache($cache_name, $cache_sub, $cache_ref, $default=false, $checklang=false)
  { 
  
  if (!self::tableExists()) {
	  self::createTable(); 
	  return $default; 
  }


	if ($checklang)
	 {
	   $cache_sub_orig = $cache_sub; 
	   if (is_bool($checklang))
	   $cache_sub .= JFactory::getLanguage()->getTag(); 
	   else 
	   $cache_sub .= $checklang; 
	 }
	
    $db = JFactory::getDBO(); 
	if (!defined('OPCJ3') || (OPCJ3))
	{
	      $q = "select `value` from #__onepage_cache where cache_name = '".$db->escape($cache_name)."' and cache_subname = '".$db->escape($cache_sub)."' and cache_ref >= ".(int)$cache_ref." limit 0,1"; 

	}
	else
	{
    $q = "select `value` from #__onepage_cache where cache_name = '".$db->escape($cache_name)."' and cache_subname = '".$db->escape($cache_sub)."' and cache_ref >= ".(int)$cache_ref." limit 0,1"; 
	}
	
	$db->setQuery($q); 
	
	$res = $db->loadResult(); 
	
	
	
	if (is_null($res)) 
	{
	  // default language query: 
	  if ($checklang)
	  {
	  $q = "select value from #__onepage_cache where cache_name = '".$db->escape($cache_name)."' and cache_subname = '".$db->escape($cache_sub_orig)."' and cache_ref >= ".(int)$cache_ref." limit 0,1"; 
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
  public static function translateValue($res, $default)
  {
	  
	  
	  if (is_null($res)) return $default; 
	  if ($res === 'true') return true; 
	  if ($res === 'false') return false; 
	  
	  if ($res === '[]') return array(); 
	  
	  
	  //otherwise leave it on the application: 
	  return $res; 
	 
	  
  } 
  public static function getArray($cache_name, $cache_sub='', $cache_ref=0, $default=array()) 
  {
	  $db = JFactory::getDBO(); 
	  $q = "select * from `#__onepage_cache` where `cache_name` = '".$db->escape($cache_name)."' "; 
	  if (!empty($cache_sub))
		  $q .= " and `cache_subname` =  '".$db->escape($cache_sub)."' "; 
	  if (!empty($cache_ref))
		  $q .= " and `cache_ref` >=  '".$db->escape($cache_ref)."' "; 
	  
	  $db->setQuery($q); 
	  $res = $db->loadAssocList(); 
	  
	 
	  if (empty($res)) return $default; 
	  if (!is_array($res)) return $default; 
	  $ret = array();  
	  foreach ($res as $k => $row)
	  {
		  $row = (array)$row; // get rid of any mysql row reference if possible
		  $row['value'] = self::translateValue($row['value'], $default); 
		  $ret[$k] = $row; 
	  }
	 
	  return $ret; 
  }
  public static function getValues($cache_name) {
	  
	  if (!empty(self::$cache[$cache_name])) {
	    {
		  return self::$cache[$cache_name]; 
		}
	
	  }
         $db = JFactory::getDBO(); 
		$q = "select * from #__onepage_cache where cache_name = '".$db->escape($cache_name)."'"; 
		$db->setQuery($q); 
		$results = $db->loadAssocList(); 
		if (empty($results)) $results = array(); 
		$results = (array)$results; 
		return $results; 
  }
  public static function getValue($cache_name, $cache_sub='', $cache_ref=0, $default='', $checklang=false)
  { 
   if (!is_string($cache_sub))
    {
	  $cache_sub = ''; 
	}
   if (!self::tableExists()) {
	   self::createTable(); 
	   return $default; 
   }
    if ($checklang)
	 {
	   $cache_sub_orig = $cache_sub; 
	   if (is_bool($checklang))
	   $cache_sub .= JFactory::getLanguage()->getTag(); 
	   else 
	   $cache_sub .= $checklang; 
	 }
	 
	 $res = null; 
	 
	if (!isset(self::$cache[$cache_name][$cache_sub][$cache_ref]))
	 {
		$results = self::getValueNoCache($cache_name, $cache_sub, $cache_ref); 
	    // fill the cache: 
		

		self::$cache[$cache_name][$cache_sub][$cache_ref] = $results; 
		 
		 
	 }
	 
	   if (isset(self::$cache[$cache_name]))
	   if (isset(self::$cache[$cache_name][$cache_sub]))
	   if (isset(self::$cache[$cache_name][$cache_sub][$cache_ref]))
	   $res = self::$cache[$cache_name][$cache_sub][$cache_ref]; 
	 
	 
  
	if (is_null($res)) 
	{
	  // default language query: 
	  if ($checklang)
	  {
	  
	  
	   if (isset(self::$cache[$cache_name]))
	   if (isset(self::$cache[$cache_name][$cache_sub_orig]))
	   if (isset(self::$cache[$cache_name][$cache_sub_orig][$cache_ref]))
	   $res = self::$cache[$cache_name][$cache_sub_orig][$cache_ref]; 
	  
	  
	  }
	  
	  if (!isset($default)) $default = new stdClass(); 
	  
	  if (is_null($res))
	  return $default; 
	}
	
	return self::translateValue($res, $default); 
	
	
  }

  private static function createTable() {
	   $db = JFactory::getDBO(); 
	$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_cache` (
	`id` int(11) NOT NULL,
	`cache_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	`cache_subname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	`cache_ref` int(11) NOT NULL,
	`value` longtext COLLATE utf8_unicode_ci NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; '; 
   $db->setQuery($q); 
	 $db->execute(); 
  
  try {
  $q = 'ALTER TABLE `#__onepage_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cache_name` (`cache_name`,`cache_subname`,`cache_ref`),
  ADD KEY `cache_name_2` (`cache_name`,`cache_subname`); '; 
	$db->setQuery($q); 
	 $db->execute(); 
  $q = 'ALTER TABLE `#__onepage_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT; '; 
  
   $db->setQuery($q); 
	 $db->execute(); 
  }
  catch (Exception $e) {
	  
  }
	 OPCmini::clearTableExistsCache(); 
  
  }
  
  public static function storeAndClear($cache_name, $cache_sub, $cache_ref=0, &$data) {
	  self::store($cache_name, $cache_sub, $cache_ref, $data); 
	  $db = JFactory::getDBO(); 
	  $cache_ref = (int)$cache_ref; 
	  $cache_ref--; 
	  self::clearConfig($cache_name, $cache_sub, $cache_ref); 
  }
  
  public static function store($cache_name, $cache_sub, $cache_ref=0, &$data)
  {
    if (!self::tableExists()) {
	   self::createTable(); 
	}
	
     $db = JFactory::getDBO(); 
	
	if (isset(self::$cache[$cache_name]))
	unset(self::$cache[$cache_name]); 
	
	 
	
	   $data = (string)$data; 
	   $datains = $db->escape($data); 
	  	 $q = "insert into `#__onepage_cache` (`id`, `cache_name`, `cache_subname`, `cache_ref`, `value`) values (NULL, '".$db->escape($cache_name)."', '".$db->escape($cache_sub)."', '".(int)$cache_ref."', '".$datains."')  ON DUPLICATE KEY UPDATE value = '".$datains."' ";  

	 
	
	 
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
}