<?php
/**
 * @package customfieldsforall
 * @copyright Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Do not change the order of loading. VmConfig calls the plugins
 */
if(!defined('CF4ALL_BASE_PLUGIN_PATH')) {
    define('CF4ALL_BASE_PLUGIN_PATH', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'customfieldsforallbase');
}

require_once(CF4ALL_BASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'customfieldsforallbase.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Update.php');


