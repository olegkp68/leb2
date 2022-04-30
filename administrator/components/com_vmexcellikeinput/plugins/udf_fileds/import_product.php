<?php


global $udf_fields,$udf_fields_index;

if(isset($udf_fields_index)){

	
	if(!empty($udf_fields_index)){
		
		if(!$pr){
			if(PLEM_VM_RUN > 2)
				  $pr = $productModel->getProduct($id,false,true,false);
			else	  
				  $pr = $productModel->getProduct($id,false,false,false);
		}
		
		if(isset($udf_fields)){
			if(!empty($udf_fields)){
			
				foreach($udf_fields as $cf){
					if(!$cf->cf_enabled)		
						continue;
					
					if($cf->cf_editoptions->formater == "filed_update_hook")
						continue;
					
					
					if(isset($udf_fields_index[$cf->cf_name])){
						global $SETTINGS;
						$cf_index = $udf_fields_index[$cf->cf_name];
					
						$set_val = $data[$cf_index];
					
					
						if(!is_array($set_val))
							$set_val = explode($SETTINGS->cf_val_separator ? $SETTINGS->cf_val_separator : ",",$set_val);
					
						if($cf->cf_editoptions->formater == "specified_list"){
							$dirty_cf = false;
							if($cf->cf_editoptions->allow_free && $cf->cf_possible_values){
							   for($J = 0; $J < count($set_val) ; $J++){
								 $val =  $set_val[$J]; 
								 if($val){
									$val = explode(":",$val);
									$val_key = $val[0];
									
									if(!isset($cf->cf_possible_values[$val_key])){
									 $cf->cf_editoptions->specified_list .= ",".$set_val[$J];
									 $cf->cf_editoptions->specified_list = str_replace(",,",",",$cf->cf_editoptions->specified_list);
									 $dirty_cf = true;
									 $cf->cf_possible_values[$val_key] = isset($val[1]) ? $val[1] : $val[0];
									}
								 }	
							   }
							}
							if($dirty_cf){
								
								$SETTINGS->pelmplugin_custom_fiels = addslashes(json_encode($udf_fields));
								SaveSettings($db, $SETTINGS);
							}
						}elseif($cf->cf_editoptions->formater == "sql"){
							if($cf->cf_editoptions->allow_free && $cf->cf_possible_values){
								
								for($J = 0; $J < count($set_val) ; $J++){
									$val =  $set_val[$J];
									if($val){
										$val = explode(":",$val);
										$val_key = $val[0];
										if(!isset($cf->cf_possible_values[$val_key])){
											
											$vname = isset($val[1]) ? $val[1] : $val[0];
											$skip = false;
											
											foreach($cf->cf_possible_values as $xvalue => $xname){
												if(strtolower($xname) == strtolower($vname)){
												   $set_val[$J] = $xvalue;	
												   $skip = true;
												   break;
												}
											}
											
											if(!$skip){
												if(isset($cf->cf_editoptions->sql_new_option)){
													if(trim($cf->cf_editoptions->sql_new_option)){
														$nopt_value = $val[0];
														$nopt_name  = isset($val[1]) ? $val[1] : $val[0];
														
														$sql_lines = explode(";",$cf->cf_editoptions->sql_new_option);
														foreach($sql_lines as $sql_line){
															if(trim($sql_line)){
																try{
																	$nopt_sql   = pelm_udf_set_parms_on_query($db, $pr, $sql_line,$nopt_name, $nopt_value);
																	$db->setQuery($nopt_sql);
																	$db->query();
																	
																	if($cf->cf_editoptions->sql_new_option_val_is_id){
																		$set_val[$J] = $db->insertid();
																		$cf->cf_possible_values[$set_val[$J]] = $nopt_name;
																	}else{
																		$cf->cf_possible_values[$nopt_value] = $nopt_name;
																	}
																}catch(Exception $nopt_ex){
																	//
																}
															}	
														}	
													}
												}
											}
										}
									}
								}
							}
						}
						
						
						
						if($cf->cf_editoptions->formater == "checkbox"){
							if(is_array($set_val))
								$set_val = implode(",",$set_val);
							$set_val = pelm_udf_checkbox_value($cf, $set_val, NULL);
						}
						
						$uqueries = explode(";",$cf->cf_update_query);
						foreach($uqueries as $uq){
							try{
								$uq = trim($uq);
								
								if($uq){
									if( substr($uq,0,1) == "*"){
										$uq = substr($uq,1);
										foreach($set_val as $sub_val){
											$sv_uq = pelm_udf_set_parms_on_query($db, $pr, $uq, NULL, $sub_val);
											$db->setQuery($sv_uq);
											$db->query();
										}
									}else{
										$uq = pelm_udf_set_parms_on_query($db, $pr, $uq, NULL, $set_val);
										$db->setQuery($uq);
										$db->query();
									}
								}
								
							}catch(Exception $udfu_ex){
								$res_item->error = $udfu_ex->getMessage();	
								$res_item->success = false;
							}
						}
					}
				}
				
				foreach($udf_fields as $cf){
					if(!$cf->cf_enabled)		
						continue;
					if($cf->cf_editoptions->formater == "filed_update_hook"){
						if(isset($cf->cf_editoptions->hook_to_fileds)){
							if(!empty($cf->cf_editoptions->hook_to_fileds)){
								if(is_string($cf->cf_editoptions->hook_to_fileds))
									$cf->cf_editoptions->hook_to_fileds = explode(",",$cf->cf_editoptions->hook_to_fileds);
								
								foreach($cf->cf_editoptions->hook_to_fileds as $fhook){
									if($fhook){
										if($fhook == "product_tax") $fhook = "product_tax_id";
										else if($fhook == "product_discount") $fhook = "product_discount_id";
										else if($fhook == "categories_names") $fhook = "categories";
										else if(strpos($fhook,"_cf")) {$fhook = explode("_cf", $fhook); $fhook = "custom_field_" . end($fhook );};
										
										if(in_array($fhook, $headers )){
											$index = array_search($fhook,$headers);
											if($index !== false){
												$uqueries = explode(";",$cf->cf_update_query);
												foreach($uqueries as $uq){
													if(!trim($uq))
														continue;
													$hook_q = pelm_udf_set_parms_on_query($db, $pr, $uq, $fhook , $data[$index]);
													$db->setQuery($hook_q);
													$db->query();	
												}
											}
										}
									}
								}
								
							}
						}
					}
				}
			
			
			}
		}
		
		
		
		
		
		
	}
}

?>
