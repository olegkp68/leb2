CREATE TABLE IF NOT EXISTS `#__awebsavedcart` (
  `userid` int(11) NOT NULL,
  `data` mediumtext NOT NULL,
  `date` DATETIME NOT NULL,
  `compr` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `userid_unique` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;