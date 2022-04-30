<?php 
/**
 * @version		stockavai.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		COMMERCIAL 
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemStockavai extends JPlugin
{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAjaxStockavai() {
		@header('Content-Type: text/html; charset=utf-8');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			
		$sku = JRequest::getVar('product_sku', ''); 
		$system = JRequest::getWord('system', ''); 
		jimport( 'joomla.filesystem.file' );
		$system = JFile::makeSafe($system); 
		$system = strtolower($system); 
		
		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$system.'.php')) {
			require_once(__DIR__.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$system.'.php'); 
		}
		
		$nocache = JRequest::getVar('nocache', false); 
		if ($nocache === 'false') $nocache = false; 
		if (!empty($nocache)) $nocache = true; 
		
		$system_class = $system.'AvailabilityStockHelper'; 
		
		if (!empty($sku)) {
			try {
		 $resp = $system_class::updateAvailability($sku, $nocache); 
		 
		 $html = $this->getAvailablityTemplate($sku, $resp); 
		 if (!empty($html)) {
			 echo $html; 
		 }
		 else {
		 $html = ''; 
		 if (!empty($resp)) $html = $resp; 
		 $html .= $system_class::getAvailablityHtml($sku); 
		 $avai = $system_class::getAvailability($sku); 
		 if (!empty($avai)) {
		  echo $html.'&nbsp;<span class="availabilityText">'.$avai.'</span>'; 
		 }
			}
			
			}
			catch(Exception $e) {
				echo (string)$e; 
			}
		 
		}
		
		echo @ob_get_clean(); 
		echo @ob_get_clean(); 
		echo @ob_get_clean(); 
		JFactory::getApplication()->close(); 
		jExit(); 
	}
	function plgGetAvailablityHtml($sku, &$html='') {
		$resp = ''; 
		$html = $this->getAvailablityTemplate($sku, $resp); 
	}
	function getAvailablityTemplate($sku, &$resp) {
		$template = JFactory::getApplication()->getTemplate();
		$tp = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'productdetails'.DIRECTORY_SEPARATOR.'default_stockavai.php'; 
		
		if (file_exists($tp)) {
			require_once(__DIR__.DIRECTORY_SEPARATOR.'viewhelper.php'); 
			$viewHelper = new viewHelper(); 
			if ($viewHelper->assignProduct($sku)) {
				$html = $viewHelper->display(); 
				$article = new stdClass(); 
				$article->text = $html; 
				$params = new JRegistry('');
				JPluginHelper::importPlugin('content'); 
				$dispatcher = JDispatcher::getInstance(); 
				$results = $dispatcher->trigger('onContentPrepare', array( 'text', &$article, &$params, 0)); 
				if (!empty($article->text)) $html = $article->text; 
				$resp = $html; 
				return $html; 
			}
		}
		$resp = ''; 
		return ''; 
		
		
	}
	
	function plgStockAvaiDisplayAvai(&$html, $sku, $loaderhtml, $system, $jQuerySelector='') {
		$sku = trim($sku); 
		
		$root = Juri::root(); 
		if ( !JFactory::getApplication()->get('jquery', false)) {
			 JHtml::_('jquery.framework', false);
			 JFactory::getApplication()->set('jquery', true);
		}
		
		if (substr($root, -1) !== '/') $root .= '/'; 
		$url = $root.'plugins/system/stockavai/stockavai.js'; 
		$document = JFactory::getDocument();
		$document->addScript($url);
		if (!empty($jQuerySelector)) {
		 $document->addScriptDeclaration(' function getStockAvaiselector() { return '.$jQuerySelector.'; } '); 
		}
		$stockurl = $root;
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		$lang = OPCloader::getLangCode(); 
		
		$url = $root.'index.php?option=com_ajax&plugin=stockavai&format=raw&nosef=1&product_sku='.urlencode($sku).'&system='.urlencode($system);
		if (!empty($lang)) $url .= '&lang='.urlencode($lang); 
		$Itemid = JRequest::getInt('Itemid', 0); 
		if (!empty($Itemid)) $url .= '&Itemid='.(int)$Itemid; 
		
		
		
		$data = array('sku'=>$sku, 'stockurl'=>$url, 'loaderhtml'=>$loaderhtml); 
		if (empty($jQuerySelector)) {
		  $html .= '<div class="stockavai" data-sku="'.htmlentities(json_encode($data)).'" id="stockavai_'.$sku.'">'.$loaderhtml.'&nbsp;</div>';
		}
		else {
			echo '<stockavai class="stockavai" data-sku="'.htmlentities(json_encode($data)).'" id="stockavai_'.$sku.'" data-selector="'.htmlentities($jQuerySelector).'" ></stockavai>';
			
		}
		
		
	}
	
}
