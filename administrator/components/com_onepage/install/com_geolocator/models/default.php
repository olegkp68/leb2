<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* GeoLocator is free software released under GNU/GPL  This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* This php file was create by www.rupostel.com team
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
jimport( 'joomla.filesystem.file' );


if(version_compare(JVERSION,'3.0.0','>=')) {
	class GeoModelExt extends JModelLegacy {
	
	}
}
else {
	if(version_compare(JVERSION,'3.0.0','<')) {
		class GeoModelExt extends JModel {
	
		}
	}
}
	
class DefaultModelDefault extends GeoModelExt {

    function __construct() {
		parent::__construct();
	}

    
    function save() { }
	
	function getName()
	{
	  return 'default';
	}

	function clean($url)
	{
	   /*
	   jimport('joomla.filesystem.folder'); 
	   jimport('joomla.filesystem.file'); 
	   JFolder::delete(JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'); 
	   $pa = pathinfo($url); 
	   if (!empty($pa['extension']))
	   $ext = $pa['extension']; 
	   else $ext = 'zip'; 
	 
	   $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata.'.$ext;
	   //if (file_exists($path)) JFile::delete($path);
	   */
	   
	   
	   $this->addIndexes(); 
	}

	
	
	function insert($from, $to) {
	$country_id_to_name = array(); 
	$county_id_to_iso = $this->getCountriesCsvData($country_id_to_name); 
	$db = JFactory::getDBO();
	$row = 0;
	$file = $this->getCsv(); 
	
	if ((empty($file)) || (!file_exists($file))) {
	   echo 'Extracted file not found !<br />'; 
	   return false;
	}
	$toInsRow = array(); 
	$is_new = false; 
	$rown = 0; 
	if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
		if ($rown === 0) {
			if (($data[0] === 'network') && ($data[1] === 'geoname_id')) {
				$is_new = true; 
				$rown++; 
				continue; 
			}
		}
		
		if ($is_new) {
				
				if (($row >= $from) && ($row <= $to)) {
				$network = $data[0]; 
				$geoname_id = (int)$data[1]; 
				if (!isset($county_id_to_iso[$geoname_id])) {
					throw new Exception($geoname_id.' not found in GeoLocation database, date may need an update'); 
				}
				$country_2_code = $county_id_to_iso[$geoname_id]; 
				
				
				$ipsrange = $this->cidrToRange($network); 
				
				$toIns = array(); 
				$toIns['geo_id'] = 'NULL';

				$toIns['ipstart'] = $ipsrange[0];
				$toIns['ipend'] = $ipsrange[1];
				$toIns['longstart'] = $this->getIP2Long($ipsrange[0]); 
				$toIns['longend'] = $this->getIP2Long($ipsrange[1]); 
				$toIns['country_2_code'] = $country_2_code;
				$toIns['country_name'] = $country_id_to_name[$geoname_id]; 
				$data = $toIns; 
				$toInsRow[] = $this->prepareIns($toIns); 
				}
		}
		else
		if ($num == 6)
		{
		if (($row >= $from) && ($row <= $to))
		{
		  if (!$this->insert2db($data)) 
		   {
		    //die('sql error');
		    return false;
		   }
        }
		else
		if ($row > $to) 
		 {
		   //echo 'End: '.$row.' of max '.$to; 
		   //if ($to > 200) 
		   //die('ok here');
		   break 1;
		 }
		}
		else die('Incorrect format');
		
		$row++;
		//if ($row > 200) echo $row. ' ';
		//echo $row.' '.$from.' '.$to; 
		//if ($row > 200) die('here');

		}
    }
	else 
	{
	  echo 'File error';
	  return false; 
	}
	
    fclose($handle);
	
	
	if (!empty($toInsRow)) {
		$db = JFactory::getDBO(); 
		$cols = array(); 
		foreach ($toIns as $key=>$dataX) {
			$cols[$key] = '`'.$db->escape($key).'`'; 
		}
		$q = "insert into `#__geodata` (".implode(',', $cols).") values "; 
		$q .= implode(',', $toInsRow); 
		$db->setQuery($q); 
		try { 
			$db->setQuery($q);
			$db->execute();
		}
		catch(Exception $e) {
			echo 'Failed to insert data !!! <br />'; 
		}
	}
	
	
	if (($data === false) && (empty($toInsRow)))
	 {
	  // terminator code :)
	  //die('-3');
	  return -3; 
	 }
	   //die('ok');
	  return true; 
	}
	
	public static function getIP2Long($ip)
  {
     $arr = explode('.', $ip); 
	 if (count($arr)<4) return 0;
	 $ipl = (16777216*$arr[0])+(65536*$arr[1])+(256*$arr[2])+$arr[3]; 
	 return $ipl;
  }
  
  function prepareIns($data) {
	  $ret = ''; 
	  $r = array(); 
	  $db = JFactory::getDBO(); 
	  foreach ($data as $ind=>$val) {
		  if (is_int($val)) {
			  $r[$ind] = (int)$val;
		  }
		  elseif ($val === 'NULL') {
			 $r[$ind] = 'NULL'; 
		  }
		  else {
		    $r[$ind] = "'".$db->escape($val)."'"; 
		  }
	  }
	  return '('.implode(',', $r).')'; 
  }
	
	function insert2db($data){

		$db = JFactory::getDBO();
		// structure:
		/*
		$ipstart = $data[0];
		$ipend = $data[1];
		$iplongstart = $data[2];
		$iplongend = $data[3];
		$country_2_code = $data[4];
		$country_name = $data[5];
		*/
		$q = "insert delayed into #__geodata (`geo_id`, `ipstart`, `ipend`, `longstart`, `longend`, `country_2_code`, `country_name`) values (NULL, '".$db->escape($data[0])."', '".$db->escape($data[1])."', '".$db->escape($data[2])."', '".$db->escape($data[3])."', '".$db->escape($data[4])."', '".$db->escape($data[5])."') ";
		try { 
		$db->setQuery($q);

		if (! $db->execute()) {
			echo $q;
			return false;
		}
		}
		catch (Exception $e) {
			echo 'Query failed: '.var_export($data, true).'<br />'.$q.'<br />'; 
		}
		return true;
	}
	function getCountriesCsvData(&$country_id_to_name) {
		$country_id_to_name = array(); 
		
		$dir = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'; 
		$files = $this->directory_list($dir, true, false, ".|..|.DS_Store|.svn", true); 
		foreach ($files as $d => $f) {
			if (is_string($f)) {
			$pa = pathinfo($f); 
			if ($pa['basename'] === 'GeoLite2-Country-Blocks-IPv4.csv') {
			 $country_data = $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR.$f; 
			}
			}
			if (is_array($f)) {
				foreach ($f as $d2 => $f2) {
				
				$pa = pathinfo($f2); 
			if ($pa['basename'] === 'GeoLite2-Country-Locations-en.csv') {
			 $country_data = $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR.$f2; 
			
			}
			}
			}
		}
		
		
	   
	   
	   
	   $row = 0; 
	   if (!empty($country_data)) {
		   if (($handle = fopen($country_data, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
				if ($row = 0) {
					$row++; 
					continue; 
				}
			$ID = (int)$data[0]; 
			$ISO = $data[4]; 
			
        $country_id_to_iso[$ID] = $ISO; 
		$country_id_to_name[$ID] = $data[5]; 
		   
	   }
		   }
	   }
	   
	   return $country_id_to_iso; 
	   
	}
	//https://stackoverflow.com/questions/4931721/getting-list-ips-from-cidr-notation-in-php
	function cidrToRange($cidr) {
  $range = array();
  $cidr = explode('/', $cidr);
  $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
  $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
  return $range;
}
function directory_list($directory_base_path, $filter_dir = false, $filter_files = false, $exclude = ".|..|.DS_Store|.svn", $recursive = true){
    $directory_base_path = rtrim($directory_base_path, "/") . "/";

    if (!is_dir($directory_base_path)){
        error_log(__FUNCTION__ . "File at: $directory_base_path is not a directory.");
        return false;
    }

    $result_list = array();
    $exclude_array = explode("|", $exclude);

    if (!$folder_handle = opendir($directory_base_path)) {
       // error_log(__FUNCTION__ . "Could not open directory at: $directory_base_path");
        return false;
    }else{
        while(false !== ($filename = readdir($folder_handle))) {
            if(!in_array($filename, $exclude_array)) {
                if(is_dir($directory_base_path . $filename . "/")) {
                    if($recursive && strcmp($filename, ".")!=0 && strcmp($filename, "..")!=0 ){ // prevent infinite recursion
                        //error_log($directory_base_path . $filename . "/");
                        $result_list[$filename] = $this->directory_list("$directory_base_path$filename/", $filter_dir, $filter_files, $exclude, $recursive);
                    }elseif(!$filter_dir){
                        $result_list[] = $filename;
                    }
                }elseif(!$filter_files){
                    $result_list[] = $filename;
                }
            }
        }
        closedir($folder_handle);
        return $result_list;
    }
}
	
	
	function getCsv() {
		$dir = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'; 
		$files = $this->directory_list($dir, true, false, ".|..|.DS_Store|.svn", true); 
		foreach ($files as $d => $f) {
			if (is_string($f)) {
			$pa = pathinfo($f); 
			if ($pa['basename'] === 'GeoLite2-Country-Blocks-IPv4.csv') {
			 return $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR.$f; 
			}
			}
			if (is_array($f)) {
				foreach ($f as $d2 => $f2) {
				
				$pa = pathinfo($f2); 
			if ($pa['basename'] === 'GeoLite2-Country-Blocks-IPv4.csv') {
			 return $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR.$f2; 
			
			}
			}
			}
		}
	    

		//old format: 
	  $files = scandir($dir); 
	  foreach ($files as $fi)
	   {
	     $pa = pathinfo($fi); 
		 if ((!empty($pa['extension'])) && ($pa['extension'] == 'csv')) {
			 return JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'.DIRECTORY_SEPARATOR.$fi; 
		 }
		
		 if ($pa['basename'] === 'GeoLite2-Country-Blocks-IPv4.csv') {
			 return $fi; 
		 }
	   }
	   
	   
	 
	   return ""; 
	}
	
	function extract($url)
	{
	
	 $lf = JRequest::getBool('localfile', false); 
	 if ($lf)
	 {
	   $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'GeoIPCountryCSV.zip' ;
	 }
	 if (!file_exists($path))
	 {
	  $pa = pathinfo($url); 
	  if (!empty($pa['extension']))
	  $ext = $pa['extension']; 
	  else $ext = 'zip'; 
	 
	   if (strpos($url, '.tar.gz') !== false) $ext = 'tar.gz'; 
	 
	   $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata.'.$ext;
	  }
	  
	 
	 
	  
	   echo 'Extracting: '.$path.'<br />'; 
	   if (!file_exists($path)) 
	   {
	   echo 'File does not exists!<br />'; 
	   return false;
	   }
	 
	   $zz = filesize($path); 
	   
	     
	   if ($zz < 5000) {
		   $test = file_get_contents($path); 
		   if (stripos($test, 'Invalid') !== false) {
			   echo '<b>'.$test.'</b>'."<br />"; 
			   JFile::delete($path); 
			   return false; 
		   }
	   }
	   
	   $dest = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'.DIRECTORY_SEPARATOR.'geodata.csv'; 
	   
	   jimport('joomla.filesystem.archive'); 
	   jimport('joomla.filesystem.jfolder'); 
	   $fold = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata'.DIRECTORY_SEPARATOR; 
	   @JFolder::create($fold); 
	   
	   if(version_compare(JVERSION,'3.0.0','>=')) {
		   
		   try {
	   $res = @JArchive::extract($path, $fold); 
		   }
		   catch(Exception $e) {
			   
			    echo $e->getMessage(); ; 
				return false;
		   }
	   }
	   else {
		   
		   if ($pa['extension'] === 'zip') {
			   
			   
			   if (class_exists('ZipArchive')) {
			   $zip = new ZipArchive;
			   $res = $zip->open($path);
			   if ($res === TRUE) {
			   $res = $zip->extractTo($fold);
			   
			   
			   $zip->close();
			   }
			   }
			   
		   }
		   else {
		   if (class_exists('PharData')) {
		   $p = new PharData($path);
		   $p->decompress();
		   if (strpos($path, '.tar.gz.' !== false)) {
		    
		   }

		// unarchive from the tar
		if (file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata.tar')) {
			$phar = new PharData(JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata.tar');
			$res = $phar->extractTo($fold);
		}
		else {
			$res = $p->extractTo($fold);
		}
		   }
		
		
	   }
	   }
	   
	   
	   
	   
	   if ($res === true) 
	   {
		   
		   
	    $db = JFactory::getDBO();
	    if (!$this->tableExists('geodata'))
		 {
			 $this->createTable();
			 //return false; 

		 }
		 else
		 {
		   echo 'Clearing previous data <br />'; 
		   $q = 'delete from #__geodata where 1 limit 99999999 ';
		   $db->setQuery($q); 
		   $db->execute(); 
		   $q = 'ALTER TABLE #__geodata AUTO_INCREMENT = 1;'; 
		   $db->setQuery($q); 
		   $db->execute(); 
		   
		   if (!empty($err)) 
		    {
			//echo $err;
			return false; 
			}
		 }
	    return true; 
	   }
	   
	   return false;
	  
	}

	
	function addIndexes()
	{
	   $q = 'ALTER TABLE `#__geodata` ADD UNIQUE KEY `iprange` (`longstart`,`longend`), ADD UNIQUE KEY `longstart` (`longstart`), ADD UNIQUE KEY `longend` (`longend`);'; 
	   $db = JFactory::getDBO(); 
	   $db->setQuery($q); 
	   try
	   {
	     $db->execute(); 
	   }
	   catch (Exception $e)
	   {
	     // once we run this twice... 
	   }
	   
	}
	function createTable(){
		$q = 'CREATE TABLE IF NOT EXISTS `#__geodata` (
				`geo_id` bigint(20) NOT NULL auto_increment,
				`ipstart` varchar(39) NOT NULL,
				`ipend` varchar(39) NOT NULL,
				`longstart` bigint(20) NOT NULL,
				`longend` bigint(20) NOT NULL,
				`country_2_code` varchar(3) NOT NULL,
				`country_name` varchar(255) NOT NULL,
				PRIMARY KEY  (`geo_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->execute();

	}

	function download($url)
	{
	  $pa = pathinfo($url); 
	  if (!empty($pa['extension']))
	  $ext = $pa['extension']; 
	  else $ext = 'zip'; 
	  if (strpos($url, '.tar.gz') !== false) $ext = 'tar.gz'; 
	  
      $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'geodata.'.$ext; 
	  if (file_exists($path)) {
		  //do not download again:
		  echo 'File was already downloaded, remove from tmp directory if you want to redownload <br />'; 
		  return true; 
	  }
 
     $fp = fopen($path, 'w');
	 if ($fp === false) 
	  {
	    $inmem = true; 
		$fp = ''; 
	  }
     $ch = curl_init($url);
	 
	 if (empty($inmem))
     curl_setopt($ch, CURLOPT_FILE, $fp);
	 else curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 
     $data = curl_exec($ch);
	 $err = curl_errno($ch); 
	 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
     curl_close($ch);
	 
	 
	 
	 
	 if (empty($inmem))
     fclose($fp);
	 else 
	  {
	    jimport('joomla.filesystem.file'); 
		$res = @JFile::write($path, $fp); 
		if (empty($res)) return false;
	  }
	  
	  if (!empty($err)) return false;
	 
	 if ($httpcode !== 200) {
		 echo 'Error: HTTP Status: '.(int)$httpcode.' for '.$url."<br />"; 
		 
		 if (empty($inmem))
		 {
			unlink($fp); 
		 }
		 else {
			 JFile::delete($fp); 
		 }
		 
		 return false; 
	 }
	  
	  //die('here');
	  return true; 
	}
    function tableExists($table)
{
 $db = JFactory::getDBO();
 $prefix = $db->getPrefix();
 $table = str_replace('#__', '', $table); 
 $table = str_replace($prefix, '', $table); 
 
  $q = "SHOW TABLES LIKE '".$db->getPrefix().$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   if (!empty($r)) return true;
 return false;
}

    
    
     
}