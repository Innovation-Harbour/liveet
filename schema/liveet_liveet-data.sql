-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2021 at 01:22 PM
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_feature`
--

INSERT INTO `admin_feature` (`admin_feature_id`, `feature_name`, `feature_url`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Handcrafted Fresh Soap', 'http://diamond.org', '1615921220', '1615939466', NULL),
(2, 'Generic Rubber Cheese', 'http://tracey.net', '1615939430', '1615939430', NULL),
(3, 'Handcrafted Granite Bike', 'https://griffin.org', '1615939433', '1615939433', NULL);

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

--
-- Dumping data for table `admin_feature_user`
--

INSERT INTO `admin_feature_user` (`admin_feature_user_id`, `admin_feature_id`, `admin_user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 3, 9, '1615939799', '1615939799', NULL),
(4, 3, 9, '1615939871', '1615939871', NULL),
(5, 3, 9, '1615939877', '1615939877', NULL),
(6, 3, 9, '1615939879', '1615939879', NULL),
(7, 3, 9, '1615939880', '1615939880', NULL);

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

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`admin_user_id`, `admin_fullname`, `admin_username`, `admin_password`, `admin_email`, `admin_priviledges`, `admin_profile_picture`, `usertype`, `public_key`, `email_verified`, `email_verification_token`, `forgot_password_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'Marjorie Lueilwitz', 'rashtell', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Freddy_Johns@yahoo.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', 'assets/images/1615850740.svg', 'ADMIN', 'BH7BLpqMlvGh', 'NOT_VERIFIED', '95848687875976439886585655113664757596874', NULL, '1615397560', '1616086174', NULL),
(8, 'Genevieve Ortiz', 'rashtell1', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Georgianna73@yahoo.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', '857565581238636588459751049510738105610664667', 'NOT_VERIFIED', NULL, NULL, '1615848381', '1615848381', NULL),
(9, 'Wendell Shanahan', 'Bernadine19', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Rasheed86@yahoo.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'V0gCSWHf9dTt', 'NOT_VERIFIED', '96968685555666776573545755844686545961055', NULL, '1615849640', '1615849640', NULL),
(10, 'Myrtle Ward', 'Eli.Lemke33', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Beth82@gmail.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'HpmtbXME2Nzp', 'NOT_VERIFIED', '454756966585854456894559115966105768888784', NULL, '1615849895', '1615849895', NULL),
(11, 'Guillermo Ebert', 'Green.Yundt48', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Jermey_Langworth@gmail.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'r6uTc32kQuUQ', 'NOT_VERIFIED', '484495744648745888856688985864999687410115', NULL, '1615850109', '1615850109', NULL),
(12, 'Lynn Kessler', 'Louie.Torp', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Ronny.Langworth@yahoo.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'MqpvSqNh2TuR', 'NOT_VERIFIED', '668847665585456886746858106754576478548710', NULL, '1615850172', '1615850172', NULL),
(13, 'Theodore Bogisich', 'Karson_Kessler2', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Malvina32@hotmail.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', '341DcwdRx8VB', 'NOT_VERIFIED', '599494758849768645410985883810857468865855', NULL, '1615850216', '1615850216', NULL),
(14, 'Jaime Hyatt', 'Briana.Bode34', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Jaquan70@yahoo.com', '[\"EVENT\",\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'CFlXoqD0RYDA', 'NOT_VERIFIED', '38565748798894575116695955697574856774865', NULL, '1615850262', '1615850262', NULL),
(15, 'Nick Howe', 'Yvette_Rice68', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', 'Amira.Howe55@gmail.com', '[\"ORGANISER\",\"ACTIVITY_LOG\",\"REPORT\",\"ADMIN\"]', NULL, 'ADMIN', 'cuUQazwSplE1', 'NOT_VERIFIED', '684579599856568766106510587364896685578754', NULL, '1616086079', '1616086148', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(256) NOT NULL,
  `event_code` varchar(256) NOT NULL,
  `event_desc` text DEFAULT NULL,
  `event_multimedia` varchar(256) DEFAULT NULL,
  `event_type` varchar(20) NOT NULL,
  `event_venue` varchar(256) DEFAULT NULL,
  `event_date_time` varchar(20) DEFAULT NULL,
  `event_payment_type` varchar(256) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `event_name`, `event_code`, `event_desc`, `event_multimedia`, `event_type`, `event_venue`, `event_date_time`, `event_payment_type`, `organiser_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'Gorgeous Cotton Tuna', '602JEC', 'Ad perspiciatis quis doloribus.', 'http://placeimg.com/640/480', 'PUBLIC', '12260 Lucio Courts', '1615998619164', 'FREE', 10, '1616004922', '1616149334', '1616149334'),
