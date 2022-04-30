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
<script type="text/javascript">
/* <![CDATA[ */
 function readURL(input) {
    if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    jQuery('#blah')
                        .attr('src', e.target.result)
                        .width(150)
                        .height(200);
                };

                reader.readAsDataURL(input.files[0]);
            }
			
        }
/* ]]> */
 </script>
<div class="moduleOPcard">
<div class="notification">
	
	<span><?php echo $perm_notification; ?></span>
</div>
			<label>Add here your permission documents</label> <br>
			

			<label for="file">Send these files:</label>
			<input type="file" name="file_upload[]" id="file_upload" multiple onchange="readURL(this);" /><br>
			<img id="blah" src="#" alt="your image" />

</div>
<?php 
