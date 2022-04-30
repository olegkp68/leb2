<?php 
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

class OPCUser {

	
	public static $opc_bt_user_info_id; 
	public static $opc_st_user_info_id; 
	
	/**
	 * Bind the post data to the JUser object and the VM tables, then saves it
	 * It is used to register new users
	 * This function can also change already registered users, this is important when a registered user changes his email within the checkout.
	 *
	 * @author Max Milbers
	 * @author Oscar van Eijk
	 * @return boolean True is the save was successful, false otherwise.
	 */
	public static function storeVM25(&$data,$checkToken = TRUE, &$userModel, $opc_no_activation=false, &$opc, $cart){

		$message = '';
		$user = '';
		$newId = 0;

		
      

		
		if($checkToken){
			JRequest::checkToken() or jexit( 'Invalid Token, while trying to save user' );
			
		}
		
		if (isset($data['name']))
		$data['name'] = trim($data['name']); 
		
		$mainframe = JFactory::getApplication() ;

		if(empty($data)){
			
			return false;
		}

		//To find out, if we have to register a new user, we take a look on the id of the usermodel object.
		//The constructor sets automatically the right id.
		$user = JFactory::getUser();
		
		$user_id = $user->id; 
		$data['virtuemart_user_id'] = (int)$user_id; 
		$new = ($user->id < 1);
		
		
		
		
		if(empty($user_id)){
			$user = new JUser();	//thealmega http://forum.virtuemart.net/index.php?topic=99755.msg393758#msg393758
		} else {
			$user = JFactory::getUser($user_id);
		}

		$gid = $user->get('gid'); // Save original gid
	    
		// Preformat and control user datas by plugin
		JPluginHelper::importPlugin('vmuserfield');
		$dispatcher = JDispatcher::getInstance();

		$valid = true ;
		$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave',array(&$valid,$user_id,&$data,$user ));
		// $valid must be false if plugin detect an error
		if( $valid === false ) {
			return false;
		}
		
		$data['virtuemart_user_id'] = (int)$user_id; 

		// Before I used this "if($cart && !$new)"
		// This construction is necessary, because this function is used to register a new JUser, so we need all the JUser data in $data.
		// On the other hand this function is also used just for updating JUser data, like the email for the BT address. In this case the
		// name, username, password and so on is already stored in the JUser and dont need to be entered again.

		if(empty ($data['email'])){
			$email = $user->get('email');
			if(!empty($email)){
				$data['email'] = $email;
			}
		} 
		
		$data['email'] = str_replace(array('\'','"',',','%','*','/','\\','?','^','`','{','}','|','~'),array(''),$data['email']);
		
		unset($data['id']); 
		unset($data['isRoot']);
		unset($data['groups']);
		unset($data['_authGroups']);
		
		//This is important, when a user changes his email address from the cart,
		//that means using view user layout edit_address (which is called from the cart)
		$user->set('email',$data['email']);

			if(empty ($data['name'])){
			$name = $user->get('name');
			if(!empty($name)){
				$data['name'] = $name;
			}
		} 
		
		if (empty($data['name']))
		 {
			$joomla_name = array(); 
		    $data['name'] = ''; 
		    if ((!empty($data['first_name'])) && ($data['first_name'] !== '_'))
		    $joomla_name[] = $data['first_name']; 

			if ((!empty($data['middle_name'])) && ($data['middle_name'] !== '_'))
		    $joomla_name[] = $data['middle_name']; 

		
			if ((!empty($data['last_name'])) && ($data['last_name'] !== '_'))
		    $joomla_name[] = $data['last_name']; 
			
			if (!empty($joomla_name)) {
			$data['name'] = implode(' ', $joomla_name); 
			}
			else {
			if (empty($data['name']))
		    $data['name'] = $data['username']; 
			if ($data['name'] == '_') $data['name'] = ''; 
			
			if (empty($data['name']))
			$data['name'] = $data['email']; 
			}
		
		 }
		

		if(empty ($data['username'])){
			$username = $user->get('username');
			if(!empty($username)){
				$data['username'] = $username;
			} else {
				$data['username'] = JRequest::getVar('username', '', 'post', 'username');
				
				if (empty($data['username']))
				$data['username'] = $data['email']; 
			}
		}
		
		
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$can_change_username = $usersConfig->get('change_login_name', null); 
		if (($can_change_username === 0) || (($can_change_username === '0'))) {
			$user_id = $user->get('id'); 
			if (!empty($user_id)) {
				$username = $user->get('username');
				/*
				if ($username !== $data['username']) {
					//username change prevented
				}
				*/
				$data['username'] = $username; 
			}
		}
		
		

		if(empty ($data['password'])){
			$data['password'] = JRequest::getVar('password', '', 'post', 'string' ,JREQUEST_ALLOWRAW);
		}

		if(empty ($data['password2'])){
			$data['password2'] = JRequest::getVar('password2', '', 'post', 'string' ,JREQUEST_ALLOWRAW);
		}

		
		if(!$new && !empty($data['password']) && empty($data['password2'])){
			unset($data['password']);
			unset($data['password2']);
		}
		
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$usernamechange = $usersConfig->get( 'change_login_name', true );
		if (!$new)
		if (empty($usernamechange))
		 {
		   $data['username'] = $user->get('username'); 
		 }
		 
		 
		 if(!$user->authorise('core.admin','com_virtuemart')){
			$whiteDataToBind = array();
			$whiteDataToBind['name'] = $data['name'];
			$whiteDataToBind['username'] = $data['username'];
			$whiteDataToBind['email'] = $data['email'];
			if(isset($data['password'])) $whiteDataToBind['password'] = $data['password'];
			if(isset($data['password2'])) $whiteDataToBind['password2'] = $data['password2'];
		} else {
			$whiteDataToBind = $data;
		}
		 
		 
		 
		// Bind Joomla userdata
		if (!$user->bind($whiteDataToBind)) {

			
			$message = 'Error 196: Could not bind data to joomla user'; //.var_export($whiteDataToBind, true);
			JFactory::getApplication()->enqueueMessage($message, 'error'); 
			return; 
			
		}

		if($new){
			// If user registration is not allowed, show 403 not authorized.
			// But it is possible for admins and storeadmins to save
			/*
			JPluginHelper::importPlugin('user');
			JPluginHelper::importPlugin('system');
			$dispatcher = JDispatcher::getInstance();

			$valid = true ;
			$dispatcher->trigger('onAfterStoreUser',array($user,true,true,'' ));
			*/
			if ((!defined('VM_VERSION')) || (VM_VERSION < 3))
			{
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'permissions.php');

			if (!Permissions::getInstance()->check("admin,storeadmin") && $usersConfig->get('allowUserRegistration') == '0') {
				VmConfig::loadJLang('com_virtuemart');
				 //JError::raiseError( 403, JText::_('COM_VIRTUEMART_ACCESS_FORBIDDEN'));
				 $data['virtuemart_user_id'] = 0; 
				 
				 unset($data['username']);
				 unset($data['password']);
			     unset($data['password2']);
				 $user = new JUser(); 
				 $userModel->_id = 0; 
				 
				 //$userModel->saveUserData($data); 
			     VirtueMartControllerOpc::$ok_arr['222'] = $opc->userStoreAddress($userModel, $data, $cart); 
				 return false;
			}
			$authorize	= JFactory::getACL();
			}
			else
			{
			  $authorize = JFactory::getUser();
			 if(!($authorize->authorise('core.admin','com_virtuemart') or $authorize->authorise('core.manage','com_virtuemart')) and $usersConfig->get('allowUserRegistration') == '0') {
				VmConfig::loadJLang('com_virtuemart');
				vmError( JText::_('COM_VIRTUEMART_ACCESS_FORBIDDEN'));
				
			    $data['virtuemart_user_id'] = 0; 
				 
				 unset($data['username']);
				 unset($data['password']);
			     unset($data['password2']);
				 $user = new JUser(); 
				 $userModel->_id = 0; 
				 
				 //$userModel->saveUserData($data); 
			     VirtueMartControllerOpc::$ok_arr['243'] = $opc->userStoreAddress($userModel, $data, $cart); 
				 return false;
				
				
			} 
				
			
			}
			  
			  
			
			// Initialize new usertype setting
			$newUsertype = $usersConfig->get( 'new_usertype' );
			
			
			if (!$newUsertype) {
				if ( JVM_VERSION===1){
					$newUsertype = 'Registered';

				} else {
					$newUsertype=2;
				}
			}
			// Set some initial user values
			$user->set('usertype', $newUsertype);

			if ( JVM_VERSION===1){
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
			} else {
				$user->groups[] = $newUsertype;
			}

			$date = JFactory::getDate();
			
			if (method_exists($date, 'toMySQL'))
			$user->set('registerDate', $date->toMySQL());
			else $user->set('registerDate', $date->toSQL());
			
			// If user activation is turned on, we need to set the activation information
			$useractivation = $usersConfig->get( 'useractivation' );
			if (!empty($opc_no_activation))
			 {
			   $useractivation = false; 
			 }
			$doUserActivation=false;
			if ( JVM_VERSION===1){
				if ($useractivation == '1' ) {
					$doUserActivation=true;
				}
			} else {
				if ($useractivation == '1' or $useractivation == '2') {
					$doUserActivation=true;
				}
			}
			vmdebug('user',$useractivation , $doUserActivation);
		
			
			if ($doUserActivation )
			{
				jimport('joomla.user.helper');
				if (method_exists('JApplication', 'getHash'))
				$user->set('activation', JApplication::getHash( JUserHelper::genRandomPassword()) );
				else
				$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				//$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				$user->set('block', '1');

			}
		}

