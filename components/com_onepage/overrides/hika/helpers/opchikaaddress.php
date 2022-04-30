<?php
defined('_JEXEC') or die('Restricted access');

class OPChikaAddress {
	public static function setDefaultAddress() {
		
		//TEST !!! : 
		self::getStateList(); 
		
		self::loadDefaultAddress(); 
		self::getAddress(); 
		OPCHikaCache::clear(); 
		
	}
	
	public static function getZoneNameKey($id) {
		
		$id = (int)$id; 
		$db = JFactory::getDBO(); 
		$q = 'select `zone_namekey` from `#__hikashop_zone` where `zone_id` = '.(int)$id.' limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		if (empty($res)) return $id; 
		return $res; 
	}
	
	public static function getHikaUserId($cms_user_id=0) {
		
		$app = JFactory::getApplication(); 
		$user_id = $app->getUserState(HIKASHOP_COMPONENT.'.user_id');
		if (!empty($user_id)) return $user_id; 
		
		$db = JFactory::getDBO(); 
		if (empty($cms_user_id)) $cms_user_id = JFactory::getUser()->get('id', 0); 
		if (!empty($cms_user_id)) {
			$q = 'select `user_id` from #__hikashop_user where cms_user_id = '.(int)$cms_user_id.' limit 1';
			$db->setQuery($q); 
			$user_id = $db->loadResult(); 
			if (!empty($user_id)) return (int)$user_id; 
		}
		$session = JFactory::getSession(); 
		$session_id = $session->getId(); 
		
		$q = "select `cart_billing_address_id`, `cart_shipping_address_ids`, `user_id` from #__hikashop_cart where session_id = '".$db->escape($session_id)."' and cart_type = 'cart' order by cart_modified desc limit 1"; 
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			if (!empty($row)) {
				if (!empty($row['user_id'])) return (int)$row['user_id'];
			}

		
		
	}
	
	public static function loadDefaultAddress() {
		$ref = OPChikaRef::getInstance(); 
		$current_ids = self::getCurrentAddressIDs(); 
		$billing_address_id = $current_ids['billing_address_id']; 
		$shipping_address_id = $current_ids['shipping_address_id']; 
		
		/*
		$ref->cart->cart_billing_address_id = X; 
		$ref->cart->cart_shipping_address_ids = Y; 
		*/
		
		  $address_table = hikashop_table('address'); 
			$db = JFactory::getDBO(); 
		
		$hika_user_id = self::getHikaUserId(); 
		
		
		if (!empty($billing_address_id)) {
			
			    $address_id = (int)$billing_address_id; 
		        $res = self::loadAddress($address_id); 
				
				if (empty($shipping_address_id)) $shipping_address_id = $billing_address_id;
				if (!empty($res)) {
					self::setCurrentBTAddress($res, $address_id); 
				}
			    $res = self::loadAddress($shipping_address_id); 
				
				
				if (!empty($res)) {
					self::setCurrentSTAddress($res, $shipping_address_id); 
				}
				
				
				
		}
		else {
		
		
		
	
		
	
		 {
			
			$session = JFactory::getSession(); 
		    $session_id = $session->getId(); 
			/*
			$q = "select * from #__hikashop_address_opc where session_id = '".$db->escape($session_id)."' and address_type = 'BT'"; 
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			
			
			if (!empty($row)) {
				
				$address_id = (int)$row['address_id']; 
		        $res = self::loadAddress($address_id); 
			   
				if (!empty($res)) {
					self::setCurrentBTAddress($res, $address_id); 
				}
			}
			*/
			
			$cart_id = $ref->cart_id; 
			if (!empty($cart_id)) {
			 $q = "select `cart_billing_address_id`, `cart_shipping_address_ids`, `user_id` from #__hikashop_cart where cart_id = ".(int)$cart_id." and cart_type = 'cart' order by cart_modified desc limit 1"; 
			}
			else {
			 $q = "select `cart_billing_address_id`, `cart_shipping_address_ids`, `user_id` from #__hikashop_cart where session_id = '".$db->escape($session_id)."' and cart_type = 'cart' order by cart_modified desc limit 1"; 
			}
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			if (!empty($row)) {
				
				$address_id = (int)$row['cart_billing_address_id']; 
				if (!empty($address_id)) {
		        $res = self::loadAddress($address_id); 
			   
				if (!empty($res)) {
					self::setCurrentBTAddress($res, $address_id); 
				}
				
				$shipping_address_id = (int)$row['cart_shipping_address_ids']; 
				if (empty($shipping_address_id)) $shipping_address_id = $address_id;
				
				
		        $res = self::loadAddress($shipping_address_id); 
			   
				if (!empty($res)) {
					self::setCurrentSTAddress($res, $shipping_address_id); 
				}
				}
				
			}
			
			if (empty($address_id)) {
					if (!empty($hika_user_id)) {
			
			
		 
		   $q = 'select * from `'.$address_table.'` where address_user_id = '.(int)$hika_user_id.' and address_published = 1 and address_default = 1 limit 1'; 
		   $db->setQuery($q); 
		   $res = $db->loadAssoc(); 
		   
		    if (!empty($res)) {
			   $billing_address_id = (int)$res['address_id']; 
			    self::setCurrentBTAddress($res, $billing_address_id); 
			    self::setCurrentSTAddress($res, $billing_address_id); 
		    }
		   
		}
			}
			
			
		
		}
		}
		
	}
	
