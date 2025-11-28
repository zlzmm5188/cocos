/**
 * Providence Admin Mock API Server
 * 模拟API服务器 - 用于开发和测试
 * 
 * 使用方法：
 * 1. 安装Node.js
 * 2. 运行: node admin/api/mock-server.cjs
 * 3. 访问: http://localhost:3000/admin/
 * 
 * 环境变量:
 * - ADMIN_USERNAME: 管理员用户名 (默认: admin)
 * - ADMIN_PASSWORD: 管理员密码 (默认: admin123)
 * - PORT: 服务端口 (默认: 3000)
 * 
 * 警告: 此Mock服务器仅用于开发测试，请勿在生产环境使用！
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

// Configuration from environment variables with defaults
const PORT = process.env.PORT || 3000;
const MOCK_CREDENTIALS = {
    username: process.env.ADMIN_USERNAME || 'admin',
    password: process.env.ADMIN_PASSWORD || 'admin123'
};

// MIME types
const MIME_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon'
};

// Mock data generators
function generateMockUsers(page = 1, pageSize = 20) {
    const users = [];
    const start = (page - 1) * pageSize;
    for (let i = 0; i < pageSize; i++) {
        const id = 10000 + start + i;
        users.push({
            id: id,
            uid: 'UID' + id,
            phone: '138****' + String(1000 + i).padStart(4, '0'),
            username: 'user_' + id,
            realname: ['张', '李', '王', '赵', '陈'][i % 5] + '**',
            vipLevel: Math.floor(Math.random() * 9),
            money: Math.random() * 1000000,
            totalInvest: Math.random() * 5000000,
            kycStatus: Math.floor(Math.random() * 4),
            createdAt: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString()
        });
    }
    return { list: users, total: 156, page, pageSize };
}

function generateMockRecharges(page = 1, pageSize = 20) {
    const methods = ['alipay', 'wechat', 'bank', 'usdt'];
    const methodNames = { alipay: '支付宝', wechat: '微信', bank: '银行卡', usdt: 'USDT' };
    const statuses = ['pending', 'approved', 'rejected'];
    
    const records = [];
    const start = (page - 1) * pageSize;
    for (let i = 0; i < pageSize; i++) {
        const method = methods[Math.floor(Math.random() * methods.length)];
        const status = i < 5 ? 'pending' : statuses[Math.floor(Math.random() * statuses.length)];
        records.push({
            id: 1000 + start + i,
            orderNo: 'RCH' + Date.now().toString().slice(-10) + i,
            userId: 10000 + Math.floor(Math.random() * 1000),
            username: 'user_' + (10000 + Math.floor(Math.random() * 1000)),
            phone: '138****' + String(1000 + i).padStart(4, '0'),
            amount: [1000, 5000, 10000, 20000, 50000][Math.floor(Math.random() * 5)],
            method: method,
            methodName: methodNames[method],
            status: status,
            createdAt: new Date(Date.now() - Math.random() * 3 * 24 * 60 * 60 * 1000).toISOString()
        });
    }
    return { list: records, total: 85, page, pageSize };
}

function generateMockWithdrawals(page = 1, pageSize = 20) {
    const statuses = ['pending', 'approved', 'paid', 'rejected'];
    
    const records = [];
    const start = (page - 1) * pageSize;
    for (let i = 0; i < pageSize; i++) {
        const type = Math.random() > 0.3 ? 'bank' : 'usdt';
        const amount = [1000, 5000, 10000, 20000, 30000][Math.floor(Math.random() * 5)];
        const fee = type === 'usdt' ? 0 : Math.floor(amount * 0.01);
        const status = i < 3 ? 'pending' : (i < 5 ? 'approved' : statuses[Math.floor(Math.random() * statuses.length)]);
        
        records.push({
            id: 2000 + start + i,
            orderNo: 'WTD' + Date.now().toString().slice(-10) + i,
            userId: 10000 + Math.floor(Math.random() * 1000),
            username: 'user_' + (10000 + Math.floor(Math.random() * 1000)),
            phone: '138****' + String(1000 + i).padStart(4, '0'),
            amount: amount,
            fee: fee,
            actualAmount: amount - fee,
            type: type,
            bankInfo: type === 'bank' ? {
                bankName: ['工商银行', '建设银行', '招商银行'][Math.floor(Math.random() * 3)],
                cardNo: '**** **** **** ' + String(1000 + i).padStart(4, '0'),
                holderName: '张**'
            } : null,
            usdtInfo: type === 'usdt' ? {
                address: 'T' + Math.random().toString(36).substring(2, 35).toUpperCase(),
                chain: 'TRC20'
            } : null,
            status: status,
            createdAt: new Date(Date.now() - Math.random() * 3 * 24 * 60 * 60 * 1000).toISOString()
        });
    }
    return { list: records, total: 65, page, pageSize };
}

function generateDashboardStats() {
    return {
        totalUsers: 12568,
        todayUsers: 45,
        totalInvest: 125680000,
        todayInvest: 1580000,
        pendingRecharges: 8,
        pendingRechargeAmount: 156000,
        pendingWithdrawals: 5,
        pendingWithdrawAmount: 89000,
        pendingKyc: 3,
        todayOrders: 28,
        todayProfit: 35600,
        totalProfit: 2580000
    };
}

// API request handler
function handleApiRequest(req, res, pathname, body) {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Token');
    res.setHeader('Content-Type', 'application/json; charset=utf-8');
    
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }
    
    // Parse the API path
    const apiPath = pathname.replace('/index.php', '').replace('/api', '');
    
    let response = { code: 1, msg: 'success', data: null };
    
    try {
        // Route handling
        if (apiPath.includes('/admin/login')) {
            // Login - use credentials from environment or defaults
            const data = JSON.parse(body || '{}');
            if (data.username === MOCK_CREDENTIALS.username && data.password === MOCK_CREDENTIALS.password) {
                response.data = {
                    token: 'admin_' + Date.now(),
                    admin: { id: 1, username: MOCK_CREDENTIALS.username, name: '超级管理员', role: 'super_admin' }
                };
            } else {
                response = { code: -1, msg: '账号或密码错误', data: null };
            }
        } else if (apiPath.includes('/admin/stats')) {
            response.data = generateDashboardStats();
        } else if (apiPath.includes('/admin/users')) {
            response.data = generateMockUsers();
        } else if (apiPath.includes('/admin/recharges')) {
            response.data = generateMockRecharges();
        } else if (apiPath.includes('/admin/withdrawals')) {
            response.data = generateMockWithdrawals();
        } else if (apiPath.includes('/admin/recharge-approve') || apiPath.includes('/admin/recharge-reject')) {
            response.msg = '操作成功';
        } else if (apiPath.includes('/admin/withdraw-approve') || apiPath.includes('/admin/withdraw-reject') || apiPath.includes('/admin/withdraw-pay')) {
            response.msg = '操作成功';
        } else if (apiPath.includes('/admin/kyc-approve') || apiPath.includes('/admin/kyc-reject')) {
            response.msg = '操作成功';
        } else if (apiPath.includes('/admin/user-update') || apiPath.includes('/admin/user-adjust-balance')) {
            response.msg = '操作成功';
        } else if (apiPath.includes('/admin/project-save') || apiPath.includes('/admin/project-delete')) {
            response.msg = '操作成功';
        } else if (apiPath.includes('/admin/settings')) {
            response.data = {
                siteName: 'Providence',
                siteDesc: '专业的金融投资管理平台',
                servicePhone: '400-888-8888',
                serviceEmail: 'support@providence.com',
                minRecharge: 100,
                minWithdraw: 100,
                maxWithdraw: 50000,
                withdrawFee: 1,
                withdrawLimit: 3,
                ribaoRate: 3.65,
                usdtRate: 7.20,
                usdtBonus: 2
            };
        } else {
            // Default - return empty success
            response.data = {};
        }
    } catch (error) {
        response = { code: -1, msg: error.message, data: null };
    }
    
    res.writeHead(200);
    res.end(JSON.stringify(response));
}

// Static file server
function serveStaticFile(res, filePath) {
    const ext = path.extname(filePath).toLowerCase();
    const contentType = MIME_TYPES[ext] || 'application/octet-stream';
    
    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.writeHead(404);
            res.end('Not Found');
            return;
        }
        res.writeHead(200, { 'Content-Type': contentType });
        res.end(data);
    });
}

// Create server
const server = http.createServer((req, res) => {
    const url = new URL(req.url, `http://localhost:${PORT}`);
    const pathname = url.pathname;
    
    console.log(`[${new Date().toISOString()}] ${req.method} ${pathname}`);
    
    // Handle API requests
    if (pathname.includes('/index.php') || pathname.startsWith('/api/')) {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            handleApiRequest(req, res, pathname, body);
        });
        return;
    }
    
    // Serve static files
    let filePath = pathname;
    
    // Default to admin pages
    if (filePath === '/' || filePath === '/admin' || filePath === '/admin/') {
        filePath = '/admin/index.html';
    }
    
    // Handle relative paths in admin
    if (!filePath.startsWith('/admin') && !filePath.startsWith('/img') && !filePath.startsWith('/css')) {
        filePath = '/admin' + filePath;
    }
    
    const fullPath = path.join(__dirname, '../..', filePath);
    
    if (fs.existsSync(fullPath) && fs.statSync(fullPath).isFile()) {
        serveStaticFile(res, fullPath);
    } else if (fs.existsSync(fullPath + '.html')) {
        serveStaticFile(res, fullPath + '.html');
    } else {
        res.writeHead(404);
        res.end('Not Found');
    }
});

server.listen(PORT, () => {
    console.log(`
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║     Providence Admin Mock Server                             ║
║     http://localhost:${PORT}                                    ║
║                                                              ║
║     Admin Panel: http://localhost:${PORT}/admin                 ║
║     Login: admin / admin123                                  ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
    `);
});
