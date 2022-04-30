<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license COMERCIAL 
* 
*/

/* EXAMPLE USAGE, CREATE A NEW SHELL SCRIPT:
#!/bin/bash

##moss test: vat 10 / vat 20 / moss:
#'{"847":3,"846":4,"845":5}'
#php7 /srv/www/rupostel.com/web/vm2/purity/administrator/components/com_onepage/cli.php 
#--override_jroot=/srv/www/rupostel.com/web/vm2/purity --debug=1

php7 /srv/www/rupostel.com/web/vm2/purity/administrator/components/com_onepage/cli.php \
--task=neworder \
--products_json='{"11830":10,"164":10,"11831":10}' \
--user_id=42 \
--order_status=P \
--myurl=https://vm2.rupostel.com/purity/ \
--override_jroot=/srv/www/rupostel.com/web/vm2/purity \
--virtuemart_paymentmethod_id=12 \
--virtuemart_shipmentmethod_id=27 \
--return_status_json=0 \
--coupon_code="parent"

printf "\n"


#--debug=1 \
printf "\n"


#user/address not found error: 

printf "\n"
php /srv/www/rupostel.com/web/vm2/purity/administrator/components/com_onepage/cli.php \
--task=neworder \
--products_json='{"1109":3}' \
--user_id=47 \
--order_status=U \
--myurl=https://vm2.rupostel.com/purity/ \
--override_jroot=/srv/www/rupostel.com/web/vm2onj25 \
--debug=1 \
#--return_status_json=1 \

printf "\n"

#product not found error: 
php /srv/www/rupostel.com/web/vm2/purity/administrator/components/com_onepage/cli.php \
--task=neworder \
--products_json='{"1109":3}' \
--user_id=42 \
--order_status=U \
--myurl=https://vm2.rupostel.com/purity/ \
--override_jroot=/srv/www/rupostel.com/web/vm2onj25 \
--debug=1 \
#--return_status_json=1 \
--coupon_code=opctest50 \

printf "\n"

php /srv/www/rupostel.com/web/vm2/purity/administrator/components/com_onepage/cli.php \
--task=neworder \
--products_json='{"10001":10,"70":10,"10002":10}' \
--user_id=804 \
--order_status=U \
--myurl=https://vm2.rupostel.com/purity/ \
--override_jroot=/srv/www/rupostel.com/web/vm2onj25 \
--coupon_code=opctest50 \
#--return_status_json=1 \

printf "\n"


*/





ob_start(); 

if (php_sapi_name() !== 'cli') {
	die('Access denied - use CRON to access php directly!'); 
	exit(1); 
}



define('OPCCLI', 1); 
define( '_JEXEC', 1 );
define( '_VALID_MOS', 1 );

function opccli_fatal_handler() {
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];
    $types = array(E_ERROR,  E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR); 
	if (!in_array($errno, $types)) return;
	$dates = date('c'); 
	$dataMsg = $errno.' '.$errstr.' in file: '.$errfile.' line: '.$errline." timestamp: ".$dates;
	if ((class_exists('cliHelper') && (!empty(cliHelper::$returnjson)))) {
	   cliHelper::flushBuffer();
	   $em = cliHelper::mapErrorCode($errno); 
	   cliHelper::returnJson(0, $em.':'.$dataMsg, 9999); 
	   die(1); 
	}
	else {
		$em = ''; 
	    if (class_exists('cliHelper')) {	
		  $em = ':'.cliHelper::mapErrorCode($errno); 
	    }
		echo 'OPC CLI: '.$em.$dataMsg."\n"; 
		$x = debug_backtrace(); 
		if (!empty($x)) {
			foreach ($x as $l) {
				if (!empty($l['file'])) {
				echo $l['file'].' '.$l['line']."\n"; 
			}
			}
		}
		if (function_exists('xdebug_get_function_stack')) {
		$x = xdebug_get_function_stack();
		foreach ($x as $l) {
			if (!empty($l['file'])) {
				echo $l['file'].' '.$l['line']."\n"; 
			}
		}
		}
		
		die(1); 
	}
     
  }
}

