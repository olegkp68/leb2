<?php
/**
 * Logs table class for Spambotcheck
 *
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
 * Logs Table class
 *
 * @package    com_Spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @subpackage Components
 */
class TableLogs extends JTable
{
	public function __construct(&$db) {
		parent::__construct('#__spambot_attempts', 'id', $db);
	}
}
?>
