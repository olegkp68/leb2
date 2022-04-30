<?php 
/**
 * @package		RuposTel Ajax search pro
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

class RupHelper {
	
	public static $cache; 
	public static $total_count; 
	public static function getIncludes()
	{
	  if (!class_exists('VmConfig'))	  
		{
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		}
		VmConfig::loadConfig(); 
		if(!class_exists('shopFunctionsF'))require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php');
		
		if (!class_exists('VmImage'))
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'image.php');
			
	   
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'calculationh.php');
		if (!class_exists('VmConfig'))
		  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 
		 
		  if (method_exists('VmConfig', 'loadJLang'))
		  {
		  VmConfig::loadJLang('com_virtuemart',TRUE);
		  VmConfig::loadJLang('com_virtuemart_orders',TRUE);
		  }
		  
		 if (method_exists('VmConfig', 'loadJLang'))
		 VmConfig::loadJLang('com_virtuemart');
		 else
		  {
		     $lang = JFactory::getLanguage();
			 $extension = 'com_virtuemart';
			 $base_dir = JPATH_SITE;
			 $language_tag = $lang->getTag();
			 $reload = false;
			 $lang->load($extension, $base_dir, $language_tag, $reload);
			 
			 $lang = JFactory::getLanguage();
			 $extension = 'com_virtuemart';
			 $base_dir = JPATH_ADMINISTRATOR;
			 $language_tag = $lang->getTag();
			 $reload = false;
			 $lang->load($extension, $base_dir, $language_tag, $reload);
			 
		  }
		  
	}

	
	public static function addTabs($searchView, $params, &$html, $keyword, $prods, $ids) {
		$tabs = $params->get('search_else', ''); 
		$tabs = strtoupper($tabs); 
		//only product display is enabled, not tabs will be added
		if ((empty($tabs)) || ($tabs === 'PRODUCTS')) return; 
		
		$productHtml = $html; 
		$data = array(); 
		
		$ea = explode(',', $tabs); 
		
		$first = true; 
		
		JFactory::getLanguage()->load('mod_virtuemart_ajax_search_pro'); 
		
		foreach ($ea as $k=>$v) {
			$data[$v] = array(); 
			$data[$v]['tabdesc'] = ''; 
		
			
			$data[$v]['id'] = $k;
			if ($first) {
			  $data[$v]['active'] = true;
			  $first = false; 
			}
			switch ($v) {
				case 'PRODUCTS': 
				 $data[$v]['tabname'] = JText::_('COM_VIRTUEMART_PRODUCTS_PRODUCT').' ('.count($ids).')'; //Text::_('MOD_VIRTUEMART_AJAX_SEARCH_PRO_SEARCH_INTABS_'.$v.'_FE'); //JText::_('COM_VIRTUEMART_'.$v); 
				 $data[$v]['tabcontent'] = $productHtml;
				 break;
				case 'CATEGORIES': 
				 
				 $count = 0; 
				 $data[$v]['tabcontent'] = self::getCategoriesHtml($params, $keyword, $prods, $count); 
				 $data[$v]['tabname'] = JText::_('COM_VIRTUEMART_CATEGORIES').' ('.$count.')';
				 break;
				case 'MANUFACTURERS': 
				 $count = 0; 
				 $data[$v]['tabcontent'] = self::getManufacturerHtml($searchView, $params, $keyword, $prods, $count); 
				 $data[$v]['tabname'] = JText::_('COM_VIRTUEMART_MANUFACTURER_CAT_MANUFACTURERS').' ('.$count.')';
				 break;
				default: 
				 $count = 0; 
				 $data[$v]['tabcontent'] = self::getArticleHtml($searchView, $params, $keyword, $prods, $count); 
				 if ($count === 0) $data[$v]['tabcontent'] = ''; 
				 $data[$v]['tabname'] = JText::_('JGLOBAL_ARTICLES').' ('.$count.')';
				 break;
				 
			}
			
			if (empty($data[$v]['tabcontent'])) {
				unset($data[$v]); 
			}
			if (empty($ids)) {
				unset($data['PRODUCTS']); 
			}
		}
		
		if (count($data) === 1)  {
			$f = reset($data); 
			$html = $f['tabcontent']; 
			return;
		}
		if (!empty($data)) {
		$framework = $params->get('framework', 'default'); 
		
		$searchView->tabdata = $data; 
		$html2 = $searchView->loadTemplate($framework.'includes');
		$html2 .= $searchView->loadTemplate($framework);
		
		$html = $html2;
		}
	}
	public static function getArticleHtml($searchView, $params, $keyword, $prods, &$count=0) 
	{
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_search'.DIRECTORY_SEPARATOR.'controller.php')) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_search'.DIRECTORY_SEPARATOR.'controller.php'); 
			$pa = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_search'; 
			if (class_exists('SearchController')) {
				JFactory::getLanguage()->load('com_search', JPATH_SITE); 
				
				JLoader::register('SearchHelper', JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php');
				JRequest::setVar('searchword', $keyword); 
				
			  $SearchController = new SearchController(); 
			    if (method_exists($SearchController, 'addViewPath')) { 
				$SearchController->set('_basePath', $pa); 
				$SearchController->set('_setPath', $pa.DIRECTORY_SEPARATOR.'views'); 
				$SearchController->set('model_path', $pa.DIRECTORY_SEPARATOR.'models'); 
				$SearchController->set('_model_path', $pa.DIRECTORY_SEPARATOR.'models'); 
				
				
				//$SearchController->set('state', $state); 
				$SearchController->addViewPath($pa.DIRECTORY_SEPARATOR.'views');
				$SearchController->addModelPath($pa.DIRECTORY_SEPARATOR.'models');
				
				//$m = $SearchController->createModel('search'); 
				
				 require_once($pa.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'search.php'); 
				 $SearchModelSearch = new SearchModelSearch(); 
				}
			  
			  $tp = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_search'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'tmpl'; 
			  
			  $state = new JRegistry(''); 
			  $state->set('keyword', $keyword); 
			  
			  $config = new JRegistry(''); 
			  
			  $view = $SearchController->getView('search', 'html', '', $config);
			  $view->setModel($SearchModelSearch, true); 
			  
			  
			  
			  
			  $view->set('state', $state); 
			  $view->document = new EmptyDocument(); 
			  //$SearchController->getView(); 
			  $view->addTemplatePath($tp); 
			  ob_start(); 
			  $view->display(); 
			  $html = ob_get_clean(); 
			  
			  $count = $SearchModelSearch->getTotal(); 
			  
			  $root = Juri::root(); 
			  if (substr($root, -1) !== '/') $root .= '/'; 
			  
			  $action_url = 'index.php?option=com_rupsearch&view=search&layout=default&nosef=1'; 
			  $clang = RupHelper::loadLangFiles(); 
			  $Iid = RupHelper::getModuleItemid($params->get('my_itemid',0)); 
			  if (!empty($Iid)) {
			   $action_url .= '&Itemid='.$Iid;
			  }
			  
			  if (!empty($clang))
			  $action_url .= '&lang='.$clang; 
			  $action_url = JRoute::_($action_url); 
			  
			  $arr = array('action=', '"com_search"'); 
			  $rep = array('action="'.$action_url.'" no_action=', '"com_rupsearch"'); 
			  
			  $html = str_replace($arr, $rep , $html); 
			   
			  return $html; 
			}
		}			
		return ''; 
	}
	
	public static function getManufacturerHtml(&$searchView, $params, $keyword, $prods, &$count=0) {
		
		$cats = self::getManufacturers($params, $keyword, $prods);
		$count = count($cats); 
		
		if (empty($cats)) return ''; 
		$query = '';
		$mModel = VmModel::getModel('manufacturer'); 
		$mediaModel = VmModel::getModel('Media');
		$langFields = array('mf_name','mf_desc','mf_url','metadesc','metakey', 'mf_email', 'customtitle','slug');
		$query .= 'SELECT c.virtuemart_manufacturer_id, '.implode(', ',VirtueMartModelManufacturer::joinLangSelectFields($langFields));
		$query .= ' FROM #__virtuemart_manufacturers as c '.implode(' ',VirtueMartModelManufacturer::joinLangTables('#__virtuemart_manufacturers','c','virtuemart_manufacturer_id'));
		$query .= ' WHERE c.`virtuemart_manufacturer_id` IN (' .implode(',', $cats).') ';
		$query .= ' AND c.`published` = 1 ';
		
	
	
		$db = JFactory::getDBO();
		$db->setQuery( $query);
		$childList = $db->loadObjectList();
		if(!empty($childList)){
			if(!class_exists('TableManufacturer_medias'))require(VMPATH_ADMIN.DS.'tables'.DS.'manufacturer_medias.php');
			foreach($childList as $child){
				$xrefTable = new TableManufacturer_medias($db);
				$child->virtuemart_media_id = $xrefTable->load($child->virtuemart_manufacturer_id);
				$mModel->addImages($child,1);
			}
			
			$categories_per_row = 4; 
			$vars = array(); 
			//$manufacturers = $mModel->getManufacturers(true, true,  true);
			$vars['manufacturers'] = array(); //$manufacturers; 
			
			if (!class_exists('VirtuemartViewManufacturer')) {
			  require_once(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'manufacturer'.DIRECTORY_SEPARATOR.'view.html.php');
			}
			
			$class = 'VirtuemartViewManufacturer';
			$tp = self::getTemplatePath('manufacturer'); 
			if (file_exists($tp.DIRECTORY_SEPARATOR.'default.php')) {
				ob_start(); 
				$vmView = new vmView(); 
				$vmView->viewname = 'manufacturer'; 
				$vmView->layout = 'default'; 
				$vmView->addTemplatePath($tp); 
				$vmView->manufacturers = $childList;
				$x = $vmView->display(); 
				//include($tp.DIRECTORY_SEPARATOR.'default.php'); 
				$html = ob_get_clean(); 
				
				return $html; 
			}
			return ''; 
		}
		

		
		return ''; 
	    
	}
	
	public static function getCategoriesHtml($params, $keyword, $prods, &$count=0) {
		
		$cats = self::getCategories($params, $keyword, $prods);
		$count = count($cats); 
		
		if (empty($cats)) return ''; 
		$query = '';
		$catModel = VmModel::getModel('category'); 
		$mediaModel = VmModel::getModel('Media');
		$langFields = array('category_name','category_description','metadesc','metakey','customtitle','slug');
		$query .= 'SELECT c.virtuemart_category_id, '.implode(', ',VirtueMartModelCategory::joinLangSelectFields($langFields));
		$query .= ' FROM #__virtuemart_categories as c '.implode(' ',VirtueMartModelCategory::joinLangTables('#__virtuemart_categories','c','virtuemart_category_id'));
		$query .= ' WHERE c.`virtuemart_category_id` IN (' .implode(',', $cats).') ';
		$query .= ' AND c.`published` = 1 ';
		
	
	
		$db = JFactory::getDBO();
		$db->setQuery( $query);
		$childList = $db->loadObjectList();
		if(!empty($childList)){
			if(!class_exists('TableCategory_medias'))require(VMPATH_ADMIN.DS.'tables'.DS.'category_medias.php');
			foreach($childList as $child){
				$xrefTable = new TableCategory_medias($db);
				$child->virtuemart_media_id = $xrefTable->load($child->virtuemart_category_id);
				$catModel->addImages($child,1);
			}
			
			$categories_per_row = 4; 
			
			$html = ShopFunctionsF::renderVmSubLayout('categories',array('categories'=>$childList, 'categories_per_row'=>$categories_per_row));
			return $html; 
		}
		

		
		return ''; 
	    
	}
	public static function getManufacturers($params, $keyword, $prods) {
		
		$db = JFactory::getDBO(); 
		$or = array(); 
		
		$or[] = self::letterWildcardsEscape('mf_name', '=', $keyword);
		$or[] = self::letterWildcardsEscape('mf_name', '=', $keyword.'%');
		$or[] = self::letterWildcardsEscape('mf_name', '=', '%'.$keyword);
		$or[] = self::letterWildcardsEscape('mf_name', 'LIKE', '%'.$keyword.'%');
		
		
		$kX = preg_replace("/([[:alpha:]])([[:digit:]])/", "\\1 \\2", $keyword);
		if ($kX) {
			$keyword2 = $kX; 
		
		}
		$kX = preg_replace("/([[:digit:]])([[:alpha:]])/", "\\1 \\2", $keyword);
		if ($kX) {
		
			$keyword2 = $kX; 
		}
		if ($keyword2 !== $keyword) {
			$or[] = self::letterWildcardsEscape('mf_name', '=', $keyword2);
		    $or[] = self::letterWildcardsEscape('mf_name', '=', $keyword2.'%');
		    $or[] = self::letterWildcardsEscape('mf_name', '=', '%'.$keyword2);
		    $or[] = self::letterWildcardsEscape('mf_name', 'LIKE', '%'.$keyword2.'%');
		}
		$keyword3 = str_replace(' ', '%', $keyword); 
		if (($keyword3 !== $keyword) && ($keyword3 !== $keyword2)) {
			$or[] = self::letterWildcardsEscape('mf_name', '=', $keyword3);
		    $or[] = self::letterWildcardsEscape('mf_name', '=', $keyword3.'%');
		    $or[] = self::letterWildcardsEscape('mf_name', '=', '%'.$keyword3);
		    $or[] = self::letterWildcardsEscape('mf_name', 'LIKE', '%'.$keyword3.'%');
		}
		
		$q = 'select `virtuemart_manufacturer_id` from `#__virtuemart_manufacturers_'.VMLANG.'` where '.implode(' or ', $or).' limit '.(int)$prods;
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (empty($res)) return array(); 

		$ret = array(); 
		foreach ($res as $row) {
			$ret[(int)$row['virtuemart_manufacturer_id']] = (int)$row['virtuemart_manufacturer_id']; 
		}
		return $ret; 
	}
	
	public static function getCategories($params, $keyword, $prods) {
		
		$db = JFactory::getDBO(); 
		$or = array(); 
		
		$or[] = self::letterWildcardsEscape('category_name', '=', $keyword);
		$or[] = self::letterWildcardsEscape('category_name', '=', $keyword.'%');
		$or[] = self::letterWildcardsEscape('category_name', '=', '%'.$keyword);
		$or[] = self::letterWildcardsEscape('category_name', 'LIKE', '%'.$keyword.'%');
		
		
		$kX = preg_replace("/([[:alpha:]])([[:digit:]])/", "\\1 \\2", $keyword);
		if ($kX) {
			$keyword2 = $kX; 
		
		}
		$kX = preg_replace("/([[:digit:]])([[:alpha:]])/", "\\1 \\2", $keyword);
		if ($kX) {
		
			$keyword2 = $kX; 
		}
		if ($keyword2 !== $keyword) {
			$or[] = self::letterWildcardsEscape('category_name', '=', $keyword2);
		    $or[] = self::letterWildcardsEscape('category_name', '=', $keyword2.'%');
		    $or[] = self::letterWildcardsEscape('category_name', '=', '%'.$keyword2);
		    $or[] = self::letterWildcardsEscape('category_name', 'LIKE', '%'.$keyword2.'%');
		}
		$keyword3 = str_replace(' ', '%', $keyword); 
		if (($keyword3 !== $keyword) && ($keyword3 !== $keyword2)) {
			$or[] = self::letterWildcardsEscape('category_name', '=', $keyword3);
		    $or[] = self::letterWildcardsEscape('category_name', '=', $keyword3.'%');
		    $or[] = self::letterWildcardsEscape('category_name', '=', '%'.$keyword3);
		    $or[] = self::letterWildcardsEscape('category_name', 'LIKE', '%'.$keyword3.'%');
		}
		
		$q = 'select `virtuemart_category_id` from `#__virtuemart_categories_'.VMLANG.'` where '.implode(' or ', $or).' limit '.(int)$prods;
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (empty($res)) return array(); 

		$ret = array(); 
		foreach ($res as $row) {
			$ret[(int)$row['virtuemart_category_id']] = (int)$row['virtuemart_category_id']; 
		}
		return $ret; 
	}
	
	public static function updateStats()
	{
		$keyword = JRequest::getVar('searchword', JRequest::getVar('product_keyword', JRequest::getVar('keyword', ''))); 
		$qt = "CREATE TABLE IF NOT EXISTS `#__com_rupsearch_stats` (
  `keyword` varchar(1000) CHARACTER SET utf8 NOT NULL,
  `md5` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `count` bigint(20) NOT NULL,
  `accessstamp` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `md5` (`md5`),
  KEY `count` (`count`),
  KEY `accessstamp` (`accessstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; "; 
	
	
	 $db = JFactory::getDBO(); 
	 
	    $prefix = $db->getPrefix();
		
		$table = '#__com_rupsearch_stats'; 
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
 
   $q = "SHOW TABLES LIKE '".$db->getPrefix().$table."'";
	 $db->setQuery($q);
	  $r = $db->loadResult();
	   if (empty($r)) 
	   {
	     $db->setQuery($qt); 
		 $db->execute(); 
	   }

	 $md5 = md5($keyword); 
	 $q = 'insert DELAYED into #__com_rupsearch_stats (`keyword`, `md5`, `count`, `accessstamp`) values '; 
	  $q .= " ('".$db->escape($keyword)."', '".$md5."', 1, ".time().") "; 
	  $q .= ' on duplicate key update count = count + 1, accessstamp = '.time(); 
	 $db->setQuery($q); 
	 $db->execute();
	 

	// 2 months old will get deleted
	/*
	 $old = time() - (60*60*24*60); 
	 $q = 'delete from #__com_rupsearch_stats where accessstamp < '.$old; 
	 $db->setQuery($q); 
	 $db->execute();
	*/
	
	}
	
	
	public static function loadLangFiles() {
	
 // load virtuemart language files
$jlang = $lang = $langO = JFactory::getLanguage();
$jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
$jlang->load('com_virtuemart', JPATH_SITE, null, true);





$extension = 'com_search';
$base_dir = JPATH_SITE;
$language_tag = $lang->getTag(); 
$lang->load($extension, $base_dir, $language_tag, true);


 
			$clang = JRequest::getVar('lang', ''); 
			$clang = preg_replace('/[^a-zA-Z0-9\-]/', '', $clang);
			
			$locales = $langO->getLocale();
		$tag = $langO->getTag(); 
		$app = JFactory::getApplication(); 		
		
		$found = false; 
		
		if (class_exists('JLanguageHelper') && (method_exists('JLanguageHelper', 'getLanguages')))
		{
		$sefs 		= JLanguageHelper::getLanguages('sef');
		foreach ($sefs as $k=>$v)
		{
			if ($v->lang_code == $tag)
			if (isset($v->sef)) 
			{
				$ret = $v->sef; 

				$clang2 = $ret; 
				if ((!empty($clang))  && ($clang === $clang2)) $found = true; 
				
				
			}
		}
		
		if ((empty($clang)) || (!$found)) $clang = $clang2; 
		
		
		}
		
		
		
			 if ( version_compare( JVERSION, '3.0', '<' ) == 1) {       
			if (isset($locales[6]) && (strlen($locales[6])==2))
			{
				
				$clang = $locales[6]; 
				
			}
			else
			if (!empty($locales[4]))
			{
				$clang = $locales[4]; 
				
				if (stripos($clang, '_')!==false)
				{
					$la = explode('_', $clang); 
					$clang = $la[1]; 
					if (stripos($clang, '.')!==false)
					{
						$la2 = explode('.', $clang); 
						$clang = strtolower($la2[0]); 
					}
				
					
				}
		     	
			}
			
			 }
			 
			 return $clang; 
	
	}
	
	
	
	public static function renderHidden()
	{
		//preserve head data: 
		$doc = JFactory::getDocument(); 
  /*
  $title = $doc->getTitle(); 
  $kw = $document->getMetaData('keywords');
  $desc =	$document->getDescription( );
  $robot = $document->getMetaData('robots');
  $uth = $document->getMetaData('author');
   */
  $hd = $doc->getHeadData(); 
		
		
		
	 $db = JFactory::getDBO(); 
	 $q = 'select virtuemart_category_id from #__virtuemart_categories where published = 1 limit 0,1'; 
	 $db->setQuery($q); 
	 $cat_id = $db->loadResult(); 
	 
	  $controllerClassName = 'VirtueMartControllerCategory' ;
	  if (!class_exists($controllerClassName)) require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'category.php');
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'category'.DIRECTORY_SEPARATOR.'view.html.php'); 
	  //$view = new VirtuemartViewCategory(); 
	   $isset = JRequest::getVar('virtuemart_category_id', null); 
	  $cat_id = JRequest::getVar('virtuemart_category_id', $cat_id); 
	  //new: JRequest::setVar('virtuemart_category_id', $cat_id); 
	  
	  $oldoption = JRequest::getVar('option'); 
	  
	  
	  //new: JRequest::setVar('option', 'com_virtuemart'); 
	  
	  
	  //JRequest::setVar('virtuemart_category_id', 1); 
	  $config = array(); 
	  $config['base_path'] = JPATH_VM_SITE;
	  $config['view_path'] = JPATH_VM_SITE.DIRECTORY_SEPARATOR.'views'; 
	  $controller = new $controllerClassName($config); 
	  if (method_exists($controller, 'addViewPath')) { 
	  $controller->set('_basePath', JPATH_VM_SITE); 
	  $controller->set('_setPath', JPATH_VM_SITE.DIRECTORY_SEPARATOR.'views'); 
	  $controller->addViewPath(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'views');
	  }
	  $tp = self::getTemplatePath('category'); 

	  //$view = $controller->getView('category', 'html', '', $config);
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'rupview.php'); 
		$view = new rupSearch(); 
	  if (method_exists($view, 'addTemplatePath'))
	  {
	   $view->addTemplatePath(JPATH_VM_SITE.'/views/category/tmpl');
	  
	  
	  if (!empty($tp))
	   {
	    $view->addTemplatePath($tp); 
		
		
	   }
	   }
	   else
	  if (method_exists($view, 'addIncludePath'))
	  {
	   $view->addIncludePath(JPATH_VM_SITE.'/views/category/tmpl');
	  
	  
	  if (!empty($tp))
	   {
	    $view->addIncludePath($tp); 
		
		
	   }
	   }
	   
	   
	   $es = ''; 

	   $orderByList = array(); 
	   $orderByList['manufacturer'] = ''; 
	   $orderByList['orderby'] = ''; 
	   
	     $view->orderByList['orderby'] = ''; 
	     $view->orderByList['manufacturer'] = ''; 
	   
	   if (method_exists($view, 'assignRef'))
	   {
		   $view->assignRef('orderByList', $orderByList); 
	   }
	   
	   
	   
	   
	   
	  
	  
	  $view->viewName = 'category'; 
	  if (method_exists($view, 'setLayout'))
	  $view->setLayout('default'); 
	  else
	  if (method_exists($view, 'set'))
	  $view->set('layout', 'default');
	  else	  
	  if (method_exists($view, 'assignRef'))
	  $view->assignRef('layout', 'default'); 
	  else
	  $view->layout =  'default';

	  
	  	 
	  if (method_exists($view, 'set'))
	  $view->set('format', 'html');
	  else	  
	  if (method_exists($view, 'assignRef'))
	  $view->assignRef('format', 'html'); 
	  else
	  $view->format = 'html'; 

	   $cc = 0; 
	   
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'pagination.php'); 
	   
	   $prodcs = array(); 
	   $rp = new rupPagination($cc, 0, 1 ,1 );
	   $view->vmPagination = $rp;
	  
	  
	
		
	  ob_start(); 
	  if (method_exists($view, 'loadYag')) {
		  $view->loadYag(); 
		}
	  $view->display(); 
	  
	 
	  
	  $x = ob_get_clean(); 
	  
	  
	  
	  //restore head data: 
	  $hd2 = $doc->getHeadData(); 
  $hd3 = array(); 
  foreach ($hd2 as $key=>$val)
    {
	   switch ($key)
	   {
	    case 'title': 
			$hd3[$key] = $hd[$key]; 
		     break; 
	    case 'description': 
			$hd3[$key] = $hd[$key]; 
		     break; 
	    case 'link':
			$hd3[$key] = $hd[$key]; 
		     break; 
	    case 'metaTags': 
			$hd3[$key] = $hd[$key]; 
		     break; 
	    case 'links':
			$hd3[$key] = $hd[$key]; 
		     break; 
	    case 'styleSheets': 
			$hd3[$key] = $hd2[$key]; 
		     break; 
	    case 'style': 
			$hd3[$key] = $hd2[$key]; 
		     break; 
	    case 'scripts': 
			$hd3[$key] = $hd2[$key]; 
		     break; 
	    case 'script': 
			$hd3[$key] = $hd2[$key]; 
		     break; 
	    case 'custom':
			$hd3[$key] = $hd2[$key]; 
		     break; 
		default:
		  $hd3[$key] = $hd2[$key]; 
		     break; 
			 
		}

			 
	}
  $doc->setHeadData($hd3); 
  
  
  
  
	}
	public static function getModuleItemid($itemid=0) {
	 if (!empty($itemid)) return (int)$itemid; 
	 $my_itemid = (int)$itemid; 

if (empty($my_itemid)) {
 $app = JFactory::getApplication();
	 $menu = $app->getMenu();
	 $items = $menu->getItems( 'link', 'index.php?option=com_rupsearch&view=search', false );
	
	 foreach ($items as $item)
	 {
	    if (($item->language === '*') || ($item->language === $language_tag))
		{
			$my_itemid = $item->id; 
			break; 
		}
	 }


}
return $my_itemid; 
	}
	
 public static function setVMLANG() {
	 
	  if (!class_exists('VmConfig'))
		    {
			     if (!class_exists('VmConfig'))
				require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
				VmConfig::loadConfig ();

			}
			if(!defined('VMLANG'))		
			if (method_exists('VmConfig', 'setdbLanguageTag')) {
			   VmConfig::setdbLanguageTag();
			}
	 
    
	if ((!defined('VMLANG')) && (!empty(VmConfig::$vmlang))) {
	  define('VMLANG', VmConfig::$vmlang); 
	}
		
 }
 
 public static function getSG() {
	 $user_id = JFactory::getUser()->get('id'); 
	 $db = JFactory::getDBO(); 
	  if (!empty($user_id)) {
	  $q = 'select `virtuemart_shoppergroup_id` from `#__virtuemart_vmuser_shoppergroups` where `virtuemart_user_id` = '.(int)$user_id; 
			 $db->setQuery($q); 
			 $res = $db->loadAssocList(); 
			 $sg_list = array(); 
			 if (!empty($res))
			 foreach ($res as $row) {
				 $sg_list[(int)$row['virtuemart_shoppergroup_id']] = (int)$row['virtuemart_shoppergroup_id']; 
			 }
			 
			  if (empty($sg_list)) {
				 
				 $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_shoppergroups where `default` = 1 and `published` = 1'; //1=default for logged in, 2=default for anonymous
				 $db->setQuery($q); 
				 $r = $db->loadResult(); 
				 if (!empty($r)) {
					 $sg_list[(int)$r] = (int)$r; 
				 }
			  }
			 
	  }
	  else {
			  $q = 'select `virtuemart_shoppergroup_id` from #__virtuemart_shoppergroups where `default` = 2 and `published` = 1'; //1=default for logged in, 2=default for anonymous
			  $db->setQuery($q); 
			  $r = $db->loadResult(); 
			   if (!empty($r)) {
					 $sg_list[(int)$r] = (int)$r; 
				 }
	  }
	  return $sg_list; 
			 
 }
 
	public static function getToCats(&$top_cats, &$category_name, $params=null) {
	   
	   self::setVMLANG(); 
	 
	 $vm_cat_id = JRequest::getVar('virtuemart_category_id', JRequest::getVar('vm_cat_id', 0, 'get', 'INT') , 'get', 'INT'); 
	 
	 
	 
	 $vm_cat_id = (int)$vm_cat_id; 
	 $c = 0; 
	 $vm_cat_id = self::getTop($vm_cat_id, $c); 
	 
	 $topc = array(); 
	 
		 $c_where = ''; 		 
	 if (!empty($top_cats)) {
	    foreach ($top_cats as $k=>$t) {
		  $top_cats[$k] = (int)$t; 
		  $t = trim($t); 
		  $t = (int)$t; 
		  if (!empty($t)) {
			  $topc[$t] = array(); 
			  $topc[$t]['category_child_id'] = $t; 
		  }
		}
	 }
	 $db = JFactory::getDBO(); 
	 
	 
	 //debug: $q = 'select distinct cat.`category_child_id`, pc.virtuemart_product_id, sg.virtuemart_shoppergroup_id from #__virtuemart_category_categories as cat ';
	 $q = 'select distinct cat.`category_child_id` from #__virtuemart_category_categories as cat ';
	 
	  if (empty($params)) {
		$params = self::getParams(); 
	 }
	 $use_sg = $params->get('use_sg', false); 
	 
	 if (!empty($use_sg)) {
	  $q .= ' join #__virtuemart_product_categories as pc on pc.virtuemart_category_id = cat.category_child_id '; 
	  $q .= ' join #__virtuemart_products as p on pc.virtuemart_product_id = p.virtuemart_product_id '; 
	  $q .= ' join `#__virtuemart_products_'.VMLANG.'` as l on p.virtuemart_product_id = l.virtuemart_product_id '; 
	  $q .= ' left join #__virtuemart_product_shoppergroups as sg on sg.virtuemart_product_id = pc.virtuemart_product_id ';
	  
	  self::setVMLANG(); 
	  
	  
	 }

	  $q .= ' where '; 
	 
	 if (!empty($topc)) {
		 $q .= ' cat.`category_child_id` IN ('.implode(',', $top_cats).') '; 
	 }
	 else {
		 $q .= ' cat.`category_parent_id` = 0 ';
	 }
	 
	 
	
	 
	 if (!empty($use_sg)) {
		 
		
	 
			 $sg_list = self::getSG(); 
			 if (empty($sg_list)) {
				 $c_where .= ' ( ( `sg`.`virtuemart_shoppergroup_id` IS NULL ) ) ';
			 }
			 else {
				 if (count($sg_list)===1) {
					 $c_sg = ' (`sg`.`virtuemart_shoppergroup_id` = '.reset($sg_list).') or ';
				 }
				 else {
				  $c_sg = ' ((`sg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg_list).'))) or ';
				 }
				 $c_where .= '( '.$c_sg.' ( `sg`.`virtuemart_shoppergroup_id` IS NULL ) ) ';
				 
			 }
		 
		 
	 
		 
		 //$q .= ' and cat.`category_child_id` IN (select pc.`virtuemart_category_id` from #__virtuemart_product_categories as pc left join #__virtuemart_product_shoppergroups as sg on sg.virtuemart_product_id = pc.virtuemart_product_id where '.$c_where.' and pc.virtuemart_product_id IS NOT NULL) ';
		 $q .= ' and '.$c_where.'  and ( p.published = 1 ) ';
	 }
	 $q .= ' order by cat.`ordering` desc'; 
	 
	 
	 
	 try {
	  $db->setQuery($q); 
	  $topc = $db->loadAssocList(); 
	 }
	 catch (Exception $e) { 
	   return array(); 
	 }
	 
	 
	 
	 /*
     //$q = 'select sg.virtuemart_product_id from #__virtuemart_product_shoppergroups as sg join #__virtuemart_product_categories as pc on pc.virtuemart_product_id = sg.virtuemart_product_id where ((sg.virtuemart_shoppergroup_id = 25) or (sg.virtuemart_shoppergroup_id IS NULL)) and pc.virtuemart_category_id = 3426'; 
	 $q = 'select sg.virtuemart_product_id from # as pc join #__virtuemart_product_shoppergroups as sg on pc.virtuemart_product_id = sg.virtuemart_product_id where ((sg.virtuemart_shoppergroup_id = 25) or (sg.virtuemart_shoppergroup_id IS NULL)) and pc.virtuemart_category_id = 3426'; 
	 $db->setQuery($q); 
	 $res = $db->loadAssocList(); 
	 */
	/*
	 $q = 'select pc.`virtuemart_category_id` from #__virtuemart_product_categories as pc left join #__virtuemart_product_shoppergroups as sg on sg.virtuemart_product_id = pc.virtuemart_product_id where (  (`sg`.`virtuemart_shoppergroup_id` = 25) or  ( `sg`.`virtuemart_shoppergroup_id` IS NULL ) )  and pc.virtuemart_product_id IS NOT NULL'; 
	 $db->setQuery($q); 
	 $res = $db->loadAssocList(); 
	 */
	 
	 
	 
	 $mq = $tcq = ''; 
	 
	 $tc = array(); 
	 if (!empty($topc)) { 
	   foreach ($topc as $k=>$row) { 
	     $tc[] = (int)$row['category_child_id']; 
	   }
	 }
	 if (!empty($tc)) { 
	  $tcq = implode(',', $tc); 
	  $tcq = ' (`c`.`published` = 1 and `c`.`virtuemart_category_id` IN ('.$tcq.') ) '; 
	 }
	 if (!empty($vm_cat_id)) { 
	   $mq = '  (c.`virtuemart_category_id` = '.(int)$vm_cat_id.') ';
	 }
	 
	 if ((!empty($mq)) && (!empty($tcq)))  {
	   $tcq = ' or '.$tcq; 
	 }
	 
	 $q = 'select l.`category_name`, l.`virtuemart_category_id` from `#__virtuemart_categories_'.VMLANG.'` as l, `#__virtuemart_categories` as c where (l.`virtuemart_category_id` = c.`virtuemart_category_id`) and ('.$mq.$tcq.') '; 
	 
	
	 
	
	 $db->setQuery($q); 
	 try { 
	 
	 $top_cats = (array)$db->loadAssocList(); 
	 
	 }
	   catch (Exception $e) {
		  return array();
	 }
	 
	
	 
	 if (!empty($top_cats))
	 foreach ($top_cats as $k=>$v)
	 {
		 $v['virtuemart_category_id'] = (int)$v['virtuemart_category_id']; 
		 if ($v['virtuemart_category_id'] === $vm_cat_id) {
		   $category_name = $v['category_name']; 
		 
		   if (!in_array($v['virtuemart_category_id'], $tc)) {
		     unset($top_cats[$k]); 
		   }
		 }
		 
	 }
	 
	 
	  self::sortCats($top_cats); 
	 
	 
	}
	
	public static function sortCats(&$cats) {
		$copy = $cats; 
		usort($copy, array('rupHelper', "sort_cats"));
		
		$ret = array(); 
		//for ($i=0; $i<count($copy); $i++)
		foreach ($copy as $i=>$val3)
		{
		foreach ($cats as $key=>$val) 
		{
			 $val2 = $copy[$i]; 
			 if ($val2 === $val) {
				 $ret[$key] = $val; 
			 }
		 }
		}
		
		$cats = $ret; 
		return $ret; 
		//$attributes = $copy; 
	}
	
	public static function sort_cats($a, $b) {
		$a1 = $a['category_name']; 
		$b1 = $b['category_name']; 
	
	
	$tag = JFactory::getLanguage()->getTag(); 
	$tag = strtolower($tag); 
	$tag = str_replace('-', '_', $tag); 
	
	if (!class_exists('Collator')) return true; 
	
	$c = new Collator($tag);
    return $c->compare($a1, $b1); 
		
	}
	
	
	
	public static function getTop($id, &$count)
	{
		// only up to 10th level: 
		$id = (int)$id; 
		if ($count > 10) return $id; 
		
		$db = JFactory::getDBO(); 
		$q = 'select `category_child_id`, `category_parent_id` from `#__virtuemart_category_categories` where `category_child_id` = '.$id.' limit 0,1'; 
		$db->setQuery($q); 
		$res = $db->loadAssoc(); 
		
		if (empty($res['category_parent_id'])) return (int)$res['category_child_id']; 
		if ($res['category_parent_id'] === $res['category_child_id']) 
			return (int)$res['category_child_id']; 
		$count++; 
		
		
		
		
		return self::getTop($res['category_parent_id'], $count); 
		 
	}
	public static function getParams($id=0, $module=null)
	{
		jimport( 'joomla.registry.registry' );
		
		if ((!empty($module)) && (!empty($module->params))) {
		 return JRegistry($module->params); 
		}
		
		if (empty($id))
		$id = JRequest::getVar('module_id', null); 
	
		if (!empty($id))
		 {
		    $id = (int)$id; 
			$q = 'select `params` from `#__modules` where `id` = '.$id.' and `module` = \'mod_virtuemart_ajax_search_pro\' limit 0, 1'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$params_s = $db->loadResult(); 
			
			
			
			if (!empty($params_s))
			{
			$params = new JRegistry($params_s); 
			
			return $params; 
			}
			
		 }
		 
		 {
			
			$q = 'select `params` from `#__modules` where `module` = \'mod_virtuemart_ajax_search_pro\' and published = 1 limit 0,1'; 
			
			$db = JFactory::getDBO(); 
			$db->setQuery($q); 
			$params_s = $db->loadResult(); 
			
			
			
			if (!empty($params_s)) {
			 $params = new JRegistry($params_s); 
			 return $params; 
			}
		 }
		 
		 $r = new JRegistry(''); 
		 return $r; 
		 
		 
	}
	
	
	
	public static function getProducts($keyword, $prods=5, $popup=false, $order_by='', $child_handling=null, $limit_start=0)
	{
		
		
		
		$keyword_orig = $keyword; 
		$prods_orig = $prods; 
		$order_by_orig = $order_by; 
		
		$kX = preg_replace("/([[:alpha:]])([[:digit:]])/", "\\1 \\2", $keyword);
		if ($kX) {
			$keyword = $kX; 
		
		}
		$kX = preg_replace("/([[:digit:]])([[:alpha:]])/", "\\1 \\2", $keyword);
		if ($kX) {
		
			$keyword = $kX; 
		}
		
		

		
	self::setVMLANG(); 
	
	 $params = self::getParams(); 
	 
	 if (empty($keyword)) {
		 if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
		 return PCH::getCategoryProducts($keyword, $prods, $popup, $order_by); 
		 }
	 }
	 
	 if (VMLANG === 'de_de') {
		$keyword = str_replace('ss', '%', $keyword); 
		
	}
	 
	 $c_from = $c_where = $c_left = ''; 
	 $db = JFactory::getDBO(); 
	 $use_sg = $params->get('use_sg', false); 
	 if (!empty($use_sg)) {
		 $user_id = JFactory::getUser()->get('id'); 
		 //$c_from = ', `#__virtuemart_product_shoppergroups` as `sg` ';
		 $c_left = ' LEFT JOIN `#__virtuemart_product_shoppergroups` as sg ON p.`virtuemart_product_id` = sg.`virtuemart_product_id` ';
		 
		 
			 $sg_list = self::getSG(); 
			 
			 if (empty($sg_list)) {
				 
				
				 $c_where .= ' ( ( `sg`.`virtuemart_shoppergroup_id` IS NULL ) ) ';
			 }
			 else {
				 if (count($sg_list)===1) {
					 $c_sg = ' (`sg`.`virtuemart_shoppergroup_id` = '.reset($sg_list).') or ';
				 }
				 else {
				  $c_sg = ' ((`sg`.`virtuemart_shoppergroup_id` IN ('.implode(',', $sg_list).'))) or ';
				 }
				 $c_where .= '( '.$c_sg.' ( `sg`.`virtuemart_shoppergroup_id` IS NULL ) ) ';
				 
			 }
		 
		 
	 }
	 $distinct = ''; 
	 $child_handling_orig = $child_handling; 
	 if (is_null($child_handling)) {
	   $child_handling = JRequest::getVar('op_childhandling', $params->get('child_products', 0)); 
	 }
	 $child_handling = (int)$child_handling; 
	 if ($child_handling === 3) {
		 $prods = (int)$prods; 
		 //$prods = $prods * $prods; 
		 
	 		 $c_left .= ' LEFT JOIN `#__virtuemart_products` as childs ON p.`virtuemart_product_id` = childs.`product_parent_id` ';
			 $c_left .= " LEFT JOIN `#__virtuemart_products_".VMLANG."` as lchilds ON childs.`virtuemart_product_id` = lchilds.`virtuemart_product_id` ";
			 if (!empty($c_where)) $c_where .= ' and '; 
			 $c_where .= ' p.`product_parent_id` = 0 '; 
			 $distinct = ' DISTINCT ';
	 }
	 
	$priorities = $params->get('search_priority', 'PRODUCT_SKU,PRODUCT_NAME,PRODUCT_NAME_WORDS,PRODUCT_NAME_MULTI_WORDS,PRODUCT_SKU_STARTS_WITH,PRODUCT_DESC,PRODUCT_S_DESC,PRODUCT_ATTRIBS,MF_NAME,CAT_NAME,PRODUCT_SKU_ENDS'); 
	// old typo: 
	if (strpos($priorities, 'PRODUCT_MPM') !== false) $priorities = str_replace('PRODUCT_MPM', 'PRODUCT_MPN', $priorities); 
	 $priorities = explode(',', $priorities); 
	 $prod_o = $prods; 
	 $prods++; 
	
	  
	  $or = $or2 = $or3 = '';
	  $ko = trim($keyword);  
	  $ko2 = preg_replace('/\s+/', ' ',$keyword);
	  if (!empty($ko2)) {
	   $ko = $ko2; 
	  }
	  $ae = explode(' ', $ko); 
	  
	  $no_short = $params->get('no_short', false); 
	  
	  $f_cats = $params->get('cat_search', false); 
	  $only_current = JRequest::getVar('only_current', $params->get('only_current', false)); 
	  if (!empty($only_current)) $f_cats = true; 
	  
	  $vm_cat_id = JRequest::getInt('vm_cat_id', 0); 
	  if (empty($vm_cat_id)) $f_cats = false; 
	  
	  
	  
	  $ora = array(); 
	  $ora2 = array(); 
	  $ora3 = array(); 
	  if ((!empty($ae)) && (count($ae)>1))
	   {
	      if (empty($or)) {
			  $or = ' OR ('; 
			  $or2 = ' OR ('; 
			  $or3 = ' OR ('; 
		  }
		  $i = 0;
		  $all = count($ae); 
	      foreach ($ae as $word)
		    {
			   if (empty($word)) {
				   $all--; 
				   continue; 
			   }
				
			   $i++; 
			   if (strlen($word)<=2) 
			   {
				   $word .= ' '; 
			   }
			   
			   // take just the base of the word: 
			   if (empty($no_short)) { 
			   if (mb_strlen($word)>8)
			   {
				   $word = mb_substr($word, 0, -3); 
			   }
			   else
			   if ((mb_strlen($keyword)>6) && (mb_strlen($keyword)<9))
			   {
				      $word = mb_substr($word, 0, -2); 
			   }
			   else
			   {
			   if (mb_strlen($word)>3) {
			    $word = mb_substr($word, 0, -1); 
			   }
			   }
			   }
			   
			   
			   
			   
			   $ora[] = " (  ".self::letterWildcardsEscape('l.`product_name`', 'LIKE', '%'.$word.'%')." ) "; 
			   $ora2[] = " (  ".self::letterWildcardsEscape('l.`product_s_desc`', 'LIKE', '%'.$word.'%')." ) "; 
			   $ora3[] = " (  ".self::letterWildcardsEscape('l.`product_desc`', 'LIKE', '%'.$word.'%')." ) "; 
			   
			   if ($i!=$all) 
			   {
				   $or .= ' AND '; 
				   $or2 .= ' AND '; 
				   $or3 .= ' AND '; 
			   }
			}
			
		  $or = ' OR ('.implode(' AND ', $ora).')'; 
		  $or2 = ' OR ('.implode(' AND ', $ora2).')'; 
		  $or3 = ' OR ('.implode(' AND ', $ora3).')'; 
		  
	   }
	   
	   if (empty($ora)) {
		     $or = $or2 = $or3 = '';
	   }
	  if (is_null($child_handling)) {
	   $child_handling = JRequest::getVar('op_childhandling', $params->get('child_products', 0)); 
	  }
      $child_handling = (int)$child_handling; 
	  
	  $only_in_stock = $params->get('only_in_stock', false); 
	  
	  if (empty($order_by)) $order_by = $params->get('order_byf', ''); 
	  if (empty($order_by)) $order_by = $params->get('order_by', ''); 
	  if ($order_by === 'none') $order_by = ''; 
	  
	  
	  
	  $stock = ''; 
	  if (!empty($only_in_stock))
	  {
		  $stock = ' p.`product_in_stock` > 0 and '; 
	  }
	  $order_single = ''; 
	  $order_sql = ''; 
	
	  if (empty($limit_start)) {
	  $limitstart = JRequest::getInt('limitstart', JRequest::getInt('start', 0)); 
	  }
	  else {
		  $limitstart = $limit_start; 
	  }
	 
	  $product_limit = " LIMIT ".$limitstart.",".$prods.' ';
	  $global_limit = " LIMIT ".$limitstart.",".$prods.' ';
	  $order_single2  = $product_limit2 = ''; 
	  
	  
	  
	  //we won't use internal limits: 
	  $product_limit = $product_limit2 = ''; 
	   $order_all = ''; 
	  if (!empty($order_by))
	  {
		  switch ($order_by)
		  {
			  case 'product_name': 
			  $order_sql = ', l.`product_name` as SORTFIELD '; 
			  $order_all = ' order by SORTFIELD '; 
			  break; 
			  case 'created_on': 
			  $order_sql = ', p.created_on as SORTFIELD '; 
			  $order_all = ' order by SORTFIELD desc '; 
			  
			  break; 
			  case 'pordering': 
				$order_sql = ', p.pordering as SORTFIELD '; 
				$order_all = ' order by SORTFIELD asc '; 
			  break;
			   case 'available_on':
			   
			   //$order_sql = ', "{ROWPRIORITY}" as `rank`, p.product_available_date as SORTFIELD '; 
			   //$order_sql = ', "{ROWPRIORITY}" as `rank`, p.product_available_date as SORTFIELD '; 
			    $order_sql = ', p.product_available_date as SORTFIELD '; 
			    $order_all = ''; 
			   //$order_all = ' order by SORTFIELD desc'; 
			   //$order_single2 = ' ORDER BY SORTFIELD asc'; 
			   //$product_limit2 = ' limit 0, '.$prods; //$product_limit;
			   //$product_limit = ''; 
			   $zero_select = ', 0 as product_available_date'; 
			   $order_single2 = ' ORDER BY p.product_available_date desc'; 
			   
			   //$global_limit = ''; 
			   break;
			  default: 
			   $order_sql = ''; 
			   $order_all = ''; 
		  }
	  }
	  
	  {
		  $product_limit2 = $product_limit; 
		  $product_limit = ''; 
		  
	  }
	  
	  $whereM = ' and (@WHEREM:=1) ';
	  $whereMSearch = $whereM; 
	  
	  
	  $search = $searchS = array(); 
	  
	  if (!empty($c_where)) $c_where .= ' and '; 
	  
	  if (!empty($f_cats)) {
		  $cats_ids = array($vm_cat_id); 
		  $c_from .= ', `#__virtuemart_product_categories` as vpc '; 
		  $c_where .= ' ( (`vpc`.`virtuemart_category_id` IN ('.implode(',', $cats_ids).') ) and (`vpc`.`virtuemart_product_id` = `p`.`virtuemart_product_id`) ) and '; 
	  }
			
			$template = JFactory::getApplication()->getTemplate(); 
	  
	  		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'custom_query.php')) {
			   
			   //extends c_from and c_where:
			   include(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'custom_query.php');
			}
	  
		  $stock .= $c_where; 
	  
	  $c_from = $c_from;
	  
	  
	  $generic_from = " from (`#__virtuemart_products` AS p, `#__virtuemart_products_".VMLANG."` as l ".$c_from.') '.$c_left; 
	  
	  $no_discontinued = $params->get('no_discontinued', false); 
	  if (!empty($no_discontinued)) {
		  $whereM .= ' and ((`p`.`product_discontinued` < 1) or (`p`.`product_discontinued` IS NULL)) '; 
	  }
	  
	 
	  
	  if (!defined('VMLANG')) define('VMLANG', 'en_gb'); 
	
	  $search_desc_request = JRequest::getInt('search_desc', 1); 
	  $ft = $params->get('use_fulltext', false); 
	   
	   $queries_path = JModuleHelper::getLayoutPath('mod_virtuemart_ajax_search_pro', 'queries'); 
	   if (file_exists($queries_path)) {
		require($queries_path); 
	   }
	   else {
		   require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'mod_virtuemart_ajax_search_pro'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'queries.php'); 
	   }
	  
		  
		  $debug = $params->get('debug', false); 
		  
		  if (!empty($order_all)) $q .= $order_all; 
		 
		  
		 
		  $f = true; 
		  $q = ''; 
		  /*
		  if (!empty($order_by)) {
		  $q = 'select 0 as virtuemart_product_id, 0 as product_parent_id '.$zero_select; 
		  $prods++; 
		  if (!empty($global_limi)) {
		    $global_limit = " LIMIT 0,".$prods.' ';
		  }
		  $f = false; 
		  }
		  */
		  if ($debug) {
		   echo '
<script>
/* <![CDATA[ */
		if (typeof console !== \'undefined\')
		 console.log(\'query\', \''.addslashes(json_encode($priorities, JSON_UNESCAPED_UNICODE)).'\');
/* ]]> */
		 </script>'; 
		  }
		  //foreach ($search as $key=>$val)
		  $singl = array();  $ret = array(); 
		  $row = 1000; 
		  $ids = array(); 
		  $px = $params->get('search_method', 0); 
		  $onlysku = true; // if the products are found only by searching SKU, ignore child handling and display childs as well
		  if (!empty($px)) {
			  
			  
			   foreach ($priorities as $k=>$v) {
				   
				   $issku = false; 
				   
				   if (stripos($v, 'PRODUCT_SKU')===0) {
					   $issku = true; 
				   }
				   
			   $row++; 
			     $key = strtoupper(trim($v));
			     if (empty($search[$key])) continue; 
				 $val = $search[$key]; 
			  if (!is_array($val)) { 
			  $val = str_replace('{ROWPRIORITY}', $row, $val); 
			  $nw = ''; 
			  if (!empty($ids)) {
			    $nw = ' and  p.`virtuemart_product_id` NOT IN ('.implode(',', $ids).')'; 
			  }
			  $val = str_replace($whereMSearch, $nw, $val); 
			  //$val .= ' COLLATE utf8_slovak_ci '; 
			  
			  
			  
			  
			  $val .= $order_single2.$product_limit2.$order_all.$global_limit; 
			    $qd = $val; 
				
				
				$rr = self::runQuery($db, $val, $debug, $key); 
			  
			    if (!empty($rr))
			    foreach ($rr as $kX=>$vX) {
					
					//we got results: 
					if (!$issku) $onlysku = false; 
					
					$pid = $vX['virtuemart_product_id'] = (int)$vX['virtuemart_product_id']; 
					$ids[$pid] = $pid; 
					$ret[] = $vX; 
					
					if (count($ids)>=$prods) {
						break 2; 
					}
					
				}
			  }
			  else
			  {
				  
				  
				  foreach ($val as $k2X => $valX) {
				     $row++; 
					 $val2 = str_replace('{ROWPRIORITY}', $row, $valX); 
					 
					 $val2 .= $order_single2.$product_limit2.$order_all.$global_limit;
					 
					 
					 $nw = ''; 
			  if (!empty($ids)) {
			    $nw = ' and  p.`virtuemart_product_id` NOT IN ('.implode(',', $ids).')'; 
			  }
			  $val2 = str_replace($whereMSearch, $nw, $val2); 
			  $qd = $val2; 
			  $rr = self::runQuery($db, $val2, $debug, $key.$k2X); 
						
			  
			    if (!empty($rr))
			    foreach ($rr as $kX=>$vX) {
					
					//we got results: 
					if (!$issku) $onlysku = false; 
					
					$pid = $vX['virtuemart_product_id'] = (int)$vX['virtuemart_product_id']; 
					$ids[$pid] = $pid; 
					$ret[] = $vX; 
					
					
					
					if (count($ids)>=$prods) {
						break 3; 
					}
					
				}
				
				
					 
				  }
			  }
			  
			  if (count($ids) === 1) {
				  //there is just a one product found, so in this case we can use child products: 
				  if ($child_handling === 3) {
					  return self::getProducts($keyword_orig, $prods_orig, $popup, $order_by_orig, 0); 
				  }
			  }
			  
			  if (count($ids)>=$prods) {
			   break; 
			  }
			  
			   }
			   
			   
		  }
		  else {
		  
		  foreach ($priorities as $k=>$v)
		  {
			  $row++; 
			  $key = strtoupper(trim($v));
			  if (empty($search[$key])) continue; 
			  $val = $search[$key]; 
			  if (!is_array($val)) { 
			  $val = str_replace('{ROWPRIORITY}', $row, $val); 
			  $singl[] = $val; 
			  if ($f)
			  {
				  $q .= $val."\n"; 
				  $f = false; 
			  }
			  else
			  {
				  $q .= ' union ('.$val.$order_single2.$product_limit2.')'."\n"; 
			  }
			  $f = false; 
			  }
			  else
			  {
				  foreach ($val as $val2) {
				  $row++; 
				  $val2 = str_replace('{ROWPRIORITY}', $row, $val2); 
				  $singl[] = $val2; 
			  if ($f)
			  {
				  $q .= $val2."\n"; 
				  $f = false; 
			  }
			  else
			  {
				  $q .= ' union ('.$val2.$order_single2.$product_limit2.')'."\n"; 
			  }
			   $f = false; 
				  }
			 
			  }
		  }
		  if (!empty($order_all)) {
		   $q .= $order_all; 
		  }
		  if (count($priorities)>1) { 
	      $q .= $global_limit;
		  }
		
		 // echo $q;
		
		
		//echo $q; die(); 
		
				$qd = str_replace('#__', $db->getPrefix(), $q); 
		$start = microtime(true); 
		  $ret = self::runQuery($db, $q, $debug); 
		  
		$q = str_replace('#__', $db->getPrefix(), $q); 
			//echo $q; 
		
		if (!empty($debug)) { 
		
		$end = microtime(true); 
		  $len = $end - $start; 
		
		self::getDebugQuery($db, $q, $len, $singl); 
		
		}
		
		  }
		$debug = $params->get('debug', false); 
		
		
		
		self::$total_count = null; 
		
		if (!empty($limitstart))
		if (empty($ret)) {
			
			static $recursion; 
			if (empty($recursion)) {
				$recursion = true; 
				$all = $limitstart+$prods;
				
				$proddb = self::getProducts($keyword_orig, $all , $popup, $order_by_orig, $child_handling_orig, $prods); 
				self::$total_count = count($proddb); 
				$proddb = array_slice($proddb, -$prods);
				return $proddb;
				
			}
		}
		if (empty($ret)) return array(); 
		
		
		
		
		if ($popup) return $ret; 
		$proddb = array(); 
		foreach ($ret as $key=>$val)
		 {
		 
		 
		 
		
		/*
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT1="Include both child and parent products"
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT2="Include only child products and products without child products (skip parent products)"
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT3="Include only parent products"

		*/
		
		
		
		if ((!empty($child_handling)) && (!$onlysku))
		{

		$child_type = array(); 
		$child_type[] = 1; 
		//if (!empty($val['product_parent_id'])) $child_type[] = 2; 
		
		
		
		// has children, ie is parent: 
		if (!empty($val['children']) && (empty($val['product_parent_id']))) $child_type[] = 3; 
		
		if (!empty($val['children'])) $child_type[] = 3;
	    if (empty($val['product_parent_id'])) $child_type[] = 3; 
		
		
		// does not have children and is not a child product (it's parent product)
		if (empty($val['children']) && (empty($val['product_parent_id']))) $child_type[] = 3;
		// is parent with no children, same as above
		if ((empty($val['product_parent_id'])) && (empty($val['children']))) {
			
			if ($child_handling === 3) {
			  $proddb[$val['virtuemart_product_id']] = $val['virtuemart_product_id']; 
			}
			
			$child_type[] = 2; 
		}
		// is child and does not have subchildren: 
		if ((!empty($val['product_parent_id'])) && (empty($val['children']))) {
			
			if ($child_handling === 3) {
			  $proddb[$val['product_parent_id']] = $val['product_parent_id']; 
			}
			$child_type[] = 2; 
		}
		
		 if (!in_array($child_handling, $child_type))
		 {
		 
		 continue; 
		 }
		 
		}
		
		 
		   $val['virtuemart_product_id'] = (int)$val['virtuemart_product_id']; 
		   $proddb[$val['virtuemart_product_id']] = $val['virtuemart_product_id']; 
		 }
		 
		if ($prods !== 1)
		if ((count($ret)===$prods) && (count($proddb) < $prods)) {
			$diff = $prods - count($proddb); 
			//we need to expenentionally get more results
			//prods=51 (for listing of 50)
			//proddb is 4
			
			//$prods = $prods * $prods; 
			//return self::getProducts($keyword_orig, $prods, $popup, $order_by); 
			
		}
		
		
		
		return $proddb; 
		
	}
	
	public static function getRepChars() {
		 if (!function_exists('mb_strtolower')) return array(); 
		 
		 $def = "Á|A,Â|A,Å|A,Ă|A,Ä|A,À|A,Ć|C,Ç|C,Č|C,Ď|D,É|E,È|E,Ë|E,Ě|E,Ì|I,Í|I,Î|I,Ï|I,Ĺ|L,Ľ|L,Ń|N,Ň|N,Ñ|N,Ò|O,Ó|O,Ô|O,Õ|O,Ö|O,Ŕ|R,Ř|R,Š|S,Ś|O,Ť|T,Ů|U,Ú|U,Ű|U,Ü|U,Ý|Y,Ž|Z,Ź|Z,á|a,â|a,å|a,ä|a,à|a,ć|c,ç|c,č|c,ď|d,đ|d,é|e,ę|e,ë|e,ě|e,è|e,ì|i,í|i,î|i,ï|i,ĺ|l,ń|n,ň|n,ñ|n,ò|o,ó|o,ô|o,ő|o,ö|o,š|s,ś|s,ř|r,ŕ|r,ť|t,ů|u,ú|u,ű|u,ü|u,ý|y,ž|z,ź|z,˙|-,ß|ss,Ą|A,µ|u,Ą|A,µ|u,ą|a,Ą|A,ę|e,Ę|E,ś|s,Ś|S,ż|z,Ż|Z,ź|z,Ź|Z,ć|c,Ć|C,ł|l,Ł|L,ó|o,Ó|O,ń|n,Ń|N,А|A,а|a,Б|B,б|b,В|V,в|v,Г|G,г|g,Д|D,д|d,Е|E,е|e,Ж|Zh,ж|zh,З|Z,з|z,И|I,и|i,Й|Y,й|y,К|K,к|k,Л|L,л|l,М|M,м|m,Н|N,н|n,О|O,о|o,П|P,п|p,Р|R,р|r,С|S,с|s,Т|T,т|t,У|U,у|u,Ф|F,ф|f,Х|Ch,х|ch,Ц|Ts,ц|ts,Ч|Ch,ч|ch,Ш|Sh,ш|sh,Щ|Sch,щ|sch,Ы|I,ы|i,Э|E,э|e,Ю|U,ю|iu,Я|Ya,я|ya,Ъ|v,ъ|v,Ь|v,ь|v,ľ|l"; 
		 
		 $e = explode(',', $def); 
		 $to_rep = array(); 
		 foreach ($e as $ind=>$pair) {
			 $ae = explode('|', $pair); 
			 if (count($ae)===2) {
			 if ((mb_strlen($ae[0]) === 1) && (mb_strlen($ae[1])===1)) {
				$s = $ae[0]; 
				$h = $ae[1]; 
				
				$h = mb_strtolower($h);
				if (!isset($to_rep[$h])) {
				 $to_rep[$h] = array(); 
				 $to_rep[$h][] = $h;
				 $up_h = mb_strtoupper($h);
				 $to_rep[$h][$up_h] = $up_h;
				}
				$low_s = mb_strtolower($s); 
				$to_rep[$h][$low_s] = $low_s;
				$up_s = mb_strtoupper($s); 
				$to_rep[$h][$up_s] = $up_s;
			 }
			 }
			 
		 }
		 
		 return $to_rep; 
		 
	}
	
	public static function letterWildcardsEscape($col, $operator, $keyword, $doNothing=false) {
		 
		 //ESCAPE FIRST !
		 $operator = strtoupper($operator); 
		 $db = JFactory::getDBO(); 
		 $returnQuery = ''; 
		 $found = false;
		 $params = self::getParams(); 
		 $w1 = $params->get('letter_wildcard', ''); 
		 $w2 = $params->get('letter_wildcard2', ''); 
		 
		 $word = $keyword; 
		 
		 $hasStart = false; 
		 $hasEnd = true; 
		 $prefix = ''; 
		 if (substr($word, 0,1)==='%') {
			 $hasStart = true; 
			 $word = substr($word, 1); 
			 $prefix = '%'; 
		 }
		 $suffix = ''; 
		 if (substr($word, -1)==='%') {
			 $hasEnd = true; 
			 $word = substr($word, 0, -1); 
			 $suffix = '%'; 
		 }
		 
		 $return = $db->escape($word); 
		 
		 if ($doNothing) {
			 return $col.' '.$operator.' \''.$prefix.$db->escape($return).$suffix.'\''; 
		 }
		 
		 
		 if (!empty($w1)) {
			 $xA = explode(',', $w1); 
			 foreach ($xA as $ind=>$str) {
				 $str = trim($str); 
				 if (empty($str)) continue; 
				 //escape single letter:
				 $str = $db->escape($str); 
				 $rep[] = $str; 
				 if ((function_exists('mb_stripos')) && (mb_stripos($word, $str)!==false)) {
					 $found = true; 
				 }
				 else
				 if ((!function_exists('mb_stripos')) && (stripos($word, $str)!==false)) {
					 $found = true; 
				 }
				 $return = str_replace($str, '{%}', $return); 
			 }
			 $toRep = '('.implode('|', $rep).'|['.implode('', $rep).'])'; 
			 
			 $special_code = true; 
			 if (!empty($special_code)) {
			 $all_rep = self::getRepChars(); 
			 
			 $myint = 0; 
			 foreach ($all_rep as $ind=>$vals) {
				 $myint++; 
				 foreach ($vals as $special_char) {
					 if (mb_stripos($return, $special_char) !== false) {
						 $return = str_replace($special_char, '{%'.$myint.'}', $return); 
					 }
				 }
			 }
			 
			 $myint2 = 0; 
			 foreach ($all_rep as $ind=>$vals) {
				$myint2++; 
				
				$rep2 = '('.implode('|', $vals).'|['.implode('', $vals).'])'; 
				$return = str_replace('{%'.$myint2.'}',$rep2,$return); 
			 }
			 }
			 
			 $return = str_replace('{%}', $toRep, $return); 
			 // $return = str_replace('][', '][', $return); 
			 if (empty($hasStart)) {
				 $return = '^'.$return; 
			 }
			 if (empty($hasEnd)) {
				 $return .= '$'; 
			 }
		 }
		 if ($operator === 'LIKE') {
		 if (!empty($w2)) {
			 $xA = explode(',', $w2); 
			 foreach ($xA as $ind=>$str) {
				 $str = trim($str); 
				 if (empty($str)) continue; 
				 $word = str_replace($str, '_', $word); 
				 
			 }
		 }
		 }
		 
		 
		 
		if ($found) {
			
			$returnQuery = $col.' REGEXP \''.$return.'\''; 
		}
		
		 $returnQuery2 = $col.' '.$operator.' \''; 
		 if ($hasStart) $returnQuery2 .= '%'; 
		 $returnQuery2 .= $db->escape($word);
		 if ($hasEnd) $returnQuery2 .= '%'; 
		 $returnQuery2 .= '\''; 
		
		if (!empty($returnQuery)) {
			$returnQuery = ' (('.$returnQuery.') or ('.$returnQuery2.')) '; 
		}
		else {
			$returnQuery = $returnQuery2; 
		}
		
		return $returnQuery; 
	}
	
	public static function runQuery(&$db, $q, $debug=true, $msg=''){
		
		if (empty(self::$cache)) self::$cache = array(); 
		if (!empty(self::$cache[$q])) return self::$cache[$q]; 
		
		$ret = array(); 
		$start = microtime(true); 
	try
		{
			$rep = array("\n", "\r", "\t"); 
			$w = array('', '', ''); 
			$q = str_replace($rep, $w, $q); 
			$qd = str_replace('#__', $db->getPrefix(), $q); 
			
			
		$db->setQuery($q); 
		
		
		
		$ret = $db->loadAssocList(); 

		
		
		if (!empty($debug)) 
		{ 
		 
		 
		 $end = microtime(true); 
		 $len = $end - $start; 
		 
		 //echo '<br /><br /><b>query: </b><br /><br />'.str_replace("\n", "<br />\n", $qd); 
		 $c = count($ret); 
		 //echo '<br />n.results: '.$c."<br />\n"; 
		 //echo '<br />time: '.$len."s<br />\n"; 
		 //if (!empty($msg)) echo '<br />'.$msg."<br />\n"; 
		 echo '
<script>
/* <![CDATA[ */
		if (typeof console !== \'undefined\')
		 console.log(\'query\', \''.addslashes(json_encode('('.$c.'): '.$qd, JSON_UNESCAPED_UNICODE)).'\');
/* ]]> */
		 </script>'; 
		}
		
		
		

		  
		  
		}
		catch (Exception $e)
		{
			
			if ($debug)
			{
			
			 echo '<br /><br /><b>error query: </b><br /><br />'.$qd; 
			$msg = (string)$e; 
			echo '<br /><br />Error: '.$msg.'<br />'; 
			die(); 
			}
		}
		$ar = array(); 
		if (!empty($ret)) {
		foreach ($ret as $k=>$v) {
			$k = (int)$k; 
			$ar[$k] = (array)$v; 
		}
		}
		self::$cache[$q] = $ar; 
		return $ar; 
	}
	public static function getDebugQuery(&$db, $q, $len, $singl=array()) {
	
	$q2 = 'explain '.$q; 
		$db->setQuery($q2); 
		$expl = $db->loadAssocList(); 
		//JFactory::getApplication()->enqueueMessage($len.'s <br />'.$q.'<br />'.print_r($expl)); 
		$GLOBALS['opcsearchbench'] = array(); 
		$GLOBALS['opcsearchbench']['time'] = $len.'s'; 
		$GLOBALS['opcsearchbench']['query'] = $q; 
		$GLOBALS['opcsearchbench']['EXPLAIN'] = $expl; 
		foreach ( $singl as $k=>$q3)
		{
			$start = microtime(true); 
			$q2 = 'explain '.$q3; 
			$db->setQuery($q2); 
			$expl = $db->loadAssocList(); 
			$end = microtime(true); 
		    $len = $end - $start; 
			$GLOBALS['opcsearchbench']['EXPLAIN_'.$k] = $expl; 
			$GLOBALS['opcsearchbench']['EXPLAIN_'.$k]['query'] = $q3; 
			$GLOBALS['opcsearchbench']['EXPLAIN_'.$k]['len'] = $len.'s'; 
		}
	}
	// original code from shopfunctionsF::renderMail
	public static function getVMView(&$ref, $viewName='category', $vars=array(),$controllerName = NULL, $layout='default', $format='html', $retObj=false)
	{
		
		if (!class_exists('CurrencyDisplay'))
	require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
	    $cache_file = $ref->cache_file; 
	    $originallayout = JRequest::getVar( 'layout' );
	  	if(!class_exists('VirtueMartControllerVirtuemart')) require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'virtuemart.php');
// 		$format = (VmConfig::get('order_html_email',1)) ? 'html' : 'raw';
		
		// calling this resets the layout
		//$controller = new VirtueMartControllerVirtuemart();
		//JRequest::setVar( 'layout', $layout );
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'rupview.php'); 
		
	   $oldoption = JRequest::getVar('option'); 
	   //new: JRequest::setVar('option', 'com_virtuemart'); 

		if ($viewName === 'category') {
		$view = new rupSearch(); 
		}
		else {
			if (!class_exists('VirtuemartView'.$viewName)) {
			  require_once(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.strtolower($viewName).DIRECTORY_SEPARATOR.'view.html.php');
			}
			$class = 'VirtuemartView'.$viewName;
			$view = new $class();
		}
		foreach ($ref as $k=>$v)
		{
			if (empty($view->$k))
			$view->$k = $v; 
		}
		
		$view->viewName = $viewName; //'category'; 
	  
	  $view->showproducts = true; 
	  $show_prices  = VmConfig::get('show_prices',1);
	  $view->show_prices = $show_prices; 
	  if (isset($ref->keyword))
	  {
	  $view->keyword = $ref->keyword; 
	  $view->search = $view->keyword; 
	  }
	  if (!isset($view->currency))
	  {
	  
	    $currency = CurrencyDisplay::getInstance( );
		$view->currency =& $currency;
	  }
	  
	  if (empty($view->category))
	  {
		  
				$view->category = new stdClass();
				$view->category->category_name = '';
				$view->category->category_description= '';
				$view->category->haschildren= false;
			
	  }
	  if (!isset($view->searchcustom))
	  {
	    $view->searchcustom = '';
		$view->searchCustomValues = '';
	  }
	  if (!isset($view->categoryId))
	  {
		  $view->categoryId = 0; 
	  }
	  if (!isset($view->perRow))
	  $view->perRow = VmConfig::get('products_per_row',3);
  
	  $view->viewName = 'category'; 
	  if (method_exists($view, 'setLayout'))
	  $view->setLayout('default'); 
	  else
	  if (method_exists($view, 'set'))
	  $view->set('layout', 'default');
	  else	  
	  if (method_exists($view, 'assignRef'))
	  $view->assignRef('layout', 'default'); 
	  else
	  $view->layout =  'default';

	   $tp = self::getTemplatePath('category'); 
	   
	   if (!empty($tp))
	   {
		if (method_exists($view, 'addTemplatePath'))
		{
	      $view->addTemplatePath($tp); 
		}
		else
		{
			if (method_exists($view, 'addIncludePath'))
			{
			$view->addIncludePath($tp); 
			}
		}
		
		
	   }
	   
	   
	 
	  if (method_exists($view, 'set'))
	  $view->set('format', 'html');
	  else	  
	  if (method_exists($view, 'assignRef'))
	  $view->assignRef('format', 'html'); 
	  else
	  $view->format = 'html'; 

		
		//$view->setLayout($layout); 
		
		if (!$controllerName) $controllerName = $viewName;
		$controllerClassName = 'VirtueMartController'.ucfirst ($controllerName) ;
		if (!class_exists($controllerClassName)) require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controllerName.'.php');

		//Todo, do we need that? refering to http://forum.virtuemart.net/index.php?topic=96318.msg317277#msg317277
		
		
		
		if (method_exists($view, 'getPath')) {
		 $path = $view->getPath('default', 'default'); 
		}
	    
		foreach ($vars as $key => $val) {
	
		
			$view->$key = $val;
			//$view->assignRef($key, $val); 
		}
		$view->search = null; 
		$view->keyword = ''; 
		if (isset($view->category) && (is_object($view->category)))
		$view->category->haschildren = false; 
		//$count = count($ids); 
		$prods = JRequest::getInt('prods', 5); 
		$cc = count($vars['products']); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'pagination.php'); 
		$rp = new rupPagination($cc, 0, $prods , $vars['perRow'] );
		$vars['vmPagination'] = $rp;
		
		$view->vmPagination = $vars['vmPagination']; 
		$view->showRating = false; 
		$view->showBasePrice = false; 
		$view->showcategory = 0; 
		$productsLayout = VmConfig::get('productsublayout','products');
		if(empty($productsLayout)) $productsLayout = 'products';
		$view->productsLayout = $productsLayout; 
		//new: $cat = JRequest::getVar('virtuemart_category_id', null); 
		//new: JRequest::setVar('virtuemart_category_id', false); 
		
		$html3 = ''; 
		 if (empty($vars['products']))
	  {
		  
		  if (method_exists($view, 'assignRef'))
		  {
			  $f = false; 
		  $view->assignRef('showproducts', $f); 
		  $vars['showproducts'] = false; 
		  }
		  else
		  {
		  $view->showproducts = false; 
		  $vars['showproducts'] = false; 
		  }
	  
	  
	     $html3 = JText::_ ('COM_VIRTUEMART_NO_RESULT');
	  }
	  $vars['product_keyword'] = ''; 
	  $vars['keyword'] = ''; 
	  foreach ($vars as $key => $val) {
	
		
			$view->$key = $val;
			//$view->assignRef($key, $val); 
		}
	  	 
		$orderByList = array(); 
	   $orderByList['manufacturer'] = ''; 
	   $orderByList['orderby'] = ''; 
	   
	     $view->orderByList['orderby'] = ''; 
	     $view->orderByList['manufacturer'] = ''; 
	   
	   if (method_exists($view, 'assignRef'))
	   {
		   $view->assignRef('orderByList', $orderByList); 
	   }
		
		
		ob_start(); 
		if (method_exists($view, 'loadYag')) {
		  $view->loadYag(); 
		}
		$html = $view->display();
		$html2 = ob_get_clean(); 
		//new: JRequest::setVar('virtuemart_category_id', $cat); 
		//new: JRequest::setVar( 'layout', $originallayout );
		
		 
	    //new:  JRequest::setVar('option', $oldoption); 
		
		$ret = $html.$html2.$html3; 
		return $ret; 
	}
	
	//shows last page of fake pagination from default.php of category:
	//required mod_customfilters + fakegination + module ajax search to be enabled
	public static function getMyView(&$ref)
	{
		
		$cat_id = JRequest::getInt('virtuemart_category_id', 0); 
		if (!empty($cat_id)) {
			
		   JRequest::setVar('option', 'com_rupsearch'); 
		   JRequest::setVar('view', 'search'); 
		   JRequest::setVar('q', ''); 

		   $db = JFactory::getDBO(); 
		   $q = 'select category_parent_id from #__virtuemart_category_categories where category_child_id = '.(int)$cat_id; 
		   $db->setQuery($q); 
		   $parent_id = $db->loadResult(); 
		   JRequest::setVar('primary_virtuemart_category_id', $parent_id); 
		   JRequest::setVar('virtuemart_category_id', array($cat_id, $parent_id)); 
		   JRequest::setVar('keyword', ''); 
		   /*https://php7.rupostel.com/zigotest3/component/rupsearch/rsearch?tab=Kateg%C3%B3rie&virtuemart_category_id[0]=13484&virtuemart_category_id[1]=13540&primary_virtuemart_category_id=13484&start=9984*/
		   
		}
		
		static $recursion; 
		if (!empty($recursion)) return ''; 
		$recursion = true; 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'rupview.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'view.html.php'); 
		$view = new RupsearchViewSearch(); 
  
	  $view->viewName = 'category'; 
	  if (method_exists($view, 'setLayout'))
	  $view->setLayout('default'); 
	  else
	  if (method_exists($view, 'set'))
	  $view->set('layout', 'default');
	  else	  
	  if (method_exists($view, 'assignRef'))
	  $view->assignRef('layout', 'default'); 
	  else
	  $view->layout =  'default';

	   $tp = self::getTemplatePath('category'); 
	   
	   if (!empty($tp))
	   {
		if (method_exists($view, 'addTemplatePath'))
		{
	      $view->addTemplatePath($tp); 
		}
		else
		{
			if (method_exists($view, 'addIncludePath'))
			{
			$view->addIncludePath($tp); 
			}
		}
		
		
	   }
	   
	   
	 
		
		
		ob_start(); 
		if (method_exists($view, 'loadYag')) {
		  $view->loadYag(); 
		}
		$html = $view->display();
		$html2 = ob_get_clean(); 
		
		
		
		//new: JRequest::setVar('virtuemart_category_id', $cat); 
		//new: JRequest::setVar( 'layout', $originallayout );
		
		 
	    //new:  JRequest::setVar('option', $oldoption); 
		$recursion = false; 
		$ret = $html.$html2.$html3; 
		return $ret; 
	}
	
	
	public static function getTemplatePath($viewName)
	{ 
	  if(!class_exists('shopFunctionsF'))require(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php');
	  if (method_exists('shopFunctionsF', 'loadVmTemplateStyle'))
	  {
	  //$template = shopFunctionsF::loadVmTemplateStyle(); 

	  }
	  //else
	  {
	  $vmtemplate = VmConfig::get('vmtemplate','default');
	  $vmtemplate = VmConfig::get('categorytemplate', $vmtemplate);

		if(($vmtemplate=='default') || (empty($vmtemplate))) {
			
				$q = 'SELECT `template` FROM `#__template_styles` WHERE `client_id`="0" AND `home` <> "0"';
			
			
			$db = JFactory::getDbo();
			$db->setQuery($q);
			$template = $db->loadResult(); 
			  
			
			
		} else {
			
			if (is_numeric($vmtemplate)) {
				$q = 'SELECT `template` FROM `#__template_styles` WHERE `id`="'.(int)$vmtemplate.'" ';
			    $db = JFactory::getDbo();
			    $db->setQuery($q);
			    $vmtemplate = $db->loadResult();
			}
			
			$template = $vmtemplate;
		}
	  }
	  
	  
	  
	  
	

		if($template){
			//$this->addTemplatePath(JPATH_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.$viewName);
			$tp = JPATH_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.$viewName;
			
			if (file_exists($tp)) return $tp; 
			}
			
			
		$tp2 = JPATH_VM_SITE.'/views/'.$viewName.'/tmpl'; 
		if (file_exists($tp2)) return $tp2; 

	
	}
	
}	

class EmptyDocument {
	function setMetadata($a1, $a2) {
	}
	function setDescription($a1) {
	}
	function setTitle($a1) {
	}
}

