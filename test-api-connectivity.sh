#!/bin/bash

# Providence API连接测试脚本
# 测试前端能否正常连接后台API

echo "======================================"
echo "Providence API 连接测试"
echo "======================================"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

API_BASE="https://api.4kp3l0iq.top"

# 测试接口列表
declare -A endpoints=(
    ["健康检查"]="/api/health"
    ["用户信息(需登录)"]="/api/user/index"
    ["项目列表"]="/api/project/index"
)

echo -e "${YELLOW}测试API基础连接...${NC}"
echo ""

success_count=0
fail_count=0

for name in "${!endpoints[@]}"; do
    endpoint="${endpoints[$name]}"
    url="${API_BASE}${endpoint}"
    
    echo -n "测试: $name ($endpoint)... "
    
    # 发送请求
    response=$(curl -s -w "\n%{http_code}" -X GET "$url" \
        -H "Content-Type: application/json" \
        --max-time 10 2>&1)
    
    http_code=$(echo "$response" | tail -n 1)
    body=$(echo "$response" | head -n -1)
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "401" ]; then
        echo -e "${GREEN}✓ 成功${NC} (HTTP $http_code)"
        ((success_count++))
        
        # 检查响应格式
        if echo "$body" | grep -q '"code"'; then
            echo "  响应格式: $(echo "$body" | head -c 100)..."
        fi
    else
        echo -e "${RED}✗ 失败${NC} (HTTP $http_code)"
        ((fail_count++))
        echo "  响应: $(echo "$body" | head -c 200)"
    fi
    echo ""
done

echo "======================================"
echo -e "测试结果: ${GREEN}成功 $success_count${NC} / ${RED}失败 $fail_count${NC}"
echo "======================================"

if [ $fail_count -eq 0 ]; then
    echo -e "${GREEN}✅ 所有API接口连接正常！${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠️  部分接口连接失败，请检查后端服务${NC}"
    exit 1
fi
