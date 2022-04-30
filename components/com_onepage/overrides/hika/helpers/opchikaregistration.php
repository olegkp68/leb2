<?php
defined('_JEXEC') or die('Restricted access');

class OPChikaregistration {
	public static function setRegType() {
		if (!defined('VM_REGISTRATION_TYPE'))
		{
			$opc_registraton_type = OPCConfig::get('opc_registraton_type', 'OPTIONAL_REGISTRATION'); 
			$usersConfig = JComponentHelper::getParams( 'com_users' );
			$registration_enabled = (bool)$usersConfig->get('allowUserRegistration', 0); 
			if (empty($registration_enabled)) {
				$opc_registraton_type = 'NO_REGISTRATION'; 
			}
			define('VM_REGISTRATION_TYPE', $opc_registraton_type); 
		}
			
	}
	
	
	public static function usernameExists($username, &$return=array(), $cmd='checkusername') {
		$return['username_exists'] = false;
		if ($cmd == 'checkusername')
		{
			$db = JFactory::getDBO(); 
			$user = JFactory::getUser(); 
			$un = $user->get('username'); 
			if ($un == $username)
			{
				// do not complain if entering the same username of already registered
				$return['username_exists'] = false; 
			}
			else
			if (!empty($username))
			{
				$q = "select `username` from #__users where `username` = '".$db->escape($username)."' limit 0,1"; 
				$db->setQuery($q); 
				$r = $db->loadResult(); 
				if (!empty($r))
				{
					$return['username_exists'] = true; 
				}
				else {
				  $return['username_exists'] = false; 
				}
				
			}
		}
		return $return['username_exists'];
	}
	
	public static function emailExists($email='', &$return=array(), $cmd='checkemail') {
		$return['email_exists'] = false; 
		
		$opc_no_duplicit_email = OPChikaconfig::get('opc_no_duplicit_email', true); 
		$op_usernameisemail = OPChikaconfig::get('op_usernameisemail', true); 
		$opc_no_duplicit_username = OPChikaconfig::get('opc_no_duplicit_username', true); 
		
		if (($cmd === 'checkemail') 
				|| ((!empty($email)) 
					&& ((!empty($opc_no_duplicit_email))) 
					|| (!empty($opc_no_duplicit_username) && ((!empty($op_usernameisemail))))))
		{
			
			
			
			$return['email_to_check'] = $email; 
			
			$return['email'] = $email;
			$user = JFactory::getUser(); 
			$ue = $user->get('email'); 
			$user_id = $user->get('id'); 
			
			
			if (!empty($user_id) && ($email === $ue))
			{
				// do not complain if user is logged in and enters the same email address
				$return['email_exists'] = false; 
				$return['user_equals_login'] = true; 
			}
			else
			if (!empty($email))
			{
				$db = JFactory::getDBO(); 
				$q = "select email from #__users where username = '".$db->escape($email)."' or email = '".$db->escape($email)."' limit 0,1"; 
				$db->setQuery($q); 
				$r = $db->loadResult(); 
				
				
				$return['q'] = $q; 
				if (!empty($r))
				{
					$return['email_exists'] = true; 
				}
				
				
			}
			
			$return['email_was_checked'] = true; 
		}
		return $return['email_exists'];
		
	}
	
	public static function doRegistration() {
		self::setRegType(); 
		$register_account = JRequest::getVar('register_account', false); 
		$user_id = JFactory::getUser()->get('id', 0); 
		if (!empty($user_id)) return; 
		
		$data = array(); 
		$post = JRequest::get('post'); 
		
		$data['password'] = JRequest::getVar('opc_password', JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW), 'post', 'string' ,JREQUEST_ALLOWRAW);
		$data['password2'] = $data['password'];
		
		if (empty($register_account)) return; 
		switch (VM_REGISTRATION_TYPE) {
			case 'NO_REGISTRATION': return; 
			case 'SILENT_REGISTRATION': 
			$data['password'] = ''; 
			$data['password2'] = ''; 
			break; 
			default: 
			break; 
		}
		
		$data['email'] = $post['email']; 
		$data['name'] = self::getJoomlaName($post); 
		$op_usernameisemail = OPCHikaConfig::get('op_usernameisemail', false); 
		if (!empty($op_usernameisemail)) {
			$data['username'] = $data['email']; 
			
		}
		
		$hikaUserClass = hikashop_get('class.user');
		$options = array(); 
		if (empty($data['password'])) {
			$mode = 1; 
		}
		else {
			$mode = 0;
		}
		
		//$mode = 2 -> guest registration -> this is handled by OPC elsewhere
		$registerData = array(); 
		$registerData['register'] = $data; 
		$registerData['user'] = array(); 
		$registerData['user']['user_email'] = $data['email'];
		$registerData['user']['user_id'] = (int)OPChikaAddress::getHikaUserId(); 
		$registerData['address'] = null; 
		
		$ret = array(); 
		if (OPCHikaRegistration::usernameExists($data['username'], $ret)) return; 
		if (OPCHikaRegistration::emailExists($data['email'], $ret)) return; 
		
