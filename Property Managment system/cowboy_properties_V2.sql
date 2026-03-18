-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 12, 2026 at 03:02 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cowboy_properties`
--
CREATE DATABASE IF NOT EXISTS `cowboy_properties` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cowboy_properties`;

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

DROP TABLE IF EXISTS `assignment`;
CREATE TABLE IF NOT EXISTS `assignment` (
  `AssignmentID` int NOT NULL AUTO_INCREMENT,
  `EmpID` int NOT NULL,
  `PropertyID` int NOT NULL,
  PRIMARY KEY (`AssignmentID`),
  UNIQUE KEY `uq_assignment_emp_property` (`EmpID`,`PropertyID`),
  KEY `fk_assignment_property` (`PropertyID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`AssignmentID`, `EmpID`, `PropertyID`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5),
(6, 6, 6),
(7, 7, 7),
(8, 8, 8),
(9, 9, 9),
(10, 10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE IF NOT EXISTS `employee` (
  `EmpID` int NOT NULL AUTO_INCREMENT,
  `Lastname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Firstname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Position` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  PRIMARY KEY (`EmpID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmpID`, `Lastname`, `Firstname`, `Position`, `Password`, `Email`, `phone`, `role`) VALUES
(1, 'Martin', 'Riley', 'Accountant', '$2y$10$D5RLYn7cGXIbT.vdaBFEp.I1gdjYeSVJy1oLgnxGYHLrzhleVoeca', 'riley.martin40@email.com', '(855) 284-2603', 'employee'),
(2, 'Wilson', 'Taylor', 'Accountant', '$2y$10$XiETM6SYzJNy58YtcZ657uK5t4.a2oZU0/kPV9lUSHgcUV8yT80ZO', 'taylor.wilson80@mail.com', '(534) 883-4923', 'employee'),
(3, 'Taylor', 'Jamie', 'Accountant', '$2y$10$QYPKua6tVVshVoUdLGSvcu1bOa5Ul8rE1UQPiyLh3illIL.FrlFWC', 'jamie.taylor95@example.com', '(434) 579-2585', 'employee'),
(4, 'Brown', 'Sam', 'Assistant Manager', '$2y$10$Xc/Y9tiWNl25A25tQndYA.Jw84y81NB2VCAbx2E6OUa6tkmnFct5W', 'sam.brown43@mail.com', '(217) 676-4180', 'employee'),
(5, 'Johnson', 'Taylor', 'Manager', '$2y$10$DT.XM8NiuAlWk0ADZi8tNOgTN2.FHdTNIho.2NU4A6tluH2BAHS3i', 'taylor.johnson70@cowboyprops.com', '(676) 714-1117', 'employee'),
(6, 'Smith', 'Taylor', 'Accountant', '$2y$10$yzlEv.y12WTuaysRM3IrOe5QH/n.esaLNpfFT1YYHaszVXX7cymcq', 'taylor.smith65@mail.com', '(810) 268-7652', 'employee'),
(7, 'Smith', 'Morgan', 'Accountant', '$2y$10$ugI3jdP9qI1LCthUsVpj7eJMt6RLEDZ3LHzDzn3GyRLpSILvmO.1O', 'morgan.smith70@example.com', '(878) 513-3831', 'employee'),
(8, 'Anderson', 'Cameron', 'Maintenance Lead', '$2y$10$Zs73/Ew4b1zXKa4QpyuqleQBAyHoUSdNDYzTflCYVe6XtcclZAvsq', 'cameron.anderson8@cowboyprops.com', '(449) 624-1473', 'employee'),
(9, 'Miller', 'Cameron', 'Accountant', '$2y$10$9KniHeyaRDFyHyzpvzMLd.NEFlhj6oit0Gg9gfAfSgoGwtnzHrJIm', 'cameron.miller24@cowboyprops.com', '(537) 533-2537', 'employee'),
(10, 'Davis', 'Addison', 'Leasing Agent', '$2y$10$qUb.cNT68d0BXP27oITyneSspMvi0ml17rIW1o1vkHBBriG9jtjZy', 'addison.davis50@cowboyprops.com', '(570) 241-1015', 'employee'),
(11, 'Thomas', 'Addison', 'Assistant Manager', '$2y$10$ayjosed.jN8.4JiXElxtaedjLlBTFZgjzjeA2JtsMdGyVZaNS3LHW', 'addison.thomas5@cowboyprops.com', '(931) 550-4925', 'employee'),
(12, 'Smith', 'Addison', 'Assistant Manager', '$2y$10$MwAs9HiTPZAxYGutqCsokuCDyXiwQjrF2p1qDcC/4Nc2BP59gZmci', 'addison.smith17@cowboyprops.com', '(961) 358-3579', 'employee'),
(13, 'Wilson', 'Drew', 'Assistant Manager', '$2y$10$O26u0NO/Y6qgSaTCL7aZnuvRtSIGEAhpV8Ba5/1CgwEvzTplyFzNy', 'drew.wilson62@cowboyprops.com', '(899) 495-9589', 'employee'),
(14, 'Wilson', 'Alex', 'Maintenance Lead', '$2y$10$ep6y0zAUVcxMKOmCoZl3oekUaTsH6xkdlHKWlSPDD0lC/KtjpwduS', 'alex.wilson39@mail.com', '(384) 828-5259', 'employee'),
(15, 'Miller', 'Addison', 'Manager', '$2y$10$JfvFX4.p.UXZ6TUxDmlyveXSSeTS8fJUzDfVRuJ9tJvX2nBoqPlUi', 'addison.miller90@cowboyprops.com', '(454) 533-5934', 'employee'),
(16, 'Wilson', 'Drew', 'Accountant', '$2y$10$3a0VChpPyX6Td3cIJao1ROfL1bqw9C7BhNL4SCvyBoY0NSmTnZLmy', 'drew.wilson78@email.com', '(526) 606-6153', 'employee'),
(17, 'Johnson', 'Drew', 'Maintenance Lead', '$2y$10$jjC7F4k9HDI0u/n5YTVXfuvFnS0yNlMeKpnKIfnNS1IdjThZDKIJ2', 'drew.johnson72@mail.com', '(628) 510-8237', 'employee'),
(18, 'Brown', 'Cameron', 'Manager', '$2y$10$NfWyxcHMy480GlfIgiDJ9eQKR4dFVP//CTpt6Gc63boVE0auhTH2S', 'cameron.brown18@mail.com', '(904) 787-2855', 'employee'),
(19, 'Davis', 'Alex', 'Assistant Manager', '$2y$10$sp2kEmzF8tpuc7sAtR7djOJgaK8cF6mRVLwdhyMmWPa.s3iMQk3ti', 'alex.davis30@mail.com', '(507) 540-9506', 'employee'),
(20, 'Brown', 'Sam', 'Assistant Manager', '$2y$10$fec5sLwnfxnZQwzyh7k9AOiw/voS4avejiDGdNjVzR7emZHR/EIeW', 'sam.brown9@mail.com', '(708) 280-3641', 'employee');

-- --------------------------------------------------------

--
-- Table structure for table `lease`
--

DROP TABLE IF EXISTS `lease`;
CREATE TABLE IF NOT EXISTS `lease` (
  `LeaseID` int NOT NULL AUTO_INCREMENT,
  `RenterID` int NOT NULL,
  `EmpID` int NOT NULL,
  `UnitID` int NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `period` int NOT NULL,
  PRIMARY KEY (`LeaseID`),
  KEY `fk_lease_renter` (`RenterID`),
  KEY `fk_lease_employee` (`EmpID`),
  KEY `fk_lease_unit` (`UnitID`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lease`
--

INSERT INTO `lease` (`LeaseID`, `RenterID`, `EmpID`, `UnitID`, `Price`, `period`) VALUES
(1, 1, 1, 1, 1825.00, 12),
(2, 2, 2, 2, 1779.00, 12),
(3, 3, 3, 3, 1697.00, 12),
(4, 4, 4, 4, 1833.00, 12),
(5, 5, 5, 5, 1768.00, 12),
(6, 6, 6, 6, 2035.00, 12),
(7, 7, 7, 7, 2017.00, 12),
(8, 8, 8, 8, 1755.00, 12),
(9, 9, 9, 9, 1470.00, 12),
(10, 10, 10, 10, 1982.00, 12),
(11, 11, 11, 11, 1761.00, 12),
(12, 12, 12, 12, 1892.00, 12),
(13, 13, 13, 13, 1852.00, 12),
(14, 14, 14, 14, 1452.00, 12),
(15, 15, 15, 15, 2015.00, 12),
(16, 16, 16, 16, 1431.00, 12),
(17, 17, 17, 17, 1811.00, 12),
(18, 18, 18, 18, 2063.00, 12),
(19, 19, 19, 19, 2099.00, 12),
(20, 20, 20, 20, 1427.00, 12);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
CREATE TABLE IF NOT EXISTS `maintenance` (
  `TicketID` int NOT NULL AUTO_INCREMENT,
  `UnitID` int NOT NULL,
  `RenterID` int NOT NULL,
  `EmpID` int NOT NULL,
  `Date` date NOT NULL,
  `Issue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`TicketID`),
  KEY `fk_maint_unit` (`UnitID`),
  KEY `fk_maint_renter` (`RenterID`),
  KEY `fk_maint_employee` (`EmpID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`TicketID`, `UnitID`, `RenterID`, `EmpID`, `Date`, `Issue`, `Status`) VALUES
(1, 11, 6, 8, '2026-01-31', 'Ceiling stain appearing', 'Open'),
(2, 4, 2, 8, '2026-01-12', 'Garbage disposal jammed', 'Open'),
(3, 12, 14, 8, '2026-01-21', 'Door lock stuck', 'In Progress'),
(4, 5, 14, 8, '2026-02-23', 'Door lock stuck', 'Open'),
(5, 15, 15, 8, '2025-12-13', 'Dishwasher leaking', 'Open'),
(6, 8, 2, 8, '2026-01-12', 'Smoke detector beeping', 'Closed'),
(7, 6, 9, 8, '2026-02-13', 'Smoke detector beeping', 'In Progress'),
(8, 11, 3, 8, '2026-02-25', 'Smoke detector beeping', 'Open'),
(9, 1, 18, 8, '2026-01-31', 'Leaky faucet in kitchen', 'Closed'),
(10, 5, 6, 8, '2025-12-31', 'Leaky faucet in kitchen', 'Open'),
(11, 11, 6, 8, '2026-01-29', 'AC not cooling', 'Open'),
(12, 2, 9, 8, '2026-03-08', 'Door lock stuck', 'Open'),
(13, 10, 19, 8, '2026-03-09', 'Heater making noise', 'In Progress'),
(14, 4, 1, 8, '2026-01-30', 'Smoke detector beeping', 'Closed'),
(15, 18, 6, 8, '2026-01-12', 'Door lock stuck', 'Open'),
(16, 2, 9, 8, '2026-01-30', 'Dishwasher leaking', 'Open'),
(17, 2, 12, 8, '2026-01-08', 'AC not cooling', 'Open'),
(18, 1, 12, 8, '2026-01-25', 'Garbage disposal jammed', 'Open'),
(19, 2, 14, 8, '2026-03-04', 'AC not cooling', 'In Progress'),
(20, 12, 13, 8, '2026-02-12', 'Window won’t close fully', 'In Progress'),
(22, 1, 1, 1, '2026-03-09', 'Bathroom | General | test', 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `InvoiceID` int NOT NULL AUTO_INCREMENT,
  `RenterID` int NOT NULL,
  `LeaseID` int NOT NULL,
  `EmpID` int NOT NULL,
  `period` int NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`InvoiceID`),
  KEY `fk_payment_renter` (`RenterID`),
  KEY `fk_payment_lease` (`LeaseID`),
  KEY `fk_payment_employee` (`EmpID`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`InvoiceID`, `RenterID`, `LeaseID`, `EmpID`, `period`, `date`, `amount`) VALUES
(1, 1, 1, 1, 202602, '2026-02-07', 1825.00),
(2, 1, 1, 1, 202601, '2026-01-08', 1825.00),
(3, 2, 2, 1, 202602, '2026-02-07', 1779.00),
(4, 2, 2, 1, 202601, '2026-01-08', 1779.00),
(5, 3, 3, 1, 202602, '2026-02-07', 1697.00),
(6, 3, 3, 1, 202601, '2026-01-08', 1697.00),
(7, 4, 4, 1, 202602, '2026-02-07', 1833.00),
(8, 4, 4, 1, 202601, '2026-01-08', 1833.00),
(9, 5, 5, 1, 202602, '2026-02-07', 1768.00),
(10, 5, 5, 1, 202601, '2026-01-08', 1768.00),
(11, 6, 6, 1, 202602, '2026-02-07', 2035.00),
(12, 6, 6, 1, 202601, '2026-01-08', 2035.00),
(13, 7, 7, 1, 202602, '2026-02-07', 2017.00),
(14, 7, 7, 1, 202601, '2026-01-08', 2017.00),
(15, 8, 8, 1, 202602, '2026-02-07', 1755.00),
(16, 8, 8, 1, 202601, '2026-01-08', 1755.00),
(17, 9, 9, 1, 202602, '2026-02-07', 1470.00),
(18, 9, 9, 1, 202601, '2026-01-08', 1470.00),
(19, 10, 10, 1, 202602, '2026-02-07', 1982.00),
(20, 10, 10, 1, 202601, '2026-01-08', 1982.00),
(21, 11, 11, 1, 202602, '2026-02-07', 1761.00),
(22, 11, 11, 1, 202601, '2026-01-08', 1761.00),
(23, 12, 12, 1, 202602, '2026-02-07', 1892.00),
(24, 12, 12, 1, 202601, '2026-01-08', 1892.00),
(25, 13, 13, 1, 202602, '2026-02-07', 1852.00),
(26, 13, 13, 1, 202601, '2026-01-08', 1852.00),
(27, 14, 14, 1, 202602, '2026-02-07', 1452.00),
(28, 14, 14, 1, 202601, '2026-01-08', 1452.00),
(29, 15, 15, 1, 202602, '2026-02-07', 2015.00),
(30, 15, 15, 1, 202601, '2026-01-08', 2015.00),
(31, 16, 16, 1, 202602, '2026-02-07', 1431.00),
(32, 16, 16, 1, 202601, '2026-01-08', 1431.00),
(33, 17, 17, 1, 202602, '2026-02-07', 1811.00),
(34, 17, 17, 1, 202601, '2026-01-08', 1811.00),
(35, 18, 18, 1, 202602, '2026-02-07', 2063.00),
(36, 18, 18, 1, 202601, '2026-01-08', 2063.00),
(37, 19, 19, 1, 202602, '2026-02-07', 2099.00),
(38, 19, 19, 1, 202601, '2026-01-08', 2099.00),
(39, 20, 20, 1, 202602, '2026-02-07', 1427.00),
(40, 20, 20, 1, 202601, '2026-01-08', 1427.00);

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

DROP TABLE IF EXISTS `property`;
CREATE TABLE IF NOT EXISTS `property` (
  `PropertyID` int NOT NULL AUTO_INCREMENT,
  `Address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ManagerEmpID` int NOT NULL,
  `Unit_Count` int NOT NULL,
  PRIMARY KEY (`PropertyID`),
  KEY `fk_property_manager` (`ManagerEmpID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`PropertyID`, `Address`, `ManagerEmpID`, `Unit_Count`) VALUES
(1, '2337 Cedar St, Stillwater, OK', 1, 2),
(2, '4541 Maple Dr, Stillwater, OK', 2, 2),
(3, '2995 Maple Dr, Stillwater, OK', 3, 2),
(4, '8516 Pine Rd, Stillwater, OK', 4, 2),
(5, '9984 Cedar St, Stillwater, OK', 5, 2),
(6, '8275 Cedar St, Stillwater, OK', 6, 2),
(7, '9570 Birch Ln, Stillwater, OK', 7, 2),
(8, '584 Pine Rd, Stillwater, OK', 8, 2),
(9, '4791 Maple Dr, Stillwater, OK', 9, 2),
(10, '2178 Pine Rd, Stillwater, OK', 10, 2);

-- --------------------------------------------------------

--
-- Table structure for table `renter`
--

DROP TABLE IF EXISTS `renter`;
CREATE TABLE IF NOT EXISTS `renter` (
  `RenterID` int NOT NULL AUTO_INCREMENT,
  `Firstname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Lastname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'renter',
  PRIMARY KEY (`RenterID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `renter`
--

INSERT INTO `renter` (`RenterID`, `Firstname`, `Lastname`, `email`, `phone`, `password`, `role`) VALUES
(1, 'Brandon', 'Smith', 'bsmith@cowboyproperties.com', '555-210-1001', '$2y$10$kZSodsMj7unvN4aS0iRFuuz17Z0UwtJHkdE52QiorY6HuWL/5S.4O', 'renter'),
(2, 'Paige', 'Jones', 'pjones@cowboyproperties.com', '555-210-1002', '$2y$10$8o3U497WkvVHCo7b9Ufg7OQlV02ziTOp6fcr5nBP7WYYIG/uJRMqi', 'renter'),
(3, 'Casey', 'Miller', 'casey.miller86@example.com', '(541) 311-2713', '$2y$10$wsiRjl2zH9yZIHdO1U6/MOeM62VOf6nLsmzyzB5/7iEsV6PNQ8OkG', 'renter'),
(4, 'Taylor', 'Thomas', 'taylor.thomas41@mail.com', '(257) 788-9340', '$2y$10$snYPsg.40QaWFSZjfx/2dO1tAHTpGoBQBQXdKPhljUnKMFbhK0rNS', 'renter'),
(5, 'Casey', 'Johnson', 'casey.johnson57@mail.com', '(588) 375-5506', '$2y$10$vDPbyGfpZkf6GxR/X1Ymeul1yLwYGWiQTqyapMzKXvKgAFWITRo1G', 'renter'),
(6, 'Jordan', 'Davis', 'jordan.davis82@mail.com', '(688) 399-3523', '$2y$10$HvW4gmHMUdsAKF27d4evO.pwTG7xwQNfLkxcKsTNll9a40grJm8Uq', 'renter'),
(7, 'Jamie', 'Miller', 'jamie.miller46@mail.com', '(434) 855-2306', '$2y$10$wVIAqJPO.gvphFuX45Gp1Os7cY95rdLicFGgPtrMEyeCfNJi8my7K', 'renter'),
(8, 'Riley', 'Wilson', 'riley.wilson54@email.com', '(905) 208-2558', '$2y$10$fHVG736tAwKZeIv5F46AJehvOlMQi./M2y0pn1iIaMAneIkngbRoe', 'renter'),
(9, 'Morgan', 'Miller', 'morgan.miller97@mail.com', '(594) 518-8617', '$2y$10$ctZUD0toBxnkl2q32NMLpepdGlzzu1MUf43lWtzJjjC5c02s8eMJm', 'renter'),
(10, 'Casey', 'Moore', 'casey.moore74@example.com', '(366) 236-6393', '$2y$10$o4SltCmoKgpy42ET4QBX5OmHXsVLNzOud6cD7mamR52o3dKRqQuFW', 'renter'),
(11, 'Taylor', 'Moore', 'taylor.moore29@mail.com', '(771) 297-2853', '$2y$10$606VSuZSsoDy/Ba8rISVRuZdOiaAYqol5L5nOo7t40hGxqZWEuIY6', 'renter'),
(12, 'Drew', 'Moore', 'drew.moore7@cowboyprops.com', '(487) 294-6510', '$2y$10$940PMrN8K51mlCAkSSOAtueIrFQblit1IAo7UbXQT/rwReStSiH32', 'renter'),
(13, 'Casey', 'Martin', 'casey.martin20@example.com', '(472) 696-1295', '$2y$10$b2u8WT63mJzP.ReUGcy8F.1nUp5MxKnURdlzElEUZVumxEV6nuWtS', 'renter'),
(14, 'Drew', 'Smith', 'drew.smith85@email.com', '(483) 474-2360', '$2y$10$Z4GpO45s31LfeIMpQPebK.Bfi/p9zjLMU9aJyJSRbvH2xRogVZJoG', 'renter'),
(15, 'Avery', 'Brown', 'avery.brown60@example.com', '(711) 224-9330', '$2y$10$Vpm4OqVzCWv.iQdRkVuXZuLttuoyynz02LeRX9dwPGkVXbZ3Uvc9K', 'renter'),
(16, 'Taylor', 'Miller', 'taylor.miller38@example.com', '(500) 444-7809', '$2y$10$06NYs5geyOcDDcZN2wArh..q/0DU3jnFJcd271e0LSNxJmMG6WMtu', 'renter'),
(17, 'Riley', 'Wilson', 'riley.wilson90@mail.com', '(581) 918-7824', '$2y$10$VU8lWArsvKBuX384UYlwm.Hw7kpo9DdYxB84YPzrE1HHD78Hxmynu', 'renter'),
(18, 'Alex', 'Davis', 'alex.davis32@email.com', '(646) 643-4627', '$2y$10$pz.Za1FrLivaWDL/c.wAlu5SDX89osHLBb0SewZlaMDB3WCVnluku', 'renter'),
(19, 'Cameron', 'Moore', 'cameron.moore11@example.com', '(845) 645-6244', '$2y$10$p.S/oWzzugU5XxpNNxXPV.iiWtQ4tKp9lLlU2noDcCR2cPWbPMbjy', 'renter'),
(20, 'Jordan', 'Davis', 'jordan.davis13@cowboyprops.com', '(235) 257-3207', '$2y$10$/7xbJ3SZhj2YvG2TMWVecejmTTkYChfHq7uGUtjje7XhPYlhOSul2', 'renter');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `TaskID` int NOT NULL AUTO_INCREMENT,
  `TicketID` int NOT NULL,
  `EmployeeID` int NOT NULL,
  PRIMARY KEY (`TaskID`),
  KEY `fk_task_ticket` (`TicketID`),
  KEY `fk_task_employee` (`EmployeeID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`TaskID`, `TicketID`, `EmployeeID`) VALUES
(1, 1, 8),
(2, 2, 8),
(3, 3, 8),
(4, 4, 8),
(5, 5, 8),
(6, 6, 8),
(7, 7, 8),
(8, 8, 8),
(9, 9, 8),
(10, 10, 8);

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
CREATE TABLE IF NOT EXISTS `unit` (
  `UnitID` int NOT NULL AUTO_INCREMENT,
  `PropertyID` int NOT NULL,
  `Unit_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Bed` int NOT NULL,
  `bath` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`UnitID`),
  UNIQUE KEY `uq_unit_property_unitnum` (`PropertyID`,`Unit_number`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`UnitID`, `PropertyID`, `Unit_number`, `Bed`, `bath`, `price`) VALUES
(1, 1, 'A198', 2, 2, 1825.00),
(2, 1, 'A422', 2, 2, 1779.00),
(3, 2, 'B268', 2, 1, 1697.00),
(4, 2, 'B263', 2, 2, 1833.00),
(5, 3, 'C525', 2, 2, 1768.00),
(6, 3, 'C402', 3, 1, 2035.00),
(7, 4, 'D553', 3, 1, 2017.00),
(8, 4, 'D459', 2, 2, 1755.00),
(9, 5, 'E257', 1, 1, 1470.00),
(10, 5, 'E263', 3, 1, 1982.00),
(11, 6, 'F142', 2, 1, 1761.00),
(12, 6, 'F311', 2, 2, 1892.00),
(13, 7, 'G349', 2, 2, 1852.00),
(14, 7, 'G320', 1, 1, 1452.00),
(15, 8, 'H315', 3, 2, 2015.00),
(16, 8, 'H144', 1, 1, 1431.00),
(17, 9, 'I567', 2, 1, 1811.00),
(18, 9, 'I310', 3, 1, 2063.00),
(19, 10, 'J337', 3, 2, 2099.00),
(20, 10, 'J112', 1, 1, 1427.00);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_emp` FOREIGN KEY (`EmpID`) REFERENCES `employee` (`EmpID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assignment_property` FOREIGN KEY (`PropertyID`) REFERENCES `property` (`PropertyID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lease`
--
ALTER TABLE `lease`
  ADD CONSTRAINT `fk_lease_employee` FOREIGN KEY (`EmpID`) REFERENCES `employee` (`EmpID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lease_renter` FOREIGN KEY (`RenterID`) REFERENCES `renter` (`RenterID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lease_unit` FOREIGN KEY (`UnitID`) REFERENCES `unit` (`UnitID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `fk_maint_employee` FOREIGN KEY (`EmpID`) REFERENCES `employee` (`EmpID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maint_renter` FOREIGN KEY (`RenterID`) REFERENCES `renter` (`RenterID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maint_unit` FOREIGN KEY (`UnitID`) REFERENCES `unit` (`UnitID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_employee` FOREIGN KEY (`EmpID`) REFERENCES `employee` (`EmpID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_lease` FOREIGN KEY (`LeaseID`) REFERENCES `lease` (`LeaseID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payment_renter` FOREIGN KEY (`RenterID`) REFERENCES `renter` (`RenterID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `property`
--
ALTER TABLE `property`
  ADD CONSTRAINT `fk_property_manager` FOREIGN KEY (`ManagerEmpID`) REFERENCES `employee` (`EmpID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fk_task_employee` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmpID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_ticket` FOREIGN KEY (`TicketID`) REFERENCES `maintenance` (`TicketID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `unit`
--
ALTER TABLE `unit`
  ADD CONSTRAINT `fk_unit_property` FOREIGN KEY (`PropertyID`) REFERENCES `property` (`PropertyID`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
