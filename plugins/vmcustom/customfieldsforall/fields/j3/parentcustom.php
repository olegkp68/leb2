<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2018-2018breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

defined('_JEXEC') or die('Restricted access');
require_once JPATH_PLUGINS.DIRECTORY_SEPARATOR.'vmcustom'.DIRECTORY_SEPARATOR.'customfieldsforall'.DIRECTORY_SEPARATOR.'bootstrap.php';
if(!class_exists('RenderFields'))require(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'vmcustom'.DIRECTORY_SEPARATOR.'customfieldsforall'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'renderFields.php');

Class JFormFieldParentcustom extends JFormField
{

	protected $type = 'customvalues';

	protected function getInput()
	{
		$fieldname='parent_id';
		$jinput=JFactory::getApplication()->input;
		$parents=$this->getParentCustoms();
		$renderFields=new RenderFields;
		$html=$renderFields->fetchParentCustom($fieldname, $parents, $this->value);
		return $html;
	}

	protected function getLabel()
	{
		$title='';
		if(empty($this->value))$title='<strong>'.JText::_($this->element['label']).'</strong><br />'.JText::_($this->element['description']);
		$html='<label data-original-title="<strong>'.JText::_($this->element['label']).'</strong><br />'.JText::_($this->element['description']).'" id="params_display_type-lbl" for="params_'.$this->element['name'].'" class="hasTooltip" title="'.$title.'">'.JText::_($this->element['label']).'</label>';
		return $html;
	}

	/**
	 * Get the custom fields from the db
	 * @return array
	 */
	protected function getParentCustoms()
	{
	    $jinput=JFactory::getApplication()->input;
	    $virtuemart_custom_id=$jinput->get('virtuemart_custom_id',array(),'ARRAY');
	    if(is_array($virtuemart_custom_id))$virtuemart_custom_id=end($virtuemart_custom_id);

	    $db=JFactory::getDbo();
	    $query=$db->getQuery(true);
	    $query->select('*')->from('#__virtuemart_customs')->where('custom_value=\'customfieldsforall\'')->order('custom_title');
	    if(!empty($virtuemart_custom_id))$query->where('virtuemart_custom_id!='.(int)$virtuemart_custom_id);
	    $db->setQuery($query);
	    $results=$db->loadObjectList();
	    return $results;
	}
}
