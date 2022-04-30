<?php
// Status Of Delimiter
$closeDelimiter = false;
$openTable = true;
$hiddenFields = '';

if (!class_exists('mod_virtuemart_product_komment')) {
	require_once(JPATH_BASE . DS . 'modules' . DS . 'mod_virtuemart_product_komment' . DS . 'mod_virtuemart_product_komment.php');
}
if (class_exists('mod_virtuemart_product_komment')) {
	$komment = mod_virtuemart_product_komment::getProductsComments($this->cart);
} else {
	$komment = '';
}

if (!empty($this->userFieldsCart['fields'])) {
	
	// Output: Userfields
	foreach ($this->userFieldsCart['fields'] as $field) {
		if ($field['name'] == 'customer_note') {
			$field['value'] = 'test';
			//var_dump($field);
		}
		
		if ($komment) {
			echo '<br/><h1>Дополнительные пожелания в заказе</h1>';
			echo $komment;
		}
		?>
			<fieldset class="vm-fieldset-<?php echo str_replace('_', '-', $field['name']) ?>">
				<div class="cart <?php echo str_replace('_', '-', $field['name']) ?>" title="<?php echo strip_tags($field['description']) ?>">
					<span class="cart <?php echo str_replace('_', '-', $field['name']) ?>"><?php echo $field['title'] ?></span>
			
			<?php
			if ($field['hidden'] == true) {
				// We collect all hidden fields
				// and output them at the end
				$hiddenFields .= $field['formcode'] . "\n";
			} else { ?>
			<?php echo $field['formcode']; ?>
					<script id="customer_komment" type="text/template"><?php echo $komment; ?></script>
					<script>
			  jQuery(document).ready(function () {
				  jQuery('textarea[name=customer_note1]').val(jQuery('script#customer_komment').text());
			  });
					</script>
				</div>
		  <?php } ?>
			
			</fieldset>
		
		<?php
	}
	
	// Output: Hidden Fields
	echo $hiddenFields;
}
?>