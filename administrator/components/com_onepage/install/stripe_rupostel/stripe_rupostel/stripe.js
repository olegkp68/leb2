var globalDropin = null;
var stripeHelper = {
	instance: null,
	elements: null,
	opcregistered: false,
	clicked: function(el) {
		var stripeConfig = stripeHelper.getConfigById(el.id); 
		if ((stripeConfig) && (!stripeConfig.always_open)) { 
		jQuery(el).addClass('was_clicked'); 
		jQuery(el).removeClass('wasnot_clicked'); 
		}
		return true; 
	},
	isStripe: function() {
		var pe = Onepage.getPaymentElement(); 
		if (pe) {
			var jpe = jQuery(pe); 
			if (jpe.data('stripe')) {
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
		var stripeConfig = stripeHelper.getConfigById(el.id); 
		if ((stripeConfig.e) && (!stripeConfig.e.checked)) {
			jQuery(stripeConfig.e).click(); 
		}
		return Onepage.validateFormOnepage(ev, el, true); 
		*/
	},
	getTotal() {
		var myTotals = Onepage.getTotalsObj(); 
		return parseInt(parseFloat(myTotals.order_total.value).toFixed(2) * 100);
	},
	init: function() {
    
	//var pe = Onepage.getPaymentElement(); 
	jQuery('input[name=virtuemart_paymentmethod_id]').each( function() {
	var pe = this; 
	
	if (pe) {
		var jpe = jQuery(pe); 
		var stripeConfig = jpe.data('stripeconfig'); 
		if (stripeConfig) {
			
			if (!window.stripe) {
				window.stripe = Stripe(stripeConfig.publicKey);
			}
			
			
			
			
			
			if (jpe.data('stripe_done')) {
			var paymentId = pe.value; 
			var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.show(); 
			}
			}
		}
		var paymentId = pe.value; 
		jQuery('.stripe_container_wrap').each( function() {
			if (this.id !== 'stripe_container_wrap_-'+paymentId) {
				var stripeLocalConfig = stripeHelper.getConfigById(this.id); 
				if ((stripeLocalConfig) && (!stripeLocalConfig.always_open)) { 
					jQuery(this).hide(); 
				}
			}
			else {
				
			}
		});
		
		jQuery('.was_clicked .stripe_container_wrap').each( function() {
			if (this.id === 'stripe_container_wrap_-'+paymentId) {
				jQuery(this).show(); 
			}
		});

		jQuery('.label_payment').each( function() {
			if (this.id != 'label_payment_-'+paymentId) {
				var stripeLocalConfig = stripeHelper.getConfigById(this.id); 
				if ((stripeLocalConfig) && (!stripeLocalConfig.always_open)) { 
					jQuery(this).removeClass('was_clicked'); 
					jQuery(this).addClass('wasnot_clicked'); 
				}
				
			}
			else {
				
			}
		});		
		var order_total = stripeHelper.getTotal(); 
		if ((stripeConfig) ) {
			
		
			
			
			var CLIENT_AUTHORIZATION = jQuery('#stripe_clienttoken').val();
			
			var stripe_currency_iso = document.getElementById('stripe_currency_iso').value; 
			var order_currency = stripe_currency_iso;
			
			var methodtype = jpe.data('stripemethod'); 
			
			var BT = stripeHelper.getBillingAddress(); 
			
			
			
			stripeHelper.elements = window.stripe.elements({locale:'auto'});
			
			
if (stripeConfig.methodtype === 'googlePay') {	

var paymentRequest = window.stripe.paymentRequest({
				country: stripeConfig.country,
				currency: stripe_currency_iso.toLowerCase(),
				total: {
					label: 'Order Total',
					amount: order_total,
				},
				requestPayerName: true,
				requestPayerEmail: true,
			});
		
var prButton = stripeHelper.elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
});
		if (typeof stripeStyle === 'undefined') {
			var stripeStyle = {};
		}
		


// Check the availability of the Payment Request API first.
var jx = jQuery('#payment-request-button_'+paymentId); 
if (jx.length)  {
paymentRequest.canMakePayment().then(function(result) {
  if (result) {
	  
	  
	  var wasMountedButton = jQuery('#payment-request-button_'+paymentId).data('mounted'); 
	  if (!wasMountedButton) {
			prButton.mount('#payment-request-button_'+paymentId);
			jQuery('#payment-request-button_'+paymentId).data('mounted', true);
			jQuery('stripe_wrap_'+paymentId).show(); 
	  }
	  else {
		  if (jpe.data('stripe_done') != order_total) {
			prButton.update('#payment-request-button');
			jQuery('stripe_wrap_'+paymentId).show();
			jpe.data('stripe_done', order_total); 
		  }
	  }
  } else {
    document.getElementById('payment-request-button_'+paymentId).style.display = 'none';
	jQuery('stripe_wrap_'+paymentId).hide(); 
	var j = jQuery('#opc_payment_wrap_'+paymentId); 
	if (j.length) {
		j.hide(); 
	}
	j = jQuery('div.pay_box.payment'+paymentId); 
	if (j.length) {
		j.hide(); 
	}
  }
});


paymentRequest.on('paymentmethod', function(ev) {
  // Confirm the PaymentIntent without handling potential next actions (yet).
  stripeHelper.doPaymentGoogle(ev); 
});
}
}

if (stripeConfig.methodtype === 'card') 
{
var hasSingle = jQuery('#card-element_'+paymentId); 
if (hasSingle.length) {
	
	if (!jQuery('#card-element_'+paymentId).data('mounted')) { 
		var cf = {}; 
		cf.iconStyle = 'solid'; 
		if ((typeof stripeStyle !== 'undefined') && (stripeStyle))
		{
			cf.style = stripeStyle; 
		}
		var zipE = document.getElementById('zip_field'); 
		if (zipE) {
			cf.value = {postalCode: zipE.value}
		}
		
	
		window.card = stripeHelper.elements.create('card', {
			iconStyle: 'solid',
			style: stripeStyle
		});
	
		window.card.mount('#card-element_'+paymentId);
		
		jQuery('#card-element_'+paymentId).data('mounted', true);
	}
	stripeHelper.stripeRegisterElement(window.card, paymentId); 
	
}
else 
{
var jcn = jQuery('#cardNumber_'+paymentId); 
if (jcn.length) {
var wasMounted = jQuery('#cardNumber_'+paymentId).data('mounted'); 
if (!wasMounted) {
	window.card = stripeHelper.elements.create('cardNumber', {
		style: stripeStyle
	});
	
	window.card.mount('#cardNumber_'+paymentId);
	stripeHelper.stripeRegisterElement(window.card, paymentId); 
	window.cardCvc = stripeHelper.elements.create('cardCvc', {
		style: stripeStyle
	});
	window.cardCvc.mount('#cardCvc_'+paymentId);
	stripeHelper.stripeRegisterElement(window.cardCvc, paymentId); 
	window.cardExpiry = stripeHelper.elements.create('cardExpiry', {
		style: stripeStyle
	});
	window.cardExpiry.mount('#cardExpiry_'+paymentId);
	stripeHelper.stripeRegisterElement(window.cardExpiry, paymentId); 
	
	jQuery('#cardNumber_'+paymentId).data('mounted', true);
	
	
	
}
}
}
}

jpe.data('stripe_done', order_total);
		}

			
			
	}
	}); 
		return true;    
	},

	createFormElements() {
		var test = document.getElementById('stripe_nounce'); 
		if (test) return;
		var opcForm = jQuery('#adminForm'); 
		if (!opcForm.length) {
			opcForm = jQuery('#checkoutForm'); 
		}
		if (opcForm.length) {
			opcForm.append('<input type="hidden" id="stripe_nounce" name="stripe_nounce" value="" />');
			opcForm.append('<input type="hidden" id="stripe_devicedata" name="stripe_devicedata" value="" />');
			opcForm.append('<input type="hidden" id="stripe_amount" name="stripe_amount" value="" />');
			opcForm.append('<input type="hidden" id="stripe_currency_id" name="stripe_currency_id" value="" />');
			
		}

		
	},
	getCurrentConfig: function() {
		 var pe = stripeHelper.isStripe(); 
		 if (!pe) return false; 
		 return stripeHelper.getConfigById(pe.value); 
	},
	getConfigById: function(id) {
		var paymentId = id; 
		if (id.toString().indexOf('_') > 0) {
			var e = id.toString().split('_'); 
			paymentId = e[e.length - 1];
		}
		var el = document.getElementById('payment_id_'+paymentId); 
		var stripeConfig = jQuery(el).data('stripeconfig');
		if (!stripeConfig) {
			stripeConfig = {};
		}
		stripeConfig.paymentId = paymentId;
		stripeConfig.e = el; 
		return stripeConfig;
		
	},
	clickMe: function(paymentId) {
		var e = document.getElementById('payment_id_'+paymentId);
		if ((e) && (!e.checked)) {
			jQuery(e).click(); 
		}
	},
	doCardPayment: function() {
		
		try {
		var order_total = stripeHelper.getTotal(); 
		var currency_2_code = document.getElementById('stripe_currency_iso').value; 
		var myurl = jQuery('#stripe_clienttoken').data('tokenurl'); 
		var pe = stripeHelper.isStripe(); 
		if (!pe) return false; 
		
		var method_id = pe.value; 
		var testE = document.getElementById('card-errors_'+method_id); 
		if (testE) {
			var hTest = testE.innerHTML; 
			if (hTest !== '') {
				Onepage.printPaymentMsg(hTest); 
				Onepage.endValidation(false); 
				return false; 
			}
		}
		
		//stop opc to reenable submit button:
		if (typeof opcsubmittimer != 'undefined')
		if (opcsubmittimer != null)	{
			clearTimeout(opcsubmittimer); 
		}
		opcsubmittimer = null; 
		
		var dataStr = 'order_total='+order_total+'&currency_2_code='+currency_2_code+'&virtuemart_paymentmethod_id='+parseInt(pe.value)+'&option=com_virtuemart&controller=plugin&cmd=gettoken&nosef=1&format=opchtml&name=stripe_rupostel&type=vmpayment&tmpl=component&';
		/*
		{
					'order_total': order_total,
					'currency_2_code': currency_2_code,
					'virtuemart_paymentmethod_id': pe.value
				}
				*/
  jQuery.ajax({
				type: 'POST',
				url: myurl,
				data: dataStr,
				cache: false,
				dataType: 'json',
				contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
				success: function(data, textStatus, jqXHR ) {
					
					if (typeof data.clientToken !== 'undefined') {
						if (typeof data.error !== 'undefined') {
							if (data.error) {
								Onepage.endValidation(false); 
								Onepage.printPaymentMsg(data.error); 
								return; 
							}
						}
						if (typeof data.clientToken.client_secret !== 'undefined') {
							
							
							stripeHelper.onFetchClientToken(data.clientToken);  
							stripeHelper.confirmCardPayment(data.clientToken.client_secret); 
						}
						else {
							Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR); 
							Onepage.endValidation(false); 
							
						}
						
					}
					else {
						Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR); 
						Onepage.endValidation(false); 
					}
				},
				complete: function(jqXHR, textStatus) { 
					stripeHelper.inAjax = false; 
				},
				async: true,
				timeout: 30000,
				error: function(jqXHR, textStatus, errorThrown ) {
					 Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR); 
					 Onepage.endValidation(false); 
				}
				
				});
		}
		catch(e) {
			Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR); 
			Onepage.endValidation(false); 
			return false; 
		}
		return -1; 
	},
	registerSubmit: function() {
		var pe = stripeHelper.isStripe(); 
		if (!pe) return true; 
		if (typeof addOpcTriggerer !== 'undefined') {
			if (!stripeHelper.opcregistered) {
				addOpcTriggerer('callSubmitFunct', 'stripeHelper.stripeOnSubmit(valid2)');
				stripeHelper.opcregistered = true; 
			}
		}
	},
  stripeOnSubmit: function(wasValid) {
	  //do not allow further proceeding
	  if (wasValid === false) return false; 
	  var pe = stripeHelper.isStripe(); 
	  if (!pe) return true; 
	  if (jQuery(pe).data('inrecursion')) return true; 
	  if (!window.stripe) return true; 
	  
	  
	  
	   var paymentId = pe.value; 
	   
	   jQuery('.opc_payment_wrap').each(function() {
		if (this.className.indexOf('opc_payment_wrap_'+paymentId) < 0) {
			var stripeLocalConfig = stripeHelper.getConfigById(paymentId); 
				if ((stripeLocalConfig) && (!stripeLocalConfig.always_open)) { 
					jQuery(this).hide(); 
				}
			}
		});
	
	   var btnWrap = jQuery('#pay_by_card_'+paymentId); 
			if (btnWrap.length) {
				btnWrap.hide(); 
			}
	   var current_billingAddress = stripeHelper.getBillingAddress(); 
	   var myTotals = Onepage.getTotalsObj(); 
	   var order_total = parseFloat(myTotals.order_total.value).toFixed(2);
	   
	   jQuery('#stripe_amount').val(order_total); 
	   jQuery('#stripe_currency_id').val(op_virtuemart_currency_id); 
	 
	 var stripeConfig = stripeHelper.getConfigById(paymentId); 
	 if (stripeConfig.methodtype == 'card') {
	 var cardElement = window.card;
	 var ev = {}; 
	 Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PROCESSING); 
	 stripeHelper.doCardPayment(); 
	 return -1; 
	 
	 }
	 return true; 
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
		  obj.billingAddress = stripeHelper.getBillingAddress();
		  Onepage.writeLog('Tokenzation error', 'stripe', obj); 
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
		 var deviceDataInput = document.getElementById('stripe_devicedata'); 
		deviceDataInput.value = payload.deviceData
	 }
	 
	 if (payload.liabilityShifted) {
      // Liability has shifted
      jQuery('#stripe_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
  } else if (payload.liabilityShiftPossible) {
    // Liability may still be shifted
    // Decide if you want to submit the nonce
	  var obj = {};
	  obj.error = 'Liability shift possible but liability was not shifted'; 
	  obj.billingAddress = stripeHelper.getBillingAddress();
	  Onepage.writeLog('3D secure validation failed', 'stripe', obj);
	  jQuery('#stripe_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
	
	
  } else {
    // Liability has not shifted and will not shift
    // Decide if you want to submit the nonce
	jQuery('#stripe_nounce').val(payload.nonce); 
	Onepage.endValidation(true, true); 
    return;
	
  }
	 /*
    if (!payload.liabilityShifted) {
      console.log('Liability did not shift', payload);
      //showNonce(payload, false);
	  jQuery('#stripe_nounce').val(payload.nonce); 
	  Onepage.endValidation(true, true); 
      return;
    }
	*/
    console.log('verification success:', payload);
    //showNonce(payload, true);
	jQuery('#stripe_nounce').val(payload.nonce); 
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
	  var billingAddress = {};
	  if (Onepage.getElementById('email_field')) {
		billingAddress.email = Onepage.getElementById('email_field').value;
	  }
	  
	  
		if (Onepage.getElementById('first_name_field')) {
		  billingAddress.name = Onepage.getElementById('first_name_field').value;
		  
		  if (Onepage.getElementById('last_name_field')) {
			  billingAddress.name += ' '+Onepage.getElementById('last_name_field');
		  }
		}
		  
		  

	  
	  /*
	  phoneNumber: Onepage.getElementById('phone_1_field').value.replace(/[\(\)\s\-]/g, ''),
		  address_line1: Onepage.getElementById('address_1_field').value,
		  address_city: Onepage.getElementById('city_field').value,
	  */
	  
	  billingAddress.address = {};
	  if (Onepage.getElementById('city_field')) {
		billingAddress.address.city = Onepage.getElementById('city_field').value
	  }
	  
	  if (Onepage.getElementById('virtuemart_country_id')) {
		billingAddress.address.country = country2code;
	  
	  }
	  
	  if (Onepage.getElementById('address_1_field')) {
		billingAddress.address.line1 = Onepage.getElementById('address_1_field').value;
	  }
	  
	  if (Onepage.getElementById('address_1_field')) {
		billingAddress.address.postal_code = Onepage.getElementById('address_1_field').value;
	  }
	  
	  
	  if (Onepage.getElementById('phone_field')) {
		  billingAddress.phone = Onepage.getElementById('phone_field').value;
	  }
	  else if (Onepage.getElementById('phone1_field')) {
		  billingAddress.phone = Onepage.getElementById('phone1_field').value;
	  }
	  if (Onepage.getElementById('middle_name_field')) {
		  billingAddress.name = Onepage.getElementById('first_name_field').value+' '+Onepage.getElementById('first_name_field').value+' '+Onepage.getElementById('last_name_field').value;
	  }
	  if (Onepage.getElementById('address_2_field')) {
		 billingAddress.address.line2 =  Onepage.getElementById('address_2_field').value;
	  }
	  if (stateName !== '') {
		billingAddress.address.state = stateName;
	  }
	  
	  
	  return billingAddress;
  },
	start: function() {
		stripeHelper.init(); 
		return; 
		
		
	},
	stripeRegisterElement: function(el, paymentId) {
	  el.on('change', function(event) {
      stripeHelper.clickMe(paymentId); 
		var displayError = document.getElementById('card-errors_'+paymentId);
		if (event.error) {
			displayError.textContent = event.error.message;
		} else {
			displayError.textContent = '';
		}
    });
	
	el.on('focus', function(event) {
		stripeHelper.clickMe(paymentId); 
	});
	
	},
	stripeCreateToken: function() {
		try {
		var additionalData = stripeHelper.getBillingAddress(); 

    // Use Stripe.js to create a token. We only need to pass in one Element
    // from the Element group in order to create a token. We can also pass
    // in the additional customer data we collected in our form.
    window.stripe.createToken(window.card, additionalData).then(function(result) {
      // Stop loading!
      

      if (result.token) {
        // If we received a token, show the token ID.
        jQuery('#stripe_token').val(result.token.id);
		var order_total = stripeHelper.getTotal(); 
		jQuery('#stripe_amount').val(order_total);
		Onepage.endValidation(true, true); 
        //example.classList.add('submitted');
      } else {
        // Otherwise, un-disable inputs.
       Onepage.endValidation(false); 
      }
    });
		} catch (e) {
			console.log(e); 
			Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR+e.toString()); 
			Onepage.endValidation(false); 
		}
	},
	 handleServerResponse: function(response) {
  if (response.error) {
    // Show error from server on payment form
  } else if (response.requires_action) {
    // Use Stripe.js to handle required card action
    stripeHelper.handleAction(response);
  } else {
    // Show success message
  }
},

