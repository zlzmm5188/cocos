#!/bin/bash
# 一键修复所有JS文件中的API路径问题

echo "🔧 开始批量修复所有API路径问题..."
echo ""

# 备份
BACKUP_DIR="backups/api-fix-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp *.js "$BACKUP_DIR/" 2>/dev/null

# 1. 替换所有 localhost:8888 为正确的域名
echo "1️⃣ 修复 localhost:8888..."
find . -maxdepth 1 -name "*.js" -exec sed -i "s|https://api.4kp3l0iq.top/api|https://api.4kp3l0iq.top|g" {} +
find . -maxdepth 1 -name "*.js" -exec sed -i "s|'https://api.4kp3l0iq.top/api'|'https://api.4kp3l0iq.top'|g" {} +

# 2. 确保所有 fetch 调用的路径都带 /api 前缀
echo "2️⃣ 修复 fetch 调用路径..."
# 修复 fetch(API_BASE + '/user/xxx' 的模式
find . -maxdepth 1 -name "*.js" -exec sed -i "s|\${API_BASE}/user/|\${API_BASE}/api/user/|g" {} +
find . -maxdepth 1 -name "*.js" -exec sed -i "s|API_BASE + '/user/|API_BASE + '/api/user/|g" {} +
find . -maxdepth 1 -name "*.js" -exec sed -i "s|API_BASE + \"/user/|API_BASE + \"/api/user/|g" {} +

# 修复其他常见路径
find . -maxdepth 1 -name "*.js" -exec sed -i "s|\${API_BASE}/project/|\${API_BASE}/api/project/|g" {} +
find . -maxdepth 1 -name "*.js" -exec sed -i "s|API_BASE + '/project/|API_BASE + '/api/project/|g" {} +

find . -maxdepth 1 -name "*.js" -exec sed -i "s|\${API_BASE}/pay/|\${API_BASE}/api/pay/|g" {} +
find . -maxdepth 1 -name "*.js" -exec sed -i "s|API_BASE + '/pay/|API_BASE + '/api/pay/|g" {} +

# 3. 防止双层 /api/api
echo "3️⃣ 清理双层 /api/api..."
find . -maxdepth 1 -name "*.js" -exec sed -i "s|/api/|/api/|g" {} +

echo ""
echo "✅ 修复完成！"
echo ""
echo "修复统计："
echo "- localhost:8888 实例: $(grep -r "localhost:8888" --include="*.js" 2>/dev/null | wc -l)"
echo "- 正确的 /api/ 路径: $(grep -r "api.4kp3l0iq.top/api/" --include="*.js" 2>/dev/null | wc -l)"
echo ""
echo "备份位置: $BACKUP_DIR"
