<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
?><form action="<?php echo $action_url ?>" method="post" style="display: inline;" id="updateQForm" name="updateQForm">
				<input type="hidden" name="option" value="com_virtuemart" />
				
				<select name="quantity" class="quantity_select " onchange="return updateQForm.submit();">
				<?php
				//you can print your own options, or use the pre-generated: for ($i=$step; $i<=$max; $i=$i+$step ) 
				echo $options; 
				
				?>
				</select>
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_virtuemart_product_id" value="<?php echo $product->cart_item_id; ?>" />
				
				<a class="deletebtn" title="<?php echo OPCLang::_('COM_VIRTUEMART_CART_DELETE'); 
?>" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&task=delete&cart_virtuemart_product_id='.$product->cart_item_id, true, $useSSL  ); 
?>">&nbsp;</a>
			  </form>