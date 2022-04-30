DROP TABLE IF EXISTS `#__spambot_attempts`;
CREATE TABLE IF NOT EXISTS `#__spambot_attempts` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', 
	`action` varchar(255), 
	`email` varchar(255), 
	`ip` varchar(15),
	`username` varchar(255), 
	`engine` varchar(255), 
	`request` varchar(255), 
	`raw_return` varchar(255), 
	`parsed_return` varchar(255), 
	`attempt_date` varchar(255),
	PRIMARY KEY(`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__user_spambotcheck`;
CREATE TABLE IF NOT EXISTS `#__user_spambotcheck` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested User Id',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'User IP on registration',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `suspicious` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'User with spambot characteristics',
  `trust` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'User that is allways trusted',
  `note` text NOT NULL COMMENT 'Spambot characteristic',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_spambotcheck` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
