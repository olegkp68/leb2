<?php

class ModProductcustomsHelper {
	//group_type in #__virtuemart_customs_ext: 
	// = 1 -> group name for the attribute separated by semicolon
	// = 2 -> color code for the attribute
	public static function getAjax() {
		//later: $accepted = array('virtuemart_category_id', 'virtuemart_manufacturer_id', 'virtuemart_custom_id', 'group_name'); 
		$accepted = array('virtuemart_custom_id'); 
		$group_name = ''; 
		$config = null; 
		$ret = array();
		require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
		if (PCH::checkPerm()) {
		    	$post = file_get_contents('php://input');
				$data = json_decode($post, true); 
				
				$data_type = array(); 
				$ids = array(); 
				foreach ($data as $row) {
				   if ($row['type'] === 'group_name') $group_name = $row['value']; 
				   if ($row['type'] === 'config') $config = (array)$row['value']; 
				   if ($row['type'] === 'remove') {
					   $ret['removing...'] = $row['value']; 
					   self::removeItem($row['value'], $ret); 
				   }
					
				   if (in_array($row['type'], $accepted)) {
					   $id = (int)$row['value']; 
					   $ids[$id] = $id; 
					   $data_type = $row['type']; 
				   }
				   
				   
				   $ret[] = $row; 
				}
				
		}
		else {
			
			$ret['error'] = __LINE__; 
			return self::r($ret); 
		
		}
		
		  
		
		
		
		
		
		$db = JFactory::getDBO(); 
		
		
		$done = array(); 
		if (!empty($ids)) {
			if (empty($data_type)) {
			$ret['error'] = __LINE__; 
			return self::r($ret); 
			}
			
			if (empty($group_name)) {
			$ret['post'] = file_get_contents('php://input');; 
			$ret['data'] = $data; 
			$ret['error'] = __LINE__; 
			return self::r($ret); 
		}
			
		$q = 'start transaction'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		
		
		
		$inDB = self::getCurrentData($ids); 
		if (!empty($inDB)) {
		foreach ($inDB as $row) {
			$row['virtuemart_custom_id'] = (int)$row['virtuemart_custom_id'];
			foreach ($ids as $id) {
			  if ($row['virtuemart_custom_id'] === $id) {
				  
				  if (stripos($row['group_value'], ';')!==false) {
				   $group_names = explode(';', $row['group_value']); 
				  }
				  else {
					  $group_names = array($row['group_value']);
				  }
					  
				  
				  $done[$id] = true; 
				  if (in_array($group_name, $group_names)) continue; 
				  $group_names[] = $group_name; 
				  /*
				  $q = 'update #__virtuemart_customs_ext set `group_value` = \''.$db->escape(implode(';', $group_names)).'\' where `virtuemart_custom_id` = '.(int)$id.' and group_type = 1'; 
				  $ret[] = $q; 
				  $db->setQuery($q); 
				  $db->execute(); 
				  */
				  self::updateGroupNames($id, $group_names); 
				  
				  
			  }
			}
		}
		}
		
		
		foreach ($ids as $id) {
			  if (!isset($done[$id]))
			  {
				  $done[$id] = true; 
				  $q = 'insert into #__virtuemart_customs_ext (`virtuemart_custom_id`, `group_value`, `group_type`) values ('.(int)$id.', \''.$db->escape($group_name).'\', 1)'; 
				  $ret[] = $q; 
				  $db->setQuery($q); 
				  $db->execute(); 
				  
				  
			  }
			}
		$q = 'commit'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		$ret['ok'] = true; 
		
		
		}
		
		$ret['ok'] = true; 
		$ret['html'] = self::renderMyModule($config['module_id']); 
		return self::r($ret); 
		
		
		
		
		
		/*
		
		jimport( 'joomla.filesystem.folder' );
			jimport('joomla.filesystem.file');
		if (!file_exists(__DIR__.DIRECTORY_SEPARATOR.'dynamic_config')) {
			JFolder::create(__DIR__.DIRECTORY_SEPARATOR.'dynamic_config'); 
			
		}
		
		$nf_group_names = array(); 
		
		$nf_groups = __DIR__.DIRECTORY_SEPARATOR.'dynamic_config'.DIRECTORY_SEAPRATOR.'group_names.php'; 
		if (file_exists($nf_groups)) include($nf_groups);  //defines: $nf_group_names
		
		$group_name_encoded = urlencode($group_name); 
		$index_id = -1; 
		foreach ($nf_group_names as $ind => $gv) {
			if ($gv === $group_name_encoded) {
				$index_id = $ind; 
				break; 
			}
		}
		if ($index_id < 0) {
			$nf_group_names[] = $group_name_encoded; 
			
			//store the new config: 
			$data = urldecode('%3C%3Fphp').' if( !defined( \'_VALID_MOS\' ) && !defined( \'_JEXEC\' ) ) die( ); '."\n";  
			$data .= ' $nf_group_names = '.var_export($nf_group_names, true).'; '; 
			JFile::write($nf_groups, $data); 
		}
		
		foreach ($nf_group_names as $ind => $gv) {
			if ($gv === $group_name_encoded) {
				$index_id = $ind; 
				break; 
			}
		}
		
		$type_data = array(); 
		$type_dataf = __DIR__.DIRECTORY_SEPARATOR.'dynamic_config'.DIRECTORY_SEAPRATOR.JFile::makeSafe($data_type).'.php'; 
		if (file_exists($nf_groups)) include($type_dataf); //defines $type_data
		
		foreach ($ids as $id) {
			$type_data[$id] = $index_id;
		}
		
		$data = urldecode('%3C%3Fphp').' if( !defined( \'_VALID_MOS\' ) && !defined( \'_JEXEC\' ) ) die( ); '."\n";  
		$data .= ' $type_data = '.var_export($type_data, true).'; '; 
		JFile::write($type_dataf, $data); 
		
		
		
			$ret['error'] = __LINE__; 
			return self::r($ret); 
			
			*/
		
	}
	
