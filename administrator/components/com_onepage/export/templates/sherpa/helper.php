<?php
class sherpaHelper {
	
	/* run this from default.php of productdetails template to update data to the very latest version
	
	//we need to check if the product is up-to-date agains sherpa because if it was created recently and not yet changed in sherpa it may not be properly synced:
	require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
	sherpaHelper::getSetVMProduct($this->product); 
	
	*/ 
	
	public static function createSupplierTable() {
		if (!OPCMini::tableExists('virtuemart_supplierstock')) {
	  $q = 'CREATE TABLE IF NOT EXISTS `#__virtuemart_supplierstock` (
  `virtuemart_product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_stock` int(11) NOT NULL,
  `product_sku` varchar(180) NOT NULL,
  `prefered` tinyint(1) NOT NULL DEFAULT \'0\',
  UNIQUE KEY `stock` (`virtuemart_product_id`,`supplier_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `virtuemart_product_id_2` (`virtuemart_product_id`,`supplier_stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'; 
	$db = JFactory::getDBO(); 
	$db->setQuery($q); 
	$db->execute(); 
		}
  }
	
	public static function getSetVMProduct(&$product) {
		$sku = $product->product_sku; 
		$mpn = $product->product_mpn; 
		$pid = (int)$product->virtuemart_product_id; 
		$product_in_stock = (int)$product->product_in_stock; 
		//#__sherpa_changedassemblystock, #__sherpa_changedstock, #___virtuemart_supplierstock, zlto4_virtuemart_supplierstock
		$db = JFactory::getDBO(); 
		$table = self::getTableNameFromCmd('ChangedAssemblyStock'); 
		$q = 'select `product_in_stock` from `'.$table.'` where `product_sku` = \''.$db->escape($sku).'\''; 
		$db->setQuery($q); 
		$assebmly_stock = $db->loadResult(); 
		
		$internal_updated = false; 
		
		if (!empty($assebmly_stock)) {
			
			$assebmly_stock = (int)$assebmly_stock; 
			if ($product_in_stock !== $assebmly_stock) {
				//update internal stock from assembly:
				
				$q = 'update #__virtuemart_products set `product_in_stock` = '.(int)$assebmly_stock.' where `virtuemart_product_id` = '.(int)$pid;
				$db->setQuery($q); 
				$db->execute(); 
				$product->product_in_stock = $assebmly_stock; 
				$internal_updated = true; 
			}
		}
		
		
		
		
		if (strpos($mpn, '+')!==false) {
					$MPNs = explode('+', $mpn); 
					
					
					$mx = array(); 
					$db = JFactory::getDBO(); 
					foreach ($MPNs as $m) {
						if (empty($m)) continue; 
						$mx[] = "'".$db->escape($m)."'"; 
					}
					
					if (!$internal_updated) {
						
						//stAn - june 2020, we need to check all SKUs, not just those in the shop:
						$min_plus_stock = null; 
						
						$q = 'select min(product_in_stock) as product_in_stock from `#__sherpa_changedstock` where `product_sku` IN ('.implode(',', $mx).')'; 
						$db->setQuery($q); 
						$min_plus_stockSherpa = $db->loadResult(); 
						
						
							$q = 'select min(product_in_stock) as product_in_stock from  `#__virtuemart_products` where `product_mpn` IN ('.implode(',', $mx).')'; 
							$db->setQuery($q); 
							$min_plus_stockVM = $db->loadResult(); 
						
						if (is_null($min_plus_stockSherpa) && (!is_null($min_plus_stockVM))) {
							$min_plus_stock = (int)$min_plus_stockVM;
						}
						elseif (!is_null($min_plus_stockSherpa) && (is_null($min_plus_stockVM))) {
							$min_plus_stock = (int)$min_plus_stockSherpa;
						}
						elseif (!is_null($min_plus_stockSherpa) && (!is_null($min_plus_stockVM))) {
							$min_plus_stockSherpa = (int)$min_plus_stockSherpa; 
							$min_plus_stockVM = (int)$min_plus_stockVM; 
							if ($min_plus_stockSherpa < $min_plus_stockVM) {
								$min_plus_stock = (int)$min_plus_stockSherpa;
							}
							else {
								$min_plus_stock = (int)$min_plus_stockVM;
							}
						}
						if (!is_null($min_plus_stock)) {
						$min_plus_stock = (int)$min_plus_stock; 
						if ($min_plus_stock !== $product_in_stock) {
							
							
							$q = 'update #__virtuemart_products set `product_in_stock` = '.(int)$min_plus_stock.' where `virtuemart_product_id` = '.(int)$pid;
							
							
							$db->setQuery($q); 
							$db->execute(); 
							$internal_updated = true; 
							$product->product_in_stock = $min_plus_stock; 
						}
						}
				
						
					}
					
					//multiple's MPN supplier stock gets shown automatically by the template
					
					
				}
				else {
					
					$table = self::getTableNameFromCmd('ChangedItemSuppliersWithDefaults'); 
					$q = 'select `supplier_stock` from `'.$table.'` where `product_sku` = \''.$db->escape($mpn).'\''; 
					$db->setQuery($q); 
					$supplier_stock = $db->loadResult(); 
					self::createSupplierTable(); 
					$q = 'select `supplier_stock` from #__virtuemart_supplierstock where virtuemart_product_id = '.(int)$pid; 
					$db->setQuery($q); 
					$supplier_stock_product = $db->loadResult(); 
					if ((!is_null($supplier_stock_product)) && (!is_null($supplier_stock)))
					{
					
					$supplier_stock_product = (int)$supplier_stock_product; 
					$supplier_stock = (int)$supplier_stock; 
					
					if ($supplier_stock !== $supplier_stock_product) {
							$q = 'update #__virtuemart_supplierstock set `supplier_stock` = '.(int)$supplier_stock.' where `virtuemart_product_id` = '.(int)$pid;
							
							$db->setQuery($q); 
							$db->execute(); 

					}
					}
					
					
				}
		if (empty($internal_updated)) {
		$db = JFactory::getDBO(); 
		$table = self::getTableNameFromCmd('ChangedStock'); 
		$q = 'select `product_in_stock` from `'.$table.'` where `product_sku` = \''.$db->escape($mpn).'\''; 
		$db->setQuery($q); 
		$item_stock = $db->loadResult(); 
		
		
		if (!is_null($item_stock)) 
		{
			$item_stock = (int)$item_stock; 
		if ($item_stock !== $product_in_stock) {
							$q = 'update #__virtuemart_products set `product_in_stock` = '.(int)$item_stock.' where `virtuemart_product_id` = '.(int)$pid;
							
							$db->setQuery($q); 
							$db->execute(); 
							$internal_updated = true; 
							$product->product_in_stock = $item_stock; 
		}
		}
		
		
		}
		
	}
	
	public static $disable_cache; 
	public static $cache;
	public static $kill_switch; //set to true from external script to use die statements
	public static function isElectronicPayment($sherpaPaymentMethodCode) {
		if (empty($sherpaPaymentMethodCode)) return false; 
		static $cache; 
		if (isset($cache[$sherpaPaymentMethodCode])) return $cache[$sherpaPaymentMethodCode]; 
		$soapmethod = 'PaymentMethodList'; 
		$xml = sherpaHelper::sendRequest($soapmethod); 
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		$datas = array(); 
		foreach ($xmlres->children() as $i) {

			$paymentdata = $i->ResponseValue->children(); 
			foreach ($paymentdata as $si) {
				$code = (string)$si->PaymentMethodCode; 
				$ePaid = (string)$si->ElectronicPayment; 
				
				$ElectronicPayment = false;
				
				if ($ePaid === 'false') {
				  $ElectronicPayment = false; 
				}
				
				if ($ePaid === 'true') {
					$ElectronicPayment = true; 
				}
				
				$datas[$code] = $ElectronicPayment; 
			}
		}
		
		
				
		$cache = $datas; 
		if (!empty($datas[$sherpaPaymentMethodCode])) return true; 
		
		return false; 
		
		
	}
	public static function onCliSherpaStockSync(&$ref) {
		//stAn - for testing purposes:
		/*
	sherpaHelper::onlineStockUpdateAvailability('TEST_FOR_SHERPA2', true); 
	return; 
	*/
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
	$JModelUtils = new JModelUtils; 
	$pairing = $JModelUtils->getProductSkus(true); 
	
	
	$data_supplier = sherpaHelper::ChangedItemSuppliersWithDefaults(false); 
	
	$new_data = array(); 
	foreach ($data_supplier as $k=>$obj) {
		$sku = $obj->product_sku; 
		$obj->prefered = (int)$obj->prefered; 
		
		foreach ($pairing['skusID'] as $product_id => $xsku) {
			if ($xsku === $sku) {
				
				$objx = new stdClass(); 
			  $objx->product_sku = $sku; 
			  $objx->prefered = (int)$obj->prefered; 
			  $objx->supplier_id = (int)$obj->supplier_id;
			  $objx->virtuemart_product_id = (int)$product_id; 
			  $objx->supplier_stock = (int)$obj->supplier_stock; 
			  $new_data[] = $objx; 
				/*
			  $objx = $obj; 
			  $objx->virtuemart_product_id = $product_id; 
			  $new_data[] = $objx; 
			  */
			  
			}
		}
		
		foreach ($pairing['mpnIDs'] as $product_id => $mpn) {
			if ($mpn === $sku) {
				/*
			  $objx = $obj; 
			  $objx->virtuemart_product_id = $product_id; 
			  $new_data[] = $objx; 
			   */
			  
			  $objx = new stdClass(); 
			  $objx->product_sku = $sku; 
			  $objx->prefered = (int)$obj->prefered; 
			  $objx->supplier_id = (int)$obj->supplier_id;
			  $objx->virtuemart_product_id = (int)$product_id; 
			  $objx->supplier_stock = (int)$obj->supplier_stock; 
			  $new_data[] = $objx; 
			  
			  
			}
		}
		/*
		if (!isset($pairing['idsToSkus'][$sku])) {
			unset($data_supplier[$k]); 
			continue; 
		}
		$product_id = (int)$pairing['idsToSkus'][$sku]; 
		$data_supplier[$k]->virtuemart_product_id = $product_id; 
		*/
	}
	
	
	
	
	//$ref->createTable(); 
	self::createSupplierTable(); 
	$retMsg = $JModelUtils->mergeDataIntoTable('#__virtuemart_supplierstock', $new_data, array('virtuemart_product_id', 'supplier_id')); 
	
	if (class_exists('cliHelper')) cliHelper::debug( 'Updated '.count($new_data).' supplier stock items from Sherpa system'); 
	if (!empty($retMsg)) {
		if (class_exists('cliHelper')) cliHelper::debug( $retMsg); 
	}
	
	
	
	
	
	$data = sherpaHelper::ChangedStockObj(false); 
	
	$toUpdate = array(); 
	foreach ($data as $obj) {
		$ids = self::getProductIds($obj->product_sku); 
		foreach ($ids as $product_id) {
		$objx = new stdClass(); 
		/*$objx->product_sku = $obj->product_sku; //stAn - we cannot set product_sku here as it will be updated to MPN*/
		$objx->product_in_stock = $obj->product_in_stock; 
		$objx->product_available_date = $obj->product_available_date; 
		$objx->virtuemart_product_id = $product_id;
		$toUpdate[$objx->virtuemart_product_id] = $objx; 
		}
		/*
		$objx = new stdClass(); 
		$objx->product_sku = $obj->product_sku; 
		$objx->product_in_stock = $obj->product_in_stock; 
		$objx->product_available_date = $obj->product_available_date; 
		$toUpdate[$objx->product_sku] = $objx; 
		*/
	}
	
	$dataA = sherpaHelper::ChangedAssemblyStockObj(false); 
	
	/*$toUpdate2 = array(); */
	foreach ($dataA as $obj) {
		$ids = self::getProductIds($obj->product_sku); 
		foreach ($ids as $product_id) {
		$objx = new stdClass(); 
		/*$objx->product_sku = $obj->product_sku; //stAn - we cannot set product_sku here as it will be updated to MPN*/
		$objx->product_in_stock = $obj->product_in_stock; 
		$objx->product_available_date = $obj->product_available_date; 
		$objx->virtuemart_product_id = $product_id;
		$toUpdate[$objx->virtuemart_product_id] = $objx; 
		}
	}
	
	if (empty($data)) {
		//if (class_exists('cliHelper')) cliHelper::debug( 'Error getting data from Sherpa '.var_export($data, true)); 
		
	}
	$c = 0; 	
	$db = JFactory::getDBO(); 
		if ((!empty($toUpdate))) {
		 $q = 'START TRANSACTION'; 
		 $db->setQuery($q); 
		 $db->execute(); 
		
		 $q = 'SET @STOCK_TRIG_DISABLED = true'; 
		 $db->setQuery($q); 
	     $db->execute(); 
	
	
	if (!empty($toUpdate)) {
	/*$c = $JModelUtils->updateProductTable($toUpdate, true); */
	 $msg = $JModelUtils->mergeDataIntoTable('#__virtuemart_products', $toUpdate, array('virtuemart_product_id')); 
	}
	else {
		if (class_exists('cliHelper')) cliHelper::debug( 'No new Item data from Sherpa system'); 
	}
	/*
	if (false)
	if (!empty($toUpdate2)) {
	 // original: $c += $JModelUtils->updateProductTable($toUpdate2, true); 
	 // new: $msg = $JModelUtils->mergeDataIntoTable('#__virtuemart_products', $toUpdate2, array('virtuemart_product_id')); 
	}
	else {
		if (class_exists('cliHelper')) cliHelper::debug( 'No new Assembly data from Sherpa system'); 
	}
	*/
	if (class_exists('cliHelper')) cliHelper::debug( 'Updated '.count($toUpdate).' Stock and Assembly Items in #__virtuemart_products'); 
	
	
			   $q = 'SET @STOCK_TRIG_DISABLED = NULL'; 
			   $db->setQuery($q); 
			   $db->query(); 
			   
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
		}
	if (class_exists('cliHelper')) cliHelper::debug( 'Updated '.(int)$c.' stock items from Sherpa system'); 
	if (class_exists('cliHelper')) cliHelper::debug( 'Updating orders..'); 
	sherpaHelper::getChangedOrdersInSherpa(); 
	if (class_exists('cliHelper')) cliHelper::debug( 'Finished'); 
	}
	//to be run from sherpa system plugin:
	public static function onlineStockUpdateAvailability($sku, $nocache=false) {
		//$data = sherpaHelper::ChangedStock(); 
	
	if ($nocache) {
		sherpaHelper::$disable_cache = true;  
	}
	
	$sku_orig = $sku; 
	
	$db = JFactory::getDBO(); 
	$q = 'select `virtuemart_product_id`, `product_sku`, `product_mpn` from #__virtuemart_products where `product_sku` = \''.$db->escape($sku).'\' or `product_mpn` = \''.$db->escape($sku).'\''; 
	$db->setQuery($q); 
	$res = $db->loadAssocList(); 
	
	$mpns2ids = array(); 
	$skus2ids = array(); 
	$pairing = array(); 
	$pairing['mpnIDs'] = array(); 
	$pairing['skusID'] = array(); 
	$product_sku = ''; 
	$mpn = ''; 
	$filter = array(); 
	if (!empty($res)) {
	foreach ($res as $row) {
		$product_sku = $row['product_sku']; 
		$mpn = $row['product_mpn']; 
		
		if (!empty($mpn)) {
		 $mpns2ids[$mpn] = (int)$row['virtuemart_product_id']; 
		 $pairing['mpnIDs'][(int)$row['virtuemart_product_id']] = $mpn; 
		}
		if (!empty($produt_sku)) {
		 $skus2ids[$product_sku] = (int)$row['virtuemart_product_id']; 
		 $pairing['skusID'][(int)$row['virtuemart_product_id']] = $product_sku; 
		}
		
		$MPNs = array(); 
				if (strpos($mpn, '+')!==false) {
					$MPNs = explode('+', $mpn); 
				}
		
		if (!empty($product_sku)) {
		 $filter[$product_sku] = $product_sku; 
		}
		if (!empty($mpn)) {
		 $filter[$mpn] = $mpn; 
		}
		
		foreach ($MPNs as $partial_mpn) {
			$filter[$partial_mpn] = $partial_mpn; 
		}
		
	   
		
		
		
	}
	}
	
	
	
	
	
	if (empty($filter)) return ''; 
	
	
	
	if ((!empty($product_sku)) && (!empty($mpn))) {
	 $data = sherpaHelper::getStockCreateProduct($product_sku, $mpn); 
	 return; 
	}
	else {
		return; 
	 $data = sherpaHelper::getAllStock($filter); 
	}
	
	
	
	
	if (empty($data)) {
		return 'No data found'; 
	}
	
	if (!is_array($data)) return $data; 
	
	
	
	$found = false; 
	if (!empty($res)) {
	foreach ($res as $row) {
		$product_sku = $row['product_sku']; 
	    if (isset($data[$product_sku])) $found = true; 
		$mpnCURRENT = $row['product_mpn']; 
	    if (isset($data[$mpnCURRENT])) $found = true; 
		
		
		$MPNsCURRENT = array(); 
				if (strpos($mpnCURRENT, '+')!==false) {
					$MPNsCURRENT = explode('+', $mpnCURRENT); 
				}
		foreach ($MPNsCURRENT as $partial_mpn) {
			if (isset($data[$partial_mpn])) $found = true; 
		}
		
	}
	}
	
	if (!$found) return 'Product not found'; 
	
	
	$data_supplier = sherpaHelper::ChangedItemSuppliersWithDefaults(false); 
	
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
	$JModelUtils = new JModelUtils; 
	
	$new_data = array(); 
	foreach ($data_supplier as $k=>$obj) {
		$sku = $obj->product_sku; 
		$obj->prefered = (int)$obj->prefered; 
		
		foreach ($pairing['skusID'] as $product_id => $xsku) {
			if ($xsku === $sku) {
				
				$objx = new stdClass(); 
			  $objx->product_sku = $sku; 
			  $objx->prefered = (int)$obj->prefered; 
			  $objx->supplier_id = (int)$obj->supplier_id;
			  $objx->virtuemart_product_id = (int)$product_id; 
			  $objx->supplier_stock = (int)$obj->supplier_stock; 
			  $new_data[] = $objx; 
				/*
			  $objx = $obj; 
			  $objx->virtuemart_product_id = $product_id; 
			  $new_data[] = $objx; 
			  */
			  
			}
		}
		
		foreach ($pairing['mpnIDs'] as $product_id => $mpn) {
			if ($mpn === $sku) {
				/*
			  $objx = $obj; 
			  $objx->virtuemart_product_id = $product_id; 
			  $new_data[] = $objx; 
			   */
			  
			  $objx = new stdClass(); 
			  $objx->product_sku = $sku; 
			  $objx->prefered = (int)$obj->prefered; 
			  $objx->supplier_id = (int)$obj->supplier_id;
			  $objx->virtuemart_product_id = (int)$product_id; 
			  $objx->supplier_stock = (int)$obj->supplier_stock; 
			  $new_data[] = $objx; 
			  
			  
			}
		}
		/*
		if (!isset($pairing['idsToSkus'][$sku])) {
			unset($data_supplier[$k]); 
			continue; 
		}
		$product_id = (int)$pairing['idsToSkus'][$sku]; 
		$data_supplier[$k]->virtuemart_product_id = $product_id; 
		*/
	}
	
	/*
	$new_data = array(); 
	foreach ($data_supplier as $k=>$obj) {
		$current_mpn = $obj->product_sku; 
		foreach ($mpns2ids as $mpn=>$product_id) {
		if ($mpn !== $current_mpn) continue; 
		$obj->prefered = (int)$obj->prefered; 
		
		
			  $objx = new stdClass(); 
			  $objx->product_sku = $obj->product_sku; 
			  $objx->prefered = (int)$obj->prefered; 
			  $objx->supplier_id = (int)$obj->supplier_id;
			  $objx->virtuemart_product_id = (int)$product_id; 
			  $objx->supplier_stock = (int)$obj->supplier_stock; 
			  $new_data[] = $objx; 
		
			  
			  
			}
	}
	*/
		
		/*
		if (!isset($pairing['idsToSkus'][$sku])) {
			unset($data_supplier[$k]); 
			continue; 
		}
		$product_id = (int)$pairing['idsToSkus'][$sku]; 
		$data_supplier[$k]->virtuemart_product_id = $product_id; 
		*/
	
	if (!empty($new_data)) {
		
	self::createSupplierTable(); 
	$retMsg = $JModelUtils->mergeDataIntoTable('#__virtuemart_supplierstock', $new_data, array('virtuemart_product_id', 'supplier_id')); 
	}
	
	//LOWEST: 
	
	//$min_stock = min($data); 
	
	
	$db = JFactory::getDBO(); 
		
		
		
	require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
	
	$toUpdate = array(); 
	foreach ($data as $obj) {
		
		$ids = self::getProductIds($obj->product_sku); 
		foreach ($ids as $product_id) {
		$objx = new stdClass(); 
		/*$objx->product_sku = $obj->product_sku; //stAn - we cannot set product_sku here as it will be updated to MPN*/
		$objx->product_in_stock = $obj->product_in_stock; 
		$objx->product_available_date = $obj->product_available_date; 
		$objx->virtuemart_product_id = $product_id;
		$toUpdate[$objx->virtuemart_product_id] = $objx; 
		}
	}
	if (!empty($toUpdate)) {
	 $q = 'START TRANSACTION'; 
		 $db->setQuery($q); 
		 $db->execute(); 
		
		 $q = 'SET @STOCK_TRIG_DISABLED = true'; 
		 $db->setQuery($q); 
	     $db->execute(); 
	
	JModelUtils::$debug = false; 
	$JModelUtils = new JModelUtils; 
	//$c = $JModelUtils->updateProductTable($toUpdate, true); 
	$msg = $JModelUtils->mergeDataIntoTable('#__virtuemart_products', $toUpdate, array('virtuemart_product_id')); 
	
			   $q = 'SET @STOCK_TRIG_DISABLED = NULL'; 
			   $db->setQuery($q); 
			   $db->query(); 
			   
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
	}
	  //return 'Updated '.$c.' products !'; 
	  return ''; 
	}
	
	//send either sku or product_id, if product_id is used, sku will be ignored
	public static function getProductBySku($sku='', $product_id=0) {
		$db = JFactory::getDBO(); 
		$product_id = (int)$product_id; 
		if (empty($product_id)) {
		  $q = 'select virtuemart_product_id from #__virtuemart_products where product_sku = \''.$db->escape($sku).'\' or product_mpn = \''.$db->escape($sku).'\' limit 1'; 
		  $db->setQuery($q); 
		  $virtuemart_product_id = $db->loadResult(); 
		  $virtuemart_product_id = (int)$virtuemart_product_id; 
		}
		else {
			$virtuemart_product_id = (int)$product_id; 
		}
		if (!empty($virtuemart_product_id)) {
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
			$config = new JModelConfig(); 
			$config->loadVmConfig(); 
			$productModel = VmModel::getModel('product'); 
			$product = $productModel->getProduct($virtuemart_product_id); 
			
			$q = 'select * from #__virtuemart_products where virtuemart_product_id = '.(int)$virtuemart_product_id; 
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 
			if (!empty($row)) {
				foreach ($row as $k=>$v) {
					$product->{$k} = $v; 
				}
			}
			$q = 'select product_name from #__virtuemart_products_en_gb where virtuemart_product_id = '.(int)$virtuemart_product_id; 
			$db->setQuery($q); 
			$product_name = $db->loadResult(); 
			if (!empty($product_name)) $product->product_name = $product_name; 
			
			$mf_id = $product->virtuemart_manufacturer_id; 
			if (is_array($mf_id)) $mf_id = reset($mf_id); 
			if (!empty($mf_id)) {
				$q = 'select mf_name from #__virtuemart_manufacturers_en_gb where virtuemart_manufacturer_id = '.(int)$mf_id; 
				$db->setQuery($q); 
				$mf_name = $db->loadResult(); 
				
				if (!empty($mf_name)) $product->mf_name = $mf_name; 
			}
			
			//$q = 'select product_price from #__virtuemart_product_prices where virtuemart_product_id = '.(int)$virtuemart_product_id.' and 
			
			
			if (empty($product->product_name)) return false; 
			
			if (!class_exists('VmImage'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');
			
			$productModel->addImages($product);
			
			$product->img_url = ''; 
			if ((isset($product->images)) && isset($product->images[0])) {
				if (isset($product->images[0]->file_url_thumb)) {
				  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'xmlexport.php'); 
				  $root = Juri::root(); 
				  if (substr($root, -1) === '/') $root = substr($root, 0, -1); 
				  $img_url = $root.OPCXmlExport::getLink('', $product->images[0]->file_url_thumb); 
				  $product->img_url = $img_url; 
				}
			}
			
			return $product; 
		}
		return false; 
	}
	
	public static function storeExportXMLError(&$data, &$respTxt='', $suffix='') {
		
		$order_id = time(); 
		
		
		$ehelper = new OnepageTemplateHelper();
		
		$templates = $ehelper->getExportTemplates('ALL');
		$et = array(); 
		foreach ($templates as $t) {
			if ($t['file'] === 'sherpa.php') {
				$et = $t; 
				break; 
			}
		}
		
		
		if (empty($et)) return; 
		
		
		$tid = (int)$et['tid']; 
		$ehelper->prepareDirectory($tid);
		
		$file = $ehelper->getFileName2Save($tid, $order_id, $order_id.'_'.$suffix);
		
		//$ehelper->setStatus($tid, $order_id, 'ERROR', urlencode($file));
		
		self::storeExportXML($order_id, $data, $respTxt, $suffix, 'ERROR'); 
		return; 
		/*
		$file = str_replace('.php', '.xml', $file); 
		if (JFile::write($file, $data)!==false)
		{
			//$ehelper->setStatus($tid, $order_id, 'ERROR', urlencode($file));
		}
		
		
		*/
	}
	
	public static function &processXML(&$origbody) {
		if (is_object($origbody)) return $origbody; 
		if (!is_string($origbody)) return false;
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($origbody);
		
		return $xml; 
		
	}
	
	public static function sendNewItems($items, $extraData=array()) {
		$params =  self::getParams(); 
		if (empty($params)) return; 
		$data = array(); 
		$securityCode = $params['config']->secret_key;
		foreach ($params['config'] as $k=>$v) {
			$data[$k] = $v; 
		}
		$data['securityCode'] = $securityCode; 
		//$data['counter'] = self::getNextCounter(); 
		$data['maxResult'] = 999999; 
		$url = $params['config']->soap_url;
		$data['soap_url'] = $url; 
		$data['orders'] = array(); 
		$data['items'] = $items; 
		$data['extra'] = $extraData;
		
		foreach ($items as $i) {
			if (class_exists('cliHelper')) cliHelper::debug('sendNewItems '.$i->ItemCode.' '.$i->vm_product->product_name); 
		}
		
		$data['debug'] = (int)$params['config']->debug; 
		$cmd = 'GetResponse'; 
		$xml = false; 
		$xmlResp = xmlHelper::get($cmd, $data, $errMsg, $xmlTxt, $xmlDebug); 
		if ($xmlResp !== false) {
			$xml = self::processXML($xmlResp); 
		}
		
		
		
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) {
			self::storeExportXMLError($xmlDebug, $xmlResp, 'debug'); 
			self::storeExportXMLError($xmlTxt); 
			self::emailError('error importing products: '.var_export($items, true)."\n".$errMsg."\n Debug Request:\n".$xmlDebug); 
			OnepageTemplateHelper::$print_output = true; 
			return false; 
		}
		
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) {
			return false; 
		}
		
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		
		$data = array(); 
		foreach ($xmlres->children() as $i) {
			$sx = (string)$i[0]; 
			if (!empty($sx)) {
			   $ret = simplexml_load_string($sx); 	
			   foreach ($ret->children() as $key=>$ii) {
				   $kn = (string)$key; 
				   $val = (string)$ii; 
				   $data[$kn] = $val; 
				   
			   }
			   
			}
			
		}
		
		
		
		$fn = array(); 
		foreach ($items as $i) {
			$fn[] = $i->ItemCode; 
		}
		$fname = implode('_', $fn); 
		
		self::storeExportXML($fname, $xmlTxt, $xmlResp); 
		$emptyStrig = ''; 
		self::storeExportXML($fname, $xmlDebug, $emptyStrig, $fname.'_debug'); 
		
		if (isset($data['ResultCode']) && ($data['ResultCode'] === 'I01')) {
			$msg = $data['ResultDescription']; 
			self::emailError($msg."\nProducts: \n".$fname."\nRequest:\n".$xmlDebug); 
			OnepageTemplateHelper::$print_output = true; 
			JFactory::getApplication()->enqueueMessage($msg, 'error'); 
			
		
		
			return false; 
		 }
		 
		
		 
		 if (isset($data['ResultCode']) && ($data['ResultCode'] === 'I00')) {
			 
			 
			 return $xml; 
		 }
		
		
		if (!empty($errMsg)) return false; 
		
	
		return $xml; 
		
	}
	