	public static function loadAddress($address_id) {
	    $address_table = hikashop_table('address'); 
		$db = JFactory::getDBO(); 
		$q = 'select * from `'.$address_table.'` where `address_id` = '.(int)$address_id.' limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadAssoc(); 
		

	    return $res; 
	}
	
	public static function setCurrentBTAddress($data, $address_id) {
			   $ref = OPChikaRef::getInstance(); 
			   $billing_address_id = (int)$address_id; 
			   $app = JFactory::getApplication('site'); 
			   $app->setUserState(HIKASHOP_COMPONENT.'.'.'billing_address', $billing_address_id);
			   
			   $nA = new stdClass();
			   foreach ($data as $kn=>$kvv) {
				   $nA->{$kn} = $kvv; 
			   }
			   
			   $ref->cart_addresses['data'][$billing_address_id] = $nA; 
			   
			  
			   $ref->cart->billing_address = new stdClass(); 
		       $ref->cart->billing_address->address_id = (int)$billing_address_id;
			   
			   $addressClass = hikashop_get('class.address');
			   $addressClass->get('reset_cache'); 
			   
			   $ref->cartClass->loadAddress($ref->cart, $billing_address_id, 'object', 'billing');
			   
			  
			   
			   
			   $ref->edit_address =& $ref->cart->billing_address;
			   
			   
			   
			   
			   
			   
	}
	
	public static function setCurrentSTAddress($data, $address_id) {
			   $ref = OPChikaRef::getInstance(); 
			   $shipping_address_id = (int)$address_id; 
			   $app = JFactory::getApplication('site'); 
			   $app->setUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', $shipping_address_id);
			   
			   $nA = new stdClass();
			   foreach ($data as $kn=>$kvv) {
				   $nA->{$kn} = $kvv; 
			   }
			   
			   $ref->cart_addresses['data'][$shipping_address_id] = $nA; 
			   
			   $ref->cart->shipping_address = new stdClass(); 
		       $ref->cart->shipping_address->address_id = (int)$shipping_address_id; 
			   
			   $addressClass = hikashop_get('class.address');
			   $addressClass->get('reset_cache'); 
			   
			   $ref->cartClass->loadAddress($ref->cart, $shipping_address_id, 'object', 'shipping');
			  
			   
			   
			   
			   
			   
			   
	}
	
	
	
	
	public static function updateAddress($force_post=false) {
		
		
		
		$ref = OPChikaRef::getInstance(); 
		
		$post = JRequest::get('request'); 
		$allfields = self::getAddressFieldsArray(false); 
		
		$session = JFactory::getSession(); 
		$session_id = $session->getId(); 
		
		$user = JFactory::getUser(); 
		$user_id = $user->get('id');
		
		
		
		$hika_user_id = self::getHikaUserId(); 
		
		
			if (isset($post['email'])) {
			  $email = $post['email']; 
			}
			else {
				$email = JFactory::getUser()->get('email', ''); 
			}
			$joomla_user_id = JFactory::getUser()->get('id', 0); 
			
		
		self::createUpdateHikaUser($email, $joomla_user_id, $hika_user_id); 
		$db = JFactory::getDBO(); 
		
		
		
		
		$current_ids = self::getCurrentAddressIDs($force_post); 
		$billing_address_id = (int)$current_ids['billing_address_id']; 
		
		
		
		$billing_address_post = array(); 
		$billing_address_loaded = array(); 
		
		if (!empty($billing_address_id)) {
			$q = 'select * from #__hikashop_address where `address_id` = '.(int)$billing_address_id.' and (`address_user_id` = 0 or `address_user_id` = '.(int)$hika_user_id.') and address_published = 1 and address_default = 1 limit 1'; 
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			
			foreach ($row as $kn=>$val) {
				$billing_address_loaded[$kn] = $val; 
				$billing_address_post[$kn] = $val; 
			}
		}
		
		foreach ($allfields['op_userfields'] as $field_name) {
		   if (substr($field_name, 0, 7) === 'shipto_') continue; 
		   
		   if (isset($post[$field_name])) {
			 
		     $billing_address_post[$field_name] = $post[$field_name];
			 
			 $get_zone_namekey = array('address_state', 'address_country'); 
			 if (in_array($field_name, $get_zone_namekey)) {
				 $billing_address_post[$field_name] = self::getZoneNameKey((int)$post[$field_name]); 
			 }
			 
			 
		   }
		   else {
			   $billing_address_post[$field_name] = null; 
		   }
		   
		   
		   
		}
		
		
		
		$billing_address_post['address_user_id'] = (int)$hika_user_id; 
		
	
		
		$app = JFactory::getApplication('site'); 
		self::createHikaAddress($billing_address_id, 'BT', $billing_address_post, $billing_address_loaded); 
		if (!empty($billing_address_id)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.'.'billing_address', $billing_address_id);
		}
		self::setCurrentBTAddress($billing_address_post, $billing_address_id); 
		
		$shipto_is_open = OPCHikaShipto::isOpen();
		
		if ($shipto_is_open) {
			
		
		/*shipto address update*/
		$shipping_address_id = (int)$current_ids['shipping_address_id']; 
		if ($shipping_address_id === $billing_address_id) $shipping_address_id = null; 
		
		$shipping_address_post = array(); 
		$shipping_address_loaded = array(); 
		
		if (!empty($shipping_address_id)) {
			$q = 'select * from #__hikashop_address where `address_id` = '.(int)$shipping_address_id.' and (`address_user_id` = 0 or `address_user_id` = '.(int)$hika_user_id.') and address_published = 1 and address_default = 0 limit 1'; 
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			foreach ($row as $kn=>$val) {
				$shipping_address_loaded[$kn] = $val; 
				$shipping_address_post[$kn] = $val; 
			}
		}
		
		foreach ($allfields['op_userfields'] as $field_name) {
		   if (substr($field_name, 0, 7) !== 'shipto_') continue; 
		   
		   $field_name_key = substr($field_name, 7); 
		   if (empty($field_name_key)) continue; 
		   
		   if (isset($post[$field_name])) {
			 
		     $shipping_address_post[$field_name_key] = $post[$field_name];
			 
			 $get_zone_namekey = array('address_state', 'address_country'); 
			 if (in_array($field_name_key, $get_zone_namekey)) {
				 $shipping_address_post[$field_name_key] = self::getZoneNameKey((int)$post[$field_name]); 
			 }
			 
			 
		   }
		   else {
			  
			   $shipping_address_post[$field_name_key] = null; 
		   }
		}
		
		if (!empty($shipping_address_post)) {
		
		$shipping_address_post['address_user_id'] = (int)$hika_user_id; 
		
		
		$app = JFactory::getApplication('site'); 
		self::createHikaAddress($shipping_address_id, 'ST', $shipping_address_post, $shipping_address_loaded); 
		if (!empty($shipping_address_id)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', $shipping_address_id);
			if ($shipping_address_id !== $billing_address_id) {
			 self::setCurrentSTAddress($shipping_address_post, $shipping_address_id); 
			}
		}
		
		
		}
		else {
			$shipping_address_id = 0; 
		}
		
		}
		else {
			$shipping_address_id = $billing_address_id;
			self::setCurrentSTAddress($billing_address_post, $billing_address_id); 
		}
		
		if (empty($shipping_address_id)) $shipping_address_id = $billing_address_id;
		
		$cart_id = $ref->checkoutHelper->getCartId();
		self::updateAddressesCart($cart_id, $billing_address_id, $shipping_address_id); 
		OPCHikaCache::clear(); 
		
		
		
		
	}
	public static function updateAddressesCart($cart_id, $bill_id, $shipto_id) {
		$db = JFactory::getDBO(); 
		$q = 'update `#__hikashop_cart` set `cart_billing_address_id` = '.(int)$bill_id.', `cart_shipping_address_ids` = '.(int)$shipto_id.' where `cart_id` = '.(int)$cart_id; 
		$db->setQuery($q); 
		$db->execute(); 
	}
	
