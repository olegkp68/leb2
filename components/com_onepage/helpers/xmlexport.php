<?php
/**
 * Controller for the OPC ajax and checkout
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 */
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

class OPCXmlExport  {
 public static $classes; 
 public static $config; 
 public static $cats; 
 public static $maxreached;
 public static function addClass($className, $config, $xml, $entity='')
  {
		if (!class_exists('VmConfig')) {
		  
		  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		  VmConfig::loadConfig(); 

		}
	  
        if (!class_exists($className)) return; 
		$class = new $className($config, $xml); 
		$class->config = $config; 
		
		
		
		
		if (empty($entity))
		$entity = strtolower(substr($className, 0, -4)); 
		
		$xmlfile = $entity; 
		
		$default = array(); 
		//$ic = OPCconfig::getValue('tracking_config_pairing', 'custom_pairing', null, $default); 					  
		
	
		$class->entity = $entity; 
		$class->xml = $xml; 
		//$class->writer = new OPCwriter($config->xmlpath); 
		
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
		$class->general = new stdClass(); 
		$JModelXmlexport = new JModelXmlexport(); 
		$JModelXmlexport->getGeneral($class->general); 
		
		if (method_exists($class, 'setExportPath')) {
			$class->setExportPath(); 
		}
		
		$default = JURI::root(); 
		if (substr($default, -1) != '/') $default .= '/'; 
		$class->config->xml_live_site = OPCconfig::getValue('xmlexport_config', 'xml_live_site', 0, $default); 

		$live_site = JFactory::getConfig()->get('live_site');
		if (!empty($live_site)) 
		{
			
			$class->config->xml_live_site = $live_site; 
			
			
		}
		if (substr($class->config->xml_live_site, -1) !== '/') $class->config->xml_live_site .= '/'; 
		
		
		
		$class->params = $class->config; 
		if (empty(OPCXmlExport::$classes)) OPCXmlExport::$classes = array(); 
		OPCXmlExport::$classes[$className] = $class; 
		
  }
  
  public static function switchLang() {
	
  }
  
  
  public static function &prepareCats($langs)
  {
     return; 
    //vmrouterHelperSEFforOPC
	if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cats.php'))
	 {
	   include(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cats.php'); 
	   $ct = time(); 
	   if (($ct - $time) > 36000)
	   return $cats; 
	 }
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'com_virtuemart_helper.php'); 
	foreach ($langs as $lang)
	{
	 $clang = strtolower(str_replace('-', '_', $lang)); 
	 $query = array(); 
	 $query['langswitch'] = $clang; 
	 $helper = vmrouterHelperSEFforOPC::getInstance($query);
	 
	 $db = JFactory::getDBO(); 
	 $q = 'select virtuemart_category_id from #__virtuemart_categories'; 
	 $db->setQuery($q); 
	 $cats = $db->loadAssocList(); 
	 
	 $mycats[$clang] = array(); 
	 $helper->nostatic = true; 
	 foreach ($cats as $catrow)
	  {
	    $cat = $catrow['virtuemart_category_id']; 
	    $mycats[$clang][$cat] = array(); 
		$mycats[$clang][$cat]['cats'] = array(); 
		$mycats[$clang][$cat]['route'] = ''; 
     		 
	    $categoryRoute = $helper->getCategoryRoute($cat); 
		if (isset($categoryRoute->route))
					{
					$arr = explode('~/~', $categoryRoute->route);
					if (count($arr)>0)
					{
					$catname = ''; 
					foreach ($arr as $c)
					  {
					   $catname = $c; 
					   $catname = str_replace("\xc2\xa0", '', $catname);
					   $mycats[$clang][$cat]['cats'][] = trim($c);
					   $helper->catsOfCats[$query['virtuemart_category_id']][] = trim($c); 
					  }
					}
					else
					{
					$mycats[$clang][$cat]['cats'][] = trim($categoryRoute->route); 
					$helper->catsOfCats[$query['virtuemart_category_id']][] = trim($categoryRoute->route);
					}
					
					$mycats[$clang][$cat]['route'] = $categoryRoute->route; 
					 
					
					}
					
		
	   
	  }
	 
	 
	}
	jimport( 'joomla.filesystem.folder' );
	if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'))
	{
	  @JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'); 
	  $data = ''; 
	  @JFile::write(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'index.html', $data); 
	}
	if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'))
	{
	  $data = '<?php defined(\'_JEXEC\') or die(\'Restricted access.\');'."\n";
	  $data .= '$time = '.time().'; '."\n"; 
	  $data .= '$cats = '.var_export($mycats, true).';'; 
	  @JFile::write(JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cats.php', $data); 
	}
	 return $mycats; 
	
  }
  
  public static function cleardir($class2, $onlydirs=false) {
	  
	 
	 
	  require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
		$cnf = new stdClass(); 
		$JModelXmlexport = new JModelXmlexport(); 
		$JModelXmlexport->getGeneral($cnf); 
	  
	  
	  
	  if (empty($cnf->xml_export_path)) return; 
	  
	  
	  if (empty($class2)) return; 
	  
	  if (empty($class2->config->xmlfile)) return; 
	  
	  $name = $class2->config->xmlfile; 
	  
	  OPCXmlExport::log("OPC Export: clearing data of ".$class2->entity); 
	 
	  $path = JPATH_SITE.DIRECTORY_SEPARATOR.$cnf->xml_export_path; 
	  if (substr($path, -1, 1) == DIRECTORY_SEPARATOR) $path = substr($path, 0, -1); 
	  if (file_exists($path)) {
		  $files = JFolder::files($path, '.', false, true, array()); 
		  $folders = JFolder::folders($path, '.', false, true, array()); 
		  $files = array_merge($files, $folders); 
		   
		   
		   
		  if (!empty($files))
		  foreach ($files as $x) {
			  //$path.$name
			 // OPCXmlExport::log("OPC Export: searching ".$path.DIRECTORY_SEPARATOR.$name.' in '.$x); 
			  if (stripos($x, $path.DIRECTORY_SEPARATOR.$name)===0) {
				  if (is_dir($x)) {
					 try {
				    if (JFolder::delete($x)===false) {
						OPCXmlExport::log("OPC Export: Cannot delete ".$x); 
					}
					else 
					{
					OPCXmlExport::log("OPC Export: Deleted ".$x); 
					}
					 }
					 catch(Exception $e) {
						 OPCXmlExport::log("OPC Export: Cannot delete ".$x); 
					 }
					
				  }
				  elseif (empty($onlydirs)) {
					  
					  $testf = $path.DIRECTORY_SEPARATOR.$name; 
					  
					  if ($x === $testf) {
						  OPCXmlExport::log('To be removed later: '.$testf); 
						  continue; 
					  }
					try {
				   if (JFile::delete($x)===false) {
					   OPCXmlExport::log("OPC Export: Cannot delete ".$x); 
				   }
				   else {
					   OPCXmlExport::log("OPC Export: Deleted ".$x); 
				   }
					}
					catch(Exception $e) {
						OPCXmlExport::log("OPC Export: Cannot delete ".$x); 
					}
				  }
			  }
		  }
	  }
	
  }
  
  public static function doWork($langs)
  {
	  
    $allshg = array(); 
	$startmem = memory_get_usage(FALSE); 
	//echo '1:'.round(memory_get_usage(FALSE)/1024/1024).'Mb'.'<br />'; 
	
	
	
    if (!empty(OPCXmlExport::$classes))
	foreach (OPCXmlExport::$classes as $class2)
	{
	
	  $allshg[] = $class2->config->shopper_group; 
	  
	
	  
	   
	  
	}
	
	
	
	$allshg = array_unique($allshg); 
    
    if (empty(OPCXmlExport::$classes)) {
		// "OPC Export: No Class loaded. Please save config first."; 
		OPCXmlExport::log("OPC Export: No Class loaded. Please save config first."); 
		return true; 
	}
	
	/*
	$cleardir = JRequest::getInt('cleardir', false); 
	
	if ($cleardir) {
//		self::cleardir(); 
	}
	*/
	
	
	// zero step: 
    $from = JRequest::getInt('from', 1); 
	$to = JRequest::getInt('to', OPCXmlExport::$config->product_count); 
	/*
	if (empty($from) && (empty($to)) && ($compress != 2)) 
	{
	 self::$cats = OPCXmlExport::prepareCats($langs); 
	 return; 
	}
	*/
	
	// case 2, all exported at once: 
	/*
	if (($from == 1) && ($to >= OPCXmlExport::$config->product_count))
	self::$cats =& OPCXmlExport::prepareCats($langs); 
    */
	
	// last step: 
	
	

	 $from = JRequest::getInt('from', 0); 
	  if (($from === 0) && ($to === 0)) {
	    if (OPCXmlExport::compress()) {
			
		  OPCXmlExport::log("OPC Export: Last step finished."); 
		  OPCXmlExport::$maxreached = true; 
		  return true; 
		}
	  }
	  
	 
	 
	 // normal steps: 
	$from = JRequest::getInt('from', 0); 


	$finished = true; 
	if (!empty($to))
	{
	if ((($from === 0) && ($to >= 1)) || (($from === 1) && ($to > 1)))
	{

		
     OPCXmlExport::clear(); 
	 OPCXmlExport::open(); 
	 OPCXmlExport::startHeaders(); 
	 
	
	}
	
	
	
	//self::$cats =& OPCXmlExport::prepareCats($langs); 
	
	
	$cats = OPCXmlExport::getCategoryItems($langs); 
	$finished = OPCXmlExport::getItems($langs, $allshg); 
	
	
	
	}
	
	OPCXmlExport::$maxreached = $finished; 
	
	if ($finished) {
		OPCXmlExport::log("OPC Export: From ".$from.' to '.$to); 
	}
	
	
	
	if (($finished) && (($from === 0) && ($to === 0)))
	{
	  OPCXmlExport::log("OPC Export: Closing files"); 
	 
	  OPCXmlExport::endHeaders(); 
	  OPCXmlExport::close(); 
	  OPCXmlExport::group(); 
	  //in the next step: OPCXmlExport::compress(); 
	  
	}
	
	$endmem = memory_get_usage(FALSE); 
	$mem = $endmem-$startmem; 
	$mms = 'Mem: '.round($mem/1024/1024).'Mb';
	OPCXmlExport::log($mms); 
	
	return false; 
  }
  
  
  public static function doWorkCli($langs)
  {
	if (php_sapi_name() !== 'cli') die('Access denied - use CRON to access php directly!'); 
    if (!class_exists('cliHelper')) return; 
    
	$allshg = array(); 
	$startmem = memory_get_usage(FALSE); 
	$skipVm = 0; 
	OPCXmlExport::log('Starting with memory: '.round(memory_get_usage(FALSE)/1024/1024).'Mb'); 
    if (!empty(OPCXmlExport::$classes))
	foreach (OPCXmlExport::$classes as $class2)
	{
	  $allshg[] = (int)$class2->config->shopper_group; 
	  if (!isset($class2->config->skipvm)) $class2->config->skipvm = 0; 
	  $skipVm += (int)$class2->config->skipvm; 
	}
	
	if ($skipVm !== count(OPCXmlExport::$classes)) {
		foreach (OPCXmlExport::$classes as $k=>$class2)
		{
			OPCXmlExport::$classes[$k]->config->skipvm = 0; 
		}
		OPCXmlExport::$config->skipvm = false; 
	}
	else {
	  OPCXmlExport::$config->skipvm = true; 
	}
	
	$allshg = array_unique($allshg); 
    
    if (empty(OPCXmlExport::$classes)) {
		// "OPC Export: No Class loaded. Please save config first."; 
		OPCXmlExport::log("OPC Export: No Class loaded. Please save config first."); 
		return true; 
	}


	 $from = 0;
	 $finished = true; 
     OPCXmlExport::clear(); 
	 OPCXmlExport::open(); 
	 OPCXmlExport::startHeaders(); 
	 
	 $cats = OPCXmlExport::getCategoryItems($langs); 
	 $finished = OPCXmlExport::getItems($langs, $allshg); 
	 OPCXmlExport::$maxreached = $finished; 
	 
	 
	 OPCXmlExport::endHeaders(); 
	 OPCXmlExport::close(); 
	 OPCXmlExport::group(); 
	 OPCXmlExport::compress(); 
	 
	 OPCXmlExport::log("OPC Export Finished"); 
	 OPCXmlExport::log("OPC Export: Closing files"); 
	  
	
	
	$endmem = memory_get_usage(FALSE); 
	$mem = $endmem-$startmem; 
	$mms = 'Memory usage: '.round($mem/1024/1024).'Mb';
	OPCXmlExport::log($mms); 
	
	return true; 
  }
  
  public static function getLog() {
	  $ret = ''; 
	  $last = ''; 
	  if (!empty(self::$msgs)) {
	  foreach (self::$msgs as $msg) {
		  if ($msg !== '.') {
			  if ($last === '.') $ret .= "<br />\n"; 
			  $ret .= $msg."<br />\n"; 
		  }
		  else {
			  $ret .= '.'; 
		  }
		  $last = $msg; 
	  }
	  }
	  return $ret; 
  }
  static $msgs; 
  public static function log($msg) {
	  if (empty(self::$msgs)) self::$msgs = array(); 
	  
	  static $lastmsg; 
	  
	  if ((defined('OPCCLI')) && (class_exists('cliHelper'))) {
		    if (strlen($msg) === 1) {
			  if ((!empty($lastmsg)) && (strlen($lastmsg) !== 1)) {
				  //new line: 
				  cliHelper::debug($msg, false, false); 
			  }
			  else {
			    cliHelper::debug($msg, false); 
			  }
			}
			else {
				if ((!empty($lastmsg)) && (strlen($lastmsg) === 1)) {
				  cliHelper::debug($msg, true, true); 
				}
				else {
					cliHelper::debug($msg, true, false); 
				}
			}
	  }
      else {
		  if (strlen($msg) > 1) {
		    self::$msgs[] = $msg; 
		  }
      }
	  $lastmsg = $msg; 
  }
  
  private static function getCategoryItems($langs)
  {
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'com_virtuemart_helper.php'); 
	
	
	$cx = 0; 
	foreach (OPCXmlExport::$classes as $cl) {
		if (method_exists($cl, 'addCategoryItem')) $cx++; 
	}
	
	
	
	if ($cx === 0) {
		//xml exports do not export products !
		return true; 
	}
	
	 
	 $db = JFactory::getDBO(); 
	 $q = 'select c.* '; 
	 $join = ''; 
	 foreach ($langs as $lang) {
		 $lang = strtolower(str_replace('-', '_', $lang)); 
		 if (empty($lang)) continue; 
		 $q .= ', `#__virtuemart_categories_'.$lang.'`.`category_name` as `category_name_'.$lang.'` ';
		 $join .= ' left join `#__virtuemart_categories_'.$lang.'` on `#__virtuemart_categories_'.$lang.'`.virtuemart_category_id = c.virtuemart_category_id '; 
		 
	 }
	 $from = ' from #__virtuemart_categories as c  ';
	 $where = ' where c.published = 1 '; //and #__virtuemart_categories.virtuemart_category_id IN (select virtuemart_category_id from #__virtuemart_product_categories )'; 
	 $q = $q.$from.$join.$where; 
	 try {
	 $db->setQuery($q); 
	 $res = $db->loadAssocList(); 
	 }
	 catch(Exception $e) {
		 echo (string)$e; die(); 
	 }
	 
	foreach ($res as $category)

	{
	  OPCXmlExport::log('c'); 
      OPCXmlExport::addCategoryItem($category); 
	}
	
	
	return true; 
  }
  private static function getItems($langs, $shoppergroups)
  {
	  
	  
    require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'com_virtuemart_helper.php'); 
	
	
	$cx = 0; 
	foreach (OPCXmlExport::$classes as $cl) {
		if (method_exists($cl, 'addItem')) $cx++; 
	}
	if ($cx === 0) {
		//xml exports do not export products !
		return true; 
	}
	
