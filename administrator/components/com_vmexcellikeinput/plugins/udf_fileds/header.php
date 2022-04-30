<style type="text/css">
 #cf-settings-panel *{
	 color:white;
	 
 }
 
 #cf_list *{
	 text-align:left;
 }
 
 #cf_list TD{
	 vertical-align:top;	
 }
 
 #cf_list tr td{
	 border-bottom:1px solid silver;
 }
 
 .cf_opt label{
	padding-left:2px;	
 }
 
 .cf_opt *{
	 margin:2px!important;
 }
 
 #cf_list textarea{
	 width:90%;
	 min-height:90px;
 }
 
 #cf_list TD.cf_no{
	text-align:center;	
 }
</style>
<script type="text/javascript">
var pelmplugin_udf_version = 1.0;
pelm_plugins.push("pelmplugin_udf:<?php echo $SETTINGS->pelmplugin_custom_fiels_api_key; ?>");
function configureCustomFileds(){
	showCFSettings();
}

jQuery(document).ready(function(){
	jQuery('<li><span><button id="cmdConfigureCustomFileds">Configure user defined fields</button></span></li>').insertBefore(jQuery("#help_item"));
	jQuery("#cmdConfigureCustomFileds").click(function(e){
		e.preventDefault();
		configureCustomFileds();
		return false;
	});
});

<?php 
if(isset($udf_fields)){
	if(!empty($udf_fields)){
		if($udf_fields){
			foreach($udf_fields as $cf){
				if(!$cf->cf_enabled)		
					continue;
				if($cf->cf_editoptions->formater == "filed_update_hook")
					continue;
				
				if($cf->cf_editoptions->formater == "specified_list"){
					?>
					var <?php echo $cf->cf_name;?>_values = <?php echo json_encode($cf->cf_possible_values); ?>; 
					<?php
				}elseif($cf->cf_editoptions->formater == "sql"){
					?>
					var <?php echo $cf->cf_name;?>_values = <?php echo json_encode($cf->cf_possible_values); ?>; 
					<?php
				}
				
			}
		}
	}	
}		
?>



</script>