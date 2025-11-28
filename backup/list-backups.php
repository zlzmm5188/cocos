<?php
/**
 * 列出备份文件
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$backupDir = __DIR__;
$files = [];

try {
    // 扫描备份目录
    $items = scandir($backupDir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === 'index.html' || $item === 'list-backups.php') {
            continue;
        }

        $filePath = $backupDir . '/' . $item;

        if (is_file($filePath) && (strpos($item, '.sql') !== false || strpos($item, '.tar.gz') !== false)) {
            $files[] = [
                'name' => $item,
                'size' => filesize($filePath),
                'time' => date('Y-m-d H:i:s', filemtime($filePath))
            ];
        }
    }

    // 按时间倒序排序
    usort($files, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    echo json_encode([
        'code' => 1,
        'message' => 'success',
        'data' => $files
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'code' => -1,
        'message' => $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
