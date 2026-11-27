SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
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
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (1,'Administrador General','Acceso completo al sistema','2026-06-12 01:40:09',NULL),(2,'Operador Electoral','Registro de resultados en JRV asignadas','2026-06-12 01:40:09',NULL),(3,'Visualizador','Solo lectura y monitoreo','2026-06-12 01:40:09','2026-10-25 19:35:31'),(4,'Supervisor','Corrige y verifica actas','2026-10-25 19:35:31',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES (1,1,1,'2026-06-12 01:40:09'),(2,1,2,'2026-06-12 01:40:09'),(3,1,3,'2026-06-12 01:40:09'),(4,1,4,'2026-06-12 01:40:09'),(5,1,5,'2026-06-12 01:40:09'),(6,1,6,'2026-06-12 01:40:09'),(7,1,7,'2026-06-12 01:40:09'),(8,1,8,'2026-06-12 01:40:09'),(9,1,9,'2026-06-12 01:40:09'),(10,1,10,'2026-06-12 01:40:09'),(11,1,11,'2026-06-12 01:40:09'),(12,1,12,'2026-06-12 01:40:09'),(13,1,13,'2026-06-12 01:40:09'),(14,1,14,'2026-06-12 01:40:09'),(15,1,15,'2026-06-12 01:40:09'),(16,1,16,'2026-06-12 01:40:09'),(17,1,17,'2026-06-12 01:40:09'),(18,1,18,'2026-06-12 01:40:09'),(19,1,19,'2026-06-12 01:40:09'),(20,1,20,'2026-06-12 01:40:09'),(21,1,21,'2026-06-12 01:40:09'),(22,1,22,'2026-06-12 01:40:09'),(23,1,23,'2026-06-12 01:40:09'),(24,1,24,'2026-06-12 01:40:09'),(25,1,25,'2026-06-12 01:40:09'),(26,1,26,'2026-06-12 01:40:09'),(27,1,27,'2026-06-12 01:40:09'),(28,1,28,'2026-06-12 01:40:09'),(29,1,29,'2026-06-12 01:40:09'),(30,1,30,'2026-06-12 01:40:09'),(31,1,31,'2026-06-12 01:40:09'),(32,1,32,'2026-06-12 01:40:09'),(33,1,33,'2026-06-12 01:40:09'),(34,1,34,'2026-06-12 01:40:09'),(35,1,35,'2026-06-12 01:40:09'),(36,1,36,'2026-06-12 01:40:09'),(37,1,37,'2026-06-12 01:40:09'),(38,1,38,'2026-06-12 01:40:09'),(39,1,39,'2026-06-12 01:40:09'),(40,1,40,'2026-06-12 01:40:09'),(41,1,41,'2026-06-12 01:40:09'),(42,1,42,'2026-06-12 01:40:09'),(43,1,43,'2026-06-12 01:40:09'),(44,1,44,'2026-06-12 01:40:09'),(45,1,45,'2026-06-12 01:40:09'),(46,1,46,'2026-06-12 01:40:09'),(47,1,47,'2026-06-12 01:40:09'),(48,1,48,'2026-06-12 01:40:09'),(49,1,49,'2026-06-12 01:40:09'),(50,1,50,'2026-06-12 01:40:09'),(51,1,51,'2026-06-12 01:40:09'),(52,1,52,'2026-06-12 01:40:09'),(53,1,53,'2026-06-12 01:40:09'),(54,1,54,'2026-06-12 01:40:09'),(55,1,55,'2026-06-12 01:40:09'),(56,1,56,'2026-06-12 01:40:09'),(57,1,57,'2026-06-12 01:40:09'),(58,1,58,'2026-06-12 01:40:09'),(59,1,59,'2026-06-12 01:40:09'),(60,1,60,'2026-06-12 01:40:09'),(61,1,61,'2026-06-12 01:40:09'),(62,1,62,'2026-06-12 01:40:09'),(63,1,63,'2026-06-12 01:40:09'),(64,1,64,'2026-06-12 01:40:09'),(65,1,65,'2026-06-12 01:40:09'),(66,1,66,'2026-06-12 01:40:09'),(67,1,67,'2026-06-12 01:40:09'),(68,1,68,'2026-06-12 01:40:09'),(69,1,69,'2026-06-12 01:40:09'),(70,2,46,'2026-06-12 01:40:09'),(71,2,51,'2026-06-12 01:40:09'),(72,2,54,'2026-06-12 01:40:09'),(73,2,55,'2026-06-12 01:40:09'),(74,2,56,'2026-06-12 01:40:09'),(75,2,59,'2026-06-12 01:40:09'),(76,2,60,'2026-06-12 01:40:09'),(77,2,65,'2026-06-12 01:40:09'),(78,3,10,'2026-06-12 01:40:09'),(79,3,14,'2026-06-12 01:40:09'),(80,3,18,'2026-06-12 01:40:09'),(81,3,26,'2026-06-12 01:40:09'),(82,3,30,'2026-06-12 01:40:09'),(83,3,34,'2026-06-12 01:40:09'),(84,3,38,'2026-06-12 01:40:09'),(85,3,42,'2026-06-12 01:40:09'),(86,3,46,'2026-06-12 01:40:09'),(87,3,51,'2026-06-12 01:40:09'),(88,3,55,'2026-06-12 01:40:09'),(89,3,62,'2026-06-12 01:40:09'),(90,3,63,'2026-06-12 01:40:09'),(91,3,64,'2026-06-12 01:40:09'),(92,3,65,'2026-06-12 01:40:09'),(93,3,66,'2026-06-12 01:40:09'),(94,4,10,'2026-10-25 19:35:31'),(95,4,14,'2026-10-25 19:35:31'),(96,4,18,'2026-10-25 19:35:31'),(97,4,22,'2026-10-25 19:35:31'),(98,4,26,'2026-10-25 19:35:31'),(99,4,30,'2026-10-25 19:35:31'),(100,4,34,'2026-10-25 19:35:31'),(101,4,38,'2026-10-25 19:35:31'),(102,4,42,'2026-10-25 19:35:31'),(103,4,46,'2026-10-25 19:35:31'),(104,4,51,'2026-10-25 19:35:31'),(105,4,54,'2026-10-25 19:35:31'),(106,4,55,'2026-10-25 19:35:31'),(107,4,56,'2026-10-25 19:35:31'),(108,4,57,'2026-10-25 19:35:31'),(109,4,59,'2026-10-25 19:35:31'),(110,4,60,'2026-10-25 19:35:31'),(111,4,62,'2026-10-25 19:35:31'),(112,4,63,'2026-10-25 19:35:31'),(113,4,64,'2026-10-25 19:35:31'),(114,4,65,'2026-10-25 19:35:31'),(115,4,66,'2026-10-25 19:35:31'),(116,4,67,'2026-10-25 19:35:31'),(117,3,60,'2026-10-25 19:35:31');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
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
-- Dumping data for table `users`
--
-- WHERE:  id=1

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `cedula`, `phone`, `role_id`, `is_active`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'admin','admin@elecciones.gob.ec','$2y$10$GlBPks2Jvhq3d1LFWD2Bz.y8edslD8TfW91VPyfLwSDE5z9PioVJK','Administrador','General','0000000000001','0999999999',1,1,'2026-11-25 21:50:38','2026-06-12 01:40:09','2026-11-25 21:50:38',NULL);
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

