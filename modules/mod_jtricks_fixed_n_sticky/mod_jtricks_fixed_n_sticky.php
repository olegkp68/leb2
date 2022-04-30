<!-- BEGIN: Fixed'n'sticky (www.pluginaria.com) --><?php
/**
 * @copyright   Copyright (C) 2013 Pluginaria.com.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$document->addScript(JURI::root() . 'modules/mod_jtricks_fixed_n_sticky/fixed_n_sticky-1.5.js'); 

// Module inclusion style(s) (chrome(s)) we're in

if (array_key_exists('style', $attribs))
    $inheritChromes = $attribs['style'];
else
    $inheritChromes = '';

$moduleStyle                   = $params->get('moduleStyle');
$cssOverride                   = $params->get('cssOverride');
$containerClass                = $params->get('containerClass');
$position                      = $params->get('position');
$targetLeft                    = $params->get('targetLeft');
$targetTop                     = $params->get('targetTop');
$moduleDebug                   = $params->get('moduleDebug');
$responsiveSmallScreenAction   = $params->get('responsiveSmallScreenAction');
$responsiveSmallScreenMaxWidth = $params->get('responsiveSmallScreenMaxWidth');
$zIndex                        = $params->get('zIndex');

if (strlen($moduleStyle) == 0)
    $moduleStyle = $inheritChromes;
// We want container to have no chrome itself, all children will be chromed
$attribs['style'] = 'none';

if (strlen($cssOverride) > 0)
    $document->addStyleDeclaration($cssOverride);

if (strlen($containerClass) > 0)
    $containerClass .= ' class="' . $containerClass . '" ';

if (strlen($targetLeft) != 0)
    $options = 'targetLeft: ' . $targetLeft;
else
    $options = 'targetLeft: 0';

if (strlen($targetTop) != 0)
    $options .= ', targetTop: ' . $targetTop;
else
    $options .= ', targetTop: 0';

//---- responsive design small screens/browser windows
if ($responsiveSmallScreenAction == 'stop')
{
    $cssMediaCheck =
        '@media only screen and (max-width: ' . strval(intval($responsiveSmallScreenMaxWidth)) . 'px) {' .
        '    #mediastopcheck_' . $position . ' { visibility:hidden }' .
        '}';

    $document->addStyleDeclaration($cssMediaCheck);

    echo '<div id="mediastopcheck_' . $position . '" style="display:none"></div>';
    $options .= ", mediaStopCheckId: 'mediastopcheck_" . $position . "'";
}

//---- parent height & confinement parent
$updateParentHeight = $params->get('updateParentHeight');

if ($updateParentHeight == "1")
    $options .= ', updateParentHeight: true';

$confinementAreaClass = $params->get('confinementAreaClass');
$confinementAreaId = $params->get('confinementAreaId');

if (strlen($confinementAreaId) > 0)
    $options .= ", confinementArea: '#" . $confinementAreaId . "'";
else if (strlen($confinementAreaClass) > 0)
    $options .= ", confinementArea: '." . $confinementAreaClass . "'";
//---- parent height & confinement parent

require_once dirname(__FILE__) . "/module_renderer.php";
extract(Fns_renderModulesWithChrome($module, $position, $moduleStyle, $attribs, isset($wrapper) ? $wrapper : NULL));

?>
<div id="fixedcontainer_<?php echo $position; ?>" style="position:relative">
<div id="fixeddiv_<?php echo $position; ?>" <?php echo $containerClass; ?> style="position:static;z-index:<?php echo $zIndex; ?>">
<?php
    if ($moduleDebug == '1')
    {
?>

<!-- begin: Fixed'n'sticky debug -->
<div style="border:1px solid black;background:lightgrey;color:black;font-size:12px;margin-bottom:4px;clear:both;padding:2px;width:100%;font-family:Arial,Helvetica,Sans-serif;text-transform:none">
    Fixed'n'sticky debug<br/>
    <b>Inner modules:</b> <?php echo $childCount; ?><br/>
    <b>Output buffer level:</b> <?php echo ob_get_level(); ?><br/>
    <b>Inherit chrome(s):</b> <?php echo $inheritChromes; ?><br/>
    <?php
        if ($childCount == 0)
        {
            echo 'No modules are within floating container. Put some into module position <b>' . $position . '</b><br/>';
        }
    ?>
</div>
<br/>
<!-- end: Fixed'n'sticky debug -->

<?php
    }
?>
<?php 

    if (strlen($params->get('prelude')) > 0)
        echo $params->get('prelude');

    echo $innerContents;

    if (strlen($params->get('finale')) > 0)
        echo $params->get('finale');

?>
</div></div>
<script type="text/javascript">
    FixedMenu.add('fixeddiv_<?php echo $position; ?>', { <?php echo $options; ?>  });
</script>
<!-- END: Fixed'n'sticky (www.pluginaria.com) -->