<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

$display_id = $row . '_' . $virtuemart_custom_id;
$ordering = 0;
$counter = 0;
$width_total = 72;
$multi_lingual = count($languages)>1?true:false;
?>

<ul style="clear: both; list-style: none; padding-right:1em; margin:0px;" id="cf_values_wrapper<?php echo $display_id?>" class="sortable cf_values_wrapper">
<?php
if (! empty($existing_values)) {
    foreach ($existing_values as $obj) {
        $color_pallette_counter = 0;
        $pk = $obj->customsforall_value_id;
        ?>
        <li id="customsforall_value_li_<?php echo $counter ?>" style="min-width: 300px;">
		<div class="removable" style="padding: 5px 10px; background: #ededed; margin-bottom: 1em;">

		<?php
		//colors
        if (! empty($custom_params) && $data_type == 'color_hex') {
            $custom_values = explode('|', $obj->customsforall_value_name);
            $no_values = count($custom_values);
            $width = $width_total / $no_values;
            ?>

            <div class="cf4all-color-value-wrapper" id="color-value-wrapper-<?php echo $counter ?>"
				style="width: 300px;">
            <?php
            foreach ($custom_values as $key => $cf):?>
               <input style="width:<?php echo $width ?>%;" type="text" name="cf_val[<?php echo $counter?>][customsforall_value_name][<?php echo $key?>]"
               value="<?php echo $cf ?>" class="<?php echo $class ?>"/>
            <?php endforeach;?>
            </div>

			<div class="cf4all-number-values-wrapper jgrid" id="number-values-wrapper-<?php echo $counter?>">
				<input type="text" name="cf4all-number-values_<?php echo $counter?>"
					id="cf4all-number-values_<?php echo $counter?>"
					value="<?php echo $no_values ?>" size="2" disabled />
					<span style="display: inline-block; cursor: pointer; min-width: 13px;"
					class="cf4all-incdec uparrow"><i class="icon-arrow-up">&nbsp&nbsp&nbsp</i></span>
				<span style="display: inline-block; cursor: pointer; min-width: 13px;"
					class="cf4all-incdec downarrow"><i class="icon-arrow-down">&nbsp&nbsp&nbsp</i></span>
			</div>
            <?php
            $field_name = '[customsforall_value_label]';
            $fieldNamePrefix = 'cf_val['.$counter.']';

            if($multi_lingual) {
                foreach ($languages as $language) {
                    if(!isset($language->default)) {
                        $fieldNamePrefixLang = $fieldNamePrefix.'['.$language->lang_code.']';
                    }
                    else {
                        $fieldNamePrefixLang = $fieldNamePrefix;
                    }
                    $field_nameLang = $fieldNamePrefixLang.$field_name;
                    $flag_image = $language->image;
                    $translation = $languageHandler->__($obj, $language->lang_code, $group ='label', $withId=true);
                    $translation_value = $translation['string'];
                    $translation_id = $translation['id'];
                    ?>
                    <div class="input-prepend" style="width: 75%;">
				        <span class="cf-add-on"><img src="<?php echo $flag_image?>" alt="<?php echo $language->lang_code?>" /></span>
				        <input style="width: 72%;" type="text" name="<?php echo $field_nameLang?>" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_LABEL')?>" value="<?php echo $translation_value?>" class="cf_value_label" />
				        <input type="hidden" name="<?php echo $fieldNamePrefix?>[id]" value="<?php echo $translation_id ?>" />
			         </div>
                    <?php
                }
            }
           else {
            ?>
            <input style="width: 72%;" type="text" name="cf_val[<?php echo $counter?>]<?php echo $field_name?>" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_LABEL')?>"
				value="<?php echo $obj->customsforall_value_label?>" class="cf_value_label" />
        <?php
          }
        } else {
            $field_name = '[customsforall_value_name]';
            $fieldNamePrefix = 'cf_val['.$counter.']';

            //display multi-lingual only for strings
            if($data_type == 'string' && $multi_lingual) {
                foreach ($languages as $language) {
                    if(!isset($language->default)) {
                        $fieldNamePrefixLang = $fieldNamePrefix.'['.$language->lang_code.']';
                    } else {
                        $fieldNamePrefixLang = $fieldNamePrefix;
                    }
                    $field_nameLang = $fieldNamePrefixLang. $field_name;
                    $flag_image = $language->image;
                    $translation = $languageHandler->__($obj, $language->lang_code, $group ='value', $withId=true);
                    $translation_value = $translation['string'];
                    $translation_id = $translation['id'];
                    ?>
                 <div class="input-prepend" style="width: 75%;">
				    <span class="cf-add-on"><img src="<?php echo $flag_image?>" alt="<?php echo $language->lang_code?>" /></span>
				    <input type="text" style="width: 94%" name="<?php echo $field_nameLang?>" value="<?php echo $translation_value; ?>" class="<?php echo $class?>" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_VALUE')?>"/>
				    <input type="hidden" name="<?php echo $fieldNamePrefixLang?>[id]" value="<?php echo $translation_id ?>" />
			     </div>
                    <?php
                }
            }
            //No string or no multi-lingual
            else {?>
                <input style="width: 75%;" type="text" name="cf_val[<?php echo $counter?>]<?php echo $field_name?>" value="<?php echo $obj->customsforall_value_name?>"
				class="<?php echo $class ?>" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_VALUE')?>"/>
                <?php
            }
        }?>
                <input type="hidden" name="cf_val[<?php echo $counter?>][customsforall_value_id]" value="<?php echo $obj->customsforall_value_id ?>" />
                <span class="vmicon vmicon-16-move"></span>
                <a href="#" class="vmicon vmicon-16-remove customsforall_delete_btn" data-row_id="<?php echo $counter?>" title="<?php echo JText::_('PLG_CUSTOMSFORALL_DELETE')?>"></a>
		</div>
	</li>
		<?php
        $ordering = $obj->ordering;
        $counter ++;
    }
}
?>
</ul>

