<?php
global $udf_fields,$udf_fields_index;
if(!isset($udf_fields_import)){
	$udf_fields_import = array();
}

if(isset($udf_fields)){
	if(!empty($udf_fields)){
		if($udf_fields){
			
			foreach($udf_fields as $cf){
				
				if(!$cf->cf_enabled)		
					continue;
				
				if($cf->cf_editoptions->formater == "filed_update_hook")
					continue;
				
				if($headers[$i] == $cf->cf_name){
					$udf_fields_index[$cf->cf_name] = $i; 
				} 
			}
		}	
	}
}
?>