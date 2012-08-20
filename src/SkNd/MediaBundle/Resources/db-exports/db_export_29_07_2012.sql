-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2012 at 10:22 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sknd_test2`
--

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `host` varchar(255) DEFAULT NULL,
  `friendlyName` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `api`
--

INSERT INTO `api` (`id`, `name`, `host`, `friendlyName`) VALUES
(1, 'amazonapi', NULL, 'Amazon'),
(2, 'youtubeapi', NULL, 'YouTube');

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
(1, 1930, '542166011', '', '1930s'),
(2, 1940, '542165011', '', '1940s'),
(3, 1950, '542164011', '1950s', '1950s'),
(4, 1960, '542163011', '1960s', '1960s'),
(5, 1970, '542162011', '1970s', '1970'),
(6, 1980, '542161011', '1980s', '1980s'),
(7, 1990, '542160011', '1990s', '1990s'),
(8, 2000, '542159011', '2000s', '2000s'),
(9, 2010, '535457031', '', '2010s');

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
  `firstname` varchar(80) DEFAULT NULL,
  `surname` varchar(80) DEFAULT NULL,
  `dateofbirth` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=347 ;

--
-- Dumping data for table `fos_user`
--

INSERT INTO `fos_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `firstname`, `surname`, `dateofbirth`) VALUES
(344, 'testuser', 'testuser', 'test@test.com', 'test@test.com', 1, 'md6fstuqbfkkcows8kssc4ggs8o8ggg', 'OEUpTQq9yW/xMpY/nfnW6s7lwpmd33YHD55Lqc3eaKugWW4zFCMs4sS/JrExrLBczwJvrTyxwIMIo4avVBnRoA==', '2012-07-17 18:06:32', 0, 0, NULL, '2cgoysmz2pz4gww4sk8ss4gcs8g40gwc4wc0ssg0soc4s8gso0', NULL, 'a:0:{}', 0, NULL, NULL, NULL, '2012-07-17'),
(345, 'testuser2', 'testuser2', 'test2@test.com', 'test2@test.com', 1, '2g4bj0h98m68cow080gc0c0kgc880ow', 'pMngkjMk7kgMKykHJAeO2jVyDviPNs3YJGxwjFp7O9C8FGnjsDMfV0kgtXANsGnHleGbi2wqD67SA0T8HdfEWA==', '2012-07-17 18:05:25', 0, 0, NULL, '18vf3l9on38k04kcswc4ogs80scw4oo8sgkws4wcs4gg0ococc', NULL, 'a:0:{}', 0, NULL, NULL, NULL, '2012-07-17'),
(346, 'testuser3', 'testuser3', 'test3@test3.com', 'test3@test3.com', 1, 'o7l2grrhmm84kkosgo484o8wowsg0g0', 'PrekatIISKL0evxmBGWAjb8VA4sOmrQWUXMwZ5X8nHMpicRriXG1/FjmKbOuYOBtZJgArPSzPLlFhmUvA+OLuw==', NULL, 0, 0, NULL, '5zrykykoelss444gww0080kgk8sww0c80ccsc84w48gooks0ww', NULL, 'a:0:{}', 0, NULL, NULL, NULL, '2012-07-17');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=52 ;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `mediaType_id`, `amazonBrowseNodeId`, `sevenDigitalTag`, `genreName`, `slug`) VALUES
(1, 1, '501778', '', 'Action and Adventure', 'action-and-adventure'),
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
-- Table structure for table `mediaresource`
--

