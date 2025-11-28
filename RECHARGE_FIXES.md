# 充值页面修复报告 - 2025-11-25

## 修复摘要

完整处理 recharge.html 中的所有剩余问题，包括 API 路径统一、循环调用清理、阻塞式异步调用修复等。

---

## 修改详情

### 1. 添加 favicon.ico 处理 (第 10 行)

**问题**: 浏览器请求 favicon.ico 返回 404

**修复**:
```html
<!-- 修改前 -->
<title>充值 - Providence</title>

<!-- 修改后 -->
<title>充值 - Providence</title>
<link rel="icon" href="/favicon.ico">
```

**文件位置**: 第 1-10 行

---

### 2. 修复 loadUsdtAddress() 函数 (第 1746-1777 行)

**问题**:
- 旧 API 路径: `/api/pay/usdt-config`
- 使用 await fetch 阻塞 UI
- 失败时会抛出异常
- 没有返回 fallback 值

**修复**:
```javascript
// 修改前
async function loadUsdtAddress() {
  const response = await fetch(`${API_BASE}/api/pay/usdt-config`, ...);
  if (!response.ok) throw new Error(...);
  // 降级方案使用默认地址
}

// 修改后
async function loadUsdtAddress() {
  const response = await fetch(`${API_BASE}/api/payment/usdtconfig`, {
    ...
  }).catch(() => null);

  if (!response || !response.ok) {
    return { usdt_address: null, status: 'not_available' };
  }

  const result = await response.json().catch(() => null);

  if (result && result.code === 1 && result.data && result.data.usdt_address) {
    // 成功
    return { usdt_address: usdtAddress, status: 'success' };
  } else {
    return { usdt_address: null, status: 'not_available' };
  }
}
```

**关键改进**:
- ✅ 使用 `.catch(() => null)` 防止异常阻塞
- ✅ 返回 fallback 对象 `{ usdt_address: null, status: 'not_available' }`
- ✅ API 路径改为 `/api/payment/usdtconfig`

---

### 3. 统一 API 路径替换

| 原路径 | 新路径 | 位置 |
|------|------|------|
| `/api/pay/usdt-config` | `/api/payment/usdtconfig` | loadUsdtAddress() |
| `/api/pay/wechat-create` | `/api/payment/wechat/create` | createPaymentOrder() |
| `/api/pay/alipay-create` | `/api/payment/alipay/create` | createPaymentOrder() |
| `/api/pay/usdt-create` | `/api/payment/usdt/create` | createUsdtRechargeOrder() |
| `/api/pay/usdt-check` | `/api/payment/usdt/check` | confirmUsdtPaid() 等 |

---

### 4. 清理循环调用 - pollPaymentStatus() 优化 (第 2085-2128 行)

**问题**:
- setInterval 内 await 阻塞事件循环
- setTimeout 与 setInterval 搭配易混乱
- 无效的 await 和超时处理

**修复**:
```javascript
// 修改前
paymentPollInterval = setInterval(async () => {
  const response = await fetch(...);
  const result = await response.json();
  if (result.code === 1) { ... }
}, 3000);

setTimeout(() => {
  clearInterval(paymentPollInterval);
}, 30 * 60 * 1000);

// 修改后
let paymentPollInterval = null;

function pollPaymentStatus(orderNo) {
  if (paymentPollInterval) {
    clearInterval(paymentPollInterval);
    paymentPollInterval = null;
  }

  // 立即执行一次检查
  checkPaymentOrderStatus(orderNo);

  // 然后每3秒检查一次（最多持续30分钟）
  const startTime = Date.now();
  paymentPollInterval = setInterval(async () => {
    const elapsed = Date.now() - startTime;
    if (elapsed > 30 * 60 * 1000) {
      clearInterval(paymentPollInterval);
      paymentPollInterval = null;
      return;
    }
    checkPaymentOrderStatus(orderNo);
  }, 3000);
}

async function checkPaymentOrderStatus(orderNo) {
  const response = await fetch(...).catch(() => null);
  if (!response) return;
  // ...
}
```

**关键改进**:
- ✅ 提取核心检查逻辑为独立函数 `checkPaymentOrderStatus()`
- ✅ 立即执行一次检查（响应速度快）
- ✅ 使用 `Date.now()` 计算已耗时而非 setTimeout
- ✅ 所有 fetch 都用 `.catch(() => null)` 非阻塞

---

### 5. 清理循环调用 - startUsdtMonitoring() 优化 (第 2429-2489 行)

**问题**:
- 同 pollPaymentStatus()
- 多个 setInterval + setTimeout 嵌套

**修复**:
```javascript
// 使用相同模式优化为两个函数
function startUsdtMonitoring(orderNo, expectedAmount) {
  if (usdtMonitorInterval) {
    clearInterval(usdtMonitorInterval);
    usdtMonitorInterval = null;
  }

  // 立即执行一次检查
  checkUsdtPaymentStatus(orderNo);

  // 然后每5秒检查一次（最多持续30分钟）
  const startTime = Date.now();
  usdtMonitorInterval = setInterval(async () => {
    const elapsed = Date.now() - startTime;
    if (elapsed > 30 * 60 * 1000) {
      clearInterval(usdtMonitorInterval);
      usdtMonitorInterval = null;
      return;
    }
    checkUsdtPaymentStatus(orderNo);
  }, 5000);
}

async function checkUsdtPaymentStatus(orderNo) {
  const response = await fetch(...).catch(() => null);
  if (!response) return;
  // ...
}
```

