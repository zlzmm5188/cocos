// PROVIDENCE前台 - 首页数据加载 - 统一使用config.js的API封装
// 确保在HTML中已加载config.js: <script src="config.js"></script>
(function () {
  // const TOKEN_KEY = 'providence_token';

  // 等待API对象加载
  function waitForAPI() {
    return new Promise((resolve) => {
      if (window.API && window.API_CONFIG) {
        resolve();
      } else {
        const check = setInterval(() => {
          if (window.API && window.API_CONFIG) {
            clearInterval(check);
            resolve();
          }
        }, 50);
        setTimeout(() => {
          clearInterval(check);
          resolve();
        }, 1500);
      }
    });
  }

  const $ = (s) => document.querySelector(s);

  window.userData = {
    totalAsset: 0,
    totalIncome: 0,
    vipLevel: 1,
    loaded: false
  };

  // app.js 首页提速（并行加载）
  async function initPage() {
    try {
      const token = localStorage.getItem("providence_token");
      if (!token) {
        location.href = "/login.html";
        return;
      }

      // 🚀 并行同时请求所有首页数据
      const [userRes, ribaoRes] = await Promise.all([
        window.httpClient.get("/api/user/info"),  // 修正：使用统一API路径
        window.httpClient.get("/user/api/ribao/head").catch(() => null)  // 修正：使用统一API路径，失败不阻塞
      ]);

      // 更新 UI（使用现有的 updateUI 函数）
      const userDataObj = (userRes && userRes.data) ? userRes.data : (userRes || {});
      if (userDataObj && (userDataObj.id || userDataObj.user_id)) {
        userData.totalAsset = parseFloat(userDataObj.balance_cny || userDataObj.money || 0) + parseFloat(userDataObj.ribao_cny || userDataObj.ribao || 0);
        userData.totalIncome = parseFloat(userDataObj.total_profit_cny || userDataObj.tfund || 0);
        userData.vipLevel = parseInt(userDataObj.vip_level || userDataObj.level || 1);
        userData.loaded = true;
        updateUI();
      }

      // 处理日利宝数据（如果有）
      if (ribaoRes && (ribaoRes.code === 1 || ribaoRes.code === 200)) {
        const ribaoData = ribaoRes.data || ribaoRes;
        // 可以在这里更新日利宝相关的UI
        // renderRibao(ribaoData);
      }
    } catch (e) {
      console.error("首页加载失败:", e);
      updateUI(); // 即使失败也更新UI（显示默认值）
    }
  }

  function updateUI() {
    if ($('#totalAsset')) {
      $('#totalAsset').textContent = formatMoney(userData.totalAsset);
// console.log('🖼️ 首页总资产已更新为:', $('#totalAsset').textContent); // 性能优化：已注释
    }
    if ($('#totalIncome')) {
      $('#totalIncome').textContent = formatMoney(userData.totalIncome);
// console.log('🖼️ 首页总收益已更新为:', $('#totalIncome').textContent); // 性能优化：已注释
    }
    const vipCard = $('.award-badge') || $('.vipcard');
    if (vipCard && userData.vipLevel >= 1) {
      vipCard.setAttribute('data-vip-level', userData.vipLevel);
// console.log('🎯 VIP卡片已更新为:', userData.vipLevel); // 性能优化：已注释
    }
  }

  function formatMoney(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  window.addEventListener('DOMContentLoaded', () => {
// console.log('🚀 PROVIDENCE前台启动'); // 性能优化：已注释
    initPage();
  });

  window.toggleAsset = function () {
    const assetEl = $('#totalAsset');
    const incomeEl = $('#totalIncome');
    const eyeEl = $('.eye-toggle');
    if (!assetEl || !incomeEl || !eyeEl) return;

    if (assetEl.textContent.includes('*')) {
      assetEl.textContent = formatMoney(userData.totalAsset);
      incomeEl.textContent = formatMoney(userData.totalIncome);
      eyeEl.textContent = '👁';
    } else {
      assetEl.textContent = '***,***.**';
      incomeEl.textContent = '***,***.**';
      eyeEl.textContent = '🙈';
    }
  };
})();
