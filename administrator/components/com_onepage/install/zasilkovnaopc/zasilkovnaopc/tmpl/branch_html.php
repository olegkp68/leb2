<?php
defined('_JEXEC') or die('Restricted access');
//nacitava sa cez ajax po vebere pobocky
//POZOR ! PO ZMENE JE NUJTNE VYGENEROVAT POBOCKY NOZVU: 
//index.php?option=com_virtuemart&view=vmplg&task=ShipmentResponseReceived&cmd=generatezasilkovna&format=raw
$branch = $viewData['branch']; 
?><div class="zasielka_div1" style="padding-top: 8px; clear:both;" id="zas_branch_<?php echo $branch->id; ?>">
 <div class="zas_image" style="float: left; max-width: 50%; margin:0; padding:0;">
  <a class="opcmodal" rel="{handler: \'iframe\', size: {x: 500, y: 400}}" href="<?php echo $branch->photos[0]->normal; ?>">
  <img style="border:1px solid black; margin-right: 8px; float: left; " src="<?php echo str_replace('http:', '', $branch->photos[0]->thumbnail); ?>" width="160" height="120" alt="" />
  </a>
 </div>
<div class="zasielka_div2"  style="float: left; clear:right; max-width: 50%;margin:0; padding:0;">
  <strong><?php echo htmlentities($branch->place, ENT_COMPAT, 'utf-8'); ?></strong><br/>
  <?php echo  htmlentities($branch->street, ENT_COMPAT, 'utf-8'); ?><br/>
  <?php echo htmlentities($branch->zip, ENT_COMPAT, 'utf-8'); ?> 
  <?php echo  htmlentities($branch->city, ENT_COMPAT, 'utf-8'); ?><br />
  <?php 
  if (!empty($branch->openingHours) && (is_string($branch->openingHours->compactLong)))
  {
  ?><div style="margin-top: 8px;">
              <div style="float: left; clear:both;">
			    <em style="clear: both;">Otevírací doba:</em>
			  </div>
			  <br style="clear:both;"/><?php
	echo $branch->openingHours->compactLong; ?></div>
	<?php
  }
  ?></div>
</div><?php


  