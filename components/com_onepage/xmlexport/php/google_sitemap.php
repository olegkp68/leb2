<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


// all classes should be named by <element>Xml per it's manifest with upper letter for the element name and the Xml
class Google_sitemapXml {
	function __construct($config, $xml) {
			JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
			$lang = JFactory::getLanguage(); 
			
	}
	
	public static $done_sef_links; 
	public static $current_xml_count; 
	public static $all_xml_count; 
	public static $index_files; 
	
	function specialURLs($sefurl) {
		
		static $c302; 
		static $c404; 
		static $cKw; 
		if (((!empty($c302)) || (!empty($c404))) || (!empty($cKw))) {
			if (isset($c302[$sefurl])) return $c302[$sefurl]; 
			if (isset($c404[$sefurl])) return ''; 
			foreach ($cKw as $keyword => $new) {
				//OPCXmlExport::log( $keyword.' '.$sefurl); 
				if (strpos($sefurl, $keyword) !== false) {
					//disabled by keyword:
					if ($new === false) return ''; 
					//redirected by keyword:
					
					return $new; 
					
				}
			}
			
			return $sefurl; 
			
			
		}
		
		if ((!empty($this->params->special_urls)) && ((empty($c302)) && (empty($c404)))) {
			$c302 = array(); 
			$c404 = array(); 
			$cKw = array(); 
			$u = str_replace(array("\r\r\n", "\r\n"), "\n", $this->params->special_urls); 
			$ua = explode("\n", $u); 
			
			
			
			foreach ($ua as $line) {
				$line = trim($line); 
				
				if (empty($line)) continue; 
				$x1 = strpos($line, '*'); 
				//includes space followed by http, which is redirect:
				if (strpos($line, ' http') !== false) {
					$xa = explode(' http', $line); 
					$from = trim($xa[0]); 
					$to = trim('http'.$xa[1]); 
					if ($x1 === 0) {
						$x2 = strpos($line, '*', $x1+1); 
						if ($x2 !== false) {
						$keyword = substr($line, 1, $x2-$x1-1); 
						$cKw[$keyword] = $to; 
						}
					}
					else {
					 $c302[$from] = $to; 
					}
					
				}
				elseif ($x1 === 0) {
					
					$x2 = strpos($line, '*', $x1+1); 
					if ($x2 !== false) {
					   $keyword = substr($line, 1, $x2-$x1-1); 
					   $cKw[$keyword] = false; 
					}
					
				}
				else {
					
					$c404[$line] = true;
				}
			}
			
			if ((!empty($c302)) || (!empty($c404))) {
			if (isset($c302[$sefurl])) return $c302[$sefurl]; 
			if (isset($c404[$sefurl])) return ''; 
			foreach ($cKw as $keyword => $new) {
				if (strpos($sefurl, $keyword) !== false) {
					//disabled by keyword:
					if ($new === false) return ''; 
					//redirected by keyword:
					return $new; 
					
				}
			}
			
			}
			
		}
		
		
		
		return $sefurl; 
	}
	
	function setExportPath($num=0) {
		
		if (empty($num)) {
			$this->orig_xmlpath = $this->config->xmlpath; 
			$this->orig_xmlfile = $this->config->xmlfile; 
		}
		//if (!empty($this->config->language_changed)) return; 
		static $lang; 
		if (empty($lang)) {
		  $lang = JRequest::getWord('lang', '', 'GET'); 
		}
		
		$myname = $this->config->entity; 
		
		
		$name = $myname; 
		
		$name .= '_'.(int)$num;
		
		if (!empty($lang)) {
			$name .= '_'.$lang; 
		}
		$this->config->xmlpath = $this->general->xml_export_path.$name.'.xml'; 
		$this->xmlfile = $name.'.xml'; 
		$this->config->xmlfile = $name.'.xml'; 
		if (empty(self::$index_files)) {
			self::$index_files = array(); 
		}
		$url = $this->getXMLUrl($this->general, $this->config, false); 
		self::$index_files[$url] = $url; 
		return $this->config->xmlpath; 
		
		/*
		$xmlfile_orig = $this->config->xmlfile; 
		$xmlfile = str_replace('google_sitemap', 'google_sitemap_'.$lang, $this->config->xmlfile); 
		if (!empty($lang)) {
			$this->config->xmlpath = str_replace($xmlfile_orig, $xmlfile, $this->config->xmlpath); 
			$this->config->xmlfile = $xmlfile; 
			$this->config->language_changed = true; 
		}
		*/
		
		
	}
	
