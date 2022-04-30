<?php // no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::jPrice();


	
$app = JFactory::getApplication(); 
$t = $app->getTemplate(); 
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$t.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi.css'))
{
	$ftime = filemtime(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$t.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi.css'); 
	//JHtml::stylesheet($root.'templates/'.$t.'/html/mod_virtuemart_product_multi/mod_virtuemart_product_multi.css?nocache='.$ftime); 
}
else
{
	//JHtml::stylesheet($root.'modules/mod_virtuemart_product_multi/tmpl/mod_virtuemart_product_multi.css'); 
}



$app = JFactory::getApplication(); 
$t = $app->getTemplate(); 
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$t.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi'.DIRECTORY_SEPARATOR.'mod_virtuemart_product_multi.js'))
{
	
	
	JHtml::script($root.'templates/'.$t.'/html/mod_virtuemart_product_multi/mod_virtuemart_product_multi.js'); 
}
else
{
	JHtml::script($root.'modules/mod_virtuemart_product_multi/tmpl/mod_virtuemart_product_multi.js'); 
}

?>
<div id="cart-product">
<?php 
$url = vmURI::getCleanUrl(); 
//$url = str_replace('http:', 'https', $url); 
/*
?>
<form action="<?php echo $url; ?>" method="post" name="currency_form" >
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
<?php 
*/
?>
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
$img_array = array();
if (isset($attribs['article'])) {
	$article = $attribs['article']; 
	$articletext = $article->text; 
	$imgs = mod_virtuemart_product_multi::getImages($articletext); 
	$img_array = array(); 
	foreach ($imgs as $i) {
		$img_array[] = $i['img']; 
	}
	$rating = 5; 
	$ratingCount = 1; 
	mod_virtuemart_product_multi::getRatingData($article, $rating, $ratingCount); 
    $title = $article->params->get('page_title', $product->product_name); 
	
?>	
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": <?php echo json_encode($title); ?>,
  "image": <?php echo json_encode($img_array) ?>,
  "description": <?php echo json_encode($article->metadesc); ?>,
  "sku": <?php echo json_encode($product->product_sku); ?>,
  "mpn": <?php echo json_encode($product->product_sku); ?>,
  "brand": {
    "@type": "Thing",
    "name": "absoluteBLACK"
  },
  "review": {
    "@type": "Review",
    "reviewRating": {
      "@type": "Rating",
      "ratingValue": <?php echo json_encode((string)$rating); ?>,
      "bestRating": "5"
    },
    "author": {
      "@type": "Person",
      "name": <?php 
	  $db = JFactory::getDBO(); 
	  $q = 'select `name`, `comment`, `title` from `#__jcomments` where published = 1 order by date desc  limit '.(int)$product->virtuemart_product_id.', 1'; 
	  $db->setQuery($q); 
	  $row = $db->loadAssoc(); 
	  echo json_encode(trim($row['name'])); 
	  ?>
    },
	"reviewBody": <?php 
		echo json_encode($row['comment']); 
	?>,
	"name": <?php echo json_encode($row['title']); ?>
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": <?php echo json_encode((string)$rating); ?>,
    "reviewCount": <?php echo json_encode((string)$ratingCount); ?>
  },
  "offers": {
    "@type": "Offer",
    "url": <?php echo json_encode((string)mod_virtuemart_product_multi::$current_url); ?>,
    "priceCurrency": <?php 
			if (!class_exists('ShopFunctions')) {
			  require(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctions.php'); 
			}
			$c3 = ShopFunctions::getCurrencyByID($currency->getcurrencyForDisplay(), 'currency_code_3'); 
			echo json_encode($c3); 
	?>,
    "price": <?php 
	echo json_encode((string)number_format((float)$product->prices['salesPrice'], 2, '.', ''));  
	?>,
    "priceValidUntil": <?php echo json_encode(date('Y-m-d',time()+24*60*60)); ?>,
    "itemCondition": "https://schema.org/UsedCondition",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@type": "Organization",
      "name": "absoluteBLACK"
    }
  }
}
</script>
	
	<?php 
	}
	
	
	
	
	
} 
	/*
		var_dump($product); 
var_dump($attribs['article']); die(); 
die(); 
$x = get_defined_vars(); 
var_dump($x); 
die(); 
	*/
	?>

</div>
</div>
</div>

