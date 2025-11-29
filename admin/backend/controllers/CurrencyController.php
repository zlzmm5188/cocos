<?php
/**
 * 币种管理控制器
 * 
 * 支持多币种管理：
 * - USDT 和 CNY (人民币) 分离
 * - CNY 兑换 USDT（单向）
 * - 积分兑换人民币（默认比例 0.5积分=1元）
 */

class CurrencyController {
    /**
     * 获取币种配置
     */
    public static function config() {
        requireLogin();
        
        // 从系统配置获取币种相关设置
        $configs = db()->fetchAll(
            "SELECT `key`, `value` FROM system_config WHERE `group` = 'currency'"
        );
        
        $result = [
            'cny_to_usdt_enabled' => true,        // CNY->USDT兑换开关
            'cny_to_usdt_rate' => 7.2,            // CNY->USDT汇率（7.2元=1USDT）
            'cny_to_usdt_min' => 100,             // 最小兑换金额
            'cny_to_usdt_max' => 100000,          // 最大兑换金额
            'cny_to_usdt_fee_rate' => 0.002,      // 兑换手续费率
            'points_to_cny_enabled' => true,      // 积分->CNY兑换开关
            'points_to_cny_rate' => 0.5,          // 积分->CNY比例（0.5积分=1元）
            'points_to_cny_min' => 100,           // 最小兑换积分
            'usdt_daily_rate' => 0.002,           // USDT日利宝日利率
            'cny_daily_rate' => 0.001,            // CNY日利宝日利率
        ];
        
        // 用数据库中的配置覆盖默认值
        foreach ($configs as $config) {
            $key = $config['key'];
            if (isset($result[$key])) {
                $value = $config['value'];
                // 布尔值字段特殊处理
                if (in_array($key, ['cny_to_usdt_enabled', 'points_to_cny_enabled'])) {
                    $result[$key] = ($value === '1' || $value === 'true' || $value === true);
                } elseif (is_numeric($value)) {
                    $result[$key] = (float)$value;
                } else {
                    $result[$key] = $value;
                }
            }
        }
        
        success($result);
    }
    
    /**
     * 保存币种配置
     */
    public static function saveConfig() {
        requirePermission('config_edit');
        
        $allowedKeys = [
            'cny_to_usdt_enabled',
            'cny_to_usdt_rate',
            'cny_to_usdt_min',
            'cny_to_usdt_max',
            'cny_to_usdt_fee_rate',
            'points_to_cny_enabled',
            'points_to_cny_rate',
            'points_to_cny_min',
            'usdt_daily_rate',
            'cny_daily_rate',
        ];
        
        db()->beginTransaction();
        
        try {
            foreach ($allowedKeys as $key) {
                $value = input($key);
                if ($value === null) {
                    continue;
                }
                
                // 检查是否存在
                $exists = db()->fetchOne(
                    "SELECT id FROM system_config WHERE `key` = ?",
                    [$key]
                );
                
                if ($exists) {
                    db()->update('system_config', [
                        'value' => (string)$value
                    ], ['key' => $key]);
                } else {
                    db()->insert('system_config', [
                        'key' => $key,
                        'value' => (string)$value,
                        'description' => '',
                        'group' => 'currency'
                    ]);
                }
            }
            
            db()->commit();
            
            logAction('update_currency_config', 'config', '更新币种配置');
            
            success(null, '保存成功');
            
        } catch (Exception $e) {
            db()->rollback();
            error('保存失败');
        }
    }
    
