-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 03, 2021 at 11:45 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `touchandpay_lamata_epurse`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `userType` int(11) NOT NULL DEFAULT 0,
  `phoneVerified` int(11) NOT NULL DEFAULT 0,
  `emailVerified` int(11) NOT NULL DEFAULT 0,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(256) DEFAULT NULL,
  `publicKey` varchar(256) DEFAULT NULL,
  `authToken` varchar(1000) DEFAULT NULL,
  `emailVerificationToken` varchar(256) DEFAULT NULL,
  `priviledges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`priviledges`)),
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `phone`, `email`, `address`, `userType`, `phoneVerified`, `emailVerified`, `username`, `password`, `publicKey`, `authToken`, `emailVerificationToken`, `priviledges`, `dateCreated`, `dateUpdated`, `dateDeleted`) VALUES
(3, 'Tella Abdulrasheed', '08134738157', 'rasheed@touchandpay.me', 'Lagos', 0, 0, 0, 'rashtell', '4cf1736ddf7d42aba830831643cd6dd0c3f0cc12e85ab5688b5f999e98ec8d37', 'OzdyIQ2TzIYD', NULL, '6587455810569865541056566896116695967896597', '[0,1,2,3,4,5,6,7,8,9]', '1609628433', '1609628775', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `userType` int(11) NOT NULL DEFAULT 0,
  `phoneVerified` int(11) NOT NULL DEFAULT 0,
  `emailVerified` int(11) NOT NULL DEFAULT 0,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(256) DEFAULT NULL,
  `publicKey` varchar(256) DEFAULT NULL,
  `authToken` varchar(1000) DEFAULT NULL,
  `emailVerificationToken` varchar(256) DEFAULT NULL,
  `priviledges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`priviledges`)),
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `phone`, `email`, `address`, `userType`, `phoneVerified`, `emailVerified`, `username`, `password`, `publicKey`, `authToken`, `emailVerificationToken`, `priviledges`, `dateCreated`, `dateUpdated`, `dateDeleted`) VALUES
(4, 'EPURSE', '08134738157', 'info@epurse.com', 'Lagos', 1, 0, 0, NULL, NULL, 'jylP5Qedpr81cRJP2xzxUZBjIgStrAfZYEb6lsSB', NULL, NULL, '[]', '1609629663', '1609629663', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `entryPoint` varchar(100) DEFAULT NULL,
  `entryTime` varchar(15) DEFAULT NULL,
  `exitPoint` varchar(100) DEFAULT NULL,
  `exitTime` varchar(15) DEFAULT NULL,
  `cardType` varchar(50) NOT NULL,
  `cardSerial` varchar(50) NOT NULL,
  `busID` varchar(50) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `userType` int(11) NOT NULL DEFAULT 0,
  `phoneVerified` int(11) NOT NULL DEFAULT 0,
  `emailVerified` int(11) NOT NULL DEFAULT 0,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(256) DEFAULT NULL,
  `publicKey` varchar(256) DEFAULT NULL,
  `authToken` varchar(1000) DEFAULT NULL,
  `emailVerificationToken` varchar(256) DEFAULT NULL,
  `priviledges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`priviledges`)),
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `previousBalance` varchar(100) NOT NULL DEFAULT '0',
  `currentBalance` varchar(100) NOT NULL DEFAULT '0',
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
