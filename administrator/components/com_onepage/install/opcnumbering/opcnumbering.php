<?php
/**
 * @version		opcnumbering.php 
 * @copyright	Copyright (C) 2005 - 2015 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemOpcnumbering extends JPlugin
{
    function __construct(& $subject, $config)
	{
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php')) return; 
		
		parent::__construct($subject, $config);
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	}
	
	private function canRun()
	{
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'))
			{
				
				return false;
			}
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
				require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'numbering.php'); 
	
	
	   //OPCNumbering::$debug = false; 
	   return true; 
	}
	
	public function plgVmOnUserInvoice($orderDetails,&$data)
	{
		if (!$this->canRun()) return; 
		
		
		
		$order_agenda_id = (int)OPCconfig::get('order_numbering', 0);
			
		
		//$data['invoice_number']
		$agenda_id = (int)OPCconfig::get('invoice_numbering', 0); 
		
		
		
		if (empty($agenda_id)) return; 
		
		//stAn - we need to check this here since VM on some broken versions always creates the invoice number:
		$orderstatusForInvoice = VmConfig::get('inv_os',array('C'));
		
		
		$order_id = null; 
		
		if (is_object($orderDetails))
		{
			$order_status1 = $orderDetails->order_status; 
			
		if (!empty($orderDetails->virtuemart_order_id ))
		$order_id = $orderDetails->virtuemart_order_id; 
	    if (!empty($orderDetails->order_number ))
			$number = $orderDetails->order_number;
			
		}
		else
		{
			$order_status1 = $orderDetails['order_status']; 
			
			if (!empty($orderDetails['details']['BT']))
			if (is_object($orderDetails['details']['BT']))
			$order_id = $orderDetails['details']['BT']->virtuemart_order_id; 
			
			if (empty($order_id))
			{
				if (is_array($orderDetails))
				{
					if (!empty($orderDetails['virtuemart_order_id']))
					{
						$order_id = $orderDetails['virtuemart_order_id']; 
					}
					
					$number = $orderDetails['order_number']; 
				}
			}
			
			
			if (isset($orderDetails['virtuemart_order_id'])) {
				$order_id = (int)$orderDetails['virtuemart_order_id']; 
				
			}
			if (isset($orderDetails['order_number'])) {
				$number = $orderDetails['order_number'];
			}
			
			//virtuemart_order_id
		}
		
		 //stAn - to fix vm3.4.3 we need to check the VM config for this since the function is always triggered
		 $force_create = false; 
		 $app = JFactory::getApplication();
		 if( $app->isAdmin() ) {
			$force_create = true; 
		 }
		if (empty($force_create)) {
		if (empty($orderstatusForInvoice)) return; 
		if (!is_array($orderstatusForInvoice)) {
			$orderstatusForInvoice = array($orderstatusForInvoice); 
		}
		
		if (!in_array($order_status1, $orderstatusForInvoice)) return; 
		}
		
		
		 if ($agenda_id === $order_agenda_id)
		 {
			 
			 //if (substr($number, -2) === '-1') $number = substr($number, 0, -2); 
		 }
		 
		if ((!empty($order_id)) && (!empty($number)))
				{
					
					OPCNumbering::updateTypeid($agenda_id, OPCNumbering::TYPE_ID_ORDER, $order_id, $number);
					$updated = true; 
					
					
				}
		
		
		if (($agenda_id === 1) && ($agenda_id === $order_agenda_id))
		{
			if (!empty($number)) {
				$numbering = $number; 
			}
		}
		
		if (empty($numbering)) {
		 $numbering = OPCNumbering::requestNew($agenda_id, OPCNumbering::TYPE_ID_INVOICE, $order_id);
		}
		
		
		if (!empty($numbering))
		{
		$db = JFactory::getDBO(); 
		$q = 'select * from `#__virtuemart_invoices` where `virtuemart_order_id` = '.(int)$order_id.' and `invoice_number` LIKE \''.$db->escape($numbering).'\' limit 1'; 
		$db->setQuery($q); 
		$cd = $db->loadAssoc(); 
		if (!empty($cd)) {
			$data = (array)$cd; 
		}
			
		$data['invoice_number'] = $numbering; 
		
		if (empty(self::$mydata)) self::$mydata = array(); 
		if (empty(self::$mydata[OPCNumbering::TYPE_ID_INVOICE])) self::$mydata[OPCNumbering::TYPE_ID_INVOICE] = array(); 
		self::$mydata[OPCNumbering::TYPE_ID_INVOICE][] = $numbering; 
		}
		
	}
	public static $mydata; 
	//plgVmConfirmedOrder
	public function plgVmConfirmedOrder($cart, $order)
	{
		
		if (!$this->canRun()) return; 
		
		
		
		$order_number = $order['details']['BT']->order_number; 
		$order_id = 0;
		$order_id = $order['details']['BT']->virtuemart_order_id; 
		$order_status = $order['details']['BT']->order_status; 
		$updated = false; 
		$agenda_id = OPCconfig::get('order_numbering', false);
		
		if (!empty(self::$mydata))
		{
			foreach (self::$mydata as $k=>$v)
			{
				// order: 
				if (empty($k))
				{
				foreach ($v as $number)
				{
				
				
				if ($order_number == $number)
				{
					
				}
				else
				{
					$db = JFactory::getDBO(); 
					$q = "select virtuemart_order_id from #__virtuemart_orders where `order_number` = '".$db->escape($number)."' order by `created_on` desc limit 0,1"; 
					$db->setQuery($q); 
					$order_id = $db->loadResult(); 
				}
				
				if (!empty($order_id))
				{
					OPCNumbering::updateTypeid($agenda_id, OPCNumbering::TYPE_ID_ORDER, $order_id, $number);
					$updated = true; 
					
					
				}
				}
				}
			}
		}
		
		
		
		if (empty($updated))
		{
			OPCNumbering::updateTypeidByNumber($agenda_id, OPCNumbering::TYPE_ID_ORDER, $order_id, $order_number);
		}
		
		$order_id = $order['details']['BT']->virtuemart_order_id; 
		
		
		
	}
	public function plgVmOnUserOrder(&$orderDetails)
	{
		
			
		
		//$_orderData->order_number
		if (!$this->canRun()) 
		{
			
			return; 
		}
		$agenda_id = OPCconfig::get('order_numbering', false); 
		
		$customer_numbering_agenda_id = (int)OPCconfig::get('customer_numbering', 0); 
		
		if (empty($agenda_id)) 
		{
			
			return;
		}
		$order_id = null; 
		
		if (is_object($orderDetails))
		{
		if (!empty($orderDetails->virtuemart_order_id )) {
		 $order_id = $orderDetails->virtuemart_order_id; 
		 $email = $orderDetails->email; 
		}
	    
		}
		else
		{
			if (!empty($orderDetails['details']['BT'])) {
			$order_id = $orderDetails['details']['BT']->virtuemart_order_id; 
			$email = $orderDetails['details']['BT']->email; 
			}
			
			
		}
		
		if (empty($email)) {
			$email = JFactory::getUser()->get('email'); 
		}
		
		$numbering = OPCNumbering::requestNew($agenda_id, OPCNumbering::TYPE_ID_ORDER, $order_id); 
		//0 is a reserved number
		/*
		if (empty($order_id)) {
		 $numbering = OPCNumbering::requestNew($agenda_id, 0, $order_id); 
		}
		else {
			$numbering = OPCNumbering::requestNew($agenda_id, 1, $order_id); 
		}
		*/
		
	
		
		if (!empty($numbering))
		{
		
		$orderDetails->order_number = $numbering; 
		
		
		if ($customer_numbering_agenda_id === $agenda_id) {
			$orderDetails->customer_number = $numbering; 
		}
		elseif (!empty($customer_numbering_agenda_id)) {
			if ($customer_numbering_agenda_id >= 1) {
				$email_id = OPCNumbering::getEmailInt($email); 
				
				$customer_numbering = OPCNumbering::requestNew($customer_numbering_agenda_id, OPCNumbering::TYPE_ID_OTHER, $email_id); 
				if (!empty($customer_numbering)) {
					$orderDetails->customer_number = $customer_numbering; 
				}
			}
		}
		
				if (empty(self::$mydata)) self::$mydata = array(); 
		if (empty(self::$mydata[OPCNumbering::TYPE_ID_ORDER])) self::$mydata[OPCNumbering::TYPE_ID_ORDER] = array(); 
		self::$mydata[OPCNumbering::TYPE_ID_ORDER][] = $numbering; 
	    return; 
		
		
		}
		
		
	}
	
	function plgVmOnBeforeUserfieldDataSave(&$valid,$user_id,&$data,$user ) {
		if (!$this->canRun()) 
		{
			
			return; 
		}
		
		$customer_numbering_agenda_id = (int)OPCconfig::get('customer_numbering', 0); 
		
		if (!empty($data['email'])) {
			$email = $data['email']; 
			$email_id = OPCNumbering::getEmailInt($email); 
			if ($customer_numbering_agenda_id >= 1) {
			$customer_numbering = OPCNumbering::requestNew($customer_numbering_agenda_id, OPCNumbering::TYPE_ID_OTHER, $email_id); 
				if (!empty($customer_numbering)) {
					$data['customer_number'] = $customer_numbering; 
				}
			}
		}
	}
	
}
