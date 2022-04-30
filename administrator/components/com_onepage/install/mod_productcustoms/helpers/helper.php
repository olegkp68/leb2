<?php  
defined('_JEXEC')or die;

class PCH {
	
	public static $getget; 
	public static $datacats; 
	public static $getcustoms; 
	public static $getmf; 
    public static $getdatacats;
	
	public static function checkPerm() {
	   $user = JFactory::getUser(); 
       $isroot = $user->authorise('core.admin');	
	   if ($isroot === true) return true; 
	   return false; 
   }
   
   
   public static function createTable() {
	   
	   $db = JFactory::getDBO(); 
	   
	   $q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_customs_ext` (
  `virtuemart_custom_id` int(1) UNSIGNED NOT NULL,
  `group_value` varchar(1024) DEFAULT NULL,
  `group_type` tinyint(1) UNSIGNED NOT NULL,
  UNIQUE KEY `unique_selector` (`virtuemart_custom_id`,`group_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
$db->setQuery($q); 
$db->execute(); 
   }
   
	public static function getFilterCats() { 
		if (isset(self::$datacats)) return self::$datacats;
		
		$categories = self::getCatsFromGet(); 
		
		$params = self::getParams(); 
		$primary_category_id = JRequest::getInt('primary_virtuemart_category_id', $params->get('default_category')); 
		
		if (empty($categories)) $wasEmpty = true; 
		else  $wasEmpty = false; 
		
		$db = JFactory::getDBO(); 
		if (empty($categories)) {
			$primary_category_id = (int)$primary_category_id; 
			
			
			$q = 'select cc.category_child_id from #__virtuemart_category_categories as cc, #__virtuemart_categories as c where cc.category_parent_id = '.(int)$primary_category_id.' and cc.category_child_id = c.virtuemart_category_id and c.published = 1'; 
			$db->setQuery($q); 
			$res = $db->loadAssocList(); 
			
			foreach ($res as $row) {
				$ic = (int)$row['category_child_id']; 
				$categories[$ic] = $ic; 
			}
			
			
		}
		
		

		self::makeUnique($categories); 
		
		if (!$wasEmpty)
		if (!empty($categories)) {
		  $other = array(); 
		  self::getListChilds($categories, $other, true); 
		}
		
		
		
		
		if (method_exists('VmConfig', 'setdbLanguageTag')) {
		if(empty(VmConfig::$vmlang)) {
			VmConfig::setdbLanguageTag();
		}
		if (empty(VmConfig::$vmlang)) return; 

		
		$ret = array(); 
		
		$q = 'select l.`category_name`, l.`virtuemart_category_id` from `#__virtuemart_categories_'.$db->escape(VmConfig::$vmlang).'` as l left join #__virtuemart_categories as c on c.virtuemart_category_id = l.virtuemart_category_id where '; 
		$or = array(); 
		foreach ($categories as $id) {
		  
		  //$q = 'select l.`category_name` from `#__virtuemart_categories_'.$db->escape(VmConfig::$vmlang).'` as l right join #__virtuemart_categories as c on c.virtuemart_category_id = l.virtuemart_category_id where c.virtuemart_category_id ='.(int)$id.' order by c.ordering '; 
		  $or[] = '( l.virtuemart_category_id ='.(int)$id.' )'; 
		  
		}
		if (!empty($or)) {
		$q .= implode(' OR ', $or); 
		$q .= ' order by c.ordering '; 					 
		$db->setQuery($q); 
		$cat_names = $db->loadAssocList();
		
		
		foreach ($cat_names as $row) {
			$cat_name = $row['category_name']; 
			$id = (int)$row['virtuemart_category_id']; 
		  if (!empty($cat_name)) {
		   $cat = new stdClass(); 
		   $cat->virtuemart_category_id = (int)$id; 
		   $cat->category_name = (string)$cat_name; 
		   //COM_VIRTUEMART_CATEGORIES
		   $ret[$id] = $cat; 
		  }
		  }
		}
		}
		
		
		
		self::$datacats = $ret; 
		return $ret; 
		
	}
	
	public static function getParams($id=0, $module=null)
	{
		
		jimport( 'joomla.registry.registry' );
		
		if ((!empty($module)) && (!empty($module->params))) {
		 return Jregistry($module->params); 
		}
		
		if (empty($id))
		$id = JRequest::getVar('module_id', null); 
	
		if (!empty($id))
		 {
			static $params_id; 
			if (empty($params_id)) $params_id = array(); 
			
			if (empty($params_id[$id])) {
		    $id = (int)$id; 
			$q = 'select `params` from `#__modules` where `id` = '.$id.' and `module` = \'mod_productcustoms\' limit 1'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$params_s = $db->loadResult(); 
			$params_id[$id] = $params_s;
			}
			
			
			
			if (!empty($params_s))
			{
			$params = new JRegistry($params_s); 
			
			return $params; 
			}
			
		 }
		 
		 {
			static $myparams; 
			if (!empty($myparams)) 
			{
				$params_s = $myparams; 
			}
			else {
			$q = 'select `params` from `#__modules` where `module` = \'mod_productcustoms\' and `published` = 1 limit 1'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$params_s = $db->loadResult(); 
			$myparams = $params_s; 
			}
			
			
			if (!empty($params_s)) {
			$params = new JRegistry($params_s); 
			
			return $params; 
			}
		 }
		 
		 $r = new JRegistry(); 
		 return $r; 
		 
		 
	}
	
	public static function getChildCustoms(&$customs, &$parents=array()) {
		
		$nc = array(); 
		foreach ($customs as $k=>$c) {
			$c = (int)$c; 
			if (empty($c)) unset($customs[$k]); 
			$nc[$c] = $c; 
		}
		$db = JFactory::getDBO(); 
		$customs = $nc; 
		
		if (empty($customs)) return array(); 
		
		$return_customs = $customs; 
		
		$q = 'select `custom_parent_id`, `virtuemart_custom_id` from `#__virtuemart_customs` where `custom_parent_id` IN ('.implode(',', $return_customs).') '; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		$parents = array(); 
		if (!empty($res)) {
			foreach ($res as $row) {
				$newid = (int)$row['virtuemart_custom_id']; 
				$return_customs[$newid] = $newid; 
				
				$current_id = (int)$row['custom_parent_id']; 
				if (!isset($parents[$newid])) $parents[$newid] = array(); 
				
				$parents[$newid][$current_id] = $current_id; 
			}
		}
		
		
		return $return_customs; 
		
	}
	
	public static function getParentCustoms(&$customs, &$groups=array()) {
		
		$nc = array(); 
		foreach ($customs as $k=>$c) {
			$c = (int)$c; 
			if (empty($c)) unset($customs[$k]); 
			$nc[$c] = $c; 
		}
		$db = JFactory::getDBO(); 
		$customs = $nc; 
		
		if (empty($customs)) return array(); 
		
		$return_customs = $customs; 
		
		$q = 'select `custom_parent_id`, `virtuemart_custom_id` from `#__virtuemart_customs` where `virtuemart_custom_id` IN ('.implode(',', $return_customs).') '; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		$groups = array(); 
		if (!empty($res)) {
			foreach ($res as $row) {
				$newid = (int)$row['custom_parent_id']; 
				$return_customs[$newid] = $newid; 
				
				$current_id = (int)$row['virtuemart_custom_id']; 
				if (!isset($groups[$newid])) $groups[$newid] = array(); 
				
				$groups[$newid][$current_id] = $current_id; 
			}
		}
		
		
		return $return_customs; 
		
	}
	
	public static function canShowChilds($customs, &$groups=array()) {
		
		if (empty($customs)) return false; 
		
		
		$params = self::getParams(); 
		$customs_with_childs = $params->get('customs_with_childs', array()); 
		if (empty($customs_with_childs)) $customs_with_childs = array(); 
		
		
		
		$all_customs = self::getParentCustoms($customs, $groups); 
		
		
		
		if (empty($all_customs)) return false; 
		
		$ng = array(); 
		$ret = self::getChildCustoms($customs_with_childs, $ng); 
		
		foreach ($ret as $k=>$r) {
			$customs_with_childs[$k] = $r; 
		}
		
		
		foreach ($customs_with_childs as $id) {
			$id = (int)$id; 
			foreach ($all_customs as $id2) {
				$id2 = (int)$id2; 
				if ($id2 === $id) {
					
					return true; 
				}
			}
		}
		
		if (empty($customs_with_childs)) return false; 
		return false; 
	}
	
