<?php
/**
 * @package     CustomfieldsforallBasebase
 *
 * @Copyright   Copyright © 2010-2020 Breakdesigns.net. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

use Joomla\CMS\Factory;

/**
 * Class PlgsystemcustomfieldsforallbaseInstallerScript
 * @since 1.1.1
 */
class PlgsystemcustomfieldsforallbaseInstallerScript
{
    /**
     * This runs before Joomla! installs or updates the package.
     *
     * @param string $type
     * @param \Joomla\CMS\Installer\Adapter\PluginAdapter $adapter
     * @return bool
     * @since 1.1.1
     */
    public function preflight($type, $adapter)
    {
        if($type == 'uninstall') {
            /*
             * Drop the language tables.
             * Otherwise the values table cannot be dropped, due to the fact that is referenced by their FKs
             */
            $this->dropDependentTables();
        }
        return true;
    }

    /**
     * Drop tables, which use Foreign keys to our main table (e.g. virtuemart_custom_plg_customsforall_values)
     *
     * If we do not drop them, the main table cannot be dropped as well (Truncate and Drop Cannot check if the referential integrity is braking, by performing their actions)
     *
     * @param string $likeTableName
     * @return bool
     * @since 1.1.1
     */
    protected function dropDependentTables($likeTableName = 'virtuemart_custom_plg_customsforall_values')
    {
        if(empty($likeTableName)) {
            return false;
        }
        $db = Factory::getDbo();
        $likeTableName = $db->getPrefix() . $likeTableName;
        $query = 'SHOW TABLES LIKE '.$db->quote($likeTableName.'%');
        $db->setQuery($query);
        $columns = $db->loadColumn();

        foreach ($columns as $dbTableName) {
            if($dbTableName != $likeTableName) {
                $query = "DROP TABLE ". $dbTableName;
                $db->setQuery($query);
                try {
                    $db->execute();
                }
                catch (\RuntimeException $exception) {
                    throw new \RuntimeException(sprintf('Cannot drop the table: %s , because of database error:', $dbTableName, $exception->getMessage()));
                }
            }
        }
        return true;
    }
}
