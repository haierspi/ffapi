/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : store

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2016-06-08 15:44:21
*/


SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `pre_member`;
CREATE TABLE `pre_member` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户UID',
  `email` char(255)  DEFAULT NULL COMMENT '邮箱',
  `mobile` char(255) DEFAULT NULL COMMENT '电话号码',
  `nickname` char(255) DEFAULT NULL COMMENT '用户昵称',
  `name` char(255) DEFAULT NULL COMMENT '用户名称',
  `pwcode` char(255) NOT NULL DEFAULT '' COMMENT '用户密码随机字符串',
  `avatar` char(255) NOT NULL DEFAULT '' COMMENT '头像',
  `level` mediumint(8) unsigned NOT NULL DEFAULT '0'  COMMENT '用户级别',
  `token` char(255) NOT NULL DEFAULT '' COMMENT '用户TOKEN',
  `datetime` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '注册时间',

  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `mobile` (`mobile`),
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员表';


DROP TABLE IF EXISTS `pre_member_weixin`;
CREATE TABLE `pre_member_weixin` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户凭证',
  `wx_openid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_sex` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_language` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_city` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_province` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_country` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `wx_headimgurl` varchar(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  PRIMARY KEY (`uid`),
  UNIQUE KEY  `wx_openid` (`wx_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员微信授权表';



DROP TABLE IF EXISTS `pre_member_count`;
CREATE TABLE `pre_member_count` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' ,
  `credits1` decimal(14,2) DEFAULT 0.00 COMMENT '积分字段1',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员计数表';



DROP TABLE IF EXISTS `pre_member_verify`;
CREATE TABLE `pre_member_verify` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT ' 0 注册邮箱 1 注册手机号', 
  `account` char(255) NOT NULL DEFAULT '' COMMENT '验证账号:手机号或邮箱',
  `code` char(255) NOT NULL DEFAULT '',
  `expiration`  DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '有效期',
  `datetime`  DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '注册时间',
  PRIMARY KEY (`id`),
  KEY `typeaccount` (`type`,`account`),
  KEY `expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员验证表';