CREATE TABLE IF NOT EXISTS `mediaresource` (
  `id` varchar(255) NOT NULL,
  `api_id` int(11) DEFAULT NULL,
  `decade_id` int(11) DEFAULT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `viewCount` int(11) NOT NULL,
  `selectedCount` int(11) DEFAULT NULL,
  `lastUpdated` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `mediaResourceCache_id` varchar(255) DEFAULT NULL,
  `mediaType_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E306BA81685AE93D` (`mediaResourceCache_id`),
  KEY `IDX_E306BA8154963938` (`api_id`),
  KEY `IDX_E306BA814FBBC852` (`mediaType_id`),
  KEY `IDX_E306BA81FF312AC0` (`decade_id`),
  KEY `IDX_E306BA814296D31F` (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mediaresourcecache`
--

CREATE TABLE IF NOT EXISTS `mediaresourcecache` (
  `id` varchar(255) NOT NULL,
  `xmlData` longtext NOT NULL,
  `dateCreated` datetime NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `imageUrl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mediaresourcelistingscache`
--

CREATE TABLE IF NOT EXISTS `mediaresourcelistingscache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL,
  `decade_id` int(11) DEFAULT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `page` int(11) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `xmlData` longtext NOT NULL,
  `dateCreated` datetime NOT NULL,
  `mediaType_id` int(11) DEFAULT NULL,
  `computedKeywords` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_623D23F154963938` (`api_id`),
  KEY `IDX_623D23F14FBBC852` (`mediaType_id`),
  KEY `IDX_623D23F1FF312AC0` (`decade_id`),
  KEY `IDX_623D23F14296D31F` (`genre_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

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
-- Table structure for table `memorywall`
--

CREATE TABLE IF NOT EXISTS `memorywall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `associatedDecade_id` int(11) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `isPublic` tinyint(1) DEFAULT NULL,
  `lastUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8881184B22AA2175` (`associatedDecade_id`),
  KEY `IDX_8881184BA76ED395` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=527 ;

--
-- Dumping data for table `memorywall`
--

INSERT INTO `memorywall` (`id`, `user_id`, `name`, `slug`, `description`, `associatedDecade_id`, `dateCreated`, `isPublic`, `lastUpdated`) VALUES
(523, 345, 'My Memory Wall', 'my-memory-wall-1', NULL, NULL, '2012-07-17 18:04:36', 1, '2012-07-17 18:04:36'),
(524, 346, 'My Memory Wall', 'my-memory-wall-2', NULL, NULL, '2012-07-17 18:04:36', 1, '2012-07-17 18:04:36'),
(525, 345, 'test memory wall', 'test-memory-wall', NULL, NULL, '2012-07-17 18:05:31', 1, '2012-07-17 18:05:31'),
(526, 344, 'My Memory Wall', 'my-memory-wall-3', NULL, NULL, '2012-07-17 18:06:35', 1, '2012-07-17 18:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `memorywallmediaresource`
--

CREATE TABLE IF NOT EXISTS `memorywallmediaresource` (
  `userTitle` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `dateAdded` datetime NOT NULL,
  `mediaResource_id` varchar(255) NOT NULL,
  `memoryWall_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  PRIMARY KEY (`mediaResource_id`,`memoryWall_id`),
  KEY `IDX_F0EBAC21B154C783` (`memoryWall_id`),
  KEY `IDX_F0EBAC2166C02C1E` (`mediaResource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `genre`
--
ALTER TABLE `genre`
  ADD CONSTRAINT `FK_42911CFC4FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`);

--
-- Constraints for table `mediaresource`
--
ALTER TABLE `mediaresource`
  ADD CONSTRAINT `FK_E306BA814296D31F` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`),
  ADD CONSTRAINT `FK_E306BA814FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`),
  ADD CONSTRAINT `FK_E306BA8154963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_E306BA81685AE93D` FOREIGN KEY (`mediaResourceCache_id`) REFERENCES `mediaresourcecache` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_E306BA81FF312AC0` FOREIGN KEY (`decade_id`) REFERENCES `decade` (`id`);

--
-- Constraints for table `mediaresourcelistingscache`
--
ALTER TABLE `mediaresourcelistingscache`
  ADD CONSTRAINT `FK_623D23F14296D31F` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`),
  ADD CONSTRAINT `FK_623D23F14FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`),
  ADD CONSTRAINT `FK_623D23F154963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_623D23F1FF312AC0` FOREIGN KEY (`decade_id`) REFERENCES `decade` (`id`);

--
-- Constraints for table `memorywall`
--
ALTER TABLE `memorywall`
  ADD CONSTRAINT `FK_8881184B22AA2175` FOREIGN KEY (`associatedDecade_id`) REFERENCES `decade` (`id`),
  ADD CONSTRAINT `FK_8881184BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`);

--
-- Constraints for table `memorywallmediaresource`
--
ALTER TABLE `memorywallmediaresource`
  ADD CONSTRAINT `FK_F0EBAC2166C02C1E` FOREIGN KEY (`mediaResource_id`) REFERENCES `mediaresource` (`id`),
  ADD CONSTRAINT `FK_F0EBAC21B154C783` FOREIGN KEY (`memoryWall_id`) REFERENCES `memorywall` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
