<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL 
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

/*OPC FOR HIKASHOP VARS*/
$selected_template = OPCHikaConfig::get('selected_theme', 'clean_simple2'); 

/*END VARS*/



$version = ''; 
if (!defined('OPCVERSION'))
{
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php'))
{
  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php'); 
}
}




  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'api.php'); 

if ($api_key === 'default')  
if (empty($do_not_show_opcregistration) || (empty($rupostel_email)))
if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'default_register.php'))
 {
   echo $this->loadTemplate('register'); 
 }

echo '<div id="vmMainPageOPC"><div id="opc_config_wrapper">'; 
 
jimport ('joomla.html.html.bootstrap');

JHTML::_('behavior.tooltip');

JHtml::_('behavior.keepalive');

jimport( 'joomla.html.html.behavior' );
JHtml::_('behavior.modal', 'a.opcmodal'); 

//JHtml::_('formbehavior.chosen', 'select');

if (OPCVERSION != '{OPCVERSION}')
$version = ' ('.OPCVERSION.')'; 


$fullConfig = OPCHikaConfig::loadFullConfig(); 
extract($fullConfig); 

	
	ob_start();
	JToolBarHelper::Title(JText::_('COM_ONEPAGE_CONFIGURATION_TITLE').$version , 'generic.png');
//	JToolBarHelper::install();
	JToolBarHelper::apply();
/*	JToolBarHelper::apply(); */
	//JToolBarHelper::cancel();
$document = JFactory::getDocument();
				$selectText = JText::_('COM_ONEPAGE_TAXES_DONOT_DELETE_GIFTS_STATUSES');
				$vm2string = "editImage: 'edit image',select_all_text: '".JText::_('Select All')."',select_some_options_text: '".JText::_($selectText)."'" ;
				


	//if (!OPCJ3)
	{
	  JHTMLOPC::stylesheet('bootstrap.min.css', 'components/com_onepage/themes/extra/bootstrap/', array());
	  JHTMLOPC::stylesheet('config.css', 'components/com_onepage/assets/css/', array());
	}
				
	
		
		
$base = JURI::base(); 
$jbase = str_replace('/administrator', '', $base); 	
if (substr($jbase, -1) !== '/') $jbase .= '/'; 






		
		
	
	//$docj = JFactory::getDocument();
	$url = JURI::base(true); 
	if (substr($url, strlen($url))!= '/') $url .= '/'; 
	$javascript =  "\n".' var op_ajaxurl = "'.$url.'"; '."\n";
	
	$document->addScriptDeclaration( $javascript );	
	
	
	
      $session = JFactory::getSession();
      
        jimport('joomla.html.pane');
        jimport('joomla.utilities.utility');
	
	JHTMLOPC::script('opcbe.js', 'administrator/components/com_onepage/assets/js/', false);
    
	
	



$document = JFactory::getDocument();
//$document->addScript('/administrator/includes/js/joomla.javascript.js');
	$is_admin = true; 
    
   	$document = JFactory::getDocument();
	$style = '
	
	div.current {
	 float: left;
	 
	 width: 98%;
	}
	div {
	 text-indent: 0;
	}
	dl {
	 margin-left: 0 !important;
	 padding: 0 !important;
	}
	dd {
	 margin-left: 0 !important;
	 padding: 0 !important;
	 width: 100%;
	 
	}
	dd div {
	 margin-left: 0 !important;
	 padding-left: 0 !important;
	 text-indent: 0 !important;
	 
	 
	}
	div.current dd {
	 display: block;
	 padding-left:1px;
     padding-right:1px;
     margin-left:1px;
     margin-right:1px;
     text-indent:1px;
     float: left;
	}
	input[type="button"]:hover, input[type="button"]:active {
	  background-color: #ddd; 
	}
	
	';
	if (!OPCJ3)
   $document->addStyleDeclaration($style);

//include_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');

// set default variables:
if (!isset($disable_onepage)) $disable_onepage = false;
if (!isset($must_have_valid_vat)) $must_have_valid_vat = true;
if (!isset($unlog_all_shoppers)) $unlog_all_shoppers = false;
if (!isset($allow_duplicit)) $allow_duplicit = true;
if (!isset($tpl_logged)) $tpl_logged = '';
if (!isset($tpl_unlogged)) $tpl_unlogged = '';
if (!isset($css_logged)) $css_logged = '';
if (!isset($css_unlogged)) $css_unlogged = '';
if (!isset($show_full_tos)) $show_full_tos = false;
if (!isset($payment_default)) $payment_default = 'default';
if (!empty($this->default_country))
if (!isset($default_shipping_country)) $default_shipping_country = $this->default_country;

