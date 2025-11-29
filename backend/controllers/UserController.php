<?php
/**
 * 用户控制器 - 用户信息、银行卡、签到等
 */

class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取用户信息
     */
    public function getInfo() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$user['user_id']]);
            if (!$userInfo) {
                error('用户不存在');
            }

            success([
                'id' => $userInfo['id'],
                'username' => $userInfo['username'],
                'nickname' => $userInfo['real_name'] ?? $userInfo['username'],
                'phone' => maskPhone($userInfo['phone'] ?? ''),
                'email' => $userInfo['email'] ?? '',
                'avatar' => $userInfo['avatar'] ?? '',
                'vip_level' => (int)($userInfo['vip_level'] ?? 0),
                'balance_cny' => formatMoney($userInfo['balance'] ?? 0),
                'balance_usdt' => formatMoney($userInfo['balance_usdt'] ?? 0),
                'frozen_balance' => formatMoney($userInfo['frozen_balance'] ?? 0),
                'points' => (int)($userInfo['points'] ?? 0),
                'invite_code' => $userInfo['invite_code'] ?? '',
                'kyc_status' => (int)($userInfo['kyc_status'] ?? 0),
                'real_name' => $userInfo['real_name'] ?? '',
                'created_at' => $userInfo['created_at'] ?? ''
            ]);
        } else {
            // Mock数据
            success([
                'id' => $user['user_id'],
                'username' => $user['username'],
                'nickname' => 'Test User',
                'phone' => '138****8888',
                'email' => '',
                'avatar' => '',
                'vip_level' => 3,
                'balance_cny' => '1000000.00',
                'balance_usdt' => '10000.00',
                'frozen_balance' => '0.00',
                'points' => 5000,
                'invite_code' => 'ABC12345',
                'kyc_status' => 2,
                'real_name' => '测试用户',
                'created_at' => '2024-01-01 00:00:00'
            ]);
        }
    }

    /**
     * 更新用户信息
     */
    public function update() {
        $user = Auth::require();
        $data = getJsonInput();

        $allowedFields = ['nickname', 'avatar', 'email'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            error('没有需要更新的数据');
        }

        if ($this->db && $this->db->isConnected()) {
            $this->db->update('users', $updateData, 'id = ?', [$user['user_id']]);
        }

        success(null, '更新成功');
    }

    /**
     * 获取VIP进度
     */
    public function getVipProgress() {
        $user = Auth::require();
        global $VIP_CONFIG;

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT vip_level, balance FROM users WHERE id = ?", [$user['user_id']]);
            
            // 获取累计投资金额
            $totalInvest = $this->db->fetch(
                "SELECT COALESCE(SUM(invest_amount), 0) as total FROM user_investments WHERE user_id = ?",
                [$user['user_id']]
            );

            $currentLevel = (int)($userInfo['vip_level'] ?? 0);
            $nextLevel = min($currentLevel + 1, 8);
            $totalAmount = (float)($totalInvest['total'] ?? 0);
            $nextAmount = $VIP_CONFIG[$nextLevel]['min_invest'] ?? 0;
            $progress = $nextAmount > 0 ? min(100, round($totalAmount / $nextAmount * 100, 2)) : 100;

            success([
                'current_level' => $currentLevel,
                'next_level' => $nextLevel,
                'current_amount' => formatMoney($totalAmount),
                'next_amount' => formatMoney($nextAmount),
                'progress' => $progress,
                'extra_rate' => ($VIP_CONFIG[$currentLevel]['extra_rate'] ?? 0) * 100 . '%',
                'level1_rate' => ($VIP_CONFIG[$currentLevel]['level1_rate'] ?? 0) * 100 . '%',
                'level2_rate' => ($VIP_CONFIG[$currentLevel]['level2_rate'] ?? 0) * 100 . '%'
            ]);
        } else {
            success([
                'current_level' => 3,
                'next_level' => 4,
                'current_amount' => '250000.00',
                'next_amount' => '800000.00',
                'progress' => 31.25,
                'extra_rate' => '0.12%',
                'level1_rate' => '4%',
                'level2_rate' => '2%'
            ]);
        }
    }

    /**
     * 获取邀请信息
     */
    public function getInviteInfo() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT invite_code FROM users WHERE id = ?", [$user['user_id']]);
            
            // 获取邀请人数
            $inviteCount = $this->db->count('users', 'parent_id = ?', [$user['user_id']]);
            
            // 获取邀请奖励
            $rewards = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM balance_logs WHERE user_id = ? AND type = 'invite_reward'",
                [$user['user_id']]
            );

            success([
                'invite_code' => $userInfo['invite_code'] ?? '',
                'invite_url' => 'https://your-domain.com/invite-register.html?code=' . ($userInfo['invite_code'] ?? ''),
                'invite_count' => (int)$inviteCount,
                'total_rewards' => formatMoney($rewards['total'] ?? 0)
            ]);
        } else {
            success([
                'invite_code' => 'ABC12345',
                'invite_url' => 'https://your-domain.com/invite-register.html?code=ABC12345',
                'invite_count' => 15,
                'total_rewards' => '50000.00'
            ]);
        }
    }

    /**
     * 获取银行卡列表
     */
    public function getBankList() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $cards = $this->db->fetchAll(
                "SELECT * FROM bank_cards WHERE user_id = ? AND card_type = 'bank' AND status = 1 ORDER BY is_default DESC, id DESC",
                [$user['user_id']]
            );

            $items = array_map(function($card) {
                return [
                    'id' => $card['id'],
                    'bank_name' => $card['bank_name'],
                    'card_no' => maskBankCard($card['card_number']),
                    'card_holder' => $card['card_holder'],
                    'bank_branch' => $card['bank_branch'] ?? '',
                    'is_default' => (int)$card['is_default']
                ];
            }, $cards);

            success(['items' => $items]);
        } else {
            success([
                'items' => [
                    [
                        'id' => 1,
                        'bank_name' => '中国银行',
                        'card_no' => '6222****1234',
                        'card_holder' => '张**',
                        'bank_branch' => '北京分行',
                        'is_default' => 1
                    ]
                ]
            ]);
        }
    }

    /**
     * 绑定银行卡
     */
    public function bindBankCard() {
        $user = Auth::require();
        $data = getJsonInput();

        $bankName = trim($data['bank_name'] ?? $data['bankName'] ?? '');
        $cardNumber = trim($data['card_number'] ?? $data['cardNumber'] ?? $data['card_no'] ?? '');
        $cardHolder = trim($data['card_holder'] ?? $data['cardHolder'] ?? $data['real_name'] ?? '');
        $bankBranch = trim($data['bank_branch'] ?? $data['bankBranch'] ?? '');

        if (empty($bankName)) {
            error('请选择银行');
        }

        if (empty($cardNumber)) {
            error('请输入卡号');
        }

        if (empty($cardHolder)) {
            error('请输入持卡人姓名');
        }

        if ($this->db && $this->db->isConnected()) {
            // 检查是否已绑定
            $exists = $this->db->fetch(
                "SELECT id FROM bank_cards WHERE user_id = ? AND card_number = ?",
                [$user['user_id'], $cardNumber]
            );
            if ($exists) {
                error('该卡号已绑定');
            }

            // 第一张卡设为默认
            $count = $this->db->count('bank_cards', "user_id = ? AND card_type = 'bank'", [$user['user_id']]);

            $cardId = $this->db->insert('bank_cards', [
                'user_id' => $user['user_id'],
                'card_type' => 'bank',
                'bank_name' => $bankName,
                'card_number' => $cardNumber,
                'card_holder' => $cardHolder,
                'bank_branch' => $bankBranch,
                'is_default' => $count == 0 ? 1 : 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            success([
                'id' => $cardId,
                'bank_name' => $bankName,
                'card_no' => maskBankCard($cardNumber)
            ], '绑定成功');
        } else {
            success([
                'id' => rand(100, 999),
                'bank_name' => $bankName,
                'card_no' => maskBankCard($cardNumber)
            ], '绑定成功');
        }
    }

    /**
     * 获取USDT地址列表
     */
    public function getUsdtAddressList() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $addresses = $this->db->fetchAll(
                "SELECT * FROM bank_cards WHERE user_id = ? AND card_type = 'usdt' AND status = 1 ORDER BY is_default DESC, id DESC",
                [$user['user_id']]
            );

            $items = array_map(function($addr) {
                return [
                    'id' => $addr['id'],
                    'address' => $addr['card_number'],
                    'chain' => $addr['bank_name'] ?? 'TRC20',
                    'is_default' => (int)$addr['is_default']
                ];
            }, $addresses);

            success(['items' => $items]);
        } else {
            success([
                'items' => [
                    [
                        'id' => 1,
                        'address' => 'TXyz...abc',
                        'chain' => 'TRC20',
                        'is_default' => 1
                    ]
                ]
            ]);
        }
    }

    /**
     * 绑定USDT地址
     */
    public function bindUsdtAddress() {
        $user = Auth::require();
        $data = getJsonInput();

        $address = trim($data['address'] ?? '');
        $chain = trim($data['chain'] ?? 'TRC20');

        if (empty($address)) {
            error('请输入USDT地址');
        }

        if ($this->db && $this->db->isConnected()) {
            $exists = $this->db->fetch(
                "SELECT id FROM bank_cards WHERE user_id = ? AND card_number = ?",
                [$user['user_id'], $address]
            );
            if ($exists) {
                error('该地址已绑定');
            }

            $count = $this->db->count('bank_cards', "user_id = ? AND card_type = 'usdt'", [$user['user_id']]);

            $addrId = $this->db->insert('bank_cards', [
                'user_id' => $user['user_id'],
                'card_type' => 'usdt',
                'bank_name' => $chain,
                'card_number' => $address,
                'is_default' => $count == 0 ? 1 : 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            success([
                'id' => $addrId,
                'address' => $address,
                'chain' => $chain
            ], '绑定成功');
        } else {
            success([
                'id' => rand(100, 999),
                'address' => $address,
                'chain' => $chain
            ], '绑定成功');
        }
    }

    /**
     * 获取USDT信息（系统收款地址等）
     */
    public function getUsdtInfo() {
        success([
            'address' => 'TXyzSystemAddressHere123456',
            'chain' => 'TRC20',
            'rate' => CNY_TO_USDT_RATE,
            'min_amount' => 100
        ]);
    }

    /**
     * 获取签到信息
     */
    public function getSignInfo() {
        $user = Auth::require();
        global $VIP_CONFIG;

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT vip_level FROM users WHERE id = ?", [$user['user_id']]);
            $vipLevel = (int)($userInfo['vip_level'] ?? 0);
            
            // 获取今日签到状态
            $today = date('Y-m-d');
            $signed = $this->db->fetch(
                "SELECT id FROM sign_logs WHERE user_id = ? AND DATE(created_at) = ?",
                [$user['user_id'], $today]
            );

            // 获取连续签到天数
            $streak = 0;
            // 简化处理：这里可以用更复杂的查询计算连续签到天数

            $checkinPoints = $VIP_CONFIG[$vipLevel]['checkin_points'] ?? 6;

            success([
                'signed_today' => !empty($signed),
                'streak' => $streak,
                'points_reward' => $checkinPoints,
                'vip_level' => $vipLevel
            ]);
        } else {
            success([
                'signed_today' => false,
                'streak' => 5,
                'points_reward' => 20,
                'vip_level' => 3
            ]);
        }
    }

    /**
     * 执行签到
     */
    public function doSign() {
        $user = Auth::require();
        global $VIP_CONFIG;

        if ($this->db && $this->db->isConnected()) {
            $today = date('Y-m-d');
            
            // 检查是否已签到
            $signed = $this->db->fetch(
                "SELECT id FROM sign_logs WHERE user_id = ? AND DATE(created_at) = ?",
                [$user['user_id'], $today]
            );
            if ($signed) {
                error('今日已签到');
            }

            $userInfo = $this->db->fetch("SELECT vip_level, points FROM users WHERE id = ?", [$user['user_id']]);
            $vipLevel = (int)($userInfo['vip_level'] ?? 0);
            $checkinPoints = $VIP_CONFIG[$vipLevel]['checkin_points'] ?? 6;

            $this->db->beginTransaction();
            try {
                // 记录签到
                $this->db->insert('sign_logs', [
                    'user_id' => $user['user_id'],
                    'points' => $checkinPoints,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 增加积分
                $this->db->update('users', [
                    'points' => $userInfo['points'] + $checkinPoints
                ], 'id = ?', [$user['user_id']]);

                $this->db->commit();

                success([
                    'points_earned' => $checkinPoints,
                    'total_points' => $userInfo['points'] + $checkinPoints
                ], '签到成功');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('签到失败');
            }
        } else {
            success([
                'points_earned' => 20,
                'total_points' => 5020
            ], '签到成功');
        }
    }

    /**
     * 获取收益日历
     */
    public function getProfitCalendar() {
        $user = Auth::require();
        $month = getQuery('month', date('Y-m'));

        if ($this->db && $this->db->isConnected()) {
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $earnings = $this->db->fetchAll(
                "SELECT DATE(earn_date) as date, SUM(amount) as amount 
                 FROM earnings_records 
                 WHERE user_id = ? AND earn_date BETWEEN ? AND ? 
                 GROUP BY DATE(earn_date)",
                [$user['user_id'], $startDate, $endDate]
            );

            $calendar = [];
            foreach ($earnings as $e) {
                $calendar[$e['date']] = formatMoney($e['amount']);
            }

            $totalEarnings = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM earnings_records WHERE user_id = ? AND earn_date BETWEEN ? AND ?",
                [$user['user_id'], $startDate, $endDate]
            );

            success([
                'month' => $month,
                'calendar' => $calendar,
                'total' => formatMoney($totalEarnings['total'] ?? 0)
            ]);
        } else {
            // Mock数据
            $calendar = [];
            for ($i = 1; $i <= 28; $i++) {
                $date = $month . '-' . sprintf('%02d', $i);
                if (rand(0, 1)) {
                    $calendar[$date] = formatMoney(rand(100, 5000));
                }
            }

            success([
                'month' => $month,
                'calendar' => $calendar,
                'total' => '35000.00'
            ]);
        }
    }
}
