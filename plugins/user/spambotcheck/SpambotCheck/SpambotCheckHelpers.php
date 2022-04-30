<?php
/**
 * @package		User SpamCheck - check for possible spambots during register and login
 * @author		vi-solutions, Aicha Vack
 * @copyright	Copyright (C) 2010 vi-solutions. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;

class plgSpambotCheckHelpers {

    public static function cleanEMailWhitelist($email_whitelist) {
        if ($email_whitelist != '') {
            //delete blanks
            $email_whitelist = str_replace(' ', '', $email_whitelist);
            //delete ',' at stringend
            while ($email_whitelist[strlen($email_whitelist) - 1] == ',') {
                $email_whitelist = substr($email_whitelist, 0, strlen($email_whitelist) - 1);
            }
        }

        return $email_whitelist;
    }

    public static function cleanEMailBlacklist($email_blacklist) {
        if ($email_blacklist != '') {
            //delete blanks
            $email_blacklist = str_replace(' ', '', $email_blacklist);
            //delete ',' at stringend
            while ($email_blacklist[strlen($email_blacklist) - 1] == ',') {
                $email_blacklist = substr($email_blacklist, 0, strlen($email_blacklist) - 1);
            }
        }

        return $email_blacklist;
    }

    public static function cleanUsername($sUsername) {
        if ($sUsername != '') {
            $sUsername = addslashes(htmlentities($sUsername));
            $sUsername = urlencode($sUsername);
            $sUsername = str_replace(" ", "%20", $sUsername); // no spaces		
        }

        return $sUsername;
    }

    /**
     * Method to check if cUrl is available on sytem.
     *
     * @return  boolean true/false
     */
    public static function isCUrlAvailable() {
        $extension = 'curl';
        if (extension_loaded($extension)) {
            return true;
        }

        return false;
    }

    public static function isURLOnline($sSiteToCheck) {
        // check, if curl is available
        if (self::isCUrlAvailable()) {
            // check if url is online
            $curl = @curl_init($sSiteToCheck);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            @curl_exec($curl);
            if (curl_errno($curl) != 0) {
	            curl_close($curl);
                return false;
            }
            curl_close($curl);
            return true;
        }

        //curl is not loaded, this won't work
        return false;
    }

    public static function getURL($sURL) {
        if (self::isURLOnline($sURL) == false) {
            $sURLTemp = 'Unable to connect to server';
            return $sURLTemp;
        }
        else {
            if (function_exists('file_get_contents') && ini_get('allow_url_fopen') == true) {
                // Use file_get_contents
                $sURLTemp = @file_get_contents($sURL);
            }
            else {
                // Use cURL (if available)
                if (self::isCUrlAvailable()) {
                    $curl = @curl_init();
                    curl_setopt($curl, CURLOPT_URL, $sURL);
                    curl_setopt($curl, CURLOPT_VERBOSE, 1);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    $sURLTemp = @curl_exec($curl);
                    curl_close($curl);
                }
                else {
                    $sURLTemp = 'Unable to connect to server';
                    return $sURLTemp;
                }
            }

            return $sURLTemp;
        }
        //echo 'DEBUG: $sURLTemp: '.$sURLTemp.'<br/>';
    }

    /**
     * Method to validate passed IP
     *
     * @param string $ip	Ip Adress
     * @return  string ($ip if IP is valid)
     */
    public static function isvalidIP($ip) {
        if ($ip != '') {
            $regex = "'\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b'";
            return preg_match($regex, $ip) ? $ip : '';
        }

        return '';
    }

    /*
     * method to validate user email
     *
     * @param string $value user input from $_POST
     * @return string ($email if input is valid)
     */

    public static function isvalidEmail($email) {
        if ($email != '') {
            $regex = '/^([a-zA-Z0-9_\.\-\+%])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
            return preg_match($regex, $email) ? $email : '';
        }

        return '';
    }

    /**
     * Method to Log spammer to database (if enabled)
     *
     * @return  boolean
     */
    // Usage example:
    // ---------------
    // logSpammerToDB('test@test.com', '12.12.12.12', 'username', 'ProjectHoneyPot', '127.41.11.5', 'ThreatScore=11, DaysSinceLastActivity=41', $plgParams)
    //
	public static function logSpammerToDB($sEmail, $sIP, $sUsername, $sEngine, $sRequest, $sRawReturn, $sParsedReturn, &$plgParams) {
        if (!$plgParams->get('spbot_log_to_db', 0)) {
            // -> save spambots to DB?
            return false;
        }

        // Change empty vars to "NULL"
        if ($sEmail == '') {
            $sEmail = 'NULL';
        }
        if ($sIP == '') {
            $sIP = 'NULL';
        }
        if ($sUsername == '') {
            $sUsername = 'NULL';
        }

        // Trim anything that could screw up SQL
        $sEmail = str_replace(array("0x", ",", "%", "'", "\r\n", "\r", "\n"), "", $sEmail);
        //$sEmail = mysql_real_escape_string($sEmail);

        $sIP = str_replace(array("0x", ",", "%", "'", "\r\n", "\r", "\n"), "", $sIP);
        //$sIP = mysql_real_escape_string($sIP);

        $sUsername = str_replace(array("0x", ",", "%", "'", "\r\n", "\r", "\n"), "", $sUsername);

        // add DB record
        $db =\JFactory::getDBO();
        $sDate = gmdate("Y-m-d H:i:s", time());
        $sAction = $plgParams->get('current_action', '-');
		$query = $db->getQuery(true);
		$columns = array('action', 'email', 'ip', 'username', 'engine', 'request', 'raw_return', 'parsed_return', 'attempt_date');
		$values = array($db->quote($sAction), $db->quote($sEmail), $db->quote($sIP), $db->quote($sUsername), $db->quote($sEngine), $db->quote($sRequest), $db->quote($sRawReturn), $db->quote($sParsedReturn), $db->quote($sDate));
		$query->insert($db->quoteName('#__spambot_attempts'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
        $db->setQuery($query);
        try {
            $db->execute();
        }
        catch (Exception $e) {
            return false;
        }
        return true;
    }

    public static function userIsAdmin($user) {
        if ($userid = \JUserHelper::getUserId($user['username'])) {
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->qn('g.id'))
	            ->from($db->qn('#__usergroups'))
	            ->join('LEFT', $db->qn('#__user_usergroup_map') . ' AS ' . $db->qn('map') . ' ON ' . $db->qn('map.group_id') . ' = ' . $db->qn('g.id'))
	            ->where($db->qn('map.user_id') .'=' . $db->quote($userid));
            //$query = 'SELECT g.id AS group_id FROM `#__usergroups` AS g LEFT JOIN `#__user_usergroup_map` AS map ON map.group_id=g.id WHERE map.user_id=' . $db->quote($userid);
            $db->setQuery($query);
            //A user can be member of more than one user groups
	        try {
		        $ugps = $db->loadObjectList();
	        }
	        catch (RuntimeException $e) {
	        	$test = true;
	        	return false;
	        }
            //check if any of this groups has admin rights
            foreach ($ugps as $ugp) {
                $groupId = $ugp->group_id;
                if (\JAccess::checkGroup($groupId, 'core.admin') == 1) { // user is admin
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Method to get all Super User Groups.
     *
     * @return  array of user group id's
     *
     * @since   1.6
     */
    public static function getSuperUserGroups() {
	    //Store superuser groups here
	    $superUsersGroups = array();

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
        //Get all usergroups from database
        $query->select($db->qn('id'));
        $query->from($db->qn('#__usergroups'));
        $db->setQuery($query);
        try {
	        $usergroups = $db->loadColumn();
        }
        catch (RuntimeException $e) {
	        return ($superUsersGroups);
        }

        foreach ($usergroups as $value) {
            //Check if group has Superuser rights (core.admin)
            $SuperAdmin = \JAccess::checkGroup($value, 'core.admin');
            if ($SuperAdmin == 1) {
                //Store in Array
                $superUsersGroups[] = $value;
            }
        }

        return ($superUsersGroups);
    }

    public static function logUserData($userId) {

        // Create and populate an object.
        $user_spambot = new stdClass;
        $user_spambot->user_id = $userId;
        if (\JFactory::getApplication()->isClient('site')) {
            $user_spambot->ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $user_spambot->ip = "";
            $user_spambot->note = "Backend creation";
            $user_spambot->trust = 1;
        }

        // Insert the object into the user_spambot table.
	    try {
		    return \JFactory::getDbo()->insertObject('#__user_spambotcheck', $user_spambot);
	    }
	    catch (RuntimeException $e) {
        	return false;
	    }
    }

    /**
     * Method to get a list of users with same IP.
     *
     * @param  string	User Ip from SERVER REMOTE ADDRESS
     * @return  mixed false or object of Array's with user data
     *
     * @since   1.6
     */
    public static function getUsersByIp($ip) {
        if ($ip == "") {
            //user was created by admin in backend or user was allready registered when component was installed
            return false;
        }
        // Get a db connection.
        $db = \JFactory::getDbo();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select all records from the user spambottable table with .
        $query
            ->select($db->quoteName(array('a.id', 'a.user_id', 'a.ip', 'a.hits', 'a.note', 'a.trust')))
            ->select($db->quoteName('b.id', 'bid'))
            ->select($db->quoteName('b.registerDate', 'registerDate'))
            ->from($db->quoteName('#__user_spambotcheck', 'a'))
            ->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('b.id') . ')')
            ->where($db->quoteName('ip') . ' = ' . $db->quote($ip))
            ->order($db->quoteName('a.id') . ' asc');

        // Reset the query using our newly populated query object.
	    $db->setQuery($query);
        //Load the results as a list of stdClass objects
	    try {
	    	return $db->loadObjectList();
	    }
	    catch (RuntimeException $e) {
		    return false;
	    }
    }

    /**
     * Method to calculate the difference between to timestamps
     *
     * @param $first_occ	JDate
     * @param $actual_occ	JDate
     *
     * @return  int in seconds
     *
     * @since   1.6
     */
    public static function getDateDiff($first_occ, $actual_occ) {
        //check that we have two dateTime strings
        if (!strtotime($actual_occ)) {
            if (is_numeric($actual_occ)) {
            //we assume we have a unix timestamp and convert it
                $actual_occ = new \JDate($actual_occ);
            } else {
                //we can set the registration date of the new user to now and create a proper Date
                $actual_occ = new \JDate();
            }
        }

        if (!strtotime($first_occ)) {
            if (is_numeric($first_occ)) {
            //we assume we have a unix timestamp and convert it
                $first_occ = new \JDate($first_occ);
            }
        }

        if ((!strtotime($first_occ)) || (!strtotime($actual_occ))) {
            return 0;
        }
        $first_occ = strtotime($first_occ);
        $actual_occ = strtotime($actual_occ);
        $diff = abs($actual_occ - $first_occ);

        return $diff;
    }

    /**
     * Method to check if a IP is suspicious and update user data.
     *
     * @param $data			array with user data
     * @param $plgParams	Object with plugin params
     * @return  boolean true
     *
     * @since   1.6
     */
    public static function checkIpSuspicious($data, $plgParams) {
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');
        $userIp = $_SERVER['REMOTE_ADDR'];
        $userRegDate = ArrayHelper::getValue($data, 'registerDate', new \JDate(), 'date');
        //Object with array of users with same IP
        $sameIps = self::getUsersByIp($userIp);
        $allowedHits = $plgParams->get('spbot_allowed_hits', 2);
        $allowedSeconds = ($plgParams->get('spbot_suspicious_time', 12)) * 60 * 60;

        if ($sameIps !== false && count($sameIps) > 1) {
        //we have allready an old user with the same IP
            $hits = count($sameIps);
            $suspicious = 1;
            foreach ($sameIps as $pk => $value) {
                //check Time Difference between first registration with this IP and the actual registration
                if ($pk == 0) {
                    $diff = self::getDateDiff($value->registerDate, $userRegDate);
                    if ($diff < $allowedSeconds) {
                        if ($hits > $allowedHits) {
                            //that is suspicious
                            $suspicious = 0;
                        }
                    }
                }

                //update data of old users with same Ip
                if ($userId != $value->user_id) {
                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    $object->id = $value->id;
                    //Set hits field
                    $object->hits = $hits;
                    //Add a note
                    $object->note = $value->note . '1: ' . $userId . '; ';
                    if (($suspicious == 0) && ($value->trust != 1)) {
                        //Set suspicious state
                        $object->suspicious = $suspicious;
                    }
                    // Update their details in the users table using id as the primary key.
	                try {
		                \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('id'));
	                }
	                catch (RuntimeException $e) {

	                }
                }

                //update data of new user
                if ($userId == $value->user_id) {
                    $note = '';
                    foreach ($sameIps as $pk1 => $value1) {
                        if ($userId != $value1->user_id) {
                            $note .= '1: ' . $value1->user_id . '; ';
                        }
                    }
                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    $object->id = $value->id;
                    //Set hits field
                    $object->hits = $hits;
                    //Add a note
                    $object->note = $note;
                    if (($suspicious == 0) && ($value->trust != 1)) {
                        //Set suspicious state
                        $object->suspicious = $suspicious;
                    }
                    // Update their details in the users table using id as the primary key.
	                try {
		                \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('id'));
	                }
	                catch (RuntimeException $e) {

	                }
                }
            }
        }
        return true;
    }

    /**
     * Method to get the value of a specified field using a where condition.
     * @param $table		string Tablename
     * @param $field		string field
     * @param $whereField	string field for where condition
     * @param $value		string value of where condition
     *
     * @return  string 		fieldvalue
     *
     * @since   1.6
     */
    public static function getTableFieldValue($table = '#__user_spambotcheck', $field = 'ip', $whereField = 'user_id', $value = '') {
        //get Registration Ip of deleted user
        if ($field != '' && $whereField != '' && $value != '') {
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName($field));
            $query->from($db->quoteName($table));
            $query->where($db->quoteName($whereField) . " = " . $db->quote($value));
            $db->setQuery($query);
            try {
	            return $db->loadResult();
            }
            catch (RuntimeException $e) {

            }
        }
	    return false;
    }

    /**
     * Method to delete parts of note text of nested datasets with same Ip when a user is deleted.
     * @param $userIp	string	IP
     * @param $userId	string	user id
     *
     * @return  void
     *
     * @since   1.6
     */
    public static function cleanUserSpambotTable($userIp, $userId) {
        //clean up note and hits field
        if ($userIp != "") {
            $sameIps = self::getUsersByIp($userIp);

            foreach ($sameIps as $pk => $value) {
                if ($value->note != "") {
                    $value->note = str_replace('1: ' . $userId . '; ', "", $value->note);
                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    $object->id = $value->id;
                    //Set hits field
                    $object->hits = $value->hits - 1;
                    //Add a note
                    $object->note = $value->note;
                    // Update their details in the users table using id as the primary key.
	                try {
		                \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('id'));
	                }
	                catch ( RuntimeException $e) {

	                }
                }
            }
        }
    }

    /**
     * Method to check if an email address is suspisious and update #_user_spambotcheck table.
     *
     * @param 	array	$data	user
     * @return  void
     *
     * @since   1.6
     */
    public static function checkEmailSuspicious($data) {
        $suspicious = 1;
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');
        $email = ArrayHelper::getValue($data, 'email', '', 'string');
        $note = '';
        if (isset($email) && $email != "") {
            //check vor 3 or more dots left of @
            $regex1 = '/^([^\.]*[\.]){3,}[^\.]*@.*$/';
            if (preg_match($regex1, $email)) {
                //that is suspicious
                $suspicious = 0;
                $note = '2: To many dots; ';
            }
        }

        if ($suspicious == 0) {

            //get Value of note field
            $notevalue = self::getTableFieldValue('#__user_spambotcheck', 'note', 'user_id', $userId);

            // Create an object for the record we are going to update.
            $object = new stdClass();
            $object->user_id = $userId;

            //Add a note
            $object->note = $notevalue . $note;
            //Set suspicious state

            $object->suspicious = $suspicious;

            // Update their details in the users table using user_id as the primary key.
	        try {
		        \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('user_id'));
	        }
	        catch (RuntimeException $e) {

	        }
        }
    }

    /**
     * Method to set an old user to suspicious, if their ip is now listed in spambot databases
     *
     * @param 	String	$userId	user Id of user who was prevented from login because listed in online spambot database
     * @return  void
     *
     * @since   1.6
     */
    public static function flagUserWithSpamUserIp($userId = '0') {
        $userIp = $_SERVER['REMOTE_ADDR'];
        $userRegDate = new \JDate();

        //Object with array of users with same IP
        $sameIps = self::getUsersByIp($userIp);
        $allowedSeconds = 48 * 60 * 60;

        if ($sameIps !== false && count($sameIps) > 0) {
        //we have allready an old user with the same IP
            $suspicious = 1;
            foreach ($sameIps as $pk => $value) {
                //check Time Difference between first registration with this IP and the actual registration
                if ($pk == 0) {
                    $diff = self::getDateDiff($value->registerDate, $userRegDate);
                    if ($diff < $allowedSeconds) {
                        //that is suspicious
                        $suspicious = 0;
                    }
                }
                //update data of old users with same Ip if it doen't allready have the error code in note

                if (($userId != $value->user_id) && ($suspicious == 0) && ($value->trust != 1)) {
                    if (strpos($value->note, '3: IP flagged; ') === false) {
                        // Create an object for the record we are going to update.
                        $object = new stdClass();
                        $object->id = $value->id;
                        //Add a note
                        $object->note = $value->note . '3: IP flagged; ';
                        //Set suspicious state
                        $object->suspicious = $suspicious;
                        // Update their details in the users table using id as the primary key.
	                    try {
		                    \JFactory::getDbo()->updateObject('#__user_spambotcheck', $object, array('id'));
	                    }
	                    catch (RuntimeException $e) {

	                    }
                    }
                }
            }
        }
    }

    public static function checkComponentInstalled() {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('element') . " = " . $db->quote('com_spambotcheck') . ' AND ' . $db->quoteName('enabled') . " = " . $db->quote('1'));
        $db->setQuery($query);
        try {
	        if (!$db->loadResult()) {
		        return false;
	        }
	        return true;
        }
        catch (RuntimeException $e) {
	        return false;
        }
    }
}