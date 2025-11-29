-- ============================================
-- PROVIDENCE 后台管理系统 - 扩展数据库设计
-- 多币种支持 + 二级返利系统
-- ============================================

-- 1. 扩展用户表 - 添加多币种余额字段
ALTER TABLE `users` 
ADD COLUMN `usdt_balance` DECIMAL(15,4) DEFAULT 0.0000 COMMENT 'USDT余额' AFTER `balance`,
ADD COLUMN `ribao_cny_balance` DECIMAL(15,2) DEFAULT 0.00 COMMENT '日利宝CNY余额' AFTER `frozen_balance`,
ADD COLUMN `ribao_usdt_balance` DECIMAL(15,4) DEFAULT 0.0000 COMMENT '日利宝USDT余额' AFTER `ribao_cny_balance`,
ADD COLUMN `total_invest` DECIMAL(15,2) DEFAULT 0.00 COMMENT '累计投资（用于VIP升级）' AFTER `points`,
ADD COLUMN `total_usdt_invest` DECIMAL(15,4) DEFAULT 0.0000 COMMENT '累计USDT投资' AFTER `total_invest`,
ADD COLUMN `team_award_level` TINYINT DEFAULT 0 COMMENT '已领取的团队奖等级' AFTER `parent_id`;

-- 2. 扩展余额流水表 - 添加币种字段
ALTER TABLE `balance_logs`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种:CNY/USDT/POINTS' AFTER `type`;

-- 3. 扩展投资订单表 - 添加币种和额外加息
ALTER TABLE `user_investments`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `invest_amount`,
ADD COLUMN `extra_rate` DECIMAL(5,4) DEFAULT 0.0000 COMMENT 'VIP额外加息' AFTER `daily_rate`;

-- 4. 扩展项目表 - 添加币种支持
ALTER TABLE `projects`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '支持的币种:CNY/USDT/BOTH' AFTER `max_invest`;

-- 5. 扩展充值表 - 添加币种
ALTER TABLE `recharge_records`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `amount`;

