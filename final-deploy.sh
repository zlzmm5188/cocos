#!/bin/bash

# Providence 最终部署脚本
# 将dist目录内容同步到正式目录

set -e

echo "======================================"
echo "Providence 最终部署"
echo "======================================"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SOURCE_DIR="/www/wwwroot/4kp3l0iq.top/dist"
TARGET_DIR="/www/wwwroot/4kp3l0iq.top"

# 验证源目录
if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${RED}错误: dist目录不存在！${NC}"
    exit 1
fi

# 备份当前配置
echo -e "${YELLOW}备份当前API配置...${NC}"
if [ -f "$TARGET_DIR/api-config.js" ]; then
    cp "$TARGET_DIR/api-config.js" "$TARGET_DIR/api-config.js.backup.$(date +%Y%m%d_%H%M%S)"
fi
if [ -f "$TARGET_DIR/config.js" ]; then
    cp "$TARGET_DIR/config.js" "$TARGET_DIR/config.js.backup.$(date +%Y%m%d_%H%M%S)"
fi

# 同步文件（保持现有的备份和文档文件）
echo -e "${YELLOW}同步文件到正式目录...${NC}"
rsync -av --exclude='备份*' --exclude='backup*' --exclude='*.md' --exclude='dist' \
    --exclude='tools' --exclude='docs' --exclude='*.sh' \
    "$SOURCE_DIR/" "$TARGET_DIR/"

# 验证关键文件
echo -e "${YELLOW}验证部署...${NC}"
critical_files=(
    "index.html"
    "login.html"
    "profile.html"
    "api-config.js"
    "config.js"
)

all_ok=true
for file in "${critical_files[@]}"; do
    if [ -f "$TARGET_DIR/$file" ]; then
        echo -e "${GREEN}  ✓ $file${NC}"
    else
        echo -e "${RED}  ✗ $file 缺失${NC}"
        all_ok=false
    fi
done

# 检查API配置
if grep -q "api.4kp3l0iq.top" "$TARGET_DIR/api-config.js"; then
    echo -e "${GREEN}  ✓ API配置正确 (api.4kp3l0iq.top)${NC}"
else
    echo -e "${RED}  ✗ API配置错误${NC}"
    all_ok=false
fi

echo ""
if [ "$all_ok" = true ]; then
    echo -e "${GREEN}======================================"
    echo "部署成功！"
    echo "======================================${NC}"
    echo "前端地址: https://4kp3l0iq.top"
    echo "API地址: https://api.4kp3l0iq.top"
    echo "管理后台: https://admin.4kp3l0iq.top"
else
    echo -e "${RED}======================================"
    echo "部署存在问题，请检查！"
    echo "======================================${NC}"
    exit 1
fi
