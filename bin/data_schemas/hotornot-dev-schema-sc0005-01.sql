

ALTER TABLE tblChallengeParticipants ADD id INT PRIMARY KEY AUTO_INCREMENT FIRST;

CREATE TABLE `tblChallengeParticipantSubjectMap` (
  `challenge_participant_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`challenge_participant_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

