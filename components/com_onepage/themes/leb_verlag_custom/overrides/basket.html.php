<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC')) die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * This is the default Basket Template. Modify as you like.
 *
 * @version $Id: basket_b2c.html.php 1377 2008-04-19 17:54:45Z gregdev $
 * @package VirtueMart
 * @subpackage templates
 * @copyright Copyright (C) 2004-2005 Soeren Eberhardt. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
// remove image wrapper:
$doc = JFactory::getDocument();
if (!VmConfig::get('oncheckout_show_images', true)) ;
$doc->addStyleDeclaration(' div#basket_container div.op_basket_row div.op_col1 { border: none !important; } ');

?>
<div id="basket_container">
	<div class="inside">
		<div class="black-basket">
			<div class="cart-title-wrap">
				<h1><?php echo OPCLang::_('COM_VIRTUEMART_CART_TITLE'); ?></h1>
			</div>

		<?php if (!empty($continue_link)) { ?>
					<div id="continue_button">
						<input type="button" class="bandBoxStyle" onclick="location.href='javascript:history.go(-1)';" value="<?php echo OPCLang::_('COM_VIRTUEMART_CONTINUE_SHOPPING'); ?>"/>
					</div>
		<?php } ?>
			<div class="col-module_fix" style="float: left;">
				<div class="col-module_content" style="float: left; width: 100%;">

					<div class="op_basket_head">
						<div class="op_col1"></div>
						<div class="op_col2">&nbsp;</div>
						<div class="op_col3"><?php echo OPCLang::_('COM_VIRTUEMART_CART_NAME') ?></div>
						<div class="op_col4 sku_column"><?php echo OPCLang::_('COM_VIRTUEMART_CART_SKU') ?>&nbsp;</div>
						<div class="op_col5 unit_column"><?php echo OPCLang::_('COM_VIRTUEMART_CART_PRICE') ?></div>
						<div class="op_col6"><?php echo OPCLang::_('COM_VIRTUEMART_CART_QUANTITY') ?></div>

						<div class="op_col7"><?php echo OPCLang::_('COM_ONEPAGE_ORDER_SUMMARY') ?></div>
						<div class="op_col8"><?php echo vmText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></div>
					</div>
			<?php
			foreach ($product_rows as $product) {

				//$prow->prices = array_merge($prow->prices, $this->cart->cartPrices[ $product ]);

				/*
		DEVELOPER INFORMATION
		If you need any other specific information about the product being showed in the basket you can use the following variables in the theme:
		$product['info'] is an instance of VirtueMartModelProduct->getProduct($product['product_id'], $front=true, $calc=false, $onlypublished=false);

		To get instance of the single product information associated with the cart without any extra info, you can use:
		$product['product']

		All of the variables used in this file are defined in:
		\components\com_onepage\helpers\loader.php
		Please don't modify loader.php if you plan to update OPC on bug fix releases.

		Tested Example to show manufacturer info:


		if (!empty($product['info']->virtuemart_manufacturer_id))
		{
		echo $product['info']->mf_name;
		}
		*/

//	echo '<pre>';
//	echo var_dump($product['info']->allPrices[0]["taxAmount"]);
//	echo var_dump($product['product_quantity']*1);
//	echo var_dump($product['product_quantity'] * $product['info']->allPrices[0]["taxAmount"]);
//	echo '</pre>';

				?>
							<div class="op_basket_row">
								<div class="op_col1"><?php echo $this->op_show_image($product['product_full_image'], '', 40, 40, 'product'); ?>
									&nbsp;</div>
								<div class="op_col2_2"><?php echo $product['product_name'] . $product['product_attributes'] ?>
									&nbsp;</div>
								<div class="op_col4 sku_column"><?php echo $product['product_sku'] ?>&nbsp;</div>
								<div class="op_col5 unit_column"><?php echo $product['product_price'] ?>&nbsp;</div>
								<div class="op_col6">
										<?php echo $product['update_form'] ?>
										<?php echo $product['delete_form']; ?>&nbsp
								</div>
								<div class="op_col7"><?php echo $product['subtotal'] ?></div>
								<?php $taxSum = round($product['product_quantity'] * $product['info']->allPrices[0]["taxAmount"], 2, PHP_ROUND_HALF_EVEN) ;?>
								<div class="op_col8"><?php echo number_format($taxSum, 2, ',', ''); ?><span>€</span></div>
							</div>
			<?php } ?>
				</div>

				<div class="col-module_fix">
			<?php


			echo $op_coupon; // will show coupon if enabled from common/couponField.tpl.php with corrected width to size ?>

					<div id="subtotal_box" <?php if (empty($op_coupon)) echo 'style="width:98%;"'; ?>>
						<div id="subtotal_head" class="bandBoxStyle"><?php echo OPCLang::_('COM_ONEPAGE_ORDER_SUMMARY'); ?></div>
						<div id="subtotal_container">
							<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
				<?php if (!empty($shipping_inside_basket)) {
					?>
									<div class="op_basket_row">
										<div class="op_col1">&nbsp;</div>
										<div class="op_col2_3">
											<div><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING'); ?></div>
											<div id='shipping_inside_basket'><?php if (!empty($shipping_select)) echo $shipping_select; ?></div>
										</div>
										<div class="op_col5_3">
											<div id='shipping_inside_basket_cost'></div>
										</div>
									</div>

					<?php
				}
				if (!empty($payment_select)) {
					?>
									<div class="op_basket_row" style="display: none;">
										<div class="op_col1"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL'); ?></div>
										<div class="op_col2_3"><?php echo $payment_select; ?></div>
										<div class="op_col5_3">&nbsp;<span id='payment_inside_basket_cost'></span></div>
									</div>


					<?php
				}
				?>
							<div class="op_basket_row" <?php if (empty($discount_before)) echo ' style="display: none;" '; ?> id="tt_order_discount_before_div_basket">
								<div class="op_col1_4" align="right"><?php echo OPCLang::_('COM_ONEPAGE_OTHER_DISCOUNT') ?>:
								</div>
								<div class="op_col5_3" align="right" id="tt_order_discount_before_basket"><?php echo $coupon_display_before ?></div>
							</div>

							<div class="op_basket_row" id="tt_order_subtotal_div_basket">
								<div class="op_col1_4" id="tt_order_subtotal_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_CART_SUBTOTAL') ?>
									:
								</div>
								<div class="op_col5_3" id="tt_order_subtotal_basket"><?php echo $subtotal_display ?></div>
							</div>

							<div class="op_basket_row" style="display: none;" id="tt_order_payment_discount_before_div_basket">
								<div class="op_col1_4" id="tt_order_payment_discount_before_txt_basket">:
								</div>
								<div class="op_col5_3" id="tt_order_payment_discount_before_basket"></div>
							</div>


							<div class="op_basket_row" style="display: none;" id="tt_order_payment_discount_after_div_basket">
								<div class="op_col1_4" id="tt_order_payment_discount_after_txt_basket">:
								</div>
								<div class="op_col5_3" align="right" id="tt_order_payment_discount_after_basket"></div>
							</div>


							<div class="op_basket_row" id="tt_shipping_rate_div_basket" <?php if (($no_shipping == '1') || (!empty($shipping_inside_basket)) || (empty($order_shipping))) echo ' style="display:none;" '; ?>>
								<div class="op_col1_4" align="right"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING') ?>:
								</div>
								<div class="op_col5_3" align="right" id="tt_shipping_rate_basket"><?php echo $order_shipping; ?></div>
							</div>
							<div class="op_basket_row" <?php if (empty($discount_after)) echo ' style="display:none;" '; ?> id="tt_order_discount_after_div_basket">
								<div class="op_col1_4" id="tt_order_discount_after_txt_basket" align="right"><?php echo OPCLang::_('COM_VIRTUEMART_COUPON_DISCOUNT') ?>
									:
								</div>
								<div class="op_col5_3" align="right" id="tt_order_discount_after_basket"><?php echo $coupon_display ?></div>
							</div>

				<?php
				//				echo '<pre>';
				//				echo var_dump($product);
				//				echo '</pre>';

				$taxAll = '';
				$vatName = array();
				$results = array();

				foreach ($product_rows as $product) {
					$quantity = $product['product']->quantity;
					$prices = $product['product']->prices;
					//var_dump($prices); //break;
					if ($prices['taxAmount']) {
						if (!empty($prices['VatTax'])) {
							foreach ($prices['VatTax'] as $key => $value) {
								if (!isset($results[ $key ])) {
									$results[ $key ] = $value;
								}
								if (!isset($results[ $key ]['totals'])) {
									$results[ $key ]['totals'] = 0;
								}
								$results[ $key ]['totals'] += $prices['subtotal_tax_amount'];
							}
						}
					}
				}
				//				echo '<pre>';
				//				echo var_dump($results);
				//				echo '</pre>';
				?>

				<?php foreach ($results as $result) {
					//$taxName = $result[0];
					$taxAmountNum = $result['totals'];
					$taxAmount = number_format($taxAmountNum, 2, ',', '');
					?>
					<div class="op_basket_row">
						<div class="op_col1_4">
							<?php echo $result[0]; ?>
						</div>
						<div class="op_col5_3" align="right">
							<?php echo $taxAmount; ?>
							<span>€</span>
						</div>
					</div>
						<?php
				$taxAll += $result['totals'];
				}  //endforeach   ?>

							<div class="op_basket_row">
								<div class="op_col1_4" align="right"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?></div>
								<div class="op_col5_3" align="right">
										<?php echo number_format($taxAll, 2, ',', ''); ?>
									<span>€</span>
								</div>
							</div>

							<!--							<div class="op_basket_row totals dynamic_lines" id="tt_genericwrapper_basket" style="display: none;">-->
							<!--								<div class="op_col1_4 dynamic_col1">{dynamic_name}:</div>-->
							<!--								<div class="op_col5_3 dynamic_col2">{dynamic_value}</div>-->
							<!--							</div>-->

							<div class="op_basket_row">
								<div style="width: 100%; clear: both;">&nbsp;</div>
							</div>


							<div class="op_basket_row" style="border-top: 1px black solid;">
							</div>
							<div class="tax_wrapper" style="padding:0; margin:0;">
								<div class="op_basket_row" id="tt_tax_total_0_div_basket" style="<?php if (empty($tax_display)) echo 'display: none;'; ?>">
									<div class="op_col1_4" align="right" id="tt_tax_total_0_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>
										:
									</div>
									<div class="op_col5_3" align="right" id="tt_tax_total_0_basket"><?php echo $tax_display ?></div>
								</div>
								<div class="op_basket_row" id="tt_tax_total_1_div_basket" style="display:none;">
									<div class="op_col1_4" align="right" id="tt_tax_total_1_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>
										:
									</div>
									<div class="op_col5_3" align="right" id="tt_tax_total_1_basket"><?php echo $tax_display ?></div>
								</div>
								<div class="op_basket_row" id="tt_tax_total_2_div_basket" style="display:none;">
									<div class="op_col1_4" align="right" id="tt_tax_total_2_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>
										:
									</div>
									<div class="op_col5_3" align="right" id="tt_tax_total_2_basket"><?php echo $tax_display ?></div>
								</div>
								<div class="op_basket_row" id="tt_tax_total_3_div_basket" style="display:none;">
									<div class="op_col1_4" align="right" id="tt_tax_total_3_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>
										:
									</div>
									<div class="op_col5_3" align="right" id="tt_tax_total_3_basket"><?php echo $tax_display ?></div>
								</div>
								<div class="op_basket_row" id="tt_tax_total_4_div_basket" style="display:none;">
									<div class="op_col1_4" align="right" id="tt_tax_total_4_txt_basket"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX') ?>
										:
									</div>
									<div class="op_col5_3" align="right" id="tt_tax_total_4_basket"><?php echo $tax_display ?></div>
								</div>
							</div>
							<div class="op_basket_row">
								<div class="op_col1_4" align="right"><?php echo OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?>:</div>
								<div class="op_col5_3" align="right" id="tt_total_basket">
									<strong><?php echo $order_total_display; ?></strong></div>
							</div>
						</div>

					</div>


				</div>
			</div>
		</div>
	</div>
</div>
<div style="clear:both;"></div>