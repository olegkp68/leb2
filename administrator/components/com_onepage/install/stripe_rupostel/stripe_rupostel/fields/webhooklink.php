<?php
defined('_JEXEC') or die();

 
jimport('joomla.form.formfield');

class JFormFieldWebhooklink extends JFormField {
	  var $_name = 'webhooklink';
	  function getInput() {
		  
		  
		  $plugin = JPluginHelper::getPlugin('vmshipment', 'zasilkovnaopc');
		  $params = new JRegistry($plugin->params);
		  $sandbox = $params->get('mode', 0); 
		  if (!empty($sandbox)) {
			  JFactory::getApplication()->enqueueMessage('This BrainTree plugin runs in Sandbox mode', 'notice'); 
		  }
		  $secret = JFactory::getConfig()->get('secret'); 
		  $hash = JApplication::getHash($secret); 
		  
		  $virtuemart_paymentmethod_id = JRequest::getVar('cid', array()); 
		  if (is_array($virtuemart_paymentmethod_id)) $virtuemart_paymentmethod_id = reset($virtuemart_paymentmethod_id); 
		  else $virtuemart_paymentmethod_id = (int)$virtuemart_paymentmethod_id; 
		  
		  if (empty($virtuemart_paymentmethod_id)) {
			  echo 'Please click save to display webhook URL'; 
		  }
		  else {
		  
		  $url = JRoute::_('index.php?option=com_virtuemart&view=vmplg&task=pluginNotification&cmd=notification&format=opchtml&webhooksecret='.$hash.'&virtuemart_paymentmethod_id='.(int)$virtuemart_paymentmethod_id, true, true, true);
		  $url = str_replace('/administrator/', '/', $url); 
			
		  ob_start(); 
		?><div>Please configure your Stripe Account with this Webhook URL: <br /><a target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a><br />When using multiple RuposTel Stripe Plugins in Virtuemart, you need to provide JUST ONE webhook link. The payment ID within the URL does not play a role as far as it is stripe_rupostel plugin. Changing secret in configuration.php will lead to a new webhook secret. Please configure Stripe signing secret in Joomla plugins section for this plugin (same as API keys)</div>
		
		<?php
		$html = ob_get_clean(); 
		  }
		

		
		return $html; 
		
    }


}