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

use EasyCalcCheckPlus\Helper;
use Joomla\CMS\{Plugin\CMSPlugin, Factory, User\User, Uri\Uri, Router\Route, Language\Text, Plugin\PluginHelper, Application\SiteApplication, Document\HtmlDocument};
use Joomla\Input\Input;

/**
 * Class PlgSystemEasyCalcCheckPlus
 *
 * @since   3.2.0-FREE
 * @version 3.3.0.0-FREE
 */
class PlgSystemEasyCalcCheckPlus extends CMSPlugin
{
    /**
     * Custom call syntax
     *
     * @since 3.2.1-FREE
     */
    private const CUSTOM_CALL_SYNTAX = '{easycalccheckplus}';

    /**
     * @var SiteApplication $app
     * @since 3.2.0-FREE
     */
    protected $app;

    /**
     * @var bool $autoloadLanguage
     * @since 3.2.0-FREE
     */
    protected $autoloadLanguage = true;

    /**
     * @var bool $customCall
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    protected $customCall = false;

    /**
     * @var bool $debugPlugin
     * @since 3.2.0-FREE
     */
    protected $debugPlugin = false;

    /**
     * @var array $extensionInfo
     * @since 3.2.0-FREE
     */
    protected $extensionInfo = [];

    /**
     * @var bool $loadEcc
     * @since 3.2.0-FREE
     */
    protected $loadEcc = false;

    /**
     * @var bool $loadEccCheck
     * @since 3.2.0-FREE
     */
    protected $loadEccCheck = false;

    /**
     * @var string $redirectUrl
     * @since 3.2.0-FREE
     */
    protected $redirectUrl;

    /**
     * @var Input $request
     * @since 3.2.0-FREE
     */
    protected $request;

    /**
     * @var User $user
     * @since 3.2.0-FREE
     */
    protected $user;

    /**
     * @var bool $warningShown
     * @since 3.2.0-FREE
     */
    protected $warningShown;

    /**
     * @var hCaptcha $hCaptcha
     * @since 3.2.0-FREE
     */
    protected $hCaptcha;

    /**
     * PlgSystemEasyCalcCheckPlus constructor.
     *
     * @param object $subject
     * @param array  $config
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public function __construct(object $subject, array $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Initialise ECC, Purge Cache, Bot-Trap, SQL Injection Protection & Backend Token
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function onAfterInitialise(): void
    {
        $this->initialise();

        // Okay, if we use the encoding option, then we have to decode all fields as soon as possible to avoid errors
        // Decode all input fields but only if debug plugin is not used
        if (!$this->debugPlugin && $this->getParams('encode')) {
            Helper::decodeFields();
        }

        // SQL injection and file inclusion check
        if ($this->getParams('sqlInjectionLfi')) {
            Helper::sqlInjectionLocalFileInclusionCheck();
        }

        // Backend protection
        if ($this->getParams('backendProtection')) {
            $this->checkBackendProtection();
        }
    }

    /**
     * Initialises the plugin
     *
     * @throws Exception
     * @since 3.2.1-FREE
     */
    private function initialise(): void
    {
        require_once __DIR__ . '/src/libraries/Helper.php';

        $this->app = Factory::getApplication();
        $this->redirectUrl = Helper::getSession('redirectUrl');
        Helper::clearSession('redirectUrl');

        if (empty($this->redirectUrl)) {
            $this->redirectUrl = Uri::getInstance()->toString();
        }

        $this->request = new Input();
        $this->user = Factory::getUser();
        $this->debugPlugin = $this->getDebugPluginStatus();
    }

    /**
     * Checks whether the debug plugin is activated - important workaround for Joomla! 3.x
     * This is important because if the plugin is activated, then the input request variables are set before ECC+
     * can decode them back. This means that the components do not use the correct request variables if the option
     * "Encode all fields" is used in ECC+. If the plugin is activated, then we cannot use the encode functionality!
     *
     * @return bool
     * @since 3.2.1-FREE
     */
    private function getDebugPluginStatus(): bool
    {
        if (PluginHelper::isEnabled('system', 'debug')) {
            return true;
        }

        return false;
    }

    /**
     * Gets legacy paths (underscore instead of camelcase) if new paths were not set yet after an update
     *
     * @param string $path
     * @param mixed  $default
     *
     * @return mixed
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function getParams(string $path, $default = null)
    {
        $param = $this->params->get($path);

        if ($param !== null) {
            return $param;
        }

        $pathLegacy = strtolower(preg_replace('@(?<!^)[A-Z]@', '_$0', $path));
        $paramLegacy = $this->params->get($pathLegacy);

        return $paramLegacy ?? $default;
    }

    /**
     * Checks the backend protection token if the requirements are met
     *
     * @since 3.2.1-FREE
     */
    private function checkBackendProtection(): void
    {
        if ($this->user->guest && $this->app->isClient('administrator')) {
            $tokenSession = Helper::getSession('token', false);

            if (!$tokenSession) {
                $requestToken = $this->request->get('token', null, 'RAW');

                if ($requestToken === null) {
                    $this->redirectBackendToken();
                }

                $token = $this->getParams('token', '');

                // Conversion to UTF8 (german umlauts)
                if (utf8_encode($requestToken) !== $token) {
                    $this->redirectBackendToken();
                }

                // Token is transferred properly, set session value to suppress further checks
                Helper::setSession('token', true);
            }
        }
    }

