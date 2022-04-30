<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
class JFormFieldajaxfieldselect extends JFormField
{
  protected $type = 'ajaxfieldselect';
  function getInput()
	{
		
		
		
		$multiple = false; 
		
		$element_definition = array(); 
		foreach ($this->element->attributes() as $k=>$v) {
			$element_definition[(string)$k] = (string)$v; 
		}
		
		$name = $element_definition['name']; 
		if (isset($element_definition['data-plugingroup'])) {
		 $group = $element_definition['data-plugingroup']; 
		}
		else {
			$group = 'system'; 
		}
		
		$element_definition['data-ajaxbackend'] = (string)$element_definition['data-ajaxbackend']; 
		
		
		
	    if (isset($element_definition['multiple'])) {
			$multiple = true; 
			$name = str_replace('[]', '', $name); 
		}
		
		$element_id = 'id_'.JFile::makeSafe($name); 


		
		if (!$this->checkPerm()) {
			return 'This Feature is available only to Super Administrators'; 
		}
		$pluginname = $this->form->getData()->get('element', 'plugin');
		
		$root = Juri::base(true); 
		
		if (substr($root, -1) !== '/') $root .= '/'; 
		/*
		if (!empty($element_definition['data-ajaxbackend'])) {
			if ($element_definition['data-ajaxbackend'] === 'com_virtuemart') {
				$url = $root .= 'index.php?option=com_virtuemart&view=vmplg&include[]=vmcoupon&include[]=vmextended&controller=vmplg&task=pluginNotification&format=raw&plugin='.$pluginname.'&cmd='.urlencode($element_definition['data-cmd']).'&elementname='.urlencode($name); 				
			}
		}
		*/
		//if (empty($element_definition['data-ajaxbackend']) || ($element_definition['data-ajaxbackend'] === 'com_ajax')) 
		{
			$url = $root .= 'index.php?option=com_ajax&format=raw&plugin='.$pluginname.'&group='.$group.'&cmd='.urlencode($element_definition['data-cmd']).'&elementname='.urlencode($name); 
		}
		
		
		
		
		
		ob_start(); 
		
		JHtml::stylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'); 
		
		?>
		
		     if (typeof jQuery !== 'undefined') {
				 jQuery(document).ready(function() {
					 jQuery('select[name="<?php echo 'jform[params]['.$name.']'; ?><?php if ($multiple) echo '[]'; ?>"]').each( function() {
					   ajaxfieldselect_<?php echo $name; ?>(this, true);
					 });
				 }); 
			 }
		 
		  
		  function ajaxfieldselect_<?php echo $name; ?>(el, checkStatus) {
			  
		
		   console.log('status: ', checkStatus); 
		   var respDiv = 'ajax_response_<?php echo $name; ?>'; 
		   if (typeof jQuery !== 'undefined') {
			  var myUrl = <?php echo json_encode($url); ?>; 
		    
			  
			  
			  var dataX = jQuery(el.form).serialize();
			  
			  var getAjax = jQuery.ajax({ 
			      url: myUrl, 
				  cache: false,
				  data: dataX,
				  method: 'POST',
				  beforeSend: function() {
					  jQuery('.'+respDiv).html('<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>'); 
					  
				  },
				  
				  complete: function(data, text, textstatus) {
					
					  var res = getAjax.getAllResponseHeaders();
					
					  if ((data.readyState === 4) && (data.status === 200)) {
					  if (typeof data.responseText !== 'undefined') {
						 
					  var respText = data.responseText; 
					  var selectedValue = <?php echo json_encode($this->value); ?>; 
					  try {
					    var reta_data = JSON.parse(respText); 
						
						 for (item in reta_data) {
							if ( !reta_data.hasOwnProperty(item) ) continue;
							keyname = item; 
							keyvalue = reta_data[item];
							
						 } 
						 
						 var el = jQuery('select[name="<?php echo 'jform[params]['.$name.']'; if ($multiple) echo '[]'; ?>"]')
						 if (!el.length) {
							  el = jQuery('select#<?php echo $element_id; ?>'); 
						 }
						
						 
						
						 jQuery.each(reta_data, function (i, item) {
							var obj = { 
								value: i,
								text : item 
							};
							//console.log(selectedvalue); 
							if (Array.isArray(selectedValue)) {
								if (selectedValue.includes(obj.value)) {
									obj.selected = 'selected'; 
								}
							}
							else {
							 if (obj.value == selectedValue) obj.selected = 'selected'; 
							}
							el.append(jQuery('<option>', obj ));
						
						});
						if (typeof el.chosen !== 'undefined') {
							el.trigger('chosen:updated'); 
							el.trigger('liszt:updated'); 
						}
						 
						 
						jQuery('.'+respDiv).html('<i class="fa fa-check" aria-hidden="true"></i>');   
					  }
					  catch(e) {
						jQuery('.'+respDiv).html(respText);   
					  }
					  
					  
					  }
					  }
					  if ((data.readyState === 4) && (data.status !== 200)) {
						  
						   jQuery('.'+respDiv).html('<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>'); 
					  }
				  },
				  error: function() {
					  jQuery('.'+respDiv).html('<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>'); 
				  }
				  
			   }); 
		   }
		 
		   return false; 
		  } 
		
		
		<?php 
		$js = ob_get_clean(); 
	    JFactory::getDocument()->addScriptDeclaration($js); 
		
		
		
		
		$html = '<select name="jform[params]['.$name.']'; 
		if ($multiple) $html .= '[]'; 
		$html .= '" '; 
		if ($multiple) $html .= ' multiple="multiple" '; 
		
		$html .= ' id="'.$element_id.'" '; 
		$html .= ' >'; 
		
		if (!empty($element_definition['data-defaultvalue'])) {
			$html .= '<option value="'.htmlentities($element_definition['data-defaultvalue']).'">'.htmlentities($element_definition['data-defaultlabel']).'</option>'; 
		}
		
		$html .= '</select>'; 
		$html .= '<div class="ajax_response_'.$name.'"></div>';
	  
	   return $html; 
	
	}
	
	
	function checkPerm() {
	   $user = JFactory::getUser(); 
	   
      $isroot = $user->authorise('core.admin');	
	  
	  if (!$isroot) 
	  {
		
		return false; 
	  }
	  
	  $iss = JFactory::getApplication()->isSite(); 
	  if (!empty($iss)) return false; 
	  
	  return true; 
   }
	
	
	
}

