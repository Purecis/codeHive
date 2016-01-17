# ************************************************************
# Sequel Pro SQL dump
# Version 4135
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.34)
# Database: purecis-v2
# Generation Time: 2015-08-30 15:05:24 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table library
# ------------------------------------------------------------

DROP TABLE IF EXISTS `library`;

CREATE TABLE `library` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `author` bigint(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `path` text NOT NULL,
  `updated` int(11) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `size` varchar(50) NOT NULL,
  `tags` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author` (`author`),
  KEY `extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table meta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `meta`;

CREATE TABLE `meta` (
  `meta_id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) NOT NULL,
  `key` varchar(50) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `table` varchar(50) DEFAULT NULL,
  `autoload` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`meta_id`),
  UNIQUE KEY `oid` (`oid`,`key`,`table`),
  KEY `key` (`key`),
  KEY `value` (`value`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table objects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `author` bigint(20) NOT NULL,
  `permalink` varchar(250) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `parent` bigint(20) NOT NULL,
  `taxonomy` varchar(50) NOT NULL DEFAULT '',
  `rel` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author` (`author`),
  KEY `parent` (`parent`),
  KEY `rel` (`rel`),
  KEY `permalink` (`permalink`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table relations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `relations`;

CREATE TABLE `relations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oid` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  `table` varchar(50) DEFAULT '',
  `taxonomy` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`,`rid`,`table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table terms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `terms`;

CREATE TABLE `terms` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `taxonomy` varchar(50) DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `oauth` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pass` varchar(200) NOT NULL,
  `rules` text,
  `group` int(2) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `rel` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `oauth`, `name`, `email`, `pass`, `rules`, `group`, `status`, `rel`)
VALUES
	(1,'0','Anonymous','---','---','new',0,'new',NULL),
	(2,'0','Master Admin','abo.al.tot@gmail.com','7bbb77da01df3defcd33ea207f8cd3935bd9d4893550d5bc63a85b72b47ce91d6121dd02','manage-contact,manage-yousef,admin-zone,manage-home,manage-menu,manage-place,manage-products,manage-callus',0,'new',NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
