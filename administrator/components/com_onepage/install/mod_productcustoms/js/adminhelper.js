if (!mod_productcustoms) mod_productcustoms = {};

mod_productcustoms.showControl = function() {
	var d = document.getElementById('admin_control').style.display = 'block'; 
	
	var jel = jQuery('#filter_form li.uk-active input'); 
	var labels = []; 
	if (jel.length) {
		jel.each(function() { 
		   if (this.checked) {
			  var je = jQuery(this); 
			  if (je.data('label')) {
			  labels.push(je.data('label')); 
			  }
			  
		   }
		}); 
	}
	document.getElementById('admin_selected_filters').innerHTML = labels.join('<br />'); 
	var da = document.getElementById('admin_do_group_btn'); 
	if (labels.length > 0) {
		da.disabled = false; 
	}
	else {
		da.disabled = true; 
	}
	console.log(labels); 
	return false; 
}

mod_productcustoms.doGroup = function(el) {
	var jel = jQuery('#filter_form li.uk-active input'); 
	var data2send = []; 
	if (jel.length) {
		jel.each(function() { 
		   if (this.checked) {
			  var je = jQuery(this); 
			  if (je.data('label')) {
			  var obj = {}; 
			  obj.type = je.data('name'); 
			  obj.value = je.data('value'); 
			  data2send.push(obj); 
			  }
			  
		   }
		}); 
	}
	var dx = document.getElementById('group_name'); 
	var config = mod_productcustoms.getConfig(); 
	if (dx.value === '') {
		alert(config.group_error); 
		return false; 
	}
	if (!data2send.length) {
		alert(config.groupnodata_error); 
		return false; 
		
	}
	
	var obj = {};
	obj.type = 'group_name'; 
	obj.value = dx.value; 
	
	data2send.push(obj); 
	
	return mod_productcustoms.doAjax(data2send); 
}
mod_productcustoms.doAjax = function(data2send) {
	var config = mod_productcustoms.getConfig(); 
	var cf = {};
	cf.type = 'config'; 
	cf.value = config;
	
	data2send.push(cf); 
	
	var dataJson = JSON.stringify(data2send);
	console.log('data2send', data2send); 
	
	var ret = jQuery.ajax({
				type: "POST",
				url: config.admin_url,
				data:  dataJson,
				contentType: 'application/json; charset=utf-8',
				cache: false,
				async: true,
				complete: function(datasRaw)
				  {
				    if (!(datasRaw.readyState==4 && datasRaw.status==200)) return;
					retObj = datasRaw; 
					if (datasRaw.readyState==4 && datasRaw.status==200) result = mod_productcustoms.process_resultResponse(datasRaw); 
					if (datasRaw.readyState==4 && datasRaw.status > 200)  console.log(datasRaw.responseText); 
					return result; 
				  },
				error: function(data, data2) {
					console.log(data); 
					console.log(data2.responseText); 
				}
				
				
				 
				
				});
				
			return false; 
   }
   mod_productcustoms.reRender = function(html) {
	   console.log(html);
	   var config = mod_productcustoms.getConfig(); 
	   jQuery('#module_id_'+config.module_id).html(html); 
	   
   }
   mod_productcustoms.process_resultResponse = function(datasRaw) {
	   
	   try {
		   var j = JSON.parse(datasRaw.responseText);
		   if (j.html) {
			   mod_productcustoms.reRender(j.html); 
		   }
		   console.log(j); 
	   }
	   catch(e) {
		   console.log(datasRaw.responseText); 
	   }
   }
   
   mod_productcustoms.removeGroupName = function(el) {
	   var jel = jQuery(el); 
	   var item = jel.data('value'); 
	   
	   var data2send = []; 
	   
	   var obj = {};
		obj.type = 'remove'; 
		obj.value = item;
	
	   data2send.push(obj); 
	   
	   return mod_productcustoms.doAjax(data2send); 
   }
	
	