		$option = JRequest::getCmd( 'option');
		// If an exising superadmin gets a new group, make sure enough admins are left...
		if (!$new && $user->get('gid') != $gid && $gid == __SUPER_ADMIN_GID) {
		    if (method_exists($userModel, 'getSuperAdminCount'))
			if ($userModel->getSuperAdminCount() <= 1) {
				vmError(JText::_('COM_VIRTUEMART_USER_ERR_ONLYSUPERADMIN'));
				return false;
			}
		}
		
		if(isset($data['language'])){
			$user->setParam('language',$data['language']);
		}
		else
		if(isset($data['order_language'])){
			$user->setParam('language',$data['order_language']);
		}
		else
		if (!isset($data['language']))
		{
			$data['language'] = JFactory::getLanguage()->getTag(); 
			$user->setParam('language',$data['language']);
		}

		
		// Save the JUser object
		$regfail = false; 
		$stored_option = JRequest::getVar('option', ''); 
		JRequest::setVar('option', 'com_onepage'); 
		$_POST['option'] = $_GET['option'] = $_REQUEST['option'] = 'com_onepage'; 
		if (!$user->save()) {
		    
			vmError(JText::_( $user->getError()) , JText::_( $user->getError()));
			$regfail = true; 
		}
		
		if (!empty($stored_option)) {
			$_POST['option'] = $_GET['option'] = $_REQUEST['option'] = $stored_option; 
			JRequest::setVar('option', $stored_option); 
		}
		
		//vmdebug('my user, why logged in? ',$user);
		if (!$regfail) 
		{
		$newId = $user->get('id');
		}
		else
		$newId = 0; 
		
		$data['virtuemart_user_id'] = $newId;	//We need this in that case, because data is bound to table later
		
		
		
		$regid = $user->get('id'); 
		if (!empty($regid))
		$GLOBALS['opc_new_user'] = $user->get('id'); 
		else
		$GLOBALS['opc_new_user'] = $newId; 
		
		
		$userModel->_id = $newId; 
		$userModel->_data = null; 

		//Save the VM user stuff
		if (!empty($data['quite']))
		{
		  $msgqx1 = JFactory::getApplication()->get('messageQueue', array()); 
		  $msgqx2 = JFactory::getApplication()->get('_messageQueue', array()); 
		}
		
		
		if (!empty($newId))
		{
		include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php');
		if (($new) || ($allow_sg_update))
		{
		  
		  $userdata = $userModel->saveUserData($data); 
		
			$groups = array(); 
			if (method_exists($userModel, 'getCurrentUser'))
			{
				$user2 = $userModel->getCurrentUser();
				$groups = $user2->shopper_groups; 
			}
			
			
		
		//if(Permissions::getInstance()->check("admin,storeadmin")) 
		{
			$shoppergroupmodel = VmModel::getModel('ShopperGroup');
			
			$default = $shoppergroupmodel->getDefault(0);
			if (is_object($default) && (isset($default->virtuemart_shoppergroup_id))) {
				$default_id = (int)$default->virtuemart_shoppergroup_id; 
			}
			else {
				if (is_numeric($default)) {
					 $default_id = $default; 
				}
			}
			if (empty($default_id)) $default_id = 1; 
		    $default = $default_id;
			
			$default1 = $shoppergroupmodel->getDefault(1);
			
		   if ((!empty($default1)) && (is_object($default1)))
		   $default1 = $default1->virtuemart_shoppergroup_id; 
		   else
		   if (!is_numeric($default1)) 
		   $default1 = 2; 
			
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shoppergroups.php'); 
			OPCShopperGroups::getSetShopperGroup(false); 
			
			
			
			$session = JFactory::getSession();
			$ids = $session->get('vm_shoppergroups_add',array(),'vm');
			if (!empty($groups))
			$ids = array_merge($ids, $groups); 
			
			$remove = $session->get('vm_shoppergroups_remove',array(),'vm');
			if (!empty($remove))
			foreach ($remove as $sr)
			foreach ($ids as $key=>$sg)
			{
			  if ($sg == $sr) unset($ids[$key]); 
			
			}
			if (!empty($ids))
			foreach ($ids as $key=>$sg)
			{
			  if ($sg == $default) unset($ids[$key]); 
			  if (empty($sg)) unset($ids[$key]);
			  if ($sg == $default1) unset($ids[$key]);
			}
		
			if(empty($data['virtuemart_shoppergroup_id']) or $data['virtuemart_shoppergroup_id']==$default->virtuemart_shoppergroup_id){
				$data['virtuemart_shoppergroup_id'] = array();
			}
			
			
			
			if (!empty($ids))
			{
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shoppergroups.php'); 
			OPCShopperGroups::updateSG($data, $user_id, $ids); 
			

			}
		}
		
		
		}
		}
		
		
		//$userAddress = $userModel->storeAddress($data); 
		$userAddress = $opc->userStoreAddress($userModel, $data, $cart); 
		
		if (empty($userAddress))
		VirtueMartControllerOpc::$ok_arr['461'] = false; 
		else
		VirtueMartControllerOpc::$ok_arr['461'] = true; 
		
		if (!empty($data['quite']))
		{
		  $x = JFactory::getApplication()->set('messageQueue', $msgqx1); 
		  $x = JFactory::getApplication()->set('_messageQueue', $msgqx2); 
		}
		
