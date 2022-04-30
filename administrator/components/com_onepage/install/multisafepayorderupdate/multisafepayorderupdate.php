<?php
/**
 * @version		multisafepayorderupdate.php - updates Shipped state from Virtuemart event to MSP
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


class plgVmPaymentMultisafepayorderupdate extends vmPSPlugin
{
	function __construct(&$subject, $config)
    {
		parent::__construct($subject, $config); 
		
	}
	
	private function loadAPI() {
		require_once dirname(__FILE__) . "/API/Autoloader.php";
		if (!defined('BASE_URL')) {
			define('BASE_URL', ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . dirname($_SERVER['SCRIPT_NAME']) . "/");
		}
		if (!defined('API_KEY')) {
			define('API_KEY', $this->params->get('api_key', ''));
		}
		
		$url = $this->params->get('api_url', ''); 
		
		if (substr($url, -1) !== '/') $url .= '/'; 
		if (!defined('API_URL')) {
			define('API_URL', $url); //test is https://testapi.multisafepay.com/v1/json/, for live environment use https://api.multisafepay.com/v1/json/
		}
		if (!defined('TOOLKIT_VERSION')) {
			define('TOOLKIT_VERSION', '2.0.3');
		}
		
		$this->api_key = API_KEY;
		$this->api_url = API_URL;
		
	}
	
	public function plgOpcOrderStatusUpdate(&$order, $old_order_status) {
		$this->updateOrder($order); 	
	}
	
	public function plgVmOnUpdateOrderPayment(&$data,$old_order_status)
	{
		
		$this->updateOrder($data); 	
	}
	
	public function plgVmCouponUpdateOrderStatus($data,$old_order_status)
	{
		
		$this->updateOrder($data); 	
		
	}
	
	private function updateOrder($data) {
		$oa = self::getOrderIdandStatus($data); 
		
		if (empty($oa)) return; 
		$order_id = (int)$oa['order_id']; 
		$order_status = $oa['order_status']; 
		$order_number = $oa['order_number']; 
		$shipped_state = $this->params->get('shipped_state', 'S'); 
		
		if (!empty($order_number))
		if ($order_status === $shipped_state) {
			
			if (!defined('ORDER_UPDATED_'.$order_id)) {
			  $this->updateMSPOrder($order_number); 
			  define('ORDER_UPDATED_'.$order_id, true); 
			}
		}
	}
	
	private function updateMSPOrder($order_number)  {
		$this->loadAPI();  
		
		$msp = new \MultiSafepayAPI\Client;
		$msp->setApiKey($this->api_key);
		$msp->setApiUrl($this->api_url);
		
		$transactionid = $order_number;
		$endpoint = 'orders/' . $transactionid;
		
		
		$data = array( "tracktrace_code" => 'null',
                "carrier" => 'null',
                "ship_date" => date('Y-m-d H:i:s'),
                "reason" => 'Shipped' ); 
				
		try {
			$order = $msp->orders->patch($data , $endpoint);
		} catch (Exception $e) {
			$msg = "MSP Order Status Update Error " . $e->getMessage();
			JFactory::getApplication()->enqueueMessage($msg, 'error'); 
		}
	}
		
	
	
	private static function getOrderIdandStatus($data) {
	  
	  
	  if ((is_array($data)) && (isset($data['virtuemart_order_id']))) $order_id = $data['virtuemart_order_id']; 
			else
			if ((is_object($data)) && (isset($data->virtuemart_order_id))) 
				$order_id = $data->virtuemart_order_id; 
			if (empty($order_id) && (is_array($data)) && (isset($data['details']['BT'])) && (isset($data['details']['BT']->virtuemart_order_id))) $order_id = $data['details']['BT']->virtuemart_order_id; 
			$order_id = (int)$order_id; 
			if (empty($order_id)) return array(); 
	
	if ((is_array($data)) && (isset($data['order_status']))) $order_status = $data['order_status']; 
			else
			if ((is_object($data)) && (isset($data->order_status))) 
				$order_status = $data->order_status; 
			if (empty($order_status) && (is_array($data)) && (isset($data['details']['BT'])) && (isset($data['details']['BT']->order_status))) $order_status = $data['details']['BT']->order_status; 
			
			if (empty($order_status)) return array(); 
			
			$order_number = ''; 
			
			if ((is_array($data)) && (isset($data['order_number']))) $order_number = $data['order_number']; 
			else
			if ((is_object($data)) && (isset($data->order_number))) 
				$order_number = $data->order_number; 
			if (empty($order_number) && (is_array($data)) && (isset($data['details']['BT'])) && (isset($data['details']['BT']->order_number))) $order_number = $data['details']['BT']->order_number; 
			
			
			
			
			return array('order_id' => (int)$order_id, 'order_status'=>$order_status, 'order_number'=>$order_number); 
	  
	  
  }
	
	
	
	
}