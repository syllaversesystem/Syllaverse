-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 10:08 PM
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
(890, 231, 'FACULTY', 'Department', 80, 'ended', '2025-12-04 17:43:11', '2025-12-04 17:56:08', NULL, '2025-12-04 17:43:11', '2025-12-04 17:56:08'),
(897, 231, 'FACULTY', 'Department', 80, 'ended', '2025-12-04 17:56:40', '2025-12-04 18:32:46', NULL, '2025-12-04 17:56:40', '2025-12-04 18:32:46'),
(902, 233, 'FACULTY', 'Department', 80, 'ended', '2025-12-04 18:08:40', '2025-12-04 18:32:47', NULL, '2025-12-04 18:08:40', '2025-12-04 18:32:47'),
(910, 233, 'FACULTY', 'Department', 100, 'ended', '2025-12-04 18:33:30', '2025-12-04 18:33:34', NULL, '2025-12-04 18:33:30', '2025-12-04 18:33:34'),
(912, 234, 'FACULTY', 'Department', 80, 'active', '2025-12-04 18:35:42', NULL, NULL, '2025-12-04 18:35:42', '2025-12-04 18:35:42'),
(916, 233, 'DEPT_HEAD', 'Department', 80, 'active', '2025-12-04 18:36:58', NULL, NULL, '2025-12-04 18:36:58', '2025-12-04 18:36:58'),
(917, 231, 'CHAIR', 'Department', 80, 'active', '2025-12-04 18:37:41', NULL, NULL, '2025-12-04 18:37:41', '2025-12-04 18:37:41'),
(918, 235, 'FACULTY', 'Department', 80, 'active', '2025-12-04 19:46:14', NULL, NULL, '2025-12-04 19:46:14', '2025-12-04 19:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('syllaverse_cache_superadmin_dashboard_accounts_by_dept', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;a:2:{s:10:\"department\";s:4:\"CICS\";s:5:\"total\";i:4;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1765215777),
('syllaverse_cache_superadmin_dashboard_leadership', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;a:4:{s:4:\"name\";s:22:\"MONTEALEGRE PAUL JELAN\";s:4:\"role\";s:5:\"Chair\";s:10:\"department\";s:4:\"CICS\";s:13:\"department_id\";i:80;}i:1;a:4:{s:4:\"name\";s:23:\"ASIBAR PAUL JUSTINE REY\";s:4:\"role\";s:9:\"Dept Head\";s:10:\"department\";s:4:\"CICS\";s:13:\"department_id\";i:80;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1765215777),
('syllaverse_cache_superadmin_dashboard_stats', 'a:5:{s:11:\"departments\";i:3;s:8:\"programs\";i:1;s:7:\"courses\";i:6;s:7:\"faculty\";i:4;s:16:\"pending_accounts\";i:0;}', 1765215776),
('syllaverse_cache_superadmin_dashboard_syllabus_status_by_dept_v4', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;a:9:{s:10:\"department\";s:4:\"CICS\";s:5:\"draft\";i:3;s:7:\"pending\";i:3;s:8:\"reviewed\";i:1;s:14:\"final_approved\";i:0;s:5:\"total\";i:7;s:12:\"reviewed_pct\";i:14;s:6:\"status\";i:0;s:12:\"status_label\";s:6:\"Behind\";}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1765215777);

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

--
-- Dumping data for table `cdios`
--

INSERT INTO `cdios` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(11, 'Disciplinary Knowledge & Reasoning', 'Knowledge of underlying mathematics and sciences, core engineering fundamental knowledge, advanced engineering fundamental knowledge, methods, and tools.', '2025-12-04 19:24:23', '2025-12-04 19:24:23'),
(13, 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problem solving; experimentation, investigation, and knowledge discovery; system thinking; attitudes, thoughts, and learning; ethics, equity, and other responsibilitie', '2025-12-04 19:24:35', '2025-12-04 19:24:35'),
(15, 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', '2025-12-04 19:24:55', '2025-12-04 19:24:55'),
(17, 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context; enterprise and business context; conceiving; systems engineering and management; designing; implementing; operating.', '2025-12-04 19:25:05', '2025-12-04 19:25:05');

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
(247, 233, 'CHAIR', 85, NULL, 'approved', NULL, '2025-12-04 18:32:47', NULL, '2025-12-04 18:30:35', '2025-12-04 18:32:47'),
(248, 231, 'ASSOC_DEAN', 85, NULL, 'approved', NULL, '2025-12-04 18:32:46', NULL, '2025-12-04 18:31:01', '2025-12-04 18:32:46'),
(249, 234, 'FACULTY', 80, NULL, 'approved', NULL, '2025-12-04 18:35:42', NULL, '2025-12-04 18:35:33', '2025-12-04 18:35:42'),
(250, 235, 'FACULTY', 80, NULL, 'approved', NULL, '2025-12-04 19:46:14', NULL, '2025-12-04 19:05:12', '2025-12-04 19:46:14');

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
(101, 'College of Arts and Sciences', 'CAS', '2025-12-04 18:34:23', '2025-12-04 18:34:23');

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
(131, 231, 273, 'owner', 1, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(136, 234, 278, 'owner', 1, '2025-12-04 19:31:26', '2025-12-04 19:31:26'),
(138, 234, 280, 'owner', 1, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(140, 234, 282, 'owner', 1, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(141, 235, 283, 'owner', 1, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(143, 234, 285, 'owner', 1, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(144, 235, 286, 'owner', 1, '2025-12-06 14:00:37', '2025-12-06 14:00:37');

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

--
-- Dumping data for table `igas`
--

INSERT INTO `igas` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(38, 'Knowledge Competence', 'Demonstrate a mastery of the fundamental knowledge and skills required for functioning effectively as a professional in the discipline, and an ability to integrate and apply them effectively to practice in the workplace.', '2025-12-04 19:20:28', '2025-12-04 19:20:28'),
(39, 'Creativity and Innovation', 'Experiment with new approaches, challenge existing knowledge boundaries, and design novel solutions to solve problems.', '2025-12-04 19:20:44', '2025-12-04 19:20:44'),
(40, 'Critical and Systems Thinking', 'identify, define, and deal with complex problems pertinent to future professional practice or daily life through logical, analytical, and critical thinking.', '2025-12-04 19:20:53', '2025-12-04 19:20:53'),
(41, 'Communication', 'Communicate effectively (both orally and in writing) with a wide range of audiences, across a range of professional and personal contexts, in English and Filipino.', '2025-12-04 19:21:05', '2025-12-04 19:21:05'),
(42, 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate eagerness to take up opportunities for learning new things as well as the ability to learn effectively on their own.', '2025-12-04 19:21:18', '2025-12-04 19:21:18'),
(43, 'Leadership, Teamwork, and Interpersonal Skills', 'Function effectively both as a leader and as a member of a team; motivate and lead a team to work toward goals; work collaboratively with other team members; and connect and interact socially and effectively with diverse culture.', '2025-12-04 19:21:30', '2025-12-04 19:21:30'),
(44, 'Global Outlook', 'Demonstrate an awareness and understanding of global issues and willingness to work, interact effectively, and show sensitivity to cultural diversity.', '2025-12-04 19:21:47', '2025-12-04 19:21:47');

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
(105, 'ILO1', 'Explain fundamental concepts of computing, including processes, procedures, and information representation.', 1, '2025-12-04 19:17:56', '2025-12-04 19:17:56', 88),
(106, 'ILO2', 'Illustrate how data is represented and manipulated within a computer system.', 2, '2025-12-04 19:18:08', '2025-12-04 19:18:08', 88),
(107, 'ILO3', 'Differentiate between programming languages and describe their key features and uses.', 3, '2025-12-04 19:18:17', '2025-12-04 19:18:17', 88),
(108, 'ILO4', 'Apply basic problem-solving strategies to design and analyze simple algorithms and procedures.', 4, '2025-12-04 19:18:23', '2025-12-04 19:18:23', 88),
(109, 'ILO5', 'Discuss the impact of computing on society, ethics, and interdisciplinary fields.', 5, '2025-12-04 19:18:29', '2025-12-04 19:18:29', 88),
(110, 'ILO1', 'Explain fundamental concepts of computing, including processes, procedures, and information representation.', 1, '2025-12-04 19:36:07', '2025-12-04 19:36:07', 86),
(111, 'ILO2', 'Illustrate how data is represented and manipulated within a computer system.', 2, '2025-12-04 19:36:12', '2025-12-04 19:36:12', 86),
(112, 'ILO3', 'Differentiate between programming languages and describe their key features and uses.', 3, '2025-12-04 19:36:19', '2025-12-04 19:36:19', 86),
(113, 'ILO4', 'Apply basic problem-solving strategies to design and analyze simple algorithms and procedures.', 4, '2025-12-04 19:36:24', '2025-12-04 19:36:24', 86),
(114, 'ILO5', 'Discuss the impact of computing on society, ethics, and interdisciplinary fields.', 5, '2025-12-04 19:36:30', '2025-12-04 19:36:30', 86);

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
(173, '2025_12_02_120100_extend_submission_history_enums', 99),
(174, '2025_12_06_000000_create_superadmins_table', 100),
(175, '2025_12_06_010000_add_email_to_superadmins', 101),
(176, '2025_12_07_000100_add_email_to_super_admins', 102);

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
(103, 80, NULL, 'Bachelor of Science in Information Technology', 'BSIT', NULL, 'active', '2025-11-03 08:46:34', '2025-12-04 11:10:09');

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
(21, 'Envisioning', 'Establish a link between long-term goals and immediate actions, and motivate people to take action by harnessing their deep aspiratio', '2025-12-04 19:26:40', '2025-12-04 19:26:40'),
(22, 'Critical Thinking and Reflection', 'Examine economic, environmental, social, and cultural structures in the context of sustainable development, and challenge people to examine and question the underlying assumptions that influence their world views by having them reflect on unsustainable practices.', '2025-12-04 19:26:51', '2025-12-04 19:26:51'),
(23, 'Systemic Thinking', 'Recognize that the whole is more than the sum of its parts, and it is a better way to understand and manage complex situations.', '2025-12-04 19:27:05', '2025-12-04 19:27:05'),
(24, 'Building Partnerships', 'Promote dialogue and negotiation, learning to work together, so as to strengthen ownership of and commitment to sustainable action through education and learning.', '2025-12-04 19:27:25', '2025-12-04 19:27:25');

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
('6iogZOZMyY5hjMRVPec2CNCnzz1Tb2LCQ0ymCqo0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidDZKd3dvMjRnZ0J2eUFJRE1IY0NDYlVDZTlmZTdNTHJCZWtQcWZmUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9mYWN1bHR5L3N5bGxhYmkvMjg2L2Fzc2Vzc21lbnQtbWFwcGluZ3MiO31zOjU6InN0YXRlIjtzOjQwOiJmWWZkQUJlSEhUc0ZZeEdweEN6dFJMa2tDbnFMTWsxODYyY2pYOXlPIjtzOjU0OiJsb2dpbl9mYWN1bHR5XzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjM1O30=', 1765228005);

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
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `username`, `email`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin', '22-77551@g.batstate-u.edu.ph', '2025-12-06 12:33:20', '$2y$12$fD4JKgsLiO9Jn7yyohC93.S5g5Ph0aeh0v5F/sTCuDCfZkC.HHCbi', '2025-12-06 03:12:05', '2025-12-08 09:42:03');

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
(273, 231, 103, 86, 'sdfdsf', '2025-2026', '1st Semester', '1st Year', 'draft', NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:01:46', NULL, '{\"sections\":[{\"section_num\":1,\"section_label\":null,\"main_row\":{\"code\":\"\",\"task\":\"\",\"percent\":null},\"main_ilo_columns\":[\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\"],\"cpa_columns\":[null,null,null]}]},{\"section_num\":2,\"section_label\":null,\"main_row\":{\"code\":\"\",\"task\":\"\",\"percent\":null},\"main_ilo_columns\":[\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\"],\"cpa_columns\":[null,null,null]}]}]}', NULL, '[]', NULL, NULL, NULL, 'MONTEALEGRE PAUL JELAN', 'Professor 1', '2025-12-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(278, 234, 103, 86, 'BA CIS', '2025-2026', '1st Semester', '3rd Year', 'pending_review', NULL, '2025-12-04 19:45:18', '2025-12-04 19:31:26', '2025-12-04 19:45:18', NULL, '{\"sections\":[{\"section_num\":1,\"section_label\":\"Lecture\",\"main_row\":{\"code\":\"\",\"task\":\"Lecture\",\"percent\":40},\"main_ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"Midterm Exams\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]},{\"code\":\"\",\"task\":\"Quizzes / Chapter Tests\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]},{\"code\":\"\",\"task\":\"Assignments / Research Review\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]},{\"code\":\"\",\"task\":\"Projects\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]},{\"code\":\"\",\"task\":\"Final Exam\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]}]},{\"section_num\":2,\"section_label\":\"Laboratory\",\"main_row\":{\"code\":\"\",\"task\":\"Laboratory\",\"percent\":60},\"main_ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"Laboratory Exercises\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]},{\"code\":\"\",\"task\":\"Laboratory Exams\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,null,null]}]}]}', NULL, '[]', NULL, NULL, NULL, 'PEREYRA MATTHEW ALEN', 'Professor 1', '2025-12-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 231, NULL),
(280, 234, 103, 88, 'Intro to Computing', '2025-2026', '1st Semester', '1st Year', 'approved', NULL, '2025-12-04 19:48:05', '2025-12-04 19:47:51', '2025-12-04 19:49:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PEREYRA MATTHEW ALEN', 'Professor 1', '2025-12-05', NULL, NULL, '2025-12-05', NULL, NULL, NULL, NULL, 231, NULL),
(282, 234, 103, 86, 'CIS for BAT401', '2025-2026', '1st Semester', '3rd Year', 'draft', NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:24:45', NULL, '{\"sections\":[{\"section_num\":1,\"section_label\":\"Laboratory\",\"main_row\":{\"code\":\"LAB\",\"task\":\"Laboratory\",\"percent\":60},\"main_ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"sub_rows\":[{\"code\":\"LE\",\"task\":\"Laboratory Exercises\",\"ird\":\"D\",\"percent\":null,\"ilo_columns\":[\"1400\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[null,1400,null]},{\"code\":\"LEX\",\"task\":\"Laboratory Exams\",\"ird\":\"D\",\"percent\":null,\"ilo_columns\":[\"200\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[100,100,null]}]},{\"section_num\":2,\"section_label\":\"Lecture\",\"main_row\":{\"code\":\"LEC\",\"task\":\"Lecture\",\"percent\":40},\"main_ilo_columns\":[\"\",\"\",\"\",\"\",\"\"],\"sub_rows\":[{\"code\":\"ME\",\"task\":\"Midterm Exam\",\"ird\":\"I\",\"percent\":null,\"ilo_columns\":[\"35\",\"35\",\"\",\"\",\"\"],\"cpa_columns\":[70,null,null]},{\"code\":\"FE\",\"task\":\"Final Exam\",\"ird\":\"R\",\"percent\":null,\"ilo_columns\":[\"35\",\"35\",\"\",\"\",\"\"],\"cpa_columns\":[70,null,null]},{\"code\":\"Q\",\"task\":\"Quiz\",\"ird\":\"R\",\"percent\":null,\"ilo_columns\":[\"100\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[100,null,null]},{\"code\":\"AS\",\"task\":\"Assignments\",\"ird\":\"I/R\",\"percent\":null,\"ilo_columns\":[\"200\",\"\",\"\",\"\",\"\"],\"cpa_columns\":[200,null,null]},{\"code\":\"PR\",\"task\":\"Projects\",\"ird\":\"D\",\"percent\":null,\"ilo_columns\":[\"50\",\"50\",\"\",\"\",\"\"],\"cpa_columns\":[70,30,null]}]}]}', NULL, '[]', NULL, NULL, NULL, 'PEREYRA MATTHEW ALEN', 'Professor 1', '2025-12-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(283, 235, 103, 92, 'GED 102', '2025-2026', '2nd Semester', '1st Year', 'pending_review', NULL, '2025-12-04 23:50:35', '2025-12-04 23:49:28', '2025-12-04 23:50:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PABLICO ADRIANE ALLEN', 'Professor 1', '2025-12-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 231, NULL),
(285, 234, 103, 86, 'CIS', '2025-2026', '1st Semester', '1st Year', 'pending_review', NULL, '2025-12-05 00:55:10', '2025-12-05 00:35:36', '2025-12-05 00:55:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PEREYRA MATTHEW ALEN', 'Professor 1', '2025-12-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 231, NULL),
(286, 235, 103, 86, 'CIS', '2025-2026', '1st Semester', '1st Year', 'draft', NULL, NULL, '2025-12-06 14:00:37', '2025-12-08 10:22:47', NULL, '{\"sections\":[{\"section_num\":1,\"section_label\":null,\"main_row\":{\"code\":\"\",\"task\":\"\",\"percent\":null},\"main_ilo_columns\":[\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\"],\"cpa_columns\":[null,null,null]}]},{\"section_num\":2,\"section_label\":null,\"main_row\":{\"code\":\"\",\"task\":\"\",\"percent\":null},\"main_ilo_columns\":[\"\"],\"sub_rows\":[{\"code\":\"\",\"task\":\"\",\"ird\":\"\",\"percent\":null,\"ilo_columns\":[\"\"],\"cpa_columns\":[null,null,null]}]}]}', NULL, '[]', NULL, NULL, NULL, 'PABLICO ADRIANE ALLEN', 'Professor 1', '2025-12-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `syllabus_assessment_mappings`
--

INSERT INTO `syllabus_assessment_mappings` (`id`, `syllabus_id`, `name`, `week_marks`, `position`, `created_at`, `updated_at`) VALUES
(1929, 278, 'Midterm Exams', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 0, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1930, 278, 'Quizzes / Chapter Tests', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 1, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1931, 278, 'Assignments / Research Review', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 2, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1932, 278, 'Projects', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 3, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1933, 278, 'Final Exam', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 4, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1934, 278, 'Laboratory Exercises', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 5, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1935, 278, 'Laboratory Exams', '{\"1\":null,\"2\":null,\"3\":null,\"4\":null}', 6, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(1943, 282, 'Laboratory Exercises', '{\"1\":null,\"2\":null,\"3\":null}', 0, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1944, 282, 'Laboratory Exams', '{\"1\":null,\"2\":null,\"3\":null}', 1, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1945, 282, 'Midterm Exam', '{\"1\":null,\"2\":null,\"3\":null}', 2, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1946, 282, 'Final Exam', '{\"1\":null,\"2\":null,\"3\":null}', 3, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1947, 282, 'Quiz', '{\"1\":null,\"2\":null,\"3\":null}', 4, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1948, 282, 'Assignments', '{\"1\":null,\"2\":null,\"3\":null}', 5, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1949, 282, 'Projects', '{\"1\":null,\"2\":null,\"3\":null}', 6, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1956, 286, NULL, '{\"1\":null}', 0, '2025-12-08 10:22:45', '2025-12-08 10:22:45'),
(1957, 286, NULL, '{\"1\":null}', 1, '2025-12-08 10:22:45', '2025-12-08 10:22:45');

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

--
-- Dumping data for table `syllabus_assessment_tasks`
--

INSERT INTO `syllabus_assessment_tasks` (`id`, `syllabus_id`, `section_number`, `row_type`, `section_legacy`, `section_label`, `code`, `task`, `ird`, `percent`, `ilo_flags`, `c`, `p`, `a`, `position`, `created_at`, `updated_at`) VALUES
(2238, 278, 1, 'main', 'Section 1', 'Lecture', NULL, 'Lecture', NULL, 40.00, '[null,null,null,null,null]', NULL, NULL, NULL, 0, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2239, 278, 1, 'sub', 'Section 1', 'Lecture', NULL, 'Midterm Exams', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 1, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2240, 278, 1, 'sub', 'Section 1', 'Lecture', NULL, 'Quizzes / Chapter Tests', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 2, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2241, 278, 1, 'sub', 'Section 1', 'Lecture', NULL, 'Assignments / Research Review', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 3, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2242, 278, 1, 'sub', 'Section 1', 'Lecture', NULL, 'Projects', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 4, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2243, 278, 1, 'sub', 'Section 1', 'Lecture', NULL, 'Final Exam', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 5, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2244, 278, 2, 'main', 'Section 2', 'Laboratory', NULL, 'Laboratory', NULL, 60.00, '[null,null,null,null,null]', NULL, NULL, NULL, 6, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2245, 278, 2, 'sub', 'Section 2', 'Laboratory', NULL, 'Laboratory Exercises', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 7, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2246, 278, 2, 'sub', 'Section 2', 'Laboratory', NULL, 'Laboratory Exams', NULL, NULL, '[null,null,null,null,null]', NULL, NULL, NULL, 8, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2256, 282, 1, 'main', 'Section 1', 'Laboratory', 'LAB', 'Laboratory', NULL, 60.00, '[null,null,null,null,null]', NULL, NULL, NULL, 0, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2257, 282, 1, 'sub', 'Section 1', 'Laboratory', 'LE', 'Laboratory Exercises', 'D', NULL, '[\"1400\",null,null,null,null]', NULL, '1400', NULL, 1, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2258, 282, 1, 'sub', 'Section 1', 'Laboratory', 'LEX', 'Laboratory Exams', 'D', NULL, '[\"200\",null,null,null,null]', '100', '100', NULL, 2, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2259, 282, 2, 'main', 'Section 2', 'Lecture', 'LEC', 'Lecture', NULL, 40.00, '[null,null,null,null,null]', NULL, NULL, NULL, 3, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2260, 282, 2, 'sub', 'Section 2', 'Lecture', 'ME', 'Midterm Exam', 'I', NULL, '[\"35\",\"35\",null,null,null]', '70', NULL, NULL, 4, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2261, 282, 2, 'sub', 'Section 2', 'Lecture', 'FE', 'Final Exam', 'R', NULL, '[\"35\",\"35\",null,null,null]', '70', NULL, NULL, 5, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2262, 282, 2, 'sub', 'Section 2', 'Lecture', 'Q', 'Quiz', 'R', NULL, '[\"100\",null,null,null,null]', '100', NULL, NULL, 6, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2263, 282, 2, 'sub', 'Section 2', 'Lecture', 'AS', 'Assignments', 'I/R', NULL, '[\"200\",null,null,null,null]', '200', NULL, NULL, 7, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2264, 282, 2, 'sub', 'Section 2', 'Lecture', 'PR', 'Projects', 'D', NULL, '[\"50\",\"50\",null,null,null]', '70', '30', NULL, 8, '2025-12-04 20:24:43', '2025-12-04 20:24:43');

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

--
-- Dumping data for table `syllabus_cdios`
--

INSERT INTO `syllabus_cdios` (`id`, `syllabus_id`, `code`, `title`, `description`, `position`, `created_at`, `updated_at`) VALUES
(2347, 273, 'CDIO1', NULL, NULL, 1, '2025-12-04 19:01:44', '2025-12-04 19:01:44'),
(2357, 278, 'CDIO1', 'Disciplinary Knowledge & Reasoning', 'Knowledge of underlying mathematics and sciences, core engineering fundamental knowledge, advanced engineering fundamental knowledge, methods, and tools.', 1, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2358, 278, 'CDIO2', 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problem solving; experimentation, investigation, and knowledge discovery; system thinking; attitudes, thoughts, and learning; ethics, equity, and other responsibilitie', 2, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2359, 278, 'CDIO3', 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', 3, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2360, 278, 'CDIO4', 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context; enterprise and business context; conceiving; systems engineering and management; designing; implementing; operating.', 4, '2025-12-04 19:45:03', '2025-12-04 19:45:03'),
(2373, 282, 'CDIO1', 'Disciplinary Knowledge & Reasoning', 'Knowledge of underlying mathematics and sciences, core engineering fundamental knowledge, advanced engineering fundamental knowledge, methods, and tools.', 1, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2374, 282, 'CDIO2', 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problem solving; experimentation, investigation, and knowledge discovery; system thinking; attitudes, thoughts, and learning; ethics, equity, and other responsibilitie', 2, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2375, 282, 'CDIO3', 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', 3, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2376, 282, 'CDIO4', 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context; enterprise and business context; conceiving; systems engineering and management; designing; implementing; operating.', 4, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(2377, 285, 'CDIO1', 'Disciplinary Knowledge & Reasoning', 'Knowledge of underlying mathematics and sciences, core engineering fundamental knowledge, advanced engineering fundamental knowledge, methods, and tools.', 1, '2025-12-05 00:40:42', '2025-12-05 00:40:42'),
(2378, 285, 'CDIO2', 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problem solving; experimentation, investigation, and knowledge discovery; system thinking; attitudes, thoughts, and learning; ethics, equity, and other responsibilitie', 2, '2025-12-05 00:40:42', '2025-12-05 00:40:42'),
(2379, 285, 'CDIO3', 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', 3, '2025-12-05 00:40:42', '2025-12-05 00:40:42'),
(2380, 285, 'CDIO4', 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context; enterprise and business context; conceiving; systems engineering and management; designing; implementing; operating.', 4, '2025-12-05 00:40:42', '2025-12-05 00:40:42'),
(2399, 286, 'CDIO1', 'Disciplinary Knowledge & Reasoning', 'Knowledge of underlying mathematics and sciences, core engineering fundamental knowledge, advanced engineering fundamental knowledge, methods, and tools.', 1, '2025-12-08 10:23:14', '2025-12-08 10:23:14');

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
(94, 251, 'ilo', 'Intended Learning Outcomes (ILO)', NULL, 'draft', 1, 217, 217, '2025-12-02 21:19:33', '2025-12-02 21:19:33'),
(95, 267, 'tlas', 'Teaching, Learning, and Assessment Strategies', 'Define Teaching, Learning, and Assessment Strategies', 'draft', 1, 229, 229, '2025-12-04 18:18:21', '2025-12-04 18:18:30');

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
(190, 273, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', NULL, '1st Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'MONTEALEGRE PAUL JELAN', '22-70787', NULL, 'Professor 1', 'December 05, 2025', '22-70787@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture\n2 hours laboratory', '3', '2', '2025-12-04 19:00:35', '2025-12-04 19:01:45'),
(195, 278, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', NULL, '1st Semester', '3rd Year', '5 (3 hrs lec; 2 hrs lab)', 'PEREYRA MATTHEW ALEN', '22-72684', NULL, 'Professor 1', 'December 05, 2025', '22-72684@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, 'This course provides students with an overview of the current trends in information technology that drives today’s business. The course will provide understanding on data management techniques that can help an organization to achieve its business goals and address operational challenges. This will also introduce different tools and methods used in business analytics to provide the students with opportunities to apply these techniques in simulations in acomputer laboratory.', 'Written/ Oral Exam\nThere will be two (2) major examinations to be conducted in-class. The examinations will cover the topics discussed for the given period but may include some topics from the preceding period due to the continuity of concepts.\n\nThe course is taught using a structured program of hybrid learning (face-to-face and online), video presentations, tutorials, laboratory activities and student-centered learning specifically: (a) self-directed learning using on-line material and lectures to supplement on-line material (b) laboratory sessions to gain practical experience and re- enforce theory (d) individual assignment work as part of laboratory work (e) web-based research and (f) reporting.\n\nStudents will be assessed using any or combination of rubrics, paper and pencil tests, oral and paper presentation and portfolio and/or any of the following methods: Midterm and Final Exam, Quizzes/Chapter Tests, Attendance/Assignments/Research Review, Evaluation of Laboratory Outputs (using rubrics), and Projects.', '3 hours lecture\n2 hours laboratory', '3', '2', '2025-12-04 19:31:28', '2025-12-04 19:43:50'),
(197, 280, 'Introduction to Computing', 'IT 111', 'Core, Elective, Professional', '', '1st Semester', '1st Year', '3 (3 hrs lec; 0 hrs lab)', 'PEREYRA MATTHEW ALEN', '22-72684', NULL, 'Professor 1', 'December 05, 2025', '22-72684@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture', '3 hours lecture', NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(199, 282, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', NULL, '1st Semester', '3rd Year', '5 (3 hrs lec; 2 hrs lab)', 'PEREYRA MATTHEW ALEN', '22-72684', NULL, 'Professor 1', 'December 05, 2025', '22-72684@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture\n2 hours laboratory', '3', '2', '2025-12-04 20:01:09', '2025-12-04 20:03:12'),
(200, 283, 'Information Management', 'IT 221', 'Core Elective', '', '2nd Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'PABLICO ADRIANE ALLEN', '22-77551', NULL, 'Professor 1', 'December 05, 2025', '22-77551@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(202, 285, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', '', '1st Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'PEREYRA MATTHEW ALEN', '22-72684', NULL, 'Professor 1', 'December 05, 2025', '22-72684@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture; 2 hours laboratory', '3 hours lecture', '2 hours laboratory', '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(203, 286, 'Fundamentals of Business Analytics', 'BAT 401', 'Professional Elective: Business Analytics Track', NULL, '1st Semester', '1st Year', '5 (3 hrs lec; 2 hrs lab)', 'PABLICO ADRIANE ALLEN', '22-77551', NULL, 'Professor 1', 'December 06, 2025', '22-77551@g.batstate-u.edu.ph', NULL, '2025-2026', NULL, NULL, NULL, '3 hours lecture\n2 hours laboratory', '3', '2', '2025-12-06 14:00:37', '2025-12-06 14:25:12');

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
(707, 273, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(708, 273, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(709, 273, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(710, 273, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(711, 273, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(732, 278, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:31:28', '2025-12-04 19:31:28'),
(733, 278, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:31:28', '2025-12-04 19:31:28'),
(734, 278, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:31:28', '2025-12-04 19:31:28'),
(735, 278, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:31:28', '2025-12-04 19:31:28'),
(736, 278, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:31:28', '2025-12-04 19:31:28'),
(742, 280, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(743, 280, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(744, 280, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(745, 280, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(746, 280, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(752, 282, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(753, 282, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(754, 282, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(755, 282, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(756, 282, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(757, 283, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(758, 283, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(759, 283, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(760, 283, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(761, 283, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(767, 285, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(768, 285, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(769, 285, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(770, 285, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(771, 285, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(772, 286, 'policy', 'Prompt and regular attendance of students is required. Total unexcused absences shall not exceed ten (10) percent of\r\nthe maximum number of hours required per course per semester (or per summer term). A semester has 17 weeks.', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 14:00:37', '2025-12-06 14:00:37'),
(773, 286, 'exams', 'Students who failed to take the exam during the schedule date can be given a special exam provided he/she has valid\r\nreason. If it is health reason, he/she should provide the faculty with the medical certificate signed by the attending\r\nPhysician. Other reasons shall be assessed first by the faculty to determine its validity.', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 14:00:37', '2025-12-06 14:00:37'),
(774, 286, 'dishonesty', 'Academic dishonesty includes acts such as cheating during examinations or plagiarism in connection with any\r\nacademic work. Such acts are considered major offenses and will be dealt with according to the University’s Student\r\nNorms of Conduct.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 14:00:37', '2025-12-06 14:00:37'),
(775, 286, 'dropping', 'Dropping must be made official by accomplishing a dropping form and submitting it at the Registrar’s Office before\r\nthe midterm examination. Students who officially drop out of class shall be marked “Dropped” whether he took the\r\npreliminary examination or not and irrespective of their preliminary grades.\r\nA student who unofficially drops out of class shall be given a mark of “5.0” by the instructor.', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 14:00:37', '2025-12-06 14:00:37'),
(776, 286, 'other', 'Students with Disabilities/Special Needs (PWD). All students who have an illness or disability are encouraged to disclose to the instructor the nate and extent of the illness or disability so that the instructor can make the necessary adjustments.\r\nAll students are expected to promote and foster an environment that encourages positive, informed and unprejudiced attitudes towads students with disability.\r\nCONSULTATION AND ACADEMIC ADVISING\r\nStudents are highly encouraged to use the consultation hour of the instructor set by the college, whether virtually or face-to-face. It will be used to seek for an advice if there is any problem or difficulty encountered during the term. Discussion for academic purposes will also be entertained.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 14:00:37', '2025-12-06 14:00:37');

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

--
-- Dumping data for table `syllabus_criteria`
--

INSERT INTO `syllabus_criteria` (`id`, `syllabus_id`, `key`, `heading`, `section`, `value`, `position`, `created_at`, `updated_at`) VALUES
(3748, 273, 'lecture', '', '', '[]', 0, '2025-12-04 19:01:46', '2025-12-04 19:01:46'),
(3749, 273, 'laboratory', '', '', '[]', 1, '2025-12-04 19:01:46', '2025-12-04 19:01:46'),
(3754, 278, 'lecture_40', 'Lecture (40%)', 'Lecture (40%)', '[{\"description\":\"Midterm Exams\",\"percent\":\"20%\"},{\"description\":\"Quizzes \\/ Chapter Tests\",\"percent\":\"15%\"},{\"description\":\"Assignments \\/ Research Review\",\"percent\":\"15%\"},{\"description\":\"Projects\",\"percent\":\"20%\"},{\"description\":\"Final Exam\",\"percent\":\"30%\"}]', 0, '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(3755, 278, 'laboratory_60', 'Laboratory (60%)', 'Laboratory (60%)', '[{\"description\":\"Laboratory Exercises\",\"percent\":\"40%\"},{\"description\":\"Laboratory Exams\",\"percent\":\"60%\"}]', 1, '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(3760, 282, 'laboratory_60', 'Laboratory (60%)', 'Laboratory (60%)', '[{\"description\":\"Laboratory Exercises\",\"percent\":\"40%\"},{\"description\":\"Laboratory Exams\",\"percent\":\"60%\"}]', 0, '2025-12-04 20:24:45', '2025-12-04 20:24:45'),
(3761, 282, 'lecture_40', 'Lecture (40%)', 'Lecture (40%)', '[{\"description\":\"Midterm Exam\",\"percent\":\"20%\"},{\"description\":\"Final Exam\",\"percent\":\"30%\"},{\"description\":\"Quiz\",\"percent\":\"15%\"},{\"description\":\"Assignments\",\"percent\":\"15%\"},{\"description\":\"Projects\",\"percent\":\"20%\"}]', 1, '2025-12-04 20:24:45', '2025-12-04 20:24:45'),
(3770, 286, 'lecture', '', '', '[]', 0, '2025-12-08 10:22:47', '2025-12-08 10:22:47'),
(3771, 286, 'laboratory', '', '', '[]', 1, '2025-12-08 10:22:47', '2025-12-08 10:22:47');

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

--
-- Dumping data for table `syllabus_igas`
--

INSERT INTO `syllabus_igas` (`id`, `syllabus_id`, `code`, `title`, `description`, `position`, `created_at`, `updated_at`) VALUES
(692, 278, 'IGA1', 'Knowledge Competence', 'Demonstrate a mastery of the fundamental knowledge and skills required for functioning effectively as a professional in the discipline, and an ability to integrate and apply them effectively to practice in the workplace.', 1, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(693, 278, 'IGA2', 'Creativity and Innovation', 'Experiment with new approaches, challenge existing knowledge boundaries, and design novel solutions to solve problems.', 2, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(694, 278, 'IGA3', 'Critical and Systems Thinking', 'identify, define, and deal with complex problems pertinent to future professional practice or daily life through logical, analytical, and critical thinking.', 3, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(695, 278, 'IGA4', 'Communication', 'Communicate effectively (both orally and in writing) with a wide range of audiences, across a range of professional and personal contexts, in English and Filipino.', 4, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(696, 278, 'IGA5', 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate eagerness to take up opportunities for learning new things as well as the ability to learn effectively on their own.', 5, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(697, 278, 'IGA6', 'Leadership, Teamwork, and Interpersonal Skills', 'Function effectively both as a leader and as a member of a team; motivate and lead a team to work toward goals; work collaboratively with other team members; and connect and interact socially and effectively with diverse culture.', 6, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(698, 278, 'IGA7', 'Global Outlook', 'Demonstrate an awareness and understanding of global issues and willingness to work, interact effectively, and show sensitivity to cultural diversity.', 7, '2025-12-04 19:40:50', '2025-12-04 19:45:00'),
(699, 282, 'IGA1', 'Knowledge Competence', 'Demonstrate a mastery of the fundamental knowledge and skills required for functioning effectively as a professional in the discipline, and an ability to integrate and apply them effectively to practice in the workplace.', 1, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(700, 282, 'IGA2', 'Creativity and Innovation', 'Experiment with new approaches, challenge existing knowledge boundaries, and design novel solutions to solve problems.', 2, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(701, 282, 'IGA3', 'Critical and Systems Thinking', 'identify, define, and deal with complex problems pertinent to future professional practice or daily life through logical, analytical, and critical thinking.', 3, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(702, 282, 'IGA4', 'Communication', 'Communicate effectively (both orally and in writing) with a wide range of audiences, across a range of professional and personal contexts, in English and Filipino.', 4, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(703, 282, 'IGA5', 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate eagerness to take up opportunities for learning new things as well as the ability to learn effectively on their own.', 5, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(704, 282, 'IGA6', 'Leadership, Teamwork, and Interpersonal Skills', 'Function effectively both as a leader and as a member of a team; motivate and lead a team to work toward goals; work collaboratively with other team members; and connect and interact socially and effectively with diverse culture.', 6, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(705, 282, 'IGA7', 'Global Outlook', 'Demonstrate an awareness and understanding of global issues and willingness to work, interact effectively, and show sensitivity to cultural diversity.', 7, '2025-12-04 20:01:31', '2025-12-04 20:24:41'),
(706, 285, 'IGA1', 'Knowledge Competence', 'Demonstrate a mastery of the fundamental knowledge and skills required for functioning effectively as a professional in the discipline, and an ability to integrate and apply them effectively to practice in the workplace.', 1, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(707, 285, 'IGA2', 'Creativity and Innovation', 'Experiment with new approaches, challenge existing knowledge boundaries, and design novel solutions to solve problems.', 2, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(708, 285, 'IGA3', 'Critical and Systems Thinking', 'identify, define, and deal with complex problems pertinent to future professional practice or daily life through logical, analytical, and critical thinking.', 3, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(709, 285, 'IGA4', 'Communication', 'Communicate effectively (both orally and in writing) with a wide range of audiences, across a range of professional and personal contexts, in English and Filipino.', 4, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(710, 285, 'IGA5', 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate eagerness to take up opportunities for learning new things as well as the ability to learn effectively on their own.', 5, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(711, 285, 'IGA6', 'Leadership, Teamwork, and Interpersonal Skills', 'Function effectively both as a leader and as a member of a team; motivate and lead a team to work toward goals; work collaboratively with other team members; and connect and interact socially and effectively with diverse culture.', 6, '2025-12-05 00:38:01', '2025-12-05 00:38:01'),
(712, 285, 'IGA7', 'Global Outlook', 'Demonstrate an awareness and understanding of global issues and willingness to work, interact effectively, and show sensitivity to cultural diversity.', 7, '2025-12-05 00:38:01', '2025-12-05 00:38:01');

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

--
-- Dumping data for table `syllabus_ilos`
--

INSERT INTO `syllabus_ilos` (`id`, `syllabus_id`, `code`, `description`, `position`, `created_at`, `updated_at`) VALUES
(780, 278, 'ILO1', 'Explain fundamental concepts of computing, including processes, procedures, and information representation.', 1, '2025-12-04 19:40:47', '2025-12-04 19:45:01'),
(781, 278, 'ILO2', 'Illustrate how data is represented and manipulated within a computer system.', 2, '2025-12-04 19:40:47', '2025-12-04 19:45:01'),
(782, 278, 'ILO3', 'Differentiate between programming languages and describe their key features and uses.', 3, '2025-12-04 19:40:47', '2025-12-04 19:45:01'),
(783, 278, 'ILO4', 'Apply basic problem-solving strategies to design and analyze simple algorithms and procedures.', 4, '2025-12-04 19:40:47', '2025-12-04 19:45:01'),
(784, 278, 'ILO5', 'Discuss the impact of computing on society, ethics, and interdisciplinary fields.', 5, '2025-12-04 19:40:47', '2025-12-04 19:45:01'),
(790, 280, 'ILO1', 'Explain fundamental concepts of computing, including processes, procedures, and information representation.', 1, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(791, 280, 'ILO2', 'Illustrate how data is represented and manipulated within a computer system.', 2, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(792, 280, 'ILO3', 'Differentiate between programming languages and describe their key features and uses.', 3, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(793, 280, 'ILO4', 'Apply basic problem-solving strategies to design and analyze simple algorithms and procedures.', 4, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(794, 280, 'ILO5', 'Discuss the impact of computing on society, ethics, and interdisciplinary fields.', 5, '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(800, 282, 'ILO1', 'Explain the fundamental concepts, scope, and applications of business analytics in organizational decision-making.', 1, '2025-12-04 20:01:09', '2025-12-04 20:24:41'),
(801, 282, 'ILO2', 'Identify and describe the main types of business data and demonstrate data preparation and visualization techniques.', 2, '2025-12-04 20:01:09', '2025-12-04 20:24:41'),
(802, 282, 'ILO3', 'Apply basic descriptive, predictive, and prescriptive analytics methods to solve business problems using appropriate tools.', 3, '2025-12-04 20:01:09', '2025-12-04 20:24:41'),
(803, 282, 'ILO4', 'Interpret analytics results and communicate insights effectively using written, oral, and visual presentation formats.', 4, '2025-12-04 20:01:09', '2025-12-04 20:24:41'),
(804, 282, 'ILO5', 'Discuss ethical, legal, and societal considerations in the use of business analytics.', 5, '2025-12-04 20:01:09', '2025-12-04 20:24:41'),
(815, 285, 'ILO1', 'Explain fundamental concepts of computing, including processes, procedures, and information representation.', 1, '2025-12-05 00:53:50', '2025-12-05 00:53:50'),
(816, 285, 'ILO2', 'Illustrate how data is represented and manipulated within a computer system.', 2, '2025-12-05 00:53:50', '2025-12-05 00:53:50'),
(817, 285, 'ILO3', 'Differentiate between programming languages and describe their key features and uses.', 3, '2025-12-05 00:53:50', '2025-12-05 00:53:50'),
(818, 285, 'ILO4', 'Apply basic problem-solving strategies to design and analyze simple algorithms and procedures.', 4, '2025-12-05 00:53:50', '2025-12-05 00:53:50'),
(819, 285, 'ILO5', 'Discuss the impact of computing on society, ethics, and interdisciplinary fields.', 5, '2025-12-05 00:53:50', '2025-12-05 00:53:50');

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
(194, 273, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-04 19:00:35', '2025-12-04 19:00:35'),
(199, 278, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-04 19:31:27', '2025-12-04 19:31:27'),
(201, 280, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-04 19:47:51', '2025-12-04 19:47:51'),
(203, 282, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-04 20:01:09', '2025-12-04 20:01:09'),
(204, 283, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-04 23:49:29', '2025-12-04 23:49:29'),
(206, 285, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-05 00:35:36', '2025-12-05 00:35:36'),
(207, 286, 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-12-06 14:00:37', '2025-12-06 14:00:37');

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

--
-- Dumping data for table `syllabus_sdgs`
--

INSERT INTO `syllabus_sdgs` (`id`, `syllabus_id`, `code`, `sort_order`, `title`, `description`, `created_at`, `updated_at`) VALUES
(569, 278, 'SDG1', 1, 'Envisioning', 'Establish a link between long-term goals and immediate actions, and motivate people to take action by harnessing their deep aspiratio', '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(570, 278, 'SDG2', 2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social, and cultural structures in the context of sustainable development, and challenge people to examine and question the underlying assumptions that influence their world views by having them reflect on unsustainable practices.', '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(571, 278, 'SDG3', 3, 'Systemic Thinking', 'Recognize that the whole is more than the sum of its parts, and it is a better way to understand and manage complex situations.', '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(572, 278, 'SDG4', 4, 'Building Partnerships', 'Promote dialogue and negotiation, learning to work together, so as to strengthen ownership of and commitment to sustainable action through education and learning.', '2025-12-04 19:45:05', '2025-12-04 19:45:05'),
(585, 282, 'SDG1', 1, 'Envisioning', 'Establish a link between long-term goals and immediate actions, and motivate people to take action by harnessing their deep aspiratio', '2025-12-04 20:24:44', '2025-12-04 20:24:44'),
(586, 282, 'SDG2', 2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social, and cultural structures in the context of sustainable development, and challenge people to examine and question the underlying assumptions that influence their world views by having them reflect on unsustainable practices.', '2025-12-04 20:24:44', '2025-12-04 20:24:44'),
(587, 282, 'SDG3', 3, 'Systemic Thinking', 'Recognize that the whole is more than the sum of its parts, and it is a better way to understand and manage complex situations.', '2025-12-04 20:24:44', '2025-12-04 20:24:44'),
(588, 282, 'SDG4', 4, 'Building Partnerships', 'Promote dialogue and negotiation, learning to work together, so as to strengthen ownership of and commitment to sustainable action through education and learning.', '2025-12-04 20:24:44', '2025-12-04 20:24:44'),
(589, 285, 'SDG1', 1, 'Envisioning', 'Establish a link between long-term goals and immediate actions, and motivate people to take action by harnessing their deep aspiratio', '2025-12-05 00:40:47', '2025-12-05 00:40:47'),
(590, 285, 'SDG2', 2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social, and cultural structures in the context of sustainable development, and challenge people to examine and question the underlying assumptions that influence their world views by having them reflect on unsustainable practices.', '2025-12-05 00:40:47', '2025-12-05 00:40:47'),
(591, 285, 'SDG3', 3, 'Systemic Thinking', 'Recognize that the whole is more than the sum of its parts, and it is a better way to understand and manage complex situations.', '2025-12-05 00:40:47', '2025-12-05 00:40:47'),
(592, 285, 'SDG4', 4, 'Building Partnerships', 'Promote dialogue and negotiation, learning to work together, so as to strengthen ownership of and commitment to sustainable action through education and learning.', '2025-12-05 00:40:47', '2025-12-05 00:40:47');

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

--
-- Dumping data for table `syllabus_sos`
--

INSERT INTO `syllabus_sos` (`id`, `syllabus_id`, `code`, `title`, `position`, `description`, `created_at`, `updated_at`) VALUES
(3193, 273, 'SO1', '', 1, 'Ability to analyze a complex computing problem and apply principles of computing and other relevant disciplines to identify solutions.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3194, 273, 'SO2', '', 2, 'Ability to design, implement, and evaluate a computing-based solution to meet a given set of computing requirements in the context of the program’s discipline.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3195, 273, 'SO3', '', 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3196, 273, 'SO4', '', 4, 'Ability to recognize professional responsibilities and make informed judgments in computing practice based on legal and ethical principles.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3197, 273, 'SO5', '', 5, 'Ability to function effectively as a member or leader of a team engaged in activities appropriate to the program’s discipline.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3198, 273, 'SO6', '', 6, 'Ability to identify and analyze user needs and take them into account in the selection, creation, integration, evaluation, and administration of computing-based systems.', '2025-12-04 19:00:56', '2025-12-04 19:01:44'),
(3200, 278, 'SO1', '', 1, 'Ability to analyze a complex computing problem and apply principles of computing and other relevant disciplines to identify solutions.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3201, 278, 'SO2', '', 2, 'Ability to design, implement, and evaluate a computing-based solution to meet a given set of computing requirements in the context of the program’s discipline.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3202, 278, 'SO3', '', 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3203, 278, 'SO4', '', 4, 'Ability to recognize professional responsibilities and make informed judgments in computing practice based on legal and ethical principles.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3204, 278, 'SO5', '', 5, 'Ability to function effectively as a member or leader of a team engaged in activities appropriate to the program’s discipline.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3205, 278, 'SO6', '', 6, 'Ability to identify and analyze user needs and take them into account in the selection, creation, integration, evaluation, and administration of computing-based systems.', '2025-12-04 19:43:38', '2025-12-04 19:45:02'),
(3206, 282, 'SO1', '', 1, 'Ability to analyze a complex computing problem and apply principles of computing and other relevant disciplines to identify solutions.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3207, 282, 'SO2', '', 2, 'Ability to design, implement, and evaluate a computing-based solution to meet a given set of computing requirements in the context of the program’s discipline.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3208, 282, 'SO3', '', 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3209, 282, 'SO4', '', 4, 'Ability to recognize professional responsibilities and make informed judgments in computing practice based on legal and ethical principles.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3210, 282, 'SO5', '', 5, 'Ability to function effectively as a member or leader of a team engaged in activities appropriate to the program’s discipline.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3211, 282, 'SO6', '', 6, 'Ability to identify and analyze user needs and take them into account in the selection, creation, integration, evaluation, and administration of computing-based systems.', '2025-12-04 20:01:34', '2025-12-04 20:24:42'),
(3212, 285, 'SO1', NULL, 1, 'Ability to analyze a complex computing problem and apply principles of computing and other relevant disciplines to identify solutions.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3213, 285, 'SO2', NULL, 2, 'Ability to design, implement, and evaluate a computing-based solution to meet a given set of computing requirements in the context of the program’s discipline.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3214, 285, 'SO3', NULL, 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3215, 285, 'SO4', NULL, 4, 'Ability to recognize professional responsibilities and make informed judgments in computing practice based on legal and ethical principles.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3216, 285, 'SO5', NULL, 5, 'Ability to function effectively as a member or leader of a team engaged in activities appropriate to the program’s discipline.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3217, 285, 'SO6', NULL, 6, 'Ability to identify and analyze user needs and take them into account in the selection, creation, integration, evaluation, and administration of computing-based systems.', '2025-12-05 00:38:06', '2025-12-05 00:38:06'),
(3218, 286, 'SO1', '', 1, '', '2025-12-06 14:25:10', '2025-12-08 10:22:43');

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
(197, 278, 234, 'draft', 'pending_review', 234, NULL, '2025-12-04 19:45:18', '2025-12-04 19:45:18', '2025-12-04 19:45:18'),
(198, 280, 234, 'draft', 'pending_review', 234, NULL, '2025-12-04 19:48:05', '2025-12-04 19:48:05', '2025-12-04 19:48:05'),
(199, 280, 234, 'pending_review', 'approved', 231, NULL, '2025-12-04 19:49:04', '2025-12-04 19:49:04', '2025-12-04 19:49:04'),
(200, 283, 235, 'draft', 'pending_review', 235, NULL, '2025-12-04 23:50:35', '2025-12-04 23:50:35', '2025-12-04 23:50:35'),
(201, 285, 234, 'draft', 'pending_review', 234, NULL, '2025-12-05 00:55:10', '2025-12-05 00:55:10', '2025-12-05 00:55:10');

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

--
-- Dumping data for table `syllabus_textbooks`
--

INSERT INTO `syllabus_textbooks` (`id`, `syllabus_id`, `file_path`, `original_name`, `type`, `created_at`, `updated_at`) VALUES
(115, 286, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 'An Introduction to Business Analytics By Ger koole.pdf', 'main', '2025-12-08 11:57:59', '2025-12-08 11:57:59');

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
(5410, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 0, 'AnIntroductiontoBusinessAnalytics Copyrightc!2019Ger Koole All rights reserved MG books, Amsterdam ISBN 978 90 820179 3 9 Cover design: Ingrid Brandenburg & Luciano Picozzi AnIntroductiontoBusinessAnalytics GerKoole MG books Amsterdam Preface Books on Business Analytics (BA) typically fall into two categories: manage- rial books without any technical details, and very technical books, written for BA majors who already have a background in advanced mathematics or computer science. This book tries to ﬁll the gap by discussing BA tech- niques at a level appropriate for readers with a less technical background. This makes it suitable for many different audiences, especially managers who want to better understand the work of their data scientists, or people who want to learn the basics of BA and do their ﬁrst BA projects themselves. The full range of BA-related topics is covered: from the many different techniques to an overview of managerial aspects; from comparisons of the usefulness of different techniques in different situations to their historical context. While working with this book, you will also learn appropriate tool- ing, especially R and a bit of Excel. There are exercises t', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5411, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 1, 'o sharpen your skills and test your understanding. Because this book contains a large variety of topics, I sought advice from many experts. I am especially indebted to Sandjai Bhulai, Bram Gorissen, Jeroen van Kasteren, Diederik Roijers and Qingchen Wang for their feed- back on scientiﬁc issues and Peggy Curley for editing. Business Analytics is a young ﬁeld in full development, which uses as- pects from various ﬁelds of science. Although I tried to integrate the knowl- edge from many ﬁelds, it is unavoidable that the content will be biased based on my background and experience. Please do not hesitate to send me an email if you have any ideas or comments to share. I sincerely hope that reading this book is a rewarding experience. All chapters can be read independently, but I advise to read Chapter1 ﬁrst to understand the connections between the chapters. The index at the end can be helpful for unknown terms and abbreviations. Ger Koole Amsterdam/Peymeinade, 2016–2019 i ii Koole — Business Analytics Contents Preface i Contents v 1 Introduction 1 1.1 What is business analytics?.................... 1 1.2 Historical overview......................... 5 1.3 Non-technical overview........', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5412, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 2, '.............. 7 1.4 Tooling................................ 10 1.5 Implementation........................... 14 1.6 Additional reading......................... 14 2 Going on a Tour with R 17 2.1 Getting started............................ 17 2.2 Learning R.............................. 19 2.3 Libraries............................... 20 2.4 Data structures........................... 20 2.5 Programming............................ 21 2.6 Simulation and hypothesis testing................ 22 2.7 Clustering.............................. 24 2.8 Regression and deep learning................... 25 2.9 Classiﬁcation............................. 26 2.10 Optimization............................. 28 2.11 Additional reading......................... 29 3 Variability 31 3.1 Summarizing data.......................... 32 3.2 Probability theory and the binomial distribution........ 34 3.3 Other distributions and the central limit theorem........ 41 iii iv Koole — Business Analytics 3.4 Parameter estimation........................ 49 3.5 Additional reading......................... 53 4 Machine Learning 55 4.1 Data preparation.......................... 56 4.2 Clustering.............................. 57', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5413, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 3, ' 4.3 Linear regression.......................... 59 4.4 Nonlinear prediction........................ 64 4.5 Forecasting.............................. 69 4.6 Classiﬁcation............................. 72 4.7 Additional reading......................... 76 5 Simulation 77 5.1 Monte Carlo simulation...................... 77 5.2 Discrete-event simulation..................... 80 5.3 Additional reading......................... 84 6 Linear Optimization 85 6.1 Problem formulation........................ 85 6.2 LO in Excel.............................. 89 6.3 Example LO problems....................... 92 6.4 Integer problems.......................... 95 6.5 Example ILO problems....................... 98 6.6 Modeling tools............................ 100 6.7 Modeling tricks........................... 101 6.8 Additional reading......................... 106 7 Combinatorial Optimization 107 7.1 The shortest path problem..................... 107 7.2 The maximum ﬂow problem ................... 111 7.3 The traveling salesman problem................. 112 7.4 Complexity.............................. 114 7.5 Additional reading......................... 116 8 Simulation Optimization 117 8.1 Introduction', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5414, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 4, '............................. 118 8.2 Comparing scenarios........................ 119 8.3 Ranking and selection....................... 119 8.4 Local search............................. 121 Contents v 8.5 Additional reading......................... 122 9 Dynamic Programming and Reinforcement Learning 123 9.1 Dynamic programming ...................... 124 9.2 Stochastic Dynamic Programming................ 126 9.3 Approximate Dynamic Programming .............. 129 9.4 Models with partial information................. 130 9.5 Reinforcement Learning...................... 132 9.6 Additional reading......................... 135 10 Answers to Exercises 137 Bibliography 153 Index 157 vi Koole — Business Analytics Chapter1 Introduction This chapter explains business analytics and data science without going into any technical detail. We will clarify the meaning of different terms used, put the current developments in a historical perspective, give the reader an idea of the potential of business analytics (BA), and give a high-level overview of the steps and pitfalls in implementing a BA strategy. Learning outcomesOn completion of this chapter, you will be able to: •describe in non-technical te', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5415, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 5, 'rms the ﬁeld of business analytics, the dif- ferent steps involved, the connections to other ﬁelds of study and its historical context •reﬂect on the skills and knowledge required to successfully apply busi- ness analytics in practice 1.1 What is business analytics? According to Wikipedia, ”Business analytics refers to the skills, technolo- gies, practices for continuous iterative exploration and investigation of past business performance to gain insight and drive business planning.” In short, BA is a rational, fact-based approach to decision making. These facts come from data, therefore BA is about the science and the skills to turn data into decisions. The science is mostlystatistics,artiﬁcial intelligence(data mining andmachine learning), andoptimization; the skills are computer skills, com- munication skills, project and change management, etc. 1 2 Koole — Business Analytics It should be clear that BA by itself is not a science. It is the total set of knowledge that is required to solve business problems in a rational way. To be a successful business analyst, experience in BA projects and knowledge of the business areas that the data comes from (such as healthcare, advertis- in', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5416, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 6, 'g, ﬁnance) is also very valuable. BA is often subdivided into three consecutive activities:descriptive ana- lytics,predictive analytics, andprescriptive analytics. During the descriptive phase, data is analyzed and patterns are found. The insights are conse- quently used in the predictive phase to predict what is likely to happen in the future, if the situation remains the same. Finally, in the prescriptive phase, alternative decisions are determined that change the situation and which will lead to desirable outcomes. Example 1.1A hotel chain analyzes its reservations to look for patterns: which are the busiest days of the week? What is the impact of events in the city? Is there a seasonal pattern? Etc. The outcomes are used to make a prediction for the revenue in the upcoming months. By changing the pricing of the rooms in certain situations (such as sports events), the expected revenue can be maximized. Analytics can only start when there is data. Certain organizations al- ready have a centralizeddata warehousein which relevant current and his- torical data is stored for the purpose of reporting and analytics. Setting up such a data warehouse and maintaining it is part of thebusi', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5417, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 7, 'ness intelligence (BI) strategy of a company. However, not all companies have such a central- ized database, and even when it exists it rarely contains all the information required for a certain analysis. Therefore, data often needs to be collected, cleansed and combined with other sources. Data collection, cleansing and further pre-processing is usually a very time-consuming task, often taking more time than the actual analysis. Example 1.2In the hotelrevenue managementexample above we need histor- ical data on reservations but also data on historical and future events in the sur- roundings of the hotel. There are many reasons why this data can be hard to get: reservation data may only be stored at an aggregated level, there may have been changes in IT systems which overrode previously collected data, there may be no centrally available list with events, etc. Many organizations assume they already have all the data required, but as soon as the data scientist asks for reservation data combined with the date the booking was made or the event list from the surrounding area, the hotel might ﬁnd out that they lack data. Chapter1— Introduction 3 Therefore, data collection and pre-proces', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5418, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 8, 'sing are always the ﬁrst steps of a BA project. Following the data collection and pre-processing the real data science steps begin with descriptive analytics. Moreover, a BA project does not end with prescriptive analytics, i.e., with generating an (optimal) decision. The decision has to be implemented, which requires various skills, such as knowledge of change management. To summarize, we distinguish the following steps in a BA project: The model above suggests a linear process, but in practice this is rarely the case. At many of the steps, depending on the outcome, you might re- visit earlier steps. For example, if the predictions are not accurate enough for a particular aaplication then you might collect extra data to improve them. Furthermore, not all BA projects include prescriptive analytics, many projects have insight or prediction as goal and therefore ﬁnish after the de- scriptive or predictive steps. The major scientiﬁc ﬁelds of study corresponding to these BA steps are: Next to cleansing,feature engineeringis an important part of data prepa- ration, to be discussed later. During descriptive analytics you get an under- standing of the data. You visualize the data and you ', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5419, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 9, 'summarize it using the tool of statistical data analysis. Getting a good understanding is crucial for making the right choices in the consecutive steps. Following the descriptive analytics a BA project continues with predic- tive analytics. A target value is speciﬁed which we want to predict. Based on the data available, the parameters of the selected predictive method are determined. We say that the model istrainedon the data. The methods originate from inferential statistics and machine learning, which have their respective roots in mathematics and computer science. Although the ap- proach and the background of these ﬁelds are quite different, the techniques 4 Koole — Business Analytics largely overlap. Example 1.3A debt collection agency wants to use its resources, mainly calls to debtors, in a better way. It collects data on payments which is enriched by exter- nal data on household composition and neighborhood characteristics. After the data analysis and visualization a method is selected that predicts, given the characteris- tics of the dept and the actions taken by the agengy, the probability that the deptor will pay off their debt. In the prescriptive step, which is to be d', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5420, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 10, 'iscussed next, the best action for each deptor is determined. Finally, during the prescriptive analytics phase, options are found to maximize a certain objective. Because the future is always unpredictable to a certain extent, optimization techniques often have to account for this randomness. The ﬁeld that specializes in this is (mathematical)optimization. It overlaps partially withreinforcement learning, which has its roots in com- puter science. A special feature of reinforcement learning is that prediction and optimization are integrated: it combines in one method the predictive and prescriptive phases. Example 1.4Consider again Example1.2on hotel revenue management. After having studied the inﬂuence of events and for example intra-week ﬂuctuations on hotel reservations in the descriptive step demand per price class isforecastedin the predictive step. These forecasts are input to an optimization algorithm that determines on a daily basis the prices that maximize total revenue. We end this section by discussing two terms that are closely related to BA:Data scienceandbig data. Data science is an older term which has re- cently shifted in meaning and increased in popularity. It is ', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5421, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 11, 'a combination of different scientiﬁc ﬁelds all concerned with extracting knowledge from data, mainly data mining and statistics. Part of the popularity probably stems from the fact that the Harvard Business Review called a data scien- tist role ”the sexiest job of 21st century”, anticipating the huge demand for data scientists. The knowledge base of data scientists and business analysts largely overlap. However, the deliverable of BA is improved business per- formance, whereas data scientists focus more on methods and insights from data. Improved business performance requires optimization to generate de- cisions andsoft skillsto implement the decisions. Finally, a few words on big data. Big data differentiates itself from reg- ular data sets by the so-called 3 V’s:volume,variety, andvelocity. A data Chapter1— Introduction 5 Box 1.1. From randomized trials to using already available data The traditional way to do scientiﬁc research in the medical and behavorial sci- ences is through (double-blind)randomized trials. This means that subjects (e.g., patients) have to be selected, and by a randomized procedure they are made part of the trial or part of the control group. It is called do', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5422, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 12, 'uble blind when the subject and the researcher are both not aware of who is in which group. This kind of research set-up allows for a relatively simple statistical analysis, but it is often hard to implement and very time-consuming. Nowadays, data can often be obtained from Electronic Health Records and other data sources. This eliminates the need for separate trials. However, there will be all kinds of statisticalbiasesin the data, making it harder to make a fair comparison between treatments. For example, patients of a certain age or hav- ing certain symptoms might get more-often a certain treatment. This calls for advanced statistical methods to eliminate these biases. These methods are usu- ally not taught in medical curricula, requiring the help of expert data scientists. set is considered to be ”big data” when the amount of data is too much to be stored in a regular database, when it lacks a homogeneous structure (i.e., free text instead of well-described ﬁelds), and/or when it is only avail- able real-time. Big data requires adapted storage systems and analysis tech- niques in order to exploit it. Big data now receives a lot of attention due to the speed at which data is col', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00'),
(5423, 115, 'syllabi/textbooks/oy7fGlArzTD5XKhePehMIvz3iCt7huPk6MRekHwV.pdf', 13, 'lected these days. As more and more devices and sensors automatically generating data are connected to the internet (theinternet of things) again, the amount of stored data doubles approximately every 3 years. However, most BA projects do not involve big data, but use with relatively small and structured data sets. It might have been the case that such a dataset had its origin in big data from whi', NULL, 300, '2025-12-08 11:58:00', '2025-12-08 11:58:00');

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
(1140, 239, '2', 'sdfsd', '23', 'ddfsd', '23', '3', 'dsfsdfsdfsd', 0, '2025-12-01 18:38:13', '2025-12-01 18:38:13'),
(1141, 278, '', '', '1', '', '', '', '', 0, '2025-12-04 19:45:02', '2025-12-04 19:45:02'),
(1142, 278, '', '', '2', '', '', '', '', 1, '2025-12-04 19:45:02', '2025-12-04 19:45:02'),
(1143, 278, '', '', '3', '', '', '', '', 2, '2025-12-04 19:45:02', '2025-12-04 19:45:02'),
(1144, 278, '', '', '4', '', '', '', '', 3, '2025-12-04 19:45:02', '2025-12-04 19:45:02'),
(1145, 282, '', 'Orientation', '1', '', '', '', '', 0, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1146, 282, '1', 'Main topic: An Introduction to Business Analytics\nQuiz # 1', '2', 'Explain the scope and significance of business analytics in modern organizations.', '1', '1', '', 1, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1147, 282, '', '', '3', '', '', '', '', 2, '2025-12-04 20:24:43', '2025-12-04 20:24:43'),
(1154, 283, '', '', '', '', '', '', '', 1, '2025-12-07 05:53:59', '2025-12-07 05:53:59'),
(1160, 273, '', '', '', '', '', '', '', 1, '2025-12-08 09:49:30', '2025-12-08 09:49:30'),
(1167, 286, '', 'Oreintation', '1', '', '', '', '', 0, '2025-12-08 10:22:43', '2025-12-08 10:22:43'),
(1168, 286, '', '', '', '', '', '', '', 1, '2025-12-08 10:22:43', '2025-12-08 10:22:43'),
(1169, 286, '', '', '', '', '', '', '', 2, '2025-12-08 10:22:43', '2025-12-08 10:22:43'),
(1170, 286, '', '', '', '', '', '', '', 3, '2025-12-08 10:22:43', '2025-12-08 10:22:43'),
(1171, 286, '', '', '', '', '', '', '', 4, '2025-12-08 10:22:43', '2025-12-08 10:22:43'),
(1172, 286, '', '', '', '', '', '', '', 5, '2025-12-08 10:22:43', '2025-12-08 10:22:43');

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
(231, 'MONTEALEGRE PAUL JELAN', '22-70787@g.batstate-u.edu.ph', '108434727613386660229', 'faculty', 'active', NULL, NULL, NULL, '2025-12-04 17:42:21', '2025-12-04 17:43:11', 'Professor 1', '22-70787'),
(233, 'ASIBAR PAUL JUSTINE REY', '22-73610@g.batstate-u.edu.ph', '115681611370892614613', 'faculty', 'active', NULL, NULL, NULL, '2025-12-04 18:06:58', '2025-12-04 18:33:43', 'Professor 1', '22-73610'),
(234, 'PEREYRA MATTHEW ALEN', '22-72684@g.batstate-u.edu.ph', '111721505571170932945', 'faculty', 'active', NULL, NULL, NULL, '2025-12-04 18:35:25', '2025-12-04 18:35:42', 'Professor 1', '22-72684'),
(235, 'PABLICO ADRIANE ALLEN', '22-77551@g.batstate-u.edu.ph', '105027007800844806186', 'faculty', 'active', NULL, NULL, NULL, '2025-12-04 19:04:51', '2025-12-04 19:46:14', 'Professor 1', '22-77551');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=919;

--
-- AUTO_INCREMENT for table `cdios`
--
ALTER TABLE `cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `chair_requests`
--
ALTER TABLE `chair_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `faculty_syllabus`
--
ALTER TABLE `faculty_syllabus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `syllabi`
--
ALTER TABLE `syllabi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `syllabus_assessment_mappings`
--
ALTER TABLE `syllabus_assessment_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1958;

--
-- AUTO_INCREMENT for table `syllabus_assessment_tasks`
--
ALTER TABLE `syllabus_assessment_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2265;

--
-- AUTO_INCREMENT for table `syllabus_cdios`
--
ALTER TABLE `syllabus_cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2400;

--
-- AUTO_INCREMENT for table `syllabus_comments`
--
ALTER TABLE `syllabus_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `syllabus_course_infos`
--
ALTER TABLE `syllabus_course_infos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `syllabus_course_policies`
--
ALTER TABLE `syllabus_course_policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=777;

--
-- AUTO_INCREMENT for table `syllabus_criteria`
--
ALTER TABLE `syllabus_criteria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3772;

--
-- AUTO_INCREMENT for table `syllabus_igas`
--
ALTER TABLE `syllabus_igas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=713;

--
-- AUTO_INCREMENT for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=830;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `syllabus_sdgs`
--
ALTER TABLE `syllabus_sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=613;

--
-- AUTO_INCREMENT for table `syllabus_sections`
--
ALTER TABLE `syllabus_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3219;

--
-- AUTO_INCREMENT for table `syllabus_submissions`
--
ALTER TABLE `syllabus_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `textbook_chunks`
--
ALTER TABLE `textbook_chunks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5424;

--
-- AUTO_INCREMENT for table `tla`
--
ALTER TABLE `tla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1173;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

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
