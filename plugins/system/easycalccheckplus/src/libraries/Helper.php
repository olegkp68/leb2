<?php

/**
 * @copyright
 * @package        EasyCalcCheck Plus - ECC+ for Joomla! 3
 * @author         Viktor Vogel <admin@kubik-rubik.de>
 * @version        3.3.0.0-FREE - 2021-05-03
 * @link           https://kubik-rubik.de/ecc-easycalccheck-plus
 *
 * @license        GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace EasyCalcCheckPlus;

defined('_JEXEC') || die('Restricted access');

use Exception;
use Joomla\CMS\{Factory, Language\Text};

/**
 * Helper class for EasyCalcCheckPlus
 *
 * @package EasyCalcCheckPlus
 * @since   3.2.0-FREE
 * @version 3.3.0.0-FREE
 */
class Helper
{
    /**
     * @var string $pluginId
     * @since 3.2.0-FREE
     */
    protected const PLUGIN_ID = 'easycalccheckplus';

    /**
     * Determines correct IP address (correct usage also with a proxy)
     *
     * @return string
     * @since 3.2.0-FREE
     */
    public static function getIpAddress(): string
    {
        $ipAddress = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
        $headers = $_SERVER;

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }

        // Get the forwarded IP if it exists
        if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $ipAddress = $headers['X-Forwarded-For'];
        } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $ipAddress = $headers['HTTP_X_FORWARDED_FOR'];
        }

        return (string)$ipAddress;
    }

    /**
     * Converts numbers into strings
     *
     * @param int $number
     *
     * @return string
     * @throws Exception
     * @version 3.2.1-FREE
     * @since   3.2.0-FREE
     */
    public static function convertToString(int $number): string
    {
        // Probability 2/3 for conversion
        $random = random_int(1, 3);

        if ($random !== 1) {
            if ($number > 20) {
                return (string)$number;
            }

            // Names of the numbers are read from language file
            $names = [
                'PLG_SYSTEM_EASYCALCCHECKPLUS_NULL',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_ONE',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_TWO',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_THREE',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_FOUR',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_FIVE',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_SIX',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_SEVEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_EIGHT',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_NINE',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_TEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_ELEVEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_TWELVE',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_THIRTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_FOURTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_FIFTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_SIXTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_SEVENTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_EIGHTEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_NINETEEN',
                'PLG_SYSTEM_EASYCALCCHECKPLUS_TWENTY',
            ];

            return Text::_($names[$number]);
        }

        return (string)$number;
    }

    /**
     * Redirects if spam check was not passed successfully
     *
     * @param string $redirectUrl
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function redirect(string $redirectUrl): void
    {
        try {
            Factory::getApplication()->redirect($redirectUrl);
        } catch (Exception $e) {
            // White page - if redirection doesn't work
            jexit(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_YOUHAVENOTRESOLVEDOURSPAMCHECK'));
        }
    }

    /**
     * Cleans cache to avoid inconsistent output
     *
     * @throws Exception
     * @version 3.2.1-FREE
     * @since   3.3.0.0-FREE
     */
    public static function cleanCache(): void
    {
        $config = Factory::getConfig();

        if ((bool)$config->get('caching') !== false) {
            $cache = Factory::getCache(Factory::getApplication()->input->get('option', '', 'WORD'));
            $cache->clean();
        }
    }

    /**
     * SQL injection and file inclusion check
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function sqlInjectionLocalFileInclusionCheck(): void
    {
        $requestMethods = ['GET', 'POST', 'REQUEST'];

        foreach ($requestMethods as $requestMethod) {
            // Raw request input required for the checks, do not go through Joomla!'s input class
            if ($requestMethod === 'GET') {
                $requestMethodVariable = $_GET;
            } elseif ($requestMethod === 'POST') {
                $requestMethodVariable = $_POST;
            } elseif ($requestMethod === 'REQUEST') {
                $requestMethodVariable = $_REQUEST;
            }

            if (!empty($requestMethodVariable)) {
                foreach ($requestMethodVariable as $key => $value) {
                    if (is_numeric($value) || is_array($value)) {
                        continue;
                    }

                    $a = preg_replace('@/\*.*?\*/@s', ' ', $value);

                    if (preg_match('@UNION(?:\s+ALL)?\s+SELECT@i', $a)) {
                        throw new Exception(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_INTERNALERRORSQLINJECTION'), 500);
                    }

                    $pDbprefix = Factory::getApplication()->get('dbprefix');
                    $ta = [
                        '/(\s+|\.|,)`?(#__)/',
                        '/(\s+|\.|,)`?(jos_)/i',
                        "/(\s+|\.|,)`?({$pDbprefix}_)/i",
                    ];

                    foreach ($ta as $t) {
                        if (preg_match($t, $value)) {
                            throw new Exception(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_INTERNALERRORSQLINJECTION'), 500);
                        }
                    }

                    if (in_array($key, ['controller', 'view', 'model', 'template'])) {
                        $recurse = str_repeat('\.\.\/', 2);

                        while (preg_match('@' . $recurse . '@', $value)) {
                            throw new Exception(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_INTERNALERRORSQLINJECTION'), 500);
                        }
                    }
                }
            }
        }
    }

    /**
     * Saves entered data into the session
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function saveData(): void
    {
        $request = Factory::getApplication()->input->getArray($_REQUEST);
        $dataArray = [];
        $keysExclude = [
            'option',
            'view',
            'layout',
            'id',
            'Itemid',
            'task',
            'controller',
            'func',
        ];

        foreach ($request as $key => $value) {
            if (!in_array($key, $keysExclude)) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        // Need second request for user profile plugin
                        if (is_array($value2)) {
                            foreach ($value2 as $key3 => $value3) {
                                $key4 = $key . '[' . ($key2 === 0 ? '' : $key2) . '][' . ($key3 === 0 ? '' : $key3) . ']';
                                $dataArray[$key4] = $value3;
                            }

                            continue;
                        }

                        $key3 = $key . '[' . ($key2 === 0 ? '' : $key2) . ']';
                        $dataArray[$key3] = $value2;
                    }

                    continue;
                }

                $dataArray[$key] = $value;
            }
        }

        self::setSession('savedData', $dataArray);
    }

    /**
     * Sets a session variable
     *
     * @param string       $name
     * @param string|array $value
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function setSession(string $name, $value): void
    {
        Factory::getSession()->set($name, $value, self::PLUGIN_ID);
    }

    /**
     * Decodes encoded fields
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function decodeFields(): void
    {
        $input = Factory::getApplication()->input;
        $encodedVariablesSession = self::getSession('fieldsEncoded', '');

        if (!empty($encodedVariablesSession)) {
            $form = [];
            $encodedVariables = unserialize(base64_decode($encodedVariablesSession));

            foreach ($encodedVariables as $key => $value) {
                $valueRequest = $input->get($value, null, 'STRING');

                // Decode variable only if it is set!
                if (isset($valueRequest)) {
                    // Is this decoded variable transmitted in the request?
                    if (!empty($valueRequest)) {
                        // If key is an array, then handle it correctly
                        if (preg_match('@(.*)\[(.+)\]@isU', $key, $matches)) {
                            $form[$matches[1]][$matches[2]] = $valueRequest;
                        } else {
                            $form[$key] = $valueRequest;
                        }

                        // Unset the decoded variable from the request
                        $input->set($value, '');

                        continue;
                    }

                    // If key is an array, then handle it correctly
                    if (preg_match('@(.*)\[(.+)\]@isU', $key, $matches)) {
                        $form[$matches[1]][$matches[2]] = '';
                    } else {
                        $form[$key] = '';
                    }

                    // Unset the decoded variable from the request
                    $input->set($value, '');
                }
            }

            // Set the decoded fields back to the request variable
            foreach ($form as $key => $value) {
                $input->set($key, $value);

                // We also need to set the variable to the global $_POST variable - needed for the token check of the components
                // Do not use the API because we need first to gather all information - set variables directly
                $_POST[$key] = $value;
            }

            self::clearSession('fieldsEncoded');
        }
    }

    /**
     * Gets a session variable
     *
     * @param string      $name
     * @param string|null $default
     *
     * @return mixed
     * @since 3.2.0-FREE
     */
    public static function getSession(string $name, $default = null)
    {
        return Factory::getSession()->get($name, $default, self::PLUGIN_ID);
    }

    /**
     * Clears a session variable
     *
     * @param string $name
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function clearSession(string $name): void
    {
        Factory::getSession()->clear($name, self::PLUGIN_ID);
    }

    /**
     * Encodes input fields
     *
     * @param string $body
     * @param string $matchExtension
     * @param string $extensionInfoException
     *
     * @throws Exception
     * @version 3.2.1-FREE
     * @since   3.2.0-FREE
     */
    public static function encodeFields(string &$body, string $matchExtension, string $extensionInfoException): void
    {
        $patternEncode = '@<[^>]+(name=("|\')([^>]*)("|\'))[^>]*>@isU';
        preg_match_all($patternEncode, $matchExtension, $matchesEncode);

        $matchEncodeReplacement = [];

        // Add global exceptions - this fields should not be renamed to avoid execution errors
        $replaceNot = [
            'option',
            'view',
            'task',
            'func',
            'layout',
            'controller',
        ];

        // Add exceptions from extension if provided
        if (!empty($extensionInfoException)) {
            $replaceNot = array_merge($replaceNot, array_map('trim', explode(',', $extensionInfoException)));
        }

        $fieldsEncoded = [];

        foreach ($matchesEncode[3] as $key => $match) {
            if (!in_array($match, $replaceNot)) {
                $random = self::randomString();
                $fieldsEncoded[$match] = $random;
                $matchEncodeReplacement[$key] = str_replace($matchesEncode[1][$key], 'name="' . $random . '"', $matchesEncode[0][$key]);
            } else {
                unset($matchesEncode[0][$key]);
            }
        }

        if (!empty($fieldsEncoded)) {
            self::setSession('fieldsEncoded', base64_encode(serialize($fieldsEncoded)));
        }

        if (!empty($matchEncodeReplacement)) {
            $body = str_replace($matchesEncode[0], $matchEncodeReplacement, $body);
        }
    }

    /**
     * Creates one or an array of (pseudo-)random strings
     *
     * @param int $count
     *
     * @return mixed - if $count is 1, then string else array
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public static function randomString(int $count = 1)
    {
        $characters = range('a', 'z');
        $numbers = range(0, 9);
        $stringArray = [];

        for ($i = 0; $i < $count; $i++) {
            $string = '';

            // first character has to be a letter
            $string .= $characters[random_int(0, 25)];

            // other characters arbitrarily
            $characters = array_merge($characters, $numbers);

            $stringLength = random_int(4, 12);

            for ($a = 0; $a < $stringLength; $a++) {
                $string .= $characters[random_int(0, 35)];
            }

            $stringArray[] = $string;
        }

        if ($count === 1) {
            $stringArray = $stringArray[0];
        }

        return $stringArray;
    }
}
