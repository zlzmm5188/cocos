<?php
/**
 * 团队管理控制器
 */

class TeamController {
    /**
     * 团队列表（顶级用户列表）
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "(username LIKE ? OR real_name LIKE ?)";
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
        
        // 查询数据（带团队人数）
        $users = db()->fetchAll(
            "SELECT 
                u.id, u.username, u.real_name, u.phone, u.vip_level,
                u.invite_code, u.created_at,
                (SELECT COUNT(*) FROM users WHERE parent_id = u.id) as direct_count,
                (SELECT COUNT(*) FROM users t1 
                 WHERE t1.parent_id = u.id 
                 OR t1.parent_id IN (SELECT id FROM users WHERE parent_id = u.id)) as team_count
             FROM users u
             $whereClause
             ORDER BY team_count DESC, u.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $users
        ]);
    }
    
    /**
     * 团队详情
     */
    public static function detail($userId) {
        requireLogin();
        
        $user = db()->fetchOne(
            "SELECT 
                id, username, real_name, phone, vip_level,
                invite_code, parent_id, created_at
             FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            error('用户不存在');
        }
        
        // 直推人数
        $directCount = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE parent_id = ?",
            [$userId]
        )['count'];
        
        // 团队总人数（两级）
        $teamCount = db()->fetchOne(
            "SELECT COUNT(*) as count FROM users t1 
             WHERE t1.parent_id = ? 
             OR t1.parent_id IN (SELECT id FROM users WHERE parent_id = ?)",
            [$userId, $userId]
        )['count'];
        
        // 团队投资总额
        $teamInvest = db()->fetchOne(
            "SELECT COALESCE(SUM(i.invest_amount), 0) as total FROM user_investments i
             WHERE i.user_id IN (
                SELECT id FROM users WHERE parent_id = ? 
                OR parent_id IN (SELECT id FROM users WHERE parent_id = ?)
             )",
            [$userId, $userId]
        )['total'];
        
        // 上级信息
        $parent = null;
        if ($user['parent_id']) {
            $parent = db()->fetchOne(
                "SELECT id, username, real_name FROM users WHERE id = ?",
                [$user['parent_id']]
            );
        }
        
        $user['stats'] = [
            'direct_count' => (int)$directCount,
            'team_count' => (int)$teamCount,
            'team_invest' => (float)$teamInvest
        ];
        $user['parent'] = $parent;
        
        success($user);
    }
    
    /**
     * 团队成员列表
     */
    public static function members($userId) {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        $level = input('level', 1); // 1=直推 2=二级
        
        $offset = ($page - 1) * $pageSize;
        
        if ($level == 1) {
            // 直推成员
            $total = db()->fetchOne(
                "SELECT COUNT(*) as count FROM users WHERE parent_id = ?",
                [$userId]
            )['count'];
            
            $members = db()->fetchAll(
                "SELECT 
                    u.id, u.username, u.real_name, u.phone, u.vip_level,
                    u.created_at,
                    (SELECT COALESCE(SUM(invest_amount), 0) FROM user_investments WHERE user_id = u.id) as invest_total,
                    (SELECT COUNT(*) FROM users WHERE parent_id = u.id) as sub_count
                 FROM users u
                 WHERE u.parent_id = ?
                 ORDER BY u.id DESC
                 LIMIT $pageSize OFFSET $offset",
                [$userId]
            );
        } else {
            // 二级成员
            $total = db()->fetchOne(
                "SELECT COUNT(*) as count FROM users 
                 WHERE parent_id IN (SELECT id FROM users WHERE parent_id = ?)",
                [$userId]
            )['count'];
            
            $members = db()->fetchAll(
                "SELECT 
                    u.id, u.username, u.real_name, u.phone, u.vip_level,
                    u.created_at,
                    p.username as parent_username,
                    (SELECT COALESCE(SUM(invest_amount), 0) FROM user_investments WHERE user_id = u.id) as invest_total
                 FROM users u
                 LEFT JOIN users p ON u.parent_id = p.id
                 WHERE u.parent_id IN (SELECT id FROM users WHERE parent_id = ?)
                 ORDER BY u.id DESC
                 LIMIT $pageSize OFFSET $offset",
                [$userId]
            );
        }
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'level' => $level,
            'items' => $members
        ]);
    }
}
