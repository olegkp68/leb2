
function gtm_addClickEvent(el) {
	var jel = jQuery(el); 
	jel.on('click', function(event) {
		window.gtm_feedDataLayer(this, event); 
	}); 
}
function gtm_feedDataLayer(el, evt) {
	
	//addtocart data are binded upon view
	//product data are binded and executed upon view
	//click data are binded with click and executed upon click
	//console.log('gtm_feedDataLayer', el); 
	var jel = jQuery(el); 
	var data = jel.data('ga'); 
	if (data) {
		if (data.a === 'add') {
				//find cart element inside
				var cartFormsGTM = jel.parents('form.product');
				if (cartFormsGTM.length > 0) {
					cartFormsGTM.each( function() {
						var productform = jQuery(this); 
						var jelcart = productform.find('button[name="addtocart"], input[name="addtocart"], a[name="addtocart"]');
						if (jelcart.length > 0) {
							data.srcElement = jelcart[0]; 
							jelcart.data('ga', data); 
						}
					}); 
				}
				
		}
		else {
		
		data.srcElement = el; 
			
		if (typeof evt !== 'undefined') {
		 gtmProductEvent(data, evt); 
		}
		else {
			gtmProductEvent(data); 
		}
		}
	}
	
	 
	
}
function gtmPrepareDatalayerImpressions(productObj, evt) {
	
	gtmProductEvent(productObj, evt, true); 
}
function gtmPushImpressions() {
	if (typeof gtmProductEvent.iLoaded !== 'undefined')
		if ((typeof gtmProductEvent.ecommercePushed === 'undefined') || (!gtmProductEvent.ecommercePushed))
		if (gtmProductEvent.iLoaded.length > 0) {
			
			if (gtmProductEvent.lastImpressionEvent !== 'scroll') {
			
			
	dataLayer.push({
		'event': gtmProductEvent.lastImpressionEvent,
		'ecommerce': {
			'currencyCode': gtmProductEvent.lastCurrency,
			'impressions': gtmProductEvent.iLoaded
		}
	  });
	  
	  }
	  else {
		  dataLayer.push({
		'ecommerce': {
			'currencyCode': gtmProductEvent.lastCurrency,
			'impressions': gtmProductEvent.iLoaded
		}
	  });
	  }
	  gtmProductEvent.ecommercePushed = true; 
	  gtm_log('GTM: dataLayer was modified'); 
		}
}
function gtmProductEvent(productObj, evt, dontPush) {
  
  if (typeof productObj.srcElement !== 'undefined') {
	  if (productObj.srcElement.running) {
		  return true; 
	  }
	  productObj.srcElement.running = true; 
  }
  if ((typeof dontPush === 'undefined') || (!dontPush)) {
	  dontPush = false; 
  }
  else {
	  dontPush = true; 
  }
  
	/*
  if ((productObj.a === 'click') || (productObj.a === 'add')) {
	  
	  if (typeof evt !== 'undefined')
	  if (typeof evt.preventDefault !== 'undefined') {
		console.log(evt); 
		evt.preventDefault(); 
	  }
  }
  */
  if (productObj.a === 'click') {
  dataLayer.push({
    'event': productObj.e,
    'ecommerce': {
	  'currencyCode': productObj.m,
       'click': {
        'actionField': {'list': productObj.l},      // Optional list property.
        'products': [{
          'name': productObj.n,                      // Name or ID is required.
          'id': productObj.i.toString(),
          'price': productObj.p.toString(),
          'brand': productObj.b,
          'category': productObj.c,
          'variant': productObj.v,
          'position': 1
         }]
       }
     },
     'eventCallback': function() {
	   gtm_log('link product data sent to GTM');
	  
	   //productObj.srcElement.dispatchEvent(evt.originalEvent); 	   
       //jQuery(productObj.srcElement).trigger(evt); 
     }
  });
  
  /*
  if (typeof productObj.srcElement !== 'undefined') {
  window.setTimeout(function(){ 
	jQuery(productObj.srcElement).trigger(evt); 
   }, 
  1000); 
  }
  */
  }
  
  if (typeof gtmProductEvent.pLoaded == 'undefined') {
		  gtmProductEvent.pLoaded = []; 
	  }
	   if (typeof gtmProductEvent.iLoaded == 'undefined') {
		  gtmProductEvent.iLoaded = []; 
	  }
	   if (typeof gtmProductEvent.aLoaded == 'undefined') {
		  gtmProductEvent.aLoaded = []; 
	  }
	  
  if (productObj.a === 'impressions') {
	  
	  if (typeof gtmProductEvent.scrollPosition == 'undefined') {
		  gtmProductEvent.scrollPosition = 1; 
	  }
	  
	  
	  gtmProductEvent.iLoaded.push({
			'name': productObj.n,                      // Name or ID is required.
            'id': productObj.i.toString(),
            'price': productObj.p.toString(),
            'brand': productObj.b,
            'category': productObj.c,
            'variant': productObj.v,
			'list': productObj.l,      // the 'list' could also be on homepage, search results, promotion page, etc
			'position': gtmProductEvent.scrollPosition
			}); 

	  /*updated on 5 june 2019
	  if (!dontPush) {
	  dataLayer.push({
		'event': productObj.e,
		'ecommerce': {
			'currencyCode': productObj.m,
			'impressions': gtmProductEvent.iLoaded
		}
	  });
	  }
	  */
	  if (!dontPush) {
	  dataLayer.push({
		'ecommerce': {
			'currencyCode': productObj.m,
			'impressions': gtmProductEvent.iLoaded
		}
	  });
	  }
	  gtmProductEvent.lastImpressionEvent = productObj.e;
	  gtmProductEvent.lastCurrency = productObj.m;
	  gtmProductEvent.scrollPosition++; 
  }
  if (productObj.a === 'detail') {
	  
	  gtmProductEvent.pLoaded.push({
				'name': productObj.n,                      // Name or ID is required.
				'id': productObj.i.toString(),
				'price': productObj.p.toString(),
				'brand': productObj.b,
				'category': productObj.c,
				'variant': productObj.v
			}); 
	  
	  dataLayer.push({
		'event': productObj.e,
		'ecommerce': {
			/*'currencyCode': productObj.m,*/
			'detail': 
			{
			'actionField': {'list': productObj.l},      // Optional list property.
			'products': gtmProductEvent.pLoaded
			
			}
		}
	  });
	  gtmProductEvent.ecommercePushed = true; 
	  
  }
  
  if (productObj.a === 'add') {
	  
	  gtmProductEvent.aLoaded.push({
				'name': productObj.n,                      // Name or ID is required.
				'id': productObj.i,
				'price': productObj.p.toString(),
				'brand': productObj.b,
				'category': productObj.c,
				'variant': productObj.v,
				'quantity': 1
			}); 
	  
	  dataLayer.push({
		'event': productObj.e,
		'ecommerce': {
			'currencyCode': productObj.m,
			'add': {
			'products': gtmProductEvent.aLoaded
			}
		},
		'eventCallback': function() {
		 gtm_log('addToCart product data sent to GTM'); 
		 //jQuery(productObj.srcElement).trigger(evt); 
		}
	  });
	  /*
	  if (typeof productObj.srcElement !== 'undefined') {
  window.setTimeout(function(){ 
	jQuery(productObj.srcElement).trigger(evt); 
   }, 
  1000); 
  }
  */
  }
  if (!dontPush) {
   gtm_log('GTM: dataLayer was modified'); 
  }
  
  
}
function gtm_log(msg, obj1, obj2) {
	if (typeof window.dataLayer == 'undefined') return; 
	if (typeof console !== 'undefined') 
if (typeof console.log === 'function') {
			console.log(msg, window.dataLayer); 
			if (typeof obj1 !== 'undefined') {
				console.log(obj1);
			}
			if (typeof obj2 !== 'undefined') {
				console.log(obj2);
			}
}
		   
}