$userConfig = JComponentHelper::getParams('com_users');
$regA = $userConfig->get('allowUserRegistration');
$regB = $userConfig->get('useractivation');

if (!empty($this->errors)) {
	    echo '<div style="width = 100%; border: 2px solid red;">';
	    echo $this->errors;
	    echo '</div>';
	
}	
	

?>
	
	<form action="<?php echo JURI::base(); ?>index.php?option=com_onepage&amp;controller=config" method="post" name="adminForm" id="adminForm">
	
	<input type="hidden" name="is_hika" value="1" />
	
	<?php 
	$x = array('en-GB'); 
    ?>
	<input type="hidden" name="<?php echo $session->getName(); ?>" value="<?php echo $session->getId(); ?>" />
	<input type="hidden" name="ignhash" value="" id="ignhash" />
	<input type="hidden" name="<?php if (method_exists('JUtility', 'getToken'))
	echo JUtility::getToken();
	else echo JSession::getFormToken(); ?>" value="1" />

	
	
<input type="hidden" name="do_not_show_opcregistration" value="<?php if (!empty($do_not_show_opcregistration)) echo '1'; else echo '0'; ?>" id="do_not_show_opcregistration" />
<?php	
	$selected = $opclang = JRequest::getVar('opclang', ''); 
	if (!in_array($opclang, $x)) $selected = $opclang = ''; 
	$flag = ''; 
	if (count($x)>1)
	{
	$a1 = explode('-', $opclang); 
	if (isset($a1[0]))
	{
	 $cl = strtolower($a1[0]); 
	 if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'mod_languages'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$cl.'.gif'))
	  {
	    $root = Juri::root().'/'; 
		$root = str_replace('/administrator/', '', $root); 
		
	    $flag = '<br style="clear:both;"><img src="'.$root.'/media/mod_languages/images/'.$cl.'.gif" alt="'.$opclang.'"/>'; 
		
	  }
	}
	?>
    <div class="langtab" style="clear: both; " >
	<label for="opclang"><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?></label>
	<select name="opclang" id="opclang" onchange="submitbutton('changelang');">
	 <?php 
	  
	  
	  echo '<option '; 
	  if (empty($selected)) echo 'selected="selected" '; 
	  echo ' value="">'.JText::_('JALL_LANGUAGE').'</option>'; 
	  foreach ($x as $l)
	   {
	     echo '<option '; 
		 if ($selected == $l) echo ' selected="selected" '; 
		 echo ' value="'.$l.'">'.$l.'</option>'; 
	   }
	   
	 ?>
	</select>
	
	
	</div>
	
	<?php
	}
	?>
	<input type="hidden" name="opc_lang_orig" value="<?php echo $opclang; ?>" />
	<?php
		$app = JFactory::getApplication(); 
		
		if ((isset($app->input)) ) {
		
		$inputCookie  = $app->input->cookie;
		$value        = $inputCookie->get('opc_tab', 'panel01id');
		
		
		
		}
		else
		{
			$value = 'panel01id'; 
		}
		
        $pane = OPCPane::getInstance('tabs', array('active'=>$value, 'startOffset'=>0));
        echo $pane->startPane('pane');
        
		echo $pane->startPanel(JText::_('COM_ONEPAGE_VERSION_PANEL'), 'panel01id');
		?>
		<div id="opc_new_version" style="display: none; width: 100%; background-color: green; color: white; font-weight: bold; padding:5px;"><?php echo JText::_('COM_ONEPAGE_UPDATE_AVAILABLE'); ?></div>
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_ONEPAGE_VERSION_INFO'); ?></legend>
        <table class="admintable table table-striped" style="width: 100%;">
		<tr>
	    <td class="key">
	     <label for="installed_version"><?php echo JText::_('COM_ONEPAGE_INSTALLED_VERSION'); ?></label> 
	    </td>
	    <td  >
		<?php echo OPCVERSION; 
		$document = JFactory::getDocument();
		$document->addScriptDeclaration(' var opc_current_version = "'.OPCVERSION.'"; ');
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'api.php'))
		{
		  $api_key = $api_stamp = 0; 
		  include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'api.php'); 
		}
		if (empty($disable_check))
		$document->addScript('//cdn.rupostel.com/rupostel.js?opcversion='.OPCVERSION.'&amp;api_key='.$api_key.'&amp;api_stamp='.$api_stamp); 
		//JHtml::script('//cdn.rupostel.com/rupostel.js');

		?>
		</td>
		</tr>
		
		<tr>
	    <td class="key">
	     <label for="latest_version"><?php echo JText::_('COM_ONEPAGE_AVAILABLE_VERSION'); ?></label> 
	    </td>
	    <td  ><div id="opc_latest_version_wrapper"><div id="opc_latest_version">&nbsp;<?php if (empty($disable_check)) { ?><img alt="Loading..." src="../media/system/images/mootree_loader.gif" /><?php } if (!empty($disable_check)) echo JText::_('COM_ONEPAGE_VERSION_CHECK_DISABLED'); ?> </div></div>
		</td>
		</tr>
		
		<tr>
	    <td class="key">
	     <label for="change_log"><?php echo JText::_('COM_ONEPAGE_CHANGELOG'); ?></label> 
	    </td>
	    <td  >
		<div id="opc_iframe_here">&nbsp;<?php if (!empty($disable_check)) echo JText::_('COM_ONEPAGE_VERSION_CHECK_DISABLED'); ?></div>
		  
		</td>
		</tr>
		
		<tr>
	    <td class="key">
	     <label for="rupostel_email"><?php echo JText::_('COM_ONEPAGE_RUPOSTEL_EMAIL'); ?></label> 
	    </td>
	    <td  >
		<?php echo JText::_('COM_ONEPAGE_RUPOSTEL_EMAIL_DESC'); ?><br />
		  <input type="text" style="width: 300px;" id="rupostel_email" name="rupostel_email" value="<?php if (!empty($rupostel_email)) echo $rupostel_email; ?>" />
		  
		  
		  <input type="hidden" value="<?php echo $this->registration->opc_registration_name; ?>" name="opc_registration_name" id="opc_registration_name_config" />
		  
		  <input type="hidden" value="<?php echo $this->registration->opc_registration_company; ?>" name="opc_registration_company" id="opc_registration_company_config" />
		  
		  <input type="hidden" value="<?php echo $this->registration->opc_registration_hash; ?>" name="opc_registration_hash" id="opc_registration_hash" />
		  
		  <input type="hidden" value="<?php echo $this->registration->opc_registration_username; ?>" name="opc_registration_username" id="opc_registration_username_config" />
		  
		
		  
		  
		  
		  
		</td>
		
		</tr>
		
		<tr>
	    <td class="key">
	     <label for="disable_check2"><?php echo JText::_('COM_ONEPAGE_DISABLE_VERSION_CHECK'); ?></label> 
	    </td>
	    <td  >
		
		  <input type="checkbox" id="disable_check"  style="float: left; text-align: left;" name="disable_check" <?php if (!empty($disable_check)) echo ' checked="checked" '; ?> value="1" />
		  <label for="disable_check"><?php echo JText::_('COM_ONEPAGE_DISABLE_VERSION_CHECK_DESC'); ?></label>
		</td>
		
		</tr>
		
		
		</table>
		</fieldset>
		<?php
		echo $pane->endPanel(); 
        echo $pane->startPanel(JText::_('COM_ONEPAGE_GENERAL_PANEL'), 'panel1');
