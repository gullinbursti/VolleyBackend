
CREATE TABLE `club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `club_type_id` smallint(1) NOT NULL DEFAULT '1',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_id` int(10) unsigned NOT NULL,
  `description` varchar(160) NOT NULL DEFAULT '',
  `img` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_key_1` (`name`),
  KEY `club_type_id_fk_1` (`club_type_id`),
  KEY `owner_id_2` (`owner_id`),
  CONSTRAINT `club_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `tblUsers` (`id`),
  CONSTRAINT `club_type_id_fk_1` FOREIGN KEY (`club_type_id`) REFERENCES `tblClubTypeEnum` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;