	$from = JRequest::getInt('from', 0); 
	if ($from === 1) $from = 0; 
	
	$to = JRequest::getInt('to', (int)OPCXmlExport::$config->product_count); 
	
	
    $db = JFactory::getDBO(); 
	$lang = 'en_gb'; 
	//$q = 'select * from #__virtuemart_products as p inner join #__virtuemart_products_'.$lang.' as l on p.virtuemart_product_id = l.virtuemart_product_id and p.published = 1'; 
	
	$iter = 400; 
	$rounds = 0; 
	//for ($is = $from; $is<=$to; $is = $is + $iter + 1 )
	{
	$rounds++; 
	$q = 'select p.`virtuemart_product_id`, p.`published`, p.`modified_on`, p.`created_on`, p.`product_parent_id` ';
	
	if (defined('VM_VERSION') && (VM_VERSION >= 3))	
	{
	  $q .= ', p.`product_canon_category_id`'; 
	  $q .= ', pr.`product_canon_category_id` as parent_product_canon_category_id '; 
	  $q .= ', p.product_discontinued ';
	}
	$q .= ' from #__virtuemart_products as p'; // order by virtuemart_product_id asc '; 
	
	$q .= ' left join #__virtuemart_products as pr on ((p.product_parent_id = pr.virtuemart_product_id) and (p.product_parent_id > 0)) '; 
	
	
	$to = (int)$to; 
	$from = (int)$from; 
	$to2 = $to-$from+1; 
	$qa = ''; 
	
	
	if (!empty(OPCXmlExport::$config->xml_export_test))
	{
	   $products  = ''; 
	   $pp = array(); 
	   $z = explode(',', OPCXmlExport::$config->xml_export_test); 
	   if ((!empty($z)) && (is_array($z)))
	   {
		   foreach ($z as $v)
		   {
			   $pp2 = (int)$v; 
			   if (!empty($pp2)) $pp[] = $pp2;
		   }
		   if (!empty($pp)) $products = implode(',', $pp); 
	   }
	   else
	   {
		   $zi = (int)OPCXmlExport::$config->xml_export_test; 
		   if (!empty($zi)) $products = $zi; 
	   }
	   if (!empty($products))
	   {
		   $qa = ' and p.`virtuemart_product_id` IN ('.$products.')'; 
	   }
		   
	}
	
	
	if (!((empty($from) || ($from == 1)) && ($to == OPCXmlExport::$config->product_count)))
	{
	$q .= ' where p.`published` = 1 '; 
	
	
	
	
	
	//test: 
	//$q .= ' and virtuemart_product_id = 1848 '; 
	
	if (!empty($qa)) $q .= $qa; 
	
	$q .= ' order by p.`virtuemart_product_id` limit '.$from.', '.$to2; 
	}
	else
	{
	$q .= ' where p.`published` = 1 '; 



	if (!empty($qa)) $q .= $qa; 
	}
	//$q .= ' where p.virtuemart_product_id = 11083'; 
	//$ito =$is+$iter; 
	//$q .= ' limit '.$is.', '.$ito; 
	
	
	
	//echo $q.'<br />'; 
	
	
	
	$db->setQuery($q); 
	//echo '3:'.round(memory_get_usage(FALSE)/1024/1024).'Mb<br />'; 
	//$products = $db->loadObjectList(); 
	//$products = $db->loadResultArray(); 
	
	
	
	$products = $db->loadAssocList(); 
	
	
	
	OPCXmlExport::log('Loaded '.count($products).' products from the database'); 
	
	
	
	
	
	
	$productModel = OPCmini::getModel('product'); 
	
	OPCXmlExport::log('Loading VM objects for '.count($products).' products'); 
	
	
	
	if (empty($products)) return true; 
	foreach ($products as $n=>$row)
	{
	  $product_id = (int)$row['virtuemart_product_id']; 
	  if (empty(OPCXmlExport::$config->skipvm)) {
	  $product = self::getProductAndParent($product_id, $productModel, $shoppergroups, $langs, true, true, false); 
	  }
	  else {
		  
		  
		  $product = new stdClass(); 
		  $product->virtuemart_product_id = $product_id; 
		  $product->published = (int)$row['published']; 
		  $product->product_parent_id = (int)$row['product_parent_id']; 
		  $product->non_sef_link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product_id; 
		  
		  if ((empty($row['modified_on'])) || ($row['modified_on'] === '0000-00-00 00:00:00')) {
			  $product->modified_on = $row['created_on']; 
		  }
		  else {
			  $product->modified_on = $row['modified_on']; 
		  }
		  
		  if (!empty($row['product_canon_category_id'])) {
			  $product->virtuemart_category_id = (int)$row['product_canon_category_id'];
			  $product->product_canon_category_id = (int)$row['product_canon_category_id'];
		  }
		  elseif (!empty($row['parent_product_canon_category_id'])) {
			  $product->virtuemart_category_id = (int)$row['parent_product_canon_category_id'];
			  $product->product_canon_category_id = (int)$row['parent_product_canon_category_id'];
		  }
		  $product->categories = array(); 
		  if (!empty($product->product_canon_category_id)) {
			$product->non_sef_link .= '&virtuemart_category_id='.(int)$product->product_canon_category_id; 
			$product->categories = array($product->product_canon_category_id); 
		  }
		 
		  
		  $product->child_type = array(); 
		  $product->child_type[] = 1; 
		  $product->product_name = 'Skip VM product_id '.$product_id; 
		  if (!empty($product->product_parent_id)) $product->child_type[] = 2; 
		  if (empty($product->product_parent_id)) $product->child_type[] = 3; 
		  
		  
		  
		  
		  
	  }
	  
	  //OPCXmlExport::log('L'); 
	  //echo __LINE__.':'.round(memory_get_usage(FALSE)/1024)."Kb\n"; 
	  
	  
	  
	  
	  if (empty($product)) 
	  {
	  OPCXmlExport::log('x'); 
	  continue; 
	  }
	  if (empty($product->allPrices)) {
		  //do not export products without prices:
		OPCXmlExport::log('x'); 
		continue; 
	  }
	  $vm1 = array(); 
      OPCXmlExport::addItem($product, $vm1); 
	  
	  
	}
	}
	
