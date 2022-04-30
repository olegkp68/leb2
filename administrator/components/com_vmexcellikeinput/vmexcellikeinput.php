<?php
/**
 * @copyright	Copyright (C) 2014 Holest Engineering www.holest.com.
 * @license		GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;




error_reporting(0);
ini_set("display_errors",0);

set_time_limit ( 60 * 5 ); //5 min
ini_set('memory_limit','256M');

function pelm_error_handler($errno, $errstr, $errfile, $errline){
	//DO NOTHING
}

set_error_handler("pelm_error_handler",E_WARNING | E_PARSE | E_NOTICE);

global $max_time, $start_time, $mem_limit;

$max_time   = ini_get('max_execution_time'); 
$start_time = time();
$plem_errors = "";


if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR ); 

if(isset($_REQUEST['keep_alive'])){
   ob_get_clean();ob_get_clean();ob_get_clean();ob_get_clean();
   header('Content-Type: text/html; charset=UTF-8');
   if($_REQUEST['keep_alive']){
	   ob_clean();	
	   die('keep alive : OK');
   }   
}else if(isset($_REQUEST['get_ip'])){
	
	ob_get_clean();ob_get_clean();ob_get_clean();ob_get_clean();
    header('Content-Type: text/html; charset=UTF-8');
    if($_REQUEST['get_ip']){
	   ob_clean();	
	   echo file_get_contents("http://holest.com/dist/vmexcellikeinput/echo.php");
	   die();
    }   
}

global $vm_lang;
$vm_lang = isset($_REQUEST['vmlang']) ? $_REQUEST['vmlang'] : ( isset( $_COOKIE['pelm_edit_language'] ) ? $_COOKIE['pelm_edit_language'] : "");

if($vm_lang){
	JRequest::setVar('vmlang',$vm_lang);
}

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
					
VmConfig::loadConfig(true);

if(isset(VmConfig::$vmlang) && $vm_lang){
	if(VmConfig::$vmlang != strtolower(str_replace("-","_",$vm_lang))){
		VmConfig::$vmlang = false;
		$vm_lang = VmConfig::setdbLanguageTag();
	}
	$vm_lang = VmConfig::$vmlang;
}else{
	if($vm_lang)
		$vm_lang = VmConfig::setdbLanguageTag($vm_lang);
	else
		$vm_lang = VMLANG;
}
global $media_product_path;

$media_product_path = trim(VmConfig::get('media_product_path'));
if(!$media_product_path)
	$media_product_path = "images/stories/virtuemart/product";
if(substr($media_product_path, -1) == "/")
	$media_product_path = substr($media_product_path,0,strlen($media_product_path) - 1);
//////////////////////////////////////////////////////////////////////////
function pelm_sprintf($str, $arg1 = null, $arg2 = null, $arg3 = null){
	$res = "";
	
	$str = str_replace('% ','%% ',$str);
	
	if($arg3)
		$res = addslashes(JText::sprintf($str,$arg1,$arg2,$arg3));
	elseif($arg2)
		$res = addslashes(JText::sprintf($str,$arg1,$arg2));
	elseif($arg1)
		$res = addslashes(JText::sprintf($str,$arg1));
	else	
		$res = addslashes(JText::sprintf($str));
	
	if(!$res)
		return addslashes($str);
	else
		return $res;
}


// Load the language file of com_virtuemart.
//JFactory::getLanguage()->load('com_virtuemart');

$jlang =JFactory::getLanguage();

if(VmConfig::get('enableEnglish', 1)){
	$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, 'en-GB', TRUE);
	$jlang->load('com_vmexcellikeinput', JPATH_ADMINISTRATOR, 'en-GB', TRUE);
}
$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, $jlang->getDefault(), TRUE);
$jlang->load('com_vmexcellikeinput', JPATH_ADMINISTRATOR, $jlang->getDefault(), TRUE);
$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, $jlang->getTag(), TRUE);
$jlang->load('com_vmexcellikeinput', JPATH_ADMINISTRATOR, $jlang->getTag(), TRUE);

if (!class_exists( 'calculationHelper' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');
if (!class_exists( 'CurrencyDisplay' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'currencydisplay.php');
if (!class_exists( 'VirtueMartModelVendor' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'models'.DS.'vendor.php');
if (!class_exists( 'VmImage' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'image.php');
//if (!class_exists( 'shopFunctionsF' )) require(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctionsf.php');

//if (!class_exists( 'calculationHelper' )) require(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'cart.php');
if (!class_exists( 'VirtueMartModelProduct' )){
   JLoader::import( 'product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' );
}


if(!defined('PLEM_VM_RUN')){
   $r = vmVersion::$RELEASE;
   
   if(!trim($r)){
	   $r = "3.0.0";
   }
   
   $v = explode(".",$r);
   if($v[0] > 2 || ($v[0] == 2 && $v[1] >= 9))
	define('PLEM_VM_RUN',3);
   else	
	define('PLEM_VM_RUN',2);
}

global $user, $db;

$user = JFactory::getUser();
$db   = JFactory::getDBO();

global $productModel,$categoryModel,$manufacturerModel,$customfieldModel;

$productModel      = VmModel::getModel('Product');
$categoryModel     = VmModel::getModel('Category');
$manufacturerModel = VmModel::getModel('Manufacturer');
$customfieldModel  = VmModel::getModel ('Customfields');

function SaveSettings(&$db,&$settings){
   
   $ssettings = $settings;
   if(!is_string($ssettings))
	$ssettings =  $db->quote(json_encode($ssettings));
   
   $q = "UPDATE #__extensions SET params = $ssettings WHERE element = 'com_vmexcellikeinput';";
   $db->setQuery($q);	
   $db->query();

};

global $SETTINGS;
$q = "SELECT params FROM #__extensions WHERE element = 'com_vmexcellikeinput';";
$db->setQuery($q);	
$res = $db->loadObject();




$SETTINGS = json_decode(  $res->params  );

if(isset($_REQUEST['save_settings'])){
   $store_settings = $_REQUEST['save_settings']; 
   $store_settings = json_decode($store_settings);
   foreach($store_settings as $key => $value){
	   $SETTINGS->{$key} = $value;
   }
   $SETTINGS->surogates = array();
   SaveSettings($db, $SETTINGS);
}else{
   if(isset($SETTINGS->surogates))
	$SETTINGS->surogates = (array)$SETTINGS->surogates;
      
   if($_SERVER['REQUEST_METHOD'] === 'GET'){
	   if(isset($SETTINGS->surogates)){
		if(!empty($SETTINGS->surogates)){
			$SETTINGS->surogates = array();
			SaveSettings($db, $SETTINGS);
		}
	   }	
   }
   
   if(!isset($SETTINGS->surogates))
	$SETTINGS->surogates = array();
}
//DEFAULTS//////////////////////////
if(!isset($SETTINGS))
	$SETTINGS = new stdClass();
	
if(!isset($SETTINGS->frozen_columns))
    $SETTINGS->frozen_columns = "2";

if(!is_numeric($SETTINGS->frozen_columns))
	$SETTINGS->frozen_columns = "2";


if(!isset($SETTINGS->cf_val_separator))
	$SETTINGS->cf_val_separator = ";";

if(!$SETTINGS->cf_val_separator)
	$SETTINGS->cf_val_separator = ";";

if(!isset($SETTINGS->modified_update))
	$SETTINGS->modified_update = false;
if(!isset($SETTINGS->allow_delete))	
	$SETTINGS->allow_delete   = 0;
if(!isset($SETTINGS->create_categories))	
	$SETTINGS->create_categories   = 1;
if(!isset($SETTINGS->allow_autoimport))	
	$SETTINGS->allow_autoimport   = 0;
if(!isset($SETTINGS->export_content))	
	$SETTINGS->export_content   = 0;
if(!isset($SETTINGS->export_images))	
	$SETTINGS->export_images   = 0;
if(!isset($SETTINGS->import_content))	
	$SETTINGS->import_content   = 0;
if(!isset($SETTINGS->import_images))	
	$SETTINGS->import_images   = 0;
if(!isset($SETTINGS->export_images_meta))	
	$SETTINGS->export_images_meta   = 0;
if(!isset($SETTINGS->import_images_meta))	
	$SETTINGS->import_images_meta   = 0;
if(!isset($SETTINGS->allow_add))	
	$SETTINGS->allow_add   = 1;	
if(!isset($SETTINGS->override_price))	
	$SETTINGS->override_price   = 1;	

if(!isset($SETTINGS->surogates))	
	$SETTINGS->surogates        = array();
	
if(!isset($SETTINGS->custom_import))
	$SETTINGS->custom_import    = 0;

if(!isset($SETTINGS->custom_export))
	$SETTINGS->custom_export    = 0;

if(!isset($SETTINGS->first_row_header))
	$SETTINGS->first_row_header = 1;

if(!isset($SETTINGS->custom_import_columns))
	$SETTINGS->custom_import_columns = array();	
	
if(!isset($SETTINGS->custom_export_columns))
	$SETTINGS->custom_export_columns = array();

if(!isset($SETTINGS->hidden_columns))
	$SETTINGS->hidden_columns = array();	

if(!isset($SETTINGS->allow_groups))
	$SETTINGS->allow_groups = array();

if(!is_array($SETTINGS->allow_groups))
	$SETTINGS->allow_groups = explode(",",$SETTINGS->allow_groups);

if(!isset($SETTINGS->show_prices))
	$SETTINGS->show_prices = 1;
if(!isset($SETTINGS->prices))
	$SETTINGS->prices = 1;	


if(!isset($SETTINGS->csv_separator))
	$SETTINGS->csv_separator = ',';

if(stripos($SETTINGS->csv_separator,"t") !== false)
	$SETTINGS->csv_separator = "\t";

if(!isset($SETTINGS->csv_separator_exp))
	$SETTINGS->csv_separator_exp = ',';

if(stripos($SETTINGS->csv_separator_exp,"t") !== false)
	$SETTINGS->csv_separator_exp = "\t";


if(!isset($SETTINGS->image_upload))
	$SETTINGS->image_upload = 0;
if(!isset($SETTINGS->german_numbers))
	$SETTINGS->german_numbers = 0;		

if(!isset($SETTINGS->par_ch_stick_disable))
	$SETTINGS->par_ch_stick_disable = 0;

if(!isset($SETTINGS->cf_no_name))
	$SETTINGS->cf_no_name               = false;

if(!isset($SETTINGS->import_images_overwrite))
	$SETTINGS->import_images_overwrite  = false;

if(!isset($SETTINGS->max_time))
	$SETTINGS->max_time = $max_time;

if($SETTINGS->max_time){
	
	if($SETTINGS->max_time < 10)
		$SETTINGS->max_time = 10;
	
	if( is_numeric($SETTINGS->max_time)){
		$max_time = $SETTINGS->max_time;
	}else
		$SETTINGS->max_time = $max_time;
}

$forbiden = false;

$isroot = $user->authorise('core.admin');

if(!$isroot){
	if(!empty($SETTINGS->allow_groups)){
		if(isset($user->groups)){
			if(!empty($user->groups)){
				$ugs = array_keys($user->groups);	
				foreach($ugs as $ug){
					if($ug){	
						if(in_array( $ug."", $SETTINGS->allow_groups)){
							$forbiden = true;
							break;
						}
					}
				}
			}
		}
	}
}

if($forbiden){
	?>
	
	<div>
	<p>You dont have sufficient permissions to use "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT"); ?>"</p>
	</div>
	
	<?php
	return;
	
}else{
	ob_get_clean();ob_get_clean();ob_get_clean();ob_get_clean();
    header('Content-Type: text/html; charset=UTF-8');
}

global $pelm_plugins;
$pelm_plugins = array();
$plugin_dir = dirname(__FILE__) . DS . "plugins";
if ($dir_handle = opendir($plugin_dir)) {
   while (false !== ($entry = readdir($dir_handle))) {
	if ($entry != "." && $entry != "..") {
	   $ppath = $plugin_dir . DS . $entry;
	  
	   if(is_dir($ppath)){
		   $pelm_plugins[] = realpath($ppath);
		   
	   }
	}
   }
   closedir($dir_handle);
}

function get_pelm_plugins_toinc($stage){
	global $pelm_plugins;
	$to_exec = array();
	if(isset($pelm_plugins)){
	   foreach($pelm_plugins as $plg_dir){
		  if(file_exists($plg_dir . DS . $stage . ".php"))
			$to_exec[] = $plg_dir . DS . $stage . ".php";	
	   }	
	}
	return $to_exec;
}


foreach(get_pelm_plugins_toinc("init") as $plg)
	include($plg);	




if(isset($_REQUEST["content_edit"])){
	$editor = JFactory::getEditor();
	?><!DOCTYPE html>
	<html>
	<head>
	<style type="text/css">
	body,HTML{
	 background:#9b4f96!important;	
     padding:0!important;	
     min-width:250px!important;	 
	}
	
	HTML{
		overflow:visible!important;
	}
	
	#editor-xtd-buttons{
	 background:#9b4f96;	
	}
	
	body > header,
	body > nav,
	body > a,
	body > #footer,
	body > p,
	body > #border-top,
	body > #header-box,
	body > #content-box,
	body > .collapse,
	body > #status{
		display:none!important;
	}

	body > .container-main{
		display:block!important;
		padding:0!important;
		
	}

	body > .container-main:before{
		display:none;
	}

	</style>
	<meta charset="UTF-8">
	</head>
	<body>
	
	<form>
	<?php echo $editor->display('product_desc',  "", '100%;', '300px;', '75', '20', array('pagebreak', 'readmore') ) ; ?>
	</form>
	
	</body>
	</html>
	<?php
	return;
}



$limit = isset($_COOKIE['pelm_txtlimit']) ? $_COOKIE['pelm_txtlimit'] : 1000;
$page  = 1;
$sortColumn = "p.virtuemart_product_id";
$sortOrder  = "ASC";
$product_sku = '';
$product_name = '';
$product_manufacturer = '';
$product_category = '';
$product_in_stock = "";
$product_show = '0';

if(isset($_REQUEST['limit'])){
	$limit = $_REQUEST['limit'];
}

if(isset($_REQUEST['page'])){
	$page = $_REQUEST['page'];
}

if(isset($_REQUEST['product_sku'])){
	$product_sku = $_REQUEST['product_sku'];
}

if(isset($_REQUEST['product_name'])){
	$product_name = $_REQUEST['product_name'];
}

if(isset($_REQUEST['product_manufacturer'])){
	$product_manufacturer = $_REQUEST['product_manufacturer'];
	if(is_array($product_manufacturer))
		$product_manufacturer = implode(",",$product_manufacturer);

}

if(isset($_REQUEST['product_category'])){
	$product_category = $_REQUEST['product_category'];
	if(is_array($product_category))
		$product_category = implode(",",$product_category);
}

$product_in_stock_f = "";
if(isset($_REQUEST['product_in_stock'])){
	$product_in_stock = $_REQUEST['product_in_stock'];
	
	if(str_ireplace(array('and','0','1','2','3','4','5','6','7','8','9',' ','=','>','<','>=','<=','!='),'', $product_in_stock))
		$product_in_stock = '';
	
	
	if($product_in_stock){
		if(is_numeric($product_in_stock)){
			$product_in_stock_f = " = ".$product_in_stock;
		}else{
			$product_in_stock_f = str_ireplace("AND"," AND p.product_in_stock ",$product_in_stock);
		}
	}
}

if(isset($_REQUEST['product_show'])){
	$product_show = $_REQUEST['product_show'];
}	

if(isset($_REQUEST['sortColumn'])){
	$sortColumn = $_REQUEST['sortColumn'];
	if($sortColumn == "virtuemart_product_id") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_sku") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_name") $sortColumn = "pl.".$sortColumn;
	elseif($sortColumn == "categories") $sortColumn = "cl.category_name";
	elseif($sortColumn == "virtuemart_manufacturer_id") $sortColumn = "ml.mf_name";
	elseif($sortColumn == "product_price") $sortColumn = "pr_p.product_price";
	elseif($sortColumn == "product_sales_price") $sortColumn = "pr_p.product_price";
	elseif($sortColumn == "product_override_price") $sortColumn = "pr_p.".$sortColumn." * coalesce((pr_p.override) = 1,0)";
	elseif($sortColumn == "slug") $sortColumn = "pl.".$sortColumn;
	elseif($sortColumn == "product_in_stock") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_ordered") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "published") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_special") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_s_desc") $sortColumn = "pl.".$sortColumn;
	elseif($sortColumn == "product_weight") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_weight_uom") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_length") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_width") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_height") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_lwh_uom") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_packaging") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_unit") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "metarobot") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "metaauthor") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_url") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_gtin") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "product_mpn") $sortColumn = "p.".$sortColumn;
	elseif($sortColumn == "metadesc") $sortColumn = "pl.".$sortColumn;
	elseif($sortColumn == "metakey") $sortColumn = "pl.".$sortColumn;
	elseif($sortColumn == "customtitle") $sortColumn = "pl.".$sortColumn;
	else {
		$sortColumn = "p.virtuemart_product_id";
	}
}

if(isset($_REQUEST['sortOrder'])){
	$sortOrder = $_REQUEST['sortOrder'];
}

global $calculator;
$calculator = calculationHelper::getInstance ();

$vm_languages  = array();
$_COOKIE['pelm_edit_language'] = $vm_lang;
foreach(VmConfig::get('active_languages',array()) as $lng){
   $vm_languages[$lng] = str_replace("-","_",strtolower($lng));
}

if(empty($vm_languages)){
  $L = explode("_",$vm_lang);
  $L[1] = strtoupper($L[1]);
  $L =implode("-",$L);
  $vm_languages[$L] = $vm_lang;
}

$manufacturerCategoriesModel = VmModel::getModel('Manufacturercategories');

function plem_getProductPrices( &$pr ){
	global $calculator;
	ob_start();
	$prices =  $calculator->getProductPrices($pr);
	ob_end_clean();
	return $prices;
}

function pelm_clearCache(){
	$cache = JFactory::getCache();
	if(method_exists($cache,"cleanCache")){
		$cache->cleanCache();
	}else if(method_exists($cache,"clean")){
		$cache->clean();
	}else if(isset($cache->cache)){
		if(method_exists($cache->cache,"cleanCache")){
			$cache->cache->cleanCache();
		}else if(method_exists($cache->cache,"clean")){
			$cache->cache->clean();
		}
	}
}

/*
$pref = $db->getPrefix();
if( substr($pref, strlen($pref) - 1) != "_"){
	$pref.= "_";
}
$db->setQuery("SHOW TABLES LIKE '".$pref."virtuemart_products_%'");
foreach($db->loadObjectList() as $key => $ltbl ){
	$ltbl = array_values((array)$ltbl);
	$ltbl = $ltbl[0];
	$lng = explode("products_",$ltbl);
	$lng = $lng[1];
    $ltag = explode("_",$lng);
	$ltag = $ltag[0] . "-" . strtoupper($ltag[1]);
	$vm_languages[$ltag] = $lng;
}
*/

/////////////////////////////////////
pelm_clearCache();
$db->setQuery("SELECT config FROM #__virtuemart_configs ORDER BY virtuemart_config_id DESC LIMIT 1");
$cfg = $db->loadObject();
$cfg = $cfg->config;
$cfg = explode("|",$cfg);
$default_cfg = array();
for($i =0; $i < count($cfg); $i++){
	if($cfg[$i]){
		$cfg[$i] = explode("=",$cfg[$i]);
		if(isset($cfg[$i][0]) && isset($cfg[$i][1])){
			$default_cfg[$cfg[$i][0]] = str_replace('"','',$cfg[$i][1]);	
		}
	}
}

if(isset($default_cfg["vmlang"]))
	$default_cfg["vmlang"] = str_replace("-","_",strtolower($default_cfg["vmlang"]));
else 
	$default_cfg["vmlang"] = "en_gb";

global $has_gtinmpn;
$has_gtinmpn = false;

$db->setQuery("SHOW COLUMNS FROM #__virtuemart_products WHERE Field LIKE 'product_gtin' OR  Field LIKE 'product_mpn';");
$pr_cols = $db->loadObjectList();
$has_gtinmpn = (count($pr_cols) == 2);
 
$hasCatfn = false;

$pref = $db->getPrefix();
$config = JFactory::getConfig();
$databaseName=$config->get('db');

if( strpos($pref, "_") === false){
	$pref.= "_";
}



try{
	$db->setQuery("SHOW FUNCTION STATUS WHERE `Type` LIKE 'FUNCTION' AND Db Like '$databaseName' AND Name LIKE '".$pref."plem_product_in_cats2'");
	$hasCatfn =  $db->loadObjectList();
	
	if(count($hasCatfn) == 0){
		$db->setQuery("
			CREATE FUNCTION `".$pref."plem_product_in_cats2`(product_id int, cats text) RETURNS bit(1)
			BEGIN
			  DECLARE parent  int;
			  DECLARE in_cat  bit;
			  DECLARE ccount  int;
			  DECLARE orig_id int;
			  
			  SET orig_id = product_id;
			  SET parent = (SELECT p.product_parent_id FROM ".$pref."virtuemart_products as p WHERE p.virtuemart_product_id = product_id); 
			  WHILE parent > 0 DO
				SET product_id = parent;
				SET parent = (SELECT p.product_parent_id FROM ".$pref."virtuemart_products as p WHERE p.virtuemart_product_id = product_id); 
			  END WHILE;
			 
			  SET ccount = (SELECT count(*) FROM
							  ".$pref."virtuemart_product_categories as pc
							  LEFT JOIN
							  ".$pref."virtuemart_category_categories as cc1 on cc1.category_child_id = pc.virtuemart_category_id
							  LEFT JOIN
							  ".$pref."virtuemart_category_categories as cc2 on cc1.category_parent_id = cc2.category_child_id 
							  LEFT JOIN
							  ".$pref."virtuemart_category_categories as cc3 on cc2.category_parent_id = cc3.category_child_id 
							  LEFT JOIN
							  ".$pref."virtuemart_category_categories as cc4 on cc3.category_parent_id = cc4.category_child_id 
							WHERE
							  pc.virtuemart_product_id IN (product_id,orig_id)
							AND
							  (	
							  LOCATE (concat(',',pc.virtuemart_category_id,','), concat(',',cats,',')) > 0
							  OR
							  LOCATE (concat(',',coalesce(cc1.category_parent_id,'-'),','), concat(',',cats,',')) > 0
							  OR
							  LOCATE (concat(',',coalesce(cc2.category_parent_id,'-'),','), concat(',',cats,',')) > 0
							  OR
							  LOCATE (concat(',',coalesce(cc3.category_parent_id,'-'),','), concat(',',cats,',')) > 0
							  OR
							  LOCATE (concat(',',coalesce(cc4.category_parent_id,'-'),','), concat(',',cats,',')) > 0
							  ));
			  
			  IF ccount > 0 THEN SET in_cat = 1;
			  ELSE SET in_cat = 0;
			  END IF;

			  RETURN in_cat;
			END;
		");

		$db->query();

		$db->setQuery("SHOW FUNCTION STATUS WHERE `Type` LIKE 'FUNCTION' AND Db Like '$databaseName' AND Name LIKE '".$pref."plem_product_in_cats2'");
		$hasCatfn =  $db->loadObjectList();
		
		$db->setQuery("GRANT EXECUTE ON FUNCTION #__plem_product_in_cats2 TO '%'@'%';");
		$db->query();
	}
	
	
}catch(Exception $ex){
//
}

/////////////////////////////////////
$q = "SELECT virtuemart_custom_id, custom_parent_id, custom_title, custom_value, custom_element, field_type, is_cart_attribute FROM #__virtuemart_customs WHERE NOT field_type LIKE 'R' AND  NOT field_type LIKE 'Z' AND published = 1";//" AND field_type IN ('S','I','B');";
$db->setQuery($q);	
$custom_fields = $db->loadAssocList('virtuemart_custom_id');
//////////////////////////////////////////////////////////////////////////
global $default_shopper_group_id;
$default_shopper_group_id = 0;

$q = "SELECT virtuemart_shoppergroup_id FROM #__virtuemart_shoppergroups WHERE `default` = 1";
$db->setQuery($q);
$default_shopper_group_id = $db->loadResult();
if(!$default_shopper_group_id)
	$default_shopper_group_id = 0;

function cf_field_name($cf){
	global $SETTINGS;
	if( $SETTINGS->cf_no_name ){
		return "customfield".'_cf'.$cf['virtuemart_custom_id'];	
	}else{
		return str_replace(array(" ","-",":",";","?",">","<","!","'",'"'),"_", strtolower($cf['custom_title'])).'_cf'.$cf['virtuemart_custom_id'];	
	}
}

//////////////////////////////////////////////////////////////////////////
function search_files($current_path, &$el) { 
    $dir = opendir($current_path); 
    
    while(false !== ( $file = readdir($dir)) ) { 
	
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($current_path . "/" . $file) ) { 
			    $item = new stdClass;
				$item->t = "d"; 
				$item->n = $file;
				$item->c    = array();
				$el->c[]    = $item;
				
			    search_files($current_path . DS . $file, $item); 
				
		    } else {
			    $match = true;
			    $ext = strtolower(end(explode('.', $file)));
				if(!in_array($ext,array("jpg","png","gif","bmp","jpeg")))
					continue;
					
				$item = new stdClass;
				$item->t = "f"; 
				$item->n = $file;
				$el->c[]    = $item;	
		    } 
        } 
    } 
    closedir($dir); 
	return true;
};


function toUTF8($str){
	if(is_string($str)){
		if(function_exists("mb_convert_encoding")){
			return mb_convert_encoding($str,"UTF-8");
		}else{
			return htmlspecialchars_decode(utf8_decode(htmlentities($str, ENT_COMPAT, 'utf-8', false)));	
		}
	}else
		return $str;
}

function encodeCSVContent($cnt){
	return htmlentities(str_replace(array("\n","\r"),array("",""), toUTF8($cnt)));
}

function decodeCSVContent($cnt){
	return stripcslashes(html_entity_decode($cnt));
}

/////////////////////////////////////
//CHECK LANGUAGE TABLES


if($_SERVER['REQUEST_METHOD'] === 'GET'){
	
	try{
	
		$db->setQuery("SELECT (SELECT count(*) FROM #__virtuemart_products)  != (SELECT  count(*) FROM #__virtuemart_products_$vm_lang) as do_fix");
		$do_plng_fix = $db->loadResult();
		
		$db->setQuery("SELECT (SELECT count(*) FROM #__virtuemart_categories)  != (SELECT  count(*) FROM #__virtuemart_categories_$vm_lang) as do_fix");
		$do_clng_fix = $db->loadResult();
		
		$db->setQuery("SELECT (SELECT count(*) FROM #__virtuemart_manufacturers)  != (SELECT  count(*) FROM #__virtuemart_manufacturers_$vm_lang) as do_fix");
		$do_mlng_fix = $db->loadResult();

		if($do_plng_fix){
			$db->setQuery("SELECT p.virtuemart_product_id FROM #__virtuemart_products as p LEFT JOIN #__virtuemart_products_$vm_lang as pl on pl.virtuemart_product_id = p.virtuemart_product_id WHERE pl.virtuemart_product_id IS NULL");
			
			$fix_ids = $db->loadColumn();
			
			$cols = array( 'virtuemart_product_id'
						  ,'product_s_desc'
						  ,'product_desc'
						  ,'product_name'
						  ,'metadesc'
						  ,'metakey'
						  ,'customtitle'
						  ,'slug');
		
			
			$q = "INSERT IGNORE INTO #__virtuemart_products_$vm_lang (".implode(",",$cols).") "; 
			
			for($I = 0 ; $I < count($cols); $I++ ){
				
				$colopt = array();
				
				foreach($vm_languages as $key => $lng){
					$colopt[] = $lng.".".$cols[$I];	
				}
				
				if($cols[$I] == "virtuemart_product_id")
					$colopt[] = "p.virtuemart_product_id";
				else if($cols[$I] == "product_name")
					$colopt[] = "'Unnamed'";
				else if($cols[$I] == "slug")
					$colopt[] = "concat('product-' ,p.virtuemart_product_id)";
				else	
					$colopt[] = "''";
				
				$cols[$I] = "coalesce(".implode(",",$colopt) .") as " . $cols[$I] . " "; 
			}
			
			$q .= "SELECT " . implode(",",$cols);
			
			$q .= "FROM #__virtuemart_products as p ";
			
			foreach($vm_languages as $key => $lng){
				$q .= " LEFT JOIN #__virtuemart_products_$lng as $lng ON $lng.virtuemart_product_id = p.virtuemart_product_id";
			}  
			
			$q .= " WHERE p.virtuemart_product_id = ";
			
			foreach($fix_ids as $fid){
				$db->setQuery($q . $fid);
				$db->query();
			}
			
		}
		
		if($do_clng_fix){
			$db->setQuery("SELECT p.virtuemart_category_id FROM #__virtuemart_categories as p LEFT JOIN #__virtuemart_categories_$vm_lang as pl on pl.virtuemart_category_id = p.virtuemart_category_id WHERE pl.virtuemart_category_id IS NULL");
			$fix_ids = $db->loadColumn();
			
			$cols = array( 'virtuemart_category_id'
						  ,'category_name'
						  ,'category_description'
						  ,'metadesc'
						  ,'metakey'
						  ,'customtitle'
						  ,'slug');
		
			$q = "INSERT IGNORE INTO #__virtuemart_categories_$vm_lang (".implode(",",$cols).") "; 
			
			for($I = 0 ; $I < count($cols); $I++ ){
				
				$colopt = array();
				
				foreach($vm_languages as $key => $lng){
					$colopt[] = $lng.".".$cols[$I];	
				}
				
				if($cols[$I] == "virtuemart_category_id")
					$colopt[] = "p.virtuemart_category_id";
				else if($cols[$I] == "category_name")
					$colopt[] = "'Unnamed'";
				else if($cols[$I] == "slug")
					$colopt[] = "concat('cat-' ,p.virtuemart_category_id)";
				else	
					$colopt[] = "''";
				
				$cols[$I] = "coalesce(".implode(",",$colopt) .") as " . $cols[$I] . " "; 
			}
			
			$q .= "SELECT " . implode(",",$cols);
			
			$q .= "FROM #__virtuemart_categories as p ";
			
			foreach($vm_languages as $key => $lng){
				$q .= " LEFT JOIN #__virtuemart_categories_$lng as $lng ON $lng.virtuemart_category_id = p.virtuemart_category_id ";
			}  
			
			$q .= " WHERE p.virtuemart_category_id = ";
			
			foreach($fix_ids as $fid){
				$db->setQuery($q . $fid);
				$db->query();
			}
			
		}
		
		if($do_mlng_fix){
			$db->setQuery("SELECT p.virtuemart_manufacturer_id FROM #__virtuemart_manufacturers as p LEFT JOIN #__virtuemart_manufacturers_$vm_lang as pl on pl.virtuemart_manufacturer_id = p.virtuemart_manufacturer_id WHERE pl.virtuemart_manufacturer_id IS NULL");
			$fix_ids = $db->loadColumn();
			
			$cols = array( 'virtuemart_manufacturer_id'
						  ,'mf_name'
						  ,'mf_email'
						  ,'mf_desc'
						  ,'mf_url'
						  ,'slug');
		
			$q = "INSERT IGNORE INTO #__virtuemart_manufacturers_$vm_lang (".implode(",",$cols).") "; 
			
			for($I = 0 ; $I < count($cols); $I++ ){
				
				$colopt = array();
				
				foreach($vm_languages as $key => $lng){
					$colopt[] = $lng.".".$cols[$I];	
				}
				
				if($cols[$I] == "virtuemart_manufacturer_id")
					$colopt[] = "p.virtuemart_manufacturer_id";
				else if($cols[$I] == "mf_name")
					$colopt[] = "'Unnamed'";
				else if($cols[$I] == "slug")
					$colopt[] = "concat('man-' ,p.virtuemart_manufacturer_id)";
				else	
					$colopt[] = "''";
				
				$cols[$I] = "coalesce(".implode(",",$colopt) .") as " . $cols[$I] . " "; 
			}
			
			$q .= "SELECT " . implode(",",$cols);
			
			$q .= "FROM #__virtuemart_manufacturers as p ";
			
			foreach($vm_languages as $key => $lng){
				if($lng != $vm_lang)
					$q .= " LEFT JOIN #__virtuemart_manufacturers_$lng as $lng ON $lng.virtuemart_manufacturer_id = p.virtuemart_manufacturer_id ";
			}  
			
			$q .= " WHERE p.virtuemart_manufacturer_id = ";
			
			foreach($fix_ids as $fid){
				$db->setQuery($q . $fid);
				$db->query();
			}
			
		}
	
	}catch(Exception $sql_ex){
		
	}
}



function fix_images(&$db){
	$db->setQuery("DELETE pm 
	  FROM 
	  #__virtuemart_product_medias as pm
	  LEFT JOIN
	  #__virtuemart_medias as m on m.virtuemart_media_id = pm.virtuemart_media_id
	  WHERE m.file_url IS NULL;");
	$db->query();  


	$db->setQuery("SELECT 
		min(pm.id) as id
	  FROM 
	  #__virtuemart_product_medias as pm
	  LEFT JOIN
	  #__virtuemart_medias as m on m.virtuemart_media_id = pm.virtuemart_media_id
	  GROUP BY pm.virtuemart_product_id, m.file_url
	  having count(pm.id) > 1;");
	
	$d_ids = $db->loadColumn();
	if(!empty($d_ids)){
		$db->setQuery("DELETE FROM #__virtuemart_product_medias WHERE id IN(" . implode(",",$d_ids) . ")");
		$db->query();
	}
	
}

function delete_exces_images(){
	global $db,$media_product_path;
	
	$res_i = 0;
	$res_t = 0;
	
	fix_images($db);
    $db->setQuery(	
		"SELECT
			LOWER(m.file_url) as file_url,
			count(m.virtuemart_media_id) as picount
		  FROM 
		  #__virtuemart_product_medias as pm
		  LEFT JOIN
		  #__virtuemart_medias as m on m.virtuemart_media_id = pm.virtuemart_media_id GROUP BY m.file_url;"
    );
	
	$images = $db->loadAssocList('file_url','picount');
	
	$db->setQuery(
		"SELECT
			LOWER(m.file_url_thumb) as file_url_thumb,
			count(m.virtuemart_media_id) as picount
		  FROM 
		  #__virtuemart_product_medias as pm
		  LEFT JOIN
		  #__virtuemart_medias as m on m.virtuemart_media_id = pm.virtuemart_media_id GROUP BY m.file_url_thumb;"
	);
	$thumbs = $db->loadAssocList('file_url_thumb','picount');	
	
	$image_dir = realpath(JPATH_SITE . DIRECTORY_SEPARATOR . $media_product_path);
	$thumb_dir = realpath(JPATH_SITE . DIRECTORY_SEPARATOR . $media_product_path . DIRECTORY_SEPARATOR . 'resized');
	
	if ($handle = opendir( $image_dir )) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && !is_dir($image_dir . DIRECTORY_SEPARATOR .$entry)) {
				if(!isset($images["$media_product_path/" . strtolower($entry)])){
					unlink($image_dir . DIRECTORY_SEPARATOR . $entry);
					$res_i++;
				}
			}
		}
		closedir($handle);
	}
	
	
	if ($handle = opendir( $thumb_dir )) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && !is_dir($thumb_dir . DIRECTORY_SEPARATOR .$entry)) {
				if(!isset($thumbs["$media_product_path/resized/" . strtolower($entry)])){
					unlink($thumb_dir . DIRECTORY_SEPARATOR . $entry);
					$res_t++;
				}
			}
		}
		closedir($handle);
	}
	
	return $res_i + $res_t;
}

