<?php
/**
 *
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2014-2020 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class CustomFieldsForAllUpdate extends UpdaterBase
{

    /**
     *
     * @var string
     */
    protected $type = 'plugin';

    /**
     *
     * @var string
     */
    protected $extension='customfieldsforall';

    /**
     * The extension name as used in the update table
     *
     * @var string
     */
    protected $name='Custom Fields For All';


    /**
     * The id of the update stream
     *
     * @var int
     */
    protected $streamId = 6;

}
