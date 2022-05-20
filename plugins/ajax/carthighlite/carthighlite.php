<?php defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

class plgAjaxCarthighlite extends JPlugin
{

	function onAjaxCarthighlite()
	{
		$my = JFactory::getUser();
		$userid = $my->get('id');
		if (!$userid) return;

		$session = JFactory::getSession();
		$cartSession = $session->get('vmcart', 0, 'vm');
		$cartSession = json_decode($cartSession);
		foreach ($cartSession->cartProductsData as $cart) {
			$data[] = $cart->virtuemart_product_id;
		}
		$data = json_encode($data);
		return $data;
	}
}
