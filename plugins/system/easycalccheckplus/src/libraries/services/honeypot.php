<?php

/**
 * @copyright
 * @package        EasyCalcCheck Plus Pro - ECC+ for Joomla! 3
 * @author         Viktor Vogel <admin@kubik-rubik.de>
 * @version        3.3.0.0-FREE - 2021-05-03
 * @link           https://kubik-rubik.de/ecc-easycalccheck-plus
 *
 * Project Honey Pot Http BlackList
 * http://www.projecthoneypot.org/httpbl_configure.php
 * Version 0.1 by Francois Dechery, www.440net.net
 */
defined('_JEXEC') || die('Restricted access');

class HttpBl
{
    protected $access_key = "";
    protected $domain = "dnsbl.httpbl.org";
    protected $answer_codes = [
        0 => 'Search Engine',
        1 => 'Suspicious',
        2 => 'Harvester',
        3 => 'Suspicious & Harvester',
        4 => 'Comment Spammer',
        5 => 'Suspicious & Comment Spammer',
        6 => 'Harvester & Comment Spammer',
        7 => 'Suspicious & Harvester & Comment Spammer',
    ];
    protected $ip = '';
    protected $type_txt = '';
    protected $type_num = 0;
    protected $engine_txt = '';
    protected $engine_num = 0;
    protected $days = 0;
    protected $score = 0;

    public function __construct($key = '')
    {
        $key && $this->access_key = $key;
    }

    // return 1 (Search engine) or 2 (Generic) if host is found, else return 0
    public function query($ip)
    {
        if (!$ip) {
            return false;
        }
        $this->ip = $ip;
        [$a, $b, $c, $d] = explode('.', $ip);
        $query = $this->access_key . ".$d.$c.$b.$a." . $this->domain;
        $host = gethostbyname($query);
        [$first, $days, $score, $type] = explode('.', $host);

        if ($first == 127) {
            //spammer
            $this->days = $days;
            $this->score = $score;
            $this->type_num = $type;
            $this->type_txt = $this->answer_codes[$type];

            // search engine
            if ($type == 0) {
                $this->days = 0;
                $this->score = 0;
                $this->engine_num = $score;

                //$this->engine_txt	=$this->engine_codes[$score];
                return 1;
            } else {
                return 2;
            }
        }

        return 0;
    }
}
