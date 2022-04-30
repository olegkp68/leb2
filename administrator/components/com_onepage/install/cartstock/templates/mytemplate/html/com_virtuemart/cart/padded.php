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


$dispatcher = JDispatcher::getInstance();
	  $countDownHtml = ''; 
	  $errors = array(); 
	  $dispatcher->trigger('plgVmOnDisplayMiniCart',array(&$countDownHtml, &$errors));
//var_dump($errors); 
$found = false; 
if($this->products){
	foreach($this->products as $product){
		if($product->quantity>0){
			$cart = VirtuemartCart::getCart(); 
			
			foreach ($cart->cartProductsData as $cart_key => $row) {
				if ($row['virtuemart_product_id'] === $product->virtuemart_product_id) {
					if ($row['quantity'] > 0) {
						$found = true; 
					}
				}
			}
			
			$qx = JRequest::getVar('quantity', 1); 
			if (is_array($qx)) $qx = (int)reset($qx); 
			$toShow = $product->quantity; 
			if (!empty($qx)) $toShow = $qx; 
			if ($found) {
				echo '<h4 class="prodduct-added">'.vmText::sprintf('COM_VIRTUEMART_CART_PRODUCT_ADDED',$product->product_name,$qx).'</h4>';
			}
			else {
				echo '<h4 class="prodduct-added">'.$product->product_name.'</h4>';
			}
			if(!empty($product->errorMsg)){
				echo '<div class="qerror">'.$product->errorMsg.'</div>';
			}
			elseif (!empty($errors)) {
				?><div class="qerror_wrap"><?php
				foreach ($errors as $err) {
					echo '<div class="qerror">'.$err.'</div>';
				}
				?></div><?php
				
			}
			
		} else {
			if(!empty($product->errorMsg)){
				echo '<div class="qerror">'.$product->errorMsg.'</div>';
			}
		}

	}
}
if (!$found) {
	?><div style="display:none;"><?php
}
echo $countDownHtml; 
if (!$found) {
	?></div><?php
}
//echo '<a class="continue_link" href="' . $this->continue_link . '" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
echo '<a class="continue_link" href="javascript:void(0);" onclick="jQuery.fancybox.close();" >' . vmText::_('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
//echo '<a class="showcart floatright" href="' . $this->cart_link . '">' . vmText::_('COM_VIRTUEMART_CART_SHOW') . '</a>';
echo '<a class="showcart pull-right" href="' . JRoute::_('index.php?option=com_virtuemart&view=cart') . '">' . vmText::_('COM_VIRTUEMART_CART_SHOW') . '</a>';
?><br style="clear:both">
<?php 
/*   Calling Joomla Module in Virtuemart product page */
$modules = JModuleHelper::getModules('add-to-cart-pop-up');
if(!empty($modules)){
	foreach ($modules as $module)
	{
	  echo JModuleHelper::renderModule($module);
	}
}

?>
