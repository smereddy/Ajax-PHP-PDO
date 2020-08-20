-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Dec 17, 2018 at 09:43 PM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `back_log_db`
--
CREATE DATABASE IF NOT EXISTS `backlog` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `backlog`;

-- --------------------------------------------------------

--
-- Table structure for table `back_log`
--
CREATE TABLE `back_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestor_id` varchar(10) DEFAULT NULL,
  `tool_name` varchar(45) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `priority` varchar(10) DEFAULT NULL,
  `date_filed` date DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `fix_confirm` tinyint(4) DEFAULT NULL,
  `date_closed` date DEFAULT NULL,
  `tester` varchar(15) DEFAULT NULL,
  `image_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Indexes for table `back_log`
--
ALTER TABLE `back_log`
  ADD PRIMARY KEY (`id`);
--
-- AUTO_INCREMENT for table `back_log`
--
ALTER TABLE `back_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Backlog ID Number - Primary Key';


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
