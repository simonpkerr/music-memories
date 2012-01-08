-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 08, 2012 at 08:39 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `thinkback_test2`
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `decade`
--

INSERT INTO `decade` (`id`, `decadeName`, `amazonBrowseNodeId`, `sevenDigitalTag`, `slug`) VALUES
(1, 1930, '542166011', '', '1930'),
(2, 1940, '542165011', '', '1940'),
(3, 1950, '542164011', '1950s', '1950'),
(4, 1960, '542163011', '1960s', '1960'),
(5, 1970, '542162011', '1970s', '1970'),
(6, 1980, '542161011', '1980s', '1980'),
(7, 1990, '542160011', '1990s', '1990'),
(8, 2000, '542159011', '2000s', '2000'),
(9, 2010, '535457031', '', '2010');

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
  `slug` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42911CFC4FBBC852` (`mediaType_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=52 ;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `mediaType_id`, `amazonBrowseNodeId`, `sevenDigitalTag`, `genreName`, `slug`) VALUES
(1, 1, '501778', '', 'Action and Adventure', 'action-and-adventure'),
(2, 1, '1025436', '', 'Adult', 'adult'),
(3, 1, '3336941', '', 'Anime', 'anime'),
(4, 1, '501858', '', 'Childrens', 'childrens'),
(5, 1, '501976', '', 'Classics', 'classics'),
(6, 1, '501866', '', 'Comedy', 'comedy'),
(7, 1, '501880', '', 'Crime, Thrillers and Mystery', 'crime-thrillers-and-mystery'),
(8, 1, '501958', '', 'Documentary', 'documentary'),
(9, 1, '501872', '', 'Drama', 'drama'),
(10, 1, '162441011', '', 'Horror', 'horror'),
(11, 1, '162440011', '', 'Interactive', 'interactive'),
(12, 1, '501888', '', 'Music', 'music'),
(13, 1, '1108824', '', 'Musicals and Classical', 'musicals-and-classical'),
(14, 1, '501916', '', 'Science fiction', 'science-fiction'),
(15, 1, '295573011', '', 'Sports', 'sports'),
(16, 2, '546262031', '', 'Action', 'action'),
(17, 2, '285270', '', 'All television', 'all-television'),
(18, 2, '501960', '', 'Childrens TV', 'childrens-tv'),
(19, 2, '501962', '', 'Comedy', 'comedy'),
(20, 2, '16281381', '', 'Crime, Thrillers and Mystery', 'crime-thrillers-and-mystery'),
(21, 2, '501966', '', 'Drama', 'drama'),
(22, 2, '501970', '', 'Horror', 'horror'),
(23, 2, '503424', '', 'Music and Entertainment', 'music-and-entertainment'),
(24, 2, '503420', '', 'Natural world', 'natural-world'),
(25, 2, '501972', '', 'Science fiction and Fantasy', 'science-fiction-and-fantasy'),
(26, 2, '501974', '', 'Soaps', 'soaps'),
(27, 2, '12970731', '', 'TV Series', 'tv-series'),
(28, 3, '', 'alternative-indie', 'Alternative and Indie', 'alternative-and-indie'),
(29, 3, '', 'blues', 'Blues', 'blues'),
(30, 3, '', 'children', 'Childrens Music', 'childrens-music'),
(31, 3, '', 'classical', 'Classical', 'classical'),
(32, 3, '', 'country', 'Country', 'country'),
(33, 3, '', 'dance', 'Dance', 'dance'),
(34, 3, '', 'electronic', 'Electronic', 'electronic'),
(35, 3, '', 'easy-listening', 'Easy Listening', 'easy-listening'),
(36, 3, '', 'folk', 'Folk and Songwriter', 'folk-and-songwriter'),
(37, 3, '', 'hard-rock-metal', 'Hard Rock and Metal', 'hard-rock-and-metal'),
(38, 3, '', 'jazz', 'Jazz', 'jazz'),
(39, 3, '', 'pop', 'Pop', 'pop'),
(40, 3, '', 'britpop', 'Britpop', 'britpop'),
(41, 3, '', 'randb-soul', 'R and B Soul', 'r-and-b-soul'),
(42, 3, '', 'hip-hop-rap', 'Rap and Hip-hop', 'rap-and-hip-hop'),
(43, 3, '', 'reggae', 'Reggae', 'reggae'),
(44, 3, '', 'rock', 'Rock', 'rock'),
(45, 3, '', 'rock-and-roll', 'Rock and Roll', 'rock-and-roll'),
(46, 3, '', 'indie-rock', 'Indie', 'indie'),
(47, 3, '', 'soundtrack', 'Soundtracks', 'soundtracks'),
(48, 3, '', 'world', 'World Music', 'world-music'),
(51, 1, '501914', '', 'Fantasy', 'fantasy');

-- --------------------------------------------------------

--
-- Table structure for table `mediatype`
--

CREATE TABLE IF NOT EXISTS `mediatype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaName` varchar(75) NOT NULL,
  `amazonBrowseNodeId` varchar(10) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `mediatype`
--

INSERT INTO `mediatype` (`id`, `mediaName`, `amazonBrowseNodeId`, `slug`) VALUES
(1, 'Film', '573406', 'film'),
(2, 'TV', '342350011', 'tv'),
(3, 'Music', '', 'music'),
(4, 'Film & TV', '283926', 'film-and-tv');

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
-- Constraints for dumped tables
--

--
-- Constraints for table `genre`
--
ALTER TABLE `genre`
  ADD CONSTRAINT `FK_42911CFC4FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
