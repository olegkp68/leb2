function opc_mon_change(el, vmid, runText)
 {

	
 }
 
 function saveMon()
 {
  

 }
 function restoreMon()
 {
   if (typeof mrInit === 'undefined') return;
   var e = jQuery('#mrSelect'); 
   if (e.length)
   if (!e.data('init_done')) {
	   e.data('init_done', true);
	   mrInit('isReady');
	   
   }
   

 } 
 
 

 var opc_last_zas = 0; 