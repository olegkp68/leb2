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
		if (!class_exists('VirtueMartModelProduct')) {
			JLoader::import('product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
		}

		//-------------------------------------------------------------------
		$user = JFactory::getUser();
		$userid = $user->get('id');
		if (!$userid) return;

		$db = JFactory::getDBO();

		$session = JFactory::getSession();
		$cartSession = $session->get('vmcart', 0, 'vm');
		//-----------------

		$db = JFactory::getDBO();
		// $db->setQuery(true);
		// $add = true;

		$q = "SELECT vmcart FROM #__sttcartusave WHERE userid=" . $db->Quote($userid);
		$db->setQuery($q);
		$r = $db->loadResult();
		if ($r) {
			$cookcart = base64_decode($r);
			//--------------------------------------------- сохраним корзину из БД
			$cookcart_old = json_decode($cookcart);
			//------------
			if ($cookcart) {
				// $session->set('vmcart', $cookcart, 'vm');
				$cart = VirtueMartCart::getCart(true);
				$cart->prepareCartData();
				// --------------


				$db->setQuery("SELECT vmprod_id FROM #__sttcartusave WHERE userid=" . $db->quote($userid));
				$db->query();

				$vmprods_ttx = json_decode($db->loadResult());
				foreach ($vmprods_ttx as $ke => $vmprod_ttx) {
					// if ($ke == 'virtuemart_product_id') {
					$vmprod_ttx = json_decode($vmprod_ttx);
					$vmprods_id[] = $vmprod_ttx->virtuemart_product_id;
					// break;
					// }
				}

				if ($vmprods_id) {
					foreach ($vmprods_id as $key => $vmprod_id) {

						$p_add[$key] = $vmprod_id;
						$db->setQuery("SELECT published FROM #__virtuemart_products WHERE virtuemart_product_id = " . $vmprod_id);
						$db->query();
						if ($db->loadResult()) {
							$add_cart = true;
							foreach ($cart->products as $product) {
								if ($product->virtuemart_product_id == $vmprod_id) {
									$add_cart = FALSE;
									break;
								}
							}
							if ($add_cart) {
								JFactory::getApplication()->input->set('quantity', array($vmprod_ttx->quantity));
								$cpd = json_decode(json_encode($vmprod_ttx->customProductData), true);
								$cpd2[$vmprod_id] = $cpd;
								$_REQUEST['customProductData'] = $cpd2;
								$_REQUEST["virtuemart_product_id"] = array($vmprod_id, $vmprod_id);
								$cart->add(array($vmprod_id));
							}
							unset($p_add[$key]);
						}
					}
				}



				foreach ($cart->products as $p_new) {
					$new[] = (int)$p_new->virtuemart_product_id;
				}

				foreach ($cookcart_old->cartProductsData as $p_old) {
					$old[] = $p_old->virtuemart_product_id;
				}
				$p_add2 = array_diff($old, $new);

				if ($p_add) {
					$p_add = array_merge($p_add, $p_add2);
				} else {
					$p_add = $p_add2;
				}

				foreach ($p_add as $key => $published) {
					$db->setQuery("SELECT published FROM #__virtuemart_products WHERE virtuemart_product_id = " . $published);
					$db->query();
					if ($db->loadResult()) {
						unset($p_add[$key]);
						// $add = false;
					} else {
						foreach ($cookcart_old->cartProductsData as $k => $ttx) {
							if ($ttx->virtuemart_product_id == $published) {
								$p_add_ttx[] = json_encode($ttx);
							}
						}
					}
					$p_add = $p_add_ttx;
				}
				//----

			}
		}

		//-----------------
		if (!empty($cartSession)) {
			$db->setQuery("INSERT INTO #__sttcartusave (created,vmcart,userid,vmprod_id) values(NOW()," .
				$db->Quote(base64_encode($cartSession)) .
				', ' . $db->quote($userid) . ', ' . $db->Quote(json_encode($p_add)) . ") ON DUPLICATE KEY UPDATE created=NOW(), vmcart="  .
				$db->Quote(base64_encode($cartSession)) . ', vmprod_id=' . $db->Quote(json_encode($p_add)));
			$db->query();

			// $db->setQuery("UPDATE #__sttcartusave SET vmprod_id=" . $db->Quote(json_encode($p_add)) . " WHERE userid=" . $db->Quote($userid));
			// $db->query();
		}
	}
}
