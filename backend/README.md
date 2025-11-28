# 资金盘管理后台 API

这是一个用于资金盘管理的后端 API 服务，使用 Node.js + Express + MongoDB 构建。

## 功能特性

- 用户认证（注册、登录、JWT令牌）
- 用户管理（CRUD操作）
- 资金盘管理（创建、编辑、成员管理）
- 交易记录管理（入金、出金、收益）
- 仪表盘统计数据
- 基于角色的权限控制（管理员、经理、用户）

## 技术栈

- Node.js
- Express.js
- MongoDB + Mongoose
- JWT 认证
- bcryptjs 密码加密

## 项目结构

```
backend/
├── src/
│   ├── config/          # 配置文件
│   │   ├── constants.js # 常量定义
│   │   └── database.js  # 数据库连接
│   ├── controllers/     # 控制器
│   │   ├── authController.js
│   │   ├── userController.js
│   │   ├── fundController.js
│   │   ├── transactionController.js
│   │   └── dashboardController.js
│   ├── middleware/      # 中间件
│   │   ├── auth.js
│   │   ├── errorHandler.js
│   │   └── validate.js
│   ├── models/          # 数据模型
│   │   ├── User.js
│   │   ├── Fund.js
│   │   └── Transaction.js
│   ├── routes/          # 路由
│   │   ├── authRoutes.js
│   │   ├── userRoutes.js
│   │   ├── fundRoutes.js
│   │   ├── transactionRoutes.js
│   │   └── dashboardRoutes.js
│   ├── utils/           # 工具函数
│   │   └── seeder.js    # 数据填充
│   ├── app.js           # Express应用
│   └── server.js        # 服务器入口
├── .env.example         # 环境变量示例
├── package.json
└── README.md
```

## 安装

1. 安装依赖
```bash
cd backend
npm install
```

2. 配置环境变量
```bash
cp .env.example .env
# 编辑 .env 文件，设置数据库连接和JWT密钥
```

3. 运行服务
```bash
# 开发模式
npm run dev

# 生产模式
npm start
```

4. 导入示例数据（可选）
```bash
npm run seed
```

## API 文档

### 认证接口

| 方法 | 路径 | 描述 | 权限 |
|------|------|------|------|
| POST | /api/auth/register | 用户注册 | 公开 |
| POST | /api/auth/login | 用户登录 | 公开 |
| GET | /api/auth/me | 获取当前用户 | 需登录 |
| PUT | /api/auth/updatedetails | 更新用户信息 | 需登录 |
| PUT | /api/auth/updatepassword | 更新密码 | 需登录 |
| GET | /api/auth/logout | 退出登录 | 需登录 |

### 用户管理接口

| 方法 | 路径 | 描述 | 权限 |
|------|------|------|------|
| GET | /api/users | 获取用户列表 | 管理员 |
| GET | /api/users/:id | 获取用户详情 | 管理员 |
| POST | /api/users | 创建用户 | 管理员 |
| PUT | /api/users/:id | 更新用户 | 管理员 |
| DELETE | /api/users/:id | 删除用户 | 管理员 |
| PUT | /api/users/:id/toggle-status | 切换用户状态 | 管理员 |

### 资金盘接口

| 方法 | 路径 | 描述 | 权限 |
|------|------|------|------|
| GET | /api/funds | 获取资金盘列表 | 需登录 |
| GET | /api/funds/:id | 获取资金盘详情 | 需登录 |
| POST | /api/funds | 创建资金盘 | 管理员/经理 |
| PUT | /api/funds/:id | 更新资金盘 | 管理员/经理 |
| DELETE | /api/funds/:id | 删除资金盘 | 管理员 |
| GET | /api/funds/:id/stats | 获取资金盘统计 | 需登录 |
| POST | /api/funds/:id/members | 添加成员 | 管理员/经理 |
| DELETE | /api/funds/:id/members/:userId | 移除成员 | 管理员/经理 |

### 交易接口

| 方法 | 路径 | 描述 | 权限 |
|------|------|------|------|
| GET | /api/transactions | 获取交易列表 | 需登录 |
| GET | /api/transactions/:id | 获取交易详情 | 需登录 |
| POST | /api/transactions | 创建交易 | 需登录 |
| PUT | /api/transactions/:id/process | 处理交易 | 管理员/经理 |
| DELETE | /api/transactions/:id | 删除交易 | 管理员 |
| GET | /api/transactions/stats | 获取交易统计 | 管理员/经理 |

### 仪表盘接口

| 方法 | 路径 | 描述 | 权限 |
|------|------|------|------|
| GET | /api/dashboard/stats | 获取仪表盘统计 | 管理员/经理 |
| GET | /api/dashboard/charts | 获取图表数据 | 管理员/经理 |

## 默认账户

导入示例数据后，可使用以下账户登录：

- 管理员: admin@example.com / admin123
- 经理: manager1@example.com / manager123
- 用户: user1@example.com / user123

## 许可证

ISC
