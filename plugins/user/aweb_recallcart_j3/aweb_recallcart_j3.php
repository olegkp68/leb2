<?php
/**
 * @package Plugin aWeb_Recall_Cart for Joomla! 3.x
 * @version 1.04
 * @author aWebSupport Team
 * @copyright (C) 2013-2015 aWebSupport.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die( 'Restricted access' );

class plgUseraWeb_RecallCart_j3 extends JPlugin {

	public function onUserLogin ($user, $options = array())	
	{	
		$this->recallCart();		
		return true;
	}

	protected function debug($message)
	{
		$debugmode = $this->params->get('aweb_debugmode');
		if ($debugmode==1)
		{
			$fname = "aweblog.htm";
			$fp = fopen($fname, 'a');
			$now = date("Y-m-d H:i:s");
			fwrite($fp, $now."\n\r");
			fwrite($fp, $message."\n\r");
			fclose($fp);
		}		
	}

	protected function is_published($pruductid) 
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
 		$query = "SELECT published from ".$db->getPrefix()."virtuemart_products where virtuemart_product_id=".$pruductid;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ( $rows as $row ) {
			if ($row->published == 0) return false;
		}
		return true;
	}
	protected function get_stock_info($pruductid,$incart)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
 		$query = "SELECT product_in_stock, 	published from ".$db->getPrefix()."virtuemart_products where virtuemart_product_id=".$pruductid;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ( $rows as $row ) {
			$instock = $row->product_in_stock;
			if ($row->published == 0) $instock = 0;
		}		
		$stoccount = 0;
		if ($instock < $incart) $stoccount = $instock;
		else $stoccount=$incart;
		if ($stoccount<0) $stoccount=0;
		return $stoccount;
	}
	
	private function getOrdStatus()
	{
		$completed="";
		$completed = $this->params->get('aweb_completed_status_flag');
		if ($completed=="") $completed="C";		
		return $completed;
	}

	protected function is_ordered_cart($uid,$cartdate)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SELECT order_status as ords,o.created_on as date ";
		$query.= "FROM ".$db->getPrefix()."virtuemart_orders o ";
		$query.= "WHERE o.created_by=$uid and o.modified_on>='$cartdate'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ( $rows as $row ) {
			// if ($row->ords==$this->getOrdStatus()) return true;
			if (strpbrk($this->getOrdStatus(), $row->ords)) return true;
		}
		return false;
		
	}
	protected function stock_check(&$products)
	{
		if (count($products)==0) return "empty";		
		foreach ($products as $k => $row)
		{
			$amount = 0;
			$aok = 0;
			$qok = 0;

			if (is_numeric($row->quantity)) { $qok=1; $amount = $row->quantity; }
			if (is_numeric($row->amount)) { $aok=1; $amount = $row->amount; }
			
			if ($this->params->get('aweb_manage_stock')==1) {
				$available = $this->get_stock_info($row->virtuemart_product_id,$amount);
			}
			else
			{
				$available = $this->is_published($row->virtuemart_product_id);
				if ($available!=0) $available = $amount;	
			}
			if ($available==0) {
				unset($products->$k);
			}
			else {
				if ($aok==1) $products->$k->amount = intval($available);	
				if ($qok==1) $products->$k->quantity= intval($available);		
			}
		}
	}

	protected function mergeCarts($cartDB,$cartS)
	{
		$mycartDB = json_decode($cartDB,true);			
		if (!is_array($mycartDB["cartProductsData"])) $mycartDB["cartProductsData"] = array(); 
		$mycartS = json_decode($cartS,true);		
		if (!is_array($mycartS["cartProductsData"])) $mycartS["cartProductsData"] = array(); 
		
		if (!isset($mycartDB["vendorId"])) $mycartDB["vendorId"]=1;
		
		foreach ($mycartS["cartProductsData"] as $ki => $rowi)
		{
			$match = false;			
			foreach ($mycartDB["cartProductsData"] as $kj => $rowj)
			{
				if ($rowi["virtuemart_product_id"] == $rowj["virtuemart_product_id"])
				{
					$mycartDB["cartProductsData"][$kj]["quantity"]+=$rowi["quantity"];					
					$match = true;			
				}
			}			
			if ($match == false)
			{
				array_push($mycartDB["cartProductsData"],$rowi);				
			}
			
		}		
		return json_encode($mycartDB);	
	}
	
	protected function check_cart($cart)
	{
		// virtuemart 3.0 support		
		
		if (substr($cart, 0,1)=='O')
		{
			$mycart = unserialize($cart);	
			if (!isset($mycart->vendorId)) $mycart->vendorId=1;		
			$session = JFactory::getSession();
			$tmpcurrentcart = unserialize($vmcart);		
			$mycart->BT = $tmpcurrentcart->BT;
			$this->stock_check($mycart->products);
			$cart = serialize($mycart);
		}
		else if (substr($cart, 0,1)=='{') 
		{
			$mycart = json_decode($cart);	
			if (!isset($mycart->vendorId)) $mycart->vendorId=1;		
			$this->stock_check($mycart->cartProductsData);			
			$cart = json_encode($mycart);			
		}
		return $cart;		
	}


	protected function recallCart()
	{	
			$db = JFactory::getDBO();
			$user =& JFactory::getUser();
			$userid = $user->get('id');
			$session = JFactory::getSession();			
			$vmcart = $session->get('vmcart', null, 'vm');
//			$cart = VirtueMartCart::getCart();
			$this->debug("user: ".$userid."\n\rcart: ".$vmcart);

			$query = $db->getQuery(true);
			$query->select('data,date');
			$query->from($db->getPrefix()."awebsavedcart");
			$query->where('userid ='.$userid);
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			foreach ( $rows as $row ) {
				$this->debug("dbdata ".$row->data);
				$data = $row->data;
				$dbdate = $row->date;
			}
					// empty cart
				if ($vmcart==null || $vmcart=="" || $vmcart==" " || strpos($vmcart,'"products";a:0')!==false || strpos($vmcart,'"cartProductsData":[]')!== false){
							
					if (!$this->is_ordered_cart($userid,$dbdate))
					{							
						$newcart = $this->check_cart($data);	
						if (substr($newcart, 0,1)=='O')
						{
							$session->set('vmcart', $newcart, 'vm');
						}
						else if (substr($newcart, 0,1)=='{') 
						{
							$session->set('vmcart', $newcart, 'vm');
							$q ="update ".$db->getPrefix()."virtuemart_carts set cartData ='' where virtuemart_user_id='$userid'";							
							$db->setQuery($q);
							$db->query();	
						}			
					}									
				} 
				else
				{		
					if ($this->params->get('aweb_overwrite_cart')==1) // OverWrite On
					{
						if (substr($vmcart, 0,1)=='{') 
						{									
							$data = $vmcart;							
							$newcart = $this->check_cart($data);	
							$q ="update ".$db->getPrefix()."virtuemart_carts set cartData ='' where virtuemart_user_id='$userid'";							
							$db->setQuery($q);
							$db->query();						
							$session->set('vmcart', $newcart, 'vm');
						}
					}
					else // OverWrite Off
					{
						if (substr($vmcart, 0,1)=='{') 
						{
							$data = $this->mergeCarts($data, $vmcart);							
							$newcart = $this->check_cart($data);								
							$q ="update ".$db->getPrefix()."virtuemart_carts set cartData ='' where virtuemart_user_id='$userid'";							
							$db->setQuery($q);
							$db->query();							
							$session->set('vmcart', $newcart, 'vm');						
						}
					}
				}					
	}


}
