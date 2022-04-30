<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_opcard
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

?>
<div class="moduleOPcard">
<div class="notification"id="over_21_div">
	<label><input type="checkbox" name="over_21_field" id="over_21_field" value="age_agreeBox"/>
	<span><?php echo $check_notification21; ?></span></label>
	<input type="text" name="agree_text_21" id="agree_text_21_field" value="<?php echo $check_notification21; ?>" class="visuallyhidden" />
</div>


</div>
<script type="text/javascript">
addOpcTriggerer('callSubmitFunct', 'op_check3'); 
</script>
<?php 
