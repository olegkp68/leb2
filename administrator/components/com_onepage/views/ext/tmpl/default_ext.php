<?php

// No direct access
defined('_JEXEC') or die;


?><fieldset><legend><?php echo JText::_('COM_ONEPAGE_OPC_EXTENSIONS'); ?></legend>
	<?php 
	/*
	if (empty($this->exthtml)) echo JText::_('COM_ONEPAGE_OPC_EXTENSIONS_NOEXT'); 
	else
	echo $this->exthtml; 
	*/
	?><input type="hidden" name="ext_id" value="" rel="" id="ext_id" /><table class="admintable table table-striped" id="extension_list">
	<?php
	if (!empty($this->opcexts))
	 {
	   $li = 0; 
	   foreach ($this->opcexts as $ext)
	    {
		
		  echo '<tr>'; 
		  echo '<td>'.$ext['name'].'</td>';  
		  echo '<td>'.$ext['description'].'</td>'; 
		  if (!empty($ext['data']))
		  {
		  if (!empty($ext['link']))
		  		  echo '<td><div style="color: green;" href="'.$ext['link'].'">'.JText::_('COM_ONEPAGE_INSTALLED').'<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_UPDATE_OPCEXTENSION').'" /></div></td>'; 
		  else
echo '<td style="color: green;">'.JText::_('COM_ONEPAGE_INSTALLED').'<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_UPDATE_OPCEXTENSION').'" /></td>'; 
		  //
		  }
		  else
		   {
		   echo '<td>'; 
		   echo '<input type="button" class="btn btn-small btn-success"  onclick="javascript: installExt(\''.$li.'\');" value="'.JText::_('COM_ONEPAGE_INSTALL_OPCEXTENSION').'" />';
		   echo '&nbsp;</td>'; 		   
		   }
		  echo '</tr>'; 
		
		
		 $li++; 
		}
	 }
	 
	?></table>
</fieldset>

