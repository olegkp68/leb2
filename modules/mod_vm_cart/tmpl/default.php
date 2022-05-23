<?php // no direct access
defined('_JEXEC') or die('Restricted access');

//dump ($cart,'mod cart');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" 
?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule">
	<a href="index.php?option=com_virtuemart&view=cart" rel="nofollow">

		<?php
		/*
        Изображение корзины
        */
		$imgcart = $params->get('imgcart', 0);

		switch ($imgcart) {
			case 0:
				$imgcartModule = 'cart1.svg';
				break;
			case 1:
				$imgcartModule = 'cart2.svg';
				break;
			case 2:
				$imgcartModule = 'cart3.svg';
				break;
			case 3:
				$imgcartModule = 'cart4.svg';
				break;
			case 4:
				$imgcartModule = 'cart5.svg';
				break;
			case 5:
				$imgcartModule = 'cart6.svg';
				break;
			case 6:
				$imgcartModule = 'cart7.svg';
				break;
			case 7:
				$imgcartModule = 'cart8.svg';
				break;
			case 8:
				$imgcartModule = 'cart9.svg';
				break;
			case 9:
				$imgcartModule = 'cart10.svg';
				break;
		}
		require(JPATH_ROOT . DS . 'modules' . DS . 'mod_vm_cart' . DS . 'assets' . DS . 'img' . DS . $imgcartModule);
		?>
		<div class="cart_top">
			<div class="total_products">
				<?php
				echo  $data->totalProduct;
				?>
			</div>
			<div class="total">
				<?php if ($data->totalProduct > 0) {
					echo $data->billTotal;
				} ?>
				<?php
				if ($data->totalProduct < 1) {
					echo '<span class="cart_empty">' . vmText::_('MOD_VM_CART_NO_PRODUCT') . '</span>';
				}
				?>
			</div>
		</div>
	</a>
	<?php
	if ($show_product_list) {
	?>
		<div class="wrap-cart-content">
			<div class="cart_content">

				<div id="hiddencontainer" style=" display: none; ">
					<div class="vmcontainer">
						<div class="product_row">
							<div class="block-left">
								<?php if ($img) : ?>
									<span class="image"></span>
								<?php endif; ?>
								<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
							</div>
							<!-- <div class="subtotal_with_tax block-right"></div> -->
							<div class="customProductData"></div>
						</div>
					</div>
				</div>
				<div class="vm_cart_products">
					<div class="vmcontainer">
						<?php
						foreach ($data->products as $product) {
						?><div class="product_row">
								<div class="block-left">
									<?php if ($img) : ?>
										<span class="image"><?php echo $product['image']; ?></span>
									<?php endif; ?>
									<span class="quantity"><?php echo  $product['quantity'] ?></span>&nbsp;x&nbsp;<span class="product_name"><?php echo  $product['product_name'] ?></span>
									<?php if (!empty($product['customProductData'])) { ?>
										<div class="customProductData"><?php echo $product['customProductData'] ?></div>
									<?php } ?>
								</div>
								<div class="subtotal_with_tax block-right">
									<?php echo $product['subtotal_with_tax'] ?>
								</div>
							</div>
						<?php }
						?>
					</div>
				</div>


				<div class="total">
					<?php if ($data->totalProduct > 0) {
						echo $data->billTotal;
					} ?>
				</div>

				<div class="cart_info">
					<?php
					if ($data->totalProduct < 1) {
						echo vmText::_('MOD_VM_CART_EMPTY');
					}
					?>
				</div>
				<div class="show_cart">
					<?php if ($data->totalProduct) echo  $data->cart_show; ?>
				</div>
				<div style="clear:both;"></div>
				<div class="payments_signin_button"></div>
				<noscript>
					<?php echo vmText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
				</noscript>
			</div>
		</div>
	<?php } ?>
</div>
<script>
	jQuery(document).ready(function($) {
		$('#vmCartModule').hover(
			function() {
				$('.wrap-cart-content').stop().addClass('open');
			},
			function() {
				$('.wrap-cart-content').stop().removeClass('open');
			}
		)
	});
</script>