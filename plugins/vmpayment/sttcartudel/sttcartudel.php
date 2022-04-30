<?php
defined('_JEXEC') or die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');
if (!class_exists('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

class plgVmPaymentSttCartudel extends vmPSPlugin 
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

    function plgVmConfirmedOrder($cart, $order) 
    {
		//очистим сохраненную корзину после подтверждения заказа
		$user = JFactory::getUser();
		$userid = $user->get('id'); 
		if(!$userid) return;
		$db = JFactory::getDBO();
		$db->setQuery('DELETE FROM #__sttcartusave WHERE userid=' . $db->Quote($userid));
		$db->query();		
		
		return;
    } 
	
}
