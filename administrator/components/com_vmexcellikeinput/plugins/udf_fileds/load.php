<?php
global $udf_fields,$udf_qfields;


$udf_fields  = NULL;
$udf_qfields = array(
					 '@virtuemart_product_id'
					,'@virtuemart_vendor_id'
					,'@product_parent_id'
					,'@product_sku'
					,'@product_gtin'
					,'@product_mpn'
					,'@product_name'
					,'@slug'
					,'@product_desc'
					,'@product_weight'
					,'@product_weight_uom'
					,'@product_length'
					,'@product_width'
					,'@product_height'
					,'@product_lwh_uom'
					,'@product_url'
					,'@product_in_stock'
					,'@product_ordered'
					,'@low_stock_notification'
					,'@product_available_date'
					,'@product_availability'
					,'@product_special'
					,'@product_sales'
					,'@product_unit'
					,'@product_packaging'
					,'@product_params'
					,'@intnotes'
					,'@customtitle'
					,'@metadesc'
					,'@metakey'
					,'@metarobot'
					,'@metaauthor'
					,'@layout'
					,'@published'
					,'@min_order_level'
					,'@max_order_level'
					,'@step_order_level'
					,'@product_box'
					,'@virtuemart_media_id'
					,'@virtuemart_category_id'
					,'@virtuemart_manufacturer_id'
					,'@ordering'
					,'@category_name'
					,'@product_tax_id'
					,'@amount'
					,'@quantity'
					,'@product_template'
					,'@link'
					,'@value'
					,'@valarr'
					,'@name'
					,'@user_id'
					,'$'.'vm_lang'
				);

function pelm_udf_checkbox_value(&$cf, $checked, $value){
	
	if($value){
		
		if($cf->cf_editoptions->checked_value){
			if(stripos($cf->cf_editoptions->checked_value,$value))
				return $cf->cf_editoptions->checked_value;
			else
				return $cf->cf_editoptions->unchecked_value;
			
		}else if($cf->cf_editoptions->unchecked_value){
			if(stripos($cf->cf_editoptions->unchecked_value,$value))
				return $cf->cf_editoptions->unchecked_value;
		}
		
		if($value)
			return true;
		else
			return false;
		
	}else{
		if($checked){
			if($cf->cf_editoptions->checked_value)
				return $cf->cf_editoptions->checked_value;
			else
				return true;
		}else{
		    if($cf->cf_editoptions->unchecked_value)
				return $cf->cf_editoptions->unchecked_value;
			else
				return false;	
		}
	}
	
}

function dbquote($str){
	return "'" . $str . "'";	
}				
				
function pelm_udf_set_parms_on_query(&$db,&$pr,$query_string, $name = NULL, $value = NULL){
	global $vm_lang;
	global $udf_qfields;
	global $user;
	
	if($value)
		if(is_array($value))
			$value = implode(",",$value);
	
	
	if(strpos($query_string, "@") === false && strpos($query_string, '$vm_lang') === false)
		return $query_string;
	
	if(isset($udf_qfields)){
		if(!empty($udf_qfields)){
			foreach($udf_qfields as $field){
				$real_field       = substr($field, 1);
				
				if($real_field == "valarr")
					$fields_replace[] = implode(",",array_map("dbquote",explode(",",$value)));
				else if($real_field == "value")
					$fields_replace[] = $db->quote($value);
				else if($real_field == "name")	
					$fields_replace[] = $db->quote($name);
				else if($real_field == "vm_lang")	
					$fields_replace[] = $vm_lang;
				else if($real_field == "user_id")	
					$fields_replace[] = $user->id;
				else {
					if($pr ){
						if(isset($pr->{$real_field})){
							$fld_val = $pr->{$real_field};
							if(is_array($fld_val)){
								$fld_val = implode(",",$fld_val);
							}
							$fields_replace[] = $db->quote($fld_val);
						}else
							$fields_replace[] = 'NULL';
					}else
						$fields_replace[] = '';
				}
			}
		}
	}
	
	$return = str_ireplace($udf_qfields, $fields_replace, $query_string);
	
	return $return;
	
}

if(!isset($SETTINGS->pelmplugin_custom_fiels))
	$SETTINGS->pelmplugin_custom_fiels = "[]";

if(!isset($SETTINGS->pelmplugin_custom_fiels_api_key))
	$SETTINGS->pelmplugin_custom_fiels_api_key = "";


if(isset($_REQUEST['pelmplugin_custom_fiels_save'])){
	if($_REQUEST['pelmplugin_custom_fiels_save']){
		$json = file_get_contents('php://input');
		$SETTINGS->pelmplugin_custom_fiels = json_decode($json);
		
		if($SETTINGS->pelmplugin_custom_fiels){
			foreach($SETTINGS->pelmplugin_custom_fiels as $cf){
				if(isset($cf->cf_install_sql)){
					if(!empty($cf->cf_install_sql)){
						foreach($cf->cf_install_sql as $InstSQL){
							try{
								if($InstSQL){
								    $db->setQuery($InstSQL);	
									$db->query();
								}
							}catch(Exception $inst_ex){
								
							}
						}
					}
				}
			}
		}
		SaveSettings($db, $SETTINGS);
		echo '{"saved":"ok"}';
		die;
		return;
	}
}

