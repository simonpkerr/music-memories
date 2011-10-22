-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 22, 2011 at 12:52 AM
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
-- Table structure for table `apitypes`
--

CREATE TABLE IF NOT EXISTS `apitypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apiName` varchar(75) NOT NULL,
  `apiHost` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `apitypes`
--


-- --------------------------------------------------------

--
-- Table structure for table `decades`
--

CREATE TABLE IF NOT EXISTS `decades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decade` int(11) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `7DigitalTag` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `decades`
--

INSERT INTO `decades` (`id`, `decade`, `amazonBrowseNodeId`, `7DigitalTag`) VALUES
(1, 1930, '562085011', NULL),
(2, 1940, '562086011', NULL),
(3, 1950, '562087011', '1950s'),
(4, 1960, '562088011', '1960s'),
(5, 1970, '562089011', '1970s'),
(6, 1980, '562090011', '1980s'),
(7, 1990, '562091011', '1990s'),
(8, 2000, '562092011', '2000s');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE IF NOT EXISTS `genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `genreTitle` varchar(150) NOT NULL,
  `mediaTypeId` int(11) NOT NULL,
  `amazonBrowseNodeId` varchar(10) DEFAULT NULL,
  `7DigitalTag` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `genreTitle`, `mediaTypeId`, `amazonBrowseNodeId`, `7DigitalTag`) VALUES
(1, 'Action and Adventure', 1, '501778', NULL),
(2, 'Adult', 1, '1025436', NULL),
(3, 'Anime', 1, '3336941', NULL),
(4, 'childrens', 1, '501858', NULL),
(5, 'Classics', 1, '501976', NULL),
(6, 'comedy', 1, '501866', NULL),
(7, 'Crime, Thrillers and Mystery', 1, '501880', NULL),
(8, 'Documentary', 1, '501958', NULL),
(9, 'Drama', 1, '501872', NULL),
(10, 'Horror', 1, '162441011', NULL),
(11, 'Interactive', 1, '162440011', NULL),
(12, 'Music', 1, '501888', NULL),
(13, 'Musicals and Classical', 1, '1108824', NULL),
(14, 'Science fiction and Fantasy: ', 1, '501912', NULL),
(15, 'Sports', 1, '295573011', NULL),
(16, 'Action', 2, '546262031', NULL),
(17, 'All television', 2, '285270', NULL),
(18, 'Childrens TV', 2, '501960', NULL),
(19, 'Comedy', 2, '501962', NULL),
(20, 'Crime, Thrillers and Mystery', 2, '16281381', NULL),
(21, 'Drama', 2, '501966', NULL),
(22, 'Horror', 2, '501970', NULL),
(23, 'Music and Entertainment', 2, '503424', NULL),
(24, 'Natural world', 2, '503420', NULL),
(25, 'Science fiction and Fantasy', 2, '501972', NULL),
(26, 'Soaps', 2, '501974', NULL),
(27, 'TV Series', 2, '12970731', NULL),
(28, 'Alternative and Indie', 3, NULL, 'alternative-indie'),
(29, 'Blues', 3, NULL, 'blues'),
(30, 'Childrens Music', 3, NULL, 'children'),
(31, 'Classical', 3, NULL, 'classical'),
(32, 'Country', 3, NULL, 'country'),
(33, 'Dance', 3, NULL, 'dance'),
(34, 'Electronic', 3, NULL, 'electronic'),
(35, 'Easy Listening', 3, NULL, 'easy-listening'),
(36, 'Folk and Songwriter', 3, NULL, 'folk'),
(37, 'Hard Rock and Metal', 3, NULL, 'hard-rock-metal'),
(38, 'Jazz', 3, NULL, 'jazz'),
(39, 'Pop', 3, NULL, 'pop'),
(40, 'Britpop', 3, NULL, 'britpop'),
(41, 'R and B Soul', 3, NULL, 'randb-soul'),
(42, 'Rap and Hip-hop', 3, NULL, 'hip-hop-rap'),
(43, 'Reggae', 3, NULL, 'reggae'),
(44, 'Rock', 3, NULL, 'rock'),
(45, 'Rock and Roll', 3, NULL, 'rock-and-roll'),
(46, 'Indie', 3, NULL, 'indie-rock'),
(47, 'Soundtracks', 3, NULL, 'soundtrack'),
(48, 'World Music', 3, NULL, 'world');

-- --------------------------------------------------------

--
-- Table structure for table `mediatypes`
--

CREATE TABLE IF NOT EXISTS `mediatypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaName` varchar(75) NOT NULL,
  `amazonBrowseNodeId` varchar(10) DEFAULT NULL,
  `mediaNameSlug` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mediatypes`
--

INSERT INTO `mediatypes` (`id`, `mediaName`, `amazonBrowseNodeId`, `mediaNameSlug`) VALUES
(1, 'Film', '283926', 'film'),
(2, 'TV', '342350011', 'tv'),
(3, 'Music', NULL, 'music');

-- --------------------------------------------------------

--
-- Table structure for table `recommendeditems`
--

CREATE TABLE IF NOT EXISTS `recommendeditems` (
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
-- Dumping data for table `recommendeditems`
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