    /**
     * Redirects the request to the backend if correct token is not provided
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function redirectBackendToken(): void
    {
        $url = $this->getParams('urlFalseToken');

        if (empty($url)) {
            $url = Uri::root();
        }

        Helper::clearSession('token');
        Helper::redirect($url);
    }

    /**
     * Detects whether the plugin routine has to be loaded and call the checks
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function onAfterRoute(): void
    {
        // Check whether ECC has to be loaded
        $option = $this->request->get('option', '', 'WORD');
        $view = $this->request->get('view', '', 'WORD');
        $task = $this->request->get('task', '', 'CMD');
        $func = $this->request->get('func', '', 'WORD');
        $layout = $this->request->get('layout', '', 'WORD');

        $this->loadEcc($option, $task, $view, $func, $layout);

        // If the custom call is activated, then the input has to be checked here to intercept the process handling of the extension
        if ($this->getParams('customCall') && $this->loadEccUserContext()) {
            // Load error notice if needed
            $this->raiseErrorWarning($option, true);

            // Determine whether the check was already loaded and the data have to be validated
            $checkCustomCall = (array)Helper::getSession('checkCustomCall', []);
            Helper::clearSession('checkCustomCall');

            if (!empty($checkCustomCall)) {
                // Get all request variables for the check
                $request = $this->request->getArray($_REQUEST);
                $customCallPerformCheck = false;

                // Go through all request variable until one hit to check whether the form was submitted by the user
                foreach ($checkCustomCall as $requestVariable) {
                    if (preg_match('@(.*)\[(.*)\]$@', $requestVariable, $requestVariablematch)) {
                        if (isset($request[$requestVariablematch[1]][$requestVariablematch[2]])) {
                            $customCallPerformCheck = true;
                        }
                    } elseif (array_key_exists($requestVariable, $request)) {
                        $customCallPerformCheck = true;
                    }

                    if ($customCallPerformCheck) {
                        // Clean cache
                        Helper::cleanCache();

                        // Save entered values in session for autofill
                        if ($this->getParams('autofillValues')) {
                            Helper::saveData();
                        }

                        // Do the checks to protect the custom form
                        if (!$this->performChecks()) {
                            $this->perfomChecksFailed('checkFailedCustom');
                        }

                        break;
                    }
                }
            }
        }

        // Clean cache of component if ECC+ has to be loaded
        if ($this->loadEccCheck === true || $this->loadEcc === true) {
            Helper::cleanCache();
        }

        if ($this->loadEccCheck === true) {
            // Save entered values in session for autofill
            if ($this->getParams('autofillValues')) {
                Helper::saveData();
            }

            // Call checks for forms
            $this->callChecks($option, $task);
        }

        if ($this->loadEcc === true) {
            // Raise error warning if needed - do the check
            $this->raiseErrorWarning($option);

            // Write head data
            $head = [];
            $head[] = '<style>#easycalccheckplus {margin: 8px 0 !important; padding: 2px !important;}</style>';

            if ($option === 'com_flexicontactplus' && $this->getParams('flexicontactplus')) {
                $head[] = '<style>#easycalccheckplus label {width: auto;}</style>';
            }

            if ($option === 'com_comprofiler' && $this->getParams('communitybuilder')) {
                $head[] = '<style>#easycalccheckplus label {width: 100%;} #easycalccheckplus input {height: 34px;}</style>';
            }

            if ($this->getParams('typeHidden')) {
                $hiddenClass = Helper::randomString();
                $head[] = '<style>.' . $hiddenClass . ' {display: none !important;}</style>';

                if ($option === 'com_foxcontact' && $this->getParams('foxcontact')) {
                    $head[] = '<style>label.' . $hiddenClass . ' {margin: 0 !important; padding: 0 !important;}</style>';
                }

                Helper::setSession('hiddenClass', $hiddenClass);
            }

            if ($option === 'com_kunena' && $this->getParams('kunena') && $this->getParams('recaptcha')) {
                $head[] = '<style>div#recaptcha_area{margin: auto !important;}</style>';
            }

            if ($option === 'com_kunena' && $this->getParams('kunena')) {
                $head[] = '<style>a#btn_qreply{display:none;}</style>';
            }

            if ($this->getParams('recaptcha')) {
                $this->getRecaptchaHead($head);
            }

            if ($this->hCaptcha()) {
                $head[] = $this->hCaptcha->getCssHead();
                $head[] = $this->hCaptcha->getJavaScriptHead();
            }

            $head = "\n" . implode("\n", array_filter($head)) . "\n";
            /** @var HtmlDocument $document */
            $document = Factory::getDocument();

