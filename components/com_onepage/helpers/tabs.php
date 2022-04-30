<?php 
/** 
 * @version		$Id: opc.php$
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 

// no direct access
defined('_JEXEC') or die;

class OPCtabs {
	
	static $ehelper;
	
	/*comes from opc's plgVmBuildTabs*/
	public static function checkInsertTabs(&$view, &$tabs) {
		
		//j2.5 not compatible here:
		$app = JFactory::getApplication(); 
		if (method_exists($app, 'isClient')) {
			if (JFactory::getApplication()->isClient('site')) return; 
		}
		else {
			return;
		}
		
		//only backend:
		if (!JFactory::getApplication()->isClient('administrator')) return; 
		
		$task = JRequest::getVar('task'); 
		
		$layout = $view->getLayout(); 
		
		$arr = array('edit', 'product_edit'); 
		if (!in_array($layout, $arr)) return; 
		
			if (self::_init('user')) {
		    $render_in_third_address = OPCconfig::get('render_in_third_address', array()); 
			if (!empty($render_in_third_address)) {
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'third_address.php');
					OPCthirdAddress::getTabs($view, $tabs); 
				 
				}
			}
			$class = get_class($view); 
			
			switch ($class)
			{
			case 'VirtuemartViewManufacturer': 
			 if (self::_init('manufactorer')) {
				
				JFactory::getLanguage()->load('com_onepage', JPATH_ADMINISTRATOR); 
				self::getTabsByType('manufacturer', $view, $tabs); 
				self::addSaveNext(); 
				}
				break;
			case 'VirtuemartViewCategory': 
				if (self::_init('category')) {
				
				JFactory::getLanguage()->load('com_onepage', JPATH_ADMINISTRATOR); 
				self::getCategoryTabs($view, $tabs); 
				
				self::addSaveNext(); 
			}
			break;
			case 'VirtuemartViewProduct': 
				if (self::_init('product')) {
				
				JFactory::getLanguage()->load('com_onepage', JPATH_ADMINISTRATOR); 
				self::getProductTabs($view, $tabs); 
				
				self::addSaveNext(); 
			}
			break;
			case 'VirtuemartViewShipmentmethod': 
			case 'VirtuemartViewShipmentMethod': 
			  if (self::_init('shipmentmethod')) {
				
				
				
				JFactory::getLanguage()->load('com_onepage', JPATH_ADMINISTRATOR); 
				self::getShipmentTabs($view, $tabs); 
				
				self::addSaveNext(); 
			}
			break; 
			case 'VirtuemartViewPaymentMethod': 
			case 'VirtuemartViewPaymentmethod': 
			  if (self::_init('paymentmethod')) {
				
				JFactory::getLanguage()->load('com_onepage', JPATH_ADMINISTRATOR); 
				self::getPaymentTabs($view, $tabs); 
				
				self::addSaveNext(); 
			}
			break; 

			}
			
		
	}
	private static function checkSaveNext($type='') {
		
		$save_and_next = JRequest::getVar('save_and_next', 0); 
		if (!empty($save_and_next)) {
			
			$cid = JRequest::getVar('virtuemart_'.$type.'method_id', 0); 
			if (is_array($cid)) $cid = reset($cid); 
			$cid = (int)$cid; 
			if (!empty($cid)) {
				$db = JFactory::getDBO(); 
				$type = strtolower(JFile::makeSafe($type)); 
				$q = 'select `virtuemart_'.$db->escape($type).'method_id` from #__virtuemart_'.$db->escape($type).'methods where `virtuemart_'.$db->escape($type).'method_id` > '.(int)$cid.' order by `virtuemart_'.$db->escape($type).'method_id` desc limit 1'; 
				$db->setQuery($q); 
				$newCid = $db->loadResult(); 
				if (!empty($newCid)) {
					$url = 'index.php?option=com_virtuemart&view=paymentmethod&task=edit&cid[]='.(int)$newCid;
					JFactory::getApplication()->redirect($url); 
				}
			
			}
		}
		
	}
	
	private static function getTabsByType($type, &$view, &$tabs) {
		self::getIncludes(); 	
		$extra_html = ''; 
		$paths = self::getIncludePaths($type); 
		
				foreach ($paths as $p) {
				  $view->addTemplatePath( $p );
				}
				$forms = array(); 
				$JModelOrder_export = new JModelOrder_export(); 
				$ret = $JModelOrder_export->getJforms(true, $type); 
				
				
				
				if (!empty($ret))
				{
					
					
				foreach ($ret as $path=>$formData) {
					
					if (!empty($formData['allrenderedfields'])) {
						
						$forms[$path] = $formData; 
						$extra_html .= '<input type="hidden" name="tabext['.$type.']['.JFile::makeSafe($path).']" value="1" />'; 
						
					}
				}
				}
				/*
			$files = self::$ehelper->getExportTemplates('ALL');
			foreach($files as $f)
			{
				if (empty($f['tid_enabled'])) continue; 
				if (!empty($f['tid_xml'])) {
					$path = $f['tid_xml']; 
					
					
					
					
					if (isset($ret[$path]) && (!empty($ret[$path]['allrenderedfields']))) {
						
						
						
						$forms[$path] = $ret[$path]; 
					}
					
					
				}
			}
			*/
			
		if (!empty($forms)) {
			$view->opc_export_forms = $forms; 
			$view->opc_export_general = '<input type="hidden" name="opc_tabs['.$type.']" value="1" />'.$extra_html; 
			$tabs['opcorderexport'] = JText::_('COM_ONEPAGE_ORDER_EXPORT_CONFIG'); 		
		}
	}
	
	private static function getCategoryTabs(&$view, &$tabs) {
		self::getIncludes(); 	
		return self::getTabsByType('category', $view, $tabs); 
		
				
		
	}
	
	private static function getProductTabs(&$view, &$tabs) {
		self::getIncludes(); 	
		return self::getTabsByType('product', $view, $tabs); 
		
		
	}
	
	private static function getShipmentTabs(&$view, &$tabs) {
			self::getIncludes(); 	
			return self::getTabsByType('shipment', $view, $tabs); 
		    
			
			
	}
	
	private static function addSaveNext() {
		 return; 
		 //not yet impolmented
		
		
		$toolbar = JToolBar::getInstance('toolbar');
		$dhtml = '<button onclick="saveAndNext();" class="btn btn-small button-apply btn-success">
	<span class="icon-apply icon-white" aria-hidden="true"></span>
	'.JText::_('COM_ONEPAGE_SAVE_AND_NEXT').'</button>'; 
		$toolbar->appendButton('Custom', $dhtml);
		
	}
	
	private static function getPaymentTabs(&$view, &$tabs) {
		
			self::getIncludes(); 	
			return self::getTabsByType('payment', $view, $tabs); 
		   
	}
	
	public static function checkStoreTabs($tabs) {
		$app = JFactory::getApplication(); 
		if (method_exists($app, 'isClient')) {
			if (JFactory::getApplication()->isClient('site')) return; 
			
		}
		else {
			return; 
		}
		if (JFactory::getApplication()->isClient('administrator')) {
			
			
			
			foreach ($tabs as $type => $tab) {
				if (empty($tab)) continue; 
				if (self::_init($type)) {
					require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'order_export.php'); 
					$JModelOrder_export = new JModelOrder_export(); 
					
					$JModelOrder_export->store($type); 
					
					//self::checkSaveNext($type);
					
				}
			}
		}
		
		
	}
	
	
	private static function getIncludes() {
			if (!class_exists('VmConfig'))
			require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 
		     VmConfig::loadConfig(true); 
			if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'))
			include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php');

			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'order_export.php'); 
		 
			
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php'); 
			self::$ehelper = new OnepageTemplateHelper;
			
	}
	
	private static function getIncludePaths($type) {
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$arr = array(); 
		$pa = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.JFile::makeSafe($type).DIRECTORY_SEPARATOR; 
		
		if (file_exists($pa)) {
			return array($pa); 
			/*
			$files = JFolder::files($pa, '.php'); 
			foreach ($files as $fn) {
				$arr[] = $pa.$fn; 
			}
			*/
		}
		return $arr; 
		
	}
	
	private static function _init($asset='product') {
		 $option = JRequest::getVar('option', ''); 
		 if ($option !== 'com_virtuemart') return false; 
		 
		 $action = 'vm.'.$asset; 
		 $assetName = 'com_virtuemart.'.$asset; 
		 $z = JFactory::getUser()->authorise($action, $assetName);
		 if (empty($z)) return false; 
		 
		 return $z; 
		
	}
	
}