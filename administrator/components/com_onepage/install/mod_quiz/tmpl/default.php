<?php
defined ('_JEXEC') or die('Restricted access');


?>
<div class="allwrap <?php 

if (!empty($q_model) && (!empty($q_brand))) { echo ' quiz_selected '; }
?>" id="quiz">
<div class="center" id="or_quiz">OR</div>
<div class=" center selector  tabselector "><a href="#" data-show="#quiz_wrap" data-hide="#cf_form_312,#or_quiz,form.cf_form" class="tabselector" onclick="return mod_quiz.toggle(this)"><?php echo JText::_('MOD_QUIZ_TITLE'); ?> <i class="fa fa-chevron-down"></i></a>

</div>
 
<form action="<?php echo $myconfig['home_url']; ?>" method="GET" id="quiz_form" name="quiz_form" >
<div style="display: none;" id="quiz_wrap">
 
<div>
 <div class="q_row center">
  <span class="q_question"><?php echo JText::_('MOD_QUIZ_QUESTION1'); ?></span>
  <span class="q_answer">
   <select name="q_brand" class="q_select" data-type="brand" data-nexttype="model">
   <option value=""><?php echo JText::_('MOD_QUIZ_CHOOSE1'); ?></option>
   <?php 
    $brand_found = false; 
    foreach ($currentdata as $brand => $data) {
		
		?><option value="<?php echo htmlentities($brand); ?>" <?php 
		  if ($q_brand === $brand) {
			  $brand_found = true; 
			  echo ' selected="selected" '; 
		  }
		?>><?php echo $brand; ?></option><?php
	}
  ?></select></span>
 </div>
 
 <div class="q_row center">
  <span class="q_question"><?php echo JText::_('MOD_QUIZ_QUESTION2'); ?></span>
  <span class="q_answer">
	<select name="q_model" class="q_select" data-type="model">
      
	  <?php 
	    if (!empty($brand_found)) {
			?><option value=""><?php echo JText::_('MOD_QUIZ_CHOOSE2'); ?></option><?php
			foreach ($currentdata[$q_brand] as $model => $data) {
				
				
				
		?><option value="<?php echo htmlentities($model); ?>" <?php 
		  if ($q_model === $model) {
			 
			  $model_found = true; 
			  echo ' selected="selected" '; 
		  }
		?>><?php echo $model; ?></option><?php
			}
		}
		else {
			?><option value=""><?php echo JText::_('MOD_QUIZ_CHOOSE2_EMPTY1'); ?></option><?php
		}
	  ?>
    </select>
  </span>
 </div>
 <input type="hidden" name="q_module" value="mod_quiz" />
 
</div>
<center class="q_reset"><div class="center"> <span class="q_reset "><a href="<?php echo $myconfig['home_url']; ?>" class="q_reset"><span><?php echo JText::_('MOD_QUIZ_RESETLNK'); ?> </span><i class="fa fa-times"></i></a></span></div></center>
</div>
<?php 
 //if (!empty($q_model)) 
 { 
?>
  
 <?php } ?>
</form>



</div>

<?php 

if (!empty($q_model) && (!empty($q_brand))) {
	$css = ' #cf_form_312, form.cf_form { display: none; } #or_quiz { display: none; } #quiz_wrap { display: block !important; }'; 
	JFactory::getDocument()->addStyleDeclaration($css); 
}


if ((empty(ModQuizHelper::$products)) && (is_array(ModQuizHelper::$products))) {
?>
<div class="center quiz noproducts"><?php echo JText::_('MOD_QUIZ_NOPRODUCTS'); ?></div>
<form action="<?php echo $myconfig['home_url']; ?>" method="POST" id="quiz_form2" name="quiz_form2" >
 <input type="hidden" name="q_module" value="mod_quiz" />
 <input type="hidden" name="q_model" value="<?php echo htmlentities($q_model); ?>" />
 <input type="hidden" name="q_brand" value="<?php echo htmlentities($q_brand); ?>" />
 <input type="email" required="required" name="ce" value="" class="px42" placeholder="<?php echo JText::_('JGLOBAL_EMAIL'); ?> *"/>
 <input type="submit" class="myOrangeButton" value="<?php echo htmlentities(JText::_('MOD_QUIZ_SEND')); ?>" />
 <input type="hidden" name="redirectto" value="<?php echo base64_encode($myconfig['home_url']); ?>" />
</form>
<?php
}