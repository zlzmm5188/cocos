<?php
/**
 * Providence 前台 + API 统一入口
 *
 * 简化方案：API 和前台在同一个地址
 * 避免跨域问题，快速测试前台功能
 */

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Token');

// 处理 OPTIONS 请求 (CORS 预检)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// 模拟数据库
$users = [
    'G138688' => [
        'id' => 1,
        'username' => 'G138688',
        'password' => 'G138688',
        'nickname' => 'Test User',
        'balance_cny' => '1000000.00',
        'balance_usdt' => '10000.00',
    ]
];

$projects = [
    [
        'id' => 1,
        'name' => '7日理财',
        'annual_rate' => '12%',
        'period' => 7,
        'min_amount' => 1000,
        'status' => 'active',
        'description' => '7天期限的短期理财项目'
    ],
    [
        'id' => 2,
        'name' => '30日理财',
        'annual_rate' => '14%',
        'period' => 30,
        'min_amount' => 5000,
        'status' => 'active',
        'description' => '30天期限的中期理财项目'
    ]
];

// Token 存储 (使用 session 或内存)
if (!isset($_SESSION)) {
    session_start();
}

// 路由处理
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 移除前缀路径
// 情况1: /api/auth/login (PHP内置服务器的情况)
// 情况2: /api.php/auth/login (如果通过 api.php 的情况)
// 情况3: /auth/login (已处理的情况)

if (strpos($path, '/api/') === 0) {
    // 移除 /api/ 前缀
    $path = preg_replace('/^\/api/', '', $path);
} elseif (preg_match('/api\.php(.*)/', $path, $matches)) {
    // 移除 /api.php 前缀
    $path = $matches[1];
}

$method = $_SERVER['REQUEST_METHOD'];

// 调试日志
error_log("请求: $method $path (原始: " . $_SERVER['REQUEST_URI'] . ")");

// 路由分发
try {
    switch (true) {
        // ============ 认证 ============
        case $path === '/auth/login' && $method === 'POST':
            handleLogin($users);
            break;

        case $path === '/auth/logout' && $method === 'POST':
            handleLogout();
            break;

        // ============ 用户信息 ============
        case $path === '/user/info' && $method === 'GET':
            handleUserInfo();
            break;

        case $path === '/user/index' && $method === 'GET':
            handleUserList();
            break;

        case $path === '/user/vip-progress' && $method === 'GET':
            handleVipProgress();
            break;

        // ============ 项目 ============
        case $path === '/project/index' && $method === 'GET':
            handleProjectIndex($projects);
            break;

        case $path === '/project/detail' && $method === 'GET':
            handleProjectDetail($projects);
            break;

        // ============ 投资 ============
        case $path === '/invest/orders' && $method === 'GET':
            handleInvestOrders();
            break;

        case $path === '/invest/orders' && $method === 'POST':
            handleCreateInvestOrder();
            break;

        // ============ 银行卡 ============
        case $path === '/user/bank/list' && $method === 'GET':
            handleBankList();
            break;

        case $path === '/pay/bank/add' && $method === 'POST':
            handleAddBank();
            break;

        // ============ USDT 地址 ============
        case $path === '/user/usdt-address/list' && $method === 'GET':
            handleUsdtAddressList();
            break;

        case $path === '/user/usdt-address/bind' && $method === 'POST':
            handleBindUsdtAddress();
            break;

        // ============ 团队 ============
        case $path === '/team/reward-summary' && $method === 'GET':
            handleTeamRewardSummary();
            break;

        // ============ 默认 ============
        default:
            http_response_code(404);
            echo json_encode([
                'code' => -1,
                'msg' => "接口不存在: $path",
                'method' => $method
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'error' => $e->getMessage()
    ]);
}

// ==================== API 处理函数 ====================

function getToken() {
    return $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
}

function checkToken() {
    $token = getToken();
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['code' => -1, 'msg' => '未授权，请登录']);
        exit;
    }
    return $token;
}

// 登录
function handleLogin($users) {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['code' => -1, 'msg' => '用户名和密码不能为空']);
        return;
    }

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $token = 'test_token_' . md5($username . time());
        $_SESSION['token'] = $token;
        $_SESSION['user_id'] = $users[$username]['id'];
        $_SESSION['username'] = $username;

        echo json_encode([
            'code' => 1,
            'msg' => '登录成功',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $users[$username]['id'],
                    'username' => $username,
                    'nickname' => $users[$username]['nickname']
                ]
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['code' => -1, 'msg' => '用户名或密码错误']);
    }
}

