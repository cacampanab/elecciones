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
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
INSERT INTO `provinces` (`id`, `code`, `name`, `capital`, `region`, `population`, `is_active`, `created_at`, `updated_at`) VALUES (1,'01','Azuay','Cuenca','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(2,'02','Bolívar','Guaranda','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(3,'03','Cañar','Azogues','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(4,'04','Carchi','Tulcán','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(5,'05','Cotopaxi','Latacunga','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(6,'06','Chimborazo','Riobamba','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(7,'07','El Oro','Machala','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(8,'08','Esmeraldas','Esmeraldas','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(9,'09','Guayas','Guayaquil','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(10,'10','Imbabura','Ibarra','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(11,'11','Loja','Loja','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(12,'12','Los Ríos','Babahoyo','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(13,'13','Manabí','Portoviejo','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(14,'14','Morona Santiago','Macas','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(15,'15','Napo','Tena','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(16,'16','Pastaza','Puyo','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(17,'17','Pichincha','Quito','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(18,'18','Tungurahua','Ambato','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(19,'19','Zamora Chinchipe','Zamora','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(20,'20','Galápagos','Puerto Baquerizo Moreno','Insular',NULL,1,'2026-06-12 01:40:09',NULL),(21,'21','Sucumbíos','Nueva Loja','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(22,'22','Orellana','Francisco de Orellana','Amazonía',NULL,1,'2026-06-12 01:40:09',NULL),(23,'23','Santo Domingo de los Tsáchilas','Santo Domingo','Sierra',NULL,1,'2026-06-12 01:40:09',NULL),(24,'24','Santa Elena','Santa Elena','Costa',NULL,1,'2026-06-12 01:40:09',NULL),(25,'90','Zonas no Delimitadas','N/A','N/A',NULL,1,'2026-06-12 01:40:09',NULL),(26,'8','ESMERALDAS',NULL,NULL,NULL,1,'2026-06-11 21:27:36','2026-06-11 21:27:36');
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
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
