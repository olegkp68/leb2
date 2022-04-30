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

class plgSystemStopbrowsercache extends JPlugin
{
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onAfterRender() {
		if (file_exists(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php')) {
		 require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers' .DIRECTORY_SEPARATOR. 'mini.php');
		 $mem_limit = OPCmini::getMemLimit(); 
		 if (!empty($mem_limit)) {
		 $current_mem = memory_get_usage(); 
		 if (($current_mem / $mem_limit) > 0.7) return; 
		 }
		}
		$doc = JFactory::getDocument(); 
		$class = get_class($doc); 
		$class = strtoupper($class); 
		$input = JFactory::getApplication()->input;
		$format = $input->get('format', 'html'); 
		$tmpl = $input->get('tmpl', ''); 
		$arr = array('JDOCUMENTHTML', 'JOOMLA\CMS\DOCUMENT\HTMLDOCUMENT'); 
	    if (!in_array($class, $arr)) 
	    {
			return; 
			
		}
		
		$app = JFactory::getApplication();
		$name = $app->getName();
		if ($name !== 'site') {
			return; 
		}
		if (($tmpl === '') && ($format === 'html')) {
				$body = JResponse::getBody();
				/*
				$t2 = '<link\s+(?:[^>]*?\s+)?href="([^"]*)"'; 
				$t3 = '/(<link[^>]*href=\"([^>]+)\"*\>)|(<script[^>]*src=\"^()\"*\>)/im';
				$t3 = '/(<link[^>]*href=\"([^>]+)\")/im';
				*/
				$matches = array(); 
				$css = '/(<link[^>]*href=\"([^"]*)\")/im'; 
				$ret = preg_match_all($css, $body, $matches);
				//var_dump($matches); 
				$toSearch = array(); 
				$toRep = array(); 
				
				if ($ret) {
					$last = end($matches); 
					if (!empty($last))
					foreach ($last as $css) {
						if (empty($css)) continue; 
						$orig_path = (string)$css; 
						$p = $this->adjustUrlPath($css); 
						if ($p !== false) {
							$toSearch[] = '"'.$orig_path.'"'; 
							$toRep[] = '"'.$css.'"'; 
						}
						
					}
				}
				$js = '/(<script[^>]*src=\"([^"]*)\")/im';
				$ret = preg_match_all($js, $body, $matches);
				
				
				
				if ($ret) {
					$last = end($matches); 
					if (!empty($last))
					foreach ($last as $js) {
						if (empty($js)) continue; 
						$orig_path = (string)$js; 
						$p = $this->adjustUrlPath($js); 
						if ($p !== false) {
							$toSearch[] = '"'.$orig_path.'"'; 
							$toRep[] = '"'.$js.'"'; 
						}
						
					}
				}
				if (!empty($toSearch)) {
				 $body = str_replace($toSearch, $toRep, $body); 
				 JResponse::setBody($body);
				}
				
				
			}
	}
	
	//return false if not on our system
	private function adjustUrlPath(&$url) {
		$has_addon = false; 
		$url_test = $url; 
		//if (substr($url, 0, 2) === '//') return false; 
		
		$ignore_search = $this->params->get('ignore_search'); 
		if (!empty($ignore_search)) {
		if (strpos($ignore_search, ',') !== false) {
			$xa = explode(',', $ignore_search); 
			foreach ($xa as $s) {
				if (strpos($url, $s) !== false) return false; 
			}
		}
		else {
			if (strpos($url, $ignore_search) !== false) return false; 
		}
		}
		if (strpos($url, '?') !== false) {
			$ea = explode( '?', $url); 
			$url_test = $ea[0]; 
			$has_addon = true; 
			
			if (strpos($url, '&amp;') !== false) {
				$is_xhtml = true; 
			}
			else {
				$is_xhtml = false; 
			}
		}
		
		$root = array(); 

		$root[0] = Juri::root();  // https://domain.com/dir/
		$root[1] = str_replace('https:', '', $root[0]); 
		$root[2] = str_replace('http:', '', $root[0]); 
		$root[3] = Juri::root(true); //     /dir		
		foreach ($root as $testroot )  {
			
		if (substr($url_test, 0, strlen($testroot)) === $testroot) {
			if (substr($testroot, -1) !== '/') {
					$testroot .= '/'; 
				}
			if ($testroot === $url_test) return false; 
			//echo $url."\n"; 
			$path = JPATH_SITE.DIRECTORY_SEPARATOR.substr($url_test, strlen($testroot)); 
			//echo $path."\n"; 
			if (file_exists($path)) {
				$mtime = filemtime($path); 
				if ($mtime === false) return false; 
				$mtime = (string)$mtime; 
				//we already got it from elsewhere
				if (strpos($url, $mtime) !== false) return false; 
				if ($has_addon) {
					if ($is_xhtml) {
					  $url .= '&amp;m='.(int)$mtime; 
					}
					else {
						$url .= '&m='.(int)$mtime; 
					}
				}
				else {
					$url .= '?m='.(int)$mtime; 
				}
				return $path; 
			}
			
		}
		}
		/*
		else 
		{
			if (substr($url, 0, strlen($root))=== $root) {
				if (substr($root, -1) !== '/') {
					$root .= '/'; 
				}
				$path = JPATH_SITE.DIRECTORY_SEPARATOR.substr($url, strlen($root)); 
				if (file_exists($path)) return $path; 
			}
		}
		*/
		
		return false; 
		
	}
	
	
}