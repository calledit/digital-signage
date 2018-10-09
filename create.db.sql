-- phpMyAdmin SQL Dump
-- version 4.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 09, 2018 at 05:00 PM
-- Server version: 5.5.58-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `storevideo`
--
CREATE DATABASE IF NOT EXISTS `storevideo` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `storevideo`;

-- --------------------------------------------------------

--
-- Table structure for table `playergroups`
--

CREATE TABLE IF NOT EXISTS `playergroups` (
  `_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(10) unsigned NOT NULL COMMENT 'owner of the player group',
  `public` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'can all users see this',
  `comment` varchar(2048) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `_id` int(10) unsigned NOT NULL,
  `hardwareid` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `mainplayergroup` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL COMMENT 'owner of the player',
  `lastcheckin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `screen_active` tinyint(1) NOT NULL DEFAULT '0',
  `screen_manufacturer_name` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `screen_name` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `screen_product_code` int(11) DEFAULT NULL,
  `screen_width` int(11) DEFAULT NULL,
  `screen_height` int(11) DEFAULT NULL,
  `lastip` varchar(120) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the ip the player last connected with',
  `exec` varchar(3000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'command that the player will execute on next checkin'
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_owners`
--

CREATE TABLE IF NOT EXISTS `player_owners` (
  `_id` int(10) unsigned NOT NULL,
  `player` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin` tinyint(1) NOT NULL COMMENT 'is the user an admin',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `playergroups`
--
ALTER TABLE `playergroups`
  ADD PRIMARY KEY (`_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`_id`), ADD KEY `mainplayergroup` (`mainplayergroup`);

--
-- Indexes for table `player_owners`
--
ALTER TABLE `player_owners`
  ADD PRIMARY KEY (`_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`_id`), ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `playergroups`
--
ALTER TABLE `playergroups`
  MODIFY `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=176;
--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=86;
--
-- AUTO_INCREMENT for table `player_owners`
--
ALTER TABLE `player_owners`
  MODIFY `_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `players`
--
ALTER TABLE `players`
ADD CONSTRAINT `A player needs a main player group` FOREIGN KEY (`mainplayergroup`) REFERENCES `playergroups` (`_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
