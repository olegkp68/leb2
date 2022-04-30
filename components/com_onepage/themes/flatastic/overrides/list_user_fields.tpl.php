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
		
		

		$missing = '';

		// collect all required fields
		$required_fields = Array(); 
		echo '<div style="width:100%;">';
				
		
		$delimiter = 0;
		
	   	foreach( $rowFields['fields'] as $field) {
		
			if (!empty($field['required']))
			{
			  $field['title'] .= ' *'; 
			}

		    $field['formcode'] = str_replace('type="', ' title="'.$field['title'].'" alt="'.$field['title'].'" placeholder="'.$field['title'].'" type="', $field['formcode']); 
		
		
			if (empty($field['type'])) continue;
			if (empty($fied['readonly'])) $field['readonly'] = false;
		    $readonly = $field['readonly'] ? ' readonly="readonly"' : '';
	   		// Title handling.
	   		$key = $field['title'];
	   		if( $field['name'] == 'agreed') {
				continue;
	   			
	   		}
	   		if (( $field['name'] == 'username' && VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' ) || (( $field['name'] == 'opc_password' && VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' && (!empty($op_usernameisemail)) ))) {
				echo '<div class="formLabel">
						<input type="checkbox" autocomplete="off" id="register_account" name="register_account" value="1" class="inputbox" '; 
						
					if (empty($op_create_account_unchecked)) echo ' checked="checked" '; 
					echo '/>
					
						<label for="register_account">'.OPCLang::_('COM_VIRTUEMART_ORDER_REGISTER').'</label>
					</div>
					';
			} elseif( $field['name'] == 'username' ) {
			    
				echo '<input type="hidden" id="register_account" name="register_account" value="1" />';
			}
	   		// a delimiter marks the beginning of a new fieldset and
	   		// the end of a previous fieldset
			/*
	   		if( $field['type'] == 'delimiter') {
	   			if( $delimiter > 0) {
	   				echo "</fieldset>\n";
	   			}
	   			if( VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' && $field['title'] == OPCLang::_('COM_VIRTUEMART_ORDER_PRINT_CUST_INFO_LBL') && $page == 'checkout.index' ) {
	   				continue;
	   			}
	   			echo '<fieldset>
				     <legend class="sectiontableheader">'.$field['title'].'</legend>
';
	   			$delimiter++;
	   			continue;
	   		}
			*/
	   		
	   		echo '<div id="'.$field['name'].'_div" class="formLabel ';
	   		if (stristr($missing,$field['name'])) {
	   			echo 'missing';
	   		}
	   		echo '"'; 
			if ($op_create_account_unchecked)
			 {
			   if (($field['name'] == 'opc_password') || ($field['name'] == 'password') || ($field['name'] == 'username') || ($field['name'] == 'password2')) echo ' style="display: none;" '; 
			 }
			echo '>';
			
						$arr = array('select', 'dropdown','multicheckbox', 'multiselect', 'radio','checkbox' );

			
						
			if (in_array($field['type'], $arr))
			{
	        echo '<label for="'.$field['name'].'_field">'.$field['title'].'</label>';
			}
	        if( !empty( $field['required'] )) {
	          
	        }
	      	echo ' </div>
	      <div class="formField" id="'.$field['name'].'_input"';
		   if ($op_create_account_unchecked)
			 {
			   if (($field['name'] == 'opc_password') || ($field['name'] == 'password') || ($field['name'] == 'username') || ($field['name'] == 'password2')) echo ' style="display: none;" '; 
			 }
		  echo '>'."\n";
	      	
	      	/**
	      	 * This is the most important part of this file
	      	 * Here we print the field & its contents!
	      	 */
			 
	   		switch( $field['name'] ) {
	   			case 'title':
	   				//$ps_html->list_user_title($db->sf('title', true, false), "id=\"title_field\"");
					echo $field['formcode'];
	   				break;
	   			case 'delimiter': 
					break; 
	   			case 'virtuemart_country_id':
	   				/*
					if( in_array('state', $allfields ) ) {
	   					$onchange = "onchange=\"changeStateList();\"";
	   				}
	   				else {
	   					$onchange = "";
	   				}
					*/
	   				//$ps_html->list_country("country", $db->sf('country', true), "id=\"country_field\" $onchange style=\"width: 215px;\"");
					$field['formcode'] = str_replace('name="', ' alt="'.$field['title'].'" placeholder="'.$field['title'].'" name="', $field['formcode']); 
					if (in_array('virtuemart_country_id', $custom_rendering_fields)) echo '<input type="hidden" id="virtuemart_country_id" name="virtuemart_country_id" value="'.$default_shipping_country.'" />'; 
					else
					echo $field['formcode'];
	   				break;
	   				
	   			
	   			case 'virtuemart_state_id':
	   				$field['formcode'] = str_replace('name="', ' alt="'.$field['title'].'" placeholder="'.$field['title'].'" name="', $field['formcode']); 
	   				//echo $ps_html->dynamic_state_lists( "country", "state", $db->sf('country', true), $db->sf('state', true, false) );
					if (in_array('virtuemart_country_id', $custom_rendering_fields) || (in_array('virtuemart_state_id', $custom_rendering_fields)))
					echo '<input type="hidden" id="virtuemart_state_id" name="virtuemart_state_id" value="0" />';
					else
					echo $field['formcode']; 
				    //echo "<noscript>\n";
				    //$ps_html->list_states("state", $db->sf('state', true, false), "", "id=\"state_field\"");
				    //echo "</noscript>\n";
	   				break;
				case 'agreed':
					//echo '<input type="checkbox" id="agreed_field" name="agreed" value="1" class="inputbox" />';
					echo $field['formcode'];
					break;
				case 'password':
				case 'password2':
					
					echo $field['formcode']; 
		   			break;
					
	   			default:
					echo $field['formcode']; 
					break;
	   				
	   		}
	   		echo '</div>';
	   }
	    /*
		if( $delimiter > 0) {
		
		if( !empty( $required_fields ))  {
			echo '<div style="padding:5px;text-align:center;"><strong>(* = '.OPCLang::_('CMN_REQUIRED').')</strong></div>';
		  	 
		}
			echo "</fieldset>\n";
		}
	   */
	
	   	   echo '</div>';

	}


?>
