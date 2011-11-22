-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 22, 2011 at 05:45 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ThinkBack_test2`
--

-- --------------------------------------------------------

--
-- Table structure for table `apitype`
--

CREATE TABLE IF NOT EXISTS `apitype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apiName` varchar(75) NOT NULL,
  `apiHost` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `apitype`
--


-- --------------------------------------------------------

--
-- Table structure for table `decade`
--

CREATE TABLE IF NOT EXISTS `decade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decadeName` int(11) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `sevenDigitalTag` varchar(10) NOT NULL,
  `slug` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `decade`
--

INSERT INTO `decade` (`id`, `decadeName`, `amazonBrowseNodeId`, `sevenDigitalTag`, `slug`) VALUES
(1, 1930, '562085011', '', '1930'),
(2, 1940, '562086011', '', '1940'),
(3, 1950, '562087011', '1950s', '1950'),
(4, 1960, '562088011', '1960s', '1960'),
(5, 1970, '562089011', '1970s', '1970'),
(6, 1980, '562090011', '1980s', '1980'),
(7, 1990, '562091011', '1990s', '1990'),
(8, 2000, '562092011', '2000s', '2000');

-- --------------------------------------------------------

--
-- Table structure for table `ext_log_entries`
--

CREATE TABLE IF NOT EXISTS `ext_log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) NOT NULL,
  `logged_at` datetime NOT NULL,
  `object_id` varchar(32) DEFAULT NULL,
  `object_class` varchar(255) NOT NULL,
  `version` int(11) NOT NULL,
  `data` longtext COMMENT '(DC2Type:array)',
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ext_log_entries`
--


-- --------------------------------------------------------

--
-- Table structure for table `ext_translations`
--

CREATE TABLE IF NOT EXISTS `ext_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locale` varchar(8) NOT NULL,
  `object_class` varchar(255) NOT NULL,
  `field` varchar(32) NOT NULL,
  `foreign_key` varchar(64) NOT NULL,
  `content` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_unique_idx` (`locale`,`object_class`,`foreign_key`,`field`),
  KEY `translations_lookup_idx` (`locale`,`object_class`,`foreign_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ext_translations`
--


-- --------------------------------------------------------

--
-- Table structure for table `fos_user`
--

CREATE TABLE IF NOT EXISTS `fos_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `username_canonical` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_canonical` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `algorithm` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `firstname` varchar(65) NOT NULL,
  `surname` varchar(65) NOT NULL,
  `dateofbirth` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `fos_user`
--


-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE IF NOT EXISTS `genre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaType_id` int(11) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `sevenDigitalTag` varchar(50) NOT NULL,
  `genreName` varchar(100) NOT NULL,
  `slug` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42911CFC4FBBC852` (`mediaType_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=50 ;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `mediaType_id`, `amazonBrowseNodeId`, `sevenDigitalTag`, `genreName`, `slug`) VALUES
(28, 3, '231193', 'alternative-indie', 'Alternative and Indie', 'alternative-and-indie'),
(29, 3, '557264', 'blues', 'Blues', 'blues'),
(30, 3, '499368', 'children', 'Childrens Music', 'childrens-music'),
(31, 3, '697386', 'classical', 'Classical', 'classical'),
(32, 3, '231177', 'country', 'Country', 'country'),
(33, 3, '231183', 'dance', 'Dance', 'dance'),
(34, 3, '231183', 'electronic', 'Electronic', 'electronic'),
(35, 3, '231219', 'easy-listening', 'Easy Listening', 'easy-listening'),
(36, 3, '358899031', 'folk', 'Folk and Songwriter', 'folk-and-songwriter'),
(37, 3, '690892', 'hard-rock-metal', 'Hard Rock and Metal', 'hard-rock-and-metal'),
(38, 3, '231201', 'jazz', 'Jazz', 'jazz'),
(39, 3, '694208', 'pop', 'Pop', 'pop'),
(40, 3, '694208', 'britpop', 'Britpop', 'britpop'),
(41, 3, '754576', 'randb-soul', 'R and B Soul', 'r-and-b-soul'),
(42, 3, '754574', 'hip-hop-rap', 'Rap and Hip-hop', 'rap-and-hip-hop'),
(43, 3, '13878751', 'reggae', 'Reggae', 'reggae'),
(44, 3, '231239', 'rock', 'Rock', 'rock'),
(45, 3, '', 'rock-and-roll', 'Rock and Roll', 'rock-and-roll'),
(46, 3, '', 'indie-rock', 'Indie', 'indie'),
(47, 3, '231249', 'soundtrack', 'Soundtracks', 'soundtracks'),
(48, 3, '231254', 'world', 'World Music', 'world-music'),
(49, 3, '', '', 'All Music', 'all-music');

-- --------------------------------------------------------

--
-- Table structure for table `mediatype`
--

CREATE TABLE IF NOT EXISTS `mediatype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaName` varchar(75) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `slug` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mediatype`
--

INSERT INTO `mediatype` (`id`, `mediaName`, `amazonBrowseNodeId`, `slug`) VALUES
(3, 'Music', '520920', 'music');

-- --------------------------------------------------------

--
-- Table structure for table `recommendeditem`
--

CREATE TABLE IF NOT EXISTS `recommendeditem` (
  `id` int(11) NOT NULL,
  `itemId` varchar(50) NOT NULL,
  `mediaTypeId` int(11) NOT NULL,
  `apiId` int(11) NOT NULL,
  `decadeId` int(11) NOT NULL,
  `genreId` int(11) NOT NULL,
  `selectedCount` int(11) NOT NULL,
  `lastUpdated` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `recommendeditem`
--


-- --------------------------------------------------------

--
-- Table structure for table `yearofbirthlinktable`
--

CREATE TABLE IF NOT EXISTS `yearofbirthlinktable` (
  `id` int(11) NOT NULL,
  `yearOfBirth` int(11) NOT NULL,
  `itemId` varchar(50) NOT NULL,
  `selectedCount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `yearofbirthlinktable`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `genre`
--
ALTER TABLE `genre`
  ADD CONSTRAINT `FK_42911CFC4FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`);
