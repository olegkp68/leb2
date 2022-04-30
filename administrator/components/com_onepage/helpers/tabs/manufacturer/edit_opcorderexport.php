<?php
defined('_JEXEC') or die;
foreach ($this->opc_export_forms as $f) {
	
	?><fieldset><?php
	
	echo $f['params'];
	?><div style="display: none;"><?php 
	  $x = 	str_replace(array('name="', 'name=\'', 'id="', 'id=\''), array('name="was_', 'name=\'was_', 'data-was-id="', 'data-was-id=\''),$f['params']); 
	  echo $x; 
	?></div>
	
	</fieldset><?php
}
echo $this->opc_export_general;

JHTMLOPC::script('opcbe.js', 'administrator/components/com_onepage/assets/js/', false);


