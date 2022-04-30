function runCountryFilters(elopc) {
	
	
	
	
	var toHideFields = []; 
	var toShowFields = []; 
	var toRequireFields = []; 
	var toNotRequire = [];
	if (typeof jQuery === 'undefined') return; 
	var country_id = Onepage.op_getSelectedCountry(); 
	
	var bt_c = jQuery('#virtuemart_country_id').val(); 
	var st_ce = jQuery('#virtuemart_country_id'); 
	var st_c = bt_c; 
	if (st_ce.length) {
	 st_c = jQuery('#shipto_virtuemart_country_id').val(); 
	}
	
	st_c = parseInt(st_c); if (isNaN(st_c)) st_c = bt_c; 
	bt_c = parseInt(bt_c); if (isNaN(bt_c)) return; 
	
	
	var main = document.getElementById('vmMainPageOPC'); 
	if (main) {
		main.className = main.className.split(' c_').join(' was_').split(' st_c_').join(' was_').split(' bt_c_').join(' was_'); 
		main.className += ' c_'+country_id; 
		main.className += ' st_c_'+st_c; 
		main.className += ' bt_c_'+bt_c; 
	}
	
	var col = jQuery('[data-country_filter]'); 
	if (col.length) {
		col.each(function() { 
		  var jel = jQuery(this);  
		  var country_filter = jel.data('country_filter'); 
		  if (country_filter) {
			  if (this.name.indexOf('shipto_') === 0) {
				  country_id = st_c; 
			  }
			  else {
				  country_id = bt_c; 
			  }
			  
			  if (((country_filter.country_field_hidden) && (country_filter.country_field_hidden[country_id])) || ((country_filter.country_field_shown) && (!country_filter.country_field_shown[country_id]) )) {
					  toHideFields.push(this.name);  
					  
				  }
				  else {
					  toShowFields.push(this.name); 
					  
				  }
				  if (country_filter.country_field_required) {
					  if (country_filter.country_field_required[country_id]) {
						  toRequireFields.push(this.name); 
						 
						  
					  }
					  else {
						  
						    toNotRequire.push(this.name); 
					    
					  }
				  }
			  
		  }
		}); 
		
		setFieldDisplay(true, toHideFields, toRequireFields);
		setFieldDisplay(false, toShowFields, toRequireFields);
		setFieldRequired(toRequireFields, toNotRequire); 
		}
		
		var col2 = jQuery('[data-country_config]'); 
	if (col2.length) {
		col2.each(function() { 
		  var jel = jQuery(this);  
		  var country_config = jel.data('country_config'); 
		  if (country_config) {
			  
			  if (this.name.indexOf('shipto_') === 0) {
				  country_id = st_c; 
			  }
			  else {
				  country_id = bt_c; 
			  }
			  
			  if (country_config[country_id]) {
				  if (country_config[country_id].html5_placeholder) {
				   jel.attr('placeholder', country_config[country_id].html5_placeholder);
				    
				   
				  }
				  else {

					   setGenericFilter(jel, country_config, 'placeholder'); 
				  }
				  
				  if (country_config[country_id].html5_validation_error) {
				   jel.attr('onerrormsg', country_config[country_id].html5_validation_error);
				  }
				  else {
					  setGenericFilter(jel, country_config, 'onerrormsg'); 
				  }
				  
				  if (country_config[country_id].html5_fields_validation) {
				   jel.attr('pattern', country_config[country_id].html5_fields_validation);
				   jel.attr('validate', 'validate');
				  }
				  else {
					  //use generic:
					  setGenericFilter(jel, country_config, 'pattern'); 
				  }
				  
				  if (country_config[country_id].html5_autocomplete) {
				   jel.attr('autocomplete', country_config[country_id].html5_autocomplete);
				  }
				  else {
					  setGenericFilter(jel, country_config, 'autocomplete'); 
				  }
			  }
			  else {
				  
				  setGenericFilter(jel, country_config, 'placeholder'); 
				  setGenericFilter(jel, country_config, 'onerrormsg'); 
				  setGenericFilter(jel, country_config, 'pattern'); 
				  setGenericFilter(jel, country_config, 'autocomplete'); 
				  
			  }
		  }
		})
	}
	
	//re-run validation if country is changed:
	if ((typeof elopc !== 'undefined') && (typeof elopc.id !== 'undefined'))
		if ((elopc.id === 'virtuemart_country_id') || (elopc.id === 'shipto_virtuemart_country_id')) {
				quickvalidation(); 
		}
		
	
}

