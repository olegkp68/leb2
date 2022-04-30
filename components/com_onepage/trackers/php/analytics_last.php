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
if (!empty($this->params->universalga)) $uga = 'true'; 
else $uga = 'false'; 

$tracker_name = 'OPCTracker'; //.$idformat; 

$this->params->universalga = (int)$this->params->universalga; 
if ($this->params->universalga === 2) {
	$this->isPureJavascript = true; 
	 
	  $app = JFactory::getApplication();
	  
	  if (!empty($GLOBALS['_gtag'])) {
		  
		  ?><script>
		  if (typeof navigator.sendBeacon !== 'undefined') {
			gtag('config', '<?php echo $this->params->google_analytics_id; ?>'<?php 
		  
		  $obj = $GLOBALS['_gtag'];
		  $obj->send_page_view = true; 
		   if (!empty($this->params->anon_ip)) {
		    $obj->anonymize_ip = true; 
		  }
		   if (!empty($this->params->track_user_id)) {
		    $obj->user_id = (int)JFactory::getUser()->get('id'); 
		  }
		  $obj->groups = 'checkout';
		  $obj->transport_type = 'beacon';
		  echo ','.json_encode($obj, JSON_PRETTY_PRINT); 
		  
		  
		   ?>);
		  }
		  else {
			 gtag('config', '<?php echo $this->params->google_analytics_id; ?>'<?php 
			   unset($obj->transport_type); 
			   echo ','.json_encode($obj, JSON_PRETTY_PRINT); 
				?>); 
		  }
	 
	  if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking gtag: Page view sent with URL override :', <?php echo json_encode($GLOBALS['_gtag']); ?>); 
	  }
	  
	 
	 
	
	 
	 </script>
	 <?php
	  }
	  
	  if (!empty($GLOBALS['GTAG_ORDER_JS'])) {
		  echo $GLOBALS['GTAG_ORDER_JS']; //as from order tracking's analytics.php
	  }
	  
	  
	  /*
	  else {
		  $obj = new stdClass(); 
		  $obj->send_page_view = true; 
		   if (!empty($this->params->track_user_id)) {
		     $obj->user_id = (int)JFactory::getUser()->get('id'); 
		   } 
		  if (!empty($this->params->anon_ip)) {
		   $obj->anonymize_ip = true; 
		  }
		 
		  echo json_encode($obj, JSON_PRETTY_PRINT); 
	  }
	 */
	
}
else {

?>
<script type="text/javascript">
//<![CDATA[
  
  
  // if universtal analytics is initialized
  if (typeof ga != 'undefined')
   {
	   <?php
	   // if normal ecommerce tracking enabled: 
	  if (!empty($this->params->ec_type)) 
	  { 
  
	   include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'ecommerce_send.php'); 
	  }
	  else
	  {
		  
		  
		  
	  }
	   ?>
	   
	    // trigger new GA thank you page if GA is found
   
	
	   <?php 
	   
	   
	     include(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'GA'.DIRECTORY_SEPARATOR.'pageview.php');  
		 ?>
	     
		 
		 
	
	   
	   
   }
   
   
 
//]]>
</script>   

<?php 
}