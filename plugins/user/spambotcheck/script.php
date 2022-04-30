<?php
/**
 * @version        $Id: script.php 22354 2011-11-07 05:01:16Z github_bot $
 * @package        com_visforms
 * @subpackage     plg_visforms_spamcheck
 * @copyright      Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class plguserspambotcheckInstallerScript {
	private $loggerName;
	private $versionInstalled;
	private $versionToInstall;
	private $versionMinimumJoomla;
    private $status;

    // construction

	public function __construct($adapter) {
		$this->initializeLogger($adapter);
		// return value is null if no prior package version is installed
		$this->versionInstalled     = $this->getExtensionParameter('manifest_cache', 'version');
		$this->addLogEntry('plugin version installed = ' . ($this->versionInstalled ?: 'not installed'), Log::INFO);
		if ($adapter->getManifest()) {
			$this->versionToInstall =(string) $adapter->getManifest()->version;
			$this->addLogEntry('plugin version in manifest = ' . ($this->versionToInstall ?: 'plugin manifest version not found'), Log::INFO);
			$this->versionMinimumJoomla = (string) $adapter->getManifest()->attributes()->version;
		}
		// holds data for user messages
		$this->status = new stdClass();
		$this->status->plugins = array();
		$this->status->modules = array();
		$this->status->components = array();
		$this->status->tables = array();
		$this->status->folders = array();
	}

    // interface

	public function preflight($route, $adapter): bool {
		if($route !== 'uninstall') {
			$jversion = new Version();
			// abort if the current Joomla release is older
			if (isset($this->versionMinimumJoomla) && version_compare($jversion->getShortVersion(), $this->versionMinimumJoomla, 'lt')) {
				$text = Text::_('PLG_USER_SPAMBOTCHECK_WRONG_JOOMLA_VERSION') . $this->versionMinimumJoomla;
				$app  = Factory::getApplication();
				$app->enqueueMessage($text, 'warning');
				$this->addLogEntry($text);
				return false;
			}
		}
        return true;
	}

	public function postflight($route, $adapter): bool {
		// run version specific update code
		if ($route == 'update') {
			if (isset($this->versionInstalled) && version_compare($this->versionInstalled, '1.3.12', 'lt'))
				$this->postFlightForVersion_1_3_12();
			if (isset($this->versionInstalled) && version_compare($this->versionInstalled, '1.3.13', 'lt'))
				$this->postFlightForVersion_1_3_13();
			$this->convertTablesToUtf8mb4();
		}

		// enable plugin

		$jversion = new Version();
		if (version_compare($jversion->getShortVersion(), '4.0.0', 'lt')) {
			// enable plugin if joomla major version is 3
			$enabled = '1';
		}
		else {
			// disable plugin if joomla major version is 4
			$enabled = '0';
		}
		$this->addLogEntry("plugin enabled set to: $enabled", Log::NOTICE);

		$db = Factory::getDbo();
        $conditions = array(
            $db->qn('type') . ' = ' . $db->q('plugin'),
            $db->qn('element') . ' = ' . $db->quote('spambotcheck'),
	        $db->qn('folder') . ' = ' . $db->quote('user')
        );
        $fields = array($db->qn('enabled') . " = $enabled ");

        $query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
        try {
	        $db->execute();
        }
        catch (RuntimeException $e) {
	        $text = Text::_('PLG_USER_SPAMBOTCHECK_NOT_ENABLED');
	        $app  = Factory::getApplication();
	        $app->enqueueMessage($text, 'warning');
	        $this->addLogEntry($text, Log::WARNING);
        }
		return true;
	}

	public function install($adapter): bool {
		// give a warning if cURL is not enabled on system; plugin will not be able to identify spammer
		$extension = 'curl';
		if (!extension_loaded($extension)) {
			$text = Text::_('PLG_USER_SPAMBOTCHECK_CURL_MISSING');
			$app  = Factory::getApplication();
			$app->enqueueMessage($text, 'warning');
			$this->addLogEntry($text, Log::WARNING);
		}
		return true;
	}

	public function uninstall($adapter): bool {
		$db = Factory::getDBO();
		if ($db) {
			$tablesAll = $db->getPrefix() . '_spambot_attempts';
			$tablesAllowed = $db->getTableList();
			if (!in_array($tablesAll, $tablesAllowed)) {
				$db->setQuery("drop table if exists #__spambot_attempts");
				try {
					$db->execute();
				}
				catch (Exception $e) {
					$this->addLogEntry('could not delete database table #__spambot_attempts', Log::WARNING);
					return false;
				}
			}
		}
		return true;
	}

	// implementation

	private function postFlightForVersion_1_3_12() {
		$this->addLogEntry('plugin performing postflight for version 1.3.12', Log::INFO);
		// inspect value of parameter spbot_monitor_events: value format changed from 0/1 to R/RL
		$name = 'spbot_monitor_events';
		$params = $this->getExtensionParameter();
		if (array_key_exists($name, $params)) {
			if ($params[$name] === '0')
				$params[$name] = 'R';
			if ($params[$name] === '1')
				$params[$name] = 'RL';
		}
		// add new parameters
		$params['spbot_blacklist_email'] = '';
		$params['spbot_bl_log_to_db'] = '0';
		$params['spbot_suspicious_time'] = '12';
		$params['spbot_allowed_hits'] = '3';
		$this->setExtensionParameters($params);
	}

	private function postFlightForVersion_1_3_13() {
		$this->addLogEntry('plugin performing postflight for version 1.3.13', Log::INFO);
		// get all extension parameters
		$params = $this->getExtensionParameter();
		// remove spambusted.com related parameter
		$name = 'spbot_spambusted';
		if (array_key_exists($name, $params)) {
			unset($params[$name]);
		}
		// remove deprecated spacer parameter (used to structure the parameter ui layout)
		$name = '@spacer';
		if (array_key_exists($name, $params)) {
			unset($params[$name]);
		}
		// add new parameters
		$name = 'spbot_projecthoneypot_api_key';
		$param = $this->getExtensionParameter('params', $name);
		$value = is_string($param) && $param != '' ? '1' : '0';
		$params['spbot_projecthoneypot'] = $value;
		// set all parameters
		$this->setExtensionParameters($params);
	}

	/*
	 * gets all parameters of extension from extension table
	 */
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

	/*
	 * sets all parameters as extension parameters to extension table
	 */
	private function setExtensionParameters($params, $field = 'params') {
		// write the existing component value(s)
		$db = Factory::getDbo();
		$paramsString = json_encode($params);
		$conditions = array(
			$db->qn('type') . ' = ' . $db->q('plugin'),
			$db->qn('element') . ' = ' . $db->quote('spambotcheck'),
			$db->qn('folder') . ' = ' . $db->quote('user')
		);
		$fields = array($db->qn($field) . ' = ' . $db->q($paramsString));

		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		//$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramsString) . ' WHERE name = "User - SpambotCheck"');
		try {
			$db->execute();
		}
		catch (Exception $e) {
			$this->addLogEntry('unable to set User - SpambotCheck parameter in database');
		}
	}

	private function convertTablesToUtf8mb4() {
		// Joomla! will use character set utf8 as default, if utf8mb4 is not supported
		// if we have successfully converted to utf8md4, we set a flag in the database
		$db = Factory::getDbo();
		$serverType = $db->getServerType();
		if ($serverType != 'mysql') {
			return;
		}
        $pluginParams = $this->getExtensionParameter('custom_data');
		if (empty($pluginParams)) {
		    $pluginParams = array();
        }
		if (!is_array($pluginParams)) {
		    return;
        }
		$convertedDB = isset($pluginParams['utf8_conversion']) ? isset($pluginParams['utf8_conversion']) : 0;

		if ($db->hasUTF8mb4Support()) {
			$converted = 2;
		}
		else {
			$converted = 1;
		}

		if ($convertedDB == $converted) {
			return;
		}
		$tablelist = array('#__spambot_attempts', '#__user_spambotcheck');
		foreach ($tablelist as $table) {
		    $query = 'ALTER TABLE ' . $table . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		    $db->setQuery($query);
		    try {
		        $db->execute();
            }
            catch (RuntimeException $e) {
	            $converted = 0;
            }
		}
		$pluginParams['utf8_conversion'] = $converted;
		$this->setExtensionParameters($pluginParams, 'custom_data');
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