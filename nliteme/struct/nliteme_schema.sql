-- MariaDB dump 10.19  Distrib 10.4.22-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: nrnlitemedb
-- ------------------------------------------------------
-- Server version	10.4.22-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `nliteme_build_increments`
--

DROP TABLE IF EXISTS `nliteme_build_increments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_build_increments` (
  `incid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of build increment addition',
  `increment` varchar(40) NOT NULL COMMENT 'name of the increment',
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`incid`),
  UNIQUE KEY `increment` (`increment`),
  KEY `createdate` (`createdate`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_build_increments_description`
--

DROP TABLE IF EXISTS `nliteme_build_increments_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_build_increments_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_incid` FOREIGN KEY (`id`) REFERENCES `nliteme_build_increments` (`incid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_builds`
--

DROP TABLE IF EXISTS `nliteme_builds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_builds` (
  `buildid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of build compilation',
  `testdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'timestamp of latest test execution',
  `build` varchar(40) NOT NULL COMMENT 'name of the build',
  `incid` int(10) NOT NULL DEFAULT 0 COMMENT 'index of the increment in build_increments table',
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`buildid`),
  UNIQUE KEY `build` (`build`),
  KEY `createdate` (`createdate`),
  KEY `testdate` (`testdate`),
  KEY `incid` (`incid`),
  CONSTRAINT `fk_builds_incid` FOREIGN KEY (`incid`) REFERENCES `nliteme_build_increments` (`incid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_builds_description`
--

DROP TABLE IF EXISTS `nliteme_builds_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_builds_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_buildid` FOREIGN KEY (`id`) REFERENCES `nliteme_builds` (`buildid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_features`
--

DROP TABLE IF EXISTS `nliteme_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_features` (
  `fid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of the feature addition',
  `fname` varchar(50) NOT NULL COMMENT 'name of the feature',
  `hlink` varchar(512) NOT NULL,
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `fname` (`fname`),
  KEY `createdate` (`createdate`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO nliteme_features (fname) values ('Unknown');

--
-- Table structure for table `nliteme_features_description`
--

DROP TABLE IF EXISTS `nliteme_features_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_features_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_fid` FOREIGN KEY (`id`) REFERENCES `nliteme_features` (`fid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_preferences`
--

DROP TABLE IF EXISTS `nliteme_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_preferences` (
  `name` varchar(64) NOT NULL,
  `params` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_suites_cases_map`
--

DROP TABLE IF EXISTS `nliteme_suites_cases_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_suites_cases_map` (
  `tsid` int(10) NOT NULL COMMENT 'test suite id',
  `tcid` int(10) NOT NULL COMMENT 'test case id',
  KEY `tsid` (`tsid`),
  KEY `tcid` (`tcid`),
  CONSTRAINT `fk_suites_cases_map_tcid` FOREIGN KEY (`tcid`) REFERENCES `nliteme_testcases` (`tcid`) ON DELETE CASCADE,
  CONSTRAINT `fk_suites_cases_map_tsid` FOREIGN KEY (`tsid`) REFERENCES `nliteme_testsuites` (`tsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testcases`
--

DROP TABLE IF EXISTS `nliteme_testcases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testcases` (
  `tcid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of test case addition',
  `tcname` varchar(255) NOT NULL,
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  `fid` int(10) DEFAULT 1,
  `coverage` tinyint(3) DEFAULT 0,
  PRIMARY KEY (`tcid`),
  UNIQUE KEY `tcname` (`tcname`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testcases_description`
--

DROP TABLE IF EXISTS `nliteme_testcases_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testcases_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_tcid` FOREIGN KEY (`id`) REFERENCES `nliteme_testcases` (`tcid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testlines`
--

DROP TABLE IF EXISTS `nliteme_testlines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testlines` (
  `tlid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of test line addition',
  `tlname` varchar(40) NOT NULL,
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`tlid`),
  UNIQUE KEY `tlname` (`tlname`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testlines_description`
--

DROP TABLE IF EXISTS `nliteme_testlines_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testlines_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_tlid` FOREIGN KEY (`id`) REFERENCES `nliteme_testlines` (`tlid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testresults`
--

DROP TABLE IF EXISTS `nliteme_testresults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testresults` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of test result addition',
  `incid` int(10) NOT NULL DEFAULT 0 COMMENT 'build increment id from nliteme_build_increments',
  `buildid` int(10) NOT NULL DEFAULT 0 COMMENT 'build id from nliteme_builds ',
  `tsid` int(10) NOT NULL DEFAULT 0 COMMENT 'ts id from nliteme_testsuites',
  `tcid` int(10) NOT NULL DEFAULT 0 COMMENT 'tc id from nliteme_testcases',
  `tlid` int(10) NOT NULL DEFAULT 0 COMMENT 'tl id from nliteme_testlines',
  `tcverdict` smallint(4) NOT NULL DEFAULT 0 COMMENT 'array index to tcstatuses defined in nliteme_preferences',
  `extracolumn_0` smallint(4) NOT NULL DEFAULT 0 COMMENT 'array index to extracolumn_0',
  `extracolumn_1` smallint(4) NOT NULL DEFAULT 0 COMMENT 'custom use small integer value',
  `extracolumn_2` mediumint(6) NOT NULL DEFAULT 0 COMMENT 'custom use medium integer value',
  `extracolumn_3` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'custom use decimal value',
  `duration` int(9) NOT NULL DEFAULT 0 COMMENT 'time it took to execute a testcase',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  `filepath` text NOT NULL COMMENT 'path to the log file. has to be unique',
  `hash` binary(32) NOT NULL COMMENT 'holds unique md5 hash from filepath',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `incid` (`incid`),
  KEY `buildid` (`buildid`),
  KEY `tcid` (`tcid`),
  KEY `tsid` (`tsid`),
  KEY `tlid` (`tlid`),
  KEY `tcverdict` (`tcverdict`),
  KEY `extracolumn_0` (`extracolumn_0`),
  KEY `extracolumn_1` (`extracolumn_1`),
  KEY `extracolumn_2` (`extracolumn_2`),
  KEY `extracolumn_3` (`extracolumn_3`),
  CONSTRAINT `fk_testresults_buildid` FOREIGN KEY (`buildid`) REFERENCES `nliteme_builds` (`buildid`) ON DELETE CASCADE,
  CONSTRAINT `fk_testresults_incid` FOREIGN KEY (`incid`) REFERENCES `nliteme_build_increments` (`incid`) ON DELETE CASCADE,
  CONSTRAINT `fk_testresults_tcid` FOREIGN KEY (`tcid`) REFERENCES `nliteme_testcases` (`tcid`) ON DELETE CASCADE,
  CONSTRAINT `fk_testresults_tlid` FOREIGN KEY (`tlid`) REFERENCES `nliteme_testlines` (`tlid`) ON DELETE CASCADE,
  CONSTRAINT `fk_testresults_tsid` FOREIGN KEY (`tsid`) REFERENCES `nliteme_testsuites` (`tsid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testresults_description`
--

DROP TABLE IF EXISTS `nliteme_testresults_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testresults_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_id` FOREIGN KEY (`id`) REFERENCES `nliteme_testresults` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testsuites`
--

DROP TABLE IF EXISTS `nliteme_testsuites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testsuites` (
  `tsid` int(10) NOT NULL AUTO_INCREMENT,
  `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date of test suite addition',
  `tsname` varchar(40) NOT NULL,
  `shortdescription` text DEFAULT NULL COMMENT 'contains most important part of the description, keywords etc',
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`tsid`),
  UNIQUE KEY `tsname` (`tsname`),
  KEY `createdate` (`createdate`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nliteme_testsuites_description`
--

DROP TABLE IF EXISTS `nliteme_testsuites_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nliteme_testsuites_description` (
  `id` int(10) NOT NULL,
  `description` mediumblob DEFAULT NULL COMMENT 'contains a gzip compresed description',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_description_tsid` FOREIGN KEY (`id`) REFERENCES `nliteme_testsuites` (`tsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Trigger for for read only column and update table `nliteme_testcases` on drop in `nliteme_features`` 
--
DELIMITER $$ 
CREATE TRIGGER before_features_delete BEFORE DELETE ON nliteme_features 
FOR EACH ROW 
BEGIN 
   UPDATE nliteme_testcases SET fid = 1, coverage = 0 where fid = OLD.fid;
   IF OLD.fid <=> 1 THEN
     SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete locked record';
   END IF;
END$$
DELIMITER ;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-02-02 22:49:31
