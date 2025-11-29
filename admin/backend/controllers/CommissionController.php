<?php
/**
 * 返佣管理控制器
 * 
 * 管理二级返利系统：
 * - 只能获得下面二级用户购买项目金额的返利
 * - 返利在项目结束后发放
 * - 根据用户VIP等级确定返利比例
 */

class CommissionController {
    
    /**
     * 获取返佣记录列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 用户搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "c.user_id IN (SELECT id FROM users WHERE username LIKE ? OR phone LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "c.status = ?";
            $params[] = $status;
        }
        
        // 级别筛选
        $level = input('level');
        if ($level !== null && $level !== '') {
            $where[] = "c.level = ?";
            $params[] = $level;
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "c.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "c.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM commission_records c $whereClause",
            $params
        )['count'] ?? 0;
        
        $records = db()->fetchAll(
            "SELECT 
                c.*,
                u.username as user_name,
                u.real_name as user_real_name,
                fu.username as from_user_name,
                p.title as project_title
             FROM commission_records c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN users fu ON c.from_user_id = fu.id
             LEFT JOIN projects p ON c.project_id = p.id
             $whereClause
             ORDER BY c.created_at DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 统计
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(CASE WHEN status = 0 THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as paid_amount,
                COALESCE(SUM(CASE WHEN level = 1 THEN amount ELSE 0 END), 0) as level1_total,
                COALESCE(SUM(CASE WHEN level = 2 THEN amount ELSE 0 END), 0) as level2_total
             FROM commission_records"
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $records,
            'stats' => $stats
        ]);
    }
    
    /**
     * 待发放返佣列表（项目已结束的）
     */
    public static function pendingList() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $offset = ($page - 1) * $pageSize;
        
