-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 31, 2024 at 01:12 PM
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
-- Database: `popay`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ID` int(11) NOT NULL,
  `Fullname` varchar(99) NOT NULL DEFAULT '',
  `Username` varchar(99) NOT NULL DEFAULT '',
  `Role` varchar(99) NOT NULL DEFAULT 'Admin',
  `Password` varchar(255) NOT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID`, `Fullname`, `Username`, `Role`, `Password`, `Status`) VALUES
(5, 'tobi', 'tibson', 'Sales', '$2y$10$5B9/HEA9B.bVDoVIXPQp4.eRT7g5bP7MPEfU7y0MeyJiqMqGZrMOy', 'Active'),
(6, 'onisile tobi', 'Manager', 'Admin', '$2y$10$0iBvmIt03zDDfvNfP5ioL.TEC1fcFaJmbWT.piMQT7g2LD8.suqNm', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

CREATE TABLE `configuration` (
  `id` int(11) NOT NULL,
  `companyName` varchar(100) NOT NULL DEFAULT '',
  `Phone` varchar(20) NOT NULL DEFAULT '',
  `Address` varchar(250) NOT NULL DEFAULT '',
  `bulkPrice` varchar(10) NOT NULL DEFAULT '',
  `retailPrice` varchar(10) NOT NULL DEFAULT '',
  `taxRate` varchar(99) NOT NULL DEFAULT '',
  `logoURL` varchar(999) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`id`, `companyName`, `Phone`, `Address`, `bulkPrice`, `retailPrice`, `taxRate`, `logoURL`) VALUES
(1, 'Popay Gas Station', '+2341234567890', '123 Gas Street, Lagos, Nigeria', '300', '600', '0', 'uploads/collections.png');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ID` bigint(20) NOT NULL,
  `Username` varchar(99) NOT NULL DEFAULT '',
  `Customer` varchar(99) NOT NULL DEFAULT '',
  `Weight1` varchar(99) NOT NULL DEFAULT '',
  `Weight2` varchar(99) NOT NULL DEFAULT '',
  `TotalWeight` varchar(99) NOT NULL,
  `Category` varchar(9) NOT NULL DEFAULT '',
  `PriceKg` varchar(99) NOT NULL,
  `Price` varchar(99) NOT NULL DEFAULT '',
  `Payment` varchar(99) NOT NULL DEFAULT 'Cash',
  `Date_` varchar(999) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ID`, `Username`, `Customer`, `Weight1`, `Weight2`, `TotalWeight`, `Category`, `PriceKg`, `Price`, `Payment`, `Date_`) VALUES
(61, 'tibson', 'ade', '12', '10', '22', 'retail', '600', '3000', 'Bank Transfer', '2024-12-31'),
(62, 'tibson', 'mide', '12', '10', '22', 'retail', '600', '3000', 'Cash', '2024-12-31'),
(63, 'tibson', 'seun', '12', '10', '22', 'retail', '600', '3000', 'Bank Transfer', '2024-12-31'),
(65, 'tibson', 'sw', '12', '10', '22', 'retail', '600', '3000', 'Cash', '2024-12-31'),
(66, 'tibson', 'rt', '12', '10', '22', 'retail', '600', '6000', 'Debit Card', '2024-12-31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `configuration`
--
ALTER TABLE `configuration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `configuration`
--
ALTER TABLE `configuration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