	$count = count($products); 
	if ($count < ($to - $from)) return true; 
	if ($count < $to2) return true; 
	
	if ($to >= OPCXmlExport::$config->product_count) return true; 
	
	return false; 
  }
  
  
  public static function getProductAndParent($product_id, &$productModel=null, $shoppergroups=array(), $langs=array(), $front = TRUE, $withCalc = TRUE, $onlyPublished = TRUE, $quantity = 1,$customfields = TRUE,$virtuemart_shoppergroup_ids=0) {
	  
	  if (empty($productModel)) $productModel = VmModel::getModel('product'); 
	  if (empty($langs)) $langs = VmConfig::get('active_languages', array()); 
	  $product = self::getProduct($productModel, $shoppergroups, $langs, $product_id, true, true, false); 
	  
	  
	 
	  $product->product_parent_id = (int)$product->product_parent_id; 
	  
	   $arr = array('product_s_desc', 
	 'product_desc', 
	 'product_name', 
	 'prices', 
	 'categories', 
	 'product_gtin', 
	 'product_mpn', 
	 'product_weight', 
	 'product_weight_uom', 
	 'product_length', 
	 'product_width', 
	 'product_height', 
	 'product_lwh_uom', 
	 'product_in_stock', 
	 'product_availability', 
	 'product_available_date', 
	 'product_special', 
	 'product_unit', 
	 'product_packaging', 
	 'product_params',
	 'product_parent_id'
	 ); 
	  
	  $tcp = 0; 
	 //if (!empty($product->product_parent_id))
	 while($product->product_parent_id !== 0)
	 {
		 	if ($tcp > 3) {
				OPCXmlExport::log('OPC Export: too may parent relations '.$product->virtuemart_product_id.' -> '.$product->product_parent_id); 
			}
		 //recursion protection: 
		 $tcp++; 
		 if ($tcp > 10) break; 
		 
	 $pp = self::getProduct($productModel, $shoppergroups, $langs, $product->product_parent_id, true, true, false); 
	 if (!empty($pp)) {
	 /*
	 if (empty($product->categories))
	 {
		 
		 {
			 
			 if (!empty($pp->categories))
			 {
				 $product->categories = $pp->categories; 
			 }
			 
			 
			 
		 }
	 }
	 */
	 $product->parentProduct = $parent = $pp; 
	 
	
	 
	 if (!empty($langs)) {
	 foreach ($langs as $l)
	 foreach ($arr as $k=>$key)
	 {
		
		 
	  if (empty($product->$key) && (!empty($parent->$key)))
	  {
		 
		 $product->$key = $parent->$key; 
		 
		 
		
	  }	
		 $l = strtolower(str_replace('-', '_', $l)); 
		 $lkey = $l.'_'.$key; 
		 if (isset($parent->$lkey)) 
		 {
			 if (empty($product->$lkey))
			 {
				 $product->$lkey = $parent->$lkey; 
			 }
			 
		 }
		 
	  
	  
	 }
	 }
	 else
	 {
	  foreach ($arr as $k=>$key)
	 {
	  if (empty($product->$key) && (!empty($parent->$key)))
	  {
		 
		 $product->$key = $parent->$key; 
		 
		 
		
	  }
	 }	  
	 }
	 

	 $product->product_parent_id = (int)$pp->product_parent_id; 
	 
	 }
	 
	 
	 
	 
	 }
	 
	 return $product; 
  }
  
  
  public static function getPID($pidformat, $product_id, $product_sku, $lang, $prefix='', $suffix='')
   {
	   
	   $pidformat = (int)$pidformat; 
	   
	    
		 
		
			 
switch ($pidformat)
{
  case 0: 
   $pid = $product_id; 
   break;
  case 2: 
    $pid = $product_sku; 
	if (empty($pid))
    $pid = $product_id; 
	break; 
  case 3: 
    $pid = $product_sku; 
	if (empty($pid))
    $pid = $product_id; 
    
	
	$lang = str_replace('-', '_', $lang); 
	$lang = strtolower($lang); 
	
	$sku_prefix_key = 'sku_prefix_'.$lang; 
	$sku_suffix_key = 'sku_suffix_'.$lang; 
	
	
	
	if (!empty($prefix))
	{
		
		$pid = $prefix.$pid; 
	}

	if (!empty($suffix))
	{
		$pid = $pid.$suffix; 
	}

	
	
	 break; 
	 case 4: 
	 //ean
	    $product_id = (int)$product_id; 
	    static $cache4; 
		if (!isset($cache4[$product_id])) {
		$db = JFactory::getDBO(); 
		$q = 'select `product_gtin`, `product_mpn` from #__virtuemart_products where virtuemart_product_id = '.(int)$product_id; 
		$db->setQuery($q); 
		$data = $db->loadAssoc(); 
		if (empty($data)) $data = array(); 
		$data = (array)$data; 
		$cache4[$product_id] = $data; 
		}
		else {
			$data = $cache4[$product_id];
		}
		
		if (!empty($data['product_gtin'])) return $data['product_gtin']; 
		if (empty($data['product_gtin']) && (!empty($product_sku))) return $product_sku; 
		
		return (int)$product_id; 
		
		
	 
}
		return $pid; 
		 
		 
		
   }
  
  /**
	 * This function creates a product with the attributes of the parent.
	 *
	 * @param int     $virtuemart_product_id
	 * @param boolean $front for frontend use
	 * @param boolean $withCalc calculate prices?
	 * @param boolean published
	 * @param int quantity
	 * @param boolean load customfields
	 */
	public static function getProduct (&$productModel, $shg, $langs, $virtuemart_product_id = NULL, $front = TRUE, $withCalc = TRUE, $onlyPublished = TRUE, $quantity = 1,$customfields = TRUE,$virtuemart_shoppergroup_ids=0) {
	    
		if (!class_exists('VirtueMartCart'))
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php');
		
		$cart = VirtuemartCart::getCart(false); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'opctracking.php'); 
		if (empty($cart->vendorId)) $cart->vendorId = 1; 
		$vendor_data = OPCtrackingHelper::getVendorInfo($cart->vendorId); 
		if (!empty($vendor_data['virtuemart_country_id'])) {
		 $cart->BT = array(); 
		 $cart->BT['virtuemart_country_id'] = $vendor_data['virtuemart_country_id']; 
		}
		
		
		$productModel = new VirtueMartModelProduct(); 
		$productModel->starttime = microtime (TRUE); 
		if (isset($virtuemart_product_id)) {
			$virtuemart_product_id = $productModel->setId ($virtuemart_product_id);
			$parent_id = $virtuemart_product_id; 
		}
		else return false; 

		if($virtuemart_shoppergroup_ids !=0 and is_array($virtuemart_shoppergroup_ids)){
			$virtuemart_shoppergroup_idsString = implode('',$virtuemart_shoppergroup_ids);
		} else {
			$virtuemart_shoppergroup_idsString = $virtuemart_shoppergroup_ids;
		}


		$front = $front?TRUE:0;
		$withCalc = $withCalc?TRUE:0;
		$onlyPublished = $onlyPublished?TRUE:0;
		$customfields = $customfields?TRUE:0;
		$productModel->withRating = false; 

		if ($productModel->memory_limit<$mem = round(memory_get_usage(FALSE)/(1024*1024),2)) {
			$m = round(memory_get_usage(FALSE)/(1024*1024),2); 
			$strm = 'low memory.. '.$m.'Mb'; 
			OPCXmlExport::log($strm); 
			return false;
		}
		$child = self::getProductSingle ($productModel, $shg, $langs, $parent_id, $virtuemart_product_id, $front,$quantity,$customfields,$virtuemart_shoppergroup_ids);
		/*
		$child->product_desc = str_replace("\0", '', $child->product_desc); 
		$child->product_s_desc = str_replace("\0", '', $child->product_s_desc); 
		$child->product_name = str_replace("\0", '', $child->product_name); 
		*/
		
		if (empty($child->virtuemart_product_id))
		{
		  return false; 
		}
		
		
		
		if(!isset($child->orderable)){
			$child->orderable = TRUE;
		}
		//store the original parent id
		$pId = $child->virtuemart_product_id;
		$ppId = $child->product_parent_id;
		$published = $child->published;

		
		
		
		$i = 0;
		$canon_cat = 0; 
		$count = 0; 
		
		if (empty($canon_cat)) {
		if (!empty($child->product_canon_category_id)) {
				$canon_cat = (int)$child->product_canon_category_id;
				OPCtrackingHelper::updateProductCategory($child); 
			}
		}
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'opctracking.php'); 
		//Check for all attributes to inherited by parent products
		
		$child->link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.(int)$child->virtuemart_product_id; 
			if (!empty($canon_cat)) {
				$child->link .= '&virtuemart_category_id='.$canon_cat; 
			}
			
			foreach ($langs as $l) {
				$child->links[$l] = JRoute::_($child->link.'&lang='.$l); 
			}
		
		
		
		while (!empty($child->product_parent_id)) {
			$count++; 
			if ($count > 3) {
				OPCXmlExport::log('OPC Export: too may parent relations '.$child->virtuemart_product_id.' -> '.$child->product_parent_id); 
			}
			if ($count > 100) return false; 
			
			if (!isset($child->product_parent_id)) $child->product_parent_id = 0; 
			
			$parentProduct = self::getProductSingle ($productModel, $shg, $langs, $child->product_parent_id, $child->product_parent_id, $front,$quantity,$customfields,$virtuemart_shoppergroup_ids);
			
			
			if (!isset($parentProduct->product_parent_id)) $parentProduct->product_parent_id = 0; 
			
			if ($child->product_parent_id === $parentProduct->product_parent_id) {

				OPCXmlExport::log(__LINE__.': Error, parent product with virtuemart_product_id = '.$parentProduct->virtuemart_product_id.' has same parent id like the child with virtuemart_product_id '.$child->virtuemart_product_id);
				break;
			}
			
			if (empty($canon_cat)) {
			if  (!empty($parentProduct->product_canon_category_id)) {
				$canon_cat = (int)$parentProduct->product_canon_category_id; 
				OPCtrackingHelper::updateProductCategory($parentProduct); 
			}
			
			}
			
			
			
			$attribs = get_object_vars ($parentProduct);

			foreach ($attribs as $k=> $v) {
				if ('product_in_stock' != $k and 'product_ordered' != $k) {// Do not copy parent stock into child
					if (strpos ($k, '_') !== 0 and empty($child->$k)) {
						$child->$k = $v;
					
					}
				}
			}
			$i++;
			
			
			
			
			
			if ($child->product_parent_id != $parentProduct->product_parent_id) {
				  $child->product_parent_id = $parentProduct->product_parent_id;
			}
			else {
				$child->product_parent_id = 0;
			}
			
			$child->link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.(int)$child->virtuemart_product_id; 
			if (!empty($canon_cat)) {
				$child->link .= '&virtuemart_category_id='.$canon_cat; 
			}
			
			foreach ($langs as $l) {
				$child->links[$l] = JRoute::_($child->link.'&lang='.$l); 
			}
			
			
			
		}
		
		
		
		$child->published = $published;
		$child->virtuemart_product_id = $pId;
		$child->product_parent_id = $ppId;

		if ($withCalc) {
		
			//$child->prices = $productModel->getPrice ($child, array(), 1);
			if (!class_exists ('calculationHelper')) {
			   require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'calculationh.php');
			}
				$session = JFactory::getSession(); 
		

		
		if (!empty($shg)) {
			$saveda = $session->get('vm_shoppergroups_add',null,'vm');
			$savedr = $session->get('vm_shoppergroups_remove',null,'vm');
		
		foreach ($shg as $sh)
		{		
			// reset calculator: 
			$calculator = calculationHelper::getInstance ();
			$calculator::$_instance = null; 
			if (!empty($sh)) {
			  @$session->set('vm_shoppergroups_add',array($sh),'vm');
			}
			
			if (!empty($savedr))
			$session->set('vm_shoppergroups_remove',null,'vm');
			
			$calculator = calculationHelper::getInstance ();
		    // Calculate the modificator
		    //$variantPriceModification = $calculator->calculateModificators ($product, $customVariant);
				  
			

			$voidprices = $productModel->fillVoidPrice();
			if (empty($child->selectedPrice)) {
				$child->selectedPrice = 0; 
			}
			if (empty($child->allPrices)) {
				$child->allPrices = array(); 
				$child->allPrices[0] = $voidprices;
				
				
			}
			/*
			$db = JFactory::getDBO(); 
			$q = 'select * from #__virtuemart_product_prices where virtuemart_product_id = '.(int)$child->virtuemart_product_id.' and virtuemart_shoppergroup_id = '.(int)$sg.' and price_quantity_start <= 1 limit 1'; 
			
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			var_dump($res); die(); 
			*/
			
			if (empty($calculator->productPrices)) {
				$calculator->productPrices = $voidprices; 
			}
			$calculator->productPrices = array(); 
			self::getProductPrices($child,$quantity,array($sh),true, $productModel, $calculator);
			if (empty($child->allPrices)) continue; 
		    $child->priceshg[$sh] = $child->prices; 
	  


			$child->prices = $child->priceshg[$sh]; 
			
			$cart = VirtuemartCart::getCart(false); 
			$cart->BT = array(); 
			
			$calculator->_cart = $cart; 
			
			if (!empty($child->prices)) {
			if (isset($child->allPrices[$child->selectedPrice])) {
				//$child->allPrices[$child->selectedPrice] = $productModel->fillVoidPrice();
				$prices = $calculator->getProductPrices ($child, 0.0, $quantity);
			}
			else {
				$prices = array(); 
				$prices = $productModel->fillVoidPrice(); 
				$prices['priceBeforeTax'] = 0; 
				$prices['salesPrice'] = 0; 
			}
			}
			else {
				$prices = array(); 
			}
			
		    

			
			if (empty($child->allPrices)) {
				$child->allPrices = array(); 
			}
			$child->pricesCalc[$sh] = $prices; 
			
		   }
		     
			unset($child->prices); 
			if (!empty($saveda)) {
		     $session->set('vm_shoppergroups_add',$saveda,'vm');
			}
			if (!empty($savedr)) {
			 $session->set('vm_shoppergroups_remove',$savedr,'vm');
			}
		} 
		else {
			$calculator = calculationHelper::getInstance ();
			$prices = self::getProductPrices($child,$quantity,array(1),true, $productModel, $calculator);
			$child->pricesCalc[0] = $prices; 
			$child->pricesCalc[1] = $prices; 
		}
		}

		if (empty($child->product_template)) {
			//$child->product_template = VmConfig::get ('producttemplate');
		}


		if(!empty($child->canonCatLink)) {
			// Add the product link  for canonical
			$child->canonical = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $virtuemart_product_id . '&virtuemart_category_id=' . $child->canonCatLink;
		} else {
			$child->canonical = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $virtuemart_product_id;
		}
		
		if(!empty($child->virtuemart_category_id)) {
			$child->link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $virtuemart_product_id . '&virtuemart_category_id=' . $child->virtuemart_category_id;
		} else {
			$child->link = $child->canonical;
		}
		
		
			if (!empty($canon_cat)) {
				$child->link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.(int)$child->virtuemart_product_id; 
				$child->link .= '&virtuemart_category_id='.$canon_cat; 
				
			}
		$child->non_sef_link = $child->link; 
		$child->link = JRoute::_($child->link, false); 
		$child->canonical = JRoute::_ ($child->canonical,FALSE);
		$child->quantity = $quantity;

	
		return $child;
		


		
	}
	
	public static function getProductPrices(&$product, $quantity, $sh, $fe, &$productModel, &$calculator)
	{
	  if (!is_array($sh)) $sh = array($sh); 
	  
	  if (method_exists($productModel, 'getProductPrices'))
	  return $productModel->getProductPrices($product,$quantity,$sh,true);
	  
	  
	  $productModel->getRawProductPrices($product, $quantity, $sh, $fe, 0); 
	   if (!isset($product->selectedPrice))
	   $product->selectedPrice = -1; 
	   if (empty($product->allPrices)) return; 
	  
	  $product->prices = $calculator->getProductPrices ($product, 0.0, $quantity);
	  
	 
	}
	
	public static function getProductSingle ($productModel, $shg, $langs, $parent_id, $virtuemart_product_id = NULL, $front = TRUE, $quantity = 1,$customfields=TRUE,$virtuemart_shoppergroup_ids=0) {
       $db = JFactory::getDBO(); 
		//$productModel->fillVoidProduct($front);
		if (!empty($virtuemart_product_id)) {
			$virtuemart_product_id = $productModel->setId ($virtuemart_product_id);
		}

		if($virtuemart_shoppergroup_ids===0){
			$virtuemart_shoppergroup_ids = array(1); 
		}

		if($virtuemart_shoppergroup_ids !=null and is_array($virtuemart_shoppergroup_ids)){
			$virtuemart_shoppergroup_idsString = implode('',$virtuemart_shoppergroup_ids);
		} else {
			$virtuemart_shoppergroup_idsString = $virtuemart_shoppergroup_ids;
		}

		$front = $front?TRUE:0;
		$customfields = $customfields?TRUE:0;
		
		
		
		
		 
		
		
		if (!empty($parent_id)) {

		$q = 'select p.*, '; 
		$q .= ' (select pmf.virtuemart_manufacturer_id from #__virtuemart_product_manufacturers as pmf where pmf.virtuemart_product_id = p.virtuemart_product_id order by pmf.virtuemart_manufacturer_id desc limit 0,1 ) as mvirtuemart_manufacturer_id, '; 
		foreach ($langs as $lang)
		{
		 $lang = strtolower(str_replace('-', '_', $lang)); 
		 if (empty($lang)) continue; 
		 $q .= '(select '.$lang.'_l.product_s_desc from #__virtuemart_products_'.$lang.' as '.$lang.'_l where '.$lang.'_l.virtuemart_product_id = p.virtuemart_product_id) as '.$lang.'_product_s_desc, ';
		 $q .= '(select '.$lang.'_l.product_desc from #__virtuemart_products_'.$lang.' as '.$lang.'_l where '.$lang.'_l.virtuemart_product_id = p.virtuemart_product_id) as '.$lang.'_product_desc, ';
		 $q .= '(select '.$lang.'_l.product_name from #__virtuemart_products_'.$lang.' as '.$lang.'_l where '.$lang.'_l.virtuemart_product_id = p.virtuemart_product_id) as '.$lang.'_product_name, ';
		 
		 $q .= '(select '.$lang.'_l.mf_name from #__virtuemart_manufacturers_'.$lang.' as '.$lang.'_l where '.$lang.'_l.virtuemart_manufacturer_id = mvirtuemart_manufacturer_id) as '.$lang.'_mf_name, ';
		 
		 $q .= '(select '.$lang.'_l.mf_desc from #__virtuemart_manufacturers_'.$lang.' as '.$lang.'_l where '.$lang.'_l.virtuemart_manufacturer_id = mvirtuemart_manufacturer_id) as '.$lang.'_mf_desc, ';
		 
		}
		$q .= ' (select  GROUP_CONCAT(pcf.virtuemart_customfield_id) from  #__virtuemart_product_customfields as pcf where pcf.virtuemart_product_id = p.virtuemart_product_id) as virtuemart_custom_fields, ';
		$q .= ' (select  GROUP_CONCAT(pcf.virtuemart_custom_id) from  #__virtuemart_product_customfields as pcf where pcf.virtuemart_product_id = p.virtuemart_product_id) as virtuemart_custom_field_ids, ';
		
		$q .= ' (select  GROUP_CONCAT(media.virtuemart_media_id) from  #__virtuemart_product_medias as media where media.virtuemart_product_id = p.virtuemart_product_id) as virtuemart_product_medias, ';
		$q .= ' (select  GROUP_CONCAT(shoppergroups.virtuemart_shoppergroup_id) from  #__virtuemart_product_shoppergroups as shoppergroups where shoppergroups.virtuemart_product_id = p.virtuemart_product_id) as shg, ';
		$q .= ' (select  GROUP_CONCAT(cats.virtuemart_category_id) from  #__virtuemart_product_categories as cats where cats.virtuemart_product_id = p.virtuemart_product_id order by ordering asc) as cats, ';
		
		$q .= ' (select  GROUP_CONCAT(pp.virtuemart_product_id) from  #__virtuemart_products as pp where pp.product_parent_id = p.virtuemart_product_id) as children ';
		
		$q .= ' from #__virtuemart_products as p';
		
		//$q .= ' LEFT JOIN `#__virtuemart_product_manufacturers` as mf on `#__virtuemart_product_manufacturers`.`virtuemart_product_id`= p.`virtuemart_product_id` '; 
		$q .= ' WHERE p.`virtuemart_product_id` = "'.$parent_id.'" limit 0,1'; 
		
		$db->setQuery($q); 
		$product = $db->loadObject(); 
		
		if (!empty($product))
		foreach ($langs as $lang)
		{
			$lang = strtolower(str_replace('-', '_', $lang)); 
			$product->{$lang.'_product_desc'} = str_replace("\0", '', $product->{$lang.'_product_desc'}); 
			$product->{$lang.'_product_s_desc'} = str_replace("\0", '', $product->{$lang.'_product_s_desc'}); 
			$product->{$lang.'_product_name'} = str_replace("\0", '', $product->{$lang.'_product_name'}); 
			$product->{$lang.'_mf_desc'} = str_replace("\0", '', $product->{$lang.'_mf_desc'}); 
		}
		
		
		
		
		
		
		if (empty($product)) return new stdClass(); 
		
		$kname = $lang.'_product_name'; 
		$product->product_name = $product->$kname; 
		
		
		
		$product->virtuemart_manufacturer_id = $product->mvirtuemart_manufacturer_id;
		
		
	
		$product->child_type = array(); 
		/*
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT1="Include both child and parent products"
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT2="Include only child products and products without child products (skip parent products)"
		COM_ONEPAGE_XML_CHILDPRODUCTS_HANDLING_OPT3="Include only parent products"

		*/
		$product->child_type[] = 1; 
		if (!empty($product->product_parent_id)) $product->child_type[] = 2; 
		
		// has children, ie is parent: 
		if (!empty($product->children) && (empty($product->product_parent_id))) $product->child_type[] = 3; 
		// does not have children and is not a child product (it's parent product)
		if (empty($product->children) && (empty($product->product_parent_id))) $product->child_type[] = 3;
		// is parent with no children, same as above
		if ((empty($product->product_parent_id)) && (empty($product->children))) $product->child_type[] = 2; 
		// is child and does not have subchildren: 
		if ((!empty($product->product_parent_id)) && (empty($product->children))) $product->child_type[] = 2; 
		
		
		
		
		// to object: 

		
			
		 if (empty($product->virtuemart_manufacturer_id)) $product->virtuemart_manufacturer_id = ''; 
		 /*
			if (!empty($product->virtuemart_manufacturer_id)) {
				$mfTable = $productModel->getTable ('manufacturers');
				$mfTable->load ((int)$product->virtuemart_manufacturer_id);
				
				foreach ($mfTable as $key=>$mf)
				{
				  $product->$key = $mf; 
				}
				
			}
			else {
			   //$product = (object)$product; 
				$product->virtuemart_manufacturer_id = array();
				$product->mf_name = '';
				$product->mf_desc = '';
				$product->mf_url = '';
			}
			*/
			
			
			$medias = explode(',', $product->virtuemart_product_medias); 
			if (is_array($medias))
			{
			  $product->virtuemart_media_id = $medias; 
			}
			else
			  $product->virtuemart_media_id = array($product->virtuemart_product_medias); 

			// shopper groups handling
			if (empty($product->shg)) $product->shoppergroups = array(); 
			else
			{
			$sh = explode(',', $product->shg); 
		    if (is_array($sh))
		    $product->shoppergroups = $sh; 
		    else $product->shoppergroups = array($product->shg); 
			}
		 
			
			// Load the categories the product is in
			if (empty($product->cats)) $product->categories = array(); 
			else
			{
			 $ar = explode(',', $product->cats); 
			 if (is_array($ar))
			 $product->categories = $ar; 
			 else 
			 $product->categories = array($product->cats); 
			}
			
			//if (!empty($product->categories))
		    //$product->virtuemart_category_id = $product->categories[0];	
			
		

			
			
				if (!empty(self::$config->xml_export_customs))
				{
					$customfieldModel = VmModel::getModel ('Customfields');
					if (method_exists($customfieldModel, 'getproductCustomslist'))
					{
					$product->customfields = $customfieldModel->getproductCustomslist ($productModel->_id);

					if (empty($product->customfields) and !empty($product->product_parent_id)) {
						
						$product->customfields = $customfieldModel->getproductCustomslist ($product->product_parent_id, $productModel->_id);
						$product->customfields_fromParent = TRUE;
					}
					}
					
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'renderer.php'); 
					OPCrenderer::filterCustomFields($product); 
					
				}
			
			 
			
			
			
			{


				
				// Fix the product packaging
				if ($product->product_packaging) {
					$product->packaging = $product->product_packaging & 0xFFFF;
					$product->box = ($product->product_packaging >> 16) & 0xFFFF;
				}
				else {
					$product->packaging = '';
					$product->box = '';
				}

				// set the custom variants
				
				if (!empty(self::$config->xml_export_customs))
				if (!empty($product->virtuemart_custom_fields )) {

					$customfieldModel = VmModel::getModel ('Customfields');
					if (method_exists($customfieldModel, 'getProductCustomsField'))
					{
					// Load the custom product fields
					$product->customfields = $customfieldModel->getProductCustomsField ($product);
					$product->customfieldsRelatedCategories = $customfieldModel->getProductCustomsFieldRelatedCategories ($product);
					$product->customfieldsRelatedProducts = $customfieldModel->getProductCustomsFieldRelatedProducts ($product);
					//  custom product fields for add to cart
					$product->customfieldsCart = $customfieldModel->getProductCustomsFieldCart ($product);
					$child = $productModel->getProductChilds ($productModel->_id);
					$product->customsChilds = $customfieldModel->getProductCustomsChilds ($child, $productModel->_id);
					}
				}

				// Check the stock level
				if (empty($product->product_in_stock)) {
					$product->product_in_stock = 0;
				}
			}
			
			return $product; 
			
		}
		else {
			return $productModel->fillVoidProduct ($front);
			
		}
		return false; 
	}

  
  
  public static function updateProduct(&$product, &$class, &$vm1)
  {
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	  
	  OPCmini::toObject($product); 
	  if (empty($product->allPrices)) return; 
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php'); 
	  	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'com_virtuemart_helper.php'); 
	  
	   
	  $lang = strtolower(str_replace('-', '_', $class->config->language)); 
	  
	  if (isset($product->{$lang.'_product_s_desc'})) {
		  $product->product_s_desc = $product->{$lang.'_product_s_desc'}; 
	  }
	  if (isset($product->{$lang.'_product_desc'})) {
		  $product->product_desc = $product->{$lang.'_product_desc'}; 
	  }
	  if (isset($product->{$lang.'_product_name'})) {
		  $product->product_name = $product->{$lang.'_product_name'}; 
	  }
	  
	  
	  $params = explode('|', $product->product_params);
	  
				$tx = new stdClass(); 
				foreach ($params as $line) {
		
					$item = explode('=', $line);
					if (count($item) !== 2) continue; 
					$key = $item[0];
					$val = $item[1]; 
					
					$item = json_decode($val);
					
					
					if (!is_null($item)) {
						$tx->{$key} = $item;
					}
					
					

				}
				
				
				foreach ($tx as $k=>$v) {
					$product->{$k} = $v; 
				}
	  
	  if ($class->config->stock_calc === 3) {
		  
		 JPluginHelper::importPlugin('vmpayment');
		 JPluginHelper::importPlugin('vmshipment');
		 JPluginHelper::importPlugin('vmcustom');
		 $dispatcher = JEventDispatcher::getInstance();
		 $dispatcher->trigger('plgUpdateProductObject', array(&$product)); 
		 
		 
	  }
	  
	  if (!empty($product->product_discontinued)) {
		  $product->product_in_stock = 0; 
	  }
	  
	  if ($class->config->stock_calc === 2) {
		  $product->product_in_stock = (int)$product->product_in_stock; 
		  
		  if ($product->product_in_stock <= 0) {
			  $product->product_in_stock = 1; 
		  }
	  }
	  
	  
      $lang = $class->config->language; 
	$a = explode('-', $lang); 
	
	$langkey = strtolower(str_replace('-', '_', $lang)); 
	$lang = strtolower($a[0]); 
	
	
	
	  $vm1 = array(); 
	  $vm1['atr'] = ''; 
	  $vm1['product_id'] = $product->virtuemart_product_id; 
	  if (empty($product->product_sku)) $product->product_sku = $product->virtuemart_product_id; 
	  $vm1['sku'] = $product->product_sku ;
	  
	  $key = $langkey.'_product_name'; 
	  if (isset($product->$key)) {
	  $vm1['product_name'] = $product->$key ; 
	  $product->product_name = $vm1['product_name']; 
	  }
	  if (isset($product->$key)) {
	   $key = $langkey.'_mf_name'; 
	   $vm1['manufacturer_name'] = $product->$key ; 
	  
	   $vm1['manufacturer'] = $vm1['manufacturer_name']; 
	   $vm1['mf_name'] = $product->$key ; 
	    $product->mf_name = $vm1['mf_name']; 
	  }
	 
	  $key = $langkey.'_mf_desc'; 
	  if (isset($product->$key )) {
	  $vm1['mf_desc'] = $product->$key ; 
	  $product->mf_desc = $vm1['mf_desc']; 
	  }
	  
	  if (isset($product->product_gtin)) {
		  $vm1['ean'] = $product->product_gtin; 
	  }
	  else {
		  $vm1['ean'] = ''; 
	  }
	  
	  $ida = $product->virtuemart_media_id; 
	  if (is_array($ida)) $id = reset($ida);
	  else $id = $ida; 
	  
	  if (empty($id))
	  {
		  if (!empty($product->parentProduct))
		  {
			  if (!empty($product->parentProduct->virtuemart_media_id))
			  {
				  $ida = $product->parentProduct->virtuemart_media_id; 
				  if (is_array($ida)) $id = reset($ida);
				  else $id = $ida; 
				  
				
			  }
		  }
	  }
	  
	  
	  
	  $img = OPCImage::getMediaData($id);
	  
	  $site = OPCXmlExport::$config->xml_live_site; 
	  
	  if (is_array($product->virtuemart_manufacturer_id))
	  {
	    $vm1['manufacturer_id'] = reset($product->virtuemart_manufacturer_id); 
	  }
	  else
	  $vm1['manufacturer_id'] = 0; 
	  
	  
	 
	  
	  if (!empty($img['file_url']))
	  {
		  
		  
	   if (stripos($img['file_url'], 'http')===false)
	   {
		
				
				
				$file_url = $img['file_url'];
				
				$test1 = $img['file_url']; 
		   
		$vm1['thumb_url'] = $site.$img['file_url']; 
	   }
	   else
	   {
	   $vm1['thumb_url'] = $img['file_url']; 
	   }
      
	  }
	  else $vm1['thumb_url'] = ''; 
	  
	  $product->imagePaths = array(); 
	  $product->imagePaths[] = $vm1['thumb_url']; 
	  
	  if (!empty($ida))
	  {
	    foreach ($ida as $im)
		 {
		   $img = OPCImage::getMediaData($id);
		   if (!empty($img['file_url']))
		    {
			  $product->imagePaths[] = OPCXmlExport::getLink('', $vm1['thumb_url']); 
			}
		 }
	  }
	 
 
	  //$vm1['thumb_url'] = $img->file_url; 
	  $vm1['fullimg'] = $vm1['thumb_url']; 
	  $key = $langkey.'_product_s_desc'; 
	  
	  $vm1['desc'] =& $product->$key; 
	  $product->product_s_desc =& $vm1['desc']; 
	  $key = $langkey.'_product_desc'; 
	  $vm1['fulldesc'] =& $product->$key; 
	  $product->fulldesc =& $vm1['fulldesc']; 
	  
	  
  
  
    if (!empty($class->config->cname))
    $utm = 'utm_source='.$class->config->cname; 
    else
	$utm = ''; 

	
  
    if ($class->config->url_type == 1)
	$product->url = OPCXmlExport::getLink('', $product->link); 
	else
	if ($class->config->url_type == 2)
	$product->url = OPCXmlExport::getLink('', $product->link, $utm); 
	else
	if ($class->config->url_type == 3)
	$product->url = OPCXmlExport::getLink('', 'index.php?option=com_virtuemart&lang='.$lang.'&view=productdetails&virtuemart_product_id='.(int)$product->virtuemart_product_id, $utm); 
	else
	$product->url = OPCXmlExport::getLink('', 'index.php?option=com_onepage&view=redirect&virtuemart_product_id='.(int)$product->virtuemart_product_id.'&nosef=1&tmpl=component&format=opchtml', $utm); 
	
	
	
	$vm1['link'] = $product->url; 
	$vm1['node_link'] = OPCXmlExport::getLink('','index.php?option=com_virtuemart&lang='.$lang.'&view=productdetails&virtuemart_product_id='.(int)$product->virtuemart_product_id, $utm); 
	
	if (!isset($product->pricesCalc) && (isset($product->prices))) {
	   $product->pricesCalc[1] = $product->prices; 
	}
	
	if (isset($product->pricesCalc[$class->config->shopper_group])) {
	$vm1['cena_s_dph'] = $product->pricesCalc[$class->config->shopper_group]['salesPrice']; 
	$product->prices = $product->pricesCalc[$class->config->shopper_group]; 
	
	if (!empty($product->prices['discountedPriceWithoutTax']))
	$vm1['cena_txt'] = $product->pricesCalc[$class->config->shopper_group]['discountedPriceWithoutTax']; 
	else
	$vm1['cena_txt'] = $product->pricesCalc[$class->config->shopper_group]['priceBeforeTax']; 
	$product->prices = $product->pricesCalc[$class->config->shopper_group]; 
	
	}
	
	$vm1['tax_rate'] = OPCXmlExport::getTaxRate($product, $class->config); 
	
	$vm1['avai_obr'] = $product->product_availability; 
	
	if (empty($product->product_availability))
	{
		if (isset($class->config->avaitext)) {
			$vm1['avaitext'] = $class->config->avaitext;
			$vm1['avaidays'] = $class->config->avaidays;
		}
		else {
			$vm1['avaitext'] = '';
			$vm1['avaidays'] = 1;
			
		}
	}
	else
	{
	  $img = $product->product_availability; 
	  $pattern = '/[^\w]+/'; //'[^a-zA-Z\s]'; 
	  $key = preg_replace( $pattern, '_', $img ); 
	  
	  
	  if (isset($class->config->{$key.'txt'}))
	  $vm1['avaitext'] = $class->config->{$key.'txt'}; 
	  else
	  $vm1['avaitext'] = $product->product_availability; 
	  
	  if (isset($class->config->{$key.'days'}))
	  $vm1['avaidays'] = $class->config->{$key.'days'}; 
	  else
	  $vm1['avaidays'] = $product->product_availability; 
	  
	}
	
	
	
	$lang = $class->config->language; 
	$lang = strtolower(str_replace('-', '_', $lang)); 
	 
	 $vm1['cats'] = array(); 
	  $cats = array(); 
	  
	  
	  if (!empty($product->categories))
	  foreach ($product->categories as $cat)
	  {
	  	//
	
	
	 $clang = strtolower(str_replace('-', '_', $lang)); 
	 $query = array(); 
	 $qeury['langswitch'] = $clang; 
	 $helper = vmrouterHelperSEFforOPC::getInstance($query);
		 
	      $categoryRoute = $helper->getCategoryRoute($cat); 
		  if (isset($categoryRoute->route))
					{
					$arr = explode('~/~', $categoryRoute->route);
					if (count($arr)>0)
					{
					$catname = ''; 
					foreach ($arr as $c)
					  {
					   $catname = $c; 
					   $catname = str_replace("\xc2\xa0", '', $catname);
					   $mycats[$clang][$cat]['cats'][] = trim($c);
					   $helper->catsOfCats[$cat][] = trim($c); 
					  }
					}
					else
					{
					$mycats[$clang][$cat]['cats'][] = trim($categoryRoute->route); 
					$helper->catsOfCats[$query['virtuemart_category_id']][] = trim($categoryRoute->route);
					}
					
					$mycats[$clang][$cat]['route'] = $categoryRoute->route; 
					 
					
					}
					
		
	   
	     $vm1['cats'][$cat] = $mycats[$clang][$cat]['cats'];
		 
		 
	  }
	 
	 
	
	$count = 0; 
	$sk = -1; 
	$l = 0; 
	foreach ($vm1['cats'] as $key=>$cat)
	{
	  
	  if (count($cat)>$count)
	  {
	  $sk = $key; 
	  $count = count($cat); 
	  $l = implode(' ', $cat); 
	  }
	  if (count($cat)==$count)
	  {
	    $l2 = implode(' ', $cat); 
		if ($l2 > $l) 
		{
		  $count = count($cat); 
		  $l=$l2; 
		  $sk = $key; 
		}
	  }
	}
	
	$vm1['longest_cat_id'] = 0; 
	
	if ($sk >= 0)
	{
	 $vm1['longest_cats'] = $vm1['cats'][$sk]; 
	 $vm1['longest_cat_id'] = $sk; 
	}
	if (!isset($vm1['longest_cats'])) $vm1['longest_cats'] = array(); 
	
	$vm1['published'] = (bool)$product->published; 
	$vm1['attribs'] = array(); 
	//if (empty($vm1['manufacturer']))
	{
	   if (!empty($product->virtuemart_custom_field_ids))
	   if (!empty($product->virtuemart_custom_fields))
	   {
	     $a = explode(',',$product->virtuemart_custom_field_ids); 
		 $a2 = explode(',',$product->virtuemart_custom_fields);
		 if (count($a) == count($a2))
		  {
		   
		     // attribs: 
			 if (!defined('VM_VERSION') || (VM_VERSION < 3))
			 {
			  $custom_field_col = 'custom_value'; 
			 }
			 else
			 {
				 $custom_field_col = 'customfield_value'; 
			 }
			
			 $q = 'select cs.custom_title, cf.'.$custom_field_col.', cf.virtuemart_customfield_id, cf.virtuemart_custom_id from #__virtuemart_customs as cs, #__virtuemart_product_customfields as cf where cs.virtuemart_custom_id = cf.virtuemart_custom_id and cf.virtuemart_customfield_id IN ('.$product->virtuemart_custom_fields.') '; 
			 $db = JFactory::getDBO(); 
			 $db->setQuery($q); 
			 $res = $db->loadAssocList(); 
			 if (!empty($res))
			 {
			 $vm1['attribs'] = $res; 
			 if (empty($product->virtuemart_customfields))
			 {
				
				 foreach ($res as $kkx=>$rr) {
				 if ((!isset($rr['customfield_value'])) && (isset($rr['custom_value']))) {
					 
					 $res[$kkx]['customfield_value'] = $rr['custom_value']; 
				 }
				 }
				  $product->virtuemart_customs = $res; 
				 
			 }
			 }
		  }
	   }
	}
	

	 
	
  }
  
  public static function getBillTaxes()
  {
    static $result; 
	if (!empty($result)) return $result; 
    $db = JFactory::getDBO(); 
	$null = $db->getNullDate();
    $jnow = JFactory::getDate();
    if (method_exists($jnow, 'toMySQL'))
    $now = $jnow->toMySQL();
    else $now = $jnow->toSQL(); 

	$q = 'select c.*, ';
	$q .= ' (select  GROUP_CONCAT(cat.virtuemart_category_id) from  #__virtuemart_calc_categories as cat where cat.virtuemart_calc_id = c.virtuemart_calc_id) as categories, ';
	//$q .= ' (select  GROUP_CONCAT(st.virtuemart_state_id) from  #__virtuemart_calc_states as st where st.virtuemart_calc_id = c.virtuemart_calc_id) as states, ';
	$q .= ' (select  GROUP_CONCAT(mf.virtuemart_manufacturer_id) from  #__virtuemart_calc_manufacturers as mf where mf.virtuemart_calc_id = c.virtuemart_calc_id) as mafucaturers, ';
	//$q .= ' (select  GROUP_CONCAT(co.virtuemart_country_id) from  #__virtuemart_calc_countries as co where co.virtuemart_calc_id = c.virtuemart_calc_id) as countries ';
	$q .= ' (select  GROUP_CONCAT(sg.virtuemart_shoppergroup_id) from  #__virtuemart_calc_shoppergroups as sg where sg.virtuemart_calc_id = c.virtuemart_calc_id) as shoppergroups ';
	
	$q .= ' from #__virtuemart_calcs as c '; 
	$q .= ' where c.published = 1 and c.calc_kind = \'TaxBill\' and c.calc_value_mathop = \'+%\' '; 
	$q .= ' AND ( c.publish_up = "' . $db->escape($null) . '" OR c.publish_up <= "' . $db->escape($now) . '" ) '; 
	$q .= ' AND ( c.publish_down = "' . $db->escape($null) . '" OR c.publish_down >= "' . $db->escape($now) . '" ) ';
	
	
	
	
	//$Q .= ' GROUP BY c.virtuemart_calc_id '; 
	
	//$q .= ', GROUP_CONCAT(IFNULL(st.virtuemart_state_id, \'\')) '; 
	//$q .= ', IFNULL(GROUP_CONCAT(co.virtuemart_country_id), 0) as countries, IFNULL(GROUP_CONCAT(mf.virtuemart_manufacturer_id), 0) as manufs, IFNULL(GROUP_CONCAT(cat.virtuemart_category_id), 0) as cats '; 
	//$q .= ', GROUP_CONCAT(st.virtuemart_state_id), GROUP_CONCAT(co.virtuemart_country_id), GROUP_CONCAT(mf.virtuemart_manufacturer_id), GROUP_CONCAT(cat.virtuemart_category_id) '; 	
	//$q .= 'left outer join #__virtuemart_calc_categories as cat using(virtuemart_calc_id) '; 
	//$q .= 'left outer join #__virtuemart_calc_manufacturers as mf using(virtuemart_calc_id)'; 
	//$q .= 'left outer join #__virtuemart_calc_countries as co using(virtuemart_calc_id)'; 
	//$q .= 'left outer join #__virtuemart_calc_states as st using(virtuemart_calc_id)'; 
	
	//
	$db->setQuery($q); 
	$res = $db->loadAssocList(); 
	$result = $res; 
	return $res; 
	
  }
  
  public static function getTaxRate(&$product, &$config)
  {
	if (empty($product->prices)) return (double)0; 
    $prices = $product->prices; 
	if (empty($prices)) return 0; 
    $taxes = array('DBTax', 'Tax', 'VatTax', 'DATax'); 
    $taxes = array('DBTax', 'Tax', 'VatTax', 'DATax'); 
    foreach ($prices as $key=>$val)
     {
	   
	   if (!empty($val))
	   if (is_array($val))
	   {
	   foreach ($val as $atax)
	   if (in_array($key, $taxes))
	    {
		   
		   if ($atax[2]=='+%')
		    {
			  return (double)$atax[1]; 
			}
		}
	   }
	 }
	 

	$billtax = OPCXmlExport::getBillTaxes(); 
    foreach ($billtax as $tax)
	 {
	    $match0 = $match1 = $match2 = false; 
		
	    if (!empty($tax['categories']))
		{
	    $cats = explode(',', $tax['categories']); 
		
		if (!empty($cats))
		{
		  foreach ($product->categories as $cat_id)
		   {
		      if (in_array($cat_id, $cats)) 
			  $match0 = (double)$tax['calc_value']; 
		   }
		}
		else
		$match0 = (double)$tax['calc_value']; 
		}
		else
		$match0 = (double)$tax['calc_value']; 
		
		if (!empty($tax['manufacturers']))
		{
		$msfs = explode(',', $tax['manufacturers']); 
		if (!empty($msfs))
		{
		   if (in_array($product->virtuemart_manufacturer_id, $msfs)) 
		   $match1 = (double)$tax['calc_value'];
		   
		   
		}
		else 
	    $match1 = (double)$tax['calc_value'];
		
		}
		else 
	    $match1 = (double)$tax['calc_value'];
		
		if (!empty($tax['shoppergroups']))
		{
		$sgs = explode(',', $tax['shoppergroups']); 
		if (!empty($sgs))
		  {
		     if (in_array($config->shopper_group, $sgs))
			 $match2 = (double)$tax['calc_value'];
		  }
		  else
		  $match2 = (double)$tax['calc_value'];
		
		}
		else
		$match2 = (double)$tax['calc_value'];
		
		if (!empty($match1))
		if (!empty($match2))
		if (!empty($match0))
		return $match1;
		
		
	 }
	return (double)0; 
	 
  }
  
  public static function getLink($domain='', $path, $merge='')
  {
    if (!empty($merge))
	{
	  if (strpos($path, '?')===false) $path .= '?'.$merge;
	  else $path .= '&'.$merge; 
	}
  

  
    if (strpos($path, 'http')===0) return $path; 
    if ($domain == '') $domain = OPCXmlExport::$config->xml_live_site; 
	
	
	if (substr($domain, -1) === '/') $domain = substr($domain, 0, -1); 
	if (substr($path, 0, 1) === '/') $path = substr($path, 1); 
	
	$t1 = explode('/', $domain); 
	$t2 = explode('/', $path); 
	if ((count($t1)>1) && (count($t2)>1)) {
	 $e1 = $t1[count($t1)-1]; 
	 $e2 = $t2[0]; 
	 
	
	 
	 if ($e1 === $e2) {
	   unset($t2[0]); 
	   $path = implode('/', $t2); 
	 }
	}
	
	
	
	return $domain.'/'.$path; 
  }
  
   public static function checkGeneric($config, $product)
  {
	  
	$config->stock_calc = (int)$config->stock_calc; 
	  
	  if (!empty($config->in_stock_only) || ($config->stock_calc === 2))
	  {
		  
		  
		  
	  
	  if ($config->stock_calc === 2) {
		  $product->product_in_stock = (int)$product->product_in_stock;
		  if ($product->product_in_stock <= 0) {
			  $product->product_in_stock = 1; 
		  }
	  }
	  else
	  if ($config->stock_calc === 1) {
	    $x1 = (float)$product->product_in_stock; 
		$x2 = abs((float)$product->product_ordered); 
		$z = $x1 - $x2; 
		if ($z <= 0) {
			OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") Empty or negative stock after products ordered !"); 
			return false; 
		}
	  }
	  
	  if (empty($product->product_in_stock)) {
			  OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") empty stock !"); 
			  return false; 
		  }
	  
	  }
	  
	  
	  
	  if (isset($config->only_category_filter)) {
	  $ea = OPCmini::parseCommas($config->only_category_filter); 
	  if (!empty($ea))
	  {
	   if (empty($product->categories))   {
		   OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") No categories !"); 
		   return false; 
	   }
	   if (!empty($product->categories))
	   foreach ($product->categories as $c)
	   {
		    $c = (int)$c; 
			if (!isset($ea[$c])) {
				OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") Product not in category ".$c." !"); 
				return false; 
			}
			
	   }
	  }
	  }
	  if (isset($config->not_category_filter)) {
		$ea = OPCmini::parseCommas($config->not_category_filter); 
	  
	 
	  if (!empty($ea))
	  {
	   if (empty($product->categories)) {
		   if (isset($ea[0])) {
			   OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") Empty categories while 'not in category' enabled !"); 
			   return false; 
		   }
		   
	   }
	   if (!empty($product->categories))
	   foreach ($product->categories as $c)
	   {
		    $c = (int)$c; 
			
			if (isset($ea[$c])) {
				OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") Product skipped as it is in filtered category ".$c." !"); 
				return false; 
				
			}
			
	   }
	   
	  }
	  }
	  if (isset($config->ignored_products)) {
	 if (!empty($config->ignored_products)) {
	  $eax = OPCmini::parseCommas($config->ignored_products); 
	  
	  if (isset($eax[$product->virtuemart_product_id])) {
		  OPCXmlExport::log("OPC Export Generic Product Check: ".$product->product_name."(".$product->virtuemart_product_id.") Product skipped !"); 
		  return false; 
	  }
	 }
	  }
	  
	  
	  return true; 
  }
  
  public static function addItem($product, $vm1m)
  {
  
  // internal error:
  if (empty($product->virtuemart_product_id )) 
  {
	 OPCXmlExport::log('Skipping product - empty ID '.var_export($vm1m, true)); 
     return; 
  }
  
  static $x; 
  if (empty($x)) $x = 0; 
  $x++; 
  
      foreach (OPCXmlExport::$classes as $class)
	  {
		
		  
	     if (!self::checkGeneric($class->config, $product))
		 {
			 OPCXmlExport::log('Skipping product '.$product->product_name.'('.$product->virtuemart_product_id.') - general check not passed'); 
			 continue; 
		 }
	     if (method_exists($class, 'addItem'))
		 {
			 
			   if (!isset($class->writer)) {
				$class->writer = new OPCwriter($class->config->xmlpath); 
				}
			 
			 
			
			 
		  // check the shopper group:
		  if (empty($product->shoppergroups) || (in_array($class->config->shopper_group, $product->shoppergroups)))
		  {
			  
			  if (!isset($class->config->child_type)) {
				 $class->config->child_type = '1'; 
			  }
			  
		  if (($class->config->child_type === '1') || (in_array($class->config->child_type, $product->child_type)))
		  {
		   $product2 = $product; 
		   if (empty(OPCXmlExport::$config->skipvm)) {
			OPCXmlExport::updateProduct($product2, $class, $vm1); 
		   }
		   else {
			   $vm1 = array(); 
		   }
		   
		  $product2->is_override = false; 
		   
		   //get pairing info: 
		   //if (!empty($product->categories))
		   if (!empty($vm1['cats']))
		   {
		   //$product2->paired_category_name = reset($vm1['cats']);
		   $deepestcat = $vm1['longest_cats'][count($vm1['longest_cats'])-1]; 
		   $product2->paired_category_name = $deepestcat; 
		   
		   if (!empty($vm1['longest_cat_id']))
		     {	
				// take the first: 
				 $cat_id = $vm1['longest_cat_id']; //$product->categories[0]; 
				 $default = new stdClass(); 
				 $entity = $class->entity;
				 $res = OPCconfig::getValue('xmlexport_pairing', $entity, $cat_id, $default);
				
				if ($res === $default)
				{
					$sorted = $vm1['cats']; 
					$map = array_map('count', $vm1['cats']); 
					arsort($map); 
				
				
				
				
					foreach ($map as $k=>$v)
					{
						$res = OPCconfig::getValue('xmlexport_pairing', $entity, $k, $default);
						if ($res !== $default)
						{
							if ((!empty($res)) && (!empty($res->txt)))
							{
							 $product2->paired_category_name = $res->txt; 
							 $product2->pairedObj = $res; 
							 $product2->is_override = true; 
							 break;
							}
						}
					}
				
			
				}
				else
				{
					if ((!empty($res)) && (!empty($res->txt)))
					{
						$product2->paired_category_name = $res->txt; 
						$product2->is_override = true; 
						
					}
					$product2->pairedObj = $res; 
				}
				
			}
			}
			else
			{
				
			  $product2->paired_category_name = '';
			}
			
			
		   if (empty($product->product_name)) {
			   
			   
			   
			 OPCXmlExport::log('Skipping product '.$product->product_name.'('.$product->virtuemart_product_id.') - empty product name'); 
		     continue;   
		   }
		   
		   $ret = $class->addItem($product2, $vm1); 
		   
		    $class->writer->write($ret); 
			
		  
		  }
		  else
		  {
			  OPCXmlExport::log('c'); 
		  }
		  }
		  else {
			  OPCXmlExport::log('s'); 
		  }
		 }
	  }

  }
  
  
  public static function addCategoryItem($category)
  {
  

      foreach (OPCXmlExport::$classes as $class)
	  {
	    
	     if (method_exists($class, 'addCategoryItem'))
		 {
		   if (!isset($class->writer)) {
		   $class->writer = new OPCwriter($class->config->xmlpath); 
		   }
		   
		   $copy = $category; 
		   $lang = $class->config->language; 
		   $langkey = strtolower(str_replace('-', '_', $lang)); 
		   $copy['category_name'] = $copy['category_name_'.$langkey];
		   $ret = $class->addCategoryItem($copy); 
		   $class->writer->write($ret); 
		  }
	  } 

  }
  
  
  public static function clear()
  {
	  
    	
	
    foreach (OPCXmlExport::$classes as $class)
	  {
		  
		 
		 
		 
		 OPCXmlExport::cleardir($class); 
		 
		 
	     if (method_exists($class, 'clear'))
		 {
			//$class->writer = new OPCwriter($class->config->xmlpath);
		    $ret = $class->clear(); 
			//$class->writer->write($ret); 
		 }
	  }
  }
  
  
  public static function open() {
	  foreach (OPCXmlExport::$classes as $class)
	  {
		   if (!isset($class->writer)) {
				$class->writer = new OPCwriter($class->config->xmlpath); 
		   }
		 
		 $ret = ''; 
	     if (method_exists($class, 'open'))
		 {
		  $ret = $class->open(); 
		  
		 }
		 $class->writer->open(); 
		 $class->writer->write($ret); 
	  }
  }
  
  public static function startHeaders()
  {
	  
	 
    foreach (OPCXmlExport::$classes as $class)
	  {
		   if (!isset($class->writer)) {
		 $class->writer = new OPCwriter($class->config->xmlpath); 
		   }
		 
	     if (method_exists($class, 'startHeaders'))
		 {
		  $ret = $class->startHeaders(); 
		  
		  $class->writer->write($ret); 
		  
		
		 }
	  }
  }
  
  public static function endHeaders()
  {
    foreach (OPCXmlExport::$classes as $class)
	  {
	     if (method_exists($class, 'endHeaders'))
		 {
			 if (!isset($class->writer)) {
				$class->writer = new OPCwriter($class->config->xmlpath); 
			  }
			 
		  $ret = $class->endHeaders(); 
		  
		  $class->writer->write($ret); 
		  
		 }
	  }
  }
  
  public static function close()
  {
    foreach (OPCXmlExport::$classes as $class)
	  {
	     if (method_exists($class, 'close'))
		 {
		  if (!isset($class->writer)) {
		   $class->writer = new OPCwriter($class->config->xmlpath); 
		  }
			 
			 
		  $ret = $class->close(); 
		  
		  $class->writer->write($ret); 
		  
		 }
		 $class->writer->close(); 
	  }
	  
	
	  
	  
	 
  }
  
  //this is the last function, if any of XML's are not finished, this will return false and the fucntion will be repeated
  public static function compress()
  {
	
	  $x1 = 0; 
	  $c = count(OPCXmlExport::$classes); 
	  
	  $xml_export_disable_compression = OPCconfig::getValue('xmlexport_config', 'xml_export_disable_compression', 0, false); 
	  
	  
	  foreach (OPCXmlExport::$classes as $class)
	  {
		
		if (!isset($class->writer)) {
				$class->writer = new OPCwriter($class->config->xmlpath); 
				}
		  
	   if ($class->writer->isFinished()) {
		 
		 if (empty($xml_export_disable_compression)) {
		  
	         $x1 += $class->writer->compress();
		  
		   
		   
		    
		 }
		 else {
			 OPCXmlExport::log("OPC Export: compression disabled"); 
			 $x1++; 
		 }
		 
		 
		 
		}
		else {
			OPCXmlExport::log("OPC Export: export not finished"); 
			continue;
		}
	  }
	  
	  if ($x1 === $c) {
		   foreach (OPCXmlExport::$classes as $class) {
			   
			   OPCXmlExport::log("OPC Export: compression finished ".$x1.' of '.$c); 
			   
			   OPCXmlExport::cleardir($class, true); 
			   
			   if (method_exists($class, 'finalize')) {
				   //move files to root dir if needed for sitemap
				   $class->finalize(); 
			   }
		   }
		  return true; 
	  }
	 
	  
	  return false; 
  }
  
  public static function group() {
	  foreach (OPCXmlExport::$classes as $class)
	  {
	    $class->writer->group(); 
	  }
  }
  
  

}