?>
<fieldset class="adminform">
        <legend><?php echo JText::_('COM_ONEPAGE_GENERAL'); ?></legend>
        <table class="admintable table table-striped" style="width: 100%;">
	<tr>
	    <td class="key">
	     <label for="disable_op"><?php echo JText::_('COM_ONEPAGE_GENERAL_DISABLEOPC_LABEL'); ?></label> 
	    </td>
	    <td  >
	    <input id="disable_op" type="checkbox" name="disable_op" value="disable" <?php if ($this->disable_onepage === true) echo 'checked="checked"'; ?>/> 

		<input type="hidden" name="option" value="com_onepage" />
		<input type="hidden" name="view" value="hikaconfig" />
		<input type="hidden" name="task" id="task" value="save" />
		<input type="hidden" name="task2" id="task2" value="" />
		<input type="hidden" name="delete_ht" id="delete_ht" value="0" />
		<input type="hidden" name="backview" id="backview" value="panel1" />


	    </td><td><?php echo JText::_('COM_ONEPAGE_GENERAL_DISABLEOPC_DESC'); ?></td>
	</tr>

	
	
		<tr>
	    <td class="key">
	     <label for="opc_debug" ><?php echo JText::_('COM_ONEPAGE_DEBUG_LABEL'); ?></label>
	    </td>
	    <td>
				<input type="checkbox" name="opc_debug" id="opc_debug" value="1" <?php if (!empty($opc_debug)) echo ' checked="checked" '; ?> />
	    </td>
		<td>
		
		<?php 
		
		echo JText::_('COM_ONEPAGE_DEBUG_DESC'); 
		
		
		
		?>
		
		
	    </td>
	</tr>

	

	
	


        </table>
    </fieldset>    
	
	<script type="text/javascript">
