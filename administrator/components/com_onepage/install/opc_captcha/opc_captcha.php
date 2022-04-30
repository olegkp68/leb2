<?php
/**
 * @copyright   Copyright (C) 2005 - 2019 RuposTel.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class PlgCaptchaOpc_captcha extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			JText::_('PLG_CAPTCHA_OPC_CAPTCHA') => array(
				JText::_('PLG_RECAPTCHA_PRIVACY_CAPABILITY_IP_ADDRESS'),
			)
		);
	}

	
	public function onInit($id = 'dynamic_recaptcha_1')
	{
		
		return true;
	}

	
	public function onDisplay($name = null, $id = 'dynamic_recaptcha_1', $class = '')
	{
		return '<input type="hidden" name="opc_captcha_loaded" value="1" data-id="'.htmlentities($id).'" />'; 
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
        $ipaddress = '127.0.0.1';
	return $ipaddress;
	}
	
	public function onJCommentsCaptchaVerify($captcha_refid, &$resp) {
		$ret = $this->onCheckAnswer(); 
		return $ret; 
	}
	
	public function onCheckAnswer($code = null)
	{
		
		

			$reason = array(); 
			$is_allowed = -1; 
			$ip = self::getIP(); 
			$is_allowed_config = $this->params->get('default_state', -1); 
			$debug = $this->params->get('debug', false); 
			if ($is_allowed_config === -1) {
				$debug = true; //send email, the plugin is not configured
				$reason[] = 'DANGER: opc_captcha plugin is enabled, but it is not configured ! Captcha will not be used. Please visit opc_captcha and review configuration.';
				
			}
			else 
			{
				if (empty($is_allowed_config)) {
				
				 $reason[] = 'DEFAULT GeoIP Configuration: ALLOWED';
				}
				else {
					
					$reason[] = 'DEFAULT GeoIP Configuration: BLOCKED';
				}
			}
			
			$reason[] = $ip.' https://whatismyipaddress.com/ip/'.$ip; 
			
			
			if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_geolocator'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'helper.php')) 
			{
			include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_geolocator'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'helper.php');
			if (class_exists('geoHelper')) 
			{
				$c = geoHelper::getCountry2Code($ip);
				$reason[] = 'COUNTRY: '.$c; 
				
				$allowed = $this->params->get('allowed_countries', ''); 
				if (!empty($allowed)) {
					if (strpos($allowed, ',') !== false) {
						$ea = explode(',', $allowed); 
						foreach ($ea as $cx) {
							$cx = strtoupper(trim($cx)); 
							if ($cx === $c) {
								$is_allowed = true; 
								$reason[] = 'ALLOWED via CONFIGURATION: country '.$c.' is in allowed configuration'; 
								break; 
							}
						}
					}
					else {
						if ($c === strtoupper($allowed)) {
							$is_allowed = true; 
							$reason[] = 'ALLOWED via CONFIGURATION: country '.$c.' is in allowed configuration'; 
						}
					}
					
				}
				$disallowed = $this->params->get('disabled_countries', ''); 
				if (!empty($disallowed)) {
					if (strpos($disallowed, ',') !== false) {
						$ea = explode(',', $disallowed); 
						foreach ($ea as $cx) {
							$cx = strtoupper(trim($cx)); 
							if ($cx === $c) {
								$is_allowed = false; 
								$reason[] = 'BLOCKED via CONFIGURATION: country '.$c.' is in blocked configuration'; 
								break; 
							}
						}
					}
					else {
						if ($c === strtoupper($disallowed)) {
							$is_allowed = false; 
							$reason[] = 'BLOCKED via CONFIGURATION: country '.$c.' is in blocked configuration'; 
							
						}
					}
					
				}
				
				
			}
			}
			else {
				$this->sendEmail(array('Error' => 'RuposTel GeoLocator Not Installed !')); 
			}
		
		$test_mail = ''; 
		foreach ($_REQUEST as $key=>$val) {
			if (stripos($val, '@')!==false) {
				if (stripos($key, 'mail') !== false) {
					$test_mail = trim($val); 
				}
			}
		}
		$status = array(); 
		$urls = array(); 
	
		
	
		
				
			
		
		//DNS is done only when the country is not in ALLOWED LIST
		if (($is_allowed !== true) || ($debug)) {
		$count_rbl = 0; 
		if (($is_allowed !== true) || (!empty($debug))) {
		$dns_check = $this->dnsCheck($ip, $status); 
		foreach ($status as $key=>$val) {
			if (!empty($val->is_spam)) {
				$reason[] = 'BLOCKED via '.$key.': '.$val->extra; 
				$count_rbl++; 
				
				//force the block:
				if ($this->params->get($key, 2) == '2') {
					$is_allowed = false; 
				}
			}
		}
		if ($is_allowed !== true)
		if ($count_rbl >= 2) {
			$reason[] = 'BLOCKED via '.$count_rbl.' RBLs'; 
			$is_allowed = false; 
		}
		}
		}
		
		
		if ($is_allowed !== true) {
			$content_filter_disabled = $this->params->get('content_filter_disabled', false); 
			if (empty($content_filter_disabled)) {
				   $input = file_get_contents('php://input'); 
				   $content_filter1 = $this->params->get('content_filter1', '[url,[URL'); 
				   
				   
				   
				   if (strpos($content_filter1, ',') !== false) {
				    $ea = explode(',', $content_filter1); 
				   }
				   else {
					   $ea = array($content_filter1); 
				   }
				   
				   
				   if (!empty($ea)) {
					   foreach ($ea as $ki=>$search) {
						   $kx = trim($search); 
						   //$ea[$ki] = str_replace('\\\\', '\\', $search); 
						   //$search = $ea[$ki]; 
					       if (empty($kx)) continue; 
						   
						   if (strpos($input, $search) !== false) {
							   $reason[] = 'BLOCKED via content filter 1X '; 
							   $is_allowed = false; 
							   break;
						   }
						   if (strpos($input, urlencode($search)) !== false) {
							   $reason[] = 'BLOCKED via content filter 1X '; 
							   $is_allowed = false; 
							   break;
						   }
						   if (strpos(html_entity_decode($input), $search) !== false) {
							   $reason[] = 'BLOCKED via content filter 1X: '.$search.' in '.html_entity_decode($input); 
							   $is_allowed = false; 
							   break;
						   }
						   else {
							   //$reason[] = 'Content filter 1X not found '.$search.' in '.html_entity_decode($input); 
						   }
						   if (strpos(urldecode($input), $search) !== false) {
							   $reason[] = 'BLOCKED via content filter 1X '.$search.' in '.urldecode($input); ; 
							   $is_allowed = false; 
							   break;
						   }
						   else {
							  // $reason[] = 'Content filter 1X not found '.$search.' in '.$input; 
						   }
						   
					   }
					   if ($is_allowed !== false)
					   {
						   $reason[] = 'Content filter 1X not found '.var_export($ea, true).' in '.var_export($input, true); 
					   }
				   }
				   
				   if ($is_allowed !== false) {
				    $search = array(); 
				    $content_filter5 = $this->params->get('content_filter5', 'href=,href\=,href =,HREF=,HREF\=,href%3D'); 
				    if (strpos($content_filter5, ',') !== false) {
				    $search_content5 = explode(',', $content_filter5); 
				   }
				   else {
					   $search_content5 = array($content_filter5); 
				   }
				   foreach ($search_content5 as $k) {
					   $kx = trim($k); 
					   if (empty($kx)) continue; 
					   $search[] = $k; 
					   $search[] = urlencode($k); 
				   }
				   
				   
					foreach ($search as $kw) {
						if (strpos($input, $kw) !== false) {
							$xa = explode($kw, $input); 
							if (count($xa) > 5) {
								$reason[] = 'BLOCKED via content filter 5X'; 
								$is_allowed = false; 
								break;
							}
						}
					}
				   }
						
					
				
				
			}
		}
		
		if ($is_allowed === -1)
		if (empty($is_allowed_config)) $is_allowed = true; 
		else $is_allowed = false; 
		
		$reason[] = ''; 
		if ($is_allowed) {
		  $reason[] = 'RESULT: ALLOWED'; 
		}
		else {
			$reason[] = 'RESULT: BLOCKED'; 
		}
		$reason_log = $reason;
		$reason[] = ''; 
		
		foreach ($status as $key=>$val) {
			if (empty($val->is_spam)) {
				$reason[] = 'ALLOWED via '.$key.': '.$val->extra; 
			}
		}
		
		// Discard spam submissions
		
		$throw_exception = $this->params->get('throw_exception', false); 
		
		if (!$is_allowed)
		{
		$error_msg = $this->params->get('error_msg', 'Your IP location is not allowed to use this form. Please contact us directly by writing an email.'); 			
		$error_msg = JText::_($error_msg); 
		JFactory::getApplication()->enqueueMessage($error_msg); 
		if ($throw_exception)
		{
			throw new \RuntimeException($error_msg);
		}
		}
		
		
		if ($debug) {
			
			$this->spamlog($reason); 
		}
		
		
		if ($is_allowed === false) {
		   openlog('joomla', LOG_NDELAY, LOG_USER);
		   $msg = '[notice] [spam_ip '.self::getIP().'] '.$_SERVER['SERVER_NAME'].' joomla spam attempt caught by opc_captcha. '.implode(' ', $reason_log);
		   syslog(LOG_NOTICE, sprintf($msg));
		   closelog();
		}
		
		return $is_allowed; 
	}
	
	
	private function dnsCheck($ip, &$status=array()) {

		$address = $ip;
		$rev = implode('.',array_reverse(explode('.', $address)));
		/*
		$lookup = $rev.'.dnsbl.ahbl.org.';
		$ahbltemp = gethostbyname($lookup);
		
			switch ($ahbltemp) {
				case "127.0.0.2":
					$sVisitorType = "Open Relay"; $ahblspambot = true; break;
				case "127.0.0.3":
					$sVisitorType = "Open Proxy"; $ahblspambot = true; break;
				case "127.0.0.4":
					$sVisitorType = "Spam Source"; $ahblspambot = true; break;
				case "127.0.0.5":
					$sVisitorType = "Provisional Spam Source Listing block (will be removed if spam stops)"; $ahblspambot = true; break;
				case "127.0.0.6":
					$sVisitorType = "Formmail Spam"; $ahblspambot = true; break;
				case "127.0.0.7":
					$sVisitorType = "Spam Supporter"; $ahblspambot = true; break;
				case "127.0.0.8":
					$sVisitorType = "Spam Supporter (indirect)"; $ahblspambot = true; break;
				case "127.0.0.9": // We don't flag end user systems unless they're spammers or match one of the other criteria
					$sVisitorType = "End User (non mail system)"; $ahblspambot = false; break;
				case "127.0.0.10":
					$sVisitorType = "Shoot On Sight"; $ahblspambot = true; break;
				case "127.0.0.11": // I'd love to match these and force RFC compliance, but that's just me, so we don't flag these either
					$sVisitorType = "Non-RFC Compliant (missing postmaster or abuse)"; $ahblspambot = false; break;
				case "127.0.0.12": // Not handling errors properly does not a spammer/attacker make
					$sVisitorType = "Does not properly handle 5xx errors"; $ahblspambot = false; break;
				case "127.0.0.13": // Again, we don't flag those just because they aren't RFC compliant
					$sVisitorType = "Other Non-RFC Compliant"; $ahblspambot = false; break;
				case "127.0.0.14":
					$sVisitorType = "Compromised System - DDoS"; $ahblspambot = true; break;
				case "127.0.0.15":
					$sVisitorType = "Compromised System - Relay"; $ahblspambot = true; break;
				case "127.0.0.16":
					$sVisitorType = "Compromised System - Autorooter/Scanner"; $ahblspambot = true; break;
				case "127.0.0.17":
					$sVisitorType = "Compromised System - Worm or mass mailing virus"; $ahblspambot = true; break;
				case "127.0.0.18":
					$sVisitorType = "Compromised System - Other virus"; $ahblspambot = true; break;
				case "127.0.0.19":
					$sVisitorType = "Open Proxy"; $ahblspambot = true; break;
				case "127.0.0.20":
					$sVisitorType = "Blog/Wiki/Comment Spammer"; $ahblspambot = true; break;
				case "127.0.0.127":
					$sVisitorType = "Other"; $ahblspambot = true; break;
				default:
					$ahblspambot = false; 
					$sVisitorType = ''; 
					break;
			} 
			
			
				
				$status['AHBL'] = new stdClass(); 
				$status['AHBL']->is_spam = (bool)$ahblspambot;
				$status['AHBL']->extra = $sVisitorType.' ('.$ahbltemp.')';
				
			 */
			if ((empty($this->params->get('SPEWS_SORBS', false))) || ($this->params->get('SPEWS_SORBS', 2) == '2'))  {
			$lookup = $rev.'.l1.spews.dnsbl.sorbs.net.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['SPEWS_SORBS'] = new stdClass(); 
				$status['SPEWS_SORBS']->is_spam = true;
				$status['SPEWS_SORBS']->extra = $lookup.' ('.$returned_lookup.')';
			}
			}
			
			if ((empty($this->params->get('PROBLEMS_SORBS', false))) || ($this->params->get('PROBLEMS_SORBS', 2) == '2'))  {
			$lookup = $rev.'.problems.dnsbl.sorbs.net.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['PROBLEMS_SORBS'] = new stdClass(); 
				$status['PROBLEMS_SORBS']->is_spam = true;
				$status['PROBLEMS_SORBS']->extra = $lookup.' ('.$returned_lookup.')';
       		} 
			}
			
			if ((empty($this->params->get('SPAMHAUS', false))) || ($this->params->get('SPAMHAUS', 2) == '2'))  {
			$lookup = $rev.'.zen.spamhaus.org.';

			// Spamhaus returns codes based on which blacklist the IP is in;
			//
			// 127.0.0.2		= SBL (Direct UBE sources, verified spam services and ROKSO spammers)
			// 127.0.0.3		= Not used
			// 127.0.0.4-8		= XBL (Illegal 3rd party exploits, including proxies, worms and trojan exploits)
			//	- 4		= CBL
			//	- 5		= NJABL Proxies (customized)
			// 127.0.0.9		= Not used
			// 127.0.0.10-11	= PBL (IP ranges which should not be delivering unauthenticated SMTP email)
			//	- 10		= ISP Maintained
			//	- 11		= Spamhaus Maintained
			//
			// We don't flag the CBL or PBL here.

			$spamhaustemp = gethostbyname($lookup);
			$spamhausspambot = false; 
			switch ($spamhaustemp){
				case "127.0.0.2":
					$sSHDB = "(SBL) Direct UBE sources, verified spam services and ROKSO spammers";
					$spamhausspambot = true;
					break;
				case "127.0.0.4": // We don't flag those in the CBL
					$sSHDB = "(CBL) (Illegal 3rd party exploits, including proxies, worms and trojan exploits)";
					$spamhausspambot = true;
					break;
				case "127.0.0.5":
					$sSHDB = "(NJABL) (Illegal 3rd party exploits, including proxies, worms and trojan exploits)";
					$spamhausspambot = true;
					break;
				case "127.0.0.6":
					$sSHDB = "(XBL) (Illegal 3rd party exploits, including proxies, worms and trojan exploits)";
					$spamhausspambot = true;
					break;
				case "127.0.0.7":
					$sSHDB = "(XBL) (Illegal 3rd party exploits, including proxies, worms and trojan exploits)";
					$spamhausspambot = true;
					break;
				case "127.0.0.8":
					$sSHDB = "(XBL) (Illegal 3rd party exploits, including proxies, worms and trojan exploits)";
					$spamhausspambot = true;
					break;
				case "127.0.0.9":
					$sSHDB = "Should be not used";
					$spamhausspambot = false;
					break;
				case "127.0.0.10": // We don't flag those in the PBL
					$sSHDB = "(PBL - ISP Maintained) ";
					$spamhausspambot = false;
					break;
				case "127.0.0.11": // We don't flag those in the PBL
					$sSHDB = "(PBL - Spamhaus Maintained) ";
					$spamhausspambot = false;
					break;
				default: // We only flag valid responses
					$sSHDB = "OK Clear ";
					$spamhausspambot = false;
					break;
			} // End switch
			
			
			$status['SPAMHAUS'] = new stdClass(); 
		    $status['SPAMHAUS']->is_spam = $spamhausspambot;
			$status['SPAMHAUS']->extra = $sSHDB.' ('.$spamhaustemp.')';
			
			}
			
			if ((empty($this->params->get('SPAMCOP', false))) || ($this->params->get('SPAMCOP', 2) == '2'))  {
			$lookup = $rev.'.bl.spamcop.net.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['SPAMCOP'] = new stdClass(); 
				$status['SPAMCOP']->is_spam = true;
				$status['SPAMCOP']->extra = $lookup.' ('.$returned_lookup.')';
				
			} 
			}
			if ((empty($this->params->get('DroneBL', false))) || ($this->params->get('DroneBL', 2) == '2'))  {
			$lookup = $rev.'.dnsbl.dronebl.org.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['DroneBL'] = new stdClass(); 
				$status['DroneBL']->is_spam = true;
				$status['DroneBL']->extra = $lookup.' ('.$returned_lookup.')';
			} 
			}
			
			
			if ((empty($this->params->get('RBL_STOPFORUMSPAM', false))) || ($this->params->get('RBL_STOPFORUMSPAM', 2) == '2'))  {
			$lookup = $rev.'.i.rbl.stopforumspam.org.';
			$returned_lookup = gethostbyname($lookup);
			$ea = explode('.', $returned_lookup); 
			$confidence = (int)$ea[3]; 
			$status['RBL_STOPFORUMSPAM'] = new stdClass(); 
			$status['RBL_STOPFORUMSPAM']->is_spam = false;
			if ($returned_lookup !== $lookup)
			{
			if ($confidence > 20) 
			{
				$status['RBL_STOPFORUMSPAM']->is_spam = true;
			}
			else {
				$confidence = ' NOT BLOCKED LOW CONFINDENCE '.$confidence; 
			}
			}
			else {
				$confidence = ''; 
			}
			
			$status['RBL_STOPFORUMSPAM']->extra = $lookup.' ('.$returned_lookup.' CONFIDENCE '.$confidence.')';
			}
			
			
			if ((empty($this->params->get('Tornevall', false))) || ($this->params->get('Tornevall', 2) == '2'))  {				
			$lookup = $rev.'.opm.tornevall.org.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['Tornevall'] = new stdClass(); 
				$status['Tornevall']->is_spam = true;
				$status['Tornevall']->extra = $lookup.' ('.$returned_lookup.')';
			} 
			}
				
			if ((empty($this->params->get('EFNet', false))) || ($this->params->get('EFNet', 2) == '2'))  {		
			$lookup = $rev.'.rbl.efnetrbl.org.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['EFNet'] = new stdClass(); 
				$status['EFNet']->is_spam = true;
				$status['EFNet']->extra = $lookup.' ('.$returned_lookup.')';
			} 
				}
			
			if ((empty($this->params->get('TOR', false))) || ($this->params->get('TOR', 2) == '2'))  {						
			$lookup = $rev.'.80.104.161.233.64.ip-port.exitlist.torproject.org.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup === "127.0.0.2")
			{
				$status['TOR'] = new stdClass(); 
				$status['TOR']->is_spam = true;
				$status['TOR']->extra = $lookup.' ('.$returned_lookup.')';
			}
			else {
				$status['TOR'] = new stdClass(); 
				$status['TOR']->is_spam = false;
				$status['TOR']->extra = $lookup.' ('.$returned_lookup.')';
			}
			}
			
			
				
			if ((empty($this->params->get('RBLDNSCOM', false))) || ($this->params->get('RBLDNSCOM', 2) == '2'))  {		
			$lookup = $rev.'.spam.rbl-dns.com.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['RBLDNSCOM'] = new stdClass(); 
				$status['RBLDNSCOM']->is_spam = true;
				$status['RBLDNSCOM']->extra = $lookup.' ('.$returned_lookup.')';
			} 
				}
				
				
				if ((empty($this->params->get('BARRACUDA', false))) || ($this->params->get('BARRACUDA', 2) == '2'))  {		
			$lookup = $rev.'.b.barracudacentral.org.';
			$returned_lookup = gethostbyname($lookup);
			if ($lookup !== $returned_lookup)
			{
				$status['BARRACUDA'] = new stdClass(); 
				$status['BARRACUDA']->is_spam = true;
				$status['BARRACUDA']->extra = $lookup.' ('.$returned_lookup.')';
			} 
				}
				
				
				
			
			
	}
	
	private function getUrl($url, $post) {
		

// init the request, set some info, send it and finally close it
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); 
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000); 
$result = curl_exec($ch);
$e = curl_error($ch); 
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);
if ($httpcode !== 200) {
	return 'Error '.$httpcode; 
}
if (!empty($e)) return strip_tags($e); 
return $result;
		
	}
	
	private function spamlog($reason=array())
	{
 
	    $app = JFactory::getApplication(); 
		if ($app->getName() != 'site') {
			return;
		}
	   
	   $arr = array(); 
	   if (isset($_GET))
	   $arr['_GET'] = $_GET; 
	   else $arr['_GET'] = array();  
	   
	   if (isset($_POST))
	   $arr['_POST'] = $_POST; 
	   else $arr['_POST'] = array(); 
	   
	   if (isset($_REQUEST))
	   $arr['_REQUEST'] = $_REQUEST; 
	   else $arr['_REQUEST'] = array(); 
	   
	   if (isset($_COOKIE))
	   $arr['_COOKIE'] = $_COOKIE; 
	   else $arr['_COOKIE'] = array(); 
	   
	   if (isset($_SERVER))
	   $arr['_SERVER'] = $_SERVER; 
	   else $arr['_SERVER'] = array(); 
	   
	   
	  
	  
	  
	  
	   $this->filterData($arr); 
	  
	   $this->sendEmail($arr, $reason); 
	   
	}
	
	
	private function sendEmail($arr, $reason) {
		
		$config = JFactory::getConfig();	
	  if (method_exists($config, 'getValue'))
	  $sender = array( $config->getValue( 'config.mailfrom' ), $config->getValue( 'config.fromname' ) );
	  else
	  $sender = array( $config->get( 'mailfrom' ), $config->get( 'fromname' ) );
	  
	  
	   
	      $email = $sender[0]; 
	   
	  
	  if (!empty($email))
	  {
	    $mailer = JFactory::getMailer();
		
		$admin_mail = $this->params->get('spam_admin', ''); 
		
		if (empty($admin_mail)) {
			$mailer->addRecipient( $email );
	    }
		else {
			$mailer->addRecipient( $admin_mail );
		}
		
		
		
		$subject = 'OPC Captcha: Log data available for form submission'; 
		$mailer->setSubject(  html_entity_decode( $subject) );
		$mailer->isHTML( false );
		
		$body = "OPC Captcha detected form submission: \n\n"; 
		foreach ($reason as $line) {
			$body .= $line."\n"; 
		}
		$body .= "\n\n"; 
		
	 $pageURL = 'http';
     if ((isset($_SERVER['HTTPS'])) && ($_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
     $pageURL .= "://";
     if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
     } else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
     }		
			$body .= $pageURL."\n\n"; 
			$body .= 'Additional data: '."\n"; 
			//$dataMsg = 'Name: '.$name." (".$first_name.' '.$last_name.') '."\n"; 
			//$dataMsg .= 'Email: '.$emailCust."\n\n"; 
			//$dataMsg .= 'Visit details about this user at '."\n";
			//$dataMsg .= Juri::root().'index.php?option=com_ajax&plugin=spamlog&format=raw&ip='.urlencode($_SERVER['REMOTE_ADDR'])."\n\n";
			//$body .= $dataMsg; 
			$body .= "\n\nTo disable these emails proceed to your Extensions -> Plug-in manager -> disable debug in opc_captcha plugin \n";
			$body .= "\n"; 
			foreach ($arr as $key => $val) {
				$body .= $key.': '.var_export($val, true); 
			}
			
		
		
		
		
		$mailer->setBody( $body );
		$mailer->setSender( $sender );
		$res = $mailer->Send();
		
		
	}
	}
	
	private function filterData(&$data) {
		$w = $this->params->get('filter_words', 'password,password2,opc_password,opc_password2,cc_number,cc_num,cc_number_,cc_cvv_'); 
		$we = explode(',', $w); 
		
		foreach ($we as $kkx=>$filterword) {
			$filterword = trim($filterword); 
			foreach ($data as $k=>&$v) {
				foreach ($v as $k2=>&$v2) {
					if ($k2 == $filterword) {
						unset($data[$k][$k2]); 
						continue; 
					}
					if (stripos($k2, $filterword)!==false) {
						unset($data[$k][$k2]); 
						continue; 
					}
					
				}
			}
		}
	}
	
	
	
	

}




