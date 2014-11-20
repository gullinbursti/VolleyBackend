ALTER TABLE `tblChallenges` ADD COLUMN `parent_id` INT(10) UNSIGNED DEFAULT 0 AFTER `id`;
ALTER TABLE `tblChallenges` ADD KEY `parent_id` (`parent_id`);
