<?php
/**
 * VIP配置控制器
 * 
 * 管理VIP等级配置：
 * - 邀请返利比例（一级、二级）
 * - 升级条件（累计投资）
 * - 额外加息
 * - 签到积分
 * - 团队管理奖
 */

class VipConfigController {
    
    /**
     * 默认VIP配置
     */
    private static $defaultConfig = [
        // VIP邀请返利比例（一级、二级）- 百分比
        'invite_rewards' => [
            0 => ['level1' => 1, 'level2' => 0],
            1 => ['level1' => 2, 'level2' => 1],
            2 => ['level1' => 3, 'level2' => 2],
            3 => ['level1' => 4, 'level2' => 2],
            4 => ['level1' => 5, 'level2' => 3],
            5 => ['level1' => 5, 'level2' => 4],
            6 => ['level1' => 6, 'level2' => 4],
            7 => ['level1' => 6, 'level2' => 5],
            8 => ['level1' => 7, 'level2' => 5],
        ],
        // VIP升级条件和额外加息
        'upgrade_rules' => [
            1 => ['min_invest' => 30000, 'extra_rate' => 0.0005],
            2 => ['min_invest' => 100000, 'extra_rate' => 0.001],
            3 => ['min_invest' => 250000, 'extra_rate' => 0.0012],
            4 => ['min_invest' => 800000, 'extra_rate' => 0.0015],
            5 => ['min_invest' => 1500000, 'extra_rate' => 0.0016],
            6 => ['min_invest' => 3800000, 'extra_rate' => 0.0018],
            7 => ['min_invest' => 8000000, 'extra_rate' => 0.0023],
            8 => ['min_invest' => 13000000, 'extra_rate' => 0.0025],
        ],
        // 签到积分
        'checkin_points' => [
            0 => 6,
            1 => 10,
            2 => 16,
            3 => 20,
            4 => 30,
            5 => 40,
            6 => 50,
            7 => 60,
            8 => 70,
        ],
        // 团队管理奖
        'team_awards' => [
            ['members' => 3, 'min_invest' => 80000, 'points' => 2000],
            ['members' => 5, 'min_invest' => 150000, 'points' => 3900],
            ['members' => 10, 'min_invest' => 500000, 'points' => 12000],
            ['members' => 20, 'min_invest' => 1500000, 'points' => 35000],
            ['members' => 50, 'min_invest' => 3800000, 'points' => 50000],
            ['members' => 100, 'min_invest' => 8800000, 'points' => 75000],
            ['members' => 200, 'min_invest' => 15000000, 'points' => 150000],
            ['members' => 500, 'min_invest' => 58000000, 'points' => 200000],
            ['members' => 1000, 'min_invest' => 98000000, 'points' => 380000],
        ]
    ];
    
    /**
     * 获取完整VIP配置
     */
    public static function getFullConfig() {
        requireLogin();
        
        $config = self::loadConfig();
        
        // 格式化返回数据
        $result = [
            'invite_rewards' => [],
            'upgrade_rules' => [],
            'checkin_points' => [],
            'team_awards' => $config['team_awards']
        ];
        
        // 格式化邀请返利
        for ($i = 0; $i <= 8; $i++) {
            $result['invite_rewards'][] = [
                'vip_level' => $i,
                'level1_rate' => $config['invite_rewards'][$i]['level1'],
                'level2_rate' => $config['invite_rewards'][$i]['level2']
            ];
        }
        
        // 格式化升级规则
        for ($i = 1; $i <= 8; $i++) {
            $result['upgrade_rules'][] = [
                'vip_level' => $i,
                'min_invest' => $config['upgrade_rules'][$i]['min_invest'],
                'extra_rate' => $config['upgrade_rules'][$i]['extra_rate'] * 100 // 转为百分比显示
            ];
        }
        
        // 格式化签到积分
        for ($i = 0; $i <= 8; $i++) {
            $result['checkin_points'][] = [
                'vip_level' => $i,
                'points' => $config['checkin_points'][$i]
            ];
        }
        
        success($result);
    }
    