/*image lazyload*/
if ((typeof opc_gtm_scroll_enabled !== 'undefined') && (opc_gtm_scroll_enabled)) {
document.addEventListener("DOMContentLoaded", function() {
	
	if ("IntersectionObserver" in window) {
    var lazyTagObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
       if ((typeof entry.isIntersecting == 'undefined') || (entry.isIntersecting)) {
          var lazyTag = entry.target;
		  lazyTagObserver.unobserve(lazyTag);
		  window.gtm_feedDataLayer(lazyTag); 
         
        }
      });
    });
}
	
  var lazyTags = [].slice.call(document.querySelectorAll("*[data-ga]"));

  
	if (typeof lazyTagObserver !== 'undefined') {
    lazyTags.forEach(function(lazyTag) {
	  if (lazyTag.tagName.toUpperCase() === 'GA-PRODUCT') {
       lazyTagObserver.observe(lazyTag);
	  }
	  else {
		  
		  var data = jQuery(lazyTag).data('ga'); 
		  if (data) {
			  if (data.a === 'click') {
				  window.gtm_addClickEvent(lazyTag); 
			  }
		  }
	  }
    });
	}
	else {
		for (var j = 0; j < lazyTags.length; j++){
			    var lazyTag = lazyTags[j]; 
				if (lazyTag.tagName === 'ga-product') {
					var data = jQuery(lazyTag).data('ga'); 
					window.gtm_feedDataLayer(data); 
				}
				else {
					var data = jQuery(lazyTag).data('ga'); 
					if (data) {
					if (data.a == 'click') {
						window.gtm_addClickEvent(lazyTag); 
					}
				}
				}
		   }
	}
  
});	

}
else {
	if (typeof document.addEventListener !== 'undefined')
	document.addEventListener("DOMContentLoaded", function() {
	if (typeof document.querySelectorAll !== 'undefined') {
	var lazyTags = [].slice.call(document.querySelectorAll("*[data-ga]"));
	var data = {}; 
	for (var j = 0; j < lazyTags.length; j++){
			    var lazyTag = lazyTags[j]; 
				
				if (lazyTag.tagName.toLowerCase() === 'ga-product') {
					data = jQuery(lazyTag).data('ga'); 
					if ((data.a !== 'add') && (data.a !== 'remove')) {
					 window.gtmPrepareDatalayerImpressions(data); 
					}
					else {
						window.gtm_addClickEvent(lazyTag); 
					}
				}
				else {
					data = jQuery(lazyTag).data('ga'); 
					if (data) {
					if (data.a == 'click') {
						window.gtm_addClickEvent(lazyTag); 
					}
				}
				}
		   }
		   gtmPushImpressions(); 
	}
	});
}


	if (typeof jQuery !== 'undefined') {
		jQuery(document).ready( function() {
			
			
			var cartFormsGTM = jQuery("form.product"); 
			if (cartFormsGTM.length == 0) return; 
			cartFormsGTM.each( function() {
				var productform = jQuery(this); 
				

				
				var jelcart = productform.find('button[name="addtocart"], input[name="addtocart"], a[name="addtocart"]');
				if (jelcart.length > 0) {
					
				
		
								gtm_log('Add to cart GTM Tracking Attached'); 
							
		
		jelcart.on('click', function(event) {
			
				var gadata = jelcart.data('ga'); 
				if (gadata) {
					var qe = productform.find('input[name="quantity"], input[name="quantity[]"]');
					var selected_quantity = 0; 
					
					if (qe.length) {
						selected_quantity = qe.val(); 
					}
					gadata.q = selected_quantity; 
					jelcart.data('ga', gadata); 
					gtmProductEvent(gadata, event); 
					return true;
				}
			
			
			try {
			
							{
								gtm_log('Add to cart GTM Tracking Click Detected'); 
							}
			
			
			if (typeof dataLayer === 'undefined') return; 
			
			
			
					
					var virtuemart_product_id = productform.find('input[name="virtuemart_product_id[]"]').val();
					if ((typeof virtuemart_product_id !== 'undefined') && (virtuemart_product_id !== 'undefined'))
					{
						if ((typeof _productTrackingData !== 'undefined') && (typeof _productTrackingData[virtuemart_product_id] !== 'undefined')) 
						{
							var productData = _productTrackingData[virtuemart_product_id]; 
							
							dataLayer.push({
								'event': 'addToCart',
								'ecommerce': {
								'currencyCode': productData.productCurrency_currency_code_3,
								'add': {                                // 'add' actionFieldObject measures.
								'products': [{                        //  adding a product to a shopping cart. --> check "1-productinformation.js" for more details about the values under 'products'
									'name': productData.name,
									'id': productData.productPID,              
									'price': productData.productPrice.toString(),                  
									'brand': '',                
									'category': productData.productCategory,                     
									'variant': '', 
									'quantity': 1
							}]
								}
								}
							});
							
							
							
					
  						
							
					
							
							
							{
								gtm_log('Add to cart GTM Tracking executed', dataLayer, productData); 
							}
						}
						else {
							
							
							
						var myUrl = productQueryUrl+'&virtuemart_product_id='+virtuemart_product_id; 
						
						
						
						var x = window.setTimeout( function() {
						jQuery.ajax({ 
						type: 'GET',
						cache: false, 
						dataType: 'json',
						timeout: '10000', 
						url: myUrl, 
						
						data: []
						}).done( function (data, testStatus ) {
							
							
							
						if ((typeof data !== 'undefined') && (data !== null)) {
							if (typeof data.productCurrency_currency_code_3 !== 'undefined') {
								
								
								dataLayer.push({
								'event': 'addToCart',
								'ecommerce': {
								'currencyCode': data.productCurrency_currency_code_3,
								'add': {                                // 'add' actionFieldObject measures.
								'products': [{                        //  adding a product to a shopping cart. --> check "1-productinformation.js" for more details about the values under 'products'
									'name': data.name,
									'id': data.productPID,              
									'price': data.offers.price.toString(),                  
									'brand': '',                
									'category': data.productCategory,                     
									'variant': '', 
									'quantity': 1
							}]
								}
								}
							});
								
								
								
							
								gtm_log('Add to cart GTM Tracking executed', dataLayer, data); 
							
								
								
								
							}
						}
						}).fail( function(err) {
							gtm_log(err); 
						}); 
						}, 2000);
						
					}
					}
			
			} catch(e) {
			return true; 
		}
		});
		
		}
	});
});


//bind product clicks:
jQuery(document).ready( function() {
var productHrefs = jQuery('[data-product]');
if (productHrefs.length) 
productHrefs.on('click', function() {
	var el = jQuery(this); 
	var dp = el.data('product'); 
	if (dp) {
	dataLayer.push({
    'event': 'productClick',
    'ecommerce': {
      'click': {
        'actionField': {'list': 'product click'},      // Optional list for example: category, subcategory, homepage, promotionpage, search results
        'products': [ dp ]
       }
     },
  });
	}
	
}); 
});

}


