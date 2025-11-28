/**
 * Providence Admin Config
 * 后台管理系统配置文件
 */

window.ADMIN_CONFIG = {
    // API Base URL - 使用与前台相同的API配置
    apiBase: window.API_CONFIG?.baseURL || 'https://api.4kp3l0iq.top',
    
    // Admin API prefix
    adminApiPrefix: '/index.php/admin',
    
    // Token key in localStorage
    tokenKey: 'admin_token',
    
    // Admin info key in localStorage
    adminInfoKey: 'admin_info',
    
    // Session timeout (ms) - 30 minutes
    sessionTimeout: 30 * 60 * 1000,
    
    // Default page size
    pageSize: 20,
    
    // Debug mode
    debug: true,
    
    // Version
    version: '1.0.0'
};

// Admin API Request Helper
window.AdminAPI = {
    /**
     * Get admin token
     */
    getToken() {
        return localStorage.getItem(ADMIN_CONFIG.tokenKey) || '';
    },
    
    /**
     * Get admin info
     */
    getAdminInfo() {
        try {
            return JSON.parse(localStorage.getItem(ADMIN_CONFIG.adminInfoKey) || '{}');
        } catch (e) {
            return {};
        }
    },
    
    /**
     * Check if logged in
     */
    isLoggedIn() {
        return !!this.getToken();
    },
    
    /**
     * Logout
     */
    logout() {
        localStorage.removeItem(ADMIN_CONFIG.tokenKey);
        localStorage.removeItem(ADMIN_CONFIG.adminInfoKey);
        window.location.href = 'login.html';
    },
    
    /**
     * API Request
     */
    async request(endpoint, options = {}) {
        const token = this.getToken();
        if (!token && !options.skipAuth) {
            this.logout();
            throw new Error('未登录');
        }
        
        const url = ADMIN_CONFIG.apiBase + ADMIN_CONFIG.adminApiPrefix + endpoint;
        
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        if (token) {
            headers['Token'] = token;
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        if (ADMIN_CONFIG.debug) {
            console.log('[AdminAPI] Request:', options.method || 'GET', url);
        }
        
        try {
            const response = await fetch(url, {
                ...options,
                headers
            });
            
            const text = await response.text();
            
            // Check for HTML error page
            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                console.error('[AdminAPI] Received HTML error page');
                throw new Error('服务器返回错误页面');
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('[AdminAPI] JSON parse error:', text.substring(0, 200));
                throw new Error('响应格式错误');
            }
            
            if (ADMIN_CONFIG.debug) {
                console.log('[AdminAPI] Response:', data);
            }
            
            // Handle auth errors
            if (data.code === 401 || data.code === -1 && data.msg?.includes('登录')) {
                this.logout();
                throw new Error('登录已过期');
            }
            
            return data;
        } catch (error) {
            console.error('[AdminAPI] Request error:', error);
            throw error;
        }
    },
    
    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    },
    
    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// Toast notifications
window.AdminToast = {
    container: null,
    
    init() {
        // Check if container already exists in DOM
        const existing = document.querySelector('.toast-container');
        if (existing) {
            this.container = existing;
            return;
        }
        
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'success', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        toast.innerHTML = `
            <i class="${iconMap[type] || iconMap.info}"></i>
            <span>${message}</span>
        `;
        
        this.container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    success(message) {
        this.show(message, 'success');
    },
    
    error(message) {
        this.show(message, 'error');
    },
    
    warning(message) {
        this.show(message, 'warning');
    },
    
    info(message) {
        this.show(message, 'info');
    }
};

// Add animation style
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Utils
window.AdminUtils = {
    /**
     * Format money
     */
    formatMoney(value, decimals = 2) {
        const num = Number(value) || 0;
        return num.toLocaleString('zh-CN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    },
    
    /**
     * Format date
     */
    formatDate(date, format = 'YYYY-MM-DD HH:mm:ss') {
        if (!date) return '-';
        const d = new Date(date);
        if (isNaN(d.getTime())) return '-';
        
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes)
            .replace('ss', seconds);
    },
    
    /**
     * Get VIP badge HTML
     */
    getVipBadge(level) {
        level = Number(level) || 0;
        return `<span class="vip-badge vip-${level}">VIP${level}</span>`;
    },
    
    /**
     * Get KYC status HTML
     */
    getKycStatus(status) {
        const statusMap = {
            0: { text: '未认证', class: 'kyc-none' },
            1: { text: '审核中', class: 'kyc-pending' },
            2: { text: '已认证', class: 'kyc-approved' },
            3: { text: '已拒绝', class: 'kyc-rejected' }
        };
        const s = statusMap[status] || statusMap[0];
        return `<span class="kyc-status ${s.class}">${s.text}</span>`;
    },
    
    /**
     * Get order status tag
     */
    getOrderStatus(status) {
        const statusMap = {
            'active': { text: '进行中', class: 'tag-blue' },
            'RUNNING': { text: '进行中', class: 'tag-blue' },
            'completed': { text: '已完成', class: 'tag-success' },
            'COMPLETED': { text: '已完成', class: 'tag-success' },
            'cancelled': { text: '已取消', class: 'tag-default' },
            'CANCELLED': { text: '已取消', class: 'tag-default' }
        };
        const s = statusMap[status] || { text: status, class: 'tag-default' };
        return `<span class="tag ${s.class}">${s.text}</span>`;
    },
    
    /**
     * Get payment status tag
     */
    getPaymentStatus(status) {
        const statusMap = {
            'pending': { text: '待审核', class: 'tag-warning' },
            'PENDING': { text: '待审核', class: 'tag-warning' },
            'approved': { text: '已通过', class: 'tag-success' },
            'APPROVED': { text: '已通过', class: 'tag-success' },
            'rejected': { text: '已拒绝', class: 'tag-danger' },
            'REJECTED': { text: '已拒绝', class: 'tag-danger' },
            'paid': { text: '已到账', class: 'tag-success' },
            'PAID': { text: '已到账', class: 'tag-success' }
        };
        const s = statusMap[status] || { text: status, class: 'tag-default' };
        return `<span class="tag ${s.class}">${s.text}</span>`;
    },
    
    /**
     * Mask phone number
     */
    maskPhone(phone) {
        if (!phone || phone.length < 7) return phone;
        return phone.substring(0, 3) + '****' + phone.substring(phone.length - 4);
    },
    
    /**
     * Mask ID card
     */
    maskIdCard(idcard) {
        if (!idcard || idcard.length < 10) return idcard;
        return idcard.substring(0, 6) + '********' + idcard.substring(idcard.length - 4);
    },
    
    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            AdminToast.success('复制成功');
        } catch (e) {
            AdminToast.error('复制失败');
        }
    },
    
    /**
     * Debounce function
     */
    debounce(fn, delay = 300) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },
    
    /**
     * Confirm dialog
     */
    confirm(message, title = '确认') {
        return new Promise((resolve) => {
            const confirmed = window.confirm(`${title}\n\n${message}`);
            resolve(confirmed);
        });
    }
};

console.log('[AdminConfig] Loaded v' + ADMIN_CONFIG.version);
