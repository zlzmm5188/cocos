<?php
/**
 * 积分控制器 - 积分查询、兑换
 */

class PointsController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取积分余额
     */
    public function getBalance() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT points FROM users WHERE id = ?", [$user['user_id']]);
            
            success([
                'points' => (int)($userInfo['points'] ?? 0),
                'exchange_rate' => POINTS_TO_CNY_RATE,
                'min_exchange' => 100
            ]);
        } else {
            success([
                'points' => 5000,
                'exchange_rate' => POINTS_TO_CNY_RATE,
                'min_exchange' => 100
            ]);
        }
    }

    /**
     * 获取积分记录
     */
    public function getLogs() {
        $user = Auth::require();
        $pagination = getPagination();

        if ($this->db && $this->db->isConnected()) {
            $total = $this->db->count('points_logs', 'user_id = ?', [$user['user_id']]);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT * FROM points_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?, ?";
            $logs = $this->db->fetchAll($sql, [$user['user_id'], $offset, $pageSize]);

            $items = array_map(function($l) {
                return [
                    'id' => $l['id'],
                    'type' => $l['type'],
                    'type_text' => $this->getTypeText($l['type']),
                    'points' => (int)$l['points'],
                    'remark' => $l['remark'] ?? '',
                    'created_at' => $l['created_at']
                ];
            }, $logs);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockLogs = [
                ['id' => 1, 'type' => 'checkin', 'type_text' => '每日签到', 'points' => 20, 'remark' => '签到获得积分', 'created_at' => '2024-11-28 08:00:00'],
                ['id' => 2, 'type' => 'invite', 'type_text' => '邀请奖励', 'points' => 100, 'remark' => '邀请用户注册', 'created_at' => '2024-11-27 15:30:00'],
                ['id' => 3, 'type' => 'exchange', 'type_text' => '积分兑换', 'points' => -500, 'remark' => '兑换人民币', 'created_at' => '2024-11-25 10:00:00'],
                ['id' => 4, 'type' => 'team_award', 'type_text' => '团队奖励', 'points' => 2000, 'remark' => '达成团队管理奖', 'created_at' => '2024-11-20 00:00:00'],
            ];
            success(paginateResponse($mockLogs, count($mockLogs), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 积分兑换人民币
     */
    public function exchange() {
        $user = Auth::require();
        $data = getJsonInput();

        $points = intval($data['points'] ?? 0);

        if ($points <= 0) {
            error('兑换积分必须大于0');
        }

        if ($points < 100) {
            error('最低兑换100积分');
        }

        // 计算兑换金额 (0.5积分 = 1元，即 2积分 = 1元)
        $amount = $points * POINTS_TO_CNY_RATE;

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT points, balance FROM users WHERE id = ?", [$user['user_id']]);
            
            if (($userInfo['points'] ?? 0) < $points) {
                error('积分不足');
            }

            $this->db->beginTransaction();
            try {
                // 扣除积分
                $this->db->update('users', [
                    'points' => $userInfo['points'] - $points,
                    'balance' => floatval($userInfo['balance'] ?? 0) + $amount
                ], 'id = ?', [$user['user_id']]);

                // 记录积分变动
                $this->db->insert('points_logs', [
                    'user_id' => $user['user_id'],
                    'type' => 'exchange',
                    'points' => -$points,
                    'remark' => "兑换人民币 ¥" . formatMoney($amount),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 记录余额变动
                $this->db->insert('balance_logs', [
                    'user_id' => $user['user_id'],
                    'type' => 'points_exchange',
                    'amount' => $amount,
                    'balance_before' => $userInfo['balance'],
                    'balance_after' => floatval($userInfo['balance'] ?? 0) + $amount,
                    'remark' => "积分兑换 {$points}积分",
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success([
                    'points_used' => $points,
                    'amount_received' => formatMoney($amount),
                    'remaining_points' => $userInfo['points'] - $points
                ], '兑换成功');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('兑换失败');
            }
        } else {
            success([
                'points_used' => $points,
                'amount_received' => formatMoney($amount),
                'remaining_points' => 5000 - $points
            ], '兑换成功');
        }
    }

    private function getTypeText($type) {
        $texts = [
            'checkin' => '每日签到',
            'invite' => '邀请奖励',
            'exchange' => '积分兑换',
            'team_award' => '团队奖励',
            'invest' => '投资奖励',
            'activity' => '活动奖励'
        ];
        return $texts[$type] ?? $type;
    }
}
