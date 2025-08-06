-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 05:23 AM
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

--
-- Dumping data for table `cdios`
--

INSERT INTO `cdios` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Disciplinary Knowledge & Reasoning', 'Knowled geofunderlyingmathematicsandsciences, coreengineeringfundamentalknowledge, advanced\r\n engineering fundamental knowledge, methods and tools', '2025-07-22 04:46:37', '2025-07-27 09:19:05'),
(2, 'Personal and Professional Skills & Attributes', 'Analytical reasoning and problemsolving; experimentation , investigation and knowledge discovery;\r\n system thinking; attitudes, thoughts and learning; ethics, equity and other responsibilities', '2025-07-22 04:46:46', '2025-07-22 04:46:46'),
(3, 'Interpersonal Skills: Teamwork & Communication', 'Teamwork, communications, communication in a foreign language', '2025-07-22 04:46:58', '2025-07-22 04:46:58'),
(4, 'Conceiving, Designing, Implementing & Operating Systems', 'External, societal and environmental context, enterprise and business context, conceiving, systems\r\n engineering and management, designing, implementing, operatin', '2025-07-22 04:47:20', '2025-07-22 04:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `units_lec` int(11) NOT NULL,
  `units_lab` int(11) NOT NULL DEFAULT 0,
  `total_units` int(11) NOT NULL,
  `contact_hours_lec` int(11) DEFAULT NULL,
  `contact_hours_lab` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `code`, `title`, `units_lec`, `units_lab`, `total_units`, `contact_hours_lec`, `contact_hours_lab`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'BAT 403', 'Fundamentals of Enterprise Data Management', 3, 3, 6, 2, 3, 'Thecourseisdesignedtointroducestudentstothefundamentalsofdatabasemanagement systems, enterprisedata\r\n managementusingdatawarehouse(DWorDWH),whichcanbeusedfor furtherdatamining, reportinganddata\r\n analysis purposes. It describes various activities involved in data mining tasks like data anomaly detection\r\n (outlier/change/deviationdetection), dataassociation rule learning (dependencymodelling), data clustering, data\r\n classification, data regressionand summarization. This course also introduces students to formalizedmeans of\r\n organizingandstoringstructuredandunstructureddata inanorganization. It describeshowEnterpriseContent\r\n Management (ECM) canmanagecorporate informationeffectively throughsimplifyingstorage, security, version\r\n control.processrouting,andretention.Thecoursealsodescribestechniquestousepredictiveanalyticsfordetection\r\n of fraudalent activities.', '2025-07-22 08:57:33', '2025-07-22 12:06:09'),
