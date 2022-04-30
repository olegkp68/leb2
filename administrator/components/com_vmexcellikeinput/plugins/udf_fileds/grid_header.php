<?php
if(isset($udf_fields)){
	if(!empty($udf_fields)){
		foreach($udf_fields as $cf){	
			if(!$cf->cf_enabled)		
				continue;
			if($cf->cf_editoptions->formater == "filed_update_hook")
					continue;
		?>
		,"<?php echo pelm_sprintf($cf->cf_title);?>"
		<?php	
			
		}
	}
}
?>