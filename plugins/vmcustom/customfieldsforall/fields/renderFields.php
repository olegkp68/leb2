<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_PLUGINS.DIRECTORY_SEPARATOR.'vmcustom'.DIRECTORY_SEPARATOR.'customfieldsforall'.DIRECTORY_SEPARATOR.'bootstrap.php';

use Joomla\CMS\Factory as JFactory;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Language\CustomFieldsForAllLanguageHandlerFactory;

class RenderFields
{
    /**
     * Fetch the datatype field renedered
     *
     * @param string $fieldname
     * @param int $virtuemart_custom_id
     * @param string $value
     * @return string
     */
	function fetchDatatype($fieldname='data_type', $virtuemart_custom_id, $value='string')
	{

		$options_array=array(
		'string'=>\JText::_('PLG_CUSTOMSFORALL_STRING'),
		'color_hex'=>\JText::_('PLG_CUSTOMSFORALL_COLOR_HEX'),
		'int'=>\JText::_('PLG_CUSTOMSFORALL_INT'),
		'float'=>\JText::_('PLG_CUSTOMSFORALL_FLOAT')
		);

		if($virtuemart_custom_id){
			$html=$options_array[$value];
			$html.='<input type="hidden" name="'.$fieldname.'" value="'.$value.'"/>';
		}
		else{
			$options_new_array=array();
			foreach ($options_array as $key=>$val){
				$myOpt=new stdClass();
				$myOpt->text=$val;
				$myOpt->value=$key;
				$options_new_array[]=$myOpt;
			}
			$html=JHtml::_('select.genericlist',$options_new_array,$fieldname,'class="inputbox required"','value', 'text',$value);
		}

		return $html;
	}

	/**
	 * Fetch the display types field renedered
	 *
	 * @param string $fieldname
	 * @param int $virtuemart_custom_id
	 * @param unknown $default
	 * @return string
	 */
	function fetchDisplaytypes($fieldname, $virtuemart_custom_id,$default)
	{
		$displaytypes=array(
		'button'=>\JText::_('PLG_CUSTOMSFORALL_BTN'),
		'button_multi'=>\JText::_('PLG_CUSTOMSFORALL_BTN_MULTI'),
		'color'=>\JText::_('PLG_CUSTOMSFORALL_COLOR_BTN'),
		'color_multi'=>\JText::_('PLG_CUSTOMSFORALL_COLOR_BTN_MULTI'),
		'checkbox'=>\JText::_('PLG_CUSTOMSFORALL_CHECKBOXES'),
		'radio'=>\JText::_('PLG_CUSTOMSFORALL_RADIO_BTN'),
		'select'=>\JText::_('PLG_CUSTOMSFORALL_SELECT_LIST'),
		);

		//assoc array containing the valid display types for each datatype
		$datatypes=array(
		'string'=>array('display_types'=>array('button','button_multi','color','color_multi','checkbox','radio','select')),
		'color_hex'=>array('display_types'=>array('button','button_multi','color','color_multi','checkbox','radio','select')),
		'int'=>array('display_types'=>array('button','button_multi','checkbox','radio','select')),
		'float'=>array('display_types'=>array('button','button_multi','checkbox','radio','select')),
		);

		if(!empty($virtuemart_custom_id)){
			$customfield=Customfield::getInstance($virtuemart_custom_id);
			$custom_params=$customfield->getCustomfieldParams($virtuemart_custom_id);
			$datatype=$custom_params['data_type'];
		}

		$options=array();

		foreach ($displaytypes as $key=>$value){
			$option=array(
			'value'=>$key,
			'text'=>$value,
			);

			if(isset($datatype) && !empty($datatypes[$datatype])){
				if(!in_array($key, $datatypes[$datatype]['display_types'])){
					$option['attr']=array('disabled'=>'true');
					if($default==$key)$default='button';
				}
			}
			$options[]=$option;
		}

		$properties = array(
	    'id' => 'displaytypes', // HTML id for select field
	    'list.attr' => array('class'=>'inputbox required',),
	    'option.value'=>'value', // key name for value in data array
	    'option.text'=>'text', // key name for text in data array
	    'option.attr'=>'attr', // key name for attr in data array
	    'list.select'=>$default, // value of the SELECTED field
		);

		$html=$result = JHtmlSelect::genericlist($options,$fieldname,$properties);
		return $html;
	}

    /**
     * Function to fetch the custom value inputs
     *
     * @param string $fieldname
     *            - the name with which all the passed inputs will start
     * @param int $virtuemart_custom_id
     * @param int $row
     *            - used mainly within the product form, where each custom has its own row
     */
    function fetchCustomvalues($fieldname, $virtuemart_custom_id, $value = '', $row = 0, $custom_params)
    {
        $languageFactory = new CustomFieldsForAllLanguageHandlerFactory();
        $languageHandler = $languageFactory->get();
        $languages = $languageHandler->getLanguages();

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $view = $jinput->get('view', '', 'STRING');
        $is_jscolor_loaded = $jinput->get('scripts_loaded', false, 'BOOLEAN');
        $existing_values = array();
        $customfield = Customfield::getInstance($virtuemart_custom_id);
        $single_entry = $custom_params['single_entry'];

        $data_type = ! empty($custom_params['data_type']) ? $custom_params['data_type'] : 'string';
        $is_custom_view = false;
        $is_stored = false;
        $class = 'input';
        $html = '';

        if (! empty($virtuemart_custom_id) && $view == 'custom') {
            $is_custom_view = true;
            $existing_values = $customfield->getCustomValues();
        }

        // load the js color script
        if (! empty($custom_params) && $data_type == 'color_hex') {
            $class .= ' color {required:false}';

            // if the color script does not exist we should load the script in the returned html
            if (! $is_jscolor_loaded) {
                $color_script_path = CF4ALL_BASE_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jscolor' . DIRECTORY_SEPARATOR . 'jscolor.js';
                ob_start();
                include ($color_script_path);
                $html .= '<script type="text/javascript">' . ob_get_contents() . '</script>';
                ob_end_clean();
                $jinput->set('scripts_loaded', true);
            }
        }

        ob_start();
        $custom_values_template_path = JPATH_PLUGINS . DIRECTORY_SEPARATOR . 'vmcustom' . DIRECTORY_SEPARATOR . 'customfieldsforall' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR . 'j3'. DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR .'customvalues.php';
        include($custom_values_template_path);
        $html.=ob_get_contents();
        ob_end_clean();

        if ($app->isAdmin() && $view == 'custom') {
            $html .= $this->fetchJEDMessage();
        }
        return $html;
    }

	/**
	 *
	 * @return string
	 */
	public function fetchJEDMessage()
	{
        $html='<div class="help-block" style="clear:both; margin-top:5em;">Are you happy with it? Please post a review at the
            <a target="_blank" href="http://extensions.joomla.org/extensions/extension/extension-specific/virtuemart-extensions/custom-fields-for-all">Joomla! Extensions Directory</a></div>';
        return $html;
	}
}
