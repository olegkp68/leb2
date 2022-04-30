<?php
/**
 * @version		orderserverinfo.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

if (!defined('JPATH_VM_PLUGINS'))
{
   if (!class_exists('VmConfig'))
   require(JPATH_ADMINISTRATOR. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_virtuemart'. DIRECTORY_SEPARATOR .'helpers'. DIRECTORY_SEPARATOR .'config.php'); 
		 
   VmConfig::loadConfig(); 
}

if (!class_exists('vmPSPlugin')) {
   
	require(JPATH_SITE. DIRECTORY_SEPARATOR .'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_virtuemart'. DIRECTORY_SEPARATOR .'plugins'. DIRECTORY_SEPARATOR .'vmpsplugin.php');
}


class plgVmPaymentOrderserverinfo extends vmPSPlugin
{


	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}
		
   // this is called as the first event after creating an order, 
   // but before any payment or shipping triggers are called
   // it pairs the cookie with the actual order 
  
  public function plgOpcOrderCreated($cart, $order)
  {
    $this->plgVmConfirmedOrder2($cart, $order); 
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
  
  function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id)
	{

	  $app = JFactory::getApplication();
		if ($app->getName() === 'administrator') {
			try { 
			if (!self::tableExists('virtuemart_orderserverinfo')) {
				$this->_createTable(); 
			}
			}
			catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage('plg_vmpayment_orderserverinfo: THIS PLUGIN REQUIRES MYSQL 5.7.8 AND LATER ! DISABLE THE PLUGIN IF YOU ARE NOT USING A COMPATIBLE MYSQL VERSION', 'error'); 
			}
		}
  }
  
  private function _createTable() {
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
		$db->setQuery($q); 
		$db->execute(); 

  }
  
  
	public function checkConditions ($cart, $method, $cart_prices) {
	  return null; 
	}
	public function plgVmOnSelectCheckPayment($id)
	{
	 return null; 
	}
    
	public function plgVmConfirmedOrderOPCExcept ($except, &$cart, &$order)
	{
	  $this->plgVmConfirmedOrder2($cart, $order); 
	}
	
	
	public function plgVmConfirmedOrder($cart, $order)
	{
	 $this->plgVmConfirmedOrder2($cart, $order); 
	}
	
	public function plgVmConfirmedOrder2($cart, $data)
	{
	
	  if (!is_object($data))
	  {
	  if (isset($data['details']['BT']))
	  {
	   //self::$delay = true; 
	   $order = $data['details']['BT']; 
	   
	   
	   
	   $this->_registerOrder($order->virtuemart_order_id);  
	  }
	  }
	  else
	  if (isset($data->virtuemart_order_id))
	  {
		
		  
		  
	       
		 
	       $this->_registerOrder($data->virtuemart_order_id);  
		   
		  
		  
		  
	  }
	}
	
	public function _registerOrder($virtuemart_order_id) {

		if (php_sapi_name() === 'cli') return false; 
		
	  	$app = JFactory::getApplication();
		if ($app->getName() !== 'site') {
			return false;
		}
		
		$virtuemart_order_id = (int)$virtuemart_order_id; 
		if (empty($virtuemart_order_id)) return false; 
		/*
		if ($virtuemart_order_id === 1) {
			$x =debug_backtrace(); 
			foreach ($x as $l) echo $l['file'].' '.$l['line']."<br />\n"; 
			die('err orderserverinfo'); 
		}
		*/
		
		//DO NOT STORE TWICE: 
		static $done; 
		if (!empty($done[$virtuemart_order_id])) return; 
		
		$db = JFactory::getDBO(); 
		
		try { 
		
		
		$q = 'select `virtuemart_order_id` from `#__virtuemart_orderserverinfo` where  `virtuemart_order_id` = '.(int)$virtuemart_order_id; 
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		
		}
		catch (Exception $e) {
		  //probably not on mysql 5.7 !	
		  echo (string)$e; 
		    var_dump($e); die(); 
		  return; 
		}
		
		if (!empty($res)) {
			$done[$virtuemart_order_id] = true; 
			return; 
		}
		
		$arr = array(); 
		if ($this->params->get('log_server', true)) {
		if (empty($_SERVER)) $_SERVER = array(); 
		   $server = json_encode($_SERVER); 
		}
		else {
			$server = json_encode($arr); 
		}
		
		if ($this->params->get('log_post', true)) {
		if (empty($_POST)) $_POST = array(); 
		$post = json_encode($_POST); 
		}
		else {
			$post = json_encode($arr); 
		}
		
		if ($this->params->get('log_post', true)) {
		if (empty($_GET)) $_GET = array(); 
		  $get = json_encode($_GET); 
		}
		else {
			$get = json_encode($arr); 
		}
		
		if ($this->params->get('log_cookie', true)) {
		if (!empty($_SERVER))  {
		 $cookie = json_encode($_COOKIE); 
		}
		else {
			$cookie = json_encode($arr); 
		}
		}
		else {
			$cookie = json_encode($arr); 
		}
		$extra = json_encode($arr); 
		
		$q = 'insert into `#__virtuemart_orderserverinfo` (`id`, `virtuemart_order_id`, `_SERVER`, `_POST`, `_GET`, `_COOKIE`, `_EXTRA`) values (NULL, '.(int)$virtuemart_order_id.", '".$db->escape($server)."', '".$db->escape($post)."', '".$db->escape($get)."', '".$db->escape($cookie)."', '".$db->escape($extra)."')"; 
		
		try { 
		 $db->setQuery($q); 
		 $db->execute(); 
		}
		catch (Exception $e) {
		  //probably not on mysql 5.7 !	
		  var_dump($e); die(); 
		}
		$done[$virtuemart_order_id] = true; 
	
	}
	
	public function plgVmOnUpdateOrderPayment(&$data,$old_order_status)
	{
	  
	 
	  
	}
	
	
	public function plgVmCouponUpdateOrderStatus($data,$old_order_status)
	{
	  
	  $this->plgVmConfirmedOrder2(null, $data);  
	  
	  
	}

	
	
	
	

	
	
	
	
	public function plgVmOnUpdateOrderShipment(&$data,$old_order_status)
	{
	
	
	  $this->plgVmConfirmedOrder2(null, $data);  
	    
	   
	
	}
	
	
	
	

		
}
