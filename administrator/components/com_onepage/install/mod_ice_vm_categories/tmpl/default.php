<?php
/**
 * IceVmCategory Extension for Joomla 2.5 By IceTheme
 * 
 * 
 * @copyright	Copyright (C) 2008 - 2012 IceTheme.com. All rights reserved.
 * @license		GNU General Public License version 2
 * 
 * @Website 	http://www.icetheme.com/Joomla-Extensions/iceaccordion.html
 * @Support 	http://www.icetheme.com/Forums/IceVmCategory/
 *
 */
  ?>
 
 
 <div class="lofmenu_virtuemart" id="lofmenu_virtuemart">
	<?php echo $categories; ?>
</div>
<?php if (false) { ?>
<script type="text/javascript">
    if (typeof jQuery != 'undefined')
	if(jQuery('#lofmenu_virtuemart .lofmenu .lofitem1') ){
		jQuery('#lofmenu_virtuemart .lofmenu .lofitem1').find('ul').css({'display':'none'});
	}
	
	if (typeof jQuery != 'undefined')
	jQuery(document).ready(function(){
	
		jQuery('#lofmenu_virtuemart .lofmenu .lofitem1 ul').each(function(){
			jQuery(this).find('li:first').addClass('loffirst');
		})
		jQuery('#lofmenu_virtuemart .lofmenu li').each(function(){
			jQuery(this).mouseenter(function(){
				jQuery(this).addClass('lofactive');
				jQuery(this).find('ul').css({'display':'block'});
				jQuery(this).find('ul li ul').css({'display':'none'});
			});
			jQuery(this).mouseleave(function(){
				jQuery(this).removeClass('lofactive');
				jQuery(this).find('ul').css({'display':'none'});
			});
		});
	});
</script>
<?php } 