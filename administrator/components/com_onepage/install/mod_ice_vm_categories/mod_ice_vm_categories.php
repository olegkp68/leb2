<?php
/**
 * IceVmCategory Extension for Joomla 2.5 By IceTheme
 * 
 * 
 * @copyright	Copyright (C) 2008 - 2012 IceTheme.com. All rights reserved.
 * @license		GNU General Public License version 2
 * 
 * @Website 	http://www.icetheme.com/Joomla-Extensions/iceaccordion.html
 * @Support 	http://www.icetheme.com/Forums/IceVmCategory/
 *
 */
 

    defined('_JEXEC') or die('Restricted access');
	
	
	 $mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
	$tPath = JPATH_BASE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$module->module.DS.'assets'.DS.'style.css';
	
	$active_cat = JRequest::getVar('virtuemart_category_id', null);
	$product_id = JRequest::getInt('virtuemart_product_id', null);
	$virtuemart_product_id = $product_id; 
	
	$db = JFactory::getDBO(); 
	$q = 'select product_parent_id, product_canon_category_id from #__virtuemart_products where virtuemart_product_id = '.(int)$product_id; 
	$db->setQuery($q); 
	$pdata = $db->loadAssoc(); 
	if (!empty($pdata)) {
		
	if (!empty($pdata['product_parent_id'])) {
		$product_id = (int)$pdata['product_parent_id'];
		
		$q = 'select product_parent_id, product_canon_category_id from #__virtuemart_products where virtuemart_product_id = '.(int)$product_id; 
		$db->setQuery($q); 
		$pdata = $db->loadAssoc(); 
		
		if (!empty($pdata)) {
		  if (!empty($pdata['product_canon_category_id'])) {
			  
		  if (empty($active_cat)) $active_cat = (int)$pdata['product_canon_category_id'];
		  }
		}
		
	}
	else  {
		
		if (!empty($pdata['product_canon_category_id'])) {
		 if (empty($active_cat)) $active_cat = (int)$pdata['product_canon_category_id'];
		}
		
	}
	
	if ((!empty($product_id)) && (!empty($active_cat))) {
		$db = JFactory::getDBO(); 
		$q = 'select virtuemart_product_id from #__virtuemart_product_categories where virtuemart_product_id = '.(int)$virtuemart_product_id.' and virtuemart_category_id = '.(int)$active_cat; 
		$db->setQuery($q); 
		$rr = $db->loadResult(); 
		if (empty($rr)) {
				$q = 'select virtuemart_category_id from #__virtuemart_product_categories where virtuemart_product_id = '.(int)$virtuemart_product_id.' order by ordering asc limit 1'; 
				$db->setquery($q); 
				$new_cat = (int)$db->loadResult(); 
				if (!empty($new_cat)) {
					$active_cat = $new_cat; 
					JRequest::setVar('virtuemart_category_id', $new_cat);
				}
		}
	}
	
	if ($active_cat === null) {
		$option = JRequest::getVar('option', null); 
		$option = JRequest::getVar('view', null); 
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', null); 
		if (!is_null($virtuemart_product_id)) {
			
			
			//$q = 'select `product_canon_category_id` from #__virtuemart_productswhere virtuemart_product_id = '.(int)$virtuemart_product_id; 
			
			$q = 'select c.virtuemart_category_id from #__virtuemart_product_categories as c, #__virtuemart_categories as cat where c.virtuemart_product_id = '.(int)$virtuemart_product_id.' and (c.virtuemart_category_id = cat.virtuemart_category_id and cat.published = 1) order by c.ordering desc limit 1'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$res = $db->loadResult(); 
			
			if (!empty($res)) {
				$active_cat = (int)$res; 
				
				$q = 'update `#__virtuemart_products` set `product_canon_category_id` = '.(int)$active_cat.' where `virtuemart_product_id` = '.(int)$product_id; 
				$db->setQuery($q); 
				$db->execute(); 
				
			}
			
		}
	}
	}
	
	$css = ''; 
	
	$user_id = JFactory::getUser()->get('id'); 
	$has_prods = false; 
	$has_prices = false; 
	if (!empty($user_id)) {
	  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc'; 
	  $db->setQuery($q); 
	  $sgres = $db->loadAssocList(); 
	  $sg = array(); 
	  if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  if (!empty($sg)) {
		  $q = 'select virtuemart_product_id from #__virtuemart_product_shoppergroups where virtuemart_shoppergroup_id IN ('.implode(',', $sg).') order by `id` asc limit 1'; 
		  $db->setQuery($q); 
		  $has_prods = $db->loadResult(); 
		  }
		  
	  }
	 
	  if (!empty($sg)) {
		  $q = 'select virtuemart_product_id from #__virtuemart_product_prices where virtuemart_shoppergroup_id IN ('.implode(',', $sg).') order by `virtuemart_product_price_id` asc limit 1'; 
		  $db->setQuery($q); 
		  $has_prices = $db->loadResult(); 
		  
	  }
	}
	
	
	
	if (!empty($has_prices)) {
		$css .= ' body li.sg_prices.lofitem1.ice-parent { display: block; } '; 
	}
	else {
		$css .= ' li.sg_prices.lofitem1.ice-parent { display: none; } '; 
	}
	
	if (!empty($has_prods)) {
		$css .= ' body li.sg_prods.lofitem1.ice-parent { display: block; } '; 
	}
	else {
		$css .= ' li.sg_prods.lofitem1.ice-parent { display: none; } '; 
	}
	
	
	if ((!empty($has_prods)) || (!empty($has_prices))) {
		$css .= ' body li.sg_all.lofitem1.ice-parent { display: block; } '; 
	}
	else {
		$css .= ' li.sg_all.lofitem1.ice-parent { display: none; } '; 
	}
	
	
	
	if (is_array($active_cat)) $active_cat = reset($active_cat); 
	if (!empty($active_cat))
	{
		$css .= ' 
		
		
li.cat_'.$active_cat.' > a {
 background-color: #eee !important; 
}
		
li.cat_'.$active_cat.' ul.cat_'.$active_cat.' { 
display: block; 
}
 
.lofmenu_virtuemart li.cat_'.$active_cat.' ul:before { 
display: block; 
}

.lofmenu_virtuemart ul.hascat_'.$active_cat.' {
 display: block !important; 
 position: static !important; 
 border: 0px none !important; 
 box-shadow: none !important; 
 margin-left: 5% !important; 
 width: 95% !important; 
}

.lofmenu_virtuemart ul.hascat_'.$active_cat.' li.has_cat_'.$active_cat.' > a {
	background-color:#eee;
	color: #222;
}

.lofmenu_virtuemart .lofmenu .lofitem1 ul.cat_'.$active_cat.', 
.lofmenu_virtuemart .lofmenu > .cat_'.$active_cat.' > a:before
{
  position: static !important; 
}


.lofmenu_virtuemart .lofmenu ul.cat_'.$active_cat.' {
 border: 0px none; 
 box-shadow: none; 
 margin-left: 5%; 
 width: 95%; 

}
		
		
		'; 
		
		
	}
	
	$document->addStyleDeclaration($css); 
	
	
	if( file_exists($tPath) ){
		JHTML::stylesheet( 'templates/'.$mainframe->getTemplate().'/html/'.$module->module.'/assets/style.css');
	}else{
		$document->addStyleSheet(JURI::base().'modules/mod_ice_vm_categories/assets/style.css');
	}
	
	$cfd = JPATH_SITE.DS.'media'.DS.'ice_menu'; 
	$cfd2 = JPATH_CACHE.DS.'ice_menu'; 
	
	if (!file_exists($cfd2)) {
		mkdir($cfd2); 
	}
	
	
	if (!file_exists($cfd))
	{
	  mkdir($cfd); 
	}
	else
	{
		
	}
	
	$cf = $cfd2.DS.'icemenu.html'; 
	
	$css = $cfd.DIRECTORY_SEPARATOR.'images.css'; 
	
	if (isset($params) && (method_exists($params, 'get'))) {
		$cache = $params->get('cache', 0); 
		$cache = (int)$cache; 
		
		
		$field_sort = $params->get('sort', 'id');
		$ordering 	= $params->get('ordering', 'asc');
		$show_image = $params->get('show_image',0);
	}
	else {
		$cache = 1; 
		$cache = (int)$cache; 
		
		$show_image = false; 
		$ordering = 'asc'; 
		$field_sort = 'id'; 
	}

	
	
	
	if (($cache === 1) && (file_exists($cf) && ($cache) && ((file_exists($css)) || (empty($show_image)))))
	{
	  echo file_get_contents($cf); 
	}
	elseif (($cache === 2) && (file_exists($cf))) {
		//do nothing
		$mtime = filemtime($cf); 
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		
		$fullurl = $root.'cache/ice_menu/icemenu.html?m='.$mtime;
	   ?><div data-loadurl="<?php echo $fullurl; ?>" class="on_demand_html" data-media-from="1250px" ><noscript>Zapnite JavaScript
	   </noscript></div><?php
	   
	   
	   $preload = '<link rel="fetch" href="'.$fullurl.'" media="(min-width: 1250px)" />' ."\n";
	   $document = JFactory::getDocument();
	   $document->addCustomTag($preload);
	}
	else
	{
	
	
		if ($show_image) {
		$css = $cfd.DIRECTORY_SEPARATOR.'images.css'; 
		if (file_exists($css))
		unlink($css); 
		
	
   
    if( !defined('PhpThumbFactoryLoaded') ) {
	require_once dirname(__FILE__).DS.'libs'.DS.'phpthumb'.DS.'ThumbLib.inc.php';
		define('PhpThumbFactoryLoaded',1);
	}
	}
    
	if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
	$config= VmConfig::loadConfig();
	if (!class_exists( 'VirtueMartModelVendor' )) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
	if(!class_exists('TableMedias')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'medias.php');
	if(!class_exists('TableCategories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'categories.php');
	if (!class_exists( 'VirtueMartModelCategory' )) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'category.php');
    require_once (dirname(__FILE__).DS.'helper.php');

   

    $lang = JFactory::getLanguage();
    
    JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
    ob_start(); 
    
    $categories = vmCategoryHelper::getHtml($field_sort, $ordering,1, $params, $active_cat);
    require(JModuleHelper::getLayoutPath('mod_ice_vm_categories'));
   $cache_print = ob_get_clean(); 
   
   if ($cache) {
   if (!file_exists($cfd))
	{
	  mkdir($cfd); 
	}
   file_put_contents($cf, $cache_print); 
   }
     //echo $cache_print; 
	if ($cache === 2) {
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
	   ?><div data-loadurl="<?php echo $root.'cache/ice_menu/icemenu.html'; ?>" class="on_demand_html" data-media-from="1250px" ></div><?php
	}
	else {
		echo $cache_print; 
	}

   }
   
   
   if (!empty($show_image))
   if (!defined('MOD_VMICE_ADDED'))
		{
		JHtml::stylesheet(Juri::base().'/media/ice_menu/images.css'); 
		define('MOD_VMICE_ADDED', 1); 
		}
		
?><script>
var el = jQuery('.on_demand_html'); 
if (el.length) {
var isVisible = el.is(':visible');
		if (isVisible) {
		var url = el.data('loadurl'); 
		if (url) {
			el.load( url );
			el.data('loaded', 1); 
			console.log('category written'); 
		}
		}
}
</script>		
		