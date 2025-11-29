<?php
/**
 * 订单管理控制器
 */

class OrderController {
    /**
     * 订单列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "i.status = ?";
            $params[] = $status;
        }
        
        // 用户ID
        $userId = input('user_id');
        if ($userId) {
            $where[] = "i.user_id = ?";
            $params[] = $userId;
        }
        
        // 产品ID
        $projectId = input('project_id');
        if ($projectId) {
            $where[] = "i.project_id = ?";
            $params[] = $projectId;
        }
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(i.order_no LIKE ? OR u.username LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "i.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "i.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM user_investments i
             LEFT JOIN users u ON i.user_id = u.id
             $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $orders = db()->fetchAll(
            "SELECT 
                i.*,
                u.username, u.real_name, u.vip_level,
                p.title as project_name, p.daily_rate as project_rate
             FROM user_investments i
             LEFT JOIN users u ON i.user_id = u.id
             LEFT JOIN projects p ON i.project_id = p.id
             $whereClause
             ORDER BY i.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $orders
        ]);
    }
    
    /**
     * 订单详情
     */
    public static function detail($id) {
        requireLogin();
        
        $order = db()->fetchOne(
            "SELECT 
                i.*,
                u.username, u.real_name, u.phone, u.vip_level,
                p.title as project_name, p.daily_rate as project_rate, p.category
             FROM user_investments i
             LEFT JOIN users u ON i.user_id = u.id
             LEFT JOIN projects p ON i.project_id = p.id
             WHERE i.id = ?",
            [$id]
        );
        
        if (!$order) {
            error('订单不存在');
        }
        
        // 获取收益记录
        $earnings = db()->fetchAll(
            "SELECT * FROM earnings_records WHERE investment_id = ? ORDER BY earn_date DESC",
            [$id]
        );
        
        $order['earnings'] = $earnings;
        
        success($order);
    }
    
    /**
     * 订单统计
     */
    public static function stats() {
        requireLogin();
        
        // 总体统计
        $overall = db()->fetchOne(
            "SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(invest_amount), 0) as total_amount,
                COALESCE(SUM(earned_amount), 0) as total_earned,
                COUNT(CASE WHEN status = 1 THEN 1 END) as active_count,
                COALESCE(SUM(CASE WHEN status = 1 THEN invest_amount ELSE 0 END), 0) as active_amount
             FROM user_investments"
        );
        
        // 今日统计
        $today = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(invest_amount), 0) as amount
             FROM user_investments 
             WHERE DATE(created_at) = CURDATE()"
        );
        
        // 本月统计
        $month = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(invest_amount), 0) as amount
             FROM user_investments 
             WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
        );
        
        // 按产品统计
        $byProduct = db()->fetchAll(
            "SELECT 
                p.id, p.title,
                COUNT(i.id) as order_count,
                COALESCE(SUM(i.invest_amount), 0) as total_amount
             FROM projects p
             LEFT JOIN user_investments i ON p.id = i.project_id
             GROUP BY p.id
             ORDER BY total_amount DESC
             LIMIT 10"
        );
        
        success([
            'overall' => $overall,
            'today' => $today,
            'month' => $month,
            'by_product' => $byProduct
        ]);
    }
}
