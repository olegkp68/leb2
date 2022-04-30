<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* This file is part of stAn RuposTel one page checkout
* This is registered Virtuemart function to process one page checkout
* registration of this function is done automatically at first use of basket.php
* it uses all the fields from <form> and saves them into session and redirects to /html/checkout.onepage
* This function saves user information and the order to database and sends emails
*
* @copyright Copyright (C) 2007 - 2010 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free shoftware released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
if (substr($selected_template, strlen($selected_template))!=='/') $selected_template.='/';
JHTMLOPC::_('behavior.modal', 'a.opcmodal'); 
JHTMLOPC::stylesheet('tabcontent1.css', 'components/com_onepage/themes/'.$selected_template, array());
JHTMLOPC::script('tabcontent.js', 'components/com_onepage/themes/'.$selected_template, false);


 $document = JFactory::getDocument();
 
 //$document->addScriptDeclaration($javascript); 
 $lang = JFactory::getLanguage(); 

if (method_exists($lang, 'isRTL'))
if ($lang->isRTL())
{
	
JHTMLOPC::stylesheet('rtl.css', 'components/com_onepage/themes/'.$selected_template, array());
}

/*
if(preg_match('/(?i)msie [1-10]/',$_SERVER['HTTP_USER_AGENT']))
{
 
}
else
if (stripos($_SERVER['HTTP_USER_AGENT'],'Trident/7.0; rv:11.0'))
{
 JHTMLOPC::stylesheet('ie.css', 'components/com_onepage/themes/'.$selected_template, array());
}
*/
JHTMLOPC::stylesheet('ie.css', 'components/com_onepage/themes/'.$selected_template, array());
/*
$document = JFactory::getDocument();
	$css = '
<!--[if gt IE 7]>
		<link rel="stylesheet" type="text/css" href="'.JURI::root().'components/com_onepage/themes/'.$selected_template.'ie.css" >
<![endif]-->
	'; 
	// $css .= ' @import url("'.JURI::root().'components/com_onepage/themes/'.$selected_template.'/ie.css");

	$document->addCustomTag($css);
	*/