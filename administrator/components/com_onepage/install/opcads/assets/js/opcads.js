function checkPOPUP() {
	
	if (window.location.toString().indexOf('testopcads') > 0) {

	if (typeof showPOPUP !== 'undefined') {
		showPOPUP(); 
	}
	}
 var config = getConfig(); 
 
  if (!config) return; 
//w3c countdown:
// Set the date we're counting down to
var lastTime = jQuery.cookie("opcads");
if (lastTime == 'shown') {
	return; 
}

 var canStartCounting = checkPagePopup(); 
 if ((!lastTime) && (!canStartCounting)) {
	 myPopupLog('new customer, not yet hit the page... '); 
	 return; 
 }
 
 var isnow = new Date().getTime();
 var countDownTime2 = new Date(isnow + (config.seconds * 1000)); //30 seconds
 var countDownTime = countDownTime2;
 if (!lastTime)  {
   jQuery.cookie('opcads', countDownTime2.toJSON(), config.options); 
 }

if (lastTime) {
	var countDownDateOld = new Date(lastTime).getTime();
	if (isNaN(countDownDateOld)) {
		jQuery.cookie('opcads', countDownTime2.toJSON(), config.options); 
		countDownTime = countDownTime2;
	}
	else {
		countDownTime = new Date(lastTime);
	}
}
var countDownDate = countDownTime.getTime()

 myPopupLog('we started counting... '); 
// Update the count down every 1 second
var myCountDownPopup = setInterval(function() {
 
  // Get todays date and time
  var now = new Date().getTime();

  // Find the distance between now and the count down date
  var distance = countDownDate - now;

  // Time calculations for days, hours, minutes and seconds
  
 if (window.playingVideo) {
	 //
	 myPopupLog('but video is playing... '); 
	 clearInterval(myCountDownPopup);
 }
  
 
  if (distance < 0) {
	clearInterval(myCountDownPopup);
	try {
	myPopupLog('elapsed time already reached... '); 
    
	if (typeof showPOPUP !== 'undefined') {
		showPOPUP(); 
	}
	else {
		  jQuery('#light-banner').simplemodal({ overlayClose:true, opacity: 50, zIndex:9999 });
	}
     jQuery.cookie('opcads', 'shown', config.options); 
	}
	catch(e) {
		myPopupLog(e); 
	}
	return; 

  }
  else {
	   myPopupLog('counting... '+distance); 
  }
 
}, 70);

}
function myPopupLog(msg) {
	var config = getConfig(); 
	if (config.debug) {
	 console.log(msg); 
	}
}

function checkVimeo() {
	//disabled for now
	return; 
	
	var iframe = jQuery('iframe');
	iframe.each( function() {
	
	if (typeof Vimeo === 'undefined') return false; 
	if (this.src.indexOf('player.vimeo.com') === -1) return true; 
	
    var player = new Vimeo.Player(this);

    player.on('play', function() {
		myPopupLog('video play...'); 
		window.playingVideo = true; 
    });
	player.on('ended', function() {
		myPopupLog('video stop...'); 
		window.playingVideo = false; 
		checkPOPUP(); 
    });
	player.on('pause', function() {
		myPopupLog('video stop...'); 
		window.playingVideo = false; 
		checkPOPUP(); 
    });
	

    
	}); 
}
var playingVideo = false; 
function checkPagePopup() {
	var current_url = window.location.href;
	var config = getConfig(); 
	
	if (!config) return false; 
	if (!config.mode) return true; 
	
	for (var i=0; i<config.urls.length; i++) {
		if (config.urls[i]) {
			if (current_url.indexOf(config.urls[i]) !== -1) {
				return true; 
			}
		}
	}
	return false; 
	
}

function getConfig(force) {
	var configEl = jQuery('opcads'); 
	
	if ((typeof force == 'undefined') || (!force)) {
	 if (configEl.css('display') === 'none') return false; 
	}
	
	if (!configEl.length) return false; 
	var config = configEl.data('config'); 
	var options = {};
	
	var isnow = new Date().getTime();
    var expiresDate = new Date(isnow + (config.expires_seconds * 1000)); //30 seconds
		   	options.expires = expiresDate; 
			options.path  = '/';
			options.domain = '';
			options.secure  = true; 
			
	config.options = options; 
	
	return config; 
} 

function startPOPUP() {
	var config = getConfig(); 
   if (!config) return; 
   var hasCookie = jQuery.cookie('opcads'); 
   if (!config.mode) {
	   if (!hasCookie) {
		   
		    jQuery.cookie('opcads', 'shown', config.options); 
			if (typeof showPOPUP !== 'undefined') {
				showPOPUP(); 
			}
			else {
			 jQuery('#light-banner').simplemodal({ overlayClose:true, opacity: 50, zIndex:9999 });
			}
	   }
   }
   else {
	  checkPOPUP();    
   }
}




jQuery(document).ready(function() {  


checkVimeo(); 
startPOPUP(); 

jQuery('[data-popup-rendermodulebyid]').each( function() {
	var jel = jQuery(this); 
	var config = getConfig(true); 
	
	var module_id = jel.data('popup-rendermodulebyid'); 
	if (module_id) {
		var extra_suffix = jel.data('urlsuffix'); 
		
		var popupurl = config.rooturl+'index.php?option=com_onepage&view=opc&controller=opc&task=popup&rendermodulebyid='+module_id+config.popupurlsuffix; 
		if (extra_suffix) {
			popupurl += extra_suffix; 
		}
	
		var hasConfig = jel.data('fancyconfig'); 
		if (!hasConfig) {	
			jel.data('fancyconfig', { 'type': 'ajax' } );  
		}
	 jel.attr('href', popupurl); 
	 jel.attr('rel', popupurl); 
	 if (typeof bindFancyBox !== 'undefined') {
	   bindFancyBox(jel); 
	 }
	}
	
}); 


jQuery('[data-popup-renderarticlebyid]').each( function() {
	var jel = jQuery(this); 
	var config = getConfig(true); 
	console.log(config); 
	var article_id = jel.data('popup-renderarticlebyid'); 
	if (article_id) {
		var extra_suffix = jel.data('urlsuffix'); 
		
		var popupurl = config.rooturl+'index.php?option=com_onepage&view=opc&controller=opc&task=popup&renderarticlebyid='+article_id+config.popupurlsuffix; 
		if (extra_suffix) {
			popupurl += extra_suffix; 
		}
		var hasConfig = jel.data('fancyconfig'); 
		if (!hasConfig) {
		  jel.data('fancyconfig', { 'type': 'ajax' } );  
		}
		
	 jel.attr('href', popupurl); 
	 jel.attr('rel', popupurl); 
	 if (typeof bindFancyBox !== 'undefined') {
	   bindFancyBox(jel); 
	 }
	}
	
}); 


}); 