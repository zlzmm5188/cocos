# Providence 系统全面修复计划

## 修复时间：2025-11-21 08:30
## 目标：让所有功能正常运行，前台后台数据库完全对接

---

## 第一部分：UI优化（profile.html）

### 1.1 九宫格上移
- 参考index.html：VIP卡margin: -30px，九宫格margin-top: -90px
- profile.html：调整九宫格margin-top，给VIP卡留出空间

### 1.2 重构四个毛玻璃卡片
- 高度缩小（从当前高度减少）
- 宽度拉长（2边拉长）
- 数字自适应（使用clamp()）
- 数字贴底部

### 1.3 银行卡/实名认证板块
- 四个卡片改为长条
- 一排2条布局
- 重新设计样式

### 1.4 背景色调整
- 九宫格背景换成白色

---

## 第二部分：功能对接

### 2.1 银行卡绑定
- API: POST /api/user/bank/bind
- 前端页面：bank-bind.html
- 对接API工具类

### 2.2 USDT绑定
- API: POST /api/user/usdt/bind
- 前端页面：usdt-bind.html
- 对接API工具类

### 2.3 交易记录
- API: GET /api/user/transactions
- 对接现有页面或创建新页面

### 2.4 AI金融顾问
- 对接AI客服API
- 确保AI服务正常运行

### 2.5 人工详情重构
- 重构收益说明UI
- 让显示更舒适

### 2.6 充值功能
- 支付宝充值：POST /api/pay/recharge/alipay
- 微信充值：POST /api/pay/recharge/wechat
- USDT充值：POST /api/pay/recharge/usdt
- 自动上分功能

---

## 第三部分：后端检查

### 3.1 数据库检查
- 检查所有表结构
- 检查字段完整性
- 检查索引

### 3.2 后台充值审核
- 检查审核流程
- 修复审核功能

### 3.3 后台提现审核
- 检查审核流程
- 修复审核功能

---

## 第四部分：全面检查

### 4.1 API检查
- 检查所有API路由
- 检查API响应格式
- 检查错误处理

### 4.2 前端检查
- 检查所有页面
- 检查JS错误
- 检查API调用

### 4.3 数据库检查
- 检查数据一致性
- 检查关联关系

---

## 执行顺序

1. ✅ 修复profile.js账号金额显示
2. ✅ 修复profile.html UI
3. ✅ 对接所有API
4. ✅ 检查后端
5. ✅ 全面测试

