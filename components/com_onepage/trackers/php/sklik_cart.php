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



if (!defined('SKLIKLOADED'))
if (!empty($this->params->seznam_retargeting)) {
	define('SKLIKLOADED', 1); 
?>

<!-- Sklik.cz remarketing -->
<script type="text/javascript">
/* <![CDATA[ */
var seznam_retargeting_id = '<?php echo $this->params->seznam_retargeting_id; ?>';

if ((typeof console != 'undefined')  && (typeof console.log != 'undefined')  &&  (console.log != null))
	  {
	     console.log('OPC Tracking: Seznam retargeting.'); 
	  }


/* ]]> */
</script>


<script type="text/javascript" src="//c.imedia.cz/js/retargeting.js"></script>
<?php } 

