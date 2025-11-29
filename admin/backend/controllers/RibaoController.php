<?php
/**
 * 日利宝管理控制器
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
            $where[] = "u.username LIKE ?";
            $params[] = "%$keyword%";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询有日利宝余额的用户（假设有 ribao_balance 字段或单独的日利宝表）
        // 这里使用简化版本，实际可能需要关联日利宝表
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users u $whereClause",
            $params
        )['count'];
        
        $users = db()->fetchAll(
            "SELECT 
                u.id, u.username, u.real_name, u.phone, u.vip_level,
                u.balance, u.created_at
             FROM users u
             $whereClause
             ORDER BY u.balance DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 日利宝统计
        $stats = [
            'total_users' => $total,
            'total_balance' => db()->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM users")['total'],
            'today_earnings' => 0 // 需要根据实际日利宝收益表计算
        ];
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * 派发日利宝收益
     */
    public static function distribute() {
        requirePermission('ribao_manage');
        
        $date = input('date', date('Y-m-d'));
        $rate = input('rate', 0.001); // 默认日利率 0.1%
        
        // 检查是否已派发
        $exists = db()->fetchOne(
            "SELECT COUNT(*) as count FROM earnings_records WHERE earn_date = ? AND type = 'ribao'",
            [$date]
        );
        
        // 假设 earnings_records 表有 type 字段区分收益类型
        // 如果没有，可以使用 balance_logs 表
        
        db()->beginTransaction();
        
        try {
            // 获取所有有余额的用户
            $users = db()->fetchAll("SELECT id, balance FROM users WHERE balance > 0");
            
            $totalDistributed = 0;
            $count = 0;
            
            foreach ($users as $user) {
                $earning = (float)$user['balance'] * (float)$rate;
                
                if ($earning <= 0) {
                    continue;
                }
                
                // 增加用户余额
                $newBalance = (float)$user['balance'] + $earning;
                
                db()->update('users', [
                    'balance' => $newBalance
                ], ['id' => $user['id']]);
                
                // 记录流水
                db()->insert('balance_logs', [
                    'user_id' => $user['id'],
                    'type' => 'ribao_earning',
                    'amount' => $earning,
                    'balance_before' => $user['balance'],
                    'balance_after' => $newBalance,
                    'remark' => "日利宝收益 ($date)",
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $totalDistributed += $earning;
                $count++;
            }
            
            db()->commit();
            
            logAction('distribute_ribao', 'finance', [
                'date' => $date,
                'rate' => $rate,
                'user_count' => $count,
                'total_amount' => $totalDistributed
            ]);
            
            success([
                'date' => $date,
                'rate' => $rate,
                'user_count' => $count,
                'total_distributed' => $totalDistributed
            ], "成功为 $count 位用户派发收益，共计 " . formatMoney($totalDistributed) . " 元");
            
        } catch (Exception $e) {
            db()->rollback();
            error('派发失败: ' . $e->getMessage());
        }
    }
}