handleAction: function(response) {
	try 
	{
	//https://stripe.com/docs/payments/payment-intents/migration-synchronous
  window.stripe.handleCardAction(
    response.payment_intent_client_secret
  ).then(function(result) {
    if (result.error) {
      // Show error in payment form
    } else {
      // The card action has been handled
      // The PaymentIntent can be confirmed again on the server
      fetch('/ajax/confirm_payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          payment_intent_id: result.paymentIntent.id
        })
      }).then(function(confirmResult) {
        return confirmResult.json();
      }).then(stripeHelper.handleServerResponse);
    }
  });
	}
  catch(e) {
	  			console.log(e); 
				Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR+e.toString()); 
				Onepage.endValidation(false); 
  }
},
	
	confirmCardPayment: function(clientSecret, ev) {
		try 
			{
		Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_AUTHORIZING); 
		var billing_details = stripeHelper.getBillingAddress(); 
		window.stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: window.card,
      billing_details: billing_details
    }
  }).then(function(result) {
    if (result.error) {
	  Onepage.printPaymentMsg(result.error.message); 
      // Show error to your customer (e.g., insufficient funds)
      console.log(result.error.message);
	  Onepage.endValidation(false); 
    } else {
      // The payment has been processed!
      if (result.paymentIntent.status === 'succeeded') {
		Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PAYMENTOK); 
        // Show a success message to your customer
        // There's a risk of the customer closing the window before callback
        // execution. Set up a webhook or plugin to listen for the
        // payment_intent.succeeded event that handles any business critical
        // post-payment actions.
		var jsonresp = JSON.stringify(result);
		jQuery('#stripe_response').val(jsonresp); 
		
		Onepage.endValidation(true, true); 
      }
    }
  }).catch( function(e) {
				console.log(e); 
				Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR+e.toString()); 
				Onepage.endValidation(false); 
  });
			} catch(e) {
	  			console.log(e); 
				Onepage.printPaymentMsg(PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR+e.toString()); 
				Onepage.endValidation(false); 
		}
	},
	confirmGooglePayment(clientSecret, ev) {
		window.stripe.confirmCardPayment(
    clientSecret,
    {payment_method: ev.paymentMethod.id},
    {handleActions: false}
  ).then(function(confirmResult) {
    if (confirmResult.error) {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete('fail');
    } else {
      // Report to the browser that the confirmation was successful, prompting
      // it to close the browser payment method collection interface.
      ev.complete('success');
      // Let Stripe.js handle the rest of the payment flow.
      window.stripe.confirmCardPayment(clientSecret).then(function(result) {
        if (result.error) {
          // The payment failed -- ask your customer for a new payment method.
        } else {
          // The payment has succeeded.
        }
      });
    }
  });
	},
	doPaymentGoogle: function(ev) {
  
  var url = jQuery('#stripe_clienttoken').data('tokenurl'); 
  
  
  
  if (!url) {
	  return stripeHelper.invalidateMethod(); 
  }
  
  var order_total = stripeHelper.getTotal(); 
  var currency_2_code = document.getElementById('stripe_currency_iso').value; 
  
  jQuery.ajax({
				type: 'GET',
				url: url,
				data: {
					'order_total': order_total,
					'currency_2_code': currency_2_code
				},
				cache: false,
				dataType: 'json',
				contentType: 'application/json; charset=utf-8',
				success: function(data) {
					if (typeof data.clientToken !== 'undefined') {
						
						if (typeof data.clientToken.client_secret !== 'undefined') {
							stripeHelper.onFetchClientToken(data.clientToken);  
							stripeHelper.confirmGooglePayment(data.clientToken.client_secret, ev); 
						}
						
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
      stripeHelper.onFetchClientToken(resp.clientToken);  
    }
  };
  xhr.open("GET", url, true);
  xhr.send(); 
  */
},

onFetchClientToken: function(clientToken) {
   jQuery('#stripe_clienttoken').data('clientToken', clientToken);
   jQuery('#stripe_clienttoken').val(clientToken.client_secret);
   
   
}
}
  
  
  if (typeof addOpcTriggerer !== 'undefined') {
	
	addOpcTriggerer('callOnTotalsChange', 'stripeHelper.init()'); 
	addOpcTriggerer('callAfterPaymentSelect', 'stripeHelper.registerSubmit()'); 
  }
  
 
  
  
 
  
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



function invalidateMethod() {
	
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

var stripe = null; 
var PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR="Error processing card payment";
var PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PROCESSING="Processing card payment...";
var PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR="Error: ";
var PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_AUTHORIZING="Authorizing card payment...";
var PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PAYMENTOK="Payment is OK, thank you. Creating order details...";
