
/* DROP TABLE IF EXISTS `tblUserPhones`; */

CREATE TABLE `tblUserPhones` (
  `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `phone_number_enc` VARCHAR(64) NOT NULL,
  `verified` TINYINT(1) DEFAULT 0,
  `verified_date` DATETIME NULL DEFAULT NULL,
  `verify_code` VARCHAR(10) DEFAULT NULL,
  `verify_count_down` TINYINT(1) DEFAULT 0,
  `verify_count_total` SMALLINT(1) UNSIGNED DEFAULT 0,
  `verify_last_attempt` DATETIME NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone_number_enc` (`phone_number_enc`),
  CONSTRAINT `user_id_fk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

