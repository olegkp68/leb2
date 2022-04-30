CREATE TABLE IF NOT EXISTS `#__sttcartusave` (
  `id` int(15) NOT NULL auto_increment,
  `vmcart` LONGTEXT NOT NULL,
  `userid` int(11) NOT NULL,
  `created` DATETIME,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;