<?php
/**
 *
 * Order items view
 *
 * @package    VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk, Valerie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: details_items.php 8310 2014-09-21 17:51:47Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if ($this->format == 'pdf') {
	$widthTable = '100';
	$widthTitle = '27';
} else {
	$widthTable = '100';
	$widthTitle = '49';
}

//echo "<pre>";
//var_dump($this->orderdetails['details']['BT']->order_billTax);
//echo "</pre>";

?>
<table width="<?php echo $widthTable ?>%" cellspacing="0" cellpadding="0" border="0" class="order-list">
	<tr align="left" class="sectiontableheader">
		
		<th align="center"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
		
		<th align="left" colspan="2" width="<?php //echo $widthTitle ?>%"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
		
		<!--        <th align="center">--><?php //echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_STATUS') ?><!--</th>-->
		<th align="center"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
		<th align="right"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
	  <?php if (VmConfig::get('show_tax')) { ?>
				<th align="right"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX') ?></th>
	  <?php } ?>
		<th align="right"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
		<th align="right">
			<span><?php echo vmText::_('COM_VIRTUEMART_CART_PRICE_NETTO') ?></span>
		</th>
<!--		<th align="right">--><?php //echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?><!--</th>-->
		<th align="right"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_BRUTTO') ?></th>
	
	</tr>
	<?php
	foreach ($this->orderdetails['items'] as $item) {
		$qtt = $item->product_quantity;
		$_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $item->virtuemart_category_id . '&virtuemart_product_id=' . $item->virtuemart_product_id, FALSE);
		?>
			<tr valign="top">
				
				<td align="center">
			<?php echo $item->order_item_sku; ?>
				</td>
				
				<td align="left" colspan="2">
					<div><a href="<?php echo $_link; ?>"><?php echo $item->order_item_name; ?></a></div>
			<?php
			if (!class_exists('VirtueMartModelCustomfields')) require(VMPATH_ADMIN . DS . 'models' . DS . 'customfields.php');
			$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item, 'FE');
			echo $product_attribute;
			?>
				</td>
				
				<!--            <td align="center">-->
				<!--                --><?php //echo $this->orderstatuses[$item->order_status]; ?>
				<!--            </td>-->
				<td align="center">
			<?php echo $qtt; ?>
				</td>
				<td align="right" class="priceCol">
			<?php
			$item->product_discountedPriceWithoutTax = (float)$item->product_discountedPriceWithoutTax;
			if (!empty($item->product_priceWithoutTax) && $item->product_discountedPriceWithoutTax != $item->product_priceWithoutTax) {
				echo '<span class="line-through">' . $this->currency->priceDisplay($item->product_item_price, $this->currency) . '</span><br />';
				echo '<span >' . $this->currency->priceDisplay($item->product_discountedPriceWithoutTax, $this->currency) . '</span><br />';
			} else {
				echo '<span >' . $this->currency->priceDisplay($item->product_item_price, $this->currency) . '</span><br />';
			}
			?>
				</td>
		  
		  <?php if (VmConfig::get('show_tax')) { ?>
						<td align="right" class="priceCol"><?php echo "<span  class='priceColor2'>" . $this->currency->priceDisplay($item->product_tax, $this->currency, $qtt) . "</span>" ?></td>
		  <?php } ?>
				<td align="right" class="priceCol">
			<?php echo $this->currency->priceDisplay($item->product_subtotal_discount, $this->currency);  //No quantity is already stored with it ?>
				</td>
				<td align="right" class="priceCol">
			<?php echo $this->currency->priceDisplay($item->product_priceWithoutTax, $this->currency, $qtt); ?>
				</td>
				<td align="right" class="priceCol">
			<?php
			$item->product_basePriceWithTax = (float)$item->product_basePriceWithTax;
			$class = '';
			if (!empty($item->product_basePriceWithTax) && $item->product_basePriceWithTax != $item->product_final_price) {
				echo '<span class="line-through" >' . $this->currency->priceDisplay($item->product_basePriceWithTax, $this->currency, $qtt) . '</span><br />';
			} elseif (empty($item->product_basePriceWithTax) && $item->product_item_price != $item->product_final_price) {
				echo '<span class="line-through">' . $this->currency->priceDisplay($item->product_item_price, $this->currency, $qtt) . '</span><br />';
			}
			echo $this->currency->priceDisplay($item->product_subtotal_with_tax, $this->currency); //No quantity or you must use product_final_price ?>
				</td>
			</tr>
		
		<?php
	}
	?>
	<tr class="sectiontableentry1">
		<td colspan="5" align="right"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>
	  
	  <?php if (VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_tax, $this->currency) . "</span>" ?></td>
	  <?php } ?>
		<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_discountAmount, $this->currency) . "</span>" ?></td>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_subtotal, $this->currency); ?></td>
		<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_salesPrice, $this->currency); ?></td>
	</tr>
	<?php
	if ($this->orderdetails['details']['BT']->coupon_discount <> 0.00) {
		$coupon_code = $this->orderdetails['details']['BT']->coupon_code ? ' (' . $this->orderdetails['details']['BT']->coupon_code . ')' : '';
		?>
			<tr>
				<td align="right" class="pricePad" colspan="6"><?php echo vmText::_('COM_VIRTUEMART_COUPON_DISCOUNT') . $coupon_code ?></td>
		  
		  <?php if (VmConfig::get('show_tax')) { ?>
						<td align="right">&nbsp;</td>
		  <?php } ?>
				<td align="right">&nbsp;</td>
				<td align="right"><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->coupon_discount, $this->currency); ?></td>
			</tr>
	<?php } ?>
	
	
	<?php
	foreach ($this->orderdetails['calc_rules'] as $rule) {
		if ($rule->calc_kind == 'DBTaxRulesBill') { ?>
					<tr>
						<td colspan="6" align="right" class="pricePad"><?php echo $rule->calc_rule_name ?> </td>
			  
			  <?php if (VmConfig::get('show_tax')) { ?>
								<td align="right"></td>
			  <?php } ?>
						<td align="right"> <?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?></td>
						<td align="right"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?> </td>
					</tr>
			<?php
		} elseif ($rule->calc_kind == 'taxRulesBill') { ?>
					<tr>
						<td colspan="6" align="right" class="pricePad"><?php echo $rule->calc_rule_name ?> </td>
			  <?php if (VmConfig::get('show_tax')) { ?>
								<td align="right"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?> </td>
			  <?php } ?>
						<td align="right"><?php ?> </td>
						<td align="right"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?> </td>
					</tr>
			<?php
		} elseif ($rule->calc_kind == 'DATaxRulesBill') { ?>
					<tr>
						<td colspan="6" align="right" class="pricePad"><?php echo $rule->calc_rule_name ?> </td>
			  <?php if (VmConfig::get('show_tax')) { ?>
								<td align="right"></td>
			  <?php } ?>
						<td align="right"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?> </td>
						<td align="right"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->currency); ?> </td>
					</tr>
			
			<?php
		}
	}
	?>
	
	<tr>
		<td align="right" class="pricePad" colspan="5">
			<strong><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>
	  
	  <?php if (VmConfig::get('show_tax')) { ?>
				<td align="right">
					<span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_billTaxAmount, $this->currency); ?></span>
				</td>
	  <?php } ?>
		<td align="right">
			<span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_billDiscountAmount, $this->currency); ?></span>
		</td>
		<td align="right">
			<strong><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_subtotal, $this->currency); ?></strong>
		</td>
		<td align="right">
			<strong><?php echo $this->currency->priceDisplay($this->orderdetails['details']['BT']->order_total, $this->currency); ?></strong>
		</td>
	</tr>
	
	<?php
	
	
	if ($this->orderdetails['details']['BT']->order_billTax) {
		$billTaxes = $this->orderdetails['details']['BT']->order_billTax;
		$billTaxes = json_decode($billTaxes); ?>
			<tr>
				<td colspan="5" align="right" class="pricePad"><?php echo vmText::_('COM_VIRTUEMART_TOTAL_INCL_TAX') ?> </td>
				<td colspan="3"></td>
			</tr>
		<?php foreach ($billTaxes as $rule) { ?>
				<tr>
					<td colspan="5" align="right" class="priceTaxAll"><?php echo $rule->calc_name; ?></td>
					<td align="right"><?php echo number_format($rule->result, 2, ',', ''); ?>
						<span>???</span>
					</td>
					<td align="right"></td>
					<td align="right"></td>
				</tr>
		<?php } ?>
	<?php } ?>

</table>
<br>
<table width="<?php echo $widthTable ?>%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<th><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
	</tr>
	<tr>
		<td valign="top" align="left" width="50%"><?php echo $this->orderdetails['details']['BT']->customer_note; ?></td>
	</tr>
</table>
