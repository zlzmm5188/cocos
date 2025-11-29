<?php
/**
 * 系统配置控制器
 */

class ConfigController {
    /**
     * 获取VIP配置
     */
    public static function vipConfig() {
        requireLogin();
        
        $levels = db()->fetchAll(
            "SELECT * FROM vip_levels ORDER BY id ASC"
        );
        
        // 解析特权列表
        foreach ($levels as &$level) {
            if ($level['privileges']) {
                $level['privileges'] = json_decode($level['privileges'], true);
            }
        }
        
        success($levels);
    }
    
    /**
     * 保存VIP配置
     */
    public static function saveVipConfig() {
        requirePermission('config_edit');
        
        $levels = input('levels');
        
        if (!is_array($levels)) {
            error('参数错误');
        }
        
        db()->beginTransaction();
        
        try {
            foreach ($levels as $level) {
                if (!isset($level['id'])) {
                    continue;
                }
                
                $data = [
                    'name' => $level['name'] ?? '',
                    'min_invest' => $level['min_invest'] ?? 0,
                    'daily_withdraw_limit' => $level['daily_withdraw_limit'] ?? null,
                    'withdraw_fee_rate' => $level['withdraw_fee_rate'] ?? 0,
                    'invite_reward_rate' => $level['invite_reward_rate'] ?? 0
                ];
                
                if (isset($level['privileges'])) {
                    $data['privileges'] = is_array($level['privileges']) 
                        ? json_encode($level['privileges']) 
                        : $level['privileges'];
                }
                
                db()->update('vip_levels', $data, ['id' => $level['id']]);
            }
            
            db()->commit();
            
            logAction('update_vip_config', 'config', 'VIP配置更新');
            
            success(null, '保存成功');
            
        } catch (Exception $e) {
            db()->rollback();
            error('保存失败');
        }
    }
    
    /**
     * 获取系统设置
     */
    public static function settings() {
        requireLogin();
        
        $configs = db()->fetchAll("SELECT `key`, `value`, `description`, `group` FROM system_config");
        
        // 按分组整理
        $result = [];
        foreach ($configs as $config) {
            $group = $config['group'] ?: 'general';
            if (!isset($result[$group])) {
                $result[$group] = [];
            }
            $result[$group][$config['key']] = [
                'value' => $config['value'],
                'description' => $config['description']
            ];
        }
        
        success($result);
    }
    
    /**
     * 保存系统设置
     */
    public static function saveSettings() {
        requirePermission('config_edit');
        
        $settings = input();
        
        if (empty($settings)) {
            error('没有需要保存的设置');
        }
        
        db()->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                // 跳过非配置字段
                if (in_array($key, ['token', 'page', 'page_size'])) {
                    continue;
                }
                
                // 检查是否存在
                $exists = db()->fetchOne(
                    "SELECT id FROM system_config WHERE `key` = ?",
                    [$key]
                );
                
                if ($exists) {
                    db()->update('system_config', ['value' => $value], ['key' => $key]);
                } else {
                    db()->insert('system_config', [
                        'key' => $key,
                        'value' => $value,
                        'description' => '',
                        'group' => 'general'
                    ]);
                }
            }
            
            db()->commit();
            
            logAction('update_settings', 'config', array_keys($settings));
            
            success(null, '保存成功');
            
        } catch (Exception $e) {
            db()->rollback();
            error('保存失败');
        }
    }
}
