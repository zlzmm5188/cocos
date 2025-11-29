<?php
/**
 * Providence Admin Backend - API 入口
 * 
 * 所有管理后台API请求的统一入口
 */

define('ADMIN_API', true);

// 加载配置和核心文件
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Token');
header('Access-Control-Max-Age: 86400');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 获取请求路径
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// 移除脚本目录前缀和查询参数
$path = parse_url($requestUri, PHP_URL_PATH);
if (strpos($path, $scriptName) === 0) {
    $path = substr($path, strlen($scriptName));
}
$path = '/' . trim($path, '/');

// 移除 /api.php 或 /index.php 前缀
$path = preg_replace('/^\/(api|index)\.php/', '', $path);

$method = $_SERVER['REQUEST_METHOD'];

// 调试日志
if (DEBUG_MODE) {
    error_log("Admin API: $method $path");
}

// ============ 路由定义 ============
try {
    // 加载控制器
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/DashboardController.php';
    require_once __DIR__ . '/controllers/UserController.php';
    require_once __DIR__ . '/controllers/RechargeController.php';
    require_once __DIR__ . '/controllers/WithdrawController.php';
    require_once __DIR__ . '/controllers/KycController.php';
    require_once __DIR__ . '/controllers/ProductController.php';
    require_once __DIR__ . '/controllers/OrderController.php';
    require_once __DIR__ . '/controllers/TeamController.php';
    require_once __DIR__ . '/controllers/ConfigController.php';
    require_once __DIR__ . '/controllers/LogController.php';
    
    // 路由分发
    switch (true) {
        // ============ 认证 ============
        case $path === '/admin/login' && $method === 'POST':
            AuthController::login();
            break;
            
        case $path === '/admin/logout' && $method === 'POST':
            AuthController::logout();
            break;
            
        case $path === '/admin/info' && $method === 'GET':
            AuthController::info();
            break;
            
        case $path === '/admin/change-password' && $method === 'POST':
            AuthController::changePassword();
            break;
            
        // ============ 仪表盘 ============
        case $path === '/admin/stats' && $method === 'GET':
            DashboardController::stats();
            break;
            
        case $path === '/admin/chart/invest-trend' && $method === 'GET':
            DashboardController::investTrend();
            break;
            
        case $path === '/admin/chart/user-distribution' && $method === 'GET':
            DashboardController::userDistribution();
            break;
            
        case $path === '/admin/recent-orders' && $method === 'GET':
            DashboardController::recentOrders();
            break;
            
        case $path === '/admin/recent-activities' && $method === 'GET':
            DashboardController::recentActivities();
            break;
            
        // ============ 用户管理 ============
        case $path === '/admin/users' && $method === 'GET':
            UserController::list();
            break;
            
        case preg_match('/^\/admin\/users\/(\d+)$/', $path, $matches) && $method === 'GET':
            UserController::detail($matches[1]);
            break;
            
        case preg_match('/^\/admin\/users\/(\d+)$/', $path, $matches) && $method === 'PUT':
            UserController::update($matches[1]);
            break;
            
        case $path === '/admin/users/adjust-balance' && $method === 'POST':
            UserController::adjustBalance();
            break;
            
        case $path === '/admin/users/set-vip' && $method === 'POST':
            UserController::setVip();
            break;
            
        case $path === '/admin/users/reset-password' && $method === 'POST':
            UserController::resetPassword();
            break;
            
        case preg_match('/^\/admin\/users\/(\d+)\/toggle-status$/', $path, $matches) && $method === 'POST':
            UserController::toggleStatus($matches[1]);
            break;
            
        // ============ 充值审核 ============
        case $path === '/admin/recharges' && $method === 'GET':
            RechargeController::list();
            break;
            
        case preg_match('/^\/admin\/recharges\/(\d+)$/', $path, $matches) && $method === 'GET':
            RechargeController::detail($matches[1]);
            break;
            
        case $path === '/admin/recharge-approve' && $method === 'POST':
            RechargeController::approve();
            break;
            
        case $path === '/admin/recharge-reject' && $method === 'POST':
            RechargeController::reject();
            break;
            
        // ============ 提现审核 ============
        case $path === '/admin/withdrawals' && $method === 'GET':
            WithdrawController::list();
            break;
            
        case preg_match('/^\/admin\/withdrawals\/(\d+)$/', $path, $matches) && $method === 'GET':
            WithdrawController::detail($matches[1]);
            break;
            
        case $path === '/admin/withdraw-approve' && $method === 'POST':
            WithdrawController::approve();
            break;
            
        case $path === '/admin/withdraw-reject' && $method === 'POST':
            WithdrawController::reject();
            break;
            
        case $path === '/admin/withdraw-pay' && $method === 'POST':
            WithdrawController::confirmPay();
            break;
            
        // ============ KYC 审核 ============
        case $path === '/admin/kyc-list' && $method === 'GET':
            KycController::list();
            break;
            
        case preg_match('/^\/admin\/kyc\/(\d+)$/', $path, $matches) && $method === 'GET':
            KycController::detail($matches[1]);
            break;
            
        case $path === '/admin/kyc-approve' && $method === 'POST':
            KycController::approve();
            break;
            
        case $path === '/admin/kyc-reject' && $method === 'POST':
            KycController::reject();
            break;
            
        // ============ 产品管理 ============
        case $path === '/admin/products' && $method === 'GET':
            ProductController::list();
            break;
            
        case preg_match('/^\/admin\/products\/(\d+)$/', $path, $matches) && $method === 'GET':
            ProductController::detail($matches[1]);
            break;
            
        case $path === '/admin/project-save' && $method === 'POST':
            ProductController::save();
            break;
            
        case preg_match('/^\/admin\/products\/(\d+)\/toggle-status$/', $path, $matches) && $method === 'POST':
            ProductController::toggleStatus($matches[1]);
            break;
            
        case preg_match('/^\/admin\/products\/(\d+)$/', $path, $matches) && $method === 'DELETE':
            ProductController::delete($matches[1]);
            break;
            
        // ============ 订单管理 ============
        case $path === '/admin/orders' && $method === 'GET':
            OrderController::list();
            break;
            
        case preg_match('/^\/admin\/orders\/(\d+)$/', $path, $matches) && $method === 'GET':
            OrderController::detail($matches[1]);
            break;
            
        case $path === '/admin/orders/stats' && $method === 'GET':
            OrderController::stats();
            break;
            
        // ============ 团队管理 ============
        case $path === '/admin/teams' && $method === 'GET':
            TeamController::list();
            break;
            
        case preg_match('/^\/admin\/teams\/(\d+)$/', $path, $matches) && $method === 'GET':
            TeamController::detail($matches[1]);
            break;
            
        case preg_match('/^\/admin\/teams\/(\d+)\/members$/', $path, $matches) && $method === 'GET':
            TeamController::members($matches[1]);
            break;
            
        // ============ 系统配置 ============
        case $path === '/admin/vip-config' && $method === 'GET':
            ConfigController::vipConfig();
            break;
            
        case $path === '/admin/vip-config' && $method === 'POST':
            ConfigController::saveVipConfig();
            break;
            
        case $path === '/admin/settings' && $method === 'GET':
            ConfigController::settings();
            break;
            
        case $path === '/admin/settings' && $method === 'POST':
            ConfigController::saveSettings();
            break;
            
        // ============ 日志管理 ============
        case $path === '/admin/logs' && $method === 'GET':
            LogController::list();
            break;
            
        case $path === '/admin/transactions' && $method === 'GET':
            LogController::transactions();
            break;
            
        // ============ 日利宝管理 ============
        case $path === '/admin/ribao-users' && $method === 'GET':
            require_once __DIR__ . '/controllers/RibaoController.php';
            RibaoController::users();
            break;
            
        case $path === '/admin/ribao-distribute' && $method === 'POST':
            require_once __DIR__ . '/controllers/RibaoController.php';
            RibaoController::distribute();
            break;
            
        // ============ 默认 ============
        default:
            error("接口不存在: $path", -404, 404);
    }
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        error('服务器错误: ' . $e->getMessage(), -500, 500);
    } else {
        error('服务器内部错误', -500, 500);
    }
}
