<?php
class ModVirtuemartAjaxSearchProHelper {
	public static function getAjax() {
		if (!self::_checkPerm()) {
			echo 'Access denied to this feature'; 
			JFactory::getApplication()->close(); 
		}
		if (!self::_checkOPC()) {
			echo 'This feature requires com_onepage core classes to be available in /components/com_onepage'; 
			JFactory::getApplication()->close(); 
		}
		
		
		ob_start(); 
		$post = JRequest::get('post'); 
		$cmd = JRequest::getWord('cmd'); 
		
		$checkstatus = JRequest::getVar('checkstatus', null); 
		if (!empty($checkstatus)) $cmd .= 'status'; 
		
		if (method_exists('ModVirtuemartAjaxSearchProHelper', 'cmd'.$cmd)) {
			$funct = 'cmd'.$cmd; 
		    call_user_func(array('ModVirtuemartAjaxSearchProHelper', $funct), $post); 
		}
		else {
			self::_die('Command not found: cmd'.$cmd); 
		}
		$html = ob_get_clean(); 
		
		@header('Content-Type: text/html; charset=utf-8');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		echo $html; 
		JFactory::getApplication()->close(); 
		
	}
	
	private static function _die($msg) {
		echo $msg; 
		JFactory::getApplication()->close(); 
	}
	
	private static  function _checkPerm() {
	  //we send a hash from BE and validate it agains FE
	  $hash = JApplicationHelper::getHash('opc ajax search');
	  
	  $hash_request = JRequest::getVar('hash', ''); 
	  if ($hash === $hash_request) return true; 
	  
	  
	  return false; 
   }
   
   private static function _checkOPC() {
	   if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	     require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		 return true; 
	   }
	   return false; 
   }
   
   private static function cmdproduct_sku_index() {
		echo self::createIndex('product_sku'); 
   }
   private static function cmdproduct_sku_indexstatus() {
	  echo self::checkIndex('product_sku'); 
   }
   
    private static function cmdproduct_customs_index() {
		echo self::createIndex('customfield_value', '#__virtuemart_product_customfields'); 
   }
   private static function cmdproduct_customs_indexstatus() {
	  echo self::checkIndex('customfield_value', '#__virtuemart_product_customfields');
	  /*
	  echo '<br />Some MySQL versions are limited to a maximum length of an indexed column to varchar(255) for utf8mb3 or varchar(191) for utf8mb4. '; 
	  $db = JFactory::getDBO(); 
	  $q = 'select length(customfield_value) as `len` from #__virtuemart_product_customfields order by `len` desc limit 1'; 
	  $db->setQuery($q); 
	  $l = $db->loadResult(); 
	  echo ' Your maximum length of a column value is '.(int)$l.'. You can adjust column definition in your phpMyAdmin, but lowering it to a lower than maximum length will cause lost of data.'; 
	  */
   }
   
   
    private static function cmdproduct_mpn_index() {
		echo self::createIndex('product_mpn'); 
   }
   private static function cmdproduct_mpn_indexstatus() {
	  echo self::checkIndex('product_mpn'); 
   }
    private static function cmdproduct_gtin_index() {
		echo self::createIndex('product_gtin'); 
   }
   private static function cmdproduct_gtin_indexstatus() {
	  echo self::checkIndex('product_gtin'); 
   }
   
   private static function createIndex($index_name, $table='#__virtuemart_products') {
		
		
		if (!OPCmini::hasIndex('#__virtuemart_products', $index_name, true)) {
			
			  $msg = OPCmini::addIndex($table, array($index_name), true); 
			if (!empty($msg)) {
				$msg = '<span style="color:red;">Failed to create Unique index</span>'; 
				if (!OPCmini::hasIndex($table, $index_name, false)) {
				$e = OPCmini::addIndex($table, array($index_name), false); 
				if (!empty($e)) {
						  $db = JFactory::getDBO(); 
						  $q = 'select length('.$index_name.') as `len` from `'.$table.'` order by `len` desc limit 1'; 
						  $db->setQuery($q); 
						  $l = $db->loadResult(); 
						  $msg .= ' Your maximum length of '.$index_name.' column value is '.(int)$l.'. You can adjust column definition in your phpMyAdmin, but lowering it to a lower than maximum length will cause lost of data.'; 
						  $msg .= '<br />'.$e; 
				}
				$msg .= '<br /><span style="color:green;">Non-unique Index created</span>'; 
				}
				else {
					$msg = '<span style="color:green;">Index already exists</span>'; 
				}
			}
			//$msg = '<span style="color:green;">Index already exists</span>'; 
		}
		else {
			$msg = '<span style="color:green;">Index already exists</span>'; 
		}
		
		return $msg; 
		
   }
   private static function checkIndex($index_col, $table='#__virtuemart_products') {
	  if (!OPCmini::hasIndex($table, $index_col, true)) {
		  if (!OPCmini::hasIndex($table, $index_col, false)) {
		     $msg = '<span style="color:red;">Index is not installed</span>'; 
		  }
		  else {
		    $msg = '<span style="color:green;">Index is already created</span>'; 
		  }
		  
	  }
	  else {
		  $msg = '<span style="color:green;">Index is already created</span>'; 
	  }
	  
	  
	 
	  return $msg; 
	   
   }
   
	
}