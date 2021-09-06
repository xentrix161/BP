-- MySQL dump 10.13  Distrib 8.0.22, for Win64 (x86_64)
--
-- Host: localhost    Database: mydb
-- ------------------------------------------------------
-- Server version	8.0.22

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rating`
--

DROP TABLE IF EXISTS `rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rating` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL,
  `rate` int NOT NULL,
  `entity_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rating`
--

LOCK TABLES `rating` WRITE;
/*!40000 ALTER TABLE `rating` DISABLE KEYS */;
INSERT INTO `rating` VALUES (12,44,'2021-03-08 00:07:14',1,'TYPE_ARTICLE',1),(13,86,'2021-04-08 00:15:02',5,'TYPE_ARTICLE',1),(14,89,'2021-04-08 00:16:11',5,'TYPE_ARTICLE',1),(15,44,'2021-04-08 00:18:09',5,'TYPE_ARTICLE',1),(16,44,'2021-04-08 00:30:35',5,'TYPE_ARTICLE',2),(17,44,'2021-04-08 00:31:35',5,'TYPE_ARTICLE',4),(18,44,'2021-04-08 00:34:56',3,'TYPE_ARTICLE',24),(19,44,'2021-04-13 15:29:28',4,'TYPE_ARTICLE',8),(20,44,'2021-04-13 15:30:22',1,'TYPE_ARTICLE',5),(21,91,'2021-04-14 00:59:43',5,'TYPE_ARTICLE',1),(22,91,'2021-04-14 01:03:22',1,'TYPE_ARTICLE',2),(26,94,'2021-04-17 23:28:16',5,'TYPE_ARTICLE',5),(27,44,'2021-04-18 00:44:40',5,'TYPE_ARTICLE',13),(30,44,'2021-04-18 00:49:50',5,'TYPE_ARTICLE',12),(31,44,'2021-05-01 15:11:39',5,'TYPE_ARTICLE',9);
/*!40000 ALTER TABLE `rating` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-09-06 20:01:36
