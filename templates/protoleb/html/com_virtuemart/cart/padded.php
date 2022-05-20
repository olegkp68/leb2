<?php

/**
 *
 * Layout for the add to cart popup
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers
 *
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2013 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
 */



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

echo '<div class="padded-wrap">';

// echo '<a class="button-cart" href="' . $this->continue_link . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
// echo '<a class="button-cart" href="' . $this->cart_link . '">' . vmText::_('COM_VIRTUEMART_CART_SHOW') . '</a>';
if ($this->products) {
	foreach ($this->products as $product) {
		if ($product->quantity > 0) {

			// Получаем объект коннектора базы данных -----------
			$db = JFactory::getDBO();

			// Получаем объект запросов
			$query = $db->getQuery(true);

			// Запрос для получения file_url товара
			$query->select('file_url')
				->from($db->quoteName('#__virtuemart_medias'))
				->where($db->quoteName('virtuemart_media_id') . ' = ' . $product->virtuemart_media_id[0]);

			// Получение file_url товара
			$media_url = $db->setQuery($query)->loadResult();

			echo '<div class="padded-product-image"><img src="/' . $media_url . '"></div>';
			//----------------
			echo '<h6>' . vmText::sprintf('COM_VIRTUEMART_CART_PRODUCT_ADDED', $product->product_name, $product->quantity) . '</h6>' . '<br >';
		} else {
			if (!empty($product->errorMsg)) {
				echo '<div>' . $product->errorMsg . '</div>';
			}
		}
	}
}


/*echo '<a class="button-cart continue_link" href="' . $this->continue_link . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';


echo '<a class="button-cart" href="' . $this->cart_link . '">' . vmText::_('COM_VIRTUEMART_CART_SHOW') . '</a>'; */


if (VmConfig::get('popup_rel', 1)) {
	//VmConfig::$echoDebug=true;
	if ($this->products and is_array($this->products) and count($this->products) > 0) {

		$product = reset($this->products);

		$customFieldsModel = VmModel::getModel('customfields');
		$product->customfields = $customFieldsModel->getCustomEmbeddedProductCustomFields($product->allIds, 'R');

		$customFieldsModel->displayProductCustomfieldFE($product, $product->customfields);
		if (!empty($product->customfields)) {
?>
			<div class="product-related-products">
				<h4><?php echo vmText::_('COM_VIRTUEMART_RELATED_PRODUCTS'); ?></h4>
				<?php
			}
			foreach ($product->customfields as $rFields) {

				if (!empty($rFields->display)) {
				?><div class="product-field product-field-type-<?php echo $rFields->field_type ?>">
						<div class="product-field-display"><?php echo $rFields->display ?></div>
					</div>
			<?php }
			} ?>
			</div>
	<?php
	}
}

	?><br style="clear:both">

	</div>




	<script>
		jQuery(document).ready(function($) {
			$('.continue_link').click(function() {
				$.fancybox.close();
				return false;
			});
		});
	</script>