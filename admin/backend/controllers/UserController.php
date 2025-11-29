<?php
/**
 * 用户管理控制器
 */

class UserController {
    /**
     * 用户列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 搜索条件
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(username LIKE ? OR phone LIKE ? OR real_name LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // VIP等级筛选
        $vipLevel = input('vip_level');
        if ($vipLevel !== null && $vipLevel !== '') {
            $where[] = "vip_level = ?";
            $params[] = $vipLevel;
        }
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        // KYC状态筛选
        $kycStatus = input('kyc_status');
        if ($kycStatus !== null && $kycStatus !== '') {
            $where[] = "kyc_status = ?";
            $params[] = $kycStatus;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $users = db()->fetchAll(
            "SELECT 
                id, username, phone, email, real_name, id_card,
                vip_level, balance, frozen_balance, points,
                invite_code, parent_id, status, kyc_status,
                avatar, login_ip, login_time, created_at
             FROM users 
             $whereClause
             ORDER BY id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 隐藏敏感信息
        foreach ($users as &$user) {
            if ($user['phone']) {
                $user['phone'] = maskString($user['phone'], 3, 4);
            }
            if ($user['id_card']) {
                $user['id_card'] = maskString($user['id_card'], 4, 4);
            }
        }
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $users
        ]);
    }
    
    /**
     * 用户详情
     */
    public static function detail($userId) {
        requireLogin();
        
        $user = db()->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            error('用户不存在');
        }
        
        // 获取投资统计
        $investStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(invest_amount), 0) as total_amount,
                COALESCE(SUM(earned_amount), 0) as total_earned
             FROM user_investments WHERE user_id = ?",
            [$userId]
        );
        
        // 获取充值统计
        $rechargeStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
             FROM recharge_records WHERE user_id = ? AND status = 1",
            [$userId]
        );
        
        // 获取提现统计
        $withdrawStats = db()->fetchOne(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
             FROM withdraw_records WHERE user_id = ? AND status IN (1, 3)",
            [$userId]
        );
        
        // 获取团队人数
        $teamCount = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE parent_id = ?",
            [$userId]
        )['count'];
        
        // 获取上级
        $parent = null;
        if ($user['parent_id']) {
            $parent = db()->fetchOne(
                "SELECT id, username, real_name FROM users WHERE id = ?",
                [$user['parent_id']]
            );
        }
        
        // 隐藏敏感信息
        unset($user['password']);
        
        $user['stats'] = [
            'invest_count' => (int)$investStats['count'],
            'invest_total' => (float)$investStats['total_amount'],
            'earned_total' => (float)$investStats['total_earned'],
            'recharge_count' => (int)$rechargeStats['count'],
            'recharge_total' => (float)$rechargeStats['total_amount'],
            'withdraw_count' => (int)$withdrawStats['count'],
            'withdraw_total' => (float)$withdrawStats['total_amount'],
            'team_count' => (int)$teamCount
        ];
        
        $user['parent'] = $parent;
        
        success($user);
    }
    
    /**
     * 更新用户信息
     */
    public static function update($userId) {
        requirePermission('user_edit');
        
        $user = db()->fetchOne("SELECT id FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        $allowFields = ['real_name', 'phone', 'email', 'status'];
        $data = [];
        
        foreach ($allowFields as $field) {
            $value = input($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }
        
        if (empty($data)) {
            error('没有需要更新的数据');
        }
        
        db()->update('users', $data, ['id' => $userId]);
        
        logAction('update_user', 'user', "更新用户 ID:$userId");
        
        success(null, '更新成功');
    }
    
    /**
     * 调整余额
     */
    public static function adjustBalance() {
        requirePermission('user_balance');
        
        $userId = input('user_id');
        $amount = input('amount');
        $type = input('type', 'add'); // add 或 reduce
        $remark = input('remark', '');
        
        if (!$userId || !$amount) {
            error('参数错误');
        }
        
        $amount = abs((float)$amount);
        
        $user = db()->fetchOne("SELECT id, balance FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        $balanceBefore = (float)$user['balance'];
        
        if ($type === 'reduce') {
            if ($balanceBefore < $amount) {
                error('用户余额不足');
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
            db()->update('users', ['balance' => $balanceAfter], ['id' => $userId]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'admin_adjust',
                'amount' => $logAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'remark' => $remark ?: '管理员调整',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            db()->commit();
            
            logAction('adjust_balance', 'user', [
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'remark' => $remark
            ]);
            
            success([
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
            ], '余额调整成功');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败');
        }
    }
    
    /**
     * 设置VIP等级
     */
    public static function setVip() {
        requirePermission('user_vip');
        
        $userId = input('user_id');
        $vipLevel = input('vip_level');
        
        if (!$userId || $vipLevel === null) {
            error('参数错误');
        }
        
        $user = db()->fetchOne("SELECT id, vip_level FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        db()->update('users', ['vip_level' => $vipLevel], ['id' => $userId]);
        
        logAction('set_vip', 'user', [
            'user_id' => $userId,
            'old_level' => $user['vip_level'],
            'new_level' => $vipLevel
        ]);
        
        success(null, 'VIP等级设置成功');
    }
    
    /**
     * 重置密码
     */
    public static function resetPassword() {
        requirePermission('user_password');
        
        $userId = input('user_id');
        $newPassword = input('new_password', '123456');
        
        if (!$userId) {
            error('参数错误');
        }
        
        $user = db()->fetchOne("SELECT id FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        db()->update('users', [
            'password' => Auth::hashPassword($newPassword)
        ], ['id' => $userId]);
        
        logAction('reset_password', 'user', "重置用户密码 ID:$userId");
        
        success(null, '密码重置成功');
    }
    
    /**
     * 切换用户状态
     */
    public static function toggleStatus($userId) {
        requirePermission('user_status');
        
        $user = db()->fetchOne("SELECT id, status FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            error('用户不存在');
        }
        
        $newStatus = $user['status'] == 1 ? 2 : 1;
        
        db()->update('users', ['status' => $newStatus], ['id' => $userId]);
        
        logAction('toggle_status', 'user', [
            'user_id' => $userId,
            'old_status' => $user['status'],
            'new_status' => $newStatus
        ]);
        
        success(['status' => $newStatus], $newStatus == 1 ? '用户已启用' : '用户已禁用');
    }
}
