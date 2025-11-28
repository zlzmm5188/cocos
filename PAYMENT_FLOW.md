# 充值页面 - 支付流程完整说明

## 核心流程图

```
┌──────────────────────────────────────────────────────────────┐
│                        用户打开充值页                          │
│                   (recharge.html 加载)                       │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
        ┌─────────────────────────────────┐
        │  DOMContentLoaded 事件触发       │
        ├─────────────────────────────────┤
        │ 1. 检查登录状态 ✓                │
        │ 2. 加载余额 (CNY/USDT) ✓         │
        │ 3. 加载USDT地址 (非阻塞) ✓       │
        │ 4. 检查待支付订单 ✓              │
        │ 5. 绑定弹窗事件 (单次) ✓         │
        └─────────────────┬───────────────┘
                          │
        ┌─────────────────▼───────────────────┐
        │      用户选择支付方式                │
        │   (selectPayment 函数调用)          │
        ├─────────────────────────────────────┤
        │ ┌─ 银行卡                           │
        │ ├─ 微信                             │
        │ ├─ 支付宝                           │
        │ └─ USDT (TRC20)                     │
        └─────────────┬───────────────────────┘
                      │
        ┌─────────────┴──────────────────────┐
        │                                    │
        ▼                                    ▼
    ┌───────────────┐            ┌──────────────────────┐
    │ 微信/支付宝    │            │   USDT 充值          │
    │              │            │                      │
    │ 输入金额     │            │ 显示充值地址         │
    │ (最低100元)  │            │ (TRC20)              │
    │              │            │ 显示二维码            │
    │ 点击提交     │            │ 30分钟倒计时          │
    └────┬─────────┘            │                      │
         │                       └──────────┬───────────┘
         │                                  │
         ▼                                  ▼
    ┌─────────────────────────────────────────────┐
    │  调用后端 API 创建订单                       │
    │  (createPaymentOrder / createUsdtRechargeOrder)
    ├─────────────────────────────────────────────┤
    │ POST /api/payment/wechat/create (微信)       │
    │ POST /api/payment/alipay/create (支付宝)    │
    │ POST /api/payment/usdt/create (USDT)        │
    └────┬────────────────────────────────────────┘
         │
         ▼
    ┌──────────────────────────────────┐
    │ 后端返回订单信息                   │
    ├──────────────────────────────────┤
    │ ├─ 支付链接 (payUrl)              │
    │ ├─ 二维码 (qrcode)                │
    │ ├─ 订单号 (order_no)              │
    │ └─ 订单ID                         │
    └────┬─────────────────────────────┘
         │
    ┌────┴────────────────────┐
    │                         │
    ▼ (有支付链接)            ▼ (有二维码)
┌──────────────┐        ┌─────────────────┐
│  跳转第三方  │        │  显示二维码弹窗  │
│  支付页面    │        │  (showQRCode    │
│              │        │   Modal)        │
└────┬─────────┘        │                │
     │                  │ 用户扫码支付   │
     │                  │ 或通过钱包转账  │
     └──────┬───────────┴────┬──────┬────┘
            │                │      │
            │                │      └─────┐
            │                │            │
            ▼                ▼            ▼
      ┌────────────────────────────────────────┐
      │   启动轮询 / 监控 (自动后台运行)      │
      ├────────────────────────────────────────┤
      │ pollPaymentStatus(orderNo)  每3秒      │
      │ startUsdtMonitoring(orderNo) 每5秒    │
      │                                       │
      │ 最多运行30分钟                        │
      └────┬──────────┬──────────────────────┘
           │          │
      ┌────▼──────────▼────┐
      │   检查订单状态      │
      │ GET /api/recharge/ │
      │     list           │
      │ GET /api/payment/  │
      │     usdt/check     │
      └────┬─────────┬─────┘
           │         │
      ┌────▼────┐  ┌─▼────────────┐
      │status=1 │  │ status≠1     │
      │已支付    │  │ 继续轮询/监控│
      └────┬────┘  └──────────────┘
           │
           ▼
    ┌──────────────────────┐
    │  订单完成处理         │
    ├──────────────────────┤
    │ 1. 停止轮询          │
    │ 2. 关闭弹窗          │
    │ 3. 清除订单记录      │
    │ 4. 显示成功提示      │
    │ 5. 刷新余额          │
    │ 6. 清空表单          │
    └──────────────────────┘
```

---

## API 端点详解

### 1. USDT 配置获取
```http
GET /api/payment/usdtconfig
Authorization: Bearer {token}

Response:
{
  "code": 1,
  "data": {
    "usdt_address": "TVU2B61wJEk6VAvdPDA3KQGD2Dpz888888",
    "network": "TRC20"
  }
}
```

### 2. 创建微信支付订单
```http
POST /api/payment/wechat/create
Authorization: Bearer {token}
Content-Type: application/json

Body:
{
  "amount": 100
}

Response:
{
  "code": 1,
  "data": {
    "order_no": "ORDER-123456789",
    "payUrl": "https://pay.wechat.com/...",
    "qrcode": "https://..." (可选)
  }
}
```

