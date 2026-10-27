-- EDUKA full export for verified source data; target only eduka_v1.
CREATE DATABASE IF NOT EXISTS `eduka_v1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eduka_v1`;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `score` decimal(8,2) DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` bigint unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_submissions_assignment_id_user_id_unique` (`assignment_id`,`user_id`),
  KEY `assignment_submissions_user_id_foreign` (`user_id`),
  KEY `assignment_submissions_graded_by_foreign` (`graded_by`),
  KEY `assignment_submissions_assignment_id_status_index` (`assignment_id`,`status`),
  CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignment_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `assignment_submissions` VALUES (1,7,3,'awafegrdg','assignments/bc8HxCrTbmRPbC9IaMjcWzsfabaEogOJ67OalKpY.pdf','Modul 1 - DW&DM.pdf','graded',90.00,'teruk berkembang',1,'2026-10-26 23:27:27','2026-10-26 23:28:38','2026-10-26 23:27:27','2026-10-26 23:28:38'),(2,1,3,NULL,'assignments/dcQnrB4lbDJsHFM6BDFukker6j7yyCfHG6DczWZU.pdf','Modul 1 - DW&DM.pdf','graded',90.00,NULL,1,'2026-10-26 23:32:47','2026-10-26 23:35:04','2026-10-26 23:32:47','2026-10-26 23:35:04'),(3,5,3,'rdtfyguh','assignments/YHpDkzXolKYiiBJba0iNJBXQ76hKznBu2wZakHvN.pdf','Modul 2 - DW&DM.pdf','graded',100.00,NULL,1,'2026-10-26 23:33:13','2026-10-26 23:35:11','2026-10-26 23:33:13','2026-10-26 23:35:11'),(4,6,3,'klkjfdgh','assignments/HG9JoQOegd6oy5rVYViRPE4cfGZixghrqzQ7CncG.pdf','Modul 2 - DW&DM.pdf','graded',80.00,NULL,1,'2026-10-26 23:33:31','2026-10-26 23:35:17','2026-10-26 23:33:31','2026-10-26 23:35:17'),(5,4,3,'rytuydhf','assignments/KTnmzBKgzuiVj10AxENPJKsO2AJ7fW8Xoidrd4Qk.pdf','Modul 2 - DW&DM.pdf','graded',80.00,NULL,1,'2026-10-26 23:33:59','2026-10-26 23:35:25','2026-10-26 23:33:59','2026-10-26 23:35:25'),(6,8,3,'Rancangan halaman dashboard dengan user flow, wireframe, token warna, state komponen, dan catatan aksesibilitas.',NULL,NULL,'graded',90.00,'Rancangan UI/UX lulus melalui review pengajar.',1,'2026-10-26 23:47:42','2026-10-26 23:47:58','2026-10-26 23:47:42','2026-10-26 23:47:58'),(7,9,3,'Skema database pengelolaan kelas dengan primary key, foreign key, normalisasi, query CRUD, dan contoh JOIN.',NULL,NULL,'graded',90.00,'Skema database dan query SQL lulus melalui review pengajar.',1,'2026-10-26 23:47:42','2026-10-26 23:47:58','2026-10-26 23:47:42','2026-10-26 23:47:58');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_at` timestamp NULL DEFAULT NULL,
  `max_file_kb` int unsigned NOT NULL DEFAULT '5120',
  `allowed_file_types` json DEFAULT NULL,
  `points` decimal(8,2) NOT NULL DEFAULT '100.00',
  `pass_score` decimal(5,2) NOT NULL DEFAULT '70.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_course_id_foreign` (`course_id`),
  KEY `assignments_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `assignments` VALUES (1,1,2,'Tugas HTML: Profil Sederhana','Buat halaman profil sederhana dengan HTML semantik, heading yang teratur, navigasi, dan formulir kontak yang memiliki label.','2026-11-02 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:46:52'),(2,2,3,'Proyek akhir Prinsip UI Design','Kirimkan hasil praktik terbaikmu.',NULL,5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(3,3,5,'Proyek akhir Manajemen Proyek Digital','Kirimkan hasil praktik terbaikmu.',NULL,5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,1,7,'Tugas CSS: Halaman Responsif','Buat halaman responsif dengan layout flexbox atau grid, tipografi terbaca, dan breakpoint yang nyaman pada layar mobile.','2026-11-05 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:28','2026-10-26 22:46:52'),(5,1,8,'Tugas JavaScript: Interaksi Sederhana','Tambahkan satu interaksi JavaScript yang berguna, seperti toggle detail, validasi form, atau filter daftar, lalu jelaskan cara kerjanya.','2026-11-09 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:46:52'),(6,1,9,'Tugas Akhir: Integrasi HTML CSS JavaScript','Gabungkan HTML, CSS, dan JavaScript menjadi satu halaman web yang utuh, responsif, mudah digunakan, dan siap dipresentasikan.','2026-11-16 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:46:52'),(7,4,14,'Proyek akhir Laravel untuk Pemula','Bangun aplikasi CRUD sederhana dengan migration, model, relasi, form request/validasi, Blade view, dan pengujian feature untuk jalur utama.','2026-11-16 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:46:52'),(8,5,19,'Proyek akhir UI/UX Design Dasar','Rancang satu halaman aplikasi dari user flow hingga high-fidelity mockup. Sertakan token warna, tipografi, spacing, state komponen, dan catatan aksesibilitas.','2026-11-16 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:46:52'),(9,6,24,'Proyek akhir Database dan SQL','Rancang skema database untuk aplikasi pengelolaan kelas. Sertakan tabel, key, relasi, contoh query CRUD, JOIN, dan alasan normalisasi yang dipilih.','2026-11-16 22:46:52',5120,'[\"pdf\", \"zip\"]',100.00,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:46:52');
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `categories` VALUES (1,'Pemrograman','pemrograman','Bangun fondasi teknis untuk membuat produk digital.','2026-10-26 22:22:53','2026-10-26 22:22:53'),(2,'Desain','desain','Rancang pengalaman yang mudah dipahami dan digunakan.','2026-10-26 22:22:53','2026-10-26 22:22:53'),(3,'Bisnis','bisnis','Kembangkan cara berpikir dan praktik kerja yang berdampak.','2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,'Database','database','Kelola data dengan struktur yang aman dan mudah dirawat.','2026-10-26 22:45:28','2026-10-26 22:45:28');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificates_course_id_user_id_unique` (`course_id`,`user_id`),
  UNIQUE KEY `certificates_code_unique` (`code`),
  KEY `certificates_user_id_foreign` (`user_id`),
  CONSTRAINT `certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `certificates` VALUES (1,1,3,'EDK-OPHPKT6675WP','2026-10-26 23:47:17','2026-10-26 23:47:17','2026-10-26 23:47:17'),(2,4,3,'EDK-W7XSMELFT3OG','2026-10-26 23:47:36','2026-10-26 23:47:36','2026-10-26 23:47:36'),(3,5,3,'EDK-TOGW5LDJX4JF','2026-10-26 23:47:58','2026-10-26 23:47:58','2026-10-26 23:47:58'),(4,6,3,'EDK-JE8V7AXZW8NK','2026-10-26 23:47:58','2026-10-26 23:47:58','2026-10-26 23:47:58');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_prerequisites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `prerequisite_course_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_prerequisites_course_id_prerequisite_course_id_unique` (`course_id`,`prerequisite_course_id`),
  KEY `course_prerequisites_prerequisite_course_id_foreign` (`prerequisite_course_id`),
  CONSTRAINT `course_prerequisites_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_prerequisites_prerequisite_course_id_foreign` FOREIGN KEY (`prerequisite_course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `instructor_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pemula',
  `visibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `duration_minutes` int unsigned NOT NULL DEFAULT '0',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_category_id_foreign` (`category_id`),
  KEY `courses_status_visibility_index` (`status`,`visibility`),
  KEY `courses_instructor_id_status_index` (`instructor_id`,`status`),
  CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courses_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `courses` VALUES (1,1,1,'Dasar Pemrograman Web','dasar-pemrograman-web','Bangun fondasi web modern dari HTML, CSS, dan JavaScript.','Kelas praktis untuk memahami cara kerja web dan membuat halaman yang responsif.','pemula','public','published',360,0.00,'2026-10-26 22:25:34','2026-10-26 22:22:53','2026-10-26 22:25:34'),(2,2,1,'Prinsip UI Design','prinsip-ui-design','Pelajari prinsip visual untuk membuat antarmuka yang jelas.','Dari hirarki visual hingga prototipe yang siap diuji.','menengah','public','published',360,0.00,'2026-10-26 22:25:34','2026-10-26 22:22:53','2026-10-26 22:25:34'),(3,3,1,'Manajemen Proyek Digital','manajemen-proyek-digital','Susun ritme kerja tim dan kirimkan produk dengan konsisten.','Praktik kerja kolaboratif untuk proyek digital yang lebih terarah.','pemula','public','published',360,0.00,'2026-10-26 22:25:34','2026-10-26 22:22:53','2026-10-26 22:25:34'),(4,1,1,'Laravel untuk Pemula','laravel-untuk-pemula','Bangun aplikasi Laravel dengan routing, Blade, model, dan CRUD.','Kelas bertahap untuk memahami fondasi Laravel dan menerapkannya pada aplikasi CRUD kecil yang tervalidasi.','pemula','public','published',360,0.00,'2026-10-26 22:45:29','2026-10-26 22:45:29','2026-10-26 22:45:29'),(5,2,1,'UI/UX Design Dasar','ui-ux-design-dasar','Rancang antarmuka yang jelas, konsisten, dan mudah digunakan.','Pelajari cara menerjemahkan kebutuhan pengguna menjadi user flow, wireframe, dan rancangan antarmuka yang dapat diuji.','pemula','public','published',360,0.00,'2026-10-26 22:45:29','2026-10-26 22:45:29','2026-10-26 22:45:29'),(6,4,1,'Database dan SQL','database-dan-sql','Bangun fondasi database relasional dan query SQL yang aman.','Kelas praktik untuk merancang tabel, menjaga integritas relasi, dan menulis query SQL untuk kebutuhan aplikasi sehari-hari.','pemula','public','published',360,0.00,'2026-10-26 22:45:29','2026-10-26 22:45:29','2026-10-26 22:45:29');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollments_course_id_user_id_unique` (`course_id`,`user_id`),
  KEY `enrollments_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `enrollments` VALUES (1,1,3,'completed','2026-10-26 22:25:34','2026-10-26 23:47:17','2026-10-26 22:22:53','2026-10-26 23:47:17'),(2,4,3,'completed','2026-10-26 23:07:29','2026-10-26 23:47:36','2026-10-26 23:07:29','2026-10-26 23:47:36'),(3,5,3,'completed','2026-10-26 23:46:56','2026-10-26 23:47:58','2026-10-26 23:46:56','2026-10-26 23:47:58'),(4,6,3,'completed','2026-10-26 23:46:56','2026-10-26 23:47:58','2026-10-26 23:46:56','2026-10-26 23:47:58');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_progress_lesson_id_user_id_unique` (`lesson_id`,`user_id`),
  KEY `lesson_progress_user_id_foreign` (`user_id`),
  CONSTRAINT `lesson_progress_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `lesson_progress` VALUES (1,1,3,'2026-10-26 22:52:24','2026-10-26 23:52:08','2026-10-26 22:23:02','2026-10-26 23:52:08'),(2,2,3,'2026-10-26 22:52:33','2026-10-26 23:36:14','2026-10-26 22:31:51','2026-10-26 23:36:14'),(3,7,3,'2026-10-26 22:52:41','2026-10-26 23:36:15','2026-10-26 22:52:36','2026-10-26 23:36:15'),(4,8,3,'2026-10-26 22:52:50','2026-10-26 23:44:47','2026-10-26 22:52:46','2026-10-26 23:44:47'),(5,9,3,'2026-10-26 22:52:56','2026-10-26 23:44:46','2026-10-26 22:52:53','2026-10-26 23:44:46'),(6,10,3,'2026-10-26 23:07:39','2026-10-26 23:30:22','2026-10-26 23:07:34','2026-10-26 23:30:22'),(7,11,3,'2026-10-26 23:07:46','2026-10-26 23:07:46','2026-10-26 23:07:42','2026-10-26 23:07:46'),(8,12,3,'2026-10-26 23:07:54','2026-10-26 23:07:55','2026-10-26 23:07:49','2026-10-26 23:07:55'),(9,13,3,'2026-10-26 23:08:03','2026-10-26 23:30:32','2026-10-26 23:07:58','2026-10-26 23:30:32'),(10,14,3,'2026-10-26 23:08:10','2026-10-26 23:30:32','2026-10-26 23:08:07','2026-10-26 23:30:32'),(11,15,3,'2026-10-26 23:47:01','2026-10-26 23:47:01','2026-10-26 23:47:01','2026-10-26 23:47:01'),(12,16,3,'2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02'),(13,17,3,'2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02'),(14,18,3,'2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02','2026-10-26 23:47:02'),(15,19,3,'2026-10-26 23:47:02','2026-10-26 23:47:03','2026-10-26 23:47:02','2026-10-26 23:47:03'),(16,20,3,'2026-10-26 23:47:03','2026-10-26 23:52:13','2026-10-26 23:47:03','2026-10-26 23:52:13'),(17,21,3,'2026-10-26 23:47:03','2026-10-26 23:47:03','2026-10-26 23:47:03','2026-10-26 23:47:03'),(18,22,3,'2026-10-26 23:47:03','2026-10-26 23:47:04','2026-10-26 23:47:03','2026-10-26 23:47:04'),(19,23,3,'2026-10-26 23:47:04','2026-10-26 23:47:04','2026-10-26 23:47:04','2026-10-26 23:47:04'),(20,24,3,'2026-10-26 23:47:04','2026-10-26 23:47:04','2026-10-26 23:47:04','2026-10-26 23:47:04');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'article',
  `position` int unsigned NOT NULL DEFAULT '1',
  `estimated_minutes` int unsigned NOT NULL DEFAULT '10',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lessons_course_id_slug_unique` (`course_id`,`slug`),
  UNIQUE KEY `lessons_course_id_position_unique` (`course_id`,`position`),
  CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `lessons` VALUES (1,1,'Pengantar pemrograman web','dasar-pemrograman-web-modul-01','Kenali browser, server, dan alur request-response.','Web tersusun dari browser sebagai client, server sebagai penyedia resource, dan protokol HTTP sebagai bahasa komunikasi. Pahami alur ini sebelum menulis kode.','article',1,35,1,1,'2026-10-26 22:22:53','2026-10-26 22:45:28'),(2,1,'Struktur HTML semantik','dasar-pemrograman-web-modul-02','Susun dokumen dengan elemen yang bermakna.','Gunakan heading berurutan, landmark seperti header/main/footer, serta elemen form yang memiliki label. Struktur semantik membantu aksesibilitas dan SEO.','article',2,35,1,1,'2026-10-26 22:22:53','2026-10-26 22:45:28'),(3,2,'Memulai perjalanan','prinsip-ui-design-modul-01','Kenali tujuan belajar dan peta kelas.','Materi pembuka untuk membantu kamu memulai.','article',1,20,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,2,'Praktik inti','prinsip-ui-design-modul-02','Terapkan konsep lewat latihan.','Latihan inti dan refleksi singkat.','article',2,35,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(5,3,'Memulai perjalanan','manajemen-proyek-digital-modul-01','Kenali tujuan belajar dan peta kelas.','Materi pembuka untuk membantu kamu memulai.','article',1,20,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(6,3,'Praktik inti','manajemen-proyek-digital-modul-02','Terapkan konsep lewat latihan.','Latihan inti dan refleksi singkat.','article',2,35,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(7,1,'CSS dan layout responsif','dasar-pemrograman-web-modul-03','Atur tampilan menggunakan box model, flexbox, dan grid.','Mulai dari box model, lalu gunakan flexbox untuk satu dimensi dan grid untuk dua dimensi. Tambahkan breakpoint hanya ketika konten membutuhkannya.','article',3,35,1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(8,1,'JavaScript dasar','dasar-pemrograman-web-modul-04','Tambahkan perilaku interaktif yang terukur.','Pelajari variabel, fungsi, event listener, dan manipulasi DOM. Pisahkan data, perilaku, dan presentasi agar perubahan tetap mudah dilacak.','article',4,35,1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(9,1,'Praktik halaman web','dasar-pemrograman-web-modul-05','Gabungkan struktur, gaya, dan interaksi menjadi satu halaman.','Buat halaman profil produk sederhana dengan navigasi, kartu informasi, form kontak, dan validasi interaksi dasar. Uji pada layar sempit sebelum menyelesaikan pekerjaan.','article',5,35,1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(10,4,'Instalasi dan struktur Laravel','laravel-untuk-pemula-modul-01','Pahami folder aplikasi dan alur bootstrap.','Kenali routes, controllers, models, migrations, resources, dan konfigurasi environment. Simpan rahasia di environment, bukan di source code.','article',1,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(11,4,'Routing dan controller','laravel-untuk-pemula-modul-02','Hubungkan URL dengan aksi aplikasi yang jelas.','Gunakan named route, route model binding, dan controller yang fokus. Validasi kepemilikan resource sebelum menjalankan perubahan.','article',2,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(12,4,'Blade template','laravel-untuk-pemula-modul-03','Bangun tampilan reusable dengan layout dan component.','Gunakan layout, section, props, escaping default, dan komponen Blade. Hindari menaruh query database di view.','article',3,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(13,4,'Migration, model, dan relasi','laravel-untuk-pemula-modul-04','Rancang data yang dapat dipertanggungjawabkan.','Migration mendefinisikan struktur, model menjaga aturan data, dan relasi menjelaskan koneksi antar entitas. Tambahkan foreign key dan unique constraint yang relevan.','article',4,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(14,4,'Validasi form dan CRUD','laravel-untuk-pemula-modul-05','Simpan data dengan validasi dan otorisasi server.','Validasi input di server, batasi mass assignment, gunakan CSRF, dan uji create-read-update-delete dengan database testing terpisah.','article',5,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(15,5,'Prinsip desain antarmuka','ui-ux-design-dasar-modul-01','Pahami hirarki, affordance, feedback, dan konsistensi.','Antarmuka yang baik memberi orientasi, menunjukkan aksi yang tersedia, dan memberi feedback setelah interaksi. Prioritaskan kejelasan sebelum dekorasi.','article',1,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(16,5,'User flow dan wireframe','ui-ux-design-dasar-modul-02','Petakan tujuan pengguna dari awal hingga selesai.','Mulai dari tujuan, susun langkah minimum, lalu visualisasikan dengan wireframe. Tandai kondisi sukses, error, empty state, dan jalur kembali.','article',2,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(17,5,'Tipografi, warna, dan spacing','ui-ux-design-dasar-modul-03','Gunakan token visual untuk membangun ritme.','Pilih skala tipografi, kontras warna, dan jarak yang konsisten. Uji teks terhadap background terang dan gelap agar informasi tetap terbaca.','article',3,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(18,5,'Komponen dan konsistensi desain','ui-ux-design-dasar-modul-04','Buat pola reusable untuk mempercepat iterasi.','Dokumentasikan button, input, card, badge, dan navigasi beserta state default, hover, focus, disabled, dan error.','article',4,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(19,5,'Responsivitas dan aksesibilitas','ui-ux-design-dasar-modul-05','Rancang pengalaman yang inklusif di berbagai perangkat.','Pastikan target sentuh cukup, keyboard focus terlihat, form memiliki label, tabel tidak memaksa teks terlalu kecil, dan layout dapat mengalir pada mobile.','article',5,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(20,6,'Dasar database relasional','database-dan-sql-modul-01','Kenali tabel, baris, kolom, dan relasi.','Database relasional menyimpan entitas dalam tabel yang saling terhubung melalui key. Mulai dari kebutuhan data sebelum menentukan bentuk tabel.','article',1,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(21,6,'Primary key dan foreign key','database-dan-sql-modul-02','Jaga identitas dan integritas antar tabel.','Primary key mengidentifikasi baris secara unik. Foreign key menghubungkan tabel dan sebaiknya memiliki aturan penghapusan yang sesuai dengan kebutuhan bisnis.','article',2,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(22,6,'SELECT, INSERT, UPDATE, DELETE','database-dan-sql-modul-03','Gunakan operasi CRUD SQL dengan aman.','Gunakan WHERE yang spesifik saat update/delete, parameter binding untuk input, dan transaksi ketika beberapa perubahan harus berhasil bersama.','article',3,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(23,6,'JOIN dan relasi','database-dan-sql-modul-04','Gabungkan data yang tersebar dengan tepat.','INNER JOIN mengambil pasangan yang cocok, LEFT JOIN mempertahankan baris dari tabel kiri. Pahami cardinality agar hasil query tidak menggandakan data.','article',4,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(24,6,'Normalisasi dasar','database-dan-sql-modul-05','Kurangi duplikasi dan anomali data.','Normalisasi memisahkan fakta agar satu informasi disimpan di tempat yang tepat. Pertahankan desain praktis dengan indeks pada kolom yang sering dicari.','article',5,35,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_27_000001_add_eduka_fields_to_users_table',1),(5,'2026_10_27_000002_create_categories_table',1),(6,'2026_10_27_000003_create_courses_table',1),(7,'2026_10_27_000004_create_course_prerequisites_table',1),(8,'2026_10_27_000005_create_enrollments_table',1),(9,'2026_10_27_000006_create_lessons_table',1),(10,'2026_10_27_000007_create_lesson_progress_table',1),(11,'2026_10_27_000008_create_quizzes_table',1),(12,'2026_10_27_000009_create_quiz_questions_table',1),(13,'2026_10_27_000010_create_quiz_options_table',1),(14,'2026_10_27_000011_create_quiz_attempts_table',1),(15,'2026_10_27_000012_create_quiz_answers_table',1),(16,'2026_10_27_000013_create_assignments_table',1),(17,'2026_10_27_000014_create_assignment_submissions_table',1),(18,'2026_10_27_000015_create_notes_table',1),(19,'2026_10_27_000016_create_certificates_table',1),(20,'2026_10_27_000017_add_required_flags_to_learning_content',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notes_course_id_foreign` (`course_id`),
  KEY `notes_lesson_id_foreign` (`lesson_id`),
  KEY `notes_user_id_course_id_index` (`user_id`,`course_id`),
  CONSTRAINT `notes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` bigint unsigned NOT NULL,
  `question_id` bigint unsigned NOT NULL,
  `option_id` bigint unsigned DEFAULT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_answers_attempt_id_question_id_unique` (`attempt_id`,`question_id`),
  KEY `quiz_answers_question_id_foreign` (`question_id`),
  KEY `quiz_answers_option_id_foreign` (`option_id`),
  CONSTRAINT `quiz_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_answers_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `quiz_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quiz_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quiz_answers` VALUES (1,1,1,NULL,NULL,0,'2026-10-26 23:47:16','2026-10-26 23:47:16'),(2,1,4,9,'Untuk menemukan regresi sebelum digunakan',1,'2026-10-26 23:47:16','2026-10-26 23:47:16'),(3,1,5,13,'Data server yang tervalidasi',1,'2026-10-26 23:47:16','2026-10-26 23:47:16'),(4,1,6,17,'Pahami konteks lalu buat perubahan terukur',1,'2026-10-26 23:47:16','2026-10-26 23:47:16'),(5,2,7,21,'Gunakan struktur dan aturan yang jelas',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(6,2,8,25,'Untuk menemukan regresi sebelum digunakan',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(7,2,9,29,'Data server yang tervalidasi',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(8,2,10,33,'Pahami konteks lalu buat perubahan terukur',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(9,3,11,37,'Gunakan struktur dan aturan yang jelas',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(10,3,12,41,'Untuk menemukan regresi sebelum digunakan',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(11,3,13,45,'Data server yang tervalidasi',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(12,3,14,49,'Pahami konteks lalu buat perubahan terukur',1,'2026-10-26 23:47:36','2026-10-26 23:47:36'),(13,4,15,53,'Gunakan struktur dan aturan yang jelas',1,'2026-10-26 23:47:37','2026-10-26 23:47:37'),(14,4,16,57,'Untuk menemukan regresi sebelum digunakan',1,'2026-10-26 23:47:37','2026-10-26 23:47:37'),(15,4,17,61,'Data server yang tervalidasi',1,'2026-10-26 23:47:37','2026-10-26 23:47:37'),(16,4,18,65,'Pahami konteks lalu buat perubahan terukur',1,'2026-10-26 23:47:37','2026-10-26 23:47:37');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `attempt_number` int unsigned NOT NULL DEFAULT '1',
  `score` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_user_id_foreign` (`user_id`),
  KEY `quiz_attempts_quiz_id_user_id_index` (`quiz_id`,`user_id`),
  CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quiz_attempts` VALUES (1,1,3,1,75.00,1,'2026-10-26 23:47:16','2026-10-26 23:47:16','2026-10-26 23:47:16','2026-10-26 23:47:16'),(2,4,3,1,100.00,1,'2026-10-26 23:47:36','2026-10-26 23:47:36','2026-10-26 23:47:36','2026-10-26 23:47:36'),(3,5,3,1,100.00,1,'2026-10-26 23:47:36','2026-10-26 23:47:36','2026-10-26 23:47:36','2026-10-26 23:47:36'),(4,6,3,1,100.00,1,'2026-10-26 23:47:37','2026-10-26 23:47:37','2026-10-26 23:47:37','2026-10-26 23:47:37');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint unsigned NOT NULL,
  `option_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `position` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_options_question_id_position_unique` (`question_id`,`position`),
  CONSTRAINT `quiz_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quiz_options` VALUES (1,1,'Gunakan struktur dan aturan yang jelas',1,1,'2026-10-26 22:22:53','2026-10-26 22:45:28'),(2,1,'Abaikan validasi',0,2,'2026-10-26 22:22:53','2026-10-26 22:45:28'),(3,2,'Membangun kemampuan yang dapat dipraktikkan',1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,2,'Menghafal seluruh materi',0,2,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(5,3,'Membangun kemampuan yang dapat dipraktikkan',1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(6,3,'Menghafal seluruh materi',0,2,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(7,1,'Simpan semua hal di satu tempat',0,3,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(8,1,'Percayakan keamanan pada browser',0,4,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(9,4,'Untuk menemukan regresi sebelum digunakan',1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(10,4,'Agar kode terlihat lebih panjang',0,2,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(11,4,'Untuk menghapus semua data',0,3,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(12,4,'Agar browser menentukan nilai',0,4,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(13,5,'Data server yang tervalidasi',1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(14,5,'Nilai tersembunyi di form',0,2,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(15,5,'Teks pada tombol',0,3,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(16,5,'Cache browser saja',0,4,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(17,6,'Pahami konteks lalu buat perubahan terukur',1,1,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(18,6,'Langsung hapus data lama',0,2,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(19,6,'Lewati validasi',0,3,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(20,6,'Tambahkan data fiktif',0,4,'2026-10-26 22:45:28','2026-10-26 22:45:28'),(21,7,'Gunakan struktur dan aturan yang jelas',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(22,7,'Abaikan validasi',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(23,7,'Simpan semua hal di satu tempat',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(24,7,'Percayakan keamanan pada browser',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(25,8,'Untuk menemukan regresi sebelum digunakan',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(26,8,'Agar kode terlihat lebih panjang',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(27,8,'Untuk menghapus semua data',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(28,8,'Agar browser menentukan nilai',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(29,9,'Data server yang tervalidasi',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(30,9,'Nilai tersembunyi di form',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(31,9,'Teks pada tombol',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(32,9,'Cache browser saja',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(33,10,'Pahami konteks lalu buat perubahan terukur',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(34,10,'Langsung hapus data lama',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(35,10,'Lewati validasi',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(36,10,'Tambahkan data fiktif',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(37,11,'Gunakan struktur dan aturan yang jelas',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(38,11,'Abaikan validasi',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(39,11,'Simpan semua hal di satu tempat',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(40,11,'Percayakan keamanan pada browser',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(41,12,'Untuk menemukan regresi sebelum digunakan',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(42,12,'Agar kode terlihat lebih panjang',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(43,12,'Untuk menghapus semua data',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(44,12,'Agar browser menentukan nilai',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(45,13,'Data server yang tervalidasi',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(46,13,'Nilai tersembunyi di form',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(47,13,'Teks pada tombol',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(48,13,'Cache browser saja',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(49,14,'Pahami konteks lalu buat perubahan terukur',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(50,14,'Langsung hapus data lama',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(51,14,'Lewati validasi',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(52,14,'Tambahkan data fiktif',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(53,15,'Gunakan struktur dan aturan yang jelas',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(54,15,'Abaikan validasi',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(55,15,'Simpan semua hal di satu tempat',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(56,15,'Percayakan keamanan pada browser',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(57,16,'Untuk menemukan regresi sebelum digunakan',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(58,16,'Agar kode terlihat lebih panjang',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(59,16,'Untuk menghapus semua data',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(60,16,'Agar browser menentukan nilai',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(61,17,'Data server yang tervalidasi',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(62,17,'Nilai tersembunyi di form',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(63,17,'Teks pada tombol',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(64,17,'Cache browser saja',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(65,18,'Pahami konteks lalu buat perubahan terukur',1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(66,18,'Langsung hapus data lama',0,2,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(67,18,'Lewati validasi',0,3,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(68,18,'Tambahkan data fiktif',0,4,'2026-10-26 22:45:29','2026-10-26 22:45:29');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint unsigned NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int unsigned NOT NULL DEFAULT '1',
  `points` decimal(8,2) NOT NULL DEFAULT '1.00',
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_questions_quiz_id_position_unique` (`quiz_id`,`position`),
  CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quiz_questions` VALUES (1,1,'Apa prinsip utama yang perlu diterapkan pada web?',1,25.00,'Struktur dan aturan yang jelas membantu aplikasi tetap dapat dipahami, diuji, dan dirawat.','2026-10-26 22:22:53','2026-10-26 22:45:28'),(2,2,'Apa tujuan utama dari kelas ini?',1,10.00,NULL,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(3,3,'Apa tujuan utama dari kelas ini?',1,10.00,NULL,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,1,'Mengapa hasil kerja perlu diuji?',2,25.00,'Pengujian memberikan bukti bahwa perilaku penting tetap benar setelah perubahan.','2026-10-26 22:45:28','2026-10-26 22:45:28'),(5,1,'Apa yang sebaiknya menjadi sumber kebenaran status?',3,25.00,'Status penting harus ditentukan dari data server dan aturan domain, bukan manipulasi client.','2026-10-26 22:45:28','2026-10-26 22:45:28'),(6,1,'Apa langkah yang baik saat menemukan kebutuhan baru?',4,25.00,'Perubahan terukur dengan konteks dan pengujian mengurangi risiko regresi.','2026-10-26 22:45:28','2026-10-26 22:45:28'),(7,4,'Apa prinsip utama yang perlu diterapkan pada laravel?',1,25.00,'Struktur dan aturan yang jelas membantu aplikasi tetap dapat dipahami, diuji, dan dirawat.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(8,4,'Mengapa hasil kerja perlu diuji?',2,25.00,'Pengujian memberikan bukti bahwa perilaku penting tetap benar setelah perubahan.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(9,4,'Apa yang sebaiknya menjadi sumber kebenaran status?',3,25.00,'Status penting harus ditentukan dari data server dan aturan domain, bukan manipulasi client.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(10,4,'Apa langkah yang baik saat menemukan kebutuhan baru?',4,25.00,'Perubahan terukur dengan konteks dan pengujian mengurangi risiko regresi.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(11,5,'Apa prinsip utama yang perlu diterapkan pada design?',1,25.00,'Struktur dan aturan yang jelas membantu aplikasi tetap dapat dipahami, diuji, dan dirawat.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(12,5,'Mengapa hasil kerja perlu diuji?',2,25.00,'Pengujian memberikan bukti bahwa perilaku penting tetap benar setelah perubahan.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(13,5,'Apa yang sebaiknya menjadi sumber kebenaran status?',3,25.00,'Status penting harus ditentukan dari data server dan aturan domain, bukan manipulasi client.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(14,5,'Apa langkah yang baik saat menemukan kebutuhan baru?',4,25.00,'Perubahan terukur dengan konteks dan pengujian mengurangi risiko regresi.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(15,6,'Apa prinsip utama yang perlu diterapkan pada database?',1,25.00,'Struktur dan aturan yang jelas membantu aplikasi tetap dapat dipahami, diuji, dan dirawat.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(16,6,'Mengapa hasil kerja perlu diuji?',2,25.00,'Pengujian memberikan bukti bahwa perilaku penting tetap benar setelah perubahan.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(17,6,'Apa yang sebaiknya menjadi sumber kebenaran status?',3,25.00,'Status penting harus ditentukan dari data server dan aturan domain, bukan manipulasi client.','2026-10-26 22:45:29','2026-10-26 22:45:29'),(18,6,'Apa langkah yang baik saat menemukan kebutuhan baru?',4,25.00,'Perubahan terukur dengan konteks dan pengujian mengurangi risiko regresi.','2026-10-26 22:45:29','2026-10-26 22:45:29');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quizzes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `time_limit_minutes` int unsigned DEFAULT NULL,
  `attempts_allowed` int unsigned NOT NULL DEFAULT '1',
  `pass_score` decimal(5,2) NOT NULL DEFAULT '70.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quizzes_course_id_slug_unique` (`course_id`,`slug`),
  KEY `quizzes_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `quizzes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quizzes_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quizzes` VALUES (1,1,1,'Evaluasi Dasar Pemrograman Web','dasar-pemrograman-web-quiz','Uji pemahamanmu setelah menyelesaikan seluruh modul.',30,2,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:45:28'),(2,2,3,'Evaluasi Prinsip UI Design','prinsip-ui-design-quiz','Uji pemahaman setelah menyelesaikan modul.',NULL,2,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(3,3,5,'Evaluasi Manajemen Proyek Digital','manajemen-proyek-digital-quiz','Uji pemahaman setelah menyelesaikan modul.',NULL,2,70.00,1,1,'2026-10-26 22:22:53','2026-10-26 22:22:53'),(4,4,10,'Evaluasi Laravel untuk Pemula','laravel-untuk-pemula-quiz','Uji pemahamanmu setelah menyelesaikan seluruh modul.',30,2,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(5,5,15,'Evaluasi UI/UX Design Dasar','ui-ux-design-dasar-quiz','Uji pemahamanmu setelah menyelesaikan seluruh modul.',30,2,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29'),(6,6,20,'Evaluasi Database dan SQL','database-dan-sql-quiz','Uji pemahamanmu setelah menyelesaikan seluruh modul.',30,2,70.00,1,1,'2026-10-26 22:45:29','2026-10-26 22:45:29');
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'peserta',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `bio` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `users` VALUES (1,'Raka Aditya','pengajar@eduka.test','pengajar','active',NULL,NULL,'$2y$12$3ckjLdEKbPPMrKCwJFPh6OSvYZRLVV1djpIUpvLaBsrpzQNAkPd8O',NULL,'2026-10-26 22:22:51','2026-10-26 22:22:51'),(2,'Admin EDUKA','admin@eduka.test','admin','active',NULL,NULL,'$2y$12$bflrcB.u6YwyQsVZ9EnG0ekBnzfvZKTvRowBfbRE3RZItShAZ6aR.',NULL,'2026-10-26 22:22:51','2026-10-26 22:22:52'),(3,'Aslam','peserta@eduka.test','peserta','active',NULL,NULL,'$2y$12$sZ.gZaMNOkNHcasDHc8YDOx34hdLvbDNwq8Oq8xJd5zquyVQmQaVi',NULL,'2026-10-26 22:22:52','2026-10-26 23:53:15');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