	public static function createUpdateHikaUser($email='', &$joomla_user_id=0, &$hika_user_id=0) {
		
		$hika_user_id = (int)$hika_user_id; 
		$joomla_user_id = (int)$joomla_user_id; 
		$email_orig = $email; 
		//create a temporary record with session_id@domain
		if (empty($email)) {
		if (!empty($_SERVER['HTTP_HOST'])) {
		  $domain = $_SERVER['HTTP_HOST']; 
		}
		else 
		if (!empty($_SERVER['SERVER_NAME'])) {
		  $domain = $_SERVER['SERVER_NAME']; 
		}
		else {
			$domain = 'localhost'; 
		}
		$session = JFactory::getSession(); 
		$session_id = $session->getId(); 
		$email = $session_id.'@'.$domain; 
		}
		
		$db = JFactory::getDBO(); 
		if (empty($joomla_user_id)) {
		$joomla_user_id = JFactory::getUser()->get('id', 0); 
		}
		if (empty($hika_user_id)) {
			$q = "select * from #__hikashop_user where `user_email` = '".$db->escape($email)."' and `user_cms_id` = ".(int)$joomla_user_id." limit 1"; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
		}
		else {
			$q = "select * from #__hikashop_user where `user_id` = ".(int)$hika_user_id.' limit 1'; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
		}
		
		
		
		$toUpdate = $res; 
		if (empty($res)) {
			$q = 'select * from #__hikashop_user where 1=1 limit 1'; 
			$db->setQuery($q); 
			$r = $db->loadAssoc(); 
			
			$toUpdate = $r; 
			
			//any non system fields in this table will receive zero: 
			foreach ($r as $row_name=>$val) {
				$toUpdate[$row_name] = 0; 
			}
			
			$toUpdate['user_id'] = null; 
			$toUpdate['user_cms_id'] = $joomla_user_id; 
			$toUpdate['user_email'] = $email; 
			$toUpdate['user_partner_email'] = ''; 
			$toUpdate['user_params'] = '';
			$toUpdate['user_partner_id'] = 0;
			$toUpdate['user_partner_price'] = 0;
			$toUpdate['user_partner_paid'] = 0;
			$toUpdate['user_created_ip'] = hikashop_getIP();
			$toUpdate['user_unpaid_amount'] = 0;
			$toUpdate['user_partner_currency_id'] = 0;
			$toUpdate['user_created'] = time();
			$toUpdate['user_currency_id'] = 0;
			$toUpdate['user_partner_activated'] = 0;
			
			
			
		}
		
		if (!empty($toUpdate['user_id'])) {
		 $where = array(); 
		 $where['user_id'] = $toUpdate['user_id']; 
		 
		 $toUpdate['user_id'] = (int)$toUpdate['user_id']; 
		}
		else {
			$where = array(); 
		}
		
		
	    $toUpdate['user_cms_id'] = (int)$toUpdate['user_cms_id']; 
		if (!empty($email_orig)) {
		 $toUpdate['user_email'] = $email; 		
		}
				 
		
		if (($toUpdate['user_email'] === $email) && ($toUpdate['user_id'] === $hika_user_id) && ($toUpdate['user_cms_id'] === $joomla_user_id)) {
			$hika_user_id = $toUpdate['user_id'];
		}
		
		if ((!empty($where)) || (empty($toUpdate['user_id']))) {
		 $table = '#__hikashop_user'; 
		 $hika_user_id = (int)OPChikadb::insertUpdateArray($table, $toUpdate, $where); 
		}
		
		if (!empty($hika_user_id)) {
		  $app = JFactory::getApplication(); 
		  $app->setUserState(HIKASHOP_COMPONENT.'.user_id', $hika_user_id);
		}
	
		
		
	}
	public static function createHikaAddress(&$current_address_id=0, $type='BT', $address_post=array(), $address_loaded=array()) {
		$address_table = hikashop_table('address'); 
		
		//system fields management: 
		if ($type === 'BT') {
		  $address_post['address_type'] = ''; 
		  $address_post['address_default'] = 1; 
		}
		elseif ($type === 'ST') {
			$address_post['address_type'] = 'shipping'; 
			$address_post['address_default'] = 0; 
		}
		$address_post['address_published'] = 1; 
		
		
		if (!empty($address_loaded)) {
			$user_id = (int)$address_loaded['address_user_id']; 
			$address_id = (int)$address_loaded['address_id']; 
			
			
			$where = array(); 
			$where['address_id'] = $address_id;
			
			$current_address_id = (int)OPChikadb::insertUpdateArray($address_table, $address_post, $where); 
			 
		}
		else 
		{
			$where = array(); 
			$current_address_id = (int)OPChikadb::insertUpdateArray($address_table, $address_post, $where); 
		}
		
		
		
		
		$user_id = JFactory::getUser()->get('id', 0); 
		if ((!empty($user_id)) && (empty($address_post['user_id']))) {
		  $address_post['user_id'] = (int)$user_id; 
		}
		
		/*
		$session = JFactory::getSession(); 
		$session_id = $session->getId(); 
		
		$opc_table = '#__hikashop_address_opc'; 
		$db = JFactory::getDBO(); 
		$q = "select * from `#__hikashop_address_opc` where `session_id` = '".$db->escape($session_id)."' and `address_type` = '".$db->escape($type)."' limit 1"; 
		$db->setQuery($q); 
		$data = $db->loadAssoc(); 
		
		$where = array(); 
		if (empty($data)) {
			$data = array(); 
			$data['id'] = null; 
			$where['session_id'] = $session_id; 
		}
		else {
			$where['id'] = (int)$data['id']; 
		}
		
		$data['address_type'] = $type; 
		$data['address_id'] = (int)$current_address_id; 
		$data['modified_on'] = time(); 
		$data['user_id'] = (int)JFactory::getUser()->get('id', 0); 
		$data['hika_user_id'] = self::getHikaUserId(); 
		$data['session_id'] = $session_id; 
			
		
		
		OPChikadb::insertUpdateArray($opc_table, $data, $where); 
		*/
		
	}
	
