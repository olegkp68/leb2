var globalDropin = null;
var braintreeHelper = {
	instance: null,
	clicked: function(el) {
		var brainTreeConfig = braintreeHelper.getConfigById(el.id); 
		if ((brainTreeConfig) && (!brainTreeConfig.always_open)) { 
		jQuery(el).addClass('was_clicked'); 
		jQuery(el).removeClass('wasnot_clicked'); 
		}
		return true; 
	},
	isBraintree: function() {
		var pe = Onepage.getPaymentElement(); 
		if (pe) {
			var jpe = jQuery(pe); 
			if (jpe.data('braintree')) {
			return pe; 
			}
		}
		return false; 
	},
	onBraintreeSubmitClick: function(ev, el, paymentId) {
		document.getElementById('payment_id_'+paymentId).checked = true; 
		Onepage.validateFormOnePagePrivate(null, null, false, false);
		return false; 
		return Onepage.validateFormOnePage(ev, el, true);
		/*
		var brainTreeConfig = braintreeHelper.getConfigById(el.id); 
		if ((brainTreeConfig.e) && (!brainTreeConfig.e.checked)) {
			jQuery(brainTreeConfig.e).click(); 
		}
		return Onepage.validateFormOnepage(ev, el, true); 
		*/
	},
	init: function() {
    
	//var pe = Onepage.getPaymentElement(); 
	jQuery('input[name=virtuemart_paymentmethod_id]').each( function() {
	var pe = this; 
	
	if (pe) {
		var jpe = jQuery(pe); 
		var brainTreeConfig = jpe.data('braintree'); 
		if (brainTreeConfig) {
			
			
			
			
			if (!jpe.data('inrecursion')) {
			jpe.data('inrecursion', true); 
			
			/*
			if (!Onepage.validateFormOnePagePrivate(null, null, true, true)) {
				jpe.data('inrecursion', false);
			}
			*/
			
			jpe.data('inrecursion', false);
			
			}
			
			
			if (jpe.data('braintree_done')) {
			var paymentId = pe.value; 
			var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.show(); 
			}
			}
		}
		var paymentId = pe.value; 
		jQuery('.braintree_container_wrap').each( function() {
			if (this.id !== 'braintree_container_wrap_-'+paymentId) {
				var brainTreeLocalConfig = braintreeHelper.getConfigById(this.id); 
				if ((brainTreeLocalConfig) && (!brainTreeLocalConfig.always_open)) { 
					jQuery(this).hide(); 
				}
			}
			else {
				
			}
		});
		
		jQuery('.was_clicked .braintree_container_wrap').each( function() {
			if (this.id === 'braintree_container_wrap_-'+paymentId) {
				jQuery(this).show(); 
			}
		});

		jQuery('.label_payment').each( function() {
			if (this.id != 'label_payment_-'+paymentId) {
				var brainTreeLocalConfig = braintreeHelper.getConfigById(this.id); 
				if ((brainTreeLocalConfig) && (!brainTreeLocalConfig.always_open)) { 
					jQuery(this).removeClass('was_clicked'); 
					jQuery(this).addClass('wasnot_clicked'); 
				}
				
			}
			else {
				
			}
		});		
		
		if ((brainTreeConfig) && (!jpe.data('braintree_done'))) {
			
			/*
			braintree.client.create({
			authorization: jQuery('#braintree_clienttoken').val(),
			}, function (err, clientInstance) {
				// Creation of any other components...
				braintree.dataCollector.create({
				client: instance,
				paypal: {
					flow: 'checkout',
					amount: order_total,
					currency: 'USD'
				}
			}, function (err, dataCollectorInstance) {
			if (err) {
				// Handle error in creation of data collector
			return;
			}
				// At this point, you should access the dataCollectorInstance.deviceData value and provide it
				// to your server, e.g. by injecting it into your form as a hidden input.
				var deviceData = dataCollectorInstance.deviceData;
				jQuery('#braintree_devicedata').val(deviceData); 
				});
					
				});

			*/
			
			var myTotals = Onepage.getTotalsObj(); 
			var CLIENT_AUTHORIZATION = jQuery('#braintree_clienttoken').val();
			var order_total = parseFloat(myTotals.order_total.value).toFixed(2);
			var braintree_currency_iso = document.getElementById('braintree_currency_iso').value; 
			var order_currency = braintree_currency_iso;
			
			var methodType = jpe.data('braintreemethod'); 
			
			var brainTreeCreateOptions = {
				authorization: jQuery('#braintree_clienttoken').val(),
				threeDSecure: true,
				
				
				
				
				translations: {
					postalCodeLabel: 'Billing address Postal Code',   //https://github.com/braintree/braintree-web-drop-in/blob/master/src/translations/en_US.js
					// Any other custom translation strings...
				},
				card: {
					
					overrides: {
						fields: {
							postalCode: {   //https://braintree.github.io/braintree-web-drop-in/docs/current/module-braintree-web-drop-in.html#~cardCreateOptions
								placeholder: 'Billing address Postal Code',
								formatInput: true // Turn off automatic formatting
								
							}
					
							}
					}
				},
				container: '#braintree-dropin-container-'+paymentId
				};
			var braintreeMethodRendered = false; 
			if (methodType === 'card') {
				
			
			
			if (brainTreeConfig.prefillzip) {
				brainTreeCreateOptions.card.overrides.fields.postalCode.prefill = adminForm.zip_field.value;
			}
			}
			
			if (brainTreeConfig.kount) {
				brainTreeCreateOptions.dataCollector = {};
				brainTreeCreateOptions.dataCollector.kount = {environment: brainTreeConfig.mode}; 
			}
			if (!brainTreeConfig.threed) {
				brainTreeCreateOptions.threeDSecure = false; 
			}
			
			if (methodType === 'paypal') {
				
				if (typeof brainTreeCreateOptions.dataCollector === 'undefined') {
					brainTreeCreateOptions.dataCollector = {}; 
				}
				
				brainTreeCreateOptions.paypal = {
					flow: 'checkout',
					amount: order_total,
					currency: braintree_currency_iso
				}
				return braintreeHelper.brainTreePaypal(pe); 
				
				
			}
			if (methodType === 'paypal') {
				brainTreeCreateOptions.applePay = {
					displayName: 'Merchant Name',
					paymentRequest: {
						label: 'Localized Name',
						total: order_total
					}
				}
			}
			
			if (!braintreeMethodRendered)
					braintree.dropin.create(brainTreeCreateOptions, function (createErr, instance) {
						
						 if (createErr) {
							var obj = {};
							obj.error = createErr;
							obj.billingAddress = braintreeHelper.getBillingAddress();
							Onepage.writeLog('DropIn Create Error', 'brainTree', obj); 
							
						  }
						  else {
							window.globalDropin = instance;
							jpe.data('braintree_done', true);
						  }
					
					
					/*jQuery('div.confirm_btn_wrap_card').show(); */
					jQuery('.was_clicked #braintree_container_wrap_'+paymentId).show(); 
					jQuery('#braintree-dropin-container-'+paymentId).show(); 
					
			
			if (methodType === 'card') {
			var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.show(); 
			}
			}
					jpe.data('inrecursion', false);
					jpe.data('braintree_done', true);
				
				}
				)
				
		}
		else
		if ((jpe.data('braintree')) && (jpe.data('braintree_done'))) {
			jpe.data('inrecursion', false);
			jQuery('#braintree_container_wrap_'+paymentId).show(); 
			jQuery('#braintree-dropin-container-'+paymentId).show(); 
			
		}
		else 
		if (!jpe.data('braintree')) {
			
			//jQuery('.braintree_container_wrap').hide(); 
			
		}
	}
	});
	
	
	return true; 
    
	},

	createFormElements() {
		var test = document.getElementById('braintree_nounce'); 
		if (test) return;
		var opcForm = jQuery('#adminForm'); 
		if (!opcForm.length) {
			opcForm = jQuery('#checkoutForm'); 
		}
		if (opcForm.length) {
			opcForm.append('<input type="hidden" id="braintree_nounce" name="braintree_nounce" value="" />');
			opcForm.append('<input type="hidden" id="braintree_devicedata" name="braintree_devicedata" value="" />');
			opcForm.append('<input type="hidden" id="braintree_amount" name="braintree_amount" value="" />');
			opcForm.append('<input type="hidden" id="braintree_currency_id" name="braintree_currency_id" value="" />');
			
		}

		
	},
	brainTreePaypal: function(pe) {
		var jpe = jQuery(pe); 
		var brainTreeConfig = jpe.data('braintree'); 
		var myTotals = Onepage.getTotalsObj(); 
		var CLIENT_AUTHORIZATION = jQuery('#braintree_clienttoken').val();
		var order_total = parseFloat(myTotals.order_total.value).toFixed(2);
		var braintree_currency_iso = document.getElementById('braintree_currency_iso').value; 
		var order_currency = braintree_currency_iso;
			
		brainTreeCreateOptions.applePay = false;
				brainTreeCreateOptions.card = false;
				
				brainTreeCreateOptions.paypal = {
					flow: 'checkout',
					amount: order_total,
					currency: braintree_currency_iso
				}
				var billingAddress = braintreeHelper.getBillingAddress();
				var paypalAddress = {
					recipientName: billingAddress.givenname+' '+billingAddress.surname,
					line1: billingAddress.streetAddress,
					line2: billingAddress.extendedAddress,
					city: billingAddress.locality,
					countryCode: billingAddress.countryCodeAlpha2,
					postalCode: billingAddress.postalCode,
					state: billingAddress.region,
					phone: billingAddress.phoneNumber,
					
					
				};
				/*
				braintree.setup(jQuery('#braintree_clienttoken').val(), "custom", {
					paypal: {
					container: "braintree-dropin-container-'+paymentId",
					singleUse: true, // Required
					amount: order_total, // Required
					currency: order_currency, // Required
					locale: 'en_US',
					enableShippingAddress: true,
					shippingAddressOverride: paypalAddress
					},
				onPaymentMethodReceived: function (payload) {
					if (!braintreeHelper.isBraintree()) return true; 
					
					
					jQuery('#braintree_nounce').val(payload.nonce); 
					Onepage.endValidation(true, true); 
					
					}
				});
				*/
				
				
				braintree.client.create({
				authorization: CLIENT_AUTHORIZATION
				}, function (clientErr, clientInstance) {

  // Stop if there was a problem creating the client.
  // This could happen if there is a network error or if the authorization
  // is invalid.
  if (clientErr) {
    console.error('Error creating client:', clientErr);
    return;
  }

  // Create a PayPal Checkout component.
  //braintree.paypalCheckout.create
  braintree.paypal.create({
    client: clientInstance
  }, function (paypalCheckoutErr, paypalCheckoutInstance) {

    // Stop if there was a problem creating PayPal Checkout.
    // This could happen if there was a network error or if it's incorrectly
    // configured.
    if (paypalCheckoutErr) {
      console.error('Error creating PayPal Checkout:', paypalCheckoutErr);
      return;
    }

    // Set up PayPal with the checkout.js library
    paypal.Button.render({
      env: brainTreeConfig.mode, // Or 'sandbox'
      commit: true, // This will add the transaction amount to the PayPal button

      payment: function () {
        return paypalCheckoutInstance.createPayment({
          flow: 'checkout', // Required
          amount: order_total, // Required
          currency: order_currency, // Required
          enableShippingAddress: true,
          shippingAddressEditable: false,
          shippingAddressOverride: paypalAddress
        });
      },

      onAuthorize: function (data, actions) {
        return paypalCheckoutInstance.tokenizePayment(data, function (err, payload) {
          // Submit `payload.nonce` to your server
		  jQuery('#braintree_nounce').val(payload.nonce); 
		  Onepage.endValidation(true, true); 
        });
      },

      onCancel: function (data) {
        console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));
      },

      onError: function (err) {
        console.error('checkout.js error', err);
      }
    }, '#pay_by_card_'+paymentId).then(function (a1, a2) {
      // The PayPal button will be rendered in an html element with the id
      // `paypal-button`. This function will be called when the PayPal button
      // is set up and ready to be used.
	  console.log(a1); 
	  console.log(a2); 
    });

  });

});
				
				braintreeMethodRendered = true; 
	},
	getConfigById: function(id) {
		var paymentId = id; 
		if (id.toString().indexOf('_') > 0) {
			var e = id.toString().split('_'); 
			paymentId = e[e.length - 1];
		}
		var el = document.getElementById('payment_id_'+paymentId); 
		var brainTreeConfig = jQuery(el).data('braintree');
		if (!brainTreeConfig) {
			brainTreeConfig = {};
		}
		brainTreeConfig.paymentId = paymentId;
		brainTreeConfig.e = el; 
		return brainTreeConfig;
		
	},
  brainTreeCreateNounce: function() {
	  var pe = braintreeHelper.isBraintree(); 
	  if (!pe) return true; 
	  if (jQuery(pe).data('inrecursion')) return true; 
	  if (!window.globalDropin) return true; 
	  
	   
	  
	   var paymentId = pe.value; 
	   
	   jQuery('.opc_payment_wrap').each(function() {
		if (this.className.indexOf('opc_payment_wrap_'+paymentId) < 0) {
			var brainTreeLocalConfig = braintreeHelper.getConfigById(paymentId); 
				if ((brainTreeLocalConfig) && (!brainTreeLocalConfig.always_open)) { 
					jQuery(this).hide(); 
				}
			}
		});
	
	   var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.hide(); 
			}
	   var current_billingAddress = braintreeHelper.getBillingAddress(); 
	   var myTotals = Onepage.getTotalsObj(); 
	   var order_total = parseFloat(myTotals.order_total.value).toFixed(2);
	   
	   jQuery('#braintree_amount').val(order_total); 
	   jQuery('#braintree_currency_id').val(op_virtuemart_currency_id); 
	 
	 window.globalDropin.requestPaymentMethod({
    threeDSecure: {
      amount: order_total,
      email: current_billingAddress.email,
      billingAddress: current_billingAddress
    },
	
  }, function(err, payload) {
    if (err) {
			
			
		  var obj = {};
		  obj.error = err;
		  obj.billingAddress = braintreeHelper.getBillingAddress();
		  Onepage.writeLog('Tokenzation error', 'brainTree', obj); 
		  jQuery('.opc_payment_wrap').each(function() {
			jQuery(this).show(); 
		  }); 	
		
      
      window.globalDropin.clearSelectedPaymentMethod();
	  
	  var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.show(); 
			}
      /*
	  dropinInstance.clearSelectedPaymentMethod();
		errorMessagesDiv.textContent = 'Transaction failed. Please select a different payment method.';
		*/
      Onepage.endValidation(false); 
      return;
    }
	
     if (payload.deviceData) {
		 var deviceDataInput = document.getElementById('braintree_devicedata'); 
		deviceDataInput.value = payload.deviceData
	 }
	 
	 if (payload.liabilityShifted) {
      // Liability has shifted
      jQuery('#braintree_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
  } else if (payload.liabilityShiftPossible) {
    // Liability may still be shifted
    // Decide if you want to submit the nonce
	  var obj = {};
	  obj.error = 'Liability shift possible but liability was not shifted'; 
	  obj.billingAddress = braintreeHelper.getBillingAddress();
	  Onepage.writeLog('3D secure validation failed', 'brainTree', obj);
	  jQuery('#braintree_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
	
	
  } else {
    // Liability has not shifted and will not shift
    // Decide if you want to submit the nonce
	jQuery('#braintree_nounce').val(payload.nonce); 
	Onepage.endValidation(true, true); 
    return;
	
  }
	 /*
    if (!payload.liabilityShifted) {
      console.log('Liability did not shift', payload);
      //showNonce(payload, false);
	  jQuery('#braintree_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
    }
	*/
    console.log('verification success:', payload);
    //showNonce(payload, true);
	jQuery('#braintree_nounce').val(payload.nonce); 
	Onepage.endValidation(true, true); 
      // send nonce and verification data to your server
  });
	  
	 
		
	//special return code which means that the payment method will take care with itself:
	return -1; 
  },
  getBillingAddress: function() {
	  
	  var country2code = ''; 
	  var stateName = ''; 
	  
	  var s = document.getElementById('virtuemart_state_id'); 
	  var sel_country = 0; 
	  var ba = Onepage.getElementById("country_field");
	    if (ba!=null)
	    sel_country = ba.value;
		else
		{
	     ba = Onepage.getElementById('virtuemart_country_id');
		 if (ba != null)
		 {
		 if ((typeof ba.options != 'undefined') && (ba.options != null))
		  sel_country = ba.options[ba.selectedIndex].value;
		 else
		 if ((ba != null) && (ba.value != null)) sel_country = ba.value;
		 }
		}
	  
	  if (sel_country)
	  if (typeof s.selectedIndex !== 'undefined') {
		  var sID = s.options[s.selectedIndex].value; 
		  if ((sID !== 0) && (sID !== '')) {
		  var statefor = eval('OPCStates.state_for_'+sel_country);   
		  if (typeof statefor[sID] !== 'undefined') {
			  stateName = statefor[sID]; 
		  }
		  }
	  }
	  
	  if (typeof OPCcountryList !== 'undefined') {
		  if (typeof OPCcountryList[sel_country] !== 'undefined') {
			  country2code = OPCcountryList[sel_country]['c2']; 
		  }
	  }
	  
	  
	  var billingAddress = {
		  email: Onepage.getElementById('email_field').value,
		  givenName: Onepage.getElementById('first_name_field').value,
		  surname: Onepage.getElementById('last_name_field').value,
		  phoneNumber: Onepage.getElementById('phone_1_field').value.replace(/[\(\)\s\-]/g, ''),
		  streetAddress: Onepage.getElementById('address_1_field').value,
		  locality: Onepage.getElementById('city_field').value,
		  postalCode: Onepage.getElementById('zip_field').value,
		  countryCodeAlpha2: country2code
	  }
	  if (stateName !== '') {
		billingAddress.region = stateName;
	  }
	  else {
		  billingAddress.region = '';
	  }
	  var a2 = Onepage.getElementById('address_2_field');
	  if (a2) {
		  billingAddress.extendedAddress = a2.value; 
	  }
	  else {
		  billingAddress.extendedAddress = ''; 
	  }
	  return billingAddress;
  },
	start: function() {
		braintreeHelper.createFormElements();
		var testT = jQuery('#braintree_clienttoken').val(); 
		if (testT === '') {
			braintreeHelper.getClientToken();
		}
		else {
			braintreeHelper.init(); 
		}
		/*
		var pe = Onepage.getPaymentElement(); 
		if (pe) {
		var jpe = jQuery(pe); 
		if (jpe.data('autosubmit')) {
			if (typeof Onepage !== 'undefined') {
				Onepage.validateFormOnePage(event, adminForm.confirmbtn_button, true);
			}
		}
		}
		*/
		
	},
	getClientToken: function() {
  
  var url = jQuery('#braintree_clienttoken').data('tokenurl'); 
  
  
  
  if (!url) {
	  return braintreeHelper.invalidateMethod(); 
  }
  
  jQuery.ajax({
				type: 'POST',
				url: url,
				data: [],
				cache: false,
				dataType: 'json',
				contentType: 'application/json; charset=utf-8',
				success: function(data) {
					if (typeof data.clientToken !== 'undefined') {
						braintreeHelper.onFetchClientToken(data.clientToken);  
					}
				},
				complete: function(jqXHR, textStatus) { 
					
				},
				async: true,
				timeout: 30000,
				error: function(error) {
					
				}
				
				});
  /*
  var xhr = new XMLHttpRequest();
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 201) {
	  var resp = JSON.parse(xhr.responseText); 
      braintreeHelper.onFetchClientToken(resp.clientToken);  
    }
  };
  xhr.open("GET", url, true);
  xhr.send(); 
  */
},
onFetchClientToken: function(clientToken) {
   jQuery('#braintree_clienttoken').val(clientToken);
   return braintreeHelper.init(); 
}
}
  
  if (typeof addOpcTriggerer !== 'undefined') {
	addOpcTriggerer('callSubmitFunct', 'braintreeHelper.brainTreeCreateNounce');
	//addOpcTriggerer('callAfterPaymentSelect', 'braintreeHelper.start()'); 
  }
  
  jQuery( document ).ready( function() {
	  braintreeHelper.start();
  }); 
 
  
 /* 
  var dropin;
var payBtn = document.getElementById('pay-btn');
var nonceGroup = document.querySelector('.nonce-group');
var nonceInput = document.querySelector('.nonce-group input');
var nonceSpan = document.querySelector('.nonce-group span');
var payGroup = document.querySelector('.pay-group');
var billingFields = [
  'email',
  'billing-phone',
  'billing-given-name',
  'billing-surname',
  'billing-street-address',
  'billing-extended-address',
  'billing-locality',
  'billing-region',
  'billing-postal-code',
  'billing-country-code'
].reduce(function (fields, fieldName) {
  var field = fields[fieldName] = {
    input: document.getElementById(fieldName),
    help: document.getElementById('help-' + fieldName)
  };
  
  field.input.addEventListener('focus', function() {
    clearFieldValidations(field);
  });

  return fields;
}, {});
*/
function autofill(e) {
  e.preventDefault();

  billingFields.email.input.value = 'your.email@email.com';
  billingFields['billing-phone'].input.value = '123-456-7890';
  billingFields['billing-given-name'].input.value = 'Jane';
  billingFields['billing-surname'].input.value = 'Doe';
  billingFields['billing-street-address'].input.value = '123 XYZ Street';
  billingFields['billing-locality'].input.value = 'Anytown';
  billingFields['billing-region'].input.value = 'State';
  billingFields['billing-postal-code'].input.value = '12345';
  billingFields['billing-country-code'].input.value = 'US';
  
  Object.keys(billingFields).forEach(function (field) {
    clearFieldValidations(billingFields[field]);
  });
}



