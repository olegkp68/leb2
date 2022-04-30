<?php
defined ('_JEXEC') or die('Restricted access');
?><script>
 var quiz_data = <?php echo json_encode($currentdata); ?>; 
 var first_option_txt = "<?php echo '-- Choose Crank Model --'; ?>"; 
</script>
<?php

$root = Juri::root(); 
		if (substr($root, -1) !== '/') $root .= '/'; 
				
//		$sor = $root.'modules/mod_quiz/assets/mod_quiz.css'; 
		//JHtml::stylesheet($sor); 
		
		$sor = $root.'modules/mod_quiz/assets/mod_quiz.js'; 
		JHtml::script($sor); 