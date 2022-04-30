<?php
CONST CF4ALLBASE_PLUGIN_PATH = __DIR__;
if (! class_exists('\vmConfig')) {
    require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
    \vmConfig::loadConfig($force = false, $fresh = false, $lang = true, $exeTrig = false);
}
if (! class_exists('\vmCustomPlugin')) {
    require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'vmcustomplugin.php');
}
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'VmCompatibilityCF.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Customfield.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Filter.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Language'.DIRECTORY_SEPARATOR.'LanguageHandlerFactory.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'LanguageHandler.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Language'.DIRECTORY_SEPARATOR.'Table.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'UpdaterBase.php');
require_once(CF4ALLBASE_PLUGIN_PATH.DIRECTORY_SEPARATOR.'Block'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'ProductRow.php');
