/**
 * Providence 统一Token管理模块 v2.0
 * 最后更新: 2025-11-23
 *
 * 核心功能：
 * 1. 统一Token存储（localStorage + sessionStorage双保险）
 * 2. 自动Token过期处理
 * 3. 防止重定向循环
 */

const TokenManager = {
    // 配置
    STORAGE_KEY: 'providence_token',
    HEADER_NAME: 'Token',
    LOGIN_PAGE: '/login.html',

    /**
     * 保存Token（双存储）
     * @param {string} token - JWT Token
     * @returns {boolean} 是否保存成功
     */
    saveToken(token) {
        if (!token) {
            console.error('[TokenManager] 尝试保存空Token');
            return false;
        }

        try {
            // 双重保险：localStorage + sessionStorage
            localStorage.setItem(this.STORAGE_KEY, token);
            sessionStorage.setItem(this.STORAGE_KEY, token);
            console.log('[TokenManager] ✅ Token已保存:', token.substring(0, 30) + '...');

            // 立即验证
            const saved = this.getToken();
            if (saved === token) {
                console.log('[TokenManager] ✅ Token验证成功');
                return true;
            } else {
                console.error('[TokenManager] ❌ Token验证失败');
                return false;
            }
        } catch (error) {
            console.error('[TokenManager] Token保存失败:', error);
            return false;
        }
    },

    /**
     * 获取Token（优先sessionStorage）
     * @returns {string|null} Token或null
     */
    getToken() {
        try {
            // 优先从sessionStorage读取（更快）
            let token = sessionStorage.getItem(this.STORAGE_KEY);

            // 降级到localStorage
            if (!token) {
                token = localStorage.getItem(this.STORAGE_KEY);
                // 如果localStorage有，同步到sessionStorage
                if (token) {
                    sessionStorage.setItem(this.STORAGE_KEY, token);
                    console.log('[TokenManager] 从localStorage恢复Token到sessionStorage');
                }
            }

            if (!token) {
                console.warn('[TokenManager] ⚠️  未找到Token');
            }

            return token;
        } catch (error) {
            console.error('[TokenManager] Token获取失败:', error);
            return null;
        }
    },

    /**
     * 检查Token是否存在
     * @returns {boolean}
     */
    hasToken() {
        const token = this.getToken();
        return !!token;
    },

    /**
     * 删除Token（登出）
     */
    clearToken() {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
            sessionStorage.removeItem(this.STORAGE_KEY);
            // 清理旧版本的token
            localStorage.removeItem('token');
            sessionStorage.removeItem('token');
            console.log('[TokenManager] ✅ Token已清除');
        } catch (error) {
            console.error('[TokenManager] Token清除失败:', error);
        }
    },

    /**
     * 检查登录状态（带重定向保护）
     * @param {boolean} autoRedirect - 是否自动跳转到登录页
     * @returns {boolean} 是否已登录
     */
    checkLogin(autoRedirect = true) {
        const token = this.getToken();
        const isLoggedIn = !!token;

        if (!isLoggedIn && autoRedirect) {
            // 防止无限循环：如果已经在登录页，不再跳转
            if (!location.pathname.includes('login.html')) {
                console.warn('[TokenManager] 未登录，跳转到登录页');
                const redirectUrl = encodeURIComponent(location.href);
                location.href = `${this.LOGIN_PAGE}?redirect=${redirectUrl}`;
            }
        }

        return isLoggedIn;
    },

    /**
     * 要求登录（未登录则跳转）- Integration Spec规范
     * @param {string} redirectUrl - 登录后跳转的URL（可选）
     * @returns {boolean} 是否已登录
     */
    requireLogin(redirectUrl = null) {
        const token = this.getToken();
        const isLoggedIn = !!token;

        if (!isLoggedIn) {
            // 防止无限循环：如果已经在登录页，不再跳转
            if (!location.pathname.includes('login.html')) {
                console.warn('[TokenManager] 未登录，跳转到登录页');
                const currentUrl = redirectUrl || location.href;
                const encodedUrl = encodeURIComponent(currentUrl);
                location.href = `${this.LOGIN_PAGE}?redirect=${encodedUrl}`;
            }
            return false;
        }

        return true;
    },

    /**
     * 获取Token的HTTP Header格式
     * @returns {Object} Header对象
     */
    getAuthHeader() {
        const token = this.getToken();
        return token ? { [this.HEADER_NAME]: token } : {};
    },

    /**
     * 检查是否在开发环境
     * @returns {boolean}
     */
    isDev() {
        return location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    }
};

// 自动初始化日志
console.log('[TokenManager] ✅ Token管理模块已加载 v2.0');
console.log('[TokenManager] 📦 配置:', {
    STORAGE_KEY: TokenManager.STORAGE_KEY,
    HEADER_NAME: TokenManager.HEADER_NAME,
    LOGIN_PAGE: TokenManager.LOGIN_PAGE
});
console.log('[TokenManager] 🔑 当前Token状态:', TokenManager.hasToken() ? '已登录' : '未登录');

// 导出到全局
window.TokenManager = TokenManager;
