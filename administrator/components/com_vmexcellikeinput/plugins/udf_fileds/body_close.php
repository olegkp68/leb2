<div id="cf-settings-panel" class="settings-panel pelmplugin_custom_fiels" style="display:none;">
<form style="text-align:center;" method="post" class="plem-form" >
	<h1>User defined fields</h1>
	<br/>
	<table cellpadding="0" cellspacing="0" class="table" style="width:100%;">
	   <thead>
		<tr style="text-align: center;">
		  <td style="width:20px;">No.</td>
		  <td style="width:60px;" >Enabled</td>
		  <td>Configuration</td>			  
		  <td>Edit options</td>
		</tr>
		</thead>
		<tbody id="cf_list">
			
		</tbody>
		<tfoot>
		   <tr>
		    <td colspan="4">
			<hr/>
			<button id="cmdAddCF">Add New Field Definition</button>
			<label>Well known fields:</label>
			<select id="cmbWellKnown" >
			  
			</select>
			<button id="cmdAddWKCF">Add Well Known Field</button>
			</td>
		   </tr>	
		</tfoot>
	</table>
	
	
	<div class="instructions">
	
			<p>To use well known product fields as parameters add '@' as prefix to filed name like:</p>
			<p style="background:yellow; border:1px solid red;color:black;padding:4px;">
				SELECT myVal from #_myTable WHERE virtuemart_product_id=@virtuemart_product_id
			</p>
			<br/>
			<h3 style="font-style:italic" >Available get and update/insert query parameters:<h3>
			<p>
			@virtuemart_product_id, @virtuemart_vendor_id, @product_parent_id, @product_sku, @product_gtin, @product_mpn, @product_name, @slug, @product_s_desc ,@product_weight, @product_weight_uom, @product_length, @product_width, @product_height, @product_lwh_uom, @product_url, @product_in_stock, @product_ordered, @low_stock_notification, @product_available_date, @product_availability, @product_special, @product_sales, @product_unit, @product_packaging, @product_params, @intnotes, @customtitle, @metadesc, @metakey, @metarobot, @metaauthor, @layout, @published, @min_order_level, @max_order_level, @step_order_level, @virtuemart_media_id, @virtuemart_category_id, @ordering, @category_name, @product_tax_id, @amount, @quantity, @product_template, @link, @user_id
			</p>
			<p>
			Use $vm_lang for language suffix (e.g. en_gb)
			</p>
			<br/>
			<p style="color:cyan;">If you are developer of component that works in symbioses with our component you can submit fields definition to us so your users can configure them easy</p>
			<p style="color:cyan;">Among visible fields you can provide installation script. For example if you need to create MY SQL function or create table.</p>
			<br/>
			<br/>
			<a class="integrate" href="mailto:support@holest.com?subject=VmExcelLike - Add CF SUPPORT">Send us your filed definitions to support@holest.com &rarr;</a> </br>
			<br/>
			<a target="_blank" href="https://holest.com/index.php/holest-outsourcing/joomla-wordpress/virtuemart-excel-like-product-manager-udf-plugin.html">Go to UDF plugin page &rarr;</a>
			<br/>
	</div>
	<form id="operationFRM" method="POST">

	</form>
	<script type="text/javascript">
	var cf_settings =  null;
	</script>
	
	<script type="text/javascript">
	try{
		cf_settings = <?php echo json_encode($SETTINGS->pelmplugin_custom_fiels) ?>;
	}catch(e){
		
	}	
	</script>
	
	<script type="text/javascript">
	if(!cf_settings)
		cf_settings = [];
	
	var cf_wellknown = [{
						 "cf_install_sql":[],	
						 "cf_enabled":true,
						 "cf_title":"Create Date",
						 "cf_get_query":"SELECT created_on FROM #__virtuemart_products WHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_update_query":"UPDATE #__virtuemart_products\nSET\n  created_on = @value\nWHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_editoptions":{
										   "formater":"date",
										   "format":"YYYY-MM-DD HH:mm:ss",
										   "default":"0000-00-00 00:00:00"
										  }
						},
						{
						 "cf_install_sql":[],	
						 "cf_enabled":true,
						 "cf_title":"Modify Date",
						 "cf_get_query":"SELECT modified_on FROM #__virtuemart_products WHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_update_query":"UPDATE #__virtuemart_products\nSET\n  modified_on = @value\nWHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_editoptions":{
										   "formater":"date",
										   "format":"YYYY-MM-DD HH:mm:ss",
										   "default":"0000-00-00 00:00:00"
										  }
						},
						{
						 "cf_install_sql":[],	
						 "cf_enabled":true,
						 "cf_title":"Availability Date",
						 "cf_get_query":"SELECT product_available_date FROM #__virtuemart_products WHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_update_query":"UPDATE #__virtuemart_products\nSET\n  product_available_date = @value\nWHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_editoptions":{
										   "formater":"date",
										   "format":"YYYY-MM-DD HH:mm:ss",
										   "default":"0000-00-00 00:00:00"
										  }
						},
						{
						 "cf_install_sql":[],	
						 "cf_enabled":true,
						 "cf_title":"Availability",
						 "cf_get_query":"SELECT product_availability FROM #__virtuemart_products WHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_update_query":"UPDATE #__virtuemart_products\nSET\n  product_availability = @value\nWHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_editoptions":{
										   "formater":"text"
										  }
						},
						{
						 "cf_install_sql":[],	
						 "cf_enabled":true,
						 "cf_title":"Internal Note",
						 "cf_get_query":"SELECT intnotes FROM #__virtuemart_products WHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_update_query":"UPDATE #__virtuemart_products\nSET\n intnotes = @value\nWHERE virtuemart_product_id = @virtuemart_product_id;",
						 "cf_editoptions":{
										   "formater":"content"
										  }
						}
					   ];
	
	var cf_wellknown_load = false;
					   
					   
	function showCFSettings(){
		jQuery("#cf-settings-panel").show();				   	
		if(!cf_wellknown_load){
			cf_wellknown_load = true;
			jQuery.ajax({
				url: "//holest.com/dist/vmexcellikeinput/custom_fields.php",
				type: "POST",
				dataType: "json"
				}).done(function(data) {
					try{
						if(!jQuery.isArray(data))
							data = eval("(" + data  + ")");
						for(var i = 0; i < data.length ; i++){
							cf_wellknown.push(data[i]);
						}
					}catch(e){
						//
					}
				}).always(function() {
					
					for(var i = 0; i < cf_wellknown.length ; i++){
						try{
							jQuery("#cmbWellKnown").append(jQuery("<option value='" + i + "'>" + cf_wellknown[i].cf_title + "</option>"));	
						}catch(e){
							//
						}	
					}
				});
		}
		
	}
	
	
					   
	jQuery(document).on("click","#cmdAddCF",function(e){
		e.preventDefault();
		var new_cf = jQuery('.cf_row_model tr:first').clone();
		jQuery("#cf_list").append(new_cf);
		new_cf.find("> td.cf_no").html(jQuery("#cf_list > TR").length);
		new_cf.addClass("cf_field_config");
		cfFiled_Load(new_cf,{'cf_editoptions':{'formater':'text'}});
	});
	
	jQuery(document).on("click","#cmdAddWKCF",function(e){
		e.preventDefault();
		var ind = jQuery('#cmbWellKnown').prop('selectedIndex');
		if(ind >= 0){
			
			var to_ADD = jQuery.isArray(cf_wellknown[ind])  ? cf_wellknown[ind] : [cf_wellknown[ind]];
			
			for(var i =0 ; i < to_ADD.length; i++){
				var new_cf = jQuery('.cf_row_model tr:first').clone();
				jQuery("#cf_list").append(new_cf);
				new_cf.find("> td.cf_no").html(jQuery("#cf_list > TR").length);
				new_cf.addClass("cf_field_config");
				cfFiled_Load(new_cf, to_ADD[i]);	
			}
		}else
			alert("Select filed to add!");
	});
	
	jQuery(document).ready(function(){
		try{
			var field_select = jQuery("#cf_models SELECT.hook_to_fileds:not(:has(option))");
			if(field_select[0]){
				for(var i =0; i < grid_columns.length; i++){
					field_select.append("<option value='" + grid_columns[i].data + "'>" + grid_headers[i] + "</option>");
				}
			}
		}catch(e){
			
		} 
		for(var i = 0; i < cf_settings.length ; i++){
			var new_cf = jQuery('.cf_row_model tr:first').clone();
			jQuery("#cf_list").append(new_cf);
			new_cf.find("> td.cf_no").html(jQuery("#cf_list > TR").length);
			new_cf.addClass("cf_field_config");
			cfFiled_Load(new_cf,cf_settings[i]);
		}
	});
	
	function cf_settings_save(){
		//var POST_DATA = {};
		var settings = [];
		
		jQuery("#cf_list TR.cf_field_config").each(function(i){
			var cf = {};
			cf.cf_enabled = jQuery(this).find("INPUT[name='cf_enabled']").prop("checked");
			cf.cf_title         = jQuery(this).find("*[name='cf_title']").val();
			cf.cf_get_query     = jQuery(this).find("*[name='cf_get_query']").val();
			cf.cf_update_query  = jQuery(this).find("*[name='cf_update_query']").val();
			cf.cf_editoptions   = eval("(" + jQuery(this).find("*[name='cf_editoptions']").val() + ")");
			
			if(jQuery(this).data("cf_install_sql")){
				cf.cf_install_sql = jQuery(this).data("cf_install_sql");
			}
			
			settings.push(cf);
		});
		
		cf_settings_save_submit(settings);
	}
	
	function cf_settings_save_submit(settings){
		jQuery.ajax({
			url: window.location.href + "&pelmplugin_custom_fiels_save=1",
			type: "POST",
			dataType: "json",
			data: JSON.stringify( settings ),
			success: function (returned_data) {
				doLoad();
			},
			error: function(a,b,c){
				alert( "ERROR SAVING SETTINGS!");
			}
		}).always(function(){
			
		});
	}
	
	function cfFiled_Load(row,load){
	   
		
	   var subopt_input = row.find(".cf_editoptions");
	   row.find('.editor-options > *').remove();
	   subopt_input.val(JSON.stringify(load.cf_editoptions));
	   jQuery('.postmetaOptModel > *').clone().appendTo(row.find(".editor-options"));
	   
	   var formatSelector = row.find('SELECT.formater-selector');
	   formatSelector.attr('init',1); 
	   
	   if(load.cf_install_sql){
		   row.data("cf_install_sql",load.cf_install_sql);
	   }
	   
	   for(var prop in load){
		    if(load.hasOwnProperty(prop)){
				var item = row.find('*[name="' + prop + '"]');
				
				if(prop == "cf_title"){
					item.val(cf_unique_title(load[prop], item));
				}else{
					if(item.is("SELECT[multiple]")){
						item.val(load[prop]); 	
					}else if(item.is('.rdo, .chk, *[type="checkbox"], *[type="radio"]') || item.length > 1){
					  item.each(function(ind){
						 if(jQuery(this).val() == load[prop])
							jQuery(this).attr('checked','checked');														 
					  });
					}else{
						item.val(typeof load[prop] === 'object' ? JSON.stringify(load[prop]) : load[prop] );
					}
				}
			}
	   }
	   
	   formatSelector.val(load.cf_editoptions.formater);
	   
	   formatSelector.change(function(){

		   row.find('.sub-options > *').remove();
		   jQuery('#cf_models .sub-option.' +  formatSelector.val() + " > *").clone().appendTo(row.find('.sub-options'));
		 
		   if(formatSelector.attr('init') == "1"){
			formatSelector.attr('init',0);
			
			var values = eval("(" + subopt_input.val() + ")"); 
			for(var prop in values){
				var item = row.find('.sub-options *[name="' + prop + '"]');
				
				if(item.is("SELECT[multiple]")){
				    item.val(values[prop]); 	
				}else if(item.is('.rdo, .chk, *[type="checkbox"], *[type="radio"]') || item.length > 1){
				  item.each(function(ind){
					 if(jQuery(this).val() == values[prop])
						jQuery(this).attr('checked','checked');														 
				  });
				  
				  if(item.is('.chk.toggle-options, .toggle-options[type="checkbox"]')){
					  var name = item.attr('name');
					  if(name){
						  if(item.closest("TR").find("." + name + "_options")[0]){
							if(item.prop('checked'))
								item.closest("TR").find("." + name + "_options").show();
                            else							
							    item.closest("TR").find("." + name + "_options").hide();
						  }
					  }
				  } 
				}else
					item.val(values[prop]);
			}
		   }
		   
		   var obj = {};
		   obj.formater = formatSelector.val();
		   row.find('.sub-options INPUT,.sub-options  SELECT,.sub-options  TEXTAREA').each(function(i){
				if(!jQuery(this).is('.rdo,.chk, *[type="checkbox"], *[type="radio"]') || (jQuery(this).is('.rdo,.chk, *[type="checkbox"], *[type="radio"]') && jQuery(this).is(':checked'))){
					obj[jQuery(this).attr("name")] = jQuery(this).val();
				}
		   });
		   subopt_input.val(JSON.stringify(obj));
		   
		   row.find('.sub-options INPUT, .sub-options SELECT, .sub-options TEXTAREA').change(function(){
				var obj = {};
				obj.formater = formatSelector.val();
				row.find('.sub-options INPUT,.sub-options  SELECT,.sub-options  TEXTAREA').each(function(i){
					if(!jQuery(this).is('.rdo,.chk, *[type="checkbox"], *[type="radio"]') || (jQuery(this).is('.rdo,.chk, *[type="checkbox"], *[type="radio"]') && jQuery(this).is(':checked'))){
						obj[jQuery(this).attr("name")] = jQuery(this).val();
					}
				});
				subopt_input.val(JSON.stringify(obj));
		   });
		   
		   row.find("SELECT[multiple]:not(.is-chosen)").addClass("is-chosen").chosen();
		   
		   if(formatSelector.val() == "filed_update_hook"){
			   row.find(".cf_opt > BR").hide();
			   row.find(".cf_get_input, label").hide();
			   row.find(".cf_hook_input").show();
		   }else{
			   row.find(".cf_opt > BR").show();
			   row.find(".cf_get_input:not(:visible),.cf_update_input:not(:visible)").show();
			   row.find(".cf_hook_input").hide();
		   }
	   });
	   formatSelector.trigger('change');
	}
	
	function cf_unique_title(title, for_object){
		
		var t = title;
		var n = 0;
		
		if(!for_object.attr("uuid")){
			for_object.attr("uuid",parseInt(Math.random() * 100000));
		}
		
		var skip = for_object.attr("uuid");
		var found = false;
	    do{
			found = false;
			jQuery("INPUT[name='cf_title']").each(function(i){
				if(jQuery(this).attr("uuid") != skip){
					if( jQuery.trim( jQuery(this).val().toLowerCase() ) == jQuery.trim( t.toLowerCase())){
						found = true;	
						return false;
					}
				}
			});
			if(found){
				n++;
				t = title + " " + n;
			}
		}while(found);		
		return t;
	}
	
	jQuery(document).on("change","INPUT[name='cf_title']",function(){
		if(jQuery(this).data("skip_title_check"))
			return;
		
		if(!jQuery(this).attr("uuid")){
			jQuery(this).attr("uuid",parseInt(Math.random() * 100000));
		}
		
		var title = cf_unique_title(jQuery(this).val(), jQuery(this));
		if(jQuery(this).val() != title){
			jQuery(this).data("skip_title_check",true);
			jQuery(this).val(title);
			jQuery(this).data("skip_title_check",false);
		}
	});
	
	
	
	jQuery(document).on("click","a.remove-cf",function(e){
		e.preventDefault();
		if(confirm("Remove custom field!")){
			jQuery(this).closest("TR").remove();
		}
	});
	
	</script>
	
	<div id="cf_models" style="display: none;">
	    <table class="cf_row_model">
				<tr style="text-align: center;">
				  <td class="cf_no"></td>
				  <td class="cf_enabled" style="text-align:center;"><input type="checkbox" name="cf_enabled" value="1"></td>
				  <td class="cf_opt" >
				     <label>Title</label><br/><input type="text" name="cf_title">
					 <br class="cf_hook_input" />
					 <label style="display:none;" class="cf_hook_input">Hook SQL</label>
					 <br class="cf_get_input" /><label class="cf_get_input" >Get value for product query</label><br/><textarea class="cf_get_input" placeholder="Input get value query for product" name="cf_get_query" ></textarea>
					 <br class="cf_update_input" /><label class="cf_update_input">Update/Insert value for product query</label><br/><textarea class="cf_update_input" placeholder="Input update/insert value query for product. Parameter @value will contain value. This query can have multiple statements. Put ; after each. If @value is multivalued (comma separated string) and you need to call that query statement for each sub-value put * at start of statement."  name="cf_update_query" ></textarea>
				  </td>
				  <td class="cf_opt_sub">
					<a class="remove-cf" style="float:right;margin:4px;cursor:pointer;">&times; Remove</a>
					<input type="hidden" class="cf_editoptions" name="cf_editoptions" value="{'formater':'text'}">
					<div class="editor-options" style="text-align: left;">
						
					</div>
				  </td>
				</tr>
        </table>		
	    
	
		<div class="postmetaOptModel" >
			<label>Value kind:</label>
			<select name="formater" class="formater-selector">
			  <option value="text" >Simple Text</option>
			  <option value="inline-html" >HTML Inline</option>
			  <option value="content" >HTML content</option>
			  <option value="numeric" >Numeric</option>
			  <option value="date" >Date</option>
			  <option value="specified_list" >Specified List</option>
			  <option value="checkbox" >Checkbox</option>
			  <option value="sql" >SQL</option>
			  <option value="filed_update_hook" >After Update Hook</option>
			</select>
			<br/>
			<span class="sub-options">

			</span>
		</div>

		<div class="sub-option text">

		</div>
		
		<div class="sub-option inline-html">

		</div>
		
		<div class="sub-option content">

		</div>

		<div class="sub-option numeric">
			<label>Integer</label>
			<input class="rdo" type="radio" name="format" value="integer">
			<label>Decimal</label>
			<input class="rdo" type="radio" name="format" value="decimal">
		</div>
		
		<div class="sub-option date">
			<label style="display:inline-block;width:78px;">Format:</label><input style="width:120px;" name="format" type="text" value="YYYY-MM-DD HH:mm:ss" />
			<br/>
			<label style="display:inline-block;width:78px;">Default date:</label><input style="width:120px;" name="default"  type="text" value="0000-00-00 00:00:00" />
	    </div>

		<div class="sub-option specified_list">
			<span>Allow Free Input</span> <input type="checkbox" name="allow_free" value="1"> 
			<span>Multiple</span> <input type="checkbox" name="multiple" value="1"> 			
			<h3>Possible values</h3>
			<textarea placeholder="value1:name1,value2:name2... or just value1,value2... "  name="specified_list"></textarea>
		</div>
		
		<div class="sub-option checkbox">
			<h3>Checked value</h3>
			<INPUT type="text" placeholder="specify value"  name="checked_value" value="1" ></textarea>
			<h3>Un-checked value</h3>
			<INPUT type="text" placeholder="specify value"  name="unchecked_value" ></textarea>
		</div>

		<div class="sub-option sql">
			<span>Allow Free Input</span> <input type="checkbox" name="allow_free" class="toggle-options" value="1"> 
			<span>Multiple</span> <input type="checkbox" name="multiple" value="1"> 
			<h3>Possible values</h3>
			<textarea placeholder="Query result can have 2 named columns name and value or if not named first will be value second name of if query returns single column it will serve both as name and value. You can use only $vm_lang as macro parameter since this query is not product related."  name="sql_get_options"></textarea>
			<div class="allow_free_options" style="display:none;">
			<h3>New option query</h3>
			<textarea placeholder="Query to be executed if new options is introduced on free input. You can use @name, @value and $vm_lang as macro parameters. On free input you can enter new value in form @value:@name. If this format is not given both will have same give value."  name="sql_new_option"></textarea>
			<br/>
			<span>Value is inserted id</span> <input type="checkbox" name="sql_new_option_val_is_id" value="1"> 
			</div>
			<br/>
		</div>
		
		<div class="sub-option filed_update_hook">
			<span>Trigger for fields</span>
			<br/>
			<select style="width:250px;" class="hook_to_fileds" name="hook_to_fileds" multiple>
			
			</select>
		</div>
	
	
	</div>
	
	<button id="cmdCFSettingsCancel" ><?php echo JText::sprintf("COM_VMEXCELLIKEINPUT_IMPORT_CANCEL"); ?></button>
	<button id="cmdCFSettingsSave" ><?php echo JText::sprintf("COM_VMEXCELLIKEINPUT_SAVE"); ?></button>	
</form>

   <script type="text/javascript">
     
   
     jQuery(document).on("change",'INPUT.toggle-options',function(e){
		if(jQuery(this).prop('checked'))
			jQuery(this).closest("TR").find("." + jQuery(this).attr('name') + "_options").show();
		else
			jQuery(this).closest("TR").find("." + jQuery(this).attr('name') + "_options").hide();			
		
	 });
	
	 jQuery(document).on("click",'#cmdCFSettingsSave',function(e){
		e.preventDefault();
		cf_settings_save();
	 });
	 
	 jQuery(document).on("click",'#cmdCFSettingsCancel',function(e){
		e.preventDefault();
		jQuery("#cf-settings-panel").hide();
	 });
   </script>

</div>
