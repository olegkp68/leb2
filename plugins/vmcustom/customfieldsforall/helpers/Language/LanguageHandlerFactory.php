<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2018 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
require_once JPATH_PLUGINS.DIRECTORY_SEPARATOR.'vmcustom'.DIRECTORY_SEPARATOR.'customfieldsforall'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'LanguageHandler.php';

class CustomFieldsForAllLanguageHandlerFactory
{
    /**
     *
     * @var Language
     */
    protected $instance;

    /**
     *
     * @return Language
     */
    public function get()
    {
        if($this->instance == null) {
            $this->instance = new CustomFieldsForAllLanguageHandler();
        }

        return $this->instance;
    }
}