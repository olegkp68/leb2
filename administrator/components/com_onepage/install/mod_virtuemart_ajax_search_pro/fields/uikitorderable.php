<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
	  
jimport('joomla.form.formfield');
class JFormFieldUikitorderable extends JFormField
{
  protected $type = 'uikitorderable';
  function getInput()
	{
		//debug_zval_dump($this->form->data->get('module')); die(); 
		$values = (string)$this->element['values'];

		$this->getExtraValues($values); 

		
		$label = (string)$this->element['label']; 
		jimport('joomla.filesystem.file');
		  $nm = 'id_'.JFile::makeSafe($this->name); 
		 if ( version_compare( JVERSION, '3.0', '<' ) == 1) {    
		    $html = '<input type="text" value="'.$this->value.'" name="'.$this->name.'" id="'.$nm.'" />'; 
			return $html; 
		 }
		
		$values = explode(',', $values); 
		$v2 = array(); 
		foreach ($values as $k=>$v) {
		  $v2[$v] = $v; 
		}
		$values = $v2; 
		
		
		
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR); 
		
		
		
		
	
		
		//if (file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'themes'.DS.'extra'.DS.'uikit')) 
		{
		  if ( version_compare( JVERSION, '3.0', '>' ) == 1) {    
		     JHtml::_('jquery.framework');
			 //JHtml::script('//code.jquery.com/jquery-3.3.1.min.js'); 
		  }
		  else {
			  $doc = JFactory::getDocument();
			  //$doc->addCustomTAg('<script src="https://code.jquery.com/jquery-2.2.4.min.js"  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>'); 
			  $doc->addScript('https://code.jquery.com/jquery-2.2.4.min.js'); 
		  }
		  
		  $uikit_url_old = '//cdnjs.cloudflare.com/ajax/libs/uikit/2.27.5/'; 
		  $uikit_url = '//cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-beta.39/'; //js/uikit.min.js'; 
		  $uikit_url = $uikit_url_old;
		  
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		  
		  JHtml::script($uikit_url.'js/uikit.min.js'); 	
		JHtml::stylesheet($uikit_url.'css/uikit.min.css'); 
		JHtml::stylesheet($uikit_url_old.'css/components/sortable.min.css'); 
		JHtml::stylesheet($uikit_url_old.'css/components/sortable.gradient.min.css'); 
		
		JHtml::script($uikit_url.'js/components/sortable.js'); 	
		
		//$sor = $root.'modules/mod_virtuemart_ajax_search_pro/js/uikit/sortable.js'; 
		//JHtml::script($sor); 
		
		$sor = $root.'modules/mod_virtuemart_ajax_search_pro/js/sortable.js'; 
		JHtml::script($sor); 
		$sor = $root.'modules/mod_virtuemart_ajax_search_pro/css/sortable.css'; 
		JHtml::stylesheet($sor); 
		  
		  
		  
		  $root = Juri::root(); 
		  $html = '<div class="clearfix clearboth" >'; 
         $html .= '<div class="uk-container uk-container-center" >';
		 $html .= '<input type="hidden" value="'.htmlentities($this->value).'" name="'.$this->name.'" id="'.$nm.'" />'; 
		 
		  $ar = $this->getArray($this->value);
		  
		  $html .= '<div class="uk-sortable " data-uk-sortable="data-uk-sortable" data-group="'.$nm.'">'; 
		  //$html .= '<li data-id="1"><div class="uk-sortable-handle"></div>...</li>'; 
		  foreach ($ar as $k=>$v) {
		  $html .= '
		  <div class="sortrow " data-val-id="'.$nm.'" data-id="'.$nm.'_'.$k.'" data-val="'.$v.'" data-group="'.$nm.'">
		  
		     <div class="uk-panel uk-panel-box">
		     <i class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right"></i>
			 <strong>'.JText::_($label.'_'.$v).'</strong>
			 <div class="k-badge uk-badge-notification uk-panel-badge">
			  <i class="uk-icon uk-icon-remove uk-margin-small-left uk-cursor-pointer" data-group="'.$nm.'" data-val="'.$v.'" data-id="'.$k.'"></i>
			 </div>
			 </div></div>'; 
			 unset($values[$v]); 
		  }
		  
		  $html .= '<h2 class="unused_separator sortrow" data-group="'.$nm.'">Unused</h2>'; 
		  $html .= '<div class="unused_values" data-group="'.$nm.'">'; 
		  $html .= '</div>'; 
		  if (!empty($values)) 
			  foreach ($values as $k=>$v) {
			      $html .= '
		  <div class="sortrow " data-val-id="'.$nm.'" data-val="'.$v.'" data-id="'.$k.'" data-group="'.$nm.'">
		  
		     <div class="uk-panel uk-panel-box">
		     <i class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right"></i>
			 <strong>'.JText::_($label.'_'.$v).'</strong>
			 <div class="k-badge uk-badge-notification uk-panel-badge">
			  <i class="uk-icon uk-icon-remove uk-margin-small-left uk-cursor-pointer" data-group="'.$nm.'" data-val="'.$v.'" data-id="'.$k.'"></i>
			 </div>
			 </div></div>'; 
			  }
			  
		  
		  
	      $html .= '</div>'; 
		  //$html .= '<button type="button" class="uk-button uk-button-success">Save</button>'; 
		  $html .= '</div></div>';
		  return $html; 
		  
		  
		  
		}
	
	}
	private function getArray($value) {
	   $a = explode(',', $value); 
	   foreach ($a as $k=>$v) {
	     $a[$k] = trim($v); 
	   }
	   return $a; 
	}
	
	private function getExtraValues(&$values) {
		$params = new JRegistry(''); 
		ob_start(); 
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
}
else {
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 	
}


//DEFINES: 
$search_desc_request = true; 
$ft = false; 
$params->optional_search = 2; 

		
		$loaded = false; 
		$db = JFactory::getDBO(); 
		$q = 'select `template` from `#__template_styles` where `client_id` = 0 and `home` = 1'; 
		$db->setQuery($q); 
		$fe_template = $db->loadResult(); 
		if (!empty($fe_template)) {
			$fe = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$fe_template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_ajax_search_pro'.DIRECTORY_SEPARATOR.'queries.php';
			if (file_exists($fe)) {
				include($fe); 
				$loaded = true; 
			}
		}
		if (!$loaded) {
		  	require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'mod_virtuemart_ajax_search_pro'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'queries.php'); 
		}
		
		$dump = ob_get_clean(); 
		
		
		$valuesA = explode(',', $values); 
		$v2 = array(); 
		foreach ($valuesA as $k=>$v) {
		  $v2[$v] = $v; 
		}
		$valuesA = $v2; 
		
		foreach ($search as $PRIORITY_NAME => $_PRIORITY_NAME) {
			$valuesA[$PRIORITY_NAME] = $PRIORITY_NAME; 
		}
		$values = implode(',', $valuesA); 
		
		
		   
	   }
	
	
	
	
}

