<?php
/**
 * Providence 前台API - 配置文件
 */

// 调试模式
define('DEBUG', true);

// 数据库配置
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'providence');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// JWT配置
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'providence_jwt_secret_key_2025');
define('JWT_EXPIRE', 86400 * 7); // 7天过期

// 应用配置
define('APP_NAME', 'Providence');
define('APP_VERSION', '1.0.0');

// 分页配置
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// 上传配置
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB

// VIP等级配置（默认值）
$VIP_CONFIG = [
    0 => ['name' => 'VIP0', 'level1_rate' => 0.01, 'level2_rate' => 0.00, 'min_invest' => 0, 'extra_rate' => 0, 'checkin_points' => 6],
    1 => ['name' => 'VIP1', 'level1_rate' => 0.02, 'level2_rate' => 0.01, 'min_invest' => 30000, 'extra_rate' => 0.0005, 'checkin_points' => 10],
    2 => ['name' => 'VIP2', 'level1_rate' => 0.03, 'level2_rate' => 0.02, 'min_invest' => 100000, 'extra_rate' => 0.001, 'checkin_points' => 16],
    3 => ['name' => 'VIP3', 'level1_rate' => 0.04, 'level2_rate' => 0.02, 'min_invest' => 250000, 'extra_rate' => 0.0012, 'checkin_points' => 20],
    4 => ['name' => 'VIP4', 'level1_rate' => 0.05, 'level2_rate' => 0.03, 'min_invest' => 800000, 'extra_rate' => 0.0015, 'checkin_points' => 30],
    5 => ['name' => 'VIP5', 'level1_rate' => 0.05, 'level2_rate' => 0.04, 'min_invest' => 1500000, 'extra_rate' => 0.0016, 'checkin_points' => 40],
    6 => ['name' => 'VIP6', 'level1_rate' => 0.06, 'level2_rate' => 0.04, 'min_invest' => 3800000, 'extra_rate' => 0.0018, 'checkin_points' => 50],
    7 => ['name' => 'VIP7', 'level1_rate' => 0.06, 'level2_rate' => 0.05, 'min_invest' => 8000000, 'extra_rate' => 0.0023, 'checkin_points' => 60],
    8 => ['name' => 'VIP8', 'level1_rate' => 0.07, 'level2_rate' => 0.05, 'min_invest' => 13000000, 'extra_rate' => 0.0025, 'checkin_points' => 70],
];

// 团队管理奖配置
$TEAM_AWARDS = [
    ['min_members' => 3, 'min_invest' => 80000, 'points' => 2000],
    ['min_members' => 5, 'min_invest' => 150000, 'points' => 3900],
    ['min_members' => 10, 'min_invest' => 500000, 'points' => 12000],
    ['min_members' => 20, 'min_invest' => 1500000, 'points' => 35000],
    ['min_members' => 50, 'min_invest' => 3800000, 'points' => 50000],
    ['min_members' => 100, 'min_invest' => 8800000, 'points' => 75000],
    ['min_members' => 200, 'min_invest' => 15000000, 'points' => 150000],
    ['min_members' => 500, 'min_invest' => 58000000, 'points' => 200000],
    ['min_members' => 1000, 'min_invest' => 98000000, 'points' => 380000],
];

// 币种配置
define('CNY_TO_USDT_RATE', 7.2); // 人民币兑换USDT汇率
define('POINTS_TO_CNY_RATE', 0.5); // 积分兑换人民币比例 (0.5积分=1元，即2积分=1元)
