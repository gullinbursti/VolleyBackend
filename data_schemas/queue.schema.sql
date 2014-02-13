-- MySQL dump 10.13  Distrib 5.1.69, for redhat-linux-gnu (i386)
--
-- Host: 127.0.0.1    Database: queue
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
-- Current Database: `queue`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `queue` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `queue`;

--
-- Table structure for table `gearman_jobs`
--

DROP TABLE IF EXISTS `gearman_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gearman_jobs` (
  `id` char(36) NOT NULL DEFAULT '',
  `handle` varchar(48) NOT NULL DEFAULT '',
  `next_run_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `class` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `disabled` smallint(6) NOT NULL DEFAULT '0',
  `schedule` varchar(30) NOT NULL DEFAULT '* * * * *',
  `params` mediumtext,
  `is_temp` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gearman_jobs_bkp`
--

DROP TABLE IF EXISTS `gearman_jobs_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gearman_jobs_bkp` (
  `id` char(36) NOT NULL DEFAULT '',
  `handle` varchar(48) NOT NULL DEFAULT '',
  `next_run_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `class` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `disabled` smallint(6) NOT NULL DEFAULT '0',
  `schedule` varchar(30) NOT NULL DEFAULT '* * * * *',
  `params` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gearman_jobs_bkp_2`
--

DROP TABLE IF EXISTS `gearman_jobs_bkp_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gearman_jobs_bkp_2` (
  `id` char(36) NOT NULL DEFAULT '',
  `handle` varchar(48) NOT NULL DEFAULT '',
  `next_run_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `class` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `disabled` smallint(6) NOT NULL DEFAULT '0',
  `schedule` varchar(30) NOT NULL DEFAULT '* * * * *',
  `params` mediumtext,
  `is_temp` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gearman_jobs_suspended`
--

DROP TABLE IF EXISTS `gearman_jobs_suspended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gearman_jobs_suspended` (
  `id` char(36) NOT NULL DEFAULT '',
  `handle` varchar(48) NOT NULL DEFAULT '',
  `next_run_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `class` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `disabled` smallint(6) NOT NULL DEFAULT '0',
  `schedule` varchar(30) NOT NULL DEFAULT '* * * * *',
  `params` mediumtext,
  `is_temp` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
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

-- Dump completed on 2014-02-13 13:16:32