	public static function getCategoryProducts($keyword, $prods=5, $popup=false, $order_by='', $categories = array(), $manufs=array(), $customs=array()) {
		
		$get = self::getget(); 				 
		
		
		if (empty($categories)) {
		 $categories = self::getCatsFromGet(); 
		}
		
		$primary_virtuemart_category_id = JRequest::getVar('primary_virtuemart_category_id', 0); 
		if (!empty($primary_virtuemart_category_id)) {
		$test = $categories; 
		unset($test[$primary_virtuemart_category_id]);
		if (!empty($test)) {
			//we have a child category selected: 
			unset($categories[$primary_virtuemart_category_id]); 
		}
		}
		
		if ((empty($categories)) && (empty($customs)) && (empty($manufs))) {
			$params = self::getParams(); 
			$dc = (int)$params->get('default_category', ''); 
			if (!empty($dc)) {
				$categories[$dc] = $dc; 
			}
			
			
		}
		
		//left join
		$lj = array(); 
		
		
		if (empty($manufs)) {
		 $manufs = self::getManufsFromGet(); 
		}
		if (empty($customs)) {
		 $customs = self::getCustomsFromGet(); 
		}
		
		self::makeUnique($categories); 
		
		
		$gcu = ''; 
		
		//find child categories: 
		$custom_groups = array(); 
		$canshowchilds = self::canShowChilds($customs, $custom_groups); 
		
		
		$limitstart = JRequest::getInt('limitstart', JRequest::getVar('start', 0)); 
		
		
		//when (isnull(p.`product_parent_id`)) then p.`virtuemart_product_id`
		//
	if (!$canshowchilds) {
		$s = ' distinct p.`virtuemart_product_id` ';
				  
	}
	else {
	  $s = ' p.`virtuemart_product_id`, p.`product_parent_id` '; 
	}
		/*
		if (!empty($customs)) {
			$s .= ', cf.virtuemart_custom_id as cid '; 
		}
		*/
	
	/*
	$s .= ', '; 
	if (!empty($categories))  {
	 $s .= ' SUM(cat.ordering+'; 
	}
	$s .= 'p.pordering ';
	if (!empty($categories)) {
		$s .= ')'; 
	}
	$s .= ' as myorder '; 
	*/
	$s .= ' from '; 
	$qf = array(); 
	$qf[] = ' #__virtuemart_products as p '; 
	//$qf[] = ' #__virtuemart_products as parents '; 
	if (!empty($categories)) {
	 self::getListChilds($categories); 
	 $lj['cat'] = ' inner join #__virtuemart_product_categories as `cat` on (`cat`.`virtuemart_product_id` = p.`virtuemart_product_id` ) '; 
	}
	else {
		if (!$canshowchilds) {
			//$qf[] = ' #__virtuemart_product_categories as cat '; 
		}
	}
	if (!empty($customs)) {
	
	// $qf[] = ' #__virtuemart_customs as c '; 
	 
	 foreach ($custom_groups as $ind => $local_group) {
	   $lj['cf'.$ind] = ' inner join #__virtuemart_product_customfields as `cf'.$ind.'` on (`cf'.$ind.'`.`virtuemart_product_id` = p.`virtuemart_product_id` ) '; 
	 }
	}
	

	
	
	
	if (!empty($categories)) {
	$gc = array(); 
	foreach ($categories as $cat_id) {
		$cat_id = (int)$cat_id; 
		if (empty($cat_id)) continue; 
		$gc[] = ' ( cat.virtuemart_category_id = '.(int)$cat_id.') ';
	}
	if (!$canshowchilds) {
		$wc[] = ' (('.implode(' OR ', $gc).') and (cat.virtuemart_product_id = p.virtuemart_product_id)) '; 
	}
	else {
	  $wc[] = ' ('.implode(' OR ', $gc).') and ((cat.virtuemart_product_id = p.product_parent_id) or (cat.virtuemart_product_id = p.virtuemart_product_id)) '; 
	}
	}
	else {
		if (!$canshowchilds) {
		 //$wc[] = ' (( cat.virtuemart_category_id > 0 ) and (cat.virtuemart_product_id = p.virtuemart_product_id)) ';
		}
	}
	
	if (!empty($customs)) {
	 $qcu = ''; 
	 //if (empty($canshowchilds)) 
	 {
	  //$qcu .= ' ('; 
	 }
	  foreach ($custom_groups as $ind => $local_group) {
	   $qcua[] = ' (`cf'.$ind.'`.virtuemart_product_id = p.virtuemart_product_id) '; //and (`cf'.$ind.'`.virtuemart_custom_id = c.virtuemart_custom_id) '
	  }
	 //$qcu .= ' (cf.virtuemart_product_id = p.virtuemart_product_id) '; 
	 
	 //if (empty($canshowchilds)) 
	 {
	 
	 $qcu .= '('.implode(' and ', $qcua).') '; //or (cf.virtuemart_product_id = p.product_parent_id)) '; 
	 
	 }
	 //$qcu .= ' and (cf.virtuemart_custom_id = c.virtuemart_custom_id) '; 
	 $gg = array(); 
	 
	
	 
	 foreach ($custom_groups as $ind => $local_group) {
		 $gg2 = array(); 
		 foreach ($local_group as $cid) {
			  
				$gg2[] = ' (`cf'.$ind.'`.virtuemart_custom_id = '.(int)$cid.') '; 
			  
		 }
		 $gg[] = ' ( '.implode(' or ', $gg2).' ) '; 
	     //$qcu .= ' c.virtuemart_custom_id IN ('.implode(',', $customs).') )' ; 
	 }
	 $gcu .= ' ( '.implode(' and ', $gg).' ) '; 
	 
	 
	 if (!empty($gg)) {
	   $wc[] = $qcu.' and '.$gcu;
	 }	 
	}
	
	//$wc[] = ' (parents.virtuemart_product_id = p.product_parent_id ) '; 
	
	/*
	if ($canshowchilds) {
	  $wc[] = ' ( p.product_parent_id > 0 ) '; 
	}
	else {
		$wc[] = ' ( p.product_parent_id = 0 ) '; 
	}
	*/
	$custom_order_by = ''; 
	$wcc = array(); 
	$custom_parent_where = ''; 
	$myqf = $get['qf']; 
	$onlysale = false; 
	foreach ($myqf as $special) {
		if ($special === 1) {
			$lj['helios'] = ' inner join #__virtuemart_products_ext as `ext` on ((p.virtuemart_product_id = ext.virtuemart_product_id) and (ext.`_SALE` = 1 )) '; 
			//$qf['helios'] = ' #__virtuemart_products_ext as `ext` '; 
			//$wc['helios'] = ' p.virtuemart_product_id = ext.virtuemart_product_id ';
			//$wcc[] = ' ext.`_SALE` = 1 '; 
			//$custom_parent_where = ' p.product_parent_id > 0 and '; 
			$onlysale = true; 
		}
		if ($special === 2) {
			$lj['helios'] = ' inner join #__virtuemart_products_ext as `ext` on (p.virtuemart_product_id = ext.virtuemart_product_id)  '; 
			//$qf['helios'] = ' #__virtuemart_products_ext as `ext` '; 
			//$wc['helios'] = ' p.virtuemart_product_id = ext.virtuemart_product_id ';
			$wcc[] = ' (ext.`_AKCIA` = 1) and (ext.`_SALE` = 0) '; 
		}
		if ($special === 3) {
			$lj['helios'] = ' inner join #__virtuemart_products_ext as `ext` on (p.virtuemart_product_id = ext.virtuemart_product_id)  '; 
			//$qf['helios'] = ' #__virtuemart_products_ext as `ext` '; 
			//$wc['helios'] = ' p.virtuemart_product_id = ext.virtuemart_product_id ';
			$wcc[] = ' ext.`_FEATURED` = 1 '; 
		}
		if ($special === 4) {
			$lj['helios'] = ' inner join #__virtuemart_products_ext as `ext` on (p.virtuemart_product_id = ext.virtuemart_product_id)  '; 
			//$wc['helios'] = ' p.virtuemart_product_id = ext.virtuemart_product_id ';
			$wcc[] = ' ext.`_NOVINKA` = 1 '; 
			$custom_order_by = ' p.`created_on` desc'; 
			//$custom_orderby_dir = ' desc '; 
		}
		
		if ($special === 12) {
			
			
			$user_id = JFactory::getUser()->get('id'); 
	$has_prods = false; 
	$has_prices = false; 
	if (!empty($user_id)) {
		$db = JFactory::getDBO(); 
	  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc'; 
	  $db->setQuery($q); 
	  $sgres = $db->loadAssocList(); 
	  
	   $sg = array(); 
	  if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  
		}
		else {
			return array(); 
		}
		  
	  
	  
	  if (!empty($sg)) {
		  
		  
		  	$qf['helios12'] = ' #__virtuemart_product_shoppergroups as `psg` '; 
			$wc['helios12'] = ' p.virtuemart_product_id = `psg`.virtuemart_product_id ';
			$wcc[] = ' `psg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).') '; 
		  
	  }
	  else {
		  $wcc[] = ' 1=0 '; 
	  }
	 
	  
	}
	else {
		return array(); 
	}
			
		
			
		}
		if ($special === 11) {
			
			
			$user_id = JFactory::getUser()->get('id'); 
	$has_prods = false; 
	$has_prices = false; 
	if (!empty($user_id)) {
		$db = JFactory::getDBO(); 
	  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc '; 
	  $db->setQuery($q); 
	 $sgres = $db->loadAssocList(); 
	  
	   $sg = array(); 
	  if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  
		}
		else {
			return array(); 
		}

	  if (!empty($sg)) {
		 
		  
		  $s = ' distinct IF (`p`.`product_parent_id` > 0, `p`.`product_parent_id`, `p`.`virtuemart_product_id`) as `virtuemart_product_id` from '; 
		  $qf['helios11'] = ' #__virtuemart_product_prices as `psg` '; 
			$wc['helios11'] = ' ((p.virtuemart_product_id = `psg`.virtuemart_product_id) and (`psg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).') )) ';
			$wcc[] = ' `psg`.`price_quantity_start` <= 0 '; 
	  }
	  else {
		  $wcc[] = ' 1=0 '; 
	  }
	}
	else {
		return array(); 
	}
			
			
			
		}
		
		if ($special === 13) {
			
			
			$user_id = JFactory::getUser()->get('id'); 
	$has_prods = false; 
	$has_prices = false; 
	if (!empty($user_id)) {
		$db = JFactory::getDBO(); 
	  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc '; 
	  $db->setQuery($q); 
	 $sgres = $db->loadAssocList(); 
	  
	   $sg = array(); 
	  if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  
		}
		else {
			//show no products: 
			return array(); 
		}

	  if (!empty($sg)) {
		 
		  
		  $s = ' distinct IF (`p`.`product_parent_id` > 0, `p`.`product_parent_id`, `p`.`virtuemart_product_id`) as `virtuemart_product_id` from  '; 
		  $lj['helios11'] = 'left join #__virtuemart_product_prices as `psg` on ((p.virtuemart_product_id = `psg`.virtuemart_product_id) and (`psg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).') ) and (`psg`.`price_quantity_start` <= 0))'; 
			
		  $lj['helios12'] = 'left join #__virtuemart_product_shoppergroups as `psg2` on ((p.virtuemart_product_id = `psg2`.virtuemart_product_id) and  `psg2`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).')) '; 
		  $wc[] = ' `psg2`.virtuemart_product_id IS NOT NULL or `psg`.virtuemart_product_id IS NOT NULL ';
			
	  }
	  else {
		  $wcc[] = ' 1=0 '; 
	  }
	}
	else {
		return array(); 
	}
			
			
			
		}
		
	}
	
	if (empty($sg)) {
		if (empty($user_id)) {
			$lj['helios12'] = ' left join #__virtuemart_product_shoppergroups as `psg2` on (`psg2`.virtuemart_product_id = p.`virtuemart_product_id`) '; //and  `psg2`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).')) '; 
			//$wc['helios12'] = ' p.virtuemart_product_id = `psg`.virtuemart_product_id ';
			$wc[] = ' ((`psg2`.`virtuemart_shoppergroup_id` IS NULL) or (`psg2`.`virtuemart_shoppergroup_id` = 1)) '; //IN ('.implode(',', $sg).') '; 
		}
		else {
			
			$db = JFactory::getDBO(); 
			$q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc '; 
			$db->setQuery($q); 
			$sgres = $db->loadAssocList(); 
	  
			$sg = array(); 
			if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  
		}
		else {
			//show no products: 
			$sg = array(); 
		}
			$sg[0] = '0'; 
			
			$lj['helios12'] = 'left join #__virtuemart_product_shoppergroups as `psg2` on (p.`virtuemart_product_id` = `psg2`.virtuemart_product_id) '; //and  `psg2`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).')) '; 
			$wc[] = ' ((`psg2`.`virtuemart_shoppergroup_id` IS NULL) or (`psg2`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).'))) '; 
		}
	}
	
	if (!empty($wcc)) {
		$wc[] = ' ('.implode(' or ', $wcc).' ) '; 
	}
	
	if (empty($wc)) {
		$wc[] = ' 1=1 '; 
	}
	
	
	$w = ' where '; 
	
	
	
	$w .= ' p.`published` = 1 and '; 
	$w .= ' p.`product_discontinued` < 1 and '; 
	if (empty($custom_parent_where)) {
	 $w .= ' p.product_parent_id = 0 and  ';
	}
	else {
		$w .= $custom_parent_where; 
	}	
	 
	$mylimit = (int)$prods + 1; 
	
	$q = $s.implode(',', $qf).implode(' ', $lj).' '.$w.implode(' and ',$wc); 
	
	$orderby = ''; 
	$orderby .= ' order by '; 
	
	if (!empty($custom_order_by)) {
		$orderbyA[] = $custom_order_by; 
	}
	
	
	if (!empty($categories))  {
	 $orderbyA[] = ' cat.`ordering` asc '; 
	}
	
	
	$orderbyA[] = ' p.`pordering` asc ';
	
	$orderby .= implode(',', $orderbyA); 
	$orderby_dir = ''; 
	//$orderby .= ' myorder '; 
	if (!empty($custom_orderby_dir)) {
		//$orderby_dir = $custom_orderby_dir;
	}
	else {
		//$orderby_dir = 'asc'; 
	}
	
	$mylimitsql = ' limit '.$limitstart.','.$mylimit; 
	//$q .= ' limit 0,'.$mylimit; 
	
	
	
	$db = JFactory::getDBO(); 
	if (class_exists('RupHelper')) {
		
		$eq = 'select '.$q.' '.$orderby.' '.$orderby_dir.' '.$mylimitsql; 
		
			$params = self::getParams(); 
			$debug = $params->get('debug', false); 
		$res = RupHelper::runQuery($db, $eq, $debug); 


	}
	else {
	 $eq = 'select '.$q.' '.$orderby.' '.$mylimitsql; 
	 $db->setQuery($eq); 
	 $res = $db->loadAssocList(); 
	 
	 
	 
	 
	
	}
	
	if (empty($res)) {
		
		$orderby = ''; 
	$orderby .= ' order by '; 
	
	if (!empty($categories))  {
	 $orderby .= '  cat.ordering, '; 
	}
	
	$orderby .= ' p.pordering ';
		
		if (!empty($limitstart)) {
			if ($orderby_dir === 'asc') $orderby_dir = 'desc'; 
			
			$mylimit = (int)$prods - 1; 
			
			$eq = 'SELECT '.$q.' '.$orderby.' '.$orderby_dir.' limit 0,'.$mylimit; 
			
			if (class_exists('RupHelper')) {
		
		
		
			$params = self::getParams(); 
			$debug = $params->get('debug', false); 
		$res = RupHelper::runQuery($db, $eq, $debug); 
	}
	else {
	 
	 $db->setQuery($eq); 
	 $res = $db->loadAssocList(); 
	}
			
		}
	}
	
	
	
	if (!empty($onlysale)) {
		$pids = array(); 
		foreach ($res as $row) {
			$product_id = (int)$row['virtuemart_product_id']; 
			$pids[$product_id] = $product_id; 
		}
		if (!empty($pids)) {
		$q = 'select p.virtuemart_product_id, p.product_parent_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as `ext` on ((p.virtuemart_product_id = ext.virtuemart_product_id) and (ext.`_SALE` = 1 )) where p.product_discontinued < 1 and p.product_parent_id > 0 and p.product_parent_id IN ('.implode(',', $pids).')'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$newRes = array(); 
		$parents = array(); 
		foreach ($res as $row) {
			$product_id = (int)$row['virtuemart_product_id']; 
			$parent_id = (int)$row['product_parent_id']; 
			$newRes[$product_id] = array(); 
			$newRes[$product_id]['virtuemart_product_id'] = $product_id; 
			$parents[$parent_id] = $parent_id; 
			
		}
		foreach ($pids as $parent_id) {
			if (!in_array($parent_id, $newRes)) {
				$newRes[$parent_id] = array(); 
				$newRes[$parent_id]['virtuemart_product_id'] = $parent_id; 
			}
		}
		if (!empty($newRes)) {
			$res = $newRes; 
			$canshowchilds = true; 
		}
		}
	}
	
	
	    $ids = array(); 
		if (!empty($res)) {
		
		//var_dump($res); die(); 
		
		if (!empty($custom_groups)) {
		 //self::filterRes($res, $custom_groups, $canshowchilds, $limitstart, $prods); 
		 $mylimit2 = $prods+1; 
		 self::filterRes($res, $customs, $custom_groups, $canshowchilds, $limitstart, $mylimit2); 
		}
		
		if (!$canshowchilds) {
			foreach ($res as $k=>$v) {
				if (empty($v['product_parent_id'])) {
				 $v['virtuemart_product_id'] = (int)$v['virtuemart_product_id']; 
				 $ids[$v['virtuemart_product_id']] = $v['virtuemart_product_id']; 
				}
				else {
					$pid = (int)$v['product_parent_id']; 
					$ids[$pid] = $pid; 
				}
			}
		}
		else {
		foreach ($res as $k=>$v) {
			$v['virtuemart_product_id'] = (int)$v['virtuemart_product_id']; 
			$ids[$v['virtuemart_product_id']] = $v['virtuemart_product_id']; 
		}
		}
		}
		
		
		
		
		return $ids; 
		
	}
	
	
	public static function getCategoryProductsBACKUP($keyword, $prods=5, $popup=false, $order_by='', $categories = array(), $manufs=array(), $customs=array()) {
		
		
		
		if (empty($categories)) {
		 $categories = self::getCatsFromGet(); 
		}
		
		
		
		
		if (empty($manufs)) {
		 $manufs = self::getManufsFromGet(); 
		}
		if (empty($customs)) {
		 $customs = self::getCustomsFromGet(); 
		}
		
		self::makeUnique($categories); 
		
		
		
		
		//find child categories: 
		$custom_groups = array(); 
		$canshowchilds = self::canShowChilds($customs, $custom_groups); 
		
		
		$limitstart = JRequest::getInt('limitstart', 0); 
		
		
		//when (isnull(p.`product_parent_id`)) then p.`virtuemart_product_id`
		//
	if (!$canshowchilds) {
		$s = 'select DISTINCT case  
				when  p.`product_parent_id` > 0 then p.`product_parent_id` 
				when (p.`product_parent_id` = 0) then p.`virtuemart_product_id` 
				
				
				else p.`virtuemart_product_id` 
				 END as `virtuemart_product_id`, 0 as `product_parent_id` '; 
				
	}
	else {
	  $s = 'select p.`virtuemart_product_id`, p.`product_parent_id` '; 
	}
		if (!empty($customs)) {
			$s .= ', cf.virtuemart_custom_id as cid '; 
		}
		
	$s .= ' from '; 
	$qf = array(); 
	$qf[] = ' #__virtuemart_products as p '; 
	//$qf[] = ' #__virtuemart_products as parents '; 
	if (!empty($categories)) {
	 self::getListChilds($categories); 
	 $qf[] = ' #__virtuemart_product_categories as cat '; 
	 
	}
	if (!empty($customs)) {
	
	 $qf[] = ' #__virtuemart_customs as c '; 
	 $qf[] = ' #__virtuemart_product_customfields as cf '; 
	}
	

	
	$w = ' where '; 
	
	$w .= ' p.`published` = 1 and '; 
	$w .= ' p.`product_discontinued` < 1 and '; 
	if (empty($custom_parent_where)) {
	 $w .= ' p.product_parent_id = 0 and  ';
	}
	else {
		$w .= $custom_parent_where; 
	}	
	
	if (!empty($categories)) {
	$gc = array(); 
	foreach ($categories as $cat_id) {
		$cat_id = (int)$cat_id; 
		if (empty($cat_id)) continue; 
		$gc[] = ' ( cat.virtuemart_category_id = '.(int)$cat_id.') ';
	}
	
	$wc[] = ' ('.implode(' OR ', $gc).') and ((cat.virtuemart_product_id = p.product_parent_id) or (cat.virtuemart_product_id = p.virtuemart_product_id)) '; 
	}
	
	if (!empty($customs)) {
	 $qcu = ''; 
	 //if (empty($canshowchilds)) 
	 {
	  $qcu .= ' ('; 
	 }
	 $qcu .= ' (cf.virtuemart_product_id = p.virtuemart_product_id) '; 
	 
	 //if (empty($canshowchilds)) 
	 {
	 
	 $qcu .= ' or (cf.virtuemart_product_id = p.product_parent_id)) '; 
	 
	 }
	 $qcu .= ' and (cf.virtuemart_custom_id = c.virtuemart_custom_id) '; 
	 $gg = array(); 
	 
	
	  //var_dump($custom_groups); die(); 
	 foreach ($custom_groups as $local_group) {
		 $gg2 = array(); 
		 foreach ($local_group as $cid) {
		 $gg2[] = ' (cf.virtuemart_custom_id = '.(int)$cid.') '; 
		 }
		 $gg[] = ' ( '.implode(' or ', $gg2).' ) '; 
	     //$qcu .= ' c.virtuemart_custom_id IN ('.implode(',', $customs).') )' ; 
	 }
	 $gcu .= ' ( '.implode(' and ', $gg).' ) '; 
	 
	 
	 if (!empty($gg)) {
	   $wc[] = $qcu.' and '.$gcu;
	 }	 
	}
	
	//$wc[] = ' (parents.virtuemart_product_id = p.product_parent_id ) '; 
	
	/*
	if ($canshowchilds) {
	  $wc[] = ' ( p.product_parent_id > 0 ) '; 
	}
	else {
		$wc[] = ' ( p.product_parent_id = 0 ) '; 
	}
	*/
	
	if (empty($wc)) {
		$wc[] = ' 1=1 '; 
	}
	
	 
	$mylimit = (int)$prods + 1; 
	
	$q = $s.implode(',', $qf).$w.implode(' and ',$wc); 
	//$q .= ' limit '.$limitstart.','.$prods; 
	$q .= ' limit 0,'.$mylimit; 
	
	
	
	$db = JFactory::getDBO(); 
	if (class_exists('RupHelper')) {
		$res = RupHelper::runQuery($db, $q); 
	}
	else {
		
	 $db->setQuery($q); 
	 $res = $db->loadAssocList(); 
	 
	 
	 
	 
	
	}
	
	
	
	
	
	
	    $ids = array(); 
		if (!empty($res)) {
		
		//var_dump($res); die(); 
		
		if (!empty($custom_groups)) {
		 //self::filterRes($res, $custom_groups, $canshowchilds, $limitstart, $prods); 
		 $mylimit2 = $prods+1; 
		 self::filterRes($res, $customs, $custom_groups, $canshowchilds, $limitstart, $mylimit2); 
		}
		
		if (!$canshowchilds) {
			foreach ($res as $k=>$v) {
				if (empty($v['product_parent_id'])) {
				 $v['virtuemart_product_id'] = (int)$v['virtuemart_product_id']; 
				 $ids[$v['virtuemart_product_id']] = $v['virtuemart_product_id']; 
				}
				else {
					$pid = (int)$v['product_parent_id']; 
					$ids[$pid] = $pid; 
				}
			}
		}
		else {
		foreach ($res as $k=>$v) {
			$v['virtuemart_product_id'] = (int)$v['virtuemart_product_id']; 
			$ids[$v['virtuemart_product_id']] = $v['virtuemart_product_id']; 
		}
		}
		}
		
		
		
		
		return $ids; 
		
	}
	
	
	public static function filterRes(&$res, $selectedCustoms, $custom_groups, $canshowchilds, $limitstart=0, $prods) {
		return; 
		$products = array(); 
		$parents = array(); 
		foreach ($res as $row) {
			$virtuemart_custom_id = (int)$row['cid']; 
			
			$product_parent_id = (int)$row['product_parent_id']; 
			
			$virtuemart_product_id = (int)$row['virtuemart_product_id']; 
			if (empty($product_parent_id)) $product_parent_id = $virtuemart_product_id;
			if (!isset($products[$virtuemart_product_id])) $products[$virtuemart_product_id] = array(); 
			$products[$virtuemart_product_id][$virtuemart_custom_id] = $virtuemart_custom_id;
			if (!isset($parents[$product_parent_id])) $parents[$product_parent_id] = array();
			$parents[$product_parent_id][$virtuemart_product_id] = $virtuemart_custom_id;
		}
		
		
		
		
		
		foreach ($parents as $parent_id =>$data) {
			foreach ($data as $product_id => $custom_id) {
				if (!isset($products[$virtuemart_product_id])) $products[$virtuemart_product_id] = array(); 
				$products[$virtuemart_product_id][$custom_id] = $custom_id;
			}
		}
		
		
		
		foreach ($products as $product_id=>$data) {
			
			//unset parents:
			if ($canshowchilds) {
				if (isset($parents[$product_id])) {
					unset($products[$product_id]); 
					continue; 
				}
			}
			else {
			 //unset childs:
			 	    if (!isset($parents[$product_id])) {
					unset($products[$product_id]); 
					continue; 
				}

			}
			
			if ($canshowchilds) {
				//selectedCustoms
				
			
				
				foreach ($selectedCustoms as $id=>$selected_id) {
			
			
				if (isset($data[$selected_id])) {
					
					break; 
				}
			    else 
			    {
				 unset($products[$product_id]); 
				
			    }
			
		}
				
			}
			else {
		foreach ($custom_groups as $local_group) {
			$found = false; 
			
			foreach ($local_group as $cid) {
				if (isset($data[$cid])) {
					$found = true; 
					break; 
				}
			}
			if (!$found) {
				unset($products[$product_id]); 
				continue 2; 
			}
			
		}
			}
		}
		
		$ret = array(); 
		$n=0; 
		foreach ($products as $product_id=>$data) {
			if ($limitstart > $n) {
				$n++; 
				continue; 
			}
			$r = array(); 
			$virtuemart_product_id = $product_id; 
			$r['virtuemart_product_id'] = $virtuemart_product_id; 
			$ret[$virtuemart_product_id] = $r; 
			if (count($ret) === $prods) break; 
			$n++; 
		}
		$res = $ret; 
		
		
		
	}
	public static function getGet() {
		
		if (isset(self::$getget)) return self::$getget;
		
		$app = JFactory::getApplication(); 
$get = array(); 


$get['option'] = 'com_rupsearch'; 
$get['view'] = 'search'; 

$get['keyword'] = JRequest::getVar('keyword', ''); 
$get['virtuemart_category_id'] = self::getCatsFromGet(false); 

if (empty($get['virtuemart_category_id'])) {
	$get['virtuemart_category_id'] = self::getCatsFromGet(true);
}

$get['virtuemart_manufacturer_id'] = JRequest::getVar('virtuemart_manufacturer_id', array()); 

$c = JRequest::getVar('virtuemart_custom_id', array()); 

$get['virtuemart_custom_id'] = self::escapeIDS($c); 

$c = JRequest::getVar('qf', array()); 
$get['qf'] = self::escapeIDS($c); 

//var_dump($_POST); 



require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
/*
$ret = array(); 
if (is_array($get['virtuemart_custom_id'])) {
foreach ($get['virtuemart_custom_id'] as $val) {
	//$ra = OPCmini::parseCommas($val, true, '_'); 
	if (stripos($val, '_') !== false) {
		$aw = explode('_', $val); 
		foreach ($aw as $valx) {
			$valx = (int)$valx; 
			if (!empty($valx)) {
				$ret[$valx] = $valx; 
			}
		}
	}
	else {
	   $val = (int)$val; 
	   $ret[$val] = $val; 
	}
	
	
	
}
}
else {
	$val = $get['virtuemart_custom_id'];
	if (stripos($val, '_') !== false) {
		$aw = explode('_', $val); 
		foreach ($aw as $valx) {
			$valx = (int)$valx; 
			if (!empty($valx)) {
				$ret[$valx] = $valx; 
			}
		}
	}
	else {
	   $val = (int)$val; 
	   $ret[$val] = $val; 
	}
	
	

}
$get['group_virtuemart_custom_id'] = implode('_', $ret); 
*/


$l = JRequest::getInt('limit'); 

 
$get['limit'] = (int)$app->getUserStateFromRequest('com_virtuemart.category.limit', 'limit', JRequest::getInt('limit', VmConfig::get ('llimit_init_FE',240)));
		
		
$get['limitstart'] = (int)$app->getUserStateFromRequest('com_virtuemart.category.limitstart', 'limitstart',  JRequest::getInt('limitstart',0));

$get['prod'] = (int)$get['limit']; 
$get['internal_caching'] = 0; 



$Itemid = JRequest::getInt('Itemid', 0); 
if (!empty($Itemid)) $get['Itemid'] = $Itemid; 

$lang = JRequest::getWord('lang', ''); 
if (!empty($lang)) $get['lang'] = $lang; 


self::$getget = $get; 


if ((!empty($get['virtuemart_category_id'])) || (!empty($get['virtuemart_custom_id'])) || (!empty($get['virtuemart_manufacturer_id']))) {
	$session = JFactory::getSession(); 
	$primary_virtuemart_category_id = JRequest::getInt('primary_virtuemart_category_id', 0); 
	$session->set('filter_'.$primary_virtuemart_category_id, json_encode($get)); 
	
	
	
	
}
else {
		$session = JFactory::getSession(); 
		$get_empty = JRequest::getVar('empty', 0); 
		$primary_virtuemart_category_id = JRequest::getInt('primary_virtuemart_category_id', 0); 
		if (!empty($get_empty)) {
		   $session->clear('filter_'.$primary_virtuemart_category_id); 
		}
		else {
	
		$ret = self::getStoredGet($primary_virtuemart_category_id); 
		if (!empty($ret)) return $ret; 
		
		
																			  
							 
										   
   
					  
 
						  
				 
	
   
   
		}
}
return $get; 
	}
	public static function getListChilds(&$categories, &$other=array(), $listCurrentLevel=false) {
		
		$db = JFactory::getDBO(); 
		
		if (!empty($other)) $selected = $other; 
		else $selected = $categories; 
		
		if (empty($selected)) return; 
		
		$q = 'select cc.category_child_id from #__virtuemart_category_categories as cc,#__virtuemart_categories as c right join `#__virtuemart_categories_'.$db->escape(VmConfig::$vmlang).'` as l on l.virtuemart_category_id = c.virtuemart_category_id  where cc.category_parent_id IN ('.implode(',', $selected).') and c.virtuemart_category_id = cc.category_child_id and c.published = 1 order by cc.`ordering`, c.`ordering`, l.`category_name`'; 
		static $cache; 
		if (isset($cache[$q])) {
			$res = $cache[$q]; 
		}
		else {
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$cache[$q] = $res; 
		}
		
		
		
		//if we have no child categories, display the current level categories: 
		if ($listCurrentLevel)
		if (empty($res)) {
			$first = reset($selected); 
			$first = (int)$first; 
			if (!empty($first)) {
			$q = 'select cc.category_parent_id id from #__virtuemart_category_categories as cc where cc.category_child_id = '.(int)$first.' limit 1';
			$db->setQuery($q); 
			$parent_id = $db->loadResult(); 
			if (!empty($parent_id)) {
			$q = 'select `cc`.`category_child_id` from `#__virtuemart_category_categories` as `cc`,`#__virtuemart_categories` as `c` right join `#__virtuemart_categories_'.$db->escape(VmConfig::$vmlang).'` as l on l.virtuemart_category_id = c.virtuemart_category_id where `cc`.`category_parent_id` = '.(int)$parent_id.' and `c`.`virtuemart_category_id` = `cc`.`category_child_id` and `c`.`published` = 1 order by cc.`ordering`, c.`ordering`, l.`category_name`'; 
		
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
			}
			}
		}
		
		if (!empty($res)) {
			$new = array(); 
			foreach ($res as $row) {
				$i = (int)$row['category_child_id']; 
				if (!in_array($i, $selected)) {
				 $new[$i] = $i; 
				}
				$categories[$i] = $i; 
			}
			
			
			
			//return self::getListChilds($categories, $new); 
		}
		
		
	}
	public static function makeUnique(&$categories) {
		$new = array(); 
		foreach ($categories as $cat) {
			$cat = (int)$cat; 
			if (!empty($cat)) {
			  $new[$cat] = $cat; 
			}
		}
		$categories = $new; 
	}
	
	public static function loadVM() {
		static $run; 
		if (!empty($run)) return; 
		$run = true; 
		
		if (!class_exists('VmConfig'))
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
			VmConfig::loadConfig(); 
			
			$tag = JFactory::getLanguage()->getTag(); 
			if (class_exists('vmLanguage')) {
				if (method_exists('vmLanguage', 'setLanguageByTag')) {
					vmLanguage::setLanguageByTag($tag); 
					
				}
			}
	}
	
	public static function getLink($get, $local) {
		$link = 'index.php?option=com_rupsearch&view=search'; 
		$app = JFactory::getApplication(); 
		self::loadVM(); 
		
		
		
		//$link .= '&limit='.$limit.'&limitstart='.$limitStart; 
		
		foreach ($get as $r => $v) {
		  	if (!is_array($v)) {
			  $link .= '&'.urlencode($r).'='.urlencode($v);
			}	
			else {
				
				foreach ($v as $i=>$val) {
					$vx = (int)$vx; 
					if (empty($vx)) continue; 
					$link .= '&'.urlencode($r.'['.$vx.']').'='.urlencode($vx);
					
				}
			}
			
		}
		
		foreach ($local as $r => $v) {
		  	if (!is_array($v)) {
			  $link .= '&'.urlencode($r).'='.urlencode($v);
			}	
			else {
				
				foreach ($v as $i=>$val) {
					$vx = (int)$vx; 
					if (empty($vx)) continue; 
					$link .= '&'.urlencode($r.'['.$vx.']').'='.urlencode($$vx);
					
				}
			}
			
		}
		
		
		return $link; 
		
	}
	
	public static function getCustomsCategories($categories, $inclchild=true, &$group_names=array()) {
		
		$params = self::getParams(); 
		
		$use_group_names = $params->get('use_group_names', false); 
		
		$datas = array(); 
		if (!empty($categories)) {
	
	self::makeUnique($categories); 
	
	self::removeParentCats($categories); 
	//find child categories: 
	if ($inclchild)
	self::getListChilds($categories); 
	
	$db = JFactory::getDBO(); 
	/*
	static $n; 
	if (empty($n)) $n = 0; 
	$n++; 
	$x = debug_backtrace(); 
	
	foreach ($x as $l) echo $l['file'].' '.$l['line'].'<br />'; 
	
	if ($n >= 2) {
		die(); 
	}
	*/
	
	$q = 'select  '; 
	$q .= ' distinct '; 
	$q.= ' `c`.custom_title, `c`.custom_tip, `c`.custom_desc, `c`.custom_value, `c`.virtuemart_custom_id';
	$q .= ', `c`.custom_parent_id '; 
	
	//$q .= ', count(`cat`.virtuemart_product_id) '; 
	
	if ($use_group_names) {
	  $q .= ', IFNULL(`ce`.`group_value`, \'\') as `group_name` '; 
	}
	//$q .= ', sum(p.published) as mycount '; 
	$q .= ' from #__virtuemart_product_categories as `cat`, #__virtuemart_products as `p`, #__virtuemart_customs as `c`, #__virtuemart_product_customfields as `cf` '; 
	if ($use_group_names) {
	 $q .= ' left outer join #__virtuemart_customs_ext as `ce` on `ce`.virtuemart_custom_id = `cf`.virtuemart_custom_id '; 
	}
	
	
	
	$q .= ' where '; 
	$q .= ' `cat`.virtuemart_category_id IN ('.implode(',', $categories).') '; 
	$q .= '	and ( '; 
	
	//we dropped support for child products since using disabler=1
	if (!empty($canshowchild)) {
	$q .= ' (`cat`.virtuemart_product_id = `p`.product_parent_id) or '; 
	}
	$q .= '	(`cat`.virtuemart_product_id = `p`.virtuemart_product_id)) and ((`cf`.virtuemart_product_id = `p`.virtuemart_product_id) '; 
	if (!empty($canshowchild)) {
	$q .= ' or (`cf`.virtuemart_product_id = `p`.product_parent_id) '; 
	}
	$q .= ' ) and `cf`.virtuemart_custom_id = `c`.virtuemart_custom_id  '; 
	
	//$q .= ' and `p`.published = 1  
	//$q .= ' and `c`.layout_pos = "filter" '; 
	
	if ($use_group_names) {
	 $q .= ' and (ISNULL(`ce`.group_type) OR `ce`.group_type = 1)'; 
	}
	$q .= ' and `p`.published = 1 and `p`.product_parent_id = 0 '; 
	//echo $q; die(); 
	$filtercf = $params->get('filter', ''); 
	$toIgnore = array(); 
	if (!empty($filtercf)) {
		if (strpos($filtercf, ',') !== false) {
		  $ea = explode(',', $filtercf); 
		
		}
		else {
			$ea = array((int)$filtercf); 
		}
		foreach ($ea as $ignore_id) {
			$ignore_id = (int)$ignore_id; 
			if (!empty($ignore_id)) {
				$toIgnore[] = $ignore_id;
			}
		}
	}
	if (!empty($toIgnore)) {
		$q .= 'and (`c`.virtuemart_custom_id NOT IN ('.implode(',', $toIgnore).') and `c`.custom_parent_id NOT IN ('.implode(',', $toIgnore).'))'; 
	}
	
	
	static $cache; 
	if (empty($cache)) $cache = array(); 
	if (isset($cache[$q])) $res = $cache[$q]; 
	else {
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$res = $db->loadAssocList(); 
	
	$cache[$q] = (array)$res; 
	
	
	
	$debug = $params->get('debug', false); 
	if ($debug) {
	$q = str_replace('#__', $db->getPrefix(), $q); 
	$script = "\n".'console.log(\'('.count($res).')\', '.json_encode($q).');'."\n"; 
	JFactory::getDocument()->addScriptDeclaration($script); 
	
	}
	
	}
	
	
	
	$isAdmin = self::checkPerm(); 
	
	
	$new = array(); 
	foreach ($res as $row) {
		$row['custom_parent_id'] = (int)$row['custom_parent_id']; 
		$row['virtuemart_custom_id']  =  (int)$row['virtuemart_custom_id']; 
		$cid = (int)$row['virtuemart_custom_id']; 
		
		$new[$cid] = $row; 
	}
	$res = null; 
	$res = $new; 
	
	$toUnset = array(); 
	foreach ($res as $ind => $row) {
		$virtuemart_custom_id = $row['virtuemart_custom_id']; 
		foreach ($res as $row2) {
			$custom_parent_id = $row2['custom_parent_id']; 
			if ($custom_parent_id === $virtuemart_custom_id) {
				$toUnset[$ind] = $ind; //$row['custom_title']; 
			}
		}
	}
	
	
	foreach ($res as $row) {
		
		
		
		$row['virtuemart_custom_id'] = (int)$row['virtuemart_custom_id'];
		$title = 'UNKNOWN'; 
		//$title = $row['custom_value']; 
		$custom_parent_id = (int)$row['custom_parent_id']; 
		if (isset($res[$custom_parent_id])) {
			continue; 
		}
		else {
			
		if (!empty($row['custom_parent_id'])) {
			$find_titles[$row['custom_parent_id']] = $row['custom_parent_id']; 
			
		}
		}
		
	}
	
	$titles = array(); 
	if (!empty($find_titles)) {
		$q = 'select `c`.`custom_value`, `custom_parent_id`, `virtuemart_custom_id` from #__virtuemart_customs as `c` where `c`.virtuemart_custom_id IN ( '.implode(',', $find_titles).')'; 
		$db->setQuery($q); 
		$restitles = $db->loadAssocList(); 
		foreach ($restitles as $r) {
			$id = (int)$r['virtuemart_custom_id']; 
			$titles[$id] = $r['custom_value']; 
		}
		
	}
	
	foreach ($res as $row) {
		
		
		
		$row['virtuemart_custom_id'] = (int)$row['virtuemart_custom_id'];
		$title = 'UNKNOWN'; 
		//$title = $row['custom_value']; 
		$custom_parent_id = (int)$row['custom_parent_id']; 
		if (isset($res[$custom_parent_id])) {
			$title = $res[$custom_parent_id]['custom_value']; 
		}
		else {
			
		if (!empty($row['custom_parent_id'])) {
			/*
			$q = 'select c.custom_value from #__virtuemart_customs as c where c.virtuemart_custom_id = '.(int)$row['custom_parent_id']; 
			$db->setQuery($q); 
			$title = $db->loadResult(); 
			*/
			if (isset($titles[$row['custom_parent_id']])) {
			 $title = $titles[$row['custom_parent_id']]; 
			}
			/*
			if ($title !== $titles[$row['custom_parent_id']]) {
				echo $q; die(); 
			}
			*/
			
		}
		}
		
		if (!isset($group_names[$title])) $group_names[$title] = array(); 
		
		if (empty($datas[$title])) $datas[$title] = array(); 
		if (!empty($row['custom_value'])) $custom_title = $row['custom_value']; //.' ('.$row['mycount'].')'; 
		else
		if (!empty($row['custom_title'])) $custom_title = $row['custom_title']; //.' ('.$row['mycount'].')'; 
		
		
		
		$has_group_name = false; 
		$obj = new stdClass(); 
		if ($isAdmin) $obj->group_names = array(); 
		
		if ($use_group_names) {
		if (!empty($row['group_name'])) {
		if (strpos($row['group_name'], ';')!==false) {
			$ea = explode(';', $row['group_name']); 
			foreach ($ea as $group_name) {
			   if (empty($group_names[$title][$group_name])) $group_names[$title][$group_name] = array(); 
			   $group_names[$title][$group_name][$row['virtuemart_custom_id']] = $row['virtuemart_custom_id']; 
			   $has_group_name = true; 
			   
			   
			   if ($isAdmin) $obj->group_names[] = $group_name;
			}
		}
		else {
			if (empty($group_names[$title][$row['group_name']])) $group_names[$title][$row['group_name']] = array(); 
			$group_names[$title][$row['group_name']][$row['virtuemart_custom_id']] = $row['virtuemart_custom_id']; 
			$has_group_name = true; 
			
			if ($isAdmin) $obj->group_names[] = $row['group_name'];
		}
		
		
		}
		else {
			if (empty($group_names[$title][$custom_title])) $group_names[$title][$custom_title] = array(); 
			$group_names[$title][$custom_title][$row['virtuemart_custom_id']] = $row['virtuemart_custom_id']; 
			$has_group_name = false; 
		}
		}
		
		
		$obj->custom_title = $custom_title; 
		$obj->has_group_name = $has_group_name; 
		
		foreach ($row as $k=>$v) {
			if (!isset($obj->$k)) $obj->$k = $v; 
		}
		if (!isset($toUnset[$row['virtuemart_custom_id']])) {
		 $datas[$title][$row['virtuemart_custom_id']] = $obj; 
		}
		
	}
	
	if (!empty($group_names)) {
	$size_tabs = $params->get('size_tabs', ''); 
	
	$eax = explode(',', $size_tabs); 
	if (!is_array($eax)) $eax = array($eax); 
	if (!empty($eax)) {
	foreach ($eax as $ind=>$text) {
		$eax[$ind] = JText::_($text); 
	}
	}
	
	
	foreach ($group_names as $title => $group) {
		
		$toSort = array(); 
		foreach ($group as $group_name => $ids) {
			$obj = new stdClass(); 
			$obj->custom_title = $group_name; 
			$obj->ids = $ids; 
			$toSort[$group_name] = $obj; 
		}
		if (((!empty($eax)) && (in_array($title, $eax))) && (!empty($size_tabs))) {
		 $newOrder = self::reOrderCustoms($title, $toSort); 
		 $group_names[$title] = $newOrder; 
		}
		else {
			$group_names[$title] = $toSort; 
		}
	}
	
	
	}
	
	//var_dump($group_names); die(); 
	
	//echo $q; 
	//debug_zval_dump($group_names); die(); 
	
	
	
	$ignore = array('UNKNOWN', 'NS '); 
	foreach ($datas as $title=>$v) {
		foreach ($ignore as $se) {
		 if (strpos($title, $se)===0) unset($datas[$title]); 
		}
	}
	}
	
	$size_tabs = $params->get('size_tabs', ''); 
	
	
	if (!empty($eax)) {
	foreach ($eax as $ind=>$text) {
		$eax[$ind] = JText::_($text); 
	}
	foreach ($datas as $title=>$v) {
		if (in_array($title, $eax))  
		{
		 $datas[$title] = self::reOrderCustoms($title, $v); 
		}
	}
	}
	
	//to calculate lengths:
	
	
	//if (empty($group_names)) 
	{
	
	$customs = self::getCustomsFromGet(); 
	$allfields = 0; 
	foreach ($datas as $title => $data) {
		foreach ($data as $cid => $titleX) {
			$allfields++;
		}
	}
	
	$calculate_numbers = false; 
	if (!empty($calculate_numbers)) {
	static $recursion; 
	if (empty($recursion)) { 
	foreach ($datas as $title => $data) {
		foreach ($data as $cid => $obj) {
			if (!empty($obj->has_group_name)) continue; 
			//get quantities: 
			$test = $customs;
			
			if (isset($customs[$cid])) continue; 
			
			$test[$cid] = $cid; 
			
			
			$c = ''; 
			//calculate number of available products:
			//if ($allfields < 20) 
			{
			
			$recursion = true; 
			$res = self::getCategoryProducts('', 0, false, '', array(), array(), $test);
			
			}
			
			if (empty($res)) {
				$datas[$title][$cid]->isempty = true; 
				$c = 0; 
				//unset($datas[$title][$cid]); 
				
			}
			else {
				$max = 16; 
				$res = self::getCategoryProducts('', $max, false, '', array(), array(), $test);
				$c = count($res); 
			}
			
			
			$mytitle = $obj->custom_title;
			if ($c > $max) {
				$c = $c.'+';
			}
			$mytitle .= ' ('.$c.')';
			$datas[$title][$cid]->custom_title = $mytitle; 
					
			
		}
	}
	}
	}
	
	 $recursion = false; 
	}
	
	
	
	return $datas; 
	}
	
	public static function getProductsCategoriesFromGet(&$categories) {
		if (empty($categories)) $categories = array(); 
		
		$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0); 
	if (!empty($virtuemart_product_id)) {
		$db = JFactory::getDBO(); 
	$q = 'select p.virtuemart_category_id from #__virtuemart_product_categories as p, #__virtuemart_categories as c, #__virtuemart_products as px where (( p.virtuemart_product_id = '.(int)$virtuemart_product_id.' and px.virtuemart_product_id = p.virtuemart_product_id) or (px.virtuemart_product_id = '.(int)$virtuemart_product_id.' and px.product_parent_id = p.virtuemart_product_id)) and (c.virtuemart_category_id = p.virtuemart_category_id) and c.published = 1'; 
	$db->setQuery($q); 
	$prodcats = $db->loadAssocList(); 
	if (!empty($prodcats))
	foreach ($prodcats as $row) {
		$cat_id = (int)$row['virtuemart_category_id']; 
		$categories[$cat_id] = $cat_id; 
	}
	}
	}
	
	public static function removeParentCats(&$categories) {

		$cx = count($categories); 
		if ($cx > 1) {
		$db = JFactory::getDBO(); 
		$q = 'select cc.category_parent_id from #__virtuemart_category_categories as cc where cc.category_parent_id IN ('.implode(',', $categories).') and cc.category_child_id IN ('.implode(',', $categories).')'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (!empty($res)) {
			foreach ($res as $row) {
				foreach ($categories as $k=>$cat_id) {
					$c1 = (int)$row['category_parent_id']; 
					$c2 = (int)$cat_id; 
					if ($c1 === $c2) {
						unset($categories[$k]); 
					}
				}
			}
		}
		}
		

	}
	