function opc_exceptions_error_handler($severity, $message, $filename, $lineno) {
	$severity = cliHelper::mapErrorCode($severity); 
	cliHelper::debug($severity.': '.$message.' @ '.$filename.':'.$lineno); 
	return true; 
}
function opc_exceptions_error_handler2($ex) {
		
		$msg = $ex->getMessage(); 
		
		$code = 0; 
		$file = ''; 
		$line = 'unknown'; 
		$trace = ''; 
		if (method_exists($ex, 'getCode'))
		$code = $ex->getCode(); 
		if (method_exists($ex, 'getFile'))
		$file = $ex->getFile(); 
		if (method_exists($ex, 'getLine'))
		$line = $ex->getLine(); 
		
		$trace .= $file.':'.$line."\n"; 
		
		if (method_exists($ex, 'getTraceAsString'))
		$trace .= $ex->getTraceAsString(); 
		
		
		if ((empty($code)) || ($code == E_WARNING) || ($code == 1054) || ($code == 1142)) {
			
			$code = E_ERROR; 
		}
	
		$severity = cliHelper::mapErrorCode($code); 
	    cliHelper::debug($severity.': '.$msg.' @ '.$file.':'.$line); 
		
		$x = debug_backtrace(); 
		if (!empty($x)) {
			cliHelper::debug('debug_backtrace:'); 
			foreach ($x as $l) {
				if (!empty($l['file'])) {
				cliHelper::debug( $l['file'].' '.$l['line']); 
			}
			}
		}
		
		
		
		if (function_exists('xdebug_get_function_stack')) {
			cliHelper::debug('xdebug_get_function_stack:'); 
		$x = xdebug_get_function_stack();
		foreach ($x as $l) {
			if (!empty($l['file'])) {
				cliHelper::debug( $l['file'].' '.$l['line']); 
			}
		}
		}
		else {
			cliHelper::debug($trace); 
		}
		return true; 
	
	}


register_shutdown_function( "opccli_fatal_handler" );
set_error_handler('opc_exceptions_error_handler'); 
set_exception_handler('opc_exceptions_error_handler2'); 

require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'clihelper.php'); 


$locale = setlocale  (LC_ALL,"0"); 
if (stripos($locale, 'UTF-8')===false) {
 @setlocale(LC_CTYPE, "en_US.UTF-8");
}

@ini_set('memory_limit', '32G'); 
@ini_set('memory_limit', PHP_INT_MAX); 
set_time_limit(PHP_INT_MAX); 

$path = realpath(dirname(__FILE__) .'/../../../'); 

$shortopts = "";
$longopts = array('task:', 'order_item_id::', 'user_id::', 'order_item_json::', 'order_status::', 'override_jroot::', 'products_json::', 'productsdata_json::', 'myurl::', 'debug::', 'return_status_json::', 'virtuemart_shipmentmethod_id::', 'virtuemart_paymentmethod_id::', 'coupon_code::', 'csvfile::', 'csvpricefile::', 'virtuemart_shoppergroup_id::', 'logfile::', 'class::', 'csv-separator::', 'csv-skip-first-line::', 'sku-column-index::', 'stock-column-index::', 'csv-enclosure::', 'order_number::', 'lang::'); 



$options = getopt($shortopts, $longopts); 

if (!empty($options['logfile'])) {
	cliHelper::$logfile = $options['logfile']; 
}

if (isset($preoptions)) {
	foreach ($preoptions as $k=>$v) {
		$options[$k] = $v; 
	}
}