	public static function getBTAddressFields() {
		
		$ref = OPChikaRef::getInstance(); 
		$rowFieldsHika = $ref->cart_addresses['fields']; 
		
		
		
		$rowFields = array(); 
		$rowFields['fields'] = array(); 
		foreach ($rowFieldsHika as $kk=>$field) {
			
			
			if (empty($field)) continue; 
			if (substr($field->field_namekey, 0, 7) === 'shipto_') continue; 
			
			$row = array();
			foreach ($field as $fk=>$fv) {
				$row[$fk] = $fv; 
			}
			$row['type'] = $row['field_type']; 
			$row['ready_only'] = false; 
			$row['required'] = $row['field_required']; 
			$row['title'] = $row['field_realname']; 
			$row['name'] = $row['field_namekey']; 
			$row['published'] = $row['field_published']; 
			
			//$row['formcode'] = str_replace('{prefix}', '', $row['formcode']); 
			
			$name = $row['name']; 
			 
			if (empty($name)) continue; 
			$rowFields['fields'][$name] = $row; 
			
			
		}
		
		return $rowFields; 
		
		
	}
	
	
	public static function getSTAddressFields() {
		
		$ref = OPChikaRef::getInstance(); 
		$rowFieldsHika = $ref->cart_addresses['fields']; 
		
		
		
		$rowFields = array(); 
		$rowFields['fields'] = array(); 
		foreach ($rowFieldsHika as $kk=>$field) {
			
			if (empty($field)) continue; 
			if (substr($field->field_namekey, 0, 7) !== 'shipto_') continue; 
			
			$row = array();
			foreach ($field as $fk=>$fv) {
				$row[$fk] = $fv; 
			}
			$row['type'] = $row['field_type']; 
			$row['ready_only'] = false; 
			$row['required'] = $row['field_required']; 
			$row['title'] = $row['field_realname']; 
			$row['name'] = $row['field_namekey']; 
			$row['published'] = $row['field_published']; 
			//$row['formcode'] = str_replace('{prefix}', 'shipto_', $row['formcode']); 
			$name = $row['name']; 
			 
			if (empty($name)) continue; 
			$rowFields['fields'][$name] = $row; 
			
			
		}
		
		return $rowFields; 
		
		
	}
	
