<?php
defined('_JEXEC') or die();

 
jimport('joomla.form.formfield');

class JFormFieldGeneratezas extends JFormField {
	  var $_name = 'generatezas';
	  function getInput() {
		  
		  $savedir = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'zasilkovnaopc'.DIRECTORY_SEPARATOR.'js'; 
		$file = $savedir.DIRECTORY_SEPARATOR.'zasilkovna.json'; //stan moved to media instead cache
		if (!file_exists($file)) {
			JFactory::getApplication()->enqueueMessage('Pozor - pobocky niesu nacitane, pouzite odkaz cez nastavenie zasilkovnaopc pluginu'); 
		}
		  
		  $plugin = JPluginHelper::getPlugin('vmshipment', 'zasilkovnaopc');
		  $params = new JRegistry($plugin->params);
		  $cron_key = $params->get('cronkey', ''); 
		  if (!empty($cron_key)) {
		  $url = JRoute::_('index.php?option=com_virtuemart&view=vmplg&task=ShipmentResponseReceived&cmd=generatezasilkovna&format=raw&cronkey='.$params->get('cronkey', ''), true, true, true);
		  $url = str_replace('/administrator/', '/', $url); 
		ob_start(); 
		?><div>Pobocky je mozne generovat prostrednictvom tohto odkazu: <a href="<?php echo $url; ?>"><?php echo $url; ?>. Zasilkovna vypina pobocky aj podla ich pretazenosti a preto je vhodne zapnut CRON synchronizaciu pobociek na kazdych 15 minut.</a></div>
		
		<?php
		$html = ob_get_clean(); 
		
		
		  }
		  else {
			  JFactory::getApplication()->enqueueMessage('Nastavte Cron URL Key v Joomla -> Plugins -> zasilkovnaopc -> Cron URL Key pre generovanie pobociek cez URL adresu'); 
			  $html = 'Nastavte Cron URL Key v Joomla -> Plugins -> zasilkovnaopc -> Cron URL Key pre generovanie pobociek cez URL adresu'; 
		  }
		$html .= '<br />Pobočky sa stiahnu z tejto URL adresy: <a target="_blank" href="https://www.zasilkovna.cz/api/v4/'.$params->get('zasilkovna_api_pass', '{zasilkovna_api_pass}').'/branch.json">https://www.zasilkovna.cz/api/'.$params->get('zasilkovna_api_pass', '{zasilkovna_api_pass}').'/v3/branch.json</a>';
		return $html; 
		
    }


}

