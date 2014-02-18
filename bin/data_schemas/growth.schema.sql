-- MySQL dump 10.13  Distrib 5.1.69, for redhat-linux-gnu (i386)
--
-- Host: 127.0.0.1    Database: growth
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
-- Current Database: `growth`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `growth` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `growth`;

--
-- Table structure for table `askfm_answer_log`
--

DROP TABLE IF EXISTS `askfm_answer_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `askfm_answer_log` (
  `time` int(11) NOT NULL,
  `qid` bigint(20) NOT NULL,
  `qtext` mediumtext,
  `qanswer` mediumtext,
  `user_id` varchar(256) NOT NULL,
  `username` varchar(256) NOT NULL,
  `network` varchar(36) NOT NULL,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `askfm_persona_stats_log`
--

DROP TABLE IF EXISTS `askfm_persona_stats_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `askfm_persona_stats_log` (
  `time` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `network` varchar(36) NOT NULL,
  `gifts` int(11) NOT NULL,
  `likes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `askfm_user_contact`
--

DROP TABLE IF EXISTS `askfm_user_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `askfm_user_contact` (
  `user_id` varchar(256) NOT NULL,
  `last_contact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_log`
--

DROP TABLE IF EXISTS `contact_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_log` (
  `time` int(11) NOT NULL,
  `url` varchar(256) NOT NULL,
  `type` varchar(24) NOT NULL,
  `comment` mediumtext NOT NULL,
  `network` varchar(36) NOT NULL,
  `name` varchar(256) NOT NULL DEFAULT 'none'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ig_promoters`
--

DROP TABLE IF EXISTS `ig_promoters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ig_promoters` (
  `name` varchar(100) NOT NULL,
  `followers` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inbound_persona_clicks`
--

DROP TABLE IF EXISTS `inbound_persona_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbound_persona_clicks` (
  `network_id` varchar(36) NOT NULL,
  `referer` text,
  `name` varchar(256) NOT NULL,
  `time` int(11) NOT NULL,
  KEY `networkId` (`network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_checkout`
--

DROP TABLE IF EXISTS `kik_checkout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_checkout` (
  `username` varchar(200) CHARACTER SET latin1 NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_checkout_bkp`
--

DROP TABLE IF EXISTS `kik_checkout_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_checkout_bkp` (
  `username` varchar(200) CHARACTER SET latin1 NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_opens`
--

DROP TABLE IF EXISTS `kik_opens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_opens` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`),
  CONSTRAINT `kik_opens_ibfk_1` FOREIGN KEY (`source`) REFERENCES `kik_reg_users_old` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_opens_bkp`
--

DROP TABLE IF EXISTS `kik_opens_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_opens_bkp` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_opens_bkp_2`
--

DROP TABLE IF EXISTS `kik_opens_bkp_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_opens_bkp_2` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_reg_users`
--

DROP TABLE IF EXISTS `kik_reg_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_reg_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `pic` varchar(200) DEFAULT NULL,
  `thumbnail` varchar(200) DEFAULT NULL,
  `firstName` varchar(200) DEFAULT NULL,
  `lastName` varchar(200) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `appOpens` int(11) DEFAULT '1',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bim_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `bim_id` (`bim_id`),
  KEY `last_login` (`last_login`),
  CONSTRAINT `kik_reg_users_ibfk_1` FOREIGN KEY (`bim_id`) REFERENCES `hotornot-dev`.`tblUsers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2192 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_reg_users_bkp`
--

DROP TABLE IF EXISTS `kik_reg_users_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_reg_users_bkp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `pic` varchar(200) DEFAULT NULL,
  `thumbnail` varchar(200) DEFAULT NULL,
  `firstName` varchar(200) DEFAULT NULL,
  `lastName` varchar(200) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `appOpens` int(11) DEFAULT '1',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bim_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `bim_id` (`bim_id`),
  KEY `last_login` (`last_login`)
) ENGINE=InnoDB AUTO_INCREMENT=818356 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_reg_users_old`
--

DROP TABLE IF EXISTS `kik_reg_users_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_reg_users_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `pic` varchar(200) DEFAULT NULL,
  `thumbnail` varchar(200) DEFAULT NULL,
  `firstName` varchar(200) DEFAULT NULL,
  `lastName` varchar(200) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `appOpens` int(11) DEFAULT '1',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bim_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `bim_id` (`bim_id`),
  KEY `last_login` (`last_login`),
  CONSTRAINT `kik_reg_users_old_ibfk_1` FOREIGN KEY (`bim_id`) REFERENCES `hotornot-dev`.`tblUsers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=829316 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_sends`
--

DROP TABLE IF EXISTS `kik_sends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_sends` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`),
  CONSTRAINT `kik_sends_ibfk_1` FOREIGN KEY (`source`) REFERENCES `kik_reg_users_old` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_sends_bkp`
--

DROP TABLE IF EXISTS `kik_sends_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_sends_bkp` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_sends_bkp_2`
--

DROP TABLE IF EXISTS `kik_sends_bkp_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_sends_bkp_2` (
  `source` varchar(200) NOT NULL,
  `target` varchar(200) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source`,`target`),
  KEY `target` (`target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_users`
--

DROP TABLE IF EXISTS `kik_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_users` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `avatar` text,
  `shout_pic` text,
  `last_update` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `network` varchar(64) NOT NULL,
  PRIMARY KEY (`username`),
  KEY `last_update` (`last_update`),
  KEY `added` (`added`),
  KEY `created_at` (`created_at`),
  KEY `network` (`network`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_users_bkp`
--

DROP TABLE IF EXISTS `kik_users_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_users_bkp` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `avatar` text,
  `shout_pic` text,
  `last_update` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `network` varchar(64) NOT NULL,
  PRIMARY KEY (`username`),
  KEY `last_update` (`last_update`),
  KEY `added` (`added`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kik_users_bkp_2`
--

DROP TABLE IF EXISTS `kik_users_bkp_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kik_users_bkp_2` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `avatar` text,
  `shout_pic` text,
  `last_update` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `network` varchar(64) NOT NULL,
  PRIMARY KEY (`username`),
  KEY `last_update` (`last_update`),
  KEY `added` (`added`),
  KEY `created_at` (`created_at`),
  KEY `network` (`network`)
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
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `type` varchar(20) NOT NULL DEFAULT 'authentic',
  UNIQUE KEY `name` (`name`,`network`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persona_bkp`
--

DROP TABLE IF EXISTS `persona_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona_bkp` (
  `network` varchar(36) NOT NULL,
  `email` varchar(256) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(255) NOT NULL,
  `extra` mediumtext,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `type` varchar(20) NOT NULL DEFAULT 'authentic',
  UNIQUE KEY `name` (`name`,`network`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persona_stats_log`
--

DROP TABLE IF EXISTS `persona_stats_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona_stats_log` (
  `time` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `network` varchar(36) NOT NULL,
  `followers` int(11) NOT NULL DEFAULT '0',
  `following` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotes`
--

DROP TABLE IF EXISTS `quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes` (
  `network` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `quotes` longtext,
  PRIMARY KEY (`network`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rkoi`
--

DROP TABLE IF EXISTS `rkoi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rkoi` (
  `link` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_pool`
--

DROP TABLE IF EXISTS `tag_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_pool` (
  `group_id` varchar(36) DEFAULT NULL,
  `tag` varchar(200) NOT NULL DEFAULT '',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `network` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `tags` longtext,
  PRIMARY KEY (`network`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tumblr_blog_contact`
--

DROP TABLE IF EXISTS `tumblr_blog_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tumblr_blog_contact` (
  `blog_id` varchar(256) NOT NULL,
  `last_contact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_checkout`
--

DROP TABLE IF EXISTS `user_checkout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_checkout` (
  `user_id` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webstagram_contact_log`
--

DROP TABLE IF EXISTS `webstagram_contact_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webstagram_contact_log` (
  `time` int(11) NOT NULL,
  `url` varchar(256) NOT NULL,
  `type` varchar(24) NOT NULL,
  `comment` mediumtext NOT NULL,
  `network` varchar(36) NOT NULL,
  `name` varchar(256) NOT NULL DEFAULT 'none',
  `logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webstagram_user_contact`
--

DROP TABLE IF EXISTS `webstagram_user_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webstagram_user_contact` (
  `user_id` int(11) NOT NULL,
  `last_contact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-02-13 13:15:59