///////////////////////////////////////////////////////////////////////////////////////
if(isset($_REQUEST['p_clean_images'])){
	
	$removed = delete_exces_images();
	
	$resp = new stdClass;
	$resp->removed = $removed;
	echo json_encode($resp);
	die;
	
}else if(isset($_REQUEST['P_CONTENT'])){

	if($_REQUEST['P_CONTENT'] == "set"){
		$json    = file_get_contents('php://input');
		$obj = json_decode($json);
		$content = $obj->content;
		
		if(isset($_REQUEST["language"])){
			$lng = $_REQUEST["language"];
			if(!$lng)
				$lng = "en_gb";
		}
		
		$q = "UPDATE #__virtuemart_products_". $lng . " SET product_desc = " . $db->quote(decodeCSVContent($content)) . " WHERE virtuemart_product_id = ". $_REQUEST["virtuemart_product_id"];
		
		$db->setQuery($q);	
		$db->query();
		
		
	}
	
	if($_REQUEST['P_CONTENT'] == "get" || $_REQUEST['P_CONTENT'] == "set"){
		$lng = "en_gb";
		if(isset($_REQUEST["language"])){
			$lng = $_REQUEST["language"];
			if(!$lng)
				$lng = "en_gb";
		}
		$q = "SELECT product_desc as content FROM #__virtuemart_products_". $lng . " WHERE virtuemart_product_id = ". $_REQUEST["virtuemart_product_id"];
		$db->setQuery($q);	
		$cnt = $db->loadObject();	
		echo json_encode($cnt);
	}

	die;
}else if(isset($_REQUEST['P_IMAGES'])){
	//VmConfig::get('img_width',array());
	//VmConfig::get('img_height',array());
	
	global $media_product_path;

	if($_REQUEST['P_IMAGES'] == "set"){
	
		$json   = file_get_contents('php://input');
		$images = json_decode($json);
		
		if(!is_array($images)){//data carrier
			$images = json_decode(base64_decode($images->data));
		}
		
		$p_id = $_REQUEST["virtuemart_product_id"];
		//DELETE
		
		$IIDS   = array();
		foreach($images as $img){
			if($img->virtuemart_media_id)
				$IIDS[] = $img->virtuemart_media_id;
		}
		
		$f_q = "SELECT 
					DISTINCT id As id
				FROM 
					#__virtuemart_product_medias 
				WHERE
					virtuemart_product_id = $p_id
					".( count($IIDS) ? " AND NOT virtuemart_media_id IN (". implode(",", $IIDS ) .")" : " " )."
				";
		
		$db->setQuery($f_q);
		$res_del_ids = $db->loadColumn();	
		
		if(!empty($res_del_ids)){
			
			$del_q = "SELECT DISTINCT virtuemart_media_id FROM #__virtuemart_product_medias WHERE id IN (". implode(",", $res_del_ids ) .");";
			
			$db->setQuery($del_q);
			$res_del_mids = $db->loadColumn();
			
			if(!empty($res_del_mids)){
				$del_qf = "SELECT 
							  virtuemart_media_id
							FROM 
							  #__virtuemart_product_medias
							WHERE virtuemart_media_id IN(".implode(",", $res_del_mids ).")
							GROUP BY virtuemart_media_id
							HAVING count(virtuemart_product_id) < 2";
				$db->setQuery($del_qf);
				$res_del_mids_final = $db->loadColumn();
				if(!empty($res_del_mids_final)){
					$del_q = "DELETE FROM #__virtuemart_medias WHERE virtuemart_media_id IN(".implode(",",$res_del_mids_final).")";
					$db->setQuery($del_q);
					$db->query();
				}
			}
				
			$del_q = "DELETE FROM #__virtuemart_product_medias WHERE id IN(".implode(",",$res_del_ids).")";
			$db->setQuery($del_q);
			$db->query();	
		}
		
		
		
		
		foreach($images as $img){
			if($img->virtuemart_media_id){//UPDATE
				$u_q = "UPDATE #__virtuemart_medias
						SET
						  published              =  ". $img->published ."
						 ,file_is_product_image  =  ". $img->file_is_product_image ."
						 ,file_description       = ". $db->quote($img->file_description) ."
						 ,file_meta              = ". $db->quote($img->file_meta) ."
						 ,file_title             = ". $db->quote($img->file_title) ."
						WHERE virtuemart_media_id = ". $img->virtuemart_media_id;
				$db->setQuery($u_q);
				$db->query();
				
				$u_q = "UPDATE #__virtuemart_product_medias
						SET
						  ordering              =  ". $img->ordering ."
						WHERE virtuemart_product_id = ".$p_id." AND virtuemart_media_id = ". $img->virtuemart_media_id;
				
				$db->setQuery($u_q);
				$db->query();
				
			}else{//ADD
				
				
				$img->file_name = str_ireplace(' ','_',$img->file_name);
				$imgpath = JPATH_SITE. DIRECTORY_SEPARATOR . $media_product_path . DIRECTORY_SEPARATOR;
				
				//$ifp  = fopen($imgpath . DS . $img->file_name , "wb"); 
				$data = $img->file_url;
				$dind = stripos($data, ',');
				$data = substr($data, $dind + 1);
				
				$image = imagecreatefromstring(base64_decode($data));
				
				if(stripos($img->file_mimetype,'png') !== false || stripos($img->file_mimetype,'gif') !== false){
					imagealphablending($image, false);
					imagesavealpha($image, true);
					$images = imagecolorallocatealpha($image, 255, 255, 255, 127);
				}
				
				$width = imagesx($image);
				$height = imagesy($image);
				
				
				if(file_exists($imgpath . DS . $img->file_name)){
					$n_in = 1;
					while(file_exists($imgpath . DS . $img->file_name)){
						$img->file_name = explode(".",$img->file_name);
						$ext = $img->file_name[count($img->file_name) - 1];
						$img->file_name[count($img->file_name) - 1] = "-" . $n_in .".". $ext; 
						$img->file_name = implode(".",$img->file_name);
						$n_in++;
					}	
				}
				
				if(stripos($img->file_mimetype,'jpg') !== false || stripos($img->file_mimetype,'jpeg') !== false){
					imagejpeg($image, $imgpath . DS . $img->file_name );
				}elseif(stripos($img->file_mimetype,'gif') !== false){
					imagegif($image, $imgpath . DS . $img->file_name);
				}else{
					imagepng($image, $imgpath . DS . $img->file_name);
				}
				
				if($writen !== false){
					
					$twidth  = VmConfig::get('img_width',array());
					$theight = VmConfig::get('img_height',array());
					
					if(!$twidth && $theight){
						$twidth = $theight * ($width/$height);
					}elseif(!$theight && $twidth){
						$theight = $twidth * ($height/$width);
					}elseif(!$theight && !$twidth){
						$twidth  = 220;
						$theight = 220;
					}
						
					$thumb_image = imagecreatetruecolor($twidth , $theight);
					if(stripos($img->file_mimetype,'png') !== false  || stripos($img->file_mimetype,'gif') !== false){
						imagealphablending($thumb_image, false);
						imagesavealpha($thumb_image, true);
						$transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
						imagefilledrectangle($thumb_image, 0, 0, $twidth, $theight, $transparent);
					}	
					
					$scale_width  = 0;
					$scale_height = 0;
					$rat_i = $width  / $height;
					$rat_t = $twidth / $theight;
					
					if($rat_i > $rat_t){
						$scale_width  = $twidth * ( $height /  $theight);
						$scale_height = $height;
					}else{
						$scale_width  = $width;
						$scale_height = $theight * ( $width /  $twidth);
					}
					
					imagecopyresampled($thumb_image, $image, 0, 0, ($width - $scale_width)/2, ($height - $scale_height)/2, $twidth, $theight, $scale_width, $scale_height);
				
					//$thumbname = explode(".",$img->file_name);
					//$thumbname = $thumbname[0];
					
					$thumbname = $img->file_name;
					
					/*
					$tpreff = "";
					$tpreff_n = 1;
					
					while(file_exists($imgpath . DS . 'resized' . DS . $tpreff . $thumbname)){
						$tpreff_n++;
						$tpreff = "thumb" . $tpreff_n . "_"; 
					}
					
					$thumbname = $tpreff . $thumbname;
					*/
					
					if(stripos($img->file_mimetype,'jpg') !== false || stripos($img->file_mimetype,'jpeg') !== false){
						//$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.jpg';
						imagejpeg($thumb_image, $imgpath . DS . 'resized' . DS . $thumbname);
					}elseif(stripos($img->file_mimetype,'gif') !== false){
						//$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.gif';
						imagegif($thumb_image, $imgpath . DS . 'resized' . DS . $thumbname);
					}else{
					    //$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.png';
						imagepng($thumb_image, $imgpath . DS . 'resized' . DS . $thumbname);
					}
					
					$i_sql = "INSERT INTO #__virtuemart_medias( virtuemart_media_id,virtuemart_vendor_id,file_title,file_description,file_meta,file_mimetype,file_type,file_url,file_url_thumb,file_lang ,file_is_product_image,file_is_downloadable,file_is_forSale,file_params,shared,published,created_on,created_by,modified_on,modified_by,locked_on,locked_by) VALUES (
							   NULL
							  ,0
							  ,". $db->quote($img->file_title) ."
							  ,". $db->quote($img->file_description) ."
							  ,". $db->quote($img->file_meta) ."
							  ,". $db->quote($img->file_mimetype) ."
							  ,'product'
							  ,". $db->quote( "$media_product_path/". $img->file_name) ."
							  ,". $db->quote( "$media_product_path/resized/". $thumbname) ."
							  ,''
							  ,". $img->file_is_product_image ."
							  ,0
							  ,0
							  ,''
							  ,0
							  ,". $img->published ."
							  ,NOW()
							  ,". $user->id ."
							  ,NOW()
							  ,". $user->id ."
							  ,'0000-00-00 00:00:00'
							  ,0
							)";
					

					
					$db->setQuery($i_sql);
					$db->query();
					$mid = $db->insertid();		
					
					
					
					$i_sql = "INSERT INTO #__virtuemart_product_medias(id,virtuemart_product_id,virtuemart_media_id ,ordering) VALUES (
								   NULL
								  ,".$p_id."
								  ,".$mid."
								  ,".$img->ordering."
								)";
					
					$db->setQuery($i_sql);
					$db->query();
				}
			}
		}
	}
	
	if($_REQUEST['P_IMAGES'] == "get" || $_REQUEST['P_IMAGES'] == "set"){
		
		fix_images($db);
	
		$pi_q = "SELECT 
				PM.virtuemart_product_id,
				PM.virtuemart_media_id,
				PM.ordering,
				M.file_mimetype,
				M.published,
				M.file_is_product_image,
				M.file_url,
				M.file_url_thumb,
				M.file_description,
				M.file_meta,
				M.file_title
				FROM 
				#__virtuemart_product_medias as PM
				LEFT JOIN
				#__virtuemart_medias As M on M.virtuemart_media_id = PM.virtuemart_media_id
				WHERE 
				" . ( (isset($_REQUEST['virtuemart_product_id']) && $_REQUEST['virtuemart_product_id']) ?  ("PM.virtuemart_product_id = ". $_REQUEST['virtuemart_product_id']) : "") . "
				AND (M.file_mimetype LIKE '%jpg' || M.file_mimetype LIKE '%jpeg' || M.file_mimetype LIKE '%png'  || M.file_mimetype LIKE '%gif' || M.file_mimetype LIKE '%bmp')
				ORDER BY PM.ordering ASC
				";
		
		$db->setQuery($pi_q);	
		$res = $db->loadObjectList();
		if(!$res)
			$res = array();
		
		echo json_encode($res);
	}
	
	die;
}else if(isset($_REQUEST['I_BROWSE'])){

	if($_REQUEST['I_BROWSE'] == "browse"){
		$fs = new stdClass;
		$fs->c = array(); 
		search_files(JPATH_SITE.DS.'images', $fs);
		echo json_encode($fs);
	}elseif($_REQUEST['I_BROWSE'] == "upload"){
	
	}
	
	die;
}

function fullTrim($str){
	return str_replace(array('  ','  ','  ','  '),array(' ', ' ', ' ', ' '),trim($str));
}


function toLoweCaseDB($str){
	global $db_case;
	if(!isset($db_case))
		$db_case   = JFactory::getDBO();
	$db_case->setQuery("SELECT LOWER(" . $db_case->quote($str) . ") as res");
	return $db_case->loadResult();
}



function normalizedLowercase($str){
	return fullTrim(toLoweCaseDB($str));
}

function normalizedLowercaseHash($str){
	return str_replace(' ','',fullTrim(toLoweCaseDB($str)));
}


///////////////////////////////////////////////////////////////////////////////////////
$ManufacturerCategories = $manufacturerCategoriesModel->getManufacturerCategories(false,true);

