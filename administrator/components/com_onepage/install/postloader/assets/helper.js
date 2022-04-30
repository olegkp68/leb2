if ("IntersectionObserver" in window) {
    var lazyImageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if ((typeof entry.isIntersecting == 'undefined') || (entry.isIntersecting)) {
          var lazyIframe = entry.target;
		  var tagType = lazyIframe.tagName;
		 
		  newTag = tagType.split('pload').join('').split('PLOAD').join(''); 
		  if ((_globalWebpEnabled) && (newTag.toUpperCase() === 'IMG')) {
			   newTag = 'picture'; 
			   var picture = document.createElement(newTag);
			   var imgtag = document.createElement('img');
			   var altsrce = document.createElement('source'); 
			   var altsrc2e = document.createElement('source'); 
			   
			   altsrc = ''; 
			   for (var i = 0, atts = lazyIframe.attributes, n = atts.length, arr = []; i < n; i++)
			   {
				   
				 
			   imgtag.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
			   if (atts[i].nodeName.toUpperCase() == 'SRC') {
				   altsrc2e.srcset = atts[i].nodeValue;
			   }
			   else {
				   if (typeof altsrc2e.setAttribute !== 'undefined') {
					altsrc2e.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
				   }
				   if (typeof altsrce.setAttribute !== 'undefined') { 
				     altsrce.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
				   }
				   if (picture.setAttribute !== 'undefined') {
				    picture.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
				   }
			   }
			   
			   if (atts[i].nodeValue.indexOf('.jpg') > 0) {
				    
					altsrc = atts[i].nodeValue.split('.jpg').join('.webp'); 
					altsrce.type = 'image/webp'; 
					altsrc2e.type = 'image/jpeg'; 
				}
				if (atts[i].nodeValue.indexOf('.png') > 0) {
					//altsrc2e = atts[i].nodeValue;
					altsrc = atts[i].nodeValue.split('.png').join('.webp'); 
					altsrce.type = 'image/webp'; 
					altsrc2e.type='image/png'; 
				}
				
				if ((altsrc) && (atts[i].nodeName.toUpperCase() == 'SRC')) {
					altsrce.srcset = altsrc; 
					 
				}
				else {
					
				}
				
			   }
				if (altsrc) {
				 
				 
				 picture.appendChild(altsrce); 
				 picture.appendChild(altsrc2e); 
				 picture.appendChild(imgtag); 
				 
				
				 
				 var iframe = picture;
				}
				else {
					var iframe = imgtag; 
				}

				
			  
		  }
		  else {
		   var iframe = document.createElement(newTag);
		  for (var i = 0, atts = lazyIframe.attributes, n = atts.length, arr = []; i < n; i++){
			   
				iframe.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
		   }
		  }
		  //lazyIframe.appendChild(iframe); 
          lazyImageObserver.unobserve(lazyIframe);
		  lazyIframe.parentNode.replaceChild(iframe, lazyIframe); 
        }
      });
    });
}
/*image lazyload*/
document.addEventListener("DOMContentLoaded", function() {
  var lazyImages = [].slice.call(document.querySelectorAll("ploadiframe, ploadimg, ploadembed"));

  
	if (typeof lazyImageObserver !== 'undefined') {
    lazyImages.forEach(function(lazyImage) {
      lazyImageObserver.observe(lazyImage);
    });
	}
	else {
		for (var j = 0; j < lazyImages.length; j++){
			    var lazyIframe = lazyImages[j]; 
				
				var tagType = lazyIframe.tagName;
				newTag = tagType.split('pload').join('').split('PLOAD').join(''); 
				var iframe = document.createElement(newTag);
				
				
				for (var i = 0, atts = lazyIframe.attributes, n = atts.length, arr = []; i < n; i++){
					iframe.setAttribute(atts[i].nodeName, atts[i].nodeValue); 
				}
				lazyIframe.parentNode.replaceChild(iframe, lazyIframe); 
		   }
	}
  
});	

if (typeof _globalWebpEnabled == 'undefined') _globalWebpEnabled = false; 