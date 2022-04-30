<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
class JFormFieldAjaxfield extends JFormField
{
  protected $type = 'ajaxfield';
  function getInput()
	{
		
		$name = $this->element['name']; 
		
		
		if (!$this->checkPerm()) {
			return 'This Feature is available only to Super Administrators'; 
		}
		
		$root = Juri::base(true); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		
		$pluginname = $this->form->getData()->get('element', '');
		$x = $this->form->getData(); 
		
		if (empty($pluginname)) {
		  $module_name = $this->form->getData()->get('module', '');
		  $st = substr($module_name, 0, 4); 
		  $st = strtolower($st); 
		  if ($st === 'mod_') $module_name = substr($module_name, 4); 
		  $root = str_replace('/administrator/', '/', $root); 
		  $hash = JApplicationHelper::getHash('opc ajax search');
		  $url = $root .= 'index.php?option=com_ajax&format=raw&module='.$module_name.'&cmd='.urlencode($this->element['data-cmd']).'&hash='.urlencode($hash); 
		}
		else {
			$url = $root .= 'index.php?option=com_ajax&format=raw&plugin='.$pluginname.'&cmd='.urlencode($this->element['data-cmd']); 
		}
		
		
		
		
		ob_start(); 
		
		JHtml::stylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'); 
		
		?>
		  <?php if (!empty($this->element['data-status'])) {
		  ?>
		     if (typeof jQuery !== 'undefined') {
				 jQuery(document).ready(function() {
					 jQuery('input[name="<?php echo 'ajaxfieldbutton_'.$name; ?>"]').each( function() {
					   ajaxfield_<?php echo $name; ?>(this, true);
					 });
				 }); 
			 }
		  <?php
		  }
		  ?>
		  
		  function ajaxfield_<?php echo $name; ?>(el, checkStatus) {
			  
		   <?php if (!empty($this->element['data-isdownload'])) {
			   ?>
			    var dataX = jQuery(el.form).serialize();
			    var newLocation = '<?php echo $url; ?>&'+dataX; 
				
				document.location = newLocation;
			   <?php
		   }
		   else { ?>
			  
		   var respDiv = 'ajax_response_<?php echo $name; ?>'; 
		   if (typeof jQuery !== 'undefined') {
			  var myUrl = '<?php echo $url; ?>'; 
		      if (checkStatus) myUrl += '&checkstatus=1'; 
			  
			  if (typeof tinyMCE !== 'undefined') {
				tinyMCE.triggerSave();
			  }
			  else {
				 var e = jQuery('.mce_editable'); 
				 if (e.length > 0) {
				  if (!confirm('HTML Editor was not found or recognized. Editor\'s content may not store properly. Do you want to continue?')) return; 
				 }
			  }
			 
			  
			  
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
					  
					  jQuery('.'+respDiv).html(respText); 
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
		   <?php } ?>
		   return false; 
		  }
		
		
		<?php 
		$js = ob_get_clean(); 
	    JFactory::getDocument()->addScriptDeclaration($js); 
	   $html = '<input type="button" onclick="return ajaxfield_'.$name.'(this)" value="'.htmlentities($this->element['data-label']).'" name="ajaxfieldbutton_'.$name.'" /><div class="ajax_response_'.$name.'"></div>'; 
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