$db->setQuery("SELECT
				catn.virtuemart_category_id as virtuemart_category_id,
				catn.category_name as category_name,
				catp.category_parent_id, 
				concat(
				   coalesce( concat(catn5.category_name,'/')  ,''),
					 coalesce( concat(catn4.category_name,'/')  ,''),
					 coalesce( concat(catn3.category_name,'/')  ,''),
					 coalesce( concat(catn2.category_name,'/')  ,''),
					 coalesce( concat(catn1.category_name,'/')  ,''),
					 catn.category_name
				) as category_pathway,
				REPLACE(LOWER(concat(
				   coalesce( concat(catn5.category_name,'/')  ,''),
					 coalesce( concat(catn4.category_name,'/')  ,''),
					 coalesce( concat(catn3.category_name,'/')  ,''),
					 coalesce( concat(catn2.category_name,'/')  ,''),
					 coalesce( concat(catn1.category_name,'/')  ,''),
					 catn.category_name
				)),' ','') as category_pathway_key,
				concat(
				   coalesce( IF(NOT catn5.category_name IS NULL,' - ','')  ,''),
					 coalesce( IF(NOT catn4.category_name IS NULL,' - ','')  ,''),
					 coalesce( IF(NOT catn3.category_name IS NULL,' - ','')  ,''),
					 coalesce( IF(NOT catn2.category_name IS NULL,' - ','')  ,''),
					 coalesce( IF(NOT catn1.category_name IS NULL,' - ','')  ,''),
					 catn.category_name
				) as category_path
				FROM 
				#__virtuemart_categories as c
				LEFT JOIN 
				#__virtuemart_categories_$vm_lang as catn on catn.virtuemart_category_id = c.virtuemart_category_id AND catn.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp on catn.virtuemart_category_id = catp.category_child_id
				LEFT JOIN
				#__virtuemart_categories_$vm_lang as catn1 on catn1.virtuemart_category_id = catp.category_parent_id AND catn1.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp1 on catn1.virtuemart_category_id = catp1.category_child_id
				LEFT JOIN
				#__virtuemart_categories_$vm_lang as catn2 on catn2.virtuemart_category_id = catp1.category_parent_id AND catn2.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp2 on catn2.virtuemart_category_id = catp2.category_child_id
				LEFT JOIN
				#__virtuemart_categories_$vm_lang as catn3 on catn3.virtuemart_category_id = catp2.category_parent_id AND catn3.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp3 on catn3.virtuemart_category_id = catp3.category_child_id
				LEFT JOIN
				#__virtuemart_categories_$vm_lang as catn4 on catn4.virtuemart_category_id = catp3.category_parent_id AND catn4.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp4 on catn4.virtuemart_category_id = catp4.category_child_id
				LEFT JOIN
				#__virtuemart_categories_$vm_lang as catn5 on catn5.virtuemart_category_id = catp4.category_parent_id AND catn5.virtuemart_category_id > 0
				LEFT JOIN
				#__virtuemart_category_categories as catp5 on catn5.virtuemart_category_id = catp5.category_child_id
				WHERE 
					c.virtuemart_category_id > 0
				ORDER BY category_pathway, c.ordering");
				
$records = $db->loadObjectList();
$categories = array();
$cat_asoc = array();
global $catway_asoc, 
       $taxs_asoc,
       $disc_asoc,
       $curr_asoc,	   
	   $catway_asoc_reverse;

	   
$catway_asoc         = array();
$catway_asoc_reverse = array();

foreach($records as $cat ){
	if($cat->category_pathway){
		$categories[] = $cat;
		$catway_asoc[intval($cat->virtuemart_category_id)] = $cat->category_pathway;
		$catway_asoc_reverse[$cat->category_pathway_key]   = intval($cat->virtuemart_category_id);
		$acats[intval($cat->virtuemart_category_id)]       = $cat->category_path;
	}
}

$taxs_asoc     = array();
$disc_asoc     = array();
$curr_asoc     = array();
$db->setQuery("SELECT virtuemart_calc_id, calc_name, calc_kind FROM #__virtuemart_calcs ORDER BY calc_value");
$calcs = $db->loadObjectList();



$disc_asoc[0] = pelm_sprintf("JDEFAULT");
$disc_asoc[-1] = pelm_sprintf("JFIELD_OPTION_NONE");

$taxs_asoc[0] = pelm_sprintf("JDEFAULT");
$taxs_asoc[-1] = pelm_sprintf("JFIELD_OPTION_NONE");

if(!empty($calcs)){
	foreach($calcs as $calc){
		if(strpos(strtolower($calc->calc_kind),"da") !== false || strpos(strtolower($calc->calc_kind),"db") !== false){
			$disc_asoc[$calc->virtuemart_calc_id] = $calc->calc_name;
		}elseif( strpos(strtolower($calc->calc_kind),"tax") !== false){
			$taxs_asoc[$calc->virtuemart_calc_id] = $calc->calc_name;
		}
	}
}


$db->setQuery("SELECT virtuemart_currency_id, currency_code_3 FROM #__virtuemart_currencies ORDER BY currency_code_3");
$currs = $db->loadObjectList();

foreach($currs as $curr){
	$curr_asoc[$curr->virtuemart_currency_id] = $curr->currency_code_3;	  
}

/*
foreach($records as $record){
	$cat = new stdClass();
	$vm_cat = $categoryModel->getCategory($record->virtuemart_category_id, false);
	$cat->virtuemart_category_id = $vm_cat->virtuemart_category_id;
	$cat->category_name          = $vm_cat->category_name;
	$cat->category_path          = $vm_cat->category_name;
	$par = $categoryModel->getParentCategory($cat->virtuemart_category_id);
	if($par){
		while($par->virtuemart_category_id){
			$cat->category_path = '-' . $cat->category_path;	
			$par = $categoryModel->getParentCategory($par->virtuemart_category_id);
			if(!$par)
				break;
		}
	}
	
	$cat_asoc[intval($vm_cat->virtuemart_category_id)] = $cat->category_path;
	
	$categories[] = $cat;
}
*/



$manCats = array();
$man_asoc = array();

try{
	$db->setQuery("SELECT virtuemart_manufacturer_id , mf_name FROM #__virtuemart_manufacturers_$vm_lang ORDER BY mf_name");
	$manufacturers = $db->loadObjectList();
	foreach($manufacturers as $m){
		$man_asoc[$m->virtuemart_manufacturer_id] = $m->mf_name;
	}
}catch(Exception $ex){
	var_dump($ex);	
}



$mc = new stdClass();
$mc->virtuemart_manufacturercategories_id = "0";
$mc->mf_category_name = "";
$mc->manufacturers = $manufacturers;
$manCats[] = $mc;

//FIX//////////////////////////////////////////////////////////////////////////////////////////////////////
function fix_prices_table(&$db,$user_id,$pr_id = 0, $nochildren = true){
    if(!$pr_id)
		$pr_id = '0';
		
	$fixq =	"INSERT IGNORE INTO #__virtuemart_product_prices(
			   virtuemart_product_price_id
			  ,virtuemart_product_id
			  ,virtuemart_shoppergroup_id
			  ,product_price
			  ,override
			  ,product_override_price
			  ,product_tax_id
			  ,product_discount_id
			  ,product_currency
			  ,product_price_publish_up
			  ,product_price_publish_down
			  ,price_quantity_start
			  ,price_quantity_end
			  ,created_on
			  ,created_by
			  ,modified_on
			  ,modified_by
			  ,locked_on
			  ,locked_by
			)
			SELECT 

			   0
			  ,pr.virtuemart_product_id
			  ,null
			  ,0
			  ,null
			  ,0
			  ,0
			  ,0
			  ,(SELECT vendor_currency FROM #__virtuemart_vendors LIMIT 0,1)
			  ,NULL
			  ,NULL
			  ,NULL
			  ,NULL
			  ,'0000-00-00 00:00:00'
			  ,0
			  ,CURRENT_DATE()
			  ,".$user_id."
			  ,'0000-00-00 00:00:00'
			  ,0

			FROM
			  #__virtuemart_products as pr
			LEFT JOIN
			  #__virtuemart_product_prices as pr_p on pr_p.virtuemart_product_id = pr.virtuemart_product_id 
			WHERE
			  "
			  .
			  ($nochildren ? "coalesce(pr.product_parent_id,0) = 0 AND " : " ")
			  .
			  "pr_p.virtuemart_product_price_id IS NULL
			  AND 
			  (".$pr_id." = 0 OR pr.virtuemart_product_id = ".$pr_id.") ";

	$db->setQuery($fixq);
	$db->query();
	if(isset($_REQUEST['fix_prices'])){
		if($_REQUEST['fix_prices']){
			
			$db->setQuery("UPDATE #__virtuemart_product_prices as p
					LEFT JOIN 
					(SELECT
					pr_p.virtuemart_product_id,
					min(pr_p.virtuemart_product_price_id) 
					FROM #__virtuemart_product_prices as pr_p
					GROUP BY pr_p.virtuemart_product_id
					HAVING COUNT(pr_p.virtuemart_product_price_id) = 1) as t  on t.virtuemart_product_id = p.virtuemart_product_id
					SET
					 p.virtuemart_shoppergroup_id = 0,
					 p.price_quantity_start       = 0,
					 p.price_quantity_end         = 0
					WHERE NOT t.virtuemart_product_id IS NULL");
			$db->query();
		}
	}
};


function clear_product_default_price(&$db,$pr_id){
    if(!$pr_id)
		return;
		
	$fixq =	"DELETE FROM #__virtuemart_product_prices
			  WHERE
				  virtuemart_product_id     = $pr_id
			  AND coalesce(product_price,0)              = 0
			  AND coalesce(product_override_price,0)     = 0
			  AND coalesce(virtuemart_shoppergroup_id,0) = 0
			  AND coalesce(price_quantity_start,0)       = 0
			  AND coalesce(price_quantity_end,0)         = 0;";
			  
	$db->setQuery($fixq);
	$db->query();
};

fix_prices_table($db,$user->id);

function sanitize_title($input)
{
    if (!@preg_match('/\pL/u', 'a')){
        $pattern = '/[^a-zA-Z0-9]/';
    }else{
        $pattern = '/[^\p{L}\p{N}]/u';
    }
	return strtolower(preg_replace($pattern, '_', (string) $input));
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function vmel_addProduct(&$db,&$vm_languages,$user_id, $parent_id = null){
	if($parent_id){
		global $productModel;
		$cid = $productModel->createChild($parent_id);
		fix_prices_table($db,$user_id,$cid);
		return $cid;
	}else{
		
		$db->setQuery(
			"INSERT INTO #__virtuemart_products(
			   published, product_available_date, created_on  ,created_by  ,modified_on  ,modified_by  ,product_params 
			   ) VALUES (
			   1, CURRENT_DATE(), NOW() ,".$user_id." ,NOW()  ,".$user_id." ,'min_order_level=0|max_order_level=0|'
			   );"
		);
		
		$db->query();
		$id = $db->insertid();
		
		foreach($vm_languages as $lng => $db_suffix){
			try{
				$db->setQuery(
					"INSERT INTO #__virtuemart_products_".$db_suffix."(
					   virtuemart_product_id ,product_name  ,product_s_desc  ,product_desc ,metadesc ,metakey  ,customtitle ,slug
					) VALUES (
					   ". $id ."
					  ,''  ,''  ,''  ,''  ,''  ,''
					  ,concat('product-',". $id .")
					);"
				);
				$db->query();
			}catch(Exception $ex){
				//
			}
		}
		
		fix_prices_table($db,$user_id,$id);

		return $id;
	}
};

function jsnum($val){
	if($val)
		return floatval($val);
	else
		return null;
}

function singleVal( $val ){
	if(is_array($val)){
		if(!empty($val))
			return $val[0];	
		else
			return NULL;
	}else
		return $val;
}


function normalizePriceVM2(&$db, &$pr){
	global $calculator,$default_shopper_group_id;
	if(PLEM_VM_RUN == 2 && !$pr->product_price){
		$pric_sql = "SELECT 
					*
				 FROM 
				#__virtuemart_product_prices as vpp
				WHERE
				vpp.virtuemart_product_id = ". $pr->virtuemart_product_id ."
				ORDER BY vpp.price_quantity_start, vpp.price_quantity_end, vpp.virtuemart_shoppergroup_id, vpp.virtuemart_product_price_id ASC LIMIT 0,1";			
		$db->setQuery($pric_sql);
		$dbpr = $db->loadObject();
		$pr->product_price            = $dbpr->product_price;
		$pr->product_override_price   = $dbpr->product_override_price;
		
		$pr->prices = plem_getProductPrices($pr);
		
	}
}



foreach(get_pelm_plugins_toinc("load") as $plg)
	include($plg);	

	
function get_customfieldsforall(&$db, $product_id,$virtuemartcustom_id){
	
	global $SETTINGS;
	
	if(!$product_id || !$virtuemartcustom_id)
		return NULL;
		
	$q= "SELECT cfa_v.customsforall_value_name, cfa_v.customsforall_value_label 
			FROM 
			#__virtuemart_custom_plg_customsforall_values AS cfa_v
			LEFT JOIN 
			#__virtuemart_product_custom_plg_customsforall AS cfa_cf ON cfa_cf.customsforall_value_id = cfa_v.customsforall_value_id
			WHERE 
			cfa_v.virtuemart_custom_id   = $virtuemartcustom_id
			AND 
			cfa_cf.virtuemart_product_id = $product_id;";
			
	$db->setQuery($q);		
	$res = $db->loadObjectList();		
	
	if(empty($res))
		return NULL;
	
	for($i = 0; $i < count($res); $i++){
		if(!trim($res[$i]->customsforall_value_label)){
			$res[$i] = $res[$i]->customsforall_value_name;
		}else{
			$res[$i] = $res[$i]->customsforall_value_name . ":" . $res[$i]->customsforall_value_label;
		}
	}
	
	return implode($SETTINGS->cf_val_separator, $res);
	
}

function set_customfieldsforall(&$db, $product_id,$virtuemartcustom_id, $product_custom_field_id, $value){
	
	
	
	
	
	
	global $SETTINGS;
	
	if(!$product_id || !$virtuemartcustom_id)
		return NULL;
	
	if(!trim($value) || !$product_custom_field_id){
		$q= "DELETE 
				cfa_cf 
			 FROM 
				#__virtuemart_custom_plg_customsforall_values AS cfa_v
			 LEFT JOIN 
				#__virtuemart_product_custom_plg_customsforall AS cfa_cf ON cfa_cf.customsforall_value_id = cfa_v.customsforall_value_id
			 WHERE 
				cfa_v.virtuemart_custom_id   = $virtuemartcustom_id
			 AND 
				cfa_cf.virtuemart_product_id = $product_id;";
			
		$db->setQuery($q);		
		$db->query();
	}else
		$value = explode($SETTINGS->cf_val_separator, $value);
	
	$q = "SELECT 
	        LOWER(cfa_v.customsforall_value_name) as customsforall_value_name_l,
			cfa_v.customsforall_value_name, 
			coalesce(cfa_v.customsforall_value_label,''), 
			cfa_v.customsforall_value_id 
		  FROM 
			#__virtuemart_custom_plg_customsforall_values AS cfa_v 
		  WHERE 
			cfa_v.virtuemart_custom_id   = $virtuemartcustom_id"; 
	
	$db->setQuery($q);		
	$existing_values = $db->loadObjectList('customsforall_value_name_l');
	
	
	
	$create    = array();
	$final_ids = array();
	
	//$add_ids   = array();
	for($i = 0; $i < count($value); $i++){
		$t = explode(":",$value[$i]);
		if(!trim($t[0]))
			continue;
			
		if(count($t) > 1){
			$value[$i] = array(
				'name'  => stripslashes(trim($t[0])),
				'label' => stripslashes(trim($t[1]))
			);
		}else{
			$value[$i] = array(
				'name'  => stripslashes(trim($t[0])),
				'label' => ''
			);
		}
		
		if(isset($existing_values[strtolower($value[$i]["name"])])){
			if($value[$i]["label"] != $existing_values[strtolower($value[$i]["name"])]->customsforall_value_label){
				$q = "UPDATE #__virtuemart_custom_plg_customsforall_values SET customsforall_value_label = " .$db->quote($value[$i]["label"]) ." WHERE customsforall_value_id = " . $existing_values[strtolower($value[$i]["name"])]->customsforall_value_id;
				$db->setQuery($q);		
				$db->query();
			}
			
			
			$final_ids[] = $existing_values[strtolower($value[$i]["name"])]->customsforall_value_id;
			$value[$i]["cvid"] =  $existing_values[strtolower($value[$i]["name"])]->customsforall_value_id;
			
		}else{
			$q = "INSERT INTO #__virtuemart_custom_plg_customsforall_values
					(customsforall_value_id, customsforall_value_name, customsforall_value_label, virtuemart_custom_id, ordering) 
				  VALUES 
					(NULL,".$db->quote($value[$i]["name"]).",".$db->quote($value[$i]["label"]).",$virtuemartcustom_id,$i)";
			$db->setQuery($q);		
            $db->query();
			$cv_id = $db->insertid();
			if($cv_id){
				
				$final_ids[]       = $cv_id;
				$value[$i]["cvid"] =  $cv_id;
				
			}
		}
	}
	
	$q= "DELETE 
				cfa_cf 
		 FROM 
				#__virtuemart_custom_plg_customsforall_values AS cfa_v
			 LEFT JOIN 
				#__virtuemart_product_custom_plg_customsforall AS cfa_cf ON cfa_cf.customsforall_value_id = cfa_v.customsforall_value_id
			 WHERE 
				cfa_v.virtuemart_custom_id   = $virtuemartcustom_id
			 AND 
				cfa_cf.virtuemart_product_id = $product_id";
	
	if(!empty($final_ids)){
		$q .= " AND NOT cfa_cf.customsforall_value_id IN (" . implode(",", $final_ids ) . ")"; 
		$db->setQuery($q);		
        $db->query();
	}
	
	$q= "SELECT 
			 cfa_cf.customsforall_value_id 
		 FROM 
				#__virtuemart_custom_plg_customsforall_values AS cfa_v
			 LEFT JOIN 
				#__virtuemart_product_custom_plg_customsforall AS cfa_cf ON cfa_cf.customsforall_value_id = cfa_v.customsforall_value_id
			 WHERE 
				cfa_v.virtuemart_custom_id   = $virtuemartcustom_id
			 AND 
				cfa_cf.virtuemart_product_id = $product_id";

	$db->setQuery($q);	
	$curr = $db->loadColumn();
	
	foreach($final_ids as $cvid){
		if(!in_array($cvid,$curr)){
			
			$q = "INSERT INTO #__virtuemart_product_custom_plg_customsforall(id, customsforall_value_id, virtuemart_product_id, customfield_id) 
				  VALUES (NULL,$cvid,$product_id,$product_custom_field_id);";
			$db->setQuery($q);		
			$db->query();
			$db->insertid();	
		}		
	}
	
	return true;
}

function vmel_getProduct($pr_id,&$productModel,&$db,&$custom_fields,&$cat_asoc,&$man_asoc){
	 global $SETTINGS, $has_gtinmpn;
	 global $catway_asoc, $catway_asoc_reverse, $taxs_asoc, $disc_asoc, $curr_asoc;
	 
	 
	 $forexport  = isset($_REQUEST["do_export"]);
	 $pr = null;
	 if(PLEM_VM_RUN > 2)
		$pr = $productModel->getProduct($pr_id,false,true,false); 
	 else
		$pr = $productModel->getProduct($pr_id,false,true,false); 
	 
	 if(!$pr->virtuemart_product_id)	
		 return NULL;
	
	
	 $prod = new stdClass();
	 $prod->virtuemart_product_id      = $pr->virtuemart_product_id;
	 if(!$SETTINGS->par_ch_stick_disable){
		$prod->parent                     = $pr->product_parent_id ? $pr->product_parent_id : NULL;          
	 }
	 $prod->product_sku                = $pr->product_sku ? $pr->product_sku : '' ;
	 $prod->slug                       = $pr->slug ? $pr->slug : '';
	 $prod->virtuemart_manufacturer_id = singleVal($pr->virtuemart_manufacturer_id);
	 $prod->categories                 = $pr->categories;
	 

	 if(PLEM_VM_RUN > 2)
		$q = "SELECT virtuemart_customfield_id, virtuemart_custom_id, customfield_value as custom_value , customfield_price as custom_price FROM #__virtuemart_product_customfields WHERE virtuemart_product_id = ".$prod->virtuemart_product_id." order by ordering;";
	 else 
		$q = "SELECT virtuemart_customfield_id, virtuemart_custom_id, custom_value, custom_price FROM #__virtuemart_product_customfields WHERE virtuemart_product_id = ".$prod->virtuemart_product_id." order by ordering;";
	 
	 $db->setQuery($q);	
	 $product_custom_fields = $db->loadObjectList();
	 
	 foreach($product_custom_fields as $pcf){
			
		if(isset($custom_fields[$pcf->virtuemart_custom_id])){
			$cf = $custom_fields[$pcf->virtuemart_custom_id];
			$filed_name = "custom_field_".$cf['virtuemart_custom_id'];
			if($forexport){
				$filed_name = cf_field_name($cf);
			}
			
			if(!property_exists($prod,$filed_name))
				$prod->{$filed_name} = null;
			
			if($cf["custom_element"] == "customfieldsforall"){
			    $prod->{$filed_name} = get_customfieldsforall($db, $prod->virtuemart_product_id, $pcf->virtuemart_custom_id); 
				continue;
			}
			
			if($cf['virtuemart_custom_id'] == $pcf->virtuemart_custom_id){
				if($prod->{$filed_name} === null)
					$prod->{$filed_name} = "";
				
				if( $cf['is_cart_attribute'] && $pcf->custom_price)
					$prod->{$filed_name} .= (($prod->{$filed_name} ? $SETTINGS->cf_val_separator : "") . $pcf->custom_value .":".round($pcf->custom_price ,2));
				else
					$prod->{$filed_name} .= (($prod->{$filed_name} ? $SETTINGS->cf_val_separator : "") . $pcf->custom_value );
			}
		}else if($forexport){
			$filed_name = "custom_field_".$pcf->virtuemart_custom_id;
			$cf = $custom_fields[$pcf->virtuemart_custom_id];
			if($forexport){
				$filed_name = cf_field_name($cf);
			}
			$prod->{$filed_name} = null;
		}	
	 }
	 
	 //$productModel->addImages(array($pr));
	 
	 if($forexport){
		 $man_id = singleVal($prod->virtuemart_manufacturer_id);
	     if(isset($prod->virtuemart_manufacturer_id))  
			if(isset($man_asoc[intval($man_id)]))
				$prod->manufacturer_name = $man_asoc[intval($man_id)];
			else	
				$prod->manufacturer_name = "";
		 else	
			$prod->manufacturer_name = "";

		 $cnames = array();
		 foreach($prod->categories as $c){
			$cn = $catway_asoc[intval($c)];
			if($cn)
				$cnames[] = $cn; 
		 }	
			
		 $prod->categories_names     = implode($SETTINGS->cf_val_separator ? $SETTINGS->cf_val_separator : "," ,$cnames);
		 
		 unset($prod->categories);
		 unset($prod->virtuemart_manufacturer_id);
		 
	 }
	 
	$prod->product_name               = $pr->product_name;
	$prod->product_in_stock           = $pr->product_in_stock;  
	$prod->product_ordered            = $pr->product_ordered;  
	
	if(is_array($pr->prices)){
		$prod->product_price          = isset($pr->prices["costPrice"]) ? $pr->prices["costPrice"] : $pr->prices["product_price"];
		$prod->product_tax_id         = isset($pr->prices["product_tax_id"]) ? $pr->prices["product_tax_id"] : 0;
		$prod->product_discount_id    = isset($pr->prices["product_discount_id"]) ? $pr->prices["product_discount_id"] : 0;
		$prod->product_currency       = isset($pr->prices["product_currency"]) ? $pr->prices["product_currency"] : null;
	}else{
		
		$prod->product_price          = $pr->product_price;
		if(!$prod->product_price){
			normalizePriceVM2($db, $pr);
			$prod->product_price      = $pr->product_price;
		}

		$prod->product_tax_id         = $pr->product_tax_id ? $pr->product_tax_id : 0;
		$prod->product_discount_id    = $pr->product_discount_id ? $pr->product_discount_id : 0;
		$prod->product_currency       = $pr->product_currency ? $pr->product_currency : null;
		
	}
	
	if($forexport){
		if(!$prod->product_tax_id)
			$prod->product_tax = NULL;
        else
			$prod->product_tax = $taxs_asoc[$prod->product_tax_id];
        unset($prod->product_tax_id);

		if(!$prod->product_discount_id)
			$prod->product_discount = NULL;
        else
			$prod->product_discount = $disc_asoc[$prod->product_discount_id];
        unset($prod->product_discount_id);
		
		if(!$prod->product_currency)
			$prod->product_currency = NULL;
        else
			$prod->product_currency = $curr_asoc[$prod->product_currency];
    }
	
	if(PLEM_VM_RUN > 2){
		$prod->product_override_price     = 0;
		if(isset($pr->prices["override"])){
			if(intval($pr->prices["override"]) !== 0)
				$prod->product_override_price = $pr->prices["product_override_price"];
		}
	}else
		$prod->product_override_price     = $pr->override !== 0 ? $pr->product_override_price : 0;
	
	$prod->product_sales_price        = $pr->prices["salesPrice"];  
	
	if($forexport && $SETTINGS->german_numbers){
		$prod->product_price              = toGerFMT($prod->product_price);
		$prod->product_override_price     = toGerFMT($prod->product_override_price);
		$prod->product_sales_price        = toGerFMT($prod->product_sales_price);
	}else{
		$prod->product_price              = jsnum($prod->product_price);
		$prod->product_override_price     = jsnum($prod->product_override_price);
		$prod->product_sales_price        = jsnum($prod->product_sales_price);
	}
	
	$prod->published                  = $pr->published ? true : false;
	$prod->product_special            = $pr->product_special ? true : false;
	$prod->product_s_desc             = stripslashes($pr->product_s_desc);
		
	$prod->metadesc                   = $pr->metadesc ;
	$prod->metakey                    = $pr->metakey ;
	$prod->customtitle                = $pr->customtitle ;
	 
	$prod->product_weight       = $pr->product_weight;
	$prod->product_weight_uom   = $pr->product_weight_uom;
	$prod->product_length       = $pr->product_length;
	$prod->product_width        = $pr->product_width;
	$prod->product_height       = $pr->product_height;      
	$prod->product_lwh_uom      = $pr->product_lwh_uom; 
	$prod->product_packaging    = $pr->product_packaging;
	$prod->product_unit         = $pr->product_unit;
	
	$prod->metarobot            = $pr->metarobot;
    $prod->metaauthor			= $pr->metaauthor;
	
	$prod->product_url                = $pr->product_url;

	if($has_gtinmpn){
		$prod->product_gtin           = $pr->product_gtin;
		$prod->product_mpn            = $pr->product_mpn;
	}
	
	if(!$forexport){
		
		$prod->i_id  = $pr->virtuemart_product_id;
		$prod->c_id  = $pr->virtuemart_product_id;
		$prod->link           		= str_ireplace('/administrator','',JRoute::_($pr->link,false));
	    
		
		if($SETTINGS->prices){
			$prod->prices = array();
			
			$allPrices = null;
			
			if(!isset($pr->allPrices)){
				$pric_sql = "SELECT 
								*
							 FROM 
							#__virtuemart_product_prices as vpp
							WHERE
							vpp.virtuemart_product_id = ". $prod->virtuemart_product_id 
							//." coalesce(vpp.price_quantity_start,0) + coalesce(vpp.price_quantity_end,0) = 0 AND (coalesce(vpp.virtuemart_shoppergroup_id,0) = 0 OR coalesce(vpp.virtuemart_shoppergroup_id,0) = $default_shopper_group_id) "
							." ORDER BY vpp.price_quantity_start, vpp.price_quantity_end, vpp.virtuemart_shoppergroup_id, vpp.virtuemart_product_price_id ASC LIMIT 0,999";			
				$db->setQuery($pric_sql);			
				$allPrices = $db->loadObjectList();
			}else{
				$allPrices = $pr->allPrices;
			}
			
			if(isset($pr->virtuemart_product_price_id))
				$def_price_id = $pr->virtuemart_product_price_id;
			elseif(isset($pr->prices["virtuemart_product_price_id"]))
				$def_price_id = $pr->prices["virtuemart_product_price_id"];
			
			global $calculator;
			ob_start();
			foreach ($allPrices as $k => $sPrices) {
				if(PLEM_VM_RUN < 3)
					$sPrices = (array)$sPrices;
				
				if(isset($sPrices["virtuemart_product_price_id"])){
					if($def_price_id == $sPrices["virtuemart_product_price_id"])
						continue;
				}
				
				$price = null;
				
				if(PLEM_VM_RUN < 3){
					if(count($sPrices) == 0) continue;
					if (empty($sPrices['virtuemart_product_price_id'])) {
						$sPrices['virtuemart_product_price_id'] = '';
					}
					
					$tmp_pr = (object)array_merge ((array)$pr, $sPrices);
					$calsPrices = plem_getProductPrices ($tmp_pr);
					$price = array_merge($sPrices,(array)$calsPrices);
					
				}else{	
					$pr->selectedPrice = $k;
					$calsPrices = plem_getProductPrices ($pr);
					$price = array_merge($pr->allPrices[$k],$calsPrices);
				}
				
				if(!is_array($price))
					continue;
				
				if(!isset($price["virtuemart_product_price_id"]))
					continue;
			
				$p = new stdClass;
				$p->pp_id               = $price["virtuemart_product_price_id"];
				$p->sg_id               = $price["virtuemart_shoppergroup_id"];
				$p->price               = $price["product_price"];
				
				$p->price_override      = intval($price["override"]) ? $price["product_override_price"] : null;
				
				$p->q_start             = $price["price_quantity_start"];
				$p->q_end               = $price["price_quantity_end"];
				$p->sales_price         = $price["salesPriceTemp"];
				$p->product_tax_id      = $price["product_tax_id"] ? jsnum($price["product_tax_id"]) : 0;
				$p->product_discount_id = $price["product_discount_id"] ? jsnum($price["product_discount_id"]) : 0;
				$p->product_currency    = $price["product_currency"] ? jsnum($price["product_currency"]) : null;

				$p->price           = jsnum($p->price);
				$p->price_override  = jsnum($p->price_override); 
				$p->sales_price     = jsnum($p->sales_price);
				
					
				if(!intval($p->q_start) && !intval($p->q_end) && !intval($p->sg_id)){
					if(!isset($prod->product_price)){
						$prod->product_price          = $p->price;
						$prod->product_sales_price    = $p->sales_price;
						$prod->product_override_price = $p->price_override;
					}
				}else
					$prod->prices[]     = $p;
				
			}
			ob_end_clean();
		}
	}
	
	$ce_cols = array();
	if($forexport){
		$ce_cols = explode(",",$SETTINGS->custom_export_columns);
		
		if($SETTINGS->export_images || (in_array('i_id', $ce_cols) !== false && $SETTINGS->custom_export)){
			$iquery="SELECT file_url, file_title, file_description, file_meta FROM #__virtuemart_product_medias AS pm JOIN #__virtuemart_medias AS vm ON vm.virtuemart_media_id=pm.virtuemart_media_id WHERE virtuemart_product_id=".$pr_id." ";
			$db->setQuery($iquery);
			$images = $db->loadObjectList();
			$images_array=array();
			
			foreach($images as $img){
				$img_entry = JURI::root().$img->file_url;
				
				if(basename($img->file_url) == "-1")
					continue;
				
				if($SETTINGS->export_images_meta){
					$img_entry .= ("|" . $img->file_title);
					$img_entry .= ("|" . $img->file_description);
					$img_entry .= ("|" . $img->file_meta);
				}
				
				$images_array[]= $img_entry;
			}
			
			$prod->images = implode($SETTINGS->cf_val_separator  ? $SETTINGS->cf_val_separator : ",",$images_array);
		}
		
		if($SETTINGS->export_content || (in_array('c_id', $ce_cols) !== false && $SETTINGS->custom_export)){
			
			$prod->product_desc = '' . str_ireplace('"',"''",$pr->product_desc) . '';// encodeCSVContent($pr->product_desc);
			
			
			//if(!strip_tags($prod->product_desc))
			//	$prod->product_desc = null;
			//$prod->product_desc = trim(preg_replace('/\s\s+/', ' ', $prod->product_desc));
		}
		
		//if($prod->product_s_desc){
		//	$prod->product_s_desc = '' . str_ireplace('"',"''",$prod->product_s_desc) . '';//encodeCSVContent($prod->product_s_desc);
		//}
	}
	
	foreach(get_pelm_plugins_toinc("get_product") as $plg)
		include($plg);
	
	unset($pr);
	
	if($SETTINGS->custom_export && $forexport){
		$prod2 = new stdClass();
		foreach($ce_cols as $col){
			
			if(strpos($col,"custom_field_") === 0){
				global $cf_col_cache;
				if(!isset($cf_col_cache))
					$cf_col_cache = array();
				
				if(!isset($cf_col_cache[$col])){
					$cf_id = str_ireplace("custom_field_","",$col);
					foreach($custom_fields as $cf){
						if($cf_id == $cf['virtuemart_custom_id']){
							$cf_col_cache[$col] = cf_field_name($cf);
							break;
						} 
					}	
				}
				
				if(isset($cf_col_cache[$col])){
					$col = $cf_col_cache[$col];
				}
				
			}
			
			if($col == "i_id")
				$col = "images";
			elseif($col == "c_id")
				$col = "product_desc";
			elseif($col == "categories")
				$col= "categories_names";	
			elseif($col == "virtuemart_manufacturer_id")
				$col= "manufacturer_name";	
			elseif($col == "product_discount_id")
				$col= "product_discount";	
			elseif($col == "product_tax_id")
				$col= "product_tax";	
			
			$prod2->{$col} = $prod->{$col};
		}
		return $prod2;
	}

	return $prod;
}

///////////////////////////////////////////////////////////////////////////////////////


function Getfloat($str) { 
  global $SETTINGS;
  if($SETTINGS->german_numbers){
	  if(strstr($str, ".")) { 
		$str = str_replace(".", "", $str); // replace ',' with '.' 
	  }
	  $str = str_replace(",", ".", $str);  
  }else{
	  if(strstr($str, ",")) { 
		$str = str_replace(",", "", $str); // replace ',' with '.' 
	  }
  }
  return floatval($str);
}; 


function toGerFMT($val){
	if(!$val)
		return NULL;
	$str = floatval($val).'';
	return str_replace(".", ",", $str);  
}

function default_val($val,$default){
   if($val === null)
     return $default;
   if(!isset($val))
     return $default;
   if(strlen($val) === 0)
     return $default;
   return $val;	 
};

function default_val_num($val,$default){
   if($val === null)
     return $default;
   if(!isset($val))
     return $default;
   if(strlen($val) === 0)
     return $default;
	 
   return Getfloat($val);	 
};



function dbnum($val){
	global $db;
	if(!$val)
		return "NULL";
	else if(!is_numeric($val))
		return "NULL";
	else
		return $db->quote(floatval($val));
};

function createClone($id,&$db){
	global $productModel;
	
	if(PLEM_VM_RUN < 3)
		$product = $productModel->getProduct ($id, FALSE, TRUE, FALSE);
	else	
		$product = $productModel->getProduct ($id, FALSE, FALSE, FALSE);
	
	$q = "SELECT * FROM `#__virtuemart_product_customfields`";
	$q .= " WHERE `virtuemart_product_id` = " . $id;
	$db->setQuery ($q);
	$customfields = $db->loadAssocList ();
	
	if ($customfields) {
		foreach ($customfields as &$customfield) {
			unset($customfield['virtuemart_product_id'], $customfield['virtuemart_customfield_id']);
		}
	}
	
	$product->field = $customfields;
	$product->virtuemart_product_id = $product->virtuemart_product_price_id = 0;
	$product->slug = $product->slug . '-' . $id;
	if(PLEM_VM_RUN < 3){
		$product->save_customfields = 1;
	}else{
		////////////////////////////////////////////////////////////////////////////
		$q = "SELECT * FROM `#__virtuemart_product_prices`";
		$q .= " WHERE `virtuemart_product_id` = " . $id;
		$db->setQuery ($q);
		$prices = $db->loadAssocList ();
		if ($prices) {
			foreach ($prices as $k => &$price) {
				unset($price['virtuemart_product_id'], $price['virtuemart_product_price_id']);
				if(empty($mprices[$k])) $mprices[$k] = array();
				foreach ($price as $i => $value) {
					if(empty($mprices[$i])) $mprices[$i] = array();
					$mprices[$i][$k] = $value;
				}
			}
		}
		////////////////////////////////////////////////////////////////////////////
		$product->mprices = $prices;
		$product->originId = $id;
	}
	
	$tok = vRequest::getFormToken(true);
	$_REQUEST["token"] = $tok;
	
	if(PLEM_VM_RUN < 3){
		$productModel->store ($product);
		$newId = $productModel->_id;
	}else{
		$newId = $productModel->store ($product);
	}
	
	return $newId;	
}

if(isset($_REQUEST['DO_UPDATE'])){
if($_REQUEST['DO_UPDATE'] == '1' && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
	$json = file_get_contents('php://input');
	$tasks = json_decode($json);
	
	$res = array();
	$temp = '';
	
	foreach($tasks as $key => $task){
       $return_added = false;  
       $res_item = new stdClass();
	   $res_item->returned = new stdClass();
	   $res_item->returned->dg_index = $task->dg_index;
	   $pr =  NULL;
	   
	   $sKEY = "".$key;
	   if($sKEY[0] == 's'){
			if(isset($SETTINGS->surogates[$sKEY]))
				$key = $SETTINGS->surogates[$sKEY];
			else{	
				$parent_id = null;
				if(isset($task->parent))
					$parent_id = $task->parent;
				
				$key = vmel_addProduct($db,$vm_languages,$user->id, $parent_id);
				$SETTINGS->surogates[$sKEY] = $key;
				SaveSettings($db, $SETTINGS);
			}
			$return_added = true;
	   }
	   
	   $res_item->virtuemart_product_id = $key;
	   $res_item->success = true;
	   
	   if(isset($task->DO_DELETE)){
	     if($task->DO_DELETE === 'delete'){
		 
		    $tables = array();
			$tables[] = '#__virtuemart_product_categories';
			$tables[] = '#__virtuemart_product_customfields';
			
			if(PLEM_VM_RUN < 3) $tables[] = '#__virtuemart_product_downloads';
			$tables[] = '#__virtuemart_product_manufacturers';
			$tables[] = '#__virtuemart_product_medias';
			$tables[] = '#__virtuemart_product_prices';
			if(PLEM_VM_RUN < 3) $tables[] = '#__virtuemart_product_relations';
			$tables[] = '#__virtuemart_product_shoppergroups';
			$tables[] = '#__virtuemart_products';
			
		 
		    foreach($vm_languages as $lng => $db_suffix)
				$tables[] = ('#__virtuemart_products_'.$db_suffix);
            
			foreach($tables as $ind => $tbl){
				$db->setQuery("DELETE FROM ".$tbl." WHERE virtuemart_product_id = ".$key);
				$db->query();
			}
				
			$res[] = $res_item;
			continue;
		 }
	   }
	   
	   if(isset($task->DO_CLONE)){
		   if($task->DO_CLONE == 'clone'){
			   
			   $cid = createClone($key,$db);
			   if($cid){
				   $res_item->clones   = array();
				   $res_item->clones[] = vmel_getProduct($cid,$productModel,$db,$custom_fields,$cat_asoc,$man_asoc);
				   //NO CHILDREN 
			   }
			   
			   $res[] = $res_item;
			   continue;
		   }
	   }
	   
	   $upd_prop = array();
	   $p_par = NULL;
	    
	   
	   
	   $db->setQuery("SELECT * FROM #__virtuemart_products WHERE virtuemart_product_id = ".$key);
	   $prod_info = $db->loadObject();
	   $arrtask = get_object_vars($task);
	   
	   foreach($arrtask as $pname => $pvalue ){
		   if($task->{$pname}){
			   if(is_string($task->{$pname}))
				   $task->{$pname} = addslashes($task->{$pname});
		   }
	   }
	   
	   if($prod_info->product_parent_id){
		$p_par = $productModel->getProduct($prod_info->product_parent_id,false,true,false);	
	   }
	   
	   if(isset($task->product_sku)) $upd_prop[] = " p.product_sku = ". $db->quote($task->product_sku) . " ";
	   
	   if(isset($task->published)) $upd_prop[] = " p.published = ". $db->quote($task->published ? "1" : "0" ) . " ";
	   if(isset($task->product_special)) $upd_prop[] = " p.product_special = ".$db->quote($task->product_special ? "1" : "0") . " ";
	   if(isset($task->product_in_stock)) $upd_prop[] = " p.product_in_stock = ". default_val_num($task->product_in_stock,"0")  . " ";
	   if(isset($task->product_ordered)) $upd_prop[] = " p.product_ordered = ". default_val_num($task->product_ordered,"0")  . " ";
	   
	   if(isset($task->product_url))  $upd_prop[]  = "p.product_url = ".$db->quote($task->product_url)."";
	   
	   if($has_gtinmpn){
		   if(isset($task->product_gtin)) $upd_prop[]  = "p.product_gtin = ".$db->quote($task->product_gtin)."";
		   if(isset($task->product_mpn))  $upd_prop[]  = "p.product_mpn = ".$db->quote($task->product_mpn)."";
	   }
	   
	   if(isset($task->product_name)) $upd_prop[] = " pl.product_name    = ". $db->quote( stripslashes($task->product_name) )  . " ";
	   if(isset($task->product_s_desc)) $upd_prop[] = " pl.product_s_desc    = ". $db->quote( stripslashes($task->product_s_desc))  . " ";
	   
	   
	   if(isset($task->metadesc)) $upd_prop[] = " pl.metadesc = ".$db->quote($task->metadesc)  . " ";
	   if(isset($task->metakey)) $upd_prop[] = " pl.metakey = ".$db->quote($task->metakey)  . " ";
	   if(isset($task->customtitle)) $upd_prop[] = " pl.customtitle  = ". $db->quote(stripslashes($task->customtitle))  . " ";
	   
	   
	   
	   if(isset($task->slug)) $upd_prop[] = " pl.slug            = ". $db->quote($task->slug)  . " ";
	   
	   $any_price_set = false;
	   
	   if(isset($task->product_tax_id)){
		   if($task->product_tax_id)
			   $upd_prop[] = " pr_p.product_tax_id = ". dbnum($task->product_tax_id)  . " " ;
		   else
			   $upd_prop[] = " pr_p.product_tax_id = 0 " ;
		   $any_price_set = true;
	   }

	   if(isset($task->product_discount_id)){
		   if($task->product_discount_id)
			   $upd_prop[] = " pr_p.product_discount_id = ". dbnum($task->product_discount_id)  . " " ;
		   else
			   $upd_prop[] = " pr_p.product_discount_id = 0 " ;
		   $any_price_set = true;
	   }

	   if(isset($task->product_currency)){
		   if($task->product_currency)
			   $upd_prop[] = " pr_p.product_currency = ". dbnum($task->product_currency)  . " " ;
		   else
			   $upd_prop[] = " pr_p.product_currency = NULL " ;
		   $any_price_set = true;
	   }
	   
	   if(!isset($task->product_sales_price) && isset($task->product_price)){ 
			$upd_prop[] = " pr_p.product_price = ". dbnum($task->product_price)  . " " ;
			$any_price_set = true;
	   }
	   
	   if(isset($task->product_override_price)){
	       if(!$task->product_override_price){
		    $upd_prop[] = " pr_p.product_override_price = null " ;
		    $upd_prop[] = " pr_p.override = 0 ";
		   }else{
			$upd_prop[] = " pr_p.product_override_price = ". dbnum($task->product_override_price)  . " " ;
			$upd_prop[] = " pr_p.override = " . $SETTINGS->override_price;
		   }
		   $any_price_set = true;
	   }
	   
	   if(isset($task->product_sales_price)){
			global $calculator;
			$pdata = array();
			$pdata["salesPrice"] = $task->product_sales_price;
			ob_start();
			$product_price = $calculator->calculateCostprice ($key, $pdata);
			ob_end_clean();
			$upd_prop[] = " pr_p.product_price = ". dbnum($product_price) . " " ;
			$res_item->returned->product_price = jsnum($product_price);
			$any_price_set = true;
	   }

       		
	   
	   if($any_price_set && $prod_info->product_parent_id){
			fix_prices_table( $db, $user->id, $key, false); 	
	   }
	   
	   if(isset($task->product_weight)) $upd_prop[] = " p.product_weight = ".dbnum($task->product_weight) . " ";
	   if(isset($task->product_weight_uom)) $upd_prop[] = " p.product_weight_uom = ".$db->quote($task->product_weight_uom) . " ";
	   if(isset($task->product_length)) $upd_prop[] = " p.product_length = ".dbnum($task->product_length) . " ";
	   if(isset($task->product_width)) $upd_prop[] = " p.product_width = ".dbnum($task->product_width) . " ";
	   if(isset($task->product_height)) $upd_prop[] = " p.product_height = ".dbnum($task->product_height) . " ";
	   if(isset($task->product_lwh_uom)) $upd_prop[] = " p.product_lwh_uom = ".$db->quote($task->product_lwh_uom) . " ";
	   if(isset($task->metarobot)) $upd_prop[] = " p.metarobot = ". $db->quote($task->metarobot) . " ";
	   if(isset($task->metaauthor)) $upd_prop[] = " p.metaauthor = ".$db->quote($task->metaauthor) . " ";
	   
	   if(isset($task->product_packaging)) $upd_prop[] = " p.product_packaging = ".dbnum($task->product_packaging) . " ";
	   if(isset($task->product_unit)) $upd_prop[] = " p.product_unit = ".$db->quote($task->product_unit) . " ";
	   
	   $dispatcher = null;
	   try{  
	        $task->virtuemart_product_id = $key;
			JPluginHelper::importPlugin('vmcustom');
			JPluginHelper::importPlugin('vmextended');
			$dispatcher = JDispatcher::getInstance();
			if($dispatcher)
				$dispatcher->trigger('plgVmBeforeStoreProduct',array(&$task, &$pr));
	   }catch(Exception $ps_ex){
		
	   }
	   
	   if(count($upd_prop)){
		   $u_query = "UPDATE 
						   #__virtuemart_products as p
						LEFT JOIN
						   #__virtuemart_product_prices as pr_p on pr_p.virtuemart_product_id = p.virtuemart_product_id AND (coalesce( pr_p.price_quantity_start,0) + coalesce(pr_p.price_quantity_end,0) = 0) AND (NOT coalesce(pr_p.virtuemart_shoppergroup_id,0) > 0 OR coalesce(pr_p.virtuemart_shoppergroup_id,0) = $default_shopper_group_id)  
						LEFT JOIN
						   #__virtuemart_products_".$vm_lang." as pl on pl.virtuemart_product_id = p.virtuemart_product_id
						SET
						
						  ". implode(",",$upd_prop) ."
						  
						WHERE p.virtuemart_product_id = ".$key.";";
		  
		   $db->setQuery($u_query);
		   $res_item->success = $res_item->success && $db->query();
	   }
	   
	   try{ 
			if($dispatcher)
				$dispatcher->trigger('plgVmAfterStoreProduct',array(&$task, &$pr));
	   }catch(Exception $ps_ex){
		
	   }
	   
	   if(isset($task->virtuemart_manufacturer_id)){
			if($task->virtuemart_manufacturer_id){
				$db->setQuery("SELECT count(*) as `exists` FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id = ".$key);
				if($db->loadObject()->exists){
					$db->setQuery("UPDATE #__virtuemart_product_manufacturers SET virtuemart_manufacturer_id = ".$task->virtuemart_manufacturer_id." WHERE virtuemart_product_id = ".$key);
				}else{
					$db->setQuery("INSERT INTO #__virtuemart_product_manufacturers(id ,virtuemart_product_id,virtuemart_manufacturer_id) VALUES (NULL,".$key.",".$task->virtuemart_manufacturer_id.")");
				}
				$res_item->success = $res_item->success && $db->query();
			}else{
				$db->setQuery("DELETE FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id = ".$key);
				$res_item->success = $res_item->success && $db->query();
			}
			
			if( $p_par ){
				if(is_array($p_par->virtuemart_manufacturer_id))
					$p_par->virtuemart_manufacturer_id = $p_par->virtuemart_manufacturer_id[0];
				
				if(!$task->virtuemart_manufacturer_id || $task->virtuemart_manufacturer_id == $p_par->virtuemart_manufacturer_id){
					$db->setQuery("DELETE FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id = ".$key);
					$db->query();
					$task->virtuemart_manufacturer_id = $p_par->virtuemart_manufacturer_id;
					$res_item->returned->virtuemart_manufacturer_id = $task->virtuemart_manufacturer_id;
				}
			}
	   }
	   
	   if(isset($task->categories)){
		    if(!is_array($task->categories))
				$task->categories = explode(",",trim($task->categories));
		   
			$task->categories = array_map("intval",$task->categories);
		   
		    if(trim(implode(",",$task->categories)))
				$db->setQuery("DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id = ".$key.  " AND NOT virtuemart_category_id in ( ". implode(",",$task->categories) . ")");
            else
				$db->setQuery("DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id = ".$key);
		   
	  	    
			$db->query();
			
            $db->setQuery("SELECT virtuemart_category_id FROM #__virtuemart_product_categories WHERE virtuemart_product_id = ".$key);
  			
			$cur_cats_o = $db->loadObjectList();
			$cur_cats = array();
			
			foreach($cur_cats_o as $cat)
			   $cur_cats[] = $cat->virtuemart_category_id;
			
			$cats = $task->categories;
			
			foreach($cats as $c){
			  if($c){
				  if(!in_array($c,$cur_cats)){
					$db->setQuery("INSERT INTO #__virtuemart_product_categories(id,virtuemart_product_id,virtuemart_category_id,ordering) VALUES (NULL,".$key.",".$c.",0)");
					$db->query();
				  }
			  }
			}
			
			if( $p_par ){
				
				if(!$task->categories){
					$task->categories = $p_par->categories;
					$res_item->returned->categories = $task->categories;
				}else{
					asort($task->categories);
					asort($p_par->categories);
					if(implode(",",$task->categories) == implode(",",$p_par->categories)){
						 $db->setQuery("DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id = ".$key);
						 $db->query();
					}
				}
			}
	   }
	   
	   foreach($custom_fields as $cf_id => $cf){
	     if(isset($task->{"custom_field_".$cf_id})){
			 
			 
		    if($task->{"custom_field_".$cf_id} === null || $task->{"custom_field_".$cf_id} === ''){
				
				 
				$Q = "DELETE FROM #__virtuemart_product_customfields
					  WHERE
					   virtuemart_product_id = ".$key."
 					  AND
					   virtuemart_custom_id  = ".$cf_id;
				$db->setQuery($Q);	  
			    $res_item->success = $res_item->success && ($db->query() !== false);
				
				if($cf["custom_element"] == "customfieldsforall"){
					$res_item->success = $res_item->success && !!set_customfieldsforall($db, $key, $cf_id ,NULL , NULL);
				}
				
			}else{ 
				 $values = explode($SETTINGS->cf_val_separator,stripslashes($task->{"custom_field_".$cf_id}));
				 
				 if($cf["custom_element"] == "customfieldsforall"){
					$values = array('customfieldsforall');
				 }	
				 
				 $Q = "SELECT virtuemart_customfield_id FROM #__virtuemart_product_customfields
						WHERE
						   virtuemart_product_id = ".$key."
						AND
						   virtuemart_custom_id  = ".$cf_id;
				 
				 $db->setQuery($Q);
				 $existing = $db->loadObjectList();
				 
				 $last_pcf_id = NULL;
				 
				 for($I = 0; $I < count($values); $I++){
					$value = explode(":",$values[$I]);
					$price = 'NULL';
					if(count($value)> 1){
						$price = $value[1];
						$value = stripslashes($value[0]);
					}else
						$value = stripslashes($value[0]);
						
					if(!$price && $price !== '0')					
						$price = 'NULL';
					
					$order = $cf_id * 1000 + $I;
					
					if($price != 'NULL'){
						if(!is_numeric($price)){
						  $value .= (" : " . $price);
						  $price = 'NULL';
						}
					}
					
				  
					if($I < count($existing)){
						 
						 $Q = "UPDATE #__virtuemart_product_customfields
							SET
							   custom".(PLEM_VM_RUN > 2 ? "field" : "")."_value = ". $db->quote($value) .",
							   custom".(PLEM_VM_RUN > 2 ? "field" : "")."_price = ".dbnum($price).",
							   ordering = $order
							WHERE
							   virtuemart_customfield_id = ".$existing[$I]->virtuemart_customfield_id;
						 
						 $db->setQuery($Q);
						 $res_item->success = $res_item->success && ($db->query() !== false);
						 
						 $last_pcf_id = $existing[$I]->virtuemart_customfield_id;
						 
					}else{
						
						 $Q = "INSERT INTO #__virtuemart_product_customfields(
								   virtuemart_product_id, virtuemart_custom_id, custom".(PLEM_VM_RUN > 2 ? "field" : "")."_value ,custom".(PLEM_VM_RUN > 2 ? "field" : "")."_price
								,ordering) VALUES (".$key.",".$cf_id.",". $db->quote($value) .",".$db->quote($price).", $order)"; 
						 $db->setQuery($Q);	
						 $res_item->success = $res_item->success && ($db->query() !== false);
						 
						 $last_pcf_id = $db->insertid();
					}
				 }

				 for($I = count($values); $I < count($existing) ; $I++){
					$Q = "DELETE FROM #__virtuemart_product_customfields
						  WHERE
						   virtuemart_customfield_id = ".$existing[$I]->virtuemart_customfield_id;
						   
					$db->setQuery($Q);	  
					$res_item->success = $res_item->success && ($db->query() !== false);
				 }
				 
				 if($cf["custom_element"] == "customfieldsforall"){
					$res_item->success = $res_item->success && !!set_customfieldsforall($db, $key, $cf_id , $last_pcf_id , $task->{"custom_field_".$cf_id});
				 }	
				 
			}	
		 }
	   }
	   
	   if(isset($task->prices)){
	      $prices_ids = array();
		  $cprices_add        = array();
		  foreach($task->prices as $cprice){
			 if( strpos($cprice->pp_id,'s') === false){
				$prices_ids[] = $cprice->pp_id;
			 }else
				$cprices_add[] = &$cprice;
		  }
		 
		  //DELETE
		  $del_sql = "SELECT 
						d.virtuemart_product_price_id
					  FROM 
						#__virtuemart_product_prices as d
					  WHERE
						d.virtuemart_product_id = ". $key ."
						" . (!empty($prices_ids) ?	(" AND NOT d.virtuemart_product_price_id IN (". implode(",",$prices_ids) .") ") : "") . "
					     AND NOT (coalesce(d.price_quantity_start,0) + coalesce(d.price_quantity_end,0) = 0 AND (coalesce(d.virtuemart_shoppergroup_id,0) = 0 OR coalesce(d.virtuemart_shoppergroup_id,0) = $default_shopper_group_id))	
						
					  ORDER BY d.virtuemart_product_price_id ASC LIMIT 0,999";
		  $db->setQuery($del_sql);	
		  $delete = $db->loadObjectList();	
		  if(!empty($delete)){
		    $del_ids = array(); 
		    foreach($delete as $d){
				$del_ids[] = $d->virtuemart_product_price_id;
			}
			
			$del_sql = "DELETE FROM #__virtuemart_product_prices
						WHERE 
						virtuemart_product_price_id IN (".implode(",",$del_ids).")";
		    $db->setQuery($del_sql);	
		    $db->query();	
		  }
		  
		  $psurog = array();
		  //ADD
		  $model_price = null;
		  if(!empty($cprices_add)){
			$db->setQuery("SELECT 
								*
							FROM 
							  #__virtuemart_product_prices
							WHERE
							  virtuemart_product_id = ".$key."
							ORDER BY virtuemart_product_price_id ASC  
							LIMIT 1 ");
							
			$model_price = $db->loadObject();
			$model_price = get_object_vars($model_price);
			foreach($model_price as $name => $value){
				if($value === null)
					$model_price[$name] = "NULL";
				else
					$model_price[$name] = $db->quote($model_price[$name]);
			}
			
			foreach($cprices_add as $addprice){
				$addprice->surogate = $addprice->pp_id;
				$model_price["virtuemart_product_price_id"] = 0;
				$ins_q = "INSERT INTO #__virtuemart_product_prices (" . implode(",",array_keys($model_price)) . ") VALUES (" . implode(",",array_values($model_price)) . ");";	
				$db->setQuery($ins_q);	
				$db->query();	
				$addprice->pp_id = $db->insertid();
				$psurog[$addprice->pp_id] = $addprice->surogate;
				
			}
		  }
		  
		  //UPDATE
		  foreach($task->prices as $updateprice){
			 pelm_clearCache();
			 $upd = array();
			 if(!$updateprice->price_override)
				$updateprice->price_override = 0;
				
			 if($updateprice->price_override > 0){
				$upd[] = " product_override_price = " . dbnum($updateprice->price_override);
				$upd[] = " override = " . $SETTINGS->override_price;
			 }else{
				$upd[] = " product_override_price = null " ;
				$upd[] = " override = 0 ";
			 }
			 
			 if($updateprice->sales_price && ($updateprice->lastset == "sales" || !$updateprice->price)){
				
				global $calculator;
				$pdata = array();
				$pdata["salesPrice"] = $updateprice->sales_price;
				ob_start();
				$pprice = $calculator->calculateCostprice ($key, $pdata);
				ob_end_clean();
				$upd[] = " product_price = " . dbnum($pprice) . " ";
				
			 }else if($updateprice->price){
				$upd[] = " product_price = " . dbnum($updateprice->price) . " ";
			 }else{
				$upd[] = " product_price = null ";
			 }
			 
			 $upd_sql = "UPDATE #__virtuemart_product_prices
						 SET
							 virtuemart_shoppergroup_id = ".$updateprice->sg_id."
							,price_quantity_start       = ".($updateprice->q_start ? $updateprice->q_start : 0)."
							,price_quantity_end         = ".($updateprice->q_end ? $updateprice->q_end : 0)."
							,product_tax_id             = ".($updateprice->product_tax_id ? $updateprice->product_tax_id : 0)."
							,product_discount_id        = ".($updateprice->product_discount_id ? $updateprice->product_discount_id : 0)."
							,product_currency           = ".($updateprice->product_currency ? $updateprice->product_currency : "NULL")."
							,".implode(",", $upd )."
						 WHERE virtuemart_product_price_id = ". $updateprice->pp_id;
						 
			 $db->setQuery($upd_sql);	
			 $db->query();			 
		  }
		  
		  $ret_prices = array();
		 
		  if(PLEM_VM_RUN > 2)
			  $pr = $productModel->getProduct($key,false,true,false);
		  else	  
			  $pr = $productModel->getProduct($key,false,true,false);
		  
		  $allPrices = null;
		  
		  
		
		if(!isset($pr->allPrices)){
			$pric_sql = "SELECT 
							*
						 FROM 
						#__virtuemart_product_prices as vpp
						WHERE
						vpp.virtuemart_product_id = ". $key ."
						AND NOT (coalesce(vpp.price_quantity_start,0) + coalesce(vpp.price_quantity_end,0) = 0 AND (coalesce(vpp.virtuemart_shoppergroup_id,0) = 0 OR coalesce(vpp.virtuemart_shoppergroup_id,0) = $default_shopper_group_id))	
						ORDER BY vpp.price_quantity_start, vpp.price_quantity_end, vpp.virtuemart_shoppergroup_id, vpp.virtuemart_product_price_id ASC LIMIT 0,999";			
			$db->setQuery($pric_sql);			
			$allPrices = $db->loadObjectList();
		}else{
			$allPrices = $pr->allPrices;
		}
		
		if(isset($pr->virtuemart_product_price_id))
			$def_price_id = $pr->virtuemart_product_price_id;
		else
			$def_price_id = $pr->prices["virtuemart_product_price_id"];
		
		global $calculator;
		ob_start();
		foreach ($allPrices as $k => $sPrices) {
			if(PLEM_VM_RUN < 3)
				$sPrices = (array)$sPrices;
			
			if($def_price_id == $sPrices["virtuemart_product_price_id"])
				continue;
			
			$price = null;
			
			if(PLEM_VM_RUN < 3){
				if(count($sPrices) == 0) continue;
				if (empty($sPrices['virtuemart_product_price_id'])) {
					$sPrices['virtuemart_product_price_id'] = '';
				}
				
				$tmp_pr = (object)array_merge ((array)$pr, $sPrices);
				$calsPrices = plem_getProductPrices ($tmp_pr);
				$price = array_merge($sPrices,(array)$calsPrices);
				
			}else{	
				$pr->selectedPrice = $k;
				$calsPrices = plem_getProductPrices ($pr);
				$price = array_merge($pr->allPrices[$k],$calsPrices);
			}

			$p = new stdClass;
			
			$p->pp_id               = $price["virtuemart_product_price_id"];
			$p->sg_id               = $price["virtuemart_shoppergroup_id"];
			$p->price               = $price["product_price"];
			$p->price_override      = $price["override"];
			$p->q_start             = $price["price_quantity_start"];
			$p->q_end               = $price["price_quantity_end"];
			$p->sales_price         = $price["salesPriceTemp"];
			$p->product_tax_id      = $price["product_tax_id"] ? jsnum($price["product_tax_id"]) : 0;
			$p->product_discount_id = $price["product_discount_id"] ? jsnum($price["product_discount_id"]) : 0;
			$p->product_currency    = $price["product_currency"] ? jsnum($price["product_currency"]) : null;
		
		    $p->price           = jsnum($p->price);
			$p->price_override  = jsnum($p->price_override); 
			$p->sales_price     = jsnum($p->sales_price);
			
			if(isset($psurog[$p->pp_id]))
				$p->surogate = $psurog[$p->pp_id];
			$ret_prices[] = $p;
		  }
		 
		  $res_item->returned->prices = $ret_prices;
	   }
	   ob_end_clean();
	   $reload_price = $any_price_set;
	   
	   if($prod_info->product_parent_id){
			if(isset($task->product_sales_price) || isset($task->product_override_price) || isset($task->product_price)){
				clear_product_default_price( $db,  $key); 	
				$reload_price = true;
			}
	   }
	   
	   
	   
	   if($return_added){
		$res_item->surogate = $sKEY;
		$res_item->full     = vmel_getProduct($key,$productModel,$db,$custom_fields,$cat_asoc,$man_asoc);
	   }
	  
	   $npp =  null;
	   if($reload_price || (!isset($task->product_sales_price) && (isset($task->product_price) || isset($task->product_override_price)))){ 
			pelm_clearCache();
			 
			$npp = $productModel->getProduct($key,false,true,false);
			$res_item->returned->product_sales_price = $npp->prices["salesPrice"];
			$res_item->returned->product_price       = $npp->prices["costPrice"];
			
			$res_item->returned->product_sales_price = jsnum($res_item->returned->product_sales_price);
			$res_item->returned->product_price       = jsnum($res_item->returned->product_price);
			
			//$res_item->prices = $npp->prices;
	   }
	   
	  
		
	   if($any_price_set){
		   $db->setQuery("  SELECT 
							 p.virtuemart_product_id
							FROM 
							#__virtuemart_products as p 
							LEFT JOIN
							#__virtuemart_product_prices as pp on pp.virtuemart_product_id = p.virtuemart_product_id
							WHERE 
							p.product_parent_id = $key AND pp.virtuemart_product_id IS NULL");
			
			$children_noprice = $db->loadObjectList(); 				 
			if(!empty($children_noprice)){
				if(!$npp)
					$npp = $productModel->getProduct($key,false,true,false);
				
				if(!isset($res_item->dependent))
					$res_item->dependent = array();
				//OVDE children of children
				foreach($children_noprice as $cnp){
					if(!isset($res_item->dependent[$cnp->virtuemart_product_id]))
						$res_item->dependent[$cnp->virtuemart_product_id] = new stdClass;
					$res_item->dependent[$cnp->virtuemart_product_id]->product_sales_price = $npp->prices["salesPrice"];
					$res_item->dependent[$cnp->virtuemart_product_id]->product_price       = $npp->prices["costPrice"];
					if(PLEM_VM_RUN > 2){
						if(isset($npp->prices["override"])){
							if(intval($npp->prices["override"]) !== 0)
								$res_item->dependent[$cnp->virtuemart_product_id]->product_override_price = $npp->prices["product_override_price"];
						}
					}else
						$res_item->dependent[$cnp->virtuemart_product_id]->product_override_price     = $npp->override !== 0 ? $npp->product_override_price : 0;
					
					$res_item->dependent[$cnp->virtuemart_product_id]->product_sales_price    = jsnum($res_item->dependent[$cnp->virtuemart_product_id]->product_sales_price);
					$res_item->dependent[$cnp->virtuemart_product_id]->product_price          = jsnum($res_item->dependent[$cnp->virtuemart_product_id]->product_price);
					$res_item->dependent[$cnp->virtuemart_product_id]->product_override_price = jsnum($res_item->dependent[$cnp->virtuemart_product_id]->product_override_price);
				}
			}
	   }
	   
	   foreach(get_pelm_plugins_toinc("update_product") as $plg)
		include($plg);
	   
	   if($SETTINGS->modified_update){
		   $db->setQuery("UPDATE #__virtuemart_products SET modified_on = NOW(), modified_by = $user->id WHERE virtuemart_product_id = '$key'");
		   $db->query();
	   }
		
	   $res[] = $res_item;
	   
	   
	}
	echo json_encode($res);
    exit; 
	return;
}
}


///IMPORT UTILITY FUNCTIONS START//////////////////////////////////////////////////////////
function check_import_tables(&$db){
	$pref = $db->getPrefix();
	//if( strpos($pref, "_") === false){
	//	$pref.= "_";
	//}
	
	$db->setQuery("SHOW TABLES LIKE '".$pref."virtuemart_pelm_toimport%'");
	if(!count($db->loadObjectList())){
		$db->setQuery("CREATE TABLE `".$pref."virtuemart_pelm_toimport` (
					  `no`   int NOT NULL,
					  `data` text CHARACTER SET utf8
					) DEFAULT CHARSET=utf8;");
		$db->query();			
	}
	
	$db->setQuery("SHOW TABLES LIKE '".$pref."virtuemart_pelm_imported_images%'");
	if(!count($db->loadObjectList())){
		$db->setQuery("CREATE TABLE `".$pref."virtuemart_pelm_imported_images` (
					  `id`   int NOT NULL,
					  `url`  varchar(1024) CHARACTER SET utf8 NOT NULL DEFAULT ''
					) DEFAULT CHARSET=utf8;");
		$db->query();			
	}
}

function store_csv_import_data(&$db, $data_csv){
	global $SETTINGS;
	check_import_tables($db);
	
	
	if ($data_csv){
		$q = "DELETE FROM #__virtuemart_pelm_toimport";
		$db->setQuery($q);
		$db->query();
		
		$probecnt = "";
		
		for($I = 0; $I < 10 ; $I++){
			$line = fgets ($data_csv, 2048);	
			if($line)
				$probecnt .= $line;
		}
		
		
		if( !preg_match('!!u', $probecnt) ){
			 echo "CSV file you tried to import is not UTF8 encoded! Correct this then try to import again!";
			 die;
			 return;
		}
		
		
		$first_line = explode("\n",$probecnt);
		if(empty($first_line)){
			echo "CSV file you tried to import is invalid. Check file then try again!";
			die;
			return;
		}elseif(strpos($probecnt,$SETTINGS->csv_separator) === false){
			echo "CSV file you tried to import of invalid format. Probable cause id incorrect delimiter! Check file then try again!";
			die;
			return;
		}
		
		rewind($data_csv);
		$n = 0;	
		while (($data = fgetcsv($data_csv, 32768 * 4, $SETTINGS->csv_separator)) !== FALSE) {
			if($n == 0){//REMOVE UTF8 BOM IF THERE
				 $bom     = pack('H*','EFBBBF');
				 $data[0] = preg_replace("/^$bom/", '', $data[0]);
			}
			
			$d = $db->quote(serialize($data));
			$q = "INSERT INTO #__virtuemart_pelm_toimport(`no`,`data`) VALUES ('$n',$d)";
			$db->setQuery($q);
			$db->query();
			$n++;	
		}
		if($data_csv)
			fclose($data_csv);
	}
}

function clear_csv_import_data(&$db){
	check_import_tables($db);
	$q = "DELETE FROM #__virtuemart_pelm_toimport";
	$db->setQuery($q);
	$db->query();
	
	$q = "DELETE FROM #__virtuemart_pelm_imported_images";
	$db->setQuery($q);
	$db->query();
}

function get_csv_import_data(&$db,$n){
	$q = "SELECT * FROM #__virtuemart_pelm_toimport where no = $n";
	$db->setQuery($q);
	$data = $db->loadObject();
	if($data){
		$data = $data->data;
		if($data){
			$data = unserialize($data);
			return $data;
		}else
			return NULL;
	}else
		return NULL;
}

function media_image_store(&$db,$image,$i,$p_id,$user){
	global $SETTINGS, $media_product_path;
	
	
	global $inserted_images;
	if(!isset($inserted_images))
		$inserted_images = array();
	
	$filename =  basename(urldecode($image));
	
	$imgpath = JPATH_SITE. DIRECTORY_SEPARATOR . $media_product_path . DIRECTORY_SEPARATOR;
	
	//in case that image is old one (probabably from exported .csv)
	$query="SELECT file_url, vm.virtuemart_media_id FROM #__virtuemart_product_medias AS pm JOIN #__virtuemart_medias AS vm ON vm.virtuemart_media_id=pm.virtuemart_media_id WHERE virtuemart_product_id=".$p_id." ";
	$db->setQuery($query);
	$images= $db->loadObjectList();

	$cmp1 = "";
	$cmp2 = "";
	
	for($k = 0; $k<count( $images ) ; $k++ ){
		$cmp1 = normalizedLowercase(JURI::root().$images[$k]->file_url);
		$cmp2 = normalizedLowercase($image);
		$cmp1 = str_ireplace("//",'/',$cmp1);
		$cmp2 = str_ireplace("//",'/',$cmp2);
		$cmp1 = str_ireplace("//",'/',$cmp1);
		$cmp2 = str_ireplace("//",'/',$cmp2);
		$cmp1 = str_ireplace("https:/",'',$cmp1);
		$cmp2 = str_ireplace("https:/",'',$cmp2);
		$cmp1 = str_ireplace("http:/",'',$cmp1);
		$cmp2 = str_ireplace("http:/",'',$cmp2);
		
		if (strcasecmp($cmp1,$cmp2) == 0){
			if(file_exists($imgpath.$filename)){
				$mid = $images[$k]->virtuemart_media_id;
				
				$inserted_images[$image] = $mid;
				
				return $mid;
				
			}
			break;
		} 	
	}
	
	$dind = stripos($image, ',');
	$data = substr($image, $dind);
	
	$mime = pathinfo($image);
	$mime = $mime["extension"];
	
	$file_check = $imgpath.$filename;
	
	if(isset($inserted_images[$image])){
		//ako jeste znqaci vec postoji u ovoj sesiji, za taj produkt samo dodajem vrednosti postojeceg u bazi, necu unositi nov
		$n_sql = "SELECT virtuemart_media_id FROM #__virtuemart_product_medias WHERE virtuemart_product_id=".$p_id." ";
		$db->setQuery($n_sql);
		$all_media = $db->loadRowList();
		$copy = false;
		for($k=0;$k<count($all_media); $k++){
			if ($all_media[$k][0]==$inserted_images[$image])
			{
				$copy=true;
				break;
			}
		}
		if(!$copy){
			$i_sql = "INSERT INTO #__virtuemart_product_medias(id,virtuemart_product_id,virtuemart_media_id ,ordering) VALUES (
						   NULL
						  ,".$p_id."
						  ,".$inserted_images[$image]."
						  ,".$i."
						)";
						
			$db->setQuery($i_sql);
			$db->query();
			return;
		}
	}elseif(file_exists($imgpath.$filename) && !$SETTINGS->import_images_overwrite){
	
		$i= 1;
		$file = basename($filename, ".".$mime);
		while (file_exists($imgpath.$filename)){
			$filename= $file . '-' . $i . '.' . $mime;
			$i++;
		}
	}
	
	
	
	
	$image_url = $image;
	$image = imagecreatefromstring(file_get_contents($data));
	
	if(stripos($mime,'png') !== false || stripos($mime,'gif') !== false){
		imagealphablending($image, false);
		imagesavealpha($image, true);
		$images = imagecolorallocatealpha($image, 255, 255, 255, 127);
	}
	
	$width = imagesx($image);
	$height = imagesy($image);
	
	if(stripos($mime,'jpg') !== false || stripos($mime,'jpeg') !== false){
		imagejpeg($image, $imgpath . DS . $filename );
	}elseif(stripos($mime,'gif') !== false){
		imagegif($image, $imgpath . DS . $filename);
	}else{
		imagepng($image, $imgpath . DS . $filename);
	}
	
	if($writen !== false){
	
		$twidth  = VmConfig::get('img_width',array());
		$theight = VmConfig::get('img_height',array());
		
		if(!$twidth && $theight){
			$twidth = $theight * ($width/$height);
		}elseif(!$theight && $twidth){
			$theight = $twidth * ($height/$width);
		}elseif(!$theight && !$twidth){
			$twidth  = 220;
			$theight = 220;
		}
		
		$twidth  = round($twidth);
		$theight = round($theight);
		
		$thumb_image = imagecreatetruecolor($twidth , $theight);
		if(stripos($mime,'png') !== false  || stripos($mime,'gif') !== false){
			imagealphablending($thumb_image, false);
			imagesavealpha($thumb_image, true);
			$transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
			imagefilledrectangle($thumb_image, 0, 0, $twidth, $theight, $transparent);
		}	
		
		$scale_width  = 0;
		$scale_height = 0;
		$rat_i = $width  / $height;
		$rat_t = $twidth / $theight;
		
		if($rat_i > $rat_t){
			$scale_width  = $twidth * ( $height /  $theight);
			$scale_height = $height;
		}else{
			$scale_width  = $width;
			$scale_height = $theight * ( $width /  $twidth);
		}
		
		$scale_width  = round($scale_width);
		$scale_height = round($scale_height);
		
		imagecopyresampled($thumb_image, $image, 0, 0, ($width - $scale_width)/2, ($height - $scale_height)/2, $twidth, $theight, $scale_width, $scale_height);
	
		//$thumbname = explode(".",$filename);
		//$thumbname = $thumbname[0];
		
		$thumbname = $filename;
		
		if(stripos($mime,'jpg') !== false || stripos($mime,'jpeg') !== false){
			//$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.jpg';
			imagejpeg($thumb_image, $imgpath . DIRECTORY_SEPARATOR . 'resized' . DIRECTORY_SEPARATOR . $thumbname);
		}elseif(stripos($mime,'gif') !== false){
			//$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.gif';
			imagegif($thumb_image, $imgpath . DIRECTORY_SEPARATOR . 'resized' . DIRECTORY_SEPARATOR . $thumbname);
		}else{
			//$thumbname = $thumbname . '_' . $twidth . 'x' . $theight . '.png';
			imagepng($thumb_image, $imgpath . DIRECTORY_SEPARATOR . 'resized' . DIRECTORY_SEPARATOR . $thumbname);
		}
		
		$product_image=1;
		
		$i_sql = "INSERT INTO #__virtuemart_medias( virtuemart_media_id,virtuemart_vendor_id,file_title,file_description,file_meta,file_mimetype,file_type,file_url,file_url_thumb,file_lang ,file_is_product_image,file_is_downloadable,file_is_forSale,file_params,shared,published,created_on,created_by,modified_on,modified_by,locked_on,locked_by) VALUES (
				   NULL
				  ,0
				  ,". $db->quote($filename) ."
				  ,''
				  ,". $db->quote($filename) ."
				  ,". $db->quote($mime) ."
				  ,'product'
				  ,". $db->quote( "$media_product_path/". $filename ) ."
				  ,". $db->quote( "$media_product_path/resized/". $thumbname ) ."
				  ,''
				  ,".$product_image."
				  ,0
				  ,0
				  ,''
				  ,0
				  ,1
				  ,NOW()
				  ,". $user."
				  ,NOW()
				  ,". $user."
				  ,'0000-00-00 00:00:00'
				  ,0
				)";
		
		$db->setQuery($i_sql);	
		$db->query();

		$mid = $db->insertid();		
		
		$i_sql = "INSERT INTO #__virtuemart_product_medias(id,virtuemart_product_id,virtuemart_media_id ,ordering) VALUES (
					   NULL
					  ,".$p_id."
					  ,".$mid."
					  ,".$i."
					)";
					
		$db->setQuery($i_sql);
		$db->query();
		$inserted_images[$image_url]=$mid;
		return $mid;
	}
	
    return 0;	
}