            if ($document->getType() === 'html') {
                $document->addCustomTag($head);
            }
        }

        // Workaround for Kunena - Remove Quick Reply button, only allow full editor replies
        if ($this->user->guest && $option === 'com_kunena' && $this->getParams('kunena')) {
            /** @var HtmlDocument $document */
            $document = Factory::getDocument();

            if ($document->getType() === 'html') {
                $document->addCustomTag('<style>a#btn_qreply, a.Kreplyclick{display:none;}</style>');
            }
        }
    }

    /**
     * Checks whether ECC+ has to be loaded in normal call and defines check rules depending on the loaded component
     *
     * @param string $option
     * @param string $task
     * @param string $view
     * @param string $func
     * @param string $layout
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function loadEcc(string $option, string $task, string $view, string $func, string $layout): void
    {
        $this->loadEccSetRules($option, $task, $view, $func, $layout);

        // Clear userLogin session variable to avoid errors if a user logs in via the module
        if ($this->getParams('userLogin') && Helper::getSession('userLogin')) {
            if ($option === 'com_users') {
                if ($this->loadEcc === false) {
                    Helper::clearSession('userLogin');
                }
            } else {
                Helper::clearSession('userLogin');
            }
        }

        Helper::clearSession('eccLoaded');
    }

    /**
     * Defines checking rules depending on the loaded component
     *
     * @param string $option
     * @param string $task
     * @param string $view
     * @param string $func
     * @param string $layout
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function loadEccSetRules(string $option, string $task, string $view, string $func, string $layout): void
    {
        if (!$this->loadEccUserContext()) {
            $this->loadEcc = false;
            $this->loadEccCheck = false;

            return;
        }

        // Find out if ECC+ has to be loaded depending on the called component
        if ($option === 'com_contact') {
            // Array -> (name, form, regex for hidden field, regex for output, task, request exception for encode option);
            $this->extensionInfo = [
                'com_contact',
                '<form[^>]+id="contact-form".+</form>',
                '<label id="jform_contact.+>',
                '<button class="btn btn-primary validate" type="submit">',
                'contact.submit',
            ];

            if ($view === 'contact' && $this->getParams('contact')) {
                $this->loadEcc = true;
            }

            if ($task === 'contact.submit' && $this->getParams('contact')) {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_mailto') {
            $this->extensionInfo = [
                'com_mailto',
                '<form[^>]+id="mailtoForm".+</form>',
                '<label for=".+_field".+>',
                '<button type="button" class="button" onclick=".+submitbutton.+">',
                'send',
            ];

            if ($this->getParams('mailtoContent')) {
                if ($task !== 'send') {
                    $this->loadEcc = true;
                } else {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_users') {
            if ($layout !== 'confirm' && $layout !== 'complete') {
                if ($view === 'registration') {
                    $this->extensionInfo = [
                        'com_users',
                        '<form[^>]+id="member-registration".+</form>',
                        '<label id="jform.+>',
                        '<button type="submit" class="validate">',
                        'registration.register',
                    ];
                } elseif ($view === 'reset' || $view === 'remind') {
                    $this->extensionInfo = [
                        'com_users',
                        '<form[^>]+id="user-registration".+</form>',
                        '<label id="jform_email-lbl"',
                        '<button type="submit">',
                        'registration.register',
                    ];
                } elseif ($view === 'login' || $view === '') {
                    $this->extensionInfo = [
                        'com_users',
                        '<form[^>]+(task=user\.login.+>|>.+value="user\.login")+.+</form>',
                        '<label id=".+"',
                        '<button type="submit" class=".*">',
                        'registration.register',
                    ];
                }

                if (($view === 'registration' || $view === 'reset' || $view === 'remind') && $this->getParams('userRegistration')) {
                    $this->loadEcc = true;
                }

                if (($task === 'registration.register' || $task === 'reset.request' || $task === 'remind.remind') && $this->getParams('userRegistration')) {
                    $this->loadEccCheck = true;
                }

                if (($view === 'login' || $view === '') && ($task === '') && $this->getParams('userLogin')) {
                    $this->loadEcc = true;
                    Helper::setSession('userLogin', 1);
                } elseif ($task === 'user.login' && $this->getParams('userLogin')) {
                    $userLoginCheck = Helper::getSession('userLogin');

                    if (!empty($userLoginCheck)) {
                        $this->loadEccCheck = true;
                    } else {
                        $failedLoginAttempts = Helper::getSession('failedLoginAttempts');

                        if (empty($failedLoginAttempts)) {
                            $failedLoginAttempts = 0;
                        }

                        if ($failedLoginAttempts >= $this->getParams('userLoginAttempts')) {
                            $this->perfomChecksFailed('loginAttempts', Route::_('index.php?option=com_users&view=login', false));
                        }
                    }
                }
            }
        } elseif ($option === 'com_easybookreloaded' && $this->getParams('easybookreloaded')) {
            // Easybook Reloaded 3.3.1
            $this->extensionInfo = [
                'com_easybookreloaded',
                '<form[^>]+name=("|\')gbookForm("|\').+</form>',
                '<input type=.+>',
                '<p id="easysubmit">',
                'save',
                'gbookForm, gbvote, gbtext',
            ];

            if ($task === 'add') {
                $this->loadEcc = true;
            } elseif ($task === 'save') {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_phocaguestbook' && $this->getParams('phocaguestbook')) {
            // Phoca Guestbook 3.0.6
            $this->extensionInfo = [
                'com_phocaguestbook',
                '<form.+com_phocaguestbook.*</form>',
                '<input type=.+>',
                '<div class="btn-group">\s*<button type="submit".*>',
                'submit',
            ];

            if ($view === 'guestbook' && $task !== 'phocaguestbook.submit') {
                $this->loadEcc = true;
            } elseif ($task === 'phocaguestbook.submit') {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_comprofiler' && $this->getParams('communitybuilder')) {
            // Community Builder 2.4.5
            $eccLoaded = Helper::getSession('eccLoaded', false);

            if ($task === 'registers' || $view === 'registers' || $view === 'saveregisters') {
                $this->extensionInfo = [
                    'com_comprofiler',
                    '<form[^>]+id="cbcheckedadminForm".+</form>',
                    '<label for=".+>',
                    '<input type="submit" value=".+" class="button" />',
                    'saveregisters',
                ];
            } elseif ($task === 'lostpassword' || $view === 'lostpassword') {
                $this->extensionInfo = [
                    'com_comprofiler',
                    '<form[^>]+id="adminForm".+</form>',
                    '<label for=".+>',
                    '<input type="submit" class="button" id="cbsendnewuspass" value=".+" />',
                    'sendNewPass',
                ];
            }

            if ($task === 'registers' || $view === 'registers' || $task === 'lostpassword' || $view === 'lostpassword') {
                $this->loadEcc = true;
            } elseif ($task === 'saveregisters' || $view === 'saveregisters' || $task === 'sendNewPass' || $view === 'sendnewpass') {
                if ($eccLoaded === true) {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_dfcontact' && $this->getParams('dfcontact')) {
            // DFContact - tested with version 1.6.6
            $this->extensionInfo = [
                'com_dfcontact',
                '<form[^>]+id="dfContactForm".+</form>',
                '<label for="dfContactField.+>',
                '<input type="submit" value=".+" class="button" />',
            ];

            if ($view === 'dfcontact') {
                if (empty($this->app->input->get('submit', ''))) {
                    $this->loadEcc = true;
                } else {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_foxcontact' && $this->getParams('foxcontact')) {
            // FoxContact - tested with version 3.8.2
            $this->extensionInfo = [
                'com_foxcontact',
                '<form[^>]+name="fox-form-c\d+".+</form>',
                '<input id="fox-c.+>',
                '<button type="submit" [^>]*/>',
            ];

            if ($view === 'foxcontact') {
                if ($task === 'form.send') {
                    $this->loadEccCheck = true;
                } else {
                    $this->loadEcc = true;
                }
            }
        } elseif (($option === 'com_flexicontact' && $this->getParams('flexicontact')) || ($option === 'com_flexicontactplus' && $this->getParams('flexicontactplus'))) {
            // FlexiContact 10.05 / FlexiContact Plus - tested with version 6.07
            $regexOutput = '<input type="submit" class=".+".*name="send_button".+/>';

            if ($option === 'com_flexicontactplus') {
                $regexOutput = '<div class="fcp_sendrow">';
            }

            $this->extensionInfo = [
                $option,
                '<form[^>]+name="fc.?_form".+</form>',
                '<input type=.+>',
                $regexOutput,
                'send',
            ];

            if ($view === 'contact') {
                if (empty($task)) {
                    $this->loadEcc = true;
                } elseif ($task === 'send') {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_kunena' && $this->getParams('kunena')) {
            // Kunena Forum 5.0.7
            $this->extensionInfo = [
                'com_kunena',
                '<form[^>]+id="postform".+</form>',
                '<input type=.+>',
                '<button[^>]+type="submit"[^>]+>',
                'post',
            ];

            if (($func === 'post' || ($view === 'topic' && ($layout === 'reply' || $layout === 'create' || $layout === ''))) && $task !== 'post') {
                $this->loadEcc = true;
            } elseif ($func === 'post' || $task === 'post') {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_alfcontact' && $this->getParams('alfcontact')) {
            // ALFContact 3.2.6
            $this->extensionInfo = [
                'com_alfcontact',
                '<form[^>]+id="contact-form".+</form>',
                '<label for=".+>',
                '<button class="button">',
                'sendemail',
            ];

            if ($view === 'alfcontact' && empty($task)) {
                $this->loadEcc = true;
            } elseif ($task === 'sendemail') {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_aicontactsafe' && $this->getParams('aicontactsafe')) {
            // aiContactSafe - tested with version 2.0.19
            $this->extensionInfo = [
                'com_aicontactsafe',
                '<form[^>]+id="adminForm_.+</form>',
                '<label for=".+>',
                '<input type="submit" id="aiContactSafeSendButton"',
                'display',
            ];

            $sTask = $this->request->get('sTask', '', 'STRING');

            if (empty($sTask)) {
                $this->loadEcc = true;
            } elseif ($sTask === 'message') {
                $this->loadEccCheck = true;
            }
        } elseif ($option === 'com_community' && $this->getParams('jomsocial')) {
            // JomSocial - tested with version 2.6 RC2
            $this->extensionInfo = [
                'com_community',
                '<form[^>]+id="jomsForm".+</form>',
                '<label id=".+>',
                '<div[^>]+cwin-wait.*></div>',
                'register_save',
            ];

            if ($view === 'register') {
                if ($task === '' || $task === 'register') {
                    $this->loadEcc = true;
                } elseif ($task === 'register_save') {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_virtuemart' && $this->getParams('virtuemart')) {
            // Virtuemart - tested with version 2.0.24a
            if ($task === 'askquestion' || $task === 'mailAskquestion') {
                $this->extensionInfo = [
                    'com_virtuemart',
                    '<form[^>]+id="askform".+</form>',
                    '<label>',
                    '<input[^>]*type="submit" name="submit_ask"[^>]*/>',
                    'mailAskquestion',
                ];

                if ($view === 'productdetails') {
                    if ($task === 'askquestion') {
                        $this->loadEcc = true;
                    } elseif ($task === 'mailAskquestion') {
                        $this->loadEccCheck = true;
                    }
                }
            } elseif ($task === 'editaddresscheckout' || $task === 'registercheckoutuser' || $task === 'savecheckoutuser') {
                $this->extensionInfo = [
                    'com_virtuemart',
                    '<form[^>]+id="userForm".+</form>',
                    '<label.+>',
                    '<button[^>]*type="submit"[^>]*>',
                    'savecheckoutuser',
                ];

                if ($view === 'user') {
                    if ($task === 'editaddresscheckout') {
                        $this->loadEcc = true;
                    } elseif ($task === 'registercheckoutuser' || $task === 'savecheckoutuser') {
                        $this->loadEccCheck = true;
                    }
                }
            } elseif ($task === 'editaddresscart' || $task === 'registercartuser' || $task === 'savecartuser') {
                $this->extensionInfo = [
                    'com_virtuemart',
                    '<form[^>]+id="userForm".+</form>',
                    '<label.+>',
                    '<button[^>]*type="submit"[^>]*>',
                    'savecartuser',
                ];

                if ($view === 'user') {
                    if ($task === 'editaddresscart') {
                        $this->loadEcc = true;
                    } elseif ($task === 'registercartuser' || $task === 'savecartuser') {
                        $this->loadEccCheck = true;
                    }
                }
            } elseif ($view === 'user' && ($layout === 'edit' || $layout === 'default' || $task === 'saveUser' || $task === 'register')) {
                $this->extensionInfo = [
                    'com_virtuemart',
                    '<form[^>]+name="userForm".+</form>',
                    '<label.+>',
                    '<button[^>]*type="submit"[^>]*>',
                    'saveUser',
                ];

                if (($layout === 'edit' || $layout === 'default' || $task === 'register') && $task !== 'saveUser') {
                    $this->loadEcc = true;
                } elseif ($task === 'saveUser') {
                    $this->loadEccCheck = true;
                }
            }
        } elseif ($option === 'com_iproperty' && $this->getParams('iproperty')) {
            // IProperty - tested with version 3.3
            $this->extensionInfo = [
                'com_iproperty',
                '<form[^>]+name="sendRequest".+</form>',
                '<label id=".+>',
                '<button[^>]*type="submit"[^>]*>',
                'property.sendRequest',
            ];

            if ($view === 'property') {
                if ($task === '') {
                    $this->loadEcc = true;
                } elseif ($task === 'property.sendRequest') {
                    $this->loadEccCheck = true;
                }
            }
        }
    }

    /**
     * Checks whether ECC+ has to be loaded in the user context
     *
     * @return bool
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function loadEccUserContext(): bool
    {
        // Do not execute the call in the administration or if the check is disabled for guests
        return !($this->app->isClient('administrator') || ($this->getParams('onlyGuests') && !$this->user->guest));
    }

    /**
     * The spam checks have not resolved, execute exit actions and redirect the request
     *
     * @param string $messageType
     * @param string $redirectUrl
     *
     * @since 3.3.0.0-FREE
     */
    private function perfomChecksFailed(string $messageType = 'checkFailed', string $redirectUrl = ''): void
    {
        if ($redirectUrl === '') {
            $redirectUrl = $this->redirectUrl;
        }

        // Set error session variable for the message output
        Helper::setSession('errorOutput', $messageType);
        Helper::redirect($redirectUrl);
    }

    /**
     * Loads error notice if needed only once per process
     *
     * @param string $option
     * @param bool   $custom
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function raiseErrorWarning(string $option, bool $custom = false): void
    {
        if (empty($this->warningShown)) {
            // Load error session variable for the message output
            $errorOutput = Helper::getSession('errorOutput');

            if (!empty($errorOutput)) {
                if ($errorOutput === 'checkFailed') {
                    // No message output needed - message is raised by components
                    if (!(($option === 'com_phocaguestbook' && Helper::getSession('phocaguestbook') === 0) || ($option === 'com_easybookreloaded' && Helper::getSession('easybookreloaded') === 0))) {
                        $this->app->enqueueMessage(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_YOUHAVENOTRESOLVEDOURSPAMCHECK'), 'error');
                    }
                } elseif ($errorOutput === 'checkFailedCustom' && $custom === true) {
                    // Only raise general error message if the custom call is used
                    $this->app->enqueueMessage(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_YOUHAVENOTRESOLVEDOURSPAMCHECK'), 'error');
                } elseif ($errorOutput === 'loginAttempts') {
                    $this->app->enqueueMessage(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_TOOMANYFAILEDLOGINATTEMPTS'), 'error');
                }

                Helper::clearSession('errorOutput');
                $this->warningShown = true;
            }
        }
    }

    /**
     * Performs the antispam checks
     *
     * @return bool
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function performChecks(): bool
    {
        $request = $this->request->getArray($_REQUEST);

        // Calc check
        if ($this->getParams('typeCalc')) {
            $spamcheckResult = base64_decode(Helper::getSession('spamcheckresult'));
            $spamcheck = $request[Helper::getSession('spamcheck')];

            Helper::clearSession('spamcheck');
            Helper::clearSession('spamcheckresult');

            if (!is_numeric($spamcheckResult) || $spamcheckResult !== $spamcheck) {
                return false; // Wrong result - failed
            }
        }

        // Hidden field
        if ($this->getParams('typeHidden')) {
            $hiddenField = $request[Helper::getSession('hiddenField')];
            Helper::clearSession('hiddenField');

            if (!empty($hiddenField)) {
                return false; // Hidden field was filled out - failed
            }
        }

        // Time lock
        if ($this->getParams('typeTime')) {
            $time = Helper::getSession('time');
            Helper::clearSession('time');

            if (time() - $this->getParams('typeTimeSec') <= $time) {
                return false; // Submitted too fast - failed
            }
        }

        // Self-defined Question
        if ($this->getParams('question')) {
            $question = $this->getSelfDefinedQuestion();

            if (!empty($question)) {
                $answer = strtolower($request[Helper::getSession('question')]);
                Helper::clearSession('question');

                if ($answer !== strtolower($question['answer'])) {
                    return false; // Question wasn't answered - failed
                }
            }
        }

        // StopForumSpam - Check the IP Address
        // Further information: http://www.stopForumSpam.com
        if ($this->getParams('stopForumSpam')) {
            $url = 'http://www.stopForumSpam.com/api?ip=' . Helper::getSession('ipAddress');

            // Function test - Comment out to test - Important: Enter a active Spam-IP
            // $ip = '88.180.52.46';
            // $url = 'http://www.stopForumSpam.com/api?ip='.$ip;

            $response = false;
            $isSpam = '';

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
            }

            if ($response) {
                preg_match('#<appears>(.*)</appears>#', $response, $out);
                $isSpam = $out[1];
            } else {
                $response = @fopen($url, 'r');

                if ($response) {
                    while (!feof($response)) {
                        $line = fgets($response, 1024);

                        if (preg_match('#<appears>(.*)</appears>#', $line, $out)) {
                            $isSpam = $out[1];
                            break;
                        }
                    }

                    fclose($response);
                }
            }

            if ((string)$isSpam === 'yes' && (bool)$response === true) {
                return false; // Spam-IP - failed
            }
        }

        // Honeypot Project
        // Further information: http://www.projecthoneypot.org/home.php
        // BL ACCESS KEY - http://www.projecthoneypot.org/httpbl_configure.php
        if ($this->getParams('honeyPot')) {
            require_once __DIR__ . '/src/libraries/services/honeypot.php';
            $httpBlKey = $this->getParams('honeyPotKey');

            if ($httpBlKey) {
                $httpBl = new HttpBl($httpBlKey);
                $result = $httpBl->query(Helper::getSession('ipAddress'));

                // Function test - Comment out to test - Important: Enter an active Spam-IP
                // $ip = '117.21.224.251';
                // $result = $http_bl->query($ip);

                if ($result === 2) {
                    return false;
                }
            }
        }

        // Akismet
        // Further information: http://akismet.com/
        if ($this->getParams('akismet')) {
            require_once __DIR__ . '/src/libraries/services/akismet.php';
            $akismetKey = $this->getParams('akismetKey');

            if ($akismetKey) {
                $akismetUrl = Uri::getInstance()->toString();

                $name = '';
                $email = '';
                $url = '';
                $comment = '';

                if ($request['option'] === 'com_contact') {
                    $name = $request['jform']['contact_name'];
                    $email = $request['jform']['contact_email'];
                    $comment = $request['jform']['contact_message'];
                } elseif ($request['option'] === 'com_mailto') {
                    $name = $request['sender'];
                    $email = $request['mailto'];
                    $comment = $request['subject'];
                } elseif ($request['option'] === 'com_users') {
                    $name = $request['jform']['name'];
                    $email = $request['jform']['email1'];

                    if (isset($request['jform']['email'])) {
                        $email = $request['jform']['email'];
                    }
                } elseif ($request['option'] === 'com_comprofiler') {
                    $name = $request['name'];
                    $email = $request['email'];

                    if (isset($request['checkusername'])) {
                        $name = $request['checkusername'];
                    }

                    if (isset($request['checkemail'])) {
                        $email = $request['checkemail'];
                    }
                } elseif ($request['option'] === 'com_easybookreloaded') {
                    $name = $request['gbname'];
                    $email = $request['gbmail'];
                    $comment = $request['gbtext'];

                    if (isset($request['gbpage'])) {
                        $url = $request['gbpage'];
                    }
                } elseif ($request['option'] === 'com_phocaguestbook') {
                    $name = $request['pgusername'];
                    $email = $request['email'];
                    $comment = $request['pgbcontent'];
                } elseif ($request['option'] === 'com_dfcontact') {
                    $name = $request['name'];
                    $email = $request['email'];
                    $comment = $request['message'];
                } elseif ($request['option'] === 'com_flexicontact' || $request['option'] === 'com_flexicontactplus') {
                    $name = $request['from_name'];
                    $email = $request['from_email'];
                    $comment = $request['area_data'];
                } elseif ($request['option'] === 'com_alfcontact') {
                    $name = $request['name'];
                    $email = $request['email'];
                    $comment = $request['message'];
                } elseif ($request['option'] === 'com_community') {
                    $name = $request['usernamepass'];
                    $email = $request['emailpass'];
                } elseif ($request['option'] === 'com_virtuemart') {
                    $name = $request['name'];
                    $email = $request['email'];
                    $comment = $request['comment'];
                } elseif ($request['option'] === 'com_aicontactsafe') {
                    $name = $request['aics_name'];
                    $email = $request['aics_email'];
                    $comment = $request['aics_message'];
                }

                $akismet = new Akismet($akismetUrl, $akismetKey);
                $akismet->setCommentAuthor($name);
                $akismet->setCommentAuthorEmail($email);
                $akismet->setCommentAuthorURL($url);
                $akismet->setCommentContent($comment);

                if ($akismet->isCommentSpam()) {
                    return false;
                }
            }
        }

        // ReCaptcha - Further information: https://www.google.com/recaptcha
        if ($this->getParams('recaptcha')) {
            if ($this->getParams('recaptchaPublicKey') && $this->getParams('recaptchaPrivateKey')) {
                require_once __DIR__ . '/src/libraries/services/recaptchalib.php';
                $privateKey = $this->getParams('recaptchaPrivateKey');

                $reCaptcha = new ReCaptcha($privateKey);
                $response = $reCaptcha->verifyResponse(Helper::getSession('ipAddress'), $request['g-recaptcha-response']);

                if ($response->success === false) {
                    return false;
                }
            }
        }

        // hCaptcha - Further information: https://www.hcaptcha.com/
        if ($this->hCaptcha() && !$this->hCaptcha->validateInput()) {
            return false;
        }

        // Botscout - Check the IP Address
        // Further information: http://botscout.com/
        if ($this->getParams('botScout') && $this->getParams('botScoutKey')) {
            $url = 'http://botscout.com/test/?ip=' . Helper::getSession('ipAddress') . '&key=' . $this->getParams('botScoutKey');

            // Function test - Comment out to test - Important: Enter a active Spam-IP
            // $ip = '87.103.128.199';
            // $url = 'http://botscout.com/test/?ip='.$ip.'&key='.$this->getParams('botScoutKey');

            $response = false;
            $isSpam = '';

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
            }

            if ($response) {
                $isSpam = substr($response, 0, 1);
            } else {
                $response = @fopen($url, 'r');

                if ($response) {
                    while (!feof($response)) {
                        $line = fgets($response, 1024);
                        $isSpam = substr($line, 0, 1);
                    }

                    fclose($response);
                }
            }

            if ((string)$isSpam === 'Y' && (bool)$response === true) {
                return false; // Spam-IP - failed
            }
        }

        // No spam detected!
        Helper::clearSession('ipAddress');
        Helper::clearSession('savedData');
        $this->loadEccCheck = false;

        return true;
    }

    /**
     * Gets the self defined question for the loaded language
     *
     * @return array
     * @since 3.2.0-FREE
     */
    private function getSelfDefinedQuestion(): array
    {
        $questions = $this->getParams('questions');

        if (empty($questions)) {
            return [];
        }

        $languageTag = $this->app->getLanguage()->getTag();

        foreach ($questions as $question) {
            if ($question->languageTag === $languageTag) {
                return (array)$question;
            }
        }

        return [];
    }

    /**
     * Loads the hCaptcha class if the option is enabled and keys are provided
     *
     * @return bool
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    private function hCaptcha(): bool
    {
        if (!$this->getParams('hcaptcha')) {
            return false;
        }

        if (!$this->getParams('hcaptchaSiteKey') || !$this->getParams('hcaptchaSecret')) {
            return false;
        }

        if (empty($this->hCaptcha)) {
            require_once __DIR__ . '/src/libraries/services/hCaptcha.php';
            $this->hCaptcha = new hCaptcha($this->getParams('hcaptchaSiteKey'), $this->getParams('hcaptchaSecret'));
        }

        return true;
    }

    /**
     * Calls checks for supported extensions
     *
     * @param string $option
     * @param string $task
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    private function callChecks(string $option, string $task): void
    {
        $checkFailed = false;
        $view = $this->request->get('view', '', 'WORD');

        if ($option === 'com_users') {
            if ($task === 'reset.request' || $task === 'remind.remind') {
                if (!$this->performChecks()) {
                    $checkFailed = true;
                }
            } elseif ($task === 'user.login') {
                if ($this->performChecks()) {
                    Helper::clearSession('failedLoginAttempts');
                } else {
                    $checkFailed = true;
                }
            }
        } elseif ($option === 'com_mailto' && $task === 'send') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_easybookreloaded' && $task === 'save') {
            if (!$this->performChecks()) {
                Helper::setSession('easybookreloaded', 1);
                $checkFailed = true;
            }
        } elseif ($option === 'com_phocaguestbook' && $task === 'phocaguestbook.submit') {
            if (!$this->performChecks()) {
                Helper::setSession('phocaguestbook', 1);
                $checkFailed = true;
            }
        } elseif ($option === 'com_comprofiler' && ($task === 'sendNewPass' || $view === 'sendnewpass' || $view === 'saveregisters' || $task === 'saveregisters')) {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_dfcontact' && !empty($_REQUEST["submit"])) {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_foxcontact' && $task === 'form.send') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif (($option === 'com_flexicontact' || $option === 'com_flexicontactplus') && $task === 'send') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_kunena' && $task === 'post') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_alfcontact' && $task === 'sendemail') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_community' && $task === 'register_save') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_virtuemart' && ($task === 'mailAskquestion' || $task === 'registercheckoutuser' || $task === 'savecheckoutuser' || $task === 'registercartuser' || $task === 'savecartuser' || $task === 'saveUser')) {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_aicontactsafe') {
            $sTask = $this->request->get('sTask', '', 'STRING');

            if (($sTask === 'message') && !$this->performChecks()) {
                $checkFailed = true;
            }
        } elseif ($option === 'com_iproperty' && $task === 'property.sendRequest') {
            if (!$this->performChecks()) {
                $checkFailed = true;
            }
        }

        if ($checkFailed === true) {
            $this->perfomChecksFailed();
        }
    }

    /**
     * Creates the head information for the reCaptcha implementation
     *
     * @param array $head
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function getRecaptchaHead(array &$head): void
    {
        if ($this->getParams('recaptchaPublicKey') && $this->getParams('recaptchaPrivateKey')) {
            $head[] = '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' . substr(Factory::getLanguage()->getTag(), 0, 2) . '" defer async></script>';
            $head[] = '<script type="text/javascript">var onloadCallback = function() {grecaptcha.render("recaptcha", {"sitekey" : "' . $this->getParams('recaptchaPublicKey') . '", "theme" : "' . (string)$this->getParams('recaptchaTheme') . '"});};</script>';
            $head[] = '<style>.ecc-recaptcha{margin-top: 5px;}</style>';
        }
    }

    /**
     * Detects whether the plugin routine has to be loaded and call the checks
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function onAfterRender(): void
    {
        // Custom call check - call it here because we need access to the output
        if (!$this->loadEcc && $this->getParams('customCall')) {
            $this->customCall();
        }

        if (!$this->loadEcc) {
            return;
        }

        $option = $this->request->get('option', '', 'WORD');

        // Read in content of the output
        $body = $this->app->getBody();

        // Get form of extension
        preg_match('@' . $this->extensionInfo[1] . '@isU', $body, $matchExtension);

        // Form was not found, the template probably uses overrides, try it with the detection of the task or set error message for debug mode
        if (empty($matchExtension)) {
            // Try to find the form by the task if provided
            if (!empty($this->extensionInfo[4])) {
                // Get all forms on the loaded page and find the correct form by the task value
                preg_match_all('@<form[^>]*>.*</form>@isU', $body, $matchExtensionForms);

                if (!empty($matchExtensionForms)) {
                    foreach ($matchExtensionForms[0] as $matchExtensionForm) {
                        if (preg_match('@<form[^>]*>.*value=["|\']' . $this->extensionInfo[4] . '["|\'].*</form>@isU', $matchExtensionForm, $matchExtension)) {
                            break;
                        }
                    }
                }
            }

            if (empty($matchExtension)) {
                $this->app->enqueueMessage(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_WARNING_FORMNOTFOUND'), 'error');
            }
        }

        // Fill in form input values if the check failed previously (_warning_shown is set)
        if (!empty($this->warningShown) && $this->getParams('autofillValues')) {
            $this->fillForm($body, $matchExtension);
        }

        // Hidden field
        if (!empty($matchExtension) && $this->getParams('typeHidden')) {
            $patternSearchString = '@' . $this->extensionInfo[2] . '@isU';
            preg_match_all($patternSearchString, $matchExtension[0], $matches);

            if (empty($matches[0])) {
                $this->app->enqueueMessage(Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_WARNING_NOHIDDENFIELD'), 'error');
            } else {
                $count = random_int(0, count($matches[0]) - 1);
                $searchStringHidden = $matches[0][$count];

                // Generate random variable
                $hiddenField = Helper::randomString();
                $hiddenFieldLabel = Helper::randomString();

                Helper::setSession('hiddenField', $hiddenField);
                Helper::setSession('hiddenFieldLabel', $hiddenFieldLabel);

                // Line width for obfuscation
                $addString = '<label class="' . Helper::getSession('hiddenClass') . '" for="' . $hiddenFieldLabel . '"></label><input type="text" id="' . $hiddenFieldLabel . '" name="' . $hiddenField . '" size="30" class="inputbox ' . Helper::getSession('hiddenClass') . '" />';

                // Yootheme Fix - Put the hidden field in an own div container to avoid displacement of other fields
                if (preg_match('@<div[^>]*>\s*' . preg_quote($searchStringHidden, '@') . '@isU', $matchExtension[0], $matchesDiv)) {
                    $searchStringHidden = $matchesDiv[0];
                }

                if (isset($searchStringHidden)) {
                    $body = str_replace($searchStringHidden, $addString . $searchStringHidden, $body);
                }
            }
        }

        // Calc check
        if (!empty($matchExtension) && ($this->getParams('typeCalc') || $this->getParams('recaptcha') || $this->getParams('hcaptcha') || $this->getParams('question'))) {
            // Without overrides
            $patternOutput = '@' . $this->extensionInfo[3] . '@isU';

            if (preg_match($patternOutput, $matchExtension[0], $matches)) {
                $searchStringOutput = $matches[0];
            } else {
                // Alternative search string from settings
                $stringAlternative = $this->getParams('stringAlternative');

                if (!empty($stringAlternative)) {
                    $pattern = '@' . $stringAlternative . '@isU';

                    if (preg_match($pattern, $matchExtension[0], $matches)) {
                        $searchStringOutput = $matches[0];
                    }
                }

                // With overrides
                if (!isset($searchStringOutput)) {
                    // Artisteer Template
                    if (preg_match('@<span class=".*-button-wrapper">@isU', $matchExtension[0], $matches)) {
                        $searchStringOutput = $matches[0];
                    }

                    // Rockettheme Template
                    if (preg_match('@<div class="readon">@isU', $matchExtension[0], $matches)) {
                        $searchStringOutput = $matches[0];
                    }

                    // String still not found - take the submit attribute
                    if (!isset($searchStringOutput)) {
                        if (preg_match('@<[^>]*type="submit".*>@isU', $matchExtension[0], $matches)) {
                            $searchStringOutput = $matches[0];
                        }
                    }
                }
            }

            $addString = '<!-- EasyCalcCheck Plus - Kubik-Rubik Joomla! Extensions --><div id="easycalccheckplus">';

            if ($this->getParams('typeCalc')) {
                Helper::setSession('spamcheck', Helper::randomString());

                // Determine operator
                $operator = 1;

                if ((int)$this->getParams('operator') === 2) {
                    $operator = random_int(1, 2);
                } elseif ((int)$this->getParams('operator') === 1) {
                    $operator = 2;
                }

                $operand = (int)$this->getParams('operand');

                // Determine max. operand
                $maxValue = $this->getParams('maxValue', 20);

                $spamCheck1 = random_int(1, $maxValue);
                $spamCheck2 = random_int(1, $maxValue);
                $spamCheck3 = random_int(0, $maxValue);

                if ($operator === 2 && !(bool)$this->getParams('negative')) {
                    $spamCheck1 = random_int($maxValue / 2, $maxValue);
                    $spamCheck2 = random_int(1, $maxValue / 2);

                    if ($operand === 3) {
                        $spamCheck3 = random_int(0, $spamCheck1 - $spamCheck2);
                    }
                }

                if ($operator === 1) // Addition
                {
                    if ($operand === 2) {
                        Helper::setSession('spamcheckresult', base64_encode($spamCheck1 + $spamCheck2));
                    } elseif ($operand === 3) {
                        Helper::setSession('spamcheckresult', base64_encode($spamCheck1 + $spamCheck2 + $spamCheck3));
                    }
                } elseif ($operator === 2) // Subtraction
                {
                    if ($operand === 2) {
                        Helper::setSession('spamcheckresult', base64_encode($spamCheck1 - $spamCheck2));
                    } elseif ($operand === 3) {
                        Helper::setSession('spamcheckresult', base64_encode($spamCheck1 - $spamCheck2 - $spamCheck3));
                    }
                }

                $addString .= '<div><label for="' . Helper::getSession('spamcheck') . '">' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_SPAMCHECK');

                if ($operator === 1) {
                    if ($this->getParams('convertToString')) {
                        if ($operand === 2) {
                            $addString .= Helper::convertToString($spamCheck1) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . Helper::convertToString($spamCheck2) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        } elseif ($operand === 3) {
                            $addString .= Helper::convertToString($spamCheck1) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . Helper::convertToString($spamCheck2) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . Helper::convertToString($spamCheck3) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        }
                    } else {
                        if ($operand === 2) {
                            $addString .= $spamCheck1 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . $spamCheck2 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        } elseif ($operand === 3) {
                            $addString .= $spamCheck1 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . $spamCheck2 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_PLUS') . ' ' . $spamCheck3 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        }
                    }
                } elseif ($operator === 2) {
                    if ($this->getParams('convertToString')) {
                        if ($operand === 2) {
                            $addString .= Helper::convertToString($spamCheck1) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . Helper::convertToString($spamCheck2) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        } elseif ($operand === 3) {
                            $addString .= Helper::convertToString($spamCheck1) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . Helper::convertToString($spamCheck2) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . Helper::convertToString($spamCheck3) . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        }
                    } else {
                        if ($operand === 2) {
                            $addString .= $spamCheck1 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . $spamCheck2 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        } elseif ($operand === 3) {
                            $addString .= $spamCheck1 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . $spamCheck2 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_MINUS') . ' ' . $spamCheck3 . ' ' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_EQUALS') . ' ';
                        }
                    }
                }

                $addString .= '</label>';
                $addString .= '<input type="text" name="' . Helper::getSession('spamcheck') . '" id="' . Helper::getSession('spamcheck') . '" size="3" class="inputbox ' . Helper::randomString() . ' validate-numeric required" value="" required="required" />';
                $addString .= '</div>';

                // Show warnings
                if ($this->getParams('warnReference') && !$this->getParams('autofillValues')) {
                    $addString .= '<p><img src="' . Uri::root() . 'plugins/system/easycalccheckplus/src/libraries/warning.png" alt="' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_WARNING') . '" /> ';
                    $addString .= '<strong>' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_WARNING') . '</strong><br /><small>' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_WARNING_DESC') . '</small>';

                    if ($this->getParams('convertToString')) {
                        $addString .= '<br /><small>' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_CONVERTWARNING') . '</small><br />';
                    }

                    $addString .= '</p>';
                } elseif ($this->getParams('convertToString')) {
                    $addString .= '<p><small>' . Text::_('PLG_SYSTEM_EASYCALCCHECKPLUS_CONVERTWARNING') . '</small></p>';
                }
            }

            // Self-Defined Question
            if ($this->getParams('question')) {
                $question = $this->getSelfDefinedQuestion();

                if (!empty($question)) {
                    Helper::setSession('question', Helper::randomString());
                    $size = strlen($question['answer']) + random_int(0, 2);

                    $addString .= '<div><label for="' . Helper::getSession('question') . '">' . $question['question'] . '</label><input type="text" name="' . Helper::getSession('question') . '" id="' . Helper::getSession('question') . '" size="' . $size . '" class="inputbox ' . Helper::randomString() . ' required" value="" /></div>';
                }
            }

            // ReCaptcha
            if ($this->getParams('recaptcha') && $this->getParams('recaptchaPublicKey') && $this->getParams('recaptchaPrivateKey')) {
                $addString .= '<div class="ecc-recaptcha"><div class="g-recaptcha" id="recaptcha"></div></div>';
            }

            // hCaptcha
            if ($this->hCaptcha()) {
                $addString .= '<div class="ecc-hcaptcha">' . $this->hCaptcha->getHtmlContent((string)$this->getParams('hCaptchaTheme')) . '</div>';
            }

            $addString .= '</div>';

            if (isset($searchStringOutput)) {
                $replaceString = $addString;

                if (!$this->customCall) {
                    $replaceString = $addString . $searchStringOutput;
                }

                $body = str_replace($searchStringOutput, $replaceString, $body);
            }
        }

        // Encode fields - since 2.5-8 in all forms where ECC+ is loaded
        if (!$this->debugPlugin && $this->getParams('encode')) {
            $extensionInfoException = $this->extensionInfo[5] ?? '';
            Helper::encodeFields($body, $matchExtension[0], $extensionInfoException);
        }

        // Set body content after all modifications have been applied
        $this->app->setBody($body);

        // Get IP address
        Helper::setSession('ipAddress', Helper::getIpAddress());

        // Set session variable for error output - Phoca Guestbook / Easybook Reloaded
        if ($option === 'com_phocaguestbook') {
            Helper::setSession('phocaguestbook', 0);
        } elseif ($option === 'com_easybookreloaded') {
            Helper::setSession('easybookreloaded', 0);
        }

        // Set redirect url
        Helper::setSession('redirectUrl', Uri::getInstance()->toString());
        Helper::setSession('eccLoaded', true);

        // Time Lock
        if ($this->getParams('typeTime')) {
            Helper::setSession('time', time());
        }
    }

    /**
     * Prepares the custom call for the correct output
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    private function customCall(): void
    {
        // Read in content of the output
        $body = $this->app->getBody();

        if (preg_match("@(<form[^>]*>)(.*)(" . self::CUSTOM_CALL_SYNTAX . ")(.*</form>)@Us", $body, $matches)) {
            // Workaround to get the correct form if several form attributes are provided on the loaded page
            if (strripos($matches[2], '<form') !== false) {
                $matches[0] = substr($matches[2], strripos($matches[2], '<form')) . $matches[3] . $matches[4];

                // Set a new matches array with the correct form
                preg_match("@(<form[^>]*>)(.*)(" . self::CUSTOM_CALL_SYNTAX . ")(.*)(</form>)@Us", $matches[0], $matches);
            }

            if (!empty($matches)) {
                // Remove the syntax for signed-in users who should not see the spam check
                if (!$this->loadEccUserContext()) {
                    $body = str_replace(self::CUSTOM_CALL_SYNTAX, '', $body);
                    $this->app->setBody($body);

                    return;
                }

                // Custom call string was found, set needed class attribute
                $this->customCall = true;

                // Clean the cache of the component first
                Helper::cleanCache();

                // The request does not have to be validated, so get all information for the output of the checks
                $customCallForm = $matches[0];
                $customCallFormContent = $matches[2] . $matches[4];

                // Do some general checks to get needed information from the form of the unknown extension
                // Hidden field - check whether labels are used if not take the input tags
                $customCallHiddenRegex = '<input.+>';

                if (strripos($customCallFormContent, '<label') !== false) {
                    $customCallHiddenRegex = '<label.+>';
                }

                // Get task value of the form
                $customCallFormTask = '';

                if (strripos($customCallFormContent, 'name="task"') !== false) {
                    preg_match('@<input[^>]+name="task".+>@sU', $customCallFormContent, $matchTask);

                    if (preg_match('@value="(.+)"@', $matchTask[0], $matchValue)) {
                        $customCallFormTask = $matchValue[1];
                    }
                }

                // Set the extension info array for the further execution with the collected information
                // Array -> (name, form, regex for hidden field, regex for output, task, request exception for encode option);
                $this->loadEcc = true;
                $this->extensionInfo = [
                    $this->request->get('option', '', 'WORD'),
                    preg_quote($customCallForm, '@'),
                    $customCallHiddenRegex,
                    '{easycalccheckplus}',
                    $customCallFormTask,
                ];

                // Set the needed CSS instructions - since we are already in the trigger onAfterRender, we have to manipulate the output manually
                $head = [];
                $head[] = '<style>#easycalccheckplus {margin: 8px 0 !important; padding: 2px !important;}</style>';

                if ($this->getParams('typeHidden')) {
                    Helper::setSession('hiddenClass', Helper::randomString());
                    $head[] = '<style>.' . Helper::getSession('hiddenClass') . ' {display: none !important;}</style>';
                }

                if ($this->getParams('recaptcha')) {
                    $this->getRecaptchaHead($head);
                }

                $head = implode("\n", $head) . "\n";

                // Set body after the modifications have been applied
                $body = str_replace('</head>', $head . '</head>', $body);
                $this->app->setBody($body);

                // Set the custom call session variable - Get all possible request variable of the loaded form
                preg_match_all('@name=["|\'](.*)["|\']@Us', $matches[0], $matchesRequestVariables);
                $keysExclude = ['option', 'view', 'layout', 'id', 'Itemid', 'task', 'controller', 'func'];
                $customCallRequestVariables = array_diff($matchesRequestVariables[1], $keysExclude);

                // Remove hidden input fields from the custom call session variable
                foreach ($customCallRequestVariables as $customCallRequestVariableKey => $customCallRequestVariable) {
                    if (preg_match('@<input[^>]*name=["|\']' . preg_quote($customCallRequestVariable, '@') . '["|\'][^>]*>@isU', $matches[0], $customCallFieldNameMatch)) {
                        if (stripos($customCallFieldNameMatch[0], 'type="hidden"') !== false || stripos($customCallFieldNameMatch[0], "type='hidden'") !== false) {
                            unset($customCallRequestVariables[$customCallRequestVariableKey]);
                        }
                    }
                }

                Helper::setSession('checkCustomCall', $customCallRequestVariables);
            }
        }
    }

    /**
     * Fills the form with the entered data from the user - autofill function
     *
     * @param string $body
     * @param array  $matchExtensionMain
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    private function fillForm(string &$body, array &$matchExtensionMain): void
    {
        $autofill = Helper::getSession('savedData');

        if (!empty($autofill)) {
            // Get form of extension
            $patternForm = '@' . $this->extensionInfo[1] . '@isU';
            preg_match($patternForm, $body, $matchExtension);

            $patternInput = '@<input[^>].*/?>@isU';
            preg_match_all($patternInput, $matchExtension[0], $matchesInput);

            foreach ($matchesInput[0] as $inputValue) {
                foreach ($autofill as $key => $autofillValue) {
                    if ($autofillValue !== '') {
                        $value = '@name=("|\')' . preg_quote($key, '@') . '("|\')@isU';

                        if (preg_match($value, $inputValue)) {
                            $value = '@value=("|\').*("|\')@isU';

                            if (preg_match($value, $inputValue, $match)) {
                                $patternValue = '/' . preg_quote($match[0], '/') . '/isU';
                                $inputValueReplaced = preg_replace($patternValue, 'value="' . $autofillValue . '"', $inputValue);

                                // Set the value to the body and the extension form for further modifications
                                $body = str_replace($inputValue, $inputValueReplaced, $body);
                                $matchExtensionMain[0] = str_replace($inputValue, $inputValueReplaced, $matchExtensionMain[0]);
                                unset($autofill[$key]);
                                break;
                            }
                        }
                    }
                }
            }

            $patternTextarea = '@<textarea[^>].*>(.*</textarea>)@isU';
            preg_match_all($patternTextarea, $matchExtension[0], $matchesTextarea);

            $count = 0;

            foreach ($matchesTextarea[0] as $textareaValue) {
                foreach ($autofill as $key => $autofillValue) {
                    $value = '@name=("|\')' . preg_quote($key, '@') . '("|\')@';

                    if (preg_match($value, $textareaValue)) {
                        $patternValue = '@' . preg_quote($matchesTextarea[1][$count], '@') . '@isU';
                        $textareaValueReplaced = preg_replace($patternValue, $autofillValue . '</textarea>', $textareaValue);

                        // Set the value to the body and the extension form for further modifications
                        $body = str_replace($textareaValue, $textareaValueReplaced, $body);
                        $matchExtensionMain[0] = str_replace($textareaValue, $textareaValueReplaced, $matchExtensionMain[0]);
                        unset($autofill[$key]);
                        break;
                    }
                }

                $count++;
            }

            Helper::clearSession('savedData');
        }
    }

    /**
     * Detects whether the plugin routine has to be loaded and call the checks
     *
     * Do not use type declarations for function arguments here to avoid
     * TypeError errors if the plugin trigger is called with wrong types.
     *
     * @param object $contact
     * @param array  $post
     *
     * @return bool
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function onValidateContact($contact, $post): bool
    {
        if ($this->loadEccCheck === true) {
            $option = $this->request->get('option', '', 'WORD');

            if ($option === 'com_contact' && $this->getParams('contact')) {
                if (!$this->performChecks()) {
                    $this->perfomChecksFailed();
                }
            }
        }

        return true;
    }

    /**
     * Detect whether the plugin routine has to be loaded and call the checks
     *
     * Do not use type declarations for function arguments here to avoid
     * TypeError errors if the plugin trigger is called with wrong types.
     *
     * @param array $user
     * @param bool  $isnew
     * @param array $new
     *
     * @throws Exception
     * @since   3.2.0-FREE
     * @version 3.3.0.0-FREE
     */
    public function onUserBeforeSave($user, $isnew, $new): void
    {
        if (($this->loadEccCheck === true) && !empty($isnew)) {
            $option = $this->request->get('option', '', 'WORD');

            if (($this->getParams('userRegistration') && $option === 'com_users') || ($this->getParams('communitybuilder') && $option === 'com_comprofiler')) {
                if (!$this->performChecks()) {
                    $this->perfomChecksFailed();
                }
            }
        }
    }

    /**
     * Detects whether the plugin routine has to be loaded and call the checks
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public function onUserLoginFailure(): void
    {
        $failedLoginAttempts = Helper::getSession('failedLoginAttempts');
        Helper::setSession('failedLoginAttempts', $failedLoginAttempts + 1);
    }

    /**
     * Successful login, clear sessions variable
     *
     * @since   3.2.0-FREE
     * @version 3.2.1-FREE
     */
    public function onUserLogin(): void
    {
        Helper::clearSession('failedLoginAttempts');
    }

    /**
     * The cache plugin should not cache form pages where ECC+ is loaded
     *
     * @return bool
     * @since 3.3.0.0-FREE
     */
    public function onPageCacheIsExcluded(): bool
    {
        $customCallSession = Helper::getSession('checkCustomCall');

        return $this->loadEcc || $this->loadEccCheck || $customCallSession !== null;
    }
}
