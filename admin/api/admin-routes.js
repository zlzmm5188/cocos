/**
 * Providence Admin API Routes
 * 后台管理API路由
 * 
 * 这个文件定义了后台管理的所有API接口
 * 可以与ThinkPHP后端集成，或作为独立的Node.js服务运行
 */

// API路径映射 - 管理员接口
window.ADMIN_API_ROUTES = {
    // 认证相关
    AUTH: {
        LOGIN: '/index.php/admin/login',           // POST - 管理员登录
        LOGOUT: '/index.php/admin/logout',         // POST - 管理员登出
        INFO: '/index.php/admin/info',             // GET - 获取管理员信息
        CHANGE_PASSWORD: '/index.php/admin/change-password' // POST - 修改密码
    },
    
    // 仪表盘
    DASHBOARD: {
        STATS: '/index.php/admin/stats',           // GET - 获取统计数据
        RECENT_ORDERS: '/index.php/admin/recent-orders', // GET - 最近订单
        RECENT_ACTIVITY: '/index.php/admin/recent-activity', // GET - 最近活动
        CHARTS: '/index.php/admin/charts'          // GET - 图表数据
    },
    
    // 用户管理
    USERS: {
        LIST: '/index.php/admin/users',            // GET - 用户列表
        DETAIL: '/index.php/admin/user-detail',    // GET - 用户详情
        UPDATE: '/index.php/admin/user-update',    // POST - 更新用户
        ADJUST_BALANCE: '/index.php/admin/user-adjust-balance', // POST - 调整余额
        SET_VIP: '/index.php/admin/user-set-vip',  // POST - 设置VIP等级
        SET_INTERNAL: '/index.php/admin/user-set-internal', // POST - 设置内部用户
        RESET_PASSWORD: '/index.php/admin/user-reset-password', // POST - 重置密码
        TEAM_TREE: '/index.php/admin/user-team-tree' // GET - 用户团队树
    },
    
    // KYC实名认证
    KYC: {
        LIST: '/index.php/admin/kyc-list',         // GET - 认证申请列表
        DETAIL: '/index.php/admin/kyc-detail',     // GET - 认证详情
        APPROVE: '/index.php/admin/kyc-approve',   // POST - 通过认证
        REJECT: '/index.php/admin/kyc-reject'      // POST - 拒绝认证
    },
    
    // 团队管理
    TEAMS: {
        LIST: '/index.php/admin/teams',            // GET - 团队列表
        DETAIL: '/index.php/admin/team-detail',    // GET - 团队详情
        TREE: '/index.php/admin/team-tree',        // GET - 团队树形结构
        COMMISSION_LOGS: '/index.php/admin/commission-logs' // GET - 返佣记录
    },
    
    // 产品管理
    PRODUCTS: {
        LIST: '/index.php/admin/products',         // GET - 产品列表
        DETAIL: '/index.php/admin/product-detail', // GET - 产品详情
        SAVE: '/index.php/admin/project-save',     // POST - 保存产品
        DELETE: '/index.php/admin/project-delete', // POST - 删除产品
        TOGGLE_STATUS: '/index.php/admin/project-toggle' // POST - 切换状态
    },
    
    // 订单管理
    ORDERS: {
        LIST: '/index.php/admin/orders',           // GET - 订单列表
        DETAIL: '/index.php/admin/order-detail',   // GET - 订单详情
        COMPLETE: '/index.php/admin/order-complete', // POST - 完成订单
        CANCEL: '/index.php/admin/order-cancel'    // POST - 取消订单
    },
    
    // 充值管理
    RECHARGES: {
        LIST: '/index.php/admin/recharges',        // GET - 充值记录
        DETAIL: '/index.php/admin/recharge-detail', // GET - 充值详情
        APPROVE: '/index.php/admin/recharge-approve', // POST - 通过充值
        REJECT: '/index.php/admin/recharge-reject', // POST - 拒绝充值
        STATS: '/index.php/admin/recharge-stats'   // GET - 充值统计
    },
    
    // 提现管理
    WITHDRAWALS: {
        LIST: '/index.php/admin/withdrawals',      // GET - 提现记录
        DETAIL: '/index.php/admin/withdraw-detail', // GET - 提现详情
        APPROVE: '/index.php/admin/withdraw-approve', // POST - 通过提现
        REJECT: '/index.php/admin/withdraw-reject', // POST - 拒绝提现
        PAY: '/index.php/admin/withdraw-pay',      // POST - 确认打款
        STATS: '/index.php/admin/withdraw-stats'   // GET - 提现统计
    },
    
    // 日利宝管理
    RIBAO: {
        USERS: '/index.php/admin/ribao/users',     // GET - 日利宝用户列表
        PROFITS: '/index.php/admin/ribao/profits', // GET - 收益记录
        CONFIG: '/index.php/admin/ribao/config',   // GET - 日利宝配置
        CONFIG_SAVE: '/index.php/admin/ribao/config-save', // POST - 保存配置
        DISPATCH_PROFIT: '/index.php/admin/ribao/dispatch' // POST - 手动派息
    },
    
    // 交易记录
    TRANSACTIONS: {
        LIST: '/index.php/admin/transactions',     // GET - 交易记录
        WALLET_LOGS: '/index.php/admin/wallet-logs', // GET - 钱包日志
        EXPORT: '/index.php/admin/transactions-export' // GET - 导出交易
    },
    
    // VIP配置
    VIP: {
        LIST: '/index.php/admin/vip-config',       // GET - VIP配置列表
        SAVE: '/index.php/admin/vip-config-save',  // POST - 保存VIP配置
        STATS: '/index.php/admin/vip-stats'        // GET - VIP用户统计
    },
    
    // 系统设置
    SETTINGS: {
        GET: '/index.php/admin/settings',          // GET - 获取设置
        SAVE: '/index.php/admin/settings-save',    // POST - 保存设置
        USDT_CONFIG: '/index.php/admin/usdt-config', // GET - USDT配置
        USDT_CONFIG_SAVE: '/index.php/admin/usdt-config-save' // POST - 保存USDT配置
    },
    
    // 操作日志
    LOGS: {
        LIST: '/index.php/admin/logs',             // GET - 操作日志
        LOGIN_LOGS: '/index.php/admin/login-logs'  // GET - 登录日志
    },
    
    // 活动管理
    ACTIVITIES: {
        LIST: '/index.php/admin/activities',       // GET - 活动列表
        DETAIL: '/index.php/admin/activity-detail', // GET - 活动详情
        SAVE: '/index.php/admin/activity-save',    // POST - 保存活动
        DELETE: '/index.php/admin/activity-delete' // POST - 删除活动
    },
    
    // 文章管理
    ARTICLES: {
        LIST: '/index.php/admin/articles',         // GET - 文章列表
        DETAIL: '/index.php/admin/article-detail', // GET - 文章详情
        SAVE: '/index.php/admin/article-save',     // POST - 保存文章
        DELETE: '/index.php/admin/article-delete'  // POST - 删除文章
    }
};

