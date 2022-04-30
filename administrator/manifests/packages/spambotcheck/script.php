<?php
/**
 * Component for Spambotcheck plugin
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         https://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2021 vi-solutions
 * @since        Joomla 4.0
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class pkg_spambotcheckInstallerScript {
	private $loggerName;
	private $versionInstalled;
	private $versionToInstall;
	private $versionMinimumJoomla;

	// construction

	public function __construct($adapter) {
		$this->initializeLogger($adapter);
		$this->addLogEntry('*** starting package script ***', Log::INFO);
		// return value is null if no prior package version is installed
		$this->versionInstalled     = $this->getExtensionParameter('manifest_cache', 'version', 'package', 'pkg_spambotcheck');
		$this->addLogEntry('package version installed = ' . ($this->versionInstalled ?: 'not installed'), Log::INFO);
		if ($adapter->getManifest()) {
			$this->versionToInstall = (string) $adapter->getManifest()->version;
			$this->addLogEntry('package version in manifest = ' . ($this->versionToInstall ?: 'package manifest version not found'), Log::INFO);
			$this->versionMinimumJoomla = (string) $adapter->getManifest()->attributes()->version;
		}
	}

	// interface

	public function preflight($route, $adapter): bool {
		if($route !== 'uninstall') {
			$jversion = new Version();
			// abort if the current Joomla release is older
			if (isset($this->versionMinimumJoomla) && version_compare($jversion->getShortVersion(), $this->versionMinimumJoomla, 'lt')) {
				$text = Text::_('PKG_USER_SPAMBOTCHECK_WRONG_JOOMLA_VERSION') . $this->versionMinimumJoomla;
				$app  = Factory::getApplication();
				$app->enqueueMessage($text, 'warning');
				$this->addLogEntry($text);
				return false;
			}
		}
		return true;
	}

	public function postflight($route, $adapter): bool {
		// delete update site entries from plugin
		if ($route == 'install' || $route == 'update') {
			$manifest = $adapter->getParent()->manifest;
			$packages = $manifest->xpath('files/file');
			if (!empty($packages)) {
				$this->deleteUpdateSites($packages);
			}
		}
		return true;
	}

	public function install($adapter): bool {
		return true;
	}

	public function uninstall($adapter): bool {
		return true;
	}

	public function update($adapter): bool {
		return true;
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

	private function deleteUpdateSites($packages) {
		$db = Factory::getDbo();
		// remove upload site information for all extensions from database
		foreach ($packages as $package) {
			$type = (string) $package->attributes()->type;
			$name = (string) $package->attributes()->id;
			$group = (!empty($package->attributes()->group)) ? (string) $package->attributes()->group : '';
			$id = $this->getExtensionId($type, $name, $group, 0);
			if (!empty($id)) {
				$update_site_ids = $this->getUpdateSites($id);
				if (!empty($update_site_ids)) {
					$update_sites_ids_a = implode(',', $update_site_ids);
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__update_sites'));
					$query->where($db->quoteName('update_site_id') . ' IN (' . $update_sites_ids_a . ')');
					try {
						$db->setQuery($query);
						$db->execute();
					}
					catch (RuntimeException $e) {
						$this->addLogEntry("Problems deleting record sets in #__update_sites : " . $e->getMessage(), Log::INFO);
					}
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__update_sites_extensions'));
					$query->where($db->quoteName('extension_id') . ' = ' . $id);
					try {
						$db->setQuery($query);
						$db->execute();
					}
					catch (RuntimeException $e) {
						$this->addLogEntry("Problems deleting record sets in #__update_sites_extensions : " . $e->getMessage(), Log::INFO);
					}
				}
			}
		}
	}

	private function getExtensionId($type, $name, $group = '', $client_id = 0) {
		$db = Factory::getDbo();
		$where = $db->quoteName('type') . ' = ' . $db->quote($type) . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name);
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($where);
		try {
			$db->setQuery($query);
			$id = $db->loadResult();
		}
		catch (RuntimeException $e) {
			$this->addLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), Log::INFO);
			return false;
		}
		return $id;
	}

	private function getUpdateSites($extension) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('update_site_id'))
			->from($db->quoteName('#__update_sites_extensions'))
			->where($db->quoteName('extension_id') . ' = ' . $extension);
		try {
			$db->setQuery($query);
			$update_site_ids = $db->loadColumn();
		}
		catch (RuntimeException $e) {
			$this->addLogEntry('Unable to get update sites id: ' . $extension . ', ' . $e->getMessage(), Log::INFO);
			return false;
		}
		return $update_site_ids;
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