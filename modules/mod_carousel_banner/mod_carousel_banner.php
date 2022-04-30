<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_carousel_banner
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * @subpackage	mod_carousel_banner
 * @author     	TemplatePlazza
 * @link 		http://www.templateplazza.com
 */

defined('_JEXEC') or die;

// Include the banners functions only once
JLoader::register('ModCarouselBannerHelper', __DIR__ . '/helper.php');

JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');
BannersHelper::updateReset();
$list = &ModCarouselBannerHelper::getList($params);

$doc            = JFactory::getDocument();
$baseurl        = JURI::base(true).'/';
$modulebase     = JURI::base(true).'/modules/mod_carousel_banner/';

$static_files_version = "3.0.0";
$autoplay 					= $params->get('autoplay', true);
$autoplay_timeout 			= $params->get('autoplay_timeout', 3000);
$anim_in					= $params->get('anim_in', 'tns-fadeIn');
if($anim_in !== "tns-fadeIn") {
    $doc->addStylesheet($modulebase.'assets/css/animate.min.css?v='.$static_files_version);
}
$doc->addStylesheet($modulebase.'assets/css/style.min.css?v='.$static_files_version);
$doc->addScript($modulebase.'assets/js/tiny-slider.min.js?v='.$static_files_version);

$css_adjustment = $params->get('css_adjustment');
if($css_adjustment){
    $doc->addStyleDeclaration($css_adjustment);
}

/* Carousel Settings */

$num_column				= $params->get('num_column', 1);
$column_settings		= $params->get('column_settings', 0);
$border_radius			= $params->get('border_radius', 0);
$edge_padding			= $params->get('edge_padding', 0);

if($num_column == 1) {
    $gutter_size        = 0;
}else{
    $gutter_size 			= $params->get('gutter_size', '0');
}
if($anim_in == "tns-fadeIn") {
    $anim_out          		= "tns-fadeOut";
	$anim_speed        		= "1000";
	$mode					="carousel";
} else {
    $anim_out          		= $params->get('anim_out');
	$anim_speed        		= $params->get('anim_speed');
	$mode					="gallery";
}

if($mode == "gallery"){
	$doc->addStyleDeclaration("#carouselbanner-wrp-".$module->id." .tns-inner {margin: 0 -".$gutter_size."px 0 0!important;}#carouselbanner-wrp-".$module->id." .tns-outer {overflow:hidden;}");
}
$doc->addStyleDeclaration("#carouselbanner-wrp-".$module->id." .banneritem .banneritem-inner {border-radius:".$border_radius."px!important;overflow: hidden!important;}");

if($column_settings == 0){
	if($num_column > 2){
		$responsive = "responsive: {
			320: {gutter: ".($gutter_size*0.3).", items: ".($num_column - 2).", edgePadding:0},
			567: {gutter: ".($gutter_size*0.6).",	items: ".($num_column - 1).", edgePadding:".floor($edge_padding*0.5)."},
			768: {gutter: ".($gutter_size*0.9).",	items: ".($num_column - 1).", edgePadding:".floor($edge_padding*0.75)."},
			992: {gutter: ".$gutter_size.", items: ".$num_column.", edgePadding:".$edge_padding."}
		}";
	}elseif($num_column == 2){
		$responsive = "responsive: {
			320: {gutter: 0, items: 1, edgePadding:0},
			567: {gutter: ".($gutter_size*0.4).", items: 2, edgePadding:".floor($edge_padding*0.5)."},
			768: {gutter: ".($gutter_size*0.8).",items: 2, edgePadding:".floor($edge_padding*0.75)."},
			992: {gutter: ".$gutter_size.", items: 2, edgePadding:".$edge_padding."}
		}";
	}else{
		$responsive = "";
	}
}else {
	$custom_num_column		= $params->get('custom_num_column');
	$responsive = "responsive: {";
	foreach ((array)$custom_num_column as $item) : 
		$responsive .= $item->breakpoint .":{gutter:".$item->gutter.", items:".$item->col.", edgePadding:".$item->edgepadding."},";
	endforeach;
	$responsive .= "}";
}

$scriptcr ="var slider".$module->id." = tns({container: '.carouselbanner-".$module->id."',
	items: ".$num_column.",
	autoplay: ".$autoplay.",
	autoplayTimeout: ".$autoplay_timeout.",
	autoplayHoverPause:true,
	nav: false,
	autoplayButtonOutput: false,
	mode : '".$mode."',
	animateIn: '".$anim_in."',
	animateOut: '".$anim_out."',
	speed: ".$anim_speed.",
	swipeAngle: false,
	edgePadding : ".$edge_padding.",
	controlsContainer: '.controls-container-".$module->id."',
	".$responsive."
});";
$scriptcr_preload =	"document.addEventListener('DOMContentLoaded', function(event) { 
	document.getElementById('carouselbanner-wrp-".$module->id."').style.display = 'block'; // show
});";
// $doc->addScriptDeclaration($scriptcr, 'module');
$doc->addScriptDeclaration($scriptcr_preload);

require JModuleHelper::getLayoutPath('mod_carousel_banner', $params->get('layout', 'default') );

