-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2021 at 03:00 AM
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
-- Database: `touchandpay_lamata_bus_locator`
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
(3, 'Tella Abdulrasheed', '08134738157', 'rasheed@touchandpay.me', 'Lagos', 0, 0, 0, 'rashtell', '4cf1736ddf7d42aba830831643cd6dd0c3f0cc12e85ab5688b5f999e98ec8d37', '1T4jwElhC2ea', NULL, '6587455810569865541056566896116695967896597', '[0,1,2,3,4,5,6,7,8,9]', '1609628433', '1610985342', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `organizationID` int(11) DEFAULT NULL,
  `busID` varchar(50) DEFAULT NULL,
  `lat` varchar(30) DEFAULT NULL,
  `lng` varchar(30) DEFAULT NULL,
  `issuerID` varchar(255) DEFAULT NULL,
  `issuerName` varchar(200) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `dateCreated` varchar(15) DEFAULT NULL,
  `dateUpdated` varchar(15) DEFAULT NULL,
  `dateDeleted` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(5, 'TAP', '08134738157', 'info@touchandpay.me', 'Lagos', 1, 0, 0, NULL, NULL, 'GWgnQQWIdnjLlnZoKvPGaTsGuURmhZzVUjAWorwp', NULL, NULL, '[]', '1610985391', '1611009962', NULL),
(6, 'LAMATA', '08134738158', 'helo@touchandpay.me', 'Lagos', 1, 0, 0, NULL, NULL, 'XpKuiFLTNWp7FpnLKTr1UEo55Ny0QJhER33alJa3', NULL, NULL, '[]', '1611019693', '1611019693', NULL);

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
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
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
