var mod_productcustoms = {
	goTo: function(el) {
		var jel = jQuery(el); 
		var hm = jel.data('name'); 
		var result = false; 
		if (typeof hm === 'undefined') return false; 
		
		/*
		var stype = jel.attr('type'); 
		
		if (stype == 'checkbox') {
			result = true; 
		}
		else {
		var s = "input[name='"+jel.data('name')+"'][value='"+jel.data('value')+"']";  
		
		var se = jQuery(s); 
		if (se.length > 0) {
			if (se[0].checked == true) {
				se[0].checked = false;
			}
			else {
			  se[0].checked = true; 
			}
		}
		
		}
		*/
		
		var config = mod_productcustoms.getConfig(); 
		
		var selected_tab = document.querySelector('#filter_tabs > li.uk-active'); 
		if (selected_tab) {
			var active_tab_e = document.getElementById('active_tab'); 
			if (active_tab_e) {
			 active_tab_e.value = jQuery(selected_tab).data('key'); 
			}
		}
		
		var tag = el.tagName.toLowerCase(); 
		if (tag === 'a') {
			
			
			
			var c = jel.data('checkbox'); 
			var ch = document.getElementById(c);
			if (ch) {
			if (ch.checked === true) {
				ch.checked = false; 
			}
			else {
				ch.checked = true;
			}
			}
			
			var x = mod_productcustoms.getSelected(); 
			
			
			if (config.chosenmethod && config.chosenmethod === 'LINK') {
				if (x.indexOf('empty=1') < 0) {
					return true; 
				}
				else {
					if (config.home_url) {
						document.location = config.home_url; 
						return false; 
					}
				}
			
			}
			
			
			
			if (config.admin) {
				return false; 
			}
			
			if (!config.has_button) {
				
				if ((typeof ch.form !== 'undefined')){
					
					if (x.indexOf('empty=1') > 0) {
					 jQuery(ch.form).append('<input type="hidden" name="empty" value="1" />'); 
					}
					
					ch.form.submit(); 
					return false; 
				}
				else {
					var x = mod_productcustoms.getSelected(); 
					el.href = x; 
					return true; 
				}
				//filter_form.submit(); 
			}
			return false; 
			
		}
		else {
			var x = mod_productcustoms.getSelected(); 
			
			if (config.chosenmethod && config.chosenmethod === 'LINK') {
				if (x.indexOf('empty=1') < 0) {
				var h = jel.data('href'); 
				if (h) {
					document.location = h;
					return true; 
				}
				
				var myid = el.id; 
				var linke = jQuery('a[data-checkbox='+myid+']'); 
				if (linke.length) {
					var href = linke.attr('href');
					if (href) {
						document.location = linke.attr('href');
						return true; 
					}
				}
				}
				else {
					if (config.home_url) {
						document.location = config.home_url; 
						return true; 
					}
				}
			}
			
			
			
			if (config.admin) {
				return true; 
			}
			if (!config.has_button) {
				if (typeof el.form !== 'undefined') {
					
					if (x.indexOf('empty=1') > 0) {
					 jQuery(el.form).append('<input type="hidden" name="empty" value="1" />'); 
					}
					
					el.form.submit(); 
					return false; 
				}
				document.location = x; 
				return true; 
				return filter_form.submit(); 
			}
			return true; 
		
		}
		
		
	},
	
	getSelected: function() {
		var ret = 'index.php?'; 
		var foundn = 0; 
		var lastel = null;
		jQuery('.productfilter_selector:checked').each( function() {
			var el = this; 
			if (ret !== '') ret += '&';
			if (el.name.indexOf('[') < 0) {
				ret += el.name+'[]='+el.value; 
			}
			else {
				ret += el.name+'='+el.value; 
			}
			foundn++;
			lastel = this; 
		}); 
		
		//current main category: 
		var obj = jQuery.parseJSON(window.getUrl); 
		delete obj.virtuemart_category_id; 
		delete obj.virtuemart_custom_id; 
		
		if (ret !== '') ret += '&';
		ret += jQuery.param(obj);
		/*
		if (typeof obj.virtuemart_category_id !== 'undefined') {
			 ret += '&virtuemart_category_id[]='+obj.virtuemart_category_id; 
		}
		*/
		if (foundn === 0) {
			ret += '&empty=1'; 
			
		}
		
		
		return ret; 
	},
	
	getConfig: function() {
			var dd =  document.getElementById('ajaxconfig');
			if (dd) {
			  var config = jQuery(dd).data('config'); 
			  return config; 
			}
		var obj = {}; 
		obj.admin = false; 
		return obj; 
	},
	
	removeFilter: function(event, group) {
		event.preventDefault(); 
		var els = document.querySelectorAll('input[data-value="'+group+'"]'); 
		for (var i = 0; i < els.length; i++) {
			els[i].checked = false;
		}
		
		//reset_url
		var els2 = document.querySelectorAll('input[data-name="virtuemart_custom_id"]'); 
		var has_checked = false; 
		for (var i = 0; i < els2.length; i++) {
			if (els2[i].checked) has_checked = true; 
		}
		if (!has_checked) {
			var config = mod_productcustoms.getConfig(); 
			if (config.reset_url) {
				document.location = config.reset_url; 
				return false; 
			}
		}
		else
		if (els.length) {
			filter_form.submit(); 
		}
		return false; 
	},
	
	
  openTab: function(el) {
	  var jel = jQuery(el); 
	  var ref = jel.data('uk-tab'); 
	  
	  var remote_id = ref.connect; 
	  if (remote_id) {
	    var section = jQuery(remote_id); 
		section.toggle(); 
	  }
	  return false; 
  }
  	
}


jQuery(document).ready(function () {
	  jQuery('.button_for_ajaxsearch').on('click', function() {
			var d = document.getElementById('rup_next_value'); 
			if (d != null) {
					var next = d.value; 
					document.getElementById('filter_form_limit').form.method = 'post'; 
					document.getElementById('filter_form_limit').value = next;
					document.getElementById('filter_form_prod').value = next;
					document.getElementById('filter_form').submit(); 
					return false; 
			}
		  
	  }); 
	}); 