<?php

// No direct access
defined('_JEXEC') or die;
JHTMLOPC::script('opcbe.js', 'administrator/components/com_onepage/assets/js/', false);
?>
<form action="<?php echo JURI::base(); ?>index.php?option=com_onepage&amp;controller=config" method="post" name="adminForm" id="adminForm">

<input type="hidden" name="<?php if (method_exists('JUtility', 'getToken'))
	echo JUtility::getToken();
	else echo JSession::getFormToken(); ?>" value="1" />

	<input type="hidden" name="option" value="com_onepage" />
		<input type="hidden" name="view" value="ext" />
		<input type="hidden" name="task" id="task" value="save" />
		<input type="hidden" name="task2" id="task2" value="" />
		<input type="hidden" name="delete_ht" id="delete_ht" value="0" />
		<input type="hidden" name="backview" id="backview" value="panel1" />	
<?php
include(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'ext'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'default_ext.php'); 
?></form>
<?php