///IMPORT UTILITY FUNCTIONS END////////////////////////////////////////////////////////////

function setProductImagesFromCSV($pr_id, $images_str , &$db, $user_id){
	global $SETTINGS;
	
	$images_data = array();
	
	$iquery="SELECT pm.id, pm.virtuemart_media_id, vm.file_url FROM #__virtuemart_product_medias AS pm JOIN #__virtuemart_medias AS vm ON vm.virtuemart_media_id=pm.virtuemart_media_id WHERE virtuemart_product_id=".$pr_id." ";
	$db->setQuery($iquery);
	$images = $db->loadObjectList();
	
	$current_images = array();
	$new_images     = array();
	
	
	foreach($images as $img){
		$current_images[str_ireplace(array('https','http','/','\\',' '),array("","","","",''), normalizedLowercase(JURI::root().$img->file_url))] = $img;
	}
	
	
	$images = explode($SETTINGS->cf_val_separator ? $SETTINGS->cf_val_separator : "," , $images_str ); 
	
	foreach ($images as $image_url) {
		
		$title         = "";
		$description   = "";
		$meta          = "";
			
		if($image_url){
			$has_data      = false;
			
			$image_url = explode("|",$image_url);
			if($SETTINGS->import_images_meta){
				if(isset($image_url[1])){
					$title       = $image_url[1];
					$has_data    = true;
				}
				if(isset($image_url[2]))
					$description = $image_url[2];
				
				if(isset($image_url[3]))
					$meta        = $image_url[3];
			}  
			
			$image_url = $image_url[0];
			
			if(!basename($image_url) || strpos(basename($image_url),".") === false)
				continue;
			
			$hash = str_ireplace(array('https','http','/','\\',' '),array("","","","",''),normalizedLowercase($image_url));
			
			$new_images[$hash] = $image_url;
			
			if($SETTINGS->import_images_meta && $has_data){
				$images_data[$hash] = new stdClass;
				$images_data[$hash]->title       = $title;
				$images_data[$hash]->description = $description;
				$images_data[$hash]->meta        = $meta;
			}
		}
	}
	
	foreach($current_images as $hash => $img){
		if(!isset($new_images[$hash])){
			
			$db->setQuery("DELETE FROM #__virtuemart_product_medias WHERE id = " . $img->id);
			$db->query();
			
			$db->setQuery("SELECT count(id) FROM #__virtuemart_product_medias WHERE virtuemart_media_id = " . $img->virtuemart_media_id);
			if(!$db->loadResult()){
				$db->setQuery("SELECT * FROM #__virtuemart_medias WHERE virtuemart_media_id = " . $img->virtuemart_media_id);
				$m = $db->loadObject();
				$db->setQuery("DELETE FROM #__virtuemart_medias WHERE virtuemart_media_id = " . $img->virtuemart_media_id);
				$db->query();
				
				if($m->file_url)
					unlink(JPATH_SITE . DS . str_replace(array("\\","/"),array(DS,DS),$m->file_url));
				if($m->file_url_thumb)
					unlink(JPATH_SITE . DS . str_replace(array("\\","/"),array(DS,DS),$m->file_url_thumb));
			}
			
		}else{
			if($SETTINGS->import_images_meta && isset($images_data[$hash])){
				$mid = $img->virtuemart_media_id;
				if($SETTINGS->import_images_meta && isset($images_data[$hash]) && $mid){
					////////////////////////////////////////////////////////////
					$db->setQuery("UPDATE #__virtuemart_medias SET 
					                 file_title       = " . $db->quote($images_data[$hash]->title) . ",
									 file_description = " . $db->quote($images_data[$hash]->description) . ",
									 file_meta        = " . $db->quote($images_data[$hash]->meta) . "
								   WHERE 
									 virtuemart_media_id = " . $mid);
					$db->query();				 
				}
			}
		}
	}
	
	
	$i = 0;
	foreach($new_images as $hash => $image_url){
		if(!isset($current_images[$hash])){
			if(basename($image_url)){
				$mid = media_image_store($db,$image_url,$i,$pr_id,$user_id);
				
				if($SETTINGS->import_images_meta && isset($images_data[$hash]) && $mid){
					////////////////////////////////////////////////////////////
					$db->setQuery("UPDATE #__virtuemart_medias SET 
									 file_title       = " . $db->quote($images_data[$hash]->title) . ",
									 file_description = " . $db->quote($images_data[$hash]->description) . ",
									 file_meta        = " . $db->quote($images_data[$hash]->meta) . "
								   WHERE 
									 virtuemart_media_id = " . $mid);
					$db->query();
				}
				
				$i++;
			}
		}else{
			if($SETTINGS->import_images_meta && isset($images_data[$hash])){
				////////////////////////////////////////////////////////////
				$db->setQuery("UPDATE #__virtuemart_medias SET 
								 file_title       = " . $db->quote($images_data[$hash]->title) . ",
								 file_description = " . $db->quote($images_data[$hash]->description) . ",
								 file_meta        = " . $db->quote($images_data[$hash]->meta) . "
							   WHERE 
								 virtuemart_media_id = " . $current_images[$hash]->virtuemart_media_id);
				$db->query();
			}
		}
	}
}


$import_count  = 0;
if(isset($_REQUEST["do_import"])){
	if($_REQUEST["do_import"] = "1"){
		
		
		if($_REQUEST["remote_import"] == '1'){
			if(!$SETTINGS->allow_autoimport){
				header('HTTP/1.0 403 Forbidden');
				echo "Remote import is not allowed!";
			}
		}
		
	    //$fileContent = file_get_contents($_FILES['file']['tmp_name']);
		
		
	    $n = 0;
		if($_FILES['file']){
			if($_FILES['file']['tmp_name']){
				if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
					if(!isset($_REQUEST["continueFrom"])){
						clear_csv_import_data($db);
						store_csv_import_data($db, $handle);
					}
				}else{
					echo "Can not access upload stream on " . $_FILES['file']['tmp_name'] . " check temp folders setting in php.ini for your web server. Contact your server administrator.";
					die;
				}
			}
		}else if(isset($_REQUEST["csv_import_data"])){
			$temp = tmpfile();
			fwrite($temp, $_REQUEST["csv_import_data"]);
			fseek($temp, 0);
			clear_csv_import_data($db);
			store_csv_import_data($db, $temp);
		}
		
		
		$id_index           	 = -1;
		$price_index        	 = -1;
		$price_o_index      	 = -1;
		$product_sales_price_index = -1;
		$stock_index        	 = -1;
		$sku_index          	 = -1;
		$parent_index            = -1;
		$ordered_index      	 = -1;
		
		$weight_index        	 = -1;
		$weight_uom_index   	 = -1;
		$length_index       	 = -1;
		$width_index        	 = -1;
		$height_index        	 = -1;
		$lwh_uom_index       	 = -1;
		$packaging_index     	 = -1;
		$unit_index          	 = -1;
		
		$metarobot_index         = -1;
		$metaauthor_index        = -1;
		$metadesc_index          = -1;
		$metakey_index           = -1;
		$customtitle_index       = -1;
		
		$product_url_index       = -1;
		$product_gtin            = -1;
		$product_mpn             = -1;
		
		$manufacturer_name_index = -1; 
		$categories_names_index  = -1;
		$product_name_index      = -1;
		$product_s_desc_index    = -1;
		$product_desc_index      = -1;  
		$product_special_index   = -1;
		$published_index         = -1;
		$slug_index              = -1;
		$images_index			 = -1;
		$product_tax_index		 = -1;
		$product_discount_index	 = -1;
		$product_currency_index	 = -1;
		
		$cf_indexes = array();
		$col_count = 0;
		
		$headers = null;
		
		
		
		if($SETTINGS->custom_import){
			$cic = array();
			if(!is_array($SETTINGS->custom_import_columns))
				$SETTINGS->custom_import_columns = explode(",",$SETTINGS->custom_import_columns);
			foreach($SETTINGS->custom_import_columns as $col){
			   if($col)
				$cic[] = $col;
			}
			$headers = $cic;
			if($SETTINGS->first_row_header)
				$n = 1;
		}else{
			$headers = get_csv_import_data($db,0);
			$n = 1;
		}
		
		if(!$headers){
			echo "Import error. Could not resolve data headers!";
			if($SETTINGS->custom_import){
				echo "<br/>If you use custom import you need to set headers for custom import in settings!";
			}
			die;
		}
		
		if(isset($_REQUEST["continueFrom"]))
			$n = intval($_REQUEST["continueFrom"]);
		if(isset($_REQUEST["import_count"]))
			$import_count = intval($_REQUEST["import_count"]);
		
		
		
		for($i = 0 ; $i < count($headers); $i++){
			if    ($headers[$i] == "virtuemart_product_id")  $id_index                = $i;
			elseif($headers[$i] == "parent")                 $parent_index            = $i;
			elseif($headers[$i] == "product_price")          $price_index             = $i;
			elseif($headers[$i] == "product_override_price") $price_o_index           = $i;
			elseif($headers[$i] == "product_sales_price")    $product_sales_price_index  = $i;
			
			elseif($headers[$i] == "product_tax" || $headers[$i] == "product_tax_id")            $product_tax_index  = $i;
			elseif($headers[$i] == "product_discount"  || $headers[$i] == "product_discount_id")       $product_discount_index  = $i;
			elseif($headers[$i] == "product_currency")       $product_currency_index  = $i;
			
			elseif($headers[$i] == "product_sku")            $sku_index               = $i;
			elseif($headers[$i] == 'product_in_stock')       $stock_index             = $i;
			elseif($headers[$i] == 'product_ordered')    	  $ordered_index           = $i;
			elseif($headers[$i] == 'product_weight')     	  $weight_index            = $i;
			elseif($headers[$i] == 'product_weight_uom') 	  $weight_uom_index        = $i;
			elseif($headers[$i] == 'product_length')     	  $length_index            = $i;
			elseif($headers[$i] == 'product_width')      	  $width_index             = $i;
			elseif($headers[$i] == 'product_height')     	  $height_index            = $i;
			elseif($headers[$i] == 'product_lwh_uom')    	  $lwh_uom_index           = $i;
			elseif($headers[$i] == 'metarobot')              $metarobot_index         = $i;
			elseif($headers[$i] == 'metaauthor')             $metaauthor_index        = $i;
			elseif($headers[$i] == 'metadesc')               $metadesc_index          = $i;
			elseif($headers[$i] == 'metakey')                $metakey_index           = $i;
			elseif($headers[$i] == 'customtitle')            $customtitle_index       = $i;
			elseif($headers[$i] == 'product_url')            $product_url_index       = $i;
			elseif($headers[$i] == 'product_gtin')           $product_gtin_index      = $i;
			elseif($headers[$i] == 'product_mpn')            $product_mpn_index       = $i;
			elseif($headers[$i] == 'product_packaging')  	  $packaging_index         = $i;
			elseif($headers[$i] == 'product_unit')       	  $unit_index              = $i;
			elseif($headers[$i] == 'product_special')    	  $product_special_index   = $i;
			elseif($headers[$i] == 'published')          	  $published_index         = $i;
			elseif($headers[$i] == 'product_name')       	  $product_name_index      = $i;
			elseif($headers[$i] == 'product_s_desc')     	  $product_s_desc_index    = $i;
			elseif($headers[$i] == 'product_desc' || $headers[$i] == 'c_id')$product_desc_index      = $i;
			elseif($headers[$i] == 'slug')               	  $slug_index              = $i;
			
			elseif($headers[$i] == 'images' || $headers[$i] == 'i_id') $images_index            = $i;
			
			elseif($headers[$i] == 'virtuemart_manufacturer_id') $manufacturer_name_index = $i; 
			elseif($headers[$i] == 'categories')   	      	  $categories_names_index  = $i;
			
			elseif($headers[$i] == 'manufacturer_name')  	  	  $manufacturer_name_index = $i; 
			elseif($headers[$i] == 'categories_names')   	      $categories_names_index  = $i;
			else{
				foreach($custom_fields as $cf_id => $cf){
					if($SETTINGS->custom_import){
						$filed_name = 'custom_field_'.$cf_id;
					}else{
						$filed_name = cf_field_name($cf);
					}	
					
					if(strcasecmp($filed_name,$headers[$i]) == 0){
						$cf_indexes[$cf_id] = $i;
						break;
					} 
					
				}
			}
			
			foreach(get_pelm_plugins_toinc("import_headers") as $plg)
				include($plg);
			
			
		}
		$exit = false;
		
		$db->setQuery("SELECT * FROM #__virtuemart_pelm_imported_images");
		$inserted_images = $db->loadAssocList("url","id");
		
		
		
		global $calculator;
		$processed = 0;
		//LOOP START
		while (($data = get_csv_import_data($db,$n)) != null && !$exit) {
			try{
				$pr = null;
				if( $processed == 300 || $start_time + $max_time + 2 < time()){
					if(isset($inserted_images)){
						foreach($inserted_images as $url => $mid){
							$url = $db->quote($url);
							$mid = $db->quote($mid);
							$db->setQuery("INSERT INTO #__virtuemart_pelm_imported_images(`id`, `url`) VALUES($mid,$url);");
							$db->query();
						}
					}
					
					?>
					<!DOCTYPE html>
					<html>
						<head>
							<style type="text/css">
								html, body{
									background:#505050;
									color:white;
									font-family:sans-serif;	
								}
							</style>
						</head>
						<body>
							<form method="POST" id="continueImportForm">
								<h2><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORTING"); ?></h2>
								<p><?php echo $import_count; ?> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORTING_PROC"); ?> <?php echo $n." CSV ". pelm_sprintf("rows.");  ?> </p>
								<hr/>
								<?php foreach($_POST as $p_parm => $p_value){ 
								   if($p_parm == "import_count"
									  ||
									  $p_parm == "continueFrom"
									  ||
									  $p_parm == "do_import"
									  ||
									  $p_parm == "csv_import_data")
									  continue;
								?>
								<input type="hidden" name="<?php echo $p_parm;?>" value="<?php echo $p_value;?>">	
								<?php } ?>
								<input type="hidden" name="import_count" value="<?php echo $import_count;?>">
								<input type="hidden" name="continueFrom" value="<?php echo $n;?>">
								<input type="hidden" name="do_import" value="1">
							</form>
							<script data-cfasync="false"  type="text/javascript">
									document.getElementById("continueImportForm").submit();
							</script>
						</body>	
					</html>
					<?php
					die;
					return;
				}
				
				/////////////////////////////////////////////////////////////////
				
				while(count($data) < $col_count)
					$data[] = NULL;
				
				for($I = 0; $I < $col_count ; $I++){
					if($data[$I])
						$data[$I] = addslashes($data[$I]);
				}
			
			    $id  = 0;
				if($id_index > -1)
					$id = $data[$id_index];
				
				$sku = '';
				if($sku_index > -1)
					$sku = $data[$sku_index];
				
				///////////////////////////////////////////
				if(!$id && $sku){
				  $db->setQuery("SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_sku LIKE ". $db->quote($sku) .";");
				  $obj = $db->loadObject();
				  if($obj){
					$id = $obj->virtuemart_product_id;
				  }
				}
				
				
				
				$continue = false;
				$parent   = 0;
				$with_par = NULL;
				if($parent_index > -1){
					if($data[$parent_index]){
						if(strpos($data[$parent_index],":") !== false){
							$criteria = explode(":",$data[$parent_index]);
							$criteria_key = strtolower($criteria[0]);
							$criteria_val = trim($criteria[1]);
							if($criteria_key == "sku"){
								$db->setQuery("SELECT p.virtuemart_product_id
												FROM 
												#__virtuemart_products as p
												LEFT JOIN
												#__virtuemart_products as pl on pl.virtuemart_product_id = p.virtuemart_product_id
												WHERE
												p.product_sku LIKE '$criteria_val'");
								$with_par = $db->loadResult();
								if(!$with_par){
									$continue = true;
									$with_par = NULL;
								}
							}elseif($criteria_key == "title"){
								$db->setQuery("SELECT p.virtuemart_product_id
												FROM 
												#__virtuemart_products as p
												LEFT JOIN
												#__virtuemart_products as pl on pl.virtuemart_product_id = p.virtuemart_product_id
												WHERE
												pl.product_name LIKE '$criteria_val'");
								$with_par = $db->loadResult();
								if(!$with_par){
									$continue = true;
									$with_par = NULL;
								}
							}elseif($criteria_key == "slug"){
								$criteria_val = sanitize_title($criteria_val);
								$db->setQuery("SELECT p.virtuemart_product_id
												FROM 
												#__virtuemart_products as p
												LEFT JOIN
												#__virtuemart_products as pl on pl.virtuemart_product_id = p.virtuemart_product_id
												WHERE
												pl.slug LIKE '$criteria_val'");
												
								$with_par = $db->loadResult();
								if(!$with_par){
									$continue = true;
									$with_par = NULL;
								}
							}elseif($criteria_key == "id"){
								$with_par = intval($criteria_val);
							}
						}else{
							$with_par = intval($data[$parent_index]);
						}
					}	
				}
				
				$is_add = false;
				if(!$id){
				   if($data[$sku_index] || $data[$product_name_index]){	
					   $id = vmel_addProduct($db,$vm_languages,$user->id, $with_par);
					   if($id < 1 || !$id){
							$n++;
							continue;
					   }
					   $is_add = true;	
				   }
				}
				
				if($is_add){
					if($sku_index != -1){
						if(!$data[$sku_index]){
							$data[$sku_index] = "SKU_".$id;
						}
					}
				}
				
				if(!$id && !$sku){
					$n++;
					continue;
				}
				
				
			
				///////////////////////////////////////////
				
				$db->setQuery("SELECT * FROM #__virtuemart_products WHERE virtuemart_product_id = ".$id);
				$prod_info = $db->loadObject();
				
				
				
				$uset = array();
				
				if($sku_index > -1)
					$uset[] = " pr.product_sku = ". $db->quote($data[$sku_index]) ." ";
				
				if($with_par)
					$uset[] = " pr.product_parent_id = ". $db->quote($with_par) ." ";
				
				
				if($product_tax_index > -1){
				   global $taxs_asoc_rev;
				   if(!isset($taxs_asoc_rev)){
					   $taxs_asoc_rev = array();
					   foreach($taxs_asoc as $t_id => $t_name){
						   $taxs_asoc_rev[trim(strtolower($t_name))] = $t_id;
					   }
				   }
				   
				   $tname = trim(strtolower($data[$product_tax_index]));
				   
				   
				   if(isset($taxs_asoc_rev[$tname]))
					   $uset[] = " pr_p.product_tax_id = ". default_val_num($taxs_asoc_rev[$tname],'0')  . " " ;
				   else
					   $uset[] = " pr_p.product_tax_id = 0 " ;
				   $any_price_set = true;
				   
			   }

			   if($product_discount_index > -1){
				   global $disc_asoc_rev;
				   if(!isset($disc_asoc_rev)){
					   $disc_asoc_rev = array();
					   foreach($disc_asoc as $d_id => $d_name){
						   $disc_asoc_rev[trim(strtolower($d_name))] = $d_id;
					   }
				   }
				   
				   $dname = trim(strtolower($data[$product_discount_index]));
				   
				   if(isset($disc_asoc_rev[$dname]))
					   $uset[] = " pr_p.product_discount_id = ". default_val_num($disc_asoc_rev[$dname],'0')  . " " ;
				   else
					   $uset[] = " pr_p.product_discount_id = 0 " ;
				   $any_price_set = true;
				   
			   }

			   if($product_currency_index > -1){
				   global $curr_asoc_rev;
				   if(!isset($curr_asoc_rev)){
					   $curr_asoc_rev = array();
					   foreach($curr_asoc as $c_id => $c_name){
						   $curr_asoc_rev[trim(strtolower($c_name))] = $c_id;
					   }
				   }
				   
				   $cname = trim(strtolower($data[$product_currency_index]));
				   
				   if(isset($curr_asoc_rev[$cname])){
					   $uset[] = " pr_p.product_currency = ". default_val_num($curr_asoc_rev[$cname],'NULL')  . " " ;
				   }else
					   $uset[] = " pr_p.product_currency = NULL " ;
				   $any_price_set = true;
				   
				}	
				
				$any_price_set = false;
				$price_set = false;
				if($product_sales_price_index != -1){
					$data[$product_sales_price_index] = default_val_num($data[$product_sales_price_index],'NULL');
					if( is_numeric($data[$product_sales_price_index])){
						$pdata = array();
						$pdata["salesPrice"] = $data[$product_sales_price_index];
						ob_start();
						$uset[] = " pr_p.product_price = ". dbnum($calculator->calculateCostprice ($id, $pdata)) . " " ;
						ob_end_clean();
						$price_set = true;
						$any_price_set = true;
					}
				}
				
				if($price_index != -1 && !$price_set){
					$uset[] = " pr_p.product_price  = ".default_val_num($data[$price_index],'NULL')." ";
					$any_price_set = true;
				}
				
				if($price_o_index != -1){
					$uset[] = " pr_p.product_override_price  = ".default_val_num($data[$price_o_index],'NULL')." ";	
					if(!$data[$price_o_index])
						$uset[] = " pr_p.override = 0";	
					else
						$uset[] = " pr_p.override = ".$SETTINGS->override_price;	
					$any_price_set = true;
				}
				
				if($any_price_set && $prod_info->product_parent_id){
					fix_prices_table( $db, $user->id, $id, false); 	
				}
				
				if($stock_index != -1)
					$uset[] = " pr.product_in_stock = ".default_val_num($data[$stock_index],'0')." ";
				
				if($ordered_index != -1)
					$uset[] = " pr.product_ordered = ".default_val_num($data[$ordered_index],'0')." ";	
					
				if($weight_index      != -1)
					$uset[] = " pr.product_weight  = ".dbnum($data[$weight_index])." ";
					
				if($weight_uom_index  != -1){
					
					$w_uom = $data[$weight_uom_index];
					
					if(stripos($w_uom,'gr') !== false)
						$w_uom = "G";
					else
						$w_uom = strtoupper($w_uom );
					
					$uset[] = " pr.product_weight_uom  = ". $db->quote( $w_uom ) ." ";
				}
				
				if($length_index      != -1)
					$uset[] = " pr.product_length  = ".dbnum($data[$length_index])." "; 
				
				if($width_index       != -1)
					$uset[] = " pr.product_width  = ".dbnum($data[$width_index])." ";
				
				if($height_index      != -1)
					$uset[] = " pr.product_height  = ".dbnum($data[$height_index])." ";
				
				if($lwh_uom_index     != -1)
					$uset[] = " pr.product_lwh_uom  = ". $db->quote($data[$lwh_uom_index]) ." ";
					
				if($metarobot_index     != -1)
					$uset[] = " pr.metarobot  = ".$db->quote($data[$metarobot_index])." ";

				if($metaauthor_index     != -1)
					$uset[] = " pr.metaauthor  = ". $db->quote( $data[$metaauthor_index] )." ";
					
				if($product_url_index != -1)
					$uset[] = " pr.product_url  = ". $db->quote( $data[$product_url_index] )." ";
				
				if($has_gtinmpn){
					if($product_gtin_index != -1)
						$uset[] = " pr.product_gtin  = ".$db->quote($data[$product_gtin_index])." ";
					
					if($product_mpn_index != -1)
						$uset[] = " pr.product_mpn  = ".$db->quote($data[$product_mpn_index])." ";						
				}
				
				if($packaging_index   != -1)
					$uset[] = " pr.product_packaging  =  ".dbnum($data[$packaging_index])." ";
				
				if($unit_index        != -1)
					$uset[] = " pr.product_unit  = ". $db->quote($data[$unit_index]) ." ";
					
				if($product_special_index != -1)
					$uset[] = " pr.product_special  = ". $db->quote(($data[$product_special_index] || strtolower($data[$product_special_index]) == "yes") ? "1" : "0")." ";
					
				if($published_index != -1){
					
					if($data[$published_index] === "")
						$uset[] = " pr.published  = '1'";
					else
						$uset[] = " pr.published  = ".$db->quote(($data[$published_index] || strtolower($data[$published_index]) == "yes") ? "1" : "0")." ";
					
				}elseif($is_add)
					$uset[] = " pr.published  = '1'";
				
				$sql_q = " UPDATE 
									#__virtuemart_product_prices as pr_p
									LEFT JOIN
									#__virtuemart_products as pr on pr.virtuemart_product_id = pr_p.virtuemart_product_id
								SET
									".implode(",",$uset)."
								WHERE
									NOT (coalesce(pr_p.price_quantity_start,0) > 0 OR coalesce(pr_p.price_quantity_end,0) > 0) AND (NOT coalesce(pr_p.virtuemart_shoppergroup_id,0) > 0 OR coalesce(pr_p.virtuemart_shoppergroup_id,0) = $default_shopper_group_id)
									AND
									pr_p.virtuemart_product_id = $id;";	
				
				
				$db->setQuery($sql_q);
				
				
			    $dispatcher = null;
			    try{  
					$data->virtuemart_product_id  = $id;
					JPluginHelper::importPlugin('vmcustom');
					JPluginHelper::importPlugin('vmextended');
					$dispatcher = JDispatcher::getInstance();
					if($dispatcher)
						$dispatcher->trigger('plgVmBeforeStoreProduct',array(&$data, &$pr));
			    }catch(Exception $ps_ex){
				
			    }
				
				
				$db->query();
				
				try{ 
					if($dispatcher)
						$dispatcher->trigger('plgVmAfterStoreProduct',array(&$data, &$pr));
			    }catch(Exception $ps_ex){
				
			    }
				
				if($prod_info->product_parent_id){
					if($any_price_set){
						clear_product_default_price( $db,  $id); 	
					}
				}
				
				$import_count ++;
				
				
				foreach($custom_fields as $cf_id => $cf){
					if(isset($cf_indexes[$cf_id])){
					
						$delete_it = false;
						
						if(!isset($data[$cf_indexes[$cf_id]]))
							$delete_it = true;
						elseif( $data[$cf_indexes[$cf_id]] === null || $data[$cf_indexes[$cf_id]] === '')
							$delete_it = true;
						
						
						
						if( $delete_it ){
						
							$Q = "DELETE FROM #__virtuemart_product_customfields
								  WHERE
									   virtuemart_product_id = ".$id."
								  AND
									   virtuemart_custom_id  = ".$cf_id;
									   
							$db->setQuery($Q);
							$db->query();
							
							if($cf["custom_element"] == "customfieldsforall"){
								set_customfieldsforall($id, $cf_id ,NULL,NULL);
							}
							
						}else{	 
						
							$t_values = explode($SETTINGS->cf_val_separator,stripslashes($data[$cf_indexes[$cf_id]]));
							$values = array();
							foreach($t_values as $t_v)
								if(isset($t_v))if($t_v)
									$values[] = $t_v;
								
							if($cf["custom_element"] == "customfieldsforall"){
								$values = array('customfieldsforall');
							}	
							
							$Q = "SELECT virtuemart_customfield_id FROM #__virtuemart_product_customfields
									WHERE
									   virtuemart_product_id = ".$id."
									AND
									   virtuemart_custom_id  = ".$cf_id;
							 
							 $db->setQuery($Q);
							 $existing = $db->loadObjectList();
							 
							$last_pcf_id = NULL; 
							 
							for($I = 0; $I < count($values); $I++){
								$value = explode(":",$values[$I]);
								$price = 'NULL';
								if(count($value)> 1){
									$price = $value[1];
									$value = $value[0];
								}else
									$value = $value[0];
									
								if(!$price || $price === '0')					
									$price = 'NULL';
								
								$order = $cf_id * 1000 + $I;
							  
								if($I < count($existing)){
									 
									 $Q = "UPDATE #__virtuemart_product_customfields
										SET
										   custom".(PLEM_VM_RUN > 2 ? "field" : "")."_value = ".$db->quote(decodeCSVContent($value)).",
										   custom".(PLEM_VM_RUN > 2 ? "field" : "")."_price = ".dbnum($price).",
										   ordering = $order
										WHERE
										   virtuemart_customfield_id = ".$existing[$I]->virtuemart_customfield_id;
									 
									 $db->setQuery($Q);
									 $db->query();
									 $last_pcf_id = $existing[$I]->virtuemart_customfield_id;
									 
								}else{
									
									 $Q = "INSERT INTO #__virtuemart_product_customfields(
											   virtuemart_product_id, virtuemart_custom_id, custom".(PLEM_VM_RUN > 2 ? "field" : "")."_value ,custom".(PLEM_VM_RUN > 2 ? "field" : "")."_price
											, ordering) VALUES (".$id.",".$cf_id.",".$db->quote(decodeCSVContent($value)).",".$db->quote($price).",$order)"; 
											
									 $db->setQuery($Q);
									 $db->query();
									 
									 $last_pcf_id = $db->insertid();
								}
							 }

							 for($I = count($values); $I < count($existing) ; $I++){
								$Q = "DELETE FROM #__virtuemart_product_customfields
									  WHERE
									   virtuemart_customfield_id = ".$existing[$I]->virtuemart_customfield_id;
									   
								$db->setQuery($Q);	
								$db->query();										
							 }
							 
							 if($cf["custom_element"] == "customfieldsforall"){
								set_customfieldsforall($db ,$id, $cf_id ,$last_pcf_id, stripslashes($data[$cf_indexes[$cf_id]]));
							 }
						}					
					}
				}
				
				if($is_add){
					if($slug_index != -1){
						if(!$data[$slug_index]){
							if($product_name_index != -1){
								$data[$slug_index] = str_replace("_","-", str_replace(" ","", strtolower($data[$product_name_index])));
							}
						}
					}
				}
				
				if($product_name_index != -1 || $product_s_desc_index != -1 || $product_desc_index != -1 || $slug_index != -1 || $metadesc_index != -1 || $metakey_index != -1 || $customtitle_index != -1){
					  $pl_user = array();
					  
					  if( $product_name_index != -1)
						$pl_user[] = "pr_l.product_name = ". $db->quote($data[$product_name_index])."";
					  
					  if( $product_s_desc_index != -1)
						$pl_user[] = "pr_l.product_s_desc = ".$db->quote(decodeCSVContent($data[$product_s_desc_index]));
					
					  if( $product_desc_index != -1 && $SETTINGS->import_content)
						$pl_user[] = "pr_l.product_desc = ".  $db->quote(decodeCSVContent($data[$product_desc_index]));
					
					  if( $slug_index != -1)
						$pl_user[] = "pr_l.slug = ".$db->quote($data[$slug_index])."";
						
					  if( $metadesc_index != -1 )
						$pl_user[] = "pr_l.metadesc = ".$db->quote($data[$metadesc_index])."";
					
					  if( $metakey_index != -1 ) 
						$pl_user[] = "pr_l.metakey = ".$db->quote($data[$metakey_index])."";
					
					  if( $customtitle_index != -1)	
						$pl_user[] = "pr_l.customtitle = ".$db->quote($data[$customtitle_index])."";
						
					  $pl_sql =	"UPDATE 
									#__virtuemart_products_$vm_lang as pr_l
								 SET
									".implode(",",$pl_user)." 
								 WHERE
									pr_l.virtuemart_product_id = $id;";
									
					  $db->setQuery($pl_sql);		
					  $db->query();			
				}
				
				if($images_index > -1 && $SETTINGS->import_images){
					setProductImagesFromCSV($id, $data[$images_index] , $db,$user->id);
				}
				
				
				
				if($manufacturer_name_index != -1 || $categories_names_index != -1){
				
					if($manufacturer_name_index != -1){
						$man_name = trim( $data[$manufacturer_name_index] );
						$db->setQuery("SELECT id, virtuemart_product_id, virtuemart_manufacturer_id 
										FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id = ".$id);
						$mp_obj = $db->loadObject();
						
						$db->setQuery("SELECT virtuemart_manufacturer_id, mf_name, mf_email, mf_desc, mf_url, slug 
										FROM #__virtuemart_manufacturers_$vm_lang WHERE mf_name LIKE " . $db->quote($man_name) . ";");
						
						$m_obj  = $db->loadObject();
						
						if($m_obj){
						  if($mp_obj){
								if($mp_obj->virtuemart_manufacturer_id != $m_obj->virtuemart_manufacturer_id){
									$db->setQuery("
										UPDATE #__virtuemart_product_manufacturers 
											SET virtuemart_manufacturer_id = ". $m_obj->virtuemart_manufacturer_id. "
										WHERE id = ". $mp_obj->id );
									$db->query();	
								}
							}else{
								$db->setQuery("
									INSERT INTO #__virtuemart_product_manufacturers(id ,virtuemart_product_id ,virtuemart_manufacturer_id) 
										VALUES 
									( NULL,".$id.",".$m_obj->virtuemart_manufacturer_id.")");
								$db->query();	
							
							}
						}else if(!$man_name && $mp_obj){
							$db->setQuery("DELETE FROM #__virtuemart_product_manufacturers WHERE id=". $mp_obj->id);
							$db->query();	
						}
					}
					
				
					if($categories_names_index != -1){
						
						$categories_names = explode($SETTINGS->cf_val_separator ? $SETTINGS->cf_val_separator : ",", $data[$categories_names_index]);
						$new_categories_ids = array();
						
						for($I = 0; $I < count($categories_names); $I++){
							if(!$categories_names[$I])
								continue;
							
							$pcat_id = null;
							
							
							$cat_name = str_replace("\\","/",$cat_name);
							$cat_name = str_replace(" /","/",$cat_name);
							$cat_name = str_replace("/ ","/",$cat_name);
							if($cat_name[0] == '/')
								$cat_name[0] = ' ';
							$cat_name = normalizedLowercaseHash($categories_names[$I]);
							
							if(isset($catway_asoc_reverse[$cat_name])){
								$pcat_id = $catway_asoc_reverse[$cat_name];
								$new_categories_ids[] = $pcat_id; 
							}else{
								foreach($categories as $cat){
									if(normalizedLowercaseHash($cat->category_name) == $cat_name){
											$pcat_id = $cat->virtuemart_category_id;
											$new_categories_ids[] = $pcat_id;
											break;
									}
								}	
							}
							
							if($SETTINGS->create_categories && !$pcat_id){
								
								$catpath = str_replace("\\","/",$catpath);
								$catpath = str_replace(" /","/",$catpath);
								$catpath = str_replace("/ ","/",$catpath);
								$catpath = fullTrim($categories_names[$I]);
								
								if($catpath[0] == '/')
									$catpath[0] = ' ';
								
								$catpath     = explode('/',$catpath);
								$drillpath   = '';
								$prev_parent = 0;
								$cid         = -1;
								
								for($cI = 0; $cI < count($catpath); $cI++){
									
									$drillpath .= ( ($cI > 0 ? "/" : "") . $catpath[$cI] ) ;
									$drillpath_n = normalizedLowercaseHash($drillpath);
									
									if(!isset($catway_asoc_reverse[$drillpath_n])){
										$csql = "   INSERT INTO 
													#__virtuemart_categories(virtuemart_category_id,virtuemart_vendor_id,category_template,category_layout,category_product_layout,products_per_row,limit_list_step,limit_list_initial,hits,metarobot,metaauthor,ordering,shared,published,created_on,created_by,modified_on,modified_by,locked_on,locked_by) 
													VALUES ( NULL,1,0,0,0,0,0,0,0,'','',0,0,1,NOW(),$user->id,NOW(),$user->id,'0000-00-00 00:00:00',0 );";
													
										$db->setQuery($csql);
										$db->query();
										$cid = $db->insertid();
										
										
										$cpsql = "INSERT INTO #__virtuemart_category_categories(
													   id
													  ,category_parent_id
													  ,category_child_id
													  ,ordering
													) VALUES (
													   NULL
													  ,$prev_parent
													  ,$cid
													  ,0
													)";
										$db->setQuery($cpsql);
										$db->query();			
										
										
										$slug = strtolower(fullTrim($catpath[$cI]));
										$slug = str_replace(array('@','*',' ','_','.','$','#',"'",'"'),
															array('' ,'' ,'-','-','-','-','-',"" ,"" ),
															$slug);
															
										foreach($vm_languages as $ltag => $lsuff){
											try{
												if($cid > 0){
													
													
													
													$db->setQuery("SELECT count(*) FROM #__virtuemart_categories_$lsuff WHERE slug = " . $db->quote( $slug));
													if($db->loadResult()){
														$slug .= "-$cid";	
													}
										
													$clsql = "INSERT INTO #__virtuemart_categories_$lsuff(
													   virtuemart_category_id
													  ,category_name
													  ,slug
													) VALUES (
													   $cid 
													  ," . $db->quote( fullTrim($catpath[$cI])) . "
													  ," . $db->quote( $slug /*str_replace(array(" ","/"),array("_","_"), $drillpath_n) . "-" . $cid*/) . "
													)";
													$db->setQuery($clsql);
													$db->query();
												}
											}catch(Exception $ic_ex){
												
											}
										}
										
										
										
										$cat = new stdClass();
										$cat->virtuemart_category_id = $cid;
										$cat->category_name          = fullTrim($catpath[$cI]);
										$cat->category_pathway       = $drillpath;
										
										$categories[] = $cat;
		
										$catway_asoc_reverse[$drillpath_n] = $cid;
										
										$catway_asoc[$cid] = $drillpath;
										
									}
									
									$prev_parent = $catway_asoc_reverse[$drillpath_n];
								}
								
								$pcat_id = $cid;
								if($pcat_id > 0)
									$new_categories_ids[] = $pcat_id;
								
								
							}
						}
						
						/*
						$db->setQuery("SELECT 
										CL.virtuemart_category_id,
										CL.category_name,
										CL.slug
									   FROM 
										#__virtuemart_categories_$vm_lang as CL
									   WHERE '|". implode("|",$categories_names) ."|' LIKE concat('%|', CL.category_name ,'|%') ");
						
						$new_categories = $db->loadObjectList();
						*/
						
						
						$db->setQuery("SELECT 
										DISTINCT PC.virtuemart_category_id
									   FROM 
										#__virtuemart_product_categories as PC
									   WHERE PC.virtuemart_product_id = $id");
						
						
						$old_categories_ids = $db->loadColumn();
						
						$to_remove = array();
						$to_add    = array();							    
						
						foreach($new_categories_ids as $ncid){
							if($ncid && !in_array($ncid,$old_categories_ids))
								$to_add[] = $ncid;
						}
						
						foreach($old_categories_ids as $ocid){
							if($ocid && !in_array($ocid,$new_categories_ids))
								$to_remove[] = $ocid;
						}
						
						if(!empty($to_remove)){
							if(implode( ",", $to_remove )){
								$db->setQuery("DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id=".$id." and virtuemart_category_id IN (".implode( ",", $to_remove ).");");
								$db->query();
							}
						}
						
						if(!empty($to_add)){
							if(implode(",",$to_add)){
								$db->setQuery("INSERT INTO #__virtuemart_product_categories(
												   id ,virtuemart_product_id ,virtuemart_category_id  ,ordering) 
												SELECT DISTINCT
												  NULL,
												  ".$id.",
												  virtuemart_category_id,
												  0
												FROM 
												  #__virtuemart_categories 
												WHERE
												  virtuemart_category_id IN (".implode(",",$to_add).")");
								$db->query();			
							}							
						}
					}
					
				}
				
				
				
				if($SETTINGS->modified_update){
				   $db->setQuery("UPDATE #__virtuemart_products SET modified_on = NOW(), modified_by = $user->id WHERE virtuemart_product_id = '$id'");
				   $db->query();
				}
				
				foreach(get_pelm_plugins_toinc("import_product") as $plg)
					include($plg);
					
				/////////////////////////////////////////////////////////////////
				$processed++;
				$n++;		
				
				
					
			}catch(Exception $ex){
				
				echo $ex->getMessage();
				echo "<br/>Exception while processing:<br/>";
				var_dump($data);
				die;
				
			}			
		}
		
		//NORMALISE manufacturers/categories for children products
		//AFTER FULL IMPORT
		$db->setQuery("SELECT virtuemart_product_id, product_parent_id FROM #__virtuemart_products WHERE coalesce(product_parent_id,0)  <> 0");
		$all_ch = $db->loadObjectList();
		foreach($all_ch as $ch){
			$p_ch  = $productModel->getProduct($ch->virtuemart_product_id,false,true,false);
			$p_par = $productModel->getProduct($ch->product_parent_id,false,true,false);
			
			if(implode(",",$p_ch->categories) == implode(",",$p_par->categories)){
				$db->setQuery("DELETE FROM #__virtuemart_product_categories WHERE virtuemart_product_id=".$ch->virtuemart_product_id);
				$db->query();
			}
			
			if(is_array($p_ch->virtuemart_manufacturer_id))
				$p_ch->virtuemart_manufacturer_id = $p_ch->virtuemart_manufacturer_id[0];
			
			if(is_array($p_par->virtuemart_manufacturer_id))
				$p_par->virtuemart_manufacturer_id = $p_par->virtuemart_manufacturer_id[0];
			
			if($p_ch->virtuemart_manufacturer_id == $p_par->virtuemart_manufacturer_id){
				$db->setQuery("DELETE FROM #__virtuemart_product_manufacturers WHERE virtuemart_product_id=".$ch->virtuemart_product_id);
				$db->query();
			}
			
		}
		//clear_csv_import_data($db);
	}
	delete_exces_images();
}


if(isset($_REQUEST["remote_import"])){
if($_REQUEST["remote_import"] == '1'){
	echo "Remote import end. Imported " . $import_count . " product rows.";
	die;
}
}


if(isset($_REQUEST["IDS"])){
	$records = explode(",",$_REQUEST["IDS"]);
}else{

	$cf_filter    = false;
	$cf_col       = false;
	$cf_col_check = false;

	foreach($custom_fields as $cf_id => $cf){
		if(isset($_REQUEST["custom_field_" . $cf_id])){
			if($_REQUEST["custom_field_" . $cf_id] !== ''){
				if(!$cf_filter){
					$cf_filter    = array();
					$cf_col       = array();
					$cf_col_check = array();
				}
				
				$cf_f = $_REQUEST["custom_field_" . $cf_id];
				
				if(PLEM_VM_RUN > 2){
					$cf_filter[] = " (CF.virtuemart_custom_id = $cf_id AND CF.customfield_value LIKE " . $db->quote("%" . $cf_f . "%") . ") ";
					$cf_col[] = " max(CASE WHEN (CF.virtuemart_custom_id = $cf_id AND CF.customfield_value LIKE '%$cf_f%') THEN 1 ELSE 0 END) As CF_$cf_id ";
					$cf_col_check[] = " CF_$cf_id  = 1"; 
				}else{
					$cf_filter[] = " (CF.virtuemart_custom_id = $cf_id AND CF.custom_value LIKE '%$cf_f%') ";
					$cf_col[] = " max(CASE WHEN (CF.virtuemart_custom_id = $cf_id AND CF.custom_value LIKE " . $db->quote("%" . $cf_f . "%") . ") THEN 1 ELSE 0 END) As CF_$cf_id ";
					$cf_col_check[] = " CF_$cf_id  = 1";
				}
			}
		}
	}

	if(isset($_REQUEST["mass_update_val"])){
		$page  = 0;
		$limit = 99999999;
	}


	$query = "	FROM 
				  #__virtuemart_products as p
				LEFT JOIN
				  #__virtuemart_product_prices as pr_p on pr_p.virtuemart_product_id = p.virtuemart_product_id  
				LEFT JOIN
				  #__virtuemart_products_".$vm_lang." as pl on pl.virtuemart_product_id = p.virtuemart_product_id
				LEFT JOIN
				  #__virtuemart_product_categories as pc on pc.virtuemart_product_id = p.virtuemart_product_id
				LEFT JOIN
				  #__virtuemart_categories_".$vm_lang." as cl on cl.virtuemart_category_id = pc.virtuemart_category_id 
				LEFT JOIN
				  #__virtuemart_product_manufacturers as pm on pm.virtuemart_product_id = p.virtuemart_product_id
				LEFT JOIN  
				  #__virtuemart_manufacturers_".$vm_lang." as ml on ml.virtuemart_manufacturer_id = pm.virtuemart_manufacturer_id
				".(
					(!$cf_filter ? "" : "
				LEFT JOIN
				  #__virtuemart_product_customfields as CF on CF.virtuemart_product_id = p.virtuemart_product_id		
				")
				)."  
				WHERE p.virtuemart_product_id > 0
				 " .($product_sku ? " AND p.product_sku LIKE " . $db->quote("%" . $product_sku . "%") . " " : ""). "
				 " .($product_name ? " AND pl.product_name LIKE " . $db->quote("%" . $product_name . "%") . " " : "")."
				  ---CATEGORY_FILTER--- 
				 " .($product_manufacturer ? " AND pm.virtuemart_manufacturer_id = ".$product_manufacturer." " : "")."
				 " .($product_in_stock ? " AND p.product_in_stock ".$product_in_stock_f." " : "")."
				 " .($product_show  ? ($product_show == 1 ? " AND coalesce(p.published,0) = 0 " : "")  : " AND coalesce(p.published,0) = 1 ");
	
    $s_query = "";
	if(!empty($cf_col)){
	 $s_query = "SELECT p.virtuemart_product_id as id, p.product_parent_id as parent ," . implode(",",$cf_col) . " " . $query . " GROUP BY p.virtuemart_product_id,p.product_parent_id ";
	}else{
	 $s_query = "SELECT p.virtuemart_product_id as id, p.product_parent_id as parent " . $query . " GROUP BY p.virtuemart_product_id,p.product_parent_id ";	
	}
				 
	$count_q = "SELECT count(*) as len FROM (".$s_query.") as inn_s ";
	
	if(!empty($cf_col_check)){
		$count_q  = $count_q. " WHERE " .implode(" AND ",$cf_col_check) . ";"; 
	}

	$cat_flt = ( $hasCatfn ?
				   ($product_category ? " AND #__plem_product_in_cats2(p.virtuemart_product_id,".$db->quote($product_category).") " : ""):
				   ($product_category ? " AND pc.virtuemart_category_id IN (".$product_category.") " : "")
	); 
	
	$count_exec_q = str_replace("---CATEGORY_FILTER---", $cat_flt ,$count_q);
	$db->setQuery($count_exec_q);
	$count = $db->loadObject();
	
	if(!$count){
		$hasCatfn = false;
		$cat_flt  = ($product_category ? " AND pc.virtuemart_category_id IN (".$product_category.") " : "");
		$count_exec_q = str_replace("---CATEGORY_FILTER---", $cat_flt ,$count_q);
		$db->setQuery($count_exec_q);
		$count = $db->loadObject();
	}
	
	$query = str_replace("---CATEGORY_FILTER---", $cat_flt ,$query);
	$s_query = str_replace("---CATEGORY_FILTER---", $cat_flt ,$s_query);
	
	$count = $count->len;
	
	$_num_sample = "0.0";
	$db->setQuery("SELECT 1 / 2 as `numeric`"); 
	$_num_sample = $db->loadObject()->numeric;

	$p_q = "SELECT id as id , parent as parent FROM (".$s_query. " ORDER BY " . $sortColumn . " " . $sortOrder .") as inn_s";
	if(!empty($cf_col_check)){
		$p_q  = $p_q. " WHERE " .implode(" AND ",$cf_col_check) . " " . ($limit ? " LIMIT ".( ($page > 1 ? ($page - 1) : 0) * $limit).",".$limit  : "") . ";"; 
	}else
		$p_q  = $p_q. " " . ($limit ? " LIMIT ".( ($page > 1 ? ($page - 1) : 0) * $limit).",".$limit  : "") . ";"; 

	
	$db->setQuery($p_q);
	$records = $db->loadAssocList("id", "parent");
	
	


	if(!$SETTINGS->par_ch_stick_disable){
		if(!empty($records)){
			
			$records_missing_children =  NULL;
			$records_missing_parent   =  NULL;
			
			do{
				$records_missing_children =  NULL;
				$records_missing_parent   =  NULL;
				
				$p_recs   = array_keys($records);
				if(!empty($p_recs)){
					$db->setQuery("SELECT DISTINCT p.virtuemart_product_id as id, p.product_parent_id as parent FROM #__virtuemart_products as p WHERE p.product_parent_id IN (". implode(",", $p_recs ) .") AND NOT p.virtuemart_product_id IN(". implode(",", $p_recs ) .")");
					$records_missing_children = $db->loadAssocList("id", "parent");
					if(!empty($records_missing_children)){
						foreach($records_missing_children as $key => $val){
							if($key)
								$records[$key] = $val; 
						}
					}
				}
				
				$par_recs = array_values($records);
				$pars = array();
				foreach($par_recs as $par_id){
					if($par_id)
						$pars[] = $par_id;	
				}
				
				$p_recs   = array_keys($records);
				if(!empty($pars) && !empty($p_recs)){
					$db->setQuery("SELECT DISTINCT p.virtuemart_product_id as id, p.product_parent_id as parent FROM #__virtuemart_products as p WHERE p.virtuemart_product_id IN (". implode(",", $pars ) .") AND NOT p.virtuemart_product_id IN(". implode(",", $p_recs ) .")");
					$records_missing_parent = $db->loadAssocList("id", "parent");
					if(!empty($records_missing_parent)){
						$prec = array();
						foreach($records_missing_parent as $key => $val){
							if($key)
								$prec[$key] = $val; 
						}
						if(!empty($prec))
							$records = $prec + $records;
					}
				}
			}while(!empty($records_missing_children) || !empty($records_missing_parent));
		}



		function tree_trav($pid, &$ch, &$parch,&$res){
			foreach($ch[$pid] as $ch_id){
				$res[] = $ch_id;
				if(isset($parch[$ch_id])){
					if($parch[$ch_id]){
						tree_trav($ch_id,$ch, $parch,$res);
					}
				}
			}	
		}


		function to_tree_order(&$array)
		{
			
			$res = array();
			
			if($array){
				if(!empty($array)){
						$par = array();
						$ch  = array();
						$parch = array();
						
						foreach ($array as $id => $parent_id) {
							if($parent_id){
								if($parent_id > 0){
									if(!isset($ch[$parent_id]))
										$ch[$parent_id]    = array();
									
									$ch[$parent_id][] = $id;
									$parch[$parent_id] = true;
								}
							}else{
								if($id)
									$par[] = $id;
							}
						}
						
						foreach($par as $pid){
							$res[] = $pid;
							if(isset($parch[$pid]))
								tree_trav($pid, $ch, $parch,$res);
						}
				}	
			}
			
			return $res;
		}

		$records = to_tree_order($records);
	}else{
		$records = array_keys($records);
	}

}



foreach(get_pelm_plugins_toinc("query_products") as $plg)
	include($plg);

$products = array(); 


$mu_res = 0;
if(isset($_REQUEST["mass_update_val"])){

 
	$ucol    = "";
	$uprop   = "pr_p.product_price";
	$exc_cnd = " AND coalesce(pr_p.product_price,'') != '' ";

	if(isset($_REQUEST['mass_update_override'])){
	if($_REQUEST['mass_update_override']){
	 $ucol = " pr_p.override = ".$SETTINGS->override_price." , ";
	 $uprop = "pr_p.product_override_price";
	 $exc_cnd = " AND coalesce(pr_p.product_override_price,'') != '' ";
	}
	} 


	if($_REQUEST["mass_update_percentage"]){
	 $ucol .= "$uprop = $uprop * (1 +  ".$_REQUEST["mass_update_val"]." / 100)";
	}else{
	 $ucol .= "$uprop = $uprop + ".$_REQUEST["mass_update_val"];
	}

	foreach(get_pelm_plugins_toinc("mass_update") as $plg)
		include($plg);
	
	foreach($records as $pr_id){
		$muquery ="   UPDATE 
					  #__virtuemart_products as p
					  LEFT JOIN
					  #__virtuemart_product_prices as pr_p on pr_p.virtuemart_product_id = p.virtuemart_product_id
					  SET
					  " . $ucol . "
					  WHERE  p.virtuemart_product_id = $pr_id 
					  ".$exc_cnd."
					  ";
		$db->setQuery($muquery);
		$mu_res += $db->query();			  
	}
	
	$db->setQuery("UPDATE #__virtuemart_product_prices SET override = 0 WHERE coalesce(product_override_price,0) = 0");
	$db->query();
	
	echo '{"mu_res":"'.$mu_res.'"}';
	die;
	return;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////


if(isset($_REQUEST["do_export"])){
	if($_REQUEST["do_export"] = "1"){
		
		
		
		////////////////////////////////////////////////////////////////////////////////////////////////////
		$pref = $db->getPrefix();
		//if( strpos($pref, "_") === false){
		//	$pref.= "_";
		//}
		
		$db->setQuery("SHOW TABLES LIKE '".$pref."virtuemart_pelm_export%'");
		if(!count($db->loadObjectList())){
			$db->setQuery("CREATE TABLE `".$pref."virtuemart_pelm_export` (
							  `id` bigint(20) NOT NULL AUTO_INCREMENT,
							  `export_uid` varchar(255) NOT NULL,
							  `data` text CHARACTER SET UTF8,
							  PRIMARY KEY (`id`)
						 ) DEFAULT CHARSET=utf8;");
			$db->query();			
		}
		////////////////////////////////////////////////////////////////////////////////////////////////////
		if(!isset($_REQUEST["export_uid"])){
		    $db->setQuery("DELETE FROM #__virtuemart_pelm_export");
			$db->query();
		}
		
		$export_uid   = isset($_REQUEST["export_uid"]) ? $_REQUEST["export_uid"] : uniqid("plem_export_");
		$export_count = isset($_REQUEST["export_count"]) ? $_REQUEST["export_count"] : 0;
		$do_export_output = isset($_REQUEST["do_export_output"]) ? $_REQUEST["do_export_output"] : 0;
		
		if($do_export_output){
			
			$filename = "csv_export_" .(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']."_" : ""). date("Y-m-d") . ".csv";
			$now = gmdate("D, d M Y H:i:s");
			header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
			header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
			header("Last-Modified: {$now} GMT");

			// force download  
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");

			// disposition / encoding on response body
			header("Content-Disposition: attachment;filename={$filename}");
			header("content-type:application/csv;charset=UTF-8");
			header("Content-Transfer-Encoding: binary");
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			
			$e_id = 0;
            $prod = null;
			$df = fopen("php://output", 'w');
			
			$separator = $SETTINGS->csv_separator_exp;
			
			
			
			
			
			$prod = null;	
			$db->setQuery("SELECT * FROM  #__virtuemart_pelm_export WHERE export_uid = '$export_uid' ORDER BY id");
			$prods = $db->loadObjectList();
			
			if(!empty($prods)){
				
				$cols = null;
				foreach($prods as $p){
					
					 $p->data = unserialize($p->data);
					 if(!$p->data)
						 continue;
					
					if(!$cols){
						$cols     = $p->data;
						$row_data = $cols;
						fputcsv($df, $row_data ,$separator);
						
					}else{
						$row_data = array();
						foreach($cols as $col){
							if(isset($p->data[$col])){
								$row_data[] = $p->data[$col];
							}else{
								$row_data[] = "";
							}
						}
						fputcsv($df, $row_data ,$separator);
					}
					unset($p->data);
				}
			}
		    
			
			fclose($df);
			die;
			return;
		}else{
			$db->setQuery("DELETE FROM  #__virtuemart_pelm_export WHERE export_uid != '$export_uid'");
			$db->query();
		}
		
		$header_output = false;
		
		if($export_count > 0)
			$header_output = true;
		
		$processed = 0 ;
		$interupt  = false;
		
		if(count($records)){
			$props = array();
			foreach(get_pelm_plugins_toinc("export") as $plg)
				include($plg);
			foreach($records as $pr_id){
				try{
					$prod       = vmel_getProduct( $pr_id,$productModel,$db,$custom_fields,$cat_asoc,$man_asoc);
					if($prod === NULL)
						continue;
					
					if(!$header_output){
						$pprops =  (array)$prod ;
						$props = array();
						foreach( $pprops as $key => $pprop){
							$props[] = $key;
						}
						//fputcsv($df, $props,$SETTINGS->csv_separator); 
						
						$db_prod = $db->quote(serialize($props));
						$db->setQuery("INSERT INTO #__virtuemart_pelm_export(id,export_uid,data) VALUES(NULL,'$export_uid', $db_prod);");
						$db->query();
						$header_output = true;
					}
					
					//fputcsv($df, (array)$prod,$SETTINGS->csv_separator);
					$db_prod = $db->quote(serialize((array)$prod));
					$db->setQuery("INSERT INTO #__virtuemart_pelm_export(id,export_uid,data) VALUES(NULL,'$export_uid', $db_prod);");
					$db->query();
					 
				}catch(Exception $e){
					$plem_errors .= "Product id:" . $record->pr_id . " broken data!";
				}
				$processed++;
				
				if($processed >= 1000){
					$interupt = true;
			        break;		
				}
			}
		}
		
		
		
		if($processed > 0){
			$export_count += $processed;
			$records = array_slice($records,$processed);
		}
		
		$output = false;
		if(empty($records))
			$output = true;
		
		if($interupt || $output){
			?>
			<!DOCTYPE html>
			<html>
				<head>
					<style type="text/css">
						html, body{
							background:#505050;
							color:white;
							font-family:sans-serif;	
						}
					</style>
				</head>
				<body>
					<form method="POST" id="continueExportForm">
						<h2><?php echo pelm_sprintf("Exporting..."); ?></h2>
						<p><?php echo $export_count; ?> / <?php echo $export_count + count($records); ?> </p>
						<hr/>
						<?php foreach($_POST as $p_parm => $p_value){ 
						   if(
							  $p_parm == "export_count"
							  ||
							  $p_parm == "IDS"
							  ||
							  $p_parm == "do_export"
							  ||
							  $p_parm == "export_uid"
							 )
								continue;
						?>
						<input type="hidden" name="<?php echo $p_parm;?>" value="<?php echo $p_value;?>">	
						<?php } ?>
						<input type="hidden" name="export_count" value="<?php echo $export_count;?>">
						<input type="hidden" name="IDS" value="<?php echo implode(",",$records);?>">
						<input type="hidden" name="do_export" value="1">
						<input type="hidden" name="export_uid" value="<?php echo $export_uid; ?>">
						<?php if($output){
							?>
								<input type="hidden" name="do_export_output" value="1">
							<?php
						} ?>
					</form>
					<script data-cfasync="false"  type="text/javascript">
							document.getElementById("continueExportForm").submit();
					</script>
				</body>	
			</html>
			<?php
			die;
			return;
		}
		
	   
	    
		
		die();
	    exit;  
	    return;
	}
}


/*else{
	
	if(count($records)){
		foreach($records as  $pr_id){
			try{
				$prod       = vmel_getProduct($pr_id,$productModel,$db,$custom_fields,$cat_asoc,$man_asoc);  
				if($prod === NULL){
					continue;
				}	
				$products[] = $prod;		 
			}catch(Exception $e){
				$plem_errors .= "Product id:" . $pr_id . " broken data!";
			}
		}
	}
}*/



pelm_clearCache();


?>
<html>
<head>
<meta charset="UTF-8">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/lib/jquery-2.0.3.min.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/m/moment.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/pday/pikaday.css'; ?>">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/pday/pikaday.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/zc/ZeroClipboard.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/jquery.handsontable.css'; ?>">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/jquery.handsontable.js'; ?>" data-cfasync="false"  type="text/javascript"></script>


<?php if( $SETTINGS->allow_delete){ ?>
<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/removeRow.css'; ?>">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/handsontable/removeRow.js'; ?>" data-cfasync="false"  type="text/javascript"></script>
<?php } ?>

<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/lib/chosen.min.css'; ?>">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/lib/chosen.jquery.min.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/assets/style.css'; ?>">
<!--
<link rel="stylesheet" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/assets/tinyeditor.css'; ?>">
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/lib/tiny.editor.packed.js'; ?>" data-cfasync="false"  type="text/javascript"></script>
-->

<script data-cfasync="false"  type="text/javascript">
var pelm_version = 2;
var Base64 = null;
var pelm_plugins = [];

try{
	Base64 = {_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}};
}catch(e){
	//
}

var localStorage_clear_flag = false;
function cleanLayout(){
	localStorage.clear();
	localStorage_clear_flag = true;
	doLoad();
	return false;
}

function cleanImages(){
	jQuery.ajax({
		data: "p_clean_images=1",
		success: function(data){
			try{
				data = eval("(" + data + ")");
			}catch(rex){}
			
			alert(data.removed + ' <?php echo pelm_sprintf('COM_VMEXCELLIKEINPUT_CLEAN_IMAGES_SUCCESS'); ?>');	
		},
		error: function(x,y,z){
			alert('<?php echo pelm_sprintf('COM_VMEXCELLIKEINPUT_CLEAN_IMAGES_ERR'); ?>');	
		}	
	});
};

function showSettings(){
    jQuery('#settings-panel').show();
}

jQuery(document).ready(function(){
    jQuery('#cmdSettingsSave').click(function(){
		doLoad(true);
	});
	
	jQuery('#cmdSettingsCancel').click(function(){
		jQuery('#settings-panel').hide();
	});
});




</script>
<?php

foreach(get_pelm_plugins_toinc("header") as $plg)
	include($plg);

?>
<script data-cfasync="false"  type="text/javascript">
var pending_load = 0;
function doLoad(withSettingsSave){
	if(!window.pelm_load_ok){
		var limit = <?php echo $limit; ?>;
		limit = parseInt(limit / 2); 
		
	    if(limit > 50){
			window.location.href = window.location.href.split("&limit")[0] + "&limit=" + limit + "&fail_load=1";
			return;
		}
	}
	
    pending_load++;
	if(pending_load < 6){
		var n = 0;
		for(var key in tasks)
			n++;
			
		if(n > 0) {
		  setTimeout(function(){
			doLoad();
		  },2000);
		  return;
		}
	}

    var POST_DATA = {};
	
	POST_DATA.sortOrder            = $('#dg').data('handsontable').sortOrder ? "ASC" : "DESC";
	POST_DATA.sortColumn           = getSortProperty();
	POST_DATA.limit                = $('#txtlimit').val();
	POST_DATA.page                 = $('#paging_page').val();
	POST_DATA.vmlang        = $('#edit_language').val(); 
	
	jQuery(".filter_holder > DIV.filter_option *[name]").each(function(i){
		POST_DATA[jQuery(this).attr("name")] = jQuery(this).val();
	});
	
	if(withSettingsSave){
	  var settings = {};
	  jQuery('#settings-panel INPUT[name],#settings-panel TEXTAREA[name],#settings-panel SELECT[name]').each(function(i){
		if(jQuery(this).attr('type') == "checkbox")
			settings[jQuery(this).attr('name')] = jQuery(this)[0].checked ? 1 : 0;
		else
			settings[jQuery(this).attr('name')] = jQuery(this).val(); 
	  });
	  POST_DATA.save_settings = JSON.stringify(settings);
	}
	
    jQuery('#operationFRM').empty();
	
	for(var key in POST_DATA){
		if(POST_DATA[key])
			jQuery('#operationFRM').append("<INPUT type='hidden' name='" + key + "' value='" + POST_DATA[key] + "' />");
	}
	
    jQuery('#operationFRM').submit();
}

jQuery(window).load(function(){
	setTimeout(function(){
		if(!window.pelm_load_ok)
			doLoad();
		else if(window.location.href.indexOf("fail_load") > 0){
			alert("P.E.L.M. was not able to complete load with given 'product per page limit' probably due site resources limitations.\nPlese ask your server administrator so check allowed memory, max. execution time and max. response size for your server or keep product per page limit at current value!");
		}
	},500); 
});

</script>
</head>
<body>
<div class="header">


<ul class="menu">
  <li><span class="back">
		<a class="cmdBackToJoomla" href="<?php echo JURI::root(1) . '/administrator/'; ?>" > <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_BACK_TO_JOOMLA"); ?> </a>
  </span></li>

  <li><span class="undo"><button id="cmdUndo" onclick="undo();" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_UNDO"); ?></button></span></li>
  <li><span class="redo"><button id="cmdRedo" onclick="redo();" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_REDO"); ?></button></span></li>
  <li><span class="copy"><button id="cmdCopy" onclick="copy();" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLONE"); ?></button></span></li>
  <?php
  if($SETTINGS->allow_delete){
  ?>		
  <li><span class="delete"><button id="cmdDelete" onclick="deleteproducts();" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DELETE"); ?></button></span></li>
 <?php
  }
  ?> 
  <li>
   <span><span> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT_IMPORT"); ?> &#9655;</span></span>
   <ul>
     <li><span><button onclick="do_export();return false;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT"); ?></button></span></li>
     <li><span><button onclick="do_import();return false;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT"); ?></button></span></li>
   </ul>
  </li>
  <li>
   <span><span> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OPTIONS"); ?> &#9655;</span></span>
   <ul>
     <li><span><button onclick="cleanLayout();return false;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLEAN_CACHE"); ?></button></span></li>
	 <li><span><button onclick="cleanImages();return false;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLEAN_IMAGES"); ?></button></span></li>
	 <li><span><button onclick="showSettings();return false;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SETTINGS"); ?></button></span></li>
     <li id="help_item" ><span><a target="_blank" href="<?php echo "http://www.holest.com/vm-excel-like-product-manager-documentation"; ?>" > <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_HELP"); ?> </a></span></li>
   </ul>
  </li>
  <li id="adata-search" style="min-width:200px;">
  <input style="width:130px;display:inline-block;" type="text" id="activeFind" placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_AD_SEARCH"); ?>" />
  <span style="display:inline-block;" id="search_matches"></span>
  <button id="cmdActiveFind" >&#9655;&#9655;</button> 
  </li>
  <!--
  <li style="font-weight: bold;">
   <span><a style="color: cyan;font-size: 16px;" href="http://holest.com/index.php/holest-outsourcing/joomla-wordpress/virtuemart-excel-like-product-manager.html">Buy this component!</a></span> 
  </li>
  -->
</ul>
<ul class="lng-menu" style="float:right;">
	<li>
     <span>
		 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_LANGUAGE"); ?></label>
		 <select id="edit_language" style="color:black;" class="save-state" >
		  <?php
		  foreach($vm_languages as $lng => $db_suffix){
			$selected = "";
			if($db_suffix == $vm_lang)
				$selected = ' selected="selected" ';
			
		  ?>
		  <option value="<?php echo $lng;?>" <?php echo $selected;?> ><?php echo $lng; ?></option>
		  <?php
		  }
		  ?>
		 </select>	 
	 </span>
   </li>
</ul>


</div>
<div class="content">
<div class="right_panel opened filtering">
<span class="right_panel_label" ><span class="toggler"><span><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILTERS");?></span></span></span>

<div class="filter_holder">
  <?php if($SETTINGS->prices) { ?>
  <div id="custom_prices">
   <h4><?php echo pelm_sprintf("Quantity"); ?>/<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SHOPPERGROUP"); ?> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_PRICE"); ?><span></span>:</h4>
   <table cellpadding="0" cellspacing="0" >
	<thead>
	<tr>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SHOPPERGROUP"); ?></th>
		<th>&gt;</th>
		<th>&lt;</th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_PRICE"); ?></th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SALES"); ?></th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_OVERRIDE"); ?></th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_TAX"); ?></th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_DISCOUNT"); ?></th>
		<th><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_CURRENCY"); ?></th>
		
		<th></th>
	</tr>
	</thead>
	<tbody>
	</tbody>
	<tfoot>
		<th colspan="10" ><a class="cmdAddPrice" >+ <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_PRICE"); ?></a></th>
	</tfoot>
   </table>
  </div>
  <?php } ?>
  
  <div class="filter_option">
     <input id="cmdRefresh" type="submit" class="cmd" value="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_REFRESH");?>" onclick="doLoad();" />
  </div>

  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SKU");?></label>
	 <input placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SKU");?>" type="text" name="product_sku" value="<?php echo $product_sku;?>"/>
  </div>
  
  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_NAME");?></label>
	 <input placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_NAME");?>" type="text" name="product_name" value="<?php echo $product_name;?>"/>
  </div>
  
  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_MANUFACTURER");?></label>
	 <select placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_MANUFACTURER"); ?>" name="product_manufacturer">
	 <option value=""></option>
	 <?php
	    foreach($manCats as $mancat){
		  if(count($manCats) > 1) echo "<optgroup label='".$mancat->mf_category_name."'>";
		  foreach($manufacturers as $man){
		    echo '<option value="'.$man->virtuemart_manufacturer_id.'">'.$man->mf_name.'</option>';
		  }
		  if(count($manCats) > 1) echo "</optgroup>";
		}
	 ?>
	 </select>
  </div>
  
  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_CATEGORY");?></label>
	 <?php
		echo '<select data-placeholder="'.pelm_sprintf("COM_VMEXCELLIKEINPUT_SELECT_CATEGORIES").'" class="inputbox" multiple name="product_category" >';
		echo  '<option value=""></option>';
		foreach($categories as $cat){
			echo  '<option value="'.$cat->virtuemart_category_id.'" >'.$cat->category_path.'</option>';
		}
		echo "</select>";
	 ?>
  </div>

  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_STOCK");?> (<, >, >=, <=, AND)</label>
	 <input placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_STOCK");?>" type="text" name="product_in_stock" value="<?php echo $product_in_stock;?>"/>
  </div>
  
  <div class="filter_option">
     <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SHOW");?></label>
	 <select name="product_show">
		 <option value="0"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_ONLY_PUBLISHED");?></option>
		 <option value="1"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_ONLY_ULPUBLISHED");?></option>
		 <option value="2"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_ALL");?></option>
	 </select>
  </div>
  
  <?php
    foreach($custom_fields as $cf_id => $cf){
		?>
		<div class="filter_option">
		<label><?php echo pelm_sprintf($cf['custom_title']);?></label>
		<input placeholder="<?php echo pelm_sprintf($cf['custom_title']);?>" type="text" name="<?php echo "custom_field_" . $cf_id; ?>" value="<?php echo isset($_REQUEST["custom_field_" . $cf_id]) ? $_REQUEST["custom_field_" . $cf_id] : "";?>"/>
	    </div>
		<?php
	}
 ?>
  
  
  
  
  
  <br/>
  <br/>
  <hr/>
  
  <div class="filter_option">
	  <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MASS_UPDATE"); ?></label> 
	  <input style="width:110px;float:left;" placeholder="<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MU_WATERMARK",'%'); ?>" type="text" id="txtMassUpdate" value="" /> 
	  <button id="cmdMassUpdate" class="cmd" onclick="massUpdate(false);return false;" style="float:right;"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRICE_UPDATE"); ?></button>
	  <button id="cmdMassUpdateOverride" class="cmd" onclick="massUpdate(true);return false;" style="float:right;"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OVERRIDE_UPDATE"); ?></button>
	  
  </div>
  
