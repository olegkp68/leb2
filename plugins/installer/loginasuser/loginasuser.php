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

class plgInstallerLoginasuser extends JPlugin
{
    // Web357 Framework Helper Class
	public static function W357FrameworkHelperClass()
	{
		// Call the Web357 Framework Helper Class
		require_once(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'web357framework'.DIRECTORY_SEPARATOR.'web357framework.class.php');
		$w357frmwrk = new Web357FrameworkHelperClass;
		return $w357frmwrk;
	}
    
    public function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        if (parse_url($url, PHP_URL_HOST) == 'downloads.web357.com') {

            if ($apikey = $this->W357FrameworkHelperClass()->apikey) {

                $current_url = JURI::getInstance()->toString();
                $parse = parse_url($current_url);
                $domain = isset($parse['host']) ? $parse['host'] : 'domain.com';
        		$uri = JUri::getInstance($url);
                $uri->setVar('liveupdate', 'true');
                $uri->setVar('domain', $domain);
                $uri->setVar('dlid', $apikey);
                $url = $uri->toString();

            } else {

                // load default and current language
                $jlang = JFactory::getLanguage();
                $jlang->load('plg_system_web357framework', JPATH_ADMINISTRATOR, 'en-GB', true);
                $jlang->load('plg_system_web357framework', JPATH_ADMINISTRATOR, null, true);

                // warn about missing api key
                JFactory::getApplication()->enqueueMessage(JText::_('W357FRM_APIKEY_WARNING'), 'notice');
            }

        }

        return true;
    }

}