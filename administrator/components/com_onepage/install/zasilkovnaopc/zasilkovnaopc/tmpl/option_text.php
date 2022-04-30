<?php
defined('_JEXEC') or die('Restricted access');
/*
stano - tato sablona upravuje text zobrazeny vramci textu vnutri <option><?php echo obsah tohto suboru</option>
htmlentities nieje nutny
*/
$branch = $viewData['branch']; 
echo strtoupper($branch->country).', '.$branch->nameStreet; 