class OPCWriter {
 public $file = ''; 
 public $suffix = '.tmp'; 
 private $method = 0; 
 private $buffer = ''; 
 private $block = true; 
 public function __construct($file)
 {
    if (empty($file)) {
		die('z'); 
	}
    $this->file = JPATH_SITE.DIRECTORY_SEPARATOR.$file; 
	/*$this->suffix = '.tmp'; 
	//if (file_exists($this->file.$this->suffix)) 
	*/
	
	
		
		
		$filekey = JRequest::getInt('filekey', 0); 
		
		$folder = $this->file.'_temp_'.$filekey.DIRECTORY_SEPARATOR; 
		clearstatcache(true, $folder); 
		
		if (!file_exists($folder)) {
			jimport( 'joomla.filesystem.folder' );
			JFolder::create($folder); 
		}
		$timeX = microtime(true) * 100000; 
		$timeX = (int)$timeX; 
		$newname = $timeX.'.tmp'; 
		$newname = str_pad($newname, 20, '0', STR_PAD_LEFT); 
		
		$this->folder = $folder; 
		$this->suffix = '_temp_'.$filekey.DIRECTORY_SEPARATOR.$newname; 
		//$this->suffix = $stamp.'.tmp'; 
	
	$this->block = false; 
	// check method: 
	$data = ''; 
	$this->buffer = ''; 
	
	$from = JRequest::getInt('from', 1); 
	$this->method = 2; 
	
	
	
	if ($from == 1)
	{

	$res = @file_put_contents($this->file.$this->suffix, $data, LOCK_EX); 
	if ($res === false)
	{
	  	  $res = @JFile::write($this->file.$this->suffix, $data); 
		  if ($res === false)
		  {
		   $this->method = 0; 
		  }
		  else
		  {
		   $this->method = 1; 
		  }
	}
	else
	{  
		$this->method = 2; 
	}
	}
	
 }
 
