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

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="html-email">
    <tr>
        <th align="left" width="50%"><strong><?php echo vmText::_('COM_VIRTUEMART_ORDER_INFO'); ?></strong></th>
        <th width="50%"></th>
    </tr>
    <tr>
        <td width="50%">
            <?php echo vmText::_('COM_VIRTUEMART_MAIL_SHOPPER_YOUR_ORDER'); ?>
        </td>
        <td width="50%">
            <strong><?php echo substr($this->orderDetails['details']['BT']->order_number, 4); ?></strong>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <?php echo vmText::_('COM_VIRTUEMART_ORDER_CDATE'); ?>
        </td>
        <td width="50%">
            <strong><?php echo vmJsApi::date($this->invoiceDate, 'LC4', true); ?></strong>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <p><?php echo vmText::_('COM_VIRTUEMART_MAIL_ORDER_STATUS_TEXT'); ?></p>
        </td>
        <td width="50%">
            <strong><?php echo vmText::sprintf('COM_VIRTUEMART_MAIL_ORDER_STATUS', vmText::_($this->orderDetails['details']['BT']->order_status_name)); ?></strong>
        </td>
    </tr>

<!--    <tr>-->
<!--        <td colspan="2">-->
<!--            --><?php //echo vmText::_('COM_VIRTUEMART_MAIL_SHOPPER_YOUR_PASSWORD'); ?>
<!--            <strong>--><?php //echo $this->orderDetails['details']['BT']->order_pass ?><!--</strong>-->
<!--        </td>-->
<!--    </tr>-->

<!--    <tr>-->
<!--        <td width="50%">-->
<!--            <p>--><?php //echo vmText::sprintf('COM_VIRTUEMART_MAIL_SHOPPER_TOTAL_ORDER', $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total, $this->user_currency_id)); ?><!--</p>-->
<!--        </td>-->
<!--        <td width="50%"></td>-->
<!--    </tr>-->

    <tr>
        <td width="50%">
            <p>
                <a class="default1" title="<?php echo $this->vendor->vendor_store_name ?>" href="<?php echo JURI::root() . 'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $this->orderDetails['details']['BT']->order_number . '&order_pass=' . $this->orderDetails['details']['BT']->order_pass; ?>"><?php echo vmText::_('COM_VIRTUEMART_MAIL_SHOPPER_YOUR_ORDER_LINK'); ?></a>
            </p>
        </td>
        <td width="50%"></td>
    </tr>

    <?php $nb = count($this->orderDetails['history']);
    if ($this->orderDetails['history'][$nb - 1]->customer_notified && !(empty($this->orderDetails['history'][$nb - 1]->comments))) { ?>
        <tr>
            <td width="50%">
                <?php echo nl2br($this->orderDetails['history'][$nb - 1]->comments); ?>
            </td>
            <td width="50%"></td>
        </tr>
    <?php } ?>
</table>

<!-- General comment for all pieces -->
<?php //if(!empty($this->orderDetails['details']['BT']->customer_note)){ ?>
<!--    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="html-email">-->
<!--        <tr>-->
<!--            <th align="left">-->
<!--                <strong>--><?php //echo vmText::_('COM_VIRTUEMART_RECOMMEND_COMMENT'); ?><!--</strong>-->
<!--            </th>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td valign="top" align="left">-->
<!--                <p>-->
<!--                    --><?php //echo $this->orderDetails['details']['BT']->customer_note; ?>
<!--                </p>-->
<!--            </td>-->
<!--        </tr>-->
<!--    </table>-->
<?php //} ?>