<button class="btn" id="cf_newvalue_btn<?php echo $display_id?>" type="button"><?php echo JText::_('PLG_CUSTOMSFORALL_NEW_VALUE')?></button>
<script type="text/javascript">
jQuery(function($){
	$(".cfield-chosen-select").chosen({width:"200px",display_selected_options:false});
	is_added_<?php echo $display_id?>=false;
	$("#cf_values_wrapper<?php echo $display_id?>").delegate("a.customsforall_delete_btn","click",function(){
		$(this).parents("li").remove();
		is_added_<?php echo $display_id?>=false;
		return false;
	});

	var counter=<?php echo $counter?>;
	$("#cf_newvalue_btn<?php echo $display_id?>").click(function(){
<?php
if (! $is_custom_view && (boolean) $single_entry) {?>
    if(!is_added_<?php echo $display_id?>){
    	<?php
    }
    if ($data_type != 'color_hex') {?>
        var elem_appended='\
            <li id="customsforall_value_li_'+counter+'" style="min-width:300px;">\
             <div class="removable" style="padding:5px 10px; background:#ededed; margin-bottom:1em;">';
             <?php
             if($data_type == 'string' && $multi_lingual) {
                 foreach ($languages as $language) {
                        if(isset($language->default)) {
                            ?>
                            fieldNamePrefix = '<?php echo $fieldname?>['+counter+']';
                            <?php
                        }else {
                            ?>
                            fieldNamePrefix = '<?php echo $fieldname?>['+counter+'][<?php echo $language->lang_code?>]';
                            <?php
                        }
                        $flag_image = $language->image;
                        ?>
                        elem_appended+='\
                        <div class="input-prepend" style="width:75%;">\
                        <span class="cf-add-on"><img src="<?php echo $flag_image?>" alt="<?php echo $language->lang_code?>"/></span>\
                        <input type="text" style="width:94%" name="'+fieldNamePrefix+'[customsforall_value_name]" value="" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_VALUE')?>" class="<?php echo $class?>"/>\
                        </div>';
                        <?php
                    }
                 }
                 //is not multi-lingual or not string
                 else {?>
                     elem_appended+='<input style="width:75%;" type="text" name="<?php echo $fieldname?>['+counter+'][customsforall_value_name]" value="" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_VALUE')?>" class="<?php echo $class ?>"/>';

              <?php }?>
             elem_appended+='\
                 <input type="hidden" name="" value="0"/>\
                 <span class="vmicon vmicon-16-move"></span>\
                 <a href="#" class="vmicon vmicon-16-remove customsforall_delete_btn" alt="<?php echo JText::_('PLG_CUSTOMSFORALL_DELETE')?>"></a>\
                </div>\
                </li>';
        <?php }
        else {?>
        var elem_appended='\
            <li id="customsforall_value_li_'+counter+'" style="min-width:300px;">\
            <div class="removable" style="padding:5px 10px; background:#ededed">\
            <div class="cf4all-color-value-wrapper" id="color-value-wrapper-'+counter+'" style="width:300px;">\
            <input style="width:<?php echo $width_total?>%;" type="text" name="<?php echo $fieldname?>['+counter+'][customsforall_value_name][]" value="" class="<?php echo $class?>" id="cf_value_input<?php echo $display_id ?>'+counter+'"/>\
            </div>\
            <div class="cf4all-number-values-wrapper jgrid" id="number-values-wrapper-'+counter+'">\
            <input type="text" name="cf4all-number-values_'+counter+'" id="cf4all-number-values_'+counter+'" value="1" size="2" disabled/>\
            <span style="display:inline-block; cursor:pointer; min-width:13px;" class="cf4all-incdec uparrow">\
            <i class="icon-arrow-up">&nbsp&nbsp&nbsp</i></span>\
            <span style="display:inline-block; cursor:pointer; width:13px;" class="cf4all-incdec downarrow">\
            <i class="icon-arrow-down">&nbsp&nbsp&nbsp</i></span>\
            </div>';
            <?php
            if($multi_lingual) {

            foreach ($languages as $language) {
                if (isset($language->default)) {
                    ?>
                            fieldNamePrefix = '<?php echo $fieldname?>['+counter+']';
                            <?php
                } else {
                    ?>
                            fieldNamePrefix = '<?php echo $fieldname?>['+counter+'][<?php echo $language->lang_code?>]';
                            <?php
                }
                $flag_image = $language->image;
                ?>
                            elem_appended+='\
                            <div class="input-prepend" style="width:75%;">\
                            <span class="cf-add-on"><img src="<?php echo $flag_image?>" alt="<?php echo $language->lang_code?>"/></span>\
                            <input type="text" style="width:94%" name="'+fieldNamePrefix+'[customsforall_value_label]" value="" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_LABEL')?>" class="cf_value_label" id="cf_label_input<?php echo $display_id ?>'+counter+'"/>\
                            </div>';
                            <?php
            }
        }else {?>
            elem_appended+='\
            <input style="width:72%;" type="text" name="<?php echo $fieldname?>['+counter+'][customsforall_value_label]" value="" placeholder="<?php echo JText::_('PLG_CUSTOMSFORALL_NEW_LABEL')?>" class="cf_value_label" id="cf_label_input<?php echo $display_id ?>'+counter+'"/>';
       <?php }?>

            elem_appended+='\
            <input type="hidden" name="<?php echo $fieldname?>['+counter+'][customsforall_value_id]" value="0"/>\
            <span class="vmicon vmicon-16-move"></span>\
            <a href="#" class="vmicon vmicon-16-remove customsforall_delete_btn" alt="<?php echo JText::_('PLG_CUSTOMSFORALL_DELETE')?>"></a>\
            </div>\
            </li>';
    <?php }?>

$("#cf_values_wrapper<?php echo $display_id?>").append(elem_appended);
		is_added_<?php echo $display_id?>=true;
		mypicker="myPicker"+counter;
<?php
if ($data_type == 'color_hex') {?>
    var  mypicker= new jscolor.color(document.getElementById('cf_value_input<?php echo $display_id?>'+counter), {});
<?php }?>
counter++;
<?php
if (! $is_custom_view && $single_entry) {?>
}
<?php }?>
return false
	});

	jQuery("#cf_values_wrapper<?php echo $display_id?>").sortable({handle: ".vmicon-16-move"});
<?php
if ($data_type == 'color_hex') {?>
	$("#cf_values_wrapper<?php echo $display_id?>").delegate("span.cf4all-incdec", "click",function(){
		var $button = $(this);
		var counter=($button.parent().attr("id").match(/\d+$/));
		var oldValue = parseInt($button.parent().find("input").val());

		if($button.hasClass("uparrow")){
			var newValue=oldValue+1;
			var elem_appended='\
				<input style="width:<?php echo $width_total?>%;" type="text" name="<?php echo $fieldname?>['+counter+'][customsforall_value_name][]" value="" \
				class="<?php echo $class ?>" id="cf_value_input<?php echo $display_id?>'+counter+'_'+newValue+'"/>';
			jQuery("#color-value-wrapper-"+counter).append(elem_appended);
			var  mypicker= new jscolor.color(document.getElementById('cf_value_input<?php echo $display_id?>'+counter+'_'+newValue), {});
		}else if(oldValue>1){
			var newValue=oldValue-1;
			jQuery("#color-value-wrapper-"+counter+" .color:last-child").remove();

		}else{newValue=1}
		$button.parent().find("input").val(newValue);
		return true;
	});
<?php }?>

});
</script>
