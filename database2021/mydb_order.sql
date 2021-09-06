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
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `total_price` double NOT NULL,
  `invoice_number` int NOT NULL,
  `payment_method` tinyint(1) NOT NULL,
  `paid` tinyint(1) NOT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
INSERT INTO `order` VALUES (1,'2016-01-01 01:04:00','Na Sihoti',1,10,1,1,1,'+421949732916'),(2,'2021-03-17 16:46:21','Adresa objednávky',42,19.8,100,1,0,''),(3,'2021-03-17 16:46:34','Adresa objednávky',42,19.8,100,1,1,''),(4,'2021-03-17 16:46:45','Adresa objednávky',42,19.8,100,1,0,''),(5,'2021-03-17 16:48:08','Adresa objednávky',42,19.8,100,1,1,''),(6,'2021-03-17 16:48:33','Adresa objednávky',44,99,100,1,0,''),(7,'2021-03-17 18:06:25','Adresa objednávky',44,99,100,1,1,''),(8,'2021-03-17 18:07:48','Adresa objednávky',44,99,100,1,1,''),(9,'2021-03-17 18:11:07','Adresa objednávky',44,99,100,1,0,''),(10,'2021-03-17 18:11:44','Adresa objednávky',44,99,100,1,1,''),(11,'2021-03-17 18:14:23','Adresa objednávky',44,99,100,1,1,''),(12,'2021-03-17 18:25:47','Adresa objednávky',44,89.1,100,1,1,''),(13,'2021-03-17 18:53:45','Adresa objednávky',44,79.2,100,1,1,''),(14,'2021-03-17 18:55:19','Adresa objednávky',44,79.2,100,1,1,''),(15,'2021-03-17 19:05:25','Adresa objednávky',44,79.2,100,1,0,''),(16,'2021-03-17 19:15:46','Adresa objednávky',44,79.2,100,1,1,''),(17,'2021-03-17 19:17:10','Adresa objednávky',44,229.8,100,1,0,''),(18,'2021-03-17 19:41:31','Adresa objednávky',44,247.6,100,1,0,''),(19,'2021-03-20 17:49:09','Adresa objednávky',44,247.6,100,1,0,''),(20,'2021-03-20 17:50:10','Adresa objednávky',44,247.6,100,1,1,''),(21,'2021-03-20 17:52:38','Adresa objednávky',44,247.6,100,1,1,''),(22,'2021-03-20 17:52:59','Adresa objednávky',44,247.6,100,1,0,''),(23,'2021-03-20 17:53:32','Adresa objednávky',44,247.6,100,1,1,''),(24,'2021-03-20 17:53:36','Adresa objednávky',44,247.6,100,1,0,''),(25,'2021-03-20 17:54:53','Adresa objednávky',44,247.6,100,1,1,''),(26,'2021-03-20 17:55:01','Adresa objednávky',44,247.6,100,1,0,''),(27,'2021-03-20 17:56:21','Adresa objednávky',44,247.6,100,1,1,''),(28,'2021-03-20 17:56:49','Adresa objednávky',44,247.6,100,1,0,''),(29,'2021-03-20 18:00:20','Adresa objednávky',44,247.6,100,1,1,''),(30,'2021-03-20 18:01:18','Adresa objednávky',44,247.6,100,1,1,''),(31,'2021-03-20 18:01:43','Adresa objednávky',44,247.6,100,1,0,''),(32,'2021-03-20 18:02:28','Adresa objednávky',44,247.6,100,1,0,''),(33,'2021-03-20 18:05:04','Adresa objednávky',44,247.6,100,1,1,''),(34,'2021-03-20 18:05:29','Adresa objednávky',44,247.6,100,1,0,''),(35,'2021-03-20 18:05:34','Adresa objednávky',44,247.6,100,1,0,''),(36,'2021-03-20 18:06:48','Adresa objednávky',44,247.6,100,1,0,''),(37,'2021-03-20 18:07:14','Adresa objednávky',44,247.6,100,1,1,''),(38,'2021-03-20 18:08:02','Adresa objednávky',44,247.6,100,1,1,''),(39,'2021-03-20 18:08:58','Adresa objednávky',44,247.6,100,1,0,''),(40,'2021-03-20 18:09:14','Adresa objednávky',44,247.6,100,1,1,''),(41,'2021-03-20 18:09:45','Adresa objednávky',44,247.6,100,1,1,''),(42,'2021-03-20 18:10:07','Adresa objednávky',44,247.6,100,1,1,''),(43,'2021-03-20 18:10:24','Adresa objednávky',44,247.6,100,1,1,''),(44,'2021-03-20 18:10:45','Adresa objednávky',44,247.6,100,1,0,''),(45,'2021-03-20 18:10:58','Adresa objednávky',44,247.6,100,1,1,''),(46,'2021-03-20 18:11:40','Adresa objednávky',44,247.6,100,1,0,''),(47,'2021-03-20 18:11:58','Adresa objednávky',44,247.6,100,1,0,''),(48,'2021-03-20 18:12:41','Adresa objednávky',44,247.6,100,1,1,''),(49,'2021-03-20 18:13:55','Adresa objednávky',44,247.6,100,1,1,''),(50,'2021-03-20 18:14:01','Adresa objednávky',44,247.6,100,1,1,''),(51,'2021-03-20 18:14:58','Adresa objednávky',44,247.6,100,1,0,''),(52,'2021-03-20 18:15:13','Adresa objednávky',44,247.6,100,1,1,''),(53,'2021-03-20 18:22:57','bbbbbbbb',44,247.6,100,1,1,''),(54,'2021-03-20 18:48:29','bbbbbbbb, 02601, bbbbbbbbb',44,247.6,21030001,1,1,''),(55,'2021-03-20 18:49:46','Na Sihoti 1162/29, 02601, Dolný Kubín',44,36.6,21030002,1,1,''),(56,'2021-03-20 18:50:54','Na Sihoti 555/18, 01165, Žilina',44,36.6,21020003,0,1,''),(57,'2021-03-20 18:52:37','Na Sihoti 555/18, 02601, DK',44,36.6,21030001,1,0,''),(58,'2021-03-22 22:21:54','Na Sihoti 1162/29, 02601, DK',44,36.6,21030003,0,1,''),(59,'2021-03-27 16:40:55','Na Sihoti 1162/29, 02601, Dolný Kubín',44,99,21030004,0,0,'0949732916'),(60,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',44,338.8,21030005,0,0,'5546546446'),(61,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',66,338.8,21030006,0,1,'5546546446'),(62,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',66,338.8,21030007,0,1,'5546546446'),(63,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',66,338.8,21030008,0,1,'5546546446'),(64,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',75,338.8,21030009,1,1,'5546546446'),(65,'2021-03-27 22:30:21','Na Sihoti 1162/29, 02601, DK',75,338.8,21030010,1,1,'5546546446'),(66,'2021-03-30 04:14:28','Doma',44,50,21030011,1,1,'111111111111111111111'),(67,'2021-04-05 03:34:21','Na Sihoti',44,50,21040001,1,1,'+421949732916'),(68,'2021-04-05 07:17:17','Na Sihoti +1162/29, 02601555, DK5665',44,7.9,21040002,0,0,'5546546446'),(69,'2021-04-05 07:21:07','Na Sihoti +1162/29, 02601555, DK5665',44,0,21040003,0,1,'5546546446'),(70,'2021-04-05 07:40:40','Na Sihoti 1162/29, 02601, Dolný Kubín',44,7.9,21040004,1,0,'+421949732916'),(71,'2021-04-05 07:42:04','Na Sihoti 1162/29, 02601, Dolný Kubín',44,9.9,21040005,0,0,'+421949732916'),(72,'2021-04-05 07:42:44','Na Sihoti 1162/29, 02601, Dolný Kubín',44,9.9,21040006,0,0,'+421949732916'),(73,'2021-04-11 02:40:34','Na Sihoti 1162/29, 02601, DK',44,134.8,21040007,0,0,'5546546446'),(74,'2021-04-17 23:08:19','Na Sihoti 1162/29, 02601, Dolný Kubín',44,9.9,21040008,1,1,'+421949732916'),(75,'2021-06-03 18:11:38','Na Sihoti 1162/29, 02601, Dolný Kubín',44,82.4,21060001,0,1,'+421949732916');
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-09-06 20:01:37
