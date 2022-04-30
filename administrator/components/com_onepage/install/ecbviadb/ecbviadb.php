<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* ECB Currency Converter Module
*
* @version $Id: convertECB.php 9925 2018-09-09 09:20:59Z Milbo $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

/**
 * This class uses the currency rates provided by an XML file from the European Central Bank
 * Requires cURL or allow_url_fopen
 */
class ecbviadb {

	var $document_address = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	var $info_address = 'https://www.ecb.int/stats/eurofxref/';
	var $supplier = 'European Central Bank';
	
	public static $clear7day = true; //if you need to retain history of rates, just set this to false
	
	/**
	 * Converts an amount from one currency into another using
	 * the rate conversion table from the European Central Bank
	 *
	 * @param float $amountA
	 * @param string $currA defaults to $vendor_currency
	 * @param string $currB defaults to
	 * @return mixed The converted amount when successful, false on failure
	 */
	function convert( $amountA, $currA='', $currB='', $a2rC = true, $relatedCurrency = 'EUR') {

		if($currA === $currB){
			return $amountA;
		}

		static $globalCurrencyConverter;
		if (empty($globalCurrencyConverter)) $globalCurrencyConverter = array(); 
		
		if (((empty($globalCurrencyConverter)) || ((!empty($globalCurrencyConverter)) && (!isset( $globalCurrencyConverter[$currA]))) || (!isset($globalCurrencyConverter[$currB])))) {
			$rates = ecbviadb::getSetExchangeRates($this->document_address, $currA, $currB); 
			foreach ($rates as $currency => $ex) {
			  if (empty($globalCurrencyConverter)) $globalCurrencyConverter = array(); 
			  $globalCurrencyConverter[$currency] = $ex; 
			}
		}

		if(empty($globalCurrencyConverter )) {
			return $amountA;
		} else {
			
			$valA = isset( $globalCurrencyConverter[$currA] ) ? $globalCurrencyConverter[$currA] : 1.0;
			$valB = isset( $globalCurrencyConverter[$currB] ) ? $globalCurrencyConverter[$currB] : 1.0;

			$val = (float)$amountA * (float)$valB / (float)$valA;

			return $val;
		}
	}
	 static function tableExists($table)
  {
   
   
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
   
   
   
   
   
 
 
   $q = "SHOW TABLES LIKE '".$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   
	   
	   
	   if (!empty($r)) 
	    {
		
		return true;
		}
		
      return false;
  }
  
  
	private static function checkCreateTable() {
		static $wasRun; 
		if (!empty($wasRun)) return; 
		if (!self::tableExists('virtuemart_ecbviadb')) {
		$q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_ecbviadb` (
			`currency_code_3` char(3) NOT NULL,
			`exchange_rate` decimal(12,5) NOT NULL,
			`created_on` datetime NOT NULL,
			UNIQUE KEY `currency_code_3` (`currency_code_3`,`created_on`),
			KEY `currency_code_3_2` (`currency_code_3`),
			KEY `created_on` (`created_on`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
		
		$db->setQuery($q); 
		$db->execute(); 
		}
		$wasRun = true; 
			
	}
	
	static function fallback($currA, $currB) {
			$db = JFactory::getDBO(); 
			$qS = "select currency_code_3, `created_on`, `exchange_rate`, NOW() as `current_time` from `#__virtuemart_ecbviadb` where "; 
			if (!empty($currA) && (!empty($currB))) {
			 $q1 = " `currency_code_3` = '".$db->escape($currA)."' "; 
			 $q2 = " `currency_code_3` = '".$db->escape($currB)."'  ";
			}
			$qW = " order by `created_on` desc limit 1"; 
			$db->setQuery($qS.$q1.$qW); 
			$res = $db->loadAssoc(); 
			$ret = array(
			  $currA => 1,
			  $currB => 1
			); 
			if (!empty($res)) 
			$ret[$res['currency_code_3']] = (float)$res['exchange_rate']; 
			
			$db->setQuery($qS.$q2.$qW); 
			$res = $db->loadAssoc(); 
			if (!empty($res)) 
			$ret[$res['currency_code_3']] = (float)$res['exchange_rate']; 
			return $ret; 
	}
	
	static function getSetExchangeRates($url, $currA='', $currB=''){
			
			
			
			$db = JFactory::getDBO(); 
			
			//test:
			/*
			$q = 'update `#__virtuemart_ecbviadb` set `created_on` = DATE_SUB(`created_on`, INTERVAL 1 DAY)'; 
			$db->setQuery($q); 
			$db->query(); 
			*/
			
			$last_friday = "DATE_FORMAT(NOW() - INTERVAL WEEKDAY(NOW()) + 3 DAY, '%Y-%m-%d 00:00:00')"; 
			//DATE_FORMAT(DATE_SUB(NOW(), INTERVAL ((7 + WEEKDAY(DATE_SUB(NOW(), INTERVAL 1 WEEK)) - 4) % 7) DAY)
			// is last friday
			$qS = "select currency_code_3, `created_on`, `exchange_rate`, NOW() as `current_time` from `#__virtuemart_ecbviadb` where "; 
			if (!empty($currA) && (!empty($currB))) {
			 $q1 = " `currency_code_3` = '".$db->escape($currA)."' and "; 
			 $q2 = " `currency_code_3` = '".$db->escape($currB)."' and ";
			}
			$qW = " `created_on` > ".$last_friday." order by `created_on` desc limit 1"; 
			
			$q = $qS.$q1.$qW;
			
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			$rates = array(); 
			$data = array(); 
			if (!empty($res)) {
				//$rates[$currA] = (float)$res['exchange_rate']; 
				$data = array_merge($data, array($res)); 
			}
			if ($currA !== $currB) {
				$q = $qS.$q2.$qW;
				$db->setquery($q); 
				$res = $db->loadAssoc(); 
				
				
				if (!empty($res)) {
					$data = array_merge($data, array($res)); 
					//$rates[$currB] = (float)$res['exchange_rate']; 
				}
			}
			
			
			
			
			
			
			$needsUpdate = false; 
			
			//testing: update `g52p3_virtuemart_ecbviadb` set `created_on` = DATE_SUB(`created_on`, INTERVAL 30 DAY)
			
			
			
			if (!empty($data)) {
				foreach($data as $row) {
					if (empty($row)) continue; 
					if (!isset($row['exchange_rate'])) continue; 
					
					$now = strtotime($row['current_time']); 
					
					$created_on = strtotime($row['created_on']); 
					$dayofweek = (int)date('w', $now); 
					$midnight = strtotime(date("Y-m-d 00:00:00", $now));
					$minus = $now - $midnight; //caching up to last midnight
					
					
					if ($dayofweek === 0) {
						$minus = 48*3600; //set to friday
					}
					if ($dayofweek === 6) {
						//it's weekend in mysql: 
						$minus = 24*3600; //set to friday
					}
					$now = $now - $minus; 
					if ($created_on < $now) {
						// update DB
						continue; 
					}
					
					
					$rates[$row['currency_code_3']] = (float)$row['exchange_rate']; 
				}
			}
			
			
			
			if ($currA !== $currB) {
			if (count($rates) < 2) {
				$needsUpdate = true; 
				
			}
			}
			if ($currA === $currB) {
			if (count($rates) < 1) {
				$needsUpdate = true; 
				
			}
			}
		
		

		
			if (!$needsUpdate) {
				return $rates; 
			}
		
			$contents = self::fetchUrl($url); 
			
		
			if( $contents ) {

				$contents = str_replace ("<Cube currency='USD'", " <Cube currency='EUR' rate='1'/> <Cube currency='USD'", $contents);

				/* XML Parsing */
				$xmlDoc = new DomDocument();

				if( !$xmlDoc->loadXML($contents) ) {
					
					return self::fallback($currA, $currB);
				}
				$rates = array(); 
				$currency_list = $xmlDoc->getElementsByTagName( "Cube" );
				// Loop through the Currency List
				$length = $currency_list->length;
				for ($i = 0; $i < $length; $i++) {
					$currNode = $currency_list->item($i);
					if(!empty($currNode) && !empty($currNode->attributes->getNamedItem("currency")->nodeValue)){
						$rates[$currNode->attributes->getNamedItem("currency")->nodeValue] = (float)$currNode->attributes->getNamedItem("rate")->nodeValue;
						unset( $currNode );
					}

				}
				
				
				
				$db = JFactory::getDBO(); 
				$ins = array(); 
				foreach ($rates as $currency_code_3 => $rate_val) {
					$toIns = "('".$db->escape($currency_code_3)."', ".number_format($rate_val,5, '.', '').", NOW())"; 
					$ins[] = $toIns; 
				}
				if (!empty($ins)) {
					$q = 'start transaction'; 
					$db->setQuery($q); 
					$db->execute(); 
					
					$q = 'insert into `#__virtuemart_ecbviadb` (`currency_code_3`, `exchange_rate`, `created_on`) values '; 
					$q .= implode(', ', $ins); 
					$db->setQuery($q); 
					$db->execute(); 
					
					
					if (!empty(self::$clear7day)) {
						$q = 'delete from `#__virtuemart_ecbviadb` where `created_on` < DATE_SUB(NOW(), INTERVAL 7 DAY)'; 
						$db->setQuery($q); 
						$db->execute(); 
					}
					
					$q = 'COMMIT'; 
					$db->setQuery($q); 
					$db->execute(); 
					
					
					return $rates; 
				}
				
				
				
			}
			else {
				return self::fallback($currA, $currB);
			}
			
			if (empty($rates)) return self::fallback($currA, $currB); 
			
			return $rates;
	}
	
	public static function fetchUrl($url, $XPost='')
	{
	
	 if (!function_exists('curl_init'))
	 {
	  return file_get_contents($url); 
	 
	 }
		
	 $ch = curl_init(); 
	 
//	 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
	 curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
	 curl_setopt($ch, CURLOPT_TIMEOUT, 4000); // times out after 4s
     curl_setopt($ch, CURLOPT_POSTFIELDS, $XPost); // add POST fields
     if (!empty($XPost))
	 curl_setopt($ch, CURLOPT_POST, 1); 
	 else
	 curl_setopt($ch, CURLOPT_POST, 0); 
     curl_setopt($ch, CURLOPT_ENCODING , "gzip");
	 $result = curl_exec($ch);   
	
    
    
    if ( curl_errno($ch) ) {      
	    
	    
		@curl_close($ch);
		return false; 
    } else {
        $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
        switch($returnCode){
            case 404:
			    @curl_close($ch);
                return false; 
                break;
            case 200:
        	break;
            default:
				 @curl_close($ch);
            	return false; 
                break;
        }
    }
    
    @curl_close($ch);
    
  
    return $result;   
    
    

	}
	

}
// pure php no closing tag
