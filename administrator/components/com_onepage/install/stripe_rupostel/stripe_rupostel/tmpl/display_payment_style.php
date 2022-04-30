<?php  defined ('_JEXEC') or die();

//see - https://stripe.com/docs/js/appendix/style 
//to override this file, put it into /components/com_onepage/themes/{YOUR THEME}/overrides/payment/stripe_rupostel/display_payment_style.php
//make sure to define stripeStyle
?><script>
var stripeStyle = {
    base: {
      iconColor: '#c4f0ff',
      color: '#fff',
      fontWeight: 500,
      fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
      fontSize: '16px',
      fontSmoothing: 'antialiased',
      ':-webkit-autofill': {
        color: '#fce883',
      },
      '::placeholder': {
        color: '#87BBFD',
      },
	  backgroundColor: '#fff',
    },
    invalid: {
      iconColor: '#FFC7EE',
      color: '#FFC7EE',
    },
  };
</script>  