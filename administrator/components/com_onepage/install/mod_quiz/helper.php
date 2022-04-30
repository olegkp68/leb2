<?php
class ModQuizHelper {
	
	public static $products; 
	
	//if return is set to true, then com_rupsearch helper returns even zero values
	public static function getProducts($keyword='', $prods=100, $popup=false, $order_by='ordering', &$return_empty=false) {
		$redirect = false; 
		
		$session = JFactory::getSession(); 
		$statmodel = JRequest::getVar('q_model', $session->get('q_model', '_') ); 
		$statbrand = JRequest::getVar('q_brand', $session->get('q_brand', '_') ); 
		self::updateStats($statmodel, $statbrand); 
		
		$email = JRequest::getVar('ce', ''); 
		if (!empty($email)) {
			$model = JRequest::getVar('q_model', '' ); 
			$brand = JRequest::getVar('q_brand', '' ); 
			if ((!empty($model)) && (!empty($brand))) {
				static $done; 
				if (empty($done)) {
				 self::processEmail($email, $brand, $model); 
				 $done = true; 
				 $return_empty = true; 
				 
				
				}
				
				 $redirect = true; 
				
			}
		}
		
		
		$cleardata = JRequest::getVar('empty', 0); 

		if (!empty($cleardata)) {
	
			$session->set('q_brand', ''); 
			$session->set('q_model', ''); 
			self::$products = null;
			JRequest::setVar('q_brand', ''); 
			JRequest::setVar('q_model', ''); 
			if (empty($email)) {
			 $return_empty = false; 
			}
			
			if ($redirect) {
			
			$redirectto = JRequest::getVar('redirectto', ''); 
			if (!empty($redirectto)) {
				$redirectto = base64_decode($redirectto); 
				
				JFactory::getApplication()->redirect($redirectto); 
				JFactory::getApplication()->close(); 
			}
			
			$Itemid = (int)JRequest::getVar('Itemid', 0); 
		if (!empty($Itemid)) {
			
			JFactory::getApplication()->redirect(JRoute::_('index.php?Itemid='.$Itemid.'&empty=1')); 
			JFactory::getApplication()->close(); 
		}
		}
			
			return array(); 
		} 
		$model = JRequest::getVar('q_model', $session->get('q_model', '_') ); 
		$brand = JRequest::getVar('q_brand', $session->get('q_brand', '_') ); 
		$currentData = ModQuizHelper::loadData(); 
		
		
		
		
		if (isset($currentData[$brand])) {
			
			$session->set('q_brand', $brand); 
		}
		
		if (isset($currentData[$brand][$model])) {
			$session->set('q_model', $model); 
			self::$products = $currentData[$brand][$model]; 
			$return_empty = true; 
			return $currentData[$brand][$model]; 
		}
		
		if ($brand === '_') $brand = ''; 
		if ($model === '_') $model = ''; 
		
		
		
		self::$products = array(); 
		
		if (!empty($model) && (!empty($brand))) {
			
			//we have some stored state here, lets load it: 
			$return_empty = true;
	
		}
		else {
			self::$products = null; 
		}
		
		
		return array(); 
		
	}
	
	public static function updateStats($model, $brand) {
		$session = JFactory::getSession(); 
		
		
		if ($brand === '_') return; 
		if (empty($brand)) return; 
		if ($model === '_') $model = ''; 
		
		
		$test = $session->get('stats_'.$brand.'_'.$model, 0); 
		if (empty($test)) {
		$db = JFactory::getDBO(); 
		$q = "select * from #__quiz_stats where `brand` = '".$db->escape($brand)."' and `model` = ''"; 
		$db->setQuery($q); 
		$brand_row = $db->loadAssoc(); 
		if (empty($brand_row)) {
			$q = "insert into #__quiz_stats (`id`, `brand`, `model`, `num`) values (NULL, '".$db->escape($brand)."', '', 1)"; 
			$db->setQuery($q); 
			$db->query(); 
		}
		else {
			$id = $brand_row['id']; 
			$q = 'update #__quiz_stats set `num` = `num` +1 where `id` = '.(int)$id; 
			$db->setQuery($q); 
			$db->query(); 
		}
		if (!empty($model)) {
		$q = "select * from #__quiz_stats where `brand` = '".$db->escape($brand)."' and `model` = '".$db->escape($model)."'"; 
		$db->setQuery($q); 
		$brand_row = $db->loadAssoc(); 
		if (empty($brand_row)) {
			$q = "insert into #__quiz_stats (`id`, `brand`, `model`, `num`) values (NULL, '".$db->escape($brand)."', '".$db->escape($model)."', 1)"; 
			$db->setQuery($q); 
			$db->query(); 
		}
		else {
			$id = $brand_row['id']; 
			$q = 'update #__quiz_stats set `num` = `num` +1 where `id` = '.(int)$id; 
			$db->setQuery($q); 
			$db->query(); 
		}
		}
		  $session->set('stats_'.$brand.'_'.$model, 1); 
		}
		
		
	}
	
