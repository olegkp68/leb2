<?php
class OPCHikaUrl {
	public static function getActionUrlCheckout() {
		
		$root = JURI::root(true); 
			if (substr($root, -1) !== '/') $root .= '/'; 
			
			$action_url = $root.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;controller=hikaopc&amp;task=submitstep&amp;nosef=1';
			
			$p_ty = OPCHikaconfig::get('product_id_ty', false); 
			if (empty($p_ty))
			if (!empty($p_id)) {
				$action_url .= '&amp;product_id='.(int)$p_id; 
			}
			
			
			
			
			
			if (!empty($lang))
		    $action_url .= '&amp;lang='.$lang; 
			
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
			
		    $op_customitemidty = OPCconfig::getValue('hikaopc_config', 'op_customitemidty', 0, 0, true); 
			
			if (!empty($op_customitemidty))
			$action_url .= '&Itemid='.$op_customitemidty; 
		
		return $action_url; 
	}
	
	public static function appendFormVars(&$html) {
		$html .= '<input type="hidden" name="task" id="task" value="submitstep" />'; 
		$html .= '<input type="hidden" name="controller" id="controller" value="cart" />'; 
		$html .= '<input type="hidden" name="view" id="view" value="checkout" />'; 
		$html .= '<input type="hidden" name="ctrl" id="ctrl" value="checkout" />'; 
		$html .= '<input type="hidden" name="option" id="option" value="com_hikashop" />'; 
	}
}