public static function getStoredGet($primary_virtuemart_category_id) {
		
		$session = JFactory::getSession(); 
		$get_empty = JRequest::getVar('empty', 0); 
		if (empty($get_empty)) {
		$stored_json = $session->get('filter_'.$primary_virtuemart_category_id, '');
		if (!empty($stored_json)) {
			$ret = json_decode($stored_json, true); 
			
			if (!empty($ret)) {
				
				self::$getget = $ret; 
				return $ret; 
			}
			
		}
		}
		return array(); 
	}
	
	public static function getCatsFromGet($withProd=true, $current_category=0) {

	if (isset(self::$getdatacats)) return self::$getdatacats;
	
		$primary_virtuemart_category_id = JRequest::getVar('primary_virtuemart_category_id', 0); 																				  
	if (empty($current_category)) {
		$category = JRequest::getVar('virtuemart_category_id'); 
	}
	else {
		$category = $current_category; 
	}
 if (empty($category)) {
		$get = self::getStoredGet($primary_virtuemart_category_id); 
		if (!empty($get)) {
			if (!empty($get['virtuemart_category_id'])) {
				$category = $get['virtuemart_category_id'];
			}
		}
	}
$categories = array(); 
if (!empty($category)) {
	if (is_array($category)) foreach ($category as $c) $categories[(int)$c] = (int)$c; 
	else {
		$category = (int)$category; 
		$categories[$category] = $category; 
	}
}



if ($withProd) {
$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0); 
if (!empty($virtuemart_product_id)) {
	self::getProductsCategoriesFromGet($categories); 
}
	
}

