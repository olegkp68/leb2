<?php
/**
 * @package		customfieldsforall
 * @copyright	Copyright (C)2014-2018 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class that carries on the translation process
 *
 * @author sakis
 *
 */
class CustomFieldsForAllTranslator
{

    /**
     * The name of the values table
     *
     * @var string
     */
    const TABLE_NAME = 'virtuemart_custom_plg_customsforall_values';

    /**
     * The primary key of the table
     *
     * @var string
     */
    const PRIMARY_KEY = 'customsforall_value_id';

    /**
     * The value field name that stores the translation
     *
     * @var string
     */
    const VALUE_FIELD = 'customsforall_value_name';

    /**
     * The label field name that stores the translation
     *
     * @var string
     */
    const LABEL_FIELD = 'customsforall_value_label';

    /**
     * The alias used for the fields of the defualt language
     *
     * @var string
     */
    const DEFAULT_LANG_ALIAS = 'defaultLang';

    /**
     *
     * @var stdClass
     */
    protected $defaultLanguage;

    /**
     *
     * @var array
     */
    protected $installedLanguages = [];

    /**
     *
     * @var array
     */
    protected $existingLanguageTables;

    /**
     * The cached strings
     *
     * @var array
     */
    protected $strings = [];

    /**
     *
     * @param array $installedLanguages
     */
    public function __construct($installedLanguages)
    {
        $this->installedLanguages = $installedLanguages;
    }

    /**
     * Get the string in the requested language
     *
     * @param int $value_id
     * @param string $lang
     * @param string $group
     * @return string|array
     */
    public function get($customFieldValueRecord, $lang, $group = 'value', $withRecordId = false)
    {
        $primaryKey = self::PRIMARY_KEY;
        $value_id = $customFieldValueRecord->$primaryKey;
        $ouput = '';
        if (! isset($this->strings[$group][$value_id])) {
            $this->build($customFieldValueRecord, $group);
        }
        if (isset($this->strings[$group][$value_id])) {
            if (! empty($this->strings[$group][$value_id][$lang->db_code])) {
                $ouput = $this->strings[$group][$value_id][$lang->db_code];
                if ($withRecordId) {
                    $ouput = [
                        'string' => $ouput,
                        'id' => $this->strings[$group][$value_id][$lang->db_code . '_recordId']
                    ];
                }
            } else {
                $ouput = $this->strings[$group][$value_id][self::DEFAULT_LANG_ALIAS];
                if ($withRecordId) {
                    $ouput = [
                        'string' => $ouput,
                        'id' => 0
                    ];
                }
            }
        } else {
            throw new RuntimeException('The value with the id:' . $value_id . ' cannot be found');
        }

        return $ouput;
    }

    /**
     * Build/adds elements to the array that holds the translations
     *
     * @param stdClass $customFieldValueRecord
     * @param string $group
     * @throws Exception
     * @return CustomFieldsForAllTranslator
     */
    protected function build($customFieldValueRecord, $group)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $existingLanguageTables = $this->getLanguageTables();
        $fieldName = $group == 'label' ? self::LABEL_FIELD : self::VALUE_FIELD;
        $primaryKey = self::PRIMARY_KEY;
        $conditionField = 'virtuemart_custom_id';

        // Nothing to be translated
        if (count($this->installedLanguages) == 1 && reset($this->installedLanguages)->default) {
            $this->strings[$group][$customFieldValueRecord->$primaryKey][self::DEFAULT_LANG_ALIAS] = $customFieldValueRecord->$fieldName;
            return $this;
        }

        try {
            $table = '#__' . self::TABLE_NAME;
            $query->select('defTable.' . $primaryKey . ' AS `value_id`, defTable.' . $fieldName . ' AS ' . self::DEFAULT_LANG_ALIAS)
                ->from($table . ' AS defTable')
                ->where('defTable.' .$conditionField. '=' . (int) $customFieldValueRecord->$conditionField);

            foreach ($this->installedLanguages as $key => $language) {
                $table = '#__' . self::TABLE_NAME . '_' . $language->db_code;
                if (! empty($language->default) || ! isset($existingLanguageTables[$language->db_code])) {
                    continue;
                }

                $query->leftJoin($table . ' ON defTable.' . $primaryKey . '=' . $table . '.' . $primaryKey);
                $query->select($table . '.' . $fieldName . ' AS ' . $language->db_code . ', ' . $table . '.id AS ' . $language->db_code . '_recordId');
            }
            $db->setQuery($query);
            $this->strings[$group] = $db->loadAssocList('value_id');
        } catch (Exception $e) {
            JLog::add('Cannot query the language tables:' . $e->getMessage(), JLog::ERROR);
            throw $e;
        }
        return $this;
    }

    /**
     * Fetch the existing language tables (values)
     *
     * @param string $tableName
     */
    public function getLanguageTables()
    {
        if ($this->existingLanguageTables === null) {
            $db = JFactory::getDbo();
            $tables = $db->getTableList();
            $existTables = [];
            foreach ($this->installedLanguages as $key => $language) {
                $table = $db->getPrefix() . self::TABLE_NAME . '_' . $language->db_code;
                if (in_array($table, $tables)) {
                    $existTables[$language->db_code] = $table;
                }
            }
            $this->existingLanguageTables = $existTables;
        }
        return $this->existingLanguageTables;
    }
}