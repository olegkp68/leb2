<?php
/**
 * @copyright	Copyright (C) RuposTel.com
 */

// no direct access
defined('_JEXEC') or die;


class modCartsaveHelper
{
	public static function getAjax() {
		
		$my_action = JRequest::getVar('myaction', ''); 
		if ($my_action === 'upload') {
			self::uploadXLS(); 
		}
		if ($my_action === 'save') {
			
			
			if (self::hasItems()) {
				self::loadVM(); 
				$cart = VirtuemartCart::getCart(); 
				$data = $cart->cartProductsData; 
				
				
				if (empty($data)) return self::returnHandler();
				$data = json_encode($data); 
				$user_id = JFactory::getUser()->get('id', 0); 
				$cart_name = JRequest::getVar('cart_name', ''); 
				
				
				if (empty($cart_name)) {
					$user_id = JFactory::getUser()->get('id'); 
					if (!empty($user_id)) {
						$date = new DateTime();
						$created_on = strftime("%e-%b-%Y_%H:%M", $date->getTimestamp());
						$cart_name = $created_on.'-'.substr(uniqid(), 0, 4); 
					}
					else {
						return self::returnHandler(); 
					}
				}
				if (empty($data)) return self::returnHandler(); 
				
				self::store($cart_name, $data, $user_id); 
				
			}
		}
	
		if ($my_action === 'download') {
			
			self::downloadExcell(); 
		}
		
		if ($my_action === 'load') {
			
			
			
			
			$cart_name = JRequest::getVar('cart_name', ''); 
			
			
			$user_id = JFactory::getUser()->get('id', 0); 
			$merge = JRequest::getVar('merge', false); 
			$n = self::loadCart($cart_name, $user_id, $merge); 
			$txt = JText::_('MOD_CARTSAVE_LOADEDN'); 
			$txt = str_replace('{n}', $n, $txt); 
			return self::returnHandler($txt);
		}
		if ($my_action === 'loadid') {
			self::loadid(); 
			
		}
		
		if ($my_action === 'dropid') {
			self::dropid(); 
			
		}
		
		return self::returnHandler();
		
	}
	private static function uploadXLS() {
		$file = JFactory::getApplication()->input->files->get('cart_upload_file');
		jimport('joomla.filesystem.file');
		$filename = JFile::makeSafe($file['name']);
		$errortxt = JText::_('MOD_CARTSAVE_ERRORUPLOADING'); 
		if (empty($filename)) {
			return self::returnHandler($error);
		}
	// Set up the source and destination of the file
	$src = $file['tmp_name'];
	$dest = dirname($src). DIRECTORY_SEPARATOR . $filename;

	// First verify that the file has the right extension. We need jpg only.
	$ext = strtolower(JFile::getExt($filename)); 
	$allowed = array('xls', 'xlsx'); 
	if (in_array($ext, $allowed)) 
	{
	// TODO: Add security checks.

   if (JFile::upload($src, $dest))
   {
      // Redirect to a page of your choice.
	  $cart_id = JRequest::getInt('cart_name_id', 0); 
	  try {
		$returnrows = self::readXLS($dest, $cart_id); 
	  }
	  catch(Exception $e) {
		  if (file_exists($src)) {
		  JFile::delete($src); 
		}
		if (file_exists($dest)) {
		  JFile::delete($dest); 
		}
		return self::returnHandler($error);
	  }
	  // Redirect and throw an error message.
	  if (file_exists($src)) {
		  JFile::delete($src); 
	  }
	  if (file_exists($dest)) {
		  JFile::delete($dest); 
	  }
	  
	  static $sku_col; 
	  static $quantity_col; 
	  foreach ($returnrows as $row) {
		  if (empty($sku_col)) {
		  $sku = JText::_('MOD_CARTSAVE_XLS_SKU'); 
		  if (isset($row[$sku])) {
			  $sku_col = $sku; 
		  }
		  else {
			  if (isset($row['SKU'])) {
				  $sku_col = 'SKU'; 
			  }
			  else
			  if (isset($row['sku'])) {
				  $sku_col = 'sku'; 
			  }
			  foreach ($row as $ind => $val) {
				  if (!empty($val)) {
					  $db = JFactory::getDBO(); 
					  $q = 'select `virtuemart_product_id` from #__virtuemart_products where `product_sku` = \''.$db->escape($val).'\''; 
					  $db->setQuery($q); 
					  $res = $db->loadAssocList(); 
					  if (count($res) !== 1) continue; 
					  $sku_col = $ind; 
					  break; 
					  
				  }
			  }
		  }
		  }
		  if (empty($quantity_col)) {
		  $quantitytxt = JText::_('MOD_CARTSAVE_XLS_QUANTITY'); 
		  if (isset($row[$quantitytxt])) {
			  $quantity_col = $quantitytxt; 
		  }
		  else {
			  if (isset($row['QUANTITY'])) {
				  $quantity_col = 'QUANTITY'; 
			  }
			  else
			  if (isset($row['quantity'])) {
				  $quantity_col = 'quantity'; 
			  }
			  foreach ($row as $ind => $val) {
				  if (!empty($val)) {
					  if (preg_match('/^[0-9]+$/', $val)) {
							if (is_numeric($val)) {
								$quantity_col = $ind; 
								break; 
							}
					  }
					  
					  
				  }
			  }
		  }
		  }
		  
		  if ((empty($sku_col)) || (empty($quantity_col))) {
			  return self::returnHandler($error);
		  }
		  
		 
		  $cart_row = array(); 
		  
		  $db = JFactory::getDBO(); 
		  $sku = $row[$sku_col]; 
		  $q = 'select `virtuemart_product_id` from #__virtuemart_products where `product_sku` = \''.$db->escape($sku).'\' limit 1'; 


		  $db->setQuery($q); 
		  $virtuemart_product_id = (int)$db->loadResult(); 
		  if (!empty($virtuemart_product_id)) {
			$cart_row['virtuemart_product_id'] = (int)$virtuemart_product_id; 
			$cart_row['quantity'] = (int)$row[$quantity_col]; 
			$cart_row['customProductData'] = array(); 
			$cartProductsData[] = $cart_row; 
		  }
		  
		  
		  
	  }
	  
	  $merge = JRequest::getVar('merge', false); 
	  
	  
	  
	  if (empty($cart_id)) {
		$cart = VirtuemartCart::getCart(); 
		if ((!empty($cart->cartProductsData)) && (!empty($merge))) {
			$txt = JText::_('MOD_CARTSAVE_XLS_LOADED'); 
		}
		else {
			$txt = JText::_('MOD_CARTSAVE_XLS_REPLACED'); 
		}
		
		self::loadCartData($cartProductsData, $merge); 
		return self::returnHandler($txt);
	  }
	  else {
		  $user_id = JFactory::getUser()->get('id', 0); 
		  $cart_name = JRequest::getVar('cart_name', ''); 
		  
		  $cart_name_by_id = self::getValidateCartName($cart_id, $user_id, $cart_name); 
		  
		  $cartProductsData = self::getCart($cart_name_by_id, $user_id); 
		  
		  if (!empty($cart_name_by_id)) {
			self::store($cart_name_by_id, json_encode($cartProductsData), $user_id); 
			$txt = JText::_('MOD_CARTSAVE_XLS_SAVED'); 
			$txt = str_replace('{cart_name}', $cart_name_by_id, $txt); 
			return self::returnHandler($txt);
		  }
		  else {
			  return self::returnHandler($error);
		  }
	  }
	 
	  
	  return self::returnHandler();
   } 
   else
   {
      // Redirect and throw an error message.
	  if (file_exists($src)) {
		  JFile::delete($src); 
	  }
	  if (file_exists($dest)) {
		  JFile::delete($dest); 
	  }
	  return self::returnHandler($error);
   }
}
else
{
   // Redirect and notify user file does not have right extension.
   if (file_exists($src)) {
		  JFile::delete($src); 
	  }
	  if (file_exists($dest)) {
		  JFile::delete($dest); 
	  }
	  return self::returnHandler($error);
}
	return self::returnHandler($error);
	}
	private static function downloadExcell() {
			$locale = JText::_('MOD_CARTSAVE_TIMELOCALE'); 
			if ($locale === 'MOD_CARTSAVE_TIMELOCALE') $locale = ''; 
			
			if (!empty($locale)) { setlocale(LC_TIME, $locale); }
			self::loadVM(); 
			if (!empty($locale)) { setlocale(LC_TIME, $locale); }
			
			$date = new DateTime();
			$created_on = strftime("%e.%b %Y (%A) - %H:%M", $date->getTimestamp());
			
			$cart_name = JRequest::getVar('cart_name', ''); 
			$user_id = JFactory::getUser()->get('id', 0); 
			$cartProductsData = self::getCart($cart_name, $user_id); 
			$cart = new stdClass(); 
			$cart->cartProductsData = $cartProductsData; 
			
			$product_model = VmModel::getModel ('product');
			
			$cart->products = array(); 
			
			foreach ($cart->cartProductsData as $ind=>$p )
			{
				$p = (array)$p; 
				$virtuemart_product_id = (int)$p['virtuemart_product_id']; 
				$quantity = (int)$p['quantity']; 
				$product = $product_model->getProduct($virtuemart_product_id, true, true, true, $quantity); 
				$product->quantity = $quantity; 
				$cart->products[$ind] = $product; 
			}
			$dispatcher = JDispatcher::getInstance(); 
			$results = $dispatcher->trigger('plgAdjustCartPrices', array( &$cart, true, true)); 
			$curcols = array('unitprice', 'subtotal'); 
			$xls = array(); 
			foreach ($cart->products as $p) {
				
				$row = array(); 
				$row['sku'] = $p->product_sku; 
				$row['name'] = $p->product_name; 
				$row['unitprice'] = number_format($p->prices['product_price'], 4, '.', '');; 
				$row['quantity'] = $p->quantity; 
				$row['subtotal'] = number_format($p->prices['product_price'] * $p->quantity, 4, '.', ''); 
				$xls[] = $row; 
			}
			
			
			self::_getPHPExcel();  
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();
			// Set properties
			$objPHPExcel->getProperties()->setCreator("")
							 ->setLastModifiedBy("")
							 ->setTitle("Listing")
							 ->setSubject("")
							 ->setDescription("")
							 ->setKeywords("")
							 ->setCategory("");
			
			$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
			$header_done = false; 
			$firstrow = reset($xls); 
			foreach ($firstrow as $key=>$val) {
				
				$kt = JText::_('MOD_CARTSAVE_XLS_'.strtoupper($key)); 
				if ($kt === 'MOD_CARTSAVE_XLS_'.strtoupper($key)) {
					$keytext = $key;
				}
				else {
					$keytext = $kt; 
				}
				
				$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, 1, $keytext);
			 $i++; 
			}
			
