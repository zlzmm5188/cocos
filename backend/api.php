<?php
/**
 * Providence 前台用户API - 完整版
 * 
 * 统一入口文件，处理所有前台用户端API请求
 * 
 * API路径: /backend/api.php 或 /api/
 * 
 * 版本: 1.0.0
 * 日期: 2025-11-29
 */

// 启用错误报告（开发模式）
error_reporting(E_ALL);
ini_set('display_errors', 0); // 生产环境关闭
ini_set('log_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 加载配置和数据库
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Token, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// 解析请求路径
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);

// 获取相对路径
$path = parse_url($requestUri, PHP_URL_PATH);

// 移除各种前缀
$prefixes = ['/backend/api.php', '/api.php', '/api', $basePath];
foreach ($prefixes as $prefix) {
    if (strpos($path, $prefix) === 0) {
        $path = substr($path, strlen($prefix));
    }
}

// 确保路径以 / 开头
if (empty($path) || $path[0] !== '/') {
    $path = '/' . $path;
}

$method = $_SERVER['REQUEST_METHOD'];

// 调试日志
if (defined('DEBUG') && DEBUG) {
    error_log("[API] $method $path (原始: $requestUri)");
}

// 初始化数据库连接
try {
    $db = new Database();
} catch (Exception $e) {
    // 数据库连接失败时使用Mock模式
    $db = null;
    error_log("[API] 数据库连接失败，使用Mock模式: " . $e->getMessage());
}

