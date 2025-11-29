<?php
/**
 * Providence 前台API - 辅助函数
 */

/**
 * 成功响应
 */
function success($data = null, $msg = '成功') {
    echo json_encode([
        'code' => 1,
        'msg' => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 失败响应
 */
function error($msg = '操作失败', $code = -1, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'code' => $code,
        'msg' => $msg
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 获取POST JSON数据
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

/**
 * 获取GET参数
 */
function getQuery($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * 获取POST参数
 */
function getPost($key = null, $default = null) {
    $data = getJsonInput();
    if (empty($data)) {
        $data = $_POST;
    }
    if ($key === null) {
        return $data;
    }
    return $data[$key] ?? $default;
}

/**
 * 生成订单号
 */
function generateOrderNo($prefix = 'ORD') {
    return $prefix . date('YmdHis') . sprintf('%04d', mt_rand(0, 9999));
}

/**
 * 格式化金额
 */
function formatMoney($amount, $decimals = 2) {
    return number_format($amount, $decimals, '.', '');
}

/**
 * 手机号脱敏
 */
function maskPhone($phone) {
    if (strlen($phone) >= 11) {
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }
    return $phone;
}

/**
 * 银行卡号脱敏
 */
function maskBankCard($cardNo) {
    $len = strlen($cardNo);
    if ($len > 8) {
        return substr($cardNo, 0, 4) . '****' . substr($cardNo, -4);
    }
    return $cardNo;
}

/**
 * 分页参数
 */
function getPagination() {
    $page = max(1, intval(getQuery('page', 1)));
    
    // 先检查 limit 参数，再检查 pageSize 参数，最后使用默认值
    $limitParam = getQuery('limit');
    $pageSizeParam = getQuery('pageSize');
    $rawPageSize = $limitParam !== null ? $limitParam : ($pageSizeParam !== null ? $pageSizeParam : DEFAULT_PAGE_SIZE);
    $pageSize = min(MAX_PAGE_SIZE, max(1, intval($rawPageSize)));
    
    $offset = ($page - 1) * $pageSize;
    
    return [
        'page' => $page,
        'pageSize' => $pageSize,
        'offset' => $offset
    ];
}

/**
 * 生成分页响应
 */
function paginateResponse($items, $total, $page, $pageSize) {
    return [
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPages' => ceil($total / $pageSize)
    ];
}

/**
 * 验证手机号
 */
function isValidPhone($phone) {
    return preg_match('/^1[3-9]\d{9}$/', $phone);
}

/**
 * 验证邮箱
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 验证身份证号
 */
function isValidIdCard($idCard) {
    return preg_match('/^\d{17}[\dXx]$/', $idCard);
}

/**
 * 日期格式化
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '';
    }
    if (is_string($date)) {
        $date = strtotime($date);
    }
    return date($format, $date);
}