			$keytext = JText::_('MOD_CARTSAVE_XLS_SAVEDON').' '.$created_on; 
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, 1, $keytext);
			
			foreach ($xls as $ind=>$row) {
				$i = 0; 
				foreach ($row as $key=>$val) {
				$rown_n = $ind+2; 
				
				
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, $rown_n, $val);
			 
			 if (in_array($key, $curcols)) {
				 $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, $rown_n)->getNumberFormat()->setFormatCode('#,####0.0000_-'); 
			 }
			 
			 $i++; 
			}
			}
			
			$objPHPExcel->getActiveSheet()->setTitle('Listing');
			$objPHPExcel->setActiveSheetIndex(0);
			
			$sheet = $objPHPExcel->getActiveSheet();
			$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(true);
    
			foreach ($cellIterator as $cell) {
					$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			}
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output'); 
			unset($objWriter); 
			$objWriter = null; 

			@header('Content-Type: application/vnd.ms-excel');
			
			$prefix = self::getParams()->get('fileprefix', 'listing'); 
			
			@header('Content-Disposition: attachment;filename="'.$prefix.$cart_name.'-'.date('d-m-Y_Hi').'.xlsx"');
			@header('Cache-Control: max-age=0');
			flush(); 
			JFactory::getApplication()->close(); 
			
			debug_zval_dump($xls); die(); 
			var_dump($cartProductsData); 
			die(); 
	}
	
	private function _die($msg) {
		echo $msg; 
		JFactory::getApplication()->close(); 
	}
	
	public static function remove_accent($str) 
{ 
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'); 
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
  return str_replace($a, $b, $str); 
} 
	private static function getParams() {
		static $params; 
		if (!empty($params)) return $params; 
		$module_id = (int)JRequest::getInt('module_id', 0); 
		
		
		if (!empty($module_id)) {
			$db = JFactory::getDBO(); 
			$q = 'select `params` from `#__modules` where `id` = '.(int)$module_id; 
			$db->setQuery($q); 
			$params_txt = $db->loadResult(); 
			
			
			if (!empty($params_txt)) {
				$params = new JRegistry($params_txt); 
				return $params; 
			}
		}
		else
			{
			$db = JFactory::getDBO(); 
			$q = 'select `params` from `#__modules` where `module` = \'mod_cartsave\' and `published` = 1'; 
			$db->setQuery($q); 
			$params_txt = $db->loadResult(); 
			
			
			if (!empty($params_txt)) {
				$params = new JRegistry($params_txt); 
				return $params; 
			}
		}
		return new JRegistry(''); 
	}
	
	private static function getValidateCartName($my_id, $user_id, $cart_name='') {
			$db = JFactory::getDBO(); 
		
			if (empty($user_id)) return false; //only for logged in users
			if (!empty($my_id)) {
				$q = 'select `hash` from #__mod_cartsave where `id` = '.(int)$my_id;
				$b2bshared = self::getParams()->get('b2bshared', 0); 
				if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
				$db->setQuery($q); 
				$cart_name_loaded = $db->loadResult(); 
				if (empty($cart_name_loaded)) return false; 
				
				if (!empty($b2bshared)) {
					return $cart_name_loaded; 
				}
				
				if ($cart_name !== $cart_name_loaded) {
					return false; 
				}
				if ($cart_name === $cart_name_loaded) {
					return $cart_name_loaded;
				}
				
			}
			return false; 
	}
	
	private static function loadid() {
			$db = JFactory::getDBO(); 
			$my_id = JRequest::getInt('cart_name_id', 0); 
			$user_id = JFactory::getUser()->get('id', 0); 
			$merge = JRequest::getVar('merge', false); 
			
			
			
			if (empty($user_id)) return; //only for logged in users
			if (!empty($my_id)) {
				$q = 'select `hash` from #__mod_cartsave where `id` = '.(int)$my_id;
				$b2bshared = self::getParams()->get('b2bshared', 0); 
				if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
				$db->setQuery($q); 
				$cart_name = $db->loadResult(); 
				
				
				
				if (!empty($cart_name)) {
					$n = self::loadCart($cart_name, $user_id, $merge); 
				
				
					
					
					$txt = JText::_('MOD_CARTSAVE_LOADEDN'); 
					$txt = str_replace('{n}', $n, $txt); 
					return self::returnHandler($txt); 
				}
			}
	}
	
	private static function dropid() {
		
		
			$db = JFactory::getDBO(); 
			$my_id = JRequest::getInt('cart_name_id', 0); 
			$user_id = JFactory::getUser()->get('id', 0); 
			$merge = JRequest::getVar('merge', false); 
			
			if (empty($user_id)) return; //only for logged in users
			if (!empty($my_id)) {
				$q = 'select `hash` from #__mod_cartsave where `id` = '.(int)$my_id; 
				
				$b2bshared = self::getParams()->get('b2bshared', 0); 
				if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
				
				
				$db->setQuery($q); 
				$cart_name = $db->loadResult(); 
				
				
				
				if ((!empty($cart_name)) || ($cart_name === '')) {
					$q = 'delete from #__mod_cartsave where `id` = '.$my_id; 
					$db->setQuery($q); 
					$db->execute(); 
					return self::returnHandler(); 
				}
			}
	}
	
	private static function getRealCart() {
		self::loadVM(); 
		$cart = VirtuemartCart::getCart(); 
		return $cart->cartProductsData; 
		
	}
	private static function getCart($cart_name, $user_id, $cart_id=0) {
		
		if (empty($cart_name) && (empty($cart_id)))
		{
			return self::getRealCart(); 
		}
		
		self::checkCreateTable(); 
		$res = ''; 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'addtocartaslink.php'); 
		$db = JFactory::getDBO(); 
		$q = "select `cart` from #__mod_cartsave where "; 
		if (!empty($cart_id)) {
			$q .= ' `id` = '.(int)$cart_id; 
		}
		else {
		$q .= " `hash` = '".$db->escape($cart_name)."' "; 
		}
		if (!empty($user_id)) {
				$b2bshared = self::getParams()->get('b2bshared', 0); 
				if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
		}
		$q .= " limit 1";
		$db->setQuery($q); 
		$res = $db->loadResult(); 
		
		if (empty($res)) { 
		
		$params = self::getParams(); 
		
		if ($params->get('allowany', 0)) {
			//check organization carts:
			$q = "select `cart` from #__mod_cartsave where `hash` = '".$db->escape($cart_name)."' "; 
		
			$b2bshared = self::getParams()->get('b2bshared', 0); 
			if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
				$q .= " limit 1";
				$db->setQuery($q); 
				$res = $db->loadResult(); 
				
			if (empty($res)) {
			
			//get last cart by name of any user:
			$q = "select `cart` from `#__mod_cartsave` where `hash` = '".$db->escape($cart_name)."' order by `id` desc limit 1"; 
			
			$db->setQuery($q); 
			$res = $db->loadResult(); 
			}
			
		}
		}
		
		
		
		if (empty($res)) return array(); 
		
		
		$cartProductsData = json_decode($res, true); 
		return $cartProductsData; 
	}
	
	
	private static function loadCart($cart_name, $user_id, $merge=false) {
		$cartProductsData = self::getCart($cart_name, $user_id); 
		return self::loadCartData($cartProductsData, $merge); 
	}
	//check own cart by name 
	//then check organization cart by name (sharedb2b)
	//then check anybodys cart by name (allowany)
	private static function loadCartData($cartProductsData, $merge=false) {
		
		
		
		if (empty($cartProductsData)) {
			self::returnHandler(JText::_('MOD_CARTSAVE_NOTFOUND')); 
			return; 
		}
		
		
		
		
		
		
		
		
		if (empty($cartProductsData)) return self::returnHandler(JText::_('MOD_CARTSAVE_NOTFOUND')); 
		
		self::loadVM(); 
		$cart = VirtuemartCart::getCart(); 
		
		
		$ign = array('virtuemart_product_id', 'quantity'); 
		
		
		if (empty($merge)) {
			
			
			$cart->cartProductsData = array();
			$cart->products = array();
			
			
			
		}
		else {
			/*
			foreach ($cartProductsData as $ind => $v) {
				$cart->cartProductsData[] = $v; 
			}
			
			$cartX = OPCmini::getCart(); 
			*/
			
			
		}
			$obj = new stdClass(); 
			$obj->cart =& $cart; 
			
			foreach ($cartProductsData as $ind=>$p) {
				$add_id = array(); 
				$qadd = array(); 
				$other = array(); 
			
			
				$add_id[$ind] = $p['virtuemart_product_id']; 
				$qadd[$ind] = $p['quantity']; 
				foreach($p as $key=>$val) {
					if (in_array($key, $ign)) continue; 
					$other[$ind][$key] = $val; 
				}
				/*
				link_type: 
				0 -> feature disabled
				1 -> deletect cart and set link products
				2 -> do not increment quantity and do not delete cart
				3 -> increment quantity and do not delete cart
				*/
				require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
				require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'addtocartaslink.php'); 
				OPCAddToCartAsLink::addtocartaslink($obj,$add_id, $qadd, $other, false, 3); 
				
			}

			
			 
			//$cart->cartProductsData = $cartProductsData; 
			$cartX = OPCmini::getCart(); 

		
		return count($cartProductsData); 
	}
	public static function getAuthorizedUsers($user_id) {
		$users = array(); 
		$users[$user_id] = $user_id; 
		$b2bshared = self::getParams()->get('b2bshared', 0); 
		if (empty($b2bshared)) return $users; 
		
		$sgs = array(); 
		$pairbyemail = true; 
		$ignoreusers = array(); 
		$testusers = array(); 
		$ownsgs = self::getParams()->get('ownsgs', 0); 
		if (!empty($ownsgs)) {
			$sgs = self::getCurrentSG(false); 
			$users = self::getUsersInSGS($sgs, $pairbyemail, $testusers, $ownsgs); 
		}
		
		
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mini.php'); 
		if (OPCmini::tableExists('usertabs')) {
			
			if (!empty($users)) {
				$users[$user_id] = $user_id; 
			}
			else {
				$users = array(); 
				$users[$user_id] = $user_id; 
			}
			
			$db = JFactory::getDBO(); 
			$q = 'select t2.`virtuemart_user_id` from #__usertabs as t1 ';
			$q .= ' inner join #__usertabs as t2 on t1.`authorized_user_id` = t2.`authorized_user_id` ';
			$q .= ' where t1.`virtuemart_user_id` IN ('.implode(',', $users).')'; 
			$db->setQuery($q); 
			$res = $db->loadAssocList(); 
			
			
			
			if (!empty($res)) {
				foreach ($res as $row) {
					$user_id = (int)$row['virtuemart_user_id']; 
					if (empty($user_id)) continue; 
					$users[$user_id] = $user_id; 
				}
			}
			return $users; 
		}
		return $users; 
	}
	public static function getCurrentSG($all=true) {
		
		$db = JFactory::getDBO(); 
		$user = JFactory::getUser(); 
		$user_id = $user->get('id'); 
		if (empty($user_id)) {
		  if ($all) { 
		  return array('1'); 
		  }
		  return array(); 
		}
		$ownsgs = self::getParams()->get('ownsgs', 0); 
		$qx = 'select `virtuemart_shoppergroup_id` from `#__virtuemart_vmuser_shoppergroups` where `virtuemart_user_id` = '.(int)$user_id; 
		$db->setQuery($qx); 
		$res = $db->loadAssocList(); 
		$ret = array(); 
		foreach ($res as $row) {
			$sgid = (int)$row['virtuemart_shoppergroup_id']; 
			if ($all) {
				$ret[$sgid] = $sgid; 
			}
			else {
				if (!empty($ownsgs) && ($sgid > $ownsgs)) {
					$ret[$sgid] = $sgid; 
				}
			}
		}
		if (empty($ret)) {
			if ($all) {
				$ret[2] = 2; 
				return $ret; 
			}
			else {
				return array(); 
			}
		}
		return $ret; 
		
		
	}
	public static function getUsersInSGS($sgs, &$incm=false, &$testusers=array(), $ownsgs=11) {
		
		$manager = (int)self::getParams()->get('manager', 0); 
		$testuser = (int)self::getParams()->get('testuser', 0); 
		
		$db = JFactory::getDBO(); 
		foreach ($sgs as $k => $sg) {
			if ($sg <= $ownsgs) unset($sgs[$k]); 
		}
		if (empty($sgs)) return array(); 
		$qx = 'select s.`virtuemart_user_id` '; 
		if ($incm) {
			$qx .= ', u.`email` '; 
		}
		$qx .= ' from `#__virtuemart_vmuser_shoppergroups` as s '; 
		if ($incm) {
			$qx .= ' left join #__users as u on ((s.`virtuemart_user_id` > 0) and (u.`id` = s.`virtuemart_user_id`)) '; 
			
		}
		
		$qx .= ' where s.`virtuemart_shoppergroup_id` IN ('.implode(',', $sgs).')'; 
		try {
			
			$db->setQuery($qx); 
			$res = $db->loadAssocList(); 
		}
		catch (JDatabaseExceptionExecuting $e) {  
			
		}
		$users = array(); 
		$emails = array(); 
		$or = array(); 
		foreach ($res as $row) {
			$user_id = (int)$row['virtuemart_user_id']; 
			$users[$user_id] = $user_id; 
			if (!empty($row['email'])) {
				$emails[$row['email']] = $row['email']; 
		
				$or[] = '(u.`email` like \''.$db->escape($row['email']).'\')';
		
			}
		}
        
        /*
        $db = JFactory::getDBO(); 
	$q = 'select `group_id`, `user_id` from #__user_usergroup_map where user_id in ('.implode(',', $user_ids).') '; 
	$db->setQuery($q); 
	$gs = $db->loadAssocList(); 
	$users = array(); 
	foreach ($gs as $row) {
			$uid = (int)$row['user_id']; 
			$gid = (int)$row['group_id']; 
			if (empty($users[$uid])) $users[$uid] = array(); 
			$users[$uid][$gid] = $gid; 
			
	}
    */
		
		$q = 'select g.`user_id`, u.`email`, g.`group_id`, count(g.`group_id`) as `c` from `#__user_usergroup_map` as g right join #__users as u on (g.`user_id` = u.`id`) where (('.implode(' or ', $or).') or (u.`id` in ('.implode(',', $users).'))) group by g.`user_id` ';
		
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		$my_user_id = JFactory::getUser()->get('id'); 
		foreach ($res as $row) {
			$user_id = (int)$row['user_id']; 
			$c = (int)$row['c']; 
            $g = (int)$row['group_id']; 
            if ($g === $manager) {
                $testusers[$user_id] = new stdClass(); 
					$testusers[$user_id]->email = $row['email']; 
					$testusers[$user_id]->user_id = $user_id; 
					unset($emails[$row['email']]); 
                     unset($users[$user_id]); 
            }    
            if ($g === $testuser) {
                $testusers[$user_id] = new stdClass(); 
					$testusers[$user_id]->email = $row['email']; 
					$testusers[$user_id]->user_id = $user_id; 
					unset($emails[$row['email']]); 
                     unset($users[$user_id]); 
            }              
			//unset test users assigned to 2 and more groups:
			if (($user_id !== $my_user_id) && ($c > 1)) {
				if (isset($emails[$row['email']])) {
					$testusers[$user_id] = new stdClass(); 
					$testusers[$user_id]->email = $row['email']; 
					$testusers[$user_id]->user_id = $user_id; 
					unset($emails[$row['email']]); 
				}
				if (isset($users[$user_id])) {
				  $testusers[$user_id] = new stdClass(); 
				  $testusers[$user_id]->email = $row['email']; 
				  $testusers[$user_id]->user_id = $user_id; 
				  unset($users[$user_id]); 
				}
			}
			elseif ($c > 1) {
				$testusers[$user_id] = new stdClass(); 
				$testusers[$user_id]->email = $row['email']; 
				$testusers[$user_id]->user_id = $user_id; 
			}
			
		}
		
		
		
		if ($incm) {
		 $incm = $emails; 
		}
		return $users; 
	}
	
	public static function getNames() {
		
		$user_id = JFactory::getUser()->get('id', 0); 
		if (empty($user_id)) return array(); 
		
		$params = self::getParams(); 
		$b2bshared = $params->get('b2bshared', 0); 
		if (!empty($b2bshared)) {
			self::getAuthorizedUsers($user_id); 
		}
		self::checkCreateTable();
		$db = JFactory::getDBO(); 
		$q = 'select `id`, `hash` from `#__mod_cartsave` where '; 
		
		if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' `user_id` = '.(int)$user_id;  
				}
		
		$q .= ' order by `id` desc '; 
		
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (!empty($res)) {
			$names = array(); 
			foreach ($res as $row) {
				$names[(int)$row['id']] = $row['hash']; 
			}
			return $names; 
		}
		else {
			return array(); 
		}
		
	}
	private static function checkValidateQuantities($cartData) {
		
	}
	private static function returnHandler($txt='') {
		$return = JRequest::getVar('return', ''); 
		$redirecttocart = (int)self::getParams()->get('redirecttocart', 0); 
		if ($redirecttocart === 0) {
			$url = JRoute::_('index.php?option=com_virtuemart&view=cart'); 
			if (empty($txt)) {
			  JFactory::getApplication()->redirect($url); 
			}
			else {
				JFactory::getApplication()->redirect($url, $txt); 
			}
		}
		if (!empty($return)) {
			$url = base64_decode($return); 
			if (empty($txt)) {
			  JFactory::getApplication()->redirect($url); 
			}
			else {
				JFactory::getApplication()->redirect($url, $txt); 
			}
		}
	}
	
	private static function store($hash, $json_data, $user_id) {
		self::checkCreateTable(); 
		if (empty($hash)) return; 
		$db = JFactory::getDBO(); 
		$b2bshared = self::getParams()->get('b2bshared', 0); 
		$q = "select `id` from #__mod_cartsave where `hash` = '".$db->escape($hash)."' "; 
		if (!empty($user_id)) {
			if (!empty($b2bshared)) {
					$users = self::getAuthorizedUsers($user_id); 
					$q .= ' and `user_id`IN ('.implode(',', $users).')';  
				}
				else
				{
					/*if (!$params->get('allowany', 0)) */
					$q .= ' and `user_id` = '.(int)$user_id;  
				}
		}
		$q .= " limit 1";
		$db->setQuery($q); 
		$rx = $db->loadResult(); 
		if (empty($user_id)) {
			if (!empty($rx)) {
				
				
				return self::returnHandler(__LINE__); 
			}
		}
		if (!empty($rx)) { 
			$q = "update `#__mod_cartsave` set `cart` = '".$db->escape($json_data)."', `modified` = NOW() where `id` = ".(int)$rx;
			$db->setQuery($q); 
			$db->execute(); 
			
			
			return self::returnHandler(__LINE__); 
		}
		
		$q = "insert into `#__mod_cartsave`  (`id`, `cart`, `hash`, `user_id`, `created`, `created_by`, `modified`, `modified_by`) ";
	    $q .= " values (NULL, '".$db->escape($json_data)."', '".$db->escape($hash)."', ".(int)$user_id.", NOW(), ".(int)$user_id.", NOW(), ".(int)$user_id.")"; 
		$db->setQuery($q); 
		$db->execute(); 
		
		return self::returnHandler(__LINE__); 
		
	}
	
	private static function checkCreateTable() {
		if (self::tableExists('mod_cartsave')) return; 
		
		$inno = 'CREATE TABLE IF NOT EXISTS `#__mod_cartsave` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`cart` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
		`hash` varchar(160) CHARACTER SET utf8 NOT NULL COLLATE utf8_general_ci NOT NULL,
		`user_id` int(11) NOT NULL,
		`extra` varchar(255) NOT NULL DEFAULT \'\',
		`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_by` int(11) NOT NULL DEFAULT \'0\',
		`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`modified_by` int(11) NOT NULL DEFAULT \'0\',
		
		PRIMARY KEY (`id`),
		KEY `hash` (`hash`),
		KEY `user_id` (`user_id`)
	
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=380'; 
  
   $db = JFactory::getDBO(); 
   $db->setQuery($inno); 
   $db->execute(); 
	}
	
	
  private static function tableExists($table)
  {
   $db = JFactory::getDBO();
   $prefix = $db->getPrefix();
   $table = str_replace('#__', '', $table); 
   $table = str_replace($prefix, '', $table); 
 
   $q = "SHOW TABLES LIKE '".$db->getPrefix().$table."'";
	   $db->setQuery($q);
	   $r = $db->loadResult();
	   if (!empty($r)) 
	   {
	   
	   return true;
	   }
   return false;
  }
	
	public static function hasItems() {
		
		$session = JFactory::getSession(); 
		$cart = $session->get('vmcart', 0, 'vm');
		
		if (empty($cart)) {
			$c2 = $session->get('vmcart', 0);
			if (!empty($c2)) $cart = $c2; 
		}
		
		
		if (empty($cart)) {
			
			return false; 
		}
		$sessionCart = (object)json_decode( $cart ,true);
		if (!empty($sessionCart) && (!empty($sessionCart->cartProductsData))) {
			
			return true; 
		}
		
		
		
	self::loadVM(); 
	   
	 $cart = VirtueMartCart::getCart(false); 
	 if (!empty($cart->cartProductsData)) return true; 
	 
	 
	 
	 return false; 
		

	}
	
	public static function loadVM() {
			
	   if (!class_exists('VmConfig'))	  
	   {
	    require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
	    
	   }
	   VmConfig::loadConfig(); 
	  
	   
	 if (!class_exists('VirtueMartCart'))
	 require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php');
 
	 JFactory::getLanguage()->load('com_virtuemart'); 
 
	}
	public static function readXLS($src) {
		
		self::_getPHPExcel(); 
		
				
		// Create new PHPExcel object
		if (!file_exists($src)) return array(); 
		
		$reader = PHPExcel_IOFactory::createReaderForFile($src); 
		$reader->setReadDataOnly(true);
		$objXLS = $reader->load($src);
		$value = $objXLS->getSheet(0)->getCell('A1')->getValue();
		$sheet = $objXLS->getSheet(0); //->getCellByColumnAndRow(0, 1);
		$rows = $sheet->getHighestRow();
		$rows = (int)$rows; 
		$data = array(); 
		
		$returnrows = array(); 
		for ($i=0; $i<=10; $i++) {
			  $row = 1; 
			  $val = $sheet->getCellByColumnAndRow($i,$row)->getValue(); 
			  if (empty($val)) continue; 
			  $header[$i] = $val; 
		}
		for ($row=2; $row<=$rows; $row++) {
			
			
			
			
			
			$line = array(); 
			for ($i=0; $i<=10; $i++) {
			  $val = $sheet->getCellByColumnAndRow($i,$row)->getValue(); 
			  
			  //echo $i.'_'.$row.'_'.$val."<br />\n"; 
			  if (isset($header[$i])) {
				  $key = $header[$i]; 
				  if (is_null($val)) $val = ''; 
				  $line[$key] = $val; 
			  }
			 
			 
			  
			 
			  
			}
			 
				  $returnrows[] = $line; 
			 
			
			
			  
			 
		}
		$objXLS->disconnectWorksheets();
		
		$reader = null; 
		$objXLS = null; 
		
		unset($reader);
		unset($objXLS);
		
		
		
		return $returnrows; 
		
		
	}
	private function createExcell($post) {
		$this->_getPHPExcel(); 
		$post = JRequest::get('get'); 
			
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("RuposTel Systems")
							 ->setLastModifiedBy("RuposTel Systems")
							 ->setTitle("OPC Product Tabs")
							 ->setSubject("OPC Product Tabs")
							 ->setDescription("OPC Product Tabs for VirtueMart")
							 ->setKeywords("orders, virtuemart, eshop")
							 ->setCategory("Products");
		
		
		
			 
		$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
		
		$db = JFactory::getDBO(); 
		$q = 'select * from #__producttabs where 1=1'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		$header_done = false; 
		
		$has_data = array(); 
		
		$lg = $this->getDefaultLang(); 
			$lgO = $lg; 
			$lg = strtolower($lg);
			$lg = str_replace('-', '_', $lg); 
		
		if (!empty($res))
		foreach ($res as $ind=>$row) {
			
			$product_id = (int)$row['virtuemart_product_id']; 
			$has_data[$product_id] = $product_id; 
			
			$q = 'select product_s_desc, product_desc,product_name from `#__virtuemart_products_'.$lg.'` where virtuemart_product_id = '.(int)$product_id; 
			$db->setQuery($q); 
			$datas = $db->loadAssoc(); 
			$row['product_name'] = $datas['product_name']; 
			$row['product_s_desc'] = $datas['product_s_desc']; 
			$row['product_desc'] = $datas['product_desc']; 
			
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
			if (empty($header_done)) {
				$extraMsg = 'Note: id (local autoincrement value), virtuemart_product_id (as reference to #__virtuemart_products, tabname+tabcontent (no html recommended here), tabcontent (html of the tab), extra1 (language for the input, used for single language sites as well), extra2 (zero for main language set up in Joomla frontend default, non-zero as reference to the main language #__producttabs.id column), ordering (ordering of the tab within the single product, it must not share the same value with the other tabs), params (reserved, later a json data of rendered modules per joomla params columns in modules or components)';
			$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, 1, $extraMsg);
			}
			$header_done = true; 
			$example_row = $row; 
		}
		
		foreach ($example_row as $kk=>$vX) {
			$example_row[$kk] = ''; 
		}
		
		
		$incl_all = $post['jform']['params']['with_empty']; //jform[params][with_empty]
		if (!empty($incl_all)) {
			
			
			$q = 'SELECT  virtuemart_product_id,product_s_desc, product_desc, product_name FROM    `#__virtuemart_products_'.$lg.'` p WHERE   NOT EXISTS 
		(
        SELECT  1
        FROM    #__producttabs e
        WHERE   e.virtuemart_product_id = p.virtuemart_product_id
        )'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		
		$last_rown = $rown_n+1; 
		foreach ($res as $k=>$v) {
			$rown_n = $last_rown+$k; 
			
			
			foreach ($v as $ColN => $ColV) {
			  $example_row[$ColN] = $ColV;
			}
			
			$example_row['extra2'] = 0; 
			$example_row['extra1'] = $lgO; 
			$example_row['ordering'] = 0; 
			
			
			
			$i = 0; 
			foreach ($example_row as $II=>$VV) {
				$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($i, $rown_n, $VV);
			 $i++; 
			}
			
		}
		
		}
		
		
		
		
		

	$objPHPExcel->getActiveSheet()->setTitle('ProductTabs');
	$objPHPExcel->setActiveSheetIndex(0);
	


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output'); 
//$objWriter->save($tmp); 

unset($objWriter); 
$objWriter = null; 

@header('Content-Type: application/vnd.ms-excel');
@header('Content-Disposition: attachment;filename="products.xlsx"');
@header('Cache-Control: max-age=0');
	flush(); 
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
	
	
private static function _getPHPExcel() {
		@ini_set("memory_limit",'32G');
		
		if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) 
			self::_die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel<br />Install via RuposTel One Page Checkout -> OPC Order Manager -> Excell Export -> Download and Install');
		
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
		require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');
	}
	
	
}
