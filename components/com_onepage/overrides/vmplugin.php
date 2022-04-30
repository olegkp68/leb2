<?php 
/** 
 * @version		$Id: opc.php$
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 
if (!class_exists('vmPlugin')) {
	
		{
			
			$version_suffix = '3'; 
			if ( !defined('VM_VERSION') || (VM_VERSION < 3 ) ) {
			$version_suffix = '26'; 
			if (!class_exists('VmVersion')) {
				require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'version.php'); 
			}	
			
			if (isset(VmVersion::$RELEASE)) {
				if (substr(VmVersion::$RELEASE, 0, 1) === '2') {
					$version_suffix = '26'; 
				}
				else {
					$version_suffix = '3'; 
				}
			}
			else {
				$version_suffix = '3'; 
			}
			}
			
			
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'vmplugin'.$version_suffix.'.php'); 
		}	 	
}