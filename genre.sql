-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 11, 2011 at 11:21 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `SkNd_test2`
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
  `slug` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42911CFC4FBBC852` (`mediaType_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

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
(14, 1, '501912', '', 'Science fiction and Fantasy', 'science-fiction-and-fantasy'),
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
(49, 3, '', '', 'All Music', 'all-music'),
(50, 1, '', '', 'All Films', 'all-films');

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