	//returns virtuemart order_id
	public static function checkOrderExported($OrderNumber) {
		
		
		$db = JFactory::getDBO(); 
		$q = 'select e.`localid` from #__onepage_exported as e, #__onepage_export_templates as t where e.`ai` = \''.$db->escape($OrderNumber).'\' and e.`tid` = t.`tid` and t.`file` = \'sherpa.php\' limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		if (empty($res)) return false; 
		$resInt = (int)$res; 
		//by default ai = order_id which means that sherpa order number was not yet created
		
		
		return $resInt; 
		
	}
	
	
	//returns sherpa order number
	public static function checkOrderImported($order) {
		if (is_array($order)) {
		$order_id = (int)$order['details']['BT']->virtuemart_order_id; 
		}
		elseif (is_numeric($order)) {
			$order_id = (int)$order; 
		}
		else return false; 
		
		$db = JFactory::getDBO(); 
		$q = 'select `ai` from #__onepage_exported where `localid` = '.(int)$order_id.' limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		if (empty($res)) return false; 
		$resInt = (int)$res; 
		//by default ai = order_id which means that sherpa order number was not yet created
		if ($resInt === $order_id) return false; 
		
		return $res; 
		
	}
	
	
	public static function updateSherpaOrderAlreadyImportedRealTime($order_id) {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'opctracking.php'); 
		 $config = new JModelConfig(); 
		 $config->loadVmConfig(); 
		 VmConfig::loadJLang('com_virtuemart');
		 VmConfig::loadJLang('plg_vmpsplugin', false);
		 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 


