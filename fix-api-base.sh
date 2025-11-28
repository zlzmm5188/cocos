#!/bin/bash

ROOT="/www/wwwroot/4kp3l0iq.top"

echo "🔧 开始批量修复 API 路径..."

# 1. 修复双重 /api/api
grep -Rl "api/api" $ROOT | xargs sed -i "s#https://api.4kp3l0iq.top/api#https://api.4kp3l0iq.top/api#g"

# 2. 修复旧域名
grep -Rl "apis.frevix.top" $ROOT | xargs sed -i "s#https://api.4kp3l0iq.top/api#https://api.4kp3l0iq.top/api#g"

# 3. 修复 localhost
grep -Rl "localhost:8888/api" $ROOT | xargs sed -i "s#https://api.4kp3l0iq.top/api#https://api.4kp3l0iq.top/api#g"

grep -Rl "localhost:8888" $ROOT | xargs sed -i "s#https://api.4kp3l0iq.top/api#https://api.4kp3l0iq.top/api#g"

echo "✅ 修复完成！"
