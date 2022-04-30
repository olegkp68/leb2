<?php

/*
 * Created on 19.05.2020
 *
 * Author: stt
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemstt_cartusave extends JPlugin
{

	function __construct($event, $params)
	{
		parent::__construct($event, $params);
	}

	function onAfterRender()
	{
		$user = JFactory::getUser();
		$userid = $user->get('id');
		$input = JFactory::getApplication()->input;
		if ($userid && $input->getCmd('option') == 'com_virtuemart' && $input->getCmd('view') == 'cart') {
			$this->putCart();
		}
		if ($userid && $input->getCmd('option') == 'com_sttvmorder' && $input->getCmd('view') == 'cart') {
			$this->putCart();
		}
	}

	function onAfterRoute()
	{
		$session = null;
		if (JFactory::getApplication()->isAdmin()) {
			return;
		}
		$user = JFactory::getUser();
		$userid = $user->get('id');
		$input = JFactory::getApplication()->input;
		if ($userid && $input->getCmd('option') == 'com_virtuemart' && $input->getCmd('view') == 'cart' && $input->getCmd('task') == 'addJS' && $input->getCmd('format') == 'json') {
			if (!class_exists('VmConfig'))
				require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
			VmConfig::loadConfig();
			require_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/image.php';
			if (!class_exists('VirtueMartCart'))
				require(JPATH_VM_SITE . '/helpers/cart.php');
			if (!class_exists('shopFunctionsF'))
				require(JPATH_VM_SITE . '/helpers/shopfunctionsf.php');

			//			$this->json = new stdClass();
			$lang = JFactory::getLanguage();
			$lang->load('com_virtuemart');
			VmConfig::setdbLanguageTag();
			$cart = VirtueMartCart::getCart(false);
			if ($cart) {
				$this->putCart();
			}
			return;
		}
	}

	function putCart()
	{
		$user = JFactory::getUser();
		$userid = $user->get('id');
		if (!$userid)
			return;

		$db = JFactory::getDBO();
		//		$db->setQuery('DELETE FROM #__sttcartusave WHERE userid=' . $db->Quote($userid));
		//		$db->query();

		$session = JFactory::getSession();
		$cartSession = $session->get('vmcart', 0, 'vm');
		if (!empty($cartSession)) {
			$db->setQuery("INSERT INTO #__sttcartusave (created,vmcart,userid) values(NOW()," .
				$db->Quote(base64_encode($cartSession)) .
				', ' . $db->Quote($userid) . ") ON DUPLICATE KEY UPDATE created=NOW(), vmcart="  .
				$db->Quote(base64_encode($cartSession)));
			$db->query();
		}
	}
}
