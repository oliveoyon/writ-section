-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 15, 2026 at 06:45 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `writ_section`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1774847497;', 1774847497),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1774847497),
('laravel-cache-123|127.0.0.1:timer', 'i:1774847497;', 1774847497),
('laravel-cache-123|127.0.0.1', 'i:1;', 1774847497),
('laravel-cache-admin@email.com|127.0.0.1:timer', 'i:1773166470;', 1773166470),
('laravel-cache-admin@email.com|127.0.0.1', 'i:1;', 1773166470);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
CREATE TABLE IF NOT EXISTS `cases` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `lawyer_id` bigint UNSIGNED DEFAULT NULL,
  `initiated_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `entry_source` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lawyer',
  `case_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `temporary_barcode` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temporary_barcode_generated_at` timestamp NULL DEFAULT NULL,
  `permanent_barcode` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_barcode_generated_at` timestamp NULL DEFAULT NULL,
  `section_verified_at` timestamp NULL DEFAULT NULL,
  `section_verified_by` bigint UNSIGNED DEFAULT NULL,
  `final_case_number` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_case_year` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_section` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_holder_user_id` bigint UNSIGNED DEFAULT NULL,
  `current_holder_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `returned_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `return_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cases_temporary_barcode_unique` (`temporary_barcode`),
  UNIQUE KEY `cases_permanent_barcode_unique` (`permanent_barcode`),
  KEY `cases_lawyer_id_foreign` (`lawyer_id`),
  KEY `cases_section_verified_by_foreign` (`section_verified_by`),
  KEY `cases_initiated_by_user_id_foreign` (`initiated_by_user_id`),
  KEY `cases_current_holder_user_id_foreign` (`current_holder_user_id`),
  KEY `cases_returned_by_user_id_foreign` (`returned_by_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `lawyer_id`, `initiated_by_user_id`, `entry_source`, `case_type`, `subject`, `description`, `status`, `temporary_barcode`, `temporary_barcode_generated_at`, `permanent_barcode`, `permanent_barcode_generated_at`, `section_verified_at`, `section_verified_by`, `final_case_number`, `final_case_year`, `current_section`, `current_holder_user_id`, `current_holder_at`, `returned_at`, `returned_by_user_id`, `return_reason`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'lawyer', 'Writ Petition', 'Service Matter', 'Illegal Termination of an employee', 'in_progress', 'TEMP1774846193', '2026-03-29 22:49:53', 'WRIT-2026-00000001', '2026-03-29 22:56:49', '2026-03-29 22:56:49', 3, 'WR-2026-000001', '2026', 'Affidavit Section', 4, '2026-03-29 23:03:01', NULL, NULL, NULL, '2026-03-29 22:49:53', '2026-03-29 23:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `case_files`
--

