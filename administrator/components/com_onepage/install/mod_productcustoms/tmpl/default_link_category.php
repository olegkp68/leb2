<?php
defined('_JEXEC')or die;

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 


//tranformse current GET object into URL: 
require(JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_productcustoms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'getget.php');
	
	

?>

<?php 
if ($first) { 
?><input type="hidden" <?php echo $checked; ?> class="productfilter_selector" value="<?php echo $id; ?>" data-value="<?php echo $id; ?>"  id="catid_<?php echo $id; ?>" onclick="return mod_productcustoms.goTo(this);" name="virtuemart_category_id[]" data-name="virtuemart_category_id" data-label="<?php echo htmlentities($val); ?>" />
<input type="hidden" <?php echo $checked; ?> class="productfilter_selector" value="<?php echo $id; ?>" data-value="<?php echo $id; ?>"  id="catid_<?php echo $id; ?>" onclick="return mod_productcustoms.goTo(this);" name="primary_virtuemart_category_id" data-name="virtuemart_category_id" data-label="<?php echo htmlentities($val); ?>" />
<span style="display: none;"><a data-checkbox="catid_<?php echo $id; ?>" href="<?php echo '#'; 
//PCH::getLink($get, array('virtuemart_category_id'=>$id)); 
?>" data-value="<?php echo $id; ?>" data-name="virtuemart_category_id" rel="nofollow" onclick="return mod_productcustoms.goTo(this);" data-label="<?php echo htmlentities($val); ?>"><?php echo $val; ?></a></span>
<?php
}
else {
?>
<label for="catid_<?php echo $id; ?>" id="label_catid_<?php echo $id; ?>">
<input type="checkbox" <?php echo $checked; ?> class="productfilter_selector" value="<?php echo $id; ?>" data-value="<?php echo $id; ?>"  id="catid_<?php echo $id; ?>" onclick="return mod_productcustoms.goTo(this);" name="virtuemart_category_id[]" data-name="virtuemart_category_id" data-label="<?php echo htmlentities($val); ?>" />
<a data-checkbox="catid_<?php echo $id; ?>" href="<?php echo '#'; 
//PCH::getLink($get, array('virtuemart_category_id'=>$id)); 
?>" data-value="<?php echo $id; ?>" data-name="virtuemart_category_id" rel="nofollow" onclick="return mod_productcustoms.goTo(this);" data-label="<?php echo htmlentities($val); ?>"><?php echo $val; ?></a>
</label>
<?php } ?>
