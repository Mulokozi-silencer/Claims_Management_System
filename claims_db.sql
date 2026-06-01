-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 01, 2026 at 01:04 AM
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
-- Database: `claims_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `claim_number` varchar(30) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `claim_type` enum('auto','health','property','life','travel','liability','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `incident_date` date NOT NULL,
  `incident_location` varchar(255) DEFAULT NULL,
  `claimed_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `approved_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected','settled','closed') DEFAULT 'draft',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `claim_number`, `claimant_id`, `assigned_to`, `claim_type`, `title`, `description`, `incident_date`, `incident_location`, `claimed_amount`, `approved_amount`, `status`, `priority`, `notes`, `rejection_reason`, `submitted_at`, `reviewed_at`, `settled_at`, `created_at`, `updated_at`) VALUES
(1, 'CLM-2025-00001', 4, 2, 'auto', 'Vehicle Collision on Highway 101', 'My vehicle was rear-ended at a red light on Highway 101. The other driver admitted fault. Significant rear bumper damage and whiplash injury sustained.', '2025-03-10', 'Highway 101, San Francisco, CA', 8500.00, NULL, 'under_review', 'high', NULL, NULL, '2026-05-28 01:24:16', NULL, NULL, '2026-05-27 22:24:16', '2026-05-27 22:24:16'),
(2, 'CLM-2025-00002', 4, 2, 'health', 'Emergency Surgery - Appendectomy', 'Required emergency appendectomy at St. Mary Hospital. Surgery was successful. Claiming for surgery, anesthesia, hospital stay, and medication costs.', '2025-02-22', 'St. Mary Hospital, Los Angeles, CA', 42000.00, 38500.00, 'approved', 'urgent', NULL, NULL, '2026-05-28 01:24:16', NULL, NULL, '2026-05-27 22:24:16', '2026-05-27 22:24:16'),
(3, 'CLM-2025-00003', 5, 3, 'property', 'House Fire - Kitchen Damage', 'A fire started in the kitchen due to a faulty electrical outlet. Significant damage to kitchen, dining room, and structural damage. All kitchen appliances destroyed.', '2025-03-01', '742 Evergreen Terrace, Springfield', 125000.00, NULL, 'under_review', 'urgent', NULL, NULL, '2026-05-28 01:24:16', '2026-05-28 02:36:07', NULL, '2026-05-27 22:24:16', '2026-05-27 23:36:07'),
(4, 'CLM-2025-00004', 5, NULL, 'travel', 'Flight Cancellation & Lost Luggage', 'Flight was cancelled due to airline fault. Spent 3 extra days in a hotel. Luggage was also lost containing electronics and clothing.', '2025-03-15', 'JFK Airport, New York', 3200.00, NULL, 'draft', 'low', NULL, NULL, NULL, NULL, NULL, '2026-05-27 22:24:16', '2026-05-27 22:24:16'),
(5, 'CLM-2025-00005', 4, 3, 'liability', 'Slip and Fall at Retail Store', 'Slipped on wet floor without warning sign at a retail store. Fractured wrist and required physio for 6 weeks. Claiming medical bills and lost wages.', '2025-01-18', 'MegaMart, Chicago, IL', 15750.00, 12000.00, 'settled', 'medium', NULL, NULL, '2026-05-28 01:24:16', NULL, NULL, '2026-05-27 22:24:16', '2026-05-27 22:24:16'),
(6, 'CLM-2026-00006', 6, NULL, 'auto', 'Bus Staff', 'It was on parking spot at NIT ground', '2026-05-28', 'Dar es Salaam', 1000.00, NULL, 'draft', 'high', 'No more', NULL, NULL, NULL, NULL, '2026-05-27 23:28:20', '2026-05-27 23:28:20'),
(7, 'CLM-2026-00001', 7, 8, 'auto', 'Tesla claims repair', 'I got hit by your company\'s car truck on 1st June, 2026 which was driven by Mr. Amos (36 years old) at Mango Park Police Station.', '2026-01-06', 'Dar es Salaam', 100000.00, NULL, 'under_review', 'high', 'My Tesla is brand new model G56-2026', NULL, '2026-05-31 18:34:13', '2026-06-01 01:38:42', NULL, '2026-05-31 22:34:13', '2026-05-31 22:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `claim_activities`
--

CREATE TABLE `claim_activities` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('comment','status_change','assignment','document_upload','amount_update') DEFAULT 'comment',
  `message` text NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claim_activities`
--