/* GLOBAL VARIABLES DEFINITION */
if (isset($options['override_jroot'])) {
  $path = $options['override_jroot']; 
}
else {
	$jroot = false; 
if (($_SERVER['PHP_SELF'] === 'export.php') && (file_exists($_SERVER['PWD'].DIRECTORY_SEPARATOR.'export.php'))) {
	
	$xa = explode(DIRECTORY_SEPARATOR, $_SERVER['PWD']); 
	array_splice($xa, -3); 
	$jroot = implode(DIRECTORY_SEPARATOR, $xa); 
	
}
else {
	
	if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..')) {
		$jroot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..';
	}
	}
	if (!empty($jroot)) {
		$path = $jroot; 
		$options['override_jroot'] = $path; 
	}
}


if (!empty($options['return_status_json'])) {
	
  cliHelper::$returnjson = true; 
}
else {
	cliHelper::$returnjson = false; 
}

$debug=false; 
if (!empty($options['debug'])) {
	$debug = true; 
}

$order_id = 0; 
$e = ''; 


cliHelper::setErrorReporting($debug); 

define( 'JPATH_BASE',  $path) ;
if (!defined('DS'))
define( 'DS', DIRECTORY_SEPARATOR );


if (!file_exists(JPATH_BASE. DS.'configuration.php')) {
	if ($debug) {
		cliHelper::showUsage('Joomla root directory ('.$path.') could not be found, use --override_jroot= to set it to your Joomla Root directory', 9001); 
	}
	else {
		cliHelper::showUsage('Joomla root directory could not be found, use --override_jroot= to set it to your Joomla Root directory', 9001); 
	}
	die(1); 
}


if (file_exists(JPATH_BASE. '/defines.php')) {
	include_once JPATH_BASE . '/defines.php';
}


if (!defined('_JDEFINES')) {	
	require_once JPATH_BASE.'/includes/defines.php';
}




$_POST = $_GET = $_REQUEST = array(); 
$_SERVER['REQUEST_METHOD'] = 'GET'; 
$_SERVER['HTTP_HOST'] = 'localhost'; 


$QUERY_STRING = "option=com_onepage&view=xmlexport";
$_SERVER["REQUEST_SCHEME"]='http'; 

$_SERVER["DOCUMENT_ROOT"] = JPATH_SITE; 
$_SERVER['SCRIPT_FILENAME'] = JPATH_SITE.DIRECTORY_SEPARATOR.'index.php'; 
$_SERVER['SCRIPT_NAME'] = '/index.php'; 
$_SERVER["PHP_SELF"] = '/index.php'; 
$_SERVER['HTTP_USER_AGENT'] = 'Cron'; 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; 
$_SERVER['SERVER_PORT'] = 80; 

chdir(JPATH_SITE); 

if (!empty($options['debug'])) {
	$debug = true; 
}




//reset error reporting when changed by joomla !
cliHelper::setErrorReporting($debug); 

cliHelper::flushBuffer(); 


define('JPATH_COMPONENT', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'); 



cliHelper::flushBuffer(); 




	$default = 'http://localhost/'; 
	if (empty($options['myurl'])) {
		
	  require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'framework.php' );
	  $app = JFactory::getApplication('site');																						   
	if (!empty($options['debug'])) {
	cliHelper::debug('multilanguage problem: use --myurl parameter to point to language entry page'); 
	}
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	  
	  echo 'OPC CLI.PHP (C) Rupostel.com 2016'."\n"; 
      $site = $fullpathsite = OPCconfig::getValue('xmlexport_config', 'xml_live_site', 0, $default); 
	}
	else 
	{
		$site = $fullpathsite = $options['myurl']; 
	}
	
	$_SERVER['SERVER_SOFTWARE'] = 'Apache/2.4.23'; 
	
	$site = str_replace('http://', '', $site); 
	$site = str_replace('https://', '', $site); 
	$x = strpos($site, '/'); 
	if ($x !== false) {
		$siteO = $site; 
		$site = substr($site, 0, $x); 
		$_SERVER['HTTP_HOST'] = $site; 
		$_SERVER['SERVER_NAME'] = $site; 
		$rest = substr($siteO, $x); 
		
		
		if (substr($rest, 0, 1) !== '/') $rest = '/'.$rest; 
		if (substr($rest, -1) !== '/') $rest = $rest.'/'; 
		$_SERVER['SCRIPT_URL'] = $rest.'index.php'; 
		$_SERVER['SCRIPT_URI'] = $fullpathsite; 
		$REQUEST_URI = $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_URL']; 
		
		if (substr($fullpathsite, 0,5) == 'https') {
			$_SERVER['SERVER_PORT'] = 443; 
			$_SERVER['SSL_TLS_SNI'] = $_SERVER['HTTP_HOST']; 
			$_SERVER['REQUEST_SCHEME'] = 'https'; 
			$_SERVER['HTTPS'] = 'on'; 
			
		}
		
	}