function clearFieldValidations (field) {
  field.help.innerText = '';
  field.help.parentNode.classList.remove('has-error');
}



function validateBillingFields() {
  var isValid = true;

  Object.keys(billingFields).forEach(function (fieldName) {
    var fieldEmpty = false;
    var field = billingFields[fieldName];

    if (field.optional) {
      return;
    }

    fieldEmpty = field.input.value.trim() === '';

    if (fieldEmpty) {
      isValid = false;
      field.help.innerText = 'Field cannot be blank.';
      field.help.parentNode.classList.add('has-error');
    } else {
      clearFieldValidations(field);
    }
  });

  return isValid;
}

function invalidateMethod() {
	
}


function setupDropin (clientToken) {
  return braintree.dropin.create({
    authorization: clientToken,
    container: '#drop-in',
    threeDSecure: true
  })
}


function setupForm() {
  enablePayNow();
}

function enablePayNow() {
  payBtn.value = 'Pay Now';
  payBtn.removeAttribute('disabled');
}

function showNonce(payload, liabilityShift) {
  nonceSpan.textContent = "Liability shifted: " + liabilityShift;
  nonceInput.value = payload.nonce;
  payGroup.classList.add('hidden');
  payGroup.style.display = 'none';
  nonceGroup.classList.remove('hidden');
}
/*
payBtn.addEventListener('click', function(event) {
  payBtn.setAttribute('disabled', 'disabled');
  payBtn.value = 'Processing...';
  
  var billingIsValid = validateBillingFields();
  
  if (!billingIsValid) {
    enablePayNow();
    
    return;
  }
  var current_billingAddress = getBillingAddress(); 
  dropin.requestPaymentMethod({
    threeDSecure: {
      amount: '100.00',
      email: billingFields.email.input.value,
      billingAddress: current_billingAddress
    }
  }, function(err, payload) {
    if (err) {
      console.log('tokenization error:');
      console.log(err);
      dropin.clearSelectedPaymentMethod();
      enablePayNow();
      
      return;
    }
      
    if (!payload.liabilityShifted) {
      console.log('Liability did not shift', payload);
      showNonce(payload, false);
      return;
    }

    console.log('verification success:', payload);
    showNonce(payload, true);
      // send nonce and verification data to your server
  });
});
*/
