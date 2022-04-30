<?php
/**
 * @package		RuposTel OPC 
 * @subpackage	mod_opcard
 * @copyright	Copyright (C) 2005 - 2012 RuposTel.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ($params->get('fontawesome', 0) == 0) {
?>
<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
<?php } 

JHtml::_('script', $root.'/modules/mod_cartsave/assets/js/mod_cartsave.js', array('version' => 'auto', 'relative' => false), array('async' => 'async', 'defer'=>'defer')); 
JHtml::stylesheet($root.'/modules/mod_cartsave/assets/css/mod_cartsave.css'); 



?>
<script type="text/javascript"><!--//--><![CDATA[//><!--
  var MOD_CARTSAVE_QUESTION = <?php echo json_encode(JText::_('MOD_CARTSAVE_QUESTION')); ?>; 
  var MOD_CARTSAVE_WRONGFILEFORMAT = <?php echo json_encode(JText::_('MOD_CARTSAVE_WRONGFILEFORMAT')); ?>; 
  var MOD_CARTSAVE_ERROR_NAME_MISSING_LOAD = <?php echo json_encode(JText::_('MOD_CARTSAVE_ERROR_NAME_MISSING_LOAD')); ?>; 
  var MOD_CARTSAVE_ERROR_NAME_MISSING_SAVE = <?php echo json_encode(JText::_('MOD_CARTSAVE_ERROR_NAME_MISSING_SAVE')); ?>; 
  
//--><!]]>  
</script>
<div class="modulecartsaver <?php echo $params->get('header_class', ''); ?>">
<?php
if ($params->get('showtoolboxlink', 0) != 0) { ?>
	<div class="cartsavertoolbox" id="cartsavertoolbox_<?php echo $id; ?>"><a href="#" onclick="return toolboxopen(<?php echo (int)$id; ?>);"><i class="fas fa-wrench"></i><?php echo JText::_('COM_MODULES_TOOLBOXLINK'); ?></a></div>
<?php 
}
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="POST" id="cartsaverform_<?php echo $id; ?>" class="is_empty" name="cartsaverform_<?php echo $id; ?>" <?php if ($params->get('showtoolboxlink', 0) != 0) { echo ' style="display:none;" '; } ?>>
<?php if ($params->get('displaycartnameinput', 0) == 0) { ?>

  <input class="form-control cart_name_input" id="cart_name_<?php echo $id; ?>" onkeyup="alterDisplayFields(this, <?php echo $id; ?>)" onblur="alterDisplayFields(this, <?php echo $id; ?>)" type="text" value="" placeholder="<?php echo htmlentities(JText::_('MOD_CARTSAVE_YOUR_CART_NAME')); ?>"  form="cartsaverform_<?php echo $id; ?>" name="cart_name" />

<?php } ?>
<?php if (!empty($hasItems)) { 
$mergetype = (int)$params->get('mergetype', 0);
$do_not_show = array(2, 3, 4, 5); 
if (!in_array($mergetype, $do_not_show)) {
?>
<div>
 <div class="mergecheckbox"><input class="mergecheckbox" id="merge_<?php echo $id; ?>" type="checkbox" <?php 
 if ($mergetype === 0) {
	echo ' checked="checked" '; 
 }
 
 ?> value="1" form="cartsaverform_<?php echo $id; ?>" name="merge">
 <label for="merge_<?php echo $id; ?>"><?php echo JText::_('MOD_CARTSAVE_MERGE'); ?></label>
 </div>
</div>
<?php }
else {
  if ($mergetype === 4) {
	  ?><input id="merge_<?php echo $id; ?>" type="hidden" value="0" form="cartsaverform_<?php echo $id; ?>" name="merge" /><?php
  }
  else if ($mergetype === 5) {
	  ?><input id="merge_<?php echo $id; ?>" type="hidden" value="1" form="cartsaverform_<?php echo $id; ?>" name="merge" /><?php
  }
  elseif ($mergetype === 2) {
	  ?><input data-value="1" value="1"  id="merge_<?php echo $id; ?>" data-question="<?php echo htmlentities(json_encode(JText::_('MOD_CARTSAVE_MERGE_DIALOG'))); ?>" data-questionyes="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_YES')))); ?>" data-questioncancel="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_CANCEL')))); ?>" data-questionno="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_NO')))); ?>" type="hidden" form="cartsaverform_<?php echo $id; ?>" name="merge" /><?php
  }
  elseif ($mergetype === 3) {
	  ?><input data-value="0" value="0"  id="merge_<?php echo $id; ?>" data-question="<?php echo htmlentities(json_encode(JText::_('MOD_CARTSAVE_MERGE_DIALOG'))); ?>" data-questionyes="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_YES')))); ?>" data-questioncancel="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_CANCEL')))); ?>" data-questionno="<?php echo htmlentities(json_encode(JText::_(JText::_('MOD_CARTSAVE_MERGE_DIALOG_NO')))); ?>" type="hidden" form="cartsaverform_<?php echo $id; ?>" name="merge" /><?php
  }
}	
}
else {
	?><input id="merge_<?php echo $id; ?>" type="hidden" value="1" form="cartsaverform_<?php echo $id; ?>" name="merge" /><?php
}
?>

<?php if ((!empty($hasItems)) && ($params->get('displaysavebutton', 0) == 0)) {
$onlylogged = $params->get('displaysavebuttonforlogged', 0);
if (($params->get('displaycartnameinput', 0) == 0) || (!empty($user_id)))
if (((!empty($onlylogged)) && (!empty($user_id))) || (empty($onlylogged))) {
	?>
 <button type="button" class="btn btn-primary show_on_input" value="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SAVE'))); ?>" onclick="return actionCart('save', <?php echo (int)$id; ?>)" ><i class="far fa-save"></i> <?php echo JText::_(JText::_('MOD_CARTSAVE_SAVE')); ?></button>
<?php } } ?>
<?php 
if ($params->get('displaycartnameinput', 0) == 0) 
if ($params->get('displayloadbutton', 0) == 0) { ?>
 <button type="submit" class="btn btn-primary show_on_input"  value="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_LOAD'))); ?>" onclick="return actionCart('load', <?php echo (int)$id; ?>)" ><i class="far fa-folder-open"></i> <?php echo JText::_(JText::_('MOD_CARTSAVE_LOAD'))?></button>
<?php } ?> 

<?php if ((!empty($cart_names)) && ($params->get('displaycartlist', 0) == 0)) { ?>
<div class="cartname_list hide_on_input">
<?php if ($params->get('displayfulllist', 0) == 0) { ?>
<a href="#" class="listtoggler listtoggler_<?php echo $id; ?>" onclick="return toggleList(<?php echo $id; ?>)" >(<?php echo count($cart_names); ?>) <?php echo JText::_('MOD_CARTSAVE_LISTCARTS'); ?><span class="list_state"></span></a>
<?php } ?>
<div class="cart_list cart_list_<?php echo $id; ?>" <?php
if ($params->get('displayfulllist', 0) == 0) {
 echo ' style="display:none;" '; 
}
?>>

<?php
foreach ($cart_names as $cart_name_id=>$name) {
	
	
	/* stAn note -> this line is compatible ONLY with latest joomla 3.8+ */
	$cart_link = JRoute::_('index.php?option=com_ajax&module=cartsave&cart_name_id='.(int)$cart_name_id.'&format=raw&module_id='.(int)$module_id.'&myaction=load&cart_name='.urlencode($name), true, true, true);
	$xls_link = JRoute::_('index.php?option=com_ajax&module=cartsave&cart_name_id='.(int)$cart_name_id.'&format=raw&module_id='.(int)$module_id.'&myaction=download&cart_name='.urlencode($name), true, true, true); 
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace('{cartname}', $name, $MOD_CARTSAVE_SHARE_EMAIL_BODY); 
	$MOD_CARTSAVE_SHARE_EMAIL_SUBJECT_NOW = str_replace('{cartname}', $name, $MOD_CARTSAVE_SHARE_EMAIL_SUBJECT); 
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace('{cartlink}', $cart_link, $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace('{xlslink}', $xls_link, $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = htmlentities($MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW, ENT_COMPAT | ENT_HTML401); 
	
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace(htmlentities('&amp;'), '%26', $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	
	
	//$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace(array("\r\r\n", "\r\n", "\n"), array('&#13;&#10;', '&#13;&#10;', '&#13;&#10;'), $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace(array("\r\r\n", "\r\n", "\n"), array('%0D%0A', '%0D%0A', '%0D%0A'), $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	$MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW = str_replace(' ', '%20', $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW); 
	//%0A
	
	?><div class="namedcart_row">
	<a class="load_named_cart hasTooltip" href="<?php echo $cart_link; ?>" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_LOAD')).': '.$name); ?>" onclick="return loadCart(<?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>)">
		<?php if ($params->get('displayloadicon', 0) == 0) { ?>
		<i class="fas fa-arrow-alt-circle-up"></i>
		<?php } ?>
		<?php if ($params->get('displaycartname', 0) == 0) { ?>
		<span class="cart_name" ><?php echo $name; ?></span>
		<?php } ?>
	</a>
	<?php if ($params->get('displayremoveicon', 0) == 0) { ?>
	<a class="drop_named_cart hasTooltip menuicon" href="#" onclick="return dropCart(<?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_DROP')).': '.$name); ?>" ><i class="fas fa-trash-alt"></i></a>
	<?php } ?>
	<?php if ($params->get('displaydowloadicon', 0) == 0) { ?>
	<a class="download_named_cart hasTooltip menuicon" href="<?php echo $xls_link; ?>" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_DOWNLOAD')).': '.$name); ?>"><i class="fas fa-file-download"></i></a>
	<?php } ?>
	<?php if ($params->get('displayuploadicon', 0) == 0) { ?>
	<a class="upload_named_cart hasTooltip menuicon" href="#" onclick="return uploadFile(<?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_LOAD_XLS')).': '.$name); ?>" tooltip="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_LOAD_XLS')).': '.$name); ?>"><i class="fas fa-file-upload"></i></a>
	<?php } 
	$displaysharename = (int)$params->get('displayshareicon', 0);
	$displaysharelink = (int)$params->get('displaysharelink', 0);
	$displayshareemail = (int)$params->get('displayshareemail', 0);
	$sh = $displayshareemail + $displaysharelink + $displaysharename;
	?>
	<?php if (($params->get('displayshareicon', 0) == 0) && ($sh !== 3)){ ?>
	<a class="share_named hasTooltip menuicon" href="#" onclick="return shareIcon(<?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE')).': '.$name); ?>" tooltip="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE')).': '.$name); ?>"><i class="far fa-copy"></i></a>


	<div class="share_menu" id="share_menu_<?php echo $cart_name_id.'_'.$id; ?>">
	  <?php if ($params->get('displaysharename', 0) == 0) { ?>
	  <div><a class="share_named hasTooltip" href="#" data-name="<?php echo htmlentities($name); ?>" onclick="return shareIconName(this, <?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_NAME')).': '.$name); ?>" tooltip="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_NAME')).': '.$name); ?>"><i class="far fa-copy"></i><?php echo JText::_(JText::_('MOD_CARTSAVE_SHARE_NAME')); ?></a></div>
	  <?php } ?>
	  <?php if ($params->get('displaysharelink', 0) == 0) { ?>
	  <div><a class="share_named hasTooltip" href="<?php echo $cart_link;  ?>" onclick="return shareIconLink(this, <?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_LINK')).': '.$name); ?>" tooltip="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_LINK')).': '.$name); ?>"><i class="fas fa-link"></i><?php echo JText::_(JText::_('MOD_CARTSAVE_SHARE_LINK')); ?></a></div>
	  <?php } ?>
	  <?php if ($params->get('displayshareemail', 0) == 0) { ?>
	  <div><a class="share_named hasTooltip" href="mailto:?subject=<?php echo htmlentities($MOD_CARTSAVE_SHARE_EMAIL_SUBJECT_NOW); ?>&body=<?php echo $MOD_CARTSAVE_SHARE_EMAIL_BODY_NOW; ?>" onclick="return shareIconEmail(this, <?php echo (int)$cart_name_id; ?>, <?php echo (int)$id; ?>, '<?php echo htmlentities($name); ?>');" title="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_EMAIL')).': '.$name); ?>" tooltip="<?php echo htmlentities(JText::_(JText::_('MOD_CARTSAVE_SHARE_EMAIL')).': '.$name); ?>"><i class="far fa-envelope"></i><?php echo JText::_(JText::_('MOD_CARTSAVE_SHARE_EMAIL')); ?></a></div>
	  <?php } ?>
	</div>
	
	<?php } ?>
	

	</div>
<?php
}
?>





</div>
</div>
<?php } ?>

 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="option" value="com_ajax" />
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="module" value="cartsave" />
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="cart_name_id" value="" id="cart_name_id_<?php echo $id; ?>" />
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="format" value="raw" />
 <span style="display: none;"><input type="file" onchange="return validateUploadFile(this, <?php echo (int)$id; ?>)" form="cartsaverform_<?php echo $id; ?>" name="cart_upload_file" class="cart_upload_file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"  /></span>
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="myaction" value="" id="myaction_<?php echo $id; ?>" />
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="return" value="<?php echo $return; ?>" />
 
 <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="module_id" value="<?php echo (int)$module_id; ?>" />
 <?php 
 
 $Itemid = JRequest::getInt('Itemid', 0); 
 $lang = JRequest::getVar('lang', ''); 
 if (!empty($lang)) {
	 ?><input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="lang" value="<?php echo htmlentities($lang); ?>" /><?php
 }
 
 if (!empty($Itemid)) { ?>
  <input type="hidden" form="cartsaverform_<?php echo $id; ?>" name="Itemid" value="<?php echo (int)$Itemid; ?>" />
 <?php }
 
 
 if ($params->get('clearcart', 0)) {
	 $cart = VirtuemartCart::getCart(); 
	 if (!empty($cart->cartProductsData)) {
	 JFactory::getLanguage()->load('com_onepage'); 
	 ?> 
	 <a class="btn btn-danger hide_on_input" href="<?php echo JRoute::_('index.php?option=com_onepage&view=opc&task=clearcart'); ?>"><i class="fas fa-trash-alt"></i> <?php echo JText::_('COM_ONEPAGE_CLEAR_CART'); ?></a>
	 <?php
	 }
 }
 
 if ($params->get('displayuploadbutton', 0) == 0) {
	  ?> 
	 
	 <a class="upload_cart_cart btn btn-danger hide_on_input" href="#" onclick="return uploadFile(0, <?php echo (int)$id; ?>, '');"><i class="fas fa-file-upload"></i><?php echo JText::_('MOD_CARTSAVE_LOAD_XLS'); ?></a>
	 <?php
	 
 }
 
 if (!empty($hasItems)) {
 	
 if ($params->get('displaystorebutton', 0) == 0) {
	  ?> 
	 
	 <a class="download_cart_cart btn btn-danger hide_on_input" href="<?php echo JRoute::_('index.php?option=com_ajax&module=cartsave&cart_name_id=0&format=raw&module_id='.(int)$module_id.'&myaction=download&cart_name='); ?>" ><i class="fas fa-file-download"></i><?php echo JText::_('MOD_CARTSAVE_STORE_XLS'); ?></a>
	 <?php
	 
 }
 }
 
 ?>
 

</form>
</div> 
	
<?php 
