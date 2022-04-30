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

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

$user		= \JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_spambotcheck&view=logs');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<table class="table table-striped">
	<thead>
		<tr>
			<th width="3%">
				<input type="checkbox" name="toggle" value="" title="<?php echo \JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>			
			<th width="3%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
            <th width="4%">
                <?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_LOGS_ACTION', 'a.action', $listDirn, $listOrder); ?>	
            </th>
			<th width="10%">
					<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_EMAIL', 'a.email', $listDirn, $listOrder); ?>
			</th>
			<th width="6%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_IP', 'a.ip', $listDirn, $listOrder); ?>
			</th>
			<th width="8%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_USERNAME', 'a.username', $listDirn, $listOrder); ?>
			</th>
			<th width="7%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_LOGS_ENGINE', 'a.engine', $listDirn, $listOrder); ?>
			</th>
			
			<th width="13%">
				<?php echo \JText::_( 'COM_SPAMBOTCHECK_LOGS_REQUEST' ); ?>
			</th>
			<th width="25%">
				<?php echo \JText::_( 'COM_SPAMBOTCHECK_LOGS_RAW_RETURN' ); ?>
			</th>
			<th width="13%">
				<?php echo \JText::_( 'COM_SPAMBOTCHECK_LOGS_PARSED_RETURN' ); ?>
			</th>
			<th width="8%">
				<?php echo JHtml::_('grid.sort', 'COM_SPAMBOTCHECK_LOGS_ATTEMPTDATE', 'a.attempt_date', $listDirn, $listOrder); ?>
			</th>
			

		</tr>			
	</thead>
	<?php
	$k = 0;
	$n=count( $this->items );
	for ($i=0; $i < $n; $i++)
	{
		$item = $this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $item->id );
		$canEdit	= $user->authorise('core.edit',			'com_spambotcheck');
		
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<?php echo $this->escape($item->id); ?>
			</td>
			<td class="center">
                <?php echo $this->escape($item->action);?>
            </td>
			<td class="center">
                <?php echo $this->escape($item->email);?>
            </td>
            
			<td class="center">
				<?php echo $this->escape($item->ip); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->username); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->engine); ?>
			</td>
			
			<td class="center">
				<?php echo $this->escape($item->request); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->raw_return); ?>
			</td>
			<td class="center">
				<?php echo $this->escape($item->parsed_return); ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('date', $item->attempt_date, 'Y-m-d H:i:s'); ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
    
    
    <tfoot>
    <tr>
      <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
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
		<?php JHTML::_('spambotcheck.description', 'logs'); ?>
		<?php JHTML::_('spambotcheck.creditsBackend'); ?>
</div>
</form>