# Providence Admin Backend API

完整的后台管理API系统，用于管理资金盘返利平台的所有业务功能。

## 核心功能

- **多币种支持**：CNY（人民币）和 USDT 分离管理
- **二级返利系统**：VIP等级邀请返利，项目结束后发放
- **VIP等级权益**：升级条件、额外加息、签到积分、团队奖励
- **日利宝储蓄**：CNY/USDT 独立账户，每日收益派发
- **币种兑换**：CNY→USDT（单向）、积分→CNY

## 目录结构

```
admin/backend/
├── api.php                  # API入口文件
├── config.php               # 配置文件
├── database.php             # 数据库操作类
├── database-extend.sql      # 扩展数据库（多币种+返佣）
├── auth.php                 # JWT认证类
├── helpers.php              # 辅助函数
├── controllers/             # 控制器目录
│   ├── AuthController.php         # 认证
│   ├── DashboardController.php    # 仪表盘
│   ├── UserController.php         # 用户管理
│   ├── RechargeController.php     # 充值审核
│   ├── WithdrawController.php     # 提现审核
│   ├── KycController.php          # KYC审核
│   ├── ProductController.php      # 产品管理
│   ├── OrderController.php        # 订单管理
│   ├── TeamController.php         # 团队管理
│   ├── ConfigController.php       # 系统配置
│   ├── LogController.php          # 日志管理
│   ├── RibaoController.php        # 日利宝管理（多币种）
│   ├── VipConfigController.php    # VIP完整配置
│   ├── CommissionController.php   # 返佣管理（二级返利）
│   └── CurrencyController.php     # 币种管理
└── README.md
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
# 基础表结构
mysql -u root -p providence < database-design.sql

# 扩展表结构（多币种+返佣）
mysql -u root -p providence < admin/backend/database-extend.sql
```

### 3. 配置Web服务器

**Nginx 配置示例：**

```nginx
location /admin/backend/ {
    try_files $uri $uri/ /admin/backend/api.php?$query_string;
}
```

### 4. 默认管理员

- 用户名：`admin`
- 密码：`admin123`

## VIP等级权益配置

### 邀请返利比例

| VIP等级 | 一级返利 | 二级返利 |
|--------|---------|---------|
| VIP0 | 1% | 0% |
| VIP1 | 2% | 1% |
| VIP2 | 3% | 2% |
| VIP3 | 4% | 2% |
| VIP4 | 5% | 3% |
| VIP5 | 5% | 4% |
| VIP6 | 6% | 4% |
| VIP7 | 6% | 5% |
| VIP8 | 7% | 5% |

**返利规则**：
- 只能获得下面二级用户购买项目金额的返利
- 返利在项目结束后发放

### VIP升级条件

| VIP等级 | 累计投资 | 额外加息 |
|--------|---------|---------|
| VIP1 | ¥30,000 | 0.05% |
| VIP2 | ¥100,000 | 0.10% |
| VIP3 | ¥250,000 | 0.12% |
| VIP4 | ¥800,000 | 0.15% |
| VIP5 | ¥1,500,000 | 0.16% |
| VIP6 | ¥3,800,000 | 0.18% |
| VIP7 | ¥8,000,000 | 0.23% |
| VIP8 | ¥13,000,000 | 0.25% |

### 每日签到积分

| VIP等级 | 积分 |
|--------|------|
| VIP0 | 6 |
| VIP1 | 10 |
| VIP2 | 16 |
| VIP3 | 20 |
| VIP4 | 30 |
| VIP5 | 40 |
| VIP6 | 50 |
| VIP7 | 60 |
| VIP8 | 70 |

### 团队管理奖

| 下级成员 | 累计投资 | 积分奖励 |
|---------|---------|---------|
| 3人 | ¥80,000 | 2,000 |
| 5人 | ¥150,000 | 3,900 |
| 10人 | ¥500,000 | 12,000 |
| 20人 | ¥1,500,000 | 35,000 |
| 50人 | ¥3,800,000 | 50,000 |
| 100人 | ¥8,800,000 | 75,000 |
| 200人 | ¥15,000,000 | 150,000 |
| 500人 | ¥58,000,000 | 200,000 |
| 1000人 | ¥98,000,000 | 380,000 |

## API 接口列表

### 认证接口

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | /admin/login | 管理员登录 |
| POST | /admin/logout | 退出登录 |
| GET | /admin/info | 获取当前管理员信息 |
| POST | /admin/change-password | 修改密码 |

