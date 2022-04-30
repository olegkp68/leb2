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
include(__DIR__.DIRECTORY_SEPARATOR.'helper.php'); 	

if (!empty($json))
{

$extra = '';
$sel = '<select class="zasielka_select" name="branch" onchange="opc_zas_change(this, '.$vm_id.');" id="branchselect_'.$vm_id.'" >';
//if (!in_array('sk', $method->country)) 
if (((is_array($method->country)) && (!in_array('sk', $method->country))) || ((is_string($method->country) && ($method->country !== 'sk'))))
$sel .= '<option data-branch-id="" value="">–– vyberte si místo osobního odběru ––</option>';
else
$sel .= '<option data-branch-id="" value="">–– vyberte si miesto osobného odberu ––</option>';
$num_branches = 0; 
if (!empty($json->data))
foreach ($json->data as $branch)
{
if (!isset($branch->id)) continue; 
  // if ($branch->country == 'cz') $country = 'ČR'; 
  $cc = $branch->country; 
  $branch->id = (int)$branch->id;
  if (!empty($method->country))
  if (((is_array($method->country)) && (!in_array($cc, $method->country))) || ((is_string($method->country) && ($method->country != $cc))))
  {
    continue; 
  }
  if (isset($json->countries->{$cc})) {
  $country = $json->countries->{$cc}; 
  
  if ($cc !== $current_country_2) continue; 
  
  $sel .= '<option data-branch-id="'.$branch->id.'" value="'.$branch->id.'" '; 
  if ($branch->id === $branch_id) $sel .= ' selected="selected" '; 
  $sel .= ' >'.$country.', '.$branch->nameStreet.'</option>'; 
  $num_branches++; 
  }

}
$sel .= '</select>';
$post = ''; 

if (!empty($num_branches)) {

if (!defined('ZAS_ONCE'))
{
$post = '<input type="hidden" name="branch_id" id="branch_id" value="" />
        <input type="hidden" name="branch_currency" id="branch_currency" value="" />
        <input type="hidden" name="branch_name_street" id="branch_name_street" value="" />'; 

define('ZAS_ONCE', 1); 
}



if (strpos($def_html, 'id="shipment_id_'.$vm_id.'"')===false)
{
$def_html = str_replace('name="virtuemart_shipmentmethod_id"', ' name="virtuemart_shipmentmethod_id" id="zasilkovna_shipment_id_'.$vm_id.'" ', $def_html); 




}
else {
	$def_html = str_replace( 'id="shipment_id_'.$vm_id.'"', ' name="virtuemart_shipmentmethod_id" id="zasilkovna_shipment_id_'.$vm_id.'" ', $def_html); 
}
$def_html = str_replace('for="shipment_id_'.$vm_id.'"', 'for="zasilkovna_shipment_id_'.$vm_id.'"', $def_html); 



include(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'); 
if (empty($shipping_inside_choose))
$def_html = str_replace('value="'.$vm_id.'"', 'value="'.$vm_id.'" data-json="'.htmlentities(json_encode(array('branch_id'=>$branch_id))).'"', $def_html); 
$ex = ''; 
//$html = $def_html.'<input type="radio" name="virtuemart_shipmentmethod_id" id="zas_vm_'.$vm_id.'" value="'.$vm_id.'"><div id="opc_zas_place">&nbsp;</div>'.$sel.$ex.$post; 
$html2 = ''; 
if (!empty($branch_id)) {
	include(__DIR__.DIRECTORY_SEPARATOR.'extras.php'); 
	$html2 = $html; 
}

$html = '<div class="zasilkovina_output">
  <div style="clear: both;">'.$def_html.'
   <div id="opc_zas_place" style="clear: both;">&nbsp;</div>
   <div for="shipment_id_'.$vm_id.'">'.$sel.'</div>'.$ex.'
   <div id="zasilkovna_resp_'.$vm_id.'">&nbsp;'.$html2.'</div>'.$post.'
  </div>
  </div>'; 


}
else {
	$html = '&nbsp;'; 
}



}
else {
	//$html = '&nbsp;'; 
}