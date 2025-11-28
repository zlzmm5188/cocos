/**
 * Providence API配置文件 - 统一版本
 * 版本: 3.0.0 (前后端对接统一规范)
 * 日期: 2025-11-23
 * 服务器: 72.60.196.188
 */

// 🎯 统一API配置（新服务器）
window.API_CONFIG = {
    // 🚀 生产环境：新服务器（72.60.196.188）
    baseURL: 'https://api.4kp3l0iq.top',

    // 管理后台地址
    adminURL: 'https://admin.4kp3l0iq.top',

    // Token配置
    tokenKey: 'providence_token',

    // 请求配置
    timeout: 30000,
    debug: true,

    // 版本信息
    version: '3.0.0',
    updated: '2025-11-23',
    server: '72.60.196.188'
};

// 兼容性配置
window.API_BASE = window.API_CONFIG.baseURL;

// 输出配置信息
console.log('🎯 Providence API配置已加载');
console.log('📡 API地址:', window.API_CONFIG.baseURL);
console.log('🏢 管理后台:', window.API_CONFIG.adminURL);
console.log('📅 更新时间:', window.API_CONFIG.updated);
