<?php
/**
 * @copyright	Copyright (C) 2014 Holest Engineering www.holest.com.
 * @license		GNU General Public License version 2 or later
 */
defined('_JEXEC') or die('Restricted Access not allowed');
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR );

if($_REQUEST["auth_import"]){
	$user = JFactory::getUser();
	$app =JFactory::getApplication();
	
	if(isset($_REQUEST["auth_import"]) && isset($_REQUEST["password"])){
		
		$credentials = array();
		$credentials['username'] = $_REQUEST["auth_import"];
		$credentials['password'] = $_REQUEST["password"];
		
		$user = JFactory::getUser();
		
		if(!$user->id){
			$app->login($credentials);
			$user = JFactory::getUser();
		}else if($user->username != $credentials['username']){
			$app->logout();
			$app->login($credentials);
			$user = JFactory::getUser();
		}
		
		$user = JFactory::getUser();
		if($user->id){
			
			include( dirname(__FILE__) . DS . ".." . DS . ".." . DS .'administrator' . DS . 'components' . DS . 'com_vmexcellikeinput' . DS . 'vmexcellikeinput.php');
			die;
		}else{
			header('HTTP/1.0 403 Forbidden');
			echo "403 Forbidden";
		}
	}else{
		header('HTTP/1.0 403 Forbidden');
		echo "403 Forbidden";
	}
}else{
	header('HTTP/1.0 403 Forbidden');
	echo '403 Forbidden. This component does not have front-end features!';
}
exit;
die;
?>