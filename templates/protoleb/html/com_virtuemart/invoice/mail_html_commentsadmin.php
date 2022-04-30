<?php
/**
 *
 * Layout for the shopping cart, look in mailshopper for more details
 *
 * @package    VirtueMart
 * @subpackage Order
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
        <th align="left">
            <?php echo vmText::_('COM_VIRTUEMART_CART_MAIL_VENDOR_SHOPPER_QUESTION_COMMENT'); ?>
        </th>
    </tr>
    <tr>
        <td>
            <?php
            if (!empty($this->orderDetails['details']['BT']->customer_note)) {
                echo  vmText::sprintf('COM_VIRTUEMART_CART_MAIL_VENDOR_SHOPPER_QUESTION', $this->orderDetails['details']['BT']->customer_note) . '<br />';
            }
            ?>
        </td>
    </tr>
</table>
