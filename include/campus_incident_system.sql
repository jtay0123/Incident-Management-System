-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 26, 2025 at 06:28 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmacy_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `admin_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_dob` date DEFAULT NULL,
  `admin_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_by` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_login_status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  KEY `registered_by` (`registered_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_username`, `admin_dob`, `admin_password`, `admin_email`, `registered_by`, `admin_login_status`) VALUES
('A001', 'admin1', '1995-04-10', '$argon2i$v=19$m=65536,t=4,p=1$L0w0RWZhTXIxREw2cG5zcA$ruSAS26tWxlHD0eYHlJcdomTVAboV4u+I71698u/Uic', 'admin1@mail.com', 'SA001', 'active'),
('A002', 'admin2', '2025-11-21', '$argon2i$v=19$m=65536,t=4,p=1$N21qVWVsTTguNHBIdmtZRA$1TnIjHd0G8DNCuwmmnP4dMvdcl6M4nZa9z03FaVYUxE', 'admin2@gmail.com', 'SA001', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `login_session`
--

DROP TABLE IF EXISTS `login_session`;
CREATE TABLE IF NOT EXISTS `login_session` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine_info`
--

DROP TABLE IF EXISTS `medicine_info`;
CREATE TABLE IF NOT EXISTS `medicine_info` (
  `medicine_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `medicine_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `medicine_price` decimal(10,2) DEFAULT NULL,
  `medicine_quantity` int DEFAULT NULL,
  `medicine_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_info`
--

INSERT INTO `medicine_info` (`medicine_id`, `medicine_name`, `medicine_price`, `medicine_quantity`, `medicine_description`) VALUES
('M001', 'Panadol', 5.00, 99, 'Pain relief tablet'),
('M002', 'Amoxicillin', 12.50, 99, 'Antibiotic 500mg'),
('M003', 'Vitamin C', 8.00, 100, 'Immune booster tablet');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `order_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_date` date DEFAULT NULL,
  `patient_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_id` enum('Pending','Approved','Rejected','Done') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `patient_id` (`patient_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `order_date`, `patient_id`, `staff_id`, `status_id`) VALUES
