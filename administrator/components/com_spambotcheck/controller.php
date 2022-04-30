<?php
/**
 * Default controller for Spambotcheck
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
 
// no direct access
defined('_JEXEC') or die;

/**
 * Default controller class for Spambotcheck
 *
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 *
 * @since        Joomla 1.6 
 */

class SpambotcheckController extends JControllerLegacy
{
	protected $default_view = 'users';	

	public function display($cachable = false, $urlparams = false) {
		//Load the submenu
		SpambotcheckHelper::addSubmenu(\JFactory::getApplication()->input->get->get('view', 'users', 'cmd'));
		parent::display($cachable, $urlparams);
		return $this;
	}
}
