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
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (1,'2025-01-01-000001','App\\Database\\Migrations\\CreateRolesTable','default','App',1781228370,1),(2,'2025-01-01-000002','App\\Database\\Migrations\\CreatePermissionsTable','default','App',1781228370,1),(3,'2025-01-01-000003','App\\Database\\Migrations\\CreateRolePermissionsTable','default','App',1781228370,1),(4,'2025-01-01-000004','App\\Database\\Migrations\\CreateUsersTable','default','App',1781228370,1),(5,'2025-01-01-000005','App\\Database\\Migrations\\CreateElectionsTable','default','App',1781228370,1),(6,'2025-01-01-000006','App\\Database\\Migrations\\CreateDignitiesTable','default','App',1781228370,1),(7,'2025-01-01-000007','App\\Database\\Migrations\\CreatePoliticalPartiesTable','default','App',1781228370,1),(8,'2025-01-01-000008','App\\Database\\Migrations\\CreateCandidateListsTable','default','App',1781228370,1),(9,'2025-01-01-000009','App\\Database\\Migrations\\CreateCandidatesTable','default','App',1781228370,1),(10,'2025-01-01-000010','App\\Database\\Migrations\\CreateProvincesTable','default','App',1781228370,1),(11,'2025-01-01-000011','App\\Database\\Migrations\\CreateCantonsTable','default','App',1781228370,1),(12,'2025-01-01-000012','App\\Database\\Migrations\\CreateParishesTable','default','App',1781228370,1),(13,'2025-01-01-000013','App\\Database\\Migrations\\CreatePrecinctsTable','default','App',1781228370,1),(14,'2025-01-01-000014','App\\Database\\Migrations\\CreatePollingStationsTable','default','App',1781228370,1),(15,'2025-01-01-000015','App\\Database\\Migrations\\CreateAssignmentsTable','default','App',1781228370,1),(16,'2025-01-01-000016','App\\Database\\Migrations\\CreateElectionResultsTable','default','App',1781228370,1),(17,'2025-01-01-000017','App\\Database\\Migrations\\CreateUploadedMinutesTable','default','App',1781228370,1),(18,'2025-01-01-000018','App\\Database\\Migrations\\CreateAuditsTable','default','App',1781228370,1),(19,'2025-01-01-000019','App\\Database\\Migrations\\CreateRefreshTokensTable','default','App',1781228370,1),(20,'2025-06-11-200001','App\\Database\\Migrations\\FixCandidatesNullable','default','App',1781231650,2),(21,'2025-06-11-200002','App\\Database\\Migrations\\FixPollingStationsGender','default','App',1781232016,3),(22,'2025-06-11-200003','App\\Database\\Migrations\\AddAsignadaStatus','default','App',1792974931,4),(23,'2026-10-25-000001','App\\Database\\Migrations\\CreateElectionDignitiesTable','default','App',1792974931,4),(24,'2026-10-25-000002','App\\Database\\Migrations\\AddDignityAndStatusToUploadedMinutes','default','App',1792974931,4),(25,'2026-10-25-000003','App\\Database\\Migrations\\CreateResultVersionsTable','default','App',1792974931,4),(26,'2026-10-25-000004','App\\Database\\Migrations\\SeedElectionDignities','default','App',1792974931,4),(27,'2026-10-25-000005','App\\Database\\Migrations\\CreateSupervisorRole','default','App',1792974931,4),(29,'2026-10-25-000006','App\\Database\\Migrations\\AddDashboardIndexes','default','App',1792982837,5),(30,'2026-11-08-000001','App\\Database\\Migrations\\CreateElectionSeatsTable','default','App',1794176131,6),(31,'2026-11-08-000002','App\\Database\\Migrations\\AddCantonAndPositionForConcejales','default','App',1794186202,7),(32,'2026-11-08-000003','App\\Database\\Migrations\\CreateElectionListVotesTable','default','App',1794186202,7),(33,'2026-11-08-000004','App\\Database\\Migrations\\CreateElectionListResultsTable','default','App',1794187709,8),(34,'2026-11-08-000005','App\\Database\\Migrations\\EnableConcejalElectionDignities','default','App',1794187768,9),(35,'2026-11-08-000006','App\\Database\\Migrations\\AddCantonToCandidates','default','App',1794189207,10),(36,'2026-11-08-000007','App\\Database\\Migrations\\BackfillAlcaldeCantonFromResults','default','App',1794192912,11),(37,'2026-11-08-000008','App\\Database\\Migrations\\EnableCandidateDignities','default','App',1794194082,12),(38,'2026-11-12-000001','App\\Database\\Migrations\\CreateElectionCircumscriptionsTable','default','App',1794534969,13),(39,'2026-11-12-000002','App\\Database\\Migrations\\CreateElectionCircumscriptionParishesTable','default','App',1794534969,13),(40,'2026-11-12-000003','App\\Database\\Migrations\\AddCircumscriptionToConcejalTables','default','App',1794535017,14),(41,'2026-11-12-000004','App\\Database\\Migrations\\SeedEsmeraldasCircumscriptions','default','App',1794535017,14),(42,'2026-11-12-000005','App\\Database\\Migrations\\BackfillListResultCircumscriptions','default','App',1794536405,15),(43,'2026-11-25-000001','App\\Database\\Migrations\\CreateUserSessionsTable','default','App',1795654190,16),(44,'2026-11-25-000002','App\\Database\\Migrations\\AddSessionToRefreshTokens','default','App',1795654190,16);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