-- Dump completed on 2026-11-26  6:31:53
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
-- Dumping data for table `political_parties`
--

LOCK TABLES `political_parties` WRITE;
/*!40000 ALTER TABLE `political_parties` DISABLE KEYS */;
INSERT INTO `political_parties` (`id`, `name`, `acronym`, `logo`, `list_number`, `color`, `is_active`, `created_at`, `updated_at`) VALUES (1,'MPD','MPD','uploads/parties/1794540052_d6345181f2268a94ebc6.webp',3,'#000000',1,'2026-06-11 21:31:53','2026-11-12 22:20:52'),(2,'RC','RC',NULL,5,'#00ff80',1,'2026-06-11 21:32:13','2026-06-11 21:32:13'),(3,'ADN','ADN',NULL,4,'#ff00ff',1,'2026-06-11 21:32:27','2026-06-11 21:32:27');
/*!40000 ALTER TABLE `political_parties` ENABLE KEYS */;
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
-- Dumping data for table `cantons`
--

LOCK TABLES `cantons` WRITE;
/*!40000 ALTER TABLE `cantons` DISABLE KEYS */;
INSERT INTO `cantons` (`id`, `province_id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES (1,8,'560','Esmeraldas',1,'2026-06-11 21:13:01','2026-06-11 21:13:01'),(2,8,'561','RioVerde',1,'2026-06-11 21:13:25','2026-06-11 21:13:25'),(3,8,'565','Eloy Alfaro',1,'2026-06-11 21:13:48','2026-06-11 21:13:48'),(4,8,'570','Muisne',1,'2026-06-11 21:14:08','2026-06-11 21:14:08'),(5,8,'580','San Lorenzo',1,'2026-06-11 21:14:29','2026-06-11 21:14:29'),(6,8,'785','Atacames',1,'2026-06-11 21:14:44','2026-06-11 21:14:44'),(7,8,'575','Quininde',1,'2026-06-11 21:27:36','2026-06-11 21:27:36');
/*!40000 ALTER TABLE `cantons` ENABLE KEYS */;
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
-- Dumping data for table `parishes`
--

LOCK TABLES `parishes` WRITE;
/*!40000 ALTER TABLE `parishes` DISABLE KEYS */;
INSERT INTO `parishes` (`id`, `canton_id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES (1,1,'450','CAMARONES',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(2,1,'655','CRNL.CARLOS CONCHA TORRES',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(3,1,'895','CHINCA',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(4,1,'2320','MAJUA',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(5,1,'3570','SAN MATEO',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(6,1,'3930','TABIAZO',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(7,1,'3940','TACHINA',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(8,1,'4240','VUELTA LARGA',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(9,1,'5205','BARTOLOME RUIZ',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(10,1,'5210','5 DE agosto',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(11,1,'5335','ESMERALDAS',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(12,1,'5600','LUIS TELLO / LAS PALMAS /',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(13,2,'930','CHONTADURO',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(14,2,'965','CHUMUNDE',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(15,1,'6905','SIMON PLATA TORRES',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(16,3,'4595','SANTA LUCIA DE LAS PEÑAS',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(17,2,'2035','LAGARTO',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(18,2,'2445','MONTALVO / HORQUETA',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(19,2,'3215','ROCAFUERTE',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(20,2,'6720','RIO VERDE',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(21,3,'120','ANCHAYACU',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(22,3,'200','ATAHUALPA/CAMARONES',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(23,3,'370','BORBON',1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(24,3,'1505','SAN JOSE DE CAYAPAS',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(25,3,'1510','COLON ELOY DE MARIA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(26,3,'1515','TIMBIRE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(27,3,'2125','LA TOLA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(28,3,'2225','LUIS VARGAS TORRES',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(29,3,'2335','MALDONADO',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(30,3,'2670','PAMPANAL DE BOLIVAR',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(31,3,'3405','SAN FRANCISCO DE ONZOLE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(32,3,'3805','SELVA ALEGRE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(33,3,'4170','TELEMBI',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(34,3,'4465','SANTO DOMINGO DE ONZOLE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(35,3,'6205','VALDEZ/LIMONES/',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(36,4,'355','BOLIVAR',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(37,4,'1010','DAULE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(38,4,'1305','GALERA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(39,4,'3125','QUINGUE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(40,4,'3280','SALIMA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(41,4,'3390','SAN FRANCISCO',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(42,4,'3430','SAN GREGORIO',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(43,4,'3470','SAN JOSE DE CHAMANGA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(44,4,'5670','MUISNE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(45,7,'700','CUBE / CHANCAMA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(46,7,'980','CHURA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(47,7,'1530','LA UNION',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(48,7,'2345','MALIMPIA / GUAYLLABAMBA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(49,7,'4230','VICHE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(50,7,'5895','ROSA ZARATE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(51,5,'70','ALTO TAMBO/GUADAL',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(52,5,'110','ANCON/PALMA REAL',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(53,5,'435','CALDERON',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(54,5,'535','CARONDELET',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(55,5,'570','5 DE JUNIO/HUIMBI',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(56,5,'620','CONCEPCION',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(57,5,'2395','MATAJE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(58,5,'3455','SAN JAVIER DE CACHABI',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(59,5,'3725','SANTA RITA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(60,5,'3955','TAMBILLO',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(61,5,'4075','TULULBI /RICAURTE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(62,5,'4125','URBINA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(63,5,'6130','SAN LORENZO',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(64,6,'1615','TONSUPA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(65,6,'2130','LA UNION',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(66,6,'3880','SUA/BOCANA',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(67,6,'4055','TONCHIGUE',1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(68,6,'6380','ATACAMES',1,'2026-06-11 21:44:20','2026-06-11 21:44:20');
/*!40000 ALTER TABLE `parishes` ENABLE KEYS */;
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
-- Dumping data for table `precincts`
--

LOCK TABLES `precincts` WRITE;
/*!40000 ALTER TABLE `precincts` DISABLE KEYS */;
INSERT INTO `precincts` (`id`, `parish_id`, `code`, `name`, `address`, `latitude`, `longitude`, `is_active`, `created_at`, `updated_at`) VALUES (1,1,'1208','UNIDAD EDUCATIVA \"DR. FRANKLIN TELLO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(2,2,'3878','ESCUELA GENERAL BASICA \"JARDIN DE LA INFANCIA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(3,3,'6068','UNIDAD EDUCATIVA DEL MILENIUM CHINCA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(4,4,'1194','ESCUELA GENERAL BASICA \"GUAYAS\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(5,5,'3450','ESCUELA GENERAL BÁSICA \"DR. RICARDO PAREDES ROMERO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(6,5,'1911','UNIDAD EDUCATIVA SAN MATEO NRO. 2',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(7,5,'6043','UNIDAD EDUCATIVA SAN MATEO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(8,6,'1250','UNIDAD EDUCATIVA FISCAL TABIAZO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(9,7,'1149','ESCUELA GENERAL BASICA PEDRO CORNELIO DROUET',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(10,8,'52','UNIDAD EDUCATIVA \"LEON FEBRES CORDERO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(11,8,'6133','ESCUELA EGB CESAR NAVIL ESTUPIÑAN BASS',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(12,9,'1302','UNIDAD EDUCATIVA FISCOMISIONAL \"CRISTO REY\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(13,9,'1650','UNIDAD EDUCATIVA \"DR. LUIS PRADO VITERI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(14,9,'4988','UNIDAD EDUCATIVA MANUEL NIETO CADENA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(15,9,'6128','UNIVERSIDAD TECNICA LUIS VARGAS TORRES',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(16,10,'605','UNIDAD EDUCATIVA MARGARITA CORTEZ NRO.2',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(17,10,'3446','E.G.B. \"DR. JOSE MARÍA VELASCO IBARRA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(18,10,'1067','UNIDAD EDUCATIVA FISCAL LUIS VARGAS TORRES',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(19,10,'1733','UNIDAD EDUCATIVA NELSON ORTIZ ESTAFANUTO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(20,10,'1063','UNIDAD EDUCATIVA FISCAL MARGARITA CORTEZ',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(21,10,'1283','UNIDAD EDUCATIVA PARTICULAR LA INMACULADA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(22,10,'3324','UNIDAD EDUCATIVA LUIS TELLO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(23,10,'3330','UNIDAD EDUCATIVA \"5 DE AGOSTO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(24,10,'6120','ESCUELA EGB ESMERALDAS LIBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(25,10,'6023','ESCUELA EGB SAN JOSE OBRERO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(26,11,'1099','ESCUELA GENERAL BÁSICA \"HISPANO AMERICA NRO. 2\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(27,11,'1660','UNIDAD EDUCATIVA PARTICULAR LUZ Y LIBERTAD',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(28,11,'1135','U.E. FISCOMISIONAL \"SAGRADO CORAZÓN\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(29,11,'1639','ESCUELA GENERAL BASICA \"JUAN MONTALVO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(30,11,'1294','UNIDAD EDUCATIVA FISCAL ELOY ALFARO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(31,11,'1920','UNIDAD EDUCATIVA \"21 DE SEPTIEMBRE\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(32,11,'1967','U.E. FISCOMISIONAL DON BOSCO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(33,11,'1968','E.G.B. FISCOMISIONAL DON BOSCO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(34,11,'4990','ESCUELA LEONIDAS GRUEZO GEORGE',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(35,11,'5458','U.E. ELOY ALFARO NRO. 2',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(36,11,'6114','COLEGIO DE BACHILLERATO NOCTURNO ESMERALDAS',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(37,11,'6117','UNIDAD EDUCATIVA MONSEÑOR LEONIDAS PROAÑO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(38,12,'1246','UNIDAD EDUCATIVA FISCOMISIONAL \"NUEVO ECUADOR\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(39,12,'3359','EGB \"DR. RICARDO PAREDES\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(40,12,'1229','ESCUELA GENERAL BASICA FISCAL \"GUAYAQUIL\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(41,12,'6067','EGB MODESTO ELIAS MENDOZA MOREIRA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(42,13,'1674','UNIDAD EDUCATIVA FISCAL CHONTADURO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(43,13,'5905','UNIDAD EDUCATIVA EUGENIO CHUZING ALDAZ',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(44,14,'3901','ESCUELA RICARDO PLAZA BASTIDAS',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(45,14,'3902','ESCUELA DE EDUCACION BASICA ANTONIO ELIZALDE',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(46,14,'3286','UNIDAD EDUCATIVA ARMENGOL LARA VALENCIA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(47,14,'5928','ESCUELA 7 DE JULIO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(48,14,'5927','ESCUELA SAN RUFINO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(49,14,'6986','ESCUELA FISCAL MIXTA HERNAN MALO GONZALEZ',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(50,15,'3270','UNIDA EDUCATIVA FISCOMISIONAL \"SAN DANIEL COMBONI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(51,15,'3448','UNIDAD EDUCATIVA FISCOMISIONAL MARÍA AUXILIADORA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(52,15,'1915','ESCUELA GENERAL BASICA \"NUESTRA SEÑORA DE LORETO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(53,15,'1910','ESCUELA GENERAL BASICA CONSUELO BENAVIDES CEVALLOS',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(54,15,'5457','UNIDAD EDUCATIVA ALFONSO QUIÑONEZ GEORGE',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(55,15,'6119','ESCUELA HOMERO LOPEZ SAUD',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(56,16,'3310','U.E. CONCEPCION TEMBORY FE Y ALEGRIA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(57,17,'3261','E.G.B. SEBASTIÁN DE ILLESCAS',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(58,17,'1666','U.E. FISCAL AURELIA BECERRA DE QUIÑONEZ NRO. 1',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(59,17,'3262','ESCUELA DE EDUCACIÓN GENERAL BÁSICA DONATO YANUZELLY ORTÍZ',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(60,17,'6181','UNIDAD EDUCATIVA AURELIA BECERRA DE QUIÑONEZ BLOQUE 2',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(61,18,'1687','UNIDAD EDUCATIVA JUAN MONTALVO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(62,19,'1684','UNIDAD EDUCATIVA FISCAL RAMÓN ESTUPIÑAN',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(63,19,'3258','ESCUELA GENERAL BÁSICA FISCAL COTOPAXI',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(64,19,'6180','UNIDAD EDUCATIVA FISCAL RAMON ESTUPIÑAN BLOQUE 1',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(65,20,'1664','UNIDAD EDUCATIVA RIO VERDE NRO.2',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(66,20,'3284','CASA COMUNAL CABUYAL',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(67,20,'6135','UNIDAD EDUCATIVA RIO VERDE',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(68,21,'3308','UNIDAD EDUCATIVA FISCAL 2 DE AGOSTO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(69,22,'3441','ESCUELA PUERTO BAQUERIZO',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(70,22,'4140','PLAZOLETA',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(71,23,'2017','UNIDAD EDUCATIVA FISCAL BORBON',NULL,NULL,NULL,1,'2026-06-11 21:44:19','2026-06-11 21:44:19'),(72,23,'2016','U.E. FISCOMISIONAL SANTA MARÍA GORETTI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(73,24,'4175','UNIDAD EDUCATIVA \"HERCILIO PIANCHICHE OREJUELA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(74,24,'4156','ESCUELA GENERAL BÁSICA \"RUMIÑAHUI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(75,24,'5154','UNIDAD EDUCATIVA JOSE RICARDO MARTINEZ COBO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(76,24,'5155','ESCUELA GENERAL BASICA RAFAEL MARIA ARIZA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(77,25,'3331','UNIDAD EDUCATIVA RAFAEL ASTUDILLO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(78,26,'3335','UNIDAD EDUCATIVA GONZALEZ S. CORDOVA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(79,27,'1102','UNIDAD EDUCATIVA LA TOLA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(80,27,'3312','ESCUELA FISCAL 17 DE DICIEMBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(81,28,'4147','UNIDAD EDUCATIVA RITA LECUMBERRY',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(82,29,'3271','U.E. FISCAL PEDRO VICENTE MALDONADO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(83,30,'4148','E.G.B. MANUEL J. CALLE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(84,31,'3345','E.G.B. AGUSTIN BEDON',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(85,31,'4149','UNIDAD EDUCATIVA FISCAL \"SAN FRANCISCO DE ONZOLE\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(86,32,'3272','UNIDAD EDUCATIVA DOMINGO MEDINA ZURITA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(87,32,'3443','ESCUELA FISCAL PAULINA PERALTA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(88,33,'4198','ESCUELA GENERAL BÁSICA \"LEOPOLDO N. CHAVEZ\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(89,33,'4200','UNIDAD EDUCATIVA CECIB ATALAYA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(90,33,'4204','CENTRO EDUCATIVO COMUNITARIO INTERCULTURAL BILINGUE \"JOSÉ ZAPATA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(91,33,'4206','ESCUELA INTERCULTURAL RÍO ZAPALLO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(92,33,'4645','ESCUELA GENERAL BÁSICA \"TEEKALI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(93,34,'3979','ESCUELA ABDON CALDERON MUÑOZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(94,34,'4000','UNIDAD EDUCATIVA JIBA JAKI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(95,34,'4207','ESCUELA ROSENDO ORTIZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(96,34,'4185','UNIDAD EDUCATIVA GRAN COLOMBIA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(97,34,'4194','CENTRO EDUCATIVO COMUNITARIO \"GONZALO PIZARRO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(98,34,'7573','CASA COMUNAL DE SANTO DOMIMGO DE ÓNZOLE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(99,35,'702','U.E. CATOLICA FISCOMISIONAL PAULO VI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(100,35,'1966','U.E. 16 DE OCTUBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(101,35,'6963','UNIDAD EDUCATIVA 13 DE MAYO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(102,36,'3283','ESCUELA GENERAL BÁSICA \"MAXIMINO INTRIAGO PATIÑO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(103,36,'3285','UNIDAD EDUCATIVA JOSE JOAQUIN DE OLMEDO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(104,37,'4026','E.G.B. ALBERTO PERDOMO FRANCO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(105,37,'3287','ESCUELA GENERAL BÁSICA LUIS FELIPE BORJA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(106,38,'3872','E.G.B. \"VIÑA DEL MAR\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(107,38,'3295','E.G.B. \"GENERAL VILLAMIL\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(108,39,'3875','ESCUELA REPUBLICA DE FRANCIA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(109,40,'4046','ESCUELA EDUCACION GENERAL BASICA MANABI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(110,41,'3871','UNIDAD EDUCATIVA SAN FRANCISCO DEL CABO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(111,41,'3873','E.G.B. \"20 DE JULIO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(112,42,'4043','ESCUELA FISCAL MIXTA FILOCLEZ MUÑOZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(113,42,'4044','ESCUELA EDUCACIÒN BASICA ATAHUALPA PERDOMO FRANCO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(114,42,'4045','ESCUELA EDUCACION BASICA PRESIDENTE ROSSVELT',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(115,42,'3874','UNIDAD EDUCATIVA GABRIEL IRIARTE RÍOS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(116,42,'4060','UNIDAD EDUCATIVA BILINGUE \"JUAN LORENZO AÑAPA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(117,42,'6989','EGB MIXTA 27 DE SEPTIEMBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(118,43,'1697','UNIDAD EDUCATIVA \"CHAMANGA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(119,44,'3293','UNIDAD EDUCATIVA MUISNE NRO.2',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(120,44,'4461','UNIDAD EDUCATIVA \"MUISNE\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(121,44,'5800','UNIDAD EDUCATIVA FISCOMISIONAL SAN LUIS GONZAGA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(122,44,'5796','UNIDAD EDUCATIVA VICENTE TOBAR JARRIN',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(123,44,'5930','ESCUELA HELLEN KALLER',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(124,44,'5929','UNIDAD EDUCATIVA VICENTE ROCAFUERTE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(125,45,'1399','UNIDAD EDUCATIVA REPUBLICA DE CUBA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(126,45,'4999','UNIDAD EDUCATIVA ARMADA NACIONAL',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(127,45,'6960','ESCUELA DE EDUCACION BASICA FISCAL 10 DE JULIO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(128,45,'6987','UNIDAD EDUCATIVA JESUS DEL GRAN PODER',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(129,46,'3870','E.G.B. ANGELO PATRY',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(130,47,'3257','ESCUELA FISCAL MIXTA SOBERANIA ESMERALDEÑA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(131,47,'3259','ESCUELA FISCAL DR. CARLOS CUEVA TAMARIZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(132,47,'3260','E.G.B. CORONEL RAMON VALDEZ CERVANTES',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(133,47,'3290','E.G.B. CIUDAD DE AMBATO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(134,47,'3352','E.G.B. \"CARLOS ANDRADE DE MARIN\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(135,47,'3292','ESCUELA GENERAL BÁSICA NUEVO AZUAY',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(136,47,'634','ESCUELA GENERAL BASICA JHON F. KENEDDY',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(137,47,'3288','ESCUELA FISCAL MIXTA MIGUEL RIOFRIO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(138,47,'3291','ESCUELA FISCAL MIXTA 15 DE AGOSTO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(139,47,'4989','E.G.B. CORNELIO MERIZALDE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(140,47,'5456','UNIDAD EDUCATIVA QUITO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(141,47,'5975','UNIDAD EDUCATIVA NUESTRA SEÑORA DEL CISNE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(142,47,'5931','UNIDAD EDUCATIVA JAIME ROLDOS AGUIERA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(143,47,'6000','U.E. FISCOMISIONAL CATOLICA SANTA MARIA DE NAZARET',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(144,47,'6202','UNIDAD EDUCATIVA LA UNION',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(145,47,'6953','U.G.B CONTO PATIÑO MARTINEZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(146,47,'6979','ESCUELA DE EDUCACION BASICA PRINCIPADO MÒNACO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(147,48,'3281','ESCUELA FISCAL MIXTA LUZ DE CANANDE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(148,48,'3762','ESCUELA EDUCACIÓN GENERAL BÁSICA 28 DE SEPTIEMBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(149,48,'3866','U.E. INTERCULTURAL BILINGUE DE LA LENGUA \"BATALLA DE TARQUI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(150,48,'3867','ESCUELA FISCAL MIXTA JOSE BENIGNO PALACIOS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(151,48,'3868','ESCUELA EDUCACIÓN GENERAL BÁSICA CAMILO PONCE ENRIQUE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(152,48,'3821','ESCUELA GENERAL DE EDUCACIÓN BÁSICA \"ISABEL DE SANTIAGO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(153,48,'3823','COLEGIO TÉCNICO AGROPECUARIO CULTURAL BILINGUE RÍO CANANDE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(154,48,'3824','UNIDAD EDUCATIVA SERAFINA QUINTERO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(155,48,'3825','ESCUELA DE EDUCACIÓN GENERAL BÁSICA LUZ Y VIDA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(156,48,'3826','U.E. BATALLA DE PICHINCHA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(157,48,'1139','UNIDAD EDUCATIVA ESTANDARIZADA NUEVO MILENIUN MALIMPIA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(158,48,'3297','E.G.B. \"PROVINCIA ESMERALDAS\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(159,48,'3300','ESCUELA FISCAL GABRIELA MISTRAL',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(160,48,'3889','ESCUELA EDUCACIÓN GENERAL BÁSICA EDEN SADE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(161,48,'3349','ESCUELA DE EDUCACIÓN GENERAL BÁSICA 5 DE JUNIO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(162,48,'6956','CASA COMUNAL ÑAMPI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(163,49,'1317','UNIDAD EDUCATIVA FISCOMISIONAL CRISTO REDENTOR',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(164,49,'5974','UNIDAD EDUCATIVA TARQUI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(165,50,'1278','U.E. FISCOMISIONAL JUAN XXIII',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(166,50,'3869','UNIDAD EDUCATIVA 12 DE ENERO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(167,50,'1348','ESCUELA GENERAL BÁSICA PARTICULAR DOCTOR CAMILO GALLEGOS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(168,50,'1370','UNIDAD EDUCATIVA SIMÓN PLATA TORRES',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(169,50,'3985','ESCUELA FISCAL MIXTA 10 DE AGOSTO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(170,50,'3294','ESCUELA GENERAL BÁSICA VELASQUEZ KUFFO/ANTES UNIDAD EDUCATIVA LOS ANDES',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(171,50,'3354','ESCUELA DE EDUCACIÓN GENERAL BÁSICA \"SAN RAMON\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(172,50,'1331','ESCUELA DE EDUCACIÓN GENERAL BÁSICA 3 DE JULIO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(173,50,'1293','U.E. FISCOMISIONAL JUAN XXIII NRO.2',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(174,50,'1295','UNIDAD EDUCATIVA 9 DE OCTUBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(175,50,'3298','UNIDAD EDUCATIVA PARTICULAR CATÓLICA \"SAGRADA FAMILIA DE NAZARET\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(176,50,'3299','UNIDAD EDUCATIVA FISCAL NUEVA JERUSALEN',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(177,50,'3296','UNIDAD EDUCATIVA FISCAL \"CIUDAD DE GUARANDA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(178,50,'5999','U.E. ANDRES F CORDOVA NRO.2',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(179,50,'5937','UNIDAD EDUCATIVA ALEJANDRO OTOYA BRIONES',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(180,50,'5998','U.E. ANDRES F. CORDOVA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(181,50,'5957','ESCUELA EGB PATRIA NUEVA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(182,51,'3356','ESCUELA GENERAL BASICA LIZARDO GOYES BENITEZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(183,51,'4190','CASA COMUNAL',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(184,52,'4199','UNIDAD EDUCATIVA ANCON DE SARDINAS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(185,52,'4201','ESCUELA GENERAL BÁSICA \"ANTONIO ALMEIDA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(186,53,'3274','ESCUELA DE EDUCACION GENERAL BASICA ABDON CALDERON',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(187,54,'3337','ESCUELA GENERAL BASICA REMIGIO CRESPO TORAL',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(188,54,'3340','UNIDAD EDUCATIVA BARON DE CARONDELET',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(189,55,'4208','ESCUELA DE EDUCACIÓN GENERAL BÁSICA \"JUAN SALINAS\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(190,56,'3338','U.E. TARCILA BEDON BATIOJA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(191,56,'3444','ESCUELA EDUCACIÓN GENERAL BÁSICA \"JOSÉ PLACIDO CAAMAÑO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(192,56,'4212','ESCUELA DE EDUCACIÓN GENERAL BÁSICA \"4 DE OCTUBRE\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(193,56,'5157','ESCUELA 12 DE OCTUBRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(194,57,'3279','ESCUELA DE EDUCACION GENERAL BASICA FISCAL MI PATRIA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(195,57,'3342','ESCUELA DE EDUCACION GENERAL BASICA SEGUNDO MARCOS CABEZAS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(196,57,'4214','ESCUELA DE E.G.B. LUCIANO GERMAN NASTACUAZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(197,58,'3275','UNIDAD EDUCATIVA IBARRA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(198,58,'3276','ESCUELA FISCAL ANA VILLAMIL ICAZA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(199,59,'3336','ESCUELA GENERAL JULIO ANDRADE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(200,59,'4209','ESCUELA DE EDUCACIÓN BÁSICA \"JOSE GABRIEL BATALLAS\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(201,60,'3343','ESCUELA EDUCACION BASICA JOSE ABEL CASTILLO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(202,60,'4210','ESCUELA DE EDUCACIÓN GENERAL BÁSICA FISCAL \"PEDRO MONCAYO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(203,61,'3273','UNIDAD EDUCATIVA \"TULULBI\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(204,61,'3348','CENTRO EDUCATIVO SANTA ROSA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(205,61,'3971','CENTRO EDUCATIVO INTERCULTURAL BILINGUE MOISES CANTICUZ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(206,61,'4213','CENTRO EDUCATIVO COMUNITARIO INTERCULTRAL BILINGUE COZOPANGUI',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(207,61,'6970','ESCUELA DE EDUCACION BASICA VUELTA DE MICO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(208,62,'3277','UNIDAD EDUCATIVA LUIS CADENA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(209,63,'1258','UNIDAD EDUCATIVA MARISCAL SUCRE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(210,63,'1263','UNIDAD EDUCATIVA FISCOMISIONAL 10 DE AGOSTO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(211,63,'1239','ESCUELA DE EDUCACIÓN GENERAL BÁSICA 26 DE AGOSTO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(212,63,'4678','UNIDAD EDUCATIVA \"22 DE MARZO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(213,64,'3900','ESCUELA MIXTA 1 DE MAYO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(214,64,'77','COLEGIO DE BACHILLERATO FISCAL NELSON ESTUPIÑAN BASS',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(215,64,'5867','ESCUELA EGB JAIME ROLDOS AGUILERA',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(216,64,'6182','ESCUELA EGB ZOILA UGARTE DE LANDIVAR',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(217,64,'6958','UNIDAD EDUCATIVA ANTONIO ANTE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(218,65,'1206','UNIDAD EDUCATIVA CESAR PLAZA MONZON',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(219,66,'1209','UNIDAD EDUCATIVA FISCAL \"BALNEARIO DE SUA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(220,66,'6955','CASA COMUNAL MUCHIN',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(221,67,'3267','ESCUELA DE EDUCACIÓN BÁSICA \"ADALBERTO ORTIZ QUIÑONEZ\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(222,67,'3249','ESCUELA DE EDUCACIÓN GENERAL BÁSICA FISCAL \"CIUDAD DE SAN LORENZO\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(223,67,'1057','COLEGIO DE BACHILLERATO FISCAL TONCHIGUE',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(224,67,'5866','ESCUELA DE EDUCACION GENERAL BASICA QUITO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(225,67,'6959','E.G.B. RAMÓN PACHECO PERDOMO',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(226,67,'6971','UNIDAD EDUCATIVA MACARÁ',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(227,67,'6966','EGB FISCAL MIXTA HOMERO CHIRIBOGA JIJÓN',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(228,68,'2112','UNIDAD EDUCATIVA \"ESMERALDAS\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(229,68,'1176','UNIDAD EDUCATIVA FISCOMISIONAL ESTRELLA DEL MAR',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(230,68,'2113','UNIDAD EDUCATIVA GENERAL BÁSICA \"AZUAY\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(231,68,'4483','ESCUELA DE EDUCACIÓN GENERAL BÁSICA \"ATAHUALPA\"',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(232,68,'5805','UNIDAD EDUCATIVA ATACAMES',NULL,NULL,NULL,1,'2026-06-11 21:44:20','2026-06-11 21:44:20'),(233,68,'5862','UNIDAD EDUCATIVA LCDA. AGUEDA GONZALEZ QUIÑONEZ',NULL,NULL,NULL,1,'2026-06-11 21:44:21','2026-06-11 21:44:21');
/*!40000 ALTER TABLE `precincts` ENABLE KEYS */;
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
SET FOREIGN_KEY_CHECKS=1;