### 3. 创建支付宝支付订单
```http
POST /api/payment/alipay/create
Authorization: Bearer {token}
Content-Type: application/json

Body:
{
  "amount": 100
}

Response:
{
  "code": 1,
  "data": {
    "order_no": "ORDER-123456789",
    "payUrl": "https://pay.alipay.com/...",
    "qrcode": "https://..." (可选)
  }
}
```

### 4. 创建 USDT 充值订单
```http
POST /api/payment/usdt/create
Authorization: Bearer {token}
Content-Type: application/json

Body:
{
  "amount": 10
}

Response:
{
  "code": 1,
  "data": {
    "order_no": "USDT-123456789",
    "usdt_address": "TVU2B61wJEk6VAvdPDA3KQGD2Dpz888888",
    "amount": 10,
    "expireTime": 1734144000
  }
}
```

### 5. 检查 USDT 付款状态
```http
GET /api/payment/usdt/check?order_no=USDT-123456789
Authorization: Bearer {token}

Response:
{
  "code": 1,
  "data": {
    "status": 1,  // 1=已支付, 0=待支付, -1=已取消
    "amount": 10,
    "tx_hash": "0x..." (交易哈希)
  }
}
```

### 6. 查询充值订单列表
```http
GET /api/recharge/list?order_no=ORDER-123456789
Authorization: Bearer {token}

Response:
{
  "code": 1,
  "data": {
    "list": [
      {
        "order_no": "ORDER-123456789",
        "amount": 100,
        "currency": "CNY",
        "status": 1,  // 0=待支付, 1=已支付, -1=已取消
        "payment_method": "wechat",
        "created_at": "2025-11-25 10:00:00"
      }
    ]
  }
}
```

---

## 关键函数说明

### selectPayment(method)
**作用**: 选择支付方式
- 参数: 'bank' | 'usdt' | 'wechat' | 'alipay'
- 功能: 切换余额显示顺序、显示/隐藏 USDT 地址区域

### createPaymentOrder(amount, paymentMethod)
**作用**: 创建微信/支付宝支付订单
- 调用 API: POST /api/payment/{wechat|alipay}/create
- 返回处理: payUrl 跳转 | qrcode 显示弹窗 | 继续轮询

### createUsdtRechargeOrder(amount)
**作用**: 创建 USDT 充值订单
- 调用 API: POST /api/payment/usdt/create
- 启动监控: startUsdtMonitoring()

### pollPaymentStatus(orderNo)
**作用**: 轮询微信/支付宝订单状态 (每3秒)
- 调用 API: GET /api/recharge/list
- 最多运行: 30 分钟
- 停止条件: 订单完成 | 过期

### startUsdtMonitoring(orderNo)
**作用**: 监控 USDT 链上交易 (每5秒)
- 调用 API: GET /api/payment/usdt/check
- 最多运行: 30 分钟
- 停止条件: 交易确认 | 过期

### confirmUsdtPaid()
**作用**: 用户手动检查 USDT 付款状态
- 调用 API: GET /api/payment/usdt/check
- 功能: 立即检查一次，不会阻塞自动监控

---

## 数据流说明

### localStorage 存储

```javascript
// 待支付订单（用于恢复）
pending_payment_order: {
  order_no: "ORDER-123456789",
  amount: 100,
  payment_method: "wechat",
  timestamp: 1734099600000
}

// USDT 充值订单（30分钟有效期）
usdt_recharge_order: {
  order_no: "USDT-123456789",
  timestamp: 1734099600000
}
```

### 全局变量

```javascript
selectedMethod = "bank"      // 当前选择的支付方式
rechargeAmount = 0           // 充值金额
usdtAddress = ""             // USDT 充值地址
balances = {                 // 用户余额
  cny: 0,
  usdt: 0
}

// 轮询控制
paymentPollInterval = null   // 支付订单轮询句柄
usdtMonitorInterval = null   // USDT 监控句柄
usdtCountdownInterval = null // USDT 倒计时句柄
usdtOrderNo = null           // 当前 USDT 订单号
```

---

## 错误处理流程

### 网络错误
```
状态: 任何网络错误
处理: .catch(() => null)
显示: "网络错误，请重试" 提示
继续: 继续轮询或重试
```

### API 错误
```
状态: code ≠ 1
处理: 检查 result.msg 或 result.message
显示: 后端错误信息（去除 URL）
继续: 停止轮询，提示用户重试
```

### 订单过期
```
状态: 轮询超过 30 分钟
处理: 自动清除 clearInterval
显示: "订单已过期，请重新创建" 提示
继续: 用户可重新提交
```

### 支付失败
```
状态: status = -1
处理: 立即停止轮询
显示: "支付失败，请重试" 提示
继续: 用户可重新提交
```

---

## 性能优化总结

✅ **非阻塞异步**: 所有 fetch 使用 `.catch(() => null)` 防止 UI 卡顿
✅ **单次事件绑定**: DOMContentLoaded 仅绑定一次，避免重复
✅ **时间计算优化**: 使用 `Date.now()` 计算已耗时，避免嵌套 setTimeout
✅ **内存清理**: 明确的 clearInterval 和变量赋值为 null
✅ **Fallback 处理**: 所有失败都返回 fallback 值而非抛出异常
✅ **自动超时**: 轮询和监控都会在 30 分钟后自动停止
✅ **立即响应**: 轮询启动时立即执行一次检查，增加响应速度
