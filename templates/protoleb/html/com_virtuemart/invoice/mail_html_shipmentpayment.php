<?php
/**
 *
 * Layout for the shopper mail, when he confirmed an ordner
 *
 * The addresses are reachable with $this->BTaddress['fields'], take a look for an exampel at shopper_adresses.php
 *
 * With $this->cartData->paymentName or shipmentName, you get the name of the used paymentmethod/shippmentmethod
 *
 * In the array order you have details and items ($this->orderDetails['details']), the items gather the products, but that is done directly from the cart data
 *
 * $this->orderDetails['details'] contains the raw address data (use the formatted ones, like BTaddress['fields']). Interesting informatin here is,
 * order_number ($this->orderDetails['details']['BT']->order_number), order_pass, coupon_code, order_status, order_status_name,
 * user_currency_rate, created_on, customer_note, ip_address
 *
 * @package    VirtueMart
 * @subpackage Cart
 * @author Max Milbers, Valerie Isaksen
 *
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<table class="html-email" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th align="left">
            <strong><?php echo vmText::_('COM_VIRTUEMART_SHOPPER_SHIPMENT_FORM_LBL') ?></strong></th>
        <th align="left"><strong><?php echo vmText::_('COM_VIRTUEMART_CART_SELECTED_PAYMENT') ?></strong>
        </th>
    </tr>
    <tr>
        <td>
            <table class="html-email" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="left" class="pricePad"><?php echo $this->orderDetails['shipmentName'] ?></td>
                    <?php if (VmConfig::get('show_tax')) { ?>
                        <td align="left">
                            <span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment_tax, $this->user_currency_id) ?></span>
                        </td>
                    <?php } ?>
                    <td align="left"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment + $this->orderDetails['details']['BT']->order_shipment_tax, $this->user_currency_id); ?></td>
                </tr>
            </table>
        </td>
        <td>
            <table class="html-email" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="left" class="pricePad"><?php echo $this->orderDetails['paymentName'] ?></td>
                    <?php if (VmConfig::get('show_tax')) { ?>
                        <td align="left">
                            <span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment_tax, $this->user_currency_id) ?></span>
                        </td>
                    <?php } ?>
                    <td align="left"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment + $this->orderDetails['details']['BT']->order_payment_tax, $this->user_currency_id); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
