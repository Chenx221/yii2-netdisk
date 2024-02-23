/*
 Navicat Premium Data Transfer

 Source Server         : local_m
 Source Server Type    : MariaDB
 Source Server Version : 110202 (11.2.2-MariaDB)
 Source Host           : localhost:3307
 Source Schema         : yii2basic

 Target Server Type    : MariaDB
 Target Server Version : 110202 (11.2.2-MariaDB)
 File Encoding         : 65001

 Date: 23/02/2024 14:51:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for collection_tasks
-- ----------------------------
DROP TABLE IF EXISTS `collection_tasks`;
CREATE TABLE `collection_tasks`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '收集任务id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `folder_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '收集目标文件夹(相对路径)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '收集任务创建时间',
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '访问密钥',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `collection_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for collection_uploaded
-- ----------------------------
DROP TABLE IF EXISTS `collection_uploaded`;
CREATE TABLE `collection_uploaded`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文件收集的上传记录id',
  `task_id` int(11) NOT NULL COMMENT '对应的文件收集id',
  `uploader_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '上传者ip',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '上传时间',
  `subfolder_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '对应的子文件夹名',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `task_id`(`task_id`) USING BTREE,
  CONSTRAINT `collection_uploaded_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `collection_tasks` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for country
-- ----------------------------
DROP TABLE IF EXISTS `country`;
CREATE TABLE `country`  (
  `code` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `name` char(52) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `population` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for share
-- ----------------------------
DROP TABLE IF EXISTS `share`;
CREATE TABLE `share`  (
  `share_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分享ID',
  `sharer_id` int(11) NOT NULL COMMENT '分享者ID',
  `file_relative_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件的相对路径',
  `access_code` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分享密钥',
  `creation_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT '分享创建日期',
  PRIMARY KEY (`share_id`) USING BTREE,
  INDEX `sharer_id`(`sharer_id`) USING BTREE,
  CONSTRAINT `share_ibfk_1` FOREIGN KEY (`sharer_id`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码',
  `auth_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'authkey',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '用户状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
