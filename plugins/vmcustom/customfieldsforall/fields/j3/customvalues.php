<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

defined('_JEXEC') or die('Restricted access');
require_once __DIR__.'/../../bootstrap.php';
if(!class_exists('RenderFields'))require(__DIR__.DIRECTORY_SEPARATOR.'/../renderFields.php');

use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;

Class JFormFieldCustomvalues extends JFormField{

	protected $type = 'customvalues';


	protected function getInput()
	{
		$fieldname='cf_val';
		$jinput=JFactory::getApplication()->input;
		$virtuemart_custom_id=$jinput->get('virtuemart_custom_id',array(),'ARRAY');
		if(is_array($virtuemart_custom_id))$virtuemart_custom_id=end($virtuemart_custom_id);
		$customfield=Customfield::getInstance($virtuemart_custom_id);
		$custom_params=$customfield->getCustomfieldParams($virtuemart_custom_id);
		$custom_params['single_entry']=!empty($custom_params['is_price_variant'])?true:false;
		$renderFields=new RenderFields;
		$html=$renderFields->fetchCustomvalues($fieldname,$virtuemart_custom_id,$this->value,$row=0,$custom_params);
		return $html;
	}

	protected function getLabel(){
		$title='';
		if(empty($this->value))$title='<strong>'.JText::_($this->element['label']).'</strong><br />'.JText::_($this->element['description']);
		$html='<label data-original-title="<strong>'.JText::_($this->element['label']).'</strong><br />'.JText::_($this->element['description']).'" id="params_display_type-lbl" for="params_'.$this->element['name'].'" class="hasTooltip" title="'.$title.'">'.JText::_($this->element['label']).'</label>';
		return $html;
	}
}
