#!/bin/bash

# Providence 项目前端部署脚本
# 版本: 1.0.0
# 日期: 2025-11-23

set -e

echo "======================================"
echo "Providence 前端部署脚本"
echo "======================================"

# 定义颜色
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 工作目录
SOURCE_DIR="/www/wwwroot/4kp3l0iq.top"
DIST_DIR="${SOURCE_DIR}/dist"
DEPLOY_DIR="/www/wwwroot/4kp3l0iq.top"

echo -e "${YELLOW}步骤 1/5: 清理dist目录...${NC}"
if [ -d "$DIST_DIR" ]; then
    rm -rf "$DIST_DIR"
fi
mkdir -p "$DIST_DIR"

echo -e "${YELLOW}步骤 2/5: 复制核心HTML文件...${NC}"
# 复制所有HTML文件（排除备份和测试文件）
find "$SOURCE_DIR" -maxdepth 1 -name "*.html" \
    ! -name "*test*" \
    ! -name "*backup*" \
    ! -name "*debug*" \
    ! -name "*-old.*" \
    ! -name "*broken*" \
    -exec cp {} "$DIST_DIR/" \;

echo -e "${YELLOW}步骤 3/5: 复制JS、CSS和资源文件...${NC}"
# 复制必要的目录
for dir in js css img assets lib components; do
    if [ -d "$SOURCE_DIR/$dir" ]; then
        cp -r "$SOURCE_DIR/$dir" "$DIST_DIR/"
        echo "  ✓ 复制 $dir/"
    fi
done

# 复制核心JS文件（根目录下的）
for file in api-config.js config.js login.js register.js profile.js app.js \
            token-manager-v2.js api-unified.js api-utils.js ios-toast.js \
            ribao.js ribao-api.js team-api.js team-rewards.js \
            project-api.js project-detail.js my-investments.js \
            recharge-api.js payment-api.js bank-bind-api.js \
            transaction-api.js notification-api.js \
            checkin.js daily-checkin-api.js deposit.js \
            kyc-verification-ocr.js face-recognition-api.js \
            forgot.js reset-password.js finance.js \
            profit-calendar.js trial-money-popup.js \
            invite.js invite-share.js; do
    if [ -f "$SOURCE_DIR/$file" ]; then
        cp "$SOURCE_DIR/$file" "$DIST_DIR/"
    fi
done

echo -e "${YELLOW}步骤 4/5: 复制配置和资源文件...${NC}"
# 复制其他必要文件
for file in manifest.json favicon.ico *.css; do
    if [ -f "$SOURCE_DIR/$file" ]; then
        cp "$SOURCE_DIR/$file" "$DIST_DIR/" 2>/dev/null || true
    fi
done

echo -e "${YELLOW}步骤 5/5: 验证API配置...${NC}"
# 检查api-config.js是否存在并包含正确的域名
if grep -q "api.4kp3l0iq.top" "$DIST_DIR/api-config.js"; then
    echo -e "${GREEN}  ✓ API配置正确${NC}"
else
    echo -e "${RED}  ✗ API配置异常${NC}"
    exit 1
fi

# 统计文件数量
html_count=$(find "$DIST_DIR" -maxdepth 1 -name "*.html" | wc -l)
js_count=$(find "$DIST_DIR" -name "*.js" | wc -l)

echo ""
echo "======================================"
echo -e "${GREEN}部署完成！${NC}"
echo "======================================"
echo "  HTML文件: $html_count"
echo "  JS文件: $js_count"
echo "  目标目录: $DIST_DIR"
echo ""
echo "下一步："
echo "  1. 检查 $DIST_DIR 内容"
echo "  2. 如果确认无误，将文件部署到正式目录"
echo "======================================"