    /**
     * 获取兑换记录列表
     */
    public static function exchangeRecords() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 类型筛选
        $type = input('type'); // cny_to_usdt, points_to_cny
        if ($type) {
            $where[] = "type = ?";
            $params[] = $type;
        }
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        // 用户搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "user_id IN (SELECT id FROM users WHERE username LIKE ? OR phone LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM currency_exchange_records $whereClause",
            $params
        )['count'] ?? 0;
        
        $records = db()->fetchAll(
            "SELECT e.*, u.username, u.real_name
             FROM currency_exchange_records e
             LEFT JOIN users u ON e.user_id = u.id
             $whereClause
             ORDER BY e.created_at DESC
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
     * 审核兑换申请
     */
    public static function reviewExchange() {
        requirePermission('finance_approve');
        
        $id = input('id');
        $action = input('action'); // approve 或 reject
        $remark = input('remark', '');
        
        if (!$id || !in_array($action, ['approve', 'reject'])) {
            error('参数错误');
        }
        
        $record = db()->fetchOne(
            "SELECT * FROM currency_exchange_records WHERE id = ?",
            [$id]
        );
        
        if (!$record) {
            error('记录不存在');
        }
        
        if ($record['status'] != 0) {
            error('该记录已处理');
        }
        
        $admin = getCurrentAdmin();
        
        db()->beginTransaction();
        
        try {
            if ($action === 'approve') {
                // 审核通过，执行兑换
                self::executeExchange($record);
                $status = 1;
            } else {
                // 拒绝，退回资产
                self::refundExchange($record);
                $status = 2;
            }
            
            db()->update('currency_exchange_records', [
                'status' => $status,
                'remark' => $remark,
                'reviewed_by' => $admin['id'],
                'reviewed_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
            
            db()->commit();
            
            logAction('review_exchange', 'finance', [
                'id' => $id,
                'action' => $action,
                'type' => $record['type']
            ]);
            
            success(null, $action === 'approve' ? '审核通过' : '已拒绝');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 执行兑换
     */
    private static function executeExchange($record) {
        $userId = $record['user_id'];
        
        if ($record['type'] === 'cny_to_usdt') {
            // CNY -> USDT
            $user = db()->fetchOne("SELECT usdt_balance FROM users WHERE id = ?", [$userId]);
            $newBalance = (float)($user['usdt_balance'] ?? 0) + (float)$record['to_amount'];
            
            db()->update('users', ['usdt_balance' => $newBalance], ['id' => $userId]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'exchange_in',
                'currency' => 'USDT',
                'amount' => $record['to_amount'],
                'balance_before' => $user['usdt_balance'] ?? 0,
                'balance_after' => $newBalance,
                'remark' => 'CNY兑换USDT',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } elseif ($record['type'] === 'points_to_cny') {
            // 积分 -> CNY
            $user = db()->fetchOne("SELECT balance FROM users WHERE id = ?", [$userId]);
            $newBalance = (float)$user['balance'] + (float)$record['to_amount'];
            
            db()->update('users', ['balance' => $newBalance], ['id' => $userId]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'exchange_in',
                'currency' => 'CNY',
                'amount' => $record['to_amount'],
                'balance_before' => $user['balance'],
                'balance_after' => $newBalance,
                'remark' => '积分兑换人民币',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * 退回兑换资产
     */
    private static function refundExchange($record) {
        $userId = $record['user_id'];
        
        if ($record['type'] === 'cny_to_usdt') {
            // 退回CNY
            $user = db()->fetchOne("SELECT balance FROM users WHERE id = ?", [$userId]);
            $refundAmount = (float)$record['from_amount'] + (float)$record['fee'];
            $newBalance = (float)$user['balance'] + $refundAmount;
            
            db()->update('users', ['balance' => $newBalance], ['id' => $userId]);
            
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'exchange_refund',
                'currency' => 'CNY',
                'amount' => $refundAmount,
                'balance_before' => $user['balance'],
                'balance_after' => $newBalance,
                'remark' => 'CNY兑换USDT退回',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } elseif ($record['type'] === 'points_to_cny') {
            // 退回积分
            $user = db()->fetchOne("SELECT points FROM users WHERE id = ?", [$userId]);
            $newPoints = (int)$user['points'] + (int)$record['from_amount'];
            
            db()->update('users', ['points' => $newPoints], ['id' => $userId]);
        }
    }
    
    /**
     * 用户余额概览（多币种）
     */
    public static function userBalances() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $keyword = input('keyword');
        $where = [];
        $params = [];
        
        if ($keyword) {
            $where[] = "(username LIKE ? OR phone LIKE ? OR real_name LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users $whereClause",
            $params
        )['count'];
        
        $users = db()->fetchAll(
            "SELECT 
                id, username, real_name, phone, vip_level,
                balance as cny_balance,
                COALESCE(usdt_balance, 0) as usdt_balance,
                COALESCE(ribao_cny_balance, 0) as ribao_cny_balance,
                COALESCE(ribao_usdt_balance, 0) as ribao_usdt_balance,
                points,
                created_at
             FROM users
             $whereClause
             ORDER BY (balance + COALESCE(usdt_balance, 0)) DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 统计总额
        $stats = db()->fetchOne(
            "SELECT 
                COALESCE(SUM(balance), 0) as total_cny,
                COALESCE(SUM(usdt_balance), 0) as total_usdt,
                COALESCE(SUM(ribao_cny_balance), 0) as total_ribao_cny,
                COALESCE(SUM(ribao_usdt_balance), 0) as total_ribao_usdt,
                COALESCE(SUM(points), 0) as total_points
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
     * 调整用户特定币种余额
     */
    public static function adjustBalance() {
        requirePermission('user_balance');
        
        $userId = input('user_id');
        $currency = input('currency', 'CNY'); // CNY, USDT, RIBAO_CNY, RIBAO_USDT, POINTS
        $amount = input('amount');
        $type = input('type', 'add'); // add 或 reduce
        $remark = input('remark', '');
        
        if (!$userId || !$amount || !in_array($currency, ['CNY', 'USDT', 'RIBAO_CNY', 'RIBAO_USDT', 'POINTS'])) {
            error('参数错误');
        }
        
        $amount = abs((float)$amount);
        
        // 字段映射
        $fieldMap = [
            'CNY' => 'balance',
            'USDT' => 'usdt_balance',
            'RIBAO_CNY' => 'ribao_cny_balance',
            'RIBAO_USDT' => 'ribao_usdt_balance',
            'POINTS' => 'points'
        ];
        
        $field = $fieldMap[$currency];
        
        $user = db()->fetchOne("SELECT id, $field FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        $balanceBefore = (float)($user[$field] ?? 0);
        
        if ($type === 'reduce') {
            if ($balanceBefore < $amount) {
                error('余额不足');
            }
            $balanceAfter = $balanceBefore - $amount;
            $logAmount = -$amount;
        } else {
            $balanceAfter = $balanceBefore + $amount;
            $logAmount = $amount;
        }
        
        db()->beginTransaction();
        
        try {
            // 更新余额
            db()->update('users', [$field => $balanceAfter], ['id' => $userId]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'admin_adjust',
                'currency' => $currency,
                'amount' => $logAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'remark' => $remark ?: '管理员调整',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            db()->commit();
            
            logAction('adjust_balance', 'user', [
                'user_id' => $userId,
                'currency' => $currency,
                'type' => $type,
                'amount' => $amount,
                'remark' => $remark
            ]);
            
            success([
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
            ], '余额调整成功');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败');
        }
    }
}