	function getXMLUrl($generalconfig, $extconfig, $ondemand=false)
	{
	   $path = $generalconfig->xml_export_path; 
	   if (stripos($path, JPATH_SITE)===0)
	   $path = substr($path, strlen(JPATH_SITE)); 
	   $path = str_replace(DS, '/', $path); 
	   if (substr($path, 0,1) == '/') $path = substr($path, 1); 
	   $url = $generalconfig->xml_live_site.$path.$extconfig->xmlfile; 
	   
	   if (!empty($ondemand)) {
	     $url = $generalconfig->xml_live_site.'index.php?option=com_onepage&view=xmlexport&format=opchtml&tmpl=component&lang=en&dowork=dowork&print=1&file='.$extconfig->name; 
	   }
	   
	   return $url; 
	}
	
	
 function clear()
  {
  }
  function startHeaders()
  {
	 if (empty(self::$done_links)) self::$done_links = array();   
			  
	  
    $xml = urldecode('%3C%3F').'xml version="1.0" encoding="UTF-8"?>'."\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n"; 
	
	
	
	return $xml; 
	 
  }
  
  static $done_links; 
  function addItem($product, $vm1)
  {
	  static $justOnce; 
	  if (empty($justOnce)) {
		  $root = Juri::root(); 
	
	$isnow = date("Y-m-d"); 
	if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
	$link = $root; 
	$xml = ''; 
	/*
	if (empty(self::$done_links[$link])) {
		$xml .= $this->getLinkXml($root, $isnow, 1, 'daily'); 
	}
	*/
	$homes = $this->getHomes(); 
	
	$lang = JRequest::getWord('lang', '', 'GET'); 
	if (!empty($lang)) {
		$url = 'index.php?lang='.$lang; 
		$langTag = $this->getLangTagFromSef($lang); 
		
		if (isset($homes[$langTag])) {
			$url .= '&Itemid='.$homes[$langTag]->id; 
		}
		else {
			$url .= '&Itemid='.$homes['*']->id; 
		}
		
		if ($url !== '/') {
			$link = $url; 
			if (empty(self::$done_links[$link])) {
				
				$xml .= $this->getLinkXml($link, $isnow, 1, 'daily'); 
			}
		}
	}
	  if (php_sapi_name() === 'cli') {
		 //only possible in SAPI as we don't know which cats are already exported in batch/step export
		 $xml .= $this->getCategoryLinks(); 
		 $xml .= $this->getMenus(); 
	   }
	
	  }
	  
	 
	  if (empty($product->published)) {
		  OPCXmlExport::log('x'); 
		  return ''; 
	  }
	  
	 $virtuemart_product_id = $product->virtuemart_product_id; 
	 
		  $modified = ''; 
	      $oDate = new DateTime($product->modified_on);
		  if (!empty($odate)) {
		  $modified = $oDate->format("Y-m-d");
		  }
		  if (empty($product->product_parent_id)) { $changefreq = 'daily'; } else { $changefreq =  'weekly'; } 
		  if (empty($product->product_parent_id)) { $priority = '1'; } else { $priority = '0.9'; }
		  $data = ''; 
		  $link = $product->non_sef_link;
		  
		  
		  
		  
			  
			 
		  
		   
			  
			 if (empty(self::$done_links)) self::$done_links = array();   
			  if (empty(self::$done_links[$link])) {
			  self::$done_links[$link] = true;
			  $data = $this->getLinkXml($link, $modified, $priority, $changefreq); 
			  }
		  
		  
	if (php_sapi_name() !== 'cli') {
	foreach ($product->categories as $cat_id) {
		$data .= $this->getCategoryLink($cat_id); 
	}
	}
	
			
  
			return $data; 

  }
  