//<![CDATA[
//http://www.codeproject.com/Tips/585663/Communication-with-Cross-Domain-IFrame-A-Cross-Bro
// Here "addEventListener" is for standards-compliant web browsers and "attachEvent" is for IE Browsers.
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];


var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

eventer(messageEvent, function (e) {

	if ((e.origin == 'https://cdn.rupostel.com') || (e.origin == 'http://cdn.rupostel.com') || (e.origin == '//cdn.rupostel.com')) {
		setVersion(e.data); 
		
	}
}, false);    



	var op_next = 0;
	<?php 
	if (false) 
	{
	?>
	var html1 = '<tr><td class="key"><label for="hidep_';
	var html2 = '" >Payment configuration: </label></td><td colspan="3" > For this shipping method <select style="max-width: 100px;"  id="hidepsid_';
	var html21 = '" name="hidepsid_';
	var html3 = '"><option value="del" selected="selected">NOT CONFIGURED/DELETE</option><?php
		  if (!empty($this->sids))
		  foreach ($this->sids as $k => &$sid)
		  {
		  ?><option value="<?php echo addslashes($k); ?>"><?php echo $sid ?></option><?php
		  }
		  ?></select> 	disable these payment payments methods (use CTRL)		<select style="max-width: 100px;" multiple="multiple" size="5" id="hidep_';
	var html31 = '" name="hidep_';	
	var html4 = '[]">	<?php
		if (!empty($this->pms))
		foreach($this->pms as $p)
		{
		 ?> <option value=<?php echo '"'.addslashes($p['payment_method_id']).'" '; ?>><?php echo addslashes($p['payment_method_name']);?></option><?php
		}
		?></select>and make default this one	<select style="max-width: 100px;" id="hidepdef_';
	var html41 = '"  name="hidepdef_';	
	var html5 = '">	<?php
	    if (!empty($this->pms))
		foreach($this->pms as $p)
		{
		 ?> <option value=<?php echo '"'.$p['payment_method_id'].'" ';  ?>><?php echo addslashes($p['payment_method_name']);?></option><?php
		}
		?></select><a href="#" onclick="javascript: return(addnew());"> Click here to ADD MORE ... </a>	    </td>	</tr>';
    <?php } ?>


		




	
//]]>
	</script>

	
<?php    

        echo $pane->endPanel();
   
    echo $pane->startPanel(JText::_('COM_ONEPAGE_PAYMENT_PANEL'), 'panel799');
    ?>
    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_ONEPAGE_PAYMENT'); ?></legend>
     <table class="admintable table table-striped" style="width: 100%;">
   
   
  

   
		<tr>
	    <td class="key">
	     <label for="opc_payment_refresh" ><?php echo JText::_('COM_ONEPAGE_PAYMENT_DISABLE_PAYMENT_REFRESH'); ?></label>
	    </td>
	    <td>
	     <input  class="opc_payment_refresh" type="checkbox" value="1" name="opc_payment_refresh" id="opc_payment_refresh" <?php if (!empty($opc_payment_refresh)) echo 'checked="checked"'; ?>/>
	    </td>
	    <td><?php echo JText::_('COM_ONEPAGE_PAYMENT_DISABLE_PAYMENT_REFRESH_DESC'); ?>
	    </td>
	</tr>
	
	
	
   
	
     </table>
    </fieldset>
    <?php 
   
    echo $pane->endPanel(); 
	
            echo $pane->startPanel(JText::_('COM_ONEPAGE_DISPLAY_PANEL'), 'panelz7');
