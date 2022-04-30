<?php
$add_scripts = true;
if (!defined('countdown_scripts_added')) {
	define('countdown_scripts_added', true); 
	$add_scripts = true;
}

$cart = VirtuemartCart::getCart(); 


?><span class="countdown_wrap lazyrun" data-hascart="<?php if (!empty($cart->cartProductsData)) echo '1'; else echo '0'; ?>" data-cmd="showMyCountdown" data-timestamp="<?php echo htmlentities($viewData['timestamp']); ?>" data-minutes="<?php echo (int)$viewData['minutes']; ?>"><span class="countdown_label countdown_positive"><?php echo JText::_('PLG_SYSTEM_CARTSTOCK_TIMEEXP'); ?> </span><span class="cartstock_countdown countdown_positive"></span><span class="countdown_finished countdown_negative"><?php echo JText::_('PLG_SYSTEM_CARTSTOCK_TIMEEXPIRED'); ?></span></span>
<?php

if ($add_scripts) {
	
	if (empty($viewData['debug'])) {
		$doc = JFactory::getDocument(); 
		$doc->addStyleDeclaration('.debugstock { display: none !important; }'); 
	}
	
$root = Juri::root(); 
if (strpos($root, -1) !== '/') $root .= '/'; 
JHtml::stylesheet($root.'plugins/system/cartstock/cartstock/tmpl/countdown.css'); 
$m = filemtime(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins/system/cartstock/cartstock/tmpl/countdown.js'); 
?><script src="<?php echo $root.'plugins/system/cartstock/cartstock/tmpl/countdown.js?m='.(int)$m; ?>" defer="defer" async="async" type="text/javascript"></script>
<?php

}
?><script>
if (typeof jQuery !== 'undefined') {
  jQuery('body').trigger('updateCounter', [ <?php echo json_encode($viewData['timestamp']); ?>, <?php if (!empty($cart->cartProductsData)) echo '1'; else echo '0'; ?> ]); 
}
</script><?php

