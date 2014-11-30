CREATE TABLE `tbl_status_update_voter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_update_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `vote` tinyint(2) NOT NULL,
  `voted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_update_id` (`status_update_id`,`member_id`),
  KEY `status_update_index` (`status_update_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `tbl_status_update_voter_ibfk_1` FOREIGN KEY (`status_update_id`) REFERENCES `tblChallenges` (`id`),
  CONSTRAINT `tbl_status_update_voter_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;