    /**
     * 保存邀请返利配置
     */
    public static function saveInviteRewards() {
        requirePermission('config_edit');
        
        $rewards = input('rewards');
        
        if (!is_array($rewards)) {
            error('参数错误');
        }
        
        $config = self::loadConfig();
        
        foreach ($rewards as $item) {
            if (!isset($item['vip_level'])) {
                continue;
            }
            $level = (int)$item['vip_level'];
            if ($level < 0 || $level > 8) {
                continue;
            }
            
            $config['invite_rewards'][$level] = [
                'level1' => (float)($item['level1_rate'] ?? 0),
                'level2' => (float)($item['level2_rate'] ?? 0)
            ];
        }
        
        self::saveConfig($config);
        
        logAction('update_invite_rewards', 'config', '更新邀请返利配置');
        
        success(null, '保存成功');
    }
    
    /**
     * 保存升级规则配置
     */
    public static function saveUpgradeRules() {
        requirePermission('config_edit');
        
        $rules = input('rules');
        
        if (!is_array($rules)) {
            error('参数错误');
        }
        
        $config = self::loadConfig();
        
        foreach ($rules as $item) {
            if (!isset($item['vip_level'])) {
                continue;
            }
            $level = (int)$item['vip_level'];
            if ($level < 1 || $level > 8) {
                continue;
            }
            
            $config['upgrade_rules'][$level] = [
                'min_invest' => (float)($item['min_invest'] ?? 0),
                'extra_rate' => (float)($item['extra_rate'] ?? 0) / 100 // 百分比转小数
            ];
        }
        
        self::saveConfig($config);
        
        logAction('update_upgrade_rules', 'config', '更新VIP升级规则');
        
        success(null, '保存成功');
    }
    
    /**
     * 保存签到积分配置
     */
    public static function saveCheckinPoints() {
        requirePermission('config_edit');
        
        $points = input('points');
        
        if (!is_array($points)) {
            error('参数错误');
        }
        
        $config = self::loadConfig();
        
        foreach ($points as $item) {
            if (!isset($item['vip_level'])) {
                continue;
            }
            $level = (int)$item['vip_level'];
            if ($level < 0 || $level > 8) {
                continue;
            }
            
            $config['checkin_points'][$level] = (int)($item['points'] ?? 0);
        }
        
        self::saveConfig($config);
        
        logAction('update_checkin_points', 'config', '更新签到积分配置');
        
        success(null, '保存成功');
    }
    
    /**
     * 保存团队管理奖配置
     */
    public static function saveTeamAwards() {
        requirePermission('config_edit');
        
        $awards = input('awards');
        
        if (!is_array($awards)) {
            error('参数错误');
        }
        
        $config = self::loadConfig();
        
        $config['team_awards'] = [];
        foreach ($awards as $item) {
            if (!isset($item['members']) || !isset($item['min_invest']) || !isset($item['points'])) {
                continue;
            }
            
            $config['team_awards'][] = [
                'members' => (int)$item['members'],
                'min_invest' => (float)$item['min_invest'],
                'points' => (int)$item['points']
            ];
        }
        
        // 按成员数排序
        usort($config['team_awards'], function($a, $b) {
            return $a['members'] - $b['members'];
        });
        
        self::saveConfig($config);
        
        logAction('update_team_awards', 'config', '更新团队管理奖配置');
        
        success(null, '保存成功');
    }
    
    /**
     * 加载配置
     */
    private static function loadConfig() {
        $stored = db()->fetchOne(
            "SELECT `value` FROM system_config WHERE `key` = 'vip_full_config'"
        );
        
        if ($stored && $stored['value']) {
            $config = json_decode($stored['value'], true);
            if (is_array($config)) {
                return array_merge(self::$defaultConfig, $config);
            }
        }
        
        return self::$defaultConfig;
    }
    
