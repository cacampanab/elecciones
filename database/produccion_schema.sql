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
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `polling_station_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `assignment_type` enum('operador','coordinador','supervisor') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'operador',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_user_id_foreign` (`user_id`),
  KEY `assignments_polling_station_id_foreign` (`polling_station_id`),
  KEY `assignments_election_id_foreign` (`election_id`),
  KEY `assignments_created_by_foreign` (`created_by`),
  CONSTRAINT `assignments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assignments_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assignments_polling_station_id_foreign` FOREIGN KEY (`polling_station_id`) REFERENCES `polling_stations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audits`
--

DROP TABLE IF EXISTS `audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audits_user_id_foreign` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=319 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidate_lists`
--

DROP TABLE IF EXISTS `candidate_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `political_party_id` int unsigned NOT NULL,
  `canton_id` int unsigned DEFAULT NULL,
  `circumscription_id` int unsigned DEFAULT NULL,
  `dignity_id` int unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_candidate_lists_scope` (`election_id`,`canton_id`,`dignity_id`,`circumscription_id`,`political_party_id`),
  KEY `candidate_lists_political_party_id_foreign` (`political_party_id`),
  KEY `candidate_lists_dignity_id_foreign` (`dignity_id`),
  KEY `fk_candidate_lists_canton` (`canton_id`),
  KEY `fk_candidate_lists_circumscription` (`circumscription_id`),
  CONSTRAINT `candidate_lists_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `candidate_lists_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `candidate_lists_political_party_id_foreign` FOREIGN KEY (`political_party_id`) REFERENCES `political_parties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_lists_canton` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_lists_circumscription` FOREIGN KEY (`circumscription_id`) REFERENCES `election_circumscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `cedula` varchar(13) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dignity_id` int unsigned NOT NULL,
  `political_party_id` int unsigned NOT NULL,
  `candidate_list_id` int unsigned DEFAULT NULL,
  `position` int unsigned DEFAULT NULL,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `uq_candidates_list_position` (`candidate_list_id`,`position`),
  KEY `candidates_dignity_id_foreign` (`dignity_id`),
  KEY `candidates_political_party_id_foreign` (`political_party_id`),
  KEY `fk_candidates_canton` (`canton_id`),
  KEY `idx_candidates_election_dignity_canton` (`election_id`,`dignity_id`,`canton_id`),
  CONSTRAINT `candidates_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `candidates_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `candidates_political_party_id_foreign` FOREIGN KEY (`political_party_id`) REFERENCES `political_parties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidates_canton` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cantons`
--

DROP TABLE IF EXISTS `cantons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cantons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `province_id` int unsigned NOT NULL,
  `code` varchar(4) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cantons_province_id_foreign` (`province_id`),
  CONSTRAINT `cantons_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dignities`
--

DROP TABLE IF EXISTS `dignities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dignities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` enum('nacional','provincial','cantonal','parroquial') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'nacional',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_circumscription_parishes`
--

DROP TABLE IF EXISTS `election_circumscription_parishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_circumscription_parishes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  `circumscription_id` int unsigned NOT NULL,
  `parish_id` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_circumscription_parish` (`election_id`,`parish_id`),
  KEY `election_circumscription_parishes_canton_id_foreign` (`canton_id`),
  KEY `election_circumscription_parishes_circumscription_id_foreign` (`circumscription_id`),
  KEY `election_circumscription_parishes_parish_id_foreign` (`parish_id`),
  CONSTRAINT `election_circumscription_parishes_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_circumscription_parishes_circumscription_id_foreign` FOREIGN KEY (`circumscription_id`) REFERENCES `election_circumscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_circumscription_parishes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_circumscription_parishes_parish_id_foreign` FOREIGN KEY (`parish_id`) REFERENCES `parishes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_circumscriptions`
--

DROP TABLE IF EXISTS `election_circumscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_circumscriptions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  `dignity_id` int unsigned NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_circumscriptions_context` (`election_id`,`canton_id`,`code`),
  KEY `election_circumscriptions_canton_id_foreign` (`canton_id`),
  KEY `election_circumscriptions_dignity_id_foreign` (`dignity_id`),
  CONSTRAINT `election_circumscriptions_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_circumscriptions_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_circumscriptions_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_dignities`
--

