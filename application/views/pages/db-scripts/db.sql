-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 09, 2013 at 03:30 AM
-- Server version: 5.5.23
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DELIMITER $$

-- --------------------------------------------------------

--
-- Table structure for table `fb_hometowns`
--

CREATE TABLE IF NOT EXISTS `fb_hometowns` (
  `hometown_id` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`hometown_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_locations`
--

CREATE TABLE IF NOT EXISTS `fb_locations` (
  `location_id` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_schools`
--

CREATE TABLE IF NOT EXISTS `fb_schools` (
  `school_id` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_users`
--

CREATE TABLE IF NOT EXISTS `fb_users` (
  `fb_id` varchar(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `link` varchar(200) NOT NULL,
  `username` varchar(100) NOT NULL,
  `hometown_id` varchar(30) DEFAULT NULL,
  `location_id` varchar(30) DEFAULT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `updated_time` varchar(30) NOT NULL,
  PRIMARY KEY (`fb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_user_education`
--

CREATE TABLE IF NOT EXISTS `fb_user_education` (
  `fb_id` varchar(30) NOT NULL,
  `school_id` varchar(30) NOT NULL,
  `year` varchar(4) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`fb_id`,`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_user_users`
--

CREATE TABLE IF NOT EXISTS `fb_user_users` (
  `user_id` int(11) NOT NULL,
  `fb_id` varchar(30) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
