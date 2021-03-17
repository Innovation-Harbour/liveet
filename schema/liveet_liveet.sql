-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2021 at 12:13 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `liveet_liveet`
--
CREATE DATABASE IF NOT EXISTS `liveet_liveet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `liveet_liveet`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

DROP TABLE IF EXISTS `admin_activity_log`;
CREATE TABLE `admin_activity_log` (
  `activity_log_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `activity_log_desc` int(11) NOT NULL,
  `activity_log_datetime` int(11) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin_feature`
--

DROP TABLE IF EXISTS `admin_feature`;
CREATE TABLE `admin_feature` (
  `admin_feature_id` int(11) NOT NULL,
  `feature_name` varchar(40) NOT NULL,
  `feature_url` text NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin_feature_user`
--

DROP TABLE IF EXISTS `admin_feature_user`;
CREATE TABLE `admin_feature_user` (
  `admin_feature_user_id` int(11) NOT NULL,
  `admin_feature_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `admin_user_id` int(11) NOT NULL,
  `admin_fullname` varchar(100) NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `admin_password` text NOT NULL,
  `admin_email` varchar(100) DEFAULT NULL,
  `admin_priviledges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`admin_priviledges`)),
  `admin_profile_picture` text DEFAULT NULL,
  `usertype` varchar(20) NOT NULL DEFAULT 'ADMIN',
  `public_key` text NOT NULL,
  `email_verified` varchar(20) NOT NULL DEFAULT 'NOT_VERIFIED',
  `email_verification_token` text DEFAULT NULL,
  `forgot_password_token` text DEFAULT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(256) NOT NULL,
  `event_code` varchar(256) NOT NULL,
  `event_desc` text NOT NULL,
  `event_multimedia` varchar(256) NOT NULL,
  `event_type` varchar(20) NOT NULL,
  `event_venue` varchar(256) NOT NULL,
  `event_date_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `event_payment_type` varchar(256) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_access`
--