DROP TABLE IF EXISTS `election_dignities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_dignities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `dignity_id` int unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `election_id_dignity_id` (`election_id`,`dignity_id`),
  KEY `election_dignities_dignity_id_foreign` (`dignity_id`),
  CONSTRAINT `election_dignities_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_dignities_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_list_results`
--

DROP TABLE IF EXISTS `election_list_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_list_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `polling_station_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  `circumscription_id` int unsigned DEFAULT NULL,
  `dignity_id` int unsigned NOT NULL,
  `candidate_list_id` int unsigned NOT NULL,
  `votes` int NOT NULL DEFAULT '0',
  `blank_votes` int NOT NULL DEFAULT '0',
  `null_votes` int NOT NULL DEFAULT '0',
  `total_voters` int NOT NULL DEFAULT '0',
  `registered_voters` int NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` int unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `registered_by` int unsigned NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_list_results_station` (`polling_station_id`,`election_id`,`dignity_id`,`candidate_list_id`),
  KEY `election_list_results_election_id_foreign` (`election_id`),
  KEY `election_list_results_canton_id_foreign` (`canton_id`),
  KEY `election_list_results_dignity_id_foreign` (`dignity_id`),
  KEY `election_list_results_candidate_list_id_foreign` (`candidate_list_id`),
  KEY `election_list_results_verified_by_foreign` (`verified_by`),
  KEY `election_list_results_registered_by_foreign` (`registered_by`),
  KEY `fk_election_list_results_circumscription` (`circumscription_id`),
  CONSTRAINT `election_list_results_candidate_list_id_foreign` FOREIGN KEY (`candidate_list_id`) REFERENCES `candidate_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_polling_station_id_foreign` FOREIGN KEY (`polling_station_id`) REFERENCES `polling_stations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_results_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `fk_election_list_results_circumscription` FOREIGN KEY (`circumscription_id`) REFERENCES `election_circumscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_list_votes`
--

DROP TABLE IF EXISTS `election_list_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_list_votes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  `circumscription_id` int unsigned DEFAULT NULL,
  `dignity_id` int unsigned NOT NULL,
  `candidate_list_id` int unsigned NOT NULL,
  `votes` int unsigned NOT NULL DEFAULT '0',
  `previous_votes` int unsigned NOT NULL DEFAULT '0',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_list_votes_context` (`election_id`,`canton_id`,`dignity_id`,`candidate_list_id`),
  KEY `election_list_votes_canton_id_foreign` (`canton_id`),
  KEY `election_list_votes_dignity_id_foreign` (`dignity_id`),
  KEY `election_list_votes_candidate_list_id_foreign` (`candidate_list_id`),
  KEY `election_list_votes_created_by_foreign` (`created_by`),
  KEY `fk_election_list_votes_circumscription` (`circumscription_id`),
  CONSTRAINT `election_list_votes_candidate_list_id_foreign` FOREIGN KEY (`candidate_list_id`) REFERENCES `candidate_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_votes_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_votes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `election_list_votes_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_list_votes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_election_list_votes_circumscription` FOREIGN KEY (`circumscription_id`) REFERENCES `election_circumscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_results`
--

DROP TABLE IF EXISTS `election_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `polling_station_id` int unsigned NOT NULL,
  `candidate_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `dignity_id` int unsigned NOT NULL,
  `votes` int NOT NULL DEFAULT '0',
  `blank_votes` int NOT NULL DEFAULT '0',
  `null_votes` int NOT NULL DEFAULT '0',
  `total_voters` int NOT NULL DEFAULT '0',
  `registered_voters` int NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` int unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `registered_by` int unsigned NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `election_results_polling_station_id_foreign` (`polling_station_id`),
  KEY `election_results_candidate_id_foreign` (`candidate_id`),
  KEY `election_results_dignity_id_foreign` (`dignity_id`),
  KEY `election_results_verified_by_foreign` (`verified_by`),
  KEY `election_results_registered_by_foreign` (`registered_by`),
  KEY `election_results_election_id_foreign` (`election_id`),
  KEY `idx_results_election_dignity_station` (`election_id`,`dignity_id`,`polling_station_id`),
  CONSTRAINT `election_results_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_results_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_results_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_results_polling_station_id_foreign` FOREIGN KEY (`polling_station_id`) REFERENCES `polling_stations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_results_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_results_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_seats`
--

DROP TABLE IF EXISTS `election_seats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_seats` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  `circumscription_id` int unsigned DEFAULT NULL,
  `dignity_id` int unsigned NOT NULL,
  `seats` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_election_seats_scope` (`election_id`,`canton_id`,`dignity_id`,`circumscription_id`),
  KEY `election_seats_canton_id_foreign` (`canton_id`),
  KEY `election_seats_dignity_id_foreign` (`dignity_id`),
  KEY `fk_election_seats_circumscription` (`circumscription_id`),
  CONSTRAINT `election_seats_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_seats_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `election_seats_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_election_seats_circumscription` FOREIGN KEY (`circumscription_id`) REFERENCES `election_circumscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elections`
--

