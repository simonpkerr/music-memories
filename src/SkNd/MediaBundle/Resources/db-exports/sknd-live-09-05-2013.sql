-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2013 at 10:06 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sknd`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl_classes`
--

CREATE TABLE IF NOT EXISTS `acl_classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_69DD750638A36066` (`class_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `acl_entries`
--

CREATE TABLE IF NOT EXISTS `acl_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `object_identity_id` int(10) unsigned DEFAULT NULL,
  `security_identity_id` int(10) unsigned NOT NULL,
  `field_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ace_order` smallint(5) unsigned NOT NULL,
  `mask` int(11) NOT NULL,
  `granting` tinyint(1) NOT NULL,
  `granting_strategy` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `audit_success` tinyint(1) NOT NULL,
  `audit_failure` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4` (`class_id`,`object_identity_id`,`field_name`,`ace_order`),
  KEY `IDX_46C8B806EA000B103D9AB4A6DF9183C9` (`class_id`,`object_identity_id`,`security_identity_id`),
  KEY `IDX_46C8B806EA000B10` (`class_id`),
  KEY `IDX_46C8B8063D9AB4A6` (`object_identity_id`),
  KEY `IDX_46C8B806DF9183C9` (`security_identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `acl_object_identities`
--

CREATE TABLE IF NOT EXISTS `acl_object_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_object_identity_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `object_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `entries_inheriting` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9407E5494B12AD6EA000B10` (`object_identifier`,`class_id`),
  KEY `IDX_9407E54977FA751A` (`parent_object_identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `acl_object_identity_ancestors`
--

CREATE TABLE IF NOT EXISTS `acl_object_identity_ancestors` (
  `object_identity_id` int(10) unsigned NOT NULL,
  `ancestor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`object_identity_id`,`ancestor_id`),
  KEY `IDX_825DE2993D9AB4A6` (`object_identity_id`),
  KEY `IDX_825DE299C671CEA1` (`ancestor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `acl_security_identities`
--

CREATE TABLE IF NOT EXISTS `acl_security_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `username` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8835EE78772E836AF85E0677` (`identifier`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

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
(5, 1970, '542162011', '1970s', '1970s'),
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
  `firstname` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `date_of_birth` datetime DEFAULT NULL,
  `lastname` varchar(64) DEFAULT NULL,
  `website` varchar(64) DEFAULT NULL,
  `biography` varchar(255) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `locale` varchar(8) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `facebook_uid` varchar(255) DEFAULT NULL,
  `facebook_name` varchar(255) DEFAULT NULL,
  `facebook_data` longtext COMMENT '(DC2Type:json)',
  `twitter_uid` varchar(255) DEFAULT NULL,
  `twitter_name` varchar(255) DEFAULT NULL,
  `twitter_data` longtext COMMENT '(DC2Type:json)',
  `gplus_uid` varchar(255) DEFAULT NULL,
  `gplus_name` varchar(255) DEFAULT NULL,
  `gplus_data` longtext COMMENT '(DC2Type:json)',
  `token` varchar(255) DEFAULT NULL,
  `two_step_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=350 ;

-- --------------------------------------------------------

--
-- Table structure for table `fos_user_group`
--

CREATE TABLE IF NOT EXISTS `fos_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_583D1F3E5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fos_user_user_group`
--

CREATE TABLE IF NOT EXISTS `fos_user_user_group` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `IDX_B3C77447A76ED395` (`user_id`),
  KEY `IDX_B3C77447FE54D947` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `lastModified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_623D23F154963938` (`api_id`),
  KEY `IDX_623D23F14FBBC852` (`mediaType_id`),
  KEY `IDX_623D23F1FF312AC0` (`decade_id`),
  KEY `IDX_623D23F14296D31F` (`genre_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

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
  `dateCreated` datetime NOT NULL,
  `isPublic` tinyint(1) DEFAULT NULL,
  `lastUpdated` datetime NOT NULL,
  `associatedDecade_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8881184B22AA2175` (`associatedDecade_id`),
  KEY `IDX_8881184BA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `memorywallmediaresource`
--

CREATE TABLE IF NOT EXISTS `memorywallmediaresource` (
  `mediaResource_id` varchar(255) NOT NULL,
  `api_id` int(11) NOT NULL,
  `userTitle` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `dateAdded` datetime NOT NULL,
  `memoryWall_id` int(11) NOT NULL,
  PRIMARY KEY (`mediaResource_id`,`memoryWall_id`),
  KEY `IDX_F0EBAC2166C02C1E` (`mediaResource_id`),
  KEY `IDX_F0EBAC21B154C783` (`memoryWall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acl_entries`
--
ALTER TABLE `acl_entries`
  ADD CONSTRAINT `FK_46C8B806DF9183C9` FOREIGN KEY (`security_identity_id`) REFERENCES `acl_security_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_46C8B8063D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_46C8B806EA000B10` FOREIGN KEY (`class_id`) REFERENCES `acl_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `acl_object_identities`
--
ALTER TABLE `acl_object_identities`
  ADD CONSTRAINT `FK_9407E54977FA751A` FOREIGN KEY (`parent_object_identity_id`) REFERENCES `acl_object_identities` (`id`);

--
-- Constraints for table `acl_object_identity_ancestors`
--
ALTER TABLE `acl_object_identity_ancestors`
  ADD CONSTRAINT `FK_825DE299C671CEA1` FOREIGN KEY (`ancestor_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_825DE2993D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fos_user_user_group`
--
ALTER TABLE `fos_user_user_group`
  ADD CONSTRAINT `FK_B3C77447FE54D947` FOREIGN KEY (`group_id`) REFERENCES `fos_user_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_B3C77447A76ED395` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `FK_67EEB4A522AA2175` FOREIGN KEY (`associatedDecade_id`) REFERENCES `decade` (`id`) ON DELETE SET NULL,
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
