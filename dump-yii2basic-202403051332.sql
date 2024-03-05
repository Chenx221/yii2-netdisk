-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: yii2basic
-- ------------------------------------------------------
-- Server version	11.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  KEY `idx-auth_assignment-user_id` (`user_id`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_assignment`
--

LOCK TABLES `auth_assignment` WRITE;
/*!40000 ALTER TABLE `auth_assignment` DISABLE KEYS */;
INSERT INTO `auth_assignment` VALUES ('admin','4',1709616656),('user','1',1709616611),('user','2',1709616611),('user','3',1709616611);
/*!40000 ALTER TABLE `auth_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`),
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_item`
--

LOCK TABLES `auth_item` WRITE;
/*!40000 ALTER TABLE `auth_item` DISABLE KEYS */;
INSERT INTO `auth_item` VALUES ('accessHome',2,'访问文件管理',NULL,NULL,1709616611,1709616611),('admin',1,NULL,NULL,NULL,1709616611,1709616611),('user',1,NULL,NULL,NULL,1709616611,1709616611);
/*!40000 ALTER TABLE `auth_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_item_child`
--

LOCK TABLES `auth_item_child` WRITE;
/*!40000 ALTER TABLE `auth_item_child` DISABLE KEYS */;
INSERT INTO `auth_item_child` VALUES ('user','accessHome');
/*!40000 ALTER TABLE `auth_item_child` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_rule`
--

LOCK TABLES `auth_rule` WRITE;
/*!40000 ALTER TABLE `auth_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_tasks`
--

DROP TABLE IF EXISTS `collection_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '收集任务id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `folder_path` varchar(255) NOT NULL COMMENT '收集目标文件夹(相对路径)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '收集任务创建时间',
  `secret` varchar(255) NOT NULL COMMENT '访问密钥',
  `status` tinyint(1) DEFAULT 1 COMMENT '收集任务是否启用',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `collection_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_tasks`
--

LOCK TABLES `collection_tasks` WRITE;
/*!40000 ALTER TABLE `collection_tasks` DISABLE KEYS */;
INSERT INTO `collection_tasks` VALUES (1,1,'PsQREdit 2.4.3.exe123123','2024-02-23 06:24:22','2333333',1),(2,2,'PsQREdit 2.4.3.exe123123','2024-02-23 06:34:10','666',1);
/*!40000 ALTER TABLE `collection_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collection_uploaded`
--

DROP TABLE IF EXISTS `collection_uploaded`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_uploaded` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文件收集的上传记录id',
  `task_id` int(11) NOT NULL COMMENT '对应的文件收集id',
  `uploader_ip` varchar(45) NOT NULL COMMENT '上传者ip',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '上传时间',
  `subfolder_name` varchar(255) NOT NULL COMMENT '对应的子文件夹名',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `task_id` (`task_id`) USING BTREE,
  CONSTRAINT `collection_uploaded_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `collection_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection_uploaded`
--

LOCK TABLES `collection_uploaded` WRITE;
/*!40000 ALTER TABLE `collection_uploaded` DISABLE KEYS */;
INSERT INTO `collection_uploaded` VALUES (1,1,'127.0.0.1','2024-02-23 06:25:54','66666'),(2,2,'192.168.1.20','2024-02-23 06:34:30','11111'),(4,1,'::1','2024-02-26 03:30:27','06ab4a01-3c8e-4b3b-a25f-8e00c78fed8c');
/*!40000 ALTER TABLE `collection_uploaded` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `country` (
  `code` char(2) NOT NULL,
  `name` char(52) NOT NULL,
  `population` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
INSERT INTO `country` VALUES ('AU','Australia',24016400),('BR','Brazil',205722000),('CA','Canada',35985751),('CN','China',1375210000),('DE','Germany',81459000),('FR','France',64513242),('GB','United Kingdom',65097000),('IN','India',1285400000),('RU','Russia',146519759),('US','United States',322976000);
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
INSERT INTO `migration` VALUES ('m000000_000000_base',1709607583),('m140506_102106_rbac_init',1709607803),('m170907_052038_rbac_add_index_on_auth_assignment_user_id',1709607803),('m180523_151638_rbac_updates_indexes_without_prefix',1709607804),('m200409_110543_rbac_update_mssql_trigger',1709607804),('m240305_042554_init_rbac',1709616611);
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `share`
--

DROP TABLE IF EXISTS `share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `share` (
  `share_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分享ID',
  `sharer_id` int(11) NOT NULL COMMENT '分享者ID',
  `file_relative_path` varchar(255) NOT NULL COMMENT '文件的相对路径',
  `access_code` varchar(4) NOT NULL COMMENT '分享密钥',
  `creation_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT '分享创建日期',
  `status` tinyint(1) DEFAULT 1 COMMENT '分享是否启用',
  PRIMARY KEY (`share_id`) USING BTREE,
  KEY `sharer_id` (`sharer_id`) USING BTREE,
  CONSTRAINT `share_ibfk_1` FOREIGN KEY (`sharer_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `share`
--

LOCK TABLES `share` WRITE;
/*!40000 ALTER TABLE `share` DISABLE KEYS */;
INSERT INTO `share` VALUES (2,2,'WePE_32_V2.3.iso','6666','2024-02-16 14:48:07',1),(3,1,'WePE_32_V2.3.iso','0000','2024-02-16 15:11:35',1),(4,1,'WePE_32_V2.3_副本.iso','c7hg','2024-02-17 15:35:32',1),(5,1,'PsQREdit 2.4.3.exe123123/PsQREdit 2.4.3.7z','wcvy','2024-02-17 15:37:03',1),(6,1,'test','6666','2024-02-17 15:45:44',1),(7,4,'01 Deep Sleep Sheep.m4a','1111','2024-03-04 16:24:07',0);
/*!40000 ALTER TABLE `share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(255) DEFAULT NULL COMMENT '用户名',
  `name` varchar(255) DEFAULT NULL COMMENT '昵称',
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `auth_key` varchar(255) DEFAULT NULL COMMENT 'authkey',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `status` tinyint(1) DEFAULT 1 COMMENT '账户是否启用',
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT '账户创建时间',
  `last_login` timestamp NULL DEFAULT NULL COMMENT '上次登陆时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '上次登录ip',
  `bio` text DEFAULT NULL COMMENT '备注',
  `role` varchar(255) DEFAULT NULL COMMENT '身份',
  `encryption_key` varchar(255) DEFAULT NULL COMMENT '加密密钥',
  `otp_secret` varchar(255) DEFAULT NULL COMMENT 'otp密钥',
  `is_encryption_enabled` tinyint(1) DEFAULT 0 COMMENT '启用加密',
  `is_otp_enabled` tinyint(1) DEFAULT 0 COMMENT '启用otp',
  `storage_limit` bigint(20) DEFAULT -1 COMMENT '存储容量限制,MB',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'chenx221','chenx221','$2y$13$uSvq3bPE7IneFL9f8fiDiO2AnXreroMiA.hOmujGjazfO/G.yo3wy','CxQfOU9W6Vwijbv-a4fcukFihFuoIPR3','chenx221@yandex.com',1,'2024-03-01 06:08:04','2024-03-05 04:52:40','::1','你好世界1234123123','user',NULL,NULL,0,0,4096),(2,'chenx2210','chenx2210','$2y$13$Y3IZtFPU7vfAKlkeaQzTI.6lSfo/F/qmv2VFybG7wmh5yX49uc29m','4en_00n5mhDST7AJdyk_CCfdoQcG5IS9','chenx2210@outlook.com',1,'2024-03-01 06:08:04',NULL,NULL,NULL,'user',NULL,NULL,0,0,-1),(3,'demo','demo','$2y$13$MKq55jcWKnMW8zleaP68y.f1orVoXWcRsWqoP4gyqPuRzvxNvYGRe','G02zKldhVVkqxlIFcFzYDz3q7HaWDzMW','demo@chenx221.cyou',1,'2024-03-04 07:26:48','2024-03-04 07:27:01','::1',NULL,'user',NULL,NULL,0,0,-1),(4,'demo1','demo1','$2y$13$VXO1jbDN31HjfqvIMOde1Ol4pr66QrnCYCwUgyASaodgQrk8yBt3i','JsF4ILeMqpVTwdSvi2V240S5HTGeLMcx','demo1@chenx221.cyou',1,'2024-03-04 07:31:41','2024-03-05 05:30:56','::1',NULL,'admin',NULL,NULL,0,0,16);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'yii2basic'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-03-05 13:32:38
