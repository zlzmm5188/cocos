/**
 * 交易记录API工具类
 * API文档：Providence前端API接口文档.md
 */

class TransactionAPI {
    constructor() {
        this.baseURL = window.API_CONFIG?.baseURL || '/backend/api.php';
    }

    /**
     * 获取Token
     */
    getToken() {
        // 统一使用TokenManager获取Token
        if (typeof TokenManager !== 'undefined' && TokenManager.getToken) {
            return TokenManager.getToken() || '';
        }
        return localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
    }

    /**
     * 获取交易记录
     * @param {Object} params - 查询参数
     * @param {number} params.page - 页码（默认1）
     * @param {number} params.pageSize - 每页数量（默认20）
     * @param {string} params.type - 类型筛选（all|recharge|withdraw|invest|ribao|points）
     * @param {string} params.currency - 币种筛选（all|CNY|USDT|RIBAO|POINTS）
     */
    async getRecords(params = {}) {
        const token = this.getToken();
        if (!token) {
            throw new Error('未登录');
        }

        const queryParams = new URLSearchParams({
            page: params.page || 1,
            pageSize: params.pageSize || 20,
            type: params.type || 'all',
            currency: params.currency || 'all'
        });

        const response = await fetch(`${this.baseURL}/user/transaction/records?${queryParams}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Token': token,
                'Authorization': `Bearer ${token}`
            }
        });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        const result = JSON.parse(text);
        if (result.code === 1 || result.code === 200) {
            return {
                list: result.data || [],
                total: result.total || 0,
                page: params.page || 1,
                pageSize: params.pageSize || 20
            };
        } else {
            throw new Error(result.message || result.msg || '获取失败');
        }
    }
}

// 导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TransactionAPI;
} else {
    window.TransactionAPI = TransactionAPI;
}
