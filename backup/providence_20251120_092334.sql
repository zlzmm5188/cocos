/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.10-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: providence
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `address_modify_requests`
--

DROP TABLE IF EXISTS `address_modify_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_modify_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL COMMENT '类型：1=USDT地址，2=银行卡',
  `old_data` text DEFAULT NULL COMMENT '旧数据（JSON）',
  `new_data` text NOT NULL COMMENT '新数据（JSON）',
  `reason` varchar(500) DEFAULT NULL COMMENT '修改原因',
  `apply_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '申请时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0=待审核，1=已通过，2=已拒绝',
  `review_admin_id` bigint(20) DEFAULT NULL COMMENT '审核管理员ID',
  `review_time` timestamp NULL DEFAULT NULL COMMENT '审核时间',
  `review_remark` varchar(500) DEFAULT NULL COMMENT '审核备注',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_apply_time` (`apply_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='地址/银行卡修改申请表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_modify_requests`
--

LOCK TABLES `address_modify_requests` WRITE;
/*!40000 ALTER TABLE `address_modify_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_modify_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `actor_type` enum('ADMIN','USER','SYSTEM') DEFAULT NULL,
  `actor_id` bigint(20) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) DEFAULT NULL,
  `before_data` text DEFAULT NULL,
  `after_data` text DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES
(1,'project','创建项目','ADMIN',1,'invest_projects',4,NULL,'{\"title\":\"测试\",\"subtitle\":\"反馈100\",\"description\":\"\",\"category\":\"IPO\",\"currency\":\"CNY\",\"cycle_days\":8,\"base_rate\":1,\"added_rate\":0,\"gift_rate\":0,\"total_rate\":1,\"min_invest\":100,\"max_invest\":1000,\"total_quota\":100000,\"manager_id\":5,\"risk_level\":1,\"status\":0,\"is_index\":0,\"sort\":100,\"payment_type\":2,\"vip_min\":0,\"need_referral\":0,\"team_member_required\":0,\"mcount\":0,\"updated_at\":\"2025-11-10 23:40:41\",\"project_code\":\"PRJ20241110002\",\"version\":1,\"schedule\":0,\"view_count\":0,\"invest_count\":0,\"total_invested\":0,\"sold\":0,\"remain\":100000,\"created_at\":\"2025-11-10 23:40:41\"}','172.71.82.78','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-10 15:40:41'),
(2,'user','编辑用户信息','ADMIN',1,'users',1,'{\"id\":1,\"uid\":\"G138688\",\"username\":\"G138688\",\"phone\":null,\"email\":null,\"password\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"trade_password\":null,\"realname_status\":1,\"vip_level\":8,\"total_invest\":\"0.00000000\",\"is_internal\":0,\"status\":1,\"parent_id\":null,\"created_at\":\"2025-11-10 14:37:43\",\"updated_at\":\"2025-11-10 14:37:43\"}','{\"is_internal\":1,\"updated_at\":\"2025-11-11 00:07:14\"}','','','2025-11-10 16:07:14'),
(3,'user','管理员重置用户密码','ADMIN',1,'users',1,'{\"username\":\"G138688\"}','{\"password\":\"***已重置***\"}','172.71.82.79','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-10 18:15:55'),
(4,'user','管理员重置用户密码','ADMIN',1,'users',1,'{\"username\":\"G138688\"}','{\"password\":\"***已重置***\"}','172.70.143.137','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-10 18:17:00'),
(5,'project','创建项目','ADMIN',1,'invest_projects',5,NULL,'{\"title\":\"123\",\"subtitle\":\"123\",\"description\":\"123\",\"category\":\"BOND\",\"currency\":\"USDT\",\"cycle_days\":112,\"base_rate\":1,\"added_rate\":1,\"gift_rate\":1,\"total_rate\":3,\"min_invest\":1111,\"max_invest\":111111,\"total_quota\":11111111,\"manager_id\":0,\"status\":1,\"is_index\":1,\"sort\":100,\"payment_type\":0,\"vip_min\":2,\"need_referral\":0,\"team_member_required\":0,\"mcount\":0,\"updated_at\":\"2025-11-11 04:39:55\",\"project_code\":\"213123\",\"version\":1,\"schedule\":0,\"view_count\":0,\"invest_count\":0,\"total_invested\":0,\"sold\":0,\"remain\":11111111,\"created_at\":\"2025-11-11 04:39:55\"}','172.70.188.153','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-10 20:39:55');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invest_orders`
--

DROP TABLE IF EXISTS `invest_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invest_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `project_id` bigint(20) NOT NULL COMMENT '项目ID',
  `currency` enum('CNY','USDT') NOT NULL COMMENT '币种',
  `amount` decimal(20,8) NOT NULL COMMENT '投资金额',
  `vip_level` tinyint(4) NOT NULL COMMENT 'VIP等级',
  `base_rate` decimal(6,3) NOT NULL COMMENT '基础利率（%）',
  `vip_extra_rate` decimal(6,3) NOT NULL COMMENT 'VIP加息（%）',
  `added_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '活动加息（%）',
  `gift_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '赠送利率（%）',
  `final_rate` decimal(6,3) NOT NULL COMMENT '最终利率（%）',
  `cycle_days` int(11) NOT NULL COMMENT '投资周期（天）',
  `expected_profit` decimal(20,8) DEFAULT NULL COMMENT '预计收益',
  `earned_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '已赚收益',
  `status` enum('PENDING','RUNNING','FINISHED','REFUND','REJECT') NOT NULL DEFAULT 'PENDING' COMMENT '订单状态',
  `is_bonus_order` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为体验金订单：0=否，1=是',
  `bonus_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '使用的体验金金额',
  `start_at` datetime DEFAULT NULL COMMENT '开始时间',
  `end_at` datetime DEFAULT NULL COMMENT '结束时间',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_is_bonus_order` (`is_bonus_order`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invest_orders`
--

LOCK TABLES `invest_orders` WRITE;
/*!40000 ALTER TABLE `invest_orders` DISABLE KEYS */;
INSERT INTO `invest_orders` VALUES
(1,'ORD202511100001',1,1,'CNY',43609.00000000,0,4.500,0.500,0.000,0.000,5.000,30,1100.00000000,527.00000000,'RUNNING',0,0.00000000,'2025-10-26 22:26:34','2025-12-10 22:26:34','2025-11-10 22:26:34','2025-11-10 22:26:34'),
(2,'ORD202511100002',1,2,'CNY',27526.00000000,0,15.000,0.500,0.000,0.000,15.500,60,1522.00000000,1889.00000000,'RUNNING',0,0.00000000,'2025-11-01 22:26:34','2026-01-09 22:26:34','2025-11-10 22:26:34','2025-11-10 22:26:34'),
(3,'ORD202511100003',1,3,'CNY',28181.00000000,0,18.000,0.500,0.000,0.000,18.500,90,7201.00000000,1452.00000000,'FINISHED',0,0.00000000,'2025-10-27 22:26:34','2026-02-08 22:26:34','2025-11-10 22:26:34','2025-11-10 22:26:34'),
(4,'ORD202511100004',1,4,'CNY',32370.00000000,0,1.000,0.500,0.000,0.000,1.500,8,573.00000000,71.00000000,'FINISHED',0,0.00000000,'2025-11-04 22:26:34','2025-11-18 22:26:34','2025-11-10 22:26:34','2025-11-10 22:26:34'),
(5,'ORD202511100005',1,5,'CNY',17936.00000000,0,1.000,0.500,0.000,0.000,1.500,112,79.00000000,228.00000000,'RUNNING',0,0.00000000,'2025-11-03 22:26:34','2026-03-02 22:26:34','2025-11-10 22:26:34','2025-11-10 22:26:34'),
(8,'ORD2025111000002009',2,9,'CNY',3536.00000000,0,6.500,0.500,0.000,0.000,7.000,90,275.00000000,361.00000000,'FINISHED',0,0.00000000,'2025-10-18 22:42:40','2026-01-16 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(9,'ORD2025111000002008',2,8,'USDT',4691.00000000,0,4.200,0.500,0.000,0.000,4.700,45,98.00000000,77.00000000,'FINISHED',0,0.00000000,'2025-10-18 22:42:40','2025-12-02 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(10,'ORD2025111000002007',2,7,'CNY',34812.00000000,0,5.800,0.500,0.000,0.000,6.300,60,836.00000000,88.00000000,'FINISHED',0,0.00000000,'2025-10-18 22:42:40','2025-12-17 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(11,'ORD2025111000002006',2,6,'CNY',38604.00000000,0,3.500,0.500,0.000,0.000,4.000,30,392.00000000,44.00000000,'FINISHED',0,0.00000000,'2025-10-18 22:42:40','2025-11-17 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(12,'ORD2025111000003009',3,9,'CNY',5591.00000000,0,6.500,0.500,0.000,0.000,7.000,90,773.00000000,174.00000000,'FINISHED',0,0.00000000,'2025-10-20 22:42:40','2026-01-18 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(13,'ORD2025111000003008',3,8,'USDT',4698.00000000,0,4.200,0.500,0.000,0.000,4.700,45,364.00000000,109.00000000,'FINISHED',0,0.00000000,'2025-10-20 22:42:40','2025-12-04 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(14,'ORD2025111000003007',3,7,'CNY',41752.00000000,0,5.800,0.500,0.000,0.000,6.300,60,604.00000000,17.00000000,'FINISHED',0,0.00000000,'2025-10-20 22:42:40','2025-12-19 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(15,'ORD2025111000003006',3,6,'CNY',16610.00000000,0,3.500,0.500,0.000,0.000,4.000,30,145.00000000,3.00000000,'FINISHED',0,0.00000000,'2025-10-20 22:42:40','2025-11-19 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(16,'ORD2025111000004009',4,9,'CNY',16406.00000000,0,6.500,0.500,0.000,0.000,7.000,90,283.00000000,27.00000000,'RUNNING',0,0.00000000,'2025-10-23 22:42:40','2026-01-21 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(17,'ORD2025111000004008',4,8,'USDT',5791.00000000,0,4.200,0.500,0.000,0.000,4.700,45,289.00000000,63.00000000,'RUNNING',0,0.00000000,'2025-10-23 22:42:40','2025-12-07 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(18,'ORD2025111000004007',4,7,'CNY',6327.00000000,0,5.800,0.500,0.000,0.000,6.300,60,465.00000000,204.00000000,'FINISHED',0,0.00000000,'2025-10-23 22:42:40','2025-12-22 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(19,'ORD2025111000004006',4,6,'CNY',22492.00000000,0,3.500,0.500,0.000,0.000,4.000,30,103.00000000,122.00000000,'FINISHED',0,0.00000000,'2025-10-23 22:42:40','2025-11-22 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(20,'ORD2025111000005009',5,9,'CNY',28249.00000000,0,6.500,0.500,0.000,0.000,7.000,90,493.00000000,109.00000000,'FINISHED',0,0.00000000,'2025-10-25 22:42:40','2026-01-23 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(21,'ORD2025111000005008',5,8,'USDT',7648.00000000,0,4.200,0.500,0.000,0.000,4.700,45,449.00000000,52.00000000,'RUNNING',0,0.00000000,'2025-10-25 22:42:40','2025-12-09 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(22,'ORD2025111000005007',5,7,'CNY',12370.00000000,0,5.800,0.500,0.000,0.000,6.300,60,769.00000000,167.00000000,'RUNNING',0,0.00000000,'2025-10-25 22:42:40','2025-12-24 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(23,'ORD2025111000005006',5,6,'CNY',7558.00000000,0,3.500,0.500,0.000,0.000,4.000,30,117.00000000,36.00000000,'RUNNING',0,0.00000000,'2025-10-25 22:42:40','2025-11-24 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(24,'ORD2025111000006009',6,9,'CNY',31475.00000000,0,6.500,0.500,0.000,0.000,7.000,90,549.00000000,2.00000000,'RUNNING',0,0.00000000,'2025-10-27 22:42:40','2026-01-25 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(25,'ORD2025111000006008',6,8,'USDT',120.00000000,0,4.200,0.500,0.000,0.000,4.700,45,91.00000000,7.00000000,'FINISHED',0,0.00000000,'2025-10-27 22:42:40','2025-12-11 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(26,'ORD2025111000006007',6,7,'CNY',48205.00000000,0,5.800,0.500,0.000,0.000,6.300,60,411.00000000,99.00000000,'RUNNING',0,0.00000000,'2025-10-27 22:42:40','2025-12-26 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(27,'ORD2025111000006006',6,6,'CNY',4110.00000000,0,3.500,0.500,0.000,0.000,4.000,30,429.00000000,93.00000000,'RUNNING',0,0.00000000,'2025-10-27 22:42:40','2025-11-26 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(28,'ORD2025111000007009',7,9,'CNY',34948.00000000,0,6.500,0.500,0.000,0.000,7.000,90,144.00000000,25.00000000,'FINISHED',0,0.00000000,'2025-10-28 22:42:40','2026-01-26 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(29,'ORD2025111000007008',7,8,'USDT',5634.00000000,0,4.200,0.500,0.000,0.000,4.700,45,297.00000000,40.00000000,'FINISHED',0,0.00000000,'2025-10-28 22:42:40','2025-12-12 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(30,'ORD2025111000007007',7,7,'CNY',26799.00000000,0,5.800,0.500,0.000,0.000,6.300,60,532.00000000,24.00000000,'FINISHED',0,0.00000000,'2025-10-28 22:42:40','2025-12-27 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(31,'ORD2025111000007006',7,6,'CNY',13067.00000000,0,3.500,0.500,0.000,0.000,4.000,30,421.00000000,10.00000000,'RUNNING',0,0.00000000,'2025-10-28 22:42:40','2025-11-27 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39'),
(32,'ORD2025111000008009',8,9,'CNY',17909.00000000,0,6.500,0.500,0.000,0.000,7.000,90,308.00000000,94.00000000,'RUNNING',0,0.00000000,'2025-10-31 22:42:40','2026-01-29 22:42:40','2025-11-10 22:43:39','2025-11-10 22:43:39');
/*!40000 ALTER TABLE `invest_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invest_projects`
--

DROP TABLE IF EXISTS `invest_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invest_projects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '项目ID',
  `project_code` varchar(32) NOT NULL COMMENT '项目编号',
  `title` varchar(120) NOT NULL COMMENT '项目标题',
  `subtitle` varchar(255) DEFAULT NULL COMMENT '副标题',
  `description` text DEFAULT NULL COMMENT '项目描述',
  `category` enum('IPO','BOND','FUND','FIXED','INVEST') NOT NULL COMMENT '项目类型',
  `currency` enum('CNY','USDT') NOT NULL COMMENT '币种',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `images` text DEFAULT NULL COMMENT '图片集（JSON）',
  `cycle_days` int(11) NOT NULL COMMENT '周期（天）',
  `base_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '基础利率（%）',
  `added_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '活动加息（%）',
  `gift_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '赠送利率（%）',
  `total_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '总利率（%）',
  `min_invest` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '最低投资额',
  `max_invest` decimal(20,8) DEFAULT NULL COMMENT '最高投资额',
  `total_quota` decimal(20,8) DEFAULT NULL COMMENT '总募集额度',
  `schedule` decimal(9,4) NOT NULL DEFAULT 0.0000 COMMENT '募集进度（%）',
  `manager_id` bigint(20) DEFAULT NULL COMMENT '项目经理ID',
  `risk_level` tinyint(4) NOT NULL DEFAULT 1 COMMENT '风险等级（1-5）',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态（0:草稿,1:募集中,2:已满额,3:已结束）',
  `is_index` tinyint(4) DEFAULT 0 COMMENT '首页显示:0否1是',
  `sort` int(11) DEFAULT 0 COMMENT '排序权重(越大越靠前)',
  `payment_type` tinyint(4) DEFAULT 0 COMMENT '支付方式:0不限1仅USDT2仅CNY',
  `vip_min` tinyint(4) DEFAULT 0 COMMENT '最低VIP等级要求',
  `need_referral` tinyint(4) DEFAULT 0 COMMENT '需要推荐人:0否1是',
  `team_member_required` int(11) DEFAULT 0 COMMENT '团队人数要求',
  `mcount` int(11) DEFAULT 0 COMMENT '购买次数限制:0不限',
  `version` int(11) NOT NULL DEFAULT 1 COMMENT '版本号',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `invest_count` int(11) NOT NULL DEFAULT 0 COMMENT '认购人数',
  `total_invested` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '已募集金额',
  `sold` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT '已募集金额缓存',
  `remain` decimal(18,2) NOT NULL DEFAULT 0.00 COMMENT '剩余额度缓存',
  `cache_updated_at` datetime DEFAULT NULL COMMENT '缓存更新时间',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_category` (`category`),
  KEY `idx_is_index` (`is_index`),
  KEY `idx_sort` (`sort`),
  KEY `idx_payment_type` (`payment_type`),
  KEY `idx_vip_min` (`vip_min`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invest_projects`
--

LOCK TABLES `invest_projects` WRITE;
/*!40000 ALTER TABLE `invest_projects` DISABLE KEYS */;
INSERT INTO `invest_projects` VALUES
(6,'PROJ001','稳健理财计划A','低风险 稳定收益','适合稳健型投资者，收益稳定，风险极低。本项目投资于优质债券和固定收益类资产。','FIXED','CNY',NULL,NULL,30,3.500,0.000,0.000,3.500,1000.00000000,50000.00000000,1000000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,0,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-10 22:41:41'),
(7,'PROJ002','高收益投资B','高回报 快速增值','高收益项目，适合激进型投资者。投资于高成长性企业IPO项目。','IPO','CNY',NULL,NULL,60,5.800,0.000,0.000,5.800,5000.00000000,100000.00000000,5000000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,0,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-10 22:41:41'),
(8,'PROJ003','USDT理财计划','USDT专属 稳定收益','USDT币种专属理财，收益按USDT结算。适合持有USDT的用户。','FIXED','USDT',NULL,NULL,45,4.200,0.000,0.000,4.200,100.00000000,10000.00000000,500000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,0,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-10 22:41:41'),
(9,'PROJ004','基金定投计划','长期投资 复利增长','适合长期投资，享受复利增长。投资于多元化基金组合。','FUND','CNY',NULL,NULL,90,6.500,0.000,0.000,6.500,2000.00000000,80000.00000000,3000000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,1,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-11 22:47:24'),
(10,'PROJ005','短期理财精选','灵活配置 快速回本','15天短期理财，资金灵活。投资于短期债券和货币市场工具。','FIXED','CNY',NULL,NULL,15,2.800,0.000,0.000,2.800,500.00000000,30000.00000000,800000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,0,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-10 22:41:41'),
(11,'PROJ006','USDT增值计划Pro','高收益USDT项目','USDT高收益投资计划，适合专业投资者。投资于区块链优质项目。','IPO','USDT',NULL,NULL,60,7.200,0.000,0.000,7.200,500.00000000,50000.00000000,2000000.00000000,0.0000,NULL,1,1,0,0,0,0,0,0,0,1,30,0,0.00000000,0.00,0.00,NULL,'2025-11-10 22:41:41','2025-11-20 09:17:23');
/*!40000 ALTER TABLE `invest_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_verify_log`
--

DROP TABLE IF EXISTS `kyc_verify_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kyc_verify_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `kyc_id` bigint(20) DEFAULT NULL COMMENT '认证记录ID',
  `similarity` decimal(5,2) DEFAULT NULL COMMENT '相似度',
  `result` enum('success','failed') NOT NULL COMMENT '结果',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='实名认证验证日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_verify_log`
--

LOCK TABLES `kyc_verify_log` WRITE;
/*!40000 ALTER TABLE `kyc_verify_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `kyc_verify_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newbie_bonus_records`
--

DROP TABLE IF EXISTS `newbie_bonus_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbie_bonus_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `amount` decimal(20,8) NOT NULL DEFAULT 888.00000000 COMMENT '体验金金额',
  `status` varchar(20) NOT NULL DEFAULT 'claimed' COMMENT '状态：claimed=已领取',
  `created_at` datetime NOT NULL COMMENT '领取时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新人体验金领取记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbie_bonus_records`
--

LOCK TABLES `newbie_bonus_records` WRITE;
/*!40000 ALTER TABLE `newbie_bonus_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `newbie_bonus_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newbie_gift_claims`
--

DROP TABLE IF EXISTS `newbie_gift_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbie_gift_claims` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `amount` decimal(20,8) NOT NULL DEFAULT 888.00000000 COMMENT '体验金金额',
  `claim_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '领取时间',
  `expire_time` timestamp NULL DEFAULT NULL COMMENT '过期时间（预留）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1=有效，2=已用完，3=已过期，4=已撤回',
  `ip_address` varchar(50) DEFAULT NULL COMMENT '领取IP',
  `device_info` varchar(255) DEFAULT NULL COMMENT '设备信息',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  KEY `idx_claim_time` (`claim_time`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新人体验金领取记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbie_gift_claims`
--

LOCK TABLES `newbie_gift_claims` WRITE;
/*!40000 ALTER TABLE `newbie_gift_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `newbie_gift_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newbie_gift_config`
--

DROP TABLE IF EXISTS `newbie_gift_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbie_gift_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '活动是否开启：0=关闭，1=开启',
  `gift_amount` decimal(20,8) NOT NULL DEFAULT 888.00000000 COMMENT '体验金金额',
  `allowed_project_ids` text DEFAULT NULL COMMENT '允许投资的项目ID（逗号分隔，空表示全部）',
  `require_realname` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否需要实名：0=否，1=是',
  `max_claims_per_day` int(11) NOT NULL DEFAULT 1000 COMMENT '每日最大领取人数（风控）',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '活动开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '活动结束时间',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新人体验金活动配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbie_gift_config`
--

LOCK TABLES `newbie_gift_config` WRITE;
/*!40000 ALTER TABLE `newbie_gift_config` DISABLE KEYS */;
INSERT INTO `newbie_gift_config` VALUES
(1,1,888.00000000,NULL,1,1000,NULL,NULL,'2025-11-19 10:25:59','2025-11-19 10:25:59');
/*!40000 ALTER TABLE `newbie_gift_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newbie_gift_logs`
--

DROP TABLE IF EXISTS `newbie_gift_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbie_gift_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL COMMENT '类型：claim=领取，invest=投资，return=回收，profit=收益',
  `amount` decimal(20,8) NOT NULL COMMENT '金额',
  `before_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '变动前余额',
  `after_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '变动后余额',
  `related_order_id` int(11) unsigned DEFAULT NULL COMMENT '关联订单ID',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_related_order` (`related_order_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新人体验金流水记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbie_gift_logs`
--

LOCK TABLES `newbie_gift_logs` WRITE;
/*!40000 ALTER TABLE `newbie_gift_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `newbie_gift_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newbie_gift_orders`
--

DROP TABLE IF EXISTS `newbie_gift_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbie_gift_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_no` varchar(64) NOT NULL COMMENT '订单号',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `project_id` int(11) unsigned NOT NULL COMMENT '项目ID',
  `amount` decimal(20,8) NOT NULL COMMENT '投资金额（体验金）',
  `expect_profit` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '预期收益',
  `actual_profit` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '实际收益',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '到期时间',
  `settle_time` timestamp NULL DEFAULT NULL COMMENT '结算时间',
  `status` tinyint(2) NOT NULL DEFAULT 1 COMMENT '状态：1=进行中，2=已到期待结算，3=已结算，4=已取消',
  `is_principal_returned` tinyint(1) NOT NULL DEFAULT 0 COMMENT '本金是否已回收：0=否，1=是',
  `is_profit_paid` tinyint(1) NOT NULL DEFAULT 0 COMMENT '收益是否已发放：0=否，1=是',
  `settle_remark` varchar(500) DEFAULT NULL COMMENT '结算备注',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_settle_time` (`settle_time`),
  KEY `idx_user_status_end` (`user_id`,`status`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新人体验金专用订单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbie_gift_orders`
--

LOCK TABLES `newbie_gift_orders` WRITE;
/*!40000 ALTER TABLE `newbie_gift_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `newbie_gift_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `points_exchange_logs`
--

DROP TABLE IF EXISTS `points_exchange_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `points_exchange_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `points_used` int(11) NOT NULL COMMENT '使用积分',
  `amount_received` decimal(20,8) NOT NULL COMMENT '获得金额',
  `exchange_rate` decimal(10,4) NOT NULL COMMENT '兑换汇率',
  `before_points` decimal(20,2) DEFAULT 0.00 COMMENT '兑换前积分',
  `after_points` decimal(20,2) DEFAULT 0.00 COMMENT '兑换后积分',
  `before_balance` decimal(20,8) DEFAULT 0.00000000 COMMENT '兑换前余额',
  `after_balance` decimal(20,8) DEFAULT 0.00000000 COMMENT '兑换后余额',
  `status` tinyint(4) DEFAULT 1 COMMENT '状态：0=失败，1=成功',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分兑换日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `points_exchange_logs`
--

LOCK TABLES `points_exchange_logs` WRITE;
/*!40000 ALTER TABLE `points_exchange_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `points_exchange_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `points_logs`
--

DROP TABLE IF EXISTS `points_logs`;
/*!50001 DROP VIEW IF EXISTS `points_logs`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `points_logs` AS SELECT
 1 AS `id`,
  1 AS `user_id`,
  1 AS `change_amount`,
  1 AS `balance_after`,
  1 AS `biz_type`,
  1 AS `ref_id`,
  1 AS `description`,
  1 AS `created_at` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `project_managers`
--

DROP TABLE IF EXISTS `project_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_managers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_managers`
--

LOCK TABLES `project_managers` WRITE;
/*!40000 ALTER TABLE `project_managers` DISABLE KEYS */;
INSERT INTO `project_managers` VALUES
(1,'张明','首席投资官',NULL,'拥有15年投资经验，专注于股权投资和并购',1,'2025-11-10 04:27:14'),
(2,'李华','高级基金经理',NULL,'管理超过100亿资产，擅长科技领域投资',1,'2025-11-10 04:27:14');
/*!40000 ALTER TABLE `project_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recharge_records`
--

DROP TABLE IF EXISTS `recharge_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recharge_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '充值记录ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `amount` decimal(20,8) NOT NULL COMMENT '充值金额',
  `payment_method` varchar(50) NOT NULL DEFAULT 'BANK' COMMENT '支付方式',
  `certificate_images` text DEFAULT NULL COMMENT 'JSON数组',
  `currency` enum('CNY','USDT') NOT NULL DEFAULT 'CNY' COMMENT '币种',
  `status` tinyint(4) DEFAULT 0 COMMENT '0待审 1通过 2拒绝',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `reviewed_at` datetime DEFAULT NULL COMMENT '审核时间',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharge_records`
--

LOCK TABLES `recharge_records` WRITE;
/*!40000 ALTER TABLE `recharge_records` DISABLE KEYS */;
INSERT INTO `recharge_records` VALUES
(1,'RCH20251110000002',2,44915.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-10-16 23:42:40'),
(2,'RCH20251110000003',3,14190.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-10-18 23:42:40'),
(3,'RCH20251110000004',4,26205.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-10-21 23:42:40'),
(4,'RCH20251110000005',5,83457.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-10-23 23:42:40'),
(5,'RCH20251110000006',6,48672.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-10-25 23:42:40'),
(6,'RCH20251110000007',7,82986.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-10-26 23:42:40'),
(7,'RCH20251110000008',8,73916.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-10-29 23:42:40'),
(8,'RCH20251110000009',9,20621.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-10-31 23:42:40'),
(9,'RCH20251110000010',10,66358.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-11-01 23:42:40'),
(10,'RCH20251110000011',11,74928.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-11-02 23:42:40'),
(11,'RCH20251110000012',12,75568.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-11-03 23:42:40'),
(12,'RCH20251110000013',13,53057.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-11-04 23:42:40'),
(13,'RCH20251110000014',14,33581.00000000,'BANK','/uploads/proof/example.jpg','CNY',1,NULL,NULL,'2025-11-05 23:42:40'),
(14,'RCH20251110000015',15,98732.00000000,'BANK','/uploads/proof/example.jpg','USDT',1,NULL,NULL,'2025-11-06 23:42:40');
/*!40000 ALTER TABLE `recharge_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ribao_logs`
--

DROP TABLE IF EXISTS `ribao_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ribao_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `type` enum('in','out') NOT NULL COMMENT '类型：in=转入，out=转出',
  `amount` decimal(20,8) NOT NULL COMMENT '金额',
  `before_balance` decimal(20,8) DEFAULT 0.00000000 COMMENT '操作前余额',
  `after_balance` decimal(20,8) DEFAULT 0.00000000 COMMENT '操作后余额',
  `status` tinyint(4) DEFAULT 1 COMMENT '状态：0=处理中，1=成功，-1=失败',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日利宝转入转出记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ribao_logs`
--

LOCK TABLES `ribao_logs` WRITE;
/*!40000 ALTER TABLE `ribao_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ribao_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_config`
--

DROP TABLE IF EXISTS `system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` text DEFAULT NULL COMMENT '配置值（JSON格式）',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_config`
--

LOCK TABLES `system_config` WRITE;
/*!40000 ALTER TABLE `system_config` DISABLE KEYS */;
INSERT INTO `system_config` VALUES
(1,'ribao_settings','{\"daily_rate\":\"0.00100000\",\"min_amount\":\"100.00000000\",\"max_amount\":\"1000000.00000000\",\"is_enabled\":true,\"settlement_time\":\"00:00:00\"}','日利宝配置','2025-11-11 15:16:11','2025-11-11 22:35:25');
/*!40000 ALTER TABLE `system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_reward_logs`
--

DROP TABLE IF EXISTS `team_reward_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_reward_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `rule_id` int(11) NOT NULL COMMENT '规则ID',
  `team_members` int(11) NOT NULL COMMENT '团队人数',
  `team_invest` decimal(20,8) NOT NULL COMMENT '团队累计投资',
  `reward_points` int(11) NOT NULL COMMENT '奖励积分',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:1已发放0待发放',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='团队奖励发放记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_reward_logs`
--

LOCK TABLES `team_reward_logs` WRITE;
/*!40000 ALTER TABLE `team_reward_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_reward_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_reward_rules`
--

DROP TABLE IF EXISTS `team_reward_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_reward_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `min_members` int(11) NOT NULL COMMENT '最少下级人数',
  `min_invest` decimal(20,8) NOT NULL COMMENT '最低累计投资',
  `reward_points` int(11) NOT NULL COMMENT '奖励积分',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:1启用0禁用',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_members_invest` (`min_members`,`min_invest`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='团队管理奖励规则表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_reward_rules`
--

LOCK TABLES `team_reward_rules` WRITE;
/*!40000 ALTER TABLE `team_reward_rules` DISABLE KEYS */;
INSERT INTO `team_reward_rules` VALUES
(1,3,80000.00000000,1800,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(2,5,150000.00000000,2500,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(3,10,500000.00000000,8800,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(4,20,1500000.00000000,18000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(5,50,3800000.00000000,25000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(6,100,8800000.00000000,38000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(7,200,15000000.00000000,66000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(8,500,58000000.00000000,100000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50'),
(9,1000,98000000.00000000,180000,1,'2025-11-19 23:26:50','2025-11-19 23:26:50');
/*!40000 ALTER TABLE `team_reward_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_bank_cards`
--

DROP TABLE IF EXISTS `user_bank_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_bank_cards` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '银行名称',
  `bank_card` varchar(30) DEFAULT NULL COMMENT '银行卡号',
  `bank_branch` varchar(100) DEFAULT NULL COMMENT '开户行',
  `usdt_address` varchar(100) DEFAULT NULL COMMENT 'USDT地址',
  `is_default` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否默认 0否 1是',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户银行卡表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_bank_cards`
--

LOCK TABLES `user_bank_cards` WRITE;
/*!40000 ALTER TABLE `user_bank_cards` DISABLE KEYS */;
INSERT INTO `user_bank_cards` VALUES
(2,2,'中国工商银行','6222478070139398','北京分行',NULL,1,'2025-11-10 22:54:17','2025-11-10 22:54:17'),
(3,3,'中国工商银行','6222750476521206','北京分行',NULL,1,'2025-11-10 22:54:17','2025-11-10 22:54:17'),
(4,4,'中国工商银行','6222318172218574','北京分行',NULL,1,'2025-11-10 22:54:17','2025-11-10 22:54:17'),
(5,5,'中国工商银行','6222339431559982','北京分行',NULL,1,'2025-11-10 22:54:17','2025-11-10 22:54:17');
/*!40000 ALTER TABLE `user_bank_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_kyc`
--

DROP TABLE IF EXISTS `user_kyc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_kyc` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `id_card` varchar(20) NOT NULL,
  `id_card_front` varchar(255) DEFAULT NULL,
  `id_card_back` varchar(255) DEFAULT NULL,
  `hand_held_photo` varchar(255) DEFAULT NULL,
  `face_similarity` decimal(5,2) DEFAULT NULL COMMENT '人脸相似度',
  `face_check_result` varchar(20) DEFAULT NULL COMMENT '人脸识别结果：auto_passed/manual_review',
  `face_check_time` datetime DEFAULT NULL COMMENT '人脸识别时间',
  `submit_time` datetime DEFAULT NULL COMMENT '提交时间',
  `status` tinyint(4) DEFAULT 0 COMMENT '0待审 1通过 2拒绝',
  `reject_reason` varchar(500) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_kyc`
--

LOCK TABLES `user_kyc` WRITE;
/*!40000 ALTER TABLE `user_kyc` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_kyc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_login_logs`
--

DROP TABLE IF EXISTS `user_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `login_ip` varchar(50) NOT NULL COMMENT '登录IP',
  `login_location` varchar(100) DEFAULT NULL COMMENT 'IP归属地',
  `device_type` varchar(50) DEFAULT NULL COMMENT '设备类型',
  `device_info` text DEFAULT NULL COMMENT '设备详细信息',
  `user_agent` text DEFAULT NULL COMMENT 'UserAgent',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `status` tinyint(1) DEFAULT 1 COMMENT '登录状态 1成功 0失败',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户登录历史记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_login_logs`
--

LOCK TABLES `user_login_logs` WRITE;
/*!40000 ALTER TABLE `user_login_logs` DISABLE KEYS */;
INSERT INTO `user_login_logs` VALUES
(1,1,'162.158.163.176',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 19:31:01',0,'密码错误'),
(2,1,'172.69.165.42',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 19:31:15',1,NULL),
(3,1,'172.70.208.167',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 20:08:09',1,NULL),
(4,1,'104.23.175.167',NULL,'Mac',NULL,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-11 20:14:06',1,NULL),
(5,0,'172.70.208.167',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 22:39:57',0,'用户不存在: adminlu'),
(6,1,'172.70.208.167',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 22:40:42',1,NULL),
(7,1,'172.70.208.167',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 22:40:44',1,NULL),
(8,1,'172.70.143.217',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-11 22:41:10',1,NULL),
(9,1,'162.158.106.76',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-12 01:30:50',1,NULL),
(10,1,'172.70.208.166',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-12 01:30:54',1,NULL),
(11,1,'172.71.124.49',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-12 01:30:58',1,NULL),
(12,1,'162.158.88.69',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 18:19:45',1,NULL),
(13,1,'162.158.88.69',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 18:20:05',1,NULL),
(14,1,'172.70.143.211',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:14:01',1,NULL),
(15,1,'162.158.107.78',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:15:07',1,NULL),
(16,1,'162.158.107.78',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:15:22',1,NULL),
(17,1,'162.158.107.78',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:15:39',1,NULL),
(18,1,'162.158.107.78',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:15:58',1,NULL),
(19,1,'172.71.124.49',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:16:14',1,NULL),
(20,1,'104.23.175.6',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.128 Mobile/15E148 Safari/604.1','2025-11-12 19:20:17',1,NULL),
(21,1,'127.0.0.1',NULL,'web','{}','curl/8.5.0','2025-11-20 00:47:46',1,'登录成功'),
(22,1,'162.158.162.57',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 00:47:48',1,'登录成功'),
(23,1,'162.158.162.57',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 00:47:56',1,'登录成功'),
(24,1,'162.158.88.109',NULL,'web','{}','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-20 00:48:27',1,'登录成功'),
(25,1,'172.71.81.228',NULL,'web','{}','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-20 00:49:23',1,'登录成功'),
(26,1,'172.71.81.6',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 00:49:35',1,'登录成功'),
(27,1,'172.71.81.6',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 00:50:05',1,'登录成功'),
(28,1,'172.71.124.127',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 00:50:10',1,'登录成功'),
(29,1,'172.71.124.127',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 00:50:18',1,'登录成功'),
(30,1,'162.158.108.98',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:13:22',1,'登录成功'),
(31,1,'162.158.88.109',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:19:06',1,'登录成功'),
(32,1,'172.71.124.126',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:25:19',1,'登录成功'),
(33,1,'172.71.124.126',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:25:31',1,'登录成功'),
(34,1,'172.69.176.171',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:27:14',1,'登录成功'),
(35,1,'172.69.176.171',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:27:28',1,'登录成功'),
(36,1,'172.71.81.5',NULL,'web','{}','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-20 01:27:37',1,'登录成功'),
(37,1,'162.158.162.56',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:28:20',1,'登录成功'),
(38,1,'172.68.164.21',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:28:58',1,'登录成功'),
(39,1,'172.71.81.6',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:39:48',1,'登录成功'),
(40,1,'104.23.175.68',NULL,'web','{}','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-20 01:40:13',1,'登录成功'),
(41,1,'162.158.88.108',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:44:38',1,'登录成功'),
(42,1,'172.68.164.21',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:58:22',1,'登录成功'),
(43,1,'172.68.164.21',NULL,'web','{}','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 01:59:13',1,'登录成功'),
(44,1,'162.158.108.98',NULL,'web','{}','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-20 02:04:32',1,'登录成功'),
(45,0,'172.70.93.17',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 04:07:39',0,'用户不存在: test'),
(46,0,'104.23.175.90',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 04:11:04',0,'用户不存在: test'),
(47,0,'172.69.166.26',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 04:36:24',0,'用户不存在: test'),
(48,0,'162.158.107.35',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 05:46:42',0,'用户不存在: test'),
(49,0,'172.70.208.88',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 05:52:48',0,'用户不存在: test'),
(50,0,'172.70.208.88',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 05:52:50',0,'用户不存在: test'),
(51,0,'108.162.226.119',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:22:44',0,'用户不存在: 1'),
(52,0,'172.70.142.20',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:26:16',0,'用户不存在: 1'),
(53,0,'172.70.142.20',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:26:19',0,'用户不存在: 1'),
(54,0,'104.23.175.91',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:30:16',0,'用户不存在: zlzmm5188@gmail.com'),
(55,0,'162.158.88.139',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 07:31:13',0,'用户不存在: test'),
(56,0,'162.158.26.56',NULL,NULL,NULL,'curl/8.5.0','2025-11-20 07:31:19',0,'用户不存在: test'),
(57,0,'108.162.226.118',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X)','2025-11-20 07:31:59',0,'用户不存在: test'),
(58,0,'172.68.164.82',NULL,NULL,NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15','2025-11-20 07:39:21',0,'用户不存在: test'),
(59,1,'162.158.26.56',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 07:50:08',1,NULL),
(60,1,'172.69.176.28',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:50:21',1,NULL),
(61,1,'172.69.176.28',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:50:32',1,NULL),
(62,1,'172.70.93.16',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 07:55:43',1,NULL),
(63,1,'172.71.82.36',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 07:57:04',1,NULL),
(64,1,'172.69.166.26',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 07:58:11',1,NULL),
(65,1,'172.71.124.61',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 07:58:12',1,NULL),
(66,1,'162.158.163.173',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 07:59:04',1,NULL),
(67,1,'162.158.107.36',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 08:06:05',1,NULL),
(68,1,'172.70.142.226',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 08:20:59',1,NULL),
(69,1,'162.158.26.57',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 08:21:23',1,NULL),
(70,1,'162.158.26.57',NULL,'Unknown',NULL,'curl/8.5.0','2025-11-20 08:27:34',1,NULL),
(71,1,'172.68.242.103',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','2025-11-20 08:27:55',1,NULL),
(72,1,'172.71.124.61',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_1_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 08:43:32',1,NULL),
(73,1,'104.23.175.91',NULL,'iOS',NULL,'Mozilla/5.0 (iPhone; CPU iPhone OS 26_1_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1','2025-11-20 08:49:33',1,NULL);
/*!40000 ALTER TABLE `user_login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_points_logs`
--

DROP TABLE IF EXISTS `user_points_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_points_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `change_amount` decimal(20,2) NOT NULL COMMENT '变动积分',
  `balance_after` decimal(20,2) NOT NULL COMMENT '变动后余额',
  `biz_type` enum('SIGN','EXCHANGE','REWARD','ADMIN','REFUND') NOT NULL COMMENT '业务类型',
  `ref_id` bigint(20) DEFAULT NULL COMMENT '关联ID',
  `description` varchar(255) DEFAULT NULL COMMENT '说明',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_biz_type` (`biz_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户积分流水表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_points_logs`
--

LOCK TABLES `user_points_logs` WRITE;
/*!40000 ALTER TABLE `user_points_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_points_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sign_logs`
--

DROP TABLE IF EXISTS `user_sign_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sign_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `points` int(11) NOT NULL DEFAULT 0 COMMENT '获得积分',
  `signed_at` datetime NOT NULL COMMENT '签到时间',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`user_id`,`signed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户签到记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sign_logs`
--

LOCK TABLES `user_sign_logs` WRITE;
/*!40000 ALTER TABLE `user_sign_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sign_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Token ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `token` varchar(255) NOT NULL COMMENT 'Token字符串',
  `expires_at` datetime NOT NULL COMMENT '过期时间',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 1=有效 0=失效',
  `device` varchar(100) DEFAULT NULL COMMENT '设备信息',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户Token表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
INSERT INTO `user_tokens` VALUES
(1,1,'test-token-123','2025-11-27 07:58:44',1,NULL,NULL,'2025-11-20 07:58:44','2025-11-20 07:58:44'),
(2,1,'ecaf572332d727a9ca8a55aee34d3eaba0121031b34512ab74d3897ea53338fa','2025-11-27 08:27:34',1,NULL,NULL,'2025-11-20 08:27:34','2025-11-20 08:27:34'),
(3,1,'b8f25b153d4776ae0bcfa9b006ff9ed19f92b78f83b1a1ba8a492bdd14dfbe55','2025-11-27 08:27:55',1,NULL,NULL,'2025-11-20 08:27:55','2025-11-20 08:27:55'),
(4,1,'ec4beacc9ba3a78820190056c18b3a5f82293288db3fb70dbfa6dd5f6cf8827c','2025-11-27 08:43:32',1,NULL,NULL,'2025-11-20 08:43:32','2025-11-20 08:43:32'),
(5,1,'0db3dcc2c977fc79f9b81a66bf0abb4de1ed5a10fd2742287ea830e06048be54','2025-11-27 08:49:33',1,NULL,NULL,'2025-11-20 08:49:33','2025-11-20 08:49:33');
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_usdt_addresses`
--

DROP TABLE IF EXISTS `user_usdt_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_usdt_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `chain` varchar(20) NOT NULL DEFAULT 'TRC20' COMMENT '链类型：TRC20/ERC20/OMNI',
  `address` varchar(100) NOT NULL COMMENT 'USDT地址',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已验证：0=否，1=是',
  `verify_time` timestamp NULL DEFAULT NULL COMMENT '验证时间',
  `is_default` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否默认地址',
  `bind_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '绑定时间',
  `last_modified_time` timestamp NULL DEFAULT NULL COMMENT '最后修改时间',
  `modify_count` int(11) NOT NULL DEFAULT 0 COMMENT '修改次数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1=正常，2=冻结，3=已删除',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_chain` (`user_id`,`chain`,`is_default`),
  KEY `idx_address` (`address`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户USDT地址表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_usdt_addresses`
--

LOCK TABLES `user_usdt_addresses` WRITE;
/*!40000 ALTER TABLE `user_usdt_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_usdt_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `uid` char(8) NOT NULL COMMENT '随机8位邀请码/外显ID',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `phone` varchar(24) DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `password` varchar(255) NOT NULL COMMENT '密码（bcrypt加密）',
  `token` varchar(255) DEFAULT NULL COMMENT '当前Token',
  `trade_password` varchar(255) DEFAULT NULL COMMENT '交易密码',
  `realname_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0待审 1通过 2拒绝',
  `vip_level` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'VIP等级（0-8）',
  `gift_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '体验金余额',
  `has_claimed_gift` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已领取体验金：0=否，1=是',
  `gift_claim_time` timestamp NULL DEFAULT NULL COMMENT '体验金领取时间',
  `total_invest` decimal(20,8) DEFAULT 0.00000000 COMMENT '累计投资金额（用于VIP升级）',
  `is_internal` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0普通 1内部人员',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1正常 2冻结 3禁用',
  `parent_id` bigint(20) DEFAULT NULL COMMENT '推荐人ID',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_uid` (`uid`),
  KEY `idx_users_parent` (`parent_id`),
  KEY `idx_users_vip` (`vip_level`),
  KEY `idx_users_status` (`status`),
  KEY `idx_uid` (`uid`),
  KEY `idx_gift_claim` (`has_claimed_gift`,`gift_claim_time`),
  KEY `idx_token` (`token`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`),
  CONSTRAINT `users_chk_1` CHECK (`vip_level` between 0 and 8)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'G138688','G138688',NULL,NULL,'$2y$10$2mqD0kJc/DIscXsg2.jwkecH09eK79rwkEe3Iz6EBKAtL./sfzqY2','0db3dcc2c977fc79f9b81a66bf0abb4de1ed5a10fd2742287ea830e06048be54',NULL,1,8,0.00000000,0,NULL,0.00000000,1,1,NULL,'2025-11-10 14:37:43','2025-11-20 08:49:33'),
(2,'U522166','张三',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,81643.00000000,0,1,1,'2025-10-16 22:42:40','2025-11-10 22:43:39'),
(3,'U791876','李四',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,68651.00000000,0,1,1,'2025-10-18 22:42:40','2025-11-10 22:43:39'),
(4,'U492884','王五',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,51016.00000000,0,1,2,'2025-10-21 22:42:40','2025-11-10 22:43:39'),
(5,'U888794','赵六',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,55825.00000000,0,1,2,'2025-10-23 22:42:40','2025-11-10 22:43:39'),
(6,'U165320','孙七',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,83910.00000000,0,1,3,'2025-10-25 22:42:40','2025-11-10 22:43:39'),
(7,'U760215','周八',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,80448.00000000,0,1,3,'2025-10-26 22:42:40','2025-11-10 22:43:39'),
(8,'U505117','吴九',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,17909.00000000,0,1,4,'2025-10-29 22:42:40','2025-11-10 22:43:39'),
(9,'U144942','郑十',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,4,'2025-10-31 22:42:40','2025-11-10 22:42:40'),
(10,'U909358','冯十一',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,5,'2025-11-01 22:42:40','2025-11-10 22:42:40'),
(11,'U411967','陈十二',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,5,'2025-11-02 22:42:40','2025-11-10 22:42:40'),
(12,'U131760','褚十三',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,6,'2025-11-03 22:42:40','2025-11-10 22:42:40'),
(13,'U222901','卫十四',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,6,'2025-11-04 22:42:40','2025-11-10 22:42:40'),
(14,'U619225','蒋十五',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,7,'2025-11-05 22:42:40','2025-11-10 22:42:40'),
(15,'U527425','沈十六',NULL,NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,0,0,0.00000000,0,NULL,0.00000000,0,1,7,'2025-11-06 22:42:40','2025-11-10 22:42:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip_interest_rules`
--

DROP TABLE IF EXISTS `vip_interest_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vip_interest_rules` (
  `vip_level` tinyint(4) NOT NULL,
  `vip_name` varchar(50) NOT NULL,
  `min_invest` decimal(18,2) NOT NULL,
  `extra_rate` decimal(6,3) NOT NULL,
  `extra_rate_display` varchar(20) DEFAULT NULL,
  `level1_percent` decimal(5,2) DEFAULT 0.00,
  `level2_percent` decimal(5,2) DEFAULT 0.00,
  `daily_withdraw_limit` decimal(18,2) DEFAULT NULL,
  `withdraw_fee_rate` decimal(5,4) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`vip_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip_interest_rules`
--

LOCK TABLES `vip_interest_rules` WRITE;
/*!40000 ALTER TABLE `vip_interest_rules` DISABLE KEYS */;
INSERT INTO `vip_interest_rules` VALUES
(0,'VIP0',0.00,0.000,'+0%',1.00,0.00,NULL,NULL,'2025-11-10 04:27:14'),
(1,'VIP1',30000.00,0.050,'+0.05%',2.00,1.00,NULL,NULL,'2025-11-10 04:27:14'),
(2,'VIP2',100000.00,0.100,'+0.1%',3.00,2.00,NULL,NULL,'2025-11-10 04:27:14'),
(3,'VIP3',250000.00,0.120,'+0.12%',4.00,2.00,NULL,NULL,'2025-11-10 04:27:14'),
(4,'VIP4',800000.00,0.150,'+0.15%',5.00,3.00,NULL,NULL,'2025-11-10 04:27:14'),
(5,'VIP5',1500000.00,0.160,'+0.16%',5.00,4.00,NULL,NULL,'2025-11-10 04:27:14'),
(6,'VIP6',3800000.00,0.180,'+0.18%',6.00,4.00,NULL,NULL,'2025-11-10 04:27:14'),
(7,'VIP7',8000000.00,0.230,'+0.23%',6.00,5.00,NULL,NULL,'2025-11-10 04:27:14'),
(8,'VIP8',13000000.00,0.250,'+0.25%',7.00,5.00,NULL,NULL,'2025-11-10 04:27:14');
/*!40000 ALTER TABLE `vip_interest_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip_level_rules`
--

DROP TABLE IF EXISTS `vip_level_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vip_level_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` tinyint(4) NOT NULL COMMENT 'VIP等级',
  `name` varchar(50) NOT NULL COMMENT '等级名称',
  `min_invest` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '最低累计投资额',
  `interest_rate` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT '加息比例(%)',
  `daily_withdraw_limit` decimal(20,8) DEFAULT NULL COMMENT '每日提现限额',
  `monthly_withdraw_limit` decimal(20,8) DEFAULT NULL COMMENT '每月提现限额',
  `withdraw_fee_rate` decimal(5,3) NOT NULL DEFAULT 0.000 COMMENT '提现手续费率(%)',
  `priority_customer_service` tinyint(4) NOT NULL DEFAULT 0 COMMENT '专属客服(0=否,1=是)',
  `exclusive_projects` tinyint(4) NOT NULL DEFAULT 0 COMMENT '专属项目(0=否,1=是)',
  `birthday_bonus` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '生日礼金',
  `description` text DEFAULT NULL COMMENT '等级说明',
  `icon` varchar(255) DEFAULT NULL COMMENT '等级图标',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_level` (`level`),
  KEY `idx_min_invest` (`min_invest`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='VIP等级规则表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip_level_rules`
--

LOCK TABLES `vip_level_rules` WRITE;
/*!40000 ALTER TABLE `vip_level_rules` DISABLE KEYS */;
INSERT INTO `vip_level_rules` VALUES
(1,0,'VIP0',0.00000000,0.000,NULL,NULL,0.500,0,0,0.00,'注册用户',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27'),
(2,1,'VIP1',10000.00000000,0.100,NULL,NULL,0.300,0,0,0.00,'累计投资1万解锁',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27'),
(3,2,'VIP2',50000.00000000,0.200,NULL,NULL,0.200,0,0,0.00,'累计投资5万解锁',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27'),
(4,3,'VIP3',100000.00000000,0.300,NULL,NULL,0.100,0,0,0.00,'累计投资10万解锁',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27'),
(5,4,'VIP4',500000.00000000,0.500,NULL,NULL,0.050,0,0,0.00,'累计投资50万解锁',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27'),
(6,5,'VIP5',1000000.00000000,0.800,NULL,NULL,0.000,0,0,0.00,'累计投资100万解锁',NULL,'2025-11-11 22:49:27','2025-11-11 22:49:27');
/*!40000 ALTER TABLE `vip_level_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_logs`
--

DROP TABLE IF EXISTS `wallet_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `wallet_id` bigint(20) NOT NULL,
  `currency` enum('CNY','USDT') NOT NULL,
  `change_amount` decimal(24,8) NOT NULL COMMENT '正负数',
  `balance_after` decimal(24,8) NOT NULL,
  `biz_type` enum('RECHARGE','WITHDRAW','SUBSCRIBE','UNFREEZE','INCOME','REWARD') NOT NULL,
  `ref_id` bigint(20) DEFAULT NULL COMMENT '关联订单/审核ID',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '元数据' CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `idx_wallet_logs_user_time` (`user_id`,`created_at`),
  KEY `idx_wallet_logs_type` (`biz_type`),
  KEY `idx_wallet_logs_ref` (`ref_id`),
  CONSTRAINT `wallet_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `wallet_logs_ibfk_2` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包流水';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_logs`
--

LOCK TABLES `wallet_logs` WRITE;
/*!40000 ALTER TABLE `wallet_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `wallet_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallets`
--

DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '钱包ID',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `currency` enum('CNY','USDT') NOT NULL COMMENT '币种（CNY/USDT）',
  `balance` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '可用余额',
  `bonus_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000 COMMENT '体验金余额（虚拟本金）',
  `frozen` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '冻结金额',
  `ribao_balance` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '日利宝余额',
  `ribao_total_profit` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '日利宝累计收益',
  `ribao_yesterday_profit` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '日利宝昨日收益',
  `points` decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '积分余额',
  `points_frozen` decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '冻结积分',
  `total_income` decimal(24,8) NOT NULL DEFAULT 0.00000000 COMMENT '累计收益',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_currency` (`user_id`,`currency`),
  KEY `idx_wallets_user` (`user_id`),
  CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `wallets_chk_1` CHECK (`balance` >= 0),
  CONSTRAINT `wallets_chk_2` CHECK (`frozen` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='币种钱包';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallets`
--

LOCK TABLES `wallets` WRITE;
/*!40000 ALTER TABLE `wallets` DISABLE KEYS */;
INSERT INTO `wallets` VALUES
(1,2,'CNY',11065.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(2,3,'CNY',4779.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(3,4,'CNY',40703.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(4,5,'CNY',39180.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(5,6,'CNY',23788.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(6,7,'CNY',1401.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(7,8,'CNY',35643.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(8,9,'CNY',24013.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(9,10,'CNY',13134.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(10,11,'CNY',43633.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(11,12,'CNY',28762.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(12,13,'CNY',12913.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(13,14,'CNY',28279.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(14,15,'CNY',2655.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(15,1,'CNY',28440.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(16,2,'USDT',3423.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(17,3,'USDT',3586.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(18,4,'USDT',2659.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(19,5,'USDT',2537.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(20,6,'USDT',4710.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(21,7,'USDT',937.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(22,8,'USDT',559.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(23,9,'USDT',4982.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(24,10,'USDT',3234.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(25,11,'USDT',1225.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(26,12,'USDT',1422.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(27,13,'USDT',3436.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(28,14,'USDT',2913.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(29,15,'USDT',4258.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29'),
(30,1,'USDT',2551.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00000000,0.00,0.00,0.00000000,'2025-11-10 22:47:29','2025-11-10 22:47:29');
/*!40000 ALTER TABLE `wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdraw_records`
--

DROP TABLE IF EXISTS `withdraw_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdraw_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '提现记录ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `amount` decimal(20,8) NOT NULL COMMENT '提现金额',
  `fee` decimal(20,8) DEFAULT 0.00000000 COMMENT '手续费',
  `actual_amount` decimal(20,8) DEFAULT NULL COMMENT '实际到账金额',
  `withdraw_method` varchar(50) NOT NULL DEFAULT 'BANK' COMMENT '提现方式',
  `bank_info` text DEFAULT NULL COMMENT '银行卡信息（JSON）',
  `currency` enum('CNY','USDT') NOT NULL DEFAULT 'CNY' COMMENT '币种',
  `status` tinyint(4) DEFAULT 0 COMMENT '0待审 1通过 2拒绝 3已打款',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `reviewed_at` datetime DEFAULT NULL COMMENT '审核时间',
  `paid_at` datetime DEFAULT NULL COMMENT '打款时间',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdraw_records`
--

LOCK TABLES `withdraw_records` WRITE;
/*!40000 ALTER TABLE `withdraw_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdraw_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `points_logs`
--

/*!50001 DROP VIEW IF EXISTS `points_logs`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`providence`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `points_logs` AS select `user_points_logs`.`id` AS `id`,`user_points_logs`.`user_id` AS `user_id`,`user_points_logs`.`change_amount` AS `change_amount`,`user_points_logs`.`balance_after` AS `balance_after`,`user_points_logs`.`biz_type` AS `biz_type`,`user_points_logs`.`ref_id` AS `ref_id`,`user_points_logs`.`description` AS `description`,`user_points_logs`.`created_at` AS `created_at` from `user_points_logs` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-20  9:23:34