$lang = ''; 
if (isset($options['lang'])) {
	$lang = $options['lang'];
	$_GET['lang'] = $options['lang']; 
	$REQUEST_URI .= '?lang='.$options['lang'];
	$QUERY_STRING .= '&lang='.$options['lang'];
	
}

$_SERVER['QUERY_STRING'] = $QUERY_STRING;
$_SERVER['REQUEST_URI'] = $REQUEST_URI;



$app = null; 
try {
require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'framework.php' );
if ( version_compare( JVERSION, '3.0', '<' ) == 1) {    
 $app = JFactory::getApplication('site');
}
else {
	$app = JFactory::getApplication('site');
	$app = JFactory::getApplication();
}	



echo 'OPC CLI.PHP (C) Rupostel.com 2016'."\n"; 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 

if (!empty($options['debug'])) {
  cliHelper::debug(' Joomla Initialized'); 
}

if (empty($options)) {
  cliHelper::showUsage('No options provided', 9999); 
  
}

if (!empty($options['debug'])) {
  cliHelper::debug(' Parameters parsed'); 
  cliHelper::debug(' Root URL: '.Juri::root()); 
  cliHelper::debug(' Root Path: '.JPATH_SITE); 
}


$languages   = JLanguageHelper::getLanguages('lang_code');
if (empty($lang)) {
	foreach ($languages as $l) {
		if ((int)$l->published === 1) {
			$lang = $l->sef; 
			break;
		}
	}
	
}