function callAfterPaymentSelectGTM()
{
	if (typeof Onepage == 'undefined') return; 
	if (typeof jQuery == 'undefined') return; 
	//if (typeof ga == 'undefined') return; 
	
	var payment_id = Onepage.getPaymentId();
	
	if (payment_id == 'payment_id_0') return; 
	if (payment_id == 0) return; 
	
	var my_label = payment_id; 
	
	
	var label = jQuery("label[for='payment_id_"+payment_id+"']"); 
	if (label != null)
	if (typeof label.text != 'undefined')
	{
	  	var my_label2 = label.text(); 
		if (my_label2.length > 0) my_label = my_label2; 
		
		my_label = my_label.split("\r\r\n").join(' ').split("\r\n").join(" ").split("\n").join(" ").trim(); 
	}
	
	onCheckoutOptionGTM(3, my_label); 
	gtm_log('OPC GTM Tracking: Payment selected '+my_label+', step 3'); 
}

function onCheckoutOptionGTM(step, checkoutOption) {
  if (typeof gtmCartProducts === 'undefined') gtmCartProducts = []; 	
  
  var gtmCartProducts2 = []; 
  for (var i=0; i<gtmCartProducts.length; i++) {
	  var current = gtmCartProducts[i]; 
	  delete current.position; 
	  gtmCartProducts2.push(current); 
  }
  var actionField = {}; 
  if (checkoutOption) {
    actionField = {'step': step, 'option': checkoutOption}; 
  }
  else {
	  actionField = {'step': step}; 
  }

  if (typeof dataLayer != 'undefined')
  dataLayer.push({ 
    'event': 'checkoutOption',
    'ecommerce': {
      'checkout': {
        'actionField': actionField,
		'products': gtmCartProducts2
      }
    }
  });
}

