<?php
/**
 * 仪表盘控制器
 */

class DashboardController {
    /**
     * 获取统计数据
     */
    public static function stats() {
        requireLogin();
        
        // 用户统计
        $totalUsers = db()->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        $todayUsers = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()"
        )['count'];
        
        // 投资统计
        $totalInvest = db()->fetchOne(
            "SELECT COALESCE(SUM(invest_amount), 0) as total FROM user_investments"
        )['total'];
        $todayInvest = db()->fetchOne(
            "SELECT COALESCE(SUM(invest_amount), 0) as total FROM user_investments WHERE DATE(created_at) = CURDATE()"
        )['total'];
        
        // 充值待审核
        $pendingRecharges = db()->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM recharge_records WHERE status = 0"
        );
        
        // 提现待审核
        $pendingWithdrawals = db()->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as amount FROM withdraw_records WHERE status = 0"
        );
        
        // KYC待审核
        $pendingKyc = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE kyc_status = 1"
        )['count'];
        
        // 今日订单
        $todayOrders = db()->fetchOne(
            "SELECT COUNT(*) as count FROM user_investments WHERE DATE(created_at) = CURDATE()"
        )['count'];
        
        // 今日收益（已发放）
        $todayProfit = db()->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM earnings_records WHERE DATE(earn_date) = CURDATE()"
        )['total'];
        
        // 总收益
        $totalProfit = db()->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM earnings_records WHERE status = 1"
        )['total'];
        
        success([
            'totalUsers' => (int)$totalUsers,
            'todayUsers' => (int)$todayUsers,
            'totalInvest' => (float)$totalInvest,
            'todayInvest' => (float)$todayInvest,
            'pendingRecharges' => (int)$pendingRecharges['count'],
            'pendingRechargeAmount' => (float)$pendingRecharges['amount'],
            'pendingWithdrawals' => (int)$pendingWithdrawals['count'],
            'pendingWithdrawAmount' => (float)$pendingWithdrawals['amount'],
            'pendingKyc' => (int)$pendingKyc,
            'todayOrders' => (int)$todayOrders,
            'todayProfit' => (float)$todayProfit,
            'totalProfit' => (float)$totalProfit
        ]);
    }
    
    /**
     * 投资趋势图表数据（近7天）
     */
    public static function investTrend() {
        requireLogin();
        
        $data = db()->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(invest_amount), 0) as amount,
                COUNT(*) as count
             FROM user_investments 
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );
        
        // 填充缺失的日期
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayData = array_filter($data, fn($d) => $d['date'] === $date);
            $dayData = reset($dayData);
            
            $result[] = [
                'date' => $date,
                'amount' => $dayData ? (float)$dayData['amount'] : 0,
                'count' => $dayData ? (int)$dayData['count'] : 0
            ];
        }
        
        success($result);
    }
    
    /**
     * 用户VIP分布
     */
    public static function userDistribution() {
        requireLogin();
        
        $data = db()->fetchAll(
            "SELECT vip_level, COUNT(*) as count FROM users GROUP BY vip_level ORDER BY vip_level"
        );
        
        success($data);
    }
    
    /**
     * 最近订单
     */
    public static function recentOrders() {
        requireLogin();
        
        $orders = db()->fetchAll(
            "SELECT 
                i.id, i.order_no, i.invest_amount, i.status, i.created_at,
                u.username,
                p.title as project_name
             FROM user_investments i
             LEFT JOIN users u ON i.user_id = u.id
             LEFT JOIN projects p ON i.project_id = p.id
             ORDER BY i.created_at DESC
             LIMIT 10"
        );
        
        success($orders);
    }
    
    /**
     * 最近活动
     */
    public static function recentActivities() {
        requireLogin();
        
        // 获取最近的各类活动
        $activities = [];
        
        // 新用户注册
        $newUsers = db()->fetchAll(
            "SELECT id, username, created_at, 'register' as type FROM users 
             ORDER BY created_at DESC LIMIT 5"
        );
        
        // 充值
        $recharges = db()->fetchAll(
            "SELECT r.id, u.username, r.amount, r.created_at, 'recharge' as type 
             FROM recharge_records r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.status = 1
             ORDER BY r.created_at DESC LIMIT 5"
        );
        
        // 投资
        $investments = db()->fetchAll(
            "SELECT i.id, u.username, p.title as project_name, i.invest_amount as amount, i.created_at, 'invest' as type 
             FROM user_investments i
             LEFT JOIN users u ON i.user_id = u.id
             LEFT JOIN projects p ON i.project_id = p.id
             ORDER BY i.created_at DESC LIMIT 5"
        );
        
        // 提现申请
        $withdrawals = db()->fetchAll(
            "SELECT w.id, u.username, w.amount, w.created_at, 'withdraw' as type 
             FROM withdraw_records w
             LEFT JOIN users u ON w.user_id = u.id
             ORDER BY w.created_at DESC LIMIT 5"
        );
        
        // 合并并按时间排序
        $activities = array_merge($newUsers, $recharges, $investments, $withdrawals);
        usort($activities, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        success(array_slice($activities, 0, 10));
    }
}
