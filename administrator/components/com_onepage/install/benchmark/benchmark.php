<?php
//$x = debug_backtrace(); foreach ($x as $l) echo $l['file'].' '.$l['line']."<br />\n"; die(); 
/**
* @version		$Id: benchmark.php 1 stAn
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2012 RuposTel.com
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// create bench global 

jimport('joomla.application.menu');
jimport( 'joomla.plugin.plugin' );

class plgSystemBenchmark extends JPlugin
{
   public function onAfterRoute() {
	   if (php_sapi_name() === 'cli') {
			return; 
		}
   
   
      if (!isset($GLOBALS['bench_arr'])) return; 
      // second
	  $bench = array(); 
	  $bench['name'] = 'onAfterInitialise to onAfterRoute'; 
	  $bench['end'] = microtime(true); 
	  if (isset($GLOBALS['bench_arr']['global']['onAfterInitialise']))
	  $bench['duration'] = $bench['end'] - $GLOBALS['bench_arr']['global']['onAfterInitialise']['end']; 
	  
	  $GLOBALS['bench_arr']['global']['onAfterRoute'] = $bench; 

     
   
    $mainframe = JFactory::getApplication(); 
    
  
    if (!$mainframe->isAdmin())
    {
		 if(version_compare(JVERSION,'3.8.0','ge')) {
			 
		//include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'document.php'); 
		include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'j38'.DIRECTORY_SEPARATOR.'ModuleRenderer.php');		  
	  }
	  else
    if(version_compare(JVERSION,'3.1.0','ge')) {
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'document.php'); 
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'j35'.DIRECTORY_SEPARATOR.'module.php');
    return; 
    } 
    else
	if(version_compare(JVERSION,'2.5.0','ge')) {
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'document.php'); 
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'j25'.DIRECTORY_SEPARATOR.'module.php');
      // Joomla! 1.7 code here
    } 
	elseif(version_compare(JVERSION,'1.7.0','ge')) {
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'document.php'); 
	include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'j17'.DIRECTORY_SEPARATOR.'module.php');
// Joomla! 1.7 code here
} elseif(version_compare(JVERSION,'1.6.0','ge')) {
// Joomla! 1.6 code here
} else {
// Joomla! 1.5 code here
     include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'benchmark'.DIRECTORY_SEPARATOR.'module.php');
}
  
    }
   }

	

	
	
	function __construct(& $subject, $config)
	{
			
	  	parent::__construct($subject, $config);
	}

	
	function onAfterInitialise()
	{
		
		if (php_sapi_name() === 'cli') {
			return; 
		}
		
	if (!isset($GLOBALS['bench_arr'])) return; 
	$mainframe = JFactory::getApplication(); 
    if ($mainframe->isAdmin()) return;
	   
	   
	// $x = debug_backtrace(); 

	  // first
	  $bench = array(); 
	  $bench['name'] = 'start to onAfterInitialise'; 
	  $bench['end'] = microtime(true); 
	  $bench['duration'] = $bench['end'] - $GLOBALS['bench_arr']['global']['system']['start']; 
	  
	  $GLOBALS['bench_arr']['global']['onAfterInitialise'] = $bench; 
	  
	}
	
	function onAfterDispatch()
	{
		if (php_sapi_name() === 'cli') {
			return; 
		}
		
		if (!isset($GLOBALS['bench_arr'])) return; 
		$mainframe = JFactory::getApplication(); 
        if ($mainframe->isAdmin()) return;
		
	 // third
	  $bench = array(); 
	  $bench['name'] = 'onAfterRoute to onAfterDispatch'; 
	  $bench['end'] = microtime(true); 
	  $bench['duration'] = $bench['end'] - $GLOBALS['bench_arr']['global']['onAfterRoute']['end']; 
	  
	  $GLOBALS['bench_arr']['global']['onAfterDispatch'] = $bench; 

	}
	function _compare($a, $b)
	{
		if (!empty($a['all']))
		{
			$d1 = 0; 
			foreach ($a['all'] as $v)
			{
				$d1 += $v['duration']; 
			}
		}
		
		if (!empty($b['all']))
		{
			$d2 = 0; 
			foreach ($b['all'] as $v2)
			{
				$d2 += $v2['duration']; 
			}
		}
		
		if (empty($d1)) $d1 = $a['duration']; 
		if (empty($d2)) $d2 = $b['duration']; 
		
		if ($d1 < $d2) return 1; 
		if ($d1 > $d2) return -1; 
		if ($d1 === $d2) return 0; 
	}
	function _sumD($a)
	{
		if (!empty($a['all']))
		{
			$d1 = 0; 
			foreach ($a['all'] as $v)
			{
				$d1 += $v['duration']; 
			}
			return $d1; 
		}
		return $a['duration']; 
	}
	function iterateAll($arr=array())
	{
		
		$all = array(); 
		foreach ($GLOBALS['bench_arr'] as $typeO)
		foreach ($typeO as $k=>$v)
		{
			if (!empty($v['duration']))
			{
		
			//$v['duration'] = $v['duration']*1000; 
			$v['duration'] = $this->_sumD($v); 
			if (is_array($v)) {
			 unset($v['start']); 
			 unset($v['end']); 
			}
			//$d = number_format($v['duration'], 6).'ms'; 
			//$all[$v['name'].' '.$d] = $v; 
			$all[] = $v; 
			}
		}
		
		usort($all, array($this,'_compare')); 
		
		$newa = array(); 
		for($k=0; $k<count($all); $k++)
		{
		 $v = $all[$k]; 
	     $d = number_format($v['duration'], 6).'ms'; 
		 $newa[$k.':'.$v['name'].' '.$d] = $v; 
		}
		
		return $newa; 
	}
	function onAfterRender()
	{
		
		if (!empty($GLOBALS['bench_arr']['all_triggers']))
		foreach ($GLOBALS['bench_arr']['all_triggers'] as $k=>$v) {
			$GLOBALS['bench_arr']['all_triggers'][$k] = reset($v); 
		}
			
		
		if (php_sapi_name() === 'cli') {
			return; 
		}
		
		 if (!isset($GLOBALS['bench_arr'])) return; 
		$mainframe = JFactory::getApplication(); 
		if ($mainframe->isAdmin()) return;
	 // 4th
	  $bench = array(); 
	  $bench['name'] = 'onAfterDispatch to onAfterRender'; 
	  $bench['end'] = microtime(true); 
	  $bench['duration'] = $bench['end'] - $GLOBALS['bench_arr']['global']['onAfterDispatch']['end']; 
	  
	  $GLOBALS['bench_arr']['global']['onAfterRender'] = $bench; 
	
	  $GLOBALS['bench_arr']['global']['system']['name'] = 'system';
	  $GLOBALS['bench_arr']['global']['system']['end'] = microtime(true); 
	  $GLOBALS['bench_arr']['global']['system']['duration'] = $GLOBALS['bench_arr']['global']['system']['end'] - $GLOBALS['bench_arr']['global']['system']['start']; 

	  $GLOBALS['bench_arr']['all'] = $this->iterateAll(); 
	  
	  $db = JFactory::getDBO();
		//  fix  for support custom shutdown function via register_shutdown_function().
		$db->disconnect();
		$log = $db->getLog();
		$total = 0; 
		if ($log) {
		    $timings = $db->getTimings();
		    $nl = array();  
			$duplicates = array(); 
		   foreach ($log as $id => $q) {
			$queryTime = ($timings[$id * 2 + 1] - $timings[$id * 2]) * 1000;
			$nl[$id.'_'.$queryTime.'ms'] = $log[$id]; 
			$total += $queryTime;
		   if (isset($duplicates[$q])) {
		      $duplicates[$q] += (int)($queryTime * 1000); 
			}
		    else {
			$duplicates[$q] = (int)($queryTime * 1000); 
		   }
		   
		}
		}
		else {
			$nl = 'Enable system debug to see DB performance'; 
		}
		
		
		
		$d = array_flip($duplicates); 
		$d2 = array(); 
		foreach ($d as $ms =>$q) {
			$d2[$ms.'ns'] = $q; 
		}
		$d2['total'] = (int)$total.'ms'; 
		
	  
	  $GLOBALS['bench_arr']['DB'] = $nl; 
	  $GLOBALS['bench_arr']['QUERY_DUPLICATES'] = $d2;
	  $buffer = JResponse::getBody();
	  
	  $txt = ''; 
	  $rand = rand(); 
	  if (!empty($GLOBALS['bench_arr']))  {
		  $js = '<script language="javascript" type="text/javascript">//<![CDATA[ '; 
		  foreach ($GLOBALS['bench_arr'] as $k=>$data) {
			  foreach ($data as $kd => $kv) {
				  if (is_array($kv) && (isset($kv['duration']))) {
					  
					  $dur = $data[$kd]['duration'] * 1000;
					  
					  $test = $data[$kd]['duration']; 
					  if ($dur < 5)  //only relevant above 5ms
					  {
						 unset($data[$kd]); 
					  }
					  else {
						  
					  $data[$kd]['duration'] = number_format($dur, 2, '.', ' ').'ms';
					  
					  unset($data[$kd]['start']); 
					  unset($data[$kd]['end']); 
					  }
				  }
				  
			  }
	  $js .= '
	try
	{  
	'; 
	   if (defined('JSON_PRETTY_PRINT'))  {
	   $js .= " var _data_".$k.$rand." = ".json_encode($data, JSON_PRETTY_PRINT)."; "; 
	   $js .= ' console.log("'.$k.'",_data_'.$k.$rand.'); '; 
	   }
       else
	   {
	   $js .= " var _data_".$k.$rand." = '".str_replace("'", "\'", json_encode($data))."'; "; 
   
		   $js .= ' console.log('.$k.', JSON.parse(_data_'.$k.$rand.'));'."\n"; 
	   }
	$js .= ' }	catch (e)
	{
	   console.log(\'Error parsing data: \', e); 
	} '; 
	  
	 
		  }
		   $js .= '
	  //]]>
	</script>
	';
	  $txt = '<div style="display: none;" id="debug_benchmark">'.$txt.'</div>'.$js; 
	  $buffer = str_replace('</body', $txt.'</body', $buffer); 
	  JResponse::setBody($buffer);
	  }
	}

}