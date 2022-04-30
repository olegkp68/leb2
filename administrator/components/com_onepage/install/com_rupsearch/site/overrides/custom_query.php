<?php 
/**
 * @package		RuposTel Ajax search pro
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

$extra_where = ''; 
if (file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.'municakeu_kategorie.php')) {
	include(JPATH_ROOT.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.'municakeu_kategorie.php'); 
	if (!empty($zakazane)) {
		
		$c_from .= ', `#__virtuemart_product_categories` as vpcX '; 
		$c_where .= ' ( (`vpcX`.`virtuemart_category_id` NOT IN ('.$zakazane.') ) and (`vpcX`.`virtuemart_product_id` = `p`.`virtuemart_product_id`) ) and '; 
		
	}
}