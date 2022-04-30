<?php 
/** 
* @version		$Id: opc.php$
* @copyright	Copyright (C) 2005 - 2014 RuposTel.com
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/


// no direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
class plgSystemHikaopc extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	private function hikaAutoload() {
		
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );
		
		if (!defined('AUTOLOADREGISTERED')) {
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
			OPCPlatform::hikaAutoload(); 
			
		
			
			jimport( 'joomla.session.session' );
			//stAn - this line will make sure that joomla uses it's session handler all the time. If any other extension is using $ _SESSION before this line, the session may not be consistent
			JFactory::getSession(); 
			// many 3rd party plugins faild on JParameter not found: 
			jimport( 'joomla.html.parameter' );
			// many 3rd party plugins also fail on JRegistry not found: 
			jimport( 'joomla.registry.registry' );
			// basic security classes should also be globally included: 
			

			if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR); 
			
			
		}
	}
	
	
	
	
	
	public function onTriggerPlughikaopc() {
		$document = JFactory::getDocument();
		$type = $document->getType(); 
		if (($type === 'html') || ($type === 'opchtml')) {
			
			$this->hikaAutoload(); 
			
			
			$old_task = JRequest::getVar('_old_task', null); 
			
			$arr = array('confirm', 'submitstep'); 
			
			if (in_array($old_task, $arr)) {
			   	$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'hikaopc.php'; 
			if (file_exists($path)) {
				
				require_once($path); 
				$OPCControllerHikaopc = new OPCControllerHikaopc(); 
				$OPCControllerHikaopc->checkout(); 
				return true; 
			}
			}
			else {
			$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'hika'.DIRECTORY_SEPARATOR.'opc.php'; 
			if (file_exists($path)) {
				
			
				
				require($path); 
				return true; 
			}
			}
		}
	}
	
	public function onAfterRoute() {

		if (!OPCPlatform::isHika()) return; 
		
		$view = JRequest::getWord('view', JRequest::getVar('ctrl', '')); 
		$option = JRequest::getWord('option', ''); 
		$layout = JRequest::getWord('layout', 'default'); 
		$importHika = false; 
		$task = JRequest::getVar('task', ''); 
		//com_hikashop
		if ($option === 'com_hikashop') {
			
			$views = array('cart', 'checkout'); 
			if (in_array($view, $views)) {
				if ($layout === 'default') {
					$importHika = true; 
					
					
					JFactory::getLanguage()->load('com_onepage', JPATH_SITE); 	
					
					JRequest::setVar('_old_task', $task); 
					JRequest::setVar('task', 'triggerplug-hikaopc'); 
					
					
				   
			}
			
			
			
			
			
		}
		}
		
		$view = JRequest::getVar('view', ''); 
		
		if (($option === 'com_onepage') ) {
			
			$importHika = true; 
			
		}
		
		if (!empty($importHika)) {
			JRequest::setVar('hikashop_front_end_main',1);
			
			//mystery lines from hikashop.php		
			$session = JFactory::getSession();
			if(is_null($session->get('registry'))) {
				jimport('joomla.registry.registry');
				$session->set('registry', new JRegistry('session'));
			}
			
			if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR); 
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) {
				return;
			};
			
			$this->hikaAutoload(); 
			
			
			
			
				    $old_task = JRequest::getVar('_old_task', $task); 
					$arr = array('confirm', 'submitstep'); 
					if (in_array($old_task, $arr)) {
						$cart_id = OPChikaCart::getCartId(); 
						$cart_id = (int)$cart_id; 
						JRequest::setVar('cart_id', $cart_id); 
					}
					else {
						if (class_exists('OPChikaplugin')) {
						OPChikaplugin::unregister('hikashopshipping');			
						}
					}
			
		}
		

		
	}
	
	
}


