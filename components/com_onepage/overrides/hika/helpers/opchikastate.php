<?php
class OPCHikaState {
	
	public static function set($key, $val) {
		$session = JFactory::getSession(); 
		$data = $session->get('opc_fields', ''); 
		if (!empty($data)) {
			$fields = @json_decode($data, true); 
		}
		else
		{
			$fields = array(); 
		}
		
		if ($val === true) $val = 1; 
		if ($val === false) $val = 0; 
		if ($val === 'true') $val = 1; 
		if ($val === 'false') $val = 0; 
		
		$fields[$key] = $val; 
		$txt = json_encode($fields); 
		$session = JFactory::getSession(); 
		$session->set('opc_fields', $txt); 
		
		
		
	}
	
	public static function get($key, $defaultVal) {
		$session = JFactory::getSession(); 
		$data = $session->get('opc_fields', ''); 
		if (!empty($data)) {
			$fields = @json_decode($data, true); 
		}
		else
		{
			$fields = array(); 
		}
		if (isset($fields[$key])) return $fields[$key]; 
		
		return $defaultVal; 
		
		
	}
	
	public static function storePost() {
		$session = JFactory::getSession(); 
		$data = $session->get('opc_fields', ''); 
		if (!empty($data)) {
			$fields = @json_decode($data, true); 
		}
		else
		{
			$fields = array(); 
		}
		
	
		
		
		$user_id = JFactory::getUser()->get('id'); 
		$saved_fields['user_id'] = $user_id; 
		
		$post = JRequest::get('post'); 
		
	
		
		if (!empty($post['register_account'])) 
		$saved_fields['register_account'] = true; 
		else
		$saved_fields['register_account'] = false; 
		
		$stopen = (bool)OPCHikaShipto::isOpen(); 
		$saved_fields['stopen'] = $stopen; 
		
		
		$date_picker_text = JRequest::getVar('opc_date_picker', ''); 
		$date_picker_store = JRequest::getVar('opc_date_picker_store', ''); 
		if (!empty($date_picker_store))
		{
			$saved_fields['date_picker_store'] = $date_picker_store; 
			$saved_fields['date_picker_text'] = $date_picker_text; 
		}
		
		$agreed = JRequest::getVar('tosAccepted', JRequest::getVar('agreed', false)); 
		if (!empty($agreed))
		{
			$saved_fields['agreed'] = true; 
		}
		
		//italianagreed
		$italianagreed = JRequest::getVar('italianagreed', false); 
		if (!empty($italianagreed))
		{
			$saved_fields['italianagreed'] = true; 
		}
		else
		{
			$saved_fields['italianagreed'] = false; 
		}
		
		
		$opc_is_business = JRequest::getVar('opc_is_business', false); 
		
		if (!empty($opc_is_business))
		{
			$saved_fields['opc_is_business'] = true; 
		}
		else
		{
			$saved_fields['opc_is_business'] = false; 
		}
		
		
		$acysub = JRequest::getVar('acysub', false); 
		if (!empty($acysub))
		{
			$saved_fields['acysub'] = true; 
		}
		
		$checkoutData = JRequest::getVar('checkout', array()); 
		
		if (isset($checkoutData['payment']['id'])) {
		$p_id = $checkoutData['payment']['id'];
		if (!empty($p_id))
		{
			$saved_fields['p_id'] = $p_id; 
		}
		}
		
		if (isset($checkoutData['shipping'][0]['id'])) {
		$s_id = $checkoutData['shipping'][0]['id']; 
		
		if (!empty($s_id))
		{
			$saved_fields['s_id'] = $s_id; 
		}
		}
		
		$saved_shipping_id = JRequest::getVar('saved_shipping_id', ''); 
		if (!empty($saved_shipping_id))
		{
			$saved_fields['saved_shipping_id'] = $saved_shipping_id; 
		}
		
		$third_address_opened = JRequest::getVar('third_address_opened', 0); 
		$saved_fields['third_address_opened'] = $third_address_opened; 
		
		$render_in_third_address = OPCconfig::get('render_in_third_address', array()); 
		$arr = array(); 
		foreach ($render_in_third_address as $k=>$v)
		{
			$val = JRequest::getVar('third_'.$v, ''); 
			$arr[$v] = $val; 
			
		}
		$saved_fields['RD'] = $arr; 
		
		
		
		if (!empty($post['customer_note'])) {
			$saved_fields['customer_note'] = $post['customer_note']; 
		}
		else
		if (!empty($post['customer_comment'])) {
			$saved_fields['customer_note'] = $post['customer_comment']; 
		}
		
		$txt = json_encode($saved_fields); 
		$session = JFactory::getSession(); 
		$session->set('opc_fields', $txt); 
		
	}
}