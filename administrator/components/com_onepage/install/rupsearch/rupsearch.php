<?php 
/**
 * @version		stockavai.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		COMMERCIAL 
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemRupsearch extends JPlugin
{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	function onAfterRoute() {
		$option = JRequest::getVar('option', ''); 
		$keyword = JRequest::getVar('keyword', ''); 
		$view = JRequest::getVar('view', ''); 
		$virtuemart_category_id = JRequest::getInt('virtuemart_category_id', 0); 
		if ($option === 'com_virtuemart')
		if ($view === 'category')
		if (empty($virtuemart_category_id))
		if (!empty($keyword)) {
			$this->setVars($keyword); 
		}
		
		
	}
	
	function setVars($keyword) {
		JRequest::setVar('option', 'com_rupsearch'); 
		JRequest::setVar('product_keyword', urldecode($keyword)); 
		JRequest::setVar('view', 'search'); 
		JRequest::setVar('layout', 'default'); 
	}
	
	
	
}
