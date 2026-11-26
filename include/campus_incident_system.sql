-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 03:29 AM
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
-- Database: `campus_incident_system_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `keywords` text DEFAULT NULL,
  `default_priority` enum('Low','Medium','High','Emergency') NOT NULL DEFAULT 'Low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `department_id`, `status`, `created_at`, `keywords`, `default_priority`) VALUES
(1, 'Maintenance - Minor', 'Minor maintenance issues with low impact.', 3, 'Active', '2026-10-25 22:04:32', 'loose chair,minor damage,small crack,loose handle,minor furniture issue', 'Low'),
(2, 'Maintenance - General', 'General maintenance issues requiring repair.', 3, 'Active', '2026-10-25 22:04:32', 'broken light,faulty door,damaged desk,broken tap,fan not working', 'Medium'),
(3, 'Maintenance - Serious', 'Serious maintenance issues affecting facilities or safety.', 3, 'Active', '2026-10-25 22:04:32', 'major water leakage,ceiling damage,exposed wiring,large crack,electrical fault', 'High'),
(4, 'Maintenance - Emergency', 'Immediate maintenance hazards requiring urgent attention.', 3, 'Active', '2026-10-25 22:04:32', 'electrical fire,exposed live wire,structural collapse,immediate physical danger', 'Emergency'),
(5, 'Cleanliness - Minor', 'Minor cleanliness issues.', 5, 'Active', '2026-10-25 22:04:32', 'dusty table,small litter,minor stain', 'Low'),
(6, 'Cleanliness - General', 'General cleanliness issues requiring cleaning.', 5, 'Active', '2026-10-25 22:04:32', 'dirty classroom,overflowing bin,dirty toilet,food spill', 'Medium'),
(7, 'Cleanliness - Serious', 'Serious cleanliness issues affecting hygiene.', 5, 'Active', '2026-10-25 22:04:32', 'sewage leakage,severe contamination,large sewage spill,serious pest infestation', 'High'),
(8, 'Cleanliness - Emergency', 'Hazardous cleanliness incidents requiring immediate attention.', 5, 'Active', '2026-10-25 22:04:32', 'hazardous chemical spill,toxic chemical exposure,immediate health danger', 'Emergency'),
(9, 'IT Issues - Minor', 'Minor IT problems with limited impact.', 2, 'Active', '2026-10-25 22:04:32', 'slow computer,weak wifi,slow wifi,minor software issue', 'Low'),
(10, 'IT Issues - General', 'Common IT problems affecting normal use.', 2, 'Active', '2026-10-25 22:04:32', 'wifi,wifi disconnecting,printer not working,projector failure,login problem,computer malfunction', 'Medium'),
(11, 'IT Issues - Serious', 'Major IT disruptions affecting multiple users.', 2, 'Active', '2026-10-25 22:04:32', 'campus network outage,department network down,multiple systems unavailable,server outage', 'High'),
(12, 'IT Issues - Emergency', 'Critical IT failures affecting essential emergency operations.', 2, 'Active', '2026-10-25 22:04:32', 'critical emergency system network failure,emergency communication system down,critical infrastructure failure', 'Emergency'),
(13, 'Bullying and Harassment - Minor', 'Lower-severity interpersonal concerns requiring review.', 4, 'Active', '2026-10-25 22:04:32', 'isolated rude comment,minor disagreement,one-time teasing', 'Low'),
(14, 'Bullying and Harassment - General', 'Bullying or harassment concerns requiring follow-up.', 4, 'Active', '2026-10-25 22:04:32', 'repeated teasing,verbal bullying,offensive messages,insulting remarks', 'Medium'),
(15, 'Bullying and Harassment - Serious', 'Serious or repeated bullying and harassment incidents.', 4, 'Active', '2026-10-25 22:04:32', 'repeated harassment,stalking,threatening messages,serious bullying', 'High'),
(16, 'Bullying and Harassment - Emergency', 'Incidents involving immediate danger or physical harm.', 4, 'Active', '2026-10-25 22:04:32', 'ongoing physical assault,immediate threat of physical harm,immediate danger to life', 'Emergency');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `status`, `created_at`) VALUES
(1, 'Academic', 'Handles academic-related matters.', 'Active', '2026-09-14 16:28:53'),
(2, 'IT Department', 'Handles IT support and technical-related matters.', 'Active', '2026-09-14 16:28:53'),
(3, 'Maintenance Department', 'Handles maintenance related matters.', 'Active', '2026-09-14 16:28:53'),
(4, 'Security Department', 'Handles campus security and sensitive incidents.', 'Active', '2026-09-14 16:28:53'),
(5, 'Cleaning Department', 'Handles cleaning-related matters.', 'Active', '2026-09-14 16:28:53');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `incident_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `location_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Emergency') DEFAULT 'Medium',
  `status` enum('Submitted','Assigned','In Progress','Resolved','Closed') DEFAULT 'Submitted',
  `escalation_status` enum('None','Raised','Reviewed') DEFAULT 'None',
  `escalation_reason` text DEFAULT NULL,
  `reporter_approved` tinyint(1) DEFAULT 0,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`incident_id`, `reporter_id`, `category_id`, `department_id`, `assigned_staff_id`, `location_id`, `title`, `description`, `priority`, `status`, `escalation_status`, `escalation_reason`, `reporter_approved`, `submitted_at`, `resolved_at`, `updated_at`) VALUES
(2, 5, 10, 2, 11, 4, 'Wifi connection issue', 'Pls fix the wifi connection issue near classroom LR303 tks', 'Medium', 'Closed', 'None', NULL, 1, '2026-11-03 16:06:31', '2026-11-03 16:19:44', '2026-11-03 17:07:41'),
(3, 6, 2, 3, 15, 3, 'Broken table', 'Pls fix the broken table at cc lab', 'Medium', 'Assigned', 'None', NULL, NULL, '2026-11-03 16:55:11', NULL, '2026-11-03 16:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `incident_assignments`
--

CREATE TABLE `incident_assignments` (
  `assignment_id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `assignment_type` enum('Automatic','Manual','Reassignment') NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  `unassigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_assignments`
