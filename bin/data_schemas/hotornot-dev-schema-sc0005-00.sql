CREATE TABLE `tblChallengeSubjectMap` (
  `challenge_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`challenge_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