</div>

<div id="images_browser" class="aux-editor" >
	<div class="mask"></div>
</div>

<div id="images_editor" class="aux-editor" >
	<div class="mask"></div>
	<button class="back-to-filter">&lt;&lt;&nbsp;&nbsp;<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILTERS");?></button>
    <button class="save_product_images"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE");?></button>
	<h3 class="product_info"></h3>
	<div class="main_image">
	
	</div>
	<table id="dg_images" cellpadding="0" cellspacing="0" >
		<thead>
		<tr class="header">
		    <th class="move" ></th>
			<th class="order" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_ORDER");?></th>
			<th class="published" >Publish</th>
			
			<th class="ismain" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT");?> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMAGE");?></th>
			<th class="alt_title" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_TITLE");?> / <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DESCRIPTION");?> / <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_METAINFO");?></th>
			<th class="thumb" ></th>
			<th class="delete"></th>
		</tr>
		</thead>
		<tbody>
		
		</tbody>
		<tfoot>
		<tr class="footer">
		<td colspan="7">
			<label>Ask for names:</label>  
			<input style="position: relative;top: 6px;" type="checkbox" name="file" id="p_img_upload_ask_names" class="p_img_upload_ask_names">
			
		    <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_ADDIMAGES") ;?>:</label>  
			<input type="file" name="file" id="p_img_upload" multiple class="product-image-upload">
			<div class="data">
			</div>
		</td>
		</tr>	
		</tfoot>
	</table>
	
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("#p_img_upload_ask_names").prop("checked",!!localStorage['p_img_upload_ask_names']);	
})

