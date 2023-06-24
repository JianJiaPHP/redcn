/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : localhost:3306
 Source Schema         : redchina

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 25/06/2023 00:25:07
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for accumulate_config
-- ----------------------------
DROP TABLE IF EXISTS `accumulate_config`;
CREATE TABLE `accumulate_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(255) NOT NULL COMMENT '1团队 2邀请',
  `key` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `num` int(11) NULL DEFAULT NULL COMMENT '要求数量',
  `value` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '奖励比例',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '累计奖励配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of accumulate_config
-- ----------------------------
INSERT INTO `accumulate_config` VALUES (1, 1, '团队业绩达到10万奖励团队总金额1%', 100000, '0.01', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (2, 1, '团队业绩达到30万奖励团队总金额1.5%', 300000, '0.02', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (3, 1, '团队业绩达到50万奖励团队总金额2.5%', 500000, '0.02', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (4, 1, '团队业绩达到80万奖励团队总金额3%', 800000, '0.03', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (5, 1, '团队业绩达到100万奖励团队总金额4%', 1000000, '0.04', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (6, 1, '团队业绩达到200万奖励团队总金额6%', 2000000, '0.06', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (7, 1, '团队业绩达到300万奖励团队总金额10%', 3000000, '0.10', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (8, 2, '邀请0-5人有效会员奖励188元', 5, '188', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (9, 2, '邀请0-10人有效会员奖励388元', 10, '388', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (10, 2, '邀请0-30人有效会员奖励888元', 30, '888', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (11, 2, '邀请0-80人有效会员奖励3888元', 80, '3888', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (12, 2, '邀请0-150人有效会员奖励8888元', 150, '8888', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (13, 2, '邀请0-300人有效会员奖励28888元', 300, '28888', '2023-06-24 15:59:21', '2023-06-24 15:59:23');
INSERT INTO `accumulate_config` VALUES (14, 2, '邀请0-500人有效会员奖励58888元', 500, '58888', '2023-06-24 15:59:21', '2023-06-24 15:59:23');

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '组',
  `key` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置key',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置值',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '配置描述',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sy_config_id_index`(`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES (1, 'admin', 'admin_name', '', '后台名', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (5, 'douyin', 'callback', '', '抖音回调地址', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (8, 'withdrawal', 'min', '', '提现最低提现金额', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (9, 'withdrawal', 'max', '', '最高提现金额', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (10, 'withdrawal', 'fee', '', '提现手续费', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (26, 'distribution', 'one', '0.02', '推荐奖一代18%', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (27, 'distribution', 'two', '0.18', '推荐奖二代2%', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (28, 'sign', ' in', '1', '每日签到赠送红旗', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (29, 'invitation', 'award', '50', '邀请用户奖励金额（元）', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (30, 'invitation', 'value', '6000', '注册赠送金额', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (31, 'sign', 'value', '5', '红旗价值', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (16, 'service', 'phone_herf', '', 'APP客服联系地址', '2023-06-24 22:17:29', '2023-06-24 22:17:29');
INSERT INTO `config` VALUES (24, 'agent', 'agent_name', '', '代理商后台名', '2023-06-24 22:17:29', '2023-06-24 22:17:29');

-- ----------------------------
-- Table structure for goods
-- ----------------------------
DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NULL DEFAULT NULL COMMENT '1:养老 2:医疗 3：教育',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '产品名称',
  `amount` decimal(11, 2) NULL DEFAULT NULL COMMENT '金额',
  `income` decimal(11, 2) NULL DEFAULT NULL COMMENT '日收益',
  `validity_day` int(10) NOT NULL COMMENT '有效期 天',
  `introduce` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '持续6个月 共180天一天60 一共23800元 商品介绍',
  `end_rewards` decimal(11, 2) NULL DEFAULT NULL COMMENT '最后一天的奖励',
  `img` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '商品海报图',
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '商品富文本介绍',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '产品表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of goods
-- ----------------------------

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL COMMENT '分类id',
  `key_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'key值',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标题',
  `content_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '富文本',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '链接地址',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '最后修改时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key_name`(`key_name`) USING BTREE,
  INDEX `id`(`id`, `class_id`, `key_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '富文本新闻页' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of news
-- ----------------------------

-- ----------------------------
-- Table structure for pay_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_order`;
CREATE TABLE `pay_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '统一下单订单号',
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `total_amount` decimal(10, 2) NOT NULL COMMENT '当时下单金额',
  `pay_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付状态 1：待支付 2：已支付 3：已回调 4：已取消/支付失败',
  `pay_type` tinyint(1) NOT NULL COMMENT '1:支付宝支付  2：微信支付  3：其他',
  `pay_user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付到账账号',
  `json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '参数体json',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  `goods_id` int(11) NULL DEFAULT NULL COMMENT '商品id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '支付订单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pay_order
-- ----------------------------

-- ----------------------------
-- Table structure for sign_log
-- ----------------------------
DROP TABLE IF EXISTS `sign_log`;
CREATE TABLE `sign_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `value` int(255) NULL DEFAULT NULL COMMENT '获得数量',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户签到记录' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of sign_log
-- ----------------------------
INSERT INTO `sign_log` VALUES (1, 1, 1, '2023-06-01 21:26:11', '2023-06-24 21:26:12');
INSERT INTO `sign_log` VALUES (2, 1, 1, '2023-06-02 21:26:16', '2023-06-24 21:26:18');
INSERT INTO `sign_log` VALUES (3, 1, 1, '2023-06-03 21:26:26', '2023-06-01 21:26:35');
INSERT INTO `sign_log` VALUES (4, 1, 1, '2023-06-06 21:26:11', '2023-06-24 21:26:12');
INSERT INTO `sign_log` VALUES (5, 1, 1, '2023-06-07 21:26:16', '2023-06-24 21:26:18');
INSERT INTO `sign_log` VALUES (6, 1, 1, '2023-06-09 21:26:26', '2023-06-01 21:26:35');
INSERT INTO `sign_log` VALUES (7, 1, 1, '2023-06-22 21:26:11', '2023-06-24 21:26:12');
INSERT INTO `sign_log` VALUES (8, 1, 1, '2023-06-23 21:26:16', '2023-06-24 21:26:18');
INSERT INTO `sign_log` VALUES (9, 1, 1, '2023-06-21 21:26:26', '2023-06-01 21:26:35');
INSERT INTO `sign_log` VALUES (10, 1, 1, '2023-06-20 22:49:38', '2023-06-24 22:49:38');
INSERT INTO `sign_log` VALUES (15, 1, 1, '2023-06-24 23:01:44', '2023-06-24 23:01:44');

-- ----------------------------
-- Table structure for user_account
-- ----------------------------
DROP TABLE IF EXISTS `user_account`;
CREATE TABLE `user_account`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `old_balance` decimal(11, 2) NOT NULL COMMENT '变化前总余额',
  `profit` decimal(11, 2) NOT NULL COMMENT '收入/支出',
  `total_balance` decimal(11, 2) NOT NULL COMMENT '更新后总额',
  `type` tinyint(1) NOT NULL COMMENT '1.下级购买商品奖励\r\n2.产品收益金额\r\n3.团队业绩达到目标奖励的金额\r\n4.邀请用户数量达到指定目标奖励的金额\r\n5.购买产品\r\n6.提现',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `key`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户余额明细表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_account
-- ----------------------------

-- ----------------------------
-- Table structure for user_account_bonus
-- ----------------------------
DROP TABLE IF EXISTS `user_account_bonus`;
CREATE TABLE `user_account_bonus`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `old_balance` decimal(11, 2) NOT NULL COMMENT '变化前总余额',
  `profit` decimal(11, 2) NOT NULL COMMENT '收入/支出',
  `total_balance` decimal(11, 2) NOT NULL COMMENT '更新后总额',
  `type` tinyint(1) NOT NULL COMMENT '1.邀请奖励\r\n2.注册赠送金额3：小红旗兑换',
  `to_user_id` int(11) NOT NULL COMMENT '被邀请人id',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '更新时间',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `key`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户奖金表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_account_bonus
-- ----------------------------
INSERT INTO `user_account_bonus` VALUES (9, 1, 0.00, 6000.00, 6000.00, 2, 0, '2023-06-24 19:00:00', '2023-06-24 19:00:00', '注册成功赠送奖励金');
INSERT INTO `user_account_bonus` VALUES (10, 1, 6000.00, 50.00, 6050.00, 1, 127, '2023-06-24 19:00:28', '2023-06-24 19:00:28', '邀请新用户奖励');
INSERT INTO `user_account_bonus` VALUES (11, 127, 0.00, 6000.00, 6000.00, 2, 0, '2023-06-24 19:00:28', '2023-06-24 19:00:28', '注册成功赠送奖励金');
INSERT INTO `user_account_bonus` VALUES (12, 1, 6050.00, 50.00, 6100.00, 1, 128, '2023-06-24 19:00:37', '2023-06-24 19:00:37', '邀请新用户奖励');
INSERT INTO `user_account_bonus` VALUES (13, 128, 0.00, 6000.00, 6000.00, 2, 0, '2023-06-24 19:00:37', '2023-06-24 19:00:37', '注册成功赠送奖励金');
INSERT INTO `user_account_bonus` VALUES (14, 1, 6100.00, 50.00, 6150.00, 1, 136, '2023-06-24 19:17:00', '2023-06-24 19:17:00', '邀请新用户奖励');
INSERT INTO `user_account_bonus` VALUES (15, 136, 0.00, 6000.00, 6000.00, 2, 0, '2023-06-24 19:17:00', '2023-06-24 19:17:00', '注册成功赠送奖励金');
INSERT INTO `user_account_bonus` VALUES (16, 1, 6150.00, 50.00, 6200.00, 1, 137, '2023-06-24 19:17:27', '2023-06-24 19:17:27', '邀请新用户奖励');
INSERT INTO `user_account_bonus` VALUES (17, 137, 0.00, 6000.00, 6000.00, 2, 0, '2023-06-24 19:17:27', '2023-06-24 19:17:27', '注册成功赠送奖励金');

-- ----------------------------
-- Table structure for user_bank_cards
-- ----------------------------
DROP TABLE IF EXISTS `user_bank_cards`;
CREATE TABLE `user_bank_cards`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户ID',
  `card_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '银行卡号',
  `cardholder_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '持卡人姓名',
  `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '银行名称',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户银行卡' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_bank_cards
-- ----------------------------

-- ----------------------------
-- Table structure for user_board
-- ----------------------------
DROP TABLE IF EXISTS `user_board`;
CREATE TABLE `user_board`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '留言板' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_board
-- ----------------------------
INSERT INTO `user_board` VALUES (1, 1, '564654649787', '2023-06-24 23:57:57', '2023-06-24 23:57:57');

-- ----------------------------
-- Table structure for user_goods
-- ----------------------------
DROP TABLE IF EXISTS `user_goods`;
CREATE TABLE `user_goods`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `goods_id` int(11) NULL DEFAULT NULL COMMENT '商品id',
  `status` tinyint(1) NOT NULL COMMENT '产品状态 1：正在运行 2:结束运行',
  `type` int(11) NULL DEFAULT NULL COMMENT '1:养老 2:医疗 3：教育',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '产品名称',
  `amount` decimal(11, 2) NULL DEFAULT NULL COMMENT '金额',
  `income` decimal(11, 2) NULL DEFAULT NULL COMMENT '日收益',
  `validity_day` int(10) NOT NULL COMMENT '有效期 天',
  `introduce` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '持续6个月 共180天一天60 一共23800元 商品介绍',
  `end_rewards` decimal(11, 2) NULL DEFAULT NULL COMMENT '最后一天的奖励',
  `start_date` datetime(0) NULL DEFAULT NULL COMMENT '开始时间',
  `end_date` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户产品' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_goods
-- ----------------------------

-- ----------------------------
-- Table structure for user_goods_log
-- ----------------------------
DROP TABLE IF EXISTS `user_goods_log`;
CREATE TABLE `user_goods_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `user_goods_id` int(11) NULL DEFAULT NULL COMMENT '用户产品id',
  `income` decimal(10, 2) NULL DEFAULT NULL COMMENT '收益金额',
  `date` datetime(0) NULL DEFAULT NULL COMMENT '产生收益时间',
  `created_at` datetime(0) NOT NULL,
  `updated_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户产品收益明细' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of user_goods_log
-- ----------------------------

-- ----------------------------
-- Table structure for user_info
-- ----------------------------
DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info`  (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '姓名',
  `date` datetime(0) NULL DEFAULT NULL COMMENT '加入中国梦时间',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '电话',
  `address` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '地址',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户个人信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_info
-- ----------------------------
INSERT INTO `user_info` VALUES (1, '张三', '2023-06-24 22:17:29', '1310000000', '谁都爱时间都', '2023-06-25 00:05:14', '2023-06-25 00:05:14');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `p_id` int(11) NOT NULL DEFAULT 0 COMMENT '上级用户id',
  `avatar` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '头像地址',
  `is_withdrawal` tinyint(1) NOT NULL DEFAULT 2 COMMENT '2:未禁止提现 1:禁止提现',
  `is_locked` tinyint(1) NOT NULL DEFAULT 2 COMMENT '2:未锁定 1:已锁定',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '手机号',
  `password` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '登陆密码',
  `pay_password` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '交易密码',
  `last_login_date` datetime(0) NOT NULL COMMENT '最后一次登录时间',
  `last_login_ip` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '最后一次登录IP',
  `lock_date` datetime(0) NULL DEFAULT NULL COMMENT '锁定时间',
  `register_date` datetime(0) NOT NULL COMMENT '注册时间',
  `balance` decimal(11, 2) NULL COMMENT '账户余额 （更新用户资产记录的时候更新该字段）',
  `bonus` decimal(11, 2) NULL COMMENT '用户奖金',
  `frozen_balance` decimal(11, 2) NULL COMMENT '冻结金额 （更新用户资产记录的时候更新该字段）',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL COMMENT '更新时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户token',
  `invitation` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请码',
  `sign_sum` int(11) NULL DEFAULT 0 COMMENT '签到红旗数量',
  `is_real_name` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否实名 0：未 1：是',
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '实名名称',
  `real_card` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '身份证号',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `only`(`phone`) USING BTREE COMMENT ' 手机号 抖音号  平台编号唯一',
  UNIQUE INDEX `invitation`(`invitation`) USING BTREE,
  INDEX `key`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 118 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'km', 0, 'baidu.com', 2, 2, '13100000000', '$2y$10$ylx/5NiUfdnzPA0wP5WK7eeeJkOYvx77QOOehwNfQPUEQHdGoLN86', '$2y$10$UrhI9vqq7a4YsaeCr/8jeuunI.eXvBV1ChEF79r6DyIEBnUnG5/De', '2023-06-24 23:53:24', '127.0.0.1', NULL, '2023-06-24 19:00:00', 0.00, 6200.00, 0.00, '2023-06-24 19:00:00', '2023-06-24 23:53:46', NULL, NULL, 'AVaWBU', 6, 1, '王麻子', '564645789798789');
INSERT INTO `users` VALUES (127, '13100000001', 1, 'img/head.png', 2, 2, '13100000001', '$2y$10$Ulu9CsLAMTaFe/x8e674ReDuKm5GDt7SyOCXU6dPXdpR62Ki1/S2K', '$2y$10$GWSL6IBTTwaux3rMHKdlq.8/QOWE4vb1QBQIgv4uagT7D6z0DYLP6', '2023-06-24 19:00:28', '127.0.0.1', NULL, '2023-06-24 19:00:28', 0.00, 6000.00, 0.00, '2023-06-24 19:00:28', '2023-06-24 19:00:28', NULL, NULL, 'Qgom0r', 0, 0, NULL, NULL);
INSERT INTO `users` VALUES (128, '13100000002', 1, 'img/head.png', 2, 2, '13100000002', '$2y$10$uKAkF9KVrdRgGQ1VI5HhueUxHMsdHWBZslZHl5/3n5tY/EGoT1IKq', '$2y$10$6pSmNkjEGq6mO9XfkI3IOulnNv7Lu2Y4iXi8h1DnsnJhAo4h6cWyG', '2023-06-24 19:00:37', '127.0.0.1', NULL, '2023-06-24 19:00:37', 0.00, 6000.00, 0.00, '2023-06-24 19:00:37', '2023-06-24 19:00:37', NULL, NULL, 'AJaMR4', 0, 0, NULL, NULL);
INSERT INTO `users` VALUES (136, '13100000004', 1, 'img/head.png', 2, 2, '13100000004', '$2y$10$z5F69SV0x8IDJR3PL3LD6O8SxDIkoPxIdXQ8KZPVSDGw/7R.BF02G', '$2y$10$8HNpceMOsrWBy.M.tlPOdua6FEwbFIQJ6K9L7LSIP2meUz8zZuivW', '2023-06-24 19:17:00', '127.0.0.1', NULL, '2023-06-24 19:17:00', 0.00, 6000.00, 0.00, '2023-06-24 19:17:00', '2023-06-24 19:17:00', NULL, NULL, '68mAj3', 0, 0, NULL, NULL);
INSERT INTO `users` VALUES (137, '13100000005', 1, 'img/head.png', 2, 2, '13100000005', '$2y$10$pjRcCP2HoB1JPDXOLDFsd.6QbimlIVPSD8RyBM0wr8h1TR8t9fo52', '$2y$10$3RSCxbKD2oYYx3BITfZN.OjMxTxvSFMtrpbLJljOdn97VjSMfCsTS', '2023-06-24 19:17:27', '127.0.0.1', NULL, '2023-06-24 19:17:27', 0.00, 6000.00, 0.00, '2023-06-24 19:17:27', '2023-06-24 19:17:27', NULL, NULL, 'gvgSKn', 0, 0, NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