	public static function createTable() {
		$db  = JFactory::getDBO(); 
		$q = 'CREATE TABLE IF NOT EXISTS `#__quiz_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `brand` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `model` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `current_cart` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'; 
$db->setQuery($q); 
$db->execute(); 

$q = 'CREATE TABLE IF NOT EXISTS `#__quiz_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(500) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
  `model` varchar(500) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `num` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `brand` (`brand`),
  KEY `model` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'; 
$db->setQuery($q); 
$db->execute(); 
	}
	public static function processEmail($email, $brand, $model) {
		self::createTable(); 
		self::loadVM(); 
		$db = JFactory::getDBO(); 
		$q = 'insert into #__quiz_data (`id`, `email`, `brand`, `model`, `created_on`, `current_cart`) values (NULL, '; 
		
		$cart = VirtuemartCart::getCart(); 
		$cart->prepareAjaxData();
		$cart_content = ''; 
		if (!empty($cart->products)) {
			foreach ($cart->products as $p) {
				$cart_content .= $p->product_name.' x '.$p->quantity.'; '; 
			}
		}
		$q .= "'".$db->escape($email)."', '".$db->escape($brand)."', '".$db->escape($model)."', NOW(), '".$db->escape($cart_content)."')"; 
		$db->setQuery($q); 
		$db->execute(); 
		$fn = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if (file_exists($fn)) {
		
		require_once($fn);
		
		$listname = $brand.' - '.$model; 
		$q = 'select * from #__acymailing_list where name = \''.$db->escape($listname).'\''; 
		$db->setQuery($q); 
		$res = $db->loadAssoc(); 
		if (empty($res)) {
			$maxq = 'select max(ordering) + 1 from #__acymailing_list where 1=1'; 
			$db->setQuery($maxq); 
			$max = (int)$db->loadResult(); 
			$max++; 
			
			$q = "insert into #__acymailing_list (`name`, `description`, `ordering`, `published`, `color`) values ('".$db->escape($listname)."', 'Quiz Data', ".(int)$max.", 1, '#000000')"; 
		    $db->setQuery($q); 
			$db->execute(); 
			
			$q = 'select * from #__acymailing_list where `name` = \''.$db->escape($listname).'\''; 
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
		}
		
		if (!empty($res['listid'])) {
			$id = (int)$res['listid']; 
			
			
			
			$userClass = acymailing_get('class.subscriber');
			$userClass->subid($email);
			if(!acymailing_isAdmin()) $userClass->geolocRight = true;
			$userClass->checkVisitor = false;
			$userClass->sendConf = false;
			$userClass->triggerFilterBE = true;
			
			$joomUser = new stdClass();
			$joomUser->email = trim(strip_tags($email));
			$joomUser->confirmed = 1;
			$joomUser->name = trim(strip_tags($email));
			$joomUser->enabled = 1;
			$joomUser->userid = 0;
			$joomUser->subid = null;
			acymailing_setVar('acy_source', 'quiz');
			
			$subid = $userClass->save($joomUser);
			$newlists = array();
			$newlists['status'] = 1;
			$formLists = array(); 
			$formLists[$id] = $newlists;
			$userClass->saveSubscription($subid, $formLists);
			$userClass->confirmSubscription($subid);
			
			
		}
		}
		
		return; 
		self::sendMail($email, $brand, $model, $cart_content); 
		
		
	}
	
