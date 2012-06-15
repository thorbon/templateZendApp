-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.16 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4156
-- Date/time:                    2012-06-15 16:30:07
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping database structure for zendapp_web
CREATE DATABASE IF NOT EXISTS `zendapp_web` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `zendapp_web`;


-- Dumping structure for table zendapp_web.acl_permission
CREATE TABLE IF NOT EXISTS `acl_permission` (
  `permission_id` int(10) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(150) NOT NULL,
  `permission_menu_name` varchar(150) NOT NULL,
  `menu_order` int(10) DEFAULT NULL,
  `display_in_menu` enum('Y','N') NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_permission: ~12 rows (approximately)
/*!40000 ALTER TABLE `acl_permission` DISABLE KEYS */;
INSERT INTO `acl_permission` (`permission_id`, `permission_name`, `permission_menu_name`, `menu_order`, `display_in_menu`) VALUES
	(1, 'clientlist', 'Client List', 1, 'Y'),
	(2, 'addlist', 'Add List', 1, 'N'),
	(3, 'editlist', 'Edit List', 1, 'Y'),
	(4, 'viewworkers', 'View Workers', 1, 'N'),
	(5, 'addworker', 'Add Worker', 1, 'Y'),
	(6, 'viewlist', 'View List', 1, 'Y'),
	(7, 'statistics', 'Statistics', 1, 'Y'),
	(8, 'manageUsers', 'Manage Users', 1, 'Y'),
	(9, 'preferences', 'Preferences', 1, 'Y'),
	(10, 'logout', 'Logout', 2, 'Y'),
	(11, 'detailsbyclient', 'Details By Client', 1, 'Y'),
	(12, 'detailsbymessage', 'Details By Message', 1, 'Y');
/*!40000 ALTER TABLE `acl_permission` ENABLE KEYS */;


-- Dumping structure for table zendapp_web.acl_permissions
CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `role_id` int(1) NOT NULL,
  `resource_uid` int(4) NOT NULL,
  `permission_id` int(10) NOT NULL,
  `action` enum('allow','deny') NOT NULL DEFAULT 'allow',
  PRIMARY KEY (`role_id`,`resource_uid`,`permission_id`,`action`),
  KEY `FK_acl_permissions_acl_resources` (`resource_uid`),
  KEY `FK_acl_permissions_acl_permission` (`permission_id`),
  CONSTRAINT `FK_acl_permissions_acl_permission` FOREIGN KEY (`permission_id`) REFERENCES `acl_permission` (`permission_id`),
  CONSTRAINT `FK_acl_permissions_acl_resources` FOREIGN KEY (`resource_uid`) REFERENCES `acl_resources` (`uid`),
  CONSTRAINT `FK_acl_permissions_acl_roles` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_permissions: ~8 rows (approximately)
/*!40000 ALTER TABLE `acl_permissions` DISABLE KEYS */;
INSERT INTO `acl_permissions` (`role_id`, `resource_uid`, `permission_id`, `action`) VALUES
	(4, 1, 9, 'allow'),
	(3, 2, 1, 'allow'),
	(3, 2, 5, 'allow'),
	(3, 2, 11, 'allow'),
	(3, 2, 12, 'allow'),
	(4, 3, 3, 'allow'),
	(4, 3, 6, 'allow'),
	(4, 3, 7, 'allow');
/*!40000 ALTER TABLE `acl_permissions` ENABLE KEYS */;


-- Dumping structure for table zendapp_web.acl_resources
CREATE TABLE IF NOT EXISTS `acl_resources` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `resource` varchar(64) NOT NULL,
  `resource_menu_name` varchar(150) NOT NULL,
  `resource_menu_order` varchar(150) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `resource` (`resource`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_resources: ~4 rows (approximately)
/*!40000 ALTER TABLE `acl_resources` DISABLE KEYS */;
INSERT INTO `acl_resources` (`uid`, `resource`, `resource_menu_name`, `resource_menu_order`) VALUES
	(1, 'dashboard', 'Main', '1'),
	(2, 'client', 'Client', '2'),
	(3, 'campaign', 'Campaign', '3'),
	(4, 'system', 'System', '1');
/*!40000 ALTER TABLE `acl_resources` ENABLE KEYS */;


-- Dumping structure for table zendapp_web.acl_roles
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(64) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_roles: ~5 rows (approximately)
/*!40000 ALTER TABLE `acl_roles` DISABLE KEYS */;
INSERT INTO `acl_roles` (`role_id`, `role_name`) VALUES
	(2, 'Administrator'),
	(3, 'ConstManager'),
	(4, 'DivLeader'),
	(5, 'Everyone'),
	(1, 'Super');
/*!40000 ALTER TABLE `acl_roles` ENABLE KEYS */;


-- Dumping structure for table zendapp_web.acl_users
CREATE TABLE IF NOT EXISTS `acl_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(4) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `user_name` varchar(64) NOT NULL,
  `password` varchar(500) NOT NULL,
  `password_salt` varchar(500) NOT NULL,
  `user_status` varchar(20) NOT NULL DEFAULT 'PENDING',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `FK_acl_users_acl_roles` (`role_id`),
  CONSTRAINT `FK_acl_users_acl_roles` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_users: ~5 rows (approximately)
/*!40000 ALTER TABLE `acl_users` DISABLE KEYS */;
INSERT INTO `acl_users` (`uid`, `role_id`, `firstname`, `lastname`, `user_name`, `password`, `password_salt`, `user_status`) VALUES
	(1, 1, 'Mills', 'Fareh', 'super', '6660ae6207d6d3f12c6b868f447b85ebad4176d6', ':pw&D7%71vZhSn5?kL{.kjHzr8c{spFt/$Y{TkutFVEtUA`aBX', 'ACTIVE'),
	(2, 2, 'Maria', 'Lisser', 'admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'oO^:L$jq`RW>$aG+^f*5-F~+TtzfOv=Y(ct\\t}&?!{_]%B$%)F', 'ACTIVE'),
	(3, 3, 'Gavin', 'Morris', 'mp', 'e1daf886508b135d3f2dd0ae999ba291b504b6a7', '#Rek|Q\'hd"b#ZMblPs`md`d:,qIv$suw?bc&>l|Pq09!=7biN~', 'ACTIVE'),
	(4, 4, 'Sheldon', 'Donaldson', 'division_leader', 'b973aad2592401be43226a4908464cf660c637c0', 'pQgvmLzy3?HV(@~g\'3$|8-D25rCr&Zp}E)0!:R/u?[0-\\Z%ak4', 'ACTIVE'),
	(5, 5, '', '', 'Guest', '', '-!u\\:(SYmDl?Xp=Tz}+Lffok', 'ACTIVE');
/*!40000 ALTER TABLE `acl_users` ENABLE KEYS */;


-- Dumping structure for table zendapp_web.acl_user_client
CREATE TABLE IF NOT EXISTS `acl_user_client` (
  `acl_user_id` int(10) NOT NULL,
  `client_id` int(10) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL,
  PRIMARY KEY (`acl_user_id`,`client_id`),
  KEY `FK_acl_user_client_client` (`client_id`),
  CONSTRAINT `FK_acl_user_client_acl_users` FOREIGN KEY (`acl_user_id`) REFERENCES `acl_users` (`uid`),
  CONSTRAINT `FK_acl_user_client_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table zendapp_web.acl_user_client: ~3 rows (approximately)
/*!40000 ALTER TABLE `acl_user_client` DISABLE KEYS */;
INSERT INTO `acl_user_client` (`acl_user_id`, `client_id`, `status`) VALUES
	(1, 1, 'ACTIVE'),
	(1, 9, 'ACTIVE'),
	(1, 10, 'ACTIVE');
/*!40000 ALTER TABLE `acl_user_client` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
