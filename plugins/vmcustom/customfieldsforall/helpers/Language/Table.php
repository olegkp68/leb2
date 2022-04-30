<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2018 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class TableCustomvalueLanguage extends JTable   {
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct($db, $langCode)
	{
		parent::__construct('#__virtuemart_custom_plg_customsforall_values_'.$langCode, 'id', $db);

	}
}
