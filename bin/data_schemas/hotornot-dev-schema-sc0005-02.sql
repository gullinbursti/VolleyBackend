
CREATE TABLE `tblClubTypeEnum` (
  `id` SMALLINT(1) NOT NULL AUTO_INCREMENT,
  `club_type` varchar(16) NOT NULL,
  `description` varchar(64) NOT NULL DEFAULT '',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `club_type_id_1` (`club_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (1, "USER_GENERATED", "User generated");
INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (2, "FEATURE", "Feature");
INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (3, "SCHOOL", "School");
INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (4, "NEARBY", "Nearby");
INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (5, "STAFF_CREATED", "Staff created");
INSERT INTO `tblClubTypeEnum` (`id`, `club_type`, `description`) VALUE (6, "THIRD_PARTY", "Sponsored / 3rd party");

ALTER TABLE `club` ADD COLUMN `club_type_id` SMALLINT(1) NOT NULL DEFAULT 1 AFTER `name`;
ALTER TABLE `club` ADD CONSTRAINT `club_type_id_fk_1` FOREIGN KEY (`club_type_id`) REFERENCES `tblClubTypeEnum` (`id`);