 public function compress()
 {
      if (isset($this->isCompressed)) return $this->isCompressed; 
	  clearstatcache(true, $this->folder.'compressed.tmp'); 
	  if (file_exists($this->folder.'compressed.tmp')) {
	  
	    OPCXmlExport::log('File already compressed '.$this->file.' as with '.$this->folder.'compressed.tmp'); 
	  
		return 1; 
	  }
	
	clearstatcache(true, $this->file); 
	if (file_exists($this->file)) {
	OPCXmlExport::log('Compressing '.$this->file.'...'); 
	  try {
      if (JFile::delete($this->file.'.gz')) {
	   $ret = $this->gzcompressfile($this->file, 9); 
	 }
	 else {
		 OPCXmlExport::log('Compression - cannot remove: '.$this->file.'.gz'); 
		 return 0; 
	 }
	  }
	  catch (Exception $e) {
		  OPCXmlExport::log('Compression - cannot remove: '.$this->file.'.gz'); 
	  }
	}
	else {
		OPCXmlExport::log('Compression - file not found: '.$this->file.'...'); 
		return 0; 
	}
	
	 $endfile = ''; 
	 //we might had removed it with close()
	 if (file_exists($this->folder)) {
		JFile::write($this->folder.'compressed.tmp', $endfile); 
	 }
	
	return 1; 
 }
 
//http://www.php.net/manual/en/function.gzwrite.php
private function gzcompressfile($source,$level=false){ 
    $start = time(); 
    $dest=$source.'.gz'; 
	
    $mode='wb'.$level; 
    $error=false; 
    if($fp_out=gzopen($dest,$mode)){ 
        if($fp_in=fopen($source,'rb')){ 
            while(!feof($fp_in)) 
			{
                gzwrite($fp_out,fread($fp_in,1024*512)); 
				$time2 = time(); 
				// stAn: if time is over 10 seconds, let's rather close, delete the file and return
				if (($time2 - $start) > 10)
				 {
				   fclose($fp_in); 
				   gzclose($fp_out); 
				   unlink($dest); 
				   OPCXmlExport::log('Deleting '.$dest); 
				   return -1; 
				 }
			}
            fclose($fp_in); 
            } 
          else $error=true; 
        gzclose($fp_out); 
        } 
      else $error=true; 
    if($error) return -2; 
      else return $dest; 
    } 

 
 public function open()
 {
	 
   OPCXmlExport::log('Opening '.$this->file.$this->suffix.'... '); 
   
   $res = false; 
   $data = ''; 
   if ($this->method === 2)
	$res = @file_put_contents($this->file.$this->suffix, $data, LOCK_EX); 
	
	if ($this->method === 1)
	$res = @JFile::write($this->file.$this->suffix, $data); 
   
   if ($res === false)
   {
     $this->block = true; 
   }
   
   return $res; 
 }
 public function close()
 {
   if ($this->isFinished()) return; 
   if (!empty($this->isFinished)) return; 
   OPCXmlExport::log( 'closing '.$this->file.$this->suffix.'... '); 
    
   if ($this->block) return; 
   if ($this->method === 1) {
     JFile::write($this->file.$this->suffix, $this->buffer); 
   }
   $file = $this->file; //substr($this->file, 0, -4); 
   
   
   $endfile = ''; 
   JFile::write($this->folder.'finished.tmp', $endfile); 
   
   
   $this->buffer = ''; 
   $this->isFinished = true; 
 }
 public function isFinished() {
	if (isset($this->isFinished)) return $this->isFinished; 
	clearstatcache(true, $this->folder.'finished.tmp'); 
	if (file_exists($this->folder.'finished.tmp')) {
		return true; 
	}
	return false; 
 }	 
 public function group() {
	
	clearstatcache(false); 
	clearstatcache(true); 
	
	if ($this->isFinished()) {
	jimport( 'joomla.filesystem.folder' );
	
	$path = $this->folder; 
	if (substr($path, -1, 1) == DIRECTORY_SEPARATOR) $path = substr($path, 0, -1); 
	
	$files = JFolder::files($path, 'tmp', false, true); 
	if (empty($files)) {
		OPCXmlExport::log("OPC Export: cannot find temp files"); 
	}
	
	sort($files); 
	
	
	if (!empty($files)) {
	
	
	$fh = fopen($this->file, 'w+');
	if (empty($fh)) {
		OPCXmlExport::log("OPC Export: cannot open file for writing ".$this->file); 
	}
	$bytes = 0; 
	foreach ($files as $file) {
		if (strpos($file, 'finished.tmp') !== false) {
			continue; 
		}
		//Get all the matches from the file
        $fileContents = file_get_contents($file);
		$fileContents = trim($fileContents); 
		$bytesF = strlen($fileContents); 
		$bytes += $bytesF;
		
		OPCXmlExport::log("OPC Export: grouping file in folder ".$file.' into '.$this->file.' bytes '.$bytesF); 
		fputs($fh, $fileContents);
		
		JFile::delete($file); 
	}
	
	 fclose($fh);
	 
	 OPCXmlExport::log("OPC Export: written ".$bytes.' to '.$this->file); 
	}
	else {
		
	}
	
	
	}
	else {
		 OPCXmlExport::log("OPC Export: finished.tmp does not exists ! ".$this->folder.'finished.tmp'); 
	}
	
	if (file_exists($this->folder) && (is_dir($this->folder))) {
		JFolder::delete($this->folder); 
	}
	 
 }
 
 
 public function write($data)
 {
   
    OPCXmlExport::log('.'); 
	
	
	
    if (empty($data)) return; 
	
	
	if ($this->block) return; 
	
    if ($this->method === 2)
	{
	clearstatcache(true, $this->file.$this->suffix); 
	if (!file_exists($this->file.$this->suffix))
	{
		//echo 'creating a new file: '.$this->file; 
	 OPCXmlExport::log("OPC Export: creating new file ".$this->file.$this->suffix.' datalength '.strlen($data)); 
	 file_put_contents($this->file.$this->suffix, $data, LOCK_EX ); 
	 
	}
	else
	{
	 file_put_contents($this->file.$this->suffix, $data, FILE_APPEND | LOCK_EX); 
	}
	}
	
	
	
	
	if ($this->method === 1)
	$this->buffer .= $data; 
	
 }
 
}