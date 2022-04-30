function opc_zas_show() {
	 
 }
 
 function opc_zas_change(el, vmid, runText)
 {
	 
  
	 
   var id = el.options[el.selectedIndex].value; 
   var branch_id = id; 
   
   var ctp3 = true; 
   if (typeof runText != 'undefined')
   if (runText == false)
   ctp3 = false; 
  
  /*
   var d = document.getElementById('zas_branch_'+opc_last_zas);
   if (d != null) 
   d.style.display = 'none'; 
    opc_last_zas = id; 
   var d = document.getElementById('zas_branch_'+id); 
   if (d != null) 
   d.style.display = 'block'; 
  */
   
   //var branch_id = document.getElementById('branch_id'+id).value; 
   document.getElementById('branch_id').value= branch_id; 
 //   document.getElementById('branch_currency').value= document.getElementById('branch_currency'+id).value; 
 //	 document.getElementById('branch_name_street').value= document.getElementById('branch_name_street'+id).value; 
	 
	var d = document.getElementById('zasilkovna_shipment_id_'+vmid);
	if (d != null)
	{
		
		var je = jQuery(d); 
		var data = { 'branch_id': branch_id }
		je.data('json', data); 
		
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
		
	//d.setAttribute('checked', true); 
	//d.setAttribute('selected', true); 
	if (id == "") 
	 {
	   d.value=d.value+"|choose_shipping"; 
	 
	 }
     else
	 {
      d.value = vmid; 
	  d.setAttribute('saved_id', 'zasilkovna_shipment_id_'+vmid+'_'+id); 
	 }
	 
	 if (ctp3)
	 Onepage.changeTextOnePage3(op_textinclship, op_currency, op_ordertotal);
	}
	
	
	 var opc_zas_show2 = function(xmlhttp2_local) {
	   
  if (xmlhttp2_local.readyState==4 && xmlhttp2_local.status==200)
     {
       // here is the response from request
	   if (typeof  xmlhttp2_local.responseText !== 'undefined') {
         var resp = xmlhttp2_local.responseText;
		 
		 var reta = Onepage.parseJson(resp); 
		 
		 var d = document.getElementById('zasilkovna_resp_'+vmid); 
		 if (d && (typeof reta.shipping_extras !== 'undefined')) {
		  Onepage.setInnerHtml(d, reta.shipping_extras); 
		 }
	   }
	 }	   
	 }
	var cmd = 'getshippingextras&branch_id='+branch_id;
    Onepage.op_runSS(el, false, true, cmd, false, opc_zas_show2); 
 
	return true; 
	
 }
 var storedBranch = null; 
 var storedId = null; 
 
 function saveZasAfterAjax() {
	 if (!storedId) return saveZas();
	 if (storedId === null) return saveZas(); 
	 
 }
 
 function saveZas()
 {
  
   var b = document.getElementsByName('branch'); 
   for (var i=0; i<b.length; i++)
     {
	   if (b[i].options != null)
	   if (b[i].selectedIndex != null)
	   if (b[i].selectedIndex >= 0)
	   {
	    storedId = b[i].id;
	    storedBranch = b[i].selectedIndex; 
		//Onepage.op_log(storedBranch); 
	    return; 
	   }
	 }
 }
 
 function loadZas(cmd) {
	 if (!cmd) return; 
	 if (cmd.indexOf('getshippingextras')>=0) {
		 return 2; 
	 }
 }
 function restoreZas()
 {
 
   if (typeof storedBranch != 'undefined')
   if (storedBranch != null)
   {
    var d = document.getElementById(storedId); 
	if (d!= null) 
	  {
		
		
		if (d.selectedIndex  !== storedBranch) {
	    d.selectedIndex = storedBranch; 
		var vmid = storedId.split('branchselect_').join(''); 
		opc_zas_change(d, vmid, false); 
		}
	  }
    
   }

 }
 
 function validateZasilkovnaOPC() {
	  var sShipping = Onepage.getVShippingRate();
	 
	  var d = document.getElementById(storedId); 
	if (d != null) 
	  {
		
		
		if (typeof d.selectedIndex !== 'undefined') {
			
		var vmid = storedId.split('branchselect_').join(''); 
		
		if (vmid == sShipping) {
		var dx = document.getElementById('zasilkovna_shipment_id_'+vmid); 
		if (dx != null) {		
		if (d.selectedIndex > 0) {

		
			dx.value = dx.value.split('|choose_shipping').join(''); 
		
		}
		else {
			dx.value = dx.value.split('|choose_shipping').join('');
			dx.value += '|choose_shipping'; 
			
			Onepage.ga(shipChangeCountry, 'Checkout Error'); 
			alert(shipChangeCountry);
			
			
			return false; 
			
		}
		}
		
		
		
		}
		
		}
	  }
 }
 //callAfterAjax 
 
 addOpcTriggerer('callBeforeLoader', 'loadZas(cmd)'); 
 addOpcTriggerer('callBeforeAjax', 'saveZas()'); 
 addOpcTriggerer('callAfterAjax', 'saveZasAfterAjax()'); 
 addOpcTriggerer('callAfterRender', 'restoreZas()'); 
 addOpcTriggerer('callSubmitFunct', 'validateZasilkovnaOPC');
 var opc_last_zas = 0; 