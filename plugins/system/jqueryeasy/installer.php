<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Script file of the jQuery Easy plugin
 */
class plgsystemjqueryeasyInstallerScript
{
	static $version = '4.2.0';
	static $available_languages = array('bg-BG', 'de-DE', 'en-GB', 'en-US', 'es-CO', 'es-ES', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'uk-UA');
	static $changelog_link = 'http://www.simplifyyourweb.com/downloads/jquery-easy/file/58-jquery-easy';
	static $translation_link = 'https://simplifyyourweb.com/translators';

	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
		// make sure we are under Joomla 3.1 or over

		if (version_compare(JVERSION, '3.1.0', 'lt') || version_compare(JVERSION, '3.11.0', 'gt')) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JOOMLA_REQUIRED_VERSION', '3.1', 'https://simplifyyourweb.com/downloads/jquery-easy#downloads'), 'error');
			return false;
		}

		return true;
	}

	/**
	 * Called after an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		echo '<p style="margin: 20px 0">';
		//echo '<img src="../plugins/system/jqueryeasy/images/logo.png" />';
		echo '<span class="label">'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERSIONNUMBER_LABEL', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

 		// language test

 		$current_language = JFactory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			JFactory::getApplication()->enqueueMessage('The ' . JFactory::getLanguage()->getName() . ' language is missing for this plugin.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
 		}

		if ($type == 'update') {

			// delete unnecessary files

			$files = array(
				'/plugins/system/jqueryeasy/fields/help.php',
				'/plugins/system/jqueryeasy/fields/warningjqueryui.php',
				'/plugins/system/jqueryeasy/fields/warningnoconflict.php',
			    '/plugins/system/jqueryeasy/fields/sywgroup.php',
				'/plugins/system/jqueryeasy/images/jqueryeasyprofiles.png',
			    '/plugins/system/jqueryeasy/images/chat.png',
			    '/plugins/system/jqueryeasy/images/visibility.png',
			    '/plugins/system/jqueryeasy/images/thumb-up.png',
			    '/plugins/system/jqueryeasy/images/wallet-membership.png',
			    '/plugins/system/jqueryeasy/images/local-library.png',
			    '/plugins/system/jqueryeasy/images/lifebuoy.png',
			    '/plugins/system/jqueryeasy/images/SimplifyYourWeb_24.png',
			    '/plugins/system/jqueryeasy/jquerynoconflict.js'
			);

			$folders = array(
				'/plugins/system/jqueryeasy/fields/help',
				'/plugins/system/jqueryeasy/fields/preview'
			);

			foreach ($files as $file) {
				if (JFile::exists(JPATH_ROOT.$file) && !JFile::delete(JPATH_ROOT.$file)) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file), 'warning');
				}
			}

			foreach ($folders as $folder) {
				if (JFolder::exists(JPATH_ROOT.$folder) && !JFolder::delete(JPATH_ROOT.$folder)) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder), 'warning');
				}
			}

			// remove the old update site

			$this->removeUpdateSite('plugin', 'jqueryeasy', 'system', 'http://www.barejoomlatemplates.com/autoupdates/jqueryeasy/jqueryeasy-update.xml');
			$this->removeUpdateSite('plugin', 'jqueryeasy', 'system', 'http://www.barejoomlatemplates.com/autoupdates/jqueryeasy/jqueryeasy-update-beta.xml');
			$this->removeUpdateSite('plugin', 'jqueryeasy', 'system', 'http://www.barejoomlatemplates.com/autoupdates/jqueryeasy/jqueryeasy-v2-update.xml');
			$this->removeUpdateSite('plugin', 'jqueryeasy', 'system', 'https://updates.simplifyyourweb.com/free/jqueryeasy/jqueryeasy-v2-update.xml');

			// update warning

			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_SYSTEM_JQUERYEASY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
		}

		return true;
	}

	/**
	 * Called on installation
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent) {}

	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent) {}

	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) {}

	private function removeUpdateSite($type, $element, $folder = '', $location = '')
	{
	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select('extension_id');
	    $query->from('#__extensions');
	    $query->where($db->quoteName('type').'='.$db->quote($type));
	    $query->where($db->quoteName('element').'='.$db->quote($element));
	    if ($folder) {
	        $query->where($db->quoteName('folder').'='.$db->quote($folder));
	    }

	    $db->setQuery($query);

	    $extension_id = '';
	    try {
	        $extension_id = $db->loadResult();
	    } catch (RuntimeException $e) {
	        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return false;
	    }

	    if ($extension_id) {

	        $query->clear();

	        $query->select('update_site_id');
	        $query->from('#__update_sites_extensions');
	        $query->where($db->quoteName('extension_id').'='.$db->quote($extension_id));

	        $db->setQuery($query);

	        $updatesite_id = array(); // can have several results
	        try {
	            $updatesite_id = $db->loadColumn();
	        } catch (RuntimeException $e) {
	            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	            return false;
	        }

	        if (empty($updatesite_id)) {
	            return false;
	        } else if (count($updatesite_id) == 1) {

	            $query->clear();

	            $query->delete($db->quoteName('#__update_sites'));
	            $query->where($db->quoteName('update_site_id').' = '.$db->quote($updatesite_id[0]));

	            $db->setQuery($query);

	            try {
	                $db->execute();
	            } catch (RuntimeException $e) {
	                JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	                return false;
	            }
	        } else { // several update sites exist for the same extension therefore we need to specify which to delete

	            if ($location) {
	                $query->clear();

	                $query->delete($db->quoteName('#__update_sites'));
	                $query->where($db->quoteName('update_site_id').' IN ('.implode(',', $updatesite_id).')');
	                $query->where($db->quoteName('location').' = '.$db->quote($location));

	                $db->setQuery($query);

	                try {
	                    $db->execute();
	                } catch (RuntimeException $e) {
	                    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	                    return false;
	                }
	            } else {
	                return false;
	            }
	        }
	    } else {
	        return false;
	    }

	    return true;
	}

}
?>
