<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2018 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR . 'loader.php';

class CustomFieldsForAllLanguageHandler
{

    /**
     * The path to the flag images
     *
     * @var string
     */
    const FLAG_IMAGE_PATH = 'media/mod_languages/images/';

    /**
     *
     * @var string
     */
    protected $defaultLang;

    /**
     *
     * @var array
     */
    protected $languages = [];

    /**
     *
     * @var string
     */
    protected $appLanguageCode;

    /**
     * Constructor activating the default information of the language.
     *
     * @param string $lang The language code
     */
    public function __construct()
    {
        $this->translator = new CustomFieldsForAllTranslator($this->getLanguages());
    }

    /**
     * Translation function
     *
     * @param stdClass $customFieldValueRecord
     * @param string $lang The lang code
     * @param string $group
     * @param bool $withRecordId returns an array that contains also the recordId
     *
     * @throws RuntimeException
     * @return string
     */
    public function __($customFieldValueRecord, $lang = '', $group = 'value', $withRecordId = false)
    {
        if(empty($lang)) {
            $lang = $this->getAppLanguageCode();
        }
        if (! isset($this->languages[$lang])) {
           $lang =$this->getDefaultLangTag();
        }

        $translated = $this->translator->get($customFieldValueRecord, $this->languages[$lang], $group, $withRecordId);

        //fallback for older versions
        return JFactory::getApplication()->isSite() ? JText::_($translated) : $translated;

    }

    /**
     * Get the language of the app
     *
     * @return string
     */
    public function getAppLanguageCode()
    {
        if($this->appLanguageCode === null) {
            $language = JFactory::getLanguage();
            $this->appLanguageCode = $language->getTag();
        }
        return $this->appLanguageCode;
    }

    /**
     * Create the missing language tables
     *
     * @throws Exception
     * @return Language
     */
    public function createLanguageTables()
    {
        $languages = $this->getLanguages($withDefault = false);
        foreach ($languages as $key => $language) {

            try {
                $installer = new CustomFieldsForAllLanguageInstaller($language);
                $installer->install();
            } catch (Exception $e) {
                throw $e;
                JLog::add($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Return the icon that can be used as multi-lingual
     *
     * @return string
     */
    public function getMultiLingualIcon()
    {
        return JURI::root() . self::FLAG_IMAGE_PATH . 'icon-16-language.png';
    }

    /**
     *
     * @param string $lang
     * @return Language
     */
    public function getDefaultLangTag($lang = null)
    {
        if ($this->defaultLang == null) {
            if ($lang == null) {
                $this->defaultLang = isset(VmConfig::$jDefLangTag) ? VmConfig::$jDefLangTag : (JFactory::getLanguage()->getDefault());
            } else {
                $this->defaultLang = $lang;
            }
        }
        return $this->defaultLang;
    }


    /**
     * Return the translator
     *
     * @return CustomFieldsForAllTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Create/Set an array with language objects
     *
     * @return Language
     */
    protected function setLanguages()
    {
        $vmLanguages = VmConfig::get('active_languages', array(
            $this->getDefaultLangTag()
        ));

        $defaultLanguage = [];
        $myLanguages = [];
        $jLanguages = JLanguageHelper::getLanguages();
        foreach ($jLanguages as $jLanguage) {
            if (in_array($jLanguage->lang_code, $vmLanguages)) {
                $myLanguages[$jLanguage->lang_code] = $jLanguage;
            }
        }

        $installedLanguages = JLanguageHelper::getKnownLanguages();
        foreach ($installedLanguages as $langCode => $installedLanguage) {
            if (! in_array($langCode, $vmLanguages)) {
                continue;
            }

            if (isset($myLanguages[$langCode])) {
                $language = clone $myLanguages[$langCode];
            } else {
                $language = $this->createLanguageFromArray($langCode, $installedLanguage);
            }

            $language->db_code = $this->getDbLanguageCode($language);
            $this->setFlagImagePath($language);

            // set the default
            if ($this->getDefaultLangTag() == $language->lang_code) {
                $language->default = true;
                $defaultLanguage = [
                    $langCode => $language
                ];
            } else {
                $this->languages[$langCode] = $language;
            }
        }
        // put the default always 1st
        $this->languages = array_merge($defaultLanguage, $this->languages);
        return $this;
    }

    /**
     *
     * @return array|null
     */
    public function getLanguages($withDefault = true)
    {
        if(empty($this->languages)) {
            $this->setLanguages();
        }

        if ($withDefault) {
            return $this->languages;
        }

        $languages = [];
        foreach ($this->languages as $key => $language) {

            if (isset($language->default)) {
                continue;
            }
            $languages[$key] = $language;
        }
        return $languages;
    }

    /**
     * Return other than the default lang tags as used in the database
     *
     * @return array
     */
    protected function getDbLanguageCode($jLanguage)
    {
        if (empty($jLanguage)) {
            throw new RuntimeException('The language object is missing');
        }

        $tag = strtolower(str_replace('-', '_', $jLanguage->lang_code));
        return $tag;
    }

    /**
     * Generates a language object from array
     *
     * @param string $langCode
     * @param array $langArray
     * @return stdClass
     */
    protected function createLanguageFromArray($langCode, $langArray)
    {
        $language = new stdClass();
        $language->image = substr($langCode, 0, 2);
        $language->lang_code = $langCode;
        $language->title_native = isset($langArray['nativeName']) ? $langArray['nativeName'] : $langArray['name'];
        return $language;
    }

    /**
     * Sets the language image path
     *
     * @param stdCladd $language
     * @return CustomFieldsForAllLanguageHandler
     */
    protected function setFlagImagePath($language)
    {
        $imageExtensions = [
            'gif',
            'png',
            'jpg',
            'bmp'
        ];

        if(strpos($language->image, 'http') === false) {
            $language->image = JURI::root() . self::FLAG_IMAGE_PATH . $language->image;
        }
        $parts = explode('.', $language->image);
        $extension = end($parts);
        if(!isset($extension) || !in_array($extension, $imageExtensions)){
            $language->image.='.gif';
        }

        return $this;
    }
}