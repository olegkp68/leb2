<?php
/**
 * @version		$Id: cache.php 21518 2011-06-10 21:38:12Z chdemko $
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
jimport( 'joomla.registry.registry' );
class OPCparametersJForm
{
   public static function render($form, $file='', $tid=0, $filter_type='', &$allrenderedfields=array())
   {
	   
	 
	  
      $fieldSets = $form->getFieldsets();
	  $fieldsByFieldset = array(); 
	  
	  
	  
	  foreach ($fieldSets as $name => $fieldSet) {
		  
		  $fields = $form->getFieldset($name);
		  $fieldsByFieldset[$name] = $fields; 
		  if (!empty($filter_type)) {
		  foreach ($fields as $ind => $field) { 
									
										$field->skip = true; 
									
									$class = new ReflectionClass($field);
									$property = $class->getProperty("element");
									$property->setAccessible(true);
									$tes = $property->getValue($field);
									if (isset($tes['filter-tab-type'])) {
										$filter = (string)$tes['filter-tab-type']; 
										if ($filter !== $filter_type) {
										    	
										}
										else {
											$field->skip = false; 
											$field->filter_type = $filter_type; 
										}
									}
									if (!empty($field->skip)) {
										unset($fieldsByFieldset[$name][$ind]); 
										if (empty($fieldsByFieldset[$name])) unset($fieldsByFieldset[$name]); 
										
									}
							}
									
									
					}
			}
		  
	  
	
	 $control_label_class = 'control'; 
	 $control_field_class = 'class'; 
	 $control_input_class = 'input'; 
	 $control_group_class = 'group'; 
	 ob_start(); 
		if (!empty($fieldSets)) {
			?>

				<?php
				foreach ($fieldsByFieldset as $name => $X) {
					
					$fieldSet = $fieldSets[$name]; 
					
					?>
					<div class="opc_renderer_fields <?php echo $control_field_class ?> ">
						<?php
					$label = !empty($fieldSet->label) ? $fieldSet->label : strtoupper('VMPSPLUGIN_FIELDSET_' . $name);

						if (!empty($label)) {
							$class = isset($fieldSet->class) && !empty($fieldSet->class) ? "class=\"".$fieldSet->class."\"" : '';
							?>
							<h3> <span<?php echo $class  ?>><?php echo JText::_($label) ?></span></h3>
							<?php
							if (isset($fieldSet->description) && trim($fieldSet->description)) {
								echo '<p class="tip">' . JText::_($fieldSet->description) . '</p>';
							}
						}
					?>

					<?php $i=0; 
					
					
					  
					?>
					<?php foreach ($fieldsByFieldset[$name] as $field) { 
					
					
					
					
					?>
						<?php if (!$field->hidden) {
							?>
						<div class="<?php echo $control_group_class ?> control-group form-group">
							<div class="<?php echo $control_label_class ?> col-lg-9"><label class="control-label">
									<?php echo $field->label; ?>
							</label>
							</div>
							<div class="<?php echo $control_input_class ?> controls col-lg-3">
									<?php 
									//var_dump($field); 
									
									$default = new stdClass(); 
									$values = OPCconfig::get('order_export_config', $file, $tid, $default);
									
									$field->values = $values; 
									
									$html = (string)$field->input; 
									if (!empty($html)) {
										$allrenderedfields[] = $field; 
									}
									echo $html; 
									
									
									?>
							</div>
						</div>
					<?php } ?>
					<?php } 
					
					
					?>

				</div>
				<?php

				}
				?>

		<?php


		}
		
		
		return ob_get_clean(); 
   }
}