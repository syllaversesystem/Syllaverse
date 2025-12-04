-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 01:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `syllaverse_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(64) NOT NULL,
  `scope_type` varchar(64) NOT NULL,
  `scope_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `start_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `role`, `scope_type`, `scope_id`, `status`, `start_at`, `end_at`, `assigned_by`, `created_at`, `updated_at`) VALUES
(869, 225, 'FACULTY', 'Department', 96, 'ended', '2025-12-03 22:22:56', '2025-12-03 22:23:49', NULL, '2025-12-03 22:22:56', '2025-12-03 22:23:49'),
(870, 225, 'FACULTY', 'Department', 80, 'ended', '2025-12-03 22:23:49', '2025-12-04 00:25:28', NULL, '2025-12-03 22:23:49', '2025-12-04 00:25:28'),
(871, 225, 'FACULTY', 'Department', 85, 'ended', '2025-12-04 00:25:28', '2025-12-04 02:25:46', NULL, '2025-12-04 00:25:28', '2025-12-04 02:25:46'),
(872, 225, 'FACULTY', 'Department', 80, 'ended', '2025-12-04 02:25:46', '2025-12-04 02:26:39', NULL, '2025-12-04 02:25:46', '2025-12-04 02:26:39'),
(873, 225, 'DEPT_HEAD', 'Department', 80, 'ended', '2025-12-04 02:26:39', '2025-12-04 02:32:48', NULL, '2025-12-04 02:26:39', '2025-12-04 02:32:48'),
(874, 225, 'CHAIR', 'Department', 85, 'ended', '2025-12-04 02:32:48', '2025-12-04 02:55:48', NULL, '2025-12-04 02:32:48', '2025-12-04 02:55:48'),
(875, 225, 'CHAIR', 'Department', 80, 'ended', '2025-12-04 02:55:48', '2025-12-04 03:00:18', NULL, '2025-12-04 02:55:48', '2025-12-04 03:00:18'),
(876, 225, 'FACULTY', 'Department', 80, 'active', '2025-12-04 03:00:18', NULL, NULL, '2025-12-04 03:00:18', '2025-12-04 03:00:18');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cdios`
--

CREATE TABLE `cdios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chair_requests`
--

CREATE TABLE `chair_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `requested_role` varchar(64) NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `decided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chair_requests`
--

INSERT INTO `chair_requests` (`id`, `user_id`, `requested_role`, `department_id`, `program_id`, `status`, `decided_by`, `decided_at`, `notes`, `created_at`, `updated_at`) VALUES
(236, 225, 'FACULTY', 80, NULL, 'approved', NULL, '2025-12-04 03:00:18', NULL, '2025-12-04 03:00:11', '2025-12-04 03:00:18');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `course_category` varchar(255) DEFAULT NULL,
  `has_iga` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `contact_hours_lec` int(11) DEFAULT NULL,
  `contact_hours_lab` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `code`, `title`, `course_category`, `has_iga`, `status`, `contact_hours_lec`, `contact_hours_lab`, `description`, `created_at`, `updated_at`) VALUES
(86, 80, 'BAT 401', 'Fundamentals of Business Analytics', 'Professional Elective: Business Analytics Track', 0, 'active', 3, 2, NULL, '2025-11-03 10:31:53', '2025-11-03 10:31:53'),
(88, 80, 'IT 111', 'Introduction to Computing', 'Core, Elective, Professional', 0, 'active', 3, 0, NULL, '2025-11-30 23:06:51', '2025-11-30 23:06:51'),
(89, 80, 'CS 121', 'Advanced Computer Programming', 'Core Elective', 0, 'active', 2, 3, NULL, '2025-12-03 08:21:26', '2025-12-03 08:21:26'),
(90, 80, 'CS 111', 'Computer Programming', 'Core Elective', 0, 'active', 3, 2, NULL, '2025-12-03 08:22:12', '2025-12-03 08:22:12'),
(91, 80, 'IT 212', 'Computer Networking 1', 'Core Elective', 0, 'active', 3, 2, NULL, '2025-12-03 08:23:47', '2025-12-03 08:23:47'),
(92, 80, 'IT 221', 'Information Management', 'Core Elective', 0, 'active', 3, 2, NULL, '2025-12-03 08:36:24', '2025-12-03 08:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `course_prerequisite`
--

CREATE TABLE `course_prerequisite` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `prerequisite_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_prerequisite`
--

INSERT INTO `course_prerequisite` (`id`, `course_id`, `prerequisite_id`, `created_at`, `updated_at`) VALUES
(96, 89, 90, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(80, 'College of Informatics and Computing Sciences', 'CICS', '2025-10-03 22:56:33', '2025-12-02 21:42:34'),
(85, 'College of Teacher Education', 'CTE', '2025-10-05 01:51:06', '2025-10-15 20:55:12'),
(97, 'General Education', 'GENED', '2025-12-02 15:19:13', '2025-12-02 15:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_syllabus`
--

CREATE TABLE `faculty_syllabus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('owner','collaborator','viewer') NOT NULL DEFAULT 'collaborator',
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_syllabus`
--