	public static function getBTAddressFieldsHTML() {
		$ref = OPChikaRef::getInstance(); 
		$rowFields = self::getBTAddressFields(); 
		$unlg = OPChikauser::logged(); 
		
		$op_create_account_unchecked = OPChikaConfig::get('op_create_account_unchecked', false); 
		
		$vars = array(
		'rowFields' => $rowFields, 
		'rowFields_st' => $rowFields, 
		'cart' => $ref->cart, 
		'opc_logged' => $unlg,
		'op_create_account_unchecked' => $op_create_account_unchecked,
		);
		
		$html = OPChikarenderer::fetch('list_user_fields.tpl', $vars); 
		
		return $html;  
	}
	
	public static function getDefaultCountry() {
		$country_id = (int)hikashop_getZone('shipping'); 
		return $country_id; 
	}
	public static function getAddress() {
		self::getAddressFieldsBT(); 
		self::getAddressFieldsST(); 
	}
	
	public static function getAddressFieldsBT() {
		$ref = OPChikaRef::getInstance(); 
		
		
		if (!empty($ref->fields_done )) return; 
		
		$step = 1; 
		$module_position = 'opc'; 
		
		$current_ids = self::getCurrentAddressIDs(); 
		
		
		$billing_address_id = $current_ids['billing_address_id']; 
		$shipping_address_id = $current_ids['shipping_address_id']; 
		
		if (isset($ref->cart->billing_address)) {
		 $edit_address = $ref->cart->billing_address;
		}
		elseif (isset($ref->edit_address)) {
			$edit_address = $ref->edit_address;
		}
		
		$opc_ajax_fields = OPCHikaconfig::get('opc_ajax_fields', array()); 
		
		
		foreach($ref->cart_addresses['fields'] as $kk=> $field) {
			if(empty($field->field_frontcomp))
				continue;
			
			$classname = ''; 
			$title = $ref->fieldClass->getFieldName($field, true, $classname);
			$field->title = $title; 
			$value = ''; 
			
			$fieldname = $field->field_namekey;
			
			
			
			
			$countryType = $countryType = hikashop_get('type.country'); 
				if (!empty($edit_address->address_country)) 
				{	
					if (is_object($edit_address->address_country)) {
						$country_id = $edit_address->address_country->zone_id;
					}
					elseif (is_numeric($edit_address->address_country)) {
					 $country_id = $edit_address->address_country;
					}
					
					
				}
				if (empty($country_id)) {
				 $country_id =  self::getDefaultCountry(); 
				}
			
			
			
			if ($fieldname === 'address_country') {
				
				
				$options = ' onchange="javascript: Hikaonepage.op_validateCountryOp2(false, false, this);" autocomplete="country" class="country_select" '; 
				$countryType->prefix = ''; 
			    $formcode = $countryType->display($fieldname, $country_id, false, $options, 'address_country_field'); 
			}
			elseif ($fieldname === 'address_state') {
				$countryType = $countryType = hikashop_get('type.country'); 
				
				$options = ' onchange="javascript:Hikaonepage.op_runSS(this);" autocomplete="state" class="state_select" '; 
				$key = 0; 
				
				$formcode = self::listStatesHTML($country_id, 'address_state', $options); 
				
				
				
			}
			else {
			
			$field->table_name = 'order';
			$field->value = null; 
			
			if (isset($ref->cart_addresses['data'][$billing_address_id]->$fieldname)) {
				$field->value = $ref->cart_addresses['data'][$billing_address_id]->$fieldname;
			}
			
			$extraCode = ''; 
			
			
			
			if (in_array($fieldname, $opc_ajax_fields)) {
				$extraCode = ' onchange="javascript:Hikaonepage.op_runSS(this);" '; 
				
				
			}
			
		    $ref->fieldClass->prefix = ''; 
			$formcode = $ref->fieldClass->display(
				$field,
				$field->value,
				$fieldname,
				false,
				' class="opcfield" '.$extraCode,
				false,
				$ref->cart_addresses['fields'],
				$edit_address,
				false
			);
			
			}
			
			
			$field->formcode = $formcode; 
			$ref->cart_addresses['fields'][$kk] = $field; 
		}
		
		$ref->fields_done = true; 
		if (isset($ref->cart->billing_address)) {
		 $ref->cart->billing_address = $edit_address; 
		}
		elseif (isset($ref->edit_address)) {
			$ref->edit_address = $edit_address;
		}
		
		
	}
	
	
	public static function getAddressFieldsST() {
		
		$ref = OPChikaRef::getInstance(); 
		
		
		if (!empty($ref->fields_done_st )) return; 
		
		
		
		$step = 1; 
		$module_position = 'opc'; 
		
		$current_ids = self::getCurrentAddressIDs(); 
		$billing_address_id = $current_ids['billing_address_id']; 
		$shipping_address_id = $current_ids['shipping_address_id']; 
		
		if (isset($ref->cart->shipping_address)) {
		 $edit_address = $ref->cart->shipping_address;
		 
		}
		elseif (isset($ref->edit_address)) {
			$edit_address = $ref->edit_address;
		}
		
		$nf = array(); 
		foreach ($ref->cart_addresses['fields'] as $kk => $field) {
			//note, adding new entries to the current iterated object gives mem leak on php55 and php56
			$nf['shipto_'.$kk] = clone $field; 
		}
		$ref->cart_addresses['fields'] = array_merge($ref->cart_addresses['fields'], $nf); 
		$opc_ajax_fields = OPChikaconfig::get('opc_ajax_fields', array()); 
		
		
		foreach($ref->cart_addresses['fields'] as $kk=> $field) {
			
			
			
			
			if (substr($kk, 0, 7) !== 'shipto_') continue; 
			
			if(empty($field->field_frontcomp))
				continue;
			
			$classname = ''; 
			$title = $ref->fieldClass->getFieldName($field, true, $classname);
			$field->title = $title; 
			$value = ''; 
			
			if (substr($field->field_namekey, 0, 7) !== 'shipto_') {
			 $field->field_namekey = 'shipto_'.$field->field_namekey;
			}
			$fieldname = $field->field_namekey;
			
			
			$fieldname_original = substr($fieldname, 7); 
				$extraCode = ''; 
			if (in_array($fieldname_original, $opc_ajax_fields)) {
				$extraCode = ' onchange="javascript:Hikaonepage.op_runSS(this);" '; 
			}
			
			
			$countryType = $countryType = hikashop_get('type.country'); 
				if (!empty($edit_address->address_country)) 
				{	
					if (is_object($edit_address->address_country)) {
						$country_id = $edit_address->address_country->zone_id;
					}
					elseif (is_numeric($edit_address->address_country)) {
					 $country_id = $edit_address->address_country;
					}
					
					
				}
				if (empty($country_id)) {
				 $country_id =  self::getDefaultCountry(); 
				}
			
			
			
			if ($fieldname === 'shipto_address_country') {
				
				
				$options = ' onchange="javascript: Hikaonepage.op_validateCountryOp2(false, false, this);" autocomplete="country" class="country_select" '; 
				$countryType->prefix = ''; 
			    $formcode = $countryType->display($fieldname, $country_id, false, $options, 'shipto_address_country_field'); 
			}
			elseif ($fieldname === 'shipto_address_state') {
				$countryType = $countryType = hikashop_get('type.country'); 
				
				$options = ' onchange="javascript:Hikaonepage.op_runSS(this);" autocomplete="state" class="state_select" '; 
				$key = 0; 
				
				$formcode = self::listStatesHTML($country_id, 'shipto_address_state', $options); 
				
				
				
			}
			else {
			
			$field->table_name = 'order';
			$field->value = null; 
			
			if (isset($ref->cart_addresses['data'][$billing_address_id]->$fieldname)) {
				$field->value = $ref->cart_addresses['data'][$billing_address_id]->$fieldname;
			}
		    $ref->fieldClass->prefix = ''; 
			$formcode = $ref->fieldClass->display(
				$field,
				$field->value,
				$fieldname,
				false,
				' class="opcfield" '.$extraCode,
				false,
				$ref->cart_addresses['fields'],
				$edit_address,
				false
			);
			
			}
			
		    $field->formcode = $formcode; 
			$ref->cart_addresses['fields'][$kk] = $field; 
			
			
			
		}
		
		$ref->fields_done = true; 
		if (isset($ref->cart->shipping_address)) {
		 $ref->cart->shipping_address = $edit_address; 
		}
		elseif (isset($ref->edit_address)) {
			$ref->edit_address = $edit_address;
		} 
		
		
		$ref->fields_done_st = true; 
		
		
		
	}
	
	
	public static function getCurrentAddressIDs($force_post=false) {
		
		$app = JFactory::getApplication(); 
		
		
		$ref = OPChikaref::getInstance(); 
		if (empty($ref->cart)) {
		  $cart = OPChikaRef::getCart();  	
		}
		else {
		 $cart = $ref->cart; 
		}
		
		$billing_address_id = 0; 
		$shipping_address_id = 0; 
		
		if(!empty($cart)) {
		 if (is_array($cart->cart_shipping_address_ids)) {
			 $shipping_address_id = (int)reset($cart->cart_shipping_address_ids); 
		 }
		 else {
		   $shipping_address_id = (int)$cart->cart_shipping_address_ids;
		 }
		}
		if (empty($shipping_address_id)) {
		 $shipping_address_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);
		}
		
