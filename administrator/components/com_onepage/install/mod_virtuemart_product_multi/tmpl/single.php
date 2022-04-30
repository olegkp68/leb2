<?php // no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::jPrice();
vmJsApi::cssSite();
$root = Juri::root(true); 
	if (substr($root, -1)!=='/') $root .= '/'; 
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$t.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi.css'))
{
	
	JHtml::stylesheet($root.'templates/'.$t.'/html/mod_virtuemart_product_multi/mod_virtuemart_product_multi.css'); 
}
else
{
	JHtml::stylesheet($root.'modules/mod_virtuemart_product_multi/tmpl/mod_virtuemart_product_multi.css'); 
}



$app = JFactory::getApplication(); 
$t = $app->getTemplate(); 
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$t.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi.js'))
{
	$root = Juri::root(true); 
	if (substr($root, -1)!=='/') $root .= '/'; 
	JHtml::script($root.'templates/'.$t.'/html/mod_virtuemart_product_multi/mod_virtuemart_product_multi.js'); 
}
else
{
	JHtml::script($root.'modules/mod_virtuemart_product_multi/tmpl/mod_virtuemart_product_multi.js'); 
}

?>
<div id="cart-product">
<form action="<?php echo vmURI::getCleanUrl() ?>" method="post" name="currency_form" >
 <h3>Choose Currency</h3>
 <div class="cur_wrap">
  
   <?php 
   $i = 0; 
   $c = count($currencies); 
   foreach ($currencies as $k=>$v)
   {
	   $i++; 
	   ?>
	   <div class="cur_wrapper <?php if ($i === $c) echo ' cur_last '; ?>  cur_n_<?php echo $i; ?> <?php if ($i === 1) echo ' cur_first '; ?> <?php if ($virtuemart_currency_id == $v->virtuemart_currency_id) echo ' currency_selected '; ?> "><span class="cur_box"><button class="cur_submit" onclick="return changeCurrency(this);" rel="<?php echo $v->virtuemart_currency_id; ?>"><?php echo $v->currency_symbol; ?></button></span></div>
	   <?php
   }
   ?>
   <input type="hidden" name="virtuemart_currency_id" id="cur_virtuemart_currency_id" value="<?php echo $virtuemart_currency_id; ?>" />
   
  </div>
</form>

<div class="cur_product_wrapper">

<div class="vmproduct2">
<?php

foreach ($products as $product)
{
?>
	<div class="cur_product_p">
<?php
 
 if (empty($product->priceDisplay))
	 $product->priceDisplay[] = ''; 

if (!empty($product->priceDisplay)) {
	foreach ($product->priceDisplay as $html) { echo '<div class="cur_p price_for_'.$product->virtuemart_product_id.'">'.$html.'</div>'; }
 
  
 }
 $addtocart = ''; 
 if ($show_addtocart) $addtocart = shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product));
 
 $addtocart = str_replace('js-recalculate', '', $addtocart); 
 echo $addtocart; 
 
 ?>
 </div>
<div class="cur_avai"><?php 

echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$product,'position'=>'special')); 

//if (!empty($product->availability)) echo $product->availability; 
?></div>
	<?php 
} 
	
	
	
	?>

</div>
</div>
</div>