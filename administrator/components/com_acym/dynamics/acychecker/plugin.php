<?php

use AcyMailing\Libraries\acymPlugin;
use AcyChecker\Classes\ConfigurationClass;
use AcyChecker\Services\ApiService;

class plgAcymAcychecker extends acymPlugin
{
    private function loadAcychecker()
    {
        if (ACYM_CMS === 'joomla') {
            $cteFolder = rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acychecker'.DS;
        } else {
            $cteFolder = WP_PLUGIN_DIR.DS.'acychecker'.DS;
        }
        include_once $cteFolder.'vendor'.DS.'autoload.php';
        include_once $cteFolder.'defines.php';
    }

    public function onBeforeSaveConfigFields(&$formData)
    {
        if (!isset($formData['email_verification'])) return;
        if (!acym_isAcyCheckerInstalled()) return;
        $this->loadAcychecker();

        $cteConfig = new ConfigurationClass();
        $registrationIntegrations = explode(',', $cteConfig->get('registration_integrations'));
        if (empty($formData['email_verification'])) {
            if (in_array('acymailing', $registrationIntegrations)) {
                unset($registrationIntegrations[array_search('acymailing', $registrationIntegrations)]);

                $cteConfig->save(
                    [
                        'registration_integrations' => implode(',', $registrationIntegrations),
                    ]
                );
            }
        } else {
            if (!in_array('acymailing', $registrationIntegrations)) {
                $registrationIntegrations[] = 'acymailing';
            }

            $registrationConditions = [];
            if (!empty($formData['email_verification_non_existing'])) $registrationConditions[] = 'invalid_smtp';
            if (!empty($formData['email_verification_disposable'])) $registrationConditions[] = 'disposable';
            if (!empty($formData['email_verification_free'])) $registrationConditions[] = 'free_domain';
            if (!empty($formData['email_verification_role'])) $registrationConditions[] = 'role_based';
            if (!empty($formData['email_verification_acceptall'])) $registrationConditions[] = 'accept_all';
            if (!empty($formData['email_checkdomain'])) $registrationConditions[] = 'domain_not_exists';

            $cteConfig->save(
                [
                    'registration_integrations' => trim(implode(',', $registrationIntegrations), ','),
                    'registration_conditions' => trim(implode(',', $registrationConditions), ','),
                ]
            );
        }
    }

    public function onAcymBeforeUserCreate(&$user)
    {
        if (!acym_isAcyCheckerInstalled()) return true;

        if ($this->config->get('email_verification') == 0) return true;

        $this->loadAcychecker();

        $cteConfig = new ConfigurationClass();
        $conditions = $cteConfig->get('registration_conditions');

        if (empty($conditions) || $conditions === 'domain_not_exists') return true;

        $apiService = new ApiService();
        $emailOk = $apiService->testEmail($user->email, $conditions);
        if ($emailOk !== true) {
            acym_setVar('acychecker_error', acym_translation('ACYM_INVALID_EMAIL_ADDRESS'));

            return false;
        }

        return true;
    }
}