jQuery(document).on('change','#p_img_upload_ask_names',function(e){
	if(jQuery("#p_img_upload_ask_names").prop("checked"))
		localStorage['p_img_upload_ask_names'] =1;
	else
		delete localStorage['p_img_upload_ask_names'];
})
</script>


<div id="content_editor" class="aux-editor" >
	<div class="mask"></div>
	<button class="back-to-filter">&lt;&lt;&nbsp;&nbsp;<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILTERS");?></button>
    <button class="save_product_content"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE");?></button>
	<h3 class="product_info"></h3>
	<iframe id="content_edit_ifr" src="index.php?option=com_vmexcellikeinput&content_edit=1"></iframe>
	
	<?php
		
	?>
</div>

</div>

<div id="dg" style="margin-left:1px;margin-top:1px;overflow: scroll;background:#FBFBFB;">
</div>

</div>
<div class="footer">
 <div class="pagination">
   <label for="txtLimit" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_LIMIT");?></label><input id="txtlimit" class="save-state" style="width:40px;text-align:center;" value="<?php echo $limit;?>"  />
   <?php
       if($limit && ceil($count / $limit) > 1){
	    ?>
	       <input type="hidden" id="paging_page" value="<?php echo $page ?>" />	
		   
		<?php
		  if($page > 1){
		   ?>
		   <span class="page_number" onclick="setPage(this,1);return false;" ><<</span>
		   <span class="page_number" onclick="setPage(this,'<?php echo ($page - 1); ?>');return false;" ><</span>
		   <?php
		  }
		  
	      for($i = 0; $i < ceil($count / $limit); $i++ ){
		    if(($i + 1) < $page - 2 ) continue;
			if(($i + 1) > $page + 2) {
              echo "<label>...</label>";			  
			  break;
			}
		    ?>
              <span class="page_number <?php echo ($i + 1) == $page ? " active " : "";  ?>" onclick="setPage(this,'<?php echo ($i + 1); ?>');return false;" ><?php echo ($i + 1); ?></span>
            <?php			
		  }
		  
		  if($page < ceil($count / $limit)){
		   ?>
		   <span class="page_number" onclick="setPage(this,'<?php echo ($page + 1); ?>');return false;" >></span>
		   <span class="page_number" onclick="setPage(this,'<?php echo ceil($count / $limit); ?>');return false;" >>></span>
		   <?php
		  }
		  
	   }
   ?>
   <span class="pageination_info"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PAGINATION",$page,ceil($count / $limit),"<span id='rcount'>".$count."</span>"); ?></span>
   
 </div>
 
 <span class="note" style="float:right;"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CHANGES_ARE_AUTOSAVED");?></span>
 <span class="wait save_in_progress" ></span>
 
</div>
<iframe id="frameKeepAlive" style="display:none;"></iframe>

<form id="operationFRM" method="POST" >

</form>

<script data-cfasync="false"  type="text/javascript">
var categories = <?php echo json_encode($categories);?>;
var manufacturers = new Array();
//var manCategories = <?php echo json_encode($manCats); ?>;
var asoc_cats  = {};
var asoc_mans  = <?php echo json_encode($man_asoc);?>;
var tasks      = {};
var DG         = null;
var SUROGATES  = {};

var multidel     = false;
var sortedBy     = 0;
var sortedOrd    = true;
var explicitSort = false;
var jumpIndex    = 0;
var page_col_w   = {};

var taxs_asoc = <?php echo json_encode( $taxs_asoc ); ?>;
var disc_asoc = <?php echo json_encode( $disc_asoc ); ?>;
var curr_asoc = <?php echo json_encode( $curr_asoc ); ?>;
var cf_val_separator = "<?php echo $SETTINGS->cf_val_separator ?>";


var ContentEditorCurrentlyEditing = {};


if(localStorage['dg_page_col_w'])
	page_col_w = eval("(" + localStorage['dg_page_col_w'] + ")");


var site_url   = '<?php echo JURI::root(1); ?>/';

window.onbeforeunload = function() {
    try{
		localStorage['dg_page_col_w'] = JSON.stringify(page_col_w);
		pelmStoreState();
	}catch(e){}  
	
    var n = 0;
	for(var key in tasks)
		n++;
     
	if(n > 0){
	  doSave();
	  return "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PAGE_LEAVE");?>";
	}else
	  return;	   
}

for(var c in categories){
  asoc_cats[categories[c].virtuemart_category_id] = categories[c].category_path;
}

for(var mf_id in asoc_mans){
	manufacturers.push({
		virtuemart_manufacturer_id : mf_id,
		mf_name: asoc_mans[mf_id]
	});
}

var keepAliveTimeoutHande = null;
var resizeTimeout
  , availableWidth
  , availableHeight
  , $window = $(window)
  , $dg     = $('#dg');


var calculateSize = function () {
  var offset = $dg.offset();
  
  $('div.content').outerHeight(window.innerHeight - $('BODY > DIV.header').outerHeight() - $('BODY > DIV.footer').outerHeight());
  
  availableWidth = $('div.content').innerWidth() - offset.left + $window.scrollLeft() - (jQuery('.right_panel').innerWidth() + parseInt(jQuery('.right_panel').css('right')));
  availableHeight = $('div.content').innerHeight();
  $('.right_panel').css('height',(availableHeight) + 'px');
  
  //$('#dg').handsontable('render');
  if(DG)
	DG.updateSettings({ width: availableWidth, height: availableHeight });

  jQuery('.right_panel_label .toggler').outerHeight(jQuery('.right_panel').innerHeight());
  
  //jQuery(".tinyeditor > DIV.edit-panel").innerHeight(jQuery('DIV.right_panel').innerHeight() - 90);
	
};

calculateSize();
$window.on('resize', calculateSize);  


jQuery(document).ready(function(){calculateSize();});
jQuery(window).load(function(){calculateSize();});  

jQuery('#frameKeepAlive').blur(function(e){
     e.preventDefault();
	 return false;
   });
   
function setKeepAlive(){
   if(keepAliveTimeoutHande)
	clearTimeout(keepAliveTimeoutHande);
	
   keepAliveTimeoutHande = setTimeout(function(){
	  jQuery('#frameKeepAlive').attr('src',window.location.href + "&keep_alive=1&diff=" + Math.random() + ("&vmlang=" + jQuery("#edit_language").val() || "en-GB"));
	  setKeepAlive();
   },30000);
}

function setPage(sender,page){
	jQuery('#paging_page').val(page);
	jQuery('.page_number').removeClass('active');
	jQuery(sender).addClass('active');
	doLoad();
	return false;
}



function getSortProperty(){
    if(!DG)
		DG = $('#dg').data('handsontable');
	
 	if(!DG)
		return "virtuemart_product_id";
   	
    var prop = DG.colToProp( DG.sortColumn);
	
	if(prop == "c_id" || prop == "i_id" || DG.getSettings().columns[DG.sortColumn].sortable === false)
		return "virtuemart_product_id";
	
	return prop;
}



function massUpdate(update_override){
    if(!jQuery.trim(jQuery('#txtMassUpdate').val())){
	  alert("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_NO_VALUE");?>");
	  return;
	} 

	if(confirm("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MASS_CONFIRM");?>")){
		var POST_DATA = {};
		
		POST_DATA.mass_update_val        = parseFloat(jQuery('#txtMassUpdate').val()); 
		POST_DATA.mass_update_percentage = (jQuery('#txtMassUpdate').val().indexOf("%") >= 0) ? 1 : 0;
		POST_DATA.mass_update_override   = update_override ? '1' : '0';
		
		POST_DATA.sortOrder            = $('#dg').data('handsontable').sortOrder ? "ASC" : "DESC";
		POST_DATA.sortColumn           = getSortProperty();
		POST_DATA.limit                = $('#txtlimit').val();
		POST_DATA.page                 = $('#paging_page').val() || 0;
		POST_DATA.vmlang        = $('#edit_language').val();
		
		jQuery(".filter_holder > DIV.filter_option *[name]").each(function(i){
			POST_DATA[jQuery(this).attr("name")] = jQuery(this).val();
		});
		
		/*
		jQuery('#operationFRM').empty();
		
		for(var key in POST_DATA){
			if(POST_DATA[key])
				jQuery('#operationFRM').append("<INPUT type='hidden' name='" + key + "' value='" + POST_DATA[key] + "' />");
		}
		*/
		
		jQuery.ajax({
			url: window.location.href + "&diff=" + Math.random()+ ("&vmlang=" + jQuery("#edit_language").val() || "en-GB"),
			type: "POST",
			jsonp:false,
			dataType: "json",
			data: POST_DATA,
			success: function (data){
				alert('(updated count: ' + data.mu_res + ') <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MASS_UPDATE_RESULT","%VALUE%"); ?>'.replace('%VALUE%',(POST_DATA.mass_update_val < 0 ? "-" : "+") + POST_DATA.mass_update_val + (POST_DATA.mass_update_percentage ? "%" : "")));
				doLoad();
			},
			error: function(a,b,c){
				alert("ERROR");
			}
		});
		
		//jQuery('#operationFRM').submit();
	}
}

var saveHandle = null;
var save_in_progress = false;
var id_index         = null;

function build_id_index_directory(rebuild){
	if(rebuild)
		id_index = null;
	
	if(!id_index){
		id_index = [];
		var n = 0;
		DG.getData().map(function(s){
		  if(id_index[s.virtuemart_product_id])
			id_index[s.virtuemart_product_id].ind = n;
		  else
			id_index[s.virtuemart_product_id] = {ind:n,ch:[]}; 
		  
		  if(s.parent){
			  if(id_index[s.parent])
				id_index[s.parent].ch.push(n);
			  else
				id_index[s.parent] = {ind:-1,ch:[n]}; 
		  }  			  
		  n++;
		});
	}	
}


function doSave(callback, error_callback){
	var update_data = JSON.stringify(tasks); 	   
	save_in_progress = true;
	jQuery(".save_in_progress").show();

	jQuery.ajax({
	url: window.location.href + "&DO_UPDATE=1&diff=" + Math.random()+ ("&vmlang=" + jQuery("#edit_language").val() || "en-GB"),
	type: "POST",
	jsonp:false,
	dataType: "json",
	data: update_data,
	success: function (data) {
		build_id_index_directory();
		var rebuild_indexes = false;
		var re_sort         = false;
		
		
		for(var i = 0; i < data.length ; i++){
			
			if(data[i].clones){
				var insert_index = (DG.countRows() - (DG.getSettings().minSpareRows || 0));	
				var insert_data = data[i].clones;
				for(var p_id in insert_data){
					try{
						DG.alter("insert_row",insert_index);
						if (insert_data.hasOwnProperty(p_id)) {
							
							for( var prop in insert_data[p_id]){
								try{
									if (insert_data[p_id].hasOwnProperty(prop)) {
										DG.getSourceDataAtRow(insert_index)[prop] = insert_data[p_id][prop];
									}
								}catch(ex1){}
							}
						}
					}catch(ex1){}
					insert_index++;
					rebuild_indexes = true;
				}
				re_sort = true;
			}
		
			if(data[i].surogate){
				var row_ind = SUROGATES[data[i].surogate];
				if(data[i].full){
					for(var prop in data[i].full){
						try{
							if (data[i].full.hasOwnProperty(prop)) {
								DG.getSourceDataAtRow(row_ind)[prop] = data[i].full[prop];
							}
						}catch(e){}
					}
					if(data[i].full.virtuemart_product_id){
						if(id_index[data[i].full.virtuemart_product_id])
							id_index[data[i].full.virtuemart_product_id].ind = row_ind;
						else
							id_index[data[i].full.virtuemart_product_id] = {ind:row_ind,ch:[]}; 
					}
				}
			}else{
				if(data[i].returned){
					
					for(var prop in data[i].returned){
						try{
							if (data[i].returned.hasOwnProperty(prop)) {
								if(prop != 'dg_index'){
									
									var row_ind = id_index[data[i].virtuemart_product_id].ind;
									DG.getSourceDataAtRow(row_ind)[prop] = data[i].returned[prop];
									
									if(prop == 'prices'){
										if(jQuery('#custom_prices > table')[0]){
											if(data[i].returned.dg_index == jQuery('#custom_prices > table').attr("dg_index")){
												refreshCustomPrices(data[i].returned[prop]);
											}
										}
									}
									
								}
							}
						}catch(e){}
					}
					
				}
			}
			
			var dep = data[i].dependent;
			if(dep){
				for(var p_id in dep){
					try{
						for(var prop in dep[p_id]){
							if (dep[p_id].hasOwnProperty(prop)){
								var row_ind = id_index[p_id].ind;
								DG.getSourceDataAtRow(row_ind)[prop] = dep[p_id][prop];
							}
						}
					}catch(dpex){
							//
					}
				}
			}
		}
		
		if(rebuild_indexes)
			build_id_index_directory(true);

		if(re_sort){
			explicitSort = true;
			DG.sort( DG.sortColumn , DG.sortOrder);
			explicitSort = false;
		}
		
		var updated = eval("(" + update_data + ")");
		for(key in updated){
		 if(tasks[key]){
			if(JSON.stringify(tasks[key]) == JSON.stringify(updated[key]))
				delete tasks[key];
		 }
		}

		save_in_progress = false;
		jQuery(".save_in_progress").hide();
		
		if(callback){
			try{
				callback(data);
			}catch(ex){}
		}
		DG.render();
		
		jQuery("#rcount").html(DG.countRows() - 1);

	},
	error: function(a,b,c){
		try{	
			if(a.status == 200 || a.status == 500){
				var failed = eval("(" + this.data + ")");
				if(failed){
					for(var uid in failed){
						if(failed.hasOwnProperty(uid)){
							delete tasks[uid];
						}
					}
				}
				alert("Request failed with code " + a.status + "\n\nRequest data:\n" + this.data);
			}
		}catch(ex){
			//	
		}
		
		save_in_progress = false;
		jQuery(".save_in_progress").hide();
		if(error_callback){
			try{
				tasks = {};
				error_callback();
			}catch(ex){}
		}else
			callSave();
		
	}
	});
}

function callSave(){
    if(saveHandle){
	   clearTimeout(saveHandle);
	   saveHandle = null;
	}
	
	saveHandle = setTimeout(function(){
	   saveHandle = null;
	   
	   if(save_in_progress){
	       setTimeout(function(){
			callSave();
		   },2000);
		   return;
	   }
       doSave();
	},2000);
}

function undo(){
	$('#dg').data('handsontable').undo();
}

function redo(){
	$('#dg').data('handsontable').redo();
}

function numf(num,dec){
	if(!num)
		return "";
		
	var res = "";	
	if(dec && dec > 0){
		res = parseFloat(num).toFixed(dec);
	}else{
		res = parseInt(num);	
	}
	if(isNaN(res))
		return "";
	else 
		return res;
}

function loadCustomPrices(product_id, dg_index , prices, product_name){
    if(prices){
		jQuery('#custom_prices').show();
		var pGrid = jQuery('#custom_prices > table > tbody');
		pGrid.find('> tr').remove();
		jQuery('#custom_prices > table').attr('virtuemart_product_id', product_id)
										.attr('dg_index',dg_index);
		
		jQuery('#custom_prices > h4 > span').html("(" + product_name + ")");
		for( var i = 0; i < prices.length; i++){
			var row = jQuery("<tr alter_no='0'>"
							   +"<td class='group' >" + jQuery(".hidden-control-models .shopper-groups").html() + "</td>"
							   +"<td class='qstart text-center' ><input class='integer' type='text' /></td>"
							   +"<td class='qend text-center' ><input class='integer' type='text' /></td>"
							   +"<td class='price text-right' ><input class='numeric' type='text' /></td>"
							   +"<td class='sales text-right' ><input class='numeric' type='text' /></td>"
							   +"<td class='override numeric text-right'><input type='text' /></td>"
							   +"<td class='tax'>" + jQuery(".hidden-control-models .tax").html() + "</td>"
						       +"<td class='discount'>" + jQuery(".hidden-control-models .discount").html() + "</td>"
						       +"<td class='currency'>" + jQuery(".hidden-control-models .currency").html() + "</td>"
							   +"<td class='remove'><a>&times;</a></td>"
							+"</tr>");
			
			row.find(".group SELECT").val( prices[i].sg_id );
			row.find(".qstart INPUT").val( numf( prices[i].q_start));
			row.find(".qend INPUT").val( numf(prices[i].q_end));
			row.find(".price INPUT").val( numf(prices[i].price,2));
			row.find(".sales INPUT").val( numf(prices[i].sales_price,2));
			row.find(".override INPUT").val(prices[i].override != "0" ?  numf(prices[i].price_override,2) : "0.00");
			
			row.find(".tax SELECT").val( prices[i].product_tax_id );
			row.find(".discount SELECT").val( prices[i].product_discount_id );
			row.find(".currency SELECT").val( prices[i].product_currency );
			
			pGrid.append(row);
			row.attr('id', prices[i].pp_id );
		}
	}else
		jQuery('#custom_prices').hide();
	return false;
};

function saveCustomPrices(){
	var virtuemart_product_id = jQuery('#custom_prices > table').attr('virtuemart_product_id');
	var dg_index              = jQuery('#custom_prices > table').attr('dg_index');
	var prices     = [];
	
	jQuery('#custom_prices > table > tbody > TR').each(function(i){
	    var row   = jQuery(this);
		var price = {};
		price.pp_id          = row.attr("id");
		price.sg_id          = row.find(".group SELECT").val();
		price.q_start        = row.find(".qstart INPUT").val();
		price.q_end          = row.find(".qend INPUT").val();
		price.price_override = row.find(".override INPUT").val();
		price.price          = row.find(".price INPUT").val();
		price.sales_price    = row.find(".sales INPUT").val();
		
		price.product_tax_id      = row.find(".tax SELECT").val();
		price.product_discount_id = row.find(".discount SELECT").val();
		price.product_currency    =	row.find(".currency SELECT").val();
		
		if((parseInt(row.find(".price INPUT").attr("alter_no")) | 0) < (parseInt(row.find(".sales INPUT").attr("alter_no")) | 0)){
			price.lastset = "sales";
		}else{
			price.lastset = "price";	
		}
		prices.push(price);
	});
	
	if(!tasks[virtuemart_product_id])
		tasks[virtuemart_product_id] = {};
	tasks[virtuemart_product_id]["prices"]   = prices;
	tasks[virtuemart_product_id]["dg_index"] = dg_index;
	callSave();
};

function refreshCustomPrices(prices){
	try{
		for( var i = 0; i < prices.length; i++ ){
			var row = null;
			if(prices[i].surogate){
				row = jQuery('#custom_prices > table > tbody > TR[id="' + prices[i].surogate + '"]');
				if(row[0])
					row.attr("id", prices[i].pp_id );
			}else
				row = jQuery('#custom_prices > table > tbody > TR[id="' + prices[i].pp_id + '"]');
			if(row[0]){
				if( parseFloat( prices[i].price ) !=  parseFloat( row.find(".price INPUT").val()))
					row.find(".price INPUT").val( numf(prices[i].price,2));
				if( parseFloat( prices[i].sales_price ) !=  parseFloat( row.find(".sales INPUT").val()))
					row.find(".sales INPUT").val( numf(prices[i].sales_price,2));
			}
		}
	}catch(e){}
};

jQuery(document).on("click touchstart",'a.cmdAddPrice',function(e){
	e.preventDefault();
	var pGrid = jQuery('#custom_prices > table > tbody');
	var row = jQuery("<tr alter_no='0' >"
						   +"<td class='group' >" + jQuery(".hidden-control-models .shopper-groups").html() + "</td>"
						   +"<td class='qstart text-center' ><input class='integer' type='text' /></td>"
						   +"<td class='qend text-center' ><input class='integer' type='text' /></td>"
						   +"<td class='price text-right' ><input class='numeric' type='text' /></td>"
						   +"<td class='sales text-right' ><input class='numeric' type='text' /></td>"
						   +"<td class='override numeric text-right'><input type='text' /></td>"
						   +"<td class='tax'>" + jQuery(".hidden-control-models .tax").html() + "</td>"
						   +"<td class='discount'>" + jQuery(".hidden-control-models .discount").html() + "</td>"
						   +"<td class='currency'>" + jQuery(".hidden-control-models .currency").html() + "</td>"
						   +"<td class='remove'><a>&times;</a></td>"
					    +"</tr>");
	pGrid.append(row);
	row.attr('id', "s_" + parseInt( Math.random() * 100000) );
	
	return false;
});

jQuery(document).on("click touchstart",'#custom_prices > table > tbody TD.remove a' ,function(e){
	e.preventDefault();
	var TR = jQuery(this).closest("TR");
	TR.remove();	
	saveCustomPrices();
});

