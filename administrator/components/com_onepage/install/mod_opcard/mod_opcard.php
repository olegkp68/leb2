<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_rmenu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
 
 
$option = JRequest::getVar('option', ''); 
$view = JRequest::getVar('view', ''); 
$arr = array('user', 'cart', 'opc', 'checkout'); 

if (($option == 'com_virtuemart') && (in_array($view, $arr)))
{

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );
jimport( 'joomla.application.module.helper' ); 
$hello = modOpcardHelper::getHello( $params );
//require( JModuleHelper::getLayoutPath( 'mod_opcard' ) );


$app	= JFactory::getApplication();
/*v there is some feature
$opcard	= $app->getMenu();
*/
//$active	= $menu->getActive();
//var_dump($active); die();
//$active_id = isset($active) ? $active->id : $menu->getDefault()->id;

/*
	if (!class_exists( 'VmModel' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmmodel.php');
	if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
    if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
    $cart = VirtueMartCart::getCart(false);
*/




//$path	= isset($active) ? $active->tree : array();

$notification	= $params->get('notification');
$weight	= $params->get('weight');
$weight18	= $params->get('weight_18');
$weight21	= $params->get('weight_21');
$weight_perm	= $params->get('weight_perm');
$check_notification18	= $params->get('check_notification18');
$check_notification21	= $params->get('check_notification21');
$perm_notification	= $params->get('perm_notification');
$control	= $params->get('control');
$attr1 = $params->get('attr1');
//var_dump($attr1);die();
	switch ($attr1) {
		case $weight:	
				require JModuleHelper::getLayoutPath('mod_opcard', $params->get('layout', 'default')); 
				break;
		case $weight18 : 
				require JModuleHelper::getLayoutPath('mod_opcard', $params->get('layout', 'default_over18'));
				break;
		case $weight21:
				require JModuleHelper::getLayoutPath('mod_opcard', $params->get('layout', 'default_over21'));
				break;
		case $weight_perm:
				require JModuleHelper::getLayoutPath('mod_opcard', $params->get('layout', 'default_permission'));
				break;
		default : echo 'not required';
				break;
				}
		
	$document = &JFactory::getDocument();

	$css = "templates/".$app->getTemplate()."/html/".$module->module."/css/opcard.css";
	$js = JURI::base().'modules/'.$module->module.'/assets/js/askid2.js';;
	$document->addScript($js);
	if(is_file($css)) {
	$document->addStyleSheet($css);
	} else {
	$css = JURI::base().'modules/'.$module->module.'/assets/css/opcard.css';
	$document->addStyleSheet($css);
			}

}