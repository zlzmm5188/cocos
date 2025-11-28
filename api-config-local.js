/**
 * Providence API配置文件 - 本地测试版 (简化方案)
 * 前台 + API 合并在同一个地址
 *
 * 版本: 3.0.0-local-simplified
 * 日期: 2025-11-28
 */

// 🎯 统一 API 配置（前台和API在同一地址）
window.API_CONFIG = {
    // 🚀 本地测试环境：使用同一个地址的 api.php
    baseURL: window.location.origin + '/api',

    // Token配置
    tokenKey: 'providence_token',

    // 请求配置
    timeout: 30000,
    debug: true,

    // 版本信息
    version: '3.0.0-local-simplified',
    updated: '2025-11-28',
    server: 'localhost'
};

// 兼容性配置
window.API_BASE = window.API_CONFIG.baseURL;

// 输出配置信息
console.log('🎯 Providence API配置已加载（简化版）');
console.log('📡 API地址:', window.API_CONFIG.baseURL);
console.log('📅 更新时间:', window.API_CONFIG.updated);
console.log('✨ 前台 + API 已合并在同一地址');
