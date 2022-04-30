<?php
$requires_painting = false; 
$xml_has_data = false; 
$withTax = true; //for item import
ob_start(); 
?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soap:Header/>
   <soap:Body>
      <sher:GetResponse>
         <sher:xml><?php 
$header = ob_get_clean(); 
ob_start(); 
echo '<?xml version="1.0" encoding="utf-8" ?>'; 

?><Import>
	<InterfaceVersion>1.0.0.0</InterfaceVersion>
	<SecurityCode><?php echo $this->securityCode; ?></SecurityCode>
	<ApplicationId><?php echo $this->ApplicationId; ?></ApplicationId>
	
	<?php //if (false) { 
	?> 
	<?php if (!empty($this->orders)) { 
	$xml_has_data = true; 
	?>
	<Orders>
	    <?php foreach ($this->orders as $order) { 
		$color_code = ''; 
		$requires_painting = sherpaHelper::checkForOnHoldAttribute($order, $color_code); 
		$bt = $order['details']['BT']; 
		$st = $order['details']['ST']; 
		if (empty($st)) $st = $bt; 
		
		
		foreach ($bt as $kk=>$vv) {
			$mv = html_entity_decode($vv);
			if ($mv === '_') $mv = ''; 
			$bt->$kk = $mv; 
		}
		
		foreach ($st as $kk=>$vv) {
			$mv = html_entity_decode($vv);
			if ($mv === '_') $mv = ''; 
			$st->$kk = $mv;
		}
		
		if (!empty($bt->addon)) {
			$bt->HouseNumberAddon = $bt->addon; 
		}
		if (!empty($st->addon)) {
			$st->HouseNumberAddon = $st->addon; 
		}
		
		
		
		//$bt->house_nr = (int)$bt->house_nr; 
		/*
		$ea = array(); 
						if (!empty($bt->house_nr)) {
							$nr_found = true; 
							
							
							$test_house = (int)$bt->house_nr;
							
							
							if ($test_house != $bt->house_nr) {
									$bt->HouseNumberAddon = $bt->house_nr; 
									$bt->house_nr = ''; 
									$nr_found = false; 
							}
						}
						else {
						$nr_found = false; 
						$ea = explode(' ', $bt->address_1); 
						foreach ($ea as $z => $adrpart) {
							$adrpart = trim($adrpart); 
							if (is_numeric($adrpart)) {
								$xint = (int)$adrpart; 
								if ($xint == $adrpart) {
									$bt->house_nr = $xint; 
									unset($ea[$z]); continue; 
									$nr_found = true; 
								}
							}
						}
						}
					
					if (!empty($ea)) {
					$eatest = implode(' ', $ea); 
					if ($eatest !== $bt->address_1) {
						$bt->address_1 = $eatest; 
					}
					}
					
					//$st->house_nr = (int)$st->house_nr; 
		$ea = array(); 
						if (!empty($st->house_nr)) {
							$nr_found = true; 
							
							$test_house = (int)$st->house_nr;
							if ($test_house != $st->house_nr) {
									$st->HouseNumberAddon = $st->house_nr; 
									
									$st->house_nr = ''; 
									$nr_found = false; 
							}
							
						}
						else {
						$nr_found = false; 
						$ea = explode(' ', $st->address_1); 
						foreach ($ea as $z => $adrpart) {
							$adrpart = trim($adrpart); 
							if (is_numeric($adrpart)) {
								$xint = (int)$adrpart; 
								if ($xint == $adrpart) {
									$st->house_nr = $xint; 
									unset($ea[$z]); continue; 
									$nr_found = true; 
								}
							}
						}
						}
					
					if (!empty($ea)) {
					$eatest = implode(' ', $ea); 
					if ($eatest !== $st->address_1) {
						$st->address_1 = $eatest; 
					}
					}
				
		*/
		$bt->order_billDiscountAmount = (float)$bt->order_billDiscountAmount; 
		if ($bt->order_billDiscountAmount === 'NaN') $bt->order_billDiscountAmount = 0; 

		$bt->coupon_discount = (float)$bt->coupon_discount; 
		if ($bt->coupon_discount === 'NaN') $bt->coupon_discount = 0; 

		
		$bt->order_total = (float)$bt->order_total; 
		if ($bt->order_total < 0.001) $bt->order_total = 0; 
		
		$bt->order_payment = (float)$bt->order_payment; 
		if ($bt->order_payment === 'NaN') $bt->order_payment = 0; 
		
		
		?>
		<Order>
			<Reference><?php echo $this->CustomerCodePrefix; 
			echo $order['details']['BT']->order_number; ?></Reference>
			<PaymentMethodCode><?php 
			//var_dump($order['details']['BT']->virtuemart_paymentmethod_id); 
			//var_dump($bt->order_total); die(); 
			
			 if (empty($bt->order_total) && (empty($order['details']['BT']->virtuemart_paymentmethod_id))) {
			   $zero_p = OPCConfig::get('default_payment_zero_total', 0);
			   $order['details']['BT']->virtuemart_paymentmethod_id = (int)$zero_p; 
			   
			 }
			 
			 $vmid = $order['details']['BT']->virtuemart_paymentmethod_id; 
			 $key = 'payment_'.$vmid; 
			 $sherpaPayment = ''; 
			 $this->paymentpair = (object)$this->paymentpair;
			 if (!empty($this->paymentpair->$key)) {
				 $sherpaPayment = $this->paymentpair->$key; 
				 echo htmlentities($this->paymentpair->$key);
			 }
			
			?></PaymentMethodCode>
			
			<?php 
			 $ElectronicPaid = sherpaHelper::isElectronicPayment($sherpaPayment); 
			
			 if ($ElectronicPaid) {
				 ?>
				 <ElectronicPaid><?php echo number_format($bt->order_total, 2, '.', ''); ?></ElectronicPaid>
				 <?php
			 }
			 
			 
			?>
			 <!--Optional: 
				<ElectronicPaid></ElectronicPaid>
				
				<ShippingDate></ShippingDate>
				<SendInvoiceByMail></SendInvoiceByMail>
				<OrderNumber></OrderNumber>
				<SenderAddressCode></SenderAddressCode>
			-->

			<?php
			$withTax = false; 
			$f = reset($order['items']); 
			$s = floatval($f->product_final_price); 
			$t = floatval($f->product_priceWithoutTax); 
			if (($s - $t) > 0) { 
			$withTax = true; 
			
			
			?><PricesIncl>True</PricesIncl><?php 
			}
			else {
				?><CalculateVAT>false</CalculateVAT><?php
				if (!empty($order['details']['BT']->extra_field_2)) {
					?><TaxIdNumber><![CDATA[<?php echo $order['details']['BT']->extra_field_2; ?>]]></TaxIdNumber><?php
				}
				
			}
			
			?>
			
			<WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
			<!--Optional: 
				<CalculateVAT></CalculateVAT>
				<TaxIdNumber></TaxIdNumber>
				<Priority></Priority>
			-->	
			<?php 
			  $vm_id = $order['details']['BT']->virtuemart_shipmentmethod_id; 
			  $parceltype = 'NONE'; 
			  foreach ($this->shipmentpair as $kn => $arr) {
				  $arr = (array)$arr; 	  
				  if (in_array($vm_id, $arr)) {
					  
					  
					  $parceltype = str_replace('shipment_','', $kn); 
					  if (empty($parceltype)) continue; 
					  $pa = explode('____', $parceltype); 
					  
					  
					  
					  $ParcelServiceCode = $pa[0]; 
					  $ParcelTypeCode = $pa[1]; 
					  
					  break; 
				  }
			  }
			$x = get_defined_vars(); 
			if ((!empty($this->klarnapair)) && (!empty($this->klarnapair->sherpa_shipping)))
			{
				$this->klarnapair = (object)$this->klarnapair;
				$this->klarnapair->order_total = floatval($this->klarnapair->order_total); 
				$this->klarnapair->payment = intval($this->klarnapair->payment); 
				if (!empty($this->klarnapair->order_total)) {
			if ($bt->order_total >= $this->klarnapair->order_total) 
			if ((int)$order['details']['BT']->virtuemart_paymentmethod_id === (int)$this->klarnapair->payment) {
				if ((int)$st->virtuemart_country_id === (int)$this->klarnapair->country) {
				$pa = explode('____', $this->klarnapair->sherpa_shipping); 
				$ParcelServiceCode = $pa[0]; 
			    $ParcelTypeCode = $pa[1]; 
				
				
				
				}
			}	
				}
			}
			
			
		
			
			if (!empty($ParcelServiceCode)) {
			?>
			
			<Parcel>
				<ParcelServiceCode><?php echo $ParcelServiceCode; ?></ParcelServiceCode>
				<ParcelTypeCode><?php echo $ParcelTypeCode; ?></ParcelTypeCode>
				<?php //number of packages to send: ?>
				<NumberOfColli>1</NumberOfColli>
				<?php /*
				<Kiala>
					<PickupLocation>
						<ID></ID>
						<ShortID></ShortID>
						<Name></Name>
						<Street></Street>
						<Zip></Zip>
						<City></City>
						<CountryCode></CountryCode>
						<Country></Country>
					</PickupLocation>
				</Kiala>
				*/
				?>
			</Parcel>
			<?php /* 
			<Parcel>
				<ParcelServiceCode></ParcelServiceCode>
				<ParcelTypeCode></ParcelTypeCode>
				<NumberOfColli></NumberOfColli>
				<SelektVracht>
					<PickUpLocation>
						<ID></ID>
						<Name></Name>
						<Street></Street>
						<Zip></Zip>
						<City></City>
					</PickUpLocation>
				</SelektVracht >
			</Parcel>
			<Parcel>
				<PakjeGemakLocation>
					<Id></Id>
					<LocationTypeId></LocationTypeId>
					<LocationType></LocationType>
					<Name></Name>
					<Street> </Street>
					<PostalcodeNumeric></PostalcodeNumeric>
					<PostalcodeAlpha></PostalcodeAlpha>
					<City></City>
					<Housenumber></Housenumber>
					<HousenumberAdditional></HousenumberAdditional>
					<Phonenumber />
					<Kic />
					<IsActive />
					<ProductCode></ProductCode>
				</PakjeGemakLocation>
			</Parcel>
			<Parcel>
				<ParcelShop>
					<ParcelShopId></ParcelShopId>
					<PudoId></PudoId>
					<Company></Company>
					<Street></Street>
					<HouseNumber></HouseNumber>
					<State></State>
					<CountryCode></CountryCode>
					<PostalCode></PostalCode>
					<City></City>
					<Town></Town>
					<Phone></Phone>
					<Fax></Fax>
					<Email></Email>
					<Homepage></Homepage>
				</ParcelShop>
				
			</Parcel>
			*/
			
			}
			?>
			<Customer>
				<CustomerCode><?php echo $this->CustomerCodePrefix; 
				
				if (!empty($order['details']['BT']->virtuemart_user_id)) {
				  echo $order['details']['BT']->virtuemart_user_id; 
				}
				else {
					echo '__'.$order['details']['BT']->virtuemart_order_id; 
				}
				
				
				?></CustomerCode>
				<ShopCode><?php 
				$key = $order['details']['BT']->order_language; 
				if (!empty($this->shop_code_lang->$key)) 
					echo $this->shop_code_lang->$key; 
				else 
					echo $this->shop_code; 
				
				?></ShopCode>
				 <!--Optional: 
				<Creditlimit></Creditlimit>
				<MaxNumberOfOrdersDue></MaxNumberOfOrdersDue>
				-->
				<BillingAddress>
					<CountryCode><?php
					  $db = JFactory::getDBO(); 
					  $q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$bt->virtuemart_country_id; 
					  $db->setQuery($q); 
					  $c = $db->loadResult(); 
					  if (!empty($c)) {
					    echo $c; 
					  }
					?></CountryCode>
					<Gender><?php 
					  $title = $bt->title; 
					  switch ($bt->title) {
						  case 'Mr': echo 'M'; break; 
						  case 'COM_VIRTUEMART_SHOPPER_TITLE_MR': echo 'M'; break; 
						  case JText::_('COM_VIRTUEMART_SHOPPER_TITLE_MR'): echo 'M'; break; 
						  default: echo 'F'; 
					  }
					?></Gender>
					<NameFirst><![CDATA[<?php echo $bt->first_name; ?>]]></NameFirst>
					<?php if (!empty($bt->middle_name)) { ?><NamePreLast><![CDATA[<?php echo $bt->middle_name; ?>]]></NamePreLast><?php } ?>
					<NameLast><![CDATA[<?php echo $bt->last_name; ?>]]></NameLast>
					<Phone><![CDATA[<?php echo $bt->phone_1; ?>]]></Phone>
					<?php if (!empty($bt->phone_2)) { ?><Mobile><?php echo $bt->phone_2; ?>]]></Mobile><?php } ?>
					<!-- <Fax></Fax> -->
					<?php 
					/* ADDRESS1 AND ADDRESS2 ARE NOT USED BY SHERPA AT ALL */
					?>
					
					<AddressLine1><![CDATA[<?php echo $bt->address_1; 
					//if (!empty($bt->house_nr)) echo $bt->house_nr; 
					?>]]></AddressLine1>
					
					<?php if (!empty($bt->address_2)) { ?>
					<AddressLine2><![CDATA[<?php echo $bt->address_2; ?>]]></AddressLine2>
					<?php } ?>
				
					<!-- <AddressLine3></AddressLine3>
					<StateCode></StateCode>-->
					
					<Street><![CDATA[<?php echo $bt->address_1; ?>]]></Street>
					
					<?php  if (!empty($bt->house_nr)) { ?><HouseNumber><![CDATA[<?php echo $bt->house_nr; ?>]]></HouseNumber><?php } 
					if (!empty($bt->HouseNumberAddon)) { 
					?>
					<HouseNumberAddon><![CDATA[<?php echo $bt->HouseNumberAddon; ?>]]></HouseNumberAddon>
					<?php } ?>
					<PostalCode><![CDATA[<?php echo $bt->zip; ?>]]></PostalCode>
					<City><![CDATA[<?php echo $bt->city; ?>]]></City>
					<Company><![CDATA[<?php echo $bt->company; ?>]]></Company>
					<TaxIdNumber><![CDATA[<?php echo $bt->extra_field_2; ?>]]></TaxIdNumber>
					<!-- <ChamberNumber></ChamberNumber> -->
					<Email><![CDATA[<?php echo $bt->email; ?>]]></Email>
					<!-- <Homepage></Homepage> 
					<AllowMailing></AllowMailing>
					<BankAccount></BankAccount>
					<NameBankAccount></NameBankAccount>
					<CityBankAccount></CityBankAccount>
					<PersonalNumber></PersonalNumber>
					<DateOfBirth></DateOfBirth> -->
				</BillingAddress>
				<ShipmentAddress>
					<CountryCode><?php
					  $db = JFactory::getDBO(); 
					  $q = 'select country_2_code from #__virtuemart_countries where virtuemart_country_id = '.(int)$st->virtuemart_country_id; 
					  $db->setQuery($q); 
					  $c = $db->loadResult(); 
					  if (!empty($c)) {
					    echo $c; 
					  }
					?></CountryCode>
					<?php if (!empty($st->title)) { ?><Gender><?php 
					  $title = $st->title; 
					  switch ($st->title) {
						  case 'Mr': echo 'M'; break; 
						  case 'COM_VIRTUEMART_SHOPPER_TITLE_MR': echo 'M'; break; 
						  case JText::_('COM_VIRTUEMART_SHOPPER_TITLE_MR'): echo 'M'; break; 
						  default: echo 'F'; 
					  }
					?></Gender><?php } ?>
					<NameFirst><![CDATA[<?php echo $st->first_name; ?>]]></NameFirst>
					<?php if (!empty($st->middle_name)) { ?><NamePreLast><![CDATA[<?php echo $st->middle_name; ?>]]></NamePreLast><?php } ?>
					<NameLast><![CDATA[<?php echo $st->last_name; ?>]]></NameLast>
					<Phone><![CDATA[<?php echo $st->phone_1; ?>]]></Phone>
					<?php if (!empty($st->phone_2)) { ?><Mobile><?php echo $st->phone_2; ?>]]></Mobile><?php } ?>
					<!-- <Fax></Fax> -->
					<?php
					/*NOT USED BY SHERPA:*/
					?>
					
					<AddressLine1><![CDATA[<?php echo $st->address_1; 
					 //if (!empty($st->house_nr)) echo $st->house_nr; 
					 ?>]]></AddressLine1>
					
					<?php if (!empty($bt->address_2)) { ?>
					<AddressLine2><![CDATA[<?php echo $st->address_2; ?>]]></AddressLine2>
					<?php } ?>
					
					<!-- <AddressLine3></AddressLine3>
					<StateCode></StateCode>-->
					
					<Street><![CDATA[<?php echo $st->address_1; ?>]]></Street>
					
					<?php  if (!empty($st->house_nr)) { 
					?><HouseNumber><![CDATA[<?php echo $st->house_nr; ?>]]></HouseNumber>
					<?php } 
					if (!empty($st->HouseNumberAddon)) { 
					?>
					<HouseNumberAddon><![CDATA[<?php echo $st->HouseNumberAddon; ?>]]></HouseNumberAddon>
					<?php } ?>
					<PostalCode><![CDATA[<?php echo $st->zip; ?>]]></PostalCode>
					<City><![CDATA[<?php echo $st->city; ?>]]></City>
					<Company><![CDATA[<?php echo $st->company; ?>]]></Company>
					<TaxIdNumber><![CDATA[<?php echo $st->extra_field_2; ?>]]></TaxIdNumber>
					<!-- <ChamberNumber></ChamberNumber> -->
					<Email><![CDATA[<?php echo $st->email; ?>]]></Email>
					<!-- <Homepage></Homepage> 
					<AllowMailing></AllowMailing>
					<BankAccount></BankAccount>
					<NameBankAccount></NameBankAccount>
					<CityBankAccount></CityBankAccount>
					<PersonalNumber></PersonalNumber>
					<DateOfBirth></DateOfBirth> -->
				</ShipmentAddress>
				<?php /*
				<CustomFields>
					<Field1></Field1>
					<Field2></Field2>
					<Field3></Field3>
				</CustomFields>
				*/
				?>
			</Customer>
			<OrderLines><?php 
			$total = 0; 
			$has_painting_desc = false; 
			foreach ($order['items'] as $item) { 
			$fn = (float)$item->product_final_price; 
			$q = (float)$item->product_quantity;
			$total += ($fn * $q); 
			$painting_desc = ''; 
			$product_title = $item->order_item_name; 
			$custom_fields = array(); 
			if (!empty($item->product_attribute)) {
				$jdata = json_decode($item->product_attribute, true); 
				foreach ($jdata as $custom_id => $row) {
					foreach ($row as $customfield_id => $value) {
						
						$customer_value = $value['comment']; 
						
						$db = JFactory::getDBO(); 
						$q = 'select `custom_title` from #__virtuemart_customs where `virtuemart_custom_id` = '.(int)$custom_id; 
						$db->setQuery($q); 
						$title = $db->loadResult(); 
						if (empty($title)) continue; 
						$titleN = JFile::makeSafe($title); 
						
						$title = JText::_($title); 
						$title = str_replace('?', '', $title); 
						if (empty($customer_value)) continue; 
						
						$painting_desc = $title.': '.$customer_value; 
						$product_title .= ', '.$title.': '.$customer_value; 
						$custom_fields[$titleN] = $title.': '.$customer_value;
						$has_painting_desc = true; 
					}
				}
			}
			if (false)
			if (!empty($requires_painting)) {
			?>
				
				<OrderLine>
					<Description><![CDATA[<?php echo 'Painting required: '.$color_code; ?>]]></Description>
				</OrderLine>
			<?php } ?>
				<OrderLine>
					<ItemCode><![CDATA[<?php echo $item->order_item_sku; ?>]]></ItemCode>
					<QuantityOrdered><?php echo $item->product_quantity; ?></QuantityOrdered>
					<QuantityToDeliver><?php echo $item->product_quantity; ?></QuantityToDeliver>
					<Description><![CDATA[<?php 
					echo $product_title; 
					//echo $item->order_item_name; 
					?>]]></Description>
					<!--<QuantityDropship></QuantityDropship>
					<DropshipSupplierCode></DropshipSupplierCode>
					<QuantityBackorder></QuantityBackorder>-->
					<?php if (empty($withTax)) { ?>
					<Price><?php echo number_format($item->product_priceWithoutTax, 6, '.', ''); ?></Price>
					<Amount><?php echo number_format(floatval($item->product_priceWithoutTax) * floatval($item->product_quantity), 6, '.', ''); ?></Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl><?php echo number_format($item->product_final_price, 6, '.', ''); ?></PriceIncl>
					<AmountIncl><?php echo number_format($item->product_subtotal_with_tax, 6, '.', ''); ?></AmountIncl>
					<?php } ?>
					
					<!--<VatPercentage></VatPercentage>-->
					
					<?php if (!empty($custom_fields)) { ?>
					<CustomFields>
					  <?php foreach ($custom_fields as $c_key=>$c_val) { 
						echo '<'.$c_key.'><![CDATA['.$c_val.']]></'.$c_key.'>'; 
					   } ?>
					</CustomFields>
					<?php } ?>
					
				</OrderLine>
				<?php
				if (!empty($painting_desc)) {
			?>
				<OrderLine>
					<!--<Description><![CDATA[Discount]]></Description>-->
					<ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'PAINTED'; ?>]]></ItemCode>
					<Description><![CDATA[<?php echo $painting_desc; ?>]]></Description>
					<QuantityOrdered>1</QuantityOrdered>
					<QuantityToDeliver>1</QuantityToDeliver>
					<?php if (empty($withTax)) { ?>
					<Price>0</Price>
					<Amount>0</Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl>0</PriceIncl>
					<AmountIncl>0</AmountIncl>
					<?php } ?>
				</OrderLine>
			
			
			<?php 
			}
				
				
				
			 } 
			
			if (isset($bt->order_shipment)) {
				?><OrderLine>
					<!--<Description><![CDATA[<?php echo $bt->shipment_name_txt; ?>]]></Description>-->
					<ItemCode><?php echo $this->CustomerCodePrefix.'SHIPPING_'.$order['details']['BT']->virtuemart_shipmentmethod_id; ?></ItemCode>
					<QuantityOrdered>1</QuantityOrdered>
					<QuantityToDeliver>1</QuantityToDeliver>
					<?php if (empty($withTax)) { ?>
					<Price><?php echo $bt->order_shipment; ?></Price>
					<Amount><?php echo $bt->order_shipment; ?></Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl><?php 
					  $sh =  (float)$bt->order_shipment + (float)$bt->order_shipment_tax; 
					  $sh = number_format($sh, 6, '.', ''); 
					  $total += $sh; 
					  echo $sh; 
					?></PriceIncl>
					<AmountIncl><?php echo $sh; ?></AmountIncl>
					<?php } ?>
				</OrderLine>
				
				
				<?php
			}
			?>
			<?php
			/*
			if (!empty($bt->order_billDiscountAmount)) {
				$bt->order_billDiscountAmount = (float)$bt->order_billDiscountAmount; 
				$bt->order_billDiscountAmount = (-1) * abs($bt->order_billDiscountAmount); 
				
				
				?><OrderLine>
					<!--<Description><![CDATA[Discount]]></Description>-->
					<ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'DISCOUNT_'.$order['details']['BT']->coupon_code; ?>]]></ItemCode>
					<QuantityOrdered>1</QuantityOrdered>
					<QuantityToDeliver>1</QuantityToDeliver>
					<?php if (empty($withTax)) { ?>
					<Price><?php echo $bt->order_billDiscountAmount; ?></Price>
					<Amount><?php echo $bt->order_billDiscountAmount; ?></Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl><?php 
					  $sh =  (float)$bt->order_billDiscountAmount; 
					  $sh = number_format($sh, 6, '.', ''); 
					  $total += $sh; 
					  echo $sh; 
					?></PriceIncl>
					<AmountIncl><?php echo $sh; ?></AmountIncl>
					<?php } ?>
				</OrderLine>
				
				
				<?php
			}
			
			
			
			*/
			
			if (!empty($bt->coupon_discount)) {
				$bt->coupon_discount = (float)$bt->coupon_discount; 
				$bt->coupon_discount = (-1) * abs($bt->coupon_discount); 
				
				
				?><OrderLine>
					<!--<Description><![CDATA[Discount]]></Description>-->
					<ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'DISCOUNT_'.$order['details']['BT']->coupon_code; ?>]]></ItemCode>
					<QuantityOrdered>1</QuantityOrdered>
					<QuantityToDeliver>1</QuantityToDeliver>
					<?php if (empty($withTax)) { ?>
					<Price><?php echo $bt->coupon_discount; ?></Price>
					<Amount><?php echo $bt->coupon_discount; ?></Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl><?php 
					  $sh =  (float)$bt->coupon_discount; 
					  $sh = number_format($sh, 6, '.', ''); 
					  $total += $sh; 
					  echo $sh; 
					?></PriceIncl>
					<AmountIncl><?php echo $sh; ?></AmountIncl>
					<?php } ?>
				</OrderLine>
				
				
				<?php
			}
			
			if (!empty($bt->order_payment)) {
				
				
				
				?><OrderLine>
					<!--<Description><![CDATA[Discount]]></Description>-->
					<ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'PAYMENT_FEE'; ?>]]></ItemCode>
					<QuantityOrdered>1</QuantityOrdered>
					<QuantityToDeliver>1</QuantityToDeliver>
					<?php if (empty($withTax)) { ?>
					<Price><?php echo $bt->order_payment; ?></Price>
					<Amount><?php echo $bt->order_payment; ?></Amount>
					<?php } if ($withTax) { ?>
					<PriceIncl><?php 
					  $sh =  (float)$bt->order_payment; 
					  $sh = number_format($sh, 6, '.', ''); 
					  $total += $sh; 
					  echo $sh; 
					?></PriceIncl>
					<AmountIncl><?php echo $sh; ?></AmountIncl>
					<?php } ?>
				</OrderLine>
				
				
				<?php
			}
			

			
			
			
			
			
			?>
			
			
			
			</OrderLines>
			<CustomFields>
			  <OrderTotalEshop><?php echo number_format($bt->order_total, 2, '.', ''); ?></OrderTotalEshop>
			</CustomFields>
			<?php /*
			<CustomFields>
				<Field1></Field1>
				<Field2></Field2>
				<Field3></Field3>

			</CustomFields>
			<Properties>
				<remote_ip></remote_ip>
			</Properties>
			*/
			?>
			<UserRemarks><![CDATA[<?php 
			$bt->customer_note = str_replace('<![CDATA[', '', $bt->customer_note); 
			$bt->customer_note = str_replace(']]>', '', $bt->customer_note); 
			
			echo $bt->customer_note; ?>]]></UserRemarks>
		</Order>
		<?php } ?>
	</Orders>
	<?php } ?>

	
	<?php 
	 //} 
	?>
	<Items CollectErrors="true">
	  <?php if (!empty($bt)) { ?>
		<Item>
		 <ItemType>Text</ItemType>
		 <ItemCode><?php echo $this->CustomerCodePrefix.'SHIPPING_'.$order['details']['BT']->virtuemart_shipmentmethod_id; ?></ItemCode>
		 <Description><![CDATA[<?php echo $bt->shipment_name_txt; ?>]]></Description>
		 <Brand><?php echo $this->CustomerCodePrefix.'SHIPPING'; ?></Brand>
		 <BrandGroup><?php echo 'SHIPPING'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>2</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <Price><?php echo $bt->order_shipment; ?></Price>
		 <ItemStatus>Active</ItemStatus>
		 <?php if (false) { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		</Item>
		<?php
	  }
	  
	  if (!empty($bt->order_payment)) {
		  ?>
			<Item>
		 <ItemType>Text</ItemType>
		 <ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'PAYMENT_FEE'; ?>]]></ItemCode>
		 <Description><![CDATA[<?php echo 'Payment Fee'; ?>]]></Description>
		 <Brand><?php echo $this->CustomerCodePrefix.'PAYMENT_FEE'; ?></Brand>
		 <BrandGroup><?php echo 'PAYMENT_FEE'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>0</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <ItemStatus>Active</ItemStatus>
		 <?php if (false) { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		</Item>
			
			<?php
	  }
	  
		
		if ((!empty($requires_painting)) || (!empty($has_painting_desc))) {
			?> 
			<Item>
		 <ItemType>Stock</ItemType>
		 <ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'PAINTED'; ?>]]></ItemCode>
		 <Description><![CDATA[<?php echo 'Painting required '; ?>]]></Description>
		 <Brand><?php echo $this->CustomerCodePrefix.'PAINTING'; ?></Brand>
		 <BrandGroup><?php echo 'PAINTING'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>2</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <ItemStatus>Active</ItemStatus>
		 <?php if (false) { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		</Item>
			
			<?php
		}
		
		//if (!empty($bt->order_billDiscountAmount)) {
		if (!empty($bt->coupon_discount)) {
			
		?>
		<Item>
		 <ItemType>Text</ItemType>
		 <ItemCode><![CDATA[<?php echo $this->CustomerCodePrefix.'DISCOUNT_'.$order['details']['BT']->coupon_code; ?>]]></ItemCode>
		 <Description><![CDATA[<?php echo 'DISCOUNT'; ?>]]></Description>
		 <Brand><?php echo $this->CustomerCodePrefix.'DISCOUNT'; ?></Brand>
		 <BrandGroup><?php echo 'DISCOUNT'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>2</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <ItemStatus>Active</ItemStatus>
		 <?php if (false) { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		</Item>
		<?php 
			}
		
		$db = JFactory::getDBO(); 
		
		if (!empty($order['items'])) {
			foreach ($order['items'] as $item) {

				$sku = $item->order_item_sku; 
				if (isset($item->product_mpn)) {
				 $mpn = $item->product_mpn;
				 $sherpaTypeMPN = sherpaHelper::getProductType($mpn); 
				}
				$sherpaType = sherpaHelper::getProductType($sku); 
				$sherpaType_orig = $sherpaType; 
				
				$found = true; 
				if ($sherpaType === false) {
					$sherpaType = 'Stock'; 
					$found = false; 
				}
				
				if (!$found) {
				
				if (($mpn !== $sku) && (!empty($mpn))) {
					if (empty($sherpaTypeMPN)) $sherpaTypeMPN = 'Stock'; 
					if ($sherpaTypeMPN === 'Stock') $sherpaType = 'Assembly'; 
				}
				
				$englishProduct = sherpaHelper::getProductBySku($item->order_item_sku, $item->virtuemart_product_id); 
				
				//import only non-existent assemblies:
				if (($sherpaType !== 'Stock') && ($sherpaType_orig === false)) 
				{
				?>
				<Item>
				<ItemType><?php echo $sherpaType; ?></ItemType>
		 <ItemCode><![CDATA[<?php echo $item->order_item_sku; ?>]]></ItemCode>
		 <Description><![CDATA[<?php echo $englishProduct->product_name; ?>]]></Description>
		 <Brand><![CDATA[<?php 
		 if (!empty($item->virtuemart_manufacturer_name)) echo $item->virtuemart_manufacturer_name; 
		 else echo 'UNKNOWN'; ?>]]></Brand>
		 <BrandGroup><?php echo 'VM Product Import'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>2</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <ItemStatus>Active</ItemStatus>
		 <?php if ($sherpaType === 'Stock') { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		 <?php if (!empty($item->product_gtin)) {
			 ?>
				<EanCodes>
					<EanCode><![CDATA[<?php echo $item->product_gtin; ?>]]></EanCode>
				</EanCodes>
			<?php		 
		 }
		 
		
		 ?>
		 <Price><?php echo number_format($item->product_priceWithoutTax, 6, '.', ''); ?></Price>
		 
		 <?php 
if (!empty($englishProduct->img_url)) {
	?>
			<CustomFields>
				<imageurl><![CDATA[<?php echo $englishProduct->img_url; ?>]]></imageurl>
				<?php 
				if ($sherpaType === 'Assembly') { ?>
				<ShowAsmOnPicklist>true</ShowAsmOnPicklist>
				<?php } 
				?>
			</CustomFields>
	
	<?php
}


 if ($sherpaType === 'Assembly') {
			 ?>
			 <AssemblyItems>
			  <?php 
			  
			    $MPNs = array(); 
				if (strpos($mpn, '+')!==false) {
					$MPNs = explode('+', $mpn); 
				}
				else {
					$MPNs[$item->product_mpn] = $item->product_mpn; 
				}
				foreach ($MPNs as $partial_mpn) { 
			  ?>
			  <AssemblyItem>
			   <ItemCode><![CDATA[<?php echo $partial_mpn; ?>]]></ItemCode>
			   <Number>1</Number>
			  </AssemblyItem>
				<?php } ?>
			 </AssemblyItems>
			 <?php
		 }
		 elseif ($sherpaType === 'Stock') {
			 if (false) { //removed not to send any stock... 
			 ?>
			 <Stock overwrite="false"><?php echo (int)$item->product_in_stock; ?></Stock>
			 <?php
			 }
		 }
		 

?>		 
		 
			</Item>
			
				
				<?php
				} // don't import Stock products
			}
			
			
			
			
			
			
		}
		}
		
		
		
		if (!empty($this->items)) { 
		$xml_has_data = true; 
		
		foreach ($this->items as $item) {
			
		if ($item->ItemType !== 'Stock') { 
		?>
		<Item>
				<ItemType><?php echo $item->ItemType; ?></ItemType>
		 <ItemCode><![CDATA[<?php echo $item->ItemCode; ?>]]></ItemCode>
		 <Description><![CDATA[<?php echo $item->Description; ?>]]></Description>
		 <Brand><![CDATA[<?php 
		 if (!empty($item->vm_product->mf_name)) echo $item->vm_product->mf_name; 
		 else echo 'UNKNOWN'; ?>]]></Brand>
		 <BrandGroup><?php echo 'VM Product Import'; ?></BrandGroup>
		 <?php if ($withTax) { ?>
		 <VatCode>2</VatCode>
		 <?php } else { ?>
		 <VatCode>0</VatCode>
		 <?php } ?>
		 <ItemStatus>Active</ItemStatus>
		 <?php if ($item->ItemType === 'Stock') { ?>
		 <Warehouses>
		 <WarehouseCode><?php echo $this->warehouse; ?></WarehouseCode>
		 </Warehouses>
		 <?php } ?>
		 <?php if (!empty($item->vm_product->product_gtin)) {
			 ?>
				<EanCodes>
					<EanCode><![CDATA[<?php echo $item->vm_product->product_gtin; ?>]]></EanCode>
				</EanCodes>
			<?php		 
		 }
		 
		 if ($item->ItemType === 'Assembly') {
			 ?>
			 <AssemblyItems>
			    <?php foreach ($item->assemblyCode as $code) { ?>
			   <AssemblyItem>
			   <ItemCode><![CDATA[<?php echo $code; ?>]]></ItemCode>
			   <Number>1</Number>
			   </AssemblyItem>
				<?php } ?>
			 </AssemblyItems>
			 <?php
		 }
		 elseif ($item->ItemType === 'Stock') {
			 if (false) { 
			 ?>
			 <Stock overwrite="false"><?php echo (int)$item->vm_product->product_in_stock; ?></Stock>
			 <?php
			 }
		 }
		 $key = $item->vm_product->selectedPrice; 
		 if (!empty($item->vm_product->prices)) {
			 $prices = $item->vm_product->prices; 
		 }
		 else
		 if (isset($item->vm_product->allPrices[$key])) {
			 $prices = $item->vm_product->allPrices[$key]; 
		 }
		 else {
			 $prices = reset($item->vm_product->allPrices); 
		 }
		 $withoutTax = $prices['priceWithoutTax']; 
		 
		 ?>
		 <Price><?php echo number_format($withoutTax, 6, '.', ''); ?></Price>
		
		

<?php 
if (!empty($item->vm_product->img_url)) {
	?>
			<CustomFields>
				<imageurl><![CDATA[<?php echo $item->vm_product->img_url; ?>]]></imageurl>
				<?php 
				if ($item->ItemType === 'Assembly') { ?>
				<ShowAsmOnPicklist>true</ShowAsmOnPicklist>
				<?php } 
				?>
			</CustomFields>
	
	<?php
}


if (false) { ?>		
		
			<ItemType></ItemType>
			<ItemCode></ItemCode>
			<Description></Description>
			<VatCode></VatCode>
			<Brand></Brand>
			<BrandGroup></BrandGroup>
			<Stock overwrite="false"></Stock>
			<HideOnPicklist></HideOnPicklist>
			<Location></Location>
			<AutoStockLevel></AutoStockLevel>
			<StockPeriod></StockPeriod>
			<OrderVolume></OrderVolume>
			<OrderVolumeCeilFrom></OrderVolumeCeilFrom>
			<CostPrice></CostPrice>
			<Price></Price>
			<Dropship></Dropship>
			<PrintLabelsReceivedPurchaseItems></PrintLabelsReceivedPurchaseItems>
			<ItemStatus></ItemStatus>
			<EanCodes>
				<EanCode></EanCode>

			</EanCodes>
			<CustomFields>
				<imageurl></imageurl>
				<Field1></Field1>
				<Field2></Field2>
				<Field3></Field3>

			</CustomFields>
			<ItemSuppliers>
				<ItemSupplier>
					<SupplierCode></SupplierCode>
					<Preferred></Preferred>
					<SupplierItemCode></SupplierItemCode>
					<SupplierDescription></SupplierDescription>
					<SupplierPrice></SupplierPrice>
					<OrderPeriod></OrderPeriod>
					<DeliveryPeriod></DeliveryPeriod>
					<SupplierStock></SupplierStock>
					<SupplierPurchaseQty></SupplierPurchaseQty>
					<Dropship></Dropship>
				</ItemSupplier>

			</ItemSuppliers>
			<Warehouses>
				<Warehouse>
					<WarehouseCode></WarehouseCode>
					<Location></Location>
					<MinStock></MinStock>
					<MaxStock></MaxStock>
				</Warehouse>
			</Warehouses>
			
<?php }  ?>			
		</Item>
		
		<?php 
		}
		} ?>
		
			<?php } 
			
			
			?>
	</Items>

	<?php if (!empty($this->customers)) {
		$xml_has_data = true; 
		?>
	<Customers>
		<Customer>
			<CustomerCode></CustomerCode>
			<ShopCode></ShopCode>
			<BillingAddress>
				<CountryCode></CountryCode>
				<Gender></Gender>
				<NameFirst></NameFirst>
				<NamePreLast></NamePreLast>
				<NameLast></NameLast>
				<Phone></Phone>
				<Mobile></Mobile>
				<Fax></Fax>
				<AddressLine1></AddressLine1>
				<AddressLine2></AddressLine2>
				<StateCode></StateCode>
				<Street></Street>
				<HouseNumber></HouseNumber>
				<HouseNumberAddon></HouseNumberAddon>
				<PostalCode></PostalCode>
				<City></City>
				<Company></Company>
				<TaxIdNumber></TaxIdNumber>
				<ChamberNumber></ChamberNumber>
				<Email></Email>
				<Homepage></Homepage>
				<AllowMailing></AllowMailing>
				<BankAccount></BankAccount>
				<NameBankAccount></NameBankAccount>
				<CityBankAccount></CityBankAccount>
			</BillingAddress>
			<ShipmentAddress>
				<CountryCode></CountryCode>
				<Gender></Gender>
				<NameFirst></NameFirst>
				<NamePreLast></NamePreLast>
				<NameLast></NameLast>
				<Phone></Phone>
				<Mobile></Mobile>
				<Fax></Fax>
				<AddressLine1></AddressLine1>
				<AddressLine2></AddressLine2>
				<StateCode></StateCode>
				<Street></Street>
				<HouseNumber></HouseNumber>
				<HouseNumberAddon></HouseNumberAddon>
				<PostalCode></PostalCode>
				<City></City>
				<Company></Company>
				<TaxIdNumber></TaxIdNumber>
				<ChamberNumber></ChamberNumber>
				<Email></Email>
				<Homepage></Homepage>
				<AllowMailing></AllowMailing>
				<BankAccount></BankAccount>
				<NameBankAccount></NameBankAccount>
				<CityBankAccount></CityBankAccount>
			</ShipmentAddress>
			<CustomFields>
				<Field1></Field1>
				<Field2></Field2>
				<Field3></Field3>
			</CustomFields>
		</Customer>
	</Customers>
	<?php } ?>
	<?php if (!empty($this->suppliers)) { 
	$xml_has_data = true; 
	?>
	<Suppliers>
		<Supplier>
			<SupplierCode></SupplierCode>
			<BillingAddress>
				<CountryCode></CountryCode>
				<Gender></Gender>
				<NameFirst></NameFirst>
				<NamePreLast></NamePreLast>
				<NameLast></NameLast>
				<Phone></Phone>
				<Mobile></Mobile>
				<Fax></Fax>
				<AddressLine1></AddressLine1>
				<AddressLine2></AddressLine2>
				<StateCode></StateCode>
				<Street></Street>
				<HouseNumber></HouseNumber>
				<HouseNumberAddon></HouseNumberAddon>
				<PostalCode></PostalCode>
				<City></City>
				<Company></Company>
				<TaxIdNumber></TaxIdNumber>
				<ChamberNumber></ChamberNumber>
				<Email></Email>
				<Homepage></Homepage>
				<AllowMailing></AllowMailing>
				<BankAccount></BankAccount>
				<NameBankAccount></NameBankAccount>
				<CityBankAccount></CityBankAccount>
			</BillingAddress>
			<CustomFields>
				<Field1></Field1>
				<Field2></Field2>
				<Field3></Field3>

			</CustomFields>
		</Supplier>
	</Suppliers>
	<?php } ?>
	<?php if (!empty($this->notes)) { 
	$xml_has_data = true; 
	?>
	<Notes>
		<Note>
			<CustomerCode></CustomerCode>
			<Subject></Subject>
			<Note></Note>
			<OrderNumber></OrderNumber>
		</Note>
	</Notes>
	<?php } ?>
</Import>
<?php 
$xml = ob_get_clean(); 

ob_start(); 
$xml_to_process = str_replace(array("\r\r\n", "\r\n", "\n"), array('', '', ''), $xml); 
$xml_to_process = str_replace(']]>', ']]]]><![CDATA[>', $xml_to_process); 
$xml_to_process = '<![CDATA['.$xml_to_process.']]>'; 
echo $xml_to_process; 
//echo htmlentities($xml_to_process,ENT_COMPAT | ENT_HTML401 , 'UTF-8', true); 
$body = ob_get_clean(); 

ob_start(); 
?>
   </sher:xml>
     </sher:GetResponse>
   </soap:Body>
</soap:Envelope>
<?php 
$foot = ob_get_clean(); 
echo $header.$body.$foot; 

$debug_body = $header.$xml.$foot; 


//die('GetResponse'.__LINE__); 