-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 07, 2012 at 11:04 PM
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
-- Table structure for table `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apiName` varchar(100) NOT NULL,
  `host` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `api`
--

INSERT INTO `api` (`id`, `apiName`, `host`) VALUES
(1, 'amazonapi', NULL),
(2, 'youtubeapi', NULL),
(3, 'sevendigitalapi', NULL),
(4, 'gdataimagesapi', NULL);

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
  `slug` varchar(128) NOT NULL,
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
-- Table structure for table `mediaresource`
--

CREATE TABLE IF NOT EXISTS `mediaresource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decade_id` int(11) DEFAULT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `api_id` int(11) DEFAULT NULL,
  `itemId` varchar(50) NOT NULL,
  `viewCount` int(11) NOT NULL,
  `selectedCount` int(11) NOT NULL,
  `lastUpdated` datetime NOT NULL,
  `mediaType_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E306BA814FBBC852` (`mediaType_id`),
  KEY `IDX_E306BA81FF312AC0` (`decade_id`),
  KEY `IDX_E306BA814296D31F` (`genre_id`),
  KEY `IDX_E306BA8154963938` (`api_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `mediaresourcelistingscache`
--

CREATE TABLE IF NOT EXISTS `mediaresourcelistingscache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decade_id` int(11) DEFAULT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `api_id` int(11) DEFAULT NULL,
  `page` int(11) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `xmlData` longtext NOT NULL COMMENT '(DC2Type:object)',
  `dateCreated` datetime NOT NULL,
  `mediaType_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_623D23F14FBBC852` (`mediaType_id`),
  KEY `IDX_623D23F1FF312AC0` (`decade_id`),
  KEY `IDX_623D23F14296D31F` (`genre_id`),
  KEY `IDX_623D23F154963938` (`api_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mediaresourcelistingscache`
--

INSERT INTO `mediaresourcelistingscache` (`id`, `decade_id`, `genre_id`, `api_id`, `page`, `keywords`, `xmlData`, `dateCreated`, `mediaType_id`) VALUES
(1, NULL, NULL, 1, NULL, NULL, '<?xml version="1.0" ?>\r\n<ItemSearchResponse xmlns="http://webservices.amazon.com/AWSECommerceService/2009-03-31">\r\n  <OperationRequest>\r\n    <RequestId>49ce42ef-73e5-4b21-8d86-8511e87fdce2</RequestId>\r\n    <Arguments>\r\n      <Argument Name="Condition" Value="All"></Argument>\r\n      <Argument Name="Operation" Value="ItemSearch"></Argument>\r\n      <Argument Name="Service" Value="AWSECommerceService"></Argument>\r\n      <Argument Name="Signature" Value="n3mgH4Bx6WTlKHCuE0vkVqf7xDZcZn6yYnM7SY4lcn4="></Argument>\r\n      <Argument Name="MerchantId" Value="All"></Argument>\r\n      <Argument Name="ItemPage" Value="1"></Argument>\r\n      <Argument Name="AssociateTag" Value="thinkbackcouk-20"></Argument>\r\n      <Argument Name="BrowseNode" Value="542159011"></Argument>\r\n      <Argument Name="Version" Value="2009-03-31"></Argument>\r\n      <Argument Name="Sort" Value="salesrank"></Argument>\r\n      <Argument Name="Validate" Value="True"></Argument>\r\n      <Argument Name="AWSAccessKeyId" Value="AKIAJH6G4CGNH5FCPVHA"></Argument>\r\n      <Argument Name="Timestamp" Value="2011-12-15T13:21:31Z"></Argument>\r\n      <Argument Name="ResponseGroup" Value="Images,ItemAttributes,SalesRank,Request"></Argument>\r\n      <Argument Name="SearchIndex" Value="Video"></Argument>\r\n    </Arguments>\r\n    <RequestProcessingTime>0.2932520000000000</RequestProcessingTime>\r\n  </OperationRequest>\r\n  <Items>\r\n    <Request>\r\n      <IsValid>True</IsValid>\r\n      <ItemSearchRequest>\r\n        <BrowseNode>542159011</BrowseNode>\r\n        <Condition>All</Condition>\r\n        <ItemPage>1</ItemPage>\r\n        <MerchantId>Deprecated</MerchantId>\r\n        <ResponseGroup>Images</ResponseGroup>\r\n        <ResponseGroup>ItemAttributes</ResponseGroup>\r\n        <ResponseGroup>SalesRank</ResponseGroup>\r\n        <ResponseGroup>Request</ResponseGroup>\r\n        <SearchIndex>Video</SearchIndex>\r\n        <Sort>salesrank</Sort>\r\n      </ItemSearchRequest>\r\n    </Request>\r\n    <TotalResults>1</TotalResults>\r\n    <TotalPages>1</TotalPages>\r\n    <Item>\r\n    <ASIN>B00061S0QE</ASIN>\r\n    <DetailPageURL>http://www.amazon.co.uk/Elf-DVD-Will-Ferrell/dp/B00061S0QE%3FSubscriptionId%3DAKIAJH6G4CGNH5FCPVHA%26tag%3Dthinkbackcouk-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB00061S0QE</DetailPageURL>\r\n    <ItemLinks>\r\n      <ItemLink>\r\n        <Description>Add To Wishlist</Description>\r\n        <URL>http://www.amazon.co.uk/gp/registry/wishlist/add-item.html%3Fasin.0%3DB00061S0QE%26SubscriptionId%3DAKIAJH6G4CGNH5FCPVHA%26tag%3Dthinkbackcouk-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D12734%26creativeASIN%3DB00061S0QE</URL>\r\n      </ItemLink>\r\n      <ItemLink>\r\n        <Description>Tell A Friend</Description>\r\n        <URL>http://www.amazon.co.uk/gp/pdp/taf/B00061S0QE%3FSubscriptionId%3DAKIAJH6G4CGNH5FCPVHA%26tag%3Dthinkbackcouk-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D12734%26creativeASIN%3DB00061S0QE</URL>\r\n      </ItemLink>\r\n      <ItemLink>\r\n        <Description>All Customer Reviews</Description>\r\n        <URL>http://www.amazon.co.uk/review/product/B00061S0QE%3FSubscriptionId%3DAKIAJH6G4CGNH5FCPVHA%26tag%3Dthinkbackcouk-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D12734%26creativeASIN%3DB00061S0QE</URL>\r\n      </ItemLink>\r\n      <ItemLink>\r\n        <Description>All Offers</Description>\r\n        <URL>http://www.amazon.co.uk/gp/offer-listing/B00061S0QE%3FSubscriptionId%3DAKIAJH6G4CGNH5FCPVHA%26tag%3Dthinkbackcouk-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D12734%26creativeASIN%3DB00061S0QE</URL>\r\n      </ItemLink>\r\n    </ItemLinks>\r\n    <SalesRank>50</SalesRank>\r\n    <SmallImage>\r\n      <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL75_.jpg</URL>\r\n      <Height Units="pixels">75</Height>\r\n      <Width Units="pixels">52</Width>\r\n    </SmallImage>\r\n    <MediumImage>\r\n      <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL160_.jpg</URL>\r\n      <Height Units="pixels">160</Height>\r\n      <Width Units="pixels">112</Width>\r\n    </MediumImage>\r\n    <LargeImage>\r\n      <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L.jpg</URL>\r\n      <Height Units="pixels">250</Height>\r\n      <Width Units="pixels">175</Width>\r\n    </LargeImage>\r\n    <ImageSets>\r\n      <ImageSet Category="primary">\r\n        <SwatchImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL30_.jpg</URL>\r\n          <Height Units="pixels">30</Height>\r\n          <Width Units="pixels">21</Width>\r\n        </SwatchImage>\r\n        <SmallImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL75_.jpg</URL>\r\n          <Height Units="pixels">75</Height>\r\n          <Width Units="pixels">52</Width>\r\n        </SmallImage>\r\n        <ThumbnailImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL75_.jpg</URL>\r\n          <Height Units="pixels">75</Height>\r\n          <Width Units="pixels">52</Width>\r\n        </ThumbnailImage>\r\n        <TinyImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL110_.jpg</URL>\r\n          <Height Units="pixels">110</Height>\r\n          <Width Units="pixels">77</Width>\r\n        </TinyImage>\r\n        <MediumImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L._SL160_.jpg</URL>\r\n          <Height Units="pixels">160</Height>\r\n          <Width Units="pixels">112</Width>\r\n        </MediumImage>\r\n        <LargeImage>\r\n          <URL>http://ecx.images-amazon.com/images/I/31RPTC68B7L.jpg</URL>\r\n          <Height Units="pixels">250</Height>\r\n          <Width Units="pixels">175</Width>\r\n        </LargeImage>\r\n      </ImageSet>\r\n    </ImageSets>\r\n    <ItemAttributes>\r\n      <Actor>Will Ferrell</Actor>\r\n      <Actor>Edward Asner</Actor>\r\n      <Actor>Bob Newhart</Actor>\r\n      <Actor>James Caan</Actor>\r\n      <Actor>Mary Steenburgen</Actor>\r\n      <AudienceRating>Parental Guidance</AudienceRating>\r\n      <Binding>DVD</Binding>\r\n      <Creator Role="Primary Contributor">Will Ferrell</Creator>\r\n      <Creator Role="Primary Contributor">Edward Asner</Creator>\r\n      <Creator Role="Producer">Cale Boyter</Creator>\r\n      <Creator Role="Producer">David B. Householter</Creator>\r\n      <Creator Role="Producer">Jimmy Miller</Creator>\r\n      <Creator Role="Producer">Jon Berg</Creator>\r\n      <Creator Role="Producer">Julie Wixson Darmody</Creator>\r\n      <Creator Role="Producer">Kent Alterman</Creator>\r\n      <Creator Role="Writer">David Berenbaum</Creator>\r\n      <Director>Jon Favreau</Director>\r\n      <EAN>5017239192470</EAN>\r\n      <Format>PAL</Format>\r\n      <Label>Eiv</Label>\r\n      <Languages>\r\n        <Language>\r\n          <Name>English</Name>\r\n          <Type>Subtitled</Type>\r\n        </Language>\r\n        <Language>\r\n          <Name>English</Name>\r\n          <Type>Original Language</Type>\r\n        </Language>\r\n      </Languages>\r\n      <ListPrice>\r\n        <Amount>999</Amount>\r\n        <CurrencyCode>GBP</CurrencyCode>\r\n        <FormattedPrice>£9.99</FormattedPrice>\r\n      </ListPrice>\r\n      <Manufacturer>Eiv</Manufacturer>\r\n      <NumberOfDiscs>2</NumberOfDiscs>\r\n      <NumberOfItems>2</NumberOfItems>\r\n      <PackageDimensions>\r\n        <Height Units="hundredths-inches">55</Height>\r\n        <Length Units="hundredths-inches">748</Length>\r\n        <Weight Units="hundredths-pounds">22</Weight>\r\n        <Width Units="hundredths-inches">535</Width>\r\n      </PackageDimensions>\r\n      <PackageQuantity>1</PackageQuantity>\r\n      <ProductGroup>DVD</ProductGroup>\r\n      <ProductTypeName>ABIS_DVD</ProductTypeName>\r\n      <Publisher>Eiv</Publisher>\r\n      <RegionCode>2</RegionCode>\r\n      <ReleaseDate>2004-11-08</ReleaseDate>\r\n      <RunningTime Units="minutes">93</RunningTime>\r\n      <SKU>13933</SKU>\r\n      <Studio>Eiv</Studio>\r\n      <Title>Elf [DVD] [2003]</Title>\r\n      <UPC>501723919247</UPC>\r\n    </ItemAttributes>\r\n    </Item>\r\n	</Items>\r\n</ItemSearchResponse>', '2012-03-07 18:35:00', 1);

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
  ADD CONSTRAINT `FK_E306BA8154963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_E306BA814296D31F` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`),
  ADD CONSTRAINT `FK_E306BA814FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`),
  ADD CONSTRAINT `FK_E306BA81FF312AC0` FOREIGN KEY (`decade_id`) REFERENCES `decade` (`id`);

--
-- Constraints for table `mediaresourcelistingscache`
--
ALTER TABLE `mediaresourcelistingscache`
  ADD CONSTRAINT `FK_623D23F14296D31F` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`),
  ADD CONSTRAINT `FK_623D23F14FBBC852` FOREIGN KEY (`mediaType_id`) REFERENCES `mediatype` (`id`),
  ADD CONSTRAINT `FK_623D23F154963938` FOREIGN KEY (`api_id`) REFERENCES `api` (`id`),
  ADD CONSTRAINT `FK_623D23F1FF312AC0` FOREIGN KEY (`decade_id`) REFERENCES `decade` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
