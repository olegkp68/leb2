<?php
/**
 * @package		com_visforms
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class com_spambotcheckInstallerScript {
	private $loggerName;
	private $versionInstalled;
	private $versionToInstall;
	private $versionMinimumJoomla;

	// construction

	public function __construct($adapter) {
		$this->initializeLogger($adapter);
		// return value is null if no prior package version is installed
		$this->versionInstalled     = $this->getExtensionParameter('manifest_cache', 'version', 'component', 'com_spambotcheck');
		$this->addLogEntry('component version installed = ' . ($this->versionInstalled ?: 'not installed'), Log::INFO);
		if ($adapter->getManifest()) {
			$this->versionToInstall = (string) $adapter->getManifest()->version;
			$this->addLogEntry('component version in manifest = ' . ($this->versionToInstall ?: 'component manifest version not found'), Log::INFO);
			$this->versionMinimumJoomla = (string) $adapter->getManifest()->attributes()->version;
		}
	}

	// interface

	public function preflight($route, $adapter): bool {
		if($route !== 'uninstall') {
			$jversion = new Version();
			// abort if the current Joomla release is older
			if (isset($this->versionMinimumJoomla) && version_compare($jversion->getShortVersion(), $this->versionMinimumJoomla, 'lt')) {
				$text = Text::_('COM_USER_SPAMBOTCHECK_WRONG_JOOMLA_VERSION') . $this->versionMinimumJoomla;
				$app  = Factory::getApplication();
				$app->enqueueMessage($text, 'warning');
				$this->addLogEntry($text);
				return false;
			}
		}
		return true;
	}

	public function install($adapter) {
		//create an empty dataset in table user_spambotcheck for each user
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__users'));
		$db->setQuery($query);
		$users = $db->loadColumn();
 
        foreach ($users as $user)
        {
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->clear();
			 
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__user_spambotcheck'))
				->columns(array($db->quoteName('user_id'), $db->quoteName('note')))
				->values(implode(',', array($db->quote($user), $db->quote('user was created before component installation.'))));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			try {
				$db->execute();
			}
			catch (RuntimeException $e) {
				$this->addLogEntry('unable to create user in table #__user_spambotcheck');
			}
        }
	}

	// implementation

	function getExtensionParameter($field = 'params', $name = null, $type = 'plugin', $element = 'spambotcheck') {
		// return value is null if no prior package version is installed (found)
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$query->select($db->qn($field))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($type) . ' AND ' . $db->qn('element') . ' = ' . $db->q($element));
		$db->setQuery($query);
		try {
			$params = json_decode($db->loadResult(), true);
			if (isset($name)) {
				return $params[$name];
			}
			return $params;
		}
		catch (RuntimeException $e) {
			$this->addLogEntry("unable to get element '$element' of type '$type' own '$field' parameter " . ($name ?? '') . ' from database');
		}
	}

	private function initializeLogger($adapter) {
		$this->loggerName = (string) $adapter->getManifest()->loggerName;
		$options['format']              = "{CODE}\t{MESSAGE}";
		$options['text_entry_format']   = "{PRIORITY}\t{MESSAGE}";
		$options['text_file']           = 'spambotcheck_update.php';
		try {
			Log::addLogger($options, Log::ALL, array($this->loggerName, 'jerror'));
		}
		catch (RuntimeException $e) {}
	}

	private function addLogEntry($message, $level = Log::ERROR) {
		try {
			Log::add($message, $level, $this->loggerName);
		}
		catch (RuntimeException $exception)
		{
			// prevent installation routine from failing due to problems with logger
		}
	}
}
