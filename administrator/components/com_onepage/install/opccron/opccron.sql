CREATE TABLE IF NOT EXISTS `#__onepage_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callable` varchar(512) CHARACTER SET ascii NOT NULL,
  `params` longtext NOT NULL,
  `require_once` text NOT NULL,
  `token` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `job_status` varchar(1) NOT NULL,
  `result` text NOT NULL,
  `cron_repeat` varchar(128) NOT NULL DEFAULT '',
  `repeated` BIGINT NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_on` datetime DEFAULT NULL,
  `finished_on` datetime DEFAULT NULL,
  `notify_email` varchar(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE(`token`),
  KEY `waiting` (`job_status`,`started_on`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;