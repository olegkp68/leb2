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

require_once __DIR__ . '/script.install.helper.php';

class Mod_AddtoMenuInstallerScript extends Mod_AddtoMenuInstallerScriptHelper
{
	public $name            = 'ADD_TO_MENU';
	public $alias           = 'addtomenu';
	public $extension_type  = 'module';
	public $module_position = 'status';
	public $client_id       = 1;
}
