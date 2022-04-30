<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* OPC ADS plugin is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 


// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemOpcads extends JPlugin
{
   function __construct(& $subject, $config)
	{
 	  
	  $app = JFactory::getApplication(); 
	  if ($app->getName() != 'site') {
			return;
		}
		
	  parent::__construct($subject, $config);
	  		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		
		define('OPCADSPATH', $root.'plugins/system/opcads/'); 
		define('OPCPLUGINPATH', $root.'plugins/system/opcads/'); 
		
		$jlang = JFactory::getLanguage();
		
		$jlang->load('com_content', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_content', JPATH_ADMINISTRATOR, null, true);
		
		
	}
	
	function getExt($link)
	{
	  $a = explode('.', $link); 
	  if (empty($a[count($a)-1])) return ''; 
	  if ((strlen($a[count($a)-1])!==3) || ((strlen($a[count($a)-1])!==4))) return ''; 
	  return $a[count($a)-1]; 
	}
	 function getArticle()
	{
    $w = $this->params->get('image_width', 0); 
	$h = $this->params->get('image_height', 0); 
	$html = ''; 
	$image = $this->params->get('image_link', 0);
	if (!empty($image))
	{
	
	  $url = $this->params->get('url', 0);
	  if (!empty($url))
	  $html .= '<a style="margin:0;padding:0;" href="'.$url.'">'; 
	  $html .= '<img width="100%" style="display:inline;margin:0;padding:0;" src="'.$image.'" />'; 
	  if (!empty($url))
	  $html .= '</a>'; 
	
	$w = $this->params->get('image_width', 0); 
	$h = $this->params->get('image_height', 0); 
	if (empty($w) || (empty($h)))
	{
	 $ext = $this->getExt($image); 
	 $md5file = JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.md5($image).'.'.$ext; 
	 
	 if (function_exists('getimagesize'))
	 if (file_exists($md5file))
	  {
	     list($w, $h, $type, $attr) = @getimagesize($md5file);
	  }
	  else
	  {
	    $data = @file_get_contents($image); 
		if (!empty($data))
		 {
		   jimport( 'joomla.filesystem.file' );
		   @JFile::write($md5file, $data); 
		   	 if (file_exists($md5file))
			 {
			    list($w, $h, $type, $attr) = @getimagesize($md5file);
				$size = getimagesize($md5file);    
			 } 

		 }
		
		
		 
	  }
	  
	  
	}
	
	//$size = @getimagesize($image_link); 
	
	  // Add styles
	  if ((!empty($h)) && (!empty($w)))
	  {
	  
$style = '
        
		#simplesimplemodal-container {
		  width:'.$w.'px !important; 
		  max-width:'.$w.'px !important; 
		  /*height:'.$h.'px !important; */
		  max-height:'.$h.'px !important;
		};';
		 
		 $document = JFactory::getDocument();
		 $document->addStyleDeclaration($style);
	  }
	  return $html; 
	}
	
	
	 if ((!empty($h)) && (!empty($w)))
	  {
	  
$style = '
        
		#simplesimplemodal-container {
		  width:'.$w.'px !important; 
		  /*height:'.$h.'px !important; */
		  max-height:'.$h.'px !important;
		};';
		 
		 $document = JFactory::getDocument();
		 $document->addStyleDeclaration($style);
	  }
   
	$op_articleid = $this->params->get('article_id', 0);
   if (!is_numeric($op_articleid)) return "";
   
   if (is_numeric($op_articleid))
    {
	   $article = JTable::getInstance("content");
	   
	   $article->load($op_articleid);
	
	
		$parametar = new JRegistry($article->attribs);
	    $x = $parametar->get('show_title', false); 
		$x2 = $parametar->get('title_show', false); 
		
		
		
		$intro = $article->get('introtext'); 
		$full = $article->get("fulltext"); // and/or fulltext
		 JPluginHelper::importPlugin('content'); 
		  $dispatcher = JDispatcher::getInstance(); 
		  $mainframe = JFactory::getApplication(); 
		  $params = $mainframe->getParams('com_content'); 

		 if ($x || $x2)
		 {
		
		

		  $title = '<div class="componentheading'.$params->get('pageclass_sfx').'">'.$article->get('title').'</div>';
		  
		  }
		  else $title = ''; 
		  if (empty($article->text))
		  $article->text = $title.$intro.$full; 
		  
	      
	     
		  $results = $dispatcher->trigger('onPrepareContent', array( &$article, &$params, 0)); 
		  $results = $dispatcher->trigger('onContentPrepare', array( 'text', &$article, &$params, 0)); 
		  
		  return $article->get('text');
		
		
	}
   return ""; 

 }
	
    
	
	public function onAfterRoute()
	{
		
	   $doc = JFactory::getDocument(); 
		 $class = strtoupper(get_class($doc)); 
		// never run in an ajax context !
		$arr = array('JDOCUMENTHTML', 'JOOMLA\CMS\DOCUMENT\HTMLDOCUMENT'); 
		if (!in_array($class, $arr)) {
		  return false; 
		}
		$tmpl = JRequest::getVar('tmpl', ''); 
		if (!empty($tmpl)) return false; 
		$format = JRequest::getVar('format', 'html'); 
		if ($format !== 'html') return false; 
		
		
		
	  $maxresolution = (int)$this->params->get('maxresolution', 0); 
	  if (!empty($maxresolution)) {

		 
		  $style = "\n".' @media screen and (max-width: '.(int)$maxresolution.'px) { opcads { display: inline !important; } } ';
		  $doc->addStyleDeclaration($style);
		 
	  }
	  else {
		  $style = "\n".' opcads { display: inline !important; } '."\n"; 
		  $doc->addStyleDeclaration($style);

	  }
	  
	  $root = Juri::root(); 
	  if (substr($root, -1) !== '/') $root .= '/'; 
	  JHTML::script($root.'media/jui/js/jquery.cookie.js'); 
	  
	  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	 JHTMLOPC::script('fancybinder.js', 'components/com_onepage/assets/js/'); 
	  
	  JHTML::script(OPCADSPATH.'assets/js/opcads.js');
	  $pwe = $this->params->get('use_pwe', '');
	  if (!empty($pwe)) {
		  $myscript = ' function showPOPUP() {
			  '.$pwe.';
		  }'; 
		  $doc->addScriptDeclaration($myscript); 
		  
	  }
	  
	  $mode = $this->params->get('functiontype', 0); 
	  
	  //functiontype
	  $app = JFactory::getApplication(); 
	  if ($app->getName() != 'site') {
			return true;
		}
	  
	  $urls = $this->params->get('urls', ''); 
	  if (empty($urls)) {
		  $urlsA = array(); 
	  }
	  else
	  if (strpos($urls, "\n") !== false) {
	   $urls = $this->params->get('urls', ''); 
	   $urls = str_replace("\r\r\n", "\n", $urls); 
	   $urls = str_replace("\r\n", "\n", $urls); 
	   $urlsA = explode("\n", $urls); 
	  }
	  else {
		  
		  $urlsA = array($urls); 
	  }
	 // $script = ' var popup_urls = '.json_encode($urlsA).'; '; 
	 // $doc->addScriptDeclaration($script); 
	  
	  $config = new stdClass(); 
	  $config->urls = $urlsA; 
	  
	  $ct = $this->params->get('cookietime', 3600*24); 
	  $expires = time()+$ct; 
	  $xps = date(DATE_COOKIE, $expires); 
	
	  $config->expires = $xps; 
	  $config->debug = $this->params->get('debug', false); 
	  $config->mode = (int)$mode; 
	  $config->expires_seconds = (int)$ct; 
	  $config->seconds = (int)$this->params->get('seconds', 0); 
	   
	   if ( version_compare( JVERSION, '3.0', '>' ) == 1) {     
	  JHtml::_('jquery.framework');
	  JHtml::_('jquery.ui');
	  }
	  else
	  if (!$app->get('jquery'))
	  {
	    if ($this->params->get('jquery', 1))
		{
	      JHTML::script(OPCADSPATH.'/assets/js/jquery.js');
		}
	  }
	 
	 $root = Juri::root(); 
	 if (substr($root, -1) !== '/') $root .= '/'; 
	 $config->rooturl = $root; 
	 
	 $popupurlsuffix = $this->params->get('popupurlsuffix', ''); 
	 $config->popupurlsuffix = $popupurlsuffix; 
	 
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php'); 
	 JHTMLOPC::script('fancybinder.js', 'components/com_onepage/assets/js/'); 																																																	 
	  //set display with media queries to execute the logic
	  self::$myhtml = '<opcads style="display:none;" data-config="'.htmlentities(json_encode($config)).'"></opcads>';   
	  if (empty($pwe)) {
	   JHTML::script(OPCADSPATH.'assets/js/jquery.simplemodal.js');
	   JHtml::stylesheet(OPCADSPATH.'assets/css/jquery.simplemodal.css'); 
	   self::$myhtml .= '<div id="light-banner" style="display:none;">';
	   self::$myhtml .= $this->getArticle(); //.'<div style="clear:both;margin:0;padding:0;">&nbsp;</div>'; 
	   self::$myhtml .= '</div>'; 
	   
	  }
	}
	/*
	function onBeforeRender() {
		
		 $app = JFactory::getApplication(); 
	 if ($app->getName() != 'site') {
			return true;
		}

		$document = JFactory::getDocument();
	  $classd = strtoupper(get_class($document)); 
	  
		$arr = array('JDOCUMENTHTML', 'JOOMLA\CMS\DOCUMENT\HTMLDOCUMENT'); 
		if (!in_array($classd, $arr)) {
		  return false; 
		}
		
		
		if (empty(self::$myhtml)) return; 
		
		$html = self::$myhtml; 
		$htmlbody = JApplication::getBody();
		echo $htmlbody; die();  
		$htmlbody .= $html; 
		JApplication::setBody($htmlbody);
		
		
		//$htmlbody2 = JResponse::getBody();
		
		define('OPCADSRENDERED', 1); 
	}
	*/ 
	
	static $myhtml; 
	public function onAfterRender()
	{
	
	 if (defined('OPCADSRENDERED')) return; 
	
	 $app = JFactory::getApplication(); 
	 if ($app->getName() != 'site') {
			return true;
		}

		$document = JFactory::getDocument();
	  $classd = strtoupper(get_class($document)); 
	  
		$arr = array('JDOCUMENTHTML', 'JOOMLA\CMS\DOCUMENT\HTMLDOCUMENT'); 
		if (!in_array($classd, $arr)) {
		  return false; 
		}
		if (empty(self::$myhtml)) return; 
		
		
		
		/*
	//$session = JFactory::getSession(); 
	//$show = $session->get('opcads', 0);
	
	if (!empty($_COOKIE['opcads'])) 
	{
	  return; 
	}
	
	if (!empty($show)) return; 
	*/
	$html = self::$myhtml; //
	
	if (empty($html)) return;

	$buffer = JResponse::getBody();
	
	$bodyp = stripos($buffer, '</body'); 
	
	/*
	$ct = $this->params->get('cookietime', 3600*24); 
	$expires = time()+$ct; 
	$xps = date(DATE_COOKIE, $expires); 
	*/
	
	if ($bodyp === false) return; 
	
	if (strpos($buffer, 'player.vimeo.com/') !== false) {
		//$html .= '<script src="https://player.vimeo.com/api/player.js" crossorigin="crossorigin"></script>'; 
	}
	
	$javascript = $html;


//<div style="clear: both" class="ruplink"><a href="http://www.rupostel.com/" title="Virtuemart Extensions by RuposTel.com">Virtuemart Extensions by RuposTel.com</a></div>
//<link href="'.OPCADSPATH.'assets/css/jquery.simplemodal.css" rel="stylesheet" async="async" />
	//JHtml::stylesheet('jquery.simplemodal.css', OPCADSPATH.'assets/css/'); 
	$buffer = substr($buffer, 0, $bodyp).$javascript.substr($buffer, $bodyp); 
	
	JResponse::setBody($buffer);
	
	
	
	//setcookie(name,value,expire,path,domain,secure)
	//$domain = JURI::root();
	//$domain = $_SERVER['HTTP_HOST']; 
	//setcookie('opcads',1,time()+$ct,'/','',false);
	//setcookie("opcads", "opcads", 1);
	//$session->set('opcads', 'ok');
	
	}

	
}