if (!empty($lang)) {
	   //$_GET['lang'] = $lang; 
	   $languages   = JLanguageHelper::getLanguages('lang_code');
	   
	   foreach ($languages as $l) {
		   
		   if ($l->sef === $lang) {
			   $currentLangObj = $l; 
			   
			   $app->input->set('language', $l->lang_code);
			   $langObj = JLanguage::getInstance($l->lang_code, false); 
			   $app->loadLanguage($langObj);
			   $app->set('language', $langObj);
			   
			   //JFActory::$language = $langObj; 
			   if (!empty($debug)) { 
				cliHelper::debug('Setting language to:  '.$l->lang_code); 
				}
				break;	   
			   
		   }
	   }
	   
	 

	   JPluginHelper::importPlugin('system');
	   $dispatcher = JDispatcher::getInstance();
	   try {
        $returnValues = $dispatcher->trigger('onAfterInitialise');
	   }
	   catch (Exception $e) {
	   }
	   
		//parse current URL so we set proper language:
		$uri = clone JUri::getInstance();
		if (!empty($debug)) { 
		        //the current URI must be in format: https://domain.com/index.php?lang=nl
				cliHelper::debug('Current URL including language:  '.$uri); 
				}
		try {
		$router = $app->getRouter();
		//first usage of parse initializes language in languagefilter plugin:
		$app->input->set('nolangfilter', 1);
		if ((!class_exists('plgSystemJoomsef')) && (!empty($options['myurl']))) {
		  $result = $router->parse($uri);
		  
		}
		else {
			
			if (class_exists('plgSystemJoomsef')) {
				
		        //the current URI must be in format: https://domain.com/index.php?lang=nl
				cliHelper::debug('Multilanguage and Artio SEF will not work... '); 
				
			}
			
			if (empty($options['myurl'])) {
				
				cliHelper::debug('Please set --myurl=https://yoursite.com/ as CLI cannot know what your URL is... '); 
			}
			
		}
		
		$app->input->set('nolangfilter', null);
	    }
		catch (Exception $e) {
								//no prob, this just removes language prefix.... 
							}
	   if (!empty($lang)) {
	     $test = JRoute::_('index.php?lang='.$lang); 
	   }
	   
	   
}
cliHelper::debug('Memory limit is '.ini_get('memory_limit')); 
if (isset($options['task'])) {
	$task = $options['task']; 
	switch ($task) {
	case 'cron':
		 JPluginHelper::importPlugin( 'system' );
		 JFactory::getApplication()->triggerEvent('plgRunJobsInCron', array($options)); 
		 
		 break; 
	case 'product_stock_update':
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
		$src = @$options['csvfile']; 
		
		if (empty($options['csv-separator'])) {
			$csv_separator = ';';
		}
		else {
		 $csv_separator = @$options['csv-separator']; 
		}
		
		if (empty($options['csv-skip-first-line'])) $skip_first_line = false; 
		else $skip_first_line = true; 
		
		if (empty($options['sku-column-index'])) $sku_column_index = 0; 
		else $sku_column_index = (int)$options['sku-column-index']; 
		
		if (empty($options['stock-column-index'])) $stock_column_index = 1; 
		else $stock_column_index = (int)$options['stock-column-index']; 
		
		if (empty($options['csv-enclosure'])) $csv_enclosure = '"'; 
		else $csv_enclosure = $options['csv-enclosure'];
		
		
		//'delimiter::', 'skip-first-line::', 'sku-column-index::', 'stock-column-index::'); 
		$ext = JFile::getExt($src);
		$ext = strtolower($ext); 
		if ((empty($src)) || (!file_exists($src)) || ($ext !== 'csv')) {
			
			cliHelper::showUsage('CSV file '.$src.' not found, use --csvfile=/fullpath/file.csv ', 9257); 
		}
		else {
			
		$csvParams = new stdClass(); 
		$csvParams->csv_separator = $csv_separator; 
		$csvParams->skip_first_line = $skip_first_line; 
		$csvParams->csv_enclosure = $csv_enclosure; 
		
		$JModelUtils = new JModelUtils; 
		if ($sku_column_index !== $stock_column_index) {
		  $JModelUtils->stock_update($src, $csvParams, $stock_column_index, $sku_column_index ); 
		  $e = 'done:Stock Import'."\n"; 
		}
		else {
			$e = 'Stock column index is equal to SKU column index !'; 
		}
		
		
		cliHelper::flushBuffer(); 
	    $code = 0; 
		}
		break;
	case 'to_parent_cats': 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
		$JModelUtils = new JModelUtils; 
		$JModelUtils->to_parent_cats(); 
		echo 'done:to_parent_cats'."\n"; 
		cliHelper::flushBuffer(); 
		$e = 'Category pairing OK'; 
	    $code = 0; 
		break;
	case 'xmlexport':
	/*
	   $opt = array(); 
	   $opt['language'] = 'fr-FR'; 
	   $opt['session'] = null;
	   $app->initialise(false, null, 'fr-FR'); 
	  */ 
	  
	  
	   
	   
	 
	   
	   //require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'export.php'); 
	   require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	  
	  $VirtueMartControllerXmlexport = new VirtueMartControllerXmlexport(); 
      $VirtueMartControllerXmlexport->createXml(); 

	   
	   cliHelper::flushBuffer(); 
	   $e = 'XML Export OK'; 
	   $code = 0; 
       break; 
	case 'product_import':
	
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
	    $JModelUtils = new JModelUtils; 
	    $src = @$options['csvfile']; 
		$virtuemart_shoppergroup_id = @$options['virtuemart_shoppergroup_id']; 
		if (empty($virtuemart_shoppergroup_id)) $virtuemart_shoppergroup_id = 0; 
		
		$ext = JFile::getExt($src);
		$ext = strtolower($ext); 
		if ((empty($src)) || (!file_exists($src)) || ($ext !== 'csv')) {
			
			cliHelper::showUsage('CSV file '.$src.' not found, use --csvfile=/fullpath/file.csv ', 9257); 
		}
		else {
			
			 $srcp = @$options['csvpricefile']; 
		
		$ext = JFile::getExt($srcp);
		$ext = strtolower($ext); 
		if ((empty($srcp)) || (!file_exists($srcp)) || ($ext !== 'csv')) {
		  
		  $pricefile = ''; 
		}
		else {
			$pricefile = $srcp; 
		}
		
			
		
			/*virtuemart security section start*/
			$user_id = 0; 
			if (!empty($options['user_id'])) {
				$user_id = (int)$options['user_id']; 
				
				//this will log in the user in CLI automatically: 
				JFactory::getUser()->load($user_id); 
				
			}
			
				require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
		 
				$config = new JModelConfig(); 
				$config->loadVmConfig(); 
				$session = JFactory::getSession(); 
				$token = vRequest::getFormToken(); 
				$_GET['token'] = $_POST['token'] = $token; 
				$_GET[$token] = $_POST[$token] = $_REQUEST[$token] = 1;
			
				/*virtuemart security section end*/
			
			
			
			
		$JModelUtils->csv_upload_product($src, true, $virtuemart_shoppergroup_id, true, $pricefile); 
		echo 'done:product_import'."\n"; 
		}
		cliHelper::flushBuffer(); 
		$e = 'CSV product import OK'; 
	    $code = 0;
		break;
	case 'rename_images':
	    $imgdir = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.'virtuemart'.DIRECTORY_SEPARATOR.'product'; 
	    $files = scandir($imgdir); 
		foreach ($files as $f) {
			if (stripos($f, '#')!==false) {
				echo $imgdir.DIRECTORY_SEPARATOR.$f."\n"; 
				$nf = str_replace('#', '_', $f); 
				rename($imgdir.DIRECTORY_SEPARATOR.$f, $imgdir.DIRECTORY_SEPARATOR.$nf); 
			}
		}
		
		$imgdir = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.'virtuemart'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.'resized'; 
	    $files = scandir($imgdir); 
		foreach ($files as $f) {
			if (stripos($f, '#')!==false) {
				echo $imgdir.DIRECTORY_SEPARATOR.$f."\n"; 
				$nf = str_replace('#', '_', $f); 
				rename($imgdir.DIRECTORY_SEPARATOR.$f, $imgdir.DIRECTORY_SEPARATOR.$nf); 
			}
		}
		
		break;
	case 'price_import':
	
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
	    $JModelUtils = new JModelUtils; 
	    $src = @$options['csvfile']; 
		
		$ext = JFile::getExt($src);
		$ext = strtolower($ext); 
		if ((empty($src)) || (!file_exists($src)) || ($ext !== 'csv')) {
			
			cliHelper::showUsage('CSV file '.$src.' not found, use --csvfile=/fullpath/file.csv ', 9257); 
		}
		else {
		$JModelUtils->price_import($src, true); 
		echo 'done:price_import'."\n"; 
		}
		cliHelper::flushBuffer(); 
		$e = 'CSV price import OK'; 
	    $code = 0;
		break;
	
	case 'order':
	
		
		if (!empty($options['order_item_id'])) {
		if (stripos($options['order_item_id'], ',')!==false) {
			$a = explode(',', $options['order_item_id']); 
			
			$arr = array(); 
			foreach ($a as $order_item_id) {
				// when comma separated, the quantity is always 1
				$arr[$order_item_id] = 1; 
			}
			$order_item_id = $arr; 
		}
		else {
		// when single item the quantity is always 1
		$order_item_id = (int)$options['order_item_id']; 
		if (empty($order_item_id)) { 
		   $er = 'Missing order_item_id parameter'; 
		   cliHelper::showUsage($er, 9200); 
		}
		
		$arr = array(); 
		$arr[$order_item_id] = 1; 
		$order_item_id = $arr; 
		
		}
		}
		
		if (!empty($options['order_item_json'])) {
			if (!is_string($options['order_item_json'])) {
			  cliHelper::showUsage('Error deserializing JSON - wrap paremeter data in quotes !', 9213); 
			}
			$oi = json_decode($options['order_item_json'], true); 
			if (empty($oi)) {
				cliHelper::showUsage('Error deserializing JSON', 9217); 
			}
			$order_item_id = $oi; 
		}
		
		
		
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'order.php'); 
		$e = ''; 
		
		$user_id = 0; 
		if (!empty($options['user_id'])) $user_id = (int)$options['user_id']; 
		
		if (!empty($options['order_status'])) $order_status = substr($options['order_status'], 0,1); 
		else
		$order_status = ''; 
		
		// order_item_id is either an INT or an array of INTs !
		$code = 1; $order_id = 0; 
		
		if (!empty($options['virtuemart_shipmentmethod_id'])) $virtuemart_shipmentmethod_id = (int)$options['virtuemart_shipmentmethod_id']; 
		else $virtuemart_shipmentmethod_id = 0; 
		if (!empty($options['virtuemart_paymentmethod_id'])) $virtuemart_paymentmethod_id = (int)$options['virtuemart_paymentmethod_id']; 
		else $virtuemart_paymentmethod_id = 0; 
		
		if (!empty($debug)) { 
		  cliHelper::debug(' virtuemart_paymentmethod_id:  '.$virtuemart_paymentmethod_id); 
		  cliHelper::debug(' virtuemart_shipmentmethod_id:  '.$virtuemart_shipmentmethod_id); 
		}
		
		OPCorder::createOrderFromOrderLine($order_item_id, $user_id, $order_status, $e, $debug, $code, $order_id, $virtuemart_shipmentmethod_id, $virtuemart_paymentmethod_id); 
		if (!empty($code)) {
		   cliHelper::showUsage($e, $code); 
		}
		break; 
		
	case 'load':
		try {
	   $filename = $options['class']; 
	   jimport('joomla.filesystem.file');
	   $filename = JFile::makeSafe($filename); 
	   if (!class_exists($filename))
	   if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR.$filename.'.php')) {
		   require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR.$filename.'.php'); 
	   }
	   else {
		     $er = 'File for CLI execution does not exists'; 
		     cliHelper::showUsage($er, 9537); 
	   }
	   if (class_exists($filename)) {
		   if (method_exists($filename, 'onCli')) {
			     $toRun = new $filename(); 
				 $toRun->onCli(); 
				 
				 $e = 'CLI processing '.$filename.' OK'; 
				 $code = 0; 
				 break; 
				 
		   }
		   else {
			   $er = 'CLI class '.$filename.' does not contain function onCli()'; 
			   cliHelper::showUsage($er, 9539); 
		   }
	   }
	   else {
		   $er = 'Class '.$filename.' for CLI execution does not exists'; 
		   cliHelper::showUsage($er, 9538); 
	   }
		}
		catch (Exception $e) {
			
			opc_exceptions_error_handler2($e); 
		}
		
		$e = 'CLI processing '.$filename.' Error'; 
		$code = 1; 
		
		
	   break;
	   
	case 'neworder':
	
		
		
		if (stripos($options['products_json'], '{')!==false) 
		{
			$oi = json_decode($options['products_json'], true); 
			if (empty($oi)) {
				cliHelper::showUsage('Error deserializing JSON', 9266); 
			}
			$products = $oi; 
		}
		else {
		// when single item the quantity is always 1
		$products = (int)$options['products_json']; 
		if (empty($products)) { 
		   $er = 'Missing products parameter'; 
		   cliHelper::showUsage($er, 9275); 
		}
		
		$arr = array(); 
		$arr[$products] = 1; 
		$products = $arr; 
		
		}
		
		
		
		
			
		
		
		
		
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'order.php'); 
		$e = ''; 
		
		$user_id = 0; 
		if (!empty($options['user_id'])) $user_id = (int)$options['user_id']; 
		if (empty($user_id)) {
		   cliHelper::showUsage('user_id parameter is required!', 9302); 
		}
		
		$productsdata = array(); 
		if (!empty($options['productsdata_json'])) {
			if (!is_string($options['productsdata_json'])) {
			  cliHelper::showUsage('productsdata_json parameter is provided but it is not wrapped in quotes!', 9308); 
			}
			else {
				$productsdata = json_decode($options['productsdata_json'], true); 
			}
		}
		
		if (!empty($options['order_status'])) $order_status = substr($options['order_status'], 0,1); 
		else
		$order_status = ''; 
		
		
