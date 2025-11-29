<?php
/**
 * 日利宝管理控制器
 * 
 * 支持多币种日利宝：
 * - CNY日利宝
 * - USDT日利宝
 * - 转入转出功能（划转）
 */

class RibaoController {
    /**
     * 日利宝用户列表
     */
    public static function users() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(u.username LIKE ? OR u.phone LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 币种筛选
        $currency = input('currency');
        if ($currency === 'CNY') {
            $where[] = "u.ribao_cny_balance > 0";
        } elseif ($currency === 'USDT') {
            $where[] = "u.ribao_usdt_balance > 0";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users u $whereClause",
            $params
        )['count'];
        
        $users = db()->fetchAll(
            "SELECT 
                u.id, u.username, u.real_name, u.phone, u.vip_level,
                u.balance as cny_balance,
                COALESCE(u.usdt_balance, 0) as usdt_balance,
                COALESCE(u.ribao_cny_balance, 0) as ribao_cny_balance,
                COALESCE(u.ribao_usdt_balance, 0) as ribao_usdt_balance,
                u.created_at
             FROM users u
             $whereClause
             ORDER BY (COALESCE(u.ribao_cny_balance, 0) + COALESCE(u.ribao_usdt_balance, 0)) DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 日利宝统计
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(CASE WHEN ribao_cny_balance > 0 THEN 1 END) as cny_user_count,
                COUNT(CASE WHEN ribao_usdt_balance > 0 THEN 1 END) as usdt_user_count,
                COALESCE(SUM(ribao_cny_balance), 0) as total_cny_balance,
                COALESCE(SUM(ribao_usdt_balance), 0) as total_usdt_balance
             FROM users"
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * 派发日利宝收益（支持多币种）
     */
    public static function distribute() {
        requirePermission('ribao_manage');
        
        $date = input('date', date('Y-m-d'));
        $currency = input('currency', 'CNY'); // CNY 或 USDT
        
        // 从配置获取利率
        $rateKey = $currency === 'USDT' ? 'usdt_daily_rate' : 'cny_daily_rate';
        $rateConfig = db()->fetchOne(
            "SELECT `value` FROM system_config WHERE `key` = ?",
            [$rateKey]
        );
        $rate = $rateConfig ? (float)$rateConfig['value'] : ($currency === 'USDT' ? 0.002 : 0.001);
        
        // 根据币种选择余额字段
        $balanceField = $currency === 'USDT' ? 'ribao_usdt_balance' : 'ribao_cny_balance';
        
        // 检查是否已派发
        $exists = db()->fetchOne(
            "SELECT COUNT(*) as count FROM ribao_earnings WHERE earn_date = ? AND currency = ?",
            [$date, $currency]
        );
        
        if ($exists && $exists['count'] > 0) {
            error("今日{$currency}收益已派发");
        }
        
        db()->beginTransaction();
        
        try {
            // 获取所有有日利宝余额的用户
            $users = db()->fetchAll(
                "SELECT id, $balanceField as ribao_balance FROM users WHERE $balanceField > 0"
            );
            
            $totalDistributed = 0;
            $count = 0;
            
            foreach ($users as $user) {
                $earning = (float)$user['ribao_balance'] * (float)$rate;
                
                if ($earning <= 0) {
                    continue;
                }
                
                // 增加用户日利宝余额
                $newBalance = (float)$user['ribao_balance'] + $earning;
                
                db()->update('users', [
                    $balanceField => $newBalance
                ], ['id' => $user['id']]);
                
                // 记录收益
                db()->insert('ribao_earnings', [
                    'user_id' => $user['id'],
                    'currency' => $currency,
                    'balance' => $user['ribao_balance'],
                    'rate' => $rate,
                    'earning' => $earning,
                    'earn_date' => $date,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // 记录流水
                db()->insert('balance_logs', [
                    'user_id' => $user['id'],
                    'type' => 'ribao_earning',
                    'currency' => $currency,
                    'amount' => $earning,
                    'balance_before' => $user['ribao_balance'],
                    'balance_after' => $newBalance,
                    'remark' => "日利宝{$currency}收益 ($date)",
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $totalDistributed += $earning;
                $count++;
            }
            
            db()->commit();
            
            logAction('distribute_ribao', 'finance', [
                'date' => $date,
                'currency' => $currency,
                'rate' => $rate,
                'user_count' => $count,
                'total_amount' => $totalDistributed
            ]);
            
            $unit = $currency === 'USDT' ? 'USDT' : '元';
            success([
                'date' => $date,
                'currency' => $currency,
                'rate' => $rate,
                'user_count' => $count,
                'total_distributed' => $totalDistributed
            ], "成功为 $count 位用户派发{$currency}收益，共计 " . formatMoney($totalDistributed, $currency === 'USDT' ? 4 : 2) . " $unit");
            
        } catch (Exception $e) {
            db()->rollback();
            error('派发失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取日利宝收益记录
     */
    public static function earningRecords() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 用户搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "e.user_id IN (SELECT id FROM users WHERE username LIKE ? OR phone LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 币种筛选
        $currency = input('currency');
        if ($currency) {
            $where[] = "e.currency = ?";
            $params[] = $currency;
        }
        
        // 日期筛选
        $date = input('date');
        if ($date) {
            $where[] = "e.earn_date = ?";
            $params[] = $date;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM ribao_earnings e $whereClause",
            $params
        )['count'] ?? 0;
        
        $records = db()->fetchAll(
            "SELECT 
                e.*,
                u.username,
                u.real_name
             FROM ribao_earnings e
             LEFT JOIN users u ON e.user_id = u.id
             $whereClause
             ORDER BY e.earn_date DESC, e.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $records
        ]);
    }
    
    /**
     * 获取日利宝转账记录
     */
    public static function transferRecords() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 用户搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "r.user_id IN (SELECT id FROM users WHERE username LIKE ? OR phone LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 币种筛选
        $currency = input('currency');
        if ($currency) {
            $where[] = "r.currency = ?";
            $params[] = $currency;
        }
        
        // 类型筛选
        $type = input('type');
        if ($type) {
            $where[] = "r.type = ?";
            $params[] = $type;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM ribao_transfer_records r $whereClause",
            $params
        )['count'] ?? 0;
        
        $records = db()->fetchAll(
            "SELECT 
                r.*,
                u.username,
                u.real_name
             FROM ribao_transfer_records r
             LEFT JOIN users u ON r.user_id = u.id
             $whereClause
             ORDER BY r.created_at DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $records
        ]);
    }
    
    /**
     * 日利宝统计
     */
    public static function stats() {
        requireLogin();
        
        // 总体统计
        $overall = db()->fetchOne(
            "SELECT 
                COUNT(CASE WHEN ribao_cny_balance > 0 THEN 1 END) as cny_user_count,
                COUNT(CASE WHEN ribao_usdt_balance > 0 THEN 1 END) as usdt_user_count,
                COALESCE(SUM(ribao_cny_balance), 0) as total_cny_balance,
                COALESCE(SUM(ribao_usdt_balance), 0) as total_usdt_balance
             FROM users"
        );
        
        // 今日收益
        $today = date('Y-m-d');
        $todayEarnings = db()->fetchOne(
            "SELECT 
                COALESCE(SUM(CASE WHEN currency = 'CNY' THEN earning ELSE 0 END), 0) as cny_earning,
                COALESCE(SUM(CASE WHEN currency = 'USDT' THEN earning ELSE 0 END), 0) as usdt_earning
             FROM ribao_earnings WHERE earn_date = ?",
            [$today]
        );
        
        // 累计收益
        $totalEarnings = db()->fetchOne(
            "SELECT 
                COALESCE(SUM(CASE WHEN currency = 'CNY' THEN earning ELSE 0 END), 0) as cny_earning,
                COALESCE(SUM(CASE WHEN currency = 'USDT' THEN earning ELSE 0 END), 0) as usdt_earning
             FROM ribao_earnings"
        );
        
        // 最近7天派发记录
        $recentDays = db()->fetchAll(
            "SELECT 
                earn_date,
                currency,
                COUNT(*) as user_count,
                SUM(earning) as total_earning
             FROM ribao_earnings 
             WHERE earn_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY earn_date, currency
             ORDER BY earn_date DESC"
        );
        
        success([
            'overall' => $overall,
            'today_earnings' => $todayEarnings,
            'total_earnings' => $totalEarnings,
            'recent_days' => $recentDays
        ]);
    }
}
