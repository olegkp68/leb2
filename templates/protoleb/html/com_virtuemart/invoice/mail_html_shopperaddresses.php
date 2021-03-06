<?php
/**
 *
 * Layout for the order email
 * shows the chosen adresses of the shopper
 * taken from the stored order
 *
 * @package	VirtueMart
 * @subpackage Order
 * @author Max Milbers,   Valerie Isaksen
 *
 * @link https://virtuemart.net
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
<table class="html-email" cellspacing="0" cellpadding="5" border="0" width="100%" style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0 auto;">
	<tr>
	<th width="50%" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;">
	    <?php echo vmText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL'); ?>
	</th>
	
    </tr>
    <tr>
	<td valign="top" width="50%" style="border: 1px solid #CCCCCC;">
		
	    <?php

	    foreach ($this->userfields['fields'] as $field) {
		if (!empty($field['value'])and $field['name'] != "activity" ) {
			?><!-- span class="titles"><?php echo $field['title'] ?></span -->
	    	    <span class="values vm2<?php echo '-' . $field['name'] ?>" ><?php echo $field['value'] ?></span>
			<?php if ($field['name'] != 'title' and $field['name'] != 'first_name' and $field['name'] != 'middle_name' and $field['name'] != 'zip') { ?>
			    <br class="clear" />
			    <?php
			}
		    }
		 
	    }
	    ?>

	</td>
	
    </tr>
		<tr><td valign="top" width="50%" style="padding:5px"></td><td valign="top" width="50%" style="padding:5px"></td></tr>
</table>

