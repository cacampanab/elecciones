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
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (1,'users.create','Crear usuarios','2026-06-12 01:40:09',NULL),(2,'users.read','Ver usuarios','2026-06-12 01:40:09',NULL),(3,'users.update','Editar usuarios','2026-06-12 01:40:09',NULL),(4,'users.delete','Eliminar usuarios','2026-06-12 01:40:09',NULL),(5,'roles.create','Crear roles','2026-06-12 01:40:09',NULL),(6,'roles.read','Ver roles','2026-06-12 01:40:09',NULL),(7,'roles.update','Editar roles','2026-06-12 01:40:09',NULL),(8,'roles.delete','Eliminar roles','2026-06-12 01:40:09',NULL),(9,'elections.create','Crear elecciones','2026-06-12 01:40:09',NULL),(10,'elections.read','Ver elecciones','2026-06-12 01:40:09',NULL),(11,'elections.update','Editar elecciones','2026-06-12 01:40:09',NULL),(12,'elections.delete','Eliminar elecciones','2026-06-12 01:40:09',NULL),(13,'dignities.create','Crear dignidades','2026-06-12 01:40:09',NULL),(14,'dignities.read','Ver dignidades','2026-06-12 01:40:09',NULL),(15,'dignities.update','Editar dignidades','2026-06-12 01:40:09',NULL),(16,'dignities.delete','Eliminar dignidades','2026-06-12 01:40:09',NULL),(17,'parties.create','Crear partidos','2026-06-12 01:40:09',NULL),(18,'parties.read','Ver partidos','2026-06-12 01:40:09',NULL),(19,'parties.update','Editar partidos','2026-06-12 01:40:09',NULL),(20,'parties.delete','Eliminar partidos','2026-06-12 01:40:09',NULL),(21,'lists.create','Crear listas','2026-06-12 01:40:09',NULL),(22,'lists.read','Ver listas','2026-06-12 01:40:09',NULL),(23,'lists.update','Editar listas','2026-06-12 01:40:09',NULL),(24,'lists.delete','Eliminar listas','2026-06-12 01:40:09',NULL),(25,'candidates.create','Crear candidatos','2026-06-12 01:40:09',NULL),(26,'candidates.read','Ver candidatos','2026-06-12 01:40:09',NULL),(27,'candidates.update','Editar candidatos','2026-06-12 01:40:09',NULL),(28,'candidates.delete','Eliminar candidatos','2026-06-12 01:40:09',NULL),(29,'provinces.create','Crear provincias','2026-06-12 01:40:09',NULL),(30,'provinces.read','Ver provincias','2026-06-12 01:40:09',NULL),(31,'provinces.update','Editar provincias','2026-06-12 01:40:09',NULL),(32,'provinces.delete','Eliminar provincias','2026-06-12 01:40:09',NULL),(33,'cantons.create','Crear cantones','2026-06-12 01:40:09',NULL),(34,'cantons.read','Ver cantones','2026-06-12 01:40:09',NULL),(35,'cantons.update','Editar cantones','2026-06-12 01:40:09',NULL),(36,'cantons.delete','Eliminar cantones','2026-06-12 01:40:09',NULL),(37,'parishes.create','Crear parroquias','2026-06-12 01:40:09',NULL),(38,'parishes.read','Ver parroquias','2026-06-12 01:40:09',NULL),(39,'parishes.update','Editar parroquias','2026-06-12 01:40:09',NULL),(40,'parishes.delete','Eliminar parroquias','2026-06-12 01:40:09',NULL),(41,'precincts.create','Crear recintos','2026-06-12 01:40:09',NULL),(42,'precincts.read','Ver recintos','2026-06-12 01:40:09',NULL),(43,'precincts.update','Editar recintos','2026-06-12 01:40:09',NULL),(44,'precincts.delete','Eliminar recintos','2026-06-12 01:40:09',NULL),(45,'polling_stations.create','Crear JRV','2026-06-12 01:40:09',NULL),(46,'polling_stations.read','Ver JRV','2026-06-12 01:40:09',NULL),(47,'polling_stations.update','Editar JRV','2026-06-12 01:40:09',NULL),(48,'polling_stations.delete','Eliminar JRV','2026-06-12 01:40:09',NULL),(49,'polling_stations.import','Importar JRV desde Excel','2026-06-12 01:40:09',NULL),(50,'assignments.create','Asignar usuarios','2026-06-12 01:40:09',NULL),(51,'assignments.read','Ver asignaciones','2026-06-12 01:40:09',NULL),(52,'assignments.update','Editar asignaciones','2026-06-12 01:40:09',NULL),(53,'assignments.delete','Eliminar asignaciones','2026-06-12 01:40:09',NULL),(54,'results.create','Registrar resultados','2026-06-12 01:40:09',NULL),(55,'results.read','Ver resultados','2026-06-12 01:40:09',NULL),(56,'results.update','Editar resultados','2026-06-12 01:40:09',NULL),(57,'results.verify','Verificar resultados','2026-06-12 01:40:09',NULL),(58,'results.delete','Eliminar resultados','2026-06-12 01:40:09',NULL),(59,'minutes.upload','Subir actas','2026-06-12 01:40:09',NULL),(60,'minutes.read','Ver actas','2026-06-12 01:40:09',NULL),(61,'minutes.delete','Eliminar actas','2026-06-12 01:40:09',NULL),(62,'reports.generate','Generar reportes','2026-06-12 01:40:09',NULL),(63,'reports.export_pdf','Exportar PDF','2026-06-12 01:40:09',NULL),(64,'reports.export_excel','Exportar Excel','2026-06-12 01:40:09',NULL),(65,'dashboard.read','Ver dashboard','2026-06-12 01:40:09',NULL),(66,'dashboard.statistics','Ver estadísticas','2026-06-12 01:40:09',NULL),(67,'audit.read','Ver auditoría','2026-06-12 01:40:09',NULL),(68,'config.read','Ver configuración','2026-06-12 01:40:09',NULL),(69,'config.update','Actualizar configuración','2026-06-12 01:40:09',NULL);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
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
