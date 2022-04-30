<?php
/**
 * @version		opccron.php 
 * @copyright	Copyright (C) 2005 - 2018 RuposTel.com
 * @license		COMMERCIAL !
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemOpccron extends JPlugin
{
    function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	}
	private static function _canRun() {
	
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php')) {
			throw new Exception('com_onepage must be installed for opcCron system plugin to be used'); 
		}
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php');
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cron.php'); 
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'clihelper.php'); 
		return true; 
	}
	public function plgRunJobsInCron($options=array()) {
	
		
	   self::runJobs($options); 	
	}
	public static function runJobs($options=array()) {
	    if (php_sapi_name() !== 'cli') {
			return false; 
		}
		
		if (!self::_canRun()) return; 
		
		
		
		cliHelper::debug('plgSystemOpccron::runJobs running... '); 
		return OPCcron::run(); 
	}
	
	public function plgAddJobInCron($callable, $params=array(), $require=array(), $repeat='', $notify_email='') {
		if (!self::_canRun()) return false; 
		OPCcron::addJob($callable, $params, $require, $repeat, $notify_email);
		
		return true; 
	}
		
}
