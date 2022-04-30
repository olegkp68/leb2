<?php
/**
 * Users view for Spambotcheck
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Users View
 *
 * @package    Joomla.Administrator
 * @subpackage com_spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since      Joomla 1.6
 */
class SpambotcheckViewUsers extends JViewLegacy
{
	protected $form;
	protected $items;
	protected $state;
	protected $canDo;
	

	function display($tpl = null) {
		
		// Get data from the model
		$this->form	    = $this->get('Form');
		$this->items    = $this->get('Items') ?: array();
		$this->state    = $this->get('State');
		$pagination     = $this->get('Pagination');

		$this->assignRef('pagination', $pagination);
		
		// We don't need toolbar in the modal window.
		if (($this->getLayout() !== 'modal') && ($this->getLayout() !== 'modal_data')) {
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar() {
		$canDo	= JHelperContent::getActions('com_users');
		$canDoSpambotcheck = SpambotcheckHelper::getActions();
		$doc = \JFactory::getDocument();
		$css = '.icon-48-spambotcheck {background:url(../administrator/components/com_spambotcheck/images/logo-banner.png) no-repeat;} ';
   		$doc->addStyleDeclaration($css);		

		JToolBarHelper::title(\JText::_( 'COM_SPAMBOTCHECK_USERS' ), 'spambotcheck' );
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('users.trust', 'publish.png', 'publish.png', 'COM_SPAMBOTCHECK_TRUST', true);
			JToolBarHelper::custom('users.distrust', 'unpublish.png', 'unpublish.png', 'COM_SPAMBOTCHECK_DISTRUST', true);
			JToolBarHelper::divider();
			JToolBarHelper::publish('users.activate', 'COM_SPAMBOTCHECK_TOOLBAR_ACTIVATE', true);
			JToolBarHelper::divider();
			JToolBarHelper::unpublish('users.block', 'COM_SPAMBOTCHECK_TOOLBAR_BLOCK', true);
			JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_SPAMBOTCHECK_TOOLBAR_UNBLOCK', true);
			JToolBarHelper::divider();

		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('COM_SPAMBOTCHECK_DELETE', 'users.delete', 'COM_SPAMBOTCHECK_DELETE');
		}
		
		if ($canDoSpambotcheck->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_spambotcheck');
			JToolBarHelper::divider();
		}
		
		JHtmlSidebar::setAction('index.php?option=com_spambotcheck&view=users');
		$options = array(
			JHtml::_('select.option', 'today', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_TODAY')),
			JHtml::_('select.option', 'past_week', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_PAST_WEEK')),
			JHtml::_('select.option', 'past_1month', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_PAST_1MONTH')),
			JHtml::_('select.option', 'past_3month', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_PAST_3MONTH')),
			JHtml::_('select.option', 'past_6month', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_PAST_6MONTH')),
			JHtml::_('select.option', 'past_year', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_PAST_YEAR')),
			JHtml::_('select.option', 'post_year', \JText::_('COM_SPAMBOTCHECK_OPTION_RANGE_POST_YEAR')),
			JHtml::_('select.option', '*', 'JALL'),
		);
		
		JHtmlSidebar::addFilter(
			\JText::_('COM_SPAMBOTCHECK_SELECT_RANGE'),
			'filter_range',				
			JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.range'), true)
		);
		
		$options = array();
		$options[] = JHtml::_('select.option', '0', 'COM_SPAMBOTCHECK_SUSPICIOUS');
		$options[] = JHtml::_('select.option', '1', 'COM_SPAMBOTCHECK_NOT_SUSPICIOUS');
		$options[] = JHtml::_('select.option', '*', 'JALL');
		
		JHtmlSidebar::addFilter(
			\JText::_('COM_SPAMBOTCHECK_SELECT_SUSPICION_STATE'),
			'filter_suspicious',				
			JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.suspicious'), true)
		);
		
		$options = array();
		$options[] = JHtml::_('select.option', '0', 'COM_SPAMBOTCHECK_NOT_BLOCKED');
		$options[] = JHtml::_('select.option', '1', 'COM_SPAMBOTCHECK_BLOCKED');
		$options[] = JHtml::_('select.option', '*', 'JALL');
		
		JHtmlSidebar::addFilter(
			\JText::_('COM_SPAMBOTCHECK_SELECT_BLOCK_STATE'),
			'filter_block',				
			JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.block'), true)
		);
		
		$options = array();
		$options[] = JHtml::_('select.option', '1', 'COM_SPAMBOTCHECK_NOT_ACTIVATED');
		$options[] = JHtml::_('select.option', '0', 'COM_SPAMBOTCHECK_ACTIVATED');
		$options[] = JHtml::_('select.option', '*', 'JALL');
		
		JHtmlSidebar::addFilter(
			\JText::_('COM_SPAMBOTCHECK_SELECT_ACTIVATION_STATE'),
			'filter_activation',				
			JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.activation'), true)
		);
		
		$options = array();
		$options[] = JHtml::_('select.option', '0', 'COM_SPAMBOTCHECK_DISTRUST');
		$options[] = JHtml::_('select.option', '1', 'COM_SPAMBOTCHECK_TRUST');
		$options[] = JHtml::_('select.option', '*', 'JALL');
		
		JHtmlSidebar::addFilter(
			\JText::_('COM_SPAMBOTCHECK_SELECT_TRUST_STATE'),
			'filter_trust',				
			JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.trust'), true)
		);
	}
}
