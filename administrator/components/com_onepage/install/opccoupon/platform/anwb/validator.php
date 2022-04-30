<?php
/**
* @component OPC for Virtuemart
* @copyright Copyright (C) RuposTel.com - All rights reserved.
* @license : GNU/GPL
**/
 
if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

class anwbValidator {
	public function __construct(&$ref) {
		$this->ref = $ref; 
	}
	
	
	public function validateCouponSyntax($coupon_code) {
		return $this->checkANWBElevenProof($coupon_code); 
	}
	
	public function checkANWBElevenProof($Relatienummer) {
		
		
		$Outputs = new stdClass; 
		$Outputs->ret = false; 
		$Outputs->msg = ''; 
		$Outputs->code = ''; 
		
		$Relatienummer = $res = preg_replace("/[^0-9]/", "", $Relatienummer );
		
		$i = 0;
		$iTotal = 0;
		$iDivision = 1;
		// Relatienummer should have a length of 9 characters and be numeric
		if (strlen($Relatienummer) !== 9 || (!is_numeric($Relatienummer))) {
			$Outputs->msg = "Invalid: Number length must be 9 numbers.";
			$Outputs->ret = false;
			return $Outputs;
		}
		else
		{
			$Relatienummer = (string)$Relatienummer; 
			while ($i < 9)
			{
				$iDigit = $Relatienummer[$i];
				$iDidit = (int)$iDigit; 
				$iTotal = $iTotal + ($iDigit * (9 - $i));
				$iTotal = (int)$iTotal; 
				$i++;
			}
			$iDivision = $iTotal % 11; 
			
			
			
			if ($iDivision === 0)
			{
				$Outputs->msg = "Valid";
				$Outputs->ret = true; 
				$Outputs->coupon_code = $Relatienummer; 
				return $Outputs; 
			}
			else
			{
				if ($Relatienummer[7] == $Relatienummer[8])
				{
					$iTotal = $iTotal - (int)$Relatienummer[8] + 10;
					$iDivision = $iTotal % 11; 
					if ($iDivision === 0)
					{
						$Outputs->msg = "Valid";
						$Outputs->ret = true; 
						$Outputs->coupon_code = $Relatienummer; 
						return $Outputs; 
					}
					else
					{
						$Outputs->msg = "Invalid: Input Value does not comply with the 11 proof.";
						$Outputs->ret = false;
						return $Outputs;
					}
				} else {
					$Outputs->msg = "Invalid: Input Value does not comply with the 11 proof.";
					$Outputs->ret = false; 
					return $Outputs; 
				}
			}
		}
		return $Outputs; 
	}
	
	function checkTimeValidity($coupon_code) {
		
		$statuses = $this->ref->params->get('order_statuses', array()); 
		$days = (int)$this->ref->params->get('limit_days', 0); 
		
		if (empty($days)) return true; 
		
		
		 require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php');
		  $z = OPCmini::hasIndex('virtuemart_orders', array('coupon_code')); 
		  if (empty($z)) {
		     OPCmini::addIndex('virtuemart_orders', array('coupon_code')); 
		  }
		
		$db = JFactory::getDBO(); 
		
		if (empty($statuses)) {
			return true; 
		}
		$in = array(); 
		foreach ($statuses as $s) {
			$in[] = ' o.`order_status` = \''.$db->escape($s).'\''; 
		}
		$this->ref->checkCreateTable();
		$q = 'select c.`virtuemart_order_id` from `#__onepage_opccoupon` as c inner join #__virtuemart_orders as o on c.virtuemart_order_id = o.virtuemart_order_id where c.`coupon_code` = \''.$db->escape($coupon_code).'\' ';
		if (!empty($days)) {
			//only check last year via days set
			$q .= ' and c.`created_on` > DATE_SUB(NOW(), INTERVAL '.(int)$days.' DAY) '; 
		}
		$q .= ' and ('.implode(' or ', $in).') order by virtuemart_order_id desc limit 1'; 
		
		
		
		$db->setQuery($q); 
		$test = $db->loadResult(); 
		
		
		
		if (!empty($test)) {
			//is not valid because order with the same coupon already exists
			return false; 
		}
		
		if (empty($test)) {
			//$q = 'select o.`virtuemart_order_id`, o.`coupon_code` REGEXP \'[[:alnum:]]+\' as `cr` from #__virtuemart_orders as o where ((o.`coupon_code` <> \'\') and (o.`coupon_code` is NOT NULL)) and  ((o.`coupon_code` LIKE \''.$db->escape($coupon_code).'\') or ( `cr` = '.$db->escape((int)$coupon_code).')) ';
			$q = 'select o.`virtuemart_order_id` from #__virtuemart_orders as o where ((o.`coupon_code` <> \'\') and (o.`coupon_code` is NOT NULL)) and  ((o.`coupon_code` LIKE \''.$db->escape($coupon_code).'\') or ( CAST(o.`coupon_code` AS UNSIGNED) = '.$db->escape((int)$coupon_code).')) ';
		if (!empty($days)) {
			//only check last year via days set
			$q .= ' and (o.`created_on` > DATE_SUB(NOW(), INTERVAL '.(int)$days.' DAY)) '; 
		}
		$q .= ' and ('.implode(' or ', $in).') order by `virtuemart_order_id` desc limit 1'; 
		
		}
		
		$db->setQuery($q); 
		$test = $db->loadResult(); 
		
		
		if (!empty($test)) {
			//is not valid because order with the same coupon already exists
			return false; 
		}
		
		
		//is valid:
		return true; 
		
		
	}
	
	
	
}