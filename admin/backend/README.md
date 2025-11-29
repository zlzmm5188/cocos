# Providence Admin Backend API

完整的后台管理API系统，用于管理资金盘返利平台的所有业务功能。

## 目录结构

```
admin/backend/
├── api.php              # API入口文件
├── config.php           # 配置文件
├── database.php         # 数据库操作类
├── auth.php             # JWT认证类
├── helpers.php          # 辅助函数
├── controllers/         # 控制器目录
│   ├── AuthController.php        # 认证
│   ├── DashboardController.php   # 仪表盘
│   ├── UserController.php        # 用户管理
│   ├── RechargeController.php    # 充值审核
│   ├── WithdrawController.php    # 提现审核
│   ├── KycController.php         # KYC审核
│   ├── ProductController.php     # 产品管理
│   ├── OrderController.php       # 订单管理
│   ├── TeamController.php        # 团队管理
│   ├── ConfigController.php      # 系统配置
│   ├── LogController.php         # 日志管理
│   └── RibaoController.php       # 日利宝管理
└── README.md
```

## 快速开始

### 1. 配置数据库

编辑 `config.php` 或设置环境变量：

```php
// 方式一：直接修改 config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'providence');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');

// 方式二：使用环境变量
export DB_HOST=localhost
export DB_NAME=providence
export DB_USER=root
export DB_PASS=your_password
```

### 2. 导入数据库

```bash
mysql -u root -p providence < database-design.sql
```

### 3. 配置Web服务器

**Nginx 配置示例：**

```nginx
location /admin/backend/ {
    try_files $uri $uri/ /admin/backend/api.php?$query_string;
}
```

**Apache .htaccess：**

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ api.php [QSA,L]
```

### 4. 访问API

API基础路径：`/admin/backend/api.php`

## API 接口列表

### 认证接口

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | /admin/login | 管理员登录 |
| POST | /admin/logout | 退出登录 |
| GET | /admin/info | 获取当前管理员信息 |
| POST | /admin/change-password | 修改密码 |

### 仪表盘

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/stats | 统计数据 |
| GET | /admin/chart/invest-trend | 投资趋势 |
| GET | /admin/chart/user-distribution | 用户VIP分布 |
| GET | /admin/recent-orders | 最近订单 |
| GET | /admin/recent-activities | 最近活动 |

### 用户管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/users | 用户列表 |
| GET | /admin/users/{id} | 用户详情 |
| PUT | /admin/users/{id} | 更新用户 |
| POST | /admin/users/adjust-balance | 调整余额 |
| POST | /admin/users/set-vip | 设置VIP |
| POST | /admin/users/reset-password | 重置密码 |
| POST | /admin/users/{id}/toggle-status | 切换状态 |

### 充值审核

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/recharges | 充值列表 |
| GET | /admin/recharges/{id} | 充值详情 |
| POST | /admin/recharge-approve | 审核通过 |
| POST | /admin/recharge-reject | 审核拒绝 |

### 提现审核

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/withdrawals | 提现列表 |
| GET | /admin/withdrawals/{id} | 提现详情 |
| POST | /admin/withdraw-approve | 审核通过 |
| POST | /admin/withdraw-reject | 审核拒绝 |
| POST | /admin/withdraw-pay | 确认打款 |

### KYC审核

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/kyc-list | KYC列表 |
| GET | /admin/kyc/{id} | KYC详情 |
| POST | /admin/kyc-approve | 审核通过 |
| POST | /admin/kyc-reject | 审核拒绝 |

### 产品管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/products | 产品列表 |
| GET | /admin/products/{id} | 产品详情 |
| POST | /admin/project-save | 保存产品 |
| POST | /admin/products/{id}/toggle-status | 切换状态 |
| DELETE | /admin/products/{id} | 删除产品 |

### 订单管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/orders | 订单列表 |
| GET | /admin/orders/{id} | 订单详情 |
| GET | /admin/orders/stats | 订单统计 |

### 团队管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/teams | 团队列表 |
| GET | /admin/teams/{id} | 团队详情 |
| GET | /admin/teams/{id}/members | 团队成员 |

### 系统配置

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/vip-config | VIP配置 |
| POST | /admin/vip-config | 保存VIP配置 |
| GET | /admin/settings | 系统设置 |
| POST | /admin/settings | 保存设置 |

### 日志管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/logs | 操作日志 |
| GET | /admin/transactions | 交易记录 |

### 日利宝

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/ribao-users | 日利宝用户 |
| POST | /admin/ribao-distribute | 派发收益 |

## 请求格式

### 请求头

```
Content-Type: application/json
Authorization: Bearer {token}
```

### 分页参数

所有列表接口支持分页：

```
?page=1&page_size=20
```

### 搜索/筛选参数

```
?keyword=张三&status=1&start_date=2024-01-01&end_date=2024-12-31
```

## 响应格式

### 成功响应

```json
{
    "code": 1,
    "msg": "success",
    "data": { ... }
}
```

### 错误响应

```json
{
    "code": -1,
    "msg": "错误信息",
    "data": null
}
```

### 分页响应

```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "total": 100,
        "page": 1,
        "page_size": 20,
        "items": [ ... ]
    }
}
```

## 权限说明

| 权限标识 | 说明 |
|---------|------|
| user_edit | 编辑用户 |
| user_balance | 调整余额 |
| user_vip | 设置VIP |
| user_password | 重置密码 |
| user_status | 切换用户状态 |
| recharge_audit | 充值审核 |
| withdraw_audit | 提现审核 |
| withdraw_pay | 确认打款 |
| kyc_audit | KYC审核 |
| product_edit | 编辑产品 |
| product_delete | 删除产品 |
| config_edit | 编辑配置 |
| ribao_manage | 日利宝管理 |

超级管理员（super_admin）拥有所有权限。

## 安全注意事项

1. **生产环境必须修改 JWT_SECRET**
2. 使用 HTTPS
3. 配置正确的 CORS 来源
4. 定期备份数据库
5. 记录所有敏感操作日志

## 与前台API集成

后台API与前台API使用相同的数据库，数据结构一致：

- 用户表：`users`
- 投资订单：`user_investments`
- 充值记录：`recharge_records`
- 提现记录：`withdraw_records`
- 资金流水：`balance_logs`

## 版本信息

- 版本：1.0.0
- 更新：2024-11-29
- 框架：原生PHP（无第三方依赖）
