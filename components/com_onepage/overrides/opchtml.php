<?php
/**
 * Overrided portion of JDocumentRaw class for OPC2 on Virtuemart 2
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
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'opchtmlclass.php'); 
jimport('joomla.application.module.helper');
jimport('joomla.utilities.utility');

/**
 * DocumentHTML class, provides an easy interface to parse and display a HTML document
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentOpchtml extends JDocumentOpchtmlclass
{
	/**
	 * Array of Header <link> tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_links = array();

	/**
	 * Array of custom tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_custom = array();

	/**
	 * Name of the template
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $template = null;

	/**
	 * Base url
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $baseurl = null;

	/**
	 * Array of template parameters
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $params = null;

	/**
	 * File name
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_file = null;

	/**
	 * String holding parsed template
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_template = '';

	/**
	 * Array of parsed template JDoc tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_template_tags = array();

	/**
	 * Integer with caching setting
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $_caching = null;


}
