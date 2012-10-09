CREATE TABLE IF NOT EXISTS `#__postman_tickets` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticketid` VARCHAR(100) NOT NULL DEFAULT '',
    `newsgroup_id` INT(11) unsigned NOT NULL, 
    `email` varchar(100) NOT NULL DEFAULT '',
	`date` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
	`type` TINYINT(1) NOT NULL default '1',
	`checked_out` TINYINT(1) NOT NULL default '0',
	`checked_out_time` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	UNIQUE KEY `idx_ticket` (`ticketid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ticket subscription or cancelation table!';

CREATE TABLE IF NOT EXISTS `#__postman_log` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`time_stamp` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
	`message` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Logs!';

CREATE TABLE IF NOT EXISTS `#__postman_newsletters` (
	`letter_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`subject` VARCHAR(50) NOT NULL DEFAULT '',
	`message` TEXT NOT NULL,
	`published` TINYINT(1) NOT NULL DEFAULT '0',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`checked_out` TINYINT(1) NOT NULL DEFAULT '0',
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`letter_id`),
	UNIQUE KEY `idx_subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Newsletters' AUTO_INCREMENT=18 ;

CREATE TABLE IF NOT EXISTS `#__postman_newsgroups` (
	`newsgroup_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(45) NOT NULL,
	`description` VARCHAR(45) NOT NULL, 
	`public` TINYINT(1) NOT NULL DEFAULT '0',
	`creation_date` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP, 
	`checked_out` TINYINT(1) NOT NULL default '0',
	`checked_out_time` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`newsgroup_id`),
	KEY `idx_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Newsgroups';

CREATE TABLE IF NOT EXISTS `#__postman_subscribers` ( 
    `subscriber_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(64) NOT NULL default '', 
	`email` VARCHAR(64) NOT NULL default '', 
	`active` TINYINT(1) NOT NULL DEFAULT '0',
	`subscribe_date` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
	`checked_out` TINYINT(1) NOT NULL default '0',
	`checked_out_time` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`subscriber_id`),
	UNIQUE KEY `idx_email` (`email`) 
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Subscribers';

CREATE TABLE IF NOT EXISTS `#__postman_newsgroups_subscribers` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`newsgroup_id` INT(11) unsigned NOT NULL, 
	`subscriber_id` INT(11) unsigned NOT NULL, 
	`checked_out` TINYINT(1) NOT NULL default '0',
	`checked_out_time` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`id`,`newsgroup_id`),
	KEY `idx_newsgroup_subscriber` (`newsgroup_id`, `subscriber_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Newsgroups subscribers associations';
