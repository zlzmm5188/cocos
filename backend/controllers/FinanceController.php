<?php
/**
 * 财务控制器 - 充值、提现、流水
 */

class FinanceController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取充值记录
     */
    public function getRechargeList() {
        $user = Auth::require();
        $pagination = getPagination();
        $status = getQuery('status');

        if ($this->db && $this->db->isConnected()) {
            $where = "user_id = ?";
            $params = [$user['user_id']];

            if ($status !== null && $status !== '') {
                $where .= " AND status = ?";
                $params[] = $status;
            }

            $total = $this->db->count('recharge_records', $where, $params);
            
            $sql = "SELECT * FROM recharge_records WHERE $where ORDER BY created_at DESC LIMIT {$pagination['offset']}, {$pagination['pageSize']}";
            $records = $this->db->fetchAll($sql, $params);

            $items = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'order_no' => $r['order_no'],
                    'amount' => formatMoney($r['amount']),
                    'payment_method' => $r['payment_method'],
                    'status' => (int)$r['status'],
                    'status_text' => $this->getRechargeStatusText($r['status']),
                    'remark' => $r['remark'] ?? '',
                    'created_at' => $r['created_at']
                ];
            }, $records);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockRecords = [
                ['id' => 1, 'order_no' => 'RCH2024112800001', 'amount' => '50000.00', 'payment_method' => 'bank', 'status' => 1, 'status_text' => '已完成', 'remark' => '', 'created_at' => '2024-11-28 10:00:00'],
                ['id' => 2, 'order_no' => 'RCH2024112800002', 'amount' => '10000.00', 'payment_method' => 'usdt', 'status' => 0, 'status_text' => '待审核', 'remark' => '', 'created_at' => '2024-11-28 14:30:00'],
            ];
            success(paginateResponse($mockRecords, count($mockRecords), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 创建充值订单
     */
    public function createRecharge() {
        $user = Auth::require();
        $data = getJsonInput();

        $amount = floatval($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? $data['paymentMethod'] ?? 'bank';
        $certificate = $data['certificate'] ?? '';

        if ($amount <= 0) {
            error('充值金额必须大于0');
        }

        if ($this->db && $this->db->isConnected()) {
            $orderNo = generateOrderNo('RCH');

            $orderId = $this->db->insert('recharge_records', [
                'order_no' => $orderNo,
                'user_id' => $user['user_id'],
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'certificate' => $certificate,
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            success([
                'order_id' => $orderId,
                'order_no' => $orderNo,
                'amount' => formatMoney($amount),
                'payment_method' => $paymentMethod
            ], '充值申请已提交');
        } else {
            $orderNo = generateOrderNo('RCH');
            success([
                'order_id' => rand(1000, 9999),
                'order_no' => $orderNo,
                'amount' => formatMoney($amount),
                'payment_method' => $paymentMethod
            ], '充值申请已提交');
        }
    }

    /**
     * 获取提现记录
     */
    public function getWithdrawList() {
        $user = Auth::require();
        $pagination = getPagination();
        $status = getQuery('status');

        if ($this->db && $this->db->isConnected()) {
            $where = "user_id = ?";
            $params = [$user['user_id']];

            if ($status !== null && $status !== '') {
                $where .= " AND status = ?";
                $params[] = $status;
            }

            $total = $this->db->count('withdraw_records', $where, $params);
            
            $sql = "SELECT * FROM withdraw_records WHERE $where ORDER BY created_at DESC LIMIT {$pagination['offset']}, {$pagination['pageSize']}";
            $records = $this->db->fetchAll($sql, $params);

            $items = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'order_no' => $r['order_no'],
                    'amount' => formatMoney($r['amount']),
                    'fee' => formatMoney($r['fee'] ?? 0),
                    'actual_amount' => formatMoney($r['actual_amount']),
                    'withdraw_method' => $r['withdraw_method'],
                    'status' => (int)$r['status'],
                    'status_text' => $this->getWithdrawStatusText($r['status']),
                    'remark' => $r['remark'] ?? '',
                    'created_at' => $r['created_at']
                ];
            }, $records);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockRecords = [
                ['id' => 1, 'order_no' => 'WDR2024112800001', 'amount' => '20000.00', 'fee' => '20.00', 'actual_amount' => '19980.00', 'withdraw_method' => 'bank', 'status' => 3, 'status_text' => '已打款', 'remark' => '', 'created_at' => '2024-11-27 10:00:00'],
                ['id' => 2, 'order_no' => 'WDR2024112800002', 'amount' => '5000.00', 'fee' => '5.00', 'actual_amount' => '4995.00', 'withdraw_method' => 'usdt', 'status' => 1, 'status_text' => '审核通过', 'remark' => '', 'created_at' => '2024-11-28 11:30:00'],
            ];
            success(paginateResponse($mockRecords, count($mockRecords), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 创建提现申请
     */
    public function createWithdraw() {
        $user = Auth::require();
        $data = getJsonInput();

        $amount = floatval($data['amount'] ?? 0);
        $withdrawMethod = $data['withdraw_method'] ?? $data['withdrawMethod'] ?? 'bank';
        $bankCardId = $data['bank_card_id'] ?? $data['bankCardId'] ?? null;
        $payPassword = $data['pay_password'] ?? $data['payPassword'] ?? '';

        if ($amount <= 0) {
            error('提现金额必须大于0');
        }

        if ($this->db && $this->db->isConnected()) {
            // 获取用户信息
            $userInfo = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$user['user_id']]);
            
            // 检查余额
            $balanceField = $withdrawMethod === 'usdt' ? 'balance_usdt' : 'balance';
            $balance = floatval($userInfo[$balanceField] ?? 0);

            if ($balance < $amount) {
                error('余额不足');
            }

            // 计算手续费（示例：0.1%）
            $feeRate = 0.001;
            $fee = $amount * $feeRate;
            $actualAmount = $amount - $fee;

            // 获取银行卡/USDT地址信息
            $bankInfo = null;
            if ($bankCardId) {
                $card = $this->db->fetch("SELECT * FROM bank_cards WHERE id = ? AND user_id = ?", [$bankCardId, $user['user_id']]);
                if ($card) {
                    $bankInfo = json_encode([
                        'bank_name' => $card['bank_name'],
                        'card_number' => $card['card_number'],
                        'card_holder' => $card['card_holder']
                    ]);
                }
            }

            $this->db->beginTransaction();
            try {
                // 冻结金额
                $this->db->update('users', [
                    $balanceField => $balance - $amount,
                    'frozen_balance' => floatval($userInfo['frozen_balance'] ?? 0) + $amount
                ], 'id = ?', [$user['user_id']]);

                // 创建提现记录
                $orderNo = generateOrderNo('WDR');
                $orderId = $this->db->insert('withdraw_records', [
                    'order_no' => $orderNo,
                    'user_id' => $user['user_id'],
                    'amount' => $amount,
                    'fee' => $fee,
                    'actual_amount' => $actualAmount,
                    'withdraw_method' => $withdrawMethod,
                    'bank_info' => $bankInfo,
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 记录资金流水
                $this->db->insert('balance_logs', [
                    'user_id' => $user['user_id'],
                    'type' => 'withdraw',
                    'amount' => -$amount,
                    'balance_before' => $balance,
                    'balance_after' => $balance - $amount,
                    'ref_id' => $orderId,
                    'remark' => "提现申请 $orderNo",
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success([
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'amount' => formatMoney($amount),
                    'fee' => formatMoney($fee),
                    'actual_amount' => formatMoney($actualAmount)
                ], '提现申请已提交');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('提现失败');
            }
        } else {
            $orderNo = generateOrderNo('WDR');
            $fee = $amount * 0.001;
            success([
                'order_id' => rand(1000, 9999),
                'order_no' => $orderNo,
                'amount' => formatMoney($amount),
                'fee' => formatMoney($fee),
                'actual_amount' => formatMoney($amount - $fee)
            ], '提现申请已提交');
        }
    }

    /**
     * 获取钱包流水
     */
    public function getWalletLogs() {
        $user = Auth::require();
        $pagination = getPagination();
        $type = getQuery('type');

        if ($this->db && $this->db->isConnected()) {
            $where = "user_id = ?";
            $params = [$user['user_id']];

            if ($type) {
                $where .= " AND type = ?";
                $params[] = $type;
            }

            $total = $this->db->count('balance_logs', $where, $params);
            
            $sql = "SELECT * FROM balance_logs WHERE $where ORDER BY created_at DESC LIMIT {$pagination['offset']}, {$pagination['pageSize']}";
            $logs = $this->db->fetchAll($sql, $params);

            $items = array_map(function($l) {
                return [
                    'id' => $l['id'],
                    'type' => $l['type'],
                    'type_text' => $this->getLogTypeText($l['type']),
                    'amount' => formatMoney($l['amount']),
                    'balance_before' => formatMoney($l['balance_before']),
                    'balance_after' => formatMoney($l['balance_after']),
                    'remark' => $l['remark'] ?? '',
                    'created_at' => $l['created_at']
                ];
            }, $logs);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockLogs = [
                ['id' => 1, 'type' => 'recharge', 'type_text' => '充值', 'amount' => '50000.00', 'balance_before' => '0.00', 'balance_after' => '50000.00', 'remark' => '充值成功', 'created_at' => '2024-11-28 10:00:00'],
                ['id' => 2, 'type' => 'invest', 'type_text' => '投资', 'amount' => '-10000.00', 'balance_before' => '50000.00', 'balance_after' => '40000.00', 'remark' => '投资稳健理财7天', 'created_at' => '2024-11-28 11:00:00'],
                ['id' => 3, 'type' => 'earning', 'type_text' => '收益', 'amount' => '120.00', 'balance_before' => '40000.00', 'balance_after' => '40120.00', 'remark' => '每日收益', 'created_at' => '2024-11-28 00:00:00'],
            ];
            success(paginateResponse($mockLogs, count($mockLogs), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取USDT汇率
     */
    public function getUsdtRate() {
        success([
            'rate' => CNY_TO_USDT_RATE,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function getRechargeStatusText($status) {
        $texts = [0 => '待审核', 1 => '已完成', 2 => '已拒绝'];
        return $texts[$status] ?? '未知';
    }

    private function getWithdrawStatusText($status) {
        $texts = [0 => '待审核', 1 => '审核通过', 2 => '已拒绝', 3 => '已打款'];
        return $texts[$status] ?? '未知';
    }

    private function getLogTypeText($type) {
        $texts = [
            'recharge' => '充值',
            'withdraw' => '提现',
            'invest' => '投资',
            'earning' => '收益',
            'refund' => '退款',
            'invite_reward' => '邀请奖励',
            'team_reward' => '团队奖励'
        ];
        return $texts[$type] ?? $type;
    }
}
