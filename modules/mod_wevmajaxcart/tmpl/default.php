<?php
/**
 * @version     1.0.0
 * @package     mod_wevmajaxcart
 * @copyright   WEB EXPERT SERVICES LTD / Web-expert.gr - Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Stergios Zgouletas <info@web-expert.gr> - http://web-expert.gr
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::removeJScript("/modules/mod_virtuemart_cart/assets/js/update_cart.js");
?>
<div class="vmCartModuleWeAJAX vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule<?php echo $params->get('moduleid_sfx'); ?>">
<?php
if ($show_product_list) {
	?>
	<div class="hiddencontainer" style=" display: none; ">
		<div class="vmcontainer">
			<div class="product_row">
				<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>

			<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
				<div class="subtotal_with_tax" style="float: right;"></div>
			<?php } ?>
			<div class="customProductData"></div><br>
			</div>
		</div>
	</div>
	<div class="vm_cart_products">
		<div class="vmcontainer">

		<?php
			foreach ($data->products as $product){
				?><div class="product_row">
					<span class="quantity"><?php echo  $product['quantity'] ?></span>&nbsp;x&nbsp;<span class="product_name"><?php echo  $product['product_name'] ?></span>
				<?php if ($show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
				  <div class="subtotal_with_tax" style="float: right;"><?php echo $product['subtotal_with_tax'] ?></div>
				<?php } ?>
				<?php if ( !empty($product['customProductData']) ) { ?>
					<div class="customProductData"><?php echo $product['customProductData'] ?></div><br>

				<?php } ?>

			</div>
		<?php }
		?>
		</div>
	</div>
<?php } ?>

	<div class="total" style="float: right;">
		<?php if ($data->totalProduct and $show_price and $currencyDisplay->_priceConfig['salesPrice'][0]) { ?>
		<?php echo $data->billTotal; ?>
		<?php } ?>
	</div>

	<div class="total_products"><?php echo  $data->totalProductTxt ?></div>
	<div class="show_cart">
		<?php if ($data->totalProduct) echo  $data->cart_show; ?>
	</div>
	<div style="clear:both;"></div>
	<?php
	$view = vRequest::getCmd('view');
	if($view!='cart' and $view!='user'){
		?><div class="payments-signin-button" ></div><?php
	}
	?>
	<noscript>
	<?php echo JText::_('MOD_WEVMAJAXCART_JAVASCRIPT') ?>
	</noscript>
</div>