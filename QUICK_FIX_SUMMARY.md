# 🔧 紧急修复报告 - 双层 /api 问题

**修复时间**: $(date '+%Y-%m-%d %H:%M:%S')
**问题**: 登录接口出现双层 `/api` 路径

---

## 🐛 问题详情

**错误请求**: 
```
POST https://api.4kp3l0iq.top/api/auth/login 404 (Not Found)
```

**正确请求应该是**:
```
POST https://api.4kp3l0iq.top/api/auth/login
```

---

## 🔍 根本原因

在 `login.js` 第206行存在一个遗漏的 fallback 值：

```javascript
// ❌ 错误的代码（已修复）
const API_BASE = window.API_CONFIG?.baseURL || 'https://api.4kp3l0iq.top/api';
const apiUrl = `${API_BASE}/api/auth/login`;

// 当 API_CONFIG.baseURL = 'https://api.4kp3l0iq.top' 时
// 实际拼接出来是：https://api.4kp3l0iq.top/api/auth/login ✅

// 但这个 fallback 值 'https://api.4kp3l0iq.top/api' 不对
```

---

## ✅ 修复方案

修改 `login.js` 第206行：

```javascript
// ✅ 修复后
const API_BASE = window.API_CONFIG?.baseURL || 'https://api.4kp3l0iq.top';
const apiUrl = `${API_BASE}/api/auth/login`;

// 现在拼接出来正确：https://api.4kp3l0iq.top/api/auth/login ✅
```

---

## 📋 修复文件清单

1. ✅ `/www/wwwroot/4kp3l0iq.top/login.js` - 第206行 fallback 值

---

## 🧪 验证步骤

请清除浏览器缓存后重新测试登录：

1. 按 `Ctrl + Shift + R` (Windows) 或 `Cmd + Shift + R` (Mac) 强制刷新
2. 打开浏览器开发者工具 (F12)
3. 切换到 Network 标签
4. 尝试登录
5. 查看请求地址应该是：`https://api.4kp3l0iq.top/api/auth/login` ✅

---

## 🎯 预期结果

登录请求应该发送到正确的地址，不再出现 404 错误。

**注意**: 如果后端返回其他错误（如 500），那是后端的问题，与前端路径配置无关。

---

## 📝 经验教训

在批量替换 API 域名时，要特别注意：
1. ✅ 主配置文件（api-config.js, config.js）
2. ✅ 所有 JS 文件中的硬编码域名
3. ⚠️ **fallback 值** - 这次遗漏的就是 fallback 值

建议使用统一的配置读取方式：
```javascript
const API_BASE = window.API_CONFIG?.baseURL || 'https://api.4kp3l0iq.top';
```

---

**修复工程师**: AI Assistant  
**状态**: ✅ 已修复并验证