  private function getLinkXml(&$link, $modified_on='', $priority='0.5', $changefreq='weekly') {
	  $toStop = false;
	  if (empty(self::$current_xml_count)) {
	     self::$current_xml_count = 0; 
	  }
	  if (empty(self::$all_xml_count)) {
	     self::$all_xml_count = 0; 
	  }
	  
/*	  
	  if (strpos($link, 'coupé') !== false) {
		  $toStop = true; 
	  }
	  $domain = $this->params->xml_live_site;
			  $url_path = substr($link, strlen($domain)); 
			  if (strpos($url_path, '/') !== false) {
			  $xa = explode('/', $url_path); 
			  $parts = array(); 
			  foreach ($xa as $part) {
				  $parts[] = urlencode($part); 
			  }
			   $url_path = implode('/', $parts); 
			   $link = $domain.$url_path; 
			  }
			  else {
				  
			  }
			  */
			  /*
	  echo $link."\n";
	  echo htmlentities($link, ENT_XML1)."\n"; 
	  if (strpos($link, 'coupé') !== false) {
		  echo $link; die(); 
	  }
	  */
	   
	   
	   $multi_lang_links = $this->getMultiLangUrls($link); 
	   
	   
	   
	   if (empty(self::$done_sef_links)) self::$done_sef_links = array(); 
	   
	   //$link = reset($multi_lang_links); 
	   
	   ob_start(); 
	   
	   foreach ($multi_lang_links as $lang_tagX => $link) {
		   //302:
		   $link = $this->specialURLs($link); 
		   //404:
	       if (empty($link)) continue; 
	       if (!empty(self::$done_sef_links[$link])) {
		     continue; 
	       }
	       self::$done_sef_links[$link] = true; 
		   
	 ?>
	 <url><loc><?php echo htmlentities($link, ENT_XML1); ?></loc><?php
		self::$current_xml_count++;
		self::$all_xml_count++; 
		if (count($multi_lang_links) > 1) {
			foreach ($multi_lang_links as $lang_tag => $url) {
				
				//302 and 404 handling:
				$url = $this->specialURLs($url); 
				if (empty($url)) continue; 
				
				
				$la = explode('-', $lang_tag);
				$cl = reset($la); 
				self::$current_xml_count++;
				self::$all_xml_count++; 
				?><xhtml:link rel="alternate" hreflang="<?php echo $cl; ?>" href="<?php echo htmlentities($url, ENT_XML1); ?>"/>
				<?php
			}
		}
	 
	 
	 
		if (!empty($modified_on)) { 
		?><lastmod><?php 
		  
		  echo htmlentities($modified_on, ENT_XML1);
		  
		?></lastmod><?php 
		}
		if (!empty($changefreq)) { 
		?><changefreq><?php echo $changefreq; ?></changefreq><?php 
		}  if (!empty($priority)) { 
		?><priority><?php echo $priority; ?></priority><?php } 
		?></url>
		<?php 
	   }
	  $data = ob_get_clean(); 
	  if (empty($this->config->maxlinks)) $this->config->maxlinks = 40000;
	  $max_links = (int)$this->config->maxlinks; 
	  if (empty($max_links)) $max_links = 40000; 
	  if (self::$current_xml_count > $max_links) {
		  if (class_exists('cliHelper')) {
			cliHelper::debug('reached max links count '.$max_links.', creating new XML file'); 
		  }
		  
		  $endHeaders = $this->endHeaders(); 
		  $this->writer->write($endHeaders); 
		  $this->isFinished = true; 
		  $this->writer->isFinished = true; 
		  $this->writer->close();
		  $this->writer->group(); 
		  $xml_export_disable_compression = OPCconfig::getValue('xmlexport_config', 'xml_export_disable_compression', 0, false); 
		  if (empty($xml_export_disable_compression)) {
		    $this->writer->compress(); 
		  }
		  $this->isCompressed = true; 
		  $this->writer->isCompressed = true; 
		  static $iter; 
		  if (empty($iter)) {
			  $iter = 0; 
		  }
		  $iter++; 
		  $new_path = $this->setExportPath($iter); 
		  $this->isFinished = null; 
		  $this->writer->isFinished = null; 
		  $this->isCompressed = null; 
		  $this->writer->isCompressed = null; 
		  $this->writer = new OPCWriter($new_path); 
		  $this->writer->open(); 
		  $startHeaders = $this->startHeaders(); 
		  $this->writer->write($startHeaders); 
		  self::$current_xml_count = 0; 
	  }
	  $this->writer->write($data); 
	  return ''; 
  }
  
