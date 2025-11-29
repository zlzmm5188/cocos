<?php
/**
 * Providence Admin Backend - JWT 认证类
 */

if (!defined('ADMIN_API')) {
    die('Access Denied');
}

class Auth {
    /**
     * 生成 JWT Token
     */
    public static function generateToken($adminId, $username, $role) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $payload = base64_encode(json_encode([
            'admin_id' => $adminId,
            'username' => $username,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + TOKEN_EXPIRE
        ]));
        
        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * 验证并解析 Token
     */
    public static function verifyToken($token) {
        if (empty($token)) {
            return false;
        }
        
        // 移除 Bearer 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // 验证签名
        $expectedSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET);
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        // 解析 payload
        $data = json_decode(base64_decode($payload), true);
        if (!$data) {
            return false;
        }
        
        // 检查过期
        if (isset($data['exp']) && $data['exp'] < time()) {
            return false;
        }
        
        return $data;
    }
    
    /**
     * 获取当前管理员信息
     */
    public static function getCurrentAdmin() {
        $token = self::getTokenFromRequest();
        return self::verifyToken($token);
    }
    
    /**
     * 从请求中获取 Token
     */
    public static function getTokenFromRequest() {
        // 从 Authorization header 获取
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        // 从自定义 header 获取
        if (isset($_SERVER['HTTP_TOKEN'])) {
            return $_SERVER['HTTP_TOKEN'];
        }
        
        // 从 Query 参数获取
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        return null;
    }
    
    /**
     * 检查权限
     */
    public static function checkPermission($required) {
        $admin = self::getCurrentAdmin();
        
        if (!$admin) {
            return false;
        }
        
        // 超级管理员拥有所有权限
        if ($admin['role'] === 'super_admin') {
            return true;
        }
        
        // 获取管理员权限列表
        $adminInfo = db()->fetchOne(
            "SELECT permissions FROM admins WHERE id = ?",
            [$admin['admin_id']]
        );
        
        if (!$adminInfo || empty($adminInfo['permissions'])) {
            return false;
        }
        
        $permissions = json_decode($adminInfo['permissions'], true) ?: [];
        
        return in_array($required, $permissions);
    }
    
    /**
     * 密码哈希
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * 验证密码
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