DROP TABLE IF EXISTS `elections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `election_date` date NOT NULL,
  `election_type` enum('nacional','provincial','cantonal','parroquial') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'nacional',
  `status` enum('pendiente','en_curso','finalizada','anulada') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
  `created_by` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `elections_created_by_foreign` (`created_by`),
  CONSTRAINT `elections_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parishes`
--

DROP TABLE IF EXISTS `parishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parishes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `canton_id` int unsigned NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parishes_canton_id_foreign` (`canton_id`),
  CONSTRAINT `parishes_canton_id_foreign` FOREIGN KEY (`canton_id`) REFERENCES `cantons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `political_parties`
--

DROP TABLE IF EXISTS `political_parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `political_parties` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `acronym` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `list_number` int NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acronym` (`acronym`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polling_stations`
--

DROP TABLE IF EXISTS `polling_stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `polling_stations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `precinct_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `code_province` varchar(2) COLLATE utf8mb4_general_ci NOT NULL,
  `name_province` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code_canton` varchar(4) COLLATE utf8mb4_general_ci NOT NULL,
  `name_canton` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code_parish` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `name_parish` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `code_precinct` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `name_precinct` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `genero` enum('femenina','masculina') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `junta_femenina_inicio` int DEFAULT NULL,
  `junta_femenina_fin` int DEFAULT NULL,
  `junta_masculina_inicio` int DEFAULT NULL,
  `junta_masculina_fin` int DEFAULT NULL,
  `numero_junta` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `numero_electores` int NOT NULL,
  `status` enum('pendiente','asignada','en_proceso','reportada','revisada','aprobada') COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `polling_stations_precinct_id_foreign` (`precinct_id`),
  KEY `polling_stations_election_id_foreign` (`election_id`),
  KEY `idx_stations_election_province` (`election_id`,`name_province`),
  CONSTRAINT `polling_stations_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `polling_stations_precinct_id_foreign` FOREIGN KEY (`precinct_id`) REFERENCES `precincts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1461 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `precincts`
--

DROP TABLE IF EXISTS `precincts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precincts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parish_id` int unsigned NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `precincts_parish_id_foreign` (`parish_id`),
  CONSTRAINT `precincts_parish_id_foreign` FOREIGN KEY (`parish_id`) REFERENCES `parishes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `provinces`
--

DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `capital` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `region` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `population` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refresh_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `session_id` bigint unsigned DEFAULT NULL,
  `token` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refresh_tokens_user_id_foreign` (`user_id`),
  KEY `idx_refresh_tokens_session` (`session_id`),
  CONSTRAINT `fk_refresh_tokens_session` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `refresh_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `result_versions`
--

DROP TABLE IF EXISTS `result_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `polling_station_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `dignity_id` int unsigned NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `data` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `result_versions_election_id_foreign` (`election_id`),
  KEY `result_versions_dignity_id_foreign` (`dignity_id`),
  KEY `result_versions_created_by_foreign` (`created_by`),
  KEY `polling_station_id_election_id_dignity_id` (`polling_station_id`,`election_id`,`dignity_id`),
  CONSTRAINT `result_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `result_versions_dignity_id_foreign` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `result_versions_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `result_versions_polling_station_id_foreign` FOREIGN KEY (`polling_station_id`) REFERENCES `polling_stations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `permission_id` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id_permission_id` (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uploaded_minutes`
--

DROP TABLE IF EXISTS `uploaded_minutes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploaded_minutes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `polling_station_id` int unsigned NOT NULL,
  `election_id` int unsigned NOT NULL,
  `dignity_id` int unsigned NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `is_current` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('registrada','con_novedad','verificada') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'registrada',
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verified_by` int unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `consistency_status` enum('ok','error','sin_datos') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'sin_datos',
  `consistency_detail` text COLLATE utf8mb4_general_ci,
  `uploaded_by` int unsigned NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploaded_minutes_uploaded_by_foreign` (`uploaded_by`),
  KEY `idx_minutes_station_dignity` (`polling_station_id`,`election_id`,`dignity_id`,`is_current`),
  KEY `fk_minutes_dignity` (`dignity_id`),
  KEY `fk_minutes_verified_by` (`verified_by`),
  KEY `uploaded_minutes_election_id_foreign` (`election_id`),
  KEY `idx_minutes_election_current_dignity` (`election_id`,`is_current`,`dignity_id`),
  CONSTRAINT `fk_minutes_dignity` FOREIGN KEY (`dignity_id`) REFERENCES `dignities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_minutes_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `uploaded_minutes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uploaded_minutes_polling_station_id_foreign` FOREIGN KEY (`polling_station_id`) REFERENCES `polling_stations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uploaded_minutes_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_activity` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int unsigned DEFAULT NULL,
  `revoke_reason` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_sessions_token` (`token_hash`),
  KEY `user_sessions_revoked_by_foreign` (`revoked_by`),
  KEY `idx_user_sessions_user` (`user_id`,`revoked_at`,`last_activity`),
  CONSTRAINT `user_sessions_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `cedula` varchar(13) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role_id` int unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'elecciones'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-26  6:31:53