  public function hasOwnWriter() {
	  return true; 
  }
  
  private function getQuery($non_sef_url) {
	  $query = array(); 
	  $qa = parse_url($non_sef_url); 
		if (!isset($qa['query'])) {
			if (empty($qa['query'])) return array(); 
		}
		parse_str($qa['query'], $query);
		return $query; 
  }
  
  private function getCategoryLink($cat_id) {

		//$link = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.(int)$cat_id, false); 
		$link = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.(int)$cat_id; 
		//$link = OPCXmlExport::getLink('', $link); 
		if (empty(self::$done_links[$link])) {
		 self::$done_links[$link] = true; 
		 return $this->getLinkXml($link, '', '0.7', 'weekly'); 
		}
		return ''; 
	  
  }
  private function getCategoryLinks() {
	  $db = JFactory::getDBO(); 
	  $q = 'select virtuemart_category_id from #__virtuemart_categories where published = 1'; 
	  $db->setQuery($q); 
	  $res = $db->loadAssocList(); 
	  $extra = ''; 
	  if (!empty($res)) {
	  foreach ($res as $row) {
		  $link = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.(int)$row['virtuemart_category_id']; 
		  //$link = OPCXmlExport::getLink('', $link); 
		  if (empty(self::$done_links[$link])) {
			  self::$done_links[$link] = true; 
			  $extra .=  $this->getLinkXml($link, '', '0.6', 'weekly'); 
			  
		  }
	  }
	  }
	  return $extra; 
  }
  
  private function getMenus() {
	  if (!empty($this->config->menus)) {
	  $menus = $this->config->menus;
	  }
	  else {
		  $menus = array(); 
	  }
	  
	  
	  $db = JFactory::getDBO(); 
	  $q = 'select `id`, `menutype`, `link` from #__menu where `published` = 1 and `client_id` = 0 and `access` = 1 and `type` = "component" '; 
	  $db->setQuery($q); 
	  $res = $db->loadAssocList(); 
	  $extra = ''; 
	  if (!empty($res)) {
	  foreach ($res as $row) {
		  if (!empty($menus)) {
			  if (!in_array($row['menutype'], $menus)) {
				  //echo 'Skipping '.$row['menutype']; die(); 
				  continue; 
			  }
		  }
		  
		  $link = $row['link']; //.'&Itemid='.(int)$row['id']; 
		  $link = 'index.php?Itemid='.(int)$row['id']; 
		  //$link = OPCXmlExport::getLink('', $link); 
		  
		  if (empty(self::$done_links[$link])) {
			  
			  
			  
			  self::$done_links[$link] = true; 
			  $extra .=  $this->getLinkXml($link, '', '1', 'weekly'); 
			  
		  }
	  }
	  }
	  return $extra; 
	  
  }
  
  
  
  function endHeaders()
  {
	  $xml = ''; 
	  
	 $xml .= '</urlset>'."\n"; 
	 return $xml; 
  
  }
  function compress()
  {
  }
  
  function getSitemapIndexes() {
	  
	  $oDate = new DateTime();

	  $modified_on = date('c'); 
	  $xml_export_disable_compression = OPCconfig::getValue('xmlexport_config', 'xml_export_disable_compression', 0, false); 
		if (empty($xml_export_disable_compression)) {
			$suffix = '.gz'; 
		}
		else {
			$suffix = ''; 
		}
	  
	  $indexHtml = urldecode('%3C%3F').'xml version="1.0" encoding="UTF-8"?>
   <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'; 
   foreach (self::$index_files as $url) {
	  $indexHtml .= '<sitemap>
      <loc>'.htmlentities($url.$suffix, ENT_XML1).'</loc>
      <lastmod>'.$modified_on.'</lastmod>
   </sitemap>';    
   }
   
   
   $indexHtml .= '</sitemapindex>';
   return $indexHtml; 
  }
  
