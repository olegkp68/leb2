<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


?><!-- BEGIN GCR Opt-in Module Code -->
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async="acync" defer="defer"></script>

<?php
$created_on = $this->order['details']['BT']->created_on; 
$date = date('Y-m-d', strtotime($created_on.'  + '.(int)$this->params->DELIVERY_DATE_ADD.' days')); 


$lang = $this->order['details']['BT']->order_language; 
$lang = str_replace('-', '_', $lang); 
$google_langs = array('af', 'ar-AE', 'cs', 'da', 'de', 'en_AU', 'en_GB', 'en_US', 'es', 'es-419', 'fil', 'fr', 'ga', 'id', 'it', 'ja', 'ms', 'nl', 'no', 'pl', 'pt_BR', 'pt_PT', 'ru', 'sv', 'tr', 'zh-CN', 'zh-TW'); 
$selected_lang = '';
if (in_array($lang, $google_langs)) {
	$selected_lang = $lang; 
}
else {
	$ea = explode('_', $lang);
	if (count($ea) === 2) {
	$nl = strtolower($ea[0]).'_'.strtoupper($ea[1]); 
	if (in_array($nl, $google_langs)) {
		$selected_lang = $nl; 
	}
	else {
		if (in_array($ea[0], $google_langs)) {
			$selected_lang = $ea[0]; 
		}
	}
	}
}
?>

<script>
  window.renderOptIn = function() { 
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          // REQUIRED
          "merchant_id":"<?php echo $this->params->MERCHANT_ID; ?>",
          "order_id": "<?php echo htmlentities($this->idformat); ?>",
          "email": "<?php echo htmlentities($this->order['details']['BT']->email); ?>",
          "delivery_country": "<?php echo htmlentities($this->order['details']['BT']->country_2_code); ?>",
          "estimated_delivery_date": "<?php echo $date; ?>",

          // OPTIONAL
          "opt_in_style": "<?php echo $this->params->OPT_IN_STYLE; ?>"
        }); 
     });
  }
</script>
<!-- END GCR Opt-in Module Code -->

<?php
if (!empty($selected_lang)) {
?>

<!-- BEGIN GCR Language Code -->
<script>
  window.___gcfg = {
    lang: '<?php echo $selected_lang; ?>'
  };
</script>
<!-- END GCR Language Code -->
<?php 
}

$this->isPureJavascript = false; 
?>