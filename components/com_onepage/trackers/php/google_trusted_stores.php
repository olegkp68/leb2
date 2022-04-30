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
# $date must be in YYYY-MM-DD format
# You can pass in either an array of holidays in YYYYMMDD format
# OR a URL for a .ics file containing holidays
# this defaults to the UK government holiday data for England and Wales
if (!function_exists('x_addBusinessDays')) {
function x_addBusinessDays($date,$numDays=1,$holidays='') {
 #   if ($holidays==='') $holidays = 'https://www.gov.uk/bank-holidays/england-and-wales.ics';

    if (!is_array($holidays)) {
        $ch = curl_init($holidays);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $ics = curl_exec($ch);
        curl_close($ch);
        $ics = explode("\n",$ics);
        $ics = preg_grep('/^DTSTART;/',$ics);
        $holidays = preg_replace('/^DTSTART;VALUE=DATE:(\\d{4})(\\d{2})(\\d{2}).*/s','$1-$2-$3',$ics);
    }

    $addDay = 0;
    while ($numDays--) {
        while (true) {
            $addDay++;
            $newDate = date('Y-m-d', strtotime("$date +$addDay Days"));
            $newDayOfWeek = date('w', strtotime($newDate));
            if ( $newDayOfWeek>0 && $newDayOfWeek<6 && !in_array($newDate,$holidays)) break;
        }
    }

    return $newDate;
}
}

  $idformat = $this->idformat; 
 

 if (empty($this->order['details']['BT']->currency_code_3))
$this->order['details']['BT']->currency_code_3 = 'USD'; 
?>
<div id="gts-order" style="display:none;" translate="no">

<span id="gts-o-id"><?php echo $idformat; ?></span>
<span id="gts-o-domain"><?php echo $this->params->domain; ?></span>
<span id="gts-o-email"><?php echo $this->order['details']['BT']->email ?></span>
<span id="gts-o-country"><?php echo $this->order['details']['BT']->country_2_code; ?></span>
<span id="gts-o-currency"><?php echo $this->order['details']['BT']->currency_code_3; ?></span>
<span id="gts-o-total"><?php echo round($this->order['details']['BT']->order_total,2) ?></span>
<span id="gts-o-discounts"><?php echo round($this->order['details']['BT']->order_discount,2) ?></span>
<span id="gts-o-shipping-total"><?php echo round($this->order['details']['BT']->order_shipment,2) ?></span>
<span id="gts-o-tax-total"><?php echo round($this->order['details']['BT']->order_total/1.1*0.1,2) ?></span>
<span id="gts-o-est-ship-date"><?php echo x_addBusinessDays(date("Y-m-d"),1) ?></span>
<span id="gts-o-est-delivery-date"><?php echo x_addBusinessDays(date("Y-m-d"),5) ?></span>
<span id="gts-o-has-preorder">N</span>
<span id="gts-o-has-digital">N</span>

<?php foreach ($this->order['items'] as $i => $item) : ?>
<span class="gts-item">
	<span class="gts-i-name"><?php echo $item->order_item_name ?></span>
	<span class="gts-i-price"><?php echo round($item->product_final_price,2) ?></span>
	<span class="gts-i-quantity"><?php echo $item->product_quantity ?></span>
	<span class="gts-i-prodsearch-id"><?php echo $item->pid ?></span>
	<span class="gts-i-prodsearch-store-id"><?php echo $this->params->storeid; ?></span>
</span>
<?php endforeach; ?>

</div>