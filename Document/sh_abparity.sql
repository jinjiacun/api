/*
Navicat MySQL Data Transfer

Source Server         : 221.6.167.248
Source Server Version : 50617
Source Host           : 221.6.167.248:3306
Source Database       : stocks

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-11 10:28:20
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sh_abparity
-- ----------------------------
DROP TABLE IF EXISTS `sh_abparity`;
CREATE TABLE `sh_abparity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bcode` varchar(8) DEFAULT NULL,
  `bname` varchar(10) DEFAULT NULL,
  `acode` varchar(8) DEFAULT NULL,
  `aname` varchar(10) DEFAULT NULL,
  `machinetime` datetime NOT NULL,
  PRIMARY KEY (`id`,`machinetime`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
