CREATE TABLE IF NOT EXISTS `#__postman_newsletters` (`letter_id` int(11) NOT NULL auto_increment,
`subject` varchar(50) NOT NULL default '',
`message` text NOT NULL,
`published` tinyint(1) NOT NULL default '0',
`created` datetime NOT NULL default '0000-00-00 00:00:00',
`sent` datetime NOT NULL default '0000-00-00 00:00:00',
`checked_out` tinyint(1) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY
(`letter_id`) ) TYPE=MyISAM COMMENT='Contains all newsletters';

CREATE TABLE  IF NOT EXISTS `#__postman_newsgroups` (`newsgroup_id` INTEGER
UNSIGNED NOT NULL AUTO_INCREMENT, 
`name` VARCHAR(45) NOT NULL,
`description` VARCHAR(45) NOT NULL, 
`creation_date` datetime NOT NULL default '0000-00-00 00:00:00', 
`checked_out` tinyint(1) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY (`newsgroup_id`) )
ENGINE = MyISAM COMMENT='Contains all Newsgroups';

CREATE TABLE IF NOT EXISTS `#__postman_newsgroups_subscribers` (`id` int(11) unsigned NOT NULL auto_increment, 
`newsgroup_id` int(11) unsigned NOT NULL, 
`subscriber_id` int(11) unsigned NOT NULL, 
`checked_out` tinyint(1) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY (`id`,`newsgroup_id`),
KEY `newsgroup_id` (`newsgroup_id`), 
KEY `subscriber_id` (`subscriber_id`) ) 
ENGINE=MyISAM COMMENT='Contains newsgroups to subscribers associations';

CREATE TABLE IF NOT EXISTS `#__postman_subscribers` ( `subscriber_id` int(11) NOT NULL auto_increment, 
`user_id` int(11) NOT NULL default '0', 
`name` varchar(64) NOT NULL default '', 
`email` varchar(64) NOT NULL default '', 
`confirmed` tinyint(1) NOT NULL default '0',
`subscribe_date` datetime NOT NULL default '0000-00-00 00:00:00',
`checked_out` tinyint(1) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY (`subscriber_id`), UNIQUE KEY
`email` (`email`) ) TYPE=MyISAM COMMENT='Contains all Subscribers';


DROP TABLE `xelfz_postman_log`;

CREATE TABLE IF NOT EXISTS `xlefz_postman_log` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
	`time_stamp` datetime NOT NULL default '0000-00-00 00:00:00',
	`message` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Contains logs!';

SELECT DISTINCT subscriber_id FROM `xlefz_postman_newsgroups_subscribers`
GROUP BY subscribers_id

INSERT INTO INTO `xlefz_temp` (id)
SELECT DISTINCT id 
FROM `xlefz_postman_newsgroups_subscribers` s
GROUP BY s.subscriber_id 
LIMIT 0, 1000