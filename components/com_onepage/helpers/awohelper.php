<?php
/* 
*
* @copyright Copyright (C) 2007 - 2018 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
defined('_JEXEC') or die('Restricted access');

class OPCAwoHelper {
	public static function clearCoupon(&$cart) {
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		  $session = JFactory::getSession(); 
		  $session->set('opc_last_coupon', ''); 
		  $session->set('coupon', '', 'awocoupon'); 
		  //awo3:
		  $session->set( 'site.coupon', '', 'com_awocoupon' );
		  $cart->couponCode = '';   
		  $opc_debug = OPCconfig::get('opc_debug', false);  
		  if (!empty($opc_debug)) 
		  { 
			if (class_exists('OPCloader')) {
			
			$x = debug_backtrace(); 
			$b = array(); 
			foreach ($x as $l) {
				$b[] = $l['file'].' '.$l['line']; 
			}
			  OPCloader::opcDebug(array('awohelper coupon cleared'=>true, 'b' => $b), 'awohelper '.__LINE__); 
			}
			
		  }
		 if (self::loadAwo()) {
			//AC()->storediscount->clearSessionAwocouponHistory(); 
		 
		 if (function_exists('AC')) {
		    $session_id = JFactory::getSession()->getId();
		    $db = JFactory::getDBO(); 
		    $db->setQuery( 'DELETE FROM `#__awocoupon_history` WHERE `session_id` = "' . $db->escape($session_id) . '" AND (`order_id` = 0 OR `order_id` IS NULL)' );
			$db->execute(); 
			/*
			// clean out old history that is more than 15 minuites old
			$db->setQuery( 'DELETE FROM #__awocoupon_history WHERE session_id IS NOT NULL AND session_id!="" AND coupon_entered_id IS NULL AND TIMESTAMPDIFF(MINUTE,timestamp,now())>15' );
			$db->execute(); 
			*/
		  }
		 }
		  
	}
	
	private static $awoinstance; 
	public static function loadAwo() {
		
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_awocoupon/helper/awocoupon.php')) return false; 
		
		if ( ! class_exists( 'awocoupon' ) ) {
        require JPATH_ADMINISTRATOR . '/components/com_awocoupon/helper/awocoupon.php';
		}
		if (empty(self::$awoinstance)) {
		self::$awoinstance = AwoCoupon::instance();
		if (function_exists('AC')) {
		  AC()->init();
		}
		}
		return true; 
	}
	
	
	public static function defineItems(&$cart) {
		//done with opc.php
	}
	
	public static function processAutoCoupon($debug=false) {
		
		
		if (self::loadAwo()) {
			
			
			
		if (function_exists('AC')) {
			AC()->storediscount->cart_coupon_validate_auto();
		}
		else {
		if (class_exists('AwoCouponVirtuemartCouponHandler')) {
				if (!empty($debug)) {
			      AwoCouponVirtuemartCouponHandler::process_autocoupon($debug);
				}
				else {
					AwoCouponVirtuemartCouponHandler::process_autocoupon();
				}
					
				
				
				}
		}
		}
	}
	public static function getAwoError() {
		if (self::loadAwo()) {
			$msgs = AC()->storediscount->error_msgs; 
			
			if (empty($msgs)) {
				$msgs = array(); 
			}
			
			return $msgs; 
		}
		return array(); 
	}
	public static function isAwoEnabled() {
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		
		$awo = OPCmini::extExists('com_awocoupon'); 	
		
		if ($awo) {
			$awo_enabled = OPCmini::extExists('awocoupon', 'plugin', 'vmcoupon', 1); 	
			
			
			if (!$awo_enabled) return false; 
		}
		return $awo; 
		/*
		$q = 'select `enabled` from #__extensions where `element` = "com_awocoupon" and `enabled` = 1'; 
		$db = JFactory::getDBO(); 
		$db->setQuery($q); 
		$r = $db->loadResult(); 
		if (!empty($r)) return true; 
		*/
		return false; 
	}
	
	public static function setCouponCode(&$cart, $coupon) {
		 if (self::loadAwo()) {
		 $cart->couponCode = $coupon; 
		 
		 
		 $opc_debug = OPCconfig::get('opc_debug', false);  
		  if (!empty($opc_debug)) 
		  { 
			if (class_exists('OPCloader')) {
			
			$x = debug_backtrace(); 
			$b = array(); 
			foreach ($x as $l) {
				$b[] = $l['file'].' '.$l['line']; 
			}
			  OPCloader::opcDebug(array('awohelper coupon changed to'=>$coupon, 'b' => $b), 'awohelper '.__LINE__); 
			}
			
		  }
		 
		 }
	}
	
	public static function getEnteredAwoCoupons() {
		$session = JFactory::getSession(); 
		$coupon_session = $session->get('coupon', '', 'awocoupon');
		if (!empty($coupon_session)) {
			$coupon_session = unserialize($coupon_session);
			if (!empty($coupon_session)) {
				$entered_coupons = array();
				foreach($coupon_session['processed_coupons'] as $coupon) {
					if(!empty($coupon['isauto']) && $coupon['isauto']==1) continue;
					if(!empty($coupon['isbalance']) && $coupon['isbalance']==1) continue;
					$entered_coupons[$coupon['coupon_code']] = 1;
				}
				$ret = implode(',', array_keys($entered_coupons));
				if (!empty($ret)) return $ret; 
			}
		}
		//awo3:
		$coupon_session = $session->get( 'site.coupon', '', 'com_awocoupon' );
		if (!empty($coupon_session)) {
			$coupon_session = unserialize($coupon_session);
			if (!empty($coupon_session)) {
				$entered_coupons = array();
				foreach($coupon_session['processed_coupons'] as $coupon) {
					if(!empty($coupon['isauto']) && $coupon['isauto']==1) continue;
					if(!empty($coupon['isbalance']) && $coupon['isbalance']==1) continue;
					$entered_coupons[$coupon['coupon_code']] = 1;
				}
				$ret = implode(',', array_keys($entered_coupons));
				if (!empty($ret)) return $ret; 
			}
		}
		
		return false; 
		
		
	}
	
	public static function getCouponValue($couponCode) {
		if (OPCloader::tableExists('awocoupon')) {
		$db = JFactory::getDBO(); 
				$q = "select `coupon_value` from #__awocoupon where `coupon_code` = '".$db->escape($couponCode)."' and `coupon_value_type` = 'percent' limit 0,1"; 
				$db->setQuery($q); 
				$res = $db->loadAssoc(); 
				if (!empty($res)) {
				  $cp = $res['coupon_value']; 
				  
				  return (float)$cp; 
				}
		}
		if (empty($res)) {
			$db = JFactory::getDBO(); 
			$q = "select `coupon_value` from #__virtuemart_coupons where `coupon_code` = '".$db->escape($couponCode)."' limit 0,1"; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			
			
			if (!empty($res))
			if ($res['percent_or_total'] == 'percent') {
			$cp = $res['coupon_value']; 
				return (float)$cp; 
			}
		}
			
		
		return 0; 
	}
	
	
}

//to support OPC logic in awo coupons:
if (!class_exists('plgSystemVPOnePageCheckout')) {
	class plgSystemVPOnePageCheckout {
	}
}