DROP TABLE IF EXISTS `event_access`;
CREATE TABLE `event_access` (
  `event_access_id` int(11) NOT NULL,
  `event_access_code` varchar(256) NOT NULL,
  `event_ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_access_used_status` varchar(3) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_control`
--

DROP TABLE IF EXISTS `event_control`;
CREATE TABLE `event_control` (
  `event_control_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_can_invite` varchar(3) NOT NULL,
  `event_sale_stop_time` datetime NOT NULL,
  `event_can_transfer_ticket` varchar(3) NOT NULL,
  `event_can_call` varchar(3) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_ticket`
--

DROP TABLE IF EXISTS `event_ticket`;
CREATE TABLE `event_ticket` (
  `event_ticket_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `ticket_name` varchar(256) NOT NULL,
  `ticket_desc` text NOT NULL,
  `ticket_cost` decimal(10,0) NOT NULL,
  `ticket_population` decimal(10,0) NOT NULL,
  `ticket_discount` decimal(10,0) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_ticket_users`
--

DROP TABLE IF EXISTS `event_ticket_users`;
CREATE TABLE `event_ticket_users` (
  `event_ticket_user_id` int(11) NOT NULL,
  `event_ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_face_id` text NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_timeline`
--

DROP TABLE IF EXISTS `event_timeline`;
CREATE TABLE `event_timeline` (
  `timeline_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `timeline_desc` text NOT NULL,
  `timeline_datetime` datetime NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_user`
--

DROP TABLE IF EXISTS `event_user`;
CREATE TABLE `event_user` (
  `event_user_id` int(11) NOT NULL,
  `event_ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_face_id` text NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `organiser`
--

DROP TABLE IF EXISTS `organiser`;
CREATE TABLE `organiser` (
  `organiser_id` int(11) NOT NULL,
  `organiser_name` varchar(100) NOT NULL,
  `organiser_username` varchar(100) NOT NULL,
  `organiser_password` text NOT NULL,
  `organiser_phone` varchar(20) DEFAULT NULL,
  `organiser_email` varchar(100) DEFAULT NULL,
  `organiser_address` text DEFAULT NULL,
  `organiser_profile_picture` varchar(255) DEFAULT NULL,
  `usertype` varchar(20) NOT NULL DEFAULT 'ORGANIZER_ADMIN',
  `phone_verified` varchar(20) NOT NULL DEFAULT 'NOT_VERIFIED',
  `email_verified` varchar(20) NOT NULL DEFAULT 'NOT_VERIFIED',
  `email_verification_token` text DEFAULT NULL,
  `forgot_password_token` text DEFAULT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `organiser_activity_log`
--

DROP TABLE IF EXISTS `organiser_activity_log`;
CREATE TABLE `organiser_activity_log` (
  `activity_log_id` int(11) NOT NULL,
  `organiser_staff_id` int(11) NOT NULL,
  `activity_log_desc` text NOT NULL,
  `activity_log_datetime` datetime NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `organiser_staff`
--

DROP TABLE IF EXISTS `organiser_staff`;
CREATE TABLE `organiser_staff` (
  `organiser_staff_id` int(11) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `organiser_staff_name` varchar(100) NOT NULL,
  `organiser_staff_username` varchar(100) NOT NULL,
  `organiser_staff_password` text NOT NULL,
  `organiser_staff_phone` varchar(20) DEFAULT NULL,
  `organiser_staff_email` varchar(100) DEFAULT NULL,
  `organiser_staff_profile_picture` text DEFAULT NULL,
  `organiser_staff_priviledges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `usertype` varchar(20) NOT NULL DEFAULT 'ORGANIZER_STAFF',
  `phone_verified` varchar(20) NOT NULL DEFAULT 'NOT_VERIFIED',
  `email_verified` varchar(20) NOT NULL DEFAULT 'NOT_VERIFIED',
  `email_verification_token` text DEFAULT NULL,
  `forgot_password_token` text DEFAULT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `event_ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_desc` text NOT NULL,
  `payment_amount` decimal(10,0) NOT NULL,
  `payment_discount_amount` decimal(10,0) NOT NULL,
  `payment_datetime` datetime NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `timeline_media`
--

DROP TABLE IF EXISTS `timeline_media`;
CREATE TABLE `timeline_media` (
  `timeline_media_id` int(11) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `timeline_media` text NOT NULL,
  `media_type` varchar(20) NOT NULL,
  `media_datetime` datetime NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_fullname` text NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `user_password` text NOT NULL,
  `user_picture` text NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`activity_log_id`),
  ADD KEY `activity_user_id` (`admin_user_id`);

--
-- Indexes for table `admin_feature`
--
ALTER TABLE `admin_feature`
  ADD PRIMARY KEY (`admin_feature_id`);

--
-- Indexes for table `admin_feature_user`
--
ALTER TABLE `admin_feature_user`
  ADD PRIMARY KEY (`admin_feature_user_id`),
  ADD KEY `admin_feature_id` (`admin_feature_id`,`admin_user_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`admin_user_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organiser_id` (`organiser_id`);

--
-- Indexes for table `event_access`
--
ALTER TABLE `event_access`
  ADD PRIMARY KEY (`event_access_id`),
  ADD KEY `event_ticket_id` (`event_ticket_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_control`
--
ALTER TABLE `event_control`
  ADD PRIMARY KEY (`event_control_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_ticket`
--
ALTER TABLE `event_ticket`
  ADD PRIMARY KEY (`event_ticket_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_ticket_users`
--
ALTER TABLE `event_ticket_users`
  ADD PRIMARY KEY (`event_ticket_user_id`),
  ADD KEY `event_ticket_id` (`event_ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_timeline`
--
ALTER TABLE `event_timeline`
  ADD PRIMARY KEY (`timeline_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_user`
--
ALTER TABLE `event_user`
  ADD PRIMARY KEY (`event_user_id`),
  ADD KEY `event_ticket_id` (`event_ticket_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `organiser`
--
ALTER TABLE `organiser`
  ADD PRIMARY KEY (`organiser_id`);

--
-- Indexes for table `organiser_activity_log`
--
ALTER TABLE `organiser_activity_log`
  ADD PRIMARY KEY (`activity_log_id`),
  ADD KEY `activity_organiser_id` (`activity_log_id`,`organiser_staff_id`),
  ADD KEY `organiser_admin_id` (`organiser_staff_id`);

--
-- Indexes for table `organiser_staff`
--
ALTER TABLE `organiser_staff`
  ADD PRIMARY KEY (`organiser_staff_id`),
  ADD KEY `organiser_id` (`organiser_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `event_ticket_id` (`event_ticket_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `timeline_media`
--
ALTER TABLE `timeline_media`
  ADD PRIMARY KEY (`timeline_media_id`),
  ADD KEY `timeline_id` (`timeline_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `activity_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_feature`
--
ALTER TABLE `admin_feature`
  MODIFY `admin_feature_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_feature_user`
--
ALTER TABLE `admin_feature_user`
  MODIFY `admin_feature_user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `admin_user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_access`
--
ALTER TABLE `event_access`
  MODIFY `event_access_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_control`
--
ALTER TABLE `event_control`
  MODIFY `event_control_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_ticket`
--
ALTER TABLE `event_ticket`
  MODIFY `event_ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_ticket_users`
--
ALTER TABLE `event_ticket_users`
  MODIFY `event_ticket_user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_timeline`
--
ALTER TABLE `event_timeline`
  MODIFY `timeline_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_user`
--
ALTER TABLE `event_user`
  MODIFY `event_user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organiser`
--
ALTER TABLE `organiser`
  MODIFY `organiser_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organiser_activity_log`
--
ALTER TABLE `organiser_activity_log`
  MODIFY `activity_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organiser_staff`
--
ALTER TABLE `organiser_staff`
  MODIFY `organiser_staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timeline_media`
--
ALTER TABLE `timeline_media`
  MODIFY `timeline_media_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_user` (`admin_user_id`);

--
-- Constraints for table `admin_feature_user`
--
ALTER TABLE `admin_feature_user`
  ADD CONSTRAINT `admin_feature_user_ibfk_1` FOREIGN KEY (`admin_feature_id`) REFERENCES `admin_feature` (`admin_feature_id`),
  ADD CONSTRAINT `admin_feature_user_ibfk_2` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_user` (`admin_user_id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`organiser_id`) REFERENCES `organiser` (`organiser_id`);

--
-- Constraints for table `event_access`
--
ALTER TABLE `event_access`
  ADD CONSTRAINT `event_access_ibfk_1` FOREIGN KEY (`event_ticket_id`) REFERENCES `event_ticket` (`event_ticket_id`),
  ADD CONSTRAINT `event_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `event_control`
--
ALTER TABLE `event_control`
  ADD CONSTRAINT `event_control_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

--
-- Constraints for table `event_ticket`
--
ALTER TABLE `event_ticket`
  ADD CONSTRAINT `event_ticket_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

--
-- Constraints for table `event_ticket_users`
--
ALTER TABLE `event_ticket_users`
  ADD CONSTRAINT `event_ticket_users_ibfk_1` FOREIGN KEY (`event_ticket_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `event_ticket_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `event_timeline`
--
ALTER TABLE `event_timeline`
  ADD CONSTRAINT `event_timeline_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

--
-- Constraints for table `event_user`
--
ALTER TABLE `event_user`
  ADD CONSTRAINT `event_user_ibfk_1` FOREIGN KEY (`event_ticket_id`) REFERENCES `event_ticket` (`event_ticket_id`),
  ADD CONSTRAINT `event_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `organiser_activity_log`
--
ALTER TABLE `organiser_activity_log`
  ADD CONSTRAINT `organiser_activity_log_ibfk_1` FOREIGN KEY (`organiser_staff_id`) REFERENCES `organiser_staff` (`organiser_staff_id`);

--
-- Constraints for table `organiser_staff`
--
ALTER TABLE `organiser_staff`
  ADD CONSTRAINT `organiser_staff_ibfk_1` FOREIGN KEY (`organiser_id`) REFERENCES `organiser` (`organiser_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`event_ticket_id`) REFERENCES `event_ticket` (`event_ticket_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `timeline_media`
--
ALTER TABLE `timeline_media`
  ADD CONSTRAINT `timeline_media_ibfk_1` FOREIGN KEY (`timeline_id`) REFERENCES `event_timeline` (`timeline_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
