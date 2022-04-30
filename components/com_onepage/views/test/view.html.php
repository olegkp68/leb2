<?php
/**
 * @version		$Id: view.html.php 21705 2011-06-28 21:19:50Z RuposTel.com $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class OPCSessionHandler implements SessionHandlerInterface
{
    private $savePath;

    function open($savePath, $sessionName)
    {
        error_log(JFactory::getApplication()->get('istest', '').'null session open ...'); 
        return true;
    }

    function close()
    {
		error_log(JFactory::getApplication()->get('istest', '').'null session close ...'); 
        return true;
    }

    function read($id)
    {
		error_log(JFactory::getApplication()->get('istest', '').'null session read ...'); 
        return ''; 
    }

    function write($id, $data)
    {
		error_log(JFactory::getApplication()->get('istest', '').JFactory::getApplication()->get('istest', '').'OK: null session write attempt'); 
        return true;
    }

    function destroy($id)
    {
        error_log(JFactory::getApplication()->get('istest', '').'null session destroy ...'); 
        return true;
    }

    function gc($maxlifetime)
    {

        return true;
    }
}
$GLOBALS['ISTEST'] = 'TEST'; 

session_write_close(); 
session_module_name('user'); 
$handler = new OPCSessionHandler();
session_set_save_handler(
    array($handler, 'open'),
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
    );
	session_start(['read_and_close'=>1]);
	error_reporting(E_ALL); 
//session_set_save_handler($handler, true); 

error_log(JFactory::getApplication()->get('istest', '')."\n\n".'test ...'); 
$c = JFactory::getConfig(); 
$c->set('session_handler', 'opc'); 


$session = JFactory::getSession(); 
$session->set('t', 1); 


if(!class_exists('VmView'))
{
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmview.php'))
require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmview.php');
else
require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'vmview.php');
}
 
class virtuemartViewtest extends OPCView
{
	
	 function display($tpl = null) {
		 require(__DIR__.DIRECTORY_SEPARATOR.'test.config.php'); 
		 
		  $dev = JRequest::getVar('dev', false); 
		 if (!$dev) {
		 if (empty($ip)) return; 
		 if (!in_array($_SERVER['REMOTE_ADDR'], $ip)) return; 
		 }
		
		$session = JFactory::getSession(); 
		$testX = $session->get('testX'); 
		var_dump($testX); 
		$session->set('testX', 'XXX'); 
		
		 
		require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'test.calc.php');	 
		$OPCTest = new OPCTest; 
		$OPCTest->runTest(); 
	
	 }
	 
	
}