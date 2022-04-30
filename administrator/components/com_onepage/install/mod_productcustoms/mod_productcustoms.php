<?php
defined('_JEXEC')or die;

$do_not_load_filter = false; 
if (!empty($params)) {
	$ignorecats = $params->get('ignorecats', ''); 
	$current_cat = JRequest::getInt('virtuemart_category_id', 0); 
		
		
		if (!empty($ignorecats)) {
			$nc = array(); 
			$ea = explode(',', $ignorecats);
			
			foreach ($ea as $tc)			
			{
				$tc = (int)$tc; 
				if (empty($tc)) continue; 
				if ($tc === $current_cat) {
					$do_not_load_filter = true; 
				}
			   
			}
			
		}
}
if (empty($do_not_load_filter)) {
$Itemid = JRequest::getInt('Itemid', 0); 

$category = JRequest::getVar('virtuemart_category_id', JRequest::getVar('primary_virtuemart_category_id', $params->get('default_category', 0))); 
$primary = JRequest::getVar('primary_virtuemart_category_id', 0); 
if ((empty($primary)) && (!empty($category))) {
	JRequest::setVar('primary_virtuemart_category_id', (int)$category); 
}


require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
PCH::loadVM(); 
if (!empty($module->datacats)) {
	PCH::$datacats = $module->datacats; 
	PCH::$getget = $module->getget; 
	PCH::$getcustoms = $module->getcustoms; 
	PCH::$getmf = $module->getmf; 
	PCH::$getdatacats = $module->getdatacats; 
	
	
}


require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
require(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'getget.php'); 

$params = PCH::getParams(); 


	//provided by GET and POST
  $datacats = PCH::getFilterCats(); 



$active_tab = JRequest::getVar('tab', ''); 

$filter_headers_array = array(); 
$filter_headers_array_obj = array(); 
$direction = 'ltr'; 
$view = 'module'; 
if (empty($moduleclass_sfx)) $moduleclass_sfx = ''; 

$isAdmin = PCH::checkPerm(); 

$selectedfilters = array(); 

$html = ''; 
$first = true; 

	if (empty($category)) {
		$first = false; 
	}
$active_cats = array(); 	
foreach ($datacats as $catObj) {
	$catObj = (object)$catObj; 
	$id = $catObj->virtuemart_category_id;
	$val = $catObj->category_name;
	$checked = ''; 
	
	$ind = 'virtuemart_category_id'; 
			  if ((!empty($get[$ind])) && (($get[$ind] == $id) || ((is_array($get[$ind])) && (in_array($id, $get[$ind]))))) {
			    $checked = ' checked="checked" '; 
				
				$active_cats[] = $catObj->category_name; 
			  }
	$path = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_link_category'); 
	ob_start(); 
	require($path); 
	$html .= ob_get_clean(); 
	$first = false; 
}



$default_link_path = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_link'); 
$default_delim = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_delim'); 

if (!empty($html)) {
			$key = JText::_('COM_VIRTUEMART_CATEGORIES'); 
			$filters_html_array[$key] = $html; 
			$expanded_state[$key] = 0; 
			$filter_headers_array[$key] = JText::_('COM_VIRTUEMART_CATEGORIES'); 
			$filter_headers_array_obj[$key] = new stdClass(); 
			$filter_headers_array_obj[$key]->id = 0; 
			}
if(!empty($filters_html_array))
{
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
	
	$custom_group_names = array(); 
	$datas = PCH::collectCustomsFromGet($custom_group_names); 
	
	
	
	if (!empty($datas)) {
		
		
		$nh = ''; 
		foreach ($datas as $title=>$v) {
			
			
			
			$ind = 'virtuemart_custom_id'; 
			$html = ''; 
			$has_group_name = false; 
			$loop_html = ''; 
		    foreach ($v as $id => $obj) { 
			  $obj = (object)$obj; 
			  
			  if (!empty($obj->is_delim)) {
				  ob_start(); 
					require($default_delim); 
				   $loop_html .= ob_get_clean(); 
				  continue; 
			  }
			  
			  if (!$isAdmin)
			  if (!empty($obj->has_group_name)) {
				  
				  if (!empty($custom_group_names[$title] )) {
				  
				  $has_group_name = true; 
				  unset($selectedfilters[$title]); 
				  break 1; 
				  }
			  }
			  
			  $val = $obj->custom_title; 
			  
			  //
			 
			  
			 
			  $checked = ''; 
			  if (((!empty($get[$ind])) && (($get[$ind] == $id) || ((is_array($get[$ind])) && (in_array($id, $get[$ind]))))) ) {
			    $checked = ' checked="checked" '; 
			  }
			 
	ob_start(); 
	require($default_link_path); 
	$loop_html .= ob_get_clean(); 
			
		    
			if (!empty($checked)) {
			if (empty($selectedfilters[$title])) $selectedfilters[$title] = array(); 
			$selectedfilters[$title][$id] = $val;
			}
			
			}
			
			
			if (!$has_group_name) {
			   $html .= $loop_html;	
			}
			else			
			{
				$obj = null; 
				
				foreach ($custom_group_names[$title] as $group_name => $obj) {
					
					 if (!empty($obj->is_delim)) {
					ob_start(); 
					require($default_delim); 
				    $html .= ob_get_clean(); 
					continue; 
					}
					
					$ids = (array)$obj->ids;
					asort($ids); 
					$current = implode('_', $ids); 
					
					
					
					
					$checked = ''; 
					
					//var_dump($get['virtuemart_custom_id']); die();
					//if ($get['group_'.$ind] === $current) 
						//var_dump($get['virtuemart_custom_id']); die(); 
					if (in_array($current, $get['virtuemart_custom_id']))
					{
						$checked = ' checked="checked" data-test="'.$current.'" data-test2="'.htmlentities(json_encode($get['virtuemart_custom_id'])).'"';  
						
						$selectedfilters[$title][$current] = $group_name;
						
					}
					
					
					
						 
						 $id = 0; 
						 
						 ob_start(); 
						 require($default_link_path); 
						 $html .= ob_get_clean(); 
						 
						 
						 
				}
				
				
				
			}
			
			if (!empty($html)) {
				
				
				
			$key = $title; 
			$filters_html_array[$key] = $html; 
			
			if ((!empty($active_tab)) && ($key === $active_tab)) {
				$expanded_state[$key] = 1; 
			}
			else {
			 $expanded_state[$key] = 0; 
			}
			$filter_headers_array[$key] = $title; 
			//$filter_headers_array_obj[$key] = $obj; 
			
			}
	}
	}
}

$show_sale_tab = $params->get('show_sale_tab', false); 

if (!empty($show_sale_tab))						   
if (!empty($filters_html_array)) {
			$key = JText::_('MOD_PRODUCTCUSTOMS_SALE_TAB');
			$obj = new stdClass(); 
			$obj->category_name = JText::_('MOD_PRODUCTCUSTOMS_SALE_PRODUCTS'); 
			$obj->id = 1; 
			
			$ind = 'qf'; 
			
			$sales[] = $obj; 
			
			$key = JText::_('MOD_PRODUCTCUSTOMS_DISCOUNT');
			$obj = new stdClass(); 
			$obj->category_name = JText::_('MOD_PRODUCTCUSTOMS_DISCOUNT'); 
			$obj->id = 2; 
			
			$ind = 'qf'; 
			
			$sales[] = $obj; 
			
			$key = JText::_('MOD_PRODUCTCUSTOMS_TOP');
			$obj = new stdClass(); 
			$obj->category_name = JText::_('MOD_PRODUCTCUSTOMS_TOP'); 
			$obj->id = 3; 
			
			$ind = 'qf'; 
			
			$sales[] = $obj; 
			
			
			$key = JText::_('MOD_PRODUCTCUSTOMS_NEW');
			$obj = new stdClass(); 
			$obj->category_name = JText::_('MOD_PRODUCTCUSTOMS_NEW'); 
			$obj->id = 4; 
			
			$ind = 'qf'; 
			
			$sales[] = $obj; 
			
			$html = ''; 
			foreach ($sales as $catObj) {
				$id = $catObj->id;
				$checked = ''; 
				if ((!empty($get[$ind])) && (($get[$ind] == $id) || ((is_array($get[$ind])) && (in_array($id, $get[$ind]))))) {
				  $checked = ' checked="checked" '; 
				}
				
				$val = $catObj->category_name;
			$path = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_sale'); 
			ob_start(); 
			require($path); 
			$html .= ob_get_clean(); 
			
			
			}
			$key = JText::_('MOD_PRODUCTCUSTOMS_SALE');
			$filters_html_array[$key] = $html; 
			$expanded_state[$key] = 0; 
			$filter_headers_array[$key] = JText::_('MOD_PRODUCTCUSTOMS_SALE'); 
}

$myconfig = array(); 
if (PCH::checkPerm()) {
	$is_admin = true; 
	$myconfig['admin_url'] = JRoute::_('index.php?option=com_ajax&module=productcustoms&format=raw&nosef=1', false); 
		$myconfig['group_error'] = 'Vyberte názov skupiny !'; 
		$myconfig['groupnodata_error'] = 'Neboli vybrané žiadne filtre !'; 
		$myconfig['getget'] = PCH::getGet(); 
		$myconfig['datacats'] = $datacats; 
		$myconfig['getdatacats'] = PCH::getCatsFromGet(); 
		$myconfig['getcustoms'] = PCH::getCustomsFromGet(); 
		$myconfig['getmf'] = PCH::getManufsFromGet();
		
		$categories = $myconfig['getdatacats']; 
}
else {
	$is_admin = false; 
	$categories = PCH::getCatsFromGet();
}

$myconfig['module_id'] = (int)$module->id; 
$myconfig['has_button'] = (int)$params->get('filter_on_button', 0);
$myconfig['admin'] = (bool)$is_admin; 



$primary_category_id = JRequest::getInt('primary_virtuemart_category_id', 0); 
if (!empty($primary_category_id)) $first_id = $primary_category_id; 
else {
 $first_id = reset($categories); 
}
$default_category = $params->get('default_category', 0); 
if ((empty($first_id)) && (!empty($default_category))) {
	$first_id = (int)$default_category;
}

if (!empty($first_id)) {
 $resetUri = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.(int)$first_id); 
}
else $resetUri = ''; 

