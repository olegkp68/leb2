<?php
/**
 * Spambotcheck view Users
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
defined('_JEXEC') or die;
 
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user		= \JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_spambotcheck');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo \JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_LABEL'); ?>" />
		</div>
		<div class="btn-group pull-left hidden-phone">
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo \JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>
	<div class="clearfix"> </div>

	<table class="table table-striped">
	<thead>
		<tr>
			<th width="3%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>	
			<th width="3%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>			
			<th width="5%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_USER_ID', 'a.user_id', $listDirn, $listOrder); ?>
			</th>
            <th width="5%">
                <?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_SUSPICIOUS', 'a.suspicious', $listDirn, $listOrder); ?>	
            </th>
			<th width="4%">
                <?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_TRUST', 'a.trust', $listDirn, $listOrder); ?>	
            </th>
			<th class="nowrap" width="4%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_HEADING_ENABLED', 'block', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap" width="4%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_HEADING_ACTIVATED', 'activation', $listDirn, $listOrder); ?>
			</th>
			<th width="6%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_IP', 'a.ip', $listDirn, $listOrder); ?>
			</th>
			<th width="6%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_NAME', 'name', $listDirn, $listOrder); ?>
			</th>
			<th width="6%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_USERNAME', 'username', $listDirn, $listOrder); ?>
			</th>
			<th width="6%">
				<?php echo \JText::_('COM_SPAMBOTCHECK_USER_GROUP_NAME'); ?>
			</th>
			<th width="12%">
					<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_EMAIL', 'email', $listDirn, $listOrder); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_REGISTERDATE', 'registerdate', $listDirn, $listOrder); ?>
			</th>
			<th width="23%">
				<?php echo \JText::_( 'COM_SPAMBOTCHECK_NOTE' ); ?>
			</th>
			<th width="3%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_HITS', 'a.hits', $listDirn, $listOrder); ?>
			</th>
			

		</tr>			
	</thead>
	<?php
	$k = 0;
	$n=count( $this->items );
	for ($i=0; $i < $n; $i++)
	{
		$item = $this->items[$i];
		$checked 	= JHtml::_('grid.id',   $i, $item->user_id );
		$canChange	= $user->authorise('core.edit.state',	'com_users');
		// If this group is super admin and this user is not super admin, $canChange is false
		if ((!$user->authorise('core.admin')) && JAccess::check($item->user_id, 'core.admin', 'com_users')) {
			$canChange	= false;
		}
		$supicious = !$item->suspicious ? JHtml::_('image', 'administrator/components/com_spambotcheck/images/suspicious_16.png', \JText::_('JYES')) : '';
		
	?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<?php echo $this->escape($item->id); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->user_id); ?>
			</td>
			<td class="center">
                <?php echo $supicious;?>
            </td>
			<td class="center">
				<?php if ($canChange) : ?>
					<?php echo JHtml::_('jgrid.state', JHTMLSpambotcheck::trustStates(), $item->trust, $i, 'users.', true); ?>
				<?php else : ?>
					<?php echo \JText::_($item->trust ? 'JYES' : 'JNO'); ?>
				<?php endif; ?>
            </td>
			<td class="center">
				<?php if ($canChange) : ?>
					<?php
						$self = $user->id == $item->user_id;
						echo JHtml::_('jgrid.state', JHtmlUsers::blockStates($self), $item->block, $i, 'users.', !$self);
					?>
				<?php else : ?>
					<?php echo \JText::_($item->block ? 'JNO' : 'JYES'); ?>
				<?php endif; ?>
			</td>
			<td class="center">
				<?php if ($canChange) : ?>
					<?php 
						$activated = empty( $item->activation) ? 0 : 1;
					echo JHtml::_('jgrid.state', JHtmlUsers::activateStates(), $activated, $i, 'users.', (boolean) $activated);
					?>
				<?php else : ?>
					<?php echo \JText::_($item->activation ? 'JYES' : 'JNO'); ?>
				<?php endif; ?>
			</td>
			<td class="center">
                <?php echo $this->escape($item->ip);?>
            </td>
            
			<td class="center">
				<?php echo $this->escape($item->name); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->username); ?>
			</td>
			<td class="center">
				<?php echo $item->group_names; ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->email); ?>
			</td>
			<td class="center">
	
				<?php echo JHtml::_('date', $item->registerdate, 'Y-m-d H:i:s'); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->note); ?>
			</td>		
           	<td class="center">
				<?php echo $item->hits; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
    
    
    <tfoot>
    <tr>
      <td colspan="15"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
  	</tfoot>
    
	</table>
<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
		<?php JHTML::_('spambotcheck.description', 'users'); ?>

		<?php JHTML::_('spambotcheck.creditsBackend'); ?>
</div>
</form>