?>
		<fieldset class="adminform">
        <legend><?php echo JText::_('COM_ONEPAGE_DISPLAY'); ?></legend>
        <table class="admintable table table-striped" style="width: 100%;">
	
		

        	
	   <tr> 
	    <td class="key">
	     <label for="selected_template"><?php echo JText::_('COM_ONEPAGE_DISPLAY_SELECTED_TEMPLATE_LABEL'); ?></label>
	    </td>
		
	    <td colspan="1" >
	
	     <select style="float: left; max-width: 200px; "  name="selected_template" id="selected_template">
	     <?php
		 
	     if (!empty($this->templates)) 
	     foreach($this->templates as $t)
	     {
		  if ($t == 'extra') continue; 
	      ?>
	      <option value="<?php echo $t; ?>" <?php if ((empty($selected_template) && ($t=='default')) || ($selected_template == $t)) echo ' selected="selected" '; ?>><?php echo $t; ?></option>
	      <?php
	     }
	     ?>
	     </select>
		
		
		 </td>
		<td colspan="1">
		
		 <input style="float: left;" type="checkbox" name="load_min_bootstrap" value="1" <?php if (!empty($load_min_bootstrap)) echo ' checked="checked" '; ?> id="load_min_bootstrap" /><label style="float: left; clear: right; margin: 0;" for="load_min_bootstrap"><?php echo JText::_('COM_ONEPAGE_DISPLAY_LOAD_MIN_BOOTSTRAP'); ?></label>
		 <?php if (false) { ?>
		 <input style="float: left;"type="checkbox" name="opc_rtl" value="1" <?php if (!empty($opc_rtl)) echo ' checked="checked" '; ?> id="opc_rtl" /><label style="float: left; clear: right; margin: 0;" for="opc_rtl"><?php echo JText::_('COM_ONEPAGE_DISPLAY_OPC_RTL'); ?></label>
		 <?php } ?>
		  
		 <br />
	     <input class="text_area" type="hidden" name="override_css_by_class" id="override_css_by_class" value=""/>
	     <input class="text_area" type="hidden" name="override_css_by_id" id="override_css_by_id" value="<?php if (!empty($op_ids)) echo $op_ids ?>"/>
		 <input type="hidden" name="php_logged" value="onepage.logged.tpl.php" />
		 <input type="hidden" name="css_logged" value="onepage.css" />
 		 <input type="hidden" name="php_unlogged" value="onepage.unlogged.tpl.php" />
		 <input type="hidden" name="css_unlogged" value="onepage.css" />

	    </td>
		</tr>
		
		<?php 
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$selected_template.DIRECTORY_SEPARATOR.'config.xml'))
		{
		?>
		<tr>
	    <td class="key">
		<label><?php echo JText::_('COM_ONEPAGE_THEME_CONFIG'); ?></label>
		</td>
		<td colspan="2">
				 <a id="theme_config" href="index.php?option=com_onepage&amp;view=themeconfig"><?php echo JText::_('COM_ONEPAGE_THEMESPECIFIC_CONFIG').' '.$selected_template.'...'; ?></a>
		</td>
		</tr>
		<?php 
		}
		?>
		
		
		<tr>
	    <td class="key"><label><?php echo JText::_('COM_ONEPAGE_RENAME_THEME'); ?></label><?php OPCVideoHelp::show('COM_ONEPAGE_RENAME_THEME'); ?></td>
		<td>
				 <input type="button" class="btn btn-small btn-success" name="rename_theme" value="<?php echo JText::_('COM_ONEPAGE_RENAME_TO_CUSTOM');  ?>" id="rename_theme" onclick="javascript: submitbutton('rename_theme');"/>
		</td>
		<td>
				<label for="rename_theme"><?php echo JText::_('COM_ONEPAGE_RENAME_THEME_DESC'); ?> 
				</label>
		</td>
		</tr>
       
	
        
        </table>
        </fieldset>
		
		
		
		
		
        <?php 
         echo $pane->endPanel();
		 




                    echo $pane->startPanel(JText::_('COM_ONEPAGE_REGISTRATION_PANEL'), 'panela8');
					
					?>
					<fieldset class="adminform">
		 <legend><?php echo JText::_('COM_ONEPAGE_REGISTRATION'); ?></legend>
		 <table class="admintable table table-striped" id="comeshere2x" style="width: 100%;">
	
				 
		 	<tr>
	    <td class="key">
	     <label for="op_usernameisemail" ><?php echo JText::_('COM_ONEPAGE_REGISTRATION_USERNAME_IS_EMAIL_LABEL'); ?></label><?php OPCVideoHelp::show('COM_ONEPAGE_REGISTRATION_USERNAME_IS_EMAIL_LABEL'); ?>
	    </td>
	    <td  >
	     <input type="checkbox" name="op_usernameisemail" id="op_usernameisemail" value="op_usernameisemail" <?php if (isset($op_usernameisemail)) if ($op_usernameisemail==true) echo 'checked="checked"';?> />
	    </td>
	    <td>
	     <?php echo JText::_('COM_ONEPAGE_REGISTRATION_USERNAME_IS_EMAIL_DESC'); ?>
	    </td>
		</tr>
		
	   

	  
		
		
	   
	    <tr>
		 <td class="key">
		 <label for="double_email" ><?php echo JText::_('COM_ONEPAGE_REGISTRATION_EMAIL_DOUBLE_LABEL'); ?></label>
		  
		</td>
		<td>
		  <input type="checkbox" name="double_email" value="1" <?php if (!empty($double_email)) echo ' checked="checked" '; ?>/> 
		</td>
		<td>
		<?php echo JText::_('COM_ONEPAGE_REGISTRATION_EMAIL_DOUBLE_DESC'); ?>
		</td>
		</tr>
			
	<tr>
	    <td class="key">
	     <label for="op_no_display_name" ><?php echo JText::_('COM_ONEPAGE_REGISTRATION_NO_DISPLAY_NAME_LABEL'); ?></label>
	    </td>
	    <td>
	     <input class="op_no_display_name" type="checkbox" value="1" name="op_no_display_name" id="op_no_display_name" <?php if (!empty($op_no_display_name)) echo 'checked="checked"'; ?>/>
	    </td>
	    <td><?php echo JText::_('COM_ONEPAGE_REGISTRATION_NO_DISPLAY_NAME_DESC'); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
	     <label for="op_create_account_unchecked" ><?php echo JText::_('COM_ONEPAGE_REGISTRATION_CREATE_ACCOUNT_UNCHECKED_LABEL'); ?></label>
	    </td>
	    <td>
	     <input class="op_create_account_unchecked" type="checkbox" value="1" name="op_create_account_unchecked" id="op_create_account_unchecked" <?php if (!empty($op_create_account_unchecked)) echo 'checked="checked"'; ?>/>
	    </td>
	    <td><?php echo JText::_('COM_ONEPAGE_REGISTRATION_CREATE_ACCOUNT_UNCHECKED_DESC'); ?>
	    </td>
	</tr>        
	
	
	

	<tr>
	
	
	<td class="key">
	  	    
	     <label><?php echo JText::_('COM_ONEPAGE_ACYMAILING_CHECKBOX_LABEL'); ?></label>
	    

	</td>
	<td colspan="2">
	  
		
			<input type="checkbox" id="opc_acymailing_checkbox" name="opc_acymailing_checkbox" value="opc_acymailing_checkbox" <?php if (!empty($opc_acymailing_checkbox)) echo ' checked="checked" '; ?> /> <input type="text" value="<?php if (isset($opc_acy_id)) echo $opc_acy_id; else echo "2"; ?>" name="opc_acy_id" />
		 <?php echo JText::_('COM_ONEPAGE_ACYMAILING_CHECKBOX_DESC'); 
		 ?><br /><label for="default_acy_checked"><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS'); ?>
		 <select name="default_acy_checked" id="default_acy_checked">
		 <option value=""><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_NOTCHECKED'); ?></option>
		 <option <?php if (!empty($default_acy_checked)) echo ' selected="selected" '; ?> value="1"><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_CHECKED'); ?></option>
		 </select>
		 </label>
	    </td>
	   
	</tr>

	
	
	
		<tr>
	
	
	<td class="key">
	  	    
	     <label><?php echo JText::_('COM_ONEPAGE_ITALIAN_CHECKBOX_LABEL'); ?></label>
	    

	</td>
	<td colspan="2">
	  
		
			<input type="checkbox" id="opc_italian_checkbox" name="opc_italian_checkbox" value="opc_italian_checkbox" <?php if (!empty($opc_italian_checkbox)) echo ' checked="checked" '; ?> /> 
		 <?php echo JText::_('COM_ONEPAGE_ITALIAN_CHECKBOX_DESC'); ?><br /><label for="default_italian_checked"><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS'); ?></label>
		 <select name="default_italian_checked" id="default_italian_checked">
		 <option value=""><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_NOTCHECKED'); ?></option>
		 <option <?php if (!empty($default_acy_checked)) echo ' selected="selected" '; ?> value="1"><?php echo JText::_('COM_ONEPAGE_DEFAULT_STATUS_CHECKED'); ?></option>
		 </select>
	    </td>
	   
	</tr>

	

	
	

	
		</table>
		</fieldset>
		
		
		<?php
		   $VM_REGISTRATION_TYPE = $opc_registraton_type;
		   
 
   
   ?>
   
   
					<fieldset class="adminform">
					<legend><?php echo JText::_('COM_ONEPAGE_REGISTRATION_VIRTUEMART'); ?></legend>
					
		<table class="admintable table table-striped" id="comeshere2" style="width: 100%;">
		 <tr>
	    <td class="key"><label>
		<?php echo JText::_('COM_ONEPAGE_JOOMLA_ACTIVATION'); ?></label>
		</td>
		
		<td>
		<a class="opcmodal" href="index.php?option=com_config&amp;view=component&amp;component=com_users&amp;path=&amp;tmpl=component" rel="<?php echo htmlentities("{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}"); ?>">
