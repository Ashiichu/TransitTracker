-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Generation Time: Mar 15, 2026 at 03:02 PM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40964251_intellibus`
--

-- --------------------------------------------------------

--
-- Table structure for table `driver_profiles`
--

CREATE TABLE `driver_profiles` (
  `driver_id` int(11) NOT NULL,
  `route_id` varchar(50) NOT NULL,
  `vehicle_type` enum('taxi','bus') NOT NULL,
  `vehicle_description` varchar(255) NOT NULL,
  `max_capacity` int(11) NOT NULL,
  `current_capacity` int(11) DEFAULT 0,
  `is_online` tinyint(1) DEFAULT 0,
  `has_penalty` tinyint(1) DEFAULT 0,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `current_lat` decimal(10,8) DEFAULT 18.01280000,
  `current_lng` decimal(10,8) DEFAULT -76.79890000
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `driver_profiles`
--

INSERT INTO `driver_profiles` (`driver_id`, `route_id`, `vehicle_type`, `vehicle_description`, `max_capacity`, `current_capacity`, `is_online`, `has_penalty`, `total_earnings`, `current_lat`, `current_lng`) VALUES
(100, 'r1', 'taxi', '2013 Subaru G4 Impreza (FB20)', 4, 1, 1, 0, '0.00', '18.01280000', '-76.79890000'),
(101, 'r1', 'bus', 'Toyota Hiace Bus (White)', 15, 0, 0, 0, '0.00', '18.01280000', '-76.79890000'),
(102, 'r2', 'taxi', 'Toyota Probox (White)', 4, 0, 1, 0, '0.00', '18.01280000', '-76.79890000'),
(103, 'r2', 'bus', 'Coaster Bus (White/Blue)', 22, 0, 1, 0, '0.00', '18.01280000', '-76.79890000');

-- --------------------------------------------------------

--
-- Table structure for table `passenger_demand`
--

CREATE TABLE `passenger_demand` (
  `passenger_id` int(11) NOT NULL,
  `route_id` varchar(50) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(10,8) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `route_id` varchar(50) NOT NULL,
  `pickup_lat` decimal(10,8) DEFAULT NULL,
  `pickup_lng` decimal(10,8) DEFAULT NULL,
  `status` enum('pending','accepted','completed','cancelled_by_passenger','rejected') DEFAULT 'pending',
  `fare` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `passenger_id`, `driver_id`, `route_id`, `pickup_lat`, `pickup_lng`, `status`, `fare`, `created_at`) VALUES
(1, 1, 100, 'r1', NULL, NULL, 'accepted', '150.00', '2026-03-15 18:52:06'),
(2, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:08'),
(3, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:08'),
(4, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:08'),
(5, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:08'),
(6, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:09'),
(7, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:09'),
(8, 1, 100, 'r1', NULL, NULL, 'rejected', '150.00', '2026-03-15 18:52:09');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` varchar(50) NOT NULL,
  `route_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `route_name`) VALUES
('r1', 'Portmore to Half Way Tree'),
('r2', 'Downtown to Papine'),
('r3', 'Spanish Town to Cross Roads'),
('r4', 'Half Way Tree to UWI/UTech'),
('r5', 'Montego Bay to Lucea'),
('r6', 'Downtown to Harbour View'),
('r7', 'Cross Roads to Constant Spring'),
('r8', 'Ocho Rios to St. Ann\'s Bay'),
('r9', 'Spanish Town to Linstead'),
('r10', 'Half Way Tree to Meadowbrook');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('passenger','driver') NOT NULL,
  `display_name` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `display_name`) VALUES
(1, 'demo@network.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', 'Passenger 1'),
(100, 'driver100@network.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Driver 100'),
(101, 'driver101@network.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Driver 101'),
(102, 'driver102@network.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Driver 102'),
(103, 'driver103@network.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Driver 103'),
(104, 'demo@network.comtest', '$2y$10$oOLRaUQFyC49xiaNtO73ZO4MxrbDYtb7fTFCiUB3QkHrmgXc0BwVu', 'passenger', 'Passenger 104');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `driver_profiles`
--
ALTER TABLE `driver_profiles`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `passenger_demand`
--
ALTER TABLE `passenger_demand`
  ADD PRIMARY KEY (`passenger_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`);

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
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