-- 6. 扩展提现表 - 添加币种
ALTER TABLE `withdraw_records`
ADD COLUMN `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种' AFTER `amount`;

-- 7. 返佣记录表
CREATE TABLE IF NOT EXISTS `commission_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '获得返佣的用户ID',
  `from_user_id` INT UNSIGNED NOT NULL COMMENT '来源用户ID（购买者）',
  `project_id` INT UNSIGNED COMMENT '项目ID',
  `investment_id` INT UNSIGNED COMMENT '投资订单ID',
  `level` TINYINT NOT NULL COMMENT '返佣级别:1一级 2二级',
  `invest_amount` DECIMAL(15,4) NOT NULL COMMENT '投资金额',
  `rate` DECIMAL(5,4) NOT NULL COMMENT '返佣比例',
  `amount` DECIMAL(15,4) NOT NULL COMMENT '返佣金额',
  `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种',
  `status` TINYINT DEFAULT 0 COMMENT '状态:0待发放 1已发放 2已取消',
  `paid_at` DATETIME COMMENT '发放时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_from_user` (`from_user_id`),
  INDEX `idx_investment` (`investment_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='返佣记录表';

-- 8. 币种兑换记录表
CREATE TABLE IF NOT EXISTS `currency_exchange_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `type` VARCHAR(20) NOT NULL COMMENT '类型:cny_to_usdt/points_to_cny',
  `from_amount` DECIMAL(15,4) NOT NULL COMMENT '兑换前金额',
  `to_amount` DECIMAL(15,4) NOT NULL COMMENT '兑换后金额',
  `rate` DECIMAL(10,6) NOT NULL COMMENT '兑换比例',
  `fee` DECIMAL(15,4) DEFAULT 0 COMMENT '手续费',
  `status` TINYINT DEFAULT 0 COMMENT '状态:0待审核 1已完成 2已拒绝',
  `remark` VARCHAR(500) COMMENT '备注',
  `reviewed_by` INT UNSIGNED COMMENT '审核人ID',
  `reviewed_at` DATETIME COMMENT '审核时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='币种兑换记录表';

-- 9. 日利宝转入转出记录表
CREATE TABLE IF NOT EXISTS `ribao_transfer_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种',
  `type` VARCHAR(10) NOT NULL COMMENT '类型:in/out',
  `amount` DECIMAL(15,4) NOT NULL COMMENT '金额',
  `balance_before` DECIMAL(15,4) NOT NULL COMMENT '转账前日利宝余额',
  `balance_after` DECIMAL(15,4) NOT NULL COMMENT '转账后日利宝余额',
  `status` TINYINT DEFAULT 1 COMMENT '状态:1成功 2失败',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_currency` (`currency`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日利宝转入转出记录';

-- 10. 日利宝收益记录表
CREATE TABLE IF NOT EXISTS `ribao_earnings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `currency` VARCHAR(10) DEFAULT 'CNY' COMMENT '币种',
  `balance` DECIMAL(15,4) NOT NULL COMMENT '计息本金',
  `rate` DECIMAL(8,6) NOT NULL COMMENT '日利率',
  `earning` DECIMAL(15,4) NOT NULL COMMENT '收益金额',
  `earn_date` DATE NOT NULL COMMENT '收益日期',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_date` (`earn_date`),
  UNIQUE KEY `uk_user_date_currency` (`user_id`, `earn_date`, `currency`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日利宝收益记录';

-- 11. 签到记录表
CREATE TABLE IF NOT EXISTS `checkin_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `vip_level` TINYINT DEFAULT 0 COMMENT '签到时的VIP等级',
  `points` INT NOT NULL COMMENT '获得积分',
  `checkin_date` DATE NOT NULL COMMENT '签到日期',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_date` (`user_id`, `checkin_date`),
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到记录表';

-- 12. 团队奖领取记录表
CREATE TABLE IF NOT EXISTS `team_award_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `award_level` TINYINT NOT NULL COMMENT '奖励等级',
  `member_count` INT NOT NULL COMMENT '达成时成员数',
  `total_invest` DECIMAL(15,2) NOT NULL COMMENT '达成时累计投资',
  `points` INT NOT NULL COMMENT '获得积分',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='团队奖领取记录';

-- ============================================
-- 插入默认VIP配置
-- ============================================

-- 清空并重新插入VIP等级（如果表存在）
DELETE FROM `vip_levels` WHERE 1=1;

INSERT INTO `vip_levels` (`id`, `name`, `min_invest`, `daily_withdraw_limit`, `withdraw_fee_rate`, `invite_reward_rate`, `privileges`) VALUES
(0, 'VIP0', 0, 5000, 0.01, 0.01, '{"checkin_points": 6, "level1_rate": 1, "level2_rate": 0, "extra_rate": 0}'),
(1, 'VIP1', 30000, 10000, 0.008, 0.02, '{"checkin_points": 10, "level1_rate": 2, "level2_rate": 1, "extra_rate": 0.0005}'),
(2, 'VIP2', 100000, 30000, 0.006, 0.03, '{"checkin_points": 16, "level1_rate": 3, "level2_rate": 2, "extra_rate": 0.001}'),
(3, 'VIP3', 250000, 50000, 0.005, 0.04, '{"checkin_points": 20, "level1_rate": 4, "level2_rate": 2, "extra_rate": 0.0012}'),
(4, 'VIP4', 800000, 100000, 0.004, 0.05, '{"checkin_points": 30, "level1_rate": 5, "level2_rate": 3, "extra_rate": 0.0015}'),
(5, 'VIP5', 1500000, 200000, 0.003, 0.05, '{"checkin_points": 40, "level1_rate": 5, "level2_rate": 4, "extra_rate": 0.0016}'),
(6, 'VIP6', 3800000, 500000, 0.002, 0.06, '{"checkin_points": 50, "level1_rate": 6, "level2_rate": 4, "extra_rate": 0.0018}'),
(7, 'VIP7', 8000000, 1000000, 0.001, 0.06, '{"checkin_points": 60, "level1_rate": 6, "level2_rate": 5, "extra_rate": 0.0023}'),
(8, 'VIP8', 13000000, 9999999, 0, 0.07, '{"checkin_points": 70, "level1_rate": 7, "level2_rate": 5, "extra_rate": 0.0025}');

-- 插入币种配置
INSERT INTO `system_config` (`key`, `value`, `description`, `group`) VALUES
('cny_to_usdt_enabled', '1', 'CNY兑换USDT开关', 'currency'),
('cny_to_usdt_rate', '7.2', 'CNY兑换USDT汇率', 'currency'),
('cny_to_usdt_min', '100', 'CNY兑换最小金额', 'currency'),
('cny_to_usdt_max', '100000', 'CNY兑换最大金额', 'currency'),
('cny_to_usdt_fee_rate', '0.002', 'CNY兑换手续费率', 'currency'),
('points_to_cny_enabled', '1', '积分兑换CNY开关', 'currency'),
('points_to_cny_rate', '0.5', '积分兑换CNY比例(0.5积分=1元)', 'currency'),
('points_to_cny_min', '100', '积分兑换最小值', 'currency'),
('usdt_daily_rate', '0.002', 'USDT日利宝日利率', 'currency'),
('cny_daily_rate', '0.001', 'CNY日利宝日利率', 'currency')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 插入VIP完整配置
INSERT INTO `system_config` (`key`, `value`, `description`, `group`) VALUES
('vip_full_config', '{
  "invite_rewards": {
    "0": {"level1": 1, "level2": 0},
    "1": {"level1": 2, "level2": 1},
    "2": {"level1": 3, "level2": 2},
    "3": {"level1": 4, "level2": 2},
    "4": {"level1": 5, "level2": 3},
    "5": {"level1": 5, "level2": 4},
    "6": {"level1": 6, "level2": 4},
    "7": {"level1": 6, "level2": 5},
    "8": {"level1": 7, "level2": 5}
  },
  "upgrade_rules": {
    "1": {"min_invest": 30000, "extra_rate": 0.0005},
    "2": {"min_invest": 100000, "extra_rate": 0.001},
    "3": {"min_invest": 250000, "extra_rate": 0.0012},
    "4": {"min_invest": 800000, "extra_rate": 0.0015},
    "5": {"min_invest": 1500000, "extra_rate": 0.0016},
    "6": {"min_invest": 3800000, "extra_rate": 0.0018},
    "7": {"min_invest": 8000000, "extra_rate": 0.0023},
    "8": {"min_invest": 13000000, "extra_rate": 0.0025}
  },
  "checkin_points": {
    "0": 6, "1": 10, "2": 16, "3": 20, "4": 30,
    "5": 40, "6": 50, "7": 60, "8": 70
  },
  "team_awards": [
    {"members": 3, "min_invest": 80000, "points": 2000},
    {"members": 5, "min_invest": 150000, "points": 3900},
    {"members": 10, "min_invest": 500000, "points": 12000},
    {"members": 20, "min_invest": 1500000, "points": 35000},
    {"members": 50, "min_invest": 3800000, "points": 50000},
    {"members": 100, "min_invest": 8800000, "points": 75000},
    {"members": 200, "min_invest": 15000000, "points": 150000},
    {"members": 500, "min_invest": 58000000, "points": 200000},
    {"members": 1000, "min_invest": 98000000, "points": 380000}
  ]
}', 'VIP完整配置', 'vip')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
