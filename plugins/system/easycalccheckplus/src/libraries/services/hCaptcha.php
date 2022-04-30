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
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\{Factory, Http\HttpFactory};
use Joomla\Registry\Registry;
use EasyCalcCheckPlus\Helper;

class hCaptcha
{
    /**
     * @var string $siteKey
     * @since 3.2.0-FREE
     */
    protected $siteKey = '';

    /**
     * @var string $secretKey
     * @since 3.2.0-FREE
     */
    protected $secretKey = '';

    /**
     * @var string $javaScriptUrl
     * @since 3.2.0-FREE
     */
    protected $javaScriptUrl = 'https://www.hCaptcha.com/1/api.js';

    /**
     * @var string $verifyURL
     * @since 3.2.0-FREE
     */
    protected $verifyURL = 'https://hcaptcha.com/siteverify';

    /**
     * hCaptacha constructor.
     *
     * @param string $siteKey
     * @param string $secret
     *
     * @since 3.2.0-FREE
     */
    public function __construct(string $siteKey, string $secret)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secret;
    }

    /**
     * Gets the required JavaScript code for the head section to show the hCaptcha
     *
     * @return string
     * @since 3.2.0-FREE
     */
    public function getJavaScriptHead(): string
    {
        $languageCode = 'en-GB';
        $languageTag = Factory::getLanguage()->getTag();

        if ($languageCode !== $languageTag) {
            $languageCode = substr($languageTag, 0, 2);
        }

        return '<script src="' . $this->javaScriptUrl . '?hl=' . $languageCode . '" async defer></script>';
    }

    /**
     * Gets the required CSS code for the head section to show the hCaptcha
     *
     * @return string
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function getCssHead(): string
    {
        return '<style>.h-captcha{margin-top: 5px;}</style>';
    }

    /**
     * Gets the required HTML content code to show the hCaptcha
     *
     * @param string $theme
     *
     * @return string
     * @since 3.2.0-FREE
     */
    public function getHtmlContent(string $theme): string
    {
        return '<div class="h-captcha" data-sitekey="' . $this->siteKey . '" data-theme="' . $theme . '"></div>';
    }

    /**
     * Validates the intput against hCaptcha's verification server
     *
     * @return bool
     * @throws Exception
     * @since 3.2.0-FREE
     */
    public function validateInput(): bool
    {
        $requestToken = Factory::getApplication()->input->get('h-captcha-response', null, 'RAW');

        if (empty($requestToken)) {
            return false;
        }

        $data = [
            'secret'   => $this->secretKey,
            'response' => $requestToken,
            'remoteip' => Helper::getIpAddress(),
        ];

        $hCaptchaResponse = HttpFactory::getHttp(new Registry(), 'Curl')->post($this->verifyURL, $data);

        if (!empty($hCaptchaResponse->body)) {
            $hCaptchaResponseBody = json_decode($hCaptchaResponse->body);

            if (isset($hCaptchaResponseBody->success) && $hCaptchaResponseBody->success) {
                return true;
            }
        }

        return false;
    }
}
