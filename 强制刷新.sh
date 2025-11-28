#!/bin/bash

echo "======================================"
echo "  强制清除浏览器缓存并重启服务"
echo "======================================"
echo ""

# 1. 重启前台服务（强制重新加载文件）
echo "1️⃣  重启前台服务..."
lsof -ti:3000 | xargs kill -9 2>/dev/null
sleep 1
python3 -m http.server 3000 > /dev/null 2>&1 &
echo "   ✅ 前台已重启"

# 2. 重启后端服务
echo "2️⃣  重启后端服务..."
lsof -ti:8888 | xargs kill -9 2>/dev/null
sleep 1
cd ../api/public && php -S 0.0.0.0:8888 test-api.php > /tmp/php-backend.log 2>&1 &
echo "   ✅ 后端已重启"

sleep 2

echo ""
echo "======================================"
echo "  ✅ 所有服务已重启！"
echo "======================================"
echo ""
echo "现在请执行以下步骤："
echo ""
echo "1. 在浏览器中打开："
echo "   http://localhost:3000/clear-cache.html"
echo ""
echo "2. 点击【清除所有缓存】按钮"
echo ""
echo "3. 自动跳转到登录页后，重新登录"
echo ""
echo "或者直接访问（带时间戳破缓存）："
echo "   http://localhost:3000/login.html?t=$(date +%s)"
echo ""
