<?php
/**
 * 日志控制器
 */

class LogController {
    /**
     * 操作日志列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 管理员筛选
        $adminId = input('admin_id');
        if ($adminId) {
            $where[] = "l.admin_id = ?";
            $params[] = $adminId;
        }
        
        // 模块筛选
        $module = input('module');
        if ($module) {
            $where[] = "l.module = ?";
            $params[] = $module;
        }
        
        // 操作类型筛选
        $action = input('action');
        if ($action) {
            $where[] = "l.action = ?";
            $params[] = $action;
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "l.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "l.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM admin_logs l $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $logs = db()->fetchAll(
            "SELECT 
                l.*,
                a.username as admin_username,
                a.real_name as admin_name
             FROM admin_logs l
             LEFT JOIN admins a ON l.admin_id = a.id
             $whereClause
             ORDER BY l.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $logs
        ]);
    }
    
    /**
     * 交易记录列表
     */
    public static function transactions() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 用户筛选
        $userId = input('user_id');
        if ($userId) {
            $where[] = "b.user_id = ?";
            $params[] = $userId;
        }
        
        // 类型筛选
        $type = input('type');
        if ($type) {
            $where[] = "b.type = ?";
            $params[] = $type;
        }
        
        // 搜索（用户名）
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "u.username LIKE ?";
            $params[] = "%$keyword%";
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "b.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "b.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM balance_logs b
             LEFT JOIN users u ON b.user_id = u.id
             $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $transactions = db()->fetchAll(
            "SELECT 
                b.*,
                u.username, u.real_name
             FROM balance_logs b
             LEFT JOIN users u ON b.user_id = u.id
             $whereClause
             ORDER BY b.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 统计
        $stats = db()->fetchOne(
            "SELECT 
                COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) as total_out
             FROM balance_logs"
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $transactions,
            'stats' => $stats
        ]);
    }
}
