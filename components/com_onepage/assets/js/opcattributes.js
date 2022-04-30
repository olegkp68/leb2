var OPCCart = {
	setproducttype2: function(id) {
		
		
		var cart_key = id.split('atr_').join(''); 
		var d = document.getElementById('atr_switch_'+cart_key); 
		if (d != null)
		{
			d.value = 1; 
		}
		
		
		//OPCCart.setproducttype(d, product_id); 
		var datas = jQuery('.opc_atr_'+cart_key); 
		var query = '&'; 
		var name = ''; 
		var key = 'opc_atr_'+cart_key+'_';
		var current_multivariant = 0; 
		var current_product_id = 0; 
		datas.each(function() { 
				 
				  if ((typeof this.name != 'undefined') && (typeof this.value != 'undefined'))
				  {
				    //stAn - no other characters then & have to be encoded here, all are handled by apache and other systems
					name = this.name.split(key).join(''); 
					if ((typeof this.type !== 'undefined') && (this.type === 'checkbox')) {
						if (this.checked) {
						query += '&'+name+'='+this.value.split("&").join("%26");
						}
						else {
							return true; 
							//query += '&'+name+'=0';
						}
					}
					else if ((typeof this.type !== 'undefined') && (this.type === 'radio')) {
						if (this.checked) {
						query += '&'+name+'='+this.value.split("&").join("%26");
						}
						else
						{
							return true; 
						}
					}
					else {
						query += '&'+name+'='+this.value.split("&").join("%26");
					}
					
					if (name == 'cart_virtuemart_product_id') var cart_id = this.value; 
					
					
					if ((name === 'virtuemart_product_id') || (name.indexOf('virtuemart_product_id[') === 0)) {
						current_product_id = this.value; 
					}
					
					//console.log('OPCATTRIBUTES', this); 
					if (((typeof this.selectedIndex !== 'undefined') && (typeof this.options !== 'undefined')) && (typeof this.options[this.selectedIndex] !== 'undefined')) {
						var s = this.options[this.selectedIndex]; 
						var je = jQuery(s); 
						var multivariant = je.data('multivariant'); 
						if ((typeof multivariant !== 'undefined') && (multivariant)) {
							query += '&multivariant['+multivariant['cart_key']+']='+multivariant['product_id'];  
							current_multivariant = multivariant; 
						}
					}
				  }
				  
				  
		}); 
		
		datas = jQuery('.opc_atr_'+cart_key+' input'); 
		datas.each(function() { 
				  if (((typeof this.type !== 'undefined') && (this.type === 'radio') && (!this.checked))) {
					   return true; 
					   
				  }
				  else
				if (((typeof this.type !== 'undefined') && (this.type === 'checkbox')) && (!this.checked)) {
					return true; 
						
					}
					else
				  if ((typeof this.name != 'undefined') && (typeof this.value != 'undefined'))
				  {
					name = this.name.split(key).join(''); 
				    //stAn - no other characters then & have to be encoded here, all are handled by apache and other systems
				    query += '&'+name+'='+this.value.split("&").join("%26");
					
					if ((name === 'virtuemart_product_id') || (name.indexOf('virtuemart_product_id[') === 0)) {
						current_product_id = this.value; 
					}
					
					if (name == 'cart_virtuemart_product_id') var cart_id = datas[i].value; 
					
					if (((typeof this.selectedIndex !== 'undefined') && (typeof this.options !== 'undefined')) && (typeof this.options[this.selectedIndex] !== 'undefined')) {
						var s = this.options[this.selectedIndex]; 
						var je = jQuery(s); 
						var multivariant = je.data('multivariant'); 
						if ((typeof multivariant !== 'undefined') && (multivariant)) {
							query += '&multivariant['+multivariant['cart_key']+']='+multivariant['product_id'];  
							current_multivariant = multivariant; 
						}
					}
				  }
				  
				  
		}); 
		var value = ''; 
		datas = jQuery('.opc_atr_'+cart_key+' select'); 
		datas.each(function() { 
				  value = ''; 
				  
				  if ((typeof this.name != 'undefined') && (typeof this.selectedIndex != 'undefined'))
				  {
					name = this.name.split(key).join(''); 
					value = this.options[this.selectedIndex].value; 
				    //stAn - no other characters then & have to be encoded here, all are handled by apache and other systems
				    query += '&'+name+'='+value.split("&").join("%26");
					
					if (name == 'cart_virtuemart_product_id') var cart_id = datas[i].value; 
					//console.log('OPCATTRIBUTES', this); 
					if (((typeof this.selectedIndex !== 'undefined') && (typeof this.options !== 'undefined')) && (typeof this.options[this.selectedIndex] !== 'undefined')) {
						var s = this.options[this.selectedIndex]; 
						var je = jQuery(s); 
						var multivariant = je.data('multivariant'); 
						if ((typeof multivariant !== 'undefined') && (multivariant)) {
							query += '&multivariant['+multivariant['cart_key']+']='+multivariant['product_id'];  
							current_multivariant = multivariant; 
						}
					}
				  }
				  
				  
		}); 
		
		if (current_product_id && current_multivariant) {
			var new_product_id = current_multivariant['product_id']; 
			var old_product_id = current_product_id; 
			if (new_product_id != old_product_id) {
				query = query.split('field['+old_product_id+']').join('field['+new_product_id+']').split('customProductData['+old_product_id+']').join('customProductData['+new_product_id+']'); 
			}
		}
				
				Onepage.op_log(query); 
				Onepage.updateProductAttributes(query); 
		
	},
			setproducttype : function (form, id) {
				form.view = null;
				
				//orignal: datas = form.serialize();
				
				var datas = form.serializeArray(); 
				console.log(datas); 
				var query = ''; 
				var cart_id = id; 
				query += '&option=com_onepage&nosef=1&task=opc&view=opc&controller=opc&cmd=updateattributes&tmpl=component&virtuemart_product_id[0]='+id+'&format=opchtml';
				for (var i=0; i<datas.length; i++)
				{
				  if (datas[i].name != 'undefined')
				  {
				    //stAn - no other characters then & have to be encoded here, all are handled by apache and other systems
				    query += '&'+datas[i].name+'='+datas[i].value.split("&").join("%26");
					
					if (datas[i].name == 'cart_virtuemart_product_id') cart_id = datas[i].value; 
				  }
				}
				
				cart_id = cart_id.split('::').join('___').split(';').join('__').split(':').join('_'); 
				
			   
				
				
				
				
				
				
				Onepage.op_log(query); 
				
				Onepage.op_runSS(null, null, true, 'updateattributes'+query, true); 
				
				return true; 
				
				
				
				
			},


};			

