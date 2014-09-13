CREATE TABLE `tbl_emotion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_status_update_emotion` (
  `status_update_id` int(10) unsigned NOT NULL,
  `emotion_id_count` tinyint(3) unsigned NOT NULL,
  `emotion_id_json` blob NOT NULL,
  PRIMARY KEY (`status_update_id`),
  CONSTRAINT `status_update` FOREIGN KEY (`status_update_id`) REFERENCES `tblChallenges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
