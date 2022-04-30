<?php
/* 
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* This loads before first ajax call is done, this file is called per each shipping html generated
*/
defined('_JEXEC') or die('Restricted access');



// local variables are defined in \components\com_onepage\helpers\transform.php
// $vm_id, $html (is the original output)

$dispatcher = JDispatcher::getInstance();

$result = ''; 
$method = new stdClass(); 

$returnValues = $dispatcher->trigger('getPluginHtmlOPC', array(&$result, &$method, 'shipment', $vm_id, $cart));

$def_html = $result; 

$file = JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'zasilkovna.json'; 
jimport( 'joomla.filesystem.file' );
if (file_exists($file))
{
  $data = file_get_contents($file); 
  
  $json = json_decode($data); 
  
  
  if (!empty($json))
 {
   $time = $json->OPCtime; 
   $now = time(); 
   if (($now - $time) > (24 * 60 * 60)) $refresh = true; 
   
  
 }
 else $refresh = true; 
}
else $refresh = true; 



if ((!empty($refresh)))
{
	
if (isset($method->zasilkovna_api_pass))
{
	
	$zas_model = VmModel::getModel('zasilkovna');
	if (method_exists($zas_model, 'setConfig'))
	{
	 $zas_model->setConfig($method->zasilkovna_api_pass); 
     $url = $zas_model->_zas_url.'api/v2/'.$zas_model->api_key.'/branch.json'; 
     $data = OPCloader::fetchUrl($url); 
	 
	 
	}
}
else
{
$zas_model = VmModel::getModel('zasilkovna');
$url = $zas_model->_zas_url.'api/v2/'.$zas_model->api_key.'/branch.json'; 
$data = OPCloader::fetchUrl($url); 
}


$json = @json_decode($data); 
if (!empty($json))
{
  $json->OPCtime = time(); 
  $data = @json_encode($json); 
}

 JFile::write($file, $data); 
}




$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

$country_id = $address['virtuemart_country_id']; 
		$db = JFactory::getDBO(); 
		$q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$country_id.' limit 1'; 
		$db->setQuery($q); 
		$current_country_2 = strtolower($db->loadResult()); 
		
	
	
		
$extra_json = JRequest::getVar('extra_json', ''); 
if (!empty($extra_json)) {
	$data = json_decode($extra_json, true); 
	
	if (isset($data['branch_id'])) {
		$branch_id = (int)$data['branch_id'];
	}
}
if (empty($branch_id)) {
 $branch_id = JRequest::getInt('branch_id', 0); 
}
