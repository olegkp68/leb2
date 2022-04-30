

function checkOptions(country_id) {
	
	jQuery('.zasielka_select').each( function() {
		
		var selected_was_changed = false; 
		var changedEl = null; 
		
		var jel = jQuery(this); 
		var el = this; 
		var selectId = el.id; 
		var countries = jel.data('countries'); 
		for (var i=0; i<countries.length;  i++) {
			var select_country_id = countries[i]; 
			if (select_country_id != country_id) {
				continue; 
			}
	var branches = window.zasilkovnaCountryBranches;
	
	var wasLoaded = jel.data('loaded_'+country_id); 
	if (!wasLoaded) wasLoaded = false; 
	
	
					var stored = jQuery('body').data(selectId); 
					var selectedId = ''; 
					if (stored) {
							selectedId = stored.storedId; 
					}
					else {
						   selectedId = jel.data('default'); 
						
					}
					
	if ((el.options.length === 1) || (!wasLoaded)) {
		if (typeof branches[country_id] !== 'undefined') {
			
			for (var i = 0; i < branches[country_id].length; i++) {
				{
					var broption = document.createElement('option'); 
					var current = branches[country_id][i]; 
					for (var atr in current) {
							if (!current.hasOwnProperty(atr)) continue; 
							
							broption[atr] = current[atr]; 
					}
					
					if (broption.value == selectedId) {
						broption.selected = true; 
						selected_was_changed = selectedId; 
						changedEl = el; 
					}
					
					el.add(broption); 
				}
			}
			jel.data('loaded_'+country_id, true); 
		}
	}
	}
	if (selected_was_changed == false) {
		selected_was_changed = jQuery(this).val(); 
	}
	if (selected_was_changed) {
		loadShowBranch(this, selected_was_changed); 
	}
	
});
}	

function getNowAntiCache() {
	var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
	var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
    var hours = today.getHours(); 
	
	var ret = mm + '-' + dd + '-' + yyyy + '-' + hours;
	
	return ret; 
}
function getNowAntiCacheDay() {
	var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
	var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
    var hours = today.getHours(); 
	
	var ret = mm + '-' + dd + '-' + yyyy;
	
	return ret; 
}

function populateOptions() {
	if (typeof zasilkovnaCountryBranches === 'undefined') {
		window.zasilkovnaCountryBranches = []; 
	}
	jQuery('.zasielka_select').each( function() {
		var jel = jQuery(this); 
		var currentEl = this; 
		var selectId = currentEl.id; 
		var countries = jel.data('countries'); 
		for (var i=0; i<countries.length;  i++) {
			var country_id = countries[i]; 
			if (typeof window.zasilkovnaCountryBranches[country_id] === 'undefined')
			{
				var exists = document.getElementById('zasilkovnaCountryBranches_'+country_id); 
				if (!exists) {
					var js = document.createElement('script');
					js.onload = js.onreadystatechange = function() {
					var state = this.readyState;
					if( !this.loaded && ( !state || state=='loaded' || state=='complete' ) ) {
					this.loaded = 1;
					checkOptions(country_id); 
					}
				}
					js.id = 'zasilkovnaCountryBranches_'+country_id; 
					var now = getNowAntiCache(); 
					js.src = zasilkovnaRoot+'media/zasilkovnaopc/js/zasilkovnaCountryBranches_'+country_id+'.js?'+now; 
					document.getElementsByTagName('head')[0].appendChild(js);
				}
				else {
					//zrejme 404-ka
					
				}
			}
			else {
				checkOptions(country_id); 
			}
		}
	}); 
}