		$orderModel = VmModel::getModel('orders'); 
		$order = array(); 
		$order_array = array(); 
		OPCtrackingHelper::getOrderVars($order_id, $arr, $order); 
		return sherpaHelper::ChangeOrderStatus($order, $extraData); 


	}
	//to sync a single order from sherpa to VM without updating token
	public static function sherpaToVmSingleBySherpaOrderNumber($OrderNumber) {
		
		 $params = self::getParams();
		 $sherpaOrderInfo = self::OrderInfo($OrderNumber); 
		 if (empty($sherpaOrderInfo)) return false; 
		 if (is_array($sherpaOrderInfo)) $sherpaOrderInfo = (object)$sherpaOrderInfo; 
		 self::sherpaToVmSingle($sherpaOrderInfo, false); 
	}
	
	public static function sherpaToVmSingle($sherpaOrderData, $updateToken=true) {
		 $params = self::getParams();
		 
		$sherpaOrderStatus = $sherpaOrderData->OrderStatus; 
			$newVmStatus = ''; 
			foreach ($params['config']->statuspair as $vm_status => $sherpa_status) {
				if ($sherpa_status === $sherpaOrderStatus) {
					$newVmStatus = $vm_status; 
				}
			}
			
			
			
			if (empty($newVmStatus)) {
				if (!empty($updateToken)) self::setNewOrderToken($sherpaOrderData->Token); 
				return; 
			}
	        $history = self::getOrderHistoryInVM($sherpaOrderData); 
			if ($history === false) {
				if (!empty($updateToken)) self::setNewOrderToken($sherpaOrderData->Token); 
				return;
			}
			
			//defineds in getOrderHistoryInVM
			$order_number = $sherpaOrderData->virtuemart_order_number;
			
			
			
			
			
		
			//$sherpaOrderData->sherpaStatus -> latest valid VM2Sherpa status on the order
			//$sherpaOrderData->current_virtuemart_order_status -> latest VM status even when sherpa status is not recognized
			
			
			//error !:
			if ($sherpaOrderData->OrderNumber === '80004074') {
				
				
				
			}
			
			
			
			
			if ($sherpaOrderData->lastSherpaStatusInVM !== $sherpaOrderData->OrderStatus) {
				if ($newVmStatus !== $sherpaOrderData->current_virtuemart_order_status) {
					
					
					
						if (isset($history[$newVmStatus])) {
							$eM = 'Cannot update virtuemart_order_id '.$sherpaOrderData->virtuemart_order_id.' to virtuemart status code '.$newVmStatus.' because status was already used before. Sherpa order number: '.$sherpaOrderData->OrderNumber.' Virtuemart order number: '.$order_number.' previous order status history '.var_export($history, true);
							if (class_exists('cliHelper')) cliHelper::debug($eM); 
							self::emailError($eM, 'Error '.$sherpaOrderData->OrderNumber.': Order Update failed from Sherpa to VM', 'Sherpa order status change detected'); 
							if (!empty($updateToken)) self::setNewOrderToken($sherpaOrderData->Token); 
							return; 
						}
			
			if (class_exists('cliHelper')) cliHelper::debug('Detected OrderNumber: '.$sherpaOrderData->OrderNumber); 
			//we must check if we really import the right order into VM so we don't send emails to old customers
			$sherpaOrderInfo = self::OrderInfo($sherpaOrderData->OrderNumber); 
			$reference = $sherpaOrderInfo['Reference']; 
			$CustomerCodePrefix = $params['config']->CustomerCodePrefix; 
			if (class_exists('cliHelper')) cliHelper::debug('Detected Order: '.$reference); 
	
		
			if ($reference !== $CustomerCodePrefix.$order_number) {
				$eM = 'System mitchmatch: Reference '.$reference.' does not match Virtuemart order in this system '.$CustomerCodePrefix.$order_number;
				if (class_exists('cliHelper')) cliHelper::debug($eM); 
				if (strpos($CustomerCodePrefix, 'TEST')===false) {
				  self::emailError($eM); 
				}
	
				if (!empty($updateToken)) self::setNewOrderToken($sherpaOrderData->Token); 
				return; 
			}
	
			
					$msg = 'Updating virtuemart_order_id '.$sherpaOrderData->virtuemart_order_id.' to virtuemart status code '.$newVmStatus.' '.$sherpaOrderData->OrderNumber.': Order Update from Sherpa to VM'.' Sherpa order status change detected';
					
					self::updateOrderStatus($sherpaOrderData->virtuemart_order_id, $newVmStatus); 
					
					self::emailError('Updating virtuemart_order_id '.$sherpaOrderData->virtuemart_order_id.' to virtuemart status code '.$newVmStatus, $sherpaOrderData->OrderNumber.': Order Update from Sherpa to VM', 'Sherpa order status change detected'); 
					if (class_exists('cliHelper')) cliHelper::debug($msg); 
					
				}
			}
			
			if (!empty($updateToken)) self::setNewOrderToken($sherpaOrderData->Token); 
	}
	
	//changes order status in VM
	public static function getChangedOrdersInSherpa() {
	   self::_getOPCMini(); 
	   $data = array(); 
	   $token_data = self::getLastOrderToken(); 
	   
	   $data['order_token'] = $token_data->order_token; 
	   
	   $xml = self::sendRequest('ChangedOrders', $data); 
	   if (self::isSoapError($xml)) 
		{
			if (class_exists('cliHelper')) cliHelper::debug('ERROR: Communication error detected in last request - ChangedOrders'); 
			return false; 
		}
	   $largest_token = 0; 
	   $datas = self::getGenericDatas($xml, array('OrderNumber', 'Token', 'OrderStatus'), $largest_token);
	   if (empty($largest_token)) $largest_token = $token_data->order_token;
	   $params = self::getParams();
	   if (class_exists('cliHelper')) cliHelper::debug(' found '.count($datas).' ChangedOrders since last token '.$token_data->order_token.' next token will be '.$largest_token); 
	   if (empty($datas)) return; 
	   
	    foreach ($datas as $sherpaOrderData) {
			self::sherpaToVmSingle($sherpaOrderData, true);
		}
		
		$largest_token++;
		self::setNewOrderToken($largest_token); 
		if (class_exists('cliHelper')) cliHelper::debug('ChangedOrders finished'); 
	}
	
	//comes from opc.php controller:
	private static function updateOrderStatus($order_id, $order_status, $notified=0) {
		
		self::_getOPCMini(); 
		$modelOrder = OPCmini::getModel('orders');
		//$order_id = $order['details']['BT']->virtuemart_order_id; 
		VirtueMartControllerOpc::emptyCache(); 
		$lastOrder = $modelOrder->getOrder($order_id); 
		
		$lastOrder['customer_notified'] = $notified;
		if (!isset($lastOrder['comments'])) $lastOrder['comments'] = ''; 
		$lastOrder['order_status'] = $order_status;
		if (isset($lastOrder['details']['BT']))
		{
			//if (!isset($lastOrder['order_status'])) 
			
			$order_id = $lastOrder['details']['BT']->virtuemart_order_id; 
			
			
			if ($lastOrder['details']['BT']->order_status === $order_status) {
				//it was already updated
				return; 
			}
		}
		if (!empty($order_id)) {
		$modelOrder->updateStatusForOneOrder($order_id, $lastOrder, false);
		
		if (class_exists('cliHelper')) cliHelper::debug('Updating order_id '.(int)$order_id.' to order status '.htmlentities($order_status)); 
		
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance(); 
		$old_status = $lastOrder['details']['BT']->order_status;
		$lastOrder['details']['BT']->order_status = $order_status;
		$dispatcher->trigger('plgOpcOrderStatusUpdate', array( &$lastOrder, $old_status ));
		
		}
		VirtueMartControllerOpc::emptyCache(); 
	}
	
	public static function getOrderHistoryInVM(&$sherpaOrderData, $onlyLast=false) {
		if (!isset($sherpaOrderData->OrderNumber)) return false; 
		$sherpa_order_number = $sherpaOrderData->OrderNumber; 
		
		$db = JFactory::getDBO(); 
		if (empty($sherpaOrderData->virtuemart_order_id)) {
		/*
		$q = 'select `localid`, `ai` from #__onepage_exported where `ai` = \''.$db->escape($sherpa_order_number).'\' '; 
		$q .= ' limit 1'; 
		$db->setQuery($q); 
		$order_id = $db->loadResult(); 
		*/
		
		$order_id = self::checkOrderExported($sherpaOrderData->OrderNumber); 
		if (empty($order_id)) return false; 
		
		$order_id = (int)$order_id; 
		$sherpa_order_number_int = (int)$sherpa_order_number; 
		if ($sherpa_order_number_int === $order_id) return false; 
		
		
		$sherpaOrderData->virtuemart_order_id = $order_id; 
		}
		
		$q = 'select `order_status_code` as `order_status` from `#__virtuemart_order_histories` where `virtuemart_order_id` = '.(int)$order_id.' order by `created_on` asc'; 
		if ($onlyLast) $q .= ' limit 1'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (empty($res)) return array(); 
		
		$params = self::getParams(); 
		
		$ret = array(); 
		
		$sherpaOrderData->lastSherpaStatusInVM = ''; 
		
		foreach ($res as $row) {
			$vm_status = $row['order_status']; 
			
			
			$sherpaOrderData->current_virtuemart_order_status = $vm_status; 
			
			
			if (!isset($params['config']->statuspair->$vm_status)) continue; 
			
			$sherpaStatus = $params['config']->statuspair->$vm_status;
			if (!empty($sherpaStatus)) {
				$ret[$vm_status] = $sherpaStatus; 
				$sherpaOrderData->lastSherpaStatusInVM = $sherpaStatus;
			}
			
		}
		
		if (empty($sherpaOrderData->virtuemart_order_number)) {
		$q = 'select `order_number` from #__virtuemart_orders where `virtuemart_order_id` = '.(int)$order_id; 
		$db->setQuery($q); 
		$virtuemart_order_number = $db->loadResult(); 
		$sherpaOrderData->virtuemart_order_number = $virtuemart_order_number;
		}
		return $ret; 
		
	}
	
	public static function getGenericDatas(&$xml, $keys=array(), &$largest_token=0) {
		$datas = array(); 
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		//$r2 = $soap->Body; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		if (!empty($keys)) {
		 $key1 = reset($keys); 
		}
		
		//$largest_token = 0; 
		
		foreach ($res->children() as $i) {
			$respItems = $i->ResponseValue->children(); 
			foreach ($respItems as $keyname=>$si) {
				
				if (!empty($key1)) {
				  $sku = (string)$si->$key1; 
				}
				else {
					$sku = (string)$keyname; 
				}
			    if (!empty($keys)) {
				$md = new stdClass(); 
				foreach ($keys as $kx ) {
					$md->$kx = (string)$si->$kx; 
				}
				
				if (isset($si->Token)) {
				 $token = (int)$si->Token; 
				 if ($token > $largest_token) $largest_token = $token; 
				}
				
				$datas[$sku] = $md; 
				}
				else {
					$datas[$sku] = (string)$si; 
				}
			}
		}
		return $datas; 
	}
	
	
	public static function token($type, $last_token=0, &$new_token=0) {
		self::checkSherpaTable(); 
		
		$tokenID = 0; 
		switch($type) {
			case 'order': 
				$tokenID = 1; 
				break; 
			case 'item': 
			case 'ChangedStock':
				$tokenID = 3; 
				break; 
			case 'supplier':
			case 'ChangedItemSuppliersWithDefaults':
				$tokenID = 4; 
				break; 
			case 'ChangedAssemblyStock':
				$tokenID = 6; 
				break;
			default: 
				$tokenID = 5; 
		}
		$db = JFactory::getDBO(); 
		$q = 'select * from #__sherpa_counter where `keycode` = '.(int)$tokenID; 
		if (!empty($last_token)) {
			$q .= ' and `value` = '.$last_token;
			$db->setQuery($q); 
			$row = $db->loadAssoc(); 			
		}
		else {
		 $db->setQuery($q); 
		 $row = $db->loadAssoc(); 
		 $last_token = (int)$row['value']; 
		}
		
		if (!empty($new_token)) {
			if (empty($row)) {
				$q = 'select * from #__sherpa_counter where `keycode` = '.(int)$tokenID; 
				$db->setQuery($q); 
				$row = $db->loadAssoc(); 
				if (empty($row)) {
					$q = 'insert into `#__sherpa_counter` (`keycode`, `value`) values ('.(int)$tokenID.', '.(int)$new_token.')'; 
					$db->setQuery($q); 
					$db->execute(); 
				}
			}
			else {
			$q  = 'update #__sherpa_counter set `value` = '.(int)$new_token.' where `keycode` = '.(int)$tokenID.' and `value` < '.$new_token; ; 
			$db->setQuery($q); 
			$db->execute(); 
			}
			
		}
		else {
			//$q  = 'update #__sherpa_counter set `value` = `value` + 1 where `keycode` = '.(int)$tokenID;
			$new_token = $last_token; 
		}
		
		
		
		return $new_token; 
	}
	
	public static function setNewOrderToken($order_token) {
		
		$emp = 0; 
		return self::token('order', $emp, $order_token); 
		
		self::checkSherpaTable(); 
		$db = JFactory::getDBO(); 
		$q  = 'update #__sherpa_counter set `value` = '.(int)$order_token.' where `keycode` = 1'; 
		$db->setQuery($q); 
		$db->execute(); 
		$rows = $db->getAffectedRows(); 
		if (empty($rows)) {
			$q  = 'insert into #__sherpa_counter (`keycode`, `value`) values (1, '.(int)$order_token.') '; 
			try {
			 $db->setQuery($q); 
			 $db->execute(); 
			}
			catch (Exception $e) {
				//silent fail
			}
		}
		
		$time = time(); 
		$q  = 'update #__sherpa_counter set `value` = '.(int)$time.' where `keycode` = 2'; 
		$db->setQuery($q); 
		$db->execute(); 
		$rows = $db->getAffectedRows(); 
		if (empty($rows)) {
			$q  = 'insert into #__sherpa_counter (`keycode`, `value`) values (2, '.(int)$time.') '; 
			try {
			 $db->setQuery($q); 
			 $db->execute(); 
			}
			catch (Exception $e) {
				//silent fail
			}
		}
		
	}
	
	public static function getLastOrderToken() {
		self::checkSherpaTable(); 
		$db = JFactory::getDBO(); 
		$q = 'select `keycode`, `value` from `#__sherpa_counter` where `keycode` = 1 or `keycode` = 2 limit 2'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$ret = new stdClass(); 
		$ret->order_token = 0; 
		$ret->order_timestamp = 0; 
		foreach ($res as $row) {
			$code = (int)$row['keycode']; 
			$val = (int)$row['value']; 
			if ($code === 1) {
				$ret->order_token = $val;
			}
			elseif ($code === 2) {
				$ret->order_timestamp = $val; 
			}
		}
		return $ret; 
	}
	
	
	public static function OrderInfo($sherpa_order_number) {
		self::_getOPCMini(); 
		$data = array(); 
		$data['orderNumber'] = $sherpa_order_number;
		
		$xml = sherpaHelper::sendRequest('OrderInfo', $data); 
		
		if (self::isSoapError($xml)) 
		{
			
			if (class_exists('cliHelper')) cliHelper::debug('ERROR: Communication error detected in last request - OrderInfo'); 
			return false; 
		}
		if (class_exists('cliHelper')) cliHelper::debug('OrderInfo finished: '.$sherpa_order_number); 
		$datas = self::getGenericDatas($xml);
		if (!empty($datas)) {
		  unset($datas['Remarks']); 
		}
		if (class_exists('cliHelper')) cliHelper::debug('OrderInfo generic datas parse finished: '.$sherpa_order_number); 
		return $datas; 
		
	}
	
	public static function checkForOnHoldAttribute($order, &$color_code='') {
		foreach ($order['items'] as $item) {
			if (!empty($item->product_attribute)) {
				if (!empty($item->product_attribute)) {
				$jdata = json_decode($item->product_attribute, true); 
				foreach ($jdata as $custom_id => $row) {
					foreach ($row as $customfield_id => $value) {
						if (!isset($value['comment'])) continue; 
						$customer_value = $value['comment']; 
						if (empty($customer_value)) continue; 
						$color_code = $customer_value;
						return true; 
					}
				}
			}
			}
		}
		
		return false; 
	}
	//changes order status in Sherpa
	public static function ChangeOrderStatus($order, $extraData) {
		
		static $recursion; 
		if (!empty($recursion)) return; 
		
		self::_getOPCMini(); 
		$sherpa_order_number = self::checkOrderImported($order); 
		
		
		
		if (empty($sherpa_order_number)) {
			
			return; 
		}
		
		$sherpaOrderInfo = self::OrderInfo($sherpa_order_number); 
		
		
		$data = array(); 
		$data['orderNumber'] = $sherpa_order_number;
		$new_status = $order['details']['BT']->order_status; 
		$params =  self::getParams(); 
		$newSherpaStatus = $params['config']->statuspair->$new_status;
		//this prevents pending orders to be included (if we don't pair the status agains sherpa, such as Pending -> OnHold):
		if (empty($newSherpaStatus)) return; 
		
		
		$painting_vm_status = $params['config']->customstatus;
		$requires_painting = self::checkForOnHoldAttribute($order); 
		
		
		
		if (empty($painting_vm_status)) $requires_painting = false; 
		
		if (!empty($requires_painting)) {
			$sherpaOrderData = new stdClass(); 
			$sherpaOrderData->OrderNumber = $sherpa_order_number;
			$sherpaOrderData->virtuemart_order_id = $order['details']['BT']->virtuemart_order_id; 
			$sherpaOrderData->virtuemart_order_number = $order['details']['BT']->order_number; 
			
			$history = self::getOrderHistoryInVM($sherpaOrderData); 
			if (!in_array($painting_vm_status, $history)) {
				//we need to update both VM and Sherpa with the new status since product requires painting which wasn't yet processed
				if (isset($params['config']->statuspair->$painting_vm_status)) {
				  $newSherpaStatus = $params['config']->statuspair->$painting_vm_status;
				  
				  $recursion = true; 
				   
				  
				  
				  self::updateOrderStatus($order['details']['BT']->virtuemart_order_id, $painting_vm_status, 1); 
				  $order['details']['BT']->order_status = $painting_vm_status;
				  $order['details']['ST']->order_status = $painting_vm_status;
				  $order['order_status'] = $painting_vm_status;
				}
				
			}
			
			
		}
		
		
		
		
		$recursion = false; 
		
		
	
		if ($sherpaOrderInfo['OrderStatus'] === $newSherpaStatus) return; 
		
		$reference = $sherpaOrderInfo['Reference']; 
		
		$CustomerCodePrefix = $params['config']->CustomerCodePrefix; 
		$order_number = $order['details']['BT']->order_number;
		
		//compare the prefixes as well:
		
	
		
		if ($reference !== $CustomerCodePrefix.$order_number) return; 								  
		if (empty($newSherpaStatus)) return; 
		
		if (class_exists('cliHelper')) cliHelper::debug('Updating sherpa order number '.(int)$sherpa_order_number.' to order status '.htmlentities($newSherpaStatus)); 
		
		$data['newStatus'] = $newSherpaStatus; 
		$xml = self::sendRequest('ChangeOrderStatus', $data); 
		
		
		
		
		//will mark it as an error:
		if (self::isSoapError($xml)) 
		{
			if (class_exists('cliHelper')) cliHelper::debug('ERROR: Communication error detected in last request - ChangeOrderStatus'); 
			return false; 
		}
		
		
		
		return 'Sherpa status updated to '.$newSherpaStatus; 
	}
	
	//returns true if an error
	public static function isSoapError(&$xml) {
		if ($xml === false) return true; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return true; 
		if (!method_exists($xml, 'getNamespaces')) return true; 
		$ns = $xml->getNamespaces(true);
		
		 $soap = $xml->children($ns['soap']);
		 if (isset($soap->Body->Fault->Reason->Text)) {
		 $z = (string)$soap->Body->Fault->Reason->Text;
		 return true; 
		 }
	}
	
	
	public static function updateSherpaOrderAlreadyImported($order, $params, $extra) {
		$new_status = $order['details']['BT']->order_status; 
		
		$order_id = (int)$order['details']['BT']->virtuemart_order_id; 
		
		$newSherpaStatus = $params['config']->statuspair->$new_status;
		
		//do nothing when not configured
		if (empty($newSherpaStatus)) return; 
		
		
	$db = JFactory::getDBO(); 
	$q = 'select `enabled` from #__extensions where `element` = "opccron" order by `enabled` desc limit 1'; 
	$db->setQuery($q); 
	$x = $db->loadResult(); 
	if (!empty($x)) {
		$callable = array('sherpaHelper', 'updateSherpaOrderAlreadyImportedRealTime'); 
		$args = array($order_id); 
		$require = array(
		 JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'compatibility.php',
		 JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php',
		 JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php',
		 JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'numbering.php',
		 JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'
		 ); 
		 
		 if (php_sapi_name() !== 'cli') {
		 if (JFactory::getApplication()->isSite()) {
		  $ret = JFactory::getApplication()->triggerEvent('plgAddJobInCron', array($callable, $args, $require)); 
		
		foreach ($ret as $r) { 
		  if ($r === true) return; 
		}
		 }
	}
	}
	
	
	if (self::updateSherpaOrderAlreadyImportedRealTime($order_id) === false) {
		    if (!empty($x)) {
			 //$ret = JFactory::getApplication()->triggerEvent('plgAddJobInCron', array($callable, $args, $require)); 
			}
			JFactory::getApplication()->enqueueMessage('Sherpa order status update had failed'); 
	}
	
	}
	
	public static function checkForOrderUpdate($order, $params, $data, $previous_action=true) {
		
		if (self::checkOrderImported($order)) {
			return self::updateSherpaOrderAlreadyImported($order, $params, $data); 
		}
		
		return $previous_action; 
	}
	
	public static function sendOrder($order, $extraData) {
		if (empty($order)) return false; 
		$params =  self::getParams(); 
		if (empty($params)) return; 
		$data = array(); 
		$securityCode = $params['config']->secret_key;
		foreach ($params['config'] as $k=>$v) {
			$data[$k] = $v; 
		}
		$data['config'] = $params; 
		$data['securityCode'] = $securityCode; 
		//$data['counter'] = self::getNextCounter(); 
		$data['maxResult'] = 99999; 
		$url = $params['config']->soap_url;
		$data['soap_url'] = $url; 
		$data['orders'] = array($order); 
		$data['extra'] = $extraData;
		
		$data['debug'] = (int)$params['config']->debug; 
		
		/*
		if (self::checkOrderImported($order)) {
			return self::updateSherpaOrderAlreadyImported($order, $params, $data); 
		}
		*/
		
		
		
		$cmd = 'GetResponse'; 
		$xml = false; 
		try {
		$xmlResp = xmlHelper::get($cmd, $data, $errMsg, $xmlTxt, $xmlDebug); 
		
		if ($xmlResp !== false) {
			$xml = self::processXML($xmlResp); 
		}
		
		
		
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) {
			self::storeExportXMLError($xmlDebug, $xmlResp, 'debug'); 
			self::storeExportXMLError($xmlTxt); 
			self::emailError('error importing order: '.$order['details']['BT']->virtuemart_order_id."\n".$errMsg."\n Debug Request:\n".$xmlDebug); 
			OnepageTemplateHelper::$print_output = true; 
			
			if (php_sapi_name() === 'cli') {
				 throw new Exception('error importing order.');
				 return false; 
			}
			
			return self::checkForOrderUpdate($order, $params, $data, false); ; 
			
		}
		
		
		
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) {
			if (php_sapi_name() === 'cli') {
				 throw new Exception('error importing order.');
				 return false; 
			}
			
			
			return self::checkForOrderUpdate($order, $params, $data, false); 
		}
		
		
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$xmlres = $soap->Body->children();
		
		$data = array(); 
		foreach ($xmlres->children() as $i) {
			$sx = (string)$i[0]; 
			if (!empty($sx)) {
			   $ret = simplexml_load_string($sx); 	
			   foreach ($ret->children() as $key=>$ii) {
				  $kn = (string)$key; 
				   {
							foreach ($ii->children() as $k2i => $v2i) {
								
								foreach ($v2i->children() as $k3i => $v3i) {
								 
								$k2i = (string)$k2i; 
								$val = (string)$v3i; 
								
								$k3iT = (string)$k3i; 
								
								$data[$kn.'_'.$k2i.'_'.$k3iT] = $val; 
								}		


								$k2iT = (string)$k2i; 
								$val = (string)$v2i; 
								
								if (!empty($val)) {
								 $data[$kn.'_'.$k2iT] = $val; 
								}
								
								
							}
				   }
				   {
					$kn = (string)$key; 
				    $val = (string)$ii; 
					if (!empty($val)) {
				     $data[$kn] = $val; 
					}
				   }
				   
			   }
			   
			}
			
		}
		
		 
		}
		catch(Exception $e) {
			self::storeExportXMLError($xmlDebug, $xmlResp, 'debug'); 
			self::storeExportXMLError($xmlTxt); 
			$errMsg = (string)$e; 
			self::emailError('error importing order: '.$order['details']['BT']->virtuemart_order_id."\n".$errMsg."\n Debug Request:\n".$xmlDebug); 
			OnepageTemplateHelper::$print_output = true; 
			OnepageTemplateHelper::$has_error = true; 
			if (php_sapi_name() === 'cli') {
				 throw new Exception('error importing order.');
				 return false; 
			}
			
			
			return self::checkForOrderUpdate($order, $params, $data, false);  
		}
		self::storeExportXML($order['details']['BT']->virtuemart_order_id, $xmlTxt, $xmlResp); 
		$emptyString = ''; 
		self::storeExportXML($order['details']['BT']->virtuemart_order_id, $xmlDebug, $emptyString, $order['details']['BT']->virtuemart_order_id.'_debug'); 
		
		if (isset($data['ResultCode']) && ($data['ResultCode'] === 'I01')) {
			$msg = $data['ResultDescription']; 
			self::emailError($msg."\nOrder ID:\n".$order['details']['BT']->virtuemart_order_id."\nRequest:\n".$xmlDebug); 
			OnepageTemplateHelper::$print_output = true; 
			OnepageTemplateHelper::$has_error = true; 
			self::storeExportXMLError($xmlDebug, $xmlResp, 'debug'); 
			JFactory::getApplication()->enqueueMessage($msg, 'error'); 
			
			if (php_sapi_name() === 'cli') {
				 throw new Exception('error importing order.');
				 return false; 
			}
			
		
			return self::checkForOrderUpdate($order, $params, $data, false);  
		 }
		 
		
		 
		 if (isset($data['ResultCode']) && ($data['ResultCode'] === 'I00')) {
			 if (!empty($data['Orders_Order_OrderNumber'])) {
				 
			 $SherpaOrderNumber = $data['Orders_Order_OrderNumber']; 
			 $OnepageTemplateHelper = new OnepageTemplateHelper();
			 $tid = (int)$params['tid']; 
			 $OnepageTemplateHelper->setSpecials($tid, (int)$order['details']['BT']->virtuemart_order_id, array($SherpaOrderNumber), 'CREATED'); 
			 
			 
			 $app = JFactory::getApplication();
			 $msg = 'New order imported to sherpa '.$SherpaOrderNumber;
			  if ($app->isAdmin()) {
			    $app->enqueueMessage($msg, 'notice'); 
			  }
			  if (empty(OnepageTemplateHelper::$has_error)) OnepageTemplateHelper::$has_error = false; 
			  if (class_exists('cliHelper')) cliHelper::debug($msg); 
			 }
			 
			 
			 self::checkForOrderUpdate($order, $params, $data, true); 
			 return $xml; 
		 }
		
		
		if (!empty($errMsg)) {
			OnepageTemplateHelper::$has_error = true; 
			if (php_sapi_name() === 'cli') {
				 throw new Exception('error importing order.');
				 return false; 
			}
			
			
			return self::checkForOrderUpdate($order, $params, $data, false);  
		}
		
		
		return $xml;  
	}
	
	public static function storeExportXML($order_id, &$data, &$respTxt='', $suffix='', $status='CREATED') {
		
		$ehelper = new OnepageTemplateHelper();
		
		$templates = $ehelper->getExportTemplates('ALL');
		$et = array(); 
		foreach ($templates as $t) {
			if ($t['file'] === 'sherpa.php') {
				$et = $t; 
				break; 
			}
		}
		
		
		if (empty($et)) return; 
		
		
		$tid = (int)$et['tid']; 
		$ehelper->prepareDirectory($tid);
		
		$file = $ehelper->getFileName2Save($tid, $order_id, $suffix);
		
		$file = str_replace('.php', '.xml', $file); 
		
		$file2 = str_replace('.xml', '.response.xml', $file); 

		$params =  self::getParams(); 
		
		if (!empty($params['config']->debug)) {
		
		if ((!empty($respTxt)) && (is_string($respTxt))) {
		if (JFile::write($file2, $respTxt)!==false) {
			
		}
		}
		if (!empty($data)) {
		if (JFile::write($file, $data)!==false)
		{
			
		}
		}
		}
		
		if (!empty($status)) {
		  $ehelper->setStatus($tid, $order_id, $status, urlencode($file));
		}
		
		
		
		
	}
	
	public static function checkSherpaTable() {
		
		self::_getOPCMini(); 
		if (!OPCMini::tableExists('sherpa_counter')) {
		$db = JFactory::getDBO(); 
		$q = "CREATE TABLE IF NOT EXISTS `#__sherpa_counter` (
			`keycode` int(11) NOT NULL,
			`value` bigint(20) NOT NULL,
			UNIQUE KEY `key_index` (`keycode`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='on each call to sherpa, counter has to be raised by one'";
		$db->setQuery($q); 
		$db->execute(); 
		OPCMini::clearTableExistsCache(); 
		}
		
		
		
	}
	
	public static function _getOPCMini() {
		if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php')) {
			echo 'OPC not found, install RuposTel One Page Checkout'; 
			JFactory::getApplication()->close(); 
		}
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'dbcache.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'clihelper.php'); 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'opc.php');
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'clihelper.php'); 
		cliHelper::$logfile = JPATH_SITE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'sherpa.log.php'; 
	}
	
	public static function getNextCounter($cmd='') {
		if (empty($cmd)) return 0; 
		
		$token = self::token($cmd); 
		
		
		return $token; 
		return 0; 
		self::checkSherpaTable(); 
		
		$db = JFactory::getDBO(); 
		$q = 'update #__sherpa_counter set `counter` = `counter` + 1 where 1=1'; 
		$db->setQuery($q); 
		$db->execute(); 
		
		$q = 'select `counter` from #__sherpa_counter where 1=1'; 
		$db->setQuery($q); 
		//$counter = $db->loadAssocList(); 
		$counter = $db->loadResult(); 
		return (int)$counter; 
		
		
	}
	
	public static function resetCounter() {
		return 0; 
		self::checkSherpaTable(); 
		
		$db = JFactory::getDBO(); 
		$q = 'update #__sherpa_counter set `counter` = 0 where 1=1'; 
		$db->setQuery($q); 
		$db->execute(); 
	}
	public static function isStockOrAssembly($sku) {
		
		//only positive cache:
		static $cache; 
		if (isset($cache[$sku])) return $cache[$sku]; 
		$db = JFactory::getDBO(); 
		$table = self::getTableNameFromCmd('ChangedStock'); 
		$q = 'select `product_sku` from `'.$table.'` where `product_sku` = \''.$db->escape($sku).'\' limit 1'; 
		$db->setQuery($q); 
		$isStock = $db->loadResult(); 
		
		if (!empty($isStock)) {
			$cache[$sku] = 'Stock'; 
			return 'Stock'; 
		}
		
		$table = self::getTableNameFromCmd('ChangedAssemblyStock'); 
		$q = 'select `product_sku` from `'.$table.'` where `product_sku` = \''.$db->escape($sku).'\' limit 1'; 
		$db->setQuery($q); 
		$isAssembly = $db->loadResult(); 
		if (!empty($isAssembly)) {
			$cache[$sku] = 'Assembly'; 
			return 'Assembly'; 
		}
		
		return false; 
	}
	
	//checks if product is an assembly or stock in sherpa
	//if not found, returns false
	//returns string: Assembly or Stock
	public static function getProductType($sku) {
		
		$MPNs = array(); 
		if (strpos($sku, '+')!==false) {
			$MPNs = explode('+', $sku); 
		}
		
			/*$data = self::ChangedStock(); 
		if (isset($data[$sku])) return 'Stock'; 
			*/
		$isStockOrAssembly = self::isStockOrAssembly($sku); 
		if ($isStockOrAssembly === 'Stock') return 'Stock'; 
		if ($isStockOrAssembly === 'Assembly') return 'Assembly'; 
		
		
		/*
		$data2 = self::ChangedAssemblyStock(); 
		if (isset($data2[$sku])) return 'Assembly'; 
		*/
		
		
		if (!empty($MPNs)) {
			$all_found = 0; 
			foreach ($MPNs as $partial_sku) {
				$isStockOrAssembly = self::isStockOrAssembly($partial_sku);
				if (!empty($isStockOrAssembly)) {
					$all_found++; 
				}
				/*
				if (isset($data[$partial_sku])) {
					$all_found++; 
				}
				elseif (isset($data2[$partial_sku])) {
					$all_found++; 
				}
				*/
			}
			if ($all_found === count($MPNs)) return 'Stock'; 
		}
		
		return false; 
	}
	
	
	public static function createProducts($orderItems) {
		foreach ($order['items'] as $item) {
			
		}
	}
	
	
	
	public static function getStockCreateProduct($sku, $mpn) {
		$sherpaTypeMPN = sherpaHelper::getProductType($mpn); 
		$sherpaTypeSKU = sherpaHelper::getProductType($sku); 
		
		
		
		if (($sherpaTypeMPN === false) || ($sherpaTypeSKU === false)) {
			self::$disable_cache = true; 
		}
		
		$sherpaTypeMPN_orig = $sherpaTypeMPN; 
		$sherpaTypeSKU_orig = $sherpaTypeSKU; 
		
		 $filter = array(); 
		 $filter[$mpn] = $mpn; 
		 $filter[$sku] = $sku; 
		 	
			$MPNs = array(); 
				if (strpos($mpn, '+')!==false) {
					$MPNs = explode('+', $mpn); 
				}
				
		 foreach ($MPNs as $partial_mpn) {
			$filter[$partial_mpn] = $partial_mpn; 
		}
		
		
	
		
				$found = true; 
				
				if (($sherpaTypeMPN === false) && ($sherpaTypeSKU === false)) {
					$sherpaTypeMPN = 'Stock'; 
					$sherpaTypeSKU = 'Assembly'; 
					$found = false; 
				}
				else
				if (($sherpaTypeSKU === false) && ($sherpaTypeMPN === 'Stock')) {
					$sherpaTypeSKU = 'Assembly'; 
					$found = false; 
				}
				else 
				if (!empty($sherpaTypeSKU)) {
					$found = true; 
				}
				
				
				
				if (($mpn !== $sku) && (!empty($mpn))) {
					if ($sherpaTypeMPN === 'Stock') $sherpaTypeSKU = 'Assembly'; 
				}
				
				if ($found) {
				  
				   //stAn - 10.1.2020, we won't resync existing products:
				   return; 
				   //return self::getAllStock($filter); 
				}
				
				
				if ($found) {
					if (class_exists('cliHelper')) cliHelper::debug('create new product '.$sku.' '.$mpn.' FOUND'); 
				}
				else {
					if (class_exists('cliHelper')) cliHelper::debug('create new product '.$sku.' '.$mpn.' NOT FOUND'); 
				}
				
				
		
				$items = array(); 
				
				
				//stAn, update assembly only if it didn't exists:
				if ($sherpaTypeSKU_orig === false) 
				{
				$sku_item = new stdClass(); 
				$sku_item->ItemCode = $sku; 
				$sku_item->ItemType = $sherpaTypeSKU; 
				if ($sherpaTypeSKU === 'Assembly') {
					$sku_item->assemblyCode = array(); 
					if (empty($MPNs)) {
					  $sku_item->assemblyCode[$mpn] = $mpn; 
					}
					else {
						
						foreach ($MPNs as $partial_mpn) {
							$sku_item->assemblyCode[$partial_mpn] = $partial_mpn; 
						}
					}
				}
				
				$product = self::getProductBySku($sku); 
				$sku_item->vm_product = $product;
				$sku_item->Description = $product->product_name; 
				$items[$sku] = $sku_item; 
				}
				
				
				if (!empty($MPNs)) {
					foreach ($MPNs as $partial_mpn) {
						$sherpaTypePartial_MPN = sherpaHelper::getProductType($partial_mpn); 
						if ($sherpaTypePartial_MPN === false) $sherpaTypePartial_MPN = 'Stock'; 
						
						$mpn_item = new stdClass(); 
						$mpn_item->ItemType = $sherpaTypePartial_MPN;  //this could be also hard written to Stock
						$mpn_item->ItemCode = $partial_mpn; 
				
						$product = self::getProductBySku($partial_mpn); 
						$mpn_item->vm_product = $product;
						$mpn_item->Description = $product->product_name; 
						$items[$partial_mpn] = $mpn_item; 
					}
					
				}
				else {
				//if ($sherpaTypeMPN_orig === false) 
				{
				$mpn_item = new stdClass(); 
				$mpn_item->ItemType = $sherpaTypeMPN; 
				$mpn_item->ItemCode = $mpn; 
				
				$product = self::getProductBySku($mpn); 
				$mpn_item->vm_product = $product;
				$mpn_item->Description = $product->product_name; 
				$items[$mpn] = $mpn_item; 
				}
				}
				
				
				
				
				if (!empty($items)) {
					self::sendNewItems($items); 
				}
				 //we won't update stock upon creating a new product
				 //return; 
				 return self::getAllStock($filter); 
	}
	
	public static function getAllStock($filter=array()) {
		
		$data = self::ChangedStockObj(); 
		
		
		$data2 = self::ChangedAssemblyStockObj(); 
		if (!is_array($data)) {
			if (!empty($filter)) {
				$ret = array(); 
			if (is_array($data2)) {
				foreach ($filter as $sku=>$skuval) {
					if (isset($data2[$sku])) $ret[$sku] = $data2[$sku]; 
				}
				return $ret; 
			 }
			}
			return $data2; 
		}
		if (!is_array($data2)) {
			if (!empty($filter)) {
				$ret = array(); 
			if (is_array($data)) {
				foreach ($filter as $sku=>$skuval) {
					if (isset($data[$sku])) $ret[$sku] = $data[$sku]; 
				}
				return $ret; 
			 }
			}
			return $data; 
		}
		$ret = array(); 
		if (empty($filter)) {
		foreach ($data as $sku=>$row) {
			
			 $data2[$sku] = $row; 
			
		}
		return $data2; 
		}
		else {
			foreach ($data as $sku => $val) {
			if ((!empty($filter)) && (isset($filter[$sku]))) {
			 $ret[$sku] = $val; 
			}
			}
			foreach ($data2 as $sku => $val) {
			if ((!empty($filter)) && (isset($filter[$sku]))) {
			 $ret[$sku] = $val; 
			}
			}
			return $ret; 
		}
		
		return $data2; 
	}

	//this function needs caching
	public static function ChangedItemSuppliersWithDefaults($merge=true) {
		static $cache; 
		if ($merge && (!empty($cache))) return $cache; 
		
		$datas = array(); 
		
		$xml = self::sendRequest('ChangedItemSuppliersWithDefaults'); 
		if ($xml === false) return $datas; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return $datas; 
		if (!method_exists($xml, 'getNamespaces')) return $datas; 
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		//$r2 = $soap->Body; 
		$maxToken = 0; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		foreach ($res->children() as $i) {
			$stockItems = $i->ResponseValue->children(); 
			foreach ($stockItems as $si) {
				$sku = (string)$si->ItemCode; 
			    $stock = (int)$si->SupplierStock; 
				//$adate = (int)$si->DeliveryPeriod;
				$SupplierCode = (int)$si->SupplierCode; 
				$pref = (string)$si->Preferred; ; 
				if ($pref === 'true') {
					$pref = 1; 
				}
				else {
					$pref = 0; 
				}
				$obj = new stdClass(); 
				$obj->supplier_stock = $stock; 
				$obj->supplier_id = $SupplierCode; 
				$obj->product_sku = $sku; 
				$obj->prefered = $pref; 
				$obj->token = (int)$si->Token;
				if ($obj->token > $maxToken) {
					$maxToken = $obj->token; 
				}
				$datas[] = $obj; 
			}
		}
		
		self::tableStorage('ChangedItemSuppliersWithDefaults', $datas, array('product_sku'), $merge); 
		if (!empty($maxToken)) {
		self::token('ChangedItemSuppliersWithDefaults', 0, $maxToken); 
		}
		$xml = null; 
		if ($merge) {
		$cache = $datas; 
		}
		return $datas; 
		
	}
	
	public static function getProductIds($sku) {
		$ids = array(); 
		$db = JFactory::getDBO(); 
		$q = 'select `virtuemart_product_id` from `#__virtuemart_products` where `product_sku` = \''.$db->escape($sku).'\' or `product_mpn` = \''.$db->escape($sku).'\''; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (!empty($res)) {
			foreach ($res as $row) {
				$id = (int)$row['virtuemart_product_id']; 
				$ids[$id] = $id; 
			}
		}
		return $ids; 
	}
	//this function needs caching
	public static function ChangedStockObj($merge=true) {
		static $cache; 
		if ($merge && (!empty($cache))) return $cache; 
		
		$datas = array(); 
		
		$xml = self::sendRequest('ChangedStock'); 
		if ($xml === false) return $datas; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return $datas; 
		if (!method_exists($xml, 'getNamespaces')) return $datas; 
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		$maxToken = 0; 
		//$r2 = $soap->Body; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		foreach ($res->children() as $i) {
			$stockItems = $i->ResponseValue->children(); 
			foreach ($stockItems as $si) {
				$sku = (string)$si->ItemCode; 
			    $stock = (int)$si->Available; 
				$adate = (string)$si->ExpectedDate;
				
				$obj = new stdClass(); 
				$obj->product_in_stock = $stock; 
				$obj->product_available_date = $adate; 
				$obj->product_sku = $sku; 
				$obj->token = (int)$si->Token; 
				if ($obj->token > $maxToken) {
					$maxToken = $obj->token; 
				}
				$datas[$sku] = $obj; 
			}
		}
		
		self::tableStorage('ChangedStock', $datas, array('product_sku'), $merge); 
		
		if (!empty($maxToken)) {
		self::token('ChangedStock', 0, $maxToken); 
		}
		$xml = null; 
		if ($merge) {
		$cache = $datas; 
		}
		return $datas; 
		
	}
	//this function needs caching
	public static function ChangedStock($merge=true) {
		
		$datas = self::ChangedStockObj($merge); 
		$ret = array(); 
		foreach ($datas as $obj) {
			$ret[$obj->product_sku] = (int)$obj->product_in_stock; 
		}
		return $ret; 
		
		static $cache; 
		if (!empty($cache)) return $cache; 
		
		$datas = array(); 
		
		$xml = self::sendRequest('ChangedStock'); 
		if ($xml === false) return $datas; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return $datas; 
		if (!method_exists($xml, 'getNamespaces')) return $datas; 
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		//$r2 = $soap->Body; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		foreach ($res->children() as $i) {
			$stockItems = $i->ResponseValue->children(); 
			foreach ($stockItems as $si) {
				$sku = (string)$si->ItemCode; 
			    $stock = (int)$si->Available; 
				
				$datas[$sku] = $stock; 
			}
		}
		$xml = null; 
		
		return $datas; 
		
	}
	
	public static function emailError($err, $subj='Sherpa Error', $first_line='Sherpa Error Detected') {

			$app = JFactory::getApplication();
			
			  if ($app->isAdmin()) {
				$msg = $subj.' '.$err;
			    $app->enqueueMessage($msg, 'notice'); 
			  }
	
		$params =  self::getParams(); 
		if (empty($params)) return; 
		$data = array(); 
		$recipient = $params['config']->error_email;
		
		if (empty($recipient)) return ''; 
		
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$sender = array( 
			$config->get( 'mailfrom' ),
			$config->get( 'fromname' ) 
		);
		$mailer->setSubject($subj);
		$mailer->setSender($sender);
		if (strpos($recipient, ',') !== false) {
			$recipient = explode(',', $recipient); 
		}
		else {
		 $recipient = array($recipient);
		}
	    $mailer->addRecipient($recipient);
		$body   = $first_line."\n".$err;
    
		$mailer->isHtml(false);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);
		$send = $mailer->Send();
	}
	
	public static function ChangedAssemblyStockObj($merge=true) {
		static $cache; 
		if ($merge && (!empty($cache))) return $cache; 
		
		$datas = array(); 
		
		$data = array(); 
		//$data['debug'] = true; 
		
		$xml = self::sendRequest('ChangedAssemblyStock', $data); 
		if ($xml === false) return $datas; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return $datas; 
		if (!method_exists($xml, 'getNamespaces')) {
			return $datas; 
		}
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		$maxToken = 0; 
		//$r2 = $soap->Body; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		foreach ($res->children() as $i) {
			$stockItems = $i->ResponseValue->children(); 
			foreach ($stockItems as $si) {
				$sku = (string)$si->ItemCode; 
			    $stock = (int)$si->Available; 
				$adate = (string)$si->ExpectedDate;
				
				$obj = new stdClass(); 
				$obj->product_in_stock = $stock; 
				$obj->product_available_date = $adate; 
				$obj->product_sku = $sku; 
				$obj->token = (int)$si->Token; 
				if ($obj->token > $maxToken) {
					$maxToken = $obj->token; 
				}
				$datas[$sku] = $obj; 
				//$datas[$sku] = $stock; 
			}
		}
		
		
		self::tableStorage('ChangedAssemblyStock', $datas, array('product_sku'), $merge); 
		if (!empty($maxToken)) {
		self::token('ChangedAssemblyStock', 0, $maxToken); 
		}
		
		
		
		
		$xml = null; 
		if ($merge) {
		 $cache = $datas; 
		}
		return $datas; 
		
	}
	
	public static function ChangedAssemblyStock($merge=true) {
		$datas = self::ChangedAssemblyStockObj($merge); 
		$ret = array(); 
		foreach ($datas as $obj) {
			$ret[$obj->product_sku] = (int)$obj->product_in_stock; 
		}
		return $ret; 
		
		static $cache; 
		if (!empty($cache)) return $cache; 
		
		$datas = array(); 
		
		$data = array(); 
		
		
		$xml = self::sendRequest('ChangedAssemblyStock', $data); 
		if ($xml === false) return $datas; 
		if ((empty($xml)) && (!($xml instanceof SimpleXMLElement))) return $datas; 
		if (!method_exists($xml, 'getNamespaces')) {
			return $datas; 
		}
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['soap']);
		$res = $soap->Body->children();
		//$r2 = $soap->Body; 
		//$p = $r2->xpath('/ResponseValue/ItemStockToken'); 
		foreach ($res->children() as $i) {
			$stockItems = $i->ResponseValue->children(); 
			foreach ($stockItems as $si) {
				$sku = (string)$si->ItemCode; 
			    $stock = (int)$si->Available; 
				
				$datas[$sku] = $stock; 
			}
		}
		$xml = null; 
		$cache = $datas; 
		return $datas; 
		
	}
	public static function getParams() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
		$OnepageTemplateHelper = new OnepageTemplateHelper(); 
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDER_DATA_TXT', true); 
		foreach ($tids as $t) {
			if ($t['file'] === 'sherpa.php') return $t; 
		}
		
		$tids = $OnepageTemplateHelper->getExportTemplates('ORDERS_TXT', true); 
		foreach ($tids as $t) {
			if ($t['file'] === 'sherpa.php') return $t; 
		}
		
		return array(); 
	}
	
	
	public static function checkCache($cmd, $data, &$hasCache=false) {
		if (!empty(self::$disable_cache)) {
			$hasCache = false; 
			return; 
		}
		switch ($cmd) {
			case 'ChangedStock':
			case 'ChangedAssemblyStock':
			  self::_getOPCMini(); 
			  $now = time(); 
			  $checkTime = $now - 3600; 
			  $res = OPCDbCache::getValue($cmd, '', $checkTime); 
			  if (empty($res)) return; 
			  else {
				  $hasCache = true; 
				  return $res; 
			  }
			break; 
		}
		$hasCache = false; 
		return; 
	}
	
	public static function storeCache($cmd, &$data) {
		
		
		
		self::_getOPCMini(); 
		$now = time(); 
		try {
		 OPCDbCache::storeAndClear($cmd, '', $now, $data); 
		}
		catch(Exception $e) {
			
		}
	}
	
	public static function getTableNameFromCmd($cmd, $runCmd=true) {
		$table = strtolower(JFile::makeSafe($cmd)); 
		$table = '#__sherpa_'.$table;
		if ($runCmd) 
		{
		self::_getOPCMini(); 
		if (!OPCMini::tableExists($table)) {
			if (method_exists('sherpaHelper', $cmd)) {
				//stAn, this will create tables and do a full sync for the first time usage:
				sherpaHelper::$cmd(); 
			}
		}
		}
		
		return $table; 
	}
	
	public static function tableStorage($cmd, &$new_data, $uniques=array(), $merge=true)
	{
		$db = JFactory::getDBO(); 
		$table = self::getTableNameFromCmd($cmd, false); 
		if (empty($new_data)) {
			if (!$merge) return array(); 
			if (OPCMini::tableExists($table)) {
			if (class_exists('cliHelper')) cliHelper::debug('SLOW: Getting full table '.$table); 
			$q = 'select * from `'.$table.'` where 1=1 order by `primaryid`'; 
			$db->setQuery($q); 
			$res = $db->loadObjectList(); 
			$ret_data = array(); 
			if (!empty($res)) {
			
			foreach ($res as $row) {
				$key = array(); 
				foreach ($uniques as $u) {
					$key[] = $row->{$u}; 
				}
				$key = implode('_', $key); 
				$ret_data[$key] = $row; 
			}
			$new_data = $ret_data; 
			}
			if (class_exists('cliHelper')) cliHelper::debug('SLOW: Got full table '.$table); 
			return $ret_data; 
			}
			else {
				return array(); 
			}
			
		}
		
		$first = reset($new_data); 
		$cols = array(); 
		$cols['primaryid'] = '`primaryid` int(1) UNSIGNED NOT NULL AUTO_INCREMENT'; 
		foreach ($first as $col=>$test) {
			if ((int)$test === $test) {
				$cols[$col] = '`'.$col.'` INT(1) NOT NULL'; 
			}
			else {
				$cols[$col] = '`'.$col.'` VARCHAR(255) COLLATE utf8mb4_unicode_ci'; 
			}
			
		}
		
		$q = 'CREATE TABLE IF NOT EXISTS `'.$table.'` ('; 
		$q .= ' '.implode(',', $cols).' '; 
		$indexes = array(); 
		
		$indexes[] = 'PRIMARY KEY (`primaryid`)'; 
		if (!empty($uniques)) {
			$uu = array(); 
			foreach ($uniques as $u) {
				$uu[] = '`'.$u.'`'; 
			}
			$indexes[] = ' UNIQUE KEY `uniquekey` ('.implode(',', $uu).')';
		}
		if (!empty($indexes)) {
		$q .= ', '.implode(',', $indexes).' '; 
		}
		$q .= ') ENGINE=InnoDB'; 
		
		if (class_exists('cliHelper')) cliHelper::debug($q); 
		
		$db->setQuery($q); 
		$db->execute(); 
		
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		//later - auto adjust table schema
		$def = static::getColumns($table); 
		foreach ($cols as $ctest=>$xx) {
			if ($ctest === 'primaryid') continue; 
			if (!isset($def[$ctest])) {
				//ALTER TABLE `zlto4_sherpa_changeditemsupplierswithdefaults` ADD `token` INT NOT NULL AFTER `prefered`;
				if (!empty($cols[$ctest])){
					$q = 'alter table `'.$table.'` ADD '.$cols[$ctest]; 
					$lastCol = end($def); 
					if (!empty($lastCold)) {
						$q .= ' after `'.end($def).'`';
					}
					
				}
				if (class_exists('cliHelper')) cliHelper::debug($q); 
				$db->setQuery($q); 
				$db->execute(); 
			}
		}
		
		if (!empty($new_data)) {
		require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'utils.php'); 
		$JModelUtils = new JModelUtils; 
		$retMsg = $JModelUtils->mergeDataIntoTable($table, $new_data, $uniques); 
		
		switch ($cmd) {
			case 'ChangedItemSuppliersWithDefaults':
			$mx = array();

			$toMerge = array(); 
			foreach ($new_data as $obj) {
				if (!empty($obj->product_sku)) {
					$mx[] = "'".$db->escape($obj->product_sku)."'"; 
					/*
					$sku = $obj->product_sku; 
					$q = 'select `virtuemart_product_id`, `product_sku`, `product_mpn` from #__virtuemart_products where `product_sku` = \''.$db->escape($sku).'\' or `product_mpn` = \''.$db->escape($sku).'\''; 
					$db->setQuery($q); 
					$ps = $db->loadObjectList(); 
					if (!empty($ps)) {
						foreach ($ps as $r) {
							$objx = new stdClass(); 
							$objx->virtuemart_product_id = (int)$r->virtuemart_product_id; 
							$objx->product_available_date = $obj->product_available_date; 
							$objx->product_in_stock = $obj->product_in_stock; 
							$toMerge[$r->virtuemart_product_id] = $objx; 
						}
					}
					*/
					
				}
			}
			if (!empty($mx)) {
				self::createSupplierTable(); 
				$q = 'START TRANSACTION'; 
			$db->setQuery($q); 
			$db->execute(); 
				
			  $q = 'update #__virtuemart_supplierstock as s, '.$table.' as sherpa set s.supplier_stock = sherpa.supplier_stock where s.product_sku = sherpa.product_sku'; 
			  $q .= ' and s.product_sku IN ('.implode(',', $mx).')'; 
			  if (class_exists('cliHelper')) cliHelper::debug('Updating '.count($mx).' items in #__virtuemart_supplierstock'); 
			  $db->setQuery($q); 
			  $db->execute(); 
			  if (class_exists('cliHelper')) cliHelper::debug('SLOW: '.$q); 
			  
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			}
			  break; 
			case 'ChangedStock':
			case 'ChangedAssemblyStock':
			$q = 'START TRANSACTION'; 
			$db->setQuery($q); 
			$db->execute(); 
		
			$q = 'SET @STOCK_TRIG_DISABLED = true'; 
			$db->setQuery($q); 
			$db->execute(); 
			
			$mx = array(); 
			foreach ($new_data as $row) {
				if (!empty($row->product_sku)) {
					$mx[] = "'".$db->escape($row->product_sku)."'"; 
				}
			}
			 
			 //$q = 'update #__virtuemart_products as s, '.$table.' as sherpa set s.product_in_stock = sherpa.product_in_stock where s.product_sku = sherpa.product_sku or s.product_mpn = sherpa.product_sku'; 
			 if (!empty($mx)) {
			 if (class_exists('cliHelper')) cliHelper::debug('Updating '.count($mx).' items in #__virtuemart_products'); 
			 $q = 'update #__virtuemart_products as s, '.$table.' as sherpa set s.product_in_stock = sherpa.product_in_stock where '; 
			 $q .= ' s.product_mpn IN ('.implode(',', $mx).')'; 
			 $q .= ' and s.product_mpn = sherpa.product_sku '; 
			 
			 $db->setQuery($q); 
			 $db->execute(); 
			 if (class_exists('cliHelper')) cliHelper::debug('SLOW: '.$q); 
			 }
			 
			  $q = 'SET @STOCK_TRIG_DISABLED = NULL'; 
			   $db->setQuery($q); 
			   $db->query(); 
			   
			   $q = 'COMMIT'; 
			   $db->setQuery($q); 
			   $db->execute(); 
			 
			 
			 break; 
			  
		}
		}
		
		if ($merge) {
		if (class_exists('cliHelper')) cliHelper::debug('Merging '.count($new_data).' rows into '.$table); 
		if (class_exists('cliHelper')) cliHelper::debug('SLOW: Getting full table'.$table); 
		$q = 'select * from `'.$table.'` where 1=1 order by `primaryid`'; 
		$db->setQuery($q); 
		$res = $db->loadObjectList(); 
		if (!empty($res)) {
		$ret_data = array(); 
		foreach ($res as $row) {
			$key = array(); 
			foreach ($uniques as $u) {
				$key[] = $row->{$u}; 
			}
			$key = implode('_', $key); 
			$ret_data[$key] = $row; 
		}
		 $new_data = $ret_data; 
		 if (class_exists('cliHelper')) cliHelper::debug('SLOW: Got full table '.$table); 
		 return $ret_data; 
		}
		}
		else {
			
		}
		
		
		
		return $new_data; 
		
	}
	
	public static function getColumns($table) {
   if (!OPCmini::tableExists($table)) {
	   
	   return array(); 
   }
   
   if (empty(self::$cache)) self::$cache = array(); 
   
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
   $table = $db->getPrefix().$table; 
	 
   if (isset(self::$cache['columns_'.$table])) return self::$cache['columns_'.$table]; 
   // here we load a first row of a table to get columns
   
   
   
   $q = 'SHOW COLUMNS FROM '.$table; 
   $db->setQuery($q); 
   $res = $db->loadAssocList(); 
  
  
  
   $new = array(); 
   if (!empty($res)) {
    foreach ($res as $k=>$v)
	{
		
		$new[$v['Field']] = $v['Field']; 
	}
	self::$cache['columns_'.$table] = $new; 
	
	
	
	return $new; 
   }
   static::$cache['columns_'.$table] = array(); 
   return array(); 
   
   
 }
	
	public static function sendRequest($cmd, $data=array()) {

		$params =  self::getParams(); 
		if (empty($params)) return false; 
		$securityCode = $params['config']->secret_key;
		if (empty($securityCode)) {
			return false; 
		}
	
		$hasCache = false; 
		if (empty(sherpaHelper::$disable_cache)) {
		$xmlResp = self::checkCache($cmd, $data, $hasCache); 
		}
		
		
		
		if ($hasCache) {
			
			if (!empty($xmlResp)) {
				$xml = self::processXML($xmlResp); 
				
				return $xml; 
			}
		
			
		}
		
		
		
		$params =  self::getParams(); 
		if (empty($params)) return; 
		
		$securityCode = $params['config']->secret_key;
		
		$data['securityCode'] = $securityCode; 
		$data['counter'] = self::getNextCounter($cmd); 
		
		
		
		$data['maxResult'] = 99999; 
		$url = $params['config']->soap_url;
		$data['soap_url'] = $url; 
		
		
		$xmlResp = xmlHelper::get($cmd, $data, $errMsg); 
		
		
		
		$xml = false; 
		if ($xmlResp !== false) {
			$xml = self::processXML($xmlResp); 
		}
		
		
		
		if (!($xml instanceof SimpleXMLElement)) {
			
			return false; 
		}
		if (empty(sherpaHelper::$disable_cache)) 
		{
		self::storeCache($cmd, $xmlResp); 
		}
		
		if (!empty($errMsg)) return false; 
		return $xml; 
	}
	
	
	
}

