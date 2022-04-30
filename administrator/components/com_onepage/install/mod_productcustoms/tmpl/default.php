<?php
defined('_JEXEC') or die('Restricted access');


	
	
	if(!empty($filters_html_array))
{
	
/*
 * view == module is used only when the module is loaded with ajax. 
 * We want only the form to be loaded with ajax requests. 
 * The cf_wrapp_all of the primary module, will be used as the container of the ajax response   
 */
	if($view!='module'){?>
	<div id="cf_wrapp_all_<?php echo $module->id ?>" class="cf_wrapp_all">
	<?php } 
?>
<div id="filter_loader"></div>
<form method="<?php echo $params->get('chosenmethod', 'POST'); ?>" action="<?php 
echo JRoute::_('index.php?option=com_rupsearch&view=search'); 
//echo PCH::getLink($get, array()); 
?>" class="filter_form" id="filter_form" name="filter_form">
	<?php
	/*
	foreach ($get as $key=>$val) {
		if (!is_array($val)) {
			$t1= (int)$val; 
			if (empty($t1)) continue;
			?><input type="hidden" name="<?php echo $key; ?>" id="filter_form_<?php echo $key; ?>" value="<?php echo (int)$val; ?>"/>
		<?php
		}
		else
		foreach ($val as $ind=>$vali) {
		 $t1= (int)$vali; 
		 if ($t1 != $vali) continue;
		 
		 ?><input type="hidden" name="<?php echo $key; ?>[<?php echo (int)$vali; ?>]" value="<?php echo (int)$vali; ?>"/>
		<?php
		}
	}
	*/
	
	?>
	<input type="hidden" name="option" value="com_rupsearch" />
	<input type="hidden" name="view" value="search" />
	<input type="hidden" name="tab" value="<?php echo htmlentities(JRequest::getVar('tab', '')); ?>" id="active_tab" />
	
	
	<div class="allwrap">
	
    <ul class="uk-tab " data-uk-switcher="{connect:'#f_tab_content_<?php echo $module->id; ?>'}" id="filter_tabs">
	<?php 
	$first = true; 
	$active = ''; 
	foreach($filters_html_array as $key=>$flt_html){
	
	if(isset($filter_headers_array[$key])) {
		//toggle state
		if(isset($expanded_state[$key])){
			if($expanded_state[$key] === 1) {
				$state='show';
			}
			else {
				$state='hide';
			}
		} 
		else 
		{
		 $state='show';
		}
		
		//$filters_render_array['scriptProcesses'][]="customFilters.createToggle('".$key."','$state');";
	}
	?>
	<li data-key="<?php echo htmlentities($key); ?>" <?php if ($first) if ($state === 'show') { 
	
	
	$active = $key; echo ' class="uk-active" '; $first = false; } ?>><a href="#"><?php echo $filter_headers_array[$key]?></a></li>
	<?php
	
	
	}
	
	?> 
	</ul>
	<ul id="f_tab_content_<?php echo $module->id; ?>" class="uk-switcher uk-margin">
	<?php
	foreach($filters_html_array as $key=>$flt_html){?> 
	
	<li data-key="<?php echo htmlentities($key); ?>" <?php 
	if ($active === $key) echo ' class="uk-active" '; ?> >
	
	
	
	<div class="cf_flt_wrapper  cf_flt_wrapper_id_<?php echo $module->id?> cf_flt_wrapper_<?php echo $direction; ?>" id="cf_flt_wrapper_<?php echo $key ?>_<?php echo $module->id; ?> " role="presentation">

		<div class="cf_wrapper_inner" id="cf_wrapper_inner_<?php echo $key?>_<?php echo $module->id; ?>" role="tabpanel">
			<?php echo $flt_html?>
		</div>
	</div>
	</li>
	<?php
	}
	?>
	</ul>
	<?php
	unset($flt_html);
	
	
		
		if(empty($filters_html_array['virtuemart_category_id_'.$module->id]) && !empty($filters_render_array['selected_flt']['virtuemart_category_id'])) {
			foreach($filters_render_array['selected_flt']['virtuemart_category_id'] as $key=>$id){?>
				<input type="hidden" name="virtuemart_category_id[<?php echo $key?>]" value="<?php echo $id?>"/>
			<?php 
			  $selectedfilters[$id] = $key; 
			}
		}
		
		
		if(empty($filters_html_array['virtuemart_manufacturer_id_'.$module->id]) && !empty($filters_render_array['selected_flt']['virtuemart_manufacturer_id'])) {
			foreach($filters_render_array['selected_flt']['virtuemart_manufacturer_id'] as $key=>$id){?>
				<input type="hidden" name="virtuemart_manufacturer_id[<?php echo $key?>]" value="<?php echo $id?>" />
			<?php 
			$selectedfilters[$id] = $key; 
			}
		}	
				
		
		//if the keyword search does not exist we have to add it as hidden, because it may added by the search mod
		 if(empty($filters_html_array['q_'.$module->id])) {
		 	$query=!empty($filters_render_array['selected_flt']['q'])?$filters_render_array['selected_flt']['q']:'';?>
		 	<input name="q" type="hidden" value="<?php echo $query;?>"/>
		 <?php 
		 
		 }
		
		
				
		//in case of button add some extra vars to the form
		
		if ($params->get('filter_on_button', false)) {
			?><button class="filter_button btn btn-primary"><?php echo JText::_('MOD_PRODUCTCUSTOMS_BUTTON'); ?></button><?php
		}
		
		?>
		
		
		
	</div>
	
	
	<div class="selected_filters_list">
	  <?php 
	  if (!empty($selectedfilters)) {
		  ?><span><?php echo JText::_('MOD_PRODUCTCUSTOMS_ACTIVE'); ?> </span><br /><?php 
	  }
	  
	  foreach ($selectedfilters as $group_title=>$vals) {
		  if (!empty($vals)) {
			  ?><span class="group_title btn"><?php echo $group_title; ?></span><?php
			  foreach ($vals as $id => $name) {
		  ?><span class="selected_filter btn"><?php echo $name; ?><a href="#" onclick="return mod_productcustoms.removeFilter(event, '<?php echo $id; ?>');">
		   <i class="fas fa-times-circle"></i>
		   </a></span>
		  <?php
			  }
			  ?><br /><?php
			}
			}
			
			
			
			
	if (PCH::checkPerm()) {
		
		?><button class="button btn-primary" onclick="return mod_productcustoms.showControl()" >Zoskupiť vybrané...</button>
		<div class="admin_group" id="admin_control" style="display:none;">
		    <h2>Vybrané filtre</h2>
			<div id="admin_selected_filters" style="font-weight:bold;">&nbsp;</div>
			<input type="text" placeholder="Zoskupený názov" id="group_name" name="group_name" />
		    <button class="button btn-primary" id="admin_do_group_btn" onclick="return mod_productcustoms.doGroup(this)">Vykonať...</button>
			
		</div>
		
	
		
		<?php
		$is_admin = true; 
		
	}
	else {
		$is_admin = false;  
	}
		
		
		
		
	
			  
			  $root = Juri::root(); 
			  //if (substr($root, -1) !== '/') $root .= '/'; 
			 
			  
			  
		
			
		  ?>
	
	</div>
	<?php
	if (!empty($selectedfilters)) {
		?>
	<div class="reset_all_div">
	<?php 
	
	
	
	?>
	<a class="cf_resetAll_link nounderline" rel="nofollow" data-module-id="<?php echo $module->id?>" href="<?php echo JRoute::_($resetUri)?>">
		<span class="cf_resetAll_label flexbox"><i class="fas fa-times-circle"></i><span><?php echo JText::_('MOD_PRODUCTCUSTOMS_CLEAR')?></span></span>
	</a>
	</div>
	<?php } ?>
</form>
<?php 
if($view!='module'){?>
	</div>
	<?php }

	
	
	
	


} 

