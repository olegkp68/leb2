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

jimport('joomla.application.component.controller');



class VirtueMartControllerXmlexport extends OPCController {
  
  
  var $enabled = false; 
  
  public function __construct() {
	parent::__construct();
	if (function_exists('ini_set')) {
	  @ini_set('memory_limit', '32G');
	  @ini_set('max_execution_time', 0); 
	  @ini_set('max_input_time', -1); 
	  
	}	
	if (function_exists('set_time_limit')) {
	  @set_time_limit(0);
	}
	
	if (function_exists('ignore_user_abort')) @ignore_user_abort(true); 
	
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
    $enabled = OPCconfig::getValue('xmlexport_config', 'xml_general_enable', 0, false); 
	//if (empty($enabled)) die('XML Export not enabled'); 
	$this->enabled = $enabled; 

	
  }
  
  
  
  // when used from URL this will return a JSON object of a particular product to be used by javascript parsers
  // when used as a function from other code, this will genarate json-ld format for Google or other purposes
  public function getproduct($product_id=0, $display=true, $withExtras=true, $pidformat=0, $pid_prefix='', $pid_suffix='', $desc_type='product_desc', $withRating=true) 
  {
	  if ($display) {
			@header('Content-Type: text/html; charset=utf-8');
			@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	  }
	 
	  $productData = array();
	  
	  if (empty($product_id)) {
	  session_write_close();
	  
	  
	  $product_id = JRequest::getInt('virtuemart_product_id', 0); 
	  }
  
	  if (!empty($product_id)) {
		  
		  
		  
		  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
		  
		  if (empty($pidformat)) $pidformat = JRequest::getInt('pidformat', 0); 
		  if (empty($pid_prefix)) $pid_prefix = JRequest::getVar('pid_prefix', ''); 
		  if (empty($pid_suffix)) $pid_suffix = JRequest::getVar('pid_suffix', ''); 
		  
		  
		  
		  $product = OPCXmlExport::getProductAndParent($product_id); 
		  $lang = JFactory::getLanguage()->getTag(); 
		  $class = new stdClass(); 
		  $class->config = new stdClass(); 
		  $class->config->language = $lang; 
		  $class->config->cname = 'json-ld'; 
		  $class->config->url_type = 1; 
		  $class->config->shopper_group = 0; 
		  $class->config->avaitext = ''; 
		  $class->config->avaidays = 0; 
		  $vm1 = array(); 
		  
		  
		  if (empty(OPCXmlExport::$config)) {
			OPCXmlExport::$config = new stdClass();
			$root = Juri::root(); 
			if (substr($root, -1) !== '/') $root .= '/'; 
		    OPCXmlExport::$config->xml_live_site = $root; 
		  }
		  
		   OPCXmlExport::updateProduct($product, $class, $vm1); 
		    
		   
		   
		   $product2 = VmModel::getModel('product')->getProduct($product_id); 
		   
			   if (!empty($product2->prices)) {
				   
				   $prices = $product2->prices; 
				   $prices = (array)$prices; 
				
				    $db = JFactory::getDBO(); 
					$q = 'select currency_code_2, currency_code_3, currency_name from #__virtuemart_currencies where virtuemart_currency_id = '.(int)$prices['product_currency'].' limit 1'; 
					$db->setQuery($q); 
					$currency_info = $db->loadAssoc(); 
					
			   
		   $product_sku = $product->product_sku; 
		  
		   $productPID = OPCXmlExport::getPID($pidformat, $product_id, $product_sku, $lang, $pid_prefix, $pid_suffix); 
		   
		   
		   
		   $productData['@context'] = 'http://schema.org/'; 
		   $productData['@type'] = 'Product'; 
		   $productData['image'] = $vm1['fullimg']; 
		   if (isset($vm1['product_name'])) {
		    $productData['name'] = $vm1['product_name']; 
		   }
		   else {
		    $productData['name'] = $product->product_name; 
		   }
		   if ($desc_type === 'product_desc') {
				$productData['description'] = $vm1['fulldesc']; 
		   }
		   else {
			   $productData['description'] = $vm1['desc']; 
		   }
		   
		   
		   $offer = array(); 
		   if (!empty($prices) && (!empty($currency_info))) {
		   $offer['@type'] = 'offer'; 
		   $offer['priceCurrency'] = $currency_info['currency_code_3']; 
		   $offer['price'] = $prices['salesPrice'];
		   }
		   
		   $withRating = true; 
		   if (!empty($withRating)) {
			   
			   if (!empty($product2->rating)) {
				   
				   $ratingModel = VmModel::getModel('Ratings');
				   $productrating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
				   $productratingcount = isset($productrating->ratingcount) ? $productrating->ratingcount:'';
				   
				   $rating = array(); 
				   $rating['@type'] = 'AggregateRating'; 
				   $rating['ratingValue'] = $product2->rating; 
				   $rating['ratingCount'] = $productratingcount; 
				   $productData['aggregateRating'] = $rating; 
			   }
			   
		   }
		   
		   $productData['offers'] = $offer; 
		   
		   
		   
		   if (empty($product->category_name) && (!empty($product2->category_name))) {
			   $product->category_name = $product2->category_name; 
			   $product->virtuemart_category_id = $product2->virtuemart_category_id; 
		   }
		   if ($desc_type === 'product_desc') {
		   if (empty($productData['description']) && (!empty($product->product_desc))) {
			   $productData['description'] = $product->product_desc;
		   }
		   }
		   else {
			   if (empty($productData['description']) && (!empty($product->product_s_desc))) {
			   $productData['description'] = $product->product_s_desc;
		   }
		   }
		   
		   if (empty($product->category_name)) $product->category_name = ''; 
		   if (empty($product->virtuemart_category_id)) $product->virtuemart_category_id = 0; 
		   
		  if ($withExtras) {
		   $productData['productPID'] = $productPID;
		   $productData['productID'] =  $product->virtuemart_product_id;
		   $productData['productCategory'] = $product->category_name;
           $productData['productCategoryID'] = $product->virtuemart_category_id;
		   $productData['productPrice'] = $prices['salesPrice'];
		   $productData['productCurrency_currency_code_2'] = $currency_info['currency_code_2'];
		   $productData['productCurrency_currency_code_3'] = $currency_info['currency_code_3'];
		   $productData['productCurrency_currency_name'] = $currency_info['currency_name'];
		  }
		   
		  
		   
		  }
	  }
		  
		   if ($display) {
			@header('Content-Type: text/html; charset=utf-8');
			@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			
			 if (defined('JSON_PRETTY_PRINT')) {
		       echo json_encode($productData, JSON_PRETTY_PRINT); 
			 }
			 else {
				 echo json_encode($productData); 
			 }
			  
			 JFactory::getApplication()->close(); 
		   }
		   return $productData; 
	  
  }
  
  
  public function getList() {
	  $this->getListForEntity(); 
  }
  
