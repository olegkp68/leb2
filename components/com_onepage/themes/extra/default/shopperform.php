<?php 
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
defined('_JEXEC') or die('Restricted access');
?>
<div class="container">
<h3><?php echo vmText::_ ('COM_VIRTUEMART_CART_CHANGE_SHOPPER'); ?></h3>

<cfm action="#" method="post" form="outsideForm">
	<div class="row form-group" >
		 <div class="row">
			<div class="span3 col-sm">
				<input form="outsideForm" type="text" name="usersearch" size="20" maxlength="50" />
				<input form="outsideForm" type="submit" name="searchShopper" title="<?php echo vmText::_('COM_VIRTUEMART_SEARCH'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SEARCH'); ?>" class="button btn btn-secondary"  />
				<input form="outsideForm" type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid', 0); ?>" />
			</div>
			
			<div class="span6 col-sm">
				<?php 
				if (!class_exists ('VirtueMartModelUser')) {
					require(VMPATH_ADMIN . DS . 'models' . DS . 'user.php');
				}

				$currentUser = (int)$cart->user->virtuemart_user_id;
				echo JHtml::_('Select.genericlist', $userList, 'userID', 'class="vm-chzn-select" form="outsideForm" style="min-width: 200px"', 'id', 'displayedName', $currentUser,'userIDcart');
				
				
				
			
				?>
			</div>
			<div class="span3 col-sm">
				<input form="outsideForm" type="submit" name="changeShopper" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" class="button btn btn-primary"   />
				<input form="outsideForm" type="hidden" name="view" value="cart" />
				<input form="outsideForm" type="hidden" name="task" value="changeShopper" />
			</div>
		</div>
		<div class="row">
			
			<div class="span12">
				<?php if($adminID && $currentUser !== $adminID) { ?>
					<strong><?php echo vmText::_('COM_VIRTUEMART_CART_ACTIVE_ADMIN') .' '.JFactory::getUser($adminID)->name; ?></strong>
				<?php } ?>
				
			</div>
		</div>
	</div>
</cfm>
<br />
<?php
if (!empty($shopperGroupList)) {
?>
<h3><?php echo vmText::_ ('COM_VIRTUEMART_CART_CHANGE_SHOPPERGROUP'); ?></h3>

<cfm action="#" method="post" form="outsideForm2">
    <input type="hidden" form="outsideForm2" name="option" value="com_virtuemart" />
	<input type="hidden" form="outsideForm2" name="view" value="cart" />
	<input type="hidden" form="outsideForm2" name="controller" value="cart" />
	
	<div class="row">
		
			<div>
				<?php 
				
					echo $shopperGroupList;
				
				?>
			</div>
			<div>
				<input form="outsideForm2" type="submit" name="changeShopperGroup" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" class="button btn btn-primary" />
				<input form="outsideForm2" type="hidden" name="view" value="cart" />
				<input form="outsideForm2" type="hidden" name="task" value="changeShopperGroup" />
				<?php 
				
				?>
			</div>
			<?php if (JFactory::getSession()->get('tempShopperGroups', FALSE, 'vm')) { ?>
			<div>
				<input form="outsideForm2" type="reset" title="<?php echo vmText::_('COM_VIRTUEMART_RESET'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_RESET'); ?>" class="button btn btn-danger"   onclick="window.location.href='<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=cart&task=resetShopperGroup'); ?>'" />
			</div>
			<?php } ?>
			<input form="outsideForm2" type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid', 0); ?>" />
		
	</div>
</cfm>
<?php 
 } 
?>
</div>