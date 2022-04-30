<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


$document = JFactory::getDocument();
		$selectText = ''; //JText::_('COM_ONEPAGE_TAXES_DONOT_DELETE_GIFTS_STATUSES');
				$vm2string = "editImage: 'edit image',select_all_text: '".JText::_('Select All')."',select_some_options_text: '".JText::_($selectText)."'" ;
		if (!class_exists('VmConfig'))
	    require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
	    VmConfig::loadConfig(); 
		$app = JFactory::getApplication(); 
		if (method_exists('vmJsApi', 'js'))
		{
		
		$jq = $app->get('jquery', false); 
		$jq_ui = $app->get('jquery-ui', false); 
		if (empty($jq) && (!OPCJ3))
		{
		
		//DEPRECATED IN VM3: 
		//vmJsApi::js('jquery','//ajax.googleapis.com/ajax/libs/jquery/1.6.4','',TRUE);
		//vmJsApi::js ('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16', '', TRUE);
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') {
		 $root .= '/'; 
		}
		$root = str_replace('/administrator/', '/', $root); 
		$opc_jquery = $root.'components/com_onepage/themes/extra/jquery-ui/jquery-1.11.2.min.js'; 
		JHTMLOPC::script($opc_jquery); 
		//$document->addScript('//code.jquery.com/jquery-latest.min.js'); 
		if (empty($jq_ui))
		{
		JHTMLOPC::script('jquery-ui.min.js', 'components/com_onepage/themes/extra/jquery-ui/', false);
		JHTMLOPC::stylesheet('jquery-ui.min.css', 'components/com_onepage/themes/extra/jquery-ui/', false);
		} 
		$document->addScript('//code.jquery.com/jquery-migrate-1.2.1.min.js'); 
		$app->set('jquery', true); 
		$app->set('jquery-migrate', true); 
		
		
		}
		if (OPCJ3)
		 {
		 
		   JHtml::_('jquery.framework');
		   JHtml::_('jquery.ui');
		   JHtml::_('formbehavior.chosen', 'select.vm-chzn-select');
		   
		      $root = Juri::root(); 
		if (substr($root, -1)!=='/') $root .= '/'; 
		   
		   JHtml::script($root.'administrator/components/com_onepage/install/mod_coolrunner/media/jquery.cookie.js'); 
		 }
		 else
		 {
		vmJsApi::js('chosen.jquery.min');
		vmJsApi::css('chosen');
		 }
		$document->addScriptDeclaration ( '
//<![CDATA[
		var vm2string ={'.$vm2string.'} ;
		 jQuery( function($) {
			$(".vm-chzn-select").chosen({enable_select_all: true,select_all_text : vm2string.select_all_text,select_some_options_text:vm2string.select_some_options_text});
		});
//]]>
				');
		
		
		}
		else
		{
		vmJsApi::jQuery(); 
		}


JHtml::stylesheet('https://use.fontawesome.com/releases/v5.3.1/css/all.css', array('integrity'=>'sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU', 'crossorigin'=>'anonymous')); 
JHTMLOPC::script('opcbe.js', 'administrator/components/com_onepage/assets/js/', false);






  
		   
JHTMLOPC::stylesheet('bootstrap.min.css', 'components/com_onepage/themes/extra/bootstrap/', array());
JHTMLOPC::stylesheet('config.css', 'components/com_onepage/assets/css/', array());

$document = JFactory::getDocument(); 
$document->addStyleDeclaration('
 .subhead-collapse { display: none; }
 header.header { display: none; }
 #vmMainPageOPC .row { margin-left: 0px !important; }
 .row label.hasTip { min-width: 30%; }
 .span11.labelwrap input { clear: both; }
 br { width: 100%; clear: both; }
 .section_wrap > div { clear: both; }
 .span1 { min-width: 8%; }
 #toolbar-box { display: none; }
'); 


