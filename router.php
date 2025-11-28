<?php
/**
 * PHP 内置服务器路由器
 * 将所有 /api/* 请求转发到 api.php
 */

$requested_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 如果请求的是实际文件或目录，直接返回
if (file_exists(__DIR__ . $requested_path) && is_file(__DIR__ . $requested_path)) {
    return false; // 让 PHP 服务器直接处理文件
}

// 如果请求 /api/* 路由，转发到 api.php
if (strpos($requested_path, '/api/') === 0) {
    $_SERVER['PHP_SELF'] = '/api.php';
    include __DIR__ . '/api.php';
    exit;
}

// 其他请求返回 404
if (preg_match('/\.\w+$/', $requested_path)) {
    // 看起来是文件请求
    http_response_code(404);
    return false;
}

// 否则返回 index.html
return false;