INSERT INTO `claim_activities` (`id`, `claim_id`, `user_id`, `activity_type`, `message`, `old_value`, `new_value`, `created_at`) VALUES
(1, 1, 4, 'comment', 'I have uploaded the police report and photos of the damage.', NULL, NULL, '2026-05-27 22:24:16'),
(2, 1, 2, 'status_change', 'Claim status updated to Under Review. Initial assessment started.', 'submitted', 'under_review', '2026-05-27 22:24:16'),
(3, 2, 2, 'status_change', 'Claim approved after reviewing all medical records.', 'under_review', 'approved', '2026-05-27 22:24:16'),
(4, 2, 2, 'amount_update', 'Approved amount set to $38,500. Some non-covered items deducted.', NULL, '38500.00', '2026-05-27 22:24:16'),
(5, 5, 3, 'status_change', 'Claim settled. Payment processed.', 'approved', 'settled', '2026-05-27 22:24:16'),
(6, 3, 6, 'status_change', 'Status changed from Submitted to Under Review.', 'submitted', 'under_review', '2026-05-27 23:36:07'),
(7, 7, 7, 'status_change', 'Claim submitted for review.', 'draft', 'submitted', '2026-05-31 22:34:13'),
(8, 7, 6, 'comment', 'we are working on it. Soon will back to you.', NULL, NULL, '2026-05-31 22:35:50'),
(9, 7, 6, 'comment', 'we are working on it. Soon will back to you.', NULL, NULL, '2026-05-31 22:36:07'),
(10, 7, 6, 'assignment', 'Claim assigned to adjuster ID 8.', NULL, NULL, '2026-05-31 22:38:36'),
(11, 7, 6, 'status_change', 'Status changed from Submitted to Under Review', 'submitted', 'under_review', '2026-05-31 22:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `claim_documents`
--

CREATE TABLE `claim_documents` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `document_type` enum('evidence','medical_report','police_report','invoice','receipt','photo','other') DEFAULT 'other',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_sequence`
--

CREATE TABLE `claim_sequence` (
  `year` int(11) NOT NULL,
  `seq` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claim_sequence`
--

INSERT INTO `claim_sequence` (`year`, `seq`) VALUES
(2026, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `claim_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 1, 'Claim Under Review', 'Your claim CLM-2025-00001 is now under review by our adjuster.', 0, '2026-05-27 22:24:16'),
(2, 4, 2, 'Claim Approved', 'Great news! Your claim CLM-2025-00002 has been approved for $38,500.', 0, '2026-05-27 22:24:16'),
(3, 2, 1, 'New Claim Assigned', 'Claim CLM-2025-00001 has been assigned to you for review.', 0, '2026-05-27 22:24:16'),
(4, 5, 3, 'Claim Status Updated', 'Your claim CLM-2025-00003 status changed to Under Review.', 0, '2026-05-27 23:36:07'),
(5, 1, 7, 'New Claim Submitted', 'Claim CLM-2026-00001 has been submitted and needs review.', 0, '2026-05-31 22:34:13'),
(6, 2, 7, 'New Claim Submitted', 'Claim CLM-2026-00001 has been submitted and needs review.', 0, '2026-05-31 22:34:13'),
(7, 6, 7, 'New Claim Submitted', 'Claim CLM-2026-00001 has been submitted and needs review.', 1, '2026-05-31 22:34:13'),
(8, 7, 7, 'Claim Status Updated', 'Your claim CLM-2026-00001 status changed to Under Review', 0, '2026-05-31 22:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','adjuster','claimant') NOT NULL DEFAULT 'claimant',
  `phone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `phone`, `avatar`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@claimsys.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+1-555-0100', NULL, 'active', NULL, '2026-05-27 22:24:16', '2026-05-27 23:46:31'),
(2, 'Sarah Mitchell', 'sarah.mitchell@claimsys.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adjuster', '+1-555-0101', NULL, 'active', NULL, '2026-05-27 22:24:16', '2026-05-27 22:24:16'),
(3, 'James Carter', 'james.carter@claimsys.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adjuster', '+1-555-0102', NULL, 'inactive', NULL, '2026-05-27 22:24:16', '2026-05-27 23:43:13'),
(4, 'Emily Johnson', 'emily@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'claimant', '+1-555-0201', NULL, 'inactive', NULL, '2026-05-27 22:24:16', '2026-05-27 23:43:07'),
(5, 'Robert Williams', 'robert@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'claimant', '+1-555-0202', NULL, 'active', NULL, '2026-05-27 22:24:16', '2026-05-27 23:46:18'),
(6, 'Mulokozi Geofrey', 'mulokoziwillium@gmail.com', '$2y$10$mOon/P/5iFAj/W8cbC9uZuhURjh.5GnYSazzWMTj0BtioBF/OocsK', 'admin', '+255655478996', NULL, 'active', '2026-06-01 01:34:56', '2026-05-27 23:24:33', '2026-05-31 22:34:56'),
(7, 'Justin', 'justin1@gmail.com', '$2y$10$CoY0Y29vqCy2RBqAxj2u5OvMIKMtmQ9Jr4pPhuqI2mH8v9cj8SWpC', 'claimant', NULL, NULL, 'active', '2026-06-01 01:30:23', '2026-05-28 06:26:07', '2026-05-31 22:30:23'),
(8, 'Winfrida Willium', 'winfrida@gmail.com', '$2y$10$.zjAtOwEo3cRKCLMxZAzOu1ivE8sPCm4OU8SHtm3zKdTYsWMcguJO', 'adjuster', '+255 768201869', NULL, 'active', '2026-06-01 01:39:04', '2026-05-31 22:38:05', '2026-05-31 22:39:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `claim_number` (`claim_number`),
  ADD KEY `claimant_id` (`claimant_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `claim_activities`
--
ALTER TABLE `claim_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `claim_documents`
--
ALTER TABLE `claim_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `claim_sequence`
--
ALTER TABLE `claim_sequence`
  ADD PRIMARY KEY (`year`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `claim_id` (`claim_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `claim_activities`
--
ALTER TABLE `claim_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `claim_documents`
--
ALTER TABLE `claim_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `claim_activities`
--
ALTER TABLE `claim_activities`
  ADD CONSTRAINT `claim_activities_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_activities_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_documents`
--
ALTER TABLE `claim_documents`
  ADD CONSTRAINT `claim_documents_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
