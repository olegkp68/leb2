<?php
/**
 * @Copyright
 * @package     Field - Component Params Description
 * @author      Aicha Vack 
 *
 * @license GNU/GPL
 */
defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for User Plugin Spambotcheck Joomla! Extensions.
 * Provides a description for Component Parameter Settings
 */
class JFormFieldComponentParamDesc extends JFormField
{
    protected $type = 'componentparamdesc';

    protected function getInput()
    {
 

        $field_value = '<div style="font-size: 1.2em;">' . \JText::_('PLG_USER_SPAMBOTCHECK_COMPONENT_OPTIONS_DESC') . '</div>';
        return $field_value;
    }

    protected function getLabel()
    {
        return;
    }

}
