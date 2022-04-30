<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
class aftersale {
  function __construct() {
    
  }
   
  function createTable() {
	  $q = 'CREATE TABLE IF NOT EXISTS `#__onepage_aftersale` (
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
		if (empty($days)) return; 
		
		$debug = (int)$params['config']->debug; 
		
		$statuses = $params['tid_autocreatestatus']; 
		
		$db = JFactory::getDBO(); 
		$im = array(); 
		foreach ($statuses as $s) {
		  $im[] = "'".$db->escape($s)."'"; 	
		}
		
		//stAn, we are going to use LIVE QUERY
		if ($debug) 
		if (false) 
		{
		for ($i=$days; $i>=0; $i--) {
			
		$q = 'select o.virtuemart_order_id  from #__virtuemart_orders as o '; 
		$q .= ' left join #__onepage_aftersale as a on o.virtuemart_order_id = a.virtuemart_order_id ';
		$q .= ' where '; 
		$w = array(); 
		$w[] = ' a.virtuemart_order_id IS NULL '; 
		if (!empty($im)) {
		 $w[] = ' o.order_status IN ('.implode(',', $im).') '; 
		}
		//$w[] = ' and created_on > timestamp(created_on) and created_on < timestamp(DATE_ADD(created_on, INTERVAL 1 DAY) '
		$w[] = ' DATE(DATE_ADD(o.created_on, INTERVAL +'.(int)$i.' DAY)) = CURRENT_DATE '; 
		$q = $q.implode(' and ', $w); 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		if (!empty($res)) {
			    $days = $i; 
				break;
		}
		}
		}
		
		{
			
			$q = 'select `o`.`virtuemart_order_id`, `u`.`email`, `u`.`first_name`, `u`.`middle_name`, `u`.`last_name`, HOUR(`o`.`created_on`) as `created_on_hour`, HOUR(NOW()) as `nowhour`, DATE(DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)) as `ordertime` from #__virtuemart_orders as `o` '; 
			$q .= ' left join `#__onepage_aftersale` as `a` on `o`.virtuemart_order_id = `a`.virtuemart_order_id ';
			$q .= " right join `#__virtuemart_order_userinfos` as `u` on ((`o`.`virtuemart_order_id` = `u`.`virtuemart_order_id`) and (`u`.`address_type` = 'BT')) "; 
			$q .= ' where '; 
			$w = array(); 
			$w[] = ' (`a`.`virtuemart_order_id` IS NULL) '; 
		
		if (!empty($im)) {
		 $w[] = ' `o`.`order_status` IN ('.implode(',', $im).') '; 
		}
		//$w[] = ' and created_on > timestamp(created_on) and created_on < timestamp(DATE_ADD(created_on, INTERVAL 1 DAY) '
		$w[] = ' DATE(DATE_ADD(`o`.`created_on`, INTERVAL +'.(int)$days.' DAY)) = CURRENT_DATE '; 
		$q = $q.implode(' and ', $w); 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		}
		
		if (!empty($res)) {
			
			$first = reset($res); 
			
			
			if (class_exists('cliHelper')) 	cliHelper::debug('Found '.count($res).' orders for after sale email system on '.date('d F Y', strtotime($first['ordertime']))); 
			
			foreach ($res as $row) {
				$virtuemart_order_id = (int)$row['virtuemart_order_id']; 
				
				$row['created_on_hour'] = (int)$row['created_on_hour']; 
				$row['nowhour'] = (int)$row['nowhour']; 
				
				
				$name = array(); 
				if (!empty($row['first_name'])) $name[] = $row['first_name']; 
				if (!empty($row['middle_name'])) $name[] = $row['middle_name']; 
				if (!empty($row['last_name'])) $name[] = $row['last_name']; 
				$name = implode(' ', $name); 
				
				//send it at the SAME HOUR as the order was made (comparing purely mysql time)
				if (($row['created_on_hour'] <= $row['nowhour']) || ($debug === 1)) {
				if (class_exists('cliHelper')) 	cliHelper::debug('Processing: Order ID '.$row['virtuemart_order_id'].' created on '.$row['created_on_hour'].' o\'clock'); 
				
				
				
				$orderModel = OPCmini::getModel('Orders'); 
				VirtueMartControllerOpc::emptyCache(); 			
				$order = $orderModel->getOrder($virtuemart_order_id);
				$langTag = $order['details']['BT']->order_language; 
				self::loadLanguage($langTag); 
				
				$args = array('order' => $order, 'myItemid' => (int)$params['config']->myItemid, 'myParams' => $params ); 
				
				$view = new stdClass(); 
				$email_content = self::_getOPCView('email', $args, 'email', 'default', 'html', $view); 
				
				$config = JFactory::getConfig();	
				$sender = array( $config->get( 'mailfrom' ), $config->get( 'fromname' ) );
				
				$mailer = JFactory::getMailer();
				
				
				$mailer->setSender( $sender );
				
				if ($debug === 2) {
				  $mailer->addBcc( $sender );
				}
				
				$email = array($row['email'], $name); 
				
				if ($debug === 1) {
					//$mailer->addRecipient( $sender );
					$email = $sender; 
				}
				
				
				 $mailer->addRecipient( $email );
				
				
				$bcc = $params['config']->emails; 
				if (!empty($bcc)) {
					if (strpos($bcc, ',') !== false) {
						$ar = explode(',', $bcc); 
						$mailer->addBcc($ar); 
					}
					else {
						$mailer->addBcc(array($bcc => $bcc)); 
					}
				}
				
				$subject = $params['config']->email_subject; 
				if (!empty($subject)) $subject = JText::_($subject); 
				
				if (isset($view->subject)) {
					$subject = $view->subject;
				}
				if ($debug === 1) {
					$subject = '[DEBUG ONLY] - '.$subject; 
				}
				
				$mailer->setSubject(  html_entity_decode( $subject) );
				$mailer->isHTML( true );
				$mailer->setBody( $email_content );
				$resMail = $mailer->Send();
				
				if (empty($debug)) if (class_exists('cliHelper')) 	cliHelper::debug('DEBUG: Email was sent to customer only'); 
				if ($debug === 1) if (class_exists('cliHelper')) 	cliHelper::debug('DEBUG: Email was sent to vendor only'); 
				if ($debug === 2) if (class_exists('cliHelper')) 	cliHelper::debug('DEBUG: Email was sent to vendor and customer'); 
				
				
				
				if ($resMail) {
					if (class_exists('cliHelper')) 	cliHelper::debug('Email sent to '.var_export($email, true)); 
					self::insertLog($email, $virtuemart_order_id); 
					break;
				}
				else {
					if (class_exists('cliHelper')) 	cliHelper::debug('Error sending email to '.var_export($email, true)); 
				}
				
				
				
				}
				else {
					if (class_exists('cliHelper')) 	cliHelper::debug('Not processed yet: Order ID '.$row['virtuemart_order_id'].' will be processed after '.$row['created_on_hour'].' o\'clock today'); 
				}
				}
				
				
		}
		else {
			if (class_exists('cliHelper')) 	cliHelper::debug('No orders found on '.date("d F Y", strtotime("-".(int)$days." days", time()))); 
		}
		if (class_exists('cliHelper')) 	cliHelper::debug( 'Finished...'); 
		
		 
  }
  
  
  private static function insertLog($email, $virtuemart_order_id) {
	  $db = JFactory::getDBO(); 
	  $q = "insert into `#__onepage_aftersale` (`id`, `email`, `virtuemart_order_id`) values (NULL, '".$db->escape(json_encode($email))."', ".(int)$virtuemart_order_id.")"; 
	  $db->setQuery($q); 
	  $db->execute(); 
  }
  
  private static function loadLanguage($langTag) {
	  $lang = JLanguage::getInstance($langTag, false);
	  $app     = JFactory::getApplication('site');
	  $app->loadLanguage($lang);
	  $app->set('language', $langTag); 
	  
	  JFactory::getLanguage()->load('com_onepage', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_onepage_aftersale', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart_orders', JPATH_SITE); 
	  JFactory::getLanguage()->load('com_virtuemart_shoppers', JPATH_SITE); 
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
			if ($t['file'] === 'aftersale.php') return $t; 
		}
		
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDERS_TXT', true); 
		
		foreach ($tids as $t) {
			if ($t['file'] === 'aftersale.php') return $t; 
		}
		
		return array(); 
	}
  
}
