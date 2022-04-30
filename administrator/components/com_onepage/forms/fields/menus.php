<?php
defined ('_JEXEC') or die();
jimport('joomla.form.formfield');
class JFormFieldMenus extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'menus';
	protected $type = 'menus';
	function getInput () {
		/*
		if (!class_exists('VmConfig'))
		 require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		 if (!defined('VMPATH_SITE'))
		 VmConfig::loadConfig(); 
		*/
		
		$client_id   = (string) $this->element['clientid'];
		$multiple    = (string) $this->element['multiple'];
		
		
		
		$name = $this->name; 
		
		
		if (!empty($multiple)) {
			$multiple = true; 
			$name = str_replace('[]', '', $name); 
			$name .= '[]'; 
		}
		else {
			$multiple = false; 
		}
		
		$value = $this->value; 
		
		$control_name = 'params'; 
		
		$q = 'select `id`, `menutype`, `title`, `client_id` from #__menu_types where '; 
		$w = array(); 
		
		if ($client_id !== '') {
		$w[] = ' `client_id` = '.(int)$client_id; 
		}
		else {
			$w[] = ' 1=1 '; 
		}
		$q = $q.implode(' and ', $w); 
		
		$db = JFactory::getDBO(); 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		//
		$html = '<select name="'.$name; 
		
		$html .= '" '; 
		if ($multiple) { $html .= ' multiple="multiple" '; }
		$html .= ' >'; 
		$html .= '<option value="">All</option>'; 
		
		
		
		foreach ($res as $row)
		{
			
			$sgid = $row['menutype']; 
			if ((!empty($this->value)) && (in_array($sgid, $this->value))) $selected = ' selected="selected" '; 
			else $selected = ''; 
			$html .= '<option '.$selected.' value="'.$sgid.'">'.htmlentities($row['title']).'</option>'; 
		}
		$html .= '</select>'; 
		
		return $html; 
	
	}
	
	
	
}