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
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `cat_id_id` int DEFAULT NULL,
  `available` tinyint(1) NOT NULL,
  `amount` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_23A0E66C33F2EBA` (`cat_id_id`),
  CONSTRAINT `FK_23A0E66C33F2EBA` FOREIGN KEY (`cat_id_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article`
--

LOCK TABLES `article` WRITE;
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
INSERT INTO `article` VALUES (1,'Tričko Acidez','Tričko Acidez čierne pánske, 100% bavlna',9.9,'1e122fc319891b96dc60cdb1d271f1b9.jpeg',1,1,3,44,4.2),(2,'Tričko Načo Názov','Tričko Načo Názov červené pánske, 100% bavlna',7.9,'1941570d9e8a99bccf94065dcd9b2c00.jpeg',1,1,2,42,3),(3,'Tričko Conflict','Tričko Conflict čierne pánske, materiál 100% bavlna',7.9,'4f7d4edd7212547e209148dcfa2c849b.jpeg',1,1,4,0,0),(4,'Opasok vybíjaný','2.radový vybíjaný opasok s chrómovanými pyramídkami 1,6 x 1,6cm /šírka 4 cm/',19.9,'bf30939a2175d8be399531633a5f358a.jpeg',3,1,2,44,5),(5,'Obuv Dr.Martens','CR 1460z bez oceľovej špičky bordové 8 dierok',124.9,'c5e2a51a27d690612f02e810865e68a8.jpeg',2,1,1,44,3),(6,'Obuv Dr.Martens','kožené 3.dierkové poltopánky, čierne s oceľovou špicou 1925z',112.9,'4f589a8d400286cae16c94db0d39969d.jpeg',2,1,2,0,NULL),(7,'Obuv Steadys VEGAN','umelá koža, topánky 10 dierové čierne s prešívanou oceľovou špičkou',69,'4782938aeacdcf156755d70a38385cd5.jpeg',2,1,1,0,NULL),(8,'Bunda Harrington s kapucou','čierna bunda Harrington s kapucou s podšívkou červené káro TARTAN \"jar/jeseň',31.9,'1ff9a82ab3d5c1f5a48527227cae1954.jpeg',4,1,1,44,4),(9,'Bunda Bomber MA-1','čierna zimná letecká bunda BOMBER typu MA-1 z pevného materiálu s masívnym zipsom na zapínanie 100%nylón, čiastočne vodeodolná',34.9,'b545b5c994690b2e50e3b5be2ff3753b.jpeg',4,1,0,44,5),(10,'Bunda Bomber CWU','čierna zimná letecká bunda BOMBER s límcom, typ CWU z pevného materiálu s masívnym zipsom na zapínanie 100%nylón, čiastočne vodeodolná',42.9,'42afa2477bfc6b0949df8d6068748a19.jpeg',4,1,3,0,NULL),(11,'Bunda Sidovka','Sidovka, čierna koženková bunda Sida s čiernou!!! podšívkou - Křivák, materiál zvonku koženka - podšívka nylón',49.7,'4c1a9ecf8626d8adebf85bc2351922cf.jpeg',4,1,2,0,NULL),(12,'Čiapka Restarts','Restarts čierna pletená čiapka stredne hrubá vo vnútri naviac zateplená, univerzálna veľkosť, materiálové zloženie 100% akryl',9.9,'866d1e194a9dca94df575e279932293f.jpeg',5,1,2,44,5),(13,'Čiapka Casualties','Casualties Zimná čiapka s tlačeným logom univerzálna veľkosť 65%akryl 35%vlna',7.9,'77ef79be33ebee5bc5c5ab576e73158d.jpeg',5,1,9,44,3.5),(29,'Opasok The Exploited','Opasok s motívom EXPLOITED  Pevný textilný opasok',9.99,'ab88b3081839b0113af26ee71de47f72.jpg',3,1,10,44,NULL),(30,'Opasok Motorhead','Opasok s motívom MOTORHEAD  Pevný textilný opasok MOTORHEAD s kovovou prackou.',9.99,'f2c9e40e370484d11ec704f55759829d.jpg',3,1,10,44,NULL),(31,'Opasok Sex Pistols','hrubý látkový opasok so zapínaním na posuvnú kovovú pracku',8.9,'770d99feba94cadd7b43eb0987f072d2.jpg',3,1,15,44,NULL),(32,'Opasok Blink 182','hrubý čierny bavlnený opasok, kovová posuvná pracka s vyrazeným logom.',11.62,'d0964ee79b808a93d9ba3a76c8eaaad5.jpg',3,1,10,44,NULL),(33,'Obuv Dr. Martens 1461','poltopánky bez oceľovej špice, čierne',112.9,'83d66a08abf494f40cd9a793b3d705ea.jpg',2,1,10,44,NULL),(34,'Obuv T-REX 3.dierkové','3.dierkové bordové poltopánky s prešívanou oceľovou špičkou',59,'f55c2f1c4728d992c0322149c15c4167.jpg',2,1,5,44,NULL),(35,'Obuv T-REX 10.dierkové','10.dierkové čierne topánky z pravej kože najvyššej akosti',77,'353954ca5be584bacf1a2154bd0c6c65.jpg',2,1,15,44,NULL),(36,'Obuv Fred Perry','Unisex Tenisky',55,'857881fdb33425c26acd44d69bafd1a0.jpg',2,1,10,44,NULL),(37,'Obuv Tenisky','3/4ťové tenisky \"číny\" červené dámske 7.dierkové',9.9,'21ded74f04beffede7572f596c3ba53a.jpg',2,1,20,44,NULL),(38,'Obuv Steadys','Kožené topánky 10 dierové čierne s prešívanou oceľovou špičkou',64,'978fd508c480a7c35c7f6f5d09c6f834.jpg',2,1,8,44,NULL),(39,'Nohavice Škótske káro pánske','100%bavlna obvod pásu 76cm a 80cm',38.9,'e8f3944a60ed6c9d68dc193d460fed1b.jpg',8,1,25,44,NULL),(40,'Nohavice Škótske káro TARTAN','pánske aj dámske 100%bavlna',38.9,'bb4d0e9281583545b87680b89524c659.jpg',8,1,30,44,NULL),(41,'Nohavice škótske káro red/black','Nohavice škótske káro red/black',48.13,'5a38357693bc72713e3e06b1700a449d.jpg',8,1,10,44,NULL),(42,'Nohavice BDU','65%bavlna 35%polyester',17.9,'d2e3ea7aa038801035ed136062ff0c41.jpg',8,1,10,44,NULL),(43,'Nohavice kraťasové','3/4ťové kraťasové nohavice Škótske káro TARTAN',39.9,'41dc752118842d6cea77f19d1556d9ea.jpg',8,1,20,44,NULL),(44,'Tielko Black Flag','Tielko Black Flag',7.9,'58e17209d2c9a4e109ec6ca2ee5008c6.jpg',6,1,20,44,NULL),(45,'Tielko Adicts','Adicts čierne pánske tielko 100%bavlna',7.9,'20c88dcbb682e14b613097e70445fd31.jpg',6,1,50,44,NULL),(46,'Tielko Casualties','Tielko Casualties',7.95,'6b54ca9be0145ee04a3072aaa9ee9c7a.jpg',6,1,5,44,NULL),(47,'Tielko Anti Nowhere League','Anti Nowhere League čierne tielko 100%bavlna',7.9,'45516ce27a277dde43b494084a35dc94.jpg',6,1,25,44,NULL),(48,'Tielko Defiance','Defiance čierne tielko 100% bavlna',7.9,'09b334e4a9b194231873f8109877a00f.jpg',6,1,15,44,NULL),(49,'Tielko Antidote','Antidote čierne tielko 100%bavlna',8.9,'60641d1d46d308a2ed4bb352a0f67da4.jpg',6,1,13,44,NULL),(50,'Tielko Načo Názov','Načo Názov - Fucking lies',7.9,'2d83940bf1433d3d0adfb17fc6f6945a.jpg',6,1,15,44,NULL),(51,'Tielko Načo Názov','Načo Názov - Myslieť nadovšetko čierne tielko materiál 100% bavlna',7.9,'a7e6f34aa19db23dcaed45172d9bd47a.jpg',6,1,10,44,NULL),(52,'Exploited Zimná čiapka','s tlačeným logom univerzálna veľkosť 65%akryl 35%vlna',8.5,'d8f035754679a416a1349d1edeeed1bb.jpg',5,1,25,44,NULL),(53,'Načo Názov červená zimná čiapka','s tlačeným logom 100% akryl',10.95,'eb92b40f70f8165289548ed5e165a4ca.jpg',5,1,50,44,NULL),(54,'Čiapka Rude Boy','SKA čiernobiela 100% bavlna',9.9,'5cd33a5f0ee8e268d0d0286739d7d2a0.jpg',5,1,15,44,NULL),(55,'Čiapka Šiltovka US','púštny maskáč 100% bavlna',5.3,'0c9af466a4b9f9e3f760376cb0c3bae2.jpg',5,1,20,44,NULL),(56,'Čiapka Šiltovka SKA','šachovnica čiernobiela šiltovka s old school plastovou sieťkou vzadu',7.3,'c2a295b6ac210bd9d4d9ccd0fa0b9097.jpg',5,1,10,44,NULL),(57,'Odznak Stop zbraniam','odznak veľký, priemer 55mm',1.16,'8e76f6a086916a60aa5f873a8d3765f3.jpg',7,1,50,44,NULL),(58,'Odznak NOFX','odznak veľký, priemer 55mm',1.16,'5d0aac548b0316aeb7d425109b30857f.jpg',7,1,55,44,NULL),(59,'Odznak The Clash','The Clash odznak veľký, priemer 55mm',1.16,'8999f822bc99d78eeba6de0a4eee6f81.jpg',7,1,100,44,NULL),(60,'Odznak Konflikt','Konflikt odznak veľký, priemer 55mm',1.16,'fdc3ab6cd87613458b3b0a745e7666d0.jpg',7,1,55,44,NULL),(61,'Odznak DRI','D.R.I. odznak veľký, priemer 55mm',1.16,'2f7e16327646b3fc0c300a5d0037f8a5.jpg',7,1,45,44,NULL),(62,'Odznak Breeds Trash Antifascist','odznak veľký, priemer 55mm',1.16,'2ab862727f85935b36897f4b39a2e627.jpg',7,1,35,44,NULL),(63,'Odznak The Exploited','odznak veľký, priemer 55mm',1.16,'e0e53da8a9e1fdead7b7946ecad796f2.jpg',7,1,29,44,NULL),(64,'Odznak Gegen Nazis','odznak veľký, priemer 55mm',1.16,'25403a686f64fd40d256b63fad66e7ee.jpg',7,1,39,44,NULL),(65,'Odznak Dead Kennedys','odznak veľký, priemer 55mm',1.16,'e9dc6c516116649aa42133e46352770f.jpg',7,1,31,44,NULL),(66,'Odznak Dead Kennedys','odznak veľký, priemer 55mm',1.16,'fb54cb61e4195ada973b6d4a87cb0ac9.jpg',7,1,21,44,NULL),(67,'Tričko U.K. Subs','100% bavlna',7.9,'12203be4054fb07d6285b563dc25531b.jpg',1,1,10,44,NULL),(68,'The Clash čierne pánske tričko','100% bavlna',8.95,'1748173ffacc9d1fa5dc583075c8e277.jpg',1,1,25,44,NULL),(69,'Konflikt čierne pánske tričko','100% bavlna',14.7,'2b0f430b628cf1e2143a1a1a3ed4b3af.jpg',1,1,25,44,NULL),(70,'Agnostic Front čierne pánske tričko','100% bavlna',7.9,'e5d11389f3f797e6744f3ae170b80a30.jpg',1,1,15,44,NULL),(71,'Dropkick Murphys bielozelené pánske tričko','100% bavlna',10.9,'27f89ab0aaa0e133173214f454f66142.jpg',1,1,12,44,NULL),(72,'NOFX čierne pánske tričko','100% bavlna',9.8,'c5431f0c0d7083fb5dc3b55ca18bf0ea.jpg',1,1,13,44,NULL),(73,'Odpad čierne pánske tričko','100% bavlna',11.9,'54b5688edfece4e0a6afc3823de45f38.jpg',1,1,23,44,NULL),(74,'One Way System čierne pánske tričko','100% bavlna',7.9,'b2ba14beb597f33304c8cc057c4e1e70.jpg',1,1,15,44,NULL),(75,'TELEX šedé pánske tričko materiál','100% bavlna',12.9,'4a693e61f5968be068a4da9f830c8dc6.jpg',1,1,22,44,NULL),(76,'Minor Threat pánske tričko materiál','100% bavlna',8.9,'49a51206628ad12fc29260f931b84e1a.jpg',1,1,102,44,NULL),(77,'Rancid červené pánske tričko materiál','100% bavlna',8.9,'ad400444205be6999f65949ef2929615.jpg',1,1,19,44,NULL);
/*!40000 ALTER TABLE `article` ENABLE KEYS */;
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
