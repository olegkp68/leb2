<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Filter view class for Finder.
 *
 * @since  2.5
 */
class FinderViewFilter extends JViewLegacy
{
	/**
	 * The filter object
	 *
	 * @var  FinderTableFilter
	 *
	 * @since  3.6.2
	 */
	protected $filter;

	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since  3.6.2
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  JObject|boolean
	 *
	 * @since  3.6.2
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  mixed
	 *
	 * @since  3.6.2
	 */
	protected $state;

	/**
	 * The total indexed items
	 *
	 * @var  integer
	 *
	 * @since  3.8.0
	 */
	protected $total;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load the view data.
		$this->filter = $this->get('Filter');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->state = $this->get('State');
		$this->total = $this->get('Total');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->filter_id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == JFactory::getUser()->id);
		$canDo = JHelperContent::getActions('com_finder');

		// Configure the toolbar.
		JToolbarHelper::title(
			$isNew ? JText::_('COM_FINDER_FILTER_NEW_TOOLBAR_TITLE') : JText::_('COM_FINDER_FILTER_EDIT_TOOLBAR_TITLE'),
			'zoom-in finder'
		);

		// Set the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::apply('filter.apply');
				JToolbarHelper::save('filter.save');
				JToolbarHelper::save2new('filter.save2new');
			}

			JToolbarHelper::cancel('filter.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			// Since it's an existing record, check the edit permission.
			if (!$checkedOut && $canDo->get('core.edit'))
			{
				JToolbarHelper::apply('filter.apply');
				JToolbarHelper::save('filter.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					JToolbarHelper::save2new('filter.save2new');
				}
			}

			// If an existing item, can save as a copy
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('filter.save2copy');
			}

			JToolbarHelper::cancel('filter.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_SEARCH_FILTERS_EDIT');
	}
}
