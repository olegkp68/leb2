jQuery(document).ready(function() {
	stockAvaiRun(false, false); 
}); 

function setSpin(spinEl, data) {
	var el = jQuery(spinEl); 
	el.html(data.loaderhtml); 
}

function stockAvaiRun(spinEl, nocache) {
	if ((typeof getStockAvaiselector === 'function')) {
		//var insselector = getStockAvaiselector(); 
	}
	
	if ((typeof nocache == 'undefined') || (!nocache)) nocache = false; 
	
	
	var selector = jQuery('.stockavai');
	
	if (selector.length > 0) {
	selector.each( function() { 
	   var jel = jQuery(this); 
	   
	   var data = jel.data('sku'); 
	   
	   if ((typeof spinEl !== 'undefined') && (spinEl)) setSpin(spinEl, data); 
	   if ((typeof data != 'undefined') && (data !== null))
	   if (typeof data.stockurl !== 'undefined') {
		   
		   var stockurl = data.stockurl+'&nocache='+nocache; 
		   var selector = jel.data('selector'); 
		   
		   if (selector) {
			 
		     var insE = eval(selector); 
			 insE.load(stockurl, function() {
				  var el = jQuery(this); 
				  el.trigger('refresh'); 
				  el.trigger('create'); 
				  el.trigger('chosen:updated'); 
				  if (typeof RegularLabsTooltips !== 'undefined') RegularLabsTooltips.init();
				});
			 
			
		   }
		   else {
			 jel.load(stockurl, function() {
				  var el = jQuery(this); 
				  el.trigger('refresh'); 
				  el.trigger('create'); 
				  el.trigger('chosen:updated'); 
				  if (typeof RegularLabsTooltips !== 'undefined') RegularLabsTooltips.init();
				}); 
		   }
		   
		   
	   }
	}); 
	}
	
	return false; 
}

if (typeof stockAvaiSelector === 'undefined') {
  var stockAvaiSelector = ''; 
}