<?php echo JText::_('COM_ONEPAGE_REGISTRATION_VIRTUEMART_ACTIVATION'); ?> 
</a>
		</td>
		
		</tr>
		
		<tr>
		<td class="key">
		  <label for="opc_registraton_type">
<?php echo JText::_('COM_ONEPAGE_SELECT_REGISTRATION_TYPE'); echo JText::_('COM_ONEPAGE_WILL_ALTER_VIRTUEMART_CONFIGURATION'); ?>
</label>
<?php OPCVideoHelp::show('COM_ONEPAGE_SELECT_REGISTRATION_TYPE'); ?>
		</td>
		<td>
		<select <?php if (!$is_admin) echo ' disabled="disabled" '; ?> name="opc_registraton_type" id="opc_registraton_type">
 <?php 
 
 $usersConfig = JComponentHelper::getParams( 'com_users' );
 $registration_enabled = (bool)$usersConfig->get('allowUserRegistration', 0); 
 
 
 echo '<option value="NO_REGISTRATION"';
  if ($VM_REGISTRATION_TYPE=='NO_REGISTRATOIN') 
 echo ' selected="selected"'; 
 echo '>'; 
 echo JText::_("COM_ONEPAGE_NO_REGISTRATION").'</option>'; 
 
 if (!empty($registration_enabled)) {
 echo '<option value="OPTIONAL_REGISTRATION"';
  if ($VM_REGISTRATION_TYPE=='OPTIONAL_REGISTRATION') 
 echo ' selected="selected"'; 
echo '>'; 
 echo JText::_("COM_ONEPAGE_OPTIONAL_REGISTRATION").'</option>'; 
 
 echo '<option value="SILENT_REGISTRATION"';
  if ($VM_REGISTRATION_TYPE=='SILENT_REGISTRATION') 
 echo ' selected="selected"'; 
echo '>'; 
 echo JText::_("COM_ONEPAGE_SILENT_REGISTRATION").'</option>'; 
 
 
 echo '<option value="NORMAL_REGISTRATION"';
  if ($VM_REGISTRATION_TYPE=='NORMAL_REGISTRATION') 
 echo ' selected="selected"'; 
 echo '>'; 
 echo JText::_("COM_ONEPAGE_NORMAL_REGISTRATION").'</option>'; 
 }
 ?>
