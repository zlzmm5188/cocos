# Providence 后台管理系统

## 项目说明

这是 Providence 资金盘返利系统的后台管理面板，用于管理用户、产品、订单、财务等核心业务功能。

## 功能模块

### 1. 仪表盘 (Dashboard)
- 用户统计（总数、今日新增）
- 投资统计（总金额、今日金额）
- 待审核充值/提现
- 最近订单
- 投资趋势图表
- 用户VIP分布图

### 2. 用户管理
- 用户列表（搜索、筛选、分页）
- 用户详情查看/编辑
- VIP等级设置
- 余额调整
- 密码重置

### 3. 实名认证 (KYC)
- 待审核列表
- 身份证照片审核
- 人脸照片审核
- 通过/拒绝操作

### 4. 团队管理
- 团队列表
- 邀请关系查看
- 返佣统计

### 5. 产品管理
- 投资产品列表
- 新增/编辑产品
- 产品状态管理（开启/关闭）
- 募集进度监控

### 6. 订单管理
- 投资订单列表
- 订单详情
- 订单结算
- 订单统计

### 7. 充值审核
- 待审核充值列表
- 凭证审核
- 充值通过/拒绝
- 充值统计

### 8. 提现审核
- 待审核提现列表
- 收款信息核对
- 提现通过/拒绝
- 打款确认
- 批量操作

### 9. 日利宝管理
- 日利宝用户列表
- 收益派发
- 配置管理

### 10. 交易记录
- 全部交易流水
- 按类型筛选
- 导出功能

### 11. VIP配置
- VIP等级配置
- 累计投资要求
- 加息比例设置
- 返佣比例设置

### 12. 系统设置
- 基础信息设置
- 财务参数设置
- 日利宝设置
- USDT设置

## 技术栈

- HTML5 + CSS3
- 原生 JavaScript
- Font Awesome 图标
- 响应式布局

## 快速开始

### 方式一：静态文件访问

直接用浏览器打开 `admin/login.html` 文件即可使用（使用Mock数据）。

### 方式二：使用Mock服务器

```bash
# 进入项目目录
cd cocos

# 启动Mock服务器
node admin/api/mock-server.cjs

# 访问后台
# http://localhost:3000/admin/
```

### 方式三：集成到后端

将 `admin` 目录部署到你的Web服务器，并确保后端API路由与 `admin/api/admin-routes.js` 中定义的一致。

## 默认账号

| 账号 | 密码 | 角色 |
|------|------|------|
| admin | admin123 | 超级管理员 |
| manager | manager123 | 运营经理 |
| operator | operator123 | 客服人员 |

## 目录结构

```
admin/
├── index.html          # 仪表盘
├── login.html          # 登录页面
├── users.html          # 用户管理
├── kyc.html            # 实名认证
├── teams.html          # 团队管理
├── products.html       # 产品管理
├── orders.html         # 订单管理
├── recharges.html      # 充值审核
├── withdrawals.html    # 提现审核
├── ribao.html          # 日利宝管理
├── transactions.html   # 交易记录
├── vip-config.html     # VIP配置
├── settings.html       # 系统设置
├── css/
│   └── admin.css       # 管理后台样式
├── js/
│   └── admin-config.js # 配置和工具函数
└── api/
    ├── admin-routes.js # API路由定义
    └── mock-server.cjs # Mock服务器
```

## API 接口

完整的API接口定义请参考 `admin/api/admin-routes.js` 文件。

### 主要接口：

| 接口 | 方法 | 说明 |
|------|------|------|
| /admin/login | POST | 管理员登录 |
| /admin/stats | GET | 仪表盘统计 |
| /admin/users | GET | 用户列表 |
| /admin/recharges | GET | 充值记录 |
| /admin/recharge-approve | POST | 充值审核通过 |
| /admin/recharge-reject | POST | 充值审核拒绝 |
| /admin/withdrawals | GET | 提现记录 |
| /admin/withdraw-approve | POST | 提现审核通过 |
| /admin/withdraw-reject | POST | 提现审核拒绝 |
| /admin/withdraw-pay | POST | 确认打款 |
| /admin/kyc-list | GET | KYC申请列表 |
| /admin/kyc-approve | POST | KYC审核通过 |
| /admin/kyc-reject | POST | KYC审核拒绝 |
| /admin/products | GET | 产品列表 |
| /admin/project-save | POST | 保存产品 |
| /admin/orders | GET | 订单列表 |
| /admin/vip-config | GET | VIP配置 |
| /admin/settings | GET | 系统设置 |

## 与前端API对应关系

后台管理系统需要处理前台的所有业务数据，API接口与前台使用相同的数据结构：

- 用户信息：`/user/user/index`
- 投资产品：`/fund/project/all`
- 充值申请：`/user/recharge/add`
- 提现申请：`/user/withdraw/add`
- 日利宝：`/user/ribao/*`
- 团队信息：`/user/team/team`

## 注意事项

1. **安全性**：生产环境请确保启用HTTPS
2. **权限控制**：实现完整的角色权限控制
3. **日志记录**：记录所有管理操作日志
4. **数据备份**：定期备份重要数据

## 开发团队

Providence Team

## 许可证

MIT License