/**
 * 管理员API请求方法扩展
 */
if (typeof AdminAPI !== 'undefined') {
    // 用户管理
    AdminAPI.users = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.USERS.LIST, params);
        },
        async detail(userId) {
            return AdminAPI.get(ADMIN_API_ROUTES.USERS.DETAIL, { id: userId });
        },
        async update(data) {
            return AdminAPI.post(ADMIN_API_ROUTES.USERS.UPDATE, data);
        },
        async adjustBalance(userId, type, amount, remark) {
            return AdminAPI.post(ADMIN_API_ROUTES.USERS.ADJUST_BALANCE, { 
                user_id: userId, type, amount, remark 
            });
        },
        async setVip(userId, vipLevel) {
            return AdminAPI.post(ADMIN_API_ROUTES.USERS.SET_VIP, { 
                user_id: userId, vip_level: vipLevel 
            });
        }
    };
    
    // 充值管理
    AdminAPI.recharges = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.RECHARGES.LIST, params);
        },
        async approve(id, actualAmount, remark) {
            return AdminAPI.post(ADMIN_API_ROUTES.RECHARGES.APPROVE, { 
                id, actual_amount: actualAmount, remark 
            });
        },
        async reject(id, reason) {
            return AdminAPI.post(ADMIN_API_ROUTES.RECHARGES.REJECT, { id, reason });
        }
    };
    
    // 提现管理
    AdminAPI.withdrawals = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.WITHDRAWALS.LIST, params);
        },
        async approve(id, remark) {
            return AdminAPI.post(ADMIN_API_ROUTES.WITHDRAWALS.APPROVE, { id, remark });
        },
        async reject(id, reason) {
            return AdminAPI.post(ADMIN_API_ROUTES.WITHDRAWALS.REJECT, { id, reason });
        },
        async pay(id, transactionId) {
            return AdminAPI.post(ADMIN_API_ROUTES.WITHDRAWALS.PAY, { 
                id, transaction_id: transactionId 
            });
        }
    };
    
    // KYC管理
    AdminAPI.kyc = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.KYC.LIST, params);
        },
        async approve(id) {
            return AdminAPI.post(ADMIN_API_ROUTES.KYC.APPROVE, { id });
        },
        async reject(id, reason) {
            return AdminAPI.post(ADMIN_API_ROUTES.KYC.REJECT, { id, reason });
        }
    };
    
    // 产品管理
    AdminAPI.products = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.PRODUCTS.LIST, params);
        },
        async save(data) {
            return AdminAPI.post(ADMIN_API_ROUTES.PRODUCTS.SAVE, data);
        },
        async delete(id) {
            return AdminAPI.post(ADMIN_API_ROUTES.PRODUCTS.DELETE, { id });
        },
        async toggle(id) {
            return AdminAPI.post(ADMIN_API_ROUTES.PRODUCTS.TOGGLE_STATUS, { id });
        }
    };
    
    // 订单管理
    AdminAPI.orders = {
        async list(params = {}) {
            return AdminAPI.get(ADMIN_API_ROUTES.ORDERS.LIST, params);
        },
        async complete(id) {
            return AdminAPI.post(ADMIN_API_ROUTES.ORDERS.COMPLETE, { id });
        },
        async cancel(id) {
            return AdminAPI.post(ADMIN_API_ROUTES.ORDERS.CANCEL, { id });
        }
    };
    
    // 仪表盘
    AdminAPI.dashboard = {
        async getStats() {
            return AdminAPI.get(ADMIN_API_ROUTES.DASHBOARD.STATS);
        },
        async getRecentOrders() {
            return AdminAPI.get(ADMIN_API_ROUTES.DASHBOARD.RECENT_ORDERS);
        },
        async getCharts() {
            return AdminAPI.get(ADMIN_API_ROUTES.DASHBOARD.CHARTS);
        }
    };
    
    // VIP配置
    AdminAPI.vip = {
        async getConfig() {
            return AdminAPI.get(ADMIN_API_ROUTES.VIP.LIST);
        },
        async saveConfig(data) {
            return AdminAPI.post(ADMIN_API_ROUTES.VIP.SAVE, data);
        },
        async getStats() {
            return AdminAPI.get(ADMIN_API_ROUTES.VIP.STATS);
        }
    };
    
    // 系统设置
    AdminAPI.settings = {
        async get() {
            return AdminAPI.get(ADMIN_API_ROUTES.SETTINGS.GET);
        },
        async save(data) {
            return AdminAPI.post(ADMIN_API_ROUTES.SETTINGS.SAVE, data);
        }
    };
}

console.log('[AdminAPIRoutes] Loaded');
