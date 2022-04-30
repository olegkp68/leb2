<?php
/* ======================================================
# Login as User for Joomla! - v3.3.2
# -------------------------------------------------------
# For Joomla! CMS
# Author: Web357 (Yiannis Christodoulou)
# Copyright (©) 2009-2019 Web357. All rights reserved.
# License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
# Website: https:/www.web357.com/
# Demo: https://demo.web357.com/?item=loginasuser
# Support: support@web357.com
# Last modified: 21 Mar 2019, 01:46:37
========================================================= */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgInstallerLoginasuserInstallerScript extends PlgInstallerLoginasuserInstallerScriptHelper
{
	public $name           	= 'Login as User';
	public $alias          	= 'loginasuser';
	public $extension_type 	= 'plugin';
	public $plugin_folder   = 'installer';
}