INSERT INTO `faculty_syllabus` (`id`, `faculty_id`, `syllabus_id`, `role`, `can_edit`, `created_at`, `updated_at`) VALUES
(112, 226, 254, 'owner', 1, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(113, 226, 255, 'owner', 1, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(114, 226, 256, 'owner', 1, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(115, 226, 257, 'owner', 1, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(116, 226, 258, 'owner', 1, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(117, 226, 259, 'owner', 1, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(118, 226, 260, 'owner', 1, '2025-12-03 09:34:39', '2025-12-03 09:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_information`
--

CREATE TABLE `general_information` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `general_information`
--

INSERT INTO `general_information` (`id`, `department_id`, `section`, `content`, `created_at`, `updated_at`) VALUES
(1, NULL, 'mission', 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', '2025-07-20 14:10:56', '2025-08-14 08:57:56'),
(2, NULL, 'vision', 'A premier national university that develops leaders in the global knowledge economy', '2025-07-20 14:11:31', '2025-07-20 14:11:31'),
(3, 80, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', '2025-07-20 14:11:50', '2025-07-20 14:11:50'),
(4, 80, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', '2025-07-20 14:12:07', '2025-07-20 14:12:07'),
(5, 80, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', '2025-07-20 14:12:18', '2025-07-20 14:12:18'),
(6, 80, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', '2025-07-20 14:12:30', '2025-07-20 14:12:30'),
(10, 80, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', '2025-09-07 15:36:26', '2025-09-07 15:36:26');

-- --------------------------------------------------------

--
-- Table structure for table `igas`
--

CREATE TABLE `igas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intended_learning_outcomes`
--

CREATE TABLE `intended_learning_outcomes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(34, '2025_07_14_091747_create_courses_table', 1),
(35, '2025_07_14_091954_create_programs_table', 1),
(36, '2025_07_14_092050_create_program_courses_table', 1),
(37, '2025_07_22_165446_add_contact_hours_to_courses_table', 2),
(38, '2025_07_22_165818_create_course_prerequisite_table', 3),
(39, '2025_07_22_173011_add_course_id_to_intended_learning_outcomes_table', 4),
(40, '2025_07_22_224114_create_syllabi_table', 5),
(41, '2025_07_22_225305_add_year_level_to_syllabi_table', 6),
(42, '2025_07_26_151152_add_textbook_file_path_to_syllabi_table', 7),
(44, '2025_07_26_191116_create_syllabus_textbooks_table', 9),
(45, 'xxxx_xx_xx_xxxxxx_create_syllabus_textbooks_table', 10),
(46, '2025_07_27_000001_update_ilo_so_columns_in_syllabus_tlas_table', 11),
(48, '2025_07_28_000002_add_position_to_intended_learning_outcomes_table', 13),
(49, '2025_07_26_152842_create_tla_table', 14),
(50, '2025_07_28_000000_create_syllabus_ilos_table', 15),
(51, '2025_07_28_000001_create_syllabus_sos_table', 16),
(57, 'xxxx_xx_xx_create_syllabus_sdg_table', 17),
(62, '2025_07_29_000001_add_position_to_student_outcomes_table', 18),
(63, '2025_07_28_205432_add_code_and_position_to_syllabus_ilos_table', 19),
(64, '2025_07_29_000001_add_code_and_position_to_syllabus_sos_table', 20),
(65, '2025_07_28_223651_create_tla_ilo_table', 21),
(66, '2025_07_28_223651_create_tla_so_table', 21),
(67, '2025_08_08_000000_add_hr_fields_to_users_table', 22),
(68, '2025_08_08_000001_create_chair_requests_table', 23),
(69, '2025_08_08_000002_create_appointments_table', 23),
(70, '2025_08_12_000001_add_code_and_sort_order_to_master_data', 24),
(71, '2025_08_15_185055_update_role_and_scope_in_appointments_table', 25),
(72, '2025_08_16_144042_remove_units_from_courses_table', 26),
(73, 'create_assessment_task_groups_table', 27),
(74, 'create_assessment_tasks_table', 28),
(75, '2025_08_30_000001_update_ilo_unique_index', 29),
(76, '2025_09_01_000000_create_syllabus_course_infos_table', 29),
(77, '2025_09_01_000001_add_course_category_to_courses_table', 30),
(78, '2025_09_01_010000_create_syllabus_mission_visions_table', 31),
(79, '2025_09_01_020000_backfill_syllabus_mission_visions', 32),
(80, '2025_09_01_030000_add_contact_hours_text_to_syllabus_course_infos', 32),
(81, '2025_09_01_040000_add_contact_hours_lec_lab_to_syllabus_course_infos', 32),
(82, '2025_09_01_120000_change_contact_hours_lec_lab_to_text', 33),
(83, '2025_09_01_200000_create_syllabus_criteria_table', 34),
(84, '2025_09_01_210000_add_tla_strategies_to_syllabus_course_infos', 35),
(85, '2025_09_02_000000_add_section_to_syllabus_criteria_table', 36),
(86, '2025_09_02_010000_create_syllabus_criterion_items_table', 37),
(87, '2025_09_01_120000_drop_syllabus_criteria_tables', 38),
(88, '2025_09_01_100000_create_syllabus_sections_table', 39),
(89, '2025_09_01_100100_create_syllabus_section_items_table', 39),
(90, '2025_09_01_190138_create_syllabus_criteria_table_new', 40),
(91, '2025_09_02_000000_add_criteria_fields_to_syllabus_course_infos', 41),
(92, '2025_09_03_000000_migrate_criteria_to_normalized_table_and_drop_columns', 42),
(93, '2025_09_03_000001_add_assessment_tasks_data_to_syllabi_table', 43),
(94, '2025_09_03_000002_create_syllabus_assessment_tasks_table', 43),
(95, '2025_09_03_000001_change_cpa_columns_to_text', 44),
(96, '2025_09_03_000004_create_syllabus_igas_table', 45),
(97, '2025_09_03_000005_create_student_outcomes_table', 46),
(98, '2025_09_03_000006_create_syllabus_cdios_table', 46),
(99, '2025_09_05_000001_add_position_and_code_to_syllabus_sdg_table', 47),
(100, '2025_09_05_010000_create_syllabus_sdgs_table', 48),
(101, '2025_09_05_020000_replace_syllabus_sdg_with_cdios_structure', 49),
(102, '2025_09_06_000000_create_syllabus_course_policies_table', 50),
(103, '2025_09_06_120000_create_syllabus_course_policies_table', 51),
(104, '2025_09_07_120000_merge_disability_advising_into_other_policies', 52),
(105, '2025_09_07_000000_drop_tla_tables', 53),
(106, '2025_09_07_235900_create_tla_table', 54),
(107, '2025_09_08_000000_add_position_to_tla_table', 54),
(108, '2025_09_08_000000_create_missing_tla_pivots', 55),
(109, '2025_09_09_000001_create_syllabus_assessment_mappings_table', 56),
(110, '2025_09_09_120000_create_syllabus_assessment_mappings_table', 57),
(111, '2025_09_13_000000_expand_roles', 57),
(112, '2025_09_13_000100_nullable_department_in_chair_requests', 58),
(113, '2025_09_13_120000_make_programs_created_by_nullable_and_null_on_delete', 59),
(114, '2025_09_14_000000_add_ilo_so_cpa_data_to_syllabi_table', 60),
(115, '2025_09_14_010000_create_syllabus_ilo_so_cpa_table', 61),
(116, '2025_09_14_020000_create_syllabus_ilo_iga_table', 62),
(117, '2025_09_15_120000_create_syllabus_ilo_cdio_sdg_table', 63),
(118, '2025_09_18_000000_create_textbook_chunks_table', 64),
(119, '2025_09_18_120000_create_textbook_chunks_table', 65),
(120, '2025_10_02_100441_add_description_to_departments_table', 66),
(121, '2025_10_05_085903_add_status_to_programs_table', 66),
(122, '2025_10_11_045331_add_course_type_and_iga_to_courses_table', 67),
(123, '2025_10_16_011501_drop_course_type_from_courses_table', 68),
(124, '2025_10_16_011505_drop_course_type_from_courses_table', 68),
(125, '2025_10_16_012000_add_status_to_courses_table', 69),
(126, '2025_10_16_012004_add_status_to_courses_table', 69),
(127, '2025_10_18_060615_create_faculty_role_requests_table', 70),
(128, '2025_10_18_174907_create_notifications_table', 71),
(129, '2025_10_23_013044_add_title_to_student_outcomes_table', 72),
(130, '2025_10_23_100000_modify_student_outcomes_structure', 73),
(131, '2025_10_23_add_department_id_to_student_outcomes_table', 73),
(132, '2025_11_06_084829_remove_code_from_sdgs_table', 74),
(133, '2025_11_06_092544_remove_sort_order_from_sdgs_table', 75),
(134, '2025_11_06_093132_remove_code_from_igas_table', 76),
(135, '2025_11_06_093207_remove_sort_order_from_igas_table', 76),
(136, '2025_11_06_093928_add_department_id_to_igas_table', 77),
(137, '2025_11_07_100001_remove_code_from_cdios_table', 78),
(138, '2025_11_07_100045_remove_sort_order_from_cdios_table', 78),
(140, '2025_11_14_000100_add_title_columns_to_syllabus_items', 79),
(141, '2025_11_16_180628_restructure_syllabus_assessment_tasks_table', 80),
(142, '2025_11_16_215803_remove_department_id_from_igas_table', 81),
(143, '2025_11_17_150846_remove_department_id_from_student_outcomes_table', 82),
(144, '2025_11_18_004228_add_department_id_to_student_outcomes_table', 83),
(145, '2025_11_19_000000_add_department_id_to_general_information_table', 84),
(146, '2025_11_20_000000_add_constraint_mission_vision_university_wide', 85),
(147, '2025_11_20_000001_drop_old_section_unique_constraint', 86),
(149, '2025_11_22_084722_drop_extra_ilo_so_cpa_tables', 87),
(150, '2025_11_23_034301_add_so_columns_to_syllabi_table', 88),
(154, '2025_11_24_010733_create_syllabi_ilo_iga_table', 89),
(155, '2025_11_24_110551_add_iga_columns_to_syllabi_table', 89),
(157, '2025_11_26_102630_add_cdio_sdg_labels_to_syllabus_ilo_cdio_sdg_table', 90),
(158, '2025_11_26_103312_drop_cdio_sdg_labels_from_syllabus_ilo_cdio_sdg_table', 91),
(159, '2025_11_26_102130_add_cdio_sdg_columns_to_syllabi_table', 92),
(160, '2025_11_27_003819_add_status_fields_to_syllabi_table', 92),
(161, '2025_11_27_100000_create_faculty_syllabus_table', 93),
(166, '2025_11_27_040322_add_submission_status_to_syllabi_table', 94),
(167, '2025_11_27_040554_create_syllabus_submissions_table', 94),
(168, '2025_11_27_015314_remove_faculty_id_from_syllabi_table', 95),
(170, '2025_11_28_000001_create_syllabus_comments_table', 96),
(171, '2025_11_29_021232_add_faculty_id_to_syllabi_table', 97),
(172, '2025_12_02_120000_extend_submission_status_enum', 98),
(173, '2025_12_02_120100_extend_submission_history_enums', 99);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('04de64e2-d5bd-4af1-a3cb-d3d3e0024a3d', 'App\\Notifications\\FacultyRoleRequestNotification', 'App\\Models\\User', 108, '{\"chair_request_id\":76,\"requester_name\":\"PABLICO ADRIANE ALLEN\",\"requester_email\":\"22-77551@g.batstate-u.edu.ph\",\"department_name\":\"College of Informatics and Computing Science\",\"requested_role\":\"FACULTY\",\"message\":\"New faculty member request from PABLICO ADRIANE ALLEN for College of Informatics and Computing Science\"}', NULL, '2025-10-18 10:59:13', '2025-10-18 10:59:13'),
('2c8bc1c2-a522-4dcc-a5c9-16f23b18251a', 'App\\Notifications\\FacultyRoleRequestNotification', 'App\\Models\\User', 120, '{\"chair_request_id\":83,\"requester_name\":\"PABLICO ADRIANE ALLEN\",\"requester_email\":\"22-77551@g.batstate-u.edu.ph\",\"department_name\":\"College of Informatics and Computing Science\",\"requested_role\":\"FACULTY\",\"message\":\"New faculty member request from PABLICO ADRIANE ALLEN for College of Informatics and Computing Science\"}', NULL, '2025-10-19 11:42:18', '2025-10-19 11:42:18'),
('2e0d647a-f0a7-4b23-b99a-1a955b6e939d', 'App\\Notifications\\FacultyRoleRequestNotification', 'App\\Models\\User', 120, '{\"chair_request_id\":84,\"requester_name\":\"PABLICO ADRIANE ALLEN\",\"requester_email\":\"22-77551@g.batstate-u.edu.ph\",\"department_name\":\"College of Informatics and Computing Science\",\"requested_role\":\"FACULTY\",\"message\":\"New faculty member request from PABLICO ADRIANE ALLEN for College of Informatics and Computing Science\"}', NULL, '2025-10-23 03:02:50', '2025-10-23 03:02:50'),
('84b5100e-fe4a-4452-afcc-b8cb7c920b72', 'App\\Notifications\\FacultyRoleRequestNotification', 'App\\Models\\User', 108, '{\"chair_request_id\":78,\"requester_name\":\"PABLICO ADRIANE ALLEN\",\"requester_email\":\"22-77551@g.batstate-u.edu.ph\",\"department_name\":\"College of Informatics and Computing Science\",\"requested_role\":\"FACULTY\",\"message\":\"New faculty member request from PABLICO ADRIANE ALLEN for College of Informatics and Computing Science\"}', NULL, '2025-10-18 11:58:59', '2025-10-18 11:58:59'),
('b9e086a9-1d82-43b1-ad26-596a71e1c12c', 'App\\Notifications\\FacultyRoleRequestNotification', 'App\\Models\\User', 108, '{\"chair_request_id\":79,\"requester_name\":\"PABLICO ADRIANE ALLEN\",\"requester_email\":\"22-77551@g.batstate-u.edu.ph\",\"department_name\":\"College of Informatics and Computing Science\",\"requested_role\":\"FACULTY\",\"message\":\"New faculty member request from PABLICO ADRIANE ALLEN for College of Informatics and Computing Science\"}', NULL, '2025-10-18 13:06:32', '2025-10-18 13:06:32');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `department_id`, `created_by`, `name`, `code`, `description`, `status`, `created_at`, `updated_at`) VALUES
(103, 80, NULL, 'Bachelor of Science in Information Technology', 'BSIT', NULL, 'active', '2025-11-03 08:46:34', '2025-11-30 06:36:33');

-- --------------------------------------------------------

--
-- Table structure for table `sdgs`
--

CREATE TABLE `sdgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sdgs`
--

INSERT INTO `sdgs` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Envisioning', 'Est ablishalinkbetweenlong-termgoalsandandimmediateactions,andmotivatepeopletotakeactionby\r\n harnessing their deep aspirations.', '2025-07-22 04:37:43', '2025-11-07 04:19:51'),
(2, 'Critical', 'Examine economic, environmental, social and cultural structures in the context of sustainable\r\n development, andchallengespeople toexamineandquestiontheunderlyingassumptions that influence\r\n their world views by having them reflect on unsustainable practices.', '2025-07-22 04:37:59', '2025-11-06 02:20:00'),
(3, 'Systematic thinking', 'Recognise that the whole is more than the sum of its parts, and it is a better way to understand and manage \r\ncomplex situations.', '2025-07-22 04:38:50', '2025-08-15 09:50:47'),
(4, 'Building Partnership', 'Promote dialogue andnegotiation, learning towork together, so as to strengthenownership of and\r\n commitment to sustainable action through education and learning.', '2025-07-22 04:39:05', '2025-08-15 09:50:47'),
(5, 'Participation in Making Decisions', 'Empower oneself and others through involvement in joint analysis, planning and control of local decisions.', '2025-07-22 04:39:26', '2025-08-15 09:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3BEeDTEUZtV9Sc9NeNRT4nAZ6c2v0h0bp0h7lk4h', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoieTZJUlc3QUtkZktCQzVBd1FmaFY0a0RkR1hKM01nNTRpYWlyOGRoMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czozOiJ1cmwiO2E6MDp7fXM6NToic3RhdGUiO3M6NDA6IjBpNGpUNThDVmExVVNJM0NnbU1Kd2RjUUdjTkN6RnV5ZGtSWXA3MXQiO3M6MTM6ImlzX3N1cGVyYWRtaW4iO2I6MTt9', 1764849801),
('bDAPidRMmz3LZ8TN4XZMqMBcMclSHDv8kVJU88as', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidjNIN2d2OUF4UFlSVGg5ZFZkMW1UQkczeGhDck5kR20zYUNJaG1iSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9fQ==', 1764843935),
('dOyKZEAWfONH9C6OcoxkEs1zZAJHi55X26FIlHsC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZTlWblhLVWR1RlZnaGVLRTZXbXEyS0xpSHJXWndmSVUzVTlXcE1hVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3N1cGVyYWRtaW4vc3VwZXJhZG1pbi9kYXNoYm9hcmQiO31zOjU6InN0YXRlIjtzOjQwOiIwaTRqVDU4Q1ZhMVVTSTNDZ21NSndkY1FHY05DekZ1eWRrUllwNzF0Ijt9', 1764846811),
('Huc79mzQ8LQDPcplWDvp8Tjs8T6Wy7PxvotsFf5G', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiMmo4QnN5Nmg0cmNaWWJVRGtHeDdYaGs3QWRZcFQ2Nlg1dXhnYzMxNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czozOiJ1cmwiO2E6MDp7fXM6NToic3RhdGUiO3M6NDA6IjBpNGpUNThDVmExVVNJM0NnbU1Kd2RjUUdjTkN6RnV5ZGtSWXA3MXQiO3M6MTM6ImlzX3N1cGVyYWRtaW4iO2I6MTt9', 1764846907),
('yFewtXYzlVdv6JlyTGuJ4OKQROCuFyx9CGtSqNqK', 225, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRmRPVzdwYldEV0hkcVJGcXJrM2FNUHFJcDY1aHd3eHAzVDNtSmtGMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9mYWN1bHR5L21hbmFnZS1wcm9maWxlIjt9czo1OiJzdGF0ZSI7czo0MDoiaE40cUtmV29nZFdObjFFVXc2UXh5TlJzVHVLdHMwdkZOSlc2clRRYSI7czo1NDoibG9naW5fZmFjdWx0eV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjIyNTt9', 1764849801);

-- --------------------------------------------------------

--
-- Table structure for table `so`
--

CREATE TABLE `so` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_outcomes`
--

CREATE TABLE `student_outcomes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_outcomes`
--

INSERT INTO `student_outcomes` (`id`, `department_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(38, 80, NULL, 'Ability to analyze a complex computing problem and apply principles of computing and other relevant disciplines to identify solutions.', '2025-12-02 20:37:37', '2025-12-02 20:37:37'),
(39, 80, NULL, 'Ability to design, implement, and evaluate a computing-based solution to meet a given set of computing requirements in the context of the program’s discipline.', '2025-12-02 20:37:54', '2025-12-02 20:37:54'),
(40, 80, NULL, 'Ability to communicate effectively in a variety of professional contexts.', '2025-12-02 20:38:13', '2025-12-02 20:38:13'),
(41, 80, NULL, 'Ability to recognize professional responsibilities and make informed judgments in computing practice based on legal and ethical principles.', '2025-12-02 20:38:38', '2025-12-02 20:38:38'),
(43, 80, NULL, 'Ability to function effectively as a member or leader of a team engaged in activities appropriate to the program’s discipline.', '2025-12-02 20:39:43', '2025-12-02 20:39:43'),
(44, 80, NULL, 'Ability to identify and analyze user needs and take them into account in the selection, creation, integration, evaluation, and administration of computing-based systems.', '2025-12-02 20:39:49', '2025-12-02 20:39:49');

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabi`
--

CREATE TABLE `syllabi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `year_level` varchar(255) NOT NULL,
  `submission_status` enum('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL DEFAULT 'draft',
  `submission_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `textbook_file_path` varchar(255) DEFAULT NULL,
  `assessment_tasks_data` text DEFAULT NULL,
  `ilo_so_cpa_data` text DEFAULT NULL,
  `so_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`so_columns`)),
  `iga_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`iga_columns`)),
  `cdio_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cdio_columns`)),
  `sdg_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sdg_columns`)),
  `prepared_by_name` varchar(255) DEFAULT NULL,
  `prepared_by_title` varchar(255) DEFAULT NULL,
  `prepared_by_date` date DEFAULT NULL,
  `reviewed_by_name` varchar(255) DEFAULT NULL,
  `reviewed_by_title` varchar(255) DEFAULT NULL,
  `reviewed_by_date` date DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  `approved_by_title` varchar(255) DEFAULT NULL,
  `approved_by_date` date DEFAULT NULL,
  `status_remarks` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabi`
--

INSERT INTO `syllabi` (`id`, `faculty_id`, `program_id`, `course_id`, `title`, `academic_year`, `semester`, `year_level`, `submission_status`, `submission_remarks`, `submitted_at`, `created_at`, `updated_at`, `textbook_file_path`, `assessment_tasks_data`, `ilo_so_cpa_data`, `so_columns`, `iga_columns`, `cdio_columns`, `sdg_columns`, `prepared_by_name`, `prepared_by_title`, `prepared_by_date`, `reviewed_by_name`, `reviewed_by_title`, `reviewed_by_date`, `approved_by_name`, `approved_by_title`, `approved_by_date`, `status_remarks`, `reviewed_by`, `reviewed_at`) VALUES
(254, 226, 103, 90, 'CIS CS 111 2023-2024', '2023-2024', '2nd Semester', '1st Year', 'final_approved', NULL, '2025-12-03 08:27:09', '2025-12-03 08:26:15', '2025-12-03 08:28:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, '2025-12-03', 'PABLICO ADRIANE ALLEN', NULL, '2025-12-03', NULL, 225, NULL),
(255, 226, 103, 91, 'CIS for Networking', '2024-2025', '2nd Semester', '2nd Year', 'final_approval', NULL, '2025-12-03 08:29:30', '2025-12-03 08:29:17', '2025-12-03 08:30:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, '2025-12-03', 'PABLICO ADRIANE ALLEN', NULL, '2025-12-03', NULL, 225, NULL),
(256, 226, 103, 86, 'CIS BAT401', '2025-2026', '1st Semester', '1st Year', 'approved', NULL, '2025-12-03 08:32:07', '2025-12-03 08:31:56', '2025-12-03 08:32:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, '2025-12-03', NULL, NULL, NULL, NULL, 224, NULL),
(257, 226, 103, 89, 'Advance Computer Programming', '2024-2025', '1st Semester', '2nd Year', 'revision', NULL, '2025-12-03 08:38:14', '2025-12-03 08:33:57', '2025-12-03 09:33:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 224, NULL),
(258, 226, 103, 88, 'Intro to Computing', '2023-2024', '1st Semester', '1st Year', 'pending_review', NULL, '2025-12-03 08:38:11', '2025-12-03 08:35:05', '2025-12-03 08:38:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 224, NULL),
(259, 226, 103, 92, 'CIS for IM', '2023-2024', '2nd Semester', '2nd Year', 'pending_review', NULL, '2025-12-03 08:37:13', '2025-12-03 08:37:10', '2025-12-03 08:37:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 224, NULL),
(260, 226, 103, 89, 'Advanced Computer Programming', '2025-2026', '1st Semester', '2nd Year', 'pending_review', NULL, '2025-12-03 09:34:42', '2025-12-03 09:34:39', '2025-12-03 09:34:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ASIBAR PAUL JUSTINE REY', 'sad', '2025-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 224, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_assessment_mappings`
--

CREATE TABLE `syllabus_assessment_mappings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `week_marks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`week_marks`)),
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_assessment_tasks`
--

CREATE TABLE `syllabus_assessment_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `section_number` int(11) DEFAULT NULL,
  `row_type` enum('main','sub') NOT NULL DEFAULT 'sub',
  `section_legacy` varchar(255) DEFAULT NULL,
  `section_label` varchar(255) DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `task` text DEFAULT NULL,
  `ird` varchar(16) DEFAULT NULL,
  `percent` decimal(8,2) DEFAULT NULL,
  `ilo_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ilo_flags`)),
  `c` text DEFAULT NULL,
  `p` text DEFAULT NULL,
  `a` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_cdios`
--

CREATE TABLE `syllabus_cdios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_comments`
--

CREATE TABLE `syllabus_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `partial_key` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` varchar(24) NOT NULL DEFAULT 'draft',
  `batch` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_comments`
--

INSERT INTO `syllabus_comments` (`id`, `syllabus_id`, `partial_key`, `title`, `body`, `status`, `batch`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 199, 'course-info', 'Course Info', 'aa', 'draft', 1, 139, 139, '2025-11-28 19:19:24', '2025-11-28 19:19:24'),
(2, 199, 'mission-vision', 'Mission Vision', 'aa', 'draft', 2, 139, 139, '2025-11-28 19:22:16', '2025-11-28 19:22:16'),
(3, 199, 'course-info', 'Course Info', 'aa', 'draft', 2, 139, 139, '2025-11-28 19:22:18', '2025-11-28 19:22:18'),
(4, 200, 'mission-vision', 'Mission Vision', 'mission', 'draft', 1, 139, 139, '2025-11-28 21:43:04', '2025-11-28 21:43:04'),
(5, 200, 'criteria-assessment', 'Criteria Assessment', 'make it', 'draft', 1, 139, 139, '2025-11-28 21:43:09', '2025-11-28 21:43:09'),
(6, 201, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 139, 139, '2025-11-28 22:05:13', '2025-11-28 22:05:13'),
(7, 202, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 139, 139, '2025-11-28 22:08:06', '2025-11-28 22:08:06'),
(8, 202, 'mission-vision', 'Mission Vision', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 'draft', 2, 139, 139, '2025-11-28 22:08:54', '2025-11-28 22:09:01'),
(9, 202, 'mission-vision', 'Mission Vision', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:35', '2025-11-28 22:10:35'),
(10, 202, 'course-info', 'Course Info', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:35', '2025-11-28 22:10:35'),
(11, 202, 'criteria-assessment', 'Criteria Assessment', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:35', '2025-11-28 22:10:35'),
(12, 202, 'ilo', 'Ilo', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:36', '2025-11-28 22:10:36'),
(13, 202, 'tlas', 'Tlas', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:36', '2025-11-28 22:10:36'),
(14, 202, 'assessment-tasks-distribution', 'Assessment Tasks Distribution', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:36', '2025-11-28 22:10:36'),
(15, 202, 'textbook-upload', 'Textbook Upload', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:36', '2025-11-28 22:10:36'),
(16, 202, 'iga', 'Iga', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:37', '2025-11-28 22:10:37'),
(17, 202, 'so', 'So', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:37', '2025-11-28 22:10:37'),
(18, 202, 'cdio', 'Cdio', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:37', '2025-11-28 22:10:37'),
(19, 202, 'sdg', 'Sdg', NULL, 'draft', 3, 139, 139, '2025-11-28 22:10:38', '2025-11-28 22:10:38'),
(20, 202, 'mission-vision', 'Mission Vision', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:10', '2025-11-28 22:12:10'),
(21, 202, 'course-info', 'Course Info', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:10', '2025-11-28 22:12:10'),
(22, 202, 'criteria-assessment', 'Criteria Assessment', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:11', '2025-11-28 22:12:11'),
(23, 202, 'ilo', 'Ilo', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:11', '2025-11-28 22:12:11'),
(24, 202, 'assessment-tasks-distribution', 'Assessment Tasks Distribution', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:11', '2025-11-28 22:12:11'),
(25, 202, 'cdio', 'Cdio', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:11', '2025-11-28 22:12:11'),
(26, 202, 'sdg', 'Sdg', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:12', '2025-11-28 22:12:12'),
(27, 202, 'course-policies', 'Course Policies', NULL, 'draft', 4, 139, 139, '2025-11-28 22:12:12', '2025-11-28 22:12:12'),
(28, 202, 'mission-vision', 'Mission Vision', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 'draft', 5, 139, 139, '2025-11-28 22:12:50', '2025-11-28 22:12:50'),
(29, 202, 'course-info', 'Course Info', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:51', '2025-11-28 22:12:51'),
(30, 202, 'criteria-assessment', 'Criteria Assessment', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:51', '2025-11-28 22:12:51'),
(31, 202, 'ilo', 'Ilo', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:52', '2025-11-28 22:12:52'),
(32, 202, 'assessment-tasks-distribution', 'Assessment Tasks Distribution', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:52', '2025-11-28 22:12:52'),
(33, 202, 'textbook-upload', 'Textbook Upload', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:52', '2025-11-28 22:12:52'),
(34, 202, 'iga', 'Iga', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:53', '2025-11-28 22:12:53'),
(35, 202, 'so', 'So', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:53', '2025-11-28 22:12:53'),
(36, 202, 'cdio', 'Cdio', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:54', '2025-11-28 22:12:54'),
(37, 202, 'sdg', 'Sdg', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:54', '2025-11-28 22:12:54'),
(38, 202, 'course-policies', 'Course Policies', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:55', '2025-11-28 22:12:55'),
(39, 202, 'assessment-mapping', 'Assessment Mapping', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:55', '2025-11-28 22:12:55'),
(40, 202, 'tla', 'Tla', NULL, 'draft', 5, 139, 139, '2025-11-28 22:12:55', '2025-11-28 22:12:55'),
(41, 202, 'mission-vision', 'Mission Vision', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:49', '2025-11-28 22:14:49'),
(42, 202, 'course-info', 'Course Info', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:50', '2025-11-28 22:14:50'),
(43, 202, 'criteria-assessment', 'Criteria Assessment', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:50', '2025-11-28 22:14:50'),
(44, 202, 'tlas', 'Tlas', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:50', '2025-11-28 22:14:50'),
(45, 202, 'ilo', 'Ilo', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:51', '2025-11-28 22:14:51'),
(46, 202, 'assessment-tasks-distribution', 'Assessment Tasks Distribution', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:51', '2025-11-28 22:14:51'),
(47, 202, 'textbook-upload', 'Textbook Upload', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:51', '2025-11-28 22:14:51'),
(48, 202, 'iga', 'Iga', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:51', '2025-11-28 22:14:51'),
(49, 202, 'so', 'So', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:52', '2025-11-28 22:14:52'),
(50, 202, 'cdio', 'Cdio', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:52', '2025-11-28 22:14:52'),
(51, 202, 'sdg', 'Sdg', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:52', '2025-11-28 22:14:52'),
(52, 202, 'course-policies', 'Course Policies', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:52', '2025-11-28 22:14:52'),
(53, 202, 'tla', 'Tla', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:53', '2025-11-28 22:14:53'),
(54, 202, 'assessment-mapping', 'Assessment Mapping', NULL, 'draft', 6, 139, 139, '2025-11-28 22:14:53', '2025-11-28 22:14:53'),
(55, 202, 'mission-vision', 'Mission Vision', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 'draft', 7, 139, 139, '2025-11-28 22:15:35', '2025-11-28 22:15:35'),
(56, 211, 'ilo-iga-mapping', 'ILO-IGA Mapping', 'a', 'draft', 1, 155, 155, '2025-11-30 10:31:43', '2025-11-30 10:31:43'),
(57, 211, 'ilo-so-cpa-mapping', 'ILO-SO and ILO-CPA Mapping', 'a', 'draft', 1, 155, 155, '2025-11-30 10:31:44', '2025-11-30 10:32:15'),
(58, 211, 'ilo-cdio-sdg-mapping', 'ILO-CDIO and ILO-SDG Mapping', 'a', 'draft', 1, 155, 155, '2025-11-30 10:31:45', '2025-11-30 10:32:14'),
(59, 213, 'criteria-assessment', 'Criteria for Assessment', 'a', 'draft', 1, 155, 155, '2025-11-30 10:59:24', '2025-11-30 10:59:24'),
(60, 213, 'course-info', 'Course Info', 'a', 'draft', 1, 155, 155, '2025-11-30 10:59:25', '2025-11-30 10:59:25'),
(61, 213, 'mission-vision', 'Mission Vision', 'a', 'draft', 1, 155, 155, '2025-11-30 10:59:25', '2025-11-30 10:59:25'),
(62, 213, 'course-info', 'Course Info', NULL, 'draft', 2, 155, 155, '2025-11-30 11:00:12', '2025-11-30 11:00:12'),
(63, 213, 'mission-vision', 'Mission Vision', NULL, 'draft', 2, 155, 155, '2025-11-30 11:00:13', '2025-11-30 11:00:13'),
(64, 213, 'tlas', 'Teaching, Learning, and Assessment Strategies', NULL, 'draft', 3, 155, 155, '2025-11-30 11:00:34', '2025-11-30 11:00:34'),
(65, 213, 'ilo', 'Intended Learning Outcomes (ILO)', NULL, 'draft', 3, 155, 155, '2025-11-30 11:00:35', '2025-11-30 11:00:35'),
(66, 213, 'criteria-assessment', 'Criteria for Assessment', NULL, 'draft', 3, 155, 155, '2025-11-30 11:00:35', '2025-11-30 11:00:35'),
(67, 214, 'course-info', 'Course Info', NULL, 'draft', 1, 155, 155, '2025-11-30 11:08:34', '2025-11-30 11:08:34'),
(68, 214, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 155, 155, '2025-11-30 11:08:34', '2025-11-30 11:08:34'),
(69, 214, 'criteria-assessment', 'Criteria for Assessment', NULL, 'draft', 1, 155, 155, '2025-11-30 11:08:34', '2025-11-30 11:08:34'),
(70, 214, 'ilo', 'Intended Learning Outcomes (ILO)', NULL, 'draft', 1, 155, 155, '2025-11-30 11:08:34', '2025-11-30 11:08:34'),
(71, 214, 'tlas', 'Teaching, Learning, and Assessment Strategies', NULL, 'draft', 1, 155, 155, '2025-11-30 11:08:35', '2025-11-30 11:08:35'),
(72, 216, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 155, 155, '2025-11-30 11:12:30', '2025-11-30 11:12:30'),
(73, 216, 'course-info', 'Course Info', NULL, 'draft', 1, 155, 155, '2025-11-30 11:12:31', '2025-11-30 11:12:31'),
(74, 216, 'tlas', 'Teaching, Learning, and Assessment Strategies', NULL, 'draft', 1, 155, 155, '2025-11-30 11:12:31', '2025-11-30 11:12:31'),
(75, 216, 'course-info', 'Course Info', NULL, 'draft', 2, 155, 155, '2025-11-30 11:13:31', '2025-11-30 11:13:31'),
(76, 216, 'mission-vision', 'Mission Vision', NULL, 'draft', 2, 155, 155, '2025-11-30 11:13:31', '2025-11-30 11:13:31'),
(77, 216, 'course-info', 'Course Info', NULL, 'draft', 4, 155, 155, '2025-11-30 11:33:30', '2025-11-30 11:33:30'),
(78, 219, 'course-info', 'Course Info', NULL, 'draft', 1, 155, 155, '2025-11-30 18:50:33', '2025-11-30 18:50:33'),
(79, 219, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 155, 155, '2025-11-30 18:50:33', '2025-11-30 18:50:33'),
(80, 219, 'tlas', 'Teaching, Learning, and Assessment Strategies', NULL, 'draft', 1, 155, 155, '2025-11-30 18:50:33', '2025-11-30 18:50:33'),
(81, 221, 'course-info', 'Course Info', NULL, 'draft', 1, 155, 155, '2025-11-30 18:56:40', '2025-11-30 18:56:40'),
(82, 231, 'mission-vision', 'Mission Vision', 'hhjhkhhkhjk', 'draft', 1, 158, 158, '2025-12-01 17:25:29', '2025-12-01 17:25:29'),
(83, 231, 'course-info', 'Course Info', NULL, 'draft', 1, 158, 158, '2025-12-01 17:25:33', '2025-12-01 17:25:33'),
(84, 231, 'criteria-assessment', 'Criteria for Assessment', NULL, 'draft', 1, 158, 158, '2025-12-01 17:25:33', '2025-12-01 17:25:33'),
(85, 235, 'course-info', 'Course Info', NULL, 'draft', 1, 158, 158, '2025-12-01 18:22:05', '2025-12-01 18:24:05'),
(86, 235, 'mission-vision', 'Mission Vision', 'dsf', 'draft', 1, 158, 158, '2025-12-01 18:22:06', '2025-12-01 18:22:11'),
(87, 233, 'course-info', 'Course Info', NULL, 'draft', 1, 158, 158, '2025-12-01 18:25:39', '2025-12-01 18:25:39'),
(88, 233, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 158, 158, '2025-12-01 18:25:39', '2025-12-01 18:25:39'),
(89, 247, 'course-info', 'Course Info', 'No Course Rationale and Description', 'draft', 1, 216, 216, '2025-12-02 20:16:23', '2025-12-02 20:17:54'),
(90, 247, 'criteria-assessment', 'Criteria for Assessment', 'Indicate Criteria', 'draft', 1, 216, 216, '2025-12-02 20:17:05', '2025-12-02 20:17:25'),
(91, 251, 'course-info', 'Course Info', NULL, 'draft', 1, 217, 217, '2025-12-02 21:19:32', '2025-12-02 21:19:32'),
(92, 251, 'mission-vision', 'Mission Vision', NULL, 'draft', 1, 217, 217, '2025-12-02 21:19:33', '2025-12-02 21:19:33'),
(93, 251, 'criteria-assessment', 'Criteria for Assessment', NULL, 'draft', 1, 217, 217, '2025-12-02 21:19:33', '2025-12-02 21:19:33'),
(94, 251, 'ilo', 'Intended Learning Outcomes (ILO)', NULL, 'draft', 1, 217, 217, '2025-12-02 21:19:33', '2025-12-02 21:19:33');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_course_infos`
--

CREATE TABLE `syllabus_course_infos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `course_title` varchar(255) DEFAULT NULL,
  `course_code` varchar(255) DEFAULT NULL,
  `course_category` varchar(255) DEFAULT NULL,
  `course_prerequisites` text DEFAULT NULL,
  `semester` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `credit_hours_text` varchar(255) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `employee_code` varchar(255) DEFAULT NULL,
  `reference_cmo` varchar(255) DEFAULT NULL,
  `instructor_designation` varchar(255) DEFAULT NULL,
  `date_prepared` varchar(255) DEFAULT NULL,
  `instructor_email` varchar(255) DEFAULT NULL,
  `revision_no` varchar(255) DEFAULT NULL,
  `academic_year` varchar(255) DEFAULT NULL,
  `revision_date` varchar(255) DEFAULT NULL,
  `course_description` text DEFAULT NULL,
  `tla_strategies` text DEFAULT NULL,
  `contact_hours` text DEFAULT NULL,
  `contact_hours_lec` text DEFAULT NULL,
  `contact_hours_lab` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_course_infos`
--

INSERT INTO `syllabus_course_infos` (`id`, `syllabus_id`, `course_title`, `course_code`, `course_category`, `course_prerequisites`, `semester`, `year_level`, `credit_hours_text`, `instructor_name`, `employee_code`, `reference_cmo`, `instructor_designation`, `date_prepared`, `instructor_email`, `revision_no`, `academic_year`, `revision_date`, `course_description`, `tla_strategies`, `contact_hours`, `contact_hours_lec`, `contact_hours_lab`, `created_at`, `updated_at`) VALUES
(171, 254, 'Computer Programming', 'CS 111', 'Core Elective', '', '2nd Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2023-2024', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(172, 255, 'Computer Networking 1', 'IT 212', 'Core Elective', '', '2nd Semester', '2nd Year', '5 (3 hrs lec; 2 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2024-2025', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(173, 256, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', '', '1st Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(174, 257, 'Advanced Computer Programming', 'CS 121', 'Core Elective', 'CS 111 - Computer Programming', '1st Semester', '2nd Year', '5 (2 hrs lec; 3 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', '2', '2024-2025', '2025-12-03', NULL, NULL, '2 hours lecture; 3 hours laboratory', '2 hours lecture', '3 hours laboratory', '2025-12-03 08:33:57', '2025-12-03 09:33:48'),
(175, 258, 'Introduction to Computing', 'IT 111', 'Core, Elective, Professional', '', '1st Semester', '1st Year', '3 (3 hrs lec; 0 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2023-2024', NULL, NULL, NULL, '3 hours lecture', '3 hours lecture', NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(176, 259, 'Information Management', 'IT 221', 'Core Elective', '', '2nd Semester', '2nd Year', '5 (3 hrs lec; 2 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2023-2024', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(177, 260, 'Advanced Computer Programming', 'CS 121', 'Core Elective', 'CS 111 - Computer Programming', '1st Semester', '2nd Year', '5 (2 hrs lec; 3 hrs lab)', 'ASIBAR PAUL JUSTINE REY', '22-73610', NULL, 'sad', 'December 03, 2025', '22-73610@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '2 hours lecture; 3 hours laboratory', '2 hours lecture', '3 hours laboratory', '2025-12-03 09:34:39', '2025-12-03 09:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_course_policies`
--

CREATE TABLE `syllabus_course_policies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `section` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `grading_system` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grading_system`)),
  `class_policy` text DEFAULT NULL,
  `missed_exams` text DEFAULT NULL,
  `academic_dishonesty` text DEFAULT NULL,
  `dropping` text DEFAULT NULL,
  `other_policies` text DEFAULT NULL,
  `consultation_advising` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_course_policies`
--

INSERT INTO `syllabus_course_policies` (`id`, `syllabus_id`, `section`, `content`, `position`, `grading_system`, `class_policy`, `missed_exams`, `academic_dishonesty`, `dropping`, `other_policies`, `consultation_advising`, `created_at`, `updated_at`) VALUES
(612, 254, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(613, 254, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(614, 254, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(615, 254, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(616, 254, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(617, 255, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(618, 255, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(619, 255, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(620, 255, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(621, 255, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(622, 256, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(623, 256, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(624, 256, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(625, 256, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(626, 256, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(627, 257, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(628, 257, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(629, 257, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(630, 257, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(631, 257, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(632, 258, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(633, 258, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(634, 258, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(635, 258, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(636, 258, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(637, 259, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(638, 259, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(639, 259, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(640, 259, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(641, 259, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(642, 260, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 09:34:39', '2025-12-03 09:34:39'),
(643, 260, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 09:34:39', '2025-12-03 09:34:39'),
(644, 260, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 09:34:39', '2025-12-03 09:34:39'),
(645, 260, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 09:34:39', '2025-12-03 09:34:39'),
(646, 260, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 09:34:39', '2025-12-03 09:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_criteria`
--

CREATE TABLE `syllabus_criteria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `heading` varchar(255) DEFAULT NULL,
  `section` varchar(255) DEFAULT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_igas`
--

CREATE TABLE `syllabus_igas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_ilos`
--

CREATE TABLE `syllabus_ilos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_ilo_cdio_sdg`
--

CREATE TABLE `syllabus_ilo_cdio_sdg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `ilo_text` text DEFAULT NULL,
  `cdios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cdios`)),
  `sdgs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sdgs`)),
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_ilo_iga`
--

CREATE TABLE `syllabus_ilo_iga` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `ilo_text` varchar(255) DEFAULT NULL,
  `igas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`igas`)),
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_ilo_so_cpa`
--

CREATE TABLE `syllabus_ilo_so_cpa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `ilo_text` varchar(255) DEFAULT NULL,
  `sos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sos`)),
  `c` text DEFAULT NULL,
  `p` text DEFAULT NULL,
  `a` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_mission_visions`
--

CREATE TABLE `syllabus_mission_visions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_mission_visions`
--

INSERT INTO `syllabus_mission_visions` (`id`, `syllabus_id`, `mission`, `vision`, `created_at`, `updated_at`) VALUES
(175, 254, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:26:15', '2025-12-03 08:26:15'),
(176, 255, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:29:17', '2025-12-03 08:29:17'),
(177, 256, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:31:56', '2025-12-03 08:31:56'),
(178, 257, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:33:57', '2025-12-03 08:33:57'),
(179, 258, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:35:05', '2025-12-03 08:35:05'),
(180, 259, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 08:37:10', '2025-12-03 08:37:10'),
(181, 260, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-03 09:34:39', '2025-12-03 09:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_sdgs`
--

CREATE TABLE `syllabus_sdgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_sections`
--

CREATE TABLE `syllabus_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_sos`
--

CREATE TABLE `syllabus_sos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_submissions`
--

CREATE TABLE `syllabus_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `submitted_by` bigint(20) UNSIGNED NOT NULL,
  `from_status` enum('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL,
  `to_status` enum('draft','pending_review','revision','approved','final_approval','final_approved') NOT NULL,
  `action_by` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `action_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_submissions`
--

INSERT INTO `syllabus_submissions` (`id`, `syllabus_id`, `submitted_by`, `from_status`, `to_status`, `action_by`, `remarks`, `action_at`, `created_at`, `updated_at`) VALUES
(158, 254, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:27:10', '2025-12-03 08:27:10', '2025-12-03 08:27:10'),
(159, 254, 226, 'pending_review', 'approved', 224, NULL, '2025-12-03 08:27:19', '2025-12-03 08:27:19', '2025-12-03 08:27:19'),
(160, 254, 226, 'approved', 'final_approval', 226, NULL, '2025-12-03 08:27:50', '2025-12-03 08:27:50', '2025-12-03 08:27:50'),
(161, 254, 226, 'final_approval', 'final_approved', 225, NULL, '2025-12-03 08:28:36', '2025-12-03 08:28:36', '2025-12-03 08:28:36'),
(162, 255, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:29:30', '2025-12-03 08:29:30', '2025-12-03 08:29:30'),
(163, 255, 226, 'pending_review', 'approved', 224, NULL, '2025-12-03 08:30:26', '2025-12-03 08:30:26', '2025-12-03 08:30:26'),
(164, 255, 226, 'approved', 'final_approval', 226, NULL, '2025-12-03 08:30:46', '2025-12-03 08:30:46', '2025-12-03 08:30:46'),
(165, 256, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:32:07', '2025-12-03 08:32:07', '2025-12-03 08:32:07'),
(166, 256, 226, 'pending_review', 'approved', 224, NULL, '2025-12-03 08:32:22', '2025-12-03 08:32:22', '2025-12-03 08:32:22'),
(167, 257, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:34:00', '2025-12-03 08:34:00', '2025-12-03 08:34:00'),
(168, 257, 226, 'pending_review', 'revision', 224, NULL, '2025-12-03 08:34:16', '2025-12-03 08:34:16', '2025-12-03 08:34:16'),
(169, 259, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:37:13', '2025-12-03 08:37:13', '2025-12-03 08:37:13'),
(170, 258, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 08:38:11', '2025-12-03 08:38:11', '2025-12-03 08:38:11'),
(171, 257, 226, 'revision', 'pending_review', 226, NULL, '2025-12-03 08:38:14', '2025-12-03 08:38:14', '2025-12-03 08:38:14'),
(172, 257, 226, 'pending_review', 'revision', 224, NULL, '2025-12-03 09:33:48', '2025-12-03 09:33:48', '2025-12-03 09:33:48'),
(173, 260, 226, 'draft', 'pending_review', 226, NULL, '2025-12-03 09:34:42', '2025-12-03 09:34:42', '2025-12-03 09:34:42');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_textbooks`
--

CREATE TABLE `syllabus_textbooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `type` enum('main','other') NOT NULL DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `textbook_chunks`
--

CREATE TABLE `textbook_chunks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `textbook_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Optional reference id to a textbook record',
  `source_path` varchar(255) DEFAULT NULL,
  `chunk_index` int(11) NOT NULL DEFAULT 0,
  `content` longtext NOT NULL,
  `embedding` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`embedding`)),
  `tokens_estimate` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `textbook_chunks`
--

INSERT INTO `textbook_chunks` (`id`, `textbook_id`, `source_path`, `chunk_index`, `content`, `embedding`, `tokens_estimate`, `created_at`, `updated_at`) VALUES
(5256, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 0, 'Introduction to Computing Explorations in Language, Logic, and Machines David Evans University of Virginia For the latest version of this book and supplementary materials, visit: http://computingbook.org Version: August 19, 2011 Attribution-Noncommercial-Share Alike 3.0 United States License Contents 1 Computing 1 1.1 Processes, Procedures, and Computers . . . . . . . . . . . . . . . . 2 1.2 Measuring Computing Power . . . . . . . . . . . . . . . . . . . . . 3 1.2.1 Information . . . . . . . . . . . . . . . . . . . . . . . . . . . 3 1.2.2 Representing Data . . . . . . . . . . . . . . . . . . . . . . . 8 1.2.3 Growth of Computing Power . . . . . . . . . . . . . . . . . 12 1.3 Science, Engineering, and the Liberal Arts . . . . . . . . . . . . . . 13 1.4 Summary and Roadmap . . . . . . . . . . . . . . . . . . . . . . . . 16 Part I: Dening Procedures 2 Language 19 2.1 Surface Forms and Meanings . . . . . . . . . . . . . . . . . . . . . 19 2.2 Language Construction . . . . . . . . . . . . . . . . . . . . . . . . . 20 2.3 Recursive Transition Networks . . . . . . . . . . . . . . . . . . . . . 22 2.4 Replacement Grammars . . . . . . . . . . . . . . . . . . . . . . . . 26 2.5 Summary . . ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5257, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 1, '. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 32 3 Programming 35 3.1 Problems with Natural Languages . . . . . . . . . . . . . . . . . . . 36 3.2 Programming Languages . . . . . . . . . . . . . . . . . . . . . . . . 37 3.3 Scheme . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 39 3.4 Expressions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 40 3.4.1 Primitives . . . . . . . . . . . . . . . . . . . . . . . . . . . . 40 3.4.2 Application Expressions . . . . . . . . . . . . . . . . . . . . 41 3.5 Denitions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 44 3.6 Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 45 3.6.1 Making Procedures . . . . . . . . . . . . . . . . . . . . . . . 45 3.6.2 Substitution Model of Evaluation . . . . . . . . . . . . . . . 46 3.7 Decisions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 48 3.8 Evaluation Rules . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 50 3.9 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 52 4 Problems and Procedures 53 4.1 Solving Problems . . . . . . . . . . . . . . . . . . . . . . .', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5258, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 2, ' . . . . . 53 4.2 Composing Procedures . . . . . . . . . . . . . . . . . . . . . . . . . 54 4.2.1 Procedures as Inputs and Outputs . . . . . . . . . . . . . . 55 4.3 Recursive Problem Solving . . . . . . . . . . . . . . . . . . . . . . . 56 4.4 Evaluating Recursive Applications . . . . . . . . . . . . . . . . . . . 64 4.5 Developing Complex Programs . . . . . . . . . . . . . . . . . . . . 67 4.5.1 Printing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 68 4.5.2 Tracing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 69 4.6 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 73 5 Data 75 5.1 Types . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 75 5.2 Pairs . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 77 5.2.1 Making Pairs . . . . . . . . . . . . . . . . . . . . . . . . . . . 79 5.2.2 Triples to Octuples . . . . . . . . . . . . . . . . . . . . . . . 80 5.3 Lists . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 81 5.4 List Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 83 5.4.1 Procedures that Examine Lists . . . . . . . . . . . . . . . . . 83 5', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5259, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 3, '.4.2 Generic Accumulators . . . . . . . . . . . . . . . . . . . . . 84 5.4.3 Procedures that Construct Lists . . . . . . . . . . . . . . . . 86 5.5 Lists of Lists . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 90 5.6 Data Abstraction . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 92 5.7 Summary of Part I . . . . . . . . . . . . . . . . . . . . . . . . . . . . 102 Part II: Analyzing Procedures 6 Machines 105 6.1 History of Computing Machines . . . . . . . . . . . . . . . . . . . . 106 6.2 Mechanizing Logic . . . . . . . . . . . . . . . . . . . . . . . . . . . 108 6.2.1 Implementing Logic . . . . . . . . . . . . . . . . . . . . . . 109 6.2.2 Composing Operations . . . . . . . . . . . . . . . . . . . . . 111 6.2.3 Arithmetic . . . . . . . . . . . . . . . . . . . . . . . . . . . . 114 6.3 Modeling Computing . . . . . . . . . . . . . . . . . . . . . . . . . . 116 6.3.1 Turing Machines . . . . . . . . . . . . . . . . . . . . . . . . 118 6.4 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 123 7 Cost 125 7.1 Empirical Measurements . . . . . . . . . . . . . . . . . . . . . . . . 125 7.2 Orders of Growth . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5260, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 4, '. . . . . . . . . . 129 7.2.1 BigO. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 130 7.2.2 Omega . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 133 7.2.3 Theta . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 134 7.3 Analyzing Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . 136 7.3.1 Input Size . . . . . . . . . . . . . . . . . . . . . . . . . . . . 136 7.3.2 Running Time . . . . . . . . . . . . . . . . . . . . . . . . . . 137 7.3.3 Worst Case Input . . . . . . . . . . . . . . . . . . . . . . . . 138 7.4 Growth Rates . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 139 7.4.1 No Growth: Constant Time . . . . . . . . . . . . . . . . . . 139 7.4.2 Linear Growth . . . . . . . . . . . . . . . . . . . . . . . . . . 140 7.4.3 Quadratic Growth . . . . . . . . . . . . . . . . . . . . . . . . 145 7.4.4 Exponential Growth . . . . . . . . . . . . . . . . . . . . . . . 147 7.4.5 Faster than Exponential Growth . . . . . . . . . . . . . . . . 149 7.4.6 Non-terminating Procedures . . . . . . . . . . . . . . . . . 149 7.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 149 8 Sorting and Searching 153 8.1 ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5261, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 5, 'Sorting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 153 8.1.1 Best-First Sort . . . . . . . . . . . . . . . . . . . . . . . . . . 153 8.1.2 Insertion Sort . . . . . . . . . . . . . . . . . . . . . . . . . . 157 8.1.3 Quicker Sorting . . . . . . . . . . . . . . . . . . . . . . . . . 158 8.1.4 Binary Trees . . . . . . . . . . . . . . . . . . . . . . . . . . . 161 8.1.5 Quicksort . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 166 8.2 Searching . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 167 8.2.1 Unstructured Search . . . . . . . . . . . . . . . . . . . . . . 168 8.2.2 Binary Search . . . . . . . . . . . . . . . . . . . . . . . . . . 168 8.2.3 Indexed Search . . . . . . . . . . . . . . . . . . . . . . . . . 169 8.3 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 178 Part III: Improving Expressiveness 9 Mutation 179 9.1 Assignment . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 179 9.2 Impact of Mutation . . . . . . . . . . . . . . . . . . . . . . . . . . . 181 9.2.1 Names, Places, Frames, and Environments . . . . . . . . . 182 9.2.2 Evaluation Rules with State . . . . . . . . . . . . . .', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5262, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 6, ' . . . . 183 9.3 Mutable Pairs and Lists . . . . . . . . . . . . . . . . . . . . . . . . . 186 9.4 Imperative Programming . . . . . . . . . . . . . . . . . . . . . . . . 188 9.4.1 List Mutators . . . . . . . . . . . . . . . . . . . . . . . . . . . 188 9.4.2 Imperative Control Structures . . . . . . . . . . . . . . . . . 191 9.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 193 10 Objects 195 10.1 Packaging Procedures and State . . . . . . . . . . . . . . . . . . . . 196 10.1.1 Encapsulation . . . . . . . . . . . . . . . . . . . . . . . . . . 196 10.1.2 Messages . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 197 10.1.3 Object Terminology . . . . . . . . . . . . . . . . . . . . . . . 199 10.2 Inheritance . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 200 10.2.1 Implementing Subclasses . . . . . . . . . . . . . . . . . . . 202 10.2.2 Overriding Methods . . . . . . . . . . . . . . . . . . . . . . 204 10.3 Object-Oriented Programming . . . . . . . . . . . . . . . . . . . . 207 10.4 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 209 11 Interpreters 211 11.1 Python . . . . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5263, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 7, '. . . . . . . . . . . . . 212 11.1.1 Python Programs . . . . . . . . . . . . . . . . . . . . . . . . 213 11.1.2 Data Types . . . . . . . . . . . . . . . . . . . . . . . . . . . . 216 11.1.3 Applications and Invocations . . . . . . . . . . . . . . . . . 219 11.1.4 Control Statements . . . . . . . . . . . . . . . . . . . . . . . 219 11.2 Parser . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 221 11.3 Evaluator . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 223 11.3.1 Primitives . . . . . . . . . . . . . . . . . . . . . . . . . . . . 223 11.3.2 If Expressions . . . . . . . . . . . . . . . . . . . . . . . . . . 225 11.3.3 Denitions and Names . . . . . . . . . . . . . . . . . . . . . 226 11.3.4 Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . 227 11.3.5 Application . . . . . . . . . . . . . . . . . . . . . . . . . . . 228 11.3.6 Finishing the Interpreter . . . . . . . . . . . . . . . . . . . . 229 11.4 Lazy Evaluation . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 229 11.4.1 Lazy Interpreter . . . . . . . . . . . . . . . . . . . . . . . . . 230 11.4.2 Lazy Programming . . . . . . . . . . . . . . . . . . . . . . . 232', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5264, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 8, ' 11.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 235 Part IV: The Limits of Computing 12 Computability 237 12.1 Mechanizing Reasoning . . . . . . . . . . . . . . . . . . . . . . . . 237 12.1.1 G¨odel\'s Incompleteness Theorem . . . . . . . . . . . . . . . 240 12.2 The Halting Problem . . . . . . . . . . . . . . . . . . . . . . . . . . 241 12.3 Universality . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 244 12.4 Proving Non-Computability . . . . . . . . . . . . . . . . . . . . . . 245 12.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 251 Indexes 253 Index . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 253 People . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 256 List of Explorations 1.1 Guessing Numbers . . . . . . . . . . . . . . . . . . . . . . . . . . . . 7 1.2 Twenty Questions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 8 2.1 Power of Language Systems . . . . . . . . . . . . . . . . . . . . . . . 29 4.1 Square Roots . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 62 4.2 Recipes forp. . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5265, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 9, '. . . . . . . . . . . . . 69 4.3 Recursive Denitions and Games . . . . . . . . . . . . . . . . . . . . 71 5.1 Pascal\'s Triangle . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 91 5.2 Pegboard Puzzle . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 93 7.1 Multiplying Like Rabbits . . . . . . . . . . . . . . . . . . . . . . . . . 127 8.1 Searching the Web . . . . . . . . . . . . . . . . . . . . . . . . . . . . 177 12.1 Virus Detection . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 246 12.2 Busy Beavers . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 249 List of Figures 1.1 Using three bits to distinguish eight possible values. . . . . . . . . . . 6 2.1 Simple recursive transition network. . . . . . . . . . . . . . . . . . . . 22 2.2 RTN with a cycle. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 23 2.3 Recursive transition network with subnetworks. . . . . . . . . . . . . 24 2.4 AlternateNounsubnetwork. . . . . . . . . . . . . . . . . . . . . . . . . 24 2.5 RTN generating Alice runs. . . . . . . . . . . . . . . . . . . . . . . . . 25 2.6 System power relationships. . . . . . . . . . . . . . . . . . . . . . . . . 30 ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5266, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 10, '2.7 Converting theNumberproductions to an RTN. . . . . . . . . . . . . 31 2.8 Converting theMoreDigitsproductions to an RTN. . . . . . . . . . . . 31 2.9 Converting theDigitproductions to an RTN. . . . . . . . . . . . . . . 32 3.1 Running a Scheme program. . . . . . . . . . . . . . . . . . . . . . . . . 39 4.1 A procedure maps inputs to an output. . . . . . . . . . . . . . . . . . . 54 4.2 Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 54 4.3 Circular Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . . 57 4.4 Recursive Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . 58 4.5 Cornering the Queen. . . . . . . . . . . . . . . . . . . . . . . . . . . . . 72 5.1 Pegboard Puzzle. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 93 6.1 Computingandwith wine. . . . . . . . . . . . . . . . . . . . . . . . . . 110 6.2 Computing logicalorandnotwith wine . . . . . . . . . . . . . . . . . 111 6.3 Computingand3by composing twoandfunctions. . . . . . . . . . . 112 6.4 Turing Machine model. . . . . . . . . . . . . . . . . . . . . . . . . . . . 119 6.5 Rules for checking balanced parentheses Turing Machine. . . . . . . . 121 6.6', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5267, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 11, ' Checking parentheses Turing Machine. . . . . . . . . . . . . . . . . . 121 7.1 Evaluation ofboprocedure. . . . . . . . . . . . . . . . . . . . . . . . 128 7.2 Visualization of the setsO(f),W(f), andQ(f). . . . . . . . . . . . . . 130 7.3 Orders of Growth. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 131 8.1 Unbalanced trees. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 165 9.1 Sample environments. . . . . . . . . . . . . . . . . . . . . . . . . . . . 182 9.2 Environment created to evaluate (bigger 3 4). . . . . . . . . . . . . . . 184 9.3 Environment after evaluating (dene inc(make-adder1)). . . . . . . 185 9.4 Environment for evaluating the body of (inc 149). . . . . . . . . . . . . 186 9.5 Mutable pair created by evaluating (set-mcdr! pair pair ). . . . . . . . 187 9.6 MutableList created by evaluating (mlist 1 2 3). . . . . . . . . . . . . . 187 10.1 Environment produced by evaluating: . . . . . . . . . . . . . . . . . . 197 10.2 Inheritance hierarchy. . . . . . . . . . . . . . . . . . . . . . . . . . . . 201 10.3 Counter class hierarchy. . . . . . . . . . . . . . . . . . . . . . . . . . . 206 12.1 Incomplete and inconsistent axiomatic systems. .', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5268, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 12, ' . . . . . . . . . . . 239 12.2 Universal Turing Machine. . . . . . . . . . . . . . . . . . . . . . . . . . 245 12.3 Two-state Busy Beaver Machine. . . . . . . . . . . . . . . . . . . . . . 249 Image Credits Most of the images in the book, including the tiles on the cover, were generated by the author. Some of the tile images on the cover are from ickr creative commons licenses images from: ell brown, Johnson Cameraface, cogdogblog, Cyberslayer, dmealif- fe, Dunechaser, MichaelFitz, Wole Fox, glingl, jurvetson, KayVee.INC, michaeld- beavers, and Oneras. The Van GoghStarry Nightimage from Section 1.2.2 is from the Google Art Project. The Apollo Guidance Computer image in Section 1.2.3 was released by NASA and is in the public domain. The trafc light in Section 2.1 is from iStock- Photo, and the rotary trafc signal is from the Wikimedia Commons. The pic- ture of Grace Hopper in Chapter 3 is from the Computer History Museum. The playing card images in Chapter 4 are from iStockPhoto. The images of Gauss, Heron, and Grace Hopper\'s bug are in the public domain. The Dilbert comic in Chapter 4 is licensed from United Feature Syndicate, Inc. The Pascal\'s triangle image in Excursion 5.1 ', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5269, 104, 'syllabi/textbooks/Ww3pfG4oOieCuDDkU5vcVfZRMKKsMoCTPZyn7dmZ.pdf', 13, 'is from Wikipedia and is in the public domain. The image of Ada Lovelace in Chapter 6 is from the Wikimedia Commons, of a painting by Margaret Carpenter. The odomoter image in Chapter 7 is from iStockPhoto, as is the image of the frustrated student. The Python snake charmer in Section 11.1 is from iStockPhoto. The Dynabook images at the end of Chapter 10 are from Alan Kay\'s paper. The xkcd comic a', NULL, 300, '2025-11-30 23:24:52', '2025-11-30 23:24:52'),
(5270, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 0, 'Introduction to Computing Explorations in Language, Logic, and Machines David Evans University of Virginia For the latest version of this book and supplementary materials, visit: http://computingbook.org Version: August 19, 2011 Attribution-Noncommercial-Share Alike 3.0 United States License Contents 1 Computing 1 1.1 Processes, Procedures, and Computers . . . . . . . . . . . . . . . . 2 1.2 Measuring Computing Power . . . . . . . . . . . . . . . . . . . . . 3 1.2.1 Information . . . . . . . . . . . . . . . . . . . . . . . . . . . 3 1.2.2 Representing Data . . . . . . . . . . . . . . . . . . . . . . . 8 1.2.3 Growth of Computing Power . . . . . . . . . . . . . . . . . 12 1.3 Science, Engineering, and the Liberal Arts . . . . . . . . . . . . . . 13 1.4 Summary and Roadmap . . . . . . . . . . . . . . . . . . . . . . . . 16 Part I: Dening Procedures 2 Language 19 2.1 Surface Forms and Meanings . . . . . . . . . . . . . . . . . . . . . 19 2.2 Language Construction . . . . . . . . . . . . . . . . . . . . . . . . . 20 2.3 Recursive Transition Networks . . . . . . . . . . . . . . . . . . . . . 22 2.4 Replacement Grammars . . . . . . . . . . . . . . . . . . . . . . . . 26 2.5 Summary . . ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5271, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 1, '. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 32 3 Programming 35 3.1 Problems with Natural Languages . . . . . . . . . . . . . . . . . . . 36 3.2 Programming Languages . . . . . . . . . . . . . . . . . . . . . . . . 37 3.3 Scheme . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 39 3.4 Expressions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 40 3.4.1 Primitives . . . . . . . . . . . . . . . . . . . . . . . . . . . . 40 3.4.2 Application Expressions . . . . . . . . . . . . . . . . . . . . 41 3.5 Denitions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 44 3.6 Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 45 3.6.1 Making Procedures . . . . . . . . . . . . . . . . . . . . . . . 45 3.6.2 Substitution Model of Evaluation . . . . . . . . . . . . . . . 46 3.7 Decisions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 48 3.8 Evaluation Rules . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 50 3.9 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 52 4 Problems and Procedures 53 4.1 Solving Problems . . . . . . . . . . . . . . . . . . . . . . .', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5272, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 2, ' . . . . . 53 4.2 Composing Procedures . . . . . . . . . . . . . . . . . . . . . . . . . 54 4.2.1 Procedures as Inputs and Outputs . . . . . . . . . . . . . . 55 4.3 Recursive Problem Solving . . . . . . . . . . . . . . . . . . . . . . . 56 4.4 Evaluating Recursive Applications . . . . . . . . . . . . . . . . . . . 64 4.5 Developing Complex Programs . . . . . . . . . . . . . . . . . . . . 67 4.5.1 Printing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 68 4.5.2 Tracing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 69 4.6 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 73 5 Data 75 5.1 Types . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 75 5.2 Pairs . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 77 5.2.1 Making Pairs . . . . . . . . . . . . . . . . . . . . . . . . . . . 79 5.2.2 Triples to Octuples . . . . . . . . . . . . . . . . . . . . . . . 80 5.3 Lists . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 81 5.4 List Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 83 5.4.1 Procedures that Examine Lists . . . . . . . . . . . . . . . . . 83 5', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5273, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 3, '.4.2 Generic Accumulators . . . . . . . . . . . . . . . . . . . . . 84 5.4.3 Procedures that Construct Lists . . . . . . . . . . . . . . . . 86 5.5 Lists of Lists . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 90 5.6 Data Abstraction . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 92 5.7 Summary of Part I . . . . . . . . . . . . . . . . . . . . . . . . . . . . 102 Part II: Analyzing Procedures 6 Machines 105 6.1 History of Computing Machines . . . . . . . . . . . . . . . . . . . . 106 6.2 Mechanizing Logic . . . . . . . . . . . . . . . . . . . . . . . . . . . 108 6.2.1 Implementing Logic . . . . . . . . . . . . . . . . . . . . . . 109 6.2.2 Composing Operations . . . . . . . . . . . . . . . . . . . . . 111 6.2.3 Arithmetic . . . . . . . . . . . . . . . . . . . . . . . . . . . . 114 6.3 Modeling Computing . . . . . . . . . . . . . . . . . . . . . . . . . . 116 6.3.1 Turing Machines . . . . . . . . . . . . . . . . . . . . . . . . 118 6.4 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 123 7 Cost 125 7.1 Empirical Measurements . . . . . . . . . . . . . . . . . . . . . . . . 125 7.2 Orders of Growth . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5274, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 4, '. . . . . . . . . . 129 7.2.1 BigO. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 130 7.2.2 Omega . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 133 7.2.3 Theta . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 134 7.3 Analyzing Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . 136 7.3.1 Input Size . . . . . . . . . . . . . . . . . . . . . . . . . . . . 136 7.3.2 Running Time . . . . . . . . . . . . . . . . . . . . . . . . . . 137 7.3.3 Worst Case Input . . . . . . . . . . . . . . . . . . . . . . . . 138 7.4 Growth Rates . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 139 7.4.1 No Growth: Constant Time . . . . . . . . . . . . . . . . . . 139 7.4.2 Linear Growth . . . . . . . . . . . . . . . . . . . . . . . . . . 140 7.4.3 Quadratic Growth . . . . . . . . . . . . . . . . . . . . . . . . 145 7.4.4 Exponential Growth . . . . . . . . . . . . . . . . . . . . . . . 147 7.4.5 Faster than Exponential Growth . . . . . . . . . . . . . . . . 149 7.4.6 Non-terminating Procedures . . . . . . . . . . . . . . . . . 149 7.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 149 8 Sorting and Searching 153 8.1 ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5275, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 5, 'Sorting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 153 8.1.1 Best-First Sort . . . . . . . . . . . . . . . . . . . . . . . . . . 153 8.1.2 Insertion Sort . . . . . . . . . . . . . . . . . . . . . . . . . . 157 8.1.3 Quicker Sorting . . . . . . . . . . . . . . . . . . . . . . . . . 158 8.1.4 Binary Trees . . . . . . . . . . . . . . . . . . . . . . . . . . . 161 8.1.5 Quicksort . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 166 8.2 Searching . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 167 8.2.1 Unstructured Search . . . . . . . . . . . . . . . . . . . . . . 168 8.2.2 Binary Search . . . . . . . . . . . . . . . . . . . . . . . . . . 168 8.2.3 Indexed Search . . . . . . . . . . . . . . . . . . . . . . . . . 169 8.3 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 178 Part III: Improving Expressiveness 9 Mutation 179 9.1 Assignment . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 179 9.2 Impact of Mutation . . . . . . . . . . . . . . . . . . . . . . . . . . . 181 9.2.1 Names, Places, Frames, and Environments . . . . . . . . . 182 9.2.2 Evaluation Rules with State . . . . . . . . . . . . . .', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5276, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 6, ' . . . . 183 9.3 Mutable Pairs and Lists . . . . . . . . . . . . . . . . . . . . . . . . . 186 9.4 Imperative Programming . . . . . . . . . . . . . . . . . . . . . . . . 188 9.4.1 List Mutators . . . . . . . . . . . . . . . . . . . . . . . . . . . 188 9.4.2 Imperative Control Structures . . . . . . . . . . . . . . . . . 191 9.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 193 10 Objects 195 10.1 Packaging Procedures and State . . . . . . . . . . . . . . . . . . . . 196 10.1.1 Encapsulation . . . . . . . . . . . . . . . . . . . . . . . . . . 196 10.1.2 Messages . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 197 10.1.3 Object Terminology . . . . . . . . . . . . . . . . . . . . . . . 199 10.2 Inheritance . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 200 10.2.1 Implementing Subclasses . . . . . . . . . . . . . . . . . . . 202 10.2.2 Overriding Methods . . . . . . . . . . . . . . . . . . . . . . 204 10.3 Object-Oriented Programming . . . . . . . . . . . . . . . . . . . . 207 10.4 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 209 11 Interpreters 211 11.1 Python . . . . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5277, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 7, '. . . . . . . . . . . . . 212 11.1.1 Python Programs . . . . . . . . . . . . . . . . . . . . . . . . 213 11.1.2 Data Types . . . . . . . . . . . . . . . . . . . . . . . . . . . . 216 11.1.3 Applications and Invocations . . . . . . . . . . . . . . . . . 219 11.1.4 Control Statements . . . . . . . . . . . . . . . . . . . . . . . 219 11.2 Parser . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 221 11.3 Evaluator . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 223 11.3.1 Primitives . . . . . . . . . . . . . . . . . . . . . . . . . . . . 223 11.3.2 If Expressions . . . . . . . . . . . . . . . . . . . . . . . . . . 225 11.3.3 Denitions and Names . . . . . . . . . . . . . . . . . . . . . 226 11.3.4 Procedures . . . . . . . . . . . . . . . . . . . . . . . . . . . . 227 11.3.5 Application . . . . . . . . . . . . . . . . . . . . . . . . . . . 228 11.3.6 Finishing the Interpreter . . . . . . . . . . . . . . . . . . . . 229 11.4 Lazy Evaluation . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 229 11.4.1 Lazy Interpreter . . . . . . . . . . . . . . . . . . . . . . . . . 230 11.4.2 Lazy Programming . . . . . . . . . . . . . . . . . . . . . . . 232', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5278, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 8, ' 11.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 235 Part IV: The Limits of Computing 12 Computability 237 12.1 Mechanizing Reasoning . . . . . . . . . . . . . . . . . . . . . . . . 237 12.1.1 G¨odel\'s Incompleteness Theorem . . . . . . . . . . . . . . . 240 12.2 The Halting Problem . . . . . . . . . . . . . . . . . . . . . . . . . . 241 12.3 Universality . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 244 12.4 Proving Non-Computability . . . . . . . . . . . . . . . . . . . . . . 245 12.5 Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 251 Indexes 253 Index . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 253 People . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 256 List of Explorations 1.1 Guessing Numbers . . . . . . . . . . . . . . . . . . . . . . . . . . . . 7 1.2 Twenty Questions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 8 2.1 Power of Language Systems . . . . . . . . . . . . . . . . . . . . . . . 29 4.1 Square Roots . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 62 4.2 Recipes forp. . . . . . . . . . . . . . . . . . . ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5279, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 9, '. . . . . . . . . . . . . 69 4.3 Recursive Denitions and Games . . . . . . . . . . . . . . . . . . . . 71 5.1 Pascal\'s Triangle . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 91 5.2 Pegboard Puzzle . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 93 7.1 Multiplying Like Rabbits . . . . . . . . . . . . . . . . . . . . . . . . . 127 8.1 Searching the Web . . . . . . . . . . . . . . . . . . . . . . . . . . . . 177 12.1 Virus Detection . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 246 12.2 Busy Beavers . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 249 List of Figures 1.1 Using three bits to distinguish eight possible values. . . . . . . . . . . 6 2.1 Simple recursive transition network. . . . . . . . . . . . . . . . . . . . 22 2.2 RTN with a cycle. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 23 2.3 Recursive transition network with subnetworks. . . . . . . . . . . . . 24 2.4 AlternateNounsubnetwork. . . . . . . . . . . . . . . . . . . . . . . . . 24 2.5 RTN generating Alice runs. . . . . . . . . . . . . . . . . . . . . . . . . 25 2.6 System power relationships. . . . . . . . . . . . . . . . . . . . . . . . . 30 ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5280, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 10, '2.7 Converting theNumberproductions to an RTN. . . . . . . . . . . . . 31 2.8 Converting theMoreDigitsproductions to an RTN. . . . . . . . . . . . 31 2.9 Converting theDigitproductions to an RTN. . . . . . . . . . . . . . . 32 3.1 Running a Scheme program. . . . . . . . . . . . . . . . . . . . . . . . . 39 4.1 A procedure maps inputs to an output. . . . . . . . . . . . . . . . . . . 54 4.2 Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 54 4.3 Circular Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . . 57 4.4 Recursive Composition. . . . . . . . . . . . . . . . . . . . . . . . . . . 58 4.5 Cornering the Queen. . . . . . . . . . . . . . . . . . . . . . . . . . . . . 72 5.1 Pegboard Puzzle. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 93 6.1 Computingandwith wine. . . . . . . . . . . . . . . . . . . . . . . . . . 110 6.2 Computing logicalorandnotwith wine . . . . . . . . . . . . . . . . . 111 6.3 Computingand3by composing twoandfunctions. . . . . . . . . . . 112 6.4 Turing Machine model. . . . . . . . . . . . . . . . . . . . . . . . . . . . 119 6.5 Rules for checking balanced parentheses Turing Machine. . . . . . . . 121 6.6', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5281, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 11, ' Checking parentheses Turing Machine. . . . . . . . . . . . . . . . . . 121 7.1 Evaluation ofboprocedure. . . . . . . . . . . . . . . . . . . . . . . . 128 7.2 Visualization of the setsO(f),W(f), andQ(f). . . . . . . . . . . . . . 130 7.3 Orders of Growth. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 131 8.1 Unbalanced trees. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 165 9.1 Sample environments. . . . . . . . . . . . . . . . . . . . . . . . . . . . 182 9.2 Environment created to evaluate (bigger 3 4). . . . . . . . . . . . . . . 184 9.3 Environment after evaluating (dene inc(make-adder1)). . . . . . . 185 9.4 Environment for evaluating the body of (inc 149). . . . . . . . . . . . . 186 9.5 Mutable pair created by evaluating (set-mcdr! pair pair ). . . . . . . . 187 9.6 MutableList created by evaluating (mlist 1 2 3). . . . . . . . . . . . . . 187 10.1 Environment produced by evaluating: . . . . . . . . . . . . . . . . . . 197 10.2 Inheritance hierarchy. . . . . . . . . . . . . . . . . . . . . . . . . . . . 201 10.3 Counter class hierarchy. . . . . . . . . . . . . . . . . . . . . . . . . . . 206 12.1 Incomplete and inconsistent axiomatic systems. .', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5282, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 12, ' . . . . . . . . . . . 239 12.2 Universal Turing Machine. . . . . . . . . . . . . . . . . . . . . . . . . . 245 12.3 Two-state Busy Beaver Machine. . . . . . . . . . . . . . . . . . . . . . 249 Image Credits Most of the images in the book, including the tiles on the cover, were generated by the author. Some of the tile images on the cover are from ickr creative commons licenses images from: ell brown, Johnson Cameraface, cogdogblog, Cyberslayer, dmealif- fe, Dunechaser, MichaelFitz, Wole Fox, glingl, jurvetson, KayVee.INC, michaeld- beavers, and Oneras. The Van GoghStarry Nightimage from Section 1.2.2 is from the Google Art Project. The Apollo Guidance Computer image in Section 1.2.3 was released by NASA and is in the public domain. The trafc light in Section 2.1 is from iStock- Photo, and the rotary trafc signal is from the Wikimedia Commons. The pic- ture of Grace Hopper in Chapter 3 is from the Computer History Museum. The playing card images in Chapter 4 are from iStockPhoto. The images of Gauss, Heron, and Grace Hopper\'s bug are in the public domain. The Dilbert comic in Chapter 4 is licensed from United Feature Syndicate, Inc. The Pascal\'s triangle image in Excursion 5.1 ', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00'),
(5283, 105, 'syllabi/textbooks/YjWCUT8MMGug2vWGfdh4rm7DsPNqv4HLvQG8xRNH.pdf', 13, 'is from Wikipedia and is in the public domain. The image of Ada Lovelace in Chapter 6 is from the Wikimedia Commons, of a painting by Margaret Carpenter. The odomoter image in Chapter 7 is from iStockPhoto, as is the image of the frustrated student. The Python snake charmer in Section 11.1 is from iStockPhoto. The Dynabook images at the end of Chapter 10 are from Alan Kay\'s paper. The xkcd comic a', NULL, 300, '2025-12-01 00:57:00', '2025-12-01 00:57:00');

-- --------------------------------------------------------

--
-- Table structure for table `tla`
--

CREATE TABLE `tla` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `ch` varchar(255) DEFAULT NULL,
  `topic` text DEFAULT NULL,
  `wks` varchar(255) DEFAULT NULL,
  `outcomes` text DEFAULT NULL,
  `ilo` varchar(255) DEFAULT NULL,
  `so` varchar(255) DEFAULT NULL,
  `delivery` varchar(255) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tla`
--

INSERT INTO `tla` (`id`, `syllabus_id`, `ch`, `topic`, `wks`, `outcomes`, `ilo`, `so`, `delivery`, `position`, `created_at`, `updated_at`) VALUES
(1010, 157, '', '', '2', '', '', '', '', 0, '2025-11-25 01:48:33', '2025-11-25 01:48:33'),
(1011, 157, '', '', '3', '', '', '', '', 1, '2025-11-25 01:48:33', '2025-11-25 01:48:33'),
(1012, 157, '', '', '1', '', '', '', '', 2, '2025-11-25 01:48:33', '2025-11-25 01:48:33'),
(1013, 157, '', '', '4', '', '', '', '', 3, '2025-11-25 01:48:33', '2025-11-25 01:48:33'),
(1015, 179, 'a', 'a', '1', '', '', '', '', 0, '2025-11-26 19:46:57', '2025-11-26 19:46:57'),
(1020, 180, '', '', '23', '', '', '', '', 0, '2025-11-26 19:55:01', '2025-11-26 19:55:01'),
(1134, 225, '', 'Orientation & Introduction', '1', 'VMGO Orientation, Presentation of \nSyllabus, Class Rules', '', '', 'Face-to-face/  Online Discussion', 0, '2025-12-01 03:46:49', '2025-12-01 03:46:49'),
(1135, 225, '1', 'Main Topic 1: Overview of Big Data and \nAnalytics\n\nAssignment #1\nLaboratory Activity #1', '1-2', 'Describe the basic concepts on \nbusiness intelligence, Big Data and \nbusiness analytics                        \nDiscuss the importance of business \nanalytics in decision making                        \nTrace the evolution of business \nanalytics                                     \nDiscuss the scope of business analytics                                                          \nDiscuss the importance of data in \nbusiness analytics', '1,2', '1', 'Face-to\nface/Online \nDiscussion,  \nVideos,\n Practical Work, \nModule', 1, '2025-12-01 03:46:49', '2025-12-01 03:46:49'),
(1140, 239, '2', 'sdfsd', '23', 'ddfsd', '23', '3', 'dsfsdfsdfsd', 0, '2025-12-01 18:38:13', '2025-12-01 18:38:13');

-- --------------------------------------------------------

--
-- Table structure for table `tla_ilo`
--

CREATE TABLE `tla_ilo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tla_id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_ilo_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tla_so`
--

CREATE TABLE `tla_so` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tla_id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_so_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `status` enum('pending','active','rejected') NOT NULL DEFAULT 'pending',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `employee_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `role`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `designation`, `employee_code`) VALUES
(221, 'PEREYRA MATTHEW ALEN', '22-72684@g.batstate-u.edu.ph', '111721505571170932945', 'faculty', 'active', NULL, NULL, NULL, '2025-12-03 08:04:55', '2025-12-04 03:00:35', 'Professor 1', '22-72684'),
(224, 'MONTEALEGRE PAUL JELAN', '22-70787@g.batstate-u.edu.ph', '108434727613386660229', 'faculty', 'active', NULL, NULL, NULL, '2025-12-03 08:06:28', '2025-12-04 03:00:34', 'Professor 1', '22-70787'),
(225, 'PABLICO ADRIANE ALLEN', '22-77551@g.batstate-u.edu.ph', '105027007800844806186', 'faculty', 'active', NULL, NULL, NULL, '2025-12-03 08:11:53', '2025-12-03 08:16:16', 'sdsf', '22-77551'),
(226, 'ASIBAR PAUL JUSTINE REY', '22-73610@g.batstate-u.edu.ph', '115681611370892614613', 'faculty', 'active', NULL, NULL, NULL, '2025-12-03 08:12:22', '2025-12-04 03:00:30', 'sad', '22-73610'),
(227, 'MATUNDAN JAYLORD', '22-77774@g.batstate-u.edu.ph', '102131477618847675288', 'faculty', 'active', NULL, NULL, NULL, '2025-12-03 08:12:47', '2025-12-04 03:00:34', 'dsfdsfdsf', '22-77774');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_assigned_by_foreign` (`assigned_by`),
  ADD KEY `appointments_user_id_role_status_index` (`user_id`,`role`,`status`),
  ADD KEY `appointments_scope_type_scope_id_role_status_index` (`scope_type`,`scope_id`,`role`,`status`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cdios`
--
ALTER TABLE `cdios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chair_requests`
--
ALTER TABLE `chair_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chair_requests_department_id_foreign` (`department_id`),
  ADD KEY `chair_requests_program_id_foreign` (`program_id`),
  ADD KEY `chair_requests_decided_by_foreign` (`decided_by`),
  ADD KEY `chair_requests_user_id_status_index` (`user_id`,`status`),
  ADD KEY `chair_requests_requested_role_department_id_program_id_index` (`requested_role`,`department_id`,`program_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courses_code_unique` (`code`),
  ADD KEY `courses_department_id_foreign` (`department_id`);

--
-- Indexes for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_prerequisite_course_id_foreign` (`course_id`),
  ADD KEY `course_prerequisite_prerequisite_id_foreign` (`prerequisite_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`);

--
-- Indexes for table `faculty_syllabus`
--
ALTER TABLE `faculty_syllabus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_syllabus_faculty_id_syllabus_id_unique` (`faculty_id`,`syllabus_id`),
  ADD KEY `faculty_syllabus_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `general_information`
--
ALTER TABLE `general_information`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_per_department` (`section`,`department_id`),
  ADD KEY `general_information_department_id_foreign` (`department_id`);

--
-- Indexes for table `igas`
--
ALTER TABLE `igas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ilo_course_id_code_unique` (`course_id`,`code`),
  ADD KEY `intended_learning_outcomes_course_id_foreign` (`course_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `programs_code_unique` (`code`),
  ADD KEY `programs_department_id_foreign` (`department_id`),
  ADD KEY `programs_created_by_foreign` (`created_by`);

--
-- Indexes for table `sdgs`
--
ALTER TABLE `sdgs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `so`
--
ALTER TABLE `so`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_outcomes`
--
ALTER TABLE `student_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_outcomes_department_id_foreign` (`department_id`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `super_admins_username_unique` (`username`);

--
-- Indexes for table `syllabi`
--
ALTER TABLE `syllabi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabi_course_id_foreign` (`course_id`),
  ADD KEY `syllabi_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `syllabi_faculty_id_foreign` (`faculty_id`);

--
-- Indexes for table `syllabus_assessment_mappings`
--
ALTER TABLE `syllabus_assessment_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_assessment_mappings_syllabus_id_position_index` (`syllabus_id`,`position`);

--
-- Indexes for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_assessment_tasks_syllabus_id_position_index` (`syllabus_id`,`position`),
  ADD KEY `sat_section_row_pos_idx` (`syllabus_id`,`section_number`,`row_type`,`position`);

--
-- Indexes for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_cdios_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_comments`
--
ALTER TABLE `syllabus_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_comments_syllabus_id_partial_key_batch_index` (`syllabus_id`,`partial_key`,`batch`),
  ADD KEY `syllabus_comments_created_by_index` (`created_by`),
  ADD KEY `syllabus_comments_updated_by_index` (`updated_by`);

--
-- Indexes for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_course_infos_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_course_policies`
--
ALTER TABLE `syllabus_course_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_course_policies_syllabus_id_foreign` (`syllabus_id`),
  ADD KEY `syllabus_course_policies_section_index` (`section`);

--
-- Indexes for table `syllabus_criteria`
--
ALTER TABLE `syllabus_criteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `syllabus_criteria_syllabus_id_key_unique` (`syllabus_id`,`key`);

--
-- Indexes for table `syllabus_igas`
--
ALTER TABLE `syllabus_igas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_igas_syllabus_id_foreign` (`syllabus_id`),
  ADD KEY `syllabus_igas_position_index` (`position`);

--
-- Indexes for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_ilos_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_ilo_cdio_sdg`
--
ALTER TABLE `syllabus_ilo_cdio_sdg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_ilo_cdio_sdg_syllabus_id_index` (`syllabus_id`);

--
-- Indexes for table `syllabus_ilo_iga`
--
ALTER TABLE `syllabus_ilo_iga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_ilo_iga_syllabus_id_index` (`syllabus_id`);

--
-- Indexes for table `syllabus_ilo_so_cpa`
--
ALTER TABLE `syllabus_ilo_so_cpa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_ilo_so_cpa_syllabus_id_index` (`syllabus_id`);

--
-- Indexes for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `syllabus_mission_visions_syllabus_id_unique` (`syllabus_id`);

--
-- Indexes for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `syllabus_sdgs_syllabus_id_code_unique` (`syllabus_id`,`code`),
  ADD KEY `syllabus_sdgs_sort_order_index` (`sort_order`);

--
-- Indexes for table `syllabus_sections`
--
ALTER TABLE `syllabus_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_sections_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_sos_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_submissions`
--
ALTER TABLE `syllabus_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_submissions_submitted_by_foreign` (`submitted_by`),
  ADD KEY `syllabus_submissions_action_by_foreign` (`action_by`),
  ADD KEY `syllabus_submissions_syllabus_id_action_at_index` (`syllabus_id`,`action_at`);

--
-- Indexes for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_textbooks_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `textbook_chunks`
--
ALTER TABLE `textbook_chunks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tla`
--
ALTER TABLE `tla`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tla_syllabus_id_index` (`syllabus_id`),
  ADD KEY `tla_position_index` (`position`);

--
-- Indexes for table `tla_ilo`
--
ALTER TABLE `tla_ilo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tla_ilo_tla_id_foreign` (`tla_id`),
  ADD KEY `tla_ilo_syllabus_ilo_id_foreign` (`syllabus_ilo_id`);

--
-- Indexes for table `tla_so`
--
ALTER TABLE `tla_so`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tla_so_tla_id_foreign` (`tla_id`),
  ADD KEY `tla_so_syllabus_so_id_foreign` (`syllabus_so_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=877;

--
-- AUTO_INCREMENT for table `cdios`
--
ALTER TABLE `cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `chair_requests`
--
ALTER TABLE `chair_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `faculty_syllabus`
--
ALTER TABLE `faculty_syllabus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_information`
--
ALTER TABLE `general_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `igas`
--
ALTER TABLE `igas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `so`
--
ALTER TABLE `so`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_outcomes`
--
ALTER TABLE `student_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabi`
--
ALTER TABLE `syllabi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `syllabus_assessment_mappings`
--
ALTER TABLE `syllabus_assessment_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1911;

--
-- AUTO_INCREMENT for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2212;

--
-- AUTO_INCREMENT for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2345;

--
-- AUTO_INCREMENT for table `syllabus_comments`
--
ALTER TABLE `syllabus_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `syllabus_course_policies`
--
ALTER TABLE `syllabus_course_policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=647;

--
-- AUTO_INCREMENT for table `syllabus_criteria`
--
ALTER TABLE `syllabus_criteria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3742;

--
-- AUTO_INCREMENT for table `syllabus_igas`
--
ALTER TABLE `syllabus_igas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=692;

--
-- AUTO_INCREMENT for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=771;

--
-- AUTO_INCREMENT for table `syllabus_ilo_cdio_sdg`
--
ALTER TABLE `syllabus_ilo_cdio_sdg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `syllabus_ilo_iga`
--
ALTER TABLE `syllabus_ilo_iga`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `syllabus_ilo_so_cpa`
--
ALTER TABLE `syllabus_ilo_so_cpa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=561;

--
-- AUTO_INCREMENT for table `syllabus_sections`
--
ALTER TABLE `syllabus_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3187;

--
-- AUTO_INCREMENT for table `syllabus_submissions`
--
ALTER TABLE `syllabus_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `textbook_chunks`
--
ALTER TABLE `textbook_chunks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5284;

--
-- AUTO_INCREMENT for table `tla`
--
ALTER TABLE `tla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1141;

--
-- AUTO_INCREMENT for table `tla_ilo`
--
ALTER TABLE `tla_ilo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tla_so`
--
ALTER TABLE `tla_so`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chair_requests`
--
ALTER TABLE `chair_requests`
  ADD CONSTRAINT `chair_requests_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chair_requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chair_requests_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chair_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  ADD CONSTRAINT `course_prerequisite_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_prerequisite_prerequisite_id_foreign` FOREIGN KEY (`prerequisite_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_syllabus`
--
ALTER TABLE `faculty_syllabus`
  ADD CONSTRAINT `faculty_syllabus_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_syllabus_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `general_information`
--
ALTER TABLE `general_information`
  ADD CONSTRAINT `general_information_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  ADD CONSTRAINT `intended_learning_outcomes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_outcomes`
--
ALTER TABLE `student_outcomes`
  ADD CONSTRAINT `student_outcomes_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabi`
--
ALTER TABLE `syllabi`
  ADD CONSTRAINT `syllabi_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabi_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabi_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `syllabus_assessment_mappings`
--
ALTER TABLE `syllabus_assessment_mappings`
  ADD CONSTRAINT `syllabus_assessment_mappings_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  ADD CONSTRAINT `syllabus_assessment_tasks_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  ADD CONSTRAINT `syllabus_cdios_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  ADD CONSTRAINT `syllabus_course_infos_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_course_policies`
--
ALTER TABLE `syllabus_course_policies`
  ADD CONSTRAINT `syllabus_course_policies_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_criteria`
--
ALTER TABLE `syllabus_criteria`
  ADD CONSTRAINT `syllabus_criteria_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_igas`
--
ALTER TABLE `syllabus_igas`
  ADD CONSTRAINT `syllabus_igas_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  ADD CONSTRAINT `syllabus_ilos_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_ilo_cdio_sdg`
--
ALTER TABLE `syllabus_ilo_cdio_sdg`
  ADD CONSTRAINT `syllabus_ilo_cdio_sdg_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_ilo_iga`
--
ALTER TABLE `syllabus_ilo_iga`
  ADD CONSTRAINT `syllabus_ilo_iga_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_ilo_so_cpa`
--
ALTER TABLE `syllabus_ilo_so_cpa`
  ADD CONSTRAINT `syllabus_ilo_so_cpa_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  ADD CONSTRAINT `syllabus_mission_visions_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  ADD CONSTRAINT `syllabus_sdgs_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sections`
--
ALTER TABLE `syllabus_sections`
  ADD CONSTRAINT `syllabus_sections_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  ADD CONSTRAINT `syllabus_sos_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_submissions`
--
ALTER TABLE `syllabus_submissions`
  ADD CONSTRAINT `syllabus_submissions_action_by_foreign` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `syllabus_submissions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `syllabus_submissions_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  ADD CONSTRAINT `syllabus_textbooks_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tla_ilo`
--
ALTER TABLE `tla_ilo`
  ADD CONSTRAINT `tla_ilo_syllabus_ilo_id_foreign` FOREIGN KEY (`syllabus_ilo_id`) REFERENCES `syllabus_ilos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tla_ilo_tla_id_foreign` FOREIGN KEY (`tla_id`) REFERENCES `tla` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tla_so`
--
ALTER TABLE `tla_so`
  ADD CONSTRAINT `tla_so_syllabus_so_id_foreign` FOREIGN KEY (`syllabus_so_id`) REFERENCES `syllabus_sos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tla_so_tla_id_foreign` FOREIGN KEY (`tla_id`) REFERENCES `tla` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
