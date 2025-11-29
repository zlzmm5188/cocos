// Providence 团队管理 API 工具类

(function() {
    if (window.__TEAM_API_JS__) return;
    window.__TEAM_API_JS__ = true;

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
            console.error('[TeamAPI] 返回HTML错误页面:', text.substring(0, 200));
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        // 解析JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('[TeamAPI] JSON解析失败:', text.substring(0, 200));
            throw new Error('服务器返回格式错误，不是有效的JSON');
        }

        if (result.code === 401) {
            localStorage.clear();
            window.location.href = 'login.html';
        }

        return result;
    }

    window.TeamAPI = {
        // 1. 团队概览 - GET /user/team/overview
        async getOverview() {
            const result = await apiRequest('/user/team/overview');
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取团队概览失败');
        },

        // 2. 团队列表 - GET /team/members?level=1&page=1
        async getTeamList(level = 1, page = 1, pageSize = 20) {
            const result = await apiRequest(`/team/members?level=${level}&page=${page}&page_size=${pageSize}`);
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取团队列表失败');
        },

        // 3. 推荐奖励记录 - GET /user/referral-rewards
        async getReferralRewards(page = 1, pageSize = 20) {
            const result = await apiRequest(`/user/referral-rewards?page=${page}&page_size=${pageSize}`);
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取推荐奖励失败');
        }
    };
})();