$myconfig['reset_url'] = $resetUri;


$format = JRequest::getVar('format', 'html'); 
if ($format === 'html') {
$path = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_js'); 
require($path);

?><div id="module_id_<?php echo $module->id; ?>"><?php
}

$path = JModuleHelper::getLayoutPath('mod_productcustoms'); 
require($path);
if ($format === 'html') {
	?></div>
<?php
}
$myconfig['chosenmethod'] = $params->get('chosenmethod', 'GET'); 

$my_itemid = $params->get('my_itemid', 0); 
if (!empty($my_itemid)) {
	$Itemid = (int)$my_itemid;
}
$myconfig['Itemid'] = $Itemid; 
$primary_category_id = (int)$params->get('default_category', 0); 
if (!empty($my_itemid)) {
	$myconfig['home_url'] = JRoute::_('index.php?Itemid='.$Itemid.'&empty=1'); 
}
else
if (!empty($primary_category_id)) {
	$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search&virtuemart_category_id='.$primary_category_id.'&empty=1'); 
}
else {
	//$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search'); 
	$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search&Itemid='.$Itemid.'&empty=1'); 
}
?><ajaxconfig id="ajaxconfig" data-config="<?php echo htmlentities(json_encode($myconfig)); ?>"></ajaxconfig><?php

}