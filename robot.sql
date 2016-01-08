DROP DATABASE IF EXISTS `phalcapi`;

CREATE DATABASE `phalcon`;
USE `phalcapi`;

CREATE TABLE `robots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `type` varchar(50) NOT NULL,
  `year` date,
  PRIMARY KEY (`id`)
);
