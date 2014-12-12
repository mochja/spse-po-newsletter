-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL COMMENT '; 1 published; 0 private',
  `number` int(4) NOT NULL COMMENT '; first two are month number from 1 to 12; last two year last two ints',
  `created` datetime DEFAULT NULL,
  `published` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_uniq` (`number`),
  KEY `date` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `newsletter_article`;
CREATE TABLE `newsletter_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsletter_id` int(11) NOT NULL,
  `type` smallint(6) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `text` text NOT NULL,
  `html` text,
  `pos` int(11) NOT NULL DEFAULT '0',
  `modified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `newsletter_id` (`newsletter_id`),
  KEY `type` (`type`),
  CONSTRAINT `newsletter_article_ibfk_5` FOREIGN KEY (`type`) REFERENCES `newsletter_article_types` (`id`),
  CONSTRAINT `newsletter_article_ibfk_6` FOREIGN KEY (`newsletter_id`) REFERENCES `newsletter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `newsletter_article_types`;
CREATE TABLE `newsletter_article_types` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `newsletter_article_types` (`id`, `title`) VALUES
(0, 'top'),
(1, 'flash'),
(2, 'activity');

DROP TABLE IF EXISTS `newsletter_email`;
CREATE TABLE `newsletter_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `email_i` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `newsletter_sponsor`;
CREATE TABLE `newsletter_sponsor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img_path` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
  `pos` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


-- 2014-08-02 14:25:44