if(isset($_REQUEST['pelmplugin_custom_fiels_save_api_key'])){
	if($_REQUEST['pelmplugin_custom_fiels_save_api_key']){
		$SETTINGS->pelmplugin_custom_fiels_api_key = $_REQUEST['pelmplugin_custom_fiels_save_api_key'];
		SaveSettings($db, $SETTINGS);
		echo '{"saved":"ok"}';
		die;
		return;
	}
}

if(isset($SETTINGS->pelmplugin_custom_fiels)){
  	//$udf_fields = json_decode( stripslashes( $SETTINGS->pelmplugin_custom_fiels));
	if(is_string($SETTINGS->pelmplugin_custom_fiels)){
		$SETTINGS->pelmplugin_custom_fiels = json_decode($SETTINGS->pelmplugin_custom_fiels);
	}
	
	$udf_fields = $SETTINGS->pelmplugin_custom_fiels;
	
	if(isset($udf_fields)){
		if(!empty($udf_fields)){
			foreach($udf_fields as $cf){
				$cf->cf_name = "udf_".sanitize_title($cf->cf_title);
				
				try{
				
					if($cf->cf_editoptions->formater == "specified_list"){
						$cf->cf_possible_values = array();
						
						$sl_vals = explode(",",$cf->cf_editoptions->specified_list);
						
						foreach($sl_vals as $sval){
							$sval = trim($sval);
							if(strpos($sval,":") === false){
								$cf->cf_possible_values[$sval] = $sval; 		
							}else{
								$sval = explode(":", $sval);
								$sval[0] = trim($sval[0]);
								$sval[1] = trim($sval[1]);
								$cf->cf_possible_values[$sval[0]] = $sval[1];
							}	
						}
					}elseif($cf->cf_editoptions->formater == "sql"){
						$cf->cf_possible_values = array();
						
						try{
							$nullprr = NULL;
							$pv_q = pelm_udf_set_parms_on_query($db,$nullprr, $cf->cf_editoptions->sql_get_options);
							
							$db->setQuery($pv_q);
							$rows = $db->loadAssocList();
							
							if(count($rows)){
								$vcol = "";
								$ncol = "";
								
								if(isset($rows[0]["name"]) && isset($rows[0]["value"])){
									$vcol = "value";
									$ncol = "name";
								}elseif(count($rows[0]) == 1){
									$cols_names = array_keys($rows[0]);
									$vcol = $cols_names[0];
									$ncol = $cols_names[0];
								}else{
									$cols_names = array_keys($rows[0]);
									$vcol = $cols_names[0];
									$ncol = $cols_names[1];
								}
								
								if($vcol && $ncol){
									foreach($rows as $r){
										$cf->cf_possible_values[$r[$vcol]] = $r[$ncol];
									}
								}
							}
						}catch(Exception $pv_ex){
							
						}
					}
					
					if(isset($_REQUEST["CF_CONTENT_GET"]) || isset($_REQUEST["CF_CONTENT_SET"])){
						
						$prop = isset($_REQUEST["CF_CONTENT_GET"]) ? $_REQUEST["CF_CONTENT_GET"] : $_REQUEST["CF_CONTENT_SET"];
						
						if($prop == $cf->cf_name){
							$pr_id = NULL; 
							if(isset($_REQUEST["virtuemart_product_id"]))
								$pr_id = $_REQUEST["virtuemart_product_id"];
						
							if($pr_id){
								 $pr = null;
								 if(PLEM_VM_RUN > 2)
									$pr = $productModel->getProduct($pr_id,false,true,false); 
								 else
									$pr = $productModel->getProduct($pr_id,false,false,false);
								
								if(isset($_REQUEST["CF_CONTENT_GET"])){
									if($cf->cf_get_query){
										$db->setQuery(pelm_udf_set_parms_on_query($db,$pr, $cf->cf_get_query)); 
										$data = new stdClass;
										$data->content = $db->loadResult();
										echo json_encode($data);
									}else{
										echo '{"content":""}'; 
									}
								}elseif(isset($_REQUEST["CF_CONTENT_SET"])){
									$json    = file_get_contents('php://input');
									$obj = json_decode($json);
									$content = $obj->content;
									
									if($cf->cf_update_query){
										$cnt_u_q = pelm_udf_set_parms_on_query($db,$pr, $cf->cf_update_query,NULL,$content);
										$db->setQuery($cnt_u_q); 
										$db->query();
									}
									
									$data = new stdClass;
									$data->content = $obj->content;
									echo json_encode($data);
								}
								break;
							}
						}
					}
				}catch(Exception $ludf){
					
				}
			}
		}
	}
}

if(isset($_REQUEST["CF_CONTENT_GET"]) || isset($_REQUEST["CF_CONTENT_SET"])){
	die;
	return;
}