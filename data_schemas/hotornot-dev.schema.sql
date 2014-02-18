-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hotornot-dev
-- ------------------------------------------------------
-- Server version	5.5.29-0ubuntu0.12.04.2-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `hotornot-dev`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `hotornot-dev` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `hotornot-dev`;

--
-- Table structure for table `boot_conf`
--

DROP TABLE IF EXISTS `boot_conf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boot_conf` (
  `data` longtext,
  `type` varchar(36) NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boot_conf_bkp`
--

DROP TABLE IF EXISTS `boot_conf_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boot_conf_bkp` (
  `data` longtext,
  `type` varchar(36) NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club`
--

DROP TABLE IF EXISTS `club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_id` int(10) unsigned NOT NULL,
  `description` varchar(160) NOT NULL DEFAULT '',
  `img` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner_id_2` (`owner_id`),
  CONSTRAINT `club_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `club_member`
--

DROP TABLE IF EXISTS `club_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club_member` (
  `club_id` int(11) NOT NULL,
  `extern_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(25) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pending` tinyint(4) DEFAULT '1',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `invited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `joined` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `blocked_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `club_name` (`club_id`,`extern_name`),
  UNIQUE KEY `user_id` (`user_id`,`club_id`),
  UNIQUE KEY `club_id_2` (`club_id`,`mobile_number`),
  UNIQUE KEY `club_id_3` (`club_id`,`email`),
  KEY `club_id` (`club_id`),
  KEY `user_id_2` (`user_id`),
  CONSTRAINT `club_member_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`),
  CONSTRAINT `club_member_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `explore_ids`
--

DROP TABLE IF EXISTS `explore_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `explore_ids` (
  `id` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invite_messages`
--

DROP TABLE IF EXISTS `invite_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite_messages` (
  `type` varchar(36) NOT NULL,
  `message` mediumtext,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mobile_numbers`
--

DROP TABLE IF EXISTS `mobile_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_numbers` (
  `user_id` int(10) NOT NULL,
  `number` varchar(15) NOT NULL,
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persona`
--

DROP TABLE IF EXISTS `persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona` (
  `network` varchar(36) NOT NULL,
  `email` varchar(256) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(255) NOT NULL,
  `extra` mediumtext,
  UNIQUE KEY `name` (`name`,`network`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblChallengeParticipants`
--

DROP TABLE IF EXISTS `tblChallengeParticipants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblChallengeParticipants` (
  `challenge_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `joined` int(11) NOT NULL,
  `likes` int(11) NOT NULL DEFAULT '-1',
  `subject` varchar(255) DEFAULT NULL,
  `has_viewed` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `img` (`img`,`challenge_id`),
  KEY `user_id` (`user_id`),
  KEY `challenge_id` (`challenge_id`),
  KEY `subject` (`subject`),
  CONSTRAINT `tblChallengeParticipants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`),
  CONSTRAINT `tblChallengeParticipants_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `tblChallenges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblChallengeStatusTypes`
--

DROP TABLE IF EXISTS `tblChallengeStatusTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblChallengeStatusTypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `info` varchar(255) CHARACTER SET latin1 NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblChallengeSubjects`
--

DROP TABLE IF EXISTS `tblChallengeSubjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblChallengeSubjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `creator_id` int(10) unsigned NOT NULL,
  `itunes_id` varchar(255) NOT NULL,
  `linkshare_url` varchar(255) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=10003857 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblChallengeVotes`
--

DROP TABLE IF EXISTS `tblChallengeVotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblChallengeVotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `challenger_id` int(10) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `challenge_id` (`challenge_id`),
  KEY `challenger_id` (`challenger_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tblChallengeVotes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98636 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblChallenges`
--

DROP TABLE IF EXISTS `tblChallenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblChallenges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `creator_id` int(10) unsigned NOT NULL,
  `creator_img` varchar(255) CHARACTER SET latin1 NOT NULL,
  `hasPreviewed` char(1) NOT NULL DEFAULT 'N',
  `votes` int(10) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `started` datetime NOT NULL,
  `added` datetime NOT NULL,
  `is_private` char(1) NOT NULL DEFAULT 'N',
  `expires` int(11) NOT NULL DEFAULT '-1',
  `creator_likes` int(11) NOT NULL DEFAULT '-1',
  `subject` varchar(255) NOT NULL,
  `is_verify` tinyint(4) NOT NULL DEFAULT '0',
  `is_explore` tinyint(4) DEFAULT '0',
  `recent_likes` text NOT NULL,
  `club_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `subject_id` (`subject_id`),
  KEY `status_id` (`status_id`),
  KEY `creator_img` (`creator_img`),
  KEY `is_explore` (`is_explore`),
  KEY `club_id` (`club_id`)
) ENGINE=InnoDB AUTO_INCREMENT=230123 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblCommentStatusTypes`
--

DROP TABLE IF EXISTS `tblCommentStatusTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblCommentStatusTypes` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `info` varchar(255) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblComments`
--

DROP TABLE IF EXISTS `tblComments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblComments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `text` varchar(255) NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `challenge_id` (`challenge_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=899 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblFlaggedChallenges`
--

DROP TABLE IF EXISTS `tblFlaggedChallenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblFlaggedChallenges` (
  `challenge_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`challenge_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblFlaggedUserApprovals`
--

DROP TABLE IF EXISTS `tblFlaggedUserApprovals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblFlaggedUserApprovals` (
  `challenge_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `flag` tinyint(4) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  PRIMARY KEY (`challenge_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tblFlaggedUserApprovals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblUsers` (`id`),
  CONSTRAINT `tblFlaggedUserApprovals_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `tblChallenges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblInvitedUsers`
--

DROP TABLE IF EXISTS `tblInvitedUsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblInvitedUsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fb_id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblShoutouts`
--

DROP TABLE IF EXISTS `tblShoutouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblShoutouts` (
  `challenge_id` int(10) unsigned NOT NULL,
  `target_challenge_id` int(10) unsigned NOT NULL,
  `target_user_id` int(10) unsigned NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `challenge_id` (`challenge_id`),
  KEY `target_challenge_id` (`target_challenge_id`),
  KEY `target_user_id_2` (`target_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblUserPokes`
--

DROP TABLE IF EXISTS `tblUserPokes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblUserPokes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `poker_id` int(10) unsigned NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3523 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblUsers`
--

DROP TABLE IF EXISTS `tblUsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblUsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET latin1 NOT NULL,
  `device_token` char(64) CHARACTER SET latin1 DEFAULT NULL,
  `fb_id` varchar(255) CHARACTER SET latin1 NOT NULL,
  `gender` char(1) NOT NULL DEFAULT 'N',
  `img_url` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `website` varchar(255) NOT NULL,
  `paid` char(1) CHARACTER SET latin1 NOT NULL DEFAULT 'N',
  `points` int(10) unsigned NOT NULL,
  `notifications` char(1) NOT NULL DEFAULT 'Y',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `added` datetime NOT NULL,
  `age` int(11) NOT NULL DEFAULT '-1',
  `adid` varchar(36) DEFAULT NULL,
  `abuse_ct` int(11) NOT NULL DEFAULT '0',
  `total_challenges` int(11) NOT NULL DEFAULT '-1',
  `total_votes` int(11) NOT NULL DEFAULT '-1',
  `sms_verified` int(11) NOT NULL DEFAULT '-1',
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `adid` (`adid`),
  UNIQUE KEY `email` (`email`),
  KEY `device_token` (`device_token`)
) ENGINE=InnoDB AUTO_INCREMENT=109001 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tumblr_selfies`
--

DROP TABLE IF EXISTS `tumblr_selfies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tumblr_selfies` (
  `id` bigint(20) NOT NULL,
  `data` mediumtext NOT NULL,
  `time` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `id_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_archive`
--

DROP TABLE IF EXISTS `user_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_archive` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `data` longtext,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-02-18 14:03:08