if (!empty($debug)) {
  cliHelper::debug(' Products: '); 
  foreach ($products as $p=>$q) {
	 $p = (int)$p; 
	 $q = (float)$q; 
     cliHelper::debug(' Products detected:  ID '.$p.' and quantity '.$q); 
  }
     $user_id = (int)$user_id; 
     cliHelper::debug(' user_id:  '.$user_id); 
	 cliHelper::debug(' order_status:  '.$order_status); 
     
}
		
		foreach ($products as $pid=>$q) {
			if (empty($pid)) unset($products[$pid]); 
		}
		
		if ((empty($products)) || (!is_string($options['products_json']))) {
			  cliHelper::showUsage('Error deserializing JSON from parameter --products_json - wrap paremeter data in quotes !', 9288); 
			}
			
		
		// order_item_id is either an INT or an array of INTs !
		$code = 1; 
		
		if (!empty($options['virtuemart_shipmentmethod_id'])) $virtuemart_shipmentmethod_id = (int)$options['virtuemart_shipmentmethod_id']; 
		else $virtuemart_shipmentmethod_id = 0; 
		if (!empty($options['virtuemart_paymentmethod_id'])) $virtuemart_paymentmethod_id = (int)$options['virtuemart_paymentmethod_id']; 
		else $virtuemart_paymentmethod_id = 0; 
		if (!empty($debug)) { 
		  cliHelper::debug(' virtuemart_paymentmethod_id:  '.$virtuemart_paymentmethod_id); 
		  cliHelper::debug(' virtuemart_shipmentmethod_id:  '.$virtuemart_shipmentmethod_id); 
		}
		
		if (isset($options['coupon_code'])) {
		  $coupon_code = $options['coupon_code']; 
		}
		else {
			$coupon_code = ''; 
		}
		
		OPCorder::createNewOrder($products, $productsdata, $user_id, $order_status, $e, $debug, $code, $order_id, $virtuemart_shipmentmethod_id, $virtuemart_paymentmethod_id, $coupon_code); 
		if (!empty($code)) {
		   cliHelper::showUsage($e, $code); 
		}
		
		break; 
	default: 
	    cliHelper::showUsage('Wrong task provided', 9342); 
		break; 
	}
}
else {
	cliHelper::showUsage('No task provided', 9347); 
}

cliHelper::flushBuffer(); 

if (!empty(cliHelper::$returnjson)) {
	cliHelper::returnJson($order_id, $e, $code); 
}
else {
	echo $e."\n"; 
}
}
catch(Throwable $e ) {
  opc_exceptions_error_handler2($e); 	
}
catch(Exception $e) {
  opc_exceptions_error_handler2($e); 	
}
if (!empty($app)) {
 $app->close(0); 
 $app = null; 
}
die(0); 


