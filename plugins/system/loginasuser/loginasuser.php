<?php
/* ======================================================
# Login as User for Joomla! - v3.3.2
# -------------------------------------------------------
# For Joomla! CMS
# Author: Web357 (Yiannis Christodoulou)
# Copyright (©) 2009-2019 Web357. All rights reserved.
# License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
# Website: https:/www.web357.com/
# Demo: https://demo.web357.com/?item=loginasuser
# Support: support@web357.com
# Last modified: 21 Mar 2019, 01:46:39
========================================================= */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemLoginAsUser extends JPlugin
{
	public function onAfterInitialise()
	{
		jimport('joomla.environment.uri' );
		$host = JURI::root();
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option', '', 'STRING');
		
		// CSS - backend
		if ($app->isAdmin() && ($option == 'com_loginasuser' || $option == 'com_users')):
			$document->addStyleSheet($host.'plugins/system/loginasuser/assets/css/loginasuser.css');
		endif;
		
		// get more useful vars
		$app = JFactory::getApplication();

		// check if is frontend
		$is_frontend = ($app->isSite()) ? true : false; 

		// get vars from user
		$loginasclient = JFactory::getApplication()->input->get('loginasclient', '', 'INT');
		$username = JFactory::getApplication()->input->get('lacusr', '', 'RAW');
		$password = JFactory::getApplication()->input->get('lacpas', '', 'RAW');
		
		// login user
		if ($loginasclient && !empty($username) && !empty($password)):
			
			$db = JFactory::getDBO();

			// get user details from db
			$query = $db->getQuery(true)
				->select('id, name, username, password, params, lastvisitDate')
				->from('#__users')
				->where('username=' . $db->quote($username))
				->where('password=' . $db->quote($password));

			$db->setQuery($query);
			$sql_data = $db->loadObject();
	
			// get default site language
			$default_language = JComponentHelper::getParams('com_languages')->get('site','en-GB');

			// get user params
			$user_params = json_decode($sql_data->params);

			// build data object
			$data = new stdClass();
			$data->id = $sql_data->id;
			$data->fullname = $sql_data->name;
			$data->username = $sql_data->username;
			$data->password = $sql_data->password;
			$data->language = (!empty($user_params->language)) ? $user_params->language : $default_language;
			$data->lastvisitDate = $sql_data->lastvisitDate;

			// get lastvisitDate from user
			$lastvisitDate = $data->lastvisitDate;
	
			if ($data):

				// get params
				$this->_plugin = JPluginHelper::getPlugin( 'system', 'loginasuser' );
				$this->_params = new JRegistry( $this->_plugin->params ); 
				$login_system = $this->_params->get('login_system', 'joomla');
				$send_message_to_admin = $this->_params->get('send_message_to_admin', 1);
				$admin_email = $this->_params->get('admin_email');
				$url_redirect = $this->_params->get('url_redirect', 'index.php?option=com_users&view=profile');

				// login as user
				// Default Login - Plugin
				if ($login_system == 'joomla'):
					
					JPluginHelper::importPlugin('user'); // (plugin/user/joomla/)
					$options = array();
					$options['action'] = 'core.login.site';
					$app->triggerEvent('onUserLogin', array((array)$data, $options));
					
				// K2 - Plugin
				elseif ($login_system == 'k2'):
					
					require_once (JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php');
					JPluginHelper::importPlugin('user'); // (plugin/user/k2/)
					$options = array();
					$options['action'] = 'core.login.site';
					$app->triggerEvent('onUserLogin', array((array)$data, $options));
				
				// ExtendedReg - Plugin
				elseif ($login_system == 'ExtendedReg'):

					require_once (JPATH_PLUGINS.'/user/extendedreguser/extendedreguser.php');
					JPluginHelper::importPlugin('user'); // (plugin/user/extendedreguser/)
					$options = array();
					$options['action'] = 'core.login.site';
					$app->triggerEvent('onUserLogin', array((array)$data, $options));
					
				endif;
				
				// insert back the correct last visit date
				$query = 'UPDATE #__users SET lastvisitDate = "'.$lastvisitDate.'" WHERE username='.$db->Quote($username).' AND password=' . $db->Quote($password);
				$db->setQuery($query);
				$db->execute();
				
				// Send a message to Admin, to inform that a user logged in from backend, via 'Login as User' plugin.
				if ($send_message_to_admin):
					$mailer = JFactory::getMailer();
					$config = new JConfig();
					$sitename = $config->sitename;
					$email_from = $config->mailfrom;
					$email_fromname = $config->fromname;
					$sender = array($email_from, $email_fromname);
					$mailer->setSender($sender);
					$recipient = (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)) ? $admin_email : $email_from;
					$mailer->addRecipient($recipient);
					$body   = "This is an automatic informative message from 'Login as User' system joomla! plugin.<br><br><small style=\"color:#666\">There is an option at joomla backend, if you want to disable these notification emails.</small>";
					$mailer->setSubject("An admin has logged in successfully with username: \"$username\" | ".$sitename);
					$mailer->isHTML(true);
					$mailer->Encoding = 'base64';
					$mailer->setBody($body);
					$mailer->Send();
				endif;
				
				// redirect to user profile page
				if ($app->isSite()):
					$url_redirect = (!empty($url_redirect)) ? $url_redirect : 'index.php?option=com_users&view=profile';
					$app->redirect(JRoute::_(JURI::root().$url_redirect));
				elseif ($app->isAdmin()):
					$url_redirect = (!empty($url_redirect)) ? $url_redirect : 'index.php?option=com_loginasuser';
					$app->redirect($url_redirect);
				endif;
			endif;
					
		endif;
	}	
	
}