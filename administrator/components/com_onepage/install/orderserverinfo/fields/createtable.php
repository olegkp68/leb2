<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
class JFormFieldCreatetable extends JFormField
{
  protected $type = 'createtable';
  function getInput()
	{
		
		if ($this->tableExists('virtuemart_orderserverinfo')) {
			$html = 'Table #__virtuemart_orderserverinfo already created'; 
		}
		else {
	$db = JFactory::getDBO(); 
	  $db = JFactory::getDBO(); 
	  $q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_orderserverinfo` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`virtuemart_order_id` int(1) NOT NULL,
		`_SERVER` json NOT NULL,
		`_POST` json NOT NULL,
		`_GET` json NOT NULL,
		`_COOKIE` json NOT NULL,
		`_EXTRA` json NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `virtuemart_order_id` (`virtuemart_order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'; 
	try { 
		$db->setQuery($q); 
		$db->execute(); 
		
		$html = 'Table #__virtuemart_orderserverinfo created OK'; 
	}
	catch (Exception $e) {
		JFactory::getApplication()->enqueueMessage('plg_vmpayment_orderserverinfo: THIS PLUGIN REQUIRES MYSQL 5.7.8 AND LATER ! DISABLE THE PLUGIN IF YOU ARE NOT USING A COMPATIBLE MYSQL VERSION', 'error'); 
		$html = 'plg_vmpayment_orderserverinfo: THIS PLUGIN REQUIRES MYSQL 5.7.8 AND LATER ! DISABLE THE PLUGIN IF YOU ARE NOT USING A COMPATIBLE MYSQL VERSION';
	}
		}
	return $html; 
	
	}
	 private static function tableExists($table)
  {
   static $cache; 
   
   
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
   
   
   if (empty($cache)) $cache = array(); 
   
   if (isset($cache[$table])) return $cache[$table]; 
   
  
  	
   
   $q = "SHOW TABLES LIKE '".$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   
	   if (empty($cache)) $cache = array(); 
	   
	   if (!empty($r)) 
	    {
		$cache[$table] = true; 
		return true;
		}
		$cache[$table] = false; 
   return false;
  }
	
		
}

