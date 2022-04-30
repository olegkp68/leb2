<?php
/**
 * 
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 * @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
 * 
*/

class OPCGdpr {
	public static function processRequest($isCheckout=true) {
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		$is_enabled = OPCconfig::get('gdpr_log', false); 
		
		if (empty($is_enabled)) return; 
		
		
		
		self::createTables(); 
		
		$email = JRequest::getVar('email', JRequest::getVar('guest_email', JFactory::getUser()->get('email', ''))); 
		
		
		
		if (empty($email)) return; 
		
		$post_names=array(); 
		$htmls = array(); 
		$article_ids = array(); 
		$types = array(); 
		
		self::getAllGDPRCheckboxes($isCheckout, $post_names, $htmls, $article_ids, $types);
		
		$db = JFactory::getDBO(); 
		$q = 'START TRANSACTION'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		
		
		
		foreach ($post_names as $type => $post_key) {
			
			$was_rendered = JRequest::getVar('was_rendered_'.$type, 0); 
			if (empty($was_rendered)) {
				
				continue; 
			}
			
			
			
			$status_val = JRequest::getVar($post_key, 0); 
			if (is_array($status_val)) {
				//acymailing:
				$status_val = reset($status_val); 
				
				
			}
			
			
			
			//status = 1 -> agree
			//status = 0 -> don't agree
			
			//checkbox:
			if ((!isset($types[$type])) && (!empty($status_val))) {
				$status = 1;
			}
			elseif ((!isset($types[$type])) && (empty($status_val))) {
				$status = 0; 
			}
			elseif ((empty($types[$type])) && (!empty($status_val))) {
				$status = 1; 
			}
			elseif ((empty($types[$type])) && (empty($status_val))) {
				//not found in POST
				$status = 0; 
			}
			elseif ((!empty($types[$type])) && ($status_val === "1")) {
				//dropdown value can be either "1", "2" or "" where only 1 is the agree value
				$status = 1;
			}
			elseif ((!empty($types[$type])) && ($status_val === "2")) {
				$status = 0;
			}
			elseif ((!empty($types[$type])) && ($status_val === 0)) {
				//dropdown was not rendred
				continue; 
			}
			else {
				$status = 0; 
			}
			
			
			
				
			    $article_html = ''; 
				self::moveData($email, $type); 
				self::insertData($email, $status, $type, $htmls[$type], $article_ids[$type], $article_html); 
				
			
		}
		
		
		$q = 'COMMIT'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		
	}
	
