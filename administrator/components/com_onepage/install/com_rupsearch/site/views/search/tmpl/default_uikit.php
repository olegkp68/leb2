<?php
/* license: commercial ! */
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

?>
<div class="">
    <ul class="uk-tab <?php echo $this->params->get('defaultclass', ''); ?>" data-uk-tab="<?php echo htmlentities(json_encode(array('connect'=>'#search_results_content'))); ?>">
	<?php 
	$first = true; 
	foreach ($this->tabdata as $k=>$tab) { ?>
	<li <?php 
	if (!empty($tab['active'])) echo ' class="uk-active" '; ?>><a href="#"><?php echo $tab['tabname']; ?></a></li>
    
   
	<?php } ?> 
	</ul>
	
	<ul id="search_results_content" class="uk-switcher uk-margin">
	  	<?php foreach ($this->tabdata as $k=>$tab) { ?>
	<li <?php 
	if (!empty($data['active'])) echo ' class="uk-active" '; ?>>
	<p class="uk-article-meta"><?php echo $tab['tabdesc']; ?></p>
	
	<?php echo $tab['tabcontent']; ?></li>
    
   
	<?php } ?> 
	</ul>
	
	
	
</div>

