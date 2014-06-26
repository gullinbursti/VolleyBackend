
CREATE TABLE `club_member` (
  `club_id` int(11) NOT NULL,
  `extern_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(25) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pending` tinyint(4) DEFAULT '1',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `invited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `joined` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `blocked_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `club_name` (`club_id`,`extern_name`),
  UNIQUE KEY `user_id` (`user_id`,`club_id`),
  UNIQUE KEY `club_id_2` (`club_id`,`mobile_number`),
  UNIQUE KEY `club_id_3` (`club_id`,`email`),
  KEY `club_id` (`club_id`),
  KEY `user_id_2` (`user_id`),
  CONSTRAINT `club_member_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`),
  CONSTRAINT `club_member_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

