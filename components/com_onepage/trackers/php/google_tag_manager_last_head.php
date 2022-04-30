<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );






	
	$this->isPureJavascript = true; 
	
 ?>
<script>
/* <![CDATA[ */
if (typeof dataLayer == 'undefined')
	dataLayer = []; 


if (typeof dataLayerImpr !== 'undefined')
dataLayer.push(dataLayerImpr); 

if ((typeof console != 'undefined') && (typeof console.log == 'function')) {
 console.log('OPC Tracking GTM Datalayer (last_head)', dataLayer); 
}



if (dataLayer.length == 0)
{
	if (typeof window.google_tag_params === 'undefined') window.google_tag_params = { }; 
	
	dataLayer.push({
    'event': '<?php echo $this->escapeSingle($this->params->tag_event); ?>',
    'google_tag_params': window.google_tag_params
   });
   
   if ((typeof console != 'undefined') && (typeof console.log == 'function')) {
		console.log('OPC Tracking GTM: Adding empty remarketing tag', dataLayer); 
	}
}


<?php 

// Home
  // Search results
  // 404 page
  // Category
  // Productdetail
  // Checkout funnel (all steps)
  // Service --> information about Shipping, Payments, Return, etc. 
  // Uncategorized --> all pages without a specific pagetype

include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pagetype.php');   

?>
dataLayer.push({
   'pageType': '<?php echo $pageType; ?>'       // every page gets it's own pagetype as mentioned as above. 'value' is the dynamic.
});

<?php
 $la = explode('-', $lang->getTag()); 
 $glang = strtolower($la[0]); 
?>

dataLayer.push({
   'language': '<?php echo $glang; ?>'       // every page gets it's own language as mentioned as above. 'value' is the dynamic.
});




	var productQueryUrl = '<?php 
	
	$root = JUri::root(false); 
	
	echo $root; ?>index.php?option=com_onepage&view=xmlexport&task=getproduct&format=opchtml&pidformat=<?php echo $this->params->pidformat; ?>&pid_prefix=<?php echo urlencode($this->params->pid_prefix); ?>&pid_suffix=<?php echo urlencode($this->params->pid_suffix); ?><?php 
	require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php');
	$lang = OPCloader::getLangCode(); 
	if (!empty($lang)) echo '&lang='.$lang; 
	?>'; 

	



/* ]]> */
</script> 
 <?php
$this->isPureJavascript = true; 


