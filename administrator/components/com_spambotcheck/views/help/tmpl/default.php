<?php
/**
 * Help view for Spambotcheck
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
 
 if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
 
	<div><?php echo \JText::_('COM_SPAMBOTCHECK_CREDIT'); ?> </div>
 
 <?php JHTML::_('spambotcheck.creditsBackend'); ?>
 </div>
