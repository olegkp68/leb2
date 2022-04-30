<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/ 
// no direct access
defined('_JEXEC') or die('Restricted access');

class OPCPane 
{
  var $options; 
  var $type; 
  var $name; 
  static $instance; 
  public static function getInstance($type='TabSet', $options=array(), $name='myTabs')
  {
	   JHtml::_('jquery.framework');
	      JHtml::_('jquery.ui');
	  JHtml::script('//cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/js/uikit.min.js'); 	
		JHtml::stylesheet('//cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/css/uikit.min.css'); 
		JHtml::stylesheet('//cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/css/components/sortable.min.css'); 
		JHtml::stylesheet('//cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/css/components/sortable.gradient.min.css'); 
		JHtml::script('//cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/js/components/sortable.js'); 	
    
		
		   
	
	if (!empty($options))
	if (!empty($options['active']))
	$arr = array('active'=>$options['active']); 
	
 
    //$this->options = $arr; 
	$pane = new OPCPane(); 
	$pane->type = $type; 
	$pane->name = $name; 
	$pane->options = $options; 
        self::$instance = $pane; 
	return $pane; 
  }
  
  function startPane($type)
   {
	 return ''; 
	 static $started; 
	 $this->name = $type; 
	 $html = ''; 
	 if (empty($started) || (empty($started[$type]))) {
		
	 }
	 return $html; 
   }
  function startPanel($name, $id)
   { 
   
    
    $html = '<li '; 
	//$html .= '   class="uk-active"  '; 
	$html .= ' ><a href="#" >'.$name.'</a></li>'; 
	
	$this->titles[$id] = $html;
	
	ob_start(); 
	
	
	$this->lastName = $name; 
	$this->lastId = $id; 
	
    return ''; 
	
    
   }
   var $data; 
  function endPanel()
  {
	
	$data = ob_get_clean();
	
	$this->data[$this->lastId] = $data;
	return ''; 
    
  }
  
  function endPane()
  {
	  echo '<ul class="uk-tab " data-uk-switcher="'.htmlentities(json_encode(array('connect'=>'#sys_tab_content'))).'">'; 
	  foreach ($this->titles as $id=>$title) {
		  echo $title; 
	  }
	 echo '</ul>'; 
	 echo '<ul id="sys_tab_content" class="uk-switcher uk-margin">'; 
	foreach ($this->data as $id=>$html) {
		echo '<li id="'.htmlentities($id).'">'.$html.'</li>'; 
	}
	
    echo '</ul>'; 
  }
}
