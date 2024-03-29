-- MySQL dump 10.13  Distrib 5.5.49, for debian-linux-gnu (x86_64)
--
-- Host: internal-db.s215537.gridserver.com    Database: db215537_EL
-- ------------------------------------------------------
-- Server version	5.6.25-73.1

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
-- Current Database: `db215537_EL`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db215537_EL` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `db215537_EL`;

--
-- Table structure for table `matatu_test`
--

DROP TABLE IF EXISTS `matatu_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `matatu_test` (
  `_UID` int(11) NOT NULL AUTO_INCREMENT COMMENT '{"column_format": "hidden"}',
  `asdf` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`_UID`),
  KEY `asdf_IX` (`asdf`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matatu_test`
--

LOCK TABLES `matatu_test` WRITE;
/*!40000 ALTER TABLE `matatu_test` DISABLE KEYS */;
/*!40000 ALTER TABLE `matatu_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `db215537_EL`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db215537_EL` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `db215537_EL`;

--
-- Table structure for table `matatu_test`
--

DROP TABLE IF EXISTS `matatu_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `matatu_test` (
  `_UID` int(11) NOT NULL AUTO_INCREMENT COMMENT '{"column_format": "hidden"}',
  `asdf` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`_UID`),
  KEY `asdf_IX` (`asdf`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matatu_test`
--

LOCK TABLES `matatu_test` WRITE;
/*!40000 ALTER TABLE `matatu_test` DISABLE KEYS */;
/*!40000 ALTER TABLE `matatu_test` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-09-23  0:00:07
