#!/bin/bash
# 前端 API 集成验证脚本

echo "🔍 前端 API 集成检查..."
echo ""

# 颜色定义
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 检查文件存在
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✅${NC} 文件存在: $1"
        return 0
    else
        echo -e "${RED}❌${NC} 文件不存在: $1"
        return 1
    fi
}

# 检查 API 路径
check_api_path() {
    local file=$1
    local pattern=$2
    local name=$3

    if grep -q "$pattern" "$file" 2>/dev/null; then
        echo -e "${GREEN}✅${NC} $name 已正确配置"
        return 0
    else
        echo -e "${RED}❌${NC} $name 配置错误或不存在"
        return 1
    fi
}

echo "📋 检查银行卡管理接口..."
check_api_path "/www/wwwroot/4kp3l0iq.top/bank-cards.html" "/pay/bank/list" "获取银行卡列表"
check_api_path "/www/wwwroot/4kp3l0iq.top/bank-cards.html" "/pay/bank/add" "添加银行卡"
check_api_path "/www/wwwroot/4kp3l0iq.top/bank-cards.html" "/pay/bank/del" "删除银行卡"

echo ""
echo "📋 检查投资项目接口..."
check_api_path "/www/wwwroot/4kp3l0iq.top/my-investments.html" "/api/project/index" "获取投资项目"

echo ""
echo "📋 检查个人中心接口..."
check_api_path "/www/wwwroot/4kp3l0iq.top/profile.js" "/api/user/vip-progress" "VIP进度"

echo ""
echo "📋 检查日利宝接口..."
if grep -q "let userData = " "/www/wwwroot/4kp3l0iq.top/ribao.js" && \
   ! grep -q "let userData = " "/www/wwwroot/4kp3l0iq.top/ribao.html" | grep -v "script"; then
    echo -e "${GREEN}✅${NC} userData 声明无重复"
else
    echo -e "${YELLOW}⚠️${NC} 需要检查 userData 声明"
fi

echo ""
echo "📋 检查占位符逻辑..."
if grep -q "功能即将上线" "/www/wwwroot/4kp3l0iq.top/bank-cards.html"; then
    echo -e "${RED}❌${NC} bank-cards.html 仍有占位符逻辑"
else
    echo -e "${GREEN}✅${NC} bank-cards.html 占位符已移除"
fi

if grep -q "功能即将上线" "/www/wwwroot/4kp3l0iq.top/my-investments.html"; then
    echo -e "${RED}❌${NC} my-investments.html 仍有占位符逻辑"
else
    echo -e "${GREEN}✅${NC} my-investments.html 占位符已移除"
fi

echo ""
echo "📋 检查 Token 头配置..."
check_api_path "/www/wwwroot/4kp3l0iq.top/bank-cards.html" "'Token':" "Token 头大写"

echo ""
echo "📋 检查错误处理..."
check_api_path "/www/wwwroot/4kp3l0iq.top/bank-cards.html" "if (!response.ok)" "HTTP 状态检查"
check_api_path "/www/wwwroot/4kp3l0iq.top/my-investments.html" "if (!response.ok)" "HTTP 状态检查"

echo ""
echo "✅ 集成检查完成！"