class xmlHelper {
	public $xmlFile = '';
	public $soapFunction = ''; 
	public $soap_url = ''; 
	public $host = ''; 
	public function __construct($soapFunction, $url) {
		
		if (empty($soapFunction)) {
			throw new Exception('no soap action provided'); 
		}
		$pa = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.$soapFunction.'.xml.php';
		
		if (!file_exists($pa)) {
			throw new Exception($soapFunction.' definition file not found'); 
		}
		$this->soapFunction = $soapFunction; 
		$this->xmlFile = $pa; 
		
		$this->soap_url = $url; 
		
		$prefix = '://';
		$x1 = stripos($url, $prefix); 
		$x2 = stripos($url, '/', $x1+strlen($prefix)); 
		
		$host = substr($url, $x1+strlen($prefix), $x2-$x1-strlen($prefix)); 
		$this->host = $host; 
	}
	public static function &get($soapFunction, $vars, &$errMsg='', &$xmlTxt='', &$xmlDebugTxt='') {
	   
	   if (!is_string($soapFunction)) $soapFunction = (string)$soapFunction; 
	   
	   $xmlData = new xmlHelper($soapFunction, $vars['soap_url']); 
	   
	   foreach ($vars as $k=>$v) {
		   $xmlData->$k = $v; 
	   }
	   
	   $debug_xml = ''; 
	   $xmlRendered = $xmlData->render($debug_xml); 
	   
	   
	   
	   $xmlTxt = $xmlRendered; 
	   $xmlDebugTxt = $debug_xml; 
	   
	   if (!empty($vars['debug'])) {
		   if (!empty($debug_xml)) {
			   //echo $debug_xml; 
		   }
		   else {
		     //echo $xmlRendered; 
		   }
	   }
	   
	   $xml = $xmlData->sendXML($xmlRendered, $errMsg, $debug_xml); 
  
	   return $xml; 
	}
	
