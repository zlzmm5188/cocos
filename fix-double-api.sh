#!/bin/bash
# 修复双层/api问题的脚本

echo "正在检查并修复双层/api路径问题..."

# 在所有JS文件中查找 API_BASE + '/api/' 模式
# 这些应该改为 API_BASE + '/' (因为baseURL已经不带/api了)

# 但要注意：如果代码本意就是要访问 /api/xxx 路径，那就不应该修改

# 检查常见的错误模式
echo "检查结果："
grep -rn "API_BASE.*+.*['\"]\/api\/" . --include="*.js" 2>/dev/null | grep -v "备份" | grep -v "backup" | while read line; do
    echo "$line"
done

echo ""
echo "提示：检查上述文件，确认是否为双层/api问题"
