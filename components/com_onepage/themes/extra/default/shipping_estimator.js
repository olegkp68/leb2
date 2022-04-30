var sEstimator =  {
getRates : function(el) {
	var q = ''; 
	if (typeof Onepage === 'undefined') return; 
	
	if ((typeof window.estimator_fields !== 'undefined') && (window.estimator_fields)) {
		q = Onepage.buildExtra(window.estimator_fields, false, false, false); 
	}
	
	Onepage.op_runSS(el, true, true, 'estimator'+q, false, function(xmlhttp2_local, el) { return sEstimator.estimatorResponse(xmlhttp2_local, el); } ); 
},
estimatorResponse: function(xmlhttp2_local, el) {
	
	
	if ((typeof xmlhttp2_local !== 'undefined') && (xmlhttp2_local)) {
		if (xmlhttp2_local.readyState==4 && xmlhttp2_local.status==200) {
			
			if ((typeof xmlhttp2_local.isJsonObject === 'undefined') || (!xmlhttp2_local.isJsonObject)) {
			 var resp = xmlhttp2_local.responseText;
			 var reta = Onepage.parseJson(resp); 
			}
			else {
				var resp = ''; 
				var reta = xmlhttp2_local; 
			}
			
			
			var d = document.getElementById('estimator_shipping_rates_come_here'); 
			if (typeof reta.estimator_shipping_html !== 'undefined') {
			  Onepage.setInnerHtml(d, reta.estimator_shipping_html); 
			   
			   /*
			  var template = document.querySelector('#shipping_estimator_shadow');
			  var shadow = document.querySelector('#shipping_estimator_content').createShadowRoot();
			 
				var clone = document.importNode(template.content, true);
				shadow.appendChild(clone);
			  */
			  
			}
			else {
				Onepage.setInnerHtml(d, resp); 
			}
		}
	}
  //do not run rest of OPC code... 
  return true; 	
}
}

document.addEventListener('onOpcLoaded', function (e) {
  
  // e.target matches elem
  var el = jQuery('.estimator_triggerer'); 
	if (el.length > 0) {
		el.change( function() {
			sEstimator.getRates(this); 
		});
		
		
		if (typeof el[0] !== 'undefined') {
			sEstimator.getRates(el[0]); 
		}
		}
}, false);


	
