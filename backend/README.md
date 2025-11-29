# Providence 前台用户端 API

这是Providence金融理财平台的完整后端PHP API，用于前台用户端功能。

## PHP版本兼容性

**前台用户API（backend/）** 和 **后台管理系统（admin/backend/）** 都使用原生PHP编写，兼容：

- ✅ PHP 7.4+
- ✅ PHP 8.0+
- ✅ PHP 8.1+
- ✅ PHP 8.2+

**可以部署在同一服务器上，使用同一个PHP版本运行。**

## 目录结构

```
backend/
├── api.php                 # API统一入口
├── config.php              # 配置文件
├── database.php            # 数据库类
├── auth.php                # JWT认证
├── helpers.php             # 辅助函数
└── controllers/            # 控制器目录
    ├── AuthController.php      # 认证（登录/注册/密码）
    ├── UserController.php      # 用户信息
    ├── ProjectController.php   # 投资项目
    ├── OrderController.php     # 投资订单
    ├── FinanceController.php   # 充值/提现
    ├── TeamController.php      # 团队管理
    ├── RibaoController.php     # 日利宝
    ├── PointsController.php    # 积分系统
    ├── KycController.php       # 实名认证
    └── SystemController.php    # 系统公告
```

## 快速开始

### 1. 配置数据库

编辑 `config.php` 或设置环境变量：

```bash
export DB_HOST=localhost
export DB_NAME=providence
export DB_USER=root
export DB_PASS=your_password
```

### 2. 导入数据库

```bash
mysql -u root -p providence < database-design.sql
```

### 3. 部署

将代码部署到支持PHP的Web服务器（Apache/Nginx + PHP 7.4+）。

### 4. 访问

API地址：`http://your-domain/backend/api.php`

## API文档

### 认证接口

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | /auth/login | 用户登录 |
| POST | /auth/register | 用户注册 |
| POST | /auth/logout | 退出登录 |
| POST | /user/forgot-password | 忘记密码 |
| POST | /user/change-password | 修改密码 |

### 用户接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /user/info | 获取用户信息 |
| GET | /user/vip-progress | VIP进度 |
| GET | /user/invite | 邀请信息 |
| GET | /user/bank/list | 银行卡列表 |
| POST | /user/bind-bank-card | 绑定银行卡 |
| GET | /user/usdt-address/list | USDT地址列表 |
| POST | /user/bind-usdt-address | 绑定USDT地址 |

### 项目接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /project/index | 项目列表 |
| GET | /project/detail | 项目详情 |

### 订单接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /invest/orders | 订单列表 |
| POST | /invest/orders | 创建投资 |
| GET | /orders/earnings | 收益记录 |

### 财务接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/finance/recharge | 充值记录 |
| POST | /api/recharge/add | 提交充值 |
| GET | /api/finance/withdraw | 提现记录 |
| POST | /api/withdraw/create | 提交提现 |
| GET | /api/finance/wallet-logs | 资金流水 |

### 团队接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /team/info | 团队信息 |
| GET | /team/members | 团队成员 |
| GET | /team/rewards | 奖励记录 |

### 日利宝接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /user/ribao/head | 日利宝信息 |
| POST | /user/ribao/in | 转入 |
| POST | /user/ribao/out | 转出 |

### 积分接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/points/balance | 积分余额 |
| GET | /api/points/logs | 积分记录 |
| POST | /api/points/exchange | 积分兑换 |

## 请求示例

### 登录

```bash
curl -X POST http://your-domain/backend/api.php/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"123456"}'
```

响应：
```json
{
  "code": 1,
  "msg": "登录成功",
  "data": {
    "token": "eyJ...",
    "user": {
      "id": 1,
      "username": "test",
      "vip_level": 3
    }
  }
}
```

### 获取用户信息

```bash
curl http://your-domain/backend/api.php/user/info \
  -H "Authorization: Bearer eyJ..."
```

## Mock模式

如果数据库未配置或连接失败，API会自动切换到Mock模式，返回模拟数据用于开发测试。

测试账号：
- 用户名: `G138688` / 密码: `G138688`
- 用户名: `admin` / 密码: `admin123`
- 用户名: `test` / 密码: `test123`

## 多币种支持

- CNY（人民币）和 USDT 分开管理
- 投资项目按币种区分
- CNY→USDT 单向兑换（汇率7.2）
- 积分→CNY 兑换（0.5积分=1元）

## VIP等级配置

| VIP | 一级返利 | 二级返利 | 累计投资 | 额外加息 | 签到积分 |
|-----|---------|---------|---------|---------|---------|
| VIP0 | 1% | 0% | - | - | 6 |
| VIP1 | 2% | 1% | ¥30,000 | 0.05% | 10 |
| VIP2 | 3% | 2% | ¥100,000 | 0.10% | 16 |
| VIP3 | 4% | 2% | ¥250,000 | 0.12% | 20 |
| VIP4 | 5% | 3% | ¥800,000 | 0.15% | 30 |
| VIP5 | 5% | 4% | ¥1,500,000 | 0.16% | 40 |
| VIP6 | 6% | 4% | ¥3,800,000 | 0.18% | 50 |
| VIP7 | 6% | 5% | ¥8,000,000 | 0.23% | 60 |
| VIP8 | 7% | 5% | ¥13,000,000 | 0.25% | 70 |

## Nginx配置

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/cocos;
    index index.html index.php;

    # 前台页面
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 后台管理
    location /admin {
        try_files $uri $uri/ /admin/index.html;
    }

    # 前台API
    location /backend/ {
        try_files $uri $uri/ /backend/api.php?$query_string;
    }

    # 后台API
    location /admin/backend/ {
        try_files $uri $uri/ /admin/backend/api.php?$query_string;
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 版权信息

Providence Financial Platform © 2024-2025
