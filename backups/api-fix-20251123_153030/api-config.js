/**
 * Providence API配置文件 - 最终版本
 * 版本: 3.2.0 (修复双层/api问题)
 * 日期: 2025-11-23 15:03
 */

window.API_CONFIG = {
    // ⚠️ 注意：baseURL不带/api，因为代码中会拼接 /api/xxx
    baseURL: "https://api.4kp3l0iq.top/api",
    
    adminURL: 'https://admin.4kp3l0iq.top',
    tokenKey: 'providence_token',
    timeout: 30000,
    debug: true,
    version: '3.2.0',
    updated: '2025-11-23 15:03'
};

window.API_BASE = window.API_CONFIG.baseURL;

console.log('🎯 API配置加载完成');
console.log('📡 Base URL:', window.API_CONFIG.baseURL);
console.log('✅ 登录接口: POST ' + window.API_CONFIG.baseURL + '/api/auth/login');
console.log('✅ 注册接口: POST ' + window.API_CONFIG.baseURL + '/api/auth/register');
console.log('✅ 用户信息: GET ' + window.API_CONFIG.baseURL + '/api/user/info');
