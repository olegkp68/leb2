<?php
/**
* @version		fatal_catcher.php 
* @copyright	Copyright (C) 2005 - 2013 RuposTel.com
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemFatal_catcher extends JPlugin {
	public static $email; 
	public static $errortype; 
	public static $writelog; 
	public static $tracelog; 
	public static $track404;
	public static $referer; 
	public static function registerHandlers() {
		
		if (function_exists('register_shutdown_function'))
		{
			
			
			register_shutdown_function( "fatal_error_catcher" );
		}

		if (function_exists('set_error_handler')) {
			//reset:
			set_error_handler(null); 
			set_error_handler('fatal_catcher_exceptions_error_handler'); 
		}

		if (function_exists('set_exception_handler')) {
			set_exception_handler('fatal_catcher_exceptions_error_handler2');
		}
	}

	public static function test() {
		error_log('test fatal catcher'); 
	}
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		plgSystemFatal_catcher::$referer = ''; 
		if (isset($this->params))
		{
			$email = $this->params->get('email'); 
			plgSystemFatal_catcher::$email = $email; 
			
			$errortype = $this->params->get('errortype', 0); 
			plgSystemFatal_catcher::$errortype = (int)$errortype; 
			$writelog = $this->params->get('writelog', 0); 
			plgSystemFatal_catcher::$writelog = (int)$writelog; 
			
			plgSystemFatal_catcher::$track404 = $this->params->get('track404', false); 
			if (!empty($_SERVER["HTTP_REFERER"])) {
				plgSystemFatal_catcher::$referer = $_SERVER["HTTP_REFERER"];
			}
			else {
				plgSystemFatal_catcher::$referer = ''; 
			}
		}
		if (method_exists('JError', 'setErrorHandling')) {
			JError::setErrorHandling(E_ERROR, 'callback', array('plgSystemFatal_catcher', 'handleError')); 
		}
		
		plgSystemFatal_catcher::registerHandlers(); 
		
	}
	
	public static function handleError($e) {
		fatal_catcher_exceptions_error_handler2($e); 
	}
	
	public static function onAfterDispatch() {
		plgSystemFatal_catcher::registerHandlers(); 
	}
	public static function onAfterInitialise() {
		plgSystemFatal_catcher::registerHandlers(); 
	}
	public static function onAfterRoute() {
		plgSystemFatal_catcher::registerHandlers(); 
	}
	
	//http://php.net/manual/en/function.set-error-handler.php
	public static function mapErrorCode($code) {
		$error = $log = null;
		switch ($code) {
		case E_PARSE:
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			$log = LOG_ERR;
			break;
		case E_WARNING:
		case E_USER_WARNING:
		case E_COMPILE_WARNING:
		case E_RECOVERABLE_ERROR:
			$error = 'Warning';
			$log = LOG_WARNING;
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			$log = LOG_NOTICE;
			break;
		case E_STRICT:
			$error = 'Strict';
			$log = LOG_NOTICE;
			break;
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			$error = 'Deprecated';
			$log = LOG_NOTICE;
			break;
			default :
			$error = $code; 
			break;
		}
		//return array('error'=>$error, 'log'=>$log);
		return $error;
	}
	
	
	public function onAfterRouteTestError() {
		$db = JFactory::getDBO(); 
		$q = 'select testerror from testerror where 1=1'; 
		$db->setQuery($q); 
		$db->loadResult(); 
	}

}


// the definition of these functions is outside the plugin code, so it can load immidiately after joomla initiliazation


function fatal_error_catcher()
{
	
	//if (function_exists('fastcgi_finish_request')) fastcgi_finish_request(); 
	
	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$error = error_get_last();



	if( $error !== NULL) {
		$errno   = $error["type"];
		if (empty(plgSystemFatal_catcher::$errortype)) { 
			$types = array(E_ERROR,  E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR); 
			
			if (($errno >= 1000) && ($errno <= 5000)) {
				$errno = E_ERROR;
			}
			
			if (!in_array($errno, $types)) return;
		} 
		
		
		
		
		

		
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$date = JFactory::getDate(); 
		fatal_catcher_exceptions_error_handler($errno, $errstr, $errfile, $errline); 
		
		
	}
	return false; 
}

function fatal_catcher_exceptions_error_handler2($ex) {
	
	$msg = $ex->getMessage(); 
	
	$code = 0; 
	$file = ''; 
	$line = 'unknown'; 
	$trace = ''; 
	if (method_exists($ex, 'getCode'))
	$code = $ex->getCode(); 
	
	if ($code === 404) {
		if (empty(plgSystemFatal_catcher::$track404)) {
			if (method_exists('JErrorPage', 'render')) {
				JErrorPage::render($ex); 
			}
			return true; 
		}
	}
	
	if (method_exists($ex, 'getFile'))
	$file = $ex->getFile(); 
	if (method_exists($ex, 'getLine'))
	$line = $ex->getLine(); 
	
	$trace .= $file.':'.$line."\n"; 
	
	if (method_exists($ex, 'getTraceAsString'))
	$trace .= $ex->getTraceAsString(); 
	
	$cl = get_class($ex); 
	
	
	
	if (($cl === 'JDatabaseExceptionExecuting') || (($code > 1000) && ($code <= 5000))) {
		
		$code = E_ERROR; 
		if (class_exists('ReflectionObject')) {
			$refObject   = new ReflectionObject( $ex );
			$prop = $refObject->getProperty( 'query' );
			$q = ''; 
			if (!empty($prop) && (method_exists($prop, 'setAccessible'))) {
				$prop->setAccessible( true );
				$q = $prop->getValue($ex); 
			}
			
			$msg .= "\n\n".'Query: '."\n".$q."\n\n"; 
		}
		
		
	}
	
	
	if (($code === 500) || (stripos($cl, 'Exception'))) {
		
		$code = E_ERROR; 
		
		
		
	}
	
	if ((empty($code)) || ($code == E_WARNING)) {
		
		$code = E_ERROR; 
	}
	
	if ($code > 1000) {
		$code = E_ERROR; 
	}
	
	
	
	
	
	if (empty($trace)) {
		plgSystemFatal_catcher::$tracelog = ''; 
		$x = debug_backtrace(); 
		foreach ($x as $l) $trace .= @$l['file'].' '.@$l['line']."\n";
	}
	
	
	
	plgSystemFatal_catcher::$tracelog = $trace; 
	$errstr = $msg; //.' @ '.$file.':'.$line; 
	
	fatal_catcher_exceptions_error_handler($code, $errstr, $file, $line); 
	
	
	
	if (method_exists('JErrorPage', 'render')) {
		JErrorPage::render($ex); 
	}
	
	return TRUE; 
}

function fatal_catcher_exceptions_error_handler($errno, $errstr, $errfile, $errline) {
	
	//1064 -> joomla sql error
	
	if (empty(plgSystemFatal_catcher::$errortype)) {
		$types = array(E_ERROR,  E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR);
		//mysqlerrors:
		if (($errno >= 1000) && ($errno <= 5000)) {
			$errno = E_ERROR;
		}
		if (!in_array($errno, $types)) return FALSE;	
	}
	$severity = plgSystemFatal_catcher::mapErrorCode($errno); 
	
	$x = debug_backtrace(); 
	if (($errno === 1) && (strpos($errstr, 'VirtueMart') !== false) && (strpos($errstr, 'not found') !== false)) {
		
		if (empty(plgSystemFatal_catcher::$track404)) return; 
		
	}
	
	
	if (is_null($errno)) return FALSE; 

	$date = JFactory::getDate(); 
	$dates = $date->toISO8601();
	$dataMsg = $errno.' '.$errstr.' in file: '.$errfile.' line: '.$errline." \n\ntimestamp: ".$dates."\n";

	if (!empty(plgSystemFatal_catcher::$writelog)) {
		$f = JPATH_SITE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'php_errors.log.php'; 
		if (!file_exists($f))
		{
			// some versions of php's have a broken compiler if the start tag is inside a string
			$data = urldecode('%3C%3F').'php die(); ?>'."\n".$dataMsg; 
			jimport( 'joomla.filesystem.file' );
			//@JFile::write($f, $data);  -> we do not use joomla FTP layer here because it does not support incremental writes
			@file_put_contents($f, $data); 
		}
		else
		{
			@file_put_contents($f, $dataMsg, FILE_APPEND); 
		}
	}
	
	
	$email = plgSystemFatal_catcher::$email; 
	

	
	$config = JFactory::getConfig();	
	if (method_exists($config, 'getValue'))
	$sender = array( $config->getValue( 'config.mailfrom' ), $config->getValue( 'config.fromname' ) );
	else
	$sender = array( $config->get( 'mailfrom' ), $config->get( 'fromname' ) );
	
	if (empty($email)) 
	{
		$email = $sender[0]; 
	}
	
	if (!empty($email))
	{
		
		$mailer = JFactory::getMailer();		
		if (strpos(plgSystemFatal_catcher::$email, ',') !== false) {
			$ea = explode(',', plgSystemFatal_catcher::$email); 
			foreach ($ea as $ee) {
				$ee = trim($ee); 
				if (!empty($ee)) {
					$mailer->addRecipient( $ee );
				}
			}
			
		}
		else {
			$mailer->addRecipient( $email );
		}
		
		
		
		
		
		$subject = 'Fatal Error Detected on your Joomla Site'; 
		$mailer->setSubject(  html_entity_decode( $subject) );
		$mailer->isHTML( false );
		
		$body = "RuposTel.com plg_system_fatal_catcher plugin detected a problem with your site. \nYour site caused a blank screen upon a visit of this URL: \n\n"; 

		$pageURL = 'http';
		if ((isset($_SERVER['HTTPS'])) && ($_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
		$pageURL .= "://";
		if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER['SERVER_PORT'] != '443')) {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}		
		$body .= $pageURL."\n\n"; 
		if (!empty(plgSystemFatal_catcher::$referer)) {
			$body .= 'HTTP_REFERER: '.plgSystemFatal_catcher::$referer."\n\n"; 
		}
		$body .= 'Error message data: '."\n"; 
		$body .= $dataMsg; 
		$body .= "\n\nTo disable these emails proceed to your Extensions -> Plug-in manager -> disable plg_system_fatal_catcher \n";
		$body .= "It is very important that you fix all php fatal errors on your site. Resend this email to your developer."; 
		
		if (strpos($dataMsg, 'memory')===false)
		{
			if (!empty(plgSystemFatal_catcher::$tracelog)) {
				$body .= "\n\nBacktrace: \n"; 
				$body .= plgSystemFatal_catcher::$tracelog;
				//$x = debug_backtrace(); 
				//foreach ($x as $l) $body .= @$l['file'].' '.@$l['line']."\n"; 
				$body .= "\n"; 
			}
			else
			if (function_exists('xdebug_get_function_stack'))
			{
				
				
				$body .= "\n\nBacktrace: \n"; 
				$body .= var_export(xdebug_get_function_stack(), true);
				//$x = debug_backtrace(); 
				//foreach ($x as $l) $body .= @$l['file'].' '.@$l['line']."\n"; 
				$body .= "\n"; 
			}
			
			
		}
		
		
		$mailer->setBody( $body );
		$mailer->setSender( $sender );
		$res = $mailer->Send();
		
		
		
	}
	return FALSE; 
}

plgSystemFatal_catcher::registerHandlers(); 
