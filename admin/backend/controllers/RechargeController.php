<?php
/**
 * 充值审核控制器
 */

class RechargeController {
    /**
     * 充值记录列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "r.status = ?";
            $params[] = $status;
        }
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(r.order_no LIKE ? OR u.username LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 日期范围
        $startDate = input('start_date');
        $endDate = input('end_date');
        if ($startDate) {
            $where[] = "r.created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $where[] = "r.created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM recharge_records r 
             LEFT JOIN users u ON r.user_id = u.id
             $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $recharges = db()->fetchAll(
            "SELECT 
                r.id, r.order_no, r.user_id, r.amount, r.payment_method,
                r.certificate, r.status, r.remark, r.created_at, r.reviewed_at,
                u.username, u.real_name, u.phone,
                a.real_name as reviewer_name
             FROM recharge_records r
             LEFT JOIN users u ON r.user_id = u.id
             LEFT JOIN admins a ON r.reviewed_by = a.id
             $whereClause
             ORDER BY r.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 统计信息
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(CASE WHEN status = 0 THEN 1 END) as pending_count,
                COALESCE(SUM(CASE WHEN status = 0 THEN amount ELSE 0 END), 0) as pending_amount,
                COUNT(CASE WHEN status = 1 THEN 1 END) as approved_count,
                COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as approved_amount
             FROM recharge_records"
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $recharges,
            'stats' => $stats
        ]);
    }
    
    /**
     * 充值详情
     */
    public static function detail($id) {
        requireLogin();
        
        $recharge = db()->fetchOne(
            "SELECT 
                r.*,
                u.username, u.real_name, u.phone, u.balance,
                a.real_name as reviewer_name
             FROM recharge_records r
             LEFT JOIN users u ON r.user_id = u.id
             LEFT JOIN admins a ON r.reviewed_by = a.id
             WHERE r.id = ?",
            [$id]
        );
        
        if (!$recharge) {
            error('记录不存在');
        }
        
        // 解析支付信息
        if ($recharge['payment_info']) {
            $recharge['payment_info'] = json_decode($recharge['payment_info'], true);
        }
        
        success($recharge);
    }
    
    /**
     * 审核通过
     */
    public static function approve() {
        requirePermission('recharge_audit');
        
        $id = input('id');
        $remark = input('remark', '');
        
        if (!$id) {
            error('参数错误');
        }
        
        $recharge = db()->fetchOne(
            "SELECT r.*, u.balance FROM recharge_records r 
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.id = ?",
            [$id]
        );
        
        if (!$recharge) {
            error('记录不存在');
        }
        
        if ($recharge['status'] != 0) {
            error('该记录已处理');
        }
        
        $admin = Auth::getCurrentAdmin();
        
        db()->beginTransaction();
        
        try {
            // 更新充值状态
            db()->update('recharge_records', [
                'status' => 1,
                'remark' => $remark,
                'reviewed_by' => $admin['admin_id'],
                'reviewed_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
            
            // 增加用户余额
            $balanceBefore = (float)$recharge['balance'];
            $balanceAfter = $balanceBefore + (float)$recharge['amount'];
            
            db()->update('users', [
                'balance' => $balanceAfter
            ], ['id' => $recharge['user_id']]);
            
            // 记录流水
            db()->insert('balance_logs', [
                'user_id' => $recharge['user_id'],
                'type' => 'recharge',
                'amount' => $recharge['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'ref_id' => $id,
                'remark' => '充值到账',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            db()->commit();
            
            logAction('approve_recharge', 'finance', [
                'recharge_id' => $id,
                'user_id' => $recharge['user_id'],
                'amount' => $recharge['amount']
            ]);
            
            success(null, '审核通过');
            
        } catch (Exception $e) {
            db()->rollback();
            error('操作失败');
        }
    }
    
    /**
     * 审核拒绝
     */
    public static function reject() {
        requirePermission('recharge_audit');
        
        $id = input('id');
        $remark = input('remark', '');
        
        if (!$id) {
            error('参数错误');
        }
        
        if (empty($remark)) {
            error('请填写拒绝原因');
        }
        
        $recharge = db()->fetchOne("SELECT * FROM recharge_records WHERE id = ?", [$id]);
        
        if (!$recharge) {
            error('记录不存在');
        }
        
        if ($recharge['status'] != 0) {
            error('该记录已处理');
        }
        
        $admin = Auth::getCurrentAdmin();
        
        db()->update('recharge_records', [
            'status' => 2,
            'remark' => $remark,
            'reviewed_by' => $admin['admin_id'],
            'reviewed_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        logAction('reject_recharge', 'finance', [
            'recharge_id' => $id,
            'user_id' => $recharge['user_id'],
            'amount' => $recharge['amount'],
            'reason' => $remark
        ]);
        
        success(null, '已拒绝');
    }
}
