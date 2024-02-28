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

 Date: 28/02/2024 12:17:01
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
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of collection_tasks
-- ----------------------------
INSERT INTO `collection_tasks` VALUES (1, 1, 'PsQREdit 2.4.3.exe123123', '2024-02-23 14:24:22', '2333333');
INSERT INTO `collection_tasks` VALUES (2, 2, 'PsQREdit 2.4.3.exe123123', '2024-02-23 14:34:10', '666');

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of collection_uploaded
-- ----------------------------
INSERT INTO `collection_uploaded` VALUES (1, 1, '127.0.0.1', '2024-02-23 14:25:54', '66666');
INSERT INTO `collection_uploaded` VALUES (2, 2, '192.168.1.20', '2024-02-23 14:34:30', '11111');
INSERT INTO `collection_uploaded` VALUES (4, 1, '::1', '2024-02-26 11:30:27', '06ab4a01-3c8e-4b3b-a25f-8e00c78fed8c');

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
-- Records of country
-- ----------------------------
INSERT INTO `country` VALUES ('AU', 'Australia', 24016400);
INSERT INTO `country` VALUES ('BR', 'Brazil', 205722000);
INSERT INTO `country` VALUES ('CA', 'Canada', 35985751);
INSERT INTO `country` VALUES ('CN', 'China', 1375210000);
INSERT INTO `country` VALUES ('DE', 'Germany', 81459000);
INSERT INTO `country` VALUES ('FR', 'France', 64513242);
INSERT INTO `country` VALUES ('GB', 'United Kingdom', 65097000);
INSERT INTO `country` VALUES ('IN', 'India', 1285400000);
INSERT INTO `country` VALUES ('RU', 'Russia', 146519759);
INSERT INTO `country` VALUES ('US', 'United States', 322976000);

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
-- Records of share
-- ----------------------------
INSERT INTO `share` VALUES (2, 2, 'WePE_32_V2.3.iso', '6666', '2024-02-16 14:48:07');
INSERT INTO `share` VALUES (3, 1, 'WePE_32_V2.3.iso', '0000', '2024-02-16 15:11:35');
INSERT INTO `share` VALUES (4, 1, 'WePE_32_V2.3_副本.iso', 'c7hg', '2024-02-17 15:35:32');
INSERT INTO `share` VALUES (5, 1, 'PsQREdit 2.4.3.exe123123/PsQREdit 2.4.3.7z', 'wcvy', '2024-02-17 15:37:03');
INSERT INTO `share` VALUES (6, 1, 'test', '6666', '2024-02-17 15:45:44');

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

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'chenx221', '$2y$13$2d6BZn/3g0mC0HsLj0hTXuLKZjq/t2EcV6nR9H8SO6UXvFWQxu0OC', 'CxQfOU9W6Vwijbv-a4fcukFihFuoIPR3', 'chenx221@yandex.com', 1);
INSERT INTO `user` VALUES (2, 'chenx2210', '$2y$13$Y3IZtFPU7vfAKlkeaQzTI.6lSfo/F/qmv2VFybG7wmh5yX49uc29m', '4en_00n5mhDST7AJdyk_CCfdoQcG5IS9', 'chenx2210@outlook.com', 1);

SET FOREIGN_KEY_CHECKS = 1;
