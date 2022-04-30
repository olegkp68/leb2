<?php
class OPCErrors {
	public static function store($msg, $extra=array(), $task='') {
		$x = OPCconfig::get('opc_logerrors', false); 
		
		if (empty($x)) return; 
		if (empty($msg)) return; 
		
		if (is_object($extra)) {
			$extra = (array)$extra; 
		}
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		if (!OPCmini::tableExists('onepage_errorlog'))
		{
			self::createTable(); 
		}
		if (empty($task)) {
			$extra['task'] = JRequest::getWord('task', ''); 
		}
		else {
			$extra['task'] = $task; 
		}
		
		$db = JFactory::getDBO(); 
		$msg_json = json_encode($msg); 
		$extra_json = json_encode($extra); 
		
		$toIns = new stdClass(); 
		$toIns->id = null; 
		$toIns->msg = $msg_json; 
		$toIns->extra = $extra_json; 
		$db->insertObject('#__onepage_errorlog', $toIns);
		/*
		#stan note -> we use joomla's function to insert this as json inserted data via PDO can be considered better escaped then classic mysqli escape
		$q = 'insert into `#__onepage_errorlog` (`msg`, `extra`) values (\''.$db->escape($msg_json).'\', \''.$db->escape($extra_json).'\')'; 
		$db->setQuery($q); 
		$db->execute(); 
		*/
		
		$q = 'delete from `#__onepage_errorlog` where `created_on` < (CURDATE() - INTERVAL 30 DAY)';
		$db->setQuery($q); 
		$db->execute(); 
		
		
	}
	
	/* requires json input */
	public static function writeJavascriptLog() {
		
		$data = file_get_contents('php://input'); 
		
		try {
			$json = @json_decode($data); 
			if (!empty($json)) {
				
				if (!empty($json->msg)) {
				$msg = $json->msg; 
				
				if (empty($json->cat)) {
					$json->cat = 'javascript'; 
				}
				else {
					$task = $json->cat;
				}
				
				unset($json->msg); 
				unset($json->cat); 
				
				$session = JFactory::getSession(); 
				$sid = $session->getId(); 
				
				$json->session_id = $sid; 
				
				$cartSession = $session->get('vmcart', 0, 'vm');
				if (!empty($cartSession)) {
					$cartJson = json_decode($cartSession); 
					if (!empty($cartJson)) {
						$json->cart = $cartJson; 
					}
				}
				self::store($msg, (array)$json, $task); 
				
				
				return array('ok'=>'stored'); 
				}
			}
		}
		catch (Exception $e) {
			return array('ok'=>false); 
		}
		
		return array('ok'=>true); 
		
	}
	public static function createTable() {
		$db = JFactory::getDBO(); 
		$q = 'CREATE TABLE IF NOT EXISTS `#__onepage_errorlog` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`msg` text NOT NULL,
	`created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`extra` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `created_on` (`created_on`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8'; 
	$db->setQuery($q); 
	$db->execute(); 
	}
}