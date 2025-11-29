<?php
/**
 * 认证控制器
 */

class AuthController {
    /**
     * 管理员登录
     */
    public static function login() {
        $username = input('username');
        $password = input('password');
        
        if (empty($username) || empty($password)) {
            error('用户名和密码不能为空');
        }
        
        $admin = db()->fetchOne(
            "SELECT id, username, password, real_name, role, status FROM admins WHERE username = ?",
            [$username]
        );
        
        if (!$admin) {
            error('用户名或密码错误');
        }
        
        if ($admin['status'] != 1) {
            error('账号已被禁用');
        }
        
        if (!Auth::verifyPassword($password, $admin['password'])) {
            error('用户名或密码错误');
        }
        
        // 更新登录信息
        db()->update('admins', [
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'last_login_time' => date('Y-m-d H:i:s')
        ], ['id' => $admin['id']]);
        
        // 生成 token
        $token = Auth::generateToken($admin['id'], $admin['username'], $admin['role']);
        
        // 记录日志
        db()->insert('admin_logs', [
            'admin_id' => $admin['id'],
            'action' => 'login',
            'module' => 'auth',
            'content' => '登录系统',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        success([
            'token' => $token,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'name' => $admin['real_name'],
                'role' => $admin['role']
            ]
        ], '登录成功');
    }
    
    /**
     * 退出登录
     */
    public static function logout() {
        $admin = Auth::getCurrentAdmin();
        
        if ($admin) {
            logAction('logout', 'auth', '退出登录');
        }
        
        success(null, '退出成功');
    }
    
    /**
     * 获取当前管理员信息
     */
    public static function info() {
        $admin = requireLogin();
        
        $adminInfo = db()->fetchOne(
            "SELECT id, username, real_name, email, phone, role, permissions, last_login_ip, last_login_time 
             FROM admins WHERE id = ?",
            [$admin['admin_id']]
        );
        
        if (!$adminInfo) {
            error('管理员不存在', -401, 401);
        }
        
        $adminInfo['permissions'] = json_decode($adminInfo['permissions'], true) ?: [];
        
        success($adminInfo);
    }
    
    /**
     * 修改密码
     */
    public static function changePassword() {
        $admin = requireLogin();
        
        $oldPassword = input('old_password');
        $newPassword = input('new_password');
        $confirmPassword = input('confirm_password');
        
        if (empty($oldPassword) || empty($newPassword)) {
            error('请填写完整');
        }
        
        if ($newPassword !== $confirmPassword) {
            error('两次密码输入不一致');
        }
        
        if (strlen($newPassword) < 6) {
            error('密码长度不能少于6位');
        }
        
        $adminInfo = db()->fetchOne(
            "SELECT password FROM admins WHERE id = ?",
            [$admin['admin_id']]
        );
        
        if (!Auth::verifyPassword($oldPassword, $adminInfo['password'])) {
            error('原密码错误');
        }
        
        db()->update('admins', [
            'password' => Auth::hashPassword($newPassword)
        ], ['id' => $admin['admin_id']]);
        
        logAction('change_password', 'auth', '修改密码');
        
        success(null, '密码修改成功');
    }
}
