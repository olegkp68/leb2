<?php
defined('_JEXEC') or die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* featured/Latest/Topten/Random Products Module
*
* @version $Id: mod_virtuemart_product.php 2789 2011-02-28 12:41:01Z oscar $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2010 - Patrick Kohl
* @copyright (C) 2011 - 2017 The VirtueMart Team
* @author Max Milbers, Valerie Isaksen, Alexander Steiner
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* @link https://virtuemart.net
*/
if (!defined('RENDERED'.$module->id)) {
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');



VmConfig::loadConfig();
vmLanguage::loadJLang('mod_virtuemart_product', true);
/*
if(!class_exists('mod_virtuemart_product_helper')){
	class mod_virtuemart_product_helper{

		public static function getProductsListing ($group = FALSE, $nbrReturnProducts = FALSE, $withCalc = TRUE, $onlyPublished = TRUE, $single = FALSE, $filterCategory = TRUE, $category_id = 0, $filterManufacturer = TRUE, $manufacturer_id = 0, $omit = 0) {
			$productModel = VmModel::getModel('Product');
			VirtueMartModelProduct::$omitLoaded = $omit;
			$products = $productModel->getProductListing($group, $nbrReturnProducts, $withCalc, $onlyPublished, $single, $filterCategory, $category_id, $filterManufacturer, $manufacturer_id);

			$cproducts = array();
			foreach($products as $product){
				$tmp = get_object_vars($product);
				$t = new stdClass();
				foreach ($tmp as $k => $v){
					// Do not process internal variables
					if (strpos ($k, '_') !== 0 and property_exists($product, $k)){
						$t->$k = $v;
					}
				}
				$cproducts[] = $t;
			}
			return $cproducts;

		}
	}
}
*/


// Setting
$max_items = 		$params->get( 'max_items', 2 ); //maximum number of items to display
$layout = $params->get('layout','default');
$category_id = 		$params->get( 'virtuemart_category_id', null ); // Display products from this category only
$filter_category = 	(bool)$params->get( 'filter_category', 0 ); // Filter the category
$manufacturer_id = 	$params->get( 'virtuemart_manufacturer_id', null ); // Display products from this manufacturer only
$filter_manufacturer = 	(bool)$params->get( 'filter_manufacturer', 0 ); // Filter the manufacturer
$display_style = 	$params->get( 'display_style', "div" ); // Display Style
$products_per_row = $params->get( 'products_per_row', 1 ); // Display X products per Row
$show_price = 		(bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_addtocart = 	(bool)$params->get( 'show_addtocart', 1 ); // Display the "Add-to-Cart" Link?
$headerText = 		$params->get( 'headerText', '' ); // Display a Header Text
$footerText = 		$params->get( 'footerText', ''); // Display a footerText
$product_group = 	$params->get( 'product_group', 'featured'); // Display a footerText

$mainframe = Jfactory::getApplication();
$virtuemart_currency_id = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',vRequest::getInt('virtuemart_currency_id',0) );


vmJsApi::jPrice();
vmJsApi::cssSite();

$cache = $params->get( 'vmcache', true );
$cachetime = $params->get( 'vmcachetime', 2 );
$products = false;
//vmdebug('$params for mod products',$params);

$productModel = VmModel::getModel('Product');
/*
if($cache and $Product_group!='recent'){
	vmdebug('Use cache for mod products');
	//$key = 'products'.$category_id.'.'.$max_items.'.'.$filter_category.'.'.$display_style.'.'.$products_per_row.'.'.$show_price.'.'.$show_addtocart.'.'.$Product_group.'.'.$virtuemart_currency_id.'.'.$category_id.'.'.$filter_manufacturer.'.'.$manufacturer_id;
	$cache	= VmConfig::getCache('mod_virtuemart_product');
	$cache->setCaching(1);
	$cache->setLifeTime($cachetime);
	$products = $cache->call( array( 'mod_virtuemart_product_helper', 'getProductsListing' ),$Product_group, $max_items, $show_price, true, false,$filter_category, $category_id, $filter_manufacturer, $manufacturer_id, $params->get( 'omitLoaded', 0));
	if ($products) {
		vmdebug('Use cached mod products');
	}

}

if(!$products){
	$vendorId = vRequest::getInt('vendorid', 1);

	if ($filter_category ) $filter_category = TRUE;
	VirtueMartModelProduct::$omitLoaded = $params->get( 'omitLoaded', 0);
	$products = $productModel->getProductListing($Product_group, $max_items, $show_price, true, false,$filter_category, $category_id, $filter_manufacturer, $manufacturer_id);
}
*/

	$pid = JRequest::getInt('virtuemart_product_id', 0); 
	$db = JFactory::getDBO(); 
	$res = array(); 
	if (!empty($pid)) {
		$q = 'select p.product_canon_category_id, l.category_name from #__virtuemart_products as p inner join `#__virtuemart_categories_'.VmConfig::$vmlang.'` as l on l.virtuemart_category_id = p.product_canon_category_id where p.virtuemart_product_id = '.(int)$pid; 
		$db->setQuery($q); 
		$catRes = $db->loadAssoc(); 
		if (!empty($catRes)) {
			$canon_cat = (int)$catRes['product_canon_category_id']; 
			$category_name = $catRes['category_name']; 
		}
		else {
			$canon_cat = 0; 
			$category_name = ''; 
		}
	}
	
	$custom_title = $params->get('headerText', '');

$dispatcher = JDispatcher::getInstance();
$car_id = 0; 
$dispatcher->trigger('plgGetCurrentCarType', array(&$car_id)); 

if (!empty($custom_title)) {
	$ct = JText::_($custom_title); 
	
	
	$car_name = ''; 
	$dispatcher = JDispatcher::getInstance();
	
	$dispatcher->trigger('plgGetCarName', array(&$car_name)); 
	
	$ct = str_replace('{category_name}', $category_name, $ct); 
	$ct = str_replace('{car_name}', $car_name, $ct); 
	$params->set('custom_title', $ct);
	$params->set('show_title', true);
	$params->set('headerText', $ct);
	$headerText = $ct; 
}


$temp_max = $max_items * 4; 
$db = JFactory::getDBO(); 
$products = array(); 
$limit_sql = ' limit '.(int)$temp_max;
if ($product_group === 'top') {
 
 
 $q = 'select p.virtuemart_product_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as e on e.virtuemart_product_id = p.virtuemart_product_id where p.published = 1 and p.product_parent_id = 0 and p.product_discontinued = 0 and e._FEATURED = 1 AND e._SALE = 0 order by p.pordering asc '; 
 $q = $params->get('sql_top', $q).$limit_sql; 
 $db->setQuery($q); 
 $res = $db->loadAssocList(); 
}
elseif ($product_group === 'sale') {
 
 $q = 'select p.virtuemart_product_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as e on e.virtuemart_product_id = p.virtuemart_product_id where p.published = 1 and p.product_parent_id = 0 and p.product_discontinued = 0 and e._SALE = 1 order by p.pordering asc ';
 $q = $params->get('sql_sale', $q).$limit_sql; 
 $db->setQuery($q); 
 $res = $db->loadAssocList(); 
}
elseif ($product_group === 'akcia') {
 
 $q = 'select p.virtuemart_product_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as e on e.virtuemart_product_id = p.virtuemart_product_id where p.published = 1 and p.product_parent_id = 0 and p.product_discontinued = 0 and e._SALE = 0 and e._FEATURED = 0 and e._AKCIA = 1 order by p.pordering asc ';
 $q = $params->get('sql_akcia', $q).$limit_sql; 
 $db->setQuery($q); 
 $res = $db->loadAssocList(); 
}
elseif ($product_group === 'latest') {
 
 $q = 'select p.virtuemart_product_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as e on e.virtuemart_product_id = p.virtuemart_product_id right join #__virtuemart_product_medias as m on m.virtuemart_product_id = p.virtuemart_product_id where p.published = 1 and p.product_parent_id = 0 and p.product_discontinued = 0 and e._NOVINKA = 1 and e._SALE = 0 order by p.created_on desc ';
 $q = $params->get('sql_latest', $q).$limit_sql; 
 $db->setQuery($q); 
 $res = $db->loadAssocList();

 
}elseif ($product_group === 'samecategories') {
	$pid = JRequest::getVar('virtuemart_product_id', 0); 
	
	$res = array(); 
	if (!empty($pid)) {
		$q = 'select product_canon_category_id from #__virtuemart_products where virtuemart_product_id = '.(int)$pid; 
		$db->setQuery($q); 
		$canon_cat = $db->loadResult(); 
		if (!empty($canon_cat)) {
			
			
		
		if (!empty($car_id)) {
		//$q = 'select pc.virtuemart_category_id from #__virtuemart_product_categories as pc inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc.virtuemart_category_id) and (c.published = 1)) where pc.virtuemart_product_id = '.(int)$pid; 
		$q = 'select p.virtuemart_product_id from #__virtuemart_products as p '; 
		$q .= ' left join #__virtuemart_product_categories as pc on ((pc.virtuemart_product_id = p.virtuemart_product_id) and (pc.virtuemart_category_id = '.(int)$canon_cat.')) '; 
		if (!empty($car_id)) {
		$q .= ' left join #__virtuemart_product_categories as pc2 on ((pc2.virtuemart_product_id = p.virtuemart_product_id) and (pc2.virtuemart_category_id = '.(int)$car_id.'))';
		}
		$q .= ' where (p.virtuemart_product_id != '.(int)$pid.') and p.product_canon_category_id = '.(int)$canon_cat; 
		if (!empty($car_id)) {
			$q .= ' and pc2.virtuemart_category_id IS NOT NULL '; 
		}
		$q .= ' and pc.virtuemart_category_id IS NOT NULL group by p.virtuemart_product_id '; 
		
		//$q .= ' inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc2.virtuemart_category_id) and c.published = 1) '; 
		//IN (select pc.virtuemart_category_id from #__virtuemart_product_categories as pc inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc.virtuemart_category_id) and (c.published = 1)) where pc.virtuemart_product_id = '.(int)$pid.' and p.virtuemart_product_id != pc.virtuemart_product_id) group by p.virtuemart_product_id'; 
		$q .= $limit_sql; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		}
		
		}
		
	}
	
} elseif ($product_group === 'othercategories') {
	$pid = JRequest::getVar('virtuemart_product_id', 0); 
	
	$res = array(); 
	if (!empty($pid)) {
		$q = 'select product_canon_category_id from #__virtuemart_products where virtuemart_product_id = '.(int)$pid; 
		$db->setQuery($q); 
		$canon_cat = $db->loadResult(); 
		if (!empty($canon_cat)) {
			$dispatcher = JDispatcher::getInstance();
			$car_id = 0; 
			$dispatcher->trigger('plgGetCurrentCarType', array(&$car_id)); 
			
		
		if (!empty($car_id)) {
		//$q = 'select pc.virtuemart_category_id from #__virtuemart_product_categories as pc inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc.virtuemart_category_id) and (c.published = 1)) where pc.virtuemart_product_id = '.(int)$pid; 
		$q = 'select p.virtuemart_product_id from #__virtuemart_products as p '; 
		$q .= ' left join #__virtuemart_product_categories as pc on ((pc.virtuemart_product_id = p.virtuemart_product_id) and (pc.virtuemart_category_id != '.(int)$canon_cat.')) '; 
		$q .= ' inner join #__virtuemart_categories as c on ((pc.virtuemart_category_id = c.virtuemart_category_id ) and (c.published = 1)) '; 
		if (!empty($car_id)) {
		$q .= ' left join #__virtuemart_product_categories as pc2 on ((pc2.virtuemart_product_id = p.virtuemart_product_id) and (pc2.virtuemart_category_id = '.(int)$car_id.'))';
		}
		$q .= ' where (p.virtuemart_product_id != '.(int)$pid.' and p.product_canon_category_id != '.(int)$canon_cat.') '; 
		if (!empty($car_id)) {
			$q .= ' and pc2.virtuemart_category_id IS NOT NULL '; 
		}
		$q .= ' and pc.virtuemart_category_id IS NOT NULL group by pc.virtuemart_category_id order by c.ordering asc '; 
		
		//$q .= ' inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc2.virtuemart_category_id) and c.published = 1) '; 
		//IN (select pc.virtuemart_category_id from #__virtuemart_product_categories as pc inner join #__virtuemart_categories as c on ((c.virtuemart_category_id = pc.virtuemart_category_id) and (c.published = 1)) where pc.virtuemart_product_id = '.(int)$pid.' and p.virtuemart_product_id != pc.virtuemart_product_id) group by p.virtuemart_product_id'; 
		$q .= $limit_sql; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		}
		
		}
		
	}
	
}
elseif ($product_group === 'lastseen') {

 $session = JFactory::getSession(); 
 $last_products = $session->get('last_products', array()); 
 
 if (!empty($last_products)) {
 $rev = array_reverse($last_products); 
 
 
 $res = array(); 
 foreach ($rev as $pid) {
	$row = array(); 
	$xa = JRequest::getInt('virtuemart_product_id', 0); 
	if ($xa !== $pid) {
     $row['virtuemart_product_id'] = (int)$pid; 
	 $res[] = $row; 
	}
 }
 
 }
 else {
	 $res = array(); 
 }

 

}



$ids = array(); 
foreach ($res as $row) {
	$product_id = $row['virtuemart_product_id']; 
	$ids[$product_id] = (int)$product_id; 
}
if ($product_group !== 'lastseen') {
  shuffle($ids); 
}
if (count($ids) > $max_items) {
 $ids = array_slice($ids, 0, $max_items); 
}

if (!empty($ids)) {
	$products = $productModel->getProducts ($ids);
	
	//reorder:
	$pp = array(); 
	foreach ($ids as $id) {
		$id = (int)$id; 
		foreach ($products as $kx => $p) {
			$p->virtuemart_product_id = (int)$p->virtuemart_product_id; 
			if ($p->virtuemart_product_id === $id) {
				$pp[] = $p; 
				unset($products[$kx]); 
			}
		}
		//in case IDs don't match: 
		/*
		if (!empty($products)) {
			
			foreach ($products as $kk => $p) {
				$pp[] = $p; 
			}
		}
		*/
	}
	$products = $pp; 
	
		if (!class_exists ('vmCustomPlugin')) {
						require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR . 'vmcustomplugin.php');
					}
				$productModel->addImages($products,1);
				foreach ($products as $k=>$prod) {
					if (empty($prod->images[0])) {
						if (class_exists('VmMediaHandler')) {
						$products[$k]->images[0] = new VmMediaHandler(); 
						}
						else {
							if (class_exists('VmImage')) {
								$products[$k]->images[0] = new VmImage(); 
							}
							
						}
					}
					
					
					
					$products[$k]->stock = $productModel->getStockIndicator($products[$k]);
					
					if (!empty($products[$k]->product_discontinued)) {
						unset($products[$k]); 
					}
					
					
					
				}
}




if(empty($products)) return false;

//shopFunctionsF::sortLoadProductCustomsStockInd($products,$productModel);

if(empty($products)) return false;

$totalProd = 		count( $products);

$currency = CurrencyDisplay::getInstance( );

ob_start();

/* Load tmpl default */
//require(JModuleHelper::getLayoutPath('mod_virtuemart_product',$layout));
require(JModuleHelper::getLayoutPath('mod_virtuemart_product_custom',$layout));
$output = ob_get_clean();
echo $output;



echo vmJsApi::writeJS();
define('RENDERED'.$module->id, 1); 
}