// 登出
function handleLogout() {
    session_destroy();
    echo json_encode(['code' => 1, 'msg' => '登出成功']);
}

// 获取用户信息
function handleUserInfo() {
    checkToken();

    $username = $_SESSION['username'] ?? '';

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'id' => $_SESSION['user_id'] ?? 1,
            'username' => $username,
            'nickname' => 'Test User',
            'balance_cny' => '1000000.00',
            'balance_usdt' => '10000.00',
            'level' => 'vip3',
            'created_at' => '2024-01-01 00:00:00'
        ]
    ]);
}

// 用户列表
function handleUserList() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'total' => 1,
            'items' => [
                [
                    'id' => 1,
                    'username' => 'G138688',
                    'nickname' => 'Test User',
                    'balance_cny' => '1000000.00'
                ]
            ]
        ]
    ]);
}

// VIP 进度
function handleVipProgress() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'current_level' => 3,
            'next_level' => 4,
            'current_amount' => 50000,
            'next_amount' => 100000,
            'progress' => 50
        ]
    ]);
}

// 项目列表
function handleProjectIndex($projects) {
    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'total' => count($projects),
            'items' => $projects
        ]
    ]);
}

// 项目详情
function handleProjectDetail($projects) {
    $id = $_GET['id'] ?? 1;
    $project = array_filter($projects, fn($p) => $p['id'] == $id);

    if (empty($project)) {
        http_response_code(404);
        echo json_encode(['code' => -1, 'msg' => '项目不存在']);
        return;
    }

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => reset($project)
    ]);
}

// 投资订单
function handleInvestOrders() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'total' => 2,
            'items' => [
                [
                    'id' => 1,
                    'project_id' => 1,
                    'project_name' => '7日理财',
                    'amount' => 50000,
                    'status' => 'completed',
                    'created_at' => '2024-01-01 00:00:00',
                    'profit' => 1200
                ],
                [
                    'id' => 2,
                    'project_id' => 2,
                    'project_name' => '30日理财',
                    'amount' => 100000,
                    'status' => 'running',
                    'created_at' => '2024-01-15 00:00:00',
                    'profit' => 0
                ]
            ]
        ]
    ]);
}

// 创建投资订单
function handleCreateInvestOrder() {
    checkToken();

    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode([
        'code' => 1,
        'msg' => '投资成功',
        'data' => [
            'id' => rand(100, 999),
            'project_id' => $data['project_id'] ?? 1,
            'amount' => $data['amount'] ?? 0,
            'status' => 'running',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
}

// 银行卡列表
function handleBankList() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'items' => [
                [
                    'id' => 1,
                    'bank_name' => '中国银行',
                    'card_no' => '1234****5678',
                    'is_default' => 1
                ]
            ]
        ]
    ]);
}

// 添加银行卡
function handleAddBank() {
    checkToken();

    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode([
        'code' => 1,
        'msg' => '添加成功',
        'data' => [
            'id' => rand(100, 999),
            'bank_name' => $data['bank_name'] ?? '',
            'card_no' => substr($data['card_no'] ?? '', -4)
        ]
    ]);
}

// USDT 地址列表
function handleUsdtAddressList() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'items' => [
                [
                    'id' => 1,
                    'address' => '1A1z7agoat4oPLx2weKH8KZLJrozAG5zNv',
                    'chain' => 'BTC',
                    'is_default' => 1
                ]
            ]
        ]
    ]);
}

// 绑定 USDT 地址
function handleBindUsdtAddress() {
    checkToken();

    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode([
        'code' => 1,
        'msg' => '绑定成功',
        'data' => [
            'id' => rand(100, 999),
            'address' => $data['address'] ?? '',
            'chain' => $data['chain'] ?? 'USDT'
        ]
    ]);
}

// 团队奖励汇总
function handleTeamRewardSummary() {
    checkToken();

    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'total_reward' => 50000,
            'pending_reward' => 10000,
            'team_size' => 10,
            'level_rewards' => [
                'level1' => 30000,
                'level2' => 20000
            ]
        ]
    ]);
}
