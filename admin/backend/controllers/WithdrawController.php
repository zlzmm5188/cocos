<?php
/**
 * 提现审核控制器
 */

class WithdrawController {
    /**
     * 提现记录列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "w.status = ?";
            $params[] = $status;
        }
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(w.order_no LIKE ? OR u.username LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "w.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "w.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM withdraw_records w 
             LEFT JOIN users u ON w.user_id = u.id
             $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $withdrawals = db()->fetchAll(
            "SELECT 
                w.id, w.order_no, w.user_id, w.amount, w.fee, w.actual_amount,
                w.withdraw_method, w.bank_info, w.status, w.remark,
                w.created_at, w.reviewed_at, w.paid_at,
                u.username, u.real_name, u.phone,
                a.real_name as reviewer_name,
                p.real_name as payer_name
             FROM withdraw_records w
             LEFT JOIN users u ON w.user_id = u.id
             LEFT JOIN admins a ON w.reviewed_by = a.id
             LEFT JOIN admins p ON w.paid_by = p.id
             $whereClause
             ORDER BY w.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 解析银行信息
        foreach ($withdrawals as &$w) {
            if ($w['bank_info']) {
                $w['bank_info'] = json_decode($w['bank_info'], true);
            }
        }
        
        // 统计信息
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(CASE WHEN status = 0 THEN 1 END) as pending_count,
                COALESCE(SUM(CASE WHEN status = 0 THEN amount ELSE 0 END), 0) as pending_amount,
                COUNT(CASE WHEN status = 1 THEN 1 END) as approved_count,
                COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as approved_amount,
                COUNT(CASE WHEN status = 3 THEN 1 END) as paid_count,
                COALESCE(SUM(CASE WHEN status = 3 THEN amount ELSE 0 END), 0) as paid_amount
             FROM withdraw_records"
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $withdrawals,
            'stats' => $stats
        ]);
    }
    
    /**
     * 提现详情
     */
    public static function detail($id) {
        requireLogin();
        
        $withdraw = db()->fetchOne(
            "SELECT 
                w.*,
                u.username, u.real_name, u.phone, u.balance, u.vip_level,
                a.real_name as reviewer_name,
                p.real_name as payer_name
             FROM withdraw_records w
             LEFT JOIN users u ON w.user_id = u.id
             LEFT JOIN admins a ON w.reviewed_by = a.id
             LEFT JOIN admins p ON w.paid_by = p.id
             WHERE w.id = ?",
            [$id]
        );
        
        if (!$withdraw) {
            error('记录不存在');
        }
        
        // 解析银行信息
        if ($withdraw['bank_info']) {
            $withdraw['bank_info'] = json_decode($withdraw['bank_info'], true);
        }
        
        success($withdraw);
    }
    
    /**
     * 审核通过
     */
    public static function approve() {
        requirePermission('withdraw_audit');
        
        $id = input('id');
        $remark = input('remark', '');
        
        if (!$id) {
            error('参数错误');
        }
        
        $withdraw = db()->fetchOne("SELECT * FROM withdraw_records WHERE id = ?", [$id]);
        
        if (!$withdraw) {
            error('记录不存在');
        }
        
        if ($withdraw['status'] != 0) {
            error('该记录已处理');
        }
        
        $admin = Auth::getCurrentAdmin();
        
        db()->update('withdraw_records', [
            'status' => 1,
            'remark' => $remark,
            'reviewed_by' => $admin['admin_id'],
            'reviewed_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        logAction('approve_withdraw', 'finance', [
            'withdraw_id' => $id,
            'user_id' => $withdraw['user_id'],
            'amount' => $withdraw['amount']
        ]);
        
        success(null, '审核通过，请尽快打款');
    }
    
    /**
     * 审核拒绝
     */
    public static function reject() {
        requirePermission('withdraw_audit');
        
        $id = input('id');
        $remark = input('remark', '');
        
        if (!$id) {
            error('参数错误');
        }
        
        if (empty($remark)) {
            error('请填写拒绝原因');
        }
        
        $withdraw = db()->fetchOne(
            "SELECT w.*, u.balance, u.frozen_balance FROM withdraw_records w 
             LEFT JOIN users u ON w.user_id = u.id
             WHERE w.id = ?",
            [$id]
        );
        
        if (!$withdraw) {
            error('记录不存在');
        }
        
        if ($withdraw['status'] != 0) {
            error('该记录已处理');
        }
        
        $admin = Auth::getCurrentAdmin();
        
        db()->beginTransaction();
        
        try {
            // 更新提现状态
            db()->update('withdraw_records', [
                'status' => 2,
                'remark' => $remark,
                'reviewed_by' => $admin['admin_id'],
                'reviewed_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
            
            // 返还冻结金额到余额
            $balanceBefore = (float)$withdraw['balance'];
            $balanceAfter = $balanceBefore + (float)$withdraw['amount'];
            $frozenAfter = (float)$withdraw['frozen_balance'] - (float)$withdraw['amount'];
            
            db()->update('users', [
                'balance' => $balanceAfter,
                'frozen_balance' => max(0, $frozenAfter)
            ], ['id' => $withdraw['user_id']]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $withdraw['user_id'],
                'type' => 'withdraw_refund',
                'amount' => $withdraw['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'ref_id' => $id,
                'remark' => '提现申请被拒绝，金额退回',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            db()->commit();
            
            logAction('reject_withdraw', 'finance', [
                'withdraw_id' => $id,
                'user_id' => $withdraw['user_id'],
                'amount' => $withdraw['amount'],
                'reason' => $remark
            ]);
            
            success(null, '已拒绝，金额已退回用户余额');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败');
        }
    }
    
    /**
     * 确认打款
     */
    public static function confirmPay() {
        requirePermission('withdraw_pay');
        
        $id = input('id');
        $remark = input('remark', '');
        
        if (!$id) {
            error('参数错误');
        }
        
        $withdraw = db()->fetchOne(
            "SELECT w.*, u.frozen_balance FROM withdraw_records w 
             LEFT JOIN users u ON w.user_id = u.id
             WHERE w.id = ?",
            [$id]
        );
        
        if (!$withdraw) {
            error('记录不存在');
        }
        
        if ($withdraw['status'] != 1) {
            error('该记录状态异常，请先审核通过');
        }
        
        $admin = Auth::getCurrentAdmin();
        
        db()->beginTransaction();
        
        try {
            // 更新提现状态
            db()->update('withdraw_records', [
                'status' => 3,
                'remark' => $remark ?: $withdraw['remark'],
                'paid_by' => $admin['admin_id'],
                'paid_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
            
            // 扣除冻结金额
            $frozenAfter = (float)$withdraw['frozen_balance'] - (float)$withdraw['amount'];
            
            db()->update('users', [
                'frozen_balance' => max(0, $frozenAfter)
            ], ['id' => $withdraw['user_id']]);
            
            db()->commit();
            
            logAction('confirm_pay', 'finance', [
                'withdraw_id' => $id,
                'user_id' => $withdraw['user_id'],
                'amount' => $withdraw['actual_amount']
            ]);
            
            success(null, '已确认打款');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败');
        }
    }
}
