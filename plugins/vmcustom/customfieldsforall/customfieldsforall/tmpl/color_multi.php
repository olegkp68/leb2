<?php
/**
 * @package customfieldsforall
 * @copyright Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/*
 * We abstracted the layouts in the 'customfieldsforallbase' plugin.
 * This way they can be used by our plugins, without having to keep identical files in each of them.
 *
 * This file can still be used for template overrides, by using as a base the code loaded by the source file.
 */
$filePath = CF4ALL_BASE_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . pathinfo(__FILE__, PATHINFO_FILENAME) . '.php';

if(file_exists($filePath)) {
    require $filePath;
}