function setGenericFilter(jel, country_config, atrtype) {
				
				if (country_config[0]) {
					
					if (atrtype == 'placeholder') {
					  if (country_config[0].html5_placeholder) {
						  
							jel.attr('placeholder', country_config[0].html5_placeholder);
						  
						
					}
					else {
						jel.removeAttr('placeholder'); 
					}
				  }
				  
				  if (atrtype == 'onerrormsg') {
				  if (country_config[0].html5_validation_error) {
					  if (atrtype == 'onerrormsg') {
						jel.attr('onerrormsg', country_config[0].html5_validation_error);
					  }
				  }
				  else {
					  jel.removeAttr('onerrormsg'); 
				  }
				  }
				  
				  if (atrtype == 'pattern') {
				  if (country_config[0].html5_fields_validation) {
					  
						jel.attr('pattern', country_config[0].html5_fields_validation);
						jel.attr('validate', 'validate');
					  
				  }
				  else {
					  jel.removeAttr('pattern'); 
					  jel.removeAttr('validate'); 
				  }
				  }
				  
				  if (atrtype == 'autocomplete') {
				  if (country_config[0].html5_autocomplete) {
					   
						jel.attr('autocomplete', country_config[0].html5_autocomplete);
					   
				  }
				  else {
					  jel.removeAttr('validate'); 
				  }
				  }
				  
				  
	}
	else {
					 jel.removeAttr(atrtype); 
					 if (atrtype === 'pattern') {
						jel.removeAttr('validate'); 
					 }
				  }
}

function setFieldRequired(requiredFieldCollection, toNotRequire) {
	if ((typeof requiredFieldCollection != 'undefined') && (requiredFieldCollection != null) && (requiredFieldCollection.length > 0))
						{
						  Onepage.toggleRequired(requiredFieldCollection, false, '', '_field'); 
						}
						
	if ((typeof toNotRequire != 'undefined') && (toNotRequire != null) && (toNotRequire.length > 0))
						{
						  Onepage.toggleRequired(toNotRequire, true, '', '_field'); 
						}
						
}
function setFieldDisplay(toHide, fieldsCollection, requiredFieldCollection) {
						if (typeof fieldsCollection != 'undefined')
						if (fieldsCollection.length > 0) {
					  
						   //shows b
						Onepage.toggleFields(fieldsCollection, '_div', toHide); 
						for (var i = 0; i<fieldsCollection.length; i++)
							{
						if (toHide) {
							jQuery('.'+fieldsCollection[i]+'_wrap').hide(); 
						}
						else {
							jQuery('.'+fieldsCollection[i]+'_wrap').show(); 
						}
							}
						Onepage.toggleFields(fieldsCollection, '_wrap', toHide); 
						Onepage.toggleFields(fieldsCollection, '_input', toHide);
						Onepage.toggleFields(fieldsCollection, '_field', toHide);
						Onepage.toggleFields(fieldsCollection, '', toHide, 'opc_business_');
						if ((typeof requiredFieldCollection != 'undefined') && (requiredFieldCollection != null) && (requiredFieldCollection.length > 0))
						{
						  Onepage.toggleRequired(requiredFieldCollection, toHide, '', '_field'); 
						}
					   
					  
					   }
					   
					   if (typeof business_fields != 'undefined')
						if (business_fields.length > 0) {
					  
						   //shows b
						/*Onepage.toggleFields(business_fields, '_div', toHide); 
						Onepage.toggleFields(business_fields, '_input', toHide);
						Onepage.toggleFields(business_fields, '', toHide, 'opc_business_');*/
						if ((typeof requiredFieldCollection != 'undefined') && (requiredFieldCollection != null) && (requiredFieldCollection.length > 0))
						{
						  Onepage.toggleRequired(requiredFieldCollection, toHide, '', '_field'); 
						}
					   
					  
					   }
					   
}
function quickvalidation() {
	Onepage.validateFormOnePagePrivate(true, null, true, true); 
	/*
	invalidf = Onepage.fastValidation('', op_userfields, wasValid, invalidf, true, false); 
	invalidf = Onepage.fastValidation('shipto_', op_userfields, wasValid, invalidf, true, false); 
	*/
}

if (typeof addOpcTriggerer !== 'undefined') {
  addOpcTriggerer('callBeforeAjax', 'runCountryFilters(elopc)'); 
  //addOpcTriggerer('callAfterAjax', 'quickvalidation()'); 
  
}