		if((empty($userdata) || (empty($userAddress)))) {
			// we will not show the error because if we display only register fields, but an account field is marked as required, it still gives an error
			if (empty($data['quite'])) {
			  vmError('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA');
			}
			
		} 
		
		 $sendMail = false; 
		if (!$regfail)
		{
		    VirtueMartControllerOpc::$ok_arr['480'] = true; 
			if ($new) {
			    
				// make sure that VM has proper user: 
				if (!empty($newId))
				{
			     //JFactory::getUser()->load($newId); 
				 if (!class_exists('VirtueMartViewUser'))
				 {
				   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'virtuemart.user.registration.view.html.php');
				 }
				 $sendMail = true; 
				}
				if ($doUserActivation ) {
					vmInfo('COM_VIRTUEMART_REG_COMPLETE_ACTIVATE');
				} else {
					//vmInfo('COM_VIRTUEMART_REG_COMPLETE');
					$user->set('activation', '' );
					$user->set('block', '0');
					$user->set('guest', '0');
				}
			}		
		}
		else
		{
		  VirtueMartControllerOpc::$ok_arr['506'] = false; 
		}
		
		if (!empty($user->password_clear)) $password = $user->password_clear; 
		else
		if (!empty($data['password'])) $password = $data['password']; 
		
		
		
		if ($sendMail)
		OPCUser::sendRegistrationEmail($user,$password, $doUserActivation, $data);
		//The extra check for isset vendor_name prevents storing of the vendor if there is no form (edit address cart)
		// stAn, let's not alter vendor
		/*
		if((int)$data['user_is_vendor']==1 and isset($data['vendor_name'])){
			vmdebug('vendor recognised '.$data['virtuemart_vendor_id']);
			if($userModel->storeVendorData($data)){
				if ($new) {
					if ($doUserActivation ) {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE_ACTIVATE');
					} else {
						vmInfo('COM_VIRTUEMART_REG_VENDOR_COMPLETE');
					}
				} else {
					vmInfo('COM_VIRTUEMART_VENDOR_DATA_STORED');
				}
			}
		}
		*/

		return array('user'=>$user,'password'=>$data['password'],'message'=>$message,'newId'=>$newId,'success'=>!$regfail);

	}
	
	
	public static function adminRegister()
	{
	
	        JFactory::getLanguage()->load('com_users');
	  
			$admin = false; 
	   		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	        if ((OPCmini::isSuperVendor()) || ((JFactory::getUser()->authorise('core.admin', 'com_virtuemart') || JFactory::getUser()->authorise('core.admin', 'com_virtuemart')))) { 
			  $admin = true; 
			}
			$my = JFactory::getUser();
			$iAmSuperAdmin = $my->authorise('core.admin');
			if (empty($iAmSuperAdmin)) return; 
			
			if (!$admin) 
			 {
			   return; 
			 }
			 
			 $session = JFactory::getSession(); 
			 
			 
			 require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
			 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');
			 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_users'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'user.php'); 
			 
			 $UsersModelUser = new UsersModelUser(); 
			 
			 
			 $user_id = $virtuemart_user_id = JRequest::getInt('virtuemart_user_id', 0); 
			 
			 $virtuemart_userinfo_id = JRequest::getInt('virtuemart_userinfo_id', 0); 
			 
			 
		
			 //$details = $UsersModelUser->getUser($user_id);
			 
			 $data = array(); 
			 $post = JRequest::get('post'); 
			 
			 
			 if (empty($post['password']) && (!empty($post['password2']))) {
				 $post['password'] = $post['password2']; 
			 }
			 
			  
						 
			 
			 
			 if (empty($post['username']))
			 $data['username'] = $post['email']; 
			 else
			 $data['username'] = $post['username']; 
			 
			
			 if (empty($user_id)) {
			 if (empty($post['password']))
			 $data['password'] =  $data['password2'] = JUserHelper::genRandomPassword();
			 else $data['password'] = $post['password']; 
			
			 
			 
			 if (empty($post['password2'])) $data['password2'] = $post['password']; 
			 }
			 
			 if (isset($post['twofactor'])) 
			 $data['twofactor'] = $post['twofactor']; 
			 
			 $data['id'] = $user_id; 
			 
			 if (empty($user_id)) {
			  $data['block'] = 0; 
			  $data['activation'] = ''; 
			 }
			 
			 $data['email'] = $post['email']; 
			 if (empty($post['name']))
			 {
			   if (isset($post['first_name']))
			   $data['name'] = $post['first_name'];
			   
			   if (!empty($data['name']))
			   $data['name'] .= ' '; 
			   
			   if (isset($post['last_name']))
			   $data['name'] .= $post['last_name']; 
			   
			   if (empty($data['name']))
			   $data['name'] = $post['email']; 
			   
			 }
			 else
			 $data['name'] = $post['name']; 
			 
			$date = new JDate(); 
			 
			 if (method_exists($date, 'toMySQL'))
			 $data['registerDate'] =  $date->toMySQL();
			 else $data['registerDate'] = $date->toSql(); 
			 
			 
			 
			 
			 $data['registerDate'] = ''; 
			 $data['lastvisitDate'] = ''; 
			 
			 
			 $data['sendEmail'] = 0; 
			 
			 if (empty($user_id)) {
			  $data['requireReset'] = 0; 
			 }
			 
			 
			 // Initialize new usertype setting
			
			$usersConfig = JComponentHelper::getParams( 'com_users' );
			$newUsertype = $usersConfig->get( 'new_usertype', 2 );
			
		
			if (!$newUsertype) {
				
					$newUsertype=2;
				
			}
			
			// Set some initial user values
			$data['usertype'] = $newUsertype;
            
			$data['gid'] = $newUsertype; 
		    $data['groups'] = array(); 
			
			
			$data['groups'][] = $newUsertype; 
			
			if (!empty($user_id)) {
			 unset($data['password']); 
			 unset($data['password2']);
			 $password = ''; 
			}
			else {
			$data['password2'] = $data['password']; 
			$password = $data['password']; 
			}
			 
			 $UsersModelUser->setState('user.id', $user_id);
			 
			 
			 $result = $UsersModelUser->save($data); 
			 $err = $UsersModelUser->getError(); 
			 $returned_user_id = $UsersModelUser->getState('user.id', $user_id);
			 
			
			 $url = 'index.php?option=com_onepage&view=add_shopper'; 
			 if (!empty($virtuemart_user_id)) {
				$url .= '&user_id='.$virtuemart_user_id;
			 }
			 
			 if (!empty($err) || (empty($returned_user_id)))
			  {
				$session->set('admin_add_shopper', json_encode($post)); 
				$session->close(); 
				
			    JFactory::getApplication()->redirect(JRoute::_($url.'&error_redirect='.__LINE__), $err); 
				JFactory::getApplication()->close(); 
			  }
			  $session->clear('admin_add_shopper'); 
			  $user_id = $returned_user_id;
			  
			  
			  
			if (!empty($user_id))
			{
			   foreach ($post as $k=>$v)
			    {
				   if (!isset($data[$k]))
				   $data[$k] = $v; 
				}
			
			
			$data['virtuemart_user_id'] = $user_id; 
			$data['address_type'] = 'BT'; 
			$data['virtuemart_userinfo_id'] = $virtuemart_userinfo_id; 
			$userModel = OPCmini::getModel('user');
			$userModel->setId($user_id);
			
			
			
			//saveUserData
			self::storeVMuser($data, $userModel); 
			
			if (!empty($virtuemart_user_id)) {
				
				
				$msg = JText::_('COM_ONEPAGE_NEW_USER_CREATED').' '.$data['username'];  
			
			
			
				JFactory::getApplication()->redirect(JRoute::_($url), $msg); 
				JFactory::getApplication()->close(); 
				
				
			}
			
			// send email start...
			$config = JFactory::getConfig();
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
				
		  $emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);
		   // Get all admin users
		   //$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
		   
		   $db = JFactory::getDBO(); 

			// Get the user id based on the token.
			$query = $db->getQuery(true);
		   
			$query->clear()
				->select($db->quoteName(array('name', 'email', 'sendEmail', 'id')))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('sendEmail') . ' = ' . 1);
	
			$db->setQuery($query);

			try
			{
				$rows = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$rows = array(); 
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
					
				}
				OPCloader::storeError('address_history', $e); 
			}
			
			/*
			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);
			// Send mail to all users with users creating permissions and receiving system emails
			foreach ($rows as $row)
			{
				$usercreator = JFactory::getUser($row->id);

				if ($usercreator->authorise('core.create', 'com_users'))
				{
					$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

				}
			}
			
			*/
			//we'll use VM emails instead of joomla mails: 
			
			$local_user = JFactory::getUser($user_id);
			self::sendRegistrationEmail($local_user, $password, false, $data); 
			
			
			// send email end...
			
			
			$msg = JText::_('COM_ONEPAGE_NEW_USER_CREATED').' '.$data['username'];  
			
			
			
			JFactory::getApplication()->redirect(JRoute::_($url), $msg); 
			JFactory::getApplication()->close(); 
			}
			
			

	}
	
	//only for admin edits
  private static function storeVMuser($data, $userModel)
 {
	 
	 
        $user = JFactory::getUser(); 
 		$manager = ($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart'));
		if (!$manager) return; 
		
		
		
		$userinfo = $userModel->getTable('userinfos');
		$userInfoData = VirtueMartModelUser::_prepareUserFields($data, 'BT',$userinfo);
		
		if (!$userinfo->bindChecknStore($userInfoData)) {
				$error = $userinfo->getError();
				if (!empty($error)) {
					JFactory::getApplication()->enqueueMessage($error, 'error'); 
					return; 
				}
				
				
				
				
			}
				if ($userinfo->address_type === 'BT') {
					$cart = VirtuemartCart::getCart(); 
					if (!empty($userinfo->virtuemart_userinfo_id)) {
						OPCUser::$opc_bt_user_info_id = (int)$userinfo->virtuemart_userinfo_id;
					}
				}
				
				if ($userinfo->address_type === 'ST') {
					$cart = VirtuemartCart::getCart(); 
					if (!empty($userinfo->virtuemart_userinfo_id)) {
						OPCUser::$opc_st_user_info_id = (int)$userinfo->virtuemart_userinfo_id;
					}
				}
			
			
			
		// Bind the form fields to the table
		    //since our form shows shopper groups, let's remove them all; 
			$db = JFactory::getDBO(); 
			$user_id = (int)$data['virtuemart_user_id']; 
			if (!empty($user_id)) {
				$q = 'delete from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id; 
				$db->setQuery($q); 
				$db->execute(); 
			}
			
			if(!empty($data['virtuemart_shoppergroup_id'])){
				
				OPCShopperGroups::updateSG($data, $data['virtuemart_user_id'], $data['virtuemart_shoppergroup_id']); 	
				/*
				$shoppergroupData = array('virtuemart_user_id'=>$data['virtuemart_user_id'],'virtuemart_shoppergroup_id'=>$data['virtuemart_shoppergroup_id']);
				$user_shoppergroups_table = $userModel->getTable('vmuser_shoppergroups');
				$shoppergroupData = $user_shoppergroups_table -> bindChecknStore($shoppergroupData);
				$errors = $user_shoppergroups_table->getErrors();
				foreach($errors as $error){
					JFactory::getApplication()->enqueueMessage($error); 
				}
				*/
			}	
			
 }
	
	
		/**
	 * This uses the shopFunctionsF::renderAndSendVmMail function, which uses a controller and task to render the content
	 * and sents it then.
	 *
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @author Christopher Roussel
	 * @author Valérie Isaksen
	 */
	private static function sendRegistrationEmail($user, $password, $doUserActivation, $data){
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php');
		$vars = array('user' => $user);

		// Send registration confirmation mail

		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
		$vars['password'] = $password;
		
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		
		$adminMail = $usersConfig->get('mail_to_admin',false);
		
		if(empty($adminMail)){
			unset($vars['doVendor']);	//The construction is due the nasty construction in renderMail
		} else {
			$vars['doVendor'] = 1;
		}
		
		
		$sendpassword = $usersConfig->get( 'sendpassword' );
		



		
		
		//if (empty($vars['name'])) $vars['name'] = ' '; 
		if ($doUserActivation) {
			jimport('joomla.user.helper');
			if(JVM_VERSION >= 2) {
				$com_users = 'com_users';
				$activationLink = 'index.php?option='.$com_users.'&task=registration.activate&token='.$user->get('activation');
			} else {
				$com_users = 'com_user';
				$activationLink = 'index.php?option='.$com_users.'&task=activate&activation='.$user->get('activation');
			}
			$vars['activationLink'] = $activationLink;
		}
		
		
		
		//here we could alter the vendor emails if needed... 
		//$mail_to_admin = $usersConfig->get( 'mail_to_admin' );
		
		$vars['isMail'] = true; 
		
		 if (method_exists('VmConfig', 'loadJLang'))
		 {
		   VmConfig::loadJLang('com_virtuemart_shoppers',TRUE);
		   VmConfig::loadJLang('com_virtuemart_orders',TRUE);
		 }
		
			$usermodel = VmModel::getModel('user');
			$usermodel->_data = null; 
			
			if (empty($usermodel->_id))
			{
			 if (!empty($GLOBALS['opc_new_user']))
			 $usermodel->_id = $GLOBALS['opc_new_user']; 
			}
			
			$vmuser = $usermodel->getUser();
			$userInfo = reset($vmuser->userInfo);
			
		 $user->userInfo = $userInfo; 
		 
		 if (empty($sendpassword)) {
			 if (!empty($user->userInfo)) {
			 unset($user->userInfo->password); 
			 }
			 unset($vars['password']); 
		 }
		 $vars['user'] = $user; 
		
		
		
		 // stAn, to get rid of the notices: 
		 /*
		 $orderDetails = array(); 
		 $orderDetails['details'] = array(); 
		 $orderDetails['details']['BT'] = new stdClass(); 
		 
		 if (empty($data['order_language']))
		 $orderDetails['details']['BT']->order_language = JFactory::getLanguage()->getTag(); 
		 else
		 $orderDetails['details']['BT']->order_language = $data['order_language']; 
		 
		 $orderDetails['details']['BT']->first_name = $data['first_name']; 
		 $orderDetails['details']['BT']->last_name = $data['last_name']; 
		 
		
		$es = VmConfig::get('email_os_v',array('U','C','R','X'));
		$es1 = reset($es); 
		
		
		$vars['newOrderData'] = array(); 
		$vars['newOrderData']['customer_notified'] = 1; 
        $orderDetails['details']['BT']->order_status = $es1; 
		$orderDetails['details']['BT']->email = $user->get('email');
		
		$vars['orderDetails'] = $orderDetails; 
		*/
		// public function renderMail ($viewName, $recipient, $vars=array(),$controllerName = null)
		//
		
		// a custom code to trigger user registration email without notices: 
		//shopFunctionsF::renderMail('user', $user->get('email'), $vars, 'user', false, false);
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		if (!class_exists('VirtueMartCart'))
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
	    $cart = VirtuemartCart::getCart(); 
		$vi = OPCloader::getVendorInfo($cart); 
		
		if (isset($vi['vendorEmail'])) {
		  $vars['vendorEmail'] = $vi['vendorEmail']; 
		}
		try {
		  shopFunctionsF::renderMail('user', $user->get('email'), $vars);
		}
		catch (Exception $e) {
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
					
				}
				OPCloader::storeError('address_history', $e); 
		}


	}
	
	
	public static function createOrder2UserinfosTable() {
		 
		
		self::checkSchema(); 

	}
	
	
	public static function guessUserBTID($cart, &$matchPercent=0) {
		$db = JFactory::getDBO(); 
		if (!empty($cart->selected_shipto)) {
			
			$q = 'select u.`virtuemart_userinfo_id` from #__virtuemart_userinfos as u inner join #__virtuemart_userinfos as u2 on u2.virtuemart_user_id = u.virtuemart_user_id where u2.virtuemart_userinfo_id = '.(int)$cart->selected_shipto.' and u.address_type = \'BT\''; 
			$db->setQuery($q); 
			$u = $db->loadResult(); 
			$matchPercent = 100; 
			if (!empty($u)) return (int)$u; 
			
		}
		if (empty($cart->BT)) return 0;
		if (is_array($cart->BT) && (count($cart->BT) === 1)) return 0;
		if (!empty($cart->BT['virtuemart_userinfo_id'])) {
			$matchPercent = 100;  
			return (int)$cart->BT['virtuemart_userinfo_id'];
		}
		$user_id = JFactory::getUser()->get('id'); 
		
		
		
		$enabledFields = OPCUserFields::getEditableFieldsNames('BT'); 
		$db = JFactory::getDBO(); 
		
		/*
		$q = 'select `name` from #__virtuemart_userfields where `published` = 1 and `type` != \'delimiter\' and `account` = 1'; 
		if (defined('VM_VERSION') && (VM_VERSION >= 3))
		{
			$q .= ' and `cart` = 0 '; 
		}
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$enabledFields = array(); 
		foreach ($res as $row) {
			$enabledFields[$row['name']] = $row['name']; 
		}
		*/
		
		if (empty($user_id) && (!empty($GLOBALS['opc_new_user']))) $user_id = $GLOBALS['opc_new_user']; 
		if (!empty($user_id)) {
			$q = 'select * from #__virtuemart_userinfos where virtuemart_user_id = '.(int)$user_id.' and `address_type` = \'BT\''; 
		}
		else {
			//duplicate email case (user is not logged in and activation is waiting, lets get his address IDs)
			$q = 'select u.* from #__virtuemart_userinfos as u inner join #__users as j on ((j.`id` = u.virtuemart_user_id) and (j.email = \''.$db->escape($cart->BT['email']).'\')) where u.`address_type` = \'BT\''; 

		}
		
						$ign = array('virtuemart_userinfo_id', 'virtuemart_user_id', 'address_type', 'address_type_name', 'name', 'agreed', 'tos', '', 'created_on', 'created_by', 'modified_on', 'modified_by', 'locked_on', 'locked_by');  
						if (function_exists('mb_strtolower'))
						$cf = 'mb_strtolower'; 
						else $cf = 'strtolower'; 
						
						$db->setQuery($q); 
						$res = $db->loadAssocList(); 
							
					    $address = $cart->BT; 
						
						$matches = array(); 
						$maxCount = array(); 
						// user is already registered, but we need to fill some of the system fields
							foreach ($res as $k=>$ad)
							{
								$match = 0; 
								$maxCount[$k] = 0; 
								$matches[$k] = 0; 
								foreach ($ad as $nn=>$val)
								{
									//is available to ST:
									if (!isset($enabledFields[$nn])) continue; 
									if (!in_array($nn, $ign))
									{
										
										$maxCount[$k]++; 
										
										if (($cf($val) != $cf($address[$nn])) && (!(empty($val) && (empty($address[$nn]))))) { 
										
											//echo 'NOT MATCHED: '.$nn.': '.$cf($val).' | '.$cf($address[$nn])."<br />\n";
											/*
											$match = 0; 
											unset($matches[$k]); 
											break; 
											*/
										}
										else { 
											//echo 'MATCHED: '.$nn.': '.$cf($val).' | '.$cf($address[$nn])."<br />\n";
											$match++;
											$lastuid = (int)$ad['virtuemart_userinfo_id']; 
											
											
											$matches[$k]++;
											
										}
									}
								}
								
								
								
							}
							
							$maxmax = array_keys($maxCount, max($maxCount));
							$allmaxmax = (int)reset($maxmax); 
							
							if (!empty($matches)) {
								$maxs = array_keys($matches, max($matches));
								foreach ($maxs as $key) {
									$matchPercent = (int)round(($matches[$key] / $allmaxmax) * 100);
									
									
									return (int)$res[$key]['virtuemart_userinfo_id']; 
								}
							}
							
							//pair by email without exact match:
							if (!empty($res)) {
								$first = reset($res); 
								$n = reset($matches); 
								$matchPercent = (int)round(($n / $allmaxmax) * 100);
								return (int)$first['virtuemart_userinfo_id']; 
							}
							
				return 0;
		
		
	}
	public static function guessUserSTID($cart, &$matchPercent=0) {
		if (!empty($cart->selected_shipto)) {
			$matchPercent = 100; 
			return (int)$cart->selected_shipto; 
		}
		if (empty($cart->ST)) return self::guessUserBTID($cart, $matchPercent); 
		if (is_array($cart->ST) && (count($cart->ST) === 1)) return self::guessUserBTID($cart, $matchPercent); 
		if (!empty($cart->ST['virtuemart_userinfo_id'])) {
			$matchPercent = 100; 
			return (int)$cart->ST['virtuemart_userinfo_id'];
		}
		$user_id = JFactory::getUser()->get('id'); 
		
		$db = JFactory::getDBO(); 
		
		/*
		$q = 'select `name` from #__virtuemart_userfields where `published` = 1 and `type` != \'delimiter\' and `shipment` = 1'; 
		if (defined('VM_VERSION') && (VM_VERSION >= 3))
		{
			$q .= ' and `cart` = 0 '; 
		}
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$enabledFields = array(); 
		foreach ($res as $row) {
			$enabledFields[$row['name']] = $row['name']; 
		}
		*/
		$enabledFields = OPCUserFields::getEditableFieldsNames('ST'); 
		
		if (empty($user_id) && (!empty($GLOBALS['opc_new_user']))) $user_id = $GLOBALS['opc_new_user']; 
		if (!empty($user_id)) {
			$q = 'select * from #__virtuemart_userinfos where virtuemart_user_id = '.(int)$user_id.' and `address_type` = \'ST\''; 
		}
		else {
			//duplicate email case (user is not logged in and activation is waiting, lets get his address IDs)
			$q = 'select u.* from #__virtuemart_userinfos as u inner join #__users as j on ((j.`id` = u.virtuemart_user_id) and (j.email = \''.$db->escape($cart->BT['email']).'\')) where u.`address_type` = \'ST\''; 

		}
		
						$ign = array('virtuemart_userinfo_id', 'virtuemart_user_id', 'address_type', 'address_type_name', 'name', 'agreed', 'tos', '', 'created_on', 'created_by', 'modified_on', 'modified_by', 'locked_on', 'locked_by');  
						if (function_exists('mb_strtolower'))
						$cf = 'mb_strtolower'; 
						else $cf = 'strtolower'; 
						
						$db->setQuery($q); 
						$res = $db->loadAssocList(); 
							
					    $address = $cart->ST; 
						
						$matches = array(); 
						$maxCount = array(); 
						// user is already registered, but we need to fill some of the system fields
							foreach ($res as $k=>$ad)
							{
								$match = 0; 
								$maxCount[$k] = 0; 
								$matches[$k] = 0; 
								foreach ($ad as $nn=>$val)
								{
									//is available to ST:
									if (!isset($enabledFields[$nn])) continue; 
									if (!in_array($nn, $ign))
									{
										
										$maxCount[$k]++; 
										
										if (($cf($val) != $cf($address[$nn])) && (!(empty($val) && (empty($address[$nn]))))) { 
										
											//echo 'NOT MATCHED: '.$nn.': '.$cf($val).' | '.$cf($address[$nn])."<br />\n";
											$match = 0; 
											//unset($matches[$k]); 
											
										}
										else { 
											//echo 'MATCHED: '.$nn.': '.$cf($val).' | '.$cf($address[$nn])."<br />\n";
											$match++;
											$lastuid = (int)$ad['virtuemart_userinfo_id']; 
											
											
											$matches[$k]++;
											
										}
									}
								}
								
								
								
							}
							
							$maxmax = array_keys($maxCount, max($maxCount));
							$allmaxmax = (int)reset($maxmax); 
							
							//sorted by best match incl no match:
							if (!empty($matches)) {
								$maxs = array_keys($matches, max($matches));
								foreach ($maxs as $key) {
									$matchPercent = (int)round(($matches[$key] / $allmaxmax) * 100);
									if ($matches[$key] === $maxCount) {
										$cart->selected_shipto = (int)$res[$key]['virtuemart_userinfo_id'];
									}
									return (int)$res[$key]['virtuemart_userinfo_id']; 
								}
							}
							
							
							
				return 0;
		
		
	}
	/*
	
	ALTER TABLE `#__onepage_order_userinfos_ext` ADD `bt_match_percent` INT NOT NULL DEFAULT '0' AFTER `st_userinfo_id`, ADD `st_match_percent` INT NOT NULL DEFAULT '0' AFTER `bt_match_percent`, ADD `bt_addresshistory_id` INT(1) NULL DEFAULT NULL AFTER `st_match_percent`, ADD `st_addresshistory_id` INT(1) NULL DEFAULT NULL AFTER `bt_addresshistory_id`, ADD INDEX (`bt_addresshistory_id`), ADD INDEX (`st_addresshistory_id`);
	*/
	
	public static function checkSchema() {
		$clearCache = false; 
		if (!OPCmini::tableExists('onepage_order_userinfos_ext')) {
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_order_userinfos_ext` (
			`id` bigint(1) NOT NULL AUTO_INCREMENT,
			`virtuemart_order_id` int(1) UNSIGNED NOT NULL,
			`bt_userinfo_id` int(1) UNSIGNED NOT NULL,
			`st_userinfo_id` int(1) UNSIGNED NOT NULL,
			`bt_match_percent` int(1) NOT NULL DEFAULT \'0\',
			`st_match_percent` int(1) NOT NULL DEFAULT \'0\',
			`bt_addresshistory_id` bigint(1) DEFAULT NULL,
			`st_addresshistory_id` bigint(1) DEFAULT NULL,
			`person_id_bt` bigint(1) DEFAULT NULL,
			`person_id_st` bigint(1) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `order_id` (`virtuemart_order_id`),
			KEY `bt_userinfo_id` (`bt_userinfo_id`),
			KEY `st_userinfo_id` (`st_userinfo_id`),
			KEY `bt_addresshistory_id` (`bt_addresshistory_id`),
			KEY `st_addresshistory_id` (`st_addresshistory_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$db->execute();
			$clearCache = true; 
		}
		else {
			$def = OPCmini::getColumns('#__onepage_order_userinfos_ext');
			$db = JFactory::getDBO(); 
			
		if (!isset($def['bt_match_percent'])) {
			
			$q = 'ALTER TABLE `#__onepage_order_userinfos_ext` ADD `bt_match_percent` INT NOT NULL DEFAULT \'0\' AFTER `st_userinfo_id`, ADD `st_match_percent` INT NOT NULL DEFAULT \'0\' AFTER `bt_match_percent`, ADD `bt_addresshistory_id` INT(1) NULL DEFAULT NULL AFTER `st_match_percent`, ADD `st_addresshistory_id` INT(1) NULL DEFAULT NULL AFTER `bt_addresshistory_id`, ADD INDEX (`bt_addresshistory_id`), ADD INDEX (`st_addresshistory_id`);'; 
			$db->setQuery($q); 
			$db->execute(); 
			$clearCache = true; 
			
		}
		
		if (!isset($def['person_id_bt'])) {
			
			$q = 'ALTER TABLE `#__onepage_order_userinfos_ext` ADD `person_id_bt` BIGINT(1) DEFAULT NULL AFTER `st_addresshistory_id`, ADD `person_id_st` BIGINT DEFAULT NULL AFTER `person_id_bt`;';
			$db->setQuery($q); 
			$db->execute(); 
			$clearCache = true; 
			
		}
		}
		
		
		if (!OPCmini::tableExists('onepage_address_history')) {
			$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_address_history` (
			`address_id` bigint(1) NOT NULL AUTO_INCREMENT,
			`person_id` BIGINT(1) NOT NULL DEFAULT \'0\',
			`address_md5` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci GENERATED ALWAYS AS (md5(concat(`virtuemart_user_id`,`address_type`))) VIRTUAL,
			`address_md5_st` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci GENERATED ALWAYS AS (md5(concat(`virtuemart_user_id`,`address_type`))) VIRTUAL,
			`virtuemart_user_id` int(1) NOT NULL,
			`address_type` enum(\'BT\',\'ST\',\'RD\',\'\') NOT NULL,
			PRIMARY KEY (`address_id`),
			UNIQUE KEY `hash_index` (`virtuemart_user_id`,`address_type`, `address_md5`),
			UNIQUE KEY `hash_index_st` (`virtuemart_user_id`,`address_type`, `address_md5_st`),
			KEY `person_id` (`person_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'; 
			try {
				$db->setQuery($q); 
				$db->execute(); 
			}
			catch(Exception $e) {
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
					
				}
				OPCloader::storeError('address_history', $e); 
				return false; 
			}
			$clearCache = true; 
		}
		
		$private_address_fields = OPCconfig::get('private_address_fields', array()); 
		if (!empty($private_address_fields)) 
		if (!OPCmini::tableExists('onepage_address_person_history')) {
			$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_address_person_history` (
  `person_id` bigint(1) NOT NULL AUTO_INCREMENT,
  `person_md5` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci AS (MD5(`email`)) VIRTUAL,
  `email` varchar(160) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`person_id`),
  KEY `person_hash` (`person_md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'; 
				$db->setQuery($q); 
				$db->execute(); 
				$clearCache = true; 
		}
		
		if ($clearCache) {
			OPCmini::clearTableExistsCache(); 
		}
		return true; 
	}
	
	public static function storeOrderUserInfosIds($cart, $order_id) {
		
		$bt_match_percent = 0; 
		$st_match_percent = 0; 
		
		$opc_address_history = OPCconfig::get('opc_address_history', false); 
		
		
		
		if (empty($opc_address_history)) return; 
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php');  
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php');  
		
		try 
		{
			if (!self::checkSchema()) return; 
		}
		catch (Exception $e) {
			//unsupported mysql versionr
			$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
					
				}
				OPCloader::storeError('address_history', $e); 
			return; 
		}
		
		
		
		if (isset(OPCUser::$opc_st_user_info_id)) {
			$st_user_info_id = (int)OPCUser::$opc_st_user_info_id;
		}
		else {
			if ($cart->STsameAsBT === 1) {
				$st_user_info_id = self::guessUserBTID($cart, $bt_match_percent); 
			}
			else {
				if (!empty($cart->selected_shipto)) {
					$st_user_info_id = (int)$cart->selected_shipto;
				}
				else {
					$st_user_info_id = self::guessUserSTID($cart, $st_match_percent); 
				}
			}
		}
		
		if (isset(OPCUser::$opc_bt_user_info_id)) {
			$bt_user_info_id = (int)OPCUser::$opc_bt_user_info_id;
		}
		else {
			$bt_user_info_id = self::guessUserBTID($cart, $bt_match_percent); 
		}
		
		
		
		
		$order_id = (int)$order_id; 
		$bt_user_info_id = (int)$bt_user_info_id; 
		$st_user_info_id = (int)$st_user_info_id; 
		
		
		if (empty($st_user_info_id)) $st_user_info_id = $bt_user_info_id; 
		if (empty($bt_user_info_id)) $bt_user_info_id = 0; 
		
		$adr_data = array(); 
		$adr_data['id'] = 'NULL'; 
		$adr_data['virtuemart_order_id'] = (int)$order_id;
		$adr_data['bt_userinfo_id'] = (int)$bt_user_info_id; 
		$adr_data['st_userinfo_id'] = (int)$st_user_info_id; 
		
		$adr_data['bt_match_percent'] = (int)$bt_match_percent; 
		$adr_data['st_match_percent'] = (int)$st_match_percent; 
		
		$adr_data['bt_addresshistory_id'] = 'NULL'; 
		$adr_data['st_addresshistory_id'] = 'NULL'; 
		
		$adr_data['person_id_bt'] = 'NULL'; 
		$adr_data['person_id_st'] = 'NULL'; 
		
		
		
		try {
			self::storeAddressHistory($order_id, $adr_data); 
		}
		catch (Exception $e) {
			//incompatible DB... 
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
				}
					OPCloader::storeError('address_history', $e); 
			return; 
		}
			
		
		
		
		
		
		/*
		$db = JFactory::getDBO(); 
		$q = 'select `id` from #__onepage_order_userinfos_ext where `virtuemart_order_id` = '.(int)$order_id; 
		$db->setQuery($q); 
		$id = $db->loadResult(); 
		if (!empty($id)) {
			$q = 'update `#__onepage_order_userinfos_ext` set `bt_userinfo_id` = '.(int)$bt_user_info_id.', `st_userinfo_id` = '.(int)$st_user_info_id.' where `id` = '.(int)$id; 
			$db->setQuery($q); 
			$db->execute(); 
			return; 
		}
		else {
			$q = 'insert into `#__onepage_order_userinfos_ext` (`id`, `virtuemart_order_id`, `bt_userinfo_id`, `st_userinfo_id`) values (NULL, '.(int)$order_id.', '.(int)$bt_user_info_id.', '.(int)$st_user_info_id.')'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		*/
		
		
	}
	
	public static function storeAddressHistory($order_id, &$adr_data) {
		$order_id = (int)$order_id; 
		if (empty($order_id)) return; 
		$db = JFactory::getDBO(); 
		$q = 'select * from #__virtuemart_order_userinfos where `virtuemart_order_id` = '.(int)$order_id; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$BT = array(); 
		$ST = array(); 
		$RD = array(); 
		
		foreach ($res as $row) {
			if ($row['address_type'] === 'BT') {
				$BT = $row; 
			}
			if ($row['address_type'] === 'ST') {
				$ST = $row; 
			}
			if ($row['address_type'] === 'RD') {
				$RD = $row; 
			}
		}
		
		if (empty($BT)) return; 
		if (empty($ST)) $ST = $BT; 
		
		$BTrelevant = OPCUserFields::getEditableFieldsNames('BT'); 
		$STrelevant = OPCUserFields::getEditableFieldsNames('ST'); 
		
		$private_address_fields = OPCconfig::get('private_address_fields', array()); 
		
		$calculated_bt = array(); 
		$calculated_st = array(); 
		$calculated_private_bt = array(); 
		$calculated_private_st = array(); 
		
		
		$calculated_private_bt['email'] = '`email`, \'~||~\'';
		$calculated_private_st['email'] = '`email`, \'~||~\'';
		
		$all_cols = array(); 
		
		$privateBT = array(); 
		$privateST = array(); 
		
		$newBT = array(); 
		foreach ($BTrelevant as $fn=>$fnv) {
			if (empty($BT[$fn])) {
				$BT[$fn] = ''; 
				$newBT[$fn] = ''; 
				
			}
			
			$calculated_bt[$fn] = '`'.$db->escape($fn).'`,\'~||~\'';
			
			//NULL,0 or empty
			$all_cols[$fn] = $fn; 
			
			$BT[$fn]= trim($BT[$fn]); 
			if (mb_strlen($BT[$fn]) > 160) $BT[$fn] = mb_substr($BT[$fn], 0, 160); 
			$newBT[$fn] = $BT[$fn]; 
			$calculated_bt_hash[$fn] = $newBT[$fn].'~||~'; 
			
			if (in_array($fn, $private_address_fields)) {
				$calculated_private_bt[$fn] = '`'.$db->escape($fn).'`, \'~||~\'';
				$calculated_private_bt_hash[$fn] = $newBT[$fn].'~||~'; 
				$privateBT[$fn] = $BT[$fn]; 
			}
			
		}
		$newBT['address_type'] = 'BT'; 
		$newBT['virtuemart_user_id'] = $BT['virtuemart_user_id']; 
		
		$newST = array(); 
		$newST['address_type'] = 'ST'; 
		
		
		foreach ($STrelevant as $fn=>$fnv) {
			//NULL,0 or empty
			$all_cols[$fn] = $fn; 
			$calculated_st[$fn] = '`'.$db->escape($fn).'`,\'~||~\'';
			if (empty($ST[$fn])) {
				$ST[$fn] = ''; 
				$newST[$fn] = ''; 
				
			}
			
			
			
			$ST[$fn]= trim($ST[$fn]); 
			//cut the address so we can compare it:
			if (mb_strlen($ST[$fn]) > 160) $ST[$fn] = mb_substr($ST[$fn], 0, 160); 
			$newST[$fn] = $ST[$fn]; 
			$calculated_st_hash[$fn] = $newST[$fn].'~||~'; 
			
			if (in_array($fn, $private_address_fields)) {
				$calculated_private_st[$fn] = '`'.$db->escape($fn).'`, \'~||~\'';
				$calculated_private_st_hash[$fn] = $ST[$fn].'~||~'; 
				$privateST[$fn] = $ST[$fn]; 
			}
			
		}
		$newST['virtuemart_user_id'] = $ST['virtuemart_user_id']; 
		
		//alter table `g52p3_onepage_address_history` add `address_md5_st` CHAR(32) GENERATED ALWAYS AS (MD5(CONCAT(`virtuemart_user_id`, `address_type`))) VIRTUAL after `address_md5`
		//alter table `g52p3_onepage_address_history` CHANGE `address_md5` `address_md5` CHAR(32) AS (MD5(CONCAT(`virtuemart_user_id`, `address_type`))) VIRTUAL
		
		//is ST subset of BT:
		$match = 1;  //one for address_type
		foreach ($newST as $testK=>$testV) {
			if (isset($newBT[$testK]) && ($newST[$testK] === $newBT[$testK])) {
				$match++; 
			}
		}
		if ($match === count($newST)) {
			//st same as bt
		}
		
		$def = OPCmini::getColumns('onepage_address_history'); 
		$q = 'ALTER TABLE #__onepage_address_history '; 
		$add = array(); 
		$db = JFactory::getDBO(); 
		foreach ($all_cols as $colname) {
			if (!isset($def[$colname])) {
				$add[] = 'ADD COLUMN `'.$db->escape($colname).'` VARCHAR(160) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\''; 
			}
		}
		if (!empty($add)) {
			//block other processes altering table at once
			$qx = 'START TRANSACTION'; 
			$db->setQuery($qx); 
			$db->execute(); 
			
			$q .= implode(',', $add).';'; 
			//echo $q; 
			try {
			$db->setQuery($q); 
			$db->execute(); 
			}
			catch (Exception $e) {
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
				}
				OPCloader::storeError('address_history', $e); 
			}
			
			
			$qx = 'COMMIT'; 
			$db->setQuery($qx); 
			$db->execute(); 
			
			OPCmini::clearTableExistsCache(); 
		}
		
		 $bt_test = false; $st_test = false; 
		 
		 
		 
		 $q = 'SHOW CREATE TABLE #__onepage_address_history'; 
		 $db->setQuery($q); 
		 $res = $db->loadAssoc(); 
		 $create_table = $res['Create Table']; 
		 if (!empty($create_table)) {
		 $bt_needle = implode(',', $calculated_bt); 
		 $st_needle = implode(',', $calculated_st); 
		 $bt_test = strpos($create_table, $bt_needle); 
		 $st_test = strpos($create_table, $st_needle); 
		 }
		if ($bt_test === false) {
			$q = 'ALTER TABLE #__onepage_address_history CHANGE `address_md5` `address_md5` CHAR(32) CHARACTER SET latin1 COLLATE latin1_general_ci AS (MD5(CONCAT('.$bt_needle.'))) VIRTUAL'; 
			$q .= ' COMMENT \''.$db->escape($q).'\'';
			$db->setQuery($q); 
			$db->execute(); 
		}
		if ($st_test === false) {
			$q = 'ALTER TABLE #__onepage_address_history CHANGE `address_md5_st` `address_md5_st` CHAR(32) CHARACTER SET latin1 COLLATE latin1_general_ci AS (MD5(CONCAT('.$st_needle.'))) VIRTUAL'; 
			$q .= ' COMMENT \''.$db->escape($q).'\''; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
		
		
		if (!empty($private_address_fields)) {
			
		$def2 = OPCmini::getColumns('onepage_address_person_history'); 
		$q = 'ALTER TABLE #__onepage_address_person_history '; 
		$add = array(); 
		
		foreach ($private_address_fields as $colname) {
			if (!isset($def2[$colname])) {
				$add[] = 'ADD COLUMN `'.$db->escape($colname).'` VARCHAR(160) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\''; 
			}
		}
		if (!empty($add)) {
			//block other processes altering table at once
			$qx = 'START TRANSACTION'; 
			$db->setQuery($qx); 
			$db->execute(); 
			
			$q .= implode(',', $add).';'; 
			//echo $q; 
			try {
			$db->setQuery($q); 
			$db->execute(); 
			}
			catch (Exception $e) {
				$opc_debug = OPCconfig::get('opc_debug', false); 
				if ((OPCmini::isSuperVendor()) || ($opc_debug)) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error'); 
				}
				OPCloader::storeError('address_history', $e); 
			}
			
			
			$qx = 'COMMIT'; 
			$db->setQuery($qx); 
			$db->execute(); 
			
			OPCmini::clearTableExistsCache(); 
		}
		
		 $bt_test = false; $st_test = false; 
		 
		 $q = 'SHOW CREATE TABLE #__onepage_address_person_history'; 
		 $db->setQuery($q); 
		 $res = $db->loadAssoc(); 
		 $create_table = $res['Create Table']; 
		 if (!empty($create_table)) {
		 $bt_needle = implode(',', $calculated_private_bt); 
		 $st_needle = implode(',', $calculated_private_st); 
		 $private_bt_test = strpos($create_table, $bt_needle); 
		 $private_st_test = strpos($create_table, $st_needle); 
		 }
		if ($private_bt_test === false) {
			$q = 'ALTER TABLE #__onepage_address_person_history CHANGE `person_md5` `person_md5` CHAR(32) CHARACTER SET latin1 COLLATE latin1_general_ci AS (MD5(CONCAT('.$bt_needle.'))) VIRTUAL'; 
			$q .= ' COMMENT \''.$db->escape($q).'\'';
			$db->setQuery($q); 
			$db->execute(); 
		}
		
		$privateBT['person_id'] = 'NULL'; 
		$privateBT['email'] = $BT['email']; 
		$privateST['email'] = $BT['email']; 
		OPCMini::insertArray('#__onepage_address_person_history', $privateBT); 
		if (!empty($ST['email'])) {
			$privateST['email'] = $ST['email']; 
		}
		$privateST['person_id'] = 'NULL'; 
		OPCMini::insertArray('#__onepage_address_person_history', $privateST);
		
		}
		
		if (!empty($privateBT['person_id']) && ($privateBT['person_id'] !== 'NULL')) {
			$newBT['person_id'] = (int)$privateBT['person_id']; 
		}
		if (!empty($privateST['person_id']) && ($privateST['person_id'] !== 'NULL')) {
			$newST['person_id'] = (int)$privateST['person_id']; 
		}
		
		$toMd5BT = implode('', $calculated_bt_hash); 
		$toMd5ST = implode('', $calculated_st_hash); 
		
		$testMd5BT = md5($toMd5BT); 
		$testMd5ST = md5($toMd5ST); 
		
		$newBT['address_md5'] = $testMd5BT; 
		$newBT['address_id'] = 'NULL'; 
		OPCMini::insertArray('#__onepage_address_history', $newBT); 
		
		$newST['address_md5_st'] = $testMd5ST; 
		$newST['address_id'] = 'NULL'; 
		OPCMini::insertArray('#__onepage_address_history', $newST); 
		
		
		
		
		
		$adr_data['bt_addresshistory_id'] = 'NULL'; 
		$adr_data['st_addresshistory_id'] = 'NULL'; 
		
		if ((!empty($newBT['address_id'])) && ($newBT['address_id'] !== 'NULL')) {
			$adr_data['bt_addresshistory_id'] = (int)$newBT['address_id']; 
		}
		
		if ((!empty($newST['address_id'])) && ($newST['address_id'] !== 'NULL')) {
			$adr_data['st_addresshistory_id'] = (int)$newST['address_id']; 
		}
		
		$adr_data['person_id_bt'] = 0; 
		$adr_data['person_id_st'] = 0; 
		
		if ((!empty($privateBT['person_id'])) && ($privateBT['person_id'] !== 'NULL')) {
			$adr_data['person_id_bt'] = (int)$privateBT['person_id']; 
		}
		if ((!empty($privateST['person_id'])) && ($privateST['person_id'] !== 'NULL')) {
			$adr_data['person_id_st'] = (int)$privateST['person_id']; 
		}
		
		
		
		OPCMini::insertArray('#__onepage_order_userinfos_ext', $adr_data); 
		
	}

	

}