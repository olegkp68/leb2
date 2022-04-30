<?php
/**
 * @package		User SpamCheck - check for possible spambots during register and login
 * @author		vi-solutions, Aicha Vack
 * @copyright	Copyright (C) 2010 vi-solutions. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;

require_once(__DIR__ .  '/SpambotCheck/SpambotCheckImpl.php');
require_once(__DIR__ .  '/SpambotCheck/SpambotCheckHelpers.php');

class plgUserSpambotCheck extends JPlugin {

	protected $componentInstalled;

    public function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        // load the translation
        $this->loadLanguage();
        $this->componentInstalled = plgSpambotCheckHelpers::checkComponentInstalled();
    }

    public function onUserBeforeSave($userOld, $isnew) {
	    if ($this->exitOnJoomla4()) {
		    $this->disableSpambotcheckPlugin();
		    return true;
	    }

        // only site and not administrator
        // only a new user
	    $app = \JFactory::getApplication();
        if ( !$app->isClient('site') || !$isnew) {
            return true;
        }

        $input = $app->input;
        $this->params->set('current_action', 'Register');
        $data = $input->post->get('jform', array(), 'array');
        $user = array(
	        "fullname" => isset($data['name']) ? $data['name'] : $input->post->get('string', 'name', ''),
	        "username" => isset($data['username']) ? $data['username'] : $input->post->get('string','username',''),
	        "email" => isset($data['email1']) ? $data['email1'] : $input->post->get('string','email1', '')
        );
        $spamString = "";
        
        if ( !$this->isSpammer($user, $spamString)) {
            // not a spammer
            return true;
        }
        
        //check if users have lately registered with this IP
        if ($this->params->get('isSpamIp', 0) == 1) {
            if ($this->componentInstalled) {
                plgSpambotCheckHelpers::flagUserWithSpamUserIp();
            }
        }
        
        // send email notification to all sys-admins
        $this->sendMailToAdmin($user, $spamString, \JText::_('PLG_USER_SPAMBOTCHECK_EMAIL_SUBJECT_REGISTER_PREVENTION_TXT'));
        
        // redirect us to the old page and display an error notificaton to the user	
        $message = \JText::_('PLG_USER_SPAMBOTCHECK_USER_REGISTRATION_SPAM_TXT');
        JLog::add($message, JLog::ERROR, 'jerror');
        $app->redirect('index.php');
        $app->close();
        
        return false;
    }

    function onUserAfterSave($data, $isNew, $result, $error) {
	    if ($this->exitOnJoomla4()) {
		    $this->disableSpambotcheckPlugin();
		    return true;
	    }

        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');
        // only for new users that were saved successfully in database
        if ($userId && $isNew && $result) {
            if ($this->componentInstalled) {
                // always insert the new user into users_spambot table, even after backend creation
                if (!plgSpambotCheckHelpers::logUserData($userId)) {
                    //No message
                    return false;
                }
                if (\JFactory::getApplication()->isClient('site')) {
                    // check if a user has already registered using the same IP
                    plgSpambotCheckHelpers::checkIpSuspicious($data, $this->params);
                    // check for suspicious email addresses
                    plgSpambotCheckHelpers::checkEmailSuspicious($data);
                }
            }
        }

        return true;
    }

    function onUserAfterDelete($user, $success, $msg) {
	    if ($this->exitOnJoomla4()) {
		    $this->disableSpambotcheckPlugin();
		    return true;
	    }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');
        if ($userId && $success) {
            if ($this->componentInstalled) {
                // get Ip of deleted user
                $userIp = plgSpambotCheckHelpers::getTableFieldValue('#__user_spambotcheck', 'ip', 'user_id', $userId);
                // Delete row in table user_spambotcheck
                $db = \JFactory::getDbo();
                $query = $db->getQuery(true);
                $conditions = array(
                    $db->quoteName('user_id') . ' = ' . $db->quote($userId)
                );

                $query->delete($db->quoteName('#__user_spambotcheck'));
                $query->where($conditions);

                $db->setQuery($query);
                try {
	                $db->execute();
                }
                catch (RuntimeException $e) {

                }

                // clean up user_spambotcheck fields
                plgSpambotCheckHelpers::cleanUserSpambotTable($userIp, $userId);
            }
        }
        
        return true;
    }

    public function onUserLogin($user, $options = array()) {
	    if ($this->exitOnJoomla4()) {
		    $this->disableSpambotcheckPlugin();
		    return true;
	    }

        if (!($this->params->get('spbot_monitor_events', 'RL') == 'RL')) {
            return true;
        }

        // Is user trusted and not to check?
        $userId = plgSpambotCheckHelpers::getTableFieldValue('#__users', 'id', 'email', $user['email']);
        if ($this->componentInstalled && plgSpambotCheckHelpers::getTableFieldValue('#__user_spambotcheck', 'trust', 'user_id', $userId) == 1) {
            return true;
        }
        
        $this->params->set('current_action', 'Login');
        $spamString = "";
        
        // not a spammer ?
        if ( !$this->isSpammer($user, $spamString)) {
            return true;
        }

        // this is a spammer
        if (($spamString != "") && (strpos($spamString, 'E-Mail in Backlist') === false)) {
            // set user to suspicious if not already done
            // get Value of note field
	        if ($this->componentInstalled) {
		        $notevalue = plgSpambotCheckHelpers::getTableFieldValue('#__user_spambotcheck', 'note', 'user_id', $userId);
		        if (strpos($notevalue, '4: User flagged; ') === false) {
			        $note = '4: User flagged; ';
			        // Create an object for the record we are going to update.
			        $object = new stdClass();
			        $object->user_id = $userId;
			        // Add a note
			        $object->note = $notevalue . $note;
			        // Set suspicious state
			        $object->suspicious = 0;
			        // Update their details in the users table using user_id as the primary key.
			        try {
				        \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('user_id'));
			        }
			        catch (RuntimeException $e) {
			        }
		        }
		        // check if users have lately registered with this IP
		        if ($this->params->get('isSpamIp', 0) == 1) {
			        plgSpambotCheckHelpers::flagUserWithSpamUserIp($userId);
		        }
	        }
        }

        // User is already logged in by task done in plgUserJoomla::onUserLogin
        // Enforce a logout operation by resetting fields in session table to a guest user

        $config = JComponentHelper::getParams('com_users');
        $defaultUserGroup = $config->get('new_usertype', 2);

        // create a guest user		 
        $instance = new JUser(); // creates a guest user	
        $instance->set('id', 0);
        $instance->set('name', '');
        $instance->set('username', '');
        $instance->set('groups', array($defaultUserGroup));

        // get the session
        $session = \JFactory::getSession();

        // replace session user with guest user
        $session->set('user', $instance);

        // -> store the guest user to the #__session table using the session id of the session created by the spammer
        // thus replacing the logged in spammer with a guest user
        $table = JTable::getInstance('session'); // Get the session-table object
        $table->load($session->getId());
        $table->guest = $instance->get('guest');
        $table->username = $instance->get('username');
        $table->userid = intval($instance->get('id'));
        $table->set('groups');
        $table->store();

        // send email notification to all sys-admins
        if (($spamString != "") && (strpos($spamString, 'E-Mail in Backlist') === false)) {
            $this->sendMailToAdmin($user, $spamString, \JText::_('PLG_USER_SPAMBOTCHECK_EMAIL_SUBJECT_LOGIN_PREVENTION_TXT'));
        }

        // redirect us to the old page and display an error notificaton to the user
        JLog::add(sprintf(\JText::_('PLG_USER_SPAMBOTCHECK_USER_LOGIN_SPAM_TXT')), JLog::ERROR, 'jerror');
        $app = \JFactory::getApplication();
        $app->redirect(JRoute::_($options['return']));
        $app->close();

        return false;
    }

	public function onPrivacyCollectAdminCapabilities() {
		if ($this->exitOnJoomla4()) {
			$this->disableSpambotcheckPlugin();
			return true;
		}

		$this->loadLanguage();

		return array(
			JText::_('PLG_USER_SPAMBOTCHECK_PRIVACY') => array(
				JText::_('PLG_USER_SPAMBOTCHECK_PRIVACY_INFORMATION'),
			)
		);
	}

	/**
     * Method check if the user specified is a spammer.
     *
     * @param 	array		holds the user data
     * @param 	string		hold the raw string returned by "check_spammers_plain.php"
     *
     * @return boolean True if user is a spammer and False if he isn't.
     */
    private function isSpammer($user, &$spamString) {
	    if ($this->exitOnJoomla4()) {
		    return true;
	    }

        // don't check admins
        if (plgSpambotCheckHelpers::userIsAdmin($user)) {
            return false;
        }

        // check for spammer
        $SpambotCheck = new plgSpambotCheckImpl($this->params, $user['email'], $_SERVER['REMOTE_ADDR'], $user['username']);
        $SpambotCheck->checkSpambots();
        if ($SpambotCheck->sIdentifierTag == false || strlen($SpambotCheck->sIdentifierTag) == 0 || strpos($SpambotCheck->sIdentifierTag, "SPAMBOT_TRUE") === false) {
            // not a spammer
            $spamString = "";
            return false;
        }

        // if we get here we have to deal with a spammer
        $spamString = $SpambotCheck->sIdentifierTag;
        return true;
    }

	private function sendMailToAdmin(&$user, &$spamString, $subjectAddString) {
        if (!$this->params->get('spbot_email_notifications', 1)) {
            // -> NO admin notifications
            return;
        }

        // get Super User Groups
        $superUserGroups = plgSpambotCheckHelpers::getSuperUserGroups();
        if (!(count($superUserGroups) > 0)) {
            // Something went wrong with finding superadmins, don't sent mails to everybody
            return;
        }

	    $db = \JFactory::getDBO();
        // Only send notifications for selected types
        $type = $this->params->get('current_action');
        $notificationtype = $this->params->get('email_notification_type');

        if (($notificationtype == "RL") || ($notificationtype == "R" && $type == "Register") || ($notificationtype == "L" && $type == "Login")) {
            $name = $user['fullname'];
            $username = $user['username'];
            $email = $user['email'];
            $sPostersIP = $_SERVER['REMOTE_ADDR'];

            $app = \JFactory::getApplication();
            $sitename = $app->getCfg('sitename');
            $mailfrom = $app->getCfg('mailfrom');
            $fromname = $app->getCfg('fromname');

            // get all super administrator
            // create where statement for SQL
            $where = "";
            $length = count($superUserGroups);
            if ($length > 0) {
                for ($i = 0; $i < $length; $i++) {
                    $where .= $db->qn('map.group_id') .' = ' . $superUserGroups[$i];
                    if ($i < $length - 1) {
                        $where .= ' OR ';
                    }
                }
            }


            $query = $db->getQuery(true);
            $query->select(array($db->qn('u.name') . ' AS ' . $db->qn('name'), $db->qn('u.email') . 'AS' . $db->qn('email'), $db->qn('u.sendEmail') . 'AS' . $db->qn('sendEmail')))
	            ->from($db->qn('#__users'))
	            ->join('LEFT', $db->qn('#__user_usergroup_map') . ' AS ' . $db->qn('map') . ' ON ('.  $db->qn('map.user_id') . ' =' . $db->qn('u.id') .')' )
	        ->join('LEFT', $db->qn('#__usergroups') . ' AS ' . $db->qn('g') . ' ON ('.  $db->qn('map.group_id') . ' =' . $db->qn('g.id') .')' )
            ->where($where);
            $db->setQuery($query);
            try {
	            $rows = $db->loadObjectList();
            }
            catch (RuntimeException $e) {
            	$test = true;
            	return;
            }

            // Send notification to all administrators
            $subject = sprintf(\JText::_('PLG_USER_SPAMBOTCHECK_ACCOUNT_DETAILS_FOR_TXT'), $name, $sitename) . $subjectAddString;
            $subject = html_entity_decode($subject, ENT_QUOTES);

            foreach ($rows as $row) {
                if ($row->sendEmail) {
                    $message = sprintf(\JText::_('PLG_USER_SPAMBOTCHECK_SEND_EMAIL_TO_ADMIN_TXT'), $row->name, $sitename, $type, $name, $email, $username, $sPostersIP, $spamString);
                    $message = html_entity_decode($message, ENT_QUOTES);
                    $mailer = \JFactory::getMailer();
                    // Clean the email data
                    $subject = JMailHelper::cleanSubject($subject);
                    $message = JMailHelper::cleanBody($message);
                    $mailer->sendMail($mailfrom, $fromname, $row->email, $subject, $message);
                }
            }
        }
        else {
            // No Admin Notification
            return;
        }
    }

	private function exitOnJoomla4() {
		// assure joomla 4 compatibility
		return version_compare((new \JVersion)->getShortVersion(), '3.999', '>');
	}

	private function disableSpambotcheckPlugin() {
		// disable spambotcheck plugin on first call on joomla 4
		$db = \JFactory::getDbo();
		$conditions = array(
			$db->qn('type') . ' = ' . $db->q('plugin'),
			$db->qn('element') . ' = ' . $db->quote('spambotcheck'),
			$db->qn('folder') . ' = ' . $db->quote('user')
		);
		$fields = array($db->qn('enabled') . ' = 0');

		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		try {
			$db->execute();
		}
		catch (RuntimeException $e) {
			JLog::add('unable to enable Plugin SpambotCheck', JLog::ERROR, 'jerror');
		}
	}
}