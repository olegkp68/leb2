<?php
/**
 * JHTMLHelper for Spambotcheck
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
defined('_JEXEC') or die;
/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package      Joomla.Administrator
 * @subpackage   com_spambotcheck
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2013 vi-solutions
 * @since   1.5.5
 */
class JHTMLSpambotcheck
{

  public static function creditsBackend() { ?>
		<div class="spambotcheckbottom" style="text-align: center;">
			Spambotcheck Version <?php echo JHTMLSpambotcheck::getVersion(); ?>, &copy; 2013 Copyright by <a href="http://vi-solutions.de" target="_blank" class="smallgrey">vi-solutions</a>, all rights reserved. 
			Spambotcheck is Free Software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html"target="_blank" class="smallgrey">GNU/GPL License</a>. 
		</div>
	<?php }

	public static function getVersion() {
		$xml_file = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR.'/spambotcheck.xml');
		$installed_version = '1.0.0';
		if(file_exists($xml_file)) {
				$xml = simplexml_load_file($xml_file);
				if ($xml !== false) {
					$installed_version = $xml->version;
				}
			}
		return $installed_version;
	}

	public static function description($view) { ?>
		<div class="clearfix"> </div>
		<div>
			<h1>
			<?php echo \JText::_('COM_SPAMBOTCHECK_DESCRIPTION'); ?>
			</h1>
			<p>
			<?php echo \JText::_('COM_SPAMBOTCHECK_VIEW_' . \Joomla\String\StringHelper::strtoupper($view)  . '_DESCRIPTION'); ?>
			</p>
		</div>
		<?php
	}

	public static function trustStates()
	{
		$states = array(
			0	=> array(
				'task'				=> 'trust',
				'text'				=> '',
				'active_title'		=> 'COM_SPAMBOTCHECK_TRUST_DESC',
				'inactive_title'	=> '',
				'tip'				=> true,
				'active_class'		=> 'unpublish',
				'inactive_class'	=> 'unpublish'
			),
			1	=> array(
				'task'				=> 'distrust',
				'text'				=> '',
				'active_title'		=> 'COM_SPAMBOTCHECK_DISTRUST_DESC',
				'inactive_title'	=> '',
				'tip'				=> true,
				'active_class'		=> 'publish',
				'inactive_class'	=> 'publish'
			)
		);
		return $states;
	}
}
?>