	public function render(&$debug_xml='') {
		if (!file_exists($this->xmlFile)) {
			self::emailError($this->xmlFile.': '.$this->soapFunction.' definition file not found'); 
			throw new Exception($this->xmlFile.': '.$this->soapFunction.' definition file not found'); 
		}
		
		
		
		ob_start(); 
		require($this->xmlFile);
		if (isset($debug_body)) $debug_xml = $debug_body; 
		return ob_get_clean(); 
		
		
		
		
	}
	
	
	
	
	public function sendXML(&$xml_content, &$retMsg='', $debug_xml='') {
	
	
		sherpaHelper::_getOPCMini(); 
		if (empty($this->soapFunction)) {
			throw new Exception('Function not defined'); 
		}
		
		if (empty($debug_xml)) $debug_xml = $xml_content; 
		
		$server = $this->soap_url; 
		$soap_action = 'http://sherpa.sherpaan.nl/'.$this->soapFunction; 
		$host = $this->host; 
		
		$wsld_url = $server; 
		$xml_post_string = trim($xml_content); 
		
		if (class_exists('cliHelper')) cliHelper::debug(' calling '.$this->soapFunction); 
		/*
		$x = debug_backtrace(); 
		foreach ($x as $l) {
			if (class_exists('cliHelper')) cliHelper::debug(@$l['file'].' '.@$l['line']); 
		}
		*/
	if ($this->soapFunction === 'GetResponse') {
		//echo $xml_content; die(); 
	}
		
		//hardwritten per WSDL: 
		
		
		
           $headers = array(
						//"Accept-Encoding: gzip,deflate",
                        //"Content-type: application/soap+xml; charset=\"utf-8\"",
						//"Content-type: application/soap+xml",
						"Content-type: text/xml; charset=\"utf-8\"",
                        //"Accept: text/xml",
                        //"Cache-Control: no-cache",
                        //"Pragma: no-cache",
						//"Host: test-editx.mikromarc.no",
						//"Connection: Keep-Alive",
                        "SOAPAction: \"".$soap_action."\"", 
                        "Content-length: ".strlen($xml_post_string),
						"Host: ".$host,
						"User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
                    ); //SOAPAction: your op URL

           
			
			

            
            $ch = curl_init();
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, $server);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
	        curl_setopt($ch, CURLOPT_HEADER, 1);
			
			
		