  function finalize() {
	  
	  $fn = JPATH_SITE.DIRECTORY_SEPARATOR.$this->orig_xmlpath; 
	  $indexFileHtml = $this->getSitemapIndexes(); 
	  file_put_contents($fn, $indexFileHtml); 
	  
	  //JFile::copy($this->writer->file, JPATH_SITE.DIRECTORY_SEPARATOR.'sitemap.xml'); 
	  $data = ''; 
	  if (php_sapi_name() === 'cli') {
		  if (!empty(self::$done_sef_links)) {
			  foreach (self::$done_sef_links as $link => $x) {
				  $data .= $link."\n"; 
			  }
		  }
	  }
	  file_put_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'sitemap.txt', $data); 
	  
	  if (class_exists('cliHelper')) {
			cliHelper::debug('found '.self::$all_xml_count.' URLs'); 
	  }
	  
  }
  
  
function getMyRoot() {
	  $root = Juri::root(true); 
	  if (($root === '/') || (empty($root))) {
		  $root = Juri::root(); 
		  if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
		  return $root; 
	  }
	  $base = JRoute::_('index.php'); 
	  if (strpos($base, $root) !== false) {
		  $full = Juri::root(); 
		  $full = substr($full, 0, -1 * strlen($root)); 
		  if (substr($full, -1) === '/') $full = substr($full, 0, -1); 
		  return $full; 
	  }
	  $full = Juri::root(); 
	  if (substr($full, -1) === '/') $full = substr($full, 0, -1); 
	  return $full; 
	  
	  
  }
  public function getMenusLinks($Itemid) {
	  
	  
	  $absoluteUrl = $this->getMyRoot(); 
	  
	  $languages	= JLanguageHelper::getLanguages('lang_code');
	  $associations = MenusHelper::getAssociations($Itemid);
	  $ret = array(); 
	  foreach ($associations as $lang_code => $langItemId) {
		  if (!$this->setLanguageByTag($lang_code)) continue; 
		  $ret[$lang_code] = $absoluteUrl.JRoute::_('index.php?Itemid='.(int)$langItemId); 
	  }
	  if (empty($ret)) {
		  return array(0 => $absoluteUrl.JRoute::_('index.php?Itemid='.$Itemid)); 
	  }
	  return $ret; 
	  
  }
  
 /*comes from modvmLanguagesHelper*/
 private function getMultiLangUrls($non_sef_url) {
		$r = $this->getList($non_sef_url); 
		return $r; 
	}
	
	private function getHomes() {
		static $homes; 
		if (!empty($homes)) return $homes; 
		$user		= JFactory::getUser();
		$lang		= JFactory::getLanguage();
		$languages	= JLanguageHelper::getLanguages();
		$app		= JFactory::getApplication();
		$menu		= $app->getMenu();
		$query =  array(); 
		
		
		
		
		$homes = array();
		$router = $app->getRouter();
		//catch 22
		JFactory::getApplication()->setLanguageFilter(true);
		

			// Get menu home items

			$homes['*'] = $menu->getDefault('*');

			foreach ($languages as $item) {
				$default = $menu->getDefault($item->lang_code);
				
				if ($default && $default->language == $item->lang_code) {
					$homes[$item->lang_code] = $default;
				}
			}
			
			if (count($homes) === 1) {
				$db = JFactory::getDBO(); 
				$q = 'select * from #__menu where `home` = 1 and `language` <> \'*\' and `client_id` = 0 and `published` = 1 and `access` = 1'; 
				$db->setQuery($q); 
				$res = $db->loadObjectList(); 
				foreach ($languages as $item) {
					foreach ($res as $default) {
						if ($default->language == $item->lang_code) {
							$homes[$item->lang_code] = $default;
						}
					}
				}
			}
			
			return $homes; 
	}
	
	
	
	public function getLangTagFromSef($lang_sef) {
		$languages   = JLanguageHelper::getLanguages('lang_code');
		 foreach ($languages as $l) {
		   
		   if ($l->sef === $lang_sef) {
			   return $l->lang_code; 
			   	   
			   
		   }
	   }
	   return $lang_sef; 
	}
	
