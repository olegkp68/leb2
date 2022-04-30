<?php
global $udf_fields,$udf_qfields;

if(isset($udf_fields)){
	if(!empty($udf_fields)){
		if($udf_fields){
			foreach($udf_fields as $cf){	
				if(!$cf->cf_enabled)		
					continue;
				
				if($cf->cf_editoptions->formater == "filed_update_hook")
					continue;
				
				try{
				
					if($cf->cf_get_query){
						$p_udf_q = pelm_udf_set_parms_on_query($db, $pr, $cf->cf_get_query ); 
						$db->setQuery($p_udf_q);
						
						if(!isset($cf->cf_editoptions->multiple))
							$cf->cf_editoptions->multiple = false;
						
						if($cf->cf_editoptions->multiple){
							$prod->{$cf->cf_name} = $db->loadColumn();
							if(count($prod->{$cf->cf_name}) == 1){
								if(strpos($prod->{$cf->cf_name}[0],',') !== false)
									$prod->{$cf->cf_name} = array_map("trim",explode(",",$prod->{$cf->cf_name}));
							}
						}else	
							$prod->{$cf->cf_name} = $db->loadResult();
					}else{
						$prod->{$cf->cf_name} = NULL;
					}
					
					if($cf->cf_editoptions->formater == "checkbox"){
						if($cf->cf_editoptions->checked_value || $cf->cf_editoptions->unchecked_value){
							
							if($prod->{$cf->cf_name}){
								if(stripos($prod->{$cf->cf_name}, $cf->cf_editoptions->checked_value ) !== false)
									$prod->{$cf->cf_name} = true;
								else
									$prod->{$cf->cf_name} = false;
							}else{
								$prod->{$cf->cf_name} = false;
							}
 
						}else{
							if($prod->{$cf->cf_name})
								$prod->{$cf->cf_name} = true;
							else
								$prod->{$cf->cf_name} = false;
						}
					}
					
				}catch(Exception $ex){
					$prod->{$cf->cf_name} = NULL;
				}
				
				if(isset($_REQUEST["do_export"])){
					global $SETTINGS;
					if(is_array($prod->{$cf->cf_name}))
						$prod->{$cf->cf_name} = implode( $SETTINGS->cf_val_separator ? $SETTINGS->cf_val_separator : "," ,$prod->{$cf->cf_name});	
					else if(is_object($prod->{$cf->cf_name}))
						$prod->{$cf->cf_name} = json_encode($prod->{$cf->cf_name});
				}
				
				
				
			}
		}
	}
}

?>