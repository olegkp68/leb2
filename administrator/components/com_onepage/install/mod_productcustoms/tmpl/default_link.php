<?php
defined('_JEXEC')or die;

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 


//tranformse current GET object into URL: 
require(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'getget.php');

if (empty($checked)) $checked = ''; 

if (empty($id) && (!empty($ids))) {
	asort($ids); 
	$id_label = implode('_', $ids); 
	$val = $group_name;
}
else {
	$id_label = $id; 
	 
}

?>
<label for="id_<?php echo $id_label; ?>" id="label_id_<?php echo $id_label; ?>">
<input <?php 
if ((!empty($obj)) && (!empty($obj->isempty))) {
	echo ' disabled="disabled" '; 
}

?> type="checkbox" <?php echo $checked; ?> class="productfilter_selector" value="<?php echo $id_label; ?>" data-name="virtuemart_custom_id" data-value="<?php echo $id_label; ?>"  onclick="return mod_productcustoms.goTo(this);" name="virtuemart_custom_id[]" data-label="<?php echo htmlentities($val); ?>" id="checkbox_<?php echo $id_label; ?>"/>
<a <?php 
if ((!empty($obj)) && (!empty($obj->isempty))) {
	?> disabled="disabled" onclick="return false;" <?php
}
else {
	?> onclick="return mod_productcustoms.goTo(this);" <?php
}
?> class="filter_link <?php 
if ((!empty($obj)) && (!empty($obj->isempty))) {
	 echo 'disabled'; 
}
	?>" data-checkbox="<?php echo 'checkbox_'.$id_label; ?>" href="<?php 
echo '#'; 
//echo PCH::getLink($get, array('virtuemart_custom_id'=>$id_label)); 
?>" data-value="<?php echo $id_label; ?>" data-name="virtuemart_custom_id" rel="nofollow"  id="id_<?php echo $id_label; ?>" data-label="<?php echo htmlentities($val); ?>"><?php echo htmlentities($val).'&nbsp;'; 

 if ($obj->custom_desc) {
				JHtml::_('bootstrap.tooltip');
				echo JHTML::tooltip($obj->custom_desc, vmText::_($obj->custom_title), 'tooltip.png');
				}
?></a></label>

<?php

if (PCH::checkPerm()) {
	   if ((!empty($obj)) && (!empty($obj->group_names))) {
		    echo ' ('; 
		    foreach ($obj->group_names as $group_name) {
				?><span style="font-weight: bold; "><a href="#" onclick="return mod_productcustoms.removeGroupName(this)" data-value="<?php 
				$data = array(); 
				$data['id'] = $id_label; 
				$data['group_name'] = $group_name; 
				$data['val'] = $val; 
				$data['type'] = 'virtuemart_custom_id'; 
				
				echo htmlentities(json_encode($data)); 
				?>" alt="<?php echo htmlentities($group_name); ?>"> X </a><?php echo $group_name; ?></span><?php
			}
			echo ')'; 
	   }
	}