  public function getListForEntity($entity='', $returnData=false) {
	  if (!class_exists('xmlCategory')) {
		  require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlcategory.php'); 
		}
		
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'pairing.php'); 
	  //JModelPairing
		
		$JModelPairing = new JModelPairing; 
		if (empty($entity)) {
			$entity = JRequest::getVar('entity', ''); 
		}
		
		if (empty($entity)) return; 
		
	if (!empty($entity)) {
	   $class = $this->getClass($entity); 
	   
	   
	   if (empty($class)) {
		   return; 
	   }
	   
	    if (method_exists($class, 'getPairingUrl'))
	  {
	    $url = $class->getPairingUrl(); 
	 
	  }
	  else
	   {
	      if (isset($class->xml->category_pairing_url))
		   {
		      $url = (string)$class->xml->category_pairing_url; 
		   }
		   else {
			   
		     return; 
		   }
	   }
	  
	 
	 
	  if (method_exists($class, 'getPairingName'))
	  $name = $class->getPairingName(); 
	  else
	   {
	     if (isset($class->xml->category_pairing_name))
		  $name = (string)$class->xml->category_pairing_name; 
		 else 
		  $name = $entity; 
	   }
	   
	   
	 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
	 
	 
	 
	  $res = OPCloader::fetchUrl($url); 
	  
	  
	  if (empty($res)) return; 
	  
	   $converted = array(); 
	  
	  if (method_exists($class, 'processPairingData'))
	  $data = $class->processPairingData($res, $converted); 
  
  if (empty($converted))
	  {
	  foreach ($data->children as $topcat)
	   {
	      $converted[$topcat->id] = $topcat->txt; 
		  
		  if (!empty($topcat->children))
		   {
		     $JModelPairing->recurseCat($topcat->children, $converted[$topcat->id], $converted); 
		   }
		  
		  
	   }
	  }
	  
	  
	  
      if (empty($converted)) return; 
	  if ($returnData) return $converted; 
	  
      $type = JRequest::getVar('type', 'json'); 
	  
	  $name = JFile::makeSafe($name);
	  if ($type === 'json') {
      header('Content-Type: application/json');
	  $store = JRequest::getVar('store'); 
	  $json = json_encode($converted); 
	  if (!empty($store)) {
		 jimport('joomla.filesystem.file');
		 
		 $x = true; 
		 if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'data')) {
		    $x = JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'data'); 
			
		 }
		 if ($x !== false)
	     JFile::write(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$name.'.json', $json); 
	  }
	  echo $json;
	  }
	  elseif ($type === 'csv') {
		  header('Content-type: text/csv');
		  header('Content-disposition: attachment;filename='.$name.'.csv');
		  
		  foreach ($converted as $id=>$c) {
			  echo $id.'::'.$c."\n"; 
		  }
		  
	  }
	  elseif ($type === 'xls') {
		  
		  require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'pairing.php'); 
		  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		  if ($this->_getPHPExcel()) {
		  $JModelPairing = new JModelPairing(); 
		  //$vmData = $JModelPairing->xmlexportData($entity, 'virtuemart_category_id'); 
		  $vmData = $JModelPairing->getVmCats(); 
		  $data = $JModelPairing->reOrderCats($vmData); 
		  //$vmData = $data; 
		 
		  $this->downloadXLS($converted, $vmData, $name, $entity); 
		  }
	  }
	   
	}
	JFactory::getApplication()->close(); 
	
  }
  
  public function getAffiliateDataConverted($entity) {
	  
  }
  
  
  private function downloadXLS($converted, $vmData, $fn, $entity) {
	  // Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("RuposTel Systems")
							 ->setLastModifiedBy("RuposTel Systems")
							 ->setTitle("OPC Product Export Pairing")
							 ->setSubject("OPC")
							 ->setDescription("OPC")
							 ->setKeywords("orders, virtuemart, eshop")
							 ->setCategory("Products");
		
		
		
			 
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->setTitle('Category Pairing');
		$objWorkSheet = $objPHPExcel->createSheet(1);
		$objWorkSheet->setTitle('Affliate_Categories');
		//$objPHPExcel->addSheet($objWorkSheet);
		
		
		$header_done = false; 
		
		$has_data = array(); 
	
		
		/*
		
	  
	  
	  
	  $ret = '<option value="'.$refCat.'" '; 
	  
	  if (!empty($res))
	  if (isset($res->id))
	  if ($res->id == $refCat)
	  $ret .= ' selected="selected" '; 
	*/
			$rown_n = 2;
			foreach ($converted as $remote_id=>$remote_name) {
				$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(1, $rown_n, $remote_id);
			
			$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(0, $rown_n, $remote_name);
			 
			 $rown_n++; 
			}
			
			$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(1, $rown_n, 0);
			
			$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(0, $rown_n, '=\'');
			 
			$maxRows = $rown_n;
			
			$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(1, 1, 'Affiliate Category ID');
			
			$objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow(0, 1, 'Affiliate Category Name');
			 
			 
			 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(0, 1, 'Virtuemart Category ID');
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(1, 1, 'Virtuemart Category Name');
			 
			 
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(2, 1, 'Affiliate Category Name');
	
		$i = 0; 
		$rown_n = 2;
		if (!empty($vmData))
		foreach ($vmData as $category_id=>$category_name) {
			
			
			
			
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(0, $rown_n, $category_id);
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(1, $rown_n, $category_name);
			
			
			$default = new stdClass(); 
			$default->id = 0; 
			$res = OPCconfig::getValue('xmlexport_pairing', $entity, $category_id, $default); 
			
			if ($res !== $default) {
				if (isset($converted[$res->id])) {
				$txt = $converted[$res->id];
				
				$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(2, $rown_n, $txt);
				
				}
			}
			$txt = '=VLOOKUP(C'.$rown_n.',\'Affliate_Categories\'!$A$2:$B$'.$maxRows.',2,0)';
			//=VLOOKUP(C2;$Affliate_Categories.A2:B10000;2;0)
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(3, $rown_n, $txt);
			
			$objValidation2 = $objPHPExcel->setActiveSheetIndex(0)->getCell('C'.$rown_n)->getDataValidation();
			$objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
			$objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
			$objValidation2->setAllowBlank(false);
			$objValidation2->setShowInputMessage(true);
			$objValidation2->setShowDropDown(true);
			$objValidation2->setPromptTitle('Pick from list');
			$objValidation2->setPrompt('Please pick a category from the drop-down list.');
			$objValidation2->setErrorTitle('Input error');
			$objValidation2->setError('Category is not in list');
			$objValidation2->setFormula1('=\'Affliate_Categories\'!A2:A'.$maxRows);
			
			
			$rown_n++;
			
			
			
			
		}
		
		
		
		
	$objPHPExcel->setActiveSheetIndex(0);	

	$objPHPExcel->getActiveSheet()->getColumnDimension(0)
        ->setAutoSize(true);
		
	$objPHPExcel->getActiveSheet()->getColumnDimension(1)
        ->setAutoSize(true);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension(2)
        ->setAutoSize(true);
	
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	
	
	@header('Content-Type: application/vnd.ms-excel');
	@header('Content-Disposition: attachment;filename="'.$fn.'.xlsx"');
	@header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output'); 
	unset($objWriter); 
	$objWriter = null; 

	
	flush(); 
	JFactory::getApplication()->close(); 
		
  }
  
  	private function _getPHPExcel() {
		@ini_set("memory_limit",'32G');
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) 
			return false; 
		//$this->_die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel<br />Install via RuposTel One Page Checkout -> OPC Order Manager -> Excell Export -> Download and Install');
		
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');
		return true; 
	}
	
	private function _die($msg) {
		echo $msg; 
		JFactory::getApplication()->close(); 
	}
  
  
  public function getClass($loadfile) {
	   if (empty($loadfile)) return; 
	   require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	  
	   if (!function_exists('simplexml_load_file')) return; 
	  
	 
	  
	  $xmlexport = new JModelXmlexport(); 
	  $files = $xmlexport->getPhpExportThemes(); 
	  
	  
	  
	  $general = new stdClass(); 
	  $xmlexport->getGeneral($general); 
	  OPCXmlExport::$config = $general; 
	  
	  $default = array(); 
	  $ic = OPCconfig::getArray('tracking_config_pairing', 'custom_pairing', null, $default); 
	    
	  $arr2 = array(); 
	  $langs = array(); 
	  foreach ($files as $k=>$f)
	   {
		   $config = $xmlexport->getThemeConfig($f); 
		   $config->customs_override = $ic; 
		   
		   
		   if ((empty($config)) || (empty($config->enabled))) {
		   continue; 
		   }
		   else
		   {
			   
			   
			   
		     if (empty($config->language)) {
				 $config->language = 'en-GB'; 
			 }
			  
		    $arr2[$k]['file'] = $f; 
		    $arr2[$k]['config'] = $config; 
		    $langs[$config->language] = $config->language; 
		   }
	   }
	   
	   
	foreach ($arr2 as $x)
	{
	   $file = $x['file']; 
	   
	   
	   
	   // special case: 
	   if (!empty($loadfile))
	   if ($loadfile != $file) continue; 
	   
	   
	   
	   if (!empty($onlyf) && ($onlyf != $file)) continue; 
	   
	   $config = $x['config']; 
	   
	   
	   jimport('joomla.filesystem.file');
	   $file = JFile::makeSafe($file);
	   $xmlpath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$file.'.xml'; 
	   $phppath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$file.'.php'; 
	   
	   
	   
	   if (!file_exists($phppath)) continue; 
	   

	    
	   
	   
	   $xml = simplexml_load_file($xmlpath);
	   if (!empty($xml->element))
	   {
	     $class = ucfirst(strtolower($xml->element)).'Xml'; 
	   }
	   else
	   {
	     $class = ucfirst(strtolower($file)).'Xml'; 
	   }
	   
	   if (!class_exists($class))
	   include($phppath); 
	   
	   
	   
	   
	   
	   if (!class_exists($class)) continue; 
	   OPCXmlExport::addClass($class, $config, $xml, $file); 
	   
	  
	   return OPCXmlExport::$classes[$class];
	   
	   
	   
	   
	}
	
		
  }
  
  public function createXml($loadfile='')
   {
   
      require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	  
	  if (!function_exists('simplexml_load_file')) return; 
	  $finished = false; 
	  
	  
	  $xmlexport = new JModelXmlexport(); 
	  $print = JRequest::getVar('print', ''); 
	  //$this->addModelPath( JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_onepage' .DIRECTORY_SEPARATOR. 'models' );
      //$xmlexport = $this->getModel('xmlexport'); 
	  
	  $files = $xmlexport->getPhpExportThemes(); 
	  
	  $general = new stdClass(); 
	  $xmlexport->getGeneral($general); 
	  OPCXmlExport::$config = $general; 
	  
	  $default = array(); 
	  $ic = OPCconfig::getArray('tracking_config_pairing', 'custom_pairing', null, $default); 
	  
	  JRequest::setVar('product_parent_id',0);
	  JRequest::setVar('virtuemart_manufacturer_id',0);
	  
	  
	  // single file creation: 
	  $onlyf = JRequest::getVar('file', '');
	    
	  $arr2 = array(); 
	  $langs = array(); 
	  foreach ($files as $k=>$f)
	   {
		   $config = $xmlexport->getThemeConfig($f); 
		   $config->customs_override = $ic; 
		   
		   $lang = JFactory::getLanguage()->getTag(); 
			
		   if ((empty($config)) || (empty($config->enabled)))
		   continue; 
		   else
		   {
  	        if (empty($config->language)) $config->language = $lang; 
			  
		    $arr2[$k]['file'] = $f; 
		    $arr2[$k]['config'] = $config; 
		    $langs[$config->language] = $config->language; 
		   }
	   }
	   
	   
	   
	foreach ($arr2 as $x)
	{
	   $file = $x['file']; 
	   
	   // special case: 
	   if (!empty($loadfile))
	   if ($loadfile != $file) continue; 
	   
	   if (!empty($onlyf) && ($onlyf != $file)) continue; 
	   
	   $config = $x['config']; 
	   
	   
	   jimport('joomla.filesystem.file');
	   $file = JFile::makeSafe($file);
	   $xmlpath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$file.'.xml'; 
	   $phppath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'xmlexport'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$file.'.php'; 
	   
	   
	   
	   if (!file_exists($phppath)) continue; 
	   

	    
	   
	   
	   $xml = simplexml_load_file($xmlpath);
	   if (!empty($xml->element))
	   {
	     $class = ucfirst(strtolower($xml->element)).'Xml'; 
	   }
	   else
	   {
	     $class = ucfirst(strtolower($file)).'Xml'; 
	   }
	   
	   if (!class_exists($class))
	   include($phppath); 
	   
	   
	   
	   
	   
	   if (!class_exists($class)) continue; 
	   OPCXmlExport::addClass($class, $config, $xml, $file); 
	   
	   if (!empty($loadfile)) 
	   {
	   return OPCXmlExport::$classes[$class];
	   
	   }
	   
	   
	}
	
	
	
	
	if (!empty($print)) { 
	 ob_start(); 
	}
	if (empty($loadfile))  {
		if (defined('OPCCLI')) {
			$finished = OPCXmlExport::doWorkCli($langs); 
		}
		else {
			$finished = OPCXmlExport::doWork($langs); 
		}
	
	
	
	
	if (!empty($print)) { 
	  $z = ob_get_clean(); 
	  if (count(OPCXmlExport::$classes)===1)
	  {
		  $fst = reset(OPCXmlExport::$classes); 
		  if (isset($fst->writer) && (isset($fst->writer->file)))
		  if (file_exists($fst->writer->file))
		  {
			  $pa = pathinfo($fst->writer->file); 
			  if (!empty($pa['extension'])) {
			    $ext = $pa['extension']; 
				
				switch ($ext) {
				 case 'xml': 
				  @header('Content-Type: application/xml; charset=utf-8');
				  break; 
				 case 'csv': 
				  @header('Content-type: text/csv; charset=utf-8');
				  break; 
				 default:
				 	@header('Content-type: text/csv; charset=utf-8');
				}
			  }
			  
			 // @header("Content-Disposition: attachment; filename=file.csv");
			  @header("Pragma: no-cache");
		      @header("Expires: 0");
			  
			  echo file_get_contents($fst->writer->file); 
			  JFactory::getApplication()->close(); 
		  }
	  }
	  
	}
	}
	
	if ((empty($print))) {
		$msg = OPCXmlExport::getLog(); 
		$ret = array(); 
		$ret['msg'] = $msg; 
		$ret['finished'] = $finished; 
		$ret['from'] = JRequest::getInt('from', 0); 
		$ret['to'] = JRequest::getInt('to', 1); 
		$ret['filekey'] = JRequest::getInt('filekey', 1); 
		$ret['file'] = JRequest::getWord('file', ''); 
		$ret['theme'] = JRequest::getWord('file', ''); 
		$ret['maxreached'] = OPCXmlExport::$maxreached;
		if (!defined('OPCCLI')) {
		  echo json_encode($ret); 
		  JFactory::getApplication()->close(); 
		}
		elseif (class_exists('cliHelper')) {
			foreach ($ret as $key=>$msg) {
				  cliHelper::debug(' '.$key.': '.$msg); 
			}
		}
		
	}
	
	
	   
   }
}