function loadShowBranch(el, id) {
	var jel = jQuery(el); 
	var vmid = jel.data('vmid'); 
	
	var current_vmid = Onepage.getVShippingRate(); 
	
	if (vmid != current_vmid) {
		jQuery('#opc_zas_place_'+vmid).hide(); 
		return; 
	}
	
	var id = parseInt(id); 
	if (isNaN(id) || (id === 0)) {
		jQuery('#opc_zas_place_'+vmid).html(''); 
	}
	else {
		var stamp = getNowAntiCacheDay(); 
		var loadUrl = zasilkovnaRoot+'media/zasilkovnaopc/html/branch_'+id.toString()+'.html?now='+stamp; 
		jQuery('#opc_zas_place_'+vmid).load(loadUrl); 
	}
	jQuery('.opc_zas_place').each( function() {
		var jel = jQuery(this); 
		var testvmid = jel.data('vmid'); 
		if (testvmid != vmid) {
			jel.hide(); 
		}
	}); 
	
	jQuery('#opc_zas_place_'+vmid).show(); 
	
}


 function opc_zaschange(el, vmid, runText)
 {
   var id = el.options[el.selectedIndex].value; 
   var selectId = el.id; 
   
		if ((id != '') && (id != 0)) {
		 var obj  = {}; 
	     obj.storedId = id;
	     obj.storedBranch = el.selectedIndex; 
		 jQuery('body').data(selectId, obj); 
		}
   
   
   
   var ctp3 = true; 
   if (typeof runText != 'undefined')
   if (runText == false)
   ctp3 = false; 
  
   loadShowBranch(el, id); 
   
   
 
	 
	var d = document.getElementById('shipment_id_'+vmid);
	if (d != null)
	{
		//pre OPC-ko aby to poslalo cez ajax:
		d.setAttribute('saved_id', 'zasilkovna_shipment_id_'+vmid+'_'+id); 
		
		if (ctp3)
		if (!d.checked)
		{
	if (typeof jQuery != 'undefined') jQuery(d).click(); 
	else
	if (typeof d.click != 'undefined')
	d.click(); 
		
		var isChecked = false; 
		}
		else var isChecked = true; 
		
	
	if (id == "") 
	 { 
	   
	 
	 }
     else
	 {
      
	  
	 }
	 
	 if (ctp3)
	 Onepage.changeTextOnePage3(op_textinclship, op_currency, op_ordertotal);
	}
	return true; 
	
 }

 function saveZas()
 {
   
   //var b = document.getElementsByName('branch'); 
   jQuery('.zasielka_select').each( function() {
	var el = this; 
   
     {
	   if (el.options != null)
	   if (el.selectedIndex != null)
	   if (el.selectedIndex >= 0)
	   {
		var obj  = {}; 
	    obj.storedId = el.options[el.selectedIndex].value;
	    obj.storedBranch = el.selectedIndex; 
		
		
		
	    var selectId = el.id; 
		if ((obj.storedId !== '') && (obj.storedId != 0)) {
			jQuery('body').data(selectId, obj); 
		}
	   }
	 }
   });
 }
 function restoreZas()
 {
	 return populateOptions(); 
	 
   jQuery('.zasielka_select').each( function() {
	   var id = this.id; 
	   var stored = jQuery('body').data(id); 
	   
	   if (!stored) {
		   stored = {};
		   stored.storedId = jQuery(this).data('default'); 
		   stored.storedBranch = 0; 
	   }
	   else {
		   
	   }
	   
	   if (stored) {
		   if ((stored.storedBranch) && (typeof this.options[stored.storedBranch] !== 'undefined')) {
			   
		   var opt = this.options[stored.storedBranch]; 
		   if (opt.value = stored.storedId) {
		   this.selectedIndex = stored.storedBranch;
		   var vmid = jQuery(this).data('vmid'); 
		   opc_zaschange(this, vmid, false); 
		   }
		   else {
			   //reload select
			   populateOptions(); 
		   }
		   }
		   else {
			   //reload select
			   populateOptions(); 
		   }
		   
	   }
   }); 
   

 }
 
 function validateZasilkovna()
 {
	 var vmid = Onepage.getVShippingRate(); 
	 
	 var ppp = document.getElementById('shipment_id_'+vmid);
	 var ppp2 = document.getElementById("branchselect_"+vmid);
 if ((ppp != null) && (ppp2 != null))
 if (ppp.checked == true && (ppp2.value == 0 || ppp2.value == null)) {
    
	ppp2.className += " invalid "; 
	
	if (typeof zasilkovnaChyba[vmid] !== 'undefined') {
	 alert(zasilkovnaChyba[vmid]); 
	}
	else {
		alert('Vyberte pobočku Zasílkovny'); 
	}
	
    return false; 
    }
 }
 
 if (typeof addOpcTriggerer != 'undefined')
 {
   addOpcTriggerer('callSubmitFunct', 'validateZasilkovna');
 
	addOpcTriggerer('callBeforeAjax', 'saveZas()'); 
	addOpcTriggerer('callAfterRender', 'restoreZas()'); 
 	addOpcTriggerer('callAfterShippingSelect', 'populateOptions()'); 
 }
 

 
