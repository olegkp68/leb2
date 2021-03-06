<?php
/**
 *
 * Show the product details page
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 9185 2016-02-25 13:51:01Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$user = JFactory::getUser();

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	
	return;
}

echo shopFunctionsF::renderVmSubLayout('askrecomjs', array('product' => $this->product));


if (vRequest::getInt('print', false)){ ?>
<body onload="javascript:print();">
<?php } ?>

<div class="productdetails-view productdetails">
	
	<div class="row">
		<div class="vm-col-1">
		<?php // Back To Category Button
		if ($this->product->virtuemart_category_id) {
			$catURL = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
			$categoryName = vmText::_($this->product->category_name);
		} else {
			$catURL = JRoute::_('index.php?option=com_virtuemart');
			$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME');
		}
		?>
			
		</div>
	</div>
	<div class="row">
	  <?php echo $this->edit_link; ?>
	  <?php echo $this->product->event->afterDisplayTitle ?>
	  <?php
	  // PDF - Print - Email Icon
	  if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
		  ?>
				<div class="icons">
			<?php
			
			$link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;
			
			echo $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);
			//echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon');
			echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon', false, true, false, 'class="printModal"');
			$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component';
			echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false, true, false, 'class="recommened-to-friend"');
			?>
					<div class="clear"></div>
				</div>
	  <?php } // PDF - Print - Email Icon END ?>
		
		
		<div class="vm-col-1">
		<?php echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $this->product, 'position' => 'ontop')); ?>
		</div>
	
	</div>
	
	<div class="row">
		<div class="vm-col-1">
		
		 <?php echo $this->product->event->beforeDisplayContent; // event onContentBeforeDisplay?>
			
			<div class="vm-product-container">
			
			

				<div class="vm-product-media-container">
			<?php echo $this->loadTemplate('images'); ?>
			<?php $count_images = count($this->product->images);
			if ($count_images > 1) echo $this->loadTemplate('images_additional'); ?>
				</div>
				
				<div class="vm-product-details-container">
				
				<div class="back-to-category">
				<a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo vmText::sprintf('COM_VIRTUEMART_CATEGORY_BACK_TO', $categoryName) ?></a>
			</div>
				
				
					<div class="vm-col-1">
			  <?php // Product Title   ?>
						<h1 itemprop="name"><?php echo $this->product->product_name ?></h1>
						<h2>??????????????: <span style="color:#C4013D"><?php echo $this->product->product_sku ?></span></h2>
			  <?php // Product Title END   ?>
					</div>
					
					<div class="vm-col-1">
			  <?php if ($this->product->prices['salesPrice'] <= 0 and VmConfig::get('askprice', 1) and isset($this->product->images[0]) and !$this->product->images[0]->file_is_downloadable) { ?>
				  <?php $regurl = JRoute::_(JURI::root() . 'index.php?option=com_virtuemart&view=user&layout=edit'); ?>
								<div class="ask-a-question">
									<a class="leb-btn ask-price-btn" href="<?php echo $regurl ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
								</div>
								<div class="clear"></div>
			  <?php } else { ?>
				  
				  <?php if (!$user->guest) { ?>
					  
					  <?php echo shopFunctionsF::renderVmSubLayout('prices', array('product' => $this->product, 'currency' => $this->currency)); ?>
									<div class="clear"></div>
				  
				  <?php } else { ?>
					  <?php $regurl = JRoute::_(JURI::root() . 'index.php?option=com_virtuemart&view=user&layout=edit'); ?>
									<div class="ask-a-question">
										<a class="leb-btn ask-price-btn" href="<?php echo $regurl ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
									</div>
									<div class="clear"></div>
				  <?php } ?>
			  
			  <?php } ?>
					</div>
					
					<div class="vm-col-1">
						<div class="product-description">
				<?php /** @todo Test if content plugins modify the product description */ ?>
							<!--                            <span class="title">-->
				<?php //echo vmText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE') ?><!--</span>-->
							<hr>
				<?php echo $this->product->product_desc; ?>
						</div>
					</div>
					<div class="spacer-buy-area">
			  <?php
			  echo shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $this->showRating, 'product' => $this->product));
			  
			  //                        if (is_array($this->productDisplayShipments)) {
			  //                            foreach ($this->productDisplayShipments as $productDisplayShipment) {
			  //                                echo $productDisplayShipment . '<br />';
			  //                            }
			  //                        }
			  //                        if (is_array($this->productDisplayPayments)) {
			  //                            foreach ($this->productDisplayPayments as $productDisplayPayment) {
			  //                                echo $productDisplayPayment . '<br />';
			  //                            }
			  //                        }
			  
			  //In case you are not happy using everywhere the same price display fromat, just create your own layout
			  //in override /html/fields and use as first parameter the name of your file
			  //echo shopFunctionsF::renderVmSubLayout('prices', array('product' => $this->product, 'currency' => $this->currency)); ?>
			  <?php
			  
			  //echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $this->product));
			  
			  echo shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $this->product));
			  
			  // Manufacturer of the Product
			  if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) {
				  echo $this->loadTemplate('manufacturer');
			  }
			  ?>
					</div>
			
			<?php if (!$user->guest) { ?>
							<div class="vm-col-1">
								<br>
				  <?php
				  echo '<script type="text/javascript" src="' . JURI::base() . 'modules/mod_virtuemart_product_komment/js/form-submit.js"></script>';
				  echo '<script type="text/javascript" src="' . JURI::base() . 'modules/mod_virtuemart_product_komment/js/ajax.js"></script>';
				  
				  $user_id = $user->id;
				  $prod_id = $this->product->virtuemart_product_id;
				  
				  //                        echo '<pre>';
				  //                        echo var_dump($this->product);
				  //                        echo '</pre>';
				  
				  //Zugangsdaten f??r MySQL
				  $db =& JFactory::getDBO();
				  //Datenbank als Standart definieren
				  $select_satz = "select * from leb_vm_komment where user_id = '$user_id' and prod_id = '$prod_id' order by id asc";
				  $db->setQuery($select_satz);
				  if ($row = $db->loadRow()) {
					  $komment = $row[4];
				  }
				  ?>
								<fieldset>
									<legend>&nbsp;???????????????????????????? ??????????????????&nbsp;</legend>
									<form action="someplace.html" method="post" name="myForm">
										<div id="formResponse">
											<table>
												<tbody>
												<tr>
													<td align="center">
														<textarea class="textInput" cols="32" name="komment" onchange="formObj.submit()"><?php echo $komment; ?></textarea>
													</td>
												</tr>
												<tr>
													<td align="center">
														<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
														<input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>">
														<input type="hidden" name="product_name" value="<?php echo $this->product->product_name ?>">
													</td>
												</tr>
												<tr>
													<td>
														<div id="formResponse2"></div>
													</td>
												</tr>
												</tbody>
											</table>
										</div>
									</form>
								</fieldset>
				  <?php
				  echo '<script type="text/javascript">';
				  echo '  var formObj = new DHTMLSuite.form({ formRef:"myForm",action: "' . JURI::base() . '?option=com_ajax&module=virtuemart_product_komment&format=debug&method=submitKomment", responseEl:"formResponse2"});';
				  echo '</script>';
				  ?>
							</div>
			<?php } ?>
				
				<?php if (!$user->guest) { ?>
						<div class="vm-col-1">
				<?php echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $this->product)); ?>
						
						</div>
		  <?php } ?>
				</div>
				
				<div class="clear"></div>
		  
		  
			</div>
			<hr>
		</div>
	  
	  <?php
	  //echo ($this->product->product_in_stock - $this->product->product_ordered);
	  // Product Description
	  if (!empty($this->product->product_desc)) {
		  ?>
				
				<!-- product-description -->
		  
		  <?php
	  } // Product Description END
	  
	  
 

	  
	  echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $this->product, 'position' => 'normal'));
	  
	 



	 // Product Packaging
	  $product_packaging = '';
	  if ($this->product->product_box) {
		  ?>
				<div class="product-box">
			<?php
			echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') . $this->product->product_box;
			?>
				</div>
	  <?php } // Product Packaging END ?>
	
	</div>
	
	
	
	
	
	
	<?php
	echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $this->product, 'position' => 'onbot'));
	
	echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $this->product, 'position' => 'related_products', 'class' => 'product-related-products', 'customTitle' => true));
	
	echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $this->product, 'position' => 'related_categories', 'class' => 'product-related-categories'));
	
	?>
	
	<?php // onContentAfterDisplay event
	echo $this->product->event->afterDisplayContent;
	
	echo $this->loadTemplate('reviews');
	
	// Show child categories
	if (VmConfig::get('showCategory', 1)) {
		echo $this->loadTemplate('showcategory');
	}
	
	$j = 'jQuery(document).ready(function($) {
	Virtuemart.product(jQuery("form.product"));

	$("form.js-recalculate").each(function(){
		if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
			var id= $(this).find(\'input[name="virtuemart_product_id[]"]\').val();
			Virtuemart.setproducttype($(this),id);

		}
	});
});';
	//vmJsApi::addJScript('recalcReady',$j);
	
	/** GALT
	 * Notice for Template Developers!
	 * Templates must set a Virtuemart.container variable as it takes part in
	 * dynamic content update.
	 * This variable points to a topmost element that holds other content.
	 */
	$j = "Virtuemart.container = jQuery('.productdetails-view');
Virtuemart.containerSelector = '.productdetails-view';";
	
	vmJsApi::addJScript('ajaxContent', $j);
	
	if (VmConfig::get('jdynupdate', TRUE)) {
		$j = "jQuery(document).ready(function($) {
	Virtuemart.stopVmLoading();
	var msg = '';
	jQuery('a[data-dynamic-update=\"1\"]').off('click', Virtuemart.startVmLoading).on('click', {msg:msg}, Virtuemart.startVmLoading);
	jQuery('[data-dynamic-update=\"1\"]').off('change', Virtuemart.startVmLoading).on('change', {msg:msg}, Virtuemart.startVmLoading);
});";
		
		vmJsApi::addJScript('vmPreloader', $j);
	}
	
	echo vmJsApi::writeJS();
	
	if ($this->product->prices['salesPrice'] > 0) {
		echo shopFunctionsF::renderVmSubLayout('snippets', array('product' => $this->product, 'currency' => $this->currency, 'showRating' => $this->showRating));
	}
	
	?>
	
	
</div>