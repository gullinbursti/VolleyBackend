CREATE TABLE `moji_invite` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `extern_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(25) DEFAULT NULL,
  `pending` tinyint(4) DEFAULT '1',
  `emoji` varchar(255) DEFAULT NULL,
  `invited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite` (`member_id`,`user_id`,`mobile_number`),
  KEY `user` (`user_id`),
  CONSTRAINT `member` FOREIGN KEY (`member_id`) REFERENCES `tblUsers` (`id`),
  CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
