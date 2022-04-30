<?php
/**
 * @package		RuposTel OPC 
 * @subpackage	mod_opcard
 * @copyright	Copyright (C) 2005 - 2012 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 


/* loaded from: 
 /components/\com_rupsearch\helpers\helper.php
 
 
 
*/ 



		  // search product sku
		  //$q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ";
		  if ($child_handling === 3) $q .= " (";
		  $q .= "(( ".RupHelper::letterWildcardsEscape('p.`product_sku`', 'LIKE', $keyword).") or ( ".RupHelper::letterWildcardsEscape('p.`product_sku`', '=', $keyword).")) "; 
		  if ($child_handling === 3) {
		  $q .= " or ((( ".RupHelper::letterWildcardsEscape('childs.`product_sku`', 'LIKE', $keyword).") or ( ".RupHelper::letterWildcardsEscape('childs.`product_sku`', '=', $keyword).")) and childs.`published` = '1' ) ) ";
		  }
		  
		  $q .= " and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ";
		  $q .= $whereM." ".$order_single." ".$product_limit."\n"; 
		  
		  $searchS['PRODUCT_SKU'] = array(); 
		  $searchS['PRODUCT_SKU'][] = $q; 
		  $search['PRODUCT_SKU'][] = $q; 
		  
		  
		  
		  if ($keyword_orig !== $keyword) {
		  // search product sku
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_sku`', 'LIKE', $keyword_orig).") or ( ".RupHelper::letterWildcardsEscape('p.`product_sku`', '=', $keyword_orig).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  
		  $searchS['PRODUCT_SKU'][] = $q; 
		  $search['PRODUCT_SKU'][] = $q; 
		  }
		  
		  
		  
		  if (mb_strlen($keyword)>3)
		  { 
		  //$q = " union ( "; 
		  $q .= " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  #__virtuemart_products as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where  ".RupHelper::letterWildcardsEscape('`p`.`product_sku`', 'LIKE', '%'.$keyword.'%')." and p.published = '1' and p.virtuemart_product_id = l.virtuemart_product_id ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q.= " ) "."\n"; 
		  
		   $search['PRODUCT_SKU_PARTIAL'] = $searchS['PRODUCT_SKU_PARTIAL'] = $q; 
		  
		  
		  }
		  
		  // search product name
		  
		  // exact match: 
	      //$q .= " union ("; 
		  $q0 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q0 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.product_parent_id = p.`virtuemart_product_id`) as children "; 
		  $q0 .= $generic_from;
		  $q0 .= " where ".$stock." ";
		  if ($child_handling === 3) $q0 .= " (";
		  $q0 .= "(( ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', $keyword, true)." ) or (( ".RupHelper::letterWildcardsEscape('l.`product_name`', '=', $keyword)." ))) ";
		  if ($child_handling === 3) 
		  $q0 .= " or ((( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', 'LIKE', $keyword, true)." ) or (( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', '=', $keyword)." ))) and childs.`published` = '1' ) )  ";
		  
		  
		  $q0 .= " and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  $search['PRODUCT_NAME'] = $searchS['PRODUCT_NAME']  = array(); 
		  
		  $search['PRODUCT_NAME'][] = $q0; 
		  $searchS['PRODUCT_NAME'][] = $q0; 

		  if ($keyword_orig !== $keyword) {
		  $q0 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q0 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.product_parent_id = p.`virtuemart_product_id`) as children "; 
		  $q0 .= $generic_from;
		  $q0 .= " where ".$stock." ";
		  if ($child_handling === 3) $q0 .= " (";
			  
		  $q0 .= " (( ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', $keyword_orig, true)." ) or (( ".RupHelper::letterWildcardsEscape('l.`product_name`', '=', $keyword_orig)." ))) "; 
		  if ($child_handling === 3) {
		   $q0 .= " or ((( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', 'LIKE', $keyword_orig, true)." ) or (( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', '=', $keyword_orig)." )))) and childs.`published` = '1' ) "; 
		  }
		  
		  $q0 .= " and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  }

		  
		  $search['PRODUCT_NAME'][] = $q0; 
		  $searchS['PRODUCT_NAME'][] = $q0; 


		  
		  $qS = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qS .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.product_parent_id = p.`virtuemart_product_id`) as children "; 
		  $qS .= $generic_from;
		  $qS .= " where ".$stock." "; 
		   if ($child_handling === 3) $qS .= " (";
		  $qS .= " (( ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', $keyword)." ) or (( ".RupHelper::letterWildcardsEscape('l.`product_name`', '=', $keyword)." ))) "; 
		   if ($child_handling === 3) 
		  $qS .= " or ((( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', 'LIKE', $keyword)." ) or (( ".RupHelper::letterWildcardsEscape('lchilds.`product_name`', '=', $keyword)." ))) and childs.`published` = '1' ) ) ";
		  $qS .= " and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		  
		  


		   $qz1 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qz1 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qz1 .= $generic_from;
		  $qz1 .= " where ".$stock." ";
		  if ($child_handling === 3) $qz1 .= " (";
		  $qz1 .= " ( ".RupHelper::letterWildcardsEscape('l.`product_name` ', 'LIKE', '%'.$keyword, true).") ";
		  if ($child_handling === 3) 
		  $qz1 .= " or ( ".RupHelper::letterWildcardsEscape('lchilds.`product_name` ', 'LIKE', '%'.$keyword, true).") and childs.`published` = '1' ) ";
	  
		  $qz1 .= "  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  $search['PRODUCT_NAME'][] = $qz1; 
		  $searchS['PRODUCT_NAME'][] = $qz1; 
		  
		   $qza = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qza .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qza .= $generic_from;
		  $qza .= " where ".$stock." "; 
		  if ($child_handling === 3) $qza .= " (";
		  $qza .= " ( ".RupHelper::letterWildcardsEscape('l.`product_name` ', 'LIKE', $keyword.'%').") ";
		  if ($child_handling === 3) 
		  $qza .= " or (( ".RupHelper::letterWildcardsEscape('lchilds.`product_name` ', 'LIKE', $keyword.'%').") and childs.`published` = '1')) ";
		  $qza .= " and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		 


		  $qz2 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qz2 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qz2 .= $generic_from;
		  $qz2 .= " where ".$stock."  ";
		  if ($child_handling === 3) $qz2 .= " (";
		  $qz2 .= "(".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '% '.$keyword.' %', true).")"; 
		  if ($child_handling === 3) 
		  $qz2 .= " or ((".RupHelper::letterWildcardsEscape('lchilds.`product_name`', 'LIKE', '% '.$keyword.' %', true).") and childs.`published` = '1'))"; 
		  $qz2 .= "  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 

		  
		   $search['PRODUCT_NAME'][] = $qz2; 
		   $searchS['PRODUCT_NAME'][] = $qz2; 
		  
		   $qzb = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qzb .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qzb .= $generic_from;
		  $qzb .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '% '.$keyword.' %')."  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		  

		  $qz3 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qz3 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qz3 .= $generic_from;
		  $qz3 .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '%'.$keyword.'%', true)."  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 

		   $search['PRODUCT_NAME'][] = $qz3; 
		   $searchS['PRODUCT_NAME'][] = $qz3; 
		  
		  
		   $qzc = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qzc .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qzc .= $generic_from;
		  $qzc .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '%'.$keyword.'%')."  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		   
		   //wildcard product name search, after exact search: 
		   
		   if ($qS !== $q0) {
		  $search['PRODUCT_NAME'][] = $qS; 
		  $searchS['PRODUCT_NAME'][] = $qS; 
		  }
		   
		    if ($qza !== $qz1 ) {
		  $search['PRODUCT_NAME'][] = $qza; 
		  $searchS['PRODUCT_NAME'][] = $qza; 
		  }
		   
		   if ($qzb !== $qz2) {
		  $search['PRODUCT_NAME'][] = $qzb; 
		  $searchS['PRODUCT_NAME'][] = $qzb; 
		  }
		   
		   
		   if ($qzc !== $qz3) {
		  $search['PRODUCT_NAME'][] = $qzc; 
		  $searchS['PRODUCT_NAME'][] = $qzc; 
		   }
		  
		  if ((strpos($keyword, ' ') !== false) || ((strpos($keyword, '-') !== false))) {
		  $mkeyword = str_replace(' ', '%', $keyword);
		  $mkeyword = str_replace('-', '%', $mkeyword);
		  
		  $ae = explode('%', $mkeyword);
		  $mk = array(); 
		  $db = JFactory::getDBO(); 
		  foreach ($ae as $kw) {
			  if (empty($kw)) continue; 
			  $mk[] = $db->escape($kw); 
		  }
		  if (!empty($mk)) {
		  $mkeyword = implode('%', $mk); 
		  
		  
		  $qzd = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qzd .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qzd .= $generic_from;
		  $qzd .= " where  ".$stock."  l.`product_name` LIKE '%".$mkeyword."%'  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		   $search['PRODUCT_NAME'][] = $qzd; 
		  $searchS['PRODUCT_NAME'][] = $qzd; 
		  
		  
		  
		  
		   $qzd2 = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $qzd2 .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $qzd2 .= $generic_from;
		  $qzd2 .= " where  ".$stock."  l.`product_name` = '%".$mkeyword."%'  and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		   $search['PRODUCT_NAME'][] = $qzd2; 
		  $searchS['PRODUCT_NAME'][] = $qzd2; 
		  }
		  }
		  
		  /*
		  $ln = mb_strlen($keyword); 
		  for ($i=0; $i<$ln; $i++) {
			  $ch = mb_substr($keyword, $i, 1); 
			  
		  }
		  */
		   
		  
		  
		   // search attribs
		  
		   if (!empty($ft)) { 
		   
		   
		   $q = " select distinct p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l, `#__virtuemart_product_customfields` as cf ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock." (MATCH (cf.`customfield_value`) AGAINST('".$db->escape($keyword)."' IN BOOLEAN MODE) ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` and cf.`virtuemart_product_id` = p.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		   
		   
		   }
		   else
		   {
		   
	      
		  $q = " select distinct p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l, `#__virtuemart_product_customfields` as cf ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('cf.`customfield_value`', 'LIKE', '%'.$keyword.'%')." ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` and cf.`virtuemart_product_id` = p.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   }
		   
		  $search['PRODUCT_ATTRIBS'] = $q;
		  $searchS['PRODUCT_ATTRIBS'] = $q;
		  
		  if ((!defined('VM_VERSION')) || (VM_VERSION < 3)) {
		   unset($search['PRODUCT_ATTRIBS']); 
		  }
		  
		  
		 // search manufacturer name
$q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql;
 if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= " , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
           $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_product_manufacturers` as pm, `#__virtuemart_manufacturers_".VMLANG."` as m ".$c_from.') '.$c_left;
           $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('m.`mf_name`', 'LIKE', $keyword.'%')."  ) and p.`published` = '1' and p.`virtuemart_product_id` = pm.`virtuemart_product_id` and pm.`virtuemart_manufacturer_id` = m.`virtuemart_manufacturer_id` ".$whereM.$order_single." ".$product_limit."\n"; 
           

           $search['MF_NAME'] =  $searchS['MF_NAME']= $q;
		   
		   
		   
		    // search manufacturer name
		   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql;
		   if ((!empty($child_handling)) && ($child_handling === 2)) 
		   $q .= " , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
           $q .= " from (`#__virtuemart_products` AS p,  `#__virtuemart_product_categories` as pc,  `#__virtuemart_categories_".VMLANG."` as cl, `#__virtuemart_categories` as c ".$c_from.') '.$c_left;
           $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('cl.`category_name`', 'LIKE', $keyword.'%')."  ) and (c.`published` = '1') and (p.`published` = '1') and (p.`virtuemart_product_id` = pc.`virtuemart_product_id`) and (cl.`virtuemart_category_id` = c.`virtuemart_category_id`) and (pc.`virtuemart_category_id` = c.`virtuemart_category_id`) ".$whereM.$order_single." ".$product_limit."\n"; 
           

           $search['CAT_NAME'] = $searchS['CAT_NAME'] = $q;
		   
		   
		    // search manufacturer name
			$q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql;
			if ((!empty($child_handling)) && ($child_handling === 2)) 
		    $q .= " , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
            $q .= " from (`#__virtuemart_products` AS p,  `#__virtuemart_product_categories` as pc,  `#__virtuemart_categories_".VMLANG."` as cl, `#__virtuemart_categories` as c, `#__virtuemart_category_categories` as cp ".$c_from.') '.$c_left;
            $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('cl.`category_name`', 'LIKE', $keyword.'%')."  ) and (c.`published` = '1') and (p.`published` = '1') and (p.`virtuemart_product_id` = pc.`virtuemart_product_id`) and ((cl.`virtuemart_category_id` = c.`virtuemart_category_id`) )) ".$whereM.$order_single." ".$product_limit."\n"; 
           

           $search['CAT_NAME_PATH'] = $searchS['CAT_NAME_PATH']= $q;
		  
		  
		  // search product name for multi words
		  $search['PRODUCT_NAME_WORDS'] = array(); 
		  $searchS['PRODUCT_NAME_WORDS'] = array(); 
		  
		  if (!empty($or))
		  {
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		 if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', ' '.$keyword.' ').") and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` "; 
		  // the search from exact match: 
		  $q .= " ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  
		  $search['PRODUCT_NAME_WORDS'][] = $q;
		  //simplified: 
		  $searchS['PRODUCT_NAME_WORDS'][] = $q;

			 
	     
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock." (0 ".$or.") and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` "; 
		  // the search from exact match: 
		  $q .= " ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  
		  $search['PRODUCT_NAME_WORDS'][] = $q;
		  $searchS['PRODUCT_NAME_WORDS'][] = $q;
		  //simplified: 
		  
		  
		 
		  
		  }
		  else
		  {
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '% '.$keyword.' %')." and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` "; 
		  $q .= $whereM.$order_single." ".$product_limit."\n"; 

		   $search['PRODUCT_NAME_WORDS'][] = $q;
		   $searchS['PRODUCT_NAME_WORDS'][] = $q;
			  
			  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .=" , (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l ".$c_from.') '.$c_left; 
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '%'.$keyword.'%')." and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` "; 
		  $q .= $whereM.$order_single." ".$product_limit."\n"; 

		   $search['PRODUCT_NAME_WORDS'][] = $q;
		   $searchS['PRODUCT_NAME_WORDS'][] = $q;
			
			  
		  
		  }
		  
		  $keyword2 = ''; 
		  
		  if (empty($no_short)) { 
		  if ((mb_strlen($keyword)>6) && (mb_strlen($keyword)<9))
		  {
			  $keyword2 = mb_substr($keyword, 0, -2); 
		  }
		 
			  if ((mb_strlen($keyword)>8))
			  {
				  $keyword2 = mb_substr($keyword, 0, -3); 
			  }
		  else
		  {
			   $keyword2 = mb_substr($keyword, 0, -1); 
		  }
		  }
		  
		  if (!empty($keyword2))
		  {
			  // search product name for multi words
	      //$q .= " union ( "; 
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_name`', 'LIKE', '%'.$keyword2.'%')." ".$or.") and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		  
		   $search['PRODUCT_NAME_MULTI_WORDS'] = $q;
		   $searchS['PRODUCT_NAME_MULTI_WORDS'] = $q;
		  }
		  
		  /*MPM*/
		  if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
			
			$search['PRODUCT_MPN'] = array();   
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', 'LIKE', $keyword).") or ( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', '=', $keyword).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  

		  $searchS['PRODUCT_MPN'][] = $q; 
		  $search['PRODUCT_MPN'][] = $q; 
		  
		  if ($keyword_orig !== $keyword) {
		  
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', 'LIKE', $keyword_orig).") or ( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', '=', $keyword_orig).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  
		  $searchS['PRODUCT_MPN'][] = $q;
		  $search['PRODUCT_MPN'][] = $q; 
		  }
			  
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('p.`product_mpn`', 'LIKE', $ko.'%')." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_MPN'][] = $q;
		   $searchS['PRODUCT_MPN'][] = $q;
		   
		   
		     
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ".RupHelper::letterWildcardsEscape(' p.`product_mpn`', 'LIKE', '%'.$ko)." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
			  
			   $search['PRODUCT_MPN'][] = $q;
		       $searchS['PRODUCT_MPN'][] = $q;
			  
		  }
		  
		  /*MPM END*/
		  
		  
		   /*EAN*/
		  if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
			
			$search['PRODUCT_GTIN'] = array();   
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', 'LIKE', $keyword).") or ( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', '=', $keyword).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  

		  $searchS['PRODUCT_GTIN'][] = $q; 
		  $search['PRODUCT_GTIN'][] = $q; 
		  
		  if ($keyword_orig !== $keyword) {
		  // search product sku
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', 'LIKE', $keyword_orig).") or ( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', '=', $keyword_orig).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  
		  $searchS['PRODUCT_GTIN'][] = $q;
		  $search['PRODUCT_GTIN'][] = $q; 
		  }
			  
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('p.`product_gtin`', 'LIKE', $ko.'%')." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_GTIN'][] = $q;
		   $searchS['PRODUCT_GTIN'][] = $q;
		   
		   
		     // ends with... 
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ".RupHelper::letterWildcardsEscape(' p.`product_gtin`', 'LIKE', '%'.$ko)." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
			  
			   $search['PRODUCT_GTIN'][] = $q;
		       $searchS['PRODUCT_GTIN'][] = $q;
			  
		  }
		  
		  /*EAN END*/
		  
		  
		  
		    /*SKU WITHOUT SPACE*/
		  if (defined('VM_VERSION') && (VM_VERSION >= 3)) {
			
			$keyword_no_space = str_replace(' ', '', $keyword); 
			
			
			$search['PRODUCT_SKU_WITHOUT_SPACE'] = array();   
			  
			  
			     $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_sku`', 'LIKE', $keyword_no_space).") or ( ".RupHelper::letterWildcardsEscape('p.`product_sku`', '=', $keyword_no_space).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  

		  $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
		  $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
			  
			  
			     $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', 'LIKE', $keyword_no_space).") or ( ".RupHelper::letterWildcardsEscape('p.`product_mpn`', '=', $keyword_no_space).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  

		  $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
		  $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', 'LIKE', $keyword_no_space).") or ( ".RupHelper::letterWildcardsEscape('p.`product_gtin`', '=', $keyword_no_space).")) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM." ".$order_single." ".$product_limit."\n"; 
		  

		  $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
		  $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q; 
		  
	
	 $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('p.`product_sku`', 'LIKE', $keyword_no_space.'%')." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   
		   
		     // ends with... 
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ".RupHelper::letterWildcardsEscape(' p.`product_sku`', 'LIKE', '%'.$keyword_no_space)." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
			  
			   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		       $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
			   
			   
			    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('p.`product_mpn`', 'LIKE', $keyword_no_space.'%')." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   
		   
		     // ends with... 
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ".RupHelper::letterWildcardsEscape(' p.`product_mpn`', 'LIKE', '%'.$keyword_no_space)." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
			  
			   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		       $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
	
	
			  
			  
			   $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  ".RupHelper::letterWildcardsEscape('p.`product_gtin`', 'LIKE', $keyword_no_space.'%')." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		   
		   
		     // ends with... 
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ".RupHelper::letterWildcardsEscape(' p.`product_gtin`', 'LIKE', '%'.$keyword_no_space)." and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
			  
			   $search['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
		       $searchS['PRODUCT_SKU_WITHOUT_SPACE'][] = $q;
			  
		  }
		  
		  /*SKU WITHOUT SPACE END*/
		  
		  
		 
		  // product SKU starts with... 
		  //$q .= " union ( "; 
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock."  "; 
		  if ($child_handling === 3) $q .= " (";
		  $q .= RupHelper::letterWildcardsEscape('p.`product_sku`', 'LIKE', $ko.'%'); 
		  if ($child_handling === 3) 
		  $q .= " or ((( ".RupHelper::letterWildcardsEscape('childs.`product_sku`', 'LIKE', $ko.'%').") ) and childs.`published` = '1' ) ) ";
		  
		  $q .= " and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_SKU_STARTS_WITH'] = $q;
		   $searchS['PRODUCT_SKU_STARTS_WITH'] = $q;
		   
		   
		     // product SKU ends with... 
		  
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." "; 
		  if ($child_handling === 3) $q .= " (";
		  $q .= RupHelper::letterWildcardsEscape(' p.`product_sku`', 'LIKE', '%'.$ko); 
		  if ($child_handling === 3) 
		  $q .= " or ((( ".RupHelper::letterWildcardsEscape('childs.`product_sku`', 'LIKE', '%'.$ko).") ) and childs.`published` = '1' ) ) ";
	  
		  $q .= " and p.`published` = '1'  and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q .= " )"."\n";
		   $search['PRODUCT_SKU_ENDS'] = $q;
		   $searchS['PRODUCT_SKU_ENDS'] = $q;
		   
		  
		  
		  
		 
		  
		  $optional_search = $params->get('optional_search', 2); 
		  if ($optional_search === 2) $search_desc_request = true; 
		  else 
		  if (($optional_search === 1) && (!empty($search_desc_request))) $search_desc_request = true; 
		  if (empty($optional_search)) $search_desc_request = 0; 
		  
		  
		  //to set desc search: 
		  if ((!empty($search_desc_request)) || (in_array('PRODUCT_DESC', $priorities)))
		  {
		  // product desc includes the phrase
		  //$q .= " union ( "; 
		  
		  
		   if (!empty($ft)) { 
		   $q = "select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (MATCH (l.`product_desc`) AGAINST('".$db->escape($keyword)."' IN BOOLEAN MODE) ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   }
		   else
		   {
		  $q = "select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_desc`', 'LIKE', '%'.$keyword.'%')." ".$or2." ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q = " )"."\n";
		   }
		   
		  $search['PRODUCT_DESC'] = $q;
		  $searchS['PRODUCT_DESC'] = $q;
		  
		  
		  
		  
		    if (!empty($ft)) { 
		   $q = "select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." (MATCH (l.`product_desc`) AGAINST('".$db->escape($keyword)."' IN BOOLEAN MODE) ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   }
		   else
		   {
		  $q = "select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id`".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children  "; 
		  $q .= $generic_from;
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_desc`', 'LIKE', '%'.$keyword.'%')." ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  //$q = " )"."\n";
		   }
		   
		  $search['PRODUCT_DESC_PHRASE_MATCH'] = $q;
		  $searchS['PRODUCT_DESC_PHRASE_MATCH'] = $q;
		  
		  
		  }
		  if ((!empty($search_desc_request)) || (in_array('PRODUCT_S_DESC', $priorities))) {
		  
		   if (!empty($ft)) { 
		   
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children"; 
		  $q .= $generic_from;
		  
		  $q .= " where ".$stock." (MATCH (l.`product_s_desc`) AGAINST('".$db->escape($keyword)."' IN BOOLEAN MODE) ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		   
		   
		   
		   }
		   else
		   {
		  
		  
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children"; 
		  $q .= $generic_from;
		  
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_s_desc`', 'LIKE', '%'.$keyword.'%')." ".$or3.") and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  
		  
		  
		  }
		   $search['PRODUCT_S_DESC'] = $q;
		   $searchS['PRODUCT_S_DESC'] = $q;
		  }
		  
		  
		  if ((!empty($search_desc_request)) || (in_array('PRODUCT_S_DESC_PHRASEMATCH', $priorities))) {
		  
		   if (!empty($ft)) { 
		   
		    $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children"; 
		  $q .= $generic_from;
		  
		  $q .= " where ".$stock." (MATCH (l.`product_s_desc`) AGAINST('".$db->escape($keyword)."' IN BOOLEAN MODE) ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		   
		   
		   
		   
		   }
		   else
		   {
		  
		  
		  $q = " select ".$distinct.' '.__LINE__." as `qn`, p.`virtuemart_product_id`, p.`product_parent_id` ".$order_sql; 
		  if ((!empty($child_handling)) && ($child_handling === 2)) 
		  $q .= ", (select  GROUP_CONCAT(pp.`virtuemart_product_id`) from  `#__virtuemart_products` as pp where pp.`product_parent_id` = p.`virtuemart_product_id`) as children"; 
		  $q .= $generic_from;
		  
		  $q .= " where ".$stock." ( ".RupHelper::letterWildcardsEscape('l.`product_s_desc`', 'LIKE', '%'.$keyword.'%')." ) and p.`published` = '1' and p.`virtuemart_product_id` = l.`virtuemart_product_id` ".$whereM.$order_single." ".$product_limit."\n"; 
		  
		  
		  
		  
		  }
		   $search['PRODUCT_S_DESC_PHRASEMATCH'] = $q;
		   $searchS['PRODUCT_S_DESC_PHRASEMATCH'] = $q;
		  }
		  
		  
		  //when adding new, don't forget to add MOD_VIRTUEMART_AJAX_SEARCH_PRO_SEARCH_ORDERING_{YOUR PRIORITY} INTO BE OVERRIDES
		  