	private static function getConfig() {
		$post = file_get_contents('php://input');
				$data = json_decode($post, true); 
				
				$data_type = array(); 
				$ids = array(); 
				foreach ($data as $row) {
					 if ($row['type'] === 'config') {
						 $config = (array)$row['value']; 
						 return $config; 
					 }
					 
				}
				return array(); 
	}
	
	private static function removeItem($item, &$ret=array()) {
		
		$item = (array)$item; 
		$virtuemart_custom_id = (int)$item['id']; 
		if (empty($virtuemart_custom_id)) return; 
		
		$group_name = $item['group_name']; 
		
		$currentData = self::getCurrentData(array($virtuemart_custom_id)); 
		
		$ret['currentData'] = $currentData; 
		
		if (!empty($currentData))
		foreach ($currentData as $row) {
			 if (stripos($row['group_value'], ';')!==false) {
				   $group_names = explode(';', $row['group_value']); 
				  }
				  else {
					  $group_names = array($row['group_value']);
				  }
				  
		   foreach ($group_names as $ind=>$val) {
			   if ($val === $item['group_name']) {
				   unset($group_names[$ind]); 
				   continue; 
			   }
		   }
		   self::updateGroupNames($virtuemart_custom_id, $group_names); 
		   $ret['removeditem'] = $item; 
		   
		}
		
	}
	
	private static function updateGroupNames($id, $group_names=array(), &$ret=array()) {
		$db = JFactory::getDBO(); 
		if (empty($group_names)) {
			$q = 'delete from #__virtuemart_customs_ext where virtuemart_custom_id = '.(int)$id; 
		}
		else {
		$q = 'update #__virtuemart_customs_ext set `group_value` = \''.$db->escape(implode(';', $group_names)).'\' where `virtuemart_custom_id` = '.(int)$id.' and group_type = 1'; 
		}
		$ret[] = $q; 
		
				  $db->setQuery($q); 
				  $db->execute(); 
	}
	private static function getCurrentData($ids) {
		$db = JFactory::getDBO(); 
		$q = 'select `group_value`, `virtuemart_custom_id` from #__virtuemart_customs_ext where `virtuemart_custom_id` IN ('.implode(',', $ids).') and group_type = 1'; 
		
		$db->setQuery($q); 
		$inDB = $db->loadAssocList(); 
		
		return $inDB; 
	}
	
	private static function renderMyModule($id) {
		if (PCH::checkPerm()) {
		$config = self::getConfig(); 
		
		$id = (int)$id; 
		$mod = JModuleHelper::getModule('mod_productcustoms');
		
			
			$mod->id = (int)$mod->id; 
			if ($mod->id === $id) {
			$params = json_decode($mod->params, true); 
			$mod->datacats = $config['datacats']; 
			$mod->getdatacats = $config['getdatacats']; 
			$mod->getget = $config['getget']; 
			$mod->getcustoms = $config['getcustoms']; 
			$mod->getmf = $config['getmf']; 
			$render = JModuleHelper::renderModule($mod, $params );
			return $render;
			}
			
			
		 
		}
		
		return '<notfound></notfound>'; 
	}
	
	public static function r($ret=array('error'=>true))	{
		echo json_encode($ret); 
		JFactory::getApplication()->close(); 
	}
}