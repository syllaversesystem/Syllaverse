-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 11:25 PM
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
  `role` enum('DEPT_CHAIR','PROG_CHAIR','FACULTY') NOT NULL,
  `scope_type` enum('Department','Program','Faculty') NOT NULL,
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
(53, 6, 'DEPT_CHAIR', 'Department', 48, 'ended', '2025-08-15 10:41:54', '2025-08-16 03:29:26', NULL, '2025-08-15 10:41:54', '2025-08-16 03:29:26'),
(59, 35, 'FACULTY', 'Faculty', 48, 'active', '2025-08-15 13:06:09', NULL, NULL, '2025-08-15 13:06:09', '2025-08-15 13:06:09'),
(60, 6, 'DEPT_CHAIR', 'Department', 48, 'active', '2025-08-16 03:29:51', NULL, NULL, '2025-08-16 03:29:51', '2025-08-16 03:29:51');

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
  `code` varchar(32) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cdios`
--

INSERT INTO `cdios` (`id`, `code`, `sort_order`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CDIO3', 3, 'Disciplinary Knowledge & Reasoning', 'Knowled geofunderlyingmathematicsandsciences, coreengineeringfundamentalknowledge, advanced\r\n engineering fundamental knowledge, methods and tools', '2025-07-22 04:46:37', '2025-08-11 20:39:45'),
(2, 'CDIO1', 1, 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problemsolving; experimentation , investigation and knowledge discovery;\r\n system thinking; attitudes, thoughts and learning; ethics, equity and other responsibilities', '2025-07-22 04:46:46', '2025-08-11 20:39:45'),
(3, 'CDIO4', 4, 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', '2025-07-22 04:46:58', '2025-08-11 20:39:45'),
(4, 'CDIO2', 2, 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context, enterprise and business context, conceiving, systems\r\n engineering and management, designing, implementing, operatin', '2025-07-22 04:47:20', '2025-08-11 20:39:45');

-- --------------------------------------------------------

--
-- Table structure for table `chair_requests`
--

CREATE TABLE `chair_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `requested_role` enum('DEPT_CHAIR','PROG_CHAIR') NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `decided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `contact_hours_lec` int(11) DEFAULT NULL,
  `contact_hours_lab` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `code`, `title`, `course_category`, `contact_hours_lec`, `contact_hours_lab`, `description`, `created_at`, `updated_at`) VALUES
(37, 48, 'IT123', 'Introduction to Computing', NULL, 3, 2, NULL, '2025-08-16 09:27:50', '2025-08-16 09:39:26'),
(44, 48, 'BAT 401', 'Fundamentals of Business Analytics', 'Professional Elective: Business Analytics Track', 2, 3, 'Thiscourseprovidesstudentswithanoverviewofthecurrent trendsininformationtechnologythatdrivestoday’s\r\n business.Thecoursewillprovideunderstandingondatamanagement techniques thatcanhelpanorganizationto\r\n achieveitsbusinessgoalsandaddressoperationalchallenges.Thiswillalsointroducedifferent toolsandmethods\r\n usedinbusinessanalytics toprovidethestudentswithopportunities toapplythesetechniques insimulations ina\r\n computer laboratory.', '2025-08-16 11:20:22', '2025-08-31 10:01:35'),
(49, 48, 'BSIT', 'fdfd', 'Bachelor of Science in Information Technology', 3, 2, '221231', '2025-08-31 10:02:38', '2025-08-31 10:02:38'),
(51, 48, 'BSITsss', 'fdfd', 'Bachelor of Science in Information Technology', 3, 2, 'fdfd', '2025-08-31 10:08:19', '2025-08-31 10:08:19'),
(52, 48, 'BSITsssf', 'fdfd', 'Bachelor of Science in Information Technology', 3, 0, '21321', '2025-08-31 10:41:02', '2025-08-31 10:41:02'),
(53, 52, 'TEST101', 'Test Course', NULL, NULL, NULL, NULL, '2025-09-01 14:06:24', '2025-09-01 14:06:24');

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
(49, 44, 37, NULL, NULL),
(50, 49, 44, NULL, NULL),
(51, 49, 37, NULL, NULL),
(52, 51, 44, NULL, NULL),
(53, 51, 49, NULL, NULL),
(54, 51, 37, NULL, NULL),
(55, 52, 44, NULL, NULL),
(56, 52, 49, NULL, NULL),
(57, 52, 51, NULL, NULL),
(58, 52, 37, NULL, NULL);

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
(48, 'College in Computing Science', 'CICS', '2025-08-10 21:09:21', '2025-08-10 21:09:21'),
(51, 'd', 'dd', '2025-08-16 06:31:42', '2025-08-16 06:31:42'),
(52, 'Test Dept', 'DEPT1', '2025-09-01 14:06:24', '2025-09-01 14:06:24');

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
  `section` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_information`
