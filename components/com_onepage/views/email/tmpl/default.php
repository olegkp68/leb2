<?php
/**
 * @version		$Id: view.html.php 21705 2011-06-28 21:19:50Z RuposTel.com $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title></title>
        <style></style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="font-family: Trebuchet MS,Verdana,Tahoma,Arial">
            <tr>
                <td align="center" valign="top">
                    <table border="0" cellpadding="20" cellspacing="0" width="800" id="emailContainer">
                        <tr>
                            <td align="center" valign="top">
							
							<table border="0" cellpadding="20" cellspacing="0" width="100%" id="emailHeader">
                            <tr>
                                <td align="center" valign="top">
							<?php echo $this->top_article; ?></td>
								</td>
							</tr>
							</table>
						</tr>
						<tr>
						<td align="center" valign="top">
						<table border="0" cellpadding="10" cellspacing="0" width="100%" id="emailBody">
                            
                               

<?php 

//echo $this->order['details']['BT']->virtuemart_order_id."<br />"; 
if (!class_exists ('shopFunctionsF'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
shopFunctionsF::loadOrderLanguages(VmConfig::$jDefLangTag);
$orderDetails = $this->order;
$virtuemart_vendor_id = $orderDetails['details']['BT']->virtuemart_vendor_id;
$vendorModel = VmModel::getModel('vendor');
$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
$this->subject = '['.$orderDetails['details']['BT']->order_number.'] Confirmed order at '.$vendor->vendor_store_name;

$toShow = array(); 
foreach ($this->order['items'] as $ind => $item) {
	$toShowRow = array(); 
	$toShowRow['COM_VIRTUEMART_ORDER_PRINT_SKU'] = $item->order_item_sku; 
	$toShowRow['COM_VIRTUEMART_PRODUCT_NAME_TITLE'] = $item->order_item_name; 
	
		
	$toShowRow['COM_VIRTUEMART_ORDER_PRINT_QTY'] = $item->product_quantity;
	$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item,'FE');
	$product_attribute = str_replace('class="price"', ' style="display:none;" class="price" ', $product_attribute); 
	$search = '<span class="plugin_title">Size: </span><span class="attrib_title"></span><span  style="display:none;" class="price" > $0.00</span>'; 
	$product_attribute = str_replace($search, '', $product_attribute); 
	$search = '</span><br /> <span class="product-field-type-S">'; 
	$product_attribute = str_replace($search, '</span><span class="product-field-type-S">', $product_attribute);  
	
	/* stAno - atributs will be hidden for now
	
	$toShowRow['EXTRA_COM_VIRTUEMART_PRODUCT_NAME_TITLE'] = array(); 
	$toShowRow['EXTRA_COM_VIRTUEMART_PRODUCT_NAME_TITLE'][] = $product_attribute; 
	
	*/
	//$toShowRow['COM_VIRTUEMART_ORDER_PRINT_PRICE'] = $item->price;
	//$toShowRow['COM_VIRTUEMART_ORDER_PRINT_TOTAL'] = $this->currency->priceDisplay(  $item->product_subtotal_with_tax ,$this->user_currency_id);
	
	$toShow[$ind] = $toShowRow;
}


$first = true; 
foreach ($toShow as $ind2 => $row) {
	$item = $this->order['items'][$ind2]; 
	if ($first) {
	  $first = false; 
	  ?><tr><?php
	  foreach ($row as $title=>$val) {
		  if (strpos($title, 'EXTRA_') === 0) continue; 
		  ?><th align="left" valign="top"><?php echo JText::_($title); ?></th><?php
	  }
	  ?></tr><?php
	}
	
	?><tr><?php
	  $colspan = 0; 
	  foreach ($row as $title=>$val) {
		  if (strpos($title, 'EXTRA_') === 0) continue; 
		  $colspan++; 
		  ?><td align="left" valign="top" style="<?php 
		    switch($title) {
				case 'COM_VIRTUEMART_ORDER_PRINT_TOTAL':
				case 'COM_VIRTUEMART_ORDER_PRINT_PRICE':
						echo ' white-space: nowrap; '; 
						break; 
				default: 
					break; 
			}
		  ?> padding-bottom: 0;"><?php echo $val; 
		  if (!empty($row['EXTRA_'.$title])) {
			  if (is_array($row['EXTRA_'.$title])) {
				  foreach ($row['EXTRA_'.$title] as $extra) {
					  ?><br/><?php echo $extra; 
				  }
			  }
			  else {
			    ?><br/><?php echo $row['EXTRA_'.$title]; 
			  }
		  }
		  
		  ?></td><?php
	  }
	  ?></tr>
	  <tr>
	  <td style="padding-top: 0;" align="left" valign="top"></td>
	  <td style="padding-top: 0;" align="left" valign="top" colspan="<?php echo $colspan - 1; ?>">
	  <?php 
	  
	  
		if (!empty($item->mylink)) { 
		  ?><a href="<?php echo $item->mylink; ?>" target="_blank"><?php echo $item->mylink; ?></a><?php
		}
		?>
	  </td></tr>
	  
	  <?php
	  
	  
	
}

?>								</td>
                            </tr>
                        </table>

				</td>
				</tr>
  <tr>
  <td>
  
  <table border="0" cellpadding="20" cellspacing="0" width="100%" id="emailFooter">
   <tr>
                                <td align="center" valign="top">
  <?php
echo $this->bottom_article; 
?>						</td>
	</tr>
   </table>

</td>
 </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>