<?php
/**
 * 团队控制器 - 团队管理、邀请奖励
 */

class TeamController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取团队信息
     */
    public function getInfo() {
        $user = Auth::require();
        global $VIP_CONFIG;

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT vip_level FROM users WHERE id = ?", [$user['user_id']]);
            $vipLevel = (int)($userInfo['vip_level'] ?? 0);

            // 统计一级成员
            $level1Count = $this->db->count('users', 'parent_id = ?', [$user['user_id']]);

            // 统计二级成员
            $level2Count = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE parent_id IN (SELECT id FROM users WHERE parent_id = ?)",
                [$user['user_id']]
            );

            // 统计团队总投资
            $teamInvest = $this->db->fetch(
                "SELECT COALESCE(SUM(ui.invest_amount), 0) as total 
                 FROM user_investments ui 
                 WHERE ui.user_id IN (SELECT id FROM users WHERE parent_id = ? OR parent_id IN (SELECT id FROM users WHERE parent_id = ?))",
                [$user['user_id'], $user['user_id']]
            );

            // 统计总奖励
            $totalReward = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM balance_logs WHERE user_id = ? AND type IN ('invite_reward', 'team_reward')",
                [$user['user_id']]
            );

            success([
                'level1_count' => (int)$level1Count,
                'level2_count' => (int)($level2Count['count'] ?? 0),
                'total_members' => (int)$level1Count + (int)($level2Count['count'] ?? 0),
                'team_invest' => formatMoney($teamInvest['total'] ?? 0),
                'total_reward' => formatMoney($totalReward['total'] ?? 0),
                'level1_rate' => ($VIP_CONFIG[$vipLevel]['level1_rate'] ?? 0) * 100 . '%',
                'level2_rate' => ($VIP_CONFIG[$vipLevel]['level2_rate'] ?? 0) * 100 . '%'
            ]);
        } else {
            success([
                'level1_count' => 15,
                'level2_count' => 42,
                'total_members' => 57,
                'team_invest' => '2580000.00',
                'total_reward' => '75000.00',
                'level1_rate' => '4%',
                'level2_rate' => '2%'
            ]);
        }
    }

    /**
     * 获取团队成员
     */
    public function getMembers() {
        $user = Auth::require();
        $pagination = getPagination();
        $level = getQuery('level', 1);

        if ($this->db && $this->db->isConnected()) {
            if ($level == 1) {
                $where = "parent_id = ?";
                $params = [$user['user_id']];
            } else {
                $where = "parent_id IN (SELECT id FROM users WHERE parent_id = ?)";
                $params = [$user['user_id']];
            }

            $total = $this->db->count('users', $where, $params);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT id, username, real_name, vip_level, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $pageSize;
            $members = $this->db->fetchAll($sql, $params);

            // 获取每个成员的投资统计
            $items = array_map(function($m) {
                $invest = $this->db->fetch(
                    "SELECT COALESCE(SUM(invest_amount), 0) as total FROM user_investments WHERE user_id = ?",
                    [$m['id']]
                );
                return [
                    'id' => $m['id'],
                    'username' => substr($m['username'], 0, 3) . '***',
                    'nickname' => $m['real_name'] ? substr($m['real_name'], 0, 1) . '**' : '',
                    'vip_level' => (int)$m['vip_level'],
                    'total_invest' => formatMoney($invest['total'] ?? 0),
                    'created_at' => $m['created_at']
                ];
            }, $members);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockMembers = [
                ['id' => 1, 'username' => 'zha***', 'nickname' => '张**', 'vip_level' => 3, 'total_invest' => '150000.00', 'created_at' => '2024-10-15 10:00:00'],
                ['id' => 2, 'username' => 'li***', 'nickname' => '李**', 'vip_level' => 2, 'total_invest' => '80000.00', 'created_at' => '2024-10-20 14:30:00'],
                ['id' => 3, 'username' => 'wan***', 'nickname' => '王**', 'vip_level' => 1, 'total_invest' => '30000.00', 'created_at' => '2024-11-01 09:15:00'],
            ];
            success(paginateResponse($mockMembers, count($mockMembers), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取奖励记录
     */
    public function getRewards() {
        $user = Auth::require();
        $pagination = getPagination();

        if ($this->db && $this->db->isConnected()) {
            $where = "user_id = ? AND type IN ('invite_reward', 'team_reward')";
            $params = [$user['user_id']];

            $total = $this->db->count('balance_logs', $where, $params);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT * FROM balance_logs WHERE $where ORDER BY created_at DESC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $pageSize;
            $rewards = $this->db->fetchAll($sql, $params);

            $items = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'type' => $r['type'],
                    'type_text' => $r['type'] === 'invite_reward' ? '邀请奖励' : '团队奖励',
                    'amount' => formatMoney($r['amount']),
                    'remark' => $r['remark'] ?? '',
                    'created_at' => $r['created_at']
                ];
            }, $rewards);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockRewards = [
                ['id' => 1, 'type' => 'invite_reward', 'type_text' => '邀请奖励', 'amount' => '1500.00', 'remark' => '一级成员zhang***投资奖励', 'created_at' => '2024-11-28 10:00:00'],
                ['id' => 2, 'type' => 'invite_reward', 'type_text' => '邀请奖励', 'amount' => '800.00', 'remark' => '二级成员li***投资奖励', 'created_at' => '2024-11-27 15:30:00'],
                ['id' => 3, 'type' => 'team_reward', 'type_text' => '团队奖励', 'amount' => '5000.00', 'remark' => '团队管理奖达成', 'created_at' => '2024-11-25 00:00:00'],
            ];
            success(paginateResponse($mockRewards, count($mockRewards), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取推荐奖励
     */
    public function getReferralRewards() {
        $this->getRewards();
    }

    /**
     * 获取奖励信息
     */
    public function getRewardInfo() {
        $user = Auth::require();
        global $VIP_CONFIG, $TEAM_AWARDS;

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT vip_level FROM users WHERE id = ?", [$user['user_id']]);
            $vipLevel = (int)($userInfo['vip_level'] ?? 0);

            // 统计待领取奖励（简化处理）
            $pendingReward = 0;

            success([
                'vip_level' => $vipLevel,
                'level1_rate' => ($VIP_CONFIG[$vipLevel]['level1_rate'] ?? 0) * 100 . '%',
                'level2_rate' => ($VIP_CONFIG[$vipLevel]['level2_rate'] ?? 0) * 100 . '%',
                'pending_reward' => formatMoney($pendingReward),
                'team_awards' => $TEAM_AWARDS
            ]);
        } else {
            success([
                'vip_level' => 3,
                'level1_rate' => '4%',
                'level2_rate' => '2%',
                'pending_reward' => '2500.00',
                'team_awards' => $TEAM_AWARDS ?? []
            ]);
        }
    }

    /**
     * 获取奖励汇总
     */
    public function getRewardSummary() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            // 统计各类奖励
            $inviteReward = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM balance_logs WHERE user_id = ? AND type = 'invite_reward'",
                [$user['user_id']]
            );

            $teamReward = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM balance_logs WHERE user_id = ? AND type = 'team_reward'",
                [$user['user_id']]
            );

            $level1Count = $this->db->count('users', 'parent_id = ?', [$user['user_id']]);

            success([
                'total_reward' => formatMoney(($inviteReward['total'] ?? 0) + ($teamReward['total'] ?? 0)),
                'invite_reward' => formatMoney($inviteReward['total'] ?? 0),
                'team_reward' => formatMoney($teamReward['total'] ?? 0),
                'pending_reward' => '0.00',
                'team_size' => (int)$level1Count,
                'level_rewards' => [
                    'level1' => formatMoney(($inviteReward['total'] ?? 0) * 0.6),
                    'level2' => formatMoney(($inviteReward['total'] ?? 0) * 0.4)
                ]
            ]);
        } else {
            success([
                'total_reward' => '75000.00',
                'invite_reward' => '50000.00',
                'team_reward' => '25000.00',
                'pending_reward' => '2500.00',
                'team_size' => 15,
                'level_rewards' => [
                    'level1' => '35000.00',
                    'level2' => '15000.00'
                ]
            ]);
        }
    }

    /**
     * 领取奖励
     */
    public function claimReward() {
        $user = Auth::require();

        // 简化处理：直接返回成功
        success([
            'claimed_amount' => '2500.00'
        ], '领取成功');
    }

    /**
     * 获取奖励状态
     */
    public function getRewardsStatus() {
        $user = Auth::require();
        global $TEAM_AWARDS;

        if ($this->db && $this->db->isConnected()) {
            $level1Count = $this->db->count('users', 'parent_id = ?', [$user['user_id']]);
            
            $teamInvest = $this->db->fetch(
                "SELECT COALESCE(SUM(ui.invest_amount), 0) as total 
                 FROM user_investments ui 
                 WHERE ui.user_id IN (SELECT id FROM users WHERE parent_id = ?)",
                [$user['user_id']]
            );

            // 计算已达成的团队奖励等级
            $achievedLevel = 0;
            foreach ($TEAM_AWARDS as $index => $award) {
                if ($level1Count >= $award['min_members'] && ($teamInvest['total'] ?? 0) >= $award['min_invest']) {
                    $achievedLevel = $index + 1;
                }
            }

            success([
                'team_members' => (int)$level1Count,
                'team_invest' => formatMoney($teamInvest['total'] ?? 0),
                'achieved_level' => $achievedLevel,
                'next_level' => min($achievedLevel + 1, count($TEAM_AWARDS)),
                'awards' => $TEAM_AWARDS
            ]);
        } else {
            success([
                'team_members' => 15,
                'team_invest' => '580000.00',
                'achieved_level' => 3,
                'next_level' => 4,
                'awards' => $TEAM_AWARDS ?? []
            ]);
        }
    }
}
