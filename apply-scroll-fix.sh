#!/bin/bash
# 为所有HTML文件添加全局滚动优化CSS

echo "开始为所有HTML文件添加滚动优化..."

# 查找所有HTML文件（排除备份目录）
find . -maxdepth 1 -name "*.html" ! -name ".*" | while read file; do
    # 检查是否已经包含global-scroll-fix.css
    if grep -q "global-scroll-fix.css" "$file"; then
        echo "  ⏭️  跳过 $file (已包含)"
    else
        # 在</head>之前插入CSS引用
        sed -i '/<\/head>/i\    <link rel="stylesheet" href="global-scroll-fix.css?v='$(date +%s)'">' "$file"
        echo "  ✅ 已更新 $file"
    fi
done

echo ""
echo "✅ 所有文件处理完成！"
