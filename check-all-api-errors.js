/**
 * 全面检查所有API调用错误
 * 用于在浏览器控制台运行，检查所有API调用
 */

(function() {
// console.log('🔍 开始检查所有API调用...\n'); // 性能优化：已注释

    const errors = [];
    const warnings = [];

    // 拦截fetch请求
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const url = args[0];
        const options = args[1] || {};

        // 只检查API请求
        if (typeof url === 'string' && url.includes('copla.top')) {
// console.log(`📡 [API请求] ${options.method || 'GET'} ${url}`); // 性能优化：已注释

            return originalFetch.apply(this, args)
                .then(response => {
                    // 检查响应
                    const contentType = response.headers.get('content-type') || '';
                    const isJSON = contentType.includes('application/json');

                    if (!isJSON && response.status !== 200) {
                        errors.push({
                            url,
                            method: options.method || 'GET',
                            status: response.status,
                            issue: '返回非JSON格式或错误状态码'
                        });
                        console.error(`❌ [API错误] ${url} - HTTP ${response.status}, 非JSON响应`);
                    }

                    return response;
                })
                .catch(error => {
                    errors.push({
                        url,
                        method: options.method || 'GET',
                        error: error.message
                    });
                    console.error(`❌ [API错误] ${url} - ${error.message}`);
                    throw error;
                });
        }

        return originalFetch.apply(this, args);
    };

    // 检查所有已知的API端点
    const knownAPIs = [
        '/api/user/info',
        '/api/project/list',
        '/user/order/list',
        '/user/api/ribao/head',
        '/user/api/ribao/list',
        '/user/api/ribao/in',
        '/user/api/ribao/out',
        '/user/team/reward-rules',
        '/user/team/my-rewards',
        '/user/team/claim-reward',
        '/user/vip/progress',
        '/user/checkin/status',
        '/user/checkin/do',
        '/user/points/goods',
        '/user/points/exchange',
        '/user/level/list',
        '/user/user/invite',
        '/api/project/index',
        '/fund/api/project/detail',
        '/fund/api/project/add',
        '/auth/login',
        '/pay/bank/list',
        '/pay/pay/recharge',
        '/pay/pay/withdraw'
    ];

// console.log('📋 已知API端点:', knownAPIs.length, '个'); // 性能优化：已注释
// console.log('\n等待页面加载完成...\n'); // 性能优化：已注释

    // 5秒后输出报告
    setTimeout(() => {
// console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'); // 性能优化：已注释
// console.log('📊 API检查报告'); // 性能优化：已注释
// console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n'); // 性能优化：已注释

        if (errors.length > 0) {
            console.error(`❌ 发现 ${errors.length} 个API错误:\n`);
            errors.forEach((err, i) => {
                console.error(`${i + 1}. ${err.method || 'GET'} ${err.url}`);
                console.error(`   状态: ${err.status || 'N/A'}`);
                console.error(`   问题: ${err.issue || err.error}\n`);
            });
        } else {
            console.log('✅ 未发现API错误');
        }

// console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'); // 性能优化：已注释
    }, 5000);

    // 导出错误列表供外部使用
    window.__apiErrors = errors;
})();
