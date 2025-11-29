<?php
/**
 * Providence Admin Backend - 响应和工具函数
 */

if (!defined('ADMIN_API')) {
    die('Access Denied');
}

/**
 * 成功响应
 */
function success($data = null, $msg = 'success') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 错误响应
 */
function error($msg = 'error', $code = -1, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 需要登录
 */
function requireLogin() {
    $admin = Auth::getCurrentAdmin();
    if (!$admin) {
        error('请先登录', -401, 401);
    }
    return $admin;
}

/**
 * 需要权限
 */
function requirePermission($permission) {
    $admin = requireLogin();
    
    if (!Auth::checkPermission($permission)) {
        error('没有权限执行此操作', -403, 403);
    }
    
    return $admin;
}

/**
 * 获取请求参数
 */
function input($key = null, $default = null) {
    static $input = null;
    
    if ($input === null) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        } else {
            $input = array_merge($_GET, $_POST);
        }
    }
    
    if ($key === null) {
        return $input;
    }
    
    return $input[$key] ?? $default;
}

/**
 * 生成订单号
 */
function generateOrderNo($prefix = '') {
    return $prefix . date('YmdHis') . sprintf('%04d', mt_rand(0, 9999));
}

/**
 * 格式化金额
 */
function formatMoney($amount, $decimals = 2) {
    return number_format((float)$amount, $decimals, '.', '');
}

/**
 * 记录操作日志
 */
function logAction($action, $module, $content = '') {
    $admin = Auth::getCurrentAdmin();
    
    if (!$admin) {
        return;
    }
    
    try {
        db()->insert('admin_logs', [
            'admin_id' => $admin['admin_id'],
            'action' => $action,
            'module' => $module,
            'content' => is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : $content,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // 忽略日志错误
    }
}

/**
 * 安全过滤
 */
function sanitize($value) {
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * 验证必填字段
 */
function validateRequired($fields, $data = null) {
    $data = $data ?? input();
    $missing = [];
    
    foreach ($fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        error('缺少必填字段: ' . implode(', ', $missing));
    }
    
    return true;
}

/**
 * 隐藏敏感信息
 */
function maskString($str, $start = 3, $end = 4, $mask = '****') {
    $len = mb_strlen($str);
    if ($len <= $start + $end) {
        return $str;
    }
    return mb_substr($str, 0, $start) . $mask . mb_substr($str, -$end);
}

/**
 * 获取分页参数
 */
function getPagination() {
    $page = max(1, (int)input('page', 1));
    $maxPageSize = defined('MAX_PAGE_SIZE') ? MAX_PAGE_SIZE : 100;
    $pageSize = min($maxPageSize, max(1, (int)input('page_size', PAGE_SIZE)));
    
    return [$page, $pageSize];
}
