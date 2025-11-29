<?php
/**
 * 订单控制器 - 投资订单管理
 */

class OrderController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取订单列表
     */
    public function getList() {
        $user = Auth::require();
        $pagination = getPagination();
        $status = getQuery('status');

        if ($this->db && $this->db->isConnected()) {
            $where = "ui.user_id = ?";
            $params = [$user['user_id']];

            if ($status !== null && $status !== '') {
                $where .= " AND ui.status = ?";
                $params[] = $status;
            }

            $total = $this->db->count('user_investments ui', $where, $params);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT ui.*, p.title as project_title, p.cover_image, p.currency 
                    FROM user_investments ui 
                    LEFT JOIN projects p ON ui.project_id = p.id 
                    WHERE $where 
                    ORDER BY ui.created_at DESC 
                    LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $pageSize;
            $orders = $this->db->fetchAll($sql, $params);

            $items = array_map(function($o) {
                return [
                    'id' => $o['id'],
                    'order_no' => $o['order_no'],
                    'project_id' => $o['project_id'],
                    'project_title' => $o['project_title'] ?? '',
                    'cover_image' => $o['cover_image'] ?? '',
                    'currency' => $o['currency'] ?? 'CNY',
                    'invest_amount' => formatMoney($o['invest_amount']),
                    'daily_rate' => ($o['daily_rate'] * 100) . '%',
                    'total_days' => (int)$o['total_days'],
                    'earned_amount' => formatMoney($o['earned_amount'] ?? 0),
                    'total_return' => formatMoney($o['total_return'] ?? 0),
                    'start_date' => $o['start_date'],
                    'end_date' => $o['end_date'],
                    'status' => (int)$o['status'],
                    'status_text' => $this->getStatusText($o['status']),
                    'created_at' => $o['created_at']
                ];
            }, $orders);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            // Mock数据
            $mockOrders = [
                [
                    'id' => 1,
                    'order_no' => 'ORD2024112800001',
                    'project_id' => 1,
                    'project_title' => '稳健理财7天',
                    'cover_image' => '/img/project1.jpg',
                    'currency' => 'CNY',
                    'invest_amount' => '50000.00',
                    'daily_rate' => '0.12%',
                    'total_days' => 7,
                    'earned_amount' => '420.00',
                    'total_return' => '420.00',
                    'start_date' => '2024-11-21',
                    'end_date' => '2024-11-28',
                    'status' => 2,
                    'status_text' => '已完成',
                    'created_at' => '2024-11-21 10:30:00'
                ],
                [
                    'id' => 2,
                    'order_no' => 'ORD2024112800002',
                    'project_id' => 2,
                    'project_title' => '进取理财30天',
                    'cover_image' => '/img/project2.jpg',
                    'currency' => 'CNY',
                    'invest_amount' => '100000.00',
                    'daily_rate' => '0.15%',
                    'total_days' => 30,
                    'earned_amount' => '1500.00',
                    'total_return' => '4500.00',
                    'start_date' => '2024-11-25',
                    'end_date' => '2024-12-25',
                    'status' => 1,
                    'status_text' => '进行中',
                    'created_at' => '2024-11-25 14:20:00'
                ]
            ];

            success(paginateResponse($mockOrders, count($mockOrders), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取我的投资
     */
    public function getMyInvestments() {
        $this->getList();
    }

    /**
     * 获取收益记录
     */
    public function getEarnings() {
        $user = Auth::require();
        $pagination = getPagination();

        if ($this->db && $this->db->isConnected()) {
            $total = $this->db->count('earnings_records', 'user_id = ?', [$user['user_id']]);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT er.*, ui.order_no, p.title as project_title 
                    FROM earnings_records er 
                    LEFT JOIN user_investments ui ON er.investment_id = ui.id 
                    LEFT JOIN projects p ON ui.project_id = p.id 
                    WHERE er.user_id = ? 
                    ORDER BY er.earn_date DESC 
                    LIMIT ?, ?";
            $earnings = $this->db->fetchAll($sql, [$user['user_id'], $offset, $pageSize]);

            $items = array_map(function($e) {
                return [
                    'id' => $e['id'],
                    'order_no' => $e['order_no'] ?? '',
                    'project_title' => $e['project_title'] ?? '',
                    'amount' => formatMoney($e['amount']),
                    'earn_date' => $e['earn_date'],
                    'status' => (int)$e['status'],
                    'created_at' => $e['created_at']
                ];
            }, $earnings);

            // 统计总收益
            $totalEarned = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM earnings_records WHERE user_id = ?",
                [$user['user_id']]
            );

            success([
                'items' => $items,
                'total' => $total,
                'page' => $pagination['page'],
                'pageSize' => $pagination['pageSize'],
                'total_earned' => formatMoney($totalEarned['total'] ?? 0)
            ]);
        } else {
            $mockEarnings = [
                ['id' => 1, 'order_no' => 'ORD2024112800001', 'project_title' => '稳健理财7天', 'amount' => '60.00', 'earn_date' => '2024-11-28', 'status' => 1, 'created_at' => '2024-11-28 00:00:00'],
                ['id' => 2, 'order_no' => 'ORD2024112800001', 'project_title' => '稳健理财7天', 'amount' => '60.00', 'earn_date' => '2024-11-27', 'status' => 1, 'created_at' => '2024-11-27 00:00:00'],
                ['id' => 3, 'order_no' => 'ORD2024112800002', 'project_title' => '进取理财30天', 'amount' => '150.00', 'earn_date' => '2024-11-28', 'status' => 1, 'created_at' => '2024-11-28 00:00:00'],
            ];

            success([
                'items' => $mockEarnings,
                'total' => count($mockEarnings),
                'page' => 1,
                'pageSize' => 20,
                'total_earned' => '35000.00'
            ]);
        }
    }

    /**
     * 创建投资订单
     */
    public function create() {
        $user = Auth::require();
        $data = getJsonInput();

        $projectId = $data['project_id'] ?? $data['projectId'] ?? null;
        $amount = floatval($data['amount'] ?? 0);

        if (!$projectId) {
            error('请选择投资项目');
        }

        if ($amount <= 0) {
            error('投资金额必须大于0');
        }

        if ($this->db && $this->db->isConnected()) {
            // 获取项目信息
            $project = $this->db->fetch("SELECT * FROM projects WHERE id = ? AND status = 1", [$projectId]);
            if (!$project) {
                error('项目不存在或已下架');
            }

            // 检查投资金额范围
            if ($amount < $project['min_invest']) {
                error('投资金额不能低于' . formatMoney($project['min_invest']));
            }
            if ($project['max_invest'] > 0 && $amount > $project['max_invest']) {
                error('投资金额不能超过' . formatMoney($project['max_invest']));
            }

            // 获取用户信息
            $userInfo = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$user['user_id']]);
            
            // 根据币种检查余额
            $currency = $project['currency'] ?? 'CNY';
            $balanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance';
            $balance = floatval($userInfo[$balanceField] ?? 0);

            if ($balance < $amount) {
                error('余额不足');
            }

            // 计算收益
            $dailyRate = floatval($project['daily_rate']);
            $totalDays = intval($project['total_days']);
            $totalReturn = $amount * $dailyRate * $totalDays;

            $this->db->beginTransaction();
            try {
                // 扣除余额
                $this->db->update('users', [
                    $balanceField => $balance - $amount
                ], 'id = ?', [$user['user_id']]);

                // 创建订单
                $orderNo = generateOrderNo('INV');
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d', strtotime("+{$totalDays} days"));

                $orderId = $this->db->insert('user_investments', [
                    'order_no' => $orderNo,
                    'user_id' => $user['user_id'],
                    'project_id' => $projectId,
                    'invest_amount' => $amount,
                    'daily_rate' => $dailyRate,
                    'total_days' => $totalDays,
                    'total_return' => $totalReturn,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 更新项目统计
                $this->db->query(
                    "UPDATE projects SET invest_count = invest_count + 1, total_invested = total_invested + ? WHERE id = ?",
                    [$amount, $projectId]
                );

                // 记录资金流水
                $this->db->insert('balance_logs', [
                    'user_id' => $user['user_id'],
                    'type' => 'invest',
                    'amount' => -$amount,
                    'balance_before' => $balance,
                    'balance_after' => $balance - $amount,
                    'ref_id' => $orderId,
                    'remark' => "投资订单 $orderNo",
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success([
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'invest_amount' => formatMoney($amount),
                    'total_return' => formatMoney($totalReturn),
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ], '投资成功');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('投资失败: ' . $e->getMessage());
            }
        } else {
            // Mock模式
            $orderNo = generateOrderNo('INV');
            success([
                'order_id' => rand(1000, 9999),
                'order_no' => $orderNo,
                'invest_amount' => formatMoney($amount),
                'total_return' => formatMoney($amount * 0.0012 * 7),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days'))
            ], '投资成功');
        }
    }

    /**
     * 获取体验金状态
     */
    public function getTrialFundStatus() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            // 检查是否已领取
            $claimed = $this->db->fetch(
                "SELECT id FROM trial_fund_logs WHERE user_id = ?",
                [$user['user_id']]
            );

            success([
                'claimed' => !empty($claimed),
                'amount' => '1000.00',
                'expires_days' => 7
            ]);
        } else {
            success([
                'claimed' => false,
                'amount' => '1000.00',
                'expires_days' => 7
            ]);
        }
    }

    /**
     * 领取体验金
     */
    public function claimTrialFund() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            // 检查是否已领取
            $claimed = $this->db->fetch(
                "SELECT id FROM trial_fund_logs WHERE user_id = ?",
                [$user['user_id']]
            );
            if ($claimed) {
                error('您已领取过体验金');
            }

            $amount = 1000;
            $this->db->insert('trial_fund_logs', [
                'user_id' => $user['user_id'],
                'amount' => $amount,
                'expire_date' => date('Y-m-d', strtotime('+7 days')),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            success([
                'amount' => formatMoney($amount),
                'expire_date' => date('Y-m-d', strtotime('+7 days'))
            ], '领取成功');
        } else {
            success([
                'amount' => '1000.00',
                'expire_date' => date('Y-m-d', strtotime('+7 days'))
            ], '领取成功');
        }
    }

    /**
     * 获取体验金订单
     */
    public function getTrialFundOrders() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $orders = $this->db->fetchAll(
                "SELECT * FROM trial_fund_logs WHERE user_id = ? ORDER BY created_at DESC",
                [$user['user_id']]
            );

            success(['items' => $orders]);
        } else {
            success(['items' => []]);
        }
    }

    /**
     * 获取状态文本
     */
    private function getStatusText($status) {
        $texts = [
            1 => '进行中',
            2 => '已完成',
            3 => '已取消'
        ];
        return $texts[$status] ?? '未知';
    }
}