</select>
		</td>
		</tr>
		
		
		
		
		</table>
					
					

</fieldset>
<?php
					echo $pane->endPanel();
	
	echo $pane->startPanel(JText::_('COM_ONEPAGE_OPC_EXTENSIONS_PANEL'), 'panel6g9');
?>
<fieldset><legend><?php echo JText::_('COM_ONEPAGE_OPC_EXTENSIONS'); ?></legend>
	<?php 
	/*
	if (empty($this->exthtml)) echo JText::_('COM_ONEPAGE_OPC_EXTENSIONS_NOEXT'); 
	else
	echo $this->exthtml; 
	*/
	?><input type="hidden" name="ext_id" value="" rel="" id="ext_id" /><table class="admintable table table-striped" id="extension_list">
	<?php
	if (!empty($this->opcexts))
	 {
	   $li = 0; 
	   foreach ($this->opcexts as $ext)
	    {
		
		  echo '<tr>'; 
		  echo '<td>'.$ext['name'].'</td>';  
		  echo '<td>'.$ext['description'].'</td>'; 
		  if (!empty($ext['data']))
		  {
		  if (!empty($ext['link']))
		  		  echo '<td><div style="color: green;" href="'.$ext['link'].'">'.JText::_('COM_ONEPAGE_INSTALLED').'<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_UPDATE_OPCEXTENSION').'" /></div></td>'; 
		  else
echo '<td style="color: green;">'.JText::_('COM_ONEPAGE_INSTALLED').'<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_UPDATE_OPCEXTENSION').'" /></td>'; 
		  //
		  }
		  else
		   {
		   echo '<td>'; 
		   echo '<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_INSTALL_OPCEXTENSION').'" />';
		   echo '&nbsp;</td>'; 		   
		   }
		  echo '</tr>'; 
		
		
		 $li++; 
		}
	 }
	 
	?></table>
