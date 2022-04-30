<?php

class sherpaAvailabilityStockHelper {
	
	
	
	public static function updateAvailability($sku, $nocache=false) {
	 ini_set('memory_limit','8G');
	  
	 require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
	 return sherpaHelper::onlineStockUpdateAvailability($sku, $nocache); 
	
	
	}
	
	
	
	public static function getAvailability($sku) {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'sherpa'.DIRECTORY_SEPARATOR.'helper.php'); 
		$product = sherpaHelper::getProductBySku($sku); 
		
		
		if (empty($product)) return ''; 
		ob_start(); 
		if (!empty($product->product_availability) AND $product->product_in_stock > 0) 
			{
			?>
			<div class="availability2">			
				<?php 
					if ($product->product_availability == "14d.gif"): echo "(" . vmText::_('COM_VIRTUEMART_PRODUCT_LEVERING_10_15') . ")";
					elseif ($product->product_availability == "7d.gif"): echo "(" . vmText::_('COM_VIRTUEMART_PRODUCT_LEVERING_5_10') . ")";
					elseif ($product->product_availability == "3-5d.gif"): echo "(" . vmText::_('COM_VIRTUEMART_PRODUCT_LEVERING_3_5') . ")";
					elseif ($product->product_availability == "2-3d.gif"): echo "(" . vmText::_('COM_VIRTUEMART_PRODUCT_LEVERING_2_3') . ")";
					endif;
				?>
			</div>
			<?php
			}
			?>
			
			<?php
			if (!empty($product->product_availability) AND $product->product_in_stock == 0) {
			?>
			<div class="availability2">			
				<?php 
					if ($product->product_availability == "not_available.gif"): echo "(" . vmText::_('COM_VIRTUEMART_PRODUCT_LEVERING_NOT_AVAILABLE') . ")";
					endif;
				?>
			</div>
			<?php
			}
			$html = ob_get_clean(); 
			return $html; 
	}
	public static function getAvailablityHtml($sku) {
		
		
	}
}