(7, 'Awesome Concrete Pants', '5JVW5H', 'Dolorum explicabo quis quam nostrum dolorum qui explicabo.', 'http://placeimg.com/640/480', 'PUBLIC', '19157 Nolan Center', '1615998619164', 'FREE', 10, '1616005401', '1616005401', NULL),
(8, 'Practical Soft Computer', 'EG8LT8', 'Inventore consequatur suscipit harum odit unde ea veritatis placeat.', 'http://placeimg.com/640/480', 'PUBLIC', '794 Jovanny View', '1615998619164', 'FREE', 10, '1616005735', '1616005735', NULL),
(9, 'Intelligent Frozen Car', 'J0FAK0', 'Qui molestiae incidunt excepturi porro.', 'http://placeimg.com/640/480', 'PUBLIC', '1721 Arnaldo Drive', '1615998619164', 'FREE', 10, '1616005961', '1616005961', NULL),
(10, 'Intelligent Rubber Fish', 'I656AC', 'Aut hic voluptates earum laborum.', 'http://placeimg.com/640/480', 'PUBLIC', '8191 Ola Junctions', '1615998619164', 'FREE', 10, '1616006058', '1616006058', NULL),
(11, 'Intelligent Rubber Car', 'P0YQFE', 'Possimus atque temporibus aliquam repudiandae aut quas.', 'http://placeimg.com/640/480', 'PUBLIC', '4978 Donato Avenue', '1615998619164', 'FREE', 10, '1616006127', '1616006127', NULL),
(12, 'Intelligent Soft Pants', 'ZJRM9T', 'Illum vitae fugit consequuntur illum repellendus autem molestiae ad iusto.', 'http://placeimg.com/640/480', 'PUBLIC', '95561 Smith Well', '1615998619164', 'FREE', 10, '1616006347', '1616006347', NULL),
(13, 'Generic Granite Tuna', '3STPPN', 'Voluptatem incidunt magnam rerum eveniet quibusdam.', 'http://placeimg.com/640/480', 'PUBLIC', '949 Romaguera Heights', '1615998619164', 'FREE', 10, '1616006382', '1616006382', NULL),
(14, 'Small Steel Mouse', 'I6ESX3', 'Voluptates iure recusandae ullam reiciendis neque.', 'http://placeimg.com/640/480', 'PUBLIC', '52307 Fritsch Lodge', '1615998619164', 'FREE', 10, '1616007483', '1616007483', NULL),
(15, 'Awesome Plastic Table', 'ZH4TZ2', 'Impedit distinctio corrupti vel impedit sed quo aspernatur nam.', 'http://placeimg.com/640/480', 'PUBLIC', '956 Vandervort Point', '1615998619164', 'FREE', 10, '1616007516', '1616007516', NULL),
(16, 'Tasty Metal Salad', '38OPTV', 'Accusantium est aut quidem nam quia provident voluptatem officia sit.', 'http://placeimg.com/640/480', 'PUBLIC', '242 Wintheiser Forges', '1615998619164', 'FREE', 10, '1616007559', '1616007559', NULL),
(17, 'Incredible Wooden Fish', 'HYLB3J', 'Ea illo dolore possimus aut.', 'http://placeimg.com/640/480', 'PUBLIC', '653 Seamus Port', '1615998619164', 'FREE', 10, '1616007589', '1616007589', NULL),
(18, 'Awesome Granite Keyboard', 'R0PVEX', 'Rerum eos quos laborum.', 'http://placeimg.com/640/480', 'PUBLIC', '048 Effertz Valley', '1615998619164', 'FREE', 10, '1616007619', '1616007619', NULL),
(19, 'Handmade Steel Keyboard', 'UGJL34', 'Ratione laudantium aliquid.', 'http://placeimg.com/640/480', 'PUBLIC', '5641 Champlin Spring', '1615998619164', 'FREE', 10, '1616007679', '1616007679', NULL),
(20, 'Awesome Concrete Pants', '2M8S42', 'Laboriosam quo minima at aut.', 'http://placeimg.com/640/480', 'PUBLIC', '469 Schoen Ports', '1615998619164', 'FREE', 10, '1616007700', '1616007700', NULL),
(21, 'Intelligent Plastic Mouse', 'Y345PR', 'Molestiae deleniti cumque mollitia laudantium itaque facere sed.', 'http://placeimg.com/640/480', 'PUBLIC', '86317 Schowalter Parkway', '1615998619164', 'FREE', 10, '1616007733', '1616007733', NULL),
(22, 'Fantastic Metal Bacon', '8X8N1K', 'Beatae autem officiis excepturi sunt.', 'http://placeimg.com/640/480', 'PUBLIC', '2196 Kuhlman Keys', '1615998619164', 'FREE', 10, '1616007781', '1616007781', NULL),
(23, 'Generic Metal Soap', 'MG48U1', 'Possimus id et quis illo.', 'http://placeimg.com/640/480', 'PUBLIC', '337 Casimer Manors', '1615998619164', 'FREE', 10, '1616007998', '1616007998', NULL),
(24, 'Sleek Fresh Fish', 'DKO069', 'Reiciendis officiis nemo quis doloremque rerum natus.', 'http://placeimg.com/640/480', 'PUBLIC', '7747 Jamir Key', '1615998619164', 'FREE', 10, '1616008164', '1616008164', NULL),
(25, 'Handcrafted Concrete Soap', 'WT4SYQ', 'Voluptatem minus praesentium nostrum.', 'http://placeimg.com/640/480', 'PUBLIC', '408 Lura Glen', '1615998619164', 'FREE', 10, '1616008241', '1616008241', NULL),
(26, 'Small Concrete Sausages', '7A4FBN', 'Quisquam accusantium eveniet voluptatibus aut.', 'http://placeimg.com/640/480', 'PUBLIC', '478 Erdman Lane', '1615998619164', 'FREE', 10, '1616008272', '1616008272', NULL),
(27, 'Gorgeous Granite Pants', 'YUH3RF', 'Et voluptatum iusto libero impedit omnis temporibus atque.', 'http://placeimg.com/640/480', 'PUBLIC', '78950 Sam Flats', '1615998619164', 'FREE', 10, '1616008325', '1616008325', NULL),
(28, 'Unbranded Fresh Shirt', '3F7ZPX', 'Similique velit voluptatem fuga blanditiis accusamus.', 'http://placeimg.com/640/480', 'PUBLIC', '1094 Reese Shoals', '1615998619164', 'FREE', 10, '1616008347', '1616008347', NULL),
(29, 'Licensed Plastic Salad', 'E6UBS4', 'Eum nobis reiciendis error quasi sunt commodi est necessitatibus nesciunt.', 'http://placeimg.com/640/480', 'PUBLIC', '91167 Howell Plaza', '1615998619164', 'FREE', 10, '1616008447', '1616008447', NULL),
(30, 'Intelligent Concrete Computer', '3AJF72', 'Consequatur et delectus consectetur beatae.', 'http://placeimg.com/640/480', 'PUBLIC', '92456 Keith Valleys', '1615998619164', 'FREE', 10, '1616008522', '1616008522', NULL),
(31, 'Practical Soft Tuna', 'EGEPVW', 'Vel quia ratione officiis.', 'http://placeimg.com/640/480', 'PUBLIC', '90273 Julian Square', '1615998619164', 'FREE', 10, '1616008526', '1616008526', NULL),
(32, 'Small Concrete Pants', '51SAV2', 'Dolores necessitatibus iusto aut voluptas qui.', 'http://placeimg.com/640/480', 'PUBLIC', '36048 Stehr Manors', '1615998619164', 'FREE', 10, '1616109065', '1616109065', NULL),
(33, 'Generic Fresh Shirt', '7IWS5G', 'Qui ipsam consequuntur iure voluptas.', 'http://placeimg.com/640/480', 'PUBLIC', '75495 Doyle Plains', '1615998619164', 'FREE', 10, '1616109074', '1616109074', NULL);

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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_control`
--

DROP TABLE IF EXISTS `event_control`;
CREATE TABLE `event_control` (
  `event_control_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_can_invite` varchar(20) DEFAULT NULL,
  `event_sale_stop_time` varchar(20) DEFAULT NULL,
  `event_can_transfer_ticket` varchar(20) DEFAULT NULL,
  `event_can_recall` varchar(20) DEFAULT NULL,
  `created_at` varchar(15) NOT NULL,
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event_control`
--

