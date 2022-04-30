<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$user = JFactory::getUser();
$this->language = $doc->language;
$this->direction = $doc->direction;

// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option = $app->input->getCmd('option', '');
$view = $app->input->getCmd('view', '');
$layout = $app->input->getCmd('layout', '');
$task = $app->input->getCmd('task', '');
$itemid = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

// Output as HTML5
$doc->setHtml5(true);

if ($task == "edit" || $layout == "form") {
	$fullWidth = 1;
} else {
	$fullWidth = 0;
}

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

if ($_REQUEST['option'] == 'com_virtuemart') {
	//$doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/jquery.fancybox.pack.js');
	$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/jquery.fancybox.css');
}

$doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/template.js');

// Check for a custom CSS file
$userCss = JPATH_SITE . '/templates/' . $this->template . '/css/user.css';

if (file_exists($userCss) && filesize($userCss) > 0) {
	$doc->addStyleSheetVersion('templates/' . $this->template . '/css/user.css');
}

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Adjusting content width
if ($this->countModules('position-7') && $this->countModules('position-8')) {
	$span = "span6";
} elseif ($this->countModules('position-7') && !$this->countModules('position-8')) {
	$span = "span9";
} elseif (!$this->countModules('position-7') && $this->countModules('position-8')) {
	$span = "span9";
} else {
	$span = "span12";
}

// Logo file or site title param
if ($this->params->get('logoFile')) {
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
} elseif ($this->params->get('sitetitle')) {
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('sitetitle')) . '</span>';
} else {
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}

// Add Stylesheets
//$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/font-awesome-4.6.3/css/font-awesome.min.css');
//$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css');
$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template2.css');

?>

<?php $logged = '';
if ($user->guest) {
	$logged = 'not-logged ';
} else {
	$logged = 'logged-in ';
} ?>

<!DOCTYPE html>
<html xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<jdoc:include type="head"/>
	
	<!--[if lt IE 9]>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
	<![endif]-->


</head>

<body class="site <?php echo $logged;
echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($params->get('fluidContainer') ? ' fluid' : '');
echo($this->direction == 'rtl' ? ' rtl' : '');
?>">

<!-- Body -->
<div class="body">
	<div id="site" class="container<?php echo($params->get('fluidContainer') ? '-fluid' : ''); ?>">
		<!-- Header -->
		<table id="top" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td id="top-left">
					<a href="http://<?php echo($sitename)?>" ><div id="logo"></div></a>
				</td>
				<td style="width:1px;"></td>
				<td id="top-mitte">
					<div id="tel-fax">Tel.: (+49)541 -
						911576-0<br/>E-Mail:<a href="mailto:shop@leb-design.de">shop@leb-design.de</a></div>
				</td>
				<td>
					<div id="banner">
						<jdoc:include type="modules" name="banner-top"/>
					</div>
				</td>
			</tr>
		</table>
		<div id="tags"><br/><br/><br/><br/>
			<h1>
				<a href="/index.php" title="европа, Германия, иконы, книги, хозтовары, матрешки, журналы, русские товары, товары оптом, текстиль, сувениры, детские игры, игры, символика">европа,
					Германия, иконы, книги, хозтовары, матрешки, журналы, русские товары, товары оптом, текстиль, сувениры,
					детские игры, игры, символика, оптовая продажа товаров</a></h1>
			<h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;европа, Германия, иконы, книги, хозтовары, матрешки, журналы, русские товары,
				товары оптом, текстиль, сувениры, детские игры, игры, символика, оптовая продажа товаров</h3>
		</div>
		
		<!-- Leiste mit Menu (Mitte) -->
		<table id="menumitte" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="width: 33%;">
					<div id="topmenu">
						<jdoc:include type="modules" name="searchTop"/>
					</div>
				</td>
				<td style="width: 54%;">
					<div id="user-menu">
								<jdoc:include type="modules" name="logged-user"/>
								<jdoc:include type="modules" name="user-login-register"/>
					</div>
				</td>
				<td class="area" style="width: 13%;">
					<div class="langs">
						<jdoc:include type="modules" name="langSw"/>
					</div>
				</td>
			</tr>
		</table>
		
		<!-- Inhalt -->
		<table id="mainbody" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td id="leftbody" class="leftbody">
			<?php if ($this->countModules('left')) : ?>
							<div id="leftmenu">
								<jdoc:include type="modules" name="left"/>
							</div>
			<?php endif; ?>
			<?php if ($this->countModules('user3')) : ?>
							<div id="corb">
								<jdoc:include type="modules" name="user3"/>
							</div>
			<?php endif; ?>
				</td>
				<td id="leerbody"></td>
				<td id="maincontent">
			<?php if ($this->getBuffer('message')) : ?>
							<div class="error">
								<!--                            <h2>--><?php //echo JText::_('Message'); ?><!--</h2>-->
								<jdoc:include type="message"/>
							</div>
			<?php endif; ?>
					<!--                    --><?php //if ($this->countModules('advert1')) : ?>
					<!--                        <div class="advert1">-->
					<!--                            <jdoc:include type="modules" name="advert1"/>-->
					<!--                        </div>-->
					<!--                    --><?php //endif; ?>
					<jdoc:include type="component"/>
					
					<!--                    --><?php //if ($this->countModules('advert3')) : ?>
					<!--                        <div id="advert2">-->
					<!--                            <jdoc:include type="modules" name="advert3"/>-->
					<!--                        </div>-->
					<!--                    --><?php //endif; ?>
				</td>
				<td id="leerbody"></td>
				<td id="rightbody">
			<?php if ($this->countModules('specialRight')) : ?>
							<table style="width:100%; background:#c4013d; padding: 1px;margin-top:-0.01em;">
								<tr>
									<td class="spez-predl">
										<a href="<?php echo JURI::base() . 'skidki-optom'; ?>"><span class="spez-predl">Спец. предложения!</span></a>
									</td>
								</tr>
							</table>
							<div id="spez-predl">
								<jdoc:include type="modules" name="specialRight"/>
							</div>
			<?php endif; ?>
				</td>
			</tr>
		</table>
		<table id="menuBottom" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="">
					<div id="topmenu">
						<jdoc:include type="modules" name="footer-menu"/>
					</div>
				</td>
				<td style=""></td>
				<td style=""></td>
			</tr>
		</table>
		<div id="bottom">
			<div id="hr_bottom"></div>
			<div class="footer_left">
		  <?php
		  echo "<p>
    " . JText::_('All Rights Reserved') . ' ' . JHTML::Date('now', 'Y') . '  ' . '<a href="' . JURI::base() . '" title="' . JURI::base() . '">' . JURI::base() . '</a>' . "</p>";
		  ?>
			</div>
			<div class="footer_right">
				<jdoc:include type="modules" name="footer"/>
			</div>
		</div>
	</div>
</div>

<jdoc:include type="modules" name="debug" style="none"/>
</body>

</html>
