// Providence 通知系统 API 工具类

(function() {
    if (window.__NOTIFICATION_API_JS__) return;
    window.__NOTIFICATION_API_JS__ = true;

    const API_BASE = window.API_CONFIG?.baseURL || '/backend/api.php';

    async function apiRequest(endpoint, options = {}) {
        // 统一使用TokenManager获取Token
        const token = (typeof TokenManager !== 'undefined' && TokenManager.getToken)
            ? TokenManager.getToken()
            : (localStorage.getItem('providence_token') || localStorage.getItem('token') || '');
        if (!token) throw new Error('未登录');

        const url = API_BASE + endpoint;
        const headers = {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            ...options.headers
        };

        const response = await fetch(url, { ...options, headers });

        // 先获取文本，检查是否是HTML错误页面
        const text = await response.text();
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            console.error('[NotificationAPI] 返回HTML错误页面:', text.substring(0, 200));
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        // 解析JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('[NotificationAPI] JSON解析失败:', text.substring(0, 200));
            throw new Error('服务器返回格式错误，不是有效的JSON');
        }

        if (result.code === 401) {
            localStorage.clear();
            window.location.href = 'login.html';
        }

        return result;
    }

    window.NotificationAPI = {
        // 1. 获取通知列表 - GET /user/notifications
        async getNotifications(page = 1, pageSize = 20) {
            const result = await apiRequest(`/user/notifications?page=${page}&page_size=${pageSize}`);
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取通知失败');
        },

        // 2. 标记为已读 - POST /user/notification-read
        async markAsRead(id) {
            const result = await apiRequest('/user/notification-read', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            if (result.code === 1) return true;
            throw new Error(result.msg || '标记失败');
        },

        // 3. 获取未读数量 - GET /user/notification-unread-count
        async getUnreadCount() {
            const result = await apiRequest('/user/notification-unread-count');
            if (result.code === 1) return result.data.unread_count || 0;
            throw new Error(result.msg || '获取未读数量失败');
        },

        // 通知类型文本
        getTypeText(type) {
            const map = {
                'recharge': '充值成功',
                'withdraw': '提现审核',
                'invest_expire': '投资到期',
                'referral_reward': '邀请奖励',
                'team_reward': '团队奖励',
                'vip_upgrade': 'VIP升级',
                'announcement': '系统公告'
            };
            return map[type] || '通知';
        }
    };
})();
