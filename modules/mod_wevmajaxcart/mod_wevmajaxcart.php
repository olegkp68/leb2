<?php
/**
 * @version     1.0.0
 * @package     mod_wevmajaxcart
 * @copyright   WEB EXPERT SERVICES LTD / Web-expert.gr - Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Stergios Zgouletas <info@web-expert.gr> - http://web-expert.gr
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
VmConfig::loadConfig();
vmLanguage::loadJLang('mod_virtuemart_cart', true);
vmLanguage::loadJLang('com_virtuemart', true);
vmJsApi::jQuery();

vmJsApi::removeJScript("/modules/mod_virtuemart_cart/assets/js/update_cart.js");
vmJsApi::addJScript("/modules/mod_wevmajaxcart/assets/js/update_cart.js",false,false);

if($params->get('preloadcart',0))
{
	$cart = VirtueMartCart::getCart(false);
	$viewName = vRequest::getString('view',0);
	$checkAutomaticPS=$viewName=='cart';
	$data = $cart->prepareAjaxData();
}
else
{
	$data = new stdClass();
	$data->products=array();
	$data->totalProduct=0;
	$data->billTotal=0;
	$data->totalProductTxt='';
	$data->cart_show='';
}

$currencyDisplay = CurrencyDisplay::getInstance( );
vmJsApi::cssSite();
$moduleclass_sfx 	= $params->get('moduleclass_sfx', '');
$show_price 		= (bool)$params->get( 'show_price', 1 ); // Display the Product Price?
$show_product_list 	= (bool)$params->get( 'show_product_list', 1 ); // Display the Product Price?

$layMod= (int)$params->get('layoutmod', 0)? 'mod_virtuemart_cart':'mod_wevmajaxcart';
require JModuleHelper::getLayoutPath($layMod, $params->get('layout', 'default'));
echo vmJsApi::writeJS();