<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
class trustedshops {
  function __construct() {
    
  }
   
  function createTable() {
	  $q = 'CREATE TABLE IF NOT EXISTS `#__onepage_trustedshops` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `virtuemart_order_id` int(1) NOT NULL,
  `email` varchar(500) NOT NULL,
  `sent_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_order_id` (`virtuemart_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8'; 
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$db->execute(); 

	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
	$ind = OPCmini::hasIndex('virtuemart_order_histories', array('virtuemart_order_id','order_status_code')); 
	if (empty($ind)) {
		     OPCmini::addIndex('virtuemart_order_histories', array('virtuemart_order_id','order_status_code')); 
		  }
		  
		  $ind = OPCmini::hasIndex('virtuemart_orders', array('order_status')); 
		  if (empty($ind)) {
		     OPCmini::addIndex('virtuemart_orders', array('order_status')); 
		  }
		  
		 $ind = OPCmini::hasIndex('virtuemart_order_histories', array('created_on')); 
		 if (empty($ind)) {
		     OPCmini::addIndex('virtuemart_order_histories', array('created_on')); 
		  }
		  
		  

  }
  
  function onCli() {
	  
	  
		$this->createTable(); 
	  
		$app     = JFactory::getApplication('site');
		@ini_set('memory_limit','32G');
         require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php');
		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'opc.php');
		 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'numbering.php');
		 

		$params = self::getParams(); 
		
		if (empty($params)) return; 
		if (empty($params['tid_enabled'])) return; 
		
		$days = (int)$params['config']->days; 
		//if (empty($days)) return; 
		
		$debug = (int)$params['config']->debug; 
		
		$statuses = $params['tid_autocreatestatus']; 
		
		$db = JFactory::getDBO(); 
		$im = array(); 
		foreach ($statuses as $s) {
		  $im[] = "'".$db->escape($s)."'"; 	
		}
		
		
		$dataselect = 'select `o`.`virtuemart_order_id`, `o`.`order_language`, `o`.`order_number`, `u`.`email`, `u`.`first_name`, `u`.`middle_name`, `u`.`last_name`, DATE(DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)) as `ordertime`, `h`.`created_on`, `o`.`created_on` as `order_created_on` from #__virtuemart_orders as `o` '; 
		$dataselect .= ' left join #__onepage_trustedshops as a on o.virtuemart_order_id = a.virtuemart_order_id ';
		$dataselect .= " inner join `#__virtuemart_order_userinfos` as `u` on ((`o`.`virtuemart_order_id` = `u`.`virtuemart_order_id`) and (`u`.`address_type` = 'BT')) "; 
		//can cause duplicate lines:
		$dataselect .= " inner join `#__virtuemart_order_histories` as `h` on ((`o`.`virtuemart_order_id` = `h`.`virtuemart_order_id`) and (`h`.`order_status_code` = `o`.`order_status`)) "; 
		
		if ($debug === 1) {
		for ($i=$days; $i>=0; $i--) {
		/*
		$q = 'select `o`.`virtuemart_order_id` ';
		$q .= ', `u`.`email`, `u`.`first_name`, `u`.`middle_name`, `u`.`last_name`'; 
		$q .= ' , DATE(DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)) as `ordertime`, `h`.`created_on` from #__virtuemart_orders as `o` '; 
		*/
		
		$q = ' where '; 
		$w = array(); 
		$w[] = ' a.virtuemart_order_id IS NULL '; 
		if (!empty($im)) {
		 $w[] = ' o.order_status IN ('.implode(',', $im).') '; 
		}
		$w[] = ' DATE(DATE_ADD(h.created_on, INTERVAL +'.(int)$i.' DAY)) = CURRENT_DATE '; 
		$qr = $dataselect.$q.implode(' and ', $w); 
		$db->setQuery($qr); 
		$res = $db->loadAssocList(); 
		
		if (!empty($res)) {
			    $days = $i; 
				break;
		}
		}
		}
		
		
			
			/*
			$q .= ' left join `#__onepage_trustedshops` as `a` on `o`.virtuemart_order_id = `a`.virtuemart_order_id ';
			$q .= " inner join `#__virtuemart_order_userinfos` as `u` on ((`o`.`virtuemart_order_id` = `u`.`virtuemart_order_id`) and (`u`.`address_type` = 'BT')) "; 
			//can cause duplicate lines:
			$q .= " inner join `#__virtuemart_order_histories` as `h` on ((`o`.`virtuemart_order_id` = `h`.`virtuemart_order_id`) and (`h`.`order_status_code` = `o`.`order_status`)) "; 
			*/
			$q = ' where '; 
			$w = array(); 
			$w[] = ' (`a`.`virtuemart_order_id` IS NULL) '; 
		
		if (!empty($im)) {
		 $w[] = ' `h`.`order_status_code` IN ('.implode(',', $im).') '; 
		}
		$w2 = array(); 
		$w2[] = ' (DATE(DATE_ADD(`h`.`created_on`, INTERVAL +'.(int)$days.' DAY)) = CURRENT_DATE) '; 
		for ($i=$days + 1 ; $i<60; $i++) {
			$w2[] = ' (DATE(DATE_ADD(`h`.`created_on`, INTERVAL +'.(int)$i.' DAY)) = CURRENT_DATE) '; 
		}
		$w[] = '('.implode(' OR ', $w2).')'; 
		$qr = $dataselect.$q.implode(' and ', $w); 
		$db->setQuery($qr); 
		$res = $db->loadAssocList(); 
		
		
		
		$toProcess = array(); 
		
		if (!empty($res)) {
			
			$first = reset($res); 
			
			
			if (class_exists('cliHelper')) 	cliHelper::debug('Found '.count($res).' orders on '.date('d F Y', strtotime($first['ordertime']))); 
			static $done; 
			if (empty($done)) $done = array(); 
			foreach ($res as $row) {
				$virtuemart_order_id = (int)$row['virtuemart_order_id']; 
				if (!empty($done[$virtuemart_order_id])) continue; 
				
				$done[$virtuemart_order_id] = true; 
				
				
				
				
				
				
				
				//send it at the SAME HOUR as the order was made (comparing purely mysql time)
				
				if (class_exists('cliHelper')) 	cliHelper::debug('Processing: Order ID '.$row['virtuemart_order_id'].' created on '.$row['ordertime'].' '); 
				


$customer = array(); 
$customer['firstname'] = $row['first_name'];
$customer['lastname'] = $row['last_name'];
$customer['contact'] = new stdClass; 
$customer['contact']->email = $row['email']; 
$customer = (object)$customer; 



				
				/*
				$orderModel = OPCmini::getModel('Orders'); 
				VirtueMartControllerOpc::emptyCache(); 			
				$order = $orderModel->getOrder($virtuemart_order_id);
				$langTag = $order['details']['BT']->order_language; 
				self::loadLanguage($langTag); 
				
				
				foreach ($order['items'] as $i) {
					$p = array(); 

					$product = array(); 
					$product['sku'] = $i->order_item_sku; 
					$product['name'] = $i->order_item_name; 
					$product['imageUrl'] = ''; 
					$product['url'] = ''; 
				}
				*/
				$date = date_create($row['order_created_on']);
				$orderDate = date_format($date, 'Y-m-d'); 
				$orderReference = $row['order_number']; 
				$currency = 'EUR'; 
				
				$config = JFactory::getConfig();	
				
				$order = array(); 
				$order['orderDate'] = $orderDate; 
				$order['orderReference'] = $orderReference; 
				$order['currency'] = $currency; 
				$order['estimatedDeliveryDate'] = ''; //$orderDate; 
				$order = (object)$order; 
				
				$x = new stdClass(); 
				$x->order = $order;
				$x->customer = $customer; 
				$x->virtuemart_order_id = $virtuemart_order_id; 
				$x->email = $row['email']; 
				$lang = $row['order_language']; 
				$toProcess[$lang][] = $x; 
/*
$order['products'] = array(); 
$order['products'][] = $product; 			
*/
				
				
			
				} //end foreach
				
				$request = ''; 
				$response = ''; 
				
				
				 
				$rQ = ''; 
				$rS = ''; 
				if (!empty($toProcess)) {
					foreach ($toProcess as $lang=>$toP) {
						
						$ll = strtolower($lang); 
						$ll = str_replace('-', '_', $ll); 
						$langK = 'tsid_'.$ll;
						
						
						
						if (!empty($params['config']->$langK)) {
							$tsid = $params['config']->$langK;
						}
						else {
							$tsid = $params['config']->tsid;
						}
						
						$ret = $this->sendRequest($toP, $request, $response, $tsid); 
						
					
				
				if (empty($debug)) if (class_exists('cliHelper')) 	cliHelper::debug('DEBUG: TrsutedShops request was sent'); 
				if ($debug === 1) if (class_exists('cliHelper')) 	{
					$this->sendDebugMail($request."\n".$response); 
					cliHelper::debug('DEBUG: No TrustedShops request sent, email sent to admin'); 
					
				}
				if ($debug === 2) if (class_exists('cliHelper')) 	{
					$this->sendDebugMail($request."\n".$response); 
					cliHelper::debug('DEBUG: TrsutedShops request was sent and Email was sent to vendor'); 
				}
				
				
				
				if ($ret === true) {
					foreach ($toP as $o) {
						
					 $virtuemart_order_id = $o->virtuemart_order_id; 
					 if ($debug !== 1) {
					   self::insertLog($virtuemart_order_id, $o->email); 
					 }
					 else {
						 cliHelper::debug('DEBUG: Skipping insert log'); 
					 }
						
					}
					
				}
				elseif (is_array($ret)) {
					foreach ($ret as $virtuemart_order_id => $ok) {
						self::insertLog($virtuemart_order_id); 
					}
				}
				else {
					if (class_exists('cliHelper')) 	{
						if (($debug === 1) && ($ret === false)) {
							cliHelper::debug('Not sending request due debug level'); 
						}
						else {
							cliHelper::debug('Error sending request'); 
						}
						cliHelper::debug($request); 
						cliHelper::debug($response); 
					}
				}
				
				if (is_array($ret) && (count($ret) !== count($toProcess))) {
					cliHelper::debug('Error sending request'); 
					
					if (empty($debug)) {
						$this->sendDebugMail('Error sending request to TrustedShops'."\n".$request."\n".$response);
					}
					else {
						cliHelper::debug($request); 
						cliHelper::debug($response); 
					}
				}
				}
				
				
				
				
				
		}
		else {
			if (class_exists('cliHelper')) 	cliHelper::debug('No orders found on '.date("d F Y", strtotime("-".(int)$days." days", time()))); 
		}

		}
		else {
			if (class_exists('cliHelper')) 	cliHelper::debug('No orders found on '.date("d F Y", strtotime("-".(int)$days." days", time()))); 
		}
	    if (class_exists('cliHelper')) 	cliHelper::debug( 'Finished...'); 
		 
  }
  
  
  private static function insertLog($virtuemart_order_id, $email='') {
	  try {
	  $db = JFactory::getDBO(); 
	  $q = "insert into `#__onepage_trustedshops` (`id`, `virtuemart_order_id`, `email`, `sent_on`) values (NULL, ".(int)$virtuemart_order_id.", '".$db->escape($email)."', NOW())"; 
	  $db->setQuery($q); 
	  $db->execute(); 
	  }
	  catch (Exception $e) {
		   if (class_exists('cliHelper')) 	cliHelper::debug( (string)$e); 
	  }
  }
  
  private static function sendRequest($toProcess, &$request='', &$response='', $tsid='') {
	  $params = self::getParams(); 
	  
$data = array(); 
$data['username'] = $params['config']->username; 
$data['password'] = $params['config']->password; 

$langdatas = array(); 

$r = new stdClass(); 
$r->reviewCollectorRequest = new stdClass(); 
$r->reviewCollectorRequest->reviewCollectorReviewRequests = array(); 


//$dateTime = new DateTime('tomorrow'); 

$dateTime = new DateTime('now'); 
$reminderdays = (int)$params['config']->reminderdays;
if (!empty($reminderdays)) {
 $dateTime->add(new DateInterval('P'.$reminderdays.'D'));
}
$tomorrow = date_format($dateTime, 'Y-m-d'); 
foreach ($toProcess as $currentObj) {
$rX = new stdClass(); 
$rX->reminderDate = $tomorrow; 
$rX->template = new stdClass(); 
$rX->template->variant = $params['config']->template; 
$rX->template->includeWidget = 'true'; 
$rX->order = (object)$currentObj->order; 
$rX->consumer = (object)$currentObj->customer; 
$r->reviewCollectorRequest->reviewCollectorReviewRequests[] = $rX; 
}

$data_json = json_encode($r, JSON_PRETTY_PRINT); 
$debug = (int)$params['config']->debug; 


if ($debug === 1) {
	// nothing sent, just ceate request:
	$request = $data_json; 
	return false; 
}
$request = $data_json; 

//echo $data_json; die(); 

$data_string = json_encode($r); 

if (empty($tsid)) {
	$tsid = $params['config']->tsid; 
}

$url = 'https://api.trustedshops.com/rest/restricted/v2/shops/'.$tsid.'/reviews/trigger.json';

$login = base64_encode($data['username'].':'.$data['password']);
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "POST",
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string),
CURLOPT_HTTPHEADER => array(
"authorization: Basic ".$login."",
"cache-control: no-cache",
"content-type: application/json"
),
));

