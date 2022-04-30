<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 8842 2015-05-04 20:34:47Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}

$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('plgUpdateProductObject', array(&$this->product)); 

echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));




if(vRequest::getInt('print',false)){ ?>
<body onload="javascript:print();">
<?php } ?>
<div class="product-container productdetails-view productdetails" >
<div class="product-view">
<div class="product-essential"> 
    <?php
    // Product Navigation
    if (VmConfig::get('product_navigation', 1)) {
	?>
        <div class="product-neighbours">
	    <?php
	    if (!empty($this->product->neighbours ['previous'][0])) {
		$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $prev_link, $this->product->neighbours ['previous'][0]
			['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
	    }
	    if (!empty($this->product->neighbours ['next'][0])) {
		$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'], array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
	    }
	    ?>
    	<div class="clear"></div>
        </div>
    <?php } // Product Navigation END 
    ?>
    <div class="row">
        <div class="product-img-box clearfix col-md-5 col-sm-5 col-xs-12">
            <?php    echo $this->loadTemplate('images');  ?>
			
			<?php
	$count_images = count ($this->product->images);
	if ($count_images > 1) {
		echo $this->loadTemplate('images_additional');
	} ?>
        </div>
        <div class="product-shop col-md-7 col-sm-7 col-xs-12">
            <div class="product-shop-content">
                <div class="product-name"><h2><?php echo $this->product->product_name ?></h2></div>
                <?php echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$this->showRating,'product'=>$this->product)); ?>
                <div class="product-type-data"> 
                    <div class="price-box">
                        <?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency)); ?>
                     </div> 
                </div>
                <?php 	if (!empty($this->product->product_s_desc)) {  ?> 
                <div class="short-description">
                	       <?php  echo nl2br($this->product->product_s_desc); 
                   }
                    else { ?>
                    <div class="short-description">
                    <?php
                        echo shopFunctionsF::limitStringByWord($this->product->product_desc,  400);
                   ?>
                    
                </div> <?php }
                ?>
                    <?php
                    if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) {
                        echo $this->loadTemplate('manufacturer');
                    }
                    ?>

                <div class="product-options-bottom">
                    <?php 	echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product)); ?>
                    
    		    </div>
				
            </div>
                <?php // Ask a question about this product
                if (VmConfig::get('ask_question', 0) == 1) {
                    $askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
                    ?>
                    <div class="ask-a-question">
                        <a class="ask-a-question" href="<?php echo $askquestion_url ?>" rel="nofollow" ><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
                    </div>
                    <?php
                }
                ?>
        </div>
        </div>
    </div>  
    <div class="clearfix"></div>
    <div role="tabpanel" class="product-wapper-tab clearfix">
    	<!-- Nav tabs -->
    	<ul class="nav nav-tabs" role="tablist" id="dscr_tab">
    		<li role="presentation" class="active"><a href="#prod_dscr" aria-controls="prod_dscr" role="tab" data-toggle="tab" aria-expanded="true"><?php echo vmText::_('TEMPLATE_MILANO_PRODUCT_DETAIL_PAGE_DESCRIPTION') ?></a></li>
    		<!-- <li role="presentation" class=""><a href="#prod_reviews" aria-controls="prod_reviews" role="tab" data-toggle="tab" aria-expanded="false"><?php echo vmText::_( 'COM_VIRTUEMART_WRITE_REVIEW' ); ?></a></li> -->
        </ul>
    	
    	<!-- Tab panes -->
    	<div class="tab-content">
    		<div role="tabpanel" class="tab-pane fade active in" id="prod_dscr">
    			<?php  echo $this->product->product_desc; ?>
    		</div>
    		<div role="tabpanel" class="tab-pane fade" id="prod_reviews">
    			 <?php echo $this->loadTemplate('reviews');?>
    		</div> 
    	</div>
		
		<!-- <div class="share_icon_text" style="margin-top:30px;width:100%;text-align:center;"><?php echo vmText::_('TEMPLATE_MILANO_PRODUCT_DETAIL_PAGE_SOCIAL_ICON_SHARE') ?></div>
		<div class="share_this" style="margin-top:40px;text-align:center;">
        			<ul class="social-icons">  -->
        				<!-- <li class="st_facebook_large" data-toggle="tooltip" data-placement="top" title="Share on Facebook"><div class="share_icon_text"><?php echo vmText::_('TEMPLATE_MILANO_PRODUCT_DETAIL_PAGE_SOCIAL_ICON_SHARE') ?></div> <a class="icon-facebook" href="#"><i class="fa fa-facebook-f"></i></a></li> -->
                        <!-- <li class="st_twitter_large" data-toggle="tooltip" data-placement="top" title="Share on Twitter"><a class="icon-twitter" href="#"><i class="fa fa-twitter"></i></a></li>
        				<li class="st_googleplus_large"  data-toggle="tooltip" data-placement="top" title="Share on Google+"><a class="icon-google-plus" href="#"><i class="fa fa-google-plus"></i></a></li>
        				<li class="st_pinterest_large" data-toggle="tooltip" data-placement="top" title="Share on Pinterest"><a class="icon-pinterest" href="#"><i class="fa fa-pinterest"></i></a></li>
        				<li class="st_linkedin_large" data-toggle="tooltip" data-placement="top" title="Share on Linkedin"><a class="icon-dribbble" href="#"><i class="fa fa-linkedin"></i></a></li> -->
						<!-- <li class="st_instagram_large" data-toggle="tooltip" data-placement="top" title="Share on Instagram"><a class="icon-instagram" href="#"><i class="fa fa-instagram"></i></a></li> -->
						<!-- <li class="st_youtube_large" data-toggle="tooltip" data-placement="top" title="Share on Youtube"><a class="icon-youtube" href="#"><i class="fa fa-youtube"></i></a></li> -->
						<!-- <li class="st_facebook_large" data-toggle="tooltip" data-placement="top" title="Share on Facebook"><a class="icon-facebook" href="#"><i class="fa fa-facebook-f"></i></a></li> -->
        			<!-- </ul>
        		</div> -->
    </div> 
	<?php // Back To Category Button
	if ($this->product->virtuemart_category_id) {
		$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
		$categoryName = vmText::_($this->product->category_name) ;
	} else {
		$catURL =  JRoute::_('index.php?option=com_virtuemart');
		$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
	}
	?> 

    <?php // afterDisplayTitle Event
    echo $this->product->event->afterDisplayTitle ?>

    <?php
    // Product Edit Link
    echo $this->edit_link;
    // Product Edit Link END
    ?>

    <?php
    // PDF - Print - Email Icon
    if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
	?>
        <div class="icons">
	    <?php

	    $link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;

		echo $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);
	    //echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon');
		echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal"');
		$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component';
	    echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend"');
	    ?>
    	<div class="clear"></div>
        </div>
    <?php } // PDF - Print - Email Icon END
    ?>

    <?php 
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop'));
    ?>
 
<?php 
	// event onContentBeforeDisplay
	echo $this->product->event->beforeDisplayContent; ?>


    <?php        

	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));

    // Product Packaging
    $product_packaging = '';
    if ($this->product->product_box) {
	?>
        <div class="product-box">
	    <?php
	        echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') .$this->product->product_box;
	    ?>
        </div>
    <?php } // Product Packaging END ?>


<?php // onContentAfterDisplay event
echo $this->product->event->afterDisplayContent;
 


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

vmJsApi::addJScript('ajaxContent',$j);

echo vmJsApi::writeJS();
?> 
</div>
 </div>
<?php 
    echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));
    
    echo shopFunctionsF::renderVmSubLayout('customfields_related',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products module','customTitle' => true ));
    
    echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));

?>
<script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">if (typeof stLight !== 'undefined') { stLight.options({publisher: "0f5be4d4-f599-4a8a-a6b6-42b6c788f6a8", doNotHash: false, doNotCopy: false, hashAddressBar: false}); }</script>

