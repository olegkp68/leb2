<?php 
/**
 * 
 *
 * @package One Page Checkout for VirtueMart
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2018 RuposTel - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * One Page checkout is free software released under GNU/GPL and uses some code from VirtueMart
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 */
defined('_JEXEC') or die;
class OPCCron {
	public static $queue; 
	private static function _getOPCMini() {
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'dbcache.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'clihelper.php'); 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'opc.php');
		
		$db = JFactory::getDBO(); 
		$q = 'select `enabled` from #__extensions where `element` = "opccron" order by `enabled` desc limit 1'; 
		$db->setQuery($q); 
		$x = $db->loadResult(); 
		if (empty($x)) {
			cliHelper::debug('ERROR: plg_system_opccron is not enabled and thus no new jobs can be added'); 
		}
	}
	
	public static function tableExists()
  {
    
	
	self::_getOPCMini(); 
	$ret = OPCmini::tableExists('onepage_cron'); 
	if (!$ret) {
		self::createTable(); 
		return true; 
		
	}
	return $ret; 
  }
	private static function createTable() {
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callable` varchar(512) CHARACTER SET ascii NOT NULL,
  `params` longtext NOT NULL,
  `require_once` text NOT NULL,
  `token` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `job_status` varchar(1) NOT NULL,
  `result` text NOT NULL,
  `cron_repeat` varchar(128) NOT NULL DEFAULT \'\',
  `repeated` BIGINT NOT NULL DEFAULT \'0\',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_on` datetime DEFAULT NULL,
  `finished_on` datetime DEFAULT NULL,
  `notify_email` varchar(512) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`id`),
  UNIQUE(`token`),
  KEY `waiting` (`job_status`,`started_on`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
$db = JFactory::getDBO(); 
$db->setQuery($q); 
$db->execute(); 


//`repeated` BIGINT NOT NULL DEFAULT '0' AFTER `cron_repeat`;
	 OPCmini::clearTableExistsCache(); 
	}
	