	private static function loadVM() {
		
		if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');


VmConfig::loadConfig();


if (!class_exists('VmView'))
	require(JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmview.php');

if (!class_exists('shopFunctionsF'))
require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');


if (!class_exists('CurrencyDisplay'))
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');

if (!class_exists( 'VirtuemartCart' )) require(JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');

	}
	
	public static function getAjax() {
		if (!self::_checkPerm()) {
			echo 'Access denied to this feature'; 
			JFactory::getApplication()->close(); 
		}
		if (!self::_checkOPC()) {
			echo 'This feature requires com_onepage core classes to be available in /components/com_onepage'; 
			JFactory::getApplication()->close(); 
		}
		
		
		ob_start(); 
		$post = JRequest::get('post'); 
		$cmd = JRequest::getWord('cmd'); 
		
		$checkstatus = JRequest::getVar('checkstatus', null); 
		if (!empty($checkstatus)) $cmd .= 'status'; 
		
		if (method_exists('ModQuizHelper', 'cmd'.$cmd)) {
			$funct = 'cmd'.$cmd; 
		    call_user_func(array('ModQuizHelper', $funct), $post); 
		}
		else {
			self::_die('Command not found: cmd'.$cmd); 
		}
		$html = ob_get_clean(); 
		
		@header('Content-Type: text/html; charset=utf-8');
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		echo $html; 
		JFactory::getApplication()->close(); 
		
	}
	
	private static function sendMail($email, $brand, $model, $cartcontent) {
		$lang = JFactory::getLanguage(); 
		$lang->load('mod_quiz', JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_quiz'.DIRECTORY_SEPARATOR); 
		//$lang->load('mod_quiz'); 
		$subject = JText::_('MOD_QUIZ_EMAIL_SUBJECT'); 
		
		//MOD_QUIZ_EMAIL_SUBJECT="Missing items for {brand} {model}"
		//MOD_QUIZ_EMAIL="Brand: {brand}<br />Model: {model}<br /><br />Email: {email}<br />Cart content{cart}"
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$sender = array( 
			$config->get( 'mailfrom' ),
			$config->get( 'fromname' ) 
		);
		
		$mailer->addRecipient(array($config->get( 'mailfrom' ) ));
		$mailer->addRecipient(array('info@rupostel.com') );
		
		$mailer->addReplyTo(array($email)); 
		$mailer->setSender($sender);
		
		$subject = JText::_('MOD_QUIZ_EMAIL_SUBJECT'); 
		$subject = str_replace(array('{model}', '{brand}', '{email}', '{cart}'), array($model, $brand, $email, $cartcontent), $subject);
		$body = JText::_('MOD_QUIZ_EMAIL'); 
		$body = str_replace(array('{model}', '{brand}', '{email}', '{cart}'), array($model, $brand, $email, $cartcontent), $body);
		
		$mailer->setSubject($subject);
		
		$mailer->isHtml(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);
		$send = $mailer->Send();
		
	}
	
	private function cmduploadxls($post) {
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$fileName = $_FILES['file']['name'];
		$fileTemp = $_FILES['file']['tmp_name'];
		
		if (!self::_checkPerm()) {
			JFile::delete($fileTemp); 
			return;
		}
		
		
		$dest = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'quiz.xlsx';
		if (JFile::upload($fileTemp, $dest)) {
			self::_green('OK, file was uploaded OK'); 
		}
		@JFile::delete($fileTemp); 
		return self::_red('Error !'); 
		
		
		
	}
		private function cmduploaddatastatus() {
		echo 'Ready... '; 
		JFactory::getApplication()->close(); 
	}
	public static function loadData($tolowercase=false) {
		
		static $brands; 
		if (!empty($brands)) return $brands; 
		$currentData = self::loadXLS(); 
		
		if (empty($brands)) {
		$brands = array(); 
		}
		foreach ($currentData as $row) {
			$brand = $row['brand']; 
			if (empty($brand)) continue; 
			$model = $row['model']; 
			
			
			$brand = trim($brand); 
			$model = trim($model); 
			$bcd = trim($row['bcd']); 
			
			if ($tolowercase) {
				$brand = mb_strtolower($brand); 
				$model = mb_strtolower($model); 
				$bcd = mb_strtolower($bcd); 
			}
			
			//if (empty($model)) $model = '_'; 
			
			//if (empty($bcd)) $bcd = '_'; 
			if (!empty($bcd)) {
				$model_bcd = trim($model.' '.$bcd); 
			}
			else {
				$model_bcd = trim($model); 
			}
			
			$products = $row['products']; 
			if (empty($products)) $products = array(); 
			
			if (empty($brands[$brand])) {
				$brands[$brand] = array(); 
			}
			if (empty($brands[$brand][$model])) {
			 $brands[$brand][$model_bcd] = array(); 
			}
			
			$brands[$brand][$model_bcd] = $products; 
			
		}
		
		return $brands; 
	}
	public static function loadXLS() {
		
		self::_getPHPExcel(); 
		
				
		// Create new PHPExcel object
		$src = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'quiz.xlsx';
		if (!file_exists($src)) return array(); 
		
		$reader = PHPExcel_IOFactory::createReaderForFile($src); 
		$reader->setReadDataOnly(true);
		$objXLS = $reader->load($src);
		$value = $objXLS->getSheet(0)->getCell('A1')->getValue();
		$sheet = $objXLS->getSheet(0); //->getCellByColumnAndRow(0, 1);
		$rows = $sheet->getHighestRow();
		$rows = (int)$rows; 
		$data = array(); 
		for ($row=2; $row<=$rows; $row++) {
			
			
			
			
			
			$line = array(); 
			for ($i=0; $i<=3; $i++) {
			  $val = $sheet->getCellByColumnAndRow($i,$row)->getValue(); 
			  
			  //echo $i.'_'.$row.'_'.$val."<br />\n"; 
			  
			  switch ($i) {
				  case 0: 
					$line['brand'] = trim($val); 
				  case 1: 
					$line['model'] = trim($val); 
				  case 2: 
					$line['bcd'] = $val; 
				  case 3: 
					$line['products'] = self::_parseCommas($val); 
				  
				  
				 
			  }
			 
			  
			 
			  
			}
			 if (!empty($line['brand'])) {
				  $data[] = $line; 
			  }
			
			
			  
			 
		}
		$objXLS->disconnectWorksheets();
		
		$reader = null; 
		$objXLS = null; 
		
		unset($reader);
		unset($objXLS);
		
		
		
		return $data; 
		
		
	}
	
	private static function _parseCommas($data) {
		$data = trim($data); 
		$data = str_replace('.', ',', $data); //excel int tranform
		$data = str_replace(array(' ', "\r\r\n", "\r\n", "'", "\n"), array('', ',', ',', '', ','), $data);
		if (strpos($data, ',') === false) {
			$i = (int)$data; 
			if (!empty($i)) return array($i=>$i); 
			return array(); 
		}
		$ae = explode(',', $data); 
		$ret = array(); 
		foreach ($ae as $i) {
			$i = trim($i); 
			$i = (int)$i; 
			if (!empty($i)) {
				$ret[$i] = $i; 
			}
		}
		return $ret; 
	}
	
	private function cmddownloaddatastatus() {
		echo 'Click the button to download the data... '; 
		JFactory::getApplication()->close(); 
	}
	
	private function cmddownloadstatsstatus() {
		echo 'Click the button to download the data... '; 
		JFactory::getApplication()->close(); 
	}
	
	
	private function cmddownloaddata($post) {
		
		$post = JRequest::get('get'); 
		$src = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'quiz.xlsx';
		if (!file_exists($src)) {
			return self::_red('File does not exists !'); 
		}
		/*$buf = @ob_get_clean(); $buf = @ob_get_clean(); $buf = @ob_get_clean(); $buf = @ob_get_clean(); $buf = @ob_get_clean(); 
		ob_start(); */
	   

	@header('Content-Type: application/vnd.ms-excel');
	@header('Content-Disposition: attachment;filename="quiz_'.date('m-d-Y_hia').'.xlsx"');
	@header('Cache-Control: max-age=0');
	copy($src, 'php://output'); 
	/*
	$data = file_get_contents($src); 
	echo $data; 
	*/
	//file_put_contents('php://output', $data); 
	 //copy($src, 'php://output'); 
	flush(); 
	JFactory::getApplication()->close(); 
		
	}
	
	private function cmddownloadstats($post) {
		self::_getPHPExcel(); 
		$currentData = ModQuizHelper::loadData(true); 
		//$currentRealData = ModQuizHelper::loadData(); 
		
		
		
				
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("RuposTel Systems")
							 ->setLastModifiedBy("RuposTel Systems")
							 ->setTitle("Quiz Stats")
							 ->setSubject("Quiz")
							 ->setDescription("Quiz Statistics")
							 ->setKeywords("orders, virtuemart, eshop")
							 ->setCategory("Quiz");
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
		
		$db = JFactory::getDBO(); 
		$q = 'select `brand`,`model`, `num` from `#__quiz_stats` where `model` <> "" order by `num` desc'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		/*
		{
			foreach ($currentData as $brand => $xx) {
				foreach ($xx as $model => $yy) {
					$nr = array(); 
					$nr['brand'] = $brand; 
					$nr['model'] = $model; 
					$nr['num'] = 'list'; 
					$nr['no_products'] = count($yy); 
					$res[] = $nr; 
				}
			}
		}
		*/
		
		if (!empty($res))
		foreach ($res as $ind=>$row) {
			
			
				$is_empty = ''; 
				if (!empty($row['model']) && (!empty($row['brand']))) 
				{
					$brand = trim($row['brand']); 
					$model = trim($row['model']); 
					
					$brand = mb_strtolower($brand); 
					$model = mb_strtolower($model); 
					
				if (empty($currentData[$brand][$model])) {
					$is_empty = 'X'; 
				}
				}
				
			$row['no_products'] = $is_empty; 
			
			
			$rown_n = $ind+2; 
			
			
			$i = 0; 
			foreach ($row as $key=>$val) {
				if (empty($header_done)) {
				
				$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, 1, $key);
				
				}
			
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, $rown_n, $val);
			
			 $i++; 
			}
			$header_done = true; 
			
			
			
		}
		
		
		
		/*
			@header('Content-Type: application/vnd.ms-excel');
			@header('Content-Disposition: attachment;filename="quiz_stats_'.date('m-d-Y_hia').'.csv"');
			@header('Cache-Control: max-age=0');
		
		if (!empty($res)) {
			foreach ($res as $row) {
				$is_empty = '""'; 
				if (!empty($row['model']) && (!empty($row['brand'])))
				if (empty($currentData[$row['brand']][$row['model']])) {
					$is_empty = '"X"'; 
				}
				echo '"'.str_replace('"', '\"', $row['brand']).'","'.str_replace('"', '\"', $row['model']).'",'.(int)$row['num'].",".$is_empty."\n"; 
			}
		}
	   */
	   
	   	


// Iterating all the sheets
/** @var PHPExcel_Worksheet $sheet */
foreach ($objPHPExcel->getAllSheets() as $sheet) {
    // Iterating through all the columns
    // The after Z column problem is solved by using numeric columns; thanks to the columnIndexFromString method
    for ($col = 0; $col <= PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn()); $col++) {
        $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
    }
}

	$objPHPExcel->getActiveSheet()->setTitle('Quiz Stats');
	$objPHPExcel->setActiveSheetIndex(0);
	

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output'); 


	unset($objWriter); 
	$objWriter = null; 

	@header('Content-Type: application/vnd.ms-excel');
	@header('Content-Disposition: attachment;filename="quiz_stats_'.date('m-d-Y_hia').'.xlsx"');
	@header('Cache-Control: max-age=0');
	flush(); 
	JFactory::getApplication()->close(); 

	
		
		
	}
	
