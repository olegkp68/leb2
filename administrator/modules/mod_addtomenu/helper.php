<?php
/**
 * @package         Add to Menu
 * @version         6.1.6PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2018 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\Xml as RL_Xml;

class ModAddToMenu
{
	public function __construct(&$params)
	{
		// Load plugin parameters
		$this->params = RL_Parameters::getInstance()->getModuleParams('addtomenu', 1, $params);
	}

	public function render()
	{
		if ( ! isset($this->params->display_link))
		{
			return;
		}

		$option = JFactory::getApplication()->input->get('option');

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$folder = JPATH_ADMINISTRATOR . '/components/' . $option . '/addtomenu';
		if ( ! JFolder::exists($folder))
		{
			$folder = JPATH_ADMINISTRATOR . '/modules/mod_addtomenu/components/' . $option;
		}

		$comp_file = '';
		$template  = '';
		$vars      = [];

		foreach (JFolder::files($folder, '.xml') as $filename)
		{
			$file = $folder . '/' . $filename;

			$xml = RL_Xml::toObject($file, 'params');

			if ( ! isset($xml->required))
			{
				continue;
			}

			if ( ! self::checkRequiredFields($xml->required, $vars))
			{
				continue;
			}

			$comp_file = JFile::stripExt($filename);
			$template  = $xml;
			break;
		}

		if ( ! $comp_file)
		{
			return;
		}

		$opt = $option;
		// load the admin language file
		if ($opt == 'com_categories')
		{
			$opt = JFactory::getApplication()->input->get('extension', 'com_content');
		}
		$lang = JFactory::getLanguage();
		$lang->load('mod_addtomenu', JPATH_ADMINISTRATOR);
		$lang->load($opt, JPATH_ADMINISTRATOR);
		$lang->load($opt . '.sys', JPATH_ADMINISTRATOR);

		JHtml::_('jquery.framework');
		JHtml::_('behavior.modal');

		$script = "var addtomenu_root = '" . JUri::root() . "';";
		RL_Document::scriptDeclaration($script);

		RL_Document::script('regularlabs/script.min.js');
		RL_Document::script('addtomenu/script.min.js', '6.1.6.p');
		RL_Document::stylesheet('regularlabs/style.min.css');
		RL_Document::stylesheet('addtomenu/style.min.css', '6.1.6.p');

		// set height for popup
		$popup_width  = 600 + (int) $this->params->adjust_modal_w;
		$popup_height = 440 + (int) $this->params->adjust_modal_h;
		if ($this->params->display_field_access)
		{
			$popup_height += 46;
		}
		if ($this->params->display_field_language)
		{
			$popup_height += 46;
		}
		if ($this->params->display_field_template_style)
		{
			$popup_height += 46;
		}
		if (isset($template->adjust_height))
		{
			$popup_height += (int) $template->adjust_height;
		}

		if (isset($template->extras) && is_object($template->extras) && isset($template->extras->extra))
		{
			if ( ! is_array($template->extras->extra))
			{
				$template->extras->extra = [$template->extras->extra];
			}
			foreach ($template->extras->extra as $element)
			{
				if (isset($element->type))
				{
					switch ($element->type)
					{
						case 'radio':
							// add height for every line
							$popup_height += 46 + (23 * (count($element->values) - 1));
							break;
						case 'textarea':
							$popup_height += 140;
							break;
						case 'hidden':
						case 'toggler':
							// no height
							break;
						default:
							$popup_height += 46;
							break;
					}
				}
			}
		}

		$link = 'index.php?rl_qp=1'
			. '&folder=administrator.modules.mod_addtomenu'
			. '&file=popup.php'
			//. '&comp=' . $comp_file
			. '&params=' . urlencode(json_encode($this->params));

		$uri       = JUri::getInstance();
		$url_query = $uri->getQuery(true);
		foreach ($url_query as $key => $val)
		{
			$vars[$key] = $val;
		}
		if ( ! isset($vars['option']))
		{
			$vars['option'] = $option;
		}

		foreach ($vars as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}

			if (is_array($val))
			{
				$val = (string) $val[0];
			}

			$link .= '&vars[' . $key . ']=' . $val;
		}

		$text_ini = strtoupper(str_replace(' ', '_', $this->params->button_text));
		$text     = JText::_($text_ini);

		if ($text == $text_ini)
		{
			$text = JText::_($this->params->button_text);
		}

		$tip = '';
		if ($this->params->display_tooltip)
		{
			JHtml::_('bootstrap.tooltip');
			$tip = '<strong>' . JText::_('ADD_TO_MENU') . '</strong><br>' . JText::_($template->name);
		}

		if ($this->params->display_toolbar_button)
		{
			// Generate html for toolbar button
			$html    = [];
			$html[]  = '<a href="' . $link . '" class="btn btn-small addtomenu_link modal' . ($tip ? ' hasTooltip" title="' . $tip : '') . '"'
				. ' rel="{handler: \'iframe\', size: {x: ' . $popup_width . ', y: ' . $popup_height . '}}">';
			$html[]  = '<span class="icon-reglab icon-addtomenu"></span> ';
			$html[]  = $text;
			$html[]  = '</a>';
			$toolbar = JToolBar::getInstance('toolbar');
			$toolbar->appendButton('Custom', implode('', $html));
		}

		if ($this->params->display_link)
		{
			// Generate html for status link
			$html   = [];
			$html[] = '<div class="btn-group addtomenu">';
			$html[] = '<span class="btn-group separator"></span>';
			$html[] = '<a href="' . $link . '" class="addtomenu_link modal' . ($tip ? ' hasTooltip" title="' . $tip : '') . '"'
				. ' rel="{handler: \'iframe\', size: {x: ' . $popup_width . ', y: ' . $popup_height . '}}">';
			if ($this->params->display_link != 'text')
			{
				$html[] = '<span class="icon-reglab icon-addtomenu"></span> ';
			}
			if ($this->params->display_link != 'icon')
			{
				$html[] = $text;
			}
			$html[] = '</a>';
			$html[] = '</div>';
			echo implode('', $html);
		}
	}

	public static function getVar($var)
	{
		if ($var[0] == '$')
		{
			$var = substr($var, 1);
			$var = self::getVal($var);
		}

		return $var;
	}

	public static function getVal($value, $vars = '')
	{
		$url   = JFactory::getApplication()->input->getVar('url');
		$extra = JFactory::getApplication()->input->getVar('extra');

		if (isset($vars[$value]))
		{
			$val = $vars[$value];
		}
		else if (isset($url[$value]))
		{
			$val = $url[$value];
		}
		else if (isset($extra[$value]))
		{
			$val = $extra[$value];
		}
		else
		{
			$val = JFactory::getApplication()->input->getVar($value);
			if ($val == '')
			{
				$val = self::getUserStateFromRequest($value);
			}
		}

		if (is_array($val))
		{
			$val = $val[0];
		}

		return $val;
	}

	public static function getUserStateFromRequest($value)
	{
		$context = [];
		if (JFactory::getApplication()->input->get('option'))
		{
			$context[] = JFactory::getApplication()->input->get('option');
		}
		if (JFactory::getApplication()->input->get('layout'))
		{
			$context[] = JFactory::getApplication()->input->get('layout');
		}
		else if (JFactory::getApplication()->input->get('view'))
		{
			$context[] = JFactory::getApplication()->input->get('view');
		}
		else
		{
			switch (JFactory::getApplication()->input->get('option'))
			{
				case 'com_content':
					$context[] = 'articles';
					break;
			}
		}
		$context[] = 'filter';
		$val       = self::getUSFR($value, $context, '.', '.');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '.');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '.', '_');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '_');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '.', '');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '');
		if ($val != '')
		{
			return $val;
		}
		$context[0] = 'global';
		$val        = self::getUSFR($value, $context, '.', '.');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '.');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '.', '_');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '_');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '.', '');
		if ($val != '')
		{
			return $val;
		}
		$val = self::getUSFR($value, $context, '', '');

		return $val;
	}

	public static function getUSFR($value, $context = ['filter'], $glue = '', $glue2 = '')
	{
		return JFactory::getApplication()->getUserStateFromRequest(implode($glue, $context) . $glue2 . $value, 'filter_' . $value);
	}

	public static function checkRequiredFields(&$required, &$vars)
	{
		$required = RL_Array::clean((array) $required);

		if (empty($required))
		{
			return true;
		}

		$pass = true;

		foreach ($required as $key => $values)
		{
			$keyval = self::getVal($key, $vars);

			if (is_string($values))
			{
				$values = explode(',', $values);
			}

			foreach ($values as $val)
			{
				$pass = false;

				switch ($val)
				{
					case '*':
						if (strlen($keyval))
						{
							$pass = true;
						}
						break;
					case '+':
						if ($keyval)
						{
							$pass = true;
						}
						break;
					default:
						if ($keyval == $val)
						{
							$pass = true;
						}
						break;
				}

				if ($pass)
				{
					break;
				}
			}

			if ( ! $pass)
			{
				break;
			}

			$vars[$key] = $keyval;
		}

		return $pass;
	}
}
