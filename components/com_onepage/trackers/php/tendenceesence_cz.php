<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
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
$total = 0; 
foreach ($this->order['items'] as $key=>$order_item) {
	$price = (float)$order_item->product_priceWithoutTax; 
	$q = (float)$order_item->product_quantity; 
	$total += ($price * $q);
	
}

$total = number_format($total, 2, '.', ''); 
$idformat = $this->idformat;
 
 ?><iframe src="http://partneri.tendenceesence.cz/konverze/aid/<?php echo $this->params->aid; ?>/kid/<?php echo $this->params->kid; ?>/cena/<?php echo $total; ?>/transakce/<?php echo $idformat; ?>/" width="1" height="1" frameborder="0" scrolling="no"></iframe>
<script>
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: tendenceesence.cz tracking sent'); 
	  }
	  </script>