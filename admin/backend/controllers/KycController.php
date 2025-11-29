<?php
/**
 * KYC 实名认证审核控制器
 */

class KycController {
    /**
     * KYC 申请列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 状态筛选 (默认显示待审核)
        $status = input('status', '1'); // 1=待审核
        if ($status !== '' && $status !== 'all') {
            $where[] = "kyc_status = ?";
            $params[] = $status;
        }
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(username LIKE ? OR real_name LIKE ? OR id_card LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
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
                id, username, phone, real_name, id_card,
                kyc_status, created_at
             FROM users 
             $whereClause
             ORDER BY 
                CASE WHEN kyc_status = 1 THEN 0 ELSE 1 END,
                id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 隐藏敏感信息
        foreach ($users as &$user) {
            if ($user['id_card']) {
                $user['id_card_masked'] = maskString($user['id_card'], 4, 4);
            }
        }
        
        // 统计
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(CASE WHEN kyc_status = 1 THEN 1 END) as pending,
                COUNT(CASE WHEN kyc_status = 2 THEN 1 END) as approved,
                COUNT(CASE WHEN kyc_status = 3 THEN 1 END) as rejected
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
     * KYC 详情
     */
    public static function detail($userId) {
        requireLogin();
        
        $user = db()->fetchOne(
            "SELECT 
                id, username, phone, real_name, id_card,
                kyc_status, avatar, created_at
             FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            error('用户不存在');
        }
        
        // 这里可以扩展获取KYC图片等信息
        // 假设有一个 user_kyc_info 表存储详细的KYC信息
        // $kycInfo = db()->fetchOne("SELECT * FROM user_kyc_info WHERE user_id = ?", [$userId]);
        
        success($user);
    }
    
    /**
     * 审核通过
     */
    public static function approve() {
        requirePermission('kyc_audit');
        
        $userId = input('user_id');
        $remark = input('remark', '');
        
        if (!$userId) {
            error('参数错误');
        }
        
        $user = db()->fetchOne("SELECT id, kyc_status FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            error('用户不存在');
        }
        
        if ($user['kyc_status'] != 1) {
            error('该用户不在待审核状态');
        }
        
        db()->update('users', [
            'kyc_status' => 2
        ], ['id' => $userId]);
        
        logAction('approve_kyc', 'user', [
            'user_id' => $userId,
            'remark' => $remark
        ]);
        
        success(null, '实名认证已通过');
    }
    
    /**
     * 审核拒绝
     */
    public static function reject() {
        requirePermission('kyc_audit');
        
        $userId = input('user_id');
        $remark = input('remark', '');
        
        if (!$userId) {
            error('参数错误');
        }
        
        if (empty($remark)) {
            error('请填写拒绝原因');
        }
        
        $user = db()->fetchOne("SELECT id, kyc_status FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            error('用户不存在');
        }
        
        if ($user['kyc_status'] != 1) {
            error('该用户不在待审核状态');
        }
        
        db()->update('users', [
            'kyc_status' => 3,
            'real_name' => null,
            'id_card' => null
        ], ['id' => $userId]);
        
        logAction('reject_kyc', 'user', [
            'user_id' => $userId,
            'reason' => $remark
        ]);
        
        success(null, '已拒绝实名认证');
    }
}