        // 查询待发放的返佣（关联的投资订单已完成）
        $records = db()->fetchAll(
            "SELECT 
                c.*,
                u.username as user_name,
                u.real_name as user_real_name,
                u.vip_level,
                fu.username as from_user_name,
                p.title as project_title,
                i.end_date,
                i.status as investment_status
             FROM commission_records c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN users fu ON c.from_user_id = fu.id
             LEFT JOIN projects p ON c.project_id = p.id
             LEFT JOIN user_investments i ON c.investment_id = i.id
             WHERE c.status = 0 AND i.status = 2
             ORDER BY i.end_date ASC
             LIMIT $pageSize OFFSET $offset"
        );
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count 
             FROM commission_records c
             LEFT JOIN user_investments i ON c.investment_id = i.id
             WHERE c.status = 0 AND i.status = 2"
        )['count'] ?? 0;
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $records
        ]);
    }
    
    /**
     * 发放返佣（单条）
     */
    public static function pay() {
        requirePermission('commission_pay');
        
        $id = input('id');
        
        if (!$id) {
            error('参数错误');
        }
        
        $record = db()->fetchOne(
            "SELECT c.*, i.status as investment_status 
             FROM commission_records c
             LEFT JOIN user_investments i ON c.investment_id = i.id
             WHERE c.id = ?",
            [$id]
        );
        
        if (!$record) {
            error('记录不存在');
        }
        
        if ($record['status'] != 0) {
            error('该返佣已处理');
        }
        
        if ($record['investment_status'] != 2) {
            error('关联的投资订单尚未完成，不能发放返佣');
        }
        
        self::executePayCommission($record);
        
        logAction('pay_commission', 'finance', [
            'id' => $id,
            'user_id' => $record['user_id'],
            'amount' => $record['amount']
        ]);
        
        success(null, '发放成功');
    }
    
    /**
     * 批量发放返佣
     */
    public static function batchPay() {
        requirePermission('commission_pay');
        
        $ids = input('ids');
        
        if (!is_array($ids) || empty($ids)) {
            error('请选择要发放的记录');
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $records = db()->fetchAll(
            "SELECT c.*, i.status as investment_status 
             FROM commission_records c
             LEFT JOIN user_investments i ON c.investment_id = i.id
             WHERE c.id IN ($placeholders) AND c.status = 0 AND i.status = 2",
            $ids
        );
        
        if (empty($records)) {
            error('没有可发放的记录');
        }
        
        db()->beginTransaction();
        
        try {
            $successCount = 0;
            $totalAmount = 0;
            
            foreach ($records as $record) {
                self::executePayCommission($record, false);
                $successCount++;
                $totalAmount += (float)$record['amount'];
            }
            
            db()->commit();
            
            logAction('batch_pay_commission', 'finance', [
                'count' => $successCount,
                'total_amount' => $totalAmount
            ]);
            
            success([
                'success_count' => $successCount,
                'total_amount' => $totalAmount
            ], "成功发放 $successCount 条返佣，共计 " . formatMoney($totalAmount) . " 元");
            
        } catch (Exception $e) {
            db()->rollback();
            error('批量发放失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 执行发放返佣
     */
    private static function executePayCommission($record, $useTransaction = true) {
        if ($useTransaction) {
            db()->beginTransaction();
        }
        
        try {
            $userId = $record['user_id'];
            $amount = (float)$record['amount'];
            $currency = $record['currency'] ?? 'CNY';
            
            // 根据币种选择余额字段
            $balanceField = $currency === 'USDT' ? 'usdt_balance' : 'balance';
            
            $user = db()->fetchOne("SELECT id, $balanceField FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                throw new Exception('用户不存在');
            }
            
            $balanceBefore = (float)($user[$balanceField] ?? 0);
            $balanceAfter = $balanceBefore + $amount;
            
            // 更新余额
            db()->update('users', [
                $balanceField => $balanceAfter
            ], ['id' => $userId]);
            
            // 更新返佣记录状态
            db()->update('commission_records', [
                'status' => 1,
                'paid_at' => date('Y-m-d H:i:s')
            ], ['id' => $record['id']]);
            
            // 记录流水
            $level = $record['level'] == 1 ? '一级' : '二级';
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'commission',
                'currency' => $currency,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'ref_id' => $record['id'],
                'remark' => "{$level}返佣",
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($useTransaction) {
                db()->commit();
            }
            
        } catch (Exception $e) {
            if ($useTransaction) {
                db()->rollback();
            }
            throw $e;
        }
    }
    
    /**
     * 创建返佣记录（当用户购买项目时调用）
     * 
     * @param int $userId 购买者用户ID
     * @param int $projectId 项目ID
     * @param int $investmentId 投资订单ID
     * @param float $investAmount 投资金额
     * @param string $currency 币种 CNY/USDT
     */
    public static function createCommission($userId, $projectId, $investmentId, $investAmount, $currency = 'CNY') {
        // 获取购买者信息
        $user = db()->fetchOne("SELECT id, parent_id FROM users WHERE id = ?", [$userId]);
        
        if (!$user || !$user['parent_id']) {
            return; // 没有上级，不创建返佣
        }
        
        // 一级上级
        $level1Parent = db()->fetchOne(
            "SELECT id, parent_id, vip_level FROM users WHERE id = ?",
            [$user['parent_id']]
        );
        
        if ($level1Parent) {
            // 获取一级返利比例
            require_once __DIR__ . '/VipConfigController.php';
            $rates = VipConfigController::getInviteRewardRates($level1Parent['vip_level']);
            $level1Rate = (float)$rates['level1'] / 100;
            $level1Amount = $investAmount * $level1Rate;
            
            if ($level1Amount > 0) {
                db()->insert('commission_records', [
                    'user_id' => $level1Parent['id'],
                    'from_user_id' => $userId,
                    'project_id' => $projectId,
                    'investment_id' => $investmentId,
                    'level' => 1,
                    'invest_amount' => $investAmount,
                    'rate' => $level1Rate,
                    'amount' => $level1Amount,
                    'currency' => $currency,
                    'status' => 0, // 待发放
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // 二级上级
            if ($level1Parent['parent_id']) {
                $level2Parent = db()->fetchOne(
                    "SELECT id, vip_level FROM users WHERE id = ?",
                    [$level1Parent['parent_id']]
                );
                
                if ($level2Parent) {
                    $rates = VipConfigController::getInviteRewardRates($level2Parent['vip_level']);
                    $level2Rate = (float)$rates['level2'] / 100;
                    $level2Amount = $investAmount * $level2Rate;
                    
                    if ($level2Amount > 0) {
                        db()->insert('commission_records', [
                            'user_id' => $level2Parent['id'],
                            'from_user_id' => $userId,
                            'project_id' => $projectId,
                            'investment_id' => $investmentId,
                            'level' => 2,
                            'invest_amount' => $investAmount,
                            'rate' => $level2Rate,
                            'amount' => $level2Amount,
                            'currency' => $currency,
                            'status' => 0, // 待发放
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * 自动发放已完成项目的返佣
     */
    public static function autoDistribute() {
        requirePermission('commission_pay');
        
        // 查找所有待发放的返佣（关联投资已完成）
        $records = db()->fetchAll(
            "SELECT c.* 
             FROM commission_records c
             LEFT JOIN user_investments i ON c.investment_id = i.id
             WHERE c.status = 0 AND i.status = 2
             LIMIT 1000"
        );
        
        if (empty($records)) {
            success(['count' => 0, 'total_amount' => 0], '没有待发放的返佣');
            return;
        }
        
        db()->beginTransaction();
        
        try {
            $successCount = 0;
            $totalAmount = 0;
            
            foreach ($records as $record) {
                self::executePayCommission($record, false);
                $successCount++;
                $totalAmount += (float)$record['amount'];
            }
            
            db()->commit();
            
            logAction('auto_distribute_commission', 'finance', [
                'count' => $successCount,
                'total_amount' => $totalAmount
            ]);
            
            success([
                'count' => $successCount,
                'total_amount' => $totalAmount
            ], "自动发放成功，共 $successCount 条，金额 " . formatMoney($totalAmount) . " 元");
            
        } catch (Exception $e) {
            db()->rollback();
            error('自动发放失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 返佣统计
     */
    public static function stats() {
        requireLogin();
        
        // 今日统计
        $today = date('Y-m-d');
        $todayStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as amount
             FROM commission_records 
             WHERE DATE(created_at) = ? AND status = 1",
            [$today]
        );
        
        // 本月统计
        $monthStart = date('Y-m-01');
        $monthStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as amount
             FROM commission_records 
             WHERE created_at >= ? AND status = 1",
            [$monthStart]
        );
        
        // 总计
        $totalStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(CASE WHEN status = 0 THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as paid_amount,
                COALESCE(SUM(CASE WHEN level = 1 AND status = 1 THEN amount ELSE 0 END), 0) as level1_paid,
                COALESCE(SUM(CASE WHEN level = 2 AND status = 1 THEN amount ELSE 0 END), 0) as level2_paid
             FROM commission_records"
        );
        
        // 按VIP等级统计
        $vipStats = db()->fetchAll(
            "SELECT 
                u.vip_level,
                COUNT(DISTINCT c.user_id) as user_count,
                COALESCE(SUM(c.amount), 0) as total_amount
             FROM commission_records c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.status = 1
             GROUP BY u.vip_level
             ORDER BY u.vip_level"
        );
        
        success([
            'today' => [
                'count' => (int)$todayStats['count'],
                'amount' => (float)$todayStats['amount']
            ],
            'month' => [
                'count' => (int)$monthStats['count'],
                'amount' => (float)$monthStats['amount']
            ],
            'total' => [
                'count' => (int)$totalStats['total_count'],
                'pending_amount' => (float)$totalStats['pending_amount'],
                'paid_amount' => (float)$totalStats['paid_amount'],
                'level1_paid' => (float)$totalStats['level1_paid'],
                'level2_paid' => (float)$totalStats['level2_paid']
            ],
            'by_vip' => $vipStats
        ]);
    }
}
