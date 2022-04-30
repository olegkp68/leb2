<?php
/**
 *
 * @author stAn, RuposTel.com
 * @version $Id: stripe_rupostel.php 
 * @package brainTree Payment Plugin
 * @subpackage payment
 * @copyright Copyright (C) RuposTel.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * brainTree Payment is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * Based on Authorize.net plugin by Virtuemart.net team
 *
 * http://rupostel.com
 */
defined('_JEXEC') or die('Restricted access');


if (!class_exists('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS .DIRECTORY_SEPARATOR. 'vmpsplugin.php');
}

class plgVmpaymentStripe_rupostel extends vmPSPlugin
{

	private $_cc_name = '';
	private $_cc_type = '';
	private $_cc_number = '';
	private $_cc_cvv = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';
	private $_cc_valid = FALSE;
	private $_errormessage = array();
	private $_cc_cardholder = ''; 
	
	public $approved;
	public $declined;
	public $error;
	public $held;

	const APPROVED = 1;
	const DECLINED = 2;
	const ERROR = 3;
	const HELD = 4;

	public static $currentMethod; 
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	// instance of class
	function __construct (& $subject, $config)
	{

		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		
		
		$this->tableFields = array_keys($this->getTableSQLFields());
		$varsToPush = $this->getVarsToPush();

		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
		
		
		$this->sandbox_public_key = $this->params->get('sandbox_public_key', ''); 
		$this->sandbox_private_key = $this->params->get('sandbox_private_key', ''); 
		
		$this->public_key = $this->params->get('public_key', ''); 
		$this->private_key = $this->params->get('private_key', ''); 
		
		//error_log(E_ALL); 
		ini_set('display_erros', 1); 
		
		
if (!class_exists ('calculationHelper')) {
  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'calculationh.php');
}
if (!class_exists ('CurrencyDisplay')) {
  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
}
if (!class_exists ('VirtueMartModelVendor')) {
  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. 'vendor.php');
}	

		VmConfig::loadJLang('plg_vmpayment_authorizenet',TRUE); 
		 JFactory::getLanguage()->load('plg_vmpayment_authorizenet', JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'vmpayment'.DIRECTORY_SEPARATOR.'authorizenet');

		
		if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
		$cart = VirtueMartCart::getCart();
		
		
		if (empty($cart->vendorId)) $vId = 1; 
		else $vId = $cart->vendorId; 
		if ($this->getPluginMethods($vId) === 0) {
			
			
			return;
		}
		
		foreach ($this->methods as $method)
		 {
		   plgVmpaymentStripe_rupostel::$currentMethod = $method; 
		   
		   
		   return;
		 }
		 
		 
		 
		
	}
	function getPaymentIntent($m, $order_total, $currency_2_code) {
		try 
		{
		require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'stripe_rupostel'.DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
			$private_key = $this->getPrivateKey($m); 
			\Stripe\Stripe::setApiKey($private_key);

$intent = \Stripe\PaymentIntent::create([
  'amount' => $order_total,
  'currency' => $currency_2_code,
  // Verify your integration in this guide by including this parameter
  'metadata' => ['integration_check' => 'accept_a_payment'],
]);
		return $intent; 
		} catch (Exception $e) {
			return ''; 
		}
	}
	function printTokenJson($m) {
		
		$error = ''; 
		$clientToken = new stdClass(); 
		try {
			
			
						$clientToken = $this->getStripeToken($m); 
						
						@header("HTTP/1.1 201 Created");
						@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
						@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
						@header("Content-type: application/json; charset=utf-8");
					}
					catch(Exception $e) {
						
						@header("HTTP/1.1 201 Created");
						@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
						@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
						@header("Content-type: application/json; charset=utf-8");
						
						$error = (string)$e; 
					}
			$ret = new stdClass(); 
			$ret->clientToken = $clientToken; 
			$ret->error = $error; 
			echo json_encode($ret); 
			JFactory::getApplication()->close(); 
	}
	private function getOrderDetailsByPaymentIntentId($id) {
		$db = JFactory::getDBO(); 
		$q = 'select p.`virtuemart_order_id`, o.order_status from `'.$this->_tablename.'` as p inner join #__virtuemart_orders as o on p.virtuemart_order_id = o.virtuemart_order_id where `stripe_response_transaction_id` = \''.$db->escape($id).'\' and `virtuemart_order_id` > 0 order by `modified_on` desc limit 1'; 
		$db->setQuery($q); 
		$data = (int)$db->loadAssoc();
		if (empty($data)) { 
			return array('virtuemart_order_id'=>0); 
		}
		
		return (array)$data; 
	}
	function webhookNotification($m, $bt_signature, $bt_payload) {
		
				require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'stripe_rupostel'.DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
		
		// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $this->params->get('webhooklinksecret', '');
if (empty($endpoint_secret)) return; 

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}
if (isset($event->data->object->id)) {
	$orderData = $this->getOrderDetailsByPaymentIntentId($event->data->object->id); 

    $virtuemart_order_id = (int)$orderData['virtuemart_order_id'];
if (!empty($orderData['virtuemart_order_id']))
{
	
if ($event->type == "payment_intent.succeeded") {
    $intent = $event->data->object;
	
	$new_status = $m->payment_approved_status;
	$error_message = ''; 
   
} elseif ($event->type == "payment_intent.payment_failed") {
    $intent = $event->data->object;
    $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
    $new_status = $m->payment_declined_status;
}

$modelOrder = VmModel::getModel('orders');
	if ($orderData['order_status'] !== $new_status) {
					$order = $modelOrder->getOrder($virtuemart_order_id); 
					$order['order_status'] = $new_status;
					$order['customer_notified'] = 1;
					$order['comments'] = $error_message;
					$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, TRUE);
					
					$msgs[] = 'Updated Order ID '.(int)$virtuemart_order_id.' with status code '.$new_status;
					}
					
    printf("Succeeded: %s", $intent->id);
    http_response_code(200);
    exit();					
					
	}
}
return; 
		
		
		
	}
	function testWebHook($m) {
		
	}
	
	function plgVmOnSelfCallFE($type, $name, &$render) {
		if ($type !== 'vmpayment') return; 
		if ($name !== 'stripe_rupostel') return; 
		$render = false; 
		$this->plgVmOnPaymentNotification(); 
		
	}
	
	//https://demo3.absoluteblack.cc/index.php?option=com_virtuemart&view=vmplg&task=pluginNotification&virtuemart_paymentmethod_id=
	function plgVmOnPaymentNotification() {
		
		
		
		
		$virtuemart_paymentmethod_id = JRequest::getInt('virtuemart_paymentmethod_id', 0); 
		if (!$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		$m = $this->getMethodFromCart($virtuemart_paymentmethod_id); 
		
		$cmd = JRequest::getVar('cmd', ''); 
		if ($cmd === 'gettoken') {
			return $this->printTokenJson($m); 
		}
		elseif ($cmd === 'notification') {
		    $bt_signature = JRequest::getVar('bt_signature', ''); 
		    $bt_payload = JRequest::getVar('bt_payload', ''); 
			$webhooksecret = JRequest::getVar('webhooksecret', ''); 
			$secret = JFactory::getConfig()->get('secret'); 
		    $hash = JApplication::getHash($secret); 
			if ($hash === $webhooksecret) {
				return $this->webhookNotification($m, $bt_signature, $bt_payload ); 
			}
			else {
				throw new Exception('Stripe plugin - webhook secret is not correct. Please check your webhook URL.');
			}
		}
		elseif ($cmd === 'test') {
			$webhooksecret = JRequest::getVar('webhooksecret', ''); 
			$secret = JFactory::getConfig()->get('secret'); 
		    $hash = JApplication::getHash($secret); 
			if ($hash === $webhooksecret) {
				return $this->testWebHook($m); 
			}
			else {
				throw new Exception('Stripe plugin - webhook secret is not correct. Please check your webhook URL.');
			}
			
		}
		
		throw new Exception('RuposTel Stripe unsupported command'); 
		
		
		
		
		
	}
	static $scriptLoaded; 
	public function getOpcJavascript($m)
	{
		//tell OPC to send the order via ajax JS not via POST
		/*
		static $opc_under;
		if (empty($opc_under)) $opc_under = array(); 		
		if (empty($opc_under[$m->virtuemart_paymentmethod_id])) {
			$js .= ' if (typeof opc_payment_isunder == \'undefined\') { var opc_payment_isunder = new Array(); opc_payment_isunder.push('.(int)$m->virtuemart_paymentmethod_id.');   } ';
			$doc = JFactory::getDocument(); 
			if (method_exists($doc, 'addScriptDeclaration')) {
				$doc->addScriptDeclaration($js); 
				$opc_under[$m->virtuemart_paymentmethod_id] = true; 
			}
		}
		*/
		
		if (!empty(self::$scriptLoaded)) return; 
		
		
		$done = true; 
		$h = ''; 
		
		JHtml::script('https://js.stripe.com/v3/'); 
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		JHtml::script($root.'plugins/vmpayment/stripe_rupostel/stripe_rupostel/stripe.js'); 
		
		
		JFactory::getLanguage()->load('com_virtuemart');
		JFactory::getLanguage()->load('plg_vmpayment_stripe_rupostel', dirname(__FILE__).DIRECTORY_SEPARATOR);
		
		
		
		$js = ''; 
		$l = array('PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_ERROR', 'PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PROCESSING', 'PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_EXCEPTION_ERROR', 'PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_AUTHORIZING', 'PLG_VMPAYMENT_STRIPE_RUPOSTEL_JS_PAYMENTOK'); 
		foreach ($l as $key) {
			$js .= ' var '.$key.'='.json_encode(JText::_($key)).'; '; 
		}
			$doc = JFactory::getDocument(); 
			if (method_exists($doc, 'addScriptDeclaration')) {
				$doc->addScriptDeclaration($js); 
				
			}
		
		echo $this->renderByLayout('display_payment_style', array('method'=>$m)); 
		
			ob_start(); 
		?>
				 <input type="hidden" name="stripe_currency_iso" id="stripe_currency_iso" value="<?php echo htmlentities($this->getCurrencyISO($cart)); ?>" />
				 <input type="hidden" name="stripe_token" id="stripe_token" value="" />
				 <input type="hidden" name="stripe_response" id="stripe_response" value="" />
				 <input type="hidden" name="stripe_amount" id="stripe_amount" value="" />
				 <input <?php 
				 ?> data-tokenurl="<?php 
				 $lang = vRequest::getCmd('lang', ''); 
				 $url = 'index.php?option=com_virtuemart&controller=plugin&cmd=gettoken&nosef=1&format=opchtml&name=stripe_rupostel&type=vmpayment&tmpl=component&time='.time(); 
				 //$url2 = 'index.php?option=com_virtuemart&view=vmplg&cmd=gettoken&nosef=1&format=opchtml&name=stripe_rupostel&type=vmpayment&tmpl=component&time='.time(); 
				 if (!empty($lang)) {
					 $url .= '&lang='.urlencode($lang); 
				 }
				 $url .= '&Itemid=0'; 
				 
				 $root = JUri::root(); 
				 
				 if (substr($root, -1) !== '/') $root .= '/'; 
				 $url = $root.$url; 
				 $url = htmlentities($url);
				 echo $url; 
				 
				 ?>" type="hidden" name="clienttoken" id="stripe_clienttoken" value="" /><?php
				 $h = ob_get_clean(); 
				
		
	    
		if (empty(OPCloader::$extrahtml_insideform)) OPCloader::$extrahtml_insideform = ''; 
		OPCloader::$extrahtml_insideform .= $h; 
		
		$public_key = $this->getPublicKey($m); 
		
	   
	   //JHTMLOPC::script('opc_payment_isunder.js', 'components/com_onepage/assets/js/', false);
	   
	   

		
		//$js = 'var stripe = Stripe(\''.$public_key.'\');'; 
		//$doc = JFactory::getDocument(); 
		//$doc->addScriptDeclaration($js); 
		
		self::$scriptLoaded = true; 
		
	}
	
	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {
		
		if (!empty(self::$scriptLoaded)) return; 
				
		
		JHtml::script('https://js.stripe.com/v3/'); 
		
		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		JHtml::script($root.'plugins/vmpayment/stripe_rupostel/stripe_rupostel/stripe.js'); 
		
		
		
		JFactory::getLanguage()->load('com_virtuemart');
		JFactory::getLanguage()->load('plg_vmpayment_stripe_rupostel', dirname(__FILE__).DIRECTORY_SEPARATOR);
				
	}
	
	public function getPublicKey($m) {
		if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					
					$environment = 'production'; 
					$prefix = '';
				}
				
			$publicKey = $this->{$prefix.'public_key'};
			$privateKey = $this->{$prefix.'private_key'};
			return $publicKey; 
				
	}
	private function getPrivateKey($m) {
		if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					
					$environment = 'production'; 
					$prefix = '';
				}
				
			$publicKey = $this->{$prefix.'public_key'};
			$privateKey = $this->{$prefix.'private_key'};
			return $privateKey; 
				
	}
	
	
	
	public function getStripeToken($m, $order_total='', $currency_2_code='') {
		
		$clientTokenTest = JRequest::getVar('clienttoken', ''); 
		if (!empty($clientTokenTest)) return $clientTokenTest; 
			
		static $clientToken; 
		if (!empty($clientToken)) return $clientToken; 
		
		if (empty($order_total)) {
			$order_total = JRequest::getVar('order_total', ''); 
		}
		if (empty($currency_2_code)) {
			$currency_2_code = JRequest::getVar('currency_2_code', ''); 
		}
		
		require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'stripe_rupostel'.DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
			$private_key = $this->getPrivateKey($m); 
			\Stripe\Stripe::setApiKey($private_key);

$intent = \Stripe\PaymentIntent::create([
  'amount' => $order_total,
  'currency' => strtolower($currency_2_code),
  // Verify your integration in this guide by including this parameter
  'metadata' => ['integration_check' => 'accept_a_payment'],
]);
		
		
		
				return $intent;
	}

	protected function getVmPluginCreateTableSQL ()
	{
		return $this->createTableSQL('Payment Stripe Table');
	}

	function createTableSQL ($tableComment,$tablesFields=0) {

		$query = "CREATE TABLE IF NOT EXISTS `" . $this->_tablename . "` (";
		if(!empty($tablesFields)){
			foreach ($tablesFields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . " , ";
			}
		} else {
			$SQLfields = $this->getTableSQLFields ();
			$loggablefields = $this->getTableSQLLoggablefields ();
			foreach ($SQLfields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . " , ";
			}
			foreach ($loggablefields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . ", ";
			}
		}

		$query .= "	      PRIMARY KEY (`id`),
			KEY `session_id` (`session_id`),
			KEY `virtuemart_order_id` (`virtuemart_order_id`),
			KEY `order_number` (`order_number`)
	    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='" . $tableComment . "' AUTO_INCREMENT=1 ;";
		
		
		return $query;
	}
	
	function getTableSQLFields ()
	{

		$SQLfields = array(
			'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(11) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'payment_order_total' => 'decimal(15,5) NOT NULL',
			'payment_currency' => 'int(11)',
			'return_context' => 'char(255)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'char(10)',
			'tax_id' => 'int(11)',
			'stripe_response_authorization_code' => 'char(10)',
			'stripe_response_transaction_id' => 'varchar(128)',
			'session_id' => 'varbinary(192)',
			'stripe_response_response_subcode' => 'char(13)',
			'stripe_response_response_reason_code' => 'char(10)',
			'stripe_response_response_reason_text' => 'text',
			'stripe_response_transaction_type' => 'char(50)',
			'stripe_response_account_number' => 'char(4)',
			'stripe_response_card_type' => 'char(128)',
			'stripe_response_card_code_response' => 'char(5)',
			'stripe_response_cavv_response' => 'char(1)',
			'stripe_response_raw' => 'text',
			'stripe_hash'=>'char(50)'
		);
		return $SQLfields;
	}
	
	
	
	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn)
	{

		
		if ($this->getPluginMethods($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				
				return FALSE;
			} else {
				return FALSE;
			}
		}
		
		require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'stripe_rupostel'.DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
		
		
		
		
		$method_name = $this->_psType . '_name';

		
		
		
		
		
		
		$htmla = array(); 
		
		foreach ($this->methods as $this->_currentMethod) {
			$html = ''; 
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
			//IntegrationError: Invalid value for paymentRequest(): country should be one of the following strings: AE, AT, AU, BE, BR, CA, CH, CR, CZ, DE, DK, EE, ES, FI, FR, GB, GR, HK, IE, IN, IT, JP, LT, LU, LV, MX, MY, NL, NO, NZ, PE, PH, PL, PT, RO, SE, SG, SI, SK, US
				
				$method = $this->_currentMethod; 
				
				$cartPrices = $cart->cartPrices;
				$methodSalesPrice = $this->calculateSalesPrice($cart, $this->_currentMethod, $cartPrices);

				$logo = $this->displayLogos($this->_currentMethod->payment_logos);
				$payment_cost = '';
				if ($methodSalesPrice) {
					$payment_cost = $currency->priceDisplay($methodSalesPrice);
				}
				if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
					$checked = 'checked="checked"';
				} else {
					$checked = '';
				}
				//$html = $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);
				
				$html .= $this->renderByLayout('display_payment', array(
				                                                       'plugin' => $this->_currentMethod,
																	   'method' => $this->_currentMethod,
				                                                       'checked' => $checked,
				                                                       'payment_logo' => $logo,
				                                                       'payment_cost' => $payment_cost,
																	   'stripe_rupostel' => $this
				                                                  ));
				/*
				$html = $this->renderByLayout('display_payment', array(
				                                                       'plugin' => $this->_currentMethod,
				                                                       'checked' => $checked,
				                                                       'payment_logo' => $logo,
				                                                       'payment_cost' => $payment_cost,
				                                                  ));

			   */
			
 				
				$search = 'name="virtuemart_paymentmethod_id"'; 
				
				$m = $this->_currentMethod; 
				if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					$environment = 'live'; 
					$prefix = '';
				}
				
				
				
				
							
				
				

				
				
				$sandbox_msg = "";
				

				
				


				$htmla[] = $html;
			}
		}
		
		if (!empty($htmla)) {
			$htmlIn[] = $htmla;
		}
		
		

		return TRUE;
	}
	
	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 *
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions ($cart, $method, $cart_prices)
	{
		$this->convert_condition_amount($method);
		$amount = $this->getCartAmount($cart_prices);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));
		if (!$amount_cond) {
			return FALSE;
		}
		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array($method->countries)) {
				$countries[0] = (int)$method->countries;
			} else {
				$countries = $method->countries;
			}
		}
		foreach ($countries as $k=>$c) {
			$countries[(int)$k] = (int)$c; 
		}

		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		
		
		
		if (empty($countries) || in_array( (int)$address['virtuemart_country_id'], $countries)) {
			
			return TRUE;
		}
		elseif ((!empty($countries)) && (!in_array((int)$address['virtuemart_country_id'], (int)$countries))) {
			
			return false; 
		}
			

		return true;
	}


	function _setAuthorizeNetIntoSession ()
	{

		$session = JFactory::getSession();
		$sessionAuthorizeNet = new stdClass();
		// card information
		$sessionAuthorizeNet->cc_cardholder = $this->_cc_cardholder;
		$sessionAuthorizeNet->cc_type = $this->_cc_type;
		$sessionAuthorizeNet->cc_number = $this->_cc_number;
		$sessionAuthorizeNet->cc_cvv = $this->_cc_cvv;
		$sessionAuthorizeNet->cc_expire_month = $this->_cc_expire_month;
		$sessionAuthorizeNet->cc_expire_year = $this->_cc_expire_year;
		$sessionAuthorizeNet->cc_valid = $this->_cc_valid;
		
		$dt = $this->_prepareForSession($sessionAuthorizeNet); 
		$s2 = $this->_getDecodeData($dt); 
		$session->set('stripedata', $dt , 'vm');
		
	}
	
	
	
	
	function getMethodFromCart($virtuemart_paymentmethod_id) {
		if (empty($this->methods)) {
			$cart = VirtuemartCart::getCart(); 
		if (empty($cart->vendorId)) $cart->vendorId = 1; 
		if (method_exists($this, 'getPluginMethodsOPC')) {
			$mymethods = $this->getPluginMethodsOPC($cart->vendorId);
		}
		else 
		{
			$mymethods = $this->getPluginMethods($cart->vendorId);
		}
		}
		
		foreach ($this->methods as $m) {
			$mid = (int)$m->virtuemart_paymentmethod_id; 
			$ms = (int)$virtuemart_paymentmethod_id; 
			if ($ms === $mid) {
					return $m; 
			}
		}
		return null; 
	}
	
	/**
	 * This is for checking the input data of the payment method within the checkout
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmOnCheckoutCheckDataPayment (VirtueMartCart $cart)
	{

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		
		$m = $this->getMethodFromCart($cart->virtuemart_paymentmethod_id); 
		$this->_currentMethod = $m; 
		
		$stripe_nounce = JRequest::getVar('stripe_nounce', ''); 
		
		$task = JRequest::getVar('task', ''); 
		if (!(($task === 'confirm') || ($task === 'checkout'))) return null; 
		
		$stripe_response = JFactory::getApplication()->input->post->get('stripe_response', '','raw');
		if (empty($stripe_response)) {
			return false; 
		}
		$result = json_decode($stripe_response); 
		if (empty($result)) {
			return false; 
		}
		$this->transactionResult = $result; 
		
		$currency_iso = $result->paymentIntent->currency; 
	$payment_currency_id = shopFunctions::getCurrencyIDByName($currency_iso);
	$dbValues = array(); 
	$dbValues['payment_name'] = $plgname = $m->payment_name; 
	//$this->renderPluginName($this->_currentMethod); //parent::renderPluginName($this->_currentMethod);
	$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
	$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
	$dbValues['payment_currency'] = $this->_currentMethod->payment_currency = $payment_currency_id;
	$dbValues['payment_order_total'] = floatval($result->paymentIntent->amount) / 100;
	$dbValues['virtuemart_paymentmethod_id'] = $m->virtuemart_paymentmethod_id;
	$dbValues['stripe_response_raw'] = $stripe_response; 
	$dbValues['stripe_response_transaction_id'] = $result->paymentIntent->id;
	$dbValues['virtuemart_order_id'] = 0;
	$session = JFactory::getSession(); 
	$dbValues['session_id'] = $session->getId();;
	$this->storePSPluginInternalData($dbValues);
	
	return true; 
		
		
		
		
        
		

	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id)
	{

		return parent::onStoreInstallPluginTable($jplugin_id);
	}
	
	
	public static function getCurrentCurrency(&$cart)
	{
		
		if (empty($cart))
		{
			if (!class_exists('VirtueMartCart'))
			require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'cart.php');
			$cart = VirtuemartCart::getCart(); 
		}
		
		static $curr = 0; 
		if (!empty($curr)) return $curr;
		if (!empty($cart))
		$vendorId = $cart->vendorId; 
		else $vendorId = 1; 
		
		if (empty($vendorId)) $vendorId = 1; 
		
		$db = JFactory::getDBO();
		$q  = 'SELECT `vendor_accepted_currencies`, `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`='.$vendorId;
		$db->setQuery($q);
		$vendor_currency = $db->loadAssoc();
		$mainframe = JFactory::getApplication();
		$virtuemart_currency_id = (int)$mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', $cart->pricesCurrency) );
		if (empty($virtuemart_currency_id))
		{
			$virtuemart_currency_id = $vendor_currency['vendor_currency']; 
		}	  

		$curr = $virtuemart_currency_id; 
		return $virtuemart_currency_id; 
	}

	/**
	 * This is for adding the input data of the payment method to the cart, after selecting
	 *
	 * @author Valerie Isaksen
	 *
	 * @param VirtueMartCart $cart
	 * @return null if payment not selected; true if card infos are correct; string containing the errors id cc is not valid
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart, &$msg)
	{

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		
		$m = $this->getMethodFromCart($cart->virtuemart_paymentmethod_id); 
		
		
		return TRUE;
	}

	public function plgVmOnSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$payment_name)
	{

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		
		$cart_prices['payment_tax_id'] = 0;
		$cart_prices['payment_value'] = 0;

		if (!$this->checkConditions($cart, $this->_currentMethod, $cart_prices)) {
			return FALSE;
		}
		$payment_name = $this->renderPluginName($this->_currentMethod);

		$this->setCartPrices($cart, $cart_prices, $this->_currentMethod);

		return TRUE;
	}
	/*
		 * @param $plugin plugin
		 */

	protected function renderPluginName ($plugin)
	{

		$return = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';
		// 		$params = new JParameter($plugin->$plugin_params);
		// 		$logo = $params->get($this->_psType . '_logos');
		$logosFieldName = $this->_psType . '_logos';
		$logos = $plugin->$logosFieldName;
		if (!empty($logos)) {
			$return = $this->displayLogos($logos) . ' ';
		}
		$sandboxWarning='';
		if ($plugin->sandbox ) {
		$sandboxWarning .= ' <span style="color:red;font-weight:bold">Sandbox (' . $plugin->virtuemart_paymentmethod_id . ')</span><br />';
		}
		if (!empty($plugin->$plugin_desc)) {
			$description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc  . '</span>';
		}
		
		$extrainfo = $this->getExtraPluginNameInfo();
		$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' .$description;
		$pluginName .=  $sandboxWarning.$extrainfo;
		return $pluginName;
	}
	function plgVmOnRenderEmailPayment($virtuemart_order_id, $virtuemart_payment_id, &$html) {
		if (!($this->_currentMethod=$this->selectedThisByMethodId($virtuemart_payment_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}
		VmConfig::loadJLang('com_virtuemart');
		VmConfig::loadJLang('plg_vmpayment_authorizenet',TRUE); 
		VmConfig::loadJLang('plg_vmpayment_eway_rupostel',TRUE); 
		$html .= '<table class="adminlist" style="color:black; text-align:right;float:right;">' . "\n";
		$html .= $this->getHtmlHeaderBE();
		
		
		$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $paymentTable->payment_name);
		$html .= $this->getHtmlRowBE('Transaction ID', $paymentTable->stripe_response_transaction_id);
		
		$db = JFActory::getDBO(); 
		$q = 'select `id` from `'.$this->_tablename.'` where `session_id` = \''.$db->escape($paymentTable->session_id).'\' and `virtuemart_order_id` = 0'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (count($res) > 1) {
			JFactory::getApplication()->enqueueMessage('DANGER: There are multiple failed transactions done with the same session_id, total count: '.count($res), 'error'); 
		}
		
		$html .= $this->getHtmlRowBE('COM_VIRTUEMART_ORDER_PRINT_TOTAL', $paymentTable->payment_order_total.' '.$this->getCurrencyISObyId($paymentTable->payment_currency));
		$paymentTable->cost_per_transaction = (float)($paymentTable->cost_per_transaction);
		if (!empty($paymentTable->cost_per_transaction))
		$html .= $this->getHtmlRowBE('VMPAYMENT_AUTHORIZENET_COST_PER_TRANSACTION', $paymentTable->cost_per_transaction);
		
		if (!empty($paymentTable->cost_percent_total) && ($paymentTable->cost_percent_total!='0.00'))
		{
		if (empty($paymentTable->cost_percent_total)) $pt = '0'; 
		else $pt = $paymentTable->cost_percent_total; 
		$html .= $this->getHtmlRowBE('VMPAYMENT_AUTHORIZENET_COST_PERCENT_TOTAL', $pt);
		}
		
		
		
		
		$html .= '</table>' . "\n";
		return $html;
	}
	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $virtuemart_payment_id)
	{

		if (!($this->_currentMethod=$this->selectedThisByMethodId($virtuemart_payment_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}
		VmConfig::loadJLang('com_virtuemart');
		VmConfig::loadJLang('plg_vmpayment_authorizenet',TRUE); 
		VmConfig::loadJLang('plg_vmpayment_eway_rupostel',TRUE); 
		$html = '<table class="adminlist">' . "\n";
		$html .= $this->getHtmlHeaderBE();
		
		
		$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $paymentTable->payment_name);
		$html .= $this->getHtmlRowBE('Transaction ID', $paymentTable->stripe_response_transaction_id);
		
		$db = JFActory::getDBO(); 
		$q = 'select `id` from `'.$this->_tablename.'` where `session_id` = \''.$db->escape($paymentTable->session_id).'\' and `virtuemart_order_id` = 0'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (count($res) > 1) {
			JFactory::getApplication()->enqueueMessage('DANGER: There are multiple failed transactions done with the same session_id, total count: '.count($res), 'error'); 
		}
		
		$html .= $this->getHtmlRowBE('COM_VIRTUEMART_ORDER_PRINT_TOTAL', $paymentTable->payment_order_total.' '.$this->getCurrencyISObyId($paymentTable->payment_currency));
		$paymentTable->cost_per_transaction = (float)($paymentTable->cost_per_transaction);
		if (!empty($paymentTable->cost_per_transaction))
		$html .= $this->getHtmlRowBE('VMPAYMENT_AUTHORIZENET_COST_PER_TRANSACTION', $paymentTable->cost_per_transaction);
		
		if (!empty($paymentTable->cost_percent_total) && ($paymentTable->cost_percent_total!='0.00'))
		{
		if (empty($paymentTable->cost_percent_total)) $pt = '0'; 
		else $pt = $paymentTable->cost_percent_total; 
		$html .= $this->getHtmlRowBE('VMPAYMENT_AUTHORIZENET_COST_PERCENT_TOTAL', $pt);
		}
		
		
		
		
		$html .= '</table>' . "\n";
		return $html;
	}

	function plgVmConfirmedOrder (VirtueMartCart $cart, $order)
	{
		VmConfig::loadJLang('plg_vmpayment_authorizenet',TRUE);
		VmConfig::loadJLang('plg_vmpayment_eway_rupostel',TRUE);
		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		
		$m = $this->_currentMethod;
		$intentId = $this->transactionResult->paymentIntent->id; 
		$session = JFactory::getSession(); 
		$session_id = $session->getId();
		
		$virtuemart_order_id = $order['details']['BT']->virtuemart_order_id;
		$db = JFactory::getDBO(); 
		$q = 'select * from `'.$this->_tablename.'` where `session_id` = \''.$db->escape($session_id).'\' and `virtuemart_order_id` = 0 order by `created_on` desc limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadAssoc(); 
		if (!empty($res)) {
			$q = 'update `'.$this->_tablename.'` set `virtuemart_order_id` = '.(int)$virtuemart_order_id.', `order_number` = \''.$db->escape($order['details']['BT']->order_number).'\' where `id` = '.(int)$res['id']; 
			$db->setQuery($q); 
			$db->execute(); 
		}
		$new_status = $this->_currentMethod->payment_waiting_webook; 
		try 
		{
		// Set your secret key. Remember to switch to your live secret key in production!
// See your keys here: https://dashboard.stripe.com/account/apikeys
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'stripe_rupostel'.DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
$privateKey = $this->getPrivatekey($m); 
\Stripe\Stripe::setApiKey($privateKey);

$intent = \Stripe\PaymentIntent::retrieve($intentId);
$charges = $intent->charges->data;
		
		$order_total = number_format(floatval($order['details']['BT']->order_total), 2, '.', ''); 
		
		
		$currency_iso = $intent->currency;
		$payment_currency_id = shopFunctions::getCurrencyIDByName($currency_iso);
		//stAn, we will not check on currency in this stage
		$totalInPaymentCurrency = round($order['details']['BT']->order_total, 2); //
		
		if (!empty($order['details']['BT']->user_currency_id))
		if ($order['details']['BT']->user_currency_id != $payment_currency_id)
		{
		  $formatted = $totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$payment_currency_id);
		  $order_total = floatval($totalInPaymentCurrency['value']); 
		  $order_total = number_format(floatval($order_total), 2, '.', ''); 
		}
		$comments = ''; 
		
		$amount = number_format(floatval($intent->amount) / 100, 2, '.', ''); 
		if ($order_total === $amount) {
			
		
		foreach ($intent->charges->data as $x) {
			if ($x->paid === true) {
				$new_status = $this->_currentMethod->payment_approved_status; 
				break; 
			}
			else {
				$new_status = $this->_currentMethod->payment_waiting_webook; 
			}
			
		}
		}
		else {
			$new_status = $this->_currentMethod->payment_error; 
			$comments = 'Order total not same - '.$order_total.' vs '.$amount;
		}
		}
		catch (Exception $e) {
			$new_status = $this->_currentMethod->payment_error; 
			$comments = 'Exception: '.$e->getMessage(); 
		}
		
		
		$usrBT = $order['details']['BT'];
		$usrST = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);
		$session = JFactory::getSession();
		
		 
		 
		
		
		$m->log_response = (int)$m->log_response; 
		
		
		
		
		
		
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

		
	
	
		
		
	
		
	
		$modelOrder = VmModel::getModel('orders');
		$order['order_status'] = $new_status;
		$order['customer_notified'] = 1;
		$order['comments'] = $comments;
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, TRUE);
		//We delete the old stuff
		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
		
		$currencyDisplay = CurrencyDisplay::getInstance ($cart->pricesCurrency, $order['details']['BT']->virtuemart_vendor_id);
		
		$cart->emptyCart();
		
		$redirect = $this->_currentMethod->confirm_redirect; 
		if (!empty($redirect)) {
			JFactory::getApplication()->redirect($redirect); 
			return true; 
		}
		
		$html .= $this->renderByLayout('thankyou', array('payment' => $this->_currentMethod,'vmid'=>$this->_currentMethod->virtuemart_paymentmethod_id, 'totalInPaymentCurrencyCents'=>$totalInPaymentCurrency, 'order'=>$order, 'cart'=>$cart, 'currencyDisplay'=>$currencyDisplay, 'payment_name'=>$m->payment_name, 'currency_iso'=>$currency_iso));
		
		JRequest::setVar('html', $html);
	
	
	
	
	
	
		
	}
	function getCurrencyISO($cart) {
		$currency_id = (int)$this->getCurrentCurrency($cart); 
		$db = JFactory::getDBO(); 
		$q = 'select currency_code_3 from #__virtuemart_currencies where virtuemart_currency_id = '.(int)$currency_id; 
		$db->setQuery($q); 
		$iso = $db->loadResult(); 
		return $iso; 
	}
	function getCurrencyISObyId($currency_id) {
		
		$db = JFactory::getDBO(); 
		$q = 'select currency_code_3 from #__virtuemart_currencies where virtuemart_currency_id = '.(int)$currency_id; 
		$db->setQuery($q); 
		$iso = $db->loadResult(); 
		return $iso; 
	}

	function getMerchantIdPercurrency($m, $cart=null) {
		$currency_id = (int)$this->getCurrentCurrency($cart); 
		if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					
					$environment = 'production'; 
					$prefix = '';
				}
				//$defaultMerchantId = $this->{$prefix.'merchant_id'};
				$mc = (array)$this->params->get($prefix.'currency_merchant_id', array()); 
				foreach ($mc as $ci => $val) {
					$ci = (int)$ci; 
					if ($ci === $currency_id) {
						return $val; 
					}
				}
				
				return ''; 
	}

	function _handlePaymentCancel ($virtuemart_order_id, $html)
	{

		if (!class_exists('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. 'orders.php');
		}
		$modelOrder = VmModel::getModel('orders');
		$modelOrder->remove(array('virtuemart_order_id' => $virtuemart_order_id));
		// error while processing the payment
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage($html);
		$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment',FALSE), JText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
	}

	
	
	private function generateHash($id) {
		  if (method_exists('JUtility', 'getHash'))
	   $key = JUtility::getHash($id.'rand'); 
       else
	   $key =  JApplication::getHash($id.'rand'); 
   
       return $key; 
	}
	function getHash()
	{
	  $session = JFactory::getSession();
	  $x = JRequest::getVar('opcmc', ''); 
	  if (empty($x))
	   {
	      $x = $session->get('opcmc', ''); 
	   }
	   return $x; 
	}
	
	/**
	 * renderPluginName
	 * Get the name of the payment method
	 *
	 * @author Valerie Isaksen
	 * @param  $payment
	 * @return string Payment method name
	 */
	function getExtraPluginNameInfo ()
	{
		return ''; 
	}

	
	



	/**
	 * We must reimplement this triggers for joomla 1.7
	 */

	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */

	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $paymentCounter);
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
	{
		
		if (!($this->selectedThisByMethodId ($virtuemart_paymentmethod_id))) {
			return NULL;
		}
		$m = $this->getMethodFromCart($virtuemart_paymentmethod_id); 
		$payment_name = $m->payment_name; 
		
		
		return true; 
		//$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
		//return TRUE;
	}

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmOnShowOrderPrintPayment ($order_number, $method_id)
	{

		return parent::onShowOrderPrint($order_number, $method_id);
	}

	
	function plgVmDeclarePluginParamsPayment ($name, $id, &$data)
	{

		return $this->declarePluginParams('payment', $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table)
	{

		return $this->setOnTablePluginParams($name, $id, $table);
	}
	
	
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$paymentCurrencyId = shopFunctions::getCurrencyIDByName('AUD');
		
	}
function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}
	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetEmailCurrency($virtuemart_paymentmethod_id, $virtuemart_order_id, &$emailCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			
			return '';
		}
		if (empty($payments[0]->email_currency)) {
			$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
			$db = JFactory::getDBO();
			$q = 'SELECT   `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
			$db->setQuery($q);
			$emailCurrencyId = $db->loadResult();
		} else {
			$emailCurrencyId = $payments[0]->email_currency;
		}

	}
	
	
	
	
}

// No closing tag