self::$getdatacats = $categories; 
return $categories; 
	}
	public static function escapeIDS(&$getArray) {
		$copy = $getArray; 
		$ret = array(); 
		foreach ($copy as $ind=>$val) {
			if (stripos($val, '_') !== false) {
		$aw = explode('_', $val); 
		$tr = array(); 
		foreach ($aw as $valx) {
			$valx = (int)$valx; 
			if (!empty($valx)) {
				$tr[$valx] = $valx; 
			}
		}
		asort($tr); 
		$ret[$ind] = implode('_', $tr); 
		
		
		
	}
	else {
	   $val = (int)$val; 
	   if (!empty($val)) {
	     $ret[$ind] = $val; 
	   }
	}
		}
		
		
		return $ret; 
		
	}
	public static function getCustomsFromGet() {
		if (!empty(self::$getcustoms)) return self::$getcustoms; 
			
$category = JRequest::getVar('virtuemart_custom_id'); 
$categories = array(); 
if (!empty($category)) {
	if (is_array($category)) {
		
		foreach ($category as $c) {
			if (strpos($c, '_')!==false) {
				$cx = explode('_', $c); 
				foreach ($cx as $cii) {
					$cii = (int)$cii; 
					if (!empty($cii)) {
						$categories[$cii] = $cii; 
					}
				}
			}
			else {
			 $categories[(int)$c] = (int)$c; 
			}
		}
	}
	else {
		
		if (strpos($category, '_')!==false) {
				$cx = explode('_', $category); 
				foreach ($cx as $cii) {
					$cii = (int)$cii; 
					if (!empty($cii)) {
						$categories[$cii] = $cii; 
					}
				}
			}
			else {
				$category = (int)$category; 
				$categories[$category] = $category; 
			}
	}
}
return $categories; 
	}
	
	public static function reOrderCustoms($title, $customs) {
		$params = self::getParams(); 
		$size_order = $params->get('size_order', ''); 
		$size_order = explode(',', $size_order); 
		if (empty($size_order)) $size_order = array(); 
		
		$only_numeric = array(); 
		$size = array(); 
		$textual = array(); 
		
		foreach ($customs as $id=>$custom) {
			
			$custom_title = trim($custom->custom_title);
			if (in_array($custom_title, $size_order)) {
			   $size[$id] = $custom_title; 
			}
			else
			if ((ctype_digit($custom_title)) || (is_int($custom_title))) {
				$only_numeric[$id] = $custom_title; 
			}
			else {
				$textual[$id] = $custom_title; 
			}
		}
		
		 
		$reordered_sizes = self::sortTree($size, $size_order); 
		$reordered_textual = self::sortTree($textual, $size_order); 
		$reordered_num = self::sortTree($only_numeric, $size_order); 
		
		
		
		
		$ret = array(); 
		
		$delim = new stdClass();  
		$delim->is_delim = true;
		$delim->has_group_name = false; 
		$delim->custom_title = '-'; 
		$delim->tab_title = $title; 
		
		$printed = false; 
		$printed2 = false; 
		
		if (!empty($reordered_num)) {
		foreach ($reordered_num as $id=>$val) {
			$ret[$id] = $customs[$id]; 
		}
			$printed = true; 
		}
		if (!empty($reordered_sizes)) {
			if ($printed) {
				$ret['delim1'] = $delim; 
				
			}
			
			
			foreach ($reordered_sizes as $id=>$val) {
				$ret[$id] = $customs[$id]; 
			}
			$printed2 = true; 
		}
		if (!empty($reordered_textual)) {
			if (($printed2) || ($printed)) {
				$ret['delim2'] = $delim; 
			}
		foreach ($reordered_textual as $id=>$val) {
			$ret[$id] = $customs[$id]; 
		}
		}
		
				
		
		

		
		return $ret; 
		
	}
	
	
	public static function sortTree(&$attributes, $size_order) {
		
		$copy = $attributes; 
		
		
		$c = 0; 
		$cn = 0; 
		foreach ($copy as $val) {
			if (in_array($val, $size_order)) {
				$c++; 
			}
			if ((ctype_digit($val)) || (is_int($val))) {
				$cn++; 
			}
		}
		
		if ($cn === count($copy)) {
			asort($copy); 
			$attributes = $copy; 
			return $copy; 
		}
		
		if ($c > 1) {
			//usort($copy, array('adj', "sort_sizes"));
			self::sort_sizes($copy, $size_order); 
			
		}
		else {
		  usort($copy, array('PCH', "sort_cats"));
		}
		
		$ret = array(); 
		//for ($i=0; $i<count($copy); $i++)
		foreach ($copy as $i=>$val3)
		{
		foreach ($attributes as $key=>$val) 
		{
			 $val2 = $copy[$i]; 
			 if ($val2 === $val) {
				 $ret[$key] = $val; 
			 }
		 }
		}
		
		$attributes = $ret; 
		return $ret; 
		//$attributes = $copy; 
	}
	
	public static function sort_cats($a, $b) {
		$tag = JFactory::getLanguage()->getTag(); 
		$tag = str_replace('-', '_', $tag); 
		if (class_exists('Collator')) {
		$c = new Collator($tag);
		$t1 = $c->compare($a, $b); 
		if ($t1 !== 0) return $t1; 
		return 0; 
		}
		return 0; 
			 /*
		$a1 = explode(SEP, $a); 
		$b1 = explode(SEP, $b); 
	
	if (is_array($a1) && (is_array($b1))) {
		if (count($a1)>count($b1)) return 1; 
		if (count($b1)>count($a1)) return -1; 
		$c = new Collator('sk_SK');
		if (count($b1) == count($a1)) {
			for ($i=0; $i<count($b1); $i++) {
			 $t1 = $c->compare($a1[$i], $b1[$i]); 
			 if ($t1 !== 0) return $t1; 
			}
		}
		return 0; 
	}
	
	$c = new Collator('sk_SK');
    return $c->compare($a1, $b1); 
		*/
	}
	
	public static function sort_sizes(&$sizes, $size_order) {
		$copy = $sizes; 
		$ret = array(); 
		
		foreach ($size_order as $val) {
			foreach ($copy as $ind => $current_size) {
				if ($current_size === $val) {
				  $ret[] = $val; 
				  unset($copy[$ind]); 
				  break;
				}
				
			}
		}
		foreach ($copy as $other) {
			$ret[] = $other; 
		}
		$sizes = $ret; 
		
		
		
	}
	
	public static function getManufsFromGet() {
			if (!empty(self::$getmf)) return self::$getmf; 
			
			
$category = JRequest::getVar('virtuemart_custom_id'); 
$categories = array(); 
if (!empty($category)) {
	if (is_array($category)) foreach ($category as $c) $categories[] = (int)$c; 
	else {
		$category = (int)$category; 
		$categories[] = $category; 
	}
}
return $categories; 
	}
	
	
	public static function renderSaleVariants($parent_id, $extra=array()) {
		$db = JFactory::getDBO(); 
		$q = 'select p.virtuemart_product_id from #__virtuemart_products as p inner join #__virtuemart_products_ext as e on ((e._SALE = 1) and (e.virtuemart_product_id = p.virtuemart_product_id)) where p.published = 1 and p.product_discontinued < 1 and p.product_parent_id = '.(int)$parent_id; 
		$db->setQuery($q); 
		$pds = $db->loadAssocList(); 
		
		  $ids = array(); 
		  if (!empty($pds)) {
		  foreach ($pds as $row) {
			  $pid = (int)$row['virtuemart_product_id']; 
			  $ids[$pid] = $pid; 
		  }
		  }
		return self::renderProductIds($ids, $extra); 
	}
	
	public static function renderOwnProducts($cat_id, $extra=array()) {
		$user_id = JFactory::getUser()->get('id'); 
		if (empty($user_id)) return ''; 
		if (empty($cat_id)) return; 
		
		 $db = JFactory::getDBO(); 
	  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_vmuser_shoppergroups where virtuemart_user_id = '.(int)$user_id.' and virtuemart_shoppergroup_id > 11 order by virtuemart_shoppergroup_id desc '; 
	  $db->setQuery($q);  
	  $sgres = $db->loadAssocList(); 
	  
	   $sg = array(); 
	  if (!empty($sgres)) {
		  
		  foreach ($sgres as $row) {
			  $row['virtuemart_shoppergroup_id'] = (int)$row['virtuemart_shoppergroup_id']; 
			  $sg[$row['virtuemart_shoppergroup_id']] = $row['virtuemart_shoppergroup_id']; 
		  }
		  
		}

	  if (!empty($sg)) {
		 
		  if (!empty($cat_id)) {
			  
		  $q = ' select `p`.`virtuemart_product_id` from  #__virtuemart_products as p'; 
		  $q .= ' inner join #__virtuemart_product_categories as c on ((c.virtuemart_category_id = '.(int)$cat_id.') and c.virtuemart_product_id = p.virtuemart_product_id) '; 
		  $q .= ' left join #__virtuemart_product_prices as `psg` on ((p.virtuemart_product_id = `psg`.virtuemart_product_id) and (`psg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).') ) and (`psg`.`price_quantity_start` <= 0))'; 
			
		  $q .= ' left join #__virtuemart_product_shoppergroups as `psg2` on ((p.virtuemart_product_id = `psg2`.virtuemart_product_id) and  `psg2`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg).')) '; 
		  $q .=' where (`p`.`product_parent_id` = 0) and (`psg2`.virtuemart_product_id IS NOT NULL or `psg`.virtuemart_product_id IS NOT NULL )';
		 
		  
		  $db->setQuery($q); 
		  $pds = $db->loadAssocList(); 
		  $ids = array(); 
		  if (!empty($pds)) {
		  foreach ($pds as $row) {
			  $pid = (int)$row['virtuemart_product_id']; 
			  $ids[$pid] = $pid; 
		  }
		  }
		  return self::renderProductIds($ids, $extra); 
		  }
	  }
		  }
		  
		  public static function renderProductIds($ids, $extra=array()) {
			  
			  
if (!empty($ids)) {
	$productModel = VmModel::getModel('Product');
	$products = $productModel->getProducts ($ids);
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
					
					
					
					
					
				}
}

if (!empty($products)) {
$totalProd = 		count( $products);

$currency = CurrencyDisplay::getInstance( );
vmJsApi::jPrice();

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 


$params = array('products'=>$products,'products_per_row'=>5,'showRating'=>true, 'headorder'=> array(0=>'_SALE')); 
foreach ($extra as $k=>$v) {
	$params[$k] = $v; 
}

echo shopFunctionsF::renderVmSubLayout('products',$params);
}
		  
			 
	}
	
	public static function collectCustomsFromGet(&$group_names=array()) {
	
$categories = self::getCatsFromGet(); 

$ret = self::getCustomsCategories($categories, true, $group_names); 
foreach ($ret as $title=>$val) {
	asort($ret[$title]); 
}
return $ret; 


}
}