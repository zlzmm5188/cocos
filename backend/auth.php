<?php
/**
 * Providence 前台API - 认证模块
 */

class Auth {
    private static $currentUser = null;

    /**
     * 生成JWT Token
     */
    public static function generateToken($userId, $username) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'username' => $username,
            'exp' => time() + JWT_EXPIRE,
            'iat' => time()
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * 验证JWT Token
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

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // 验证签名
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64Signature !== $expectedSignature) {
            return false;
        }

        // 解析 payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);

        // 检查过期
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * 获取请求中的Token
     */
    public static function getToken() {
        // 从 Authorization header 获取
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
        if (isset($headers['authorization'])) {
            return $headers['authorization'];
        }

        // 从 Token header 获取
        if (isset($headers['Token'])) {
            return $headers['Token'];
        }
        if (isset($headers['token'])) {
            return $headers['token'];
        }

        // 从 HTTP_AUTHORIZATION 获取
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_TOKEN'])) {
            return $_SERVER['HTTP_TOKEN'];
        }

        // 从查询参数获取
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    /**
     * 检查是否已登录
     */
    public static function check() {
        $token = self::getToken();
        if (!$token) {
            return false;
        }

        $payload = self::verifyToken($token);
        if (!$payload) {
            return false;
        }

        self::$currentUser = $payload;
        return true;
    }

    /**
     * 要求登录（未登录则返回401）
     */
    public static function require() {
        if (!self::check()) {
            http_response_code(401);
            echo json_encode([
                'code' => -1,
                'msg' => '请先登录'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        return self::$currentUser;
    }

    /**
     * 获取当前用户信息
     */
    public static function user() {
        return self::$currentUser;
    }

    /**
     * 获取当前用户ID
     */
    public static function userId() {
        return self::$currentUser['user_id'] ?? null;
    }

    /**
     * 密码加密
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

    /**
     * 生成随机邀请码
     */
    public static function generateInviteCode($length = 8) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
}