INSERT INTO `event_control` (`event_control_id`, `event_id`, `event_can_invite`, `event_sale_stop_time`, `event_can_transfer_ticket`, `event_can_recall`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 8, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616005735', '1616005735', NULL),
(2, 12, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616006347', '1616006347', NULL),
(3, 13, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616006382', '1616006382', NULL),
(4, 14, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616007483', '1616007483', NULL),
(5, 15, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616007516', '1616007516', NULL),
(6, 16, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616007559', '1616007559', NULL),
(7, 17, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616007589', '1616007589', NULL),
(8, 18, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN', '1616007619', '1616007619', NULL),
(9, 19, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616007679', '1616007679', NULL),
(10, 20, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616007700', '1616007700', NULL),
(11, 28, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616008347', '1616008347', NULL),
(12, 29, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616008447', '1616008447', NULL),
(13, 30, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616008522', '1616008522', NULL),
(14, 31, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616008526', '1616008526', NULL),
(15, 6, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616084388', '1616109218', NULL),
(16, 32, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616109065', '1616109065', NULL),
(17, 33, 'CAN_INVITE', '1615998615164', 'CAN_TRANSFER', 'CAN_RECALL', '1616109074', '1616109074', NULL);

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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event_ticket`
--

INSERT INTO `event_ticket` (`event_ticket_id`, `event_id`, `ticket_name`, `ticket_desc`, `ticket_cost`, `ticket_population`, `ticket_discount`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 'REGULAR', 'Qui hic eligendi.', '1000', '10000', '0', '1616005401', '1616115208', NULL),
(2, 8, 'REGULAR', 'Deserunt iure perspiciatis nobis ducimus quam debitis corporis.', '1000', '10000', '0', '1616005735', '1616005735', NULL),
(3, 12, 'REGULAR', 'Autem adipisci qui adipisci rerum tenetur.', '1000', '10000', '0', '1616006347', '1616006347', NULL),
(4, 13, 'REGULAR', 'Doloremque dolores placeat repudiandae voluptas rerum.', '1000', '10000', '0', '1616006382', '1616006382', NULL),
(5, 14, 'REGULAR', 'Laborum est temporibus eum commodi.', '1000', '10000', '0', '1616007483', '1616007483', NULL),
(6, 15, 'REGULAR', 'A aut et.', '1000', '10000', '0', '1616007516', '1616007516', NULL),
(7, 16, 'REGULAR', 'Consequatur odio id impedit sit.', '1000', '10000', '0', '1616007559', '1616007559', NULL),
(8, 17, 'REGULAR', 'Ut at sed quia placeat.', '1000', '10000', '0', '1616007589', '1616007589', NULL),
(9, 18, 'REGULAR', 'Rerum iure aut.', '1000', '10000', '0', '1616007619', '1616007619', NULL),
(10, 19, 'REGULAR', 'Et sapiente inventore qui ea cupiditate doloremque aut.', '1000', '10000', '0', '1616007679', '1616007679', NULL),
(11, 20, 'REGULAR', 'Quidem dolorem ad a nesciunt.', '1000', '10000', '0', '1616007700', '1616007700', NULL),
(12, 28, 'REGULAR', 'Exercitationem ipsa est qui.', '1000', '10000', '0', '1616008347', '1616008347', NULL),
(13, 29, 'REGULAR', 'Aut aut maiores dolorem in vero voluptatem vel.', '1000', '10000', '0', '1616008447', '1616008447', NULL),
(14, 30, 'REGULAR', 'Natus reiciendis excepturi tempora praesentium et aperiam quibusdam.', '1000', '10000', '0', '1616008522', '1616008522', NULL),
(15, 30, 'VIP', 'Tempora magni sit recusandae ipsum voluptatem.', '10000', '1000', '0', '1616008522', '1616008522', NULL),
(16, 30, 'VVIP', 'Nam tempora autem voluptas autem non reprehenderit.', '100000', '5000', '0', '1616008522', '1616008522', NULL),
(17, 31, 'REGULAR', 'Id id praesentium veniam sunt quis.', '1000', '10000', '0', '1616008526', '1616008526', NULL),
(18, 31, 'VIP', 'Id nesciunt soluta dolorem sequi et veritatis possimus.', '10000', '1000', '0', '1616008526', '1616008526', NULL),
(19, 31, 'VVIP', 'Aut est est laborum natus in.', '100000', '5000', '0', '1616008526', '1616008526', NULL),
(20, 6, 'REGULAR', 'Cumque ut ullam perferendis.', '1000', '10000', '0', '1616084388', '1616109218', NULL),
(21, 6, 'VIP', 'Magnam explicabo velit velit ducimus expedita officia.', '10000', '1000', '0', '1616084388', '1616109218', NULL),
(22, 6, 'VVIP', 'Praesentium aperiam omnis autem accusamus quo.', '100000', '5000', '0', '1616084388', '1616109218', NULL),
(23, 6, 'REGULAR', 'Enim excepturi ea consectetur amet molestiae beatae exercitationem iste.', '1000', '10000', '0', '1616084499', '1616084499', NULL),
(24, 6, 'VIP', 'Provident necessitatibus ab dignissimos et minima omnis quisquam et.', '10000', '1000', '0', '1616084499', '1616084499', NULL),
(25, 6, 'VVIP', 'Eaque iste ratione eius velit sit id qui.', '100000', '5000', '0', '1616084499', '1616084499', NULL),
(26, 6, 'REGULAR', 'Consequatur voluptas quisquam aut qui.', '1000', '10000', '0', '1616084501', '1616084501', NULL),
(27, 6, 'VIP', 'Sed hic velit aliquid eveniet nemo alias iusto odit facere.', '10000', '1000', '0', '1616084501', '1616084501', NULL),
(28, 6, 'VVIP', 'Vel cumque natus non qui dolorem minima unde praesentium.', '100000', '5000', '0', '1616084501', '1616084501', NULL),
(29, 6, 'REGULAR', 'Magnam ut inventore hic molestiae quos facilis.', '1000', '10000', '0', '1616085038', '1616085038', NULL),
(30, 6, 'VIP', 'Ut quia et voluptates enim possimus reprehenderit quod itaque.', '10000', '1000', '0', '1616085038', '1616115272', '1616115272'),
(31, 6, 'VVIP', 'Fugit rerum odio.', '100000', '5000', '0', '1616085038', '1616085038', NULL),
(32, 6, 'REGULAR', 'Quas tenetur doloribus molestias in hic aut ratione doloremque.', '1000', '10000', '0', '1616085094', '1616085094', NULL),
(33, 6, 'VIP', 'Neque iure reiciendis velit maxime optio sed odit.', '10000', '1000', '0', '1616085094', '1616085094', NULL),
(34, 6, 'VVIP', 'Inventore sed voluptatibus ea saepe rerum consequatur unde velit.', '100000', '5000', '0', '1616085094', '1616085094', NULL),
(35, 6, 'REGULAR', 'Vel tempore sit repellat officia voluptatem officia.', '1000', '10000', '0', '1616085145', '1616085145', NULL),
(36, 6, 'VIP', 'Sequi tenetur harum autem sit aliquid nihil eius.', '10000', '1000', '0', '1616085145', '1616085145', NULL),
(37, 6, 'VVIP', 'At voluptatem magnam error et accusamus natus sequi nobis consequatur.', '100000', '5000', '0', '1616085145', '1616085145', NULL),
(38, 6, 'REGULAR', 'Ut et suscipit.', '1000', '10000', '0', '1616085147', '1616085147', NULL),
(39, 6, 'VIP', 'Voluptatibus temporibus recusandae.', '10000', '1000', '0', '1616085147', '1616085147', NULL),
(40, 6, 'VVIP', 'Impedit ea dolorem perferendis dolore et aliquam qui incidunt corrupti.', '100000', '5000', '0', '1616085147', '1616085147', NULL),
(41, 6, 'REGULAR', 'Eveniet dicta qui sunt voluptatem ratione debitis.', '1000', '10000', '0', '1616085147', '1616085147', NULL),
(42, 6, 'VIP', 'Omnis omnis voluptas provident repellendus.', '10000', '1000', '0', '1616085147', '1616085147', NULL),
(43, 6, 'VVIP', 'Suscipit laudantium labore corrupti suscipit sapiente reprehenderit.', '100000', '5000', '0', '1616085147', '1616085147', NULL),
(44, 6, 'REGULAR', 'Ut vel eum.', '1000', '10000', '0', '1616085159', '1616085159', NULL),
(45, 6, 'VIP', 'Laboriosam dolorem voluptatibus harum.', '10000', '1000', '0', '1616085159', '1616085159', NULL),
(46, 6, 'VVIP', 'Expedita non vitae voluptatem in aut omnis.', '100000', '5000', '0', '1616085159', '1616085159', NULL),
(47, 6, 'REGULAR', 'Ut corporis dolorem vel ut quidem beatae veniam eius.', '1000', '10000', '0', '1616085160', '1616085160', NULL),
(48, 6, 'VIP', 'Sunt quam et recusandae cum eos qui quis.', '10000', '1000', '0', '1616085160', '1616085160', NULL),
(49, 6, 'VVIP', 'Eaque corporis assumenda ducimus sit ea.', '100000', '5000', '0', '1616085160', '1616085160', NULL),
(50, 6, 'REGULAR', 'Soluta aut quidem libero enim.', '1000', '10000', '0', '1616085161', '1616085161', NULL),
(51, 6, 'VIP', 'Consequatur architecto qui excepturi adipisci.', '10000', '1000', '0', '1616085161', '1616085161', NULL),
(52, 6, 'VVIP', 'Inventore velit adipisci velit iure fugit.', '100000', '5000', '0', '1616085161', '1616085161', NULL),
(53, 6, 'REGULAR', 'Velit enim odio vel enim voluptatem veritatis eos.', '1000', '10000', '0', '1616085209', '1616085209', NULL),
(54, 6, 'VIP', 'Unde harum quae architecto incidunt ea exercitationem inventore laudantium ut.', '10000', '1000', '0', '1616085209', '1616085209', NULL),
(55, 6, 'VVIP', 'Recusandae ad et quaerat eos distinctio dolore velit.', '100000', '5000', '0', '1616085209', '1616085209', NULL),
(56, 6, 'REGULAR', 'Est sapiente vero amet consequuntur amet magnam.', '1000', '10000', '0', '1616085210', '1616085210', NULL),
(57, 6, 'VIP', 'Sint earum tenetur soluta expedita voluptatem sed et.', '10000', '1000', '0', '1616085210', '1616085210', NULL),
(58, 6, 'VVIP', 'Sint earum reiciendis vel magni impedit at ducimus quia.', '100000', '5000', '0', '1616085210', '1616085210', NULL),
(59, 6, 'REGULAR', 'Debitis aliquam aspernatur est inventore aut.', '1000', '10000', '0', '1616085274', '1616085274', NULL),
(60, 6, 'VIP', 'Sapiente aut rerum dolores dolorem.', '10000', '1000', '0', '1616085274', '1616085274', NULL),
(61, 6, 'VVIP', 'Quod quidem quisquam.', '100000', '5000', '0', '1616085274', '1616085274', NULL),
(62, 6, 'REGULAR', 'Voluptas quia ea sit et earum aut reprehenderit consequuntur.', '1000', '10000', '0', '1616085777', '1616085777', NULL),
(63, 6, 'VIP', 'Quibusdam sint perspiciatis nihil voluptatem sapiente repellendus.', '10000', '1000', '0', '1616085777', '1616085777', NULL),
(64, 6, 'VVIP', 'Et aspernatur earum id consequatur quod quos eos molestias.', '100000', '5000', '0', '1616085777', '1616085777', NULL),
(65, 6, 'REGULAR', 'Cumque quae ab expedita voluptas omnis quis fuga.', '1000', '10000', '0', '1616085794', '1616085794', NULL),
(66, 6, 'VIP', 'Perspiciatis qui laudantium suscipit est quibusdam adipisci labore.', '10000', '1000', '0', '1616085794', '1616085794', NULL),
(67, 6, 'VVIP', 'A aut libero quo voluptatem labore ut sint mollitia eum.', '100000', '5000', '0', '1616085794', '1616085794', NULL),
(68, 6, 'REGULAR', 'At voluptas molestiae et officia velit dignissimos eaque.', '1000', '10000', '0', '1616085796', '1616085796', NULL),
(69, 6, 'VIP', 'In deserunt ullam voluptatum sunt dolores sed quidem.', '10000', '1000', '0', '1616085796', '1616085796', NULL),
(70, 6, 'VVIP', 'Excepturi sequi officiis adipisci est dignissimos sapiente consequuntur quia.', '100000', '5000', '0', '1616085796', '1616085796', NULL),
(71, 6, 'REGULAR', 'Harum vero iure debitis quia molestiae.', '1000', '10000', '0', '1616085797', '1616085797', NULL),
(72, 6, 'VIP', 'In excepturi ea quia.', '10000', '1000', '0', '1616085797', '1616085797', NULL),
(73, 6, 'VVIP', 'Esse assumenda corrupti error dolorum ipsa voluptatem.', '100000', '5000', '0', '1616085797', '1616085797', NULL),
(74, 6, 'REGULAR', 'Quidem delectus debitis quo aut ut aut laborum qui.', '1000', '10000', '0', '1616085825', '1616085825', NULL),
(75, 6, 'VIP', 'Unde eos earum accusamus consectetur aut sunt.', '10000', '1000', '0', '1616085825', '1616085825', NULL),
(76, 6, 'VVIP', 'Eum maiores sunt consequatur optio blanditiis ut.', '100000', '5000', '0', '1616085825', '1616085825', NULL),
(77, 6, 'REGULAR', 'Voluptatum sunt sit hic enim earum et.', '1000', '10000', '0', '1616085841', '1616085841', NULL),
(78, 6, 'VIP', 'In aspernatur unde porro eum.', '10000', '1000', '0', '1616085841', '1616085841', NULL),
(79, 6, 'VVIP', 'Voluptatem quas aut.', '100000', '5000', '0', '1616085841', '1616085841', NULL),
(80, 32, 'REGULAR', 'Voluptatibus autem repudiandae id saepe sed explicabo et.', '1000', '10000', '0', '1616109065', '1616109065', NULL),
(81, 32, 'VIP', 'Possimus praesentium libero harum consequuntur rem vero accusantium beatae.', '10000', '1000', '0', '1616109065', '1616109065', NULL),
(82, 32, 'VVIP', 'Optio consectetur et quia.', '100000', '5000', '0', '1616109065', '1616109065', NULL),
(83, 33, 'REGULAR', 'Dolores dolorem repudiandae perferendis quae deleniti non soluta dolorem.', '1000', '10000', '0', '1616109074', '1616109074', NULL),
(84, 33, 'VIP', 'Itaque voluptatem illum laboriosam atque.', '10000', '1000', '0', '1616109074', '1616109074', NULL),
(85, 33, 'VVIP', 'Vel quibusdam tempora beatae voluptatem minus consequuntur minima sit.', '100000', '5000', '0', '1616109074', '1616109074', NULL),
(86, 9, 'REGULAR', 'Perferendis expedita consequatur recusandae commodi maiores sequi et non consequatur.', '1000', '10000', '0', '1616114462', '1616114462', NULL);

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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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

--
-- Dumping data for table `organiser`
--

INSERT INTO `organiser` (`organiser_id`, `organiser_name`, `organiser_username`, `organiser_password`, `organiser_phone`, `organiser_email`, `organiser_address`, `organiser_profile_picture`, `usertype`, `phone_verified`, `email_verified`, `email_verification_token`, `forgot_password_token`, `public_key`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Lora Murphy', 'Makayla43', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '984-776-2469', 'Loy23@hotmail.com', '15276 Maggie Loaf', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', NULL, NULL, 'zohfiWQiWs9W', '1615853277', '1615903708', NULL),
(2, 'Shawna Corwin', 'Miguel21', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '861-761-1901', 'Merritt90@yahoo.com', '27707 Madie Heights', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '91058794841276686748897856412687649645116856', NULL, NULL, '1615853421', '1615853421', NULL),
(3, 'Stewart Dooley', 'Kari_Flatley', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '896-557-3510', 'Reta41@gmail.com', '0584 Rippin Overpass', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '59117511585810844116788755944647446485587867', NULL, NULL, '1615853521', '1615853521', NULL),
(4, 'Miss Karl Kovacek', 'Jamaal_Mante', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '274-217-8359', 'Alexander_Runolfsson83@yahoo.com', '88478 Jerde Skyway', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '4597848989464476738105108938888976667118579', NULL, NULL, '1615853657', '1615853657', NULL),
(5, 'Faye West', 'Delmer_Dare', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '496-805-4289', 'Talia13@hotmail.com', '4374 Bosco Port', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '77767910551075558697763851087685655107476646', NULL, NULL, '1615853718', '1615853718', NULL),
(6, 'Otis Grant', 'Jedidiah.Jerde', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '352-816-2163', 'Ian.Brown@gmail.com', '262 Gregg Burg', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '77668885488777867596796105844946548877457', NULL, NULL, '1615853796', '1615853796', NULL),
(7, 'Jenna Gerlach', 'Erich31', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '529-280-7043', 'Vince40@gmail.com', '1479 Noble Manor', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '488884488455858646511576781154768986438378', NULL, NULL, '1615853844', '1615856217', NULL),
(8, 'Meredith Runolfsdottir', 'Elsie57', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '798-227-9852', 'Carley21@yahoo.com', '5366 Jaunita Garden', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '754575796687586108675984854899844798475410', NULL, NULL, '1615854041', '1615854041', NULL),
(9, 'Tamara Fahey', 'Maynard_Heller20', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '961-591-4791', 'Giovanny.Corwin45@hotmail.com', '83441 Jerde Burgs', NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '6855398108475910588759755565886710558566664', NULL, NULL, '1615854121', '1615854121', NULL),
(10, 'Mrs. Ethel Lemke', 'Laurianne.Schuster99', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '937-500-4606', 'Dianna_Jenkins@gmail.com', NULL, NULL, 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '467984787657654869781059554984965977651173', NULL, NULL, '1615854502', '1615911212', NULL);

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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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

--
-- Dumping data for table `organiser_staff`
--

INSERT INTO `organiser_staff` (`organiser_staff_id`, `organiser_id`, `organiser_staff_name`, `organiser_staff_username`, `organiser_staff_password`, `organiser_staff_phone`, `organiser_staff_email`, `organiser_staff_profile_picture`, `organiser_staff_priviledges`, `usertype`, `phone_verified`, `email_verified`, `email_verification_token`, `forgot_password_token`, `public_key`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Lora Murphy', 'Makayla43', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '984-776-2469', 'Loy23@hotmail.com', 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20version%3D%221.1%22%20baseProfile%3D%22full%22%20width%3D%22undefined%22%20height%3D%22undefined%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22grey%22%2F%3E%3Ctext%20x%3D%22NaN%22%20y%3D%22NaN%22%20font-size%3D%2220%22%20alignment-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20fill%3D%22white%22%3Eundefinedxundefined%3C%2Ftext%3E%3C%2Fsvg%3E', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', NULL, NULL, NULL, '1615853277', '1615919379', NULL),
(2, 2, 'Shawna Corwin', 'Miguel21', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '861-761-1901', 'Merritt90@yahoo.com', 'assets/images/1615853413.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '91058794841276686748897856412687649645116856', NULL, NULL, '1615853421', '1615853421', NULL),
(3, 3, 'Stewart Dooley', 'Kari_Flatley', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '896-557-3510', 'Reta41@gmail.com', 'assets/images/1615853513.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '59117511585810844116788755944647446485587867', NULL, NULL, '1615853521', '1615853521', NULL),
(4, 4, 'Miss Karl Kovacek', 'Jamaal_Mante', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '274-217-8359', 'Alexander_Runolfsson83@yahoo.com', 'assets/images/1615853649.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '4597848989464476738105108938888976667118579', NULL, NULL, '1615853657', '1615853657', NULL),
(5, 5, 'Faye West', 'Delmer_Dare', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '496-805-4289', 'Talia13@hotmail.com', 'assets/images/1615853710.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '77767910551075558697763851087685655107476646', NULL, NULL, '1615853718', '1615853718', NULL),
(6, 6, 'Otis Grant', 'Jedidiah.Jerde', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '352-816-2163', 'Ian.Brown@gmail.com', 'assets/images/1615853787.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '77668885488777867596796105844946548877457', NULL, NULL, '1615853796', '1615853796', NULL),
(7, 7, 'Jenna Gerlach', 'Erich31', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '529-280-7043', 'Vince40@gmail.com', 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20version%3D%221.1%22%20baseProfile%3D%22full%22%20width%3D%22undefined%22%20height%3D%22undefined%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22grey%22%2F%3E%3Ctext%20x%3D%22NaN%22%20y%3D%22NaN%22%20font-size%3D%2220%22%20alignment-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20fill%3D%22white%22%3Eundefinedxundefined%3C%2Ftext%3E%3C%2Fsvg%3E', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '488884488455858646511576781154768986438378', NULL, NULL, '1615853844', '1615856217', NULL),
(8, 8, 'Meredith Runolfsdottir', 'Elsie57', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '798-227-9852', 'Carley21@yahoo.com', 'assets/images/1615854033.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '754575796687586108675984854899844798475410', NULL, NULL, '1615854041', '1615854041', NULL),
(9, 9, 'Tamara Fahey', 'Maynard_Heller20', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '961-591-4791', 'Giovanny.Corwin45@hotmail.com', 'assets/images/1615854113.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '6855398108475910588759755565886710558566664', NULL, NULL, '1615854121', '1615854121', NULL),
(10, 10, 'Mrs. Ethel Lemke', 'Laurianne.Schuster99', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '937-500-4606', 'Dianna_Jenkins@gmail.com', 'assets/images/1615911212.svg', '[]', 'ORGANIZER_ADMIN', 'NOT_VERIFIED', 'NOT_VERIFIED', '467984787657654869781059554984965977651173', NULL, 'DIWR2otPB5tB', '1615854502', '1616110216', NULL),
(11, 10, 'Terrell Braun', 'Eugenia41', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '451-266-9145', 'Marisol.Toy@yahoo.com', 'assets/images/1615913960.svg', 'Array', 'ORGANIZER_STAFF', 'NOT_VERIFIED', 'NOT_VERIFIED', '87676653476810686898684487475655765898698', NULL, 'sFAvW6t7QfQb', '1615913964', '1615913964', NULL),
(12, 10, 'Cathy Kshlerin', 'Lennie32', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '620-237-1292', 'Tracy_Bailey@hotmail.com', 'assets/images/1615914195.svg', 'Array', 'ORGANIZER_STAFF', 'NOT_VERIFIED', 'NOT_VERIFIED', '104577664451169555585675668854754655578488', NULL, 'daz18zINkDaD', '1615914199', '1615914199', NULL),
(13, 10, 'Edwin Sporer', 'Kenny_Barrows90', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '411-248-2220', 'Addie19@gmail.com', 'assets/images/1615917430.svg', '[\"EVENT\",\"REPORT\",\"ACTIVITY_LOG\"]', 'ORGANIZER_STAFF', 'NOT_VERIFIED', 'NOT_VERIFIED', '6757535946577979365658549659776498545858', NULL, '1U83KoVsnKPp', '1615914239', '1615917430', NULL),
(14, 10, 'Shawn Langworth', 'Violet0', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '844-215-7758', 'Terry54@gmail.com', 'assets/images/1615914374.svg', '[\"EVENT\",\"REPORT\",\"ACTIVITY_LOG\",\"ORGANISER\"]', 'ORGANIZER_STAFF', 'NOT_VERIFIED', 'NOT_VERIFIED', '379768856751078116646446884871081158975481174', NULL, 'cJhjSaeiaslM', '1615914379', '1615914379', NULL),
(15, 10, 'Brenda Barton', 'Kelly_Hamill31', 'c30aec03227fe7ef0501ea023877d37446dfba9829a1d06c7453e5ee81ab3f7d', '957-908-3251', 'Nicholas.Farrell@hotmail.com', 'assets/images/1615914941.svg', '[\"EVENT\",\"REPORT\",\"ACTIVITY_LOG\",\"ORGANISER\"]', 'ORGANIZER_STAFF', 'NOT_VERIFIED', 'NOT_VERIFIED', '49106585585688644986410887485767776889710105', NULL, NULL, '1615914945', '1615918331', NULL);

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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  `updated_at` varchar(15) NOT NULL,
  `deleted_at` varchar(15) DEFAULT NULL
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
  MODIFY `admin_feature_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_feature_user`
--
ALTER TABLE `admin_feature_user`
  MODIFY `admin_feature_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `admin_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `event_access`
--
ALTER TABLE `event_access`
  MODIFY `event_access_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_control`
--
ALTER TABLE `event_control`
  MODIFY `event_control_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `event_ticket`
--
ALTER TABLE `event_ticket`
  MODIFY `event_ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

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
  MODIFY `organiser_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `organiser_activity_log`
--
ALTER TABLE `organiser_activity_log`
  MODIFY `activity_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organiser_staff`
--
ALTER TABLE `organiser_staff`
  MODIFY `organiser_staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