jQuery(document).on("change",'#custom_prices > table > tbody TD INPUT, #custom_prices > table > tbody TD SELECT' ,function(e){
	var TR = jQuery(this).closest("TR");
	TR.attr('alter_no', parseInt(TR.attr('alter_no')) + 1);
	jQuery(this).attr('alter_no', TR.attr('alter_no'));
	
	if(jQuery(this).val()){
		if(jQuery(this).is(".numeric")){
		   jQuery(this).val(numf(jQuery(this).val(),2));
		}else if(jQuery(this).is(".integer")){
		   jQuery(this).val(numf(jQuery(this).val(),0));
		}
	}
	
	saveCustomPrices();
});

var namevalueToDictionary = function(arr){
	    var d = {};
		for(var ind in arr){
		    d[arr[ind].value] = arr[ind].name;
		}
		return d; 
};
	
var dictionaryToNamevalue = function(dict){
	var arr = [];
	for(var prop in dict){
		if( dict.hasOwnProperty(prop)){
			arr.push({
				"name" :  dict[prop],
				"value" : prop
			});	
		}
	}
	return arr;
};

var grid_headers = null;
var grid_columns = null;
var visible_grid_headers = [];
var visible_grid_columns = [];
var grid_columns_asoc = null;

jQuery(document).ready(function(){

	var CustomSelectEditor = Handsontable.editors.BaseEditor.prototype.extend();
	CustomSelectEditor.prototype.init = function(){
	   // Create detached node, add CSS class and make sure its not visible
	   this.select = jQuery('<select multiple="1" ></select>')
		 .addClass('htCustomSelectEditor')
		 .hide();
		 
	   // Attach node to DOM, by appending it to the container holding the table
	   jQuery(this.instance.rootElement).append(this.select);
	};
	
	// Create options in prepare() method
	CustomSelectEditor.prototype.prepare = function(){
       
		//Remember to invoke parent's method
		Handsontable.editors.BaseEditor.prototype.prepare.apply(this, arguments);
		
		var options = this.cellProperties.selectOptions || [];

		var optionElements = options.map(function(option){
			var optionElement = jQuery('<option />');
			if(typeof option === typeof {}){
			  optionElement.val(option.value);
			  optionElement.html(option.name);
			}else{
			  optionElement.val(option);
			  optionElement.html(option);
			}

			return optionElement
		});

		this.select.empty();
		this.select.append(optionElements);
		
		
		var widg = this.select.next();
		var self = this;
		
		var create = false;
		
		var multiple = this.cellProperties.select_multiple;
		if(typeof multiple === "function"){
			multiple = !!multiple(this.instance,this.row, this.prop);
		}else if(!multiple)
			multiple = false;
		
		var create_option = this.cellProperties.allow_random_input;
		if(typeof create_option === "function"){
			create_option = !!create_option(this.instance,this.row, this.prop);
		}else if(!create_option)
			create_option = false;
		
		if(widg.is('.chosen-container')){
			if(
				!!this.select.data('chosen').is_multiple != multiple
				||
			    !!this.select.data('chosen').create_option != create_option
			   ){
					this.select.chosen('destroy');	
					create = true;
				}
		}else
			create = true;
		
		if(create){
			if(!multiple){
			   this.select.removeAttr('multiple');
			   this.select.change(function(){
					self.finishEditing()
					jQuery('#dg').handsontable("selectCell", self.row , self.col);					
			   });
			}else if(!this.select.attr("multiple")){
				this.select.attr('multiple','multiple');
			}
			var chos;
			if(create_option)
				chos = this.select.chosen({
					create_option: true,
					create_option_text: 'value',
					persistent_create_option: true,
					skip_no_results: true
				}).data('chosen');
			else
				chos = this.select.chosen().data('chosen');

			chos.container.bind('keyup', function (event) {
			   if(event.keyCode == 27){
				    self.cancelUpdate = true;
					self.discardEditor();
					self.finishEditing();
					
			   }else if(event.keyCode == 13){
				  var src_inp = jQuery(this).find('LI.search-field > INPUT[type="text"]:first');
				  if(src_inp[0])
					if(src_inp.val() == ''){
					   //event.stopImmediatePropagation();
					   //event.preventDefault();
					   self.discardEditor();
					   self.finishEditing();
					   //self.focus();
					   //self.close();
					   jQuery('#dg').handsontable("selectCell", self.row + 1, self.col);
					}
			   }
			});
		}
	};
	
	CustomSelectEditor.prototype.getValue = function () {
	   if(this.select.val()){
		   var value = this.select.val();
		   if(!(value instanceof Array)){
			  value = value.split(",")
		   }
		   
		   for(var i = 0; i < value.length; i++){
			  value[i] = jQuery.isNumeric(value[i]) ? parseFloat(value[i]) : value[i];
			  if(value[i]){
				  if(!this.cellProperties.dictionary[value[i]]){
					this.cellProperties.dictionary[value[i]] = value[i];
					this.cellProperties.selectOptions.push({ name: value[i], value: value[i] }); 
				  }
			  }
		   }
		   if(!this.cellProperties.select_multiple){
			if(value)
			  if(jQuery.isArray(value))
				return value[0];
              else
				return value;  
            else
			  return null;		
		   }else
			return value;
	   }else
		  return [];
	};
	
	CustomSelectEditor.prototype.setValue = function (value) {
	   if(!(value instanceof Array))
		value = value.split(',');
	   this.select.val(value);
	   this.select.trigger("chosen:updated");
	};
	
	CustomSelectEditor.prototype.open = function () {
		//sets <select> dimensions to match cell size
		
		this.cancelUpdate = false;
		
		var widg = this.select.next();
		widg.css({
		   height: jQuery(this.TD).height(),
		   'min-width' : jQuery(this.TD).outerWidth() > 250 ? jQuery(this.TD).outerWidth() : 250
		});
		
		widg.find('LI.search-field > INPUT').css({
		   'min-width' : jQuery(this.TD).outerWidth() > 250 ? jQuery(this.TD).outerWidth() : 250
		});

		//display the list
		widg.show();

		//make sure that list positions matches cell position
		widg.offset(jQuery(this.TD).offset());
	};
	
	CustomSelectEditor.prototype.focus = function () {
	     this.instance.listen();
    };

	CustomSelectEditor.prototype.close = function () {
		 if(!this.cancelUpdate)
			this.instance.setDataAtCell(this.row,this.col,this.select.val(),'edit')
		 
		 this.select.next().hide();
	};
	
	var clonableARROW = document.createElement('DIV');
	clonableARROW.className = 'htAutocompleteArrow';
	clonableARROW.appendChild(document.createTextNode('\u25BC'));
	
	var clonableEDIT = document.createElement('DIV');
	clonableEDIT.className = 'htAutocompleteArrow';
	clonableEDIT.appendChild(document.createTextNode('\u270E'));
	
	var clonableIMAGE = document.createElement('DIV');
	clonableIMAGE.className = 'htAutocompleteArrow';
	clonableIMAGE.appendChild(document.createTextNode('\u27A8'));
		
	var CustomSelectRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	    try{
		  
		   // var WRAPPER = clonableWRAPPER.cloneNode(true); //this is faster than createElement
			var ARROW = clonableARROW.cloneNode(true); //this is faster than createElement

			Handsontable.renderers.TextRenderer(instance, td, row, col, prop, value, cellProperties);
			
			var fc = td.firstChild;
			while(fc) {
				td.removeChild( fc );
				fc = td.firstChild;
			}
			
			td.appendChild(ARROW); 
			
			if(value || (value === 0)){
				
				if(cellProperties.select_multiple){ 
					var rval = value;
					if(!(rval instanceof Array))
						rval = rval.split(',');
					
					td.appendChild(document.createTextNode(rval.map(function(s){ 
							if(cellProperties.dictionary[s])
								return cellProperties.dictionary[s];
							else
								return s;
						}).join(', ')
					));
				}else{
					td.appendChild(document.createTextNode(cellProperties.dictionary[value] || value));
				}
				
			}else{
				//jQuery(td).html('');
			}
			
			Handsontable.Dom.addClass(td, 'htAutocomplete');

			if (!td.firstChild) {
			  td.appendChild(document.createTextNode('\u00A0')); //\u00A0 equals &nbsp; for a text node
			}

			if (!instance.acArrowListener) {
			  instance.acArrowHookedToDouble = true;	
			  var eventManager = Handsontable.eventManager(instance);

			  //not very elegant but easy and fast
			  instance.acArrowListener = function (event) {
				if (Handsontable.Dom.hasClass(event.target,'htAutocompleteArrow')) {
				  instance.view.wt.getSetting('onCellDblClick', null, new WalkontableCellCoords(row, col), td);
				}
			  };

			  jQuery(instance.rootElement).on("mousedown.htAutocompleteArrow",".htAutocompleteArrow",instance.acArrowListener);

			  //We need to unbind the listener after the table has been destroyed
			  instance.addHookOnce('afterDestroy', function () {
				eventManager.clear();
			  });

			}else if(!instance.acArrowHookedToDouble){
			  instance.acArrowHookedToDouble = true;	
			  var eventManager = Handsontable.eventManager(instance);	
			  jQuery(instance.rootElement).on("mousedown.htAutocompleteArrow",".htAutocompleteArrow",instance.acArrowListener);
			  //We need to unbind the listener after the table has been destroyed
			  instance.addHookOnce('afterDestroy', function () {
				eventManager.clear();
			  });	
				
			}
		}catch(e){
			jQuery(td).html('');
		}
	};
	/////////////////////////////////////////////////////////////////////////////////////////////
	var CustomFieldWithPriceEditor = Handsontable.editors.TextEditor.prototype.extend();
	var cleanValueName = function(val){
	   if(!val)
		return '';
       else{
	    var ret = new Array();
		
		val.split(cf_val_separator).map(function(v){
				if(v.replace(" ","") != ""){
					v = v.split(':');
					if(v.length < 2){
					   ret.push( v[0] + ":0");
					}else{
					   var price = v[1];
					   if(!isNaN(parseFloat(price)) && isFinite(price)){
						  ret.push(v[0] + ":" + price); 
					   }else
						  ret.push(v[0] + ":0");	
					}
				}
				return true;
			});
			
		return ret.join(cf_val_separator);
		
	   } 	   
	};
	
	CustomFieldWithPriceEditor.prototype.getValue = function () {
	   return cleanValueName(this.TEXTAREA.value)  || '';
	};

	CustomFieldWithPriceEditor.prototype.setValue = function (value) {
	   this.TEXTAREA.value = cleanValueName(value);
	};
	///////////////////////////////////////////////////////////////////////////////////////////
	


	var clonableARROW = document.createElement('DIV');
	clonableARROW.className = 'htAutocompleteArrow';
	clonableARROW.appendChild(document.createTextNode('\u25BC'));
		
	var centerCheckboxRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.CheckboxRenderer.apply(this, arguments);
	  $(td).css({
		'text-align': 'center',
		'vertical-align': 'middle'
	  });
	};
	
	var HTMLRenderer = function (instance, td, row, col, prop, value, cellProperties) {
		if(!value)
			value ="";
		else{ 
		    td.innerHTML = value;
		    value = td.innerText;
		    if(value.length > 220)
				value = value.substr(0,220);
		}
		Handsontable.renderers.HtmlRenderer.apply(this, arguments);
	};

	
	var centerTextRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.TextRenderer.apply(this, arguments);
	  $(td).css({
		'text-align': 'center',
		'vertical-align': 'middle'
	  });
	};
	
	var linkRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	   Handsontable.renderers.HtmlRenderer.apply(this, arguments);

		   td.innerHTML  = "";
		   var a = document.createElement("a");
		   a.class  = "view-product";
		   a.target = "_blank";
		   a.href   = decodeURIComponent(value);
		   a.innerHTML = "&gt;&gt;"; 
		   td.appendChild(a);
	   
	};
	
	var ParentFieldRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	   Handsontable.renderers.HtmlRenderer.apply(this, arguments);
		   build_id_index_directory();
		   
		   var fc = td.firstChild;
		   while(fc) {
				td.removeChild( fc );
				fc = td.firstChild;
		   }
			
		   td.style.color          = "#888888";
		   td.style.background     = "#FBFBFB"; 
		   td.style.padding        = 0;
		   td.style.borderTopColor = 'transparent';
		   td.style.borderBottomColor = 'transparent';
		   
		   var a = document.createElement("a");
		   a.style.fontStyle = "normal";
		   a.style.fontSize = "16px";
		   a.className  = "add-children";
		   a.href       = "?v="  + instance.getDataAtRowProp(row,'virtuemart_product_id');;
		   a.rel        = instance.getDataAtRowProp(row,'virtuemart_product_id');
		   a.innerHTML  = "+"; 
		   var id = DG.getDataAtCell(row,0);
		   
		   var tn_class = "";
		   if(!value){
			   var id = DG.getDataAtCell(row,0);
			   if(id_index[id].ch.length > 0){
				tn_class = "plevel0 has-ch";
			   }else{
				tn_class = "plevel0 no-ch";
			   }
		   }else{
			   
			   var par_id = value;
			   var level = 0;
			   do{
				   level++;
				   try{
					if(id_index[par_id]){   
					  if(id_index[par_id].ind > -1)
						par_id = DG.getSourceDataAtRow(id_index[par_id].ind)["parent"]; 
					  else
						break;  
				    }else 
						break;
				   }catch(e){	  
				      break;
				   }
			   }while(par_id);
			   
			   if(id_index[id].ch.length > 0){
				tn_class = "plevel" + level + " has-ch";
			   }else{
				tn_class = "plevel" + level + " no-ch";
			   }
		   }
		   
		   if(id){
			   td.appendChild(a);
			   var span = document.createElement("span");
			   span.className = "p-tree-node " + tn_class;
			   td.appendChild(span);
		   } 
	};
	
	
	var sideContentEditor = Handsontable.editors.BaseEditor.prototype.extend();
	sideContentEditor.prototype.open = function () {
		
		ContentEditorCurrentlyEditing.row   = this.row; 
		ContentEditorCurrentlyEditing.col   = this.col; 
		ContentEditorCurrentlyEditing.prop  = this.prop; 
		ContentEditorCurrentlyEditing.value = DG.getDataAtRowProp(this.row,"virtuemart_product_id"); 
		
		if(this.cellProperties.getFN)
			this.cellProperties.getFN(this);
		else	
			editContent( ContentEditorCurrentlyEditing.value , "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CONTENT");?>: " +  DG.getDataAtRowProp(this.row,'product_sku') + ", " + DG.getDataAtRowProp(this.row,'product_name'), "&P_CONTENT=get&virtuemart_product_id=" + ContentEditorCurrentlyEditing.value ,
		     function(callback){
			   saveSideContent("&P_CONTENT=set&virtuemart_product_id=" + jQuery('#content_edit_ifr').attr('virtuemart_product_id'), callback);	 
			 }
			);
		
		setTimeout(function(){
			DG.selectCell(ContentEditorCurrentlyEditing.row,ContentEditorCurrentlyEditing.col,ContentEditorCurrentlyEditing.row,ContentEditorCurrentlyEditing.col,false,false);	
		},200);
	};
	
	sideContentEditor.prototype.getValue = function () {
	   return DG.getDataAtRowProp(this.row,"virtuemart_product_id"); 
	};

	sideContentEditor.prototype.setValue = function (value) {
		this.finishEditing();
	};
	
	sideContentEditor.prototype.focus = function () { this.instance.listen();};
	sideContentEditor.prototype.close = function () {};
	
	
	var sideImageEditor = Handsontable.editors.BaseEditor.prototype.extend();
	sideImageEditor.prototype.open = function () {
		ContentEditorCurrentlyEditing.row   = this.row; 
		ContentEditorCurrentlyEditing.col   = this.col; 
		ContentEditorCurrentlyEditing.prop  = this.prop; 
		ContentEditorCurrentlyEditing.value = DG.getDataAtRowProp(this.row,"virtuemart_product_id"); 
		
		editImages(  ContentEditorCurrentlyEditing.value , DG.getDataAtRowProp(this.row,'product_sku') + ", " + DG.getDataAtRowProp(this.row,'product_name'));
		setTimeout(function(){
			
			DG.selectCell(ContentEditorCurrentlyEditing.row,ContentEditorCurrentlyEditing.col,ContentEditorCurrentlyEditing.row,ContentEditorCurrentlyEditing.col,false,false);	
		},200);
	};
	
	sideImageEditor.prototype.getValue = function () {
	   return DG.getDataAtRowProp(this.row,"virtuemart_product_id"); 
	};

	sideImageEditor.prototype.setValue = function (value) {
		this.finishEditing();
	};
	
	sideImageEditor.prototype.focus = function () { this.instance.listen();};
	sideImageEditor.prototype.close = function () {};
	
	var sideEditFiledRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.HtmlRenderer.apply(this, arguments);
		   td.style.fontStyle = "italic";
		   td.style.color     = "#9b4f96"; 
		   try{
			arguments[5] = "Edit..."; 
			
			Handsontable.renderers.TextRenderer.apply(this, arguments);
			Handsontable.Dom.addClass(td, 'htContent');
			td.insertBefore(clonableEDIT.cloneNode(true), td.firstChild);
			if (!td.firstChild) { //http://jsperf.com/empty-node-if-needed
			  td.appendChild(document.createTextNode('\u00A0')); //\u00A0 equals &nbsp; for a text node
			}

			if (!instance.acArrowListener) {
			  instance.acArrowHookedToDouble = true;	
			  var eventManager = Handsontable.eventManager(instance);

			  //not very elegant but easy and fast
			  instance.acArrowListener = function (event) {
				if (Handsontable.Dom.hasClass(event.target,'htAutocompleteArrow')) {
				  instance.view.wt.getSetting('onCellDblClick', null, new WalkontableCellCoords(row, col), td);
				}
			  };

			  jQuery(instance.rootElement).on("mousedown.htAutocompleteArrow",".htAutocompleteArrow",instance.acArrowListener);

			  //We need to unbind the listener after the table has been destroyed
			  instance.addHookOnce('afterDestroy', function () {
				eventManager.clear();
			  });

			}else if(!instance.acArrowHookedToDouble){
			  instance.acArrowHookedToDouble = true;	
			  var eventManager = Handsontable.eventManager(instance);	
			  jQuery(instance.rootElement).on("mousedown.htAutocompleteArrow",".htAutocompleteArrow",instance.acArrowListener);
			  
			  //We need to unbind the listener after the table has been destroyed
			  instance.addHookOnce('afterDestroy', function () {
				eventManager.clear();
			  });	
				
			}
		}catch(e){
			jQuery(td).html('');
		}
	  
	};
	
	var imagesEditorInvoker = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.HtmlRenderer.apply(this, arguments);	

		   td.innerHTML  = "";
		   
		   td.is_i_id = value;
		   
		   var a = document.createElement("span");
		   a.className  = "edit-images";
		   a.title    = instance.getDataAtRowProp(row,'product_sku') + ", " + instance.getDataAtRowProp(row,'product_name');
		   a.innerHTML = "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_EDIT_IMAGES");?>"; 
		   td.appendChild(a);
	   
	};
	
	var TextRenderer = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.TextRenderer.apply(this, arguments);
	  td.style.textAlign = 'left';
	  td.style.verticalAlign = 'center';
	};
	
	var weight_units  = [
	  { "name":"Kilogramme", "value":"KG" }
	 ,{ "name":"Gramme", "value":"G" }
	 ,{ "name":"Milligramme", "value":"MG" }
	 ,{ "name":"Pounds", "value":"LB" }
	 ,{ "name":"Ounce", "value":"OZ" }
	];
	
	var length_units  = [
	  { "name":"Metres", "value":"M" }
	 ,{ "name":"Centimetres", "value":"CM" }
	 ,{ "name":"Millimetres", "value":"MM" }
	 ,{ "name":"Yards", "value":"YD" }
	 ,{ "name":"Foot", "value":"FT" }
	 ,{ "name":"Inches", "value":"IN" }
	];
	
	var product_units = [
 	  { "name":"kg", "value":"KG" }
	 ,{ "name":"100 g", "value":"100G" }
	 ,{ "name":"m", "value":"M" }
	 ,{ "name":"m²", "value":"SM" }
	 ,{ "name":"m³", "value":"CUBM" }
	 ,{ "name":"l", "value":"L" }
	 ,{ "name":"100 ml", "value":"100ML" }
	];
	
	

	/*
	var VariationEditorInvoker = function (instance, td, row, col, prop, value, cellProperties) {
	  Handsontable.renderers.HtmlRenderer.apply(this, arguments);	
		   td.innerHTML  = "";
		   td.className += " add-var-cell";
		  
	};
	*/
	var cw = [40,80,80,160,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80];
	/*
	if(localStorage['dg_manualColumnWidths']){
		var LS_W = eval(localStorage['dg_manualColumnWidths']);
		for(var i = 0; i< LS_W.length; i++){
			if(LS_W[i])
				cw[i] = LS_W[i] || 80;
		}
	}
	*/


	sortedBy  = null;
	sortedOrd = null;
	
	if(!localStorage["dg_columnSorting"]){
		localStorage["dg_columnSorting"] = '{"sortColumn":0,"sortOrder":true}';
	}
	
	
	grid_headers = [
		"ID"
		<?php if(!$SETTINGS->par_ch_stick_disable){ ?>
		,"Parent"
		<?php } ?>
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SKU");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_NAME");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_AVAILABLE");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_FEATURED");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_MANUFACTURER");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_CATEGORY");?>"
		
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_STOCK");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_BACKORDERS");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_PRICE");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_OVERRIDE");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SALES");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_TAX");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_DISCOUNT");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_CURRENCY");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SLUG");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SDESC");?>"
		<?php
		foreach($custom_fields as $cf_id => $cf)
		    echo "\n" . ',"'.pelm_sprintf($cf['custom_title']).'"';
		?>
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_WEIGHT");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_WEIGHT_UOM");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_LENGTH");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_WIDTH");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_HEIGHT");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_LWH_UOM");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_PACKAGING");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_UNIT");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_META_ROBOT");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_META_AUTHOR");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_META_DESCR");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_META_KEYWORDS");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_CUSTOM_TITLE");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_URL");?>"
		<?php if($has_gtinmpn){ ?>
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_EAN");?>"
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_MPN");?>"
		<?php } ?>
		<?php
		foreach(get_pelm_plugins_toinc("grid_header") as $plg)
			include($plg);	
		?>
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMAGES");?>..."
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CONTENT");?>..."
		,"<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_VEIWPRODUCT");?>"
	];
	
	grid_columns = [
	   { data: "virtuemart_product_id", readOnly: true , type: 'numeric' }
	   <?php if(!$SETTINGS->par_ch_stick_disable){ ?>
	  ,{ data: "parent", type: 'numeric', renderer: ParentFieldRenderer }
	   <?php } ?>
	  ,{ data: "product_sku"}
	  ,{ data: "product_name"  }
	  ,{ data: "published", type: "checkbox", renderer: centerCheckboxRenderer  }
	  ,{ data: "product_special", type: "checkbox", renderer: centerCheckboxRenderer  }
	  ,{
	      data: "virtuemart_manufacturer_id",
		  editor: CustomSelectEditor.prototype.extend(),
		  renderer: CustomSelectRenderer,
		  dictionary: asoc_mans,
		  select_multiple: false,
		  selectOptions: manufacturers.map(function(source){
						   return {
							 "name": source.mf_name , 
							 "value": source.virtuemart_manufacturer_id
						   }
						})
	   }
	  ,{
	    data: "categories",
	    editor: CustomSelectEditor.prototype.extend(),
		renderer: CustomSelectRenderer,
		dictionary: asoc_cats,
		select_multiple: true,
        selectOptions: categories.map(function(source){
						   return {
							 "name": source.category_path , 
							 "value": source.virtuemart_category_id
						   }
						})
	   }
	  
	  ,{ data: "product_in_stock" ,type: 'numeric',format: '0'}
	  ,{ data: "product_ordered" ,type: 'numeric',format: '0'}
	  ,{ data: "product_price"  ,type: 'numeric',format: '0<?php echo substr($_num_sample,1,1);?>00'}
	  ,{ data: "product_override_price"  ,type: 'numeric',format: '0<?php echo substr($_num_sample,1,1);?>00'}
	  ,{ data: "product_sales_price"  ,type: 'numeric',format: '0<?php echo substr($_num_sample,1,1);?>00'}
	  ,{
	    data: "product_tax_id",
	    editor: CustomSelectEditor.prototype.extend(),
		renderer: CustomSelectRenderer,
		dictionary: taxs_asoc,
		select_multiple: false,
        selectOptions: dictionaryToNamevalue(taxs_asoc)
	   }
	  ,{
	    data: "product_discount_id",
	    editor: CustomSelectEditor.prototype.extend(),
		renderer: CustomSelectRenderer,
		dictionary: disc_asoc,
		select_multiple: false,
        selectOptions: dictionaryToNamevalue(disc_asoc)
	   }
	  ,{
	    data: "product_currency",
	    editor: CustomSelectEditor.prototype.extend(),
		renderer: CustomSelectRenderer,
		dictionary: curr_asoc,
		select_multiple: false,
        selectOptions: dictionaryToNamevalue(curr_asoc)
	   } 
	  ,{ data: "slug", type: 'text'  }
	  ,{ data: "product_s_desc" , type: 'text' }
	  <?php
	  foreach($custom_fields as $cf_id => $cf){
		 if($cf['is_cart_attribute'] )
			echo "\n" . ',{ data: "' . "custom_field_" . $cf_id . '" , editor: CustomFieldWithPriceEditor.prototype.extend() }';
		 else
			echo "\n" . ',{ data: "' . "custom_field_" . $cf_id . '" , type: "text" }';
	  }
	  ?>
	  
		,{ data: "product_weight"  ,type: 'numeric',format: '0<?php echo substr($_num_sample,1,1);?>00'}
		,{  
		    data: "product_weight_uom",  
		    editor: CustomSelectEditor.prototype.extend(),
			renderer: CustomSelectRenderer,
			dictionary: namevalueToDictionary(weight_units),
			select_multiple: false,
			selectOptions: weight_units
		 }
		,{ data: "product_length"  ,type: 'numeric', format: '0<?php echo substr($_num_sample,1,1);?>00'}
		,{ data: "product_width"   ,type: 'numeric', format: '0<?php echo substr($_num_sample,1,1);?>00'}
		,{ data: "product_height"  ,type: 'numeric', format: '0<?php echo substr($_num_sample,1,1);?>00'}
		,{ 
		    data: "product_lwh_uom",  
		    editor: CustomSelectEditor.prototype.extend(),
			renderer: CustomSelectRenderer,
			dictionary: namevalueToDictionary(length_units),
			select_multiple: false,
			selectOptions: length_units
		}
		,{ data: "product_packaging"  ,type: 'numeric',format: '0<?php echo substr($_num_sample,1,1);?>00'}
		,{ 
		    data: "product_unit" ,  
		    editor: CustomSelectEditor.prototype.extend(),
			renderer: CustomSelectRenderer,
			dictionary: namevalueToDictionary(product_units),
			select_multiple: false,
			selectOptions: product_units
		 }
		 ,{ data: "metarobot"}
		 ,{ data: "metaauthor"}
		 
		 ,{ data: "metadesc"}
		 ,{ data: "metakey"}
		 ,{ data: "customtitle"}
	
		 ,{ data: "product_url"}
		 <?php if($has_gtinmpn){ ?>
		 ,{ data: "product_gtin"}
		 ,{ data: "product_mpn"}
		 <?php } ?>
		 <?php
		  foreach(get_pelm_plugins_toinc("grid_columns") as $plg)
			include($plg);
		 ?>
		 ,{ data: "i_id", renderer:  sideEditFiledRenderer, editor: sideImageEditor.prototype.extend()}
		 ,{ data: "c_id", renderer:  sideEditFiledRenderer, editor: sideContentEditor.prototype.extend()}
		 ,{ data: "link", readOnly: true,  renderer:  linkRenderer }
	  ];

	  grid_columns_asoc = {};
	  for(var i = 0; i< grid_columns.length; i++)
		  grid_columns_asoc[grid_columns[i]["data"]] = grid_columns[i];
	  
	  var hiddenc = NormalizeChosenVal(<?php echo json_encode($SETTINGS->hidden_columns); ?>);
	  
	  if(hiddenc){
		  if( hiddenc.length > 0){
			for(var i = 0; i < grid_columns.length; i++){
				if(jQuery.inArray(grid_columns[i].data, hiddenc) < 0){
					visible_grid_headers.push( grid_headers[i]);
					visible_grid_columns.push( grid_columns[i]);	
				}
			}
		  }else{
			  visible_grid_headers = grid_headers;
			  visible_grid_columns = grid_columns;
		  }
	  }else{
		  visible_grid_headers = grid_headers;
		  visible_grid_columns = grid_columns;
	  }
	  
	$('#dg').handsontable({
	  data: [<?php 
	          

		  
			  if(count($records)){
				$n = 0;  
				foreach($records as  $pr_id){
					try{
						$prod       = vmel_getProduct($pr_id,$productModel,$db,$custom_fields,$cat_asoc,$man_asoc);  
						if($prod === NULL){
							continue;
						}
						echo (($n > 0 ? "," : "") .json_encode($prod));	

                        unset($prod);						
					}catch(Exception $e){
						$plem_errors .= "Product id:" . $pr_id . " broken data!" . $e->getMessage() . "\n";
					}
					$n++;
				}
			  }
			  
			 
			  
			 
	  ?>],
	  minSpareRows: <?php echo $SETTINGS->allow_add ? "1" : "0"; ?>,
	  
	  colHeaders: true,
	  rowHeaders: true,
	  contextMenu: false,
	  manualColumnResize: true,
	  manualColumnMove: true,
	  columnSorting: eval("(" + localStorage["dg_columnSorting"]  + ")"),
	  persistentState: true,
	  variableRowHeights: false,
	  fillHandle: 'vertical',
	  currentRowClassName: 'currentRow',
      currentColClassName: 'currentCol',
	  fixedColumnsLeft: <?php echo $SETTINGS->frozen_columns; ?>,
	  colWidths:function(cindex){
		  var prop = DG.colToProp(cindex);
		  if(page_col_w[prop]){
			  return page_col_w[prop];
		  }else
			return cw[cindex];
	  },
	  search:true,
	  outsideClickDeselects: false,
	  <?php if( $SETTINGS->allow_delete){ ?>
	  removeRowPlugin: true,
	  <?php } ?>
	  beforeRemoveRow: function (index, amount){
		 if(<?php echo $SETTINGS->allow_delete ? "true" : "false" ?>){
			 if(multidel)
				 return true;

			 if(!DG.getDataAtRowProp(index,"virtuemart_product_id"))
				 return false;
			 
			 if(confirm("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_REMOVE_PRODUCT");?> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_SKU");?>:" + DG.getDataAtRowProp(index,"product_sku") + ", <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_NAME");?>: '" + DG.getDataAtRowProp(index,"product_name") + "', ID:" +  DG.getDataAtRowProp(index,"virtuemart_product_id") + "?")){
				
				var virtuemart_product_id = DG.getDataAtRowProp(index,"virtuemart_product_id");
				
				if(!tasks[virtuemart_product_id])
					tasks[virtuemart_product_id] = {};
				
				tasks[virtuemart_product_id]["DO_DELETE"] = 'delete';
				id_index = null;
				callSave();
				
				return true;		 
			 }else{
				
				return false;
			 }
		 }else
			return false;
	  },
	  width: function () {
		if (availableWidth === void 0) {
		  calculateSize();
		}
		return availableWidth ;
	  },
	  height: function () {
		if (availableHeight === void 0) {
		  calculateSize();
		}
		return availableHeight;
	  },
	  colHeaders: visible_grid_headers,
	  columns: visible_grid_columns,
	  
	  //,outsideClickDeselects: false
	  //,removeRowPlugin: true
	 
	  afterChange: function (change, source) {
			if(!change)   
				return;

			if (source === 'loadData' || source === 'skip' || source === 'external') return;
			
			if(!change[0])
				return;
			
			if(!jQuery.isArray(change[0]))
				change = [change];
			
			if(!DG)
				DG = $('#dg').data('handsontable');
			
			change.map(function(data){
				if(!data)
					return;
				
				try{
					if(DG.getSettings().columns[DG.propToCol(data[1])].extern_edit === true)
						return;
				}catch(exte_e){
					//
				}
				
				if(data[1] == "c_id" || data[1] == "i_id")
					return;
			
				if (([data[2]].join("") == [data[3]].join("")) && source !== 'force')
					return;
				
				var virtuemart_product_id = DG.getDataAtRowProp (data[0],'virtuemart_product_id');	
				
				
				if(!virtuemart_product_id){
					 if(!data[3])
						return;
					
					 var surogat = "s" + parseInt( Math.random() * 10000000); 
					 DG.getSourceDataAtRow(data[0])['virtuemart_product_id'] = surogat;
					 virtuemart_product_id = surogat;
					 SUROGATES[surogat] = data[0];
				}
				
				var prop = data[1];
				var val  = data[3];
				if(!tasks[virtuemart_product_id])
					tasks[virtuemart_product_id] = {};
				tasks[virtuemart_product_id][prop] = val;
				tasks[virtuemart_product_id]["dg_index"] = data[0];
				if(prop == "parent")
					id_index = null;
				
			});
			callSave();
		}
		,afterColumnResize: function(currentCol, newSize){
			page_col_w = {};
			for(var i = 0 ; i < DG.countCols(); i++){
				page_col_w[DG.getCellMeta(0,i).prop] = DG.getColWidth(i);
			}
	    }
		,afterColumnMove:function(oldIndex, newIndex){
			if(page_col_w){
				var c_w = [];
				for(var i  = 0; i< DG.countCols() ; i++){
					var prop = DG.getCellMeta(0,i).prop;
					if(page_col_w[prop]){
						c_w.push(page_col_w[prop]);
					}else{
						c_w.push(80);
					}
				}
			}
			
			DG.c_resize_monior_disable =  true;
			DG.updateSettings({colWidths: c_w});
			DG.c_resize_monior_disable = false;
			
		}
		,beforeColumnSort: function (column, order){
		  if(explicitSort)
			  return;
		  if(DG){
			if(DG.getSelected()){
				DG.sortColumn = DG.getSelected()[1];
				
				if(DG.colToProp(DG.sortColumn) == "c_id" || DG.colToProp(DG.sortColumn) == "i_id" || DG.getSettings().columns[DG.sortColumn].sortable === false){
					DG.sortOrder  = sortedOrd;
				    DG.sortColumn = sortedBy;
				}else{
					if(sortedBy == DG.sortColumn)
						DG.sortOrder = !sortedOrd;
					else
						DG.sortOrder = true;
					
					sortedBy  = DG.sortColumn;
					sortedOrd = DG.sortOrder;
				}
				
			}
		  }
	    },
		cells: function (row, col, prop) {
			if(!DG)
				DG = jQuery('#dg').data('handsontable');
				
			if(!DG)
				return;
			
			this.readOnly = false;
			
			if(prop == "virtuemart_product_id" || prop == "parent"  )
				this.readOnly = true;
			
			if(grid_columns_asoc[prop]){
				if(grid_columns_asoc[prop].readOnly)
					this.readOnly = true;
			}
			
		}
		,afterOnCellMouseDown: function(event, coords, TD){
			if(TD.is_c_id || TD.is_i_id){
					jQuery(TD).find("span").trigger("click");
			}
		}
		<?php if($SETTINGS->prices) { ?>
		,afterSelection:function(r, c, r_end, c_end){
			var prid = DG.getDataAtRowProp(r,'virtuemart_product_id');
			if(prid){
				if(String(prid).indexOf('s') == -1){	
					var pinf = DG.getDataAtRowProp(r,'product_sku');
					if(pinf)
						pinf += ", ";
					else
						pinf = "";
					pinf += DG.getDataAtRowProp(r,'product_name');			
					loadCustomPrices( prid, DG.getCellMeta(r,c).row, DG.getDataAtRowProp(r,'prices'), pinf); 
				}
			}else
				jQuery('#custom_prices').hide();
			
		}
		<?php } ?>
	});
	
	if(!DG)
		DG = $('#dg').data('handsontable');
		
	if(!DG.sortColumn){
		DG.updateSettings({ sortColumn: 0 });
	}
	
	sortedBy  = DG.sortColumn;
	sortedOrd = DG.sortOrder;
	
	setKeepAlive();
	
	jQuery('.right_panel_label').click(function(){
		if( jQuery(this).parent().is('.opened')){
			jQuery(this).parent().removeClass('opened').addClass('closed');
		}else{
			jQuery(this).parent().removeClass('closed').addClass('opened');
		}
		jQuery(window).trigger('resize');
	});
	
	jQuery(window).load(function(){
		jQuery(window).trigger('resize');
	});
	
	if('<?php echo $product_manufacturer?>') jQuery('.filter_option *[name="product_manufacturer"]').val("<?php echo $product_manufacturer;?>");
	if('<?php echo $product_category;?>') jQuery('.filter_option *[name="product_category"]').val("<?php echo $product_category;?>".split(','));
	jQuery('.filter_option *[name="product_show"]').val(<?php echo $product_show;?>);
	jQuery("SELECT:not([data-placeholder])").attr("data-placeholder","<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SELECTOPTIONS") ?>");
	jQuery('SELECT[name="product_category"]').chosen();
	
	jQuery("<div class='grid-bottom-spacer' style='min-height:120px;'></div>").insertAfter( jQuery("table.htCore"));
	
	function screenSearch(select){
		if(DG){
			var self = document.getElementById('activeFind');
			var queryResult = DG.search.query(self.value);
			if(select){
				if(!queryResult.length){
					jumpIndex = 0;
					return;
				}
				if(jumpIndex > queryResult.length - 1)
					jumpIndex = 0;
				DG.selectCell(queryResult[jumpIndex].row,queryResult[jumpIndex].col,queryResult[jumpIndex].row,queryResult[jumpIndex].col,true);
				jQuery("#search_matches").html(("" + (jumpIndex + 1) + "/" + queryResult.length) || "");
				jumpIndex ++;
			}else{
				jQuery("#search_matches").html(queryResult.length || "");
				DG.render();
				jumpIndex = 0;
			}
		}
	}
	
	Handsontable.Dom.addEvent(document.getElementById('activeFind') , 'keyup', function (event) {
		if(event.keyCode == 13){
			screenSearch(true);
		}else{
			screenSearch(false);
		}
	});
	
	jQuery("#cmdActiveFind").click(function(){
		screenSearch(true);
	});
});



  <?php
    if($mu_res){
	   $upd_val = $_REQUEST["mass_update_val"].(  $_REQUEST["mass_update_percentage"] ? "%" : "" );
	   ?>
	   jQuery(window).load(function(){
	   alert('<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MASS_UPDATE_RESULT",$upd_val); ?>');
	   });
	   <?php
	}
	
	if($import_count){
	   ?>
	   jQuery(window).load(function(){
	   alert('<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_RESULT",$import_count); ?>');
	   });
	   <?php
	}
	
  ?>


