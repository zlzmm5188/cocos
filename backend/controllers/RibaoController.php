<?php
/**
 * 日利宝控制器 - 日利宝储蓄功能
 */

class RibaoController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取日利宝头部信息
     */
    public function getHead() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            // 获取日利宝账户
            $ribaoAccount = $this->db->fetch(
                "SELECT * FROM ribao_accounts WHERE user_id = ?",
                [$user['user_id']]
            );

            if (!$ribaoAccount) {
                // 创建账户
                $this->db->insert('ribao_accounts', [
                    'user_id' => $user['user_id'],
                    'balance_cny' => 0,
                    'balance_usdt' => 0,
                    'total_earned_cny' => 0,
                    'total_earned_usdt' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $ribaoAccount = [
                    'balance_cny' => 0,
                    'balance_usdt' => 0,
                    'total_earned_cny' => 0,
                    'total_earned_usdt' => 0
                ];
            }

            success([
                'balance_cny' => formatMoney($ribaoAccount['balance_cny'] ?? 0),
                'balance_usdt' => formatMoney($ribaoAccount['balance_usdt'] ?? 0),
                'total_earned_cny' => formatMoney($ribaoAccount['total_earned_cny'] ?? 0),
                'total_earned_usdt' => formatMoney($ribaoAccount['total_earned_usdt'] ?? 0),
                'daily_rate' => '0.05%',
                'min_amount' => '1000.00'
            ]);
        } else {
            success([
                'balance_cny' => '50000.00',
                'balance_usdt' => '1000.00',
                'total_earned_cny' => '2500.00',
                'total_earned_usdt' => '50.00',
                'daily_rate' => '0.05%',
                'min_amount' => '1000.00'
            ]);
        }
    }

    /**
     * 获取日利宝信息
     */
    public function getInfo() {
        $this->getHead();
    }

    /**
     * 获取日利宝产品列表
     */
    public function getList() {
        $user = Auth::require();
        $currency = getQuery('currency', 'CNY');

        success([
            'items' => [
                [
                    'id' => 1,
                    'name' => '活期宝',
                    'currency' => $currency,
                    'daily_rate' => '0.05%',
                    'min_amount' => '1000.00',
                    'description' => '随存随取，每日计息'
                ]
            ]
        ]);
    }

    /**
     * 转入日利宝
     */
    public function transferIn() {
        $user = Auth::require();
        $data = getJsonInput();

        $amount = floatval($data['amount'] ?? 0);
        $currency = $data['currency'] ?? 'CNY';

        if ($amount <= 0) {
            error('转入金额必须大于0');
        }

        if ($amount < 1000) {
            error('最低转入金额为1000');
        }

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$user['user_id']]);
            
            $balanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance';
            $balance = floatval($userInfo[$balanceField] ?? 0);

            if ($balance < $amount) {
                error('余额不足');
            }

            $this->db->beginTransaction();
            try {
                // 扣除用户余额
                $this->db->update('users', [
                    $balanceField => $balance - $amount
                ], 'id = ?', [$user['user_id']]);

                // 获取或创建日利宝账户
                $ribaoAccount = $this->db->fetch(
                    "SELECT * FROM ribao_accounts WHERE user_id = ?",
                    [$user['user_id']]
                );

                $ribaoBalanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance_cny';

                if ($ribaoAccount) {
                    $this->db->update('ribao_accounts', [
                        $ribaoBalanceField => floatval($ribaoAccount[$ribaoBalanceField] ?? 0) + $amount
                    ], 'user_id = ?', [$user['user_id']]);
                } else {
                    $this->db->insert('ribao_accounts', [
                        'user_id' => $user['user_id'],
                        $ribaoBalanceField => $amount,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // 记录流水
                $this->db->insert('ribao_records', [
                    'user_id' => $user['user_id'],
                    'type' => 'transfer_in',
                    'amount' => $amount,
                    'currency' => $currency,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success([
                    'amount' => formatMoney($amount),
                    'currency' => $currency
                ], '转入成功');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('转入失败');
            }
        } else {
            success([
                'amount' => formatMoney($amount),
                'currency' => $currency
            ], '转入成功');
        }
    }

    /**
     * 转出日利宝
     */
    public function transferOut() {
        $user = Auth::require();
        $data = getJsonInput();

        $amount = floatval($data['amount'] ?? 0);
        $currency = $data['currency'] ?? 'CNY';

        if ($amount <= 0) {
            error('转出金额必须大于0');
        }

        if ($this->db && $this->db->isConnected()) {
            $ribaoAccount = $this->db->fetch(
                "SELECT * FROM ribao_accounts WHERE user_id = ?",
                [$user['user_id']]
            );

            if (!$ribaoAccount) {
                error('日利宝账户不存在');
            }

            $ribaoBalanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance_cny';
            $ribaoBalance = floatval($ribaoAccount[$ribaoBalanceField] ?? 0);

            if ($ribaoBalance < $amount) {
                error('日利宝余额不足');
            }

            $this->db->beginTransaction();
            try {
                // 扣除日利宝余额
                $this->db->update('ribao_accounts', [
                    $ribaoBalanceField => $ribaoBalance - $amount
                ], 'user_id = ?', [$user['user_id']]);

                // 增加用户余额
                $userInfo = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$user['user_id']]);
                $balanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance';
                
                $this->db->update('users', [
                    $balanceField => floatval($userInfo[$balanceField] ?? 0) + $amount
                ], 'id = ?', [$user['user_id']]);

                // 记录流水
                $this->db->insert('ribao_records', [
                    'user_id' => $user['user_id'],
                    'type' => 'transfer_out',
                    'amount' => $amount,
                    'currency' => $currency,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success([
                    'amount' => formatMoney($amount),
                    'currency' => $currency
                ], '转出成功');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('转出失败');
            }
        } else {
            success([
                'amount' => formatMoney($amount),
                'currency' => $currency
            ], '转出成功');
        }
    }

    /**
     * 获取日利宝记录
     */
    public function getRecords() {
        $user = Auth::require();
        $pagination = getPagination();
        $currency = getQuery('currency');

        if ($this->db && $this->db->isConnected()) {
            $where = "user_id = ?";
            $params = [$user['user_id']];

            if ($currency) {
                $where .= " AND currency = ?";
                $params[] = $currency;
            }

            $total = $this->db->count('ribao_records', $where, $params);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT * FROM ribao_records WHERE $where ORDER BY created_at DESC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $pageSize;
            $records = $this->db->fetchAll($sql, $params);

            $items = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'type' => $r['type'],
                    'type_text' => $r['type'] === 'transfer_in' ? '转入' : ($r['type'] === 'transfer_out' ? '转出' : '收益'),
                    'amount' => formatMoney($r['amount']),
                    'currency' => $r['currency'],
                    'created_at' => $r['created_at']
                ];
            }, $records);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockRecords = [
                ['id' => 1, 'type' => 'transfer_in', 'type_text' => '转入', 'amount' => '50000.00', 'currency' => 'CNY', 'created_at' => '2024-11-20 10:00:00'],
                ['id' => 2, 'type' => 'earning', 'type_text' => '收益', 'amount' => '25.00', 'currency' => 'CNY', 'created_at' => '2024-11-21 00:00:00'],
                ['id' => 3, 'type' => 'earning', 'type_text' => '收益', 'amount' => '25.00', 'currency' => 'CNY', 'created_at' => '2024-11-22 00:00:00'],
                ['id' => 4, 'type' => 'transfer_out', 'type_text' => '转出', 'amount' => '10000.00', 'currency' => 'CNY', 'created_at' => '2024-11-25 14:30:00'],
            ];
            success(paginateResponse($mockRecords, count($mockRecords), $pagination['page'], $pagination['pageSize']));
        }
    }
}
