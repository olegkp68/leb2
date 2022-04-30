<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if(!empty($multiple))$type='checkbox';
else $type='radio';
$wrapper_id='cffall_color_btn_set'.$row;

//add buttons toolbar
if(count($values_obj_list)>1 && !empty($multiple)){?>

<div class="cf4all_values_toolbar" style="min-height:3em;">
	<button class="btn" type="button" onclick="jQuery('#<?php echo $wrapper_id?> input').attr('checked','checked');">
	  <?php echo JText::_('JGLOBAL_SELECTION_ALL')?>
	 </button>
	<button class="btn" type="button" onclick="jQuery('#<?php echo $wrapper_id?> input').removeAttr('checked');">
	  <?php echo JText::_('JGLOBAL_SELECTION_NONE')?></button>
</div>
<div class="clr"></div>
<?php
}?>

<div class="cffall_btns_wrapper" id="<?php echo $wrapper_id?>" style="max-height:200px; overflow-y:scroll;">
<?php 
foreach($values_obj_list as $v){
    //styling
    $title='';
    $tooltip='';
    $class='';

    //the value displayed as label within the button

    $label_html='';
    $custom_value_name_multi=explode('|', $v->customsforall_value_name);
    $count_multi_values=count($custom_value_name_multi);
    $width=100/$count_multi_values;
    $customsforall_value_label='';
    if($count_multi_values==1)$customsforall_value_label=$custom_value_name_multi[0];

    //multi-colors
    foreach($custom_value_name_multi as $custom_value_name){
        $color=$filterInput->checkNFormatColor($custom_value_name);
        if(empty($color))continue;
        $ishex=false;
        if(strpos($color, '#')!==false)$ishex=true;
        $label_style='color:#ffffff; text-shadow:-1px 1px #444444; background:'.$color.'; width:'.$width.'%;';
        $label_html.='<div class="cf4all_inner_value" style="'.$label_style.'">'.$customsforall_value_label.'</div>';
    }

    $el_id='cffall_color_bn'.$v->customsforall_value_id.'_'.$row;
    //check selected
    if(in_array($v->customsforall_value_id, $product_value_ids)){
        $selected='checked="checked"';
        //$option_style.='border:2px solid #000000';
    }

    if(!empty($v->customsforall_value_label))$tooltip= $this->languageHandler->__($v, $lang = '', $group = 'label').' ';
    else $tooltip='';

    if(!empty($tooltip)){
        //JHTML::_('behavior.tooltip');//load the tooltips script
        $title=' data-tip="'.$tooltip.'"';
        $class.=' cf4allTip';
    }
    ?>
    <input <?php echo $selected?> type="<?php echo $type?>" id="<?php echo $el_id?>" name="<?php echo $this->_product_paramName?>[<?php echo $row?>]<?php echo $field_prefix?>[customfieldsforall][value][]" value="<?php echo $v->customsforall_value_id?>"/>
	<label for="<?php echo $el_id?>" <?php echo $title?> class="<?php echo $class?>"><?php echo $label_html?></label>
	<?php 
    $selected='';
}
?>
<div style="clear:both"></div>
</div>

<script>
jQuery(function($){

  $(".cf4allTip").hover(function() {
  	var label=$(this).attr("data-tip");
		$( this ).append( $("<span style=\"display:block; position:absolute; margin-top:30px; background:#ffffff; border:1px solid #ccc; padding:5px; color:black;\">"+label+"</span>" ) );
	},
	function() {
		$( this ).find( "span:last" ).remove();
	});

	//insert only once
	if(typeof(cf_rule_inserted)=="undefined"){
		var stylesheet = document.styleSheets[0];
		var selector=new Array();
		var rule=new Array();

		selector[0] = '.cffall_btns_wrapper input[type="radio"]:checked+label,.cffall_btns_wrapper input[type="checkbox"]:checked+label';
		rule[0] = '{border: 2px solid #555555 !important; box-shadow: 0 0 4px rgba(10, 10, 10, 0.8);}'

		selector[1]='.cffall_btns_wrapper label';
		rule[1]='{display:block; float:left; width:56px; border-radius:2px; border:1px solid #ccc; overflow:hidden;}';

		selector[2]='.cffall_btns_wrapper .cf4all_inner_value';
		rule[2]='{height:1em; float:left; padding:6px 0px; text-align:center; }';

		selector[3]='.cffall_btns_wrapper input[type=radio],.cffall_btns_wrapper input[type=checkbox]';
		rule[3]='{display: none; }';

		selector[4]='.cffall_btns_wrapper';
		rule[4]='{clear:both; padding:10px 0px;}';

		if (stylesheet.insertRule) {
			for(var i=0; i<selector.length; i++){
		    	stylesheet.insertRule(selector[i] + rule[i], stylesheet.cssRules.length);
		    }
		} else if (stylesheet.addRule) {
			for(var i=0; i<selector.length; i++){
		    	stylesheet.addRule(selector[i], rule[i], -1);
		    }
		}
		cf_rule_inserted=true;
	}
});
</script>