            // converting
            $response = curl_exec($ch); 
			
			
            $e = curl_error ($ch); 
			if (!empty($e)) {
				if (class_exists('cliHelper')) cliHelper::debug('ERROR: CURL returned: '.$e); 
			}
			
			
			
			
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$origbody = $body = substr($response, $header_size);
			
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			curl_close($ch);
			
			//$origbody = str_replace('soap:', '', $origbody); 
			
			libxml_use_internal_errors(true);
			
			$xml = simplexml_load_string($origbody);
			
			/*
			foreach (libxml_get_errors() as $error) {
				// handle errors here
				
			}
			*/
			
			
			
			
			if ($xml !== false) {
			//$xml->registerXPathNamespace("soap", "http://schemas.xmlsoap.org/soap/envelope/");
			$xml->registerXPathNamespace("soap", "http://www.w3.org/2003/05/soap-envelope");
			}
			if ($xml === false) {
				if (class_exists('cliHelper')) cliHelper::debug('ERROR: Communication error detected in last request - '.$this->soapFunction); 
				$retMsg = 'Sherpa is not available or an error occured'."\nResponse: \n".$response."\n\nDebug request:\n".$debug_xml."\n\nReal Request:\n".$xml_content; 
				sherpaHelper::emailError('Sherpa communication error: '.$httpcode."\n".$retMsg."\nRequest:\n".$debug_xml."\n Response:\n".$response); 
				return false; 
			}
			
			
			
			
			
			
			
