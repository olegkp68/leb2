<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* GeoLocator is free software released under GNU/GPL  This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* This php file was created by www.rupostel.com team
*/

	defined( '_JEXEC' ) or die( 'Restricted access' );
	jimport('joomla.application.component.view');
	
	if(version_compare(JVERSION,'3.0.0','>=')) {
	class GeoViewExt extends JViewLegacy {
	
	}
}
else {
	if(version_compare(JVERSION,'3.0.0','<')) {
		class GeoViewExt extends JView {
	
		}
	}
}
	
	class defaultViewDefault extends GeoViewExt
	{
		function display($tpl = null)
		{	
			@header('Content-Type: text/html; charset=utf-8');
			@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

		    $from = JRequest::getInt('from', 0); 
		    $to = JRequest::getInt('to'); 
		    $url = JRequest::getVar('durl', ''); 
			$licensekey = JRequest::getVar('licensekey', ''); 
			$url = str_replace('YOUR_LICENSE_KEY', $licensekey, $url); 
			
			
			
			$savekey = JRequest::getVar('savekey', 0); 
			
			if (!empty($savekey)) {
				$db = JFactory::getDBO(); 
				$q = 'select `params` from #__extensions where `element` = \'com_geolocator\' and `type` = \'component\' limit 1'; 
				$db->setQuery($q); 
				$r = $db->loadResult(); 
				if (!empty($r)) {
				$rx = @json_decode($r, false); 
				$rx->licensekey = $licensekey;
				}
				else {
					$rx = new stdClass(); 
					$rx->licensekey = $licensekey;	
				}
				$params = json_encode($rx); 
				
				$q = 'update #__extensions set `params` = \''.$db->escape($params).'\' where `element` = \'com_geolocator\' and `type` = \'component\''; 
				
				$db->setQuery($q); 
				$db->execute(); 
			}
			
			
			$step = JRequest::getInt('step', 0); 
		    //echo $from.$to.$url;		 
			//$model = &$this->getModel();
			$model = $this->getModel('default');
			//echo $from.' '.$to; 
			ob_start(); 
			//if ($from > 0) die();
			if ($step == 0)
			{
			echo 'CSV URL: '.$url."<br />"; 
			if (!$model->download($url))
			 {
			   echo 'Cannot download and save file from specified URL to tmp directory of Joomla!'; 
			 }
			else echo '<div id="result_ok"></div>'; 
			}
			if ($step == 1)
			if (!$model->extract($url))
			 {
			   echo 'Cannot extract CSV file!<error_here></error_here>'; 
			 }
			else echo '<div id="result_ok"></div>'; 
			if ($step == 2)
			 {
			   $x = $model->insert($from, $to); 
			   if ($x === true)
			   echo '<div id="result_ok"></div>'; 
			   else if ($x === false)
			   echo '<div class="error_here">Cannnot find CSV file</div>';
			   else if ($x === -3)
			   echo '<div class="finished_rows">finished</div>';
			 }
			else
			if ($step == 3)
			 {
			   $model->clean($url);
			   echo '<div class="finished_here"></div>';
			 }
			 $x = ob_get_clean(); 
			 echo $x; 
			$mainframe = JFactory::getApplication();
			$mainframe->close(); 
		}
	}
