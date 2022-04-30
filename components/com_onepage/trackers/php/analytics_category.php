<?php
/*
*
* @copyright Copyright (C) 2007 - 2015 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );



$this->params->universalga = (int)$this->params->universalga;

if ($this->params->universalga === 2) {
	$this->isPureJavascript = true; 
	 $max = count($this->order['items']); 
	  $i = 0; 
	  $items = array(); 
	  foreach ($this->products as $key=>$order_item) 
	  { 
	  
	  $product_id = $order_item->pid; 
	    $i++; 
	  if (empty($order_item->category_name)) $order_item->category_name = ''; 
	  if (!empty($order_item->virtuemart_category_name)) $order_item->category_name = $order_item->virtuemart_category_name; 
	  
	  $json = array(); 
	    $json['id'] = (string)$product_id; 
	    $json['name'] = $order_item->order_item_name;
        $json['list_name'] = 'Product Impression'; 
		$json['brand'] = ''; 
		$json['category'] = $order_item->category_name;
        $json['variant'] = $order_item->order_item_sku;
		$json['list_position'] = $i; 
		$json['price'] = number_format($order_item->product_final_price, 2, '.', '');
        $json['quantity'] = (int)number_format($order_item->product_quantity , 0, '.', ''); 
		if (!empty($this->order['details']['BT']->coupon_code)) 
        $json['coupon'] = $this->order['details']['BT']->coupon_code;
	    $items[] = $json; 
	  
	  }
	
	?>
	<script>
	
	gtag('event', 'view_item_list', {
	  'currency': '<?php echo $this->escapeSingle($this->currency['currency_code_3']); ?>',	 
	  'items': <?php echo json_encode($items, JSON_PRETTY_PRINT); ?> });
	
	  
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Category view.'); 
	  }
	  
	</script>
	<?php
	
	
}