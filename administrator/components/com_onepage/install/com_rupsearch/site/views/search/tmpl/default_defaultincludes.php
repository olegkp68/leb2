<?php
/* license: commercial ! */
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;


			$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		$sor = $root.'components/com_rupsearch/views/search/tmpl/helper.js'; 
		JHtml::script($sor); 
		$sor = $root.'components/com_rupsearch/views/search/tmpl/default.css'; 
		JHtml::stylesheet($sor); 
