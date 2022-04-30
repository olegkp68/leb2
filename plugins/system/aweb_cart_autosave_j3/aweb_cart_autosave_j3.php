<?php
/**
 * @package Plugin aWeb_Cart_AutoSave for Joomla! 3.x
 * @version 1.04
 * @author aWebSupport Team
 * @copyright (C) 2013-2015 aWebSupport.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;

class plgSystemaWeb_Cart_AutoSave_j3 extends JPlugin
{
	
	public function onAfterRoute()
	{
		$this->saveCart();	
	}

	protected function get_date()
	{
		$config =& JFactory::getConfig();
		$dtz = new DateTimeZone('GMT');
		$date = new DateTime(NULL, $dtz);
		return $date->format('Y-m-d H:i:s');		
	}
	
	protected function getCartProducts($mycart) 
	{
		// virtuemart 3.0 support			
		if (substr($mycart, 0,1)=='O')
		{
			$cart = unserialize($mycart);	
			$products = $cart->products;
		}
		else if (substr($mycart, 0,1)=='{') 
		{
			$cart = json_decode($mycart);		
			$products = $cart->cartProductsData;	
		}
		return $products;
	}
	
	protected function isSameCart($cart1,$cart2)
	{
		// virtuemart 3.0 support			
		if (substr($cart1, 0,1)=='O')
		{
			$mycart1 = unserialize($cart1);	
			$mycart2 = unserialize($cart2);		
			if ($mycart1===FALSE) return false;
			if ($mycart2===FALSE) return false;		
			$products1 = $mycart1->products;
			$products2 = $mycart2->products;			
			if (!isset($products1)) return false;
			if (!isset($products2)) return false;
		}
		else if (substr($cart1, 0,1)=='{') 
		{
			$cart1 = json_decode($cart1);
			$cart2 = json_decode($cart2);
			$products1 = $cart1->cartProductsData;
			$products2 = $cart2->cartProductsData;
		}		
		$prod1 = array();
		$prod2 = array();	
		foreach ($products1 as $k => $row)
		{
			$amount = 0;			
			if (is_numeric($row->quantity)) { $amount = $row->quantity; }
			if (is_numeric($row->amount)) { $amount = $row->amount; }			
			$prod1[$row->virtuemart_product_id] = $amount;			
		}		
		foreach ($products2 as $k => $row)
		{
			$amount = 0;			
			if (is_numeric($row->quantity)) { $amount = $row->quantity; }
			if (is_numeric($row->amount)) { $amount = $row->amount; }			
			$prod2[$row->virtuemart_product_id] = $amount;			
		}
		
		foreach ($prod1 as $k => $v)
		{
			if ($v != $prod2[$k]) return false;
		}

		foreach ($prod2 as $k => $v)
		{
			if ($v != $prod1[$k]) return false;
		}
		return true;
	}

	protected function changedCart($data,$userid)
	{	
			$db = JFactory::getDBO();			
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->getPrefix()."awebsavedcart");
			$query->where('userid ='.$userid);
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			foreach ( $rows as $row ) {	
				$data2 = $row->data;
				return !$this->isSameCart($data,$data2);				
			}				
			return true;
	}
	
	function saveCart()
	{
		$user =& JFactory::getUser();
		$userid = $user->get('id');		
		if ($userid!=0)
		{				
			$db = JFactory::getDBO();				
			$session = JFactory::getSession();			
			if ($session->get('vmcart', null, 'vm')!=null)
			{
				$rawdata =  $session->get('vmcart', null, 'vm');
				$products = $this->getCartProducts($rawdata);
				$cartsize = count($products);						
				if ($cartsize>0)
				{		
					$db = JFactory::getDBO();
					if ($db->name == "mysql") $data = mysql_real_escape_string( $rawdata);
					else $data = mysqli_real_escape_string($db->getConnection(), $rawdata); 
					$now = $this->get_date();		
					$compr = 0;
					
					if ($this->changedCart($rawdata,$userid) == true) 
					{
						$q="INSERT INTO ".$db->getPrefix()."awebsavedcart (userid,data,date,compr) VALUES ('".$userid."','".$data."','".$now."','".$compr."')";
						$q.=" ON DUPLICATE KEY UPDATE data='".$data."',date='".$now."',compr='".$compr."'";    
						$db->setQuery($q);
						$db->query();	
					}
				}
				else {
					$q="DELETE FROM ".$db->getPrefix()."awebsavedcart where userid='".$userid."'";
					$db->setQuery($q);
					$db->query();																		
				}
			}
			
		}
	}
	
}