-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 05:32 PM
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
-- Database: `hostel_allocation`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_sessions`
--

CREATE TABLE `academic_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` enum('yes','no') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_sessions`
--

INSERT INTO `academic_sessions` (`id`, `session_name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(2, '2023/2024', '2023-01-01', '2024-12-30', 'no', '2025-10-10 15:57:03', '2025-10-10 15:57:03'),
(3, '2024/2025', '2024-01-01', '2025-12-30', 'yes', '2025-10-10 15:57:59', '2025-10-10 15:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$0gd0soNfidseT9gHosgXmO0iwoIOcda08VXqCOa..XwRzHs4qwGDC', 'System Administrator', 'admin@fedpolyayede.edu.ng', 'super_admin', '2025-10-18 09:53:11', '2025-07-09 10:29:08', '2025-10-18 09:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `academic_session` varchar(20) NOT NULL,
  `allocation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_approved` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `status` enum('active','cancelled','completed','confirmed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`id`, `student_id`, `hostel_id`, `room_id`, `academic_session`, `allocation_date`, `admin_approved`, `approved_by`, `approval_date`, `rejection_reason`, `payment_status`, `payment_method`, `payment_reference`, `amount_paid`, `payment_date`, `status`, `created_at`, `updated_at`) VALUES
(4, 7, 26, 882, '2024/2025', '2025-10-11 23:36:08', 'approved', NULL, NULL, NULL, 'paid', 'online_payment', '2563277327873287', NULL, '2025-10-12 00:17:55', 'active', '2025-10-11 23:36:08', '2025-10-12 00:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `hostel_name` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `total_capacity` int(11) NOT NULL,
  `available_spaces` int(11) NOT NULL,
  `price_per_session` decimal(10,2) NOT NULL,
  `facilities` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `hostel_name`, `gender`, `total_capacity`, `available_spaces`, `price_per_session`, `facilities`, `description`, `image_path`, `status`, `created_at`, `updated_at`) VALUES
(25, 'Hannah Villa Boys', 'male', 120, 120, 15000.00, 'N/A', 'N/A', NULL, 'active', '2025-10-11 23:13:30', '2025-10-18 12:18:28'),
(26, 'Hannah Villa Girls', 'female', 120, 119, 15000.00, 'N/A', 'N/A', NULL, 'active', '2025-10-11 23:14:26', '2025-10-11 23:36:08'),
(35, 'angola', 'male', 120, 120, 30000.00, '', '', NULL, 'active', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(36, 'angola 1', 'male', 75, 75, 35000.00, '', '', NULL, 'active', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(37, 'angola', 'male', 100, 100, 50000.00, '', '', NULL, 'active', '2025-10-18 12:14:54', '2025-10-18 12:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `occupied` int(11) DEFAULT 0,
  `room_type` enum('single','double','triple','quad') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `facilities` text DEFAULT NULL,
  `status` enum('available','maintenance','occupied') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `hostel_id`, `room_number`, `capacity`, `occupied`, `room_type`, `price`, `facilities`, `status`, `created_at`, `updated_at`) VALUES
(822, 25, '1', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-18 12:18:28'),
(823, 25, '2', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(824, 25, '3', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(825, 25, '4', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(826, 25, '5', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(827, 25, '6', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(828, 25, '7', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(829, 25, '8', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(830, 25, '9', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(831, 25, '10', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(832, 25, '11', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(833, 25, '12', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(834, 25, '13', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(835, 25, '14', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(836, 25, '15', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(837, 25, '16', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(838, 25, '17', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(839, 25, '18', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(840, 25, '19', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(841, 25, '20', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(842, 25, '21', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(843, 25, '22', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(844, 25, '23', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(845, 25, '24', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(846, 25, '25', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(847, 25, '26', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(848, 25, '27', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(849, 25, '28', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(850, 25, '29', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(851, 25, '30', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(852, 25, '31', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(853, 25, '32', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(854, 25, '33', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(855, 25, '34', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(856, 25, '35', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(857, 25, '36', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(858, 25, '37', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(859, 25, '38', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(860, 25, '39', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(861, 25, '40', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(862, 25, '41', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(863, 25, '42', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(864, 25, '43', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(865, 25, '44', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(866, 25, '45', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(867, 25, '46', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(868, 25, '47', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(869, 25, '48', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(870, 25, '49', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(871, 25, '50', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(872, 25, '51', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(873, 25, '52', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(874, 25, '53', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(875, 25, '54', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(876, 25, '55', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(877, 25, '56', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(878, 25, '57', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(879, 25, '58', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(880, 25, '59', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(881, 25, '60', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:13:30', '2025-10-11 23:13:30'),
(882, 26, '1', 2, 1, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:36:08'),
(883, 26, '2', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(884, 26, '3', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(885, 26, '4', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(886, 26, '5', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(887, 26, '6', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(888, 26, '7', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(889, 26, '8', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(890, 26, '9', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(891, 26, '10', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(892, 26, '11', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(893, 26, '12', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(894, 26, '13', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(895, 26, '14', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(896, 26, '15', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(897, 26, '16', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(898, 26, '17', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(899, 26, '18', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(900, 26, '19', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(901, 26, '20', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(902, 26, '21', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(903, 26, '22', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(904, 26, '23', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(905, 26, '24', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(906, 26, '25', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(907, 26, '26', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(908, 26, '27', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(909, 26, '28', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(910, 26, '29', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(911, 26, '30', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(912, 26, '31', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(913, 26, '32', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(914, 26, '33', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(915, 26, '34', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(916, 26, '35', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(917, 26, '36', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(918, 26, '37', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(919, 26, '38', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(920, 26, '39', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(921, 26, '40', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(922, 26, '41', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(923, 26, '42', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(924, 26, '43', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(925, 26, '44', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(926, 26, '45', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(927, 26, '46', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(928, 26, '47', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(929, 26, '48', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(930, 26, '49', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(931, 26, '50', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(932, 26, '51', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(933, 26, '52', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(934, 26, '53', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(935, 26, '54', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(936, 26, '55', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(937, 26, '56', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(938, 26, '57', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(939, 26, '58', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(940, 26, '59', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(941, 26, '60', 2, 0, 'double', 15000.00, 'Basic facilities', 'available', '2025-10-11 23:14:26', '2025-10-11 23:14:26'),
(1162, 35, '1', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1163, 35, '2', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1164, 35, '3', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1165, 35, '4', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1166, 35, '5', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1167, 35, '6', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1168, 35, '7', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1169, 35, '8', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1170, 35, '9', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1171, 35, '10', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1172, 35, '11', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1173, 35, '12', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1174, 35, '13', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1175, 35, '14', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1176, 35, '15', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1177, 35, '16', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1178, 35, '17', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1179, 35, '18', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1180, 35, '19', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1181, 35, '20', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1182, 35, '21', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1183, 35, '22', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1184, 35, '23', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1185, 35, '24', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1186, 35, '25', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1187, 35, '26', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1188, 35, '27', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1189, 35, '28', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1190, 35, '29', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1191, 35, '30', 4, 0, 'quad', 30000.00, 'Basic facilities', 'available', '2025-10-18 12:09:47', '2025-10-18 12:09:47'),
(1192, 36, '1', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1193, 36, '2', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1194, 36, '3', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1195, 36, '4', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1196, 36, '5', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1197, 36, '6', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1198, 36, '7', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1199, 36, '8', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1200, 36, '9', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1201, 36, '10', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1202, 36, '11', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1203, 36, '12', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1204, 36, '13', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1205, 36, '14', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1206, 36, '15', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1207, 36, '16', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1208, 36, '17', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1209, 36, '18', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1210, 36, '19', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1211, 36, '20', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1212, 36, '21', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1213, 36, '22', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1214, 36, '23', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1215, 36, '24', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1216, 36, '25', 3, 0, 'triple', 35000.00, 'Basic facilities', 'available', '2025-10-18 12:12:43', '2025-10-18 12:12:43'),
(1217, 37, '1', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1218, 37, '2', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1219, 37, '3', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1220, 37, '4', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1221, 37, '5', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1222, 37, '6', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1223, 37, '7', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1224, 37, '8', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1225, 37, '9', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1226, 37, '10', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1227, 37, '11', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1228, 37, '12', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1229, 37, '13', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1230, 37, '14', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1231, 37, '15', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1232, 37, '16', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1233, 37, '17', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1234, 37, '18', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1235, 37, '19', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1236, 37, '20', 2, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1237, 37, '21', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1238, 37, '22', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1239, 37, '23', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1240, 37, '24', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1241, 37, '25', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1242, 37, '26', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1243, 37, '27', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1244, 37, '28', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1245, 37, '29', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1246, 37, '30', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1247, 37, '31', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1248, 37, '32', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1249, 37, '33', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1250, 37, '34', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1251, 37, '35', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1252, 37, '36', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1253, 37, '37', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1254, 37, '38', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1255, 37, '39', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54'),
(1256, 37, '40', 3, 0, '', 50000.00, 'Basic facilities', 'available', '2025-10-18 12:14:54', '2025-10-18 12:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `matric_number` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `level` enum('ND 1','ND 2','HND 1','HND 2') NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rrr_last_digits` varchar(4) NOT NULL,
  `admission_year` year(4) NOT NULL,
  `study_mode` enum('full_time','part_time') DEFAULT 'full_time',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `applicant_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `matric_number`, `full_name`, `department`, `level`, `gender`, `phone`, `email`, `rrr_last_digits`, `admission_year`, `study_mode`, `created_at`, `updated_at`, `applicant_number`, `password`, `status`) VALUES
(7, NULL, 'zeezah beebah', 'Mass Communication', 'ND 1', 'female', '09115432678', 'zeezah@gmail.com', '9221', '2025', 'full_time', '2025-10-10 09:12:48', '2025-10-12 17:19:58', 'NDDPTAPLMAS109221', '$2y$10$yIxbNAXddKc/cFZ.KFkieuCuT0TspRijtXZhQf4wBGrr8Mg3XbqN6', 'active'),
(8, 'cs2024010001', 'ABAYOMI ISRAEL BLESSING', 'Computer Science', 'HND 1', 'male', '08075902159', 'abayomi1@gmail.com', '0001', '2024', 'full_time', '2025-10-12 02:47:53', '2025-10-12 17:19:40', NULL, '$2y$10$i2KKXei4whr4A4zbEZW1YO6LbkA5MQBulYbHYU1xhdGRyzjQIz7b.', 'active'),
(9, 'mc2025010002', 'kao sara', 'Mass Communication', 'HND 1', 'female', '08075902159', 'kaosara@gmail.com', '0002', '2023', 'full_time', '2025-10-12 17:04:36', '2025-10-12 17:24:01', NULL, '$2y$10$XOC87YMfxpW/txpqJv/EK.HLNHKSPGDuxsMdqwcrjvaEfh.CNew0q', 'active'),
(10, 'cs2023010018', 'Amole Martha', 'Computer Science', 'HND 1', 'female', '08032446390', 'amole1@gmail.com', '0018', '2023', 'full_time', '2025-10-12 17:12:20', '2025-10-12 17:18:51', NULL, '$2y$10$5IrFdV3VxoJ3iZnAXZpBv./Lf7PlLyxfm0xF0E.V8ugDLT8iMsPV2', 'active'),
(11, 'cs2023010020', 'Amoo Mutolib', 'Computer Science', 'HND 1', 'male', '08075902159', 'mustard1@gmail.com', '0020', '2023', 'full_time', '2025-10-12 17:18:06', '2025-10-12 17:18:06', NULL, '$2y$10$d3X0NdMyf5ujbhjJgsJ.5O8iTmOtJOegFJzorANexFnU9jRzPeC1O', 'active'),
(12, NULL, 'Oyewo Paul', 'Computer Science', 'ND 1', 'male', '09077724355', 'oyewopaul@gmail.com', '4322', '2025', 'full_time', '2025-10-13 09:46:58', '2025-10-13 09:46:58', 'NDDFTAPLCSC104322', '$2y$10$2mvjcmARBPy3brfUHyHsH.E1vAe8ZvMakEtjYPC5OzjQF8E5Rw5Kq', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room` (`hostel_id`,`room_number`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_number` (`matric_number`),
  ADD UNIQUE KEY `applicant_number` (`applicant_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1257;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocations`
--
ALTER TABLE `allocations`
  ADD CONSTRAINT `allocations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocations_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocations_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
