<?php
/**
 * @package		RuposTel.com
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Cache Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @since		1.6
 */
class JModelShopperfields extends OPCModel
{
	
	function store($data = null) {
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'language.php'); 
	    require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
	    // load basic stuff:
	    OPCLang::loadLang(); 
		
		$this->removeCache(); 		
		
		if (empty($data))
		 $data = JRequest::get('post');
		
		if (!empty($data['one_or_the_other'])) {
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php'); 
		OPCUserFields::$cacheDisabled = true; 
		
	  $one_or_the_other = array(); $one_or_the_other2 = array(); 
	  foreach ($data['one_or_the_other'] as $k=>$v)
	  {
		  
		  
		  $f1 = $v; 
		  
		  
		  if (empty($f1)) continue; 
		  
		  $ign = array('name', 'tos', 'username', 'password', 'password2', 'email', 'email2', 'agreed', 'register_account'); 
		  if (in_array($f1, $ign)) continue; 
		  
		  if (empty($data['one_or_the_other2'][$k])) continue; 
		  $f2 = $data['one_or_the_other2'][$k]; 
		  
		  if (in_array($f2, $ign)) continue; 
	
		  $one_or_the_other[$k] = $f1;
		  $one_or_the_other2[$k] = $f2;
		  
		$r = OPCUserFields::getIfRequired($f1); 
		if (!empty($r)) {
		   OPCUserFields::setNotRequired($f1); 
		   $msg .= JText::_('COM_ONEPAGE_SET_NOT_REQUIRED').': '.$f1."<br />"; 
		}
		$r = OPCUserFields::getIfRequired($f2); 
		if (!empty($r)) {
		   OPCUserFields::setNotRequired($f2); 
		   $msg .= JText::_('COM_ONEPAGE_SET_NOT_REQUIRED').': '.$f2."<br />"; 
		}
		  
		  
	  }
	  
	  OPCconfig::save('one_or_the_other2', $one_or_the_other2); 
	  OPCconfig::save('one_or_the_other', $one_or_the_other); 
	
	}
	else {
	  OPCconfig::save('one_or_the_other2', array()); 
	  OPCconfig::save('one_or_the_other', array()); 
	}
	
	
	
	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php'); 
	
	OPCUserFields::$cacheDisabled = true; 
	
	if (!empty($data['business_fields']))
	foreach ($data['business_fields'] as $bfn) {
		
		$r = OPCUserFields::getIfRequired($bfn); 
		if (!empty($r)) {
		   $data['business_obligatory_fields'][$bfn] = $bfn; 
		   OPCUserFields::setNotRequired($bfn); 
		   $msg .= JText::_('COM_ONEPAGE_SET_NOT_REQUIRED').': '.$bfn."<br />"; 
		}
		
		
	}
	if (!empty($data['business_fields2']))
	foreach ($data['business_fields2'] as $bfn) {
		$r = OPCUserFields::getIfRequired($bfn); 
		if (!empty($r)) {
		   $data['business_obligatory_fields'][$bfn] = $bfn; 
		   OPCUserFields::setNotRequired($bfn); 
		   $msg .= JText::_('COM_ONEPAGE_SET_NOT_REQUIRED').': '.$bfn."<br />"; 
		}
	}
	
	
	if ((!empty($data['business_selector'])) && ((!empty($data['business_fields2']))))
	{
	
		 if (!is_array($data['business_fields2']))
		 {
			 $data['business_fields2'] = array($data['business_fields2']); 
			 
			 
		 }
		 if (!empty($data['is_business2']))
		 {
			 if (!isset($data['business_fields'])) $data['business_fields'] = array(); 
			 
			 $data['business_fields'] = array_merge($data['business_fields'], $data['business_fields2']); 
			 
			 
			 
			 $cfg .= ' $is_business2 = true; '; 
			 
		 }
		 
		 if (!empty($data['business_fields2']))
		 {
			 $im = array(); 
			 foreach ($data['business_fields2'] as $k=>$v)
			 {
				 $im[] = "'".addslashes($v)."'"; 
			 }
			 {
				 
				 $im = implode(',', $im); 
				 $cfg .= ' $business_fields2 = array('.$im.'); '; 
			 }
			 
			 if (!empty($data['business2_value']))
			 {
				  $cfg .= ' $business2_value = \''.addslashes($data['business2_value']).'\'; '; 
			 }
			 
		 }
		 
	}
	
	
	 if (!empty($data['estimator_fields']))
		 {
			 $im = array(); 
			 foreach ($data['estimator_fields'] as $k=>$v)
			 {
				 $im[] = "'".addslashes($v)."'"; 
			 }
			 if (!empty($im)) {
				 
				 $im = implode(',', $im); 
				 $cfg .= ' $estimator_fields = array('.$im.'); '; 
			 }
			 
			
			 
		 }
	
	}
	
}