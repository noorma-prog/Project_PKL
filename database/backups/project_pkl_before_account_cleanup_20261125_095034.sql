-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: project_pkl
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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('layanit-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1793063681),('layanit-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1793063681;',1793063681),('layanit-cache-76546f9a641ede2beab506b96df1688d889e629a','i:1;',1793063580),('layanit-cache-76546f9a641ede2beab506b96df1688d889e629a:timer','i:1793063580;',1793063580),('layanit-cache-sitimaida483@gmail.com|127.0.0.1','i:2;',1795394121),('layanit-cache-sitimaida483@gmail.com|127.0.0.1:timer','i:1795394121;',1795394121),('layanit-cache-sucisukmawati359@gmail.com|127.0.0.1','i:1;',1793254865),('layanit-cache-sucisukmawati359@gmail.com|127.0.0.1:timer','i:1793254865;',1793254865);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'pemeliharaan atau instalasi jaringan komunikasi','pemeliharaan-atau-instalasi-jaringan-komunikasi','Menginstalasi, Memelihara, dan memperbaiki sistem komunikasi kepolisian','bi-broadcast',1,'2026-10-22 16:09:25','2026-10-27 19:50:03'),(6,'Server & Pusat Data','server-pusat-data','Kendala server penyimpanan satker, hak akses database, backup berkas dan lisensi sistem.','bi-hdd-network',1,'2026-10-22 16:09:25','2026-10-22 16:09:25'),(8,'pemeliharaan atau instalasi jaringan','pemeliharaan-atau-instalasi-jaringan','Menginstalasi, memelihara, dan memperbaiki sistem komunikasi kepolisian.','bi-router',1,'2026-10-27 19:33:07','2026-10-27 19:50:20'),(9,'Pemeliharaan Perangkat Keras (hardware)','pemeliharaan-perangkat-keras-hardware','Melakukan perawatan berkala dan perbaikan (troubleshooting) terhadap perangkat IT inventaris dinas, seperti komputer desktop, laptop, printer, dan UPS.','bi-cpu',1,'2026-10-27 19:34:02','2026-10-28 15:50:40'),(10,'pemeliharaan perangkat lunak (software)','pemeliharaan-perangkat-lunak-software','Melakukan instalasi, pembaruan (update), serta perbaikan (troubleshooting) pada sistem operasi, antivirus, maupun aplikasi internal kepolisian guna memastikan seluruh sistem berjalan lancar, aman, dan tanpa kendala (error).','bi-window',1,'2026-10-28 15:49:45','2026-10-28 15:52:23'),(11,'instalasi dan pengawasan cctv','instalasi-dan-pengawasan-cctv','Memasang, memelihara, dan memastikan sistem kamera pengawas (CCTV) dan Command Center yang terhubung ke jaringan Polda berfungsi optimal untuk keperluan pemantauan keamanan dan lalu lintas.','bi-camera-video',1,'2026-10-28 15:53:09','2026-10-28 15:53:09'),(12,'Dukungan teknik (helpdesk)','dukungan-teknik-helpdesk','Memberikan bantuan teknis kepada satuan kerja (satker) lain yang mengalami kendala dengan perangkat IT atau aplikasi internal kepolisian (seperti sistem pelaporan, database personel, dll).','bi-headset',1,'2026-10-28 15:54:01','2026-10-28 15:54:01'),(13,'Fasilitas video conference','fasilitas-video-conference','Menyiapkan perangkat audio, visual, dan koneksi jaringan untuk kegiatan rapat atau pengarahan jarak jauh (Video Conference) yang melibatkan Mabes Polri, Polda, dan jajaran di bawahnya.','bi-person-video3',1,'2026-10-28 15:55:12','2026-10-28 15:55:12');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_22_000001_add_role_and_profile_fields_to_users_table',1),(5,'2026_10_22_000002_create_categories_table',1),(6,'2026_10_22_000002b_create_padals_and_anggota_table',1),(7,'2026_10_22_000003_create_tickets_table',1),(8,'2026_10_22_000005_create_ticket_ratings_table',1),(9,'2026_10_22_000006_create_ticket_status_histories_table',1),(10,'2026_10_23_000001_add_account_approval_status_to_users_table',2),(11,'2026_10_23_000002_add_email_verification_requirement_to_users_table',3),(12,'2026_10_26_000001_create_ticket_technician_assignments_table',4),(13,'2026_11_02_000001_add_activity_schedule_to_tickets_table',5),(14,'2026_11_24_122805_create_notifications_table',6),(15,'2026_11_25_000001_create_push_subscriptions_table',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `padal_anggota`
--

DROP TABLE IF EXISTS `padal_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `padal_anggota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `padal_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tanggal_bergabung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `padal_anggota_padal_id_user_id_unique` (`padal_id`,`user_id`),
  KEY `padal_anggota_user_id_foreign` (`user_id`),
  CONSTRAINT `padal_anggota_padal_id_foreign` FOREIGN KEY (`padal_id`) REFERENCES `padals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `padal_anggota_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `padal_anggota`
--

LOCK TABLES `padal_anggota` WRITE;
/*!40000 ALTER TABLE `padal_anggota` DISABLE KEYS */;
INSERT INTO `padal_anggota` VALUES (8,7,84,'2026-10-26 17:15:47','2026-10-26 17:15:47','2026-10-26 17:15:47');
/*!40000 ALTER TABLE `padal_anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `padals`
--

DROP TABLE IF EXISTS `padals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `padals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `nama_padal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `padals_user_id_foreign` (`user_id`),
  CONSTRAINT `padals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `padals`
--

LOCK TABLES `padals` WRITE;
/*!40000 ALTER TABLE `padals` DISABLE KEYS */;
INSERT INTO `padals` VALUES (7,80,'Regu Padal Noorma N21','Unit Perwira Pengendali Noorma N21','aktif','2026-10-26 16:28:32','2026-10-26 16:28:32');
/*!40000 ALTER TABLE `padals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_subscriptions`
--

DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `endpoint` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endpoint_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_user_id_endpoint_hash_unique` (`user_id`,`endpoint_hash`),
  CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_subscriptions`
--

LOCK TABLES `push_subscriptions` WRITE;
/*!40000 ALTER TABLE `push_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('DhP70F7k4xBYuS2vZvEUiJtiJehtu9YKRUlaylmb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0','eyJfdG9rZW4iOiJZU1lVVnlja3VxM0JTTVY1ZU9IVGNaTWFhVnZMSnNJajl1S1BPYjc0IiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==',1795394662),('YGh2oJcTedfXBOxN5qRSnbRktDeZnqj8LFgKi5xJ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36 Edg/158.0.0.0','eyJfdG9rZW4iOiJuRWxVT2xnODBFZGdZMnJKSEtsaEpXa3hJRUlHQmZtWTlyOVF4SXMxIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC91c2VycyIsInJvdXRlIjoiYWRtaW4udXNlcnMuaW5kZXgifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiNzNiMWQwNWJiZTM4Mzk1YWIzMjU2MzgzNGJlMjc1MTk4MTY2YWI5OWQ0OTc5YWM4OWI4NzZkMTI1Y2IxNTAzNiJ9',1795570799);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_ratings`
--

DROP TABLE IF EXISTS `ticket_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `padal_id` bigint unsigned DEFAULT NULL,
  `rating` tinyint unsigned NOT NULL,
  `review` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_ratings_ticket_id_unique` (`ticket_id`),
  KEY `ticket_ratings_user_id_foreign` (`user_id`),
  KEY `ticket_ratings_padal_id_foreign` (`padal_id`),
  CONSTRAINT `ticket_ratings_padal_id_foreign` FOREIGN KEY (`padal_id`) REFERENCES `padals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_ratings_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_ratings`
--

LOCK TABLES `ticket_ratings` WRITE;
/*!40000 ALTER TABLE `ticket_ratings` DISABLE KEYS */;
INSERT INTO `ticket_ratings` VALUES (2,12,82,7,3,'kerja nya cepat','2026-10-26 17:18:33','2026-10-26 17:18:33'),(3,13,82,7,5,'data aman terkendali','2026-10-26 17:48:19','2026-10-26 17:48:19');
/*!40000 ALTER TABLE `ticket_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_status_histories`
--

DROP TABLE IF EXISTS `ticket_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_status_histories_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_status_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `ticket_status_histories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_status_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_status_histories`
--

LOCK TABLES `ticket_status_histories` WRITE;
/*!40000 ALTER TABLE `ticket_status_histories` DISABLE KEYS */;
INSERT INTO `ticket_status_histories` VALUES (13,11,80,NULL,'menunggu_verifikasi','Laporan permasalahan baru berhasil dibuat oleh pelapor (Noorma N21).','2026-10-25 18:24:48','2026-10-25 18:24:48'),(14,11,1,'menunggu_verifikasi','menunggu_konfirmasi_padal','Laporan dikonfirmasi oleh Administrator (Administrator TIK POLDA) dan ditugaskan kepada sentosa (Ketua: -).','2026-10-25 18:26:22','2026-10-25 18:26:22'),(15,12,82,NULL,'menunggu_verifikasi','Laporan permasalahan baru berhasil dibuat oleh pelapor (maida).','2026-10-26 16:25:14','2026-10-26 16:25:14'),(16,12,1,'menunggu_verifikasi','menunggu_konfirmasi_padal','Laporan dikonfirmasi oleh Administrator (Administrator TIK POLDA) dan ditugaskan kepada Regu Padal suci (Ketua: suci).','2026-10-26 16:26:29','2026-10-26 16:26:29'),(17,11,NULL,'menunggu_konfirmasi_padal','menunggu_konfirmasi_padal','Koreksi relasi penugasan: unit Padal sentosa tidak terhubung dengan akun Padal. Tiket dialihkan ke Regu Padal Noorma N21 tanpa mengubah status.','2026-10-26 16:51:49','2026-10-26 16:51:49'),(18,12,NULL,'menunggu_konfirmasi_padal','menunggu_konfirmasi_padal','Koreksi relasi penugasan: unit Padal Regu Padal suci tidak terhubung dengan akun Padal. Tiket dialihkan ke Regu Padal Noorma N21 tanpa mengubah status.','2026-10-26 16:51:49','2026-10-26 16:51:49'),(19,12,80,'menunggu_konfirmasi_padal','dalam_penanganan','Penugasan DITERIMA oleh Perwira Pengendali (Noorma N21 - Regu Padal Noorma N21).','2026-10-26 16:53:23','2026-10-26 16:53:23'),(20,11,80,'menunggu_konfirmasi_padal','dalam_penanganan','Penugasan DITERIMA oleh Perwira Pengendali (Noorma N21 - Regu Padal Noorma N21).','2026-10-26 17:15:28','2026-10-26 17:15:28'),(21,12,80,'dalam_penanganan','selesai','Pekerjaan dinyatakan SELESAI oleh Padal (Noorma N21 - Regu Padal Noorma N21). Catatan Tindakan: jaringan sudah aman dan terkendali. Tiket siap dinilai oleh pelapor.','2026-10-26 17:17:47','2026-10-26 17:17:47'),(22,12,82,'selesai','selesai_dinilai','Pelapor memberikan penilaian pelayanan: 3 Bintang. Ulasan: kerja nya cepat','2026-10-26 17:18:33','2026-10-26 17:18:33'),(23,12,80,'selesai_dinilai','selesai_dinilai','Pemulihan catatan relasi penugasan Teknisi berdasarkan konfirmasi bahwa Rizki menangani tiket ini. Waktu penugasan dicatat mengikuti waktu mulai penanganan yang tersimpan.','2026-10-26 17:29:31','2026-10-26 17:29:31'),(24,13,82,NULL,'menunggu_verifikasi','Laporan permasalahan baru berhasil dibuat oleh pelapor (alpin).','2026-10-26 17:43:39','2026-10-26 17:43:39'),(25,13,1,'menunggu_verifikasi','menunggu_konfirmasi_padal','Laporan dikonfirmasi oleh Administrator (Administrator TIK POLDA) dan ditugaskan kepada Regu Padal Noorma N21 (Ketua: Noorma N21).','2026-10-26 17:44:16','2026-10-26 17:44:16'),(26,13,80,'menunggu_konfirmasi_padal','dalam_penanganan','Penugasan DITERIMA oleh Perwira Pengendali (Noorma N21 - Regu Padal Noorma N21).','2026-10-26 17:44:57','2026-10-26 17:44:57'),(27,13,80,'dalam_penanganan','selesai','Pekerjaan dinyatakan SELESAI oleh Padal (Noorma N21 - Regu Padal Noorma N21). Catatan Tindakan: database berhasil di backup. Tiket siap dinilai oleh pelapor.','2026-10-26 17:47:02','2026-10-26 17:47:02'),(28,13,82,'selesai','selesai_dinilai','Pelapor memberikan penilaian pelayanan: 5 Bintang. Ulasan: data aman terkendali','2026-10-26 17:48:19','2026-10-26 17:48:19'),(29,14,82,NULL,'menunggu_verifikasi','Laporan permasalahan baru berhasil dibuat oleh pelapor (alpin).','2026-10-27 19:02:40','2026-10-27 19:02:40'),(30,11,80,'dalam_penanganan','selesai','Pekerjaan dinyatakan SELESAI oleh Padal (Noorma N21 - Regu Padal Noorma N21). Catatan Tindakan: okasdaqsdasdasdasda. Tiket siap dinilai oleh pelapor.','2026-10-27 19:08:37','2026-10-27 19:08:37'),(31,14,1,'menunggu_verifikasi','menunggu_konfirmasi_padal','Laporan dikonfirmasi oleh Administrator (Administrator TIK POLDA) dan ditugaskan kepada Regu Padal Noorma N21 (Ketua: Noorma N21).','2026-10-27 19:11:51','2026-10-27 19:11:51'),(32,14,80,'menunggu_konfirmasi_padal','dalam_penanganan','Penugasan DITERIMA oleh Perwira Pengendali (Noorma N21 - Regu Padal Noorma N21).','2026-10-27 19:12:47','2026-10-27 19:12:47');
/*!40000 ALTER TABLE `ticket_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_technician_assignments`
--

DROP TABLE IF EXISTS `ticket_technician_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_technician_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NOT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_technician_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `ticket_technician_assignments_ticket_id_released_at_index` (`ticket_id`,`released_at`),
  KEY `ticket_technician_assignments_user_id_released_at_index` (`user_id`,`released_at`),
  CONSTRAINT `ticket_technician_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_technician_assignments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_technician_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_technician_assignments`
--

LOCK TABLES `ticket_technician_assignments` WRITE;
/*!40000 ALTER TABLE `ticket_technician_assignments` DISABLE KEYS */;
INSERT INTO `ticket_technician_assignments` VALUES (1,11,84,80,'2026-10-26 17:24:39',NULL,'2026-10-26 17:24:39','2026-10-26 17:24:39'),(2,12,84,80,'2026-10-26 16:53:23',NULL,'2026-10-26 17:29:31','2026-10-26 17:29:31'),(3,13,84,80,'2026-10-26 17:45:02',NULL,'2026-10-26 17:45:02','2026-10-26 17:45:02');
/*!40000 ALTER TABLE `ticket_technician_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `padal_id` bigint unsigned DEFAULT NULL,
  `diperiksa_oleh` bigint unsigned DEFAULT NULL,
  `ditugaskan_oleh` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_start_time` time DEFAULT NULL,
  `event_end_time` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu_verifikasi',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `resolution_attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_ticket_number_unique` (`ticket_number`),
  KEY `tickets_user_id_foreign` (`user_id`),
  KEY `tickets_category_id_foreign` (`category_id`),
  KEY `tickets_padal_id_foreign` (`padal_id`),
  KEY `tickets_diperiksa_oleh_foreign` (`diperiksa_oleh`),
  KEY `tickets_ditugaskan_oleh_foreign` (`ditugaskan_oleh`),
  KEY `tickets_status_index` (`status`),
  KEY `tickets_event_schedule_index` (`event_date`,`event_start_time`),
  KEY `tickets_submission_order_index` (`created_at`,`id`),
  CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tickets_diperiksa_oleh_foreign` FOREIGN KEY (`diperiksa_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_ditugaskan_oleh_foreign` FOREIGN KEY (`ditugaskan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_padal_id_foreign` FOREIGN KEY (`padal_id`) REFERENCES `padals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (11,'TKT-20261026-GTFI',80,1,7,1,1,'jaringan internet tidak stabil','Bid TIK POLDA',NULL,NULL,NULL,'kendala yang di alami yaitu gangguan jaringan wifi pada gedung reskrim','attachments/tickets/UcmMHXGpsiHvCD3F1IHNCokaUQsXiRDttdozlsUl.pdf','selesai',NULL,NULL,'okasdaqsdasdasdasda',NULL,'2026-10-25 18:26:22','2026-10-26 16:51:49','2026-10-26 17:15:28','2026-10-27 19:08:37','2026-10-25 18:24:48','2026-10-27 19:08:37'),(12,'TKT-20261027-JZL0',82,1,7,1,1,'jaringan internet terputus','Bid TIK POLDA',NULL,NULL,NULL,'jaringan internet terputus sehingga pengerjaan di kantor terputus','attachments/tickets/QKMcyE4Ub4EEXZ1QBfBFpX8D90lPYHSkNnaX0hS5.pdf','selesai_dinilai',NULL,NULL,'jaringan sudah aman dan terkendali',NULL,'2026-10-26 16:26:29','2026-10-26 16:51:49','2026-10-26 16:53:23','2026-10-26 17:17:47','2026-10-26 16:25:14','2026-10-26 17:18:33'),(13,'TKT-20261027-SIPI',82,6,7,1,1,'hak akses database','Bid TIK POLDA',NULL,NULL,NULL,'kendala yang saya alami yaitu database nya tidak bisa di backup datanya','attachments/tickets/XcTznMgWjExjkUWaLIpM3EyqPwuMI0MljeKb9VDr.pdf','selesai_dinilai',NULL,NULL,'database berhasil di backup',NULL,'2026-10-26 17:44:16','2026-10-26 17:44:16','2026-10-26 17:44:57','2026-10-26 17:47:02','2026-10-26 17:43:39','2026-10-26 17:48:19'),(14,'TKT-20261028-EMNA',82,1,7,1,1,'hgygihigugiu','Bid TIK POLDA',NULL,NULL,NULL,'ytfyggughihu','attachments/tickets/W4MezKoGHtqeGK8BeYLGzw2j4oSj555d1gtS2wcn.jpg','dalam_penanganan',NULL,NULL,NULL,NULL,'2026-10-27 19:11:51','2026-10-27 19:11:51','2026-10-27 19:12:47',NULL,'2026-10-27 19:02:40','2026-10-27 19:12:47');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `nrp_nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `satuan_kerja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `approval_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `requires_email_verification` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_approval_status_index` (`approval_status`),
  KEY `users_requires_email_verification_index` (`requires_email_verification`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator TIK POLDA','nurmanur048@gmail.com','2026-10-25 16:41:49','$2y$12$HuoKBXDOSGhQBEAAjP1tEO28vHNMUlR2ppOmnAqgg6HYsmErN6S0K','admin','198801012010121001','081234567890',NULL,'Bid TIK POLDA',1,'active',0,NULL,'2026-10-22 16:09:26','2026-10-25 16:41:49'),(80,'Noorma N21','noorma@mhs.politala.ac.id','2026-10-25 18:22:37','$2y$12$2SXaeFaq1OWDk36E2W2VxOBIz8hOXkIZUafQTAp0zHFKhL6kpcDSi','padal','198801012010121001','082254724885',NULL,'Bid TIK POLDA',1,'active',0,NULL,'2026-10-25 18:21:51','2026-10-26 17:41:28'),(82,'alpin','noormanur5@gmail.com','2026-10-26 16:16:56','$2y$12$BRcyUcKWjYLUP9ehXNmhYeMeisUkBq/63ijuQi.C7YfeL0Yz2f8Xa','user','24013010','081234567890',NULL,'Bid TIK POLDA',1,'active',0,NULL,'2026-10-26 16:16:07','2026-10-26 17:42:21'),(84,'rizki','noormanor784@gmail.com','2026-10-26 17:13:43','$2y$12$BTTZdRrGFuwJHgwirRKdvuv2Lot.0ts9/5M1F1q.iDn6IUMlprjou','teknisi','2401301075','082254724885','Jl raya kandangan baru rt03/rw01','SPKT POLDA',1,'active',0,'SNsDrikjgubkAtp3bo7A9yqgt4iBZisIAJoBMTDTC5mfDNGxb88QzB2WS9ZK','2026-10-26 17:09:48','2026-10-26 17:14:01');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'project_pkl'
--

--
-- Dumping routines for database 'project_pkl'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25  9:50:35
