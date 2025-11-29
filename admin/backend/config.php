<?php
/**
 * Providence Admin Backend - 配置文件
 * 
 * 使用说明：
 * 1. 复制此文件为 config.local.php
 * 2. 修改数据库连接信息
 * 3. 生产环境务必修改 JWT_SECRET
 */

// 防止直接访问
if (!defined('ADMIN_API')) {
    die('Access Denied');
}

// ============ 数据库配置 ============
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'providence');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ============ 安全配置 ============
// JWT 密钥 - 生产环境必须修改！
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'providence_admin_secret_key_2024_change_in_production');
// Token 有效期（秒）
define('TOKEN_EXPIRE', 86400 * 7); // 7天

// ============ 上传配置 ============
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', '/admin/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// ============ 分页配置 ============
define('PAGE_SIZE', 20);

// ============ 调试模式 ============
define('DEBUG_MODE', getenv('DEBUG_MODE') ?: false);

// ============ 跨域配置 ============
define('CORS_ORIGIN', '*');

// ============ 时区 ============
date_default_timezone_set('Asia/Shanghai');

// ============ 错误处理 ============
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
