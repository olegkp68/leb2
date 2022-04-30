<?php
/**
 * @version		$Id: default.php 21837 2011-07-12 18:12:35Z dextercowley $
 * @package		RuposTel OnePage Utils
 * @subpackage	com_onepage
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$this->loadTemplate('header'); 

$data = array(); 
$data['id'] = 'test1'; 

$datastr = urlencode(json_encode($data)); 

$entity = JRequest::getVar('entity', ''); 

?><div id="vmMainPageOPC">
<h1><?php echo JText::_('COM_ONEPAGE_CATEGORY_PAIRING'); ?></h1>
<?php
$MAXPERPAGE = 5; 
$from = JRequest::getVar('from', 0); 
$from = (int)$from; 
echo JText::_('COM_ONEPAGE_FROM').' '.JTExt::_('COM_ONEPAGE_TO').' '.($from+$MAXPERPAGE).' of '.count($this->cats); 
?>

<table class="table table-bordered table-striped">
<tr><th><?php echo JText::_('COM_ONEPAGE_CATEGORY_PAIRING_YOURCATEGORY'); ?></th>
<th> <?php echo JText::_('COM_ONEPAGE_CATEGORY_PAIRING_PARTNERCATEGORY'); ?></th>
</tr>
<?php

$i2 = 0; 
$i = 0; 
$last_i = 0; 
foreach ($this->cats as $vmid => $cat)
 {
 
  $i++; 
 if ($i < $from) continue; 
 if (($i === ($MAXPERPAGE+$from)))
 {
 $last_i = $i; 
 
 

 break; 
 }
 
   ?><tr><?php
    echo '<td>'.$cat.'</td>'; 
	echo '<td>'; 
	
	
?><select class="vm-chzn-select" name="opt" onchange="updateCat(this)" ><?php

foreach ($this->data as $id=>$txt)
 {
   //$extoptions .= '<option value="'.$id.'">'.$txt.'</option>'; 
   //renderOption($entity, $vmCat, $refCat, $txt)
   echo $this->model->renderOption($entity, $vmid, $id, $txt); 
 }
 ?></select>
	
	</td>
	<td><div id="cat_id_<?php echo $vmid; ?>">&nbsp;</div>
	</td>
 
   </tr><?php
 }
 ?>
 <tr>
 <td><a class="btn btn-success" type="button" class="float_right navnext" href="index.php?option=com_onepage&view=pairing&asset=virtuemart_category_id&entity=<?php echo $entity; ?>&type=xmlexport&from=<?php echo $last_i-($MAXPERPAGE * 2); ?>"><?php echo JText::_('JPREVIOUS'); ?></a>
 </td>
 <td>
 <?php if (($last_i) < count($this->cats)) { ?>
 <a style="margin-left: 100px;" class="btn btn-success" type="button" class="float_right navnext" href="index.php?option=com_onepage&view=pairing&asset=virtuemart_category_id&entity=<?php echo $entity; ?>&type=xmlexport&from=<?php echo $last_i; ?>"><?php echo JText::_('JNEXT'); ?> .... </a>
 <?php } ?>
 </td>
 </tr>
</table>

<p>
<?php

		
$base = JURI::base(); 
$jbase = str_replace('/administrator', '', $base); 	
if (substr($jbase, -1) !== '/') $jbase .= '/'; 
?>

<a href="<?php echo $jbase.'index.php?option=com_onepage&view=xmlexport&task=getlist&lang=en&entity='.JRequest::getVar('entity','').'&type=csv'; ?>"><?php echo JText::_('COM_ONEPAGE_CATEGORY_CSV_LINK'); ?></a><br />
<a href="<?php echo $jbase.'index.php?option=com_onepage&view=xmlexport&task=getlist&lang=en&entity='.JRequest::getVar('entity','').'&type=json'; ?>"><?php echo JText::_('COM_ONEPAGE_CATEGORY_JSON_LINK'); ?></a><br />
<a href="<?php echo $jbase.'index.php?option=com_onepage&view=xmlexport&task=getlist&lang=en&entity='.JRequest::getVar('entity','').'&type=xls'; ?>"><?php echo JText::_('COM_ONEPAGE_CATEGORY_XLS_LINK'); ?></a><br />

<fieldset id="uploadXLS"><legend><?php echo JText::_('COM_ONEPAGE_CATEGORY_XLS_UPLOAD'); ?></legend>
<form id="adminForm" name="adminForm" action="index.php" method="post" enctype="multipart/form-data">
 	  
		 <input type="hidden" name="task" id="task" value="categoryxls_upload" />
		 <input type="hidden" name="option" value="com_onepage" />
		 <input type="hidden" name="view" value="pairing" />
		 <input type="hidden" name="controller" value="pairing" />
		  <input type="hidden" name="entity" value="<?php echo htmlentities(JRequest::getVar('entity','')); ?>" />
		  <label for="uploadedupdatefile"><?php echo JText::_('COM_ONEPAGE_EXPORT_GENERAL_UPLOAD'); ?>
		  <input class="form-control-upload custom-file-input btn" id="uploadedupdatefile" name="uploadedupdatefile" type="file" accept=".xls,.xlsx" />
		  </label>
		  <span class="custom-file-control"></span>
		 <input type="button" class="btn btn-primary" value="Upload File" onclick="javascript: submitbuttonCustom('categoryxls_upload');" />
	 
</form>
</fieldset>
</p>



</div>
<?php