DROP TABLE IF EXISTS `case_files`;
CREATE TABLE IF NOT EXISTS `case_files` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_files_case_id_foreign` (`case_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_files`
--

INSERT INTO `case_files` (`id`, `case_id`, `file_type`, `file_path`, `original_name`, `size`, `created_at`, `updated_at`) VALUES
(1, 1, 'application/pdf', 'case_files/9qC3UwKYTz5HxnKDY3KoXoDHdb7w1CrZc1WFpJ7F.pdf', 'Registration_form_1033908424 (2).pdf', 105338, '2026-03-29 22:49:53', '2026-03-29 22:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `case_petitioners`
--

DROP TABLE IF EXISTS `case_petitioners`;
CREATE TABLE IF NOT EXISTS `case_petitioners` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` bigint UNSIGNED NOT NULL,
  `name_or_organization` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `represented_by` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_petitioners_case_id_foreign` (`case_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_petitioners`
--

INSERT INTO `case_petitioners` (`id`, `case_id`, `name_or_organization`, `represented_by`, `phone`, `created_at`, `updated_at`) VALUES
(2, 1, 'Mr. John Doe', 'Adv. Jenni Willams', '01234567890', '2026-03-29 22:56:49', '2026-03-29 22:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `case_respondents`
--

DROP TABLE IF EXISTS `case_respondents`;
CREATE TABLE IF NOT EXISTS `case_respondents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` bigint UNSIGNED NOT NULL,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_respondents_case_id_foreign` (`case_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_respondents`
--

INSERT INTO `case_respondents` (`id`, `case_id`, `name`, `designation`, `organization`, `address`, `created_at`, `updated_at`) VALUES
(2, 1, 'Mr. Respondent', 'Secretary', 'Ministry of ABC', 'Dhaka', '2026-03-29 22:56:49', '2026-03-29 22:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

DROP TABLE IF EXISTS `courts`;
CREATE TABLE IF NOT EXISTS `courts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_en` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_bn` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courts_code_unique` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`id`, `name_en`, `name_bn`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(6, 'Court 3', 'কোর্ট ৩', 'CRT-03', 1, '2026-03-05 02:15:55', '2026-03-05 02:15:55'),
(4, 'Court 1', 'কোর্ট ১', 'CRT-01', 1, '2026-03-05 02:15:28', '2026-03-05 02:15:28'),
(5, 'Court 2', 'কোর্ট ২', 'CRT-02', 1, '2026-03-05 02:15:43', '2026-03-05 02:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `court_dispatch_batches`
--

DROP TABLE IF EXISTS `court_dispatch_batches`;
CREATE TABLE IF NOT EXISTS `court_dispatch_batches` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `court_id` bigint UNSIGNED NOT NULL,
  `created_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dispatch',
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `received_by_name` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_by_designation` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_by_phone` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handover_to_section` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `court_dispatch_batches_batch_no_unique` (`batch_no`),
  KEY `court_dispatch_batches_court_id_foreign` (`court_id`),
  KEY `court_dispatch_batches_created_by_user_id_foreign` (`created_by_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `court_dispatch_batch_items`
--

DROP TABLE IF EXISTS `court_dispatch_batch_items`;
CREATE TABLE IF NOT EXISTS `court_dispatch_batch_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_id` bigint UNSIGNED NOT NULL,
  `case_id` bigint UNSIGNED NOT NULL,
  `barcode_scanned` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_section` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_section` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `court_dispatch_batch_items_batch_id_case_id_unique` (`batch_id`,`case_id`),
  KEY `court_dispatch_batch_items_case_id_foreign` (`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Assistant Registrar Office', '2026-02-18 19:42:33', '2026-02-18 19:42:33'),
(2, 'Filing Section', '2026-02-18 19:42:40', '2026-02-18 19:42:40'),
(3, 'Affidavit Section', '2026-02-18 19:42:47', '2026-02-18 19:42:47'),
(4, 'Requisite Section', '2026-02-18 19:44:03', '2026-02-18 19:44:03'),
(5, 'Put-Up Section', '2026-02-18 19:44:10', '2026-02-18 19:44:10'),
(6, 'Typing Section', '2026-02-18 19:44:22', '2026-02-18 19:44:22'),
(7, 'Compare Section', '2026-02-18 19:44:39', '2026-02-18 19:44:39'),
(8, 'Superintendent', '2026-02-18 19:44:48', '2026-02-18 19:44:48'),
(9, 'Ready Table', '2026-02-18 19:44:57', '2026-02-18 19:44:57'),
(10, 'Record Room', '2026-02-18 19:45:04', '2026-02-18 19:45:04'),
(11, 'Others', '2026-02-18 19:45:14', '2026-02-18 19:45:14'),
(12, 'Court Operator', '2026-03-04 11:13:07', '2026-03-04 11:13:07'),
(13, 'Office Assistant', '2026-03-04 11:56:59', '2026-03-04 11:56:59'),
(14, 'Registrar', '2026-03-04 11:56:59', '2026-03-04 11:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_movements`
--

DROP TABLE IF EXISTS `file_movements`;
CREATE TABLE IF NOT EXISTS `file_movements` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` bigint UNSIGNED NOT NULL,
  `court_id` bigint UNSIGNED DEFAULT NULL,
  `court_dispatch_batch_id` bigint UNSIGNED DEFAULT NULL,
  `barcode_scanned` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_section` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_section` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `movement_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'receive',
  `received_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_override` tinyint(1) NOT NULL DEFAULT '0',
  `override_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file_movements_received_by_user_id_foreign` (`received_by_user_id`),
  KEY `file_movements_case_id_received_at_index` (`case_id`,`received_at`),
  KEY `file_movements_to_section_received_at_index` (`to_section`,`received_at`),
  KEY `file_movements_barcode_scanned_index` (`barcode_scanned`),
  KEY `file_movements_court_id_foreign` (`court_id`),
  KEY `file_movements_court_dispatch_batch_id_foreign` (`court_dispatch_batch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `file_movements`
--

INSERT INTO `file_movements` (`id`, `case_id`, `court_id`, `court_dispatch_batch_id`, `barcode_scanned`, `from_section`, `to_section`, `movement_type`, `received_by_user_id`, `received_at`, `notes`, `is_override`, `override_reason`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'TEMP1774846193', NULL, 'Filing Section', 'receive', 3, '2026-03-29 22:56:49', 'Converted temporary filing to permanent case.', 0, NULL, '2026-03-29 22:56:49', '2026-03-29 22:56:49'),
(2, 1, NULL, NULL, 'WRIT-2026-00000001', 'Filing Section', 'Affidavit Section', 'receive', 4, '2026-03-29 23:03:01', NULL, 0, NULL, '2026-03-29 23:03:01', '2026-03-29 23:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyers`
--

DROP TABLE IF EXISTS `lawyers`;
CREATE TABLE IF NOT EXISTS `lawyers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `bar_council_id` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barDateOfJoining` date DEFAULT NULL,
  `barDateOfEnrollment` date DEFAULT NULL,
  `barCourtType` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lawyers_bar_council_id_unique` (`bar_council_id`),
  KEY `lawyers_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lawyers`
--

INSERT INTO `lawyers` (`id`, `user_id`, `bar_council_id`, `full_name`, `phone`, `picture`, `barDateOfJoining`, `barDateOfEnrollment`, `barCourtType`, `status`, `created_at`, `updated_at`) VALUES
(1, 21, '3352', 'Syed Ziaul Hasan Kushal', '01552365025', 'https://api.scba.org.bd/api/esl/photo/3352.jpg', '2006-02-15', '2000-06-18', 'HIGH COURT', 'Active', '2026-03-29 22:44:13', '2026-03-29 22:44:13');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_18_170452_create_permission_tables', 1),
(5, '2025_09_19_044952_create_permission_groups_table', 1),
(6, '2025_09_19_050039_add_group_id_to_permissions_table', 1),
(7, '2025_11_23_075443_add_user_type_to_users_table', 1),
(8, '2025_11_23_075722_create_lawyers_table', 1),
(9, '2025_12_02_032423_create_court_cases_table', 1),
(10, '2025_12_02_032430_create_case_petitioners_table', 1),
(11, '2025_12_02_032436_create_case_respondents_table', 1),
(12, '2025_12_02_032443_create_case_files_table', 1),
(13, '2026_02_18_064719_create_departments_table', 1),
(14, '2026_02_25_000001_add_tracking_columns_to_cases_table', 2),
(15, '2026_02_25_000002_make_lawyer_id_nullable_in_cases_table', 2),
(16, '2026_02_25_000003_create_file_movements_table', 2),
(17, '2026_02_25_000004_add_return_fields_to_cases_table', 3),
(18, '2026_03_04_000101_create_courts_table', 4),
(19, '2026_03_04_000102_create_court_dispatch_batches_tables', 4),
(20, '2026_03_04_000103_add_court_columns_to_file_movements', 4),
(21, '2026_03_05_120000_drop_unused_permission_layer_tables', 5),
(22, '2026_03_11_000001_add_face_descriptor_to_users_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 5),
(3, 'App\\Models\\User', 6),
(3, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 8),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 15),
(3, 'App\\Models\\User', 16),
(3, 'App\\Models\\User', 17),
(3, 'App\\Models\\User', 18),
(3, 'App\\Models\\User', 19);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-02-19 01:34:28', '2026-02-19 01:34:28'),
(2, 'Admin', 'web', '2026-02-19 01:34:46', '2026-02-19 01:34:46'),
(3, 'Staff', 'web', '2026-02-19 01:34:54', '2026-02-19 01:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('i0MFKY8qFWsV9LAoSQdCvmp2prdhfURwfc9UCV0E', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWjBjZkJyY0pWcGFwV1RlQmtsZnYya3llVlFpVnowc1VyNW9wdVpoMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1781505787),
('S9x1pU4tONX2u6TV63IOtlz3zxG800kdSkRmQ347', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicDdMZDM3eWFaaWpvMGdiRkJvNDJKMVdlaDhqeWhjWGxPNVFialBkQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoibG9jYWxlIjtzOjI6ImVuIjt9', 1774850445);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `login_id` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lawyer',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `face_descriptor` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login_id`, `department`, `name`, `email`, `email_verified_at`, `password`, `user_type`, `is_active`, `remember_token`, `face_descriptor`, `created_at`, `updated_at`) VALUES
(1, '123', '1', 'Super Admin', 'superadmin@email.com', NULL, '$2y$12$PGDABv89kZAcs8N0MTsy..K3Qhs8d1vu3zHhx7znXt1dkcRgQ4Gfq', 'admin', 1, 'BXOBfACRA0yTJWWjZVc4O7uekCof6e0kZfuuIztw9XFv8k32xCfJ55FVI1Kq', NULL, '2026-02-19 00:51:17', '2026-02-18 19:46:15'),
(2, '0012850669', '1', 'Assistant Registrar', 'assistant.registrar@writ.local', NULL, '$2y$12$cskEIK587ZHfuZEaNjJMOe.1hfwQX35PdJaQ6xGBD6o1hb7gm55xy', 'admin', 1, 'RuZluZDoNKdsyc77g9RHaw3wzVHpjBLv1ftvIZvPL6OLNXgNDpmhpydNa5QQ', '[-0.19177411496639252, 0.03561384230852127, 0.10578130185604095, -0.022578090243041516, -0.0634514182806015, -0.0380275085568428, -0.06381048187613488, -0.05804786607623101, 0.10158494263887403, -0.04245337247848511, 0.16404348611831665, -0.021169416792690753, -0.18433382511138915, -0.03028441034257412, -0.06638285890221596, 0.10423094183206558, -0.105610953271389, -0.10533523112535476, -0.04608804248273373, -0.11569257527589798, 0.026820984110236167, 0.04965701699256897, -0.025053320080041887, 0.05196307748556137, -0.1957750916481018, -0.3485713183879852, -0.0878437727689743, -0.10538658797740937, -0.02580624967813492, -0.04534765258431435, 0.004047592869028449, -0.05489102452993393, -0.12038609385490416, -0.02157058138400316, 0.02338322065770626, 0.1372620016336441, -0.02235649786889553, -0.0532059907913208, 0.17287372052669525, -0.010844765510410073, -0.12006610631942748, -0.04806015715003013, 0.07897672802209854, 0.2865475833415985, 0.13185228407382965, 0.06852299720048904, 0.07680673003196717, -0.031204109080135822, 0.07547401785850524, -0.24319158494472504, 0.06530642062425614, 0.11497286856174468, -0.002888131281360984, 0.07046857923269272, 0.11408783495426178, -0.1123576119542122, 0.0008974634809419512, 0.12476898282766342, -0.10805526077747345, 0.07934421002864837, -0.010143156978301704, -0.0135876651853323, -0.02398834563791752, -0.035173868760466576, 0.20661086738109588, 0.09585183709859849, -0.1165778398513794, -0.10425038039684296, 0.11614544838666915, -0.19452304542064663, -0.07753218710422516, 0.030957129225134848, -0.07594417929649352, -0.16268860697746276, -0.3318993091583252, 0.052686364948749544, 0.4873725950717926, 0.15579516887664796, -0.17038763761520387, 0.017066426388919354, -0.05574935376644134, -0.029880417883396147, 0.15406342446804047, 0.03640794642269611, -0.06645248532295227, 0.03874449022114277, -0.11286710947752, 0.04019189663231373, 0.23215998709201813, 0.04465853050351143, -0.040772183984518054, 0.12470794767141342, 0.02628920190036297, 0.04664266034960747, 0.04787024036049843, 0.09061618000268935, -0.12437817901372908, -0.0023579658940434457, -0.08523907065391541, -0.00862645641900599, 0.11913405656814575, -0.0013138226000592112, 0.059157335013151166, 0.10994017869234084, -0.17719282805919648, 0.16430120766162873, 0.01786182578653097, -0.05085207149386406, -0.010461792629212142, 0.08670136630535126, -0.14970665276050568, -0.02191083859652281, 0.1816076874732971, -0.20694711208343505, 0.12054326087236404, 0.19380923509597775, -0.004214394383598119, 0.16768971979618072, 0.0955522373318672, 0.01999797727912665, -0.029764706268906593, 0.02336590178310871, -0.11459771692752838, -0.07160321772098541, -0.03447154015302658, -0.022983368113636972, 0.0947581186890602, 0.048754872381687166]', '2026-03-01 11:57:30', '2026-03-29 21:42:59'),
(3, '0012889236', '2', 'Filing Operator', 'filing.section@writ.local', NULL, '$2y$12$J8MI4IQqZ1mSKGwz25ix/ui7iyV2rhFwqilj84I4642NhiAgaGZH6', 'staff', 1, 'F4y7h76WFNVd4886bfeUKNylUU4ymRDkfWotPC9F70e3Sy78QAB7Yf0dK8E1', '[-0.17742278873920442, 0.07041005343198777, 0.12132215052843094, -0.02013641893863678, 0.0024310709908604623, -0.03449571281671524, -0.057517939060926435, -0.04756664410233498, 0.1550481766462326, -0.07778985798358917, 0.1864759296178818, -0.030777177587151528, -0.1808851808309555, -0.04030346609652043, -0.08555122464895248, 0.09226177483797074, -0.09991766214370729, -0.09854500889778137, -0.05631689801812172, -0.08409013599157333, 0.06222026273608208, 0.061557547003030774, -0.0035377250052988527, 0.08013933449983597, -0.20332533419132232, -0.34697869420051575, -0.10150224715471268, -0.1319620430469513, 0.009452085243538022, -0.08403731137514114, -0.017098430078476667, -0.045945816487073896, -0.11795254498720167, -0.019553595408797263, -0.018160399980843067, 0.13283510655164718, -0.04404933974146843, -0.06156039908528328, 0.1981845259666443, -0.009263919189106674, -0.11683080643415451, -0.023056655935943127, 0.02489898130297661, 0.30320743918418885, 0.168748939037323, 0.06839456483721733, 0.04776032567024231, -0.05517487972974777, 0.08168980628252029, -0.2662579298019409, 0.052673599869012835, 0.12500037997961044, 0.013051725551486017, 0.08588739484548569, 0.13812609761953354, -0.09438951909542084, 0.027493874356150628, 0.0984993562102318, -0.1223621591925621, 0.07393654510378837, -0.02439213767647743, -0.05602695271372795, 0.017763812188059093, -0.0029442286991979926, 0.21699292063713072, 0.08591791838407517, -0.10147502720355989, -0.06954715773463249, 0.130387082695961, -0.21256911158561703, -0.026837775483727455, 0.08218029886484146, -0.05098086111247539, -0.15315145254135132, -0.2848053276538849, 0.05588160902261734, 0.48892579078674314, 0.17156673669815065, -0.16000546813011168, 0.05537281557917595, -0.08101721853017807, -0.03912020400166512, 0.16186745464801788, 0.026989530213177203, -0.09857733249664306, -0.002021254412829876, -0.1275642454624176, 0.04015764631330967, 0.24053269922733303, 0.04456670433282852, -0.024141928553581236, 0.14084587544202803, 0.01772000836208463, 0.04333907999098301, 0.0699724942445755, 0.03124601431190967, -0.1365385264158249, -0.004956691525876522, -0.05955819934606552, 0.007854482112452387, 0.10448710322380066, -0.01307702912017703, 0.03435998000204563, 0.09110799878835678, -0.16700718998908998, 0.1495342582464218, 0.012138167722150685, -0.06886382177472114, 0.03296122122555971, 0.07721798866987228, -0.16925885677337646, -0.027056223712861534, 0.1922527313232422, -0.22528965771198273, 0.12445375919342042, 0.14256633520126344, -0.007537572516594082, 0.16385383605957032, 0.09638323485851288, 0.061931952834129333, -0.05431826338171959, 0.019468108657747507, -0.11642665863037108, -0.049747396260499954, 0.021698933467268942, -0.024025093764066696, 0.1211876168847084, 0.005116591416299343]', '2026-03-01 11:57:31', '2026-03-29 21:39:32'),
(4, '0012850659', '3', 'Affidavit Operator', 'affidavit.section@writ.local', NULL, '$2y$12$RGekGHBAsXNna/aIYNrtGuvfhxBpZtPSe.6mkjcYt5VGAqE/x2XVO', 'staff', 1, 'w82Vhu50xFDhnhumcSpasvhS1gaYNDcryg3a03DlnQqZQ9bdyeygWriz0ZxC', '[-0.17546626329421997, 0.01109009152278304, 0.06896376386284828, -0.031847990676760675, -0.03616611398756504, -0.04754904955625534, 0.010721455421298742, -0.05613575205206871, 0.141329026222229, -0.10972468405961992, 0.14047322571277618, -0.062243550270795825, -0.13075539320707322, -0.12620601952075958, -0.017960437946021555, 0.1347197934985161, -0.1464942991733551, -0.15489307045936584, -0.07042363286018372, -0.07953963577747344, 0.05743746683001518, 0.03432137928903103, 0.02957603633403778, 0.07766877710819245, -0.23693190813064577, -0.3865626454353333, -0.08242980539798736, -0.13899465501308442, 0.016354428604245186, -0.027722911909222603, -0.07635698765516281, 0.012063339818269014, -0.16370851099491118, -0.01954871490597725, -0.010414586775004864, 0.12318070232868196, 0.027511034160852432, 0.01834380514919758, 0.14755266010761262, 0.02880529910326004, -0.2066248536109924, 0.033827612176537514, 0.018781127780675887, 0.2984107196331024, 0.19347372353076936, 0.09446915239095688, 0.0374392818659544, -0.050880241021513936, 0.08163059651851653, -0.2073053687810898, 0.06255680695176125, 0.09680439978837968, 0.0376230988651514, 0.015125437825918195, 0.0934119552373886, -0.12109387069940566, -0.005932181375101209, 0.07059328854084015, -0.14467402398586274, 0.06794931590557099, -0.0553200826048851, -0.04633582308888435, 0.035722069814801215, -0.024200203269720076, 0.21658765375614167, 0.08112797439098358, -0.09424871802330018, -0.09476891607046128, 0.1287441685795784, -0.22499422132968905, -0.02227323316037655, 0.033236279338598254, -0.08676721453666687, -0.14852252304553987, -0.3470718801021576, 0.015168163925409315, 0.4901944935321808, 0.15343091785907745, -0.20234692096710205, 0.04997940286993981, -0.058614366501569745, -0.03589048944413662, 0.16891787350177764, 0.13151507675647736, -0.10993817001581192, 0.0790096402168274, -0.11757812947034836, 0.028204038739204407, 0.20581624805927276, 0.015878782235085966, -0.035704877227544785, 0.1639305382966995, 0.011281282198615373, 0.06307491436600685, 0.050225181877613066, -0.00808672783896327, -0.14846347868442536, 0.016352356318384408, -0.06920959129929542, -0.03673026002943515, 0.050386540591716766, -0.03667131401598454, 0.038186156377196315, 0.11712305843830108, -0.18404934108257293, 0.17054838836193084, -0.011369504313915969, -0.048840881884098054, -0.03433969542384148, 0.10944733768701552, -0.08196797519922257, -0.05168160125613212, 0.11007937937974928, -0.19352522492408752, 0.10526397973299026, 0.2263149529695511, 0.0059212605003267525, 0.19165847301483155, 0.08188913017511368, 0.04264917522668839, -0.04999978169798851, 0.08733024597167968, -0.13577404618263245, 0.001896754268091172, 0.04830970019102097, -0.0694719634950161, 0.15861161649227143, 0.05309858173131943]', '2026-03-01 11:57:31', '2026-03-29 21:39:50'),
(5, '0012855874', '4', 'Requisite Operator', 'requisite.section@writ.local', NULL, '$2y$12$dwlwCE0Adr1uZCa59ggJquZei0gMqth0QQlAyRa3NDL45azY3YOXa', 'staff', 1, 'G68LFRmOLf7Dzd8WpGqMssvcnYAMYgGdzaX5J5DNdXS9nQhgukQHHN1M6wbU', '[-0.0946693792939186, 0.06680053249001502, 0.09671220183372498, -0.07586216926574707, -0.1213319718837738, -0.01988531742244959, 0.027856661938130856, -0.08372947424650193, 0.2024725556373596, -0.09577760100364684, 0.15148130804300308, -0.027943150699138643, -0.19960981905460357, -0.02115677623078227, 0.017437438829801977, 0.17855502665042877, -0.0825572669506073, -0.1877101331949234, -0.06176330894231796, -0.07648993879556656, 0.01630977699533105, 0.09111084938049316, -0.024218416213989256, 0.05390168130397797, -0.12189142405986786, -0.3536526560783386, -0.035635552182793614, -0.03748063854873181, -0.0012892839382402598, -0.01182849365286529, -0.027370279002934695, 0.1647243916988373, -0.1423666626214981, -0.03920513205230236, 0.0257279422134161, 0.17840335965156556, -0.14643229246139527, -0.11082724928855896, 0.2220564663410187, 0.005811762902885676, -0.24657938182353972, -0.07499225735664368, 0.08950142711400985, 0.2653822243213654, 0.16325030624866485, 0.0019898293481674044, 0.06912503167986869, -0.05889707654714584, 0.05117024555802345, -0.29995293617248536, 0.04491050392389297, 0.1634415239095688, 0.013763702660799026, 0.09902528673410416, 0.04124135561287403, -0.11954693794250489, 0.003615170600824058, 0.09587538093328477, -0.17778525352478028, 0.010282170632854104, 0.06733401864767075, -0.1668102890253067, -0.05358109548687935, -0.11783728450536728, 0.24118969440460208, 0.13407832533121108, -0.08238082230091096, -0.08220868110656739, 0.1544455200433731, -0.20477780997753145, -0.16686640679836273, 0.02351292911916971, -0.09950452297925948, -0.15870944261550904, -0.3252088248729706, 0.005554639082401991, 0.37484710812568667, 0.12720976769924164, -0.11747638434171676, 0.058909428864717485, 0.005853322055190802, -0.005510928086005151, 0.14316442012786865, 0.1438634991645813, -0.02129406053572893, -0.017343423049896955, -0.06765500158071518, 0.01114203562028706, 0.2652093321084976, -0.04143080972135067, -0.024508966878056527, 0.17426260113716124, 0.05423529669642448, 0.026447935309261084, 0.0106043032836169, 0.07020959332585335, -0.005477196536958218, 0.01635899543762207, -0.15138325989246368, 0.02224920466542244, 0.046685485541820525, -0.022361261211335658, 0.011365297995507718, 0.1289891853928566, -0.19941396415233612, 0.16370364129543305, 0.024983563274145127, -0.0217313245870173, 0.056568586826324464, -0.035814911127090454, -0.09025883674621582, -0.09008266925811768, 0.14678046852350235, -0.1839647501707077, 0.11730245351791382, 0.18398482203483585, -0.02812181953340769, 0.18540591299533843, 0.08472033441066742, 0.10084868967533112, 0.0024627957725897433, -0.07843168377876282, -0.2100891202688217, -0.03964070044457912, 0.10523118078708649, 0.027156749926507472, 0.02161265872418881, 0.09071995317935944]', '2026-03-01 11:57:31', '2026-03-29 21:40:07'),
(6, 'CARD-PUT-0001', '5', 'Put-Up Operator', 'putup.section@writ.local', NULL, '$2y$12$Ca7zpR8MmFV2o1sHWkq9sOrBcrQUJbTR30fIUAg95HZezomStnHTK', 'staff', 1, 'fvODrFXrG7oGgXbf9uPkiTZaSNImaxhEeFXwrQ1n6UJ3WuzPIwpEBmhdF8OQ', NULL, '2026-03-01 11:57:32', '2026-03-04 13:02:46'),
(7, 'CARD-TYP-0001', '6', 'Typing Operator', 'typing.section@writ.local', NULL, '$2y$12$Vj4DUrvCR4hHrcgB3hEr2u2euR/8C3EWr5mEO1eMF7r3Tnn.kYyV2', 'staff', 1, NULL, NULL, '2026-03-01 11:57:32', '2026-03-04 13:02:46'),
(8, 'CARD-CMP-0001', '7', 'Compare Operator', 'compare.section@writ.local', NULL, '$2y$12$ksgeuHvdC1vPVWemSTwBvezIlZZgiDEentnhng3O5VL411kzf9hwa', 'staff', 1, NULL, NULL, '2026-03-01 11:57:32', '2026-03-04 13:02:46'),
(9, 'CARD-SUP-0001', '8', 'Superintendent Operator', 'superintendent@writ.local', NULL, '$2y$12$n2Qi0KYL5Nq/CLKarkL8/u.GgieLgeNQvogR6uKe5Ebow2LMwlVZq', 'staff', 1, 'aqy8WUKqyTPZMfKkFOFYtrfybtrxxR5r5Cjs84l0T2rB5uQxn87uZFK6ALr3', NULL, '2026-03-01 11:57:33', '2026-03-04 13:02:46'),
(10, 'CARD-RDY-0001', '9', 'Ready Table Operator', 'ready.table@writ.local', NULL, '$2y$12$eDDGOFyIVDfouvndlBrDduA5y5lvMieT35m0SjIUtWfC1fl1uiMZu', 'staff', 1, NULL, NULL, '2026-03-01 11:57:33', '2026-03-04 13:02:46'),
(11, 'CARD-RRM-0001', '10', 'Record Room Operator', 'record.room@writ.local', NULL, '$2y$12$Z.C925k3x94VDBqGpJLnQuKB4aJd8YXMZwgYatl0Mq3DAEyTi7o.m', 'staff', 1, 'DtmAq985z482nY9sMHKuyXfRlO4dpuNBizhVBQaG8lbOkO7eSiuwv5HYhTpr', NULL, '2026-03-01 11:57:33', '2026-03-04 13:02:46'),
(12, 'CARD-OTH-0001', '11', 'Others Operator', 'others@writ.local', NULL, '$2y$12$nQSaJa8Y38VsxWymudQJ8.EL7w90GC2GvUpeXHCc3VQl7MJ7VYVrK', 'staff', 1, 'wvMvrt0qpXbk1TbEpHPsuL7VUMFCKM023kzeZntRVwzomDAQ0hdhKFOFP79T', NULL, '2026-03-01 11:57:34', '2026-03-01 11:57:34'),
(15, 'CARD-CRT-0001', '12', 'Court Operator', 'court.operator@writ.local', NULL, '$2y$12$hxwsq/S0o1cXveYKbD39wO5A8I1yalZRU9S6M6aadVguyAiVilr1W', 'staff', 1, 'BgXaeJ7vTJ91SHbUlfhUVNIPS1knv86H50qezLzzKagL2DhGlxFP7G69ZZbh', NULL, '2026-03-04 11:13:08', '2026-03-04 21:26:45'),
(16, 'CARD-OFF-0001', '13', 'Office Assistant', 'office.assistant@writ.local', NULL, '$2y$12$tFbNMWSlpXUJOlFWxpU4e.ggX6DCcj4ES1uCuFavdt9luUFCHX9r6', 'staff', 1, 'sjwBLSIZJIRoR69U6yeHhbcTobGA0iLqCKjafcfkm1X7KzPO75sHNGjcfz2g', NULL, '2026-03-04 11:57:09', '2026-03-04 13:02:46'),
(21, NULL, NULL, 'Syed Ziaul Hasan Kushal', 'zia@email.com', NULL, '$2y$12$E2M6LWOdD4PyObWzplWQUe2mbIzP8gcCIO7ahQjesZjRjmHQnvLLO', 'lawyer', 0, NULL, NULL, '2026-03-29 22:44:13', '2026-03-29 22:44:13');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
