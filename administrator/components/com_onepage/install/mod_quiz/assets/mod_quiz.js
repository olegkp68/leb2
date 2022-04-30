var mod_quiz = {
	getConfig: function() {
			var dd =  document.getElementById('quizajaxconfig');
			if (dd) {
			  var config = jQuery(dd).data('config'); 
			  return config; 
			}
		var obj = {}; 
		obj.admin = false; 
		return obj; 
	},
	toggle: function(el) {
		if (typeof el.dataset.hide) {
			jQuery(el.dataset.hide).css('display', 'none'); 
		}
		if (typeof el.dataset.show) {
			jQuery(el.dataset.show).css('display', 'block'); 
		}
		
		return false; 
	}
	
}


jQuery(document).ready(function () {
	  jQuery('.q_select').on('change', function() {
		  var selectedValue = this.options[this.selectedIndex].value; 
		  var type = this.dataset.type; 
		  var nexttype = this.dataset.nexttype; 
		  var nextElement = document.querySelector('select.q_select[data-type="'+nexttype+'"]'); 
		  if (nextElement) {
			  nextElement.innerHTML = '';
			  if (typeof quiz_data !== 'undefined')
				  if (typeof quiz_data[selectedValue] !== 'undefined')
				  {
					  /*
					  quiz_data[selectedValue].forEach( function(products, modelName) {
						  console.log(modelName); 
						  console.log(products); 
					  }); 
					  */
					  var first = true; 
					  for (var model in quiz_data[selectedValue]) {
						  if (quiz_data[selectedValue].hasOwnProperty(model)) {
							var products = quiz_data[selectedValue][model];
							//console.log(model, products); 
							if (first) {
								//first_option_txt
								var option = document.createElement("option");
								option.value = '';
								option.text = first_option_txt;
								nextElement.appendChild(option);
							}
							first = false; 
							var option = document.createElement("option");
							option.value = model;
							option.text = model;
							option.dataset.products = products;
							nextElement.appendChild(option);
						  }
					  }
					  /*
					for (var i=0; i<quiz_data[selectedValue].length; i++) {
						console.log(quiz_data[selectedValue][i]); 
						continue; 
						var option = document.createElement("option");
							option.value = array[i];
							option.text = array[i];
							nextElement.appendChild(option);
					}
					*/
				  }
		  }
		  else {
			  this.form.submit(); 
		  }
	  }); 
	}); 