if( ! $response = curl_exec($curl))
    {
		
        $response = curl_error($curl);
		
    }
    curl_close($curl);
	 
	$c = 0; 
	$orders_ok = array(); 
	$data = @json_decode($response); 
	if (!empty($data)) {
		foreach ($data->response->data->reviewCollectorRequest->reviewCollectorReviewRequests as $status) 
		{
			$order_id = $status->order->orderReference;
			$status = $status->status;
			if ($status !== 'ERROR') {
			  $orders_ok[$order_id] = true; 
			  $c++; 
			}
		}
	}
	if ($c === count($toProcess)) {
		return true; 
	}
	return $orders_ok; 


  }
  
  private function sendDebugMail($content) {
	  $params = self::getParams(); 
	  $config = JFactory::getConfig();	
	  $sender = array( $config->get( 'mailfrom' ), $config->get( 'fromname' ) );
	  
	  $mailer = JFactory::getMailer();
				
				
				$mailer->setSender( $sender );
				
				
				
				
				
				
				
				
				
				
				 
				
				
				$bcc = $params['config']->emails; 
				if (!empty($bcc)) {
					if (strpos($bcc, ',') !== false) {
						$ar = explode(',', $bcc); 
						$mailer->addRecipient($ar); 
					}
					else {
						$mailer->addRecipient(array($bcc => $bcc)); 
					}
				}
				else {
					$mailer->addRecipient( array($sender, $sender) );
				}
				
				$subject = $params['config']->email_subject; 
				if (!empty($subject)) $subject = JText::_($subject); 
				
				if (isset($view->subject)) {
					$subject = $view->subject;
				}
				
				
				$mailer->setSubject(  html_entity_decode( $subject) );
				$mailer->isHTML( true );
				$mailer->setBody( $content );
				$resMail = $mailer->Send();
  }
  
  private static function loadLanguage($langTag) {
	  
	  
	  $tag = $langTag; 
	  $lang = JLanguage::getInstance($langTag, false);
	  $app     = JFactory::getApplication('site');
	  $app->loadLanguage($lang);
	  $app->set('language', $langTag); 
	  
	  JFactory::getLanguage()->load('com_onepage', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_onepage_trustedshops', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart_orders', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart_shoppers', JPATH_SITE); 
	  
	   $app->input->set('language', $langTag);
	   $langObj = JLanguage::getInstance($langTag, false); 
	   $app->loadLanguage($langObj);
	   $app->set('language', $langObj);
	  
	
	
		
		if (!JLanguage::exists($langTag)) return false;  
		$homes = $this->getHomes(); 
		if (!isset($homes[$langTag])) return false; 
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
		if (!method_exists($app, 'getLanguage')) {
			return false; 
		}
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
  
  
  private static function _getOPCView($viewName, &$vars=array(),$controllerName = NULL, $layout='default', $format='html', &$view)
	{
		jimport('joomla.filesystem.file');
		$lang = JFactory::getLanguage(); 
		
		$app     = JFactory::getApplication('site');
		$app->loadLanguage(); 
		
		
		$originallayout = JRequest::getVar( 'layout' );
		
		if (empty($controllerName)) {
		   $controller_name = JFile::makeSafe($viewName); 
		   
		}
		else {
			$controller_name = JFile::makeSafe($controllerName); 
			
		}
		
		$viewName = JFile::makeSafe($viewName); 
		$format = JFile::makeSafe($format); 
		
		$opc_path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'; 
		
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php')) 
			return false; 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php');
		$controller_class_name = 'VirtuemartController'.ucfirst($controller_name); 
		if (!class_exists($controller_class_name)) return false; 
		$controller = new $controller_class_name();
		JRequest::setVar( 'layout', $layout );
		

		
		
		if (method_exists($controller, 'addViewPath')) { 
			$controller->addViewPath($opc_path.DIRECTORY_SEPARATOR.'views');
		}
		else
		if (method_exists($controller, 'addIncludePath')) {
			$controller->addIncludePath($opc_path.DIRECTORY_SEPARATOR.'views');
		}
		
		
		$view = $controller->getView($viewName, $format);
		
		
		$view->assignRef('layout', $layout); 
		$view->assignRef('format', $format); 
		$view->setLayout($layout); 
		
		//STANDARD PATHS:
		if (method_exists($view, 'addTemplatePath')) {
			$view->addTemplatePath($opc_path.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewName.DIRECTORY_SEPARATOR.'tmpl'); 
		} else { 
			if (method_exists($view, 'addIncludePath')) 
			{
				$view->addIncludePath( $opc_path.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewName.DIRECTORY_SEPARATOR.'tmpl' );
			}
		}
		
		$app = JFactory::getApplication(); 
		$template = $app->getTemplate(); 
		
		$tp = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.$viewName; 
		
		if (file_exists($tp))
		{
			
			
			if (method_exists($view, 'addTemplatePath')) { 
				$view->addTemplatePath(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.$viewName.DIRECTORY_SEPARATOR); 
				
				
				
			}
			else
			{
				if (method_exists($view, 'addIncludePath')) {
					$view->addIncludePath( JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.$viewName.DIRECTORY_SEPARATOR );
				
					
				}
			}
		}
		
		foreach ($vars as $key => $val) {
			$view->{$key} = $val;
		}
		ob_start(); 
		$html = $view->display();
		$html2 = ob_get_clean(); 
		
		if (empty($html)) $html = ''; 
		if ($html === $html2) {
		 $html2 = ''; 
		}
		
		
		
		
		JRequest::setVar( 'layout', $originallayout );
		
		
		return $html.$html2; 
		
		
		
	}
  
  public static function getParams() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
		$OnepageTemplateHelper = new OnepageTemplateHelper(); 
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDER_DATA_TXT', true); 
		
		foreach ($tids as $t) {
			if ($t['file'] === 'trustedshops.php') return $t; 
		}
		
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDERS_TXT', true); 
		
		foreach ($tids as $t) {
			if ($t['file'] === 'trustedshops.php') return $t; 
		}
		
		return array(); 
	}
  
}
