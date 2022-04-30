<?php
defined('_JEXEC')or die;

$Itemid = JRequest::getInt('Itemid', 0); 

require_once(__DIR__.DIRECTORY_SEPARATOR.'helper.php'); 
$currentdata = ModQuizHelper::loadData(); 

//store state: 
ModQuizHelper::getProducts(); 

$myconfig = array(); 
$myconfig['module_id'] = (int)$module->id; 
$myconfig['admin'] = false; 

if (!empty($first_id)) {
 $resetUri = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.(int)$first_id); 
}
else $resetUri = ''; 

$myconfig['reset_url'] = $resetUri;


$myconfig['chosenmethod'] = $params->get('chosenmethod', 'GET'); 

$my_itemid = $params->get('my_itemid', 0); 
if (!empty($my_itemid)) {
	$Itemid = (int)$my_itemid;
}
$myconfig['Itemid'] = $Itemid; 

if (!empty($my_itemid)) {
	$myconfig['home_url'] = JRoute::_('index.php?Itemid='.$Itemid.'&empty=1'); 
}
else
if (!empty($primary_category_id)) {
	$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search&virtuemart_category_id='.$primary_category_id.'&empty=1'); 
}
else {
	//$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search'); 
	$myconfig['home_url'] = JRoute::_('index.php?option=com_rupsearch&view=search&Itemid='.$Itemid.'&empty=1'); 
}
?><quizajaxconfig id="quizajaxconfig" data-config="<?php echo htmlentities(json_encode($myconfig)); ?>"></quizajaxconfig><?php

 
$format = JRequest::getVar('format', 'html'); 
if ($format === 'html') {
$path = JModuleHelper::getLayoutPath('mod_quiz', 'default_js'); 
require($path);

?><div id="module_id_<?php echo $module->id; ?>"><?php
}

$path = JModuleHelper::getLayoutPath('mod_quiz'); 

$session = JFactory::getSession(); 
$cleardata = JRequest::getVar('empty', 0); 

if (!empty($cleardata)) {
	
	$session->set('q_brand', ''); 
	$session->set('q_model', ''); 
	
}
$q_brand = JRequest::getVar('q_brand', $session->get('q_brand', '')); 
$q_model = JRequest::getVar('q_model', $session->get('q_model', '')); 

require($path);
if ($format === 'html') {
	?></div>
<?php
}