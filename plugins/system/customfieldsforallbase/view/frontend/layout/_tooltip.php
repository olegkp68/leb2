<?php
/**
 * @package     CustomfieldsforallBasebase
 *
 * @Copyright   Copyright © 2010-2020 Breakdesigns.net. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

if(!isset($tooltipContent)) {
    $tooltipContent = '';
}

if(!isset($display_key_element)) {
    $display_key_element = '';
}

?>

<div class="cf4Alltooltip" role="tooltip" tabindex="-1">
    <span class="cf4All-tip-content">
        <?php echo $tooltipContent?>
    </span>
</div>