		if(!empty($cart)) {
		 $billing_address_id = (int)$cart->cart_billing_address_id;
		}
		if (empty($billing_address_id)) {
		  $billing_address_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.'billing_address', 0);
		}
		
		$cart_id = $ref->cart_id; 
		$db = JFactory::getDBO(); 
		if ((empty($billing_address_id)) || (empty($shipping_address_id))) {
			
		 if (empty($cart_id)) {
		   $q = "select `cart_billing_address_id`, `cart_shipping_address_ids`, `user_id` from #__hikashop_cart where `session_id` = '".$db->escape($session_id)."' and `cart_type` = 'cart' order by `cart_modified` desc limit 1"; 
		 }
		 else {
			 $q = "select `cart_billing_address_id`, `cart_shipping_address_ids`, `user_id` from #__hikashop_cart where `cart_id` = ".(int)$cart_id." and `cart_type` = 'cart' order by `cart_modified` desc limit 1"; 
		 }
		 $db->setQuery($q); 
		 $row = $db->loadAssoc(); 
		 if ((empty($billing_address_id)) && (!empty($row['cart_billing_address_id']))) {
			 $billing_address_id = (int)$row['cart_billing_address_id']; 
		 }
		 if ((empty($shipping_address_id)) && (!empty($row['cart_shipping_address_ids']))) {
			 $shipping_address_id = (int)$row['cart_shipping_address_ids']; 
		 }
		 
		}
		
		if (!empty($force_post)) {
			$isOpen = OPCHikaShipTo::isOpen(); 
			if (empty($isOpen)) {
				$shipping_address_id = $billing_address_id;
			}
		}
		
		if (empty($shipping_address_id)) $shipping_address_id = $billing_address_id; 
		
		return array('billing_address_id'=>$billing_address_id, 'shipping_address_id'=>$shipping_address_id); 
	}
	
	public static function getAddressFieldsArray($regToFields=true) {
		
		$ref = OPChikaRef::getInstance(); 
		
		if (empty($ref->fields_done )) {
			self::getAddress();
		}
		
		$op_userfields = array(); 
		$op_userfields_named = array(); 
		$shipping_obligatory_fields = array(); 
		$custom_rendering_fields = array(); 
		$business_fields = array(); 
		$business_fields2 = array(); 
		$business_selector = ''; 
		$is_business2 = false; 
		$opc_ajax_fields = OPChikaconfig::get('opc_ajax_fields', array('address_country', 'address_state', 'address_zip')); 
		
		
		$registration_fields = OPChikaregistration::getRegistrationFields(); 
		$op_registraton_fields = array(); 
		
		if ($regToFields) {
		foreach ($registration_fields['fields'] as $field) {
			
			$fieldX = (array)$field; 
			
			$fn = $fieldX['field_namekey']; 
			
			$op_userfields[$fn] = $fn; 
			$op_userfields_named[$fn] = $fieldX['field_realname']; 
			$op_registraton_fields[$fn] = $fn; 
			
			
		}
		}
		
		
		
		foreach ($ref->cart_addresses['fields'] as $ind=>$field) {
			
			$fn = $field->field_namekey; 
			$op_userfields[$fn] = $fn; 
			$op_userfields_named[$fn] = $field->field_realname; 
			
			
		}
		
		
		return array('op_userfields'=>$op_userfields, 'op_userfields_named'=>$op_userfields_named, 'shipping_obligatory_fields'=>$shipping_obligatory_fields, 'custom_rendering_fields'=>$custom_rendering_fields, 'business_fields'=>$business_fields, 'business_fields2'=>$business_fields2, 'business_selector'=>$business_selector,'is_business2'=>$is_business2, 'opc_ajax_fields'=>$opc_ajax_fields, 'op_registraton_fields'=>$op_registraton_fields ); 
	}
	
 public static $allZoneData; 
 public static $allCountries; 
 
 public static function getStateList(&$countries=array(), &$allData=array())
 {
	
	  
	  $db = JFactory::getDBO(); 
	 
	   $q = 'select z.zone_id, z.zone_namekey, z.zone_name, z.zone_name_english, z.zone_code_2, z.zone_code_3, l.zone_parent_namekey, c.zone_id as country_zone_id, c.zone_namekey as country_zone_namekey, c.zone_name as country_zone_name, c.zone_name_english as country_zone_name_english, c.zone_code_2 as country_zone_code_2, c.zone_code_3 as country_zone_code_3 from #__hikashop_zone as z left join #__hikashop_zone_link as l on z.zone_namekey = l.zone_child_namekey left join #__hikashop_zone as c on c.zone_namekey = l.zone_parent_namekey where z.zone_published = 1 and z.zone_type = \'state\''; 
	   $db->setQuery($q); 
	   $allData = (array)$db->loadAssocList(); 
	   
	
	   
	   
	  
	   $q = 'select sum(z.zone_id) from #__hikashop_zone as z  where z.zone_published = 1 limit 1';
	   $db->setQuery($q); 
	   $myhash = (int)$db->loadResult(); 
	   
	   self::$allZoneData = $allData; 
	   

	$lang = JFactory::getLanguage()->getTag(); 
	
	$js_filename = 'hikaopc_states_'.$lang.'_'.$myhash.'.js'; 
	$js_file = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'dynamic_scripts'.DIRECTORY_SEPARATOR.$js_filename; 
	$js_path = 'components/com_onepage/config/dynamic_scripts/'; 
	
	
	if (file_exists($js_file))
	{
	  JHTMLOPC::script($js_filename, $js_path); 
	}
	else
	{
	   
	
	
	
	
	
	$js = ' var HikaOPCStates = { '."\n";  
	
	
	
	  
	
	$list = self::getCountries(); 
	
	self::$allCountries = $list; 
	
	$countries = array();
    $states = array(); 	
	
	
	
	$cs = '';  
	$counts = count($list); 
	$ci =0; 
	
	foreach ($list as $c)
	{
	  $country_id = (int)$c['zone_id']; 
	  $ci++; 
	  $states[$country_id] = self::getStatesFromAlldata( $country_id, $allData); 

	  if (!empty($states[$country_id])) 
	  {
	  
	  $js .= ' state_for_'.$country_id.': { '."\n"; 
	  
	  $counts2 = count($states[$country_id]); 
	  $ci2 =0; 
	  
	  foreach ($states[$country_id] as $state)
	   {
	   
	     
	     $ci2++; 
	      //$js .= ' state_for_'.$c->virtuemart_country_id.'['.$state->virtuemart_state_id.']: "'.str_replace('"', '\"', $state->state_name).'",'."\n"; 
		  $js .= $state->state_id.': "'.str_replace('"', '\"', $state->state_name).'"'; 
		  if ($ci2 != $counts2) $js .= ', '; 
		  $js .= "\n"; 
	   }
	   $js .= ' }'; 
	   if ($ci != $counts) $js .= ', ';
	   $js .= "\n"; 
	 
	  }
	 

	  
	  
	}
	
	
	$js .= ' }; 
	
	
	'; 
	
	$html = '<div style="display: none;">'; 
	$html .= '<select id="no_states" name="no_states">'; 
	$html .= '<option value="">'.OPCLang::_('COM_VIRTUEMART_LIST_EMPTY_OPTION').'</option>'; 
	$html .= '</select>'; 
	$html .= '</div>'; 
	
	/*
	if (!empty($ref->cart->BT) && (!empty($ref->cart->BT['virtuemart_state_id'])))
	$cs = $ref->cart->BT['virtuemart_state_id']; 
	else $cs = '';  
	
	if (!empty($ref->cart->ST) && (!empty($ref->cart->ST['virtuemart_state_id'])))
	$css = $ref->cart->ST['virtuemart_state_id']; 
	else $css = '';  
	*/
	
	$cs = $css = ''; 
	
	$html .= '<script type="text/javascript">
	var selected_bt_state = \''.$cs.'\';
	var selected_st_state = \''.$css.'\';
	
	</script>'; 
	
	
	
	
			jimport( 'joomla.filesystem.folder' );
			jimport( 'joomla.filesystem.file' );
			
			 $rrand = rand(); 
			 if (JFile::write($js_file.'.'.$rrand.'.tmp', $js) !== false)
			 {
				 JFile::move($js_file.'.'.$rrand.'.tmp', $js_file); 
			     JHTMLOPC::script($js_filename, $js_path); 
			 }
			 else
			 {
			   $html .= '
<script type="text/javascript">
//<![CDATA[		   
			   '.$js.'
//]]>		   
</script>
'; 
			 }
	
	return $html; 
	}
	return ''; 
 }
 
 public static function listStatesHTML($country_id, $namekey='address_state', $options='') {
	 
	 $country_id = (int)$country_id; 
	 
	 if (empty(self::$allZoneData)) {
		self::getStateList(); 
	 }
	 $html = '<select name="'.$namekey.'" id="'.$namekey.'_field" '.$options.' >'; 
	 foreach (self::$allZoneData as $row) {
		 
		 if ((int)$row['country_zone_id'] === $country_id) {
		   $state_id = (int)$row['zone_id']; 
		   $html .= '<option value="'.$state_id.'">'.htmlentities($row['zone_name']).'</option>'; 
		 }
	 }
	 $html .= '</select>'; 
	 return $html; 
 }
 
 public static function &getCountries() {
	if (!empty(self::$allCountries)) return self::$allCountries; 
	 
	 $db = JFactory::getDBO(); 
	 $q = 'select * from #__hikashop_zone where zone_published = 1 and zone_type = \'country\''; 
	 $db->setQuery($q); 
	 $res = $db->loadAssocList();
	 
	 
	 self::$allCountries = (array)$res; 
	 return self::$allCountries; 
 }
 
 public static function getStatesFromAlldata($country_id, $allData) {
	 $country_id = (int)$country_id; 
	 $ret = array(); 
	 foreach ($allData as $k=>$row) {
		 if ((int)$row['country_zone_id'] === $country_id) {
			 $id = (int)$row['zone_id']; 
			 $state = new stdClass(); 
			 $state->state_id = $id; 
			 $state->state_name = $row['zone_name']; 
			 $ret[$id] = $state; 
			 
			 
		 }
	 }
	 return $ret; 
	 
 }
	
	
}