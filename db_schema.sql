SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `routers`;
CREATE TABLE IF NOT EXISTS `routers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `RouterName` varchar(100) NOT NULL,
  `NodeID` int(10) NOT NULL,
  `User_id` int(10) NOT NULL,
  `NodeName` varchar(100) NOT NULL,
  `Type` enum('mikrotik','quagga') NOT NULL,
  `Ip` varchar(200) NOT NULL,
  `Port` int(5) NOT NULL,
  `User` varchar(50) NOT NULL,
  `Pass` varchar(50) NOT NULL,
  `Active` enum('1','0') NOT NULL DEFAULT '1',
  `Status` enum('up','down') NOT NULL DEFAULT 'up',
  `Trace` enum('1','0') DEFAULT '0',
  `Stats` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

INSERT INTO `settings` (`id`, `Name`, `Value`, `Description`, `Type`) VALUES
(1, 'MAIL_SUPPORT', 'support@example.com', 'Support Mail Address', 'general'),
(2, 'ADMIN_ITEMS_PER_PAGE', '50', 'Records per page', 'panel'),
(3, 'APP_NAME', 'BGP Looking Glass NG', 'Application Name (custom branding)', 'general'),
(4, 'APP_URL', 'http://www.routers.awmn', 'Full URL to Control Panel (without trailing slash)', 'panel'),
(5, 'SIDEBAR_SUPPORT_URL', '<a href="http://www.awmn/showthread.php?t=38954" target="_blank">Support</a>', 'Support URL (optional)', 'panel'),
(6, 'BGP_LIVE_STATS_DOMAIN', 'www.stats.awmn', 'If you have installed BGP Live Statistics you an enter the domain it runs on here so a link will be ', 'panel'),
(7, 'COOKIE_NAME', 'awmn_routers', 'Cookie name for panel', 'panel'),
(8, 'SIDEBAR_LIVE_STATS_URL', '<a href="http://www.stats.awmn" target="_blank">www.stats.awmn</a>', 'URL for Live BGP Statistics', 'panel'),
(9, 'WIND_DOMAIN', 'wind.awmn', 'The domain name on which WiND Database runs', 'general'),
(10, 'WIRELESS_COMMUNITY_NAME', 'AWMN', 'The name of the Wireless Community for which this installation is running.', 'general'),
(11, 'FOOTER_TEXT', 'www.routers.awmn - 2007-2014. Developed by <a href="http://www.cha0s.awmn/" target="_blank">Cha0s #2331</a> for <a href="https://www.awmn/" target="_blank">AWMN</a>.', 'Custom text on the footer', 'panel');

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Firstname` varchar(255) DEFAULT NULL,
  `Lastname` varchar(255) DEFAULT NULL,
  `Admin_level` enum('admin','user') DEFAULT 'admin',
  `Help` enum('0','1') DEFAULT '1',
  `Active` enum('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`Username`),
  KEY `active` (`Active`),
  KEY `admin_level` (`Admin_level`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `staff` (`id`, `Username`, `Password`, `Email`, `Firstname`, `Lastname`, `Admin_level`, `Help`, `Active`) VALUES
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin@example.com', '', '', 'admin', '1', '1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
