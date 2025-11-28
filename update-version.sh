#!/bin/bash
# 更新所有JS文件的版本号，强制浏览器重新加载

TIMESTAMP=$(date +%s)
echo "新版本号: $TIMESTAMP"

# 更新 login.html 中的 login.js 版本号
sed -i "s|login\.js?v=[0-9]*|login.js?v=$TIMESTAMP|g" login.html

# 更新 index.html 等其他HTML中的JS版本号
for html in index.html profile.html register.html; do
    if [ -f "$html" ]; then
        # 更新所有带版本号的JS引用
        sed -i "s|\.js?v=[0-9]*|.js?v=$TIMESTAMP|g" "$html"
        echo "✓ 更新 $html"
    fi
done

echo ""
echo "✅ 版本号已更新为: $TIMESTAMP"
echo ""
echo "请在浏览器中："
echo "1. 按 Ctrl+Shift+R (或 Cmd+Shift+R) 强制刷新"
echo "2. 或者清除浏览器缓存后重新访问"
