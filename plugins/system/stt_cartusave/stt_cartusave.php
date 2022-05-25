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
		//-------------------------------------------------------------------
		$user = JFactory::getUser();
		$userid = $user->get('id');
		if (!$userid) return;

		$db = JFactory::getDBO();

		$session = JFactory::getSession();
		$cartSession = $session->get('vmcart', 0, 'vm');
		//-----------------
		$cartProductsData = json_decode($cartSession);
		$cart_ids = [];
		foreach ($cartProductsData->cartProductsData as $cart) {
			$cart_ids[] = (string)$cart->virtuemart_product_id;
		}

		//-----------------
		if (!empty($cartSession)) {
			$db->setQuery("INSERT INTO #__sttcartusave (created,vmcart,userid,vmprod_id) values(NOW()," .
				$db->Quote(base64_encode($cartSession)) .
				', ' . $db->quote($userid) . ', ' . $db->Quote(json_encode($cart_ids)) . ") ON DUPLICATE KEY UPDATE created=NOW(), vmcart="  .
				$db->Quote(base64_encode($cartSession)));
			$db->query();
		}
		//---------------------
		$db->setQuery("SELECT vmprod_id FROM #__sttcartusave WHERE userid=" . $db->quote($userid));
		$db->query();
		$res = (array)json_decode($db->loadResult());
		$p_add = NULL;
		if ($res) {
			$p_add = [];
			$diffs_to_bd = array_diff($cart_ids, $res);
			$diffs_to_cart = array_diff($res, $cart_ids);
			if ($diffs_to_bd == null && $diffs_to_cart == null) {
				$p_add = NULL;
			}

			if ($diffs_to_cart) {
				$unpub[] = 0;
				$cart = VirtueMartCart::getCart(true);
				$cart->prepareCartData();

				foreach ($diffs_to_cart as $key => $diff_to_cart) {
					$add_cart = true;
					$db->setQuery("SELECT published FROM #__virtuemart_products WHERE virtuemart_product_id = " . $diff_to_cart);
					$db->query();

					if ($db->loadResult()) {
						if ($key != $diff_to_cart) { //будет удалено из корзины и БД
							$add_cart = false;
						} else {
							unset($diffs_to_cart[$key]);
							unset($res[$key]);
							$res[count($res) + 1] = $diff_to_cart;
						}
					} else {
						$unpub[(string)$diff_to_cart] = (string)$diff_to_cart;
						$add_cart = false;
					}

					if ($add_cart) {
						JFactory::getApplication()->input->set('quantity', array(1));
						$cart->add(array($diff_to_cart));
					}
				}
				$p_add = array_diff($res, $diffs_to_cart) + $unpub;
			}
			if ($diffs_to_bd) {
				$p_add = array_merge($res, $diffs_to_bd);
			}
		} else {
			$p_add = $cart_ids;
		}

		if (isset($p_add)) {
			$db->setQuery("UPDATE #__sttcartusave SET vmprod_id=" . $db->Quote(json_encode($p_add)) . " WHERE userid=" . $db->Quote($userid));
			$db->query();
		}
		//---------------------

	}
}