--

INSERT INTO `incident_assignments` (`assignment_id`, `incident_id`, `assigned_by`, `assigned_to`, `department_id`, `assignment_type`, `assigned_at`, `unassigned_at`) VALUES
(2, 2, NULL, 11, 2, 'Automatic', '2026-11-03 16:06:31', NULL),
(3, 3, NULL, 14, 3, 'Automatic', '2026-11-03 16:55:11', '2026-11-03 16:56:59'),
(4, 3, 2, 15, 3, 'Reassignment', '2026-11-03 16:56:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incident_attachments`
--

CREATE TABLE `incident_attachments` (
  `attachment_id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `attachment_type` enum('Evidence','Completion Proof') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_attachments`
--

INSERT INTO `incident_attachments` (`attachment_id`, `incident_id`, `uploaded_by`, `attachment_type`, `file_name`, `file_path`, `file_type`, `uploaded_at`) VALUES
(3, 2, 5, 'Evidence', '屏幕截图 154918.png', 'uploads/incidents/incident_2_61d60fb061a87ec8.png', 'image/png', '2026-11-03 16:06:31'),
(4, 2, 5, 'Evidence', '屏幕截图  155020.png', 'uploads/incidents/incident_2_54b5cb6d3e17d01a.png', 'image/png', '2026-11-03 16:06:31'),
(5, 2, 11, 'Completion Proof', '屏幕截图 155223.png', 'uploads/completion_proof/completion_2_98dcedeb55740761.png', 'image/png', '2026-11-03 16:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `incident_updates`
--

CREATE TABLE `incident_updates` (
  `update_id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('Submitted','Assigned','In Progress','Resolved','Closed') NOT NULL,
  `update_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_updates`
--

INSERT INTO `incident_updates` (`update_id`, `incident_id`, `user_id`, `status`, `update_message`, `created_at`) VALUES
(1, 2, 11, 'In Progress', 'Start work on this', '2026-11-03 16:16:20'),
(2, 2, 11, 'Resolved', 'Work done. Network stable', '2026-11-03 16:19:44'),
(3, 3, 2, 'Assigned', 'Incident reassigned to Maintenance2.', '2026-11-03 16:56:59'),
(4, 2, 2, 'Closed', 'Incident closed by Admin.', '2026-11-03 17:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(150) NOT NULL,
  `building_name` varchar(100) NOT NULL,
  `floor_number` varchar(20) DEFAULT NULL,
  `qr_code_value` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`, `building_name`, `floor_number`, `qr_code_value`, `status`, `created_at`) VALUES
(1, 'Lobby', 'Block B', 'LG', 'LOC-6E73F1', 'Active', '2026-09-30 14:34:35'),
(3, 'CC LAB', 'Block A', 'L1', 'LOC-1B9950', 'Active', '2026-09-30 15:29:23'),
(4, 'LR-303', 'Block A', 'L3', 'LOC-64E93B', 'Active', '2026-09-30 15:29:34'),
(5, 'LR-505', 'Block A', 'L5', 'LOC-9CCAD8', 'Active', '2026-09-30 15:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `public_holidays`
--

CREATE TABLE `public_holidays` (
  `holiday_id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `holiday_name` varchar(150) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_holidays`
--

INSERT INTO `public_holidays` (`holiday_id`, `holiday_date`, `holiday_name`, `status`, `created_at`) VALUES
(1, '2026-09-16', 'Malaysia Day', 'Active', '2026-09-30 23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `status` enum('Active','Logged Out','Expired') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `user_id`, `login_time`, `last_activity`, `logout_time`, `status`) VALUES
(1, 1, '2026-11-03 15:15:19', '2026-11-03 15:15:19', '2026-11-03 15:18:49', 'Logged Out'),
(2, 2, '2026-11-03 15:18:57', '2026-11-03 15:18:57', '2026-11-03 15:20:44', 'Logged Out'),
(3, 2, '2026-11-03 15:23:54', '2026-11-03 15:23:54', '2026-11-03 15:24:42', 'Logged Out'),
(4, 5, '2026-11-03 15:42:22', '2026-11-03 15:42:24', '2026-11-03 15:58:41', 'Logged Out'),
(5, 2, '2026-11-03 15:58:49', '2026-11-03 15:59:09', '2026-11-03 15:59:11', 'Logged Out'),
(6, 2, '2026-11-03 16:04:20', '2026-11-03 16:04:22', '2026-11-03 16:04:28', 'Logged Out'),
(7, 5, '2026-11-03 16:04:44', '2026-11-03 16:09:49', '2026-11-03 16:10:48', 'Logged Out'),
(8, 11, '2026-11-03 16:12:22', '2026-11-03 16:15:15', '2026-11-03 16:20:33', 'Logged Out'),
(9, 5, '2026-11-03 16:20:47', '2026-11-03 16:21:08', '2026-11-03 16:32:56', 'Logged Out'),
(10, 1, '2026-11-03 16:33:09', '2026-11-03 16:34:52', '2026-11-03 16:35:23', 'Logged Out'),
(11, 2, '2026-11-03 16:35:32', '2026-11-03 16:39:54', '2026-11-03 16:41:22', 'Logged Out'),
(12, 2, '2026-11-03 16:43:32', '2026-11-03 16:49:07', '2026-11-03 16:52:10', 'Logged Out'),
(13, 6, '2026-11-03 16:52:24', '2026-11-03 16:52:27', '2026-11-03 16:55:19', 'Logged Out'),
(14, 2, '2026-11-03 16:55:27', '2026-11-03 16:57:21', '2026-11-03 16:58:06', 'Logged Out'),
(15, 5, '2026-11-03 16:58:42', '2026-11-03 16:58:52', '2026-11-03 16:58:53', 'Logged Out'),
(16, 2, '2026-11-03 16:59:00', '2026-11-03 17:08:50', '2026-11-03 17:08:54', 'Logged Out'),
(17, 2, '2026-11-11 14:51:07', '2026-11-11 14:53:43', '2026-11-11 14:53:52', 'Logged Out'),
(18, 2, '2026-11-18 00:15:44', '2026-11-18 00:15:55', '2026-11-18 00:15:58', 'Logged Out'),
(19, 2, '2026-11-25 14:44:09', '2026-11-25 14:49:33', '2026-11-25 14:50:06', 'Logged Out'),
(20, 2, '2026-11-25 14:51:00', '2026-11-25 15:09:53', '2026-11-25 15:09:55', 'Logged Out');

-- --------------------------------------------------------

--
-- Table structure for table `sla_records`
--

CREATE TABLE `sla_records` (
  `sla_id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `priority` enum('Low','Medium','High','Emergency') NOT NULL,
  `start_time` datetime NOT NULL,
  `due_time` datetime NOT NULL,
  `completed_time` datetime DEFAULT NULL,
  `sla_status` enum('On Track','Overdue','Completed') DEFAULT 'On Track',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sla_records`
--

INSERT INTO `sla_records` (`sla_id`, `incident_id`, `priority`, `start_time`, `due_time`, `completed_time`, `sla_status`, `created_at`) VALUES
(1, 2, 'Medium', '2026-11-03 16:06:31', '2026-11-06 14:00:00', '2026-11-03 16:19:44', 'Completed', '2026-11-03 16:06:31'),
(2, 3, 'Medium', '2026-11-03 16:55:11', '2026-11-06 14:00:00', NULL, 'Overdue', '2026-11-03 16:55:11');

-- --------------------------------------------------------

--
-- Table structure for table `sla_settings`
--

CREATE TABLE `sla_settings` (
  `setting_id` int(11) NOT NULL,
  `priority` enum('Low','Medium','High','Emergency') NOT NULL,
  `sla_hours` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sla_settings`
--

INSERT INTO `sla_settings` (`setting_id`, `priority`, `sla_hours`, `updated_at`, `updated_by`) VALUES
(1, 'Low', 48, '2026-11-02 12:51:13', 8),
(2, 'Medium', 24, '2026-11-02 12:51:13', 8),
(3, 'High', 8, '2026-11-02 12:51:13', 8),
(4, 'Emergency', 4, '2026-11-02 12:52:37', 8);

-- --------------------------------------------------------

--
-- Table structure for table `staff_leave`
--

CREATE TABLE `staff_leave` (
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Admin','Response Staff','Reporter') NOT NULL,
  `user_type` enum('Student','Lecturer','Staff','Maintenance','IT Support','Cleaning','Security') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `account_status` enum('Pending','Active','Inactive','Suspended') DEFAULT 'Pending',
  `availability_status` enum('Available','Offline') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `user_type`, `department_id`, `account_status`, `availability_status`, `created_at`, `updated_at`, `must_change_password`) VALUES
(1, 'Super Admin', 'superadmin@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Super Admin', 'Staff', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 07:13:46', 0),
(2, 'Admin1', 'admin1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Admin', 'Staff', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(3, 'Admin2', 'admin2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Admin', 'Staff', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(4, 'Admin3', 'admin3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Admin', 'Staff', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(5, 'Student1', 'student1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Reporter', 'Student', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-25 14:51:08', 0),
(6, 'Student2', 'student2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Reporter', 'Student', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(7, 'Student3', 'student3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Reporter', 'Student', NULL, 'Active', NULL, '2026-11-03 07:13:46', '2026-11-03 15:18:07', 1),
(8, 'Lecturer1', 'lecturer1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Lecturer', 1, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(9, 'Lecturer2', 'lecturer2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Lecturer', 1, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(10, 'Lecturer3', 'lecturer3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Lecturer', 1, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 15:18:07', 1),
(11, 'IT Support1', 'itsupport1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'IT Support', 2, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-25 14:51:05', 0),
(12, 'IT Support2', 'itsupport2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'IT Support', 2, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 15:18:07', 0),
(13, 'IT Support3', 'itsupport3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'IT Support', 2, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:43:17', 1),
(14, 'Maintenance1', 'maintenance1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Maintenance', 3, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:20', 0),
(15, 'Maintenance2', 'maintenance2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Maintenance', 3, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:20', 0),
(16, 'Maintenance3', 'maintenance3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Maintenance', 3, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:21', 1),
(17, 'Security1', 'security1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Security', 4, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:43:04', 0),
(18, 'Security2', 'security2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Security', 4, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:43:04', 0),
(19, 'Security3', 'security3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Security', 4, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:43:04', 1),
(20, 'Cleaning1', 'cleaning1@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Cleaning', 5, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:21', 0),
(21, 'Cleaning3', 'cleaning2@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Cleaning', 5, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:21', 0),
(22, 'Cleaning3', 'cleaning3@campus.edu.my', '$2y$12$WLvD4IBJealwj2dYxIJIzeFveIzNUdaKTPrag1bKPWPIllDDgNNsi', 'Response Staff', 'Cleaning', 5, 'Active', 'Available', '2026-11-03 07:13:46', '2026-11-03 16:42:21', 1),
(23, 'Admin4', 'admin4@campus.edu.my', '$argon2i$v=19$m=65536,t=4,p=1$WEIxd2ladWhFcm1qRUF3ZQ$guiOeIow5IJPp6CqIc4S1ERm1kxG4sTAePx6JvyG+dU', 'Admin', 'Staff', NULL, 'Inactive', NULL, '2026-11-03 16:34:18', '2026-11-03 16:34:48', 1),
(24, 'ITsupport4', 'itsupport4@campus.edu.my', '$argon2i$v=19$m=65536,t=4,p=1$OUFVM29qRlY3aVRDV280Vw$OHuMJ6m/K49qQAc4ROVH5zAQzH5A8R9JQt4vODJsd8Y', 'Response Staff', 'IT Support', 2, 'Active', 'Available', '2026-11-03 16:44:30', '2026-11-25 14:49:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `working_hours`
--

CREATE TABLE `working_hours` (
  `working_hour_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_working_day` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `working_hours`
--

INSERT INTO `working_hours` (`working_hour_id`, `day_of_week`, `start_time`, `end_time`, `is_working_day`) VALUES
(1, 'Monday', '08:00:00', '17:00:00', 1),
(2, 'Tuesday', '08:00:00', '15:30:00', 1),
(3, 'Wednesday', '08:00:00', '17:00:00', 1),
(4, 'Thursday', '08:00:00', '17:00:00', 1),
(5, 'Friday', '08:00:00', '17:00:00', 1),
(6, 'Saturday', NULL, NULL, 0),
(7, 'Sunday', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `incident_assignments`
--
ALTER TABLE `incident_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `incident_id` (`incident_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `incident_attachments`
--
ALTER TABLE `incident_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `incident_id` (`incident_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `incident_updates`
--
ALTER TABLE `incident_updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `incident_id` (`incident_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `qr_code_value` (`qr_code_value`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reset_user` (`user_id`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reset_request_user_time` (`user_id`,`requested_at`);

--
-- Indexes for table `public_holidays`
--
ALTER TABLE `public_holidays`
  ADD PRIMARY KEY (`holiday_id`),
  ADD UNIQUE KEY `unique_holiday_date` (`holiday_date`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sla_records`
--
ALTER TABLE `sla_records`
  ADD PRIMARY KEY (`sla_id`),
  ADD KEY `incident_id` (`incident_id`);

--
-- Indexes for table `sla_settings`
--
ALTER TABLE `sla_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `unique_sla_priority` (`priority`),
  ADD KEY `fk_sla_settings_user` (`updated_by`);

--
-- Indexes for table `staff_leave`
--
ALTER TABLE `staff_leave`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_department` (`department_id`);

--
-- Indexes for table `working_hours`
--
ALTER TABLE `working_hours`
  ADD PRIMARY KEY (`working_hour_id`),
  ADD UNIQUE KEY `unique_day_of_week` (`day_of_week`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `incident_assignments`
--
ALTER TABLE `incident_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `incident_attachments`
--
ALTER TABLE `incident_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incident_updates`
--
ALTER TABLE `incident_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `public_holidays`
--
ALTER TABLE `public_holidays`
  MODIFY `holiday_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sla_records`
--
ALTER TABLE `sla_records`
  MODIFY `sla_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sla_settings`
--
ALTER TABLE `sla_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_leave`
--
ALTER TABLE `staff_leave`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `working_hours`
--
ALTER TABLE `working_hours`
  MODIFY `working_hour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `incidents_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `incidents_ibfk_4` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incidents_ibfk_5` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);

--
-- Constraints for table `incident_assignments`
--
ALTER TABLE `incident_assignments`
  ADD CONSTRAINT `incident_assignments_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incident_assignments_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incident_assignments_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incident_assignments_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `incident_attachments`
--
ALTER TABLE `incident_attachments`
  ADD CONSTRAINT `incident_attachments_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incident_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `incident_updates`
--
ALTER TABLE `incident_updates`
  ADD CONSTRAINT `incident_updates_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incident_updates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD CONSTRAINT `fk_reset_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `fk_reset_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sla_records`
--
ALTER TABLE `sla_records`
  ADD CONSTRAINT `sla_records_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE;

--
-- Constraints for table `sla_settings`
--
ALTER TABLE `sla_settings`
  ADD CONSTRAINT `fk_sla_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `staff_leave`
--
ALTER TABLE `staff_leave`
  ADD CONSTRAINT `fk_staff_leave_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