(8, 1, 'sdfsdfd', 'dsf', 3, 3, 6, 3, 3, NULL, '2025-07-27 10:08:17', '2025-07-27 10:08:17');

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
(1, 'College of Informatics and Computing Science', 'CICS', '2025-07-19 08:28:49', '2025-07-19 08:28:49');

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
(1, 'mission', 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', '2025-07-20 14:10:56', '2025-07-20 14:11:21'),
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
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `igas`
--

INSERT INTO `igas` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Knowledge Competence', 'Demonstrateamasteryof thefundamentalknowledgeandskillsrequiredfor functioningeffectivelyasa\r\n professional in thediscipline, andanability to integrate andapply themeffectively topractice in the\r\n workplace.', '2025-07-22 04:40:36', '2025-07-22 04:40:36'),
(2, 'Creativity and Innovation', 'Experimentwithnewapproaches,challengeexistingknowledgeboundariesanddesignnovelsolutionsto\r\n solve problems.', '2025-07-22 04:40:46', '2025-07-22 04:40:46'),
(3, 'Critical and Systems', 'dentify,define,anddealwithcomplexproblemspertinent tothefutureprofessionalpracticeordailylife\r\n through logical, analytical and critical thinking.', '2025-07-22 04:45:05', '2025-07-22 04:45:05'),
(4, 'Communication', 'Communicateeffectively(bothorallyandinwriting)withawiderangeofaudiences, acrossarangeof\r\n professional and personal contexts, in English and Pilipino.', '2025-07-22 04:45:13', '2025-07-22 04:45:13'),
(5, 'Lifelong Learning', 'Identify own learning needs for professional or personal development; demonstrate an eagerness to take up \r\nopportunities for learning new things as well as the ability to learn effectively on their own.', '2025-07-22 04:45:49', '2025-07-22 04:45:49'),
(6, 'Leadership, teamwork, and Interpersonal Skills', 'Functioneffectivelybothas a leader andas amember of a team;motivateand leada teamtowork\r\n towardsgoal;workcollaborativelywithother teammembers;aswellasconnectandinteract sociallyand\r\n effectively with diverse culture.', '2025-07-22 04:46:00', '2025-07-22 04:46:00'),
(7, 'Global Outlook', 'Demonstrateanawarenessandunderstandingofglobalissuesandwillingnesstowork, interacteffectively\r\n and show sensitivity to cultural diversity.', '2025-07-22 04:46:12', '2025-07-22 04:46:12'),
(8, 'Social and National Responsibility', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', '2025-07-22 04:46:21', '2025-07-22 04:46:21'),
(9, 'Social and National Responsibility', 'Demonstrateanawarenessoftheirsocialandnationalresponsibility;engageinactivitiesthatcontributeto\r\n the betterment of the society; and behave ethicallyand responsibly in social, professional andwork\r\n environments.', '2025-07-22 04:46:21', '2025-07-22 04:46:21');

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
(50, 'ILO1', 'Explain data management concepts and criticality of data availability in order to make reliable business \r\ndecisions.', 1, '2025-07-28 11:50:09', '2025-07-28 11:50:09', 1),
(51, 'ILO2', 'Demonstrate understanding of  business intelligence including the importance of data gathering, data \r\nstoring, data analyzing and accessing data.', 2, '2025-07-28 11:50:21', '2025-07-28 11:50:21', 1),
(52, 'ILO3', 'Describe where to look for data in an organization and create required reports', 3, '2025-07-28 11:50:28', '2025-07-28 11:50:28', 1),
(53, 'ILO4', 'Perform high-quality tasks required by the organization in particular, and the industry in general', 4, '2025-07-28 11:50:36', '2025-07-28 11:50:36', 1);

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
(66, '2025_07_28_223651_create_tla_so_table', 21);

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
(6, 1, 6, 'dfd', 'fdf', 'dfdf', '2025-07-28 10:37:45', '2025-07-28 10:37:45'),
(7, 1, 6, 'dddd', 'dd', 'dd', '2025-07-28 10:39:24', '2025-07-28 10:39:24');

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
(1, 'Envisioning', 'Est ablishalinkbetweenlong-termgoalsandandimmediateactions,andmotivatepeopletotakeactionby\r\n harnessing their deep aspirations.', '2025-07-22 04:37:43', '2025-07-27 09:18:51'),
(2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social and cultural structures in the context of sustainable\r\n development, andchallengespeople toexamineandquestiontheunderlyingassumptions that influence\r\n their world views by having them reflect on unsustainable practices.', '2025-07-22 04:37:59', '2025-07-22 04:38:22'),
(3, 'Systematic thinking', 'Recognise that the whole is more than the sum of its parts, and it is a better way to understand and manage \r\ncomplex situations.', '2025-07-22 04:38:50', '2025-07-22 04:38:50'),
(4, 'Building Partnership', 'Promote dialogue andnegotiation, learning towork together, so as to strengthenownership of and\r\n commitment to sustainable action through education and learning.', '2025-07-22 04:39:05', '2025-07-22 04:39:05'),
(5, 'Participation in Making Decisions', 'Empower oneself and others through involvement in joint analysis, planning and control of local decisions.', '2025-07-22 04:39:26', '2025-07-22 04:39:26');

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
('B9Zv9cHfAiCNcLeU3jJGeG6RGKCyJQrZc104dmXZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieVJ0eDg0N1A5elY5c3Z6cEdqdjZRV2Y0ZG1mOHFCZ0M3cTk0eXFZZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2Rhc2hib2FyZCI7fXM6MTM6ImlzX3N1cGVyYWRtaW4iO2I6MTt9', 1754115550),
('fM4jpz1kPEVALFDsg0zF14KN6P3vGYjLxFeSSu0u', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYkpXeGlXc3IxVnU4WFdiVnRkNDZQTzFWN2RvcUNCa1dZYmFHc1hKOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1754132464),
('OBcnpKxiyrESitUw7KCuCRuztZqQzBcfKNU5Wfs0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0M5VnplZ1N1UllFN0x3dEJKZXhsdW10VDE0VnNsemhsMENiaFpTTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdXBlcmFkbWluL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1754116904);

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
(23, 'SO1', 'Abilitytoanalyzeacdddddddomplexcomputingproblemandtoapplyprinciplesofcomputingandotherrelevant\r\n disciplines to identify solutions', 1, '2025-07-28 11:50:52', '2025-07-28 11:51:26'),
(24, 'SO2', 'Abilitytodesign, implement, andevaluateacomputing-basedsolutiontomeet agivensetofcomputing\r\n requirements in the context of the program’s discipline.', 2, '2025-07-28 11:50:58', '2025-07-28 11:50:58'),
(25, 'SO3', 'Ability to communicate effectively in a variety of professional contexts.', 3, '2025-07-28 11:51:03', '2025-07-28 11:51:03'),
(26, 'SO4', 'Ability to recognize professional responsibilities andmake informed judgments incomputingpractice\r\n based on legal and ethical principles.', 4, '2025-07-28 11:51:07', '2025-07-28 11:51:07'),
(27, 'SO5', 'Abilityto functioneffectivelyasamemberor leaderofa teamengagedinactivitiesappropriate to the\r\n program’s discipline.', 5, '2025-07-28 11:51:11', '2025-07-28 11:51:11'),
(28, 'SO6', 'Ability to identifyand analyze user needs and to take theminto account in the selection, creation,\r\n integration, evaluation and administration of computing-based systems.', 6, '2025-07-28 11:51:16', '2025-07-28 11:51:16');

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
  `mission` text NOT NULL,
  `vision` text NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `year_level` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `textbook_file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabi`
--

INSERT INTO `syllabi` (`id`, `faculty_id`, `program_id`, `course_id`, `title`, `mission`, `vision`, `academic_year`, `semester`, `year_level`, `created_at`, `updated_at`, `textbook_file_path`) VALUES
(52, 10, 6, 1, 'Introduction to Computing', 'A university committed to producing leaders by providing a 21st century learning environment through innovations\r\nin education, multidisciplinary research, and community and industry partnerships in order to nurture the spirit of\r\nnationhood, propel the national economy and engage the world for sustainable development.', 'A premier national university that develops leaders in the global knowledge economy', '2025-2026', '1st Semester', '1st Year', '2025-07-29 23:07:25', '2025-07-29 23:07:25', NULL);

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
(119, 52, 'ILO1', 'Explain data management concepts and criticality of data availability in order to make reliable business \r\ndecisions.', 0, '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(120, 52, 'ILO2', 'Demonstrate understanding of  business intelligence including the importance of data gathering, data \r\nstoring, data analyzing and accessing data.', 0, '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(121, 52, 'ILO3', 'Describe where to look for data in an organization and create required reports', 0, '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(122, 52, 'ILO4', 'Perform high-quality tasks required by the organization in particular, and the industry in general', 0, '2025-07-29 23:07:25', '2025-07-29 23:07:25');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus_sdg`
--

INSERT INTO `syllabus_sdg` (`id`, `syllabus_id`, `sdg_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(25, 52, 2, 'Critical Thinking and Reflection', 'Examine economic, environmental, social and cultural structures in the context of sustainable\r\n development, andchallengespeople toexamineandquestiontheunderlyingassumptions that influence\r\n their world views by having them reflect on unsustainable practices.', '2025-07-29 23:09:02', '2025-07-29 23:09:02');

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
(199, 52, 'SO1', 1, 'Abilitytoanalyzeacdddddddomplexcomputingproblemandtoapplyprinciplesofcomputingandotherrelevant\r\n disciplines to identify solutions', '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(200, 52, 'SO2', 2, 'Abilitytodesign, implement, andevaluateacomputing-basedsolutiontomeet agivensetofcomputing\r\n requirements in the context of the program’s discipline.', '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(201, 52, 'SO3', 3, 'Ability to communicate effectively in a variety of professional contexts.', '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(202, 52, 'SO4', 4, 'Ability to recognize professional responsibilities andmake informed judgments incomputingpractice\r\n based on legal and ethical principles.', '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(203, 52, 'SO5', 5, 'Abilityto functioneffectivelyasamemberor leaderofa teamengagedinactivitiesappropriate to the\r\n program’s discipline.', '2025-07-29 23:07:25', '2025-07-29 23:07:25'),
(204, 52, 'SO6', 6, 'Ability to identifyand analyze user needs and to take theminto account in the selection, creation,\r\n integration, evaluation and administration of computing-based systems.', '2025-07-29 23:07:25', '2025-07-29 23:07:25');

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

--
-- Dumping data for table `tla`
--

INSERT INTO `tla` (`id`, `syllabus_id`, `ch`, `topic`, `wks`, `outcomes`, `ilo`, `so`, `delivery`, `created_at`, `updated_at`) VALUES
(429, 52, '', 'Orientation & Introduction', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(430, 52, '1', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(431, 52, '2', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(432, 52, '3', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(433, 52, '4', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(434, 52, '', 'Midterm Examination', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(435, 52, '5', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(436, 52, '6', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(437, 52, '7', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(438, 52, '8', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21'),
(439, 52, '9', '', '', '', '', '', '', '2025-07-29 23:11:21', '2025-07-29 23:11:21');

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
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `employee_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `role`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `department_id`, `designation`, `employee_code`) VALUES
(6, 'MONTEALEGRE PAUL JELAN', '22-70787@g.batstate-u.edu.ph', '108434727613386660229', 'admin', 'active', NULL, NULL, NULL, '2025-07-25 18:50:55', '2025-07-25 18:51:27', 1, 'Assistant Professor IV, BSIT, MSIT', '22-5534'),
(10, 'PABLICO ADRIANE ALLEN', '22-77551@g.batstate-u.edu.ph', NULL, 'faculty', 'active', NULL, NULL, NULL, '2025-07-29 23:06:26', '2025-07-29 23:06:54', 1, 'Assistant Professor IV, BSIT, MSIT', '22-5534');

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `intended_learning_outcomes_code_unique` (`code`),
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
-- Indexes for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_ilos_syllabus_id_foreign` (`syllabus_id`);

--
-- Indexes for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_sdg_syllabus_id_foreign` (`syllabus_id`),
  ADD KEY `syllabus_sdg_sdg_id_foreign` (`sdg_id`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cdios`
--
ALTER TABLE `cdios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `intended_learning_outcomes`
--
ALTER TABLE `intended_learning_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `so`
--
ALTER TABLE `so`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_outcomes`
--
ALTER TABLE `student_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `syllabi`
--
ALTER TABLE `syllabi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `syllabus_sos`
--
ALTER TABLE `syllabus_sos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `syllabus_textbooks`
--
ALTER TABLE `syllabus_textbooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tla`
--
ALTER TABLE `tla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=440;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `syllabus_ilos`
--
ALTER TABLE `syllabus_ilos`
  ADD CONSTRAINT `syllabus_ilos_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus_sdg`
--
ALTER TABLE `syllabus_sdg`
  ADD CONSTRAINT `syllabus_sdg_sdg_id_foreign` FOREIGN KEY (`sdg_id`) REFERENCES `sdgs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabus_sdg_syllabus_id_foreign` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabi` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