('O001', '2025-01-15', 'PT001', 'D001', 'Done'),
('O002', '2025-01-18', 'PT002', 'D002', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `order_approval`
--

DROP TABLE IF EXISTS `order_approval`;
CREATE TABLE IF NOT EXISTS `order_approval` (
  `approval_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approver_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `approval_status` enum('Approved','Rejected','Done','Pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approval_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`approval_id`),
  KEY `order_id` (`order_id`),
  KEY `approver_id` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_approval`
--

INSERT INTO `order_approval` (`approval_id`, `order_id`, `approver_id`, `approval_date`, `approval_status`, `approval_comment`) VALUES
('AP001', 'O001', 'P001', '2025-01-16 10:33:00', 'Approved', 'Ready for collection'),
('AP002', 'O002', 'P001', '2025-11-18 15:10:13', 'Approved', '1'),
('AP003', 'O002', 'P001', '2025-11-18 15:10:19', 'Rejected', '1'),
('AP004', 'O002', 'P001', '2025-11-18 15:21:33', 'Approved', ''),
('AP005', 'O002', 'P001', '2025-11-18 15:22:17', 'Done', '1'),
('AP006', 'O002', 'P001', '2025-11-18 15:22:31', 'Rejected', '1'),
('AP007', 'O002', 'P001', '2025-11-18 15:32:15', 'Rejected', '1'),
('AP008', 'O002', 'P001', '2025-11-18 15:46:22', 'Approved', ''),
('AP009', 'O001', 'P001', '2025-11-20 03:15:56', 'Approved', '1'),
('AP010', 'O002', 'P001', '2025-11-20 03:18:36', 'Approved', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
CREATE TABLE IF NOT EXISTS `order_details` (
  `order_detail_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `medicine_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `medicine_quantity` int DEFAULT NULL,
  `medicine_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `medicine_id` (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `medicine_id`, `medicine_quantity`, `medicine_price`) VALUES
('OD001', 'O001', 'M001', 2, 5.00),
('OD002', 'O001', 'M003', 1, 8.00),
('OD003', 'O002', 'M002', 1, 12.50),
('OD004', 'O002', 'M001', 1, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `patient_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `patient_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patient_date_of_birth` date DEFAULT NULL,
  `patient_phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patient_gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patient_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patient_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_by` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  KEY `registered_by` (`registered_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `patient_name`, `patient_date_of_birth`, `patient_phone`, `patient_gender`, `patient_email`, `patient_address`, `registered_by`) VALUES
('P001', 'sahur', '2025-11-05', '0123456789', 'Female', 'sahur@gmail.com', 'sadsd qe 2 1 12', 'A001'),
('PT001', 'John Doe', '1990-03-12', '0123456789', 'Male', 'john@example.com', '123 Main Street', 'A001'),
('PT002', 'Emily Tan', '1987-08-21', '0198765432', 'Female', 'emily@example.com', '88 Pine Road', 'A001');

-- --------------------------------------------------------

--
-- Table structure for table `pending_staff`
--

DROP TABLE IF EXISTS `pending_staff`;
CREATE TABLE IF NOT EXISTS `pending_staff` (
  `pending_id` int NOT NULL AUTO_INCREMENT,
  `staff_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `staff_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_dob` date DEFAULT NULL,
  `staff_specialization` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_role` enum('doctor','pharmacist') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_by` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pending_id`),
  UNIQUE KEY `staff_id` (`staff_id`),
  UNIQUE KEY `staff_email` (`staff_email`),
  KEY `registered_by` (`registered_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_dob` date DEFAULT NULL,
  `staff_specialization` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_role` enum('doctor','pharmacist') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_by` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `staff_status` enum('active','inactive','block') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  KEY `registered_by` (`registered_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_name`, `staff_dob`, `staff_specialization`, `staff_role`, `staff_phone`, `staff_email`, `staff_password`, `registered_by`, `staff_status`) VALUES
('D001', 'Dr. Lim Zhi Wei', '1980-02-12', 'General Medicine', 'doctor', '0182233445', 'lim@mail.com', '$argon2i$v=19$m=65536,t=4,p=1$MU5nRzNsU2d4TDdURklJUA$j0WpeWC9KU45kYJwX3t+oloYtHb/D4hJMYpPkd6rmoI', 'A001', 'active'),
('D002', 'Dr. Wong Jia Hui', '1977-09-30', 'Pediatrics', 'doctor', '0164455667', 'wong@mail.com', '$argon2i$v=19$m=65536,t=4,p=1$VVJDaVJLeFZna2I4QjNHdw$nuo4wVsyVHEyRwBZH6FRjzj91aqzhX5f6cJ+0reRGt4', 'A001', 'active'),
('D003', 'asljdhasd', '2025-11-11', 'AKSJDas', 'doctor', '23194733424', 'docdoc@gmail.com', '$argon2i$v=19$m=65536,t=4,p=1$QkpBbldjZDZmR1VKVDZoQg$etvT2dEgOL0IzL7L9Y4dZh9rEIc2jxkggqr3IxoISck', 'A001', 'active'),
('D004', 'asdakhdsgad', '2025-11-27', 'knee surgeon', 'doctor', '12837164917364', 'knee@gmail.com', '$2y$12$EZzLPdxN4KRnQkc63S6u/OoZcRTQRhrfXUWt1wjVLgg.ZjoZar83m', 'A001', ''),
('D005', 'jajaja', '2025-11-27', 'jajaja', 'doctor', '1908371934', 'jajaja@gmail.com', '$2y$12$mY/cw7i55MCy4HPdVacSOu3NAGguWboLTucM/LC3ysDW3tbeUaYS2', 'A001', ''),
('P001', 'Pharmacist Lee', '1985-11-15', 'Pharmacy', 'pharmacist', '0179988776', 'lee@mail.com', '$argon2i$v=19$m=65536,t=4,p=1$SlRVNnVYczZCcnBLSnluRg$4523W4mEVQgLNuaMIXzN9s7KEWPdBCxGGWm7uele05g', 'A001', 'active'),
('P002', 'Tung', '2025-11-12', 'drug', 'pharmacist', '0123456789', 'tung@gmail.com', '$2y$12$Bo//NLkQBsbzmDuWODmcZuNuxN9GTXL7luHse4JGzqbdJM/kcaVE.', 'A002', 'active'),
('P003', 'asdad', '2025-11-11', 'asdad', 'pharmacist', '1231231231', 'dadada@gmail.com', '$2y$12$UQPntr47SO6uNAG.LobhIe5z8R8UZkF7e01we2d7HQ9xyJbote49K', 'A002', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `super_admin`
--

DROP TABLE IF EXISTS `super_admin`;
CREATE TABLE IF NOT EXISTS `super_admin` (
  `super_admin_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `super_admin_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `super_admin_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `super_admin_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`super_admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `super_admin`
--

INSERT INTO `super_admin` (`super_admin_id`, `super_admin_username`, `super_admin_email`, `super_admin_password`) VALUES
('SA001', 'superadmin', 'superadmin@mail.com', '$argon2i$v=19$m=65536,t=4,p=1$bGZXWWNscXNvLkRBVnJ3Ug$dkcWE6Z5LXPzqbmSzpiosJdZXfIXlwVLBLc3U/kw7jE');

-- --------------------------------------------------------

--
-- Table structure for table `user_approval`
--

DROP TABLE IF EXISTS `user_approval`;
CREATE TABLE IF NOT EXISTS `user_approval` (
  `approval_id` int NOT NULL AUTO_INCREMENT,
  `approver_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_user_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`approval_id`),
  KEY `approver_id` (`approver_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_approval`
--

INSERT INTO `user_approval` (`approval_id`, `approver_id`, `approved_user_id`, `approval_date`, `status`) VALUES
(1, 'SA001', 'P003', '2025-11-27 02:12:59', 'approved');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `super_admin` (`super_admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `login_session`
--
ALTER TABLE `login_session`
  ADD CONSTRAINT `login_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_approval`
--
ALTER TABLE `order_approval`
  ADD CONSTRAINT `order_approval_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_approval_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine_info` (`medicine_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_approval`
--
ALTER TABLE `user_approval`
  ADD CONSTRAINT `user_approval_ibfk_1` FOREIGN KEY (`approver_id`) REFERENCES `super_admin` (`super_admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
