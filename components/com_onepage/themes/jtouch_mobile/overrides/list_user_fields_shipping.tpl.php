<?php if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
*
* @copyright Copyright (C) 2007 - 2010 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

	/**
	 * This allows us to print the user fields on
	 * the various sections of the shop
	 *
	 * @param array $rowFields An array returned from ps_database::loadObjectlist
	 * @param array $skipFields A one-dimensional array holding the names of fields that should NOT be displayed
	 * @param ps_DB $db A ps_DB object holding ovalues for the fields
	 * @param boolean $startform If true, print the starting <form...> tag
	 * 
	 * content of this file is a modification of function listUserFields( $rowFields, $skipFields=array(), $db = null, $startForm = true ) 
	 * of Virtuemart 1.1.7
	 */
	 
	{
	
		
		
		// we can overrride the default shipping country here
  $current_lang = JFactory::getLanguage();
  
  if (!empty($current_lang))
  if (isset($current_lang->_lang))
  if (!empty($default_country_array))
  if (!empty($default_country_array[$current_lang->_lang]))
  if (strlen($default_country_array[$current_lang->_lang])==3)
   $default_shipping_country = $default_country_array[$current_lang->_lang];
		
		$default['country'] = $default_shipping_country;
		
		$missing = '';

		// collect all required fields
		$required_fields = Array(); 

		
		
		
		
	
		echo '
		<div class="all_fields">';
		
		// Form validation function
		
		$delimiter = 0;
		
	   	foreach( $rowFields['fields'] as $field) {
			if (empty($field['type'])) continue;
			if (!empty($field['readonly'])) 
			{
			$field['readonly'] = false;
			$readonly = ' readonly="readonly" '; 
			}
			else $readonly = ''; 
			
			if (!empty($field['title'])) {
			$name = str_replace('"', '\"', $field['title']); 
			$field['formcode'] = str_replace('name="'.$field['name'], 'placeholder="'.$name.'" name="'.$field['name'], $field['formcode']); 
			}
			
	   	    $maxlength = ''; 
			
	   		// Title handling.
	   		$key = $field['title'];
			
	   		if( $field['name'] == 'agreed') {
				// we've got this in the unlogged, logged file
				continue; 
	   		
			}
			if (((!empty($op_usernameisemail) && ($field['name'] == 'opc_password') ) ) && VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' ) {
				echo '<div data-role="fieldcontain" >
						<label for="register_account">'.OPCLang::_('COM_VIRTUEMART_ORDER_REGISTER').'</label>
						<input type="checkbox"  id="register_account" name="register_account" value="1" class=" " onclick="return Onepage.showFields( this.checked, new Array( \'password\', \'password2\') );" autocomplete="off" '; 
						if (empty($op_create_account_unchecked)) echo ' checked="checked"  ';
						echo ' />
						
					</div>
					';
			}
			else
			{
			 if (((!empty($op_usernameisemail) && ($field['name'] == 'opc_password') ) ) && VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) 
			  echo '<input type="hidden" id="register_account" name="register_account" value="1" />';
			}
			
	   		if(( $field['name'] == 'username')  && VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' ) {
			
				echo '<div data-role="fieldcontain">
						<label for="register_account">'.OPCLang::_('COM_VIRTUEMART_ORDER_REGISTER').'</label>
						<input type="checkbox"  id="register_account" name="register_account" value="1" class=" " onclick="return Onepage.showFields( this.checked, new Array(\'username\', \'password\', \'password2\') );" '; 
						if (empty($op_create_account_unchecked)) echo ' checked="checked"  ';
						echo ' autocomplete="off" />
						
					</div>
					';
			} elseif( $field['name'] == 'username' ) {
			if (((empty($op_usernameisemail) && ($field['name'] == 'username') ) ) && VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) 
				echo '<input type="hidden" id="register_account" name="register_account" value="1" />';
			}
	   		// a delimiter marks the beginning of a new fieldset and
	   		// the end of a previous fieldset
			/*
			if (false)
	   		if( $field['type'] == 'delimiter') {
	   			if( $delimiter > 0) {
	   				echo '<div class="op_hr" >&nbsp;</div>';
	   			}
	   			if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' && $field->title == OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_CUST_INFO_LBL') && $page == 'checkout.index' ) {
	   				continue;
	   			}
	   			echo '
				    
';
	   			$delimiter++;
	   			continue;
	   		}
			*/
			

	   		
							if( !empty( $field['required'] )) {
							$field['title']=$field['title']." *";
							}

	      	echo '<div '; 
			
			$arr = array('select', 'dropdown','multicheckbox', 'multiselect', 'radio','checkbox','textarea', 'multidrop' );
			if (in_array($field['type'], $arr)) {
			  $field['formcode'] = str_replace('id=', ' data-role="none" data-enhance="false" id=', $field['formcode']); 
			}
				
			echo ' data-role="fieldcontain" data-min="true" '; 
		
			echo '		   class=" '; 
		   echo $field['type']; 
		   echo '" id="'.$field['name'].'_input" title="'.$field['title'].'" ';
		    if (VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION')
			if (!empty($op_create_account_unchecked))
			switch ($field['name'])
			{
			case 'password':
			case 'password2': 
			case 'opc_password':
			case 'opc_password2': 
			case 'username': 
			
			   echo ' style="display: none;" '; 
			 
			 break; 
			}
		    echo '>'."\n";
	      	
			$arr = array('select', 'dropdown','multicheckbox', 'multiselect', 'radio','checkbox','textarea', 'multidrop', 'date' );

			//
			$addfieldset = false; 
		    if (in_array($field['type'], $arr))
			{
			?>
			   <fieldset data-role="controlgroup">
			   <legend><?php echo $field['title']; ?></legend>
			 <?php
			 $addfieldset = true; 
			}

			if (!$addfieldset)
			{
	        echo '<label class="label_selects" for="'.$field['name'].'_field">';
			echo $field['title']; 
			echo '</label>'; 
			}
			
			
			/*
			echo '<span id="'.$field['name'].'_div" style="display: none;" ';
	   		
	   		
			if ($op_create_account_unchecked)
			 {
			   if (($field['name'] == 'opc_password') || ($field['name'] == 'password') || ($field['name'] == 'username') || ($field['name'] == 'password2')) echo ' style="display: none;" '; 
			 }
			echo '>'; 
			if (!empty($field['required']))
			echo OPCLang::_('COM_ONEPAGE_MISSING'); 
			echo '</span>';
            */
			
			
	      	/**
	      	 * This is the most important part of this file
	      	 * Here we print the field & its contents!
	      	 */
			//$field['formcode'] = str_replace('type="text"', 'onfocus="inputclear(this)" type="text"', $field['formcode']); 
	   		switch( $field['name'] ) {
	   			case 'title':
					
	   				echo $field['formcode'];
					
	   				break;
	   			
	   			case 'country':
	   				
					
	   				echo $field['formcode'];
					
	   				break;
	   			
	   			case 'state':
	   				
					
	   				echo $field['formcode']; 
					
					
					
	   				break;
				case 'agreed':
					echo $field['formcode'];
					break;
				case 'email':
				case 'username':
				    echo $field['formcode'];
					break;
				case 'opc_password':
				case 'password':
				case 'password2':
				    echo $field['formcode'];
					break;
					
	   			default:
	   				//echo $field['type'].'<br />';editorta,age_verification
	   				switch( $field['type'] ) {
					    case 'multicheckbox':
						
						 
							echo $field['formcode']; 
							
														
							
							
							break;
						case 'checkbox':
							
							echo $field['formcode']; 
							break; 
						case 'textarea': 
							echo $field['formcode']; 
							break;
						case 'radio':
						

							echo $field['formcode']; 

							break;
						case 'age_verification':

							echo $field['formcode']; 
	   						break;
						case 'editorta':
							$field['formcode'] = str_replace('style="width: 300px;', 'style="max-width:100px;', $field['formcode']); 
							echo $field['formcode'];
							break; 
	   					case 'date':
							$field['formcode'] = str_replace('type="text"', 'type="date"', $field['formcode']);
							echo $field['formcode']; 
							
					        
	   						break;
	   					case 'text':
	   					case 'emailaddress':
	   					case 'webaddress':
	   					case 'euvatid':	   			
							if ($field['name'] == 'username')
							if (substr($field['title'], strlen($field['title'])-1)!='*') $field['title'] .= '*';
	   						
							
					        //echo '<input type="text" id="'.$field['name'].'_field" alt="'. $field['title'] .'" onfocus="inputclear(this)" name="'.$field['name'].'" value="" class="inputbox" '.$maxlength . $readonly . ' />'."\n";
							echo $field['formcode']; 
				   			break;
				   			
						case 'delimiter':
							echo ''; //<div style="margin-top: 20px; clear: both;">&nbsp;</div>'; 
							break;
						case 'multicheckbox':
						case 'select':
						
						

							
							echo $field['formcode']; 

							break;
					  case 'multiselect':
						
							
							if (strpos($field['formcode'], 'multiple')!==false)
							{
								$field['formcode'] = str_replace('class="', 'class="multidrop ', $field['formcode']); 
							}
							echo $field['formcode']; 

							break;
						default: 
						  
						  echo $field['formcode']; 
						  
						  break;
	   				}
	   				break;
	   		}
	   		
			
			if ($addfieldset)
			 {
			   echo '</fieldset>'; 
			 }
	   		echo '</div>
				  ';
	   }
		if( $delimiter > 0) {
		
		if( !empty( $required_fields ))  {
			echo '<div style="clear: both; padding:5px;text-align:center;"><strong>(* = '.OPCLang::_('CMN_REQUIRED').')</strong></div>';
		  	 
		}
			echo "\n";
		}
	   
	   	   echo '</div>';

	}


?>