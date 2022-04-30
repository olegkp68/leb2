<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_rmenu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once(__DIR__.DIRECTORY_SEPARATOR.'helper.php'); 
$hasItems = modCartsaveHelper::hasItems(); 
$cart_names = modCartsaveHelper::getNames(); 
$return = base64_encode(JUri::getInstance()->toString());

if ((isset($module))  && (!empty($module->id))) $id = (int)$module->id; 
else $id = 0; 
$module_id = $id; 
$user_id = JFactory::getUser()->get('id'); 


$root = Juri::root();  
if (substr($root, -1) === '/') $root = substr($root, 0, -1); 

JFactory::getLanguage()->load('mod_cartsave', __DIR__); 
JFactory::getLanguage()->load('mod_cartsave'); 

$MOD_CARTSAVE_SHARE_EMAIL_SUBJECT = JText::_($params->get('MOD_CARTSAVE_SHARE_EMAIL_SUBJECT', 'MOD_CARTSAVE_SHARE_EMAIL_SUBJECT')); 
$MOD_CARTSAVE_SHARE_EMAIL_SUBJECT = str_replace('{sitename}', JFactory::getConfig()->get('sitename'), $MOD_CARTSAVE_SHARE_EMAIL_SUBJECT); 

$MOD_CARTSAVE_SHARE_EMAIL_BODY = JText::_($params->get('MOD_CARTSAVE_SHARE_EMAIL_BODY', 'MOD_CARTSAVE_SHARE_EMAIL_BODY')); 
$MOD_CARTSAVE_SHARE_EMAIL_BODY = str_replace('{sitename}', JFactory::getConfig()->get('sitename'), $MOD_CARTSAVE_SHARE_EMAIL_BODY); 


require JModuleHelper::getLayoutPath('mod_cartsave', $params->get('layout', 'default')); 
