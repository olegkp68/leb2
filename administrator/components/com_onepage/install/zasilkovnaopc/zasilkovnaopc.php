<?php

defined('_JEXEC') or die('Restricted access');

if (!defined('JPATH_VM_PLUGINS'))
{
	if (!class_exists('VmConfig'))
	{
		require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	}
	
	VmConfig::loadConfig(); 
}

if (!class_exists('vmPSPlugin'))
require(JPATH_VM_PLUGINS .DIRECTORY_SEPARATOR. 'vmpsplugin.php');



class plgVmShipmentZasilkovnaopc extends vmPSPlugin
{
	
	
	
	function plgVmOnShipmentResponseReceived(&$html,&$shipmentResponse) {
		$myCmd = JRequest::getVar('cmd', ''); 
		if ($myCmd !== 'generatezasilkovna') return null; 
		static $done; 
		if (!empty($done)) return; 
		
		$plugin = JPluginHelper::getPlugin('vmshipment', 'zasilkovnaopc');
		$params = new JRegistry($plugin->params);
		$cron_key = $params->get('cronkey', ''); 
		$cron_get = JRequest::getVar('cronkey', ''); 
		if (empty($cron_key)) {
			throw new Exception('Nastavte Cron URL key v zasilkovnaopc'); 
		}
		if ($cron_key !== $cron_get) {
			throw new Exception('Nespravny Cron URL key v zasilkovnaopc'); 
			JFactory::getApplication()->close(); 
		}
		
		if (!class_exists('VirtueMartCart')) require(JPATH_SITE. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php');
		$helper = $this->getHelper(); 
		$cart = VirtuemartCart::getCart(); 
		if (empty($cart->vendorId)) $cart->vendorId = 1; 
		if (method_exists($this, 'getPluginMethodsOPC')) {
			$mymethods = $this->getPluginMethodsOPC($cart->vendorId);
		}
		else 
		{
			$mymethods = $this->getPluginMethods($cart->vendorId);
		}
		
		
		$t = 0; 
		if (!empty($this->methods)) {
		foreach ($this->methods as $method) {
			
			if (!empty($method->zasilkovna_api_pass))
			if (empty($done[$method->zasilkovna_api_pass])) {
				
				
			$x = $helper->getBranchesJson($method, 0, true); 
			$xx = (int)$x; 
			$t = $t + $xx; 
			$done[$method->zasilkovna_api_pass] = true; 
			}
		}
		}
		
		if (($t === count($done)) && (!empty($t))) {
			echo 'Stahovanie pobociek zasilkovny bolo uspesne ! '; 
		}
		else	
		{
			echo 'Stahovanie pobociek zasilkovny nebolo uspesne alebo nieje nastaveny spravny API PASS ! Spracovne '.count($done).' VirtueMart zposobov dopravy zasilkovny'; 
		}
		JFactory::getApplication()->close(); 
		
	}
	function __construct(&$subject, $config)
	{
		
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php')) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
		}
		parent::__construct($subject, $config);
		
		if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'shipment'.DIRECTORY_SEPARATOR.'zasilkovnaopc')) {
			try {
			JFolder::delete(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'shipment'.DIRECTORY_SEPARATOR.'zasilkovnaopc'); 
			}
			catch(Exception $e) {
				
			}
		}
		
		
		$this->_loggable = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$varsToPush = $this->getVarsToPush ();
		
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
		
		if (!class_exists ('calculationHelper')) {
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'calculationh.php');
		}
		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
		}
		if (!class_exists ('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. 'vendor.php');
		}
		

		require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'zasilkovna_orders.php'); 
		
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		
		JFactory::getLanguage()->load('plg_vmshipment_zasilkovnaopc', dirname(__FILE__).DIRECTORY_SEPARATOR);  
		
		JFactory::getLanguage()->load(); 
		
		$app = JFactory::getApplication(); 
		if ($app->isAdmin())
		{
			$this->getVmPluginCreateTableSQL(); 
			
		}
		
	}    

	/**
	* Create the table for this plugin if it does not yet exist.
	* @author Valérie Isaksen
	*/
	public function getVmPluginCreateTableSQL()
	{
		
		$db = JFactory::getDBO(); 
		$q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_shipment_plg_zasilkovnaopc` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`virtuemart_order_id` int(11) unsigned DEFAULT NULL,
`virtuemart_shipmentmethod_id` int(1) unsigned DEFAULT NULL,
`order_number` varchar(200) DEFAULT NULL,
`zasilkovna_packet_id` decimal(10,0) DEFAULT NULL,
`zasilkovna_packet_price` decimal(15,2) DEFAULT NULL,
`branch_id` decimal(10,0) DEFAULT NULL,
`branch_currency` varchar(5) DEFAULT NULL,
`branch_name_street` varchar(500) DEFAULT NULL,
`email` varchar(255) DEFAULT NULL,
`phone` varchar(255) DEFAULT NULL,
`first_name` varchar(255) DEFAULT NULL,
`last_name` varchar(255) DEFAULT NULL,
`address` varchar(255) DEFAULT NULL,
`city` varchar(255) DEFAULT NULL,
`zip_code` varchar(255) DEFAULT NULL,
`virtuemart_country_id` varchar(255) DEFAULT NULL,
`adult_content` smallint(1) DEFAULT \'0\',
`is_cod` smallint(1) DEFAULT NULL,
`exported` smallint(1) DEFAULT NULL,
`printed_label` smallint(1) DEFAULT \'0\',
`shipment_name` varchar(5000) DEFAULT NULL,
`shipment_cost` decimal(10,2) DEFAULT NULL,
`shipment_package_fee` decimal(10,2) DEFAULT NULL,
`tax_id` int(1) DEFAULT NULL,
`created_on` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
`created_by` int(11) NOT NULL DEFAULT \'0\',
`modified_on` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
`modified_by` int(11) NOT NULL DEFAULT \'0\',
`locked_on` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
`locked_by` int(11) NOT NULL DEFAULT \'0\',
PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;'; 
		$db->setQuery($q); 
		try
		{
			$db->execute(); 
		}
		catch (Exception $e)
		{
			
		}
		
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->createTableSQL('zasilkovna');
	}
	
	function getTableSQLFields()
	{
		
		
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		$SQLfields = array(
		'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
		'virtuemart_order_id' => 'int(11) UNSIGNED',
		'virtuemart_shipmentmethod_id' => 'int(1) UNSIGNED',
		'order_number' => 'varchar(200)',
		'zasilkovna_packet_id' => 'decimal(10,0)',
		'zasilkovna_packet_price' => 'decimal(15,2)',
		'branch_id' => 'decimal(10,0)',
		'branch_currency' => 'varchar(5)',
		'branch_name_street' => 'varchar(500)',
		'email' => 'varchar(255)', 
		'phone' => 'varchar(255)', 
		'first_name' => 'varchar(255)',
		'last_name' => 'varchar(255)',
		'address' => 'varchar(255)',
		'city' => 'varchar(255)',
		'zip_code' => 'varchar(255)',            
		'virtuemart_country_id' => 'varchar(255)',            
		'adult_content' => 'smallint(1) DEFAULT \'0\'', 
		'is_cod' => 'smallint(1)',            
		'exported' => 'smallint(1)',
		'printed_label' => 'smallint(1) DEFAULT \'0\'',            
		'shipment_name' => 'varchar(5000)',            
		'shipment_cost' => 'decimal(10,2)',
		'shipment_package_fee' => 'decimal(10,2)',
		'tax_id' => 'int(1)'
		);
		return $SQLfields;
	}
	
	/**
	* This method is fired when showing the order details in the frontend.
	* It displays the shipment-specific data.
	*
	* @param integer $order_number The order Number
	* @return mixed Null for shipments that aren't active, text (HTML) otherwise
	* @author Valérie Isaksen
	* @author Max Milbers
	*/
	public function plgVmOnShowOrderFEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name)
	{
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
	}
	
	function &getHelper() {
		require_once(__DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
		if (!empty($this->helper)) return $this->helper; 
		$this->helper = new ZasilkovnaopcHelper($this); 
		return $this->helper; 
	}
	
	/**
	* This event is fired after the order has been stored; it gets the shipment method-
	* specific data.
	*
	* @param int $order_id The order_id being processed
	* @param object $cart  the cart
	* @param array $priceData Price information for this order
	* @return mixed Null when this method was not selected, otherwise true
	* @author Valerie Isaksen
	*/
	
	function plgVmConfirmedOrder(VirtueMartCart $cart, $order)
	{
		
		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_shipmentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->shipment_element)) {
			return false;
		}
		if (!$this->OnSelectCheck($cart)) {
			return false;
		}
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		
		$zas_model= $this->getHelper(); 
		$zas_orders=VmModel::getModel('zasilkovna_ordersopc');        
		
		$zas_orders->api_pass = $method->zasilkovna_api_pass;
		$zas_orders->zas_model = $this->getHelper(); 
		
		$fromCurrency=$zas_model->getCurrencyCode($order['details']['BT']->order_currency);
		
		//convert from payment currency to branch currency
		$session = JFactory::getSession(); 
		$price_in_branch_currency=$zas_orders->convertToBranchCurrency($order['details']['BT']->order_total,$fromCurrency,$session->get('branch_currency', 'czk'));

		$values['virtuemart_order_id']          = $order['details']['BT']->virtuemart_order_id;
		$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
		$values['order_number']                 = $order['details']['BT']->order_number;
		$values['zasilkovna_packet_id']         = 0;
		$values['zasilkovna_packet_price']      = $price_in_branch_currency;
		$values['branch_id']                    = $session->get('branch_id');
		$values['branch_currency']              = $session->get('branch_currency');
		$values['branch_name_street']           = $session->get('branch_name_street');
		$values['email']                        = $cart->BT['email'];
		$values['phone']                        = $cart->BT['phone_1'];
		$values['adult_content']                = 0;
		
		$payment_id = $cart->virtuemart_paymentmethod_id; 
		if (in_array($payment_id, $method->dobierky))
		{
			$values['is_cod'] = 1; 
		}
		else
		$values['is_cod']                       = -1; //depends on actual settings of COD payments until its set manually in administration
		$values['exported     ']                = 0;        
		$values['shipment_name']                = $method->shipment_name;             
		$values['shipment_cost']                = $this->getCosts ($cart, $method, $cart->cartPrices);                
		$values['tax_id']                       = $method->tax_id;
		$this->storePSPluginInternalData($values);
		return true;
	}
	
	
	/**
* calculateSalesPrice
* overrides default function to remove currency conversion
* @author Zasilkovna
*/

	function calculateSalesPrice ($cart, $method, $cart_prices) {    
		$value = $this->getCosts ($cart, $method, $cart_prices);

		$tax_id = @$method->tax_id;


		$vendor_id = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency ($vendor_id);

		$db = JFactory::getDBO ();
		$calculator = calculationHelper::getInstance ();
		$currency = CurrencyDisplay::getInstance ();    

		$taxrules = array();
		if (!empty($tax_id)) {
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
			$db->setQuery ($q);
			$taxrules = $db->loadAssocList ();
		}

		if (count ($taxrules) > 0) {
			$salesPrice = $calculator->roundInternal ($calculator->executeCalculation ($taxrules, $value));
		} else {
			$salesPrice = $value;
		}      
		return $salesPrice;       
	}

	/**    
	* @return delivery cost for the shipping method instance
	* @author Zasilkovna
	*/
	
	function getCosts(VirtueMartCart $cart, $method, $cart_prices){                   
		$freeShippingTreshold = $method->{'free_shipping_treshold_czk'};  
		$shippingPrice = 57.00; 
		$shippingPrice = (float)$method->packet_price_czk;
		
		$c2c = $this->getCountry2Code($cart); 
		
		if ($c2c === 'sk') $shippingPrice = (float)$method->packet_price_eur; 
		
		if($freeShippingTreshold && 
				$cart_prices['salesPrice'] >= $freeShippingTreshold && 
				$freeShippingTreshold >= 0) {
			return 0;
		}else{
			return $shippingPrice;
		}
	}
	
	/** TODO
	* Here can add check if user has filled in valid phone number or mail so he is reachable by zasilkovna
	*/
	protected function checkConditions($cart, $method, $cart_prices)
	{
		
		$weightTreshold = (float)$method->weight_treshold;
		$weightStart = (float)$method->weight_start;
		if (empty($weightTreshold)) return true; 
		
		$orderWeight = $this->getOrderWeight ($cart, $method->weight_unit);
		if(empty($weightTreshold) || $weightTreshold == -1 || $orderWeight < $weightTreshold) {
			if (empty($weightStart)) return true; 
			if ($weightStart > $orderWeight) return false; 
			return true;
		}
		
		
		return false;
	}      
	
	/*
	* We must reimplement this triggers for joomla 1.7
	*/
	
	/**
	* Create the table for this plugin if it does not yet exist.
	* This functions checks if the called plugin is active one.
	* When yes it is calling the standard method to create the tables
	* @author Valérie Isaksen
	*
	*/
	function plgVmOnStoreInstallShipmentPluginTable($jplugin_id)
	{
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->onStoreInstallPluginTable($jplugin_id);
	}
	
	/**
	* This event is fired after the shipment method has been selected. It can be used to store
	* additional payment info in the cart.
	*
	* @author Max Milbers
	* @author Valérie isaksen
	*
	* @param VirtueMartCart $cart: the actual cart
	* @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	*
	*/
	// public function plgVmOnSelectCheck($psType, VirtueMartCart $cart) {
	// return $this->OnSelectCheck($psType, $cart);
	// }
	public function plgVmOnSelectCheckShipment(VirtueMartCart &$cart)
	{        
		if (!$this->selectedThisByMethodId ($cart->virtuemart_shipmentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($method = $this->getVmPluginMethod ($cart->virtuemart_shipmentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		if ($this->OnSelectCheck($cart)) {
			
			
			 
			$branchData = JRequest::getVar('branch', array()); 
			$vm_id = (int)$method->virtuemart_shipmentmethod_id; 
			$branch_id = 0; 
			if (!empty($branchData[$vm_id])) {
			  $branch_id = (int)$branchData[$vm_id]; 
			}
			
			
			
			
			if (!empty($branch_id))
			{
				
				$zas = $this->getHelper(); 
				$has_branches = $zas->getBranchesJson($method); 
				if ($has_branches) {
					$branch = $zas->getSingleBranch($branch_id); 
					
					
					
					$session = JFactory::getSession(); 
					$session->set('branch_currency',   $branch->currency); 
					$session->set('branch_id', $branch_id); 			
					$session->set('branch_name_street', $branch->nameStreet);
				}
				else {
					return false; 
				}
				
				
				
				
				
			}
		} else {
			return false; 
			
		}
		//$cart->virtuemart_paymentmethod_id = 0;//reset selected payment. Payment options are shown depending on selected shipment
		return $this->OnSelectCheck($cart);
	}
	
	public function getScripts($js_url='')
	{
		$js_html = '';
		if (!empty($js_url)) {
			$js_html.= '<script src="' . $js_url . '"></script>';
		}
		
		
		$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
		
		$doc = JFactory::getDocument(); 
		$js = ' var zasilkovnaRoot = '.json_encode($root).'; '; 
		
		$chyby = array(); 
		foreach ($this->methods as $m) {
			$id = (int)$m->virtuemart_shipmentmethod_id; 
			$chyby[$id] = JText::_($m->chyba); 
		}
		$js .= ' var zasilkovnaChyba = '.json_encode($chyby).';'; 
		if (method_exists($doc, 'addScriptDeclaration')) {
			$doc->addScriptDeclaration($js); 
			
		}
		
		JHtml::script($root.'plugins/vmshipment/zasilkovnaopc/assets/zasilkovnaopc.js'); 
		
		$js_html .= "<div class='zasilkovna_box'>";
		$js_html .= '<input type="hidden" name="branch_id">';
		$js_html .= '<input type="hidden" name="branch_currency">';
		$js_html .= '<input type="hidden" name="branch_name_street">';
		$jsHtmlIsSet = false;                
		
		//stAn, we loaded the current $method above
		//foreach ($this->methods as $key => $method) 
		
		
		
		
		
		
		
	}
	public function loadPluginJavascriptOPC(&$cart, &$plugins, &$html)
	{
		static $done; 
		if (!empty($done)) return; 
		$done = true; 
		$h = $this->getScripts(); 
		if (empty($html)) $html = ''; 
		$html .= $h; 
		
	}
	public function getBranchHtml($branch) {
		return $this->renderByLayout('branch_html', array('branch' => $branch, 'plugin'=>&$this, 'helper'=>&$this->helper)); 
	}
	
	public function countryCode2IntoVirtuemartId($country_code_2) {
		static $cache; 
		if (!empty($cache[$country_code_2])) return $cache[$country_code_2]; 
		$db = JFactory::getDBO(); 
		$q = 'select `virtuemart_country_id` from #__virtuemart_countries where `country_2_code` = \''.$db->escape(strtoupper($country_code_2)).'\' limit 0,1'; 
		$db->setQuery($q); 
		$virtuemart_country_id = $db->loadResult(); 
		$cache[$country_code_2] = (int)$virtuemart_country_id; 
		return (int)$virtuemart_country_id; 
	}
	
	
	public function getOptionObj($branch) {
		
		$obj = new stdClass(); 
		$key = data-branch-id; 
		$obj->{$key} = $branch->id; 
		$obj->value = $branch->id; 
		$obj->text = $this->renderByLayout('option_text', array('branch'=>$branch, 'currentOption'=>&$obj)); 
		
		
		return $obj; 
	}
	
	/**
	* plgVmDisplayListFE
	* This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	*
	* @param object $cart Cart object
	* @param integer $selected ID of the method selected
	* @return boolean True on succes, false on failures, null when this plugin was not selected.
	* On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	*
	* @author Valerie Isaksen
	* @author Max Milbers
	*/
	public function plgVmDisplayListFEShipment(VirtueMartCart $cart, $selected = 0, &$htmlIn)
	{
		
		
		$html = array(); 
		if ($this->getPluginMethods($cart->vendorId) === 0) {
			return FALSE;            
		}        
		
		
		$current_country_2_code = $this->getCountry2Code($cart); 
		
		$key = 0; 
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		foreach ($this->methods as $method)
		{
			
			if (empty($method->zasilkovna_api_pass)) continue; 
			
			
			if (!in_array($current_country_2_code, $method->country)) {
				continue; 
			}
			
			
			
			
			$key = $method->virtuemart_shipmentmethod_id;  
			
			$zas_model = $this->getHelper(); 
			$zas_model->setConfig($method->zasilkovna_api_pass); 
			
			/*
		$js_url    = $zas_model->updateJSApi();
		if ($js_url === false) return false;        
		*/
			
			if (!empty($zas_model->errors)) return false; //api key or smth is wrong - more info shows in administration
			
			
			if (!defined('ZASJSLOADED'))
			{
				$js_html = $this->getScripts($js_url); 
				$html[$key] = $js_html; 
				$jsHtmlIsSet=true;
				define('ZASJSLOADED', 1); 
			}
			

			
			
			$method_name = $this->_psType . '_name';
			$session = JFactory::getSession(); 
			$prevSelectedBranch=$session->get('branch_id', 0);        
			
			/*this part adds javascript api and controls 
			ONLY TO ONE of the zasilkovna shipment methods that ARE allowed to show               
			*/
			
			/* stAn: disabling a payment per shipping is a very tricky part and i do not suggest to do this, this way... rather give an error during the checkout, but not here... 
			/*
			$selectedPayment = (empty($cart->virtuemart_paymentmethod_id) ? 0 : $cart->virtuemart_paymentmethod_id);                        
			if($jsHtmlIsSet==false){            
				$shipmentID=$method->virtuemart_shipmentmethod_id;                                    
				$configRecordName='zasilkovna_combination_payment_'.$selectedPayment.'_shipment_'.$shipmentID;                  
				$config = VmConfig::loadConfig();
				if(($config->get($configRecordName,'1')=='1')||($selectedPayment==0)){
					$html[$key] .= $js_html;
					$jsHtmlIsSet=true;
				}           
			}
			*/


			
			$country = $this->getCountry2Code($cart); 
			//$country = reset($method->country[0]);
			
			if (defined('VM_VERSION') && (VM_VERSION >= 3))
			if (!empty($cart->cartPrices))
			{
				$cart->pricesUnformatted = $cart->cartPrices; 
			}
			
			
			
			
			if (in_array($country, $method->country))
			if ($this->checkConditions($cart, $method, $cart->pricesUnformatted)) {
				
				
				if (!isset($html[$key])) $html[$key] = ''; 
				
				$html[$key] .= '<div name="helper_div">';//this div packs the select box with radio input - helps js easily find the radio
				$methodSalesPrice     = $this->calculateSalesPrice($cart, $method, $cart->pricesUnformatted);               
				$method->$method_name = $this->renderPluginName($method);
				$vm_id = $method->virtuemart_shipmentmethod_id; 
				
				/* ORIGINAL
				$html[$key] .= $this->getPluginHtml($method, $selected, $methodSalesPrice);
				$selected_id_attr = ' selected-id="'.$prevSelectedBranch.'"';       
				$html[$key] .= '<p name="select-branch-message" style="float: none; color: red; font-weight: bold; display: none; ">vyberte pobočku</p>
				<div id="zasilkovna_select" class="packetery-branch-list" list-type="3" country="' . $country . '" '.$selected_id_attr.' style="border: 1px dotted black;">Načítání: seznam poboček osobního odběru</div>';
				
				ORIGINAL END... */
				
				$vmHtml = $this->getPluginHtml($method, $selected, $methodSalesPrice);
				
				$html[$key] .= $vmHtml; 
				$selected_id_attr = ' selected-id="'.$prevSelectedBranch.'"';       
				
				
				
				$zas = $this->getHelper(); 

				
				$has_branches = $zas->getBranchesJson($method); 
				


				$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

				$country_id = $address['virtuemart_country_id']; 



				$country = $method->country; 
				if (is_array($country)) $country = reset($country); 
				$isSk = true; 
				
				$db = JFactory::getDBO(); 
				$q = 'select virtuemart_country_id from #__virtuemart_countries where country_3_code = "CZE" limit 1'; 
				$db->setQuery($q); 
				$cz_id = $db->loadResult(); 
				$q = 'select virtuemart_country_id from #__virtuemart_countries where country_3_code = "SVK" limit 1'; 
				$db->setQuery($q); 
				$sk_id = $db->loadResult(); 
				
				if (empty($country_id)) $isSk = true; 
				if (empty($sk_id) && (!empty($country_id))) $isSk = false; 
				if (empty($sk_id) && (empty($country_id))) $isSk = true; 
				if (empty($country_id) || ($country_id == $sk_id)) $isSk = true; 
				else $isSk = false; 
				
				$isCz = true; 
				if (empty($country_id)) $isCz = true; 
				
				if (empty($cz_id) && (!empty($country_id))) $isCz = false; 
				if (empty($cz_id) && (empty($country_id))) $isCz = true;  
				if (empty($country_id) || ($country_id == $cz_id)) $isCz = true;  
				else $isCz = false; 
				
				
				
				
				//if (($isSk && ($country == 'sk')) || ($isCz && ($country == 'cz'))) 	
				//if ((($isSk && (in_array('sk', $method->country))) || ($isCz && (in_array('cz', $method->country)))))
				


				{ 
					
					$sel = ''; 
					if (!empty($has_branches))
					{
						$extra = '';
						$cIDs = array(); 
						foreach ($method->country as $c2) {
							$cIDs[] = $this->countryCode2IntoVirtuemartId($c2); 
						}
						
						$branchData = JRequest::getVar('branch', array()); 
						$data_default = 0; 
						$session = JFactory::getSession(); 
						if (!empty($branchData[$vm_id])) {
							$data_default = (int)$branchData[$vm_id]; 
							$session->set('branch_'.$vm_id, (int)$data_default); 
						}
						if (empty($data_default)) {
							$data_default = (int)$session->get('branch_'.$vm_id, 0); 
						}
						
						//$sel .= var_export($isSk, true).var_export($isCz, true); 
						$sel .= '<select data-default="'.(int)$data_default.'" class="zasielka_select serialize_ajax" name="branch['.(int)$vm_id.']" onchange="opc_zaschange(this, '.$vm_id.');" data-vmid="'.(int)$vm_id.'" id="branchselect_'.$vm_id.'" data-countries="'.htmlentities(json_encode($cIDs)).'">';
						if ($isCz) 
						$sel .= '<option data-branch-id="" value="">–– vyberte si místo osobního odběru ––</option>';
						else
						if ($isSk) {
							$sel .= '<option data-branch-id="" value="">–– vyberte si miesto osobného odberu ––</option>';
						}
						else {
							$sel .= '<option data-branch-id="" value="">'.htmlentities(JText::_('PLG_VMSHIPMENT_ZASILKOVNAOPC_CHOOSE')).'</option>';
						}

						
						/*
if (!empty($json->data))
foreach ($json->data as $branch)
{
if (!isset($branch->id)) continue; 
// if ($branch->country == 'cz') $country = 'ČR'; 
$cc = $branch->country; 
if (!empty($method->country))
if ((!in_array($cc, $method->country)) || ($cc !==  $current_country_2_code))
{
	continue; 
}
$country = $json->countries->$cc; 




//if (($cc == 'cz') && (!$isCz)) continue; 
//if (($cc == 'sk') && (!$isSk)) continue; 

//$sel .= '<option data-branch-id="'.htmlentities($branch->id, ENT_COMPAT, 'utf-8').'" value="'.htmlentities($branch->id, ENT_COMPAT, 'utf-8').'">'.htmlentities($country.', '.$branch->nameStreet, ENT_COMPAT, 'utf-8').'</option>'; 


$na = array(); 
$na['branch_id'] = $branch->id; 
$na['branch_name_street'] = $branch->nameStreet; 
$na['branch_currency'] = $branch->currency; 
$data = json_encode($na); 


$newjson = '<input type="hidden" name="zasilkovna_shipment_id_'.$vm_id.'_'.$branch->id.'_extrainfo" value="'.base64_encode($data).'" />'; 

$md5 = md5($newjson); 
if (class_exists('OPCloader'))
OPCloader::$inform_html[$md5] = $newjson; 

// end json foreach 
}
*/
						$sel .= '</select>'; 
						//$sel .= $extra;

						/*
						$post = ''; 
						if (!defined('ZAS_ONCE'))
						{
							$post = '<input type="hidden" name="branch_id" id="branch_id" value="" />
		<input type="hidden" name="branch_currency" id="branch_currency" value="" />
		<input type="hidden" name="branch_name_street" id="branch_name_street" value="" />'; 

							define('ZAS_ONCE', 1); 
						}
						*/


						/*
if (strpos($def_html, 'id="shipment_id_'.$vm_id.'"')===false)
{
$def_html = str_replace('name="virtuemart_shipmentmethod_id"', ' name="virtuemart_shipmentmethod_id" id="shipment_id_'.$vm_id.'" ', $def_html); 



}
$def_html = str_replace('value="'.$vm_id.'"', 'value="'.htmlentities($vm_id.'|choose_shipping"'), $def_html); 
*/


						//include(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'onepage.cfg.php'); 

						//if (empty($shipping_inside_choose))
						//$def_html = str_replace('value="'.$vm_id.'"', 'value="'.htmlentities($vm_id.'|choose_shipping"'), $def_html); 
						$ex = ''; 



						//$html = $def_html.'<input type="radio" name="virtuemart_shipmentmethod_id" id="zas_vm_'.$vm_id.'" value="'.$vm_id.'"><div id="opc_zas_place">&nbsp;</div>'.$sel.$ex.$post; 
						$def_html = ''; 
						$htmlZ = '
<div class="zasilkovina_output">
	<div style="clear: both;">'.$def_html.'
	
		<div for="shipment_id_'.$vm_id.'">'.$sel.'
		</div>'.$ex.$post.'
	</div>
	<div class="opc_zas_place" data-vmid="'.$vm_id.'" id="opc_zas_place_'.$vm_id.'" style="clear: both;">&nbsp;</div>
</div>'; //.var_export($_POST, true); 


					}


				}



				$html[$key] .= $htmlZ; 
				$html[$key] .= '</div>';
			}
		}

		
		
		if (empty($html)) {
			return FALSE;
		}
		
		$htmlIn[] = $html;
		return TRUE;
	}
	
	private function getCountry2Code($cart)
	{
		$address = $cart->getST();
		if(!is_array($address)) $address = array();
		
		// default country is CZ in case the country is disabled or not available: 
		$country = 'cz'; 
		if (empty($address['virtuemart_country_id']))
		{
			// default country is set to CZ !
			$country = 'cz'; 
		}
		else
		{
			$country_id = (int)$address['virtuemart_country_id']; 
			$db = JFactory::getDBO(); 
			$q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$country_id.' limit 0,1'; 
			$db->setQuery($q); 
			$country_iso = $db->loadResult(); 
			
			if (!empty($country_iso))
			{
				$country = strtolower($country_iso); 
			}
			
			
		}
		return strtolower($country); 
	}

	/**
* This method is fired when showing the order details in the backend.
* It displays the shipment-specific data.
* NOTE, this plugin should NOT be used to display form fields, since it's called outside
* a form! Use plgVmOnUpdateOrderBE() instead!
*
* @param integer $virtuemart_order_id The order ID
* @param integer $virtuemart_shipmentmethod_id The order shipment method ID
* @param object  $_shipInfo Object with the properties 'shipment' and 'name'
* @return mixed Null for shipments that aren't active, text (HTML) otherwise
* @author Valerie Isaksen
*/
	public function plgVmOnShowOrderBEShipment ($virtuemart_order_id, $virtuemart_shipmentmethod_id) {

		if (!($this->selectedThisByMethodId ($virtuemart_shipmentmethod_id))) {
			return NULL;
		}
		
		if (!($method = $this->getVmPluginMethod ($virtuemart_shipmentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		$html = $this->getOrderShipmentHtml ($virtuemart_order_id);
		return $html;
	}

	/**
* @param $virtuemart_order_id
* @return string
* @author zasilkovna
*/
	function getOrderShipmentHtml ($virtuemart_order_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($q);
		if (!($shipinfo = $db->loadObject ())) {
			
			return '';
		}

		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'currencydisplay.php');
		}

		$currency = CurrencyDisplay::getInstance ();
		$tax = ShopFunctions::getTaxByID ($shipinfo->tax_id);
		$taxDisplay = is_array ($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
		$taxDisplay = ($taxDisplay == -1) ? JText::_ ('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

		$html = '<table class="adminlist">' . "\n";
		$html .= $this->getHtmlHeaderBE ();
		
		
		if (!empty($shipinfo->branch_id)) {
			$helper = $this->getHelper(); 
			$branch = $helper->getSingleBranch($shipinfo->branch_id); 
			if (empty($branch)) {
				$html .= $this->getHtmlRowBE ('Doprava', $shipinfo->shipment_name);   
			    $html .= $this->getHtmlRowBE ('Pobočka', 'ID: '.$shipinfo->branch_id.': '.$shipinfo->branch_name_street);   
		        if ((int)$shipinfo->is_cod === 1) {
				$html .= $this->getHtmlRowBE ('Suma', 'Dobírka '.number_format((float)$shipinfo->zasilkovna_packet_price, 2, ',', ' ').' '.$shipinfo->branch_currency);     
				}
				else {
					$html .= $this->getHtmlRowBE ('Suma', number_format((float)$shipinfo->zasilkovna_packet_price, 2, ',', ' ').' '.$shipinfo->branch_currency);     
				}
			}
			else {
			$html .= $this->getHtmlRowBE ('Doprava', ' '.$shipinfo->shipment_name);   
			$html .= $this->getHtmlRowBE ('Pobočka', '<a href="'.$branch->url.'" target="_blank">ID '.$shipinfo->branch_id.': '.strtoupper($branch->country).', '.$branch->place.', '.$branch->name.'</a>');   
		    if ((int)$shipinfo->is_cod === 1) {
				$html .= $this->getHtmlRowBE ('Suma', 'Dobírka '.number_format((float)$shipinfo->zasilkovna_packet_price, 2, ',', ' ').' '.$shipinfo->branch_currency);     
			}
			else {
				$html .= $this->getHtmlRowBE ('Suma', number_format((float)$shipinfo->zasilkovna_packet_price, 2, ',', ' ').' '.$shipinfo->branch_currency);     
			}
			}
			
		}
		else {
		   $html .= $this->getHtmlRowBE ('Doprava', $shipinfo->shipment_name);   
			$html .= $this->getHtmlRowBE ('Pobočka', $shipinfo->branch_name_street);   
		   $html .= $this->getHtmlRowBE ('Mena', $shipinfo->branch_currency);       	
		}

		$html .= '</table>' . "\n";

		return $html;
	}
	
	public function plgVmonSelectedCalculatePriceShipment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name)
	{        
	
	
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}
	
	/**
	* plgVmOnCheckAutomaticSelected
	* Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	* The plugin must check first if it is the correct type
	* @author Valerie Isaksen
	* @param VirtueMartCart cart: the cart object
	* @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	*
	*/
	function plgVmOnCheckAutomaticSelectedShipment(VirtueMartCart $cart, array $cart_prices = array())
	{
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->onCheckAutomaticSelected($cart, $cart_prices);
	}
	
	/**
	* This event is fired during the checkout process. It can be used to validate the
	* method data as entered by the user.
	*
	* @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	* @author Max Milbers
	
	public function plgVmOnCheckoutCheckData($psType, VirtueMartCart $cart) {
	return null;
	}
	*/
	
	/**
	* This method is fired when showing when priting an Order
	* It displays the the payment method-specific data.
	*
	* @param integer $_virtuemart_order_id The order ID
	* @param integer $method_id  method used for this order
	* @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	* @author Valerie Isaksen
	*/
	function plgVmonShowOrderPrint($order_number, $method_id)
	{
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->onShowOrderPrint($order_number, $method_id);
	}
	
	/**
	* Save updated order data to the method specific table
	*
	* @param array $_formData Form data
	* @return mixed, True on success, false on failures (the rest of the save-process will be
	* skipped!), or null when this method is not actived.
	* @author Oscar van Eijk
	
	public function plgVmOnUpdateOrder($psType, $_formData) {
	return null;
	}
	*/
	/**
	* Save updated orderline data to the method specific table
	*
	* @param array $_formData Form data
	* @return mixed, True on success, false on failures (the rest of the save-process will be
	* skipped!), or null when this method is not actived.
	* @author Oscar van Eijk
	
	public function plgVmOnUpdateOrderLine($psType, $_formData) {
	return null;
	}
	*/
	/**
	* plgVmOnEditOrderLineBE
	* This method is fired when editing the order line details in the backend.
	* It can be used to add line specific package codes
	*
	* @param integer $_orderId The order ID
	* @param integer $_lineId
	* @return mixed Null for method that aren't active, text (HTML) otherwise
	* @author Oscar van Eijk
	
	public function plgVmOnEditOrderLineBE($psType, $_orderId, $_lineId) {
	return null;
	}
	*/
	/**
	* This method is fired when showing the order details in the frontend, for every orderline.
	* It can be used to display line specific package codes, e.g. with a link to external tracking and
	* tracing systems
	*
	* @param integer $_orderId The order ID
	* @param integer $_lineId
	* @return mixed Null for method that aren't active, text (HTML) otherwise
	* @author Oscar van Eijk
	
	public function plgVmOnShowOrderLineFE($psType, $_orderId, $_lineId) {
	return null;
	}
	*/
	
	/**
	* plgVmOnResponseReceived
	* This event is fired when the  method returns to the shop after the transaction
	*
	*  the method itself should send in the URL the parameters needed
	* NOTE for Plugin developers:
	*  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	*
	* @param int $virtuemart_order_id : should return the virtuemart_order_id
	* @param text $html: the html to display
	* @return mixed Null when this method was not selected, otherwise the true or false
	*
	* @author Valerie Isaksen
	*
	
	function plgVmOnResponseReceived($psType, &$virtuemart_order_id, &$html) {
	return null;
	}
	*/
	function plgVmDeclarePluginParamsShipment($name, $id, &$data)
	{
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->declarePluginParams('shipment', $name, $id, $data);
	}
	
	
	function plgVmDeclarePluginParamsShipmentVM3 (&$data) {
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->declarePluginParams ('shipment', $data);
	}

	
	function plgVmSetOnTablePluginParamsShipment($name, $id, &$table)
	{
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		return $this->setOnTablePluginParams($name, $id, $table);
	}
	
	function plgVmSetOnTablePluginShipment(&$data,&$table){
		$name = $data['shipment_element'];
		$id = $data['shipment_jplugin_id'];
		$this->_tablename = '#__virtuemart_shipment_plg_zasilkovna'; 
		if (!empty($this->_psType) and !$this->selectedThis ($this->_psType, $name, $id)) {
			return FALSE;
		} 
		
		
		return $this->setOnTablePluginParams ($name, $id, $table);
	}
	
}

// No closing tag