</fieldset>

<?php
	echo $pane->endPanel();
	echo $pane->endPane();
		?>
  </form>

<?php
echo ob_get_clean();
function checkFile($file, $file2=null)
{
 $pi = pathinfo($file);
 if (!empty($pi['extension']))
  $name = str_replace('.'.$pi['extension'], '', $pi['basename']);
 else $name = $pi['basename']; 

 $orig = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'vm_files'.DIRECTORY_SEPARATOR.$pi['basename'];
 if (!empty($file2)) $orig = $file2;
 
 if (!file_exists($orig)) return 'Cannot Check';
 if (!file_exists($orig) && (file_exists($file))) return 'OK';
 if (file_exists($file))
 {
  
  $d1 = filemtime($file);
  $d2 = filemtime($orig);
  if ($d2>$d1)
  { 
  $d1 = hash_file('md5',$file);
  $d2 = hash_file('md5',$orig);
  if ($d1 != $d2 )
  {
   if (strpos($file, 'templates')!==false)
   return 'Template will not be overwritten'.retI($name, 'template');
   else
   return 'Upgrade'.retI($name, 'install');
  }
  else return 'OK'.retI($name, 'ok');
  
  }
  
  else
  return 'OK'.retI($name, 'ok');; 
 }
 else return 'File not found'.retI($name, 'install');;
}

function retI($name, $task)
{
 return '<input type="hidden" name="'.$name.'" value="'.$task.'" />';
}

// functions to parse variables
function parseP($hidep)
{
 $hidep = str_replace(' ', '', $hidep);
 $arr = explode (',', $hidep);
 return $arr;
}
// returns true if an payment id is there
function isThere($id, $hidep)
{


 
 $hidep = ','.$hidep.',';
 if (strpos($hidep, ','.$id.',') !== false) return true;
 if (strpos($hidep, ','.$id.'/') !== false) return true;
 return false;
}
// for an payment id get a default payment id 
function getDefP($id, $hidep)
{
 $hidep = ','.$hidep.',';
 if (strpos($hidep, '/'.$id.',') !== false) return true;
 return false;
 
}
$_SESSION['endmem'] = memory_get_usage(true); 
$mem =  $_SESSION['endmem'] - $_SESSION['startmem'];
//echo 'Cm: '.$mem.' All:'.$_SESSION['endmem'];
$document = JFactory::getDocument();
 
// Add Javascript
$js = '
//<![CDATA[
		if ((typeof window != \'undefined\') && (typeof window.addEvent != \'undefined\'))
			   {
			   window.addEvent(\'domready\', function() {
			      ';
				  if (!OPCJ3)
				  $js .= '
			      initRows(); 
				  op_checkHt();
				  '; 
				  
$js .= '				  
				   '; 
if (empty($disable_check))
$js .= '
				if (typeof getOPCExts != \'undefined\')
				  getOPCExts(); 
'; 
$js .= '				  
			    });
			   }
			   else
			   {
			     if(window.addEventListener){ // Mozilla, Netscape, Firefox
			window.addEventListener("load", function(){ 
			initRows();  
			op_checkHt(); 
			}, false);
			 } else { // IE
			window.attachEvent("onload", function(){ 
			op_checkHt(); 
			initRows();  
			});
			 }
			   }
			 
    
//]]>
';
$document->addScriptDeclaration($js); 

echo '</div></div>'; 