	public static function addJob($callable, $params=array(), $require=array(), $repeat='', $notify_email='') {
		self::_getOPCMini(); 
		//id	callable	params	require_once	token	job_status	
		//result	cron_repeat	created_on	started_on	finished_on	notify_email
		$db = JFactory::getDBO(); 
		
		$require = (array)$require; 
		
		
		
		if (!empty($require)) {
		foreach ($require as $ind=>$path) {
			if (stripos($path, JPATH_SITE) !== 0) {
				if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.$path)) {
					return false; 
				}
				else {
					$require[$ind] = $path; 
				}
			}
			else {
				$require[$ind] = substr($path, strlen(JPATH_SITE.DIRECTORY_SEPARATOR)); 
			}
		}
		}
		
		
		$insert = array(); 
		$insert['id'] = 'NULL'; 
		$insert['callable'] = json_encode($callable); 
		$insert['params'] = json_encode($params); 
		$insert['require_once'] = json_encode($require); 
		$insert['cron_repeat'] = $repeat; 
		
		$insert['job_status'] = 'P'; 
		$insert['result'] = '';
		$insert['created_on'] = 'NOW()';
		$insert['started_on'] = 'NULL'; 
		$insert['finished_on'] = 'NULL'; 
		$insert['notify_email'] = $notify_email; 
		
		
		$insert['token'] = self::getToken($insert); 
		$token = $insert['token']; 
		
		self::tableExists(); 
		$q = 'select `job_status` from #__onepage_cron where `token` = \''.$db->escape($token).'\''; 
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		
		
		
		
		if ($res === 'R') {
			
			return; 
		}
		if ((!empty($res)) && ($res !== 'P')) {
			$q = 'update #__onepage_cron set `job_status` = "P", `started_on` = NULL, `finished_on` = NULL where `token` = \''.$db->escape($token).'\''; 
			$db->setQuery($q); 
			$db->execute(); 
			return; 
		}
		
		
		
		OPCmini::insertArray('#__onepage_cron', $insert); 
		
		
		
		
	}
	public static function getToken($insert) {
		$hash = var_export($insert['callable'], true).'_'.var_export($insert['params'], true).'_'.var_export($insert['require_once'], true);
		$token = JApplication::getHash('opccron_'.$hash);
		return $token; 
	}
	
	public static function checkToken($job) {
		$token = self::getToken($job); 
		if (($token === $job['token']) && (!empty($job['token']))) return true; 
		
		
		cliHelper::debug('ERROR: signature of the job is not valid, job will not be executed'); 
		return false; 
		
	}
	
	
	/*
	P = pending, in queue
	E = first error 
	X = fatal error
	R = running 
	F = finished
	*/
	public static function setStatus($job, $status, $result='') {
		
		
		
		self::tableExists(); 
		$db = JFactory::getDBO(); 
		$q = 'update #__onepage_cron set `job_status` = \''.$db->escape($status).'\' '; 
		
		
		
		if (!empty($result)) {
		 $q .= ', `result` = \''.$db->escape($result).'\' '; 
		}
		if ($status === 'R') {
			$q .= ', `started_on` = NOW() '; 
		}
		elseif ($status === 'F') {
			$q .= ', `finished_on` = NOW() '; 
		}
		else {
			$q .= ', `started_on` = NULL '; 
		}
		
		$q .= ', `repeated` = `repeated` + 1 '; 
		
		if (function_exists('getmypid')) {
			$pid = getmypid(); 
			$q .= ', `pid` = '.(int)$pid; 
		}
		else {
			$q .= ', `pid` = 0 '; 
			$pid = 0; 
		}
		
		$job['pid'] = (int)$pid; 
		
		
		$q .= ' where `id` = '.(int)$job['id']; 
		
		
		$db->setQuery($q); 
		$db->execute(); 
				
		return false; 
	}
	
	public static function getJob($status='P') {
		self::tableExists(); 
		$db = JFactory::getDBO(); 
		$q = 'select * from `#__onepage_cron` where `job_status` = \''.$db->escape($status).'\' ';
		$errors = array('E', 'X'); 
		if (in_array($status, $errors)) {
			$q .= ' and repeated <= 5 '; 
		}
		
		if ($status === 'R') {
			$q .= ' and `created_on` < DATE_SUB(NOW(), INTERVAL 2 HOUR) '; //re-do R orders only if older then 2 hours
		}
		
		if (($status !== 'P') && (($status !== 'R'))) {
		 $q .= ' and (ISNULL(`started_on`) or `started_on` = "0000-00-00 00:00:00") '; 
		}
		$q .= ' order by created_on asc limit 1'; 
		$db->setQuery($q); 
		$job = $db->loadAssoc(); 
		
		
		
		if (empty($job)) return true; //no more jobs... 
		
		$job = (array)$job;
		
		if (!self::checkToken($job)) {
			return self::setStatus($job, 'E', 'Invalid security token'); 
		}
		
		if (!isset($job['repeated'])) {
			$q = 'alter table #__onepage_cron add column `repeated` BIGINT NOT NULL DEFAULT \'0\' AFTER `cron_repeat`'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
		if (!isset($job['pid'])) {
			$q = 'alter table #__onepage_cron add column `pid` BIGINT NOT NULL DEFAULT \'0\' AFTER `cron_repeat`'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
		$job['repeated'] = (int)$job['repeated']; 
		
	
		$job['require_once'] = json_decode($job['require_once']); 
		$job['params'] = json_decode($job['params'], true); 
		$job['callable'] = json_decode($job['callable'], true); 
		return $job; 
		
	}
	public function __destruct() {
		$e = error_get_last(); 
		if (!empty(self::$queue)) {
			foreach (self::$queue as $job) {
				 if (!empty($e)) {
					$msg = json_encode($e); 
				 }
				 else {
					 $msg = 'Unknown error'; 
				 }
				 self::setStatus($job, 'E', $msg);
			}
		}
	}
	public static function run($previous=false) {
		
		if (empty(self::$queue)) self::$queue = array(); 
		
		//initializes destructor:
		$OPCCron = new OPCCron; 
		
		static $recursion; 
		if (empty($recursion)) $recursion = 0; 
		$recursion++; 
		
		if ($recursion > 5) {
			cliHelper::debug('Max jobs reached, others will be executed next time'); 
			return false; 
		}
		
		$job = self::getJob(); 
		
		
		
		if ($job === false) return self::run(); 
		if ($job === true) 
		{
			//no pending jobs, lets see jobs with errors: 
			$job = self::getJob('E'); 
			if ($job === true) {
				$job = self::getJob('X'); 
				if ($job === true) {
				   $job = self::getJob('R'); 
					if ($job === true) {
						cliHelper::debug('No jobs found'); 
						return true; 
					}
				}
			}
		}
			
		if (!empty($job['require_once'])) {
		foreach ($job['require_once'] as $fn) {
			if (stripos($fn, '..')!==false) return self::run(self::setStatus($job, 'E', 'Invalid require path')); 
			if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.$fn)) {
				return self::run(self::setStatus($job, 'X', 'Missing or invalid require path - '.JPATH_SITE.DIRECTORY_SEPARATOR.$fn)); 
			}
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.$fn); 
		}
		}
		/*
		if (is_array($job['callable'])) {
		 $static_f = implode('::', $job['callable']); 
		 $ret = call_user_func_array($static_f, $job['params']); 
		}
		else 
		*/
		{
		 self::setStatus($job, 'R');
		 $token = self::getToken($job); 
		 self::$queue[$token] = $job; 
		 
		 try {
		   cliHelper::debug('To execute: '.implode('::', $job['callable']).'('.var_export($job['params'], true).')'); 
		   $ret = call_user_func_array($job['callable'], $job['params']); 
		   if ($ret === false) {
			   if (is_array($job['callable'])) {
				$static_f = implode('::', $job['callable']); 
			   }
			   else {
				   $static_f = (string)$job['callable']; 
			   }
			   throw new Exception($static_f.' returned FALSE'); 
		   }
		   cliHelper::debug('Finished'); 
		   
		 }
		 catch (Exception $e) {
			 cliHelper::debug('ERROR EXECUTING: '.implode('::', $job['callable']).'('.var_export($job['params'], true).') - '.(string)$e); 
			 $msg = (string)$e; 
			 self::setStatus($job, 'E', $msg);
			 unset(self::$queue[$token]); 
			 $ret = false; 
		 }
		 
		 if ($ret === false) {
			 cliHelper::debug('ERROR EXECUTING: '.implode('::', $job['callable']).'('.var_export($job['params'], true).')'); 
			 self::setStatus($job, 'E', 'Queued function returned false');
			 unset(self::$queue[$token]); 
		 }
		 else {
			 if (is_string($ret)) {
			   $result = $ret; 
			 }
			 else {
				 $result = var_export($ret, true); 
			 }
			 cliHelper::debug('Executed OK'); 
			 self::setStatus($job, 'F', $result);
			 unset(self::$queue[$token]); 
		 }
		}
		
		$job = self::getJob(); 
		if (is_array($job)) {
			cliHelper::debug('Found another job to do...'); 
			return self::run(); 
		}
		
	}
	
	
}