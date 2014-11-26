
CREATE TABLE `tbl_sku` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(32) NOT NULL,
  `description` VARCHAR(128) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB CHARSET=utf8;

INSERT INTO `tbl_sku` (`id`, `name`, `description`, `updated`, `created`)
    VALUE (1, 'selfieclub', 'The original, and default SKU', NOW(), NOW());

INSERT INTO `tbl_sku` (`name`, `description`, `updated`, `created`)
    VALUE ('emoji', 'Emoji app', NOW(), NOW());

INSERT INTO `tbl_sku` (`name`, `description`, `updated`, `created`)
    VALUE ('last24', 'Last24?', NOW(), NOW());

ALTER TABLE `tblUsers` ADD COLUMN `sku_id` int(10) unsigned NOT NULL DEFAULT 1 AFTER `id`;
ALTER TABLE `tblUsers` ADD CONSTRAINT `sku_id_fk_1` FOREIGN KEY (`sku_id`) REFERENCES `tbl_sku` (`id`);
