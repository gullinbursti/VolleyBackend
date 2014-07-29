
ALTER TABLE `club` ADD UNIQUE KEY `owner_id_name_1` (`owner_id`, `name`);
ALTER TABLE `club` DROP KEY `name_key_1`;

