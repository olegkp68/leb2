<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

$productModel = VmModel::getModel('product');
foreach ($viewData['products'] as $type => $products ) {
    $productModel->addImages($products,2);

	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);

	if(!empty($type) and count($products)>0){
		$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
<div class="<?php echo $type ?>-view">
  <h4><?php echo $productTitle ?></h4>
		<?php // Start the Output
    }

	// Calculating Products Per Row
	$cellwidth = ' width'.floor ( 100 / $products_per_row );

	$BrowseTotalProducts = count($products);

	$col = 1;
	$nb = 1;
	$row = 1;
?>

<div id="product_list" class="products gridview grid row">
<?php
	foreach ( $products as $product ) {
  

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('plgUpdateProductObject', array(&$product)); 

		// Show the vertical seperator
		if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		}
        
        $isSale = (!empty($product->prices['salesPriceWithDiscount'])) ? 1 : 0; 
        if ($isSale) {
        		$dtaxs = array();
        		if($product->prices["DATax"]) $dtaxs = $product->prices["DATax"];
        		if($product->prices["DBTax"]) $dtaxs = $product->prices["DBTax"];			
        		foreach($dtaxs as $dtax){
        			if(!empty($dtax)) {
        				$discount = rtrim(rtrim($dtax[1],'0'),'.');
        				$operation = $dtax[2];
        				$percentage = "";					
        				switch($operation) {
        					case '-':
        						$percentage = "-".$discount;
        						break;
        					case '+':
        						$percentage = "+".$discount;
        						break;
        					case '-%':
        						$percentage = "-".$discount."%";
        						break;
        					case '+%':
        						$percentage = "+".$discount."%";
        						break;
        					default:
        						return true;	
        				}
                        $product->percentage = $percentage;	
        			}					
        		}
        	}  
        $features =   $product->product_special;                 

   // Show Products ?>
   <!----GIRD --------->
   <div class="front_w col-lg-<?php echo 12/$products_per_row ?> col-md-<?php echo 12/$products_per_row ?> col-sm-6 col-xs-6 col-xxs-12">
	
		<div class="item">
            <div class="product-item">
                <div class="per-product">
        			<div class="images-container">
                        <div class="product-hover">
                            <?php 
                                $htmlLabel = '';
                                $dateDiff = date_diff(date_create(), date_create($product->product_available_date)); 
                                if ($features) {
                    				$htmlLabel .= '<span class="sticker top-left"><span class="hotsale">Hot</span></span>';
                    			}
                                /*if ($dateDiff->days < 7) {
                                    $htmlLabel .= '<span class="sticker top-right"><span class="labelnew">New</span></span>';
                                }*/
                                if (isset($product->percentage)) {
                                    $htmlLabel .= '<span class="sticker top-right"><span class="labelsale">'.$product->percentage.'</span></span>';
                                } 
                                echo $htmlLabel;
   
                            ?> 
							<?php if ($product->product_in_stock < 1) { ?>
								<span class="sticker top-right xx"><span class="labelsale"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_SOLD_OUT') ?></span></span>
							<?php } ?>
							
							<?php if ($product->product_in_stock < 1) { ?>
                            <a class="overlay" href="<?php echo $product->link.$ItemidStr; ?>"></a>
							<?php } ?>
							
							
        					<a class="product-image" title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>">
        						<?php
        						echo $product->images[0]->displayMediaFull('class="img-responsive"', false);
                                if (!empty($product->images[1])) { 
        						?>
									<span class="product-img-back">
                                    <?php echo $product->images[1]->displayMediaFull ('class="img-responsive" border="0"', FALSE); ?>
                                    </span>
                                <?php  
        							} 
                                ?>
        					</a>
                        </div>
                         <div class="actions-no hover-box">
                            <!-- <div class="actions"> -->
							<div class="actions-moje-nahradene-hore">
                            <?php 	echo shopFunctionsF::renderVmSubLayout('addtocart_zt',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
            				</div>
                        </div> 
        			</div> 
                    <div class="products-textlink clearfix">
                        <h2 class="product-name"><?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?></h2>
                        <div class="price-box"> 
                            <?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
            			</div> 
                    </div> 
                </div>
            </div>
		</div>
	</div>
    <!----END GIRD --------->
     <!----LIST ---------> 
     <div class="back_w">
         <div class="item col-xs-12">
             <div class="row">
                <div class="col-mobile-12 col-xs-5 col-md-4 col-sm-4 col-lg-4">
                    <div class="products-list-container">  
                        <div class="images-container">
    						<div class="product-hover"> 
    							<a class="product-image" title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>"> 
    							<?php 	echo $product->images[0]->displayMediaFull('class="img-responsive"', false); 
                                if (!empty($product->images[1])) { 
        						?>
                                    <span class="product-img-back">
                                    <?php echo $product->images[1]->displayMediaFull ('class="img-responsive" border="0"', FALSE); ?>
                                    </span>
                                <?php  
        							} 
                                ?>
    							</a>
    						</div>
    					</div> 
        		 	</div>
                </div>
                <div class="product-shop col-mobile-12 col-xs-7 col-md-8 col-sm-8 col-lg-8">
                    <div class="f-fix">
                        <div class="product-primary products-textlink clearfix">
                            <h2 class="product-name"><a title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>"><?php echo $product->product_name ?></a></h2>
                            <div class="ratings">
                			     <?php echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product)); 	?>
                            </div>
                            <div class="price-box"><?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?></div>
                            <div class="desc std"> 
                                <?php echo nl2br($product->product_s_desc);  ?>
                             </div>
                         </div>
                        <div class="product-secondary actions-no actions-list clearfix"> 
                        
                            <div class="action">
                             <?php 	echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
                             </div>
            		 	</div>
                    </div>
                </div>
            </div>
        </div>
     </div>
     <!----END LIST --------->
      <?php
    
  }
?>
</div>
<?php
      if(!empty($type)and count($products)>0){
        // Do we need a final closing row tag?
        //if ($col != 1) {
      ?>
    <div class="clear"></div>
  </div>
    <?php
    // }
    }
  }
