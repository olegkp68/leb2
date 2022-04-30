<?php
/**
 *
 * @author        Sakis Terz
 * @link        http://breakdesigns.net
 * @copyright    Copyright (c) 2014-2020 breakdesigns.net. All rights reserved.
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

class UpdaterBase
{

    /**
     *
     * @var string
     */
    protected $type;

    /**
     * The plugin name which has the dld (or other) setting stored in it's params
     *
     * @var string
     */
    protected $plugin;

    /**
     * The extension that will be updated. Could be the same or different with $this->plugin
     * e.g. Could be a package
     * Make sure that the extension's name is the same as the folder's name that stores the release xml in breakdesigns
     *
     * @var string
     */
    protected $extension;

    /**
     * The extension name as used in the update table
     *
     * @var string
     */
    protected $name;

    /**
     * UpdaterBase constructor.
     */
    public function __construct()
    {
        if (empty($this->extension) || empty($this->name)) {
            throw new \RuntimeException('The extension\'s update check cannot go on, because a mandatory variable (extension, extension name) is missing');
        }
    }

    /**
     * Get the params
     *
     * @return Registry
     */
    protected function getParams()
    {
        if (!isset($this->params)) {
            $pluginName = isset($this->plugin) ? $this->plugin : $this->extension;
            $plugin = JPluginHelper::getPlugin('vmcustom', $pluginName);
            $this->params = new Registry($plugin->params);
        }

        return $this->params;
    }

    /**
     * Get the update id from the updates table
     *
     * @param string $extension
     * @param string $type
     * @since 2.1.0
     */
    public function getExtensionId()
    {
        // Get the extension ID to ourselves
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote($this->type))
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->extension));
        $db->setQuery($query);

        $extension_id = $db->loadResult();

        if (empty($extension_id)) {
            return false;
        }
        return $extension_id;
    }

    /**
     * Refreshes the Joomla! update sites for this extension as needed
     *
     * @return  void
     */
    public function refreshUpdateSite()
    {
        $params = $this->getParams();
        $dlid = trim($params->get('update_dlid', ''));
        $extra_query = null;

        // If I have a valid Download ID I will need to use a non-blank extra_query in Joomla! 3.2+
        if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid)) {
            $extra_query = 'dlid=' . $dlid;
        }

        // Create the update site definition we want to store to the database
        $update_site = array(
            'name' => $this->name,
            'type' => 'extension',
            'location' => 'http://cdn.breakdesigns.net/release/' . $this->extension . '/update.xml',
            'enabled' => 1,
            'last_check_timestamp' => 0,
            'extra_query' => $extra_query
        );

        $extension_id = $this->getExtensionId();

        if (empty($extension_id)) {
            return;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('update_site_id'))
            ->from($db->quoteName('#__update_sites_extensions'))
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));
        $db->setQuery($query);

        $updateSiteIDs = $db->loadColumn();

        // No update sites defined. Create a new one.
        if (!count($updateSiteIDs)) {
            $newSite = (object)$update_site;
            $db->insertObject('#__update_sites', $newSite);
            $id = $db->insertid();

            $updateSiteExtension = (object)array(
                'update_site_id' => $id,
                'extension_id' => $extension_id,
            );
            $db->insertObject('#__update_sites_extensions', $updateSiteExtension);
        } else {
            // Loop through all update sites
            foreach ($updateSiteIDs as $id) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__update_sites'))
                    ->where($db->quoteName('update_site_id') . ' = ' . $db->quote($id));
                $db->setQuery($query);
                $aSite = $db->loadObject();

                // Does the name and location match?
                if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location'])) {
                    // Do we have the extra_query property (J 3.2+) and does it match?
                    if (property_exists($aSite, 'extra_query')) {
                        if ($aSite->extra_query == $update_site['extra_query']) {
                            continue;
                        }
                    } else {
                        // Joomla! 3.1 or earlier. Updates may or may not work.
                        continue;
                    }
                }

                $update_site['update_site_id'] = $id;
                $newSite = (object)$update_site;
                $db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
            }
        }
    }
}