function do_export(){
    var link = window.location.href + "&do_export=1" ;
   
    var QUERY_DATA = {};
	QUERY_DATA.sortOrder            = $('#dg').data('handsontable').sortOrder ? "ASC" : "DESC";
	QUERY_DATA.sortColumn           = getSortProperty();
	
	QUERY_DATA.limit                = "9999999999";
	QUERY_DATA.page                 = "1";
	QUERY_DATA.vmlang        = $('#edit_language').val();
	
	jQuery(".filter_holder > DIV.filter_option *[name]").each(function(i){
		QUERY_DATA[jQuery(this).attr("name")] = jQuery(this).val();
	});
	
	for(var key in QUERY_DATA){
		if(QUERY_DATA[key])
			link += ("&" + key + "=" + QUERY_DATA[key]);
	}
	
	//window.location =  link;
	window.open(link, '_blank');
    return false;
}

function do_import(){
    var import_panel = jQuery("<div class='import_form'><form method='POST' enctype='multipart/form-data'><span><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_MESSAGE"); ?></span><br/><label for='file'><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILENAME"); ?></label><input type='file' name='file' id='file' /><br/><br/><button class='cmdImport' ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_SUBMIT"); ?></button><button class='cancelImport'><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_CANCEL"); ?></button></form><p style='color:cyan;font-size:13px;'><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_NOTICE"); ?></p></div>"); 
    import_panel.appendTo(jQuery("BODY"));
	
	import_panel.find('.cancelImport').click(function(){
		import_panel.remove();
		return false;
	});
	
	import_panel.find('.cmdImport').click(function(){
		if(!jQuery("#file").val()){
		  alert('<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_NO_VALUE");?>');
		  return false;
		}
	    var frm = import_panel.find('FORM');
		var POST_DATA = {};
		
		POST_DATA.do_import            = "1";
		POST_DATA.sortOrder            = $('#dg').data('handsontable').sortOrder ? "ASC" : "DESC";
		POST_DATA.sortColumn           = getSortProperty();
		POST_DATA.limit                = $('#txtlimit').val();
		POST_DATA.page                 = $('#paging_page').val();
		POST_DATA.vmlang               = $('#edit_language').val();
		
		jQuery(".filter_holder > DIV.filter_option *[name]").each(function(i){
			POST_DATA[jQuery(this).attr("name")] = jQuery(this).val();
		});
		
		for(var key in POST_DATA){
			if(POST_DATA[key])
				frm.append("<INPUT type='hidden' name='" + key + "' value='" + POST_DATA[key] + "' />");
		}
			
		frm.submit();
		return false;
	});
}

$(document).ready(function(){
	$('#edit_language').change(function(){
		setTimeout(function(){ doLoad();},50);
	});
});


function copy(){
	var sel  = DG.getSelected();
	if(!sel){
		alert("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SELECT_TO_CLONE");?>");
		return false;
	}
		
	var from = sel[0];
	var to   = sel[2];
	
	if(to < from){
		from = sel[2];
	    to   = sel[0];
	}
	
	if(confirm("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLONE");?> " + (Math.abs(to - from) + 1) + (to == from ? " <?php echo strtolower(pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT"));?>" : " <?php echo strtolower(pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCTS"));?>?"))){
		var inc = (to >= from ? 1 : -1); 
		var ind = from - inc;
		do{	
			ind += inc;
			var id = DG.getDataAtRowProp(ind,"virtuemart_product_id");
			if(id){
				if(!tasks[id])
					tasks[id] = {};
				tasks[id]["DO_CLONE"] = 'clone';
			}
		}while(ind != to);	
		
		doSave(
			function(){
				alert("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLONE_FINISH");?>");
			},
			function(){
				alert("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CLONE_FAILED");?>");
			}
		);
		return true;
	}else
		return false;
}

function deleteproducts(){
	
	var sel  = DG.getSelected();
	
	if(!sel){
		alert("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SELECT_DELETE");?>");
		return false;
	}
	
	var from = sel[0];
	var to   = sel[2];
	
	if(to < from){
		from = sel[2];
	    to   = sel[0];
	}
	
    
	var lpar_ind = to;
	var lpar     = DG.getDataAtRowProp(lpar_ind,'parent');	
	if(lpar){
		for(var i = lpar_ind - 1; i >= from; i--){
			if(DG.getDataAtRowProp(i,'virtuemart_product_id') == lpar){
				var fwd = lpar_ind + 1;
				while(DG.getDataAtRowProp(fwd,'parent') == lpar){
					fwd++;
					to = fwd;
				}
				break;
			}
		}
	}
	
	if(confirm("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_REMOVE");?> " + (to - from + 1) + (to == from ? " <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT");?>? " : " <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCTS");?>?"))){
		multidel = true;
		var ind = from - 1;
		do{	
			ind += 1;
			var id = DG.getDataAtRowProp(ind,"virtuemart_product_id");
			if(id){
				if(!tasks[id])
					tasks[id] = {};
				tasks[id]["DO_DELETE"] = 'delete';
			}
		}while(ind != to);	
		
		var ind = from - 1;
		do{
			ind += 1;
			DG.alter('remove_row', from);
		}while(ind != to);		
		
		multidel = false;
		id_index = null;
		callSave();
		return true;
	}else
		return false;
}


</script>
<script data-cfasync="false"  type="text/javascript" >
window.pelm_load_ok = true;
</script>
<script src="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/lib/script.js'; ?>" data-cfasync="false"  type="text/javascript"></script>

<div id="settings-panel" class="settings-panel" style="display:none;">
<div>
  <h2> <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SETTINGS"); ?> </h2>
  <table>
  
    <tr>
	 <td colspan="2" >
	 <label><?php echo pelm_sprintf("JACTION_MANAGE_COMPONENT_DESC"); ?></label>
	 <br/>
	 <input type="hidden" name="allow_groups"  />
	 <select id="allow_groups" multiple="multiple" title="Select backend groups"> 
	 </select>
	 <p style="color:white"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_GROUPS_EMPTY"); ?></p>
	 </td>
	</tr> 
  
    <tr>
	 <td colspan="2">
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PARCH"); ?></label>
	   <input type="checkbox" value="1" name="par_ch_stick_disable" <?php echo $SETTINGS->par_ch_stick_disable ? " checked='checked' " : ""; ?> />
	   <p style="color:white"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILERTS_STRICT"); ?></p>
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_UPDATE_MODIFIED"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="modified_update" <?php echo $SETTINGS->modified_update ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FROZEN"); ?></label>
	 </td>
	 <td>
	   <input type="text" name="frozen_columns" value="<?php echo $SETTINGS->frozen_columns; ?>" />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CF_VAL_SEPARATOR"); ?></label>
	 </td>
	 <td>
	   <input type="text" name="cf_val_separator" value="<?php echo $SETTINGS->cf_val_separator; ?>" />
	 </td>
	</tr>
	
	<tr>
	 <td colspan="2" >
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_HIDECOLUMNS"); ?></label>
	 <br/>
	 <input type="hidden" name="hidden_columns"  />
	 <select id="hidden_columns" multiple="multiple"> 
	 </select>
	 </td>
	</tr>
	
	

	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OTHER_PRICES"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="prices" <?php echo $SETTINGS->prices ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>

	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_ALLOW_ADD"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="allow_add" <?php echo $SETTINGS->allow_add ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>

	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_ALLOW_DELETE"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="allow_delete" <?php echo $SETTINGS->allow_delete ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CREATE_CATEGORIES"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="create_categories" <?php echo $SETTINGS->create_categories ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_ALLOW_AUTOIMPORT"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="allow_autoimport" <?php echo $SETTINGS->allow_autoimport ? " checked='checked' " : ""; ?> />
	   <a target="_blank" href="<?php echo JURI::root(1) . '/administrator/components/com_vmexcellikeinput/utils/autoimport_vmpelm.zip'; ?>" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DOWNLOAD_AUTOIMPORT_SCRIPT"); ?></a>
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OVERRIDE_PRICE"); ?></label>
	 </td>
	 <td>
	   <select name="override_price" >
	     <option value="1"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OVERRIDE_FINAL"); ?></option>
		 <option value="-1"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OVERRIDE_WIHOUT_TAX"); ?></option>
	   </select>
	   <script data-cfasync="false"  type="text/javascript">
	     jQuery(document).ready(function(){
		      jQuery('#settings-panel SELECT[name="override_price"]').val('<?php echo $SETTINGS->override_price;?>');   
		 });
	   </script>
	 </td>
	</tr>
	
	<tr>
		<td colspan="2">
			<h2 style="color:white;font-size:20px;"><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT_IMPORT"); ?></h2>
		</td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT_CONTENT"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="export_content" <?php echo $SETTINGS->export_content ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_CONTENT"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="import_content" <?php echo $SETTINGS->import_content ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT_IMAGES"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="export_images" <?php echo $SETTINGS->export_images ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_IMAGES"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="import_images" <?php echo $SETTINGS->import_images ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_EXPORT_IMAGES_ADDIT"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="export_images_meta" <?php echo $SETTINGS->export_images_meta ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	<tr>
	 <td>
	 <label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_IMAGES_ADDIT"); ?></label>
	 </td>
	 <td>
	   <input type="checkbox" value="1" name="import_images_meta" <?php echo $SETTINGS->import_images_meta ? " checked='checked' " : ""; ?> />
	 </td>
	</tr>
	
	
	<tr>
		<td colspan="2">
			<input type="checkbox" value="1" name="german_numbers" <?php echo $SETTINGS->german_numbers ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DEC_SEP"); ?></label>	
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
			<input type="checkbox" value="1" name="cf_no_name" <?php echo $SETTINGS->cf_no_name ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_NOCFNAMES"); ?></label>	
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
			<input type="checkbox" value="1" name="import_images_overwrite" <?php echo $SETTINGS->import_images_overwrite ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_OVERWRITE_IMG"); ?></label>	
		</td>
	</tr>
	
	<tr>
	    <td>
			<label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_MAX_TIME"); ?></label>	
		</td>
		<td>
			<input type="text" value="<?php echo $SETTINGS->max_time; ?>" name="max_time" />
		</td>
	</tr>
	
	
	
	
	
	
	<tr>
	 <td colspan="2" >
		<hr/>
		<label style="font-size:16px;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CSV_OPT"); ?></label>
	    <label  class="note" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CUSTCSV_EXP"); ?></label>
        <br/>
		  	
		<input name="csv_separator" type="text" value="<?php echo str_replace("\t","{tab}",$SETTINGS->csv_separator) ;?>" /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CSV_SEP"); ?></label>
		<br/>
		<input type="checkbox" value="1" name="custom_import" <?php echo $SETTINGS->custom_import ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_USE_CUST_IMP"); ?></label>
	    <br/>
		<input type="checkbox" value="1" name="first_row_header" <?php echo $SETTINGS->first_row_header ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CIMP_FH"); ?></label>		
	    <br/>
		<label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_CUST_IMPORT_INSTRUCT"); ?>:</label>
		<br/>
		<label class="note" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_IDSKU_REQ"); ?>:</label>
		<br/>
		<input type="hidden" name="custom_import_columns"  />
	    <select id="custom_import_columns" multiple="multiple" > 
	    </select>
	 </td>
	</tr>
	<tr>
		<td colspan="2" >
			<hr/>
			<label style="font-size:16px;" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CUSTOM_EXP_OPT"); ?></label>
			<label  class="note" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CUSTOM_EXP_OPT_DESC"); ?></label>

			<br/>
			
			<label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CSV_SEP_EXP"); ?></label>
			<input name="csv_separator_exp" type="text" value="<?php echo str_replace("\t","{tab}",$SETTINGS->csv_separator_exp); ?>"  />		
			<br/>
			<input type="checkbox" value="1" name="custom_export" <?php echo $SETTINGS->custom_export ? " checked='checked' " : ""; ?> /><label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_USE_CUSTOM_EXP"); ?></label>
			<br/>
			<br/>
			<label><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_CUSTOM_EXP_COLS"); ?>:</label>
			<br/>
			<label class="note" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IDSSKU_REQ");  ?>:</label>
			<br/>
			<input type="hidden" name="custom_export_columns"  />
			<select id="custom_export_columns" multiple="multiple"> 
			</select>
		</td>
	</tr>
	
  </table>
  
  <script data-cfasync="false"  type="text/javascript">
		function NormalizeChosenVal(val){
				if(!val)
					return [];
				
				if(typeof val === typeof [])
					return val;
				
				val = val.split(",");
				var ret = [];
				val.map(function(v){
					if(v){
						ret.push(v);	
					}
				});
				
				return ret;
		}
		
		<?php
		  $db->setQuery("SELECT id, Title FROM #__usergroups");
		  $groups = $db->loadAssocList("id","Title");
		?>
		
		jQuery(window).load(function(){
			 setTimeout(function(){
				 try{
					var select = jQuery('SELECT#custom_import_columns,SELECT#custom_export_columns, SELECT#hidden_columns, SELECT#allow_groups');
					
					var select_col = jQuery('SELECT#custom_import_columns,SELECT#custom_export_columns, SELECT#hidden_columns');
					var select_gr = jQuery('SELECT#allow_groups');
					
					var groups = <?php echo json_encode($groups); ?>;
					var n = 0;
					
					grid_columns.map(function(c){
						select_col.append(jQuery('<option value="' + c.data + '">' + grid_headers[n] + '<option>'));
						n++;
						return c.data;
					});
					
					if(jQuery('SELECT#hidden_columns option[value="virtuemart_product_id"]')[0]){
						jQuery('SELECT#hidden_columns option[value="virtuemart_product_id"]').remove();
					}
					
					try{
						if(groups){
							for(var g_id in groups){
								select_gr.append(jQuery('<option value="' + g_id + '">' + groups[g_id] + '<option>'));
							}
						}
					}catch(eg){
						//
					}
					
					
					jQuery("SELECT:not([data-placeholder])").attr("data-placeholder","<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SELECTOPTIONS") ?>");
					select.addClass("for-chosen").chosen(); 
					
					jQuery('SELECT#custom_import_columns').data('s_val',NormalizeChosenVal(<?php echo json_encode($SETTINGS->custom_import_columns); ?>));
					jQuery('SELECT#custom_export_columns').data('s_val',NormalizeChosenVal(<?php echo json_encode($SETTINGS->custom_export_columns); ?>));
					jQuery('SELECT#hidden_columns').data('s_val',NormalizeChosenVal(<?php echo json_encode($SETTINGS->hidden_columns); ?>));
					jQuery('SELECT#allow_groups').data('s_val',NormalizeChosenVal(<?php echo json_encode($SETTINGS->allow_groups); ?>));
					
					select.each(function(i){
						jQuery(this).prev('INPUT').val(jQuery(this).data('s_val').join(','));
						jQuery(this).val(jQuery(this).data('s_val'));
					});
					
					select.trigger("chosen:updated");
					
					select.each(function(i){
						var value = jQuery(this).data('s_val');
						for(var i = 0 ; i < value.length; i++ ){
							var opt = jQuery(this).find('option[value="'+ value[i] +'"]');
							var cnt = jQuery(this).next('DIV.chosen-container');
							cnt.find("a[data-option-array-index='" + opt.index() + "']").parent().insertBefore(cnt.find(".search-field"));
						}
					}); 
					
				 }catch(e){
					alert(e.name + ":" + e.message);
				 }
			 },2000);
		});
		
		jQuery(document).on("change","#settings-panel SELECT.for-chosen",function(){
			var self = this;
			setTimeout(function(){
				var newval = [];
				jQuery(self).next("DIV.chosen-container").find("a[data-option-array-index]").map(function(item){
					var ind = parseInt( jQuery(this).attr("data-option-array-index"));
					newval.push(
						jQuery(jQuery(self).find("option")[ind]).attr("value")
					);
				});
				jQuery(self).prev("INPUT").val(newval.join(","));
			},50);
		});
		
		jQuery(document).on('click','#settings-panel DIV.chosen-container a.search-choice-close',function(e){
			e.preventDefault();
			jQuery(this).closest("DIV.chosen-container").prev("SELECT").trigger('change');
		});
		
	</script>
  
  <button id="cmdSettingsCancel" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMPORT_CANCEL"); ?></button>
  <button id="cmdSettingsSave" ><?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE"); ?></button>
</div>
</div>
<script data-cfasync="false"  type="text/javascript">


jQuery(document).on("click","a.add-children",function(e){
	e.preventDefault();
	var virtuemart_product_id = DG.getDataAtRowProp(DG.getSelected()[0],'virtuemart_product_id');
	var row_ind = DG.getSelected()[0] + 1;
	DG.alter("insert_row",row_ind);
	DG.setDataAtRowProp(row_ind,"parent",virtuemart_product_id,"add_chield");
	id_index = null;
});
</script>

<script data-cfasync="false"  type="text/javascript">

<?php
  if($plem_errors){
?>
	 jQuery(window).load(function(){
		alert(<?php echo json_encode($plem_errors."");?>);	
	 });  
<?php	  
  }
?>

var product_images_dirty = false;
var product_content_dirty = false;

function stripHTML(dirtyString) {
  var container = document.createElement('div');
  var text = document.createTextNode(dirtyString);
  container.appendChild(text);
  return container.innerHTML; // innerHTML will be a xss safe string
}

function getCurrentContentDesc(){
	var cnt = "";
	if(jQuery("#content_edit_ifr").contents().find("*[name='product_desc']:visible")[0])
		cnt = jQuery("#content_edit_ifr").contents().find("*[name='product_desc']").val();
	else
		cnt = jQuery("#content_edit_ifr").contents().find("IFRAME").contents().find("body").html();
	
	if(jQuery.trim(stripHTML(cnt)) == ""){
		if(cnt.toLowerCase().indexOf("<a") > -1 || cnt.toLowerCase().indexOf("<img") > -1)
			return cnt;
		else
			return "";
	}else
		return cnt;
}

function saveSideContent(set_qs_url,callback){
	$('#content_editor').addClass('waiting');
	
	var data = {}; 
	
	data.content = getCurrentContentDesc();
	
	var language = 'en_gb';
	try{
		language = jQuery('#edit_language').val().toLowerCase().replace("-","_");
	}catch(e){}
	
	jQuery.ajax({
		url: window.location.href + set_qs_url + "&language=" + language,
		type: "POST",
		jsonp:false,
		dataType: "json",
		data: JSON.stringify( data ),
		success: function (returned_data) {
			
			jQuery("#content_edit_ifr").contents().find("*[name='product_desc']").val(returned_data.content);
			jQuery("#content_edit_ifr").contents().find("IFRAME").contents().find("body").html(returned_data.content);
			
			prevConent = getCurrentContentDesc();
			setTimeout(function(){
				if(callback)
					callback();
			},50);
		},
		error: function(a,b,c){
			alert( "ERROR!");
		}
	}).always(function(){
		$('#content_editor').removeClass('waiting');
	});
}

var prevConent = null;


function editContent(id, product_info, get_qs_url ,save_fn){

	
	var cur_content = "";
	if(prevConent != null){
		cur_content = getCurrentContentDesc();
		if(prevConent != cur_content)
			product_content_dirty = true;
		else
			product_content_dirty = false;
	}else
		product_content_dirty = false;
		
    product_content_dirty = false;
	
	if(product_content_dirty == true){
		if( confirm( "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE"); ?> " + $('#images_editor .product_info').text() +  " <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DESCCONTENT"); ?>?")){
			prevConent = cur_content;
			ContentEditorCurrentlyEditing.saveFN(function(){
				editContent(id, product_info, get_qs_url, save_fn);
			});
			return;
		}
		
	}
	
	ContentEditorCurrentlyEditing.saveFN = save_fn;

	jQuery('#content_editor .product_info').text(product_info);
	
	if(!$('div.right_panel').is(".content_edit")){
		
		$('div.right_panel').removeClass("filtering content_edit images_edit images_browse").addClass("content_edit");
		$('div.right_panel .toggler span').text("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCT_EDIT_CONTENT");?>");
		
	}	
	
	var language = 'en_gb';
	try{
		language = jQuery('#edit_language').val().toLowerCase().replace("-","_");
	}catch(e){}
	
	jQuery('.right_panel').addClass('waiting');
	jQuery.ajax({
		url: window.location.href + get_qs_url + "&language=" + language + "&diff=" + Math.random()+ ("&vmlang=" + jQuery("#edit_language").val() || "en-GB"),
		type: "POST",
		jsonp:false,
		dataType: "json",
		success: function (data) {
				jQuery('#content_edit_ifr').attr('virtuemart_product_id',id);
				jQuery("#content_edit_ifr").contents().find("*[name='product_desc']").val(data.content);
				jQuery("#content_edit_ifr").contents().find("IFRAME").contents().find("body").html(data.content);
				prevConent = data.content;
			},
		error: function(a,b,c){

		}
	}).always(function(){
		jQuery('.right_panel').removeClass('waiting');
	});
	
	if(jQuery('.right_panel').is('.closed'))
		jQuery('.right_panel_label').trigger('click');
	
	calculateSize();
	
}

function saveProductImages(){
	
	var data = $('#dg_images tbody TR').toArray().map(function(row){ 
		var R = jQuery(row); 
		
		var item = {}; 
		item.virtuemart_product_id = $('#dg_images').attr('virtuemart_product_id');
		item.virtuemart_media_id   = R.attr('mid');
		item.ordering              = jQuery.trim(R.find('TD.order').text());
		item.file_mimetype         = R.attr('mime');
		item.published             = R.find('TD.published INPUT:checked')[0] ? "1" : "0";
		item.file_is_product_image = R.find('TD.ismain INPUT:checked')[0] ? "1" : "0";
		
		item.file_url              = R.attr('src');
		if(R.attr('file_name')){
			item.file_url_thumb    = '';
			item.file_name         = R.attr('file_name');
		}else{
			item.file_url_thumb    = R.attr('thumb');  
		}
		
		item.file_description      = R.find('INPUT[name="description"]').val();
		item.file_meta             = R.find('INPUT[name="meta"]').val();
		item.file_title            = R.find('INPUT[name="title"]').val();
			
	    return item;
	});
	
	var data_carrier = {};
	try{
		if(Base64)
			data_carrier.data = Base64.encode(JSON.stringify(data));
	}catch(b64e){
		//	
	}	
	
	if(!data_carrier.data)
		data_carrier = data;
	
	$('#images_editor').addClass('waiting');
	jQuery.ajax({
		url: window.location.href + "&P_IMAGES=set&virtuemart_product_id=" + $('#dg_images').attr('virtuemart_product_id'),
		type: "POST",
		jsonp:false,
		dataType: "json",
		data: JSON.stringify( data_carrier ),
		success: function (data) {
			listProductImages(data);
			product_images_dirty = false;
			setTimeout(function(){
				product_images_dirty = false;
			},250);
		},
		error: function(a,b,c){
			alert( "ERROR!");
		}
	}).always(function(){
		$('#images_editor').removeClass('waiting');
	});
	
	
	
}


function listProductImages(data){
	jQuery('.main_image').css('background-image', 'none');
	jQuery('#dg_images TR:not(.header):not(.footer)').remove();
	for( var i = 0; i < data.length; i++ ){
		var sTR = "<tr mime='" + data[i].file_mimetype + "' mid='" + data[i].virtuemart_media_id + "' src='" + data[i].file_url + "' thumb='" + data[i].file_url_thumb + "' >"
				+ "<td class='move'> <span><a class='up'>&#8679;</a><a class='down'>&#8681;</a></span></td>" 
				+ "<td class='order'>" + data[i].ordering  + "</td>" 
				+ "<td class='published'><input type='checkbox' value='1' " + (data[i].published == "1" ? "checked='checked'" : "")  + " /></td>" 
				+ "<td class='ismain'><input type='radio' name='ismain' value='1' " + (data[i].file_is_product_image == "1" ? "checked='checked'" : "")  + " /></td>" 
				+ "<td class='alt_title'>"
				  + "<input type='text' name='title' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_TITLE");?>' value='" + data[i].file_title + "' />"
				  + "<input type='text' name='description' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DESCRIPTION");?>' value='" + data[i].file_description+ "' />"
				  +	"<input type='text' name='meta' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_METAINFO");?>' value='" + data[i].file_meta + "' />"
				+ "</td>"
				+ "<td class='thumb' ><img src='" + site_url + (data[i].file_url_thumb ?  data[i].file_url_thumb : data[i].file_url) + "' alt='" + (data[i].file_url_thumb ?  data[i].file_url_thumb : data[i].file_url)  + "' /> </td>" 
				+ "<td class='delete'><a>&times;</a></td>"
				+ "</tr>";	
				
		if(i == 0 ){
			jQuery('.main_image').css('background-image', 'url(' + site_url + data[i].file_url  + ')');
		}
		
		jQuery(sTR).appendTo(jQuery('#dg_images tbody'));
	}
}

function editImages(id, product_info){
	if(product_images_dirty == true){
		
		if( confirm( "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE"); ?> " + $('#images_editor .product_info').text() +  " <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMAGES"); ?>?")){
			saveProductImages();
			return;
		}
	}
	
	
	
	product_images_dirty = false;
	$('#images_editor .product_info').text(product_info);

	if(!$('div.right_panel').is(".images_edit")){
		$('div.right_panel').removeClass("filtering content_edit images_edit images_browse").addClass("images_edit");
		$('div.right_panel .toggler span').text("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_PRODUCTIMAGES");?>");
	}
	
	
	
	jQuery('#dg_images TR:not(.header):not(.footer)').remove();
	jQuery('.right_panel').addClass('waiting');
	jQuery.ajax({
	url: window.location.href + "&P_IMAGES=get&virtuemart_product_id=" + id + "&diff=" + Math.random()+ ("&vmlang=" + jQuery("#edit_language").val() || "en-GB"),
	type: "POST",
	jsonp:false,
	dataType: "json",
	success: function (data) {
			jQuery('#dg_images').attr('virtuemart_product_id',id);
			listProductImages(data);
			productImagesGridAfterChange();	
		},
	error: function(a,b,c){

		}
	}).always(function(){
		jQuery('.right_panel').removeClass('waiting');
	});
	
	if(jQuery('.right_panel').is('.closed'))
		jQuery('.right_panel_label').trigger('click');
	
	calculateSize();
}

$(document).on('click','.back-to-filter',function(e){
	e.preventDefault();
	
	if(product_images_dirty){
		
		if( confirm( "<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_SAVE"); ?> " + $('#images_editor .product_info').text() +  " <?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_IMAGES"); ?>?")){
			saveProductImages();
			return;
		}
	}
	
	product_images_dirty = false;
	
	
	$('div.right_panel').removeClass("filtering content_edit images_edit images_browse").addClass("filtering");
	$('div.right_panel .toggler span').text("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_FILTERS");?>");
	calculateSize();
});


function productImagesGridAfterChange(){
	jQuery('#dg_images  tbody TR a.up, #dg_images  tbody TR a.down').show();
	jQuery('#dg_images  tbody TR:first a.up, #dg_images  tbody TR:last a.down').hide();
	jQuery('#dg_images tbody TR TD.order').each(function(i){
		jQuery(this).html(i);
	});	
};

function mainImageSet(){
	var selected = jQuery('#dg_images > TBODY TR:first');
	jQuery('.main_image').css('background-image', 'none');
	if(selected[0]){
		var row = selected.closest('TR');
		if(row.attr('file_name'))
		   jQuery('.main_image').css('background-image', 'url(' + row.attr('src') + ')');
		else	
		   jQuery('.main_image').css('background-image', 'url(' +  site_url + row.attr('src') + ')');
	}
}

$(document).on('click','#dg_images tbody TR TD a.up',function(e){
	var TR = jQuery(this).closest("TR");
	if(TR[0])
		if(TR.prev()[0]){
			TR.insertBefore(TR.prev());
			productImagesGridAfterChange();
			mainImageSet();
			product_images_dirty = true;
		}
});

$(document).on('click','#dg_images tbody TR TD a.down',function(e){
	var TR = jQuery(this).closest("TR");
	if(TR[0])
		if(TR.next()[0]){
			TR.insertAfter(TR.next());
			productImagesGridAfterChange();	
			mainImageSet();
			product_images_dirty = true;
		}	
});

$(document).on('change','#dg_images .ismain INPUT',function(){
	var tr = jQuery(this).closest('tr');
	setTimeout(function(){
		tr.prependTo( jQuery("#dg_images tbody"));
		
		jQuery("#dg_images tbody tr td.order").each(function(ind){
			jQuery(this).html(ind);
		});
		
	},150);
	product_images_dirty = true;
});

$(document).on('keyup','#dg_images INPUT',function(){
	product_images_dirty = true;
});

$(document).on('click','#dg_images TD.delete a',function(e){
	e.preventDefault();
	var TR = jQuery(this).closest('TR');
	var file_name = "";
	
	if(TR.attr('file_name'))
		file_name = TR.attr('file_name');
    else{
		file_name = TR.attr('src').split('/');
		file_name = file_name[file_name.length - 1];
	}
	
	if( confirm("<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DELETE");?> " + file_name + '?')){
		TR.remove();
		product_images_dirty = true;
	}
	
});

$(document).on('change','INPUT.product-image-upload',function(){
	var input = this;
	if (input.files && input.files[0]) {
		for(var i = 0; i < input.files.length; i++){
			var reader = new FileReader();
			
			reader.onload = function (e) {
				var sTR = "<tr mime='" + e.target.File___.type + "' mid='' src='' thumb='' >"
						+ "<td class='move'> <span><a class='up'>&#8679;</a><a class='down'>&#8681;</a></span></td>" 
						+ "<td class='order'>" + jQuery('#dg_images tbody TR').length  + "</td>" 
						+ "<td class='published'><input type='checkbox' value='1' checked='checked' /></td>" 
						+ "<td class='ismain'><input type='radio' name='ismain' value='1' /></td>" 
						+ "<td class='alt_title'>"
						  + "<input type='text' name='title' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_TITLE");?>' value='' />"
						  + "<input type='text' name='description' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_DESCRIPTION");?>' value='' />"
						  +	"<input type='text' name='meta' placeholder='<?php echo pelm_sprintf("COM_VMEXCELLIKEINPUT_METAINFO");?>' value='' />"
						+ "</td>"
						+ "<td class='thumb' url='' ><img src='' alt='' /></td>" 
						+ "<td class='delete'><a>&times;</a></td>"
						+ "</tr>";	
					
				var tr = jQuery(sTR);
				tr.attr('src',e.target.result);
				tr.attr('file_name',e.target.File___.name);
				tr.find('.thumb IMG').attr('src',e.target.result);
				tr.appendTo(jQuery('#dg_images tbody'));
				
				var genTitle = e.target.File___.name.toLowerCase().replace(".jpg","").replace(".jpeg","").replace(".png","").replace(".gif","");
				genTitle = genTitle.replace(/\./g,' ').replace(/-/g,', ').replace(/_/g,' ');
				
				if(genTitle.length)
					genTitle = genTitle.substr(0,1).toUpperCase() + genTitle.substr(1);
				
				if(!!localStorage['p_img_upload_ask_names']){
					var aname = prompt("Input name for for " + e.target.File___.name , genTitle);
					if (aname != null) {
						genTitle = aname;
					}
				}
				
				tr.find('input[name="title"],input[name="description"],input[name="meta"]').val(genTitle);
				
				productImagesGridAfterChange();				
				if(e.target.clearinput__)
					jQuery('#p_img_upload').val(null);
				product_images_dirty = true;	
			};
			
			
			reader.File___ = input.files[i];
			if(i == input.files.length -1){
				reader.clearinput__ = true;
			}
			reader.readAsDataURL(input.files[i]);
		}
    }
});

$(document).on('click','button.save_product_images',function(){
	saveProductImages();
});

$(document).on('click','button.save_product_content',function(){
	if(ContentEditorCurrentlyEditing.saveFN)
		ContentEditorCurrentlyEditing.saveFN(null);
});




try{
	var fFR = FileReader;
}catch(e){
	jQuery('#dg_images tfoot td').html('To upload images plese update your browser!').css('color','red').css('font-weight','bold');
}

var FF = !(window.mozInnerScreenX == null);
if(FF && !localStorage["pelm_FF_warn"]){
	localStorage["pelm_FF_warn"] = 1;
	alert("Firefox is not recommended to be used with PELM. We recommend Chrome browser");
}
</script>

<div class="hidden-control-models" style="display:none;">
	<div class="shopper-groups" >
	<select>
		<option value="0"></option>
	<?php
		$db->setQuery("SELECT virtuemart_shoppergroup_id, shopper_group_name
					   FROM #__virtuemart_shoppergroups
					   ORDER BY shopper_group_name ASC");			
		$groups = $db->loadObjectList();
		foreach($groups as $group){?>
		<option value="<?php echo $group->virtuemart_shoppergroup_id; ?>" ><?php echo pelm_sprintf($group->shopper_group_name) ? pelm_sprintf($group->shopper_group_name) : $group->shopper_group_name ; ?></option>
	<?php
		}
	?>
	</select>
	</div>
	
	<div class="tax" >
	<select>
	<?php
		foreach($taxs_asoc as $t_id => $t_name){?>
		<option value="<?php echo $t_id; ?>" ><?php echo $t_name; ?></option>
	<?php
		}
	?>
	</select>
	</div>
	
	<div class="discount" >
	<select>
	<?php
		foreach($disc_asoc as $d_id => $d_name){?>
		<option value="<?php echo $d_id; ?>" ><?php echo $d_name; ?></option>
	<?php
		}
	?>
	</select>
	</div>
	
	<div class="currency" >
	<select>
		<option value="0"></option>
	<?php
		foreach($curr_asoc as $c_id => $c_name){?>
		<option value="<?php echo $c_id; ?>" ><?php echo $c_name; ?></option>
	<?php
		}
	?>
	</select>
	</div>
	
	
</div>
<?php
foreach(get_pelm_plugins_toinc("body_close") as $plg)
	include($plg);
?>

</body>
</html>
<?php
exit;
?>
