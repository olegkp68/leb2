<?php
/**
 * @version		$Id: contact.php 21555 2011-06-17 14:39:03Z chdemko $
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class JControllerPairing extends JControllerBase
{
 function getViewName() 
	{ 
		return 'pairing';		
	} 

   function getModelName() 
	{		
		return 'pairing';
	}
	
	
  function categoryxls_upload() {
	    jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$fileName = $_FILES['uploadedupdatefile']['name'];
		$fileTemp = $_FILES['uploadedupdatefile']['tmp_name'];
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
	    $msg = ''; 
		$entity = JRequest::getVar('entity', ''); 
		$xc = new VirtueMartControllerXmlexport();  
		$converted = $xc->getListForEntity($entity, true); 
		
		if ((!$this->_getPHPExcel()) || (!$this->_checkPerm()) || (empty($entity)) || (empty($converted))) {
			
			
			
			JFile::delete($fileTemp); 
			$link = 'index.php?option=com_onepage&view=xmlexport'; 
		    $msg = JText::_('COM_ONEPAGE_ERROR').': 42'; 
			$this->setRedirect($link, $msg);
			return;
		}
		try {
			$reader = PHPExcel_IOFactory::createReaderForFile($fileTemp); 
		    $reader->setReadDataOnly(true);
			$objXLS = $reader->load($fileTemp);
		    $value = $objXLS->getSheet(0)->getCell('A1')->getValue();
		    $sheet = $objXLS->getSheet(0); //->getCellByColumnAndRow(0, 1);
			$rows = $sheet->getHighestRow();
			$cx = 0; 
			for ($row=2; $row<=$rows; $row++) {
				$category_id = (int)$sheet->getCellByColumnAndRow(0,$row)->getValue(); 
				if (empty($category_id)) continue; 
				$remote_name = $sheet->getCellByColumnAndRow(2,$row)->getValue(); 
				if (empty($remote_name)) {
					$default = new stdClass(); 
					$default->id = 0; 
					$res = OPCconfig::clearConfig('xmlexport_pairing', $entity, $category_id); ; 
				}
				foreach ($converted as $remote_id => $r_name) {
					if ($r_name === $remote_name) {
						//$remote_id = $remote_name; 
						
						$store = new stdClass(); 
						$store->id = $remote_id; 
						$store->txt = $remote_name; 
						OPCconfig::store('xmlexport_pairing', $entity, $category_id, $store); 
						$cx++; 
						break;
					}
				}
			 
			}
		$objXLS->disconnectWorksheets();
		JFile::delete($fileTemp); 
		unset($reader);
		unset($objXLS);
		
		
		
		
		  $msg = 'Stored '.(int)$cx.' category entries'; 
		}
		catch(Exception $e) {
			$msg .= (string)$e; 
		}
		
		JFile::delete($fileTemp); 
		 $link = 'index.php?option=com_onepage&view=pairing&asset=virtuemart_category_id&entity='.urlencode($entity).'&type=xmlexport'; 
		 $this->setRedirect($link, $msg);
		
  }
  private function _getPHPExcel() {
		@ini_set("memory_limit",'32G');
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) 
			return false; 
		//$this->_die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel<br />Install via RuposTel One Page Checkout -> OPC Order Manager -> Excell Export -> Download and Install');
		
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');
		return true; 
	}
  private function _checkPerm() {
	   $user = JFactory::getUser(); 
	   
      $isroot = $user->authorise('core.admin');	
	  
	  if (!$isroot) 
	  {
		
		return false; 
	  }
	  
	  $iss = JFactory::getApplication()->isSite(); 
	  if (!empty($iss)) return false; 
	  
	  return true; 
   }
}