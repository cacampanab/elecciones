-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: elecciones
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Dumping data for table `dignities`
--

LOCK TABLES `dignities` WRITE;
/*!40000 ALTER TABLE `dignities` DISABLE KEYS */;
INSERT INTO `dignities` (`id`, `name`, `slug`, `description`, `level`, `is_active`, `created_at`, `updated_at`) VALUES (1,'Presidente','presidente','Presidente de la República','nacional',1,'2026-06-12 01:40:09',NULL),(2,'Asambleísta Nacional','asambleista-nacional','Asambleísta Nacional','nacional',1,'2026-06-12 01:40:09',NULL),(3,'Prefecto','prefecto','Prefecto Provincial','provincial',1,'2026-06-12 01:40:09',NULL),(4,'Alcalde','alcalde','Alcalde Municipal','cantonal',1,'2026-06-12 01:40:09','2026-11-08 20:43:41'),(5,'Concejal Urbano','concejal-urbano','Concejal Urbano','cantonal',1,'2026-06-12 01:40:09',NULL),(6,'Concejal Rural','concejal-rural','Concejal Rural','cantonal',1,'2026-06-12 01:40:09',NULL),(7,'Vocal Junta Parroquial','vocal-junta-parroquial','Vocal de Junta Parroquial','parroquial',1,'2026-06-12 01:40:09',NULL);
/*!40000 ALTER TABLE `dignities` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-26  6:31:53