public function getList($non_sef_url)
	{
		
		
		$varsOrig = JFactory::getApplication()->getRouter()->getVars(); 


		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
		static $vmConfigLoaded; 
		if (empty($vmConfigLoaded)) {
		VmConfig::loadConfig();
		$vmConfigLoaded = true; 
		}
		
		$user		= JFactory::getUser();
		$lang		= JFactory::getLanguage();
		$languages	= JLanguageHelper::getLanguages();
		$app		= JFactory::getApplication();
		$menu		= $app->getMenu();
		$query =  array(); 
		
		
		$query = $this->getQuery($non_sef_url); 
		if ((count($query) === 1) && (!empty($query['Itemid']))) {
		   $menuLinks = $this->getMenusLinks($query['Itemid']);
		   
		   return $menuLinks; 
		}
		$homes = $this->getHomes(); 
		$router = $app->getRouter();
		//catch 22
		JFactory::getApplication()->setLanguageFilter(true);
			
			
			// Load associations
			$assoc = JLanguageAssociations::isEnabled();
		 

		if (empty($query['Itemid'])) {
			$query['Itemid'] = (int)$this->findCorrectItemid($query, $menu, $non_sef_url); 
		}
		
		if (empty($query['Itemid'])) {
			
		}
		
		$cassociations = array();
		if ($assoc)
		{
			
			$active = $menu->getItem($query['Itemid']);
			if ($active)
			{
				$associations = MenusHelper::getAssociations($active->id);
			}

			
				// Load component associations
				if (!empty($query['option'])) {
				$class = str_replace( 'com_', '', $query['option'] ).'HelperAssociation';
				$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.JFile::makeSafe($query['option']).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'association.php';
				
				if (file_exists($path)) {
				  JLoader::register( $class, $path );
				}

				if(class_exists( $class ) && is_callable( array($class, 'getAssociations') )) {
					$cassociations = call_user_func( array($class, 'getAssociations') );
				}
				}
				else {
					if (!empty($query['Itemid'])) {
						$query2 = $this->getQuery($active->link); 
						
						if (!empty($query2['option'])) {
						$class = str_replace( 'com_', '', $query2['option'] ).'HelperAssociation';
						$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.JFile::makeSafe($query2['option']).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'association.php';
						if(!class_exists( $class ))
						if (file_exists($path)) {
							JLoader::register( $class, $path );
						}

				if(class_exists( $class ) && is_callable( array($class, 'getAssociations') )) {
					$cassociations = call_user_func( array($class, 'getAssociations') );
				}
				}
						
					}
				}
			
		}

	
	
	
		
		//We need limitstart, except it is empty
		if(empty($query['limitstart'])){
			unset($query['limitstart']);
		}

		
		if (isset($query['Itemid'])) {
			$itId = (int)$query['Itemid'];
		}
		else {
			$itId = false;
		}

		$levels		=  array(1);

		$currentLang = VmLanguage::$currLangTag;
		
		
		
		
		$absoluteUrl = $this->getMyRoot(); 
		// Filter allowed languages
		
		
		$ret_urls = array(); 
		
		foreach ($languages as $i => $language) {
			
			unset($language->link); 
			
			//vmdebug('JLanguageMultilang::isEnabled',$language );
			// Do not display language without frontend UI
			if (!JLanguage::exists($language->lang_code)) {
				unset($languages[$i]);
				
			}
			// Do not display language without specific home menu
			elseif (!isset($homes[$language->lang_code])) {
				unset($languages[$i]);
				
			}
			// Do not display language without authorized access level
			elseif (isset($language->access) && $language->access && !in_array($language->access, $levels)) {
				unset($languages[$i]);
				
			}
			else {
				$url= '';
				$language->active = ($language->lang_code == $lang->getTag());

				if (JLanguageMultilang::isEnabled()) {
					
					if(!empty($language->link)) {
						$language->link = $absoluteUrl.$language->link;
						continue;
					}

					$itemid = '';
					if (isset($cassociations[$language->lang_code])) {
						$cassociations[$language->lang_code] = urldecode($cassociations[$language->lang_code]); 
						$language->link = $absoluteUrl.JRoute::_($cassociations[$language->lang_code] . '&lang=' . $language->sef);
						
					} else if(isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code])) {
						$itemid = $associations[$language->lang_code];
					} else {
						if($currentLang==$language->lang_code){
							//vmdebug('Requesting for own language');
							if(isset($query['Itemid'])){
								$itemid = $query['Itemid'];
							} 
						} else {
							$this->setLanguageByTag($language->lang_code); 
							
							
							$itemid = $this->findCorrectItemid($query, $menu, $non_sef_url);
							if(empty($itemid)) {
								$itemid = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
							}
						}
						

					}

					if(empty($language->link)){

						if(!empty($itemid)){
							$query['Itemid'] = $itemid;
						}

						if(!empty($language->sef)){
							$query['lang'] = $language->sef;
						}

						$url = 'index.php?';
						foreach($query as $n=>$v){
							$url .= $n .'='.$v.'&';
						}
						$url = rtrim($url,'&');
						
						//$url = $cUrl.'&lang='.$language->sef.$itemid;
						if ($app->getCfg('sef')=='1') {
							$this->setLanguageByTag($language->lang_code); 
							$url = urldecode($url); 
							$language->link = $absoluteUrl.JRoute::_($url);
							


							//vmdebug('JLanguageMultilang::isEnabled',$language->lang_code, $language->link,$url );
						} else {
							$language->link = $url;
						}
					}
				}
				else {
					$language->link = $absoluteUrl.JRoute::_('&Itemid=' . $homes['*']->id);
			
					
				}

			}

		}
		
		
		
		$this->setLanguageByTag($currentLang); 
		
		
		JFactory::getApplication()->getRouter()->setVars($varsOrig, false); 
		
		if (empty($languages)) {
			if ((strpos($non_sef_url, 'Itemid=') === false) && (!empty($query['Itemid']))) {
				$non_sef_url .= '&Itemid='.(int)$query['Itemid']; 
			}
			//OPCXmlExport::log($non_sef_url); 
			$sef_link = $absoluteUrl.JRoute::_($non_sef_url); 
			//OPCXmlExport::log($sef_link); 
			return array($currentLang => $sef_link); 
		}
		
		$urls = array(); 
		foreach ($languages as $l) {
		  $urls[$l->lang_code] = $l->link;
		}
		return $urls; 
		
	}
	public function setLanguageByTag($tag) {
		
		if (!JLanguage::exists($tag)) return false;  
		$homes = $this->getHomes(); 
		if (!isset($homes[$tag])) return false; 
		$langObj = JFactory::getLanguage(); 
		$root = Juri::root(); 
		if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
		$absoluteUrl = $root; 
		$sef_lang = JRequest::getVar('lang', ''); 
		$languages	= JLanguageHelper::getLanguages();
		foreach ($languages as $lang_code => $lObj) {
			if ($lObj->lang_code === $tag) {
				if ((int)$lObj->access !== 1) {
					return false; 
				}
				
				$sef_lang = $lObj->sef; 
				$langObj = $lObj; 
			}
				
		}
		
		vmlanguage::setLanguageByTag($tag, false);
		vmLanguage::$currLangTag = $tag; 
		
		//JFactory::$language = $langObj;
		JFactory::getApplication()->setLanguageFilter(true);
		$app		= JFactory::getApplication();
		$x = $app->getLanguage(); 
		if (empty($x)) {
		   return false; 
		}
		
		$langClass = JLanguage::getInstance($tag, 0);
		$app->set('language', $langClass); 
		$router = $app->getRouter();
		$trickUrl = $absoluteUrl.'/'.$sef_lang.'/'; 
		$app->input->set('nolangfilter', 1);
		$uri = new JUri($trickUrl); 
							try {
								$result = $router->parse($uri);
							}
							catch (Exception $e) {
								//no prob, this just removes language prefix.... 
							}
		$app->input->set('nolangfilter', null);
		vmLanguage::loadJLang('com_virtuemart.sef',true);
		
		return true; 				
	}
	public function findCorrectItemid($query, $menu, $non_sef_url){
		
		
		$app		= JFactory::getApplication();
		$user = JFactory::getUser();
		$andAccess = ' AND `client_id` = 0 AND `published`=1 AND ( `access` = 1 ) ';

		

		$like = '';
		$cmds = array('option', 'view', 'task', 'layout');
		$vm=0;
		foreach($cmds as $cmd){
			if(isset($query[$cmd])){
				$like .= '&'.$cmd.'='.$query[$cmd];
			}
		}
		
		
		
		
		$option = ''; 
		$view = ''; 
		if (!empty( $query['option'])) {
		$option = $query['option'];
		}
		if (!empty($query['view'])) {
		$view =  $query['view'];
		}

		if($option=='com_virtuemart'){
			$ints = array();
			if($view == 'category'){
				$ints = array('virtuemart_category_id','virtuemart_manufacturer_id');
			} else if($view == 'productdetails'){
				$ints = array('virtuemart_product_id');
			}
		} else {
			$ints = array('id');
		}

		foreach($ints as $cmd){
			if(isset($query[$cmd])){
				$like .= '&'.$cmd.'='.$query[$cmd];
			} else {
				$like .= '&'.$cmd.'=0';
				//vmdebug('The $cmd '.$cmd.' was not in the query');
			}

		}

		if($like!==''){
			$like = '`link` like "index.php?'.substr($like,1).'%"';
		} else {
			$like = '`home`="1"';
		}
		
		$db = JFactory::getDbo();
		$q = 'SELECT `id` FROM `#__menu` WHERE `link` = \''.$db->escape($non_sef_url).'\'  '.$andAccess.' limit 1'; //and (language="*" or language = "'.$db->escape(vmLanguage::$currLangTag).'" )'.$andAccess.' limit 1';
		$h = md5($q);
		if (!isset($c[$h])) {
			
		$db->setQuery($q);
		
		$c[$h] = $db->loadResult();
		if (!empty($c[$h])) {
			
			$associations = MenusHelper::getAssociations($c[$h]);
			if (isset($associations[vmLanguage::$currLangTag])) {
			  return $associations[vmLanguage::$currLangTag]; 
			}
			
		}
		}
		if (empty($c[$h])) {
		$q = 'SELECT `id` FROM `#__menu` WHERE '.$like.'  '; 
		//and (language="*" or language = "'.vmLanguage::$currLangTag.'" )'; 
		$q .= $andAccess;
		$q .= ' ORDER BY `language` DESC limit 1';
		$h = md5($q);
		}
		
		if (!empty($c[$h])) {
			$associations = MenusHelper::getAssociations($c[$h]);
			if (isset($associations[vmLanguage::$currLangTag])) {
			  return $associations[vmLanguage::$currLangTag]; 
			}
		}
		

		static $c = array();

		if(isset($c[$h])){
			vmdebug('Found CACHED itemid '.vmLanguage::$currLangTag,$c[$h]);

			return $c[$h];
		} else {
			$db = JFactory::getDbo();
			$db->setQuery($q);
			$c[$h] = $db->loadResult();
			
			if(!$c[$h]){
				if($view == 'productdetails'){
					$query['view'] = 'category';
					
					return $this->findCorrectItemid($query, $menu, $non_sef_url);
				} else if($view == 'category' and isset($query['virtuemart_category_id']) and !empty($query['virtuemart_category_id'])){
					$query['virtuemart_category_id'] = 0;
					
					return $this->findCorrectItemid($query, $menu, $non_sef_url);
				}
			} else {
				$associations = MenusHelper::getAssociations($c[$h]);
				if (isset($associations[vmLanguage::$currLangTag])) {
					return $associations[vmLanguage::$currLangTag]; 
				}
				
				vmdebug('Found as new itemid '.$q,$c[$h]);
			}


			return $c[$h];

		}
	} 
  
  
}
 