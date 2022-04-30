<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


// all classes should be named by <element>Xml per it's manifest with upper letter for the element name and the Xml
class CategoriesXml {
	function startHeaders()
    {
     $xml = '<?xml version="1.0" encoding="utf-8"?>'."\n"; 
	 $xml .= '<CATEGORIES>'."\n"; 
	 return $xml; 
	 
    }
	
	 function addCategoryItem($category) {
		    static $root; 
			$url = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category['virtuemart_category_id'], false);
		    if (empty($root)) {
				$root = Juri::root(); 
				if ((substr($root, -1) !== '/') && (substr($url, 0, 1) !== '/')) $root .= '/'; 
				if ((substr($root, -1) === '/') && (substr($url, 0,1) === '/')) $root = substr($root, 0, -1); 
				
			}
			
			if (empty($category['category_name'])) return ''; 
			
			$cat_id = (int)$category['virtuemart_category_id'];
			$data = '<CATEGORY>'."\n";
			$data .= '<ID>'.$category['virtuemart_category_id'].'</ID>'."\n";
			$data .= '<NAME><![CDATA['.$category['category_name'].']]></NAME>'."\n";
			$data .= '<URL><![CDATA['.$root.$url.']]></URL>'."\n";
			
			$db = JFactory::getDBO(); 
			
			$q = 'select min(price.product_price) as minprice, count(pc.virtuemart_product_id) as `pcount` from #__virtuemart_product_categories as pc, #__virtuemart_product_prices as price, #__virtuemart_products as p '; 
			$q .= ' where (pc.virtuemart_category_id = '.(int)$cat_id.') and (pc.virtuemart_product_id = p.virtuemart_product_id) and (price.virtuemart_product_id = pc.virtuemart_product_id) and (p.published = 1) and (price.product_price > 0) ';
			
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			
			if (empty($res)) return ''; 
			
			$min_price = floatval($res['minprice']); 
			$pcount = (int)$res['pcount']; 

			$q = 'select min(price.product_override_price) as minprice from #__virtuemart_product_categories as pc, #__virtuemart_product_prices as price, #__virtuemart_products as p '; 
			$q .= ' where (pc.virtuemart_category_id = '.(int)$cat_id.') and (pc.virtuemart_product_id = p.virtuemart_product_id) and (price.virtuemart_product_id = pc.virtuemart_product_id) and (p.published = 1) and (price.product_price > 0) ';
			$q .= ' and ((price.product_override_price > 0) and (price.override = 1)) '; 
			
			$db->setQuery($q); 
			$minoverride = $db->loadResult(); 
			if (!empty($minoverride)) {
				$minoverride = floatval($minoverride); 
			if ($minoverride < $min_price) {
				$min_price = $minoverride; 
			  }
			}
			
			if (empty($pcount)) return ''; 

			$q = 'select max(((price.product_price - price.product_override_price) / price.product_price) * 100) ';
			
			$q .=  ' as maxdiscount from #__virtuemart_product_categories as pc, #__virtuemart_product_prices as price, #__virtuemart_products as p '; 
			$q .= ' where (pc.virtuemart_category_id = '.(int)$cat_id.') and (pc.virtuemart_product_id = p.virtuemart_product_id) and (price.virtuemart_product_id = pc.virtuemart_product_id) and (p.published = 1) and (price.product_price > 0) ';
			$q .= ' and ((price.product_override_price > 0) and (price.override = 1)) '; 
			
			$db->setQuery($q); 
			$res = $db->loadResult(); 
			$res = (int)$res; 
			$data .= '<MAXDISCOUNTPERCENT>'.$res.'</MAXDISCOUNTPERCENT>'."\n"; 
			$data .= '<MINPRICE>'.number_format($min_price, 2, '.', '').'</MINPRICE>'."\n"; 
			$data .= '<PRODUCTCOUNT>'.(int)$pcount.'</PRODUCTCOUNT>'."\n"; 
			$data .= '</CATEGORY>'."\n"; 
			
			
			
			return $data; 
	 }
   
	 
	 function endHeaders()
  {
	 $xml = '</CATEGORIES>'."\n"; 
	 return $xml; 
  
  }
  function compress()
  {
  }
  
}