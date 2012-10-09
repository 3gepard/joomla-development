CREATE TABLE IF NOT EXISTS `#__postman_newsletters` (
  `letter_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(50) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`letter_id`),
  UNIQUE KEY `subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Contains all newsletters' AUTO_INCREMENT=18 ;

CREATE TABLE  IF NOT EXISTS `#__postman_newsgroups` (
	`newsgroup_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(45) NOT NULL,
	`public` TINYINT(1) NOT NULL default 0,
	`description` VARCHAR(45) NOT NULL, 
	`creation_date` DATETIME NOT NULL default '0000-00-00 00:00:00', 
	`checked_out` TINYINT(1) NOT NULL default '0',
	`checked_out_time` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`newsgroup_id`) 
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Contains all Newsgroups';

CREATE TABLE IF NOT EXISTS `#__postman_subscribers` ( 
    `subscriber_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`name` varchar(64) NOT NULL default '', 
	`email` varchar(64) NOT NULL default '', 
	`confirmed` tinyint(1) NOT NULL default '0',
	`subscribe_date` datetime NOT NULL default '0000-00-00 00:00:00',
	`checked_out` tinyint(1) NOT NULL default '0',
	`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`subscriber_id`), 
	UNIQUE KEY `email` (`email`) 
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Contains all Subscribers';

CREATE TABLE IF NOT EXISTS `#__postman_newsgroups_subscribers` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`newsgroup_id` int(11) unsigned NOT NULL, 
	`subscriber_id` int(11) unsigned NOT NULL, 
	`checked_out` tinyint(1) NOT NULL default '0',
	`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`id`,`newsgroup_id`),
	KEY `newsgroup_id` (`newsgroup_id`, `subscriber_id` (`subscriber_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Contains newsgroups to subscribers associations';

CREATE TABLE IF NOT EXISTS `#__postman_log` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`time_stamp` datetime NOT NULL default '0000-00-00 00:00:00',
	`message` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Contains logs!';

