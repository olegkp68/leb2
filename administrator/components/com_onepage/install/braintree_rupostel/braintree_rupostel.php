<?php
/**
 *
 * @author stAn, RuposTel.com
 * @version $Id: braintree_rupostel.php 
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

class plgVmpaymentBraintree_rupostel extends vmPSPlugin
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
		
		$this->sandbox_merchant_id = $this->params->get('sandbox_merchant_id', ''); 
		$this->sandbox_public_key = $this->params->get('sandbox_public_key', ''); 
		$this->sandbox_private_key = $this->params->get('sandbox_private_key', ''); 
		$this->merchant_id = $this->params->get('merchant_id', ''); 
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
		   plgVmpaymentBraintree_rupostel::$currentMethod = $method; 
		   
		   
		   return;
		 }
		
	}
	
	function printTokenJson($m) {
		$error = ''; 
		$clientToken = ''; 
		try {
						$clientToken = $this->getBraintreeToken($m); 
						
						@header("HTTP/1.1 201 Created");
						@header('Content-Type: text/html; charset=utf-8');
						@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
						@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
						@header("Content-type: application/json; charset=utf-8");
					}
					catch(Exception $e) {
						$error = (string)$e; 
					}
			$ret = new stdClass(); 
			$ret->clientToken = $clientToken; 
			$ret->error = $error; 
			echo json_encode($ret); 
			JFactory::getApplication()->close(); 
	}
	
	function webhookNotification($m, $bt_signature, $bt_payload) {
		
		$gateway = $this->getGateway($m); 
		$msgs = array(); 
		if (!empty($bt_signature) && (!empty($bt_payload))) {
		try {
			$webhookNotification = $gateway->webhookNotification()->parse($bt_signature, $bt_payload);
		}
		catch(Exception $e) {
			throw new Exception($e); 
			JFactory::getApplication()->close(); 
		}

		// Example values for webhook notification properties
		$kind = $webhookNotification->kind; // "subscription_went_past_due"
		
		if ($kind === 'dispute_opened') {
				
				$new_status = $m->payment_dispute_status;
				
				
				$modelOrder = VmModel::getModel('orders');
				
				$transaction_id = $webhookNotification->dispute->transaction->id; 
				$db = JFactory::getDBO(); 
				$q = 'select `virtuemart_order_id` from `'.$this->_tablename.'` where `braintree_response_transaction_id` = \''.$db->escape($transaction_id).'\' and `virtuemart_order_id` > 0 order by `modified_on` desc limit 1'; 
				$db->setQuery($q); 
				$virtuemart_order_id = (int)$db->loadResult();
				if (!empty($virtuemart_order_id)) {
					$order = $modelOrder->getOrder($virtuemart_order_id); 
					$order_status = $order['details']['BT']->order_status; 
					if ($order_status !== $new_status) {
					$order['order_status'] = $new_status;
					$order['customer_notified'] = 1;
					$order['comments'] = '';
					$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, TRUE);
					
					$msgs[] = 'Updated Order ID '.(int)$virtuemart_order_id.' with status code '.$new_status;
					}
					
				}
				else {
					throw new Exception('Order ID not found for transaction '.$transaction_id); 
				}
		}
		elseif ($kind === 'payment_method_revoked_by_customer') {
			//this is only when the customer changes payment method
		}
		elseif ($kind === 'transaction_settlement_declined') {
				//this shoudl work only for ACH
				$new_status = $m->payment_declined_status;
				$modelOrder = VmModel::getModel('orders');
				
				$transaction_id = $webhookNotification->transaction->id; 
				$db = JFactory::getDBO(); 
				$q = 'select `virtuemart_order_id` from `'.$this->_tablename.'` where `braintree_response_transaction_id` = \''.$db->escape($transaction_id).'\' and `virtuemart_order_id` > 0 order by `modified_on` desc limit 1'; 
				$db->setQuery($q); 
				$virtuemart_order_id = (int)$db->loadResult();
				if (!empty($virtuemart_order_id)) {
					$order = $modelOrder->getOrder($virtuemart_order_id); 
					$order['order_status'] = $new_status;
					$order['customer_notified'] = 1;
					$order['comments'] = '';
					$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, TRUE);
					
					$msgs[] = 'Updated Order ID '.(int)$virtuemart_order_id;
				}
				else {
					throw new Exception('Order ID not found for transaction '.$transaction_id); 
				}
		}
		
		if (!empty($m->log_webhook)) {
		
		$message = $webhookNotification->timestamp->format('D M j G:i:s T Y')."\n";
		
		
		$logfile = JPATH_SITE."/log/webhook.log.php"; 
		$prefix = "\n"; 
		
		if (!file_exists($logfile)) {
			$prefix = '<?php throw new Exception(\'Access denied\'); die(1); exit(1);'."\n";
			$data = $prefix.$message.var_export($webhookNotification, true)."\n".var_export($msgs, true);
			file_put_contents($logfile, $data);
		}
		else {
			$data = $prefix.$message.var_export($webhookNotification, true)."\n".var_export($msgs, true);
			file_put_contents($logfile, $data, FILE_APPEND);
		}
		}
		 
		}
		else {
			if (!empty($m->log_webhook)) {
			$logfile = JPATH_SITE."/log/webhook.log.php"; 
			$prefix = "\n"; 
			
		if (!file_exists($logfile)) {
			$prefix = '<?php throw new Exception(\'Access denied\'); die(1); exit(1);'."\n";
			$data = $prefix.$message.var_export($_GET, true).' '.var_export($_POST, true);
			file_put_contents($logfile, $data);
		}
		else {
			$data = $prefix.$message.var_export($_GET, true).' '.var_export($_POST, true);
			file_put_contents($logfile, $data, FILE_APPEND);
		}
			}
			throw new Exception('RuposTel BrainTree Plugin - Empty input parameters for BrainTree Webhook'); 
			
		}
		
		echo json_encode($msgs); 
		header("HTTP/1.1 200 OK");
		JFactory::getApplication()->close(); 
		
	}
	function testWebHook($m) {
		$gateway = $this->getGateway($m); 
		$sampleNotification = $gateway->webhookTesting()->sampleNotification(
			Braintree_WebhookNotification::DISPUTE_OPENED,
			'7xwwy2s7'
		);
		return $this->webhookNotification($m, $sampleNotification['bt_signature'], $sampleNotification['bt_payload']); 
		
$webhookNotification = $gateway->webhookNotification()->parse(
    $sampleNotification['bt_signature'],
    $sampleNotification['bt_payload']
);

var_dump($sampleNotification); die(); 

$webhookNotification->subscription->id;
		header("HTTP/1.1 200 OK");
		JFactory::getApplication()->close(); 
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
				throw new Exception('BrainTree plugin - webhook secret is not correct. Please check your webhook URL.');
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
				throw new Exception('BrainTree plugin - webhook secret is not correct. Please check your webhook URL.');
			}
			
		}
		
		throw new Exception('RuposTel BrainTree unsupported command'); 
		
		
		
		
		
	}
	static $scriptLoaded; 
	public function getOpcJavascript($m)
	{
		if (!empty(self::$scriptLoaded)) return; 
		
		 
		
		$done = true; 
		$h = ''; 
		
		JHtml::script('https://js.braintreegateway.com/web/dropin/1.22.0/js/dropin.min.js'); 
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		JHtml::script($root.'plugins/vmpayment/braintree_rupostel/braintree_rupostel/braintree.js'); 
		
		
		
		JFactory::getLanguage()->load('com_virtuemart');
		JFactory::getLanguage()->load('plg_vmpayment_braintree_rupostel', dirname(__FILE__).DIRECTORY_SEPARATOR);
		
		
		
			ob_start(); 
		?>
				 <input type="hidden" name="braintree_currency_iso" id="braintree_currency_iso" value="<?php echo htmlentities($this->getCurrencyISO($cart)); ?>" />
				 <input <?php 
				 
				 $merchantAccountId = $this->getMerchantIdPercurrency($m, $cart); 
				 echo ' data-currentid="'.htmlentities($merchantAccountId).'" '; 
				 ?> data-tokenurl="<?php 
				 echo htmlentities(JRoute::_('index.php?option=com_virtuemart&view=vmplg&task=pluginNotification&virtuemart_paymentmethod_id='.(int)$m->virtuemart_paymentmethod_id.'&cmd=gettoken&nosef=1&time='.time(), false));
				 
				 
				 ?>" type="hidden" name="clienttoken" id="braintree_clienttoken" value="" /><?php
				 $h = ob_get_clean(); 
				
		
	    
		if (empty(OPCloader::$extrahtml_insideform)) OPCloader::$extrahtml_insideform = ''; 
		OPCloader::$extrahtml_insideform .= $h; 
		
		self::$scriptLoaded = true; 
		
	}
	
	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {
		
		if (!empty(self::$scriptLoaded)) return; 
				
		
		
		JHtml::script('https://js.braintreegateway.com/web/dropin/1.22.0/js/dropin.min.js'); 
		/*
		JHtml::script('https://js.braintreegateway.com/web/3.58.0/js/client.min.js'); 
		
		
		JHtml::script('https://pay.google.com/gp/p/js/pay.js'); 
		
		JHtml::script('https://www.paypalobjects.com/api/checkout.js');
		JHtml::script('https://js.braintreegateway.com/web/3.58.0/js/data-collector.min.js'); 
		
		
		JHtml::script('https://js.braintreegateway.com/web/3.58.0/js/paypal.min.js'); 
		JHtml::script('https://js.braintreegateway.com/web/3.58.0/js/paypal-checkout.min.js'); 
		JHtml::script('https://js.braintreegateway.com/web/3.58.0/js/google-payment.min.js');
		*/
		//JHtml::script('https://js.braintreegateway.com/web/3.57.0/js/payment-request.min.js');
		//https://developers.braintreepayments.com/guides/google-pay/client-side/javascript/v3
		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		JHtml::script($root.'plugins/vmpayment/braintree_rupostel/braintree_rupostel/braintree.js'); 
		
		
		
		JFactory::getLanguage()->load('com_virtuemart');
		JFactory::getLanguage()->load('plg_vmpayment_braintree_rupostel', dirname(__FILE__).DIRECTORY_SEPARATOR);
				
	}
	
	private function getGateway($m) {
		
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'braintree_rupostel'.DIRECTORY_SEPARATOR."braintree".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
			static $gateway; 
			if (!empty($gateway)) return $gateway; 
			if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					
					$environment = 'production'; 
					$prefix = '';
				}
			
			$merchangeId = $this->{$prefix.'merchant_id'};
			//$this->getMerchantIdPercurrency($m); 
			
			$gateway = new Braintree_Gateway([
    'environment' => $environment,
    'merchantId' => $merchangeId,
    'publicKey' => $this->{$prefix.'public_key'},
    'privateKey' => $this->{$prefix.'private_key'}
	
		]);
		return $gateway; 
	}
	
	public function getBraintreeToken($m) {
		
		$clientTokenTest = JRequest::getVar('clienttoken', ''); 
		if (!empty($clientTokenTest)) return $clientTokenTest; 
			
		static $clientToken; 
		if (!empty($clientToken)) return $clientToken; 
		
		$gateway = $this->getGateway($m); 
		
		
				 $merchantAccountId = $this->getMerchantIdPercurrency($m, $cart); 
				if (!empty($merchantAccountId)) {
					$clientToken = $gateway->clientToken()->generate(array('merchantAccountId'=>$merchantAccountId));
				}
				else {
					$clientToken = $gateway->clientToken()->generate();
				}
				return $clientToken;
	}

	protected function getVmPluginCreateTableSQL ()
	{
		return $this->createTableSQL('Payment BrainTree Table');
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
			'braintree_response_authorization_code' => 'char(10)',
			'braintree_response_transaction_id' => 'varchar(128)',
			'session_id' => 'varbinary(192)',
			'braintree_response_response_subcode' => 'char(13)',
			'braintree_response_response_reason_code' => 'char(10)',
			'braintree_response_response_reason_text' => 'text',
			'braintree_response_transaction_type' => 'char(50)',
			'braintree_response_account_number' => 'char(4)',
			'braintree_response_card_type' => 'char(128)',
			'braintree_response_card_code_response' => 'char(5)',
			'braintree_response_cavv_response' => 'char(1)',
			'braintree_response_raw' => 'text',
			'braintree_hash'=>'char(50)'
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
		
		require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'braintree_rupostel'.DIRECTORY_SEPARATOR."braintree".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
		
		
		
		
		$method_name = $this->_psType . '_name';

		
		
		
		
		
		
		$htmla = array(); 
		
		foreach ($this->methods as $this->_currentMethod) {
		
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
				
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
				$html = str_replace($search, ' data-braintree="1" '.$search, $html); 
				$m = $this->_currentMethod; 
				if (!empty($m->sandbox)) {
					$environment = 'sandbox'; 
					$prefix = 'sandbox_';
				}
				else {
					$environment = 'live'; 
					$prefix = '';
				}
				
				
				static $once; 
				
				if (empty($once)) {
					try {
						/*stAn - do not slow down here:
						$clientToken = $this->getToken($m); 
						*/
						$clientToken = ''; 
					}
					catch(Exception $e) {
						$html .= (string)$e; 
					}
				
				if (strpos($html, 'braintree-dropin-container' === false)) {
				 $html .= '<br class="br_br"/><div style="box-sizing:border-box;" id="braintree-dropin-container"></div>'; 
				}
					$html .= '
				  <input '; 
				  $merchantAccountId = $this->getMerchantIdPercurrency($m, $cart); 
				 $html .= ' data-currentid="'.htmlentities($merchantAccountId).'" data-tokenurl="'.htmlentities(json_encode(JRoute::_('index.php?option=com_virtuemart&view=vmplg&task=pluginNotification&virtuemart_paymentmethod_id='.(int)$m->virtuemart_paymentmethod_id.'&cmd=gettoken&time='.time()))).'" '; 
				 $html .= ' type="hidden" name="clienttoken" id="braintree_clienttoken" value="'.htmlentities($clientToken).'" />'; 
				 $html .= ' <input type="hidden" name="braintree_currency_iso" id="braintree_currency_iso" value="'.htmlentities($this->getCurrencyISO($cart)).'" />'; 
				 $once = true; 
				 
				 
				
				} 
				
				
				if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
					
				}

				
				
				$sandbox_msg = "";
				if ($this->_currentMethod->sandbox) {
					$sandbox_msg .= '<br />BrainTree is in sandbox mode. Use credit cards provided by the sandbox (Visa: 4444333322221111 OR 424212132325454 , MasterCard: 5105105105105100). To get get a confirmed transaction the order total must be rounded to zero decimals.'; 
				}

				
				


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
		$session->set('braintreedata', $dt , 'vm');
		
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
		
		$braintree_nounce = JRequest::getVar('braintree_nounce', ''); 
		
		$task = JRequest::getVar('task', ''); 
		if (!(($task === 'confirm') || ($task === 'checkout'))) return null; 
		
		
		
		/*
		$x = debug_backtrace(); 
		foreach ($x as $l) echo $l['file'].' '.$l['line']."<br />"; 
		die(); 
		*/
		$amount = JRequest::getVar('braintree_amount'); 
		if (empty($amount)) return null; 
		
		$gateway = $this->getGateway($m); 
		
		$deviceDataFromTheClient = JRequest::getVar('braintree_devicedata', ''); 
		$paymentData = [
  'amount' => $amount,
  'paymentMethodNonce' => $braintree_nounce,
  
  'options' => [
    'submitForSettlement' => false
  ]]; 
  
  if (!empty($deviceDataFromTheClient)) {
		$paymentData['deviceData'] = $deviceDataFromTheClient;
  }
  
    $customer = array(); 
	$customer['firstName'] = $cart->BT['first_name'];
    $customer['lastName'] = $cart->BT['last_name'];
    if (!empty($cart->BT['company'])) {
		$customer['company'] = $cart->BT['company']; 
	}
	$customer['email'] = $cart->BT['email']; 
	$customer['phone'] = $cart->BT['phone_1']; 
	$paymentData['customer'] = $customer; 
  
	$billing = array(); 
	$billing['firstName'] = $customer['firstName']; 
	$billing['lastName'] = $customer['lastName']; 
	if (!empty($customer['company'])) {
		$billing['company'] = $customer['company']; 
	}	
	$billing['streetAddress'] = $cart->BT['address_1']; 
	if (!empty($cart->BT['address_2'])) {
		$billing['extendedAddress'] = $cart->BT['address_2']; 
	}
	elseif (!empty($cart->BT['house_nr'])) {
		$billing['extendedAddress'] = $cart->BT['house_nr']; 
	}
	elseif (!empty($cart->BT['house_addon'])) {
		$billing['extendedAddress'] = $cart->BT['house_addon']; 
	}
	$billing['locality'] = $cart->BT['city']; 
	$stateModel = VmModel::getModel('state'); 
	if (!empty($cart->BT['virtuemart_state_id'])) {
		$state = $stateModel->getSingleState($cart->BT['virtuemart_state_id']); 
		if (!empty($state)) {
			$state_2 = $state->state_2_code; 
		}
	}
	else {
		$state_2 = ''; 
	}
	$country_id = $cart->BT['virtuemart_country_id']; 
	$db = JFactory::getDBO(); 
	$q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$country_id; 
	$db->setQuery($q); 
	$country_2 = $db->loadResult(); 
	
	if (!empty($state_2)) {
		$billing['region'] = $state_2; 
	}
	$billing['countryCodeAlpha2'] = $country_2; 
    
	$paymentData['billing'] = $billing; 
	
	
	$merchantAccountId = $this->getMerchantIdPercurrency($m, $cart); 
	
	if (!empty($merchantAccountId)) {
		$paymentData['merchantAccountId'] = $merchantAccountId; 
	}
	
	try {
	$result = $gateway->transaction()->sale($paymentData); 
	}
	catch(Exception $e) {
		$er = $e->getMessage(); 
		if (empty($r)) {
			$er = 'Generic Error'; 
		}
		$app = JFactory::getApplication();
		$app->enqueueMessage($er);
		return false; 
		
	}
	
	
	$m->log_response = (int)$m->log_response; 
	
	if (empty($m->log_response)) {
		$xo = new stdClass(); 
		$xo->transaction = new stdClass(); 
		$xo->transaction->currencyIsoCode = $result->transaction->currencyIsoCode;
		$xo->transaction->id = $result->transaction->id;
		$xo->success = $result->success; 
		$xo->errors = $result->errors;
		$xo->transaction->amount  = $result->transaction->amount; 
		$xo->transaction->processorResponseCode = $result->transaction->processorResponseCode;
		$xo->transaction->processorResponseText = $result->transaction->processorResponseText;
		$result = $xo; 
		$this->transactionResult = $xo; 
	}
	
	$this->transactionResult = $result; 
	
	if ($m->log_response !== 1) {
		unset($result->transaction->creditCard); 
		unset($result->transaction->creditCardDetails); 
	}
	
	$currency_iso = $result->transaction->currencyIsoCode; 
	$payment_currency_id = shopFunctions::getCurrencyIDByName($currency_iso);
	$dbValues = array(); 
	$dbValues['payment_name'] = $plgname = $m->payment_name; 
	//$this->renderPluginName($this->_currentMethod); //parent::renderPluginName($this->_currentMethod);
	$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
	$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
	$dbValues['payment_currency'] = $this->_currentMethod->payment_currency = $payment_currency_id;
	$dbValues['payment_order_total'] = $amount;
	$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
	$dbValues['brantree_response_raw'] = $raw; 
	$dbValues['braintree_response_transaction_id'] = $result->transaction->id;
	$dbValues['virtuemart_order_id'] = 0;
	$session = JFactory::getSession(); 
	$dbValues['session_id'] = $session->getId();;
	$this->storePSPluginInternalData($dbValues);

	if ($result->success) {
		
		
		
		return true; 
		
	}
	else {
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'errors.php'); 
		$session = JFactory::getSession(); 
		$sid = $session->getId(); 
		$json = new stdClass(); 		
		$json->brainTreeResponse = $result; 
		$json->session_id = $sid; 
				
				$cartSession = $session->get('vmcart', 0, 'vm');
				if (!empty($cartSession)) {
					$cartJson = json_decode($cartSession); 
					if (!empty($cartJson)) {
						$json->cart = $cartJson; 
					}
				}
				$msg = 'BrainTree Failed Transaction'; 
				$task = 'brainTree'; 
				OPCerrors::store($msg, (array)$json, $task); 
		
		$textkey = 'PLG_VMPAYMENT_BRAINTREE_RUPOSTEL_PROCESSORERROR_'.$result->transaction->processorResponseCode;
		$test = JText::_($textkey); 
		if ($test !== $textkey) {
			//create custom error codes per error number returned: PLG_VMPAYMENT_BRAINTREE_RUPOSTEL_PROCESSORERROR_2000
			$app = JFactory::getApplication();
			$app->enqueueMessage($test, 'error');
			
			return false; 
		}
		
		foreach($result->errors->deepAll() AS $error) {
			$html .= $error->code . ": " . $error->message . "<br />\n";
		}
		$app = JFactory::getApplication();
		$app->enqueueMessage($html);
		
		return false; 
	}
		
		
        
		

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
		$virtuemart_currency_id = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', $cart->pricesCurrency) );
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
		$html .= $this->getHtmlRowBE('Transaction ID', $paymentTable->braintree_response_transaction_id);
		
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
		$html .= $this->getHtmlRowBE('Transaction ID', $paymentTable->braintree_response_transaction_id);
		
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
		$id = $this->transactionResult->transaction->id; 
		
		$gateway = $this->getGateway($m); 
		
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
		
		$result = $gateway->transaction()->submitForSettlement(
			$id, null, [
			  'orderId' => $order['details']['BT']->virtuemart_order_id
			]
			
			);
		
		if ($result->success) {
			$new_status = $this->_currentMethod->payment_approved_status;
		}
		else {
			$new_status = $this->_currentMethod->payment_declined_status;
		}
		
		$usrBT = $order['details']['BT'];
		$usrST = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);
		$session = JFactory::getSession();
		
		 
		$currency_iso = $this->transactionResult->transaction->currencyIsoCode; 
		$amount  = $this->transactionResult->transaction->amount; 
		
		$m->log_response = (int)$m->log_response; 
		
		if ($method->log_response !== 1) {
			unset($this->transactionResult->transaction->creditCard); 
			unset($this->transactionResult->transaction->creditCardDetails); 
			unset($result->transaction->creditCard); 
			unset($result->transaction->creditCardDetails); 
		}
		
		
		if (empty($m->log_response)) {
			$xo = new stdClass(); 
			$xo->transaction = new stdClass(); 
			$xo->transaction->currencyIsoCode = $result->transaction->currencyIsoCode;
			$xo->transaction->id = $result->transaction->id;
			$xo->success = $result->success; 
			$xo->errors = $result->errors;
		
			$result = $xo; 
		
		}
		
		$resultObj = new stdClass(); 
		$resultObj->transactionResult = $this->transactionResult; 
		$resultObj->settlementResult = $result; 
		
		$raw = json_encode($resultObj); 
		
		$payment_currency_id = shopFunctions::getCurrencyIDByName($currency_iso);
		//stAn, we will not check on currency in this stage
		$totalInPaymentCurrency = round($order['details']['BT']->order_total, 2); //
		
		if (!empty($order['details']['BT']->user_currency_id))
		if ($order['details']['BT']->user_currency_id != $payment_currency_id)
		{
		  $formatted = $totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$payment_currency_id);
		  $totalInPaymentCurrency = $totalInPaymentCurrency['value']; 
		}
		
		
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

		
	
	
		
		
	
	
	// Prepare data that should be stored in the database
		$dbValues = array(); 
		if (!empty($res)) {
			$dbValues['id'] = (int)$res['id']; 
		}
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		
		
		$dbValues['payment_name'] = $plgname = $m->payment_name; 
		//$this->renderPluginName($this->_currentMethod); //parent::renderPluginName($this->_currentMethod);
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_currency'] = $this->_currentMethod->payment_currency = $payment_currency_id;
		$dbValues['payment_order_total'] = $amount;
		$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
		$dbValues['braintree_response_raw'] = $raw; 
		
		
		$this->storePSPluginInternalData($dbValues);
		
	
		
	
		$modelOrder = VmModel::getModel('orders');
		$order['order_status'] = $new_status;
		$order['customer_notified'] = 1;
		$order['comments'] = '';
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