			$f = $xml->xpath('//Body/Fault/faultstring');
		    if ((!empty($f)) && (is_array($f))) {
			  $retMsg = (string)$f[0];
			}
			/*
			$f2 = $xml->xpath('//s:Body/OrderDropResponse/Warnings');
			if (!empty($f2) && (is_array($fw))) {
				$retMsg = ''; 
				foreach ($f2 as $warn) {
					$wn = (string)$warn;
					if (!empty($wn)) {
					 $retMsg .= (string)$wn;
					 $retMsg .= "\n"; 
					}
				}
				if (!empty($retMsg)) {
				
				}
				
			}
			*/
			
			
			if ($xml === false) {
				$retMsg = 'Error Parsing ResponseXML'; 
				if (class_exists('cliHelper')) cliHelper::debug('ERROR: Communication error detected in last request - '.$this->soapFunction); 
			}
			
			
			
		
            
			
			if ($httpcode === 200) {
				if (empty($retMsg)) $retMsg = ''; 
				
				
	
				
				return $origbody; 
			}
			else {
			
			
				
			if ($xml instanceof SimpleXMLElement) {
				 $ns = $xml->getNamespaces(true);
				 $soap = $xml->children($ns['soap']);
				 if (isset($soap->Body->Fault->Reason->Text)) {
				 $z = (string)$soap->Body->Fault->Reason->Text;
				 $retMsg = $z; 
				 
				}				
				
			}
			if (class_exists('cliHelper')) cliHelper::debug('ERROR: '.$retMsg.' - '.$this->soapFunction); 
			sherpaHelper::emailError('Sherpa communication error: '.$httpcode."\n".$retMsg."\nRequest:\n".$debug_xml."\n Response:\n".$response); 
			
			
			return false; 
			}
			
		
			
			
	}
	
	
	
	
}