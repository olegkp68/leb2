function toggleList(module_id) {
	  jQuery('.cart_list_'+module_id).toggle("slow");
	  jQuery('.listtoggler_'+module_id).hide(); 
	  return false; 
  }
  function loadCart(cart_name_id, module_id) {
	  var d = document.getElementById('cart_name_id_'+module_id); 
	  if (d != null) {
		  d.value = cart_name_id; 
		  
		  var d2 = document.getElementById('myaction_'+module_id); 
		  if (d2 != null) {
			  d2.value = 'loadid'; 
			  
			  var f = getForm(module_id); 
			  
			  var callback = function() {
				f.submit();   
			  }
			  checkMergeCart(module_id, callback); 
			  
			  
		  }
	  }
	  return false; 
  }
  function checkMergeCart(module_id, callback) {
	  var d = document.getElementById('merge_'+module_id); 
	  if (d != null) {
		  var jel = jQuery(d); 
		  var q = jel.data('question'); 
		  if (q) {
			  var yes = jel.data('questionyes'); 
			  var no = jel.data('questionno'); 
			  var cancel = jel.data('questioncancel'); 
					
			  var dialog = jQuery('<p>'+q+'</p>').dialog({
				  
					
				  
                    buttons: {
                        yes: function() {
							d.value = 1; 
							callback();
							return false; 
						},
                        no:  function() {
							d.value = 0; 
							callback();
							return false; 
						},
                        cancel:  function() {
                            
                            dialog.dialog('close');
							return false; 
                        }
                    }
                });
				return false; 
		  }
	  }
	  callback(); 
  }
   function dropCart(cart_name_id, module_id, name) {
	  var d = document.getElementById('cart_name_id_'+module_id); 
	  if (d != null) {
		  d.value = cart_name_id; 
		  
		  var d2 = document.getElementById('myaction_'+module_id); 
		  if (d2 != null) {
			  d2.value = 'dropid'; 
			  
			  var dtx = MOD_CARTSAVE_QUESTION; 
			  dtx = dtx.split('{cart_name}').join(name); 
			  if (confirm(dtx)) {
				var f = getForm(module_id); 
				f.submit(); 
				return false; 
			  }
		  }
	  }
	  return false; 
  }
  
  function actionCart(action, module_id) {
		  if (action === 'save') {
			   var d = document.getElementById('cart_name_'+module_id); 
			   if (d) {
				   if (d.value === '') {
					   alert(MOD_CARTSAVE_ERROR_NAME_MISSING_SAVE); 
					   return false; 
				   }
			   }
		  }
		  
		   if (action === 'load') {
			   var d = document.getElementById('cart_name_'+module_id); 
			   if (d) {
				   if (d.value === '') {
					   alert(MOD_CARTSAVE_ERROR_NAME_MISSING_LOAD); 
					   return false; 
				   }
			   }
		    }
		  
		  var d2 = document.getElementById('myaction_'+module_id); 
		  if (d2 != null) {
			  d2.value = action; 
			  
			  var f = getForm(module_id); 
			  f.submit(); 
		
			}
	  return false; 
  }
  
  function getForm(module_id) {
	  
	  var el = document.getElementById('cartsaverform_'+module_id);
	  if (el) {
		  var tag = el.tagName.toUpperCase();
		  if (tag !== 'FORM') {
			  //we'll replace the ID:
			  var ref = jQuery(el).data('ref'); 
			  if (ref) {
				  var dx = document.getElementById(ref); 
				  return dx; 
			  }
			  

			  
		  }
		  if (tag === 'FORM') {
			  return el; 
		  }
	  }
	  
	  
	  
  }
  function uploadFile(cart_name_id, module_id, name) {
		
		
		var myForm = getForm(module_id); 
		
		var d = document.getElementById('cart_name_id_'+module_id); 
			   if (d != null) {
				    
						d.value = cart_name_id;
										
				}
				
			var d = document.getElementById('cart_name_'+module_id); 
			   if (d != null) {
				    
						d.value = name;
										
				}
	  
	  myForm.cart_upload_file.click(); 
	  return false; 
  }
  function validateUploadFile(sender,module_id) {
	  var validExts = new Array(".xlsx", ".xls");
    var fileExt = sender.value;
    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
    if (validExts.indexOf(fileExt) < 0) {
			  alert(MOD_CARTSAVE_WRONGFILEFORMAT);
      return false;
    }
    else {
		
		  var d2 = document.getElementById('myaction_'+module_id); 
		  if (d2 != null) {
			  d2.value = 'upload'; 
			  
			  
			   
			  
			  var f = getForm(module_id); 
			  f.enctype = 'multipart/form-data'; 
			  
			  f.submit(); 
		
			}
		
	return true;
	}
	
	
  }
  
  function toolboxopen(module_id) {
	  jQuery('#cartsaverform_'+module_id).toggle(); 
	  jQuery('#cartsavertoolbox_'+module_id).hide(); 
	  return false; 
  }
  
  function shareIconName(el, cart_name_id, module_id) {
	  var name = jQuery(el).data('name'); 
	  if (name) {
	   toClipboard(name); 
	  }
	  var e = document.getElementById('share_menu_'+cart_name_id+'_'+module_id); 
	  jQuery(e).toggle(); 
	  return false; 
  }
  function shareIconLink(el, cart_name_id, module_id) {
	  toClipboard(el.href); 
	  var e = document.getElementById('share_menu_'+cart_name_id+'_'+module_id); 
	  jQuery(e).toggle(); 
	 return false; 
  }
  
  function shareIconEmail(el, cart_name_id, module_id) {
	  
	  var e = document.getElementById('share_menu_'+cart_name_id+'_'+module_id); 
	  jQuery(e).toggle(); 
	  return true; 

  }
  
  
  function toClipboard(d) {
  if ((typeof navigator !== 'undefined') && (typeof navigator.clipboard !== 'undefined')) {
	navigator.clipboard.writeText(d);
	}
	}
  
  function shareIcon(cart_name_id, module_id, $cart_name) {
	  var e = document.getElementById('share_menu_'+cart_name_id+'_'+module_id); 
	  jQuery(e).toggle(); 
	  return false; 
  }
  
  function alterDisplayFields(el, module_id) {
	  var fromwrap = document.getElementById('cartsaverform_'+module_id);
	  if (el.value === '') {
	     fromwrap.className = el.form.className.split('is_not_empty').join('')+' is_empty'; 
	  }
	  else {
		  fromwrap.className = el.form.className.split('is_empty').join('')+' is_not_empty'; 
	  }
	  
  }