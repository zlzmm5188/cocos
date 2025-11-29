// Providence 日利宝 API 工具类

(function() {
    if (window.__RIBAO_API_JS__) return;
    window.__RIBAO_API_JS__ = true;

    // 使用统一API配置
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

        // 检查是否是HTML错误页面
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') ||
            text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            console.error('[RibaoAPI] 返回HTML错误页面:', text.substring(0, 200));
            throw new Error('服务器返回错误页面，请检查API路径是否正确');
        }

        // 解析JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('[RibaoAPI] JSON解析失败:', text.substring(0, 200));
            throw new Error('服务器返回格式错误，不是有效的JSON');
        }

        if (result.code === 401) {
            localStorage.clear();
            window.location.href = 'login.html';
        }

        return result;
    }

    window.RibaoAPI = {
        // 1. 获取日利宝信息 - GET /user/ribao/head (根据api-mapping.json)
        async getInfo() {
            const result = await apiRequest('/user/ribao/head');
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取日利宝信息失败');
        },

        // 2. 转入日利宝 - POST /user/ribao/in (根据api-mapping.json)
        async transferIn(amount) {
            const result = await apiRequest('/user/ribao/in', {
                method: 'POST',
                body: JSON.stringify({ amount })
            });
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '转入失败');
        },

        // 3. 转出日利宝 - POST /user/ribao/out (根据api-mapping.json)
        async transferOut(amount) {
            const result = await apiRequest('/user/ribao/out', {
                method: 'POST',
                body: JSON.stringify({ amount })
            });
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '转出失败');
        },

        // 4. 日利宝记录 - GET /user/ribao/list (根据api-mapping.json)
        async getRecords(page = 1, pageSize = 20) {
            const result = await apiRequest(`/user/ribao/list?page=${page}&page_size=${pageSize}`);
            if (result.code === 1) return result.data;
            throw new Error(result.msg || '获取记录失败');
        }
    };
})();
