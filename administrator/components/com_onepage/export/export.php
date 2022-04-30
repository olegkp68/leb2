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
if (php_sapi_name() !== 'cli') die('Access denied - use CRON to access php directly!'); 

$jroot = false; 
if (($_SERVER['PHP_SELF'] === 'export.php') && (file_exists($_SERVER['PWD'].DIRECTORY_SEPARATOR.'export.php'))) {
	
	$xa = explode(DIRECTORY_SEPARATOR, $_SERVER['PWD']); 
	array_splice($xa, -4); 
	$jroot = implode(DIRECTORY_SEPARATOR, $xa); 
	
}
else {
	
	if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..')) {
		$jroot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..';
	}
}

//default options: 
$preoptions = array('task'=>'xmlexport', 'return_status_json'=>0, 'debug'=>1);
if (!empty($jroot)) {
	$preoptions['override_jroot'] = $jroot; 
}	
require(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cli.php'); 




