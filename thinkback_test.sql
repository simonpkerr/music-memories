-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 23, 2011 at 09:26 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `thinkback_test`
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `decade`
--

INSERT INTO `decade` (`id`, `decadeName`, `amazonBrowseNodeId`, `sevenDigitalTag`) VALUES
(1, 1930, '562085011', ''),
(2, 1940, '562086011', ''),
(3, 1950, '562087011', '1950s'),
(4, 1960, '562088011', '1960s'),
(5, 1970, '562089011', '1970s'),
(6, 1980, '562090011', '1980s'),
(7, 1990, '562091011', '1990s'),
(8, 2000, '562092011', '2000s');

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
  PRIMARY KEY (`id`),
  KEY `IDX_42911CFC4FBBC852` (`mediaType_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `mediaType_id`, `amazonBrowseNodeId`, `sevenDigitalTag`, `genreName`) VALUES
(1, 1, '501778', '', 'Action and Adventure'),
(2, 1, '1025436', '', 'Adult'),
(3, 1, '3336941', '', 'Anime'),
(4, 1, '501858', '', 'Childrens'),
(5, 1, '501976', '', 'Classics'),
(6, 1, '501866', '', 'Comedy'),
(7, 1, '501880', '', 'Crime, Thrillers and Mystery'),
(8, 1, '501958', '', 'Documentary'),
(9, 1, '501872', '', 'Drama'),
(10, 1, '162441011', '', 'Horror'),
(11, 1, '162440011', '', 'Interactive'),
(12, 1, '501888', '', 'Music'),
(13, 1, '1108824', '', 'Musicals and Classical'),
(14, 1, '501912', '', 'Science fiction and Fantasy'),
(15, 1, '295573011', '', 'Sports'),
(16, 2, '546262031', '', 'Action'),
(17, 2, '285270', '', 'All television'),
(18, 2, '501960', '', 'Childrens TV'),
(19, 2, '501962', '', 'Comedy'),
(20, 2, '16281381', '', 'Crime, Thrillers and Mystery'),
(21, 2, '501966', '', 'Drama'),
(22, 2, '501970', '', 'Horror'),
(23, 2, '503424', '', 'Music and Entertainment'),
(24, 2, '503420', '', 'Natural world'),
(25, 2, '501972', '', 'Science fiction and Fantasy'),
(26, 2, '501974', '', 'Soaps'),
(27, 2, '12970731', '', 'TV Series'),
(28, 3, '', 'alternative-indie', 'Alternative and Indie'),
(29, 3, '', 'blues', 'Blues'),
(30, 3, '', 'children', 'Childrens Music'),
(31, 3, '', 'classical', 'Classical'),
(32, 3, '', 'country', 'Country'),
(33, 3, '', 'dance', 'Dance'),
(34, 3, '', 'electronic', 'Electronic'),
(35, 3, '', 'easy-listening', 'Easy Listening'),
(36, 3, '', 'folk', 'Folk and Songwriter'),
(37, 3, '', 'hard-rock-metal', 'Hard Rock and Metal'),
(38, 3, '', 'jazz', 'Jazz'),
(39, 3, '', 'pop', 'Pop'),
(40, 3, '', 'britpop', 'Britpop'),
(41, 3, '', 'randb-soul', 'R and B Soul'),
(42, 3, '', 'hip-hop-rap', 'Rap and Hip-hop'),
(43, 3, '', 'reggae', 'Reggae'),
(44, 3, '', 'rock', 'Rock'),
(45, 3, '', 'rock-and-roll', 'Rock and Roll'),
(46, 3, '', 'indie-rock', 'Indie'),
(47, 3, '', 'soundtrack', 'Soundtracks'),
(48, 3, '', 'world', 'World Music');

-- --------------------------------------------------------

--
-- Table structure for table `mediatype`
--

CREATE TABLE IF NOT EXISTS `mediatype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaName` varchar(75) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `mediaNameSlug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mediatype`
--

INSERT INTO `mediatype` (`id`, `mediaName`, `amazonBrowseNodeId`, `mediaNameSlug`) VALUES
(1, 'Film', '283926', 'film'),
(2, 'TV', '342350011', 'tv'),
(3, 'Music', '', 'music');

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