### VIP完整配置（新增）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/vip-full-config | 获取完整VIP配置 |
| POST | /admin/vip-invite-rewards | 保存邀请返利配置 |
| POST | /admin/vip-upgrade-rules | 保存升级规则配置 |
| POST | /admin/vip-checkin-points | 保存签到积分配置 |
| POST | /admin/vip-team-awards | 保存团队管理奖配置 |

### 返佣管理（新增）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/commissions | 返佣记录列表 |
| GET | /admin/commissions/pending | 待发放返佣列表 |
| GET | /admin/commissions/stats | 返佣统计 |
| POST | /admin/commission-pay | 发放单条返佣 |
| POST | /admin/commission-batch-pay | 批量发放返佣 |
| POST | /admin/commission-auto-distribute | 自动发放已完成项目返佣 |

### 币种管理（新增）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/currency-config | 获取币种配置 |
| POST | /admin/currency-config | 保存币种配置 |
| GET | /admin/currency-exchanges | 兑换记录列表 |
| POST | /admin/currency-exchange-review | 审核兑换申请 |
| GET | /admin/user-balances | 用户多币种余额 |
| POST | /admin/adjust-currency-balance | 调整币种余额 |

### 日利宝管理（增强）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/ribao-users | 日利宝用户列表 |
| POST | /admin/ribao-distribute | 派发收益（支持CNY/USDT） |
| GET | /admin/ribao-earnings | 收益记录 |
| GET | /admin/ribao-transfers | 转入转出记录 |
| GET | /admin/ribao-stats | 日利宝统计 |

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

### 充值/提现/KYC审核

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/recharges | 充值列表 |
| POST | /admin/recharge-approve | 审核通过 |
| POST | /admin/recharge-reject | 审核拒绝 |
| GET | /admin/withdrawals | 提现列表 |
| POST | /admin/withdraw-approve | 审核通过 |
| POST | /admin/withdraw-reject | 审核拒绝 |
| POST | /admin/withdraw-pay | 确认打款 |
| GET | /admin/kyc-list | KYC列表 |
| POST | /admin/kyc-approve | 审核通过 |
| POST | /admin/kyc-reject | 审核拒绝 |

### 产品/订单/团队管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /admin/products | 产品列表 |
| POST | /admin/project-save | 保存产品 |
| GET | /admin/orders | 订单列表 |
| GET | /admin/teams | 团队列表 |
| GET | /admin/teams/{id}/members | 团队成员 |

## 币种配置

### 默认配置

```json
{
  "cny_to_usdt_enabled": true,
  "cny_to_usdt_rate": 7.2,
  "cny_to_usdt_min": 100,
  "cny_to_usdt_max": 100000,
  "cny_to_usdt_fee_rate": 0.002,
  "points_to_cny_enabled": true,
  "points_to_cny_rate": 0.5,
  "points_to_cny_min": 100,
  "usdt_daily_rate": 0.002,
  "cny_daily_rate": 0.001
}
```

### 说明

- **CNY兑换USDT**：单向兑换，汇率默认7.2，手续费0.2%
- **积分兑换CNY**：0.5积分 = 1元人民币
- **日利宝利率**：CNY 0.1%/天，USDT 0.2%/天

## 数据库表

### 新增表

- `commission_records` - 返佣记录表
- `currency_exchange_records` - 币种兑换记录表
- `ribao_transfer_records` - 日利宝转入转出记录
- `ribao_earnings` - 日利宝收益记录
- `checkin_records` - 签到记录表
- `team_award_records` - 团队奖领取记录

### 用户表扩展字段

- `usdt_balance` - USDT余额
- `ribao_cny_balance` - 日利宝CNY余额
- `ribao_usdt_balance` - 日利宝USDT余额
- `total_invest` - 累计投资（用于VIP升级）
- `team_award_level` - 已领取的团队奖等级

## 权限说明

| 权限标识 | 说明 |
|---------|------|
| user_edit | 编辑用户 |
| user_balance | 调整余额 |
| user_vip | 设置VIP |
| config_edit | 编辑配置 |
| commission_pay | 发放返佣 |
| ribao_manage | 日利宝管理 |
| finance_approve | 财务审批 |

超级管理员（super_admin）拥有所有权限。

## 版本信息

- 版本：2.0.0
- 更新：2024-11-29
- 框架：原生PHP（无第三方依赖）
- 新增：多币种支持、二级返利系统、VIP完整配置
