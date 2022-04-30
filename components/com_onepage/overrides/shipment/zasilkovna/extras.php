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
if (empty($json)) {
 include(__DIR__.DIRECTORY_SEPARATOR.'helper.php'); 	
}
$extra = ''; 
if (!empty($json))
{

foreach ($json->data as $branch)
{
if (!isset($branch->id)) continue; 
  
  
  $branch->id = (int)$branch->id;
  
if ($branch->id === $branch_id) {

	
$extra .= '
<div class="zasielka_div1" style="padding-top: 8px; clear:both;" id="zas_branch_'.$branch->id.'">
 <div class="zas_image" style="float: left; max-width: 50%; margin:0; padding:0;">
 <a class="opcmodal" rel="{handler: \'iframe\', size: {x: 500, y: 400}}" href="'.$branch->photos[0]->normal.'"><img style="border:1px solid black; margin-right: 8px; float: left; " src="'.str_replace('http:', '', $branch->photos[0]->thumbnail).'" width="160" height="120" /></a>
 </div>
<div class="zasielka_div2"  style="float: left; clear:right; max-width: 50%;margin:0; padding:0;">
  <strong>'.$branch->place.'</strong><br/>'; 
  $extra .= $branch->street.'<br/>'; 
  $extra .= $branch->zip.' '; 
  $extra .= $branch->city.'<br />'; 
  if (!empty($branch->openingHours) && (is_string($branch->openingHours->compactLong)))
  {
  $extra .= '<div style="margin-top: 8px;"><div style="float: left; clear:both;"><em style="clear: both;">Otevírací doba:</em></div><br style="clear:both;"/>'; 
  $extra .= $branch->openingHours->compactLong.'</div>'; 
  }
  else 
  {

  }
  $extra .= '</div>'; 
 
 $extra .= '</div> <input type="hidden" name="branch_id'.$branch->id.'" id="branch_id'.$branch->id.'" value="'.$branch->id.'" />'; 
 $extra .= ' <input type="hidden" name="branch_currency'.$branch->id.'" id="branch_currency'.$branch->id.'" value="'.$branch->currency .'" />'; 
 $extra .= ' <input type="hidden" name="branch_name_street'.$branch->id.'" id="branch_name_street'.$branch->id.'" value="'.$branch->nameStreet .'"/>';
  
  
  $na = array(); 
  $na['branch_id'] = $branch->id; 
  $na['branch_name_street'] = $branch->nameStreet; 
  $na['branch_currency'] = $branch->currency; 
  $data = json_encode($na); 
  $newjson = '<input type="hidden" name="zasilkovna_shipment_id_'.$vm_id.'_extrainfo" value="'.base64_encode($data).'" />'; 
  $extra .= $newjson;
  
  $md5 = md5($newjson); 
  OPCloader::$inform_html[$md5] = $newjson; 
  // end json foreach 
}
}


}

$html = $extra; 