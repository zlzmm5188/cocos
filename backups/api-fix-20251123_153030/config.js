// =============================================
// Providence 统一网络层 - 重构版
// 版本: 3.0-REFACTORED
// 日期: 2025-11-20
// =============================================

// ===================================
// 配置
// ===================================
window.API_CONFIG = {
    SPLASH_DOMAIN: 'https://sen.wyzyrx.cn',
    baseURL: "https://api.4kp3l0iq.top/api",
    adminURL: 'https://admin.4kp3l0iq.top',
    tokenKey: 'providence_token',
    timeout: 30000,  // 30秒超时
    debug: true  // 开启调试模式
};

// ===================================
// 统一 Token 管理
// ===================================
const TokenService = {
    getToken() {
        try {
            return localStorage.getItem(API_CONFIG.tokenKey) ||
                localStorage.getItem('providence_token') ||
                (window.TokenManager?.getToken?.() || '');
        } catch (err) {
            console.warn('[Token] 获取失败:', err);
            return '';
        }
    },

    setToken(token) {
        try {
            localStorage.setItem(API_CONFIG.tokenKey, token);
            if (window.TokenManager?.setToken) {
                window.TokenManager.setToken(token);
            }
            return true;
        } catch (err) {
            console.error('[Token] 保存失败:', err);
            return false;
        }
    },

    removeToken() {
        try {
            localStorage.removeItem(API_CONFIG.tokenKey);
            localStorage.removeItem('token');
            if (window.TokenManager?.removeToken) {
                window.TokenManager.removeToken();
            }
        } catch (err) {
            console.error('[Token] 删除失败:', err);
        }
    },

    checkLogin() {
        return !!this.getToken();
    },

    requireLogin(redirectUrl = 'login.html') {
        if (!this.checkLogin()) {
            console.warn('[Auth] 未登录，跳转到登录页');
            window.location.href = redirectUrl;
            return false;
        }
        return true;
    }
};

// ===================================
// 统一 HTTP 客户端（唯一实现）
// ===================================
class UnifiedHttpClient {
    constructor(config) {
        this.baseURL = config.baseURL;
        this.timeout = config.timeout;
        this.debug = config.debug;
    }

    /**
     * 构建请求头
     */
    buildHeaders(extra = {}) {
        const headers = {
            'Content-Type': 'application/json',
            ...extra
        };

        const token = TokenService.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
            headers['Token'] = token; // 兼容旧后端
        }

        return headers;
    }

    /**
     * 统一请求方法
     */
    async request(method, path, data = null, options = {}) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);

        // 确保路径以 / 开头
        const fullPath = path.startsWith('/') ? path : '/' + path;
        const url = `${this.baseURL}${fullPath}`;

        const requestOptions = {
            method: method.toUpperCase(),
            headers: this.buildHeaders(options.headers || {}),
            signal: controller.signal
        };

        if (data && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
            requestOptions.body = JSON.stringify(data);
        }

        try {
            if (this.debug) {
                // console.log(`[HTTP ${method}]`, url, data || ''); // 性能优化：已注释
            }

            const response = await fetch(url, requestOptions);
            const text = await response.text();

            // 解析 JSON
            let result;
            try {
                result = text ? JSON.parse(text) : {};
            } catch (err) {
                if (this.debug) {
                    console.error('[HTTP] JSON解析失败:', err, text.substring(0, 100));
                }
                throw new Error('响应解析失败: ' + text.substring(0, 50));
            }

            // 统一响应格式
            return {
                status: response.status,
                ok: response.ok,
                data: result,
                // 兼容旧代码：直接访问 code、msg
                code: result.code,
                msg: result.msg || result.message
            };
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('请求超时');
            }
            throw error;
        } finally {
            clearTimeout(timeoutId);
        }
    }

    /**
     * GET 请求
     */
    async get(path, params = {}, options = {}) {
        const query = new URLSearchParams(params).toString();
        const fullPath = query ? `${path}?${query}` : path;
        return this.request('GET', fullPath, null, options);
    }

    /**
     * POST 请求
     */
    async post(path, data = {}, options = {}) {
        return this.request('POST', path, data, options);
    }

    /**
     * PUT 请求
     */
    async put(path, data = {}, options = {}) {
        return this.request('PUT', path, data, options);
    }

    /**
     * DELETE 请求
     */
    async delete(path, options = {}) {
        return this.request('DELETE', path, null, options);
    }
}

