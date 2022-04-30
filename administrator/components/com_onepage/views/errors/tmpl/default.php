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
*/
	defined( '_JEXEC' ) or die( 'Restricted access' );
	if (!empty($this->items)) {
	?>
	<table class="table table-striped" id="articleList">
	
		<thead>
					<tr>
					<?php
					$f = reset($this->items); 
					foreach ($f as $kk=>$vv) {
						?><th><?php echo $kk; ?></th>
						<?php
					}
					?>
					</tr>
				</thead>
	<tbody>
				
	<?php
	foreach ($this->items as $i => $item) {
		?><tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
		   <?php foreach ($item as $k=>$w) { ?>
		   <td style="max-width: 250px; overflow-x: auto;"><?php 
		   
		   if ($k === 'msg') { $w = htmlentities(json_decode($w)); }
		   if ($k === 'extra') { $w = '<pre>'.var_export($w, true).'</pre>'; }
		   echo $w; ?></td>
		   <?php } ?>
		</tr>
		<?php
	}
	
	?>
	</tbody>
	</table>
	<form id="adminForm" name="adminForm" action="index.php?option=com_onepage&view=<?php echo JRequest::getWord('view'); ?>&controller=<?php echo JRequest::getWord('view'); ?>">
	<input type="hidden" name="limit" value="<?php echo $this->getModel()->getState('limit', 25); ?>" />
	
	<input type="hidden" name="option" value="com_onepage" />
	<input type="hidden" name="view" value="<?php echo JRequest::getWord('view'); ?>" />
	<input type="hidden" name="controller" value="<?php echo JRequest::getWord('view'); ?>" />
	<?php echo $this->pagination->getListFooter(); ?>
	</form>
	<?php 
	
	}
	else {
		
	}
	?>