	private static function _getPHPExcel() {
		@ini_set("memory_limit",'32G');
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) 
			self::_die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel<br />Install via RuposTel One Page Checkout -> OPC Order Manager -> Excell Export -> Download and Install');
		
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');
	}
	
	private static function cmduploadxlsstatus($post) {
		$msg = 'OK, everything is at it\'s place'; 
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'quiz.xlsx')) {
			$msg = DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'quiz.xlsx does not yet exists'; 
			return self::_red($msg); 
		}
		return self::_green($msg); 
		
	}
	
	private static function _green($msg) {
		
		return self::_die('<b style="color:green;"><i class="fa fa-check"></i> '.$msg."</b><br />\n"); ;
		
	}
	private static function _red($msg) {
		return self::_die('<b style="color:red;"><i class="fa fa-times"> '.$msg."</b><br />\n"); ;
	}
	
	private static function _die($msg) {
		echo $msg; 
		JFactory::getApplication()->close(); 
	}
	
	private static  function _checkPerm() {
	  //we send a hash from BE and validate it agains FE
	  $hash = JApplicationHelper::getHash('opc ajax search');
	  
	  $hash_request = JRequest::getVar('hash', ''); 
	  if ($hash === $hash_request) return true; 
	  
	  
	  return false; 
   }
   
   private static function _checkOPC() {
	   if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	     require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		 return true; 
	   }
	   return false; 
   }
   
   private static function cmdproduct_sku_index() {
		echo self::createIndex('product_sku'); 
   }
   private static function cmdproduct_sku_indexstatus() {
	  echo self::checkIndex('product_sku'); 
   }
   
    private static function cmdproduct_customs_index() {
		echo self::createIndex('customfield_value', '#__virtuemart_product_customfields'); 
   }
   private static function cmdproduct_customs_indexstatus() {
	  echo self::checkIndex('customfield_value', '#__virtuemart_product_customfields');
	  /*
	  echo '<br />Some MySQL versions are limited to a maximum length of an indexed column to varchar(255) for utf8mb3 or varchar(191) for utf8mb4. '; 
	  $db = JFactory::getDBO(); 
	  $q = 'select length(customfield_value) as `len` from #__virtuemart_product_customfields order by `len` desc limit 1'; 
	  $db->setQuery($q); 
	  $l = $db->loadResult(); 
	  echo ' Your maximum length of a column value is '.(int)$l.'. You can adjust column definition in your phpMyAdmin, but lowering it to a lower than maximum length will cause lost of data.'; 
	  */
   }
   
   
    private static function cmdproduct_mpn_index() {
		echo self::createIndex('product_mpn'); 
   }
   private static function cmdproduct_mpn_indexstatus() {
	  echo self::checkIndex('product_mpn'); 
   }
    private static function cmdproduct_gtin_index() {
		echo self::createIndex('product_gtin'); 
   }
   private static function cmdproduct_gtin_indexstatus() {
	  echo self::checkIndex('product_gtin'); 
   }
   
   private static function createIndex($index_name, $table='#__virtuemart_products') {
		
		
		if (!OPCmini::hasIndex('#__virtuemart_products', $index_name, true)) {
			
			  $msg = OPCmini::addIndex($table, array($index_name), true); 
			if (!empty($msg)) {
				$msg = '<span style="color:red;">Failed to create Unique index</span>'; 
				if (!OPCmini::hasIndex($table, $index_name, false)) {
				$e = OPCmini::addIndex($table, array($index_name), false); 
				if (!empty($e)) {
						  $db = JFactory::getDBO(); 
						  $q = 'select length('.$index_name.') as `len` from `'.$table.'` order by `len` desc limit 1'; 
						  $db->setQuery($q); 
						  $l = $db->loadResult(); 
						  $msg .= ' Your maximum length of '.$index_name.' column value is '.(int)$l.'. You can adjust column definition in your phpMyAdmin, but lowering it to a lower than maximum length will cause lost of data.'; 
						  $msg .= '<br />'.$e; 
				}
				$msg .= '<br /><span style="color:green;">Non-unique Index created</span>'; 
				}
				else {
					$msg = '<span style="color:green;">Index already exists</span>'; 
				}
			}
			//$msg = '<span style="color:green;">Index already exists</span>'; 
		}
		else {
			$msg = '<span style="color:green;">Index already exists</span>'; 
		}
		
		return $msg; 
		
   }
   private static function checkIndex($index_col, $table='#__virtuemart_products') {
	  if (!OPCmini::hasIndex($table, $index_col, true)) {
		  if (!OPCmini::hasIndex($table, $index_col, false)) {
		     $msg = '<span style="color:red;">Index is not installed</span>'; 
		  }
		  else {
		    $msg = '<span style="color:green;">Index is already created</span>'; 
		  }
		  
	  }
	  else {
		  $msg = '<span style="color:green;">Index is already created</span>'; 
	  }
	  
	  
	 
	  return $msg; 
	   
   }
   
	
}