<?php
defined('_JEXEC') or die;
class plgUserSttcartlogin extends JPlugin
{
	public function onUserLogin($user, $options = array())
	{
		// этот плагин должен быть в конце порядка следования, чтобы запускаться после плагина Пользователь - Joomla
		$my = JFactory::getUser();
		$userid = $my->get('id');
		if (!$userid) return;
		if (!class_exists('VmConfig')) require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
		VmConfig::loadConfig();
		VmConfig::loadJLang('com_virtuemart', true);
		if (!class_exists('VirtueMartCart')) require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		if (!class_exists('CurrencyDisplay')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'currencydisplay.php');
		//require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'vmecho.php');
		//require_once(VMPATH_ADMIN . '/helpers/vmecho.php');

		$session = JFactory::getSession($options);
		$cartSession = $session->get('vmcart', 0, 'vm');
		// начальное содержимое корзины 
		$cartSession = json_decode($cartSession);
		$db = JFactory::getDBO();

		$add = FALSE;

		$q = "SELECT vmcart FROM #__sttcartusave WHERE userid=" . $db->Quote($userid);
		$db->setQuery($q);
		$r = $db->loadResult();
		if ($r) {
			$cookcart = base64_decode($r);
			//--------------------------------------------- сохраним корзину из БД
			$cookcart_old = json_decode($cookcart);
			//------------
			if ($cookcart) {
				$session->set('vmcart', $cookcart, 'vm');
				$cart = VirtueMartCart::getCart(true);
				$cart->prepareCartData();
				// --------------


				$db->setQuery("SELECT vmprod_id FROM #__sttcartusave WHERE userid=" . $db->quote($userid));
				$db->query();

				$vmprods_id = json_decode($db->loadResult());
				if ($vmprods_id) {
					foreach ($vmprods_id as $key => $vmprod_id) {
						$add = FALSE;
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
								JFactory::getApplication()->input->set('quantity', array(1));
								$cart->add(array($vmprod_id));
								$add = TRUE;
							}
							unset($p_add[$key]);
						}
					}
				}
				//$db->setQuery("UPDATE #__sttcartusave SET vmprod_id=" . $db->quote(json_encode($p_add)) . " WHERE userid=" . $db->Quote($userid));
				//$db->query();


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


				$db->setQuery("UPDATE #__sttcartusave SET vmprod_id=" . $db->Quote(json_encode($p_add)) . " WHERE userid=" . $db->Quote($userid));
				$db->query();



				//----
				$addprod = $this->params->get('addprod', 1);
				if ($addprod && isset($cartSession->cartProductsData) && count($cartSession->cartProductsData)) {
					//если есть товары из начальной корзины, нужно их добавить в новую корзину (если их там еще нет)
					foreach ($cartSession->cartProductsData as $p) {
						if (isset($p->virtuemart_product_id)) {
							$add = true;
							foreach ($cart->products as $product) {
								if ($product->virtuemart_product_id == $p->virtuemart_product_id) {
									$add = FALSE;
									break;
								}
							}
							if ($add) {
								JFactory::getApplication()->input->set('quantity', array($p->quantity));
								$cart->add(array($p->virtuemart_product_id));
							}
						}
					}
				}
				//если есть продвигаемый товар, проверим его наличие в корзине и добавим в случае отсутствия
				$product_id = $this->params->get('product_id', '');
				if ($product_id) {
					$add = true;
					foreach ($cart->products as $product) {
						if ($product->virtuemart_product_id == $product_id) {
							$add = FALSE;
							break;
						}
					}
					if ($add) {
						JFactory::getApplication()->input->set('quantity', array(1));
						$cart->add(array($product_id));
					}
				}
			}
		}
		if ($add) {
			vmInfo('COM_VIRTUEMART_CART_PRODUCT_NO_AVAILABLE_ADD');
		}

		return true;
	}
}
