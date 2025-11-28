#!/bin/bash
# 修复config.js中所有缺少/api前缀的路径

echo "修复config.js中的API路径..."

# 备份
cp config.js config.js.backup.$(date +%Y%m%d_%H%M%S)

# 批量替换：在ApiService对象内的路径前添加/api
sed -i "s|http\.get('/user/|http.get('/api/user/|g" config.js
sed -i "s|http\.post('/user/|http.post('/api/user/|g" config.js
sed -i "s|http\.get('/project/|http.get('/api/project/|g" config.js
sed -i "s|http\.get('/pay/|http.get('/api/pay/|g" config.js
sed -i "s|http\.post('/recharge/|http.post('/api/recharge/|g" config.js
sed -i "s|http\.post('/withdraw/|http.post('/api/withdraw/|g" config.js

echo "✅ 修复完成"
echo ""
echo "修复的路径："
grep -n "http\.get('/api/\|http\.post('/api/" config.js | head -20
