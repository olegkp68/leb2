<?php 
/** 
 * @version		$Id: opc.php$
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 

// no direct access
defined('_JEXEC') or die; 
JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

class ModLanguagesHelper
{
	public static $languages; 
	public static function getList(&$params)
	{
		return self::$languages; 
	}
}
