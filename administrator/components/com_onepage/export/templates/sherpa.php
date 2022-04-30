<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'opctracking.php'); 
		 $config = new JModelConfig(); 
		 $config->loadVmConfig(); 
		 VmConfig::loadJLang('com_virtuemart');
		 VmConfig::loadJLang('plg_vmpsplugin', false);

$msgs = array(); 
OnepageTemplateHelper::$do_no_store_output = true; 

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 


$orderModel = VmModel::getModel('orders'); 

if (!is_array($localid)) $localid = array($localid); 
asort($localid); 
if (is_array($localid)) {
	$order_ids = $localid; 
foreach ($localid as $order_id) {

$order = array(); 
$order_array = array(); 
$arr = array(); 
OPCtrackingHelper::getOrderVars($order_id, $arr, $order); 
//$order = $orderModel->getOrder($order_id);
//OPCtrackingHelper::getTextFields($order); 
$extraData = $arr;
sherpaHelper::sendOrder($order, $extraData); 

}
}
