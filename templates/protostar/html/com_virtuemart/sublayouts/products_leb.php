<?php
/**
 * sublayout products
 *
 * @package    VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');


$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if (!empty($Itemid)) {
    $ItemidStr = '&Itemid=' . $Itemid;
}

foreach ($viewData['products'] as $type => $products) {

    $rowsHeight = shopFunctionsF::calculateProductRowsHeights($products, $currency, $products_per_row);

    if (!empty($type) and count($products) > 0) {
        $productTitle = vmText::_('COM_VIRTUEMART_' . strtoupper($type) . '_PRODUCT'); ?>
        <div class="<?php echo $type ?>-view">
        <h4><?php echo $productTitle ?></h4>
        <?php // Start the Output
    }

    // Calculating Products Per Row
    $cellwidth = ' width' . floor(100 / $products_per_row);

    $BrowseTotalProducts = count($products);

    $col = 1;
    $nb = 1;
    $row = 1;

    foreach ($products as $product) {

        // Show the horizontal seperator
        if ($col == 1 && $nb > $products_per_row) { ?>
            <div class="horizontal-separator"></div>
        <?php }

        // this is an indicator wether a row needs to be opened or not
        if ($col == 1) { ?>
            <div class="row">
        <?php }

        // Show the vertical seperator
        if ($nb == $products_per_row or $nb % $products_per_row == 0) {
            $show_vertical_separator = ' ';
        } else {
            $show_vertical_separator = $verticalseparator;
        }

        // Show Products ?>
        <div
            class="product leb-prod-category <?php //echo $show_vertical_separator ?><?php echo ' vm-col-' . $products_per_row . $show_vertical_separator ?>">
            <div class="spacer">
                <?php // more then 1 product in a row
                $imageZoom = $product->images[0];
                $is_thumb = $product->images[0]->file_name;
                ?>

                <?php //echo $rowsHeight[$row]['price'] ?>
                <?php //$url = JRoute::_(JURI::root() . 'index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . '&tmpl=component'); ?>
                <?php $url = JRoute::_(JURI::root() . 'index.php?option=com_virtuemart&view=user&layout=edit'); ?>
                <?php $askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . '&tmpl=component', FALSE); ?>


                <?php if ($products_per_row >= 2) : ?>
                    <h2 class="leb-prod-title" rel="tooltip"
                        title="<?php echo $product->product_name ?> - <?php echo $product->product_s_desc; ?>">
                        <?php echo JHtml::link($product->link . $ItemidStr, $product->product_name); ?>
                    </h2>

                    <div class="leb-product-img">
                        <?php if ($is_thumb !== '') { ?>
                            <a href="<?php echo $product->link . $ItemidStr; ?>" class="browseProductImageLink"
                               title="<?php echo $product->product_name ?>">
                                <?php echo $product->images[0]->displayMediaThumb('title="' . $product->product_name . '"class="browseProductImage"', false); ?>
                            </a>
                            <a title="<?php echo $product->product_name ?>" href="<?php echo $imageZoom->file_url; ?>"
                               class="fancybox">
                                Увеличить<?php vmText::_('ENLARGE'); ?>
                            </a>
                            <?php if (!$user->guest) { ?>
                                <?php if ($product->prices['salesPrice'] <= 0 and VmConfig::get('askprice', 1) and isset($product->images[0]) and !$product->images[0]->file_is_downloadable) { ?>
                                <?php } else { ?>
                                    <span>|</span>
                                    <?php echo shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency)); ?>
                                <?php } ?>
                            <?php } ?>

                        <?php } else { ?>
                            <a href="<?php echo $product->link . $ItemidStr; ?>"
                               title="<?php echo $product->product_name ?>" class="browseProductImageLink">
                                <?php echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false); ?>
                            </a>
                            <?php if ($product->prices['salesPrice'] <= 0 and VmConfig::get('askprice', 1) and isset($product->images[0]) and !$product->images[0]->file_is_downloadable) { ?>
                            <?php } else { ?>
                                <?php echo shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency)); ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="leb-cat-product-info">

                        <!--                        --><?php //if (!$user->guest) { ?>
                        <!--                            <div class="ask-a-question">-->
                        <!--                                <a class="leb-btn enquiry-btn" href="-->
                        <?php //echo $product->link . $ItemidStr; ?><!--"-->
                        <!--                                   rel="nofollow">-->
                        <?php //echo vmText::_('COM_VIRTUEMART_PRODUCT_ASKPRICE') ?><!--</a>-->
                        <!--                            </div>-->
                        <!--                        --><?php //} ?>

                        <!--                        --><?php //if ($product->prices['salesPrice'] <= 0 and VmConfig::get('askprice', 1) and isset($product->images[0]) and !$product->images[0]->file_is_downloadable) { ?>
                        <?php if ($user->guest) { ?>
                            <div class="ask-a-question">
                                <a class="leb-btn ask-price-btn"
                                   href="<?php echo $url ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
                            </div>
                            <div class="clear"></div>

                        <?php } ?>


                        <?php //echo $rowsHeight[$row]['customs'] ?>

                        <div class="vm3pr-<?php echo $rowsHeight[$row]['customfields'] ?>">
                            <?php if (!$user->guest) { ?>
                                <div class="ask-a-question add-enquiry">
                                    <a class="leb-btn enquiry-btn" href="<?php echo $product->link . $ItemidStr; ?>"
                                       rel="nofollow"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ASKPRICE') ?></a>
                                </div>
                                <?php echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product)); ?>
                            <?php } ?>
                            <?php //echo shopFunctionsF::renderVmSubLayout('customfields', array('product' => $product, 'position' => 'normal')); ?>

                        </div>

                    </div>

                <?php elseif ($products_per_row <= 1) : // 1 product in a row ?>
                    <div class="leb-cat-product-info">
                        <h2 class="leb-prod-title" rel="tooltip"
                            title="<?php echo $product->product_name ?> - <?php echo $product->product_s_desc; ?>">
                            <?php echo JHtml::link($product->link . $ItemidStr, $product->product_name); ?>
                        </h2>

                        <div
                            class="leb-product-descr-container leb-product-descr-container-<?php echo $rowsHeight[$row]['product_s_desc'] ?>">
                            <?php if (!empty($rowsHeight[$row]['product_s_desc'])) { ?>
                                <p class="product_s_desc">
                                    <?php // Product Short Description
                                    if (!empty($product->product_s_desc)) {
                                        echo shopFunctionsF::limitStringByWord($product->product_s_desc, 60, ' ...') ?>
                                    <?php } ?>
                                </p>
                            <?php } ?>
                        </div>

                        <div class="vm3pr-<?php echo $rowsHeight[$row]['price'] ?>">

                            <?php if (!$user->guest) { ?>
                                <?php echo shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency)); ?>
                            <?php } ?>
                            <?php if ($user->guest) { ?>
                                <div class="ask-a-question">
                                    <a class="leb-btn ask-price-btn"
                                       href="<?php echo $url ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
                                </div>
                                <div class="clear"></div>

                            <?php } ?>

                            <?php if ($product->prices['salesPrice'] <= 0 and VmConfig::get('askprice', 1) and isset($product->images[0]) and !$product->images[0]->file_is_downloadable) { ?>
                                <?php echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product, 'rowHeights' => $rowsHeight[$row])); ?>
                            <?php } ?>

                        </div>

                        <?php //echo $rowsHeight[$row]['customs'] ?>

                        <?php if (!$user->guest) { ?>
                            <div class="buy-btn-block vm3pr-<?php echo $rowsHeight[$row]['customfields'] ?>">
                                <?php echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product)); ?>
                                <?php //echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product, 'rowHeights' => $rowsHeight[$row])); ?>
                            </div>

                            <div class="ask-a-question">
                                <a class="leb-btn enquiry-btn" href="<?php echo $product->link . $ItemidStr; ?>"
                                   rel="nofollow"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ASKPRICE') ?></a>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="leb-product-img">
                        <?php if ($is_thumb !== '') { ?>
                            <a title="<?php echo $product->product_name; ?>" href="<?php echo $imageZoom->file_url; ?>"
                               class="fancybox">
                                <?php echo $product->images[0]->displayMediaThumb('title="' . $product->product_name . '" class="browseProductImage"', false); ?>
                            </a>
                        <?php } else { ?>
                            <a title="<?php echo $product->product_name ?>"
                               href="<?php echo $product->link . $ItemidStr; ?>">
                                <?php echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false); ?>
                            </a>
                        <?php } ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>


        <?php
        $nb++;

        // Do we need to close the current row now?
        if ($col == $products_per_row || $nb > $BrowseTotalProducts) { ?>
            <div class="clear"></div>
            </div>
            <?php
            $col = 1;
            $row++;
        } else {
            $col++;
        }
    }

    if (!empty($type) and count($products) > 0) {
        // Do we need a final closing row tag?
        //if ($col != 1) {
        ?>
        <div class="clear"></div>
        </div>
        <?php
        // }
    }
}
?>
