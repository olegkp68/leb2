<?php // no direct access
defined('_JEXEC') or die('Restricted access');
// add javascript for price and cart, need even for quantity buttons, so we need it almost anywhere
vmJsApi::jPrice();


$col = 1;
$pwidth = ' width' . floor(100 / $products_per_row);
if ($products_per_row > 1) {
    $float = "floatleft";
} else {
    $float = "center";
}
?>
<div class="vmgroup<?php echo $params->get('moduleclass_sfx') ?>">

    <?php if ($headerText) { ?>
        <div class="vmheader"><?php echo $headerText ?></div>
        <?php
    }
    if ($display_style == "div") {
        ?>
        <div class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?> productdetails">
            <?php foreach ($products as $product) { ?>
                <div class="<?php echo $pwidth ?> <?php echo $float ?>">
                    <div class="spacer">
                        <?php
                        if (!empty($product->images[0])) {
                            $image = $product->images[0]->displayMediaThumb('class="featuredProductImage" border="0"', FALSE);
                        } else {
                            $image = '';
                        }
                        echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
                        echo '<div class="clear"></div>';
                        $url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id); ?>
                        <a href="<?php echo $url ?>"><?php echo $product->product_name ?></a>
                        <?php echo '<div class="clear"></div>';

                        echo '<div class="productdetails">';
                        if ($show_price) {

                            echo '<div class="product-price">';
                            // 		echo $currency->priceDisplay($product->prices['salesPrice']);
                            if (!empty($product->prices['salesPrice'])) {
                                echo $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
                            }
                            // 		if ($product->prices['salesPriceWithDiscount']>0) echo $currency->priceDisplay($product->prices['salesPriceWithDiscount']);
                            if (!empty($product->prices['salesPriceWithDiscount'])) {
                                echo $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
                            }
                            echo '</div>';

                        }
                        if ($show_addtocart) {
                            echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product));
                        }
                        echo '</div>';
                        ?>
                    </div>
                </div>
                <?php
                if ($col == $products_per_row && $products_per_row && $col < $totalProd) {
                    echo "	</div><div style='clear:both;'>";
                    $col = 1;
                } else {
                    $col++;
                }
            } ?>
        </div>
        <br style='clear:both;'/>

        <?php
    } else {
        $last = count($products) - 1;
        ?>

        <ul class="vmproduct<?php echo $params->get('moduleclass_sfx'); ?> productdetails">
            <?php foreach ($products as $product) : ?>
                <img src="<?php echo JUri::root(true); ?>/images/ico_discount.png" class="discount" alt="Скидка на продукт!">

                <li class="<?php echo $pwidth ?> <?php echo $float ?>">
                    <?php
                    $url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id); ?>
                    <h2><a href="<?php echo $url ?>"><?php echo $product->product_name ?></a></h2>
                    <?php echo '<div class="clear"></div>';
                    echo '<div class="mod_vm_universal_thumbnail">';

                    if (!empty($product->images[0])) {
                        $imageZoom = $product->images[0];
                        $is_thumb = $product->images[0]->file_name;

//                        echo "<div>";
//                        var_dump($product->images[0]);
//                        echo "</div>";

                        if($is_thumb !== ''){
                            echo '<a href="' . $imageZoom->file_url . '" class="fancybox">';
                            echo '<img src="' . $imageZoom->file_url . '" alt="' . $product->product_name . '" title="' . $product->product_name . '"/>';
                            echo '</a>';
                        } else{
                            //echo "no Thumb";
                            $image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
                            echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
                        }
                    } else {
                        $image = '';
                    }
                    

                    echo '</div>';

                    echo '<div class="clear"></div>';

                    echo '<div class="productdetails">';
                    // $product->prices is not set when show_prices in config is unchecked

                    if ($show_price and isset($product->prices)) {

                        $user = JFactory::getUser();
                        $registerUrl = JRoute::_(JURI::root() . 'index.php?option=com_virtuemart&view=user&layout=edit');

                        echo '<div class="product-price">';

                        if (!$user->guest) {

//                            echo 'You are logged in as:<br />';
//                            echo 'User name: ' . $user->username . '<br />';
//                            echo 'Real name: ' . $user->name . '<br />';
//                            echo 'User ID  : ' . $user->id . '<br />';

                            echo $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);


                        } else { ?>

//                            <div class="ask-a-question">
//                                <a class="leb-btn" href="<?php echo $registerUrl ?>"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
//                            </div>

                        <?php }

                        echo '</div>';

                    } else {
                        if ($product->prices['salesPriceWithDiscount'] > 0) {
                            echo $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
                        }
                    }


                    if ($show_addtocart) {
                        echo shopFunctionsF::renderVmSubLayout('addtocart', array('product' => $product));
                    }

                    echo '</div>'; ?>

                </li>

                <?php
                if ($col == $products_per_row && $products_per_row && $last) {
                    echo '</ul><div class="clear"></div><ul  class="vmproduct' . $params->get('moduleclass_sfx') . ' productdetails">';
                    $col = 1;
                } else {
                    $col++;
                }
                $last--;
            endforeach; ?>
        </ul>
        <div class="clear"></div>

        <?php
    }
    if ($footerText) : ?>
        <div class="vmfooter<?php echo $params->get('moduleclass_sfx') ?>">
            <?php echo $footerText ?>
        </div>
    <?php endif; ?>
</div>