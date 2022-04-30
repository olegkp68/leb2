<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
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

?><script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>

<script>
if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: criteo init'); 
	  }
</script>

<?php
$lang = JFactory::getLanguage();


$app = JFactory::getApplication();
$menu = $app->getMenu();
if (($menu->getActive() == $menu->getDefault()) || (($menu->getActive() == $menu->getDefault($lang->getTag())))) {
	?>
	<script type="text/javascript">
window.criteo_q = window.criteo_q || [];
var deviceType = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
window.criteo_q.push(
 { event: "setAccount", account: "<?php echo $this->params->account; ?>" },
 { event: "setCustomerId", id: "<?php echo JFactory::getUser()->get('id', 0); ?>" },
 { event: "setEmail", email: "<?php echo htmlentities(JFactory::getUser()->get('email', '')); ?>" },
 { event: "setSiteType", type: deviceType},
 { event: "viewHome"});
</script> 

<script>
if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: criteo frontpage event', window.criteo_q); 
	  }
</script>

<?php
}

