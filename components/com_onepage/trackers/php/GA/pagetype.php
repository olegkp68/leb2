<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


// Home
  // Search results
  // 404 page
  // Category
  // Productdetail
  // Checkout funnel (all steps)
  // Service --> information about Shipping, Payments, Return, etc. 
  // Uncategorized --> all pages without a specific pagetype

$option = JRequest::getVar('option', ''); 
$view = JRequest::getVar('view', ''); 
$task = JRequest::getVar('task', ''); 

$pageType = 'Uncategorized'; 

$checkoutType = 'Checkout funnel (all steps)'; 

if ($option === 'com_virtuemart') {
	if ($view === 'productdetails') $pageType = 'Productdetail'; 
	if ($view === 'category') $pageType = 'Category'; 
	if ($view === 'cart') {
		$layout = JRequest::getVar('layout', ''); 
		if (strpos($layout, 'order') === 0) {
		   $pageType = 'purchase'; 
		}
		else {
			$pageType = $checkoutType;
		}
	}
	if ($view === 'opc') $pageType = 'purchase'; 
	if ($view === 'order') $pageType = 'Service'; 
	if ($view === 'vendor') $pageType = 'Service'; 
	if ($view === 'manufacturer') $pageType = 'Category'; 
	if ($view === 'user') $pageType = 'User'; 
	if ($view === 'pluginresponse') 
	{
		switch ($task) {
			case 'pluginUserPaymentCancel':
			$pageType = $checkoutType; 
			default: 
			$pageType = 'Service'; 
		}
		
	}
	if ($view === 'vmplg') {
		switch ($task) {
			case 'pluginUserPaymentCancel':
			$pageType = $checkoutType; 
			default: 
			$pageType = 'Service'; 
		}
		
	}
}
elseif ($option === 'com_rupsearch') {
							  
	$pageType = 'Search results'; 
}
elseif ($option === 'com_onepage') {
	$pageType = 'purchase'; 
}
elseif ($option === 'com_users') {
	$pageType = 'User'; 
}
elseif ($option === 'com_content') {
	$pageType = 'Article'; 
}

$src = array('com_rupsearch', 'com_finder', 'com_search'); 
if (in_array($option, $src)) {
	$pageType = 'Search'; 
}

$app = JFactory::getApplication();
$menu = $app->getMenu();
								
if (!empty($menu)) {
$lang = JFactory::getLanguage();
$act = $menu->getActive();
$d = $menu->getDefault($lang->getTag()); 
if (!empty($d)) {
$d_id = $d->id; 
}
else {
	$d_id = 0; 
}

$d2 = $menu->getDefault(); 
$d_id2 = $d2->id; 
if (!empty($act) && (is_object($act))) {
$act_id = $act->id; 


										 

if (($act_id === $d_id) || ($act_id === $d_id2)) {
	$pageType = 'Home'; 
}

}
}
