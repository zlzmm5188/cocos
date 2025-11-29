/**
 * 充值API工具类
 * API文档：Providence前端API接口文档.md
 */

class RechargeAPI {
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
     * 支付宝充值
     * @param {Object} data - 充值信息
     * @param {number} data.amount - 充值金额
     * @param {string} data.order_no - 第三方支付订单号（可选）
     * @param {string} data.third_party_order - 第三方支付数据（可选）
     */
    async rechargeAlipay(data) {
        const token = this.getToken();
        if (!token) {
            throw new Error('未登录');
        }

        const response = await fetch(`${this.baseURL}/pay/recharge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Token': token,
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                amount: data.amount,
                payment_method: 'alipay',
                order_no: data.order_no || '',
                third_party_order: data.third_party_order || ''
            })
        });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        const result = JSON.parse(text);
        if (result.code === 1 || result.code === 200) {
            return result.data;
        } else {
            throw new Error(result.message || result.msg || '充值失败');
        }
    }

    /**
     * 微信充值
     * @param {Object} data - 充值信息
     * @param {number} data.amount - 充值金额
     * @param {string} data.order_no - 第三方支付订单号（可选）
     * @param {string} data.third_party_order - 第三方支付数据（可选）
     */
    async rechargeWechat(data) {
        const token = this.getToken();
        if (!token) {
            throw new Error('未登录');
        }

        const response = await fetch(`${this.baseURL}/pay/recharge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Token': token,
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                amount: data.amount,
                payment_method: 'wechat',
                order_no: data.order_no || '',
                third_party_order: data.third_party_order || ''
            })
        });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        const result = JSON.parse(text);
        if (result.code === 1 || result.code === 200) {
            return result.data;
        } else {
            throw new Error(result.message || result.msg || '充值失败');
        }
    }

    /**
     * USDT充值
     * @param {Object} data - 充值信息
     * @param {number} data.amount - 充值金额（USDT）
     */
    async rechargeUSDT(data) {
        const token = this.getToken();
        if (!token) {
            throw new Error('未登录');
        }

        const response = await fetch(`${this.baseURL}/pay/us/recharge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Token': token,
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                amount: data.amount
            })
        });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        const result = JSON.parse(text);
        if (result.code === 1 || result.code === 200) {
            return result.data;
        } else {
            throw new Error(result.message || result.msg || '充值失败');
        }
    }

    /**
     * 银行卡充值（需要支付密码）
     * @param {Object} data - 充值信息
     * @param {number} data.amount - 充值金额
     * @param {string} data.pay_password - 支付密码
     */
    async rechargeBank(data) {
        const token = this.getToken();
        if (!token) {
            throw new Error('未登录');
        }

        const response = await fetch(`${this.baseURL}/pay/recharge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Token': token,
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                amount: data.amount,
                payment_method: 'bank',
                pay_password: data.pay_password
            })
        });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        const result = JSON.parse(text);
        if (result.code === 1 || result.code === 200) {
            return result.data;
        } else {
            throw new Error(result.message || result.msg || '充值失败');
        }
    }
}

// 导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RechargeAPI;
} else {
    window.RechargeAPI = RechargeAPI;
}