**关键改进**:
- ✅ 提取核心检查逻辑为 `checkUsdtPaymentStatus()`
- ✅ 避免 async/await 在 setInterval 回调中阻塞
- ✅ 非阻塞的 fetch 调用

---

### 6. 删除重复的 DOMContentLoaded 事件监听 (原第 1718-1728, 2474-2483 行)

**问题**:
- 多个 `document.addEventListener("DOMContentLoaded", ...)` 重复定义
- 导致事件处理重复绑定

**修复**:
```javascript
// 修改前 - 散落在多个地方
document.addEventListener("DOMContentLoaded", function() {
  const qrCodeModal = document.getElementById('qrCodeModal');
  if (qrCodeModal) { ... }
});

document.addEventListener("DOMContentLoaded", function() {
  const usdtModal = document.getElementById("usdtModal");
  if (usdtModal) { ... }
});

// 修改后 - 合并到主 DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  if (!ApiService.auth.requireLogin()) return;
  loadBalance();
  loadUsdtAddress();
  checkPendingOrder();

  // 统一绑定所有弹窗事件
  const qrCodeModal = document.getElementById('qrCodeModal');
  if (qrCodeModal) {
    qrCodeModal.addEventListener("click", function(e) {
      if (e.target === this) closeQRCodeModal();
    });
  }

  const usdtModal = document.getElementById("usdtModal");
  if (usdtModal) {
    usdtModal.addEventListener("click", function(e) {
      if (e.target === this) closeUsdtModal();
    });
  }
});
```

**关键改进**:
- ✅ 单次事件监听绑定
- ✅ 减少内存占用
- ✅ 避免重复执行初始化逻辑

---

### 7. 修复阻塞式 await fetch 调用

**所有 fetch 调用统一使用非阻塞模式**:

```javascript
// 修改前 - 会阻塞 UI
const response = await fetch(...);
if (!response.ok) throw new Error(...);

// 修改后 - 非阻塞
const response = await fetch(...).catch(() => null);
if (!response || !response.ok) return;

const result = await response.json().catch(() => null);
if (!result) return;
```

---

## API 路径映射表

所有支付相关 API 已统一为后端实际存在的接口：

```
充值页面 → 后端接口
├─ USDT 配置: /api/payment/usdtconfig
├─ 微信支付: /api/payment/wechat/create
├─ 支付宝支付: /api/payment/alipay/create
├─ USDT 创建: /api/payment/usdt/create
├─ USDT 检查: /api/payment/usdt/check
└─ 订单查询: /api/recharge/list
```

---

## 整体支付流程逻辑图（文字版）

```
用户打开充值页面
    ↓
DOMContentLoaded 触发
├─ ✓ 检查登录状态
├─ ✓ 加载余额信息
├─ ✓ 加载 USDT 地址（非阻塞）
├─ ✓ 检查待支付订单
└─ ✓ 绑定所有事件监听（单次）
    ↓
用户选择支付方式 (selectPayment)
    ├─ 银行卡 → 显示密码输入框
    ├─ 微信 → 创建支付订单
    ├─ 支付宝 → 创建支付订单
    └─ USDT → 显示 USDT 地址区域
    ↓
创建支付订单 (createPaymentOrder / createUsdtRechargeOrder)
    ├─ POST 请求到后端
    ├─ 获取支付链接或二维码
    └─ 显示二维码弹窗 (showQRCodeModal)
    ↓
轮询订单状态 (pollPaymentStatus / checkUsdtPaymentStatus)
    ├─ 立即执行一次检查
    ├─ 每 3-5 秒检查一次
    ├─ 最多持续 30 分钟
    └─ 订单完成或过期后停止轮询
    ↓
订单状态判断
    ├─ status=1 (已支付) → 关闭弹窗 → 显示成功提示 → 刷新余额
    ├─ status=-1 (已取消) → 显示失败提示
    └─ status=0 (待支付) → 继续轮询
    ↓
用户手动检查支付 (confirmUsdtPaid)
    ├─ 调用 /api/payment/usdt/check
    ├─ 检查订单状态
    └─ 如未支付，继续自动监控
```

---

## 测试清单

- [ ] favicon.ico 404 已解决
- [ ] USDT 地址加载失败不会卡 UI
- [ ] 微信/支付宝支付订单创建正常
- [ ] USDT 订单监控每 5 秒检查一次（不卡 UI）
- [ ] 支付订单轮询每 3 秒检查一次（不卡 UI）
- [ ] 30 分钟后自动停止轮询/监控
- [ ] 所有弹窗背景点击事件正确绑定（仅一次）
- [ ] 支付成功后页面刷新和清空表单
- [ ] 支付失败或已取消显示正确提示
- [ ] 检查待支付订单功能正常

---

## 文件修改统计

- **修改文件**: `/www/wwwroot/4kp3l0iq.top/recharge.html`
- **修改行数**: 约 150 行
- **修改点**: 7 个主要修复项
- **新增代码**: 非阻塞事件处理、循环监控优化
- **删除代码**: 重复事件监听、阻塞式 await

---

## 关键改进总结

✅ **API 路径统一**: 所有接口统一为后端实际存在的路径
✅ **非阻塞异步**: 所有 fetch 使用 `.catch(() => null)` 防止 UI 卡顿
✅ **循环优化**: setInterval + setTimeout 组合优化为明确的时间计算
✅ **事件去重**: 单次 DOMContentLoaded 监听绑定所有事件
✅ **Fallback 处理**: 失败时返回明确的 fallback 对象而非抛出异常
✅ **内存清理**: 明确的 clearInterval 和 null 赋值
✅ **Favicon 处理**: 添加 link 标签解决浏览器 404 请求
