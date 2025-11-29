<?php
/**
 * 认证控制器 - 处理用户登录、注册、密码重置等
 */

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 用户登录
     */
    public function login() {
        $data = getJsonInput();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username)) {
            error('请输入账号');
        }

        if (empty($password)) {
            error('请输入密码');
        }

        // 如果数据库可用，查询真实用户
        if ($this->db && $this->db->isConnected()) {
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE username = ? OR phone = ? OR email = ? LIMIT 1",
                [$username, $username, $username]
            );

            if (!$user) {
                error('账号不存在');
            }

            if (!Auth::verifyPassword($password, $user['password'])) {
                error('密码错误');
            }

            if ($user['status'] != 1) {
                error('账号已被禁用');
            }

            // 更新登录信息
            $this->db->update('users', [
                'login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'login_time' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);

            $token = Auth::generateToken($user['id'], $user['username']);

            success([
                'token' => $token,
                'accessToken' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'nickname' => $user['real_name'] ?? $user['username'],
                    'phone' => maskPhone($user['phone'] ?? ''),
                    'vip_level' => $user['vip_level'] ?? 0,
                    'avatar' => $user['avatar'] ?? ''
                ]
            ], '登录成功');
        } else {
            // Mock模式 - 用于开发测试
            $mockUsers = [
                'G138688' => ['id' => 1, 'password' => 'G138688', 'nickname' => 'Test User'],
                'admin' => ['id' => 2, 'password' => 'admin123', 'nickname' => 'Admin'],
                'test' => ['id' => 3, 'password' => 'test123', 'nickname' => 'Test'],
            ];

            if (!isset($mockUsers[$username])) {
                error('账号不存在');
            }

            if ($mockUsers[$username]['password'] !== $password) {
                error('密码错误');
            }

            $token = Auth::generateToken($mockUsers[$username]['id'], $username);

            success([
                'token' => $token,
                'accessToken' => $token,
                'user' => [
                    'id' => $mockUsers[$username]['id'],
                    'username' => $username,
                    'nickname' => $mockUsers[$username]['nickname'],
                    'phone' => '138****8888',
                    'vip_level' => 3,
                    'avatar' => ''
                ]
            ], '登录成功');
        }
    }

    /**
     * 用户注册
     */
    public function register() {
        $data = getJsonInput();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? $data['confirmPassword'] ?? '';
        $phone = trim($data['phone'] ?? '');
        $inviteCode = trim($data['invite_code'] ?? $data['inviteCode'] ?? '');

        if (empty($username)) {
            error('请输入用户名');
        }

        if (strlen($username) < 4 || strlen($username) > 20) {
            error('用户名长度4-20位');
        }

        if (empty($password)) {
            error('请输入密码');
        }

        if (strlen($password) < 6) {
            error('密码长度至少6位');
        }

        if ($password !== $confirmPassword) {
            error('两次密码不一致');
        }

        if ($this->db && $this->db->isConnected()) {
            // 检查用户名是否已存在
            $exists = $this->db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if ($exists) {
                error('用户名已被使用');
            }

            // 检查手机号
            if (!empty($phone)) {
                $exists = $this->db->fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
                if ($exists) {
                    error('手机号已被注册');
                }
            }

            // 查找邀请人
            $parentId = null;
            if (!empty($inviteCode)) {
                $parent = $this->db->fetch("SELECT id FROM users WHERE invite_code = ?", [$inviteCode]);
                if ($parent) {
                    $parentId = $parent['id'];
                }
            }

            // 生成邀请码
            $newInviteCode = Auth::generateInviteCode();
            while ($this->db->fetch("SELECT id FROM users WHERE invite_code = ?", [$newInviteCode])) {
                $newInviteCode = Auth::generateInviteCode();
            }

            // 创建用户
            $userId = $this->db->insert('users', [
                'username' => $username,
                'password' => Auth::hashPassword($password),
                'phone' => $phone ?: null,
                'invite_code' => $newInviteCode,
                'parent_id' => $parentId,
                'vip_level' => 0,
                'balance' => 0,
                'points' => 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $token = Auth::generateToken($userId, $username);

            success([
                'token' => $token,
                'accessToken' => $token,
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'invite_code' => $newInviteCode
                ]
            ], '注册成功');
        } else {
            // Mock模式
            $token = Auth::generateToken(999, $username);
            success([
                'token' => $token,
                'accessToken' => $token,
                'user' => [
                    'id' => 999,
                    'username' => $username,
                    'invite_code' => Auth::generateInviteCode()
                ]
            ], '注册成功');
        }
    }

    /**
     * 退出登录
     */
    public function logout() {
        success(null, '退出成功');
    }

    /**
     * 刷新Token
     */
    public function refresh() {
        $user = Auth::require();
        $token = Auth::generateToken($user['user_id'], $user['username']);
        success([
            'token' => $token,
            'accessToken' => $token
        ], '刷新成功');
    }

    /**
     * 发送短信验证码
     */
    public function sendSms() {
        $data = getJsonInput();
        $phone = trim($data['phone'] ?? '');

        if (empty($phone)) {
            error('请输入手机号');
        }

        if (!isValidPhone($phone)) {
            error('手机号格式不正确');
        }

        // 这里应该调用短信服务发送验证码
        // 为了测试，直接返回成功
        success([
            'expires_in' => 300
        ], '验证码已发送');
    }

    /**
     * 验证短信验证码
     */
    public function verifySms() {
        $data = getJsonInput();
        $phone = trim($data['phone'] ?? '');
        $code = trim($data['code'] ?? '');

        if (empty($phone) || empty($code)) {
            error('请输入手机号和验证码');
        }

        // 这里应该验证验证码
        // 为了测试，验证码123456总是有效
        if ($code !== '123456') {
            error('验证码错误');
        }

        success(null, '验证成功');
    }

    /**
     * 忘记密码
     */
    public function forgotPassword() {
        $data = getJsonInput();
        $phone = trim($data['phone'] ?? '');
        $code = trim($data['code'] ?? '');
        $newPassword = $data['new_password'] ?? $data['newPassword'] ?? '';

        if (empty($phone)) {
            error('请输入手机号');
        }

        if (empty($code)) {
            error('请输入验证码');
        }

        if (empty($newPassword)) {
            error('请输入新密码');
        }

        if (strlen($newPassword) < 6) {
            error('密码长度至少6位');
        }

        // 验证验证码（测试模式：123456）
        if ($code !== '123456') {
            error('验证码错误');
        }

        if ($this->db && $this->db->isConnected()) {
            $user = $this->db->fetch("SELECT id FROM users WHERE phone = ?", [$phone]);
            if (!$user) {
                error('用户不存在');
            }

            $this->db->update('users', [
                'password' => Auth::hashPassword($newPassword)
            ], 'id = ?', [$user['id']]);
        }

        success(null, '密码重置成功');
    }

    /**
     * 根据账号找回密码
     */
    public function forgotPasswordByAccount() {
        $data = getJsonInput();
        $username = trim($data['username'] ?? '');
        $code = trim($data['code'] ?? '');
        $newPassword = $data['new_password'] ?? $data['newPassword'] ?? '';

        if (empty($username)) {
            error('请输入账号');
        }

        if (empty($newPassword)) {
            error('请输入新密码');
        }

        if ($this->db && $this->db->isConnected()) {
            $user = $this->db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if (!$user) {
                error('用户不存在');
            }

            $this->db->update('users', [
                'password' => Auth::hashPassword($newPassword)
            ], 'id = ?', [$user['id']]);
        }

        success(null, '密码重置成功');
    }

    /**
     * 修改密码
     */
    public function changePassword() {
        $user = Auth::require();
        $data = getJsonInput();
        
        $oldPassword = $data['old_password'] ?? $data['oldPassword'] ?? '';
        $newPassword = $data['new_password'] ?? $data['newPassword'] ?? '';

        if (empty($oldPassword)) {
            error('请输入原密码');
        }

        if (empty($newPassword)) {
            error('请输入新密码');
        }

        if (strlen($newPassword) < 6) {
            error('新密码长度至少6位');
        }

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$user['user_id']]);
            if (!$userInfo) {
                error('用户不存在');
            }

            if (!Auth::verifyPassword($oldPassword, $userInfo['password'])) {
                error('原密码错误');
            }

            $this->db->update('users', [
                'password' => Auth::hashPassword($newPassword)
            ], 'id = ?', [$user['user_id']]);
        }

        success(null, '密码修改成功');
    }

    /**
     * 重置密码（Token方式）
     */
    public function resetPassword() {
        $user = Auth::require();
        $data = getJsonInput();
        
        $newPassword = $data['new_password'] ?? $data['newPassword'] ?? $data['password'] ?? '';

        if (empty($newPassword)) {
            error('请输入新密码');
        }

        if (strlen($newPassword) < 6) {
            error('新密码长度至少6位');
        }

        if ($this->db && $this->db->isConnected()) {
            $this->db->update('users', [
                'password' => Auth::hashPassword($newPassword)
            ], 'id = ?', [$user['user_id']]);
        }

        success(null, '密码重置成功');
    }
}
