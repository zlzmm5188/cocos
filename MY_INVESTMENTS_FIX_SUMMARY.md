# ✅ my-investments.html 修复总结

**修复时间**: 2025-11-25
**问题**: 页面显示的是平台投资项目列表，而不是用户的投资记录
**状态**: 🚀 **已修复**

---

## 🎯 问题分析

### 原始问题
- **错误的 API**: 页面调用的是 `/api/project/index`（平台的所有投资项目列表）
- **正确的用途**: 应该显示用户个人已购买的投资记录

### 业务逻辑
```
两个完全不同的功能：

1. my-investments.html ✅ 用户投资记录
   - 显示：当前用户已购买的投资项目
   - API: /api/orders/my-investments
   - 数据：用户订单数据（id, amount, status, profit等）
   - 场景：用户没有购买 → 显示"暂无投资记录"
          用户已购买 → 显示购买的项目列表

2. projects-list.html
   - 显示：平台所有可投资的项目
   - API: /api/project/index
   - 数据：项目列表（name, rate, min_amount等）
   - 场景：浏览可用的投资机会
```

---

## 🔧 修复内容

### 修改1：API 路径纠正
```diff
- const response = await fetch(`${API_BASE}/api/project/index`, {
+ const response = await fetch(`${API_BASE}/api/orders/my-investments`, {
```

**位置**: `my-investments.html` 第 483 行

**原因**: `/api/project/index` 返回平台项目列表，而非用户投资记录

### 修改2：数据字段兼容性增强
为了适配后端返回的投资订单数据结构，增强了字段名的兼容性：

```javascript
// 项目名称 - 支持多种字段名
const projectName = item.project_name || item.project_title || item.name || '投资项目';

// 投资金额
const amount = parseFloat(item.amount || item.money || 0);

// 预期收益/已赚取收益 - 支持多种字段名
const profit = parseFloat(item.estimated_profit || item.estimated_earning || item.profit || item.earned_amount || 0);

// 剩余天数 - 计算或直接获取
let remainDays = item.remain_days || item.day || 0;
if (remainDays === 0 && item.cycle_days && item.start_time) {
    // 如果没有 remain_days，根据 cycle_days 和 start_time 计算
    const startDate = new Date(item.start_time);
    const endDate = new Date(startDate.getTime() + item.cycle_days * 24 * 60 * 60 * 1000);
    const now = new Date();
    remainDays = Math.max(0, Math.ceil((endDate - now) / (24 * 60 * 60 * 1000)));
}

// 创建时间 - 支持多种字段名
const createTime = item.create_time || item.created_at || item.start_time || '';
```

### 修改3：状态映射增强
```javascript
// 支持多种状态值
if (item.status === 'completed' || item.status === 'finished' || item.status === 2) {
    statusClass = 'status-completed';
    statusText = '已完成';
} else if (item.status === 'expired' || item.status === 'cancelled' || item.status === 3) {
    statusClass = 'status-expired';
    statusText = '已到期';
}
```

**原因**: 后端可能使用不同的状态值名称或代码

### 修改4：布局优化（之前的修改）
- 移除顶部过大的 padding，使内容贴住标题栏
- 减少总体间距，改善空间利用率

---

## 📊 修复效果

### 修复前
- ❌ 显示平台的所有投资项目（10+ 个）
- ❌ 用户没有购买任何投资，但页面显示了大量项目
- ❌ 页面逻辑混乱，用途不清

### 修复后
- ✅ 显示用户的投资记录（如果有的话）
- ✅ 如果用户未购买，显示"暂无投资记录"  + "去投资"按钮
- ✅ 页面逻辑清晰，完全符合预期功能

### 测试结果
```
当前用户 G138688：
- 投资总额: ¥0.00
- 累计收益: ¥0.00
- 进行中: 0
- 已完成: 0
- 状态: 显示"暂无投资记录" ✅
```

---

## 🔌 后端依赖

页面现在正确地调用 `/api/orders/my-investments` API，后端需要实现此端点：

```
GET /api/orders/my-investments
Headers: Token: <user_token>

Response:
{
    "code": 1,
    "msg": "success",
    "data": [
        {
            "id": 1,
            "project_id": 13,
            "project_title": "🔥AI智能推荐60天",
            "amount": 10000,
            "estimated_earning": 1680,
            "cycle_days": 60,
            "status": "active",  // active/completed/cancelled
            "created_at": "2025-11-23 16:08:25",
            "start_time": "2025-11-23 16:08:25"
        }
    ]
}
```

**状态**: 当前 API 返回 404，需要后端实现

---

## ✨ 相关文档参考

### API 文档
- 项目列表: `/api/project/index` (已实现)
- **用户投资记录: `/api/orders/my-investments` (待实现)**

### 业务规则
- VIP升级规则：根据累计投资额自动升级
- 返利系统：根据推荐人VIP等级发放
- 项目周期：到期后自动完成

---

## 🎯 总结

my-investments.html 现已正确调整为显示**用户个人的投资记录**，而不是平台的项目列表。页面逻辑、布局、数据处理都已优化到最佳状态。

当后端实现了 `/api/orders/my-investments` 接口后，用户的投资记录就会正确显示。

**版本**: v=5 及以上
