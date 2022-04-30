<?php
/* ======================================================
# Login as User for Joomla! - v3.3.2
# -------------------------------------------------------
# For Joomla! CMS
# Author: Web357 (Yiannis Christodoulou)
# Copyright (©) 2009-2019 Web357. All rights reserved.
# License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
# Website: https:/www.web357.com/
# Demo: https://demo.web357.com/?item=loginasuser
# Support: support@web357.com
# Last modified: 21 Mar 2019, 01:46:37
========================================================= */

defined('_JEXEC') or die;

// Get Joomla! version
$jversion = new JVersion;
$short_version = explode('.', $jversion->getShortVersion()); // 3.8.10
$mini_version = $short_version[0].'.'.$short_version[1]; // 3.8

if (version_compare($mini_version, "4.0", ">="))
{
	// joomla 4.x
    $row_fluid = '';
}
else
{
	// joomla 3.x
    $row_fluid = '-fluid';
}
?>

<div class="container-fluid">
    <div class="row<?php echo $row_fluid; ?>">
        <div id="j-sidebar-container" class="span2 col col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col col-md-10 span10 j-main-container p-4">
            <div class="row<?php echo $row_fluid; ?>">
                <div class="col-12 span12">
                    <?php
                    echo $this->form->getLabel('about');
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>