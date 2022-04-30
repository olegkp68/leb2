<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

/**
*
* @version $Id: customer_info.tpl.php 1439 2008-06-25 19:08:23Z soeren_nb $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2007-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
// this template shows the customer BT address
// input parameter is $BTaddress

?>
<!-- Customer Information --> 
    <div style="width:100%" class="BTaddress">
	<?php  
	foreach ($BTaddress as $item)
	{
	
	if (!empty($item['value']))
	{
	?>
         <div style="position:relative;float:left;width:100%;">
           <div class="titleForm" ><?php echo $item['title'] ?> </div>
           <div class="valueForm">
           <?php

				// prior 2.0.116: echo $this->escape($item['value'])
				echo $item['value']; 
           ?>
           </div>
        </div>
	<?php
	}
	}
	?>
       
       
        <div>
        <div style="clear:both; width:100%;text-align:center;"><a href="<?php echo $edit_link; ?>">
            (<?php echo OPCLang::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL') ?>)</a>
            </div>
        </div>
    </div>
    <!-- customer information ends -->