--

INSERT INTO `general_information` (`id`, `section`, `content`, `created_at`, `updated_at`) VALUES
(1, 'mission', 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', '2025-07-20 14:10:56', '2025-08-14 08:57:56'),
(2, 'vision', 'A premier national university that develops leaders in the global knowledge economy', '2025-07-20 14:11:31', '2025-07-20 14:11:31'),
(3, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', '2025-07-20 14:11:50', '2025-07-20 14:11:50'),
(4, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', '2025-07-20 14:12:07', '2025-07-20 14:12:07'),
(5, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', '2025-07-20 14:12:18', '2025-07-20 14:12:18'),
(6, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', '2025-07-20 14:12:30', '2025-07-20 14:12:30'),
(7, 'disability', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to\r\ndisclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary\r\nadjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced\r\nattitudes towads students with disability.', '2025-07-20 14:12:44', '2025-07-20 14:12:44'),
(8, 'advising', 'CONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or\r\nface-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term.\r\nDiscussion for academic purposes will also be entertained.', '2025-07-20 14:12:54', '2025-07-20 14:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `igas`
--

CREATE TABLE `igas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `igas`
--

INSERT INTO `igas` (`id`, `code`, `sort_order`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'IGA1', 1, 'Knowledge Competence', 'Demonstrateamasteryof thefundamentalknowledgeandskillsrequiredfor functioningeffectivelyasa\r\n professional in thediscipline, andanability to integrate andapply themeffectively topractice in the\r\n workplace.', '2025-07-22 04:40:36', '2025-08-11 12:37:12'),
(2, 'IGA2', 2, 'Creativity and Innovation', 'Experimentwithnewapproaches,challengeexistingknowledgeboundariesanddesignnovelsolutionsto\r\n solve problems.', '2025-07-22 04:40:46', '2025-08-11 12:37:12'),
(3, 'IGA3', 3, 'Critical and Systems', 'dentify,define,anddealwithcomplexproblemspertinent tothefutureprofessionalpracticeordailylife\r\n through logical, analytical and critical thinking.', '2025-07-22 04:45:05', '2025-08-11 12:37:12'),
(4, 'IGA4', 4, 'Communication', 'Communicateeffectively(bothorallyandinwriting)withawiderangeofaudiences, acrossarangeof\r\n professional and personal contexts, in English and Pilipino.', '2025-07-22 04:45:13', '2025-08-11 12:37:12'),
(5, 'IGA5', 5, 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate an eagerness to take up \r\nopportunities for learning new things as well as the ability to learn effectively on their own.', '2025-07-22 04:45:49', '2025-08-11 12:37:12'),
(6, 'IGA6', 6, 'Leadership, teamwork, and Interpersonal Skills', 'Functioneffectivelybothas a leader andas amember of a team;motivateand leada teamtowork\r\n towardsgoal;workcollaborativelywithother teammembers;aswellasconnectandinteract sociallyand\r\n effectively with diverse culture.', '2025-07-22 04:46:00', '2025-08-11 12:37:12'),
(7, 'IGA7', 7, 'Global Outlook', 'Demonstrateanawarenessandunderstandingofglobalissuesandwillingnesstowork, interacteffectively\r\n and show sensitivity to cultural diversity.', '2025-07-22 04:46:12', '2025-08-11 12:37:12'),
(8, 'IGA8', 8, 'Social and National Responsibility', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', '2025-07-22 04:46:21', '2025-08-11 12:37:12'),
(9, 'IGA9', 9, 'Social and National Responsibility', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', '2025-07-22 04:46:21', '2025-08-11 12:37:12'),
(10, 'IGA10', 10, 'dd', 'dd', '2025-08-11 10:30:35', '2025-08-11 12:37:12');

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

--
-- Dumping data for table `intended_learning_outcomes`
--

INSERT INTO `intended_learning_outcomes` (`id`, `code`, `description`, `position`, `created_at`, `updated_at`, `course_id`) VALUES
(62, 'ILO2', 'Explain data management concepts and criticality of data availability in order to make reliable business \r\ndecisions.', 2, '2025-08-16 11:50:24', '2025-08-29 19:49:05', 44),
(63, 'ILO1', 'Demonstrate understanding of  business intelligence including the importance of data gathering, data \r\nstoring, data analyzing and accessing data.', 1, '2025-08-16 11:50:28', '2025-08-29 19:49:05', 44),
(64, 'ILO3', 'Describe where to look for data in an organization and create required reports', 3, '2025-08-16 11:50:33', '2025-08-29 19:49:05', 44),
(65, 'ILO4', 'Perform high-quality tasks required by the organization in particular, and the industry in general', 4, '2025-08-16 11:50:37', '2025-08-29 19:49:05', 44),
(85, 'ILO5', 'dddfsfs', 5, '2025-08-29 19:48:58', '2025-08-29 19:49:05', 44);

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
(100, '2025_09_05_010000_create_syllabus_sdgs_table', 48);

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `department_id`, `created_by`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(14, 48, 6, 'Bachelor of Science in Information Technology', 'BSIT', NULL, '2025-08-16 04:37:07', '2025-08-16 09:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `sdgs`
--

CREATE TABLE `sdgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sdgs`
--

INSERT INTO `sdgs` (`id`, `code`, `sort_order`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'SDG1', 1, 'Envisioning', 'Est ablishalinkbetweenlong-termgoalsandandimmediateactions,andmotivatepeopletotakeactionby\r\n harnessing their deep aspirations.', '2025-07-22 04:37:43', '2025-08-15 09:50:47'),
(2, 'SDG2', 2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social and cultural structures in the context of sustainable\r\n development, andchallengespeople toexamineandquestiontheunderlyingassumptions that influence\r\n their world views by having them reflect on unsustainable practices.', '2025-07-22 04:37:59', '2025-08-15 09:50:47'),
(3, 'SDG3', 3, 'Systematic thinking', 'Recognise that the whole is more than the sum of its parts, and it is a better way to understand and manage \r\ncomplex situations.', '2025-07-22 04:38:50', '2025-08-15 09:50:47'),
(4, 'SDG4', 4, 'Building Partnership', 'Promote dialogue andnegotiation, learning towork together, so as to strengthenownership of and\r\n commitment to sustainable action through education and learning.', '2025-07-22 04:39:05', '2025-08-15 09:50:47'),
(5, 'SDG5', 5, 'Participation in Making Decisions', 'Empower oneself and others through involvement in joint analysis, planning and control of local decisions.', '2025-07-22 04:39:26', '2025-08-15 09:50:47');

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
('psZghEwPFqCcmbgPlENCvRCoRWCpmrIsJAJBegJz', 35, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoicERZNDV1SzZvcHJnb0ZmM1NSV1hDYUhFdzBYNFZGd0V1TnNNd0ZhcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9mYWN1bHR5L3N5bGxhYmkvMTE3Ijt9czo1OiJzdGF0ZSI7czo0MDoiOE11Zkx0a0pib1d3QzZ5a05sWXBhTFF3R215NzJPU2NoRmVqUVFOTiI7czo1NDoibG9naW5fZmFjdWx0eV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM1O3M6MTM6ImlzX3N1cGVyYWRtaW4iO2I6MTtzOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7fQ==', 1757020911),
('rAGhIppuNHtEsak4tsOiSeHZIeS0DXVAGjYXbJWM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoicEx5bkFkd3poSnAxWFkyVFo3VllIUmJBdnk4cWV4aFc2VXJaSnNBdCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czo1OiJzdGF0ZSI7czo0MDoiN0RSZUU5V2UzVXlhNllDdUk2NjA3eENraDVOcjdicnY2WE9oRjR5cyI7czo1NDoibG9naW5fZmFjdWx0eV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM1O30=', 1757013762);

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
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_outcomes`
--

INSERT INTO `student_outcomes` (`id`, `code`, `description`, `position`, `created_at`, `updated_at`) VALUES
(23, 'SO2', 'Abilitytoanalyzeacomplexcomputingproblemandtoapplyprinciplesofcomputingandotherrelevant\r\n disciplines to identify solutions', 2, '2025-07-28 11:50:52', '2025-08-29 18:49:37'),
(24, 'SO1', 'Abilitytodesign, implement, andevaluateacomputing-basedsolutiontomeet agivensetofcomputing\r\n requirements in the context of the program’s discipline.', 1, '2025-07-28 11:50:58', '2025-08-29 18:49:37'),
(25, 'SO3', 'Ability to communicate effectively in a variety of professional contexts.', 3, '2025-07-28 11:51:03', '2025-08-29 18:49:37'),
(26, 'SO4', 'Ability to recognize professional responsibilities andmake informed judgments incomputingpractice\r\n based on legal and ethical principles.', 4, '2025-07-28 11:51:07', '2025-08-29 18:49:37'),
(27, 'SO5', 'Abilityto functioneffectivelyasamemberor leaderofa teamengagedinactivitiesappropriate to the\r\n program’s discipline.', 5, '2025-07-28 11:51:11', '2025-08-29 18:49:37'),
(36, 'SO7', 'Ability to identifyand analyze user needs and to take theminto account in the selection, creation,\r\n integration, evaluation and administration of computing-based systems.', 7, '2025-08-16 11:08:40', '2025-08-29 18:49:37');

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
  `faculty_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `year_level` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `textbook_file_path` varchar(255) DEFAULT NULL,
  `assessment_tasks_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabi`
--

INSERT INTO `syllabi` (`id`, `faculty_id`, `program_id`, `course_id`, `title`, `academic_year`, `semester`, `year_level`, `created_at`, `updated_at`, `textbook_file_path`, `assessment_tasks_data`) VALUES
(87, 41, NULL, 53, 'Test Syllabus 1756764384', '2025', '1st', '1', '2025-09-01 14:06:24', '2025-09-01 14:06:24', NULL, NULL),
(88, 42, NULL, 53, 'Test Syllabus 1756765180', '2025', '1st', '1', '2025-09-01 14:19:40', '2025-09-01 14:19:40', NULL, NULL),
(117, 35, 14, 37, 'Introduction to Computng', '2025-2026', '1st Semester', '1st Year', '2025-09-04 12:00:33', '2025-09-04 12:00:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_assessment_tasks`
--

CREATE TABLE `syllabus_assessment_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
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
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_cdios`
--

INSERT INTO `syllabus_cdios` (`id`, `syllabus_id`, `code`, `description`, `position`, `created_at`, `updated_at`) VALUES
(794, 117, 'CDIO1', 'Analytical reasoning and problemsolving; experimentation , investigation and knowledge discovery;\r\n system thinking; attitudes, thoughts and learning; ethics, equity and other responsibilities', 1, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(795, 117, 'CDIO2', 'External, societal and environmental context, enterprise and business context, conceiving, systems\r\n engineering and management, designing, implementing, operatin', 2, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(796, 117, 'CDIO3', 'Knowled geofunderlyingmathematicsandsciences, coreengineeringfundamentalknowledge, advanced\r\n engineering fundamental knowledge, methods and tools', 3, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(797, 117, 'CDIO4', 'Teamwork, communications, communication in a foreign language', 4, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(798, 117, 'CDIO1', 'Analytical reasoning and problemsolving; experimentation , investigation and knowledge discovery;\r\n system thinking; attitudes, thoughts and learning; ethics, equity and other responsibilities', 1, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(799, 117, 'CDIO2', 'External, societal and environmental context, enterprise and business context, conceiving, systems\r\n engineering and management, designing, implementing, operatin', 2, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(800, 117, 'CDIO3', 'Knowled geofunderlyingmathematicsandsciences, coreengineeringfundamentalknowledge, advanced\r\n engineering fundamental knowledge, methods and tools', 3, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(801, 117, 'CDIO4', 'Teamwork, communications, communication in a foreign language', 4, '2025-09-04 12:00:33', '2025-09-04 12:00:33');

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
(43, 117, 'Introduction to Computing', 'IT123', NULL, '', '1st Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'PABLICO ADRIANE ALLEN', '22-5534', NULL, 'Assistant Professor IV, BSIT, MSIT', 'September 04, 2025', '22-77551@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-09-04 12:00:33', '2025-09-04 12:00:33');

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
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_igas`
--

INSERT INTO `syllabus_igas` (`id`, `syllabus_id`, `code`, `description`, `position`, `created_at`, `updated_at`) VALUES
(274, 117, 'IGA1', 'Demonstrateamasteryof thefundamentalknowledgeandskillsrequiredfor functioningeffectivelyasa\r\n professional in thediscipline, andanability to integrate andapply themeffectively topractice in the\r\n workplace.', 1, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(275, 117, 'IGA2', 'Experimentwithnewapproaches,challengeexistingknowledgeboundariesanddesignnovelsolutionsto\r\n solve problems.', 2, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(276, 117, 'IGA3', 'dentify,define,anddealwithcomplexproblemspertinent tothefutureprofessionalpracticeordailylife\r\n through logical, analytical and critical thinking.', 3, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(277, 117, 'IGA4', 'Communicateeffectively(bothorallyandinwriting)withawiderangeofaudiences, acrossarangeof\r\n professional and personal contexts, in English and Pilipino.', 4, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(278, 117, 'IGA5', 'Identify own learning needs for professional or personal development; demonstrate an eagerness to take up \r\nopportunities for learning new things as well as the ability to learn effectively on their own.', 5, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(279, 117, 'IGA6', 'Functioneffectivelybothas a leader andas amember of a team;motivateand leada teamtowork\r\n towardsgoal;workcollaborativelywithother teammembers;aswellasconnectandinteract sociallyand\r\n effectively with diverse culture.', 6, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(280, 117, 'IGA7', 'Demonstrateanawarenessandunderstandingofglobalissuesandwillingnesstowork, interacteffectively\r\n and show sensitivity to cultural diversity.', 7, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(281, 117, 'IGA8', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', 8, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(282, 117, 'IGA9', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', 9, '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(283, 117, 'IGA10', 'dd', 10, '2025-09-04 12:00:33', '2025-09-04 12:00:33');

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
(14, 87, 'M test', 'V test', '2025-09-01 14:06:24', '2025-09-01 14:06:24'),
(15, 88, 'M test', 'V test', '2025-09-01 14:19:40', '2025-09-01 14:19:40'),
(44, 117, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-09-04 12:00:33', '2025-09-04 12:00:33');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_sdg`
--

CREATE TABLE `syllabus_sdg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `sdg_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `position` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_sdg`
--

INSERT INTO `syllabus_sdg` (`id`, `syllabus_id`, `sdg_id`, `title`, `description`, `position`, `code`, `created_at`, `updated_at`) VALUES
(95, 117, 4, 'Building Partnership', 'Promote dialogue andnegotiation, learning towork together, so as to strengthenownership of and\r\n commitment to sustainable action through education and learning.', NULL, NULL, '2025-09-04 12:49:27', '2025-09-04 12:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_sdgs`
--

CREATE TABLE `syllabus_sdgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `syllabus_id` bigint(20) UNSIGNED NOT NULL,
  `sdg_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
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
  `position` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_sos`
--

INSERT INTO `syllabus_sos` (`id`, `syllabus_id`, `code`, `position`, `description`, `created_at`, `updated_at`) VALUES
(1596, 117, 'SO1', 1, 'Abilitytodesign, implement, andevaluateacomputing-basedsolutiontomeet agivensetofcomputing\r\n requirements in the context of the program’s discipline.', '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(1597, 117, 'SO2', 2, 'Abilitytoanalyzeacomplexcomputingproblemandtoapplyprinciplesofcomputingandotherrelevant\r\n disciplines to identify solutions', '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(1598, 117, 'SO3', 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(1599, 117, 'SO4', 4, 'Ability to recognize professional responsibilities andmake informed judgments incomputingpractice\r\n based on legal and ethical principles.', '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(1600, 117, 'SO5', 5, 'Abilityto functioneffectivelyasamemberor leaderofa teamengagedinactivitiesappropriate to the\r\n program’s discipline.', '2025-09-04 12:00:33', '2025-09-04 12:00:33'),
(1601, 117, 'SO7', 6, 'Ability to identifyand analyze user needs and to take theminto account in the selection, creation,\r\n integration, evaluation and administration of computing-based systems.', '2025-09-04 12:00:33', '2025-09-04 12:00:33');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(6, 'MONTEALEGRE PAUL JELAN', '22-70787@g.batstate-u.edu.ph', '108434727613386660229', 'admin', 'active', NULL, NULL, NULL, '2025-07-25 18:50:55', '2025-08-11 08:08:37', 'Assistant Professor IV, BSIT, MSIT', '22-5534'),
(35, 'PABLICO ADRIANE ALLEN', '22-77551@g.batstate-u.edu.ph', '105027007800844806186', 'faculty', 'active', NULL, NULL, NULL, '2025-08-15 13:06:03', '2025-08-29 18:51:16', 'Assistant Professor IV, BSIT, MSIT', '22-5534'),
(38, 'Jessica Muller', 'larson.leanna@example.net', NULL, 'admin', 'pending', '2025-09-01 14:05:39', '$2y$12$fFIajB8f4LZjCbc9mESXkeQdrCzMv.6hwgCxnH54i7JlzPBObsalq', 'UaTRTBLFOx', '2025-09-01 14:05:39', '2025-09-01 14:05:39', NULL, NULL),
(39, 'Test User', 'test+1756764351@example.com', NULL, 'admin', 'pending', NULL, '$2y$12$fv0auMn5I0cYhsQqTn23feoEKNXDPBkSHWBAEmADaLejA/tfEq/dC', NULL, '2025-09-01 14:05:51', '2025-09-01 14:05:51', NULL, NULL),
(40, 'Test User', 'test+1756764366@example.com', NULL, 'admin', 'pending', NULL, '$2y$12$nUzRVXtVh9qW0ivFiVuvLOUTGJUA/v3MlThhnDQaBia9vz.vsO49e', NULL, '2025-09-01 14:06:07', '2025-09-01 14:06:07', NULL, NULL),
(41, 'Test User', 'test+1756764383@example.com', NULL, 'admin', 'pending', NULL, '$2y$12$lUbwx6Nq5JzO4tRyjDyr.uxJc4yekI49LplfstQ89SUr3c/ztKHfG', NULL, '2025-09-01 14:06:24', '2025-09-01 14:06:24', NULL, NULL),
(42, 'Test User', 'test+1756765180@example.com', NULL, 'admin', 'pending', NULL, '$2y$12$KWMSvMVUIpqBd913ZzDfNODqJmobLxWtqjD7hS/Bt4kKqTQyScunS', NULL, '2025-09-01 14:19:40', '2025-09-01 14:19:40', NULL, NULL);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cdios_code_unique` (`code`),
  ADD KEY `cdios_sort_order_index` (`sort_order`);

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
  ADD UNIQUE KEY `general_information_section_unique` (`section`);

--
-- Indexes for table `igas`
--
ALTER TABLE `igas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `igas_code_unique` (`code`),
  ADD KEY `igas_sort_order_index` (`sort_order`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sdgs_code_unique` (`code`),
  ADD KEY `sdgs_sort_order_index` (`sort_order`);

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
  ADD UNIQUE KEY `student_outcomes_code_unique` (`code`);

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
  ADD KEY `syllabi_faculty_id_foreign` (`faculty_id`),
  ADD KEY `syllabi_course_id_foreign` (`course_id`);

--
-- Indexes for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_assessment_tasks_syllabus_id_position_index` (`syllabus_id`,`position`);

--
-- Indexes for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_cdios_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_course_infos_syllabus_id_foreign` (`syllabus_id`);

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
-- Indexes for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `syllabus_mission_visions_syllabus_id_unique` (`syllabus_id`);

--
-- Indexes for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_sdg_syllabus_id_foreign` (`syllabus_id`),
  ADD KEY `syllabus_sdg_sdg_id_foreign` (`sdg_id`);

--
-- Indexes for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_sdgs_syllabus_id_foreign` (`syllabus_id`),
  ADD KEY `syllabus_sdgs_sdg_id_foreign` (`sdg_id`);

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
-- Indexes for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_textbooks_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `tla`
--
ALTER TABLE `tla`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tla_syllabus_id_foreign` (`syllabus_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `cdios`
--
ALTER TABLE `cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chair_requests`
--
ALTER TABLE `chair_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_information`
--
ALTER TABLE `general_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `igas`
--
ALTER TABLE `igas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `so`
--
ALTER TABLE `so`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_outcomes`
--
ALTER TABLE `student_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabi`
--
ALTER TABLE `syllabi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=634;

--
-- AUTO_INCREMENT for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=802;

--
-- AUTO_INCREMENT for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `syllabus_criteria`
--
ALTER TABLE `syllabus_criteria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=985;

--
-- AUTO_INCREMENT for table `syllabus_igas`
--
ALTER TABLE `syllabus_igas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;

--
-- AUTO_INCREMENT for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabus_sections`
--
ALTER TABLE `syllabus_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1602;

--
-- AUTO_INCREMENT for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `tla`
--
ALTER TABLE `tla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT for table `tla_ilo`
--
ALTER TABLE `tla_ilo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tla_so`
--
ALTER TABLE `tla_so`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
-- Constraints for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  ADD CONSTRAINT `intended_learning_outcomes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabi`
--
ALTER TABLE `syllabi`
  ADD CONSTRAINT `syllabi_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabi_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `syllabus_mission_visions`
--
ALTER TABLE `syllabus_mission_visions`
  ADD CONSTRAINT `syllabus_mission_visions_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  ADD CONSTRAINT `syllabus_sdg_sdg_id_foreign` FOREIGN KEY (`sdg_id`) REFERENCES `sdgs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabus_sdg_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  ADD CONSTRAINT `syllabus_sdgs_sdg_id_foreign` FOREIGN KEY (`sdg_id`) REFERENCES `sdgs` (`id`) ON DELETE CASCADE,
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
-- Constraints for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  ADD CONSTRAINT `syllabus_textbooks_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tla`
--
ALTER TABLE `tla`
  ADD CONSTRAINT `tla_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

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
