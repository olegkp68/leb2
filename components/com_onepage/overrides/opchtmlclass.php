<?php
/**
 * New document type for AJAX queries
 *
 * This class was overrided to support addCustomScript and other non raw header insertion from raw view in ajax
 * Later update will include synchronization of the added scripts and css with already generated header data
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 *
 * ORIGINAL LICENSE AND COPYRIGHT
 * 
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * 
 * This file was modified for One Page Checkout use
*/

 // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



JLoader::register('JDocument', JPATH_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'document.php');

jimport('joomla.application.module.helper');
jimport('joomla.utilities.utility');

/**
 * DocumentHTML class, provides an easy interface to parse and display a HTML document
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentOpchtmlclass extends JDocument
{


	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
	  
		parent::__construct($options);

		// Set document type
		$this->_type = 'raw';

		// Set default mime type and document metadata (meta data syncs with mime type by default)
		$this->setMimeEncoding('text/html');
	}
	public function getType()
	{
		return JRequest::getVar('format_override', 'opchtml'); 
	}
	/**
	 * Get the HTML document head data
	 *
	 * @return  array  The document head data in array form
	 *
	 * @since   11.1
	 */
	public function getHeadData()
	{
		$data = array();
		$data['title']       = $this->title;
		$data['description'] = $this->description;
		$data['link']        = $this->link;
		$data['metaTags']    = $this->_metaTags;
		$data['links']       = $this->_links;
		$data['styleSheets'] = $this->_styleSheets;
		$data['style']       = $this->_style;
		$data['scripts']     = $this->_scripts;
		$data['script']      = $this->_script;
		$data['custom']      = $this->_custom;
		return $data;
	}

	/**
	 * Set the HTML document head data
	 *
	 * @param   array  $data  The document head data in array form
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setHeadData($data)
	{
		if (empty($data) || !is_array($data))
		{
			return;
		}

		$this->title = (isset($data['title']) && !empty($data['title'])) ? $data['title'] : $this->title;
		$this->description = (isset($data['description']) && !empty($data['description'])) ? $data['description'] : $this->description;
		$this->link = (isset($data['link']) && !empty($data['link'])) ? $data['link'] : $this->link;
		$this->_metaTags = (isset($data['metaTags']) && !empty($data['metaTags'])) ? $data['metaTags'] : $this->_metaTags;
		$this->_links = (isset($data['links']) && !empty($data['links'])) ? $data['links'] : $this->_links;
		$this->_styleSheets = (isset($data['styleSheets']) && !empty($data['styleSheets'])) ? $data['styleSheets'] : $this->_styleSheets;
		$this->_style = (isset($data['style']) && !empty($data['style'])) ? $data['style'] : $this->_style;
		$this->_scripts = (isset($data['scripts']) && !empty($data['scripts'])) ? $data['scripts'] : $this->_scripts;
		$this->_script = (isset($data['script']) && !empty($data['script'])) ? $data['script'] : $this->_script;
		$this->_custom = (isset($data['custom']) && !empty($data['custom'])) ? $data['custom'] : $this->_custom;

		return $this;
	}
	public function isHtml5()
	{
		return false;
	}
	public function setHtml5($state)
	{
		
	}
	/**
	 * Merge the HTML document head data
	 *
	 * @param   array  $data  The document head data in array form
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function mergeHeadData($data)
	{

		if (empty($data) || !is_array($data))
		{
			return;
		}

		$this->title = (isset($data['title']) && !empty($data['title']) && !stristr($this->title, $data['title']))
			? $this->title . $data['title']
			: $this->title;
		$this->description = (isset($data['description']) && !empty($data['description']) && !stristr($this->description, $data['description']))
			? $this->description . $data['description']
			: $this->description;
		$this->link = (isset($data['link'])) ? $data['link'] : $this->link;

		if (isset($data['metaTags']))
		{
			foreach ($data['metaTags'] as $type1 => $data1)
			{
				$booldog = $type1 == 'http-equiv' ? true : false;
				foreach ($data1 as $name2 => $data2)
				{
					$this->setMetaData($name2, $data2, $booldog);
				}
			}
		}

		$this->_links = (isset($data['links']) && !empty($data['links']) && is_array($data['links']))
			? array_unique(array_merge($this->_links, $data['links']))
			: $this->_links;
		$this->_styleSheets = (isset($data['styleSheets']) && !empty($data['styleSheets']) && is_array($data['styleSheets']))
			? array_merge($this->_styleSheets, $data['styleSheets'])
			: $this->_styleSheets;

		if (isset($data['style']))
		{
			foreach ($data['style'] as $type => $stdata)
			{
				if (!isset($this->_style[strtolower($type)]) || !stristr($this->_style[strtolower($type)], $stdata))
				{
					$this->addStyleDeclaration($stdata, $type);
				}
			}
		}

		$this->_scripts = (isset($data['scripts']) && !empty($data['scripts']) && is_array($data['scripts']))
			? array_merge($this->_scripts, $data['scripts'])
			: $this->_scripts;

		if (isset($data['script']))
		{
			foreach ($data['script'] as $type => $sdata)
			{
				if (!isset($this->_script[strtolower($type)]) || !stristr($this->_script[strtolower($type)], $sdata))
				{
					$this->addScriptDeclaration($sdata, $type);
				}
			}
		}

		$this->_custom = (isset($data['custom']) && !empty($data['custom']) && is_array($data['custom']))
			? array_unique(array_merge($this->_custom, $data['custom']))
			: $this->_custom;

		return $this;
	}

	/**
	 * Adds <link> tags to the head of the document
	 *
	 * $relType defaults to 'rel' as it is the most common relation type used.
	 * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
	 * Typical tag: <link href="index.php" rel="Start">
	 *
	 * @param   string  $href      The link that is being related.
	 * @param   string  $relation  Relation of link.
	 * @param   string  $relType   Relation type attribute.  Either rel or rev (default: 'rel').
	 * @param   array   $attribs   Associative array of remaining attributes.
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addHeadLink($href, $relation, $relType = 'rel', $attribs = array())
	{
		$this->_links[$href]['relation'] = $relation;
		$this->_links[$href]['relType'] = $relType;
		$this->_links[$href]['attribs'] = $attribs;

		return $this;
	}

	/**
	 * Adds a shortcut icon (favicon)
	 *
	 * This adds a link to the icon shown in the favorites list or on
	 * the left of the url in the address bar. Some browsers display
	 * it on the tab, as well.
	 *
	 * @param   string  $href      The link that is being related.
	 * @param   string  $type      File type
	 * @param   string  $relation  Relation of link
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addFavicon($href, $type = 'image/vnd.microsoft.icon', $relation = 'shortcut icon')
	{
		$href = str_replace('\\', '/', $href);
		$this->addHeadLink($href, $relation, 'rel', array('type' => $type));

		return $this;
	}

	/**
	 * Adds a custom HTML string to the head block
	 *
	 * @param   string  $html  The HTML to add to the head
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addCustomTag($html)
	{
		$this->_custom[] = trim($html);

		return $this;
	}

	/**
	 * Get the contents of a document include
	 *
	 * @param   string  $type     The type of renderer
	 * @param   string  $name     The name of the element to render
	 * @param   array   $attribs  Associative array of remaining attributes.
	 *
	 * @return  The output of the renderer
	 *
	 * @since   11.1
	 */
	public function getBuffer($type = null, $name = null, $attribs = array())
	{
		// If no type is specified, return the whole buffer
		if ($type === null)
		{

			return parent::$_buffer['component'][""];
		}
		
		$result = null;
		if (isset(parent::$_buffer[$type][$name]))
		{
			return parent::$_buffer[$type][$name];
		}

		// If the buffer has been explicitly turned off don't display or attempt to render
		if ($result === false)
		{
			return null;
		}
	    $type = 'OPC'; 
		$renderer = $this->loadRenderer($type);
		if ($this->_caching == true && $type == 'modules')
		{
			$cache = JFactory::getCache('com_modules', '');
			$hash = md5(serialize(array($name, $attribs, $result, $renderer)));
			$cbuffer = $cache->get('cbuffer_' . $type);

			if (isset($cbuffer[$hash]))
			{
				return JCache::getWorkarounds($cbuffer[$hash], array('mergehead' => 1));
			}
			else
			{

				$options = array();
				$options['nopathway'] = 1;
				$options['nomodules'] = 1;
				$options['modulemode'] = 1;

				$this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);
				$data = parent::$_buffer[$type][$name];

				$tmpdata = JCache::setWorkarounds($data, $options);

				$cbuffer[$hash] = $tmpdata;

				$cache->store($cbuffer, 'cbuffer_' . $type);
			}

		}
		else
		{
			$this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);
		}

		return parent::$_buffer[$type][$name];
	}

	/**
	 * Set the contents a document includes
	 *
	 * @param   string  $content  The content to be set in the buffer.
	 * @param   array   $options  Array of optional elements.
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setBuffer($content, $options = array())
	{
		// The following code is just for backward compatibility.
		if (func_num_args() > 1 && !is_array($options))
		{
			$args = func_get_args();
			$options = array();
			$options['type'] = $args[1];
			$options['name'] = (isset($args[2])) ? $args[2] : null;
		}
		
		parent::$_buffer[$options['type']][$options['name']] = $content;

		return $this;
	}

	/**
	 * Parses the template and populates the buffer
	 *
	 * @param   array  $params  Parameters for fetching the template
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function parse($params = array())
	{
		return $this->_fetchTemplate($params)->_parseTemplate();
	}

	/**
	 * Outputs the template to the browser.
	 *
	 * @param   boolean  $caching  If true, cache the output
	 * @param   array    $params   Associative array of attributes
	 *
	 * @return  The rendered data
	 *
	 * @since   11.1
	 */
	public function render($caching = false, $params = array())
	{
	    parent::render();
	    $x = $this->getBuffer();
		if (is_array($x)) return $x['component']; 
		else return $x;
	
		$this->_caching = $caching;

		if (!empty($this->_template))
		{
			$data = $this->_renderTemplate();
		}
		else
		{
			$this->parse($params);
			$data = $this->_renderTemplate();
		}

		parent::render();
		return $data;
	}

	/**
	 * Count the modules based on the given condition
	 *
	 * @param   string  $condition  The condition to use
	 *
	 * @return  integer  Number of modules found
	 *
	 * @since   11.1
	 */
	public function countModules($condition)
	{
	return 0;
		
	}

	/**
	 * Count the number of child menu items
	 *
	 * @return  integer  Number of child menu items
	 *
	 * @since   11.1
	 */
	public function countMenuChildren()
	{
	return 0;
		
	}

	/**
	 * Load a template file
	 *
	 * @param   string  $directory  The name of the template
	 * @param   string  $filename   The actual filename
	 *
	 * @return  string  The contents of the template
	 *
	 * @since   11.1
	 */
	protected function _loadTemplate($directory, $filename)
	{
		//		$component	= JApplicationHelper::getComponentName();

		$contents = '';
		return $contents;
		
	}

	/**
	 * Fetch the template, and initialise the params
	 *
	 * @param   array  $params  Parameters to determine the template
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	protected function _fetchTemplate($params = array())
	{
		return $this;
		
	}

	/**
	 * Parse a document template
	 *
	 * @return  The parsed contents of the template
	 *
	 * @return  JDocumentHTML instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	protected function _parseTemplate()
	{
	

		return $this;
	}

	/**
	 * Render pre-parsed template
	 *
	 * @return string rendered template
	 *
	 * @since   11.1
	 */
	protected function _renderTemplate()
	{
		$replace = array();
		$with = array();

		foreach ($this->_template_tags as $jdoc => $args)
		{
			$replace[] = $jdoc;
			$with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
		}

		return str_replace($replace, $with, $this->_template);
	}
}


class JDocumentRendererOPC extends JDocumentRenderer {
  public function render($component = null, $params = array(), $content = null) {
    return $content; 
  }
}