<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
class JFormFieldAjaxuploadfield extends JFormField
{
  protected $type = 'ajaxuploadfield';
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
					 jQuery('input[name="<?php echo 'ajaxuploadfieldbutton_'.$name; ?>"]').each( function() {
					   ajaxuploadfield_<?php echo $name; ?>(this, true);
					 });
				 }); 
			 }
		  <?php
		  }
		  ?>
		  
		  function ajaxuploadfield_<?php echo $name; ?>(el, checkStatus) {
			  
		   <?php if (!empty($this->element['data-isdownload'])) {
			   ?>
			   document.location = '<?php echo $url; ?>'; 
			   <?php
		   }
		   else { ?>
			  
		   var respDiv = 'ajax_response_<?php echo $name; ?>'; 
		   if (typeof jQuery !== 'undefined') {
			  var myUrl = '<?php echo $url; ?>'; 
		      if (checkStatus) myUrl += '&checkstatus=1'; 
			  else {
			  if (typeof el.files[0] === 'undefined') return; 
			  
			  
			  var formData = new FormData();
			  var file = el.files[0]; 
			  
			  formData.append("file", file, file.name);
			  formData.append("upload_file", true);
			  }
			  var getAjax = jQuery.ajax({ 
			      url: myUrl, 
				  cache: false,
				  data: formData,
				  method: 'POST',
				  type: "POST",
				  async: true,
				  contentType: false,
				  processData: false,
				  timeout: 60000,
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
	   $html = '<input type="file" onclick="this.value=\'\'" onchange ="return ajaxuploadfield_'.$name.'(this)" value="'.htmlentities($this->element['data-label']).'" name="ajaxuploadfieldbutton_'.$name.'" /><div class="ajax_response_'.$name.'"></div>'; 
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