// 路由处理
try {
    // 加载控制器
    $controllers = [
        'AuthController' => __DIR__ . '/controllers/AuthController.php',
        'UserController' => __DIR__ . '/controllers/UserController.php',
        'ProjectController' => __DIR__ . '/controllers/ProjectController.php',
        'OrderController' => __DIR__ . '/controllers/OrderController.php',
        'FinanceController' => __DIR__ . '/controllers/FinanceController.php',
        'TeamController' => __DIR__ . '/controllers/TeamController.php',
        'RibaoController' => __DIR__ . '/controllers/RibaoController.php',
        'PointsController' => __DIR__ . '/controllers/PointsController.php',
        'KycController' => __DIR__ . '/controllers/KycController.php',
        'SystemController' => __DIR__ . '/controllers/SystemController.php',
    ];

    foreach ($controllers as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }

    // 路由表
    $routes = [
        // ============ 认证 ============
        'POST /auth/login' => ['AuthController', 'login'],
        'POST /auth/register' => ['AuthController', 'register'],
        'POST /auth/logout' => ['AuthController', 'logout'],
        'POST /auth/refresh' => ['AuthController', 'refresh'],
        'POST /auth/send-sms' => ['AuthController', 'sendSms'],
        'POST /auth/verify-sms' => ['AuthController', 'verifySms'],
        'POST /user/forgot-password' => ['AuthController', 'forgotPassword'],
        'POST /user/reset-password' => ['AuthController', 'resetPassword'],
        'POST /user/change-password' => ['AuthController', 'changePassword'],
        'POST /login/sms/forget' => ['AuthController', 'forgotPassword'],
        'POST /login/sms/forget/account' => ['AuthController', 'forgotPasswordByAccount'],
        
        // ============ 用户信息 ============
        'GET /user/info' => ['UserController', 'getInfo'],
        'GET /api/user/info' => ['UserController', 'getInfo'],
        'GET /user/index' => ['UserController', 'getInfo'],
        'GET /api/user/index' => ['UserController', 'getInfo'],
        'POST /user/update' => ['UserController', 'update'],
        'GET /user/vip-progress' => ['UserController', 'getVipProgress'],
        'GET /api/user/vip-progress' => ['UserController', 'getVipProgress'],
        'GET /user/invite' => ['UserController', 'getInviteInfo'],
        
        // ============ 银行卡/USDT地址 ============
        'GET /user/bank/list' => ['UserController', 'getBankList'],
        'GET /pay/bank/list' => ['UserController', 'getBankList'],
        'POST /user/bind-bank-card' => ['UserController', 'bindBankCard'],
        'POST /pay/bank/add' => ['UserController', 'bindBankCard'],
        'GET /user/usdt-address/list' => ['UserController', 'getUsdtAddressList'],
        'POST /user/usdt-address/bind' => ['UserController', 'bindUsdtAddress'],
        'POST /user/bind-usdt-address' => ['UserController', 'bindUsdtAddress'],
        'GET /pay/us/info' => ['UserController', 'getUsdtInfo'],
        
        // ============ 项目 ============
        'GET /project/index' => ['ProjectController', 'getList'],
        'GET /api/project/index' => ['ProjectController', 'getList'],
        'GET /api/project/list' => ['ProjectController', 'getList'],
        'GET /project/detail' => ['ProjectController', 'getDetail'],
        'GET /api/project/detail' => ['ProjectController', 'getDetail'],
        'GET /projects' => ['ProjectController', 'getList'],
        'GET /projects/categories' => ['ProjectController', 'getCategories'],
        
        // ============ 投资订单 ============
        'GET /invest/orders' => ['OrderController', 'getList'],
        'GET /orders/my-investments' => ['OrderController', 'getMyInvestments'],
        'GET /orders/earnings' => ['OrderController', 'getEarnings'],
        'POST /invest/orders' => ['OrderController', 'create'],
        'POST /invest/create' => ['OrderController', 'create'],
        
        // ============ 充值提现 ============
        'GET /api/finance/api/recharge/list' => ['FinanceController', 'getRechargeList'],
        'GET /api/finance/recharge' => ['FinanceController', 'getRechargeList'],
        'POST /api/recharge/add' => ['FinanceController', 'createRecharge'],
        'POST /finance/recharge' => ['FinanceController', 'createRecharge'],
        'GET /api/finance/api/withdraw/list' => ['FinanceController', 'getWithdrawList'],
        'GET /api/finance/withdraw' => ['FinanceController', 'getWithdrawList'],
        'POST /api/withdraw/create' => ['FinanceController', 'createWithdraw'],
        'POST /finance/withdraw' => ['FinanceController', 'createWithdraw'],
        'GET /api/finance/wallet-logs' => ['FinanceController', 'getWalletLogs'],
        'GET /get_usdt_rate' => ['FinanceController', 'getUsdtRate'],
        
        // ============ 团队 ============
        'GET /team/info' => ['TeamController', 'getInfo'],
        'GET /team/members' => ['TeamController', 'getMembers'],
        'GET /team/rewards' => ['TeamController', 'getRewards'],
        'GET /team/referral-rewards' => ['TeamController', 'getReferralRewards'],
        'GET /team/reward-info' => ['TeamController', 'getRewardInfo'],
        'GET /team/reward-summary' => ['TeamController', 'getRewardSummary'],
        'POST /user/team/claim_reward' => ['TeamController', 'claimReward'],
        'GET /user/team/rewards_status' => ['TeamController', 'getRewardsStatus'],
        
        // ============ 日利宝 ============
        'GET /user/ribao/head' => ['RibaoController', 'getHead'],
        'GET /user/ribao/list' => ['RibaoController', 'getList'],
        'GET /api/ribao/info' => ['RibaoController', 'getInfo'],
        'GET /user/api/ribao/info' => ['RibaoController', 'getInfo'],
        'POST /user/ribao/in' => ['RibaoController', 'transferIn'],
        'POST /user/api/ribao/transfer-in' => ['RibaoController', 'transferIn'],
        'POST /user/ribao/out' => ['RibaoController', 'transferOut'],
        'POST /user/api/ribao/transfer-out' => ['RibaoController', 'transferOut'],
        'GET /user/api/ribao/records' => ['RibaoController', 'getRecords'],
        
        // ============ 积分 ============
        'GET /api/points/balance' => ['PointsController', 'getBalance'],
        'GET /api/points/logs' => ['PointsController', 'getLogs'],
        'POST /api/points/exchange' => ['PointsController', 'exchange'],
        
        // ============ 签到 ============
        'GET /api/user/sign/info' => ['UserController', 'getSignInfo'],
        'POST /api/user/sign/sign' => ['UserController', 'doSign'],
        
        // ============ KYC实名认证 ============
        'GET /user/kyc/status' => ['KycController', 'getStatus'],
        'POST /user/kyc/submit' => ['KycController', 'submit'],
        'POST /user/kyc-submit-simple' => ['KycController', 'submitSimple'],
        
        // ============ 体验金 ============
        'GET /trial-fund/status' => ['OrderController', 'getTrialFundStatus'],
        'POST /trial-fund/claim' => ['OrderController', 'claimTrialFund'],
        'GET /trial-fund/orders' => ['OrderController', 'getTrialFundOrders'],
        
        // ============ 系统 ============
        'GET /announcements' => ['SystemController', 'getAnnouncements'],
        'GET /activities/popup' => ['SystemController', 'getActivityPopup'],
        'GET /user/profit/calendar' => ['UserController', 'getProfitCalendar'],
    ];

    // 匹配路由
    $routeKey = "$method $path";
    $handler = null;
    
    // 精确匹配
    if (isset($routes[$routeKey])) {
        $handler = $routes[$routeKey];
    } else {
        // 模糊匹配（去掉查询参数）
        $pathWithoutQuery = strtok($path, '?');
        $routeKey = "$method $pathWithoutQuery";
        if (isset($routes[$routeKey])) {
            $handler = $routes[$routeKey];
        }
    }

    if ($handler) {
        [$controller, $action] = $handler;
        
        // 检查控制器类是否存在
        if (!class_exists($controller)) {
            throw new Exception("控制器 $controller 不存在");
        }
        
        $instance = new $controller($db);
        
        // 检查方法是否存在
        if (!method_exists($instance, $action)) {
            throw new Exception("方法 $controller::$action 不存在");
        }
        
        // 调用控制器方法
        $instance->$action();
    } else {
        // 404 路由不存在
        http_response_code(404);
        echo json_encode([
            'code' => -1,
            'msg' => "接口不存在: $path",
            'method' => $method,
            'available_routes' => array_keys($routes)
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    error_log("[API Error] " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'error' => DEBUG ? $e->getMessage() : '请稍后重试'
    ], JSON_UNESCAPED_UNICODE);
}