// ===================================
// 统一字段处理（user_id → id → uid）
// ===================================
const FieldNormalizer = {
    /**
     * 标准化用户ID字段
     * 后台返回 user_id，统一转换为 id 和 uid
     */
    normalizeUserId(data) {
        if (!data || typeof data !== 'object') {
            return data;
        }

        // 如果存在 user_id，确保 id 和 uid 也存在
        if (data.user_id !== undefined) {
            data.id = data.user_id;
            data.uid = data.user_id;
        } else if (data.id !== undefined) {
            // 如果只有 id，添加 user_id 和 uid
            data.user_id = data.id;
            data.uid = data.id;
        } else if (data.uid !== undefined) {
            // 如果只有 uid，添加 user_id 和 id
            data.user_id = data.uid;
            data.id = data.uid;
        }

        return data;
    },

    /**
     * 标准化响应数据
     */
    normalizeResponse(response) {
        if (!response || !response.data) {
            return response;
        }

        // 如果 data 是对象，标准化用户ID字段
        if (typeof response.data === 'object' && !Array.isArray(response.data)) {
            response.data = this.normalizeUserId(response.data);
        }

        return response;
    }
};

// ===================================
// 创建统一 HTTP 客户端实例
// ===================================
const http = new UnifiedHttpClient(API_CONFIG);

// ===================================
// 统一 API 服务（封装常用接口）
// ===================================
const ApiService = {
    // Token 管理
    token: TokenService,

    // 认证相关
    auth: {
        checkLogin: () => TokenService.checkLogin(),
        requireLogin: (url) => TokenService.requireLogin(url)
    },

    // 用户信息
    user: {
        /**
         * 获取用户信息（自动标准化字段）
         */
        async getInfo() {
            const res = await http.get('/api/user/info');  // 修正：使用统一API路径
            return FieldNormalizer.normalizeResponse(res);
        },

        /**
         * 获取VIP进度
         */
        async getVipProgress() {
            return await http.get('/api/user/vip-progress');  // 修正路径
        }
    },

    // 日利宝
    ribao: {
        async getInfo() {
            const res = await http.get('/api/user/api/ribao/info');
            return res.data || {};
        },
        async transferIn(payload) {
            const res = await http.post('/api/user/api/ribao/transfer-in', payload);
            return res.data || {};
        },
        async transferOut(payload) {
            const res = await http.post('/api/user/api/ribao/transfer-out', payload);
            return res.data || {};
        },
        async getRecords(params = {}) {
            const res = await http.get('/api/user/api/ribao/records', params);
            return res.data || {};
        }
    },

    // 项目
    project: {
        /**
         * 获取项目列表
         */
        async getList(params = {}) {
            const res = await http.get('/api/project/index', params);
            return res;
        },
        /**
         * 获取项目详情
         */
        async getDetail(projectId) {
            const res = await http.get('/api/project/detail', { id: projectId });
            return res;
        }
    },

    // 积分
    points: {
        async getBalance() {
            const res = await http.get('/api/user/points/balance');
            return res.data || {};
        },
        async exchange(payload) {
            const res = await http.post('/api/user/points/exchange', payload);
            return res.data || {};
        },
        async getLogs(params = {}) {
            const res = await http.get('/api/user/points/logs', params);
            return res.data || {};
        }
    },

    // 财务
    finance: {
        async getUserBalance() {
            const res = await http.get('/api/user/info');
            // 标准化响应：兼容code=200和code=1
            if (res.code === 200) {
                res.code = 1;
            }
            return res; // 返回完整响应（包含code字段）
        },
        async recharge(payload) {
            const res = await http.post('/api/recharge/add', payload);
            return res;
        },
        async withdraw(payload) {
            const res = await http.post('/api/withdraw/create', payload);  // 修正路径
            return res.data || {};
        },
        async getBankList() {
            const res = await http.get('/api/pay/bank/list');
            return res.data || {};
        },
        async getUsdtInfo() {
            const res = await http.get('/api/pay/us/info');
            return res.data || {};
        }
    }
};

// ===================================
// 向后兼容：导出多种接口
// ===================================

// 1. 统一 httpClient（推荐使用）
window.httpClient = {
    async get(path, params = {}) {
        const res = await http.get(path, params);
        // 自动标准化字段
        return FieldNormalizer.normalizeResponse(res).data || res.data || res;
    },
    async post(path, data = {}) {
        const res = await http.post(path, data);
        return FieldNormalizer.normalizeResponse(res).data || res.data || res;
    }
};

// 2. ApiService（推荐使用）
window.ApiService = ApiService;

// 3. APIClient（兼容旧代码）
window.APIClient = class {
    constructor() {
        this.http = http;
        this.api = ApiService;
    }
    async get(url, params) {
        const res = await http.get(url, params);
        return FieldNormalizer.normalizeResponse(res);
    }
    async post(url, data) {
        const res = await http.post(url, data);
        return FieldNormalizer.normalizeResponse(res);
    }
};

// 4. TokenManager 兼容
if (!window.TokenManager) {
    window.TokenManager = TokenService;
}

// 5. 全局 Token 函数（兼容旧代码）
window.getToken = () => TokenService.getToken();
window.setToken = (token) => TokenService.setToken(token);
window.removeToken = () => TokenService.removeToken();

// ===================================
// 启动日志
// ===================================
if (API_CONFIG.debug) {
    console.log('[网络层] 统一HTTP客户端已初始化');
    console.log('[网络层] Token服务已初始化');
    console.log('[网络层] 字段标准化器已初始化');
}
