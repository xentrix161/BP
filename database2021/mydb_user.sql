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
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` json NOT NULL,
  `earning` double DEFAULT NULL,
  `expense` double DEFAULT NULL,
  `rating` double DEFAULT NULL,
  `activate` tinyint(1) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (42,'testovy','testovy','testovy@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$MjFqUVhMV3piVGdWMUVVYQ$zqC0pM/Obt9HHypevHepWUg16D+J5jv98qhvaGGcKOE','[\"ROLE_ADMIN\"]',29.03865,1000,3,0,NULL,'0000-00-00 00:00:00'),(43,'ha','ha','ha@ha.com','$argon2id$v=19$m=65536,t=4,p=1$azhBLjlwYUFtZlozTjl1VA$Zt9PKdqA9ESXIll2o6HtM7Us6JJfP4ijJV+8mBtTw7U','[\"ROLE_ADMIN\"]',2,500,NULL,1,'activated','2021-03-30 04:38:37'),(44,'Filip','Kosmeľ','filipkosmel@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$Nk5ubjk5V3RRb2hMOFVnRQ$BRO/fn32RoCEc1xlZi94PEPb4XbBmVsfHt+pYoEJXwE','[\"ROLE_ADMIN\"]',483.23315843586,695.55,4.2428571428571,1,'login','2021-03-28 00:04:12'),(84,'aaa','aaa','aaa@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$T2MvOWpDQ1hsd1ViWDFqMQ$zzmV+WIJD41NNipZLl2RKkANebhhdbKCJp8q7JIMjnE','[\"ROLE_NONE\"]',NULL,NULL,NULL,0,'e41eaec24eaaa4c280e720cf16b50bb2','2021-03-31 22:11:50'),(85,'dasdasd','asafasf','novyyy@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$ak93TVd6c2kvUzNwR2diaA$KGvtgmtWJ/SR9gp/iL3h1vM6uKGWbnrAiD98wcNA5uE','[\"ROLE_NONE\"]',NULL,NULL,NULL,0,'1bc0c8b03075c33e994dc41301c03512','2021-03-31 22:13:14'),(86,'novyDavajMaria','novyDavajJanko','davajkacaj@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$T1hMbEVUTFRmeGt6YTZEZA$wcFZ0cV2+3utkMG3kSdsUJa4EmTvO/llujS5Sshbzo0','[\"ROLE_USER\"]',NULL,NULL,NULL,1,'activated','2021-04-04 23:41:43'),(87,'asfasf','asfasfa','fafl@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$Z04xMVl4NGNyaXBmalFlNg$Ihm1YaY/6FSByhdZedVCm7+BnmcP1UYaNILSlUde0eU','[\"ROLE_NONE\"]',NULL,NULL,NULL,1,'activated','2021-04-04 23:50:18'),(88,'novy','asfa','novy3@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$ZWpzdlA1N2locldacC5OVw$lsdCNP0dbuaUK4PKSm7Db0QvQnWAlrmbNbwpoSM/yMA','[\"ROLE_NONE\"]',NULL,NULL,NULL,1,'activated','2021-04-04 23:52:00'),(89,'asas','asfa','novy4@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$bGhlOUU3TXJYMkFmME5idA$lUt4/o71y4Y7CnyWKMjCm0QNDz9rOvUbYmDkGLNfKss','[\"ROLE_USER\"]',NULL,NULL,NULL,1,'activated','2021-04-05 02:35:56'),(90,'asas','asfa','vratnaddy@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$MXRDUjVzSEVjazMvRi5DYw$hSTjOgyxVKvI685ombp1PahyFjA3dgai+i4iJCvFZNo','[\"ROLE_NONE\"]',NULL,NULL,NULL,1,'activated','2021-04-05 03:18:12');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
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
