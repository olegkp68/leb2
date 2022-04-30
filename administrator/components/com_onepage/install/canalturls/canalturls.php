<?php 
/** 
 * @version		$Id: opc.php$
 * @copyright	Copyright (C) 2005 - 2014 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
 

// no direct access
defined('_JEXEC') or die;

class plgSystemCanalturls extends JPlugin
{
	
	private static $urls; 
	private static $currenturl; 
	private static $canonicalurl; 
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		$pageURL = 'http';
		if (isset($_SERVER['REQUEST_URI'])) {
     if ((isset($_SERVER['HTTPS'])) && ($_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
     $pageURL .= "://";
     if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER['SERVER_PORT'] != '443')) {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
     } else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
     }	
	 self::$currenturl = $pageURL; 
		}
		
	
	
	}
	
	private function checkURL() {
		
		$app = JFactory::getApplication();
		if (!$app->isClient('site')) return; 
		
		
		if (empty(self::$canonicalurl)) return; 
	    if (empty(self::$currenturl)) return; 
		
		$jinput = JFactory::getApplication()->input;
		$ign = array('start', 'limitstart'); 
		
		foreach ($ign as $k) {
		   $d = $app->get('list_limit'); 
		   $test = $jinput->get($k);
		   if (!empty($test)) {
			 
			  return; 	
		   }
		}
		JFactory::getLanguage()->load('com_virtuemart.sef', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart', 'en-GB'); 
		JFactory::getLanguage()->load('com_virtuemart_sef', JPATH_SITE); 
		$langvars = array('COM_VIRTUEMART_SEF_RESULTS', 'COM_VIRTUEMART_SEF_BY', 'results', 'by'); 
		foreach ($langvars as $l) {
			$text = JText::_($l).','; 
			
			if (strpos(self::$currenturl, $text) !== false) {
			
				return; 
			}
		}
		
		$app = JFactory::getApplication(); 
		if (!empty(self::$canonicalurl)) {
			$xa = explode('?', self::$currenturl); 
			if (is_array($xa)) {
				$cu = $xa[0]; 
				//current url before ? is equal to canonical:
				if ($cu === self::$canonicalurl) return; 
				//handles test.html/ to test.html
				
					$toredirect = self::$canonicalurl; 
					if (!empty($xa[1])) {
						$toredirect .= '?'.$xa[1]; 
					}
				
				if ((strpos($cu, self::$canonicalurl) === 0)) {
					
					
					$app->redirect($toredirect, 301); 
					$app->close(); 
				}
				
				//redirects for canonical test.html current urls: test/html and test////html test 
				// to canonical
				if (substr(self::$canonicalurl, -5) === '.html') {
					
					$t = substr(self::$canonicalurl, 0, -5); 
					if (strpos($cu, $t) === 0) {
					   	$app->redirect($toredirect, 301); 
						$app->close(); 
					}
					
					
				}
				
				if (strpos($cu, '//') !== false) {
					$c1 = str_replace(array('.html', 'html', '/'), array('', '', ''), $cu); 
					$c2 = str_replace(array('.html', 'html', '/'), array('', '', ''), self::$canonicalurl); 
					if ($c1 === $c2) {
						$app->redirect($toredirect, 301); 
						$app->close(); 
					}
				}
				
				
				
			} 
			
		}
	}
	
	
	public function onAfterRoute() {
		if (!JFactory::getApplication()->isClient('site')) return; 
		$doc = JFactory::getDocument(); 
		if ($doc->getType() !== 'html') return; 
		$app = JFactory::getApplication(); 
		$option = $app->input->get('option'); 
		$view = $app->input->get('view'); 
		$virtuemart_category_id = (int)$app->input->get('virtuemart_category_id'); 
		$virtuemart_product_id = (int)$app->input->get('virtuemart_product_id'); 
		self::$urls = array(); 
		if ($option === 'com_virtuemart') {
			switch ($view) {
				case 'category': 
					if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
					VmConfig::loadConfig();
				    $c = new stdClass(); 
					$c->virtuemart_category_id = $virtuemart_category_id; 
					$categoryModel = VmModel::getModel('category'); 
					$cat = $categoryModel->getCategory($virtuemart_category_id, false); 
					require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
					self::$canonicalurl = ''; 
					$storedPOST = $_POST; 
					$storedGET = $_GET; 
					$storedREQUEST = $_REQUEST; 
					self::$urls = OPCmini::addCategoryAlternateUrls($c, self::$canonicalurl); 
					
					self::checkURL(); 
					self::emptyCache();
					self::fixUrlState();
					$_POST = $storedPOST; 
					$_GET = $storedGET;
					$_REQUEST = $storedREQUEST; 
					break; 
				case 'productdetails':
					$p = new stdClass(); 
					 if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
					 VmConfig::loadConfig();
					 $pM = VmModel::getModel('product'); 
					 $product = $pM->getProduct($virtuemart_product_id); 
					 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
					 self::$canonicalurl = ''; 
					 self::$urls = OPCmini::addProductAlternateUrls($product, self::$canonicalurl); 
					 self::checkURL(); 
					 self::fixUrlState(); 
					 break; 
			
			}
		}
		
		
	}
	public static function fixUrlState() {
		if (empty(self::$currenturl)) return; 
		$app = JFactory::getApplication(); 
		$app->input->set('nolangfilter', 1);
						$router = $app->getRouter();
						$uri = new JUri(self::$currenturl); 
							try {
								$result = $router->parse($uri);
							}
							catch (Exception $e) {
								//no prob, this just removes language prefix.... 
							}
							$app->input->set('nolangfilter', null);
	}
	public static function emptyCache() {
		
		$orderModel = VmModel::getModel('category'); 
		if (method_exists($orderModel, 'emptyCache')) {
			$orderModel->emptyCache(); 
		}
		

		
	}
	
	private function _setLangLinks() {
		if (!JFactory::getApplication()->isClient('site')) return; 
		if (class_exists('ModLanguagesHelper')) {
			return; //cannot adjust language switcher
		}
		
		require_once(__DIR__.DIRECTORY_SEPARATOR.'modlanguagehelperoverride.php'); 
		
		$languages	= JLanguageHelper::getLanguages();
		if (!empty(self::$urls)) {
			foreach (self::$urls as $lang=>$url) {
				foreach ($languages as $ind => $l) {
				   if ($l->lang_code === $lang) {
					   $languages[$ind]->link = $url; 
				   }					   
				}
			}
		}
		ModLanguagesHelper::$languages = $languages; 
		
	}
	
	public function onAfterDispatch() {
		if (!JFactory::getApplication()->isClient('site')) return; 
		$doc = JFactory::getDocument();
		if ($doc->getType() !== 'html') return; 
		if (JFactory::getApplication()->get('canon_alt_set')) {
		self::_setLangLinks(); 
		if (!empty($doc->_links))
		foreach ($doc->_links as $url => $l) {
			if ($l['relation'] === 'canonical') { unset($doc->_links[$url]); }
			if ($l['relation'] === 'alternate') { unset($doc->_links[$url]); }
		}
		}
	}
	
	public function onBeforeRender() {
		if (!JFactory::getApplication()->isClient('site')) return; 
		$doc = JFactory::getDocument();
		if ($doc->getType() !== 'html') return; 
		if (JFactory::getApplication()->get('canon_alt_set')) {
		self::_setLangLinks(); 
		if (!empty($doc->_links))
		foreach ($doc->_links as $url => $l) {
			if ($l['relation'] === 'canonical') { unset($doc->_links[$url]); }
			if ($l['relation'] === 'alternate') { unset($doc->_links[$url]); }
		}
		}
	}
	
	
}