		$ret = $hikaUserClass->register($registerData, $mode, $options);
		if (!empty($ret['messages'])) {
			$msgs = OPCHikaMessages::implodeMsgs($ret['messages']); 
			JFactory::getApplication()->enqueueMessage($msgs); 
		}
	}
	
	public static function getJoomlaName($post) {
		if (!empty($post['name'])) return $post['name']; 
		$data = array(); 
		$op_no_display_name = OPCHikaConfig::get('op_no_display_name', false);
		if (!empty($op_no_display_name)) {
			$joomla_name = array(); 
			if (!empty($post['address_firstname'])) $joomla_name[] = $post['address_firstname'];
			if (!empty($post['address_middlename'])) $joomla_name[] = $post['address_middlename'];
			if (!empty($post['address_lastname'])) $joomla_name[] = $post['address_lastname'];
			
			if (!empty($joomla_name)) {
			  $data['name'] = implode(' ', $joomla_name); 
			}
			else {
				if (!empty($data['username'])) $data['name'] = $data['username']; 
				else
				if (!empty($data['email'])) $data['name'] = $data['email']; 
			}
		}
		return $data['name']; 
		
	}
	
	public static function getRegistrationFieldsHTML() {
	
		$ref = OPChikaRef::getInstance(); 
		$rowFields = self::getRegistrationFields(); 
		$unlg = OPChikauser::logged(); 
		
		$state = OPCHikaState::get('register_account', null); 
		if (!is_null($state)) {
			if (empty($state)) {
			  $op_create_account_unchecked = true;
			}
			else {
				$op_create_account_unchecked = false;
			}
		}
		else {
		 $op_create_account_unchecked = OPCHikaConfig::get('op_create_account_unchecked', false); 	
		}
		
		
		
		$vars = array(
		'rowFields' => $rowFields, 
		'rowFields_st' => $rowFields, 
		'cart' => $ref->cart, 
		'opc_logged' => $unlg,
		'op_usernameisemail'=>OPCHikaConfig::get('op_usernameisemail', false),
		'op_create_account_unchecked'=>$op_create_account_unchecked,
		);
		
		$html = OPChikarenderer::fetch('list_user_fields.tpl', $vars); 
		$search = array("'password'"); 
		$rep = array("'opc_password'"); 
		$html = str_replace($search, $rep, $html); 
		return $html;  
	}
	
	public static function getRegistrationFields() {
		
		$nousername = false; 
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$registration_enabled = (bool)$usersConfig->get('allowUserRegistration', 0); 
		
		$fields = array(); 
		$fields['fields'] = array(); 
		
		$op_no_display_name = OPCHikaConfig::get('op_no_display_name', false); 
		if ((empty($op_no_display_name)) && ($registration_enabled)) {
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_USER_NAME'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = JFactory::getUser()->get('name',  OPCHikaState::get('name', ''));
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="name" id="name_field" type="text" value="'.htmlentities($field['value']).'" autocomplete="name" />'; 
		$field['name'] = 'name'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['name'] = $field; 
		}
		
		$op_usernameisemail = OPCHikaConfig::get('op_usernameisemail', false); 
		if ((empty($op_usernameisemail)) && ($registration_enabled)) {
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_USERNAME'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = JFactory::getUser()->get('username',  OPCHikaState::get('email', ''));
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="username" id="username_field" type="text" value="'.htmlentities($field['value']).'" autocomplete="username" />'; 
		$field['name'] = 'username'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['username'] = $field; 
		}
		
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_EMAIL'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = JFactory::getUser()->get('email',  OPCHikaState::get('email', ''));
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="email" id="email_field" type="email" value="'.htmlentities($field['value']).'" autocomplete="email" />'; 
		$field['name'] = 'email'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['email'] = $field; 
		
		$double_email = OPCHikaConfig::get('double_email', false); 
		if (!empty($double_email)) {
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_EMAIL_CONFIRM'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = JFactory::getUser()->get('email', OPCHikaState::get('email', ''));
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="email2" id="email2_field"  type="email" value="'.htmlentities($field['value']).'" autocomplete="email" />'; 
		$field['name'] = 'email2'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['email2'] = $field; 
		}
		
		$user_id = JFactory::getUser()->get('id', 0); 
		$opc_registraton_type = OPCConfig::get('opc_registraton_type', 'OPTIONAL_REGISTRATION'); 
		$arr = array('NO_REGISTRATION', 'SILENT_REGISTRATION'); 
		
		if (((empty($user_id)) && (!in_array($opc_registration_type, $arr))) && ($registration_enabled)) {
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_PASSWORD'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = '';
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="opc_password" id="opc_password_field"  type="password" value="" autocomplete="password" />'; 
		$field['name'] = 'opc_password'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['opc_password'] = $field; 
		
		
		$field = array(); 
		$field['required'] = true; 
		$field['title'] = JText::_('HIKA_VERIFY_PASSWORD'); 
		$field['placeholder'] = $field['title'];
		$field['value'] = '';
		$field['type'] = 'text'; 
		$field['formcode'] = '<input name="opc_password2" id="opc_password2_field"  type="password" value="" autocomplete="password" />'; 
		$field['name'] = 'password2'; 
		$field['ready_only'] = false; 
		$field['published'] = true; 
		$field['field_realname'] = $field['title'];
		$field['field_namekey'] = $field['name'];
		$fields['fields']['opc_password2'] = $field; 
		}
		return $fields; 
		
		
	}
}