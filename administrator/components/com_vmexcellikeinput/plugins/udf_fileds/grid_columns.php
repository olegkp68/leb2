<?php
if(isset($udf_fields)){
	if(!empty($udf_fields)){
		
		if($udf_fields){
			foreach($udf_fields as $cf){
				if(!$cf->cf_enabled)		
					continue;
				
				if($cf->cf_editoptions->formater == "filed_update_hook")
					continue;
				
				if($cf->cf_editoptions->formater      == "text"){
					?>
					,{ data: "<?php echo $cf->cf_name ?>"}
					<?php
				}elseif($cf->cf_editoptions->formater      == "inline-html"){
					?>
					,{ data: "<?php echo $cf->cf_name ?>",renderer: HTMLRenderer }
					<?php
				}elseif($cf->cf_editoptions->formater == "content"){
					?>
					,{ data: "<?php echo $cf->cf_name ?>", sortable:false, extern_edit:true, renderer:  sideEditFiledRenderer, editor: sideContentEditor.prototype.extend(), getFN: function(cell){
						editContent( ContentEditorCurrentlyEditing.value ,
									 "<?php echo $cf->cf_title ?>: " +  DG.getDataAtRowProp(cell.row,'product_sku') + ", " + DG.getDataAtRowProp(cell.row,'product_name'),
									 "&CF_CONTENT_GET=<?php echo $cf->cf_name ?>&virtuemart_product_id=" + ContentEditorCurrentlyEditing.value ,
									 function(callback){
										 saveSideContent("&CF_CONTENT_SET=<?php echo $cf->cf_name ?>&virtuemart_product_id=" + jQuery('#content_edit_ifr').attr('virtuemart_product_id'),callback);
									 });
					 }}
					<?php
				}elseif($cf->cf_editoptions->formater == "numeric"){
					if($cf->cf_editoptions->format = "integer"){
						?>
						,{ data: "<?php echo $cf->cf_name ?>", type: "numeric"}
						<?php	
					}else{
						?>
						,{ data: "<?php echo $cf->cf_name ?>", type: "numeric", format: "<?php echo  "0".substr($_num_sample,1,1)."00"; ?>"}
						<?php
					}
				}elseif($cf->cf_editoptions->formater == "date"){
					?>
					,{ data: "<?php echo $cf->cf_name ?>", type: "date", renderer: TextRenderer, dateFormat:"<?php echo $cf->cf_editoptions->format; ?>", defaultDate:"<?php echo $cf->cf_editoptions->default; ?>", correctFormat: true }
					<?php
				}elseif($cf->cf_editoptions->formater == "specified_list"){
					?>
					,{ 
						data: "<?php echo $cf->cf_name ?>" ,  
						editor: CustomSelectEditor.prototype.extend(),
						renderer: CustomSelectRenderer,
						dictionary: <?php echo $cf->cf_name ?>_values,
						select_multiple: false,
						selectOptions: dictionaryToNamevalue(<?php echo $cf->cf_name ?>_values),
						allow_random_input: <?php echo $cf->cf_editoptions->allow_free ? "true" : "false" ; ?>,
						select_multiple: <?php echo $cf->cf_editoptions->multiple ? "true" : "false" ; ?>
					 }
					<?php
				}elseif($cf->cf_editoptions->formater == "checkbox"){
					?>
					,{ 
						data: "<?php echo $cf->cf_name ?>" ,  
						type: "checkbox", 
						renderer: centerCheckboxRenderer
					 }
					<?php
				}elseif($cf->cf_editoptions->formater == "sql"){
					?>
					,{ 
						data: "<?php echo $cf->cf_name ?>" ,  
						editor: CustomSelectEditor.prototype.extend(),
						renderer: CustomSelectRenderer,
						dictionary: <?php echo $cf->cf_name ?>_values,
						select_multiple: false,
						selectOptions: dictionaryToNamevalue(<?php echo $cf->cf_name ?>_values),
						allow_random_input: <?php if(!isset($cf->cf_editoptions->allow_free)) echo "false"; else  echo $cf->cf_editoptions->allow_free ? "true" : "false" ; ?>,
						select_multiple: <?php if(!isset($cf->cf_editoptions->multiple)) echo "false"; else echo $cf->cf_editoptions->multiple ? "true" : "false" ; ?>
					 }
					<?php
				}
				
			
				
			}
		}
	}
}	
?>
