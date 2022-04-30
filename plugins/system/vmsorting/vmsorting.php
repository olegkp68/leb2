<?php

// No direct access

defined('_JEXEC') or die('Restricted access..');


jimport('joomla.plugin.plugin');

class plgSystemVmsorting extends JPlugin {
	public function onBeforeRender() {
        if (JFactory::getApplication()->isAdmin()) {
            return;
        }
        $doc = JFactory::getDocument();
        $view = JRequest::getVar('view');
        $option = JRequest::getVar('option');
        
        // получаем параметры
        $view_sorting = $this->params->get('view_sorting', '1');
        
        if($view == 'category'){
            if($view_sorting == 1 && $option == 'com_virtuemart'){
                $js = 'jQuery(document).ready(function($) {$(".orderlistcontainer").vmsorting();});';
            } else {
                $js = 'jQuery(document).ready(function($) {$(".orderlistcontainer").vmsorting("block");});';
            }

            $doc->addScript("/plugins/system/vmsorting/media/jquery.vmsorting.js");
            $doc->addScriptDeclaration($js);

            $doc->addStyleSheet('/plugins/system/vmsorting/media/vmsorting.css'); 
        } 
	}	
}
?>