    /**
     * 保存配置
     */
    private static function saveConfig($config) {
        $value = json_encode($config, JSON_UNESCAPED_UNICODE);
        
        $exists = db()->fetchOne(
            "SELECT id FROM system_config WHERE `key` = 'vip_full_config'"
        );
        
        if ($exists) {
            db()->update('system_config', [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['key' => 'vip_full_config']);
        } else {
            db()->insert('system_config', [
                'key' => 'vip_full_config',
                'value' => $value,
                'description' => 'VIP完整配置',
                'group' => 'vip'
            ]);
        }
    }
    
    /**
     * 获取指定VIP等级的邀请返利比例
     */
    public static function getInviteRewardRates($vipLevel) {
        $config = self::loadConfig();
        $level = max(0, min(8, (int)$vipLevel));
        return $config['invite_rewards'][$level] ?? ['level1' => 1, 'level2' => 0];
    }
    
    /**
     * 获取指定VIP等级的签到积分
     */
    public static function getCheckinPoints($vipLevel) {
        $config = self::loadConfig();
        $level = max(0, min(8, (int)$vipLevel));
        return $config['checkin_points'][$level] ?? 6;
    }
    
    /**
     * 获取指定VIP等级的额外加息
     */
    public static function getExtraRate($vipLevel) {
        $config = self::loadConfig();
        $level = max(0, min(8, (int)$vipLevel));
        return $config['upgrade_rules'][$level]['extra_rate'] ?? 0;
    }
    
    /**
     * 根据累计投资计算应得VIP等级
     */
    public static function calculateVipLevel($totalInvest) {
        $config = self::loadConfig();
        $level = 0;
        
        for ($i = 8; $i >= 1; $i--) {
            if ($totalInvest >= $config['upgrade_rules'][$i]['min_invest']) {
                $level = $i;
                break;
            }
        }
        
        return $level;
    }
    
    /**
     * 检查并发放团队管理奖
     */
    public static function checkTeamAward($userId) {
        $config = self::loadConfig();
        
        // 获取下级成员数和累计投资
        $team = db()->fetchOne(
            "SELECT 
                COUNT(*) as member_count,
                COALESCE(SUM(total_invest), 0) as total_invest
             FROM users 
             WHERE parent_id = ?",
            [$userId]
        );
        
        // 获取用户当前已领取的团队奖等级
        $user = db()->fetchOne(
            "SELECT id, team_award_level, points FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            return null;
        }
        
        $currentLevel = (int)($user['team_award_level'] ?? 0);
        $memberCount = (int)$team['member_count'];
        $totalInvest = (float)$team['total_invest'];
        
        // 检查是否达到下一等级
        $newLevel = $currentLevel;
        $awardPoints = 0;
        
        foreach ($config['team_awards'] as $index => $award) {
            $awardLevel = $index + 1;
            if ($awardLevel <= $currentLevel) {
                continue;
            }
            
            if ($memberCount >= $award['members'] && $totalInvest >= $award['min_invest']) {
                $newLevel = $awardLevel;
                $awardPoints += $award['points'];
            }
        }
        
        // 发放奖励
        if ($newLevel > $currentLevel && $awardPoints > 0) {
            $newPoints = (int)$user['points'] + $awardPoints;
            
            db()->update('users', [
                'team_award_level' => $newLevel,
                'points' => $newPoints
            ], ['id' => $userId]);
            
            // 记录积分流水
            db()->insert('balance_logs', [
                'user_id' => $userId,
                'type' => 'team_award',
                'currency' => 'POINTS',
                'amount' => $awardPoints,
                'balance_before' => $user['points'],
                'balance_after' => $newPoints,
                'remark' => "团队管理奖 等级$newLevel",
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'old_level' => $currentLevel,
                'new_level' => $newLevel,
                'points_awarded' => $awardPoints
            ];
        }
        
        return null;
    }
}