	public static function getUserAgent() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
$user_agent = $_SERVER['HTTP_USER_AGENT']; 
}
else $user_agent = ''; 	
return $user_agent; 

	}
	public static function moveData($email, $type) {
		$db = JFactory::getDBO(); 
		$q = 'insert into #__gdpr_archive select * from #__gdpr_history ';
		$where = ' where (`email` = \''.$db->escape($email).'\' ';
		$user_id = JFactory::getUser()->get('id', 0); 
		if (!empty($user_id)) {
		  $where .= ' or `user_id` = '.(int)$user_id; 
		}
		$where .= ') and `type` = \''.$db->escape($type).'\' '; 
		
		$db->setQuery($q.$where); 
		$db->execute(); 
		
		$q = 'delete from #__gdpr_history '.$where; 
		$db->setQuery($q); 
		$db->execute(); 
	}
	
	public static function insertData($email, $status, $type, $html_checkbox, $article_ref, $article_html='') {
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		$is_enabled = OPCconfig::get('gdpr_log', false); 
		if (empty($is_enabled)) return; 
		
		$db = JFactory::getDBO(); 
		
	
		
		$agree_html_hash = md5($html_checkbox); 
		
		$ref_id = 0; 
	
		if (!empty($article_ref)) {
			
			
			
			$q = 'select `ref`.`id` from #__gdpr_ref as `ref`, #__content as c where c.`id` = '.(int)$article_ref.' and `ref`.`agree_html_hash` = \''.$db->escape($agree_html_hash).'\' and `ref`.`agree_ref_hash` = MD5(CONCAT(c.`introtext`, c.`fulltext`))';
			$db->setQuery($q); 
			$ref_id = $db->loadResult(); 
			
			
			
			if (empty($ref_id)) {
			  $q = 'select CONCAT(`introtext`, `fulltext`) as `article_html`, MD5(CONCAT(`introtext`, `fulltext`)) as `agree_ref_hash` from #__content where `id` = '.(int)$article_ref; 
			  $db->setQuery($q); 
			  $res = $db->loadAssoc(); 
			  
			  $agree_ref_hash = $res['agree_ref_hash']; 
			  $agree_text = $res['article_html']; 
			  
			  
			  	$ins = array(); 
				$ins['id'] = 'NULL'; 
				$ins['agree_html'] = $html_checkbox; 
				$ins['agree_html_hash'] = $agree_html_hash; 
				$ins['agree_text'] = $agree_text; 
				$ins['agree_ref_id'] = $article_ref; 
				$ins['agree_ref_type'] = 'article'; 
			    $ins['agree_ref_hash'] = $agree_ref_hash; 
				
				
				OPCmini::insertArray('#__gdpr_ref', $ins); 
				
				
				if (!empty($ins['id'])) $ref_id = (int)$ins['id']; 
				
			}
		}
		else {
			$q = 'select `ref`.`id` from #__gdpr_ref as `ref` where `ref`.`agree_html_hash` = \''.$db->escape($agree_html_hash).'\' ';
			$db->setQuery($q); 
			$ref_id = $db->loadResult(); 
			
			
			
			if (empty($ref_id)) {
			  
			    $agreee_ref_hash = ''; 
			    $agree_text = ''; 
			  
			  
			  	$ins = array(); 
				$ins['id'] = 'NULL'; 
				$ins['agree_html'] = $html_checkbox; 
				$ins['agree_html_hash'] = $agree_html_hash; 
				$ins['agree_text'] = ''; 
				$ins['agree_ref_id'] = 0; 
				$ins['agree_ref_type'] = 'none'; 
			    $ins['agree_ref_hash'] = ''; 
				
				
				OPCmini::insertArray('#__gdpr_ref', $ins); 
				
				if (!empty($ins['id'])) $ref_id = (int)$ins['id']; 

			}
		}
		
		
		
		$ins = array();
		$ins['id'] = 'NULL'; 
		$ins['email'] = $email; 
		$ins['user_id'] = JFactory::getUser()->get('id', 0); 
		$ins['status'] = $status; 
		$ins['type'] = $type; 
		$ins['ip'] = self::getIP(); 
		$ins['user_agent'] = self::getUserAgent(); 
		$ins['agree_ref'] = $ref_id; 
		
		OPCmini::insertArray('#__gdpr_history', $ins); 
		
		
	}
	
	public static function createTables55() {
		
		
		
		$jdb = JFactory::getDBO(); 
		
		if (!OPCmini::tableExists('#__gdpr_history')) {
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `type` varchar(20) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_agent` varchar(5000) NOT NULL DEFAULT \'\',
  `agree_ref` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email_and_time` (`email`,`time`),
  KEY `email_and_type` (`email`,`user_id`, `time`, `type`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8; '; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
		}
	if (!OPCmini::tableExists('#__gdpr_ref')) {
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_ref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `agree_html` TEXT NOT NULL DEFAULT \'\',
  `agree_html_hash` varchar(32) NOT NULL DEFAULT \'\',
  `agree_text` TEXT NOT NULL DEFAULT \'\',
  `agree_ref_id` int(11) NOT NULL,
  `agree_ref_type` varchar(190) NOT NULL default \'article\',
  `agree_ref_hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_and_hash` (`agree_ref_id`,`agree_ref_hash`, `agree_html_hash`),
  KEY `agree_ref_type_key` (`agree_ref_type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
	}
	if (!OPCmini::tableExists('#__gdpr_archive')) {
		
		$engine = 'InnoDB'; 
		$db = JFactory::getDBO(); 
			$q = 'show engines'; 
		$db->setQuery($q); 
		$eng = $db->loadAssocList(); 
		foreach ($eng as $row) {
			if ($row['Engine'] === 'ARCHIVE') {
				$engine = 'ARCHIVE'; 
			}
		}
		
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_archive` (
  `id` bigint(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `type` varchar(20) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_agent` varchar(5000) NOT NULL DEFAULT \'\',
  `agree_ref` int(11) NOT NULL
	) ENGINE='.$engine.' DEFAULT CHARSET=utf8; '; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
	
	
	
	}
	}
	public static function getIP() {
		
$ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
	return $ipaddress;
	}
	
	public static function createTables() {
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		$jdb = JFactory::getDBO(); 
		
		$isM57 = OPCmini::isMysql('5.6.5');
		
		if (!$isM57) {
			  return self::createTables55(); 
		}
		
	if (!OPCmini::tableExists('#__gdpr_history')) {
		
		
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(20) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_agent` varchar(5000) NOT NULL DEFAULT \'\',
  `agree_ref` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email_and_time` (`email`,`time`),
  KEY `email_and_type` (`email`,`user_id`, `time`, `type`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8; '; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
	}
	if (!OPCmini::tableExists('#__gdpr_ref')) {
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_ref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `agree_html` TEXT NOT NULL DEFAULT \'\',
  `agree_html_hash` varchar(32) NOT NULL DEFAULT \'\',
  `agree_text` TEXT NOT NULL DEFAULT \'\',
  `agree_ref_id` int(11) NOT NULL,
  `agree_ref_type` varchar(190) NOT NULL default \'article\',
  `agree_ref_hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_and_hash` (`agree_ref_id`,`agree_ref_hash`, `agree_html_hash`),
  KEY `agree_ref_type_key` (`agree_ref_type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;'; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
	}
	if (!OPCmini::tableExists('#__gdpr_archive')) {
		
		$engine = 'InnoDB'; 
		$db = JFactory::getDBO(); 
			$q = 'show engines'; 
		$db->setQuery($q); 
		$eng = $db->loadAssocList(); 
		foreach ($eng as $row) {
			if ($row['Engine'] === 'ARCHIVE') {
				$engine = 'ARCHIVE'; 
			}
		}
		
		
		$q = 'CREATE TABLE IF NOT EXISTS `#__gdpr_archive` (
  `id` bigint(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(20) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `user_agent` varchar(5000) NOT NULL DEFAULT \'\',
  `agree_ref` int(11) NOT NULL
	) ENGINE='.$engine.' DEFAULT CHARSET=utf8; '; 
		$jdb->setQuery($q); 
		$jdb->execute(); 
	}
	
	

	}
	
	//possible types: 
	//acy,tos,{opc_tracking_file},privacy
	
	public static function getLastStatus($email, $type) {
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		$is_enabled = OPCconfig::get('gdpr_log', false); 
		if (empty($is_enabled)) return 0; 
		
		self::createTables(); 
		
		$db = JFactory::getDBO(); 
		$q = 'select `status` from #__gdpr_history where `email` = \''.$db->escape($email).'\' and `type` = \''.$db->escape($type).'\' order by time desc limit 1';  
		$db->setQuery($q); 
		$status = $db->loadResult(); 
		
		
		
		if (empty($status)) return 0; 
		
		return 1; 
		
	}
	
	
	//$types: 0=checkbox, 1=dropdown
	public static function getAllGDPRCheckboxes($isCheckout=true, &$post_names=array(), &$htmls=array(), &$article_ids=array(), &$types=array()) {
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		
		$OPCloader = new OPCloader(); 
		$post_names = array(); 
		$htmls = array(); 
		$article_ids = array(); 
		
		if ($isCheckout) {
		 self::getGDPRCheckboxes($post_names, $htmls, $article_ids, $types); 
		 
		}
 
		
		$tos_config = OPCconfig::getValue('opc_config', 'tos_config', 0, 0, true); 
		if ((!empty($tos_config)) && (is_numeric($tos_config))) {
			$article_ids['tos'] = (int)$tos_config; 
		}
		else {
			$article_ids['tos'] = 0; 
		}
			$post_names['tos'] = 'agreed'; 
			$types['tos'] = 0; 
			ob_start(); 
			?>
			<div id="agreed_div" class=" ">
				<input type="checkbox" id="agreed_field" name="agreed" value="1" class="inputbox"  checked="checked" class="terms-of-service"  required="required" autocomplete="off" />
					<label for="agreed_field"><?php echo OPCLang::_('COM_VIRTUEMART_I_AGREE_TO_TOS'); ?></label>
					<a target="_blank" rel="{handler: 'iframe', size: {x: 500, y: 400}}" class="opcmodal" href="<?php echo $tos_link; ?>" title="<?php  echo OPCLang::_('COM_VIRTUEMART_I_AGREE_TO_TOS'); ?>" >
					 (<?php echo OPCLang::_('COM_VIRTUEMART_CART_TOS'); ?>)
					</a>
				<strong>* </strong> 
			</div>
	<?php 
	$tos_html = ob_get_clean(); 
			$htmls['tos'] = $tos_html; 
		
		
		$ref = new stdClass(); 
		$privacy_checkbox = $OPCloader->getItalianCheckbox($ref); 
		if (!empty($privacy_checkbox)) {
			$htmls['privacy'] = $privacy_checkbox; 
			$post_names['privacy'] = 'italianagreed'; 
			
			$adc_op_privacyid = 0; 
			$adc_op_privacyid = OPCconfig::getValue('opc_config', 'adc_op_privacyid', 0, $adc_op_privacyid, true); 
			$article_ids['privacy'] = $adc_op_privacyid; 
			$types['privacy'] = 0; 
		}
		
		
		$newsletter_html = $OPCloader->getSubscriptionCheckbox($ref); 
		if (!empty($newsletter_html)) {
			$post_names['acy'] = 'acysub'; 
			$htmls['acy'] = $newsletter_html; 
			$article_ids['acy'] = 0; 
			$types['acy'] = 0; 
		}
		
		
	}
	
	public static function getPluginCheckboxes() {
		$dispatcher = JDispatcher::getInstance();
		
		$checkboxes = array(); 
		$dispatcher->trigger('plgOpcGetCheckboxes', array(&$checkboxes));
		
		if (empty($checkboxes)) return ''; 
		
		$ref = new stdClass(); 
		$checkbox_html = ''; 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		$OPCloader = new OPCloader(); 
		
		
		
		 foreach ($checkboxes as $config)
	    {
	     $mf = $config->post_name; 
		 
		 
		 
		 $email = JFactory::getUser()->get('email', ''); 
		 if (empty($email)) {
		    $cart = OPCmini::getCart(); 
			if (!empty($cart->BT['email'])) {
				$email = $cart->BT['email'];
				
				
			}
		 }
		 
		
		 
		 
				 
		
		 $last_status = false; 
		 if (!empty($email)) {
			 $last_status = (bool)self::getLastStatus($email, $config->post_name); 
		 }
		
		
		$ita =  ''; 
		
		$session = JFactory::getSession(); 

		$saved_f = $session->get('opc_fields', array(), 'opc'); 
		if (empty($saved_f)) $saved_fields = array(); 
		else
		$saved_fields = @json_decode($saved_f, true); 
		
		
		
		$agree_checked = $last_status; 
		if ((!empty($saved_fields))  && (!empty($saved_fields[$config->post_name])))
		{
			$agree_checked = true; 
		}
		
		$vars = array('agree_checked' => $agree_checked); 
		if ($agree_checked) {
			$vars = array('checked' => ' checked="checked" '); 
		}
		else {
			$vars = array('checked' => ' '); 
		}
		$vars['element_name'] = $config->post_name; 
		
		$post_names[$config->post_name] = $config->post_name; 
		
		
		
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'tos.php'); 
		
		
		
		
		
		
		
		
		$vars['label'] = $config->label;
		$vars['name'] = $config->post_name;
		
		
		
		
		
		
		$plugin_checkbox = $OPCloader->fetch($ref, 'plugin_checkbox', $vars, ''); 
		if (empty($plugin_checkbox)) {
		 $ital_privacy_link = OPCTos::getPrivacyLink($ref, $OPCloader); 
		 
		 $vars['opc_acy_id'] = $config->post_name; 
		 
		 $plugin_checkbox = $OPCloader->fetch($ref, 'acymailing_checkbox', $vars, ''); 
		
			 
			 $types[$mf] = 0; 
			 
			  $search = array(
		 OPCLang::_('COM_VIRTUEMART_FIELDS_NEWSLETTER'), 
		 'name="acysub[]"',
		 'name="acylistsdisplayed_dispall"',
		 'name="allVisibleLists"',
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_LABEL'),
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_DESC')
		 
		 ); 
		 $rep = array(
		 $config->label, 
		 'name="'.htmlentities($config->post_name).'"', 
		 'name="was_rendered_'.htmlentities($config->post_name).'"', 
		 'name="was_rendered_'.htmlentities($config->post_name).'"', 
		 $config->label,
		 $config->desc
		
		 ); 
		 
		 if ($agree_checked) {
			 $search[] = 'type="checkbox"'; 
			 $rep[] = 'type="checkbox" checked="checked" '; 
		 }
		 
		  $plugin_checkbox = str_replace($search, $rep, $plugin_checkbox); 
		
		  
		 }
		 
		
		 
		 
		 
		
		// default
		$htmls[$mf] = $plugin_checkbox;
		if (!empty($plugin_checkbox)) {
		  $plugin_checkbox .= '<input type="hidden" name="was_rendered_'.$mf.'" value="1" />'; 
		
		  $checkbox_html .= $plugin_checkbox; 
		  
		}
				 
			 
		 }
	   
	   
	   return $checkbox_html; 
		
	}
	
	public static function getGDPRCheckboxes(&$post_names=array(), &$htmls=array(), &$article_ids=array(), &$types=array()) {
		
		$ref = new stdClass(); 
		$checkbox_html = ''; 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		$OPCloader = new OPCloader(); 
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'tracking.php'); 
		$modelT = new JModelTracking(); 
			
		$isEnabled = $modelT->isEnabled(); 
		if (empty($isEnabled)) return ''; 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		$files = OPCtrackingHelper::getEnabledTracking(); 
		
		 foreach ($files as $mf)
	    {
	    
		 $email = JFactory::getUser()->get('email', ''); 
		 if (empty($email)) {
		    $cart = OPCmini::getCart(); 
			if (!empty($cart->BT['email'])) {
				$email = $cart->BT['email'];
				
				
			}
		 }
		 
		
		 
		 $default = new stdClass(); 
		 $config = OPCconfig::getValue('tracking_config', $mf, 0, $default); 
		 $config->key_name = $mf; 
		 if (!empty($config->enabled))
		 {
		
		 
		 if (!empty($config->has_gdpr_checkbox)) {
				 
		
		 $last_status = false; 
		 if (!empty($email)) {
			 $last_status = (bool)self::getLastStatus($email, $mf); 
		 }
		
		
		$ita =  ''; 
		
		$session = JFactory::getSession(); 

		$saved_f = $session->get('opc_fields', array(), 'opc'); 
		if (empty($saved_f)) $saved_fields = array(); 
		else
		$saved_fields = @json_decode($saved_f, true); 
		
		
		
		$agree_checked = $last_status; 
		if ((!empty($saved_fields))  && (!empty($saved_fields['gdpr_'.$mf])))
		{
			$agree_checked = true; 
		}
		
		$vars = array('agree_checked' => $agree_checked); 
		$vars['element_name'] = 'gdpr_'.$mf; 
		
		$post_names[$mf] = $vars['element_name']; 
		
		$vars['COM_ONEPAGE_GDPR_DROPDOWN_LABEL'] = $config->gdpr_checkbox_label; 
		
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'tos.php'); 
		
		//article id:
		$config->gdpr_link = (int)$config->gdpr_link; 
		$article_name = ''; 
		if (!empty($config->gdpr_link)) {
			
			$privacy_link = self::getArticleLink($OPCloader, $config->gdpr_link, $article_name); 
			
			$article_ids[$mf] = $config->gdpr_link; 
		}
		else {
			$privacy_link = ''; 
			$article_ids[$mf] = 0; 
		}
		
		$vars['privacy_link'] = $privacy_link; 
		$vars['link_title'] = $article_name; 
		$vars['COM_ONEPAGE_GDPR_DROPDOWN_DESC'] = $config->gdpr_checkbox_desc;
		$vars['privacy_label'] = $config->gdpr_checkbox_label;
		$vars['gdpr_checkbox_type'] = (int)$config->gdpr_checkbox_type; 
		
		 if (!empty($config->gdpr_checkbox_type)) {
			 
			 $types[$mf] = 1; 
		 }
		 else {
			 $types[$mf] = 0; 
		 }
		
		
		$ve = explode(';', $config->gdpr_checkbox_dropdown_values); 
		if (count($ve)>=4) {
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_CHOOSE'] = $ve[0]; 
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_IAGREE'] = $ve[1]; 
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_IDONOTAGREE'] = $ve[2];  
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_ERROR'] = $ve[3];  
		}
		else {
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_CHOOSE'] = 'COM_ONEPAGE_GDPR_DROPDOWN_CHOOSE'; 
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_IAGREE'] = 'COM_ONEPAGE_GDPR_DROPDOWN_IAGREE';
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_IDONOTAGREE'] = 'COM_ONEPAGE_GDPR_DROPDOWN_IDONOTAGREE';  
		 $vars['COM_ONEPAGE_GDPR_DROPDOWN_ERROR'] = 'COM_ONEPAGE_GDPR_DROPDOWN_ERROR';  
			
		}
		
		$gdpr_checkbox = $OPCloader->fetch($ref, 'gdpr_checkbox', $vars, ''); 
		if (empty($gdpr_checkbox)) {
		 $ital_privacy_link = OPCTos::getPrivacyLink($ref, $OPCloader); 
		 $gdpr_checkbox = $OPCloader->fetch($ref, 'italian_checkbox', $vars, ''); 
		 if (!empty($config->gdpr_checkbox_type)) {
			 
			 
			 
			 
			 $types[$mf] = 1; 
			 
			 $dropdown = '<select name="gdpr_'.$mf.'" class="gdpr_dropdown" id="gdpr_'.$mf.'_field" required="required" validate="validate" onerrormsg="'.htmlentities($vars['COM_ONEPAGE_GDPR_DROPDOWN_ERROR']).'">'; 
			 $dropdown .= '<option value="">'.htmlentities(JText::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_CHOOSE'])).'</option>'; 
			 $dropdown .= '<option value="1" '; 
			 if (!empty($agree_checked)) { $dropdown .= ' selected="selected" '; }
			 $dropdown .= ' >'.htmlentities(JText::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_IAGREE'])).'</option>'; 
			 $dropdown .= '<option value="2">'.htmlentities(JText::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_IDONOTAGREE'])).'</option>'; 
			 $dropdown .= '</select>'; 
		
/*		
		 $search = array(
		 'name="italianagreed"',
		 'id="italianagreed"'); 
		 $rep = array(
		 'name="removed_italianagreed"',
		 'id="removed_italianagreed_field"');
		 $gdpr_checkbox = str_replace($search, $rep, $gdpr_checkbox); 
		 */
		 
		 $search = array(
		 'validateItalian', 
		 'name="italianagreed"',
		 'id="italianagreed_field"',
		 'italianagreed',
		 'type="checkbox"',
		 'italagreeerr',
		 OPCloader::slash(OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_ERROR')),
		 '<input',
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_LABEL'),
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_DESC'),
		 JText::_('COM_ONEPAGE_ITALIAN_AGREE_LABEL_LINK'),
		 $ital_privacy_link
		 ); 
		 
		 $rep = array('validate_gdpr_'.$mf, 
		 'name="removed_italianagreed"',
		 'id="removed_italianagreed_field"',
		 'gdpr_'.$mf, 
		 'type="hidden"',
		 'gdpr_'.$mf.'_agreeerr',
		 OPCloader::slash(OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_ERROR'])),
		 '<input',
		 $dropdown.OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_LABEL']),
		 OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_DESC']),
		 $article_name,
		 $privacy_link
		 ); 
		 }
		 else {
			 
			 $types[$mf] = 0; 
			 
			  $search = array(
		 'addOpcTriggerer(\'callSubmitFunct\', \'validateItalian\');', 
		 'italianagreed',
		 'required="required"',
		 'required',
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_LABEL'),
		 OPCLang::_('COM_ONEPAGE_ITALIAN_AGREE_DESC'),
		 JText::_('COM_ONEPAGE_ITALIAN_AGREE_LABEL_LINK'),
		 $ital_privacy_link
		 ); 
		 $rep = array(
		 '', 
		 'gdpr_'.$mf, 
		 'data-notrequired="x"',
		 'nrq',
		 OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_IAGREE']).' '.OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_LABEL']),
		 OPCLang::_($vars['COM_ONEPAGE_GDPR_DROPDOWN_DESC']),
		 $article_name,
		 $privacy_link
		 ); 
		 }
		 
		 $gdpr_checkbox = str_replace($search, $rep, $gdpr_checkbox); 
		 //override rendred js function: 
		 if (!empty($gdpr_checkbox)) {
			 $gdpr_checkbox .= '<script>
			  function validate_gdpr_'.$mf.'() {
				 d = document.getElementById(\'gdpr_'.$mf.'_field\'); 
				 if (d != null) {
					 var val = d.options[d.selectedIndex].value; 
					 if (val === "") {
						 alert(gdpr_'.$mf.'_agreeerr); 
						 return false; 
					 }
				 }
				 return true; 
			   }
			 </script>'; 
		 }
		 else {
			 $gdpr_checkbox .= '<script>
			   function validate_gdpr_'.$mf.'() {
				   return true; 
			   }
			 </script>'; 
		 }
		 
		}
		// default
		$htmls[$mf] = $gdpr_checkbox;
		if (!empty($gdpr_checkbox)) {
		  $gdpr_checkbox .= '<input type="hidden" name="was_rendered_'.$mf.'" value="1" />'; 
		
		  $checkbox_html .= $gdpr_checkbox; 
		  
		}
				 
			 }
		 }
	   }
	   
	   return $checkbox_html; 
	}
	
	
	public static function getArticleLink(&$OPCloader, $article_id=0, &$article_name='')
 {
 
 if (empty($article_id)) return ''; 
 
 $db = JFactory::getDBO();
 if (class_exists('JLanguageAssociations')) { 
 $advClause = array(); 
  
 $currentLang = JFactory::getLanguage()->getTag();
 $advClause[] = 'c2.language = ' . $db->quote($currentLang); 
 
 $associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $article_id, 'id', 'alias', 'catid', $advClause);
 $return = array(); 
 foreach ($associations as $tag => $item) {
	 
	 if ($item->language === $currentLang) {
		 
		 $article_idT = $item->id; 
		 
		 if (strpos($article_idT, ':')!==false) {
			 $e = explode(':', $article_idT); 
			 $e[0] = (int)$e[0]; 
			 if (!empty($e[0])) {
			  $article_id = $e[0]; 
			 }
		 }
		 else {
			 $article_id = $article_idT;
		 }
		 break; 
	 }
 
 }
 }
 
 
 if (empty($article_id)) return ''; 
 $q = 'select `title` from #__content where `id` = '.(int)$article_id; 
 $db->setQuery($q); 
 $article_name = $db->loadResult(); 
 if (empty($article_name)) return ''; 

 
 require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 

 
 
 
 
 
 $langq = ''; 
 $lang = ''; 

			
 
  $lang = OPCloader::getLangCode(); 
  if (!empty($lang))
  {
	  $langq = '&lang='.$lang; 
  }
 
 

 
 
  
   
    $tos_itemid = 0; //OPCconfig::getValue('opc_config', 'tos_itemid', 0, 0, true); 
	if (!empty($tos_itemid))
	$tos_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_id.'&tmpl=component&Itemid='.$tos_itemid.$langq);
	else
	$tos_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_id.'&tmpl=component'.$langq);
  
 
 
 
 
 
 $b1 = JURI::root(true); 
 if (!empty($b1))
 if (strpos($tos_link, $b1) === 0) $tos_link = substr($tos_link, strlen($b1)); 
 
 
 
			if (strpos($tos_link, 'http')!==0)
			 {
			   $base = JURI::root(); 
			   if (substr($base, -1)=='/') $base = substr($base, 0, -1);
			   
			   if (substr($tos_link, 0, 1)!=='/') $tos_link = '/'.$tos_link; 
			   
			   $tos_link = $base.$tos_link; 
			   
			 }
			 if (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
				$tos_link = str_replace('http:', 'https:', $tos_link); 
			 }
			 
			 return $tos_link;
 } 
	
	
}