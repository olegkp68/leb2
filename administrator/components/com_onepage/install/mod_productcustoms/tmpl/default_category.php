<?php
//load this template via: 
//
//$path = JModuleHelper::getLayoutPath('mod_productcustoms', 'default_category'); 
//make sure you create: 
//$params = new JRegistry(''); 
//$params->set('key', 'val'); 
//require($path); 
	$category = JRequest::getVar('virtuemart_category_id', JRequest::getVar('primary_virtuemart_category_id', $params->get('default_category', 0))); 
	
	if (empty($category)) {
		$first = false; 
	}
	else {
	$first = true; 
	}
	foreach ($datas as $title=>$v) {
		
		?><fieldset><legend><?php echo $title; ?></legend>
		<?php foreach ($v as $id => $val) { ?>
		   <label for="id_<?php echo $id; ?>" id="label_id_<?php echo $id; ?>">
		   <?php if ($first) {
			$first = false; 			   
			?>
		    <input data-label="<?php echo htmlentities($val); ?>" type="hidden" value="<?php echo $id; ?>" name="virtuemart_custom_id" data-name="virtuemart_custom_id" data-value="<?php echo $id; ?>" /><?php 
		   }
		   else {
			   ?><input data-label="<?php echo htmlentities($val); ?>" type="checkbox" value="<?php echo $id; ?>" name="virtuemart_custom_id" data-name="virtuemart_custom_id" data-value="<?php echo $id; ?>" /><?php 
		   }
		   ?><?php echo $val; ?></label>
			<?php
		}
		?>
		</fieldset>
		<?php
		
		
	}
	
