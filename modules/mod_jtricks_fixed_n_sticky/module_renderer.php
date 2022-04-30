<?php
/**
 * @copyright	Copyright (C) 2010-2012 JTricks.com, 2013-2015 Pluginaria.com
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @version 20160323
 */

// no direct access
defined('_JEXEC') or defined('_MODULE_RENDERER_STYLESHEET') or die('Restricted access');

function Fns_responsiveHideBothText($id, $responsiveHideWidthAbove, $responsiveHideWidthBelow)
{
    if ($responsiveHideWidthAbove != '')
    {
        return
            '@media only screen and (min-width: ' . strval(intval($responsiveHideWidthAbove)) . 'px) {' .
            '    #' . $id . ' {display:none}' .
            '}';
    }

    if ($responsiveHideWidthBelow != '')
    {
        return
            '@media only screen and (max-width: ' . strval(intval($responsiveHideWidthBelow)) . 'px) {' .
            '    #' . $id . ' {display:none}' .
            '}';
    }
    
}

function Fns_responsiveHideBoth($id, $responsiveHideWidthAbove, $responsiveHideWidthBelow, $document)
{
    $document->addStyleDeclaration(
        Rerouter_responsiveHideBothText($id, $responsiveHideWidthAbove, $responsiveHideWidthBelow));
}

function Fns_responsiveHide($id, $responsiveSmallScreenMaxWidth, $document)
{
    $document->addStyleDeclaration(
        '@media only screen and (max-width: ' . strval(intval($responsiveSmallScreenMaxWidth)) . 'px) {' .
        '    #' . $id . ' {display:none}' .
        '}'
    );
}

// Render module functionality for standard Joomla and specific template rendering
function Fns_renderModulesWithChrome($module, $position, $moduleStyle, $attribs, $wrapper)
{
    $templateName = JFactory::getApplication()->getTemplate();
    $warpFileName = JRoute::_('templates/'.$templateName)."/warp/warp.php";
    $warpFileNameSeven = JRoute::_('templates/'.$templateName)."/warp.php";
        
    $rendererComments = '';

    if (file_exists($warpFileNameSeven))
    {
		$warp = require($warpFileNameSeven);

        // reset panel config for current entry
        $warp['config']->set('widgets.'.$module->id.'.panel', 'uk-panel');

        // set default for floating_absolute inheriting from the existing module position
        $panel = $warp['config']->get("panel_default.{$module->position}.panel", '');

        // Count modules for debug window
        $childModules = &JModuleHelper::getModules($position);
        $childCount = count($childModules);

        foreach($childModules as $childModule)
        {
            if ($warp['config']->get('widgets.'.$childModule->id.'.panel', '') == '')
                $warp['config']->set('widgets.'.$childModule->id.'.panel', $panel);
        }

//        $warp['config']->set("panel_default.{$position}.panel", $panel);
        
        // render modules
        $args = array('position' => $position);
        
        $innerContents = $warp['template']->render('widgets', $args);
        $rendererComments = '<b>Panel:</b> ' . $panel . '<br/>';
    }
    elseif (file_exists($warpFileName))
    {
        // YOOtheme WARP templating engine support.
                                                       
        // This should initialize warp engine
        include_once(JRoute::_('templates/'.$templateName).'/config.php');

        $warp = Warp::getInstance();
        $modules = $warp->getHelper('modules');

        if (isset($wrapper))
        {
            // WARP how it worked 5.5.14
            $child_module_options = array('style' => $moduleStyle, 'color' => 'templatecolor', 'wrapper' => $wrapper);
        }
        else
        {
            // WARP how it worked 6.0.8
            $child_module_options = array('style' => $moduleStyle, 'color' => 'templatecolor');
        }

        $innerContents = $modules->render($position, $child_module_options);
        $childCount = $modules->count($position);
    }
    else
    {
        $child_module_options = array('style' => $moduleStyle);

        // Pass down Artisteer templates additional chrome/style information
        if (isset($attribs['artstyle']))
            $child_module_options['artstyle'] = $attribs['artstyle'];

        // Pass down joomlashine.com (JSN) templates additional chrome/style information
        if (isset($attribs['class']))
            $child_module_options['class'] = $attribs['class'];

        // gavick.com templates stuff
        if (isset($attribs['name']))
            $child_module_options['name'] = $attribs['name'];

        if (isset($attribs['modcol']))
            $child_module_options['modcol'] = $attribs['modcol'];

        if (isset($attribs['modnum']))
            $child_module_options['modnum'] = $attribs['modnum'];

        if (class_exists('JV'))
        {
            JVFrameworkLoader::import ( 'framework' );
            JV::getInstance ();

            $position_helper = JV::helper('position');
            $child_modules = $position_helper->getModules($position);

            $innerContents = '';

            foreach ($child_modules as $child_module)
            {
                $innerContents .= JModuleHelper::renderModule($child_module, $child_module_options);
            }
            $childCount = count($child_modules);
        }
        else
        {
            $child_modules = &JModuleHelper::getModules($position);
            $innerContents = '';

            foreach ($child_modules as $child_module)
            {
                $innerContents .= JModuleHelper::renderModule($child_module, $child_module_options);
            }
            $childCount = count($child_modules);
        }
    }

    return array('innerContents' => $innerContents,
                 'childCount' => $childCount,
                 'rendererComments' => $rendererComments);
}
