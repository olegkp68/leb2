<?php
/**
 * @version		One page checkout for Virtuemart - plugins gallery
 * @copyright	Copyright (C) 2005 - 2011 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');


class plgSystemPostloader extends JPlugin {
	public static $current_url = ''; 
	public function __construct( &$subject, $config )
	{
		self::$current_url = (string)JUri::current();
		parent::__construct( $subject, $config );	
	}
	
	
	
	function onAfterRoute() {
		if (!$this->canRun()) return; 
		
		$tags = $this->params->get('rewrite_tags', ''); 
		if (empty($tags)) return; 
		
		$only_site_map = $this->params->get('only_sitemap', false); 
		if (!empty($only_site_map)) return; 
		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		$sor = $root.'plugins/system/postloader/assets/helper.js'; 
		JHtml::script($sor); 
		$webp = $this->params->get('rewrite_webp', 0); 
		if (((!empty($_SERVER['HTTP_ACCEPT'])) && (stripos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false)) && (empty($webp))) { 
	$src = ' var _globalWebpEnabled = true; ';  } else { $src = ' var _globalWebpEnabled = false; '; } 
	$document = JFactory::getDocument();
	if (method_exists($document, 'addScriptDeclaration')) {
		$document->addScriptDeclaration($src); 
	}
	
		
	}
	///index.php?option=com_ajax&plugin=Sitemappostloader&group=system&format=xml
	public function onAjaxSitemappostloader() {
	
	@header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	@header("Cache-Control: post-check=0, pre-check=0", false);
	@header("Pragma: no-cache");
	$format = JRequest::getVar('format', ''); 
	if ($format === 'xml') { 
		@header('Content-Type: application/xml; charset=utf-8');
	}
	else {
		@header('Content-Type: text/plain; charset=utf-8');
	}
		
		$db = JFactory::getDBO(); 
		$q = 'select `url`, `img` from #__onepage_imagesitemap where 1=1 order by `url` '; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/';
		$lasturl = ''; 
		if ($format === 'xml') { 
		echo urldecode('%3C%3F').'xml version="1.0" encoding="UTF-8"'.urldecode('%3F%3E')."\n".'<urlset 
		 xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xmlns:xhtml="http://www.w3.org/1999/xhtml"
      xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n"; 
		}
		$done = array(); 
		
		
		
		
		foreach ($res as $row) {
			
			
			
			$iurl = $row['img']; 
			
			
			
			if (strpos($iurl, $root ) !== 0) continue;
			
			
			$webpurl = ''; 
			if (strpos($iurl, '/images/') !== false) { 
			$xa = explode('/images/', $iurl); 
			
			
			$rootpath = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$xa[1]; 
			if (!file_exists($rootpath)) continue; 
			$webp = str_replace(array('.jpg', '.png', '.JPG', '.JPEG', '.PNG'), array('.webp','.webp','.webp','.webp','.webp'), $xa[1]); 
			
			
			$webppath = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$webp; 
			if (file_exists($webppath)) {
				$webpurl = $root.'images/'.$webp; 
			}
			
			}
			
			
			
			$url = $row['url']; 
			//only sef urls: 
			if (strpos($url, 'index.php') !== false) continue; 
			if (strpos($url, '?') !== false) continue; 
			if (strpos($url, $root) !== 0) continue;
			
			if ((empty($lasturl)) || ($lasturl !== $url)) {
					
					if ($format === 'xml') {	
					if (!empty($lasturl)) {
						echo '</url>'."\n"; 
					}
					echo '<url>'."\n".'<loc>'.$url.'</loc>'."\n"; 
					$timestamp = strtotime('yesterday midnight');
					
					echo '<lastmod>'.date('c', $timestamp).'</lastmod>'."\n"; 
					?><changefreq>daily</changefreq><?php echo "\n"; 
					?><priority>1.0000</priority><?php echo "\n"; ?>
					<?php
					}
					
			}
			
			
			 
				
			if ($format === 'xml') {	
	if (!empty($webpurl)) {
		?><image:image><image:loc><?php echo $webpurl; ?></image:loc></image:image><?php echo "\n";
		
	}		
     
    ?><image:image><image:loc><?php echo $iurl; ?></image:loc></image:image><?php echo "\n";
	
			}
			if ($format === 'raw') {
			  if (empty($done[$iurl])) {
				  echo $iurl."\n"; 
			  }
			  if (!empty($webpurl)) {
				  echo $webpurl."\n"; 
			  }
			  $done[$iurl] = true; 
			  continue; 	
			}
		
		 
		 
		 $lasturl = $url; 
			
		
		}
		
		if (!empty($lasturl)) {
						echo '</url>'."\n"; 
					}
					
		if ($format === 'xml') { 
		 echo '</urlset>'."\n"; 
		}
		
		JFactory::getApplication()->close(); 
		
	}
	
	 private static function tableExists($table)
  {
   static $cache; 
   
   
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
   
   
   if (empty($cache)) $cache = array(); 
   
   if (isset($cache[$table])) return $cache[$table]; 
   
  
  	
   
   $q = "SHOW TABLES LIKE '".$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   
	   if (empty($cache)) $cache = array(); 
	   
	   if (!empty($r)) 
	    {
		$cache[$table] = true; 
		return true;
		}
		$cache[$table] = false; 
   return false;
  }
	
	
	
	
	function _createTable() {
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_imagesitemap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5url` varchar(32) CHARACTER SET ascii NOT NULL,
  `md5img` varchar(32) CHARACTER SET ascii NOT NULL,
  `url` varchar(5000) CHARACTER SET utf8mb4 NOT NULL,
  `img` varchar(5000) CHARACTER SET utf8mb4 NOT NULL,
  `modified_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5url_2` (`md5url`,`md5img`),
  KEY `md5url` (`md5url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;'; 
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$db->query(); 
	}
	
	function canRun() {
		$doc = JFactory::getDocument(); 
	  $c = strtolower(get_class($doc)); 
	  
	  if (method_exists($doc, 'getType')) {
			$type = $doc->getType(); 
			if ($type !== 'html') {
			   return false;
			}
			else {
				$arr = array('joomla\cms\document\htmldocument', 'jdocumenthtml'); 
				if (!in_array($c, $arr)) return false; 
			}
	  }
	  $tmpl = JRequest::getVar('tmpl', ''); 
	  if (!empty($tmpl)) return false; 
	  $format = JRequest::getVar('format', 'html'); 
	  if ($format !== 'html') return false; 
	  
	  if (JFactory::getApplication()->isAdmin()) {
		 
		  if (!self::tableExists('onepage_imagesitemap')) {
				$this->_createTable(); 
			}
		  
		  return false; 
	  }
	  if (!JFactory::getApplication()->isSite()) return false; 
	  
	  return true; 
	  
	}
	
	function onAfterRender()
	{
		if (!$this->canRun()) return; 
		$app = JFactory::getApplication();		
		
		if ($app->isAdmin()) {
			return;
		}

		$tags = $this->params->get('rewrite_tags', ''); 
		
		$only_site_map = $this->params->get('only_sitemap', false); 
		if (!empty($only_site_map)) $only_site_map = true; 
		
		
		$search = array(); 
		$rep = array(); 
		
		//$voidelements = array('area','base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'); 
		$voidelements = array('embed', 'img'); 
		$has_void = array(); 
		if (strpos($tags, ',') !== false) {
			$ea = explode(',', $tags); 
			foreach ($ea as $t) {
				$tag = trim($t); 
				if (in_array(strtolower($tag), $voidelements)) {
					$has_void[] = strtolower($tag); 
					continue; 
				}
				
				$search[] = '<'.strtolower($tag).' '; 
				$repsearch[] = '<pload'.strtolower($tag).' '; 
				$search[] = '<'.strtoupper($tag).' '; 
				$repsearch[] = '<pload'.strtolower($tag).' '; 
				
				$search[] = '</'.strtoupper($tag).' '; 
				$repsearch[] = '</pload'.strtolower($tag).' '; 
				
				$search[] = '</'.strtolower($tag).' '; 
				$repsearch[] = '</pload'.strtolower($tag).' '; 
				
			}
		}
		else {
			
				$tag = trim($tags); 
				if (in_array(strtolower($tag), $voidelements)) {
					$has_void[] = strtolower($tag); 
				
				}
				else {
				$search[] = '<'.strtolower($tag).' '; 
				$repsearch[] = '<pload'.strtolower($tag).' '; 
				$search[] = '<'.strtoupper($tag).' '; 
				$repsearch[] = '<pload'.strtolower($tag).' '; 
				
				$search[] = '</'.strtoupper($tag).' '; 
				$repsearch[] = '</pload'.strtolower($tag).' '; 
				
				$search[] = '</'.strtolower($tag).' '; 
				$repsearch[] = '</pload'.strtolower($tag).' '; 
				}
		}
		$body = JResponse::getBody();
		if (!empty($has_void)) {
			//foreach ($has_void  as $tag) 
			
			$result = array(); 
			/*
			$body = '<img src="templates/transform/images/reviews.png" alt=" reviews" style="margin-bottom:5px;" />
			<embed src="templates/transform/images/reviews.png" alt=" reviews" style="margin-bottom:5px;" />
			<div>
			<img src="templates/transform/images/reviews.png" alt=" reviews" style="margin-bottom:5px;" >
			<embed src="templates/transform/images/reviews.png" alt=" reviews" style="margin-bottom:5px;" ></div>';
			*/
			if (count($has_void) > 1) {
			 $searchr = '/<('.implode('|', $has_void).')[^>]+>/i';
			}
			else {
				$searchr = '/<'.reset($has_void).'[^>]+>/i';
			}
			//echo $searchr; 
			$r = preg_match_all($searchr,$body, $result); 
			//var_dump($result[0]); die(); 
			if (!empty($result[0]))
			foreach ($result[0] as $img_line) {
				self::siteMapAdd($img_line); 
				if (empty($only_site_map)) {
				if (strpos($img_line, 'src=') === false) continue; 
				if (strpos($img_line, 'src="data:') !== false) continue; 
				if (strpos($img_line, 'src=\'data:') !== false) continue; 
				if (strpos($img_line, 'nopostload') !== false) continue; 
				
				foreach ($has_void as $current_tag) {
				
				$upper = strtoupper($current_tag); 
				
				$lower = strtolower($current_tag); 
				
				if (strpos($img_line, $lower) === 1) {
					$is_lower = true; 
				}
				elseif (strpos($img_line, $upper) === 1) {
					$is_lower = false; 
				}
				else {
					//we dont' support stuff like iMg 
					continue; 
				}
				
				
				
				if (substr($img_line, -2, 2) === '/>') {
					if ($is_lower) {
						$search[] = $img_line;
						$mytag = strtolower($current_tag); 
						$rep = str_replace('<'.$mytag, '<pload'.$mytag, $img_line); 
						$rep = substr($rep, 0, -2).'></pload'.$mytag.'>'; 
						
						
						$repsearch[] = $rep;
					}
					else {
						
						$search[] = $img_line;
						$mytag = strtoupper($current_tag); 
						$rep = str_replace('<'.$mytag, '<pload'.strtolower($current_tag), $img_line); 
						$rep = substr($rep, 0, -2).'></pload'.strtolower($current_tag).'>'; 
						
						$repsearch[] = $rep;
					}
					
				}
				else {
					if ($is_lower) {
						$search[] = $img_line;
						$mytag = strtolower($current_tag); 
						$rep = str_replace('<'.$mytag, '<pload'.$mytag, $img_line); 
						$rep = $rep.'</pload'.strtolower($current_tag).'>'; 
						$repsearch[] = $rep;
					}
					else {
						
						$search[] = $img_line;
						$mytag = strtoupper($current_tag); 
						$rep = str_replace('<'.$mytag, '<pload'.strtolower($current_tag), $img_line); 
						$rep = $rep.'</pload'.strtolower($current_tag).'>'; 
						$repsearch[] = $rep;
					}
				}
				}
				}
			}
			}
			/*
			die(); 
			foreach ($has_void  as $tag) 
				$doc = new DOMDocument();
				@$doc->loadHTML($body);

				$tags = $doc->getElementsByTagName('img');

				foreach ($tags as $tag) {
					echo $tag->getAttribute('src');
					echo $doc->saveXML($tag);
					die('x'.__LINE__); 
				}
				
		}
		*/
		//var_dump($search); var_dump($repsearch); die(); 
		
		//$rep = array('<iframe', '/iframe', '<IFRAME', '/IFRAME'); 
		//$with = array('<postiframe', '/postiframe', '<postiframe', '/postiframe'); 
		if (empty($only_site_map)) {
		$count = 0; 
		if (!empty($search)) {
		$body = str_replace($search, $repsearch, $body, $count); 
		
		//$body .= var_export(self::$imgs, true); 
		
		if ($count) {
		 JResponse::setBody($body);
		}
		}
		}
		
		
		
		if (!empty(self::$imgs)) {
			self::storeSiteMap(); 
		}
		
		
		
		
	}
	static $imgs; 
	public static function siteMapAdd($img_line) {
		
		if (empty(self::$imgs)) {
			self::$imgs = array(); 
		}
		$result = array(); 
		
		preg_match_all('/[^"\'=\s]+\.(jpe?g|png|gif|svg|webp)/i',$img_line, $result);
		
		foreach ($result[0] as $r) {
			self::$imgs[$r] = $r; 
			
		}
		
	}
	
	public static function storeSiteMap() {
		if (function_exists('http_response_code')) {
		 $x = http_response_code(); 
		 if ($x !== 200) { 
				return; 
		 }
		}
		$url = self::$current_url; 
		$md5url = md5($url); 
		
		$db = JFActory::getDBO(); 		
		$q = 'select * from `#__onepage_imagesitemap` where `md5url` = \''.$db->escape($md5url).'\''; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		$toUpdate = array(); 
		$toDelete = array(); 
		foreach ($res as $row) {
			foreach (self::$imgs as $ind=>$i) {
				
				$info = self::getImgInfo($i); 
				if ($info === false) {
					continue; 
				}
				else
				if (($info['img'] === $row['img']) || ($info['md5img'] === $row['md5img'])) {
					$id = (int)$row['id']; 
					if ($info['modified_on'] === 0) {
						$toDelete[$id] = $id; 
					}
					else 
					if ($row['modified_on'] !== $info['modified_on']) {
						$upd = $info; 
						$upd['id'] = $id; 
						$toUpdate[] = (object)$upd; 
						unset(self::$imgs[$ind]); 
						continue; 
					}
					elseif ($row['modified_on'] === $info['modified_on']) {
						//ignore:
						unset(self::$imgs[$ind]); 
						continue; 
					}
				}
			}
		}
		$toIns = array(); 
		foreach (self::$imgs as $imgurl) {
			$imgInfo = self::getImgInfo($imgurl); 
			if ($imgInfo !== false) {
			  $toIns[] = (object)$imgInfo;
			}
		}
		
		
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
		$JModelUtils = new JModelUtils; 
		
		
		
		if ((!empty($toDelete)) || (!empty($toUpdate)) || (!empty($toIns))) {
		$q = 'start transaction'; 
		$db->setQuery($q); $db->execute(); 
		
		
		
		if (!empty($toDelete)) {
			$q = 'delete from #__onepage_imagesitemap where `id` IN ('.implode(',', $toDelete).')'; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		
		if (!empty($toUpdate)) {
			$JModelUtils->mergeDataIntoTable('#__onepage_imagesitemap', $toUpdate, array('id')); 
		}
		
		if (!empty($toIns)) {
		  $JModelUtils->mergeDataIntoTable('#__onepage_imagesitemap', $toIns, array('md5url', 'md5img'), array('id')); 
		  //var_dump($toIns); die(); 
		}
		
		$q = 'commit'; 
		$db->setQuery($q); $db->execute(); 
		}
		
		
	}
	public static function getImgInfo($imgurl) {
		
		$imgurlorig = $imgurl; 
		$url = self::$current_url; 
		$md5url = md5($url); 
		$db = JFactory::getDBO(); 
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		
		$root4 = Juri::root(true); 
		if (substr($root4, -1) !== '/') $root4 .= '/'; 
		
		
		$root2 = str_replace('https:', '', $root); 
		$root3 = str_replace('http:', '', $root); 
		
		if (strpos($imgurl, $root) === 0) {
				$imgurl = substr($imgurl, strlen($root)); 
			}
			if (strpos($imgurl, $root2) === 0) {
				$imgurl = substr($imgurl, strlen($root2)); 
			}
			if (strpos($imgurl, $root3) === 0) {
				$imgurl = substr($imgurl, strlen($root3)); 
			}
			
			
			if (strpos($imgurl, $root4) === 0) {
				$imgurl = substr($imgurl, strlen($root4)); 
			}
			
			
			
			if (stripos($imgurl, 'http') === 0) return false; 
			if (strpos($imgurl, '//') === 0) return false; 
			if (strpos($imgurl, '/') === 0) $imgurl = substr($imgurl, 1); 
			$imgpath = JPATH_SITE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $imgurl); 
			$imgurl = $root.$imgurl;
			$allIns = array(); 
			if (file_exists($imgpath)) {
				/*
				$insRow = array(
				'id' => 'NULL', 
				'md5url' => $db->escape($md5url),
				'url' => $db->escape($url),
				'img' => $db->escape($imgurl), 
				'modified_on' => $db->escape(date("Y-m-d H:i:s", filemtime ($imgpath)))
				);
				*/
				
				$insRow = array(
				//'id' => 'NULL',
				'md5url' => $md5url,
				'md5img' => md5($imgurlorig),
				'url' => $url,
				'img' => $imgurl, 
				'modified_on' => date("Y-m-d H:i:s", filemtime ($imgpath))
				);
				
				
				return $insRow; 
			}
			else {
				$insRow = array(
				
				'md5url' => $md5url,
				'url' => $url,
				'img' => $imgurl, 
				'modified_on' => 0
				);
			}
			return false;
		
	}
	
	public static function strposall($haystack,$needle, $offset = 0){
   
    $input = md5($haystack.' '.$needle.' '.$offset); 
	if (empty(self::$_cachesearch)) self::$_cachesearch = array(); 
	if (isset(self::$_cachesearch[$input])) return self::$_cachesearch[$input]; 
	
    $s=$offset;
    $i=0;
    
	if (empty($needle)) {
		self::$_cachesearch[$input] = false; 
		return false; 
	}
	
	if (empty($haystack)) {
		self::$_cachesearch[$input] = false; 
		return false; 
	}
	
    while (is_integer($i)){
       
        $i = stripos($haystack,$needle,$s);
       
        if (is_integer($i)) {
            $aStrPos[] = $i;
            $s = $i+strlen($needle);
			
        }
    }
    if (isset($aStrPos)) {
		self::$_cachesearch[$input] = $aStrPos; 
        return $aStrPos;
    }
    else {
		self::$_cachesearch[$input] = false; 
        return false;
    }
}
	
}