function callAfterShippingSelectGTM()
{
	
	 
	 
	if (typeof Onepage == 'undefined') return; 
	if (typeof jQuery == 'undefined') return; 
	//if (typeof ga == 'undefined') return; 
	
	
	 var ship_id = Onepage.getInputIDShippingRate();
	 
	if (ship_id == 'choose_shipping') return; 
	if (ship_id == 'shipment_id_0') return; 
	
	var my_label = ship_id; 
	
	
	var label = jQuery("label[for='"+ship_id+"']"); 
	if (label != null)
	if (typeof label.text != 'undefined')
	{
	  	var my_label2 = label.text(); 
		if (my_label2.length > 0) my_label = my_label2; 
		
		my_label = my_label.split("\r\r\n").join(' ').split("\r\n").join(" ").split("\n").join(" ").trim(); 
	}
	
	
	onCheckoutOptionGTM(2, my_label); 
	gtm_log('OPC GTM Tracking: Shipping selected '+my_label+', step 2'); 
	 
}

function onOpcErrorMessageGTM(msg, cat) {
  onCheckoutOptionGTM(5, cat+': '+msg); 
}

function callSubmitFunctGTM()
{
	onCheckoutOptionGTM(4, 'OPC Confirm Button Clicked'); 
	gtm_log('OPC GTM Tracking: Confirm order button clicked, step 4'); 
}

function GTMcallBeforeProductQuantityUpdate(el, quantity, cart_id) {
	if (!quantity) {
	if (typeof gtmCartProductsIndexed !== 'undefined')
	if (typeof gtmCartProductsIndexed[cart_id] !== 'undefined') {
		dataLayer.push({
		'event': 'removeFromCart',
		'ecommerce': {
		'remove': {    
			'products': [ gtmCartProductsIndexed[cart_id] ]
		}
		}
		}); 
		
		gtm_log('removeFromCart', dataLayer); 
	}
	}
	
}
