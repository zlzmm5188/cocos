/**
 * Providence API配置文件 - 统一版本
 * 版本: 4.0.0 (完整后端API)
 * 日期: 2025-11-29
 * 
 * 配置说明:
 * - 本地开发: 自动使用 /backend/api.php
 * - 生产环境: 根据部署配置使用相对路径或完整域名
 */

(function() {
    // 检测当前环境
    const isLocalhost = window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1' ||
                        window.location.hostname.startsWith('192.168.');
    
    // API基础路径
    // 生产环境请修改为您的实际域名，例如: 'https://your-domain.com/backend/api.php'
    // 或者使用相对路径: '/backend/api.php'
    let apiBaseURL;
    
    if (isLocalhost) {
        // 本地开发环境
        apiBaseURL = window.location.origin + '/backend/api.php';
    } else {
        // 生产环境 - 使用相对路径（推荐）
        // 这样部署到任何域名都能正常工作
        apiBaseURL = '/backend/api.php';
        
        // 如果需要使用完整域名，取消下面的注释并修改域名
        // apiBaseURL = 'https://your-domain.com/backend/api.php';
    }
    
    // 🎯 统一API配置
    window.API_CONFIG = {
        // API地址（自动适配本地/生产环境）
        baseURL: apiBaseURL,
    
        // 管理后台地址
        adminURL: '/admin',
    
        // Token配置
        tokenKey: 'providence_token',
    
        // 请求配置
        timeout: 30000,
        debug: isLocalhost,
    
        // 版本信息
        version: '4.0.0',
        updated: '2025-11-29',
        
        // 环境标识
        isLocalhost: isLocalhost
    };
    
    // 兼容性配置
    window.API_BASE = window.API_CONFIG.baseURL;
    
    // 输出配置信息
    console.log('🎯 Providence API配置已加载');
    console.log('📡 API地址:', window.API_CONFIG.baseURL);
    console.log('🏢 管理后台:', window.API_CONFIG.adminURL);
    console.log('🌍 环境:', isLocalhost ? '本地开发' : '生产环境');
    console.log('📅 版本:', window.API_CONFIG.version);
})();
