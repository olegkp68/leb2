<?php
/**
 * @package		User SpamCheck - check for possible spambots during register and login
 * @author		vi-solutions, Aicha Vack
 * @copyright	Copyright (C) 2010 vi-solutions. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once(__DIR__ .  '/SpambotCheckHelpers.php');

class plgSpambotCheckImpl {
    // string SPAMBOT_FALSE (not a spambot), SPAMBOT_TRUE (spambot) + description text
    public $sIdentifierTag = '';
    
    // to access the plugin parameter
    private $plgParams;
    
    // original values
    private $pEmail = '';
    private $pIP = '';
    private $pUsername = '';
    
    // fields to work with
    private $sEmail = '';
    private $sIP = '';
    private $sUsername = '';
    private $reverseIP = '';
    private $bXMLAvailable = false;

    /**
    * Constructor
    * @access	public
    * @param   JRegistry   plugin paramters
    * @param   string   email to check
    * @param   string   IP to check
    * @param   string   username to check
    *
    * @return	void
    */
    public function __construct(&$plgParams, $sEmail, $sIP, $sUsername) {
        // to access the plugin parameter
        $this->plgParams = $plgParams;
        
        // original values
        $this->pEmail = $sEmail;
        $this->pIP = $sIP;
        $this->pUsername = $sUsername;
        
        // fields to work with
        $this->sEmail = $sEmail;
        $this->sIP = $sIP;
        $this->sUsername = $sUsername;
        
        // All DNSBL take a reverse IP
        $this->reverseIP = implode('.', array_reverse(explode('.', $sIP)));
        
        // As of PHP 5.0, the SimpleXML functions are part of the PHP core. 
        // There is no installation needed to use these functions.
        // (http://www.w3schools.com/PHP/php_xml_simplexml.asp)
        $this->bXMLAvailable = (phpversion() > '5' && class_exists('SimpleXMLElement') == true);
    }
    
    /**
     * This method checks the field sIdentifierTag if a spammer identification is made (is or is not a spammer).
     *
     * @access	private
     * @return	boolean
     *
     */
    private function isIdentified() {
        if (strpos($this->sIdentifierTag, 'SPAMBOT_TRUE') !== false || strpos($this->sIdentifierTag, 'SPAMBOT_FALSE') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * This method dispatches calls to 
     * - prepare the parameters
     * - check against SpambotChek own listings
     * - check against online providers
     * 
     * The final result is saved in field sIdentifierTag.
     *
     * @access	private
     * @return	void
     */
    public function checkSpambots() {
        // file_get_contents function and cURL are available ?
        if (!plgSpambotCheckHelpers::isCUrlAvailable() && !function_exists('file_get_contents')) {
            $this->sIdentifierTag =  'SPAMBOT_FALSE';
            return;
        }
        
        // set to empty string if invalid or if not to check, clean username
        $this->checkUserDataFormatsAndActions();
		if ($this->isIdentified()) {
            return;
        }
        
        // check against SpambotCheck own listings (black and white, mail and IP)
        $this->checkOwnListings();
		if ($this->isIdentified()) {
            return;
        }
        
        // this is it: check against the online providers
        $this->checkSpambotProviders();
    }

    /**
     * This method prepares the parameters, checks for valid input formats and cleans the user name string
     *
     * @access	private
     * @return	void
     */
    private function checkUserDataFormatsAndActions() {
        // set to empty string if it is not to check
        if (!$this->plgParams->get('spbot_check_email', 1)) {
            $this->sEmail = '';
        }
        if (!$this->plgParams->get('spbot_check_ip', 1)) {
            $this->sIP = '';
        }
        if (!$this->plgParams->get('spbot_username', 0)) {
            $this->sUsername = '';
        }

        // set to empty string if invalid
        $this->sEmail = plgSpambotCheckHelpers::isvalidEmail($this->sEmail);
        $this->sIP = plgSpambotCheckHelpers::isvalidIP($this->sIP);
        
        // clean username
        $this->sUsername = plgSpambotCheckHelpers::cleanUsername($this->sUsername);

        if ($this->sEmail == '' && $this->sIP == '' && $this->sUsername == '') {
            // nothing to check
            $this->sIdentifierTag =  'SPAMBOT_FALSE';
        }
    }
    
    /**
     * This method dispatches calls to 
     * - check against SpambotChek own IP listing
     * - check against SpambotChek own Email whitelist
     * - check against SpambotChek own Email blacklist
     *
     * @access	private
     * @return	void
     */
    private function checkOwnListings() {
        // check against SpambotCheck own listings (black and white, mail and IP)
        
        // check against own IP listing
        $this->checkOwnListingsIP();
        if ($this->isIdentified()) {
            return;
        }
        
        // check against own Email whitelist
        $this->checkOwnListingsEMailWhite();
        if ($this->isIdentified()) {
            return;
        }

        // check against own Email blacklist
        $this->checkOwnListingsEMailBlack();
        if ($this->isIdentified()) {
            return;
        }
    }
    
    /**
     * This method checks against SpambotChek own IP listing.
     *
     * @access	private
     * @return	void
     */
    private function checkOwnListingsIP() {
        $ip_whitelist = $this->plgParams->get('spbot_whitelist_ip', '');
        if ($this->sIP != '' && $ip_whitelist != '' && (strpos($ip_whitelist, $this->sIP) !== false)) {
            // ip in whitelist
            $this->sIdentifierTag =  'SPAMBOT_FALSE';
        }
    }
    
    /**
     * This method checks against SpambotChek own Email whitelist.
     *
     * @access	private
     * @return	void
     */
    private function checkOwnListingsEMailWhite() {
        // read plugin parameter settings
        $email_whitelist = $this->plgParams->get('spbot_whitelist_email', '');
        $allow_generic_email_check = $this->plgParams->get('allow_generic_email_check', false);
        
        // clean and check email whitelist
        if (($email_whitelist = plgSpambotCheckHelpers::cleanEMailWhitelist($email_whitelist)) == '') {
            return;
        }
        
        // generic whitelist
        if ($allow_generic_email_check) {
            //split all whitelist emails into an array
            $email_whitelist = explode(',', $email_whitelist);
            //check if whitelist entry is a valid email domain-port (@mail.com)
            $l = count($email_whitelist);
            for ($i = 0; $i < $l; $i++) {
                $regex = '/\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
                if (preg_match($regex, $email_whitelist[$i])) {
                    if (strpos($this->sEmail, $email_whitelist[$i]) !== false) {
                        // email in generic whitelist
                        $this->sIdentifierTag =  'SPAMBOT_FALSE';
                    }
                }
            }
        }
        // whitelist
        else if (($this->sEmail != '') && (strpos($email_whitelist, $this->sEmail) !== false)) {
            // email in whitelist
            $this->sIdentifierTag =  'SPAMBOT_FALSE';
        }
    }
    
    /**
     * This method checks against SpambotChek own Email blacklist.
     *
     * @access	private
     * @return	void
     */
    private function checkOwnListingsEMailBlack() {
        // process email blacklists
        if ($this->sEmail == '') {
            return;
        }
        
        // read plugin parameter settings
        $email_blacklist = $this->plgParams->get('spbot_blacklist_email', '');

        // clean and check email blacklist
        if (($email_blacklist = plgSpambotCheckHelpers::cleanEMailBlacklist($email_blacklist)) == '') {
            return;
        }
        
        // blacklist emails into an array
        $email_blacklist = explode(',', $email_blacklist);
        $l = count($email_blacklist);
        // for each blacklist entry
        for ($i = 0; $i < $l; $i++) {
            // check if valid email domain-port (@mail.com)
            $regex = '/\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
            if (preg_match($regex, $email_blacklist[$i])) {
                // valid email domain port
                if (strpos($this->sEmail, $email_blacklist[$i]) !== false) {
                    $spbot_bl_log_to_db = $this->plgParams->get('spbot_bl_log_to_db', 0);
                    if ($spbot_bl_log_to_db) {
                        $this->plgParams->set('spbot_log_to_db', 1);
                        plgSpambotCheckHelpers::logSpammerToDB($this->sEmail, $this->sIP, $this->sUsername, 'Blacklist', '', '', '', $this->plgParams);
                    }
                    // email in blacklist
                    $this->sIdentifierTag =  'E-Mail in Backlist' . ' SPAMBOT_TRUE';
                }
            }
        }
    }
    
    /**
     * This method dispatches calls to all online spambot sites as configured.
     *
     * @access	private
     * @return	void
     */
    private function checkSpambotProviders() {
        //store some information about spammer in plugin parameter
        $this->plgParams->set('isSpamIp', 0);

        // StopForumSpam
        $this->checkSpambotProvider_StopForumSpam();
		if ($this->isIdentified()) {
            return;
        }
        
        // finished if no IP
        if ($this->sIP == '') {
            $this->sIdentifierTag =  'SPAMBOT_FALSE';
            return;
        }
        
        // now check all DNSBL (domain name service black-list)
        // 
        // ProjectHoneyPot
        $this->checkSpambotProvider_ProjectHoneyPot();
		if ($this->isIdentified()) {
            return;
        }
        
        // checkSpambotProvider_Sorbs
        $this->checkSpambotProvider_Sorbs();
		if ($this->isIdentified()) {
            return;
        }
        
        // SpamCop.net
        $this->checkSpambotProvider_SpamCop();
		if ($this->isIdentified()) {
            return;
        }
        
        // not a spambot
        $this->sIdentifierTag =  'SPAMBOT_FALSE';
    }

    /**
     * This method checks against the provider StopForumSpam.
     *
     * @access	private
     * @return	void
     */
    private function checkSpambotProvider_StopForumSpam() {
        // we need the xml parser to work with the API call results
        if ($this->bXMLAvailable == false) {
            return;
        }
        
        // provider enabled ?
        if (($bCheckStopforumSpam = $this->plgParams->get('spbot_stopforumspam', 0)) != 1) {
            return;
        }
        
        $bStopforumspam_MaxAllowedFrequency = $this->plgParams->get('spbot_stopforumspam_max_allowed_frequency', 0);
        
        // build URL
        $URL = 'http://www.stopforumspam.com/api?';
        if ($this->sEmail != '') {
            $URL .= 'email=' . $this->sEmail . "&";
        }
        if ($this->sIP != '') {
            $URL .= 'ip=' . $this->sIP . "&";
        }
        if ($this->sUsername != '') {
            $URL .= 'username=' . $this->sUsername . "&";
        }
        // remove last '&'
        $URL = substr($URL, 0, -1);
        
        // call URL & check result 
        $fspamcheck = plgSpambotCheckHelpers::getURL($URL);
        if (strpos($fspamcheck, 'rate limit exceeded') !== false) {
            // Added due to SFS introducing a query limit
            // http://www.stopforumspam.com/forum/t573-Rate-Limiting
            // this isn't really a spammer - we add the entry neverthless to track the limit-issue of StopForumSpam
            plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'StopForumSpam', $URL, $fspamcheck, 'rate limit exceeded', $this->plgParams);
            
            return;
        }
        
        if (strpos($fspamcheck, '<') !== 0) {
            return;
        }
            
        // Read the result into a SimpleXML and investigate it
        $sfsxml = new SimpleXMLElement($fspamcheck);
        $parsedResponse = '';

        // At least one issue (email, ip, username) should be reported more than MinReportFrequency
        $frequency_array = array();
        foreach ($sfsxml->frequency as $frequency) {
            $frequency_array[] = (int) $frequency;
        }
        
        // $bStopforumspam_MaxAllowedFrequency reached? -> we have a spambot
        if (max($frequency_array) >= $bStopforumspam_MaxAllowedFrequency) {
            $bMail = false;
            $bIP = false;
            $bUsername = false;
            $cnt = 0;
            foreach ($sfsxml->type as $type) {
                switch ((string) $type) {
                    case 'email':
                        if ($sfsxml->appears[$cnt] == 'yes') {
                            $bMail = TRUE;
                            $parsedResponse .= 'EMail: frequency=' . $sfsxml->frequency[$cnt] . ', last_seen=' . $sfsxml->lastseen[$cnt] . '; ';
                        }
                        break;
                    case 'ip':
                        if ($sfsxml->appears[$cnt] == "yes") {
                            $bIP = TRUE;
                            $parsedResponse .= 'IP: frequency=' . $sfsxml->frequency[$cnt] . ', last_seen=' . $sfsxml->lastseen[$cnt] . '; ';
                            $this->plgParams->set('isSpamIp', 1);
                        }
                        break;
                    case 'username':
                        if ($sfsxml->appears[$cnt] == "yes") {
                            $bUsername = TRUE;
                            $parsedResponse .= 'UserName: frequency=' . $sfsxml->frequency[$cnt] . ', last_seen=' . $sfsxml->lastseen[$cnt] . '; ';
                        }
                        break;
                }
                $cnt = $cnt + 1;
            }

            if ($bMail || $bIP || $bUsername) {
                plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'StopForumSpam', $URL, $fspamcheck, $parsedResponse, $this->plgParams);
                $this->sIdentifierTag =  'StopForumSpam (' . $parsedResponse . ') SPAMBOT_TRUE';
            }
        }
    }
    
     /**
     * This method checks against the provider ProjectHoneyPot.
     * The final result is saved in field sIdentifierTag.
     *
     * @access	private
     * @return	void
     */
    private function checkSpambotProvider_ProjectHoneyPot() {
        // provider enabled ?
        if (($bCheckStopforumSpam = $this->plgParams->get('spbot_projecthoneypot', 0)) != 1) {
            return;
        }
        
        // Project Honey Pot API Key
        // Note: This key is required if you wish to query Projecthoneypot.org
        $sPHoneyPotApiKey = $this->plgParams->get('spbot_projecthoneypot_api_key', '');
        if (strlen($sPHoneyPotApiKey) != 12) {
            return;
        }
        
        $sPHoneyPot_MaxAllowedThreatScore = $this->plgParams->get('spbot_projecthoneypot_max_allowed_threat_rating', 0);
        $lookup = $sPHoneyPotApiKey . '.' . $this->reverseIP . '.dnsbl.httpbl.org.';
        $lookupResult = gethostbyname($lookup);
        
        if ($lookup == $lookupResult) {
            return;
        }
        
        $sTempArr = explode('.', $lookupResult);
        $sDays = $sTempArr[1];
        $sThreatScore = $sTempArr[2];
        $sVisitorType = $sTempArr[3]; // Let's see what PHP says about this IP

        if ($sThreatScore < $sPHoneyPot_MaxAllowedThreatScore) {
            return;
        }
        
        $sphpspambot = true;
        switch ($sVisitorType) {
            case "0":
                $sVisitorType = "Search Engine";
                $sphpspambot = false;
                break;
            case "1":
                $sVisitorType = "Suspicious";
                if ($sThreatScore < 25) {
                    $sphpspambot = false;
                }
                break;
            case "2":
                $sVisitorType = "Harvester";
                $sphpspambot = true;
                break;
            case "3":
                $sVisitorType = "Suspicious &amp; Harvester";
                $sphpspambot = true;
                break;
            case "4":
                $sVisitorType = "Comment Spammer";
                $sphpspambot = true;
                break;
            case "5":
                $sVisitorType = "Suspicious &amp; Comment Spammer";
                $sphpspambot = true;
                break;
            case "6":
                $sVisitorType = "Harvester &amp; Comment Spammer";
                $sphpspambot = true;
                break;
            case "7":
                $sVisitorType = "Suspicious &amp; Harvester &amp; Comment Spammer";
                $sphpspambot = true;
                break;
        }
        
        // Do an echo if $sphpspambot = true
        if ($sphpspambot == true) {
            $this->plgParams->set('isSpamIp', 1);
            $parsedResponse = 'VisitorType=' . $sVisitorType . ', ThreatScore=' . $sThreatScore . ', DaysSinceLastActivity=' . $sDays . '';
            plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'ProjectHoneyPot', $lookup, $lookupResult, $parsedResponse, $this->plgParams);
            $this->sIdentifierTag =  'ProjectHoneyPot (RawResponse=' . $lookupResult . ', ' . $parsedResponse . ')' . ' SPAMBOT_TRUE';
        }
    }
    
     /**
     * This method checks against the provider SORBS.
     * The final result is saved in field sIdentifierTag.
     *
     * @access	private
     * @return	void
     */
    private function checkSpambotProvider_Sorbs() {
        // provider enabled ?
        if (($bCheckSorbs = $this->plgParams->get('spbot_sorbs', 0)) != 1) {
            return;
        }
        
        $lookup = $this->reverseIP . '.l1.spews.dnsbl.sorbs.net.';
        $lookupResult = gethostbyname($lookup);
        if ($lookup != $lookupResult) {
            plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'Sorbs', $lookup, $lookupResult, '', $this->plgParams);
            $this->sIdentifierTag =  'Sorbs (RawResponse=' . $lookupResult . ') SPAMBOT_TRUE';
        }

        $lookup = $this->reverseIP . '.problems.dnsbl.sorbs.net.';
        $lookupResult = gethostbyname($lookup);
        if ($lookup != $lookupResult) {
            $this->plgParams->set('isSpamIp', 1);
            plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'Sorbs', $lookup, $lookupResult, '', $this->plgParams);
            $this->sIdentifierTag =  'Sorbs (RawResponse=' . $lookupResult . ') SPAMBOT_TRUE';
        }
    }
    
     /**
     * This method checks against the provider SpamCop.
     * The final result is saved in field sIdentifierTag.
     * 
     * @access	private
     * @return	void
     */
    private function checkSpambotProvider_SpamCop() {
        // provider enabled ?
        if (($bCheckSpamCop = $this->plgParams->get('spbot_spamcop', 0)) != 1) {
            return;
        }
        
        $lookup = $this->reverseIP . '.bl.spamcop.net.';
        $lookupResult = gethostbyname($lookup);
        if ($lookupResult == '127.0.0.2') {
            $this->plgParams->set('isSpamIp', 1);
            plgSpambotCheckHelpers::logSpammerToDB($this->pEmail, $this->pIP, $this->pUsername, 'SpamCop', $lookup, $lookupResult, '', $this->plgParams);
            $this->sIdentifierTag =  'SpamCop (RawResponse=' . $lookupResult . ') SPAMBOT_TRUE';
        }
    }
}