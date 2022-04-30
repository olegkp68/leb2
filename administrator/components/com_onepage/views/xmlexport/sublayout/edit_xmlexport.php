<?php
/**
 * @version		
 * @package		RuposTel OnePage Utils
 * @subpackage	com_onepage
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHTMLOPC::script('opcbe.js', 'administrator/components/com_onepage/assets/js/', false);
foreach ($this->opc_forms as $entity=>$form)
{
	?><fieldset class="adminform"><?php
	echo $form; 
	
	
	

	
$base = JURI::base(); 
$jbase = str_replace('/administrator', '', $base); 	
if (substr($jbase, -1) !== '/') $jbase .= '/'; 
?>


		

	     <a class="btn btn-primary" href="<?php echo $jbase.'index.php?option=com_onepage&view=xmlexport&task=getlist&lang=en&entity='.urlencode($entity).'&type=xls'; ?>"><?php echo JText::_('COM_ONEPAGE_CATEGORY_XLS_LINK'); ?></a>
		 <br /> 	  
		 <a href="index.php?option=com_onepage&view=pairing&asset=virtuemart_category_id&entity=<?php echo urlencode($entity); ?>&type=xmlexport#uploadXLS"><?php echo JText::_('COM_ONEPAGE_EXPORT_UPLOADNOTE'); ?></a>
	
	</fieldset><?php
}
