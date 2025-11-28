#!/bin/bash
# 为所有HTML文件添加智能导航系统（替换旧的smart-swipe-control.js）

echo "开始为所有HTML文件添加智能导航系统..."

find . -maxdepth 1 -name "*.html" ! -name ".*" | while read file; do
    # 检查是否已经包含smart-navigation.js
    if grep -q "smart-navigation.js" "$file"; then
        echo "  ⏭️  跳过 $file (已包含)"
    else
        # 如果有旧的smart-swipe-control.js，替换它
        if grep -q "smart-swipe-control.js" "$file"; then
            sed -i 's/smart-swipe-control\.js/smart-navigation.js/g' "$file"
            echo "  ✅ 已替换 $file (smart-swipe-control -> smart-navigation)"
        else
            # 在</body>之前插入JS引用
            sed -i '/<\/body>/i\    <script src="smart-navigation.js?v='$(date +%s)'"><\/script>' "$file"
            echo "  ✅ 已添加 $file"
        fi
    fi
done

echo ""
echo "✅ 所有文件处理完成！"
