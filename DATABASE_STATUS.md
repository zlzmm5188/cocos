# 📊 Providence 数据库状态报告

**查询时间**: $(date)
**数据库**: providence
**状态**: ✅ 正常运行

---

## 📈 数据统计

| 类别 | 数量 |
|------|------|
| 用户总数 | 9 |
| 项目数量 | 12 |
| 投资订单 | 0 |
| 充值记录 | 0 |
| 提现记录 | 0 |
| 日利宝记录 | 0 |
| 公告数量 | 0 |

---

## 👥 测试账号列表

### 🔑 管理员账号
| ID | 用户名 | 手机号 | 余额(CNY) | 余额(USDT) | 邀请码 |
|----|--------|--------|-----------|------------|--------|
| 1 | **G138688** | 13800138688 | 1,000,000 | 10,000 | G138688 |

**邮箱**: founder@providence.com  
**密码**: 查看测试账号.sql文件  
**特点**: 超级管理员，余额最高

---

### 👤 普通测试账号

| ID | 用户名 | 手机号 | 余额(CNY) | 余额(USDT) | 邀请码 | 状态 |
|----|--------|--------|-----------|------------|--------|------|
| 6 | kehu002 | 13900000002 | 10,000 | 100 | 81014542 | ✅ |
| 7 | kehu003 | 13900000003 | 10,000 | 100 | 50029588 | ✅ |
| 8 | kehu004 | 13900000004 | 10,000 | 100 | 87104318 | ✅ |
| 9 | kehu005 | 13900000005 | 10,000 | 100 | 95432829 | ✅ |
| 10 | kehu006 | 13900000006 | 10,000 | 100 | 25851196 | ✅ |
| 11 | kehu007 | 13900000007 | 10,000 | 100 | 12957495 | ✅ |

**密码**: 统一密码（查看测试账号.sql）  
**特点**: 标准测试账号，有初始余额

---

### 🆕 新注册账号

| ID | 用户名 | 手机号 | 余额(CNY) | 余额(USDT) | 状态 |
|----|--------|--------|-----------|------------|------|
| 15 | prouser001 | - | 0 | 0 | ✅ |
| 16 | providence888 | - | 0 | 0 | ✅ |

**特点**: 新注册，无初始余额

---

## 🗄️ 数据表结构 (31张表)

### 核心业务表
- `users` - 用户表 (9条记录)
- `projects` - 项目表 (12条记录)
- `invest_orders` - 投资订单表
- `recharge_records` - 充值记录表
- `withdraw_records` - 提现记录表

### 日利宝相关
- `ribao_config` - 日利宝配置
- `ribao_records` - 日利宝记录

### 财务相关
- `balance_log` - 余额日志
- `wallet_logs` - 钱包日志
- `earnings` - 收益记录

### 用户关系
- `user_relations` - 用户关系（邀请）
- `referral_rewards` - 推荐奖励
- `team_rewards` - 团队奖励
- `team_reward_rules` - 团队奖励规则

### 积分系统
- `points_log` - 积分日志
- `trial_funds` - 体验金

### VIP系统
- `vip_configs` - VIP配置
- `vip_levels` - VIP等级

### 支付相关
- `user_bank_cards` - 用户银行卡
- `user_usdt_addresses` - USDT地址

### 安全与风控
- `risk_alerts` - 风险警报
- `risk_blacklist` - 黑名单
- `face_verification_logs` - 人脸识别日志

### 内容管理
- `announcements` - 公告
- `activities` - 活动
- `popups` - 弹窗
- `project_categories` - 项目分类

### 系统管理
- `admins` - 管理员
- `admin_operation_logs` - 管理员操作日志
- `login_logs` - 登录日志
- `system_configs` - 系统配置

---

## 🔐 测试账号密码

### 查看密码
```bash
cat /www/wwwroot/api.4kp3l0iq.top/测试账号.sql
```

### 常用测试账号

**账号1 (推荐使用):**
- 手机号: `13800138688`
- 用户名: `G138688`
- 密码: 见测试账号.sql（通常是 `password` 或 `123456`）

**账号2:**
- 手机号: `13900000002`
- 用户名: `kehu002`
- 密码: 见测试账号.sql

---

## 💰 账户余额分配

### 管理员账号
```
CNY:  1,000,000 元（100万）
USDT: 10,000 USDT（1万U）
```

### 普通测试账号（每个）
```
CNY:  10,000 元（1万）
USDT: 100 USDT（100U）
```

### 新注册账号
```
CNY:  0 元
USDT: 0 USDT
```

---

## 🔧 数据库操作命令

### 连接数据库
```bash
mysql -u root -p
USE providence;
```

### 查看用户
```sql
SELECT id, username, phone, balance_cny, balance_usdt, status 
FROM users;
```

### 查看项目
```sql
SELECT id, name, daily_rate, min_amount, max_amount, days, status 
FROM projects;
```

### 重置测试账号密码
```sql
-- 密码改为: 123456
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE id = 1;
```

### 给账号加余额
```sql
UPDATE users 
SET balance_cny = 50000, balance_usdt = 500 
WHERE id = 1;
```

---

## 📝 快速登录指南

### 方法1: 使用主账号
1. 访问: https://4kp3l0iq.top/login.html
2. 手机号: `13800138688`
3. 密码: 查看测试账号.sql文件

### 方法2: 使用测试账号
1. 访问: https://4kp3l0iq.top/login.html
2. 手机号: `13900000002` ~ `13900000007`（任选）
3. 密码: 查看测试账号.sql文件

### 方法3: 注册新账号
1. 访问: https://4kp3l0iq.top/register.html
2. 填写信息注册
3. 登录使用

---

## ⚠️ 注意事项

### 1. 密码加密
数据库中的密码都是bcrypt加密的，格式：
```
$2y$10$...
```

### 2. 邀请码
- 每个用户都有唯一的8位数字邀请码
- 用于推荐奖励系统

### 3. VIP等级
- 当前所有用户都是VIP 0
- 根据投资金额自动升级

### 4. 余额类型
- `balance_cny` - 人民币余额
- `balance_usdt` - USDT余额
- 两种币种完全隔离

---

## 🎯 登录问题排查

如果出现"用户不存在"：

1. **清除Token**
```javascript
localStorage.clear()
sessionStorage.clear()
```

2. **使用诊断工具**
https://4kp3l0iq.top/diagnose-login.html

3. **检查数据库**
```sql
SELECT * FROM users WHERE phone = '13800138688';
```

4. **查看后端日志**
```bash
tail -f /www/wwwroot/api.4kp3l0iq.top/runtime/log/*_error.log
```

---

**数据库状态**: ✅ 健康  
**测试账号**: ✅ 可用  
**建议**: 使用 13800138688 账号登录测试
