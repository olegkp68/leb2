function showMyCountdown(el) {
  if (!window.myCountDown) {
   window.myCountDown = setInterval(updateTime, 1000); 
  }
}



function updateTime() {
   
  jQuery('.countdown_wrap').each(function() {
	  var jel = jQuery(this); 
	  var time = jel.data('timestamp'); 
	  if (!jel.data('hascart')) {
		  jel.hide(); 
		  return true; 
	  }
	  else {
		  jel.show(); 
	  }
	  var nowDate = new Date(); 
	  var now = nowDate.getTime();
	  if (typeof time === 'Date') {
		  var parsedTime = time.getTime(); 
	  }
	  else
	  if (time !== '') {
		var parsedTime = new Date(time).getTime(); 
	  }
	  else 
	  if (time === '') {
		  var parsedTime = now; 
		  jel.data('timestamp', nowDate); 
	  }
	  
	  
	  var diff = (now - parsedTime) / 1000; 
	  
	  var minutesConfig = jel.data('minutes'); 
	  var minutesConfigSeconds = parseInt(minutesConfig) * 60; 
	  
	  diff = minutesConfigSeconds - diff; 
	  
	  var display = getMyTimeString(diff); 
	  if (diff > 0) {
	  
	  if ((typeof console !== 'undefined') && (typeof console.log === 'function')) {
		  //console.log('Cart Stock Expirity min:sec', display); 
	  }
	    jel.removeClass('expired'); 
	    jel.children('.cartstock_countdown').html(display); 
		jel.children('.countdown_positive').addClass('counterShown'); 
		jel.children('.countdown_positive').removeClass('counterHiden'); 
		jel.children('.countdown_negative').addClass('counterHiden'); 
		jel.children('.countdown_negative').removeClass('counterShown'); 
	  }
	  else {
		jel.addClass('expired'); 
		jel.children('.cartstock_countdown').html('00:00'); 
		jel.children('.countdown_positive').addClass('counterHiden'); 
		jel.children('.countdown_positive').removeClass('counterShown'); 
		jel.children('.countdown_negative').addClass('counterShown'); 
		jel.children('.countdown_negative').removeClass('counterHiden'); 
		
		if ((typeof console !== 'undefined') && (typeof console.log === 'function')) {
		  //console.log('Cart Stock Expirity min:sec', display); 
		}
		if (window.myCountDown) {
			clearInterval(window.myCountDown); 
			window.myCountDown = null; 
		}
		
	  }
	  
  }); 
  
}

function getMyTimeString(diff) {
	var minutes = Math.floor(diff / 60) % 60;
	  diff -= minutes * 60;
	  var seconds = Math.floor(diff % 60);
	  
	  minutes = String(minutes).padStart(2, '0'); 
	  seconds = String(seconds).padStart(2, '0'); 
	  var display = minutes+':'+seconds; 
	  return display;
}
var myCountDown = null; 


/*stAn - lazyload all possible*/
if (typeof lazyRunObserver === 'undefined') {
if ("IntersectionObserver" in window) {
    var lazyRunObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if ((typeof entry.isIntersecting == 'undefined') || (entry.isIntersecting)) {
          var lazyElement = entry.target;
		  
		  if (typeof  lazyElement.dataset.cmd !== 'undefined') {
		  var fn = lazyElement.dataset.cmd;
		  if (fn) {
			window[fn](lazyElement);
			if (typeof lazyElement.classList !== 'undefined') {
				lazyElement.classList.remove("lazyrun");
				
			}
			lazyRunObserver.unobserve(lazyElement);
		  }
		  
		  }
          
        }
      });
    });
}
}


/*image lazyload*/
if ("MutationObserver" in window) {
var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        var nodes = Array.prototype.slice.call(mutation.addedNodes);
        nodes.forEach(function(node) {
			if (node.parentNode) {
			 var lazyElements = [].slice.call(node.parentNode.querySelectorAll(".lazyrun"));

  
	if (typeof lazyRunObserver !== 'undefined') {
    lazyElements.forEach(function(lazyElement) {
      lazyRunObserver.observe(lazyElement);
	
    });
	}
			}
			
			
        });
      });
    });
	var mybody = document.querySelector("body"); 
	if (!mybody) {
		console.log('Error: move this script to be loaded inside body tag'); 
	}
	else {
    observer.observe(mybody, {
      childList: true,
      subtree: true,
      attributes: false,
      characterData: false,
    });
	}

}
else {
if (typeof jQuery !== 'undefined')
jQuery(document).on('DOMNodeInserted', function(e) {
	if (typeof document.querySelectorAll !== 'undefined') {
  var lazyElements = [].slice.call(document.querySelectorAll(".lazyrun"));

  
	if (typeof lazyRunObserver !== 'undefined') {
    lazyElements.forEach(function(lazyImage) {
      lazyRunObserver.observe(lazyImage);
    });
	}
  }
}); 

}


jQuery('body').on('updateCounter', function(evt, timestamp, hasCart) {
	 jQuery('.countdown_wrap').each(function() {
		var jel = jQuery(this); 
		jel.data('timestamp', timestamp); 
		jel.data('hascart', hasCart); 
		if (!hasCart) {
			jel.hide(); 
		}
		else {
			jel.show(); 
		}
		
		
			
	 });
			/*
			clearInterval(window.myCountDown); 
			window.myCountDown = null; 
			window.myCountDown = setInterval(updateTime, 1000); 
			console.log(timestamp); 
			*/
			
}); 

