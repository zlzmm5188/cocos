# API 路径修复总结
**日期**: 2025-11-23
**问题**: 部分API路径错误，导致404和JSON解析失败

## 已修复的文件

### 1. invite.js
- ❌ `/user/user/invite` → ✅ `/api/user/invite`
- ❌ `/user/team/team` → ✅ `/api/user/team` (3处)

### 2. forgot.js
- ❌ `/api/user/user/resetPassword` → ✅ `/api/user/resetPassword`

### 3. reset-password.js
- ❌ `/api/user/user/verifyFaceReset` → ✅ `/api/user/verifyFaceReset`
- ❌ `/api/user/user/resetPassword` → ✅ `/api/user/resetPassword`

## 后端路由对照表

| 前端调用 | 后端路由 | 控制器方法 |
|---------|---------|-----------|
| `/api/user/index` | `api/user/index` | User::index() |
| `/api/user/invite` | `api/user/invite` | User::invite() |
| `/api/user/team` | `api/user/team` | User::team() |
| `/api/user/vip-progress` | `api/user/vip-progress` | User::vipProgress() |
| `/api/user/resetPassword` | `api/user/resetPassword` | User::resetPassword() |

## 测试验证

```bash
# 测试用户信息
curl -H "Token: YOUR_TOKEN" https://api.4kp3l0iq.top/api/user/index

# 测试邀请信息
curl -H "Token: YOUR_TOKEN" https://api.4kp3l0iq.top/api/user/invite

# 测试团队信息
curl -H "Token: YOUR_TOKEN" -X POST https://api.4kp3l0iq.top/api/user/team
```

## 注意事项

⚠️ **所有API调用必须遵循以下规范**:
1. 必须以 `/api/` 开头
2. 不能有重复路径段（如 `/user/user/`）
3. 必须带 `Token` 请求头
4. 后端路由配置在 `/www/wwwroot/api.4kp3l0iq.top/route/api.php`

## 清除缓存

修改后需要：
1. **清除CloudFlare缓存**（如果启用）
2. **浏览器强制刷新** (Ctrl+Shift+R)
3. **添加版本号参数** `?v=时间戳`
