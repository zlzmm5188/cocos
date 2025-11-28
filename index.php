<?php
/**
 * 前台 + API 统一入口
 *
 * 当访问 /api/* 时，自动路由到 api.php
 * 当访问其他路径时，提供前台页面
 */

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_dir = dirname($_SERVER['SCRIPT_NAME']);

// 移除脚本目录前缀
if (strpos($request_uri, $script_dir) === 0) {
    $request_uri = substr($request_uri, strlen($script_dir));
}

// 移除前导斜杠
$request_uri = ltrim($request_uri, '/');

// 如果是 API 请求，路由到 api.php
if (strpos($request_uri, 'api/') === 0 || $request_uri === 'api') {
    // 提供 API 响应
    include 'api.php';
    exit;
}

// 其他请求，提供前台页面
// 如果请求的是根路径或没有指定文件，返回 login.html 或 index.html
if (empty($request_uri) || $request_uri === 'index.php') {
    $file = 'login.html';
} else {
    // 构建文件路径
    $file = $request_uri;

    // 如果请求的是目录，尝试加载该目录下的 index.html
    if (is_dir($file)) {
        $file .= '/index.html';
    }
}

// 检查文件是否存在
if (file_exists($file) && is_file($file)) {
    // 确定正确的 MIME 类型
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file);
    finfo_close($finfo);

    header("Content-Type: $mime");
    readfile($file);
} else {
    // 文件不存在，返回 404
    http_response_code(404);
    echo "File not found: $request_uri";
}
