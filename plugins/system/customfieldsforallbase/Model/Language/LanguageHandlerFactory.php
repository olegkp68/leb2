<?php
/**
 * @package		CustomfieldsforallBase
 * @copyright	Copyright (C)2014-2020 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Language;
require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'LanguageHandler.php';
use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomFieldsForAllLanguageHandler;

class CustomFieldsForAllLanguageHandlerFactory
{
    /**
     *
     * @var Language
     */
    protected $instance;

    /**
     * Returns an instance of LanguageHandler
     *
     * @return CustomFieldsForAllLanguageHandler
     */
    public function get()
    {
        if($this->instance == null) {
            $this->instance = new CustomFieldsForAllLanguageHandler();
        }

        return $this->instance;
    }
}
